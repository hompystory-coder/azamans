#!/bin/bash
# Nginx 설정 업데이트 스크립트

echo "🔧 Updating Nginx configuration for shorts.neuralgrid.kr..."

# 백업 생성
BACKUP_FILE="/tmp/shorts.neuralgrid.kr.backup_$(date +%Y%m%d_%H%M%S)"
if [ -f /etc/nginx/sites-available/shorts.neuralgrid.kr ]; then
    sudo cp /etc/nginx/sites-available/shorts.neuralgrid.kr "$BACKUP_FILE"
    echo "✅ Backup created: $BACKUP_FILE"
fi

# 새 설정 복사
sudo cp ~/shorts-creator-pro/nginx/shorts.neuralgrid.kr.conf /etc/nginx/sites-available/shorts.neuralgrid.kr

# 심볼릭 링크 확인
if [ ! -L /etc/nginx/sites-enabled/shorts.neuralgrid.kr ]; then
    sudo ln -s /etc/nginx/sites-available/shorts.neuralgrid.kr /etc/nginx/sites-enabled/shorts.neuralgrid.kr
    echo "✅ Symbolic link created"
fi

# Nginx 설정 테스트
echo "🧪 Testing Nginx configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    # Nginx 재시작
    echo "♻️  Reloading Nginx..."
    sudo systemctl reload nginx
    echo "✅ Nginx configuration updated successfully!"
    echo ""
    echo "📋 Configuration includes:"
    echo "   - HTTP to HTTPS redirect"
    echo "   - Frontend proxy: / → http://127.0.0.1:3006"
    echo "   - Backend API proxy: /api/ → http://127.0.0.1:4001"
    echo "   - CORS headers configured"
else
    echo "❌ Nginx configuration test failed!"
    echo "⚠️  Restoring backup..."
    sudo cp "$BACKUP_FILE" /etc/nginx/sites-available/shorts.neuralgrid.kr
    exit 1
fi
