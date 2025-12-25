@echo off
chcp 65001 > nul
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo   🎬 로컬 PC AI 쇼츠 생성 시스템 - Windows 설치
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

echo [1/7] Python 버전 확인...
python --version
if errorlevel 1 (
    echo ❌ Python이 설치되지 않았습니다!
    echo https://www.python.org/downloads/ 에서 Python 3.10 설치 후 다시 실행하세요.
    pause
    exit /b 1
)
echo ✅ Python 확인 완료
echo.

echo [2/7] 가상환경 생성...
if not exist "venv" (
    python -m venv venv
    echo ✅ 가상환경 생성 완료
) else (
    echo ⚠️ 가상환경이 이미 존재합니다
)
echo.

echo [3/7] 가상환경 활성화...
call venv\Scripts\activate.bat
echo ✅ 가상환경 활성화 완료
echo.

echo [4/7] PyTorch 설치 (CUDA 11.8)...
echo ⏳ 시간이 걸릴 수 있습니다 (2-3분)...
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118 --quiet
if errorlevel 1 (
    echo ⚠️ CUDA 버전 설치 실패. CPU 버전으로 재시도...
    pip install torch torchvision torchaudio --quiet
)
echo ✅ PyTorch 설치 완료
echo.

echo [5/7] 의존성 패키지 설치...
echo ⏳ 시간이 걸릴 수 있습니다 (3-5분)...
pip install -r requirements.txt --quiet
echo ✅ 의존성 설치 완료
echo.

echo [6/7] Playwright 브라우저 설치...
playwright install chromium
echo ✅ Playwright 설치 완료
echo.

echo [7/7] FFmpeg 확인...
where ffmpeg > nul 2>&1
if errorlevel 1 (
    echo ⚠️ FFmpeg가 설치되지 않았습니다
    echo https://www.gyan.dev/ffmpeg/builds/ 에서 다운로드 후 PATH에 추가하세요
) else (
    echo ✅ FFmpeg 확인 완료
)
echo.

echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo   ✅ 기본 설치 완료!
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.
echo 다음 단계:
echo 1. Ollama 설치: https://ollama.ai/download
echo 2. 모델 다운로드: download_models_windows.bat 실행
echo 3. 실행: run_windows.bat
echo.
pause
