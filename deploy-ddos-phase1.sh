#!/bin/bash
# DDoS 보안 플랫폼 Phase 1 배포 스크립트

echo "======================================"
echo "DDoS Security Platform Phase 1 배포"
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
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$(date +%Y%m%d-%H%M%S)
echo "✅ 백업 완료"

# 배포
echo "🚀 새 파일 배포 중..."
sudo cp ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js
sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js
echo "✅ 파일 배포 완료"

# PM2 재시작
echo "♻️ PM2 프로세스 재시작 중..."
pm2 restart ddos-security
echo "✅ PM2 재시작 완료"

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
echo "1. 메인 페이지 확인:"
curl -I https://ddos.neuralgrid.kr/ 2>&1 | head -1

echo ""
echo "2. 등록 페이지 확인:"
curl -I https://ddos.neuralgrid.kr/register.html 2>&1 | head -1

echo ""
echo "3. API 헬스 체크:"
curl -s http://localhost:3105/health 2>&1

echo ""
echo "✅ 배포 완료!"
echo ""
echo "🌐 확인 URL:"
echo "   - 메인: https://ddos.neuralgrid.kr/"
echo "   - 등록: https://ddos.neuralgrid.kr/register.html"
echo ""
echo "📝 변경사항:"
echo "   - SSO 통합 서버 등록 API 구축"
echo "   - 무료 체험 vs. 프리미엄 플랜 선택 UI"
echo "   - API Key 자동 발급 시스템"
echo "   - 자동 설치 스크립트 생성기"
echo "======================================"
