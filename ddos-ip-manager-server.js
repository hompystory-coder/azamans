const express = require('express');
const { exec } = require('child_process');
const fs = require('fs').promises;
const path = require('path');
const dns = require('dns').promises;

const app = express();
const PORT = process.env.PORT || 3105;

app.use(express.json());
app.use(express.static(__dirname));

// CORS 설정
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, X-API-Key');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    next();
});

// ============================================
// 멀티 플랫폼 지원 (CentOS 7, Ubuntu, Debian)
// ============================================

let FIREWALL_TYPE = null; // 'iptables', 'firewalld', 'ufw'
let OS_TYPE = null; // 'centos', 'ubuntu', 'debian'

// 시스템 감지
async function detectSystem() {
    try {
        // OS 감지
        const osRelease = await fs.readFile('/etc/os-release', 'utf-8');
        if (osRelease.includes('CentOS')) {
            OS_TYPE = 'centos';
        } else if (osRelease.includes('Ubuntu')) {
            OS_TYPE = 'ubuntu';
        } else if (osRelease.includes('Debian')) {
            OS_TYPE = 'debian';
        }

        // 방화벽 타입 감지
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
        OS_TYPE = 'unknown';
        FIREWALL_TYPE = 'iptables'; // fallback
    }
}

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

// ============================================
// IP 차단 관리 데이터베이스 (메모리 + 파일)
// ============================================

const BLOCKED_IPS_FILE = '/var/lib/neuralgrid/blocked-ips.json';
const BLOCKED_DOMAINS_FILE = '/var/lib/neuralgrid/blocked-domains.json';

let blockedIPs = [];
let blockedDomains = [];

// 데이터 로드
async function loadData() {
    try {
        await execPromise('sudo mkdir -p /var/lib/neuralgrid');
        
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

        console.log(`📚 Loaded ${blockedIPs.length} blocked IPs and ${blockedDomains.length} blocked domains`);
    } catch (error) {
        console.error('Failed to load data:', error.message);
    }
}

// 데이터 저장
async function saveData() {
    try {
        await fs.writeFile(BLOCKED_IPS_FILE, JSON.stringify(blockedIPs, null, 2));
        await fs.writeFile(BLOCKED_DOMAINS_FILE, JSON.stringify(blockedDomains, null, 2));
    } catch (error) {
        console.error('Failed to save data:', error.message);
    }
}

// ============================================
// 방화벽 명령어 래퍼
// ============================================

async function blockIPInFirewall(ip) {
    try {
        if (FIREWALL_TYPE === 'firewalld') {
            // CentOS 7, CentOS 8, RHEL
            await execPromise(`sudo firewall-cmd --permanent --add-rich-rule='rule family="ipv4" source address="${ip}" reject'`);
            await execPromise('sudo firewall-cmd --reload');
        } else if (FIREWALL_TYPE === 'ufw') {
            // Ubuntu, Debian (with UFW)
            await execPromise(`sudo ufw deny from ${ip}`);
        } else {
            // iptables (universal fallback)
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
            // iptables
            await execPromise(`sudo iptables -D INPUT -s ${ip} -j DROP`);
            await execPromise(`sudo iptables-save | sudo tee /etc/iptables/rules.v4 > /dev/null 2>&1 || sudo service iptables save || true`);
        }
        return { success: true };
    } catch (error) {
        throw new Error(`Firewall unblock failed: ${error.message}`);
    }
}

async function listBlockedIPsFromFirewall() {
    try {
        if (FIREWALL_TYPE === 'firewalld') {
            const output = await execPromise('sudo firewall-cmd --list-rich-rules');
            const ips = [];
            const lines = output.split('\n');
            for (const line of lines) {
                const match = line.match(/source address="([^"]+)"/);
                if (match) ips.push(match[1]);
            }
            return ips;
        } else if (FIREWALL_TYPE === 'ufw') {
            const output = await execPromise('sudo ufw status numbered');
            const ips = [];
            const lines = output.split('\n');
            for (const line of lines) {
                const match = line.match(/DENY IN\s+(\S+)/);
                if (match) ips.push(match[1]);
            }
            return ips;
        } else {
            // iptables
            const output = await execPromise('sudo iptables -L INPUT -n --line-numbers');
            const ips = [];
            const lines = output.split('\n');
            for (const line of lines) {
                const match = line.match(/DROP\s+all\s+--\s+(\S+)/);
                if (match && match[1] !== '0.0.0.0/0') ips.push(match[1]);
            }
            return ips;
        }
    } catch (error) {
        console.error('Failed to list blocked IPs from firewall:', error.message);
        return [];
    }
}

// ============================================
// IP 관리 API
// ============================================

