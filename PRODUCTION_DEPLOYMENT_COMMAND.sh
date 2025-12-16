#!/bin/bash

# 🚀 Production Deployment Script for DDoS Security Fix
# This script must be run on the production server with sudo access

echo "============================================"
echo "🚀 DDoS Security Production Deployment"
echo "============================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as regular user (we'll use sudo)
if [ "$EUID" -eq 0 ]; then 
   echo -e "${RED}❌ Please run as regular user, not as root${NC}"
   echo "   This script will use sudo when needed"
   exit 1
fi

echo -e "${YELLOW}📍 Current directory: $(pwd)${NC}"
echo ""

# Step 1: Pull latest code
echo "📥 Step 1: Pulling latest code from GitHub..."
git pull origin genspark_ai_developer_clean
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to pull from GitHub${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Code pulled successfully${NC}"
echo ""

# Step 2: Backup current production file
echo "💾 Step 2: Backing up current production file..."
BACKUP_FILE="/var/www/ddos.neuralgrid.kr/server.js.backup.$(date +%Y%m%d_%H%M%S)"
sudo cp /var/www/ddos.neuralgrid.kr/server.js "$BACKUP_FILE"
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to create backup${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Backup created: $BACKUP_FILE${NC}"
echo ""

# Step 3: Deploy new file
echo "🚀 Step 3: Deploying updated code to production..."
sudo cp ddos-server-updated.js /var/www/ddos.neuralgrid.kr/server.js
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to deploy file${NC}"
    echo "   To rollback: sudo cp $BACKUP_FILE /var/www/ddos.neuralgrid.kr/server.js"
    exit 1
fi
echo -e "${GREEN}✅ File deployed successfully${NC}"
echo ""

# Step 4: Fix permissions
echo "🔐 Step 4: Setting correct file permissions..."
sudo chown www-data:www-data /var/www/ddos.neuralgrid.kr/server.js
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

# Step 5: Fix /var/lib/neuralgrid directory
echo "📁 Step 5: Creating and fixing /var/lib/neuralgrid directory..."
sudo mkdir -p /var/lib/neuralgrid
sudo chown www-data:www-data /var/lib/neuralgrid
sudo chmod 755 /var/lib/neuralgrid
echo -e "${GREEN}✅ Directory permissions fixed${NC}"
echo ""

# Step 6: Restart PM2 service
echo "🔄 Step 6: Restarting PM2 service..."
pm2 restart ddos-security
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to restart service${NC}"
    echo "   To rollback: sudo cp $BACKUP_FILE /var/www/ddos.neuralgrid.kr/server.js && pm2 restart ddos-security"
    exit 1
fi
echo -e "${GREEN}✅ Service restarted${NC}"
echo ""

# Step 7: Wait for service to start
echo "⏳ Waiting 3 seconds for service to start..."
sleep 3
echo ""

# Step 8: Check service status
echo "✅ Step 7: Checking service status..."
pm2 status ddos-security
echo ""

# Step 9: Show recent logs
echo "📋 Recent logs (last 30 lines):"
echo "============================================"
pm2 logs ddos-security --lines 30 --nostream
echo ""

# Success message
echo "============================================"
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo "============================================"
echo ""
echo "🧪 Next steps:"
echo "1. Test authentication:"
echo "   - Login at https://auth.neuralgrid.kr/"
echo "   - Email: aze7009011@gate.com"
echo ""
echo "2. Test registration:"
echo "   - Go to https://ddos.neuralgrid.kr/register.html"
echo "   - Fill in the form"
echo "   - Check Network tab for POST /api/servers/register-website"
echo ""
echo "3. Monitor logs:"
echo "   pm2 logs ddos-security"
echo ""
echo "4. Look for these success messages:"
echo "   [Auth] 🔍 Verifying token..."
echo "   [Auth] Response status: 200"
echo "   [Auth] ✅ Token valid for user: xxx@xxx.com"
echo ""
echo "📝 Backup location: $BACKUP_FILE"
echo "🔙 Rollback command: sudo cp $BACKUP_FILE /var/www/ddos.neuralgrid.kr/server.js && pm2 restart ddos-security"
echo ""
