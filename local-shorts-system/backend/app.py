#!/usr/bin/env python3
"""
🎬 로컬 PC AI 쇼츠 생성 시스템
FastAPI 백엔드 서버
"""

import os
import sys
from pathlib import Path
from typing import Optional
import uvicorn
from fastapi import FastAPI, HTTPException, BackgroundTasks, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse, FileResponse
from pydantic import BaseModel
from loguru import logger
import torch

# 프로젝트 루트 경로
BASE_DIR = Path(__file__).parent.parent
sys.path.insert(0, str(BASE_DIR))

# 환경 변수 로드
from dotenv import load_dotenv
load_dotenv()

# ========== FastAPI 앱 생성 ==========
app = FastAPI(
    title="Local AI Shorts Generator",
    description="사용자 PC 리소스를 활용한 로컬 AI 쇼츠 생성 API",
    version="0.1.0"
)

# CORS 설정
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # 프로덕션에서는 제한 필요
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ========== 서비스 임포트 ==========
from services.pipeline_service import PipelineService
from services.crawler_service import SimpleCrawler
import asyncio
import uuid

# ========== 전역 변수 ==========
OUTPUT_DIR = BASE_DIR / "output"
MODELS_DIR = BASE_DIR / "models"
VIDEOS_DIR = OUTPUT_DIR / "videos"
AUDIO_DIR = OUTPUT_DIR / "audio"
TEMP_DIR = OUTPUT_DIR / "temp"

# 디렉토리 생성
for dir_path in [OUTPUT_DIR, MODELS_DIR, VIDEOS_DIR, AUDIO_DIR, TEMP_DIR]:
    dir_path.mkdir(parents=True, exist_ok=True)

# GPU 확인
DEVICE = "cuda" if torch.cuda.is_available() else "cpu"
logger.info(f"🔧 Device: {DEVICE}")
if DEVICE == "cuda":
    logger.info(f"🎮 GPU: {torch.cuda.get_device_name(0)}")
    logger.info(f"💾 VRAM: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB")

# 파이프라인 서비스 초기화
pipeline = PipelineService(MODELS_DIR, OUTPUT_DIR, DEVICE)

# ========== Pydantic 모델 ==========
class ShortsGenerationRequest(BaseModel):
    """쇼츠 생성 요청"""
    url: Optional[str] = None
    text_content: Optional[str] = None
    character_id: str = "executive-fox"
    video_mode: str = "character_plus_images"
    category: str = "general"
    duration: int = 15  # 초
    aspect_ratio: str = "9:16"
    
class SystemInfoResponse(BaseModel):
    """시스템 정보"""
    device: str
    gpu_name: Optional[str] = None
    vram_gb: Optional[float] = None
    models_downloaded: bool = False
    status: str = "ready"

class JobStatusResponse(BaseModel):
    """작업 상태"""
    job_id: str
    status: str  # pending, processing, completed, failed
    progress: int  # 0-100
    message: str
    output_path: Optional[str] = None
    error: Optional[str] = None

# ========== API 엔드포인트 ==========

@app.get("/")
async def root():
    """루트 엔드포인트"""
    return {
        "name": "Local AI Shorts Generator",
        "version": "0.1.0",
        "status": "running",
        "device": DEVICE
    }

@app.get("/health")
async def health_check():
    """헬스 체크"""
    return {
        "status": "healthy",
        "device": DEVICE,
        "timestamp": str(Path(__file__).stat().st_mtime)
    }

@app.get("/api/system/info", response_model=SystemInfoResponse)
async def get_system_info():
    """시스템 정보 조회"""
    info = {
        "device": DEVICE,
        "status": "ready"
    }
    
    if DEVICE == "cuda":
        info["gpu_name"] = torch.cuda.get_device_name(0)
        info["vram_gb"] = round(torch.cuda.get_device_properties(0).total_memory / 1024**3, 1)
    
    # 모델 다운로드 확인
    sd_path = MODELS_DIR / "stable-diffusion-xl-base-1.0"
    tts_path = MODELS_DIR / "xtts-v2"
    info["models_downloaded"] = sd_path.exists() or tts_path.exists()
    
    return info

