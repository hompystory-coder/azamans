#!/bin/bash

echo "🎨 개선된 이미지 프롬프트 테스트"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 스토리 생성
RESPONSE=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "우주를 탐험하는 고양이",
    "duration": 30
  }')

TITLE=$(echo "$RESPONSE" | jq -r '.story.title')
TOTAL_SCENES=$(echo "$RESPONSE" | jq -r '.story.total_scenes')

echo "📖 제목: $TITLE"
echo "🎞️  총 장면: $TOTAL_SCENES"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔍 각 장면의 이미지 생성 프롬프트 확인"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 첫 3개 장면의 프롬프트 출력
for i in 0 1 2; do
  SCENE_NUM=$((i + 1))
  SCENE_TITLE=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].title")
  DESCRIPTION=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].description")
  
  echo "장면 $SCENE_NUM: $SCENE_TITLE"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "$DESCRIPTION" | fold -w 80 -s
  echo ""
  echo ""
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ 확인 사항:"
echo "  1. 각 프롬프트에 '우주를 탐험하는 고양이'가 포함되어 있는가?"
echo "  2. 각 막(발단/전개/위기/절정/결말)에 맞는 구체적인 행동이 있는가?"
echo "  3. 일반적인 'scene 1', 'introduction' 같은 모호한 표현이 줄어들었는가?"
echo ""

