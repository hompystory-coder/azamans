"""
AI Video Generator API
이미지를 실제 비디오로 변환
"""

from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
from PIL import Image, ImageDraw, ImageFont, ImageFilter
from moviepy import ImageClip, concatenate_videoclips, AudioFileClip, CompositeVideoClip, TextClip
import io
import os
import logging
from datetime import datetime
import requests
import numpy as np

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# 생성된 파일 저장 디렉토리
OUTPUT_DIR = '/home/azamans/webapp/zero-install-ai-studio/public/generated'
VIDEO_DIR = '/home/azamans/webapp/zero-install-ai-studio/public/videos'
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(VIDEO_DIR, exist_ok=True)

def create_beautiful_image(prompt, width=1080, height=1920, style="traditional"):
    """
    고품질 이미지 생성
    """
    try:
        img = Image.new('RGB', (width, height), color='white')
        draw = ImageDraw.Draw(img)
        
        # 스타일별 배경 생성
        if style == "traditional":
            colors = [
                (139, 115, 85),   # 갈색
                (212, 175, 55),   # 금색
                (44, 95, 45),     # 녹색
                (151, 188, 98),   # 연두색
            ]
        elif style == "modern":
            colors = [
                (102, 102, 255),  # 보라
                (255, 102, 178),  # 분홍
                (102, 255, 255),  # 청록
            ]
        else:
            colors = [
                (100, 100, 150),
                (150, 100, 200),
            ]
        
        # 그라데이션 배경
        for y in range(height):
            progress = y / height
            color_idx = int(progress * (len(colors) - 1))
            color_idx = min(color_idx, len(colors) - 2)
            
            next_color_progress = (progress * (len(colors) - 1)) - color_idx
            
            r = int(colors[color_idx][0] + (colors[color_idx + 1][0] - colors[color_idx][0]) * next_color_progress)
            g = int(colors[color_idx][1] + (colors[color_idx + 1][1] - colors[color_idx][1]) * next_color_progress)
            b = int(colors[color_idx][2] + (colors[color_idx + 1][2] - colors[color_idx][2]) * next_color_progress)
            
            draw.rectangle([(0, y), (width, y + 1)], fill=(r, g, b))
        
        # 텍스처 추가
        img = img.filter(ImageFilter.GaussianBlur(radius=2))
        
        # 프롬프트 텍스트 추가
        try:
            font_large = ImageFont.truetype("/usr/share/fonts/truetype/nanum/NanumGothicBold.ttf", 70)
            font_medium = ImageFont.truetype("/usr/share/fonts/truetype/nanum/NanumGothic.ttf", 40)
        except:
            font_large = ImageFont.load_default()
            font_medium = ImageFont.load_default()
        
        # 중앙에 텍스트
        words = prompt.split(' ')
        lines = []
        current_line = []
        
        for word in words:
            current_line.append(word)
            test_line = ' '.join(current_line)
            bbox = draw.textbbox((0, 0), test_line, font=font_medium)
            if bbox[2] - bbox[0] > width - 200 and len(current_line) > 1:
                current_line.pop()
                if current_line:
                    lines.append(' '.join(current_line))
                current_line = [word]
        
        if current_line:
            lines.append(' '.join(current_line))
        
        # 텍스트 그리기
        y_offset = (height - len(lines) * 60) // 2
        
        for line in lines:
            bbox = draw.textbbox((0, 0), line, font=font_medium)
            text_width = bbox[2] - bbox[0]
            x = (width - text_width) // 2
            
            # 그림자
            draw.text((x + 3, y_offset + 3), line, font=font_medium, fill=(0, 0, 0, 128))
            # 텍스트
            draw.text((x, y_offset), line, font=font_medium, fill=(255, 255, 255))
            
            y_offset += 60
        
        # 장식 추가
        draw.ellipse([(width//2 - 100, 100), (width//2 + 100, 300)], 
                     outline=(255, 255, 255, 200), width=5)
        draw.ellipse([(width//2 - 100, height - 300), (width//2 + 100, height - 100)], 
                     outline=(255, 255, 255, 200), width=5)
        
        return img
    
    except Exception as e:
        logger.error(f"Error creating image: {e}")
        img = Image.new('RGB', (width, height), color=(100, 100, 150))
        draw = ImageDraw.Draw(img)
        draw.text((width//2 - 100, height//2), "Image Generation", fill=(255, 255, 255))
        return img

def create_video_from_images(images_data, output_path, fps=30):
    """
    이미지들을 비디오로 변환 (트랜지션 효과 포함)
    """
    try:
        clips = []
        
        for i, img_data in enumerate(images_data):
            # 이미지 로드
            if 'image_path' in img_data:
                img_path = img_data['image_path']
                if not os.path.exists(img_path):
                    logger.warning(f"Image not found: {img_path}")
                    continue
            else:
                # 이미지 생성
                img = create_beautiful_image(
                    img_data.get('description', ''),
                    width=1080,
                    height=1920,
                    style=img_data.get('style', 'traditional')
                )
                # 임시 저장
                timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
                img_path = os.path.join(OUTPUT_DIR, f'temp_scene_{i}_{timestamp}.png')
                img.save(img_path, 'PNG', quality=95)
            
            # 지속 시간
            duration = img_data.get('duration', 3)
            
            # ImageClip 생성 (duration 명시)
            clip = ImageClip(img_path, duration=duration)
            
            clips.append(clip)
            logger.info(f"Processed scene {i+1}/{len(images_data)}")
        
        if not clips:
            raise Exception("No valid clips created")
        
        # 모든 클립 연결
        logger.info("Concatenating clips...")
        final_clip = concatenate_videoclips(clips, method="compose")
        
        # 비디오 저장
        logger.info(f"Writing video to {output_path}...")
        final_clip.write_videofile(
            output_path,
            fps=fps,
            codec='libx264',
            audio_codec='aac',
            temp_audiofile=f'/tmp/temp_audio_{datetime.now().strftime("%Y%m%d_%H%M%S")}.m4a',
            remove_temp=True,
            threads=4,
            preset='ultrafast'
        )
        
        # 클립 정리
        for clip in clips:
            clip.close()
        final_clip.close()
        
        logger.info("Video creation completed!")
        return True
        
    except Exception as e:
        logger.error(f"Error creating video: {e}")
        import traceback
        traceback.print_exc()
        return False

@app.route('/health', methods=['GET'])
def health():
    """헬스 체크"""
    return jsonify({
        'status': 'healthy',
        'service': 'video-generator',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/generate-image', methods=['POST'])
def generate_image():
    """단일 이미지 생성 API"""
    try:
        data = request.json
        prompt = data.get('prompt', 'Beautiful landscape')
        width = data.get('width', 1080)
        height = data.get('height', 1920)
        style = data.get('style', 'traditional')
        
        logger.info(f"Generating image for prompt: {prompt}")
        
        # 이미지 생성
        image = create_beautiful_image(prompt, width, height, style)
        
        # 저장
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f"generated_{timestamp}.png"
        filepath = os.path.join(OUTPUT_DIR, filename)
        image.save(filepath, 'PNG', quality=95)
        
        # URL 반환
        image_url = f"/generated/{filename}"
        
        return jsonify({
            'success': True,
            'image_url': image_url,
            'filename': filename,
            'width': width,
            'height': height
        })
    
    except Exception as e:
        logger.error(f"Error generating image: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/generate-video', methods=['POST'])
def generate_video():
    """
    이미지들을 비디오로 변환하는 API
    """
    try:
        data = request.json
        title = data.get('title', '스토리')
        scenes = data.get('scenes', [])
        fps = data.get('fps', 30)
        
        if not scenes:
            return jsonify({
                'success': False,
                'error': 'No scenes provided'
            }), 400
        
        logger.info(f"Generating video for: {title} ({len(scenes)} scenes)")
        
        # 비디오 파일명
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        safe_title = "".join(c for c in title if c.isalnum() or c in (' ', '-', '_')).strip()
        filename = f"{safe_title}_{timestamp}.mp4"
        output_path = os.path.join(VIDEO_DIR, filename)
        
        # 비디오 생성
        success = create_video_from_images(scenes, output_path, fps)
        
        if not success:
            return jsonify({
                'success': False,
                'error': 'Video creation failed'
            }), 500
        
        # 파일 크기 확인
        file_size = os.path.getsize(output_path)
        
        video_url = f"/videos/{filename}"
        
        return jsonify({
            'success': True,
            'video_url': video_url,
            'filename': filename,
            'file_size': file_size,
            'duration': sum(s.get('duration', 3) for s in scenes),
            'scenes_count': len(scenes)
        })
    
    except Exception as e:
        logger.error(f"Error generating video: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/download/<filename>', methods=['GET'])
def download_file(filename):
    """파일 다운로드"""
    try:
        # 비디오 파일 확인
        video_path = os.path.join(VIDEO_DIR, filename)
        if os.path.exists(video_path):
            return send_file(video_path, as_attachment=True)
        
        # 이미지 파일 확인
        image_path = os.path.join(OUTPUT_DIR, filename)
        if os.path.exists(image_path):
            return send_file(image_path, as_attachment=True)
        
        return jsonify({'error': 'File not found'}), 404
    
    except Exception as e:
        logger.error(f"Error downloading file: {e}")
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    logger.info("Starting AI Video Generator API on port 5003...")
    app.run(host='0.0.0.0', port=5003, debug=False, threaded=True)
