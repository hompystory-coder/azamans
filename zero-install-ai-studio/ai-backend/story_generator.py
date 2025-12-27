"""
AI Story Script Generator
사용자 입력 → 30초+ 스토리 스크립트 자동 생성
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import logging
from datetime import datetime
import json
import re
import os

# 로깅 먼저 설정
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Optional: OpenAI import (폴백 시스템이 있으므로 선택적)
try:
    from openai import OpenAI
    OPENAI_AVAILABLE = True
except ImportError:
    OpenAI = None
    OPENAI_AVAILABLE = False

# Ollama 통합 (로컬 AI 모델)
try:
    import requests
    OLLAMA_AVAILABLE = True
    OLLAMA_BASE_URL = "http://localhost:11434"
except ImportError:
    OLLAMA_AVAILABLE = False

# Replicate 통합 (이미지/영상 생성)
try:
    from replicate_client import ReplicateClient
    replicate_client = ReplicateClient()
    REPLICATE_AVAILABLE = replicate_client.enabled
    if REPLICATE_AVAILABLE:
        logger.info("✅ Replicate API 통합 완료 (유료, 초기 $5 무료)")
except ImportError:
    replicate_client = None
    REPLICATE_AVAILABLE = False
    logger.warning("⚠️ Replicate 클라이언트 미설치")

# Hugging Face 통합 (완전 무료 이미지 생성)
try:
    from huggingface_client import HuggingFaceClient
    hf_client = HuggingFaceClient()
    HF_AVAILABLE = hf_client.enabled
    if HF_AVAILABLE:
        logger.info("✅ Hugging Face API 통합 완료 (완전 무료, 느림)")
except ImportError:
    hf_client = None
    HF_AVAILABLE = False
    logger.warning("⚠️ Hugging Face 클라이언트 미설치")

# 🆕 장르 감지 시스템 통합
try:
    from genre_detector import GenreDetector
    genre_detector = GenreDetector()
    GENRE_DETECTION_AVAILABLE = True
    logger.info("✅ 장르 감지 시스템 통합 완료 (6개 장르 지원)")
except ImportError as e:
    genre_detector = None
    GENRE_DETECTION_AVAILABLE = False
    logger.warning(f"⚠️ 장르 감지 시스템 미설치: {e}")

# 🆕 나레이션 자동 생성 시스템 통합
try:
    from ollama_narration_generator import OllamaNarrationGenerator
    narration_gen = OllamaNarrationGenerator()
    NARRATION_GEN_AVAILABLE = narration_gen.enabled
    if NARRATION_GEN_AVAILABLE:
        logger.info("✅ 나레이션 자동 생성 시스템 통합 완료 (Ollama)")
except ImportError as e:
    narration_gen = None
    NARRATION_GEN_AVAILABLE = False
    logger.warning(f"⚠️ 나레이션 생성 시스템 미설치: {e}")

# 🆕 다국어 번역 시스템 통합
try:
    from multilang_translator import MultiLangTranslator
    translator = MultiLangTranslator()
    TRANSLATOR_AVAILABLE = translator.enabled
    if TRANSLATOR_AVAILABLE:
        logger.info("✅ 다국어 번역 시스템 통합 완료 (5개 언어 지원)")
except ImportError as e:
    translator = None
    TRANSLATOR_AVAILABLE = False
    logger.warning(f"⚠️ 다국어 번역 시스템 미설치: {e}")

app = Flask(__name__)
CORS(app)

# AI 시스템 초기화 우선순위:
# 1순위: Ollama (로컬, 무료, 빠름)
# 2순위: OpenAI (클라우드, 유료, 정확)
# 3순위: 규칙 기반 폴백 (항상 작동)

openai_client = None
ollama_available = False
AI_ENABLED = False
AI_PROVIDER = None

# 1순위: Ollama 체크
if OLLAMA_AVAILABLE:
    try:
        # Ollama 서비스 상태 확인
        response = requests.get(f"{OLLAMA_BASE_URL}/api/tags", timeout=2)
        if response.status_code == 200:
            models = response.json().get('models', [])
            if models:
                ollama_available = True
                AI_ENABLED = True
                AI_PROVIDER = "ollama"
                logger.info(f"✅ Ollama AI 시스템 활성화 ({len(models)}개 모델 사용 가능)")
                logger.info(f"   모델: {', '.join([m['name'] for m in models[:3]])}")
    except Exception as e:
        logger.warning(f"⚠️ Ollama 연결 실패, OpenAI 시도: {e}")

# 2순위: OpenAI 체크 (Ollama 실패 시)
if not AI_ENABLED and OPENAI_AVAILABLE and os.environ.get("OPENAI_API_KEY"):
    try:
        openai_client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))
        AI_ENABLED = True
        AI_PROVIDER = "openai"
        logger.info("✅ OpenAI API 시스템 활성화")
    except Exception as e:
        logger.warning(f"⚠️ OpenAI 초기화 실패: {e}")

# 3순위: 폴백 (항상 작동)
if not AI_ENABLED:
    logger.info("✅ 확장 키워드 템플릿 시스템 사용 (33개 직업/활동 지원)")

def is_long_story(text: str) -> bool:
    """
    입력이 장문 스토리인지 판단
    - 100자 이상: 장문 스토리로 간주
    - 특수 문자 (마침표, 느낌표, 물음표) 3개 이상: 장문 스토리
    """
    char_count = len(text.strip())
    sentence_markers = text.count('.') + text.count('!') + text.count('?') + text.count('。')
    
    return char_count > 100 or sentence_markers >= 3

def analyze_with_ollama(long_story: str) -> dict:
    """
    🤖 Ollama (로컬 AI)를 사용하여 스토리 분석
    - 무료, 빠름, 로컬 처리
    - deepseek-r1:1.5b 또는 llama3.1:8b 사용
    """
    try:
        # 장문 분석은 정확도가 중요 → llama3.1:8b 사용
        model = "llama3.1:8b"
        
        # 간단한 프롬프트 (Ollama가 더 잘 이해)
        prompt = f"""Analyze the story and create a JSON response.

Story: "{long_story[:1000]}"

Create JSON with:
- title: short catchy title
- summary: one sentence summary  
- main_character: main character name
- key_events: array of 5 key events
- five_acts: object with exposition, rising_action, conflict, climax, resolution

Output ONLY valid JSON, no explanation:"""

        response = requests.post(
            f"{OLLAMA_BASE_URL}/api/generate",
            json={
                "model": model,
                "prompt": prompt,
                "stream": False,
                "options": {
                    "temperature": 0.7,
                    "num_predict": 500
                }
            },
            timeout=30
        )
        
        if response.status_code == 200:
            result_text = response.json().get('response', '')
            
            # JSON 추출 시도
            try:
                # 중괄호로 둘러싸인 JSON 찾기
                import re
                json_match = re.search(r'\{.*\}', result_text, re.DOTALL)
                if json_match:
                    result = json.loads(json_match.group())
                    logger.info(f"✅ Ollama 분석 성공: {result.get('title', 'N/A')}")
                    return result
            except:
                pass
        
        logger.warning("⚠️ Ollama JSON 파싱 실패 - 폴백 사용")
        return None
        
    except Exception as e:
        logger.error(f"❌ Ollama 분석 실패: {e}")
        return None

def analyze_and_summarize_story(long_story: str) -> dict:
    """
    🤖 AI를 사용하여 장문 스토리를 분석하고 핵심 요약 추출
    
    Args:
        long_story: 장문 스토리 텍스트
    
    Returns:
        {
            'title': '스토리 제목',
            'summary': '한 줄 요약',
            'main_character': '주인공',
            'key_events': ['사건1', '사건2', ...],
            'five_acts': {
                'exposition': '발단 내용',
                'rising_action': '전개 내용',
                'conflict': '위기 내용',
                'climax': '절정 내용',
                'resolution': '결말 내용'
            }
        }
    """
    # 1순위: Ollama 시도
    if ollama_available:
        result = analyze_with_ollama(long_story)
        if result:
            return result
        logger.warning("⚠️ Ollama 실패 - OpenAI 시도")
    
    # 2순위: OpenAI 시도
    if not OPENAI_AVAILABLE or not openai_client:
        logger.warning("⚠️ AI 비활성화 - 기본 분석 사용")
        return _fallback_story_analysis(long_story)
    
    system_prompt = """You are a professional story analyst and screenwriter.
Your job is to analyze a long story and extract key elements for creating a short video (30-60 seconds).

Analyze the story and provide:
1. A catchy title (10 words max)
2. One-line summary (20 words max)
3. Main character
4. 5-7 key events in chronological order
5. Break the story into 5 acts (exposition, rising action, conflict, climax, resolution)

IMPORTANT: Keep everything concise and visual. Focus on action and drama.
Response must be in JSON format."""

    user_prompt = f"""Analyze this story and extract key elements:

Story:
{long_story[:2000]}

