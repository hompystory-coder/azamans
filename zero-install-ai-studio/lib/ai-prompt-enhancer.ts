/**
 * AI Prompt Enhancer
 * 간단한 입력을 풍부하고 창의적인 프롬프트로 자동 확장
 * 
 * 핵심 기능:
 * 1. 키워드 분석 및 컨텍스트 이해
 * 2. 시각적 디테일 자동 추가
 * 3. 분위기/감정 감지 및 강화
 * 4. 기술적 품질 키워드 추가
 * 5. 창의적 요소 삽입
 */

import { CHARACTER_PRESETS, type PresetStyle } from './presets'

export interface EnhancedPrompt {
  original: string
  enhanced: string
  scenes: string[]
  negativePrompt: string
  suggestedStyles: PresetStyle[]
  mood: string
  contentType: string
  keywords: string[]
  visualElements: string[]
  technicalQuality: string[]
}

export interface PromptAnalysis {
  keywords: string[]
  mood: string
  contentType: 'character' | 'nature' | 'technology' | 'abstract' | 'food' | 'lifestyle' | 'education' | 'entertainment'
  complexity: 'simple' | 'moderate' | 'complex'
  targetAudience: 'kids' | 'teens' | 'adults' | 'general'
}

/**
 * 키워드 데이터베이스
 */
const KEYWORD_DATABASE = {
  // 동물 관련
  animals: ['고양이', '강아지', '새', '물고기', '토끼', '햄스터', '고양이', '개', '강아지', '고양', 'cat', 'dog', 'bird', 'fish', 'pet', 'animal'],
  
  // 자연 관련
  nature: ['숲', '바다', '산', '하늘', '별', '달', '해', '꽃', '나무', 'forest', 'ocean', 'mountain', 'sky', 'star', 'flower'],
  
  // 음식 관련
  food: ['음식', '요리', '맛집', '케이크', '피자', '햄버거', 'food', 'cooking', 'cake', 'pizza'],
  
  // 기술 관련
  tech: ['AI', '로봇', '우주', '미래', '사이버', '디지털', 'robot', 'space', 'future', 'cyber', 'digital'],
  
  // 감정 관련
  emotions: ['행복', '슬픔', '즐거움', '신비', '긴장', '평화', 'happy', 'sad', 'joy', 'peace', 'mystery'],
  
  // 액션 관련
  actions: ['달리기', '점프', '날기', '춤', '싸움', 'run', 'jump', 'fly', 'dance', 'fight']
}

const MOOD_KEYWORDS = {
  cheerful: ['행복', '즐거움', '신나는', '밝은', 'happy', 'cheerful', 'bright', 'joyful'],
  mysterious: ['신비', '미스터리', '어두운', '이상한', 'mystery', 'mysterious', 'dark', 'strange'],
  peaceful: ['평화', '고요', '조용한', '편안한', 'peace', 'calm', 'quiet', 'relaxing'],
  intense: ['강렬', '긴장', '드라마틱', '폭발', 'intense', 'dramatic', 'explosive', 'powerful'],
  nostalgic: ['추억', '옛날', '과거', '빈티지', 'memory', 'vintage', 'retro', 'old'],
  funny: ['웃긴', '재미', '코미디', '유머', 'funny', 'comedy', 'humor', 'hilarious']
}

const VISUAL_STYLES = {
  anime: ['애니메이션', '만화', '2D', 'anime', 'cartoon', 'animated'],
  realistic: ['사실적', '실제', '포토', 'realistic', 'photo', 'real'],
  fantasy: ['판타지', '마법', '환상', 'fantasy', 'magic', 'mystical'],
  cyberpunk: ['사이버펑크', '네온', '미래', 'cyberpunk', 'neon', 'futuristic'],
  minimalist: ['미니멀', '단순', '깔끔', 'minimal', 'simple', 'clean']
}

/**
 * 시각적 디테일 라이브러리
 */
const VISUAL_DETAILS = {
  lighting: [
    'soft natural lighting',
    'dramatic studio lighting',
    'golden hour sunlight',
    'magical glowing lights',
    'cinematic lighting',
    'rim lighting',
    'volumetric rays'
  ],
  atmosphere: [
    'dreamy atmosphere',
    'mystical fog',
    'sparkling particles',
    'bokeh background',
    'atmospheric perspective',
    'ethereal glow'
  ],
  composition: [
    'rule of thirds',
    'centered composition',
    'dynamic angle',
    'close-up shot',
    'wide angle view',
    'symmetrical framing'
  ],
  quality: [
    '4K resolution',
    'high detail',
    'ultra sharp',
    'professional quality',
    'masterpiece',
    'trending on artstation'
  ],
  colors: [
    'vibrant colors',
    'pastel color scheme',
    'warm color palette',
    'cool tones',
    'saturated colors',
    'complementary colors'
  ]
}

