// ========================================
// Phase 2: 리포트 생성 엔진
// ========================================

const { TrafficLog, BlockedIp, AttackEvent, ReportSchedule, ReportHistory } = require('./report-system-phase1');
const moment = require('moment-timezone');

// ========================================
// 1. 데이터 집계 함수들
// ========================================

// 주간 통계 집계 (지난 7일)
async function aggregateWeeklyStats(userId) {
    const endDate = new Date();
    const startDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
    
    try {
        // 총 트래픽 및 차단 통계
        const trafficStats = await TrafficLog.aggregate([
            {
                $match: {
                    userId: userId,
                    timestamp: { $gte: startDate, $lte: endDate }
                }
            },
            {
                $group: {
                    _id: null,
                    totalRequests: { $sum: 1 },
                    blockedRequests: { $sum: { $cond: ['$blocked', 1, 0] } },
                    uniqueIPs: { $addToSet: '$sourceIp' },
                    totalDataTransferred: { $sum: { $add: ['$requestSize', '$responseSize'] } }
                }
            }
        ]);
        
        // 차단된 IP 통계
        const blockedIpStats = await BlockedIp.aggregate([
            {
                $match: {
                    userId: userId,
                    blockedAt: { $gte: startDate, $lte: endDate }
                }
            },
            {
                $group: {
                    _id: '$country',
                    count: { $sum: 1 },
                    ips: { $push: '$ip' }
                }
            },
            {
                $sort: { count: -1 }
            },
            {
                $limit: 10
            }
        ]);
        
        // 공격 이벤트 통계
        const attackStats = await AttackEvent.aggregate([
            {
                $match: {
                    userId: userId,
                    startedAt: { $gte: startDate, $lte: endDate }
                }
            },
            {
                $group: {
                    _id: '$attackType',
                    count: { $sum: 1 },
                    avgDuration: { $avg: '$duration' },
                    totalRequests: { $sum: '$requestCount' },
                    mitigated: { $sum: { $cond: ['$mitigated', 1, 0] } }
                }
            },
            {
                $sort: { count: -1 }
            }
        ]);
        
        // 시간대별 트래픽 분포
        const hourlyTraffic = await TrafficLog.aggregate([
            {
                $match: {
                    userId: userId,
                    timestamp: { $gte: startDate, $lte: endDate }
                }
            },
            {
                $group: {
                    _id: { $hour: '$timestamp' },
                    count: { $sum: 1 }
                }
            },
            {
                $sort: { '_id': 1 }
            }
        ]);
        
        return {
            success: true,
            period: { start: startDate, end: endDate },
            traffic: trafficStats[0] || { totalRequests: 0, blockedRequests: 0, uniqueIPs: [], totalDataTransferred: 0 },
            blockedIPs: blockedIpStats,
            attacks: attackStats,
            hourlyDistribution: hourlyTraffic
        };
    } catch (error) {
        console.error('Weekly stats aggregation error:', error);
        return { success: false, error: error.message };
    }
}

// 월간 통계 집계 (지난 30일)
async function aggregateMonthlyStats(userId) {
    const endDate = new Date();
    const startDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
    
    try {
        // 기본 통계 (주간과 동일하지만 기간이 30일)
        const basicStats = await aggregateWeeklyStats(userId);
        
        // 일별 트렌드 분석
        const dailyTrend = await TrafficLog.aggregate([
            {
                $match: {
                    userId: userId,
                    timestamp: { $gte: startDate, $lte: endDate }
                }
            },
            {
                $group: {
                    _id: {
                        year: { $year: '$timestamp' },
                        month: { $month: '$timestamp' },
                        day: { $dayOfMonth: '$timestamp' }
                    },
                    totalRequests: { $sum: 1 },
                    blockedRequests: { $sum: { $cond: ['$blocked', 1, 0] } }
                }
            },
            {
                $sort: { '_id.year': 1, '_id.month': 1, '_id.day': 1 }
            }
        ]);
        
        // 공격 심각도별 통계
        const severityStats = await AttackEvent.aggregate([
            {
                $match: {
                    userId: userId,
                    startedAt: { $gte: startDate, $lte: endDate }
                }
            },
            {
                $group: {
                    _id: '$severity',
                    count: { $sum: 1 }
                }
            }
        ]);
        
        // Layer별 공격 분석
        const layerStats = await AttackEvent.aggregate([
            {
                $match: {
                    userId: userId,
                    startedAt: { $gte: startDate, $lte: endDate },
                    layer: { $exists: true }
                }
            },
            {
                $group: {
                    _id: '$layer',
                    count: { $sum: 1 },
                    avgDuration: { $avg: '$duration' }
                }
            },
            {
                $sort: { '_id': 1 }
            }
        ]);
        
        return {
            success: true,
            period: { start: startDate, end: endDate },
            ...basicStats,
            dailyTrend: dailyTrend,
            severityBreakdown: severityStats,
            layerAnalysis: layerStats
        };
    } catch (error) {
        console.error('Monthly stats aggregation error:', error);
        return { success: false, error: error.message };
    }
}

