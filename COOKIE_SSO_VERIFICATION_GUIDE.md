# 🧪 Cookie SSO 검증 가이드

## 📅 검증 일시
- 2025-12-16 03:10 KST

---

## 🚀 Step 1: Cookie 테스트 도구 배포 (5분)

### 서버에서 실행
```bash
cd /home/azamans/webapp
chmod +x DEPLOY_COOKIE_TEST_TOOL.sh
./DEPLOY_COOKIE_TEST_TOOL.sh
```

### 예상 출력
```
================================================
Cookie 테스트 도구 배포
================================================

[1/3] Git 저장소 업데이트...
✓ Git 업데이트 완료

[2/3] Auth 도메인에 배포...
✓ Auth 도메인 배포 완료
  → https://auth.neuralgrid.kr/cookie-test.html

[3/3] DDoS 도메인에 배포...
✓ DDoS 도메인 배포 완료
  → https://ddos.neuralgrid.kr/cookie-test.html

================================================
배포 완료!
================================================
```

---

## 🧪 Step 2: 인터랙티브 테스트 도구 사용 (5분)

### 2-1. 테스트 도구 접속
**시크릿 모드(Incognito)로 열기!**

```
https://auth.neuralgrid.kr/cookie-test.html
```

### 2-2. 화면 구성
```
┌─────────────────────────────────────┐
│  🍪 Cookie SSO 테스트 도구           │
├─────────────────────────────────────┤
│  📊 현재 상태                         │
│  - 현재 도메인: auth.neuralgrid.kr   │
│  - Cookie 수: 0                      │
│  - neuralgrid_token: ❌ 없음        │
│  - neuralgrid_user: ❌ 없음         │
├─────────────────────────────────────┤
│  Test 1: Cookie 확인      [대기중]  │
│  [Cookie 확인]                       │
├─────────────────────────────────────┤
│  Test 2: localStorage 확인 [대기중] │
│  [localStorage 확인]                │
├─────────────────────────────────────┤
│  Test 3: getCookie() 함수 [대기중]  │
│  [함수 테스트]                       │
├─────────────────────────────────────┤
│  Test 4: 테스트 Cookie 생성[대기중] │
│  [테스트 Cookie 생성] [삭제]        │
├─────────────────────────────────────┤
│  [🚀 모든 테스트 실행] [🔄 새로고침]│
├─────────────────────────────────────┤
│  💡 Quick Links                     │
│  [Auth 로그인] [Auth 대시보드]      │
│  [DDoS MyPage]                       │
└─────────────────────────────────────┘
```

### 2-3. 테스트 실행
1. **"🚀 모든 테스트 실행"** 버튼 클릭
2. 각 테스트 결과 확인:
   - Test 1: Cookie 확인 → **경고** (아직 로그인 안 함)
   - Test 2: localStorage → **경고** (비어있음)
   - Test 3: getCookie() → **경고** (Cookie 없음)
   - Test 4: 테스트 Cookie → **✅ 성공** (테스트 Cookie 생성됨)

### 2-4. 기대 결과
- Test 1-3: 경고 (정상, 아직 로그인 안 함)
- Test 4: **✅ 성공** (`.neuralgrid.kr` 도메인으로 Cookie 생성 가능)

**중요**: Test 4가 성공하면 Cookie SSO가 기술적으로 작동 가능함을 의미합니다!

---

## 🔐 Step 3: 실제 로그인 플로우 테스트 (5분)

### 3-1. Auth 로그인
**새 시크릿 창 열기!**

1. https://auth.neuralgrid.kr/ 접속
2. 기존 계정으로 로그인
3. 로그인 성공 → Dashboard로 자동 이동

### 3-2. Cookie 확인
**F12 → Application → Cookies → https://auth.neuralgrid.kr**

#### 예상 Cookie:
```
Name: neuralgrid_token
Value: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
Domain: .neuralgrid.kr  ← 🎯 중요!
Path: /
Expires: (24시간 후)
HttpOnly: (체크 안 됨)
Secure: ✓
SameSite: Lax
```

