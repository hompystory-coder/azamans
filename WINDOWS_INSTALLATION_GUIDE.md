# 🪟 Windows 설치 가이드 - 필수 프로그램 다운로드

**모든 다운로드 링크와 설치 방법을 한 곳에!**

---

## 📋 설치 순서

```
1. Python 3.10        (필수) ⭐
2. Git                (필수) ⭐
3. NVIDIA 드라이버    (GPU 있으면)
4. Ollama            (필수) ⭐
5. FFmpeg            (필수) ⭐
6. 프로젝트 다운로드  (필수) ⭐
```

---

## 1️⃣ Python 3.10 설치 (필수)

### **다운로드**
```
🔗 https://www.python.org/ftp/python/3.10.11/python-3.10.11-amd64.exe
```

또는

```
🔗 https://www.python.org/downloads/
→ Python 3.10.11 클릭
→ Windows installer (64-bit) 다운로드
```

### **설치 방법**

1. `python-3.10.11-amd64.exe` 다운로드
2. 실행
3. **⚠️ 중요!** `Add Python 3.10 to PATH` 체크박스 **반드시 체크**
4. `Install Now` 클릭
5. 설치 완료 대기 (2-3분)

### **확인**
```cmd
# 윈도우 키 + R → cmd → Enter
python --version
# Python 3.10.11 출력되면 성공!
```

**스크린샷 위치**
```
https://docs.python.org/3/using/windows.html
```

---

## 2️⃣ Git 설치 (필수)

### **다운로드**
```
🔗 https://github.com/git-for-windows/git/releases/download/v2.43.0.windows.1/Git-2.43.0-64-bit.exe
```

또는

```
🔗 https://git-scm.com/download/win
→ Click here to download 클릭
```

### **설치 방법**

1. `Git-2.43.0-64-bit.exe` 다운로드
2. 실행
3. 모든 옵션 기본값으로 `Next` 연속 클릭
4. `Install` 클릭
5. 설치 완료

### **확인**
```cmd
git --version
# git version 2.43.0.windows.1 출력되면 성공!
```

---

## 3️⃣ NVIDIA GPU 드라이버 (GPU 있으면 설치)

### **GPU 확인**
```
작업 관리자 (Ctrl + Shift + Esc) → 성능 탭
→ GPU 0, GPU 1 등이 있는지 확인
```

### **드라이버 다운로드**

#### **방법 1: 자동 감지 (권장)**
```
🔗 https://www.nvidia.com/Download/index.aspx?lang=en-us

1. "GeForce Drivers" 선택
2. "Automatically Find Drivers" 클릭
3. 드라이버 다운로드
```

#### **방법 2: 수동 선택**
```
🔗 https://www.nvidia.com/drivers

예시 (RTX 3060):
- Product Type: GeForce
- Product Series: GeForce RTX 30 Series
- Product: GeForce RTX 3060
- Operating System: Windows 11
- Download Type: Game Ready Driver
→ Search → Download
```

### **설치**
1. 다운로드한 `.exe` 실행
2. `NVIDIA Graphics Driver` 선택
3. `Agree and Continue`
4. 설치 완료 후 **재부팅**

### **확인**
```cmd
nvidia-smi
# GPU 정보 출력되면 성공!
```

---

## 4️⃣ Ollama 설치 (필수)

### **다운로드**
```
🔗 https://ollama.ai/download/OllamaSetup.exe
```

또는

```
🔗 https://ollama.ai/download
→ Download for Windows 클릭
```

### **설치 방법**

1. `OllamaSetup.exe` 다운로드
2. 실행
3. 자동 설치 (1분)
4. 백그라운드에서 자동 실행됨

### **확인**
```cmd
ollama --version
# ollama version is x.x.x 출력되면 성공!
```

### **LLaMA 모델 다운로드 (4.7 GB)**
```cmd
ollama pull llama3.1:8b
```

**소요 시간**: 5-10분 (인터넷 속도에 따라)

---

## 5️⃣ FFmpeg 설치 (필수)

### **다운로드**
```
🔗 https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip
```

또는

```
🔗 https://www.gyan.dev/ffmpeg/builds/
→ ffmpeg-release-essentials.zip 클릭
```

### **설치 방법**

