#!/bin/bash

echo "📚 장문 스토리 → 쇼츠 영상 테스트"
echo "========================================"

# 테스트 장문 스토리 1: 짧은 동화
STORY1="옛날 옛날 어느 마을에 가난하지만 착한 소녀가 살았습니다. 소녀는 매일 아침 일찍 일어나 산에 나무를 하러 갔어요. 어느 날 소녀가 나무를 하고 있는데, 다친 토끼 한 마리를 발견했습니다. 소녀는 토끼를 정성껏 치료해주고 숲으로 돌려보냈어요. 그날 밤, 신기한 일이 일어났습니다. 소녀의 집 앞에 금으로 만든 나무가 자라나 있었던 거예요! 토끼가 은혜를 갚기 위해 마법을 부린 것이었습니다. 소녀는 금나무 덕분에 부자가 되었고, 마을 사람들과 행복하게 살았답니다."

# 테스트 장문 스토리 2: 모험 이야기
STORY2="젊은 탐험가 준호는 전설의 보물을 찾아 깊은 정글로 떠났다. 첫 며칠은 순조로웠지만, 갑자기 거대한 폭풍이 몰아쳤다. 준호는 비를 피해 동굴로 들어갔는데, 그곳에서 고대 지도를 발견했다! 지도를 따라가자 신비한 신전이 나타났다. 신전 안에는 무시무시한 함정들이 가득했지만, 준호는 용기와 지혜로 하나씩 극복했다. 마침내 보물이 있는 방에 도착했지만, 그곳을 지키는 거대한 돌거인과 마주쳤다. 치열한 싸움 끝에 준호는 거인을 물리치고 보물을 손에 넣었다. 하지만 진짜 보물은 그 과정에서 얻은 용기와 경험이었다는 것을 깨달았다."

echo ""
echo "📝 테스트 1: 짧은 동화 (${#STORY1}자)"
echo "-------------------------------------------"
response1=$(curl -s -X POST http://localhost:5004/generate-story \
    -H "Content-Type: application/json" \
    -d "{\"prompt\": \"$STORY1\", \"duration\": 30}")

title1=$(echo "$response1" | jq -r '.story.title // "N/A"')
genre1=$(echo "$response1" | jq -r '.story.genre // "N/A"')
scenes1=$(echo "$response1" | jq -r '.story.total_scenes // 0')

echo "제목: $title1"
echo "장르: $genre1"
echo "총 장면 수: $scenes1"

# 첫 3개 장면의 설명 출력
for i in 0 1 2; do
    scene_title=$(echo "$response1" | jq -r ".story.scenes[$i].title // \"N/A\"")
    scene_desc=$(echo "$response1" | jq -r ".story.scenes[$i].description // \"N/A\"" | cut -c1-100)
    echo "  장면 $((i+1)): $scene_title"
    echo "    $scene_desc..."
done

echo ""
echo "📝 테스트 2: 모험 이야기 (${#STORY2}자)"
echo "-------------------------------------------"
response2=$(curl -s -X POST http://localhost:5004/generate-story \
    -H "Content-Type: application/json" \
    -d "{\"prompt\": \"$STORY2\", \"duration\": 30}")

title2=$(echo "$response2" | jq -r '.story.title // "N/A"')
genre2=$(echo "$response2" | jq -r '.story.genre // "N/A"')
scenes2=$(echo "$response2" | jq -r '.story.total_scenes // 0')

echo "제목: $title2"
echo "장르: $genre2"
echo "총 장면 수: $scenes2"

for i in 0 1 2; do
    scene_title=$(echo "$response2" | jq -r ".story.scenes[$i].title // \"N/A\"")
    scene_desc=$(echo "$response2" | jq -r ".story.scenes[$i].description // \"N/A\"" | cut -c1-100)
    echo "  장면 $((i+1)): $scene_title"
    echo "    $scene_desc..."
done

echo ""
echo "✅ 테스트 완료!"
echo ""
echo "🔍 검증 포인트:"
echo "  1. 장문 스토리가 자동으로 감지되었는가?"
echo "  2. 적절한 제목이 추출되었는가?"
echo "  3. 5막 구조로 장면이 생성되었는가?"
echo "  4. 각 장면이 스토리와 연관성이 있는가?"
