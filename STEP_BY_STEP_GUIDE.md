# 🎯 AI 쇼츠 생성 시스템 - 완벽 설치 가이드

## 📌 이 가이드를 따라하면 100% 성공합니다!

---

# STEP 1: 필수 프로그램 다운로드 및 설치 (20분)

## 1-1. Python 3.10 설치 (5분)

### 다운로드:
1. 브라우저에서 이동: https://www.python.org/downloads/windows/
2. 페이지에서 **"Python 3.10.11"** 찾기
3. **"Windows installer (64-bit)"** 클릭하여 다운로드
4. 파일 이름: `python-3.10.11-amd64.exe` (약 25MB)

### 설치:
1. 다운로드한 `python-3.10.11-amd64.exe` 더블클릭
2. **⚠️ 중요**: 맨 아래 **"Add Python 3.10 to PATH"** 반드시 체크!
3. **"Install Now"** 클릭
4. 설치 완료까지 기다리기 (2-3분)
5. **"Close"** 클릭

### 설치 확인:
1. 키보드에서 `Windows키 + R` 누르기
2. `cmd` 입력 후 엔터
3. 아래 명령어 입력:
   ```
   python --version
   ```
4. 결과: `Python 3.10.11` 이라고 나오면 성공! ✅

---

## 1-2. Git 설치 (3분)

### 다운로드:
1. 브라우저에서 이동: https://git-scm.com/download/win
2. **"64-bit Git for Windows Setup"** 자동 다운로드 시작
3. 파일 이름: `Git-2.43.0-64-bit.exe` (약 50MB)

### 설치:
1. 다운로드한 `Git-2.43.0-64-bit.exe` 더블클릭
2. 모든 화면에서 **"Next"** 클릭 (기본 설정 사용)
3. 설치 완료까지 기다리기 (2분)
4. **"Finish"** 클릭

### 설치 확인:
1. 새 명령 프롬프트 열기 (`Windows키 + R` → `cmd`)
2. 아래 명령어 입력:
   ```
   git --version
   ```
3. 결과: `git version 2.43.0...` 이라고 나오면 성공! ✅

---

## 1-3. Ollama 설치 (5분)

### 다운로드:
1. 브라우저에서 이동: https://ollama.ai/download/windows
2. **"Download for Windows"** 클릭
3. 파일 이름: `OllamaSetup.exe` (약 500MB)

### 설치:
1. 다운로드한 `OllamaSetup.exe` 더블클릭
2. 자동으로 설치 시작 (3-4분)
3. 설치 완료 후 Ollama가 백그라운드에서 자동 실행됨
4. 시스템 트레이(오른쪽 하단)에 Ollama 아이콘 확인

### 설치 확인:
1. 명령 프롬프트 열기 (`Windows키 + R` → `cmd`)
2. 아래 명령어 입력:
   ```
   ollama --version
   ```
3. 결과: `ollama version...` 이라고 나오면 성공! ✅

---

## 1-4. FFmpeg 설치 (10분)

### 다운로드:
1. 브라우저에서 이동: https://www.gyan.dev/ffmpeg/builds/
2. **"ffmpeg-release-essentials.zip"** 클릭하여 다운로드
3. 파일 이름: `ffmpeg-release-essentials.zip` (약 100MB)

