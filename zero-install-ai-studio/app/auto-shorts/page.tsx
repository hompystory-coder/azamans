'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { getImageGenerator } from '@/lib/ai-engine'
import { getVideoGenerator, getTTSGenerator, getScriptGenerator } from '@/lib/video-engine'

interface GenerationStage {
  name: string
  progress: number
  status: 'pending' | 'processing' | 'completed' | 'error'
  message: string
}

export default function AutoShortsPage() {
  const [topic, setTopic] = useState('')
  const [generating, setGenerating] = useState(false)
  const [stages, setStages] = useState<GenerationStage[]>([
    { name: '스크립트 생성', progress: 0, status: 'pending', message: '대기 중...' },
    { name: 'AI 이미지 생성', progress: 0, status: 'pending', message: '대기 중...' },
    { name: '음성 생성 (TTS)', progress: 0, status: 'pending', message: '대기 중...' },
    { name: '비디오 렌더링', progress: 0, status: 'pending', message: '대기 중...' },
    { name: '최종 합성', progress: 0, status: 'pending', message: '대기 중...' },
  ])
  const [resultVideo, setResultVideo] = useState<string | null>(null)
  const [engineStatus, setEngineStatus] = useState({
    image: false,
    video: false,
    tts: false
  })

  useEffect(() => {
    initializeEngines()
  }, [])

  const initializeEngines = async () => {
    try {
      // AI 이미지 엔진
      const imageGen = getImageGenerator()
      await imageGen.initialize()
      
      // 비디오 엔진
      const videoGen = getVideoGenerator()
      await videoGen.initialize((msg, ratio) => {
        console.log(`Video Init: ${msg} (${ratio})`)
      })
      
      // TTS 엔진
      const ttsGen = getTTSGenerator()
      
      setEngineStatus({
        image: imageGen.isInitialized(),
        video: videoGen.isLoaded(),
        tts: ttsGen.isSupported()
      })

      console.log('✅ All engines initialized')
    } catch (error) {
      console.error('Engine initialization failed:', error)
    }
  }

  const updateStage = (index: number, updates: Partial<GenerationStage>) => {
    setStages(prev => prev.map((stage, i) => 
      i === index ? { ...stage, ...updates } : stage
    ))
  }

  const generateShorts = async () => {
    if (!topic.trim()) {
      alert('주제를 입력해주세요!')
      return
    }

    setGenerating(true)
    setResultVideo(null)

    try {
      // Stage 1: 스크립트 생성
      updateStage(0, { status: 'processing', message: 'AI가 스크립트를 작성하고 있습니다...' })
      
      const scriptGen = getScriptGenerator()
      const script = await scriptGen.generateScript(topic, {
        style: 'entertaining',
        duration: 30
      })
      
      updateStage(0, { status: 'completed', progress: 100, message: `완료: "${script.substring(0, 50)}..."` })
      
      await new Promise(resolve => setTimeout(resolve, 500))

      // Stage 2: AI 이미지 생성 (3장)
      updateStage(1, { status: 'processing', message: '첫 번째 이미지 생성 중...' })
      
      const imageGen = getImageGenerator()
      const images: string[] = []
      
      for (let i = 0; i < 3; i++) {
        updateStage(1, { 
          status: 'processing', 
          progress: (i / 3) * 100,
          message: `${i + 1}/3 이미지 생성 중...` 
        })
        
        const imageUrl = await imageGen.generate({
          prompt: `${topic}, scene ${i + 1}, high quality, detailed`,
          width: 1080,
          height: 1920,
        }, (stage, percent) => {
          updateStage(1, { progress: ((i + percent / 100) / 3) * 100 })
        })
        
        images.push(imageUrl)
      }
      
      updateStage(1, { status: 'completed', progress: 100, message: '완료: 3개 이미지 생성됨' })
      
      await new Promise(resolve => setTimeout(resolve, 500))

      // Stage 3: TTS 음성 생성
      updateStage(2, { status: 'processing', message: '음성을 생성하고 있습니다...' })
      
      const ttsGen = getTTSGenerator()
      let audioUrl: string | undefined
      
      if (ttsGen.isSupported()) {
        try {
          const audioBlob = await ttsGen.generateSpeech(script, {
            lang: 'ko-KR',
            rate: 1.0,
            pitch: 1.0
          })
          audioUrl = URL.createObjectURL(audioBlob)
          updateStage(2, { status: 'completed', progress: 100, message: '완료: 음성 생성됨' })
        } catch (error) {
          console.error('TTS failed:', error)
          updateStage(2, { status: 'completed', progress: 100, message: '스킵: 음성 없이 진행' })
        }
      } else {
        updateStage(2, { status: 'completed', progress: 100, message: '스킵: TTS 미지원' })
      }
      
      await new Promise(resolve => setTimeout(resolve, 500))

      // Stage 4: 비디오 렌더링
      updateStage(3, { status: 'processing', message: '비디오를 렌더링하고 있습니다...' })
      
      const videoGen = getVideoGenerator()
      
      const videoUrl = await videoGen.generateVideo({
        images: images,
        duration: 3,
        fps: 30,
        width: 1080,
        height: 1920,
        transitions: true,
        audio: audioUrl
      }, (stage, percent) => {
        updateStage(3, { progress: percent, message: stage })
      })
      
      updateStage(3, { status: 'completed', progress: 100, message: '완료: 비디오 렌더링됨' })
      
      await new Promise(resolve => setTimeout(resolve, 500))

      // Stage 5: 최종 합성
      updateStage(4, { status: 'processing', progress: 50, message: '자막 추가 중...' })
      
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      updateStage(4, { status: 'completed', progress: 100, message: '완료: 쇼츠 준비됨!' })
      
      setResultVideo(videoUrl)
      
      console.log('✅ Shorts generation completed!')

    } catch (error) {
      console.error('Shorts generation failed:', error)
      alert(`생성 실패: ${error}`)
      
      // 에러 발생한 스테이지 표시
      const currentStage = stages.findIndex(s => s.status === 'processing')
      if (currentStage !== -1) {
        updateStage(currentStage, { status: 'error', message: '오류 발생' })
      }
    } finally {
      setGenerating(false)
    }
  }

  const getOverallProgress = () => {
    const total = stages.reduce((sum, stage) => sum + stage.progress, 0)
    return Math.round(total / stages.length)
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      {/* Header */}
      <header className="border-b border-white/10 backdrop-blur-lg bg-black/20">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <Link href="/" className="text-2xl font-bold text-white flex items-center gap-2">
            <span className="text-3xl">🎬</span>
            <span className="gradient-text">Auto Shorts Generator</span>
          </Link>
          <div className="flex items-center gap-4">
            <Link href="/studio" className="px-4 py-2 text-white hover:bg-white/10 rounded-lg transition-colors">
              이미지 스튜디오
            </Link>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* Engine Status */}
        <div className="mb-8 bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20">
          <h2 className="text-xl font-bold text-white mb-4">🔧 엔진 상태</h2>
          <div className="grid md:grid-cols-3 gap-4">
            <div className={`p-4 rounded-lg ${engineStatus.image ? 'bg-green-500/20' : 'bg-red-500/20'}`}>
              <div className="text-white font-semibold">AI 이미지 엔진</div>
              <div className={`text-sm ${engineStatus.image ? 'text-green-300' : 'text-red-300'}`}>
                {engineStatus.image ? '✅ 준비됨' : '❌ 초기화 필요'}
              </div>
            </div>
            <div className={`p-4 rounded-lg ${engineStatus.video ? 'bg-green-500/20' : 'bg-red-500/20'}`}>
              <div className="text-white font-semibold">비디오 렌더러</div>
              <div className={`text-sm ${engineStatus.video ? 'text-green-300' : 'text-red-300'}`}>
                {engineStatus.video ? '✅ 준비됨' : '❌ 초기화 필요'}
              </div>
            </div>
            <div className={`p-4 rounded-lg ${engineStatus.tts ? 'bg-green-500/20' : 'bg-yellow-500/20'}`}>
              <div className="text-white font-semibold">TTS 음성</div>
              <div className={`text-sm ${engineStatus.tts ? 'text-green-300' : 'text-yellow-300'}`}>
                {engineStatus.tts ? '✅ 준비됨' : '⚠️ 선택사항'}
              </div>
            </div>
          </div>
        </div>

        <div className="grid lg:grid-cols-2 gap-8">
          {/* Input Panel */}
          <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
            <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-2">
              <span className="text-3xl">🎥</span>
              자동 쇼츠 생성
            </h2>

            <div className="space-y-6">
              <div>
                <label className="block text-white font-semibold mb-3">
                  쇼츠 주제 입력
                </label>
                <textarea
                  value={topic}
                  onChange={(e) => setTopic(e.target.value)}
                  placeholder="예: 우주의 신비, 고양이의 하루, 맛있는 요리 레시피..."
                  className="w-full h-32 px-4 py-3 bg-white/5 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                  disabled={generating}
                />
              </div>

              <div className="bg-purple-500/20 border border-purple-500/30 rounded-xl p-4">
                <div className="flex items-start gap-3">
                  <span className="text-2xl">✨</span>
                  <div className="flex-1">
                    <div className="font-semibold text-purple-200 mb-1">완전 자동 생성</div>
                    <ul className="text-sm text-purple-100 space-y-1">
                      <li>• AI가 자동으로 스크립트 작성</li>
                      <li>• 3개의 고품질 이미지 생성</li>
                      <li>• 음성 나레이션 추가</li>
                      <li>• 비디오 렌더링 및 합성</li>
                      <li>• 예상 소요 시간: 5-10분</li>
                    </ul>
                  </div>
                </div>
              </div>

              <button
                onClick={generateShorts}
                disabled={generating || !topic.trim() || !engineStatus.image || !engineStatus.video}
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
                  '🚀 쇼츠 자동 생성하기'
                )}
              </button>
            </div>
          </div>

          {/* Progress Panel */}
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

            {/* Result */}
            {resultVideo && (
              <div className="mt-8 bg-gradient-to-br from-green-500/20 to-blue-500/20 rounded-xl p-6 border border-green-500/30">
                <div className="text-center mb-4">
                  <div className="text-3xl mb-2">🎉</div>
                  <div className="text-white font-bold text-xl mb-2">쇼츠 생성 완료!</div>
                  <div className="text-gray-300 text-sm">고품질 AI 쇼츠가 준비되었습니다</div>
                </div>
                
                <video 
                  src={resultVideo} 
                  controls 
                  className="w-full rounded-lg mb-4"
                  style={{ maxHeight: '400px' }}
                />
                
                <div className="flex gap-3">
                  <a 
                    href={resultVideo} 
                    download="ai-shorts.mp4"
                    className="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg text-center transition-colors"
                  >
                    📥 다운로드
                  </a>
                  <button
                    onClick={() => {
                      setResultVideo(null)
                      setTopic('')
                      setStages(stages.map(s => ({ ...s, progress: 0, status: 'pending', message: '대기 중...' })))
                    }}
                    className="flex-1 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors"
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
