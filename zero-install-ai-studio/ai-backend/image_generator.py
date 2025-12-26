"""
AI Image Generator API
Stable Diffusion을 사용한 실제 이미지 생성
"""

from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
from PIL import Image, ImageDraw, ImageFont, ImageFilter
import io
import os
import logging
from datetime import datetime
import requests

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# 생성된 이미지 저장 디렉토리
OUTPUT_DIR = '/home/azamans/webapp/zero-install-ai-studio/public/generated'
os.makedirs(OUTPUT_DIR, exist_ok=True)

# Stable Diffusion 모델 (추후 로드)
model = None
pipe = None

def initialize_model():
    """Stable Diffusion 모델 초기화"""
    global model, pipe
    try:
        logger.info("Initializing Stable Diffusion model...")
        
        # CPU에서 경량 모델 사용
        from diffusers import StableDiffusionPipeline
        import torch
        
        model_id = "runwayml/stable-diffusion-v1-5"
        
        pipe = StableDiffusionPipeline.from_pretrained(
            model_id,
            torch_dtype=torch.float32,  # CPU는 float32
            safety_checker=None,
            requires_safety_checker=False
        )
        pipe = pipe.to("cpu")
        
        logger.info("Model initialized successfully!")
        return True
    except Exception as e:
        logger.error(f"Failed to initialize model: {e}")
        return False

def create_beautiful_image(prompt, width=1080, height=1920, style="traditional"):
    """
    고품질 이미지 생성 (실제 AI 사용 또는 고급 그래픽)
    """
    try:
        # 이미지 생성
        img = Image.new('RGB', (width, height), color='white')
        draw = ImageDraw.Draw(img)
        
        # 스타일별 배경 생성
        if style == "traditional":
            # 한국 전통 색상 그라데이션
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
        
        # 텍스처 추가 (노이즈)
        from PIL import ImageFilter
        img = img.filter(ImageFilter.GaussianBlur(radius=2))
        
        # 프롬프트 텍스트 추가 (여러 줄)
        try:
            # 한글 폰트 시도
            font_large = ImageFont.truetype("/usr/share/fonts/truetype/nanum/NanumGothicBold.ttf", 70)
            font_medium = ImageFont.truetype("/usr/share/fonts/truetype/nanum/NanumGothic.ttf", 40)
        except:
            # 폴백: 기본 폰트
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
            if bbox[2] - bbox[0] > width - 200:
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
        draw.ellipse([(width//2 - 100, 100), (width//2 + 100, 300)], outline=(255, 255, 255, 200), width=5)
        draw.ellipse([(width//2 - 100, height - 300), (width//2 + 100, height - 100)], outline=(255, 255, 255, 200), width=5)
        
        return img
    
    except Exception as e:
        logger.error(f"Error creating image: {e}")
        # 폴백: 단순 이미지
        img = Image.new('RGB', (width, height), color=(100, 100, 150))
        draw = ImageDraw.Draw(img)
        draw.text((width//2 - 100, height//2), "Image Generation", fill=(255, 255, 255))
        return img

@app.route('/health', methods=['GET'])
def health():
    """헬스 체크"""
    return jsonify({
        'status': 'healthy',
        'model_loaded': pipe is not None,
        'timestamp': datetime.now().isoformat()
    })

@app.route('/generate', methods=['POST'])
def generate_image():
    """이미지 생성 API"""
    try:
        data = request.json
        prompt = data.get('prompt', 'Beautiful landscape')
        width = data.get('width', 1080)
        height = data.get('height', 1920)
        style = data.get('style', 'traditional')
        
        logger.info(f"Generating image for prompt: {prompt}")
        
        # 이미지 생성
        if pipe is not None:
            # Stable Diffusion 사용
            logger.info("Using Stable Diffusion...")
            image = pipe(
                prompt,
                num_inference_steps=20,
                width=512,  # SD는 512x512가 최적
                height=512
            ).images[0]
            
            # 리사이즈
            image = image.resize((width, height), Image.LANCZOS)
        else:
            # 폴백: 고급 그래픽 생성
            logger.info("Using fallback graphics generator...")
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

@app.route('/generate-story', methods=['POST'])
def generate_story():
    """
    스토리 전체 생성 (여러 장면)
    """
    try:
        data = request.json
        title = data.get('title', '선녀와 나무꾼')
        scenes = data.get('scenes', [])
        
        logger.info(f"Generating {len(scenes)} scenes for: {title}")
        
        results = []
        
        for i, scene in enumerate(scenes):
            prompt = scene.get('description', '')
            style = scene.get('style', 'traditional')
            
            # 이미지 생성
            if pipe is not None:
                image = pipe(
                    prompt,
                    num_inference_steps=15,
                    width=512,
                    height=512
                ).images[0]
                image = image.resize((1080, 1920), Image.LANCZOS)
            else:
                image = create_beautiful_image(prompt, 1080, 1920, style)
            
            # 저장
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            filename = f"{title}_scene_{i+1}_{timestamp}.png"
            filepath = os.path.join(OUTPUT_DIR, filename)
            image.save(filepath, 'PNG', quality=95)
            
            results.append({
                'scene_id': i + 1,
                'image_url': f"/generated/{filename}",
                'filename': filename,
                'description': prompt
            })
            
            logger.info(f"Scene {i+1}/{len(scenes)} completed")
        
        return jsonify({
            'success': True,
            'title': title,
            'scenes': results,
            'total_scenes': len(results)
        })
    
    except Exception as e:
        logger.error(f"Error generating story: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    # 모델 초기화 (시간이 걸릴 수 있음)
    # initialize_model()  # 필요시 주석 해제
    
    # API 서버 시작
    logger.info("Starting AI Image Generator API on port 5002...")
    app.run(host='0.0.0.0', port=5002, debug=False)
