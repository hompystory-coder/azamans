"""
TTS Audio Generator API
구어체 나레이션 → 실제 음성 변환
"""

from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
from gtts import gTTS
import os
import logging
from datetime import datetime
import tempfile

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# 생성된 음성 저장 디렉토리
OUTPUT_DIR = '/home/azamans/webapp/zero-install-ai-studio/public/audio'
os.makedirs(OUTPUT_DIR, exist_ok=True)

def generate_tts_audio(text: str, lang: str = 'ko', slow: bool = False) -> str:
    """
    텍스트를 음성으로 변환 (Google TTS)
    """
    try:
        logger.info(f"Generating TTS for text: {text[:50]}...")
        
        # Google TTS 생성
        tts = gTTS(text=text, lang=lang, slow=slow)
        
        # 임시 파일로 저장
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f"narration_{timestamp}.mp3"
        filepath = os.path.join(OUTPUT_DIR, filename)
        
        tts.save(filepath)
        
        logger.info(f"TTS audio saved: {filename}")
        return filename
        
    except Exception as e:
        logger.error(f"Error generating TTS: {e}")
        raise

@app.route('/health', methods=['GET'])
def health():
    """헬스 체크"""
    return jsonify({
        'status': 'healthy',
        'service': 'tts-generator',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/generate-tts', methods=['POST'])
def generate_tts():
    """
    단일 나레이션 음성 생성
    """
    try:
        data = request.json
        text = data.get('text', '')
        lang = data.get('lang', 'ko')
        slow = data.get('slow', False)
        
        if not text:
            return jsonify({
                'success': False,
                'error': 'Text is required'
            }), 400
        
        # TTS 생성
        filename = generate_tts_audio(text, lang, slow)
        
        return jsonify({
            'success': True,
            'audio_url': f"/audio/{filename}",
            'filename': filename
        })
        
    except Exception as e:
        logger.error(f"Error in generate_tts: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/generate-story-audio', methods=['POST'])
def generate_story_audio():
    """
    스토리 전체 장면의 나레이션 음성 생성
    """
    try:
        data = request.json
        scenes = data.get('scenes', [])
        title = data.get('title', 'story')
        
        if not scenes:
            return jsonify({
                'success': False,
                'error': 'Scenes are required'
            }), 400
        
        logger.info(f"Generating TTS for {len(scenes)} scenes: {title}")
        
        audio_files = []
        
        for i, scene in enumerate(scenes):
            narration = scene.get('narration', scene.get('korean_description', ''))
            
            if not narration:
                logger.warning(f"Scene {i+1} has no narration, skipping...")
                continue
            
            # 장면별 TTS 생성
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            filename = f"{title}_scene_{i+1:02d}_{timestamp}.mp3"
            filepath = os.path.join(OUTPUT_DIR, filename)
            
            tts = gTTS(text=narration, lang='ko', slow=False)
            tts.save(filepath)
            
            audio_files.append({
                'scene_number': i + 1,
                'audio_url': f"/audio/{filename}",
                'filename': filename,
                'narration': narration
            })
            
            logger.info(f"Scene {i+1}/{len(scenes)} audio generated: {filename}")
        
        return jsonify({
            'success': True,
            'title': title,
            'audio_files': audio_files,
            'total_scenes': len(audio_files)
        })
        
    except Exception as e:
        logger.error(f"Error in generate_story_audio: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    logger.info("Starting TTS Generator API on port 5005...")
    app.run(host='0.0.0.0', port=5005, debug=False)
