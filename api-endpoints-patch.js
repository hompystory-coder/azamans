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

        // orders 배열에 추가 (전역 변수 또는 데이터베이스)
        if (!global.orders) global.orders = [];
        global.orders.push(order);
        await saveData();

        // TODO: 결제 시스템 연동 (Toss Payments, KG이니시스 등)
        // TODO: 이메일 발송 (결제 안내)

        res.json({
            success: true,
            message: '홈페이지 보호 신청이 완료되었습니다. 결제 안내 이메일을 확인해주세요.',
            order: {
                orderId,
                type: 'website',
                amount: 330000,
                currency: 'KRW',
                status: 'pending_payment',
                paymentUrl: `https://ddos.neuralgrid.kr/payment/${orderId}`,
                domains: domainList,
                estimatedActivationTime: '결제 완료 후 10분 이내'
            }
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 서버 보호 (₩2,990,000/년)
app.post('/api/servers/register-server', authMiddleware, async (req, res) => {
    try {
        const { companyName, phone, serverIps, domains, osType, purpose, trafficScale, description } = req.body;
        const userId = req.user.id;

        // serverIps를 배열로 변환 (쉼표로 구분된 문자열)
        const serverIpList = serverIps ? serverIps.split(',').map(ip => ip.trim()).filter(ip => ip) : [];
        
        if (serverIpList.length === 0) {
            return res.status(400).json({ error: '최소 1개의 서버 IP를 입력해주세요.' });
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
            domains: domainList,
            osType: osType || 'unknown',
            purpose: purpose || null,
            trafficScale: trafficScale || 'medium',
            description: description || null,
            amount: 2990000,
            currency: 'KRW',
            status: 'pending_payment', // pending_payment -> paid -> manager_assigned -> active
            serverId,
            managerId,
            managerName: '김담당', // TODO: 실제 매니저 배정 시스템
            managerPhone: '02-1234-5678',
            createdAt: new Date().toISOString(),
            expiresAt: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString() // 1년
        };

        // orders 배열에 추가
        if (!global.orders) global.orders = [];
        global.orders.push(order);
        await saveData();

        // TODO: 결제 시스템 연동
        // TODO: 이메일 발송 (결제 안내)
        // TODO: 전담 매니저 배정 알림

        res.json({
            success: true,
            message: '서버 보호 신청이 완료되었습니다. 결제 안내 이메일을 확인해주세요.',
            order: {
                orderId,
                type: 'server',
                amount: 2990000,
                currency: 'KRW',
                status: 'pending_payment',
                paymentUrl: `https://ddos.neuralgrid.kr/payment/${orderId}`,
                serverIps: serverIpList,
                domains: domainList,
                managerId,
                managerName: '김담당',
                managerPhone: '02-1234-5678',
                estimatedContactTime: '24시간 이내',
                slaGuarantee: '99.9%'
            }
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});