/**
 * 창의적 요소 추가 템플릿
 */
const CREATIVE_ELEMENTS = {
  magical: ['magical sparkles', 'glowing aura', 'floating particles', 'enchanted atmosphere'],
  dramatic: ['dramatic shadows', 'intense contrast', 'epic scale', 'powerful presence'],
  whimsical: ['playful details', 'unexpected elements', 'imaginative twist', 'surreal touches'],
  elegant: ['refined aesthetic', 'sophisticated style', 'graceful composition', 'timeless beauty']
}

/**
 * PromptEnhancer 클래스
 */
export class PromptEnhancer {
  /**
   * 간단한 프롬프트를 완전히 분석하고 확장
   */
  async enhance(simplePrompt: string): Promise<EnhancedPrompt> {
    // 1. 입력 분석
    const analysis = this.analyzePrompt(simplePrompt)
    
    // 2. 스타일 자동 선택
    const suggestedStyles = this.suggestStyles(analysis)
    
    // 3. 시각적 디테일 추가
    const visualElements = this.addVisualDetails(analysis)
    
    // 4. 기술적 품질 키워드
    const technicalQuality = this.getTechnicalQuality(analysis)
    
    // 5. 메인 프롬프트 확장
    const enhanced = this.buildEnhancedPrompt(simplePrompt, analysis, visualElements, technicalQuality)
    
    // 6. 3장면 자동 생성
    const scenes = this.generateScenes(simplePrompt, analysis, visualElements)
    
    // 7. 네거티브 프롬프트
    const negativePrompt = this.generateNegativePrompt(analysis)
    
    return {
      original: simplePrompt,
      enhanced,
      scenes,
      negativePrompt,
      suggestedStyles,
      mood: analysis.mood,
      contentType: analysis.contentType,
      keywords: analysis.keywords,
      visualElements,
      technicalQuality
    }
  }

  /**
   * 프롬프트 분석
   */
  private analyzePrompt(prompt: string): PromptAnalysis {
    const lowercased = prompt.toLowerCase()
    const keywords: string[] = []
    let contentType: PromptAnalysis['contentType'] = 'general'
    let mood = 'cheerful'
    let complexity: PromptAnalysis['complexity'] = 'simple'
    let targetAudience: PromptAnalysis['targetAudience'] = 'general'

    // 키워드 추출
    Object.entries(KEYWORD_DATABASE).forEach(([category, words]) => {
      words.forEach(word => {
        if (lowercased.includes(word.toLowerCase())) {
          keywords.push(word)
          
          // 컨텐츠 타입 결정
          if (category === 'animals' || category === 'nature') contentType = 'nature'
          if (category === 'tech') contentType = 'technology'
          if (category === 'food') contentType = 'food'
        }
      })
    })

    // 분위기 감지
    Object.entries(MOOD_KEYWORDS).forEach(([moodName, words]) => {
      if (words.some(word => lowercased.includes(word.toLowerCase()))) {
        mood = moodName
      }
    })

    // 복잡도 판단
    if (prompt.length > 50) complexity = 'complex'
    else if (prompt.length > 20) complexity = 'moderate'

    // 타겟 오디언스 추론
    if (keywords.some(k => ['귀여운', '애니', 'cute', 'kawaii'].includes(k))) {
      targetAudience = 'kids'
    } else if (keywords.some(k => ['사이버', '미래', 'cyber', 'futuristic'].includes(k))) {
      targetAudience = 'teens'
    }

    return {
      keywords,
      mood,
      contentType,
      complexity,
      targetAudience
    }
  }

  /**
   * 스타일 자동 추천
   */
  private suggestStyles(analysis: PromptAnalysis): PresetStyle[] {
    const suggested: PresetStyle[] = []

    // 컨텐츠 타입에 따른 추천
    if (analysis.contentType === 'nature') {
      suggested.push(
        ...CHARACTER_PRESETS.filter(p => 
          ['nature', 'fantasy', 'minimalist'].includes(p.id)
        )
      )
    }
    
    if (analysis.contentType === 'technology') {
      suggested.push(
        ...CHARACTER_PRESETS.filter(p => 
          ['cyberpunk', 'minimalist'].includes(p.id)
        )
      )
    }

    // 분위기에 따른 추천
    if (analysis.mood === 'cheerful') {
      suggested.push(
        ...CHARACTER_PRESETS.filter(p => 
          ['anime-girl', 'comedy'].includes(p.id)
        )
      )
    }

    if (analysis.mood === 'mysterious') {
      suggested.push(
        ...CHARACTER_PRESETS.filter(p => 
          ['fantasy', 'horror'].includes(p.id)
        )
      )
    }

    // 타겟 오디언스에 따른 추천
    if (analysis.targetAudience === 'kids') {
      suggested.push(
        ...CHARACTER_PRESETS.filter(p => 
          ['anime-girl', 'nature'].includes(p.id)
        )
      )
    }

    // 중복 제거 및 최대 3개까지
    const unique = Array.from(new Set(suggested.map(s => s.id)))
      .map(id => CHARACTER_PRESETS.find(p => p.id === id)!)
      .filter(Boolean)
      .slice(0, 3)

    // 아무것도 없으면 기본 추천
    if (unique.length === 0) {
      return [
        CHARACTER_PRESETS.find(p => p.id === 'anime-girl')!,
        CHARACTER_PRESETS.find(p => p.id === 'cyberpunk')!,
        CHARACTER_PRESETS.find(p => p.id === 'nature')!
      ].filter(Boolean)
    }

    return unique
  }

