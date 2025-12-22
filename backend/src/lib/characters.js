// Character definitions for backend
// 캐릭터 타입 정의

/**
 * 10가지 AI 캐릭터 프리셋
 */
export const characters = [
  {
    id: 'business-pro',
    name: 'Business Pro',
    nameKr: '비즈니스 프로',
    icon: '👔',
    description: '전문적이고 신뢰감 있는 비즈니스맨',
    voiceStyle: 'male-professional',
    tone: 'serious',
    suitableFor: ['비즈니스', '재테크', '경제'],
    imagePrompt: 'A professional male businessman in his 30s, wearing a navy blue suit and tie, standing in a modern office with glass windows, confident posture, natural lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A professional businessman in a suit, {action}, in a modern office setting, professional lighting, realistic style'
  },
  {
    id: 'news-anchor',
    name: 'News Anchor',
    nameKr: '여성 리포터',
    icon: '📺',
    description: '밝고 명랑한 뉴스 앵커',
    voiceStyle: 'female-professional',
    tone: 'friendly',
    suitableFor: ['뉴스', '트렌드', '라이프스타일'],
    imagePrompt: 'A friendly female news anchor in her late 20s, wearing a light blue blouse, sitting at a news desk with studio lighting, warm smile, professional makeup, realistic style, 4K quality',
    videoPromptTemplate: 'A female news anchor, {action}, at a news desk with studio background, professional lighting, realistic style'
  },
  {
    id: 'tech-guru',
    name: 'Tech Guru',
    nameKr: '테크 전문가',
    icon: '💻',
    description: '젊고 트렌디한 IT 전문가',
    voiceStyle: 'male-energetic',
    tone: 'enthusiastic',
    suitableFor: ['IT', '가젯', '기술'],
    imagePrompt: 'A young tech expert in his 20s, wearing a casual hoodie and glasses, in a modern tech workspace with computers, energetic expression, cool lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A young tech expert with glasses, {action}, in a tech workspace with computers and gadgets, modern lighting, realistic style'
  },
  {
    id: 'chef',
    name: 'Chef',
    nameKr: '요리 전문가',
    icon: '👨‍🍳',
    description: '친근한 셰프',
    voiceStyle: 'male-warm',
    tone: 'cheerful',
    suitableFor: ['요리', '레시피', '식당'],
    imagePrompt: 'A friendly chef in his 30s, wearing white chef uniform and hat, in a professional kitchen, warm smile, good lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A chef in white uniform, {action}, in a professional kitchen with cooking equipment, warm lighting, realistic style'
  },
  {
    id: 'fitness-coach',
    name: 'Fitness Coach',
    nameKr: '피트니스 트레이너',
    icon: '💪',
    description: '활기찬 운동 코치',
    voiceStyle: 'male-motivational',
    tone: 'energetic',
    suitableFor: ['운동', '건강', '다이어트'],
    imagePrompt: 'An energetic fitness trainer in his 20s, wearing sports outfit and headband, in a gym with equipment, motivational expression, bright lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A fitness trainer in sportswear, {action}, in a gym with workout equipment, dynamic lighting, realistic style'
  },
  {
    id: 'fashion-creator',
    name: 'Fashion Creator',
    nameKr: '패션 크리에이터',
    icon: '👗',
    description: '세련된 패션 인플루언서',
    voiceStyle: 'female-stylish',
    tone: 'trendy',
    suitableFor: ['패션', '뷰티', '쇼핑'],
    imagePrompt: 'A stylish fashion influencer in her 20s, wearing trendy outfit with accessories, in a modern studio with fashion items, confident pose, professional lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A stylish fashion influencer, {action}, in a modern studio with fashion backdrop, elegant lighting, realistic style'
  },
  {
    id: 'educator',
    name: 'Educator',
    nameKr: '교육 멘토',
    icon: '👨‍🏫',
    description: '지적이고 친근한 선생님',
    voiceStyle: 'male-clear',
    tone: 'educational',
    suitableFor: ['교육', '학습', '자기계발'],
    imagePrompt: 'A friendly educator in his 30s, wearing smart casual and glasses, in a bright classroom or study room, explaining gesture, natural lighting, realistic style, 4K quality',
    videoPromptTemplate: 'An educator with glasses, {action}, in a classroom or study environment, soft lighting, realistic style'
  },
  {
    id: 'travel-guide',
    name: 'Travel Guide',
    nameKr: '여행 가이드',
    icon: '🌍',
    description: '활발하고 모험심 넘치는 가이드',
    voiceStyle: 'female-excited',
    tone: 'adventurous',
    suitableFor: ['여행', '관광', '문화'],
    imagePrompt: 'An adventurous travel guide in her 20s, wearing casual travel outfit with backpack, in a scenic location, excited expression, natural outdoor lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A travel guide in casual outfit, {action}, with scenic background or landmark, natural lighting, realistic style'
  },
  {
    id: 'gamer',
    name: 'Gamer',
    nameKr: '게임 스트리머',
    icon: '🎮',
    description: '열정적인 게임 크리에이터',
    voiceStyle: 'male-excited',
    tone: 'entertaining',
    suitableFor: ['게임', 'e스포츠', '리뷰'],
    imagePrompt: 'An enthusiastic gamer in his 20s, wearing gaming headset and casual clothes, in a gaming setup with RGB lights, excited expression, colorful lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A gamer with headset, {action}, in a gaming room with RGB lights and monitors, dynamic lighting, realistic style'
  },
  {
    id: 'business-woman',
    name: 'Business Woman',
    nameKr: '비즈니스 우먼',
    icon: '💼',
    description: '카리스마 있는 여성 CEO',
    voiceStyle: 'female-confident',
    tone: 'powerful',
    suitableFor: ['창업', '리더십', '비즈니스'],
    imagePrompt: 'A confident female CEO in her 30s, wearing professional suit, in an executive office with city view, powerful presence, professional lighting, realistic style, 4K quality',
    videoPromptTemplate: 'A professional businesswoman in suit, {action}, in an executive office with city skyline view, sophisticated lighting, realistic style'
  }
];

/**
 * 캐릭터 ID로 캐릭터 찾기
 */
export function getCharacterById(id) {
  return characters.find(char => char.id === id);
}

/**
 * 콘텐츠 타입에 맞는 캐릭터 추천
 */
export function getRecommendedCharacters(contentType) {
  return characters.filter(char => 
    char.suitableFor.some(category => 
      category.toLowerCase().includes(contentType.toLowerCase())
    )
  );
}

/**
 * 비디오 프롬프트 생성
 */
export function generateVideoPrompt(character, narration, action) {
  const defaultAction = action || 'explaining and gesturing naturally';
  return character.videoPromptTemplate
    .replace('{action}', defaultAction) + 
    `. The character is discussing: "${narration}". Natural movements, 3 seconds, high quality.`;
}
