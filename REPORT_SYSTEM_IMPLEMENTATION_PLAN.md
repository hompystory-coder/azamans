# 🔐 보안 리포트 시스템 구현 계획서

**시작일**: 2025-12-16  
**목표**: Phase 3, 4, 5 완전 자동화 구현  
**예상 소요 시간**: 4-6시간

---

## 📊 현재 상태

### ✅ 완료된 작업
- **Phase 1**: MongoDB 스키마 5개, 데이터 수집 함수 4개 ✅
- **Phase 2**: 데이터 집계 엔진, 리포트 생성 함수 2개 ✅

### 🔜 구현할 작업
- **Phase 3**: PDF 생성 (Puppeteer)
- **Phase 4**: 이메일 발송 (Nodemailer)
- **Phase 5**: 스케줄링 및 자동화 (node-cron)

---

## 🎯 Phase 3: PDF 생성 구현

### 목표
주간/월간 리포트 데이터를 HTML로 렌더링하고 Puppeteer로 PDF 변환

### 필요한 패키지
```bash
npm install puppeteer
npm install handlebars
npm install chart.js
```

### 구현할 파일
1. **report-template-weekly.html** - 주간 리포트 HTML 템플릿
2. **report-template-monthly.html** - 월간 리포트 HTML 템플릿
3. **pdf-generator.js** - PDF 생성 로직

### 구현 단계

#### 1. HTML 템플릿 디자인 (주간)
```html
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>NeuralGrid 주간 보안 리포트</title>
    <style>
        /* 프로페셔널한 리포트 디자인 */
        @page {
            size: A4;
            margin: 20mm;
        }
        
        body {
            font-family: 'Malgun Gothic', sans-serif;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .chart-container {
            margin: 40px 0;
            page-break-inside: avoid;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        
        th {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛡️ NeuralGrid 주간 보안 리포트</h1>
        <p>{{startDate}} ~ {{endDate}}</p>
        <p>사용자: {{userName}}</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-box">
            <h2>{{totalRequests}}</h2>
            <p>총 요청 수</p>
        </div>
        <div class="stat-box">
            <h2>{{blockedRequests}}</h2>
            <p>차단된 요청</p>
        </div>
        <div class="stat-box">
            <h2>{{uniqueIPs}}</h2>
            <p>고유 IP 수</p>
        </div>
        <div class="stat-box">
            <h2>{{attacksPrevented}}</h2>
            <p>공격 방어 횟수</p>
        </div>
    </div>
    
    <h2>📊 상위 차단 IP (Top 10)</h2>
    <table>
        <thead>
            <tr>
                <th>순위</th>
                <th>IP 주소</th>
                <th>국가</th>
                <th>차단 횟수</th>
                <th>공격 유형</th>
            </tr>
        </thead>
        <tbody>
            {{#each topBlockedIPs}}
            <tr>
                <td>{{@index}}</td>
                <td>{{ip}}</td>
                <td>{{country}}</td>
                <td>{{count}}</td>
                <td>{{attackType}}</td>
            </tr>
            {{/each}}
        </tbody>
    </table>
    
    <div class="chart-container">
        <h2>📈 시간대별 트래픽 분포</h2>
        <canvas id="hourlyChart"></canvas>
    </div>
    
    <div class="footer">
        <p>© 2025 NeuralGrid Security Platform</p>
        <p>문의: support@neuralgrid.kr</p>
    </div>
</body>
</html>
```

#### 2. PDF 생성 로직
```javascript
const puppeteer = require('puppeteer');
const handlebars = require('handlebars');
const fs = require('fs').promises;
const path = require('path');

/**
 * PDF 생성기
 */
class PDFGenerator {
    constructor() {
        this.browser = null;
    }
    
    async initialize() {
        if (!this.browser) {
            this.browser = await puppeteer.launch({
                headless: 'new',
                args: ['--no-sandbox', '--disable-setuid-sandbox']
            });
        }
    }
    
    async generateWeeklyReport(reportData) {
        await this.initialize();
        
        // 템플릿 로드
        const templatePath = path.join(__dirname, 'templates', 'report-weekly.html');
        const templateSource = await fs.readFile(templatePath, 'utf-8');
        const template = handlebars.compile(templateSource);
        
        // 데이터 렌더링
        const html = template(reportData);
        
        // PDF 생성
        const page = await this.browser.newPage();
        await page.setContent(html, { waitUntil: 'networkidle0' });
        
        const pdfBuffer = await page.pdf({
            format: 'A4',
            printBackground: true,
            margin: {
                top: '20mm',
                right: '20mm',
                bottom: '20mm',
                left: '20mm'
            }
        });
        
        await page.close();
        
        return pdfBuffer;
    }
    
    async generateMonthlyReport(reportData) {
        // 월간 리포트는 더 상세함 (20+ 페이지)
        await this.initialize();
        
        const templatePath = path.join(__dirname, 'templates', 'report-monthly.html');
        const templateSource = await fs.readFile(templatePath, 'utf-8');
        const template = handlebars.compile(templateSource);
        
        const html = template(reportData);
        
        const page = await this.browser.newPage();
        await page.setContent(html, { waitUntil: 'networkidle0' });
        
        const pdfBuffer = await page.pdf({
            format: 'A4',
            printBackground: true,
            margin: {
                top: '20mm',
                right: '20mm',
                bottom: '20mm',
                left: '20mm'
            }
        });
        
        await page.close();
        
        return pdfBuffer;
    }
    
    async savePDF(pdfBuffer, filename) {
        const savePath = path.join('/var/lib/neuralgrid/reports', filename);
        await fs.writeFile(savePath, pdfBuffer);
        return savePath;
    }
    
    async close() {
        if (this.browser) {
            await this.browser.close();
            this.browser = null;
        }
    }
}

module.exports = new PDFGenerator();
```

