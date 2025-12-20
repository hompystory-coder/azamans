#!/bin/bash
# AI Shorts Pro - Fix Nginx Frontend Configuration
# Run with: sudo bash fix-nginx-frontend.sh

set -e

echo "=========================================="
echo "Fixing Nginx Frontend Configuration"
echo "=========================================="
echo ""

# Backup current config
echo "Backing up configuration..."
cp /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf \
   /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup.frontend

# Create corrected configuration
cat > /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf << 'NGINX_EOF'
server {
    listen 80;
    server_name ai-shorts.neuralgrid.kr;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ai-shorts.neuralgrid.kr;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/ai-shorts.neuralgrid.kr/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ai-shorts.neuralgrid.kr/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
    access_log /var/log/nginx/ai-shorts.neuralgrid.kr.access.log;
    error_log /var/log/nginx/ai-shorts.neuralgrid.kr.error.log;
    
    # Backend API (Port 5555)
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
    
    # Frontend (React) - Must be last
    location / {
        root /home/azamans/webapp/ai-shorts-pro/frontend/dist;
        try_files $uri $uri/ /index.html;
        add_header Cache-Control "no-cache";
    }
}
NGINX_EOF

echo "✓ Configuration updated"
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
    echo "✓ Frontend Configuration Fix Complete!"
    echo "=========================================="
    echo ""
    echo "Your site should now work properly:"
    echo "   https://ai-shorts.neuralgrid.kr"
    echo ""
    echo "Testing..."
    sleep 2
    curl -I https://ai-shorts.neuralgrid.kr 2>&1 | head -10
    echo ""
else
    echo ""
    echo "⚠️  Configuration test failed!"
    echo "Restoring backup..."
    cp /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup.frontend \
       /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf
    systemctl reload nginx
    echo "Configuration restored."
    echo ""
fi
