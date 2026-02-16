#!/bin/bash

echo "🧪 미지원 키워드 테스트 (현재 시스템)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

test_stories=(
  "행복한 제빵사의 아침"
  "택시 기사의 특별한 날"
  "수영 선수의 금메달 도전"
  "바리스타의 완벽한 커피"
)

for story in "${test_stories[@]}"; do
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "📖 테스트: $story"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  
  RESPONSE=$(curl -s -X POST http://localhost:5004/generate-story \
    -H "Content-Type: application/json" \
    -d "{\"prompt\": \"$story\", \"duration\": 30}")
  
  # 장면 1, 6만 출력
  for i in 0 5; do
    SCENE_NUM=$((i + 1))
    DESC=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].description" | cut -d':' -f2 | cut -d'.' -f1)
    echo "  장면 $SCENE_NUM:$DESC"
  done
  
  echo ""
  echo "  ❌ 문제: 일반적인 'character', 'journey' 같은 키워드만 사용됨"
  echo ""
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "💡 해결책: AI 기반 자동 행동 생성 필요!"
echo ""

