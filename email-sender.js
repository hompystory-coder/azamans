/**
 * NeuralGrid Security Platform - Email Sender
 * Nodemailer를 사용한 보안 리포트 이메일 발송
 */

const nodemailer = require('nodemailer');
const fs = require('fs').promises;
const path = require('path');

class EmailSender {
    constructor() {
        // SMTP 설정 (환경 변수 사용)
        this.transporter = nodemailer.createTransport({
            host: process.env.SMTP_HOST || 'smtp.gmail.com',
            port: parseInt(process.env.SMTP_PORT || '587'),
            secure: false, // TLS 사용
            auth: {
                user: process.env.SMTP_USER || 'security@neuralgrid.kr',
                pass: process.env.SMTP_PASS || 'your-app-password'
            }
        });
        
        console.log(`✅ Email sender initialized (${process.env.SMTP_USER || 'security@neuralgrid.kr'})`);
    }
    
    /**
     * SMTP 연결 테스트
     */
    async verifyConnection() {
        try {
            await this.transporter.verify();
            console.log('✅ SMTP connection verified');
            return true;
        } catch (error) {
            console.error('❌ SMTP connection failed:', error.message);
            return false;
        }
    }
    
    /**
     * 주간 보안 리포트 이메일 발송
     * @param {string} userEmail - 수신자 이메일
     * @param {Object} reportData - 리포트 데이터
     * @param {Buffer} pdfBuffer - PDF 파일 버퍼
     * @returns {Object} 발송 결과
     */
    async sendWeeklyReport(userEmail, reportData, pdfBuffer) {
        try {
            const htmlContent = this.generateWeeklyEmailHTML(reportData);
            
            const mailOptions = {
                from: {
                    name: 'NeuralGrid Security',
                    address: process.env.SMTP_USER || 'security@neuralgrid.kr'
                },
                to: userEmail,
                subject: `[NeuralGrid] 🛡️ 주간 보안 리포트 - ${reportData.startDate}`,
                html: htmlContent,
                attachments: [
                    {
                        filename: `neuralgrid-weekly-${reportData.startDate}.pdf`,
                        content: pdfBuffer,
                        contentType: 'application/pdf'
                    }
                ]
            };
            
            const info = await this.transporter.sendMail(mailOptions);
            console.log(`✅ Weekly report email sent to ${userEmail} (Message ID: ${info.messageId})`);
            
            return {
                success: true,
                messageId: info.messageId,
                response: info.response
            };
        } catch (error) {
            console.error(`❌ Weekly report email failed to ${userEmail}:`, error.message);
            
            return {
                success: false,
                error: error.message
            };
        }
    }
    
    /**
     * 월간 상세 보안 리포트 이메일 발송
     * @param {string} userEmail - 수신자 이메일
     * @param {Object} reportData - 리포트 데이터
     * @param {Buffer} pdfBuffer - PDF 파일 버퍼
     * @returns {Object} 발송 결과
     */
    async sendMonthlyReport(userEmail, reportData, pdfBuffer) {
        try {
            const htmlContent = this.generateMonthlyEmailHTML(reportData);
            
            const mailOptions = {
                from: {
                    name: 'NeuralGrid Security',
                    address: process.env.SMTP_USER || 'security@neuralgrid.kr'
                },
                to: userEmail,
                subject: `[NeuralGrid] 🛡️ 월간 상세 보안 리포트 - ${reportData.month}`,
                html: htmlContent,
                attachments: [
                    {
                        filename: `neuralgrid-monthly-${reportData.month}.pdf`,
                        content: pdfBuffer,
                        contentType: 'application/pdf'
                    }
                ]
            };
            
            const info = await this.transporter.sendMail(mailOptions);
            console.log(`✅ Monthly report email sent to ${userEmail} (Message ID: ${info.messageId})`);
            
            return {
                success: true,
                messageId: info.messageId,
                response: info.response
            };
        } catch (error) {
            console.error(`❌ Monthly report email failed to ${userEmail}:`, error.message);
            
            return {
                success: false,
                error: error.message
            };
        }
    }
    
