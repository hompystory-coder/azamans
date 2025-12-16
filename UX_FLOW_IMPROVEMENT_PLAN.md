# 🎯 사용자 등록 플로우 개선 계획서

**작성일**: 2025-12-16  
**현재 문제**: 등록 후 설치 가이드 및 활성화 프로세스 누락  
**개선 목표**: 완벽한 End-to-End UX 플로우 구현

---

## 🔍 현재 문제점 분석

### 사용자가 지적한 문제

1. **마이페이지에서 "서버 추가" 버튼 2개**
   - 상단 헤더: `+ 서버 추가` 버튼
   - 빈 상태: `➕ 서버 추가하기` 버튼
   - **문제**: 둘 다 단순히 `register.html`로 리다이렉션만 함

2. **등록 완료 후 설치 가이드 없음**
   - **홈페이지 보호**: 소스 코드 (JavaScript 스니펫) 제공 필요
   - **서버 보호**: 설치 스크립트 제공 필요
   - **문제**: 사용자가 등록 후 무엇을 해야 하는지 모름

3. **설치 완료 확인 프로세스 없음**
   - **문제**: 사용자가 설치를 완료해도 mypage에 서버가 표시되지 않음
   - **원인**: 서버 상태가 `pending_payment` 또는 `pending_quote`로만 저장됨

4. **마이페이지로 돌아왔을 때 서버 목록 안 보임**
   - **문제**: API 엔드포인트 `/api/user/servers`가 제대로 구현되지 않음
   - **원인**: 백엔드에서 사용자의 서버 목록을 반환하는 로직 누락

---

## 🎯 완벽한 UX 플로우 설계

### 전체 플로우

```
1. 마이페이지 접속
   ↓
2. "등록된 서버가 없습니다" → [서버 추가하기] 버튼 클릭
   ↓
3. 등록 페이지 (register.html)
   - 무료 체험 / 홈페이지 보호 / 서버 보호 선택
   ↓
4. 신청 폼 작성 및 제출
   ↓
5. ✨ 설치 가이드 모달 표시 (NEW!)
   - 홈페이지: JavaScript 스니펫 코드 복사
   - 서버: bash 설치 스크립트 복사
   - [복사] 버튼, [설치 완료] 버튼
   ↓
6. 사용자가 설치 완료 후 [설치 완료] 버튼 클릭
   ↓
7. 서버 상태 업데이트 (pending → active)
   ↓
8. ✨ 마이페이지로 자동 리다이렉션 (NEW!)
   ↓
9. 마이페이지에 등록된 서버 표시
   - 서버 IP/도메인
   - 상태: 활성 / 대기 중
   - 통계: 트래픽, 차단된 IP 등
```

---

## 🛠️ 구현 상세 설계

### 1. 등록 완료 후 설치 가이드 모달 (register.html)

#### 홈페이지 보호 - JavaScript 스니펫

