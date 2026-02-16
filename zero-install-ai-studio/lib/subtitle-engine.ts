/**
 * 자막 자동 생성 엔진 v1.0
 * 
 * 기능:
 * - Web Speech API를 사용한 음성 인식
 * - 자동 타임스탬프 생성
 * - SRT 형식 내보내기
 * - 실시간 자막 미리보기
 */

export interface SubtitleSegment {
  id: number;
  startTime: number; // 밀리초
  endTime: number;   // 밀리초
  text: string;
}

export interface SubtitleStyle {
  fontFamily: string;
  fontSize: number;
  color: string;
  backgroundColor: string;
  position: 'top' | 'middle' | 'bottom';
  alignment: 'left' | 'center' | 'right';
}

export class SubtitleEngine {
  private recognition: any = null;
  private segments: SubtitleSegment[] = [];
  private isRecording: boolean = false;
  private startTimeOffset: number = 0;
  
  constructor() {
    this.initializeSpeechRecognition();
  }
  
  private initializeSpeechRecognition(): void {
    // 브라우저 환경에서만 실행
    if (typeof window === 'undefined') {
      return;
    }
    
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
      console.warn('Speech Recognition API not supported');
      return;
    }
    
    const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
    this.recognition = new SpeechRecognition();
    
    this.recognition.continuous = true;
    this.recognition.interimResults = true;
    this.recognition.lang = 'ko-KR'; // 한국어 기본
    
    this.recognition.onresult = (event: any) => {
      const results = event.results;
      const lastResult = results[results.length - 1];
      
      if (lastResult.isFinal) {
        const transcript = lastResult[0].transcript;
        const endTime = Date.now() - this.startTimeOffset;
        
        this.segments.push({
          id: this.segments.length + 1,
          startTime: endTime - (transcript.length * 100), // 대략적인 시작 시간
          endTime: endTime,
          text: transcript.trim()
        });
      }
    };
    
