/**
 * Smart Style Selector
 * 입력 내용을 분석하여 최적의 스타일을 자동으로 선택
 * 
 * 핵심 기능:
 * 1. 컨텍스트 기반 스타일 매칭
 * 2. 다중 스타일 조합 추천
 * 3. 플랫폼별 최적화
 * 4. 트렌드 기반 추천
 */

import { CHARACTER_PRESETS, PLATFORM_TEMPLATES, type PresetStyle } from './presets'
import type { PromptAnalysis } from './ai-prompt-enhancer'

export interface StyleRecommendation {
  primary: PresetStyle
  alternatives: PresetStyle[]
  confidence: number
  reason: string
}

export interface PlatformRecommendation {
  platform: keyof typeof PLATFORM_TEMPLATES
  confidence: number
  reason: string
}

export interface FullRecommendation {
  styles: StyleRecommendation
  platform: PlatformRecommendation
  settings: {
    duration: number
    fps: number
    resolution: { width: number; height: number }
    music: string
    voiceStyle: string
  }
}

/**
 * 스타일 매칭 룰
 */
const STYLE_MATCHING_RULES = {
  // 키워드 → 스타일 매핑
  keywordToStyle: {
    '고양이': ['anime-girl', 'nature'],
    '강아지': ['comedy', 'nature'],
    '우주': ['cyberpunk', 'fantasy'],
    '마법': ['fantasy', 'anime-girl'],
    '공포': ['horror'],
    '웃긴': ['comedy'],
    '교육': ['educational'],
    '동기부여': ['motivational'],
    '미래': ['cyberpunk'],
    '자연': ['nature'],
    '빈티지': ['vintage']
  },

  // 분위기 → 스타일 매핑
  moodToStyle: {
    cheerful: ['anime-girl', 'comedy', 'nature'],
    mysterious: ['fantasy', 'horror', 'cyberpunk'],
    peaceful: ['nature', 'minimalist', 'vintage'],
    intense: ['cyberpunk', 'motivational', 'horror'],
    nostalgic: ['vintage', 'nature'],
    funny: ['comedy', 'anime-girl']
  },

  // 컨텐츠 타입 → 스타일 매핑
  contentTypeToStyle: {
    nature: ['nature', 'fantasy', 'minimalist'],
    technology: ['cyberpunk', 'minimalist'],
    character: ['anime-girl', 'fantasy', 'comedy'],
    abstract: ['minimalist', 'cyberpunk'],
    food: ['minimalist', 'vintage'],
    lifestyle: ['minimalist', 'nature'],
    education: ['educational', 'minimalist'],
    entertainment: ['comedy', 'anime-girl', 'cyberpunk']
  }
}

/**
 * 플랫폼 추천 룰
 */
const PLATFORM_MATCHING_RULES = {
  // 컨텐츠 타입 → 플랫폼
  contentToPlatform: {
    entertainment: 'tiktok',
    education: 'youtube',
    lifestyle: 'instagram',
    technology: 'youtube',
    nature: 'instagram'
  },

  // 길이 추천
  lengthRecommendation: {
    tiktok: 15,      // 짧고 임팩트 있게
    youtube: 30,     // 중간 길이
    instagram: 30,   // 중간 길이
    square: 30       // 범용
  }
}

/**
 * SmartStyleSelector 클래스
 */
export class SmartStyleSelector {
  /**
   * 입력 분석을 기반으로 완전한 추천 생성
   */
  getFullRecommendation(
    prompt: string,
    analysis: PromptAnalysis
  ): FullRecommendation {
    const styles = this.recommendStyles(prompt, analysis)
    const platform = this.recommendPlatform(analysis)
    const settings = this.generateOptimalSettings(styles.primary, platform, analysis)

    return {
      styles,
      platform,
      settings
    }
  }

  /**
   * 스타일 추천
   */
  recommendStyles(
    prompt: string,
    analysis: PromptAnalysis
  ): StyleRecommendation {
    const scores = new Map<string, { score: number; reasons: string[] }>()

    // 모든 프리셋 평가
    CHARACTER_PRESETS.forEach(preset => {
      scores.set(preset.id, { score: 0, reasons: [] })
    })

    // 1. 키워드 기반 매칭
    this.scoreByKeywords(prompt, scores)

    // 2. 분위기 기반 매칭
    this.scoreByMood(analysis.mood, scores)

    // 3. 컨텐츠 타입 기반 매칭
    this.scoreByContentType(analysis.contentType, scores)

    // 4. 타겟 오디언스 기반 조정
    this.scoreByAudience(analysis.targetAudience, scores)

    // 점수로 정렬
    const sorted = Array.from(scores.entries())
      .map(([id, data]) => ({
        id,
        score: data.score,
        reasons: data.reasons,
        preset: CHARACTER_PRESETS.find(p => p.id === id)!
      }))
      .filter(item => item.preset)
      .sort((a, b) => b.score - a.score)

    const top = sorted[0]
    const alternatives = sorted.slice(1, 4).map(item => item.preset)

    return {
      primary: top.preset,
      alternatives,
      confidence: Math.min(top.score / 10, 1),
      reason: top.reasons.join(', ') || '일반적인 추천'
    }
  }

