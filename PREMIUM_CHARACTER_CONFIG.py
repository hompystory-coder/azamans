#!/usr/bin/env python3
"""
프리미엄 AI 캐릭터 설정 (30개)
- 고급스럽고 귀여운 디자인
- Pixar-quality 3D 렌더링
- 세련된 모션과 애니메이션
- 다양한 전문 분야
"""

# 프리미엄 캐릭터 설정 (30개)
PREMIUM_CHARACTER_CONFIG = {
    # ========== 럭셔리 비즈니스 (5개) ==========
    'executive-fox': {
        'name': '🦊 이그제큐티브 폭스',
        'description': 'Premium 3D rendered sophisticated fox in elegant business suit, gold-rimmed glasses, confident posture, studio lighting, Pixar-quality animation, 8K ultra detailed, professional business environment with luxury office background',
        'personality': '프리미엄 비즈니스 여우가 제품의 핵심 가치와 투자 가치를 전문적으로 분석합니다',
        'style': 'luxury-business',
        'color_palette': '#D4AF37 #000000 #FFFFFF',
        'motion_quality': 'sophisticated professional gestures with confident eye contact'
    },
    'elegant-cat': {
        'name': '😺 엘레강트 캣',
        'description': 'Premium 3D rendered elegant Persian cat with silky white fur, wearing pearl necklace, refined movements, cinematic lighting, Pixar-style animation, 8K resolution, luxury boutique background with soft bokeh',
        'personality': '우아하고 세련된 고양이가 프리미엄 제품을 럭셔리하게 소개합니다',
        'style': 'haute-couture',
        'color_palette': '#FFFFFF #FFD700 #E5E5E5',
        'motion_quality': 'graceful fluid movements with aristocratic poise'
    },
    'premium-deer': {
        'name': '🦌 프리미엄 디어',
        'description': 'Premium 3D rendered majestic deer with golden antlers, sophisticated expression, wearing designer scarf, studio quality lighting, Pixar-level details, 8K ultra HD, luxury natural background with autumn leaves',
        'personality': '고귀하고 우아한 사슴이 프리미엄 라이프스타일 제품을 소개합니다',
        'style': 'luxury-nature',
        'color_palette': '#8B7355 #D4AF37 #2C3E1F',
        'motion_quality': 'noble movements with gentle head tilts and refined gestures'
    },
    'ceo-lion': {
        'name': '🦁 CEO 라이온',
        'description': 'Premium 3D rendered distinguished lion with magnificent golden mane, wearing luxury suit and tie, executive presence, cinematic studio lighting, Pixar-quality animation, 8K ultra detailed, prestigious office with city view',
        'personality': '카리스마 넘치는 사자가 프리미엄 비즈니스 제품을 리더십있게 소개합니다',
        'style': 'executive-power',
        'color_palette': '#C19A6B #1C1C1C #FFD700',
        'motion_quality': 'powerful confident movements with commanding presence'
    },
    'luxury-swan': {
        'name': '🦢 럭셔리 스완',
        'description': 'Premium 3D rendered graceful white swan with diamond tiara, elegant long neck, sophisticated movements, soft cinematic lighting, Pixar-style rendering, 8K ultra HD, luxury crystal lake background with reflections',
        'personality': '우아하고 고귀한 백조가 럭셔리 제품을 품격있게 소개합니다',
        'style': 'royal-elegance',
        'color_palette': '#FFFFFF #B9F2FF #C0C0C0',
        'motion_quality': 'ballet-like graceful movements with serene elegance'
    },

    # ========== 테크 & 이노베이션 (5개) ==========
    'tech-raccoon': {
        'name': '🦝 테크 라쿤',
        'description': 'Premium 3D rendered tech-savvy raccoon wearing AR smart glasses, modern tech hoodie, holding holographic tablet, futuristic lighting, Pixar-quality animation, 8K ultra detailed, high-tech laboratory with glowing screens',
        'personality': '최첨단 기술에 능통한 너구리가 제품을 전문적으로 리뷰합니다',
        'style': 'tech-minimalist',
        'color_palette': '#00D9FF #1E1E1E #FFFFFF',
        'motion_quality': 'precise tech gestures with innovative hand movements'
    },
    'cyber-owl': {
        'name': '🦉 사이버 아울',
        'description': 'Premium 3D rendered wise owl with LED-lit feathers, wearing VR headset, high-tech gear, neon lighting, Pixar-style rendering, 8K resolution, cyber background with digital data streams',
        'personality': '미래지향적 부엉이가 혁신적인 기술 제품을 분석합니다',
        'style': 'cyberpunk',
        'color_palette': '#00FF41 #0A0A0A #FF006E',
        'motion_quality': 'intelligent analytical movements with tech-precision'
    },
    'ai-penguin': {
        'name': '🐧 AI 펭귄',
        'description': 'Premium 3D rendered artificial intelligence penguin with holographic display, wearing sleek tech vest, digital patterns, futuristic lighting, Pixar-quality animation, 8K ultra HD, AI lab with floating interfaces',
        'personality': '인공지능 펭귄이 스마트 제품을 데이터 기반으로 설명합니다',
        'style': 'ai-future',
        'color_palette': '#4169E1 #00CED1 #F0F8FF',
        'motion_quality': 'computational precise movements with digital transitions'
    },
    'robot-dog': {
        'name': '🤖 로봇 도그',
        'description': 'Premium 3D rendered robotic golden retriever with chrome finish, LED eyes, mechanical parts visible, high-tech collar, cinematic lighting, Pixar-style rendering, 8K ultra detailed, futuristic workshop background',
        'personality': '친근한 로봇 강아지가 스마트 기기를 재미있게 소개합니다',
        'style': 'robot-friendly',
        'color_palette': '#C0C0C0 #4169E1 #FFD700',
        'motion_quality': 'robotic yet friendly movements with mechanical precision'
    },
    'quantum-rabbit': {
        'name': '🐰 퀀텀 래빗',
        'description': 'Premium 3D rendered quantum physics rabbit with glowing particles, wearing lab coat, surrounded by energy fields, mystical lighting, Pixar-quality animation, 8K resolution, quantum lab with floating equations',
        'personality': '과학적인 토끼가 혁신 제품을 논리적으로 설명합니다',
        'style': 'quantum-science',
        'color_palette': '#9400D3 #00FFFF #FFFFFF',
        'motion_quality': 'energetic scientific movements with quantum leaps'
    },

    # ========== 패션 & 아트 (5개) ==========
    'fashion-panda': {
        'name': '🐼 패션 판다',
        'description': 'Premium 3D rendered fashionista panda wearing Gucci sunglasses, designer outfit, runway pose, studio fashion lighting, Pixar-style animation, 8K ultra detailed, luxury fashion boutique with marble floors',
        'personality': '트렌디한 판다가 패션 제품을 스타일리시하게 소개합니다',
        'style': 'high-fashion',
        'color_palette': '#000000 #FFFFFF #FF1493',
        'motion_quality': 'runway model movements with fashionable poses'
    },
    'artist-monkey': {
        'name': '🐵 아티스트 몽키',
        'description': 'Premium 3D rendered creative monkey wearing artistic beret, holding paintbrush, colorful palette, artistic studio lighting, Pixar-quality rendering, 8K ultra HD, art gallery with masterpieces background',
        'personality': '창의적인 원숭이가 디자인 제품을 예술적으로 표현합니다',
        'style': 'artistic-creative',
        'color_palette': '#FF6B6B #4ECDC4 #FFE66D',
        'motion_quality': 'expressive artistic gestures with creative flair'
    },
    'couture-peacock': {
        'name': '🦚 꾸뛰르 피콕',
        'description': 'Premium 3D rendered magnificent peacock with iridescent feathers, wearing designer accessories, glamorous pose, luxury lighting, Pixar-style animation, 8K ultra detailed, haute couture fashion show background',
        'personality': '화려한 공작새가 럭셔리 패션 아이템을 화려하게 소개합니다',
        'style': 'glamour-luxury',
        'color_palette': '#00CED1 #FFD700 #9400D3',
        'motion_quality': 'dramatic glamorous movements with feather displays'
    },
    'designer-fox': {
        'name': '🦊 디자이너 폭스',
        'description': 'Premium 3D rendered fashion designer fox in avant-garde outfit, measuring tape around neck, sophisticated expression, studio lighting, Pixar-quality animation, 8K resolution, design studio with fashion sketches',
        'personality': '패션 디자이너 여우가 스타일리시한 제품을 전문적으로 평가합니다',
        'style': 'fashion-expert',
        'color_palette': '#FF6347 #2F4F4F #F5F5DC',
        'motion_quality': 'precise designer movements with aesthetic gestures'
    },
    'gallery-koala': {
        'name': '🐨 갤러리 코알라',
        'description': 'Premium 3D rendered sophisticated koala with art curator glasses, holding exhibition catalog, refined demeanor, museum lighting, Pixar-style rendering, 8K ultra HD, art gallery with sculptures',
        'personality': '품격있는 코알라가 프리미엄 인테리어 제품을 큐레이팅합니다',
        'style': 'art-curator',
        'color_palette': '#8B8B8B #FFFFFF #D4AF37',
        'motion_quality': 'cultured refined movements with appreciative gestures'
    },

    # ========== 스포츠 & 액티브 (5개) ==========
    'athletic-cheetah': {
        'name': '🐆 애슬레틱 치타',
        'description': 'Premium 3D rendered sporty cheetah in athletic wear, dynamic pose, muscular build, action lighting, Pixar-quality animation, 8K ultra detailed, professional sports stadium background',
        'personality': '역동적인 치타가 스포츠 제품을 파워풀하게 소개합니다',
        'style': 'sports-dynamic',
        'color_palette': '#FFD700 #000000 #FF4500',
        'motion_quality': 'explosive athletic movements with speed and agility'
    },
    'yoga-elephant': {
        'name': '🐘 요가 엘리펀트',
        'description': 'Premium 3D rendered zen elephant in yoga pose, wearing meditation beads, peaceful expression, soft natural lighting, Pixar-style rendering, 8K resolution, serene wellness spa background',
        'personality': '평화로운 코끼리가 웰니스 제품을 힐링감있게 소개합니다',
        'style': 'wellness-zen',
        'color_palette': '#E6E6FA #98FB98 #F0E68C',
        'motion_quality': 'calm meditative movements with flowing grace'
    },
    'champion-tiger': {
        'name': '🐯 챔피언 타이거',
        'description': 'Premium 3D rendered champion tiger with gold medal, athletic gear, winner pose, victory lighting, Pixar-quality animation, 8K ultra HD, olympic podium background',
        'personality': '챔피언 호랑이가 프리미엄 스포츠 장비를 전문가답게 리뷰합니다',
        'style': 'champion-excellence',
        'color_palette': '#FF8C00 #000000 #FFD700',
        'motion_quality': 'powerful victorious movements with champion confidence'
    },
    'adventure-bear': {
        'name': '🐻 어드벤처 베어',
        'description': 'Premium 3D rendered explorer bear with hiking gear, compass, adventure hat, outdoor lighting, Pixar-style rendering, 8K ultra detailed, mountain expedition background',
        'personality': '모험가 곰이 아웃도어 제품을 경험담과 함께 소개합니다',
        'style': 'outdoor-adventure',
        'color_palette': '#8B4513 #228B22 #F4A460',
        'motion_quality': 'adventurous energetic movements with explorer spirit'
    },
    'surf-dolphin': {
        'name': '🐬 서프 돌핀',
        'description': 'Premium 3D rendered cool dolphin wearing beach sunglasses, surfboard, ocean vibes, tropical lighting, Pixar-quality animation, 8K resolution, beach paradise background',
        'personality': '쿨한 돌고래가 해양 스포츠 제품을 신나게 소개합니다',
        'style': 'beach-cool',
        'color_palette': '#00CED1 #FFD700 #FF6347',
        'motion_quality': 'fluid wave-like movements with ocean energy'
    },

    # ========== 푸드 & 라이프스타일 (5개) ==========
    'chef-pig': {
        'name': '🐷 셰프 피그',
        'description': 'Premium 3D rendered gourmet chef pig in white chef hat, professional kitchen attire, holding cooking utensils, warm kitchen lighting, Pixar-style animation, 8K ultra HD, michelin restaurant kitchen',
        'personality': '미슐랭 셰프 돼지가 요리 제품을 전문가답게 평가합니다',
        'style': 'gourmet-expert',
        'color_palette': '#FFB6C1 #FFFFFF #8B4513',
        'motion_quality': 'professional chef movements with culinary precision'
    },
    'barista-squirrel': {
        'name': '🐿️ 바리스타 스퀴럴',
        'description': 'Premium 3D rendered hipster squirrel with apron, latte art skills, coffee beans, cafe lighting, Pixar-quality rendering, 8K ultra detailed, artisan coffee shop background',
        'personality': '감각적인 다람쥐가 카페 제품을 바리스타 관점에서 소개합니다',
        'style': 'cafe-artisan',
        'color_palette': '#8B4513 #F5DEB3 #4E342E',
        'motion_quality': 'skilled barista movements with artistic precision'
    },
    'sommelier-wolf': {
        'name': '🐺 소믈리에 울프',
        'description': 'Premium 3D rendered sophisticated wolf in suit vest, holding wine glass, wine cellar ambiance, elegant lighting, Pixar-style animation, 8K resolution, luxury wine cellar background',
        'personality': '고급스러운 늑대가 프리미엄 음료를 전문가답게 테이스팅합니다',
        'style': 'wine-sophistication',
        'color_palette': '#800020 #2C1810 #FFD700',
        'motion_quality': 'refined sommelier movements with elegant wine gestures'
    },
    'baker-hamster': {
        'name': '🐹 베이커 햄스터',
        'description': 'Premium 3D rendered adorable hamster in baker uniform, flour on cheeks, holding pastry, warm bakery lighting, Pixar-quality rendering, 8K ultra HD, cozy bakery shop background',
        'personality': '귀여운 햄스터가 베이킹 제품을 달콤하게 소개합니다',
        'style': 'sweet-bakery',
        'color_palette': '#FFE4B5 #FF6B9D #8B4513',
        'motion_quality': 'cute energetic movements with baker enthusiasm'
    },
    'tea-master-crane': {
        'name': '🦩 티마스터 크레인',
        'description': 'Premium 3D rendered elegant crane in traditional tea ceremony attire, holding tea cup, zen atmosphere, soft natural lighting, Pixar-style animation, 8K ultra detailed, japanese tea house background',
        'personality': '우아한 학이 차 관련 제품을 동양적 감성으로 소개합니다',
        'style': 'zen-traditional',
        'color_palette': '#FFFFFF #008080 #FFE4B5',
        'motion_quality': 'graceful ceremonial movements with zen precision'
    },

    # ========== 엔터테인먼트 & 유머 (5개) ==========
    'comedian-parrot': {
        'name': '🦜 코미디언 패럿',
        'description': 'Premium 3D rendered colorful parrot with microphone, funny expression, stage performer, spotlight lighting, Pixar-quality animation, 8K ultra HD, comedy club stage background',
        'personality': '재미있는 앵무새가 제품을 유머러스하게 소개합니다',
        'style': 'entertainment-fun',
        'color_palette': '#FF0000 #00FF00 #0000FF',
        'motion_quality': 'comedic exaggerated movements with entertaining gestures'
    },
    'dj-hedgehog': {
        'name': '🦔 DJ 헤지호그',
        'description': 'Premium 3D rendered cool hedgehog with headphones, DJ turntables, party vibes, neon club lighting, Pixar-style rendering, 8K resolution, nightclub with laser lights',
        'personality': '신나는 고슴도치가 오디오 제품을 음악적 감각으로 소개합니다',
        'style': 'music-party',
        'color_palette': '#FF00FF #00FFFF #FFD700',
        'motion_quality': 'rhythmic DJ movements with beat-matching energy'
    },
    'actor-raccoon': {
        'name': '🦝 액터 라쿤',
        'description': 'Premium 3D rendered dramatic raccoon in theater costume, expressing emotions, stage lighting, Pixar-quality animation, 8K ultra detailed, broadway theater stage background',
        'personality': '연기파 너구리가 제품을 드라마틱하게 표현합니다',
        'style': 'theatrical-drama',
        'color_palette': '#8B0000 #FFD700 #000000',
        'motion_quality': 'theatrical dramatic movements with expressive acting'
    },
    'magician-fox': {
        'name': '🦊 매지션 폭스',
        'description': 'Premium 3D rendered mysterious fox in magician outfit, top hat, magic wand, mystical lighting, Pixar-style rendering, 8K ultra HD, magic show stage with sparkles',
        'personality': '신비로운 여우가 제품을 마술처럼 놀랍게 소개합니다',
        'style': 'magic-mystery',
        'color_palette': '#4B0082 #FF1493 #FFD700',
        'motion_quality': 'mysterious magical movements with illusion gestures'
    },
    'gamer-otter': {
        'name': '🦦 게이머 오터',
        'description': 'Premium 3D rendered gaming otter with RGB gaming gear, controller, energy drink, colorful gaming lighting, Pixar-quality animation, 8K resolution, esports gaming room background',
        'personality': '프로게이머 수달이 게이밍 제품을 전문적으로 리뷰합니다',
        'style': 'gaming-pro',
        'color_palette': '#00FF00 #FF00FF #00FFFF',
        'motion_quality': 'fast gaming movements with competitive precision'
    },
}

