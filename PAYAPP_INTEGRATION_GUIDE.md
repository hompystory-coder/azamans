# 🏦 PayApp 통합 결제 시스템 - 완벽 가이드

## 📋 개요

NeuralGrid 플랫폼의 **모든 서브 서비스**에서 사용하는 **통합 전자결제 시스템**입니다.  
PayApp 결제 서비스를 통해 신용카드, 휴대전화, 계좌이체, 카카오페이 등 다양한 결제 수단을 지원합니다.

### 판매자 정보 (보안 주의!)
```javascript
판매자 아이디: asg701
연동 Key:      n3RxEKA9UFZ2yN9Y5MJIqu1DPJnCCRVaOgT+oqg6zaM=
연동 Value:    n3RxEKA9UFZ2yN9Y5MJIqt+Djo1UzL1TZBMuvg+MK+E=
```

⚠️ **보안 주의사항**:
- 이 정보는 **절대 외부로 유출되지 않도록** 관리
- Git에 커밋 시 환경변수 또는 별도 설정 파일 사용
- 프론트엔드 코드에 직접 노출 금지

## 📁 파일 구조

```
/home/azamans/webapp/
├── payapp-integration.js       # 백엔드 결제 모듈 (Node.js)
├── payapp-client.js            # 프론트엔드 결제 모듈 (Browser)
├── PAYAPP_INTEGRATION_GUIDE.md # 이 문서
└── [서비스별 연동 예제]
```

## 🔧 시스템 아키텍처

### 전체 플로우:
```
[사용자]
   ↓ 결제 버튼 클릭
[프론트엔드] (payapp-client.js)
   ↓ PayApp.payrequest()
[PayApp 결제창] (lite.payapp.kr)
   ↓ 사용자 결제 진행
[PayApp 서버] (api.payapp.kr)
   ↓ feedbackurl POST
[백엔드 API] (payapp-integration.js)
   ↓ 결제 검증 & DB 저장
[사용자 리다이렉트] → 마이페이지/결제완료
```

### 주요 컴포넌트:

#### 1. 백엔드 모듈 (`payapp-integration.js`)
- **역할**: 서버 사이드 결제 처리
- **기능**:
  - 결제 요청 생성
  - 결제 결과 검증 (feedbackurl)
  - 결제 취소
  - 결제 내역 조회
- **사용 환경**: Node.js 서버

#### 2. 프론트엔드 모듈 (`payapp-client.js`)
- **역할**: 클라이언트 결제창 호출
- **기능**:
  - PayApp JS API 래퍼
  - 서비스별 결제 헬퍼 함수
  - 사용자 친화적 인터페이스
- **사용 환경**: 웹 브라우저

## 🚀 빠른 시작 (Quick Start)

### Step 1: 백엔드 설치
```bash
cd /home/azamans/webapp
npm install node-fetch  # fetch API 사용
```

### Step 2: 백엔드 연동
```javascript
// ddos-server-updated.js 또는 다른 서버 파일
const { payApp } = require('./payapp-integration');

// 결제 요청 엔드포인트
app.post('/api/payment/request', authenticateToken, async (req, res) => {
    const {
        service,     // 'ddos', 'music', 'shorts' 등
        goodname,    // 상품명
        price,       // 금액
        orderId      // 주문번호
    } = req.body;

    const userId = req.user.userId;
    const userEmail = req.user.email;
    const userPhone = req.user.phone;

    // 결제 요청
    const result = await payApp.requestPayment({
        service: service,
        goodname: goodname,
        price: price,
        recvphone: userPhone,
        recvemail: userEmail,
        orderId: orderId,
        userId: userId,
        feedbackUrl: 'https://ddos.neuralgrid.kr/api/payment/feedback',
        returnUrl: 'https://ddos.neuralgrid.kr/mypage.html',
        paymentMethods: 'card,phone,kakaopay,naverpay'
    });

    if (result.success) {
        res.json({
            success: true,
            payurl: result.payurl,   // 결제 URL로 리다이렉트
            mul_no: result.mul_no    // 결제 번호
        });
    } else {
        res.json({
            success: false,
            error: result.error
        });
    }
});

// 결제 결과 수신 (feedbackurl)
app.post('/api/payment/feedback', async (req, res) => {
    const feedbackData = req.body;

    // 결제 검증
    const verification = payApp.verifyPayment(feedbackData);

    if (verification.success) {
        const {
            status,         // 'pending', 'completed', 'cancelled'
            paymentNumber,  // 결제 번호
            amount,         // 금액
            orderId,        // 주문번호
            userId          // 사용자 ID
        } = verification;

        // 결제 완료 처리
        if (status === 'completed') {
            // TODO: 주문 상태 업데이트
            // TODO: 서비스 활성화
            // TODO: 이메일 발송
            console.log('✅ 결제 완료:', orderId);
        }

        // PayApp에 SUCCESS 응답 (필수!)
        res.send('SUCCESS');
    } else {
        res.status(400).send('FAIL');
    }
});
```

