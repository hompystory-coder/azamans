#!/usr/bin/env python3
"""
🎬 비디오 생성 모듈
AnimateDiff + Stable Diffusion을 활용한 Image-to-Video 변환
"""

import torch
from diffusers import (
    AnimateDiffPipeline,
    MotionAdapter,
    DDIMScheduler,
    StableDiffusionPipeline
)
from diffusers.utils import export_to_video
from pathlib import Path
from typing import Optional, List, Union
from loguru import logger
import time
from PIL import Image
import numpy as np

class VideoGenerator:
    """AnimateDiff 기반 비디오 생성기"""
    
    def __init__(self, models_dir: Path, device: str = "cuda"):
        self.models_dir = models_dir
        self.device = device
        self.pipe = None
        self.motion_adapter = None
        self.loaded = False
        
    def load_model(
        self,
        base_model: str = "runwayml/stable-diffusion-v1-5",
        motion_adapter_id: str = "guoyww/animatediff-motion-adapter-v1-5-2"
    ):
        """AnimateDiff 모델 로드
        
        Args:
            base_model: Stable Diffusion 베이스 모델
            motion_adapter_id: AnimateDiff 모션 어댑터
        """
        try:
            logger.info(f"🎬 Loading AnimateDiff...")
            logger.info(f"   Base model: {base_model}")
            logger.info(f"   Motion adapter: {motion_adapter_id}")
            start_time = time.time()
            
            # Motion Adapter 로드
            logger.info("   Loading motion adapter...")
            self.motion_adapter = MotionAdapter.from_pretrained(
                motion_adapter_id,
                torch_dtype=torch.float16 if self.device == "cuda" else torch.float32,
                cache_dir=self.models_dir
            )
            
            # AnimateDiff 파이프라인 로드
            logger.info("   Loading AnimateDiff pipeline...")
            self.pipe = AnimateDiffPipeline.from_pretrained(
                base_model,
                motion_adapter=self.motion_adapter,
                torch_dtype=torch.float16 if self.device == "cuda" else torch.float32,
                cache_dir=self.models_dir
            )
            
            # 스케줄러 설정
            self.pipe.scheduler = DDIMScheduler.from_config(
                self.pipe.scheduler.config,
                beta_schedule="linear",
                clip_sample=False
            )
            
            # GPU로 이동
            if self.device == "cuda":
                self.pipe = self.pipe.to(self.device)
                
                # 메모리 최적화
                self.pipe.enable_vae_slicing()
                self.pipe.enable_model_cpu_offload()  # CPU 오프로드 (VRAM 절약)
                
                try:
                    self.pipe.enable_xformers_memory_efficient_attention()
                    logger.info("   ✅ xformers enabled")
                except:
                    logger.warning("   ⚠️ xformers not available")
            
            load_time = time.time() - start_time
            logger.info(f"✅ AnimateDiff loaded in {load_time:.1f}s")
            self.loaded = True
            
        except Exception as e:
            logger.error(f"❌ Failed to load AnimateDiff: {str(e)}")
            raise
    
    def text_to_video(
        self,
        prompt: str,
        negative_prompt: Optional[str] = None,
        num_frames: int = 16,
        fps: int = 8,
        width: int = 512,
        height: int = 512,
        num_inference_steps: int = 25,
        guidance_scale: float = 7.5,
        seed: Optional[int] = None,
        output_path: Optional[Path] = None
    ) -> str:
        """텍스트로부터 비디오 생성
        
        Args:
            prompt: 생성 프롬프트
            negative_prompt: 네거티브 프롬프트
            num_frames: 프레임 수 (16-24 권장)
            fps: 초당 프레임 (8-12 권장)
            width: 비디오 너비
            height: 비디오 높이
            num_inference_steps: 추론 스텝
            guidance_scale: 가이던스 스케일
            seed: 랜덤 시드
            output_path: 출력 파일 경로
            
        Returns:
            생성된 비디오 파일 경로
        """
        if not self.loaded:
            raise RuntimeError("Model not loaded. Call load_model() first.")
        
        try:
            logger.info(f"🎬 Generating video from text")
            logger.info(f"   Prompt: {prompt[:100]}...")
            logger.info(f"   Frames: {num_frames}, FPS: {fps}, Size: {width}x{height}")
            
            start_time = time.time()
            
            # 시드 설정
            generator = None
            if seed is not None:
                generator = torch.Generator(device=self.device).manual_seed(seed)
            
            # 네거티브 프롬프트
            if negative_prompt is None:
                negative_prompt = (
                    "bad quality, low quality, blurry, distorted, "
                    "deformed, ugly, text, watermark"
                )
            
            # 비디오 생성
            output = self.pipe(
                prompt=prompt,
                negative_prompt=negative_prompt,
                num_frames=num_frames,
                width=width,
                height=height,
                num_inference_steps=num_inference_steps,
                guidance_scale=guidance_scale,
                generator=generator
            )
            
            frames = output.frames[0]  # List of PIL Images
            
            # 출력 경로 설정
            if output_path is None:
                output_dir = self.models_dir.parent / "output" / "videos"
                output_dir.mkdir(parents=True, exist_ok=True)
                timestamp = int(time.time())
                output_path = output_dir / f"video_{timestamp}.mp4"
            
            # 비디오 저장
            export_to_video(frames, str(output_path), fps=fps)
            
            gen_time = time.time() - start_time
            duration = num_frames / fps
            
            logger.info(f"✅ Video generated in {gen_time:.1f}s")
            logger.info(f"   Duration: {duration:.1f}s")
            logger.info(f"   Saved: {output_path.name}")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ Video generation failed: {str(e)}")
            raise
    
    def image_to_video(
        self,
        image: Union[str, Path, Image.Image],
        prompt: str,
        negative_prompt: Optional[str] = None,
        num_frames: int = 16,
        fps: int = 8,
        num_inference_steps: int = 25,
        guidance_scale: float = 7.5,
        seed: Optional[int] = None,
        output_path: Optional[Path] = None
    ) -> str:
        """이미지로부터 비디오 생성 (Image-to-Video)
        
        Args:
            image: 입력 이미지 (경로 또는 PIL Image)
            prompt: 모션 프롬프트
            negative_prompt: 네거티브 프롬프트
            num_frames: 프레임 수
            fps: 초당 프레임
            num_inference_steps: 추론 스텝
            guidance_scale: 가이던스 스케일
            seed: 랜덤 시드
            output_path: 출력 경로
            
        Returns:
            생성된 비디오 파일 경로
        """
        if not self.loaded:
            raise RuntimeError("Model not loaded.")
        
        try:
            # 이미지 로드
            if isinstance(image, (str, Path)):
                image = Image.open(image).convert("RGB")
            
            logger.info(f"🎬 Generating video from image")
            logger.info(f"   Image size: {image.size}")
            logger.info(f"   Prompt: {prompt[:100]}...")
            
            # Image-to-Video는 IP-Adapter나 ControlNet 필요
            # 여기서는 간단히 text-to-video로 구현
            # 실제로는 img2img 파이프라인이나 IP-Adapter 사용 권장
            
            # 이미지 크기에 맞춤
            width, height = image.size
            
            # 비디오 생성 (이미지 정보를 프롬프트에 통합)
            enhanced_prompt = f"{prompt}, based on the reference image style"
            
            return self.text_to_video(
                prompt=enhanced_prompt,
                negative_prompt=negative_prompt,
                num_frames=num_frames,
                fps=fps,
                width=width,
                height=height,
                num_inference_steps=num_inference_steps,
                guidance_scale=guidance_scale,
                seed=seed,
                output_path=output_path
            )
            
        except Exception as e:
            logger.error(f"❌ Image-to-video failed: {str(e)}")
            raise
    
    def generate_character_video(
        self,
        character_id: str,
        character_prompt: str,
        action_prompt: str,
        duration_seconds: float = 2.0,
        aspect_ratio: str = "9:16",
        output_path: Optional[Path] = None
    ) -> str:
        """캐릭터 비디오 생성 (쇼츠용)
        
        Args:
            character_id: 캐릭터 ID
            character_prompt: 캐릭터 외형 프롬프트
            action_prompt: 액션/모션 프롬프트
            duration_seconds: 비디오 길이 (초)
            aspect_ratio: 화면 비율 (9:16 세로, 16:9 가로)
            output_path: 출력 경로
            
        Returns:
            생성된 비디오 파일 경로
        """
        try:
            # 화면 비율 계산
            if aspect_ratio == "9:16":
                width, height = 512, 896  # 세로 (쇼츠)
            elif aspect_ratio == "16:9":
                width, height = 896, 512  # 가로
            else:
                width, height = 512, 512  # 정사각형
            
            # FPS 및 프레임 수 계산
            fps = 8
            num_frames = int(duration_seconds * fps)
            num_frames = min(max(num_frames, 8), 24)  # 8-24 프레임 제한
            
            # 프롬프트 결합
            full_prompt = f"{character_prompt}, {action_prompt}, smooth animation, fluid motion"
            
            logger.info(f"🎬 Generating character video: {character_id}")
            logger.info(f"   Duration: {duration_seconds}s")
            logger.info(f"   Aspect ratio: {aspect_ratio} ({width}x{height})")
            logger.info(f"   Frames: {num_frames} @ {fps} FPS")
            
            # 비디오 생성
            return self.text_to_video(
                prompt=full_prompt,
                num_frames=num_frames,
                fps=fps,
                width=width,
                height=height,
                output_path=output_path
            )
            
        except Exception as e:
            logger.error(f"❌ Character video generation failed: {str(e)}")
            raise
    
    def unload_model(self):
        """모델 언로드"""
        if self.pipe is not None:
            del self.pipe
            self.pipe = None
        
        if self.motion_adapter is not None:
            del self.motion_adapter
            self.motion_adapter = None
        
        if self.device == "cuda":
            torch.cuda.empty_cache()
        
        logger.info("🗑️ Video generator unloaded")
        self.loaded = False


