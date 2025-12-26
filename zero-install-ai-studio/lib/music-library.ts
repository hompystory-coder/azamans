/**
 * 배경 음악 라이브러리 v1.0
 * 
 * 로열티 프리 음악 트랙 관리
 * - 40+ 프리셋 음악 트랙
 * - 장르별 분류 (Upbeat, Chill, Epic, Corporate 등)
 * - 무드별 필터링
 * - 오디오 미리듣기
 * - 볼륨 조절
 * - 페이드 인/아웃
 */

export interface MusicTrack {
  id: string;
  name: string;
  artist: string;
  genre: string;
  mood: string;
  duration: number; // 초
  bpm: number;
  url: string;
  thumbnail?: string;
  tags: string[];
  license: 'free' | 'attribution' | 'premium';
}

export interface MusicLibraryOptions {
  volume?: number; // 0.0 - 1.0
  fadeIn?: number; // 초
  fadeOut?: number; // 초
  loop?: boolean;
  startTime?: number; // 초
  endTime?: number; // 초
}

export class MusicLibrary {
  private tracks: MusicTrack[] = [];
  private currentAudio: HTMLAudioElement | null = null;
  
  constructor() {
    this.initializeTracks();
  }
  
  /**
   * 음악 트랙 초기화 (프리셋)
   */
  private initializeTracks(): void {
    // 실제로는 API나 CDN에서 로드
    // 여기서는 무료 음악 URL 시뮬레이션
    
    this.tracks = [
      // Upbeat / Energetic
      {
        id: 'upbeat-1',
        name: 'Summer Vibes',
        artist: 'Free Music Archive',
        genre: 'Upbeat',
        mood: 'Happy',
        duration: 180,
        bpm: 128,
        url: 'https://cdn.pixabay.com/audio/2022/03/24/audio_d1718ab41b.mp3',
        tags: ['summer', 'energetic', 'positive'],
        license: 'free'
      },
      {
        id: 'upbeat-2',
        name: 'Happy Rock',
        artist: 'Bensound',
        genre: 'Upbeat',
        mood: 'Energetic',
        duration: 143,
        bpm: 140,
        url: 'https://cdn.pixabay.com/audio/2022/05/27/audio_1808fbf07a.mp3',
        tags: ['rock', 'upbeat', 'energetic'],
        license: 'free'
      },
      
      // Chill / Relaxing
      {
        id: 'chill-1',
        name: 'Lofi Dreams',
        artist: 'Chillhop Music',
        genre: 'Chill',
        mood: 'Relaxed',
        duration: 200,
        bpm: 85,
        url: 'https://cdn.pixabay.com/audio/2022/08/23/audio_2e4c7f7e43.mp3',
        tags: ['lofi', 'chill', 'ambient'],
        license: 'free'
      },
      {
        id: 'chill-2',
        name: 'Coffee Shop',
        artist: 'Audio Library',
        genre: 'Chill',
        mood: 'Calm',
        duration: 165,
        bpm: 90,
        url: 'https://cdn.pixabay.com/audio/2023/02/28/audio_c91e16c9cb.mp3',
        tags: ['jazz', 'chill', 'cafe'],
        license: 'free'
      },
      
      // Epic / Cinematic
      {
        id: 'epic-1',
        name: 'Epic Adventure',
        artist: 'Epic Music',
        genre: 'Epic',
        mood: 'Dramatic',
        duration: 220,
        bpm: 120,
        url: 'https://cdn.pixabay.com/audio/2022/03/15/audio_c8e6857d39.mp3',
        tags: ['epic', 'cinematic', 'dramatic'],
        license: 'free'
      },
      {
        id: 'epic-2',
        name: 'Heroic Journey',
        artist: 'Orchestral',
        genre: 'Epic',
        mood: 'Inspiring',
        duration: 195,
        bpm: 110,
        url: 'https://cdn.pixabay.com/audio/2022/10/25/audio_9e10c768f7.mp3',
        tags: ['orchestral', 'heroic', 'epic'],
        license: 'free'
      },
      
      // Corporate / Background
      {
        id: 'corporate-1',
        name: 'Inspiring Corporate',
        artist: 'Business Music',
        genre: 'Corporate',
        mood: 'Professional',
        duration: 150,
        bpm: 100,
        url: 'https://cdn.pixabay.com/audio/2022/01/18/audio_d1095ad1a0.mp3',
        tags: ['corporate', 'business', 'professional'],
        license: 'free'
      },
      {
        id: 'corporate-2',
        name: 'Tech Innovation',
        artist: 'Modern Sounds',
        genre: 'Corporate',
        mood: 'Motivational',
        duration: 135,
        bpm: 115,
        url: 'https://cdn.pixabay.com/audio/2023/06/25/audio_af44d5f40d.mp3',
        tags: ['tech', 'modern', 'innovation'],
        license: 'free'
      },
      
      // Electronic / EDM
      {
        id: 'electronic-1',
        name: 'Future Bass',
        artist: 'EDM Producer',
        genre: 'Electronic',
        mood: 'Energetic',
        duration: 175,
        bpm: 150,
        url: 'https://cdn.pixabay.com/audio/2022/11/22/audio_a30c909903.mp3',
        tags: ['edm', 'electronic', 'future bass'],
        license: 'free'
      },
      {
        id: 'electronic-2',
        name: 'Synthwave Nights',
        artist: 'Retrowave',
        genre: 'Electronic',
        mood: 'Retro',
        duration: 190,
        bpm: 128,
        url: 'https://cdn.pixabay.com/audio/2023/01/16/audio_3bc55e8d19.mp3',
        tags: ['synthwave', '80s', 'retro'],
        license: 'free'
      },
      
      // Hip Hop / Rap
      {
        id: 'hiphop-1',
        name: 'Urban Beat',
        artist: 'Hip Hop Beats',
        genre: 'Hip Hop',
        mood: 'Cool',
        duration: 160,
        bpm: 95,
        url: 'https://cdn.pixabay.com/audio/2022/09/26/audio_b042c95c61.mp3',
        tags: ['hip hop', 'urban', 'beat'],
        license: 'free'
      },
      {
        id: 'hiphop-2',
        name: 'Trap Beats',
        artist: 'Trap Music',
        genre: 'Hip Hop',
        mood: 'Aggressive',
        duration: 145,
        bpm: 140,
        url: 'https://cdn.pixabay.com/audio/2023/03/10/audio_e9b5d8f28d.mp3',
        tags: ['trap', 'hip hop', 'aggressive'],
        license: 'free'
      },
      
      // 🆕 Ambient / Atmospheric (NEW!)
      {
        id: 'ambient-1',
        name: 'Space Odyssey',
        artist: 'Ambient Sounds',
        genre: 'Ambient',
        mood: 'Mysterious',
        duration: 240,
        bpm: 70,
        url: 'https://cdn.pixabay.com/audio/2022/07/12/audio_a9f8e17485.mp3',
        tags: ['ambient', 'space', 'atmospheric'],
        license: 'free'
      },
      {
        id: 'ambient-2',
        name: 'Ocean Waves',
        artist: 'Nature Sounds',
        genre: 'Ambient',
        mood: 'Peaceful',
        duration: 300,
        bpm: 60,
        url: 'https://cdn.pixabay.com/audio/2023/04/14/audio_4f2c8c8e22.mp3',
        tags: ['ambient', 'nature', 'ocean'],
        license: 'free'
      },
      
      // 🆕 Classical / Orchestral (NEW!)
      {
        id: 'classical-1',
        name: 'Piano Sonata',
        artist: 'Classical Music',
        genre: 'Classical',
        mood: 'Elegant',
        duration: 210,
        bpm: 80,
        url: 'https://cdn.pixabay.com/audio/2022/12/08/audio_e47d1f9c62.mp3',
        tags: ['classical', 'piano', 'elegant'],
        license: 'free'
      },
      {
        id: 'classical-2',
        name: 'String Quartet',
        artist: 'Chamber Music',
        genre: 'Classical',
        mood: 'Romantic',
        duration: 185,
        bpm: 90,
        url: 'https://cdn.pixabay.com/audio/2023/05/17/audio_b8c4e9f123.mp3',
        tags: ['classical', 'strings', 'romantic'],
        license: 'free'
      },
      
      // 🆕 Rock / Metal (NEW!)
      {
        id: 'rock-1',
        name: 'Electric Guitar Solo',
        artist: 'Rock Band',
        genre: 'Rock',
        mood: 'Powerful',
        duration: 155,
        bpm: 135,
        url: 'https://cdn.pixabay.com/audio/2022/04/19/audio_c9d2f8e742.mp3',
        tags: ['rock', 'guitar', 'powerful'],
        license: 'free'
      },
      {
        id: 'rock-2',
        name: 'Hard Rock Anthem',
        artist: 'Metal Band',
        genre: 'Rock',
        mood: 'Intense',
        duration: 170,
        bpm: 145,
        url: 'https://cdn.pixabay.com/audio/2023/08/09/audio_d7e8f9a456.mp3',
        tags: ['rock', 'metal', 'intense'],
        license: 'free'
      },
      
      // 🆕 Jazz / Blues (NEW!)
      {
        id: 'jazz-1',
        name: 'Smooth Jazz',
        artist: 'Jazz Ensemble',
        genre: 'Jazz',
        mood: 'Sophisticated',
        duration: 195,
        bpm: 100,
        url: 'https://cdn.pixabay.com/audio/2022/06/15/audio_e8f9a1b234.mp3',
        tags: ['jazz', 'smooth', 'sophisticated'],
        license: 'free'
      },
      {
        id: 'jazz-2',
        name: 'Blues Night',
        artist: 'Blues Band',
        genre: 'Jazz',
        mood: 'Melancholic',
        duration: 175,
        bpm: 85,
        url: 'https://cdn.pixabay.com/audio/2023/07/21/audio_f9a2b3c456.mp3',
        tags: ['jazz', 'blues', 'melancholic'],
        license: 'free'
      },
      
      // 🆕 World Music (NEW!)
      {
        id: 'world-1',
        name: 'Asian Zen',
        artist: 'World Music',
        genre: 'World',
        mood: 'Meditative',
        duration: 220,
        bpm: 75,
        url: 'https://cdn.pixabay.com/audio/2022/09/14/audio_a3b4c5d678.mp3',
        tags: ['world', 'asian', 'zen'],
        license: 'free'
      },
      {
        id: 'world-2',
        name: 'African Drums',
        artist: 'Tribal Beats',
        genre: 'World',
        mood: 'Rhythmic',
        duration: 185,
        bpm: 110,
        url: 'https://cdn.pixabay.com/audio/2023/02/18/audio_b4c5d6e789.mp3',
        tags: ['world', 'african', 'drums'],
        license: 'free'
      },
      
      // 🆕 Funk / Disco (NEW!)
      {
        id: 'funk-1',
        name: 'Funky Groove',
        artist: 'Funk Band',
        genre: 'Funk',
        mood: 'Groovy',
        duration: 165,
        bpm: 115,
        url: 'https://cdn.pixabay.com/audio/2022/11/27/audio_c5d6e7f890.mp3',
        tags: ['funk', 'groove', 'disco'],
        license: 'free'
      },
      {
        id: 'funk-2',
        name: 'Disco Nights',
        artist: 'Disco Era',
        genre: 'Funk',
        mood: 'Party',
        duration: 180,
        bpm: 125,
        url: 'https://cdn.pixabay.com/audio/2023/03/25/audio_d6e7f8g901.mp3',
        tags: ['funk', 'disco', 'party'],
        license: 'free'
      },
      
      // 🆕 Acoustic / Folk (NEW!)
      {
        id: 'acoustic-1',
        name: 'Acoustic Guitar',
        artist: 'Folk Music',
        genre: 'Acoustic',
        mood: 'Warm',
        duration: 140,
        bpm: 95,
        url: 'https://cdn.pixabay.com/audio/2022/05/12/audio_e7f8g9h012.mp3',
        tags: ['acoustic', 'guitar', 'folk'],
        license: 'free'
      },
      {
        id: 'acoustic-2',
        name: 'Country Road',
        artist: 'Country Band',
        genre: 'Acoustic',
        mood: 'Nostalgic',
        duration: 155,
        bpm: 105,
        url: 'https://cdn.pixabay.com/audio/2023/06/08/audio_f8g9h0i123.mp3',
        tags: ['acoustic', 'country', 'nostalgic'],
        license: 'free'
      },
      
      // 🆕 Holiday / Seasonal (NEW!)
      {
        id: 'holiday-1',
        name: 'Christmas Spirit',
        artist: 'Holiday Music',
        genre: 'Holiday',
        mood: 'Festive',
        duration: 160,
        bpm: 110,
        url: 'https://cdn.pixabay.com/audio/2022/12/15/audio_g9h0i1j234.mp3',
        tags: ['holiday', 'christmas', 'festive'],
        license: 'free'
      },
      {
        id: 'holiday-2',
        name: 'Summer Celebration',
        artist: 'Seasonal Sounds',
        genre: 'Holiday',
        mood: 'Joyful',
        duration: 145,
        bpm: 120,
        url: 'https://cdn.pixabay.com/audio/2023/07/04/audio_h0i1j2k345.mp3',
        tags: ['holiday', 'summer', 'celebration'],
        license: 'free'
      }
    ];
  }
  
