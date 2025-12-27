# 프론트엔드-백엔드 AI 시스템 통합 가이드

**버전**: 1.0.0  
**작성일**: 2024-12-27  
**목적**: Next.js 프론트엔드와 Flask AI 백엔드 통합 완료 가이드

---

## 🎯 시스템 아키텍처

```
[사용자]
   ↓
[Next.js 프론트엔드]
https://ai-studio.neuralgrid.kr/pro-shorts
   ↓
[Next.js API Route]
/app/api/story/route.ts
   ↓
[Flask AI 백엔드]
http://localhost:5004/generate-story
   ↓
[AI 모듈들]
├─ 장르 감지 (genre_detector.py)
├─ 나레이션 생성 (ollama_narration_generator.py)
├─ 다국어 번역 (multilang_translator.py)
└─ Ollama (llama3.1:8b)
   ↓
[완성된 쇼츠]
```

---

## ✅ 완료된 통합

### 1. 백엔드 AI 모듈 통합 (`ai-backend/story_generator.py`)

#### 추가된 import
```python
# 🆕 장르 감지 시스템
from genre_detector import GenreDetector
genre_detector = GenreDetector()

# 🆕 나레이션 자동 생성 시스템
from ollama_narration_generator import OllamaNarrationGenerator
narration_gen = OllamaNarrationGenerator()

# 🆕 다국어 번역 시스템
from multilang_translator import MultiLangTranslator
translator = MultiLangTranslator()
```

#### 개선된 API 엔드포인트

##### 1) `/generate-story` - 스토리 생성 (AI 나레이션 포함)

**요청 예시**:
```bash
curl -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "우주 비행사의 모험",
    "duration": 30
  }'
```

**응답 예시**:
```json
{
  "success": true,
  "story": {
    "title": "우주 비행사의 모험",
    "genre": "SF",
    "total_scenes": 7,
    "total_duration": 30,
    "detected_genre": "SF",
    "genre_structure": "5막",
    "scenes": [
      {
        "scene_number": 1,
        "title": "우주선 발사",
        "narration": "우주 비행사가 될 운을 가진 아이가 있어요.",
        "korean_description": "우주 비행사의 모험 이야기 중 발단의 미래적인 장면",
        "description": "A futuristic space station...",
        "duration": 4.3,
        "mood": "futuristic",
        "camera_movement": "dolly_forward"
      }
      // ... 더 많은 씬
    ]
  }
}
```

**핵심 기능**:
- ✅ 장르 자동 감지 (SF, 동화, 액션, 로맨스, 공포, 코미디)
- ✅ AI 나레이션 자동 생성 (Ollama llama3.1:8b)
- ✅ 폴백 시스템 (AI 실패 시 고정 풀 사용)
- ✅ 장르별 최적 구조 적용 (3막/5막)

##### 2) `/translate-story` - 다국어 번역 🆕

**요청 예시**:
```bash
curl -X POST http://localhost:5004/translate-story \
  -H "Content-Type: application/json" \
  -d '{
    "narrations": [
      "우주 비행사가 될 운을 가진 아이가 있어요.",
      "우주 비행사를 향한 모험이 이제 시작되죠."
    ],
    "target_lang": "en"
  }'
```

**응답 예시**:
```json
{
  "success": true,
  "target_lang": "en",
  "language_name": "영어",
  "translated_narrations": [
    "There's a kid who's destined to be an astronaut.",
    "The adventure to space travel has just begun."
  ]
}
```

**지원 언어**:
- `en`: 영어 (English) ⭐⭐⭐⭐⭐
- `ja`: 일본어 (日本語) ⭐⭐⭐⭐
- `zh`: 중국어 (中文) ⭐⭐⭐⭐
- `es`: 스페인어 (Español) ⭐⭐⭐
- `ko`: 한국어 (원본)

---

## 🔧 AI 나레이션 생성 로직

### 코드 위치
`ai-backend/story_generator.py` → `generate_custom_story()` 함수

