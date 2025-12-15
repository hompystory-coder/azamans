# 🌐 DDoS 방어 시스템 - 다중 서버 배포 가이드

## 📊 아키텍처 개요

```
┌─────────────────────────────────────────────────────────────┐
│                 중앙 DDoS 방어 대시보드                        │
│              https://ddos.neuralgrid.kr                      │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  서버 A 현황  │  │  서버 B 현황  │  │  서버 C 현황  │      │
│  │  • 트래픽    │  │  • 트래픽    │  │  • 트래픽    │      │
│  │  • 차단 IP   │  │  • 차단 IP   │  │  • 차단 IP   │      │
│  │  • 공격 감지  │  │  • 공격 감지  │  │  • 공격 감지  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                           ▲  ▲  ▲
                           │  │  │
              ┌────────────┘  │  └────────────┐
              │               │               │
    ┌─────────▼─────────┐ ┌──▼──────────┐ ┌──▼──────────┐
    │   서버 A          │ │  서버 B      │ │  서버 C      │
    │ 192.168.1.10      │ │ 192.168.1.20 │ │ 192.168.1.30 │
    │                   │ │             │ │             │
    │ [Agent 스크립트]   │ │ [Agent]     │ │ [Agent]     │
    │   • Nginx 로그    │ │  • 로그     │ │  • 로그     │
    │   • Fail2ban      │ │  • 통계     │ │  • 통계     │
    │   • 시스템 상태    │ │  • 상태     │ │  • 상태     │
    └───────────────────┘ └─────────────┘ └─────────────┘
```

---

## 🎯 방법 1: SaaS 형태 (추천) - 중앙 집중식

### 1️⃣ 중앙 서버 API 확장

#### A. 다중 서버 지원 API 추가

**파일**: `ddos-defense-server.js` 에 추가

```javascript
// 서버 등록 및 데이터 수신 API
const servers = new Map(); // 서버 ID -> 데이터 저장

// 서버 등록
app.post('/api/server/register', (req, res) => {
    const { serverId, serverName, serverIp } = req.body;
    
    servers.set(serverId, {
        id: serverId,
        name: serverName,
        ip: serverIp,
        lastSeen: new Date(),
        status: 'online'
    });
    
    res.json({ success: true, message: 'Server registered' });
});

// 서버 데이터 수신
app.post('/api/server/:serverId/stats', (req, res) => {
    const { serverId } = req.params;
    const { traffic, blockedIPs, systemStatus } = req.body;
    
    // 서버별 데이터 저장
    if (!servers.has(serverId)) {
        return res.status(404).json({ error: 'Server not registered' });
    }
    
    const server = servers.get(serverId);
    server.traffic = traffic;
    server.blockedIPs = blockedIPs;
    server.systemStatus = systemStatus;
    server.lastSeen = new Date();
    
    servers.set(serverId, server);
    
    res.json({ success: true });
});

// 전체 서버 상태 조회
app.get('/api/servers', (req, res) => {
    const serverList = Array.from(servers.values()).map(server => ({
        ...server,
        isOnline: (new Date() - server.lastSeen) < 60000 // 1분 이내
    }));
    
    res.json(serverList);
});

// 특정 서버 상세 정보
app.get('/api/server/:serverId', (req, res) => {
    const { serverId } = req.params;
    
    if (!servers.has(serverId)) {
        return res.status(404).json({ error: 'Server not found' });
    }
    
    res.json(servers.get(serverId));
});
```

---

### 2️⃣ 각 서버에 Agent 스크립트 배포

#### B. Agent 스크립트 생성

**파일**: `ddos-agent.sh`