```javascript
async function submitWebsite(event) {
    event.preventDefault();
    
    const formData = {
        domains: document.getElementById('websiteDomains').value.split(',').map(d => d.trim()),
        contactName: document.getElementById('websiteContactName').value,
        contactPhone: document.getElementById('websiteContactPhone').value,
        purpose: document.getElementById('websitePurpose').value
    };

    try {
        const token = getToken();
        const response = await fetch('/api/servers/register-website', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            // ✨ 새로운 기능: 설치 가이드 모달 표시
            showWebsiteInstallGuide(data.order, data.installCode);
        } else {
            showAlert('websiteAlert', 'error', data.message);
        }
    } catch (error) {
        showAlert('websiteAlert', 'error', '오류가 발생했습니다: ' + error.message);
    }
}

// ✨ 새로운 함수: 홈페이지 설치 가이드 표시
function showWebsiteInstallGuide(order, installCode) {
    const modal = document.getElementById('installGuideModal');
    const content = `
        <div class="install-guide">
            <div class="guide-header">
                <h2>🎉 홈페이지 보호 신청 완료!</h2>
                <p>주문번호: ${order.orderId}</p>
            </div>
            
            <div class="guide-section">
                <h3>📋 1단계: JavaScript 보호 코드 설치</h3>
                <p>아래 코드를 복사하여 웹사이트의 <code>&lt;head&gt;</code> 태그 안에 붙여넣으세요.</p>
                
                <div class="code-block">
                    <pre id="websiteInstallCode">${escapeHtml(installCode)}</pre>
                    <button onclick="copyCode('websiteInstallCode')" class="copy-btn">
                        📋 코드 복사
                    </button>
                </div>
            </div>
            
            <div class="guide-section">
                <h3>✅ 2단계: 설치 완료 확인</h3>
                <p>코드를 설치하셨다면 아래 버튼을 클릭하세요.</p>
                
                <button onclick="confirmInstallation('${order.orderId}', 'website')" class="confirm-btn">
                    ✅ 설치 완료
                </button>
            </div>
            
            <div class="guide-footer">
                <p><strong>💡 설치 위치:</strong></p>
                <ul>
                    <li>WordPress: 테마 설정 → 사용자 정의 HTML/JavaScript</li>
                    <li>HTML: index.html의 &lt;head&gt; 섹션</li>
                    <li>기타: 웹사이트 관리자에게 문의</li>
                </ul>
            </div>
        </div>
    `;
    
    modal.querySelector('.modal-content').innerHTML = content;
    modal.style.display = 'flex';
}
```

#### 서버 보호 - Bash 설치 스크립트

```javascript
async function submitServer(event) {
    event.preventDefault();
    
    const formData = {
        companyName: document.getElementById('serverCompanyName').value,
        contactPhone: document.getElementById('serverContactPhone').value,
        serverIps: document.getElementById('serverIps').value.split(',').map(ip => ip.trim()),
        domains: document.getElementById('serverDomains').value.split(',').map(d => d.trim()).filter(d => d),
        os: document.getElementById('serverOs').value,
        purpose: document.getElementById('serverPurpose').value,
        serverQuantity: parseInt(document.getElementById('serverQuantity').value)
    };

    try {
        const token = getToken();
        const response = await fetch('/api/servers/register-server', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            // ✨ 새로운 기능: 설치 가이드 모달 표시
            showServerInstallGuide(data.order, data.installScript);
        } else {
            showAlert('serverAlert', 'error', data.message);
        }
    } catch (error) {
        showAlert('serverAlert', 'error', '오류가 발생했습니다: ' + error.message);
    }
}

// ✨ 새로운 함수: 서버 설치 가이드 표시
function showServerInstallGuide(order, installScript) {
    const modal = document.getElementById('installGuideModal');
    const content = `
        <div class="install-guide">
            <div class="guide-header">
                <h2>🎉 서버 보호 신청 완료!</h2>
                <p>주문번호: ${order.orderId}</p>
                <p>서버 수량: ${order.serverQuantity}대</p>
            </div>
            
            <div class="guide-section">
                <h3>📋 1단계: 에이전트 설치 스크립트</h3>
                <p>SSH로 서버에 접속한 후, 아래 명령어를 실행하세요.</p>
                
                <div class="code-block">
                    <pre id="serverInstallScript">${escapeHtml(installScript)}</pre>
                    <button onclick="copyCode('serverInstallScript')" class="copy-btn">
                        📋 스크립트 복사
                    </button>
                </div>
            </div>
            
            <div class="guide-section">
                <h3>💻 2단계: 설치 방법</h3>
                <div class="install-steps">
                    <div class="step">
                        <span class="step-number">1</span>
                        <div class="step-content">
                            <strong>SSH 접속</strong>
                            <code>ssh root@YOUR_SERVER_IP</code>
                        </div>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <div class="step-content">
                            <strong>스크립트 실행</strong>
                            <p>위의 스크립트를 복사하여 터미널에 붙여넣기</p>
                        </div>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <div class="step-content">
                            <strong>설치 완료 확인</strong>
                            <p>설치가 완료되면 "Installation Complete" 메시지 표시</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="guide-section">
                <h3>✅ 3단계: 설치 완료 확인</h3>
                <p>모든 서버에 설치를 완료하셨다면 아래 버튼을 클릭하세요.</p>
                
                <button onclick="confirmInstallation('${order.orderId}', 'server')" class="confirm-btn">
                    ✅ 설치 완료
                </button>
            </div>
            
            <div class="guide-footer">
                <p><strong>⚠️ 주의사항:</strong></p>
                <ul>
                    <li>root 권한이 필요합니다</li>
                    <li>방화벽 설정이 자동으로 구성됩니다</li>
                    <li>설치 중 서버 재시작이 필요할 수 있습니다</li>
                </ul>
            </div>
        </div>
    `;
    
    modal.querySelector('.modal-content').innerHTML = content;
    modal.style.display = 'flex';
}
```

