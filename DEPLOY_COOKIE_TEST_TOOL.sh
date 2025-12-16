#!/bin/bash
#
# Cookie 테스트 도구 배포 스크립트
# 서버 115.91.5.140에서 실행
#

set -e

echo "================================================"
echo "Cookie 테스트 도구 배포"
echo "================================================"
echo ""

# 색상 정의
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Git 업데이트
echo -e "${YELLOW}[1/3] Git 저장소 업데이트...${NC}"
cd /home/azamans/webapp
git pull origin genspark_ai_developer_clean
echo -e "${GREEN}✓ Git 업데이트 완료${NC}"
echo ""

# 2. Auth 도메인에 배포
echo -e "${YELLOW}[2/3] Auth 도메인에 배포...${NC}"
sudo cp cookie-test.html /var/www/auth.neuralgrid.kr/
sudo chown www-data:www-data /var/www/auth.neuralgrid.kr/cookie-test.html
echo -e "${GREEN}✓ Auth 도메인 배포 완료${NC}"
echo "  → https://auth.neuralgrid.kr/cookie-test.html"
echo ""

# 3. DDoS 도메인에 배포
echo -e "${YELLOW}[3/3] DDoS 도메인에 배포...${NC}"
sudo cp cookie-test.html /var/www/ddos.neuralgrid.kr/
sudo chown www-data:www-data /var/www/ddos.neuralgrid.kr/cookie-test.html
echo -e "${GREEN}✓ DDoS 도메인 배포 완료${NC}"
echo "  → https://ddos.neuralgrid.kr/cookie-test.html"
echo ""

echo "================================================"
echo -e "${GREEN}배포 완료!${NC}"
echo "================================================"
echo ""
echo "브라우저에서 다음 URL로 접속하세요:"
echo ""
echo "  1. https://auth.neuralgrid.kr/cookie-test.html"
echo "  2. https://ddos.neuralgrid.kr/cookie-test.html"
echo ""
echo "테스트 방법:"
echo "  1. 위 URL 중 하나를 시크릿 모드로 열기"
echo "  2. '🚀 모든 테스트 실행' 버튼 클릭"
echo "  3. 결과 확인"
echo ""
