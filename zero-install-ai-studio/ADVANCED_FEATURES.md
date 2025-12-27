# 🚀 고급 기능 가이드

**작성일**: 2024-12-27  
**버전**: v3.0.0  
**상태**: 구현 가이드

---

## 📋 목차
1. [장르별 스토리 구조 자동 적용](#장르별-스토리-구조-자동-적용)
2. [다국어 쇼츠 생성](#다국어-쇼츠-생성)
3. [GPU 서버 렌탈 가이드](#gpu-서버-렌탈-가이드)

---

## 1️⃣ 장르별 스토리 구조 자동 적용

### 개념

현재 시스템은 모든 스토리에 동일한 5막 구조를 적용합니다.  
**개선**: 장르 자동 인식 → 맞춤 구조 적용

### 지원 장르

| 장르 | 구조 | 특징 |
|------|------|------|
| **동화** | 5막 | 교훈, 해피엔딩, 마법 요소 |
| **액션** | 3막 | 빠른 전개, 클라이맥스 중심 |
| **로맨스** | 5막 | 감정 중심, 갈등 해소 |
| **공포** | 4막 | 긴장감 누적, 반전 엔딩 |
| **코미디** | 3막 | 가벼운 갈등, 해결 |
| **SF** | 영웅의 여정 | 12단계 구조 |

### 구현 방법

```python
# genre_detector.py
import requests

def detect_genre_with_ollama(user_input: str, model: str = "llama3.1:8b") -> dict:
    """
    Ollama로 장르 자동 인식
    
    Returns:
        {
            "genre": "동화",
            "structure": "5막",
            "tone": "따뜻한",
            "keywords": ["마법", "교훈", "해피엔딩"]
        }
    """
    prompt = f"""다음 스토리의 장르를 판단하세요:
"{user_input}"

장르 옵션: 동화, 액션, 로맨스, 공포, 코미디, SF
구조 옵션: 3막, 4막, 5막, 영웅의 여정

JSON 형식으로 응답:
{{"genre": "동화", "structure": "5막", "tone": "따뜻한", "keywords": ["마법", "교훈"]}}

JSON만 출력:"""
    
    response = requests.post(
        "http://localhost:11434/api/generate",
        json={"model": model, "prompt": prompt, "stream": False}
    )
    
    if response.status_code == 200:
        result = response.json()["response"]
        # JSON 파싱
        import json
        return json.loads(result)
    
    return {"genre": "일반", "structure": "5막", "tone": "중립적"}


# 장르별 구조 적용
GENRE_STRUCTURES = {
    "동화": {
        "acts": 5,
        "act_names": ["발단", "전개", "위기", "절정", "결말"],
        "narration_style": "curious",
        "mood_palette": ["따뜻한", "신비로운", "희망찬"]
    },
    "액션": {
        "acts": 3,
        "act_names": ["도입", "갈등", "해결"],
        "narration_style": "dramatic",
        "mood_palette": ["긴박한", "폭발적인", "카타르시스"]
    },
    "로맨스": {
        "acts": 5,
        "act_names": ["만남", "접근", "갈등", "화해", "결합"],
        "narration_style": "calm",
        "mood_palette": ["설레는", "아픈", "감동적인"]
    },
    "공포": {
        "acts": 4,
        "act_names": ["평온", "불안", "공포", "반전"],
        "narration_style": "dramatic",
        "mood_palette": ["불안한", "섬뜩한", "충격적인"]
    }
}


def apply_genre_structure(user_input: str, scenes_count: int) -> dict:
    """
    장르 감지 후 맞춤 구조 적용
    """
    # 1. 장르 감지
    genre_info = detect_genre_with_ollama(user_input)
    genre = genre_info["genre"]
    
    # 2. 해당 장르 구조 로드
    structure = GENRE_STRUCTURES.get(genre, GENRE_STRUCTURES["동화"])
    
    # 3. 씬 배분
    acts_count = structure["acts"]
    scenes_per_act = scenes_count // acts_count
    
    # 4. 씬 생성
    scenes = []
    for act_num in range(acts_count):
        for scene_in_act in range(scenes_per_act):
            scene = {
                "act_name": structure["act_names"][act_num],
                "mood": structure["mood_palette"][act_num % len(structure["mood_palette"])],
                "style": structure["narration_style"]
            }
            scenes.append(scene)
    
    return {
        "genre": genre,
        "structure": structure,
        "scenes": scenes
    }
```

### 사용 예시

```python
# story_generator.py에 통합
from genre_detector import apply_genre_structure

def generate_story_with_genre_detection(user_input: str, duration: int = 30):
    scenes_count = max(5, duration // 4)
    
    # 장르 감지 및 구조 적용
    genre_result = apply_genre_structure(user_input, scenes_count)
    
    logger.info(f"🎭 장르 감지: {genre_result['genre']}")
    logger.info(f"📐 구조: {genre_result['structure']['acts']}막")
    
    # 장르별 맞춤 스토리 생성
    # ...
```

---

## 2️⃣ 다국어 쇼츠 생성

### 지원 언어

| 언어 | Ollama 번역 | TTS 지원 | 품질 |
|------|------------|---------|------|
| 🇰🇷 한국어 | 원본 | ✅ | ⭐⭐⭐⭐⭐ |
| 🇺🇸 영어 | ✅ | ✅ | ⭐⭐⭐⭐⭐ |
| 🇯🇵 일본어 | ✅ | ✅ | ⭐⭐⭐⭐ |
| 🇨🇳 중국어 | ✅ | ✅ | ⭐⭐⭐⭐ |
| 🇪🇸 스페인어 | ✅ | ✅ | ⭐⭐⭐ |

### 구현 방법

```python
# multilang_translator.py
import requests

class MultiLangTranslator:
    """Ollama 기반 다국어 번역기"""
    
    LANGUAGES = {
        "ko": "한국어",
        "en": "English",
        "ja": "日本語",
        "zh": "中文",
        "es": "Español"
    }
    
    def __init__(self, model: str = "llama3.1:8b"):
        self.model = model
        self.base_url = "http://localhost:11434"
    
    def translate_narration(
        self, 
        korean_text: str, 
        target_lang: str = "en"
    ) -> str:
        """
        나레이션을 목표 언어로 번역
        
        Args:
            korean_text: 한국어 나레이션
            target_lang: 목표 언어 코드 (en, ja, zh, es)
            
        Returns:
            번역된 텍스트
        """
        lang_name = self.LANGUAGES.get(target_lang, "English")
        
        prompt = f"""다음 한국어 나레이션을 {lang_name}로 번역하세요.

원문: "{korean_text}"

요구사항:
1. 같은 톤과 느낌 유지
2. 30자 이내 (원문 길이 유지)
3. 자연스러운 구어체
4. 번역문만 출력 (설명 없이)

{lang_name} 번역:"""
        
        response = requests.post(
            f"{self.base_url}/api/generate",
            json={
                "model": self.model,
                "prompt": prompt,
                "stream": False,
                "options": {"temperature": 0.3}
            }
        )
        
        if response.status_code == 200:
            translated = response.json()["response"].strip()
            return translated
        
        return korean_text  # 폴백: 원문 반환
    
    def translate_story_batch(
        self, 
        scenes: list[dict], 
        target_lang: str = "en"
    ) -> list[dict]:
        """
        전체 스토리의 나레이션을 일괄 번역
        
        Args:
            scenes: 씬 리스트 (각 씬에 "narration" 포함)
            target_lang: 목표 언어
            
        Returns:
            번역된 씬 리스트
        """
        translated_scenes = []
        
        for scene in scenes:
            translated_scene = scene.copy()
            
            # 나레이션 번역
            translated_scene["narration"] = self.translate_narration(
                scene["narration"],
                target_lang
            )
            
            # 제목 번역
            translated_scene["title"] = self.translate_narration(
                scene["title"],
                target_lang
            )
            
            translated_scenes.append(translated_scene)
        
        return translated_scenes


# 사용 예시
translator = MultiLangTranslator()

# 한국어 스토리 생성
korean_story = generate_story_script("우주 비행사의 모험", duration=30)

# 영어로 번역
english_story = translator.translate_story_batch(
    korean_story["scenes"], 
    target_lang="en"
)

# 일본어로 번역
japanese_story = translator.translate_story_batch(
    korean_story["scenes"], 
    target_lang="ja"
)
```

### TTS 연동

```python
# Google TTS (다국어 지원)
from gtts import gTTS

def generate_multilang_tts(text: str, lang: str = "ko"):
    """
    다국어 TTS 생성
    
    Args:
        text: 텍스트
        lang: 언어 코드 (ko, en, ja, zh-CN, es)
    """
    tts = gTTS(text=text, lang=lang)
    tts.save(f"narration_{lang}.mp3")
```

---

## 3️⃣ GPU 서버 렌탈 가이드

### 서비스 비교

| 서비스 | GPU | 시간당 비용 | 월 비용 (24/7) | 추천도 |
|--------|-----|------------|----------------|--------|
| **Vast.ai** | RTX 3060 (12GB) | $0.15 | $108 | ⭐⭐⭐⭐⭐ |
| **RunPod** | RTX 3090 (24GB) | $0.20 | $144 | ⭐⭐⭐⭐ |
| **Lambda Labs** | A100 (40GB) | $0.50 | $360 | ⭐⭐⭐ |
| **AWS EC2** | g4dn.xlarge | $0.526 | $379 | ⭐⭐ |

### Vast.ai 설정 가이드 (최저가)

#### Step 1: 계정 생성
```
1. https://vast.ai/ 접속
2. Sign Up (이메일 가입)
3. $10 충전 (초기 테스트용)
```

#### Step 2: GPU 인스턴스 검색
```
Filter:
- GPU: RTX 3060 이상
- VRAM: 12GB 이상
- Disk: 50GB 이상
- Upload: 100 Mbps 이상

Sort: Price (lowest first)

예상 가격: $0.10-0.20/hr
```

#### Step 3: SSH 접속 설정
```bash
# Vast.ai에서 SSH 포트 확인 (예: ssh://root@123.456.78.90:12345)
ssh -p 12345 root@123.456.78.90

# 비밀번호는 대시보드에 표시
```

#### Step 4: ComfyUI 설치
```bash
# 1. 업데이트
apt update && apt upgrade -y

# 2. Python 환경
apt install -y python3-pip python3-venv git

# 3. ComfyUI 클론
cd /root
git clone https://github.com/comfyanonymous/ComfyUI.git
cd ComfyUI

# 4. 의존성 설치
pip install -r requirements.txt

# 5. SDXL 모델 다운로드
cd models/checkpoints
wget https://huggingface.co/stabilityai/stable-diffusion-xl-base-1.0/resolve/main/sd_xl_base_1.0.safetensors

# 6. 실행
cd /root/ComfyUI
python main.py --listen 0.0.0.0 --port 8188

# 7. 포트 포워딩 (Vast.ai 대시보드에서 8188 포트 열기)
# 브라우저: http://[YOUR_INSTANCE_IP]:8188
```

#### Step 5: 자동 시작 설정
```bash
# systemd 서비스 생성
cat > /etc/systemd/system/comfyui.service << 'EOF'
[Unit]
Description=ComfyUI
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/ComfyUI
ExecStart=/usr/bin/python3 main.py --listen 0.0.0.0 --port 8188
Restart=always

[Install]
WantedBy=multi-user.target
EOF

# 서비스 활성화
systemctl enable comfyui
systemctl start comfyui
systemctl status comfyui
```

### RunPod 설정 가이드 (고성능)

#### Step 1: 계정 생성
```
1. https://runpod.io/ 접속
2. Sign Up
3. $25 충전
```

#### Step 2: Pod 생성
```
Template: RunPod PyTorch
GPU: RTX 3090 (24GB)
Disk: 100GB

예상 가격: $0.20/hr
```

#### Step 3: Jupyter 또는 SSH 접속
```
RunPod는 Jupyter Notebook 제공
또는 SSH로 직접 접속 가능
```

#### Step 4: ComfyUI 설치 (동일)
```bash
# Vast.ai와 동일한 설치 스크립트 사용
```

### 비용 최적화 팁

1. **On-Demand vs 계약형**
   - On-Demand: 시간당 과금, 유연함
   - 계약형 (Reserved): 월 단위, 30-50% 저렴

2. **Spot 인스턴스 활용**
   - Vast.ai/RunPod의 Spot 인스턴스
   - 50-70% 할인 (중단 위험 있음)
   - 개발/테스트용 최적

3. **사용 패턴 최적화**
   - 야간/주말만 사용: 월 $30-50
   - 24/7 운영: 월 $100-150
   - 배치 작업 (1시간 집중): 월 $10-20

---

## 🎯 최종 구현 체크리스트

### 이미지/영상 생성
- [x] Replicate API 연동
- [x] Hugging Face API 연동
- [ ] 로컬 ComfyUI 설치 (GPU 서버)

### 나레이션 생성
- [x] 75개 고정 나레이션 풀
- [x] Ollama 자동 생성 시스템
- [ ] 장르별 맞춤 나레이션

### 스토리 구조
- [x] 5막 구조 (기본)
- [ ] 장르 자동 감지
- [ ] 장르별 맞춤 구조

### 다국어 지원
- [x] 한국어 (기본)
- [ ] Ollama 번역 시스템
- [ ] 다국어 TTS 연동

### 서버 인프라
- [ ] GPU 서버 렌탈 (Vast.ai/RunPod)
- [ ] ComfyUI 설치 및 설정
- [ ] API 엔드포인트 연동

---

## 📞 관련 문서

- [FREE_IMAGE_VIDEO_AI.md](./FREE_IMAGE_VIDEO_AI.md) - 무료 이미지/영상 AI 가이드
- [SERVER_AI_CAPABILITIES.md](./SERVER_AI_CAPABILITIES.md) - 서버 AI 기능 분석
- [OLLAMA_INTEGRATION.md](./OLLAMA_INTEGRATION.md) - Ollama 통합 가이드

---

**© 2024 Zero-Install AI Studio. All rights reserved.**
