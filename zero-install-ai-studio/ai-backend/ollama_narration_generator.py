"""
Ollama 기반 나레이션 자동 생성 시스템
75개 고정 풀 → 무한 AI 자동 생성
"""

import requests
import logging
import json
from typing import Optional, List

logger = logging.getLogger(__name__)


class OllamaNarrationGenerator:
    """Ollama를 사용한 나레이션 자동 생성"""
    
    def __init__(self, base_url: str = "http://localhost:11434", model: str = "llama3.1:8b"):
        """
        초기화
        
        Args:
            base_url: Ollama 서버 URL
            model: 사용할 모델 (llama3.1:8b 권장)
        """
        self.base_url = base_url
        self.model = model
        self.enabled = self._check_availability()
        
        if self.enabled:
            logger.info(f"✅ Ollama 나레이션 생성기 활성화 (모델: {model})")
        else:
            logger.warning("⚠️ Ollama 서비스 미실행 - 고정 나레이션 풀 사용")
    
    def _check_availability(self) -> bool:
        """Ollama 서비스 상태 확인"""
        try:
            response = requests.get(f"{self.base_url}/api/tags", timeout=2)
            return response.status_code == 200
        except:
            return False
    
    def generate_narration(
        self,
        scene_number: int,
        act_name: str,
        korean_mood: str,
        scene_title: str,
        user_input: str,
        style: str = "curious"
    ) -> Optional[str]:
        """
        씬별 맞춤 나레이션 생성
        
        Args:
            scene_number: 씬 번호 (1~7)
            act_name: 막 이름 (발단/전개/위기/절정/결말)
            korean_mood: 분위기 (신비로운/긴장감 넘치는/충격적인 등)
            scene_title: 씬 제목
            user_input: 사용자 입력 스토리 주제
            style: 나레이션 스타일 ('curious', 'dramatic', 'calm')
            
        Returns:
            생성된 나레이션 (실패 시 None)
        """
        if not self.enabled:
            return None
        
        # 스타일별 프롬프트 템플릿
        style_prompts = {
            "curious": "궁금증을 유발하는 구어체로, 30자 이내로 짧고 강렬하게",
            "dramatic": "극적이고 감정적인 구어체로, 35자 이내로 생동감 있게",
            "calm": "차분하고 여운이 남는 구어체로, 30자 이내로 서정적으로"
        }
        
        style_instruction = style_prompts.get(style, style_prompts["curious"])
        
        prompt = f"""당신은 30초 쇼츠 영상의 나레이션 작가입니다.

스토리 주제: {user_input}
현재 씬: {scene_number}번째 장면
막 구조: {act_name} ({korean_mood} 분위기)
씬 제목: {scene_title}

요구사항:
1. {style_instruction}
2. 반말 종결어미 사용 (예: ~어요, ~죠, ~네요)
3. 시청자에게 직접 말하듯 친근하게
4. 핵심만 담은 한 문장
5. 이모티콘 사용 금지
6. 나레이션만 출력 (설명 없이)

예시:
- "여러분, 이건 정말 믿기 힘든 이야기인데 한번 들어보세요."
- "그런데 여기서 예상치 못한 일이 벌어지기 시작했죠."
- "바로 이 순간, 모든 게 완전히 바뀌어버렸어요."

지금 {scene_number}번 씬 ({act_name}, {korean_mood})의 나레이션을 작성하세요:"""
        
        try:
            response = requests.post(
                f"{self.base_url}/api/generate",
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "stream": False,
                    "options": {
                        "temperature": 0.7,
                        "top_p": 0.9,
                        "max_tokens": 50
                    }
                },
                timeout=30
            )
            
            if response.status_code == 200:
                result = response.json()
                narration = result.get("response", "").strip()
                
                # 따옴표 제거
                narration = narration.strip('"\'""''')
                
                # 30자 제한 (안전장치)
                if len(narration) > 40:
                    narration = narration[:37] + "..."
                
                logger.info(f"✅ 나레이션 생성 완료: {narration}")
                return narration
            else:
                logger.error(f"❌ Ollama 응답 오류: {response.status_code}")
                return None
                
        except Exception as e:
            logger.error(f"❌ 나레이션 생성 실패: {e}")
            return None
    
    def generate_batch(
        self,
        scenes_info: List[dict]
    ) -> List[Optional[str]]:
        """
        여러 씬의 나레이션을 배치로 생성
        
        Args:
            scenes_info: 씬 정보 리스트
                [{
                    "scene_number": 1,
                    "act_name": "발단",
                    "korean_mood": "신비로운",
                    "scene_title": "시작",
                    "user_input": "우주 비행사의 모험"
                }, ...]
                
        Returns:
            생성된 나레이션 리스트
        """
        results = []
        
        for info in scenes_info:
            narration = self.generate_narration(
                scene_number=info["scene_number"],
                act_name=info["act_name"],
                korean_mood=info["korean_mood"],
                scene_title=info["scene_title"],
                user_input=info["user_input"],
                style=info.get("style", "curious")
            )
            results.append(narration)
        
        success_count = sum(1 for n in results if n is not None)
        logger.info(f"✅ 배치 나레이션 생성: {success_count}/{len(scenes_info)} 성공")
        
        return results


