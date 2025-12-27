#!/bin/bash

echo "🎬 스토리 품질 테스트 시작..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 30초 스토리 생성 (7-8개 장면 예상)
RESPONSE=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "마법의 성을 찾아가는 용감한 기사",
    "duration": 30
  }')

echo "✅ 스토리 생성 완료!"
echo ""

# 제목과 장면 수 확인
TITLE=$(echo "$RESPONSE" | jq -r '.story.title')
TOTAL_SCENES=$(echo "$RESPONSE" | jq -r '.story.total_scenes')

echo "📖 제목: $TITLE"
echo "🎞️  총 장면 수: $TOTAL_SCENES"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔍 각 장면별 나레이션 검사 (중복 확인)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 각 장면의 나레이션 출력
for ((i=0; i<$TOTAL_SCENES; i++)); do
  SCENE_NUM=$((i + 1))
  TITLE=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].title")
  NARRATION=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].narration")
  MOOD=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].mood")
  
  echo "장면 $SCENE_NUM: $TITLE"
  echo "  🎭 분위기: $MOOD"
  echo "  🎙️  나레이션: $NARRATION"
  echo ""
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ 테스트 완료!"
echo ""

# 중복 나레이션 검사
echo "🔍 중복 나레이션 검사..."
NARRATIONS=$(echo "$RESPONSE" | jq -r '.story.scenes[].narration' | sort)
UNIQUE_NARRATIONS=$(echo "$NARRATIONS" | uniq)
TOTAL_COUNT=$(echo "$NARRATIONS" | wc -l)
UNIQUE_COUNT=$(echo "$UNIQUE_NARRATIONS" | wc -l)

echo "  전체 나레이션 수: $TOTAL_COUNT"
echo "  고유 나레이션 수: $UNIQUE_COUNT"

if [ "$TOTAL_COUNT" -eq "$UNIQUE_COUNT" ]; then
  echo "  ✅ 중복 없음! 모든 장면이 고유한 나레이션을 가지고 있습니다."
else
  echo "  ⚠️ 중복 발견! $((TOTAL_COUNT - UNIQUE_COUNT))개의 중복이 있습니다."
  echo ""
  echo "중복된 나레이션:"
  echo "$NARRATIONS" | uniq -d
fi

