@echo off
chcp 65001 > nul
echo 🎬 Windows GUI 프로그램 시작...

REM 가상환경 활성화
call venv\Scripts\activate.bat

REM GUI 실행
python gui_windows.py

pause
