#!/bin/bash

echo "🎬 장면 순서 테스트 시작..."

# 기존 테스트 이미지와 오디오 사용
IMAGES=(
  "/home/azamans/webapp/zero-install-ai-studio/public/generated/generated_20251226_235741.png"
  "/home/azamans/webapp/zero-install-ai-studio/public/generated/generated_20251226_235739.png"
  "/home/azamans/webapp/zero-install-ai-studio/public/generated/generated_20251226_235738.png"
)

AUDIOS=(
  "/home/azamans/webapp/zero-install-ai-studio/public/audio/narration_20251226_235747.mp3"
  "/home/azamans/webapp/zero-install-ai-studio/public/audio/narration_20251226_235746.mp3"
  "/home/azamans/webapp/zero-install-ai-studio/public/audio/narration_20251226_235745.mp3"
)

echo "📸 이미지: ${#IMAGES[@]}개"
echo "🎙️ 오디오: ${#AUDIOS[@]}개"

# JSON 페이로드 생성 (scene_number 명시)
cat > /tmp/video_test_order.json << PAYLOAD
{
  "title": "순서테스트",
  "fps": 30,
  "scenes": [
    {
      "scene_number": 1,
      "description": "장면 1 - 첫 번째",
      "duration": 5,
      "style": "traditional",
      "camera_movement": "static",
      "audio_url": "/audio/narration_20251226_235747.mp3",
      "image_url": "/generated/generated_20251226_235741.png"
    },
    {
      "scene_number": 2,
      "description": "장면 2 - 두 번째",
      "duration": 5,
      "style": "traditional",
      "camera_movement": "static",
      "audio_url": "/audio/narration_20251226_235746.mp3",
      "image_url": "/generated/generated_20251226_235739.png"
    },
    {
      "scene_number": 3,
      "description": "장면 3 - 세 번째",
      "duration": 5,
      "style": "traditional",
      "camera_movement": "static",
      "audio_url": "/audio/narration_20251226_235745.mp3",
      "image_url": "/generated/generated_20251226_235738.png"
    }
  ]
}
PAYLOAD

echo ""
echo "🎥 비디오 합성 시작..."
curl -X POST http://localhost:5003/generate-video \
  -H "Content-Type: application/json" \
  -d @/tmp/video_test_order.json | jq '.'

echo ""
echo "✅ 테스트 완료!"
