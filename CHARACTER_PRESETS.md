# 🎭 AI 캐릭터 프리셋 정의

## 캐릭터 목록 (10가지)

### 1. 비즈니스 프로 (Business Pro) 👔
**특징**: 전문적이고 신뢰감 있는 남성 비즈니스맨
- **음성**: 차분하고 전문적인 남성 목소리
- **스타일**: 정장 차림, 깔끔한 헤어스타일
- **톤**: 진지하고 정확한 정보 전달
- **적합한 콘텐츠**: 비즈니스, 재테크, 경제 뉴스

### 2. 여성 리포터 (News Anchor) 📺
**특징**: 밝고 명랑한 여성 뉴스 앵커
- **음성**: 명확하고 경쾌한 여성 목소리
- **스타일**: 단정한 블라우스, 깔끔한 메이크업
- **톤**: 친근하면서도 전문적
- **적합한 콘텐츠**: 뉴스, 트렌드, 라이프스타일

### 3. 테크 전문가 (Tech Guru) 💻
**특징**: 젊고 트렌디한 IT 전문가
- **음성**: 에너지 넘치는 젊은 남성 목소리
- **스타일**: 캐주얼한 후디, 안경
- **톤**: 열정적이고 친근한
- **적합한 콘텐츠**: IT, 가젯, 기술 리뷰

### 4. 요리 전문가 (Chef) 👨‍🍳
**특징**: 친근한 셰프
- **음성**: 따뜻하고 친근한 목소리
- **스타일**: 셰프복, 요리 모자
- **톤**: 즐겁고 맛있게 설명
- **적합한 콘텐츠**: 레시피, 요리 팁, 식당 리뷰

### 5. 피트니스 트레이너 (Fitness Coach) 💪
**특징**: 활기찬 운동 코치
- **음성**: 에너지 넘치는 활발한 목소리
- **스타일**: 운동복, 헤드밴드
- **톤**: 동기부여 가득한
- **적합한 콘텐츠**: 운동, 건강, 다이어트

### 6. 패션 크리에이터 (Fashion Creator) 👗
**특징**: 세련된 패션 인플루언서
- **음성**: 세련되고 감각적인 목소리
- **스타일**: 트렌디한 의상, 액세서리
- **톤**: 스타일리시하고 트렌디한
- **적합한 콘텐츠**: 패션, 뷰티, 쇼핑

### 7. 교육 멘토 (Educator) 👨‍🏫
**특징**: 지적이고 친근한 선생님
- **음성**: 명확하고 이해하기 쉬운 목소리
- **스타일**: 스마트 캐주얼, 안경
- **톤**: 설명이 쉽고 친절한
- **적합한 콘텐츠**: 교육, 학습, 자기계발

### 8. 여행 가이드 (Travel Guide) 🌍
**특징**: 활발하고 모험심 넘치는 여행 가이드
- **음성**: 신나고 활기찬 목소리
- **스타일**: 캐주얼한 여행복, 백팩
- **톤**: 흥미진진하고 생동감 있는
- **적합한 콘텐츠**: 여행, 관광, 문화

### 9. 게임 스트리머 (Gamer) 🎮
**특징**: 열정적인 게임 크리에이터
- **음성**: 리액션이 풍부한 목소리
- **스타일**: 게이밍 헤드셋, 캐주얼
- **톤**: 흥분되고 재미있는
- **적합한 콘텐츠**: 게임, e스포츠, 리뷰

### 10. 비즈니스 우먼 (Business Woman) 💼
**특징**: 카리스마 있는 여성 CEO
- **음성**: 자신감 넘치는 여성 목소리
- **스타일**: 프로페셔널한 정장
- **톤**: 강력하고 설득력 있는
- **적합한 콘텐츠**: 창업, 리더십, 비즈니스

---

## 캐릭터 데이터 구조

```typescript
interface Character {
  id: string;
  name: string;
  nameKr: string;
  icon: string;
  description: string;
  voiceStyle: string;
  tone: string;
  suitableFor: string[];
  imagePrompt: string;  // Minimax 비디오 생성용 프롬프트
  previewImage: string; // 미리보기 이미지 URL
}
```

## 캐릭터 프롬프트 템플릿

### 비즈니스 프로 예시
```
A professional male businessman in his 30s, wearing a navy blue suit and tie, 
standing in a modern office with glass windows, confident posture, 
natural lighting, realistic style, 4K quality
```

### 여성 리포터 예시
```
A friendly female news anchor in her late 20s, wearing a light blue blouse, 
sitting at a news desk with studio lighting, warm smile, professional makeup, 
realistic style, 4K quality
```

---

## 음성 매핑

각 캐릭터에 맞는 Minimax TTS 음성 스타일:

| 캐릭터 | 음성 스타일 | 특징 |
|--------|------------|------|
| 비즈니스 프로 | male-01 | 차분하고 전문적 |
| 여성 리포터 | female-01 | 명확하고 경쾌 |
| 테크 전문가 | male-02 | 젊고 에너제틱 |
| 요리 전문가 | male-03 | 따뜻하고 친근 |
| 피트니스 트레이너 | male-04 | 활기차고 동기부여 |
| 패션 크리에이터 | female-02 | 세련되고 트렌디 |
| 교육 멘토 | male-05 | 명확하고 친절 |
| 여행 가이드 | female-03 | 신나고 활발 |
| 게임 스트리머 | male-06 | 흥분되고 리액션 풍부 |
| 비즈니스 우먼 | female-04 | 자신감 넘치고 강력 |

---

## 구현 계획

### 1. 캐릭터 선택 페이지
```tsx
// pages/character-selection.tsx
<div className="character-grid">
  {characters.map(char => (
    <CharacterCard
      key={char.id}
      character={char}
      onSelect={handleSelect}
      selected={selectedCharacter?.id === char.id}
    />
  ))}
</div>
```

### 2. 캐릭터 데이터 저장
```typescript
// lib/characters.ts
export const characters: Character[] = [
  {
    id: 'business-pro',
    name: 'Business Pro',
    nameKr: '비즈니스 프로',
    icon: '👔',
    description: '전문적이고 신뢰감 있는 비즈니스맨',
    voiceStyle: 'male-professional',
    tone: 'serious-accurate',
    suitableFor: ['business', 'finance', 'economy'],
    imagePrompt: 'A professional male businessman...',
    previewImage: '/characters/business-pro.jpg'
  },
  // ... 나머지 캐릭터들
];
```

### 3. Minimax 비디오 생성 프롬프트
각 장면마다 캐릭터 프롬프트 + 콘텐츠 프롬프트 결합:

```
{character.imagePrompt} 
The character is {narration_action} while explaining: "{narration_text}". 
Natural hand gestures, friendly facial expressions, 
professional video quality, 3 seconds duration.
```

---

## 다음 단계

1. ✅ 캐릭터 프리셋 정의 완료
2. [ ] 캐릭터 선택 UI 컴포넌트 개발
3. [ ] Minimax Hailuo API 연동
4. [ ] 캐릭터별 비디오 생성 테스트
5. [ ] 기존 시스템과 통합

---

**작성일**: 2024-12-22  
**버전**: 1.0
