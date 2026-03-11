#!/bin/bash

# YouTube Trend Analyzer - SSL 인증서 설치 스크립트
# 실행: sudo bash setup-ssl.sh

set -e

echo "================================================"
echo "YouTube Trend Analyzer - SSL 설정"
echo "================================================"
echo ""

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

SUBDOMAIN="youtube-trend.neuralgrid.kr"
EMAIL="admin@neuralgrid.kr"  # 실제 이메일로 변경하세요

# Root 권한 확인
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}이 스크립트는 root 권한으로 실행해야 합니다.${NC}"
    echo "다음과 같이 실행하세요: sudo bash setup-ssl.sh"
    exit 1
fi

# Certbot 설치 확인
echo "================================================"
echo "1. Certbot 설치 확인"
echo "================================================"

if ! command -v certbot &> /dev/null; then
    echo -e "${YELLOW}Certbot이 설치되어 있지 않습니다. 설치를 시작합니다...${NC}"
    apt-get update
    apt-get install -y certbot python3-certbot-nginx
    echo -e "${GREEN}✓ Certbot 설치 완료${NC}"
else
    echo -e "${GREEN}✓ Certbot이 이미 설치되어 있습니다${NC}"
fi
echo ""

# DNS 확인
echo "================================================"
echo "2. DNS 설정 확인"
echo "================================================"

echo "서브도메인: $SUBDOMAIN"
echo "DNS 레코드가 올바르게 설정되어 있는지 확인합니다..."

if nslookup $SUBDOMAIN &> /dev/null; then
    echo -e "${GREEN}✓ DNS 레코드가 올바르게 설정되어 있습니다${NC}"
    nslookup $SUBDOMAIN | grep "Address:"
else
    echo -e "${RED}✗ DNS 레코드를 찾을 수 없습니다${NC}"
    echo ""
    echo "다음 DNS 레코드를 추가하세요:"
    echo "  Type: A"
    echo "  Name: youtube-trend"
    echo "  Value: [서버 IP 주소]"
    echo ""
    exit 1
fi
echo ""

# 서비스 확인
echo "================================================"
echo "3. 서비스 상태 확인"
echo "================================================"

if ! systemctl is-active --quiet nginx; then
    echo -e "${RED}✗ Nginx가 실행되고 있지 않습니다${NC}"
    echo "다음 명령으로 Nginx를 시작하세요:"
    echo "  sudo systemctl start nginx"
    exit 1
fi

echo -e "${GREEN}✓ Nginx가 정상 작동 중입니다${NC}"
echo ""

# SSL 인증서 발급
echo "================================================"
echo "4. SSL 인증서 발급"
echo "================================================"

echo "Certbot으로 SSL 인증서를 발급합니다..."
echo "서브도메인: $SUBDOMAIN"
echo "이메일: $EMAIL"
echo ""

certbot --nginx \
    -d $SUBDOMAIN \
    --non-interactive \
    --agree-tos \
    --email $EMAIL \
    --redirect

echo ""
echo -e "${GREEN}✓ SSL 인증서 발급 완료${NC}"
echo ""

# 자동 갱신 설정
echo "================================================"
echo "5. 자동 갱신 설정"
echo "================================================"

# 자동 갱신 테스트
if certbot renew --dry-run; then
    echo -e "${GREEN}✓ 자동 갱신 설정 완료${NC}"
else
    echo -e "${YELLOW}⚠ 자동 갱신 테스트 실패. 수동으로 확인이 필요합니다.${NC}"
fi
echo ""

# Nginx 재시작
echo "================================================"
echo "6. Nginx 재시작"
echo "================================================"

systemctl restart nginx
echo -e "${GREEN}✓ Nginx 재시작 완료${NC}"
echo ""

# 완료
echo "================================================"
echo "✅ SSL 설정 완료!"
echo "================================================"
echo ""
echo -e "${GREEN}HTTPS가 성공적으로 활성화되었습니다!${NC}"
echo ""
echo "📌 HTTPS 접속 URL:"
echo "   https://$SUBDOMAIN"
echo ""
echo "🔒 SSL 인증서 정보:"
certbot certificates
echo ""
echo "🔄 자동 갱신:"
echo "   인증서는 90일마다 자동으로 갱신됩니다."
echo "   수동 갱신: sudo certbot renew"
echo ""
echo "✅ 모든 HTTP 요청은 자동으로 HTTPS로 리다이렉트됩니다."
echo ""
