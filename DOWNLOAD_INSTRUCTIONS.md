# AI 쇼츠 생성 시스템 - 다운로드 및 설치 가이드

## 🎯 다운로드 방법 3가지

### 방법 1: GitHub에서 직접 다운로드 (권장)
```bash
# Git이 설치되어 있다면:
git clone https://github.com/[YOUR_USERNAME]/local-shorts-system.git
cd local-shorts-system
```

**또는 ZIP 파일로 다운로드:**
1. GitHub 페이지 접속: `https://github.com/[YOUR_USERNAME]/local-shorts-system`
2. 녹색 "Code" 버튼 클릭
3. "Download ZIP" 클릭
4. 압축 해제 후 폴더 열기

---

### 방법 2: 직접 파일 다운로드 (현재 시스템)

현재 시스템 경로: `/home/azamans/webapp/local-shorts-system`

**압축 파일 생성하여 다운로드:**
```bash
cd /home/azamans/webapp
tar -czf local-shorts-system.tar.gz local-shorts-system/
```

---

### 방법 3: 필수 프로그램 다운로드 링크

#### 1️⃣ Python 3.10 (필수)
- 다운로드: https://www.python.org/downloads/windows/
- 파일: `python-3.10.11-amd64.exe`
- 설치 시 **"Add Python to PATH"** 반드시 체크!

#### 2️⃣ Git (필수)
- 다운로드: https://git-scm.com/download/win
- 파일: `Git-2.43.0-64-bit.exe`
- 기본 설정으로 설치

#### 3️⃣ Ollama (LLaMA 3.1 실행용, 필수)
- 다운로드: https://ollama.ai/download/windows
- 파일: `OllamaSetup.exe`
- 설치 후 자동 실행됨

#### 4️⃣ NVIDIA CUDA Toolkit (GPU 가속, 선택)
- 다운로드: https://developer.nvidia.com/cuda-downloads
- NVIDIA GPU가 있는 경우에만 필요
- RTX 20/30/40 시리즈 권장

#### 5️⃣ FFmpeg (영상 처리, 필수)
- 다운로드: https://www.gyan.dev/ffmpeg/builds/
- 파일: `ffmpeg-release-essentials.zip`
- 압축 해제 후 `bin` 폴더를 PATH에 추가

---

## 🚀 설치 순서

### Windows 사용자 (권장)

#### Step 1: 필수 프로그램 설치 (20분)
```batch
REM 1. Python 3.10 설치
python-3.10.11-amd64.exe

REM 2. Git 설치
Git-2.43.0-64-bit.exe

REM 3. Ollama 설치
OllamaSetup.exe

REM 4. FFmpeg 설치 (PATH 추가 필요)
REM 압축 해제 후 ffmpeg\bin 폴더를 시스템 환경 변수에 추가
```

#### Step 2: 프로젝트 다운로드 (2분)
```batch
git clone https://github.com/[YOUR_USERNAME]/local-shorts-system.git
cd local-shorts-system
```

#### Step 3: 자동 설치 실행 (5분)
```batch
REM 더블클릭으로 실행:
install_windows.bat
```

#### Step 4: AI 모델 다운로드 (30분, 15.3GB)
```batch
REM 더블클릭으로 실행:
download_models_windows.bat
```

#### Step 5: 실행! (2분)
```batch
REM GUI로 실행 (권장):
start_gui_windows.bat

REM 또는 웹 브라우저로 실행:
run_windows.bat
open_browser_windows.bat
```

---

## 📊 다운로드 용량 및 시간

| 항목 | 용량 | 다운로드 시간 (예상) |
|------|------|---------------------|
| Python 3.10 | 25 MB | 1분 |
| Git | 50 MB | 2분 |
| Ollama | 500 MB | 5분 |
| FFmpeg | 100 MB | 3분 |
| **AI 모델들** | **15.3 GB** | **30-60분** |
| - Stable Diffusion XL | 6.9 GB | 15분 |
| - AnimateDiff | 1.7 GB | 5분 |
| - Coqui TTS | 2.0 GB | 6분 |
| - LLaMA 3.1 (Ollama) | 4.7 GB | 12분 |
| **총합** | **~16 GB** | **40-70분** |