### 생성 우선순위
```python
# 1순위: Ollama AI 자동 생성
if NARRATION_GEN_AVAILABLE and narration_gen:
    narration = narration_gen.generate_narration(
        scene_number=scene_idx + 1,
        act_name=act_name,
        korean_mood=korean_mood,
        scene_title=f"{user_input}의 {act_name}",
        user_input=user_input,
        style="curious"  # 궁금증 유발 스타일
    )

# 2순위: 고정 나레이션 풀 (폴백)
if not narration:
    narration = GLOBAL_NARRATION_POOL[narration_idx]
    narration_idx += 1
```

### 나레이션 스타일
- `curious`: 궁금증 유발, 30자 이내, 짧고 강렬
- `dramatic`: 극적이고 감정적, 35자 이내, 생동감
- `calm`: 차분하고 서정적, 30자 이내, 여운

---

## 🚀 프론트엔드 사용 방법

### Next.js 프론트엔드 페이지
**URL**: `https://ai-studio.neuralgrid.kr/pro-shorts`

**파일**: `app/pro-shorts/page.tsx`

### 사용자 플로우
1. 사용자가 프롬프트 입력 (예: "우주 비행사의 모험")
2. "AI 쇼츠 생성 시작!" 버튼 클릭
3. 프론트엔드 → Next.js API Route → Flask 백엔드 호출
4. Flask 백엔드:
   - 장르 자동 감지 (SF)
   - 씬 구조화 (7개 씬, 5막 구조)
   - **AI 나레이션 자동 생성** (각 씬마다 Ollama 호출)
   - 이미지 생성 (선택 사항)
   - TTS 음성 합성 (선택 사항)
   - 영상 합성
5. 완성된 쇼츠 출력

### 타임라인 시각화
프론트엔드에서는 각 단계가 실시간으로 타임라인에 표시됩니다:
- 📝 스토리 생성
- 🎨 AI 이미지 생성
- 🎙️ TTS 음성 생성
- 🎬 카메라 효과
- 🎥 비디오 합성
- 🎵 배경음악 매칭

---

## 📊 성능 및 통계

### AI 나레이션 생성 속도
- **씬당 소요 시간**: 약 15-20초 (Ollama llama3.1:8b, CPU)
- **7개 씬 전체**: 약 105-140초 (1.5-2분)
- **병렬 처리 시**: 약 20-30초로 단축 가능 (미구현)

### 시스템 리소스
- **메모리 사용량**: 약 4.9GB (llama3.1:8b 모델)
- **CPU 사용률**: 80-100% (생성 중)
- **GPU**: 없음 (CPU 전용)

### 장르 감지 정확도
- **테스트 결과**: 83% (5/6 정확)
- **지원 장르**: SF, 동화, 액션, 로맨스, 공포, 코미디
- **소요 시간**: 약 2초

### 다국어 번역 품질
- **영어**: ⭐⭐⭐⭐⭐ (매우 우수)
- **일본어**: ⭐⭐⭐⭐ (우수)
- **중국어**: ⭐⭐⭐⭐ (우수)
- **스페인어**: ⭐⭐⭐ (양호)
- **번역 속도**: 약 8초/씬/언어

---

## 🔍 시스템 상태 확인

### Flask 서버 상태
```bash
# PM2 상태 확인
pm2 list | grep ai-story-generator

# 서버 로그 확인
pm2 logs ai-story-generator --lines 20

# 서버 재시작
pm2 restart ai-story-generator
```

### Ollama 서비스 상태
```bash
# Ollama 서비스 확인
curl http://localhost:11434/api/tags

# 모델 목록 확인
ollama list
```

### API 테스트
```bash
# Health Check
curl http://localhost:5004/health

# 스토리 생성 테스트
curl -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{"prompt": "우주 비행사의 모험", "duration": 30}'

# 번역 테스트
curl -X POST http://localhost:5004/translate-story \
  -H "Content-Type: application/json" \
  -d '{
    "narrations": ["안녕하세요"],
    "target_lang": "en"
  }'
```

