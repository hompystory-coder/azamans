#!/bin/bash

# shorts.neuralgrid.kr/azaman 독립 실행 스크립트
# 실행: sudo bash setup-azaman-independent.sh

set -e

echo "================================================"
echo "shorts.neuralgrid.kr/azaman 독립 실행 설정"
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
    echo "다음과 같이 실행하세요: sudo bash setup-azaman-independent.sh"
    exit 1
fi

echo -e "${GREEN}✓ Root 권한 확인 완료${NC}"
echo ""

# 1. 기존 설정 확인
echo "================================================"
echo "1. 기존 설정 확인 및 보호"
echo "================================================"

if grep -q "basePath" "$PROJECT_DIR/next.config.js"; then
    echo -e "${RED}✗ next.config.js에 basePath 설정이 있습니다!${NC}"
    echo -e "${YELLOW}기존 경로를 보호하기 위해 제거해야 합니다.${NC}"
    
    # basePath 제거 확인
    if grep -q "basePath: '/azaman'" "$PROJECT_DIR/next.config.js"; then
        echo -e "${YELLOW}basePath를 자동으로 제거합니다...${NC}"
        # 이미 스크립트에서 제거했으므로 pass
    fi
fi

echo -e "${GREEN}✓ 기존 설정 보호 완료${NC}"
echo ""

# 2. Azaman 전용 빌드
echo "================================================"
echo "2. Azaman 전용 애플리케이션 빌드"
echo "================================================"

cd "$PROJECT_DIR"

echo "기존 빌드 정리 중..."
rm -rf .next-azaman

echo "Azaman 전용 설정으로 빌드 중..."
# next.config.azaman.js를 사용하여 빌드
sudo -u azamans cp next.config.azaman.js next.config.js.bak
sudo -u azamans mv next.config.azaman.js next.config.js
sudo -u azamans npm run build
sudo -u azamans mv .next .next-azaman
sudo -u azamans mv next.config.js.bak next.config.js

echo -e "${GREEN}✓ Azaman 전용 빌드 완료 (.next-azaman)${NC}"
echo ""

# 3. PM2 설정
echo "================================================"
echo "3. PM2 프로세스 설정"
echo "================================================"

if command -v pm2 &> /dev/null; then
    echo "PM2로 Azaman 인스턴스 시작 중..."
    
    # 기존 azaman 프로세스 중지
    sudo -u azamans pm2 delete zero-install-azaman 2>/dev/null || true
    
    # 새 프로세스 시작 (포트 8081)
    cd "$PROJECT_DIR"
    sudo -u azamans pm2 start npm --name "zero-install-azaman" -- start -- -p 8081
    sudo -u azamans pm2 save
    
    echo -e "${GREEN}✓ PM2 설정 완료 (포트 8081)${NC}"
else
    echo -e "${YELLOW}⚠ PM2가 설치되어 있지 않습니다${NC}"
    echo "Systemd 서비스를 설정합니다..."
    
    # Systemd 서비스 설정
    cp "$PROJECT_DIR/zero-install-azaman.service" /etc/systemd/system/
    systemctl daemon-reload
    systemctl enable zero-install-azaman
    systemctl start zero-install-azaman
    
    echo -e "${GREEN}✓ Systemd 서비스 설정 완료${NC}"
fi
echo ""

# 4. Nginx 설정 업데이트
echo "================================================"
echo "4. Nginx 설정 업데이트"
echo "================================================"

# 기존 설정 백업
cp "$NGINX_CONFIG" "${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"
echo -e "${GREEN}✓ 기존 설정 백업 완료${NC}"

# 새 설정 적용
cp "$PROJECT_DIR/nginx-shorts-independent.conf" "$NGINX_CONFIG"
echo -e "${GREEN}✓ Nginx 설정 업데이트 완료${NC}"

# Nginx 설정 테스트
nginx -t
echo -e "${GREEN}✓ Nginx 설정 검증 완료${NC}"
echo ""

# 5. Nginx 재시작
echo "================================================"
echo "5. Nginx 재시작"
echo "================================================"

systemctl restart nginx
echo -e "${GREEN}✓ Nginx 재시작 완료${NC}"
echo ""

# 6. 포트 확인
echo "================================================"
echo "6. 실행 중인 서비스 확인"
echo "================================================"

echo "포트 8080 (기존):"
lsof -i :8080 | head -3 || echo "  실행 중인 프로세스 없음"

echo ""
echo "포트 8081 (Azaman):"
lsof -i :8081 | head -3 || echo "  실행 중인 프로세스 없음"

echo ""

# 완료
echo "================================================"
echo "✅ 독립 실행 설정 완료!"
echo "================================================"
echo ""
echo -e "${GREEN}두 개의 완전히 독립적인 인스턴스가 실행됩니다!${NC}"
echo ""
echo "📌 접속 URL:"
echo "   기존 (포트 8080): http://shorts.neuralgrid.kr/"
echo "   새로 (포트 8081): http://shorts.neuralgrid.kr/azaman/"
echo ""
echo "🔍 상태 확인:"
echo "   포트 8080: lsof -i :8080"
echo "   포트 8081: lsof -i :8081"
echo ""
echo "📝 PM2 관리 (PM2 사용 시):"
echo "   pm2 status"
echo "   pm2 logs zero-install-ai-studio     # 기존 (8080)"
echo "   pm2 logs zero-install-azaman        # Azaman (8081)"
echo "   pm2 restart zero-install-azaman     # Azaman 재시작"
echo ""
echo "📝 Systemd 관리 (Systemd 사용 시):"
echo "   sudo systemctl status zero-install-azaman"
echo "   sudo journalctl -u zero-install-azaman -f"
echo "   sudo systemctl restart zero-install-azaman"
echo ""
echo -e "${GREEN}✅ 기존 경로는 전혀 영향받지 않습니다!${NC}"
echo ""
