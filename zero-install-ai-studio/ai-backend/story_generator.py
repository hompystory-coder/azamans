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

def create_detailed_scene_description(prompt: str, scene_num: int, korean_mood: str, act_num: int) -> str:
    """
    각 막의 특성에 맞는 상세한 장면 설명 생성
    스토리 제목의 핵심 키워드를 추출하여 각 막에 맞는 구체적 행동으로 변환
    """
    # 프롬프트에서 핵심 키워드 추출 및 직업/캐릭터 식별
    prompt_lower = prompt.lower()
    
    # 각 막별 기본 행동 템플릿
    act_templates = {
        1: {  # 발단
            "default": "showing the main character starting their day in their usual environment",
            "keywords": {
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
                "해적|pirate": "pirate captain on ship deck, looking through telescope at horizon"
            }
        },
        2: {  # 전개
            "default": "character beginning their main activity or challenge",
            "keywords": {
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
                "해적|pirate": "pirate ship sailing stormy seas, crew working on deck"
            }
        },
        3: {  # 위기
            "default": "facing major challenge or obstacle",
            "keywords": {
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
                "해적|pirate": "pirate ship under attack, enemy ships firing cannons, battle at sea"
            }
        },
        4: {  # 절정
            "default": "peak action moment, climactic scene",
            "keywords": {
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
                "해적|pirate": "pirate captain winning epic sword duel, claiming victory"
            }
        },
        5: {  # 결말
            "default": "peaceful resolution, character satisfied",
            "keywords": {
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
                "해적|pirate": "pirate crew celebrating with treasure, joyful party on ship deck"
            }
        }
    }
    
    # 현재 막의 템플릿 가져오기
    act_template = act_templates.get(act_num, act_templates[1])
    
    # 키워드 매칭하여 구체적 행동 찾기
    specific_action = act_template["default"]
    for pattern, action in act_template["keywords"].items():
        if any(keyword in prompt_lower for keyword in pattern.split("|")):
            specific_action = action
            break
    
    # 최종 프롬프트 구성 - 제목보다 구체적 행동을 먼저!
    return (
        f"{prompt}, scene {scene_num}: {specific_action}. "
        f"{korean_mood} atmosphere. "
        f"This is a scene from the story '{prompt}'. "
        f"Highly detailed, cinematic lighting, 1080x1920 vertical format, "
        f"professional photography, dramatic storytelling, 4K quality, masterpiece"
    )