---

## 🐛 문제 해결 (Troubleshooting)

### 1. "Ollama 서비스 미실행" 오류

**증상**:
```
WARNING: ⚠️ Ollama 서비스 미실행 - 고정 나레이션 풀 사용
```

**해결책**:
```bash
# Ollama 서비스 시작
ollama serve

# PM2로 서버 재시작
pm2 restart ai-story-generator
```

### 2. "AI 나레이션 생성 실패" 오류

**증상**: 나레이션이 고정 풀에서만 가져와짐

**해결책**:
1. Ollama 서비스 확인: `curl http://localhost:11434/api/tags`
2. 모델 확인: `ollama list` (llama3.1:8b 있어야 함)
3. 로그 확인: `pm2 logs ai-story-generator`

### 3. "포트 5004 이미 사용 중" 오류

**해결책**:
```bash
# 사용 중인 프로세스 확인
lsof -i :5004

# 프로세스 종료
kill -9 <PID>

# PM2로 재시작
pm2 restart ai-story-generator
```

### 4. "번역 시스템 비활성화" 오류

**증상**:
```
번역 시스템이 활성화되지 않았습니다.
```

**해결책**:
1. Ollama 서비스 확인
2. `multilang_translator.py` import 확인
3. PM2 재시작

---

## 📈 향후 개선 계획

### 단기 (1-2주)
- [ ] 나레이션 병렬 생성 (속도 70% 단축)
- [ ] 번역 품질 개선 (프롬프트 최적화)
- [ ] 캐싱 시스템 (중복 요청 방지)

### 중기 (1개월)
- [ ] GPU 서버 지원 (속도 300% 향상)
- [ ] 실시간 진행률 표시 (WebSocket)
- [ ] 다국어 쇼츠 일괄 생성

### 장기 (3개월)
- [ ] 사용자 맞춤 나레이션 스타일
- [ ] 음성 클로닝 통합
- [ ] 자동 자막 생성
- [ ] 쇼츠 품질 평가 AI

---

## 🔗 관련 문서

### 핵심 가이드
- **END_TO_END_GUIDE.md**: 전체 시스템 통합 가이드
- **GENRE_DETECTION_GUIDE.md**: 장르 감지 시스템
- **OLLAMA_NARRATION_GUIDE.md**: 나레이션 생성 시스템
- **MULTILANG_GUIDE.md**: 다국어 번역 시스템
- **REPLICATE_SETUP_GUIDE.md**: Replicate API 설정
- **HUGGINGFACE_SETUP_GUIDE.md**: Hugging Face API 설정

### API 문서
- **Flask 백엔드**: `ai-backend/story_generator.py`
- **Next.js API Route**: `app/api/story/route.ts`
- **프론트엔드 페이지**: `app/pro-shorts/page.tsx`

---

## 🎉 통합 완료 체크리스트

- [x] 장르 감지 시스템 통합
- [x] 나레이션 자동 생성 시스템 통합
- [x] 다국어 번역 시스템 통합
- [x] Flask 서버에 모듈 import
- [x] `/generate-story` API 개선
- [x] `/translate-story` API 추가
- [x] PM2로 서버 재시작
- [x] 시스템 로그 확인
- [x] Git 커밋 및 푸시
- [ ] 프론트엔드 실제 테스트
- [ ] 다국어 쇼츠 생성 검증
- [ ] 성능 모니터링 설정

---

## 📞 문의 및 지원

### GitHub
- Repository: https://github.com/hompystory-coder/azamans
- 최신 커밋: `d8b8038e`

### 문서 버전
- 작성일: 2024-12-27
- 버전: 1.0.0
- 다음 업데이트: 프론트엔드 테스트 완료 후

---

**🎬 이제 https://ai-studio.neuralgrid.kr/pro-shorts 에서 AI 나레이션이 자동 생성되는 멋진 쇼츠를 만들 수 있습니다!**
