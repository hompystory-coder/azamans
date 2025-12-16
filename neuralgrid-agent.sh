#!/bin/bash

#==============================================================================
# NeuralGrid DDoS Protection Agent
# Version: 1.0.0
# Description: 실시간 DDoS 탐지 및 자동 차단 에이전트
#==============================================================================

set -e

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 설정
INSTALL_DIR="/opt/neuralgrid"
CONFIG_FILE="$INSTALL_DIR/config.json"
LOG_FILE="$INSTALL_DIR/logs/agent.log"
BLOCKED_IPS_FILE="$INSTALL_DIR/rules/blocked-ips.txt"
SERVICE_NAME="neuralgrid-agent"
API_ENDPOINT="https://ddos.neuralgrid.kr"

# API Key (설치 시 파라미터로 전달)
API_KEY="${1:-}"

#==============================================================================
# 유틸리티 함수
#==============================================================================

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE" 2>/dev/null || true
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
    echo "[ERROR] $1" >> "$LOG_FILE" 2>/dev/null || true
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
    echo "[WARNING] $1" >> "$LOG_FILE" 2>/dev/null || true
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

#==============================================================================
# OS 감지
#==============================================================================

detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        VERSION=$VERSION_ID
    elif [ -f /etc/redhat-release ]; then
        OS="centos"
        VERSION=$(cat /etc/redhat-release | grep -oE '[0-9]+\.[0-9]+' | head -1)
    else
        error "지원하지 않는 운영체제입니다."
        exit 1
    fi
    
    log "OS 감지: $OS $VERSION"
    echo "$OS"
}

#==============================================================================
# 방화벽 감지
#==============================================================================

detect_firewall() {
    if command -v ufw &> /dev/null; then
        echo "ufw"
    elif command -v firewall-cmd &> /dev/null; then
        echo "firewalld"
    elif command -v iptables &> /dev/null; then
        echo "iptables"
    else
        echo "none"
    fi
}

#==============================================================================
# 설치 함수
#==============================================================================

install_agent() {
    log "=== NeuralGrid 에이전트 설치 시작 ==="
    
    # API Key 확인
    if [ -z "$API_KEY" ]; then
        error "API Key가 필요합니다. 사용법: $0 <API_KEY>"
        exit 1
    fi
    
    # Root 권한 확인
    if [ "$EUID" -ne 0 ]; then 
        error "Root 권한이 필요합니다. sudo를 사용하세요."
        exit 1
    fi
    
    # OS 감지
    OS_TYPE=$(detect_os)
    FIREWALL_TYPE=$(detect_firewall)
    
    log "방화벽 타입: $FIREWALL_TYPE"
    
    # 디렉토리 생성
    log "디렉토리 생성 중..."
    mkdir -p "$INSTALL_DIR"
    mkdir -p "$INSTALL_DIR/logs"
    mkdir -p "$INSTALL_DIR/rules"
    
    # 설정 파일 생성
    log "설정 파일 생성 중..."
    cat > "$CONFIG_FILE" << CONFIG_JSON
{
  "apiKey": "$API_KEY",
  "apiEndpoint": "$API_ENDPOINT",
  "osType": "$OS_TYPE",
  "firewallType": "$FIREWALL_TYPE",
  "checkInterval": 300,
  "maxRequestsPerMinute": 100,
  "maxRequestsPerIP": 20,
  "autoBlockDuration": 60,
  "logFiles": [
    "/var/log/nginx/access.log",
    "/var/log/apache2/access.log",
    "/var/log/httpd/access_log"
  ]
}
CONFIG_JSON
    
    # 모니터링 스크립트 생성
    log "모니터링 스크립트 생성 중..."
    create_monitor_script
    
    # Systemd 서비스 생성
    log "Systemd 서비스 생성 중..."
    create_systemd_service
    
    # 서비스 시작
    log "서비스 시작 중..."
    systemctl daemon-reload
    systemctl enable "$SERVICE_NAME"
    systemctl start "$SERVICE_NAME"
    
    log "=== 설치 완료! ==="
    info ""
    info "✅ NeuralGrid 에이전트가 성공적으로 설치되었습니다!"
    info ""
    info "📍 설치 위치: $INSTALL_DIR"
    info "📝 로그 파일: $LOG_FILE"
    info "⚙️  설정 파일: $CONFIG_FILE"
    info ""
    info "🔍 상태 확인: systemctl status $SERVICE_NAME"
    info "📊 로그 확인: tail -f $LOG_FILE"
    info ""
}

#==============================================================================
# 모니터링 스크립트 생성
#==============================================================================

