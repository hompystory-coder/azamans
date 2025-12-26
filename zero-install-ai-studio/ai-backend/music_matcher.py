"""
Background Music Matcher API
스토리 분위기에 맞는 배경음악 자동 매칭
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import logging
from datetime import datetime
import os

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# 장르/분위기별 음악 매핑 (무료 CC0 음악 URL)
MUSIC_LIBRARY = {
    "romantic": {
        "name": "Romantic Piano",
        "url": "https://cdn.pixabay.com/audio/2022/05/27/audio_1808fbf07a.mp3",
        "description": "로맨틱한 피아노 멜로디"
    },
    "sad": {
        "name": "Melancholic Strings",
        "url": "https://cdn.pixabay.com/audio/2022/03/10/audio_c8c8b3f4e6.mp3",
        "description": "슬픈 현악기 선율"
    },
    "happy": {
        "name": "Joyful Acoustic",
        "url": "https://cdn.pixabay.com/audio/2022/03/24/audio_e5c3b48e76.mp3",
        "description": "즐거운 어쿠스틱 기타"
    },
    "traditional": {
        "name": "Korean Traditional",
        "url": "https://cdn.pixabay.com/audio/2021/09/17/audio_7181e8a4f0.mp3",
        "description": "전통 한국 음악"
    },
    "mysterious": {
        "name": "Mysterious Ambient",
        "url": "https://cdn.pixabay.com/audio/2022/05/17/audio_5f1acad357.mp3",
        "description": "신비로운 앰비언트"
    },
    "epic": {
        "name": "Epic Cinematic",
        "url": "https://cdn.pixabay.com/audio/2022/03/30/audio_d0fa0fc621.mp3",
        "description": "웅장한 시네마틱"
    },
    "peaceful": {
        "name": "Peaceful Nature",
        "url": "https://cdn.pixabay.com/audio/2022/03/15/audio_53fd8c6945.mp3",
        "description": "평화로운 자연 사운드"
    }
}

def match_music_by_mood(mood: str, genre: str) -> dict:
    """
    분위기와 장르에 맞는 음악 매칭
    """
    mood_lower = mood.lower()
    genre_lower = genre.lower()
    
    logger.info(f"Matching music for mood: {mood}, genre: {genre}")
    
    # 우선순위 1: 정확한 매칭
    if mood_lower in MUSIC_LIBRARY:
        return MUSIC_LIBRARY[mood_lower]
    
    # 우선순위 2: 한국 전통
    if "한국" in genre or "전통" in genre or "traditional" in genre_lower:
        return MUSIC_LIBRARY["traditional"]
    
    # 우선순위 3: 분위기 키워드 매칭
    if any(word in mood_lower for word in ["romantic", "love", "romance"]):
        return MUSIC_LIBRARY["romantic"]
    elif any(word in mood_lower for word in ["sad", "melancholic", "tragic"]):
        return MUSIC_LIBRARY["sad"]
    elif any(word in mood_lower for word in ["happy", "joyful", "festive"]):
        return MUSIC_LIBRARY["happy"]
    elif any(word in mood_lower for word in ["mysterious", "mystical", "magical"]):
        return MUSIC_LIBRARY["mysterious"]
    elif any(word in mood_lower for word in ["epic", "dramatic", "heroic"]):
        return MUSIC_LIBRARY["epic"]
    elif any(word in mood_lower for word in ["peaceful", "calm", "serene"]):
        return MUSIC_LIBRARY["peaceful"]
    
    # 기본값: 평화로운 음악
    return MUSIC_LIBRARY["peaceful"]

@app.route('/health', methods=['GET'])
def health():
    """헬스 체크"""
    return jsonify({
        'status': 'healthy',
        'service': 'music-matcher',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/match-music', methods=['POST'])
def match_music():
    """
    스토리에 맞는 배경음악 매칭
    """
    try:
        data = request.json
        mood = data.get('mood', 'peaceful')
        genre = data.get('genre', '')
        title = data.get('title', '')
        
        logger.info(f"Matching music for story: {title} (mood: {mood}, genre: {genre})")
        
        # 음악 매칭
        matched_music = match_music_by_mood(mood, genre)
        
        return jsonify({
            'success': True,
            'music': matched_music,
            'mood': mood,
            'genre': genre
        })
        
    except Exception as e:
        logger.error(f"Error in match_music: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/list-music', methods=['GET'])
def list_music():
    """
    사용 가능한 음악 목록 조회
    """
    return jsonify({
        'success': True,
        'music_library': MUSIC_LIBRARY,
        'total_count': len(MUSIC_LIBRARY)
    })

if __name__ == '__main__':
    logger.info("Starting Music Matcher API on port 5006...")
    app.run(host='0.0.0.0', port=5006, debug=False)