@app.get("/api/characters")
async def get_characters():
    """39개 캐릭터 목록"""
    characters = {
        "business": [
            {"id": "executive-fox", "name": "🦊 이그제큐티브 폭스", "category": "business"},
            {"id": "ceo-lion", "name": "🦁 CEO 라이온", "category": "business"},
            {"id": "strategist-eagle", "name": "🦅 전략가 이글", "category": "business"},
            {"id": "negotiator-wolf", "name": "🐺 협상가 울프", "category": "business"},
            {"id": "consultant-owl", "name": "🦉 컨설턴트 아울", "category": "business"},
        ],
        "tech": [
            {"id": "tech-fox", "name": "🦊 테크 폭스", "category": "tech"},
            {"id": "dev-raccoon", "name": "🦝 개발자 라쿤", "category": "tech"},
            {"id": "ai-panda", "name": "🐼 AI 판다", "category": "tech"},
            {"id": "startup-tiger", "name": "🐯 스타트업 타이거", "category": "tech"},
            {"id": "blockchain-monkey", "name": "🐵 블록체인 몽키", "category": "tech"},
        ],
        "fashion": [
            {"id": "fashionista-cat", "name": "😺 패셔니스타 캣", "category": "fashion"},
            {"id": "stylist-peacock", "name": "🦚 스타일리스트 피콕", "category": "fashion"},
            {"id": "luxury-leopard", "name": "🐆 럭셔리 레오파드", "category": "fashion"},
            {"id": "trendy-rabbit", "name": "🐰 트렌디 래빗", "category": "fashion"},
            {"id": "designer-swan", "name": "🦢 디자이너 스완", "category": "fashion"},
        ],
        "sports": [
            {"id": "athlete-cheetah", "name": "🐆 애슬리트 치타", "category": "sports"},
            {"id": "trainer-bear", "name": "🐻 트레이너 베어", "category": "sports"},
            {"id": "yoga-deer", "name": "🦌 요가 디어", "category": "sports"},
            {"id": "runner-kangaroo", "name": "🦘 러너 캥거루", "category": "sports"},
            {"id": "fighter-dragon", "name": "🐉 파이터 드래곤", "category": "sports"},
        ],
        "food": [
            {"id": "chef-penguin", "name": "🐧 셰프 펭귄", "category": "food"},
            {"id": "foodie-hamster", "name": "🐹 푸디 햄스터", "category": "food"},
            {"id": "barista-otter", "name": "🦦 바리스타 오터", "category": "food"},
            {"id": "sommelier-fox", "name": "🦊 소믈리에 폭스", "category": "food"},
            {"id": "baker-bear", "name": "🐻 베이커 베어", "category": "food"},
        ],
        "entertainment": [
            {"id": "comedian-parrot", "name": "🦜 코미디언 패럿", "category": "entertainment"},
            {"id": "musician-fox", "name": "🦊 뮤지션 폭스", "category": "entertainment"},
            {"id": "dancer-peacock", "name": "🦚 댄서 피콕", "category": "entertainment"},
            {"id": "artist-cat", "name": "😺 아티스트 캣", "category": "entertainment"},
            {"id": "gamer-otter", "name": "🦦 게이머 오터", "category": "entertainment"},
        ]
    }
    
    return {
        "total": sum(len(chars) for chars in characters.values()),
        "categories": characters
    }

