#!/bin/bash
# AI Shorts Pro - HTTP-Only Configuration (Temporary)
# Run with: sudo bash use-http-only.sh

set -e

echo "=========================================="
echo "AI Shorts Pro - HTTP-Only Setup"
echo "=========================================="
echo ""

# Backup existing config
cp /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf \
   /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup

# Create HTTP-only configuration
cat > /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf << 'NGINX_CONFIG'
server {
    listen 80;
    server_name ai-shorts.neuralgrid.kr;
    
    access_log /var/log/nginx/ai-shorts.neuralgrid.kr.access.log;
    error_log /var/log/nginx/ai-shorts.neuralgrid.kr.error.log;
    
    # Backend API (포트 5555)
    location /api/ {
        proxy_pass http://127.0.0.1:5555;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        client_max_body_size 100M;
        proxy_read_timeout 600s;
        proxy_connect_timeout 600s;
        proxy_send_timeout 600s;
    }
    
    # Socket.io
    location /socket.io/ {
        proxy_pass http://127.0.0.1:5555;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
    
    # Generated files
    location /generated/ {
        proxy_pass http://127.0.0.1:5555;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        add_header Cache-Control "public, max-age=31536000";
    }
    
    # Uploaded files
    location /uploads/ {
        proxy_pass http://127.0.0.1:5555;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        add_header Cache-Control "public, max-age=31536000";
    }
    
    # Frontend (React)
    location / {
        root /home/azamans/webapp/ai-shorts-pro/frontend/dist;
        try_files $uri $uri/ /index.html;
        add_header Cache-Control "no-cache";
    }
}
NGINX_CONFIG

echo "✓ HTTP-only configuration created"
echo ""

# Test nginx configuration
echo "Testing nginx configuration..."
nginx -t

# Reload nginx
echo "Reloading nginx..."
systemctl reload nginx

echo ""
echo "=========================================="
echo "✓ HTTP-Only Setup Complete!"
echo "=========================================="
echo ""
echo "⚠️  TEMPORARY: Site accessible via HTTP only"
echo "   http://ai-shorts.neuralgrid.kr"
echo ""
echo "🔌 Backend API:"
echo "   http://ai-shorts.neuralgrid.kr/api/health"
echo ""
echo "Testing..."
sleep 2
curl -I http://ai-shorts.neuralgrid.kr 2>&1 | head -5
echo ""
echo "To enable HTTPS later, run:"
echo "   sudo bash /home/azamans/webapp/fix-ssl-ai-shorts.sh"
echo ""
