#!/bin/bash

echo "========================================="
echo "🎬 6·7씬 나레이션 중복 제거 테스트"
echo "========================================="
echo ""

# 테스트 1: 7씬 생성 - 우주 비행사
echo "📝 테스트 1: 우주 비행사의 모험 (7씬)"
echo "----------------------------------------"
RESULT1=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{"prompt":"우주 비행사의 모험","duration":28}')

# 총 장면 수
TOTAL_SCENES=$(echo "$RESULT1" | jq -r '.story.total_scenes')
echo "✅ 총 장면 수: $TOTAL_SCENES"
echo ""

# 모든 장면의 나레이션 추출 및 중복 체크
echo "📋 전체 나레이션 목록:"
echo "$RESULT1" | jq -r '.story.scenes[] | "씬 \(.scene_number): \(.narration)"'
echo ""

# 중복 나레이션 체크
DUPLICATE_CHECK=$(echo "$RESULT1" | jq -r '.story.scenes[].narration' | sort | uniq -d)
if [ -z "$DUPLICATE_CHECK" ]; then
    echo "✅ 중복 나레이션 없음! (완벽)"
else
    echo "❌ 중복 발견:"
    echo "$DUPLICATE_CHECK"
fi
echo ""

# 6번과 7번 씬 비교
echo "🔍 6번·7번 씬 집중 분석:"
SCENE_6=$(echo "$RESULT1" | jq -r '.story.scenes[5].narration // "없음"')
SCENE_7=$(echo "$RESULT1" | jq -r '.story.scenes[6].narration // "없음"')
echo "씬 6: $SCENE_6"
echo "씬 7: $SCENE_7"

if [ "$SCENE_6" == "$SCENE_7" ]; then
    echo "❌ 6번과 7번 씬 중복 발견!"
else
    echo "✅ 6번과 7번 씬 완전 차별화 성공!"
fi
echo ""

# 테스트 2: 5씬 생성 - 제빵사
echo "========================================="
echo "📝 테스트 2: 제빵사의 아침 (5씬)"
echo "----------------------------------------"
RESULT2=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{"prompt":"제빵사의 아침","duration":20}')

TOTAL_SCENES2=$(echo "$RESULT2" | jq -r '.story.total_scenes')
echo "✅ 총 장면 수: $TOTAL_SCENES2"
echo ""

echo "📋 나레이션 목록:"
echo "$RESULT2" | jq -r '.story.scenes[] | "씬 \(.scene_number): \(.narration)"'
echo ""

DUPLICATE_CHECK2=$(echo "$RESULT2" | jq -r '.story.scenes[].narration' | sort | uniq -d)
if [ -z "$DUPLICATE_CHECK2" ]; then
    echo "✅ 중복 나레이션 없음! (완벽)"
else
    echo "❌ 중복 발견:"
    echo "$DUPLICATE_CHECK2"
fi
echo ""

echo "========================================="
echo "✅ 테스트 완료!"
echo "========================================="
echo ""
echo "📊 검증 포인트:"
echo "1. 7씬 생성 시 중복 없음"
echo "2. 6번·7번 씬 차별화 성공"
echo "3. 5씬 생성 시에도 중복 없음"
echo "4. 모든 나레이션 고유성 보장"
