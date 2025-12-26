/**
 * 실시간 프리뷰 시스템 v1.0
 * 
 * 기능:
 * - 생성 중 실시간 미리보기
 * - 프레임 단위 프리뷰
 * - 저해상도 빠른 미리보기
 * - 프리뷰 캐싱
 * - 메모리 최적화
 */

export interface PreviewFrame {
  id: string;
  timestamp: number;
  imageUrl: string;
  status: 'generating' | 'ready' | 'error';
  quality: 'low' | 'medium' | 'high';
}

export interface PreviewSettings {
  quality: 'low' | 'medium' | 'high';
  frameRate: number; // frames per second for preview
  maxCacheSize: number; // MB
  autoRefresh: boolean;
}

export class RealtimePreview {
  private frames: Map<string, PreviewFrame> = new Map();
  private canvas: HTMLCanvasElement;
  private ctx: CanvasRenderingContext2D;
  private settings: PreviewSettings;
  private cacheSize: number = 0;
  private isPlaying: boolean = false;
  private currentFrameIndex: number = 0;
  private animationFrameId: number | null = null;

  constructor(canvas: HTMLCanvasElement, settings?: Partial<PreviewSettings>) {
    this.canvas = canvas;
    this.ctx = canvas.getContext('2d')!;
    
    this.settings = {
      quality: settings?.quality || 'medium',
      frameRate: settings?.frameRate || 30,
      maxCacheSize: settings?.maxCacheSize || 100,
      autoRefresh: settings?.autoRefresh !== false
    };
  }

  /**
   * 새 프레임 추가
   */
  async addFrame(frameId: string, imageBlob: Blob, timestamp: number): Promise<void> {
    // 메모리 체크
    const frameSizeMB = imageBlob.size / (1024 * 1024);
    if (this.cacheSize + frameSizeMB > this.settings.maxCacheSize) {
      this.clearOldestFrames();
    }

    // 저해상도 버전 생성 (빠른 프리뷰용)
    const imageUrl = await this.createPreviewImage(imageBlob);

    const frame: PreviewFrame = {
      id: frameId,
      timestamp,
      imageUrl,
      status: 'ready',
      quality: this.settings.quality
    };

    this.frames.set(frameId, frame);
    this.cacheSize += frameSizeMB;

    // 자동 새로고침
    if (this.settings.autoRefresh && !this.isPlaying) {
      this.renderFrame(frame);
    }
  }

  /**
   * 프리뷰 이미지 생성 (해상도 조절)
   */
  private async createPreviewImage(blob: Blob): Promise<string> {
    return new Promise((resolve, reject) => {
      const img = new Image();
      const url = URL.createObjectURL(blob);

      img.onload = () => {
        // 해상도 조절
        const scale = this.getQualityScale();
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = img.width * scale;
        tempCanvas.height = img.height * scale;

        const tempCtx = tempCanvas.getContext('2d')!;
        tempCtx.drawImage(img, 0, 0, tempCanvas.width, tempCanvas.height);

        URL.revokeObjectURL(url);
        resolve(tempCanvas.toDataURL('image/jpeg', 0.8));
      };

      img.onerror = () => {
        URL.revokeObjectURL(url);
        reject(new Error('Failed to load preview image'));
      };

      img.src = url;
    });
  }

  /**
   * 품질에 따른 스케일 계산
   */
  private getQualityScale(): number {
    switch (this.settings.quality) {
      case 'low': return 0.25;
      case 'medium': return 0.5;
      case 'high': return 1.0;
      default: return 0.5;
    }
  }

  /**
   * 프레임 렌더링
   */
  private renderFrame(frame: PreviewFrame): void {
    const img = new Image();
    img.onload = () => {
      // 캔버스 크기에 맞게 조절
      const scale = Math.min(
        this.canvas.width / img.width,
        this.canvas.height / img.height
      );

      const x = (this.canvas.width - img.width * scale) / 2;
      const y = (this.canvas.height - img.height * scale) / 2;

      this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
      this.ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

      // 상태 표시
      this.drawStatusOverlay(frame);
    };
    img.src = frame.imageUrl;
  }

  /**
   * 상태 오버레이 그리기
   */
  private drawStatusOverlay(frame: PreviewFrame): void {
    const padding = 10;
    const statusText = `Frame ${frame.id} | ${frame.quality.toUpperCase()} | ${new Date(frame.timestamp).toLocaleTimeString()}`;

    this.ctx.save();
    this.ctx.font = '14px monospace';
    this.ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
    this.ctx.fillRect(0, 0, this.canvas.width, 40);
    this.ctx.fillStyle = '#00ff00';
    this.ctx.fillText(statusText, padding, 25);
    this.ctx.restore();
  }

