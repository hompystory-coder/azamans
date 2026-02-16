#!/bin/bash

# 🚀 NeuralGrid 메인 페이지 배포 (간단 버전)
# 사용법: sudo bash /home/azamans/webapp/deploy-simple.sh

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🚀 NeuralGrid 메인 페이지 업데이트 배포"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. 백업
echo "📦 Step 1/3: 현재 파일 백업..."
cp /var/www/neuralgrid.kr/html/index.html \
   /var/www/neuralgrid.kr/html/index.html.backup_$(date +%Y%m%d_%H%M%S)
echo "   ✅ 백업 완료"
echo ""

# 2. 배포
echo "📝 Step 2/3: 업데이트된 파일 배포..."
cp /home/azamans/webapp/neuralgrid-main-page-updated.html \
   /var/www/neuralgrid.kr/html/index.html
chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
chmod 664 /var/www/neuralgrid.kr/html/index.html
echo "   ✅ 배포 완료"
echo ""

# 3. Nginx 리로드
echo "🔄 Step 3/3: Nginx 리로드..."
systemctl reload nginx
echo "   ✅ 리로드 완료"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ 배포 성공!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 변경된 내용:"
echo "  ✓ URL: bn-shop → shorts.neuralgrid.kr"
echo "  ✓ 기술: 실제 사용 기술로 업데이트"
echo "  ✓ 가격: \$0.06 → ₩29"
echo "  ✓ 시간: 4분 → 15초"
echo ""
echo "🌐 확인 방법:"
echo "  1. 브라우저: https://neuralgrid.kr/"
echo "  2. 명령어: curl -s https://neuralgrid.kr/ | grep 'shorts.neuralgrid.kr'"
echo ""
