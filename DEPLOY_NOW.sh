#!/bin/bash

###############################################################################
# NeuralGrid DDoS Security - 긴급 배포 스크립트
# 
# 이 스크립트는 로그인 리다이렉트 문제를 해결하는 업데이트를 배포합니다.
# 
# 사용법:
#   chmod +x DEPLOY_NOW.sh
#   ./DEPLOY_NOW.sh
#
# 작성일: 2025-12-16
# 작성자: GenSpark AI Developer
###############################################################################

set -e  # 에러 발생 시 즉시 중단

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 환경 변수
WORK_DIR="/home/azamans/webapp"
PROD_DIR="/var/www/ddos.neuralgrid.kr"
SERVICE_NAME="ddos-security"

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════╗"
echo "║  NeuralGrid DDoS Security - 긴급 배포               ║"
echo "║  Auth 문제 해결 업데이트                            ║"
echo "╚═══════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Step 1: 현재 디렉토리 확인
echo -e "${YELLOW}📁 Step 1: 작업 디렉토리 확인...${NC}"
cd "$WORK_DIR"
pwd
echo ""

# Step 2: 최신 코드 가져오기
echo -e "${YELLOW}🔄 Step 2: Git pull...${NC}"
git pull origin genspark_ai_developer_clean
echo ""

# Step 3: 파일 존재 확인
echo -e "${YELLOW}✓ Step 3: 파일 확인...${NC}"
if [ ! -f "ddos-server-updated.js" ]; then
    echo -e "${RED}❌ Error: ddos-server-updated.js not found${NC}"
    exit 1
fi
echo -e "${GREEN}✅ ddos-server-updated.js found${NC}"
echo ""

# Step 4: 백업 생성
echo -e "${YELLOW}💾 Step 4: 백업 생성...${NC}"
BACKUP_FILE="$PROD_DIR/server.js.backup.$(date +%Y%m%d_%H%M%S)"
echo "Backup: $BACKUP_FILE"
sudo cp "$PROD_DIR/server.js" "$BACKUP_FILE"
echo -e "${GREEN}✅ Backup created${NC}"
echo ""

# Step 5: 새 파일 배포
echo -e "${YELLOW}🚀 Step 5: 파일 배포...${NC}"
sudo cp ddos-server-updated.js "$PROD_DIR/server.js"
echo -e "${GREEN}✅ File deployed${NC}"
echo ""

# Step 6: 권한 설정
echo -e "${YELLOW}🔐 Step 6: 권한 설정...${NC}"
sudo chown www-data:www-data "$PROD_DIR/server.js"
sudo chmod 644 "$PROD_DIR/server.js"
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

# Step 7: PM2 재시작
echo -e "${YELLOW}🔄 Step 7: 서비스 재시작...${NC}"
pm2 restart $SERVICE_NAME
sleep 2
echo ""

# Step 8: 서비스 상태 확인
echo -e "${YELLOW}📊 Step 8: 서비스 상태...${NC}"
pm2 status $SERVICE_NAME
echo ""

# Step 9: 최근 로그 확인
echo -e "${YELLOW}📝 Step 9: 최근 로그 (20줄)...${NC}"
pm2 logs $SERVICE_NAME --lines 20 --nostream
echo ""

# 완료
echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════╗"
echo "║  ✅ 배포 완료!                                        ║"
echo "╚═══════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${GREEN}배포 정보:${NC}"
echo "  • 배포 파일: $PROD_DIR/server.js"
echo "  • 백업 파일: $BACKUP_FILE"
echo "  • 서비스: $SERVICE_NAME"
echo ""

echo -e "${YELLOW}🧪 테스트 절차:${NC}"
echo "  1. 브라우저 열기: https://auth.neuralgrid.kr/"
echo "  2. 로그인 (aze7009011@gate.com)"
echo "  3. https://ddos.neuralgrid.kr/register.html 접속"
echo "  4. F12 → Network 탭 열기"
echo "  5. 홈페이지 보호 신청"
echo "  6. POST /api/servers/register-website → 200 OK 확인"
echo "  7. 설치 가이드 모달 표시 확인"
echo ""

echo -e "${YELLOW}📊 로그 모니터링:${NC}"
echo "  pm2 logs $SERVICE_NAME"
echo ""

echo -e "${GREEN}찾아야 할 로그:${NC}"
echo "  [Auth] 📥 Request: POST /api/servers/register-website"
echo "  [Auth] Token present: YES"
echo "  [Auth] 🔍 Verifying token..."
echo "  [Auth] Response status: 200"
echo "  [Auth] ✅ Token valid for user: xxx@gate.com"
echo "  [Auth] ✅ JWT authentication successful"
echo ""

echo -e "${RED}🔙 롤백 (문제 발생 시):${NC}"
echo "  sudo cp $BACKUP_FILE $PROD_DIR/server.js"
echo "  pm2 restart $SERVICE_NAME"
echo ""

echo -e "${BLUE}===========================================================${NC}"
echo -e "${GREEN}✅ 준비 완료! 이제 브라우저에서 테스트하세요!${NC}"
echo -e "${BLUE}===========================================================${NC}"
