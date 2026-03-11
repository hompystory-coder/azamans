#!/bin/bash

# Shorts Azaman 경로 최종 배포 스크립트
# https://shorts.neuralgrid.kr/azaman/ 독립 인스턴스 설정
# 기존 https://shorts.neuralgrid.kr/ 절대 변경하지 않음

set -e

echo "🚀 Shorts Azaman 독립 인스턴스 배포 시작..."

# 현재 디렉토리 확인
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 1. Nginx 설정 백업
echo "📦 Nginx 설정 백업 중..."
if [ -f /etc/nginx/sites-available/shorts.neuralgrid.kr ]; then
    sudo cp /etc/nginx/sites-available/shorts.neuralgrid.kr \
         /etc/nginx/sites-available/shorts.neuralgrid.kr.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ 백업 완료"
fi

# 2. 새 Nginx 설정 복사
echo "📝 Nginx 설정 업데이트 중..."
sudo cp nginx-shorts-azaman-final.conf /etc/nginx/sites-available/shorts.neuralgrid.kr

# 3. Nginx 설정 테스트
echo "🔍 Nginx 설정 검증 중..."
if sudo nginx -t; then
    echo "✅ Nginx 설정 검증 완료"
else
    echo "❌ Nginx 설정 오류! 백업에서 복구 중..."
    sudo cp /etc/nginx/sites-available/shorts.neuralgrid.kr.backup.* \
         /etc/nginx/sites-available/shorts.neuralgrid.kr
    sudo nginx -t
    exit 1
fi

# 4. Nginx 재시작
echo "🔄 Nginx 재시작 중..."
sudo systemctl reload nginx
echo "✅ Nginx 재시작 완료"

# 5. PM2 프로세스 확인
echo "🔍 PM2 프로세스 확인 중..."
pm2 list

# 6. 포트 확인
echo "🔍 포트 확인 중..."
echo "포트 3000 (Azaman):"
lsof -i :3000 || echo "⚠️ 포트 3000 사용 안 함"
echo ""
echo "포트 3006 (기존 서비스):"
lsof -i :3006 || echo "⚠️ 포트 3006 사용 안 함"

# 7. 접속 테스트
echo ""
echo "🧪 접속 테스트 중..."
echo "기존 경로:"
curl -I https://shorts.neuralgrid.kr/ 2>&1 | head -5
echo ""
echo "Azaman 경로:"
curl -I https://shorts.neuralgrid.kr/azaman/ 2>&1 | head -5

echo ""
echo "✅ 배포 완료!"
echo ""
echo "📍 접속 URL:"
echo "  - 기존 서비스: https://shorts.neuralgrid.kr/"
echo "  - Azaman 서비스: https://shorts.neuralgrid.kr/azaman/"
echo ""
echo "📊 PM2 관리 명령어:"
echo "  pm2 status"
echo "  pm2 logs zero-install-azaman"
echo "  pm2 restart zero-install-azaman"
echo ""
echo "🔒 중요: 기존 https://shorts.neuralgrid.kr/ 서비스는 변경되지 않았습니다!"