#### **Step 1: 압축 해제**
1. `ffmpeg-release-essentials.zip` 다운로드
2. 압축 해제 (예: `C:\ffmpeg`)
3. 폴더 구조 확인:
   ```
   C:\ffmpeg\
   ├── bin\
   │   ├── ffmpeg.exe  ← 이 파일이 있어야 함
   │   ├── ffplay.exe
   │   └── ffprobe.exe
   ├── doc\
   └── presets\
   ```

#### **Step 2: 환경변수 PATH 추가**

1. **시스템 속성 열기**
   ```
   윈도우 키 + R → sysdm.cpl → Enter
   ```

2. **환경 변수 열기**
   ```
   "고급" 탭 → "환경 변수" 버튼 클릭
   ```

3. **Path 편집**
   ```
   "시스템 변수" 섹션에서 "Path" 선택 → "편집" 클릭
   ```

4. **경로 추가**
   ```
   "새로 만들기" 클릭 → C:\ffmpeg\bin 입력 → "확인"
   ```

5. **모두 확인**
   ```
   "확인" → "확인" → "확인" (3번)
   ```

6. **CMD 재시작** (중요!)

### **확인**
```cmd
# 새 CMD 창 열기 (기존 창은 X)
ffmpeg -version
# ffmpeg version N-... 출력되면 성공!
```

### **그림으로 보기**
```
🔗 https://www.geeksforgeeks.org/how-to-install-ffmpeg-on-windows/
```

---

## 6️⃣ 프로젝트 다운로드 (필수)

### **방법 1: Git Clone (권장)**

```cmd
# 다운로드 위치로 이동 (예: 내 문서)
cd C:\Users\%USERNAME%\Documents

# 프로젝트 클론
git clone https://github.com/your-username/local-shorts-system.git

# 또는 직접 경로
git clone <repository-url> local-shorts-system

# 폴더 이동
cd local-shorts-system
```

### **방법 2: ZIP 다운로드**

```
🔗 프로젝트 GitHub 페이지
→ Code (녹색 버튼) → Download ZIP
```

1. ZIP 파일 다운로드
2. `C:\Users\사용자이름\Documents` 에 압축 해제
3. 폴더 이름을 `local-shorts-system` 으로 변경

---

## ✅ 설치 완료 확인

### **모든 프로그램 확인**

```cmd
# 새 CMD 창 열기
python --version
git --version
nvidia-smi
ollama --version
ffmpeg -version
```

**예상 출력**
```
Python 3.10.11
git version 2.43.0.windows.1
NVIDIA-SMI 545.92    Driver Version: 545.92    CUDA Version: 12.3
ollama version is 0.1.17
ffmpeg version N-113088-g3890a96
```

---

## 🚀 다음 단계: 프로젝트 설치

모든 프로그램 설치 완료 후:

### **1. 프로젝트 폴더로 이동**
```cmd
cd C:\Users\%USERNAME%\Documents\local-shorts-system
```

### **2. 설치 BAT 실행**
```cmd
install_windows.bat
```

또는

**더블클릭**
```
📁 C:\Users\사용자이름\Documents\local-shorts-system
📄 install_windows.bat 더블클릭
```

### **3. 모델 다운로드**
```cmd
download_models_windows.bat
```

### **4. 실행**
```cmd
start_gui_windows.bat
```

---

## 📥 빠른 다운로드 링크 모음

### **필수 프로그램 (5개)**

| 프로그램 | 다운로드 링크 | 크기 |
|---------|--------------|------|
| **Python 3.10** | https://www.python.org/ftp/python/3.10.11/python-3.10.11-amd64.exe | 25 MB |
| **Git** | https://github.com/git-for-windows/git/releases/download/v2.43.0.windows.1/Git-2.43.0-64-bit.exe | 48 MB |
| **Ollama** | https://ollama.ai/download/OllamaSetup.exe | 540 MB |
| **FFmpeg** | https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip | 78 MB |
| **NVIDIA Driver** | https://www.nvidia.com/Download/index.aspx | 700 MB |

### **AI 모델 (자동 다운로드)**
- Stable Diffusion XL: 6.9 GB
- AnimateDiff: 1.7 GB
- Coqui TTS: 2.0 GB
- LLaMA 3.1: 4.7 GB

**총 크기**: 15.3 GB (프로젝트 설치 후 자동 다운로드)

