"""
Replicate API Client for Image and Video Generation
무료 크레딧: 초기 $5 (이미지 1,600장 or 영상 100개)
비용: 이미지 $0.003/장, 영상 $0.05/10초
"""

import os
import replicate
import requests
import logging
from typing import Optional, Dict, Any

logger = logging.getLogger(__name__)


class ReplicateClient:
    """Replicate API 클라이언트"""
    
    def __init__(self, api_token: Optional[str] = None):
        """
        초기화
        
        Args:
            api_token: Replicate API 토큰 (없으면 환경변수 REPLICATE_API_TOKEN 사용)
        """
        self.api_token = api_token or os.getenv("REPLICATE_API_TOKEN")
        
        if not self.api_token:
            logger.warning("⚠️ REPLICATE_API_TOKEN 미설정 - Replicate API 사용 불가")
            self.enabled = False
        else:
            os.environ["REPLICATE_API_TOKEN"] = self.api_token
            self.enabled = True
            logger.info("✅ Replicate API 활성화")
    
    def generate_image_sdxl(
        self, 
        prompt: str, 
        width: int = 1024, 
        height: int = 1024,
        num_outputs: int = 1,
        negative_prompt: str = "ugly, blurry, low quality"
    ) -> Optional[str]:
        """
        SDXL로 이미지 생성
        
        Args:
            prompt: 영어 프롬프트 (한글은 사전 번역 필요)
            width: 이미지 너비 (기본 1024)
            height: 이미지 높이 (기본 1024)
            num_outputs: 생성 이미지 수 (기본 1)
            negative_prompt: 네거티브 프롬프트
            
        Returns:
            생성된 이미지 URL (실패 시 None)
        """
        if not self.enabled:
            logger.error("❌ Replicate API 미활성화")
            return None
        
        try:
            logger.info(f"🎨 SDXL 이미지 생성 중: {prompt[:50]}...")
            
            output = replicate.run(
                "stability-ai/sdxl:39ed52f2a78e934b3ba6e2a89f5b1c712de7dfea535525255b1aa35c5565e08b",
                input={
                    "prompt": prompt,
                    "width": width,
                    "height": height,
                    "num_outputs": num_outputs,
                    "negative_prompt": negative_prompt,
                    "num_inference_steps": 25,
                    "guidance_scale": 7.5
                }
            )
            
            image_url = output[0] if isinstance(output, list) else output
            logger.info(f"✅ 이미지 생성 완료: {image_url}")
            
            return image_url
            
        except Exception as e:
            logger.error(f"❌ SDXL 이미지 생성 실패: {e}")
            return None
    
    def generate_image_flux(
        self, 
        prompt: str, 
        width: int = 1024, 
        height: int = 1024,
        num_outputs: int = 1
    ) -> Optional[str]:
        """
        FLUX.1 [schnell]로 고품질 이미지 생성 (SDXL보다 빠르고 품질 좋음)
        
        Args:
            prompt: 영어 프롬프트
            width: 이미지 너비
            height: 이미지 높이
            num_outputs: 생성 이미지 수
            
        Returns:
            생성된 이미지 URL (실패 시 None)
        """
        if not self.enabled:
            logger.error("❌ Replicate API 미활성화")
            return None
        
        try:
            logger.info(f"🎨 FLUX 이미지 생성 중: {prompt[:50]}...")
            
            output = replicate.run(
                "black-forest-labs/flux-schnell",
                input={
                    "prompt": prompt,
                    "width": width,
                    "height": height,
                    "num_outputs": num_outputs,
                    "output_format": "png",
                    "output_quality": 90
                }
            )
            
            image_url = output[0] if isinstance(output, list) else output
            logger.info(f"✅ FLUX 이미지 생성 완료: {image_url}")
            
            return image_url
            
        except Exception as e:
            logger.error(f"❌ FLUX 이미지 생성 실패: {e}")
            return None
    
    def generate_video_animatediff(
        self, 
        prompt: str,
        num_frames: int = 16,
        fps: int = 8,
        guidance_scale: float = 7.5
    ) -> Optional[str]:
        """
        AnimateDiff로 짧은 영상 생성 (2-4초)
        
        Args:
            prompt: 영어 프롬프트
            num_frames: 프레임 수 (8=1초, 16=2초, 24=3초)
            fps: 초당 프레임 (기본 8)
            guidance_scale: 프롬프트 가이던스 (7.5 권장)
            
        Returns:
            생성된 영상 URL (실패 시 None)
        """
        if not self.enabled:
            logger.error("❌ Replicate API 미활성화")
            return None
        
        try:
            logger.info(f"🎬 AnimateDiff 영상 생성 중: {prompt[:50]}...")
            
            output = replicate.run(
                "lucataco/animate-diff:beecf59c4aee8d81bf04f0381033dfa10dc16e845b4ae00d281e2fa377e48a9f",
                input={
                    "prompt": prompt,
                    "num_frames": num_frames,
                    "guidance_scale": guidance_scale,
                    "num_inference_steps": 25
                }
            )
            
            video_url = output if isinstance(output, str) else str(output)
            logger.info(f"✅ 영상 생성 완료: {video_url}")
            
            return video_url
            
        except Exception as e:
            logger.error(f"❌ AnimateDiff 영상 생성 실패: {e}")
            return None
    
    def download_file(self, url: str, save_path: str) -> bool:
        """
        생성된 이미지/영상을 로컬에 다운로드
        
        Args:
            url: Replicate에서 생성된 파일 URL
            save_path: 저장할 로컬 경로
            
        Returns:
            성공 여부
        """
        try:
            logger.info(f"📥 다운로드 중: {url} → {save_path}")
            
            response = requests.get(url, stream=True)
            response.raise_for_status()
            
            with open(save_path, 'wb') as f:
                for chunk in response.iter_content(chunk_size=8192):
                    f.write(chunk)
            
            logger.info(f"✅ 다운로드 완료: {save_path}")
            return True
            
        except Exception as e:
            logger.error(f"❌ 다운로드 실패: {e}")
            return False
    
    def generate_image_batch(
        self, 
        prompts: list[str],
        model: str = "sdxl",
        width: int = 1024,
        height: int = 1024
    ) -> list[Optional[str]]:
        """
        여러 이미지를 배치로 생성
        
        Args:
            prompts: 프롬프트 리스트
            model: 'sdxl' or 'flux'
            width: 이미지 너비
            height: 이미지 높이
            
        Returns:
            생성된 이미지 URL 리스트
        """
        results = []
        
        for i, prompt in enumerate(prompts, 1):
            logger.info(f"📸 배치 생성 {i}/{len(prompts)}")
            
            if model == "flux":
                url = self.generate_image_flux(prompt, width, height)
            else:
                url = self.generate_image_sdxl(prompt, width, height)
            
            results.append(url)
        
        success_count = sum(1 for url in results if url is not None)
        logger.info(f"✅ 배치 생성 완료: {success_count}/{len(prompts)} 성공")
        
        return results


