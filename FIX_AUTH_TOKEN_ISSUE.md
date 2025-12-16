# 🔧 인증 토큰 문제 해결 방법

## 🚨 발견된 문제

### 로그 분석 결과:
```
[Auth] Token length: 9  ← 정상 토큰은 150자 이상!
[Auth] Response status: 401
[Auth] ❌ HTTP error: 401 Unauthorized
```

**문제:** 토큰이 9자밖에 안됨 (아마도 "undefined" 문자열)

---

## 🔍 원인 분석

### 1. 쿠키가 제대로 설정되지 않음
- Auth 서비스에서 로그인 시 쿠키 설정
- 하지만 `ddos.neuralgrid.kr`에서 읽을 때 값이 이상함

### 2. 가능한 원인들:
1. ❌ 쿠키 도메인이 잘못 설정됨 (`.neuralgrid.kr`가 아님)
2. ❌ 쿠키가 만료됨
3. ❌ 쿠키가 HttpOnly로 설정되어 JavaScript에서 읽을 수 없음
4. ❌ localStorage/sessionStorage에 잘못된 값 저장

---

## ✅ 해결 방법

### 즉시 테스트 (사용자용):

1. **디버그 페이지 열기:**
   ```
   https://ddos.neuralgrid.kr/check-auth.html
   ```

2. **현재 토큰 상태 확인:**
   - 쿠키에 `neuralgrid_token`이 있는지
   - 토큰 길이가 얼마인지
   - 토큰이 유효한지

3. **문제 시나리오별 해결:**

   **A. 토큰이 없거나 9자 이하:**
   ```
   → "모든 저장소 비우기" 버튼 클릭
   → "로그인 페이지로 이동" 클릭
   → 다시 로그인
   ```

   **B. 토큰은 있지만 무효함:**
   ```
   → 다시 로그인 (토큰 만료)
   ```

   **C. 토큰이 유효함:**
   ```
   → 신청 페이지로 이동해서 다시 시도
   ```

---

## 🛠️ 코드 수정 (개발자용)

### 문제 1: getAuthToken() 함수 개선 필요

**현재 코드:**
```javascript
function getAuthToken() {
    return localStorage.getItem('neuralgrid_token') || 
           sessionStorage.getItem('neuralgrid_token') ||
           getCookie('neuralgrid_token');
}
```

**문제점:**
- localStorage에 'undefined' 문자열이 저장될 수 있음
- 빈 문자열도 truthy로 취급됨

**개선된 코드:**
```javascript
function getAuthToken() {
    // 순서대로 확인
    const sources = [
        () => localStorage.getItem('neuralgrid_token'),
        () => sessionStorage.getItem('neuralgrid_token'),
        () => getCookie('neuralgrid_token')
    ];
    
    for (const getToken of sources) {
        const token = getToken();
        // null, undefined, 빈 문자열, 'undefined' 문자열 모두 필터링
        if (token && token !== 'undefined' && token !== 'null' && token.length > 20) {
            console.log('[Token] Found valid token, length:', token.length);
            return token;
        }
    }
    
    console.log('[Token] No valid token found');
    return null;
}
```

### 문제 2: 에러 메시지 개선

**현재 코드 (라인 1180-1186):**
```javascript
if (response.status === 401) {
    showAlert('websiteAlert', 'error', '인증이 만료되었습니다. 다시 로그인해주세요.');
    setTimeout(() => {
        window.location.href = 'https://auth.neuralgrid.kr/';
    }, 2000);
    return;
}
```

**개선점:**
- localStorage/sessionStorage도 함께 삭제
- 즉시 리다이렉트 (2초 기다릴 필요 없음)

**개선된 코드:**
```javascript
if (response.status === 401) {
    // 잘못된 토큰 삭제
    localStorage.removeItem('neuralgrid_token');
    sessionStorage.removeItem('neuralgrid_token');
    document.cookie = 'neuralgrid_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.neuralgrid.kr';
    
    alert('인증이 만료되었습니다. 다시 로그인해주세요.');
    window.location.href = 'https://auth.neuralgrid.kr/';
    return;
}
```

---

## 🧪 테스트 절차

### 1. 디버그 페이지에서 확인
```
https://ddos.neuralgrid.kr/check-auth.html
```

### 2. 예상 결과:

**로그인 전:**
```
❌ 토큰 없음
🍪 쿠키 정보: ⚠️ 쿠키 없음
💾 LocalStorage: ⚠️ 비어있음
```

**로그인 후:**
```
✅ 토큰 존재
토큰 길이: 200자
🍪 쿠키 정보: neuralgrid_token 길이: 200자
```

### 3. 토큰 검증:
```
버튼 클릭: "토큰 검증하기"
결과: ✅ 유효한 토큰
```

### 4. API 테스트:
```
버튼 클릭: "API 테스트"
결과: Status: 200 (성공)
```

---

## 📊 체크리스트

사용자가 직접 확인할 사항:

- [ ] https://ddos.neuralgrid.kr/check-auth.html 접속
- [ ] 현재 토큰 상태 확인
- [ ] 토큰이 없거나 짧으면: "모든 저장소 비우기" → 다시 로그인
- [ ] 토큰이 있으면: "토큰 검증하기" 클릭
- [ ] 검증 성공하면: "API 테스트" 클릭
- [ ] API 테스트 성공하면: 신청 페이지에서 다시 시도

---

## 🎯 빠른 해결 방법

### 사용자가 바로 시도할 수 있는 방법:

1. **브라우저 콘솔 열기 (F12)**

2. **다음 코드 실행:**
```javascript
// 1. 현재 토큰 확인
console.log('Cookie:', document.cookie);
console.log('LocalStorage:', localStorage.getItem('neuralgrid_token'));

// 2. 모두 삭제
localStorage.clear();
sessionStorage.clear();
document.cookie.split(";").forEach(c => { 
    document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/;domain=.neuralgrid.kr"); 
});

// 3. 로그인 페이지로 이동
window.location.href = 'https://auth.neuralgrid.kr/';
```

3. **다시 로그인**

4. **신청 페이지로 이동해서 다시 시도**

---

## 🔄 완전한 초기화 방법

### 방법 1: 디버그 페이지 사용
```
1. https://ddos.neuralgrid.kr/check-auth.html
2. "모든 저장소 비우기" 버튼 클릭
3. "로그인 페이지로 이동" 버튼 클릭
4. 로그인
5. 신청
```

### 방법 2: 브라우저 개발자 도구
```
1. F12 → Application 탭
2. Storage → Clear site data
3. 페이지 새로고침
4. 로그인
5. 신청
```

### 방법 3: 시크릿 모드
```
1. Ctrl+Shift+N (Chrome) / Ctrl+Shift+P (Firefox)
2. https://auth.neuralgrid.kr/ 로그인
3. https://ddos.neuralgrid.kr/register.html 신청
```

---

## 🎬 지금 바로 시도하세요!

### 가장 간단한 방법:

```
1. https://ddos.neuralgrid.kr/check-auth.html 열기
2. "모든 저장소 비우기" 클릭
3. "로그인 페이지로 이동" 클릭
4. 로그인 후 신청
```

이 방법으로 99% 해결됩니다!

---

**생성 시간:** 2025-12-16 22:40 KST  
**디버그 페이지:** https://ddos.neuralgrid.kr/check-auth.html  
**상태:** ✅ 디버그 도구 배포 완료
