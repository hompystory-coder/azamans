@echo off
chcp 65001 >nul
color 0B
title AI 쇼츠 생성기 - 시작!

echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║              AI 쇼츠 생성기 시작!                           ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: 설치 확인
if not exist "models" (
    echo ⚠️  아직 설치가 완료되지 않았습니다!
    echo.
    echo 먼저 "EASY_INSTALLER.bat"를 실행하세요.
    echo.
    pause
    exit /b 1
)

echo ✅ 설치 확인 완료
echo.
echo 🚀 AI 쇼츠 생성기를 시작합니다...
echo.
echo ═══════════════════════════════════════════════════════════════
echo.

:: Ollama 서비스 시작
echo [1/2] Ollama 서비스 시작 중...
tasklist /FI "IMAGENAME eq ollama.exe" 2>NUL | find /I /N "ollama.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo    Ollama 실행 중...
    start /B ollama serve
    timeout /t 3 /nobreak >nul
) else (
    echo    ✅ Ollama 이미 실행 중
)
echo.

:: GUI 실행
echo [2/2] GUI 실행 중...
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║  ✨ GUI 창이 열립니다!                                       ║
echo ║                                                              ║
echo ║  사용 방법:                                                  ║
echo ║    1. URL 또는 텍스트 입력                                  ║
echo ║    2. 캐릭터 선택                                           ║
echo ║    3. "Generate Shorts" 버튼 클릭!                          ║
echo ║    4. 7-8분 후 output 폴더에서 결과 확인                   ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
timeout /t 3 /nobreak >nul

python gui_windows.py

if errorlevel 1 (
    echo.
    echo ❌ GUI 실행 실패
    echo.
    echo 문제 해결:
    echo   1. Python이 설치되어 있는지 확인
    echo   2. "EASY_INSTALLER.bat"를 다시 실행
    echo.
    pause
)

exit /b 0
