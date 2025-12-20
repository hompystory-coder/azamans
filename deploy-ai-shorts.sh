#!/bin/bash

echo "🚀 AI Shorts Pro 배포 시작..."
echo "📍 도메인: ai-shorts.neuralgrid.kr"
echo ""

# 1. nginx 설정 복사
echo "📝 nginx 설정 복사 중..."
sudo cp /home/azamans/webapp/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-available/
sudo ln -sf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-enabled/

# 2. nginx 설정 테스트
echo "🔍 nginx 설정 테스트 중..."
sudo nginx -t

if [ $? -eq 0 ]; then
    # 3. nginx 재시작
    echo "🔄 nginx 재시작 중..."
    sudo systemctl reload nginx
    
    echo ""
    echo "✅ 배포 완료!"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "🌐 AI Shorts Pro 접속 정보"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "  🎬 메인 사이트:"
    echo "     https://ai-shorts.neuralgrid.kr"
    echo ""
    echo "  📡 백엔드 API:"
    echo "     https://ai-shorts.neuralgrid.kr/api/health"
    echo ""
    echo "  🔧 백엔드 포트: 5555"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "테스트 명령어:"
    echo "  curl https://ai-shorts.neuralgrid.kr/api/health"
    echo ""
else
    echo "❌ nginx 설정 오류!"
    exit 1
fi