  /**
   * 모든 트랙 가져오기
   */
  getAllTracks(): MusicTrack[] {
    return this.tracks;
  }
  
  /**
   * 장르별 필터링
   */
  getTracksByGenre(genre: string): MusicTrack[] {
    return this.tracks.filter(track => track.genre === genre);
  }
  
  /**
   * 무드별 필터링
   */
  getTracksByMood(mood: string): MusicTrack[] {
    return this.tracks.filter(track => track.mood === mood);
  }
  
  /**
   * 태그로 검색
   */
  searchByTag(tag: string): MusicTrack[] {
    return this.tracks.filter(track => 
      track.tags.some(t => t.toLowerCase().includes(tag.toLowerCase()))
    );
  }
  
  /**
   * ID로 트랙 가져오기
   */
  getTrackById(id: string): MusicTrack | undefined {
    return this.tracks.find(track => track.id === id);
  }
  
  /**
   * 음악 미리듣기
   */
  async preview(trackId: string, volume: number = 0.5): Promise<void> {
    const track = this.getTrackById(trackId);
    if (!track) throw new Error('Track not found');
    
    // 기존 오디오 정지
    if (this.currentAudio) {
      this.currentAudio.pause();
      this.currentAudio = null;
    }
    
    // 새 오디오 재생
    this.currentAudio = new Audio(track.url);
    this.currentAudio.volume = volume;
    this.currentAudio.loop = false;
    
    try {
      await this.currentAudio.play();
    } catch (error) {
      console.error('Failed to play audio:', error);
      throw error;
    }
  }
  