```bash
#!/bin/bash

# ============================================
# DDoS 방어 Agent 스크립트
# ============================================

# 설정
CENTRAL_SERVER="https://ddos.neuralgrid.kr"
SERVER_ID="server-$(hostname)"
SERVER_NAME="$(hostname)"
SERVER_IP="$(hostname -I | awk '{print $1}')"

# 색상 코드
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}  DDoS 방어 Agent 시작${NC}"
echo -e "${BLUE}==================================${NC}"

# 서버 등록
register_server() {
    echo -e "${GREEN}[1/3] 서버 등록 중...${NC}"
    
    curl -s -X POST "$CENTRAL_SERVER/api/server/register" \
        -H "Content-Type: application/json" \
        -d "{
            \"serverId\": \"$SERVER_ID\",
            \"serverName\": \"$SERVER_NAME\",
            \"serverIp\": \"$SERVER_IP\"
        }"
    
    echo -e "${GREEN}✅ 서버 등록 완료${NC}"
}

# 트래픽 통계 수집
get_traffic_stats() {
    # 최근 1분간 Nginx 로그 분석
    TOTAL_REQUESTS=$(tail -1000 /var/log/nginx/access.log 2>/dev/null | wc -l)
    REQUESTS_PER_SEC=$((TOTAL_REQUESTS / 60))
    
    # Rate limiting 차단 수
    BLOCKED=$(tail -1000 /var/log/nginx/error.log 2>/dev/null | grep -c "limiting requests")
    
    echo "{\"totalRequests\": $TOTAL_REQUESTS, \"requestsPerSecond\": $REQUESTS_PER_SEC, \"blockedTraffic\": $BLOCKED, \"normalTraffic\": $((TOTAL_REQUESTS - BLOCKED))}"
}

# Fail2ban 차단 IP 수집
get_blocked_ips() {
    BLOCKED_IPS=$(sudo fail2ban-client status sshd 2>/dev/null | grep "Currently banned" | awk '{print $4}')
    
    echo "{\"count\": ${BLOCKED_IPS:-0}}"
}

# 시스템 상태 수집
get_system_status() {
    LOAD=$(cat /proc/loadavg | awk '{print $1}')
    MEMORY=$(free | grep Mem | awk '{printf "%.2f", ($3/$2) * 100}')
    UPTIME=$(uptime -p)
    
    echo "{\"load\": $LOAD, \"memory\": $MEMORY, \"uptime\": \"$UPTIME\", \"status\": \"normal\"}"
}

# 데이터 전송
send_stats() {
    echo -e "${GREEN}[2/3] 데이터 수집 중...${NC}"
    
    TRAFFIC=$(get_traffic_stats)
    BLOCKED_IPS=$(get_blocked_ips)
    SYSTEM_STATUS=$(get_system_status)
    
    echo -e "${GREEN}[3/3] 중앙 서버로 전송 중...${NC}"
    
    RESPONSE=$(curl -s -X POST "$CENTRAL_SERVER/api/server/$SERVER_ID/stats" \
        -H "Content-Type: application/json" \
        -d "{
            \"traffic\": $TRAFFIC,
            \"blockedIPs\": $BLOCKED_IPS,
            \"systemStatus\": $SYSTEM_STATUS
        }")
    
    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo -e "${GREEN}✅ 데이터 전송 성공${NC}"
    else
        echo -e "${RED}❌ 데이터 전송 실패${NC}"
    fi
}

# 서버 등록 (최초 1회)
register_server

# 무한 루프로 데이터 전송 (30초마다)
while true; do
    send_stats
    echo -e "${BLUE}다음 전송까지 30초 대기...${NC}"
    sleep 30
done
```

---

### 3️⃣ Agent 배포 및 실행

#### C. 각 서버에 설치

```bash
# 1. Agent 스크립트 다운로드
curl -o /usr/local/bin/ddos-agent.sh https://ddos.neuralgrid.kr/scripts/ddos-agent.sh
chmod +x /usr/local/bin/ddos-agent.sh

# 2. Systemd 서비스 생성
cat > /etc/systemd/system/ddos-agent.service << 'EOF'
[Unit]
Description=DDoS Defense Agent
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/bin/ddos-agent.sh
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# 3. 서비스 시작
systemctl daemon-reload
systemctl enable ddos-agent
systemctl start ddos-agent

# 4. 상태 확인
systemctl status ddos-agent
```

---

### 4️⃣ 통합 대시보드 UI 수정

#### D. 다중 서버 표시 대시보드

**파일**: `ddos-dashboard-multi.html`

```html
<div class="server-grid">
    <h2>🖥️ 서버 현황</h2>
    <div id="servers-container">
        <!-- 서버별 카드가 동적으로 추가됨 -->
    </div>
</div>

<script>
async function updateServers() {
    try {
        const servers = await fetch('/api/servers').then(r => r.json());
        
        const container = document.getElementById('servers-container');
        container.innerHTML = servers.map(server => `
            <div class="server-card ${server.isOnline ? 'online' : 'offline'}">
                <h3>${server.name}</h3>
                <p>IP: ${server.ip}</p>
                <p>상태: ${server.isOnline ? '🟢 온라인' : '🔴 오프라인'}</p>
                <p>트래픽: ${server.traffic?.requestsPerSecond || 0} req/s</p>
                <p>차단 IP: ${server.blockedIPs?.count || 0}개</p>
                <p>부하: ${server.systemStatus?.load || 0}</p>
                <button onclick="viewServer('${server.id}')">상세보기</button>
            </div>
        `).join('');
    } catch (error) {
        console.error('서버 목록 로드 실패:', error);
    }
}

// 1초마다 업데이트
setInterval(updateServers, 1000);
updateServers();
</script>

<style>
.server-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.server-card {
    background: var(--bg-card);
    padding: 20px;
    border-radius: 8px;
    border: 2px solid var(--border);
}

.server-card.online {
    border-color: var(--success);
}

.server-card.offline {
    border-color: var(--danger);
    opacity: 0.6;
}
</style>
```

