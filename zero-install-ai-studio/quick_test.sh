#!/bin/bash

echo "🎬 빠른 쇼츠 생성 테스트"
echo "========================"

# 최근 생성된 파일 사용
IMAGES=(
  "/generated/generated_20251226_235741.png"
  "/generated/generated_20251226_235739.png"
  "/generated/generated_20251226_235738.png"
)

AUDIOS=(
  "/audio/narration_20251226_235747.mp3"
  "/audio/narration_20251226_235746.mp3"
  "/audio/narration_20251226_235745.mp3"
)

echo "📸 이미지: ${#IMAGES[@]}개"
echo "🎙️ 오디오: ${#AUDIOS[@]}개"
echo ""

# 비디오 생성
echo "🎥 비디오 합성 중..."

VIDEO_PAYLOAD=$(cat <<JSON_END
{
  "title": "테스트쇼츠",
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

VIDEO_RESPONSE=$(timeout 60 curl -s -X POST http://localhost:5003/generate-video \
  -H "Content-Type: application/json" \
  -d "$VIDEO_PAYLOAD")

echo ""
echo "🎉 결과:"
echo "$VIDEO_RESPONSE" | jq '.'

VIDEO_URL=$(echo "$VIDEO_RESPONSE" | jq -r '.video_url')
if [ "$VIDEO_URL" != "null" ] && [ -n "$VIDEO_URL" ]; then
  echo ""
  echo "✅ 비디오 생성 성공!"
  echo "📹 비디오 URL: https://ai-studio.neuralgrid.kr$VIDEO_URL"
  
  FILE_PATH="public$VIDEO_URL"
  if [ -f "$FILE_PATH" ]; then
    FILE_SIZE=$(du -h "$FILE_PATH" | cut -f1)
    echo "📊 파일 크기: $FILE_SIZE"
    echo "📁 파일 위치: $FILE_PATH"
    
    # ffprobe로 비디오 정보 확인 (있으면)
    if command -v ffprobe &> /dev/null; then
      echo ""
      echo "📊 비디오 상세 정보:"
      ffprobe -v quiet -print_format json -show_format -show_streams "$FILE_PATH" 2>/dev/null | jq -r '.format | "길이: \(.duration)초, 크기: \(.size) bytes"'
    fi
  fi
else
  echo ""
  echo "❌ 비디오 생성 실패"
  echo "에러: $VIDEO_RESPONSE"
fi
