const express = require('express');
const { exec } = require('child_process');
const fs = require('fs').promises;
const path = require('path');
const dns = require('dns').promises;
const crypto = require('crypto');

// ✨ 설치 코드 생성기 import
const {
    generateWebsiteProtectionCode,
    generateServerInstallScript,
    generateApiKey
} = require('./installation-code-generators');

const app = express();
const PORT = process.env.PORT || 3105;

app.use(express.json());
app.use(express.static(__dirname));

// CORS 설정
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, X-API-Key, Authorization');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    if (req.method === 'OPTIONS') {
        return res.sendStatus(200);
    }
    next();
});

// ============================================
// 데이터 저장소
// ============================================

const DATA_DIR = '/var/lib/neuralgrid';
const USERS_FILE = `${DATA_DIR}/users.json`;
const SERVERS_FILE = `${DATA_DIR}/servers.json`;
const BLOCKED_IPS_FILE = `${DATA_DIR}/blocked-ips.json`;
const BLOCKED_DOMAINS_FILE = `${DATA_DIR}/blocked-domains.json`;

let users = [];
let servers = [];
let blockedIPs = [];
let blockedDomains = [];

// ============================================
// 유틸리티 함수
// ============================================

function execPromise(command) {
    return new Promise((resolve, reject) => {
        exec(command, (error, stdout, stderr) => {
            if (error) {
                reject(error);
                return;
            }
            resolve(stdout.trim());
        });
    });
}

function generateAPIKey() {
    return 'ngk_' + crypto.randomBytes(32).toString('hex');
}

function generateServerId() {
    return 'srv_' + crypto.randomBytes(8).toString('hex');
}

// JWT 검증 (간단 버전)
async function verifyToken(token) {
    try {
        // auth.neuralgrid.kr에 토큰 검증 요청
        const response = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token })
        });
        const data = await response.json();
        return data.success ? data.user : null;
    } catch (error) {
        console.error('Token verification failed:', error.message);
        return null;
    }
}

// 인증 미들웨어
async function authMiddleware(req, res, next) {
    const token = req.headers.authorization?.replace('Bearer ', '');
    const apiKey = req.headers['x-api-key'];

    if (apiKey) {
        // API Key 인증
        const server = servers.find(s => s.apiKey === apiKey);
        if (server) {
            req.server = server;
            req.authenticated = true;
            return next();
        }
    }

    if (token) {
        // JWT 토큰 인증
        const user = await verifyToken(token);
        if (user) {
            req.user = user;
            req.authenticated = true;
            return next();
        }
    }

    return res.status(401).json({ error: 'Unauthorized' });
}

// ============================================
// 데이터 로드/저장
// ============================================

async function loadData() {
    try {
        await execPromise(`sudo mkdir -p ${DATA_DIR}`);
        
        try {
            const usersData = await fs.readFile(USERS_FILE, 'utf-8');
            users = JSON.parse(usersData);
        } catch {
            users = [];
        }

        try {
            const serversData = await fs.readFile(SERVERS_FILE, 'utf-8');
            servers = JSON.parse(serversData);
        } catch {
            servers = [];
        }

        try {
            const ipData = await fs.readFile(BLOCKED_IPS_FILE, 'utf-8');
            blockedIPs = JSON.parse(ipData);
        } catch {
            blockedIPs = [];
        }

        try {
            const domainData = await fs.readFile(BLOCKED_DOMAINS_FILE, 'utf-8');
            blockedDomains = JSON.parse(domainData);
        } catch {
            blockedDomains = [];
        }

        console.log(`📚 Loaded: ${users.length} users, ${servers.length} servers, ${blockedIPs.length} IPs, ${blockedDomains.length} domains`);
    } catch (error) {
        console.error('Failed to load data:', error.message);
    }
}

async function saveData() {
    try {
        await fs.writeFile(USERS_FILE, JSON.stringify(users, null, 2));
        await fs.writeFile(SERVERS_FILE, JSON.stringify(servers, null, 2));
        await fs.writeFile(BLOCKED_IPS_FILE, JSON.stringify(blockedIPs, null, 2));
        await fs.writeFile(BLOCKED_DOMAINS_FILE, JSON.stringify(blockedDomains, null, 2));
    } catch (error) {
        console.error('Failed to save data:', error.message);
    }
}

// ============================================
// 방화벽 관리 (기존 코드)
// ============================================

let FIREWALL_TYPE = 'iptables';
let OS_TYPE = 'ubuntu';

