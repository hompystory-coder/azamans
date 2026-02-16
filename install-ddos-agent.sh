#!/bin/bash

# ============================================
# DDoS 방어 Agent 자동 설치 스크립트
# ============================================

set -e

# 색상 코드
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  DDoS 방어 Agent 설치 스크립트${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Root 권한 확인
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ 이 스크립트는 root 권한이 필요합니다.${NC}"
    echo -e "${YELLOW}   sudo ./install-ddos-agent.sh 로 실행하세요.${NC}"
    exit 1
fi

# 중앙 서버 URL 입력
read -p "중앙 서버 URL (예: https://ddos.neuralgrid.kr): " CENTRAL_SERVER
if [ -z "$CENTRAL_SERVER" ]; then
    CENTRAL_SERVER="https://ddos.neuralgrid.kr"
fi

# API Key 입력
read -p "API Key (보안을 위해 필요): " API_KEY
if [ -z "$API_KEY" ]; then
    API_KEY="default-api-key"
    echo -e "${YELLOW}⚠️  기본 API Key를 사용합니다. 보안을 위해 나중에 변경하세요.${NC}"
fi

echo ""
echo -e "${GREEN}[1/6] Agent 스크립트 다운로드 중...${NC}"
curl -fsSL "$CENTRAL_SERVER/scripts/ddos-agent.sh" -o /usr/local/bin/ddos-agent.sh || {
    echo -e "${YELLOW}⚠️  다운로드 실패. 로컬 파일을 사용합니다.${NC}"
    # 대체 방법: GitHub에서 다운로드
    curl -fsSL "https://raw.githubusercontent.com/hompystory-coder/azamans/main/ddos-agent.sh" -o /usr/local/bin/ddos-agent.sh || {
        echo -e "${RED}❌ Agent 스크립트를 다운로드할 수 없습니다.${NC}"
        exit 1
    }
}

echo -e "${GREEN}[2/6] 설정 파일 업데이트 중...${NC}"
# Agent 스크립트에 중앙 서버 URL과 API Key 설정
sed -i "s|CENTRAL_SERVER=\".*\"|CENTRAL_SERVER=\"$CENTRAL_SERVER\"|" /usr/local/bin/ddos-agent.sh
sed -i "s|API_KEY=\".*\"|API_KEY=\"$API_KEY\"|" /usr/local/bin/ddos-agent.sh

echo -e "${GREEN}[3/6] 실행 권한 설정 중...${NC}"
chmod +x /usr/local/bin/ddos-agent.sh

echo -e "${GREEN}[4/6] Systemd 서비스 생성 중...${NC}"
cat > /etc/systemd/system/ddos-agent.service << 'EOF'
[Unit]
Description=DDoS Defense Agent
Documentation=https://ddos.neuralgrid.kr/docs
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/bin/ddos-agent.sh
Restart=always
RestartSec=10
StandardOutput=append:/var/log/ddos-agent.log
StandardError=append:/var/log/ddos-agent.log

# 보안 설정
NoNewPrivileges=true
PrivateTmp=true

[Install]
WantedBy=multi-user.target
EOF

echo -e "${GREEN}[5/6] Fail2ban sudo 권한 설정 중...${NC}"
# fail2ban-client를 sudo 없이 실행할 수 있도록 설정
if ! grep -q "root ALL=(ALL) NOPASSWD: /usr/bin/fail2ban-client" /etc/sudoers 2>/dev/null; then
    echo "root ALL=(ALL) NOPASSWD: /usr/bin/fail2ban-client" >> /etc/sudoers.d/ddos-agent
    chmod 440 /etc/sudoers.d/ddos-agent
fi

echo -e "${GREEN}[6/6] 서비스 시작 중...${NC}"
systemctl daemon-reload
systemctl enable ddos-agent
systemctl start ddos-agent

# 설치 완료 확인
sleep 2
if systemctl is-active --quiet ddos-agent; then
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}✅ 설치가 완료되었습니다!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "${BLUE}📊 서비스 상태:${NC}"
    systemctl status ddos-agent --no-pager -l
    echo ""
    echo -e "${BLUE}📝 로그 확인:${NC}"
    echo "  tail -f /var/log/ddos-agent.log"
    echo ""
    echo -e "${BLUE}🔧 서비스 관리:${NC}"
    echo "  systemctl status ddos-agent    # 상태 확인"
    echo "  systemctl restart ddos-agent   # 재시작"
    echo "  systemctl stop ddos-agent      # 중지"
    echo ""
    echo -e "${BLUE}🌐 대시보드:${NC}"
    echo "  $CENTRAL_SERVER"
    echo ""
else
    echo ""
    echo -e "${RED}========================================${NC}"
    echo -e "${RED}❌ 설치 중 오류가 발생했습니다.${NC}"
    echo -e "${RED}========================================${NC}"
    echo ""
    echo -e "${YELLOW}로그를 확인하세요:${NC}"
    echo "  journalctl -u ddos-agent -n 50"
    echo ""
    exit 1
fi
