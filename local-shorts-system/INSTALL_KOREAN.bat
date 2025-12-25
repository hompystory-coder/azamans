@echo off
color 0A
title AI Shorts Generator - Korean Version

echo.
echo ============================================================
echo.
echo          AI Shorts Generator - Automatic Installer
echo.
echo ============================================================
echo.
echo This will automatically:
echo   - Check required programs
echo   - Install Python libraries
echo   - Download AI models (15.3GB)
echo.
echo Time: 40-70 minutes (First time only)
echo.
pause

:: Step 1: System Check
cls
echo.
echo ============================================================
echo  Step 1/3: System Check...
echo ============================================================
echo.

echo [1/4] Python check...
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python not found!
    echo.
    echo Opening download page...
    start https://www.python.org/downloads/windows/
    echo.
    echo Please:
    echo   1. Download Python 3.10.11
    echo   2. Check "Add Python to PATH" during install
    echo   3. Run this program again
    echo.
    pause
    exit /b 1
)
echo [OK] Python found
timeout /t 1 /nobreak >nul

echo [2/4] Git check...
git --version >nul 2>&1
if errorlevel 1 (
    echo [SKIP] Git not found (Optional)
) else (
    echo [OK] Git found
)
timeout /t 1 /nobreak >nul

echo [3/4] Ollama check...
ollama --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Ollama not found!
    echo.
    echo Opening download page...
    start https://ollama.ai/download/windows
    echo.
    echo Please install and run this again
    pause
    exit /b 1
)
echo [OK] Ollama found
timeout /t 1 /nobreak >nul

echo [4/4] FFmpeg check...
ffmpeg -version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] FFmpeg not found!
    echo.
    echo Opening download page...
    start https://www.gyan.dev/ffmpeg/builds/
    echo.
    echo Install steps:
    echo   1. Download ffmpeg-release-essentials.zip
    echo   2. Extract to C:\ffmpeg
    echo   3. Add C:\ffmpeg\bin to PATH
    echo   4. Restart computer
    pause
    exit /b 1
)
echo [OK] FFmpeg found
timeout /t 1 /nobreak >nul

echo.
echo [SUCCESS] All programs ready!
timeout /t 2 /nobreak >nul

:: Step 2: Python Libraries
cls
echo.
echo ============================================================
echo  Step 2/3: Installing Python Libraries (5 min)
echo ============================================================
echo.

echo Upgrading pip...
python -m pip install --upgrade pip --quiet
echo [OK] pip upgraded
echo.

echo Installing libraries (about 50 packages)...
echo Please wait...
pip install -r requirements.txt
if errorlevel 1 (
    echo [ERROR] Installation failed
    pause
    exit /b 1
)

echo.
echo [SUCCESS] Libraries installed!
timeout /t 2 /nobreak >nul

:: Step 3: AI Models
cls
echo.
echo ============================================================
echo  Step 3/3: Downloading AI Models (15.3GB, 30-60 min)
echo ============================================================
echo.
echo NOTICE:
echo   - Size: 15.3GB
echo   - Time: 30-60 minutes
echo   - Do not turn off computer!
echo.
pause

echo.
echo Downloading models...
echo.
python scripts/install_models.py

echo.
echo Downloading LLaMA 3.1 (4.7 GB)...
ollama pull llama3.1:8b
if errorlevel 1 (
    echo [ERROR] Download failed
    pause
    exit /b 1
)

:: Complete
cls
echo.
echo ============================================================
echo.
echo              INSTALLATION COMPLETE!
echo.
echo ============================================================
echo.
echo [SUCCESS] Everything is ready!
echo.
echo Installed:
echo   - Python libraries: 50+ packages
echo   - AI models: 15.3GB
echo     * Stable Diffusion XL (6.9 GB)
echo     * AnimateDiff (1.7 GB)
echo     * Coqui TTS (2.0 GB)
echo     * LLaMA 3.1 (4.7 GB)
echo.
echo ============================================================
echo  How to run:
echo ============================================================
echo.
echo   Double-click "START_HERE.bat"
echo.
pause

echo.
echo Launching program...
timeout /t 2 /nobreak >nul
start START_HERE.bat

exit /b 0
