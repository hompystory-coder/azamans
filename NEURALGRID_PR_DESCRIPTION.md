# 🚀 NeuralGrid Platform - Complete AI Automation Ecosystem

## 📌 Overview

**NeuralGrid**는 차세대 AI 통합 플랫폼으로, 다양한 AI 서비스를 하나의 계정으로 통합 관리할 수 있는 올인원 솔루션입니다.

---

## 🎯 Main Features

### ✅ 완료된 주요 작업

1. **통합 SSO 로그인 시스템 구현**
   - JWT 기반 인증 시스템
   - 한 번의 회원가입으로 모든 서비스 이용 가능
   - Glassmorphism 디자인의 모던 로그인 모달

2. **메인 페이지 리디자인**
   - 실시간 통계(Stats) 섹션 제거
   - 서비스 중심 레이아웃으로 재구성
   - 향상된 Hero 섹션 및 CTA 버튼

3. **서브도메인 인프라 구축 및 최적화**
   - 모든 서비스 독립 도메인 설정
   - Let's Encrypt SSL 인증서 자동 발급
   - Nginx 리버스 프록시 최적화

4. **신규 서비스 배포**
   - Shorts Market (쿠팡 파트너스 연동)
   - Ollama AI 서비스 구성

---

## 🌐 Platform Architecture

