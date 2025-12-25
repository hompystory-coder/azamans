/**
 * Video Generation Engine
 * 천재적 비디오 생성: 이미지 → 비디오 변환
 */

import { FFmpeg } from '@ffmpeg/ffmpeg'
import { fetchFile, toBlobURL } from '@ffmpeg/util'

export interface VideoParams {
  images: string[]           // 이미지 URL 배열
  duration?: number          // 각 이미지 지속 시간 (초)
  fps?: number              // 프레임 속도
  width?: number            // 비디오 너비
  height?: number           // 비디오 높이
  transitions?: boolean     // 트랜지션 효과
  audio?: string            // 배경 음악 URL
}

export class VideoGenerator {
  private ffmpeg: FFmpeg
  private loaded: boolean = false

  constructor() {
    this.ffmpeg = new FFmpeg()
  }

  async initialize(onProgress?: (message: string, ratio: number) => void): Promise<void> {
    if (this.loaded) return

    try {
      onProgress?.('FFmpeg 로딩 중...', 0.1)

      // FFmpeg.wasm 로드
      const baseURL = 'https://unpkg.com/@ffmpeg/core@0.12.6/dist/umd'
      
      this.ffmpeg.on('log', ({ message }) => {
        console.log('FFmpeg:', message)
      })

      this.ffmpeg.on('progress', ({ progress }) => {
        onProgress?.('비디오 처리 중...', progress)
      })

      await this.ffmpeg.load({
        coreURL: await toBlobURL(`${baseURL}/ffmpeg-core.js`, 'text/javascript'),
        wasmURL: await toBlobURL(`${baseURL}/ffmpeg-core.wasm`, 'application/wasm'),
      })

      this.loaded = true
      onProgress?.('FFmpeg 준비 완료', 1.0)
      console.log('✅ FFmpeg loaded successfully')
    } catch (error) {
      console.error('❌ FFmpeg loading failed:', error)
      throw error
    }
  }

  async generateVideo(
    params: VideoParams,
    onProgress?: (stage: string, percent: number) => void
  ): Promise<string> {
    if (!this.loaded) {
      throw new Error('FFmpeg not loaded. Call initialize() first.')
    }

    const {
      images,
      duration = 3,
      fps = 30,
      width = 1080,
      height = 1920,
      transitions = true,
      audio
    } = params

    try {
      onProgress?.('이미지 준비 중...', 10)

      // 1. 이미지들을 FFmpeg에 로드
      for (let i = 0; i < images.length; i++) {
        onProgress?.(`이미지 ${i + 1}/${images.length} 로딩...`, 10 + (i / images.length) * 20)
        const data = await fetchFile(images[i])
        await this.ffmpeg.writeFile(`image${i}.png`, data)
      }

      onProgress?.('비디오 생성 중...', 40)

      // 2. 각 이미지를 일정 시간 동안 보여주는 비디오 생성
      const frameCount = duration * fps
      
      if (transitions && images.length > 1) {
        // 트랜지션 효과 포함
        await this.generateWithTransitions(images, duration, fps, width, height)
      } else {
        // 단순 슬라이드쇼
        await this.generateSlideshow(images, duration, fps, width, height)
      }

      onProgress?.('오디오 합성 중...', 70)

      // 3. 오디오 추가 (선택사항)
      if (audio) {
        await this.addAudio(audio)
      }

      onProgress?.('최종 렌더링 중...', 90)

      // 4. 결과 읽기
      const data = await this.ffmpeg.readFile('output.mp4')
      const blob = new Blob([data], { type: 'video/mp4' })
      const url = URL.createObjectURL(blob)

      onProgress?.('완료!', 100)
      return url

    } catch (error) {
      console.error('Video generation failed:', error)
      throw error
    }
  }

