"""
🎬 로컬 PC AI 쇼츠 생성 시스템 - Windows GUI
간단한 클릭으로 AI 쇼츠 생성!
"""

import tkinter as tk
from tkinter import ttk, scrolledtext, messagebox, filedialog
import requests
import threading
import time
import webbrowser
import subprocess
import os
import sys
from pathlib import Path

class ShortsGeneratorGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("🎬 로컬 PC AI 쇼츠 생성 시스템")
        self.root.geometry("800x700")
        self.root.resizable(False, False)
        
        self.api_base = "http://localhost:8000"
        self.current_job_id = None
        self.server_process = None
        
        self.setup_ui()
        self.check_server_status()
    
    def setup_ui(self):
        """UI 구성"""
        # 메인 프레임
        main_frame = ttk.Frame(self.root, padding="20")
        main_frame.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # 제목
        title = ttk.Label(main_frame, text="🎬 AI 쇼츠 생성 시스템", 
                         font=("맑은 고딕", 20, "bold"))
        title.grid(row=0, column=0, columnspan=2, pady=10)
        
        # 서버 상태
        status_frame = ttk.LabelFrame(main_frame, text="서버 상태", padding="10")
        status_frame.grid(row=1, column=0, columnspan=2, sticky=(tk.W, tk.E), pady=10)
        
        self.status_label = ttk.Label(status_frame, text="❌ 서버 꺼짐", 
                                      font=("맑은 고딕", 10))
        self.status_label.grid(row=0, column=0, padx=10)
        
        self.server_btn = ttk.Button(status_frame, text="서버 시작", 
                                     command=self.toggle_server)
        self.server_btn.grid(row=0, column=1, padx=10)
        
        ttk.Button(status_frame, text="브라우저 열기", 
                  command=self.open_browser).grid(row=0, column=2, padx=10)
        
        # 입력 폼
        form_frame = ttk.LabelFrame(main_frame, text="쇼츠 생성", padding="10")
        form_frame.grid(row=2, column=0, columnspan=2, sticky=(tk.W, tk.E), pady=10)
        
        # URL 입력
        ttk.Label(form_frame, text="제품 URL:").grid(row=0, column=0, sticky=tk.W, pady=5)
        self.url_entry = ttk.Entry(form_frame, width=60)
        self.url_entry.grid(row=0, column=1, pady=5, padx=10)
        self.url_entry.insert(0, "https://shopping.naver.com/product-url")
        
        # 캐릭터 선택
        ttk.Label(form_frame, text="캐릭터:").grid(row=1, column=0, sticky=tk.W, pady=5)
        self.character_var = tk.StringVar(value="executive-fox")
        character_combo = ttk.Combobox(form_frame, textvariable=self.character_var, 
                                      state="readonly", width=57)
        character_combo['values'] = [
            "executive-fox (🦊 이그제큐티브 폭스)",
            "ceo-lion (🦁 CEO 라이온)",
            "tech-fox (🦊 테크 폭스)",
            "dev-raccoon (🦝 개발자 라쿤)",
            "fashionista-cat (😺 패셔니스타 캣)",
            "chef-penguin (🐧 셰프 펭귄)",
            "comedian-parrot (🦜 코미디언 패럿)"
        ]
        character_combo.grid(row=1, column=1, pady=5, padx=10)
        
        # 생성 버튼
        button_frame = ttk.Frame(main_frame)
        button_frame.grid(row=3, column=0, columnspan=2, pady=20)
        
        self.generate_btn = ttk.Button(button_frame, text="🎬 쇼츠 생성 시작", 
                                       command=self.start_generation, 
                                       style="Accent.TButton",
                                       width=30)
        self.generate_btn.grid(row=0, column=0, padx=10)
        
        self.cancel_btn = ttk.Button(button_frame, text="❌ 취소", 
                                     command=self.cancel_generation, 
                                     state=tk.DISABLED,
                                     width=15)
        self.cancel_btn.grid(row=0, column=1, padx=10)
        
        # 진행률
        progress_frame = ttk.LabelFrame(main_frame, text="진행 상황", padding="10")
        progress_frame.grid(row=4, column=0, columnspan=2, sticky=(tk.W, tk.E), pady=10)
        
        self.progress_var = tk.IntVar(value=0)
        self.progress_bar = ttk.Progressbar(progress_frame, variable=self.progress_var, 
                                           maximum=100, length=700)
        self.progress_bar.grid(row=0, column=0, pady=5)
        
        self.progress_label = ttk.Label(progress_frame, text="대기 중...")
        self.progress_label.grid(row=1, column=0, pady=5)
        
        # 로그
        log_frame = ttk.LabelFrame(main_frame, text="로그", padding="10")
        log_frame.grid(row=5, column=0, columnspan=2, sticky=(tk.W, tk.E, tk.N, tk.S), pady=10)
        
        self.log_text = scrolledtext.ScrolledText(log_frame, height=10, width=90, 
                                                  state=tk.DISABLED)
        self.log_text.grid(row=0, column=0)
        
        # 하단 버튼
        bottom_frame = ttk.Frame(main_frame)
        bottom_frame.grid(row=6, column=0, columnspan=2, pady=10)
        
        ttk.Button(bottom_frame, text="📁 출력 폴더 열기", 
                  command=self.open_output_folder).grid(row=0, column=0, padx=10)
        
        ttk.Button(bottom_frame, text="ℹ️ 도움말", 
                  command=self.show_help).grid(row=0, column=1, padx=10)
    
    def log(self, message):
        """로그 추가"""
        self.log_text.config(state=tk.NORMAL)
        self.log_text.insert(tk.END, f"{time.strftime('%H:%M:%S')} - {message}\n")
        self.log_text.see(tk.END)
        self.log_text.config(state=tk.DISABLED)
    
    def check_server_status(self):
        """서버 상태 확인"""
        try:
            resp = requests.get(f"{self.api_base}/health", timeout=2)
            if resp.status_code == 200:
                self.status_label.config(text="✅ 서버 실행 중")
                self.server_btn.config(text="서버 중지")
                return True
        except:
            pass
        
        self.status_label.config(text="❌ 서버 꺼짐")
        self.server_btn.config(text="서버 시작")
        return False
    
    def toggle_server(self):
        """서버 시작/중지"""
        if self.check_server_status():
            # 서버 중지
            if self.server_process:
                self.server_process.terminate()
                self.server_process = None
            self.log("서버를 중지했습니다.")
            self.status_label.config(text="❌ 서버 꺼짐")
            self.server_btn.config(text="서버 시작")
        else:
            # 서버 시작
            self.log("서버를 시작하는 중...")
            backend_path = Path(__file__).parent / "backend"
            
            # run_windows.bat 실행
            bat_path = Path(__file__).parent / "run_windows.bat"
            if bat_path.exists():
                self.server_process = subprocess.Popen(
                    [str(bat_path)],
                    shell=True,
                    creationflags=subprocess.CREATE_NEW_CONSOLE
                )
                time.sleep(5)
                if self.check_server_status():
                    self.log("✅ 서버가 시작되었습니다!")
                else:
                    self.log("❌ 서버 시작 실패. run_windows.bat를 수동으로 실행하세요.")
    
    def open_browser(self):
        """브라우저 열기"""
        webbrowser.open(f"{self.api_base}/docs")
        self.log("브라우저를 열었습니다.")
    
    def start_generation(self):
        """쇼츠 생성 시작"""
        if not self.check_server_status():
            messagebox.showerror("오류", "서버가 실행 중이 아닙니다!\n먼저 '서버 시작' 버튼을 클릭하세요.")
            return
        
        url = self.url_entry.get().strip()
        if not url:
            messagebox.showerror("오류", "제품 URL을 입력하세요!")
            return
        
        character_full = self.character_var.get()
        character_id = character_full.split(" ")[0]
        
        self.log(f"🎬 쇼츠 생성 시작: {character_full}")
        self.log(f"URL: {url}")
        
        # UI 업데이트
        self.generate_btn.config(state=tk.DISABLED)
        self.cancel_btn.config(state=tk.NORMAL)
        self.progress_var.set(0)
        self.progress_label.config(text="준비 중...")
        
        # 백그라운드 스레드로 실행
        thread = threading.Thread(target=self._generate_shorts, args=(url, character_id))
        thread.daemon = True
        thread.start()
    
    def _generate_shorts(self, url, character_id):
        """쇼츠 생성 (백그라운드)"""
        try:
            # 1. 생성 시작
            resp = requests.post(f"{self.api_base}/api/shorts/generate", json={
                "url": url,
                "character_id": character_id,
                "duration": 15
            }, timeout=30)
            
            if resp.status_code != 200:
                self.log(f"❌ 오류: {resp.text}")
                self._reset_ui()
                return
            
            data = resp.json()
            self.current_job_id = data["job_id"]
            self.log(f"✅ Job ID: {self.current_job_id}")
            self.log("⏳ 생성 중... (약 7-8분 소요)")
            
            # 2. 상태 폴링
            while True:
                time.sleep(5)
                
                resp = requests.get(f"{self.api_base}/api/shorts/status/{self.current_job_id}")
                status = resp.json()
                
                progress = status["progress"]
                message = status["message"]
                
                self.progress_var.set(progress)
                self.progress_label.config(text=f"{progress}% - {message}")
                self.log(f"📊 {progress}% - {message}")
                
                if status["status"] == "completed":
                    self.log("✅ 쇼츠 생성 완료!")
                    self._download_shorts()
                    break
                elif status["status"] == "failed":
                    error = status.get("error", "알 수 없는 오류")
                    self.log(f"❌ 생성 실패: {error}")
                    messagebox.showerror("생성 실패", error)
                    break
            
        except Exception as e:
            self.log(f"❌ 예외 발생: {str(e)}")
            messagebox.showerror("오류", str(e))
        finally:
            self._reset_ui()
    
    def _download_shorts(self):
        """쇼츠 다운로드"""
        try:
            # 다운로드
            resp = requests.get(f"{self.api_base}/api/shorts/download/{self.current_job_id}")
            
            # 저장 위치 선택
            filename = filedialog.asksaveasfilename(
                defaultextension=".mp4",
                filetypes=[("MP4 파일", "*.mp4")],
                initialfile=f"{self.current_job_id}.mp4"
            )
            
            if filename:
                with open(filename, "wb") as f:
                    f.write(resp.content)
                self.log(f"💾 저장 완료: {filename}")
                messagebox.showinfo("완료", f"쇼츠가 저장되었습니다!\n\n{filename}")
            
        except Exception as e:
            self.log(f"❌ 다운로드 실패: {str(e)}")
    
    def _reset_ui(self):
        """UI 리셋"""
        self.generate_btn.config(state=tk.NORMAL)
        self.cancel_btn.config(state=tk.DISABLED)
        self.current_job_id = None
    
    def cancel_generation(self):
        """생성 취소"""
        self.log("⚠️ 생성을 취소했습니다.")
        self._reset_ui()
    
    def open_output_folder(self):
        """출력 폴더 열기"""
        output_path = Path(__file__).parent / "output" / "videos"
        output_path.mkdir(parents=True, exist_ok=True)
        os.startfile(output_path)
    
    def show_help(self):
        """도움말"""
        help_text = """
🎬 로컬 PC AI 쇼츠 생성 시스템

사용 방법:
1. '서버 시작' 버튼 클릭 (최초 1회)
2. 제품 URL 입력
3. 캐릭터 선택
4. '쇼츠 생성 시작' 버튼 클릭
5. 완료까지 약 7-8분 대기

문제 해결:
- 서버가 시작되지 않으면: run_windows.bat 수동 실행
- 생성이 느리면: GPU 드라이버 확인
- 오류 발생 시: 로그 확인

문서: WINDOWS_USER_GUIDE.md
        """
        messagebox.showinfo("도움말", help_text)

def main():
    root = tk.Tk()
    app = ShortsGeneratorGUI(root)
    root.mainloop()

if __name__ == "__main__":
    main()
