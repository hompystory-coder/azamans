#!/bin/bash
# Quick fix script - Run this with: bash APPLY_FIX.sh
# This will prompt for your sudo password

set -e

echo "🔧 Applying Nginx configuration fix for Shorts Creator Pro"
echo "📝 This will update /etc/nginx/sites-available/shorts.neuralgrid.kr"
echo ""

# Backup
echo "📦 Creating backup..."
sudo cp /etc/nginx/sites-available/shorts.neuralgrid.kr \
       /etc/nginx/sites-available/shorts.neuralgrid.kr.backup_$(date +%Y%m%d_%H%M%S)

# Apply new config
echo "📝 Applying new configuration..."
sudo cp ~/shorts-creator-pro/nginx/shorts.neuralgrid.kr.conf \
       /etc/nginx/sites-available/shorts.neuralgrid.kr

# Test
echo "🧪 Testing Nginx configuration..."
sudo nginx -t

# Reload
echo "♻️  Reloading Nginx..."
sudo systemctl reload nginx

echo ""
echo "✅ Nginx configuration updated successfully!"
echo ""
echo "🧪 Testing API endpoint..."
echo ""

# Test the API
RESULT=$(curl -s -X POST "https://shorts.neuralgrid.kr/api/crawler/fetch" \
  -H "Content-Type: application/json" \
  -d '{"url":"https://blog.naver.com/alphahome/224073397929"}' \
  -k)

if echo "$RESULT" | grep -q '"success":true'; then
    echo "✅ API is working! Crawler returned: $(echo "$RESULT" | head -c 100)..."
    echo ""
    echo "🎉 All done! Visit https://shorts.neuralgrid.kr/ and test the crawler!"
else
    echo "⚠️  API test result: $RESULT"
    echo "Please check backend logs: pm2 logs shorts-creator-backend"
fi
