/**
 * 고급 타임라인 편집기 v1.0
 * 
 * 프로페셔널 비디오 편집 기능:
 * - 다중 트랙 (비디오, 오디오, 자막, 효과)
 * - 드래그 앤 드롭
 * - 트림 및 분할
 * - 키프레임 애니메이션
 * - 트랜지션 효과
 * - 실시간 미리보기
 */

export interface TimelineTrack {
  id: string;
  type: 'video' | 'audio' | 'subtitle' | 'effect';
  name: string;
  clips: TimelineClip[];
  locked: boolean;
  muted: boolean;
  solo: boolean;
}

export interface TimelineClip {
  id: string;
  trackId: string;
  type: 'video' | 'image' | 'audio' | 'text';
  name: string;
  url: string;
  startTime: number; // 밀리초
  duration: number;  // 밀리초
  trimStart: number; // 밀리초 (원본에서 자른 시작)
  trimEnd: number;   // 밀리초 (원본에서 자른 끝)
  volume: number;    // 0-1
  opacity: number;   // 0-1
  x: number;         // 위치 X
  y: number;         // 위치 Y
  width: number;     // 크기
  height: number;
  rotation: number;  // 각도
  effects: Effect[];
  transitions: {
    in?: Transition;
    out?: Transition;
  };
  keyframes: Keyframe[];
}

export interface Effect {
  id: string;
  type: 'filter' | 'color' | 'blur' | 'sharpen';
  name: string;
  enabled: boolean;
  params: Record<string, number>;
}

export interface Transition {
  type: 'fade' | 'dissolve' | 'wipe' | 'slide' | 'zoom';
  duration: number; // 밀리초
  easing: 'linear' | 'ease-in' | 'ease-out' | 'ease-in-out';
}

export interface Keyframe {
  time: number; // 밀리초
  property: string;
  value: number;
  easing: 'linear' | 'ease-in' | 'ease-out' | 'ease-in-out';
}

export interface TimelineState {
  tracks: TimelineTrack[];
  currentTime: number;
  duration: number;
  zoom: number; // 1-100
  playbackRate: number; // 0.25-2.0
  isPlaying: boolean;
  selectedClips: string[];
}

export class TimelineEditor {
  private state: TimelineState;
  private canvas: HTMLCanvasElement | null = null;
  private ctx: CanvasRenderingContext2D | null = null;
  private animationFrame: number | null = null;
  
  constructor() {
    this.state = {
      tracks: [],
      currentTime: 0,
      duration: 60000, // 60초 기본
      zoom: 10,
      playbackRate: 1.0,
      isPlaying: false,
      selectedClips: []
    };
    
    this.initializeDefaultTracks();
  }
  
  /**
   * 기본 트랙 초기화
   */
  private initializeDefaultTracks(): void {
    this.state.tracks = [
      {
        id: 'video-1',
        type: 'video',
        name: 'Video Track 1',
        clips: [],
        locked: false,
        muted: false,
        solo: false
      },
      {
        id: 'video-2',
        type: 'video',
        name: 'Video Track 2',
        clips: [],
        locked: false,
        muted: false,
        solo: false
      },
      {
        id: 'audio-1',
        type: 'audio',
        name: 'Audio Track 1',
        clips: [],
        locked: false,
        muted: false,
        solo: false
      },
      {
        id: 'subtitle-1',
        type: 'subtitle',
        name: 'Subtitle Track',
        clips: [],
        locked: false,
        muted: false,
        solo: false
      },
      {
        id: 'effect-1',
        type: 'effect',
        name: 'Effect Track',
        clips: [],
        locked: false,
        muted: false,
        solo: false
      }
    ];
  }
  
  /**
   * 캔버스 초기화
   */
  initCanvas(canvas: HTMLCanvasElement): void {
    this.canvas = canvas;
    this.ctx = canvas.getContext('2d');
  }
  
