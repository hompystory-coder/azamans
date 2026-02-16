#!/usr/bin/env python3
"""
💬 LLM 스크립트 생성 모듈
Ollama LLaMA 3.1을 활용한 제품 쇼츠 스크립트 자동 생성
"""

import requests
import json
from pathlib import Path
from typing import Optional, List, Dict
from loguru import logger
import time

class ScriptGenerator:
    """Ollama 기반 스크립트 생성기"""
    
    def __init__(self, ollama_host: str = "http://localhost:11434"):
        self.ollama_host = ollama_host
        self.model = "llama3.1:8b"
        
    def check_ollama(self) -> bool:
        """Ollama 서버 작동 확인"""
        try:
            response = requests.get(f"{self.ollama_host}/api/tags", timeout=5)
            return response.status_code == 200
        except:
            return False
    
    def list_models(self) -> List[str]:
        """사용 가능한 모델 목록"""
        try:
            response = requests.get(f"{self.ollama_host}/api/tags")
            if response.status_code == 200:
                models = response.json().get("models", [])
                return [m["name"] for m in models]
            return []
        except:
            return []
    
    def generate_script(
        self,
        product_info: Dict[str, str],
        character_id: str,
        num_scenes: int = 5,
        target_duration: int = 15,
        tone: str = "professional"
    ) -> Dict:
        """제품 정보로부터 쇼츠 스크립트 생성
        
        Args:
            product_info: 제품 정보 (title, description, features, price 등)
            character_id: 캐릭터 ID
            num_scenes: 장면 수
            target_duration: 목표 길이 (초)
            tone: 톤 (professional, casual, funny, enthusiastic)
            
        Returns:
            스크립트 딕셔너리 (scenes, metadata)
        """
        try:
            logger.info(f"💬 Generating script for {character_id}")
            logger.info(f"   Product: {product_info.get('title', 'Unknown')}")
            logger.info(f"   Scenes: {num_scenes}, Duration: {target_duration}s")
            
            start_time = time.time()
            
            # 캐릭터 페르소나
            persona = self._get_character_persona(character_id)
            
            # 프롬프트 생성
            prompt = self._build_prompt(
                product_info, persona, num_scenes, target_duration, tone
            )
            
            # Ollama API 호출
            if not self.check_ollama():
                logger.warning("⚠️ Ollama not available, using template")
                return self._generate_template_script(product_info, num_scenes)
            
            response = requests.post(
                f"{self.ollama_host}/api/generate",
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "stream": False,
                    "options": {
                        "temperature": 0.7,
                        "top_p": 0.9,
                        "max_tokens": 1000
                    }
                },
                timeout=60
            )
            
            if response.status_code == 200:
                result = response.json()
                script_text = result.get("response", "")
                
                # 스크립트 파싱
                script = self._parse_script(script_text, num_scenes)
                
                gen_time = time.time() - start_time
                logger.info(f"✅ Script generated in {gen_time:.1f}s")
                
                return script
            else:
                logger.error(f"❌ Ollama API error: {response.status_code}")
                return self._generate_template_script(product_info, num_scenes)
                
        except Exception as e:
            logger.error(f"❌ Script generation failed: {str(e)}")
            logger.info("   Falling back to template script")
            return self._generate_template_script(product_info, num_scenes)
    
    def _get_character_persona(self, character_id: str) -> str:
        """캐릭터 페르소나 가져오기"""
        personas = {
            "executive-fox": "professional business executive with confident and authoritative tone",
            "ceo-lion": "powerful CEO with commanding presence and leadership qualities",
            "tech-fox": "tech-savvy innovator with enthusiastic and modern communication style",
            "fashionista-cat": "stylish fashion expert with elegant and trendy personality",
            "comedian-parrot": "humorous entertainer with funny and energetic character",
            "chef-penguin": "passionate chef with warm and appetizing presentation style"
        }
        return personas.get(character_id, personas["executive-fox"])
    
    def _build_prompt(
        self,
        product_info: Dict,
        persona: str,
        num_scenes: int,
        duration: int,
        tone: str
    ) -> str:
        """LLM 프롬프트 생성"""
        title = product_info.get("title", "제품")
        description = product_info.get("description", "")
        features = product_info.get("features", [])
        price = product_info.get("price", "")
        
        features_text = "\n".join([f"- {f}" for f in features]) if features else "없음"
        
        prompt = f"""You are a {persona}. Create a short-form video script (YouTube Shorts, TikTok) to promote this product.

Product Information:
- Title: {title}
- Description: {description}
- Key Features:
{features_text}
- Price: {price}

Requirements:
- Create {num_scenes} scenes
- Total duration: approximately {duration} seconds
- Tone: {tone}
- Each scene should be 2-3 seconds
- Include engaging hook, key benefits, and call-to-action
- Use simple, conversational language suitable for Korean audience
- Make it exciting and persuasive

Format your response as:
Scene 1: [2s] <script text>
Scene 2: [3s] <script text>
...

Begin:"""

        return prompt
    
    def _parse_script(self, script_text: str, num_scenes: int) -> Dict:
        """LLM 응답을 구조화된 스크립트로 파싱"""
        scenes = []
        lines = script_text.strip().split("\n")
        
        for line in lines:
            line = line.strip()
            if not line or not line.startswith("Scene"):
                continue
            
            try:
                # "Scene 1: [2s] 텍스트" 형식 파싱
                parts = line.split(":", 1)
                if len(parts) < 2:
                    continue
                
                scene_num = int(parts[0].replace("Scene", "").strip())
                content = parts[1].strip()
                
                # 시간 추출
                duration = 3  # 기본값
                if content.startswith("[") and "]" in content:
                    time_str = content[content.find("[")+1:content.find("]")]
                    try:
                        duration = int(time_str.replace("s", "").strip())
                    except:
                        pass
                    content = content[content.find("]")+1:].strip()
                
                scenes.append({
                    "scene_number": scene_num,
                    "text": content,
                    "duration": duration
                })
            except:
                continue
        
        # 장면이 충분하지 않으면 템플릿 사용
        if len(scenes) < num_scenes // 2:
            logger.warning(f"⚠️ Parsed only {len(scenes)} scenes, using template")
            return self._generate_template_script({}, num_scenes)
        
        return {
            "scenes": scenes[:num_scenes],
            "total_duration": sum(s["duration"] for s in scenes[:num_scenes]),
            "metadata": {
                "character_id": "",
                "tone": "",
                "generated_at": int(time.time())
            }
        }
    
    def _generate_template_script(
        self,
        product_info: Dict,
        num_scenes: int = 5
    ) -> Dict:
        """템플릿 기반 스크립트 생성 (폴백)"""
        logger.info("📝 Using template-based script generation")
        
        title = product_info.get("title", "이 제품")
        
        templates = [
            {"text": f"안녕하세요! 오늘은 {title}을(를) 소개해드릴게요!", "duration": 3},
            {"text": f"{title}의 가장 큰 장점은 뭘까요?", "duration": 3},
            {"text": "놀라운 기능들을 하나씩 살펴볼까요?", "duration": 3},
            {"text": "가격도 매우 합리적이에요!", "duration": 2},
            {"text": "지금 바로 확인해보세요!", "duration": 2}
        ]
        
        scenes = []
        for i, template in enumerate(templates[:num_scenes], 1):
            scenes.append({
                "scene_number": i,
                "text": template["text"],
                "duration": template["duration"]
            })
        
        return {
            "scenes": scenes,
            "total_duration": sum(s["duration"] for s in scenes),
            "metadata": {
                "character_id": "",
                "tone": "template",
                "generated_at": int(time.time())
            }
        }
    
    def enhance_script(self, script: Dict, character_id: str) -> Dict:
        """캐릭터 페르소나를 반영하여 스크립트 개선"""
        # 캐릭터별 수식어 추가
        character_modifiers = {
            "executive-fox": ["전문적으로", "확실하게", "신뢰할 수 있는"],
            "comedian-parrot": ["재미있게", "유쾌하게", "신나게"],
            "tech-fox": ["혁신적인", "최첨단", "스마트한"],
        }
        
        modifiers = character_modifiers.get(character_id, [])
        
        # 스크립트 개선 (실제로는 LLM으로 재생성하는 것이 좋음)
        enhanced = script.copy()
        enhanced["metadata"]["character_id"] = character_id
        
        return enhanced


# ========== 테스트 코드 ==========
if __name__ == "__main__":
    # 테스트
    generator = ScriptGenerator()
    
    # Ollama 확인
    if generator.check_ollama():
        logger.info("✅ Ollama is running")
        models = generator.list_models()
        logger.info(f"   Available models: {models}")
    else:
        logger.warning("⚠️ Ollama is not running")
        logger.info("   Start Ollama: ollama serve")
        logger.info("   Pull model: ollama pull llama3.1:8b")
    
    # 스크립트 생성 테스트
    # product_info = {
    #     "title": "프리미엄 무선 이어폰",
    #     "description": "최고의 음질과 노이즈 캔슬링 기능",
    #     "features": ["ANC", "30시간 배터리", "IPX7 방수"],
    #     "price": "149,000원"
    # }
    # 
    # script = generator.generate_script(
    #     product_info=product_info,
    #     character_id="executive-fox",
    #     num_scenes=5
    # )
    # print(json.dumps(script, indent=2, ensure_ascii=False))
