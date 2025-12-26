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

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def generate_story_script(user_input: str, duration_seconds: int = 30) -> dict:
    """
    사용자 입력을 기반으로 스토리 스크립트 생성
    """
    try:
        # 장면 개수 계산 (3-5초당 1장면)
        scenes_count = max(5, duration_seconds // 4)  # 최소 5장면
        scene_duration = duration_seconds / scenes_count
        
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
    """선녀와 나무꾼 스토리"""
    base_scenes = [
        {
            "scene_number": 1,
            "title": "산속의 나무꾼",
            "description": "A poor woodcutter working in deep mountain forest, cutting trees, sunset light filtering through tall trees, traditional Korean painting style, beautiful nature",
            "korean_description": "깊은 산속에서 나무를 하는 가난한 나무꾼, 석양 빛이 나뭇가지 사이로 비치는 아름다운 숲",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "peaceful"
        },
        {
            "scene_number": 2,
            "title": "연못의 선녀들",
            "description": "Beautiful fairy maidens bathing in crystal clear pond, magical sparkles, ethereal atmosphere, mystical Korean folklore scene",
            "korean_description": "맑은 연못에서 목욕하는 아름다운 선녀들, 신비로운 빛과 반짝이는 마법의 분위기",
            "duration": scene_duration,
            "camera_movement": "pan_right",
            "mood": "magical"
        },
        {
            "scene_number": 3,
            "title": "숨겨진 날개옷",
            "description": "Hidden fairy robe behind tree, glowing with magical light, shimmering fabric, enchanted object in forest",
            "korean_description": "나무 뒤에 숨겨진 선녀의 날개옷, 반짝이는 마법의 빛을 내뿜는 신비로운 천",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "mysterious"
        },
        {
            "scene_number": 4,
            "title": "운명적 만남",
            "description": "First meeting between woodcutter and fairy, romantic atmosphere, emotional encounter, love at first sight, traditional Korean style",
            "korean_description": "나무꾼과 선녀가 처음 만나는 순간, 로맨틱한 분위기, 감동적인 첫 만남",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "romantic"
        },
        {
            "scene_number": 5,
            "title": "행복한 가정",
            "description": "Happy family life, woodcutter with fairy wife and beautiful children, warm home, joyful moments, traditional Korean family",
            "korean_description": "행복한 결혼 생활, 예쁜 아이들과 함께하는 따뜻한 가족, 즐거운 일상",
            "duration": scene_duration,
            "camera_movement": "pan_left",
            "mood": "happy"
        },
        {
            "scene_number": 6,
            "title": "이별의 순간",
            "description": "Fairy finding her robe and flying to heaven, emotional farewell, tears, dramatic goodbye scene, heartbreaking moment",
            "korean_description": "선녀가 날개옷을 찾아 하늘로 날아가는 장면, 눈물의 감동적인 이별",
            "duration": scene_duration,
            "camera_movement": "tilt_up",
            "mood": "sad"
        },
        {
            "scene_number": 7,
            "title": "그리움",
            "description": "Woodcutter looking up at sky with tears, longing and sorrow, emotional final scene, sunset backdrop",
            "korean_description": "나무꾼이 하늘을 바라보며 눈물 흘리는 마지막 장면, 깊은 그리움과 슬픔",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_out",
            "mood": "melancholic"
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
    """흥부와 놀부 스토리"""
    base_scenes = [
        {
            "scene_number": 1,
            "title": "가난한 흥부의 집",
            "description": "Poor Heungbu's shabby house, humble dwelling, worn-out traditional Korean home, poverty but kindness",
            "korean_description": "가난하지만 착한 흥부의 초라한 집",
            "duration": scene_duration,
            "camera_movement": "pan_right",
            "mood": "humble"
        },
        {
            "scene_number": 2,
            "title": "부자 놀부의 저택",
            "description": "Rich Nolbu's luxurious mansion, wealthy traditional Korean house, greed and arrogance, elaborate decorations",
            "korean_description": "부자이지만 욕심 많은 놀부의 화려한 집",
            "duration": scene_duration,
            "camera_movement": "zoom_in",
            "mood": "luxurious"
        },
        {
            "scene_number": 3,
            "title": "제비를 구하다",
            "description": "Kind Heungbu saving injured swallow, caring for bird, gentle actions, compassion and kindness",
            "korean_description": "다친 제비를 구해주는 착한 흥부",
            "duration": scene_duration,
            "camera_movement": "slow_zoom_in",
            "mood": "kind"
        },
        {
            "scene_number": 4,
            "title": "박씨를 심다",
            "description": "Planting magical gourd seeds from swallow, hope and anticipation, traditional Korean garden",
            "korean_description": "제비가 가져온 박씨를 심는 흥부",
            "duration": scene_duration,
            "camera_movement": "tilt_down",
            "mood": "hopeful"
        },
        {
            "scene_number": 5,
            "title": "보물의 박",
            "description": "Golden treasures emerging from magical gourd, sparkling gold and jewels, miraculous reward, abundance",
            "korean_description": "박에서 나오는 금은보화, 반짝이는 보물들",
            "duration": scene_duration,
            "camera_movement": "zoom_out",
            "mood": "magical"
        },
        {
            "scene_number": 6,
            "title": "놀부의 욕심",
            "description": "Greedy Nolbu breaking swallow's leg intentionally, evil action, dark atmosphere, malicious intent",
            "korean_description": "욕심내어 제비 다리를 부러뜨리는 놀부",
            "duration": scene_duration,
            "camera_movement": "shake",
            "mood": "evil"
        },
        {
            "scene_number": 7,
            "title": "벌받는 놀부",
            "description": "Goblins and troubles emerging from Nolbu's gourd, punishment for greed, karma, scary creatures",
            "korean_description": "놀부의 박에서 나오는 도깨비들, 벌받는 장면",
            "duration": scene_duration,
            "camera_movement": "shake",
            "mood": "scary"
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

def generate_custom_story(user_input: str, scenes_count: int, scene_duration: float) -> dict:
    """커스텀 스토리 생성"""
    scenes = []
    
    for i in range(scenes_count):
        scene = {
            "scene_number": i + 1,
            "title": f"{user_input} - Scene {i + 1}",
            "description": f"{user_input}, scene {i + 1}, cinematic shot, beautiful composition, high quality",
            "korean_description": f"{user_input}의 장면 {i + 1}",
            "duration": scene_duration,
            "camera_movement": ["zoom_in", "zoom_out", "pan_left", "pan_right", "slow_zoom_in"][i % 5],
            "mood": "dramatic"
        }
        scenes.append(scene)
    
    return {
        "title": user_input,
        "genre": "사용자 정의 스토리",
        "total_duration": sum(s['duration'] for s in scenes),
        "total_scenes": len(scenes),
        "style": "cinematic",
        "mood": "dramatic",
        "scenes": scenes,
        "music_suggestion": "Epic cinematic music"
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
    """스토리 스크립트 생성 API"""
    try:
        data = request.json
        user_input = data.get('prompt', '선녀와 나무꾼')
        duration = data.get('duration', 30)
        
        logger.info(f"Generating story for: {user_input} ({duration}s)")
        
        # 스토리 생성
        story = generate_story_script(user_input, duration)
        
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

if __name__ == '__main__':
    logger.info("Starting AI Story Generator API on port 5004...")
    app.run(host='0.0.0.0', port=5004, debug=False, threaded=True)