---

## 🎯 방법 2: 에이전트 설치 방식 (경량)

### 특징
- 각 서버에 경량 스크립트만 설치
- Cron으로 주기적으로 데이터 전송
- 시스템 리소스 최소 사용

### 설치 방법

```bash
# 1. 경량 Agent 스크립트 생성
cat > /usr/local/bin/ddos-report.sh << 'EOF'
#!/bin/bash
# 간단한 데이터 수집 및 전송
STATS=$(curl -s http://localhost/nginx_status)
curl -X POST https://ddos.neuralgrid.kr/api/report \
    -d "server=$(hostname)&stats=$STATS"
EOF

chmod +x /usr/local/bin/ddos-report.sh

# 2. Cron 등록 (1분마다 실행)
echo "* * * * * /usr/local/bin/ddos-report.sh" | crontab -
```

---

## 🎯 방법 3: 완전 독립 설치

### 특징
- 각 서버에 전체 시스템 복사
- 완전히 독립적으로 운영
- 서버 간 통신 불필요

### 설치 방법

```bash
# 자동 설치 스크립트
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 또는 수동 설치
git clone https://github.com/hompystory-coder/azamans.git
cd azamans
./deploy-ddos-nginx.sh
```

---

## 📊 비교표

| 항목 | SaaS 방식 | Agent 방식 | 독립 설치 |
|------|-----------|-----------|----------|
| **설치 난이도** | ⭐⭐ | ⭐ | ⭐⭐⭐ |
| **유지보수** | ✅ 쉬움 | ✅ 쉬움 | ❌ 어려움 |
| **리소스 사용** | ⭐ 최소 | ⭐⭐ 중간 | ⭐⭐⭐ 높음 |
| **통합 모니터링** | ✅ 가능 | ✅ 가능 | ❌ 불가능 |
| **서버 독립성** | ❌ 중앙 의존 | ❌ 중앙 의존 | ✅ 완전 독립 |
| **확장성** | ✅✅✅ 우수 | ✅✅ 좋음 | ❌ 나쁨 |

---

## 🚀 추천 시나리오

### 🏢 **케이스 1: 여러 서버를 한 회사에서 관리**
👉 **SaaS 방식** 추천  
- 한 대시보드에서 모든 서버 모니터링
- 통합 보고서 생성
- 중앙 집중식 관리

### 🛒 **케이스 2: 고객사에 서비스 판매**
👉 **Agent 방식** 추천  
- 고객사 서버에 최소 설치
- 중앙에서 모니터링 제공
- SaaS 비즈니스 모델

### 🔒 **케이스 3: 완전히 독립적인 환경**
👉 **독립 설치** 추천  
- 보안상 외부 통신 불가능한 환경
- 각 서버가 완전히 독립적으로 운영
- 인터넷 연결이 없는 환경

---

## 📦 배포 패키지 구성

```
ddos-defense-package/
├── install.sh              # 자동 설치 스크립트
├── ddos-agent.sh          # Agent 스크립트
├── ddos-defense-server.js # 중앙 서버 코드
├── ddos-dashboard.html    # 대시보드
├── README.md              # 설치 가이드
└── configs/
    ├── nginx.conf         # Nginx 설정
    ├── fail2ban.conf      # Fail2ban 설정
    └── systemd.service    # Systemd 서비스 파일
```

---

## 🔐 보안 고려사항

### API 인증
```javascript
// API Key 기반 인증
app.use('/api/server/*', (req, res, next) => {
    const apiKey = req.headers['x-api-key'];
    
    if (!isValidApiKey(apiKey)) {
        return res.status(401).json({ error: 'Unauthorized' });
    }
    
    next();
});
```

### 통신 암호화
- ✅ HTTPS 필수
- ✅ API Key 인증
- ✅ IP 화이트리스트
- ✅ Rate Limiting

---

## 💰 비즈니스 모델 (선택사항)

### SaaS 구독 서비스
```
무료 플랜: 1개 서버
베이직: $29/월 - 5개 서버
프로: $99/월 - 20개 서버
엔터프라이즈: 커스텀 가격
```

---

**이 가이드로 다중 서버 DDoS 방어 시스템을 구축할 수 있습니다!** 🎉
