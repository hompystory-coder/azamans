#!/bin/bash
# Auth Service DNS Setup Script for Cloudflare
# Usage: ./setup_auth_dns.sh <CLOUDFLARE_API_TOKEN> <ZONE_ID>

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
DOMAIN="neuralgrid.kr"
SUBDOMAIN="auth"
FULL_DOMAIN="${SUBDOMAIN}.${DOMAIN}"
SERVER_IP="115.91.5.140"
EMAIL="admin@neuralgrid.kr"

# Check arguments
if [ "$#" -lt 1 ]; then
    echo -e "${RED}Usage: $0 <CLOUDFLARE_API_TOKEN> [ZONE_ID]${NC}"
    echo ""
    echo "Get your Cloudflare API Token from:"
    echo "  https://dash.cloudflare.com/profile/api-tokens"
    echo ""
    echo "Get your Zone ID from:"
    echo "  https://dash.cloudflare.com/ -> Select domain -> Overview (right side)"
    exit 1
fi

CF_API_TOKEN="$1"
ZONE_ID="${2:-}"

echo -e "${GREEN}=== Auth Service DNS Setup ===${NC}"
echo "Domain: ${FULL_DOMAIN}"
echo "IP: ${SERVER_IP}"
echo ""

# Get Zone ID if not provided
if [ -z "$ZONE_ID" ]; then
    echo -e "${YELLOW}[1/5] Getting Zone ID...${NC}"
    ZONE_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones?name=${DOMAIN}" \
        -H "Authorization: Bearer ${CF_API_TOKEN}" \
        -H "Content-Type: application/json" | \
        grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)
    
    if [ -z "$ZONE_ID" ]; then
        echo -e "${RED}Error: Could not get Zone ID${NC}"
        exit 1
    fi
    echo "Zone ID: ${ZONE_ID}"
fi

# Check if DNS record exists
echo -e "${YELLOW}[2/5] Checking existing DNS records...${NC}"
EXISTING_RECORD=$(curl -s -X GET \
    "https://api.cloudflare.com/client/v4/zones/${ZONE_ID}/dns_records?type=A&name=${FULL_DOMAIN}" \
    -H "Authorization: Bearer ${CF_API_TOKEN}" \
    -H "Content-Type: application/json")

RECORD_ID=$(echo "$EXISTING_RECORD" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)

if [ -n "$RECORD_ID" ]; then
    echo "Record exists (ID: ${RECORD_ID}), updating..."
    
    # Update existing record
    UPDATE_RESPONSE=$(curl -s -X PUT \
        "https://api.cloudflare.com/client/v4/zones/${ZONE_ID}/dns_records/${RECORD_ID}" \
        -H "Authorization: Bearer ${CF_API_TOKEN}" \
        -H "Content-Type: application/json" \
        --data "{
            \"type\": \"A\",
            \"name\": \"${SUBDOMAIN}\",
            \"content\": \"${SERVER_IP}\",
            \"ttl\": 1,
            \"proxied\": true
        }")
    
    SUCCESS=$(echo "$UPDATE_RESPONSE" | grep -o '"success":[^,]*' | cut -d':' -f2)
    if [ "$SUCCESS" = "true" ]; then
        echo -e "${GREEN}✅ DNS record updated successfully${NC}"
    else
        echo -e "${RED}❌ Failed to update DNS record${NC}"
        echo "$UPDATE_RESPONSE"
        exit 1
    fi
else
    echo "Record does not exist, creating..."
    
    # Create new record
    CREATE_RESPONSE=$(curl -s -X POST \
        "https://api.cloudflare.com/client/v4/zones/${ZONE_ID}/dns_records" \
        -H "Authorization: Bearer ${CF_API_TOKEN}" \
        -H "Content-Type: application/json" \
        --data "{
            \"type\": \"A\",
            \"name\": \"${SUBDOMAIN}\",
            \"content\": \"${SERVER_IP}\",
            \"ttl\": 1,
            \"proxied\": true
        }")
    
    SUCCESS=$(echo "$CREATE_RESPONSE" | grep -o '"success":[^,]*' | cut -d':' -f2)
    if [ "$SUCCESS" = "true" ]; then
        echo -e "${GREEN}✅ DNS record created successfully${NC}"
    else
        echo -e "${RED}❌ Failed to create DNS record${NC}"
        echo "$CREATE_RESPONSE"
        exit 1
    fi
fi

# Wait for DNS propagation
echo -e "${YELLOW}[3/5] Waiting for DNS propagation (30 seconds)...${NC}"
for i in {30..1}; do
    echo -ne "\rWaiting... ${i}s "
    sleep 1
done
echo ""

# Verify DNS
echo -e "${YELLOW}[4/5] Verifying DNS resolution...${NC}"
DNS_RESULT=$(dig +short ${FULL_DOMAIN} @8.8.8.8 | head -1)

if [ -n "$DNS_RESULT" ]; then
    echo -e "${GREEN}✅ DNS resolved: ${DNS_RESULT}${NC}"
else
    echo -e "${YELLOW}⚠️  DNS not yet propagated (may take up to 5 minutes)${NC}"
    echo "You can verify later with: dig +short ${FULL_DOMAIN}"
fi

# Instructions for SSL certificate
echo ""
echo -e "${YELLOW}[5/5] Next Steps: Issue SSL Certificate${NC}"
echo ""
echo "Run these commands on the server (115.91.5.140):"
echo ""
echo -e "${GREEN}# 1. Issue SSL certificate${NC}"
echo "sudo certbot certonly --nginx -d ${FULL_DOMAIN} \\"
echo "  --non-interactive --agree-tos -m ${EMAIL}"
echo ""
echo -e "${GREEN}# 2. Update Nginx configuration${NC}"
echo "sudo sed -i 's|ssl_certificate.*|ssl_certificate /etc/letsencrypt/live/${FULL_DOMAIN}/fullchain.pem;|' /etc/nginx/sites-available/auth.neuralgrid.kr"
echo "sudo sed -i 's|ssl_certificate_key.*|ssl_certificate_key /etc/letsencrypt/live/${FULL_DOMAIN}/privkey.pem;|' /etc/nginx/sites-available/auth.neuralgrid.kr"
echo ""
echo -e "${GREEN}# 3. Test and reload Nginx${NC}"
echo "sudo nginx -t && sudo systemctl reload nginx"
echo ""
echo -e "${GREEN}# 4. Test the service${NC}"
echo "curl -I https://${FULL_DOMAIN}/health"
echo ""
echo -e "${GREEN}=== DNS Setup Complete! ===${NC}"
echo ""
echo "Next: Wait 5 minutes for full DNS propagation, then issue SSL certificate"