  /**
   * 클립 추가
   */
  addClip(trackId: string, clip: Omit<TimelineClip, 'id' | 'trackId'>): TimelineClip {
    const track = this.state.tracks.find(t => t.id === trackId);
    if (!track) throw new Error('Track not found');
    
    const newClip: TimelineClip = {
      id: `clip-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      trackId,
      ...clip,
      effects: [],
      transitions: {},
      keyframes: []
    };
    
    track.clips.push(newClip);
    track.clips.sort((a, b) => a.startTime - b.startTime);
    
    // 타임라인 길이 조정
    const endTime = newClip.startTime + newClip.duration;
    if (endTime > this.state.duration) {
      this.state.duration = endTime;
    }
    
    return newClip;
  }
  
  /**
   * 클립 이동
   */
  moveClip(clipId: string, newTrackId: string, newStartTime: number): void {
    // 기존 트랙에서 제거
    let clip: TimelineClip | undefined;
    for (const track of this.state.tracks) {
      const index = track.clips.findIndex(c => c.id === clipId);
      if (index !== -1) {
        clip = track.clips.splice(index, 1)[0];
        break;
      }
    }
    
    if (!clip) throw new Error('Clip not found');
    
    // 새 트랙에 추가
    const newTrack = this.state.tracks.find(t => t.id === newTrackId);
    if (!newTrack) throw new Error('Track not found');
    
    clip.trackId = newTrackId;
    clip.startTime = newStartTime;
    newTrack.clips.push(clip);
    newTrack.clips.sort((a, b) => a.startTime - b.startTime);
  }
  
  /**
   * 클립 트림
   */
  trimClip(clipId: string, trimStart: number, trimEnd: number): void {
    const clip = this.findClip(clipId);
    if (!clip) throw new Error('Clip not found');
    
    clip.trimStart = trimStart;
    clip.trimEnd = trimEnd;
    clip.duration = trimEnd - trimStart;
  }
  
  /**
   * 클립 분할
   */
  splitClip(clipId: string, time: number): void {
    const clip = this.findClip(clipId);
    if (!clip) throw new Error('Clip not found');
    
    const track = this.state.tracks.find(t => t.id === clip.trackId);
    if (!track) throw new Error('Track not found');
    
    // 분할 시간이 클립 범위 내인지 확인
    if (time <= clip.startTime || time >= clip.startTime + clip.duration) {
      throw new Error('Split time must be within clip range');
    }
    
    // 새 클립 생성 (오른쪽 부분)
    const rightClip: TimelineClip = {
      ...clip,
      id: `clip-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      startTime: time,
      duration: (clip.startTime + clip.duration) - time,
      trimStart: clip.trimStart + (time - clip.startTime)
    };
    
    // 원본 클립 수정 (왼쪽 부분)
    clip.duration = time - clip.startTime;
    clip.trimEnd = clip.trimStart + clip.duration;
    
    // 트랙에 새 클립 추가
    track.clips.push(rightClip);
    track.clips.sort((a, b) => a.startTime - b.startTime);
  }
  
  /**
   * 효과 추가
   */
  addEffect(clipId: string, effect: Omit<Effect, 'id'>): void {
    const clip = this.findClip(clipId);
    if (!clip) throw new Error('Clip not found');
    
    clip.effects.push({
      id: `effect-${Date.now()}`,
      ...effect
    });
  }
  
  /**
   * 트랜지션 추가
   */
  addTransition(clipId: string, position: 'in' | 'out', transition: Transition): void {
    const clip = this.findClip(clipId);
    if (!clip) throw new Error('Clip not found');
    
    clip.transitions[position] = transition;
  }
  
  /**
   * 키프레임 추가
   */
  addKeyframe(clipId: string, keyframe: Keyframe): void {
    const clip = this.findClip(clipId);
    if (!clip) throw new Error('Clip not found');
    
    clip.keyframes.push(keyframe);
    clip.keyframes.sort((a, b) => a.time - b.time);
  }
  
  /**
   * 재생/일시정지
   */
  togglePlayback(): void {
    this.state.isPlaying = !this.state.isPlaying;
    
    if (this.state.isPlaying) {
      this.startPlayback();
    } else {
      this.stopPlayback();
    }
  }
  