    this.recognition.onerror = (event: any) => {
      console.error('Speech recognition error:', event.error);
    };
  }
  
  /**
   * 오디오에서 자막 생성 (시뮬레이션)
   */
  async generateFromAudio(audioBlob: Blob): Promise<SubtitleSegment[]> {
    // 실제로는 Web Speech API 또는 서버 API 사용
    // 여기서는 데모용 자막 생성
    
    return new Promise((resolve) => {
      // 오디오 길이에 따라 자막 생성 (시뮬레이션)
      const duration = 30000; // 30초 가정
      const sentences = [
        "안녕하세요, 오늘은 멋진 하루입니다.",
        "이 영상에서는 놀라운 내용을 소개합니다.",
        "지금 바로 시작해보세요!",
        "더 많은 정보는 설명란을 확인하세요."
      ];
      
      const segments: SubtitleSegment[] = sentences.map((text, index) => ({
        id: index + 1,
        startTime: (duration / sentences.length) * index,
        endTime: (duration / sentences.length) * (index + 1),
        text: text
      }));
      
      setTimeout(() => resolve(segments), 1000);
    });
  }
  
  /**
   * 텍스트 스크립트에서 자막 생성
   */
  generateFromText(script: string, duration: number): SubtitleSegment[] {
    const sentences = script.split(/[.!?]+/).filter(s => s.trim().length > 0);
    const timePerSentence = duration / sentences.length;
    
    return sentences.map((text, index) => ({
      id: index + 1,
      startTime: timePerSentence * index,
      endTime: timePerSentence * (index + 1),
      text: text.trim()
    }));
  }
  
  /**
   * 자막을 SRT 형식으로 변환
   */
  exportToSRT(segments: SubtitleSegment[]): string {
    return segments.map(segment => {
      const start = this.formatTime(segment.startTime);
      const end = this.formatTime(segment.endTime);
      return `${segment.id}\n${start} --> ${end}\n${segment.text}\n`;
    }).join('\n');
  }
  
  /**
   * 자막을 VTT 형식으로 변환
   */
  exportToVTT(segments: SubtitleSegment[]): string {
    const vtt = ['WEBVTT\n\n'];
    segments.forEach(segment => {
      const start = this.formatTime(segment.startTime);
      const end = this.formatTime(segment.endTime);
      vtt.push(`${segment.id}\n${start} --> ${end}\n${segment.text}\n\n`);
    });
    return vtt.join('');
  }
  
  /**
   * 시간을 SRT/VTT 형식으로 변환 (00:00:00,000)
   */
  private formatTime(ms: number): string {
    const hours = Math.floor(ms / 3600000);
    const minutes = Math.floor((ms % 3600000) / 60000);
    const seconds = Math.floor((ms % 60000) / 1000);
    const milliseconds = ms % 1000;
    
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')},${String(milliseconds).padStart(3, '0')}`;
  }
  
  /**
   * Canvas에 자막 렌더링
   */
  renderSubtitleOnCanvas(
    canvas: HTMLCanvasElement,
    segment: SubtitleSegment,
    style: SubtitleStyle
  ): void {
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    // 폰트 설정
    ctx.font = `${style.fontSize}px ${style.fontFamily}`;
    ctx.fillStyle = style.color;
    ctx.textAlign = style.alignment;
    
    // 텍스트 위치 계산
    let y: number;
    switch (style.position) {
      case 'top':
        y = 50;
        break;
      case 'middle':
        y = canvas.height / 2;
        break;
      case 'bottom':
        y = canvas.height - 50;
        break;
    }
    
    // 배경 박스
    const textMetrics = ctx.measureText(segment.text);
    const textWidth = textMetrics.width;
    const textHeight = style.fontSize;
    
    let x: number;
    switch (style.alignment) {
      case 'left':
        x = 20;
        break;
      case 'center':
        x = canvas.width / 2;
        break;
      case 'right':
        x = canvas.width - 20;
        break;
    }
    
    // 배경 그리기
    ctx.fillStyle = style.backgroundColor;
    const padding = 10;
    ctx.fillRect(
      x - textWidth / 2 - padding,
      y - textHeight / 2 - padding,
      textWidth + padding * 2,
      textHeight + padding * 2
    );
    
    // 텍스트 그리기
    ctx.fillStyle = style.color;
    ctx.fillText(segment.text, x, y);
  }
  
  /**
   * 비디오에 자막 오버레이
   */
  async addSubtitlesToVideo(
    videoBlob: Blob,
    segments: SubtitleSegment[],
    style: SubtitleStyle
  ): Promise<Blob> {
    // FFmpeg.wasm를 사용하여 비디오에 자막 추가
    // 실제 구현은 video-engine.ts와 통합
    
    console.log('Adding subtitles to video...');
    console.log('Segments:', segments.length);
    console.log('Style:', style);
    
    // 임시로 원본 비디오 반환 (실제로는 자막이 추가된 비디오)
    return videoBlob;
  }
  
  /**
   * 자막 타이밍 자동 조정
   */
  autoAdjustTiming(segments: SubtitleSegment[], totalDuration: number): SubtitleSegment[] {
    const totalTextLength = segments.reduce((sum, seg) => sum + seg.text.length, 0);
    const msPerChar = totalDuration / totalTextLength;
    
    let currentTime = 0;
    return segments.map(segment => {
      const duration = segment.text.length * msPerChar;
      const adjusted = {
        ...segment,
        startTime: currentTime,
        endTime: currentTime + duration
      };
      currentTime += duration;
      return adjusted;
    });
  }
  
  /**
   * 기본 자막 스타일
   */
  static getDefaultStyle(): SubtitleStyle {
    return {
      fontFamily: 'Arial, sans-serif',
      fontSize: 32,
      color: '#FFFFFF',
      backgroundColor: 'rgba(0, 0, 0, 0.7)',
      position: 'bottom',
      alignment: 'center'
    };
  }
  
  /**
   * 프리셋 자막 스타일들
   */
  static getPresetStyles(): Record<string, SubtitleStyle> {
    return {
      youtube: {
        fontFamily: 'Roboto, Arial, sans-serif',
        fontSize: 36,
        color: '#FFFFFF',
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        position: 'bottom',
        alignment: 'center'
      },
      tiktok: {
        fontFamily: 'Impact, Arial Black, sans-serif',
        fontSize: 48,
        color: '#FFFFFF',
        backgroundColor: 'rgba(255, 0, 0, 0.3)',
        position: 'middle',
        alignment: 'center'
      },
      instagram: {
        fontFamily: 'Montserrat, Arial, sans-serif',
        fontSize: 40,
        color: '#FFFFFF',
        backgroundColor: 'rgba(131, 58, 180, 0.5)',
        position: 'top',
        alignment: 'center'
      },
      minimal: {
        fontFamily: 'Helvetica, Arial, sans-serif',
        fontSize: 28,
        color: '#FFFFFF',
        backgroundColor: 'rgba(0, 0, 0, 0.5)',
        position: 'bottom',
        alignment: 'center'
      },
      bold: {
        fontFamily: 'Arial Black, sans-serif',
        fontSize: 56,
        color: '#FFD700',
        backgroundColor: 'rgba(0, 0, 0, 0.9)',
        position: 'middle',
        alignment: 'center'
      }
    };
  }
}

// 싱글톤 인스턴스
export const subtitleEngine = new SubtitleEngine();
