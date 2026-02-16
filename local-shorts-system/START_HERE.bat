@echo off
color 0B
title AI Shorts Generator - Start!

echo.
echo ============================================================
echo.
echo              AI Shorts Generator - Start!
echo.
echo ============================================================
echo.

:: Check installation
if not exist "models" (
    echo [!] Installation is not complete yet!
    echo.
    echo Please run "EASY_INSTALLER.bat" first.
    echo.
    pause
    exit /b 1
)

echo [OK] Installation verified
echo.
echo [*] Starting AI Shorts Generator...
echo.
echo ============================================================
echo.

:: Start Ollama service
echo [1/2] Starting Ollama service...
tasklist /FI "IMAGENAME eq ollama.exe" 2>NUL | find /I /N "ollama.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo     Running Ollama...
    start /B ollama serve
    timeout /t 3 /nobreak >nul
) else (
    echo     [OK] Ollama already running
)
echo.

:: Launch GUI
echo [2/2] Launching GUI...
echo.
echo ============================================================
echo.
echo   GUI window will open!
echo.
echo   How to use:
echo     1. Enter URL or text
echo     2. Select character
echo     3. Click "Generate Shorts" button!
echo     4. Check output folder after 7-8 minutes
echo.
echo ============================================================
echo.
timeout /t 3 /nobreak >nul

python gui_windows.py

if errorlevel 1 (
    echo.
    echo [X] Failed to launch GUI
    echo.
    echo Troubleshooting:
    echo   1. Check if Python is installed
    echo   2. Run "EASY_INSTALLER.bat" again
    echo.
    pause
)

exit /b 0