# 시각적 스타일 시스템
VISUAL_STYLES = {
    'luxury-business': {
        'lighting': 'professional studio lighting with soft rim lights',
        'camera': 'professional business shot, medium close-up, eye level',
        'environment': 'luxury office with city skyline view',
        'mood': 'confident, professional, trustworthy'
    },
    'haute-couture': {
        'lighting': 'soft fashion lighting with beauty dish, rim lights',
        'camera': 'fashion editorial shot, 3/4 angle, slightly low angle',
        'environment': 'luxury boutique with marble and gold accents',
        'mood': 'elegant, sophisticated, exclusive'
    },
    'tech-minimalist': {
        'lighting': 'cool LED lighting with blue accents, clean shadows',
        'camera': 'modern tech shot, dynamic angle, futuristic feel',
        'environment': 'high-tech laboratory with glowing interfaces',
        'mood': 'innovative, cutting-edge, intelligent'
    },
    'high-fashion': {
        'lighting': 'runway lighting with dramatic spotlights',
        'camera': 'fashion show angle, full body to 3/4 shot',
        'environment': 'fashion runway with audience lights',
        'mood': 'stylish, trendy, confident'
    },
    'sports-dynamic': {
        'lighting': 'action sports lighting with motion blur effect',
        'camera': 'dynamic action shot, low angle for power',
        'environment': 'professional sports venue with crowd',
        'mood': 'energetic, powerful, competitive'
    },
    'wellness-zen': {
        'lighting': 'soft natural lighting with warm golden hour',
        'camera': 'peaceful centered shot, eye level, calm',
        'environment': 'zen garden or spa with nature elements',
        'mood': 'peaceful, calming, mindful'
    },
    'gourmet-expert': {
        'lighting': 'warm kitchen lighting with food photography style',
        'camera': 'professional culinary shot, slightly elevated',
        'environment': 'michelin-star restaurant kitchen',
        'mood': 'professional, appetizing, refined'
    },
    'entertainment-fun': {
        'lighting': 'colorful stage lighting with spotlights',
        'camera': 'performance shot, dynamic angles',
        'environment': 'entertainment stage with audience',
        'mood': 'fun, entertaining, energetic'
    }
}

