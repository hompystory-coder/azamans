#!/usr/bin/env python3
"""
🎨 Stable Diffusion 이미지 생성 모델
로컬 GPU에서 Pixar 스타일 캐릭터 이미지 생성
"""

import torch
from diffusers import StableDiffusionXLPipeline, DPMSolverMultistepScheduler
from pathlib import Path
from typing import Optional, List
from loguru import logger
import time

class ImageGenerator:
    """Stable Diffusion 기반 이미지 생성기"""
    
    def __init__(self, models_dir: Path, device: str = "cuda"):
        self.models_dir = models_dir
        self.device = device
        self.pipe = None
        self.loaded = False
        
    def load_model(self, model_id: str = "stabilityai/stable-diffusion-xl-base-1.0"):
        """모델 로드"""
        try:
            logger.info(f"🎨 Loading Stable Diffusion XL from {model_id}")
            start_time = time.time()
            
            # 파이프라인 로드
            self.pipe = StableDiffusionXLPipeline.from_pretrained(
                model_id,
                torch_dtype=torch.float16 if self.device == "cuda" else torch.float32,
                variant="fp16" if self.device == "cuda" else None,
                use_safetensors=True,
                cache_dir=self.models_dir
            )
            
            # GPU로 이동
            if self.device == "cuda":
                self.pipe = self.pipe.to(self.device)
                
                # 메모리 최적화
                self.pipe.enable_attention_slicing()
                self.pipe.enable_vae_slicing()
                
                # xformers 사용 (설치되어 있다면)
                try:
                    self.pipe.enable_xformers_memory_efficient_attention()
                    logger.info("✅ xformers enabled for better performance")
                except:
                    logger.warning("⚠️ xformers not available, using default attention")
            
            # 스케줄러 최적화 (속도 향상)
            self.pipe.scheduler = DPMSolverMultistepScheduler.from_config(
                self.pipe.scheduler.config
            )
            
            load_time = time.time() - start_time
            logger.info(f"✅ Model loaded in {load_time:.1f}s")
            self.loaded = True
            
        except Exception as e:
            logger.error(f"❌ Failed to load model: {str(e)}")
            raise
    
    def generate_character(
        self,
        character_id: str,
        prompt: str,
        negative_prompt: Optional[str] = None,
        num_images: int = 1,
        width: int = 1024,
        height: int = 1024,
        num_inference_steps: int = 30,
        guidance_scale: float = 7.5,
        seed: Optional[int] = None
    ) -> List[str]:
        """캐릭터 이미지 생성
        
        Args:
            character_id: 캐릭터 ID
            prompt: 생성 프롬프트
            negative_prompt: 네거티브 프롬프트
            num_images: 생성할 이미지 수
            width: 이미지 너비
            height: 이미지 높이
            num_inference_steps: 추론 스텝 수 (높을수록 품질 ↑, 시간 ↑)
            guidance_scale: 가이던스 스케일 (높을수록 프롬프트 따름)
            seed: 랜덤 시드 (재현성)
            
        Returns:
            생성된 이미지 경로 리스트
        """
        if not self.loaded:
            raise RuntimeError("Model not loaded. Call load_model() first.")
        
        try:
            logger.info(f"🎨 Generating {num_images} image(s) for {character_id}")
            logger.info(f"   Prompt: {prompt[:100]}...")
            
            start_time = time.time()
            
            # 시드 설정
            generator = None
            if seed is not None:
                generator = torch.Generator(device=self.device).manual_seed(seed)
            
            # 기본 네거티브 프롬프트
            if negative_prompt is None:
                negative_prompt = (
                    "ugly, blurry, low quality, distorted, deformed, "
                    "bad anatomy, extra limbs, poorly drawn, "
                    "text, watermark, signature"
                )
            
            # 이미지 생성
            output = self.pipe(
                prompt=prompt,
                negative_prompt=negative_prompt,
                num_images_per_prompt=num_images,
                width=width,
                height=height,
                num_inference_steps=num_inference_steps,
                guidance_scale=guidance_scale,
                generator=generator
            )
            
            images = output.images
            
            # 이미지 저장
            output_dir = self.models_dir.parent / "output" / "images" / character_id
            output_dir.mkdir(parents=True, exist_ok=True)
            
            saved_paths = []
            timestamp = int(time.time())
            
            for idx, image in enumerate(images):
                filename = f"{character_id}_{timestamp}_{idx}.png"
                filepath = output_dir / filename
                image.save(filepath)
                saved_paths.append(str(filepath))
                logger.info(f"   💾 Saved: {filepath.name}")
            
            gen_time = time.time() - start_time
            logger.info(f"✅ Generated {num_images} image(s) in {gen_time:.1f}s ({gen_time/num_images:.1f}s per image)")
            
            return saved_paths
            
        except Exception as e:
            logger.error(f"❌ Image generation failed: {str(e)}")
            raise
    
    def unload_model(self):
        """모델 언로드 (메모리 해제)"""
        if self.pipe is not None:
            del self.pipe
            self.pipe = None
            
            if self.device == "cuda":
                torch.cuda.empty_cache()
            
            logger.info("🗑️ Model unloaded, memory freed")
            self.loaded = False