    /**
     * 주간 리포트 이메일 HTML 생성
     */
    generateWeeklyEmailHTML(data) {
        return `
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuralGrid 주간 보안 리포트</title>
    <style>
        body {
            font-family: 'Malgun Gothic', 'Apple SD Gothic Neo', sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin: 0 0 10px 0;
            font-weight: 800;
        }
        
        .header p {
            margin: 5px 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #cbd5e1;
        }
        
        .stat-box h2 {
            font-size: 36px;
            color: #3b82f6;
            margin: 0 0 8px 0;
            font-weight: 800;
        }
        
        .stat-box p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
            font-weight: 600;
        }
        
        .highlight {
            background: #fef3c7;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
        
        .highlight strong {
            color: #92400e;
        }
        
        .section {
            margin: 30px 0;
        }
        
        .section h2 {
            font-size: 20px;
            color: #3b82f6;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3b82f6;
        }
        
        .ip-list {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .ip-item {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ip-item:last-child {
            border-bottom: none;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #fecaca;
            color: #991b1b;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        
        .footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ NeuralGrid 주간 보안 리포트</h1>
            <p>${data.startDate} ~ ${data.endDate}</p>
            <p>보호된 시스템: ${data.userEmail}</p>
        </div>
        
        <div class="content">
            <p>안녕하세요,</p>
            <p>지난 한 주간의 보안 현황을 요약해드립니다.</p>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <h2>${(data.summary.totalRequests || 0).toLocaleString()}</h2>
                    <p>총 요청 수</p>
                </div>
                <div class="stat-box">
                    <h2>${(data.summary.blockedRequests || 0).toLocaleString()}</h2>
                    <p>차단된 요청</p>
                </div>
                <div class="stat-box">
                    <h2>${(data.summary.uniqueIPs || 0).toLocaleString()}</h2>
                    <p>고유 IP</p>
                </div>
                <div class="stat-box">
                    <h2>${(data.summary.attacksPrevented || 0).toLocaleString()}</h2>
                    <p>공격 방어</p>
                </div>
            </div>
            
            <div class="highlight">
                <strong>📊 차단율:</strong> ${data.summary.blockRate || '0%'} | 
                <strong>📈 데이터 전송:</strong> ${data.summary.dataTransferred || '0 MB'}
            </div>
            
            <div class="section">
                <h2>🚫 상위 차단 IP (Top 5)</h2>
                <div class="ip-list">
                    ${(data.topBlockedIPs || []).slice(0, 5).map((ip, index) => `
                        <div class="ip-item">
                            <div>
                                <strong>${index + 1}. ${ip.ip}</strong> (${ip.country})
                                <br>
                                <small style="color: #64748b;">${ip.attackType || 'Unknown'} - ${ip.count}회 차단</small>
                            </div>
                            <span class="badge">차단됨</span>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div style="text-align: center; margin: 40px 0;">
                <p>📎 <strong>상세 분석 리포트는 첨부된 PDF 파일을 확인해주세요.</strong></p>
                <a href="https://ddos.neuralgrid.kr/mypage.html" class="cta-button">
                    📊 대시보드에서 자세히 보기
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>© 2025 NeuralGrid Security Platform</strong></p>
            <p>문의: <a href="mailto:support@neuralgrid.kr">support@neuralgrid.kr</a> | 전화: 02-1234-5678</p>
            <p><a href="https://neuralgrid.kr">https://neuralgrid.kr</a></p>
            <p style="margin-top: 15px; font-size: 12px; color: #94a3b8;">
                이 이메일은 자동으로 발송되었습니다. 수신을 원하지 않으시면 
                <a href="https://ddos.neuralgrid.kr/unsubscribe">수신 거부</a>를 클릭하세요.
            </p>
        </div>
    </div>
</body>
</html>
        `;
    }
    
    /**
     * 월간 리포트 이메일 HTML 생성
     */
    generateMonthlyEmailHTML(data) {
        return `
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuralGrid 월간 상세 보안 리포트</title>
    <style>
        /* 주간 리포트와 유사한 스타일 */
        body { font-family: 'Malgun Gothic', sans-serif; background: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { font-size: 28px; margin: 0 0 10px 0; }
        .content { padding: 30px; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 30px 0; }
        .stat-box { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 25px; border-radius: 10px; text-align: center; border: 2px solid #cbd5e1; }
        .stat-box h2 { font-size: 36px; color: #3b82f6; margin: 0 0 8px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .footer { background: #f8fafc; padding: 30px; text-align: center; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ NeuralGrid 월간 상세 보안 리포트</h1>
            <p>${data.month || '2025-12'}</p>
            <p>보호된 시스템: ${data.userEmail}</p>
        </div>
        
        <div class="content">
            <p>안녕하세요,</p>
            <p>지난 한 달간의 상세 보안 현황을 요약해드립니다.</p>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <h2>${(data.summary.totalRequests || 0).toLocaleString()}</h2>
                    <p>총 요청 수 (30일)</p>
                </div>
                <div class="stat-box">
                    <h2>${(data.summary.blockedRequests || 0).toLocaleString()}</h2>
                    <p>차단된 요청</p>
                </div>
                <div class="stat-box">
                    <h2>${(data.summary.uniqueIPs || 0).toLocaleString()}</h2>
                    <p>고유 IP</p>
                </div>
                <div class="stat-box">
                    <h2>${(data.summary.attacksPrevented || 0).toLocaleString()}</h2>
                    <p>공격 방어</p>
                </div>
            </div>
            
            <div style="text-align: center; margin: 40px 0;">
                <p>📎 <strong>20+ 페이지의 상세 분석 리포트가 첨부되어 있습니다.</strong></p>
                <p style="margin: 20px 0;">리포트 포함 내용:</p>
                <ul style="text-align: left; display: inline-block; color: #64748b;">
                    <li>일별 트렌드 분석</li>
                    <li>심각도별 공격 분석</li>
                    <li>Layer별 상세 분석</li>
                    <li>보안 권장 사항</li>
                </ul>
                <br>
                <a href="https://ddos.neuralgrid.kr/mypage.html" class="cta-button">
                    📊 대시보드에서 자세히 보기
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>© 2025 NeuralGrid Security Platform</strong></p>
            <p>문의: <a href="mailto:support@neuralgrid.kr">support@neuralgrid.kr</a></p>
        </div>
    </div>
</body>
</html>
        `;
    }
}

// 싱글톤 패턴
const emailSender = new EmailSender();

module.exports = emailSender;
