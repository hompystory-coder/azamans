# 🧪 NeuralGrid Security Platform - Browser Test Report

## 📋 테스트 시나리오

### 사용자 요구사항 (고객 플로우):
```
1. 메인사이트에서 통합회원가입 (로그인 상태)
   ↓
2. https://ddos.neuralgrid.kr/ 접속
   ↓
3. "신청하기" 버튼 클릭
   ↓
4. 고객명/전화번호/이메일/도메인/서버IP 입력
   ↓
5. 신청 완료
   ↓
6. ❌ 문제: 로그인 페이지로 다시 리다이렉트됨!
   ✅ 기대: 마이페이지로 이동 + 스크립트/사용방법/DDoS 대시보드 표시
```

## 🔍 발견된 문제점

### 1. 인증 상태 유지 문제
**증상**: 신청 완료 후 로그인 페이지로 리다이렉트  
**원인 분석**:

#### A) 쿠키 도메인 설정
```javascript
// auth.neuralgrid.kr/index.html (Line 478-479)
document.cookie = `neuralgrid_token=${data.token}; domain=.neuralgrid.kr; path=/; max-age=86400; SameSite=Lax; Secure`;
```
✅ **도메인**: `.neuralgrid.kr` - 서브도메인 간 공유 가능  
✅ **경로**: `/` - 모든 경로에서 접근 가능  
✅ **Secure**: HTTPS 필수  
✅ **SameSite**: `Lax` - 일반적인 크로스 도메인 요청 허용

#### B) 토큰 읽기 로직
```javascript
// ddos-register.html (Line 1076-1080)
function getAuthToken() {
    return localStorage.getItem('neuralgrid_token') || 
           sessionStorage.getItem('neuralgrid_token') ||
           getCookie('neuralgrid_token');
}
```
✅ **우선순위**:
1. localStorage
2. sessionStorage
3. Cookie

#### C) 401 응답 처리
```javascript
// ddos-register.html (Line 1180-1186)
if (response.status === 401) {
    showAlert('websiteAlert', 'error', '인증이 만료되었습니다. 다시 로그인해주세요.');
    setTimeout(() => {
        window.location.href = 'https://auth.neuralgrid.kr/';
    }, 2000);
    return;
}
```

**가능한 원인**:
1. ❌ 백엔드 API가 401 Unauthorized 응답
2. ❌ 토큰이 localStorage/Cookie에 제대로 저장 안됨
3. ❌ 토큰 검증 실패 (만료/잘못된 토큰)
4. ❌ CORS 설정 문제로 쿠키 전달 안됨

### 2. 백엔드 API 필드명 불일치
**증상**: 서버가 등록되어도 마이페이지에 표시 안됨  
**원인**: 백엔드 응답 필드명 ≠ 프론트엔드 기대 필드명  
**상태**: ✅ 이미 수정됨 (미배포)

### 3. 프로덕션 배포 미완료
**증상**: 수정된 코드가 실제 서버에 반영 안됨  
**원인**: `ddos-server-updated.js` → `/var/www/ddos.neuralgrid.kr/server.js` 배포 안됨  
**상태**: 🔄 배포 스크립트 준비 완료

## 🧪 테스트 계획

### Phase 1: 인증 흐름 검증
```bash
# Test 1: 로그인 후 쿠키 확인
1. https://auth.neuralgrid.kr/ 로그인
2. 브라우저 DevTools → Application → Cookies
3. 확인 항목:
   - ✅ neuralgrid_token 존재
   - ✅ Domain: .neuralgrid.kr
   - ✅ Path: /
   - ✅ Secure: ✓
   - ✅ SameSite: Lax

# Test 2: localStorage 확인
1. 브라우저 DevTools → Application → Local Storage
2. 확인 항목:
   - ✅ neuralgrid_token 존재
   - ✅ user 정보 존재

# Test 3: ddos.neuralgrid.kr에서 토큰 접근 가능 확인
1. https://ddos.neuralgrid.kr/ 접속
2. Console에서 실행:
   console.log('localStorage token:', localStorage.getItem('neuralgrid_token'));
   console.log('cookie token:', document.cookie);
```

### Phase 2: API 호출 검증
```bash
# Test 4: 수동 API 테스트
1. 브라우저 DevTools → Console
2. 실행:
   const token = localStorage.getItem('neuralgrid_token');
   fetch('https://ddos.neuralgrid.kr/api/user/stats', {
       headers: { 'Authorization': `Bearer ${token}` }
   })
   .then(r => r.json())
   .then(d => console.log('Stats:', d))
   .catch(e => console.error('Error:', e));

# 기대 응답:
# - 200 OK + 통계 데이터
# 또는
# - 401 Unauthorized (토큰 문제)
```

