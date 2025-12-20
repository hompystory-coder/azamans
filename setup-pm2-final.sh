#!/bin/bash
# AI Shorts Pro - Setup PM2 (Improved Version)
# Run with: bash setup-pm2-final.sh

set -e

echo "=========================================="
echo "AI Shorts Pro - PM2 Setup"
echo "=========================================="
echo ""

cd /home/azamans/webapp/ai-shorts-pro/backend

echo "1. Stopping existing node processes..."
pkill -f "ai-shorts-pro/backend" || true
sleep 2

echo "✓ Old processes stopped"
echo ""

echo "2. Starting backend with PM2..."
pm2 start server.js \
  --name "ai-shorts-pro" \
  --cwd /home/azamans/webapp/ai-shorts-pro/backend \
  --log /home/azamans/webapp/ai-shorts-pro/backend/pm2.log \
  --error /home/azamans/webapp/ai-shorts-pro/backend/pm2-error.log \
  --output /home/azamans/webapp/ai-shorts-pro/backend/pm2-output.log \
  --time

echo ""
echo "✓ Backend started with PM2"
echo ""

echo "3. Saving PM2 configuration..."
pm2 save

echo "✓ Configuration saved"
echo ""

echo "4. Setting up PM2 startup script..."
# This will configure PM2 to start on system boot
pm2 startup systemd -u azamans --hp /home/azamans

echo ""
echo "5. Verifying PM2 status..."
pm2 list

echo ""
echo "6. Testing backend..."
sleep 3
curl -s http://localhost:5555/api/health

echo ""
echo ""
echo "=========================================="
echo "✓ PM2 Setup Complete!"
echo "=========================================="
echo ""
echo "📊 Process Management Commands:"
echo "   pm2 status              - Check status"
echo "   pm2 logs ai-shorts-pro  - View logs"
echo "   pm2 restart ai-shorts-pro - Restart"
echo "   pm2 stop ai-shorts-pro    - Stop"
echo "   pm2 delete ai-shorts-pro  - Remove"
echo ""
echo "🔄 PM2 will automatically restart your app if it crashes"
echo "🚀 PM2 will start on system boot"
echo ""
echo "Your site is running at:"
echo "   https://ai-shorts.neuralgrid.kr"
echo ""
