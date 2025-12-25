/**
 * 일괄 내보내기 시스템 v1.0
 * 
 * 여러 플랫폼 형식으로 동시에 최적화 및 내보내기
 * - YouTube (16:9, 1920x1080, 30fps)
 * - TikTok (9:16, 1080x1920, 60fps)
 * - Instagram Reels (9:16, 1080x1920, 30fps)
 * - Instagram Feed (1:1, 1080x1080, 30fps)
 * - Instagram Story (9:16, 1080x1920, 30fps)
 * - Facebook (16:9, 1920x1080, 30fps)
 * - Twitter (16:9, 1280x720, 30fps)
 * - LinkedIn (16:9, 1920x1080, 30fps)
 */

export interface PlatformSpec {
  id: string;
  name: string;
  icon: string;
  aspectRatio: string;
  width: number;
  height: number;
  fps: number;
  maxDuration: number; // 초
  maxFileSize: number; // MB
  recommendedBitrate: number; // kbps
  audioChannels: 'mono' | 'stereo';
  audioSampleRate: number; // Hz
  description: string;
}

export interface ExportOptions {
  quality: 'low' | 'medium' | 'high' | 'ultra';
  format: 'mp4' | 'webm' | 'mov';
  codec: 'h264' | 'h265' | 'vp9';
  includeSubtitles: boolean;
  includeMusic: boolean;
  watermark?: string;
}

export interface ExportResult {
  platform: string;
  blob: Blob;
  filename: string;
  size: number; // bytes
  duration: number; // 초
  url: string;
}

export class BatchExporter {
  private platforms: Map<string, PlatformSpec> = new Map();
  
  constructor() {
    this.initializePlatforms();
  }
  
  /**
   * 플랫폼 사양 초기화
   */
  private initializePlatforms(): void {
    const specs: PlatformSpec[] = [
      {
        id: 'youtube',
        name: 'YouTube',
        icon: '📺',
        aspectRatio: '16:9',
        width: 1920,
        height: 1080,
        fps: 30,
        maxDuration: 900, // 15분
        maxFileSize: 256000, // 256GB (실질적 무제한)
        recommendedBitrate: 8000,
        audioChannels: 'stereo',
        audioSampleRate: 48000,
        description: 'YouTube 동영상 (HD 1080p)'
      },
      {
        id: 'youtube-shorts',
        name: 'YouTube Shorts',
        icon: '📱',
        aspectRatio: '9:16',
        width: 1080,
        height: 1920,
        fps: 30,
        maxDuration: 60,
        maxFileSize: 256000,
        recommendedBitrate: 8000,
        audioChannels: 'stereo',
        audioSampleRate: 48000,
        description: 'YouTube Shorts (세로형 60초)'
      },
      {
        id: 'tiktok',
        name: 'TikTok',
        icon: '🎵',
        aspectRatio: '9:16',
        width: 1080,
        height: 1920,
        fps: 30,
        maxDuration: 600, // 10분
        maxFileSize: 287.6, // 287.6MB
        recommendedBitrate: 6000,
        audioChannels: 'stereo',
        audioSampleRate: 44100,
        description: 'TikTok 세로형 동영상'
      },
      {
        id: 'instagram-reels',
        name: 'Instagram Reels',
        icon: '🎬',
        aspectRatio: '9:16',
        width: 1080,
        height: 1920,
        fps: 30,
        maxDuration: 90,
        maxFileSize: 4000, // 4GB
        recommendedBitrate: 6000,
        audioChannels: 'stereo',
        audioSampleRate: 48000,
        description: 'Instagram Reels (세로형 90초)'
      },
      {
        id: 'instagram-feed',
        name: 'Instagram Feed',
        icon: '📷',
        aspectRatio: '1:1',
        width: 1080,
        height: 1080,
        fps: 30,
        maxDuration: 60,
        maxFileSize: 4000,
        recommendedBitrate: 5000,
        audioChannels: 'stereo',
        audioSampleRate: 48000,
        description: 'Instagram 피드 정사각형 (1:1)'
      },
      {
        id: 'instagram-story',
        name: 'Instagram Story',
        icon: '📲',
        aspectRatio: '9:16',
        width: 1080,
        height: 1920,
        fps: 30,
        maxDuration: 15,
        maxFileSize: 4000,
        recommendedBitrate: 6000,
        audioChannels: 'stereo',
        audioSampleRate: 48000,
        description: 'Instagram 스토리 (세로형 15초)'
      },
      {
        id: 'facebook',
        name: 'Facebook',
        icon: '👤',
        aspectRatio: '16:9',
        width: 1920,
        height: 1080,
        fps: 30,
        maxDuration: 240, // 4분
        maxFileSize: 4000,
        recommendedBitrate: 5000,
        audioChannels: 'stereo',
        audioSampleRate: 48000,
        description: 'Facebook 동영상 (HD 1080p)'
      },
      {
        id: 'twitter',
        name: 'Twitter / X',
        icon: '🐦',
        aspectRatio: '16:9',
        width: 1280,
        height: 720,
        fps: 30,
        maxDuration: 140,
        maxFileSize: 512, // 512MB
        recommendedBitrate: 5000,
        audioChannels: 'stereo',
        audioSampleRate: 44100,
        description: 'Twitter 동영상 (HD 720p)'
      },
      {
        id: 'linkedin',
        name: 'LinkedIn',
        icon: '💼',
        aspectRatio: '16:9',
        width: 1920,
        height: 1080,
        fps: 30,
        maxDuration: 600, // 10분
        maxFileSize: 5000, // 5GB
        recommendedBitrate: 5000,
        audioChannels: 'stereo',
        audioSampleRate: 48000,
        description: 'LinkedIn 전문가용 (HD 1080p)'
      }
    ];
    
    specs.forEach(spec => this.platforms.set(spec.id, spec));
  }
  
