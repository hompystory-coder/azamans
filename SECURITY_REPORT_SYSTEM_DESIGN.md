# 🔐 보안 리포트 자동 생성 및 이메일 발송 시스템

**작성일**: 2025-12-16  
**버전**: 1.0  
**상태**: 설계 단계

---

## 📋 개요

NeuralGrid DDoS 보안 플랫폼의 보안 리포트 자동 생성 및 이메일 발송 시스템 설계 문서입니다.

### 목표
- ✅ 주간/월간 보안 리포트 자동 생성
- ✅ 사용자 이메일로 자동 발송
- ✅ PDF 형식의 상세 분석 리포트 제공
- ✅ 실시간 대시보드 데이터 기반 분석

---

## 🎯 리포트 유형

### 1. 홈페이지 보호 플랜 (₩330,000/년)
- **월간 보안 리포트**: 매월 1일 자동 발송
- **형식**: 이메일 (HTML) + PDF 첨부
- **내용**:
  - 월간 트래픽 통계
  - 차단된 공격 유형 및 횟수
  - 상위 차단 IP 목록 (Top 10)
  - 도메인별 보안 현황
  - 이상 트래픽 탐지 알림

### 2. 서버 보호 플랜 (₩2,990,000/년)
- **주간 보안 리포트**: 매주 월요일 자동 발송
- **월간 상세 분석 리포트**: 매월 1일 자동 발송
- **형식**: 이메일 (HTML) + PDF 첨부
- **내용**:
  - 주간/월간 트래픽 통계
  - Layer 3/4/7 공격 분석
  - 차단된 공격 패턴 상세 분석
  - 서버별 보안 현황 (최대 5대)
  - GeoIP 차단 통계
  - 보안 권장 사항
  - 시간대별 공격 트렌드

---

## 🏗️ 시스템 아키텍처

### 구성 요소

```
┌─────────────────────────────────────────────────────────┐
│                  DDoS Security System                    │
├─────────────────────────────────────────────────────────┤
│  1. 데이터 수집 (실시간)                                  │
│     - 트래픽 로그                                         │
│     - 차단 이벤트                                         │
│     - 서버 상태                                          │
│     - 공격 패턴                                          │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│  2. 데이터 저장 (MongoDB / PostgreSQL)                   │
│     - servers: 서버 정보                                 │
│     - traffic_logs: 트래픽 로그                          │
│     - blocked_ips: 차단된 IP                             │
│     - attack_events: 공격 이벤트                         │
│     - report_schedules: 리포트 스케줄                    │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│  3. 리포트 생성 엔진 (Node.js Cron Job)                  │
│     - node-cron: 스케줄링                                │
│     - report-generator.js: 리포트 생성                   │
│     - pdf-generator.js: PDF 변환                         │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│  4. 이메일 발송 시스템 (Nodemailer)                      │
│     - SMTP 서버 연동                                     │
│     - HTML 템플릿                                        │
│     - PDF 첨부                                           │
└─────────────────────────────────────────────────────────┘
```

---

## 💾 데이터 모델

### traffic_logs (트래픽 로그)
```javascript
{
  _id: ObjectId,
  serverId: "srv_abc123",
  userId: "user123",
  timestamp: ISODate("2025-12-16T06:00:00Z"),
  sourceIp: "1.2.3.4",
  destinationIp: "115.91.5.140",
  protocol: "HTTP",
  requestType: "GET",
  url: "/api/endpoint",
  statusCode: 200,
  blocked: false,
  blockReason: null,
  userAgent: "Mozilla/5.0...",
  country: "KR",
  attackType: null // "DDoS", "SQLi", "XSS", "BruteForce", etc.
}
```

### blocked_ips (차단된 IP)
```javascript
{
  _id: ObjectId,
  serverId: "srv_abc123",
  userId: "user123",
  ip: "1.2.3.4",
  country: "CN",
  blockReason: "DDoS Attack",
  attackType: "Layer 7 Flood",
  requestCount: 10000,
  blockedAt: ISODate("2025-12-16T05:30:00Z"),
  expiresAt: ISODate("2025-12-23T05:30:00Z"), // 7일 후 자동 해제
  status: "active" // "active", "expired", "manual_unblock"
}
```

### attack_events (공격 이벤트)
```javascript
{
  _id: ObjectId,
  serverId: "srv_abc123",
  userId: "user123",
  attackType: "DDoS",
  layer: 7,
  severity: "high", // "low", "medium", "high", "critical"
  sourceIp: "1.2.3.4",
  targetUrl: "/api/login",
  requestCount: 50000,
  duration: 3600, // seconds
  startedAt: ISODate("2025-12-16T05:00:00Z"),
  endedAt: ISODate("2025-12-16T06:00:00Z"),
  mitigated: true,
  mitigationMethod: "IP Block"
}
```

### report_schedules (리포트 스케줄)
```javascript
{
  _id: ObjectId,
  userId: "user123",
  reportType: "weekly", // "weekly", "monthly"
  planType: "server", // "website", "server"
  email: "user@example.com",
  lastSent: ISODate("2025-12-09T00:00:00Z"),
  nextScheduled: ISODate("2025-12-16T00:00:00Z"),
  enabled: true
}
```

