/**
 * PayApp 통합 결제 시스템
 * ===========================================
 * NeuralGrid 플랫폼의 모든 서브 서비스에서 사용하는 통합 결제 모듈
 * 
 * 판매자 정보:
 * - 아이디: asg701
 * - LinkKey: n3RxEKA9UFZ2yN9Y5MJIqu1DPJnCCRVaOgT+oqg6zaM=
 * - LinkValue: n3RxEKA9UFZ2yN9Y5MJIqt+Djo1UzL1TZBMuvg+MK+E=
 * 
 * @author GenSpark AI Developer
 * @version 1.0
 * @date 2025-12-16
 */

const crypto = require('crypto');
const fetch = require('node-fetch');
const querystring = require('querystring');

class PayAppIntegration {
    constructor() {
        // ✨ 판매자 인증 정보 (보안 주의!)
        this.PAYAPP_USERID = 'asg701';
        this.PAYAPP_LINKKEY = 'n3RxEKA9UFZ2yN9Y5MJIqu1DPJnCCRVaOgT+oqg6zaM=';
        this.PAYAPP_LINKVAL = 'n3RxEKA9UFZ2yN9Y5MJIqt+Djo1UzL1TZBMuvg+MK+E=';
        
        // API URL
        this.API_URL = 'https://api.payapp.kr/oapi/apiLoad.html';
        
        // 서비스별 상점명 매핑
        this.SHOP_NAMES = {
            'ddos': 'NeuralGrid DDoS 보안',
            'neuralgrid': 'NeuralGrid 메인',
            'music': 'NeuronStar Music',
            'shorts': 'Shorts Market',
            // 추가 서비스는 여기에 등록
        };
    }