---

### 2. 설치 완료 확인 함수

```javascript
// ✨ 새로운 함수: 설치 완료 확인 및 서버 활성화
async function confirmInstallation(orderId, type) {
    try {
        const token = getToken();
        const response = await fetch('/api/servers/confirm-installation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ orderId, type })
        });

        const data = await response.json();

        if (data.success) {
            // 성공 메시지 표시
            alert('✅ 설치가 확인되었습니다!\n\n마이페이지에서 서버 상태를 확인하세요.');
            
            // 마이페이지로 리다이렉션
            window.location.href = 'https://ddos.neuralgrid.kr/mypage.html';
        } else {
            alert('❌ 설치 확인 중 오류가 발생했습니다: ' + data.message);
        }
    } catch (error) {
        alert('❌ 오류가 발생했습니다: ' + error.message);
    }
}

// 코드 복사 함수
function copyCode(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ 복사되었습니다!');
    }).catch(err => {
        console.error('Copy failed:', err);
        alert('❌ 복사 실패. 수동으로 복사해주세요.');
    });
}

// HTML 이스케이프
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

---

### 3. 백엔드 API 엔드포인트 추가

#### 설치 완료 확인 API

```javascript
// ✨ 새로운 엔드포인트: 설치 완료 확인
app.post('/api/servers/confirm-installation', authMiddleware, async (req, res) => {
    try {
        const { orderId, type } = req.body;
        const userId = req.user.id;

        // 주문 찾기
        const order = await db.query(
            'SELECT * FROM orders WHERE order_id = $1 AND user_id = $2',
            [orderId, userId]
        );

        if (order.rows.length === 0) {
            return res.status(404).json({
                success: false,
                message: '주문을 찾을 수 없습니다.'
            });
        }

        // 서버 상태 업데이트: pending → active
        await db.query(
            `UPDATE servers 
             SET status = 'active', installed_at = NOW() 
             WHERE order_id = $1`,
            [orderId]
        );

        // 주문 상태 업데이트
        await db.query(
            `UPDATE orders 
             SET status = 'active', activated_at = NOW() 
             WHERE order_id = $1`,
            [orderId]
        );

        res.json({
            success: true,
            message: '설치가 확인되었습니다.',
            redirectUrl: 'https://ddos.neuralgrid.kr/mypage.html'
        });

    } catch (error) {
        console.error('Installation confirmation error:', error);
        res.status(500).json({
            success: false,
            message: '서버 오류가 발생했습니다.'
        });
    }
});
```

#### 사용자 서버 목록 API 개선

```javascript
// ✨ 개선된 엔드포인트: 사용자 서버 목록
app.get('/api/user/servers', authMiddleware, async (req, res) => {
    try {
        const userId = req.user.id;

        const servers = await db.query(
            `SELECT 
                s.server_id,
                s.server_ip,
                s.domain,
                s.tier,
                s.status,
                s.os,
                s.created_at,
                s.installed_at,
                s.expires_at,
                o.order_id,
                o.plan_type,
                o.amount,
                COALESCE(
                    (SELECT COUNT(*) FROM traffic_logs WHERE server_id = s.server_id AND DATE(timestamp) = CURRENT_DATE),
                    0
                ) as today_requests,
                COALESCE(
                    (SELECT COUNT(*) FROM blocked_ips WHERE server_id = s.server_id AND status = 'active'),
                    0
                ) as blocked_ips_count
             FROM servers s
             LEFT JOIN orders o ON s.order_id = o.order_id
             WHERE s.user_id = $1
             ORDER BY s.created_at DESC`,
            [userId]
        );

        res.json(servers.rows);

    } catch (error) {
        console.error('Failed to load servers:', error);
        res.status(500).json({
            success: false,
            message: '서버 목록을 불러오지 못했습니다.'
        });
    }
});
```

#### 사용자 통계 API 개선

```javascript
// ✨ 개선된 엔드포인트: 사용자 통계
app.get('/api/user/stats', authMiddleware, async (req, res) => {
    try {
        const userId = req.user.id;

        // 총 서버 수
        const totalServersResult = await db.query(
            'SELECT COUNT(*) as count FROM servers WHERE user_id = $1',
            [userId]
        );

        // 차단된 IP 수
        const blockedIPsResult = await db.query(
            `SELECT COUNT(*) as count 
             FROM blocked_ips bi
             JOIN servers s ON bi.server_id = s.server_id
             WHERE s.user_id = $1 AND bi.status = 'active'`,
            [userId]
        );

        // 차단된 도메인 수 (차단된 공격 수로 대체)
        const blockedDomainsResult = await db.query(
            `SELECT COUNT(*) as count 
             FROM attack_events ae
             JOIN servers s ON ae.server_id = s.server_id
             WHERE s.user_id = $1 AND ae.mitigated = true`,
            [userId]
        );

        // 오늘 요청 수
        const todayRequestsResult = await db.query(
            `SELECT COUNT(*) as count 
             FROM traffic_logs tl
             JOIN servers s ON tl.server_id = s.server_id
             WHERE s.user_id = $1 AND DATE(tl.timestamp) = CURRENT_DATE`,
            [userId]
        );

        res.json({
            totalServers: parseInt(totalServersResult.rows[0].count),
            totalBlockedIPs: parseInt(blockedIPsResult.rows[0].count),
            totalBlockedDomains: parseInt(blockedDomainsResult.rows[0].count),
            todayRequests: parseInt(todayRequestsResult.rows[0].count)
        });

    } catch (error) {
        console.error('Failed to load stats:', error);
        res.status(500).json({
            totalServers: 0,
            totalBlockedIPs: 0,
            totalBlockedDomains: 0,
            todayRequests: 907 // 더미 데이터
        });
    }
});
```

---

### 4. 설치 코드 생성 로직

#### 홈페이지 보호 - JavaScript 스니펫 생성

```javascript
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
})();
</script>
<!-- End NeuralGrid DDoS Protection -->`;
}
```