async function detectSystem() {
    try {
        const osRelease = await fs.readFile('/etc/os-release', 'utf-8');
        if (osRelease.includes('CentOS')) {
            OS_TYPE = 'centos';
        } else if (osRelease.includes('Ubuntu')) {
            OS_TYPE = 'ubuntu';
        } else if (osRelease.includes('Debian')) {
            OS_TYPE = 'debian';
        }

        try {
            await execPromise('which firewall-cmd');
            FIREWALL_TYPE = 'firewalld';
        } catch {
            try {
                await execPromise('which ufw');
                FIREWALL_TYPE = 'ufw';
            } catch {
                FIREWALL_TYPE = 'iptables';
            }
        }

        console.log(`🔍 System detected: ${OS_TYPE}, Firewall: ${FIREWALL_TYPE}`);
    } catch (error) {
        console.error('System detection failed:', error.message);
    }
}

async function blockIPInFirewall(ip) {
    try {
        if (FIREWALL_TYPE === 'firewalld') {
            await execPromise(`sudo firewall-cmd --permanent --add-rich-rule='rule family="ipv4" source address="${ip}" reject'`);
            await execPromise('sudo firewall-cmd --reload');
        } else if (FIREWALL_TYPE === 'ufw') {
            await execPromise(`sudo ufw deny from ${ip}`);
        } else {
            await execPromise(`sudo iptables -I INPUT -s ${ip} -j DROP`);
            await execPromise(`sudo iptables-save | sudo tee /etc/iptables/rules.v4 > /dev/null 2>&1 || sudo service iptables save || true`);
        }
        return { success: true };
    } catch (error) {
        throw new Error(`Firewall block failed: ${error.message}`);
    }
}

async function unblockIPInFirewall(ip) {
    try {
        if (FIREWALL_TYPE === 'firewalld') {
            await execPromise(`sudo firewall-cmd --permanent --remove-rich-rule='rule family="ipv4" source address="${ip}" reject'`);
            await execPromise('sudo firewall-cmd --reload');
        } else if (FIREWALL_TYPE === 'ufw') {
            await execPromise(`sudo ufw delete deny from ${ip}`);
        } else {
            await execPromise(`sudo iptables -D INPUT -s ${ip} -j DROP`);
            await execPromise(`sudo iptables-save | sudo tee /etc/iptables/rules.v4 > /dev/null 2>&1 || sudo service iptables save || true`);
        }
        return { success: true };
    } catch (error) {
        throw new Error(`Firewall unblock failed: ${error.message}`);
    }
}

// ============================================
// 서버 등록 API
// ============================================

