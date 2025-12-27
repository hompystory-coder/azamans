# 🎨 무료 이미지/영상 생성 AI 완전 가이드

**작성일**: 2024-12-27  
**목적**: 서버에 설치 가능한 무료 오픈소스 AI 조사  
**대상**: 이미지 생성 + 영상 생성

---

## 📋 목차
1. [이미지 생성 AI (무료)](#이미지-생성-ai-무료)
2. [영상 생성 AI (무료)](#영상-생성-ai-무료)
3. [설치 가이드](#설치-가이드)
4. [서버 요구사항](#서버-요구사항)
5. [비교 분석](#비교-분석)
6. [추천 조합](#추천-조합)

---

## 🎨 이미지 생성 AI (무료)

### 1️⃣ Stable Diffusion (최강 추천!)

```yaml
타입: 오픈소스 이미지 생성 AI
라이센스: CreativeML Open RAIL-M (상업적 사용 가능)
특징:
  - ✅ 완전 무료 (로컬 설치)
  - ✅ 고품질 이미지 생성
  - ✅ 커스터마이징 가능 (LoRA, ControlNet)
  - ✅ 다양한 스타일 지원
요구사항:
  - GPU: NVIDIA 6GB+ VRAM (RTX 3060 이상 권장)
  - RAM: 16GB+
  - 디스크: 10GB+
```

#### 설치 방법 A: ComfyUI (추천!)
```bash
# 1. ComfyUI 클론
cd /home/azamans
git clone https://github.com/comfyanonymous/ComfyUI.git
cd ComfyUI

# 2. 의존성 설치
pip install -r requirements.txt

# 3. 모델 다운로드 (Stable Diffusion XL 1.0)
cd models/checkpoints
wget https://huggingface.co/stabilityai/stable-diffusion-xl-base-1.0/resolve/main/sd_xl_base_1.0.safetensors

# 4. 실행
cd /home/azamans/ComfyUI
python main.py --listen 0.0.0.0 --port 8188

# 5. 브라우저 접속
# http://localhost:8188
```

**ComfyUI 장점**:
- 🎯 노드 기반 워크플로우 (시각적)
- ⚡ 빠른 속도
- 🔧 고급 기능 (LoRA, ControlNet)
- 💾 낮은 메모리 사용

#### 설치 방법 B: Automatic1111 WebUI
```bash
# 1. 클론
cd /home/azamans
git clone https://github.com/AUTOMATIC1111/stable-diffusion-webui.git
cd stable-diffusion-webui

# 2. 자동 설치 스크립트 실행
./webui.sh --listen --port 7860

# 3. 브라우저 접속
# http://localhost:7860
```

**Automatic1111 장점**:
- 🖼️ 직관적 UI
- 🎨 다양한 확장 기능
- 📚 커뮤니티 리소스 풍부

#### API 모드로 사용
```python
# ComfyUI API 호출 예시
import requests
import json

def generate_image(prompt):
    url = "http://localhost:8188/prompt"
    workflow = {
        "3": {
            "inputs": {
                "seed": 42,
                "steps": 20,
                "cfg": 8,
                "sampler_name": "euler",
                "scheduler": "normal",
                "denoise": 1,
                "model": ["4", 0],
                "positive": ["6", 0],
                "negative": ["7", 0],
                "latent_image": ["5", 0]
            },
            "class_type": "KSampler"
        },
        "6": {
            "inputs": {
                "text": prompt,
                "clip": ["4", 1]
            },
            "class_type": "CLIPTextEncode"
        }
    }
    response = requests.post(url, json={"prompt": workflow})
    return response.json()

# 사용 예시
result = generate_image("a beautiful sunset over the ocean, cinematic")
```

---

### 2️⃣ FLUX.1 (최신 고품질!)

```yaml
타입: 오픈소스 이미지 생성 AI (2024년 최신)
라이센스: Apache 2.0 (dev), MIT (schnell)
특징:
  - ✅ Stable Diffusion보다 높은 품질
  - ✅ 빠른 생성 속도 (FLUX.1-schnell)
  - ✅ 텍스트 렌더링 우수
요구사항:
  - GPU: NVIDIA 12GB+ VRAM (RTX 4070 이상)
  - RAM: 32GB+
  - 디스크: 15GB+
```

#### 설치 방법
```bash
# ComfyUI에서 FLUX 사용
cd /home/azamans/ComfyUI/models/checkpoints

# FLUX.1-schnell (빠른 버전, 4스텝)
wget https://huggingface.co/black-forest-labs/FLUX.1-schnell/resolve/main/flux1-schnell.safetensors

# FLUX.1-dev (고품질, 50스텝)
wget https://huggingface.co/black-forest-labs/FLUX.1-dev/resolve/main/flux1-dev.safetensors
```

---

### 3️⃣ Stable Diffusion 3.5 (2024년 최신)

```yaml
타입: Stable Diffusion 최신 버전
라이센스: MIT (Medium), 상업적 사용 가능
특징:
  - ✅ SD XL보다 개선된 품질
  - ✅ 더 나은 프롬프트 이해
  - ✅ 다국어 지원 강화
요구사항:
  - GPU: NVIDIA 8GB+ VRAM
  - RAM: 16GB+
```

#### 설치 방법
```bash
cd /home/azamans/ComfyUI/models/checkpoints

# SD 3.5 Medium (8GB VRAM 가능)
wget https://huggingface.co/stabilityai/stable-diffusion-3.5-medium/resolve/main/sd3.5_medium.safetensors
```

---

### 4️⃣ 기타 오픈소스 이미지 AI

| 모델 | 특징 | VRAM | 추천도 |
|------|------|------|--------|
| **SDXL Turbo** | 1스텝 초고속 | 6GB | ⭐⭐⭐⭐ |
| **Kandinsky 3.0** | 러시아 오픈소스 | 8GB | ⭐⭐⭐ |
| **DeepFloyd IF** | 초고해상도 | 16GB | ⭐⭐⭐ |
| **Midjourney (자체 호스팅)** | ❌ 불가능 | N/A | ❌ |
| **DALL-E (자체 호스팅)** | ❌ 불가능 | N/A | ❌ |

---

## 🎬 영상 생성 AI (무료)

### 1️⃣ AnimateDiff (추천!)

```yaml
타입: Stable Diffusion 기반 영상 생성
라이센스: 오픈소스
특징:
  - ✅ SD 모델과 호환
  - ✅ 짧은 영상 생성 (2-4초)
  - ✅ 모션 LoRA 지원
요구사항:
  - GPU: NVIDIA 12GB+ VRAM
  - RAM: 32GB+
  - 생성 시간: 2-5분 (4초 영상 기준)
```

#### 설치 방법 (ComfyUI)
```bash
# 1. AnimateDiff 노드 설치
cd /home/azamans/ComfyUI/custom_nodes
git clone https://github.com/Kosinkadink/ComfyUI-AnimateDiff-Evolved.git

# 2. 모션 모델 다운로드
cd /home/azamans/ComfyUI/models/animatediff_models
wget https://huggingface.co/guoyww/animatediff/resolve/main/mm_sd_v15_v2.ckpt

# 3. ComfyUI 재시작
cd /home/azamans/ComfyUI
python main.py --listen 0.0.0.0 --port 8188
```

**사용 예시**:
```
프롬프트: "a cat walking in the garden, smooth motion"
→ 16프레임 (2초) MP4 생성
```

---

### 2️⃣ Hotshot-XL

```yaml
타입: SDXL 기반 영상 생성
라이센스: 오픈소스
특징:
  - ✅ SDXL 품질로 영상 생성
  - ✅ 512x512, 8프레임
요구사항:
  - GPU: NVIDIA 16GB+ VRAM
  - 생성 시간: 5-10분
```

#### 설치 방법
```bash
cd /home/azamans
git clone https://github.com/hotshotco/Hotshot-XL.git
cd Hotshot-XL
pip install -r requirements.txt

# 모델 다운로드 (자동)
python inference.py --prompt "a dog running on the beach"
```

---

### 3️⃣ Text2Video-Zero (무료!)

```yaml
타입: 제로샷 텍스트→영상
라이센스: 오픈소스
특징:
  - ✅ 추가 학습 불필요
  - ✅ SD 모델 활용
  - ⚠️ 품질 중간
요구사항:
  - GPU: NVIDIA 10GB+ VRAM
  - 생성 시간: 10-20분 (짧은 영상)
```

---

### 4️⃣ Deforum (카메라 모션 영상)

```yaml
타입: SD 기반 카메라 애니메이션
라이센스: 오픈소스
특징:
  - ✅ 줌/패닝/회전 효과
  - ✅ 긴 영상 가능 (30초+)
  - ✅ Automatic1111 확장
요구사항:
  - GPU: NVIDIA 8GB+ VRAM
  - 생성 시간: 프레임당 2-5초
```

#### 설치 방법
```bash
# Automatic1111 WebUI에서
# Extensions → Install from URL
# https://github.com/deforum-art/sd-webui-deforum
```

---

### 5️⃣ 기타 영상 생성 AI

| 모델 | 특징 | VRAM | 무료 여부 |
|------|------|------|-----------|
| **ModelScope Text2Video** | Hugging Face | 12GB | ✅ 무료 |
| **CogVideo** | 청화대 개발 | 16GB | ✅ 무료 |
| **Make-A-Video (Meta)** | ❌ 비공개 | N/A | ❌ |
| **Runway Gen-2** | ❌ 클라우드 전용 | N/A | ❌ 유료 |
| **Pika Labs** | ❌ 클라우드 전용 | N/A | ⚠️ 제한적 무료 |

---

## 💻 서버 요구사항

### 최소 사양 (이미지만)
```yaml
GPU: NVIDIA RTX 3060 (12GB VRAM)
RAM: 16GB
디스크: 50GB SSD
OS: Ubuntu 20.04+ / Windows 10+
Python: 3.10+
```

### 권장 사양 (이미지 + 영상)
```yaml
GPU: NVIDIA RTX 4070 Ti (16GB VRAM) 이상
RAM: 32GB
디스크: 200GB SSD
OS: Ubuntu 22.04 LTS
Python: 3.10
CUDA: 12.1+
```

### 최고 사양 (프로덕션)
```yaml
GPU: NVIDIA RTX 4090 (24GB VRAM) 또는 A100
RAM: 64GB
디스크: 1TB NVMe SSD
```

---

## 🔍 현재 서버 사양 확인

```bash
# GPU 확인
nvidia-smi

# RAM 확인
free -h

# 디스크 확인
df -h

# CUDA 확인
nvcc --version
```

---

## 📊 비교 분석

### 이미지 생성 AI 비교

| 항목 | Stable Diffusion XL | FLUX.1 | SD 3.5 |
|------|---------------------|--------|--------|
| **품질** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **속도** | 5-10초 | 2-4초 (schnell) | 8-12초 |
| **VRAM** | 6GB | 12GB | 8GB |
| **사용 난이도** | 쉬움 | 보통 | 쉬움 |
| **커뮤니티** | 매우 많음 | 증가 중 | 많음 |
| **추천도** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |

### 영상 생성 AI 비교

| 항목 | AnimateDiff | Hotshot-XL | Deforum |
|------|-------------|------------|---------|
| **품질** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| **속도** | 2-5분 (4초) | 5-10분 | 프레임당 2-5초 |
| **VRAM** | 12GB | 16GB | 8GB |
| **영상 길이** | 2-4초 | 2초 | 30초+ |
| **사용 난이도** | 보통 | 어려움 | 쉬움 |
| **추천도** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |

---

## 🎯 추천 조합

### 💰 조합 1: 완전 무료 (로컬 설치)

```yaml
이미지 생성: Stable Diffusion XL + ComfyUI
영상 생성: AnimateDiff (짧은 영상) + Deforum (긴 영상)
TTS: Edge TTS (무료 Microsoft TTS)

장점:
  - ✅ 완전 무료
  - ✅ 프라이버시 완벽
  - ✅ 사용량 무제한
  
단점:
  - ⚠️ 서버 성능 필요 (GPU 필수)
  - ⚠️ 초기 설정 복잡
  - ⚠️ 생성 속도 느림
```

### ⚡ 조합 2: 하이브리드 (로컬 + 클라우드)

```yaml
이미지 생성: 로컬 Stable Diffusion (기본)
           + Replicate API (고품질 필요 시)
영상 생성: 로컬 AnimateDiff (짧은 영상)
           + Runway API (긴 영상, 유료)
TTS: ElevenLabs (유료, 고품질)

장점:
  - ✅ 유연한 선택
  - ✅ 로컬로 비용 절감
  - ✅ 클라우드로 품질 보장
  
단점:
  - ⚠️ 일부 유료
```

### 🚀 조합 3: 프로덕션 (쇼츠 최적화)

```yaml
워크플로우:
1. 스토리 생성: Ollama llama3.1:8b (무료)
2. 이미지 생성: ComfyUI + SDXL (무료)
3. 영상 생성: AnimateDiff (무료, 짧은 모션)
4. TTS: Edge TTS (무료) 또는 ElevenLabs (유료)
5. 편집: FFmpeg (무료)

예상 비용: 완전 무료 (로컬 실행)
예상 시간: 이미지 7장 (1-2분) + 영상 전환 (5-10분) = 총 10-15분
```

---

## 🛠️ 실제 설치 가이드 (쇼츠 시스템 통합)

### Step 1: ComfyUI 설치 (이미지 생성)

```bash
# 1. ComfyUI 클론
cd /home/azamans
git clone https://github.com/comfyanonymous/ComfyUI.git
cd ComfyUI

# 2. 가상환경 생성
python3 -m venv venv
source venv/bin/activate

# 3. 의존성 설치
pip install torch torchvision --index-url https://download.pytorch.org/whl/cu121
pip install -r requirements.txt

# 4. SDXL 모델 다운로드
mkdir -p models/checkpoints
cd models/checkpoints
wget https://huggingface.co/stabilityai/stable-diffusion-xl-base-1.0/resolve/main/sd_xl_base_1.0.safetensors

# 5. 실행 (백그라운드)
cd /home/azamans/ComfyUI
nohup python main.py --listen 0.0.0.0 --port 8188 > comfyui.log 2>&1 &

# 6. 확인
curl http://localhost:8188
```

### Step 2: AnimateDiff 설치 (영상 생성)

```bash
# 1. AnimateDiff 노드 설치
cd /home/azamans/ComfyUI/custom_nodes
git clone https://github.com/Kosinkadink/ComfyUI-AnimateDiff-Evolved.git
cd ComfyUI-AnimateDiff-Evolved
pip install -r requirements.txt

# 2. 모션 모델 다운로드
mkdir -p /home/azamans/ComfyUI/models/animatediff_models
cd /home/azamans/ComfyUI/models/animatediff_models
wget https://huggingface.co/guoyww/animatediff/resolve/main/mm_sd_v15_v2.ckpt

# 3. ComfyUI 재시작
pkill -f "python main.py"
cd /home/azamans/ComfyUI
nohup python main.py --listen 0.0.0.0 --port 8188 > comfyui.log 2>&1 &
```

### Step 3: Python API 통합

```python
# /home/azamans/webapp/zero-install-ai-studio/ai-backend/comfyui_client.py
import requests
import json
import time

class ComfyUIClient:
    def __init__(self, base_url="http://localhost:8188"):
        self.base_url = base_url
    
    def generate_image(self, prompt, width=1024, height=1024):
        """SDXL 이미지 생성"""
        workflow = {
            # ComfyUI 워크플로우 JSON
            # (간소화된 예시)
        }
        
        # 1. 워크플로우 제출
        response = requests.post(
            f"{self.base_url}/prompt",
            json={"prompt": workflow}
        )
        prompt_id = response.json()['prompt_id']
        
        # 2. 생성 완료 대기
        while True:
            history = requests.get(f"{self.base_url}/history/{prompt_id}").json()
            if prompt_id in history:
                break
            time.sleep(1)
        
        # 3. 이미지 다운로드
        outputs = history[prompt_id]['outputs']
        # ... 이미지 추출 로직
        
        return image_path

# 사용 예시
client = ComfyUIClient()
image = client.generate_image("a beautiful sunset, cinematic")
```

---

## 🎁 보너스: 무료 API (제한적)

### 이미지 생성 무료 API

| 서비스 | 무료 한도 | 품질 | 제한 |
|--------|-----------|------|------|
| **Replicate** | $5 크레딧 | ⭐⭐⭐⭐ | 초기만 무료 |
| **Hugging Face Inference** | 무제한 (느림) | ⭐⭐⭐ | 속도 느림 |
| **Stability AI (Free Tier)** | 월 25장 | ⭐⭐⭐⭐⭐ | 매우 제한적 |
| **Leonardo.ai** | 일 150토큰 | ⭐⭐⭐⭐ | 제한적 |

### 영상 생성 무료 API

| 서비스 | 무료 한도 | 품질 | 제한 |
|--------|-----------|------|------|
| **Runway Gen-2** | 초기 125 크레딧 | ⭐⭐⭐⭐⭐ | 초기만 |
| **Pika Labs** | 일 3회 | ⭐⭐⭐⭐ | 매우 제한적 |
| **Luma AI** | 월 30회 | ⭐⭐⭐⭐ | 제한적 |

---

## 🎯 최종 권장사항 (쇼츠 시스템용)

### ⭐ 최고의 선택: ComfyUI + SDXL + AnimateDiff

**이유**:
1. ✅ 완전 무료 (서버에 GPU만 있으면 됨)
2. ✅ 고품질 이미지 생성 (SDXL)
3. ✅ 짧은 영상 가능 (AnimateDiff)
4. ✅ API로 자동화 가능
5. ✅ 사용량 무제한

**단점**:
1. ⚠️ GPU 필수 (최소 12GB VRAM)
2. ⚠️ 초기 설정 복잡
3. ⚠️ 생성 속도 느림 (이미지 10초, 영상 5분)

---

## 📞 관련 문서

- [SERVER_AI_CAPABILITIES.md](./SERVER_AI_CAPABILITIES.md) - Ollama AI 기능 분석
- [OLLAMA_INTEGRATION.md](./OLLAMA_INTEGRATION.md) - Ollama 통합 가이드

---

---

## ⚠️ GPU 없는 서버를 위한 대안

### 현재 서버 상태
```bash
❌ NVIDIA GPU 없음 (nvidia-smi 미확인)
```

### CPU 전용 옵션

#### 1️⃣ 무료 클라우드 API 활용 (추천!)

**Hugging Face Inference API** (무료, 제한적)
```python
# 무료 Hugging Face API 사용
import requests

API_URL = "https://api-inference.huggingface.co/models/stabilityai/stable-diffusion-xl-base-1.0"
headers = {"Authorization": "Bearer YOUR_HF_TOKEN"}  # 무료 계정 필요

def generate_image(prompt):
    response = requests.post(
        API_URL,
        headers=headers,
        json={"inputs": prompt}
    )
    image = response.content
    return image

# 무료 사용 가능!
# 제한: 속도 느림 (대기열 방식)
```

**장점**:
- ✅ GPU 불필요
- ✅ 완전 무료
- ✅ 설정 간단

**단점**:
- ⚠️ 속도 매우 느림 (1-5분 대기)
- ⚠️ 동시 요청 제한
- ⚠️ 품질 제한

#### 2️⃣ 무료 크레딧 제공 서비스

| 서비스 | 무료 크레딧 | 이미지 생성 | 영상 생성 |
|--------|-------------|-------------|-----------|
| **Replicate** | $5 (초기) | ✅ SDXL | ✅ AnimateDiff |
| **Together.ai** | $25 (초기) | ✅ FLUX | ❌ |
| **Fal.ai** | 무료 티어 | ✅ 다양 | ✅ 일부 |

**Replicate 예시** (가장 추천):
```python
import replicate

# 이미지 생성 (SDXL)
output = replicate.run(
    "stability-ai/sdxl:39ed52f2a78e934b3ba6e2a89f5b1c712de7dfea535525255b1aa35c5565e08b",
    input={"prompt": "a beautiful sunset"}
)

# 영상 생성 (AnimateDiff)
video = replicate.run(
    "lucataco/animate-diff:beecf59c4aee8d81bf04f0381033dfa10dc16e845b4ae00d281e2fa377e48a9f",
    input={"prompt": "a cat walking"}
)

# 초기 $5 무료 크레딧
# 이미지: $0.003/장 → 약 1,600장
# 영상: $0.05/10초 → 약 100개
```

#### 3️⃣ CPU 전용 Stable Diffusion (매우 느림)

```bash
# CPU 전용 설치 (권장하지 않음)
cd /home/azamans
git clone https://github.com/AUTOMATIC1111/stable-diffusion-webui.git
cd stable-diffusion-webui

# CPU 모드로 실행 (GPU 없이)
./webui.sh --skip-torch-cuda-test --precision full --no-half --use-cpu all

# 경고: 1장 생성에 10-30분 소요!
```

#### 4️⃣ 외부 GPU 서버 렌탈 (저렴한 옵션)

| 서비스 | 가격 | GPU | 시간당 |
|--------|------|-----|--------|
| **Vast.ai** | $0.15/hr | RTX 3060 | 저렴 |
| **RunPod** | $0.20/hr | RTX 3090 | 중간 |
| **Lambda Labs** | $0.50/hr | A100 | 고가 |

---

## 🎯 GPU 없는 서버를 위한 최종 권장사항

### ⭐ 최고의 선택: Replicate API (초기 무료 크레딧)

**추천 이유**:
1. ✅ GPU 불필요 (클라우드 실행)
2. ✅ 초기 $5 무료 ($5 = 이미지 1,600장 또는 영상 100개)
3. ✅ 고품질 (SDXL, FLUX, AnimateDiff)
4. ✅ 빠른 속도 (이미지 3-5초, 영상 1-2분)
5. ✅ API 연동 간단

**예상 비용** (무료 크레딧 소진 후):
- 이미지 7장/쇼츠: $0.021
- 영상 1개/쇼츠: $0.05
- 총 쇼츠 1개: **$0.071 (약 100원)**

**월 1,000개 쇼츠 생성 시**: $71 (약 10만원)

---

### 🆓 완전 무료 옵션: Hugging Face Inference API

**추천 이유**:
1. ✅ 완전 무료 (무제한)
2. ✅ GPU 불필요
3. ✅ 계정만 필요

**단점**:
1. ⚠️ 매우 느림 (이미지 1-5분 대기)
2. ⚠️ 영상 생성 제한적
3. ⚠️ 대기열 방식 (혼잡 시간 더 느림)

**사용 시나리오**:
- 개발/테스트: ✅ Hugging Face (무료)
- 프로덕션: ⭐ Replicate (저렴, 빠름)

---

## 📝 구현 가이드 (Replicate 연동)

### Step 1: Replicate 계정 생성
```bash
# 1. 가입: https://replicate.com/
# 2. API 토큰 발급: https://replicate.com/account/api-tokens
# 3. 초기 $5 무료 크레딧 확인
```

### Step 2: Python 클라이언트 설치
```bash
cd /home/azamans/webapp/zero-install-ai-studio/ai-backend
pip install replicate
```

### Step 3: API 연동
```python
# replicate_client.py
import replicate
import os

class ReplicateImageGenerator:
    def __init__(self, api_token):
        os.environ["REPLICATE_API_TOKEN"] = api_token
    
    def generate_image(self, prompt, width=1024, height=1024):
        """SDXL 이미지 생성"""
        output = replicate.run(
            "stability-ai/sdxl:39ed52f2a78e934b3ba6e2a89f5b1c712de7dfea535525255b1aa35c5565e08b",
            input={
                "prompt": prompt,
                "width": width,
                "height": height,
                "num_outputs": 1
            }
        )
        return output[0]  # 이미지 URL
    
    def generate_video(self, prompt, duration=4):
        """AnimateDiff 영상 생성"""
        output = replicate.run(
            "lucataco/animate-diff:beecf59c4aee8d81bf04f0381033dfa10dc16e845b4ae00d281e2fa377e48a9f",
            input={
                "prompt": prompt,
                "num_frames": 16,  # 2초 (8fps)
                "guidance_scale": 7.5
            }
        )
        return output  # 영상 URL

# 사용 예시
client = ReplicateImageGenerator(api_token="YOUR_API_TOKEN")
image_url = client.generate_image("a beautiful sunset, cinematic")
print(f"생성된 이미지: {image_url}")
```

### Step 4: 쇼츠 시스템 통합
```python
# story_generator.py에 추가
from replicate_client import ReplicateImageGenerator

# API 토큰 설정
REPLICATE_API_TOKEN = os.getenv("REPLICATE_API_TOKEN")
if REPLICATE_API_TOKEN:
    image_generator = ReplicateImageGenerator(REPLICATE_API_TOKEN)
    logger.info("✅ Replicate API 연동 완료")
```

---

## 🚀 다음 단계

### GPU 없는 서버의 경우:
1. ✅ **Replicate 계정 생성** (초기 $5 무료)
2. ✅ **API 토큰 발급**
3. ✅ **Python 클라이언트 설치**: `pip install replicate`
4. ✅ **쇼츠 시스템 연동**
5. ✅ **테스트**: 이미지 7장 생성 (약 30초)

### GPU 서버를 구할 수 있다면:
1. **ComfyUI 설치** (로컬 실행)
2. **완전 무료 운영**

---

**© 2024 Zero-Install AI Studio. All rights reserved.**