# ========== 캐릭터 액션 프롬프트 ==========

CHARACTER_ACTIONS = {
    "executive-fox": [
        "confidently presenting with professional gestures",
        "analyzing documents with focused expression",
        "making decisive hand movements while explaining",
        "nodding approvingly with executive presence"
    ],
    "tech-fox": [
        "interacting with holographic displays",
        "typing on futuristic keyboard",
        "demonstrating tech gadgets enthusiastically",
        "explaining with tech hand gestures"
    ],
    "comedian-parrot": [
        "telling jokes with expressive movements",
        "laughing and gesturing humorously",
        "dancing with energetic motions",
        "making funny faces and poses"
    ],
}

def get_character_action(character_id: str, action_type: str = "default") -> str:
    """캐릭터별 액션 프롬프트 가져오기"""
    actions = CHARACTER_ACTIONS.get(character_id, CHARACTER_ACTIONS["executive-fox"])
    return actions[0] if action_type == "default" else actions[0]


# ========== 테스트 코드 ==========
if __name__ == "__main__":
    # 테스트
    models_dir = Path(__file__).parent.parent.parent / "models"
    device = "cuda" if torch.cuda.is_available() else "cpu"
    
    logger.info(f"🔧 Device: {device}")
    
    generator = VideoGenerator(models_dir, device)
    
    # 모델 로드 (최초 1회, 5-10분 소요)
    # generator.load_model()
    
    # 비디오 생성 테스트
    # prompt = "A cute fox character in business suit, professional animation, Pixar style"
    # video = generator.text_to_video(
    #     prompt=prompt,
    #     num_frames=16,
    #     fps=8
    # )
    # print(f"Generated: {video}")
