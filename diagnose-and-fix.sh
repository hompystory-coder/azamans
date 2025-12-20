#!/bin/bash
# AI Shorts Pro - Diagnostics and Fix
# Run with: sudo bash diagnose-and-fix.sh

echo "=========================================="
echo "AI Shorts Pro - Full Diagnostics"
echo "=========================================="
echo ""

echo "1. Checking frontend files..."
ls -la /home/azamans/webapp/ai-shorts-pro/frontend/dist/
echo ""

echo "2. Checking file permissions..."
stat /home/azamans/webapp/ai-shorts-pro/frontend/dist/index.html
echo ""

echo "3. Checking nginx error log..."
tail -30 /var/log/nginx/error.log | grep -A 5 "ai-shorts" || echo "No specific errors found"
echo ""

echo "4. Testing file access as www-data user..."
sudo -u www-data test -r /home/azamans/webapp/ai-shorts-pro/frontend/dist/index.html && echo "✓ File is readable" || echo "✗ Permission denied"
echo ""

echo "5. Current nginx configuration for ai-shorts:"
cat /etc/nginx/sites-enabled/ai-shorts.neuralgrid.kr.conf
echo ""

echo "6. Fixing permissions..."
chmod 755 /home/azamans/webapp/ai-shorts-pro
chmod 755 /home/azamans/webapp/ai-shorts-pro/frontend
chmod 755 /home/azamans/webapp/ai-shorts-pro/frontend/dist
chmod 644 /home/azamans/webapp/ai-shorts-pro/frontend/dist/index.html
chmod 755 /home/azamans/webapp/ai-shorts-pro/frontend/dist/assets
chmod 644 /home/azamans/webapp/ai-shorts-pro/frontend/dist/assets/*

echo "✓ Permissions fixed"
echo ""

echo "7. Testing access again..."
sudo -u www-data test -r /home/azamans/webapp/ai-shorts-pro/frontend/dist/index.html && echo "✓ File is now readable" || echo "✗ Still permission denied"
echo ""

echo "8. Reloading nginx..."
systemctl reload nginx
echo ""

echo "9. Testing site..."
curl -I https://ai-shorts.neuralgrid.kr 2>&1 | head -10
echo ""

echo "=========================================="
echo "Diagnostics Complete!"
echo "=========================================="
