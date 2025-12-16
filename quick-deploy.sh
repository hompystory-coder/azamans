#!/bin/bash
###############################################################################
# Quick Deploy - 빠른 배포 스크립트
###############################################################################

set -e

echo "🚀 Quick Deploy Starting..."
echo ""

# 1. Git pull
echo "📥 Step 1: Git pull..."
cd /home/azamans/webapp
git pull origin genspark_ai_developer_clean
echo "✅ Done"
echo ""

# 2. Fix data directory
echo "🔧 Step 2: Fix data directory..."
if [ ! -d "/var/lib/neuralgrid" ]; then
    sudo mkdir -p /var/lib/neuralgrid
fi
sudo chown -R $USER:www-data /var/lib/neuralgrid
sudo chmod -R 775 /var/lib/neuralgrid
echo "✅ Done"
echo ""

# 3. Deploy
echo "📦 Step 3: Deploy file..."
sudo cp ddos-server-updated.js /var/www/ddos.neuralgrid.kr/server.js
sudo chown www-data:www-data /var/www/ddos.neuralgrid.kr/server.js
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js
echo "✅ Done"
echo ""

# 4. Restart
echo "🔄 Step 4: Restart service..."
pm2 restart ddos-security
sleep 2
echo "✅ Done"
echo ""

# 5. Status
echo "📊 Service Status:"
pm2 status ddos-security
echo ""

echo "✅ Deployment Complete!"
echo ""
echo "📝 Check logs:"
echo "   pm2 logs ddos-security"
echo ""
echo "🧪 Test in browser:"
echo "   1. Login at https://auth.neuralgrid.kr/"
echo "   2. Go to https://ddos.neuralgrid.kr/register.html"
echo "   3. Open F12 → Network tab"
echo "   4. Register website protection"
echo "   5. Check: POST /api/servers/register-website → 200 OK"
echo ""
