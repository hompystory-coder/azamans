#!/bin/bash

# YouTube Trend Analyzer - 자동 설치 및 배포 스크립트
# 실행: sudo bash setup-youtube-trend.sh

set -e

echo "================================================"
echo "YouTube Trend Analyzer - 자동 설치 시작"
echo "================================================"
echo ""

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 변수 설정
PROJECT_DIR="/home/azamans/webapp/youtube-trend-analyzer"
BACKEND_DIR="$PROJECT_DIR/backend"
FRONTEND_DIR="$PROJECT_DIR/frontend"
NGINX_CONFIG="/etc/nginx/sites-available/youtube-trend"
NGINX_ENABLED="/etc/nginx/sites-enabled/youtube-trend"
SUBDOMAIN="youtube-trend.neuralgrid.kr"

# 사용자 확인
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}이 스크립트는 root 권한으로 실행해야 합니다.${NC}"
    echo "다음과 같이 실행하세요: sudo bash setup-youtube-trend.sh"
    exit 1
fi

echo -e "${GREEN}✓ Root 권한 확인 완료${NC}"
echo ""

# 1. 프로젝트 디렉토리 확인
echo "================================================"
echo "1. 프로젝트 디렉토리 확인"
echo "================================================"

if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}✗ 프로젝트 디렉토리를 찾을 수 없습니다: $PROJECT_DIR${NC}"
    exit 1
fi

echo -e "${GREEN}✓ 프로젝트 디렉토리 확인 완료${NC}"
echo ""

# 2. Node.js 설치 확인
echo "================================================"
echo "2. Node.js 설치 확인"
echo "================================================"

if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}Node.js가 설치되어 있지 않습니다. 설치를 시작합니다...${NC}"
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

NODE_VERSION=$(node --version)
echo -e "${GREEN}✓ Node.js 버전: $NODE_VERSION${NC}"
echo ""

# 3. 백엔드 의존성 설치
echo "================================================"
echo "3. 백엔드 의존성 설치"
echo "================================================"

cd "$BACKEND_DIR"
if [ ! -d "node_modules" ]; then
    echo "npm install 실행 중..."
    sudo -u azamans npm install
    echo -e "${GREEN}✓ 백엔드 의존성 설치 완료${NC}"
else
    echo -e "${GREEN}✓ 백엔드 의존성이 이미 설치되어 있습니다${NC}"
fi
echo ""

# 4. 프론트엔드 의존성 설치
echo "================================================"
echo "4. 프론트엔드 의존성 설치"
echo "================================================"

cd "$FRONTEND_DIR"
if [ ! -d "node_modules" ]; then
    echo "npm install 실행 중..."
    sudo -u azamans npm install
    echo -e "${GREEN}✓ 프론트엔드 의존성 설치 완료${NC}"
else
    echo -e "${GREEN}✓ 프론트엔드 의존성이 이미 설치되어 있습니다${NC}"
fi
echo ""

# 5. 환경 변수 설정 확인
echo "================================================"
echo "5. 환경 변수 설정 확인"
echo "================================================"

if [ ! -f "$BACKEND_DIR/.env" ]; then
    echo -e "${YELLOW}⚠ .env 파일이 없습니다. .env.example을 복사합니다...${NC}"
    cp "$BACKEND_DIR/.env.example" "$BACKEND_DIR/.env"
    chown azamans:azamans "$BACKEND_DIR/.env"
    echo -e "${YELLOW}⚠ $BACKEND_DIR/.env 파일을 열어 YOUTUBE_API_KEY를 설정하세요!${NC}"
else
    echo -e "${GREEN}✓ .env 파일이 존재합니다${NC}"
fi
echo ""

# 6. Systemd 서비스 설치
echo "================================================"
echo "6. Systemd 서비스 설치"
echo "================================================"

# 백엔드 서비스
cp "$PROJECT_DIR/youtube-trend-backend.service" /etc/systemd/system/
echo -e "${GREEN}✓ 백엔드 서비스 파일 복사 완료${NC}"

# 프론트엔드 서비스
cp "$PROJECT_DIR/youtube-trend-frontend.service" /etc/systemd/system/
echo -e "${GREEN}✓ 프론트엔드 서비스 파일 복사 완료${NC}"

# Systemd 리로드
systemctl daemon-reload
echo -e "${GREEN}✓ Systemd 데몬 리로드 완료${NC}"
echo ""

