# 🚀 AI 캐릭터 쇼츠 자동화 - 구현 가이드

## 📋 전체 개발 로드맵

### Phase 1: 기반 구축 (1-2일)
**목표**: 기존 시스템 이해 및 캐릭터 기능 추가

#### ✅ 완료된 작업
- [x] 전체 아키텍처 설계
- [x] 10가지 캐릭터 프리셋 정의
- [x] 캐릭터 선택 UI 컴포넌트
- [x] 캐릭터 데이터 구조 설계

#### 🔄 진행 중
- [ ] Minimax Hailuo 2.3 API 연동 가이드
- [ ] 네이버 블로그 크롤링 개선

---

## 🎯 핵심 기능 구현 순서

### 1단계: 콘텐츠 모드 선택 UI
**파일**: `frontend/src/pages/ModeSelectionPage.tsx`

```tsx
export default function ModeSelectionPage() {
  const [contentMode, setContentMode] = useState<'character' | 'hybrid' | 'realistic'>('character');
  const [automationMode, setAutomationMode] = useState<'auto' | 'manual'>('auto');

  return (
    <div>
      {/* 콘텐츠 타입 선택 */}
      <div className="grid grid-cols-3 gap-4">
        <ModeCard
          icon="🤖"
          title="캐릭터만"
          description="AI 캐릭터가 모든 장면에 등장"
          selected={contentMode === 'character'}
          onClick={() => setContentMode('character')}
        />
        <ModeCard
          icon="🤖📷"
          title="하이브리드"
          description="캐릭터 + 실사 이미지 혼합"
          selected={contentMode === 'hybrid'}
          onClick={() => setContentMode('hybrid')}
        />
        <ModeCard
          icon="📷"
          title="실사만"
          description="실제 이미지로만 구성"
          selected={contentMode === 'realistic'}
          onClick={() => setContentMode('realistic')}
        />
      </div>

      {/* 자동화 모드 선택 */}
      <div className="mt-8">
        <label className="flex items-center gap-3 cursor-pointer">
          <input
            type="radio"
            checked={automationMode === 'auto'}
            onChange={() => setAutomationMode('auto')}
          />
          <span>자동 (AI가 모든 단계 자동 처리)</span>
        </label>
        <label className="flex items-center gap-3 cursor-pointer">
          <input
            type="radio"
            checked={automationMode === 'manual'}
            onChange={() => setAutomationMode('manual')}
          />
          <span>수동 (단계별 직접 설정)</span>
        </label>
      </div>
    </div>
  );
}
```

---

### 2단계: 네이버 블로그 크롤링 개선
**파일**: `backend/src/routes/crawl.js`

