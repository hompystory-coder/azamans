/**
 * 비디오 필터 시스템 v1.0
 * 
 * 10가지 프로페셔널 필터:
 * - Vintage: 복고풍 필름 효과
 * - Cinematic: 영화같은 색감
 * - Cyberpunk: 네온 사이버펑크
 * - Warm: 따뜻한 색온도
 * - Cool: 시원한 색온도
 * - Dramatic: 드라마틱 명암
 * - Dreamy: 몽환적 효과
 * - Noir: 흑백 누아르
 * - Vibrant: 선명한 색상
 * - Natural: 자연스러운 보정
 */

export interface VideoFilter {
  id: string;
  name: string;
  description: string;
  thumbnail: string;
  applyFilter: (imageData: ImageData) => ImageData;
}

export class VideoFilterEngine {
  
  /**
   * Vintage 필터: 복고풍 필름 효과
   */
  static applyVintage(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 세피아 톤
      const r = data[i];
      const g = data[i + 1];
      const b = data[i + 2];
      
      data[i] = Math.min(255, r * 0.393 + g * 0.769 + b * 0.189); // R
      data[i + 1] = Math.min(255, r * 0.349 + g * 0.686 + b * 0.168); // G
      data[i + 2] = Math.min(255, r * 0.272 + g * 0.534 + b * 0.131); // B
      
      // 약간의 노이즈 추가
      const noise = (Math.random() - 0.5) * 20;
      data[i] += noise;
      data[i + 1] += noise;
      data[i + 2] += noise;
    }
    
