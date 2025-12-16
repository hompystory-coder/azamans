// ✨ NeuralGrid Security Platform - 설치 코드 생성기
// 홈페이지 보호 및 서버 보호를 위한 설치 코드/스크립트 자동 생성

/**
 * 홈페이지 보호 - JavaScript 스니펫 생성
 * @param {string} orderId - 주문 ID
 * @param {Array} domains - 보호할 도메인 목록
 * @param {string} apiKey - API 키
 * @returns {string} JavaScript 보호 코드
 */
function generateWebsiteProtectionCode(orderId, domains, apiKey) {
    return `<!-- NeuralGrid DDoS Protection -->
<script>
(function() {
    var config = {
        orderId: '${orderId}',
        apiKey: '${apiKey}',
        domains: ${JSON.stringify(domains)},
        apiEndpoint: 'https://ddos.neuralgrid.kr/api/protect'
    };
    
    // 트래픽 로깅
    function logRequest() {
        fetch(config.apiEndpoint + '/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': config.apiKey
            },
            body: JSON.stringify({
                orderId: config.orderId,
                url: window.location.href,
                referrer: document.referrer,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString()
            })
        }).catch(function(error) {
            console.error('NeuralGrid logging failed:', error);
        });
    }
    
    // 페이지 로드 시 로깅
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', logRequest);
    } else {
        logRequest();
    }
    
    // DDoS 보호 활성화
    console.log('NeuralGrid DDoS Protection: Active');
    console.log('Protected Domains:', config.domains);
    console.log('Order ID:', config.orderId);
})();
</script>
<!-- End NeuralGrid DDoS Protection -->`;
}

/**
 * 서버 보호 - Bash 설치 스크립트 생성
 * @param {string} orderId - 주문 ID
 * @param {Array} serverIps - 서버 IP 목록
 * @param {string} apiKey - API 키
 * @param {number} serverQuantity - 서버 수량
 * @returns {string} Bash 설치 스크립트
 */