  /**
   * 플랫폼 추천
   */
  recommendPlatform(analysis: PromptAnalysis): PlatformRecommendation {
    let platform: keyof typeof PLATFORM_TEMPLATES = 'youtube'
    let confidence = 0.7
    let reason = '다목적 플랫폼'

    // 컨텐츠 타입 기반
    const contentPlatform = PLATFORM_MATCHING_RULES.contentToPlatform[analysis.contentType]
    if (contentPlatform) {
      platform = contentPlatform as keyof typeof PLATFORM_TEMPLATES
      confidence = 0.9
      reason = `${analysis.contentType} 컨텐츠에 최적화`
    }

    // 복잡도 기반 조정
    if (analysis.complexity === 'simple' && analysis.targetAudience !== 'adults') {
      platform = 'tiktok'
      confidence = 0.85
      reason = '짧고 임팩트 있는 컨텐츠에 적합'
    }

    return { platform, confidence, reason }
  }

  /**
   * 최적 설정 생성
   */
  private generateOptimalSettings(
    style: PresetStyle,
    platform: PlatformRecommendation,
    analysis: PromptAnalysis
  ) {
    const platformConfig = PLATFORM_TEMPLATES[platform.platform]

    return {
      duration: style.videoSettings.duration,
      fps: style.videoSettings.fps,
      resolution: platformConfig.resolution,
      music: this.recommendMusic(analysis.mood, style),
      voiceStyle: style.audioSettings.voiceStyle
    }
  }

  /**
   * 키워드 기반 점수
   */
  private scoreByKeywords(
    prompt: string,
    scores: Map<string, { score: number; reasons: string[] }>
  ) {
    const lowercased = prompt.toLowerCase()

    Object.entries(STYLE_MATCHING_RULES.keywordToStyle).forEach(([keyword, styleIds]) => {
      if (lowercased.includes(keyword.toLowerCase())) {
        styleIds.forEach(styleId => {
          const current = scores.get(styleId)
          if (current) {
            current.score += 3
            current.reasons.push(`키워드 "${keyword}" 매칭`)
          }
        })
      }
    })
  }

  /**
   * 분위기 기반 점수
   */
  private scoreByMood(
    mood: string,
    scores: Map<string, { score: number; reasons: string[] }>
  ) {
    const styleIds = STYLE_MATCHING_RULES.moodToStyle[mood as keyof typeof STYLE_MATCHING_RULES.moodToStyle]
    if (styleIds) {
      styleIds.forEach(styleId => {
        const current = scores.get(styleId)
        if (current) {
          current.score += 5
          current.reasons.push(`${mood} 분위기에 적합`)
        }
      })
    }
  }

  /**
   * 컨텐츠 타입 기반 점수
   */
  private scoreByContentType(
    contentType: PromptAnalysis['contentType'],
    scores: Map<string, { score: number; reasons: string[] }>
  ) {
    const styleIds = STYLE_MATCHING_RULES.contentTypeToStyle[contentType]
    if (styleIds) {
      styleIds.forEach(styleId => {
        const current = scores.get(styleId)
        if (current) {
          current.score += 4
          current.reasons.push(`${contentType} 타입에 최적`)
        }
      })
    }
  }

  /**
   * 타겟 오디언스 기반 점수 조정
   */
  private scoreByAudience(
    audience: PromptAnalysis['targetAudience'],
    scores: Map<string, { score: number; reasons: string[] }>
  ) {
    if (audience === 'kids') {
      // 어린이용: 밝고 귀여운 스타일 선호
      scores.get('anime-girl')!.score += 3
      scores.get('nature')!.score += 2
      scores.get('comedy')!.score += 2
    } else if (audience === 'teens') {
      // 청소년용: 트렌디하고 역동적인 스타일
      scores.get('cyberpunk')!.score += 3
      scores.get('comedy')!.score += 2
    } else if (audience === 'adults') {
      // 성인용: 세련되고 전문적인 스타일
      scores.get('minimalist')!.score += 3
      scores.get('educational')!.score += 2
    }
  }

  /**
   * 음악 추천
   */
  private recommendMusic(mood: string, style: PresetStyle): string {
    const musicMap: Record<string, string> = {
      cheerful: 'upbeat-pop',
      mysterious: 'ambient-dark',
      peaceful: 'lo-fi-chill',
      intense: 'epic-orchestral',
      nostalgic: 'vintage-jazz',
      funny: 'quirky-comedy'
    }

    return musicMap[mood] || 'background-ambient'
  }

  /**
   * 스타일 조합 추천 (고급 기능)
   */
  recommendStyleCombination(
    prompt: string,
    analysis: PromptAnalysis
  ): PresetStyle[] {
    const recommendation = this.recommendStyles(prompt, analysis)
    
    // 메인 스타일 + 2개 대안
    return [
      recommendation.primary,
      ...recommendation.alternatives.slice(0, 2)
    ]
  }
}

/**
 * 싱글톤 인스턴스
 */
let selectorInstance: SmartStyleSelector | null = null

export function getStyleSelector(): SmartStyleSelector {
  if (!selectorInstance) {
    selectorInstance = new SmartStyleSelector()
  }
  return selectorInstance
}

export default {
  SmartStyleSelector,
  getStyleSelector
}
