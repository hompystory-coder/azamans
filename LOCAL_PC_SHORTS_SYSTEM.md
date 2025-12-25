# 🖥️ 로컬 PC 리소스 기반 AI 쇼츠 생성 시스템

## 📋 프로젝트 목표

**사용자 PC의 CPU/GPU를 활용하여 API 비용 없이 AI 쇼츠를 생성하는 완전 로컬 시스템**

### 핵심 요구사항
- ✅ **100% 로컬 실행** - 인터넷 API 의존성 제거
- ✅ **오픈소스 모델** - 무료로 사용 가능한 AI 모델
- ✅ **사용자 친화적** - 간단한 설치 및 사용
- ✅ **성능 최적화** - 일반 PC에서도 실행 가능
- ✅ **완전 자동화** - URL 입력 → 쇼츠 완성

---

## 🏗️ 시스템 아키텍처

```
[사용자 PC]
├── 웹 브라우저 (UI)
├── 로컬 서버 (Node.js/Python)
├── GPU/CPU 처리
│   ├── 비디오 생성 (Diffusion 모델)
│   ├── 이미지 생성 (Stable Diffusion)
│   ├── 음성 합성 (TTS)
│   └── 스크립트 생성 (LLM)
└── 저장소 (로컬 디스크)
```

---

## 🤖 오픈소스 AI 모델 스택

### 1. 📹 비디오 생성 (Text/Image-to-Video)

#### **추천: AnimateDiff + Stable Diffusion**
- **장점**: 
  - 완전 오픈소스
  - 로컬 GPU 실행
  - 4-8GB VRAM으로 실행 가능
  - Pixar/애니메이션 스타일 지원
- **모델**: 
  - `AnimateDiff` (비디오 모션)
  - `Stable Diffusion 1.5/2.1` (이미지 베이스)
- **프레임워크**: ComfyUI, Automatic1111, Diffusers
- **생성 시간**: 30-120초 (5초 영상, RTX 3060 기준)

#### **대안 1: Zeroscope V2 XL**
- Hugging Face 무료 모델
- Text-to-Video 생성
- 3-5초 클립 생성

#### **대안 2: ModelScope Text-to-Video**
- Alibaba 오픈소스 모델
- 가벼운 모델 (4GB VRAM)
- 빠른 생성 속도

---

### 2. 🎨 이미지 생성 (캐릭터/제품)

#### **추천: Stable Diffusion XL (SDXL)**
- **장점**:
  - 최고 품질 오픈소스 모델
  - Pixar/3D 스타일 LoRA 지원
  - 로컬 실행 최적화
- **모델**: 
  - `SDXL 1.0` (메인)
  - `Disney Pixar LoRA` (캐릭터 스타일)
  - `3D Animation LoRA` (3D 렌더링)
- **VRAM**: 6-10GB
- **생성 시간**: 3-10초/이미지

#### **대안: Stable Diffusion 1.5 + ControlNet**
- 4GB VRAM으로 실행 가능
- 빠른 생성 속도
- 다양한 커뮤니티 모델

---

### 3. 🎙️ 음성 합성 (TTS)

#### **추천: Coqui TTS (XTTS-v2)**
- **장점**:
  - 완전 오픈소스
  - 한국어 완벽 지원
  - 음성 클로닝 가능
  - CPU 실행 가능
- **모델**: `XTTS-v2`
- **품질**: ElevenLabs 수준
- **생성 속도**: 실시간의 2-3배 (CPU)

#### **대안 1: Bark (Suno AI)**
- 다국어 지원
- 감정 표현 우수
- GPU 권장 (CPU 가능)

#### **대안 2: Piper TTS**
- 초경량 (CPU 최적화)
- 50개+ 언어
- 빠른 속도

---

### 4. 💬 스크립트 생성 (LLM)

#### **추천: LLaMA 3.1 8B (Ollama)**
- **장점**:
  - 완전 로컬 실행
  - 한국어 우수
  - 8GB VRAM or 16GB RAM
  - Ollama로 간편 설치
- **모델**: `llama3.1:8b`
- **속도**: 20-40 토큰/초

#### **대안 1: Qwen 2.5 7B**
- 한국어/중국어 특화
- 경량 모델
- 빠른 추론

#### **대안 2: Gemma 2 9B**
- Google 오픈소스
- 상업적 사용 가능
- 우수한 품질

---

## 💻 시스템 요구사항

### 최소 사양 (저사양 PC)
- **CPU**: Intel i5 8세대 이상 / AMD Ryzen 5
- **RAM**: 16GB
- **GPU**: GTX 1660 (6GB VRAM) 이상
- **저장소**: 50GB SSD
- **예상 속도**: 5-10분/쇼츠

