# 🚀 로컬 PC AI 쇼츠 생성 - 빠른 시작

## ✨ 핵심 정보

**목표**: 사용자 PC의 GPU를 활용하여 API 비용 없이 AI 쇼츠 생성

**현재 상태**: ✅ Phase 1 완료 (50%) - 이미지 + 음성 생성 가능

**예상 비용 절감**: 월 $100+ → $10 (전기료만)

---

## 📋 체크리스트

### 시스템 요구사항
- [ ] NVIDIA GPU (GTX 1660 6GB 이상)
- [ ] 16GB RAM 이상
- [ ] 50GB 디스크 여유 공간
- [ ] Python 3.8+ 설치
- [ ] 인터넷 연결 (최초 모델 다운로드)

### 설치 단계
- [ ] 프로젝트 클론
- [ ] Python 가상환경 생성
- [ ] PyTorch (CUDA) 설치
- [ ] 의존성 설치 (`requirements.txt`)
- [ ] AI 모델 다운로드 (~15GB)
- [ ] Ollama 설치 (선택)

---

## ⚡ 5분 설치

```bash
# 1. 클론
git clone <repo>
cd local-shorts-system

# 2. 가상환경
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate

# 3. PyTorch (CUDA 11.8)
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118

# 4. 의존성
pip install -r requirements.txt

# 5. 모델 다운로드 (20-30분)
python scripts/install_models.py

# 6. 서버 실행
cd backend
python app.py
```

---

## 🎨 현재 사용 가능한 기능

### ✅ 이미지 생성 (Stable Diffusion XL)
```python
from models.image_generator import ImageGenerator
from pathlib import Path

gen = ImageGenerator(Path('../models'))
gen.load_model()

# 캐릭터 이미지 생성
images = gen.generate_character(
    character_id="executive-fox",
    prompt="premium 3D fox in business suit...",
    num_images=1
)
print(f"Generated: {images}")
```

**성능**: 5-10초/이미지 (RTX 3060)

---

### ✅ 음성 합성 (Coqui TTS)
```python
from models.tts_generator import TTSGenerator
from pathlib import Path

tts = TTSGenerator(Path('../models'))
tts.load_model()

# 한국어 음성 생성
audio = tts.generate_speech(
    text="안녕하세요! 프리미엄 제품을 소개합니다.",
    language="ko"
)
print(f"Generated: {audio}")
```

**성능**: 실시간의 2-3배

---

### ✅ API 서버
```bash
# 서버 실행
cd backend
python app.py

# 브라우저에서 확인
# http://localhost:8000
```

**엔드포인트**:
- `GET /` - 루트
- `GET /health` - 헬스 체크
- `GET /api/system/info` - 시스템 정보
- `GET /api/characters` - 39개 캐릭터 목록
- `POST /api/models/install` - 모델 설치
- `GET /api/models/status` - 모델 상태

---

## 🚧 진행 중인 기능

### ⏳ 비디오 생성 (AnimateDiff)
- Image-to-Video 변환
- 5-10초 클립
- 9:16 세로 영상

### ⏳ LLM 스크립트 생성 (Ollama)
- 자동 스크립트 작성
- 캐릭터 페르소나 적용

### ⏳ 최종 렌더링 (FFmpeg)
- 비디오 + 오디오 합성
- 자막 추가
- 배경음악

**예상 완성**: 1주일 내

---

## 📊 비교

| 항목 | API 기반 | 로컬 PC 기반 |
|------|----------|-------------|
| **초기 비용** | $0 | GPU 하드웨어 |
| **월 비용** (100개) | $60-120 | $5-10 |
| **연간 비용** | $720-1,440 | $60-120 |
| **생성 시간** | 2-3분 | 4-5분 |
| **프라이버시** | 외부 전송 | 완전 로컬 |
| **제한** | API 한도 | 무제한 |
| **커스터마이징** | 제한적 | 자유로움 |

---

## 🎯 사용 사례

### 1. 이미지 테스트
```bash
cd backend
python -m models.image_generator
```

### 2. 음성 테스트
```bash
cd backend
python -m models.tts_generator
```

### 3. API 테스트
```bash
# 터미널 1: 서버 실행
cd backend && python app.py

# 터미널 2: API 호출
curl http://localhost:8000/api/system/info
curl http://localhost:8000/api/characters
```

---

## 🐛 문제 해결

### GPU 인식 안 됨
```bash
python -c "import torch; print(torch.cuda.is_available())"
# False면 NVIDIA 드라이버 재설치
```

### Out of Memory
- 다른 프로그램 종료
- 이미지 해상도 낮추기 (512x512)
- `num_inference_steps` 줄이기 (30 → 20)

### 모델 다운로드 느림
- VPN 사용
- Hugging Face 미러:
  ```bash
  export HF_ENDPOINT=https://hf-mirror.com
  ```

---

## 📚 문서

- [시스템 설계](../LOCAL_PC_SHORTS_SYSTEM.md)
- [설치 가이드](docs/INSTALLATION.md)
- [진행 상황](PROGRESS_REPORT.md)

---

## 🎉 다음 단계

1. ⏳ AnimateDiff 비디오 생성 구현
2. ⏳ Ollama LLM 통합
3. ⏳ FFmpeg 렌더링
4. ⏳ 전체 워크플로우 통합
5. ⏳ Electron 데스크톱 UI

**목표**: 완전 자동화된 로컬 쇼츠 생성 시스템 완성! 🚀

---

**업데이트**: 2025-12-25  
**상태**: Phase 1 완료 (50%)
