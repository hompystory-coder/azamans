# AI 캐릭터 쇼츠 자동화 시스템 개발 계획

## 📋 시스템 아키텍처

### 1단계: 기본 설정 (Settings)
- 배경 음악/이미지 관리
- 음성 선택 (샘플 미리듣기)
- 서체/폰트 선택
- 자막 크기/위치/효과
- 기본 프롬프트 설정

### 2단계: 캐릭터 시스템 (Characters)
- 10종 캐릭터 프리셋
  1. 친근한 이웃 언니/오빠
  2. 전문 리뷰어
  3. 귀여운 캐릭터
  4. 세련된 인플루언서
  5. 신뢰감 있는 전문가
  6. 활발한 MC
  7. 차분한 해설자
  8. 유머러스한 개그맨
  9. 감성적인 스토리텔러
  10. 파워풀한 세일즈맨

- 비디오 모드 (3가지)
  1. 캐릭터 only
  2. 캐릭터 + 실사 이미지
  3. 실사 이미지 only

### 3단계: 크롤링 & 분석 (Crawl & Analyze)
- 블로그/기사 URL 입력
- 텍스트 + 이미지 추출
- AI 기반 핵심 내용 요약
- 이미지 선택 인터페이스

### 4단계: 스크립트 생성 (Script Generation)
- AI 기반 쇼츠 스크립트 생성
- 장면별 구성
  - 인트로 (3초)
  - 훅 (4초)
  - 메인 콘텐츠 (15-20초)
  - CTA (5초)
- 각 장면에 이미지 매핑
- 편집 가능한 UI

### 5단계: 장면 생성 (Scene Production)
- Minimax Hailuo 2.3 API로 비디오 생성
- 각 장면 3초 영상화
- TTS로 음성 생성
- 자막 오버레이

### 6단계: 최종 렌더링 (Final Rendering)
- FFmpeg로 장면 병합
- 배경음악 합성
- 자막/효과 적용
- 최종 쇼츠 영상 생성

### 7단계: 메타데이터 & 업로드 (Metadata & Upload)
- 유튜브 제목 생성
- 설명 생성
- 해시태그/키워드 생성
- 원클릭 복사 기능

---

## 🔧 기술 스택

### Backend
- **비디오 생성**: Minimax Hailuo 2.3 API
- **TTS**: ElevenLabs / Google TTS
- **이미지 처리**: Sharp
- **비디오 편집**: FFmpeg
- **크롤링**: Puppeteer
- **AI**: GPT-4 / Claude

### Frontend
- **UI Framework**: React
- **State Management**: Zustand
- **Styling**: TailwindCSS
- **Video Player**: Video.js
- **File Upload**: React Dropzone

---

## 📁 파일 구조

```
ai-shorts-pro/
├── backend/
│   ├── controllers/
│   │   ├── projectController.js ✅
│   │   ├── crawlerController.js ✅
│   │   ├── characterController.js (신규)
│   │   ├── videoController.js (신규)
│   │   └── metadataController.js (신규)
│   ├── routes/
│   │   ├── projectRoutes.js ✅
│   │   ├── crawlerRoutes.js ✅
│   │   ├── characterRoutes.js (신규)
│   │   ├── videoRoutes.js (신규)
│   │   └── metadataRoutes.js (신규)
│   ├── services/
│   │   ├── minimaxService.js (신규)
│   │   ├── ttsService.js (신규)
│   │   ├── ffmpegService.js (신규)
│   │   └── aiService.js (신규)
│   └── data/
│       ├── characters/ (신규)
│       ├── fonts/ (신규)
│       ├── bgm/ (신규)
│       └── projects/
├── frontend/
│   ├── src/
│   │   ├── pages/
│   │   │   ├── ShortsCreator.jsx (신규 - 메인)
│   │   │   ├── SettingsPage.jsx (신규)
│   │   │   └── CharacterPage.jsx (신규)
│   │   ├── components/
│   │   │   ├── StepWizard/ (신규)
│   │   │   ├── VideoPreview/ (신규)
│   │   │   ├── ScriptEditor/ (신규)
│   │   │   └── SceneManager/ (신규)
│   │   └── store/
│   │       └── shortsStore.js (신규)
│   └── public/
│       ├── characters/ (10종 캐릭터 이미지)
│       ├── fonts/ (한글 폰트 파일)
│       └── bgm/ (배경음악 샘플)
```

---

## 🎯 우선순위 개발 순서

### Phase 1: 기반 구축 (현재 완료)
- ✅ 프로젝트 관리 API
- ✅ 크롤링 시스템
- ✅ 이미지 프록시
- ✅ 캐싱 시스템

### Phase 2: 캐릭터 & 설정 (진행 중)
- 캐릭터 시스템 구현
- 설정 UI 구현
- TTS 통합

### Phase 3: 스크립트 & 장면
- AI 스크립트 생성 고도화
- 장면 편집기
- 비디오 생성 통합

### Phase 4: 렌더링 & 완성
- FFmpeg 통합
- 최종 렌더링
- 메타데이터 생성

### Phase 5: 자동화 & 최적화
- 자동 모드 구현
- 성능 최적화
- UX 개선

---

## 🚀 즉시 시작할 작업

1. **캐릭터 시스템 API 구현**
2. **비디오 생성 서비스 (Minimax 통합)**
3. **TTS 서비스 구현**
4. **FFmpeg 서비스 구현**
5. **통합 UI 개발**

---

**작성일**: 2025-12-21  
**상태**: 설계 완료, 개발 준비 완료
