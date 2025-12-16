# Phase 2.5: 쿠키 기반 SSO 구현 계획

## 🎯 목표
localStorage의 Same-Origin Policy 문제를 해결하고, 모든 neuralgrid.kr 서브도메인에서 자동 인증되는 완전한 SSO 시스템 구축

---

## 📋 구현 계획

### 1단계: Auth 로그인 페이지 수정 (30분)
**파일**: `auth-login-updated.html`

**변경 사항**:
- 로그인 성공 시 localStorage + Cookie 동시 저장
- Cookie 설정: `domain=.neuralgrid.kr; path=/; SameSite=Lax; Secure`

**코드**:
```javascript
// 로그인 성공 시
function setAuthToken(token, user) {
    // 기존: localStorage만 사용
    localStorage.setItem('neuralgrid_token', token);
    localStorage.setItem('user', JSON.stringify(user));
    
    // 추가: Cookie 설정 (모든 서브도메인에서 사용 가능)
    document.cookie = `neuralgrid_token=${token}; domain=.neuralgrid.kr; path=/; max-age=86400; SameSite=Lax; Secure`;
    document.cookie = `neuralgrid_user=${encodeURIComponent(JSON.stringify(user))}; domain=.neuralgrid.kr; path=/; max-age=86400; SameSite=Lax; Secure`;
}
```

---

### 2단계: Auth 대시보드 수정 (20분)
**파일**: `auth-dashboard-updated.html`

**변경 사항**:
- 인증 체크 시 Cookie 우선 확인
- localStorage fallback 유지

**코드**:
```javascript
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

function checkAuth() {
    // 1. Cookie 먼저 확인
    let token = getCookie('neuralgrid_token');
    let userStr = getCookie('neuralgrid_user');
    
    // 2. Cookie 없으면 localStorage 확인 (하위 호환성)
    if (!token) {
        token = localStorage.getItem('neuralgrid_token');
        userStr = localStorage.getItem('user');
    }
    
    if (!token) {
        window.location.href = 'https://auth.neuralgrid.kr/';
        return;
    }
    
    // 사용자 정보 표시
    if (userStr) {
        try {
            const user = JSON.parse(decodeURIComponent(userStr));
            displayUserInfo(user);
        } catch (e) {
            console.error('User parse error:', e);
        }
    }
}
```

---

### 3단계: DDoS MyPage 수정 (30분)
**파일**: `ddos-mypage.html`

**변경 사항**:
- Cookie 기반 인증 체크
- localStorage fallback

**코드**:
```javascript
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

function checkAuth() {
    // Cookie 우선 확인
    let token = getCookie('neuralgrid_token');
    
    // localStorage fallback
    if (!token) {
        token = localStorage.getItem('neuralgrid_token');
    }
    
    if (!token) {
        // 토큰 없음 - Auth 로그인 페이지로
        window.location.href = 'https://auth.neuralgrid.kr/';
        return false;
    }
    
    return token;
}

// API 호출 시 토큰 사용
async function fetchUserStats() {
    const token = checkAuth();
    if (!token) return;
    
    const response = await fetch('/api/user/stats', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    // ...
}
```

---

### 4단계: DDoS 서버 등록 페이지 수정 (20분)
**파일**: `ddos-register.html`

**변경 사항**:
- Cookie 기반 인증 체크 추가

---

### 5단계: 로그아웃 기능 추가 (10분)

**모든 페이지 공통**:
```javascript
function logout() {
    // localStorage 삭제
    localStorage.removeItem('neuralgrid_token');
    localStorage.removeItem('user');
    
    // Cookie 삭제
    document.cookie = 'neuralgrid_token=; domain=.neuralgrid.kr; path=/; max-age=0';
    document.cookie = 'neuralgrid_user=; domain=.neuralgrid.kr; path=/; max-age=0';
    
    // 로그인 페이지로
    window.location.href = 'https://auth.neuralgrid.kr/';
}
```

---

## 🔧 기술 상세

### Cookie 설정 파라미터

```javascript
document.cookie = `neuralgrid_token=${token}; domain=.neuralgrid.kr; path=/; max-age=86400; SameSite=Lax; Secure`;
```

| 파라미터 | 값 | 설명 |
|---------|-----|------|
| `domain` | `.neuralgrid.kr` | 모든 서브도메인에서 접근 가능 |
| `path` | `/` | 모든 경로에서 접근 가능 |
| `max-age` | `86400` | 24시간 유효 (초 단위) |
| `SameSite` | `Lax` | CSRF 방어, 일반적인 네비게이션 허용 |
| `Secure` | (플래그) | HTTPS에서만 전송 |