### Phase 3: 등록 플로우 테스트
```bash
# Test 5: 홈페이지 보호 등록
1. https://ddos.neuralgrid.kr/register.html
2. "홈페이지 보호" 선택
3. 정보 입력 및 신청
4. Network 탭에서 확인:
   - POST /api/servers/register-website
   - 응답 코드: 200 or 401?
   - 응답 데이터: installCode 포함?
```

## 🔧 디버깅 체크리스트

### 백엔드 서버 상태
- [ ] PM2 서비스 실행 중: `pm2 status ddos-security`
- [ ] 포트 3105 리스닝: `netstat -tlnp | grep 3105`
- [ ] 최근 에러 로그: `pm2 logs ddos-security --err --lines 50`

### 인증 미들웨어
- [ ] `/api/servers/register-website` 엔드포인트에 `authMiddleware` 적용됨
- [ ] Bearer 토큰 검증 로직 정상 작동
- [ ] 토큰 만료 시간 확인 (24시간)

### CORS 설정
```javascript
// ddos-server-updated.js에서 확인
app.use(cors({
    origin: [
        'https://neuralgrid.kr',
        'https://auth.neuralgrid.kr',
        'https://ddos.neuralgrid.kr'
    ],
    credentials: true  // ← 쿠키 전달 허용
}));
```

## 📊 예상 결과

### 시나리오 A: 토큰이 유효한 경우
```
✅ 로그인 → ddos.neuralgrid.kr → 신청 → 설치 가이드 모달 → 마이페이지
```

### 시나리오 B: 토큰이 없거나 만료된 경우
```
❌ 로그인 → ddos.neuralgrid.kr → 신청 → 401 Error → auth.neuralgrid.kr로 리다이렉트
```

### 시나리오 C: 백엔드 에러
```
❌ 로그인 → ddos.neuralgrid.kr → 신청 → 500 Error → 에러 메시지 표시
```

## 🎯 해결 방안

### 단기 해결책 (즉시 적용 가능):

#### 1. 백엔드 배포
```bash
# 배포 스크립트 실행
cd /home/azamans/webapp
./deploy-ddos-backend.sh
```

#### 2. 토큰 검증 로그 추가
백엔드 `authMiddleware`에 디버그 로그 추가:
```javascript
function authenticateToken(req, res, next) {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    
    console.log('[Auth] Token received:', token ? 'YES' : 'NO');
    
    if (!token) {
        console.log('[Auth] 401: No token provided');
        return res.status(401).json({ error: 'Access token required' });
    }
    
    jwt.verify(token, JWT_SECRET, (err, user) => {
        if (err) {
            console.log('[Auth] 401: Token verification failed:', err.message);
            return res.status(401).json({ error: 'Invalid token' });
        }
        
        console.log('[Auth] ✅ Token valid for user:', user.userId);
        req.user = user;
        next();
    });
}
```

#### 3. 프론트엔드 에러 핸들링 개선
```javascript
// ddos-register.html에서 더 자세한 에러 로깅
try {
    const response = await fetch('/api/servers/register-website', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(data)
    });
    
    console.log('Response status:', response.status);
    console.log('Response headers:', [...response.headers.entries()]);
    
    if (response.status === 401) {
        console.error('Auth failed - redirecting to login');
        // ... 로그인 페이지로 이동
    }
    
    const result = await response.json();
    console.log('Response data:', result);
    
} catch (error) {
    console.error('Request failed:', error);
}
```

### 중기 해결책 (테스트 후 적용):

#### 4. 토큰 갱신 메커니즘
- Refresh token 도입
- 401 응답 시 자동 토큰 갱신 시도
- 갱신 실패 시에만 로그인 페이지로 이동

#### 5. 세션 스토리지 우선순위 변경
```javascript
function getAuthToken() {
    // 쿠키를 최우선으로 (서브도메인 간 공유)
    return getCookie('neuralgrid_token') ||
           localStorage.getItem('neuralgrid_token') || 
           sessionStorage.getItem('neuralgrid_token');
}
```

## 📝 실제 브라우저 테스트 결과

### Test 1: 로그인 상태 확인
```
URL: https://auth.neuralgrid.kr/
Status: [테스트 필요]
Cookies: [확인 필요]
LocalStorage: [확인 필요]
```

### Test 2: DDoS 사이트 접근
```
URL: https://ddos.neuralgrid.kr/register.html
Token Available: [확인 필요]
Console Errors: [확인 필요]
```

### Test 3: API 호출
```
Endpoint: POST /api/servers/register-website
Status Code: [확인 필요]
Response: [확인 필요]
```

## 🚀 다음 단계

1. ✅ 배포 스크립트 작성 완료
2. 🔄 백엔드 배포 실행 (sudo 권한 필요)
3. 🔄 브라우저에서 실제 테스트
4. 🔄 로그 분석 및 문제 진단
5. 🔄 추가 수정 및 재배포

---

**작성일**: 2025-12-16  
**작성자**: GenSpark AI Developer  
**상태**: 📝 Draft - 실제 테스트 대기 중