@app.post("/api/shorts/generate")
async def generate_shorts(
    request: ShortsGenerationRequest,
    background_tasks: BackgroundTasks
):
    """쇼츠 생성 시작 (완전 자동화)"""
    try:
        # Job ID 생성
        import time
        job_id = f"shorts_{int(time.time())}_{uuid.uuid4().hex[:6]}"
        
        logger.info(f"🎬 New shorts generation job: {job_id}")
        logger.info(f"   Character: {request.character_id}")
        logger.info(f"   URL: {request.url}")
        
        # 요청 데이터 준비
        request_data = {
            "character_id": request.character_id,
            "num_scenes": 5,
            "duration": request.duration,
            "aspect_ratio": request.aspect_ratio
        }
        
        # URL이 있으면 크롤링
        if request.url:
            request_data["url"] = request.url
        elif request.text_content:
            # 텍스트 직접 제공
            request_data["product_info"] = {
                "title": "제품",
                "description": request.text_content,
                "features": [],
                "price": ""
            }
        else:
            # 기본 정보
            request_data["product_info"] = {
                "title": "프리미엄 제품",
                "description": "최고의 품질",
                "features": ["고품질", "합리적인 가격"],
                "price": "99,000원"
            }
        
        # 백그라운드 작업으로 쇼츠 생성
        background_tasks.add_task(
            _generate_shorts_background,
            job_id,
            request_data
        )
        
        return {
            "job_id": job_id,
            "status": "pending",
            "message": "쇼츠 생성이 시작되었습니다. 5-10분 소요됩니다.",
            "estimated_time": "5-10분"
        }
        
    except Exception as e:
        logger.error(f"❌ Error starting shorts generation: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


async def _generate_shorts_background(job_id: str, request_data: dict):
    """백그라운드에서 쇼츠 생성"""
    try:
        result = await pipeline.generate_shorts(job_id, request_data)
        logger.info(f"✅ Background generation completed: {job_id}")
    except Exception as e:
        logger.error(f"❌ Background generation failed: {str(e)}")

@app.get("/api/shorts/status/{job_id}", response_model=JobStatusResponse)
async def get_job_status(job_id: str):
    """작업 상태 조회"""
    job_status = pipeline.get_job_status(job_id)
    
    if not job_status:
        raise HTTPException(status_code=404, detail="Job not found")
    
    return {
        "job_id": job_id,
        "status": job_status["status"],
        "progress": job_status["progress"],
        "message": job_status["message"],
        "output_path": job_status.get("output_path"),
        "error": job_status.get("error")
    }

@app.get("/api/shorts/download/{job_id}")
async def download_shorts(job_id: str):
    """완성된 쇼츠 다운로드"""
    video_path = VIDEOS_DIR / f"{job_id}.mp4"
    
    if not video_path.exists():
        raise HTTPException(status_code=404, detail="Video not found")
    
    return FileResponse(
        video_path,
        media_type="video/mp4",
        filename=f"{job_id}.mp4"
    )

@app.post("/api/models/install")
async def install_models(background_tasks: BackgroundTasks):
    """AI 모델 다운로드 및 설치"""
    try:
        # 백그라운드로 모델 다운로드
        # background_tasks.add_task(download_models)
        
        return {
            "status": "started",
            "message": "모델 다운로드가 시작되었습니다. 수 분이 소요될 수 있습니다.",
            "models": [
                "Stable Diffusion XL",
                "AnimateDiff",
                "Coqui TTS XTTS-v2",
                "LLaMA 3.1 8B (Ollama 별도)"
            ]
        }
    except Exception as e:
        logger.error(f"❌ Error installing models: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/models/status")
async def get_models_status():
    """모델 다운로드 상태"""
    models_status = {}
    
    # Stable Diffusion 확인
    sd_path = MODELS_DIR / "stable-diffusion-xl-base-1.0"
    models_status["stable_diffusion"] = {
        "name": "Stable Diffusion XL",
        "downloaded": sd_path.exists(),
        "size_gb": 6.9
    }
    
    # AnimateDiff 확인
    ad_path = MODELS_DIR / "animatediff"
    models_status["animatediff"] = {
        "name": "AnimateDiff",
        "downloaded": ad_path.exists(),
        "size_gb": 1.7
    }
    
    # TTS 확인
    tts_path = MODELS_DIR / "xtts-v2"
    models_status["tts"] = {
        "name": "Coqui TTS XTTS-v2",
        "downloaded": tts_path.exists(),
        "size_gb": 2.0
    }
    
    return {
        "models": models_status,
        "total_size_gb": sum(m["size_gb"] for m in models_status.values()),
        "all_downloaded": all(m["downloaded"] for m in models_status.values())
    }

# ========== 메인 실행 ==========
if __name__ == "__main__":
    logger.info("🚀 Starting Local AI Shorts Generator Backend")
    logger.info(f"📁 Output directory: {OUTPUT_DIR}")
    logger.info(f"🤖 Models directory: {MODELS_DIR}")
    
    uvicorn.run(
        app,
        host="0.0.0.0",
        port=8000,
        log_level="info"
    )
