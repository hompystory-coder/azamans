/**
 * Presets & Templates System
 * 천재적 프리셋: 프로페셔널 스타일 원클릭 적용
 */

export interface PresetStyle {
  id: string
  name: string
  description: string
  category: 'character' | 'theme' | 'mood' | 'platform'
  icon: string
  promptModifier: string
  negativePrompt: string
  imageStyle: {
    colorScheme: string[]
    mood: string
    visualStyle: string
  }
  videoSettings: {
    transitionType: 'fade' | 'slide' | 'zoom' | 'dissolve'
    duration: number
    fps: number
  }
  audioSettings: {
    voiceStyle: string
    rate: number
    pitch: number
  }
  thumbnail?: string
}

export const CHARACTER_PRESETS: PresetStyle[] = [
  {
    id: 'anime-girl',
    name: '애니메이션 소녀',
    description: '귀여운 애니메이션 스타일',
    category: 'character',
    icon: '👧',
    promptModifier: 'anime style, cute girl character, kawaii, vibrant colors',
    negativePrompt: 'realistic, photo, 3d',
    imageStyle: {
      colorScheme: ['#FF69B4', '#FFB6C1', '#FFC0CB'],
      mood: 'cheerful',
      visualStyle: 'anime'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 3,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'cheerful',
      rate: 1.1,
      pitch: 1.2
    }
  },
  {
    id: 'cyberpunk',
    name: '사이버펑크',
    description: '미래적이고 네온 스타일',
    category: 'theme',
    icon: '🌃',
    promptModifier: 'cyberpunk style, neon lights, futuristic city, high-tech',
    negativePrompt: 'nature, vintage, old',
    imageStyle: {
      colorScheme: ['#00FFFF', '#FF00FF', '#FFFF00'],
      mood: 'intense',
      visualStyle: 'cyberpunk'
    },
    videoSettings: {
      transitionType: 'slide',
      duration: 2.5,
      fps: 60
    },
    audioSettings: {
      voiceStyle: 'dramatic',
      rate: 1.0,
      pitch: 0.9
    }
  },
  {
    id: 'fantasy',
    name: '판타지 세계',
    description: '마법과 환상의 세계',
    category: 'theme',
    icon: '🧙‍♂️',
    promptModifier: 'fantasy style, magical, mystical, enchanted forest',
    negativePrompt: 'modern, technology, urban',
    imageStyle: {
      colorScheme: ['#8A2BE2', '#9370DB', '#BA55D3'],
      mood: 'mystical',
      visualStyle: 'fantasy'
    },
    videoSettings: {
      transitionType: 'dissolve',
      duration: 4,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'mysterious',
      rate: 0.9,
      pitch: 1.0
    }
  },
  {
    id: 'minimalist',
    name: '미니멀리스트',
    description: '깔끔하고 단순한 스타일',
    category: 'mood',
    icon: '⬜',
    promptModifier: 'minimalist style, clean, simple, geometric shapes',
    negativePrompt: 'complex, detailed, ornate',
    imageStyle: {
      colorScheme: ['#FFFFFF', '#000000', '#808080'],
      mood: 'calm',
      visualStyle: 'minimalist'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 3,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'calm',
      rate: 0.95,
      pitch: 1.0
    }
  },
  {
    id: 'vintage',
    name: '빈티지',
    description: '레트로하고 클래식한 느낌',
    category: 'mood',
    icon: '📼',
    promptModifier: 'vintage style, retro, old film, classic',
    negativePrompt: 'modern, futuristic, high-tech',
    imageStyle: {
      colorScheme: ['#D2691E', '#CD853F', '#DEB887'],
      mood: 'nostalgic',
      visualStyle: 'vintage'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 3.5,
      fps: 24
    },
    audioSettings: {
      voiceStyle: 'warm',
      rate: 0.9,
      pitch: 0.95
    }
  },
  {
    id: 'nature',
    name: '자연 다큐',
    description: '자연의 아름다움',
    category: 'theme',
    icon: '🌿',
    promptModifier: 'nature documentary style, wildlife, natural beauty, serene',
    negativePrompt: 'urban, artificial, man-made',
    imageStyle: {
      colorScheme: ['#228B22', '#32CD32', '#90EE90'],
      mood: 'peaceful',
      visualStyle: 'documentary'
    },
    videoSettings: {
      transitionType: 'dissolve',
      duration: 4,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'documentary',
      rate: 0.95,
      pitch: 0.98
    }
  },
  {
    id: 'horror',
    name: '호러',
    description: '긴장감 넘치는 공포 스타일',
    category: 'mood',
    icon: '👻',
    promptModifier: 'horror style, dark, creepy, suspenseful, eerie atmosphere',
    negativePrompt: 'bright, cheerful, colorful',
    imageStyle: {
      colorScheme: ['#000000', '#8B0000', '#2F4F4F'],
      mood: 'tense',
      visualStyle: 'horror'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 2,
      fps: 24
    },
    audioSettings: {
      voiceStyle: 'whispering',
      rate: 0.85,
      pitch: 0.9
    }
  },
  {
    id: 'comedy',
    name: '코미디',
    description: '재미있고 유쾌한 분위기',
    category: 'mood',
    icon: '😂',
    promptModifier: 'comedy style, funny, humorous, lighthearted',
    negativePrompt: 'serious, dark, dramatic',
    imageStyle: {
      colorScheme: ['#FFD700', '#FFA500', '#FF6347'],
      mood: 'funny',
      visualStyle: 'cartoon'
    },
    videoSettings: {
      transitionType: 'zoom',
      duration: 2,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'energetic',
      rate: 1.2,
      pitch: 1.1
    }
  },
  {
    id: 'educational',
    name: '교육용',
    description: '명확하고 정보 전달형',
    category: 'platform',
    icon: '📚',
    promptModifier: 'educational style, clear, informative, diagram',
    negativePrompt: 'artistic, abstract, decorative',
    imageStyle: {
      colorScheme: ['#4169E1', '#1E90FF', '#87CEEB'],
      mood: 'professional',
      visualStyle: 'educational'
    },
    videoSettings: {
      transitionType: 'slide',
      duration: 4,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'clear',
      rate: 0.95,
      pitch: 1.0
    }
  },
  {
    id: 'motivational',
    name: '동기부여',
    description: '영감을 주는 스타일',
    category: 'mood',
    icon: '💪',
    promptModifier: 'motivational style, inspiring, powerful, uplifting',
    negativePrompt: 'depressing, dark, negative',
    imageStyle: {
      colorScheme: ['#FF4500', '#FF6347', '#FFD700'],
      mood: 'energetic',
      visualStyle: 'cinematic'
    },
    videoSettings: {
      transitionType: 'zoom',
      duration: 3,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'powerful',
      rate: 1.0,
      pitch: 1.05
    }
  }
]

