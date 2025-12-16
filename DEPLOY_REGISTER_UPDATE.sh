#!/bin/bash

echo "======================================"
echo "DDoS Register Page 상품 구성 배포"
echo "======================================"

# 1. Git 업데이트
echo ""
echo "[1/4] Git 최신 코드 가져오기..."
git pull origin genspark_ai_developer_clean

# 2. DDoS Register 페이지 배포
echo ""
echo "[2/4] DDoS Register 페이지 배포 중..."
sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html
sudo chown www-data:www-data /var/www/ddos.neuralgrid.kr/register.html

# 3. Nginx 설정 확인
echo ""
echo "[3/4] Nginx 설정 확인 중..."
sudo nginx -t

# 4. Nginx 재시작
echo ""
echo "[4/4] Nginx 재시작 중..."
sudo systemctl reload nginx

echo ""
echo "======================================"
echo "배포 완료!"
echo "======================================"
echo ""
echo "확인 방법:"
echo "  https://ddos.neuralgrid.kr/register.html 접속"
echo "  3가지 상품 플랜 확인:"
echo "    1. 무료 7일 체험"
echo "    2. 홈페이지 보호 (₩330,000/년)"
echo "    3. 서버 보호 (₩2,990,000/년)"
echo ""
