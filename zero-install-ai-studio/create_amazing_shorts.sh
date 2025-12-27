#!/bin/bash

echo "🎬 멋진 AI 쇼츠 자동 생성 시작!"
echo "================================"
echo ""

# 1단계: 스토리 생성
echo "📖 1단계: 스토리 생성 중..."
STORY_RESPONSE=$(curl -s -X POST http://localhost:5004/generate-story \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "우주를 탐험하는 용감한 고양이의 모험. 신비로운 행성을 발견하고 외계 생명체를 만나는 감동적인 이야기",
    "duration": 25
  }')

echo "$STORY_RESPONSE" > /tmp/story.json
STORY_TITLE=$(echo "$STORY_RESPONSE" | jq -r '.title')
TOTAL_SCENES=$(echo "$STORY_RESPONSE" | jq -r '.total_scenes')

echo "✅ 스토리 생성 완료!"
echo "   제목: $STORY_TITLE"
echo "   장면 수: $TOTAL_SCENES"
echo ""

# 2단계: 각 장면 이미지 생성
echo "🎨 2단계: AI 이미지 생성 중 ($TOTAL_SCENES 장면)..."
SCENES=$(echo "$STORY_RESPONSE" | jq -c '.scenes[]')
SCENE_NUM=0
IMAGE_URLS=()
DESCRIPTIONS=()
NARRATIONS=()

while IFS= read -r scene; do
  SCENE_NUM=$((SCENE_NUM + 1))
  DESCRIPTION=$(echo "$scene" | jq -r '.description')
  NARRATION=$(echo "$scene" | jq -r '.narration // .korean_description')
  
  echo "   장면 $SCENE_NUM: $DESCRIPTION"
  
  IMAGE_RESPONSE=$(curl -s -X POST http://localhost:5002/generate-image \
    -H "Content-Type: application/json" \
    -d "{
      \"prompt\": \"$DESCRIPTION\",
      \"width\": 1080,
      \"height\": 1920,
      \"style\": \"cinematic\"
    }")
  
  IMAGE_URL=$(echo "$IMAGE_RESPONSE" | jq -r '.image_url')
  IMAGE_URLS+=("$IMAGE_URL")
  DESCRIPTIONS+=("$DESCRIPTION")
  NARRATIONS+=("$NARRATION")
  
  echo "   ✅ 이미지 생성: $IMAGE_URL"
done <<< "$SCENES"

echo "✅ 모든 이미지 생성 완료!"
echo ""

# 3단계: 각 장면 TTS 생성
echo "🎙️ 3단계: TTS 음성 생성 중..."
AUDIO_URLS=()

for i in "${!NARRATIONS[@]}"; do
  SCENE_NUM=$((i + 1))
  NARRATION="${NARRATIONS[$i]}"
  
  echo "   장면 $SCENE_NUM 음성 생성 중..."
  
  TTS_RESPONSE=$(curl -s -X POST http://localhost:5005/generate-tts \
    -H "Content-Type: application/json" \
    -d "{
      \"text\": \"$NARRATION\",
      \"voice_id\": \"default\"
    }")
  
  AUDIO_URL=$(echo "$TTS_RESPONSE" | jq -r '.audio_url')
  AUDIO_URLS+=("$AUDIO_URL")
  
  echo "   ✅ 음성 생성: $AUDIO_URL"
done

echo "✅ 모든 음성 생성 완료!"
echo ""

# 4단계: 비디오 합성용 JSON 생성
echo "🎥 4단계: 비디오 합성 중..."
VIDEO_SCENES='['

for i in "${!IMAGE_URLS[@]}"; do
  SCENE_NUM=$((i + 1))
  
  if [ $i -gt 0 ]; then
    VIDEO_SCENES+=','
  fi
  
  VIDEO_SCENES+="{
    \"scene_number\": $SCENE_NUM,
    \"description\": \"${DESCRIPTIONS[$i]}\",
    \"duration\": 5,
    \"style\": \"cinematic\",
    \"camera_movement\": \"slow_zoom\",
    \"image_url\": \"${IMAGE_URLS[$i]}\",
    \"audio_url\": \"${AUDIO_URLS[$i]}\"
  }"
done

VIDEO_SCENES+=']'

# 비디오 생성 요청
VIDEO_PAYLOAD="{
  \"title\": \"$STORY_TITLE\",
  \"fps\": 30,
  \"scenes\": $VIDEO_SCENES
}"

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

# 5단계: 결과 출력
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
echo "   https://ai-studio.neuralgrid.kr$VIDEO_URL"
echo ""
echo "✅ 완료!"