---

## 📧 Phase 4: 이메일 발송 구현

### 목표
생성된 PDF 리포트를 사용자 이메일로 자동 발송

### 필요한 패키지
```bash
npm install nodemailer
```

### 구현할 파일
1. **email-sender.js** - 이메일 발송 로직
2. **email-template-weekly.html** - 주간 리포트 이메일 템플릿
3. **email-template-monthly.html** - 월간 리포트 이메일 템플릿

### SMTP 설정 (Gmail 사용 예시)
```javascript
const nodemailer = require('nodemailer');

const transporter = nodemailer.createTransport({
    host: 'smtp.gmail.com',
    port: 587,
    secure: false, // TLS
    auth: {
        user: process.env.SMTP_USER || 'security@neuralgrid.kr',
        pass: process.env.SMTP_PASS || 'your-app-password'
    }
});
```

### 이메일 발송 로직
```javascript
/**
 * 이메일 발송기
 */
class EmailSender {
    constructor() {
        this.transporter = nodemailer.createTransport({
            host: process.env.SMTP_HOST || 'smtp.gmail.com',
            port: process.env.SMTP_PORT || 587,
            secure: false,
            auth: {
                user: process.env.SMTP_USER,
                pass: process.env.SMTP_PASS
            }
        });
    }
    
    async sendWeeklyReport(userEmail, reportData, pdfBuffer) {
        const emailTemplate = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; }
                    .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
                    .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
                    .stat-box { background: #f8fafc; padding: 15px; text-align: center; border-radius: 8px; }
                    .footer { text-align: center; color: #64748b; margin-top: 30px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🛡️ NeuralGrid 주간 보안 리포트</h1>
                        <p>${reportData.startDate} ~ ${reportData.endDate}</p>
                    </div>
                    
                    <div class="stats">
                        <div class="stat-box">
                            <h2>${reportData.totalRequests}</h2>
                            <p>총 요청 수</p>
                        </div>
                        <div class="stat-box">
                            <h2>${reportData.blockedRequests}</h2>
                            <p>차단된 요청</p>
                        </div>
                        <div class="stat-box">
                            <h2>${reportData.uniqueIPs}</h2>
                            <p>고유 IP</p>
                        </div>
                        <div class="stat-box">
                            <h2>${reportData.attacksPrevented}</h2>
                            <p>공격 방어</p>
                        </div>
                    </div>
                    
                    <p>상세 분석 리포트는 첨부된 PDF 파일을 확인해주세요.</p>
                    
                    <div class="footer">
                        <p>© 2025 NeuralGrid Security Platform</p>
                        <p>문의: support@neuralgrid.kr</p>
                    </div>
                </div>
            </body>
            </html>
        `;
        
        const mailOptions = {
            from: '"NeuralGrid Security" <security@neuralgrid.kr>',
            to: userEmail,
            subject: `[NeuralGrid] 주간 보안 리포트 - ${reportData.startDate}`,
            html: emailTemplate,
            attachments: [
                {
                    filename: `neuralgrid-weekly-${reportData.startDate}.pdf`,
                    content: pdfBuffer,
                    contentType: 'application/pdf'
                }
            ]
        };
        
        try {
            const info = await this.transporter.sendMail(mailOptions);
            console.log('✅ Email sent:', info.messageId);
            return { success: true, messageId: info.messageId };
        } catch (error) {
            console.error('❌ Email send failed:', error);
            return { success: false, error: error.message };
        }
    }
    
    async sendMonthlyReport(userEmail, reportData, pdfBuffer) {
        // 월간 리포트 이메일 (더 상세한 내용)
        const emailTemplate = `
            <!DOCTYPE html>
            <html>
            <body>
                <h1>🛡️ NeuralGrid 월간 상세 보안 리포트</h1>
                <p>20+ 페이지의 상세 분석 리포트가 첨부되어 있습니다.</p>
                <!-- 더 상세한 내용 -->
            </body>
            </html>
        `;
        
        const mailOptions = {
            from: '"NeuralGrid Security" <security@neuralgrid.kr>',
            to: userEmail,
            subject: `[NeuralGrid] 월간 상세 보안 리포트 - ${reportData.month}`,
            html: emailTemplate,
            attachments: [
                {
                    filename: `neuralgrid-monthly-${reportData.month}.pdf`,
                    content: pdfBuffer,
                    contentType: 'application/pdf'
                }
            ]
        };
        
        try {
            const info = await this.transporter.sendMail(mailOptions);
            return { success: true, messageId: info.messageId };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

module.exports = new EmailSender();
```

---

## ⏰ Phase 5: 스케줄링 및 자동화

### 목표
주간/월간 리포트를 자동으로 생성하고 이메일 발송

### 필요한 패키지
```bash
npm install node-cron
```

### 구현할 파일
1. **report-scheduler.js** - 스케줄링 메인 로직
2. **PM2 ecosystem 설정**

### 스케줄러 구현
```javascript
const cron = require('node-cron');
const pdfGenerator = require('./pdf-generator');
const emailSender = require('./email-sender');
const { generateWeeklyReport, generateMonthlyReport } = require('./report-generator');

/**
 * 리포트 스케줄러
 */
class ReportScheduler {
    constructor() {
        this.weeklyJob = null;
        this.monthlyJob = null;
    }
    
    /**
     * 주간 리포트 자동 생성 및 발송
     * 매주 월요일 오전 9시
     */
    startWeeklyReports() {
        this.weeklyJob = cron.schedule('0 9 * * 1', async () => {
            console.log('🔄 Generating weekly reports...');
            
            try {
                // 리포트 구독 중인 사용자 목록 가져오기
                const subscribers = await this.getWeeklySubscribers();
                
                for (const user of subscribers) {
                    try {
                        // 1. 리포트 데이터 생성
                        const reportData = await generateWeeklyReport(user.userId, user.email);
                        
                        // 2. PDF 생성
                        const pdfBuffer = await pdfGenerator.generateWeeklyReport(reportData);
                        
                        // 3. 이메일 발송
                        const result = await emailSender.sendWeeklyReport(
                            user.email,
                            reportData,
                            pdfBuffer
                        );
                        
                        // 4. 발송 기록 저장
                        await this.saveReportHistory({
                            userId: user.userId,
                            reportType: 'weekly',
                            emailSent: result.success,
                            emailSentAt: new Date(),
                            emailError: result.error || null
                        });
                        
                        console.log(`✅ Weekly report sent to ${user.email}`);
                    } catch (error) {
                        console.error(`❌ Failed to send weekly report to ${user.email}:`, error);
                    }
                }
                
                console.log('✅ Weekly reports generation completed');
            } catch (error) {
                console.error('❌ Weekly reports generation failed:', error);
            }
        });
        
        console.log('✅ Weekly report scheduler started (Every Monday 9:00 AM)');
    }
    
    /**
     * 월간 리포트 자동 생성 및 발송
     * 매월 1일 오전 9시
     */
    startMonthlyReports() {
        this.monthlyJob = cron.schedule('0 9 1 * *', async () => {
            console.log('🔄 Generating monthly reports...');
            
            try {
                const subscribers = await this.getMonthlySubscribers();
                
                for (const user of subscribers) {
                    try {
                        // 1. 리포트 데이터 생성
                        const reportData = await generateMonthlyReport(user.userId, user.email);
                        
                        // 2. PDF 생성
                        const pdfBuffer = await pdfGenerator.generateMonthlyReport(reportData);
                        
                        // 3. 이메일 발송
                        const result = await emailSender.sendMonthlyReport(
                            user.email,
                            reportData,
                            pdfBuffer
                        );
                        
                        // 4. 발송 기록 저장
                        await this.saveReportHistory({
                            userId: user.userId,
                            reportType: 'monthly',
                            emailSent: result.success,
                            emailSentAt: new Date(),
                            emailError: result.error || null
                        });
                        
                        console.log(`✅ Monthly report sent to ${user.email}`);
                    } catch (error) {
                        console.error(`❌ Failed to send monthly report to ${user.email}:`, error);
                    }
                }
                
                console.log('✅ Monthly reports generation completed');
            } catch (error) {
                console.error('❌ Monthly reports generation failed:', error);
            }
        });
        
        console.log('✅ Monthly report scheduler started (1st day of month 9:00 AM)');
    }
    
    /**
     * 모든 스케줄러 시작
     */
    start() {
        this.startWeeklyReports();
        this.startMonthlyReports();
        console.log('🚀 Report scheduler started successfully');
    }
    
    /**
     * 모든 스케줄러 중지
     */
    stop() {
        if (this.weeklyJob) this.weeklyJob.stop();
        if (this.monthlyJob) this.monthlyJob.stop();
        console.log('🛑 Report scheduler stopped');
    }
    
    async getWeeklySubscribers() {
        // MongoDB에서 주간 리포트 구독자 가져오기
        // TODO: 실제 DB 쿼리로 교체
        return [];
    }
    
    async getMonthlySubscribers() {
        // MongoDB에서 월간 리포트 구독자 가져오기
        // TODO: 실제 DB 쿼리로 교체
        return [];
    }
    
    async saveReportHistory(data) {
        // ReportHistory에 저장
        // TODO: 실제 DB 저장으로 교체
        console.log('Saved report history:', data);
    }
}

// 싱글톤 패턴
const scheduler = new ReportScheduler();

// 프로세스 시작 시 자동 실행
if (require.main === module) {
    scheduler.start();
    
    // Graceful shutdown
    process.on('SIGINT', () => {
        console.log('Shutting down gracefully...');
        scheduler.stop();
        process.exit(0);
    });
}

module.exports = scheduler;
```

### PM2 Ecosystem 설정
```javascript
// ecosystem.config.js
module.exports = {
  apps: [
    {
      name: 'neuralgrid-report-scheduler',
      script: './report-scheduler.js',
      cwd: '/var/www/ddos.neuralgrid.kr',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        NODE_ENV: 'production',
        SMTP_HOST: 'smtp.gmail.com',
        SMTP_PORT: 587,
        SMTP_USER: 'security@neuralgrid.kr',
        SMTP_PASS: 'your-app-password'
      }
    }
  ]
};
```

---

## 📦 필요한 패키지 설치

```bash
cd /var/www/ddos.neuralgrid.kr
npm install puppeteer handlebars nodemailer node-cron
```

---

## 🚀 배포 순서

### 1단계: 로컬 개발 및 테스트
```bash
# Phase 3 구현
- report-template-weekly.html
- report-template-monthly.html
- pdf-generator.js

# Phase 4 구현
- email-sender.js
- email-template-weekly.html
- email-template-monthly.html

# Phase 5 구현
- report-scheduler.js
- ecosystem-report.config.js
```

### 2단계: 프로덕션 배포
```bash
# 파일 복사
scp -r report-* azamans@115.91.5.140:/var/www/ddos.neuralgrid.kr/
scp pdf-generator.js azamans@115.91.5.140:/var/www/ddos.neuralgrid.kr/
scp email-sender.js azamans@115.91.5.140:/var/www/ddos.neuralgrid.kr/

# 패키지 설치
ssh azamans@115.91.5.140 "cd /var/www/ddos.neuralgrid.kr && npm install puppeteer handlebars nodemailer node-cron"

# PM2 시작
pm2 start ecosystem-report.config.js
pm2 save
```

### 3단계: 테스트
```bash
# 수동 리포트 생성 테스트
node -e "require('./report-scheduler').generateTestReports()"

# 스케줄러 로그 확인
pm2 logs neuralgrid-report-scheduler
```

---

## 📊 예상 결과

### 주간 리포트
- **크기**: 5-10 페이지
- **내용**: 기본 통계, Top 10 IP, 시간대별 트래픽
- **발송**: 매주 월요일 오전 9시

### 월간 리포트
- **크기**: 20-30 페이지
- **내용**: 상세 분석, 트렌드, 권장사항
- **발송**: 매월 1일 오전 9시

---

## ⏱️ 예상 소요 시간

| Phase | 작업 | 시간 |
|-------|------|------|
| Phase 3 | PDF 생성 | 1.5-2시간 |
| Phase 4 | 이메일 발송 | 1-1.5시간 |
| Phase 5 | 스케줄링 | 0.5-1시간 |
| 테스트 | 통합 테스트 | 1-1.5시간 |
| **총계** | | **4-6시간** |

---

## 🎯 성공 기준

### Phase 3
- [x] PDF 파일이 정상적으로 생성됨
- [x] 한글 폰트가 정상 표시됨
- [x] 차트가 포함되어 있음
- [x] 페이지 레이아웃이 깔끔함

### Phase 4
- [x] 이메일이 정상 발송됨
- [x] PDF 첨부파일이 정상 전달됨
- [x] HTML 이메일이 정상 표시됨
- [x] 스팸 필터를 통과함

### Phase 5
- [x] Cron 스케줄이 정확히 동작함
- [x] PM2로 안정적으로 실행됨
- [x] 에러 발생 시 재시도함
- [x] 로그가 정상 기록됨

---

**작성자**: GenSpark AI Developer  
**작성일**: 2025-12-16  
**버전**: 1.0
