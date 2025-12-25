# 🚀 빠른 설치 가이드 - 한 페이지로 끝내기

**Windows PC에서 5분 만에 시작!**

---

## ⚡ 초간단 3단계

```
1️⃣ 필수 프로그램 설치 (10분)
2️⃣ 프로젝트 설치 (5분)
3️⃣ 실행 (클릭!)
```

---

## 1️⃣ 필수 프로그램 설치

### **자동 다운로드**
```
📄 download_links_windows.bat 더블클릭
→ 모든 다운로드 페이지가 열림
```

### **수동 다운로드**

| 프로그램 | 다운로드 링크 (클릭!) |
|---------|---------------------|
| **Python 3.10** | https://www.python.org/ftp/python/3.10.11/python-3.10.11-amd64.exe |
| **Git** | https://github.com/git-for-windows/git/releases/download/v2.43.0.windows.1/Git-2.43.0-64-bit.exe |
| **Ollama** | https://ollama.ai/download/OllamaSetup.exe |
| **FFmpeg** | https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip |

### **설치 순서**

1. **Python** - `Add Python to PATH` 체크 필수!
2. **Git** - 기본값으로 설치
3. **Ollama** - 자동 설치
4. **FFmpeg** - 압축 해제 후 PATH 추가 (아래 참고)

---

## 💡 FFmpeg 빠른 설치

```
1. ffmpeg-release-essentials.zip 다운로드
2. C:\ 에 압축 해제 → C:\ffmpeg 폴더 생성됨
3. 환경변수 추가:
   Win + R → sysdm.cpl → Enter
   → 고급 → 환경 변수
   → Path 편집 → 새로 만들기
   → C:\ffmpeg\bin 입력 → 확인
4. CMD 재시작
```

---

## 2️⃣ 프로젝트 설치

### **다운로드**

**방법 A: Git**
```cmd
cd C:\Users\%USERNAME%\Documents
git clone <repository-url> local-shorts-system
cd local-shorts-system
```

**방법 B: ZIP**
- 프로젝트 ZIP 다운로드
- 내 문서에 압축 해제
- 폴더명: `local-shorts-system`

### **설치**

```
📄 install_windows.bat 더블클릭
(5분 대기)
```

### **모델 다운로드**

```
📄 download_models_windows.bat 더블클릭
(30분 대기, 최초 1회)
```

---

## 3️⃣ 실행

### **GUI 프로그램 (권장)**

```
📄 start_gui_windows.bat 더블클릭

→ GUI 프로그램 열림
→ "서버 시작" 클릭
→ URL 입력, 캐릭터 선택
→ "쇼츠 생성 시작" 클릭
→ 7-8분 대기
→ 완료!
```

### **웹 브라우저**

```
📄 run_windows.bat 더블클릭
📄 open_browser_windows.bat 더블클릭
→ http://localhost:8000/docs
```

---

## ✅ 확인

모든 프로그램 설치 확인:

```cmd
python --version
git --version
ollama --version
ffmpeg -version
```

---

## 📦 전체 파일

```
local-shorts-system/
├── 📄 download_links_windows.bat      ← 다운로드 링크 열기
├── 📄 install_windows.bat             ← 설치
├── 📄 download_models_windows.bat     ← 모델 다운로드
├── 📄 run_windows.bat                 ← 서버 실행
├── 📄 start_gui_windows.bat           ← GUI 실행 ⭐
└── 📄 open_browser_windows.bat        ← 브라우저 열기
```

---

## 🎯 한눈에 보기

```
필수 프로그램 다운로드 (10분)
  ↓
Python, Git, Ollama, FFmpeg 설치
  ↓
install_windows.bat 실행 (5분)
  ↓
download_models_windows.bat 실행 (30분, 최초 1회)
  ↓
start_gui_windows.bat 실행
  ↓
쇼츠 생성! 🎉
```

---

## 🆘 문제 해결

### **Python을 찾을 수 없습니다**
→ Python 재설치 시 `Add Python to PATH` 체크

### **FFmpeg를 찾을 수 없습니다**
→ 환경변수 PATH에 `C:\ffmpeg\bin` 추가

### **Ollama 연결 실패**
→ 작업 관리자에서 `ollama.exe` 확인

---

## 📚 자세한 가이드

- **WINDOWS_INSTALLATION_GUIDE.md** - 상세 설치
- **WINDOWS_USER_GUIDE.md** - 사용 설명서
- **QUICK_START.md** - 빠른 시작

---

## 💾 다운로드 크기

```
Python:      25 MB
Git:         48 MB
Ollama:      540 MB
FFmpeg:      78 MB
━━━━━━━━━━━━━━━━━
프로그램:    700 MB

AI 모델:     15.3 GB (자동 다운로드)
━━━━━━━━━━━━━━━━━
총 용량:     ~16 GB
```

---

## 🎉 완료!

**설치가 끝나면:**

```
start_gui_windows.bat 더블클릭
→ 클릭만으로 AI 쇼츠 생성!
```

---

**버전**: 1.0.0  
**날짜**: 2025-12-25  
**라이선스**: MIT

**🚀 지금 바로 시작하세요! 🚀**