# 7. Nginx 설정
echo "================================================"
echo "7. Nginx 설정"
echo "================================================"

# Nginx 설치 확인
if ! command -v nginx &> /dev/null; then
    echo -e "${YELLOW}Nginx가 설치되어 있지 않습니다. 설치를 시작합니다...${NC}"
    apt-get update
    apt-get install -y nginx
fi

# Nginx 설정 파일 복사
cp "$PROJECT_DIR/nginx-config.conf" "$NGINX_CONFIG"
echo -e "${GREEN}✓ Nginx 설정 파일 복사 완료${NC}"

# 심볼릭 링크 생성
if [ -L "$NGINX_ENABLED" ]; then
    rm "$NGINX_ENABLED"
fi
ln -s "$NGINX_CONFIG" "$NGINX_ENABLED"
echo -e "${GREEN}✓ Nginx 설정 활성화 완료${NC}"

# Nginx 설정 테스트
nginx -t
echo -e "${GREEN}✓ Nginx 설정 검증 완료${NC}"
echo ""

# 8. 서비스 시작
echo "================================================"
echo "8. 서비스 시작"
echo "================================================"

# 백엔드 서비스 시작
systemctl enable youtube-trend-backend
systemctl restart youtube-trend-backend
echo -e "${GREEN}✓ 백엔드 서비스 시작 완료${NC}"

# 프론트엔드 서비스 시작
systemctl enable youtube-trend-frontend
systemctl restart youtube-trend-frontend
echo -e "${GREEN}✓ 프론트엔드 서비스 시작 완료${NC}"

# Nginx 재시작
systemctl restart nginx
echo -e "${GREEN}✓ Nginx 재시작 완료${NC}"
echo ""

# 9. 방화벽 설정 (선택사항)
echo "================================================"
echo "9. 방화벽 설정 (선택사항)"
echo "================================================"

if command -v ufw &> /dev/null; then
    ufw allow 'Nginx Full'
    echo -e "${GREEN}✓ UFW 방화벽 규칙 추가 완료${NC}"
else
    echo -e "${YELLOW}⚠ UFW가 설치되어 있지 않습니다. 방화벽 설정을 건너뜁니다.${NC}"
fi
echo ""

# 10. 서비스 상태 확인
echo "================================================"
echo "10. 서비스 상태 확인"
echo "================================================"

echo ""
echo "--- 백엔드 서비스 상태 ---"
systemctl status youtube-trend-backend --no-pager -l

echo ""
echo "--- 프론트엔드 서비스 상태 ---"
systemctl status youtube-trend-frontend --no-pager -l

echo ""
echo "--- Nginx 상태 ---"
systemctl status nginx --no-pager -l

echo ""
echo "================================================"
echo "✅ 설치 완료!"
echo "================================================"
echo ""
echo -e "${GREEN}YouTube Trend Analyzer가 성공적으로 설치되었습니다!${NC}"
echo ""
echo "📌 접속 URL:"
echo "   http://$SUBDOMAIN"
echo ""
echo "⚙️  서비스 관리 명령어:"
echo "   sudo systemctl status youtube-trend-backend   # 백엔드 상태 확인"
echo "   sudo systemctl status youtube-trend-frontend  # 프론트엔드 상태 확인"
echo "   sudo systemctl restart youtube-trend-backend  # 백엔드 재시작"
echo "   sudo systemctl restart youtube-trend-frontend # 프론트엔드 재시작"
echo "   sudo systemctl stop youtube-trend-backend     # 백엔드 중지"
echo "   sudo systemctl stop youtube-trend-frontend    # 프론트엔드 중지"
echo ""
echo "📝 로그 확인:"
echo "   sudo journalctl -u youtube-trend-backend -f   # 백엔드 로그"
echo "   sudo journalctl -u youtube-trend-frontend -f  # 프론트엔드 로그"
echo "   sudo tail -f /var/log/nginx/youtube-trend-error.log  # Nginx 에러 로그"
echo ""
echo "🔐 SSL 설정 (선택사항):"
echo "   sudo certbot --nginx -d $SUBDOMAIN"
echo ""
echo -e "${YELLOW}⚠️  중요: $BACKEND_DIR/.env 파일에서 YOUTUBE_API_KEY를 설정하세요!${NC}"
echo ""
