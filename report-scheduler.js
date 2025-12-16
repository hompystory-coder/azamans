/**
 * NeuralGrid Security Platform - Report Scheduler
 * node-cron을 사용한 자동 리포트 생성 및 이메일 발송
 */

const cron = require('node-cron');
const pdfGenerator = require('./pdf-generator');
const emailSender = require('./email-sender');
const { generateWeeklyReport, generateMonthlyReport } = require('./report-generator');

class ReportScheduler {
    constructor() {
        this.weeklyJob = null;
        this.monthlyJob = null;
        this.isRunning = false;
    }
    
    /**
     * 주간 리포트 자동 생성 및 발송
     * 매주 월요일 오전 9시 (KST)
     */
    startWeeklyReports() {
        // Cron 표현식: '분 시 일 월 요일'
        // '0 9 * * 1' = 매주 월요일 9시
        this.weeklyJob = cron.schedule('0 9 * * 1', async () => {
            console.log('\n========================================');
            console.log('🔄 [Weekly Report] Starting generation...');
            console.log(`⏰ Time: ${new Date().toLocaleString('ko-KR')}`);
            console.log('========================================\n');
            
            try {
                // 구독자 목록 가져오기
                const subscribers = await this.getWeeklySubscribers();
                console.log(`📋 Found ${subscribers.length} weekly subscribers`);
                
                let successCount = 0;
                let failCount = 0;
                
                for (const user of subscribers) {
                    try {
                        console.log(`\n📧 Processing: ${user.email}`);
                        
                        // 1. 리포트 데이터 생성
                        console.log('  📊 Generating report data...');
                        const reportData = await generateWeeklyReport(user.userId, user.email);
                        
                        // 2. PDF 생성
                        console.log('  📄 Generating PDF...');
                        const pdfBuffer = await pdfGenerator.generateWeeklyReport(reportData);
                        
                        // 3. 이메일 발송
                        console.log('  📨 Sending email...');
                        const result = await emailSender.sendWeeklyReport(
                            user.email,
                            reportData,
                            pdfBuffer
                        );
                        
                        // 4. 발송 기록 저장
                        await this.saveReportHistory({
                            userId: user.userId,
                            reportType: 'weekly',
                            generatedAt: new Date(),
                            startDate: reportData.startDate,
                            endDate: reportData.endDate,
                            stats: reportData.summary,
                            emailSent: result.success,
                            emailSentAt: result.success ? new Date() : null,
                            emailError: result.error || null
                        });
                        
                        if (result.success) {
                            console.log(`  ✅ Success: ${user.email}`);
                            successCount++;
                        } else {
                            console.log(`  ❌ Failed: ${user.email} - ${result.error}`);
                            failCount++;
                        }
                    } catch (error) {
                        console.error(`  ❌ Error processing ${user.email}:`, error.message);
                        failCount++;
                    }
                }
                
                console.log('\n========================================');
                console.log('✅ [Weekly Report] Generation completed');
                console.log(`   Success: ${successCount} | Failed: ${failCount}`);
                console.log('========================================\n');
            } catch (error) {
                console.error('\n❌ [Weekly Report] Generation failed:', error);
                console.error('========================================\n');
            }
        }, {
            scheduled: true,
            timezone: "Asia/Seoul"
        });
        
        console.log('✅ Weekly report scheduler started (Every Monday 9:00 AM KST)');
    }
    
    /**
     * 월간 리포트 자동 생성 및 발송
     * 매월 1일 오전 9시 (KST)
     */
    startMonthlyReports() {
        // '0 9 1 * *' = 매월 1일 9시
        this.monthlyJob = cron.schedule('0 9 1 * *', async () => {
            console.log('\n========================================');
            console.log('🔄 [Monthly Report] Starting generation...');
            console.log(`⏰ Time: ${new Date().toLocaleString('ko-KR')}`);
            console.log('========================================\n');
            
            try {
                const subscribers = await this.getMonthlySubscribers();
                console.log(`📋 Found ${subscribers.length} monthly subscribers`);
                
                let successCount = 0;
                let failCount = 0;
                
                for (const user of subscribers) {
                    try {
                        console.log(`\n📧 Processing: ${user.email}`);
                        
                        console.log('  📊 Generating monthly report data...');
                        const reportData = await generateMonthlyReport(user.userId, user.email);
                        
                        console.log('  📄 Generating PDF (20+ pages)...');
                        const pdfBuffer = await pdfGenerator.generateMonthlyReport(reportData);
                        
                        console.log('  📨 Sending email...');
                        const result = await emailSender.sendMonthlyReport(
                            user.email,
                            reportData,
                            pdfBuffer
                        );
                        
                        await this.saveReportHistory({
                            userId: user.userId,
                            reportType: 'monthly',
                            generatedAt: new Date(),
                            startDate: reportData.startDate,
                            endDate: reportData.endDate,
                            stats: reportData.summary,
                            emailSent: result.success,
                            emailSentAt: result.success ? new Date() : null,
                            emailError: result.error || null
                        });
                        
                        if (result.success) {
                            console.log(`  ✅ Success: ${user.email}`);
                            successCount++;
                        } else {
                            console.log(`  ❌ Failed: ${user.email} - ${result.error}`);
                            failCount++;
                        }
                    } catch (error) {
                        console.error(`  ❌ Error processing ${user.email}:`, error.message);
                        failCount++;
                    }
                }
                
                console.log('\n========================================');
                console.log('✅ [Monthly Report] Generation completed');
                console.log(`   Success: ${successCount} | Failed: ${failCount}`);
                console.log('========================================\n');
            } catch (error) {
                console.error('\n❌ [Monthly Report] Generation failed:', error);
                console.error('========================================\n');
            }
        }, {
            scheduled: true,
            timezone: "Asia/Seoul"
        });
        
        console.log('✅ Monthly report scheduler started (1st day of month 9:00 AM KST)');
    }
    
