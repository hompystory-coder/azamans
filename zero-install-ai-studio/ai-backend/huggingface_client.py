"""
Hugging Face Inference API Client
완전 무료 (무제한), 계정만 필요
속도: 느림 (대기열 방식), 개발/테스트용 최적
"""

import os
import requests
import logging
import time
from typing import Optional

logger = logging.getLogger(__name__)


class HuggingFaceClient:
    """Hugging Face Inference API 클라이언트"""
    
    # 추천 모델들
    MODELS = {
        "sdxl": "stabilityai/stable-diffusion-xl-base-1.0",
        "sd15": "runwayml/stable-diffusion-v1-5",
        "sd21": "stabilityai/stable-diffusion-2-1",
    }
    
    def __init__(self, api_token: Optional[str] = None):
        """
        초기화
        
        Args:
            api_token: Hugging Face API 토큰 (없으면 환경변수 HF_TOKEN 사용)
                      https://huggingface.co/settings/tokens 에서 발급
        """
        self.api_token = api_token or os.getenv("HF_TOKEN") or os.getenv("HUGGINGFACE_TOKEN")
        
        if not self.api_token:
            logger.warning("⚠️ HF_TOKEN 미설정 - Hugging Face API 사용 불가")
            logger.info("💡 https://huggingface.co/settings/tokens 에서 토큰 발급 후 설정")
            self.enabled = False
        else:
            self.enabled = True
            logger.info("✅ Hugging Face API 활성화 (완전 무료)")
    
    def generate_image(
        self, 
        prompt: str,
        model: str = "sdxl",
        max_wait_time: int = 300,
        check_interval: int = 5
    ) -> Optional[bytes]:
        """
        이미지 생성 (대기열 방식)
        
        Args:
            prompt: 영어 프롬프트
            model: 'sdxl', 'sd15', 'sd21'
            max_wait_time: 최대 대기 시간 (초, 기본 300초=5분)
            check_interval: 상태 확인 간격 (초, 기본 5초)
            
        Returns:
            생성된 이미지 바이트 (실패 시 None)
        """
        if not self.enabled:
            logger.error("❌ Hugging Face API 미활성화")
            return None
        
        model_id = self.MODELS.get(model, self.MODELS["sdxl"])
        api_url = f"https://api-inference.huggingface.co/models/{model_id}"
        
        headers = {"Authorization": f"Bearer {self.api_token}"}
        payload = {"inputs": prompt}
        
        try:
            logger.info(f"🎨 HuggingFace 이미지 생성 중: {prompt[:50]}...")
            logger.info(f"⏳ 대기열 방식 - 최대 {max_wait_time}초 대기")
            
            start_time = time.time()
            
            while True:
                response = requests.post(api_url, headers=headers, json=payload, timeout=30)
                
                # 성공
                if response.status_code == 200:
                    elapsed = time.time() - start_time
                    logger.info(f"✅ 이미지 생성 완료 ({elapsed:.1f}초 소요)")
                    return response.content
                
                # 모델 로딩 중 (대기열)
                elif response.status_code == 503:
                    elapsed = time.time() - start_time
                    
                    if elapsed > max_wait_time:
                        logger.error(f"❌ 시간 초과 ({max_wait_time}초)")
                        return None
                    
                    try:
                        error_data = response.json()
                        estimated_time = error_data.get("estimated_time", "알 수 없음")
                        logger.info(f"⏳ 모델 로딩 중... 예상 대기 시간: {estimated_time}초")
                    except:
                        logger.info(f"⏳ 대기 중... ({elapsed:.0f}/{max_wait_time}초)")
                    
                    time.sleep(check_interval)
                    continue
                
                # 기타 오류
                else:
                    logger.error(f"❌ 오류: {response.status_code} - {response.text}")
                    return None
                    
        except Exception as e:
            logger.error(f"❌ 이미지 생성 실패: {e}")
            return None
    
    def save_image(self, image_bytes: bytes, save_path: str) -> bool:
        """
        이미지 바이트를 파일로 저장
        
        Args:
            image_bytes: 이미지 바이트 데이터
            save_path: 저장할 로컬 경로
            
        Returns:
            성공 여부
        """
        try:
            with open(save_path, 'wb') as f:
                f.write(image_bytes)
            
            logger.info(f"✅ 이미지 저장 완료: {save_path}")
            return True
            
        except Exception as e:
            logger.error(f"❌ 이미지 저장 실패: {e}")
            return False
    
    def generate_and_save(
        self, 
        prompt: str, 
        save_path: str,
        model: str = "sdxl"
    ) -> bool:
        """
        이미지 생성하고 바로 저장
        
        Args:
            prompt: 영어 프롬프트
            save_path: 저장 경로
            model: 모델 선택
            
        Returns:
            성공 여부
        """
        image_bytes = self.generate_image(prompt, model)
        
        if image_bytes:
            return self.save_image(image_bytes, save_path)
        
        return False
    
    def generate_batch(
        self, 
        prompts: list[str],
        model: str = "sdxl",
        max_wait_per_image: int = 300
    ) -> list[Optional[bytes]]:
        """
        여러 이미지를 순차적으로 생성
        
        Args:
            prompts: 프롬프트 리스트
            model: 모델 선택
            max_wait_per_image: 이미지당 최대 대기 시간
            
        Returns:
            생성된 이미지 바이트 리스트
        """
        results = []
        
        for i, prompt in enumerate(prompts, 1):
            logger.info(f"📸 배치 생성 {i}/{len(prompts)}")
            
            image_bytes = self.generate_image(
                prompt, 
                model=model,
                max_wait_time=max_wait_per_image
            )
            
            results.append(image_bytes)
            
            # API 부하 방지를 위한 짧은 대기
            if i < len(prompts):
                time.sleep(2)
        
        success_count = sum(1 for img in results if img is not None)
        logger.info(f"✅ 배치 생성 완료: {success_count}/{len(prompts)} 성공")
        
        return results


# 사용 예시
if __name__ == "__main__":
    # 로깅 설정
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
    )
    
    # 클라이언트 초기화
    client = HuggingFaceClient()
    
    if client.enabled:
        # 테스트 1: 단일 이미지 생성
        print("\n=== 테스트 1: SDXL 이미지 생성 ===")
        image_bytes = client.generate_image(
            prompt="a beautiful sunset over the ocean, cinematic lighting",
            model="sdxl"
        )
        
        if image_bytes:
            client.save_image(image_bytes, "/tmp/hf_test_sunset.png")
            print(f"✅ 이미지 저장: /tmp/hf_test_sunset.png")
        
        # 테스트 2: 배치 생성
        print("\n=== 테스트 2: 배치 이미지 생성 ===")
        prompts = [
            "a mountain landscape at sunrise",
            "a cozy coffee shop interior"
        ]
        
        batch_results = client.generate_batch(prompts, model="sdxl")
        
        for i, img_bytes in enumerate(batch_results, 1):
            if img_bytes:
                path = f"/tmp/hf_batch_{i}.png"
                client.save_image(img_bytes, path)
                print(f"✅ 이미지 {i} 저장: {path}")
    else:
        print("❌ HF_TOKEN 환경변수를 설정해주세요!")
        print("1. https://huggingface.co/settings/tokens 에서 토큰 발급")
        print("2. export HF_TOKEN='your_token_here'")