### 설치:
1. 다운로드한 ZIP 파일을 `C:\` 드라이브에 압축 해제
   - ZIP 파일 우클릭 → **"압축 풀기"**
   - 위치: `C:\ffmpeg` 로 설정
2. 폴더 구조 확인: `C:\ffmpeg\bin\ffmpeg.exe` 파일이 있어야 함

### PATH 추가 (중요!):
1. `Windows키` 눌러서 **"환경 변수"** 검색
2. **"시스템 환경 변수 편집"** 클릭
3. **"환경 변수"** 버튼 클릭
4. **"시스템 변수"** 섹션에서 **"Path"** 찾아서 더블클릭
5. **"새로 만들기"** 클릭
6. 입력: `C:\ffmpeg\bin`
7. **"확인"** 3번 클릭 (모든 창 닫기)

### 설치 확인:
1. **새로운** 명령 프롬프트 열기 (기존 창 닫고 다시 열기!)
2. 아래 명령어 입력:
   ```
   ffmpeg -version
   ```
3. 결과: `ffmpeg version N-...` 이라고 나오면 성공! ✅

---

# STEP 2: AI 쇼츠 프로그램 다운로드 (2분)

## 방법 1: GitHub ZIP 다운로드 (가장 쉬움 - 권장!)

### 다운로드:
1. 브라우저에서 이동: https://github.com/hompystory-coder/azamans
2. 녹색 **"<> Code"** 버튼 클릭
3. **"Download ZIP"** 클릭
4. 파일 이름: `azamans-main.zip` (약 30MB)
5. 다운로드 완료까지 기다리기

### 압축 해제:
1. 다운로드한 `azamans-main.zip` 파일 찾기 (보통 "다운로드" 폴더)
2. 파일 우클릭 → **"압축 풀기"** 또는 **"Extract All"**
3. 압축 풀 위치: `C:\Users\[사용자이름]\Documents\` 권장
4. **"압축 풀기"** 버튼 클릭
5. 압축이 풀리면 `azamans-main` 폴더 생성됨

### 폴더 이동:
1. `azamans-main` 폴더 열기
2. 그 안에 `local-shorts-system` 폴더 찾기
3. **이 폴더가 작업 폴더입니다!** 📁

---

## 방법 2: Git Clone (Git 사용 편한 분)

### 다운로드:
1. 명령 프롬프트 열기
2. 작업할 폴더로 이동:
   ```
   cd C:\Users\%USERNAME%\Documents
   ```
3. Git Clone 실행:
   ```
   git clone https://github.com/hompystory-coder/azamans.git
   ```
4. 완료 메시지 확인

### 폴더 이동:
```
cd azamans\local-shorts-system
```

---

# STEP 3: Python 라이브러리 설치 (5분)

## 자동 설치 (권장)

### 방법:
1. `local-shorts-system` 폴더 열기
2. **`install_windows.bat`** 파일 찾기
3. **더블클릭!** 🖱️

### 무슨 일이 일어나나요?
- 검은 창(명령 프롬프트)이 자동으로 열립니다
- 자동으로 필요한 Python 라이브러리들이 설치됩니다
- 설치 내용:
  ```
  Installing collected packages...
  - fastapi
  - uvicorn
  - torch
  - diffusers
  - transformers
  - TTS
  - ... (약 50개의 라이브러리)
  ```
- **약 5분 소요**
- 완료되면 자동으로 창이 닫힙니다

### 문제 해결:
만약 `install_windows.bat`이 없다면, 수동으로 설치:
1. `local-shorts-system` 폴더에서 Shift + 우클릭
2. **"여기에 PowerShell 창 열기"** 클릭
3. 아래 명령어 입력:
   ```
   python -m pip install --upgrade pip
   pip install -r requirements.txt
   ```

---

# STEP 4: AI 모델 다운로드 (30-60분, 15.3GB)

## 자동 다운로드 (권장)

### 방법:
1. `local-shorts-system` 폴더에서
2. **`download_models_windows.bat`** 파일 찾기
3. **더블클릭!** 🖱️

### 무슨 일이 일어나나요?
- 검은 창이 열립니다
- 자동으로 4개의 AI 모델이 다운로드됩니다:

```
1. Stable Diffusion XL 다운로드 중... (6.9 GB)
   [████████░░░░░░░░] 진행률 표시
   
2. AnimateDiff 다운로드 중... (1.7 GB)
   [████████████░░░░] 진행률 표시
   
3. Coqui TTS 다운로드 중... (2.0 GB)
   [████████████████] 진행률 표시
   
4. LLaMA 3.1 다운로드 중... (4.7 GB)
   ollama pull llama3.1:8b
   [████████████████] 진행률 표시
```

### 소요 시간 (인터넷 속도에 따라):
- **빠른 인터넷 (100Mbps 이상)**: 30-40분
- **보통 인터넷 (50Mbps)**: 50-60분
- **느린 인터넷 (20Mbps)**: 1-2시간

### 💡 팁:
- **이 작업은 최초 1회만 필요합니다!**
- 다운로드 중에는 컴퓨터를 끄지 마세요
- 인터넷 연결을 유지하세요
- 다른 작업을 해도 괜찮습니다

### 완료 확인:
다운로드가 끝나면 이런 메시지가 나옵니다:
```
✅ All models downloaded successfully!
Models directory: C:\Users\...\local-shorts-system\models\

