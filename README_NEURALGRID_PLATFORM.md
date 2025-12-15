# 🚀 NeuralGrid Platform - 차세대 AI 통합 플랫폼

<div align="center">

![NeuralGrid](https://img.shields.io/badge/NeuralGrid-Platform-6366f1?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDJMMiA3TDEyIDEyTDIyIDdMMTIgMloiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CjxwYXRoIGQ9Ik0yIDEyTDEyIDE3TDIyIDEyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8cGF0aCBkPSJNMiAxN0wxMiAyMkwyMiAxNyIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+)
![Status](https://img.shields.io/badge/status-live-success?style=for-the-badge)
![Uptime](https://img.shields.io/badge/uptime-99.9%25-success?style=for-the-badge)
![Services](https://img.shields.io/badge/services-8-blue?style=for-the-badge)

**한 번의 회원가입으로 모든 AI 서비스를 통합 이용하세요**

[🌐 홈페이지](https://neuralgrid.kr) · [📚 문서](https://github.com/hompystory-coder/azamans) · [🐛 이슈](https://github.com/hompystory-coder/azamans/issues) · [📧 문의](mailto:admin@neuralgrid.kr)

</div>

---

## 📖 목차

- [플랫폼 소개](#-플랫폼-소개)
- [주요 기능](#-주요-기능)
- [서비스 목록](#-서비스-목록)
- [시작하기](#-시작하기)
- [기술 스택](#-기술-스택)
- [인프라 구조](#-인프라-구조)
- [사용 방법](#-사용-방법)
- [API 문서](#-api-문서)
- [배포 가이드](#-배포-가이드)
- [기여하기](#-기여하기)
- [라이선스](#-라이선스)

---

## 🎯 플랫폼 소개

**NeuralGrid**는 차세대 AI 통합 플랫폼으로, 다양한 AI 서비스를 하나의 계정으로 통합 관리할 수 있는 올인원 솔루션입니다.

### 💡 핵심 가치

```
✨ 통합 SSO 인증 시스템
🚀 AI 기반 자동화 서비스
🎨 직관적인 사용자 경험
🔐 엔터프라이즈급 보안
💰 경쟁력 있는 가격 정책
```

### 🎯 Target Audience

- **콘텐츠 크리에이터** - 빠른 영상/음악 제작
- **마케터** - 자동화된 SNS 콘텐츠 생성
- **개발자** - API 기반 워크플로우 자동화
- **비즈니스 오너** - 이커머스 및 자동화 솔루션
- **시스템 관리자** - 실시간 모니터링 및 관리

---

## ✨ 주요 기능

### 🔐 통합 SSO 인증 시스템

```javascript
// 한 번의 회원가입으로 모든 서비스 이용
const services = [
  'MediaFX Shorts',      // AI 비디오 생성
  'NeuronStar Music',    // AI 음악 생성
  '블로그 쇼츠생성기',     // 기사→영상 변환
  '쿠팡쇼츠 마켓',         // YouTube×쿠팡 연동
  'N8N Automation',      // 워크플로우 자동화
  'System Monitor'       // 실시간 모니터링
];

// 단일 인증으로 모든 서비스 접근
authenticate() => accessAllServices();
```

### 🎬 AI 콘텐츠 생성

- **비디오 생성**: 텍스트 → 고품질 Shorts 영상 (4-5분)
- **음악 생성**: 프롬프트 → 오리지널 음악 (30-60초)
- **블로그 변환**: 기사 URL → 자동 영상화
- **상품 연동**: YouTube Shorts + 쿠팡 파트너스

### 🔄 자동화 엔진

- **200+ 앱 통합** via N8N
- **드래그 앤 드롭** 워크플로우 빌더
- **Cron 스케줄링** 지원
- **REST API** 자동화

### 📊 실시간 모니터링

- **CPU/Memory** 실시간 추적
- **PM2 프로세스** 관리
- **Disk Usage** 모니터링
- **Auto-alerts** (Slack/Email)

---

## 🌐 서비스 목록

### 메인 허브

| 서비스 | URL | 설명 | 상태 |
|--------|-----|------|------|
| **NeuralGrid** | [neuralgrid.kr](https://neuralgrid.kr) | 메인 플랫폼 허브 & SSO | 🟢 LIVE |

### AI 서비스

#### 1️⃣ 블로그 기사 쇼츠생성기
**🔗 [bn-shop.neuralgrid.kr](https://bn-shop.neuralgrid.kr)**

```yaml
Description: 블로그 기사를 AI가 자동으로 Shorts 영상으로 변환
Technology:
  - AI: Gemini 2.0 Flash
  - Image: Pollinations.AI
  - Video: Kling v2.1 Pro
Features:
  - 자동 콘텐츠 분석
  - 한글 자막 렌더링
  - 4-5분 빠른 처리
Cost: $0.06 per video
Status: 🟢 Online
```

#### 2️⃣ 쇼츠 영상 자동화 (MediaFX Shorts)
**🔗 [mfx.neuralgrid.kr](https://mfx.neuralgrid.kr)**

```yaml
Description: AI 기반 숏폼 비디오 자동 생성
Features:
  - 텍스트 → 비디오 변환
  - 다양한 템플릿
  - AI 음악 매칭
  - SNS 최적화
  - 배치 프로세싱
Status: 🟢 Online
```

#### 3️⃣ 스타뮤직 (NeuronStar Music)
**🔗 [music.neuralgrid.kr](https://music.neuralgrid.kr)**

```yaml
Description: 무료 AI 음악 생성 플랫폼
Features:
  - 다양한 장르 지원
  - 커스텀 가사 입력
  - 고품질 오디오
  - 상업적 이용 가능
  - 무제한 생성
Pricing: 완전 무료
Status: 🟢 Online
```

#### 4️⃣ 쿠팡쇼츠 (Shorts Market)
**🔗 [market.neuralgrid.kr](https://market.neuralgrid.kr)**

```yaml
Description: YouTube Shorts × 쿠팡 파트너스 연동 커머스
Features:
  - YouTube Shorts 자동 수집
  - 쿠팡 딥링크 생성
  - 네이버 브랜드 커넥트
  - 크리에이터 수익 대시보드
  - 자동 상품 매칭
Status: 🟢 Online
```

### 자동화 & 관리

#### 5️⃣ N8N 워크플로우 자동화
**🔗 [n8n.neuralgrid.kr](https://n8n.neuralgrid.kr)**

```yaml
Description: 200+ 앱 통합 워크플로우 자동화 엔진
Features:
  - 드래그 앤 드롭 빌더
  - REST API 자동화
  - Cron 스케줄링
  - 에러 핸들링
Pricing: 무료 (Self-hosted)
Status: 🟢 Online
```

#### 6️⃣ 시스템 모니터
**🔗 [monitor.neuralgrid.kr](https://monitor.neuralgrid.kr)**

```yaml
Description: 실시간 서버 상태 모니터링 대시보드
Features:
  - CPU/메모리 추적
  - PM2 프로세스 관리
  - 디스크 사용량
  - 자동 알림
  - 30초 새로고침
Pricing: 무료
Status: 🟢 Online
```

#### 7️⃣ 통합 인증 서비스
**🔗 [auth.neuralgrid.kr](https://auth.neuralgrid.kr)**

```yaml
Description: JWT 기반 중앙 집중식 SSO 인증 시스템
Features:
  - JWT 토큰 관리
  - API 키 발급
  - 크레딧 추적
  - 세션 관리
  - 권한 제어
Status: 🟢 Online
```

### 추가 서비스

#### 8️⃣ Ollama AI
**🔗 [ollama.neuralgrid.kr](https://ollama.neuralgrid.kr)**

```yaml
Description: LLM API 서비스
Port: 11434
Status: 🟢 Online
```

---

## 🚀 시작하기

### 빠른 시작 (3분 완성)

#### 1️⃣ 회원가입

```bash
# 1. 메인 페이지 방문
https://neuralgrid.kr

# 2. "무료 회원가입하기" 클릭

# 3. 정보 입력
사용자 이름: 홍길동
이메일: your@email.com
비밀번호: ******** (8자 이상)
비밀번호 확인: ********

# 4. "계속하기" 클릭
# ✅ 모든 서비스 즉시 이용 가능!
```

#### 2️⃣ 서비스 이용

```bash
# 이미 로그인 상태이므로 원하는 서비스 바로 접속
https://mfx.neuralgrid.kr      # AI 비디오 생성
https://music.neuralgrid.kr    # AI 음악 생성
https://market.neuralgrid.kr   # 쇼츠 커머스
```

### API 사용 (개발자용)

```javascript
// 1. 인증 토큰 발급
const response = await fetch('https://auth.neuralgrid.kr/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'your@email.com',
    password: 'your-password'
  })
});

const { token } = await response.json();

// 2. API 호출 (예: 비디오 생성)
const videoResponse = await fetch('https://mfx.neuralgrid.kr/api/generate', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    prompt: '도쿄 야경 감성 브이로그',
    style: 'cinematic'
  })
});
```

---

## 🛠️ 기술 스택

### Frontend

```yaml
Core:
  - HTML5/CSS3
  - Vanilla JavaScript
  - Chart.js (데이터 시각화)

Design:
  - Glassmorphism
  - Responsive Layout
  - Modern UI/UX
```

### Backend

```yaml
Runtime:
  - Node.js 20.19.6
  - Express.js

AI/ML:
  - Gemini 2.0 Flash
  - Pollinations.AI
  - Kling v2.1 Pro
  - Custom Music AI

Automation:
  - N8N (Self-hosted)
  - PM2 Process Manager
```

### Infrastructure

```yaml
Server:
  - Ubuntu Server
  - Nginx 1.24.0
  - PM2

SSL:
  - Let's Encrypt
  - Auto-renewal

Database:
  - SQLite (Auth)
  - Cloudflare D1 (Market)
```

---

## 🏗️ 인프라 구조

### Architecture Diagram

```
                    ┌─────────────────────────┐
                    │   neuralgrid.kr         │
                    │   (Main Platform)       │
                    └───────────┬─────────────┘
                                │
                                │ SSO Auth
                                │
        ┌───────────────────────┼───────────────────────┐
        │                       │                       │
        ▼                       ▼                       ▼
┌───────────────┐      ┌───────────────┐      ┌───────────────┐
│  AI Services  │      │  Automation   │      │  Management   │
├───────────────┤      ├───────────────┤      ├───────────────┤
│ • mfx         │      │ • n8n         │      │ • monitor     │
│ • music       │      │ • workflows   │      │ • auth        │
│ • bn-shop     │      │ • scheduling  │      │ • api-gateway │
│ • market      │      └───────────────┘      └───────────────┘
└───────────────┘
```

### Server Components

```
Server: 115.91.5.140
├── Nginx (Port 80/443)
│   ├── neuralgrid.kr → localhost:3200
│   ├── mfx.neuralgrid.kr → localhost:3101
│   ├── music.neuralgrid.kr → localhost:3002
│   ├── bn-shop.neuralgrid.kr → localhost:3001
│   ├── market.neuralgrid.kr → localhost:3003
│   ├── n8n.neuralgrid.kr → localhost:5692
│   ├── monitor.neuralgrid.kr → localhost:5001
│   ├── auth.neuralgrid.kr → localhost:3099
│   └── ollama.neuralgrid.kr → localhost:11434
│
├── PM2 Processes (8 services)
│   ├── api-gateway (Port 4000)
│   ├── auth-service (Port 3099)
│   ├── main-dashboard (Port 3200)
│   ├── monitor-server (Port 5001)
│   ├── mfx-shorts (Port 3101)
│   ├── neuronstar-music (Port 3002)
│   ├── youtube-shorts-generator (Port 3001)
│   └── shorts-market (Port 3003)
│
└── SSL Certificates (Let's Encrypt)
    └── Auto-renewal enabled
```

---

## 📚 사용 방법

### 1️⃣ AI 비디오 생성 (MediaFX Shorts)

```javascript
// Step-by-step guide
const steps = {
  1: '사이트 접속: https://mfx.neuralgrid.kr',
  2: 'SSO 자동 로그인 확인',
  3: '새 쇼츠 만들기 클릭',
  4: '텍스트 프롬프트 입력',
  5: '스타일 및 음악 선택',
  6: '생성하기 클릭 (3-4분 소요)',
  7: '완성된 영상 다운로드 또는 SNS 업로드'
};
```

### 2️⃣ AI 음악 생성 (NeuronStar Music)

```javascript
const musicGeneration = {
  url: 'https://music.neuralgrid.kr',
  steps: [
    '새 음악 만들기',
    '장르 선택 (Pop, Rock, Jazz, EDM...)',
    '분위기 설정 (감성적, 신나는, 차분한...)',
    '(선택) 가사 입력',
    '생성하기 (30-60초)',
    '미리듣기 후 다운로드 (MP3/WAV)'
  ],
  cost: 'Free Forever'
};
```

### 3️⃣ 블로그→쇼츠 변환

```javascript
const blogToShorts = {
  url: 'https://bn-shop.neuralgrid.kr',
  process: {
    1: '블로그 URL 입력',
    2: 'AI 자동 분석 시작',
    3: '스토리보드 생성 (Gemini 2.0)',
    4: '이미지 생성 (Pollinations.AI)',
    5: '비디오 렌더링 (Kling v2.1)',
    6: '한글 자막 자동 추가',
    7: '4-5분 후 완성 ($0.06)'
  }
};
```

### 4️⃣ 워크플로우 자동화 (N8N)

```javascript
// Example: Auto-post to social media
const workflow = {
  trigger: 'Schedule (Every day 9AM)',
  actions: [
    'Fetch content from RSS feed',
    'Generate image with AI',
    'Post to Twitter',
    'Post to Instagram',
    'Send notification to Slack'
  ],
  setup: 'Drag & Drop in N8N Editor'
};
```

---

## 🔌 API 문서

### Auth API

#### POST /api/auth/register
회원가입

```json
Request:
{
  "username": "홍길동",
  "email": "hong@example.com",
  "password": "securepass123"
}

Response:
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "username": "홍길동",
    "email": "hong@example.com"
  }
}
```

#### POST /api/auth/login
로그인

```json
Request:
{
  "email": "hong@example.com",
  "password": "securepass123"
}

Response:
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

#### GET /api/auth/profile
사용자 프로필 조회

```bash
Headers:
  Authorization: Bearer {token}

Response:
{
  "success": true,
  "user": {
    "id": 1,
    "username": "홍길동",
    "email": "hong@example.com",
    "credits": 1000
  }
}
```

### Service Status API

#### GET /api/services/status
전체 서비스 상태 조회

```json
Response:
{
  "success": true,
  "data": [
    {
      "name": "MediaFX Shorts",
      "status": "online",
      "port": 3101,
      "memory": "61.9 MB",
      "uptime": "27h"
    },
    // ... more services
  ]
}
```

---

## 🚢 배포 가이드

### Prerequisites

```bash
# Required Software
- Node.js v20.19.6+
- npm 10.8.2+
- PM2 (npm install -g pm2)
- Nginx
- Certbot (for SSL)
```

### Installation

```bash
# 1. Clone repository
git clone https://github.com/hompystory-coder/azamans.git
cd azamans

# 2. Install dependencies
npm install

# 3. Configure environment variables
cp .env.example .env
nano .env

# 4. Start all services
pm2 start ecosystem.config.js

# 5. Setup Nginx
sudo cp nginx-configs/* /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/* /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# 6. Generate SSL certificates
sudo certbot --nginx -d neuralgrid.kr -d *.neuralgrid.kr
```

### PM2 Management

```bash
# List all processes
pm2 list

# View logs
pm2 logs

# Restart specific service
pm2 restart [service-name]

# Monitor in real-time
pm2 monit

# Save PM2 state
pm2 save

# Setup auto-startup
pm2 startup
```

---

## 🤝 기여하기

### Contribution Guidelines

```markdown
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'feat: Add AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request
```

### Commit Convention

```
feat: 새로운 기능 추가
fix: 버그 수정
docs: 문서 수정
style: 코드 포맷팅, 세미콜론 누락 등
refactor: 코드 리팩토링
test: 테스트 코드 추가
chore: 빌드 업무 수정, 패키지 매니저 수정 등
```

---

## 📄 라이선스

Copyright © 2025 NeuralGrid. All rights reserved.

---

## 📞 연락처 & 지원

### 공식 채널

- **웹사이트**: https://neuralgrid.kr
- **GitHub**: https://github.com/hompystory-coder/azamans
- **이메일**: admin@neuralgrid.kr
- **서버**: 115.91.5.140

### Support

문제가 발생하거나 질문이 있으시면:
1. [GitHub Issues](https://github.com/hompystory-coder/azamans/issues)에 등록
2. admin@neuralgrid.kr로 이메일 발송
3. [문서](https://github.com/hompystory-coder/azamans) 참조

---

## 🎉 감사의 말

Special thanks to:
- OpenAI for AI models
- Cloudflare for infrastructure
- Let's Encrypt for SSL certificates
- The open-source community

---

<div align="center">

**🚀 NeuralGrid - Powering the Future of AI Automation**

Made with ❤️ by the NeuralGrid Team

[⬆ Back to top](#-neuralgrid-platform---차세대-ai-통합-플랫폼)

</div>