  /**
   * 재생 시작
   */
  private startPlayback(): void {
    let lastTime = Date.now();
    
    const animate = () => {
      if (!this.state.isPlaying) return;
      
      const now = Date.now();
      const delta = (now - lastTime) * this.state.playbackRate;
      lastTime = now;
      
      this.state.currentTime += delta;
      
      if (this.state.currentTime >= this.state.duration) {
        this.state.currentTime = 0; // 루프
      }
      
      this.render();
      this.animationFrame = requestAnimationFrame(animate);
    };
    
    animate();
  }
  
  /**
   * 재생 중지
   */
  private stopPlayback(): void {
    if (this.animationFrame !== null) {
      cancelAnimationFrame(this.animationFrame);
      this.animationFrame = null;
    }
  }
  
  /**
   * 특정 시간으로 이동
   */
  seek(time: number): void {
    this.state.currentTime = Math.max(0, Math.min(time, this.state.duration));
    this.render();
  }
  
  /**
   * 현재 프레임 렌더링
   */
  render(): void {
    if (!this.canvas || !this.ctx) return;
    
    const ctx = this.ctx;
    const { width, height } = this.canvas;
    
    // 캔버스 초기화
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#000000';
    ctx.fillRect(0, 0, width, height);
    
    // 각 트랙의 클립 렌더링
    for (const track of this.state.tracks) {
      if (track.muted) continue;
      
      for (const clip of track.clips) {
        const clipStartTime = clip.startTime;
        const clipEndTime = clip.startTime + clip.duration;
        
        // 현재 시간이 클립 범위 내인지 확인
        if (this.state.currentTime >= clipStartTime && this.state.currentTime < clipEndTime) {
          this.renderClip(ctx, clip);
        }
      }
    }
  }
  
  /**
   * 클립 렌더링
   */
  private renderClip(ctx: CanvasRenderingContext2D, clip: TimelineClip): void {
    ctx.save();
    
    // 트랜스폼 적용
    ctx.globalAlpha = clip.opacity;
    ctx.translate(clip.x, clip.y);
    ctx.rotate((clip.rotation * Math.PI) / 180);
    
    // 클립 유형별 렌더링
    if (clip.type === 'video' || clip.type === 'image') {
      // 이미지/비디오 렌더링 (실제로는 HTMLVideoElement 사용)
      ctx.fillStyle = '#4444FF';
      ctx.fillRect(0, 0, clip.width, clip.height);
    } else if (clip.type === 'text') {
      // 텍스트 렌더링
      ctx.fillStyle = '#FFFFFF';
      ctx.font = '24px Arial';
      ctx.fillText(clip.name, 0, clip.height / 2);
    }
    
    ctx.restore();
  }
  
  /**
   * 클립 찾기
   */
  private findClip(clipId: string): TimelineClip | undefined {
    for (const track of this.state.tracks) {
      const clip = track.clips.find(c => c.id === clipId);
      if (clip) return clip;
    }
    return undefined;
  }
  
  /**
   * 상태 가져오기
   */
  getState(): TimelineState {
    return this.state;
  }
  
  /**
   * 프로젝트 내보내기 (JSON)
   */
  exportProject(): string {
    return JSON.stringify(this.state, null, 2);
  }
  
  /**
   * 프로젝트 불러오기 (JSON)
   */
  importProject(json: string): void {
    const imported = JSON.parse(json);
    this.state = imported;
  }
  
  /**
   * 최종 비디오 렌더링
   */
  async renderFinal(): Promise<Blob> {
    console.log('Rendering final video...');
    
    // 실제로는 FFmpeg.wasm 사용
    // 여기서는 간단한 시뮬레이션
    
    // 모든 프레임 렌더링
    const frames: ImageData[] = [];
    const fps = 30;
    const totalFrames = Math.floor((this.state.duration / 1000) * fps);
    
    for (let i = 0; i < totalFrames; i++) {
      this.state.currentTime = (i / fps) * 1000;
      this.render();
      
      if (this.ctx) {
        const imageData = this.ctx.getImageData(
          0, 0,
          this.canvas!.width,
          this.canvas!.height
        );
        frames.push(imageData);
      }
    }
    
    // Blob으로 변환 (실제로는 FFmpeg.wasm)
    return new Blob([], { type: 'video/mp4' });
  }
}

// 싱글톤 인스턴스
export const timelineEditor = new TimelineEditor();
