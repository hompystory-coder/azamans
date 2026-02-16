"""
AI Video Generator API
이미지를 실제 비디오로 변환
"""

from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
from PIL import Image, ImageDraw, ImageFont, ImageFilter
from moviepy import ImageClip, concatenate_videoclips, AudioFileClip, CompositeVideoClip, TextClip, CompositeAudioClip
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

def apply_camera_effect(clip, camera_movement, duration):
    """
    카메라 효과 적용
    
    지원 효과:
    - zoom_in: 줌 인
    - zoom_out: 줌 아웃
    - pan_left: 왼쪽으로 패닝
    - pan_right: 오른쪽으로 패닝
    - dolly_forward: 전진 (줌 인과 유사)
    - dolly_backward: 후진 (줌 아웃과 유사)
    - tilt_up: 위로 틸트
    - tilt_down: 아래로 틸트
    - crane_up: 크레인 업
    - crane_down: 크레인 다운
    """
    try:
        w, h = clip.size
        
        # 줌 효과
        if camera_movement in ['zoom_in', 'dolly_forward', 'push_in']:
            def zoom_effect(get_frame, t):
                frame = get_frame(t)
                progress = t / duration
                zoom_factor = 1.0 + (0.3 * progress)  # 1.0 → 1.3배 줌
                
                # 중앙을 기준으로 줌
                new_w = int(w / zoom_factor)
                new_h = int(h / zoom_factor)
                x_offset = (w - new_w) // 2
                y_offset = (h - new_h) // 2
                
                cropped = frame[y_offset:y_offset+new_h, x_offset:x_offset+new_w]
                
                # 원본 크기로 리사이즈
                from PIL import Image
                img = Image.fromarray(cropped)
                img = img.resize((w, h), Image.Resampling.LANCZOS)
                return np.array(img)
            
            return clip.transform(zoom_effect)
        
        elif camera_movement in ['zoom_out', 'dolly_backward', 'pull_back', 'slow_zoom_out']:
            def zoom_out_effect(get_frame, t):
                frame = get_frame(t)
                progress = t / duration
                zoom_factor = 1.3 - (0.3 * progress)  # 1.3배 → 1.0배 줌
                
                new_w = int(w / zoom_factor)
                new_h = int(h / zoom_factor)
                x_offset = (w - new_w) // 2
                y_offset = (h - new_h) // 2
                
                cropped = frame[y_offset:y_offset+new_h, x_offset:x_offset+new_w]
                
                from PIL import Image
                img = Image.fromarray(cropped)
                img = img.resize((w, h), Image.Resampling.LANCZOS)
                return np.array(img)
            
            return clip.transform(zoom_out_effect)
        
        # 패닝 효과
        elif camera_movement in ['pan_left', 'dolly_left']:
            def pan_left_effect(get_frame, t):
                frame = get_frame(t)
                progress = t / duration
                x_shift = int(w * 0.2 * progress)  # 최대 20% 이동
                
                # 오른쪽에서 왼쪽으로 이동
                result = np.zeros_like(frame)
                if x_shift < w:
                    result[:, :w-x_shift] = frame[:, x_shift:]
                return result
            
            return clip.transform(pan_left_effect)
        
        elif camera_movement in ['pan_right', 'dolly_right', 'pan_right_smooth']:
            def pan_right_effect(get_frame, t):
                frame = get_frame(t)
                progress = t / duration
                x_shift = int(w * 0.2 * progress)
                
                result = np.zeros_like(frame)
                if x_shift < w:
                    result[:, x_shift:] = frame[:, :w-x_shift]
                return result
            
            return clip.transform(pan_right_effect)
        
        # 틸트 효과
        elif camera_movement in ['tilt_up', 'crane_up']:
            def tilt_up_effect(get_frame, t):
                frame = get_frame(t)
                progress = t / duration
                y_shift = int(h * 0.2 * progress)
                
                result = np.zeros_like(frame)
                if y_shift < h:
                    result[:h-y_shift, :] = frame[y_shift:, :]
                return result
            
            return clip.transform(tilt_up_effect)
        
        elif camera_movement in ['tilt_down', 'crane_down']:
            def tilt_down_effect(get_frame, t):
                frame = get_frame(t)
                progress = t / duration
                y_shift = int(h * 0.2 * progress)
                
                result = np.zeros_like(frame)
                if y_shift < h:
                    result[y_shift:, :] = frame[:h-y_shift, :]
                return result
            
            return clip.transform(tilt_down_effect)
        
        # 기본: 효과 없음
        else:
            return clip
    
    except Exception as e:
        logger.warning(f"Failed to apply camera effect '{camera_movement}': {e}")
        return clip