### 권장 사양 (중급 PC)
- **CPU**: Intel i7 10세대 이상 / AMD Ryzen 7
- **RAM**: 32GB
- **GPU**: RTX 3060 (12GB VRAM) 이상
- **저장소**: 100GB NVMe SSD
- **예상 속도**: 2-5분/쇼츠

### 최적 사양 (고사양 PC)
- **CPU**: Intel i9 12세대 이상 / AMD Ryzen 9
- **RAM**: 64GB
- **GPU**: RTX 4070 Ti (16GB VRAM) 이상
- **저장소**: 200GB NVMe SSD
- **예상 속도**: 1-3분/쇼츠

---

## 🛠️ 기술 스택

### 백엔드
- **Python 3.10+**: AI 모델 실행
- **PyTorch 2.0+**: 딥러닝 프레임워크
- **Diffusers**: Stable Diffusion 라이브러리
- **FFmpeg**: 비디오 편집
- **Ollama**: LLM 실행 엔진

### 프론트엔드
- **Electron**: 데스크톱 앱 (크로스 플랫폼)
- **React**: UI 프레임워크
- **Tauri** (대안): 경량 데스크톱 앱

### 모델 관리
- **Hugging Face Hub**: 모델 다운로드
- **ComfyUI** (선택): 비주얼 워크플로우

---

## 📦 설치 및 실행 플로우

### 1단계: 환경 설정
```bash
# Python 가상환경 생성
python -m venv shorts-env
source shorts-env/bin/activate  # Windows: shorts-env\Scripts\activate

# 의존성 설치
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118
pip install diffusers transformers accelerate
pip install TTS  # Coqui TTS
pip install opencv-python moviepy
```

### 2단계: 모델 다운로드
```python
# 자동 다운로드 스크립트
from diffusers import StableDiffusionPipeline
from TTS.api import TTS

# Stable Diffusion 다운로드 (최초 1회)
sd_pipe = StableDiffusionPipeline.from_pretrained(
    "stabilityai/stable-diffusion-xl-base-1.0"
)

# TTS 다운로드
tts = TTS("tts_models/multilingual/multi-dataset/xtts_v2")

# LLM 다운로드 (Ollama)
# ollama pull llama3.1:8b
```

### 3단계: 로컬 서버 실행
```bash
# 백엔드 서버 시작
cd local-shorts-backend
python app.py

# 프론트엔드 앱 실행
cd local-shorts-frontend
npm run electron:serve
```

---

## 🎬 워크플로우

### 전체 프로세스
```
1. 사용자 입력 (URL 또는 텍스트)
   ↓
2. 크롤링 (로컬 Puppeteer)
   ↓
3. AI 스크립트 생성 (로컬 LLM - LLaMA 3.1)
   ↓
4. 이미지 생성 (로컬 GPU - Stable Diffusion)
   ↓ (39개 캐릭터)
5. 음성 합성 (로컬 CPU/GPU - Coqui TTS)
   ↓
6. 비디오 생성 (로컬 GPU - AnimateDiff)
   ↓
7. 최종 렌더링 (FFmpeg - 자막 + 배경음악)
   ↓
8. 완성된 쇼츠 (로컬 저장)
```

### 예상 소요 시간 (RTX 3060 기준)
- 크롤링: 5초
- 스크립트 생성: 30초
- 캐릭터 이미지 생성: 30초 (5장)
- 음성 합성: 20초
- 비디오 생성: 120초 (5초 영상)
- 렌더링: 30초
- **총 소요시간: 약 4분**

---

## 🚀 최적화 전략

### 1. 모델 양자화 (Quantization)
- **INT8/INT4 양자화**: VRAM 사용량 50% 감소
- **4-bit LLM**: 8GB RAM으로 실행 가능
- **속도 향상**: 20-30%

### 2. 배치 처리
- 여러 이미지 동시 생성
- TTS 병렬 처리
- GPU 유휴 시간 최소화

### 3. 캐싱
- 캐릭터 이미지 재사용
- 모델 메모리 상주
- 자주 쓰는 프롬프트 캐시

### 4. 점진적 렌더링
- 장면별 생성 후 즉시 표시
- 사용자 경험 향상
- 중단 및 재개 가능

---

## 📊 비용 비교

### API 기반 시스템 (현재)
- **비디오 생성**: $0.50-1.00 per video
- **TTS**: $0.015 per minute
- **이미지**: $0.02-0.04 per image
- **LLM**: $0.001-0.005 per request
- **월 100개 쇼츠**: $60-120

### 로컬 PC 시스템 (제안)
- **초기 투자**: PC 하드웨어 (이미 보유)
- **전기료**: ~$5-10/월 (고강도 사용)
- **월 100개 쇼츠**: $5-10
- **ROI**: 1-2개월 후 손익분기점

---

## ✅ 장점