  private async generateSlideshow(
    images: string[],
    duration: number,
    fps: number,
    width: number,
    height: number
  ): Promise<void> {
    // 각 이미지를 duration초 동안 보여주는 비디오 생성
    const inputs: string[] = []
    
    for (let i = 0; i < images.length; i++) {
      inputs.push('-loop', '1', '-t', duration.toString(), '-i', `image${i}.png`)
    }

    // 필터: 모든 이미지를 연결하고 크기 조정
    const filterComplex = images.map((_, i) => 
      `[${i}:v]scale=${width}:${height}:force_original_aspect_ratio=decrease,pad=${width}:${height}:-1:-1:color=black,setsar=1,fps=${fps}[v${i}]`
    ).join(';') + ';' + images.map((_, i) => `[v${i}]`).join('') + `concat=n=${images.length}:v=1:a=0[outv]`

    await this.ffmpeg.exec([
      ...inputs.flatMap(x => [x]),
      '-filter_complex', filterComplex,
      '-map', '[outv]',
      '-c:v', 'libx264',
      '-pix_fmt', 'yuv420p',
      'output.mp4'
    ])
  }

  private async generateWithTransitions(
    images: string[],
    duration: number,
    fps: number,
    width: number,
    height: number
  ): Promise<void> {
    // 페이드 트랜지션이 있는 비디오 생성
    const transitionDuration = 0.5 // 0.5초 트랜지션
    const inputs: string[] = []
    
    for (let i = 0; i < images.length; i++) {
      inputs.push('-loop', '1', '-t', (duration + transitionDuration).toString(), '-i', `image${i}.png`)
    }

    // xfade 필터로 부드러운 전환 효과
    let filterComplex = images.map((_, i) => 
      `[${i}:v]scale=${width}:${height}:force_original_aspect_ratio=decrease,pad=${width}:${height}:-1:-1:color=black,setsar=1,fps=${fps}[v${i}]`
    ).join(';') + ';'

    // xfade transitions
    filterComplex += `[v0][v1]xfade=transition=fade:duration=${transitionDuration}:offset=${duration}[vt1]`
    
    for (let i = 1; i < images.length - 1; i++) {
      filterComplex += `;[vt${i}][v${i + 1}]xfade=transition=fade:duration=${transitionDuration}:offset=${(duration + transitionDuration) * i + duration}[vt${i + 1}]`
    }

    const outputLabel = images.length > 1 ? `[vt${images.length - 1}]` : '[v0]'

    await this.ffmpeg.exec([
      ...inputs.flatMap(x => [x]),
      '-filter_complex', filterComplex,
      '-map', outputLabel,
      '-c:v', 'libx264',
      '-pix_fmt', 'yuv420p',
      'output.mp4'
    ])
  }

  private async addAudio(audioUrl: string): Promise<void> {
    // 오디오 추가
    const audioData = await fetchFile(audioUrl)
    await this.ffmpeg.writeFile('audio.mp3', audioData)

    // 비디오와 오디오 합성
    await this.ffmpeg.exec([
      '-i', 'output.mp4',
      '-i', 'audio.mp3',
      '-c:v', 'copy',
      '-c:a', 'aac',
      '-shortest',
      'output_with_audio.mp4'
    ])

    // 결과를 output.mp4로 이동
    const data = await this.ffmpeg.readFile('output_with_audio.mp4')
    await this.ffmpeg.writeFile('output.mp4', data)
  }

  isLoaded(): boolean {
    return this.loaded
  }

  async cleanup(): Promise<void> {
    // 임시 파일 정리
    try {
      const files = await this.ffmpeg.listDir('/')
      for (const file of files) {
        if (file.name.startsWith('image') || file.name.includes('output')) {
          await this.ffmpeg.deleteFile(file.name)
        }
      }
    } catch (error) {
      console.warn('Cleanup warning:', error)
    }
  }
}

// TTS 엔진
export class TTSGenerator {
  private synth: SpeechSynthesis | null = null
  private voices: SpeechSynthesisVoice[] = []

