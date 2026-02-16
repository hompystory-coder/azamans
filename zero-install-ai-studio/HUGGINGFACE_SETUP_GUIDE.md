# 🤗 Hugging Face API 설정 및 사용 가이드

## 📋 목차
1. [Hugging Face API란?](#hugging-face-api란)
2. [설정 방법](#설정-방법)
3. [테스트 실행](#테스트-실행)
4. [비용 정보](#비용-정보)
5. [Replicate vs Hugging Face](#replicate-vs-hugging-face)
6. [쇼츠 시스템 연동](#쇼츠-시스템-연동)
7. [문제 해결](#문제-해결)

---

## 🎯 Hugging Face API란?

**Hugging Face Inference API**는 AI 모델을 **완전 무료**로 클라우드에서 실행할 수 있는 플랫폼입니다.

### ✨ 주요 특징
- **완전 무료**: 계정만 있으면 무제한 사용
- **GPU 서버 불필요**: 클라우드에서 실행
- **다양한 모델 지원**: SDXL, SD 1.5, SD 2.1 등
- **대기열 방식**: 1~5분 대기 시간
- **개발/테스트 최적**: 빠른 프로토타이핑

### 🆚 Hugging Face의 장단점

#### ✅ 장점
- **무료**: 크레딧/결제 정보 불필요
- **무제한**: 사용량 제한 없음
- **다양한 모델**: 수천 개의 AI 모델 접근 가능
- **커뮤니티**: 활발한 오픈소스 커뮤니티

#### ⚠️ 단점
- **느림**: 대기열 방식으로 1~5분 소요
- **품질**: Replicate보다 낮을 수 있음
- **안정성**: 무료 서비스라 간혹 불안정
- **제한적**: 일부 고급 기능 미지원

---

## 🚀 설정 방법

### Step 1: Hugging Face 계정 생성

1. **웹사이트 방문**: https://huggingface.co
2. **회원가입**: 
   - GitHub 계정으로 로그인 (추천)
   - Google 계정으로 로그인
   - 이메일로 가입
3. **완전 무료**: 결제 정보 불필요

### Step 2: API 토큰 발급

1. **토큰 페이지 이동**: https://huggingface.co/settings/tokens
2. **새 토큰 생성**: 
   - "New token" 버튼 클릭
   - Token name 입력 (예: "ai-shorts-generator")
   - Role: **"read"** 선택 (이미지 생성에는 read만 필요)
3. **토큰 복사**: 
   - `hf_`로 시작하는 토큰 전체 복사
   - ⚠️ **중요**: 토큰은 재발급 가능하므로 안심하세요!

### Step 3: 환경 변수 설정

#### 방법 A: 임시 설정 (현재 세션만)
```bash
export HF_TOKEN='hf_your_token_here'
```

#### 방법 B: 영구 설정 (.bashrc에 추가)
```bash
echo 'export HF_TOKEN="hf_your_token_here"' >> ~/.bashrc
source ~/.bashrc
```

#### 방법 C: 환경 변수 파일 (.env)
```bash
# 프로젝트 루트에 .env 파일 생성
echo 'HF_TOKEN=hf_your_token_here' >> .env
```

### Step 4: 설정 확인

```bash
# 토큰이 설정되었는지 확인
echo $HF_TOKEN

# 결과: hf_... (토큰 출력되면 성공)
```

---

## 🧪 테스트 실행

### 1️⃣ 모의 테스트 (API 호출 없음)

코드 구조와 의존성만 확인:

```bash
cd /home/azamans/webapp/zero-install-ai-studio
python3 test_huggingface_mock.py
```

**예상 출력:**
```
✅ HuggingFaceClient 클래스 임포트 성공
✅ 모든 필수 메서드가 구현되어 있습니다!
✅ 모든 의존성이 설치되어 있습니다!
🎉 Hugging Face API 사용 준비 완료!
```

### 2️⃣ 실제 API 테스트 (이미지 생성)

⏳ **주의**: 대기 시간 1~5분 소요

```bash
cd /home/azamans/webapp/zero-install-ai-studio
python3 test_huggingface_api.py
```

**테스트 항목:**
- ✅ SDXL 이미지 생성
- ✅ 프롬프트 처리
- ✅ 파일 저장 확인

**예상 출력:**
```
🎨 테스트 1/1: 마법의 숲 (SDXL)
📝 프롬프트: A magical forest with glowing mushrooms at night...
⏳ 이미지 생성 중... (대기열 방식, 1~5분 소요 가능)
⏳ 모델 로딩 중... 예상 대기 시간: 20초
✅ 이미지 생성 및 저장 성공!
📁 파일: /tmp/hf_test_forest.png
📊 파일 크기: 1247.3 KB
```

---

## 💰 비용 정보

### 완전 무료!

| 항목 | 비용 | 제한 |
|------|------|------|
| **이미지 생성** | $0 | 무제한 |
| **계정 생성** | $0 | 무료 |
| **API 호출** | $0 | 무제한 |
| **월 사용료** | $0 | 없음 |

### 💡 왜 무료인가?

Hugging Face는 AI 민주화를 목표로 하는 오픈소스 플랫폼입니다:
- 커뮤니티 기여로 운영
- 대기열 방식으로 리소스 최적화
- 프리미엄 서비스(Inference Endpoints)로 수익화

---

## 🆚 Replicate vs Hugging Face

### 비교표

| 항목 | Replicate | Hugging Face |
|------|-----------|--------------|
| **비용** | $5 무료 크레딧 → 유료 | 완전 무료 (무제한) |
| **속도** | 빠름 (30~60초) | 느림 (1~5분) |
| **품질** | 최고 | 중간~높음 |
| **대기열** | 없음 | 있음 (대기 필요) |
| **안정성** | 높음 | 중간 |
| **모델 선택** | 엄선된 모델 | 수천 개 모델 |
| **추천 용도** | 프로덕션 | 개발/테스트 |

### 💡 언제 무엇을 사용할까?

#### Hugging Face 추천 상황
- ✅ 개발 초기 단계
- ✅ 프로토타입 제작
- ✅ 개념 검증 (PoC)
- ✅ 비용 절감 필요
- ✅ 다양한 모델 실험

#### Replicate 추천 상황
- ✅ 프로덕션 환경
- ✅ 빠른 응답 필요
- ✅ 높은 품질 요구
- ✅ 안정성 중요
- ✅ 예산 있음

### 하이브리드 전략 (최적!)

```
개발 단계: Hugging Face (무료)
    ↓
테스트 단계: Replicate ($5 크레딧)
    ↓
소규모 프로덕션: Replicate (유료)
    ↓
대규모 프로덕션: GPU 서버 렌탈
```

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
이미지 생성 API  ← Hugging Face 연동!
- 옵션 1: Hugging Face (무료, 느림)
- 옵션 2: Replicate ($5 크레딧, 빠름)
      ↓
TTS API
- 나레이션 음성 합성
      ↓
비디오 편집
- 장면 조합
- 음악/효과음 추가
      ↓
완성된 쇼츠 (30초)
```

### 코드 연동 예시

#### 1. HuggingFaceClient 사용

```python
from huggingface_client import HuggingFaceClient
import os

# 클라이언트 초기화
api_token = os.getenv("HF_TOKEN")
client = HuggingFaceClient(api_token=api_token)

# 이미지 생성 및 저장
success = client.generate_and_save(
    prompt="A magical forest with glowing mushrooms at night",
    save_path="/tmp/scene1.png",
    model="sdxl"
)

if success:
    print("✅ 이미지 생성 완료!")
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

# 배치 생성
batch_results = client.generate_batch(
    prompts=prompts,
    model="sdxl",
    max_wait_per_image=300  # 각 이미지당 최대 5분 대기
)

# 결과 저장
for i, image_bytes in enumerate(batch_results, 1):
    if image_bytes:
        client.save_image(image_bytes, f"/tmp/scene{i}.png")
        print(f"✅ 장면 {i} 저장 완료")
```

#### 3. Replicate와 폴백 시스템

```python
from replicate_client import ReplicateClient
from huggingface_client import HuggingFaceClient
import os

def generate_image_with_fallback(prompt: str, save_path: str) -> bool:
    """
    Replicate 시도 → 실패 시 Hugging Face로 폴백
    """
    # 1차: Replicate (빠름, 유료)
    replicate_token = os.getenv("REPLICATE_API_TOKEN")
    if replicate_token:
        try:
            print("🚀 Replicate로 이미지 생성 시도...")
            replicate_client = ReplicateClient(api_token=replicate_token)
            image_url = replicate_client.generate_image_sdxl(prompt)
            
            if image_url:
                # URL에서 이미지 다운로드 후 저장
                import requests
                response = requests.get(image_url)
                with open(save_path, 'wb') as f:
                    f.write(response.content)
                print("✅ Replicate 성공!")
                return True
        except Exception as e:
            print(f"⚠️  Replicate 실패: {e}")
    
    # 2차: Hugging Face (느림, 무료)
    hf_token = os.getenv("HF_TOKEN")
    if hf_token:
        try:
            print("🤗 Hugging Face로 폴백...")
            hf_client = HuggingFaceClient(api_token=hf_token)
            success = hf_client.generate_and_save(prompt, save_path)
            
            if success:
                print("✅ Hugging Face 성공!")
                return True
        except Exception as e:
            print(f"❌ Hugging Face 실패: {e}")
    
    print("❌ 모든 이미지 생성 방법 실패")
    return False

# 사용 예시
generate_image_with_fallback(
    prompt="A beautiful sunset over the ocean",
    save_path="/tmp/scene.png"
)
```

---

## 🐛 문제 해결

### Q1: "API token is required" 에러

**원인**: 환경 변수가 설정되지 않음

**해결**:
```bash
# 토큰 설정 확인
echo $HF_TOKEN

# 설정되지 않았다면
export HF_TOKEN='hf_your_token_here'
```

### Q2: "Model is loading" 메시지가 계속 나옴

**원인**: 모델이 콜드 스타트 중 (정상)

**해결**:
- 1~5분 대기 (자동으로 재시도)
- `max_wait_time` 늘리기 (기본 300초)
- 다른 시간대에 재시도

```python
# 대기 시간 늘리기
image_bytes = client.generate_image(
    prompt="...",
    max_wait_time=600  # 10분으로 연장
)
```

### Q3: 이미지 생성이 너무 느림

**원인**: Hugging Face는 대기열 방식

**해결**:
1. **시간대 변경**: 사용자가 적은 시간대 시도
2. **Replicate 사용**: 빠른 생성 필요 시
3. **미리 생성**: 배치로 미리 생성해두기

### Q4: 이미지 품질이 낮음

**원인**: 프롬프트가 부족하거나 모델 선택 문제

**해결**:
```python
# 상세한 프롬프트 사용
prompt = """
A magical forest at night, glowing blue mushrooms,
fireflies flying around, misty atmosphere,
fantasy art style, highly detailed, 4k resolution,
cinematic lighting, digital painting, trending on artstation
"""

# SDXL 모델 사용 (최고 품질)
image_bytes = client.generate_image(
    prompt=prompt,
    model="sdxl"  # SDXL이 가장 좋은 품질
)
```

### Q5: 503 Service Unavailable 에러

**원인**: Hugging Face 서버 과부하 (간혹 발생)

**해결**:
1. 5~10분 후 재시도
2. Replicate로 폴백
3. 다른 모델 시도 (sd15, sd21)

---

## 📚 참고 자료

### 공식 문서
- Hugging Face 공식 사이트: https://huggingface.co
- Inference API 문서: https://huggingface.co/docs/api-inference
- 모델 탐색: https://huggingface.co/models
- Python 클라이언트: https://github.com/huggingface/huggingface_hub

### 지원되는 모델
- **SDXL**: `stabilityai/stable-diffusion-xl-base-1.0` (최고 품질)
- **SD 1.5**: `runwayml/stable-diffusion-v1-5` (빠름)
- **SD 2.1**: `stabilityai/stable-diffusion-2-1` (중간)

### 커뮤니티
- Discord: https://huggingface.co/join/discord
- 포럼: https://discuss.huggingface.co
- GitHub: https://github.com/huggingface

---

## 💡 팁 & 트릭

### 대기 시간 단축 전략

1. **모델 예열**: 처음 호출 후 연속 호출은 빠름
2. **배치 생성**: 여러 이미지를 연속으로 생성
3. **시간대 선택**: 미국/유럽 업무 시간 피하기
4. **모델 선택**: SD 1.5는 SDXL보다 빠름

### 프롬프트 최적화

**좋은 프롬프트**:
```
A magical forest at night, glowing blue mushrooms,
fireflies, misty atmosphere, fantasy art style,
highly detailed, 4k, cinematic lighting,
digital painting, trending on artstation
```

**나쁜 프롬프트**:
```
forest
```

### 비용 최적화 전략

| 단계 | API 선택 | 이유 |
|------|----------|------|
| **개발** | Hugging Face | 무료, 실험 가능 |
| **테스트** | Replicate | $5 크레딧, 빠름 |
| **소규모 운영** | Replicate | 품질 보장 |
| **대규모 운영** | GPU 서버 | 비용 효율 |

---

## 🎯 다음 단계

### ✅ 완료된 작업
- [x] Hugging Face API 클라이언트 구현
- [x] SDXL/SD1.5/SD2.1 모델 지원
- [x] 배치 생성 기능
- [x] 테스트 스크립트 작성
- [x] Replicate와 비교 분석

### 🔜 다음 작업
- [ ] Ollama 나레이션 자동 생성 테스트
- [ ] 장르별 스토리 구조 적용
- [ ] 다국어 쇼츠 생성 파이프라인
- [ ] 전체 시스템 통합 테스트

---

## 🆘 지원

문제가 발생하면:

1. **이 가이드 확인**: [문제 해결](#문제-해결) 섹션
2. **로그 확인**: 에러 메시지 읽기
3. **공식 문서**: https://huggingface.co/docs
4. **Discord**: https://huggingface.co/join/discord

---

**작성일**: 2025-12-27  
**버전**: 1.0.0  
**작성자**: AI Shorts Generator Team
