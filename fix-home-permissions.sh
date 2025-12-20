#!/bin/bash
# AI Shorts Pro - Fix Home Directory Permissions
# Run with: sudo bash fix-home-permissions.sh

echo "=========================================="
echo "Fixing Home Directory Permissions"
echo "=========================================="
echo ""

echo "Current /home/azamans permissions:"
ls -ld /home/azamans
echo ""

echo "The issue: www-data cannot traverse /home/azamans directory"
echo ""

echo "Solution: Add execute permission for 'others' on home directory chain"
echo ""

# Add execute permission for others on home directory
chmod o+x /home/azamans

echo "✓ Permission updated:"
ls -ld /home/azamans
echo ""

echo "Testing www-data access..."
sudo -u www-data test -r /home/azamans/webapp/ai-shorts-pro/frontend/dist/index.html && echo "✓ File is now readable!" || echo "✗ Still denied (checking parent directories...)"
echo ""

# If still denied, fix parent directories
echo "Ensuring all parent directories are accessible..."
chmod o+x /home/azamans/webapp
chmod o+x /home/azamans/webapp/ai-shorts-pro
chmod o+x /home/azamans/webapp/ai-shorts-pro/frontend
chmod o+x /home/azamans/webapp/ai-shorts-pro/frontend/dist
echo "✓ All parent directories now accessible"
echo ""

echo "Final test..."
sudo -u www-data test -r /home/azamans/webapp/ai-shorts-pro/frontend/dist/index.html && echo "✓ SUCCESS! File is readable" || echo "✗ Failed - manual investigation needed"
echo ""

echo "Reloading nginx..."
systemctl reload nginx
echo ""

echo "Testing site..."
sleep 1
curl -I https://ai-shorts.neuralgrid.kr 2>&1 | head -10
echo ""

echo "=========================================="
echo "Permission Fix Complete!"
echo "=========================================="
echo ""
echo "If you see HTTP/2 200 above, your site is working!"
echo "Visit: https://ai-shorts.neuralgrid.kr"
echo ""