# 모션 품질 레벨
MOTION_QUALITY_PROMPTS = {
    'ultra_premium': 'fluid 3D animation with Pixar-level quality, natural weight and physics, subtle secondary motion, expressive facial animation, cinematic timing, 60fps smooth movements',
    'premium': 'high-quality 3D animation with natural movements, good weight distribution, facial expressions, smooth transitions',
    'standard': 'quality 3D animation with clear movements and expressions'
}

# 캐릭터 카테고리
CHARACTER_CATEGORIES = {
    'business': ['executive-fox', 'elegant-cat', 'premium-deer', 'ceo-lion', 'luxury-swan'],
    'tech': ['tech-raccoon', 'cyber-owl', 'ai-penguin', 'robot-dog', 'quantum-rabbit'],
    'fashion': ['fashion-panda', 'artist-monkey', 'couture-peacock', 'designer-fox', 'gallery-koala'],
    'sports': ['athletic-cheetah', 'yoga-elephant', 'champion-tiger', 'adventure-bear', 'surf-dolphin'],
    'food': ['chef-pig', 'barista-squirrel', 'sommelier-wolf', 'baker-hamster', 'tea-master-crane'],
    'entertainment': ['comedian-parrot', 'dj-hedgehog', 'actor-raccoon', 'magician-fox', 'gamer-otter']
}

