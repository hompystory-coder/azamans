'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { getImageGenerator } from '@/lib/ai-engine'
import { getVideoGenerator, getTTSGenerator, getScriptGenerator } from '@/lib/video-engine'
import { CHARACTER_PRESETS, PLATFORM_TEMPLATES, applyPreset, type PresetStyle } from '@/lib/presets'

interface GenerationStage {
  name: string
  progress: number
  status: 'pending' | 'processing' | 'completed' | 'error'
  message: string
}

export default function ProAutoShortsPage() {
  const [topic, setTopic] = useState('')
  const [selectedPreset, setSelectedPreset] = useState<PresetStyle | null>(null)
  const [selectedPlatform, setSelectedPlatform] = useState<keyof typeof PLATFORM_TEMPLATES>('youtube')
  const [generating, setGenerating] = useState(false)
  const [stages, setStages] = useState<GenerationStage[]>([
    { name: '스크립트 생성', progress: 0, status: 'pending', message: '대기 중...' },
    { name: 'AI 이미지 생성', progress: 0, status: 'pending', message: '대기 중...' },
    { name: '음성 생성 (TTS)', progress: 0, status: 'pending', message: '대기 중...' },
    { name: '비디오 렌더링', progress: 0, status: 'pending', message: '대기 중...' },
    { name: '최종 합성', progress: 0, status: 'pending', message: '대기 중...' },
  ])
  const [resultVideo, setResultVideo] = useState<string | null>(null)
  const [showPresets, setShowPresets] = useState(false)

  const platformConfig = PLATFORM_TEMPLATES[selectedPlatform]

  const updateStage = (index: number, updates: Partial<GenerationStage>) => {
    setStages(prev => prev.map((stage, i) => 
      i === index ? { ...stage, ...updates } : stage
    ))
  }

  const getOverallProgress = () => {
    const total = stages.reduce((sum, stage) => sum + stage.progress, 0)
    return Math.round(total / stages.length)
  }

  const generateShorts = async () => {
    if (!topic.trim()) {
      alert('주제를 입력해주세요!')
      return
    }

    setGenerating(true)
    setResultVideo(null)

    try {
      // Apply preset if selected
      let enhancedPrompt = topic
      let videoSettings = { duration: 3, fps: 30, transitions: true }
      let audioSettings = { rate: 1.0, pitch: 1.0 }

      if (selectedPreset) {
        const applied = applyPreset(topic, selectedPreset)
        enhancedPrompt = applied.enhancedPrompt
        videoSettings = {
          ...videoSettings,
          ...applied.settings.videoSettings
        }
        audioSettings = applied.settings.audioSettings
      }

      // Stage 1: Script
      updateStage(0, { status: 'processing', message: `${selectedPreset?.name || '기본'} 스타일로 스크립트 생성 중...` })
      
      const scriptGen = getScriptGenerator()
      const script = await scriptGen.generateScript(enhancedPrompt, {
        style: selectedPreset?.audioSettings.voiceStyle as any || 'entertaining',
        duration: platformConfig.recommendedLength
      })
      
      updateStage(0, { status: 'completed', progress: 100, message: `완료` })
      await new Promise(resolve => setTimeout(resolve, 300))

      // Stage 2: Images (3개)
      updateStage(1, { status: 'processing', message: '스타일 적용된 이미지 생성 중...' })
      
      const imageGen = getImageGenerator()
      const images: string[] = []
      
      for (let i = 0; i < 3; i++) {
        const imagePrompt = `${enhancedPrompt}, scene ${i + 1}`
        
        const imageUrl = await imageGen.generate({
          prompt: imagePrompt,
          negativePrompt: selectedPreset?.negativePrompt,
          width: platformConfig.resolution.width,
          height: platformConfig.resolution.height,
        }, (stage, percent) => {
          updateStage(1, { 
            progress: ((i + percent / 100) / 3) * 100,
            message: `${i + 1}/3 이미지 (${selectedPreset?.name || '기본'} 스타일)`
          })
        })
        
        images.push(imageUrl)
      }
      
      updateStage(1, { status: 'completed', progress: 100, message: '완료: 3개 이미지 생성' })
      await new Promise(resolve => setTimeout(resolve, 300))

      // Stage 3: TTS
      updateStage(2, { status: 'processing', message: '음성 생성 중...' })
      
      const ttsGen = getTTSGenerator()
      let audioUrl: string | undefined
      
      if (ttsGen.isSupported()) {
        try {
          const audioBlob = await ttsGen.generateSpeech(script, {
            lang: 'ko-KR',
            rate: audioSettings.rate,
            pitch: audioSettings.pitch
          })
          audioUrl = URL.createObjectURL(audioBlob)
          updateStage(2, { status: 'completed', progress: 100, message: '완료: 음성 생성' })
        } catch (error) {
          updateStage(2, { status: 'completed', progress: 100, message: '스킵: 음성 없이 진행' })
        }
      } else {
        updateStage(2, { status: 'completed', progress: 100, message: '스킵: TTS 미지원' })
      }
      
      await new Promise(resolve => setTimeout(resolve, 300))

      // Stage 4: Video Rendering
      updateStage(3, { status: 'processing', message: `${selectedPreset?.videoSettings.transitionType || 'fade'} 트랜지션 적용 중...` })
      
      const videoGen = getVideoGenerator()
      
      const videoUrl = await videoGen.generateVideo({
        images: images,
        duration: videoSettings.duration,
        fps: videoSettings.fps,
        width: platformConfig.resolution.width,
        height: platformConfig.resolution.height,
        transitions: videoSettings.transitions,
        audio: audioUrl
      }, (stage, percent) => {
        updateStage(3, { progress: percent, message: stage })
      })
      
      updateStage(3, { status: 'completed', progress: 100, message: '완료: 비디오 렌더링' })
      await new Promise(resolve => setTimeout(resolve, 300))

      // Stage 5: Final
      updateStage(4, { status: 'processing', progress: 50, message: '최적화 중...' })
      await new Promise(resolve => setTimeout(resolve, 500))
      updateStage(4, { status: 'completed', progress: 100, message: `완료: ${platformConfig.name} 준비!` })
      
      setResultVideo(videoUrl)
      
      // Save to gallery
      if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('ai-studio-gallery') || '[]'
        const gallery = JSON.parse(stored)
        gallery.unshift({
          id: Date.now().toString(),
          type: 'video',
          url: videoUrl,
          prompt: topic,
          preset: selectedPreset?.name,
          createdAt: new Date().toISOString(),
          duration: videoSettings.duration * 3
        })
        localStorage.setItem('ai-studio-gallery', JSON.stringify(gallery))
      }

    } catch (error) {
      console.error('Generation failed:', error)
      alert(`생성 실패: ${error}`)
    } finally {
      setGenerating(false)
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      {/* Header */}
      <header className="border-b border-white/10 backdrop-blur-lg bg-black/20">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <Link href="/" className="text-2xl font-bold text-white flex items-center gap-2">
            <span className="text-3xl">🎬</span>
            <span className="gradient-text">Pro Shorts Generator</span>
          </Link>
          <div className="flex items-center gap-4">
            <Link href="/gallery" className="px-4 py-2 text-white hover:bg-white/10 rounded-lg transition-colors">
              🖼️ 갤러리
            </Link>
            <Link href="/studio" className="px-4 py-2 text-white hover:bg-white/10 rounded-lg transition-colors">
              이미지 스튜디오
            </Link>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 py-8">
        <div className="grid lg:grid-cols-3 gap-8">
          {/* Left: Input & Presets */}
          <div className="lg:col-span-1 space-y-6">
            {/* Topic Input */}
            <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20">
              <h2 className="text-xl font-bold text-white mb-4">📝 쇼츠 주제</h2>
              <textarea
                value={topic}
                onChange={(e) => setTopic(e.target.value)}
                placeholder="예: 우주의 신비..."
                className="w-full h-32 px-4 py-3 bg-white/5 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                disabled={generating}
              />
            </div>

            {/* Platform Selection */}
            <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20">
              <h2 className="text-xl font-bold text-white mb-4">📱 플랫폼</h2>
              <div className="grid grid-cols-2 gap-2">
                {Object.entries(PLATFORM_TEMPLATES).map(([key, platform]) => (
                  <button
                    key={key}
                    onClick={() => setSelectedPlatform(key as any)}
                    className={`p-3 rounded-lg font-semibold transition-all ${
                      selectedPlatform === key
                        ? 'bg-purple-500 text-white scale-105'
                        : 'bg-white/5 text-gray-300 hover:bg-white/10'
                    }`}
                  >
                    {platform.name}
                  </button>
                ))}
              </div>
              <div className="mt-3 text-sm text-gray-300">
                {platformConfig.resolution.width}x{platformConfig.resolution.height} • 최대 {platformConfig.maxDuration}초
              </div>
            </div>

            {/* Preset Selection */}
            <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20">
              <h2 className="text-xl font-bold text-white mb-4">🎨 스타일 프리셋</h2>
              
              {selectedPreset ? (
                <div className="mb-4 p-4 bg-purple-500/20 border border-purple-500/30 rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-2">
                      <span className="text-2xl">{selectedPreset.icon}</span>
                      <span className="text-white font-semibold">{selectedPreset.name}</span>
                    </div>
                    <button
                      onClick={() => setSelectedPreset(null)}
                      className="text-white/70 hover:text-white"
                    >
                      ✕
                    </button>
                  </div>
                  <div className="text-sm text-purple-200">{selectedPreset.description}</div>
                </div>
              ) : (
                <div className="mb-4 p-4 bg-white/5 rounded-lg text-center text-gray-400">
                  스타일을 선택하세요
                </div>
              )}

              <button
                onClick={() => setShowPresets(!showPresets)}
                className="w-full py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg transition-colors"
              >
                {showPresets ? '숨기기' : '프리셋 보기'}
              </button>

              {showPresets && (
                <div className="mt-4 grid grid-cols-2 gap-2 max-h-96 overflow-y-auto">
                  {CHARACTER_PRESETS.map((preset) => (
                    <button
                      key={preset.id}
                      onClick={() => {
                        setSelectedPreset(preset)
                        setShowPresets(false)
                      }}
                      className="p-3 bg-white/5 hover:bg-purple-500/20 rounded-lg transition-all text-left"
                    >
                      <div className="text-2xl mb-1">{preset.icon}</div>
                      <div className="text-white text-sm font-semibold">{preset.name}</div>
                      <div className="text-gray-400 text-xs">{preset.category}</div>
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Generate Button */}
            <button
              onClick={generateShorts}
              disabled={generating || !topic.trim()}
              className="w-full py-4 bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold text-lg rounded-xl hover:shadow-2xl transition-all disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95"
            >
              {generating ? (
                <span className="flex items-center justify-center gap-2">
                  <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                  </svg>
                  생성 중... {getOverallProgress()}%
                </span>
              ) : (
                '🚀 프로 쇼츠 생성하기'
              )}
            </button>
          </div>

          {/* Right: Progress & Result */}
          <div className="lg:col-span-2 space-y-6">
            {/* Progress */}
            <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
              <h2 className="text-2xl font-bold text-white mb-6">📊 생성 진행 상황</h2>

              <div className="space-y-4">
                {stages.map((stage, index) => (
                  <div key={index} className="bg-white/5 rounded-xl p-4">
                    <div className="flex justify-between items-center mb-2">
                      <div className="flex items-center gap-2">
                        <span className="text-2xl">
                          {stage.status === 'completed' ? '✅' :
                           stage.status === 'processing' ? '⏳' :
                           stage.status === 'error' ? '❌' : '⏸️'}
                        </span>
                        <span className="text-white font-semibold">{stage.name}</span>
                      </div>
                      <span className={`text-sm font-mono ${
                        stage.status === 'completed' ? 'text-green-400' :
                        stage.status === 'processing' ? 'text-blue-400' :
                        stage.status === 'error' ? 'text-red-400' : 'text-gray-400'
                      }`}>
                        {stage.progress}%
                      </span>
                    </div>
                    
                    <div className="h-2 bg-white/10 rounded-full overflow-hidden mb-2">
                      <div 
                        className={`h-full transition-all duration-300 ${
                          stage.status === 'completed' ? 'bg-green-500' :
                          stage.status === 'processing' ? 'bg-blue-500' :
                          stage.status === 'error' ? 'bg-red-500' : 'bg-gray-500'
                        }`}
                        style={{ width: `${stage.progress}%` }}
                      />
                    </div>
                    
                    <div className="text-sm text-gray-300">{stage.message}</div>
                  </div>
                ))}
              </div>
            </div>

            {/* Result */}
            {resultVideo && (
              <div className="bg-gradient-to-br from-green-500/20 to-blue-500/20 rounded-2xl p-8 border border-green-500/30">
                <div className="text-center mb-6">
                  <div className="text-4xl mb-3">🎉</div>
                  <div className="text-white font-bold text-2xl mb-2">프로 쇼츠 완성!</div>
                  <div className="text-gray-300">
                    {selectedPreset?.name || '기본'} 스타일 • {platformConfig.name} 최적화
                  </div>
                </div>
                
                <video 
                  src={resultVideo} 
                  controls 
                  className="w-full rounded-lg mb-6"
                  style={{ maxHeight: '500px' }}
                />
                
                <div className="grid grid-cols-3 gap-3">
                  <a 
                    href={resultVideo} 
                    download={`shorts-${Date.now()}.mp4`}
                    className="py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg text-center transition-colors"
                  >
                    📥 다운로드
                  </a>
                  <Link
                    href="/gallery"
                    className="py-3 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg text-center transition-colors"
                  >
                    🖼️ 갤러리
                  </Link>
                  <button
                    onClick={() => {
                      setResultVideo(null)
                      setTopic('')
                      setStages(stages.map(s => ({ ...s, progress: 0, status: 'pending', message: '대기 중...' })))
                    }}
                    className="py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors"
                  >
                    🔄 새로 만들기
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
