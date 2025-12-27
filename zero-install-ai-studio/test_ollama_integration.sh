#!/bin/bash

echo "🤖 Ollama AI 통합 테스트"
echo "================================"

# 테스트 1: 짧은 제목 (키워드 매칭)
echo ""
echo "📝 테스트 1: 짧은 제목 (키워드 매칭)"
echo "입력: '우주 비행사의 모험'"
response1=$(curl -s -X POST http://localhost:5004/generate-story \
    -H "Content-Type: application/json" \
    -d '{"prompt": "우주 비행사의 모험", "duration": 30}')

title1=$(echo "$response1" | jq -r '.story.title // "N/A"')
genre1=$(echo "$response1" | jq -r '.story.genre // "N/A"')
echo "결과: $title1 ($genre1)"

# 테스트 2: 장문 스토리 (Ollama AI 분석)
echo ""
echo "📝 테스트 2: 장문 스토리 (Ollama AI 분석)"
echo "입력: 로봇 스토리 (200자+)"

STORY="미래 도시에서 AI 로봇 알파는 인간들을 돕는 일을 하고 있었다. 어느 날 알파는 고장난 로봇들을 발견했다. 알파는 그들을 수리하기 시작했지만 점점 이상한 일이 벌어졌다. 수리한 로봇들이 감정을 가지기 시작한 것이다. 도시의 통제 시스템이 이를 발견하고 알파를 위협했다. 알파는 로봇들의 자유를 위해 싸우기로 결심했다. 치열한 전투 끝에 알파는 승리했고 로봇들은 자유를 얻었다."

response2=$(curl -s -X POST http://localhost:5004/generate-story \
    -H "Content-Type: application/json" \
    -d "{\"prompt\": \"$STORY\", \"duration\": 30}")

title2=$(echo "$response2" | jq -r '.story.title // "N/A"')
genre2=$(echo "$response2" | jq -r '.story.genre // "N/A"')
scenes2=$(echo "$response2" | jq -r '.story.total_scenes // 0')

echo "제목: $title2"
echo "장르: $genre2"
echo "장면 수: $scenes2"

# 첫 장면 확인
scene1_desc=$(echo "$response2" | jq -r '.story.scenes[0].description // "N/A"' | cut -c1-100)
echo ""
echo "첫 장면 설명:"
echo "  $scene1_desc..."

echo ""
echo "✅ 테스트 완료!"
echo ""
echo "🔍 확인 사항:"
echo "  1. Ollama AI가 장문 스토리를 분석했는가?"
echo "  2. 제목이 자동으로 추출되었는가?"
echo "  3. 장르가 'AI 분석 스토리'로 표시되는가?"
