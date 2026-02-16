"""
다국어 쇼츠 생성 시스템
Ollama를 사용하여 나레이션을 여러 언어로 번역
"""

import requests
import logging
from typing import Optional, List, Dict

logger = logging.getLogger(__name__)


class MultiLangTranslator:
    """Ollama 기반 다국어 번역기"""
    
    # 지원 언어
    LANGUAGES = {
        "ko": {"name": "한국어", "native": "한국어", "quality": "⭐⭐⭐⭐⭐"},
        "en": {"name": "영어", "native": "English", "quality": "⭐⭐⭐⭐⭐"},
        "ja": {"name": "일본어", "native": "日本語", "quality": "⭐⭐⭐⭐"},
        "zh": {"name": "중국어", "native": "中文", "quality": "⭐⭐⭐⭐"},
        "es": {"name": "스페인어", "native": "Español", "quality": "⭐⭐⭐"}
    }
    
    def __init__(self, base_url: str = "http://localhost:11434", model: str = "llama3.1:8b"):
        """
        초기화
        
        Args:
            base_url: Ollama 서버 URL
            model: 사용할 모델
        """
        self.base_url = base_url
        self.model = model
        self.enabled = self._check_availability()
        
        if self.enabled:
            logger.info(f"✅ 다국어 번역 시스템 활성화 (모델: {model})")
        else:
            logger.warning("⚠️ Ollama 서비스 미실행 - 번역 불가")
    
    def _check_availability(self) -> bool:
        """Ollama 서비스 상태 확인"""
        try:
            response = requests.get(f"{self.base_url}/api/tags", timeout=2)
            return response.status_code == 200
        except:
            return False
    
    def translate_narration(
        self, 
        korean_text: str, 
        target_lang: str = "en"
    ) -> Optional[str]:
        """
        나레이션을 목표 언어로 번역
        
        Args:
            korean_text: 한국어 나레이션
            target_lang: 목표 언어 코드 (en, ja, zh, es)
            
        Returns:
            번역된 텍스트 (실패 시 None)
        """
        if not self.enabled:
            logger.error("❌ Ollama 미실행 - 번역 불가")
            return None
        
        if target_lang == "ko":
            return korean_text  # 한국어는 번역 불필요
        
        if target_lang not in self.LANGUAGES:
            logger.error(f"❌ 지원하지 않는 언어: {target_lang}")
            return None
        
        lang_info = self.LANGUAGES[target_lang]
        lang_name = lang_info["native"]
        
        prompt = f"""다음 한국어 나레이션을 {lang_name}로 번역하세요.

원문: "{korean_text}"

요구사항:
1. 같은 톤과 느낌 유지
2. 원문 길이와 비슷하게 (30-40자)
3. 자연스러운 구어체
4. 번역문만 출력 (설명이나 따옴표 없이)

{lang_name} 번역:"""
        
        try:
            response = requests.post(
                f"{self.base_url}/api/generate",
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "stream": False,
                    "options": {
                        "temperature": 0.3,  # 낮은 temperature로 일관성 확보
                        "top_p": 0.9
                    }
                },
                timeout=30
            )
            
            if response.status_code == 200:
                result = response.json()
                translated = result.get("response", "").strip()
                
                # 따옴표 제거
                translated = translated.strip('"\'""''')
                
                logger.info(f"✅ 번역 완료: {korean_text[:20]}... → {translated[:20]}...")
                return translated
            else:
                logger.error(f"❌ 번역 실패: {response.status_code}")
                return None
                
        except Exception as e:
            logger.error(f"❌ 번역 오류: {e}")
            return None
    
    def translate_story_batch(
        self, 
        scenes: List[Dict], 
        target_lang: str = "en"
    ) -> List[Dict]:
        """
        전체 스토리의 나레이션을 일괄 번역
        
        Args:
            scenes: 씬 리스트 (각 씬에 "narration" 포함)
            target_lang: 목표 언어
            
        Returns:
            번역된 씬 리스트 (원본 + 번역 추가)
        """
        if target_lang == "ko":
            logger.info("한국어는 번역 불필요")
            return scenes
        
        logger.info(f"📦 배치 번역 시작: {len(scenes)}개 씬 → {target_lang}")
        
        translated_scenes = []
        success_count = 0
        
        for i, scene in enumerate(scenes, 1):
            korean_narration = scene.get("narration", "")
            
            if not korean_narration:
                logger.warning(f"⚠️  씬 {i}: 나레이션 없음")
                translated_scenes.append(scene)
                continue
            
            # 번역
            translated = self.translate_narration(korean_narration, target_lang)
            
            # 번역 결과 추가
            scene_copy = scene.copy()
            scene_copy["narration_original"] = korean_narration
            scene_copy["narration"] = translated if translated else korean_narration
            scene_copy["language"] = target_lang
            
            translated_scenes.append(scene_copy)
            
            if translated:
                success_count += 1
        
        logger.info(f"✅ 배치 번역 완료: {success_count}/{len(scenes)} 성공")
        
        return translated_scenes
    
    def get_supported_languages(self) -> List[Dict]:
        """지원 언어 목록 반환"""
        return [
            {
                "code": code,
                "name": info["name"],
                "native": info["native"],
                "quality": info["quality"]
            }
            for code, info in self.LANGUAGES.items()
        ]


# 사용 예시
if __name__ == "__main__":
    # 로깅 설정
    logging.basicConfig(
        level=logging.INFO,
        format='%(levelname)s - %(message)s'
    )
    
    # 번역기 초기화
    translator = MultiLangTranslator()
    
    if translator.enabled:
        # 테스트 1: 단일 번역
        print("\n=== 테스트 1: 단일 나레이션 번역 ===\n")
        
        korean_text = "여러분, 이건 정말 믿기 힘든 이야기인데 한번 들어보세요."
        
        for lang_code in ["en", "ja", "zh", "es"]:
            print(f"\n🌐 {translator.LANGUAGES[lang_code]['name']} 번역:")
            print(f"   원문: {korean_text}")
            
            translated = translator.translate_narration(korean_text, lang_code)
            
            if translated:
                print(f"   번역: {translated}")
            else:
                print(f"   ❌ 번역 실패")
        
        # 테스트 2: 배치 번역
        print("\n\n=== 테스트 2: 스토리 배치 번역 ===\n")
        
        test_scenes = [
            {
                "scene_number": 1,
                "narration": "오늘은 정말 특별한 날이 될 거예요."
            },
            {
                "scene_number": 2,
                "narration": "그런데 여기서 예상치 못한 일이 벌어졌죠."
            },
            {
                "scene_number": 3,
                "narration": "이제는 모든 게 달라졌어요."
            }
        ]
        
        print("📖 원본 스토리 (한국어):")
        for scene in test_scenes:
            print(f"   씬 {scene['scene_number']}: {scene['narration']}")
        
        # 영어로 번역
        print("\n🇺🇸 영어 번역:")
        translated_en = translator.translate_story_batch(test_scenes, "en")
        for scene in translated_en:
            print(f"   씬 {scene['scene_number']}: {scene['narration']}")
        
        # 지원 언어 목록
        print("\n\n=== 지원 언어 목록 ===\n")
        
        languages = translator.get_supported_languages()
        for lang in languages:
            print(f"🌐 {lang['code']}: {lang['name']} ({lang['native']}) - {lang['quality']}")
        
    else:
        print("❌ Ollama 서비스가 실행 중이지 않습니다!")
        print("실행: ollama serve")
