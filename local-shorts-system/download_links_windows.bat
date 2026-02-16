@echo off
chcp 65001 > nul
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo   📥 필수 프로그램 다운로드 페이지 열기
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

echo [1/5] Python 3.10 다운로드 페이지 열기...
start https://www.python.org/downloads/release/python-31011/
timeout /t 2 /nobreak > nul

echo [2/5] Git 다운로드 페이지 열기...
start https://git-scm.com/download/win
timeout /t 2 /nobreak > nul

echo [3/5] Ollama 다운로드 페이지 열기...
start https://ollama.ai/download
timeout /t 2 /nobreak > nul

echo [4/5] FFmpeg 다운로드 페이지 열기...
start https://www.gyan.dev/ffmpeg/builds/
timeout /t 2 /nobreak > nul

echo [5/5] NVIDIA 드라이버 다운로드 페이지 열기...
start https://www.nvidia.com/Download/index.aspx
timeout /t 2 /nobreak > nul

echo.
echo ✅ 모든 다운로드 페이지가 열렸습니다!
echo.
echo 📝 다운로드 할 파일:
echo   1. Python 3.10.11 - Windows installer (64-bit)
echo   2. Git for Windows - Latest version
echo   3. OllamaSetup.exe
echo   4. ffmpeg-release-essentials.zip
echo   5. NVIDIA Graphics Driver (GPU 있으면)
echo.
echo 자세한 설치 방법은 WINDOWS_INSTALLATION_GUIDE.md 참고
echo.
pause
