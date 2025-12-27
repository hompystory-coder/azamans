#!/bin/bash

echo "🎬 멋진 AI 쇼츠 자동 생성 시작!"
echo "================================"
echo ""

# 1단계: 스토리 생성
echo "📖 1단계: 스토리 생성 중..."
STORY_RESPONSE=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{"prompt": "우주를 탐험하는 용감한 고양이", "duration": 25}')

echo "$STORY_RESPONSE" > /tmp/story.json

# story 객체에서 데이터 추출
STORY_TITLE=$(echo "$STORY_RESPONSE" | jq -r '.story.title')
TOTAL_SCENES=$(echo "$STORY_RESPONSE" | jq -r '.story.total_scenes')

echo "✅ 스토리 생성 완료!"
echo "   제목: $STORY_TITLE"
echo "   장면 수: $TOTAL_SCENES"
echo ""

# 2단계: 각 장면 이미지 생성
echo "🎨 2단계: AI 이미지 생성 중 ($TOTAL_SCENES 장면)..."
SCENE_COUNT=$(echo "$STORY_RESPONSE" | jq '.story.scenes | length')

for ((i=0; i<$SCENE_COUNT; i++)); do
  SCENE_NUM=$((i + 1))
  DESCRIPTION=$(echo "$STORY_RESPONSE" | jq -r ".story.scenes[$i].description")
  NARRATION=$(echo "$STORY_RESPONSE" | jq -r ".story.scenes[$i].narration")
  
  echo "   장면 $SCENE_NUM: 이미지 생성 중..."
  
  IMAGE_RESPONSE=$(curl -s -X POST http://localhost:5002/generate-image \
    -H "Content-Type: application/json" \
    -d "{\"prompt\": \"$DESCRIPTION\", \"width\": 1080, \"height\": 1920}")
  
  IMAGE_URL=$(echo "$IMAGE_RESPONSE" | jq -r '.image_url')
  echo "$IMAGE_URL" >> /tmp/image_urls.txt
  echo "$DESCRIPTION" >> /tmp/descriptions.txt
  echo "$NARRATION" >> /tmp/narrations.txt
  
  echo "   ✅ 장면 $SCENE_NUM 이미지: $IMAGE_URL"
done

echo "✅ 모든 이미지 생성 완료!"
echo ""

# 3단계: 각 장면 TTS 생성
echo "🎙️ 3단계: TTS 음성 생성 중..."

SCENE_IDX=0
while IFS= read -r narration; do
  SCENE_NUM=$((SCENE_IDX + 1))
  echo "   장면 $SCENE_NUM 음성 생성 중..."
  
  TTS_RESPONSE=$(curl -s -X POST http://localhost:5005/generate-tts \
    -H "Content-Type: application/json" \
    -d "{\"text\": \"$narration\", \"voice_id\": \"default\"}")
  
  AUDIO_URL=$(echo "$TTS_RESPONSE" | jq -r '.audio_url')
  echo "$AUDIO_URL" >> /tmp/audio_urls.txt
  
  echo "   ✅ 장면 $SCENE_NUM 음성: $AUDIO_URL"
  SCENE_IDX=$((SCENE_IDX + 1))
done < /tmp/narrations.txt

echo "✅ 모든 음성 생성 완료!"
echo ""

# 4단계: 비디오 합성용 JSON 생성
echo "🎥 4단계: 비디오 합성 중..."

# 배열 읽기
mapfile -t IMAGE_URLS < /tmp/image_urls.txt
mapfile -t AUDIO_URLS < /tmp/audio_urls.txt
mapfile -t DESCRIPTIONS < /tmp/descriptions.txt

# JSON 생성
VIDEO_SCENES="["
for i in "${!IMAGE_URLS[@]}"; do
  SCENE_NUM=$((i + 1))
  
  if [ $i -gt 0 ]; then
    VIDEO_SCENES+=","
  fi
  
  VIDEO_SCENES+="{\"scene_number\":$SCENE_NUM,\"description\":\"장면 $SCENE_NUM\",\"duration\":4,\"style\":\"cinematic\",\"camera_movement\":\"static\",\"image_url\":\"${IMAGE_URLS[$i]}\",\"audio_url\":\"${AUDIO_URLS[$i]}\"}"
done
VIDEO_SCENES+="]"

VIDEO_PAYLOAD="{\"title\":\"$STORY_TITLE\",\"fps\":30,\"scenes\":$VIDEO_SCENES}"
echo "$VIDEO_PAYLOAD" > /tmp/video_payload.json

VIDEO_RESPONSE=$(curl -s -X POST http://localhost:5003/generate-video \
  -H "Content-Type: application/json" \
  -d "$VIDEO_PAYLOAD")

VIDEO_URL=$(echo "$VIDEO_RESPONSE" | jq -r '.video_url')
VIDEO_FILENAME=$(echo "$VIDEO_RESPONSE" | jq -r '.filename')
VIDEO_DURATION=$(echo "$VIDEO_RESPONSE" | jq -r '.duration')
FILE_SIZE=$(echo "$VIDEO_RESPONSE" | jq -r '.file_size')

echo "✅ 비디오 합성 완료!"
echo ""

# 결과 출력
echo "================================"
echo "🎉 AI 쇼츠 생성 완료!"
echo "================================"
echo ""
echo "📊 비디오 정보:"
echo "   제목: $STORY_TITLE"
echo "   파일명: $VIDEO_FILENAME"
echo "   재생 시간: ${VIDEO_DURATION}초"
echo "   파일 크기: $FILE_SIZE bytes"
echo "   장면 수: $TOTAL_SCENES"
echo ""
echo "🔗 다운로드 URL:"
ENCODED_URL=$(python3 -c "import urllib.parse; print(urllib.parse.quote('$VIDEO_URL'))")
echo "   https://ai-studio.neuralgrid.kr$ENCODED_URL"
echo ""
echo "✅ 완료!"

# 임시 파일 정리
rm -f /tmp/image_urls.txt /tmp/audio_urls.txt /tmp/descriptions.txt /tmp/narrations.txt