---

## 📊 리포트 생성 로직

### 1. 데이터 집계 (Aggregation)

**주간 리포트** (지난 7일):
```javascript
const weeklyStats = await TrafficLog.aggregate([
  {
    $match: {
      userId: "user123",
      timestamp: { 
        $gte: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000) 
      }
    }
  },
  {
    $group: {
      _id: null,
      totalRequests: { $sum: 1 },
      blockedRequests: { 
        $sum: { $cond: ["$blocked", 1, 0] } 
      },
      uniqueIPs: { $addToSet: "$sourceIp" }
    }
  }
]);
```

**월간 리포트** (지난 30일):
```javascript
const monthlyStats = await AttackEvent.aggregate([
  {
    $match: {
      userId: "user123",
      startedAt: { 
        $gte: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000) 
      }
    }
  },
  {
    $group: {
      _id: "$attackType",
      count: { $sum: 1 },
      totalDuration: { $sum: "$duration" },
      avgSeverity: { $avg: "$severity" }
    }
  },
  {
    $sort: { count: -1 }
  }
]);
```

### 2. PDF 생성 (puppeteer 사용)

```javascript
const puppeteer = require('puppeteer');

async function generatePDF(reportData) {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();
  
  // HTML 템플릿 렌더링
  const html = renderReportTemplate(reportData);
  await page.setContent(html);
  
  // PDF 생성
  const pdf = await page.pdf({
    format: 'A4',
    printBackground: true,
    margin: { top: '1cm', right: '1cm', bottom: '1cm', left: '1cm' }
  });
  
  await browser.close();
  return pdf;
}
```

### 3. 이메일 발송 (Nodemailer)

```javascript
const nodemailer = require('nodemailer');

const transporter = nodemailer.createTransport({
  host: 'smtp.gmail.com',
  port: 587,
  secure: false,
  auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS
  }
});

async function sendReport(email, reportData, pdfBuffer) {
  await transporter.sendMail({
    from: '"NeuralGrid Security" <security@neuralgrid.kr>',
    to: email,
    subject: `[NeuralGrid] ${reportData.type} 보안 리포트 - ${reportData.date}`,
    html: renderEmailTemplate(reportData),
    attachments: [
      {
        filename: `neuralgrid-report-${reportData.date}.pdf`,
        content: pdfBuffer
      }
    ]
  });
}
```

---

## ⏰ 스케줄링 (node-cron)

### 설정 파일: `/var/www/ddos.neuralgrid.kr/report-scheduler.js`

```javascript
const cron = require('node-cron');
const { generateWeeklyReport, generateMonthlyReport } = require('./report-generator');

// 주간 리포트: 매주 월요일 오전 9시
cron.schedule('0 9 * * 1', async () => {
  console.log('Generating weekly reports...');
  await generateWeeklyReport();
});

// 월간 리포트: 매월 1일 오전 9시
cron.schedule('0 9 1 * *', async () => {
  console.log('Generating monthly reports...');
  await generateMonthlyReport();
});

// 실시간 공격 알림: 매 5분마다 체크
cron.schedule('*/5 * * * *', async () => {
  await checkForCriticalAttacks();
});
```

---

## 📧 이메일 템플릿

### 주간 리포트 (HTML)

```html
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
    .container { max-width: 600px; margin: 20px auto; background: white; padding: 30px; }
    .header { text-align: center; border-bottom: 3px solid #3b82f6; padding-bottom: 20px; }
    .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 30px 0; }
    .stat-box { background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center; }
    .stat-value { font-size: 36px; font-weight: bold; color: #3b82f6; }
    .stat-label { color: #64748b; margin-top: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🛡️ NeuralGrid 주간 보안 리포트</h1>
      <p>{{startDate}} ~ {{endDate}}</p>
    </div>
    
    <div class="stats">
      <div class="stat-box">
        <div class="stat-value">{{totalRequests}}</div>
        <div class="stat-label">총 요청 수</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">{{blockedRequests}}</div>
        <div class="stat-label">차단된 요청</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">{{blockedIPs}}</div>
        <div class="stat-label">차단된 IP</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">{{attackPrevented}}</div>
        <div class="stat-label">공격 방어 횟수</div>
      </div>
    </div>
    
    <h3>📊 상위 차단 IP (Top 5)</h3>
    <table>
      <!-- IP 목록 -->
    </table>
    
    <p>상세 분석 리포트는 첨부된 PDF 파일을 확인해주세요.</p>
  </div>
</body>
</html>
```

---

## 🔧 구현 단계

### Phase 1: 데이터 수집 및 저장 (2-3일)
- [ ] MongoDB 스키마 설계 및 생성
- [ ] 트래픽 로그 수집 로직 구현
- [ ] 공격 이벤트 감지 및 저장
- [ ] 테스트 데이터 생성

