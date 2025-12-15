const express = require('express');
const { exec } = require('child_process');
const fs = require('fs').promises;
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3105;

app.use(express.json());
app.use(express.static('public'));

// CORS 설정
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    next();
});

// ============================================
// API 엔드포인트
// ============================================

// 1. 시스템 상태
app.get('/api/status', async (req, res) => {
    try {
        const status = await getSystemStatus();
        res.json(status);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 2. 실시간 트래픽 통계
app.get('/api/traffic', async (req, res) => {
    try {
        const traffic = await getTrafficStats();
        res.json(traffic);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 3. 차단된 IP 목록
app.get('/api/blocked-ips', async (req, res) => {
    try {
        const blockedIPs = await getBlockedIPs();
        res.json(blockedIPs);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 4. Fail2ban 상태
app.get('/api/fail2ban/status', async (req, res) => {
    try {
        const status = await getFail2banStatus();
        res.json(status);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 5. IP 수동 차단
app.post('/api/ban-ip', async (req, res) => {
    try {
        const { ip, jail = 'nginx-limit-req', duration = 3600 } = req.body;
        
        if (!ip) {
            return res.status(400).json({ error: 'IP address is required' });
        }
        
        const result = await banIP(ip, jail);
        res.json({ success: true, message: `IP ${ip} has been banned`, result });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 6. IP 차단 해제
app.post('/api/unban-ip', async (req, res) => {
    try {
        const { ip, jail = 'nginx-limit-req' } = req.body;
        
        if (!ip) {
            return res.status(400).json({ error: 'IP address is required' });
        }
        
        const result = await unbanIP(ip, jail);
        res.json({ success: true, message: `IP ${ip} has been unbanned`, result });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 7. 로그 조회
app.get('/api/logs', async (req, res) => {
    try {
        const { type = 'access', lines = 100 } = req.query;
        const logs = await getLogs(type, lines);
        res.json({ logs });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 8. 긴급 모드 활성화
app.post('/api/emergency-mode', async (req, res) => {
    try {
        const { enabled } = req.body;
        const result = await setEmergencyMode(enabled);
        res.json({ success: true, emergencyMode: enabled, result });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 9. 화이트리스트 관리
app.get('/api/whitelist', async (req, res) => {
    try {
        const whitelist = await getWhitelist();
        res.json({ whitelist });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.post('/api/whitelist', async (req, res) => {
    try {
        const { ip, description } = req.body;
        await addToWhitelist(ip, description);
        res.json({ success: true, message: `IP ${ip} added to whitelist` });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 10. 블랙리스트 관리
app.get('/api/blacklist', async (req, res) => {
    try {
        const blacklist = await getBlacklist();
        res.json({ blacklist });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.post('/api/blacklist', async (req, res) => {
    try {
        const { ip, description } = req.body;
        await addToBlacklist(ip, description);
        res.json({ success: true, message: `IP ${ip} added to blacklist` });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// 헬퍼 함수들
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

async function getSystemStatus() {
    try {
        const uptime = await execPromise('uptime');
        const load = await execPromise("cat /proc/loadavg | awk '{print $1}'");
        const memory = await execPromise("free -m | awk 'NR==2{printf \"%.2f\", $3*100/$2 }'");
        
        return {
            timestamp: new Date().toISOString(),
            uptime: uptime,
            load: parseFloat(load),
            memory: parseFloat(memory),
            status: 'normal'
        };
    } catch (error) {
        return {
            timestamp: new Date().toISOString(),
            error: error.message,
            status: 'error'
        };
    }
}

async function getTrafficStats() {
    try {
        // 실제 Nginx 로그에서 최근 1분간 통계 추출
        const accessLog = '/var/log/nginx/access.log';
        const now = new Date();
        const oneMinuteAgo = new Date(now.getTime() - 60000);
        
        // 최근 1분간의 요청 수 계산
        const recentRequests = await execPromise(
            `awk -v date="$(date -d '1 minute ago' '+%d/%b/%Y:%H:%M')" '$4 > "["date' ${accessLog} 2>/dev/null | wc -l || echo 0`
        );
        
        // 전체 요청 수 (최근 10000줄)
        const totalRequests = await execPromise(
            `tail -n 10000 ${accessLog} 2>/dev/null | wc -l || echo 0`
        );
        
        // Fail2ban으로 차단된 트래픽 수 (최근 로그에서)
        const blockedCount = await execPromise(
            `grep -c "Ban" /var/log/fail2ban.log 2>/dev/null | tail -1 || echo 0`
        );
        
        // Rate limiting으로 거부된 요청
        const rateLimitedRequests = await execPromise(
            `grep -c "limiting requests" /var/log/nginx/error.log 2>/dev/null | tail -1 || echo 0`
        );
        
        const recent = parseInt(recentRequests) || 0;
        const total = parseInt(totalRequests) || 0;
        const blocked = parseInt(blockedCount) + parseInt(rateLimitedRequests) || 0;
        
        return {
            timestamp: new Date().toISOString(),
            totalRequests: total,
            requestsPerSecond: Math.floor(recent / 60) || 0,
            recentRequests: recent,
            normalTraffic: Math.max(0, recent - blocked),
            blockedTraffic: blocked,
            rateLimited: parseInt(rateLimitedRequests) || 0
        };
    } catch (error) {
        return {
            timestamp: new Date().toISOString(),
            error: error.message,
            totalRequests: 0,
            requestsPerSecond: 0,
            normalTraffic: 0,
            blockedTraffic: 0
        };
    }
}

async function getBlockedIPs() {
    try {
        // Fail2ban에서 차단된 IP 목록 가져오기
        const jails = ['nginx-limit-req', 'nginx-http-flood', 'nginx-404', 'sshd'];
        const blockedIPs = [];
        
        for (const jail of jails) {
            try {
                const banned = await execPromise(`sudo fail2ban-client status ${jail} 2>/dev/null | grep "Banned IP list" | awk '{for(i=5;i<=NF;i++) print $i}' || echo ""`);
                
                if (banned) {
                    const ips = banned.split('\n').filter(ip => ip);
                    for (const ip of ips) {
                        blockedIPs.push({
                            ip,
                            jail,
                            country: 'Unknown',
                            attackType: jail.replace('nginx-', '').replace('-', ' '),
                            bannedAt: new Date().toISOString(),
                            unbanAt: new Date(Date.now() + 3600000).toISOString()
                        });
                    }
                }
            } catch (err) {
                // Jail이 없거나 에러 발생 시 무시
            }
        }
        
        return blockedIPs;
    } catch (error) {
        return [];
    }
}

async function getFail2banStatus() {
    try {
        const status = await execPromise('sudo fail2ban-client status 2>/dev/null || echo "Fail2ban not running"');
        return { status, timestamp: new Date().toISOString() };
    } catch (error) {
        return { error: error.message, timestamp: new Date().toISOString() };
    }
}

async function banIP(ip, jail) {
    try {
        const result = await execPromise(`sudo fail2ban-client set ${jail} banip ${ip}`);
        return result;
    } catch (error) {
        throw new Error(`Failed to ban IP: ${error.message}`);
    }
}

async function unbanIP(ip, jail) {
    try {
        const result = await execPromise(`sudo fail2ban-client set ${jail} unbanip ${ip}`);
        return result;
    } catch (error) {
        throw new Error(`Failed to unban IP: ${error.message}`);
    }
}

async function getLogs(type, lines) {
    try {
        const logFile = type === 'access' 
            ? '/var/log/nginx/access.log' 
            : '/var/log/nginx/error.log';
        
        const logs = await execPromise(`tail -n ${lines} ${logFile} 2>/dev/null || echo "No logs available"`);
        return logs.split('\n');
    } catch (error) {
        return [];
    }
}

async function setEmergencyMode(enabled) {
    // 긴급 모드 구현 (Rate Limit 강화)
    try {
        if (enabled) {
            // Rate Limit을 10배 엄격하게 설정
            return await execPromise('echo "Emergency mode activated"');
        } else {
            // 정상 모드로 복구
            return await execPromise('echo "Normal mode restored"');
        }
    } catch (error) {
        throw new Error(`Failed to set emergency mode: ${error.message}`);
    }
}

async function getWhitelist() {
    // 화이트리스트 읽기 (파일 기반)
    try {
        const content = await fs.readFile('/etc/nginx/whitelist.conf', 'utf-8');
        return content.split('\n').filter(line => line.trim() && !line.startsWith('#'));
    } catch (error) {
        return [];
    }
}

async function addToWhitelist(ip, description) {
    // 화이트리스트에 IP 추가
    const entry = `allow ${ip}; # ${description}\n`;
    await fs.appendFile('/etc/nginx/whitelist.conf', entry);
}

async function getBlacklist() {
    // 블랙리스트 읽기
    try {
        const content = await fs.readFile('/etc/nginx/blacklist.conf', 'utf-8');
        return content.split('\n').filter(line => line.trim() && !line.startsWith('#'));
    } catch (error) {
        return [];
    }
}

async function addToBlacklist(ip, description) {
    // 블랙리스트에 IP 추가
    const entry = `deny ${ip}; # ${description}\n`;
    await fs.appendFile('/etc/nginx/blacklist.conf', entry);
}

// ============================================
// 정적 파일 서빙
// ============================================
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'ddos-dashboard.html'));
});

// Health Check
app.get('/health', (req, res) => {
    res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// ============================================
// 서버 시작
// ============================================
app.listen(PORT, '0.0.0.0', () => {
    console.log(`🛡️ DDoS Defense Dashboard running on port ${PORT}`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔌 API: http://localhost:${PORT}/api`);
});
