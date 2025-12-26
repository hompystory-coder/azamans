#!/bin/bash

# Zero-Install AI Studio 배포 스크립트
# Domain: ai-studio.neuralgrid.kr

set -e  # 에러 발생 시 중단

echo "🚀 Zero-Install AI Studio 배포 시작..."
echo "📍 Domain: ai-studio.neuralgrid.kr"
echo ""

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 현재 디렉토리 확인
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo -e "${BLUE}📂 작업 디렉토리: $SCRIPT_DIR${NC}"
echo ""

# 1. DNS 확인
echo -e "${YELLOW}1️⃣  DNS 설정 확인...${NC}"
DNS_IP=$(dig +short ai-studio.neuralgrid.kr | head -1)
if [ -z "$DNS_IP" ]; then
    echo -e "${RED}❌ DNS가 아직 전파되지 않았습니다.${NC}"
    echo -e "${YELLOW}   DNS 설정을 확인하고 전파를 기다려주세요 (최대 24시간)${NC}"
    echo -e "${YELLOW}   현재는 HTTP 포트 80 설정만 진행합니다.${NC}"
    DNS_READY=false
else
    echo -e "${GREEN}✅ DNS 확인됨: $DNS_IP${NC}"
    DNS_READY=true
fi
echo ""

# 2. Nginx 설정
echo -e "${YELLOW}2️⃣  Nginx 설정 중...${NC}"

# Nginx 설정 파일 복사 (HTTP 전용)
sudo tee /etc/nginx/sites-available/ai-studio-neuralgrid > /dev/null << 'EOF'
# HTTP Only Configuration (SSL 설정 전)
server {
    listen 80;
    listen [::]:80;
    server_name ai-studio.neuralgrid.kr;

    # Let's Encrypt 인증서 발급을 위한 경로
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }

    # 로그 설정
    access_log /var/log/nginx/ai-studio.access.log;
    error_log /var/log/nginx/ai-studio.error.log;

    # 클라이언트 업로드 크기 제한
    client_max_body_size 100M;

    # Proxy to Next.js
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        proxy_cache_bypass $http_upgrade;
        
        proxy_connect_timeout 600s;
        proxy_send_timeout 600s;
        proxy_read_timeout 600s;
    }

    # Static files caching
    location /_next/static {
        proxy_pass http://localhost:3000;
        add_header Cache-Control "public, max-age=31536000, immutable";
    }

    location ~* \.(jpg|jpeg|png|gif|ico|svg|webp)$ {
        proxy_pass http://localhost:3000;
        add_header Cache-Control "public, max-age=604800";
    }

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css text/javascript application/json application/javascript;
}
EOF

echo -e "${GREEN}✅ Nginx 설정 파일 생성 완료${NC}"

# Symbolic link 생성
sudo ln -sf /etc/nginx/sites-available/ai-studio-neuralgrid /etc/nginx/sites-enabled/

# Nginx 설정 테스트
echo -e "${YELLOW}   Nginx 설정 테스트...${NC}"
if sudo nginx -t; then
    echo -e "${GREEN}✅ Nginx 설정 검증 통과${NC}"
    sudo systemctl reload nginx
    echo -e "${GREEN}✅ Nginx 재시작 완료${NC}"
else
    echo -e "${RED}❌ Nginx 설정 오류${NC}"
    exit 1
fi
echo ""

# 3. 로그 디렉토리 생성
echo -e "${YELLOW}3️⃣  로그 디렉토리 생성...${NC}"
mkdir -p logs
echo -e "${GREEN}✅ 로그 디렉토리 준비 완료${NC}"
echo ""

# 4. 프로덕션 빌드
echo -e "${YELLOW}4️⃣  프로덕션 빌드...${NC}"
if [ -d ".next" ]; then
    echo -e "${BLUE}   기존 빌드 발견, 재사용합니다.${NC}"
else
    echo -e "${YELLOW}   빌드 중... (약 1-2분 소요)${NC}"
    npm run build
    echo -e "${GREEN}✅ 빌드 완료${NC}"
