#!/bin/bash

# NeuralGrid 메인 페이지 배포 스크립트
# 사용법: sudo bash deploy-neuralgrid-main.sh

echo "🚀 NeuralGrid 메인 페이지 배포 시작..."

# 백업 생성
BACKUP_FILE="/var/www/neuralgrid.kr/html/index.html.backup_$(date +%Y%m%d_%H%M%S)"
echo "📦 현재 파일 백업 중: $BACKUP_FILE"
cp /var/www/neuralgrid.kr/html/index.html "$BACKUP_FILE"

# 업데이트된 파일 복사
echo "📝 업데이트된 파일 배포 중..."
cp /home/azamans/webapp/neuralgrid-main-page-updated.html /var/www/neuralgrid.kr/html/index.html

# 권한 설정
echo "🔐 파일 권한 설정 중..."
chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
chmod 664 /var/www/neuralgrid.kr/html/index.html

# Nginx 설정 테스트
echo "🧪 Nginx 설정 테스트..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx 설정 테스트 성공"
    
    # Nginx 리로드 (다운타임 없음)
    echo "🔄 Nginx 리로드 중..."
    systemctl reload nginx
    
    echo "✅ 배포 완료!"
    echo ""
    echo "📊 변경 사항:"
    echo "  - URL: bn-shop.neuralgrid.kr → shorts.neuralgrid.kr"
    echo "  - 설명: 실제 제품 분석 결과 반영"
    echo "  - 기능: 6가지 핵심 기능 업데이트"
    echo "  - 가격: \$0.06 → ₩29 (정확한 비용)"
    echo ""
    echo "🌐 확인: https://neuralgrid.kr/"
else
    echo "❌ Nginx 설정 테스트 실패"
    echo "🔄 백업 파일로 복구 중..."
    cp "$BACKUP_FILE" /var/www/neuralgrid.kr/html/index.html
    exit 1
fi
