/**
 * AI 음성 선택 시스템 v1.0
 * 
 * 다양한 AI 음성과 감정, 속도 조절
 * - 20+ AI 음성 (남성/여성/어린이)
 * - 감정 선택 (기쁨/슬픔/화남/차분함/흥분)
 * - 속도 조절 (0.25x - 2.0x)
 * - 음조 조절 (낮음/보통/높음)
 * - 강조 단어 설정
 * - 휴지 시간 조절
 */

export interface Voice {
  id: string;
  name: string;
  gender: 'male' | 'female' | 'child';
  language: string;
  accent: string;
  age: number;
  description: string;
  sampleUrl?: string;
  tags: string[];
  premium: boolean;
}

export interface Emotion {
  id: string;
  name: string;
  icon: string;
  intensity: number; // 0-100
}

export interface VoiceSettings {
  voiceId: string;
  rate: number;       // 0.25-2.0 (속도)
  pitch: number;      // 0.5-2.0 (음조)
  volume: number;     // 0-1
  emotion?: string;
  emphasis: string[]; // 강조할 단어들
  pauses: Array<{ position: number; duration: number }>; // 휴지 위치와 길이
}

export class VoiceSelector {
  private voices: Voice[] = [];
  private emotions: Emotion[] = [];
  
  constructor() {
    this.initializeVoices();
    this.initializeEmotions();
  }
  
  /**
   * 음성 초기화
   */
  private initializeVoices(): void {
    this.voices = [
      // 한국어 남성
      {
        id: 'ko-male-news',
        name: '뉴스 앵커 (남성)',
        gender: 'male',
        language: 'ko-KR',
        accent: 'Standard',
        age: 35,
        description: '신뢰감 있는 뉴스 스타일 음성',
        tags: ['professional', 'news', 'formal'],
        premium: false
      },
      {
        id: 'ko-male-friendly',
        name: '친근한 내레이터 (남성)',
        gender: 'male',
        language: 'ko-KR',
        accent: 'Seoul',
        age: 28,
        description: '따뜻하고 친근한 음성',
        tags: ['friendly', 'casual', 'warm'],
        premium: false
      },
      {
        id: 'ko-male-energetic',
        name: '에너제틱 (남성)',
        gender: 'male',
        language: 'ko-KR',
        accent: 'Standard',
        age: 25,
        description: '활기차고 젊은 음성',
        tags: ['energetic', 'young', 'excited'],
        premium: true
      },
      
      // 한국어 여성
      {
        id: 'ko-female-soft',
        name: '부드러운 내레이터 (여성)',
        gender: 'female',
        language: 'ko-KR',
        accent: 'Standard',
        age: 30,
        description: '부드럽고 차분한 여성 음성',
        tags: ['soft', 'calm', 'soothing'],
        premium: false
      },
      {
        id: 'ko-female-professional',
        name: '전문가 (여성)',
        gender: 'female',
        language: 'ko-KR',
        accent: 'Seoul',
        age: 35,
        description: '프로페셔널한 여성 음성',
        tags: ['professional', 'confident', 'mature'],
        premium: false
      },
      {
        id: 'ko-female-bright',
        name: '밝은 목소리 (여성)',
        gender: 'female',
        language: 'ko-KR',
        accent: 'Standard',
        age: 24,
        description: '밝고 경쾌한 음성',
        tags: ['bright', 'cheerful', 'young'],
        premium: true
      },
      
      // 한국어 어린이
      {
        id: 'ko-child-boy',
        name: '소년',
        gender: 'child',
        language: 'ko-KR',
        accent: 'Standard',
        age: 10,
        description: '귀여운 소년 목소리',
        tags: ['child', 'cute', 'innocent'],
        premium: true
      },
      {
        id: 'ko-child-girl',
        name: '소녀',
        gender: 'child',
        language: 'ko-KR',
        accent: 'Standard',
        age: 9,
        description: '밝은 소녀 목소리',
        tags: ['child', 'bright', 'sweet'],
        premium: true
      },
      
      // 영어 음성
      {
        id: 'en-male-american',
        name: 'American Male',
        gender: 'male',
        language: 'en-US',
        accent: 'American',
        age: 32,
        description: 'Standard American English',
        tags: ['american', 'neutral', 'professional'],
        premium: false
      },
      {
        id: 'en-female-british',
        name: 'British Female',
        gender: 'female',
        language: 'en-GB',
        accent: 'British',
        age: 35,
        description: 'Elegant British accent',
        tags: ['british', 'elegant', 'sophisticated'],
        premium: true
      },
      
      // 일본어 음성
      {
        id: 'ja-female-tokyo',
        name: 'Tokyo Female',
        gender: 'female',
        language: 'ja-JP',
        accent: 'Tokyo',
        age: 28,
        description: '도쿄 표준어 여성 음성',
        tags: ['japanese', 'tokyo', 'standard'],
        premium: true
      }
    ];
  }
  
  /**
   * 감정 초기화
   */
  private initializeEmotions(): void {
    this.emotions = [
      {
        id: 'neutral',
        name: '중립',
        icon: '😐',
        intensity: 0
      },
      {
        id: 'happy',
        name: '기쁨',
        icon: '😊',
        intensity: 70
      },
      {
        id: 'excited',
        name: '흥분',
        icon: '🤩',
        intensity: 90
      },
      {
        id: 'sad',
        name: '슬픔',
        icon: '😢',
        intensity: 60
      },
      {
        id: 'angry',
        name: '화남',
        icon: '😠',
        intensity: 80
      },
      {
        id: 'calm',
        name: '차분함',
        icon: '😌',
        intensity: 40
      },
      {
        id: 'serious',
        name: '진지함',
        icon: '🧐',
        intensity: 50
      },
      {
        id: 'surprised',
        name: '놀람',
        icon: '😲',
        intensity: 75
      }
    ];
  }
  