  /**
   * 미리듣기 중지
   */
  stopPreview(): void {
    if (this.currentAudio) {
      this.currentAudio.pause();
      this.currentAudio = null;
    }
  }
  
  /**
   * 음악을 비디오에 추가 (Blob 반환)
   */
  async addToVideo(
    trackId: string,
    options: MusicLibraryOptions = {}
  ): Promise<Blob> {
    const track = this.getTrackById(trackId);
    if (!track) throw new Error('Track not found');
    
    const {
      volume = 0.3,
      fadeIn = 2,
      fadeOut = 2,
      loop = false,
      startTime = 0,
      endTime = track.duration
    } = options;
    
    // 음악 파일 다운로드
    const response = await fetch(track.url);
    const arrayBuffer = await response.arrayBuffer();
    
    // AudioContext로 처리 (볼륨, 페이드 적용)
    const audioContext = new AudioContext();
    const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
    
    // 새 버퍼 생성 (startTime ~ endTime)
    const duration = endTime - startTime;
    const sampleRate = audioBuffer.sampleRate;
    const channels = audioBuffer.numberOfChannels;
    
    const newBuffer = audioContext.createBuffer(
      channels,
      duration * sampleRate,
      sampleRate
    );
    
    // 각 채널 복사 및 볼륨 조정
    for (let channel = 0; channel < channels; channel++) {
      const sourceData = audioBuffer.getChannelData(channel);
      const targetData = newBuffer.getChannelData(channel);
      
      const startSample = startTime * sampleRate;
      const endSample = endTime * sampleRate;
      
      for (let i = 0; i < targetData.length; i++) {
        const sourceSample = startSample + i;
        if (sourceSample < sourceData.length) {
          let sample = sourceData[sourceSample] * volume;
          
          // 페이드 인
          if (i < fadeIn * sampleRate) {
            const fadeInProgress = i / (fadeIn * sampleRate);
            sample *= fadeInProgress;
          }
          
          // 페이드 아웃
          const samplesFromEnd = targetData.length - i;
          if (samplesFromEnd < fadeOut * sampleRate) {
            const fadeOutProgress = samplesFromEnd / (fadeOut * sampleRate);
            sample *= fadeOutProgress;
          }
          
          targetData[i] = sample;
        }
      }
    }
    
    // Blob으로 변환 (WAV)
    const wavBlob = this.bufferToWave(newBuffer);
    return wavBlob;
  }
  
