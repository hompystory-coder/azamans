# 🎨 Replicate API 설정 및 사용 가이드

## 📋 목차
1. [Replicate API란?](#replicate-api란)
2. [설정 방법](#설정-방법)
3. [테스트 실행](#테스트-실행)
4. [비용 정보](#비용-정보)
5. [쇼츠 시스템 연동](#쇼츠-시스템-연동)
6. [문제 해결](#문제-해결)

---

## 🎯 Replicate API란?

**Replicate**는 AI 모델을 클라우드에서 간편하게 실행할 수 있는 플랫폼입니다.

### ✨ 주요 특징
- **GPU 서버 불필요**: 클라우드에서 실행
- **초기 무료 크레딧**: $5 제공 (약 70개 쇼츠 생성 가능)
- **다양한 모델 지원**: Stable Diffusion XL, FLUX.1, AnimateDiff 등
- **빠른 생성 속도**: 이미지 30초, 영상 2~5분
- **API 간편 연동**: Python SDK 제공

### 🆚 로컬 GPU vs Replicate API

| 항목 | 로컬 GPU | Replicate API |
|------|----------|---------------|
| **초기 비용** | GPU 서버 필요 (수백만원) | 무료 ($5 크레딧) |
| **유지 비용** | 전기세, 유지보수 | 사용량에 따라 과금 |
| **설정 난이도** | 높음 (CUDA, 드라이버 등) | 낮음 (API 토큰만) |
| **생성 속도** | 빠름 (로컬) | 빠름 (클라우드) |
| **확장성** | 제한적 | 무제한 |

---

## 🚀 설정 방법

### Step 1: Replicate 계정 생성

1. **웹사이트 방문**: https://replicate.com
2. **회원가입**: 
   - GitHub 계정으로 로그인 (추천)
   - Google 계정으로 로그인
   - 이메일로 가입
3. **무료 크레딧 확인**: 계정 생성 시 $5 자동 지급

### Step 2: API 토큰 발급

1. **API 토큰 페이지 이동**: https://replicate.com/account/api-tokens
2. **토큰 생성**: 
   - "Create token" 버튼 클릭
   - 토큰 이름 입력 (예: "ai-shorts-generator")
3. **토큰 복사**: 
   - `r8_`로 시작하는 토큰 전체 복사
   - ⚠️ **중요**: 토큰은 한 번만 표시되므로 안전한 곳에 저장!

### Step 3: 환경 변수 설정

#### 방법 A: 임시 설정 (현재 세션만)
```bash
export REPLICATE_API_TOKEN='r8_your_token_here'
```

#### 방법 B: 영구 설정 (.bashrc에 추가)
```bash
echo 'export REPLICATE_API_TOKEN="r8_your_token_here"' >> ~/.bashrc
source ~/.bashrc
```

#### 방법 C: 환경 변수 파일 (.env)
```bash
# 프로젝트 루트에 .env 파일 생성
echo 'REPLICATE_API_TOKEN=r8_your_token_here' > .env
```

### Step 4: 설정 확인

```bash
# 토큰이 설정되었는지 확인
echo $REPLICATE_API_TOKEN

# 결과: r8_... (토큰 출력되면 성공)
```

---

## 🧪 테스트 실행

### 1️⃣ 모의 테스트 (API 호출 없음)

코드 구조와 의존성만 확인:

```bash
cd /home/azamans/webapp/zero-install-ai-studio
python3 test_replicate_mock.py
```

**예상 출력:**
```
✅ ReplicateClient 클래스 임포트 성공
✅ 모든 필수 메서드가 구현되어 있습니다!
✅ 모든 의존성이 설치되어 있습니다!
🎉 Replicate API 사용 준비 완료!
```

### 2️⃣ 실제 API 테스트 (이미지 생성)

⚠️ **주의**: 실제 크레딧 소비 ($0.003/장)

```bash
cd /home/azamans/webapp/zero-install-ai-studio
python3 test_replicate_api.py
```

**테스트 항목:**
- ✅ SDXL 이미지 생성 (1024x1024)
- ✅ 프롬프트 처리
- ✅ URL 반환 확인

**예상 출력:**
```
🎨 테스트 1/1: 마법의 숲 (SDXL)
📝 프롬프트: A magical forest with glowing mushrooms at night...
⏳ 이미지 생성 중... (약 30-60초 소요)
✅ 이미지 생성 성공!
🔗 URL: https://replicate.delivery/...
```

---

## 💰 비용 정보

### 무료 크레딧
- **초기 제공**: $5
- **쇼츠 생성 가능량**: 약 70개
- **유효기간**: 무제한

### 유료 사용 시 비용 (무료 크레딧 소진 후)

| 항목 | 모델 | 비용 | 예시 |
|------|------|------|------|
| **이미지 생성** | SDXL | $0.003/장 | 1,000장 = $3 |
| **이미지 생성** | FLUX.1 | $0.004/장 | 1,000장 = $4 |
| **영상 생성** | AnimateDiff | $0.05/10초 | 100개(3초) = $15 |

### 쇼츠 1개당 예상 비용

**구성**: 이미지 7장 + 영상 1개 (3초)

```
이미지: 7장 × $0.003 = $0.021
영상:   1개 × $0.050 = $0.050
------------------------
합계:                   $0.071 (약 100원)
```

### 월간 예상 비용

| 쇼츠 개수 | 비용 (USD) | 비용 (KRW) |
|-----------|-----------|-----------|
| 100개 | $7.1 | 약 1만원 |
| 500개 | $35.5 | 약 5만원 |
| 1,000개 | $71 | 약 10만원 |
| 10,000개 | $710 | 약 100만원 |

### 💡 비용 절감 팁

1. **개발/테스트 단계**: Hugging Face API (무료) 사용
2. **프로덕션**: Replicate API 사용
3. **장기 운영**: GPU 서버 렌탈 (월 $50~100) 고려
4. **이미지 재사용**: 동일한 씬은 이미지 캐싱
5. **배치 생성**: 한 번에 여러 이미지 생성으로 오버헤드 감소

---

## 🔗 쇼츠 시스템 연동

### 현재 시스템 구조

```
사용자 입력 (스토리)
      ↓
Ollama (llama3.1:8b)
- 스토리 분석
- 5막 구조 생성
- 이미지 프롬프트 생성
      ↓
Replicate API  ← 여기에 연동!
- 이미지 생성 (SDXL/FLUX)
- 영상 생성 (AnimateDiff)
      ↓
TTS API (ElevenLabs/Google)
- 나레이션 음성 합성
      ↓
비디오 편집
- 장면 조합
- 음악/효과음 추가
      ↓
완성된 쇼츠 (30초)
```

### 코드 연동 예시

#### 1. ReplicateClient 사용

```python
from replicate_client import ReplicateClient
import os

# 클라이언트 초기화
api_token = os.getenv("REPLICATE_API_TOKEN")
client = ReplicateClient(api_token=api_token)

# 이미지 생성
image_url = client.generate_image_sdxl(
    prompt="A magical forest with glowing mushrooms at night",
    negative_prompt="blurry, low quality",
    width=1024,
    height=1024
)

print(f"생성된 이미지: {image_url}")
```

#### 2. 배치 생성 (쇼츠용)

```python
# 쇼츠 7개 장면 이미지 일괄 생성
prompts = [
    "Opening scene: A young astronaut looking at stars",
    "Training: Astronaut in simulation room",
    "Launch: Rocket taking off into space",
    # ... 4개 더
]

images = client.generate_image_batch(
    prompts=prompts,
    model="sdxl",
    width=1024,
    height=1024
)

for i, img_url in enumerate(images, 1):
    print(f"장면 {i}: {img_url}")
```

#### 3. story_generator.py 연동

```python
# story_generator.py에 추가

from replicate_client import ReplicateClient

# ... (기존 코드)

def generate_scene_images(scenes, api_token):
    """각 장면의 이미지를 Replicate로 생성"""
    client = ReplicateClient(api_token=api_token)
    
    for scene in scenes:
        # 영어 프롬프트 사용
        image_url = client.generate_image_sdxl(
            prompt=scene['description'],  # 이미 영어로 작성됨
            negative_prompt="blurry, low quality, distorted",
            width=1024,
            height=1024
        )
        
        scene['image_url'] = image_url
    
    return scenes
```

---

## 🐛 문제 해결

### Q1: "API token is required" 에러

**원인**: 환경 변수가 설정되지 않음

**해결**:
```bash
# 토큰 설정 확인
echo $REPLICATE_API_TOKEN

# 설정되지 않았다면
export REPLICATE_API_TOKEN='r8_your_token_here'
```

### Q2: "Insufficient credits" 에러

**원인**: 무료 크레딧 소진

**해결**:
1. 크레딧 확인: https://replicate.com/account/billing
2. 결제 정보 등록 또는 Hugging Face API로 전환

### Q3: "Model not found" 에러

**원인**: 모델명이 잘못됨

**해결**:
```python
# 올바른 모델명 사용
client.generate_image_sdxl(...)  # ✅
client.generate_image_flux(...)  # ✅
client.generate_video_animatediff(...)  # ✅
```

### Q4: 이미지 생성이 느림

**원인**: 클라우드 큐 대기

**해결**:
- 평균 30~60초 소요 (정상)
- 배치 생성으로 오버헤드 감소
- 급한 경우 Hugging Face API 병행

### Q5: 이미지 품질이 낮음

**원인**: 프롬프트가 부족하거나 부정 프롬프트 누락

**해결**:
```python
# 상세한 프롬프트 사용
prompt = """
A magical forest at night, glowing blue mushrooms, 
fireflies, misty atmosphere, fantasy art style, 
highly detailed, 4k, cinematic lighting
"""

negative_prompt = """
blurry, low quality, distorted, ugly, 
bad anatomy, poorly drawn
"""

image_url = client.generate_image_sdxl(
    prompt=prompt,
    negative_prompt=negative_prompt,
    width=1024,
    height=1024
)
```

---

## 📊 크레딧 모니터링

### 실시간 확인

https://replicate.com/account/billing

**표시 정보:**
- 현재 잔액
- 사용 내역
- 모델별 비용
- 월간 통계

### 알림 설정

1. Billing 페이지 이동
2. "Set spending limit" 클릭
3. 월 예산 설정 (예: $10)
4. 알림 이메일 활성화

---

## 🎯 다음 단계

### ✅ 완료된 작업
- [x] Replicate API 클라이언트 구현
- [x] SDXL/FLUX 이미지 생성 지원
- [x] AnimateDiff 영상 생성 지원
- [x] 배치 생성 기능
- [x] 테스트 스크립트 작성

### 🔜 다음 작업
- [ ] Hugging Face API 연동 (무료 대안)
- [ ] story_generator.py 통합
- [ ] 이미지 캐싱 시스템
- [ ] 비용 최적화
- [ ] 에러 핸들링 강화

---

## 📚 참고 자료

### 공식 문서
- Replicate 공식 사이트: https://replicate.com
- API 문서: https://replicate.com/docs
- Python SDK: https://github.com/replicate/replicate-python
- 모델 탐색: https://replicate.com/explore

### 지원되는 모델
- **SDXL**: `stability-ai/sdxl`
- **FLUX.1**: `black-forest-labs/flux-schnell`
- **AnimateDiff**: `lucataco/animate-diff`

### 커뮤니티
- Discord: https://discord.gg/replicate
- GitHub Issues: https://github.com/replicate/replicate-python/issues

---

## 💡 팁 & 트릭

### 프롬프트 최적화

**좋은 프롬프트**:
```
A magical forest at night, glowing blue mushrooms,
fireflies flying around, misty atmosphere,
fantasy art style, highly detailed, 4k resolution,
cinematic lighting, trending on artstation
```

**나쁜 프롬프트**:
```
forest
```

### 비용 최적화 전략

1. **개발 단계**: Hugging Face (무료)
2. **테스트 단계**: Replicate ($5 크레딧)
3. **소규모 프로덕션**: Replicate (pay-as-you-go)
4. **대규모 프로덕션**: GPU 서버 렌탈

### 이미지 재사용

```python
# 이미지 캐싱 예시
import hashlib
import json

def get_image_cache_key(prompt, params):
    """프롬프트와 파라미터로 캐시 키 생성"""
    cache_data = {
        "prompt": prompt,
        "params": params
    }
    cache_str = json.dumps(cache_data, sort_keys=True)
    return hashlib.md5(cache_str.encode()).hexdigest()

# 사용 예시
cache_key = get_image_cache_key(prompt, {"width": 1024, "height": 1024})
if cache_key in image_cache:
    image_url = image_cache[cache_key]  # 캐시에서 가져오기
else:
    image_url = client.generate_image_sdxl(prompt)  # 새로 생성
    image_cache[cache_key] = image_url  # 캐시에 저장
```

---

## 🆘 지원

문제가 발생하면:

1. **이 가이드 확인**: [문제 해결](#문제-해결) 섹션
2. **로그 확인**: 에러 메시지 읽기
3. **공식 문서**: https://replicate.com/docs
4. **GitHub Issues**: 버그 리포트

---

**작성일**: 2025-12-27  
**버전**: 1.0.0  
**작성자**: AI Shorts Generator Team
