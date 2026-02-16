@echo off
color 0A
title AI Shorts Generator - Easy Installer

echo.
echo ============================================================
echo.
echo          AI Shorts Generator - Easy Installer
echo.
echo          Easy Installation for Everyone!
echo.
echo ============================================================
echo.
echo This program will automatically:
echo   [OK] Check required programs
echo   [OK] Install Python libraries
echo   [OK] Download AI models (15.3GB)
echo   [OK] Complete all setup
echo.
echo Estimated time: 40-70 minutes (First time only)
echo.
pause

:: ============================================
:: Step 1: System Check
:: ============================================
cls
echo.
echo ============================================================
echo  Step 1: System Check...
echo ============================================================
echo.

:: Check Python
echo [1/4] Checking Python...
python --version >nul 2>&1
if errorlevel 1 (
    echo [X] Python is not installed!
    echo.
    echo [!] Opening Python download page...
    start https://www.python.org/downloads/windows/
    echo.
    echo IMPORTANT:
    echo    1. Download "Python 3.10.11"
    echo    2. Check "Add Python to PATH" during installation!
    echo    3. Run this program again after installation
    echo.
    pause
    exit /b 1
)
echo [OK] Python installed
for /f "tokens=*" %%i in ('python --version') do set PYTHON_VERSION=%%i
echo     Version: %PYTHON_VERSION%
timeout /t 2 /nobreak >nul

:: Check Git
echo [2/4] Checking Git...
git --version >nul 2>&1
if errorlevel 1 (
    echo [!] Git is not installed (Optional)
) else (
    echo [OK] Git installed
    for /f "tokens=*" %%i in ('git --version') do set GIT_VERSION=%%i
    echo     Version: %GIT_VERSION%
)
timeout /t 2 /nobreak >nul

:: Check Ollama
echo [3/4] Checking Ollama...
ollama --version >nul 2>&1
if errorlevel 1 (
    echo [X] Ollama is not installed!
    echo.
    echo [!] Opening Ollama download page...
    start https://ollama.ai/download/windows
    echo.
    echo Please install Ollama and run this program again
    echo.
    pause
    exit /b 1
)
echo [OK] Ollama installed
timeout /t 2 /nobreak >nul

:: Check FFmpeg
echo [4/4] Checking FFmpeg...
ffmpeg -version >nul 2>&1
if errorlevel 1 (
    echo [X] FFmpeg is not installed!
    echo.
    echo [!] Opening FFmpeg download page...
    start https://www.gyan.dev/ffmpeg/builds/
    echo.
    echo Installation steps:
    echo    1. Download "ffmpeg-release-essentials.zip"
    echo    2. Extract to C:\ffmpeg
    echo    3. Add "C:\ffmpeg\bin" to system PATH
    echo    4. Restart computer
    echo    5. Run this program again
    echo.
    pause
    exit /b 1
)
echo [OK] FFmpeg installed
timeout /t 2 /nobreak >nul

echo.
echo [OK] All required programs are installed!
timeout /t 3 /nobreak >nul

:: ============================================
:: Step 2: Install Python Libraries
:: ============================================
cls
echo.
echo ============================================================
echo  Step 2: Installing Python Libraries... (5 minutes)
echo ============================================================
echo.

echo [*] Upgrading pip...
python -m pip install --upgrade pip --quiet
if errorlevel 1 (
    echo [X] Failed to upgrade pip
    pause
    exit /b 1
)
echo [OK] pip upgraded
echo.

echo [*] Installing required libraries... (About 50 packages, 5 min)
echo     Please wait...
echo.
pip install -r requirements.txt
if errorlevel 1 (
    echo [X] Failed to install libraries
    echo.
    echo Solutions:
    echo   1. Check internet connection
    echo   2. Run this program again
    pause
    exit /b 1
)

echo.
echo [OK] Python libraries installed successfully!
timeout /t 3 /nobreak >nul

:: ============================================
:: Step 3: Download AI Models
:: ============================================
cls
echo.
echo ============================================================
echo  Step 3: Downloading AI Models... (15.3GB, 30-60 min)
echo ============================================================
echo.
echo NOTICE:
echo   - Total download: 15.3GB
echo   - Time required: 30-60 minutes (depends on internet speed)
echo   - Do not turn off computer during download!
echo   - Required only once
echo.
pause

echo.
echo ============================================================
echo 1/4: Downloading Stable Diffusion XL... (6.9 GB)
echo ============================================================
echo.
python scripts/install_models.py
if errorlevel 1 (
    echo [!] Some models download failed (Continuing...)
)

echo.
echo ============================================================
echo 4/4: Downloading LLaMA 3.1... (4.7 GB)
echo ============================================================
echo.
ollama pull llama3.1:8b
if errorlevel 1 (
    echo [X] LLaMA 3.1 download failed
    echo.
    echo Solutions:
    echo   1. Check Ollama service is running
    echo   2. Check internet connection
    echo   3. Run this program again
    pause
    exit /b 1
)

:: ============================================
:: Step 4: Installation Complete
:: ============================================
cls
echo.
echo ============================================================
echo.
echo              Installation Complete!
echo.
echo ============================================================
echo.
echo [OK] All installation completed successfully!
echo.
echo Installation Summary:
echo   [OK] Python libraries: About 50 packages
echo   [OK] AI models (15.3GB):
echo        - Stable Diffusion XL (6.9 GB)
echo        - AnimateDiff (1.7 GB)
echo        - Coqui TTS (2.0 GB)
echo        - LLaMA 3.1 (4.7 GB)
echo.
echo ============================================================
echo  You can now run the program!
echo ============================================================
echo.
echo How to run:
echo   1. Double-click "START_HERE.bat"
echo   2. Or double-click "start_gui_windows.bat"
echo.
echo Help:
echo   - User Guide: README.md
echo   - Troubleshooting: STEP_BY_STEP_GUIDE.md
echo.
pause

:: Auto-launch GUI
echo.
echo [*] Launching GUI automatically...
timeout /t 3 /nobreak >nul
start start_gui_windows.bat

exit /b 0