# 사용 예시
if __name__ == "__main__":
    # 로깅 설정
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
    )
    
    # 생성기 초기화
    generator = OllamaNarrationGenerator()
    
    if generator.enabled:
        # 테스트 1: 단일 나레이션 생성
        print("\n=== 테스트 1: 1막 발단 나레이션 ===")
        narration1 = generator.generate_narration(
            scene_number=1,
            act_name="발단",
            korean_mood="신비로운",
            scene_title="시작",
            user_input="우주 비행사의 모험",
            style="curious"
        )
        print(f"생성: {narration1}")
        
        # 테스트 2: 4막 절정 나레이션
        print("\n=== 테스트 2: 4막 절정 나레이션 ===")
        narration2 = generator.generate_narration(
            scene_number=5,
            act_name="절정",
            korean_mood="충격적인",
            scene_title="결정적 순간",
            user_input="우주 비행사의 모험",
            style="dramatic"
        )
        print(f"생성: {narration2}")
        
        # 테스트 3: 배치 생성 (7씬)
        print("\n=== 테스트 3: 7씬 배치 생성 ===")
        scenes = [
            {"scene_number": 1, "act_name": "발단", "korean_mood": "신비로운", 
             "scene_title": "시작", "user_input": "제빵사의 아침"},
            {"scene_number": 2, "act_name": "전개", "korean_mood": "흥미진진한", 
             "scene_title": "준비", "user_input": "제빵사의 아침"},
            {"scene_number": 3, "act_name": "전개", "korean_mood": "발전하는", 
             "scene_title": "반죽", "user_input": "제빵사의 아침"},
            {"scene_number": 4, "act_name": "위기", "korean_mood": "긴장감 넘치는", 
             "scene_title": "오븐", "user_input": "제빵사의 아침"},
            {"scene_number": 5, "act_name": "절정", "korean_mood": "충격적인", 
             "scene_title": "완성", "user_input": "제빵사의 아침"},
            {"scene_number": 6, "act_name": "결말", "korean_mood": "여운이 남는", 
             "scene_title": "성공", "user_input": "제빵사의 아침"},
            {"scene_number": 7, "act_name": "결말", "korean_mood": "희망찬", 
             "scene_title": "새 시작", "user_input": "제빵사의 아침"}
        ]
        
        narrations = generator.generate_batch(scenes)
        
        for i, narration in enumerate(narrations, 1):
            print(f"{i}. {narration}")
    else:
        print("❌ Ollama 서비스가 실행 중이지 않습니다!")
        print("실행: ollama serve")
