#!/bin/bash

echo "🔧 Nginx 설정 업데이트 중..."
echo ""

# Backup original config
echo "📦 기존 설정 백업..."
sudo cp /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup.$(date +%Y%m%d_%H%M%S)

# Copy new config
echo "📝 새 설정 복사..."
sudo cp /home/azamans/webapp/nginx-config-update.conf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf

# Test config
echo "✅ 설정 테스트..."
if sudo nginx -t; then
    echo ""
    echo "🔄 Nginx 재시작..."
    sudo systemctl reload nginx
    
    echo ""
    echo "✨ ============================================"
    echo "✅ Nginx 설정이 성공적으로 업데이트되었습니다!"
    echo "============================================"
    echo ""
    echo "📹 이제 다음 URL에서 영상을 다운로드할 수 있습니다:"
    echo "   https://ai-shorts.neuralgrid.kr/videos/DEMO_SHORTS.mp4"
    echo ""
    echo "🧪 테스트:"
    echo "   curl -I https://ai-shorts.neuralgrid.kr/videos/DEMO_SHORTS.mp4"
    echo ""
else
    echo ""
    echo "❌ Nginx 설정 테스트 실패!"
    echo "   백업에서 복원 중..."
    sudo cp /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup.* /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf
    exit 1
fi
