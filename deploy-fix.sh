#!/bin/bash
# 서버에 배포 스크립트

echo "======================================"
echo "NeuralGrid Homepage 버그 수정 배포"
echo "======================================"

# Git 업데이트
cd /tmp/azamans || exit 1
echo "✅ 디렉토리 이동 완료: /tmp/azamans"

git fetch origin
echo "✅ Git fetch 완료"

git checkout genspark_ai_developer_clean
echo "✅ Branch 전환 완료: genspark_ai_developer_clean"

git pull origin genspark_ai_developer_clean
echo "✅ Git pull 완료"

# 백업
echo "📦 현재 파일 백업 중..."
sudo cp /var/www/html/index.html /var/www/html/index.html.backup-$(date +%Y%m%d-%H%M%S)
echo "✅ 백업 완료"

# 배포
echo "🚀 새 파일 배포 중..."
sudo cp neuralgrid-homepage.html /var/www/html/index.html
sudo chown www-data:www-data /var/www/html/index.html
sudo chmod 644 /var/www/html/index.html
echo "✅ 파일 배포 완료"

# Nginx 재시작
echo "♻️ Nginx 재시작 중..."
sudo nginx -t
sudo systemctl reload nginx
echo "✅ Nginx 재시작 완료"

# 검증
echo ""
echo "======================================"
echo "🔍 배포 검증"
echo "======================================"
curl -I https://neuralgrid.kr/ | head -1
echo ""
echo "✅ 배포 완료!"
echo "🌐 https://neuralgrid.kr/ 에서 확인해주세요."
echo ""
echo "📝 변경사항:"
echo "   - 서비스 콘텐츠가 30초마다 사라지는 버그 수정"
echo "   - 이제 서비스 카드가 계속 표시됩니다!"
echo "======================================"
