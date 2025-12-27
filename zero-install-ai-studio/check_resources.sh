#!/bin/bash

echo "🖥️  AI 쇼츠 생성 리소스 사용 현황"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "📍 1. 현재 서버 정보"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  호스트명: $(hostname)"
echo "  IP 주소: $(hostname -I | awk '{print $1}')"
echo "  운영체제: $(lsb_release -d | cut -f2-)"
echo "  커널: $(uname -r)"
echo ""

echo "📍 2. CPU 사용 현황"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  CPU 모델: $(lscpu | grep 'Model name' | cut -d':' -f2 | xargs)"
echo "  코어 수: $(nproc) cores"
echo "  현재 CPU 사용률:"
top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print "    " 100 - $1 "% 사용 중"}'
echo ""

echo "📍 3. 메모리 사용 현황"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
free -h | awk 'NR==1{print "    " $0} NR==2{printf "    총 메모리: %s\n    사용 중: %s\n    여유: %s\n    사용률: %.1f%%\n", $2, $3, $7, ($3/$2)*100}'
echo ""

echo "📍 4. 디스크 사용 현황"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
df -h / | awk 'NR==1{print "    " $0} NR==2{print "    " $0}'
echo ""

echo "📍 5. AI 백엔드 서비스 리소스 사용 (PM2)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
pm2 list | grep -E "ai-story-generator|ai-image-generator|ai-tts-generator|ai-video-generator|ai-music-matcher" | awk '{printf "  %-25s PID: %-8s CPU: %-6s Mem: %s\n", $2, $10, $16, $18}'
echo ""

echo "📍 6. 각 AI 서비스별 상세 리소스"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
for service in ai-story-generator ai-image-generator ai-tts-generator ai-video-generator ai-music-matcher; do
  PID=$(pm2 jlist | jq -r ".[] | select(.name==\"$service\") | .pid")
  if [ ! -z "$PID" ] && [ "$PID" != "0" ]; then
    CPU=$(ps -p $PID -o %cpu --no-headers 2>/dev/null | xargs)
    MEM=$(ps -p $PID -o %mem --no-headers 2>/dev/null | xargs)
    RSS=$(ps -p $PID -o rss --no-headers 2>/dev/null | awk '{printf "%.1f", $1/1024}')
    echo "  🔹 $service"
    echo "     PID: $PID | CPU: ${CPU}% | 메모리: ${MEM}% (${RSS}MB)"
  else
    echo "  🔹 $service: 미실행"
  fi
done
echo ""

echo "📍 7. 외부 API 사용 여부 확인"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  🎨 이미지 생성 API:"
grep -n "pollinations\|huggingface\|stability" ai-backend/image_generator.py 2>/dev/null | head -3 | sed 's/^/     /'
echo ""
echo "  🎙️  TTS 음성 생성:"
if [ -f "ai-backend/tts_generator.py" ]; then
  grep -n "gTTS\|elevenlabs\|google" ai-backend/tts_generator.py 2>/dev/null | head -3 | sed 's/^/     /'
fi
echo ""

echo "📍 8. 네트워크 외부 API 호출 확인 (최근 로그)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  최근 Pollinations.ai 호출:"
tail -50 ~/.pm2/logs/ai-image-generator-out.log 2>/dev/null | grep -i "pollinations" | tail -3 | sed 's/^/     /'
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ 요약:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  📌 모든 AI 백엔드는 이 서버($(hostname))에서 실행 중"
echo "  📌 CPU/메모리는 현재 서버의 하드웨어 리소스 사용"
echo "  📌 이미지 생성: Pollinations.ai 외부 API (무료, 서버 리소스 절약)"
echo "  📌 TTS/비디오: 로컬 서버에서 처리 (서버 CPU/메모리 사용)"
echo ""

