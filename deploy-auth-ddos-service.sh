#!/bin/bash
echo "========================================"
echo "Auth 서비스에 DDoS 플랫폼 추가 배포"
echo "========================================"

cd /home/azamans/webapp
git fetch origin
git checkout genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean

# 백업
echo "📦 백업 중..."
echo "7009011226119" | sudo -S cp /var/www/auth.neuralgrid.kr/index.html /var/www/auth.neuralgrid.kr/index.html.backup-$(date +%Y%m%d-%H%M%S) 2>/dev/null
echo "7009011226119" | sudo -S cp /var/www/auth.neuralgrid.kr/dashboard.html /var/www/auth.neuralgrid.kr/dashboard.html.backup-$(date +%Y%m%d-%H%M%S) 2>/dev/null

# 배포
echo "🚀 배포 중..."
echo "7009011226119" | sudo -S cp auth-login-updated.html /var/www/auth.neuralgrid.kr/index.html
echo "7009011226119" | sudo -S cp auth-dashboard-updated.html /var/www/auth.neuralgrid.kr/dashboard.html

# 권한 설정
echo "7009011226119" | sudo -S chown -R azamans:azamans /var/www/auth.neuralgrid.kr/
echo "7009011226119" | sudo -S chmod 644 /var/www/auth.neuralgrid.kr/*.html

echo ""
echo "✅ 배포 완료!"
echo ""
echo "🔍 확인:"
curl -I https://auth.neuralgrid.kr/ 2>&1 | head -1
curl -I https://auth.neuralgrid.kr/dashboard 2>&1 | head -1

echo ""
echo "배포된 서비스:"
echo "  - 로그인 페이지: https://auth.neuralgrid.kr/"
echo "  - 대시보드: https://auth.neuralgrid.kr/dashboard"
echo "  - DDoS 플랫폼: https://ddos.neuralgrid.kr/register.html"
echo ""
