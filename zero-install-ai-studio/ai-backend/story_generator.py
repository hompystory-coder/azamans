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

def generate_custom_story(user_input: str, scenes_count: int, scene_duration: float) -> dict:
    """커스텀 스토리 생성 - 구어체 궁금증 유발형"""
    
    # 스토리 구조 템플릿 (도입-전개-위기-절정-결말)
    story_templates = [
        "여러분 이건 진짜 믿기 힘든 이야기인데 한번 들어보세요.",
        "처음에는 평범해 보였지만 알고 보니 완전히 다른 상황이었어요.",
        "그런데 여기서 예상치 못한 일이 벌어지기 시작했어요.",
        "이제부터가 진짜 중요한 순간인데 과연 어떻게 될까요.",
        "결국 상황은 점점 심각해지고 긴장감이 최고조에 달했어요.",
        "그리고 마지막에 완전히 반전이 일어나는데 정말 놀라울 거예요.",
        "이 이야기의 진짜 의미는 여러분이 직접 느껴보시면 알 수 있을 거예요."
    ]
    
    # 카메라 무빙과 분위기 조합
    camera_moods = [
        ("slow_zoom_in", "mysterious", "조용히 다가가는"),
        ("pan_right", "revealing", "서서히 드러나는"),
        ("zoom_in", "intense", "긴장감 넘치는"),
        ("shake", "shocking", "충격적인"),
        ("tilt_up", "dramatic", "극적인"),
        ("zoom_out", "expansive", "광활한"),
        ("slow_zoom_out", "reflective", "여운이 남는")
    ]
    
    scenes = []
    
    for i in range(scenes_count):
        camera_movement, mood, korean_mood = camera_moods[i % len(camera_moods)]
        narration = story_templates[i % len(story_templates)]
        
        # 영어 프롬프트 (AI 이미지 생성용)
        description = (
            f"{user_input}, scene {i + 1}, "
            f"{korean_mood} atmosphere, cinematic lighting, "
            f"highly detailed, dramatic composition, "
            f"4K quality, professional photography, "
            f"emotional storytelling moment"
        )
        
        scene = {
            "scene_number": i + 1,
            "title": f"장면 {i + 1}",
            "description": description,
            "korean_description": f"{user_input}의 {korean_mood} 순간",
            "narration": narration,
            "duration": scene_duration,
            "camera_movement": camera_movement,
            "mood": mood
        }
        scenes.append(scene)
    
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
