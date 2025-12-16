# 🚀 Cookie SSO 검증 시작 가이드

## ⚡ 빠른 시작 (5분)

### Step 1: 서버에서 실행 (2분)

터미널을 열고 다음 명령어를 **그대로 복사하여 실행**하세요:

```bash
cd /home/azamans/webapp && chmod +x DEPLOY_COOKIE_TEST_TOOL.sh && ./DEPLOY_COOKIE_TEST_TOOL.sh
```

### Step 2: 브라우저 테스트 (3분)

1. **시크릿 모드(Incognito)** 브라우저 열기
2. 다음 URL 접속:
   ```
   https://auth.neuralgrid.kr/cookie-test.html
   ```
3. **"🚀 모든 테스트 실행"** 버튼 클릭
4. 결과 확인

---

## 📋 상세 검증 절차 (15분)

### ✅ Test 1: 테스트 도구 배포 확인 (2분)

**실행**:
```bash
cd /home/azamans/webapp
./DEPLOY_COOKIE_TEST_TOOL.sh
```

**예상 출력**:
```
✓ Auth 도메인 배포 완료
  → https://auth.neuralgrid.kr/cookie-test.html
✓ DDoS 도메인 배포 완료
  → https://ddos.neuralgrid.kr/cookie-test.html
```

**확인**: 두 URL이 404 없이 정상 접근되어야 함

---

### ✅ Test 2: 기본 기능 테스트 (3분)

**브라우저 (시크릿 모드)**:
1. https://auth.neuralgrid.kr/cookie-test.html 접속
2. "🚀 모든 테스트 실행" 클릭
3. **Test 4 (테스트 Cookie 생성)** 결과 확인

**성공 조건**:
- Test 4: ✅ **성공** (Cookie가 .neuralgrid.kr 도메인으로 생성됨)
- Test 1-3: 경고 (정상, 아직 로그인 안 함)

**의미**: `.neuralgrid.kr` 도메인으로 Cookie를 생성할 수 있음 = Cookie SSO 기술적으로 가능!

---

### ✅ Test 3: 로그인 및 Cookie 확인 (5분)

#### 3-1. 로그인
**새 시크릿 창**:
1. https://auth.neuralgrid.kr/ 접속
2. 기존 계정으로 로그인
3. Dashboard로 이동 확인

#### 3-2. Cookie 확인
**F12 → Application → Cookies → https://auth.neuralgrid.kr**

**확인 사항**:
```
Name: neuralgrid_token
Domain: .neuralgrid.kr  ← 🎯 이게 중요!

Name: neuralgrid_user  
Domain: .neuralgrid.kr  ← 🎯 이게 중요!
```

**성공 조건**: Domain이 **반드시** `.neuralgrid.kr`이어야 함 (앞에 점 `.` 있음)

**실패 시**: Domain이 `auth.neuralgrid.kr`인 경우
→ Cookie SSO 코드가 배포되지 않음
→ `./DEPLOY_COOKIE_SSO.sh` 재실행 필요

#### 3-3. 테스트 도구로 재확인
**같은 시크릿 창**:
1. https://auth.neuralgrid.kr/cookie-test.html 접속
2. "🚀 모든 테스트 실행" 클릭

**성공 조건**: 모든 테스트 ✅ 성공

---

### ✅ Test 4: Cross-domain SSO 검증 (3분)

#### 4-1. Dashboard → MyPage 이동
**같은 시크릿 창**:
1. https://auth.neuralgrid.kr/dashboard 이동
2. "🛡️ DDoS 보안 플랫폼" 카드 클릭
3. MyPage로 이동됨

**✅ 성공 (기대)**:
- 로그인 페이지로 리다이렉트 **안 됨**
- MyPage가 **바로 표시됨**
- 사용자 이름, 통계 표시됨

**❌ 실패 (문제)**:
- 로그인 페이지로 리다이렉트됨
- 해결: Troubleshooting 섹션 참고

