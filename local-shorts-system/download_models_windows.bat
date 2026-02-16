@echo off
chcp 65001 > nul
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo   📥 AI 모델 다운로드 (15.3 GB)
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.
echo ⏳ 약 20-30분 소요됩니다...
echo 인터넷 연결을 유지해주세요.
echo.

call venv\Scripts\activate.bat

echo [1/4] Stable Diffusion XL (6.9 GB)...
python scripts\install_models.py

echo.
echo [2/4] Ollama 설치 확인...
where ollama > nul 2>&1
if errorlevel 1 (
    echo ❌ Ollama가 설치되지 않았습니다!
    echo https://ollama.ai/download 에서 다운로드하세요
    start https://ollama.ai/download
    echo.
    echo Ollama 설치 후 아무 키나 누르세요...
    pause
)

echo.
echo [3/4] LLaMA 3.1 모델 다운로드 (4.7 GB)...
ollama pull llama3.1:8b

echo.
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo   ✅ 모델 다운로드 완료!
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.
echo 다음 단계: run_windows.bat 실행
echo.
pause