def generate_custom_story(user_input: str, scenes_count: int, scene_duration: float) -> dict:
    """커스텀 스토리 생성 - 구어체 궁금증 유발형 (5막 구조) - 완전 고유 나레이션"""
    
    # 5막 구조: 발단 → 전개 → 위기 → 절정 → 결말
    # 각 막당 최대 10개의 고유 나레이션 풀 제공 (중복 완전 제거)
    story_structure = [
        # 1막: 발단 (Exposition) - 호기심 유발
        {
            "narrations": [
                "여러분, 이건 정말 믿기 힘든 이야기인데 한번 들어보세요.",
                "이 이야기는 아주 평범한 하루에서 시작됐어요.",
                "오늘 들려드릴 이야기는 여러분을 완전히 사로잡을 거예요.",
                "모든 건 아무도 예상하지 못한 순간에 시작됐죠.",
                "평범해 보이는 이 장면 뒤에 숨겨진 비밀이 있어요.",
                "자, 이제 정말 놀라운 이야기의 시작입니다.",
                "아주 오래전부터 전해 내려오는 이야기가 있어요.",
                "이 순간이 모든 것을 바꿔놓을 줄은 아무도 몰랐어요.",
                "처음엔 아무것도 특별해 보이지 않았죠.",
                "이 이야기의 주인공은 평범한 일상을 보내고 있었어요."
            ],
            "moods": ["mysterious", "curious", "intriguing", "calm", "wondering"],
            "cameras": ["slow_zoom_in", "pan_right", "dolly_in", "crane_down", "static_wide"],
            "korean_moods": ["신비로운", "호기심 가득한", "흥미진진한", "고요한", "궁금증 유발하는"]
        },
        # 2막: 전개 (Rising Action) - 상황 발전
        {
            "narrations": [
                "처음에는 평범해 보였지만, 뭔가 이상한 느낌이 들기 시작했어요.",
                "그런데 여기서 예상치 못한 일이 벌어지기 시작했죠.",
                "상황이 점점 더 흥미로워지고 있었어요.",
                "모든 게 계획대로 흘러가는 것처럼 보였지만 사실은 아니었어요.",
                "이때부터 이야기는 완전히 다른 방향으로 흘러가기 시작했죠.",
                "주인공은 아직 자신에게 무슨 일이 일어날지 몰랐어요.",
                "작은 변화들이 하나씩 나타나기 시작했어요.",
                "평범했던 하루가 특별한 모험으로 바뀌고 있었죠.",
                "이 순간부터 모든 것이 달라지기 시작했어요.",
                "아무도 예상하지 못한 전개가 펼쳐지고 있었어요."
            ],
            "moods": ["revealing", "intriguing", "developing", "surprising", "transforming"],
            "cameras": ["pan_left", "zoom_in", "dolly_forward", "orbit", "tracking"],
            "korean_moods": ["서서히 드러나는", "흥미진진한", "발전하는", "놀라운", "변화하는"]
        },
        # 3막: 위기 (Conflict) - 긴장감 고조
        {
            "narrations": [
                "이제부터가 진짜 중요한 순간인데, 과연 어떻게 될까요?",
                "긴장감이 점점 고조되고, 모두가 숨죽이고 지켜보고 있었어요.",
                "예상치 못한 장애물이 앞을 가로막았어요.",
                "이대로는 절대 안 될 것 같은 위기의 순간이었죠.",
                "모든 게 무너질 것만 같은 아슬아슬한 순간이에요.",
                "과연 이 난관을 어떻게 헤쳐나갈 수 있을까요?",
                "상황은 점점 더 복잡하고 어려워지고 있었어요.",
                "이제 선택의 순간이 다가오고 있었죠.",
                "모두가 불가능하다고 생각하는 그 순간이에요.",
                "여기서 포기하면 모든 게 끝나버릴 거예요."
            ],
            "moods": ["intense", "suspenseful", "challenging", "critical", "tense"],
            "cameras": ["shake", "quick_zoom", "dutch_angle", "handheld", "tight_close"],
            "korean_moods": ["긴장감 넘치는", "숨막히는", "도전적인", "결정적인", "팽팽한"]
        },
        # 4막: 절정 (Climax) - 결정적 순간
        {
            "narrations": [
                "그리고 드디어, 결정적인 순간이 찾아왔어요!",
                "바로 이 순간, 모든 게 완전히 바뀌어버렸죠.",
                "상상도 못 했던 일이 눈앞에서 펼쳐지고 있었어요.",
                "이게 바로 운명을 가르는 결정적인 한 순간이에요.",
                "모든 것이 이 한 번의 선택으로 결정되는 순간이죠.",
                "세상이 멈춘 것 같은 그 짧은 순간이었어요.",
                "지금까지의 모든 것이 이 순간을 위한 거였어요.",
                "믿을 수 없는 반전이 기다리고 있었죠.",
                "아무도 예상하지 못한 놀라운 결과가 나타났어요.",
                "바로 그 순간, 기적이 일어났어요!"
            ],
            "moods": ["shocking", "dramatic", "explosive", "pivotal", "epic"],
            "cameras": ["tilt_up", "dramatic_zoom", "360_spin", "crash_zoom", "aerial_rise"],
            "korean_moods": ["충격적인", "극적인", "폭발적인", "전환점의", "장대한"]
        },
        # 5막: 결말 (Resolution) - 마무리와 여운
        {
            "narrations": [
                "그렇게 이야기는 마무리되었고, 모두가 깨달음을 얻었어요.",
                "이 이야기의 진짜 의미는 여러분이 직접 느껴보시면 알 수 있을 거예요.",
                "모든 것이 제자리를 찾아가고 평화가 찾아왔어요.",
                "이제 모든 게 이해가 되기 시작했죠.",
                "결국 진실은 언제나 빛을 발하게 되어 있어요.",
                "이렇게 또 하나의 이야기가 끝이 났어요.",
                "그리고 그들은 새로운 시작을 맞이하게 됐어요.",
                "이 경험을 통해 얻은 교훈은 평생 잊지 못할 거예요.",
                "마지막 장면은 새로운 희망으로 가득했어요.",
                "이야기는 끝났지만, 그 의미는 영원히 남을 거예요."
            ],
            "moods": ["reflective", "peaceful", "hopeful", "enlightening", "satisfying"],
            "cameras": ["zoom_out", "slow_zoom_out", "crane_up", "pull_back", "wide_establishing"],
            "korean_moods": ["여운이 남는", "평화로운", "희망찬", "깨달음의", "만족스러운"]
        }
    ]
    
    # 5막 구조에 따라 장면 배분
    scenes = []
    act_distribution = distribute_scenes_to_acts(scenes_count)
    
    scene_idx = 0
    for act_num, (act_data, num_scenes_in_act) in enumerate(zip(story_structure, act_distribution)):
        for scene_in_act in range(num_scenes_in_act):
            # 중요: 각 장면마다 고유한 나레이션 사용 (절대 중복 없음)
            if scene_in_act < len(act_data["narrations"]):
                narration = act_data["narrations"][scene_in_act]
            else:
                # 예외적으로 장면이 10개를 넘어가면 조합 생성
                base_narration = act_data["narrations"][scene_in_act % len(act_data["narrations"])]
                narration = f"{base_narration} (파트 {scene_in_act + 1})"
            
            mood_idx = scene_in_act % len(act_data["moods"])
            camera_idx = scene_in_act % len(act_data["cameras"])
            
            mood = act_data["moods"][mood_idx]
            camera_movement = act_data["cameras"][camera_idx]
            korean_mood = act_data["korean_moods"][mood_idx]
            
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
