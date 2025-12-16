#!/bin/bash

# 🚀 Automated Deployment Script (No Interaction Required)
# This script uses sudo without password prompt by writing password to stdin

SUDO_PASSWORD="7009011226119"

echo "============================================"
echo "🚀 DDoS Security Automated Deployment"
echo "============================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Pull latest code
echo "📥 Step 1: Pulling latest code..."
cd /home/azamans/webapp
git pull origin genspark_ai_developer_clean
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to pull from GitHub${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Code pulled${NC}"
echo ""

# Step 2: Backup current production file
echo "💾 Step 2: Creating backup..."
BACKUP_FILE="/var/www/ddos.neuralgrid.kr/server.js.backup.$(date +%Y%m%d_%H%M%S)"
echo "$SUDO_PASSWORD" | sudo -S cp /var/www/ddos.neuralgrid.kr/server.js "$BACKUP_FILE"
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to create backup${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Backup created: $BACKUP_FILE${NC}"
echo ""

# Step 3: Deploy new file
echo "🚀 Step 3: Deploying updated code..."
echo "$SUDO_PASSWORD" | sudo -S cp /home/azamans/webapp/ddos-server-updated.js /var/www/ddos.neuralgrid.kr/server.js
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to deploy${NC}"
    exit 1
fi
echo -e "${GREEN}✅ File deployed${NC}"
echo ""

# Step 4: Fix permissions
echo "🔐 Step 4: Setting permissions..."
echo "$SUDO_PASSWORD" | sudo -S chown www-data:www-data /var/www/ddos.neuralgrid.kr/server.js
echo "$SUDO_PASSWORD" | sudo -S chmod 644 /var/www/ddos.neuralgrid.kr/server.js
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

# Step 5: Fix directory
echo "📁 Step 5: Fixing /var/lib/neuralgrid..."
echo "$SUDO_PASSWORD" | sudo -S mkdir -p /var/lib/neuralgrid
echo "$SUDO_PASSWORD" | sudo -S chown www-data:www-data /var/lib/neuralgrid
echo "$SUDO_PASSWORD" | sudo -S chmod 755 /var/lib/neuralgrid
echo -e "${GREEN}✅ Directory fixed${NC}"
echo ""

# Step 6: Restart service
echo "🔄 Step 6: Restarting PM2 service..."
pm2 restart ddos-security
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to restart service${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Service restarted${NC}"
echo ""

# Step 7: Wait and check
echo "⏳ Waiting 3 seconds for service to start..."
sleep 3
echo ""

echo "✅ Step 7: Service status:"
pm2 status ddos-security
echo ""

echo "📋 Recent logs:"
echo "============================================"
pm2 logs ddos-security --lines 30 --nostream
echo ""

echo "============================================"
echo -e "${GREEN}✅ DEPLOYMENT COMPLETED!${NC}"
echo "============================================"
echo ""
echo "🧪 Test now:"
echo "1. Login: https://auth.neuralgrid.kr/"
echo "2. Register: https://ddos.neuralgrid.kr/register.html"
echo "3. Monitor: pm2 logs ddos-security"
echo ""
echo "📝 Backup: $BACKUP_FILE"
echo ""