// IP 차단
app.post('/api/firewall/block', async (req, res) => {
    try {
        const { ip, reason = '' } = req.body;

        if (!ip) {
            return res.status(400).json({ error: 'IP address is required' });
        }

        // IP 형식 검증
        const ipRegex = /^(\d{1,3}\.){3}\d{1,3}(\/\d{1,2})?$/;
        if (!ipRegex.test(ip)) {
            return res.status(400).json({ error: 'Invalid IP address format' });
        }

        // 이미 차단되어 있는지 확인
        const existing = blockedIPs.find(item => item.ip === ip);
        if (existing) {
            return res.json({ success: true, message: 'IP already blocked', alreadyBlocked: true });
        }

        // 방화벽에 차단 규칙 추가
        await blockIPInFirewall(ip);

        // 데이터베이스에 추가
        const blockEntry = {
            ip,
            reason,
            blockedAt: new Date().toISOString(),
            blockedBy: 'manual',
            method: FIREWALL_TYPE
        };

        blockedIPs.push(blockEntry);
        await saveData();

        res.json({ success: true, message: `IP ${ip} blocked successfully`, entry: blockEntry });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// IP 차단 해제
app.post('/api/firewall/unblock', async (req, res) => {
    try {
        const { ip } = req.body;

        if (!ip) {
            return res.status(400).json({ error: 'IP address is required' });
        }

        // 방화벽에서 차단 규칙 제거
        await unblockIPInFirewall(ip);

        // 데이터베이스에서 제거
        blockedIPs = blockedIPs.filter(item => item.ip !== ip);
        await saveData();

        res.json({ success: true, message: `IP ${ip} unblocked successfully` });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 차단된 IP 목록 조회
app.get('/api/firewall/list', async (req, res) => {
    try {
        // 방화벽에서 실제 차단된 IP와 동기화
        const firewallIPs = await listBlockedIPsFromFirewall();
        
        res.json({
            blocked: blockedIPs,
            firewallSync: firewallIPs,
            count: blockedIPs.length,
            firewallType: FIREWALL_TYPE,
            osType: OS_TYPE
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// 도메인 기반 차단 API
// ============================================

// 도메인 IP 조회
app.get('/api/firewall/lookup-domain', async (req, res) => {
    try {
        const { domain } = req.query;

        if (!domain) {
            return res.status(400).json({ error: 'Domain is required' });
        }

        // DNS 조회
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

// 도메인 차단 (도메인 -> IP 조회 -> 차단)
app.post('/api/firewall/block-domain', async (req, res) => {
    try {
        const { domain, reason = '' } = req.body;

        if (!domain) {
            return res.status(400).json({ error: 'Domain is required' });
        }

        // DNS 조회
        const addresses = await dns.resolve4(domain);

        if (addresses.length === 0) {
            return res.status(404).json({ error: 'No IP addresses found for domain' });
        }

        // 각 IP를 차단
        const blockedAddresses = [];
        for (const ip of addresses) {
            try {
                await blockIPInFirewall(ip);
                blockedAddresses.push(ip);

                // IP 목록에도 추가
                if (!blockedIPs.find(item => item.ip === ip)) {
                    blockedIPs.push({
                        ip,
                        reason: `Domain: ${domain} - ${reason}`,
                        blockedAt: new Date().toISOString(),
                        blockedBy: 'domain',
                        domain,
                        method: FIREWALL_TYPE
                    });
                }
            } catch (error) {
                console.error(`Failed to block ${ip}:`, error.message);
            }
        }

        // 도메인 목록에 추가
        const domainEntry = {
            domain,
            ips: blockedAddresses,
            reason,
            blockedAt: new Date().toISOString(),
            blockedBy: 'manual'
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

// 차단된 도메인 목록
app.get('/api/firewall/domains', async (req, res) => {
    try {
        res.json({
            domains: blockedDomains,
            count: blockedDomains.length
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 도메인 차단 해제
app.post('/api/firewall/unblock-domain', async (req, res) => {
    try {
        const { domain } = req.body;

        if (!domain) {
            return res.status(400).json({ error: 'Domain is required' });
        }

        // 도메인에 연결된 IP들 찾기
        const domainEntry = blockedDomains.find(item => item.domain === domain);
        
        if (!domainEntry) {
            return res.status(404).json({ error: 'Domain not found in blocked list' });
        }

        // 각 IP 차단 해제
        for (const ip of domainEntry.ips) {
            try {
                await unblockIPInFirewall(ip);
                blockedIPs = blockedIPs.filter(item => item.ip !== ip || item.domain !== domain);
            } catch (error) {
                console.error(`Failed to unblock ${ip}:`, error.message);
            }
        }

        // 도메인 목록에서 제거
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
// 국가별 차단 API (GeoIP - 추후 구현)
// ============================================

app.post('/api/firewall/block-countries', async (req, res) => {
    try {
        const { countries } = req.body;

        if (!countries || !Array.isArray(countries)) {
            return res.status(400).json({ error: 'Countries array is required' });
        }

        // TODO: GeoIP 데이터베이스 통합 필요
        // 현재는 placeholder 응답
        res.json({
            success: false,
            message: 'GeoIP blocking feature coming soon',
            note: 'This feature requires GeoIP database integration',
            requestedCountries: countries
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
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// 정적 파일 서빙
// ============================================

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'ddos-ip-manager.html'));
});

app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        version: '2.0.0',
        features: ['ddos-testing', 'ip-blocking', 'domain-blocking', 'geo-blocking-planned'],
        osType: OS_TYPE,
        firewallType: FIREWALL_TYPE,
        blockedIPs: blockedIPs.length,
        blockedDomains: blockedDomains.length,
        timestamp: new Date().toISOString()
    });
});

// ============================================
// 서버 시작
// ============================================

async function startServer() {
    try {
        // 시스템 감지
        await detectSystem();
        
        // 데이터 로드
        await loadData();

        // 서버 시작
        app.listen(PORT, '0.0.0.0', () => {
            console.log(`
╔════════════════════════════════════════════════════════════╗
║   🛡️  NeuralGrid Security & Performance Platform          ║
╠════════════════════════════════════════════════════════════╣
║  🌐 Dashboard: http://localhost:${PORT}                     
║  🔌 API: http://localhost:${PORT}/api                       
║  💻 OS: ${OS_TYPE}                                          
║  🔥 Firewall: ${FIREWALL_TYPE}                             
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