Return JSON format:
{{
    "title": "catchy title here",
    "summary": "one-line summary",
    "main_character": "protagonist name/description",
    "key_events": ["event1", "event2", ...],
    "five_acts": {{
        "exposition": "beginning description",
        "rising_action": "development description",
        "conflict": "crisis description",
        "climax": "peak moment description",
        "resolution": "ending description"
    }}
}}"""

    try:
        response = openai_client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            max_tokens=1000,
            temperature=0.7,
            response_format={"type": "json_object"},
            timeout=30
        )
        
        result = json.loads(response.choices[0].message.content)
        logger.info(f"✅ 장문 스토리 분석 성공: {result.get('title', 'N/A')}")
        return result
        
    except Exception as e:
        logger.error(f"❌ AI 분석 실패: {e}")
        return _fallback_story_analysis(long_story)

def _fallback_story_analysis(long_story: str) -> dict:
    """
    AI 실패 시 폴백: 간단한 규칙 기반 분석
    """
    # 제목 추출 로직 개선
    # 1. 따옴표 안의 제목이 있는지 확인
    import re
    quoted_match = re.search(r'["\']([^"\']+)["\']', long_story[:100])
    if quoted_match:
        title = quoted_match.group(1)[:30]
    else:
        # 2. 첫 20자를 제목으로 (의미 있는 부분만)
        first_part = long_story[:30].strip()
        # 마지막 완전한 단어까지만 포함
        if ' ' in first_part:
            words = first_part.split()
            title = ' '.join(words[:3]) + "..."
        else:
            title = first_part
    
    # 간단한 5막 구조 (텍스트를 5등분)
    story_length = len(long_story)
    chunk_size = max(1, story_length // 5)
    
    # 각 막의 핵심 내용 추출 (첫 100자)
    acts = []
    for i in range(5):
        start = i * chunk_size
        end = min((i + 1) * chunk_size, story_length)
        chunk = long_story[start:end].strip()
        # 완전한 문장으로 자르기
        sentences = chunk.split('.')
        act_text = '. '.join(sentences[:2])[:150]
        acts.append(act_text)
    
    return {
        'title': title,
        'summary': long_story[:100].strip() + "...",
        'main_character': "주인공",
        'key_events': [
            long_story[i*chunk_size:min((i+1)*chunk_size, story_length)][:50].strip() + "..."
            for i in range(min(5, story_length // chunk_size))
        ],
        'five_acts': {
            'exposition': acts[0] if len(acts) > 0 else "",
            'rising_action': acts[1] if len(acts) > 1 else "",
            'conflict': acts[2] if len(acts) > 2 else "",
            'climax': acts[3] if len(acts) > 3 else "",
            'resolution': acts[4] if len(acts) > 4 else ""
        }
    }

def generate_story_script(user_input: str, duration_seconds: int = 30) -> dict:
    """
    사용자 입력을 기반으로 스토리 스크립트 생성
    
    📝 지원 입력 형식:
    1. 짧은 제목 (예: "행복한 제빵사의 아침")
    2. 장문 스토리 (100자 이상)
    """
    try:
        # 장면 개수 계산 (3-5초당 1장면)
        scenes_count = max(5, duration_seconds // 4)  # 최소 5장면
        scene_duration = duration_seconds / scenes_count
        
        # 🆕 장문 스토리 감지 및 분석
        if is_long_story(user_input):
            logger.info(f"📚 장문 스토리 감지 ({len(user_input)}자) - AI 분석 시작...")
            analysis = analyze_and_summarize_story(user_input)
            
            # 분석 결과를 사용하여 스토리 생성
            logger.info(f"✅ 분석 완료: '{analysis.get('title', 'Unknown')}' - {scenes_count} scenes")
            return generate_story_from_analysis(analysis, scenes_count, scene_duration)
        
        # 기존: 짧은 제목 처리
        logger.info(f"Generating story: '{user_input}' - {scenes_count} scenes, {scene_duration}s each")
        
        # 스토리 템플릿 매칭
        story_templates = {
            "선녀와 나무꾼": generate_seonnyeo_story,
            "흥부와 놀부": generate_heungbu_story,
            "심청전": generate_simcheong_story,
            "토끼와 거북이": generate_rabbit_turtle_story,
            "콩쥐팥쥐": generate_kongjui_patjui_story,
        }
        
        # 템플릿 매칭
        for key, generator_func in story_templates.items():
            if key in user_input:
                return generator_func(scenes_count, scene_duration)
        
        # 커스텀 스토리 생성
        return generate_custom_story(user_input, scenes_count, scene_duration)
        
    except Exception as e:
        logger.error(f"Error generating story: {e}")
        raise

def generate_seonnyeo_story(scenes_count: int, scene_duration: float) -> dict:
    """선녀와 나무꾼 스토리 - 구어체 궁금증 유발형"""
    base_scenes = [
        {
            "scene_number": 1,
            "title": "이상한 소리",
            "description": "A poor woodcutter in deep mountain forest suddenly hears mysterious laughter, curious expression, sunset light filtering through trees, traditional Korean painting style",
            "korean_description": "평범하게 나무를 하던 나무꾼이 갑자기 어디선가 들려오는 이상한 웃음소리를 듣게 돼요.",
            "narration": "평범하게 나무를 하던 나무꾼이 갑자기 어디선가 들려오는 이상한 웃음소리를 듣게 돼요.",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "curious"
        },
        {
            "scene_number": 2,
            "title": "믿을 수 없는 광경",
            "description": "Beautiful fairy maidens bathing in crystal clear pond, magical sparkles, shocked woodcutter peeking from bushes, ethereal atmosphere",
            "korean_description": "소리 나는 쪽으로 살금살금 다가갔더니 세상에 진짜 선녀들이 목욕을 하고 있는 거예요.",
            "narration": "소리 나는 쪽으로 살금살금 다가갔더니 세상에 진짜 선녀들이 목욕을 하고 있는 거예요.",
            "duration": scene_duration,
            "camera_movement": "pan_right",
            "mood": "shocking"
        },
        {
            "scene_number": 3,
            "title": "사슴의 속삭임",
            "description": "Wise deer whispering to woodcutter, pointing at hidden fairy robe glowing behind tree, magical light, mystical Korean folklore",
            "korean_description": "그때 사슴 한 마리가 나타나서 날개옷을 숨기면 선녀를 아내로 맞을 수 있다고 귓속말을 해줘요.",
            "narration": "그때 사슴 한 마리가 나타나서 날개옷을 숨기면 선녀를 아내로 맞을 수 있다고 귓속말을 해줘요.",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "mysterious"
        },
        {
            "scene_number": 4,
            "title": "운명의 만남",
            "description": "Crying fairy desperately searching for her robe, woodcutter appearing with gentle smile, romantic first encounter, traditional Korean style",
            "korean_description": "날개옷이 없어진 선녀는 울면서 나무꾼 앞에 나타났고 둘은 서로에게 첫눈에 반하게 돼요.",
            "narration": "날개옷이 없어진 선녀는 울면서 나무꾼 앞에 나타났고 둘은 서로에게 첫눈에 반하게 돼요.",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "romantic"
        },
        {
            "scene_number": 5,
            "title": "행복한 나날",
            "description": "Happy family with three children playing, woodcutter and fairy wife laughing together, warm home, joyful moments, traditional Korean family",
            "korean_description": "세월이 흘러 두 사람은 예쁜 아이 셋을 낳고 너무나 행복한 나날을 보내게 되죠.",
            "narration": "세월이 흘러 두 사람은 예쁜 아이 셋을 낳고 너무나 행복한 나날을 보내게 되죠.",
            "duration": scene_duration,
            "camera_movement": "pan_left",
            "mood": "happy"
        },
        {
            "scene_number": 6,
            "title": "치명적 실수",
            "description": "Fairy discovering hidden robe in old chest, shocked expression, realization moment, dramatic lighting, emotional scene",
            "korean_description": "그런데 어느 날 선녀가 오래된 상자에서 숨겨져 있던 날개옷을 발견하고 말아요.",
            "narration": "그런데 어느 날 선녀가 오래된 상자에서 숨겨져 있던 날개옷을 발견하고 말아요.",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "tense"
        },
        {
            "scene_number": 7,
            "title": "비극적 결말",
            "description": "Fairy flying to heaven with children, crying woodcutter reaching out desperately, dramatic farewell, heartbreaking separation, sunset sky",
            "korean_description": "선녀는 아이들을 안고 하늘로 날아가버렸고 나무꾼은 그저 하늘만 바라보며 울 수밖에 없었어요.",
            "narration": "선녀는 아이들을 안고 하늘로 날아가버렸고 나무꾼은 그저 하늘만 바라보며 울 수밖에 없었어요.",
            "duration": scene_duration,
            "camera_movement": "tilt_up",
            "mood": "tragic"
        },
    ]
    
    # 장면 개수에 맞게 조정
    selected_scenes = base_scenes[:scenes_count]
    
    return {
        "title": "선녀와 나무꾼",
        "genre": "한국 전통 설화",
        "total_duration": sum(s['duration'] for s in selected_scenes),
        "total_scenes": len(selected_scenes),
        "style": "traditional Korean painting",
        "mood": "romantic and melancholic",
        "scenes": selected_scenes,
        "music_suggestion": "Traditional Korean gayageum with emotional melody"
    }

def generate_heungbu_story(scenes_count: int, scene_duration: float) -> dict:
    """흥부와 놀부 스토리 - 구어체 궁금증 유발형"""
    base_scenes = [
        {
            "scene_number": 1,
            "title": "불공평한 세상",
            "description": "Two brothers living in opposite worlds, poor Heungbu's shabby house vs rich Nolbu's luxurious mansion side by side, dramatic contrast",
            "korean_description": "똑같은 형제인데 한쪽은 부자 한쪽은 가난뱅이 이게 대체 무슨 일일까요.",
            "narration": "똑같은 형제인데 한쪽은 부자 한쪽은 가난뱅이 이게 대체 무슨 일일까요.",
            "duration": scene_duration,
            "camera_movement": "pan_right",
            "mood": "contrasting"
        },
        {
            "scene_number": 2,
            "title": "냉혹한 형님",
            "description": "Evil Nolbu kicking starving Heungbu out of his mansion, cold-hearted rejection, cruel brother, dark atmosphere",
            "korean_description": "가난한 흥부가 도움을 청하러 갔지만 형 놀부는 매몰차게 쫓아내버려요.",
            "narration": "가난한 흥부가 도움을 청하러 갔지만 형 놀부는 매몰차게 쫓아내버려요.",
            "duration": scene_duration,
            "camera_movement": "shake",
            "mood": "harsh"
        },
        {
            "scene_number": 3,
            "title": "기적의 만남",
            "description": "Kind Heungbu discovering injured baby swallow fallen from nest, gentle rescue, compassionate moment, traditional Korean style",
            "korean_description": "그런데 흥부가 집으로 돌아오다가 둥지에서 떨어진 새끼 제비를 발견하게 돼요.",
            "narration": "그런데 흥부가 집으로 돌아오다가 둥지에서 떨어진 새끼 제비를 발견하게 돼요.",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "hopeful"
        },
        {
            "scene_number": 4,
            "title": "정성스런 돌봄",
            "description": "Heungbu carefully treating swallow's broken leg, bandaging with care, family helping together, warm scene",
            "korean_description": "흥부는 자기도 굶주리고 있었지만 제비를 정성껏 치료해주고 날려보내죠.",
            "narration": "흥부는 자기도 굶주리고 있었지만 제비를 정성껏 치료해주고 날려보내죠.",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "caring"
        },
        {
            "scene_number": 5,
            "title": "믿을 수 없는 선물",
            "description": "Swallow returning with magical seed in beak, dropping glowing gourd seed, mysterious gift, grateful bird",
            "korean_description": "그리고 이듬해 봄 그 제비가 다시 찾아와서 신기한 박씨 하나를 물고 왔어요.",
            "narration": "그리고 이듬해 봄 그 제비가 다시 찾아와서 신기한 박씨 하나를 물고 왔어요.",
            "duration": scene_duration,
            "camera_movement": "tilt_down",
            "mood": "magical"
        },
        {
            "scene_number": 6,
            "title": "대박의 순간",
            "description": "Enormous golden treasures bursting from magical gourd, sparkling gold coins and jewels everywhere, miraculous wealth, family shocked and happy",
            "korean_description": "박을 타보니 세상에 엄청난 금은보화가 쏟아져 나와서 흥부네는 단번에 부자가 됐어요.",
            "narration": "박을 타보니 세상에 엄청난 금은보화가 쏟아져 나와서 흥부네는 단번에 부자가 됐어요.",
            "duration": scene_duration,
            "camera_movement": "zoom_out",
            "mood": "explosive"
        },
        {
            "scene_number": 7,
            "title": "욕심의 대가",
            "description": "Greedy Nolbu intentionally breaking swallow's leg with evil grin, planting seed with greed, then terrifying goblins and monsters emerging from his gourd, destruction and chaos",
            "korean_description": "이 소식을 들은 놀부가 일부러 제비 다리를 부러뜨렸다가 박에서 도깨비들만 나와서 집이 완전히 망해버렸어요.",
            "narration": "이 소식을 들은 놀부가 일부러 제비 다리를 부러뜨렸다가 박에서 도깨비들만 나와서 집이 완전히 망해버렸어요.",
            "duration": scene_duration,
            "camera_movement": "shake",
            "mood": "catastrophic"
        },
    ]
    
    selected_scenes = base_scenes[:scenes_count]
    
    return {
        "title": "흥부와 놀부",
        "genre": "한국 전통 설화",
        "total_duration": sum(s['duration'] for s in selected_scenes),
        "total_scenes": len(selected_scenes),
        "style": "traditional Korean painting",
        "mood": "moral lesson story",
        "scenes": selected_scenes,
        "music_suggestion": "Traditional Korean instruments with dramatic changes"
    }

def generate_simcheong_story(scenes_count: int, scene_duration: float) -> dict:
    """심청전 스토리"""
    base_scenes = [
        {
            "scene_number": 1,
            "title": "심봉사와 심청",
            "description": "Blind father Sim Bongsa with young daughter Simcheong, poverty and love, traditional Korean home, emotional bond",
            "korean_description": "앞을 보지 못하는 심봉사와 어린 심청",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "emotional"
        },
        {
            "scene_number": 2,
            "title": "공양미 삼백 석",
            "description": "Simcheong selling herself for 300 sacks of rice offering, sacrifice for father, emotional decision",
            "korean_description": "아버지를 위해 공양미 300석에 팔리는 심청",
            "duration": scene_duration,
            "camera_movement": "pan_left",
            "mood": "sacrificial"
        },
        {
            "scene_number": 3,
            "title": "인당수에 몸을 던지다",
            "description": "Simcheong jumping into Indangsu sea, dramatic sacrifice, stormy ocean, emotional farewell, waves",
            "korean_description": "인당수에 빠지는 심청, 슬픈 이별",
            "duration": scene_duration,
            "camera_movement": "tilt_down",
            "mood": "dramatic"
        },
        {
            "scene_number": 4,
            "title": "용궁",
            "description": "Underwater dragon palace, magnificent underwater kingdom, magical sea creatures, beautiful corals and lights",
            "korean_description": "용궁에서 환대받는 심청, 화려한 수중 궁전",
            "duration": scene_duration,
            "camera_movement": "pan_around",
            "mood": "magical"
        },
        {
            "scene_number": 5,
            "title": "연꽃을 타고",
            "description": "Simcheong rising on lotus flower, rebirth, magical transformation, beautiful lotus blooming from water",
            "korean_description": "연꽃을 타고 떠오르는 심청",
            "duration": scene_duration,
            "camera_movement": "tilt_up",
            "mood": "miraculous"
        },
        {
            "scene_number": 6,
            "title": "왕비가 되다",
            "description": "Simcheong becoming queen, royal palace, beautiful traditional Korean royal clothing, crown and elegance",
            "korean_description": "왕비가 된 심청",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "royal"
        },
        {
            "scene_number": 7,
            "title": "감동의 재회",
            "description": "Emotional reunion of Simcheong and father, tears of joy, miracle of father regaining sight, happy ending",
            "korean_description": "심청과 아버지의 감동적인 재회, 눈을 뜬 심봉사",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_out",
            "mood": "joyful"
        },
    ]
    
    selected_scenes = base_scenes[:scenes_count]
    
    return {
        "title": "심청전",
        "genre": "한국 전통 설화",
        "total_duration": sum(s['duration'] for s in selected_scenes),
        "total_scenes": len(selected_scenes),
        "style": "traditional Korean painting with underwater fantasy",
        "mood": "sacrificial and miraculous",
        "scenes": selected_scenes,
        "music_suggestion": "Emotional traditional Korean music with dramatic crescendo"
    }

def generate_rabbit_turtle_story(scenes_count: int, scene_duration: float) -> dict:
    """토끼와 거북이 스토리"""
    base_scenes = [
        {
            "scene_number": 1,
            "title": "자만하는 토끼",
            "description": "Arrogant rabbit boasting about speed, showing off, prideful expression, forest setting",
            "korean_description": "자신의 빠른 속도를 자랑하는 교만한 토끼",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "prideful"
        },
        {
            "scene_number": 2,
            "title": "느린 거북이",
            "description": "Slow but steady turtle, determined expression, perseverance, humble attitude",
            "korean_description": "느리지만 꾸준한 거북이",
            "duration": scene_duration,
            "camera_movement": "pan_left",
            "mood": "determined"
        },
        {
            "scene_number": 3,
            "title": "경주 시작",
            "description": "Race starting line, rabbit and turtle ready, forest race track, excitement and anticipation",
            "korean_description": "경주가 시작되는 출발선",
            "duration": scene_duration,
            "camera_movement": "zoom_out",
            "mood": "exciting"
        },
        {
            "scene_number": 4,
            "title": "토끼의 낮잠",
            "description": "Rabbit sleeping under tree, overconfident nap, peaceful forest, turtle passing by",
            "korean_description": "나무 아래서 낮잠 자는 자만한 토끼",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "peaceful"
        },
        {
            "scene_number": 5,
            "title": "거북이의 노력",
            "description": "Turtle continuing steadily, perseverance and determination, slow but consistent progress",
            "korean_description": "꾸준히 나아가는 거북이의 노력",
            "duration": scene_duration,
            "camera_movement": "follow",
            "mood": "persevering"
        },
        {
            "scene_number": 6,
            "title": "결승선",
            "description": "Turtle crossing finish line first, victory of perseverance, surprised rabbit in background",
            "korean_description": "결승선을 먼저 통과하는 거북이",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "triumphant"
        },
        {
            "scene_number": 7,
            "title": "교훈",
            "description": "Moral lesson scene, wisdom text, rabbit learning humility, turtle's smile, meaningful ending",
            "korean_description": "느리지만 꾸준함이 이긴다는 교훈",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_out",
            "mood": "educational"
        },
    ]
    
    selected_scenes = base_scenes[:scenes_count]
    
    return {
        "title": "토끼와 거북이",
        "genre": "이솝 우화",
        "total_duration": sum(s['duration'] for s in selected_scenes),
        "total_scenes": len(selected_scenes),
        "style": "illustrated storybook style",
        "mood": "educational and inspiring",
        "scenes": selected_scenes,
        "music_suggestion": "Playful music with triumphant ending"
    }

def generate_kongjui_patjui_story(scenes_count: int, scene_duration: float) -> dict:
    """콩쥐팥쥐 스토리"""
    base_scenes = [
        {
            "scene_number": 1,
            "title": "착한 콩쥐",
            "description": "Kind Kongjui working hard, humble girl, traditional Korean clothes, diligent and gentle",
            "korean_description": "착하고 부지런한 콩쥐",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "gentle"
        },
        {
            "scene_number": 2,
            "title": "심술궂은 팥쥐",
            "description": "Mean stepsister Patjui, lazy and jealous, traditional Korean setting, spoiled character",
            "korean_description": "심술궂고 게으른 팥쥐",
            "duration": scene_duration,
            "camera_movement": "pan_right",
            "mood": "mean"
        },
        {
            "scene_number": 3,
            "title": "힘든 노동",
            "description": "Kongjui doing all household chores, hard work, unfair treatment, perseverance despite hardship",
            "korean_description": "모든 집안일을 하는 고된 콩쥐",
            "duration": scene_duration,
            "camera_movement": "pan_around",
            "mood": "hardship"
        },
        {
            "scene_number": 4,
            "title": "동물 친구들의 도움",
            "description": "Magical animals helping Kongjui, birds, frogs, and cows assisting with work, fairy tale magic",
            "korean_description": "콩쥐를 도와주는 동물 친구들",
            "duration": scene_duration,
            "camera_movement": "zoom_out",
            "mood": "magical"
        },
        {
            "scene_number": 5,
            "title": "잔치",
            "description": "Grand traditional Korean festival, beautiful hanbok, celebration atmosphere, colorful decorations",
            "korean_description": "화려한 잔치 장면",
            "duration": scene_duration,
            "camera_movement": "pan_left",
            "mood": "festive"
        },
        {
            "scene_number": 6,
            "title": "신을 잃다",
            "description": "Kongjui losing her shoe while running, dramatic moment, beautiful traditional shoe, Cinderella moment",
            "korean_description": "콩쥐가 잃어버린 신",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "dramatic"
        },
        {
            "scene_number": 7,
            "title": "행복한 결말",
            "description": "Kongjui finding happiness, fairy tale ending, reunion with shoe, beautiful traditional wedding",
            "korean_description": "행복한 결말을 맞이하는 콩쥐",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_out",
            "mood": "happy"
        },
    ]
    
    selected_scenes = base_scenes[:scenes_count]
    
    return {
        "title": "콩쥐팥쥐",
        "genre": "한국 전통 설화",
        "total_duration": sum(s['duration'] for s in selected_scenes),
        "total_scenes": len(selected_scenes),
        "style": "traditional Korean fairy tale style",
        "mood": "magical and heartwarming",
        "scenes": selected_scenes,
        "music_suggestion": "Traditional Korean music with magical elements"
    }

def distribute_scenes_to_acts(total_scenes: int) -> list:
    """
    장면을 5막 구조에 따라 배분
    발단(20%) → 전개(25%) → 위기(20%) → 절정(20%) → 결말(15%)
    """
    if total_scenes <= 5:
        return [1] * total_scenes + [0] * (5 - total_scenes)
    
    distribution = [
        max(1, int(total_scenes * 0.20)),  # 발단
        max(1, int(total_scenes * 0.25)),  # 전개
        max(1, int(total_scenes * 0.20)),  # 위기
        max(1, int(total_scenes * 0.20)),  # 절정
        max(1, int(total_scenes * 0.15))   # 결말
    ]
    
    # 남은 장면 조정
    diff = total_scenes - sum(distribution)
    if diff > 0:
        # 전개에 추가 (가장 유연한 파트)
        distribution[1] += diff
    elif diff < 0:
        # 결말에서 조정
        distribution[4] += diff
    
    return distribution

def get_act_name(act_num: int) -> str:
    """막 이름 반환"""
    act_names = {
        1: "발단",
        2: "전개", 
        3: "위기",
        4: "절정",
        5: "결말"
    }
    return act_names.get(act_num, f"제{act_num}막")

def extract_subject_and_activity(prompt: str) -> tuple:
    """
    프롬프트에서 주체(subject)와 활동(activity)을 추출
    예: "용감한 소방관의 하루" → ("소방관", "하루")
    예: "행복한 제빵사의 아침" → ("제빵사", "아침")
    """
    import re
    
    # 조사 제거 패턴
    prompt_clean = re.sub(r'의|가|을|를|이|은|는|에서|과|와', ' ', prompt)
    
    # 명사 추출 (간단한 패턴)
    words = prompt_clean.split()
    
    # 직업/캐릭터 후보 단어들
    subjects = []
    for word in words:
        # 2글자 이상 명사로 보이는 것
        if len(word) >= 2:
            subjects.append(word)
    
    # 주체는 보통 앞쪽, 활동은 뒤쪽
    subject = subjects[0] if len(subjects) > 0 else prompt
    activity = subjects[-1] if len(subjects) > 1 else ""
    
    return (subject, activity)

def generate_dynamic_actions_with_ollama(prompt: str, act_num: int) -> str:
    """
    🤖 Ollama를 사용하여 주제에 맞는 구체적 행동 자동 생성
    """
    try:
        act_names = {
            1: "Introduction", 2: "Rising Action", 3: "Conflict", 4: "Climax", 5: "Resolution"
        }
        
        user_prompt = f"""Story: "{prompt}"
