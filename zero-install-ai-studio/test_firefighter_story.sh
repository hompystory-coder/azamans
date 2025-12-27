#!/bin/bash

echo "🔥 '용감한 소방관의 하루' 스토리 테스트"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 스토리 생성
RESPONSE=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "용감한 소방관의 하루",
    "duration": 30
  }')

TITLE=$(echo "$RESPONSE" | jq -r '.story.title')
TOTAL_SCENES=$(echo "$RESPONSE" | jq -r '.story.total_scenes')

echo "📖 제목: $TITLE"
echo "🎞️  총 장면: $TOTAL_SCENES"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎨 각 장면의 이미지 프롬프트 확인"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

for i in 0 1 2 3 4 5 6; do
  SCENE_NUM=$((i + 1))
  SCENE_TITLE=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].title")
  DESCRIPTION=$(echo "$RESPONSE" | jq -r ".story.scenes[$i].description")
  
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "장면 $SCENE_NUM: $SCENE_TITLE"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "현재 프롬프트:"
  echo "$DESCRIPTION" | fold -w 80 -s
  echo ""
  
  # '소방관' 키워드 체크
  if echo "$DESCRIPTION" | grep -qi "firefighter\|소방관"; then
    echo "✅ '소방관' 키워드 포함됨"
  else
    echo "❌ '소방관' 키워드 없음!"
  fi
  echo ""
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎯 올바른 이미지 시퀀스 (기대되는 것):"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  장면 1: 소방서에서 아침 준비하는 소방관 (소방복 입기)"
echo "  장면 2: 출동 벨 울리고 소방차 타는 소방관들"
echo "  장면 3: 불타는 건물 앞에 도착한 소방차"
echo "  장면 4: 화재 진압하는 용감한 소방관 (물 호스 사용)"
echo "  장면 5: 건물에서 사람 구출하는 소방관"
echo "  장면 6: 화재 진압 완료, 피곤하지만 뿌듯한 소방관"
echo "  장면 7: 소방서로 돌아와 휴식하는 소방관들"
echo ""

