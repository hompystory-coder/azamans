#!/bin/bash
# AI Shorts Pro - Enable Nginx Configuration
# Run with: sudo bash enable-ai-shorts.sh

set -e

echo "=========================================="
echo "AI Shorts Pro - Enabling Nginx Config"
echo "=========================================="

# Enable nginx site
if [ ! -f /etc/nginx/sites-enabled/ai-shorts.neuralgrid.kr.conf ]; then
    echo "Creating symlink to enable site..."
    ln -sf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-enabled/
    echo "✓ Site enabled"
else
    echo "✓ Site already enabled"
fi

# Test nginx configuration
echo "Testing nginx configuration..."
nginx -t

# Reload nginx
echo "Reloading nginx..."
systemctl reload nginx

echo ""
echo "=========================================="
echo "✓ Deployment Complete!"
echo "=========================================="
echo ""
echo "🌐 Your AI Shorts Pro is now live at:"
echo "   https://ai-shorts.neuralgrid.kr"
echo ""
echo "🔌 Backend API:"
echo "   https://ai-shorts.neuralgrid.kr/api/health"
echo ""
echo "📊 Backend Process:"
echo "   Running on port 5555 (PID: $(pgrep -f 'node server.js' | grep -v grep | head -1))"
echo ""
echo "=========================================="