Act: {act_names.get(act_num, 'Scene')}

Generate a VERY SPECIFIC visual scene description for AI image generation.
Include concrete actions, objects, and visual details.
Keep it under 30 words, in English.

Response format: [specific action description only]"""

        response = requests.post(
            f"{OLLAMA_BASE_URL}/api/generate",
            json={
                "model": "deepseek-r1:1.5b",
                "prompt": user_prompt,
                "stream": False,
                "options": {
                    "temperature": 0.7,
                    "num_predict": 100
                }
            },
            timeout=10
        )
        
        if response.status_code == 200:
            action = response.json().get('response', '').strip()
            logger.info(f"✅ Ollama 행동 생성: {action[:50]}...")
            return action
        return None
    except Exception as e:
        logger.error(f"❌ Ollama 행동 생성 실패: {e}")
        return None

def generate_dynamic_actions_with_ai(prompt: str, act_num: int) -> str:
    """
    🤖 AI를 사용하여 주제에 맞는 구체적 행동 자동 생성
    
    우선순위:
    1. Ollama (로컬, 무료)
    2. OpenAI (클라우드, 유료)
    
    Args:
        prompt: 사용자 입력 스토리 제목 (예: "행복한 제빵사의 아침")
        act_num: 막 번호 (1-5)
    
    Returns:
        구체적 행동 설명 (영어)
    """
    # 1순위: Ollama
    if ollama_available:
        action = generate_dynamic_actions_with_ollama(prompt, act_num)
        if action:
            return action
    
    # 2순위: OpenAI
    if not AI_ENABLED or not openai_client:
        logger.warning("AI 비활성화 - 폴백 사용")
        return None
    
    act_names = {
        1: "발단 (Introduction)",
        2: "전개 (Rising Action)", 
        3: "위기 (Conflict)",
        4: "절정 (Climax)",
        5: "결말 (Resolution)"
    }
    
    act_description = act_names.get(act_num, "장면")
    
    system_prompt = """You are a professional storyboard artist and visual storyteller.