create_monitor_script() {
    cat > "$INSTALL_DIR/monitor.sh" << 'MONITOR_EOF'
#!/bin/bash

CONFIG_FILE="/opt/neuralgrid/config.json"
LOG_FILE="/opt/neuralgrid/logs/agent.log"
BLOCKED_IPS_FILE="/opt/neuralgrid/rules/blocked-ips.txt"

# 설정 로드
API_KEY=$(jq -r '.apiKey' "$CONFIG_FILE" 2>/dev/null || echo "")
API_ENDPOINT=$(jq -r '.apiEndpoint' "$CONFIG_FILE" 2>/dev/null || echo "https://ddos.neuralgrid.kr")
FIREWALL_TYPE=$(jq -r '.firewallType' "$CONFIG_FILE" 2>/dev/null || echo "iptables")

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# IP 차단 함수
block_ip() {
    local ip=$1
    local reason=${2:-"Auto-blocked"}
    
    # 이미 차단된 IP 확인
    if grep -q "^$ip$" "$BLOCKED_IPS_FILE" 2>/dev/null; then
        return 0
    fi
    
    # 방화벽 규칙 추가
    case $FIREWALL_TYPE in
        "ufw")
            ufw deny from "$ip" 2>/dev/null
            ;;
        "firewalld")
            firewall-cmd --permanent --add-rich-rule="rule family='ipv4' source address='$ip' reject" 2>/dev/null
            firewall-cmd --reload 2>/dev/null
            ;;
        "iptables")
            iptables -I INPUT -s "$ip" -j DROP 2>/dev/null
            ;;
    esac
    
    # 차단 목록에 추가
    echo "$ip" >> "$BLOCKED_IPS_FILE"
    log "⛔ IP 차단: $ip (사유: $reason)"
    
    # 중앙 서버에 알림
    if [ -n "$API_KEY" ]; then
        curl -s -X POST "$API_ENDPOINT/api/agent/block-notification" \
            -H "Authorization: Bearer $API_KEY" \
            -H "Content-Type: application/json" \
            -d "{\"ip\":\"$ip\",\"reason\":\"$reason\"}" >/dev/null 2>&1 || true
    fi
}

# 로그 분석 및 공격 탐지
analyze_logs() {
    local log_file=$1
    
    if [ ! -f "$log_file" ]; then
        return
    fi
    
    # 최근 1분간의 로그 분석
    local cutoff_time=$(date -d '1 minute ago' '+%d/%b/%Y:%H:%M' 2>/dev/null || date -v-1M '+%d/%b/%Y:%H:%M' 2>/dev/null)
    
    # IP별 요청 수 카운트
    awk -v cutoff="$cutoff_time" '
    $4 > "["cutoff {
        match($0, /([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/, arr)
        if (arr[1]) {
            count[arr[1]]++
        }
    }
    END {
        for (ip in count) {
            if (count[ip] > 20) {
                print ip, count[ip]
            }
        }
    }
    ' "$log_file" | while read ip count; do
        block_ip "$ip" "DDoS detected (${count} requests/min)"
    done
}

# 통계 수집 및 전송
send_stats() {
    if [ -z "$API_KEY" ]; then
        return
    fi
    
    # 통계 데이터 수집
    local total_requests=$(grep -c '' /var/log/nginx/access.log 2>/dev/null || echo 0)
    local blocked_count=$(wc -l < "$BLOCKED_IPS_FILE" 2>/dev/null || echo 0)
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
    local memory_usage=$(free | grep Mem | awk '{print int($3/$2 * 100)}')
    
    # API로 전송
    curl -s -X POST "$API_ENDPOINT/api/agent/stats" \
        -H "Authorization: Bearer $API_KEY" \
        -H "Content-Type: application/json" \
        -d "{
            \"totalRequests\": $total_requests,
            \"blockedIPs\": $blocked_count,
            \"cpu\": $cpu_usage,
            \"memory\": $memory_usage,
            \"timestamp\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"
        }" >/dev/null 2>&1 || true
}

# 메인 루프
log "📊 모니터링 시작..."

while true; do
    # 로그 파일 분석
    for log_file in /var/log/nginx/access.log /var/log/apache2/access.log /var/log/httpd/access_log; do
        analyze_logs "$log_file"
    done
    
    # 5분마다 통계 전송
    if [ $(($(date +%s) % 300)) -eq 0 ]; then
        send_stats
        log "📈 통계 전송 완료"
    fi
    
    # 10초 대기
    sleep 10
done
MONITOR_EOF
    
    chmod +x "$INSTALL_DIR/monitor.sh"
}

#==============================================================================
# Systemd 서비스 생성
#==============================================================================

create_systemd_service() {
    cat > "/etc/systemd/system/$SERVICE_NAME.service" << SERVICE_EOF
[Unit]
Description=NeuralGrid DDoS Protection Agent
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=$INSTALL_DIR
ExecStart=$INSTALL_DIR/monitor.sh
Restart=always
RestartSec=10
StandardOutput=append:$LOG_FILE
StandardError=append:$LOG_FILE

[Install]
WantedBy=multi-user.target
SERVICE_EOF
}

#==============================================================================
# 제거 함수
#==============================================================================

uninstall_agent() {
    log "=== NeuralGrid 에이전트 제거 시작 ==="
    
    # 서비스 중지 및 제거
    systemctl stop "$SERVICE_NAME" 2>/dev/null || true
    systemctl disable "$SERVICE_NAME" 2>/dev/null || true
    rm -f "/etc/systemd/system/$SERVICE_NAME.service"
    systemctl daemon-reload
    
    # 파일 제거
    rm -rf "$INSTALL_DIR"
    
    log "=== 제거 완료 ==="
}

#==============================================================================
# 메인 실행
#==============================================================================

case "${2:-install}" in
    install)
        install_agent
        ;;
    uninstall)
        uninstall_agent
        ;;
    *)
        echo "사용법: $0 <API_KEY> [install|uninstall]"
        exit 1
        ;;
esac
