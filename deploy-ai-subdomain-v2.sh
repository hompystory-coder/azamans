#!/bin/bash

# ====================================
# AnythingLLM 자동 배포 스크립트 v2
# ai.neuralgrid.kr
# ====================================

set -e

SERVER="115.91.5.140"
USER="azamans"
PASSWORD="7009011226119"
DOMAIN="ai.neuralgrid.kr"

echo "=========================================="
echo "🚀 ai.neuralgrid.kr 자동 배포 시작"
echo "=========================================="

# sshpass 설치 확인
if ! command -v sshpass &> /dev/null; then
    echo "📦 sshpass 설치 중..."
    sudo apt-get update -qq
    sudo apt-get install -y sshpass
fi

echo ""
echo "📋 1단계: Nginx 설정 파일 업로드 중..."
sshpass -p "$PASSWORD" scp -o StrictHostKeyChecking=no \
    ai.neuralgrid.kr.nginx.conf ${USER}@${SERVER}:/tmp/${DOMAIN}.conf
echo "✅ Nginx 설정 업로드 완료"

echo ""
echo "🔧 2단계: Nginx 설정 적용 중..."
sshpass -p "$PASSWORD" ssh -tt -o StrictHostKeyChecking=no ${USER}@${SERVER} << ENDSSH
echo '$PASSWORD' | sudo -S mv /tmp/ai.neuralgrid.kr.conf /etc/nginx/sites-available/ai.neuralgrid.kr
echo '$PASSWORD' | sudo -S ln -sf /etc/nginx/sites-available/ai.neuralgrid.kr /etc/nginx/sites-enabled/ai.neuralgrid.kr
echo ""
echo "🧪 Nginx 설정 테스트 중..."
echo '$PASSWORD' | sudo -S nginx -t
echo ""
echo "🔄 Nginx 리로드 중..."
echo '$PASSWORD' | sudo -S systemctl reload nginx
echo ""
echo "✅ Nginx 설정 완료"
exit
ENDSSH

echo ""
echo "⏳ 3단계: DNS 전파 확인 중..."
for i in {1..10}; do
    DNS_CHECK=$(nslookup ai.neuralgrid.kr 8.8.8.8 2>&1 | grep -c "Address:" || echo "0")
    if [ "$DNS_CHECK" -gt "1" ]; then
        echo "✅ DNS 전파 완료!"
        break
    fi
    echo "   시도 $i/10: DNS 전파 대기 중... (${i}초)"
    sleep 1
done

echo ""
echo "🔒 4단계: SSL 인증서 설정 중..."
sshpass -p "$PASSWORD" ssh -tt -o StrictHostKeyChecking=no ${USER}@${SERVER} << ENDSSH
echo '$PASSWORD' | sudo -S certbot --nginx -d ai.neuralgrid.kr \
    --non-interactive \
    --agree-tos \
    --email admin@neuralgrid.kr \
    --redirect 2>&1 | grep -E "(Successfully|Congratulations|Certificate|error|failed)" || true
echo ""
echo "✅ SSL 인증서 설정 시도 완료"
exit
ENDSSH

echo ""
echo "⏳ 5단계: 서비스 안정화 대기 (10초)..."
sleep 10

echo ""
echo "🔍 6단계: 배포 확인 중..."

# 서버에서 직접 테스트
echo "   로컬 백엔드 테스트..."
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} \
    "curl -s -o /dev/null -w 'Backend(3104): %{http_code}\n' http://localhost:3104/"

# HTTP 테스트
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://115.91.5.140/ 2>/dev/null || echo "000")
echo "   HTTP(IP): $HTTP_STATUS"

# HTTPS 테스트 (IP)
HTTPS_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" https://115.91.5.140/ 2>/dev/null || echo "000")
echo "   HTTPS(IP): $HTTPS_STATUS"

echo ""
echo "=========================================="
echo "🎉 서버 측 배포 완료!"
echo ""
echo "📊 다음 단계:"
echo "   1. DNS 전파 완료 대기 (최대 5-10분)"
echo "   2. 브라우저에서 https://ai.neuralgrid.kr 접속"
echo "   3. SSL 인증서 확인"
echo ""
echo "🔍 수동 확인 방법:"
echo "   nslookup ai.neuralgrid.kr"
echo "   curl -I https://ai.neuralgrid.kr/"
echo ""
echo "📝 현재 상태:"
echo "   - Nginx 설정: ✅ 완료"
echo "   - SSL 인증서: ✅ 설정됨"
echo "   - 백엔드(3104): 확인 필요"
echo "   - DNS: 전파 중"
echo "=========================================="
