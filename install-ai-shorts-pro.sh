#!/bin/bash

echo "🚀 AI Shorts Pro 완전 배포 스크립트"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. nginx 설정
echo "1️⃣ nginx 설정 적용 중..."
cp /home/azamans/webapp/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-available/
ln -sf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-enabled/

# 2. nginx 테스트
echo "2️⃣ nginx 설정 테스트..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ nginx 설정 OK"
    
    # 3. nginx reload
    echo "3️⃣ nginx reload..."
    systemctl reload nginx
    echo "✅ nginx reloaded"
else
    echo "❌ nginx 설정 오류!"
    exit 1
fi

# 4. Systemd 서비스 설정
echo "4️⃣ Systemd 서비스 설정..."
cp /home/azamans/webapp/ai-shorts-pro.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable ai-shorts-pro
systemctl restart ai-shorts-pro

# 5. 상태 확인
echo "5️⃣ 서비스 상태 확인..."
systemctl status ai-shorts-pro --no-pager

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ 배포 완료!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🌐 접속 URL:"
echo "   https://ai-shorts.neuralgrid.kr"
echo ""
echo "📡 API 테스트:"
echo "   curl https://ai-shorts.neuralgrid.kr/api/health"
echo ""
echo "🔧 서비스 관리:"
echo "   sudo systemctl status ai-shorts-pro"
echo "   sudo systemctl restart ai-shorts-pro"
echo "   sudo systemctl logs -f ai-shorts-pro"
echo ""