fi
echo ""

# 5. PM2로 앱 시작
echo -e "${YELLOW}5️⃣  PM2로 애플리케이션 시작...${NC}"

# PM2가 설치되어 있는지 확인
if ! command -v pm2 &> /dev/null; then
    echo -e "${YELLOW}   PM2가 설치되지 않았습니다. 설치 중...${NC}"
    sudo npm install -g pm2
fi

# 기존 인스턴스 중지
pm2 delete ai-studio 2>/dev/null || true

# PM2로 시작
pm2 start ecosystem.config.json

# PM2 상태 확인
pm2 status

# 부팅 시 자동 시작 설정
pm2 startup systemd -u $USER --hp $HOME 2>/dev/null || true
pm2 save

echo -e "${GREEN}✅ PM2로 애플리케이션 시작 완료${NC}"
echo ""

# 6. 상태 확인
echo -e "${YELLOW}6️⃣  서비스 상태 확인...${NC}"
sleep 3

if pm2 list | grep -q "ai-studio.*online"; then
    echo -e "${GREEN}✅ 애플리케이션이 정상 실행 중입니다${NC}"
else
    echo -e "${RED}❌ 애플리케이션 시작 실패${NC}"
    pm2 logs ai-studio --lines 20
    exit 1
fi
echo ""

# 7. SSL 설정 (DNS가 준비된 경우)
if [ "$DNS_READY" = true ]; then
    echo -e "${YELLOW}7️⃣  SSL 인증서 발급 (Let's Encrypt)...${NC}"
    
    # Certbot 설치 확인
    if ! command -v certbot &> /dev/null; then
        echo -e "${YELLOW}   Certbot 설치 중...${NC}"
        sudo apt-get update
        sudo apt-get install -y certbot python3-certbot-nginx
    fi
    
    # SSL 인증서 발급
    echo -e "${YELLOW}   인증서 발급 중...${NC}"
    sudo certbot --nginx -d ai-studio.neuralgrid.kr --non-interactive --agree-tos --email admin@neuralgrid.kr --redirect
    
    echo -e "${GREEN}✅ SSL 인증서 발급 완료${NC}"
    echo -e "${GREEN}✅ HTTPS 리다이렉트 설정 완료${NC}"
else
    echo -e "${YELLOW}7️⃣  SSL 인증서 발급 건너뜀 (DNS 대기 중)${NC}"
    echo -e "${YELLOW}   DNS 전파 후 다음 명령어로 SSL을 설정하세요:${NC}"
    echo -e "${BLUE}   sudo certbot --nginx -d ai-studio.neuralgrid.kr${NC}"
fi
echo ""

# 최종 결과
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🎉 배포 완료!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${BLUE}📍 접속 주소:${NC}"
if [ "$DNS_READY" = true ]; then
    echo -e "   ${GREEN}https://ai-studio.neuralgrid.kr${NC}"
else
    echo -e "   ${YELLOW}http://ai-studio.neuralgrid.kr${NC} (DNS 전파 대기 중)"
    echo -e "   ${GREEN}http://115.91.5.140:3000${NC} (직접 접속 가능)"
fi
echo ""
echo -e "${BLUE}📊 유용한 명령어:${NC}"
echo -e "   ${YELLOW}pm2 status${NC}              - 앱 상태 확인"
echo -e "   ${YELLOW}pm2 logs ai-studio${NC}      - 로그 확인"
echo -e "   ${YELLOW}pm2 restart ai-studio${NC}   - 앱 재시작"
echo -e "   ${YELLOW}pm2 stop ai-studio${NC}      - 앱 중지"
echo -e "   ${YELLOW}pm2 monit${NC}               - 실시간 모니터링"
echo ""
echo -e "   ${YELLOW}sudo nginx -t${NC}           - Nginx 설정 검증"
echo -e "   ${YELLOW}sudo systemctl status nginx${NC} - Nginx 상태"
echo -e "   ${YELLOW}sudo tail -f /var/log/nginx/ai-studio.access.log${NC} - Nginx 로그"
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