// ========================================
// 2. 리포트 생성 함수들
// ========================================

// 주간 리포트 생성
async function generateWeeklyReport(userId, userEmail) {
    try {
        const stats = await aggregateWeeklyStats(userId);
        
        if (!stats.success) {
            throw new Error(stats.error);
        }
        
        const reportData = {
            type: 'weekly',
            userId: userId,
            userEmail: userEmail,
            generatedAt: new Date(),
            startDate: stats.period.start,
            endDate: stats.period.end,
            summary: {
                totalRequests: stats.traffic.totalRequests,
                blockedRequests: stats.traffic.blockedRequests,
                blockRate: ((stats.traffic.blockedRequests / stats.traffic.totalRequests) * 100).toFixed(2),
                uniqueIPs: stats.traffic.uniqueIPs.length,
                attacksPrevented: stats.attacks.reduce((sum, a) => sum + a.mitigated, 0),
                dataTransferred: formatBytes(stats.traffic.totalDataTransferred)
            },
            topBlockedIPs: stats.blockedIPs.slice(0, 10),
            attackBreakdown: stats.attacks,
            hourlyDistribution: stats.hourlyDistribution
        };
        
        // 리포트 히스토리 저장
        const history = new ReportHistory({
            userId: userId,
            reportType: 'weekly',
            startDate: stats.period.start,
            endDate: stats.period.end,
            stats: {
                totalRequests: reportData.summary.totalRequests,
                blockedRequests: reportData.summary.blockedRequests,
                uniqueIPs: reportData.summary.uniqueIPs,
                attacksPrevented: reportData.summary.attacksPrevented,
                blockedIPCount: stats.blockedIPs.length
            }
        });
        
        await history.save();
        
        return {
            success: true,
            reportData: reportData,
            historyId: history._id
        };
    } catch (error) {
        console.error('Weekly report generation error:', error);
        return { success: false, error: error.message };
    }
}

// 월간 리포트 생성
async function generateMonthlyReport(userId, userEmail) {
    try {
        const stats = await aggregateMonthlyStats(userId);
        
        if (!stats.success) {
            throw new Error(stats.error);
        }
        
        const reportData = {
            type: 'monthly',
            userId: userId,
            userEmail: userEmail,
            generatedAt: new Date(),
            startDate: stats.period.start,
            endDate: stats.period.end,
            summary: {
                totalRequests: stats.traffic.totalRequests,
                blockedRequests: stats.traffic.blockedRequests,
                blockRate: ((stats.traffic.blockedRequests / stats.traffic.totalRequests) * 100).toFixed(2),
                uniqueIPs: stats.traffic.uniqueIPs.length,
                attacksPrevented: stats.attacks.reduce((sum, a) => sum + a.mitigated, 0),
                dataTransferred: formatBytes(stats.traffic.totalDataTransferred)
            },
            topBlockedIPs: stats.blockedIPs.slice(0, 10),
            attackBreakdown: stats.attacks,
            dailyTrend: stats.dailyTrend,
            severityAnalysis: stats.severityBreakdown,
            layerAnalysis: stats.layerAnalysis,
            hourlyDistribution: stats.hourlyDistribution
        };
        
        // 리포트 히스토리 저장
        const history = new ReportHistory({
            userId: userId,
            reportType: 'monthly',
            startDate: stats.period.start,
            endDate: stats.period.end,
            stats: {
                totalRequests: reportData.summary.totalRequests,
                blockedRequests: reportData.summary.blockedRequests,
                uniqueIPs: reportData.summary.uniqueIPs,
                attacksPrevented: reportData.summary.attacksPrevented,
                blockedIPCount: stats.blockedIPs.length
            }
        });
        
        await history.save();
        
        return {
            success: true,
            reportData: reportData,
            historyId: history._id
        };
    } catch (error) {
        console.error('Monthly report generation error:', error);
        return { success: false, error: error.message };
    }
}

// ========================================
// 3. 유틸리티 함수들
// ========================================

// 바이트를 읽기 쉬운 형식으로 변환
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// ========================================
// Export
// ========================================
module.exports = {
    aggregateWeeklyStats,
    aggregateMonthlyStats,
    generateWeeklyReport,
    generateMonthlyReport
};