---

## 🔧 설치 스크립트 (PowerShell)

**자동 다운로드 스크립트** (고급 사용자용)

```powershell
# PowerShell을 관리자 권한으로 실행

# Python 다운로드
Invoke-WebRequest -Uri "https://www.python.org/ftp/python/3.10.11/python-3.10.11-amd64.exe" -OutFile "$env:TEMP\python-installer.exe"
Start-Process -FilePath "$env:TEMP\python-installer.exe" -ArgumentList "/quiet InstallAllUsers=1 PrependPath=1" -Wait

# Git 다운로드
Invoke-WebRequest -Uri "https://github.com/git-for-windows/git/releases/download/v2.43.0.windows.1/Git-2.43.0-64-bit.exe" -OutFile "$env:TEMP\git-installer.exe"
Start-Process -FilePath "$env:TEMP\git-installer.exe" -ArgumentList "/VERYSILENT" -Wait

# Ollama 다운로드
Invoke-WebRequest -Uri "https://ollama.ai/download/OllamaSetup.exe" -OutFile "$env:TEMP\ollama-installer.exe"
Start-Process -FilePath "$env:TEMP\ollama-installer.exe" -Wait

# FFmpeg 다운로드 및 압축 해제
Invoke-WebRequest -Uri "https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip" -OutFile "$env:TEMP\ffmpeg.zip"
Expand-Archive -Path "$env:TEMP\ffmpeg.zip" -DestinationPath "C:\ffmpeg" -Force

# PATH 환경변수 추가
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\ffmpeg\bin", [EnvironmentVariableTarget]::Machine)

Write-Host "설치 완료! CMD를 재시작하세요."
```

**사용법**:
1. `Win + X` → `Windows PowerShell (관리자)`
2. 위 스크립트 복사 & 붙여넣기
3. Enter

---

## 📱 설치 도움말

### **Python PATH 추가 확인**
```cmd
where python
# C:\Python310\python.exe 출력되어야 함
```

안 나오면:
```
제어판 → 시스템 → 고급 시스템 설정 → 환경 변수
→ Path에 C:\Python310 추가
```

### **FFmpeg PATH 추가 확인**
```cmd
where ffmpeg
# C:\ffmpeg\bin\ffmpeg.exe 출력되어야 함
```

---

## ❓ 자주 묻는 질문

### **Q: Python 3.10이 아닌 3.12도 되나요?**
A: 3.10 권장. 3.11도 가능. 3.12는 일부 패키지 호환성 문제 있을 수 있음.

### **Q: GPU가 없어도 되나요?**
A: 네! CPU만으로도 작동합니다. (속도는 느림: 30-45분/쇼츠)

### **Q: Windows 10도 되나요?**
A: 네! Windows 10/11 모두 지원합니다.

### **Q: 설치 공간이 얼마나 필요한가요?**
```
프로그램:   2 GB
AI 모델:    15.3 GB
여유 공간:  10 GB
━━━━━━━━━━━━━━━━━━━
총 필요:    약 30 GB
```

---

## 🎯 체크리스트

설치 전 체크리스트:

```
□ Windows 10 또는 11
□ 디스크 여유 공간 30GB+
□ 인터넷 연결
□ 관리자 권한

설치 완료 체크리스트:

□ Python 3.10 설치 (python --version)
□ Git 설치 (git --version)
□ Ollama 설치 (ollama --version)
□ FFmpeg 설치 (ffmpeg -version)
□ NVIDIA 드라이버 (nvidia-smi) - 선택
□ 프로젝트 다운로드
□ install_windows.bat 실행
□ download_models_windows.bat 실행
□ start_gui_windows.bat 실행
```

---

## 🎉 완료!

모든 프로그램 설치가 완료되었습니다!

### **다음 단계:**

```
1. 프로젝트 폴더 열기
   📁 C:\Users\사용자이름\Documents\local-shorts-system

2. install_windows.bat 더블클릭 (5분)

3. download_models_windows.bat 더블클릭 (30분)

4. start_gui_windows.bat 더블클릭 (시작!)
```

---

**작성일**: 2025-12-25  
**버전**: 1.0.0  
**도움이 필요하면**: WINDOWS_USER_GUIDE.md 참고

**🚀 모든 링크가 준비되었습니다! 🚀**
