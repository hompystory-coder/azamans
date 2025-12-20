#!/bin/bash
# AI Shorts Pro - Fix SSL Certificate Path
# Run with: sudo bash fix-ssl-path.sh

set -e

echo "=========================================="
echo "Fixing SSL Certificate Path"
echo "=========================================="
echo ""

# Backup original config
echo "Backing up configuration..."
cp /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf \
   /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup.$(date +%Y%m%d_%H%M%S)

# Update SSL certificate paths to use the new certificate
echo "Updating SSL certificate paths..."
sed -i 's|/etc/letsencrypt/live/neuralgrid.kr/|/etc/letsencrypt/live/ai-shorts.neuralgrid.kr/|g' \
    /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf

echo "✓ Certificate paths updated"
echo ""

# Show the updated SSL configuration
echo "Current SSL configuration:"
grep -A 3 "ssl_certificate" /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf
echo ""

# Test nginx configuration
echo "Testing nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Nginx configuration is valid"
    echo ""
    
    # Reload nginx
    echo "Reloading nginx..."
    systemctl reload nginx
    
    echo ""
    echo "=========================================="
    echo "✓ SSL Certificate Fix Complete!"
    echo "=========================================="
    echo ""
    echo "Your site should now work with HTTPS:"
    echo "   https://ai-shorts.neuralgrid.kr"
    echo ""
    echo "Testing connection..."
    sleep 2
    curl -I https://ai-shorts.neuralgrid.kr 2>&1 | head -5
    echo ""
    echo "API Health Check:"
    curl -s https://ai-shorts.neuralgrid.kr/api/health
    echo ""
    echo ""
else
    echo ""
    echo "⚠️  Nginx configuration test failed!"
    echo "Restoring backup..."
    cp /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup.* \
       /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf
    echo ""
    echo "Configuration restored. Please check the error above."
    echo ""
fi
