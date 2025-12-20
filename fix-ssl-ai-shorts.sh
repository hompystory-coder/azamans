#!/bin/bash
# AI Shorts Pro - SSL Certificate Fix
# Run with: sudo bash fix-ssl-ai-shorts.sh

set -e

echo "=========================================="
echo "AI Shorts Pro - SSL Certificate Setup"
echo "=========================================="
echo ""

# Option 1: Add to existing wildcard certificate
echo "Option 1: Adding ai-shorts.neuralgrid.kr to existing certificate..."
echo ""

certbot certonly --nginx \
  -d ai-shorts.neuralgrid.kr \
  --non-interactive \
  --agree-tos \
  --email admin@neuralgrid.kr \
  --expand

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ SSL certificate obtained successfully!"
    echo ""
    
    # Update nginx configuration to use the new certificate
    echo "Updating nginx configuration..."
    
    # Check which certificate path to use
    if [ -d "/etc/letsencrypt/live/ai-shorts.neuralgrid.kr" ]; then
        CERT_PATH="/etc/letsencrypt/live/ai-shorts.neuralgrid.kr"
    else
        CERT_PATH="/etc/letsencrypt/live/neuralgrid.kr"
    fi
    
    # Update the nginx config
    sed -i "s|ssl_certificate /etc/letsencrypt/live/neuralgrid.kr/|ssl_certificate $CERT_PATH/|g" \
        /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf
    
    # Test nginx configuration
    echo "Testing nginx configuration..."
    nginx -t
    
    # Reload nginx
    echo "Reloading nginx..."
    systemctl reload nginx
    
    echo ""
    echo "=========================================="
    echo "✓ SSL Setup Complete!"
    echo "=========================================="
    echo ""
    echo "Your site is now accessible with HTTPS:"
    echo "   https://ai-shorts.neuralgrid.kr"
    echo ""
    echo "Testing..."
    sleep 2
    curl -I https://ai-shorts.neuralgrid.kr 2>&1 | head -3
    echo ""
else
    echo ""
    echo "⚠️  SSL certificate generation failed!"
    echo ""
    echo "Alternative: Use HTTP-only access temporarily"
    echo "Run: sudo bash /home/azamans/webapp/use-http-only.sh"
    echo ""
fi
