#!/bin/bash

# NeuralGrid DDoS Backend Deployment Script
# 이 스크립트는 업데이트된 백엔드를 프로덕션에 배포합니다.

set -e  # 에러 발생 시 즉시 중단

echo "🚀 NeuralGrid DDoS Backend Deployment"
echo "======================================"
echo ""

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 배포 디렉토리
SOURCE_FILE="/home/azamans/webapp/ddos-server-updated.js"
DEST_DIR="/var/www/ddos.neuralgrid.kr"
DEST_FILE="$DEST_DIR/server.js"
BACKUP_FILE="$DEST_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# 1. 소스 파일 존재 확인
echo "📁 Step 1: Checking source file..."
if [ ! -f "$SOURCE_FILE" ]; then
    echo -e "${RED}❌ Error: Source file not found: $SOURCE_FILE${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Source file found${NC}"
echo ""

# 2. 대상 디렉토리 존재 확인
echo "📂 Step 2: Checking destination directory..."
if [ ! -d "$DEST_DIR" ]; then
    echo -e "${RED}❌ Error: Destination directory not found: $DEST_DIR${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Destination directory exists${NC}"
echo ""

# 3. 기존 파일 백업
echo "💾 Step 3: Creating backup..."
if [ -f "$DEST_FILE" ]; then
    cp "$DEST_FILE" "$BACKUP_FILE"
    echo -e "${GREEN}✅ Backup created: $BACKUP_FILE${NC}"
else
    echo -e "${YELLOW}⚠️  No existing file to backup${NC}"
fi
echo ""

# 4. 파일 복사 (sudo 필요)
echo "📦 Step 4: Deploying new file..."
echo -e "${YELLOW}⚠️  This step requires sudo password${NC}"
sudo cp "$SOURCE_FILE" "$DEST_FILE"
echo -e "${GREEN}✅ File deployed${NC}"
echo ""

# 5. 권한 설정
echo "🔐 Step 5: Setting permissions..."
sudo chown www-data:www-data "$DEST_FILE"
sudo chmod 644 "$DEST_FILE"
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

# 6. PM2 서비스 재시작
echo "🔄 Step 6: Restarting PM2 service..."
pm2 restart ddos-security
echo -e "${GREEN}✅ Service restarted${NC}"
echo ""

# 7. 서비스 상태 확인
echo "📊 Step 7: Checking service status..."
pm2 status ddos-security
echo ""

# 8. 로그 확인 (마지막 20줄)
echo "📝 Step 8: Checking recent logs..."
echo -e "${YELLOW}Last 20 log lines:${NC}"
pm2 logs ddos-security --lines 20 --nostream
echo ""

# 완료
echo "======================================"
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo "📍 Deployed file: $DEST_FILE"
echo "💾 Backup file: $BACKUP_FILE"
echo ""
echo "🧪 Next steps:"
echo "  1. Test API: curl -I https://ddos.neuralgrid.kr/api/user/stats"
echo "  2. Test registration flow"
echo "  3. Verify server list on My Page"
echo ""
echo "🔙 Rollback if needed:"
echo "  sudo cp $BACKUP_FILE $DEST_FILE"
echo "  pm2 restart ddos-security"
echo ""