  /**
   * 시각적 디테일 추가
   */
  private addVisualDetails(analysis: PromptAnalysis): string[] {
    const details: string[] = []

    // 조명
    details.push(this.randomPick(VISUAL_DETAILS.lighting))

    // 분위기
    details.push(this.randomPick(VISUAL_DETAILS.atmosphere))

    // 색상
    if (analysis.mood === 'cheerful') {
      details.push('vibrant colors', 'warm color palette')
    } else if (analysis.mood === 'mysterious') {
      details.push('cool tones', 'dramatic shadows')
    } else {
      details.push(this.randomPick(VISUAL_DETAILS.colors))
    }

    // 구도
    details.push(this.randomPick(VISUAL_DETAILS.composition))

    // 창의적 요소
    const creativeType = analysis.mood === 'mysterious' ? 'dramatic' : 'magical'
    details.push(this.randomPick(CREATIVE_ELEMENTS[creativeType] || CREATIVE_ELEMENTS.magical))

    return details
  }

  /**
   * 기술적 품질 키워드
   */
  private getTechnicalQuality(analysis: PromptAnalysis): string[] {
    return [
      '4K resolution',
      'high detail',
      'professional quality',
      'sharp focus',
      'masterpiece'
    ]
  }

  /**
   * 확장된 프롬프트 구축
   */
  private buildEnhancedPrompt(
    original: string,
    analysis: PromptAnalysis,
    visualElements: string[],
    technicalQuality: string[]
  ): string {
    const parts: string[] = []

    // 원본
    parts.push(original)

    // 추가 디테일 (내용에 따라)
    if (analysis.contentType === 'nature') {
      parts.push('beautiful', 'serene', 'majestic')
    } else if (analysis.contentType === 'technology') {
      parts.push('futuristic', 'high-tech', 'innovative')
    }

    // 시각적 요소
    parts.push(...visualElements.slice(0, 3))

    // 품질
    parts.push(...technicalQuality.slice(0, 2))

    return parts.join(', ')
  }

  /**
   * 3장면 자동 생성
   */
  private generateScenes(
    original: string,
    analysis: PromptAnalysis,
    visualElements: string[]
  ): string[] {
    const scenes: string[] = []

    // Scene 1: 소개 (Introduction)
    scenes.push(`${original}, establishing shot, ${visualElements[0]}, ${visualElements[1]}, wide angle`)

    // Scene 2: 메인 액션 (Main Action)
    scenes.push(`${original}, dynamic action, ${visualElements[2]}, ${visualElements[3]}, medium shot`)

    // Scene 3: 클로즈업/감정 (Closeup/Emotion)
    scenes.push(`${original}, close-up detail, emotional moment, ${visualElements[4]}, cinematic`)

    return scenes
  }

  /**
   * 네거티브 프롬프트 생성
   */
  private generateNegativePrompt(analysis: PromptAnalysis): string {
    const negatives = [
      'low quality',
      'blurry',
      'bad anatomy',
      'watermark',
      'text',
      'ugly',
      'deformed'
    ]

    // 스타일에 따라 추가
    if (analysis.contentType === 'nature') {
      negatives.push('urban', 'artificial', 'man-made')
    } else if (analysis.contentType === 'technology') {
      negatives.push('organic', 'natural', 'vintage')
    }

    return negatives.join(', ')
  }

  /**
   * 랜덤 선택 헬퍼
   */
  private randomPick<T>(array: T[]): T {
    return array[Math.floor(Math.random() * array.length)]
  }
}

/**
 * 싱글톤 인스턴스
 */
let enhancerInstance: PromptEnhancer | null = null

export function getPromptEnhancer(): PromptEnhancer {
  if (!enhancerInstance) {
    enhancerInstance = new PromptEnhancer()
  }
  return enhancerInstance
}

export default {
  PromptEnhancer,
  getPromptEnhancer
}