### 보안 고려사항

1. **HttpOnly 플래그 미사용**
   - 이유: JavaScript에서 토큰을 읽어야 함 (API 호출)
   - 대안: XSS 방어를 위한 입력 검증 강화

2. **Secure 플래그 사용**
   - HTTPS에서만 쿠키 전송
   - 중간자 공격 방지

3. **SameSite=Lax**
   - CSRF 공격 방어
   - 일반적인 링크 클릭은 허용

4. **토큰 유효기간**
   - 24시간 (86400초)
   - 필요시 Refresh Token 구현

---

## 🧪 테스트 시나리오

### 시나리오 1: 정상 로그인 플로우
```
1. auth.neuralgrid.kr 접속
2. 로그인 (test@example.com / password)
3. 브라우저 콘솔에서 확인:
   document.cookie
   // 결과: "neuralgrid_token=xxx; neuralgrid_user=xxx"
4. 대시보드 자동 이동
5. DDoS 카드 클릭
6. ddos.neuralgrid.kr/mypage.html 정상 표시 ✅
```

### 시나리오 2: 직접 URL 접근
```
1. auth.neuralgrid.kr 로그인
2. 새 탭에서 직접 입력:
   https://ddos.neuralgrid.kr/mypage.html
3. 자동 인증되어 MyPage 표시 ✅
```

### 시나리오 3: 로그아웃
```
1. 로그아웃 버튼 클릭
2. 쿠키 삭제 확인
3. auth.neuralgrid.kr 로그인 페이지로 이동 ✅
```

### 시나리오 4: 토큰 만료
```
1. 24시간 경과 (또는 수동으로 쿠키 삭제)
2. MyPage 새로고침
3. 자동으로 로그인 페이지로 리다이렉트 ✅
```

---

## 📦 수정 파일 목록

1. ✅ `auth-login-updated.html` - 로그인 시 쿠키 설정
2. ✅ `auth-dashboard-updated.html` - 쿠키 인증 체크
3. ✅ `ddos-mypage.html` - 쿠키 인증 체크
4. ✅ `ddos-register.html` - 쿠키 인증 체크

---

## 🚀 배포 순서

1. **파일 수정** (로컬)
2. **Git 커밋 & 푸시**
3. **서버 배포**:
   ```bash
   cd /home/azamans/webapp
   git pull origin genspark_ai_developer_clean
   sudo cp auth-login-updated.html /var/www/auth.neuralgrid.kr/index.html
   sudo cp auth-dashboard-updated.html /var/www/auth.neuralgrid.kr/dashboard.html
   sudo cp ddos-mypage.html /var/www/ddos.neuralgrid.kr/mypage.html
   sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html
   sudo chown -R azamans:azamans /var/www/auth.neuralgrid.kr/
   sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
   ```
4. **Nginx 리로드**:
   ```bash
   sudo systemctl reload nginx
   ```
5. **테스트**

---

## ⏱️ 예상 소요 시간

| 작업 | 시간 |
|------|------|
| Auth 로그인 페이지 수정 | 30분 |
| Auth 대시보드 수정 | 20분 |
| DDoS MyPage 수정 | 30분 |
| DDoS 등록 페이지 수정 | 20분 |
| 로그아웃 기능 추가 | 10분 |
| 테스트 & 디버깅 | 30분 |
| 배포 및 검증 | 20분 |
| **총합** | **~2.5시간** |

---

## ✅ 완료 체크리스트

- [ ] Auth 로그인 페이지 - 쿠키 설정 추가
- [ ] Auth 대시보드 - 쿠키 인증 체크
- [ ] DDoS MyPage - 쿠키 인증 체크
- [ ] DDoS 등록 페이지 - 쿠키 인증 체크
- [ ] 로그아웃 기능 구현
- [ ] Git 커밋 & 푸시
- [ ] 서버 배포
- [ ] 브라우저 테스트 (로그인 플로우)
- [ ] 크로스 도메인 테스트
- [ ] 로그아웃 테스트
- [ ] 문서화

---

**시작 시각**: 2025-12-16 01:40 KST  
**예상 완료**: 2025-12-16 04:10 KST  
**상태**: 🚀 시작 준비 완료
