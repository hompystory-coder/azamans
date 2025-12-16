#!/bin/bash

# 🚀 Automated Deployment Script for DDoS Security
# This script can be run directly on the production server

set -e  # Exit on any error

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║          🚀 DDoS Security Auto Deployment v2.0              ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
WEBAPP_DIR="/home/azamans/webapp"
PROD_DIR="/var/www/ddos.neuralgrid.kr"
PROD_FILE="$PROD_DIR/server.js"
SOURCE_FILE="$WEBAPP_DIR/ddos-server-updated.js"
BACKUP_DIR="$PROD_DIR/backups"
PM2_SERVICE="ddos-security"

# Check if running on correct server
if [ ! -d "$WEBAPP_DIR" ]; then
    echo -e "${RED}❌ Error: $WEBAPP_DIR not found${NC}"
    echo "   This script must be run on azamans@115.91.5.140"
    exit 1
fi

# Print current location
echo -e "${BLUE}📍 Current directory: $(pwd)${NC}"
echo -e "${BLUE}📂 Webapp directory: $WEBAPP_DIR${NC}"
echo -e "${BLUE}🎯 Production file: $PROD_FILE${NC}"
echo ""

# Step 1: Navigate to webapp directory
echo -e "${YELLOW}📁 Step 1: Navigating to webapp directory...${NC}"
cd "$WEBAPP_DIR" || exit 1
echo -e "${GREEN}✅ Changed to: $(pwd)${NC}"
echo ""

# Step 2: Pull latest code
echo -e "${YELLOW}📥 Step 2: Pulling latest code from GitHub...${NC}"
git fetch origin genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Code pulled successfully${NC}"
else
    echo -e "${RED}❌ Failed to pull code${NC}"
    exit 1
fi
echo ""

# Step 3: Check if source file exists
echo -e "${YELLOW}🔍 Step 3: Verifying source file...${NC}"
if [ ! -f "$SOURCE_FILE" ]; then
    echo -e "${RED}❌ Source file not found: $SOURCE_FILE${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Source file found${NC}"
echo ""

# Step 4: Create backup directory
echo -e "${YELLOW}📁 Step 4: Creating backup directory...${NC}"
sudo mkdir -p "$BACKUP_DIR"
sudo chown azamans:azamans "$BACKUP_DIR"
echo -e "${GREEN}✅ Backup directory ready${NC}"
echo ""

# Step 5: Backup current production file
BACKUP_FILE="$BACKUP_DIR/server.js.backup.$(date +%Y%m%d_%H%M%S)"
echo -e "${YELLOW}💾 Step 5: Creating backup...${NC}"
echo -e "   Backup location: $BACKUP_FILE"
sudo cp "$PROD_FILE" "$BACKUP_FILE"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Backup created successfully${NC}"
else
    echo -e "${RED}❌ Failed to create backup${NC}"
    exit 1
fi
echo ""

# Step 6: Deploy new file
echo -e "${YELLOW}🚀 Step 6: Deploying updated code...${NC}"
sudo cp "$SOURCE_FILE" "$PROD_FILE"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ File deployed successfully${NC}"
else
    echo -e "${RED}❌ Deployment failed${NC}"
    echo -e "${YELLOW}   Rolling back...${NC}"
    sudo cp "$BACKUP_FILE" "$PROD_FILE"
    exit 1
fi
echo ""

# Step 7: Fix permissions
echo -e "${YELLOW}🔐 Step 7: Setting file permissions...${NC}"
sudo chown www-data:www-data "$PROD_FILE"
sudo chmod 644 "$PROD_FILE"
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

# Step 8: Fix /var/lib/neuralgrid directory
echo -e "${YELLOW}📁 Step 8: Fixing /var/lib/neuralgrid directory...${NC}"
sudo mkdir -p /var/lib/neuralgrid
sudo chown www-data:www-data /var/lib/neuralgrid
sudo chmod 755 /var/lib/neuralgrid
echo -e "${GREEN}✅ Directory permissions fixed${NC}"
echo ""

# Step 9: Restart PM2 service
echo -e "${YELLOW}🔄 Step 9: Restarting PM2 service...${NC}"
pm2 restart "$PM2_SERVICE"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Service restarted successfully${NC}"
else
    echo -e "${RED}❌ Failed to restart service${NC}"
    echo -e "${YELLOW}   Rolling back...${NC}"
    sudo cp "$BACKUP_FILE" "$PROD_FILE"
    pm2 restart "$PM2_SERVICE"
    exit 1
fi
echo ""

# Step 10: Wait for service to start
echo -e "${YELLOW}⏳ Step 10: Waiting for service to start (3 seconds)...${NC}"
sleep 3
echo -e "${GREEN}✅ Service should be running now${NC}"
echo ""

# Step 11: Check service status
echo -e "${YELLOW}✅ Step 11: Checking service status...${NC}"
pm2 status "$PM2_SERVICE" | tail -5
echo ""

# Step 12: Show recent logs
echo -e "${YELLOW}📋 Step 12: Recent logs (last 30 lines):${NC}"
echo "════════════════════════════════════════════════════════════════"
pm2 logs "$PM2_SERVICE" --lines 30 --nostream
echo "════════════════════════════════════════════════════════════════"
echo ""

# Success summary
echo "╔══════════════════════════════════════════════════════════════╗"
echo -e "║  ${GREEN}✅ DEPLOYMENT COMPLETED SUCCESSFULLY!${NC}                   ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
echo -e "${BLUE}📊 Deployment Summary:${NC}"
echo "   • Source: $SOURCE_FILE"
echo "   • Target: $PROD_FILE"
echo "   • Backup: $BACKUP_FILE"
echo "   • Service: $PM2_SERVICE (restarted)"
echo ""
echo -e "${YELLOW}🧪 Next Steps:${NC}"
echo ""
echo "1. Test Authentication:"
echo "   • Login: https://auth.neuralgrid.kr/"
echo "   • Email: aze7009011@gate.com"
echo ""
echo "2. Test Registration:"
echo "   • URL: https://ddos.neuralgrid.kr/register.html"
echo "   • Fill form and submit"
echo "   • Check Network tab for /api/servers/register-website"
echo ""
echo "3. Monitor Logs:"
echo "   pm2 logs $PM2_SERVICE"
echo ""
echo "4. Look for these success messages:"
echo "   [Auth] 🔍 Verifying token..."
echo "   [Auth] Response status: 200"
echo "   [Auth] ✅ Token valid for user: xxx@xxx.com"
echo ""
echo -e "${BLUE}🔙 Rollback Command (if needed):${NC}"
echo "   sudo cp $BACKUP_FILE $PROD_FILE"
echo "   pm2 restart $PM2_SERVICE"
echo ""
echo -e "${GREEN}🎉 Deployment completed at: $(date)${NC}"
echo ""