### Phase 2: 리포트 생성 엔진 (3-4일)
- [ ] 데이터 집계 쿼리 작성
- [ ] 통계 계산 로직 구현
- [ ] HTML 템플릿 디자인
- [ ] PDF 생성 로직 (puppeteer)
- [ ] 차트/그래프 생성 (Chart.js)

### Phase 3: 이메일 발송 시스템 (2일)
- [ ] SMTP 서버 설정
- [ ] Nodemailer 설정
- [ ] 이메일 템플릿 작성
- [ ] 발송 로직 구현
- [ ] 발송 실패 재시도 로직

### Phase 4: 스케줄링 및 자동화 (1-2일)
- [ ] node-cron 설정
- [ ] 주간 리포트 스케줄
- [ ] 월간 리포트 스케줄
- [ ] PM2로 백그라운드 실행
- [ ] 로그 모니터링

### Phase 5: 테스트 및 배포 (2일)
- [ ] 단위 테스트
- [ ] 통합 테스트
- [ ] 실제 사용자 테스트
- [ ] 프로덕션 배포

**예상 총 소요 시간**: 10-12일

---

## 💰 필요 리소스

### 서버
- **MongoDB**: 로그 데이터 저장 (최소 10GB)
- **SMTP 서버**: 이메일 발송
- **추가 메모리**: 리포트 생성 시 최소 2GB RAM

### 라이브러리
```json
{
  "dependencies": {
    "node-cron": "^3.0.3",
    "nodemailer": "^6.9.7",
    "puppeteer": "^21.6.1",
    "chart.js": "^4.4.1",
    "mongoose": "^8.0.3",
    "handlebars": "^4.7.8",
    "moment": "^2.30.1"
  }
}
```

### 비용
- **SMTP 서비스** (SendGrid, AWS SES): 월 $10-50
- **추가 서버 리소스**: 월 $20-30
- **총 예상 비용**: 월 $30-80

---

## 🎯 리포트 내용 상세

### 홈페이지 보호 - 월간 보안 리포트

1. **요약 (Summary)**
   - 총 트래픽: 1,234,567 요청
   - 차단된 요청: 5,678 (0.46%)
   - 방어한 공격: 12건
   - 보안 등급: A+

2. **트래픽 분석**
   - 일별 트래픽 그래프
   - 시간대별 트래픽 분포
   - 도메인별 트래픽

3. **위협 분석**
   - 차단된 공격 유형별 통계
   - 상위 차단 IP (국가별)
   - 의심스러운 User-Agent 목록

4. **권장 사항**
   - 보안 강화 제안
   - 설정 최적화 권장

---

### 서버 보호 - 주간 보안 리포트

1. **주간 요약**
   - 7일간 총 트래픽
   - Layer 3/4/7 공격 통계
   - 서버별 상태

2. **상세 분석**
   - 공격 패턴 분석
   - GeoIP 차단 통계
   - 프로토콜별 트래픽

3. **서버별 현황** (최대 5대)
   - Server 1: IP, 상태, 트래픽
   - Server 2: IP, 상태, 트래픽
   - ...

---

### 서버 보호 - 월간 상세 분석 리포트 (PDF)

1. **Executive Summary** (경영진용 요약)
   - 한눈에 보는 보안 현황
   - 주요 지표 (KPI)
   - 월간 비교

2. **기술 분석** (Technical Analysis)
   - Layer별 공격 상세 분석
   - 취약점 분석
   - 보안 이벤트 타임라인

3. **통계 및 차트**
   - 20+ 차트 및 그래프
   - 트렌드 분석
   - 예측 분석

4. **권장 조치**
   - 즉시 조치 필요 항목
   - 중기 보안 개선 계획
   - 장기 보안 로드맵

---

## 🔐 보안 고려사항

### 데이터 보호
- [ ] 로그 데이터 암호화 (AES-256)
- [ ] 개인정보 마스킹 (IP 마지막 옥텟)
- [ ] 리포트 PDF 암호화 (선택사항)

### 이메일 보안
- [ ] DKIM, SPF, DMARC 설정
- [ ] TLS 암호화 전송
- [ ] 스팸 필터 회피 전략

### 접근 제어
- [ ] 리포트 다운로드 인증 필요
- [ ] 히스토리 열람 권한 관리
- [ ] 관리자 리포트 별도 생성

---

## 🎉 예상 효과

### 사용자 경험
- ✅ 자동화된 보안 리포트로 시간 절약
- ✅ 시각화된 데이터로 이해도 향상
- ✅ 이메일 자동 발송으로 편의성 증대

### 비즈니스 가치
- ✅ 프리미엄 서비스 차별화
- ✅ 고객 신뢰도 향상
- ✅ 추가 매출 기회 (리포트 커스터마이징)

### 기술적 장점
- ✅ 확장 가능한 아키텍처
- ✅ 모듈화된 시스템
- ✅ 재사용 가능한 컴포넌트

---

**문서 작성자**: GenSpark AI Developer  
**최종 업데이트**: 2025-12-16  
**버전**: 1.0