#### 4-2. DDoS 테스트 도구 확인
**같은 시크릿 창**:
1. https://ddos.neuralgrid.kr/cookie-test.html 접속
2. "🚀 모든 테스트 실행" 클릭

**성공 조건**:
- neuralgrid_token: ✅ 존재 (Auth와 동일한 토큰)
- neuralgrid_user: ✅ 존재
- **Cross-domain Cookie 읽기 성공!**

#### 4-3. 직접 URL 접근
**새 탭**:
1. https://ddos.neuralgrid.kr/mypage.html 직접 입력
2. 엔터

**성공**: MyPage 바로 표시됨 (재로그인 불필요)

---

### ✅ Test 5: 로그아웃 확인 (2분)

1. 로그아웃 버튼 클릭
2. **F12 → Cookies** 확인
   - neuralgrid_token: ❌ 삭제됨
   - neuralgrid_user: ❌ 삭제됨
3. MyPage 재접속: 로그인 페이지로 리다이렉트됨

**성공**: Cookie 삭제 및 재로그인 필요

---

## 🎯 빠른 체크리스트

| # | 테스트 | 결과 |
|---|--------|------|
| 1 | 테스트 도구 배포 | ⏳ |
| 2 | Test 4 (테스트 Cookie) | ⏳ |
| 3 | 로그인 Cookie Domain | ⏳ |
| 4 | Dashboard → MyPage | ⏳ |
| 5 | DDoS 도메인 Cookie 읽기 | ⏳ |
| 6 | 직접 URL 접근 | ⏳ |
| 7 | 로그아웃 Cookie 삭제 | ⏳ |

**✅ 4개 이상 성공**: Cookie SSO 정상 작동!

---

## 🐛 문제 해결

### 문제: Cookie Domain이 `auth.neuralgrid.kr`
**해결**:
```bash
cd /home/azamans/webapp
./DEPLOY_COOKIE_SSO.sh
```
재배포 후 **하드 리프레시** (`Ctrl + Shift + R`)

### 문제: MyPage 접근 시 로그인 페이지로 리다이렉트
**해결**:
1. 시크릿 모드 재시도
2. Cookie Domain 확인 (`.neuralgrid.kr`이어야 함)
3. 브라우저 캐시 삭제

### 문제: 테스트 도구 404
**해결**:
```bash
cd /home/azamans/webapp
./DEPLOY_COOKIE_TEST_TOOL.sh
```

---

## 📸 필수 스크린샷 (6개)

1. Auth Cookie (Domain: .neuralgrid.kr)
2. Auth 테스트 도구 (모든 테스트 성공)
3. Auth Dashboard (DDoS 카드)
4. DDoS MyPage (통계 표시)
5. DDoS 테스트 도구 (Cookie 읽기)
6. 로그아웃 후 (Cookie 삭제)

---

## ⏱️ 예상 시간

| 작업 | 시간 |
|------|------|
| 배포 | 2분 |
| 기본 테스트 | 3분 |
| 로그인 플로우 | 5분 |
| Cross-domain | 3분 |
| 로그아웃 | 2분 |
| **총** | **15분** |

---

## 🎉 성공 시 다음 단계

- ✅ Cookie SSO 검증 완료
- ✅ Phase 2.5 최종 완료
- 🚀 Phase 3 개발 시작!

---

## 📞 지원

- **상세 가이드**: `COOKIE_SSO_VERIFICATION_GUIDE.md`
- **Git**: https://github.com/hompystory-coder/azamans
- **Branch**: `genspark_ai_developer_clean`
- **Commit**: `9d1eb95`

---

## 🚀 지금 시작하세요!

**명령어 한 줄로 시작**:
```bash
cd /home/azamans/webapp && ./DEPLOY_COOKIE_TEST_TOOL.sh
```

**브라우저 접속**:
```
https://auth.neuralgrid.kr/cookie-test.html
```

**버튼 클릭**:
```
🚀 모든 테스트 실행
```

**끝!** 🎉

---

**작성자**: GenSpark AI Developer  
**최종 수정**: 2025-12-16 03:15 KST
