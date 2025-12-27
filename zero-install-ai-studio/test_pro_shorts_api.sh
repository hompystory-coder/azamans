#!/bin/bash

echo "🎬 Pro Shorts API 엔드투엔드 테스트"
echo "======================================"
echo ""

# 1. 스토리 생성
echo "1️⃣ 스토리 생성 테스트..."
STORY_RESULT=$(curl -s -X POST http://localhost:3001/api/story \
  -H "Content-Type: application/json" \
  -d '{"prompt": "달나라로 여행을 떠난 토끼", "duration": 20}')

echo "$STORY_RESULT" | jq '.success, .story.title, .story.total_scenes' 2>/dev/null || echo "스토리 생성 실패"
echo ""

# 2. 이미지 생성 테스트  
echo "2️⃣ 이미지 생성 테스트..."
IMAGE_RESULT=$(curl -s -X POST http://localhost:3001/api/image \
  -H "Content-Type: application/json" \
  -d '{"prompt": "달나라 토끼가 우주선을 타고 출발하는 장면", "width": 1080, "height": 1920}')

IMAGE_URL=$(echo "$IMAGE_RESULT" | jq -r '.image_url' 2>/dev/null)
echo "이미지 URL: $IMAGE_URL"
echo ""

# 3. TTS 생성 테스트
echo "3️⃣ TTS 생성 테스트..."
TTS_RESULT=$(curl -s -X POST http://localhost:3001/api/tts \
  -H "Content-Type: application/json" \
  -d '{"text": "토끼가 달나라로 여행을 떠났습니다.", "voice_id": "default"}')

AUDIO_URL=$(echo "$TTS_RESULT" | jq -r '.audio_url' 2>/dev/null)
echo "오디오 URL: $AUDIO_URL"
echo ""

# 4. 비디오 생성 테스트
echo "4️⃣ 비디오 생성 테스트..."
VIDEO_RESULT=$(curl -s -X POST http://localhost:3001/api/video \
  -H "Content-Type: application/json" \
  -d "{
    \"title\": \"테스트_쇼츠\",
    \"fps\": 30,
    \"scenes\": [
      {
        \"scene_number\": 1,
        \"description\": \"테스트 장면\",
        \"duration\": 5,
        \"style\": \"cinematic\",
        \"camera_movement\": \"static\",
        \"image_url\": \"$IMAGE_URL\",
        \"audio_url\": \"$AUDIO_URL\"
      }
    ]
  }")

echo "$VIDEO_RESULT" | jq '.' 2>/dev/null || echo "비디오 생성 실패"

VIDEO_URL=$(echo "$VIDEO_RESULT" | jq -r '.video_url' 2>/dev/null)
echo ""
echo "✅ 테스트 완료!"
echo "비디오 URL: https://ai-studio.neuralgrid.kr$VIDEO_URL"
