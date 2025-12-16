/**
 * PayApp 클라이언트 결제 모듈 (프론트엔드)
 * ===========================================
 * 브라우저에서 PayApp 결제창을 호출하는 JavaScript 모듈
 * 
 * 사용법:
 * 1. HTML에 스크립트 포함:
 *    <script src="https://lite.payapp.kr/public/api/v2/payapp-lite.js"></script>
 *    <script src="/payapp-client.js"></script>
 * 
 * 2. 결제 호출:
 *    NeuralGridPayment.startPayment({
 *        service: 'ddos',
 *        goodname: 'DDoS 보안 서비스',
 *        price: 330000,
 *        orderId: 'ORD-123456'
 *    });
 * 
 * @author GenSpark AI Developer
 * @version 1.0
 * @date 2025-12-16
 */

(function(window) {
    'use strict';

    // PayApp 스크립트 로드 확인
    if (typeof PayApp === 'undefined') {
        console.error('[NeuralGrid Payment] ❌ PayApp script not loaded. Please include: https://lite.payapp.kr/public/api/v2/payapp-lite.js');
        return;
    }

    /**
     * NeuralGrid 통합 결제 시스템
     */
    class NeuralGridPayment {
        constructor() {
            this.config = {
                // 서비스별 상점명
                shopNames: {
                    'ddos': 'NeuralGrid DDoS 보안',
                    'neuralgrid': 'NeuralGrid 메인',
                    'music': 'NeuronStar Music',
                    'shorts': 'Shorts Market'
                },
                // 기본 설정
                userid: 'asg701',
                smsuse: 'y',
                checkretry: 'y'
            };
        }

        /**
         * 결제 시작
         * @param {Object} paymentData - 결제 정보
         */
        async startPayment(paymentData) {
            try {
                const {
                    service,        // 서비스 이름
                    goodname,       // 상품명
                    price,          // 금액
                    orderId,        // 주문번호
                    userId,         // 사용자 ID
                    recvphone,      // 전화번호 (선택)
                    recvemail,      // 이메일 (선택)
                    memo,           // 메모
                    feedbackUrl,    // 결과 수신 URL
                    returnUrl,      // 완료 후 이동 URL
                    paymentMethods, // 결제 수단 제한
                    taxable,        // 과세 금액
                    taxfree,        // 면세 금액
                    vat,            // 부가세
                    onSuccess,      // 성공 콜백
                    onError         // 실패 콜백
                } = paymentData;

                // 필수 검증
                if (!service || !goodname || !price) {
                    throw new Error('필수 파라미터가 누락되었습니다.');
                }

                // 상점명 가져오기
                const shopname = this.config.shopNames[service] || 'NeuralGrid Service';

                console.log('[Payment] 💳 결제 시작:', {
                    service,
                    goodname,
                    price,
                    orderId
                });

                // PayApp 파라미터 설정
                PayApp.setDefault('userid', this.config.userid);
                PayApp.setDefault('shopname', shopname);
                PayApp.setDefault('smsuse', this.config.smsuse);
                PayApp.setDefault('checkretry', this.config.checkretry);

                // 결제 정보 설정
                const params = {
                    goodname: goodname,
                    price: parseInt(price),
                    var1: orderId || '',
                    var2: userId || '',
                    memo: memo || `${shopname} 결제`,
                    feedbackurl: feedbackUrl || window.location.origin + '/api/payment/feedback',
                    returnurl: returnUrl || window.location.href,
                    recvphone: recvphone || '',
                    recvemail: recvemail || '',
                    openpaytype: paymentMethods || '',
                    amount_taxable: taxable || 0,
                    amount_taxfree: taxfree || 0,
                    amount_vat: vat || 0,
                    buyerid: userId || ''
                };

                // 결제창 호출
                PayApp.payrequest(params);

                // 성공 콜백 (결제창이 닫힌 후)
                if (onSuccess) {
                    setTimeout(() => {
                        onSuccess({
                            orderId: orderId,
                            goodname: goodname,
                            price: price
                        });
                    }, 1000);
                }

            } catch (error) {
                console.error('[Payment] ❌ 결제 오류:', error);
                if (paymentData.onError) {
                    paymentData.onError(error);
                } else {
                    alert('결제 오류: ' + error.message);
                }
            }
        }

        /**
         * DDoS 보안 서비스 결제
         * @param {Object} orderData - 주문 정보
         */
        payForDDoS(orderData) {
            const {
                orderId,
                productType,    // 'website' or 'server'
                quantity,       // 서버 수량
                price,
                userInfo        // { phone, email }
            } = orderData;

            const productNames = {
                'website': '홈페이지 보호 (1년)',
                'server': `서버 보호 (${quantity}대, 1년)`
            };

            return this.startPayment({
                service: 'ddos',
                goodname: productNames[productType] || 'DDoS 보안 서비스',
                price: price,
                orderId: orderId,
                userId: userInfo?.userId,
                recvphone: userInfo?.phone,
                recvemail: userInfo?.email,
                memo: `NeuralGrid DDoS ${productType === 'website' ? '홈페이지' : '서버'} 보호`,
                feedbackUrl: 'https://ddos.neuralgrid.kr/api/payment/feedback',
                returnUrl: 'https://ddos.neuralgrid.kr/mypage.html',
                paymentMethods: 'card,phone,kakaopay,naverpay,tosspay',
                taxable: price,
                vat: Math.floor(price * 0.1)
            });
        }

        /**
         * 서비스 등록
         * @param {string} serviceKey - 서비스 키
         * @param {string} shopName - 상점명
         */
        registerService(serviceKey, shopName) {
            this.config.shopNames[serviceKey] = shopName;
            console.log(`[Payment] ✅ 서비스 등록: ${serviceKey} → ${shopName}`);
        }

        /**
         * 결제 수단 한글명
         */
        getPaymentTypeName(payType) {
            const types = {
                'card': '신용카드',
                'phone': '휴대전화',
                'rbank': '계좌이체',
                'vbank': '가상계좌',
                'kakaopay': '카카오페이',
                'naverpay': '네이버페이',
                'smilepay': '스마일페이',
                'applepay': '애플페이',
                'payco': '페이코',
                'wechat': '위챗페이',
                'myaccount': '내통장결제',
                'tosspay': '토스페이'
            };
            return types[payType] || payType;
        }
    }

    // 전역 인스턴스 생성
    window.NeuralGridPayment = new NeuralGridPayment();

    console.log('[Payment] ✅ NeuralGrid Payment System initialized');

})(window);