    /**
     * 모든 스케줄러 시작
     */
    start() {
        if (this.isRunning) {
            console.log('⚠️  Report scheduler is already running');
            return;
        }
        
        console.log('\n');
        console.log('╔════════════════════════════════════════════════════════════╗');
        console.log('║  🚀 NeuralGrid Report Scheduler Starting...              ║');
        console.log('╚════════════════════════════════════════════════════════════╝');
        console.log('');
        
        this.startWeeklyReports();
        this.startMonthlyReports();
        
        this.isRunning = true;
        
        console.log('');
        console.log('╔════════════════════════════════════════════════════════════╗');
        console.log('║  ✅ Report Scheduler Started Successfully                  ║');
        console.log('╠════════════════════════════════════════════════════════════╣');
        console.log('║  📅 Weekly Reports: Every Monday 9:00 AM KST             ║');
        console.log('║  📅 Monthly Reports: 1st day of month 9:00 AM KST        ║');
        console.log('╚════════════════════════════════════════════════════════════╝');
        console.log('');
    }
    
    /**
     * 모든 스케줄러 중지
     */
    stop() {
        console.log('\n🛑 Stopping report scheduler...');
        
        if (this.weeklyJob) {
            this.weeklyJob.stop();
            this.weeklyJob = null;
        }
        
        if (this.monthlyJob) {
            this.monthlyJob.stop();
            this.monthlyJob = null;
        }
        
        this.isRunning = false;
        console.log('✅ Report scheduler stopped');
    }
    
    /**
     * 주간 리포트 구독자 가져오기
     */
    async getWeeklySubscribers() {
        // TODO: MongoDB에서 실제 데이터 가져오기
        // 현재는 테스트용 더미 데이터
        
        // 실제 구현 예시:
        // const ReportSchedule = require('./models/ReportSchedule');
        // const schedules = await ReportSchedule.find({
        //     'reportTypes.type': 'weekly',
        //     'reportTypes.enabled': true
        // });
        // return schedules.map(s => ({
        //     userId: s.userId,
        //     email: s.email
        // }));
        
        return [
            // { userId: 'user123', email: 'user@example.com' }
        ];
    }
    
    /**
     * 월간 리포트 구독자 가져오기
     */
    async getMonthlySubscribers() {
        // TODO: MongoDB에서 실제 데이터 가져오기
        
        return [
            // { userId: 'user123', email: 'user@example.com' }
        ];
    }
    
    /**
     * 리포트 히스토리 저장
     */
    async saveReportHistory(data) {
        // TODO: MongoDB에 실제 저장
        
        // 실제 구현 예시:
        // const ReportHistory = require('./models/ReportHistory');
        // await ReportHistory.create(data);
        
        console.log('  💾 Report history saved:', {
            userId: data.userId,
            type: data.reportType,
            emailSent: data.emailSent
        });
    }
    
    /**
     * 테스트용 리포트 즉시 생성 (수동 실행)
     */
    async generateTestReports() {
        console.log('🧪 Generating test reports...\n');
        
        try {
            // 테스트 데이터
            const testUser = {
                userId: 'test-user-123',
                email: 'test@neuralgrid.kr'
            };
            
            // 주간 리포트 테스트
            console.log('📊 Generating test weekly report...');
            const weeklyData = await generateWeeklyReport(testUser.userId, testUser.email);
            const weeklyPDF = await pdfGenerator.generateWeeklyReport(weeklyData);
            await pdfGenerator.savePDF(weeklyPDF, `test-weekly-${Date.now()}.pdf`);
            console.log('✅ Test weekly report generated\n');
            
            // 월간 리포트 테스트
            console.log('📊 Generating test monthly report...');
            const monthlyData = await generateMonthlyReport(testUser.userId, testUser.email);
            const monthlyPDF = await pdfGenerator.generateMonthlyReport(monthlyData);
            await pdfGenerator.savePDF(monthlyPDF, `test-monthly-${Date.now()}.pdf`);
            console.log('✅ Test monthly report generated\n');
            
            console.log('🎉 Test reports generation completed!');
        } catch (error) {
            console.error('❌ Test reports generation failed:', error);
        }
    }
}

// 싱글톤 패턴
const scheduler = new ReportScheduler();

// 프로세스 시작 시 자동 실행
if (require.main === module) {
    scheduler.start();
    
    // Graceful shutdown
    process.on('SIGINT', async () => {
        console.log('\n\n🛑 Received SIGINT signal...');
        scheduler.stop();
        await pdfGenerator.close();
        console.log('✅ Graceful shutdown completed');
        process.exit(0);
    });
    
    process.on('SIGTERM', async () => {
        console.log('\n\n🛑 Received SIGTERM signal...');
        scheduler.stop();
        await pdfGenerator.close();
        console.log('✅ Graceful shutdown completed');
        process.exit(0);
    });
    
    // Keep the process running
    process.on('uncaughtException', (error) => {
        console.error('❌ Uncaught Exception:', error);
        // 프로세스를 종료하지 않고 계속 실행
    });
    
    process.on('unhandledRejection', (reason, promise) => {
        console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
        // 프로세스를 종료하지 않고 계속 실행
    });
}

module.exports = scheduler;
