'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { getImageGenerator } from '@/lib/ai-engine'

interface SystemStatus {
  webgpu: boolean
  gpu: string
  browser: string
  modelStatus: string
}

interface GenerationProgress {
  stage: string
  progress: number
  message: string
}

export default function StudioPage() {
  const [systemStatus, setSystemStatus] = useState<SystemStatus>({
    webgpu: false,
    gpu: 'Checking...',
    browser: 'Unknown',
    modelStatus: 'Initializing...'
  })

  const [topic, setTopic] = useState('')
  const [generating, setGenerating] = useState(false)
  const [progress, setProgress] = useState<GenerationProgress>({
    stage: '',
    progress: 0,
    message: ''
  })
  const [resultImage, setResultImage] = useState<string | null>(null)
  const [aiInitialized, setAiInitialized] = useState(false)

  useEffect(() => {
    checkSystem()
    initializeAI()
  }, [])

  const checkSystem = async () => {
    // Browser detection
    const ua = navigator.userAgent
    let browser = 'Unknown'
    if (ua.includes('Chrome')) browser = 'Chrome'
    else if (ua.includes('Firefox')) browser = 'Firefox'
    else if (ua.includes('Safari')) browser = 'Safari'

    // WebGPU check with AI engine
    let webgpuSupported = false
    let gpuInfo = 'Not available'

    try {
      const generator = getImageGenerator()
      await generator.initialize((msg, percent) => {
        console.log(`Init: ${msg} (${percent}%)`)
      })

      webgpuSupported = generator.hasGPU()
      const info = await generator.getGPUInfo()
      gpuInfo = info?.device || 'WebGPU Available'

      setAiInitialized(true)
    } catch (e) {
      console.error('AI initialization failed:', e)
      gpuInfo = 'Initialization failed'
    }

    setSystemStatus({
      webgpu: webgpuSupported,
      gpu: gpuInfo,
      browser: browser,
      modelStatus: aiInitialized ? (webgpuSupported ? 'Ready (GPU Mode)' : 'Ready (CPU Mode)') : 'Initializing...'
    })
  }

  const initializeAI = async () => {
    try {
      const generator = getImageGenerator()
      
      await generator.initialize((message, percent) => {
        setSystemStatus(prev => ({
          ...prev,
          modelStatus: `${message} (${percent}%)`
        }))
      })

      setAiInitialized(true)
      
      setSystemStatus(prev => ({
        ...prev,
        modelStatus: generator.hasGPU() ? '✅ Ready (GPU Accelerated)' : '✅ Ready (CPU Mode)'
      }))

      console.log('✅ AI Engine fully initialized')
    } catch (error) {
      console.error('❌ AI initialization error:', error)
      setSystemStatus(prev => ({
        ...prev,
        modelStatus: '⚠️ Fallback Mode'
      }))
    }
  }

  const generateShort = async () => {
    if (!topic.trim()) {
      alert('주제를 입력해주세요!')
      return
    }

    setGenerating(true)
    setResultImage(null)

    try {
      const generator = getImageGenerator()

      // 실제 AI 이미지 생성
      const imageUrl = await generator.generate(
        {
          prompt: topic,
          negativePrompt: 'blurry, low quality, distorted',
          width: 512,
          height: 512,
          steps: 30,
          guidanceScale: 7.5
        },
        (stage, percent) => {
          setProgress({
            stage: stage,
            progress: percent,
            message: `${stage}... ${percent}%`
          })
        }
      )

      setProgress({ stage: '완료', progress: 100, message: '✅ 생성 완료!' })
      setResultImage(imageUrl)

      console.log('✅ Image generated:', imageUrl)

    } catch (error) {
      console.error('Generation error:', error)
      alert(`생성 중 오류가 발생했습니다: ${error}`)
    } finally {
      setGenerating(false)
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900">
      {/* Header */}
      <header className="border-b border-white/10 backdrop-blur-lg bg-black/20">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <Link href="/" className="text-2xl font-bold text-white flex items-center gap-2">
            <span className="text-3xl">🎬</span>
            <span className="gradient-text">Zero-Install AI Studio</span>
          </Link>
          <div className="flex items-center gap-4">
            <button className="px-4 py-2 text-white hover:bg-white/10 rounded-lg transition-colors">
              내 쇼츠
            </button>
            <button className="px-4 py-2 bg-gradient-to-r from-purple-500 to-blue-500 text-white rounded-lg hover:shadow-lg transition-all">
              Pro 업그레이드
            </button>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* System Status */}
        <div className="mb-8 bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20">
          <h2 className="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span className="text-2xl">⚙️</span>
            시스템 상태
          </h2>
          <div className="grid md:grid-cols-4 gap-4">
            <div className="bg-white/5 rounded-lg p-4">
              <div className="text-sm text-gray-400 mb-1">WebGPU</div>
              <div className={`font-semibold ${systemStatus.webgpu ? 'text-green-400' : 'text-yellow-400'}`}>
                {systemStatus.webgpu ? '✅ 지원됨' : '⚠️ 미지원'}
              </div>
            </div>
            <div className="bg-white/5 rounded-lg p-4">
              <div className="text-sm text-gray-400 mb-1">GPU</div>
              <div className="font-semibold text-white truncate">{systemStatus.gpu}</div>
            </div>
            <div className="bg-white/5 rounded-lg p-4">
              <div className="text-sm text-gray-400 mb-1">브라우저</div>
              <div className="font-semibold text-white">{systemStatus.browser}</div>
            </div>
            <div className="bg-white/5 rounded-lg p-4">
              <div className="text-sm text-gray-400 mb-1">AI 모델</div>
              <div className="font-semibold text-green-400">{systemStatus.modelStatus}</div>
            </div>
          </div>
        </div>

        {/* Main Studio */}
        <div className="grid lg:grid-cols-2 gap-8">
          {/* Input Panel */}
          <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
            <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-2">
              <span className="text-3xl">✨</span>
              AI 쇼츠 생성
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

              <div className="bg-blue-500/20 border border-blue-500/30 rounded-xl p-4">
                <div className="flex items-start gap-3">
                  <span className="text-2xl">💡</span>
                  <div className="flex-1">
                    <div className="font-semibold text-blue-200 mb-1">팁</div>
                    <ul className="text-sm text-blue-100 space-y-1">
                      <li>• 구체적인 주제일수록 좋은 결과가 나옵니다</li>
                      <li>• 감정이나 분위기를 함께 표현해보세요</li>
                      <li>• 첫 생성은 5-10분 소요될 수 있습니다 (모델 로딩)</li>
                    </ul>
                  </div>
                </div>
              </div>

              <button
                onClick={generateShort}
                disabled={generating || !topic.trim()}
                className="w-full py-4 bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold text-lg rounded-xl hover:shadow-2xl transition-all disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95"
              >
                {generating ? (
                  <span className="flex items-center justify-center gap-2">
                    <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    생성 중... {progress.progress}%
                  </span>
                ) : (
                  '🚀 AI 이미지 생성하기'
                )}
              </button>
            </div>
          </div>

          {/* Output Panel */}
          <div className="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
            <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-2">
              <span className="text-3xl">🎥</span>
              결과 미리보기
            </h2>

            {!generating && !resultImage && (
              <div className="h-96 flex items-center justify-center bg-white/5 rounded-xl border-2 border-dashed border-white/20">
                <div className="text-center">
                  <div className="text-6xl mb-4">📹</div>
                  <div className="text-gray-400 text-lg">생성된 쇼츠가 여기에 표시됩니다</div>
                </div>
              </div>
            )}

            {generating && (
              <div className="space-y-6">
                {/* Progress Bar */}
                <div className="bg-white/5 rounded-xl p-6">
                  <div className="flex justify-between items-center mb-3">
                    <span className="text-white font-semibold">{progress.stage}</span>
                    <span className="text-purple-400 font-bold">{progress.progress}%</span>
                  </div>
                  <div className="h-3 bg-white/10 rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-gradient-to-r from-purple-500 to-blue-500 transition-all duration-500 rounded-full"
                      style={{ width: `${progress.progress}%` }}
                    />
                  </div>
                  <div className="mt-3 text-sm text-gray-400">{progress.message}</div>
                </div>

                {/* Live Preview Placeholder */}
                <div className="bg-white/5 rounded-xl p-6 h-64 flex items-center justify-center">
                  <div className="text-center">
                    <div className="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-4" />
                    <div className="text-white font-semibold">AI가 열심히 작업 중...</div>
                  </div>
                </div>
              </div>
            )}

            {resultImage && !generating && (
              <div className="space-y-6">
                {/* Image Preview */}
                <div className="bg-gradient-to-br from-purple-900 to-blue-900 rounded-xl p-8 text-center">
                  <div className="text-6xl mb-4">🎨</div>
                  <div className="text-white text-xl font-bold mb-2">AI 이미지 생성 완료!</div>
                  <div className="text-gray-300 mb-6">주제: {topic}</div>
                  
                  {/* Actual Image Display */}
                  <div className="bg-black/50 rounded-lg p-4 mb-6">
                    <img 
                      src={resultImage} 
                      alt="Generated AI Image"
                      className="max-w-full h-auto rounded-lg mx-auto shadow-2xl"
                      style={{ maxHeight: '500px' }}
                    />
                  </div>

                  {/* Action Buttons */}
                  <div className="flex gap-4 justify-center flex-wrap">
                    <a 
                      href={resultImage} 
                      download="ai-generated-image.png"
                      className="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors"
                    >
                      📥 다운로드
                    </a>
                    <button 
                      onClick={() => {
                        setResultImage(null)
                        setTopic('')
                      }}
                      className="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors"
                    >
                      🔄 새로 만들기
                    </button>
                    <button className="px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg transition-colors">
                      🎥 비디오로 변환
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Stats */}
        <div className="mt-8 grid md:grid-cols-3 gap-6">
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
            <div className="text-3xl mb-2">⚡</div>
            <div className="text-2xl font-bold text-white mb-1">3-5분</div>
            <div className="text-gray-400">평균 생성 시간</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
            <div className="text-3xl mb-2">💰</div>
            <div className="text-2xl font-bold text-white mb-1">$0</div>
            <div className="text-gray-400">완전 무료</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
            <div className="text-3xl mb-2">🔒</div>
            <div className="text-2xl font-bold text-white mb-1">100%</div>
            <div className="text-gray-400">로컬 처리</div>
          </div>
        </div>
      </div>
    </div>
  )
}
