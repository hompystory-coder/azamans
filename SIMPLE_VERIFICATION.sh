#!/bin/bash
# Phase 2 간단 검증 스크립트

echo "============================================"
echo "🔍 Phase 2 배포 검증"
echo "============================================"
echo ""

echo "1️⃣ PM2 프로세스 상태:"
pm2 list | grep ddos-security
echo ""

echo "2️⃣ API 서버 헬���체크:"
curl -s http://localhost:3105/health | jq '.' 2>/dev/null || curl -s http://localhost:3105/health
echo ""
echo ""

echo "3️⃣ MyPage 파일 존재 확인:"
ls -lh /var/www/ddos.neuralgrid.kr/mypage.html 2>/dev/null && echo "✅ MyPage 파일 존재" || echo "❌ MyPage 파일 없음"
echo ""

echo "4️⃣ Auth 대시보드 DDoS 링크 확인:"
grep -o 'href="https://ddos.neuralgrid.kr/[^"]*"' /var/www/auth.neuralgrid.kr/dashboard.html | grep ddos
echo ""

echo "============================================"
echo "✅ 검증 완료!"
echo "============================================"
echo ""
echo "📌 MyPage 접근 URL:"
echo "   https://ddos.neuralgrid.kr/mypage.html"
echo ""
echo "⚠️  브라우저 캐시 문제가 있다면:"
echo "   - Ctrl+Shift+R (강력 새로고침)"
echo "   - 시크릿 모드로 접속"