# 사용 예시
if __name__ == "__main__":
    # 로깅 설정
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
    )
    
    # 클라이언트 초기화 (환경변수에서 자동으로 토큰 로드)
    client = ReplicateClient()
    
    if client.enabled:
        # 테스트 1: SDXL 이미지 생성
        print("\n=== 테스트 1: SDXL 이미지 생성 ===")
        image_url = client.generate_image_sdxl(
            prompt="a beautiful sunset over the ocean, cinematic lighting, 4k",
            width=1024,
            height=1024
        )
        print(f"생성된 이미지: {image_url}")
        
        # 테스트 2: FLUX 이미지 생성
        print("\n=== 테스트 2: FLUX 이미지 생성 ===")
        flux_url = client.generate_image_flux(
            prompt="a cute cat sitting on a chair, studio lighting",
            width=1024,
            height=1024
        )
        print(f"생성된 이미지: {flux_url}")
        
        # 테스트 3: 영상 생성
        print("\n=== 테스트 3: AnimateDiff 영상 생성 ===")
        video_url = client.generate_video_animatediff(
            prompt="a dog running in the park, smooth motion",
            num_frames=16  # 2초 영상
        )
        print(f"생성된 영상: {video_url}")
        
        # 테스트 4: 배치 생성
        print("\n=== 테스트 4: 배치 이미지 생성 ===")
        prompts = [
            "a mountain landscape at sunrise",
            "a futuristic city with flying cars",
            "a cozy coffee shop interior"
        ]
        batch_urls = client.generate_image_batch(prompts, model="sdxl")
        for i, url in enumerate(batch_urls, 1):
            print(f"이미지 {i}: {url}")
    else:
        print("❌ REPLICATE_API_TOKEN 환경변수를 설정해주세요!")
        print("예시: export REPLICATE_API_TOKEN='your_token_here'")