  /**
   * 모든 플랫폼 사양 가져오기
   */
  getAllPlatforms(): PlatformSpec[] {
    return Array.from(this.platforms.values());
  }
  
  /**
   * 플랫폼 ID로 사양 가져오기
   */
  getPlatform(id: string): PlatformSpec | undefined {
    return this.platforms.get(id);
  }
  
  /**
   * 여러 플랫폼으로 동시 내보내기
   */
  async exportToMultiplePlatforms(
    sourceVideo: Blob,
    platformIds: string[],
    options: ExportOptions
  ): Promise<ExportResult[]> {
    const results: ExportResult[] = [];
    
    // 각 플랫폼에 대해 병렬 처리
    const promises = platformIds.map(async (platformId) => {
      const platform = this.getPlatform(platformId);
      if (!platform) {
        throw new Error(`Platform not found: ${platformId}`);
      }
      
      const result = await this.exportForPlatform(sourceVideo, platform, options);
      return result;
    });
    
    const exportResults = await Promise.all(promises);
    return exportResults;
  }
  
  /**
   * 특정 플랫폼용으로 최적화하여 내보내기
   */
  async exportForPlatform(
    sourceVideo: Blob,
    platform: PlatformSpec,
    options: ExportOptions
  ): Promise<ExportResult> {
    console.log(`Exporting for ${platform.name}...`);
    
    // Canvas로 비디오 처리
    const canvas = document.createElement('canvas');
    canvas.width = platform.width;
    canvas.height = platform.height;
    const ctx = canvas.getContext('2d');
    
    if (!ctx) {
      throw new Error('Could not get canvas context');
    }
    
    // 비디오 요소 생성
    const video = document.createElement('video');
    video.src = URL.createObjectURL(sourceVideo);
    
    await new Promise((resolve) => {
      video.onloadedmetadata = resolve;
    });
    
    // 비디오를 플랫폼 사양에 맞게 리사이즈
    // (실제로는 FFmpeg.wasm 사용)
    
    // 임시로 원본 반환 (실제로는 최적화된 버전)
    const filename = `${platform.id}_${Date.now()}.${options.format}`;
    const url = URL.createObjectURL(sourceVideo);
    
    return {
      platform: platform.name,
      blob: sourceVideo,
      filename,
      size: sourceVideo.size,
      duration: video.duration,
      url
    };
  }
  