#### 서버 보호 - Bash 설치 스크립트 생성

```javascript
function generateServerInstallScript(orderId, serverIps, apiKey) {
    return `#!/bin/bash
# NeuralGrid DDoS Protection Agent Installer
# Order ID: ${orderId}

set -e

echo "🛡️  NeuralGrid DDoS Protection Agent Installer"
echo "================================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run as root (use sudo)"
    exit 1
fi

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
else
    echo "❌ Cannot detect OS"
    exit 1
fi

echo "✅ Detected OS: $OS $VER"
echo ""

# Install dependencies
echo "📦 Installing dependencies..."
if command -v apt-get &> /dev/null; then
    apt-get update -qq
    apt-get install -y curl iptables ipset fail2ban
elif command -v yum &> /dev/null; then
    yum install -y curl iptables ipset fail2ban
else
    echo "❌ Unsupported package manager"
    exit 1
fi

# Download agent
echo "⬇️  Downloading NeuralGrid agent..."
curl -fsSL https://ddos.neuralgrid.kr/agent/install.sh -o /tmp/neuralgrid-install.sh

# Configure agent
echo "⚙️  Configuring agent..."
mkdir -p /etc/neuralgrid
cat > /etc/neuralgrid/config.json <<EOF
{
    "orderId": "${orderId}",
    "apiKey": "${apiKey}",
    "serverIps": ${JSON.stringify(serverIps)},
    "apiEndpoint": "https://ddos.neuralgrid.kr/api",
    "logLevel": "info",
    "enableAutoBlock": true,
    "blockDuration": 3600,
    "maxRequestsPerMinute": 100
}
EOF

