/**
 * 🎯 Enhanced Progress Bar Component
 * 멋진 애니메이션과 실시간 업데이트
 */

'use client'

import { useEffect, useState } from 'react'

interface ProgressBarProps {
  progress: number
  status: 'pending' | 'processing' | 'completed' | 'error'
  label?: string
  showPercentage?: boolean
  animated?: boolean
  color?: 'blue' | 'purple' | 'green' | 'red'
}

export default function ProgressBar({
  progress,
  status,
  label,
  showPercentage = true,
  animated = true,
  color = 'blue'
}: ProgressBarProps) {
  const [displayProgress, setDisplayProgress] = useState(0)

  useEffect(() => {
    if (animated) {
      const timer = setTimeout(() => {
        setDisplayProgress(progress)
      }, 100)
      return () => clearTimeout(timer)
    } else {
      setDisplayProgress(progress)
    }
  }, [progress, animated])

  const getStatusIcon = () => {
    switch (status) {
      case 'pending':
        return '⏳'
      case 'processing':
        return '🔄'
      case 'completed':
        return '✅'
      case 'error':
        return '❌'
      default:
        return '⏳'
    }
  }

  const getColorClasses = () => {
    const base = status === 'error' ? 'red' : color
    
    switch (base) {
      case 'blue':
        return {
          bg: 'bg-blue-500',
          glow: 'shadow-blue-500/50',
          text: 'text-blue-600'
        }
      case 'purple':
        return {
          bg: 'bg-purple-500',
          glow: 'shadow-purple-500/50',
          text: 'text-purple-600'
        }
      case 'green':
        return {
          bg: 'bg-green-500',
          glow: 'shadow-green-500/50',
          text: 'text-green-600'
        }
      case 'red':
        return {
          bg: 'bg-red-500',
          glow: 'shadow-red-500/50',
          text: 'text-red-600'
        }
      default:
        return {
          bg: 'bg-blue-500',
          glow: 'shadow-blue-500/50',
          text: 'text-blue-600'
        }
    }
  }

  const colors = getColorClasses()

  return (
    <div className="w-full">
      {/* Label */}
      {label && (
        <div className="flex justify-between items-center mb-2">
          <div className="flex items-center gap-2">
            <span className="text-lg">{getStatusIcon()}</span>
            <span className="text-white font-medium">{label}</span>
          </div>
          {showPercentage && (
            <span className={`font-bold ${colors.text}`}>
              {Math.round(displayProgress)}%
            </span>
          )}
        </div>
      )}

      {/* Progress Bar */}
      <div className="relative w-full h-3 bg-gray-700 rounded-full overflow-hidden">
        {/* Background pulse for processing */}
        {status === 'processing' && (
          <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer"></div>
        )}

        {/* Progress fill */}
        <div
          className={`h-full ${colors.bg} transition-all duration-500 ease-out relative overflow-hidden ${
            status === 'processing' ? `shadow-lg ${colors.glow}` : ''
          }`}
          style={{ width: `${displayProgress}%` }}
        >
          {/* Shimmer effect */}
          {status === 'processing' && (
            <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer-fast"></div>
          )}
        </div>

        {/* Completion sparkle */}
        {status === 'completed' && displayProgress === 100 && (
          <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-transparent animate-sparkle"></div>
        )}
      </div>

      {/* Mini stats */}
      {status === 'processing' && (
        <div className="mt-1 text-xs text-gray-400 flex items-center gap-2">
          <span className="animate-pulse">●</span>
          <span>처리 중...</span>
        </div>
      )}
    </div>
  )
}

// 멀티 단계 진행바
interface MultiStageProgressProps {
  stages: Array<{
    name: string
    progress: number
    status: 'pending' | 'processing' | 'completed' | 'error'
    message?: string
  }>
  compact?: boolean
}

export function MultiStageProgress({ stages, compact = false }: MultiStageProgressProps) {
  const overallProgress = Math.round(
    stages.reduce((sum, stage) => sum + stage.progress, 0) / stages.length
  )

  const completedCount = stages.filter(s => s.status === 'completed').length

  if (compact) {
    return (
      <div className="space-y-3">
        {/* Overall progress */}
        <div>
          <div className="flex justify-between mb-2">
            <span className="text-white font-bold">전체 진행</span>
            <span className="text-purple-400 font-bold">{overallProgress}%</span>
          </div>
          <ProgressBar
            progress={overallProgress}
            status={overallProgress === 100 ? 'completed' : 'processing'}
            showPercentage={false}
            color="purple"
          />
          <div className="mt-2 text-sm text-gray-400">
            {completedCount} / {stages.length} 단계 완료
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      {/* Overall progress */}
      <div className="bg-white/5 rounded-xl p-4 backdrop-blur-sm">
        <div className="flex justify-between mb-2">
          <span className="text-white font-bold text-lg">전체 진행</span>
          <span className="text-purple-400 font-bold text-xl">{overallProgress}%</span>
        </div>
        <ProgressBar
          progress={overallProgress}
          status={overallProgress === 100 ? 'completed' : 'processing'}
          showPercentage={false}
          color="purple"
        />
        <div className="mt-3 flex items-center justify-between text-sm">
          <span className="text-gray-400">
            {completedCount} / {stages.length} 단계 완료
          </span>
          {overallProgress < 100 && (
            <span className="text-blue-400 animate-pulse">
              ⚡ 생성 중...
            </span>
          )}
        </div>
      </div>

      {/* Individual stages */}
      <div className="space-y-3">
        {stages.map((stage, index) => (
          <div
            key={index}
            className={`bg-white/5 rounded-lg p-3 backdrop-blur-sm transition-all ${
              stage.status === 'processing' ? 'ring-2 ring-blue-500/50' : ''
            }`}
          >
            <ProgressBar
              progress={stage.progress}
              status={stage.status}
              label={stage.name}
              color={stage.status === 'completed' ? 'green' : 'blue'}
            />
            {stage.message && (
              <div className="mt-2 text-xs text-gray-400 ml-7">
                {stage.message}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}
