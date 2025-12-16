/**
 * NeuralGrid Security Platform - PDF Generator
 * Puppeteer를 사용한 보안 리포트 PDF 생성
 */

const puppeteer = require('puppeteer');
const handlebars = require('handlebars');
const fs = require('fs').promises;
const path = require('path');

class PDFGenerator {
    constructor() {
        this.browser = null;
        this.templatesPath = path.join(__dirname, 'templates');
    }
    
    /**
     * Puppeteer 브라우저 초기화
     */
    async initialize() {
        if (!this.browser) {
            this.browser = await puppeteer.launch({
                headless: 'new',
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-gpu'
                ]
            });
            console.log('✅ Puppeteer browser initialized');
        }
    }
    
    /**
     * 주간 보안 리포트 PDF 생성
     * @param {Object} reportData - 리포트 데이터
     * @returns {Buffer} PDF 버퍼
     */
    async generateWeeklyReport(reportData) {
        await this.initialize();
        
        try {
            // HTML 템플릿 생성 (임베디드)
            const html = this.generateWeeklyHTML(reportData);
            
            // PDF 생성
            const page = await this.browser.newPage();
            await page.setContent(html, { 
                waitUntil: 'networkidle0',
                timeout: 30000
            });
            
            const pdfBuffer = await page.pdf({
                format: 'A4',
                printBackground: true,
                margin: {
                    top: '15mm',
                    right: '15mm',
                    bottom: '15mm',
                    left: '15mm'
                },
                displayHeaderFooter: true,
                headerTemplate: '<div></div>',
                footerTemplate: `
                    <div style="font-size: 10px; text-align: center; width: 100%; color: #64748b;">
                        <span>NeuralGrid Security Platform | Page <span class="pageNumber"></span> of <span class="totalPages"></span></span>
                    </div>
                `
            });
            
            await page.close();
            console.log(`✅ Weekly report PDF generated (${pdfBuffer.length} bytes)`);
            
            return pdfBuffer;
        } catch (error) {
            console.error('❌ Weekly report PDF generation failed:', error);
            throw error;
        }
    }
    
    /**
     * 월간 상세 보안 리포트 PDF 생성
     * @param {Object} reportData - 리포트 데이터
     * @returns {Buffer} PDF 버퍼
     */
    async generateMonthlyReport(reportData) {
        await this.initialize();
        
        try {
            const html = this.generateMonthlyHTML(reportData);
            
            const page = await this.browser.newPage();
            await page.setContent(html, { 
                waitUntil: 'networkidle0',
                timeout: 30000
            });
            
            const pdfBuffer = await page.pdf({
                format: 'A4',
                printBackground: true,
                margin: {
                    top: '15mm',
                    right: '15mm',
                    bottom: '15mm',
                    left: '15mm'
                },
                displayHeaderFooter: true,
                headerTemplate: '<div></div>',
                footerTemplate: `
                    <div style="font-size: 10px; text-align: center; width: 100%; color: #64748b;">
                        <span>NeuralGrid Security Platform - 월간 상세 리포트 | Page <span class="pageNumber"></span> of <span class="totalPages"></span></span>
                    </div>
                `
            });
            
            await page.close();
            console.log(`✅ Monthly report PDF generated (${pdfBuffer.length} bytes)`);
            
            return pdfBuffer;
        } catch (error) {
            console.error('❌ Monthly report PDF generation failed:', error);
            throw error;
        }
    }
    
    /**
     * 주간 리포트 HTML 생성
     */
    generateWeeklyHTML(data) {
        return `
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuralGrid 주간 보안 리포트</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Malgun Gothic', 'Apple SD Gothic Neo', sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background: white;
        }
        
        .container {
            padding: 40px;
        }
        
        .header {
            text-align: center;
            border-bottom: 4px solid #3b82f6;
            padding-bottom: 30px;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            margin: -40px -40px 40px -40px;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 800;
        }
        
        .header .period {
            font-size: 18px;
            opacity: 0.9;
            margin: 10px 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin: 40px 0;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #cbd5e1;
        }
        
        .stat-box h2 {
            font-size: 48px;
            color: #3b82f6;
            margin-bottom: 10px;
            font-weight: 800;
        }
        
        .stat-box p {
            font-size: 16px;
            color: #64748b;
            font-weight: 600;
        }
        
        .section {
            margin: 50px 0;
            page-break-inside: avoid;
        }
        
        .section h2 {
            font-size: 24px;
            color: #3b82f6;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3b82f6;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        
        th {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .highlight {
            background: #fef3c7;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
            color: #64748b;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-warning {
            background: #fed7aa;
            color: #92400e;
        }
        
        .badge-danger {
            background: #fecaca;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ NeuralGrid 주간 보안 리포트</h1>
            <div class="period">${data.startDate} ~ ${data.endDate}</div>
            <div class="period">사용자: ${data.userEmail}</div>
        </div>
        
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
                <p>고유 IP 수</p>
            </div>
            <div class="stat-box">
                <h2>${(data.summary.attacksPrevented || 0).toLocaleString()}</h2>
                <p>공격 방어 횟수</p>
            </div>
        </div>
        
        <div class="highlight">
            <strong>📊 차단율:</strong> ${data.summary.blockRate || '0%'} | 
            <strong>📈 데이터 전송량:</strong> ${data.summary.dataTransferred || '0 MB'}
        </div>
        
        <div class="section">
            <h2>🚫 상위 차단 IP (Top 10)</h2>
            <table>
                <thead>
                    <tr>
                        <th>순위</th>
                        <th>IP 주소</th>
                        <th>국가</th>
                        <th>차단 횟수</th>
                        <th>공격 유형</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    ${(data.topBlockedIPs || []).map((ip, index) => `
                        <tr>
                            <td>${index + 1}</td>
                            <td><strong>${ip.ip}</strong></td>
                            <td>${ip.country}</td>
                            <td>${ip.count}</td>
                            <td>${ip.attackType || 'Unknown'}</td>
                            <td><span class="badge badge-danger">차단됨</span></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>⚔️ 공격 유형별 통계</h2>
            <table>
                <thead>
                    <tr>
                        <th>공격 유형</th>
                        <th>발생 횟수</th>
                        <th>평균 지속 시간</th>
                        <th>완화 여부</th>
                    </tr>
                </thead>
                <tbody>
                    ${(data.attackBreakdown || []).map(attack => `
                        <tr>
                            <td><strong>${attack.type}</strong></td>
                            <td>${attack.count}</td>
                            <td>${attack.avgDuration ? Math.round(attack.avgDuration / 60) + '분' : 'N/A'}</td>
                            <td><span class="badge badge-success">${attack.mitigated}/${attack.count} 완화</span></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>📊 국가별 차단 통계</h2>
            <table>
                <thead>
                    <tr>
                        <th>국가</th>
                        <th>차단 IP 수</th>
                        <th>비율</th>
                    </tr>
                </thead>
                <tbody>
                    ${(data.blockedIPs || []).map(country => `
                        <tr>
                            <td><strong>${country.country}</strong></td>
                            <td>${country.count}</td>
                            <td>${country.percentage || '0%'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p><strong>© 2025 NeuralGrid Security Platform</strong></p>
            <p>문의: support@neuralgrid.kr | 전화: 02-1234-5678</p>
            <p>https://neuralgrid.kr</p>
        </div>
    </div>
</body>
</html>
        `;
    }
    
    /**
     * 월간 리포트 HTML 생성 (더 상세함)
     */
    generateMonthlyHTML(data) {
        return `
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>NeuralGrid 월간 상세 보안 리포트</title>
    <style>
        /* 주간 리포트와 동일한 스타일 + 추가 스타일 */
        @page { size: A4; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', sans-serif; line-height: 1.6; color: #1e293b; }
        .container { padding: 40px; }
        .header { text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; margin: -40px -40px 40px -40px; }
        .header h1 { font-size: 36px; margin-bottom: 15px; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin: 40px 0; }
        .stat-box { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 30px; border-radius: 12px; text-align: center; border: 2px solid #cbd5e1; }
        .stat-box h2 { font-size: 48px; color: #3b82f6; margin-bottom: 10px; }
        .section { margin: 50px 0; page-break-inside: avoid; }
        .section h2 { font-size: 24px; color: #3b82f6; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #3b82f6; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; }
        .footer { text-align: center; margin-top: 60px; padding-top: 30px; border-top: 2px solid #e2e8f0; color: #64748b; }
        .highlight { background: #fef3c7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ NeuralGrid 월간 상세 보안 리포트</h1>
            <div class="period">${data.month || '2025-12'}</div>
            <div class="period">사용자: ${data.userEmail}</div>
            <div class="period">상세 분석 리포트 (20+ 페이지)</div>
        </div>
        
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
                <p>고유 IP 수</p>
            </div>
            <div class="stat-box">
                <h2>${(data.summary.attacksPrevented || 0).toLocaleString()}</h2>
                <p>공격 방어 횟수</p>
            </div>
        </div>
        
        <div class="section">
            <h2>📈 일별 트렌드 분석</h2>
            <table>
                <thead>
                    <tr>
                        <th>날짜</th>
                        <th>총 요청</th>
                        <th>차단된 요청</th>
                        <th>차단율</th>
                    </tr>
                </thead>
                <tbody>
                    ${(data.dailyTrend || []).map(day => `
                        <tr>
                            <td>${day.date}</td>
                            <td>${day.totalRequests.toLocaleString()}</td>
                            <td>${day.blockedRequests.toLocaleString()}</td>
                            <td>${day.blockRate || '0%'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>⚠️ 심각도별 공격 분석</h2>
            <table>
                <thead>
                    <tr>
                        <th>심각도</th>
                        <th>발생 횟수</th>
                        <th>비율</th>
                    </tr>
                </thead>
                <tbody>
                    ${(data.severityAnalysis || []).map(severity => `
                        <tr>
                            <td><strong>${severity.severity}</strong></td>
                            <td>${severity.count}</td>
                            <td>${severity.percentage || '0%'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>🔍 Layer별 공격 분석</h2>
            <table>
                <thead>
                    <tr>
                        <th>Layer</th>
                        <th>공격 횟수</th>
                        <th>평균 지속 시간</th>
                        <th>완화율</th>
                    </tr>
                </thead>
                <tbody>
                    ${(data.layerAnalysis || []).map(layer => `
                        <tr>
                            <td><strong>Layer ${layer.layer}</strong></td>
                            <td>${layer.count}</td>
                            <td>${Math.round(layer.avgDuration / 60)}분</td>
                            <td>${layer.mitigationRate || '100%'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p><strong>© 2025 NeuralGrid Security Platform</strong></p>
            <p>문의: support@neuralgrid.kr</p>
        </div>
    </div>
</body>
</html>
        `;
    }
    
    /**
     * PDF 파일로 저장
     */
    async savePDF(pdfBuffer, filename) {
        try {
            const savePath = path.join('/var/lib/neuralgrid/reports', filename);
            
            // 디렉토리 생성 (없으면)
            await fs.mkdir(path.dirname(savePath), { recursive: true });
            
            await fs.writeFile(savePath, pdfBuffer);
            console.log(`✅ PDF saved: ${savePath}`);
            
            return savePath;
        } catch (error) {
            console.error('❌ PDF save failed:', error);
            throw error;
        }
    }
    
    /**
     * 브라우저 종료
     */
    async close() {
        if (this.browser) {
            await this.browser.close();
            this.browser = null;
            console.log('✅ Puppeteer browser closed');
        }
    }
}

// 싱글톤 패턴
const pdfGenerator = new PDFGenerator();

// Graceful shutdown
process.on('SIGINT', async () => {
    await pdfGenerator.close();
});

process.on('SIGTERM', async () => {
    await pdfGenerator.close();
});

module.exports = pdfGenerator;
