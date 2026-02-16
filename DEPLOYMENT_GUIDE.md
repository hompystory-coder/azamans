# 🚀 로컬 PC AI 쇼츠 생성 시스템 - 배포 및 사용 가이드

**날짜**: 2025-12-25  
**버전**: 1.0.0  
**상태**: ✅ 프로덕션 준비 완료

---

## 📋 목차

1. [시스템 요구사항](#시스템-요구사항)
2. [설치 방법](#설치-방법)
3. [실행 방법](#실행-방법)
4. [사용 방법](#사용-방법)
5. [트러블슈팅](#트러블슈팅)
6. [FAQ](#faq)

---

## 🖥️ 시스템 요구사항

### **필수 요구사항**

```
GPU:        NVIDIA GTX 1660 6GB 이상 (RTX 3060 12GB 권장)
RAM:        16 GB 이상
디스크:     50 GB 여유 공간 (AI 모델 15.3GB + 출력 파일)
OS:         Windows 10/11, Linux, macOS (Intel/Apple Silicon)
Python:     Python 3.8 - 3.11 (3.10 권장)
CUDA:       11.8 이상 (NVIDIA GPU 사용 시)
```

### **권장 사양**

```
GPU:        NVIDIA RTX 3060 12GB 이상
RAM:        32 GB
CPU:        Intel i5 / AMD Ryzen 5 이상 (6코어+)
디스크:     SSD 100 GB+
```

---

## 📥 설치 방법

### **Step 1: 사전 준비**

#### **1.1 Python 설치 확인**
```bash
python --version
# Python 3.8 - 3.11 필요 (3.10 권장)
```

Python이 없다면:
- **Windows**: https://www.python.org/downloads/
- **Linux**: `sudo apt install python3.10 python3.10-venv`
- **macOS**: `brew install python@3.10`

#### **1.2 Git 설치 확인**
```bash
git --version
```

Git이 없다면: https://git-scm.com/downloads

#### **1.3 NVIDIA GPU 드라이버 (선택)**
NVIDIA GPU 사용 시:
- 최신 드라이버 다운로드: https://www.nvidia.com/drivers
- CUDA Toolkit 11.8: https://developer.nvidia.com/cuda-11-8-0-download-archive

---

### **Step 2: 프로젝트 다운로드**

```bash
# 프로젝트 디렉토리로 이동
cd /home/azamans/webapp

# 또는 새로운 위치에 클론
# git clone <repository-url>
# cd <repository-name>

# local-shorts-system 디렉토리로 이동
cd local-shorts-system
```

---

### **Step 3: Python 가상환경 생성**

```bash
# 가상환경 생성
python -m venv venv

# 가상환경 활성화
# Linux/macOS:
source venv/bin/activate

# Windows:
venv\Scripts\activate

# 확인 (프롬프트에 (venv) 표시됨)
which python  # Linux/macOS
where python  # Windows
```

---

### **Step 4: PyTorch 설치 (GPU 가속)**

#### **NVIDIA GPU 있는 경우 (권장)**
```bash
# CUDA 11.8 버전
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118

# CUDA 12.1 버전 (최신 GPU)
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu121
```

#### **GPU 없는 경우 (CPU만)**
```bash
pip install torch torchvision torchaudio
```

#### **macOS (Apple Silicon)**
```bash
pip install torch torchvision torchaudio
```

**설치 확인:**
```bash
python -c "import torch; print(f'PyTorch: {torch.__version__}'); print(f'CUDA available: {torch.cuda.is_available()}')"
```

---

### **Step 5: 의존성 설치**

```bash
# requirements.txt 의존성 설치
pip install -r requirements.txt

# 설치 확인
pip list | grep -E "fastapi|diffusers|TTS|ollama"
```

**주요 패키지:**
- `fastapi` - 백엔드 서버
- `diffusers` - Stable Diffusion / AnimateDiff
- `TTS` - Coqui TTS 음성 합성
- `playwright` - 웹 크롤링
- `ffmpeg-python` - 비디오 렌더링

---

### **Step 6: FFmpeg 설치**

#### **Linux (Ubuntu/Debian)**
```bash
sudo apt update
sudo apt install ffmpeg
ffmpeg -version
```

#### **macOS**
```bash
brew install ffmpeg
ffmpeg -version
```

#### **Windows**
1. https://www.gyan.dev/ffmpeg/builds/ 다운로드
2. 압축 해제 후 `bin` 폴더를 PATH에 추가
3. `ffmpeg -version` 확인

---

### **Step 7: AI 모델 다운로드 (15.3 GB)**

```bash
# 모델 자동 다운로드 스크립트 실행 (20-30분 소요)
python scripts/install_models.py

# 다운로드 진행 상황:
# [1/4] Stable Diffusion XL (6.9 GB) ...
# [2/4] AnimateDiff (1.7 GB) ...
# [3/4] Coqui TTS (2.0 GB) ...
# [4/4] 완료!
```

**수동 다운로드 (선택):**
```python
# Python 인터프리터에서
from diffusers import StableDiffusionXLPipeline
StableDiffusionXLPipeline.from_pretrained(
    "stabilityai/stable-diffusion-xl-base-1.0",
    cache_dir="./models"
)
```

---

### **Step 8: Ollama 설치 (LLM)**

#### **자동 설치 (권장)**
```bash
# Linux/macOS
curl -fsSL https://ollama.ai/install.sh | sh

# Windows
# https://ollama.ai/download 에서 다운로드
```

#### **LLaMA 3.1 모델 다운로드 (4.7 GB)**
```bash
ollama pull llama3.1:8b

# 확인
ollama list
```

**Ollama 서버 시작:**
```bash
# 백그라운드 실행
ollama serve &

# 확인
curl http://localhost:11434/api/tags
```

---

### **Step 9: Playwright 브라우저 설치**

```bash
# Playwright 브라우저 다운로드
playwright install chromium

# 또는 전체 브라우저
playwright install
```

---

## ✅ 설치 완료 확인

```bash
# 모든 구성 요소 확인
python -c "
import torch
from diffusers import StableDiffusionXLPipeline
from TTS.api import TTS
import requests

print('✅ PyTorch:', torch.__version__)
print('✅ CUDA:', torch.cuda.is_available())
print('✅ Diffusers: OK')
print('✅ TTS: OK')

# Ollama 확인
try:
    resp = requests.get('http://localhost:11434/api/tags', timeout=5)
    print('✅ Ollama:', resp.status_code == 200)
except:
    print('❌ Ollama: Not running')
"
```

**예상 출력:**
```
✅ PyTorch: 2.1.0+cu118
✅ CUDA: True
✅ Diffusers: OK
✅ TTS: OK
✅ Ollama: True
```

---

## 🚀 실행 방법

### **Step 1: 터미널 열기**

```bash
# 프로젝트 디렉토리로 이동
cd /home/azamans/webapp/local-shorts-system

# 가상환경 활성화
source venv/bin/activate  # Linux/macOS
# venv\Scripts\activate   # Windows
```

---

### **Step 2: Ollama 서버 시작 (별도 터미널)**

```bash
# 터미널 1: Ollama 서버
ollama serve
```

---

### **Step 3: FastAPI 백엔드 서버 시작**

```bash
# 터미널 2: 백엔드 서버
cd backend
python app.py

# 서버 시작 로그:
# 🚀 Starting Local AI Shorts Generator Backend
# 🔧 Device: cuda
# 🎮 GPU: NVIDIA GeForce RTX 3060
# 💾 VRAM: 12.0 GB
# INFO:     Uvicorn running on http://0.0.0.0:8000
```

---

### **Step 4: 브라우저 열기**

```
http://localhost:8000
```

**API 문서 (Swagger UI):**
```
http://localhost:8000/docs
```

---

## 🎬 사용 방법

### **방법 1: API 호출 (cURL)**

#### **1. 시스템 정보 확인**
```bash
curl http://localhost:8000/api/system/info | jq
```

**응답:**
```json
{
  "device": "cuda",
  "gpu_name": "NVIDIA GeForce RTX 3060",
  "vram_gb": 12.0,
  "models_downloaded": true,
  "status": "ready"
}
```

---

#### **2. 캐릭터 목록 조회**
```bash
curl http://localhost:8000/api/characters | jq
```

**응답:**
```json
{
  "total": 30,
  "categories": {
    "business": [
      {"id": "executive-fox", "name": "🦊 이그제큐티브 폭스", "category": "business"},
      {"id": "ceo-lion", "name": "🦁 CEO 라이온", "category": "business"}
    ],
    "tech": [...],
    ...
  }
}
```

---

#### **3. 쇼츠 생성 시작**

```bash
curl -X POST http://localhost:8000/api/shorts/generate \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://shopping.naver.com/example-product",
    "character_id": "executive-fox",
    "duration": 15
  }' | jq
```

**응답:**
```json
{
  "job_id": "shorts_1703512345_abc123",
  "status": "pending",
  "message": "쇼츠 생성이 시작되었습니다. 5-10분 소요됩니다.",
  "estimated_time": "5-10분"
}
```

---

#### **4. 상태 확인**

```bash
# job_id를 사용하여 상태 확인
curl http://localhost:8000/api/shorts/status/shorts_1703512345_abc123 | jq
```

**응답 (진행 중):**
```json
{
  "job_id": "shorts_1703512345_abc123",
  "status": "processing",
  "progress": 45,
  "message": "비디오 생성 중... (3/5)",
  "output_path": null,
  "error": null
}
```

**응답 (완료):**
```json
{
  "job_id": "shorts_1703512345_abc123",
  "status": "completed",
  "progress": 100,
  "message": "쇼츠 생성 완료!",
  "output_path": "/home/azamans/webapp/local-shorts-system/output/videos/shorts_1703512345_abc123.mp4",
  "error": null
}
```

---

#### **5. 완성된 쇼츠 다운로드**

```bash
# 다운로드
curl -O http://localhost:8000/api/shorts/download/shorts_1703512345_abc123

# 파일 확인
ls -lh shorts_1703512345_abc123.mp4
# -rw-rw-r-- 1 user user 45M Dec 25 12:34 shorts_1703512345_abc123.mp4
```

---

### **방법 2: Python 스크립트**

```python
# generate_shorts.py
import requests
import time
import json

API_BASE = "http://localhost:8000"

def generate_shorts(url, character_id="executive-fox"):
    """쇼츠 생성 및 다운로드"""
    
    # 1. 쇼츠 생성 시작
    print(f"🎬 쇼츠 생성 시작...")
    response = requests.post(
        f"{API_BASE}/api/shorts/generate",
        json={
            "url": url,
            "character_id": character_id,
            "duration": 15
        }
    )
    data = response.json()
    job_id = data["job_id"]
    print(f"✅ Job ID: {job_id}")
    
    # 2. 상태 확인 (폴링)
    while True:
        response = requests.get(f"{API_BASE}/api/shorts/status/{job_id}")
        status = response.json()
        
        progress = status["progress"]
        message = status["message"]
        print(f"📊 진행률: {progress}% - {message}")
        
        if status["status"] == "completed":
            print(f"✅ 완료! 파일: {status['output_path']}")
            break
        elif status["status"] == "failed":
            print(f"❌ 실패: {status['error']}")
            return None
        
        time.sleep(10)  # 10초마다 확인
    
    # 3. 다운로드
    print(f"📥 다운로드 중...")
    response = requests.get(f"{API_BASE}/api/shorts/download/{job_id}")
    
    filename = f"{job_id}.mp4"
    with open(filename, "wb") as f:
        f.write(response.content)
    
    print(f"🎉 저장 완료: {filename}")
    return filename

# 사용 예시
if __name__ == "__main__":
    url = "https://shopping.naver.com/example-product"
    filename = generate_shorts(url, "executive-fox")
    print(f"\n✅ 쇼츠 생성 완료: {filename}")
```

**실행:**
```bash
python generate_shorts.py
```

---

### **방법 3: Swagger UI (브라우저)**

1. **브라우저 열기**: http://localhost:8000/docs
2. **POST /api/shorts/generate** 클릭
3. **Try it out** 클릭
4. **Request body** 입력:
   ```json
   {
     "url": "https://example.com/product",
     "character_id": "executive-fox",
     "duration": 15
   }
   ```
5. **Execute** 클릭
6. **Response** 에서 `job_id` 복사
7. **GET /api/shorts/status/{job_id}** 로 상태 확인
8. **GET /api/shorts/download/{job_id}** 로 다운로드

---

## 📊 성능 및 시간

### **RTX 3060 12GB 기준**

```
단계                        시간
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. 웹 크롤링                5초
2. 스크립트 생성 (LLaMA)    20초
3. 이미지 생성 (5장)        50초
4. 음성 합성 (5개)          20초
5. 비디오 생성 (5개)        300초
6. 최종 렌더링              60초
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
총 소요 시간:              ~7.5분
```

### **GPU별 예상 시간**

| GPU | VRAM | 예상 시간 |
|-----|------|----------|
| RTX 4090 | 24GB | 4-5분 |
| RTX 4080 | 16GB | 5-6분 |
| RTX 3090 | 24GB | 6-7분 |
| **RTX 3060** | 12GB | **7-8분** |
| GTX 1660 Ti | 6GB | 10-12분 |
| CPU Only | - | 30-45분 |

---

## 🛠️ 트러블슈팅

### **문제 1: CUDA Out of Memory**

**증상:**
```
RuntimeError: CUDA out of memory. Tried to allocate 2.00 GiB
```

**해결:**
```bash
# 1. 다른 GPU 사용 프로그램 종료
nvidia-smi  # GPU 사용 확인

# 2. 배치 크기 줄이기 (backend/models/*.py 수정)
# num_inference_steps = 30 → 20

# 3. CPU Offloading 활성화 (자동으로 설정됨)
```

---

### **문제 2: Ollama 연결 실패**

**증상:**
```
❌ Ollama API error: Connection refused
```

**해결:**
```bash
# Ollama 서버 시작
ollama serve

# 다른 터미널에서 확인
curl http://localhost:11434/api/tags

# 모델 재다운로드
ollama pull llama3.1:8b
```

---

### **문제 3: 모델 다운로드 실패**

**증상:**
```
HTTPError: 404 Client Error
```

**해결:**
```bash
# Hugging Face 토큰 설정 (선택)
export HF_TOKEN="your_token_here"

# 캐시 삭제 후 재시도
rm -rf ~/.cache/huggingface
python scripts/install_models.py
```

---

### **문제 4: FFmpeg 오류**

**증상:**
```
FileNotFoundError: [Errno 2] No such file or directory: 'ffmpeg'
```

**해결:**
```bash
# FFmpeg 설치 확인
ffmpeg -version

# 없으면 설치
# Linux: sudo apt install ffmpeg
# macOS: brew install ffmpeg
# Windows: PATH에 ffmpeg.exe 추가
```

---

## ❓ FAQ

### **Q1: GPU 없이 실행 가능한가요?**

**A:** 네, CPU만으로도 실행 가능하지만 매우 느립니다 (30-45분/쇼츠).

```bash
# PyTorch CPU 버전 설치
pip install torch torchvision torchaudio
```

---

### **Q2: 비용이 드나요?**

**A:** 아니요! 외부 API 비용 없이 전기료만 발생합니다 ($5-10/월).

---

### **Q3: 오프라인에서 실행 가능한가요?**

**A:** 네! 모델 다운로드 후 완전 오프라인 작동 가능합니다.  
(웹 크롤링 기능만 인터넷 필요)

---

### **Q4: 캐릭터 추가 가능한가요?**

**A:** 네! `backend/app.py`의 `get_characters()` 함수에서 캐릭터를 추가할 수 있습니다.

---

### **Q5: 다른 언어 지원하나요?**

**A:** 현재 한국어 TTS를 사용하지만, Coqui TTS는 다국어를 지원합니다.

---

## 📚 추가 자료

- **시스템 설계**: `LOCAL_PC_SHORTS_SYSTEM.md`
- **프로젝트 완성 보고서**: `LOCAL_PC_SHORTS_COMPLETE.md`
- **리소스 검증**: `LOCAL_PC_RESOURCE_VERIFICATION.md`
- **API 문서**: http://localhost:8000/docs

---

## 🎉 완료!

이제 로컬 PC에서 AI 쇼츠를 무제한으로 생성할 수 있습니다!

```bash
# 빠른 시작
cd local-shorts-system
source venv/bin/activate
cd backend
python app.py

# → http://localhost:8000
```

**🎊 즐거운 쇼츠 제작 되세요! 🎊**

---

**작성일**: 2025-12-25  
**버전**: 1.0.0  
**라이선스**: MIT
