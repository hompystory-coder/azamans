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

// 🎨 확장된 프리셋 시스템 (20+ 스타일)
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
  },
  // 🆕 새로운 프리셋 10개 추가!
  {
    id: 'neon-art',
    name: '네온 아트',
    description: '화려한 네온 사인 스타일',
    category: 'theme',
    icon: '✨',
    promptModifier: 'neon art style, glowing neon lights, vibrant colors, electric glow, dark background',
    negativePrompt: 'natural, matte, dull, daylight',
    imageStyle: {
      colorScheme: ['#FF1493', '#00FFFF', '#FFFF00', '#00FF00'],
      mood: 'electric',
      visualStyle: 'neon'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 2.5,
      fps: 60
    },
    audioSettings: {
      voiceStyle: 'energetic',
      rate: 1.1,
      pitch: 1.1
    }
  },
  {
    id: 'watercolor',
    name: '수채화',
    description: '부드러운 수채화 스타일',
    category: 'mood',
    icon: '🎨',
    promptModifier: 'watercolor painting style, soft brushstrokes, flowing colors, artistic',
    negativePrompt: 'photorealistic, sharp, digital',
    imageStyle: {
      colorScheme: ['#E6F3FF', '#FFE6F0', '#FFF8E6'],
      mood: 'dreamy',
      visualStyle: 'watercolor'
    },
    videoSettings: {
      transitionType: 'dissolve',
      duration: 4,
      fps: 24
    },
    audioSettings: {
      voiceStyle: 'soft',
      rate: 0.9,
      pitch: 1.05
    }
  },
  {
    id: 'pixel-art',
    name: '픽셀 아트',
    description: '8비트 레트로 게임 스타일',
    category: 'theme',
    icon: '🎮',
    promptModifier: 'pixel art style, 8-bit, retro game, pixelated, sprite art',
    negativePrompt: 'high resolution, smooth, realistic, 3d',
    imageStyle: {
      colorScheme: ['#FF0000', '#00FF00', '#0000FF', '#FFFF00'],
      mood: 'nostalgic',
      visualStyle: 'pixel-art'
    },
    videoSettings: {
      transitionType: 'slide',
      duration: 2,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'retro',
      rate: 1.0,
      pitch: 1.0
    }
  },
  {
    id: 'studio-ghibli',
    name: '지브리 스튜디오',
    description: '지브리 애니메이션 스타일',
    category: 'character',
    icon: '🌳',
    promptModifier: 'Studio Ghibli style, beautiful scenery, peaceful, hand-drawn animation, miyazaki',
    negativePrompt: 'dark, horror, realistic, 3d',
    imageStyle: {
      colorScheme: ['#90EE90', '#87CEEB', '#FFE4B5'],
      mood: 'peaceful',
      visualStyle: 'ghibli'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 4,
      fps: 24
    },
    audioSettings: {
      voiceStyle: 'gentle',
      rate: 0.9,
      pitch: 1.05
    }
  },
  {
    id: 'cinematic',
    name: '시네마틱',
    description: '영화같은 고품질 영상',
    category: 'mood',
    icon: '🎬',
    promptModifier: 'cinematic style, film grain, dramatic lighting, anamorphic lens, color grading',
    negativePrompt: 'cartoon, anime, amateur, low quality',
    imageStyle: {
      colorScheme: ['#1C1C1C', '#DAA520', '#4682B4'],
      mood: 'dramatic',
      visualStyle: 'cinematic'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 3.5,
      fps: 24
    },
    audioSettings: {
      voiceStyle: 'dramatic',
      rate: 0.95,
      pitch: 0.95
    }
  },
  {
    id: 'vaporwave',
    name: '베이퍼웨이브',
    description: '80-90년대 레트로 퓨처',
    category: 'theme',
    icon: '🌴',
    promptModifier: 'vaporwave aesthetic, retro futuristic, pastel colors, palm trees, sunset, glitch art',
    negativePrompt: 'modern, realistic, dark, gritty',
    imageStyle: {
      colorScheme: ['#FF6AD5', '#C774E8', '#AD8CFF', '#8795E8'],
      mood: 'nostalgic',
      visualStyle: 'vaporwave'
    },
    videoSettings: {
      transitionType: 'slide',
      duration: 3,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'smooth',
      rate: 0.9,
      pitch: 0.9
    }
  },
  {
    id: 'comic-book',
    name: '코믹북',
    description: '만화책 스타일',
    category: 'character',
    icon: '💥',
    promptModifier: 'comic book style, bold outlines, halftone dots, speech bubbles, pop art',
    negativePrompt: 'realistic, photo, 3d, smooth',
    imageStyle: {
      colorScheme: ['#FF0000', '#FFFF00', '#0000FF', '#000000'],
      mood: 'energetic',
      visualStyle: 'comic'
    },
    videoSettings: {
      transitionType: 'slide',
      duration: 2.5,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'dramatic',
      rate: 1.1,
      pitch: 1.0
    }
  },
  {
    id: 'steampunk',
    name: '스팀펑크',
    description: '빅토리아 시대 + 증기 기술',
    category: 'theme',
    icon: '⚙️',
    promptModifier: 'steampunk style, victorian era, brass machinery, gears, steam powered, industrial',
    negativePrompt: 'modern, digital, clean, futuristic',
    imageStyle: {
      colorScheme: ['#8B4513', '#D4AF37', '#2F4F4F'],
      mood: 'industrial',
      visualStyle: 'steampunk'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 3,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'formal',
      rate: 0.95,
      pitch: 0.9
    }
  },
  {
    id: 'pastel-cute',
    name: '파스텔 큐트',
    description: '부드럽고 귀여운 파스텔톤',
    category: 'mood',
    icon: '🌸',
    promptModifier: 'pastel colors, cute, soft, kawaii, dreamy, fluffy, gentle',
    negativePrompt: 'dark, bold, realistic, gritty',
    imageStyle: {
      colorScheme: ['#FFB3BA', '#BAFFC9', '#BAE1FF', '#FFFFBA'],
      mood: 'cute',
      visualStyle: 'pastel'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 3,
      fps: 30
    },
    audioSettings: {
      voiceStyle: 'cute',
      rate: 1.05,
      pitch: 1.15
    }
  },
  {
    id: 'dark-fantasy',
    name: '다크 판타지',
    description: '어둡고 신비로운 판타지',
    category: 'theme',
    icon: '🦇',
    promptModifier: 'dark fantasy style, gothic, mystical, shadows, moonlight, mysterious',
    negativePrompt: 'bright, cheerful, colorful, modern',
    imageStyle: {
      colorScheme: ['#1A1A1A', '#4B0082', '#8B0000'],
      mood: 'dark',
      visualStyle: 'dark-fantasy'
    },
    videoSettings: {
      transitionType: 'fade',
      duration: 3.5,
      fps: 24
    },
    audioSettings: {
      voiceStyle: 'mysterious',
      rate: 0.9,
      pitch: 0.85
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

// PRESETS를 export 추가
export const PRESETS = CHARACTER_PRESETS;

export default {
  CHARACTER_PRESETS,
  PRESETS,
  PLATFORM_TEMPLATES,
  TRENDING_TEMPLATES,
  applyPreset,
  getPresetsByCategory,
  getPresetById
}
