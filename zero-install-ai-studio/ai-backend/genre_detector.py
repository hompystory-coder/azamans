"""
장르 자동 감지 및 맞춤 구조 적용 시스템
Ollama를 사용하여 스토리 장르를 자동으로 인식하고
각 장르에 최적화된 스토리 구조를 적용합니다.
"""

import requests
import logging
import json
import re
from typing import Optional, Dict

logger = logging.getLogger(__name__)


class GenreDetector:
    """장르 자동 감지 시스템"""
    
    # 장르별 스토리 구조 정의
    GENRE_STRUCTURES = {
        "동화": {
            "acts": 5,
            "act_names": ["발단", "전개", "위기", "절정", "결말"],
            "narration_style": "curious",
            "mood_palette": ["따뜻한", "신비로운", "희망찬", "감동적인", "행복한"],
            "description": "교훈적이고 마법적인 요소가 있는 이야기"
        },
        "액션": {
            "acts": 3,
            "act_names": ["도입", "갈등", "해결"],
            "narration_style": "dramatic",
            "mood_palette": ["긴박한", "폭발적인", "카타르시스"],
            "description": "빠른 전개와 클라이맥스 중심의 이야기"
        },
        "로맨스": {
            "acts": 5,
            "act_names": ["만남", "접근", "갈등", "화해", "결합"],
            "narration_style": "calm",
            "mood_palette": ["설레는", "두근거리는", "아픈", "감동적인", "행복한"],
            "description": "감정 중심의 사랑 이야기"
        },
        "공포": {
            "acts": 4,
            "act_names": ["평온", "불안", "공포", "반전"],
            "narration_style": "dramatic",
            "mood_palette": ["평온한", "불안한", "섬뜩한", "충격적인"],
            "description": "긴장감과 공포를 다루는 이야기"
        },
        "코미디": {
            "acts": 3,
            "act_names": ["설정", "문제", "해결"],
            "narration_style": "curious",
            "mood_palette": ["가벼운", "웃긴", "재미있는"],
            "description": "유머러스하고 가벼운 이야기"
        },
        "SF": {
            "acts": 5,
            "act_names": ["평범한 세계", "모험 시작", "시련", "보상", "귀환"],
            "narration_style": "dramatic",
            "mood_palette": ["미래적인", "신비로운", "긴장감 있는", "경이로운", "희망찬"],
            "description": "미래, 우주, 과학 기술을 다루는 이야기"
        }
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
            logger.info(f"✅ 장르 감지 시스템 활성화 (모델: {model})")
        else:
            logger.warning("⚠️ Ollama 서비스 미실행 - 기본 구조 사용")
    
    def _check_availability(self) -> bool:
        """Ollama 서비스 상태 확인"""
        try:
            response = requests.get(f"{self.base_url}/api/tags", timeout=2)
            return response.status_code == 200
        except:
            return False
    
    def detect_genre(self, user_input: str) -> Dict:
        """
        스토리 입력에서 장르 자동 감지
        
        Args:
            user_input: 사용자 입력 스토리
            
        Returns:
            {
                "genre": "동화",
                "confidence": 0.9,
                "keywords": ["마법", "교훈"],
                "structure": {...}
            }
        """
        if not self.enabled:
            logger.warning("Ollama 미실행 - 기본 장르(동화) 사용")
            return self._get_default_genre()
        
        # 장르 목록
        genre_options = list(self.GENRE_STRUCTURES.keys())
        genre_descriptions = "\n".join([
            f"- {genre}: {info['description']}"
            for genre, info in self.GENRE_STRUCTURES.items()
        ])
        
        prompt = f"""당신은 스토리 장르 분석 전문가입니다.

다음 스토리의 장르를 판단하세요:
"{user_input}"

장르 옵션:
{genre_descriptions}

요구사항:
1. 위 장르 중 가장 적합한 것을 선택
2. JSON 형식으로만 응답
3. 설명 없이 JSON만 출력

JSON 형식:
{{"genre": "동화", "keywords": ["마법", "교훈"], "confidence": 0.9}}

JSON 출력:"""
        
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
                response_text = result.get("response", "").strip()
                
                # JSON 추출
                genre_info = self._parse_json_response(response_text)
                
                if genre_info and "genre" in genre_info:
                    genre = genre_info["genre"]
                    
                    # 장르가 유효한지 확인
                    if genre in self.GENRE_STRUCTURES:
                        logger.info(f"✅ 장르 감지: {genre}")
                        
                        # 구조 정보 추가
                        genre_info["structure"] = self.GENRE_STRUCTURES[genre]
                        return genre_info
                    else:
                        logger.warning(f"⚠️ 알 수 없는 장르: {genre}, 기본 장르 사용")
                
        except Exception as e:
            logger.error(f"❌ 장르 감지 실패: {e}")
        
        # 실패 시 기본 장르
        return self._get_default_genre()
    
    def _parse_json_response(self, text: str) -> Optional[Dict]:
        """응답에서 JSON 추출"""
        try:
            # JSON 블록 찾기
            json_match = re.search(r'\{[^{}]*\}', text)
            if json_match:
                json_str = json_match.group(0)
                return json.loads(json_str)
        except:
            pass
        
        # 직접 파싱
        try:
            return json.loads(text)
        except:
            return None
    
    def _get_default_genre(self) -> Dict:
        """기본 장르 반환 (동화)"""
        return {
            "genre": "동화",
            "confidence": 1.0,
            "keywords": [],
            "structure": self.GENRE_STRUCTURES["동화"]
        }
    
    def apply_genre_structure(
        self,
        user_input: str,
        scenes_count: int = 7
    ) -> Dict:
        """
        장르 감지 후 맞춤 구조 적용
        
        Args:
            user_input: 스토리 입력
            scenes_count: 총 씬 개수
            
        Returns:
            {
                "genre": "동화",
                "structure": {...},
                "scenes": [...]
            }
        """
        # 1. 장르 감지
        genre_info = self.detect_genre(user_input)
        genre = genre_info["genre"]
        structure = genre_info["structure"]
        
        # 2. 씬 배분
        acts_count = structure["acts"]
        act_names = structure["act_names"]
        mood_palette = structure["mood_palette"]
        narration_style = structure["narration_style"]
        
        # 막별 씬 수 계산
        base_scenes_per_act = scenes_count // acts_count
        remaining_scenes = scenes_count % acts_count
        
        # 3. 씬 구성
        scenes = []
        scene_number = 1
        
        for act_num in range(acts_count):
            # 이 막에 배정할 씬 수 (남은 씬을 앞쪽 막에 분배)
            act_scenes = base_scenes_per_act + (1 if act_num < remaining_scenes else 0)
            
            for scene_in_act in range(act_scenes):
                # 막에 맞는 분위기 선택
                mood_index = min(scene_in_act, len(mood_palette) - 1)
                
                scene = {
                    "scene_number": scene_number,
                    "act_number": act_num + 1,
                    "act_name": act_names[act_num],
                    "mood": mood_palette[mood_index],
                    "style": narration_style,
                    "scene_in_act": scene_in_act + 1
                }
                
                scenes.append(scene)
                scene_number += 1
        
        logger.info(f"✅ 장르별 구조 적용: {genre} ({acts_count}막, {len(scenes)}씬)")
        
        return {
            "genre": genre,
            "genre_info": genre_info,
            "structure": structure,
            "scenes": scenes,
            "total_scenes": len(scenes)
        }


# 사용 예시
if __name__ == "__main__":
    # 로깅 설정
    logging.basicConfig(
        level=logging.INFO,
        format='%(levelname)s - %(message)s'
    )
    
    # 감지기 초기화
    detector = GenreDetector()
    
    if detector.enabled:
        # 테스트 스토리들
        test_stories = [
            {
                "input": "선녀와 나무꾼의 사랑 이야기",
                "expected": "동화"
            },
            {
                "input": "우주 해적과의 최후의 결전",
                "expected": "액션"
            },
            {
                "input": "외계인이 지구에 처음 도착한 날",
                "expected": "SF"
            },
            {
                "input": "한밤중 폐가에서 들리는 괴상한 소리",
                "expected": "공포"
            },
            {
                "input": "짝사랑하던 그녀와의 첫 데이트",
                "expected": "로맨스"
            }
        ]
        
        print("\n=== 장르 감지 테스트 ===\n")
        
        for test in test_stories:
            print(f"📖 스토리: {test['input']}")
            print(f"🎯 예상 장르: {test['expected']}")
            
            # 장르 감지
            result = detector.detect_genre(test['input'])
            detected_genre = result["genre"]
            
            print(f"🤖 감지 장르: {detected_genre}")
            print(f"🔑 키워드: {result.get('keywords', [])}")
            
            # 정확도 체크
            if detected_genre == test['expected']:
                print("✅ 정확!")
            else:
                print(f"⚠️  불일치 (예상: {test['expected']})")
            
            print()
        
        # 구조 적용 테스트
        print("\n=== 장르별 구조 적용 테스트 ===\n")
        
        test_input = "우주 비행사의 모험"
        print(f"📖 스토리: {test_input}")
        
        result = detector.apply_genre_structure(test_input, scenes_count=7)
        
        print(f"\n🎭 장르: {result['genre']}")
        print(f"📐 구조: {result['structure']['acts']}막 구조")
        print(f"🎬 총 씬: {result['total_scenes']}개")
        print(f"\n씬 구성:")
        
        for scene in result['scenes']:
            print(f"  씬 {scene['scene_number']}: {scene['act_name']} - {scene['mood']}")
        
    else:
        print("❌ Ollama 서비스가 실행 중이지 않습니다!")