### Step 3: 프론트엔드 연동
```html
<!-- register.html 또는 결제 페이지 -->
<!DOCTYPE html>
<html>
<head>
    <!-- PayApp JS 라이브러리 로드 (필수) -->
    <script src="https://lite.payapp.kr/public/api/v2/payapp-lite.js"></script>
    
    <!-- NeuralGrid 결제 모듈 로드 -->
    <script src="/payapp-client.js"></script>
</head>
<body>
    <button onclick="startPayment()">결제하기</button>

    <script>
        function startPayment() {
            // DDoS 서비스 결제 예시
            NeuralGridPayment.payForDDoS({
                orderId: 'ORD-2025-123456',
                productType: 'website',  // 'website' or 'server'
                quantity: 1,
                price: 330000,
                userInfo: {
                    userId: 'user123',
                    phone: '01012345678',
                    email: 'user@example.com'
                }
            });
        }

        // 또는 직접 호출
        function customPayment() {
            NeuralGridPayment.startPayment({
                service: 'ddos',
                goodname: 'DDoS 보안 서비스',
                price: 330000,
                orderId: 'ORD-123',
                userId: 'user123',
                recvphone: '01012345678',
                recvemail: 'user@example.com',
                memo: '홈페이지 보호 서비스',
                onSuccess: (data) => {
                    console.log('결제 시작:', data);
                },
                onError: (error) => {
                    alert('결제 오류: ' + error.message);
                }
            });
        }
    </script>
</body>
</html>
```

## 📊 서비스별 연동 가이드

### 1. DDoS 보안 서비스 (ddos.neuralgrid.kr)

#### 홈페이지 보호 (₩330,000/년)
```javascript
// 프론트엔드
NeuralGridPayment.payForDDoS({
    orderId: order.orderId,
    productType: 'website',
    quantity: 1,
    price: 330000,
    userInfo: {
        userId: user.id,
        phone: user.phone,
        email: user.email
    }
});
```

#### 서버 보호 (₩2,990,000~/년)
```javascript
// 프론트엔드
NeuralGridPayment.payForDDoS({
    orderId: order.orderId,
    productType: 'server',
    quantity: 5,  // 5, 10, 15, 20 or custom
    price: 2990000,
    userInfo: {
        userId: user.id,
        phone: user.phone,
        email: user.email
    }
});
```

### 2. NeuronStar Music (미래 서비스)

```javascript
// 서비스 등록 (최초 1회)
payApp.registerService('music', 'NeuronStar Music');

// 결제 호출
NeuralGridPayment.startPayment({
    service: 'music',
    goodname: '프리미엄 구독 (1개월)',
    price: 9900,
    orderId: 'MUS-' + Date.now(),
    userId: user.id,
    recvphone: user.phone,
    recvemail: user.email
});
```

### 3. Shorts Market (미래 서비스)

```javascript
// 서비스 등록
payApp.registerService('shorts', 'Shorts Market');

// 결제 호출
NeuralGridPayment.startPayment({
    service: 'shorts',
    goodname: '숏폼 제작 크레딧 (100개)',
    price: 50000,
    orderId: 'SHT-' + Date.now(),
    userId: user.id
});
```

### 4. 새로운 서비스 추가 방법

#### 백엔드 (`payapp-integration.js`):
```javascript
// 서비스 등록
payApp.registerService('new-service', '새로운 서비스명');
```

#### 프론트엔드 (`payapp-client.js`):
```javascript
// 서비스 등록
NeuralGridPayment.registerService('new-service', '새로운 서비스명');

// 결제 호출
NeuralGridPayment.startPayment({
    service: 'new-service',
    goodname: '상품명',
    price: 10000,
    orderId: 'NEW-123',
    userId: user.id
});
```

## 🔐 보안 고려사항

### 1. 인증 정보 보호
```javascript
// ❌ 잘못된 방법 (환경변수 미사용)
const PAYAPP_USERID = 'asg701';

// ✅ 올바른 방법 (환경변수 사용)
const PAYAPP_USERID = process.env.PAYAPP_USERID || 'asg701';
const PAYAPP_LINKKEY = process.env.PAYAPP_LINKKEY;
const PAYAPP_LINKVAL = process.env.PAYAPP_LINKVAL;
```

### 2. feedbackurl 검증
```javascript
// feedbackurl에서 반드시 검증
app.post('/api/payment/feedback', async (req, res) => {
    const { shop_user_id, mul_no, good_mny } = req.body;

    // 판매자 ID 확인
    if (shop_user_id !== PAYAPP_USERID) {
        return res.status(403).send('FAIL');
    }

    // 주문 금액 확인
    const order = await getOrder(req.body.var1);
    if (parseInt(good_mny) !== order.amount) {
        return res.status(400).send('FAIL');
    }

    // 중복 결제 확인
    const existingPayment = await getPaymentByMulNo(mul_no);
    if (existingPayment) {
        return res.send('SUCCESS'); // 이미 처리됨
    }

    // 처리 후 반드시 SUCCESS 응답
    res.send('SUCCESS');
});
```

