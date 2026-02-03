#!/bin/bash

# shorts.neuralgrid.kr/azaman 경로 설정 스크립트
# 실행: sudo bash setup-azaman-path.sh

set -e

echo "================================================"
echo "shorts.neuralgrid.kr/azaman 경로 설정"
echo "================================================"
echo ""

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# 변수 설정
PROJECT_DIR="/home/azamans/webapp/zero-install-ai-studio"
NGINX_CONFIG="/etc/nginx/sites-available/shorts"

# Root 권한 확인
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}이 스크립트는 root 권한으로 실행해야 합니다.${NC}"
    echo "다음과 같이 실행하세요: sudo bash setup-azaman-path.sh"
    exit 1
fi

echo -e "${GREEN}✓ Root 권한 확인 완료${NC}"
echo ""

# 1. Next.js 설정 확인
echo "================================================"
echo "1. Next.js basePath 설정 확인"
echo "================================================"

if grep -q "basePath: '/azaman'" "$PROJECT_DIR/next.config.js"; then
    echo -e "${GREEN}✓ Next.js basePath 설정이 이미 적용되어 있습니다${NC}"
else
    echo -e "${RED}✗ Next.js basePath 설정이 없습니다${NC}"
    echo "next.config.js 파일을 확인하세요."
    exit 1
fi
echo ""

# 2. Nginx 설정 업데이트
echo "================================================"
echo "2. Nginx 설정 업데이트"
echo "================================================"

# 기존 설정 백업
cp "$NGINX_CONFIG" "${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"
echo -e "${GREEN}✓ 기존 설정 백업 완료${NC}"

# 새 설정 적용
cp "$PROJECT_DIR/nginx-shorts-azaman.conf" "$NGINX_CONFIG"
echo -e "${GREEN}✓ Nginx 설정 업데이트 완료${NC}"

# Nginx 설정 테스트
nginx -t
echo -e "${GREEN}✓ Nginx 설정 검증 완료${NC}"
echo ""

# 3. Next.js 애플리케이션 재빌드
echo "================================================"
echo "3. Next.js 애플리케이션 재빌드"
echo "================================================"

cd "$PROJECT_DIR"

echo "기존 빌드 파일 정리 중..."
rm -rf .next

echo "npm install 실행 중..."
sudo -u azamans npm install

echo "Next.js 빌드 실행 중..."
sudo -u azamans npm run build

echo -e "${GREEN}✓ Next.js 빌드 완료${NC}"
echo ""

# 4. PM2 프로세스 재시작
echo "================================================"
echo "4. PM2 프로세스 재시작"
echo "================================================"

if command -v pm2 &> /dev/null; then
    echo "PM2로 애플리케이션 재시작 중..."
    sudo -u azamans pm2 restart zero-install-ai-studio || \
    sudo -u azamans pm2 restart all
    echo -e "${GREEN}✓ PM2 재시작 완료${NC}"
else
    echo -e "${YELLOW}⚠ PM2가 설치되어 있지 않습니다${NC}"
    echo "systemd 서비스를 사용 중이라면 다음 명령으로 재시작하세요:"
    echo "  sudo systemctl restart zero-install-ai-studio"
fi
echo ""

# 5. Nginx 재시작
echo "================================================"
echo "5. Nginx 재시작"
echo "================================================"

systemctl restart nginx
echo -e "${GREEN}✓ Nginx 재시작 완료${NC}"
echo ""

# 완료
echo "================================================"
echo "✅ 설정 완료!"
echo "================================================"
echo ""
echo -e "${GREEN}shorts.neuralgrid.kr/azaman 경로가 설정되었습니다!${NC}"
echo ""
echo "📌 접속 URL:"
echo "   기존: http://shorts.neuralgrid.kr/"
echo "   새로: http://shorts.neuralgrid.kr/azaman/"
echo ""
echo "🔍 테스트:"
echo "   curl -I http://shorts.neuralgrid.kr/azaman/"
echo ""
echo "📝 로그 확인:"
echo "   sudo tail -f /var/log/nginx/error.log"
echo "   sudo -u azamans pm2 logs zero-install-ai-studio"
echo ""