    /**
     * 결제 요청 (REST API 방식)
     * @param {Object} paymentData - 결제 정보
     * @returns {Promise<Object>} 결제 요청 결과
     */
    async requestPayment(paymentData) {
        try {
            const {
                service,        // 서비스 이름 (ddos, music, shorts 등)
                goodname,       // 상품명
                price,          // 결제 금액
                recvphone,      // 구매자 전화번호
                recvemail,      // 구매자 이메일
                orderId,        // 고객사 주문번호
                userId,         // 구매자 ID
                memo,           // 메모
                feedbackUrl,    // 결제 결과 수신 URL
                returnUrl,      // 결제 완료 후 이동 URL
                paymentMethods, // 결제 수단 (card, phone, kakaopay 등)
                taxable,        // 과세 금액
                taxfree,        // 면세 금액
                vat             // 부가세
            } = paymentData;

            // 필수 파라미터 검증
            if (!service || !goodname || !price || !recvphone) {
                throw new Error('필수 파라미터가 누락되었습니다.');
            }

            // 상점명 가져오기
            const shopname = this.SHOP_NAMES[service] || 'NeuralGrid Service';

            // 결제 요청 파라미터
            const params = {
                cmd: 'payrequest',
                userid: this.PAYAPP_USERID,
                shopname: shopname,
                goodname: goodname,
                price: parseInt(price),
                recvphone: recvphone.replace(/[^0-9]/g, ''), // 숫자만
                recvemail: recvemail || '',
                memo: memo || `${shopname} 결제`,
                feedbackurl: feedbackUrl || '',
                returnurl: returnUrl || '',
                var1: orderId || '',        // 주문번호
                var2: userId || '',         // 구매자 ID
                smsuse: 'y',                // SMS 발송
                openpaytype: paymentMethods || '', // 결제 수단
                amount_taxable: taxable || 0,
                amount_taxfree: taxfree || 0,
                amount_vat: vat || 0,
                checkretry: 'y',            // feedbackurl 재시도
                buyerid: userId || ''
            };

            console.log('[PayApp] 💳 결제 요청:', {
                service,
                goodname,
                price,
                orderId
            });

            // API 호출
            const response = await fetch(this.API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: querystring.stringify(params)
            });

            const responseText = await response.text();
            const result = querystring.parse(responseText);

            console.log('[PayApp] 📥 응답:', result);

            if (result.state === '1') {
                // 성공
                return {
                    success: true,
                    mul_no: result.mul_no,      // 결제 요청 번호
                    payurl: result.payurl,       // 결제 URL
                    qrurl: result.qrurl          // QR URL
                };
            } else {
                // 실패
                throw new Error(result.errorMessage || '결제 요청 실패');
            }

        } catch (error) {
            console.error('[PayApp] ❌ 결제 요청 오류:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * 결제 결과 검증 (feedbackurl에서 호출)
     * @param {Object} feedbackData - PayApp에서 전달받은 데이터
     * @returns {Object} 검증 결과
     */
    verifyPayment(feedbackData) {
        try {
            const {
                pay_state,      // 결제 상태 (1:요청, 2:완료, 3:취소)
                mul_no,         // 결제 번호
                good_mny,       // 결제 금액
                pay_type,       // 결제 수단
                pay_istpm,      // 할부 개월
                card_name,      // 카드사명
                receipt_no,     // 승인번호
                shop_user_id,   // 판매자 아이디
                pay_date,       // 결제 일시
                state_date,     // 상태 변경 일시
                cash_no,        // 현금영수증 번호
                cash_yn,        // 현금영수증 발행 여부
                var1,           // 주문번호
                var2,           // 구매자 ID
                var3,           // 추가 변수
                // 기타 필드들...
            } = feedbackData;

            // 판매자 ID 검증
            if (shop_user_id !== this.PAYAPP_USERID) {
                throw new Error('Invalid shop user ID');
            }

            // 결제 상태별 처리
            let status = 'unknown';
            switch (pay_state) {
                case '1':
                    status = 'pending';     // 결제 요청
                    break;
                case '2':
                    status = 'completed';   // 결제 완료
                    break;
                case '3':
                    status = 'cancelled';   // 결제 취소
                    break;
                default:
                    status = 'unknown';
            }

            console.log('[PayApp] 🔔 결제 알림:', {
                mul_no,
                status,
                amount: good_mny,
                orderId: var1
            });

            return {
                success: true,
                status: status,
                paymentNumber: mul_no,
                amount: parseInt(good_mny),
                paymentType: pay_type,
                cardName: card_name,
                receiptNo: receipt_no,
                paymentDate: pay_date,
                orderId: var1,
                userId: var2,
                cashReceiptNo: cash_no,
                cashReceiptIssued: cash_yn === 'y'
            };

        } catch (error) {
            console.error('[PayApp] ❌ 결제 검증 오류:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * 결제 취소
     * @param {Object} cancelData - 취소 정보
     * @returns {Promise<Object>} 취소 결과
     */
    async cancelPayment(cancelData) {
        try {
            const {
                mul_no,         // 결제 번호
                cancel_mny,     // 취소 금액 (부분 취소 가능)
                cancel_msg      // 취소 사유
            } = cancelData;

            const params = {
                cmd: 'paycancel',
                userid: this.PAYAPP_USERID,
                linkkey: this.PAYAPP_LINKKEY,
                linkval: this.PAYAPP_LINKVAL,
                mul_no: mul_no,
                cancel_mny: cancel_mny || '',   // 전액 취소 시 빈 값
                cancel_msg: cancel_msg || '고객 요청에 의한 취소'
            };

            console.log('[PayApp] 🔄 결제 취소 요청:', { mul_no, cancel_mny });

            const response = await fetch(this.API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: querystring.stringify(params)
            });

            const responseText = await response.text();
            const result = querystring.parse(responseText);

            console.log('[PayApp] 📥 취소 응답:', result);

            if (result.state === '1') {
                return {
                    success: true,
                    message: '결제 취소가 완료되었습니다.',
                    mul_no: mul_no
                };
            } else {
                throw new Error(result.errorMessage || '취소 실패');
            }

        } catch (error) {
            console.error('[PayApp] ❌ 결제 취소 오류:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * 결제 내역 조회
     * @param {Object} queryData - 조회 조건
     * @returns {Promise<Object>} 결제 내역
     */
    async queryPayment(queryData) {
        try {
            const {
                mul_no,         // 결제 번호
                startdate,      // 시작일 (YYYYMMDD)
                enddate         // 종료일 (YYYYMMDD)
            } = queryData;

            const params = {
                cmd: 'paylist',
                userid: this.PAYAPP_USERID,
                linkkey: this.PAYAPP_LINKKEY,
                linkval: this.PAYAPP_LINKVAL,
                mul_no: mul_no || '',
                startdate: startdate || '',
                enddate: enddate || ''
            };

            console.log('[PayApp] 🔍 결제 내역 조회:', queryData);

            const response = await fetch(this.API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: querystring.stringify(params)
            });

            const responseText = await response.text();
            const result = querystring.parse(responseText);

            if (result.state === '1') {
                return {
                    success: true,
                    data: result
                };
            } else {
                throw new Error(result.errorMessage || '조회 실패');
            }

        } catch (error) {
            console.error('[PayApp] ❌ 결제 조회 오류:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * 결제 수단별 한글명 반환
     * @param {string} payType - 결제 수단 코드
     * @returns {string} 한글명
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

    /**
     * 서비스 등록
     * @param {string} serviceKey - 서비스 키
     * @param {string} shopName - 상점명
     */
    registerService(serviceKey, shopName) {
        this.SHOP_NAMES[serviceKey] = shopName;
        console.log(`[PayApp] ✅ 서비스 등록: ${serviceKey} → ${shopName}`);
    }
}

// 싱글톤 인스턴스 생성
const payAppInstance = new PayAppIntegration();

module.exports = {
    PayAppIntegration,
    payApp: payAppInstance
};