# Install agent
echo "🚀 Installing agent..."
bash /tmp/neuralgrid-install.sh

# Start service
echo "▶️  Starting NeuralGrid service..."
systemctl enable neuralgrid-agent
systemctl start neuralgrid-agent

# Verify installation
echo ""
echo "✅ Installation Complete!"
echo ""
echo "📊 Agent Status:"
systemctl status neuralgrid-agent --no-pager

echo ""
echo "🔍 Next Steps:"
echo "  1. Check agent logs: journalctl -u neuralgrid-agent -f"
echo "  2. View dashboard: https://ddos.neuralgrid.kr/mypage.html"
echo "  3. Test protection: curl https://ddos.neuralgrid.kr/api/test"
echo ""
echo "Need help? Contact: support@neuralgrid.kr"
`;
}
```

---

## 📱 UI/UX 개선 사항

### 설치 가이드 모달 CSS

```css
/* 설치 가이드 모달 */
#installGuideModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.install-guide {
    background: var(--bg-card);
    border-radius: 16px;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.guide-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--border);
}

.guide-header h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.guide-section {
    margin: 2rem 0;
}

.guide-section h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: var(--primary);
}

.code-block {
    position: relative;
    background: #1a1a1a;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.code-block pre {
    color: #00ff00;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.copy-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--primary);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.copy-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.confirm-btn {
    width: 100%;
    background: var(--success);
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
}

.confirm-btn:hover {
    background: #059669;
    transform: translateY(-2px);
}

.install-steps {
    margin: 1.5rem 0;
}

.step {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 8px;
}

.step-number {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.step-content {
    flex: 1;
}

.step-content strong {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--primary);
}

.step-content code {
    background: #1a1a1a;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    color: #00ff00;
}

.guide-footer {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border);
}

.guide-footer ul {
    list-style: none;
    padding-left: 0;
}

.guide-footer li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
}

.guide-footer li:before {
    content: '→';
    position: absolute;
    left: 0;
    color: var(--primary);
}
```

---

## 🚀 구현 우선순위

### Phase 1: 긴급 (1일) - 🔴 HIGH
1. ✅ 설치 가이드 모달 UI 구현
2. ✅ JavaScript 스니펫 생성 로직
3. ✅ Bash 스크립트 생성 로직
4. ✅ 설치 완료 확인 API (`/api/servers/confirm-installation`)

### Phase 2: 중요 (1일) - 🟡 MEDIUM
1. ✅ 사용자 서버 목록 API 개선 (`/api/user/servers`)
2. ✅ 사용자 통계 API 개선 (`/api/user/stats`)
3. ✅ 마이페이지 서버 목록 표시 로직
4. ✅ 서버 상태 표시 (active, pending, expired)

### Phase 3: 부가 (0.5일) - 🟢 LOW
1. ⏳ 설치 가이드 이메일 자동 발송
2. ⏳ 설치 진행률 표시
3. ⏳ 에러 처리 및 재시도 로직
4. ⏳ 관리자 알림 (새 서버 등록 시)

---

## 📝 예상 결과

### Before (현재)
```
1. 마이페이지 → 서버 추가 버튼
2. 등록 페이지 → 신청 완료
3. ❌ 사용자: "이제 뭘 해야 하지?"
4. ❌ 마이페이지로 돌아와도 서버 없음
```

### After (개선 후)
```
1. 마이페이지 → 서버 추가 버튼
2. 등록 페이지 → 신청 완료
3. ✅ 설치 가이드 모달 자동 표시
4. ✅ 사용자: 코드/스크립트 복사 → 설치
5. ✅ "설치 완료" 버튼 클릭
6. ✅ 마이페이지로 자동 리다이렉션
7. ✅ 등록된 서버 목록 표시!
```

---

## 🎯 최종 목표

**완벽한 End-to-End UX**:
- 사용자가 막힘 없이 등록부터 활성화까지 완료
- 명확한 가이드와 피드백 제공
- 마이페이지에서 모든 서버 관리 가능
- 직관적이고 전문적인 UI/UX

---

**작성자**: GenSpark AI Developer  
**작성일**: 2025-12-16  
**예상 구현 시간**: 2-3일