1. **비용 절감**: API 비용 $0
2. **프라이버시**: 데이터 외부 전송 없음
3. **무제한 생성**: 제한 없음
4. **커스터마이징**: 모델 미세조정 가능
5. **오프라인 작동**: 인터넷 불필요
6. **데이터 소유**: 완전한 통제권

---

## ⚠️ 도전 과제

1. **하드웨어 요구사항**: GPU 필수
2. **초기 설치 복잡도**: 모델 다운로드 필요
3. **생성 속도**: API보다 느림
4. **품질**: 최신 API 모델보다 약간 낮을 수 있음
5. **유지보수**: 모델 업데이트 관리

---

## 🎯 구현 단계

### Phase 1: 프로토타입 (1-2주)
- [ ] 환경 설정 및 모델 다운로드
- [ ] Stable Diffusion 이미지 생성 테스트
- [ ] Coqui TTS 음성 합성 테스트
- [ ] LLaMA 3.1 스크립트 생성 테스트
- [ ] 간단한 CLI 도구 구현

### Phase 2: 비디오 생성 (2-3주)
- [ ] AnimateDiff 통합
- [ ] Image-to-Video 파이프라인
- [ ] FFmpeg 렌더링 파이프라인
- [ ] 자막 자동 생성

### Phase 3: 통합 및 UI (2-3주)
- [ ] Electron 데스크톱 앱
- [ ] 사용자 친화적 UI
- [ ] 프로그레스 바 및 로그
- [ ] 설정 관리

### Phase 4: 최적화 (1-2주)
- [ ] 모델 양자화
- [ ] 배치 처리
- [ ] 캐싱 전략
- [ ] 성능 프로파일링

### Phase 5: 배포 (1주)
- [ ] 인스톨러 생성 (Windows, macOS, Linux)
- [ ] 자동 업데이트 시스템
- [ ] 사용자 문서
- [ ] 비디오 튜토리얼

---

## 🔧 핵심 코드 구조

```
local-shorts-system/
├── backend/
│   ├── models/
│   │   ├── video_generator.py      # AnimateDiff
│   │   ├── image_generator.py      # Stable Diffusion
│   │   ├── tts_generator.py        # Coqui TTS
│   │   └── script_generator.py     # LLaMA 3.1
│   ├── services/
│   │   ├── crawler_service.py      # 웹 크롤링
│   │   ├── render_service.py       # FFmpeg 렌더링
│   │   └── pipeline_service.py     # 전체 파이프라인
│   ├── app.py                       # FastAPI 서버
│   └── config.yaml                  # 설정 파일
├── frontend/
│   ├── electron/
│   │   ├── main.js                  # Electron 메인
│   │   └── preload.js
│   ├── src/
│   │   ├── App.vue                  # 메인 UI
│   │   ├── components/
│   │   │   ├── InputForm.vue        # URL 입력
│   │   │   ├── CharacterSelector.vue # 캐릭터 선택
│   │   │   ├── ProgressView.vue     # 진행 상황
│   │   │   └── VideoPreview.vue     # 결과 미리보기
│   │   └── services/
│   │       └── api.js               # 백엔드 통신
│   └── package.json
├── models/                           # 다운로드된 AI 모델
│   ├── sd-xl/
│   ├── animatediff/
│   ├── xtts-v2/
│   └── llama-3.1-8b/
├── scripts/
│   ├── install_models.py            # 모델 자동 설치
│   ├── benchmark.py                 # 성능 테스트
│   └── optimize.py                  # 최적화 도구
├── output/                           # 생성된 쇼츠
├── requirements.txt
└── README.md
```

---

## 📚 참고 자료

### 오픈소스 프로젝트
- **AnimateDiff**: https://github.com/guoyww/AnimateDiff
- **Stable Diffusion**: https://github.com/Stability-AI/stablediffusion
- **Coqui TTS**: https://github.com/coqui-ai/TTS
- **Ollama**: https://ollama.com/
- **ComfyUI**: https://github.com/comfyanonymous/ComfyUI

### 모델 허브
- **Hugging Face**: https://huggingface.co/models
- **Civitai**: https://civitai.com/ (Stable Diffusion 커뮤니티)

---

## 🎉 결론

**로컬 PC 기반 AI 쇼츠 생성 시스템은 실현 가능하며, 다음과 같은 이점을 제공합니다:**

1. ✅ **완전한 독립성**: API 의존 없음
2. ✅ **비용 효율성**: 장기적으로 매우 저렴
3. ✅ **프라이버시 보장**: 데이터 외부 유출 없음
4. ✅ **무제한 생성**: 제한 없는 쇼츠 제작
5. ✅ **커스터마이징**: 자유로운 모델 조정

**이 시스템을 반드시 성공시키겠습니다!** 🚀

---

**작성일**: 2025-12-25  
**목표**: 로컬 PC 리소스 활용 쇼츠 생성 시스템 구축  
**상태**: 설계 완료, 구현 시작 준비
