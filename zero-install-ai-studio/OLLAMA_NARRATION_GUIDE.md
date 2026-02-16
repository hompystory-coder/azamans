# 🤖 Ollama 나레이션 자동 생성 가이드

## 📋 목차
1. [개요](#개요)
2. [기능](#기능)
3. [설정 방법](#설정-방법)
4. [테스트 실행](#테스트-실행)
5. [story_generator 통합](#story_generator-통합)
6. [비교: 고정 풀 vs AI 생성](#비교-고정-풀-vs-ai-생성)
7. [문제 해결](#문제-해결)

---

## 🎯 개요

### 기존 시스템: 고정 나레이션 풀

**문제점**:
- ❌ 75개 고정 나레이션 (5막 × 15개)
- ❌ 7씬 생성 시 순차 선택으로 유연성 부족
- ❌ 스토리 주제와 무관하게 동일한 나레이션
- ❌ 사용자 커스터마이징 불가

### 새 시스템: Ollama AI 자동 생성

**장점**:
- ✅ 무한 나레이션 생성 가능
- ✅ 스토리 주제 맞춤 생성
- ✅ 막/씬별 분위기 반영
- ✅ 스타일 선택 가능 (curious/dramatic/calm)
- ✅ 완전 무료 (로컬 AI)
- ✅ 100% 고유 나레이션

---

## 🎨 기능

### 1. 씬별 맞춤 나레이션 생성

```python
narration = generator.generate_narration(
    scene_number=1,           # 씬 번호 (1~7)
    act_name="발단",           # 막 이름
    korean_mood="신비로운",     # 분위기
    scene_title="시작",        # 씬 제목
    user_input="우주 비행사",   # 스토리 주제
    style="curious"            # 스타일
)
```

**예시 출력**:
```
"우주를 향해 날아가는 그들에게 갑자기 생긴 미스테리..."
```

### 2. 배치 생성 (7씬 일괄)

```python
scenes_info = [
    {"scene_number": 1, "act_name": "발단", ...},
    {"scene_number": 2, "act_name": "전개", ...},
    # ... 5개 더
]

narrations = generator.generate_batch(scenes_info)
# 결과: ['나레이션1', '나레이션2', ..., '나레이션7']
```

### 3. 스타일 선택

| 스타일 | 설명 | 예시 |
|--------|------|------|
| **curious** | 궁금증 유발, 호기심 자극 (30자) | "여러분, 이건 믿기 힘든 이야기예요." |
| **dramatic** | 극적, 감정적, 생동감 (35자) | "이제는 목숨을 건 선택을 할 수밖에요." |
| **calm** | 차분, 여운, 서정적 (30자) | "별빛 가득한 밤하늘, 새로운 시작이네요." |

---

## 🚀 설정 방법

### Step 1: Ollama 서비스 확인

```bash
# Ollama가 실행 중인지 확인
ollama list

# 예상 출력:
# NAME                ID              SIZE      MODIFIED
# llama3.1:8b         46e0c10c039e    4.9 GB    13 days ago
# deepseek-r1:1.5b    e0979632db5a    1.1 GB    13 days ago
```

### Step 2: Ollama 서비스 시작 (미실행 시)

```bash
# 백그라운드 실행
ollama serve &

# 또는 터미널에서 직접 실행
ollama serve
```

### Step 3: 설정 확인

```bash
# Ollama API 테스트
curl http://localhost:11434/api/tags

# 정상 출력: {"models":[...]}
```

---

## 🧪 테스트 실행

### 1️⃣ 기본 테스트

```bash
cd /home/azamans/webapp/zero-install-ai-studio
python3 test_ollama_narration.py
```

**테스트 항목**:
- ✅ Ollama 서비스 상태 확인
- ✅ 단일 나레이션 생성 (3가지 스타일)
- ✅ 배치 나레이션 생성 (7씬)
- ✅ 다양한 테마 테스트 (동화/SF/액션)
- ✅ 품질 평가 (길이/종결어미/이모티콘)

**예상 결과**:
```
🎯 총 4/4 테스트 통과 (100%)
🎉 모든 테스트 성공!
```

### 2️⃣ 개별 테스트

```bash
# ollama_narration_generator.py 직접 실행
cd ai-backend
python3 ollama_narration_generator.py
```

---

## 🔗 story_generator 통합

### 기존 코드 (고정 나레이션 풀)

```python
# story_generator.py (기존)
GLOBAL_NARRATION_POOL = [
    "여러분, 이 이야기 한번 들어보세요.",  # 고정 1
    "그런데 여기서 예상치 못한 일이 벌어졌죠.",  # 고정 2
    # ... 73개 더
]

narration = GLOBAL_NARRATION_POOL[narration_idx]  # 순차 선택
```

### 새 코드 (Ollama AI 생성)

```python
# story_generator.py (개선)
from ollama_narration_generator import OllamaNarrationGenerator

# 초기화 (한 번만)
narration_generator = OllamaNarrationGenerator()

# 씬별 생성
if narration_generator.enabled:
    # AI 자동 생성
    narration = narration_generator.generate_narration(
        scene_number=scene_number,
        act_name=act_name,
        korean_mood=korean_mood,
        scene_title=f"{act_name} {scene_in_act+1}",
        user_input=user_input,
        style="curious"
    )
else:
    # 폴백: 고정 풀 사용
    narration = GLOBAL_NARRATION_POOL[narration_idx]
```

### 통합 예시 (전체)

```python
def generate_custom_story(user_input: str, scenes_count: int, scene_duration: float):
    """5막 구조 스토리 생성 with AI 나레이션"""
    
    # Ollama 나레이션 생성기 초기화
    narration_gen = OllamaNarrationGenerator()
    
    # ... (기존 5막 구조 로직)
    
    for scene_number in range(1, scenes_count + 1):
        # 막 정보 가져오기
        act_data = ACT_SETTINGS[current_act]
        act_name = act_data["name"]
        korean_mood = act_data["korean_moods"][scene_in_act]
        
        # AI 나레이션 생성
        if narration_gen.enabled:
            narration = narration_gen.generate_narration(
                scene_number=scene_number,
                act_name=act_name,
                korean_mood=korean_mood,
                scene_title=f"{act_name} {scene_in_act+1}",
                user_input=user_input,
                style="curious"
            )
        
        # 폴백: 고정 풀
        if not narration or not narration_gen.enabled:
            narration = GLOBAL_NARRATION_POOL[narration_idx]
        
        # 씬 구성
        scene = {
            "scene_number": scene_number,
            "narration": narration,
            # ... (나머지 필드)
        }
        
        scenes.append(scene)
    
    return {
        "title": title,
        "scenes": scenes,
        # ...
    }
```

---

## 📊 비교: 고정 풀 vs AI 생성

### 특징 비교

| 항목 | 고정 나레이션 풀 | Ollama AI 생성 |
|------|------------------|----------------|
| **개수** | 75개 고정 | 무한 |
| **맞춤화** | ❌ 불가 | ✅ 스토리별 맞춤 |
| **중복** | ✅ 없음 | ✅ 없음 |
| **품질** | 중간 | 높음 |
| **속도** | 즉시 (0초) | 느림 (씬당 10초) |
| **비용** | 무료 | 무료 (로컬) |
| **의존성** | 없음 | Ollama 필요 |
| **오프라인** | ✅ 가능 | ⚠️  Ollama 필요 |

### 성능 비교

#### 고정 풀
```
생성 시간: 0초 (즉시)
메모리: 최소
품질: 중간 (범용)
```

#### Ollama AI
```
생성 시간: 씬당 약 10초 (7씬 = 70초)
메모리: Ollama 실행 필요 (~4GB)
품질: 높음 (맞춤형)
```

### 권장 사용 시나리오

#### 고정 풀 사용
- ✅ 빠른 프로토타입 제작
- ✅ Ollama 미실행 환경
- ✅ 범용 나레이션으로 충분
- ✅ 오프라인 환경

#### Ollama AI 사용
- ✅ 프로덕션 환경
- ✅ 스토리별 맞춤 나레이션 필요
- ✅ 고품질 쇼츠 제작
- ✅ Ollama 실행 가능 환경

---

## 🛠️ API 참조

### OllamaNarrationGenerator 클래스

```python
class OllamaNarrationGenerator:
    def __init__(self, base_url="http://localhost:11434", model="llama3.1:8b"):
        """초기화"""
        
    def generate_narration(
        self,
        scene_number: int,
        act_name: str,
        korean_mood: str,
        scene_title: str,
        user_input: str,
        style: str = "curious"
    ) -> Optional[str]:
        """씬별 나레이션 생성"""
        
    def generate_batch(
        self,
        scenes_info: List[dict]
    ) -> List[Optional[str]]:
        """배치 나레이션 생성"""
```

### 파라미터 상세

#### scene_number
- **타입**: `int`
- **범위**: 1~7
- **설명**: 씬 번호

#### act_name
- **타입**: `str`
- **값**: "발단" | "전개" | "위기" | "절정" | "결말"
- **설명**: 5막 구조의 막 이름

#### korean_mood
- **타입**: `str`
- **예시**: "신비로운", "긴장감 넘치는", "충격적인" 등
- **설명**: 씬의 분위기

#### scene_title
- **타입**: `str`
- **예시**: "시작", "결정적 순간" 등
- **설명**: 씬 제목

#### user_input
- **타입**: `str`
- **예시**: "우주 비행사의 모험"
- **설명**: 사용자가 입력한 스토리 주제

#### style
- **타입**: `str`
- **값**: "curious" | "dramatic" | "calm"
- **기본값**: "curious"
- **설명**: 나레이션 스타일

---

## 🐛 문제 해결

### Q1: "Ollama 서비스 미실행" 에러

**원인**: Ollama가 실행되지 않음

**해결**:
```bash
# 서비스 시작
ollama serve &

# 상태 확인
ollama list
```

### Q2: 나레이션 생성이 느림 (10초/씬)

**원인**: Ollama는 로컬 LLM으로 추론 시간 소요

**해결**:
- **방법 1**: 배치 생성으로 한 번에 7씬 생성
- **방법 2**: 빠른 모델 사용 (`deepseek-r1:1.5b`)
- **방법 3**: 고정 풀로 폴백 설정

```python
# 빠른 모델 사용
generator = OllamaNarrationGenerator(model="deepseek-r1:1.5b")
```

### Q3: 나레이션이 40자를 초과함

**원인**: LLM이 긴 응답 생성

**해결**: 자동 절단 (코드에 이미 구현됨)
```python
if len(narration) > 40:
    narration = narration[:37] + "..."
```

### Q4: 나레이션이 영어로 생성됨

**원인**: 프롬프트에 한국어 명시 부족

**해결**: 프롬프트 개선 (코드에 이미 구현됨)
```python
prompt = """당신은 30초 쇼츠 영상의 나레이션 작가입니다.
반말 종결어미 사용 (예: ~어요, ~죠, ~네요)
시청자에게 직접 말하듯 친근하게
"""
```

### Q5: Ollama 연결 실패

**원인**: 포트 충돌 또는 방화벽

**해결**:
```bash
# 포트 확인
netstat -tuln | grep 11434

# Ollama 재시작
pkill ollama
ollama serve &
```

---

## 📈 성능 최적화

### 1. 배치 생성 사용

❌ **비효율**:
```python
for i in range(7):
    narration = generator.generate_narration(...)  # 7번 호출
```

✅ **효율적**:
```python
narrations = generator.generate_batch(scenes_info)  # 1번 호출
```

### 2. 빠른 모델 선택

| 모델 | 크기 | 속도 | 품질 |
|------|------|------|------|
| llama3.1:8b | 4.9GB | 보통 | 높음 ⭐ |
| deepseek-r1:1.5b | 1.1GB | 빠름 | 중간 |

### 3. 폴백 시스템

```python
if narration_gen.enabled:
    narration = narration_gen.generate_narration(...)
else:
    narration = FIXED_NARRATION_POOL[idx]  # 폴백
```

---

## 🎯 다음 단계

### ✅ 완료된 작업
- [x] Ollama 나레이션 생성기 구현
- [x] 배치 생성 기능
- [x] 3가지 스타일 지원
- [x] 품질 검증 로직
- [x] 테스트 스크립트 작성
- [x] 모든 테스트 통과 (100%)

### 🔜 다음 작업
- [ ] story_generator.py 통합
- [ ] 고정 풀 → AI 생성 전환
- [ ] 성능 벤치마크
- [ ] 사용자 피드백 수집
- [ ] 스타일 커스터마이징 UI

---

## 💡 팁 & 트릭

### 프롬프트 커스터마이징

```python
# 장르별 프롬프트 템플릿
if genre == "horror":
    style_instruction = "공포스럽고 소름 끼치는 구어체로"
elif genre == "comedy":
    style_instruction = "유머러스하고 가벼운 구어체로"
```

### 캐싱으로 속도 개선

```python
# 동일한 주제는 캐시에서 가져오기
cache_key = f"{user_input}_{scene_number}_{act_name}"
if cache_key in narration_cache:
    return narration_cache[cache_key]
```

---

## 📚 참고 자료

### Ollama 문서
- 공식 사이트: https://ollama.ai
- 모델 목록: https://ollama.ai/library
- GitHub: https://github.com/ollama/ollama

### 관련 문서
- `OLLAMA_INTEGRATION.md` - Ollama 통합 가이드
- `NARRATION_FIX_SUMMARY.md` - 나레이션 중복 제거
- `SERVER_AI_CAPABILITIES.md` - 서버 AI 기능

---

**작성일**: 2025-12-27  
**버전**: 1.0.0  
**작성자**: AI Shorts Generator Team
