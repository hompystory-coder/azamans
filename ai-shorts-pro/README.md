# 🎬 AI 쇼츠 자동화 Pro

> 블로그 URL 하나로 전문가급 쇼츠 영상을 자동 생성하는 차세대 AI 플랫폼

[![Status](https://img.shields.io/badge/status-in--development-yellow)]()
[![License](https://img.shields.io/badge/license-MIT-blue)]()
[![Node](https://img.shields.io/badge/node-%3E%3D18.0.0-brightgreen)]()
[![React](https://img.shields.io/badge/react-18.2.0-61dafb)]()

---

## ✨ 주요 기능

### 🎭 10가지 전문 캐릭터
- **포티 (Forty)**: 범용 콘텐츠 🎭
- **비즈니 (Bizny)**: 비즈니스/업무 💼
- **쿠키 (Cookie)**: 음식 리뷰 🍪
- **테키 (Techy)**: IT/가전 🤖
- **뷰티 (Beauty)**: 뷰티/패션 💄
- **홈이 (Homey)**: 인테리어/가구 🏠
- **피티 (Fitty)**: 피트니스/운동 💪
- **트래비 (Travvy)**: 여행/관광 ✈️
- **페티 (Petty)**: 반려동물 🐶
- **에듀 (Edu)**: 교육/학습 📚

### 🎤 8가지 AI 음성
- Google Gemini TTS (5가지 스타일)
- Minimax TTS (2가지 감정)
- ElevenLabs (멀티링구얼 고품질)

### 🎨 완벽한 커스터마이징
- ✅ 배경음악/이미지 업로드
- ✅ 6가지 한글 폰트 선택
- ✅ 자막 크기/위치/색상 조정
- ✅ 이미지 효과 (줌, 팬, 켄번즈)

### 🤖 완전 자동화
- ✅ 블로그/기사 URL 입력
- ✅ AI 자동 스크립트 생성
- ✅ 자동 장면 구성 (5~10개)
- ✅ 원클릭 쇼츠 생성

### 💰 비용 효율성
- **70% 비용 절감**: Minimax Hailuo 2.3 사용
- **무료 렌더링**: FFmpeg 로컬 처리
- **이미지 캐싱**: 재사용으로 비용 0원
- **예상 비용**: ~$0.30/영상

---

## 🚀 빠른 시작

### 사전 요구사항
```bash
- Node.js >= 18.0.0
- npm or yarn
- FFmpeg (로컬 설치)
- Redis (선택사항 - 캐싱용)
```

### 설치

```bash
# 프로젝트 클론
cd /home/azamans/webapp/ai-shorts-pro

# Backend 설치
cd backend
npm install
cp .env.example .env
# .env 파일 편집하여 API 키 입력

# Frontend 설치
cd ../frontend
npm install

# 개발 서버 실행
# Terminal 1 - Backend
cd backend
npm run dev

# Terminal 2 - Frontend
cd frontend
npm run dev
```

### 환경 변수 설정

`.env` 파일에 다음 키 입력:
```env
# AI API Keys
OPENAI_API_KEY=your_key
GOOGLE_AI_KEY=your_key
ELEVENLABS_API_KEY=your_key

# Server
PORT=5000
FRONTEND_URL=http://localhost:3000
```

---

## 📖 사용 방법

### 1. 프로젝트 생성
```
1. "신규 프로젝트" 클릭
2. 캐릭터 선택 (10가지 중)
3. 음성 선택 (8가지 중)
4. 폰트/스타일 선택
```

### 2. 콘텐츠 입력
```
Option A: 블로그 URL 입력
  → AI가 자동으로 크롤링 및 분석
  → 스크립트 자동 생성
  
Option B: 수동 입력
  → 장면별 텍스트 입력
  → 이미지 직접 업로드
```

### 3. 설정 조정
```
- 배경음악/이미지 업로드
- 자막 스타일 조정
- 장면 순서 변경
- 미리보기 확인
```

### 4. 생성 시작
```
- "생성 시작" 클릭
- 실시간 진행률 확인 (~25분)
- 완성 후 다운로드
```

---

## 🏗️ 프로젝트 구조

```
ai-shorts-pro/
├── backend/
│   ├── routes/           # API 라우트
│   ├── controllers/      # 비즈니스 로직
│   ├── services/         # AI 서비스
│   ├── models/           # 데이터 모델
│   ├── utils/            # 유틸리티
│   ├── uploads/          # 업로드 파일
│   └── generated/        # 생성 파일
├── frontend/
│   ├── src/
│   │   ├── components/   # React 컴포넌트
│   │   ├── pages/        # 페이지
│   │   ├── hooks/        # Custom hooks
│   │   ├── utils/        # 유틸리티
│   │   └── store/        # 상태 관리
│   └── public/
└── shared/               # 공유 데이터
    ├── characters.json   # 캐릭터 프리셋
    ├── voices.json       # 음성 프리셋
    ├── fonts.json        # 폰트 프리셋
    └── default-settings.json
```

---

## 🛠️ 기술 스택

### Backend
- **Node.js** + **Express**: REST API
- **Socket.io**: 실시간 통신
- **Bull Queue**: 비동기 작업 관리
- **Redis**: 캐싱 (선택사항)
- **Multer**: 파일 업로드
- **Axios** + **Cheerio**: 웹 크롤링

### Frontend
- **React 18**: UI 프레임워크
- **Vite**: 빌드 도구
- **Zustand**: 상태 관리
- **TailwindCSS**: 스타일링
- **React Router**: 라우팅
- **React Dropzone**: 파일 업로드
- **Wavesurfer.js**: 오디오 미리보기

### AI & Media
- **Nano Banana Pro**: 이미지 생성
- **Minimax Hailuo 2.3**: 비디오 생성
- **Google Gemini TTS**: 음성 생성
- **ElevenLabs**: 고품질 음성/음악
- **FFmpeg**: 비디오 렌더링

---

## 📊 API 문서

### Characters API
```http
GET  /api/characters
GET  /api/characters/:id
GET  /api/characters/category/:category
```

### Voices API
```http
GET  /api/voices
GET  /api/voices/:id
POST /api/voices/sample
POST /api/voices/test
```

### Projects API
```http
GET    /api/projects
POST   /api/projects
GET    /api/projects/:id
PUT    /api/projects/:id
DELETE /api/projects/:id
POST   /api/projects/:id/generate
GET    /api/projects/:id/status
```

### Crawler API
```http
POST /api/crawler/crawl
POST /api/crawler/analyze
POST /api/crawler/generate-script
POST /api/crawler/extract-images
```

### Generation API
```http
POST /api/generation/start
GET  /api/generation/:jobId/progress
POST /api/generation/:jobId/cancel
GET  /api/generation/:jobId/download
POST /api/generation/scene
POST /api/generation/character-image
POST /api/generation/voice
POST /api/generation/bgm
POST /api/generation/render
```

---

## 💡 워크플로우

### 자동 모드
```
1. URL 입력
   ↓
2. 크롤링 & 분석 (30초)
   ↓
3. 스크립트 자동 생성 (30초)
   ↓
4. 이미지 생성 (1분)
   ↓
5. 비디오 생성 (20분)
   ↓
6. 음성 생성 (1분)
   ↓
7. 최종 렌더링 (2분)
   ↓
8. 완성! (~25분)
```

### 수동 모드
```
1. 장면별 입력
   ↓
2. 이미지 업로드 또는 생성
   ↓
3. 스크립트 편집
   ↓
4. 설정 조정
   ↓
5. 생성 시작
   ↓
6. 완성!
```

---

## 🎯 로드맵

### Phase 1: MVP (완료 80%)
- [x] 10가지 캐릭터 프리셋
- [x] 8가지 음성 프리셋
- [x] 6가지 폰트 프리셋
- [x] Backend API 구조
- [x] 기본 설정 시스템
- [ ] Frontend UI
- [ ] 음성 미리듣기
- [ ] 파일 업로드

### Phase 2: 핵심 기능
- [ ] 블로그 크롤링 & 분석
- [ ] AI 스크립트 자동 생성
- [ ] 3가지 생성 모드
- [ ] 자동/수동 모드
- [ ] 실시간 진행률

### Phase 3: 고급 기능
- [ ] Redis 캐싱
- [ ] 비용 최적화
- [ ] 유튜브 자동 업로드
- [ ] A/B 테스트
- [ ] 템플릿 시스템

### Phase 4: 엔터프라이즈
- [ ] 팀 협업 기능
- [ ] 분석 대시보드
- [ ] API 제공
- [ ] 화이트라벨

---

## 📝 라이선스

MIT License - 자유롭게 사용하세요!

---

## 🤝 기여하기

PR과 이슈는 언제나 환영합니다!

---

## 📞 문의

- Website: https://shorts.neuralgrid.kr
- Email: support@neuralgrid.kr

---

**Made with ❤️ by NeuralGrid Team**
