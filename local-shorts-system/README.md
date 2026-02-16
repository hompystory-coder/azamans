# 🎬 Local PC AI Shorts Generation System

## 📌 프로젝트 개요

**사용자 PC의 CPU/GPU를 활용한 완전 로컬 AI 쇼츠 생성 시스템**

- ✅ API 비용 $0 (오픈소스 모델 사용)
- ✅ 완전한 프라이버시 (데이터 외부 전송 없음)
- ✅ 무제한 생성 (제한 없음)
- ✅ 오프라인 작동 가능

---

## 🚀 빠른 시작

### 1. 시스템 요구사항

#### 최소 사양
- CPU: Intel i5 8세대 이상
- RAM: 16GB
- GPU: GTX 1660 (6GB VRAM)
- 저장소: 50GB SSD

#### 권장 사양
- CPU: Intel i7 10세대 이상
- RAM: 32GB
- GPU: RTX 3060 (12GB VRAM)
- 저장소: 100GB NVMe SSD

### 2. 설치

```bash
# 저장소 클론
git clone <repository>
cd local-shorts-system

# Python 가상환경 생성
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate

# 의존성 설치
pip install -r requirements.txt

# AI 모델 자동 다운로드 (최초 1회)
python scripts/install_models.py

# 프론트엔드 설치 (선택)
cd frontend
npm install
```

### 3. 실행

```bash
# 백엔드 서버 시작
cd backend
python app.py

# 프론트엔드 실행 (선택)
cd frontend
npm run dev
```

---

## 🤖 사용하는 오픈소스 AI 모델

### 비디오 생성
- **AnimateDiff** - 텍스트/이미지를 비디오로 변환
- **Stable Diffusion XL** - 고품질 이미지 생성

### 음성 합성
- **Coqui TTS (XTTS-v2)** - 한국어 완벽 지원

### 스크립트 생성
- **LLaMA 3.1 8B** - 로컬 LLM (Ollama)

---

## 📁 프로젝트 구조

```
local-shorts-system/
├── backend/              # Python 백엔드
│   ├── models/          # AI 모델 래퍼
│   ├── services/        # 비즈니스 로직
│   ├── controllers/     # API 컨트롤러
│   └── app.py          # FastAPI 서버
├── frontend/            # Electron 데스크톱 앱
│   ├── src/            # Vue.js 소스
│   └── electron/       # Electron 메인
├── models/              # 다운로드된 AI 모델
├── scripts/             # 유틸리티 스크립트
├── output/              # 생성된 쇼츠
└── docs/                # 문서
```

---

## 🎯 주요 기능

### ✨ 완전 자동화 워크플로우

```
1. URL 입력 (제품 페이지, 블로그 등)
   ↓
2. 자동 크롤링 (텍스트 + 이미지)
   ↓
3. AI 스크립트 생성 (로컬 LLM)
   ↓
4. 캐릭터 이미지 생성 (Stable Diffusion)
   ↓
5. 음성 합성 (Coqui TTS)
   ↓
6. 비디오 생성 (AnimateDiff)
   ↓
7. 최종 렌더링 (FFmpeg)
   ↓
8. 완성된 쇼츠 저장
```

### 🎭 39개 프리미엄 캐릭터
- 💼 비즈니스 (5개)
- 🚀 테크 (5개)
- 👗 패션 (5개)
- ⚽ 스포츠 (5개)
- 🍜 푸드 (5개)
- 🎪 엔터테인먼트 (5개)
- 🐾 레거시 (9개)

---

## ⚡ 성능

### 예상 생성 시간 (RTX 3060 기준)
- 크롤링: 5초
- 스크립트 생성: 30초
- 이미지 생성: 30초
- 음성 합성: 20초
- 비디오 생성: 120초
- 렌더링: 30초
- **총: 약 4분**

---

## 📊 비용 비교

### API 기반 시스템
- 월 100개 쇼츠: **$60-120**

### 로컬 PC 시스템 (이 프로젝트)
- 월 100개 쇼츠: **$5-10** (전기료만)
- **연간 절감: $600-1,200**

---

## 🛠️ 개발 진행 상황

- [x] 시스템 설계
- [ ] 환경 설정
- [ ] 모델 다운로드 스크립트
- [ ] Stable Diffusion 통합
- [ ] Coqui TTS 통합
- [ ] LLaMA 3.1 통합
- [ ] AnimateDiff 통합
- [ ] FFmpeg 렌더링 파이프라인
- [ ] 웹 크롤러
- [ ] FastAPI 백엔드
- [ ] Electron 프론트엔드
- [ ] 테스트 및 최적화
- [ ] 문서 작성
- [ ] 배포 패키징

---

## 📚 문서

- [시스템 설계](../LOCAL_PC_SHORTS_SYSTEM.md)
- [설치 가이드](docs/INSTALLATION.md)
- [사용자 가이드](docs/USER_GUIDE.md)
- [API 문서](docs/API.md)
- [모델 관리](docs/MODELS.md)

---

## 🤝 기여

이 프로젝트는 오픈소스입니다. 기여를 환영합니다!

---

## 📄 라이선스

MIT License

---

**작성일**: 2025-12-25  
**버전**: 0.1.0-alpha  
**상태**: 개발 중 🚧
