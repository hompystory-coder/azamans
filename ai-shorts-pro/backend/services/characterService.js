// 10종 캐릭터 프리셋 정의
const characters = [
  {
    id: 'friendly_neighbor',
    name: '친근한 이웃 언니/오빠',
    description: '친구처럼 편안하고 다정한 톤',
    voice: 'female_gentle',
    scriptStyle: {
      greeting: '안녕하세요! 오늘도 좋은 정보 들고 왔어요~',
      tone: 'casual',
      emojis: true,
      callToAction: '궁금한 점 있으면 댓글 남겨주세요!'
    },
    visualStyle: 'warm and friendly',
    color: '#FF6B9D'
  },
  {
    id: 'professional_reviewer',
    name: '전문 리뷰어',
    description: '객관적이고 신뢰감 있는 전문가 톤',
    voice: 'female_energetic',
    scriptStyle: {
      greeting: '안녕하세요, 전문 리뷰어입니다.',
      tone: 'professional',
      emojis: false,
      callToAction: '더 자세한 리뷰는 링크를 확인해주세요.'
    },
    visualStyle: 'professional and trustworthy',
    color: '#4A90E2'
  },
  {
    id: 'cute_character',
    name: '귀여운 캐릭터',
    description: '발랄하고 귀여운 톤',
    voice: 'child_cute',
    scriptStyle: {
      greeting: '안녕! 오늘 소개할 제품 정말 귀여워!',
      tone: 'cute',
      emojis: true,
      callToAction: '좋아요 꾹! 구독도 부탁해!'
    },
    visualStyle: 'cute and colorful',
    color: '#FFB6C1'
  },
  {
    id: 'stylish_influencer',
    name: '세련된 인플루언서',
    description: '트렌디하고 감각적인 톤',
    voice: 'female_energetic',
    scriptStyle: {
      greeting: '하이! 오늘의 픽은 이거예요',
      tone: 'trendy',
      emojis: true,
      callToAction: '링크는 프로필에서 확인하세요✨'
    },
    visualStyle: 'modern and stylish',
    color: '#C4A5E8'
  },
  {
    id: 'trusted_expert',
    name: '신뢰감 있는 전문가',
    description: '차분하고 전문적인 톤',
    voice: 'male_calm',
    scriptStyle: {
      greeting: '안녕하세요, 제품 분석 전문가입니다.',
      tone: 'expert',
      emojis: false,
      callToAction: '더 많은 분석은 채널을 구독해주세요.'
    },
    visualStyle: 'calm and professional',
    color: '#5D8AA8'
  },
  {
    id: 'energetic_mc',
    name: '활발한 MC',
    description: '에너지 넘치고 역동적인 톤',
    voice: 'male_powerful',
    scriptStyle: {
      greeting: '여러분! 오늘 대박 제품 준비했습니다!',
      tone: 'energetic',
      emojis: true,
      callToAction: '놓치면 후회합니다! 지금 확인하세요!'
    },
    visualStyle: 'dynamic and energetic',
    color: '#FF4500'
  },
  {
    id: 'calm_narrator',
    name: '차분한 해설자',
    description: '조용하고 차분한 설명 톤',
    voice: 'male_calm',
    scriptStyle: {
      greeting: '오늘 소개할 제품을 살펴보겠습니다.',
      tone: 'calm',
      emojis: false,
      callToAction: '자세한 내용은 링크를 참고하세요.'
    },
    visualStyle: 'calm and soothing',
    color: '#708090'
  },
  {
    id: 'humorous_comedian',
    name: '유머러스한 개그맨',
    description: '재미있고 웃긴 톤',
    voice: 'male_powerful',
    scriptStyle: {
      greeting: '여러분! 오늘 제가 뭘 발견했냐면요!',
      tone: 'humorous',
      emojis: true,
      callToAction: '웃겼으면 좋아요 꾹! 😂'
    },
    visualStyle: 'fun and entertaining',
    color: '#FFD700'
  },
  {
    id: 'emotional_storyteller',
    name: '감성적인 스토리텔러',
    description: '따뜻하고 감성적인 톤',
    voice: 'female_gentle',
    scriptStyle: {
      greeting: '오늘은 특별한 이야기를 들려드릴게요.',
      tone: 'emotional',
      emojis: true,
      callToAction: '공감하셨다면 하트 부탁드려요 ❤️'
    },
    visualStyle: 'warm and emotional',
    color: '#FF69B4'
  },
  {
    id: 'powerful_salesman',
    name: '파워풀한 세일즈맨',
    description: '설득력 있고 강력한 톤',
    voice: 'male_powerful',
    scriptStyle: {
      greeting: '주목! 이 제품 안 사면 손해입니다!',
      tone: 'powerful',
      emojis: true,
      callToAction: '지금 바로 링크 클릭! 🔥'
    },
    visualStyle: 'bold and persuasive',
    color: '#DC143C'
  }
];

// 비디오 모드 정의
const videoModes = [
  {
    id: 'character_only',
    name: '캐릭터만',
    description: 'AI 생성 캐릭터 영상만 사용',
    useCharacter: true,
    useRealImages: false
  },
  {
    id: 'character_plus_images',
    name: '캐릭터 + 실사 이미지',
    description: 'AI 캐릭터와 크롤링한 실사 이미지 혼합',
    useCharacter: true,
    useRealImages: true
  },
  {
    id: 'images_only',
    name: '실사 이미지만',
    description: '크롤링한 실사 이미지만 사용',
    useCharacter: false,
    useRealImages: true
  }
];

/**
 * 캐릭터별 스크립트 생성
 */
function generateCharacterScript(character, content, scenes) {
  const style = character.scriptStyle;
  
  // 캐릭터 스타일에 맞게 스크립트 조정
  const adjustedScenes = scenes.map((scene, index) => {
    let script = scene.script;

    // 인트로에 캐릭터 인사말 추가
    if (index === 0) {
      script = `${style.greeting} ${script}`;
    }

    // 마지막 장면에 CTA 추가
    if (index === scenes.length - 1) {
      script = `${script} ${style.callToAction}`;
    }

    // 이모지 추가/제거
    if (style.emojis) {
      script = addEmojis(script, style.tone);
    }

    return {
      ...scene,
      script,
      characterId: character.id,
      voiceType: character.voice,
      visualStyle: character.visualStyle
    };
  });

  return adjustedScenes;
}

/**
 * 톤에 맞는 이모지 추가
 */
function addEmojis(text, tone) {
  const emojiMap = {
    casual: ['😊', '👍', '💕', '✨'],
    cute: ['🥰', '💖', '🌟', '🎀'],
    trendy: ['✨', '💫', '🔥', '👀'],
    energetic: ['🔥', '💥', '⚡', '🎯'],
    humorous: ['😂', '🤣', '😎', '👏'],
    emotional: ['❤️', '💝', '🌸', '🌈']
  };

  const emojis = emojiMap[tone] || ['😊'];
  const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];

  return text + ' ' + randomEmoji;
}

/**
 * 캐릭터 정보 가져오기
 */
function getCharacter(characterId) {
  return characters.find(c => c.id === characterId) || characters[0];
}

/**
 * 비디오 모드 정보 가져오기
 */
function getVideoMode(modeId) {
  return videoModes.find(m => m.id === modeId) || videoModes[1];
}

module.exports = {
  characters,
  videoModes,
  generateCharacterScript,
  getCharacter,
  getVideoMode
};
