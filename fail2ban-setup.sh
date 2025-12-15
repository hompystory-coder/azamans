#!/bin/bash

# ============================================
# NeuralGrid DDoS Defense - Fail2ban Setup
# ============================================

set -e

echo "=========================================="
echo "🛡️ Fail2ban 설치 및 설정"
echo "=========================================="

SERVER="115.91.5.140"
USER="azamans"
PASSWORD="7009011226119"

# SSH로 서버 접속해서 설치 및 설정
sshpass -p "$PASSWORD" ssh -tt -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'

echo "📦 1단계: Fail2ban 설치 중..."
echo '7009011226119' | sudo -S apt-get update -qq
echo '7009011226119' | sudo -S apt-get install -y fail2ban

echo ""
echo "📋 2단계: Fail2ban 설정 파일 생성 중..."

# jail.local 생성
echo '7009011226119' | sudo -S tee /etc/fail2ban/jail.local > /dev/null << 'EOF'
[DEFAULT]
# 기본 설정
bantime = 3600
findtime = 600
maxretry = 5
destemail = admin@neuralgrid.kr
sendername = NeuralGrid-Fail2ban
action = %(action_mwl)s

# ============================================
# SSH 보호
# ============================================
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 7200

# ============================================
# Nginx 보호
# ============================================

# HTTP Flood 방어
[nginx-http-flood]
enabled = true
port = http,https
filter = nginx-http-flood
logpath = /var/log/nginx/access.log
maxretry = 100
findtime = 10
bantime = 86400

# Rate Limiting 위반
[nginx-limit-req]
enabled = true
port = http,https
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10
findtime = 10
bantime = 3600

# 404 에러 반복
[nginx-404]
enabled = true
port = http,https
filter = nginx-404
logpath = /var/log/nginx/access.log
maxretry = 10
findtime = 10
bantime = 3600

# Bad Bot 차단
[nginx-bad-bot]
enabled = true
port = http,https
filter = nginx-bad-bot
logpath = /var/log/nginx/access.log
maxretry = 3
findtime = 60
bantime = 86400

# Slowloris 공격 방어
[nginx-slowloris]
enabled = true
port = http,https
filter = nginx-slowloris
logpath = /var/log/nginx/access.log
maxretry = 5
findtime = 30
bantime = 3600

# ============================================
# Auth 서비스 보호
# ============================================
[neuralgrid-auth]
enabled = true
port = http,https
filter = neuralgrid-auth
logpath = /var/log/nginx/access.log
maxretry = 5
findtime = 300
bantime = 1800
EOF

echo ""
echo "🔍 3단계: 필터 파일 생성 중..."

# HTTP Flood 필터
echo '7009011226119' | sudo -S tee /etc/fail2ban/filter.d/nginx-http-flood.conf > /dev/null << 'EOF'
[Definition]
failregex = ^<HOST> -.*"(GET|POST|HEAD).*HTTP.*"
ignoreregex =
EOF

# Rate Limiting 필터
echo '7009011226119' | sudo -S tee /etc/fail2ban/filter.d/nginx-limit-req.conf > /dev/null << 'EOF'
[Definition]
failregex = limiting requests, excess:.* by zone.*client: <HOST>
ignoreregex =
EOF

# 404 에러 필터
echo '7009011226119' | sudo -S tee /etc/fail2ban/filter.d/nginx-404.conf > /dev/null << 'EOF'
[Definition]
failregex = ^<HOST> -.*"(GET|POST|HEAD).*HTTP.* 404
ignoreregex =
EOF

# Bad Bot 필터
echo '7009011226119' | sudo -S tee /etc/fail2ban/filter.d/nginx-bad-bot.conf > /dev/null << 'EOF'
[Definition]
badbots = aggressive|archiver|backdoor|bandwidth|bot|casper|clshttp|cmsworldmap|comodo|copier|cosmos|crawler|curl|disco|downloader|email|extract|flashget|getright|harvest|httrack|libweb|libwww|loader|miner|nikto|nutch|octopus|proxy|python|scanner|scraper|siphon|spider|stripper|sucker|teleport|vampire|wget|winhttp|wwwoffle|zeus|zmeu

failregex = ^<HOST> -.*"(GET|POST).*HTTP.*".*(<badbots>)
ignoreregex = googlebot|bingbot|slackbot|telegrambot
EOF

# Slowloris 필터
echo '7009011226119' | sudo -S tee /etc/fail2ban/filter.d/nginx-slowloris.conf > /dev/null << 'EOF'
[Definition]
failregex = ^<HOST>.*"(GET|POST).*HTTP.* 408
ignoreregex =
EOF

# Auth 서비스 필터
echo '7009011226119' | sudo -S tee /etc/fail2ban/filter.d/neuralgrid-auth.conf > /dev/null << 'EOF'
[Definition]
failregex = ^<HOST> -.*POST /api/auth/(login|register).*HTTP.* (401|403|422)
ignoreregex =
EOF

echo ""
echo "🔧 4단계: Fail2ban 서비스 시작 중..."
echo '7009011226119' | sudo -S systemctl enable fail2ban
echo '7009011226119' | sudo -S systemctl restart fail2ban

echo ""
echo "✅ 5단계: 상태 확인 중..."
echo '7009011226119' | sudo -S fail2ban-client status

echo ""
echo "=========================================="
echo "🎉 Fail2ban 설치 및 설정 완료!"
echo "=========================================="
echo ""
echo "📊 사용 가능한 명령어:"
echo "  - fail2ban-client status: 전체 상태 확인"
echo "  - fail2ban-client status nginx-limit-req: 특정 jail 상태"
echo "  - fail2ban-client set nginx-limit-req banip <IP>: IP 수동 차단"
echo "  - fail2ban-client set nginx-limit-req unbanip <IP>: IP 차단 해제"
echo ""

exit
ENDSSH

echo ""
echo "✅ Fail2ban 설치 완료!"
