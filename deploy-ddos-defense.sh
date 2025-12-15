#!/bin/bash

# ============================================
# NeuralGrid DDoS Defense System 전체 배포
# ============================================

set -e

SERVER="115.91.5.140"
USER="azamans"
PASSWORD="7009011226119"
SUBDOMAIN="defense.neuralgrid.kr"
PORT="3105"

echo "=========================================="
echo "🛡️ NeuralGrid DDoS Defense System 배포"
echo "=========================================="

echo ""
echo "📦 준비 작업..."
chmod +x fail2ban-setup.sh

echo ""
echo "=========================================="
echo "1단계: Fail2ban 설치 및 설정"
echo "=========================================="
./fail2ban-setup.sh

echo ""
echo "=========================================="
echo "2단계: Nginx Rate Limiting 설정"
echo "=========================================="

sshpass -p "$PASSWORD" scp -o StrictHostKeyChecking=no nginx-rate-limiting.conf ${USER}@${SERVER}:/tmp/

sshpass -p "$PASSWORD" ssh -tt -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'
echo '7009011226119' | sudo -S mv /tmp/nginx-rate-limiting.conf /etc/nginx/conf.d/rate-limiting.conf
echo '7009011226119' | sudo -S nginx -t
echo '7009011226119' | sudo -S systemctl reload nginx
echo "✅ Nginx Rate Limiting 설정 완료"
exit
ENDSSH

echo ""
echo "=========================================="
echo "3단계: DDoS Defense 서버 배포"
echo "=========================================="

# 서버 디렉토리 생성
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} "mkdir -p /home/azamans/ddos-defense"

# 파일 업로드
sshpass -p "$PASSWORD" scp -o StrictHostKeyChecking=no ddos-defense-server.js ${USER}@${SERVER}:/home/azamans/ddos-defense/
sshpass -p "$PASSWORD" scp -o StrictHostKeyChecking=no ddos-dashboard.html ${USER}@${SERVER}:/home/azamans/ddos-defense/

# Node.js 의존성 설치 및 서비스 시작
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'
cd /home/azamans/ddos-defense

# package.json 생성
cat > package.json << 'EOF'
{
  "name": "ddos-defense-dashboard",
  "version": "1.0.0",
  "description": "NeuralGrid DDoS Defense Dashboard",
  "main": "ddos-defense-server.js",
  "scripts": {
    "start": "node ddos-defense-server.js"
  },
  "dependencies": {
    "express": "^4.18.2"
  }
}
EOF

# npm 설치
npm install

# PM2로 서비스 시작
pm2 delete ddos-defense 2>/dev/null || true
pm2 start ddos-defense-server.js --name ddos-defense --watch
pm2 save

echo "✅ DDoS Defense 서버 시작 완료"
ENDSSH

echo ""
echo "=========================================="
echo "4단계: Nginx 프록시 설정"
echo "=========================================="

# defense.neuralgrid.kr Nginx 설정 생성
cat > /tmp/defense.neuralgrid.kr.conf << EOF
server {
    listen 80;
    server_name ${SUBDOMAIN};

    # Logging
    access_log /var/log/nginx/${SUBDOMAIN}.access.log ddos_defense;
    error_log /var/log/nginx/${SUBDOMAIN}.error.log;

    # Rate Limiting (대시보드 보호)
    limit_req zone=api burst=30 nodelay;
    limit_conn conn_limit 10;

    # Reverse Proxy
    location / {
        proxy_pass http://localhost:${PORT};
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
    }
}
EOF

# 서버로 업로드 및 적용
sshpass -p "$PASSWORD" scp -o StrictHostKeyChecking=no /tmp/defense.neuralgrid.kr.conf ${USER}@${SERVER}:/tmp/

sshpass -p "$PASSWORD" ssh -tt -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'
echo '7009011226119' | sudo -S mv /tmp/defense.neuralgrid.kr.conf /etc/nginx/sites-available/defense.neuralgrid.kr
echo '7009011226119' | sudo -S ln -sf /etc/nginx/sites-available/defense.neuralgrid.kr /etc/nginx/sites-enabled/defense.neuralgrid.kr
echo '7009011226119' | sudo -S nginx -t
echo '7009011226119' | sudo -S systemctl reload nginx
echo "✅ Nginx 프록시 설정 완료"
exit
ENDSSH

echo ""
echo "=========================================="
echo "5단계: 방화벽 규칙 강화"
echo "=========================================="

sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'
# UFW 방화벽 규칙 추가
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw --force enable
echo "✅ 방화벽 규칙 설정 완료"
ENDSSH

echo ""
echo "=========================================="
echo "6단계: 배포 확인"
echo "=========================================="

sleep 3

# HTTP 테스트
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://${SUBDOMAIN}/ 2>/dev/null || echo "000")
echo "HTTP 상태: $HTTP_STATUS"

# API 테스트
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://${SUBDOMAIN}/api/status 2>/dev/null || echo "000")
echo "API 상태: $API_STATUS"

# PM2 상태
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} "pm2 list | grep ddos-defense"

echo ""
echo "=========================================="
echo "🎉 DDoS Defense System 배포 완료!"
echo "=========================================="
echo ""
echo "📊 서비스 정보:"
echo "  - 대시보드: http://${SUBDOMAIN}"
echo "  - API: http://${SUBDOMAIN}/api"
echo "  - 백엔드 포트: ${PORT}"
echo ""
echo "🔧 주요 기능:"
echo "  ✅ Nginx Rate Limiting (10 req/s)"
echo "  ✅ Fail2ban 자동 차단"
echo "  ✅ 실시간 모니터링 대시보드"
echo "  ✅ IP 화이트리스트/블랙리스트"
echo "  ✅ 자동 공격 탐지 및 차단"
echo ""
echo "📝 다음 단계:"
echo "  1. DNS 레코드 추가 (Cloudflare)"
echo "     Type: A, Name: defense, IPv4: ${SERVER}, Proxy: ON"
echo ""
echo "  2. SSL 인증서 설정"
echo "     ssh ${USER}@${SERVER}"
echo "     sudo certbot --nginx -d ${SUBDOMAIN}"
echo ""
echo "  3. 대시보드 접속"
echo "     https://${SUBDOMAIN}"
echo ""
echo "=========================================="
