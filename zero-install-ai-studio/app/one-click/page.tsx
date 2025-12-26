'use client'

/**
 * One-Click AI Shorts Generator
 * 🚀 세계 최초 완전 자동화 쇼츠 생성 시스템
 * 
 * 사용자가 단 한 문장만 입력하면:
 * - AI가 프롬프트를 자동으로 풍부하게 확장
 * - 최적의 스타일을 자동으로 선택
 * - 플랫폼에 맞게 자동 최적화
 * - 3장면 자동 생성
 * - 음악/음성 자동 매칭
 * - 완성된 쇼츠 출력
 */

import { useState } from 'react'
import Link from 'next/link'
import { getPromptEnhancer } from '@/lib/ai-prompt-enhancer'
import { getStyleSelector } from '@/lib/ai-style-selector'
import { getImageGenerator } from '@/lib/ai-engine'
import { getVideoGenerator, getTTSGenerator, getScriptGenerator } from '@/lib/video-engine'
import { applyPreset } from '@/lib/presets'

interface Stage {
  name: string
  status: 'pending' | 'processing' | 'completed' | 'error'
  progress: number
  message: string
  details?: any
}

export default function OneClickShortsPage() {
  const [input, setInput] = useState('')
  const [generating, setGenerating] = useState(false)
  const [stages, setStages] = useState<Stage[]>([
    { name: '🧠 AI 분석', status: 'pending', progress: 0, message: '대기 중...' },
    { name: '✨ 프롬프트 확장', status: 'pending', progress: 0, message: '대기 중...' },
    { name: '🎨 스타일 선택', status: 'pending', progress: 0, message: '대기 중...' },
    { name: '🖼️ 이미지 생성 (3장)', status: 'pending', progress: 0, message: '대기 중...' },
    { name: '🎙️ 음성 생성', status: 'pending', progress: 0, message: '대기 중...' },
    { name: '🎬 비디오 합성', status: 'pending', progress: 0, message: '대기 중...' },
  ])
  const [resultVideo, setResultVideo] = useState<string | null>(null)
  const [analysis, setAnalysis] = useState<any>(null)
  const [showDetails, setShowDetails] = useState(false)

  const updateStage = (index: number, updates: Partial<Stage>) => {
    setStages(prev => prev.map((stage, i) => 
      i === index ? { ...stage, ...updates } : stage
    ))
  }

  const getOverallProgress = () => {
    return Math.round(stages.reduce((sum, s) => sum + s.progress, 0) / stages.length)
  }

  const generateWithOneClick = async () => {
    if (!input.trim()) {
      alert('무엇을 만들고 싶으신가요? 간단하게 입력해주세요!\n예: "고양이", "우주", "맛있는 음식"')
      return
    }

    setGenerating(true)
    setResultVideo(null)
    setAnalysis(null)

    try {
      // Stage 1: AI 분석
      updateStage(0, { status: 'processing', message: '입력 내용 분석 중...' })
      
      const enhancer = getPromptEnhancer()
      const selector = getStyleSelector()
      
      const enhanced = await enhancer.enhance(input)
      
      updateStage(0, { 
        status: 'completed', 
        progress: 100, 
        message: `분석 완료!`,
        details: {
          keywords: enhanced.keywords,
          mood: enhanced.mood,
          contentType: enhanced.contentType
        }
      })

      // Stage 2: 프롬프트 확장
      updateStage(1, { status: 'processing', message: 'AI가 프롬프트 풍부하게 확장 중...' })
      
      await new Promise(resolve => setTimeout(resolve, 500))
      
      updateStage(1, { 
        status: 'completed', 
        progress: 100, 
        message: `확장 완료!`,
        details: {
          original: enhanced.original,
          enhanced: enhanced.enhanced,
          scenes: enhanced.scenes
        }
      })

      // Stage 3: 스타일 자동 선택
      updateStage(2, { status: 'processing', message: '최적 스타일 자동 선택 중...' })
      
      const promptAnalysis = {
        keywords: enhanced.keywords,
        mood: enhanced.mood as any,
        contentType: enhanced.contentType as any,
        complexity: 'simple' as const,
        targetAudience: 'general' as const
      }
      
      const recommendation = selector.getFullRecommendation(input, promptAnalysis)
      
      updateStage(2, { 
        status: 'completed', 
        progress: 100, 
        message: `${recommendation.styles.primary.name} 스타일 선택!`,
        details: {
          primary: recommendation.styles.primary.name,
          reason: recommendation.styles.reason,
          platform: recommendation.platform.platform
        }
      })

      // 전체 분석 결과 저장
      setAnalysis({
        enhanced,
        recommendation
      })

      // Stage 4: 이미지 생성 (3장)
      updateStage(3, { status: 'processing', message: 'AI 엔진 초기화 중...' })
      
      const imageGen = getImageGenerator()
      
      if (!imageGen.isInitialized()) {
        await imageGen.initialize((message, percent) => {
          updateStage(3, { 
            progress: percent * 0.3, 
            message: `초기화: ${message}` 
          })
        })
      }
      
      const images: string[] = []
      
      // 3장면 생성
      for (let i = 0; i < 3; i++) {
        updateStage(3, { 
          status: 'processing',
          message: `장면 ${i + 1}/3 생성 중...`,
          progress: 30 + (i * 20)
        })
        
        const applied = applyPreset(enhanced.scenes[i], recommendation.styles.primary)
        
        try {
          const imageUrl = await imageGen.generate({
            prompt: applied.enhancedPrompt,
            negativePrompt: applied.negativePrompt,
            progressCallback: (percent) => {
              updateStage(3, {
                progress: 30 + (i * 20) + (percent * 0.2)
              })
            }
          })
          
          images.push(imageUrl)
        } catch (error) {
          console.error(`Image ${i + 1} generation failed:`, error)
          // 폴백: 간단한 플레이스홀더
          images.push(`https://via.placeholder.com/1080x1920/6366f1/ffffff?text=Scene+${i+1}`)
        }
      }
      
      updateStage(3, { 
        status: 'completed', 
        progress: 100, 
        message: '3장면 생성 완료!' 
      })

      // Stage 5: 음성 생성
      updateStage(4, { status: 'processing', message: '스크립트 생성 중...' })
      
      const scriptGen = getScriptGenerator()
      const script = await scriptGen.generateScript(enhanced.enhanced, {
        style: recommendation.styles.primary.audioSettings.voiceStyle as any,
        duration: recommendation.settings.duration * 3
      })
      
      updateStage(4, { 
        progress: 50,
        message: 'TTS 음성 생성 중...' 
      })
      
      const ttsGen = getTTSGenerator()
      const audioUrl = await ttsGen.generate(script.text, {
        voice: recommendation.styles.primary.audioSettings.voiceStyle,
        rate: recommendation.styles.primary.audioSettings.rate,
        pitch: recommendation.styles.primary.audioSettings.pitch
      })
      
      updateStage(4, { 
        status: 'completed', 
        progress: 100, 
        message: '음성 생성 완료!' 
      })

      // Stage 6: 비디오 합성
      updateStage(5, { status: 'processing', message: 'FFmpeg 초기화 중...' })
      
      const videoGen = getVideoGenerator()
      await videoGen.initialize()
      
      updateStage(5, { 
        progress: 20,
        message: '이미지와 음성 합성 중...' 
      })
      
      const videoUrl = await videoGen.createFromImages(images, {
        audioUrl,
        duration: recommendation.settings.duration,
        fps: recommendation.settings.fps,
        resolution: recommendation.settings.resolution,
        transition: recommendation.styles.primary.videoSettings.transitionType,
        progressCallback: (percent) => {
          updateStage(5, {
            progress: 20 + (percent * 0.8),
            message: `비디오 렌더링 중... ${Math.round(percent)}%`
          })
        }
      })
      
      updateStage(5, { 
        status: 'completed', 
        progress: 100, 
        message: '완성!' 
      })

      // 최종 결과
      setResultVideo(videoUrl)

      // 갤러리에 저장
      const gallery = JSON.parse(localStorage.getItem('ai-shorts-gallery') || '[]')
      gallery.unshift({
        id: Date.now(),
        videoUrl,
        thumbnail: images[0],
        title: `${input} - ${recommendation.styles.primary.name}`,
        createdAt: new Date().toISOString(),
        metadata: {
          input,
          style: recommendation.styles.primary.name,
          platform: recommendation.platform.platform
        }
      })
      localStorage.setItem('ai-shorts-gallery', JSON.stringify(gallery.slice(0, 50)))

    } catch (error: any) {
      console.error('Generation error:', error)
      const failedStage = stages.findIndex(s => s.status === 'processing')
      if (failedStage >= 0) {
        updateStage(failedStage, {
          status: 'error',
          message: `오류: ${error.message || '생성 실패'}`
        })
      }
      alert('생성 중 오류가 발생했습니다. 다시 시도해주세요.')
    } finally {
      setGenerating(false)
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
      {/* 헤더 */}
      <header className="bg-white/80 backdrop-blur-md border-b border-purple-100 sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
          <Link href="/" className="flex items-center gap-3">
            <div className="w-10 h-10 bg-gradient-to-br from-purple-600 to-blue-600 rounded-xl flex items-center justify-center">
              <span className="text-2xl">🤖</span>
            </div>
            <div>
              <h1 className="text-xl font-bold text-gray-900">원클릭 AI 쇼츠</h1>
              <p className="text-xs text-gray-500">세계 최초 완전 자동화</p>
            </div>
          </Link>
          
          <div className="flex gap-2">
            <Link href="/pro-shorts" 
              className="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
              프로 모드
            </Link>
            <Link href="/gallery" 
              className="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-medium hover:bg-purple-200 transition-colors">
              갤러리
            </Link>
          </div>
        </div>
      </header>

      {/* 메인 컨텐츠 */}
      <main className="max-w-6xl mx-auto px-4 py-12">
        
        {/* 히어로 섹션 */}
        <div className="text-center mb-12">
          <div className="inline-block px-4 py-2 bg-gradient-to-r from-purple-100 to-blue-100 rounded-full mb-6">
            <span className="text-sm font-semibold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
              ✨ 초보자도 30초 만에 프로 수준 쇼츠 제작
            </span>
          </div>
          
          <h2 className="text-5xl md:text-6xl font-bold mb-6 leading-tight">
            <span className="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
              단 한 문장으로
            </span>
            <br />
            <span className="text-gray-900">완벽한 쇼츠 완성</span>
          </h2>
          
          <p className="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
            복잡한 설정 필요 없어요. 만들고 싶은 내용을 간단하게 입력하면
            <strong className="text-purple-600"> AI가 모든 것을 자동으로</strong> 처리합니다.
          </p>

          {/* 예시 입력 */}
          <div className="flex flex-wrap justify-center gap-2 mb-8">
            {['고양이', '우주 탐험', '맛있는 음식', '미래 도시', '자연의 아름다움'].map(example => (
              <button
                key={example}
                onClick={() => setInput(example)}
                className="px-4 py-2 bg-white border-2 border-purple-200 rounded-full text-sm font-medium text-gray-700 hover:border-purple-400 hover:bg-purple-50 transition-all"
              >
                💡 {example}
              </button>
            ))}
          </div>
        </div>

        {/* 입력 영역 */}
        <div className="bg-white rounded-3xl shadow-2xl p-8 mb-12">
          <div className="max-w-3xl mx-auto">
            <label className="block text-lg font-semibold text-gray-900 mb-4">
              무엇을 만들고 싶으신가요?
            </label>
            
            <div className="relative">
              <input
                type="text"
                value={input}
                onChange={(e) => setInput(e.target.value)}
                onKeyPress={(e) => e.key === 'Enter' && !generating && generateWithOneClick()}
                placeholder="예: 귀여운 강아지, 신비한 숲, 맛있는 디저트..."
                className="w-full px-6 py-5 text-lg border-2 border-purple-200 rounded-2xl focus:outline-none focus:border-purple-500 transition-colors pr-32"
                disabled={generating}
              />
              
              <button
                onClick={generateWithOneClick}
                disabled={generating || !input.trim()}
                className="absolute right-2 top-2 px-8 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {generating ? '생성 중...' : '✨ 생성하기'}
              </button>
            </div>

            <div className="mt-4 flex items-center gap-2 text-sm text-gray-500">
              <span>💡 팁:</span>
              <span>간단할수록 좋아요! AI가 자동으로 풍부하게 만들어드려요.</span>
            </div>
          </div>
        </div>

        {/* 진행 상황 */}
        {generating || resultVideo && (
          <div className="bg-white rounded-3xl shadow-2xl p-8 mb-12">
            <div className="mb-8">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-2xl font-bold text-gray-900">
                  {generating ? '🎬 AI가 작업 중이에요...' : '✅ 완성되었어요!'}
                </h3>
                <span className="text-3xl font-bold text-purple-600">
                  {getOverallProgress()}%
                </span>
              </div>
              
              <div className="w-full h-4 bg-gray-200 rounded-full overflow-hidden">
                <div
                  className="h-full bg-gradient-to-r from-purple-600 to-blue-600 transition-all duration-500"
                  style={{ width: `${getOverallProgress()}%` }}
                />
              </div>
            </div>

            {/* 단계별 진행 */}
            <div className="space-y-4">
              {stages.map((stage, index) => (
                <div
                  key={index}
                  className={`p-4 rounded-xl border-2 transition-all ${
                    stage.status === 'completed' ? 'border-green-300 bg-green-50' :
                    stage.status === 'processing' ? 'border-purple-300 bg-purple-50' :
                    stage.status === 'error' ? 'border-red-300 bg-red-50' :
                    'border-gray-200 bg-gray-50'
                  }`}
                >
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-3">
                      <span className="text-2xl">
                        {stage.status === 'completed' ? '✅' :
                         stage.status === 'processing' ? '⏳' :
                         stage.status === 'error' ? '❌' : '⏸️'}
                      </span>
                      <span className="font-semibold text-gray-900">{stage.name}</span>
                    </div>
                    <span className="text-sm font-medium text-gray-600">
                      {stage.progress}%
                    </span>
                  </div>
                  
                  <p className="text-sm text-gray-600 ml-11">{stage.message}</p>
                  
                  {stage.status === 'processing' && (
                    <div className="mt-2 ml-11 w-full h-1 bg-gray-200 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-gradient-to-r from-purple-600 to-blue-600 transition-all duration-300"
                        style={{ width: `${stage.progress}%` }}
                      />
                    </div>
                  )}

                  {stage.details && showDetails && (
                    <div className="mt-3 ml-11 p-3 bg-white rounded-lg text-xs font-mono text-gray-700">
                      <pre>{JSON.stringify(stage.details, null, 2)}</pre>
                    </div>
                  )}
                </div>
              ))}
            </div>

            {analysis && (
              <button
                onClick={() => setShowDetails(!showDetails)}
                className="mt-4 px-4 py-2 text-sm text-purple-600 hover:text-purple-800 font-medium"
              >
                {showDetails ? '숨기기' : '상세 정보 보기'} 🔍
              </button>
            )}
          </div>
        )}

        {/* 결과 비디오 */}
        {resultVideo && (
          <div className="bg-white rounded-3xl shadow-2xl p-8">
            <h3 className="text-2xl font-bold text-gray-900 mb-6 text-center">
              🎉 완성된 쇼츠
            </h3>
            
            <div className="max-w-md mx-auto mb-6">
              <video
                src={resultVideo}
                controls
                className="w-full rounded-2xl shadow-lg"
              />
            </div>

            <div className="flex justify-center gap-4">
              <a
                href={resultVideo}
                download="ai-shorts.mp4"
                className="px-8 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all"
              >
                📥 다운로드
              </a>
              
              <Link
                href="/gallery"
                className="px-8 py-3 bg-white border-2 border-purple-300 text-purple-700 rounded-xl font-semibold hover:bg-purple-50 transition-all"
              >
                🖼️ 갤러리에서 보기
              </Link>
              
              <button
                onClick={() => {
                  setResultVideo(null)
                  setInput('')
                  setAnalysis(null)
                  setStages(stages.map(s => ({ ...s, status: 'pending', progress: 0, message: '대기 중...' })))
                }}
                className="px-8 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all"
              >
                🔄 새로 만들기
              </button>
            </div>
          </div>
        )}

        {/* 기능 설명 */}
        {!generating && !resultVideo && (
          <div className="mt-16 grid md:grid-cols-3 gap-6">
            <div className="text-center p-6">
              <div className="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span className="text-3xl">🧠</span>
              </div>
              <h4 className="text-lg font-bold text-gray-900 mb-2">AI가 이해해요</h4>
              <p className="text-gray-600">
                단순한 입력도 AI가 맥락을 파악하고 풍부하게 확장합니다
              </p>
            </div>

            <div className="text-center p-6">
              <div className="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span className="text-3xl">🎨</span>
              </div>
              <h4 className="text-lg font-bold text-gray-900 mb-2">자동 스타일 매칭</h4>
              <p className="text-gray-600">
                내용에 가장 잘 어울리는 스타일을 AI가 자동으로 선택합니다
              </p>
            </div>

            <div className="text-center p-6">
              <div className="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span className="text-3xl">⚡</span>
              </div>
              <h4 className="text-lg font-bold text-gray-900 mb-2">원클릭 완성</h4>
              <p className="text-gray-600">
                클릭 한 번으로 이미지, 음성, 비디오까지 모두 자동 생성
              </p>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}
