#!/bin/bash

echo "🎬 AI 쇼츠 생성 테스트 시작!"
echo "================================"

# 1. 스토리 생성
echo ""
echo "1️⃣ 스토리 생성 중..."
STORY_RESPONSE=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{"prompt": "토끼와 거북이", "duration": 15}')

echo "✅ 스토리 생성 완료!"
echo "$STORY_RESPONSE" | jq -r '.story.title'
SCENES_COUNT=$(echo "$STORY_RESPONSE" | jq -r '.story.total_scenes')
echo "총 $SCENES_COUNT 개 장면"

# 장면 데이터 추출
SCENES=$(echo "$STORY_RESPONSE" | jq -c '.story.scenes')

# 2. 이미지 생성 (첫 3개 장면만)
echo ""
echo "2️⃣ 이미지 생성 중 (3개 장면)..."
IMAGES=()
for i in 0 1 2; do
  DESCRIPTION=$(echo "$SCENES" | jq -r ".[$i].description")
  echo "  Scene $((i+1)): $DESCRIPTION"
  
  IMAGE_RESPONSE=$(curl -s -X POST http://localhost:5002/generate-image \
    -H "Content-Type: application/json" \
    -d "{\"prompt\": \"$DESCRIPTION\", \"width\": 1080, \"height\": 1920}")
  
  IMAGE_URL=$(echo "$IMAGE_RESPONSE" | jq -r '.image_url')
  IMAGES+=("$IMAGE_URL")
  echo "  ✅ 이미지: $IMAGE_URL"
done

# 3. TTS 생성 (첫 3개 장면만)
echo ""
echo "3️⃣ TTS 음성 생성 중 (3개 장면)..."
AUDIOS=()
for i in 0 1 2; do
  NARRATION=$(echo "$SCENES" | jq -r ".[$i].narration // .[$i].korean_description")
  echo "  Scene $((i+1)) 음성 생성 중..."
  
  TTS_RESPONSE=$(curl -s -X POST http://localhost:5005/generate-tts \
    -H "Content-Type: application/json" \
    -d "{\"text\": \"$NARRATION\"}")
  
  AUDIO_URL=$(echo "$TTS_RESPONSE" | jq -r '.audio_url')
  AUDIOS+=("$AUDIO_URL")
  echo "  ✅ 음성: $AUDIO_URL"
done

# 4. 비디오 생성
echo ""
echo "4️⃣ 비디오 합성 중..."

# JSON 페이로드 생성
VIDEO_PAYLOAD=$(cat <<JSON_END
{
  "title": "토끼와 거북이",
  "scenes": [
    {
      "description": "Scene 1",
      "duration": 5,
      "style": "traditional",
      "camera_movement": "static",
      "image_url": "${IMAGES[0]}",
      "audio_url": "${AUDIOS[0]}"
    },
    {
      "description": "Scene 2",
      "duration": 5,
      "style": "traditional",
      "camera_movement": "static",
      "image_url": "${IMAGES[1]}",
      "audio_url": "${AUDIOS[1]}"
    },
    {
      "description": "Scene 3",
      "duration": 5,
      "style": "traditional",
      "camera_movement": "static",
      "image_url": "${IMAGES[2]}",
      "audio_url": "${AUDIOS[2]}"
    }
  ],
  "fps": 30
}
JSON_END
)

echo "$VIDEO_PAYLOAD" > /tmp/video_request.json
echo "비디오 요청 JSON 저장: /tmp/video_request.json"

VIDEO_RESPONSE=$(curl -s -X POST http://localhost:5003/generate-video \
  -H "Content-Type: application/json" \
  -d "$VIDEO_PAYLOAD")

echo ""
echo "================================"
echo "🎉 결과:"
echo "$VIDEO_RESPONSE" | jq '.'

VIDEO_URL=$(echo "$VIDEO_RESPONSE" | jq -r '.video_url')
if [ "$VIDEO_URL" != "null" ]; then
  echo ""
  echo "✅ 비디오 생성 성공!"
  echo "📹 비디오 URL: https://ai-studio.neuralgrid.kr$VIDEO_URL"
  echo "📁 파일 위치: public$VIDEO_URL"
  
  # 파일 확인
  FILE_PATH="public$VIDEO_URL"
  if [ -f "$FILE_PATH" ]; then
    FILE_SIZE=$(du -h "$FILE_PATH" | cut -f1)
    echo "📊 파일 크기: $FILE_SIZE"
  fi
else
  echo ""
  echo "❌ 비디오 생성 실패"
fi