Your job is to create VERY SPECIFIC, VISUAL, and ACTIONABLE scene descriptions for AI image generation.

IMPORTANT RULES:
1. Generate CONCRETE ACTIONS and VISUALS (not abstract concepts)
2. Include DETAILED visual elements (objects, movements, expressions, environment)
3. Use English for AI image generation
4. Be SPECIFIC to the given topic/profession/character
5. Each act should progress the story logically

Example:
Topic: "Happy Baker's Morning"
Act 1 (Introduction): "baker opening bakery door at dawn, turning on lights, putting on white apron and chef hat, checking flour bags and ingredients on wooden shelves"
Act 2 (Rising Action): "baker kneading dough with flour dust in air, mixing ingredients in large bowl, bread rising in warm oven"
Act 3 (Conflict): "oven timer beeping urgently, smoke coming from oven, baker rushing to save burning bread, stressed expression"
Act 4 (Climax): "baker pulling out perfectly golden bread loaves, steam rising, beautiful brown crust, relieved and proud smile"
Act 5 (Resolution): "happy customers buying fresh bread, baker smiling behind counter, warm cozy bakery atmosphere, satisfied day ending"
"""

    user_prompt = f"""Story Title: "{prompt}"
Act: {act_description}

Generate a VERY SPECIFIC visual scene description for this act.
Include concrete actions, objects, and visual details.
Keep it under 30 words, in English, perfect for AI image generation.

