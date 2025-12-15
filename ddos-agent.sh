#!/bin/bash

# ============================================
# DDoS 방어 Agent 스크립트
# 각 서버에 설치하여 중앙 서버로 데이터 전송
# ============================================

# 설정 (사용자가 수정해야 할 부분)
CENTRAL_SERVER="https://ddos.neuralgrid.kr"
API_KEY="your-api-key-here"  # 보안을 위해 API Key 사용
SERVER_ID="server-$(hostname)"
SERVER_NAME="$(hostname)"
SERVER_IP="$(hostname -I | awk '{print $1}')"

# 색상 코드
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# 로그 파일
LOG_FILE="/var/log/ddos-agent.log"

# 로깅 함수
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}  DDoS 방어 Agent v1.0${NC}"
echo -e "${BLUE}  서버: $SERVER_NAME ($SERVER_IP)${NC}"
echo -e "${BLUE}==================================${NC}"

# 서버 등록
register_server() {
    log "서버 등록 시작..."
    
    RESPONSE=$(curl -s -X POST "$CENTRAL_SERVER/api/server/register" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: $API_KEY" \
        -d "{
            \"serverId\": \"$SERVER_ID\",
            \"serverName\": \"$SERVER_NAME\",
            \"serverIp\": \"$SERVER_IP\"
        }")
    
    if echo "$RESPONSE" | grep -q '"success":true'; then
        log "✅ 서버 등록 완료"
    else
        log "⚠️  서버 등록 실패 또는 이미 등록됨"
    fi
}

# 트래픽 통계 수집
get_traffic_stats() {
    # Nginx 로그가 있는지 확인
    if [ -f /var/log/nginx/access.log ]; then
        # 최근 1분간 로그 분석 (마지막 1000줄)
        TOTAL_REQUESTS=$(tail -1000 /var/log/nginx/access.log 2>/dev/null | wc -l)
        REQUESTS_PER_SEC=$((TOTAL_REQUESTS / 60))
        
        # Rate limiting 차단 수
        if [ -f /var/log/nginx/error.log ]; then
            BLOCKED=$(tail -1000 /var/log/nginx/error.log 2>/dev/null | grep -c "limiting requests")
        else
            BLOCKED=0
        fi
        
        NORMAL=$((TOTAL_REQUESTS - BLOCKED))
    else
        TOTAL_REQUESTS=0
        REQUESTS_PER_SEC=0
        BLOCKED=0
        NORMAL=0
    fi
    
    echo "{\"totalRequests\": $TOTAL_REQUESTS, \"requestsPerSecond\": $REQUESTS_PER_SEC, \"blockedTraffic\": $BLOCKED, \"normalTraffic\": $NORMAL}"
}

# Fail2ban 차단 IP 수집
get_blocked_ips() {
    # Fail2ban이 설치되어 있고 실행 중인지 확인
    if command -v fail2ban-client &> /dev/null && systemctl is-active --quiet fail2ban; then
        # 모든 jail에서 차단된 IP 수 합계
        TOTAL_BANNED=0
        
        # 활성화된 jail 목록
        JAILS=$(sudo fail2ban-client status 2>/dev/null | grep "Jail list" | sed 's/.*://; s/,//g')
        
        if [ -n "$JAILS" ]; then
            for JAIL in $JAILS; do
                BANNED=$(sudo fail2ban-client status "$JAIL" 2>/dev/null | grep "Currently banned" | awk '{print $4}')
                TOTAL_BANNED=$((TOTAL_BANNED + ${BANNED:-0}))
            done
        fi
        
        echo "{\"count\": $TOTAL_BANNED, \"jails\": \"$JAILS\"}"
    else
        echo "{\"count\": 0, \"jails\": \"none\"}"
    fi
}

# 시스템 상태 수집
get_system_status() {
    # CPU 부하
    LOAD=$(cat /proc/loadavg | awk '{print $1}')
    
    # 메모리 사용률
    MEMORY=$(free | grep Mem | awk '{printf "%.2f", ($3/$2) * 100}')
    
    # 업타임
    UPTIME=$(uptime -p | sed 's/up //')
    
    # 디스크 사용률
    DISK=$(df -h / | tail -1 | awk '{print $5}' | sed 's/%//')
    
    # CPU 사용률 (간단한 방법)
    CPU=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//')
    
    echo "{\"load\": $LOAD, \"memory\": $MEMORY, \"uptime\": \"$UPTIME\", \"disk\": $DISK, \"cpu\": ${CPU:-0}, \"status\": \"normal\"}"
}

# 데이터 전송
send_stats() {
    log "데이터 수집 중..."
    
    TRAFFIC=$(get_traffic_stats)
    BLOCKED_IPS=$(get_blocked_ips)
    SYSTEM_STATUS=$(get_system_status)
    
    log "중앙 서버로 전송 중..."
    
    RESPONSE=$(curl -s -X POST "$CENTRAL_SERVER/api/server/$SERVER_ID/stats" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: $API_KEY" \
        -d "{
            \"traffic\": $TRAFFIC,
            \"blockedIPs\": $BLOCKED_IPS,
            \"systemStatus\": $SYSTEM_STATUS,
            \"timestamp\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"
        }" 2>&1)
    
    if echo "$RESPONSE" | grep -q '"success":true'; then
        log "✅ 데이터 전송 성공"
    else
        log "❌ 데이터 전송 실패: $RESPONSE"
    fi
}

# Health check
health_check() {
    # 중앙 서버가 살아있는지 확인
    if curl -s --max-time 5 "$CENTRAL_SERVER/api/status" > /dev/null 2>&1; then
        return 0
    else
        log "⚠️  중앙 서버 연결 불가"
        return 1
    fi
}

# 메인 루프
main() {
    # 서버 등록 (최초 1회)
    register_server
    
    # 초기 health check
    if ! health_check; then
        log "❌ 중앙 서버에 연결할 수 없습니다. 설정을 확인하세요."
        log "   CENTRAL_SERVER: $CENTRAL_SERVER"
        exit 1
    fi
    
    log "📊 모니터링 시작..."
    
    # 무한 루프로 데이터 전송 (30초마다)
    while true; do
        send_stats
        sleep 30
    done
}

# 시그널 핸들링 (Ctrl+C 등)
trap 'log "Agent 종료"; exit 0' SIGINT SIGTERM

# 실행
main
