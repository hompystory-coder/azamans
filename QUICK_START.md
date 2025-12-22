# 🎬 AI 캐릭터 쇼츠 자동화 - 빠른 시작 가이드

## 📦 생성된 파일 목록

### 문서
1. `/var/www/mfx.neuralgrid.kr/AI_CHARACTER_SHORTS_ARCHITECTURE.md`
   - 전체 시스템 아키텍처
   - UI/UX 플로우
   - 기술 스택

2. `/home/azamans/shorts-creator-pro/CHARACTER_PRESETS.md`
   - 10가지 캐릭터 상세 정의
   - 음성 매핑
   - 프롬프트 템플릿

3. `/home/azamans/shorts-creator-pro/IMPLEMENTATION_GUIDE.md`
   - 단계별 구현 가이드
   - 코드 예시
   - API 연동 방법

4. `/home/azamans/shorts-creator-pro/QUICK_START.md` (현재 파일)
   - 빠른 시작 가이드
   - 체크리스트

### 코드
1. `/home/azamans/shorts-creator-pro/frontend/src/lib/characters.ts`
   - 캐릭터 타입 정의
   - 10가지 캐릭터 데이터
   - 유틸리티 함수

2. `/home/azamans/shorts-creator-pro/frontend/src/pages/CharacterSelectionPage.tsx`
   - 캐릭터 선택 UI 컴포넌트
   - 인터랙티브 선택 기능

---

## ⚡ 빠른 실행 체크리스트

### Phase 1: 환경 설정 (10분)
- [ ] Minimax API 키 발급
  ```bash
  # .env 파일에 추가
  MINIMAX_API_KEY=your_api_key_here
  ```
- [ ] 의존성 설치 확인
  ```bash
  cd /home/azamans/shorts-creator-pro/frontend
  npm install framer-motion lucide-react
  ```

### Phase 2: 캐릭터 시스템 통합 (30분)
- [ ] `characters.ts` 파일을 프로젝트에 복사
- [ ] `CharacterSelectionPage.tsx` 컴포넌트 추가
- [ ] 라우팅에 캐릭터 선택 페이지 추가
- [ ] 상태 관리에 캐릭터 정보 추가

### Phase 3: API 연동 (1시간)
- [ ] Minimax 비디오 생성 서비스 구현
- [ ] 네이버 블로그 크롤러 개선
- [ ] 캐릭터 비디오 생성 엔드포인트 추가

### Phase 4: 테스트 (30분)
- [ ] 네이버 블로그 크롤링 테스트
  - URL: https://blog.naver.com/alphahome/224106828152
- [ ] 캐릭터 선택 UI 테스트
- [ ] 비디오 생성 파이프라인 테스트

---

## 🎯 핵심 기능 요약

### 3가지 콘텐츠 모드
1. **캐릭터만** 🤖
   - AI 캐릭터가 모든 장면 등장
   - Minimax 비디오 생성 사용
   - 비용: 약 ₩15,000/쇼츠

2. **하이브리드** 🤖📷
   - 캐릭터 + 실사 이미지 혼합
   - 장면별 선택 가능
   - 비용: 약 ₩7,500/쇼츠

3. **실사만** 📷
   - 실제 이미지로만 구성
   - 기존 시스템 활용
   - 비용: 약 ₩360/쇼츠

### 2가지 자동화 모드
1. **자동 모드**
   - URL 입력만으로 완성
   - AI가 모든 단계 자동 처리
   - 소요 시간: 약 10분

2. **수동 모드**
   - 단계별 직접 설정
   - 세밀한 커스터마이징
   - 소요 시간: 약 20분

---

## 💻 주요 코드 스니펫

### 1. 캐릭터 선택 사용 예시
```typescript
import { characters, getCharacterById, generateVideoPrompt } from './lib/characters';

// 캐릭터 목록 가져오기
const allCharacters = characters;

// 특정 캐릭터 가져오기
const businessPro = getCharacterById('business-pro');

// 비디오 프롬프트 생성
const prompt = generateVideoPrompt(
  businessPro,
  "LG 그램은 가볍습니다",
  "explaining with hand gestures"
);
```

### 2. Minimax 비디오 생성
```javascript
import { MinimaxVideoService } from './services/minimaxVideo';

const service = new MinimaxVideoService(process.env.MINIMAX_API_KEY);

const task = await service.generateVideo({
  imageUrl: 'https://example.com/image.jpg',
  prompt: prompt,
  character: businessPro,
  duration: 3
});

const videoUrl = await service.waitForCompletion(task.taskId);
```

### 3. 네이버 블로그 크롤링
```javascript
const response = await axios.post('/api/crawl/naver-blog', {
  url: 'https://blog.naver.com/alphahome/224106828152'
});

const { title, content, images, metadata } = response.data;
```

---

## 📊 시스템 플로우

