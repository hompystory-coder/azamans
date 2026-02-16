# 📦 설치 가이드

## 🎯 시스템 요구사항

### 최소 사양
- **OS**: Windows 10/11, macOS 12+, Ubuntu 20.04+
- **CPU**: Intel i5 8세대 이상 / AMD Ryzen 5
- **RAM**: 16GB
- **GPU**: NVIDIA GTX 1660 (6GB VRAM) 이상
- **저장소**: 50GB SSD 여유 공간
- **인터넷**: 모델 다운로드용 (최초 1회)

### 권장 사양
- **CPU**: Intel i7 10세대 이상 / AMD Ryzen 7
- **RAM**: 32GB
- **GPU**: NVIDIA RTX 3060 (12GB VRAM) 이상
- **저장소**: 100GB NVMe SSD

### 최적 사양
- **CPU**: Intel i9 12세대 이상 / AMD Ryzen 9
- **RAM**: 64GB
- **GPU**: NVIDIA RTX 4070 Ti (16GB VRAM) 이상
- **저장소**: 200GB NVMe SSD

---

## 🚀 빠른 설치 (5분)

### 1. Python 설치 (3.8 이상)

#### Windows
```bash
# Python 3.10 다운로드
https://www.python.org/downloads/

# 설치 시 "Add Python to PATH" 체크 필수!
```

#### macOS
```bash
brew install python@3.10
```

#### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install python3.10 python3.10-venv python3-pip
```

---

### 2. 프로젝트 클론

```bash
# Git 클론
git clone <repository-url>
cd local-shorts-system

# 또는 ZIP 다운로드 후 압축 해제
```

---

### 3. 가상환경 생성 및 활성화

#### Windows
```bash
python -m venv venv
venv\Scripts\activate
```

#### macOS / Linux
```bash
python3 -m venv venv
source venv/bin/activate
```

활성화되면 프롬프트 앞에 `(venv)`가 표시됩니다.

---

### 4. PyTorch 설치 (GPU 지원)

#### NVIDIA GPU 있는 경우 (권장)

**CUDA 11.8** (대부분의 최신 GPU 지원):
```bash
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118
```

**CUDA 12.1** (RTX 40 시리즈 등):
```bash
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu121
```

#### CPU만 있는 경우
```bash
pip install torch torchvision torchaudio
```

**설치 확인:**
```bash
python -c "import torch; print(f'PyTorch: {torch.__version__}'); print(f'CUDA available: {torch.cuda.is_available()}')"
```

---

### 5. 의존성 설치

```bash
pip install -r requirements.txt
```

이 과정은 5-10분 소요됩니다.

---

### 6. AI 모델 다운로드 (최초 1회)

```bash
python scripts/install_models.py
```

**다운로드되는 모델:**
- ✅ Stable Diffusion XL (6.9 GB) - 이미지 생성
- ✅ Coqui TTS XTTS-v2 (2 GB) - 음성 합성
- ⚠️ Ollama LLaMA 3.1 (4.7 GB) - LLM (선택)

**예상 소요 시간:**
- 빠른 인터넷: 20-30분
- 보통 인터넷: 30-60분

**디스크 공간:** 총 ~15GB 필요

---

### 7. Ollama 설치 (선택, LLM용)

AI 스크립트 자동 생성 기능을 사용하려면 Ollama를 설치하세요.

#### Windows
```bash
winget install Ollama.Ollama
```

또는 https://ollama.com/download 에서 설치 프로그램 다운로드

#### macOS
```bash
brew install ollama
```

#### Linux
```bash
curl -fsSL https://ollama.com/install.sh | sh
```

**LLaMA 3.1 모델 다운로드:**
```bash
ollama pull llama3.1:8b
```

---

## ✅ 설치 검증

### 시스템 정보 확인
```bash
python scripts/install_models.py --verify
```

### 백엔드 서버 실행 테스트
```bash
cd backend
python app.py
```

브라우저에서 http://localhost:8000 접속하여 확인

**성공 시 출력:**
```json
{
  "name": "Local AI Shorts Generator",
  "version": "0.1.0",
  "status": "running",
  "device": "cuda"
}
```

---

## 🔧 문제 해결

### "CUDA not available" 오류

**원인:** GPU 드라이버 또는 CUDA 버전 불일치

**해결:**
1. NVIDIA 드라이버 최신 버전 설치: https://www.nvidia.com/drivers
2. PyTorch CUDA 버전 확인:
   ```bash
   python -c "import torch; print(torch.version.cuda)"
   ```
3. 맞는 버전 재설치

---

### "Out of Memory" 오류

**원인:** GPU VRAM 부족

**해결:**
1. 다른 프로그램 종료
2. 배치 크기 줄이기
3. 해상도 낮추기
4. 모델 양자화 사용

---

### 모델 다운로드 느림/실패

**원인:** 네트워크 문제 또는 Hugging Face 서버 부하

**해결:**
1. VPN 사용 (중국/일부 국가)
2. Hugging Face 미러 사용:
   ```bash
   export HF_ENDPOINT=https://hf-mirror.com
   ```
3. 다시 시도

---

### FFmpeg 오류

**원인:** FFmpeg 미설치

**해결:**

#### Windows
```bash
# Chocolatey 사용
choco install ffmpeg

# 또는 https://ffmpeg.org/download.html 에서 다운로드
```

#### macOS
```bash
brew install ffmpeg
```

#### Linux
```bash
sudo apt install ffmpeg
```

---

## 🎓 다음 단계

설치 완료 후:

1. [사용자 가이드](USER_GUIDE.md) 읽기
2. [API 문서](API.md) 확인
3. 첫 쇼츠 생성해보기:
   ```bash
   cd backend
   python app.py
   
   # 다른 터미널에서
   curl -X POST http://localhost:8000/api/shorts/generate \
     -H "Content-Type: application/json" \
     -d '{"url": "https://example.com/product", "character_id": "executive-fox"}'
   ```

---

## 📞 도움이 필요하신가요?

- 📖 [문서](../README.md)
- 🐛 [이슈 리포트](https://github.com/your-repo/issues)
- 💬 [디스코드 커뮤니티](https://discord.gg/your-invite)

---

**설치 성공을 축하합니다!** 🎉
