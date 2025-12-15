#!/bin/bash
# 서버 (115.91.5.140)에서 직접 실행할 원라인 배포 명령어

echo "======================================"
echo "Phase 1 원라인 배포 명령어"
echo "======================================"
echo ""
echo "서버 터미널에서 아래 명령어를 복사해서 실행하세요:"
echo ""
echo "------------------------------------"
cat << 'EOF'
cd /home/azamans/webapp && \
git fetch origin && \
git checkout genspark_ai_developer_clean && \
git pull origin genspark_ai_developer_clean && \
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$(date +%Y%m%d-%H%M%S) 2>/dev/null && \
sudo cp ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js && \
sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html && \
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/ && \
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html && \
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js && \
pm2 restart ddos-security && \
sleep 2 && \
echo "" && \
echo "✅ 배포 완료!" && \
echo "" && \
echo "🔍 검증:" && \
curl -s http://localhost:3105/health && \
echo "" && \
curl -I https://ddos.neuralgrid.kr/register.html 2>&1 | head -1
EOF
echo "------------------------------------"
echo ""
echo "배포 후 확인:"
echo "  - 등록 페이지: https://ddos.neuralgrid.kr/register.html"
echo "  - 메인 대시보드: https://ddos.neuralgrid.kr/"
echo ""
