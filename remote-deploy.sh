#!/bin/bash
# 서버에서 GitHub에서 직접 다운로드하여 배포하는 스크립트
# 사용법: curl -fsSL https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/remote-deploy.sh | bash

echo "======================================"
echo "🚀 NeuralGrid DDoS Platform Phase 1"
echo "   원격 자동 배포 스크립트"
echo "======================================"
echo ""

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 배포 디렉토리
DEPLOY_DIR="/var/www/ddos.neuralgrid.kr"
TEMP_DIR="/tmp/neuralgrid-deploy-$$"

# GitHub 원본 URL
GITHUB_RAW="https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean"

echo -e "${YELLOW}📦 준비 중...${NC}"

# 임시 디렉토리 생성
mkdir -p "$TEMP_DIR"
cd "$TEMP_DIR" || exit 1

echo -e "${YELLOW}⬇️  파일 다운로드 중...${NC}"

# 파일 다운로드
curl -fsSL "$GITHUB_RAW/ddos-security-platform-server.js" -o server.js
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ server.js 다운로드 실패${NC}"
    exit 1
fi
echo -e "${GREEN}✅ server.js 다운로드 완료${NC}"

curl -fsSL "$GITHUB_RAW/ddos-register.html" -o register.html
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ register.html 다운로드 실패${NC}"
    exit 1
fi
echo -e "${GREEN}✅ register.html 다운로드 완료${NC}"

echo ""
echo -e "${YELLOW}💾 백업 중...${NC}"

# 기존 파일 백업
if [ -f "$DEPLOY_DIR/server.js" ]; then
    sudo cp "$DEPLOY_DIR/server.js" "$DEPLOY_DIR/server.js.backup-$(date +%Y%m%d-%H%M%S)"
    echo -e "${GREEN}✅ 기존 파일 백업 완료${NC}"
fi

echo ""
echo -e "${YELLOW}🚀 배포 중...${NC}"

# 파일 배포
sudo cp server.js "$DEPLOY_DIR/server.js"
sudo cp register.html "$DEPLOY_DIR/register.html"

# 권한 설정
sudo chown -R azamans:azamans "$DEPLOY_DIR/"
sudo chmod 644 "$DEPLOY_DIR/"*.html
sudo chmod 644 "$DEPLOY_DIR/server.js"

echo -e "${GREEN}✅ 파일 배포 완료${NC}"

echo ""
echo -e "${YELLOW}♻️  서비스 재시작 중...${NC}"

# PM2 재시작
pm2 restart ddos-security >/dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ PM2 프로세스 재시작 완료${NC}"
else
    echo -e "${YELLOW}⚠️  PM2 재시작 실패 (수동으로 재시작 필요)${NC}"
fi

# 정리
cd /
rm -rf "$TEMP_DIR"

echo ""
echo "======================================"
echo -e "${GREEN}✅ 배포 완료!${NC}"
echo "======================================"
echo ""
echo "🔍 검증 중..."
echo ""

# 헬스 체크
HEALTH=$(curl -s http://localhost:3105/health 2>/dev/null)
if [ -n "$HEALTH" ]; then
    echo -e "${GREEN}✅ API 서버 정상 작동: $HEALTH${NC}"
else
    echo -e "${RED}❌ API 서버 응답 없음${NC}"
fi

echo ""
echo "🌐 확인 URL:"
echo "   - 등록 페이지: https://ddos.neuralgrid.kr/register.html"
echo "   - 메인 대시보드: https://ddos.neuralgrid.kr/"
echo ""
echo "📝 변경사항:"
echo "   - SSO 통합 서버 등록 API"
echo "   - 무료 체험 vs 프리미엄 플랜 UI"
echo "   - API Key 자동 발급 시스템"
echo "   - 멀티 플랫폼 방화벽 지원"
echo ""
echo "======================================"
