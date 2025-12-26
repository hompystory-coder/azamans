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

def enhance_prompt_for_story(prompt, scene_context=""):
    """
    스토리 장면에 맞게 프롬프트를 개선
    """
    # 기본 고품질 키워드 추가
    enhanced = f"{prompt}, cinematic lighting, highly detailed, 4K quality, professional photography"
    
    # 한국 전통 설화 스타일 추가
    if "선녀" in prompt or "나무꾼" in prompt or "전통" in prompt:
        enhanced += ", traditional Korean style, watercolor painting, elegant composition"
    
    # 자연 배경 강화
    if any(word in prompt for word in ["산", "숲", "하늘", "나무", "mountain", "forest", "sky", "tree"]):
        enhanced += ", natural landscape, atmospheric perspective, beautiful scenery"
    
    # 인물 강화
    if any(word in prompt for word in ["사람", "남자", "여자", "man", "woman", "person", "fairy", "선녀"]):
        enhanced += ", portrait, expressive face, detailed clothing, dynamic pose"
    
    return enhanced

def generate_ai_image_pollinations(prompt, width=1080, height=1920, style="traditional"):
    """
    Pollinations.ai를 사용한 실제 AI 이미지 생성 (완전 무료!)
    여러 AI 모델 시도 및 재시도 로직 포함
    """
    # 프롬프트 개선
    enhanced_prompt = enhance_prompt_for_story(prompt)
    
    # 여러 AI 모델 시도
    models = [
        "flux",           # 최신 고품질 모델
        "turbo",          # 빠른 생성
        "flux-realism",   # 사실적인 이미지
    ]
    
    for model in models:
        try:
            logger.info(f"🎨 Generating AI image with Pollinations.ai ({model}): {prompt}")
            
            # Pollinations.ai API 호출 (완전 무료, API 키 불필요)
            url = (
                f"https://image.pollinations.ai/prompt/{requests.utils.quote(enhanced_prompt)}"
                f"?width={width}&height={height}&model={model}&nologo=true&enhance=true&seed={hash(prompt) % 10000}"
            )
            
            response = requests.get(url, timeout=90)  # 타임아웃 증가
            
            if response.status_code == 200 and len(response.content) > 1000:
                # 이미지 데이터 로드
                img = Image.open(io.BytesIO(response.content))
                
                # 크기 조정 (필요시)
                if img.size != (width, height):
                    img = img.resize((width, height), Image.LANCZOS)
                
                logger.info(f"✅ AI image generated successfully with {model}: {img.size}")
                return img
            else:
                logger.warning(f"⚠️ {model} failed (status: {response.status_code}), trying next model...")
                continue
                
        except Exception as e:
            logger.warning(f"⚠️ {model} error: {e}, trying next model...")
            continue
    
    # 모든 모델 실패 시
    logger.error("❌ All Pollinations AI models failed")
    return None

def create_beautiful_image(prompt, width=1080, height=1920, style="traditional"):
    """
    고품질 이미지 생성 (실제 AI 사용 또는 고급 그래픽)
    """
    try:
        # 먼저 실제 AI 이미지 생성 시도! (최대 3회 재시도)
        for attempt in range(3):
            logger.info(f"🔄 AI 이미지 생성 시도 {attempt + 1}/3...")
            ai_image = generate_ai_image_pollinations(prompt, width, height, style)
            if ai_image:
                logger.info(f"✅ AI 이미지 생성 성공! (시도 {attempt + 1})")
                return ai_image
            
            if attempt < 2:
                import time
                time.sleep(2)  # 재시도 전 대기
        
        logger.warning("⚠️ AI generation failed after 3 attempts, falling back to graphics generator...")
        
        # 폴백: 그래픽 생성
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
    스토리 전체 생성 (여러 장면) - 실제 AI 이미지 생성
    """
    try:
        data = request.json
        title = data.get('title', '선녀와 나무꾼')
        scenes = data.get('scenes', [])
        
        logger.info(f"📖 Starting story generation: '{title}' with {len(scenes)} scenes")
        
        results = []
        
        for i, scene in enumerate(scenes):
            prompt = scene.get('description', scene.get('prompt', ''))
            style = scene.get('style', 'traditional')
            
            logger.info(f"🎬 Generating scene {i+1}/{len(scenes)}: {prompt[:50]}...")
            
            # 실제 AI 이미지 생성 (Pollinations.ai 우선)
            image = create_beautiful_image(prompt, 1080, 1920, style)
            
            # 저장
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            # 파일명을 안전하게 변환
            safe_title = "".join(c for c in title if c.isalnum() or c in (' ', '-', '_')).strip()
            filename = f"{safe_title}_scene_{i+1:02d}_{timestamp}.png"
            filepath = os.path.join(OUTPUT_DIR, filename)
            image.save(filepath, 'PNG', quality=95, optimize=True)
            
            results.append({
                'scene_id': i + 1,
                'image_url': f"/generated/{filename}",
                'filename': filename,
                'description': prompt,
                'width': 1080,
                'height': 1920
            })
            
            logger.info(f"✅ Scene {i+1}/{len(scenes)} completed: {filename}")
        
        logger.info(f"🎉 Story generation completed! Total scenes: {len(results)}")
        
        return jsonify({
            'success': True,
            'title': title,
            'scenes': results,
            'total_scenes': len(results)
        })
    
    except Exception as e:
        logger.error(f"❌ Error generating story: {e}")
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
