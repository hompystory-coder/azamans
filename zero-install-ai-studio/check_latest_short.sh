#!/bin/bash

LATEST_VIDEO=$(ls -t public/videos/*.mp4 | head -1)

echo "🎬 최신 생성 쇼츠 정보"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📁 파일: $LATEST_VIDEO"
echo "📦 크기: $(du -h "$LATEST_VIDEO" | cut -f1)"
echo ""

# ffprobe로 상세 정보 확인
echo "🎥 비디오 상세:"
ffprobe -v quiet -print_format json -show_format -show_streams "$LATEST_VIDEO" | jq -r '
  "  ⏱️  재생시간: \(.format.duration // "N/A")초",
  "  🎞️  해상도: \(.streams[0].width // "N/A")x\(.streams[0].height // "N/A")",
  "  🎬 코덱: \(.streams[0].codec_name // "N/A")",
  "  🎙️  오디오: \(.streams[1].codec_name // "N/A")"
'

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# URL 생성
FILENAME=$(basename "$LATEST_VIDEO")
ENCODED_FILENAME=$(python3 -c "import urllib.parse; print(urllib.parse.quote('$FILENAME'))")
echo "🔗 다운로드 URL:"
echo "   https://ai-studio.neuralgrid.kr/videos/$ENCODED_FILENAME"
echo ""

