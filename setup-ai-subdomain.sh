#!/bin/bash

# ====================================
# AnythingLLM Subdomain Setup Script
# ====================================
# Domain: ai.neuralgrid.kr
# Backend Port: 3104
# Server: 115.91.5.140
# ====================================

set -e  # Exit on error

echo "=========================================="
echo "🚀 Setting up ai.neuralgrid.kr subdomain"
echo "=========================================="

# Configuration
DOMAIN="ai.neuralgrid.kr"
SERVER_IP="115.91.5.140"
BACKEND_PORT="3104"
NGINX_CONF="/etc/nginx/sites-available/$DOMAIN"
NGINX_ENABLED="/etc/nginx/sites-enabled/$DOMAIN"

# Step 1: Upload Nginx configuration
echo ""
echo "📋 Step 1: Uploading Nginx configuration..."
scp ai.neuralgrid.kr.nginx.conf azamans@$SERVER_IP:/tmp/$DOMAIN.conf
ssh azamans@$SERVER_IP "sudo mv /tmp/$DOMAIN.conf $NGINX_CONF"
echo "✅ Nginx configuration uploaded"

# Step 2: Enable site
echo ""
echo "🔗 Step 2: Enabling site..."
ssh azamans@$SERVER_IP "sudo ln -sf $NGINX_CONF $NGINX_ENABLED"
echo "✅ Site enabled"

# Step 3: Test Nginx configuration
echo ""
echo "🧪 Step 3: Testing Nginx configuration..."
ssh azamans@$SERVER_IP "sudo nginx -t"
echo "✅ Nginx configuration is valid"

# Step 4: Reload Nginx
echo ""
echo "🔄 Step 4: Reloading Nginx..."
ssh azamans@$SERVER_IP "sudo systemctl reload nginx"
echo "✅ Nginx reloaded"

# Step 5: Configure SSL with Certbot
echo ""
echo "🔒 Step 5: Configuring SSL certificate..."
ssh azamans@$SERVER_IP "sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email admin@neuralgrid.kr --redirect"
echo "✅ SSL certificate configured"

# Step 6: Verify deployment
echo ""
echo "🔍 Step 6: Verifying deployment..."
sleep 5
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://$DOMAIN/ || echo "000")

if [ "$HTTP_STATUS" = "200" ]; then
    echo "✅ Deployment successful!"
    echo ""
    echo "=========================================="
    echo "🎉 SUCCESS! Service is now live at:"
    echo "🌐 https://$DOMAIN"
    echo "=========================================="
else
    echo "⚠️  Warning: HTTP status code: $HTTP_STATUS"
    echo "Please check the configuration manually"
fi

echo ""
echo "📝 Next Steps:"
echo "1. Add DNS A record in Cloudflare:"
echo "   Type: A"
echo "   Name: ai"
echo "   Content: $SERVER_IP"
echo "   Proxy: Enabled (orange cloud)"
echo ""
echo "2. Wait 1-5 minutes for DNS propagation"
echo ""
echo "3. Test access: https://$DOMAIN"
echo ""
echo "4. Integrate with main page (neuralgrid.kr)"
echo ""
echo "=========================================="