  /**
   * 프리뷰 재생
   */
  play(): void {
    if (this.frames.size === 0) return;

    this.isPlaying = true;
    const frameArray = Array.from(this.frames.values());
    const frameDelay = 1000 / this.settings.frameRate;

    const animate = () => {
      if (!this.isPlaying) return;

      const frame = frameArray[this.currentFrameIndex];
      this.renderFrame(frame);

      this.currentFrameIndex = (this.currentFrameIndex + 1) % frameArray.length;

      setTimeout(() => {
        this.animationFrameId = requestAnimationFrame(animate);
      }, frameDelay);
    };

    animate();
  }

  /**
   * 프리뷰 일시정지
   */
  pause(): void {
    this.isPlaying = false;
    if (this.animationFrameId !== null) {
      cancelAnimationFrame(this.animationFrameId);
      this.animationFrameId = null;
    }
  }

  /**
   * 특정 프레임으로 이동
   */
  seekToFrame(frameId: string): void {
    const frame = this.frames.get(frameId);
    if (frame) {
      this.renderFrame(frame);
      const frameArray = Array.from(this.frames.values());
      this.currentFrameIndex = frameArray.findIndex(f => f.id === frameId);
    }
  }

  /**
   * 특정 시간으로 이동
   */
  seekToTime(timestamp: number): void {
    const frameArray = Array.from(this.frames.values());
    const closestFrame = frameArray.reduce((prev, curr) => {
      return Math.abs(curr.timestamp - timestamp) < Math.abs(prev.timestamp - timestamp)
        ? curr
        : prev;
    });

    if (closestFrame) {
      this.seekToFrame(closestFrame.id);
    }
  }

  /**
   * 오래된 프레임 제거 (메모리 관리)
   */
  private clearOldestFrames(): void {
    const frameArray = Array.from(this.frames.values())
      .sort((a, b) => a.timestamp - b.timestamp);

    // 가장 오래된 25% 제거
    const removeCount = Math.floor(frameArray.length * 0.25);
    for (let i = 0; i < removeCount; i++) {
      this.frames.delete(frameArray[i].id);
    }

    this.recalculateCacheSize();
  }

  /**
   * 캐시 크기 재계산
   */
  private recalculateCacheSize(): void {
    this.cacheSize = Array.from(this.frames.values())
      .reduce((sum, frame) => {
        // 대략적인 크기 추정 (base64 string length)
        return sum + (frame.imageUrl.length / 1024 / 1024);
      }, 0);
  }

  /**
   * 프리뷰 설정 업데이트
   */
  updateSettings(settings: Partial<PreviewSettings>): void {
    this.settings = { ...this.settings, ...settings };
  }

  /**
   * 모든 프레임 제거
   */
  clear(): void {
    this.pause();
    this.frames.clear();
    this.cacheSize = 0;
    this.currentFrameIndex = 0;
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
  }

  /**
   * 프리뷰 스냅샷 저장
   */
  async saveSnapshot(): Promise<Blob> {
    return new Promise((resolve) => {
      this.canvas.toBlob((blob) => {
        resolve(blob!);
      }, 'image/png');
    });
  }

  /**
   * 현재 상태 정보
   */
  getStatus() {
    return {
      totalFrames: this.frames.size,
      currentFrame: this.currentFrameIndex,
      isPlaying: this.isPlaying,
      cacheSize: `${this.cacheSize.toFixed(2)} MB`,
      quality: this.settings.quality,
      frameRate: this.settings.frameRate
    };
  }
}

/**
 * 프리뷰 생성 도우미
 */
export async function createQuickPreview(
  imageBlob: Blob,
  width: number = 400,
  height: number = 400
): Promise<string> {
  return new Promise((resolve, reject) => {
    const img = new Image();
    const url = URL.createObjectURL(imageBlob);

    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;

      const ctx = canvas.getContext('2d')!;
      const scale = Math.min(width / img.width, height / img.height);
      const x = (width - img.width * scale) / 2;
      const y = (height - img.height * scale) / 2;

      ctx.fillStyle = '#000';
      ctx.fillRect(0, 0, width, height);
      ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

      URL.revokeObjectURL(url);
      resolve(canvas.toDataURL('image/jpeg', 0.7));
    };

    img.onerror = () => {
      URL.revokeObjectURL(url);
      reject(new Error('Failed to create preview'));
    };

    img.src = url;
  });
}

export default RealtimePreview;
