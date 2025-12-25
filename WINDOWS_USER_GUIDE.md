# 🪟 Windows 사용자를 위한 완벽 가이드

**윈도우 PC에서 더블클릭만으로 AI 쇼츠 생성!**

---

## 📋 목차

1. [사전 준비 (필수 프로그램)](#사전-준비)
2. [설치 방법 (3단계)](#설치-방법)
3. [실행 방법 (더블클릭!)](#실행-방법)
4. [사용 방법](#사용-방법)
5. [문제 해결](#문제-해결)

---

## 💻 사전 준비 (필수 프로그램)

### **1. Python 3.10 설치**

#### **다운로드**
https://www.python.org/downloads/release/python-3100/

#### **설치 시 주의사항**
```
✅ "Add Python to PATH" 체크박스 반드시 체크!
✅ "Install for all users" 선택
```

#### **설치 확인**
1. `Win + R` → `cmd` → Enter
2. `python --version` 입력
3. `Python 3.10.x` 표시되면 OK

---

### **2. NVIDIA GPU 드라이버 (선택)**

NVIDIA GPU가 있다면:

#### **드라이버 다운로드**
https://www.nvidia.com/drivers

#### **확인 방법**
1. `Win + R` → `cmd` → Enter
2. `nvidia-smi` 입력
3. GPU 정보가 표시되면 OK

---

### **3. Ollama 설치**

#### **다운로드**
https://ollama.ai/download

#### **설치**
1. `OllamaSetup.exe` 다운로드
2. 더블클릭으로 설치
3. 자동으로 백그라운드 실행

---

### **4. FFmpeg 설치 (비디오 렌더링)**

#### **방법 1: 자동 설치 (권장)**
1. https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip 다운로드
2. 압축 해제 (예: `C:\ffmpeg`)
3. 환경변수 PATH에 추가:
   - `Win + R` → `sysdm.cpl` → Enter
   - "고급" 탭 → "환경 변수"
   - "시스템 변수"에서 `Path` 선택 → "편집"
   - "새로 만들기" → `C:\ffmpeg\bin` 입력
   - 모두 "확인"

#### **방법 2: Chocolatey (고급)**
```powershell
choco install ffmpeg
```

#### **확인**
```cmd
ffmpeg -version
```

---

## 🚀 설치 방법 (3단계)

### **Step 1: 프로젝트 다운로드**

#### **방법 1: Git 사용 (권장)**
```cmd
cd C:\Users\사용자이름\Documents
git clone <repository-url>
cd local-shorts-system
```

#### **방법 2: ZIP 다운로드**
1. 프로젝트 ZIP 파일 다운로드
2. `C:\Users\사용자이름\Documents` 에 압축 해제
3. 폴더 이름을 `local-shorts-system`으로 변경

---

### **Step 2: 기본 설치 (5분)**

#### **실행**
```
📁 local-shorts-system 폴더 열기
📄 install_windows.bat 더블클릭
```

#### **과정**
```
[1/7] Python 확인...         ✅
[2/7] 가상환경 생성...       ✅
[3/7] 가상환경 활성화...     ✅
[4/7] PyTorch 설치...        ⏳ 2-3분
[5/7] 의존성 설치...         ⏳ 3-5분
[6/7] Playwright 설치...     ✅
[7/7] FFmpeg 확인...         ✅
```

#### **완료 메시지**
```
✅ 기본 설치 완료!

다음 단계:
1. Ollama 설치: https://ollama.ai/download
2. 모델 다운로드: download_models_windows.bat 실행
3. 실행: run_windows.bat
```

---

### **Step 3: AI 모델 다운로드 (20-30분, 최초 1회)**

#### **실행**
```
📄 download_models_windows.bat 더블클릭
```

#### **과정**
```
[1/4] Stable Diffusion XL...  ⏳ 6.9 GB
[2/4] AnimateDiff...          ⏳ 1.7 GB
[3/4] Coqui TTS...            ⏳ 2.0 GB
[4/4] LLaMA 3.1...            ⏳ 4.7 GB
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
총 15.3 GB 다운로드
```

#### **완료 메시지**
```
✅ 모델 다운로드 완료!
다음 단계: run_windows.bat 실행
```

---

## 🎬 실행 방법 (더블클릭!)

### **시작**

```
📄 run_windows.bat 더블클릭
```

### **실행 화면**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  🚀 로컬 PC AI 쇼츠 생성 시스템 시작
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[1/2] Ollama 서버 시작...
✅ Ollama 서버 실행 중

[2/2] GPU 확인 중...
✅ GPU 사용 가능

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ✅ 서버 시작 중...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📋 서버 주소: http://localhost:8000
📚 API 문서:  http://localhost:8000/docs

⏹️  종료하려면 Ctrl+C 누르세요
```

### **브라우저 열기**

```
📄 open_browser_windows.bat 더블클릭
→ 자동으로 브라우저가 열립니다
```

---

## 🎯 사용 방법

### **방법 1: 웹 인터페이스 (가장 쉬움)**

#### **1. 브라우저 열기**
```
http://localhost:8000/docs
```

#### **2. 쇼츠 생성**
1. `POST /api/shorts/generate` 클릭
2. `Try it out` 버튼 클릭
3. 정보 입력:
   ```json
   {
     "url": "https://shopping.naver.com/product-url",
     "character_id": "executive-fox",
     "duration": 15
   }
   ```
4. `Execute` 버튼 클릭
5. Response에서 `job_id` 복사

#### **3. 상태 확인**
1. `GET /api/shorts/status/{job_id}` 클릭
2. `Try it out` 클릭
3. `job_id` 입력
4. `Execute` 클릭
5. `progress`: 0-100% 확인

#### **4. 다운로드**
1. 100% 완료 후
2. `GET /api/shorts/download/{job_id}` 클릭
3. `Try it out` → `job_id` 입력 → `Execute`
4. `Download file` 클릭

---

### **방법 2: Python 스크립트**

#### **generate_shorts.py 생성**
```python
import requests
import time

API = "http://localhost:8000"

# 1. 쇼츠 생성 시작
print("🎬 쇼츠 생성 시작...")
resp = requests.post(f"{API}/api/shorts/generate", json={
    "url": "https://example.com/product",
    "character_id": "executive-fox",
    "duration": 15
})

job_id = resp.json()["job_id"]
print(f"✅ Job ID: {job_id}")

# 2. 상태 확인 (폴링)
while True:
    resp = requests.get(f"{API}/api/shorts/status/{job_id}")
    status = resp.json()
    
    progress = status["progress"]
    message = status["message"]
    print(f"📊 {progress}% - {message}")
    
    if status["status"] == "completed":
        print("✅ 완료!")
        break
    elif status["status"] == "failed":
        print(f"❌ 실패: {status['error']}")
        exit(1)
    
    time.sleep(10)

# 3. 다운로드
resp = requests.get(f"{API}/api/shorts/download/{job_id}")
filename = f"{job_id}.mp4"
with open(filename, "wb") as f:
    f.write(resp.content)

print(f"🎉 저장 완료: {filename}")
```

#### **실행**
```cmd
python generate_shorts.py
```

---

### **방법 3: Windows PowerShell**

```powershell
# 쇼츠 생성
$body = @{
    url = "https://example.com/product"
    character_id = "executive-fox"
    duration = 15
} | ConvertTo-Json

$resp = Invoke-RestMethod -Uri "http://localhost:8000/api/shorts/generate" `
    -Method Post -Body $body -ContentType "application/json"

$jobId = $resp.job_id
Write-Host "Job ID: $jobId"

# 상태 확인
$resp = Invoke-RestMethod -Uri "http://localhost:8000/api/shorts/status/$jobId"
Write-Host "$($resp.progress)% - $($resp.message)"

# 다운로드
Invoke-WebRequest -Uri "http://localhost:8000/api/shorts/download/$jobId" `
    -OutFile "$jobId.mp4"
```

---

## 🛠️ 문제 해결

### **문제 1: Python을 찾을 수 없습니다**

**증상**
```
'python'은(는) 내부 또는 외부 명령... 실행할 수 있는 프로그램이 아닙니다.
```

**해결**
1. Python 재설치
2. "Add Python to PATH" 반드시 체크
3. 또는 환경변수에 수동 추가:
   - `Win + R` → `sysdm.cpl`
   - "고급" → "환경 변수"
   - `Path`에 `C:\Python310` 추가

---

### **문제 2: CUDA Out of Memory**

**증상**
```
RuntimeError: CUDA out of memory
```

**해결**
1. 작업 관리자 (`Ctrl + Shift + Esc`)
2. 다른 GPU 사용 프로그램 종료
3. 또는 코드 수정 (`backend/models/*.py`):
   ```python
   num_inference_steps = 20  # 30에서 줄임
   ```

---

### **문제 3: Ollama 연결 실패**

**증상**
```
Connection refused: http://localhost:11434
```

**해결**
1. 작업 관리자에서 `ollama.exe` 확인
2. 없으면 수동 시작:
   ```cmd
   ollama serve
   ```
3. 또는 Ollama 재설치

---

### **문제 4: FFmpeg를 찾을 수 없습니다**

**증상**
```
FileNotFoundError: ffmpeg
```

**해결**
1. FFmpeg 설치 확인:
   ```cmd
   ffmpeg -version
   ```
2. 없으면 설치 후 환경변수 PATH에 추가
3. CMD 재시작 필수

---

### **문제 5: 포트가 이미 사용 중**

**증상**
```
Error: [Errno 10048] Address already in use
```

**해결**
1. 포트 사용 프로그램 찾기:
   ```cmd
   netstat -ano | findstr :8000
   ```
2. PID 확인 후 종료:
   ```cmd
   taskkill /PID <PID번호> /F
   ```

---

## 📊 성능 (Windows PC 기준)

### **RTX 3060 12GB**
```
웹 크롤링:     5초
스크립트:      20초
이미지:        50초
음성:          20초
비디오:        300초 (5분)
렌더링:        60초
━━━━━━━━━━━━━━━━━━━
총 시간:      ~7.5분
```

### **GTX 1660 Ti 6GB**
```
총 시간:      ~10-12분
```

### **CPU Only (GPU 없음)**
```
총 시간:      ~30-45분
⚠️ 매우 느림
```

---

## 🎭 캐릭터 목록 (30개)

### **비즈니스 (5개)**
- executive-fox (🦊 이그제큐티브 폭스)
- ceo-lion (🦁 CEO 라이온)
- strategist-eagle (🦅 전략가 이글)
- negotiator-wolf (🐺 협상가 울프)
- consultant-owl (🦉 컨설턴트 아울)

### **기술 (5개)**
- tech-fox (🦊 테크 폭스)
- dev-raccoon (🦝 개발자 라쿤)
- ai-panda (🐼 AI 판다)
- startup-tiger (🐯 스타트업 타이거)
- blockchain-monkey (🐵 블록체인 몽키)

### **패션 (5개)**
- fashionista-cat (😺 패셔니스타 캣)
- stylist-peacock (🦚 스타일리스트 피콕)
- luxury-leopard (🐆 럭셔리 레오파드)
- trendy-rabbit (🐰 트렌디 래빗)
- designer-swan (🦢 디자이너 스완)

### **스포츠 (5개)**
- athlete-cheetah (🐆 애슬리트 치타)
- trainer-bear (🐻 트레이너 베어)
- yoga-deer (🦌 요가 디어)
- runner-kangaroo (🦘 러너 캥거루)
- fighter-dragon (🐉 파이터 드래곤)

### **음식 (5개)**
- chef-penguin (🐧 셰프 펭귄)
- foodie-hamster (🐹 푸디 햄스터)
- barista-otter (🦦 바리스타 오터)
- sommelier-fox (🦊 소믈리에 폭스)
- baker-bear (🐻 베이커 베어)

### **엔터테인먼트 (5개)**
- comedian-parrot (🦜 코미디언 패럿)
- musician-fox (🦊 뮤지션 폭스)
- dancer-peacock (🦚 댄서 피콕)
- artist-cat (😺 아티스트 캣)
- gamer-otter (🦦 게이머 오터)

---

## 📁 파일 구조

```
local-shorts-system/
├── 📄 install_windows.bat             ← 설치 (Step 2)
├── 📄 download_models_windows.bat     ← 모델 다운로드 (Step 3)
├── 📄 run_windows.bat                 ← 실행 ⭐
├── 📄 open_browser_windows.bat        ← 브라우저 열기
├── 📁 backend/
│   ├── app.py                         ← FastAPI 서버
│   ├── models/                        ← AI 모델 (4개)
│   └── services/                      ← 서비스
├── 📁 scripts/
│   └── install_models.py              ← 모델 다운로드 스크립트
├── 📁 venv/                           ← 가상환경 (자동 생성)
├── 📁 models/                         ← AI 모델 저장소
└── 📁 output/                         ← 생성된 쇼츠
    └── videos/                        ← MP4 파일
```

---

## 🎉 완성!

### **빠른 시작 체크리스트**

```
✅ Python 3.10 설치
✅ NVIDIA 드라이버 설치 (선택)
✅ Ollama 설치
✅ FFmpeg 설치
✅ install_windows.bat 실행
✅ download_models_windows.bat 실행 (15.3 GB)
✅ run_windows.bat 실행
✅ open_browser_windows.bat 실행
✅ 첫 쇼츠 생성!
```

---

## 🚀 시작하기

### **3단계로 시작**

```
1️⃣ install_windows.bat 더블클릭
   (5분)

2️⃣ download_models_windows.bat 더블클릭
   (30분, 최초 1회)

3️⃣ run_windows.bat 더블클릭
   (시작!)
```

---

## 💡 팁

### **바탕화면에 바로가기 만들기**
1. `run_windows.bat` 우클릭
2. "바로 가기 만들기"
3. 바로가기를 바탕화면으로 이동
4. 이름 변경: "AI 쇼츠 생성"

### **자동 시작 (선택)**
1. `Win + R` → `shell:startup`
2. `run_windows.bat` 바로가기 복사
3. Windows 시작 시 자동 실행

---

**🎊 즐거운 쇼츠 제작 되세요! 🎊**

**날짜**: 2025-12-25  
**버전**: 1.0.0 Windows Edition  
**라이선스**: MIT