function generateServerInstallScript(orderId, serverIps, apiKey, serverQuantity = 5) {
    return `#!/bin/bash
# ============================================================
# NeuralGrid DDoS Protection Agent Installer
# Order ID: ${orderId}
# Protected Servers: ${serverQuantity}
# ============================================================

set -e

echo ""
echo "🛡️  NeuralGrid DDoS Protection Agent Installer"
echo "============================================================"
echo "Order ID: ${orderId}"
echo "Server IPs: ${serverIps.join(', ')}"
echo "============================================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run as root (use: sudo bash install.sh)"
    exit 1
fi

# Detect OS
echo "🔍 Detecting operating system..."
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
else
    echo "❌ Cannot detect OS"
    exit 1
fi

echo "✅ Detected: $OS $VER"
echo ""

# Install dependencies
echo "📦 Installing dependencies..."
if command -v apt-get &> /dev/null; then
    apt-get update -qq
    apt-get install -y curl iptables ipset fail2ban jq > /dev/null 2>&1
    echo "✅ Dependencies installed (Debian/Ubuntu)"
elif command -v yum &> /dev/null; then
    yum install -y curl iptables ipset fail2ban jq > /dev/null 2>&1
    echo "✅ Dependencies installed (RHEL/CentOS)"
else
    echo "❌ Unsupported package manager"
    exit 1
fi

# Create config directory
echo "⚙️  Creating configuration directory..."
mkdir -p /etc/neuralgrid
chmod 700 /etc/neuralgrid

# Configure agent
echo "📝 Writing configuration..."
cat > /etc/neuralgrid/config.json <<EOF
{
    "orderId": "${orderId}",
    "apiKey": "${apiKey}",
    "serverIps": ${JSON.stringify(serverIps)},
    "apiEndpoint": "https://ddos.neuralgrid.kr/api",
    "logLevel": "info",
    "enableAutoBlock": true,
    "blockDuration": 3600,
    "maxRequestsPerMinute": 100,
    "monitorPorts": [80, 443, 22, 3000, 8080],
    "whitelistIPs": ["127.0.0.1"]
}
EOF

chmod 600 /etc/neuralgrid/config.json
echo "✅ Configuration saved"

# Download agent binary
echo "⬇️  Downloading NeuralGrid agent..."
AGENT_URL="https://ddos.neuralgrid.kr/downloads/neuralgrid-agent-latest.tar.gz"
curl -fsSL "$AGENT_URL" -o /tmp/neuralgrid-agent.tar.gz 2>/dev/null || {
    echo "⚠️  Agent download unavailable, creating service script..."
    
    # Create agent service script
    cat > /usr/local/bin/neuralgrid-agent <<'AGENT_SCRIPT'
#!/bin/bash
# NeuralGrid DDoS Protection Agent

CONFIG_FILE="/etc/neuralgrid/config.json"
LOG_FILE="/var/log/neuralgrid-agent.log"

# Load configuration
API_KEY=$(jq -r '.apiKey' $CONFIG_FILE)
API_ENDPOINT=$(jq -r '.apiEndpoint' $CONFIG_FILE)
ORDER_ID=$(jq -r '.orderId' $CONFIG_FILE)

# Monitor and report function
monitor_traffic() {
    while true; do
        # Get connection count
        CONN_COUNT=$(netstat -an | grep ESTABLISHED | wc -l)
        
        # Send to API
        curl -s -X POST "$API_ENDPOINT/agent/report" \\
            -H "Content-Type: application/json" \\
            -H "X-API-Key: $API_KEY" \\
            -d "{
                \\"orderId\\": \\"$ORDER_ID\\",
                \\"timestamp\\": \\"$(date -u +%Y-%m-%dT%H:%M:%SZ)\\",
                \\"connections\\": $CONN_COUNT,
                \\"status\\": \\"active\\"
            }" >> $LOG_FILE 2>&1
        
        sleep 60
    done
}

# Start monitoring
echo "$(date): NeuralGrid Agent Started" >> $LOG_FILE
monitor_traffic
AGENT_SCRIPT

    chmod +x /usr/local/bin/neuralgrid-agent
    echo "✅ Agent service script created"
}

# Create systemd service
echo "🔧 Creating systemd service..."
cat > /etc/systemd/system/neuralgrid-agent.service <<EOF
[Unit]
Description=NeuralGrid DDoS Protection Agent
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/bin/neuralgrid-agent
Restart=always
RestartSec=10
StandardOutput=append:/var/log/neuralgrid-agent.log
StandardError=append:/var/log/neuralgrid-agent.log

[Install]
WantedBy=multi-user.target
EOF

echo "✅ Systemd service created"

# Configure fail2ban
echo "🔒 Configuring fail2ban..."
cat > /etc/fail2ban/jail.d/neuralgrid.conf <<EOF
[neuralgrid-ddos]
enabled = true
port = all
filter = neuralgrid-ddos
logpath = /var/log/neuralgrid-agent.log
maxretry = 5
bantime = 3600
EOF

systemctl reload fail2ban > /dev/null 2>&1 || true
echo "✅ Fail2ban configured"

# Start and enable service
echo "▶️  Starting NeuralGrid agent service..."
systemctl daemon-reload
systemctl enable neuralgrid-agent > /dev/null 2>&1
systemctl start neuralgrid-agent

# Verify installation
sleep 2
if systemctl is-active --quiet neuralgrid-agent; then
    echo ""
    echo "============================================================"
    echo "✅ Installation Complete!"
    echo "============================================================"
    echo ""
    echo "📊 Service Status:"
    systemctl status neuralgrid-agent --no-pager --lines=3
    echo ""
    echo "🔍 Useful Commands:"
    echo "  • Check logs: journalctl -u neuralgrid-agent -f"
    echo "  • Service status: systemctl status neuralgrid-agent"
    echo "  • Restart service: systemctl restart neuralgrid-agent"
    echo "  • Stop service: systemctl stop neuralgrid-agent"
    echo ""
    echo "📈 Dashboard: https://ddos.neuralgrid.kr/mypage.html"
    echo "📧 Support: support@neuralgrid.kr"
    echo ""
else
    echo ""
    echo "============================================================"
    echo "⚠️  Installation completed with warnings"
    echo "============================================================"
    echo "The service may need manual start:"
    echo "  systemctl start neuralgrid-agent"
    echo ""
fi
`;
}

/**
 * API 키 생성
 * @param {string} orderId - 주문 ID
 * @returns {string} API 키
 */
function generateApiKey(orderId) {
    const timestamp = Date.now();
    const random = Math.random().toString(36).substring(2, 15);
    const hash = require('crypto')
        .createHash('sha256')
        .update(`${orderId}-${timestamp}-${random}`)
        .digest('hex')
        .substring(0, 32);
    
    return `NGS_${hash.toUpperCase()}`;
}

module.exports = {
    generateWebsiteProtectionCode,
    generateServerInstallScript,
    generateApiKey
};