---

## 🔧 설치 후 확인

### 1. Python 확인
```batch
python --version
# 출력: Python 3.10.11
```

### 2. Git 확인
```batch
git --version
# 출력: git version 2.43.0
```

### 3. Ollama 확인
```batch
ollama --version
# 출력: ollama version 0.x.x
```

### 4. FFmpeg 확인
```batch
ffmpeg -version
# 출력: ffmpeg version N-...
```

---

## ❓ 자주 묻는 질문 (FAQ)

### Q1: 인터넷 연결 없이 사용 가능한가요?
**A:** 네! 최초 AI 모델 다운로드(15.3GB)만 인터넷이 필요하고, 이후에는 완전 오프라인 사용 가능합니다.

### Q2: GPU가 없어도 사용 가능한가요?
**A:** 가능하지만 속도가 느립니다:
- **GPU 있음**: 쇼츠 1개 생성 7-8분
- **CPU만 사용**: 쇼츠 1개 생성 30-60분

### Q3: 다운로드한 AI 모델은 어디에 저장되나요?
**A:** `local-shorts-system/models/` 폴더에 저장됩니다.

### Q4: 설치 중 오류가 발생하면?
**A:** `WINDOWS_INSTALLATION_GUIDE.md` 또는 `QUICK_INSTALL.md` 참고하세요.

---

## 📁 다운로드 후 폴더 구조

```
local-shorts-system/
├── install_windows.bat          # ← 더블클릭: 설치
├── download_models_windows.bat  # ← 더블클릭: AI 모델 다운로드
├── run_windows.bat              # ← 더블클릭: 서버 시작
├── start_gui_windows.bat        # ← 더블클릭: GUI 실행 (권장)
├── open_browser_windows.bat     # ← 더블클릭: 브라우저 열기
│
├── backend/
│   ├── app.py                   # FastAPI 서버
│   ├── models/                  # AI 모델 클래스
│   └── services/                # 쇼츠 생성 파이프라인
│
├── models/                      # AI 모델 저장 위치 (15.3GB)
│   ├── stable-diffusion-xl/
│   ├── animatediff/
│   ├── tts/
│   └── llama/
│
├── output/                      # 생성된 쇼츠 저장
└── docs/                        # 문서들
```

---

## 🎉 시작하기

### 초간단 3단계:
1. **다운로드**: `Git` + `Python` + `Ollama` + `FFmpeg` 설치
2. **설치**: `install_windows.bat` 더블클릭
3. **실행**: `start_gui_windows.bat` 더블클릭

### 더 자세한 가이드:
- 빠른 시작: `QUICK_INSTALL.md`
- 완전한 가이드: `WINDOWS_INSTALLATION_GUIDE.md`
- 사용자 가이드: `WINDOWS_USER_GUIDE.md`

---

## 💡 핵심 요약

✅ **100% 로컬 PC 실행** - 외부 AI API 제로  
✅ **월 $0 비용** - 무제한 쇼츠 생성  
✅ **완전한 프라이버시** - 데이터 외부 전송 없음  
✅ **오프라인 가능** - 인터넷 연결 불필요 (설치 후)  
✅ **프로덕션 품질** - Stable Diffusion XL + AnimateDiff  

**지금 바로 시작하세요! 🚀**

---

## 📞 지원 및 문의

- 설치 가이드: `WINDOWS_INSTALLATION_GUIDE.md`
- 빠른 참조: `QUICK_INSTALL.md`
- 사용자 가이드: `WINDOWS_USER_GUIDE.md`
- 문제 해결: `DEPLOYMENT_GUIDE.md` 참고

**프로젝트 GitHub**: `https://github.com/[YOUR_USERNAME]/local-shorts-system`