Response format: [specific action description only, no explanation]"""

    try:
        response = openai_client.chat.completions.create(
            model="gpt-4o-mini",  # 빠르고 저렴한 모델
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            max_tokens=100,
            temperature=0.7,
            timeout=10  # 10초 타임아웃
        )
        
        action = response.choices[0].message.content.strip()
        logger.info(f"✅ AI 생성 성공: {prompt} (막 {act_num}) → {action[:50]}...")
        return action
        
    except Exception as e:
        logger.error(f"❌ AI 생성 실패: {e}")
        return None

def create_detailed_scene_description(prompt: str, scene_num: int, korean_mood: str, act_num: int) -> str:
    """
    각 막의 특성에 맞는 상세한 장면 설명 생성
    스토리 제목의 핵심 키워드를 추출하여 각 막에 맞는 구체적 행동으로 변환
    
    🎯 확장된 키워드 시스템:
    - 기존 13개 키워드 지원
    - 새로운 직업/활동 자동 감지 (제빵사, 택시기사, 수영선수, 바리스타 등)
    - 일반 패턴 폴백
    """
    # 프롬프트에서 핵심 키워드 추출 및 직업/캐릭터 식별
    prompt_lower = prompt.lower()
    
    # 주체와 활동 추출
    subject, activity = extract_subject_and_activity(prompt)
    
    # 각 막별 확장 행동 템플릿 (기존 13개 + 새로운 20개 = 총 33개 패턴)
    act_templates = {
        1: {  # 발단
            "default": "showing the main character starting their day in their usual environment",
            "keywords": {
                # 기존 13개
                "소방관|firefighter|소방": "firefighter at fire station getting ready, putting on fire gear, checking equipment",
                "우주|space|astronaut": "astronaut preparing for space mission, checking spacecraft systems",
                "기사|knight": "brave knight at castle preparing armor and sword",
                "고양이|cat": "curious cat waking up and stretching in cozy home",
                "의사|doctor": "doctor arriving at hospital, putting on white coat",
                "요리사|chef|cook": "chef entering kitchen, preparing cooking tools",
                "선생님|teacher": "teacher preparing classroom, organizing lesson materials",
                "마법사|wizard|magic": "wizard in mystical library, examining ancient magical books and scrolls",
                "탐험|explorer|adventure": "brave explorer preparing expedition gear, checking map and compass",
                "로봇|robot": "friendly robot powering up, checking systems and circuits",
                "공주|princess": "beautiful princess waking up in royal castle bedroom",
                "해적|pirate": "pirate captain on ship deck, looking through telescope at horizon",
                
                # 🆕 새로운 20개 키워드
                "제빵사|baker|빵": "baker opening bakery at dawn, turning on lights, putting on apron and chef hat, checking flour and ingredients",
                "택시|taxi|운전사|driver": "taxi driver starting morning shift, cleaning car, checking GPS and meter",
                "수영|swimmer|선수|athlete": "swimmer arriving at pool, stretching muscles, putting on swim cap and goggles",
                "바리스타|barista|커피": "barista opening coffee shop, turning on espresso machine, arranging cups and beans",
                "경찰|police|officer": "police officer at station, putting on uniform and badge, checking equipment",
                "간호사|nurse": "nurse arriving at hospital, changing into scrubs, checking patient charts",
                "조종사|pilot": "pilot entering cockpit, checking flight instruments, preparing for takeoff",
                "농부|farmer": "farmer waking up at sunrise, putting on work clothes, heading to fields",
                "화가|painter|artist": "artist in studio, setting up canvas and paints, preparing brushes",
                "음악가|musician": "musician in practice room, tuning instrument, warming up",
                "건축가|architect": "architect at desk, reviewing blueprints, preparing design tools",
                "사진작가|photographer": "photographer checking camera equipment, adjusting lenses, preparing for shoot",
                "과학자|scientist": "scientist in lab, putting on lab coat, checking microscope and samples",
                "변호사|lawyer": "lawyer at office, reviewing case files, preparing briefcase",
                "소설가|writer|작가": "writer at desk, opening laptop, staring at blank page with coffee nearby",
                "댄서|dancer": "dancer in studio, stretching at barre, warming up muscles",
                "배우|actor": "actor reading script, practicing lines in dressing room mirror",
                "정원사|gardener": "gardener entering garden, putting on gloves, checking plants and tools",
                "기자|journalist|reporter": "journalist at newsroom, checking notes, preparing recording equipment",
                "승무원|flight attendant": "flight attendant at airport, checking uniform, preparing for boarding"
            }
        },
        2: {  # 전개
            "default": "character beginning their main activity or challenge",
            "keywords": {
                # 기존 13개
                "소방관|firefighter|소방": "fire alarm rings, firefighters sliding down pole, rushing to fire truck",
                "우주|space|astronaut": "rocket launch, astronaut floating in space station",
                "기사|knight": "knight riding horse towards adventure, encountering first obstacle",
                "고양이|cat": "cat exploring outside, discovering new interesting things",
                "의사|doctor": "doctor examining patients, checking medical charts",
                "요리사|chef|cook": "chef cooking actively, flames and steam rising from pans",
                "선생님|teacher": "teacher explaining lesson to students, writing on blackboard",
                "마법사|wizard|magic": "wizard casting spell, magical energy glowing from hands, mysterious ancient book",
                "탐험|explorer|adventure": "explorer discovering hidden cave entrance, venturing into unknown territory",
                "로봇|robot": "robot beginning mission, moving through futuristic city streets",
                "공주|princess": "princess leaving castle, starting royal journey, guards escorting",
                "해적|pirate": "pirate ship sailing stormy seas, crew working on deck",
                
                # 🆕 새로운 20개
                "제빵사|baker|빵": "baker kneading dough, flour dust in air, bread dough rising in warm oven",
                "택시|taxi|운전사|driver": "taxi driving through city streets, picking up first passenger, navigating traffic",
                "수영|swimmer|선수|athlete": "swimmer diving into pool, powerful strokes cutting through water, racing against time",
                "바리스타|barista|커피": "barista making espresso, milk steam rising, artistic latte art creation",
                "경찰|police|officer": "police officer on patrol, checking neighborhood, responding to call",
                "간호사|nurse": "nurse checking vital signs, administering medication, caring for patients",
                "조종사|pilot": "pilot taking off, aircraft ascending through clouds, navigating flight path",
                "농부|farmer": "farmer planting seeds, driving tractor through fields, tending crops",
                "화가|painter|artist": "artist painting with bold strokes, colors mixing on palette, creating masterpiece",
                "음악가|musician": "musician performing, fingers moving rapidly on instrument, music flowing",
                "건축가|architect": "architect sketching designs, using drafting tools, creating 3D models",
                "사진작가|photographer": "photographer capturing moments, adjusting camera settings, finding perfect angle",
                "과학자|scientist": "scientist conducting experiment, mixing chemicals, observing reactions",
                "변호사|lawyer": "lawyer in courtroom, presenting case, questioning witness",
                "소설가|writer|작가": "writer typing rapidly, ideas flowing, words appearing on screen",
                "댄서|dancer": "dancer performing choreography, graceful movements, expressing through motion",
                "배우|actor": "actor on stage, delivering lines, immersed in character",
                "정원사|gardener": "gardener planting flowers, watering plants, pruning bushes",
                "기자|journalist|reporter": "journalist interviewing subject, taking notes, recording statements",
                "승무원|flight attendant": "flight attendant serving passengers, demonstrating safety, helping travelers"
            }
        },
        3: {  # 위기
            "default": "facing major challenge or obstacle",
            "keywords": {
                # 기존 13개
                "소방관|firefighter|소방": "arriving at burning building, intense flames and smoke everywhere",
                "우주|space|astronaut": "spacecraft malfunction, warning lights flashing, crisis moment",
                "기사|knight": "knight fighting dangerous dragon or monster",
                "고양이|cat": "cat stuck in dangerous situation, looking worried",
                "의사|doctor": "emergency surgery, doctor focused intensely on critical patient",
                "요리사|chef|cook": "kitchen crisis, multiple dishes burning, chef stressed",
                "선생님|teacher": "classroom chaos, students causing trouble, teacher worried",
                "마법사|wizard|magic": "wizard facing dark magic attack, magical battle with evil sorcerer, intense spell combat",
                "탐험|explorer|adventure": "explorer trapped by collapsing ruins, dangerous situation, rocks falling",
                "로봇|robot": "robot malfunction, sparks flying, system error warnings",
                "공주|princess": "princess captured by villain, locked in tower, desperate situation",
                "해적|pirate": "pirate ship under attack, enemy ships firing cannons, battle at sea",
                
                # 🆕 새로운 20개
                "제빵사|baker|빵": "oven timer beeping urgently, bread burning, smoke alarm, baker rushing in panic",
                "택시|taxi|운전사|driver": "stuck in terrible traffic jam, passenger getting angry, time running out",
                "수영|swimmer|선수|athlete": "swimmer losing breath underwater, cramping muscles, struggling to continue",
                "바리스타|barista|커피": "espresso machine breaking down, long line of impatient customers, stress mounting",
                "경찰|police|officer": "high-speed chase, dangerous criminal fleeing, tense pursuit situation",
                "간호사|nurse": "patient's condition worsening, emergency codes, medical crisis unfolding",
                "조종사|pilot": "engine failure mid-flight, warning alarms, emergency protocols activated",
                "농부|farmer": "sudden storm threatening crops, heavy rain and wind, harvest in danger",
                "화가|painter|artist": "paint spilling on canvas, ruining work, deadline approaching, artistic crisis",
                "음악가|musician": "instrument string breaking during performance, missed notes, concert crisis",
                "건축가|architect": "structural problem discovered, building plans rejected, major revision needed",
                "사진작가|photographer": "camera malfunctioning at crucial moment, memory card full, missing the shot",
                "과학자|scientist": "experiment going wrong, chemical reaction out of control, lab emergency",
                "변호사|lawyer": "key witness contradicting case, opposing lawyer dominating, trial turning bad",
                "소설가|writer|작가": "writer's block, deadline tomorrow, story falling apart, creative crisis",
                "댄서|dancer": "dancer injuring ankle mid-performance, pain shooting through leg, critical moment",
                "배우|actor": "forgetting lines on stage, audience watching, career-defining moment failing",
                "정원사|gardener": "plants dying from disease, garden wilting, all work threatened",
                "기자|journalist|reporter": "source backing out, story falling apart, publication deadline approaching",
                "승무원|flight attendant": "severe turbulence, passengers panicking, emergency situation onboard"
            }
        },
        4: {  # 절정
            "default": "peak action moment, climactic scene",
            "keywords": {
                # 기존 13개
                "소방관|firefighter|소방": "firefighter heroically rescuing person from burning building, carrying victim through flames",
                "우주|space|astronaut": "astronaut making daring spacewalk repair, Earth in background",
                "기사|knight": "knight delivering final blow to enemy, epic battle climax",
                "고양이|cat": "cat making incredible leap or escape, action peak",
                "의사|doctor": "doctor successfully completing difficult surgery, life saved",
                "요리사|chef|cook": "chef presenting masterpiece dish, judges amazed",
                "선생님|teacher": "students finally understanding, breakthrough teaching moment",
                "마법사|wizard|magic": "wizard unleashing ultimate spell, massive magical explosion, defeating evil with powerful magic",
                "탐험|explorer|adventure": "explorer finding legendary treasure, triumphant discovery moment, golden artifacts",
                "로봇|robot": "robot saving the day with incredible strength, heroic robot action",
                "공주|princess": "princess bravely escaping captivity, showing courage and determination",
                "해적|pirate": "pirate captain winning epic sword duel, claiming victory",
                
                # 🆕 새로운 20개
                "제빵사|baker|빵": "baker pulling out perfect golden bread loaves, steam rising, beautiful crust, triumph",
                "택시|taxi|운전사|driver": "taxi speeding through shortcut, arriving just in time, passenger relieved and grateful",
                "수영|swimmer|선수|athlete": "swimmer touching wall first, winning gold medal, arms raised in victory",
                "바리스타|barista|커피": "barista creating perfect latte art, customer amazed, winning coffee competition",
                "경찰|police|officer": "police officer catching criminal, handcuffs clicking, justice served",
                "간호사|nurse": "patient's vitals stabilizing, crisis averted, successful emergency response",
                "조종사|pilot": "pilot executing perfect emergency landing, passengers safe, heroic aviation save",
                "농부|farmer": "storm passing, crops saved, rainbow appearing over fields, harvest secured",
                "화가|painter|artist": "artist unveiling completed masterpiece, gallery crowd applauding, artistic triumph",
                "음악가|musician": "musician's flawless improvisation saving performance, standing ovation, musical genius",
                "건축가|architect": "architect's innovative solution approved, building design perfected, professional victory",
                "사진작가|photographer": "photographer capturing once-in-lifetime perfect shot, award-winning moment",
                "과학자|scientist": "breakthrough discovery made, experiment succeeding brilliantly, scientific triumph",
                "변호사|lawyer": "lawyer presenting decisive evidence, jury convinced, winning the case",
                "소설가|writer|작가": "writer typing final perfect sentence, story complete, manuscript finished",
                "댄서|dancer": "dancer executing perfect final leap despite injury, audience gasping, triumph over pain",
                "배우|actor": "actor delivering powerful emotional climax, audience in tears, performance peak",
                "정원사|gardener": "garden blooming magnificently, flowers in full color, gardening triumph",
                "기자|journalist|reporter": "journalist publishing exposé, truth revealed, journalistic victory",
                "승무원|flight attendant": "flight attendant successfully calming all passengers, safe landing, crisis resolved"
            }
        },
        5: {  # 결말
            "default": "peaceful resolution, character satisfied",
            "keywords": {
                # 기존 13개
                "소방관|firefighter|소방": "tired but proud firefighter at station, fire extinguished, hero's rest",
                "우주|space|astronaut": "astronaut safely back on Earth, mission accomplished",
                "기사|knight": "victorious knight returning home, peace restored",
                "고양이|cat": "happy cat back home, sleeping peacefully after adventure",
                "의사|doctor": "doctor smiling with recovered patient, successful healing",
                "요리사|chef|cook": "chef receiving praise, satisfied with delicious meal",
                "선생님|teacher": "teacher happy with student success, rewarding teaching",
                "마법사|wizard|magic": "wise wizard back in peaceful library, organizing magical books, satisfied smile",
                "탐험|explorer|adventure": "exhausted but happy explorer returning home with treasure, adventure complete",
                "로봇|robot": "robot resting after mission complete, happy robot expression",
                "공주|princess": "princess living happily in castle, peace and harmony restored",
                "해적|pirate": "pirate crew celebrating with treasure, joyful party on ship deck",
                
                # 🆕 새로운 20개
                "제빵사|baker|빵": "happy customers enjoying fresh bread, baker smiling behind counter, successful day ending",
                "택시|taxi|운전사|driver": "taxi driver finishing shift, satisfied smile, heading home after good day",
                "수영|swimmer|선수|athlete": "swimmer on podium with medal, national anthem playing, dreams achieved",
                "바리스타|barista|커피": "barista closing shop contentedly, satisfied regulars waving goodbye, fulfilling day",
                "경찰|police|officer": "police officer at station, paperwork done, community safe and peaceful",
                "간호사|nurse": "nurse seeing recovered patient smile, rewarding moment, successful care",
                "조종사|pilot": "pilot leaving cockpit after safe flight, passengers thanking, job well done",
                "농부|farmer": "farmer surveying bountiful harvest, sunset over fields, year's work rewarded",
                "화가|painter|artist": "artist in peaceful studio, paintings hanging in gallery, artistic satisfaction",
                "음악가|musician": "musician at home, relaxing with instrument, memories of performance",
                "건축가|architect": "architect seeing building completed, design realized, professional pride",
                "사진작가|photographer": "photographer reviewing day's perfect shots, exhibition planning, fulfilled",
                "과학자|scientist": "scientist publishing findings, colleagues congratulating, research success",
                "변호사|lawyer": "lawyer at office, client grateful, justice served, case closed",
                "소설가|writer|작가": "writer sending completed manuscript to publisher, relieved and hopeful",
                "댄서|dancer": "dancer resting peacefully, injury healing, next performance awaiting",
                "배우|actor": "actor receiving flowers backstage, critics praising, performance success",
                "정원사|gardener": "gardener sitting peacefully in beautiful garden, life's work blooming",
                "기자|journalist|reporter": "journalist receiving journalism award, story making impact, truth prevailing",
                "승무원|flight attendant": "flight attendant at home relaxing, another safe flight completed, day well done"
            }
        }
    }
    
    # 🤖 1순위: AI 기반 동적 행동 생성 시도
    specific_action = None
    if AI_ENABLED:
        specific_action = generate_dynamic_actions_with_ai(prompt, act_num)
    
    # 🔧 2순위: AI 실패 시 키워드 매칭 폴백
    if not specific_action:
        act_template = act_templates.get(act_num, act_templates[1])
        specific_action = act_template["default"]
        
        # 키워드 매칭 - 더 구체적인 패턴 우선 (긴 패턴부터 체크)
        matched = False
        sorted_patterns = sorted(act_template["keywords"].items(), 
                                key=lambda x: len(x[0]), reverse=True)
        
        for pattern, action in sorted_patterns:
            keywords = pattern.split("|")
            # 정확한 매칭을 위해 공백이나 경계 체크
            for keyword in keywords:
                if keyword in prompt_lower:
                    # "택시 기사" vs "기사" 구분
                    if keyword == "기사" and ("택시" in prompt_lower or "운전" in prompt_lower):
                        continue  # "택시 기사"는 taxi 패턴으로 처리
                    specific_action = action
                    logger.info(f"✅ 키워드 매칭 성공: {prompt} → {pattern}")
                    matched = True
                    break
            if matched:
                break
    
    # 최종 프롬프트 구성 - 제목보다 구체적 행동을 먼저!
    return (
        f"{prompt}, scene {scene_num}: {specific_action}. "
        f"{korean_mood} atmosphere. "
        f"This is a scene from the story '{prompt}'. "
        f"Highly detailed, cinematic lighting, 1080x1920 vertical format, "
        f"professional photography, dramatic storytelling, 4K quality, masterpiece"
    )

def generate_story_from_analysis(analysis: dict, scenes_count: int, scene_duration: float) -> dict:
    """
    분석된 장문 스토리를 기반으로 5막 구조 쇼츠 생성
    
    Args:
        analysis: analyze_and_summarize_story()의 결과
        scenes_count: 총 장면 수
        scene_duration: 장면당 시간
    
    Returns:
        완성된 스토리 객체
    """
    title = analysis.get('title', '멋진 이야기')
    five_acts = analysis.get('five_acts', {})
    key_events = analysis.get('key_events', [])
    
    # 5막 구조 매핑
    act_descriptions = [
        five_acts.get('exposition', '이야기의 시작'),
        five_acts.get('rising_action', '사건이 전개됨'),
        five_acts.get('conflict', '위기가 찾아옴'),
        five_acts.get('climax', '결정적 순간'),
        five_acts.get('resolution', '이야기의 마무리')
    ]
    
    # 장면 배분
    act_distribution = distribute_scenes_to_acts(scenes_count)
    
    scenes = []
    scene_idx = 0
    
    # 기존 5막 스토리 구조 사용 (나레이션, 무드 등)
    story_structure = _get_default_story_structure()
    
    for act_num, (act_data, num_scenes_in_act) in enumerate(zip(story_structure, act_distribution)):
        for scene_in_act in range(num_scenes_in_act):
            # 나레이션 선택
            if scene_in_act < len(act_data["narrations"]):
                narration = act_data["narrations"][scene_in_act]
            else:
                base_narration = act_data["narrations"][scene_in_act % len(act_data["narrations"])]
                narration = f"{base_narration} (파트 {scene_in_act + 1})"
            
            mood_idx = scene_in_act % len(act_data["moods"])
            camera_idx = scene_in_act % len(act_data["cameras"])
            
            mood = act_data["moods"][mood_idx]
            camera_movement = act_data["cameras"][camera_idx]
            korean_mood = act_data["korean_moods"][mood_idx]
            
            # 영어 프롬프트 생성 - 분석된 내용 활용
            act_context = act_descriptions[act_num]
            description = (
                f"{title}, scene {scene_idx + 1}: {act_context}. "
                f"{korean_mood} atmosphere. "
                f"Visual storytelling, cinematic lighting, 1080x1920 vertical format, "
                f"dramatic composition, 4K quality, masterpiece"
            )
            
            # 한국어 설명
            korean_desc = f"{title} 이야기 중 {get_act_name(act_num + 1)}의 {korean_mood} 장면"
            
            scene = {
                "scene_number": scene_idx + 1,
                "title": f"{get_act_name(act_num + 1)} - 장면 {scene_in_act + 1}",
                "description": description,
                "korean_description": korean_desc,
                "narration": narration,
                "duration": scene_duration,
                "camera_movement": camera_movement,
                "mood": mood
            }
            scenes.append(scene)
            scene_idx += 1
    
    return {
        "title": title,
        "genre": "AI 분석 스토리",
        "total_duration": sum(s['duration'] for s in scenes),
        "total_scenes": len(scenes),
        "style": "cinematic storytelling based on analyzed narrative",
        "mood": "engaging and dramatic",
        "scenes": scenes,
        "music_suggestion": "Emotional cinematic music matching the story arc",
        "original_analysis": analysis  # 원본 분석 정보 포함
    }

def _get_default_story_structure() -> list:
    """
    기본 5막 스토리 구조 (나레이션, 무드, 카메라) 반환
    """
    return [
        # 1막: 발단
        {
            "narrations": [
                "여러분, 이건 정말 믿기 힘든 이야기인데 한번 들어보세요.",
                "이 이야기는 아주 평범한 하루에서 시작됐어요.",
                "오늘 들려드릴 이야기는 여러분을 완전히 사로잡을 거예요.",
                "모든 건 아무도 예상하지 못한 순간에 시작됐죠.",
                "평범해 보이는 이 장면 뒤에 숨겨진 비밀이 있어요.",
            ],
            "moods": ["mysterious", "curious", "intriguing", "calm", "wondering"],
            "cameras": ["slow_zoom_in", "pan_right", "dolly_in", "crane_down", "static_wide"],
            "korean_moods": ["신비로운", "호기심 가득한", "흥미진진한", "고요한", "궁금증 유발하는"]
        },
        # 2막: 전개
        {
            "narrations": [
                "처음에는 평범해 보였지만, 뭔가 이상한 느낌이 들기 시작했어요.",
                "그런데 여기서 예상치 못한 일이 벌어지기 시작했죠.",
                "상황이 점점 더 흥미로워지고 있었어요.",
            ],
            "moods": ["revealing", "intriguing", "developing"],
            "cameras": ["pan_left", "zoom_in", "dolly_forward"],
            "korean_moods": ["서서히 드러나는", "흥미진진한", "발전하는"]
        },
        # 3막: 위기
        {
            "narrations": [
                "이제부터가 진짜 중요한 순간인데, 과연 어떻게 될까요?",
                "긴장감이 점점 고조되고, 모두가 숨죽이고 지켜보고 있었어요.",
            ],
            "moods": ["intense", "suspenseful"],
            "cameras": ["shake", "quick_zoom"],
            "korean_moods": ["긴장감 넘치는", "숨막히는"]
        },
        # 4막: 절정
        {
            "narrations": [
                "그리고 드디어, 결정적인 순간이 찾아왔어요!",
                "바로 이 순간, 모든 게 완전히 바뀌어버렸죠.",
            ],
            "moods": ["shocking", "dramatic"],
            "cameras": ["tilt_up", "dramatic_zoom"],
            "korean_moods": ["충격적인", "극적인"]
        },
        # 5막: 결말
        {
            "narrations": [
                "그렇게 이야기는 마무리되었고, 모두가 깨달음을 얻었어요.",
                "이 이야기의 진짜 의미는 여러분이 직접 느껴보시면 알 수 있을 거예요.",
            ],
            "moods": ["reflective", "peaceful"],
            "cameras": ["zoom_out", "slow_zoom_out"],
            "korean_moods": ["여운이 남는", "평화로운"]
        }
    ]

def generate_custom_story(user_input: str, scenes_count: int, scene_duration: float) -> dict:
    """커스텀 스토리 생성 - 구어체 궁금증 유발형 (5막 구조) - 완전 고유 나레이션 (중복 제로)"""
    
    # ✅ 혁신적 해결책: 전체 나레이션을 하나의 글로벌 풀로 관리
    # 5막 구조별로 15개씩 = 총 75개 고유 나레이션 (7씬 완벽 커버)
    GLOBAL_NARRATION_POOL = [
        # === 1막: 발단 (Exposition) - 호기심 유발 (15개) ===
        "여러분, 이건 정말 믿기 힘든 이야기인데 한번 들어보세요.",
        "이 이야기는 아주 평범한 하루에서 시작됐어요.",
        "오늘 들려드릴 이야기는 여러분을 완전히 사로잡을 거예요.",
        "모든 건 아무도 예상하지 못한 순간에 시작됐죠.",
        "평범해 보이는 이 장면 뒤에 숨겨진 비밀이 있어요.",
        "자, 이제 정말 놀라운 이야기의 시작입니다.",
        "아주 오래전부터 전해 내려오는 이야기가 있어요.",
        "이 순간이 모든 것을 바꿔놓을 줄은 아무도 몰랐어요.",
        "처음엔 아무것도 특별해 보이지 않았죠.",
        "이 이야기의 주인공은 평범한 일상을 보내고 있었어요.",
        "누구나 한 번쯤은 꿈꿔봤을 법한 그런 순간이에요.",
        "이 장면을 지금 꼭 기억해 두세요. 나중에 깜짝 놀라실 거예요.",
        "세상은 넓고, 이야기는 무궁무진하지만, 이것만큼은 특별해요.",
        "아침 햇살이 비추는 조용한 순간, 모든 것이 시작되었어요.",
        "평온한 일상 속에 감춰진 놀라운 비밀이 있었죠.",
        
        # === 2막: 전개 (Rising Action) - 상황 발전 (15개) ===
        "처음에는 평범해 보였지만, 뭔가 이상한 느낌이 들기 시작했어요.",
        "그런데 여기서 예상치 못한 일이 벌어지기 시작했죠.",
        "상황이 점점 더 흥미로워지고 있었어요.",
        "모든 게 계획대로 흘러가는 것처럼 보였지만 사실은 아니었어요.",
        "이때부터 이야기는 완전히 다른 방향으로 흘러가기 시작했죠.",
        "주인공은 아직 자신에게 무슨 일이 일어날지 몰랐어요.",
        "작은 변화들이 하나씩 나타나기 시작했어요.",
        "평범했던 하루가 특별한 모험으로 바뀌고 있었죠.",
        "이 순간부터 모든 것이 달라지기 시작했어요.",
        "아무도 예상하지 못한 전개가 펼쳐지고 있었어요.",
        "뭔가 심상치 않은 분위기가 감돌기 시작했어요.",
        "한 걸음 한 걸음, 운명의 갈림길로 다가가고 있었죠.",
        "이제 돌이킬 수 없는 선택을 해야 하는 시간이 다가왔어요.",
        "주변 사람들도 뭔가 이상하다는 걸 느끼기 시작했죠.",
        "평화로운 일상은 서서히 균열을 보이기 시작했어요.",
        
        # === 3막: 위기 (Conflict) - 긴장감 고조 (15개) ===
        "이제부터가 진짜 중요한 순간인데, 과연 어떻게 될까요?",
        "긴장감이 점점 고조되고, 모두가 숨죽이고 지켜보고 있었어요.",
        "예상치 못한 장애물이 앞을 가로막았어요.",
        "이대로는 절대 안 될 것 같은 위기의 순간이었죠.",
        "모든 게 무너질 것만 같은 아슬아슬한 순간이에요.",
        "과연 이 난관을 어떻게 헤쳐나갈 수 있을까요?",
        "상황은 점점 더 복잡하고 어려워지고 있었어요.",
        "이제 선택의 순간이 다가오고 있었죠.",
        "모두가 불가능하다고 생각하는 그 순간이에요.",
        "여기서 포기하면 모든 게 끝나버릴 거예요.",
        "심장이 쿵쾅거리는 소리가 들릴 정도로 긴박한 순간이었어요.",
        "시간이 멈춘 듯한, 숨 막히는 찰나의 순간이죠.",
        "앞으로 나아갈지, 물러설지 결정해야 하는 갈림길이에요.",
        "한 치 앞도 보이지 않는 캄캄한 터널 같은 순간이었어요.",
        "이 위기를 넘기지 못하면 모든 게 물거품이 돼버려요.",
        
        # === 4막: 절정 (Climax) - 결정적 순간 (15개) ===
        "그리고 드디어, 결정적인 순간이 찾아왔어요!",
        "바로 이 순간, 모든 게 완전히 바뀌어버렸죠.",
        "상상도 못 했던 일이 눈앞에서 펼쳐지고 있었어요.",
        "이게 바로 운명을 가르는 결정적인 한 순간이에요.",
        "모든 것이 이 한 번의 선택으로 결정되는 순간이죠.",
        "세상이 멈춘 것 같은 그 짧은 순간이었어요.",
        "지금까지의 모든 것이 이 순간을 위한 거였어요.",
        "믿을 수 없는 반전이 기다리고 있었죠.",
        "아무도 예상하지 못한 놀라운 결과가 나타났어요.",
        "바로 그 순간, 기적이 일어났어요!",
        "모든 사람들이 숨을 죽이고 지켜보는 클라이막스의 순간!",
        "이 한 순간이 영원처럼 느껴졌어요.",
        "운명의 톱니바퀴가 돌아가기 시작했죠.",
        "드디어 진실이 밝혀지는 충격적인 순간이에요!",
        "모든 의문이 풀리고, 퍼즐이 완성되는 순간이었어요.",
        
        # === 5막: 결말 (Resolution) - 마무리와 여운 (15개) ===
        "그렇게 이야기는 마무리되었고, 모두가 깨달음을 얻었어요.",
        "이 이야기의 진짜 의미는 여러분이 직접 느껴보시면 알 수 있을 거예요.",
        "모든 것이 제자리를 찾아가고 평화가 찾아왔어요.",
        "이제 모든 게 이해가 되기 시작했죠.",
        "결국 진실은 언제나 빛을 발하게 되어 있어요.",
        "이렇게 또 하나의 이야기가 끝이 났어요.",
        "그리고 그들은 새로운 시작을 맞이하게 됐어요.",
        "이 경험을 통해 얻은 교훈은 평생 잊지 못할 거예요.",
        "마지막 장면은 새로운 희망으로 가득했어요.",
        "이야기는 끝났지만, 그 의미는 영원히 남을 거예요.",
        "결말을 맞이하면서, 모두가 미소 짓게 되었죠.",
        "이 이야기가 끝나는 순간, 새로운 이야기가 시작돼요.",
        "마지막까지 놀라움이 가득한 이야기였어요.",
        "이렇게 모든 것이 원래 있어야 할 자리로 돌아갔어요.",
        "긴 여정이 끝나고, 평온한 일상이 다시 찾아왔어요."
    ]
    
    # 5막별 mood/camera 설정 (각 15개씩)
    ACT_SETTINGS = [
        {
            "moods": ["mysterious", "curious", "intriguing", "calm", "wondering", 
                      "enchanting", "discovering", "awakening", "gentle", "serene",
                      "inviting", "anticipating", "fresh", "beginning", "hopeful"],
            "cameras": ["slow_zoom_in", "pan_right", "dolly_in", "crane_down", "static_wide",
                        "orbit_left", "gentle_push", "wide_establishing", "soft_focus", "slow_reveal",
                        "tracking_left", "dolly_right", "crane_up_slow", "pan_left_gentle", "zoom_in_subtle"],
            "korean_moods": ["신비로운", "호기심 가득한", "흥미진진한", "고요한", "궁금증 유발하는",
                            "매혹적인", "발견하는", "깨어나는", "부드러운", "평온한",
                            "초대하는", "기대하는", "신선한", "시작하는", "희망찬"]
        },
        {
            "moods": ["revealing", "intriguing", "developing", "surprising", "transforming",
                      "evolving", "unfolding", "shifting", "changing", "progressing",
                      "advancing", "intensifying", "building", "emerging", "growing"],
            "cameras": ["pan_left", "zoom_in", "dolly_forward", "orbit", "tracking",
                        "push_in", "crane_right", "tilt_down", "dolly_left", "pan_right_smooth",
                        "zoom_in_steady", "orbit_right", "tracking_forward", "crane_down_slow", "dolly_in_medium"],
            "korean_moods": ["서서히 드러나는", "흥미진진한", "발전하는", "놀라운", "변화하는",
                            "진화하는", "펼쳐지는", "이동하는", "바뀌는", "진행되는",
                            "전진하는", "강화되는", "쌓이는", "출현하는", "성장하는"]
        },
        {
            "moods": ["intense", "suspenseful", "challenging", "critical", "tense",
                      "gripping", "thrilling", "precarious", "urgent", "desperate",
                      "perilous", "daunting", "nerve-wracking", "harrowing", "pressing"],
            "cameras": ["shake", "quick_zoom", "dutch_angle", "handheld", "tight_close",
                        "crash_zoom", "rapid_pan", "shaky_cam", "close_tracking", "erratic_move",
                        "quick_tilt", "jerky_zoom", "unstable_shot", "fast_dolly", "chaotic_orbit"],
            "korean_moods": ["긴장감 넘치는", "숨막히는", "도전적인", "결정적인", "팽팽한",
                            "사로잡는", "짜릿한", "아슬아슬한", "긴급한", "필사적인",
                            "위태로운", "벅찬", "조마조마한", "고통스러운", "압박적인"]
        },
        {
            "moods": ["shocking", "dramatic", "explosive", "pivotal", "epic",
                      "climactic", "breathtaking", "stunning", "overwhelming", "powerful",
                      "monumental", "spectacular", "astonishing", "electrifying", "thunderous"],
            "cameras": ["tilt_up", "dramatic_zoom", "360_spin", "crash_zoom", "aerial_rise",
                        "rapid_tilt", "explosive_zoom", "spinning_crane", "power_zoom", "vertical_crane",
                        "fast_orbit", "dynamic_tilt", "surge_forward", "spiral_up", "rocket_zoom"],
            "korean_moods": ["충격적인", "극적인", "폭발적인", "전환점의", "장대한",
                            "절정의", "숨막히는", "놀라운", "압도적인", "강력한",
                            "거대한", "장관을 이루는", "경이로운", "짜릿한", "천둥같은"]
        },
        {
            "moods": ["reflective", "peaceful", "hopeful", "enlightening", "satisfying",
                      "serene", "tranquil", "harmonious", "uplifting", "triumphant",
                      "content", "joyful", "fulfilled", "grateful", "nostalgic"],
            "cameras": ["zoom_out", "slow_zoom_out", "crane_up", "pull_back", "wide_establishing",
                        "gentle_crane", "smooth_retreat", "rising_crane", "slow_pull", "wide_reveal",
                        "ascending_shot", "gradual_zoom_out", "soft_crane_up", "gentle_rise", "panoramic_out"],
            "korean_moods": ["여운이 남는", "평화로운", "희망찬", "깨달음의", "만족스러운",
                            "고요한", "평온한", "조화로운", "고무적인", "승리의",
                            "만족한", "기쁜", "충족된", "감사하는", "그리운"]
        }
    ]
    
    # 5막 구조에 따라 장면 배분
    scenes = []
    act_distribution = distribute_scenes_to_acts(scenes_count)
    
    scene_idx = 0
    narration_idx = 0  # 글로벌 나레이션 인덱스
    
    for act_num, num_scenes_in_act in enumerate(act_distribution):
        for scene_in_act in range(num_scenes_in_act):
            # 해당 막의 설정에서 mood/camera 선택 (먼저 필요)
            act_settings = ACT_SETTINGS[act_num]
            setting_idx = scene_in_act % len(act_settings["moods"])
            
            mood = act_settings["moods"][setting_idx]
            camera_movement = act_settings["cameras"][setting_idx]
            korean_mood = act_settings["korean_moods"][setting_idx]
            act_name = get_act_name(act_num + 1)
            
            # 🆕 AI 나레이션 생성 시도 (우선순위 1)
            narration = None
            if NARRATION_GEN_AVAILABLE and narration_gen:
                try:
                    narration = narration_gen.generate_narration(
                        scene_number=scene_idx + 1,
                        act_name=act_name,
                        korean_mood=korean_mood,
                        scene_title=f"{user_input}의 {act_name}",
                        user_input=user_input,
                        style="curious"  # 기본 스타일
                    )
                    if narration:
                        logger.info(f"✅ AI 나레이션 생성: Scene {scene_idx + 1}")
                except Exception as e:
                    logger.warning(f"⚠️ AI 나레이션 생성 실패 (Scene {scene_idx + 1}): {e}")
            
            # 폴백: 글로벌 풀에서 가져오기 (AI 실패 시)
            if not narration:
                if narration_idx < len(GLOBAL_NARRATION_POOL):
                    narration = GLOBAL_NARRATION_POOL[narration_idx]
                    narration_idx += 1
                else:
                    # 75개를 초과하면 (극히 드문 케이스) 조합 생성
                    base_idx = (narration_idx - len(GLOBAL_NARRATION_POOL)) % len(GLOBAL_NARRATION_POOL)
                    narration = f"{GLOBAL_NARRATION_POOL[base_idx]} [파트 {narration_idx - len(GLOBAL_NARRATION_POOL) + 1}]"
                    narration_idx += 1
            

            
            # 영어 프롬프트 (AI 이미지 생성용) - 더 구체적으로
            description = create_detailed_scene_description(
                user_input, scene_idx + 1, korean_mood, act_num + 1
            )
            
            # 한국어 설명도 더 구체적으로
            korean_desc = f"{user_input} 이야기 중 {get_act_name(act_num + 1)}의 {korean_mood} 장면"
            
            scene = {
                "scene_number": scene_idx + 1,
                "title": f"{get_act_name(act_num + 1)} - 장면 {scene_in_act + 1}",
                "description": description,
                "korean_description": korean_desc,
                "narration": narration,
                "duration": scene_duration,
                "camera_movement": camera_movement,
                "mood": mood
            }
            scenes.append(scene)
            scene_idx += 1
    
    return {
        "title": user_input,
        "genre": "사용자 정의 스토리",
        "total_duration": sum(s['duration'] for s in scenes),
        "total_scenes": len(scenes),
        "style": "cinematic storytelling with suspense",
        "mood": "engaging and curious",
        "scenes": scenes,
        "music_suggestion": "Epic cinematic music with emotional build-up"
    }

@app.route('/health', methods=['GET'])
def health():
    """헬스 체크"""
    return jsonify({
        'status': 'healthy',
        'service': 'story-generator',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/generate-story', methods=['POST'])
def generate_story():
    """스토리 스크립트 생성 API (🆕 AI 나레이션 자동 생성 통합)"""
    try:
        data = request.json
        user_input = data.get('prompt', '선녀와 나무꾼')
        duration = data.get('duration', 30)
        
        logger.info(f"Generating story for: {user_input} ({duration}s)")
        
        # 🆕 장르 감지 (선택 사항)
        genre_info = None
        if GENRE_DETECTION_AVAILABLE and genre_detector:
            try:
                genre_info = genre_detector.detect_genre(user_input)
                if genre_info:
                    logger.info(f"🎭 장르 감지: {genre_info.get('genre', 'Unknown')}")
            except Exception as e:
                logger.warning(f"장르 감지 실패: {e}")
        
        # 스토리 생성 (AI 나레이션 자동 생성 포함)
        story = generate_story_script(user_input, duration)
        
        # 장르 정보 추가
        if genre_info:
            story['detected_genre'] = genre_info.get('genre')
            story['genre_structure'] = genre_info.get('structure')
        
        return jsonify({
            'success': True,
            'story': story
        })
        
    except Exception as e:
        logger.error(f"Error in generate_story: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/translate-story', methods=['POST'])
def translate_story():
    """다국어 번역 API (🆕 5개 언어 지원)"""
    try:
        data = request.json
        narrations = data.get('narrations', [])
        target_lang = data.get('target_lang', 'en')  # en, ja, zh, es
        
        if not TRANSLATOR_AVAILABLE or not translator:
            return jsonify({
                'success': False,
                'error': '번역 시스템이 활성화되지 않았습니다.'
            }), 503
        
        logger.info(f"Translating {len(narrations)} narrations to {target_lang}")
        
        # 나레이션 번역
        translated_narrations = []
        for i, narration in enumerate(narrations, 1):
            try:
                translated = translator.translate_narration(narration, target_lang)
                if translated and translated != narration:
                    translated_narrations.append(translated)
                    logger.info(f"✅ 번역 완료 ({i}/{len(narrations)})")
                else:
                    translated_narrations.append(narration)  # 원문 유지
                    logger.warning(f"⚠️ 번역 실패 ({i}/{len(narrations)}) - 원문 사용")
            except Exception as e:
                logger.error(f"번역 오류 ({i}/{len(narrations)}): {e}")
                translated_narrations.append(narration)
        
        return jsonify({
            'success': True,
            'target_lang': target_lang,
            'translated_narrations': translated_narrations,
            'language_name': translator.LANGUAGES.get(target_lang, {}).get('name', target_lang)
        })
        
    except Exception as e:
        logger.error(f"Error in translate_story: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    logger.info("Starting AI Story Generator API on port 5004...")
    app.run(host='0.0.0.0', port=5004, debug=False, threaded=True)