def create_video_from_images(images_data, output_path, fps=30, background_music_url=None):
    """
    이미지들을 비디오로 변환 (카메라 효과 + 배경음악 포함)
    
    Args:
        images_data: 씬 데이터 리스트
        output_path: 출력 비디오 경로
        fps: 프레임레이트
        background_music_url: 배경음악 URL (선택 사항)
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
            
            # 오디오 먼저 확인 (audio_url이 있는 경우)
            audio_clip = None
            if 'audio_url' in img_data and img_data['audio_url']:
                audio_url = img_data['audio_url']
                # /audio/xxx.mp3 → 실제 파일 경로로 변환
                if audio_url.startswith('/audio/'):
                    audio_filename = audio_url.replace('/audio/', '')
                    audio_path = os.path.join('/home/azamans/webapp/zero-install-ai-studio/public/audio', audio_filename)
                    
                    if os.path.exists(audio_path):
                        try:
                            audio_clip = AudioFileClip(audio_path)
                            # 오디오 길이가 있으면 그 길이를 duration으로 사용
                            if audio_clip.duration > 0:
                                duration = audio_clip.duration
                                logger.info(f"  → Using audio duration: {audio_clip.duration:.1f}s for {audio_filename}")
                        except Exception as e:
                            logger.warning(f"  → Failed to load audio: {e}")
                            audio_clip = None
                    else:
                        logger.warning(f"  → Audio file not found: {audio_path}")
            
            # ImageClip 생성 (duration 명시)
            clip = ImageClip(img_path, duration=duration)
            
            # 🆕 카메라 효과 적용
            camera_movement = img_data.get('camera_movement', None)
            if camera_movement:
                logger.info(f"  → Applying camera effect: {camera_movement}")
                clip = apply_camera_effect(clip, camera_movement, duration)
            
            # 오디오 추가
            if audio_clip is not None:
                try:
                    clip = clip.with_audio(audio_clip)
                    logger.info(f"  → Audio successfully attached!")
                except Exception as e:
                    logger.warning(f"  → Failed to attach audio: {e}")
            
            clips.append(clip)
            logger.info(f"Processed scene {i+1}/{len(images_data)}")
        
        if not clips:
            raise Exception("No valid clips created")
        
        # 모든 클립 연결
        logger.info("Concatenating clips...")
        final_clip = concatenate_videoclips(clips, method="compose")
        
        # 🆕 배경음악 추가
        if background_music_url:
            logger.info(f"Adding background music: {background_music_url}")
            try:
                # 배경음악 로드
                bgm_path = None
                if background_music_url.startswith('http'):
                    # URL인 경우 다운로드
                    import requests
                    response = requests.get(background_music_url, timeout=10)
                    if response.status_code == 200:
                        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
                        bgm_path = f'/tmp/bgm_{timestamp}.mp3'
                        with open(bgm_path, 'wb') as f:
                            f.write(response.content)
                        logger.info(f"  → Downloaded BGM to: {bgm_path}")
                elif background_music_url.startswith('/'):
                    # 로컬 파일 경로
                    bgm_path = background_music_url
                
                if bgm_path and os.path.exists(bgm_path):
                    bgm_clip = AudioFileClip(bgm_path)
                    
                    # 배경음악 길이 조정 (비디오 길이에 맞춤)
                    video_duration = final_clip.duration
                    if bgm_clip.duration < video_duration:
                        # 배경음악이 짧으면 반복
                        logger.info(f"  → Looping BGM (original: {bgm_clip.duration:.1f}s, needed: {video_duration:.1f}s)")
                        num_loops = int(video_duration / bgm_clip.duration) + 1
                        bgm_clip = bgm_clip.loop(n=num_loops).subclipped(0, video_duration)
                    elif bgm_clip.duration > video_duration:
                        # 배경음악이 길면 자르기
                        logger.info(f"  → Trimming BGM (original: {bgm_clip.duration:.1f}s, needed: {video_duration:.1f}s)")
                        bgm_clip = bgm_clip.subclipped(0, video_duration)
                    
                    # 배경음악 볼륨 조절 (30%로 낮춤)
                    bgm_clip = bgm_clip.with_effects([("audio_fadein", 1.0), ("audio_fadeout", 1.0)])
                    bgm_clip = bgm_clip.multiply_volume(0.3)
                    
                    # 기존 오디오와 배경음악 믹싱
                    if final_clip.audio is not None:
                        logger.info("  → Mixing narration + BGM")
                        mixed_audio = CompositeAudioClip([final_clip.audio, bgm_clip])
                        final_clip = final_clip.with_audio(mixed_audio)
                    else:
                        logger.info("  → Adding BGM only (no narration)")
                        final_clip = final_clip.with_audio(bgm_clip)
                    
                    logger.info("  ✅ Background music added successfully!")
                else:
                    logger.warning(f"  ⚠️ BGM file not found: {bgm_path}")
                    
            except Exception as e:
                logger.warning(f"  ⚠️ Failed to add background music: {e}")
                import traceback
                traceback.print_exc()
        
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
        background_music_url = data.get('background_music_url', None)  # 🆕 배경음악 URL
        
        if not scenes:
            return jsonify({
                'success': False,
                'error': 'No scenes provided'
            }), 400
        
        logger.info(f"Generating video for: {title} ({len(scenes)} scenes)")
        if background_music_url:
            logger.info(f"  🎵 Background music URL received: {background_music_url}")
        else:
            logger.warning(f"  ⚠️ No background music URL provided")
        
        # scene_number로 정렬 (있는 경우)
        if scenes and 'scene_number' in scenes[0]:
            scenes = sorted(scenes, key=lambda x: x.get('scene_number', 0))
            logger.info(f"Sorted scenes by scene_number")
        
        # 비디오 파일명
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        safe_title = "".join(c for c in title if c.isalnum() or c in (' ', '-', '_')).strip()
        video_filename = f"{safe_title}_{timestamp}.mp4"
        output_path = os.path.join(VIDEO_DIR, video_filename)
        
        # scenes 데이터 변환 (image_url을 실제 파일 경로로)
        processed_scenes = []
        for scene in scenes:
            scene_data = dict(scene)
            
            # image_url을 실제 파일 경로로 변환
            if 'image_url' in scene_data:
                image_url = scene_data['image_url']
                # /generated/xxx.png → /home/azamans/webapp/zero-install-ai-studio/public/generated/xxx.png
                if image_url.startswith('/generated/'):
                    image_filename = image_url.replace('/generated/', '')
                    scene_data['image_path'] = os.path.join(OUTPUT_DIR, image_filename)
                elif image_url.startswith('/videos/'):
                    image_filename = image_url.replace('/videos/', '')
                    scene_data['image_path'] = os.path.join(VIDEO_DIR, image_filename)
                else:
                    # 절대 경로인 경우 그대로 사용
                    scene_data['image_path'] = image_url
                    
                logger.info(f"Scene {len(processed_scenes)+1}: {scene_data.get('image_path', 'N/A')}")
            
            processed_scenes.append(scene_data)
        
        # 비디오 생성 (🆕 배경음악 포함)
        success = create_video_from_images(
            processed_scenes, 
            output_path, 
            fps, 
            background_music_url=background_music_url
        )
        
        if not success:
            return jsonify({
                'success': False,
                'error': 'Video creation failed'
            }), 500
        
        # 파일 크기 확인
        file_size = os.path.getsize(output_path)
        
        video_url = f"/videos/{video_filename}"
        
        return jsonify({
            'success': True,
            'video_url': video_url,
            'filename': video_filename,
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
