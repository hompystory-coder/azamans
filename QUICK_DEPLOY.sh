#!/bin/bash

##############################################################################
# NeuralGrid 메인페이지 빠른 배포 스크립트
##############################################################################
# 사용법: 
#   1. SSH로 서버 접속: ssh azamans@115.91.5.140
#   2. 이 스크립트 실행: bash QUICK_DEPLOY.sh
##############################################################################

set -e

echo "🚀 NeuralGrid 메인페이지 배포 시작..."
echo ""

# 1. 백업 생성
echo "📦 현재 파일 백업 중..."
BACKUP_FILE="/var/www/neuralgrid.kr/html/index.html.backup_$(date +%Y%m%d_%H%M%S)"
sudo cp /var/www/neuralgrid.kr/html/index.html "$BACKUP_FILE"
echo "✅ 백업 완료: $BACKUP_FILE"
echo ""

# 2. 새 파일 다운로드 (GitHub에서)
echo "📥 새 메인페이지 다운로드 중..."
cd /tmp
wget -O neuralgrid-new.html "https://raw.githubusercontent.com/YOUR_USERNAME/YOUR_REPO/main/neuralgrid-main-page.html" 2>/dev/null || {
    echo "⚠️  GitHub에서 다운로드 실패. 로컬 파일 사용..."
    
    # 로컬 파일이 있는지 확인
    if [ -f /home/azamans/webapp/neuralgrid-main-page.html ]; then
        cp /home/azamans/webapp/neuralgrid-main-page.html /tmp/neuralgrid-new.html
        echo "✅ 로컬 파일 복사 완료"
    else
        echo "❌ 파일을 찾을 수 없습니다!"
        echo ""
        echo "수동 배포 방법:"
        echo "1. neuralgrid-main-page.html 파일을 서버로 전송"
        echo "2. sudo cp neuralgrid-main-page.html /var/www/neuralgrid.kr/html/index.html"
        exit 1
    fi
}
echo ""

# 3. 파일 배포
echo "🚀 메인페이지 배포 중..."
sudo cp /tmp/neuralgrid-new.html /var/www/neuralgrid.kr/html/index.html
sudo chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
sudo chmod 644 /var/www/neuralgrid.kr/html/index.html
echo "✅ 파일 배포 완료!"
echo ""

# 4. Nginx 설정 확인
echo "🔍 Nginx 설정 확인 중..."
sudo nginx -t
echo ""

# 5. Nginx 재시작 (필요시)
read -p "Nginx를 재시작하시겠습니까? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    sudo systemctl reload nginx
    echo "✅ Nginx 재시작 완료!"
else
    echo "ℹ️  Nginx 재시작을 건너뛰었습니다."
fi
echo ""

# 6. 완료 메시지
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 메인페이지 배포 완료!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📍 배포 위치: /var/www/neuralgrid.kr/html/index.html"
echo "🌐 URL: https://neuralgrid.kr"
echo "📦 백업 파일: $BACKUP_FILE"
echo ""
echo "✅ 브라우저에서 확인하세요!"
echo ""
