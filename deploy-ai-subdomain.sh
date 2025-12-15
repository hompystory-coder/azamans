#!/bin/bash

# ====================================
# AnythingLLM 자동 배포 스크립트
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
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'
# Nginx 설정 이동
sudo mv /tmp/ai.neuralgrid.kr.conf /etc/nginx/sites-available/ai.neuralgrid.kr

# 심볼릭 링크 생성
sudo ln -sf /etc/nginx/sites-available/ai.neuralgrid.kr /etc/nginx/sites-enabled/ai.neuralgrid.kr

# Nginx 설정 테스트
echo "🧪 Nginx 설정 테스트 중..."
sudo nginx -t

# Nginx 리로드
echo "🔄 Nginx 리로드 중..."
sudo systemctl reload nginx

echo "✅ Nginx 설정 완료"
ENDSSH

echo ""
echo "🔒 3단계: SSL 인증서 설정 중..."
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'
# Certbot으로 SSL 인증서 발급
sudo certbot --nginx -d ai.neuralgrid.kr \
    --non-interactive \
    --agree-tos \
    --email admin@neuralgrid.kr \
    --redirect

echo "✅ SSL 인증서 설정 완료"
ENDSSH

echo ""
echo "⏳ 4단계: 서비스 안정화 대기 (5초)..."
sleep 5

echo ""
echo "🔍 5단계: 배포 확인 중..."

# HTTP 리다이렉트 테스트
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://ai.neuralgrid.kr/ 2>/dev/null || echo "000")
echo "   HTTP 상태: $HTTP_STATUS (301 예상 - HTTPS 리다이렉트)"

# HTTPS 테스트
HTTPS_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://ai.neuralgrid.kr/ 2>/dev/null || echo "000")
echo "   HTTPS 상태: $HTTPS_STATUS (200 예상)"

echo ""
echo "=========================================="
if [ "$HTTPS_STATUS" = "200" ]; then
    echo "🎉 배포 성공!"
    echo ""
    echo "✅ ai.neuralgrid.kr 서비스가 정상 작동 중입니다"
    echo ""
    echo "🌐 접속 URL:"
    echo "   https://ai.neuralgrid.kr"
    echo ""
    echo "📊 서비스 정보:"
    echo "   - 서비스: AnythingLLM (AI 어시스턴트)"
    echo "   - 백엔드 포트: 3104"
    echo "   - SSL: ✅ Let's Encrypt"
    echo "   - 상태: 🟢 온라인"
else
    echo "⚠️  경고: HTTPS 상태 코드가 예상과 다릅니다 ($HTTPS_STATUS)"
    echo ""
    echo "가능한 원인:"
    echo "1. DNS 전파가 아직 완료되지 않음 (1-5분 더 대기)"
    echo "2. 백엔드 서비스(포트 3104)가 실행 중이지 않음"
    echo "3. 방화벽 설정 문제"
    echo ""
    echo "수동 확인 방법:"
    echo "   ssh ${USER}@${SERVER}"
    echo "   sudo nginx -t"
    echo "   curl http://localhost:3104/"
    echo "   pm2 list"
fi
echo "=========================================="