### 3. HTTPS 필수
- PayApp API는 **HTTPS 전용**
- feedbackurl, returnurl 모두 HTTPS 필수

## 💳 결제 수단

### 지원 결제 수단:
- `card`: 신용카드
- `phone`: 휴대전화 결제
- `rbank`: 계좌이체
- `vbank`: 가상계좌
- `kakaopay`: 카카오페이
- `naverpay`: 네이버페이
- `smilepay`: 스마일페이
- `applepay`: 애플페이
- `payco`: 페이코
- `wechat`: 위챗페이
- `myaccount`: 내통장결제
- `tosspay`: 토스페이

### 결제 수단 제한:
```javascript
// 신용카드, 카카오페이만 허용
paymentMethods: 'card,kakaopay'

// 모든 수단 허용
paymentMethods: ''  // 빈 문자열
```

## 📝 결제 상태 관리

### 결제 상태 (pay_state):
- `1`: 결제 요청 (pending)
- `2`: 결제 완료 (completed)
- `3`: 결제 취소 (cancelled)

### DB 스키마 예시:
```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mul_no VARCHAR(50) UNIQUE NOT NULL,     -- PayApp 결제 번호
    order_id VARCHAR(100) NOT NULL,          -- 주문 번호
    user_id VARCHAR(100) NOT NULL,           -- 사용자 ID
    service VARCHAR(50) NOT NULL,            -- 서비스 (ddos, music 등)
    product_name VARCHAR(200) NOT NULL,      -- 상품명
    amount INT NOT NULL,                     -- 금액
    pay_type VARCHAR(50),                    -- 결제 수단
    pay_state INT DEFAULT 1,                 -- 결제 상태
    receipt_no VARCHAR(100),                 -- 승인번호
    payment_date DATETIME,                   -- 결제 일시
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_user_id (user_id),
    INDEX idx_mul_no (mul_no)
);
```

## 🧪 테스트

### 테스트 계정:
PayApp 판매자 사이트에서 테스트 모드 활성화 후 테스트 가능

### 테스트 절차:
1. 판매자 사이트 로그인 (https://payapp.kr)
2. 설정 → 테스트 모드 활성화
3. 테스트 결제 진행
4. feedbackurl 응답 확인
5. DB 저장 확인

### 로그 확인:
```bash
# 백엔드 로그
pm2 logs ddos-security | grep -i payapp

# 프론트엔드 로그
브라우저 DevTools → Console → [Payment] 로그 확인
```

## 🔧 트러블슈팅

### 문제 1: 결제창이 안 뜸
**원인**: PayApp JS 라이브러리 미로드  
**해결**:
```html
<script src="https://lite.payapp.kr/public/api/v2/payapp-lite.js"></script>
```

### 문제 2: feedbackurl 호출 안됨
**원인**: HTTPS 아님 또는 외부 접근 불가  
**해결**:
- feedbackurl은 반드시 HTTPS
- 외부에서 접근 가능한 공개 URL

### 문제 3: SUCCESS 응답 후에도 재시도
**원인**: 응답이 정확히 'SUCCESS'가 아님  
**해결**:
```javascript
res.send('SUCCESS');  // ✅
res.json({ success: true });  // ❌ (JSON은 안됨!)
```

### 문제 4: 오류 코드
| 코드 | 설명 | 해결 |
|------|------|------|
| 70001 | HTTPS 아님 | HTTPS로 변경 |
| 70010 | userid/linkkey 오류 | 인증 정보 확인 |
| 70020 | 파라미터 오류 | 파라미터 값 확인 |
| 70040 | cmd 값 오류 | cmd 값 확인 |
| 80010/80020 | 취소 불가 상태 | 결제 상태 확인 |

## 📚 참고 자료

- **PayApp 개발자 센터**: https://payapp.kr/dev_center/dev_center01.html
- **판매자 관리 사이트**: https://payapp.kr (로그인 필요)
- **API 문서**: REST API 매뉴얼 참고

## 🎯 체크리스트

### 배포 전:
- [ ] 환경변수 설정 (PAYAPP_USERID, LINKKEY, LINKVAL)
- [ ] feedbackurl HTTPS 확인
- [ ] 외부 접근 가능 확인
- [ ] DB 스키마 생성
- [ ] 테스트 결제 완료

### 새 서비스 추가 시:
- [ ] 백엔드에 서비스 등록
- [ ] 프론트엔드에 서비스 등록
- [ ] 상품명/가격 설정
- [ ] feedbackurl 라우팅 추가
- [ ] 테스트 완료

---

**작성일**: 2025-12-16  
**작성자**: GenSpark AI Developer  
**버전**: 1.0  
**상태**: ✅ Production Ready

**⚠️ 중요**: 이 문서와 인증 정보는 **보안 관리** 필수!