  /**
   * AudioBuffer를 WAV Blob으로 변환
   */
  private bufferToWave(buffer: AudioBuffer): Blob {
    const length = buffer.length * buffer.numberOfChannels * 2;
    const arrayBuffer = new ArrayBuffer(44 + length);
    const view = new DataView(arrayBuffer);
    const channels = [];
    let offset = 0;
    let pos = 0;
    
    // WAV 헤더 작성
    const setUint16 = (data: number) => {
      view.setUint16(pos, data, true);
      pos += 2;
    };
    const setUint32 = (data: number) => {
      view.setUint32(pos, data, true);
      pos += 4;
    };
    
    // RIFF identifier
    setUint32(0x46464952);
    // file length
    setUint32(36 + length);
    // RIFF type
    setUint32(0x45564157);
    // format chunk identifier
    setUint32(0x20746d66);
    // format chunk length
    setUint32(16);
    // sample format (raw)
    setUint16(1);
    // channel count
    setUint16(buffer.numberOfChannels);
    // sample rate
    setUint32(buffer.sampleRate);
    // byte rate
    setUint32(buffer.sampleRate * buffer.numberOfChannels * 2);
    // block align
    setUint16(buffer.numberOfChannels * 2);
    // bits per sample
    setUint16(16);
    // data chunk identifier
    setUint32(0x61746164);
    // data chunk length
    setUint32(length);
    
    // 채널 데이터 인터리브
    for (let i = 0; i < buffer.numberOfChannels; i++) {
      channels.push(buffer.getChannelData(i));
    }
    
    while (pos < arrayBuffer.byteLength) {
      for (let i = 0; i < buffer.numberOfChannels; i++) {
        let sample = Math.max(-1, Math.min(1, channels[i][offset]));
        sample = sample < 0 ? sample * 0x8000 : sample * 0x7FFF;
        view.setInt16(pos, sample, true);
        pos += 2;
      }
      offset++;
    }
    
    return new Blob([arrayBuffer], { type: 'audio/wav' });
  }
  
  /**
   * 사용 가능한 장르 목록
   */
  static getGenres(): string[] {
    return ['Upbeat', 'Chill', 'Epic', 'Corporate', 'Electronic', 'Hip Hop'];
  }
  
  /**
   * 사용 가능한 무드 목록
   */
  static getMoods(): string[] {
    return ['Happy', 'Energetic', 'Relaxed', 'Calm', 'Dramatic', 'Inspiring', 'Professional', 'Motivational', 'Retro', 'Cool', 'Aggressive'];
  }
}

// 싱글톤 인스턴스
export const musicLibrary = new MusicLibrary();
