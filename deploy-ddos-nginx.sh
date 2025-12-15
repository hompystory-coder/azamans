#!/bin/bash

# ==============================================
# DDoS Defense System - Nginx Deployment Script
# Domain: ddos.neuralgrid.kr
# ==============================================

set -e

echo "🛡️ DDoS Defense System - Nginx 배포 시작"
echo "======================================"
echo ""

SERVER_IP="115.91.5.140"
SERVER_USER="azamans"
DOMAIN="ddos.neuralgrid.kr"
NGINX_CONF="ddos.neuralgrid.kr.nginx.conf"

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Nginx 설정 파일 업로드
echo -e "${YELLOW}[1/5]${NC} Nginx 설정 파일 업로드 중..."
sshpass -p '7009011226119' scp -o StrictHostKeyChecking=no \
    "$NGINX_CONF" \
    "${SERVER_USER}@${SERVER_IP}:/tmp/${NGINX_CONF}"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} 설정 파일 업로드 완료"
else
    echo -e "${RED}✗${NC} 설정 파일 업로드 실패"
    exit 1
fi

# 2. Nginx sites-available로 이동
echo ""
echo -e "${YELLOW}[2/5]${NC} Nginx 설정 적용 중..."
sshpass -p '7009011226119' ssh -o StrictHostKeyChecking=no \
    "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
sudo -S mv /tmp/ddos.neuralgrid.kr.nginx.conf /etc/nginx/sites-available/ddos.neuralgrid.kr.conf <<< '7009011226119'
sudo ln -sf /etc/nginx/sites-available/ddos.neuralgrid.kr.conf /etc/nginx/sites-enabled/
ENDSSH

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Nginx 설정 적용 완료"
else
    echo -e "${RED}✗${NC} Nginx 설정 적용 실패"
    exit 1
fi

# 3. Nginx 설정 테스트
echo ""
echo -e "${YELLOW}[3/5]${NC} Nginx 설정 테스트 중..."
sshpass -p '7009011226119' ssh -o StrictHostKeyChecking=no \
    "${SERVER_USER}@${SERVER_IP}" \
    "sudo -S nginx -t <<< '7009011226119'"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Nginx 설정 테스트 통과"
else
    echo -e "${RED}✗${NC} Nginx 설정 테스트 실패"
    exit 1
fi

# 4. Nginx 재시작
echo ""
echo -e "${YELLOW}[4/5]${NC} Nginx 재시작 중..."
sshpass -p '7009011226119' ssh -o StrictHostKeyChecking=no \
    "${SERVER_USER}@${SERVER_IP}" \
    "sudo -S systemctl reload nginx <<< '7009011226119'"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Nginx 재시작 완료"
else
    echo -e "${RED}✗${NC} Nginx 재시작 실패"
    exit 1
fi

# 5. DNS 전파 확인
echo ""
echo -e "${YELLOW}[5/5]${NC} DNS 전파 확인 중..."
sleep 2

DNS_RESULT=$(nslookup ${DOMAIN} 8.8.8.8 2>/dev/null | grep -A1 "Name:" | tail -1 | awk '{print $2}')

if [ -z "$DNS_RESULT" ]; then
    echo -e "${YELLOW}⚠${NC} DNS가 아직 전파되지 않았습니다"
    echo ""
    echo "다음 명령어로 DNS 전파를 확인하세요:"
    echo "  nslookup ${DOMAIN} 8.8.8.8"
    echo ""
    echo -e "${YELLOW}DNS 전파 후 다음 단계를 진행하세요:${NC}"
    echo "  1. SSH 접속: ssh ${SERVER_USER}@${SERVER_IP}"
    echo "  2. SSL 인증서 발급:"
    echo "     sudo certbot --nginx -d ${DOMAIN}"
else
    echo -e "${GREEN}✓${NC} DNS 전파 확인됨: ${DNS_RESULT}"
    echo ""
    echo "이제 SSL 인증서를 발급할 수 있습니다:"
    echo ""
    echo "  ssh ${SERVER_USER}@${SERVER_IP}"
    echo "  sudo certbot --nginx -d ${DOMAIN}"
fi

# 6. 배포 완료
echo ""
echo "======================================"
echo -e "${GREEN}✅ Nginx 배포 완료!${NC}"
echo "======================================"
echo ""
echo "📊 현재 상태:"
echo "  • Nginx 설정: ✅ 완료"
echo "  • HTTP 접속: ✅ 가능 (http://${DOMAIN})"
echo "  • HTTPS 접속: 🔄 DNS 전파 후 SSL 인증서 발급 필요"
echo ""
echo "🔗 접속 URL:"
echo "  • Dashboard: http://${DOMAIN}/"
echo "  • API Status: http://${DOMAIN}/api/status"
echo "  • Health Check: http://${DOMAIN}/health"
echo ""
echo "⏭️  다음 단계:"
echo "  1. DNS 레코드 추가 (아직 안했다면)"
echo "     Type: A, Name: ddos, IPv4: ${SERVER_IP}, Proxy: ON"
echo ""
echo "  2. DNS 전파 확인 (5-10분 소요)"
echo "     nslookup ${DOMAIN} 8.8.8.8"
echo ""
echo "  3. SSL 인증서 발급"
echo "     ssh ${SERVER_USER}@${SERVER_IP}"
echo "     sudo certbot --nginx -d ${DOMAIN}"
echo ""
echo "  4. HTTPS 접속 테스트"
echo "     curl -I https://${DOMAIN}/"
echo ""