export const PLATFORM_TEMPLATES = {
  youtube: {
    name: 'YouTube Shorts',
    aspectRatio: '9:16',
    resolution: { width: 1080, height: 1920 },
    maxDuration: 60,
    recommendedLength: 30,
    requirements: {
      titleLength: 100,
      descriptionLength: 5000,
      tags: 15
    }
  },
  tiktok: {
    name: 'TikTok',
    aspectRatio: '9:16',
    resolution: { width: 1080, height: 1920 },
    maxDuration: 60,
    recommendedLength: 15,
    requirements: {
      captionLength: 150,
      hashtags: 5
    }
  },
  instagram: {
    name: 'Instagram Reels',
    aspectRatio: '9:16',
    resolution: { width: 1080, height: 1920 },
    maxDuration: 90,
    recommendedLength: 30,
    requirements: {
      captionLength: 2200,
      hashtags: 30
    }
  },
  square: {
    name: 'Square (1:1)',
    aspectRatio: '1:1',
    resolution: { width: 1080, height: 1080 },
    maxDuration: 60,
    recommendedLength: 30,
    requirements: {}
  }
}

export const TRENDING_TEMPLATES = [
  {
    id: 'facts',
    name: '놀라운 사실',
    description: '흥미로운 사실 공유',
    structure: [
      { type: 'hook', duration: 3, text: '이것 알고 계셨나요?' },
      { type: 'main', duration: 20, text: '[주요 내용]' },
      { type: 'cta', duration: 7, text: '더 알고 싶다면 팔로우!' }
    ],
    visualStyle: 'bold text overlays',
    audioStyle: 'energetic'
  },
  {
    id: 'tutorial',
    name: '빠른 튜토리얼',
    description: '단계별 가이드',
    structure: [
      { type: 'intro', duration: 5, text: '오늘은 [주제]를 배워봅시다' },
      { type: 'steps', duration: 20, text: '1단계, 2단계, 3단계' },
      { type: 'result', duration: 5, text: '완성!' }
    ],
    visualStyle: 'step by step',
    audioStyle: 'instructional'
  },
  {
    id: 'story',
    name: '스토리텔링',
    description: '짧은 이야기',
    structure: [
      { type: 'setup', duration: 10, text: '옛날 옛적에...' },
      { type: 'conflict', duration: 10, text: '그런데 문제가 생겼어요' },
      { type: 'resolution', duration: 10, text: '결국...' }
    ],
    visualStyle: 'cinematic',
    audioStyle: 'narrative'
  }
]

export function applyPreset(
  basePrompt: string,
  preset: PresetStyle
): {
  enhancedPrompt: string
  negativePrompt: string
  settings: any
} {
  return {
    enhancedPrompt: `${basePrompt}, ${preset.promptModifier}`,
    negativePrompt: preset.negativePrompt,
    settings: {
      imageStyle: preset.imageStyle,
      videoSettings: preset.videoSettings,
      audioSettings: preset.audioSettings
    }
  }
}

export function getPresetsByCategory(category: PresetStyle['category']): PresetStyle[] {
  return CHARACTER_PRESETS.filter(p => p.category === category)
}

export function getPresetById(id: string): PresetStyle | undefined {
  return CHARACTER_PRESETS.find(p => p.id === id)
}

export default {
  CHARACTER_PRESETS,
  PLATFORM_TEMPLATES,
  TRENDING_TEMPLATES,
  applyPreset,
  getPresetsByCategory,
  getPresetById
}