```
Name: neuralgrid_user
Value: %7B%22id%22%3A1%2C%22name%22%3A...
Domain: .neuralgrid.kr  ← 🎯 중요!
Path: /
Expires: (24시간 후)
HttpOnly: (체크 안 됨)
Secure: ✓
SameSite: Lax
```

#### ✅ 성공 조건:
- **Domain이 `.neuralgrid.kr`이어야 함** (앞에 점 `.` 있음)
- `neuralgrid_token`과 `neuralgrid_user` 둘 다 존재

#### ❌ 실패 시:
- Domain이 `auth.neuralgrid.kr`인 경우 → Cookie SSO 코드 배포 안 됨
- Cookie가 없는 경우 → 로그인 실패 또는 코드 오류

---

### 3-3. 테스트 도구로 재확인
**같은 시크릿 창에서**

1. https://auth.neuralgrid.kr/cookie-test.html 접속
2. "🚀 모든 테스트 실행" 클릭
3. 결과 확인:

#### 예상 결과:
```
📊 현재 상태
- 현재 도메인: auth.neuralgrid.kr
- Cookie 수: 2개 이상
- neuralgrid_token: ✅ 존재
- neuralgrid_user: ✅ 존재

Test 1: Cookie 확인 ✅ 성공
- neuralgrid_token: eyJhbG...
- neuralgrid_user: %7B%22...

Test 2: localStorage 확인 ✅ 성공
- neuralgrid_token: eyJhbG...
- user: {"id":1,"name":"..."}

Test 3: getCookie() 함수 ✅ 성공
- neuralgrid_token: ✅ 읽기 성공
- neuralgrid_user: ✅ 읽기 성공

Test 4: 테스트 Cookie ✅ 성공
```

**✅ 모든 테스트가 성공하면 Cookie SSO가 정상 작동 중!**

---

## 🌐 Step 4: Cross-domain Cookie 인증 테스트 (3분)

### 4-1. Auth Dashboard에서 DDoS 카드 클릭
**같은 시크릿 창에서**

1. https://auth.neuralgrid.kr/dashboard 이동
2. "🛡️ DDoS 보안 플랫폼" 카드 찾기
3. 클릭
4. https://ddos.neuralgrid.kr/mypage.html로 이동

#### ✅ 성공 (기대 결과):
- 로그인 페이지로 리다이렉트 **되지 않음**
- MyPage가 **바로 표시됨**
- 사용자 이름, 아바타 표시됨
- "등록된 서버", "총 차단 IP" 등 통계 표시됨

