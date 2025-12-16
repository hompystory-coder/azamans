#!/bin/bash
#
# Cookie SSO 배포 스크립트
# 서버 관리자가 115.91.5.140 서버에서 직접 실행
#
# 실행 방법:
#   chmod +x DEPLOY_COOKIE_SSO.sh
#   ./DEPLOY_COOKIE_SSO.sh
#

set -e

echo "================================================"
echo "Cookie SSO 배포 스크립트"
echo "================================================"
echo ""

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Git 저장소 업데이트
echo -e "${YELLOW}[1/5] Git 저장소 업데이트...${NC}"
cd /home/azamans/webapp
git fetch origin
git checkout genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean
echo -e "${GREEN}✓ Git 업데이트 완료${NC}"
echo ""

# 2. Auth 서비스 배포
echo -e "${YELLOW}[2/5] Auth 서비스 배포...${NC}"
sudo cp /home/azamans/webapp/auth-login-updated.html /var/www/auth.neuralgrid.kr/index.html
sudo cp /home/azamans/webapp/auth-dashboard-updated.html /var/www/auth.neuralgrid.kr/dashboard.html
sudo chown -R www-data:www-data /var/www/auth.neuralgrid.kr/
echo -e "${GREEN}✓ Auth 서비스 배포 완료${NC}"
echo ""

# 3. DDoS 서비스 배포
echo -e "${YELLOW}[3/5] DDoS 서비스 배포...${NC}"
sudo cp /home/azamans/webapp/ddos-mypage.html /var/www/ddos.neuralgrid.kr/mypage.html
sudo chown -R www-data:www-data /var/www/ddos.neuralgrid.kr/
echo -e "${GREEN}✓ DDoS 서비스 배포 완료${NC}"
echo ""

# 4. Nginx 설정 확인
echo -e "${YELLOW}[4/5] Nginx 설정 확인...${NC}"
sudo nginx -t
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Nginx 설정 정상${NC}"
else
    echo -e "${RED}✗ Nginx 설정 오류${NC}"
    exit 1
fi
echo ""

# 5. Nginx 리로드
echo -e "${YELLOW}[5/5] Nginx 리로드...${NC}"
sudo systemctl reload nginx
echo -e "${GREEN}✓ Nginx 리로드 완료${NC}"
echo ""

# 배포 확인
echo "================================================"
echo -e "${GREEN}배포 완료!${NC}"
echo "================================================"
echo ""
echo "다음 명령어로 Cookie SSO 코드가 배포되었는지 확인하세요:"
echo ""
echo "  # Auth 로그인 페이지 확인"
echo "  curl -s https://auth.neuralgrid.kr/ | grep -o 'neuralgrid_token' | head -1"
echo ""
echo "  # DDoS MyPage 확인"
echo "  curl -s https://ddos.neuralgrid.kr/mypage.html | grep -o 'getCookie' | head -1"
echo ""
echo "브라우저 테스트:"
echo "  1. https://auth.neuralgrid.kr/ 접속"
echo "  2. 로그인"
echo "  3. 개발자도구 > Application > Cookies 확인"
echo "     → neuralgrid_token, neuralgrid_user 쿠키가 .neuralgrid.kr 도메인에 생성되어야 함"
echo "  4. Auth 대시보드에서 'DDoS 보안 플랫폼' 클릭"
echo "  5. MyPage로 이동 시 로그인 없이 바로 표시되어야 함"
echo ""