# ========== 캐릭터 프롬프트 설정 ==========

CHARACTER_PROMPTS = {
    "executive-fox": (
        "Premium 3D rendered sophisticated fox in elegant business suit, "
        "gold-rimmed glasses, confident posture, studio lighting, "
        "Pixar-quality animation, 8K ultra detailed, professional business "
        "environment with luxury office background, sophisticated professional "
        "gestures with confident eye contact"
    ),
    "ceo-lion": (
        "Premium 3D rendered distinguished lion with magnificent golden mane, "
        "wearing luxury suit and tie, executive presence, cinematic studio lighting, "
        "Pixar-quality animation, 8K ultra detailed, prestigious office with city view, "
        "powerful confident movements with commanding presence"
    ),
    "tech-fox": (
        "Premium 3D rendered tech-savvy fox wearing AR smart glasses, "
        "modern tech hoodie, holding holographic tablet, futuristic lighting, "
        "Pixar-quality animation, 8K ultra detailed, high-tech laboratory with glowing screens, "
        "precise tech gestures with innovative hand movements"
    ),
    "fashionista-cat": (
        "Premium 3D rendered elegant Persian cat with silky white fur, "
        "wearing designer accessories, refined movements, cinematic lighting, "
        "Pixar-style animation, 8K resolution, luxury boutique background, "
        "graceful fluid movements with aristocratic poise"
    ),
    "athlete-cheetah": (
        "Premium 3D rendered athletic cheetah in sports gear, "
        "dynamic pose, energy trails, dramatic lighting, "
        "Pixar-quality animation, 8K ultra detailed, stadium background, "
        "powerful athletic movements with speed effects"
    ),
    "chef-penguin": (
        "Premium 3D rendered professional penguin chef with white chef hat, "
        "elegant cooking pose, warm kitchen lighting, "
        "Pixar-style rendering, 8K resolution, Michelin restaurant background, "
        "graceful cooking gestures with culinary expertise"
    ),
    "comedian-parrot": (
        "Premium 3D rendered colorful parrot comedian with microphone, "
        "expressive face, stage lighting, "
        "Pixar-quality animation, 8K ultra detailed, comedy club background, "
        "energetic entertaining movements with humorous expressions"
    ),
}

def get_character_prompt(character_id: str) -> str:
    """캐릭터 ID로 프롬프트 가져오기"""
    return CHARACTER_PROMPTS.get(
        character_id,
        CHARACTER_PROMPTS["executive-fox"]  # 기본값
    )


# ========== 테스트 코드 ==========
if __name__ == "__main__":
    # 테스트
    models_dir = Path(__file__).parent.parent.parent / "models"
    device = "cuda" if torch.cuda.is_available() else "cpu"
    
    logger.info(f"🔧 Device: {device}")
    
    generator = ImageGenerator(models_dir, device)
    
    # 모델 로드 (최초 1회, 자동 다운로드)
    # generator.load_model()
    
    # 이미지 생성 테스트
    # prompt = get_character_prompt("executive-fox")
    # images = generator.generate_character(
    #     character_id="executive-fox",
    #     prompt=prompt,
    #     num_images=1,
    #     num_inference_steps=30
    # )
    # print(f"Generated: {images}")