#### ❌ 실패 (문제):
- 로그인 페이지(https://auth.neuralgrid.kr/)로 리다이렉트됨
- 원인:
  1. Cookie Domain이 `.neuralgrid.kr`이 아님
  2. ddos-mypage.html에 Cookie 코드 배포 안 됨
  3. 브라우저 캐시 문제

---

### 4-2. DDoS 도메인에서 테스트 도구 확인
**같은 시크릿 창에서**

1. https://ddos.neuralgrid.kr/cookie-test.html 접속
2. "🚀 모든 테스트 실행" 클릭

#### 예상 결과:
```
📊 현재 상태
- 현재 도메인: ddos.neuralgrid.kr  ← auth에서 ddos로 변경됨!
- Cookie 수: 2개 이상
- neuralgrid_token: ✅ 존재  ← auth 도메인의 Cookie를 읽음!
- neuralgrid_user: ✅ 존재

Test 1: Cookie 확인 ✅ 성공
- neuralgrid_token: eyJhbG...  ← auth와 동일한 토큰!
- neuralgrid_user: %7B%22...

Test 3: getCookie() 함수 ✅ 성공
- neuralgrid_token: ✅ 읽기 성공
- neuralgrid_user: ✅ 읽기 성공
```

**✅ DDoS 도메인에서도 Auth 도메인의 Cookie를 읽을 수 있으면 Cross-domain SSO 성공!**

---

### 4-3. 직접 URL 접근 테스트
**같은 시크릿 창에서**

1. 새 탭 열기
2. 주소창에 `https://ddos.neuralgrid.kr/mypage.html` 직접 입력
3. 엔터

#### ✅ 성공:
- MyPage가 **바로 표시됨**
- 로그인 페이지로 리다이렉트 안 됨

#### ❌ 실패:
- 로그인 페이지로 리다이렉트됨

---

## 🔍 Step 5: 로그아웃 테스트 (2분)

### 5-1. 로그아웃 실행
1. MyPage 또는 Dashboard에서 로그아웃 버튼 클릭
2. 로그인 페이지로 리다이렉트 확인

### 5-2. Cookie 삭제 확인
**F12 → Application → Cookies**

#### 예상 결과:
- `neuralgrid_token` Cookie **삭제됨** ✅
- `neuralgrid_user` Cookie **삭제됨** ✅

### 5-3. 재접근 테스트
1. https://ddos.neuralgrid.kr/mypage.html 재접속
2. 예상: 로그인 페이지로 리다이렉트됨 ✅

**✅ 로그아웃 시 Cookie가 삭제되고, 재로그인이 필요하면 정상!**

---

## 📊 테스트 결과 체크리스트

### 필수 테스트 (모두 통과해야 함)

| # | 테스트 항목 | 예상 결과 | 실제 결과 | 상태 |
|---|-------------|----------|----------|------|
| 1 | 테스트 도구 배포 | 2개 URL 접근 가능 | ? | ⏳ |
| 2 | 테스트 Cookie 생성 (Test 4) | ✅ 성공 | ? | ⏳ |
| 3 | 로그인 후 Cookie 확인 | Domain=.neuralgrid.kr | ? | ⏳ |
| 4 | Auth 테스트 도구 | 모든 테스트 성공 | ? | ⏳ |
| 5 | Dashboard → MyPage 이동 | 바로 표시됨 | ? | ⏳ |
| 6 | DDoS 테스트 도구 | Cookie 읽기 성공 | ? | ⏳ |
| 7 | 직접 URL 접근 | 바로 표시됨 | ? | ⏳ |
| 8 | 로그아웃 | Cookie 삭제 | ? | ⏳ |

### 선택 테스트 (추가 검증)

| # | 테스트 항목 | 예상 결과 | 실제 결과 | 상태 |
|---|-------------|----------|----------|------|
| 9 | localStorage fallback | 작동함 | ? | ⏳ |
| 10 | 24시간 후 만료 | 자동 로그아웃 | ? | ⏳ |
| 11 | 다른 브라우저 | 개별 로그인 필요 | ? | ⏳ |

---

## 🐛 문제 해결 (Troubleshooting)

### 문제 1: Cookie Domain이 `.neuralgrid.kr`이 아님
**증상**: Domain이 `auth.neuralgrid.kr`로 표시됨

**원인**: Cookie SSO 코드가 배포되지 않음

**해결**:
```bash
# 서버에서 확인
grep "domain=.neuralgrid.kr" /var/www/auth.neuralgrid.kr/index.html

# 출력이 없으면 재배포
cd /home/azamans/webapp
./DEPLOY_COOKIE_SSO.sh
```

---

### 문제 2: MyPage 접근 시 로그인 페이지로 리다이렉트
**증상**: Cross-domain 인증 실패

**원인**:
1. Cookie Domain 설정 오류
2. ddos-mypage.html에 Cookie 코드 배포 안 됨
3. 브라우저 캐시

**해결**:
1. **하드 리프레시**: `Ctrl + Shift + R`
2. **시크릿 모드** 재테스트
3. **서버 파일 확인**:
   ```bash
   grep "getCookie.*neuralgrid_token" /var/www/ddos.neuralgrid.kr/mypage.html
   ```
4. 출력이 없으면 재배포:
   ```bash
   cd /home/azamans/webapp
   ./DEPLOY_COOKIE_SSO.sh
   ```

---

### 문제 3: 테스트 도구 접근 불가 (404)
**증상**: https://auth.neuralgrid.kr/cookie-test.html → 404

**원인**: 테스트 도구가 배포되지 않음

**해결**:
```bash
cd /home/azamans/webapp
./DEPLOY_COOKIE_TEST_TOOL.sh
```

---

### 문제 4: Test 4 (테스트 Cookie 생성) 실패
**증상**: "⚠️ Cookie 생성에 실패했습니다"

**원인**:
1. HTTPS가 아님 (Secure 플래그 때문)
2. 브라우저 설정에서 Cookie 차단

**해결**:
1. HTTPS 사용 확인 (`https://` 로 접속)
2. 브라우저 쿠키 설정 확인
3. 시크릿 모드에서 재시도

---

## 📸 스크린샷 가이드

### 필수 스크린샷 (6개)
1. **Auth 로그인 후 Cookie** (F12 → Application → Cookies)
   - `neuralgrid_token` (Domain: .neuralgrid.kr)
   - `neuralgrid_user` (Domain: .neuralgrid.kr)

2. **Auth 테스트 도구** (https://auth.neuralgrid.kr/cookie-test.html)
   - 모든 테스트 실행 결과

3. **Auth Dashboard** (https://auth.neuralgrid.kr/dashboard)
   - DDoS 보안 플랫폼 카드

4. **DDoS MyPage** (https://ddos.neuralgrid.kr/mypage.html)
   - 사용자 정보, 통계 표시

5. **DDoS 테스트 도구** (https://ddos.neuralgrid.kr/cookie-test.html)
   - Cross-domain Cookie 읽기 성공

6. **로그아웃 후 Cookie** (F12 → Application → Cookies)
   - Cookie 삭제 확인

---

## ✅ 검증 성공 기준

### 최소 요구사항 (필수)
- [x] 테스트 도구 배포 완료
- [ ] 로그인 후 Cookie Domain이 `.neuralgrid.kr`
- [ ] Dashboard → MyPage 자동 이동 (재로그인 불필요)
- [ ] DDoS 도메인에서 Auth Cookie 읽기 성공

**4개 모두 충족 시**: ✅ **Cookie SSO 정상 작동!**

### 추가 확인사항 (권장)
- [ ] 직접 URL 접근 성공
- [ ] 로그아웃 시 Cookie 삭제
- [ ] localStorage fallback 작동
- [ ] 테스트 도구 모든 항목 성공

---

## 📝 검증 완료 후

### 1. 결과 기록
이 문서의 체크리스트를 채워주세요:
- ✅ 성공
- ❌ 실패 (사유)
- ⚠️ 부분 성공

### 2. 스크린샷 보관
6개 필수 스크린샷 저장

### 3. 다음 단계 결정
- **성공 시**: Phase 3 진행
- **실패 시**: Troubleshooting → 재배포 → 재테스트

---

## 🎯 예상 소요 시간

| 단계 | 예상 시간 | 설명 |
|------|----------|------|
| Step 1: 테스트 도구 배포 | 2분 | 스크립트 실행 |
| Step 2: 인터랙티브 테스트 | 3분 | 자동 테스트 실행 |
| Step 3: 로그인 플로우 | 5분 | 로그인 & Cookie 확인 |
| Step 4: Cross-domain | 3분 | MyPage 접근 테스트 |
| Step 5: 로그아웃 | 2분 | Cookie 삭제 확인 |
| **총 소요 시간** | **~15분** | 모든 테스트 완료 |

---

## 📞 지원

- **문서**: `COOKIE_SSO_IMPLEMENTATION_COMPLETE.md`
- **배포**: `DEPLOY_COOKIE_SSO.sh`
- **Git**: https://github.com/hompystory-coder/azamans
- **Branch**: `genspark_ai_developer_clean`

---

**작성자**: GenSpark AI Developer  
**최종 수정**: 2025-12-16 03:10 KST

---

**🚀 지금 바로 테스트를 시작하세요!**

1. 서버에서 `./DEPLOY_COOKIE_TEST_TOOL.sh` 실행
2. 브라우저 시크릿 모드로 https://auth.neuralgrid.kr/cookie-test.html 접속
3. "🚀 모든 테스트 실행" 클릭
4. 결과 확인!