  /**
   * 파일 크기 체크
   */
  checkFileSize(blob: Blob, platform: PlatformSpec): boolean {
    const sizeMB = blob.size / (1024 * 1024);
    return sizeMB <= platform.maxFileSize;
  }
  
  /**
   * 최적 품질 추천
   */
  getRecommendedQuality(platform: PlatformSpec, duration: number): ExportOptions['quality'] {
    // 파일 크기 제한에 따라 추천 품질 결정
    if (platform.maxFileSize < 100) {
      return 'medium';
    } else if (platform.maxFileSize < 1000) {
      return 'high';
    } else {
      return 'ultra';
    }
  }
  
  /**
   * ZIP 파일로 다운로드
   */
  async downloadAsZip(results: ExportResult[]): Promise<void> {
    // 실제로는 JSZip 사용
    console.log('Creating ZIP archive...');
    
    // 간단한 구현: 순차적으로 다운로드
    for (const result of results) {
      const a = document.createElement('a');
      a.href = result.url;
      a.download = result.filename;
      a.click();
      
      // 브라우저가 동시 다운로드를 처리할 시간 주기
      await new Promise(resolve => setTimeout(resolve, 500));
    }
  }
  
  /**
   * 플랫폼별 업로드 가이드
   */
  getUploadGuide(platformId: string): string {
    const guides: Record<string, string> = {
      'youtube': '1. YouTube Studio 열기\n2. "만들기" → "동영상 업로드"\n3. 파일 선택 및 업로드\n4. 제목, 설명, 태그 입력\n5. 썸네일 설정\n6. 게시!',
      'tiktok': '1. TikTok 앱 열기\n2. "+" 버튼 클릭\n3. "업로드" 선택\n4. 파일 선택\n5. 편집 및 필터 (선택)\n6. 캡션 및 해시태그 추가\n7. 게시!',
      'instagram-reels': '1. Instagram 앱 열기\n2. 릴스 탭 → "+" 클릭\n3. 갤러리에서 선택\n4. 편집 및 음악 추가 (선택)\n5. 캡션 및 해시태그\n6. 공유!',
      'instagram-feed': '1. Instagram 앱 열기\n2. "+" 버튼\n3. 갤러리에서 선택\n4. 필터 및 편집 (선택)\n5. 캡션, 위치, 태그\n6. 공유!',
      'facebook': '1. Facebook 열기\n2. 포스트 작성란 클릭\n3. "사진/동영상" 클릭\n4. 파일 선택\n5. 설명 및 태그 추가\n6. 게시!',
      'twitter': '1. Twitter/X 열기\n2. 트윗 작성 클릭\n3. 미디어 아이콘 클릭\n4. 파일 선택\n5. 캡션 및 해시태그\n6. 트윗!',
      'linkedin': '1. LinkedIn 열기\n2. "게시물 시작" 클릭\n3. 비디오 아이콘\n4. 파일 선택\n5. 설명 및 해시태그\n6. 게시!'
    };
    
    return guides[platformId] || '플랫폼별 업로드 가이드를 참조하세요.';
  }
  
  /**
   * 플랫폼 그룹 (빠른 선택용)
   */
  static getPlatformGroups(): Record<string, string[]> {
    return {
      'all': ['youtube', 'tiktok', 'instagram-reels', 'instagram-feed', 'facebook', 'twitter', 'linkedin'],
      'shorts': ['youtube-shorts', 'tiktok', 'instagram-reels', 'instagram-story'],
      'social': ['facebook', 'twitter', 'linkedin', 'instagram-feed'],
      'vertical': ['tiktok', 'instagram-reels', 'instagram-story', 'youtube-shorts'],
      'horizontal': ['youtube', 'facebook', 'twitter', 'linkedin']
    };
  }
}

// 싱글톤 인스턴스
export const batchExporter = new BatchExporter();