  constructor() {
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
      this.synth = window.speechSynthesis
      this.loadVoices()
    }
  }

  private loadVoices(): void {
    if (!this.synth) return

    this.voices = this.synth.getVoices()
    
    if (this.voices.length === 0) {
      // 일부 브라우저는 비동기로 voice를 로드
      this.synth.onvoiceschanged = () => {
        this.voices = this.synth!.getVoices()
        console.log('✅ Loaded voices:', this.voices.length)
      }
    }
  }

  async generateSpeech(
    text: string,
    options?: {
      lang?: string
      rate?: number
      pitch?: number
      volume?: number
      voiceName?: string
    }
  ): Promise<Blob> {
    return new Promise((resolve, reject) => {
      if (!this.synth) {
        reject(new Error('Speech Synthesis not supported'))
        return
      }

      const utterance = new SpeechSynthesisUtterance(text)
      
      // 옵션 설정
      utterance.lang = options?.lang || 'en-US'
      utterance.rate = options?.rate || 1.0
      utterance.pitch = options?.pitch || 1.0
      utterance.volume = options?.volume || 1.0

      // 음성 선택
      if (options?.voiceName) {
        const voice = this.voices.find(v => v.name === options.voiceName)
        if (voice) utterance.voice = voice
      }

      // Web Audio API로 녹음
      const audioContext = new AudioContext()
      const dest = audioContext.createMediaStreamDestination()
      const mediaRecorder = new MediaRecorder(dest.stream)
      const chunks: Blob[] = []

      mediaRecorder.ondataavailable = (e) => {
        chunks.push(e.data)
      }

      mediaRecorder.onstop = () => {
        const blob = new Blob(chunks, { type: 'audio/webm' })
        resolve(blob)
      }

      utterance.onstart = () => {
        mediaRecorder.start()
      }

      utterance.onend = () => {
        mediaRecorder.stop()
      }

      utterance.onerror = (error) => {
        reject(error)
      }

      this.synth.speak(utterance)
    })
  }

  getAvailableVoices(): SpeechSynthesisVoice[] {
    return this.voices
  }

  isSupported(): boolean {
    return this.synth !== null
  }
}

// 스크립트 생성기
export class ScriptGenerator {
  private apiKey: string = ''

  setApiKey(key: string): void {
    this.apiKey = key
  }

  async generateScript(
    topic: string,
    options?: {
      style?: 'informative' | 'entertaining' | 'educational' | 'dramatic'
      duration?: number
      language?: string
    }
  ): Promise<string> {
    const {
      style = 'entertaining',
      duration = 30,
      language = 'korean'
    } = options || {}

    // 데모: 간단한 스크립트 생성
    const templates = {
      informative: `${topic}에 대해 알아봅시다. 이것은 매우 흥미로운 주제입니다. 많은 사람들이 궁금해하는 내용이죠.`,
      entertaining: `와! ${topic}! 정말 재미있는 이야기가 있어요. 듣고 나면 깜짝 놀랄 거예요!`,
      educational: `오늘은 ${topic}에 대해 배워볼게요. 이 개념을 이해하면 정말 유용할 거예요.`,
      dramatic: `${topic}... 이것은 당신이 알아야 할 중요한 진실입니다. 지금부터 그 비밀을 밝히겠습니다.`
    }

    // 실제로는 OpenAI/Gemini API 호출
    // const response = await fetch('https://api.openai.com/v1/chat/completions', ...)
    
    return templates[style]
  }
}

// 전역 싱글톤
let globalVideoGenerator: VideoGenerator | null = null
let globalTTSGenerator: TTSGenerator | null = null
let globalScriptGenerator: ScriptGenerator | null = null

export function getVideoGenerator(): VideoGenerator {
  if (!globalVideoGenerator) {
    globalVideoGenerator = new VideoGenerator()
  }
  return globalVideoGenerator
}

export function getTTSGenerator(): TTSGenerator {
  if (!globalTTSGenerator) {
    globalTTSGenerator = new TTSGenerator()
  }
  return globalTTSGenerator
}

export function getScriptGenerator(): ScriptGenerator {
  if (!globalScriptGenerator) {
    globalScriptGenerator = new ScriptGenerator()
  }
  return globalScriptGenerator
}

export default {
  VideoGenerator,
  TTSGenerator,
  ScriptGenerator,
  getVideoGenerator,
  getTTSGenerator,
  getScriptGenerator
}