### Main Domain
**[neuralgrid.kr](https://neuralgrid.kr)** - 메인 플랫폼 허브
- **기술 스택**: HTML/CSS/JavaScript, Glassmorphism UI
- **기능**: 통합 로그인, 서비스 소개, 원클릭 회원가입
- **서버**: Nginx Static + Express API Gateway (Port 3200)

---

## 🎨 Service Portfolio

### 1️⃣ **블로그 기사 쇼츠생성기**
**🔗 URL**: [bn-shop.neuralgrid.kr](https://bn-shop.neuralgrid.kr)

#### 📝 서비스 소개
블로그 기사를 AI가 자동으로 분석하여 YouTube Shorts 형식의 짧은 영상으로 변환하는 서비스입니다.

#### ✨ 주요 기능
- 📄 블로그 URL 입력 → 자동 콘텐츠 분석
- 🎬 AI 스토리보드 자동 생성 (Gemini 2.0)
- 🖼️ 이미지 생성 (Pollinations.AI)
- 🎥 비디오 렌더링 (Kling v2.1)
- 🔊 한국어 자막 자동 렌더링
- ⚡ 평균 처리 시간: 4~5분
- 💰 비용: 영상당 약 $0.06

#### 🎯 사용 방법
```
1. bn-shop.neuralgrid.kr 접속
2. '무료 회원가입' 클릭 → NeuralGrid SSO 로그인
3. 대시보드에서 '새 프로젝트 만들기' 클릭
4. 블로그 URL 입력
5. AI 자동 분석 시작
6. 4~5분 후 완성된 Shorts 영상 다운로드
```

#### 📊 기술 스택
- **Backend**: Node.js + Express
- **AI Models**: Gemini 2.0, Pollinations.AI, Kling v2.1
- **Port**: 3001
- **PM2 Process**: `youtube-shorts-generator`

---

### 2️⃣ **쇼츠 영상 자동화 (MediaFX Shorts)**
**🔗 URL**: [mfx.neuralgrid.kr](https://mfx.neuralgrid.kr)

#### 📝 서비스 소개
AI 기반 쇼트폼 영상 자동 생성 플랫폼으로, 텍스트 프롬프트만으로 전문가급 Shorts 영상을 제작합니다.

#### ✨ 주요 기능
- 🤖 텍스트 → 비디오 자동 생성
- 🎨 다양한 템플릿 및 스타일 지원
- 🎵 AI 음악 자동 매칭
- 📱 SNS 최적화 (Instagram, TikTok, YouTube Shorts)
- 🔄 배치 프로세싱 지원
- 📊 영상 성과 분석 대시보드

#### 🎯 사용 방법
```
1. mfx.neuralgrid.kr 접속 → SSO 로그인
2. '새 쇼츠 만들기' 클릭
3. 텍스트 프롬프트 입력
   예: "도쿄 야경을 배경으로 한 감성적인 여행 브이로그"
4. 스타일 및 음악 선택
5. '생성하기' 클릭
6. 3~4분 후 완성된 영상 확인
7. 다운로드 또는 직접 SNS 업로드
```

#### 📊 기술 스택
- **Backend**: Node.js + Express
- **AI Models**: Custom AI Pipeline
- **Port**: 3101
- **PM2 Process**: `mfx-shorts`

---

### 3️⃣ **스타뮤직 (NeuronStar Music)**
**🔗 URL**: [music.neuralgrid.kr](https://music.neuralgrid.kr)

#### 📝 서비스 소개
무료 AI 음악 생성 플랫폼으로, 텍스트 설명만으로 고품질 음악을 제작할 수 있습니다.

#### ✨ 주요 기능
- 🎼 다양한 장르 지원 (Pop, Rock, Jazz, EDM, Classical 등)
- ✍️ 커스텀 가사 입력 가능
- 🎧 고품질 오디오 출력 (WAV/MP3)
- 💼 상업적 이용 가능
- 📦 무제한 생성 (무료 플랜)
- 🔊 실시간 미리듣기

#### 🎯 사용 방법
```
1. music.neuralgrid.kr 접속 → SSO 로그인
2. '새 음악 만들기' 클릭
3. 음악 설명 입력
   예: "잔잔한 피아노 선율과 함께하는 감성적인 발라드"
4. 장르 및 분위기 선택
5. (선택) 가사 입력
6. '생성하기' 클릭
7. 30초~1분 후 완성된 음악 청취
8. 다운로드 (MP3/WAV)
```

#### 📊 기술 스택
- **Backend**: Node.js + Express
- **AI Model**: Custom Music Generation AI
- **Port**: 3002
- **PM2 Process**: `neuronstar-music`

---

### 4️⃣ **N8N 워크플로우 자동화**
**🔗 URL**: [n8n.neuralgrid.kr](https://n8n.neuralgrid.kr)

#### 📝 서비스 소개
200개 이상의 앱 연동을 지원하는 강력한 워크플로우 자동화 엔진입니다.

#### ✨ 주요 기능
- 🔗 200+ 앱 통합 지원
- 🖱️ 드래그 앤 드롭 빌더
- 🔄 REST API 자동화
- ⏰ Cron 스케줄링
- 🔐 보안 자체 호스팅
- 📊 실시간 로그 및 디버깅

#### 🎯 사용 방법
```
1. n8n.neuralgrid.kr 접속 → SSO 로그인
2. '새 워크플로우 만들기' 클릭
3. 트리거 노드 선택 (예: Webhook, Schedule)
4. 작업 노드 연결 (예: Send Email, HTTP Request)
5. 각 노드 설정 및 연결
6. '활성화' 버튼 클릭
7. 워크플로우 자동 실행 모니터링
```

#### 📊 기술 스택
- **Platform**: N8N (Self-hosted)
- **Backend**: Node.js
- **Port**: 5692
- **Database**: PostgreSQL

---

### 5️⃣ **서버 모니터링 (System Monitor)**
**🔗 URL**: [monitor.neuralgrid.kr](https://monitor.neuralgrid.kr)

#### 📝 서비스 소개
실시간 서버 상태 및 PM2 프로세스를 한눈에 모니터링하는 프리미엄 대시보드입니다.

#### ✨ 주요 기능
- 📊 실시간 CPU/메모리 사용률 (Chart.js)
- 💿 디스크 용량 모니터링
- ⚙️ PM2 프로세스 상태 추적
- 🔔 자동 알림 시스템
- 🎨 Glassmorphism 디자인
- 🔄 30초 자동 갱신

#### 🎯 사용 방법
```
1. monitor.neuralgrid.kr 접속 → SSO 로그인
2. 실시간 대시보드 자동 표시
3. 각 서비스 상태 확인
   - 🟢 녹색: 정상 가동
   - 🔴 빨간색: 서비스 중단
4. CPU/메모리 그래프 실시간 모니터링
5. PM2 프로세스 제어 (재시작/중지)
```

#### 📊 기술 스택
- **Frontend**: HTML/CSS/JavaScript + Chart.js
- **Backend**: Express API
- **Port**: 5001
- **PM2 Process**: `monitor-server`

---

### 6️⃣ **쿠팡쇼츠 (Shorts Market)**
**🔗 URL**: [market.neuralgrid.kr](https://market.neuralgrid.kr)

#### 📝 서비스 소개
YouTube Shorts와 쿠팡 파트너스를 연동한 쇼츠 커머스 플랫폼입니다.

#### ✨ 주요 기능
- 📹 YouTube Shorts 자동 수집
- 🛒 쿠팡 파트너스 딥링크 자동 생성
- 🔗 네이버 브랜드 커넥트 지원
- 💰 크리에이터 수익 대시보드
- 📊 조회수/좋아요/댓글 통계
- 🎯 자동 상품 매칭

#### 🎯 사용 방법
```
1. market.neuralgrid.kr 접속 → SSO 로그인
2. '새 쇼츠 등록' 클릭
3. YouTube Shorts URL 입력
4. 자동 정보 수집 (제목, 썸네일, 조회수 등)
5. 쿠팡 상품 링크 입력 또는 자동 추천 선택
6. '등록하기' 클릭
7. 크리에이터 대시보드에서 수익 추적
```

#### 📊 기술 스택
- **Backend**: Hono.js (Cloudflare Pages)
- **Database**: Cloudflare D1 (Production) / SQLite (Local)
- **Port**: 3003
- **PM2 Process**: `shorts-market`

---

### 7️⃣ **통합 인증 서비스 (Auth Service)**
**🔗 URL**: [auth.neuralgrid.kr](https://auth.neuralgrid.kr)

#### 📝 서비스 소개
모든 NeuralGrid 서비스를 위한 중앙 집중식 JWT 기반 SSO 인증 시스템입니다.

#### ✨ 주요 기능
- 🔐 JWT 기반 보안 인증
- 🎫 API 키 발급 및 관리
- 💳 크레딧 추적 시스템
- 👤 통합 사용자 관리
- 🔄 자동 토큰 갱신
- 🛡️ 권한 기반 접근 제어

#### 🎯 사용 방법
```
1. 모든 서비스에서 '로그인' 클릭
2. auth.neuralgrid.kr 자동 리다이렉트
3. 이메일/비밀번호 입력
4. JWT 토큰 자동 발급
5. 원래 서비스로 자동 복귀
6. 모든 서비스 즉시 사용 가능
```

#### 📊 기술 스택
- **Backend**: Express.js
- **Database**: SQLite
- **Port**: 3099
- **PM2 Process**: `auth-service`

---

## 🏗️ Infrastructure

### 🖥️ Server Information
- **IP**: 115.91.5.140
- **OS**: Ubuntu Server
- **Web Server**: Nginx 1.24.0
- **Process Manager**: PM2
- **SSL**: Let's Encrypt (Auto-renewal)

### 📊 Active PM2 Processes
```
┌─────────────────────────┬────────┬──────────┬─────────┐
│ Name                    │ Port   │ Memory   │ Status  │
├─────────────────────────┼────────┼──────────┼─────────┤
│ api-gateway             │ 4000   │ 94.1 MB  │ online  │
│ auth-service            │ 3099   │ 60.7 MB  │ online  │
│ main-dashboard          │ 3200   │ 74.9 MB  │ online  │
│ monitor-server          │ 5001   │ 81.2 MB  │ online  │
│ mfx-shorts              │ 3101   │ 61.9 MB  │ online  │
│ neuronstar-music        │ 3002   │ 229.7 MB │ online  │
│ youtube-shorts-gen      │ 3001   │ 95.7 MB  │ online  │
│ shorts-market           │ 3003   │ 57.1 MB  │ online  │
└─────────────────────────┴────────┴──────────┴─────────┘
```

---

## 🔒 Security Features

### SSL/TLS Configuration
- ✅ All subdomains secured with Let's Encrypt
- ✅ Auto-renewal configured
- ✅ HTTPS redirect enforced
- ✅ Modern TLS protocols only

### Authentication
- 🔐 JWT-based token system
- 🔒 Secure cookie storage
- 🔄 Token refresh mechanism
- 🛡️ CORS protection

---

## 📈 Performance Metrics

### Response Times
- **Main Page**: ~200ms
- **API Gateway**: ~50ms
- **Auth Service**: ~100ms
- **Video Generation**: 4~5 minutes
- **Music Generation**: 30~60 seconds

### Uptime
- **Target**: 99.9%
- **Current**: 26+ hours continuous operation
- **Monitoring**: Real-time via monitor.neuralgrid.kr

---

## 🚀 Deployment Guide

### Prerequisites
```bash
# Required software
- Node.js v20.19.6+
- npm 10.8.2+
- PM2 (process manager)
- Nginx
- Certbot (for SSL)
```

### Quick Start
```bash
# 1. Clone repository
git clone https://github.com/hompystory-coder/azamans.git
cd azamans

# 2. Install dependencies
npm install

# 3. Start all services
pm2 start ecosystem.config.js

# 4. Check status
pm2 status

# 5. View logs
pm2 logs
```

### Individual Service Deployment
```bash
# Start specific service
pm2 start ecosystem.config.js --only [service-name]

# Examples:
pm2 start ecosystem.config.js --only auth-service
pm2 start ecosystem.config.js --only mfx-shorts
pm2 start ecosystem.config.js --only shorts-market
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. Service Not Starting
```bash
# Check logs
pm2 logs [service-name]

# Restart service
pm2 restart [service-name]

# Delete and restart
pm2 delete [service-name]
pm2 start ecosystem.config.js --only [service-name]
```

#### 2. Port Already in Use
```bash
# Find process using port
lsof -i :[port-number]

# Kill process
kill -9 [PID]

# Restart service
pm2 restart [service-name]
```

#### 3. SSL Certificate Issues
```bash
# Renew certificate
sudo certbot renew --force-renewal -d [domain]

# Update Nginx config
sudo nginx -t
sudo systemctl reload nginx
```

---

## 📝 Recent Updates

### December 15, 2025
- ✅ Main page redesign completed
- ✅ Real-time stats section removed
- ✅ Integrated SSO login system deployed
- ✅ Shorts Market local deployment completed
- ✅ Ollama service configured
- ✅ All SSL certificates renewed
- ✅ Nginx configurations optimized

### Outstanding Tasks
- 🔄 Auth modal signup form enhancement (username + confirm password)
- 🔄 DNS A record for market.neuralgrid.kr
- 🔄 Cloudflare D1 to SQLite migration for Shorts Market

---

## 🌟 Future Roadmap

### Q1 2026
- [ ] AI Chatbot integration
- [ ] Mobile app development
- [ ] Multi-language support (EN, JP, CN)
- [ ] Advanced analytics dashboard

### Q2 2026
- [ ] API marketplace
- [ ] Plugin ecosystem
- [ ] Enterprise pricing tiers
- [ ] White-label solutions

---

## 👥 Contributors

- **Development Team**: NeuralGrid Engineering
- **Infrastructure**: Cloud Operations Team
- **AI Models**: AI Research Lab

---

## 📞 Support

### Contact Information
- **Website**: https://neuralgrid.kr
- **Documentation**: https://docs.neuralgrid.kr (Coming soon)
- **GitHub**: https://github.com/hompystory-coder/azamans

### Support Channels
- 📧 Email: support@neuralgrid.kr
- 💬 Discord: discord.gg/neuralgrid (Coming soon)
- 🐛 Issues: GitHub Issues

---

## 📄 License

Copyright © 2025 NeuralGrid. All rights reserved.

---

## 🙏 Acknowledgments

Special thanks to:
- OpenAI for AI models
- Cloudflare for infrastructure
- Let's Encrypt for SSL certificates
- The open-source community

---

**🎉 NeuralGrid - Powering the Future of AI Automation**