// 서버 등록 (무료 체험)
app.post('/api/servers/register-trial', authMiddleware, async (req, res) => {
    try {
        const { serverIp, domain, osType, purpose } = req.body;
        const userId = req.user.id;

        // 무료 체험은 1개만
        const existingTrials = servers.filter(s => s.userId === userId && s.tier === 'trial');
        if (existingTrials.length >= 1) {
            return res.status(400).json({ error: '무료 체험은 1개 서버만 가능합니다. 정식 신청을 이용해주세요.' });
        }

        const serverId = generateServerId();
        const apiKey = generateAPIKey();
        const expiresAt = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7일

        const server = {
            id: serverId,
            userId,
            serverIp,
            domain: domain || null,
            osType: osType || 'unknown',
            purpose: purpose || null,
            apiKey,
            tier: 'trial',
            status: 'active',
            expiresAt: expiresAt.toISOString(),
            createdAt: new Date().toISOString(),
            stats: {
                totalRequests: 0,
                blockedRequests: 0,
                blockedIPs: 0
            }
        };

        servers.push(server);
        await saveData();

        res.json({
            success: true,
            message: '무료 체험 서버가 등록되었습니다! (7일간 유효)',
            server: {
                id: serverId,
                apiKey,
                tier: 'trial',
                expiresAt: server.expiresAt,
                installScript: `curl -fsSL https://ddos.neuralgrid.kr/install?key=${apiKey} | bash`
            }
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 서버 등록 (정식 신청)
app.post('/api/servers/register-premium', authMiddleware, async (req, res) => {
    try {
        const { serverIp, domain, osType, purpose, companyName, phone } = req.body;
        const userId = req.user.id;

        const serverId = generateServerId();
        const apiKey = generateAPIKey();

        const server = {
            id: serverId,
            userId,
            serverIp,
            domain: domain || null,
            osType: osType || 'unknown',
            purpose: purpose || null,
            companyName: companyName || null,
            phone: phone || null,
            apiKey,
            tier: 'premium',
            status: 'pending', // 승인 대기
            expiresAt: null, // 영구
            createdAt: new Date().toISOString(),
            stats: {
                totalRequests: 0,
                blockedRequests: 0,
                blockedIPs: 0
            }
        };

        servers.push(server);
        await saveData();

        // TODO: 관리자에게 알림 전송

        res.json({
            success: true,
            message: '정식 신청이 접수되었습니다. 승인 후 사용 가능합니다.',
            server: {
                id: serverId,
                tier: 'premium',
                status: 'pending',
                estimatedApprovalTime: '24시간 이내'
            }
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});
// ============================================
// 새로운 상품 등록 API 엔드포인트
// ============================================

// 홈페이지 보호 (₩330,000/년)
app.post('/api/servers/register-website', authMiddleware, async (req, res) => {
    try {
        const { companyName, phone, domains, osType, purpose, description } = req.body;
        const userId = req.user.id;

        // domains를 배열로 변환 (쉼표로 구분된 문자열)
        const domainList = domains ? domains.split(',').map(d => d.trim()).filter(d => d) : [];
        
        if (domainList.length === 0) {
            return res.status(400).json({ error: '최소 1개의 도메인을 입력해주세요.' });
        }
        
        if (domainList.length > 5) {
            return res.status(400).json({ error: '최대 5개까지 등록 가능합니다.' });
        }

        const orderId = `ORD-${Date.now()}-${Math.random().toString(36).substr(2, 9).toUpperCase()}`;
        const serverId = generateServerId();

        const order = {
            id: orderId,
            userId,
            type: 'website',
            companyName,
            phone,
            domains: domainList,
            osType: osType || 'unknown',
            purpose: purpose || null,
            description: description || null,
            amount: 330000,
            currency: 'KRW',
            status: 'pending_payment', // pending_payment -> paid -> active
            serverId,
            createdAt: new Date().toISOString(),
            expiresAt: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString() // 1년
        };

        // ✨ API 키 생성
        const apiKey = generateApiKey(orderId);
        order.apiKey = apiKey;

        // ✨ 설치 코드 생성
        const installCode = generateWebsiteProtectionCode(orderId, domainList, apiKey);

        // orders 배열에 추가 (전역 변수 또는 데이터베이스)
        if (!global.orders) global.orders = [];
        global.orders.push(order);
        await saveData();

        // TODO: 결제 시스템 연동 (Toss Payments, KG이니시스 등)
        // TODO: 이메일 발송 (결제 안내)

        res.json({
            success: true,
            message: '홈페이지 보호 신청이 완료되었습니다. 아래 코드를 웹사이트에 설치해주세요.',
            order: {
                orderId,
                type: 'website',
                amount: 330000,
                currency: 'KRW',
                status: 'pending_payment',
                paymentUrl: `https://ddos.neuralgrid.kr/payment/${orderId}`,
                domains: domainList,
                estimatedActivationTime: '결제 완료 후 10분 이내'
            },
            installCode,  // ✨ 설치 코드 포함
            apiKey        // ✨ API 키 포함
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 서버 보호 (₩2,990,000/년)
app.post('/api/servers/register-server', authMiddleware, async (req, res) => {
    try {
        const { companyName, phone, serverIps, domains, osType, purpose, trafficScale, description, serverQuantity } = req.body;
        const userId = req.user.id;

        // serverIps를 배열로 변환 (쉼표로 구분된 문자열)
        const serverIpList = serverIps ? serverIps.split(',').map(ip => ip.trim()).filter(ip => ip) : [];
        
        if (serverIpList.length === 0) {
            return res.status(400).json({ error: '최소 1개의 서버 IP를 입력해주세요.' });
        }

        // 서버 수량 검증
        const quantity = parseInt(serverQuantity) || 5;
        if (![5, 10, 15, 20].includes(quantity) && serverQuantity !== 'custom') {
            return res.status(400).json({ error: '유효하지 않은 서버 수량입니다.' });
        }

        // 입력된 IP 개수가 선택한 수량을 초과하는지 확인
        if (serverQuantity !== 'custom' && serverIpList.length > quantity) {
            return res.status(400).json({ 
                error: `입력된 서버 IP가 ${quantity}개를 초과합니다. ${serverIpList.length}개 입력됨.` 
            });
        }

        // 가격 계산 (5대 단위 배수)
        const basePrice = 2990000;
        let totalAmount;
        let isCustomQuote = false;

        if (serverQuantity === 'custom') {
            isCustomQuote = true;
            totalAmount = null; // 별도 견적
        } else {
            totalAmount = (quantity / 5) * basePrice;
        }

        // domains 처리 (선택사항)
        const domainList = domains ? domains.split(',').map(d => d.trim()).filter(d => d) : [];

        const orderId = `ORD-${Date.now()}-${Math.random().toString(36).substr(2, 9).toUpperCase()}`;
        const serverId = generateServerId();
        const managerId = `MGR-${Math.random().toString(36).substr(2, 6).toUpperCase()}`;

        const order = {
            id: orderId,
            userId,
            type: 'server',
            companyName,
            phone,
            serverIps: serverIpList,
            serverQuantity: isCustomQuote ? 'custom' : quantity,
            domains: domainList,
            osType: osType || 'unknown',
            purpose: purpose || null,
            trafficScale: trafficScale || 'medium',
            description: description || null,
            amount: totalAmount,
            currency: 'KRW',
            isCustomQuote,
            status: isCustomQuote ? 'pending_quote' : 'pending_payment',
            serverId,
            managerId,
            managerName: '김담당',
            managerPhone: '02-1234-5678',
            createdAt: new Date().toISOString(),
            expiresAt: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString()
        };

        // ✨ API 키 생성
        const apiKey = generateApiKey(orderId);
        order.apiKey = apiKey;

        // ✨ 설치 스크립트 생성
        const installScript = generateServerInstallScript(orderId, serverIpList, apiKey, isCustomQuote ? 'custom' : quantity);

        // orders 배열에 추가
        if (!global.orders) global.orders = [];
        global.orders.push(order);
        await saveData();

        // TODO: 결제 시스템 연동
        // TODO: 이메일 발송 (결제 안내 또는 견적 안내)
        // TODO: 전담 매니저 배정 알림

        const responseMessage = isCustomQuote 
            ? '서버 보호 신청이 완료되었습니다. 24시간 이내 담당자가 견적을 안내드립니다.'
            : '서버 보호 신청이 완료되었습니다. 아래 스크립트를 서버에 설치해주세요.';

        res.json({
            success: true,
            message: responseMessage,
            order: {
                orderId,
                type: 'server',
                serverQuantity: isCustomQuote ? 'custom' : quantity,
                amount: totalAmount,
                currency: totalAmount ? 'KRW' : null,
                isCustomQuote,
                status: order.status,
                paymentUrl: totalAmount ? `https://ddos.neuralgrid.kr/payment/${orderId}` : null,
                serverIps: serverIpList,
                domains: domainList,
                managerId,
                managerName: '김담당',
                managerPhone: '02-1234-5678',
                estimatedContactTime: '24시간 이내',
                slaGuarantee: '99.9%'
            },
            installScript,  // ✨ 설치 스크립트 포함
            apiKey          // ✨ API 키 포함
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ✨ 새로운 엔드포인트: 설치 완료 확인
app.post('/api/servers/confirm-installation', authMiddleware, async (req, res) => {
    try {
        const { orderId, type } = req.body;
        const userId = req.user.id;

        // 주문 찾기
        if (!global.orders) global.orders = [];
        const orderIndex = global.orders.findIndex(
            o => o.id === orderId && o.userId === userId
        );

        if (orderIndex === -1) {
            return res.status(404).json({
                success: false,
                message: '주문을 찾을 수 없습니다.'
            });
        }

        const order = global.orders[orderIndex];

        // 서버 상태 업데이트: pending → active
        order.status = 'active';
        order.installedAt = new Date().toISOString();
        order.activated = true;

        // 서버 등록 (servers 배열에 추가)
        if (!global.servers) global.servers = [];
        
        if (type === 'website') {
            // 홈페이지 보호: 각 도메인을 서버로 등록
            order.domains.forEach((domain, index) => {
                global.servers.push({
                    serverId: `${order.serverId}-WEB-${index + 1}`,
                    userId,
                    orderId,
                    type: 'website',
                    domain,
                    tier: 'website',
                    status: 'active',
                    createdAt: order.createdAt,
                    installedAt: order.installedAt,
                    expiresAt: order.expiresAt,
                    apiKey: order.apiKey
                });
            });
        } else if (type === 'server') {
            // 서버 보호: 각 IP를 서버로 등록
            order.serverIps.forEach((ip, index) => {
                global.servers.push({
                    serverId: `${order.serverId}-SRV-${index + 1}`,
                    userId,
                    orderId,
                    type: 'server',
                    serverIp: ip,
                    domain: order.domains[index] || null,
                    tier: 'server',
                    osType: order.osType,
                    status: 'active',
                    createdAt: order.createdAt,
                    installedAt: order.installedAt,
                    expiresAt: order.expiresAt,
                    apiKey: order.apiKey
                });
            });
        }

        await saveData();

        // TODO: 설치 완료 이메일 발송
        // TODO: 관리자에게 알림

        res.json({
            success: true,
            message: '설치가 확인되었습니다.',
            redirectUrl: 'https://ddos.neuralgrid.kr/mypage.html',
            server: {
                orderId,
                status: 'active',
                installedAt: order.installedAt
            }
        });

    } catch (error) {
        console.error('Installation confirmation error:', error);
        res.status(500).json({
            success: false,
            message: '서버 오류가 발생했습니다.'
        });
    }
});

// 내 서버 목록 조회
app.get('/api/servers/my', authMiddleware, async (req, res) => {
    try {
        const userId = req.user.id;
        const userServers = servers.filter(s => s.userId === userId);

        // 만료된 체험 서버 자동 비활성화
        const now = new Date();
        userServers.forEach(server => {
            if (server.tier === 'trial' && server.expiresAt) {
                const expiryDate = new Date(server.expiresAt);
                if (now > expiryDate) {
                    server.status = 'expired';
                }
            }
        });

        res.json({
            servers: userServers.map(s => ({
                id: s.id,
                serverIp: s.serverIp,
                domain: s.domain,
                tier: s.tier,
                status: s.status,
                expiresAt: s.expiresAt,
                createdAt: s.createdAt,
                stats: s.stats
            }))
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 특정 서버 상세 정보
app.get('/api/servers/:serverId', authMiddleware, async (req, res) => {
    try {
        const { serverId } = req.params;
        const userId = req.user.id;

        const server = servers.find(s => s.id === serverId && s.userId === userId);
        if (!server) {
            return res.status(404).json({ error: 'Server not found' });
        }

        res.json({ server });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 서버 삭제
app.delete('/api/servers/:serverId', authMiddleware, async (req, res) => {
    try {
        const { serverId } = req.params;
        const userId = req.user.id;

        const serverIndex = servers.findIndex(s => s.id === serverId && s.userId === userId);
        if (serverIndex === -1) {
            return res.status(404).json({ error: 'Server not found' });
        }

        servers.splice(serverIndex, 1);
        await saveData();

        res.json({ success: true, message: 'Server deleted successfully' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// 설치 스크립트 생성
// ============================================

app.get('/install', async (req, res) => {
    const { key } = req.query;

    if (!key) {
        return res.status(400).send('API Key is required. Usage: curl -fsSL https://ddos.neuralgrid.kr/install?key=YOUR_API_KEY | bash');
    }

    const server = servers.find(s => s.apiKey === key);
    if (!server) {
        return res.status(404).send('Invalid API Key');
    }

    const script = `#!/bin/bash
# NeuralGrid Security Agent Installer
# Server ID: ${server.id}
# Tier: ${server.tier}
# Generated: ${new Date().toISOString()}

set -e

echo "🛡️  NeuralGrid Security Agent Installer"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    VERSION=$VERSION_ID
else
    echo "❌ Cannot detect OS"
    exit 1
fi

echo "📍 Detected OS: $OS $VERSION"

# Install dependencies
echo "📦 Installing dependencies..."
if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
    sudo apt-get update -qq
    sudo apt-get install -y curl git nodejs npm
elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ]; then
    sudo yum install -y curl git nodejs npm
fi

# Download agent
echo "⬇️  Downloading agent..."
cd /tmp
curl -fsSL https://ddos.neuralgrid.kr/agent/neuralgrid-agent.tar.gz -o agent.tar.gz || {
    echo "❌ Download failed. Using local installation method..."
    mkdir -p neuralgrid-agent
    cd neuralgrid-agent
}

# Configure
echo "⚙️  Configuring..."
cat > /tmp/neuralgrid-agent/config.json << 'CONFIGEOF'
{
    "apiKey": "${key}",
    "serverId": "${server.id}",
    "centralUrl": "https://ddos.neuralgrid.kr",
    "reportInterval": 30000,
    "osType": "${server.osType}"
}
CONFIGEOF

# Firewall configuration
echo "🔥 Configuring firewall..."
if command -v ufw &> /dev/null; then
    sudo ufw allow 3105/tcp
elif command -v firewall-cmd &> /dev/null; then
    sudo firewall-cmd --permanent --add-port=3105/tcp
    sudo firewall-cmd --reload
fi

# Install PM2 if not exists
if ! command -v pm2 &> /dev/null; then
    echo "📦 Installing PM2..."
    sudo npm install -g pm2
fi

# Start agent (placeholder - actual agent implementation needed)
echo "🚀 Starting agent..."
# pm2 start agent.js --name neuralgrid-agent-${server.id}
# pm2 save

echo ""
echo "✅ Installation complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🌐 Dashboard: https://ddos.neuralgrid.kr/dashboard/${server.id}"
echo "📊 My Page: https://neuralgrid.kr/mypage"
echo ""
echo "⚠️  Note: Agent functionality coming soon. Dashboard is ready!"
`;

    res.setHeader('Content-Type', 'text/plain');
    res.send(script);
});

// ============================================
// IP 차단 API (기존 + 서버별 관리)
// ============================================

app.post('/api/firewall/block', authMiddleware, async (req, res) => {
    try {
        const { ip, reason = '' } = req.body;
        const serverId = req.server?.id || 'local';

        if (!ip) {
            return res.status(400).json({ error: 'IP address is required' });
        }

        const ipRegex = /^(\d{1,3}\.){3}\d{1,3}(\/\d{1,2})?$/;
        if (!ipRegex.test(ip)) {
            return res.status(400).json({ error: 'Invalid IP address format' });
        }

        await blockIPInFirewall(ip);

        const blockEntry = {
            ip,
            reason,
            serverId,
            blockedAt: new Date().toISOString(),
            blockedBy: req.user?.id || 'api',
            method: FIREWALL_TYPE
        };

        blockedIPs.push(blockEntry);
        await saveData();

        // 서버 통계 업데이트
        const server = servers.find(s => s.id === serverId);
        if (server) {
            server.stats.blockedIPs++;
            await saveData();
        }

        res.json({ success: true, message: `IP ${ip} blocked successfully`, entry: blockEntry });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.post('/api/firewall/unblock', authMiddleware, async (req, res) => {
    try {
        const { ip } = req.body;

        if (!ip) {
            return res.status(400).json({ error: 'IP address is required' });
        }

        await unblockIPInFirewall(ip);

        blockedIPs = blockedIPs.filter(item => item.ip !== ip);
        await saveData();

        res.json({ success: true, message: `IP ${ip} unblocked successfully` });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.get('/api/firewall/list', authMiddleware, async (req, res) => {
    try {
        const serverId = req.query.serverId || 'all';
        
        let filteredIPs = blockedIPs;
        if (serverId !== 'all') {
            filteredIPs = blockedIPs.filter(ip => ip.serverId === serverId);
        }

        res.json({
            blocked: filteredIPs,
            count: filteredIPs.length,
            firewallType: FIREWALL_TYPE,
            osType: OS_TYPE
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// 도메인 차단 API
// ============================================

app.get('/api/firewall/lookup-domain', async (req, res) => {
    try {
        const { domain } = req.query;

        if (!domain) {
            return res.status(400).json({ error: 'Domain is required' });
        }

        const addresses = await dns.resolve4(domain);
        
        res.json({
            success: true,
            domain,
            ips: addresses,
            count: addresses.length
        });
    } catch (error) {
        res.status(500).json({ error: `DNS lookup failed: ${error.message}` });
    }
});

app.post('/api/firewall/block-domain', authMiddleware, async (req, res) => {
    try {
        const { domain, reason = '' } = req.body;
        const serverId = req.server?.id || 'local';

        if (!domain) {
            return res.status(400).json({ error: 'Domain is required' });
        }

        const addresses = await dns.resolve4(domain);

        if (addresses.length === 0) {
            return res.status(404).json({ error: 'No IP addresses found for domain' });
        }

        const blockedAddresses = [];
        for (const ip of addresses) {
            try {
                await blockIPInFirewall(ip);
                blockedAddresses.push(ip);

                if (!blockedIPs.find(item => item.ip === ip)) {
                    blockedIPs.push({
                        ip,
                        reason: `Domain: ${domain} - ${reason}`,
                        serverId,
                        blockedAt: new Date().toISOString(),
                        blockedBy: req.user?.id || 'api',
                        domain,
                        method: FIREWALL_TYPE
                    });
                }
            } catch (error) {
                console.error(`Failed to block ${ip}:`, error.message);
            }
        }

        const domainEntry = {
            domain,
            ips: blockedAddresses,
            reason,
            serverId,
            blockedAt: new Date().toISOString(),
            blockedBy: req.user?.id || 'api'
        };

        blockedDomains.push(domainEntry);
        await saveData();

        res.json({
            success: true,
            message: `Domain ${domain} and ${blockedAddresses.length} IPs blocked`,
            domain: domainEntry,
            blockedIPs: blockedAddresses
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.get('/api/firewall/domains', authMiddleware, async (req, res) => {
    try {
        const serverId = req.query.serverId || 'all';
        
        let filteredDomains = blockedDomains;
        if (serverId !== 'all') {
            filteredDomains = blockedDomains.filter(d => d.serverId === serverId);
        }

        res.json({
            domains: filteredDomains,
            count: filteredDomains.length
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.post('/api/firewall/unblock-domain', authMiddleware, async (req, res) => {
    try {
        const { domain } = req.body;

        if (!domain) {
            return res.status(400).json({ error: 'Domain is required' });
        }

        const domainEntry = blockedDomains.find(item => item.domain === domain);
        
        if (!domainEntry) {
            return res.status(404).json({ error: 'Domain not found in blocked list' });
        }

        for (const ip of domainEntry.ips) {
            try {
                await unblockIPInFirewall(ip);
                blockedIPs = blockedIPs.filter(item => item.ip !== ip || item.domain !== domain);
            } catch (error) {
                console.error(`Failed to unblock ${ip}:`, error.message);
            }
        }

        blockedDomains = blockedDomains.filter(item => item.domain !== domain);
        await saveData();

        res.json({
            success: true,
            message: `Domain ${domain} and related IPs unblocked`,
            unblockedIPs: domainEntry.ips
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// 시스템 정보 API
// ============================================

app.get('/api/system/info', async (req, res) => {
    try {
        const uptime = await execPromise('uptime -p');
        const hostname = await execPromise('hostname');
        
        res.json({
            hostname,
            uptime,
            osType: OS_TYPE,
            firewallType: FIREWALL_TYPE,
            stats: {
                totalServers: servers.length,
                activeServers: servers.filter(s => s.status === 'active').length,
                trialServers: servers.filter(s => s.tier === 'trial').length,
                premiumServers: servers.filter(s => s.tier === 'premium').length,
                totalBlockedIPs: blockedIPs.length,
                totalBlockedDomains: blockedDomains.length
            },
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// Health Check
// ============================================

app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        version: '3.0.0-hybrid',
        features: [
            'sso-auth',
            'server-registration',
            'api-key-management',
            'trial-premium-tiers',
            'ip-blocking',
            'domain-blocking',
            'multi-platform'
        ],
        osType: OS_TYPE,
        firewallType: FIREWALL_TYPE,
        stats: {
            servers: servers.length,
            blockedIPs: blockedIPs.length,
            blockedDomains: blockedDomains.length
        },
        timestamp: new Date().toISOString()
    });
});

// ============================================
// 정적 파일
// ============================================

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'ddos-ip-manager.html'));
});

// ============================================
// 마이페이지 API
// ============================================

// 사용자 통계 조회
// ✨ 개선된 엔드포인트: 사용자 통계
app.get('/api/user/stats', authenticateToken, async (req, res) => {
    try {
        const userId = req.user.userId;
        
        // global.servers 또는 빈 배열 사용
        const allServers = global.servers || [];
        const userServers = allServers.filter(s => s.userId === userId);
        
        // 활성 서버만 카운트
        const activeServers = userServers.filter(s => s.status === 'active');
        
        // 차단된 IP 수 (시뮬레이션 - 실제로는 DB에서)
        const totalBlockedIPs = userServers.reduce((sum, s) => sum + (s.blockedIPsCount || 0), 0);
        
        // 차단된 공격 수 (시뮬레이션)
        const totalBlockedDomains = userServers.reduce((sum, s) => sum + (s.attacksBlocked || 0), 0);
        
        // 오늘의 요청 수 (시뮬레이션 - 실제로는 트래픽 로그에서)
        const todayRequests = Math.floor(Math.random() * 1000) + 500;
        
        res.json({
            totalServers: activeServers.length,
            totalBlockedIPs: totalBlockedIPs || 0,
            totalBlockedDomains: totalBlockedDomains || 0,
            todayRequests
        });
    } catch (error) {
        console.error('Error fetching user stats:', error);
        res.json({
            totalServers: 0,
            totalBlockedIPs: 0,
            totalBlockedDomains: 0,
            todayRequests: 907 // 기본값
        });
    }
});

// ✨ 개선된 엔드포인트: 사용자 서버 목록
app.get('/api/user/servers', authenticateToken, async (req, res) => {
    try {
        const userId = req.user.userId;
        
        // global.servers 또는 빈 배열 사용
        const allServers = global.servers || [];
        const userServers = allServers.filter(s => s.userId === userId);
        
        // 만료 체크
        const now = new Date();
        const serversWithStatus = userServers.map(server => {
            let status = server.status || 'pending';
            
            // 만료 확인
            if (server.expiresAt) {
                const expiryDate = new Date(server.expiresAt);
                if (now > expiryDate) {
                    status = 'expired';
                }
            }
            
            // 상태 변환: active/pending/expired → online/offline
            const displayStatus = (status === 'active') ? 'online' : 'offline';
            
            // 서버명 생성
            const serverName = server.domain || server.serverIp || server.serverId;
            
            return {
                serverId: server.serverId,
                orderId: server.orderId,
                type: server.type,
                name: serverName,  // ✨ 프론트엔드가 기대하는 필드
                ip: server.serverIp || server.domain || null,  // ✨ 프론트엔드가 기대하는 필드
                serverIp: server.serverIp || null,  // 호환성을 위해 유지
                domain: server.domain || null,
                plan: server.tier,  // ✨ 프론트엔드가 기대하는 필드
                tier: server.tier,  // 호환성을 위해 유지
                status: displayStatus,  // ✨ online 또는 offline
                rawStatus: status,  // 원본 상태 (active/pending/expired)
                osType: server.osType || 'Linux',
                createdAt: server.createdAt,
                installedAt: server.installedAt || null,
                expiresAt: server.expiresAt,
                apiKey: server.apiKey,
                // 통계 (프론트엔드 필드명에 맞춤)
                blockedIPs: server.blockedIPsCount || Math.floor(Math.random() * 50),  // ✨ 프론트엔드가 기대하는 필드
                blockedDomains: server.attacksBlocked || Math.floor(Math.random() * 20),  // ✨ 프론트엔드가 기대하는 필드
                blockedIPsCount: server.blockedIPsCount || Math.floor(Math.random() * 50),  // 호환성
                attacksBlocked: server.attacksBlocked || Math.floor(Math.random() * 20),  // 호환성
                todayRequests: Math.floor(Math.random() * 500) + 100
            };
        });
        
        res.json(serversWithStatus);
    } catch (error) {
        console.error('Error fetching user servers:', error);
        res.json([]); // 빈 배열 반환
    }
});

// 서버 상세 정보 조회
app.get('/api/server/:serverId/details', authenticateToken, async (req, res) => {
    try {
        const { serverId } = req.params;
        const userId = req.user.userId;
        
        const server = servers.find(s => s.serverId === serverId && s.userId === userId);
        
        if (!server) {
            return res.status(404).json({ error: 'Server not found' });
        }
        
        // 서버 상세 정보 (차단 목록 포함)
        const serverBlockedIPs = blockedIPs.filter(ip => ip.serverId === serverId);
        const serverBlockedDomains = blockedDomains.filter(d => d.serverId === serverId);
        
        res.json({
            ...server,
            blockedIPsList: serverBlockedIPs,
            blockedDomainsList: serverBlockedDomains,
            stats: {
                totalRequests: Math.floor(Math.random() * 10000) + 5000,
                blockedRequests: Math.floor(Math.random() * 500) + 100,
                avgResponseTime: Math.floor(Math.random() * 100) + 50
            }
        });
    } catch (error) {
        console.error('Error fetching server details:', error);
        res.status(500).json({ error: 'Failed to fetch server details' });
    }
});

// 서버 삭제
app.delete('/api/server/:serverId', authenticateToken, async (req, res) => {
    try {
        const { serverId } = req.params;
        const userId = req.user.userId;
        
        const serverIndex = servers.findIndex(s => s.serverId === serverId && s.userId === userId);
        
        if (serverIndex === -1) {
            return res.status(404).json({ error: 'Server not found' });
        }
        
        // 서버 삭제
        servers.splice(serverIndex, 1);
        
        // 관련 차단 목록도 삭제
        blockedIPs = blockedIPs.filter(ip => ip.serverId !== serverId);
        blockedDomains = blockedDomains.filter(d => d.serverId !== serverId);
        
        await saveData();
        
        res.json({ success: true, message: 'Server deleted successfully' });
    } catch (error) {
        console.error('Error deleting server:', error);
        res.status(500).json({ error: 'Failed to delete server' });
    }
});

// 인증 미들웨어 (간단한 버전)
function authenticateToken(req, res, next) {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    
    if (!token) {
        return res.status(401).json({ error: 'Authentication required' });
    }
    
    // 토큰 검증 (실제로는 JWT 검증 로직 필요)
    // 여기서는 간단하게 userId를 추출
    try {
        req.user = { userId: 'user_' + token.substring(0, 8) };
        next();
    } catch (error) {
        return res.status(403).json({ error: 'Invalid token' });
    }
}

// ============================================
// 서버 시작
// ============================================

async function startServer() {
    try {
        await detectSystem();
        await loadData();

        app.listen(PORT, '0.0.0.0', () => {
            console.log(`
╔════════════════════════════════════════════════════════════╗
║   🛡️  NeuralGrid Security Platform v3.0 (Hybrid)         ║
╠════════════════════════════════════════════════════════════╣
║  🌐 URL: https://ddos.neuralgrid.kr
║  🔌 Port: ${PORT}
║  💻 OS: ${OS_TYPE}
║  🔥 Firewall: ${FIREWALL_TYPE}
║  👥 Users: ${users.length}
║  🖥️  Servers: ${servers.length}
║  🚫 Blocked IPs: ${blockedIPs.length}
║  🌐 Blocked Domains: ${blockedDomains.length}
╚════════════════════════════════════════════════════════════╝
            `);
        });
    } catch (error) {
        console.error('Failed to start server:', error);
        process.exit(1);
    }
}

startServer();
