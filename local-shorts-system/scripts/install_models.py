#!/usr/bin/env python3
"""
📦 AI 모델 자동 다운로드 및 설치 스크립트
로컬 PC에서 쇼츠 생성에 필요한 모든 오픈소스 AI 모델 다운로드
"""

import os
import sys
from pathlib import Path
import subprocess
from loguru import logger
import torch

# 프로젝트 루트
BASE_DIR = Path(__file__).parent.parent
MODELS_DIR = BASE_DIR / "models"
MODELS_DIR.mkdir(parents=True, exist_ok=True)

def check_system():
    """시스템 요구사항 확인"""
    logger.info("🔍 Checking system requirements...")
    
    # Python 버전
    python_version = sys.version_info
    logger.info(f"   Python: {python_version.major}.{python_version.minor}.{python_version.micro}")
    
    if python_version < (3, 8):
        logger.error("❌ Python 3.8 이상이 필요합니다!")
        return False
    
    # PyTorch
    try:
        import torch
        logger.info(f"   PyTorch: {torch.__version__}")
        
        # CUDA 확인
        if torch.cuda.is_available():
            device_name = torch.cuda.get_device_name(0)
            vram = torch.cuda.get_device_properties(0).total_memory / 1024**3
            logger.info(f"   GPU: {device_name}")
            logger.info(f"   VRAM: {vram:.1f} GB")
            
            if vram < 6:
                logger.warning(f"⚠️ VRAM이 {vram:.1f}GB로 권장 사양(6GB 이상)보다 적습니다.")
                logger.warning("   일부 기능이 제한될 수 있습니다.")
        else:
            logger.warning("⚠️ CUDA 사용 불가 - CPU 모드로 실행됩니다 (느릴 수 있음)")
    except ImportError:
        logger.error("❌ PyTorch가 설치되지 않았습니다!")
        logger.info("   설치: pip install torch torchvision torchaudio")
        return False
    
    # 디스크 공간 확인
    import shutil
    total, used, free = shutil.disk_usage(MODELS_DIR)
    free_gb = free / 1024**3
    logger.info(f"   Free disk space: {free_gb:.1f} GB")
    
    if free_gb < 30:
        logger.error(f"❌ 디스크 여유 공간이 {free_gb:.1f}GB로 부족합니다! (최소 30GB 필요)")
        return False
    
    logger.info("✅ System requirements check passed!")
    return True

def install_package(package: str):
    """Python 패키지 설치"""
    try:
        logger.info(f"📦 Installing {package}...")
        subprocess.check_call([sys.executable, "-m", "pip", "install", package, "-q"])
        logger.info(f"   ✅ {package} installed")
        return True
    except subprocess.CalledProcessError:
        logger.error(f"   ❌ Failed to install {package}")
        return False

def download_stable_diffusion():
    """Stable Diffusion XL 다운로드"""
    logger.info("🎨 Downloading Stable Diffusion XL...")
    logger.info("   Model: stabilityai/stable-diffusion-xl-base-1.0")
    logger.info("   Size: ~6.9 GB")
    logger.info("   This may take 10-30 minutes depending on your internet speed...")
    
    try:
        from diffusers import StableDiffusionXLPipeline
        
        # 모델 다운로드 (캐시 디렉토리 지정)
        model_id = "stabilityai/stable-diffusion-xl-base-1.0"
        
        logger.info("   Downloading... (진행 상황이 표시됩니다)")
        pipe = StableDiffusionXLPipeline.from_pretrained(
            model_id,
            torch_dtype=torch.float16 if torch.cuda.is_available() else torch.float32,
            variant="fp16" if torch.cuda.is_available() else None,
            cache_dir=MODELS_DIR,
            use_safetensors=True
        )
        
        logger.info("✅ Stable Diffusion XL downloaded successfully!")
        
        # 메모리 해제
        del pipe
        if torch.cuda.is_available():
            torch.cuda.empty_cache()
        
        return True
        
    except Exception as e:
        logger.error(f"❌ Failed to download Stable Diffusion XL: {str(e)}")
        return False

def download_coqui_tts():
    """Coqui TTS 다운로드"""
    logger.info("🎙️ Downloading Coqui TTS (XTTS-v2)...")
    logger.info("   Model: xtts-v2 (multilingual)")
    logger.info("   Size: ~2 GB")
    
    try:
        from TTS.api import TTS
        
        # TTS 모델 다운로드
        logger.info("   Downloading... (자동으로 캐시됩니다)")
        tts = TTS(
            model_name="tts_models/multilingual/multi-dataset/xtts_v2",
            progress_bar=True
        )
        
        logger.info("✅ Coqui TTS downloaded successfully!")
        logger.info(f"   Supported languages: {tts.languages if hasattr(tts, 'languages') else 'multilingual'}")
        
        del tts
        return True
        
    except Exception as e:
        logger.error(f"❌ Failed to download Coqui TTS: {str(e)}")
        return False