Press any key to close...
```

---

# STEP 5: 프로그램 실행! 🚀

## 방법 1: GUI로 실행 (가장 쉬움 - 권장!)

### 실행:
1. `local-shorts-system` 폴더에서
2. **`start_gui_windows.bat`** 파일 찾기
3. **더블클릭!** 🖱️

### 무슨 일이 일어나나요?
1. **Ollama 서버 시작 중...**
   ```
   Starting Ollama service...
   ✅ Ollama is running
   ```

2. **FastAPI 백엔드 시작 중...**
   ```
   INFO: Uvicorn running on http://localhost:8000
   ```

3. **GUI 창이 열립니다!** 🎨

### GUI 사용법:

```
╔═══════════════════════════════════════════════════════════╗
║        Local AI Shorts Generator - GUI                    ║
╠═══════════════════════════════════════════════════════════╣
║                                                           ║
║  입력 URL 또는 텍스트:                                      ║
║  ┌─────────────────────────────────────────────────────┐ ║
║  │ https://blog.example.com/article                    │ ║
║  └─────────────────────────────────────────────────────┘ ║
║                                                           ║
║  캐릭터 선택:                                              ║
║  ┌─────────────────────────────────────────────────────┐ ║
║  │ pixar_young_asian_man_glasses ▼                     │ ║
║  └─────────────────────────────────────────────────────┘ ║
║                                                           ║
║  ┌──────────────────────────────────────────────────┐    ║
║  │          🎬 Generate Shorts                      │    ║
║  └──────────────────────────────────────────────────┘    ║
║                                                           ║
║  상태: Ready                                              ║
║  ─────────────────────────────────────────────────────   ║
║  진행률: [                    ] 0%                        ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
```

### 쇼츠 생성하기:
1. **"입력 URL 또는 텍스트"** 란에 입력:
   - 블로그 URL: `https://blog.naver.com/...`
   - 또는 직접 텍스트: `AI 기술의 미래에 대해...`

2. **"캐릭터 선택"** 드롭다운에서 선택:
   - `pixar_young_asian_man_glasses` (안경 쓴 젊은 남성)
   - `pixar_young_woman_long_hair` (긴 머리 젊은 여성)
   - `pixar_child_boy_curious` (호기심 많은 소년)
   - ... (총 39종)

3. **"Generate Shorts"** 버튼 클릭! 🖱️

### 생성 과정:
```
상태: Generating script...                    ⏱️ 30초
진행률: [█████░░░░░░░░░░░░░░░] 25%

상태: Generating images...                    ⏱️ 45초
진행률: [██████████░░░░░░░░░░] 50%

상태: Generating audio...                     ⏱️ 180초
진행률: [███████████████░░░░░] 75%

상태: Generating video...                     ⏱️ 210초
진행률: [████████████████████] 100%

✅ Shorts generated successfully!
파일 위치: C:\...\local-shorts-system\output\shorts_20251225_123456.mp4
```

### 완료!
- **총 소요 시간**: 약 7-8분 (RTX 3060 기준)
- **결과 파일**: `output` 폴더에서 찾기
- **파일명 예시**: `shorts_20251225_123456.mp4`

---

## 방법 2: 웹 브라우저로 실행

### 실행:
1. **서버 시작**:
   - `run_windows.bat` 더블클릭

2. **브라우저 열기**:
   - `open_browser_windows.bat` 더블클릭
   - 또는 브라우저에서 수동으로: http://localhost:8000/docs

### Swagger UI 사용법:
1. 브라우저가 자동으로 열립니다
2. **"POST /api/shorts/generate"** 섹션 찾기
3. **"Try it out"** 버튼 클릭
4. JSON 입력:
   ```json
   {
     "url": "https://blog.example.com/article",
     "character_id": "pixar_young_asian_man_glasses"
   }
   ```
5. **"Execute"** 버튼 클릭
6. 결과 확인:
   ```json
   {
     "job_id": "abc123",
     "status": "processing",
     "message": "Shorts generation started"
   }
   ```

---

# STEP 6: 결과 확인! 🎉

## 생성된 쇼츠 찾기:

### 위치:
```
local-shorts-system/
└── output/
    ├── shorts_20251225_123456.mp4    ← 최종 쇼츠 영상!
    ├── videos/
    │   └── character_animation.mp4
    ├── audio/
    │   └── narration.wav
    └── temp/
        └── (임시 파일들)
```

### 쇼츠 열기:
1. `local-shorts-system` 폴더 열기
2. `output` 폴더 열기
3. 생성된 `.mp4` 파일 찾기
4. 더블클릭하여 재생! 🎬

---

# 📊 성능 및 소요 시간

## GPU 있을 때 (권장):

### RTX 3060 (12GB VRAM):
- 이미지 생성: 45초
- 음성 생성: 180초 (3분)
- 비디오 생성: 210초 (3.5분)
- 렌더링: 20초
- **총 시간: 약 7.5분/쇼츠** ⚡

### RTX 4090 (24GB VRAM):
- **총 시간: 약 4분/쇼츠** 🚀

