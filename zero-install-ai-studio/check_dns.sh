#!/bin/bash

DOMAIN="ai-studio.neuralgrid.kr"
EXPECTED_IP="115.91.5.140"

echo "🔍 DNS 전파 확인 중: $DOMAIN"
echo "예상 IP: $EXPECTED_IP"
echo "=================================="

# Google DNS로 확인
echo -e "\n📡 Google DNS (8.8.8.8):"
nslookup $DOMAIN 8.8.8.8 2>&1 | grep -A2 "Name:" || echo "❌ 아직 전파되지 않음"

# Cloudflare DNS로 확인
echo -e "\n📡 Cloudflare DNS (1.1.1.1):"
nslookup $DOMAIN 1.1.1.1 2>&1 | grep -A2 "Name:" || echo "❌ 아직 전파되지 않음"

# 로컬 DNS로 확인
echo -e "\n📡 로컬 DNS:"
nslookup $DOMAIN 2>&1 | grep -A2 "Name:" || echo "❌ 아직 전파되지 않음"

# dig 명령어로 상세 확인
echo -e "\n🔬 상세 DNS 정보 (dig):"
dig $DOMAIN +short

# 전파 상태 요약
echo -e "\n=================================="
CURRENT_IP=$(dig $DOMAIN +short | head -1)
if [ "$CURRENT_IP" == "$EXPECTED_IP" ]; then
    echo "✅ DNS 전파 완료!"
    echo "✅ $DOMAIN → $CURRENT_IP"
    echo ""
    echo "다음 단계:"
    echo "1. Nginx 설정: sudo cp nginx-config.conf /etc/nginx/sites-available/ai-studio-neuralgrid"
    echo "2. 심볼릭 링크: sudo ln -sf /etc/nginx/sites-available/ai-studio-neuralgrid /etc/nginx/sites-enabled/"
    echo "3. 설정 테스트: sudo nginx -t"
    echo "4. Nginx 재시작: sudo systemctl reload nginx"
    echo "5. SSL 인증서: sudo certbot --nginx -d $DOMAIN"
else
    echo "⏳ DNS 아직 전파 중..."
    echo "현재 IP: ${CURRENT_IP:-없음}"
    echo "예상 IP: $EXPECTED_IP"
    echo ""
    echo "💡 팁: 5분마다 다시 실행해보세요"
    echo "   ./check_dns.sh"
fi