    return imageData;
  }
  
  /**
   * Cinematic 필터: 영화같은 색감
   */
  static applyCinematic(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 대비 증가
      data[i] = this.adjustContrast(data[i], 1.2);
      data[i + 1] = this.adjustContrast(data[i + 1], 1.2);
      data[i + 2] = this.adjustContrast(data[i + 2], 1.2);
      
      // 채도 증가
      const gray = (data[i] + data[i + 1] + data[i + 2]) / 3;
      const factor = 1.3;
      data[i] = gray + (data[i] - gray) * factor;
      data[i + 1] = gray + (data[i + 1] - gray) * factor;
      data[i + 2] = gray + (data[i + 2] - gray) * factor;
      
      // 블루 톤 강조
      data[i + 2] *= 1.1;
    }
    
    return imageData;
  }
  
  /**
   * Cyberpunk 필터: 네온 사이버펑크
   */
  static applyCyberpunk(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 네온 컬러 강조
      const r = data[i];
      const g = data[i + 1];
      const b = data[i + 2];
      
      // 어두운 부분은 더 어둡게
      const brightness = (r + g + b) / 3;
      if (brightness < 128) {
        data[i] *= 0.5;
        data[i + 1] *= 0.5;
        data[i + 2] *= 0.5;
      } else {
        // 밝은 부분은 네온 컬러로
        data[i] = Math.min(255, r * 1.5); // 레드 강조
        data[i + 1] = Math.min(255, g * 0.8); // 그린 약화
        data[i + 2] = Math.min(255, b * 2); // 블루 강조
      }
    }
    
    return imageData;
  }
  
  /**
   * Warm 필터: 따뜻한 색온도
   */
  static applyWarm(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      data[i] = Math.min(255, data[i] * 1.1); // 레드 증가
      data[i + 1] = Math.min(255, data[i + 1] * 1.05); // 그린 약간 증가
      data[i + 2] *= 0.9; // 블루 감소
    }
    
    return imageData;
  }
  
  /**
   * Cool 필터: 시원한 색온도
   */
  static applyCool(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      data[i] *= 0.9; // 레드 감소
      data[i + 1] = Math.min(255, data[i + 1] * 1.05); // 그린 약간 증가
      data[i + 2] = Math.min(255, data[i + 2] * 1.1); // 블루 증가
    }
    
    return imageData;
  }
  
  /**
   * Dramatic 필터: 드라마틱 명암
   */
  static applyDramatic(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 대비를 극대화
      const factor = 1.5;
      data[i] = this.adjustContrast(data[i], factor);
      data[i + 1] = this.adjustContrast(data[i + 1], factor);
      data[i + 2] = this.adjustContrast(data[i + 2], factor);
      
      // 비네팅 효과 (가장자리 어둡게)
      // 이미지의 중심으로부터의 거리 계산 필요 (간소화)
    }
    
    return imageData;
  }
  
  /**
   * Dreamy 필터: 몽환적 효과
   */
  static applyDreamy(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 밝기 증가
      data[i] = Math.min(255, data[i] * 1.15);
      data[i + 1] = Math.min(255, data[i + 1] * 1.15);
      data[i + 2] = Math.min(255, data[i + 2] * 1.15);
      
      // 채도 감소 (파스텔톤)
      const gray = (data[i] + data[i + 1] + data[i + 2]) / 3;
      const factor = 0.7;
      data[i] = gray + (data[i] - gray) * factor;
      data[i + 1] = gray + (data[i + 1] - gray) * factor;
      data[i + 2] = gray + (data[i + 2] - gray) * factor;
    }
    
    return imageData;
  }
  
  /**
   * Noir 필터: 흑백 누아르
   */
  static applyNoir(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 흑백 변환 (가중 평균)
      const gray = data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
      
      // 대비 증가
      const enhanced = this.adjustContrast(gray, 1.4);
      
      data[i] = enhanced;
      data[i + 1] = enhanced;
      data[i + 2] = enhanced;
    }
    
    return imageData;
  }
  
  /**
   * Vibrant 필터: 선명한 색상
   */
  static applyVibrant(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 채도 대폭 증가
      const gray = (data[i] + data[i + 1] + data[i + 2]) / 3;
      const factor = 1.8;
      data[i] = Math.min(255, gray + (data[i] - gray) * factor);
      data[i + 1] = Math.min(255, gray + (data[i + 1] - gray) * factor);
      data[i + 2] = Math.min(255, gray + (data[i + 2] - gray) * factor);
      
      // 밝기 약간 증가
      data[i] = Math.min(255, data[i] * 1.05);
      data[i + 1] = Math.min(255, data[i + 1] * 1.05);
      data[i + 2] = Math.min(255, data[i + 2] * 1.05);
    }
    
    return imageData;
  }
  
  /**
   * Natural 필터: 자연스러운 보정
   */
  static applyNatural(imageData: ImageData): ImageData {
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
      // 약간의 대비 증가
      data[i] = this.adjustContrast(data[i], 1.05);
      data[i + 1] = this.adjustContrast(data[i + 1], 1.05);
      data[i + 2] = this.adjustContrast(data[i + 2], 1.05);
      
      // 약간의 채도 증가
      const gray = (data[i] + data[i + 1] + data[i + 2]) / 3;
      const factor = 1.1;
      data[i] = gray + (data[i] - gray) * factor;
      data[i + 1] = gray + (data[i + 1] - gray) * factor;
      data[i + 2] = gray + (data[i + 2] - gray) * factor;
    }
    
    return imageData;
  }
  
  /**
   * 대비 조정 헬퍼 함수
   */
  private static adjustContrast(value: number, factor: number): number {
    return Math.min(255, Math.max(0, ((value - 128) * factor) + 128));
  }
  
  /**
   * 모든 필터 목록 가져오기
   */
  static getAllFilters(): VideoFilter[] {
    return [
      {
        id: 'vintage',
        name: '빈티지',
        description: '복고풍 필름 효과',
        thumbnail: '🎞️',
        applyFilter: this.applyVintage
      },
      {
        id: 'cinematic',
        name: '시네마틱',
        description: '영화같은 색감',
        thumbnail: '🎬',
        applyFilter: this.applyCinematic
      },
      {
        id: 'cyberpunk',
        name: '사이버펑크',
        description: '네온 미래 효과',
        thumbnail: '🌃',
        applyFilter: this.applyCyberpunk
      },
      {
        id: 'warm',
        name: '따뜻한',
        description: '따뜻한 색온도',
        thumbnail: '☀️',
        applyFilter: this.applyWarm
      },
      {
        id: 'cool',
        name: '시원한',
        description: '시원한 색온도',
        thumbnail: '❄️',
        applyFilter: this.applyCool
      },
      {
        id: 'dramatic',
        name: '드라마틱',
        description: '강렬한 명암',
        thumbnail: '⚡',
        applyFilter: this.applyDramatic
      },
      {
        id: 'dreamy',
        name: '몽환적',
        description: '부드러운 파스텔',
        thumbnail: '💭',
        applyFilter: this.applyDreamy
      },
      {
        id: 'noir',
        name: '누아르',
        description: '흑백 영화 효과',
        thumbnail: '🎭',
        applyFilter: this.applyNoir
      },
      {
        id: 'vibrant',
        name: '선명한',
        description: '생생한 색상',
        thumbnail: '🌈',
        applyFilter: this.applyVibrant
      },
      {
        id: 'natural',
        name: '자연스러운',
        description: '자연스러운 보정',
        thumbnail: '🌿',
        applyFilter: this.applyNatural
      }
    ];
  }
  
  /**
   * 필터 ID로 필터 가져오기
   */
  static getFilterById(id: string): VideoFilter | undefined {
    return this.getAllFilters().find(filter => filter.id === id);
  }
  
  /**
   * Canvas에서 필터 적용
   */
  static applyFilterToCanvas(canvas: HTMLCanvasElement, filterId: string): void {
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const filter = this.getFilterById(filterId);
    
    if (filter) {
      const filtered = filter.applyFilter(imageData);
      ctx.putImageData(filtered, 0, 0);
    }
  }
  
  /**
   * 이미지 URL에 필터 적용
   */
  static async applyFilterToImage(imageUrl: string, filterId: string): Promise<string> {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.crossOrigin = 'anonymous';
      
      img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        
        const ctx = canvas.getContext('2d');
        if (!ctx) {
          reject(new Error('Could not get canvas context'));
          return;
        }
        
        ctx.drawImage(img, 0, 0);
        
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const filter = this.getFilterById(filterId);
        
        if (filter) {
          const filtered = filter.applyFilter(imageData);
          ctx.putImageData(filtered, 0, 0);
        }
        
        resolve(canvas.toDataURL('image/png'));
      };
      
      img.onerror = () => reject(new Error('Failed to load image'));
      img.src = imageUrl;
    });
  }
}

// 싱글톤 인스턴스 (필요 시)
export const videoFilters = VideoFilterEngine;