## CPU만 사용할 때:
- **총 시간: 약 30-60분/쇼츠** 🐌
- (권장하지 않음, 하지만 작동은 합니다!)

---

# ❗ 문제 해결 (Troubleshooting)

## 문제 1: "Python을 찾을 수 없습니다"

### 원인:
Python PATH가 설정되지 않음

### 해결:
1. Python 재설치
2. 설치 시 **"Add Python to PATH"** 체크 확인!
3. 컴퓨터 재시작

---

## 문제 2: "ffmpeg를 찾을 수 없습니다"

### 원인:
FFmpeg PATH가 설정되지 않음

### 해결:
1. `C:\ffmpeg\bin\ffmpeg.exe` 파일 존재 확인
2. 환경 변수에 `C:\ffmpeg\bin` 추가 (STEP 1-4 참고)
3. **새로운** 명령 프롬프트 열기 (기존 창 닫고 다시 열기!)
4. `ffmpeg -version` 으로 확인

---

## 문제 3: "Ollama 연결 실패"

### 원인:
Ollama 서비스가 실행되지 않음

### 해결:
1. 시스템 트레이(오른쪽 하단)에서 Ollama 아이콘 확인
2. 없으면 시작 메뉴에서 "Ollama" 검색하여 실행
3. 또는 명령 프롬프트에서:
   ```
   ollama serve
   ```

---

## 문제 4: "CUDA out of memory" 에러

### 원인:
GPU 메모리 부족

### 해결:
1. 다른 GPU 사용 프로그램 종료 (게임, 영상 편집 등)
2. 또는 CPU 모드로 전환:
   - `backend/app.py` 파일 열기
   - `device = "cuda"` 를 `device = "cpu"` 로 변경
   - (속도는 느려지지만 작동합니다)

---

## 문제 5: "모델 다운로드 실패"

### 원인:
인터넷 연결 불안정 또는 디스크 공간 부족

### 해결:
1. 인터넷 연결 확인
2. 디스크 공간 확인 (최소 20GB 필요)
3. 다시 시도: `download_models_windows.bat` 더블클릭
4. 수동 다운로드:
   ```
   cd local-shorts-system
   python scripts/install_models.py
   ollama pull llama3.1:8b
   ```

---

# 💡 유용한 팁

## 팁 1: 빠른 생성 속도
- **GPU 사용** (RTX 3060 이상 권장)
- CUDA Toolkit 설치
- 다른 GPU 사용 프로그램 종료

## 팁 2: 고품질 쇼츠
- 긴 텍스트보다 핵심 내용만 입력
- 적절한 캐릭터 선택
- 16:9 화면 비율 사용

## 팁 3: 여러 쇼츠 생성
- GUI에서 **"Generate Shorts"** 여러 번 클릭 가능
- 각 쇼츠는 별도 파일로 저장됨
- 파일명에 타임스탬프 포함

## 팁 4: 오프라인 사용
- 최초 설치 및 모델 다운로드 후
- **인터넷 연결 없이도 작동!**
- 완전한 프라이버시 보장

---

# 🎯 요약: 처음부터 끝까지

```
✅ STEP 1: Python, Git, Ollama, FFmpeg 설치 (20분)
✅ STEP 2: GitHub ZIP 다운로드 및 압축 해제 (2분)
✅ STEP 3: install_windows.bat 더블클릭 (5분)
✅ STEP 4: download_models_windows.bat 더블클릭 (30-60분)
✅ STEP 5: start_gui_windows.bat 더블클릭 (2분)
✅ STEP 6: URL 입력 → 캐릭터 선택 → Generate! (7-8분)

총 소요 시간: 약 1-1.5시간 (최초 설치)
이후 사용: 7-8분/쇼츠
```

---

# 🚀 지금 바로 시작하세요!

1. **STEP 1**부터 순서대로 따라하세요
2. 각 단계의 **"설치 확인"**을 꼭 하세요
3. 문제 발생 시 **"문제 해결"** 섹션 참고
4. **완료되면 무제한 쇼츠 생성!** 🎉

---

# 📞 추가 도움말

- **완전한 가이드**: `WINDOWS_INSTALLATION_GUIDE.md`
- **빠른 참조**: `QUICK_INSTALL.md`
- **사용자 가이드**: `WINDOWS_USER_GUIDE.md`
- **다운로드 링크**: `DOWNLOAD_LINKS.txt`

---

**행운을 빕니다! 🍀**
**무제한 AI 쇼츠 생성을 즐기세요! 🎬✨**