```javascript
// 네이버 블로그 특화 크롤링
router.post('/naver-blog', async (req, res) => {
  const { url } = req.body;

  try {
    // 1. iframe 내부 콘텐츠 URL 추출
    const mainResponse = await axios.get(url);
    const $ = cheerio.load(mainResponse.data);
    const iframeSrc = $('#mainFrame').attr('src');
    
    if (!iframeSrc) {
      throw new Error('네이버 블로그 iframe을 찾을 수 없습니다.');
    }

    // 2. 실제 콘텐츠 크롤링
    const contentUrl = `https://blog.naver.com${iframeSrc}`;
    const contentResponse = await axios.get(contentUrl);
    const content$ = cheerio.load(contentResponse.data);

    // 3. 본문 텍스트 추출
    const title = content$('.se-title-text').text().trim();
    const paragraphs = [];
    content$('.se-text-paragraph').each((i, el) => {
      const text = content$(el).text().trim();
      if (text) paragraphs.push(text);
    });
    const bodyText = paragraphs.join('\n\n');

    // 4. 이미지 URL 추출 (고해상도)
    const images = [];
    content$('.se-image-resource').each((i, el) => {
      const src = content$(el).attr('data-src') || content$(el).attr('src');
      if (src && src.startsWith('http')) {
        images.push({
          url: src,
          alt: content$(el).attr('alt') || '',
          order: i + 1
        });
      }
    });

    // 5. 메타데이터 추출
    const metadata = {
      author: content$('.blog_author').text().trim(),
      date: content$('.blog_date').text().trim(),
      category: content$('.blog_category').text().trim()
    };

    res.json({
      success: true,
      data: {
        title,
        content: bodyText,
        images,
        metadata,
        wordCount: bodyText.length,
        imageCount: images.length
      }
    });

  } catch (error) {
    console.error('네이버 블로그 크롤링 실패:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});
```

---

### 3단계: Minimax Hailuo 2.3 비디오 생성
**파일**: `backend/src/services/minimaxVideo.js`

```javascript
import axios from 'axios';

export class MinimaxVideoService {
  constructor(apiKey) {
    this.apiKey = apiKey;
    this.baseUrl = 'https://api.minimaxi.chat/v1/video_generation';
  }

  /**
   * 이미지를 비디오로 변환 (Hailuo 2.3)
   */
  async generateVideo(options) {
    const {
      imageUrl,
      prompt,
      duration = 3,
      character = null
    } = options;

    try {
      // 캐릭터 프롬프트 + 콘텐츠 프롬프트 결합
      const fullPrompt = character 
        ? `${character.videoPromptTemplate.replace('{action}', 'explaining naturally')}. ${prompt}`
        : prompt;

      const response = await axios.post(
        this.baseUrl,
        {
          model: 'hailuo-2.3',
          prompt: fullPrompt,
          image_url: imageUrl,
          duration: duration,
          aspect_ratio: '9:16',
          quality: 'high'
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          }
        }
      );

      return {
        taskId: response.data.task_id,
        status: 'processing'
      };

    } catch (error) {
      console.error('Minimax 비디오 생성 실패:', error);
      throw error;
    }
  }

  /**
   * 비디오 생성 상태 확인
   */
  async checkStatus(taskId) {
    try {
      const response = await axios.get(
        `${this.baseUrl}/${taskId}`,
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`
          }
        }
      );

      return {
        status: response.data.status,
        videoUrl: response.data.video_url,
        progress: response.data.progress
      };

    } catch (error) {
      console.error('상태 확인 실패:', error);
      throw error;
    }
  }

  /**
   * 비디오 생성 완료 대기
   */
  async waitForCompletion(taskId, maxWaitTime = 300000) {
    const startTime = Date.now();
    const pollInterval = 5000; // 5초마다 확인

    while (Date.now() - startTime < maxWaitTime) {
      const status = await this.checkStatus(taskId);

      if (status.status === 'completed') {
        return status.videoUrl;
      } else if (status.status === 'failed') {
        throw new Error('비디오 생성 실패');
      }

      // 5초 대기
      await new Promise(resolve => setTimeout(resolve, pollInterval));
    }

    throw new Error('비디오 생성 시간 초과');
  }
}

// 사용 예시
export async function generateSceneVideo(scene, character) {
  const service = new MinimaxVideoService(process.env.MINIMAX_API_KEY);

  // 1. 비디오 생성 시작
  const task = await service.generateVideo({
    imageUrl: scene.imageUrl,
    prompt: scene.narration,
    character: character,
    duration: 3
  });

  // 2. 완료 대기
  const videoUrl = await service.waitForCompletion(task.taskId);

  // 3. 비디오 다운로드
  const videoPath = await downloadVideo(videoUrl, scene.id);

  return videoPath;
}
```

---

### 4단계: 캐릭터 비디오 생성 API
**파일**: `backend/src/routes/video.js`

```javascript
router.post('/generate-with-character', async (req, res) => {
  try {
    const { 
      scenes, 
      characterId, 
      settings 
    } = req.body;

    const character = getCharacterById(characterId);
    if (!character) {
      return res.status(400).json({
        success: false,
        error: '유효하지 않은 캐릭터 ID'
      });
    }

    console.log(`🎭 캐릭터: ${character.nameKr}`);
    console.log(`🎬 ${scenes.length}개 장면 생성 시작`);

    const generatedScenes = [];

    // 각 장면을 비디오로 변환
    for (let i = 0; i < scenes.length; i++) {
      const scene = scenes[i];
      console.log(`\n🎬 장면 ${i + 1}/${scenes.length} 생성 중...`);

      // 1. TTS 음성 생성
      const audioPath = await generateTTS({
        text: scene.narration,
        voiceStyle: character.voiceStyle
      });

      // 2. Minimax 비디오 생성
      const videoPath = await generateSceneVideo(scene, character);

      // 3. 자막 + 음성 합성
      const finalPath = await renderSceneWithSubtitles({
        videoPath,
        audioPath,
        subtitle: scene.narration,
        settings: settings.subtitleSettings
      });

      generatedScenes.push({
        sceneNumber: i + 1,
        videoPath: finalPath,
        duration: scene.duration
      });

      console.log(`✅ 장면 ${i + 1} 완료`);
    }

    // 4. 최종 렌더링 (모든 장면 결합)
    const finalVideo = await renderFinalVideo({
      scenes: generatedScenes,
      bgMusic: settings.bgMusic,
      bgImage: settings.bgImage
    });

    res.json({
      success: true,
      data: {
        videoUrl: finalVideo.url,
        duration: finalVideo.duration,
        size: finalVideo.size
      }
    });

  } catch (error) {
    console.error('캐릭터 비디오 생성 실패:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});
```

---

### 5단계: 전체 플로우 통합
**파일**: `frontend/src/pages/VideoPage.tsx`

```tsx
export default function VideoPage() {
  const [step, setStep] = useState(1);
  const [config, setConfig] = useState({
    contentMode: 'character',
    automationMode: 'auto',
    blogUrl: '',
    crawledData: null,
    selectedCharacter: null,
    script: [],
    settings: {}
  });

  // 1단계: 모드 선택
  const handleModeSelect = (contentMode, automationMode) => {
    setConfig({ ...config, contentMode, automationMode });
    setStep(2);
  };

  // 2단계: 블로그 크롤링
  const handleCrawl = async (url) => {
    const response = await api.post('/api/crawl/naver-blog', { url });
    setConfig({ ...config, blogUrl: url, crawledData: response.data });
    setStep(3);
  };

  // 3단계: 캐릭터 선택
  const handleCharacterSelect = (character) => {
    setConfig({ ...config, selectedCharacter: character });
    
    // 자동 모드면 다음 단계로
    if (config.automationMode === 'auto') {
      generateScriptAutomatically(character);
    } else {
      setStep(4);
    }
  };

  // 4단계: 스크립트 생성 (자동 또는 수동)
  const generateScriptAutomatically = async (character) => {
    const response = await api.post('/api/script/generate', {
      content: config.crawledData.content,
      images: config.crawledData.images,
      character: character,
      sceneCount: 12
    });
    
    setConfig({ ...config, script: response.data.scenes });
    setStep(5); // 비디오 생성 단계로
  };

  // 5단계: 비디오 생성
  const handleGenerateVideo = async () => {
    const response = await api.post('/api/video/generate-with-character', {
      scenes: config.script,
      characterId: config.selectedCharacter.id,
      settings: config.settings
    });
    
    // 결과 표시
    setStep(6);
  };

  return (
    <div>
      {step === 1 && <ModeSelectionPage onSelect={handleModeSelect} />}
      {step === 2 && <CrawlPage onCrawl={handleCrawl} />}
      {step === 3 && <CharacterSelectionPage onSelect={handleCharacterSelect} />}
      {step === 4 && <ScriptPage script={config.script} onNext={() => setStep(5)} />}
      {step === 5 && <GenerationPage onGenerate={handleGenerateVideo} />}
      {step === 6 && <ResultPage video={config.finalVideo} />}
    </div>
  );
}
```

---

## 💰 예상 비용 분석

### 1개 쇼츠 생성 비용 (12개 장면)

| 항목 | 수량 | 단가 | 총 비용 |
|------|------|------|---------|
| 블로그 크롤링 | 1회 | ₩0 | ₩0 |
| AI 스크립트 생성 | 1회 | ₩0 | ₩0 |
| TTS 음성 생성 | 12개 | ₩30 | ₩360 |
| Minimax 비디오 생성 | 12개 | ₩1,200 | ₩14,400 |
| FFmpeg 렌더링 | 1회 | ₩0 | ₩0 |
| **총 비용** | | | **₩14,760** |

### 비용 최적화 방안

1. **캐싱 시스템**
   - 동일 캐릭터 + 동일 프롬프트 = 재사용
   - 예상 절감: 30%

2. **배치 처리**
   - 여러 장면 동시 생성
   - 예상 절감: 20%

3. **저비용 옵션**
   - 실사 이미지만 사용 시 비디오 생성 비용 ₩0
   - 예상 절감: 100% (비디오 생성 비용)

---

## 🧪 테스트 시나리오

### 시나리오 1: 캐릭터만 모드 (자동)
```
1. 모드 선택: 캐릭터만 + 자동
2. 블로그 입력: https://blog.naver.com/alphahome/224106828152
3. 자동 크롤링 완료
4. 캐릭터 자동 추천: "비즈니스 프로" (부동산 콘텐츠)
5. 스크립트 자동 생성 (12개 장면)
6. 비디오 자동 생성 (약 5분 소요)
7. 최종 렌더링 (약 1분 소요)
8. 결과물 다운로드
```

### 시나리오 2: 하이브리드 모드 (수동)
```
1. 모드 선택: 하이브리드 + 수동
2. 블로그 입력 및 크롤링
3. 캐릭터 선택: "여성 리포터"
4. 스크립트 확인 및 편집
5. 장면별 이미지/캐릭터 비율 조정
   - 인트로: 캐릭터
   - 본문: 실사 이미지
   - 아웃트로: 캐릭터
6. 음성/폰트 설정
7. 비디오 생성
8. 최종 렌더링
```

### 시나리오 3: 실사만 모드 (기존 시스템)
```
1. 모드 선택: 실사만 + 자동
2. 블로그 입력 및 크롤링
3. 캐릭터 선택 생략
4. 스크립트 자동 생성
5. 기존 shorts-creator-pro 파이프라인 사용
6. 비디오 생성 (FFmpeg만 사용, 비용 ₩360)
7. 결과물 다운로드
```

---

## 📝 다음 개발 단계

### 즉시 작업 가능
1. ✅ 모드 선택 UI 구현
2. ✅ 캐릭터 선택 UI 구현
3. 🔄 네이버 블로그 크롤러 개선
4. 🔄 Minimax API 연동

### 추가 개발 필요
5. 음성 미리듣기 기능
6. 폰트 미리보기 기능
7. 장면별 미리보기
8. 유튜브 메타데이터 생성
9. 배치 생성 기능
10. 템플릿 시스템

---

## ✅ 완료된 작업

1. ✅ **전체 아키텍처 설계**
   - `/var/www/mfx.neuralgrid.kr/AI_CHARACTER_SHORTS_ARCHITECTURE.md`

2. ✅ **10가지 캐릭터 프리셋 정의**
   - `/home/azamans/shorts-creator-pro/CHARACTER_PRESETS.md`

3. ✅ **캐릭터 데이터 구조**
   - `/home/azamans/shorts-creator-pro/frontend/src/lib/characters.ts`

4. ✅ **캐릭터 선택 UI 컴포넌트**
   - `/home/azamans/shorts-creator-pro/frontend/src/pages/CharacterSelectionPage.tsx`

5. ✅ **구현 가이드 작성**
   - 현재 문서

---

## 🚀 배포 가이드

### 환경 변수 설정
```bash
# .env
MINIMAX_API_KEY=your_minimax_api_key
MINIMAX_TTS_API_KEY=your_tts_api_key
ELEVENLABS_API_KEY=your_elevenlabs_key (선택)
```

### 빌드 및 재시작
```bash
# Frontend 빌드
cd /home/azamans/shorts-creator-pro/frontend
npm run build

# Backend 재시작
pm2 restart shorts-creator-backend

# Frontend 재시작
pm2 restart shorts-creator-frontend
```

---

## 📞 다음 단계

**개발자가 해야 할 일**:

1. **Minimax API 키 발급**
   - https://www.minimaxi.com/ 가입
   - API 키 발급
   - 비디오 생성 크레딧 충전

2. **코드 통합**
   - 제공된 코드 스니펫을 프로젝트에 통합
   - API 엔드포인트 연결
   - 테스트 진행

3. **UI 개선**
   - 캐릭터 프리뷰 이미지 추가
   - 음성 샘플 녹음
   - 로딩 애니메이션 추가

4. **테스트**
   - 네이버 블로그로 전체 플로우 테스트
   - 다양한 캐릭터 조합 테스트
   - 성능 최적화

---

**작성일**: 2024-12-22  
**프로젝트**: AI 캐릭터 쇼츠 자동화  
**상태**: 설계 및 가이드 완료 ✅  
**다음**: 실제 구현 및 테스트