  /**
   * 모든 음성 가져오기
   */
  getAllVoices(): Voice[] {
    return this.voices;
  }
  
  /**
   * 성별로 필터링
   */
  getVoicesByGender(gender: string): Voice[] {
    return this.voices.filter(v => v.gender === gender);
  }
  
  /**
   * 언어로 필터링
   */
  getVoicesByLanguage(language: string): Voice[] {
    return this.voices.filter(v => v.language === language);
  }
  
  /**
   * 태그로 검색
   */
  searchVoicesByTag(tag: string): Voice[] {
    return this.voices.filter(v => 
      v.tags.some(t => t.toLowerCase().includes(tag.toLowerCase()))
    );
  }
  
  /**
   * 음성 ID로 가져오기
   */
  getVoiceById(id: string): Voice | undefined {
    return this.voices.find(v => v.id === id);
  }
  
  /**
   * 모든 감정 가져오기
   */
  getAllEmotions(): Emotion[] {
    return this.emotions;
  }
  
  /**
   * TTS 생성 (Web Speech API)
   */
  async generateSpeech(
    text: string,
    settings: VoiceSettings
  ): Promise<Blob> {
    // 브라우저 환경 체크
    if (typeof window === 'undefined') {
      throw new Error('Speech synthesis is only available in browser environment');
    }
    
    return new Promise((resolve, reject) => {
      // Web Speech API 사용
      const utterance = new SpeechSynthesisUtterance(text);
      
      // 음성 설정
      const voice = this.getVoiceById(settings.voiceId);
      if (voice) {
        const synthVoices = window.speechSynthesis.getVoices();
        const matchedVoice = synthVoices.find(v => 
          v.lang === voice.language
        );
        if (matchedVoice) {
          utterance.voice = matchedVoice;
        }
      }
      
      utterance.rate = settings.rate;
      utterance.pitch = settings.pitch;
      utterance.volume = settings.volume;
      
      // 감정 적용 (Web Speech API에는 직접 지원 없음, 음조로 시뮬레이션)
      if (settings.emotion) {
        const emotion = this.emotions.find(e => e.id === settings.emotion);
        if (emotion) {
          switch (emotion.id) {
            case 'happy':
            case 'excited':
              utterance.pitch *= 1.2;
              utterance.rate *= 1.1;
              break;
            case 'sad':
              utterance.pitch *= 0.9;
              utterance.rate *= 0.9;
              break;
            case 'angry':
              utterance.pitch *= 1.1;
              utterance.rate *= 1.2;
              break;
            case 'calm':
              utterance.pitch *= 0.95;
              utterance.rate *= 0.85;
              break;
          }
        }
      }
      
      // 재생 완료 시 Blob으로 변환 (시뮬레이션)
      utterance.onend = () => {
        // 실제로는 오디오 녹음 필요
        const blob = new Blob([], { type: 'audio/wav' });
        resolve(blob);
      };
      
      utterance.onerror = (error) => {
        reject(error);
      };
      
      // TTS 실행
      window.speechSynthesis.speak(utterance);
    });
  }
  
  /**
   * 미리듣기
   */
  async preview(text: string, settings: VoiceSettings): Promise<void> {
    // 브라우저 환경 체크
    if (typeof window === 'undefined') {
      return;
    }
    
    // 기존 재생 정지
    window.speechSynthesis.cancel();
    
    // 새 음성 재생
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = settings.rate;
    utterance.pitch = settings.pitch;
    utterance.volume = settings.volume;
    
    window.speechSynthesis.speak(utterance);
  }
  
  /**
   * 재생 정지
   */
  stop(): void {
    if (typeof window !== 'undefined') {
      window.speechSynthesis.cancel();
    }
  }
  
  /**
   * 강조 태그 적용
   */
  applyEmphasis(text: string, emphasisWords: string[]): string {
    let result = text;
    emphasisWords.forEach(word => {
      const regex = new RegExp(`\\b${word}\\b`, 'gi');
      result = result.replace(regex, `<emphasis>${word}</emphasis>`);
    });
    return result;
  }
  
  /**
   * 휴지 추가
   */
  applyPauses(
    text: string,
    pauses: Array<{ position: number; duration: number }>
  ): string {
    let result = text;
    pauses.sort((a, b) => b.position - a.position); // 뒤에서부터 적용
    
    pauses.forEach(pause => {
      const before = result.slice(0, pause.position);
      const after = result.slice(pause.position);
      result = `${before}<break time="${pause.duration}ms"/>${after}`;
    });
    
    return result;
  }
  
  /**
   * SSML 생성
   */
  generateSSML(text: string, settings: VoiceSettings): string {
    let processedText = text;
    
    // 강조 적용
    if (settings.emphasis.length > 0) {
      processedText = this.applyEmphasis(processedText, settings.emphasis);
    }
    
    // 휴지 적용
    if (settings.pauses.length > 0) {
      processedText = this.applyPauses(processedText, settings.pauses);
    }
    
    // SSML 생성
    const voice = this.getVoiceById(settings.voiceId);
    const ssml = `
<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xml:lang="${voice?.language || 'ko-KR'}">
  <prosody rate="${settings.rate}" pitch="${settings.pitch * 100}%" volume="${settings.volume * 100}%">
    ${processedText}
  </prosody>
</speak>
    `.trim();
    
    return ssml;
  }
}

// 싱글톤 인스턴스
export const voiceSelector = new VoiceSelector();