def install_ollama():
    """Ollama 설치 안내 (LLM용)"""
    logger.info("🤖 Ollama (LLM) 설치 안내")
    logger.info("   Ollama는 별도 설치가 필요합니다.")
    logger.info("")
    logger.info("   1. https://ollama.com/ 에서 다운로드")
    logger.info("   2. 설치 후 다음 명령어 실행:")
    logger.info("      ollama pull llama3.1:8b")
    logger.info("")
    logger.info("   또는 CLI로 설치:")
    
    import platform
    system = platform.system()
    
    if system == "Linux":
        logger.info("      curl -fsSL https://ollama.com/install.sh | sh")
    elif system == "Darwin":  # macOS
        logger.info("      brew install ollama")
    elif system == "Windows":
        logger.info("      PowerShell에서: winget install Ollama.Ollama")
    
    logger.info("")
    logger.info("⚠️ Ollama는 선택사항입니다. AI 스크립트 생성 기능을 사용하려면 설치하세요.")
    
    return True

def verify_models():
    """다운로드된 모델 확인"""
    logger.info("🔍 Verifying downloaded models...")
    
    models_found = []
    
    # Stable Diffusion 확인
    sd_path = MODELS_DIR / "models--stabilityai--stable-diffusion-xl-base-1.0"
    if sd_path.exists():
        models_found.append("✅ Stable Diffusion XL")
    else:
        models_found.append("❌ Stable Diffusion XL (not found)")
    
    # TTS 확인 (Hugging Face 캐시)
    import os
    hf_home = os.environ.get("HF_HOME", Path.home() / ".cache" / "huggingface")
    tts_path = Path(hf_home) / "hub"
    if tts_path.exists():
        tts_models = list(tts_path.glob("models--coqui--*"))
        if tts_models:
            models_found.append("✅ Coqui TTS")
        else:
            models_found.append("❌ Coqui TTS (not found)")
    else:
        models_found.append("❌ Coqui TTS (not found)")
    
    # Ollama 확인
    try:
        result = subprocess.run(["ollama", "list"], capture_output=True, text=True)
        if "llama3.1" in result.stdout:
            models_found.append("✅ Ollama LLaMA 3.1")
        else:
            models_found.append("⚠️ Ollama installed but LLaMA 3.1 not pulled")
    except FileNotFoundError:
        models_found.append("❌ Ollama (not installed)")
    
    logger.info("")
    logger.info("📊 Model Status:")
    for status in models_found:
        logger.info(f"   {status}")
    
    return True

def main():
    """메인 설치 프로세스"""
    logger.info("=" * 60)
    logger.info("🚀 AI Shorts Generator - Model Installation")
    logger.info("=" * 60)
    logger.info("")
    
    # 1. 시스템 체크
    if not check_system():
        logger.error("❌ System requirements not met. Aborting.")
        return False
    
    logger.info("")
    logger.info("=" * 60)
    logger.info("📥 Starting model downloads...")
    logger.info("=" * 60)
    logger.info("")
    
    # 2. 필수 패키지 확인
    required_packages = {
        "diffusers": "diffusers>=0.25.0",
        "transformers": "transformers>=4.36.0",
        "TTS": "TTS>=0.22.0",
        "accelerate": "accelerate>=0.25.0",
    }
    
    for module, package in required_packages.items():
        try:
            __import__(module)
            logger.info(f"✅ {module} already installed")
        except ImportError:
            logger.info(f"📦 {module} not found, installing...")
            if not install_package(package):
                logger.error(f"❌ Failed to install {package}")
                return False
    
    logger.info("")
    
    # 3. 모델 다운로드
    logger.info("⏳ This will download ~10GB of AI models.")
    logger.info("   Please ensure you have:")
    logger.info("   - Good internet connection")
    logger.info("   - At least 30GB free disk space")
    logger.info("   - 30-60 minutes of time")
    logger.info("")
    
    input("Press Enter to continue or Ctrl+C to cancel...")
    logger.info("")
    
    # Stable Diffusion
    if not download_stable_diffusion():
        logger.error("❌ Stable Diffusion download failed")
        return False
    
    logger.info("")
    
    # Coqui TTS
    if not download_coqui_tts():
        logger.error("❌ Coqui TTS download failed")
        return False
    
    logger.info("")
    
    # Ollama 안내
    install_ollama()
    
    logger.info("")
    logger.info("=" * 60)
    logger.info("🎉 Model installation process complete!")
    logger.info("=" * 60)
    logger.info("")
    
    # 4. 검증
    verify_models()
    
    logger.info("")
    logger.info("✅ You can now run the backend server:")
    logger.info("   cd backend")
    logger.info("   python app.py")
    logger.info("")
    
    return True

if __name__ == "__main__":
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        logger.warning("\n⚠️ Installation cancelled by user")
        sys.exit(1)
    except Exception as e:
        logger.error(f"\n❌ Unexpected error: {str(e)}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
