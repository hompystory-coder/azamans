// ========================================
// Phase 1: 데이터 수집 및 저장 시스템
// ========================================

const mongoose = require('mongoose');

// MongoDB 연결
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/neuralgrid_security', {
    useNewUrlParser: true,
    useUnifiedTopology: true
});

// ========================================
// 1. 트래픽 로그 스키마
// ========================================
const trafficLogSchema = new mongoose.Schema({
    serverId: { type: String, required: true, index: true },
    userId: { type: String, required: true, index: true },
    timestamp: { type: Date, default: Date.now, index: true },
    sourceIp: { type: String, required: true },
    destinationIp: { type: String, required: true },
    protocol: { type: String, enum: ['HTTP', 'HTTPS', 'TCP', 'UDP', 'ICMP'], default: 'HTTP' },
    requestType: { type: String }, // GET, POST, etc.
    url: { type: String },
    statusCode: { type: Number },
    blocked: { type: Boolean, default: false, index: true },
    blockReason: { type: String },
    userAgent: { type: String },
    country: { type: String, index: true },
    attackType: { type: String, enum: ['DDoS', 'SQLi', 'XSS', 'BruteForce', 'BotAttack', null] },
    requestSize: { type: Number }, // bytes
    responseSize: { type: Number }, // bytes
    responseTime: { type: Number } // ms
});

// 인덱스 최적화
trafficLogSchema.index({ userId: 1, timestamp: -1 });
trafficLogSchema.index({ serverId: 1, timestamp: -1 });
trafficLogSchema.index({ sourceIp: 1, blocked: 1 });

const TrafficLog = mongoose.model('TrafficLog', trafficLogSchema);

// ========================================
// 2. 차단된 IP 스키마
// ========================================
const blockedIpSchema = new mongoose.Schema({
    serverId: { type: String, required: true, index: true },
    userId: { type: String, required: true, index: true },
    ip: { type: String, required: true, index: true },
    country: { type: String },
    blockReason: { type: String, required: true },
    attackType: { type: String, enum: ['DDoS', 'SQLi', 'XSS', 'BruteForce', 'BotAttack', 'Manual'] },
    requestCount: { type: Number, default: 0 },
    blockedAt: { type: Date, default: Date.now, index: true },
    expiresAt: { type: Date, index: true }, // null이면 영구 차단
    status: { type: String, enum: ['active', 'expired', 'manual_unblock'], default: 'active', index: true },
    unblockedAt: { type: Date },
    unblockedBy: { type: String }
});

blockedIpSchema.index({ ip: 1, status: 1 });
blockedIpSchema.index({ userId: 1, blockedAt: -1 });

const BlockedIp = mongoose.model('BlockedIp', blockedIpSchema);

// ========================================
// 3. 공격 이벤트 스키마
// ========================================
const attackEventSchema = new mongoose.Schema({
    serverId: { type: String, required: true, index: true },
    userId: { type: String, required: true, index: true },
    attackType: { type: String, enum: ['DDoS', 'SQLi', 'XSS', 'BruteForce', 'BotAttack', 'PortScan', 'Other'], required: true },
    layer: { type: Number, enum: [3, 4, 7] }, // OSI Layer
    severity: { type: String, enum: ['low', 'medium', 'high', 'critical'], default: 'medium', index: true },
    sourceIp: { type: String, required: true },
    sourceCountry: { type: String },
    targetUrl: { type: String },
    targetPort: { type: Number },
    requestCount: { type: Number, default: 0 },
    peakRequestsPerSecond: { type: Number },
    duration: { type: Number }, // seconds
    startedAt: { type: Date, required: true, index: true },
    endedAt: { type: Date },
    mitigated: { type: Boolean, default: false },
    mitigationMethod: { type: String }, // 'IP Block', 'Rate Limit', 'Firewall Rule', etc.
    affectedUrls: [{ type: String }],
    notes: { type: String }
});

attackEventSchema.index({ userId: 1, startedAt: -1 });
attackEventSchema.index({ serverId: 1, attackType: 1 });
attackEventSchema.index({ severity: 1, mitigated: 1 });

const AttackEvent = mongoose.model('AttackEvent', attackEventSchema);

// ========================================
// 4. 리포트 스케줄 스키마
// ========================================
const reportScheduleSchema = new mongoose.Schema({
    userId: { type: String, required: true, index: true, unique: true },
    email: { type: String, required: true },
    reportTypes: [{
        type: { type: String, enum: ['weekly', 'monthly'], required: true },
        planType: { type: String, enum: ['website', 'server'], required: true },
        enabled: { type: Boolean, default: true }
    }],
    lastSent: {
        weekly: { type: Date },
        monthly: { type: Date }
    },
    nextScheduled: {
        weekly: { type: Date },
        monthly: { type: Date }
    },
    timezone: { type: String, default: 'Asia/Seoul' },
    createdAt: { type: Date, default: Date.now }
});

const ReportSchedule = mongoose.model('ReportSchedule', reportScheduleSchema);

// ========================================
// 5. 리포트 히스토리 스키마
// ========================================
const reportHistorySchema = new mongoose.Schema({
    userId: { type: String, required: true, index: true },
    reportType: { type: String, enum: ['weekly', 'monthly'], required: true },
    generatedAt: { type: Date, default: Date.now, index: true },
    startDate: { type: Date, required: true },
    endDate: { type: Date, required: true },
    stats: {
        totalRequests: { type: Number, default: 0 },
        blockedRequests: { type: Number, default: 0 },
        uniqueIPs: { type: Number, default: 0 },
        attacksPrevented: { type: Number, default: 0 },
        blockedIPCount: { type: Number, default: 0 }
    },
    pdfUrl: { type: String }, // S3 or local storage URL
    emailSent: { type: Boolean, default: false },
    emailSentAt: { type: Date },
    emailError: { type: String }
});

