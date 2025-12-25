# 🚀 Zero-Install AI Studio

**설치 없이 브라우저에서 바로 실행되는 AI 이미지 생성 스튜디오**

[![Next.js](https://img.shields.io/badge/Next.js-14-black)](https://nextjs.org/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.3-blue)](https://www.typescriptlang.org/)
[![ONNX Runtime](https://img.shields.io/badge/ONNX-Runtime-green)](https://onnxruntime.ai/)
[![WebGPU](https://img.shields.io/badge/WebGPU-Enabled-purple)](https://www.w3.org/TR/webgpu/)

---

## ✨ 주요 기능

### 🎯 **Zero-Install**
- 프로그램 설치 없이 웹브라우저만으로 즉시 실행
- Python, Git, 15GB 모델 다운로드 불필요
- 15초 안에 시작 준비 완료

### 🤖 **Real AI**
- Stable Diffusion 기반 실제 AI 이미지 생성
- ONNX Runtime Web + WebGPU 가속
- Hugging Face Transformers 통합

### 💰 **완전 무료**
- 당신의 PC GPU를 활용 (서버 비용 $0)
- 월 제한 없이 무제한 생성
- 100% 오픈소스

### ⚡ **초고속**
- WebGPU 지원 브라우저에서 GPU 가속
- 3-5분 안에 고품질 이미지 완성
- 실시간 진행률 표시

---

## 🎬 데모

**🌐 Live Demo:** [http://115.91.5.140:3000](http://115.91.5.140:3000)

### 스크린샷

#### 랜딩 페이지
- 아름다운 그라데이션 애니메이션
- 실시간 WebGPU 감지
- 카운팅 통계 효과

#### AI 스튜디오
- 실시간 시스템 상태 대시보드
- 주제 입력 및 생성
- 실시간 진행률 표시
- 이미지 다운로드

---

## 🛠️ 기술 스택

### Frontend
- **Next.js 14** - React 프레임워크
- **TypeScript** - 타입 안전성
- **Tailwind CSS** - 유틸리티 CSS

### AI Engine
- **ONNX Runtime Web** - 브라우저 AI 실행
- **Hugging Face Transformers** - AI 모델 라이브러리
- **WebGPU API** - GPU 가속

### Storage
- **IndexedDB** - 모델 캐싱
- **Cache API** - 에셋 캐싱

---

## 🚀 시작하기

### 사용자용 (설치 없음!)

1. 웹사이트 방문: [http://115.91.5.140:3000](http://115.91.5.140:3000)
2. "스튜디오 열기" 클릭
3. 주제 입력 후 "생성하기" 클릭
4. 완성된 이미지 다운로드!

### 개발자용

```bash
# 1. 저장소 클론
git clone https://github.com/hompystory-coder/azamans.git
cd azamans/zero-install-ai-studio

# 2. 의존성 설치
npm install

# 3. 개발 서버 실행
npm run dev

# 4. 브라우저에서 열기
open http://localhost:3000
```

---

## 📋 시스템 요구사항

### 권장 환경
- **브라우저:** Chrome 113+ 또는 Edge 113+ (WebGPU 지원)
- **GPU:** NVIDIA/AMD GPU (선택사항, CPU 모드 지원)
- **RAM:** 최소 4GB (8GB+ 권장)

### 지원 브라우저
| 브라우저 | WebGPU | 상태 |
|---------|--------|------|
| Chrome 113+ | ✅ | 완전 지원 |
| Edge 113+ | ✅ | 완전 지원 |
| Firefox | ⚠️ | 실험적 지원 |
| Safari | ⏳ | 개발 중 |

---

## 🎨 사용 예제

### 기본 이미지 생성

```typescript
import { getImageGenerator } from '@/lib/ai-engine'

const generator = getImageGenerator()

await generator.initialize()

const imageUrl = await generator.generate({
  prompt: 'a beautiful landscape with mountains',
  negativePrompt: 'blurry, low quality',
  width: 512,
  height: 512,
  steps: 30
})
```

### 진행률 모니터링

```typescript
const imageUrl = await generator.generate(
  { prompt: 'cute cat' },
  (stage, percent) => {
    console.log(`${stage}: ${percent}%`)
  }
)
```

---

## 🏗️ 프로젝트 구조

```
zero-install-ai-studio/
├── app/                    # Next.js App Router
│   ├── page.tsx           # 랜딩 페이지
│   ├── studio/            
│   │   └── page.tsx       # AI 스튜디오
│   ├── layout.tsx         # 루트 레이아웃
│   └── globals.css        # 글로벌 스타일
├── lib/                   # 라이브러리
│   └── ai-engine.ts       # AI 엔진 코어
├── components/            # 재사용 컴포넌트 (예정)
├── public/                # 정적 파일
├── package.json           # 의존성
├── next.config.js         # Next.js 설정
├── tailwind.config.js     # Tailwind 설정
└── tsconfig.json          # TypeScript 설정
```

---

## 🔧 개발 로드맵

### ✅ Phase 1-3 완료 (60%)
- [x] Next.js 14 프로젝트 설정
- [x] 랜딩 페이지 UI/UX
- [x] 스튜디오 대시보드
- [x] WebGPU 감지 시스템
- [x] ONNX Runtime Web 통합
- [x] AI 이미지 생성 엔진
- [x] IndexedDB 캐싱

### 🚧 Phase 4-6 진행 중 (40%)
- [ ] 로컬 ONNX 모델 통합
- [ ] 비디오 생성 (AnimateDiff)
- [ ] TTS 음성 생성
- [ ] FFmpeg.wasm 비디오 렌더링
- [ ] 사용자 인증 시스템
- [ ] 결제 통합 (Stripe)

### 📅 Phase 7-9 예정
- [ ] Vercel 배포
- [ ] 커스텀 도메인
- [ ] 성능 최적화
- [ ] 모바일 최적화
- [ ] PWA 지원
- [ ] 베타 테스트

---

## 🤝 기여하기

기여를 환영합니다! 다음과 같이 기여할 수 있습니다:

1. Fork the Project
2. Create Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit Changes (`git commit -m 'Add AmazingFeature'`)
4. Push to Branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📝 라이선스

MIT License - 자유롭게 사용, 수정, 배포 가능

---

## 🙏 감사의 말

- **Hugging Face** - AI 모델 및 Transformers 라이브러리
- **Microsoft** - ONNX Runtime
- **Vercel** - Next.js 프레임워크
- **Stability AI** - Stable Diffusion 모델

---

## 📞 연락처

- **GitHub**: [hompystory-coder/azamans](https://github.com/hompystory-coder/azamans)
- **Email**: support@zero-install-ai.studio
- **Demo**: [http://115.91.5.140:3000](http://115.91.5.140:3000)

---

<div align="center">

**Made with 💜 by Genius AI Team**

⭐ 이 프로젝트가 마음에 드시나요? Star를 눌러주세요!

</div>
