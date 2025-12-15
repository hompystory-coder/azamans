#!/bin/bash

# NeuralGrid Services SSO Integration Script
# This script automatically integrates SSO authentication to all services

echo "🚀 Starting SSO integration for all NeuralGrid services..."

# Service directories on the server
SERVICES=(
    "/var/www/bn-shop.neuralgrid.kr/html"
    "/var/www/mfx.neuralgrid.kr/html"
    "/var/www/music.neuralgrid.kr/html"
    "/var/www/market.neuralgrid.kr/html"
    "/var/www/n8n.neuralgrid.kr/html"
    "/var/www/monitor.neuralgrid.kr/html"
)

SERVICE_NAMES=(
    "블로그 쇼츠 (bn-shop)"
    "MediaFX (mfx)"
    "StarMusic (music)"
    "쿠팡쇼츠 (market)"
    "N8N 자동화 (n8n)"
    "서버 모니터링 (monitor)"
)

# SSO middleware content
SSO_SCRIPT='<script src="https://auth.neuralgrid.kr/sso-middleware.js"></script>'

# Login button HTML
LOGIN_BUTTON='<button id="neural-auth-btn" style="position:fixed;top:20px;right:20px;z-index:9999;padding:10px 20px;background:linear-gradient(135deg,#8b5cf6,#ec4899);color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;box-shadow:0 4px 15px rgba(139,92,246,0.3);transition:all 0.3s ease;">로그인</button>'

echo ""
echo "📋 Services to integrate:"
for i in "${!SERVICE_NAMES[@]}"; do
    echo "  $((i+1)). ${SERVICE_NAMES[$i]}"
done
echo ""

# Function to integrate SSO
integrate_sso() {
    local service_path=$1
    local service_name=$2
    
    echo "🔧 Integrating SSO for: $service_name"
    echo "   Path: $service_path"
    
    # Find index.html or main HTML file
    if [ -f "$service_path/index.html" ]; then
        local html_file="$service_path/index.html"
        
        # Backup original
        cp "$html_file" "$html_file.backup-$(date +%Y%m%d)"
        
        # Check if SSO already integrated
        if grep -q "sso-middleware.js" "$html_file"; then
            echo "   ✓ SSO already integrated, skipping..."
        else
            # Add SSO script before </body>
            sed -i "s|</body>|$SSO_SCRIPT\n$LOGIN_BUTTON\n</body>|" "$html_file"
            echo "   ✓ SSO integrated successfully!"
        fi
    else
        echo "   ✗ index.html not found, skipping..."
    fi
    echo ""
}

# Main integration loop
for i in "${!SERVICES[@]}"; do
    integrate_sso "${SERVICES[$i]}" "${SERVICE_NAMES[$i]}"
done

echo "✅ SSO integration complete for all services!"
echo ""
echo "📊 Summary:"
echo "   - Total services: ${#SERVICES[@]}"
echo "   - SSO middleware: https://auth.neuralgrid.kr/sso-middleware.js"
echo "   - Auth service: https://auth.neuralgrid.kr"
echo ""
echo "🔄 Next steps:"
echo "   1. Test each service to verify SSO integration"
echo "   2. Check login button visibility"
echo "   3. Test login flow from each service"
echo ""
echo "🎉 All done!"