reportHistorySchema.index({ userId: 1, generatedAt: -1 });

const ReportHistory = mongoose.model('ReportHistory', reportHistorySchema);

// ========================================
// 6. 데이터 수집 함수들
// ========================================

// 트래픽 로그 기록
async function logTraffic(data) {
    try {
        const log = new TrafficLog({
            serverId: data.serverId,
            userId: data.userId,
            sourceIp: data.sourceIp,
            destinationIp: data.destinationIp,
            protocol: data.protocol || 'HTTP',
            requestType: data.requestType,
            url: data.url,
            statusCode: data.statusCode,
            blocked: data.blocked || false,
            blockReason: data.blockReason,
            userAgent: data.userAgent,
            country: data.country,
            attackType: data.attackType,
            requestSize: data.requestSize,
            responseSize: data.responseSize,
            responseTime: data.responseTime
        });
        
        await log.save();
        return { success: true, logId: log._id };
    } catch (error) {
        console.error('Traffic log error:', error);
        return { success: false, error: error.message };
    }
}

// IP 차단 기록
async function blockIP(data) {
    try {
        const blocked = new BlockedIp({
            serverId: data.serverId,
            userId: data.userId,
            ip: data.ip,
            country: data.country,
            blockReason: data.blockReason,
            attackType: data.attackType,
            requestCount: data.requestCount || 0,
            expiresAt: data.expiresAt || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) // 기본 7일
        });
        
        await blocked.save();
        return { success: true, blockId: blocked._id };
    } catch (error) {
        console.error('Block IP error:', error);
        return { success: false, error: error.message };
    }
}

// 공격 이벤트 기록
async function logAttackEvent(data) {
    try {
        const event = new AttackEvent({
            serverId: data.serverId,
            userId: data.userId,
            attackType: data.attackType,
            layer: data.layer,
            severity: data.severity || 'medium',
            sourceIp: data.sourceIp,
            sourceCountry: data.sourceCountry,
            targetUrl: data.targetUrl,
            targetPort: data.targetPort,
            requestCount: data.requestCount || 0,
            peakRequestsPerSecond: data.peakRequestsPerSecond,
            duration: data.duration,
            startedAt: data.startedAt || new Date(),
            endedAt: data.endedAt,
            mitigated: data.mitigated || false,
            mitigationMethod: data.mitigationMethod,
            affectedUrls: data.affectedUrls || [],
            notes: data.notes
        });
        
        await event.save();
        return { success: true, eventId: event._id };
    } catch (error) {
        console.error('Attack event log error:', error);
        return { success: false, error: error.message };
    }
}

// 리포트 스케줄 등록
async function registerReportSchedule(data) {
    try {
        const schedule = await ReportSchedule.findOneAndUpdate(
            { userId: data.userId },
            {
                email: data.email,
                reportTypes: data.reportTypes,
                timezone: data.timezone || 'Asia/Seoul',
                nextScheduled: {
                    weekly: getNextMonday(),
                    monthly: getNextFirstOfMonth()
                }
            },
            { upsert: true, new: true }
        );
        
        return { success: true, scheduleId: schedule._id };
    } catch (error) {
        console.error('Report schedule error:', error);
        return { success: false, error: error.message };
    }
}

// ========================================
// 7. 유틸리티 함수들
// ========================================

// 다음 월요일 날짜 구하기
function getNextMonday() {
    const now = new Date();
    const dayOfWeek = now.getDay();
    const daysUntilMonday = dayOfWeek === 0 ? 1 : 8 - dayOfWeek;
    const nextMonday = new Date(now.setDate(now.getDate() + daysUntilMonday));
    nextMonday.setHours(9, 0, 0, 0); // 오전 9시
    return nextMonday;
}

// 다음 달 1일 구하기
function getNextFirstOfMonth() {
    const now = new Date();
    const nextMonth = new Date(now.getFullYear(), now.getMonth() + 1, 1, 9, 0, 0, 0);
    return nextMonth;
}

// ========================================
// 8. Express 라우트 추가
// ========================================

// 트래픽 로그 API
app.post('/api/logs/traffic', async (req, res) => {
    const result = await logTraffic(req.body);
    res.json(result);
});

// IP 차단 API
app.post('/api/logs/block-ip', async (req, res) => {
    const result = await blockIP(req.body);
    res.json(result);
});

// 공격 이벤트 기록 API
app.post('/api/logs/attack-event', async (req, res) => {
    const result = await logAttackEvent(req.body);
    res.json(result);
});

// 리포트 스케줄 등록 API (사용자 신청 시 자동 호출)
app.post('/api/reports/subscribe', authMiddleware, async (req, res) => {
    const { email, reportTypes } = req.body;
    const userId = req.user.id;
    
    const result = await registerReportSchedule({
        userId,
        email,
        reportTypes
    });
    
    res.json(result);
});

// ========================================
// Export
// ========================================
module.exports = {
    TrafficLog,
    BlockedIp,
    AttackEvent,
    ReportSchedule,
    ReportHistory,
    logTraffic,
    blockIP,
    logAttackEvent,
    registerReportSchedule
};