```
┌─────────────────────────────────────────────────┐
│  1. 사용자 입력                                  │
│  - 콘텐츠 모드 선택 (캐릭터/하이브리드/실사)     │
│  - 자동화 모드 선택 (자동/수동)                 │
│  - 블로그 URL 입력                              │
└─────────────┬───────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────┐
│  2. 블로그 크롤링                                │
│  - 본문 텍스트 추출                             │
│  - 이미지 수집                                  │
│  - 메타데이터 수집                              │
└─────────────┬───────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────┐
│  3. AI 스크립트 생성                             │
│  - 12개 장면 생성 (30초+)                       │
│  - 10-15자 짧은 문장                            │
│  - 이미지-텍스트 매칭                           │
└─────────────┬───────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────┐
│  4. 캐릭터 선택                                  │
│  - 10가지 프리셋 중 선택                        │
│  - AI 자동 추천 (콘텐츠 기반)                   │
└─────────────┬───────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────┐
│  5. 장면별 비디오 생성                           │
│  - TTS 음성 생성 (12개)                         │
│  - Minimax 비디오 생성 (12개)                   │
│  - 자막 + 음성 동기화                           │
└─────────────┬───────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────┐
│  6. 최종 렌더링                                  │
│  - FFmpeg로 장면 결합                           │
│  - 배경 음악 믹싱                               │
│  - 자막/효과 적용                               │
└─────────────┬───────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────┐
│  7. 결과물 제공                                  │
│  - 비디오 다운로드                              │
│  - 유튜브 메타데이터 (제목/본문/키워드)         │
│  - 한 번에 복사 가능                            │
└─────────────────────────────────────────────────┘
```

---

## 🔧 트러블슈팅

### Q1: Minimax API 호출 실패
```bash
# 해결 방법
1. API 키 확인
2. 크레딧 잔액 확인
3. 네트워크 연결 확인
4. 로그 확인: pm2 logs shorts-creator-backend
```

### Q2: 네이버 블로그 크롤링 실패
```bash
# 해결 방법
1. URL 형식 확인 (https://blog.naver.com/...)
2. 블로그 공개 설정 확인
3. iframe 로딩 대기 시간 증가
```

### Q3: 비디오 생성 너무 느림
```bash
# 해결 방법
1. 배치 처리로 전환 (동시에 여러 장면)
2. 저해상도 옵션 사용
3. 캐싱 시스템 활용
```

---

## 📞 다음 단계

### 개발자가 해야 할 일
1. **즉시 (오늘)**
   - [ ] Minimax API 키 발급
   - [ ] 코드 파일 복사 및 통합
   - [ ] 기본 라우팅 설정

2. **단기 (1주일)**
   - [ ] Minimax API 연동
   - [ ] 네이버 블로그 크롤러 개선
   - [ ] 전체 플로우 테스트

3. **중기 (2주일)**
   - [ ] UI/UX 개선
   - [ ] 음성/폰트 미리보기
   - [ ] 유튜브 메타데이터 생성

4. **장기 (1개월)**
   - [ ] 배치 생성 기능
   - [ ] 템플릿 시스템
   - [ ] 비용 최적화

---

## 📚 참고 자료

### 생성된 문서
1. `AI_CHARACTER_SHORTS_ARCHITECTURE.md` - 전체 아키텍처
2. `CHARACTER_PRESETS.md` - 캐릭터 정의
3. `IMPLEMENTATION_GUIDE.md` - 구현 가이드
4. `QUICK_START.md` - 빠른 시작 (현재)

### 기존 시스템
- `shorts-creator-pro` - 기존 30초 쇼츠 시스템
- `https://shorts.neuralgrid.kr/` - 실사 이미지 쇼츠

### API 문서
- Minimax Hailuo: https://docs.minimaxi.com/video
- Minimax TTS: https://docs.minimaxi.com/tts

---

## ✅ 체크리스트

### 완료된 작업
- [x] 전체 아키텍처 설계
- [x] 10가지 캐릭터 프리셋 정의
- [x] 캐릭터 선택 UI 컴포넌트
- [x] 캐릭터 데이터 구조
- [x] 구현 가이드 작성
- [x] 빠른 시작 가이드 작성

### 다음 작업
- [ ] Minimax API 연동
- [ ] 네이버 블로그 크롤러
- [ ] 비디오 생성 파이프라인
- [ ] 전체 플로우 테스트
- [ ] UI/UX 개선

---

## 💡 핵심 포인트

1. **기존 시스템 활용**
   - `shorts-creator-pro`를 기반으로 구축
   - 크롤링, TTS, FFmpeg 재사용
   - 캐릭터 기능만 추가

2. **단계적 개발**
   - Phase 1: 캐릭터 선택 UI
   - Phase 2: Minimax 연동
   - Phase 3: 전체 통합

3. **비용 최적화**
   - 실사 모드: ₩360 (저렴)
   - 하이브리드: ₩7,500 (중간)
   - 캐릭터만: ₩15,000 (고품질)

---

**작성일**: 2024-12-22  
**프로젝트**: AI 캐릭터 쇼츠 자동화  
**상태**: 설계 완료, 구현 대기 ✅  
**예상 완성**: 2-3주
