#!/bin/bash

echo "🎙️ TTS 음성 생성 재시도..."

> /tmp/audio_urls_new.txt

# 나레이션 읽어서 TTS 생성
cat << 'NARR' > /tmp/narrations_final.txt
여러분 이건 진짜 믿기 힘든 이야기인데 한번 들어보세요.
처음에는 평범해 보였지만 알고 보니 완전히 다른 상황이었어요.
그런데 여기서 예상치 못한 일이 벌어지기 시작했어요.
이제부터가 진짜 중요한 순간인데 과연 어떻게 될까요.
결국 상황은 점점 심각해지고 긴장감이 최고조에 달했어요.
NARR

SCENE_IDX=0
while IFS= read -r narration; do
  SCENE_NUM=$((SCENE_IDX + 1))
  echo "   장면 $SCENE_NUM: 음성 생성 중..."
  
  TTS_RESPONSE=$(curl -s -X POST http://localhost:5005/generate-tts \
    -H "Content-Type: application/json" \
    -d "{\"text\": \"$narration\", \"voice_id\": \"default\"}")
  
  AUDIO_URL=$(echo "$TTS_RESPONSE" | jq -r '.audio_url')
  
  if [ "$AUDIO_URL" != "null" ] && [ -n "$AUDIO_URL" ]; then
    echo "$AUDIO_URL" >> /tmp/audio_urls_new.txt
    echo "   ✅ 장면 $SCENE_NUM: $AUDIO_URL"
  fi
  
  SCENE_IDX=$((SCENE_IDX + 1))
done < /tmp/narrations_final.txt

echo ""
echo "🎥 비디오 합성 시작..."

mapfile -t IMAGE_URLS < /tmp/image_urls.txt
mapfile -t AUDIO_URLS < /tmp/audio_urls_new.txt

# JSON 생성
VIDEO_SCENES="["
for i in "${!IMAGE_URLS[@]}"; do
  SCENE_NUM=$((i + 1))
  
  if [ $i -gt 0 ]; then
    VIDEO_SCENES+=","
  fi
  
  VIDEO_SCENES+="{\"scene_number\":$SCENE_NUM,\"description\":\"우주 모험 장면 $SCENE_NUM\",\"duration\":4,\"style\":\"cinematic\",\"camera_movement\":\"static\",\"image_url\":\"${IMAGE_URLS[$i]}\",\"audio_url\":\"${AUDIO_URLS[$i]}\"}"
done
VIDEO_SCENES+="]"

VIDEO_PAYLOAD="{\"title\":\"우주를 탐험하는 용감한 고양이 나비\",\"fps\":30,\"scenes\":$VIDEO_SCENES}"

echo "$VIDEO_PAYLOAD" > /tmp/final_video_payload.json

VIDEO_RESPONSE=$(curl -s -X POST http://localhost:5003/generate-video \
  -H "Content-Type: application/json" \
  -d "$VIDEO_PAYLOAD")

echo "$VIDEO_RESPONSE" | jq '.'

VIDEO_URL=$(echo "$VIDEO_RESPONSE" | jq -r '.video_url')
VIDEO_FILENAME=$(echo "$VIDEO_RESPONSE" | jq -r '.filename')

if [ "$VIDEO_URL" != "null" ]; then
  echo ""
  echo "🎉 쇼츠 생성 완료!"
  echo "🔗 https://ai-studio.neuralgrid.kr/videos/$(python3 -c "import urllib.parse; print(urllib.parse.quote('$VIDEO_FILENAME'))")"
fi
