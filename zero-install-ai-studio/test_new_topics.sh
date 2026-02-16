#!/bin/bash

echo "🧪 새로운 주제 테스트: 제빵사, 택시기사, 수영선수, 바리스타"
echo "=================================================="

test_topics=(
    "행복한 제빵사의 아침"
    "택시 기사의 특별한 날"
    "수영 선수의 금메달 도전"
    "바리스타의 완벽한 커피"
)

for topic in "${test_topics[@]}"; do
    echo ""
    echo "📝 테스트: '$topic'"
    echo "----------------------------"
    
    response=$(curl -s -X POST http://localhost:5004/generate-story \
        -H "Content-Type: application/json" \
        -d "{\"prompt\": \"$topic\", \"duration\": 30}")
    
    # 스토리 제목 출력
    title=$(echo "$response" | jq -r '.story.title // "N/A"')
    total_scenes=$(echo "$response" | jq -r '.story.total_scenes // 0')
    
    echo "제목: $title"
    echo "총 장면 수: $total_scenes"
    
    # 장면 1, 3, 6의 description 출력 (키워드 포함 확인)
    for scene_num in 1 3 6; do
        desc=$(echo "$response" | jq -r ".story.scenes[$((scene_num-1))].description // \"N/A\"")
        echo ""
        echo "  장면 $scene_num:"
        echo "    $(echo "$desc" | cut -c1-150)..."
    done
    
    echo ""
done

echo ""
echo "✅ 모든 테스트 완료!"
echo ""
echo "🔍 검증 포인트:"
echo "  1. 각 주제에 맞는 구체적 행동이 포함되었는가?"
echo "  2. '제빵사' → 빵 굽기, 반죽, 오븐 관련 키워드"
echo "  3. '택시기사' → 운전, 승객, 교통 관련 키워드"
echo "  4. '수영선수' → 수영장, 물, 레이스 관련 키워드"
echo "  5. '바리스타' → 커피, 에스프레소, 라떼 관련 키워드"
