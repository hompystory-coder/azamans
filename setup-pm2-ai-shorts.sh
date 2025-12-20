#!/bin/bash
# AI Shorts Pro - Setup PM2 Process Manager
# Run with: bash setup-pm2.sh

set -e

echo "=========================================="
echo "AI Shorts Pro - Setting up PM2"
echo "=========================================="

cd /home/azamans/webapp/ai-shorts-pro/backend

# Kill existing node process on port 5555
echo "Stopping any existing backend process..."
pkill -f "node server.js" || true
sleep 2

# Start with PM2
echo "Starting backend with PM2..."
pm2 start server.js --name "ai-shorts-pro" --log /home/azamans/webapp/ai-shorts-pro/backend/pm2.log

# Save PM2 configuration
pm2 save

echo ""
echo "=========================================="
echo "✓ PM2 Setup Complete!"
echo "=========================================="
echo ""
echo "📊 Process Management:"
echo "   pm2 status              - Check status"
echo "   pm2 logs ai-shorts-pro  - View logs"
echo "   pm2 restart ai-shorts-pro - Restart"
echo "   pm2 stop ai-shorts-pro    - Stop"
echo ""
