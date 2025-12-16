// 서버 보호 (₩2,990,000/년) - 서버 수량 로직 업데이트
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
            status: isCustomQuote ? 'pending_quote' : 'pending_payment', // pending_quote, pending_payment -> paid -> manager_assigned -> active
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
        // TODO: 이메일 발송 (결제 안내 또는 견적 안내)
        // TODO: 전담 매니저 배정 알림

        const responseMessage = isCustomQuote 
            ? '서버 보호 신청이 완료되었습니다. 24시간 이내 담당자가 견적을 안내드립니다.'
            : '서버 보호 신청이 완료되었습니다. 결제 안내 이메일을 확인해주세요.';

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
            }
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});
