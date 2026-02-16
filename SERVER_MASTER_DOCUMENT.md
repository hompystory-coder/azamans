# 🏢 NeuralGrid Platform - 마스터 서버 문서

**최종 업데이트:** 2025-12-15  
**작성자:** azamans  
**목적:** 서버 환경, API 키, 서비스 구성 통합 관리

---

## 📋 목차

1. [서버 인프라 정보](#서버-인프라-정보)
2. [도메인 및 DNS 구성](#도메인-및-dns-구성)
3. [서비스 포트 맵핑](#서비스-포트-맵핑)
4. [API 키 통합 관리](#api-키-통합-관리)
5. [데이터베이스 접속 정보](#데이터베이스-접속-정보)
6. [각 서비스별 상세 정보](#각-서비스별-상세-정보)
7. [보안 및 접근 제어](#보안-및-접근-제어)

---

## 🖥️ 서버 인프라 정보

### 하드웨어 사양
- **모델:** GMKtec K12 Mini PC
- **CPU:** AMD Ryzen 7 H 255
- **RAM:** 32GB DDR5
- **Storage:** 1TB SSD + PCIe 4.0 Oculink
- **OS:** Ubuntu Server + Win11Pro

### 네트워크 구성
```
외부 IP: 182.213.14.5
사용 가능 IP: 115.91.5.140
게이트웨이: 115.91.5.137
서브넷마스크: 255.255.255.248
IP 대역: 115.91.5.136/29
DNS 서버: 61.41.153.2
```

### 모뎀 정보
```
모뎀 접속 IP: 192.168.219.1
관리자 웹 암호: [보안상 비공개]
인터넷 연결방식: DHCP
```

### SSH 접속 정보
```bash
ssh azamans@115.91.5.140
Password: [보안상 비공개 - 관리자에게 문의]

# 관리자 계정
Username: admin
Email: hompystory@gmail.com
Password: [보안상 비공개]
```

---

## 🌐 도메인 및 DNS 구성

### 주 도메인
**neuralgrid.kr**

### DNS 관리
- **관리 사이트:** https://dnszi.com/
- **1차 DNS:** ns3.dnszi.com (121.78.251.20)
- **2차 DNS:** ns18.dnszi.com (121.78.72.36)

### 서비스 도메인 맵핑

| 서비스 | 도메인 | 포트 | 상태 |
|--------|--------|------|------|
| 메인 페이지 | https://neuralgrid.kr | 80/443 | ✅ 운영중 |
| MediaFX Shorts | https://mfx.neuralgrid.kr | 3101 | ✅ 운영중 |
| BN Shop | https://bn-shop.neuralgrid.kr | 3001 | ✅ 운영중 |
| NeuronStar Music | https://music.neuralgrid.kr | 3002 | ✅ 운영중 |
| N8N Automation | https://n8n.neuralgrid.kr | 5678 | ✅ 운영중 |
| System Monitor | https://monitor.neuralgrid.kr | 5001 | ✅ 운영중 |
| AnythingLLM | http://115.91.5.140:3104 | 3104 | ✅ 운영중 |
| Ollama API | http://115.91.5.140:11434 | 11434 | ✅ 운영중 |
| API Endpoint | https://api.neuralgrid.kr | - | ⏳ 설정 필요 |
| Stable Diffusion | https://sd.neuralgrid.kr | - | ⏳ 설치 필요 |

---

## 🔑 API 키 통합 관리

**⚠️ 보안 주의:**
- 실제 API 키는 환경변수(.env) 또는 안전한 키 관리 시스템에 저장
- 아래 형식은 키 구조 참고용이며, 실제 키는 별도 관리

### AI 서비스 API

#### OpenAI
```
API Key: [환경변수 OPENAI_API_KEY 사용]
사용처: GPT-4, GPT-3.5-turbo 등
```

#### Claude (Anthropic)
```
API Key: [환경변수 CLAUDE_API_KEY 사용]
사용처: Claude 3 Opus, Sonnet, Haiku
```

#### Google Gemini
```
API Key: [환경변수 GEMINI_API_KEY 사용]
사용처: Gemini Pro, Gemini Vision
```

#### MiniMax
```
Group ID: [환경변수 MINIMAX_GROUP_ID 사용]
API Key: [환경변수 MINIMAX_API_KEY 사용]
사용처: 음성 생성, 영상 생성
```

#### OpenRouter
```
API Key: [환경변수 OPENROUTER_API_KEY 사용]
사용처: 다양한 LLM 통합 API
```

### 미디어 생성 API

#### Replicate.com
```
API Key: [환경변수 REPLICATE_API_KEY 사용]
사용처: Flux 1.1 Pro, SDXL 등
```

#### ElevenLabs
```
API Key: [환경변수 ELEVENLABS_API_KEY 사용]
사용처: TTS, 음성 합성
```

#### Shotstack
```
Owner ID (Sandbox): [환경변수 SHOTSTACK_SANDBOX_OWNER_ID 사용]
API Key (Sandbox): [환경변수 SHOTSTACK_SANDBOX_API_KEY 사용]
Owner ID (Production): [환경변수 SHOTSTACK_PROD_OWNER_ID 사용]
API Key (Production): [환경변수 SHOTSTACK_PROD_API_KEY 사용]
사용처: 영상 렌더링, 자막 합성
```

### Cloudflare

#### API 토큰
```
API Token: [환경변수 CLOUDFLARE_API_TOKEN 사용]
Account ID: [환경변수 CLOUDFLARE_ACCOUNT_ID 사용]
Email: hompystory@gmail.com
```

#### R2 Storage
```
Public Dev URL: https://pub-463c78173c284efb8c8a6c37f6b1766c.r2.dev
```

### Supabase

```
Project URL: https://rauhoefqchpgwmgspfp.supabase.co
Publishable Key: [환경변수 SUPABASE_PUBLISHABLE_KEY 사용]
Anon Public Key: [환경변수 SUPABASE_ANON_KEY 사용]
Service Role Key: [환경변수 SUPABASE_SERVICE_ROLE_KEY 사용]
```

### 쿠팡 파트너스 API
```
ID: AF8150630
Access Key: [환경변수 COUPANG_ACCESS_KEY 사용]
Secret Key: [환경변수 COUPANG_SECRET_KEY 사용]
```

### Google OAuth
```
Client ID: [환경변수 GOOGLE_CLIENT_ID 사용]
Client Secret: [환경변수 GOOGLE_CLIENT_SECRET 사용]
```

### YouTube
```
채널 ID (캠핑저널): UClqs21GOjnO90oFIcQuHIgw
```

### 수노 API
```
관리 대시보드: https://sunoapi.org/ko/dashboard
```

---

## 💾 데이터베이스 접속 정보

### PostgreSQL (N8N)
```bash
Host: localhost
Port: 5434
Database: n8n_neuralgrid
Username: neuralgrid
Password: [환경변수 N8N_DB_PASSWORD 사용]

# 접속 명령어 예시
PGPASSWORD='$N8N_DB_PASSWORD' psql -U neuralgrid -d n8n_neuralgrid -p 5434 -h localhost
```

### PostgreSQL (Memory DB - RAG System)
```bash
Host: localhost
Port: 5435
Database: memory_db
Username: neuralgrid
Password: [환경변수 MEMORY_DB_PASSWORD 사용]
Extensions: pgvector
```

### PostgreSQL 관리 쿼리
```sql
-- 사용자 목록 확인
SELECT id, email, name, role, created_at FROM users;

-- 관리자 권한 부여
UPDATE users SET role = 'ADMIN' WHERE email = 'aza700901@nate.com';
```

---

## 📦 각 서비스별 상세 정보

### 1. MediaFX Shorts (mfx.neuralgrid.kr)
**포트:** 3101  
**설명:** AI 쇼츠 자동 생성 플랫폼  
**기능:**
- 블로그 크롤링
- AI 이미지 생성 (Replicate Flux 1.1 Pro)
- 영상 변환 (Fal.ai Kling v2.1)
- 한글 자막 렌더링
- FFmpeg 합성

**로그인 정보:**
```
Email: hompystory@gmail.com
Password: [보안상 비공개]
```

### 2. BN Shop (bn-shop.neuralgrid.kr)
**포트:** 3001  
**설명:** 이커머스 플랫폼  
**로그인 정보:**
```
Email: hompystory@gmail.com
Password: [보안상 비공개]
```

### 3. NeuronStar Music (music.neuralgrid.kr)
**포트:** 3002  
**설명:** AI 음악 생성 플랫폼  
**특징:** 무료 음악 제공

### 4. N8N Automation (n8n.neuralgrid.kr)
**포트:** 5678  
**관리자 계정:**
```
URL: http://115.91.5.140:5678
Username: admin
Password: [보안상 비공개]
```

**데이터베이스:**
```
PostgreSQL User: neuralgrid
PostgreSQL Password: [환경변수 사용]
Database: neuralgrid
```

### 5. System Monitor (monitor.neuralgrid.kr)
**포트:** 5001  
**모니터링 항목:**
- CPU 사용률
- RAM 사용량
- 디스크 사용량/잔량
- 외장하드 사용량/잔량 (실시간)
- 네트워크 트래픽

### 6. RAG + Multi-AI System
**AnythingLLM:** http://115.91.5.140:3104  
**Ollama API:** http://115.91.5.140:11434  
**PostgreSQL (Memory DB):** Port 5435  

**기능:**
- 대화 영구 기억
- 벡터 검색 (HNSW 인덱스)
- Multi-AI 통합 (Llama 3.1 8B, DeepSeek R1 1.5B)
- 비용 최적화 (로컬 AI 우선 사용)

---

## 🔐 보안 및 접근 제어

### SSH 키 (Deploy)
```
ssh-ed25519 [공개키 해시] deploy@neuralgrid.kr
```

### GitHub 통합
```
Token: [환경변수 GITHUB_TOKEN 사용]
사용자: hompystory-coder
```

### 통합 관리자 계정
```
Email: aza700901@nate.com
Password: [보안상 비공개]
용도: 모든 서브 콘텐츠 통합 관리
```

---

## 🚀 배포 및 자동화

### Cloudflare Pages 배포
```bash
# 쇼츠 마켓
URL: https://a48be6e9.shorts-market.pages.dev/
Admin Email: admin@shorts-market.com
Admin Password: [보안상 비공개]

# YouTube Shorts Generator
Main URL: https://youtube-shorts-generator.pages.dev
Latest: https://ad00eb11.youtube-shorts-generator.pages.dev

# 알파스타 뮤직
URL: https://b467f592.alphastar-music.pages.dev/
```

### Webhook 자동 배포
```
GitHub Push → Webhook → 원격 서버 자동 배포
스크립트 위치: /home/azamans/webapp/QUICK_DEPLOY.sh
```

---

## 📝 추가 참고 정보

### GenSpark 에이전트 링크
- **쇼츠 마켓:** https://www.genspark.ai/agents?id=3a882d89-6934-41fb-8a96-8a3c84ed8606
- **YouTube Shorts:** https://www.genspark.ai/agents?id=e0d3f284-1467-48ca-af25-c73dc95b9364
- **알파스타 뮤직:** https://www.genspark.ai/agents?id=eb6fa388-71de-4cf8-a303-46deec9cf0e9
- **통합 관리:** https://www.genspark.ai/agents?id=2f1f5d57-f159-40e8-81c4-2238f2b93cfa

### Novita 샌드박스
```
ID: bc5eb05f-4ba6-4550-8e76-82693b7443f5
```

---

## ⚠️ 중요 주의사항

1. **API 키 보안:** 
   - 절대 공개 저장소에 커밋하지 말 것
   - 환경변수(.env) 또는 AWS Secrets Manager 등 안전한 키 관리 시스템 사용
   - `.env` 파일은 반드시 `.gitignore`에 추가

2. **데이터베이스 백업:** 
   - 매일 자동 백업 설정 필요
   - 백업 위치: `/mnt/aidrive/backups/` 또는 외장하드

3. **포트 충돌 방지:** 
   - 신규 서비스 추가 시 포트 맵핑 확인
   - 현재 사용 중인 포트: 3001, 3002, 3101, 3104, 5001, 5432, 5433, 5434, 5435, 5678, 6379, 6380, 8100, 11434

4. **외장하드 경로:** 
   - `/mnt/aidrive` 또는 지정된 마운트 포인트 사용
   - 느린 I/O 특성으로 대용량 파일은 tar로 압축 후 전송

5. **토큰 관리:** 
   - API 호출 비용 모니터링 시스템 구축 필요
   - 월별 사용량 추적 및 알림 설정

6. **환경변수 파일 예시:**
```bash
# .env.example (실제 .env는 .gitignore에 추가)
OPENAI_API_KEY=your_openai_key_here
CLAUDE_API_KEY=your_claude_key_here
GEMINI_API_KEY=your_gemini_key_here
N8N_DB_PASSWORD=your_db_password_here
MEMORY_DB_PASSWORD=your_memory_db_password_here
# ... 기타 필요한 키들
```

---

## 🔄 환경변수 관리 방법

### 1. 로컬 개발 환경
```bash
# .env 파일 생성 (프로젝트 루트)
cp .env.example .env
nano .env  # 실제 키 값 입력

# .gitignore에 추가 (이미 추가되어 있어야 함)
echo ".env" >> .gitignore
```

### 2. 프로덕션 환경
```bash
# systemd 서비스에서 환경변수 로드
# /etc/systemd/system/yourservice.service
[Service]
EnvironmentFile=/path/to/.env
```

### 3. Docker 환경
```bash
# docker-compose.yml에서 환경변수 파일 지정
services:
  app:
    env_file:
      - .env
```

---

**문서 작성일:** 2025-12-15  
**다음 업데이트 예정:** 2025-12-22  
**보안 강화 버전:** v2.0 (API 키 환경변수화 완료)
