# ⚡ 빠른 시작 가이드 - 로컬 PC AI 쇼츠 생성

**5분 안에 시작하기!**

---

## 🚀 초간단 설치 (복사 & 붙여넣기)

### **1단계: 기본 설정 (1분)**

```bash
# 프로젝트 디렉토리로 이동
cd /home/azamans/webapp/local-shorts-system

# 가상환경 생성 및 활성화
python -m venv venv
source venv/bin/activate  # Linux/macOS
# venv\Scripts\activate   # Windows
```

---

### **2단계: PyTorch 설치 (1분)**

```bash
# NVIDIA GPU 있는 경우 (권장)
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118

# GPU 없는 경우
pip install torch torchvision torchaudio
```

---

### **3단계: 의존성 설치 (1분)**

```bash
pip install -r requirements.txt
```

---

### **4단계: AI 모델 다운로드 (20-30분, 최초 1회만)**

```bash
# 자동 다운로드 (15.3 GB)
python scripts/install_models.py
```

---

### **5단계: Ollama 설치 (2분)**

```bash
# Ollama 설치
curl -fsSL https://ollama.ai/install.sh | sh  # Linux/macOS
# Windows: https://ollama.ai/download

# LLaMA 모델 다운로드 (4.7 GB)
ollama pull llama3.1:8b

# Ollama 서버 시작 (별도 터미널)
ollama serve
```

---

## 🎬 실행 및 사용

### **서버 시작**

```bash
# 백엔드 서버 실행
cd backend
python app.py

# 브라우저 열기
# http://localhost:8000
```

---

### **쇼츠 생성 (3가지 방법)**

#### **방법 1: cURL**

```bash
# 쇼츠 생성
curl -X POST http://localhost:8000/api/shorts/generate \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://shopping.naver.com/your-product-url",
    "character_id": "executive-fox",
    "duration": 15
  }'

# 응답에서 job_id 확인
# {"job_id": "shorts_xxx_yyy", "status": "pending"}

# 상태 확인 (5-10분 대기)
curl http://localhost:8000/api/shorts/status/shorts_xxx_yyy

# 다운로드
curl -O http://localhost:8000/api/shorts/download/shorts_xxx_yyy
```

---

#### **방법 2: Python 스크립트**

```python
# quick_generate.py
import requests
import time

API = "http://localhost:8000"

# 쇼츠 생성
resp = requests.post(f"{API}/api/shorts/generate", json={
    "url": "https://example.com/product",
    "character_id": "executive-fox"
})
job_id = resp.json()["job_id"]
print(f"Job ID: {job_id}")

# 상태 확인 (폴링)
while True:
    resp = requests.get(f"{API}/api/shorts/status/{job_id}")
    status = resp.json()
    print(f"{status['progress']}% - {status['message']}")
    if status["status"] == "completed":
        break
    time.sleep(10)

# 다운로드
resp = requests.get(f"{API}/api/shorts/download/{job_id}")
with open(f"{job_id}.mp4", "wb") as f:
    f.write(resp.content)
print("✅ 완료!")
```

```bash
python quick_generate.py
```

---

#### **방법 3: Swagger UI (가장 쉬움)**

1. 브라우저: http://localhost:8000/docs
2. `POST /api/shorts/generate` 클릭
3. `Try it out` → 정보 입력 → `Execute`
4. `job_id` 복사
5. `GET /api/shorts/status/{job_id}` 로 상태 확인
6. 완료되면 `GET /api/shorts/download/{job_id}` 로 다운로드

---

## 📊 시간 및 성능

```
RTX 3060 기준: 7.5분 / 쇼츠
GTX 1660:      10-12분 / 쇼츠
CPU Only:      30-45분 / 쇼츠
```

---

## 🎯 캐릭터 목록

```bash
# 캐릭터 목록 조회
curl http://localhost:8000/api/characters | jq

# 주요 캐릭터:
# - executive-fox (🦊 이그제큐티브 폭스) - 비즈니스
# - tech-fox (🦊 테크 폭스) - 기술
# - fashionista-cat (😺 패셔니스타 캣) - 패션
# - chef-penguin (🐧 셰프 펭귄) - 음식
# - comedian-parrot (🦜 코미디언 패럿) - 엔터테인먼트
```

---

## ❗ 문제 해결

### **CUDA Out of Memory**
```bash
# nvidia-smi 로 다른 프로그램 확인 및 종료
nvidia-smi
```

### **Ollama 연결 실패**
```bash
# Ollama 서버 재시작
ollama serve
```

### **모델 다운로드 실패**
```bash
# 캐시 삭제 후 재시도
rm -rf ~/.cache/huggingface
python scripts/install_models.py
```

---

## 📚 자세한 가이드

- **전체 가이드**: `DEPLOYMENT_GUIDE.md`
- **API 문서**: http://localhost:8000/docs
- **시스템 설계**: `LOCAL_PC_SHORTS_SYSTEM.md`

---

## ✅ 체크리스트

```
□ Python 3.8-3.11 설치됨
□ NVIDIA GPU 드라이버 최신 (선택)
□ 가상환경 생성 및 활성화
□ PyTorch 설치 (CUDA 버전)
□ requirements.txt 설치
□ AI 모델 다운로드 (15.3 GB)
□ Ollama 설치 및 llama3.1:8b 다운로드
□ Ollama 서버 실행 중
□ 백엔드 서버 실행 중
□ http://localhost:8000 접속 가능
```

---

## 🎉 완료!

**이제 로컬 PC에서 AI 쇼츠를 무제한으로 생성할 수 있습니다!**

```
✅ API 비용: $0
✅ 무제한 생성
✅ 완전한 프라이버시
✅ 오프라인 작동
```

**🚀 즐거운 쇼츠 제작 되세요! 🚀**