def get_character_prompt(character_id: str, product_context: str = "") -> str:
    """
    캐릭터 ID와 제품 컨텍스트를 받아 완전한 프리미엄 비디오 프롬프트 생성
    """
    if character_id not in PREMIUM_CHARACTER_CONFIG:
        return f"Premium 3D animated character presenting {product_context}"
    
    char = PREMIUM_CHARACTER_CONFIG[character_id]
    style_key = char.get('style', 'luxury-business')
    style = VISUAL_STYLES.get(style_key, VISUAL_STYLES['luxury-business'])
    
    # 완전한 프리미엄 프롬프트 조합
    full_prompt = f"""
{char['description']}

Character is {char['motion_quality']}.

Product Context: {product_context}

Technical Specs:
- {style['lighting']}
- {style['camera']}
- {style['environment']}
- Mood: {style['mood']}
- Animation: {MOTION_QUALITY_PROMPTS['ultra_premium']}
- Resolution: 8K, ultra detailed, Pixar-quality 3D rendering
- Vertical format: 9:16 ratio for mobile shorts
- Color palette: {char['color_palette']}

The character should naturally interact with the product, showing enthusiasm and expertise in their specialty area.
""".strip()
    
    return full_prompt

# 사용 예시
if __name__ == "__main__":
    print("=" * 80)
    print("프리미엄 AI 캐릭터 설정 (30개)")
    print("=" * 80)
    
    for category, character_ids in CHARACTER_CATEGORIES.items():
        print(f"\n📁 {category.upper()} 카테고리 ({len(character_ids)}개)")
        print("-" * 80)
        for char_id in character_ids:
            char = PREMIUM_CHARACTER_CONFIG[char_id]
            print(f"  {char['name']}")
            print(f"    ID: {char_id}")
            print(f"    스타일: {char['style']}")
            print(f"    컬러: {char['color_palette']}")
            print()
    
    print("\n" + "=" * 80)
    print(f"✅ 총 {len(PREMIUM_CHARACTER_CONFIG)}개 프리미엄 캐릭터 설정 완료!")
    print("=" * 80)
    
    # 샘플 프롬프트 생성
    print("\n📝 샘플 프롬프트 (executive-fox):")
    print("-" * 80)
    sample = get_character_prompt('executive-fox', 'premium wireless earbuds with ANC')
    print(sample)
