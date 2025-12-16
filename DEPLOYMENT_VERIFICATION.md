# Cookie SSO 배포 검증

## 배포 완료 확인
- 일시: 2025-12-16 02:34 KST
- 서버: 115.91.5.140
- 배포 스크립트: `DEPLOY_COOKIE_SSO.sh` 실행 완료 ✅

## 배포된 파일

### 1. Auth 서비스
```bash
/var/www/auth.neuralgrid.kr/index.html
/var/www/auth.neuralgrid.kr/dashboard.html
```

### 2. DDoS 서비스
```bash
/var/www/ddos.neuralgrid.kr/mypage.html
```

### 3. Nginx 상태
- 설정 확인: ✅ 정상
- Nginx 리로드: ✅ 완료

## 서버 측 검증 명령어

배포 서버(115.91.5.140)에서 다음 명령어를 실행하여 확인하세요:

### 1. Cookie 코드 배포 확인
```bash
# Auth 로그인 페이지
grep -o "neuralgrid_token" /var/www/auth.neuralgrid.kr/index.html | head -3

# 기대 출력:
# neuralgrid_token
# neuralgrid_token
# neuralgrid_token
```

```bash
# DDoS MyPage
grep -o "getCookie" /var/www/ddos.neuralgrid.kr/mypage.html | head -3

# 기대 출력:
# getCookie
# getCookie
# getCookie
```

### 2. Cookie 설정 코드 상세 확인
```bash
# Cookie 저장 로직 확인
grep "document.cookie.*domain=.neuralgrid.kr" /var/www/auth.neuralgrid.kr/index.html

# 기대 출력:
# document.cookie = `neuralgrid_token=${data.token}; domain=.neuralgrid.kr; path=/; max-age=86400; SameSite=Lax; Secure`;
# document.cookie = `neuralgrid_user=${encodeURIComponent(JSON.stringify(data.user))}; domain=.neuralgrid.kr; path=/; max-age=86400; SameSite=Lax; Secure`;
```

### 3. 파일 타임스탬프 확인
```bash
ls -lh /var/www/auth.neuralgrid.kr/index.html
ls -lh /var/www/auth.neuralgrid.kr/dashboard.html
ls -lh /var/www/ddos.neuralgrid.kr/mypage.html

# 기대: 2025-12-16 02:34 경 수정됨
```

## 브라우저 테스트 (사용자 실행 필요)

### 🎯 Test 1: 로그인 및 Cookie 생성
**시크릿 모드(Incognito)로 테스트하세요!**

1. 브라우저 시크릿 모드 열기
2. `https://auth.neuralgrid.kr/` 접속
3. 로그인 (기존 계정 사용)
4. **F12** (개발자 도구) → **Application** 탭 → **Cookies** 클릭
5. `https://auth.neuralgrid.kr` 확인

**기대 결과**:
```
Name: neuralgrid_token
Value: eyJhbGc... (JWT 토큰)
Domain: .neuralgrid.kr  ← 중요!
Path: /
Expires: (24시간 후)
HttpOnly: (체크 안 됨)
Secure: ✓
SameSite: Lax
```

```
Name: neuralgrid_user
Value: %7B%22id%22... (URL 인코딩된 JSON)
Domain: .neuralgrid.kr  ← 중요!
Path: /
Expires: (24시간 후)
HttpOnly: (체크 안 됨)
Secure: ✓
SameSite: Lax
```

**스크린샷**: Cookie 설정을 캡처하여 확인

---

### 🎯 Test 2: Cross-domain SSO 인증

1. Auth 대시보드 (`https://auth.neuralgrid.kr/dashboard`)로 이동
2. "🛡️ DDoS 보안 플랫폼" 카드 클릭
3. `https://ddos.neuralgrid.kr/mypage.html`로 이동됨

**기대 결과**:
- ✅ 로그인 페이지로 리다이렉트 **되지 않음**
- ✅ MyPage가 **바로 표시됨**
- ✅ 사용자 이름, 통계, 서버 목록 정상 표시

**실패 시 (로그인 페이지로 리다이렉트)**:
- Cookie의 Domain이 `.neuralgrid.kr`이 아닌 `auth.neuralgrid.kr`로 설정되었을 가능성
- 브라우저 캐시 문제 → 하드 리프레시 (Ctrl + Shift + R)
- 배포가 제대로 안 됨 → 서버 측 파일 재확인

---

### 🎯 Test 3: 직접 URL 접근

1. 새 탭 열기
2. `https://ddos.neuralgrid.kr/mypage.html` 주소창에 직접 입력
3. 엔터

**기대 결과**:
- ✅ 로그인 없이 MyPage 바로 표시
- ✅ Cookie에서 자동으로 인증 정보 읽어옴

---

### 🎯 Test 4: 로그아웃

1. 로그아웃 버튼 클릭
2. **개발자 도구** → **Application** → **Cookies** 확인

**기대 결과**:
- ✅ `neuralgrid_token` Cookie 삭제됨
- ✅ `neuralgrid_user` Cookie 삭제됨
- ✅ localStorage의 token, user도 삭제됨

3. `https://ddos.neuralgrid.kr/mypage.html` 재접속

**기대 결과**:
- ✅ 로그인 페이지 (`https://auth.neuralgrid.kr/`)로 리다이렉트

---

### 🎯 Test 5: localStorage Fallback (하위 호환)

1. **개발자 도구** → **Application** → **Cookies**에서
2. `neuralgrid_token` 및 `neuralgrid_user` Cookie 수동 삭제
3. **Console** 탭에서 다음 실행:
   ```javascript
   localStorage.setItem('neuralgrid_token', 'test-token-12345');
   localStorage.setItem('user', '{"id":1,"name":"Test User","email":"test@example.com"}');
   ```
4. 페이지 새로고침 (F5)

**기대 결과**:
- ✅ localStorage에서 토큰 읽어옴 (Cookie 없어도)
- ✅ 페이지가 정상적으로 로드됨 (인증 작동)
- ⚠️ API 호출은 실패할 수 있음 (test-token이 유효하지 않으므로)

---

## 테스트 결과 체크리스트

| Test | 예상 결과 | 실제 결과 | 상태 |
|------|----------|----------|------|
| Test 1: Cookie 생성 | Cookie Domain=.neuralgrid.kr | ? | ⏳ |
| Test 2: Cross-domain SSO | MyPage 바로 표시 | ? | ⏳ |
| Test 3: 직접 URL 접근 | MyPage 바로 표시 | ? | ⏳ |
| Test 4: 로그아웃 | Cookie 삭제, 리다이렉트 | ? | ⏳ |
| Test 5: localStorage Fallback | 여전히 작동 | ? | ⏳ |

**테스트 완료 후 이 표를 채워주세요!**

---

## 문제 해결 (Troubleshooting)

### 문제 1: Cookie Domain이 `.neuralgrid.kr`이 아님
**증상**: Cookie Domain이 `auth.neuralgrid.kr`로 설정됨

**원인**: 배포 파일이 제대로 적용되지 않음

**해결**:
```bash
# 서버에서 확인
grep "domain=.neuralgrid.kr" /var/www/auth.neuralgrid.kr/index.html

# 출력이 없으면 재배포 필요
cd /home/azamans/webapp
git pull origin genspark_ai_developer_clean
sudo cp auth-login-updated.html /var/www/auth.neuralgrid.kr/index.html
sudo systemctl reload nginx
```

---

### 문제 2: MyPage에서 계속 로그인 페이지로 리다이렉트
**증상**: Cross-domain 인증 실패

**가능한 원인**:
1. Cookie Domain 설정 오류
2. 브라우저 캐시
3. Secure 플래그 문제 (HTTP vs HTTPS)

**해결**:
1. 브라우저 하드 리프레시: `Ctrl + Shift + R`
2. 시크릿 모드에서 재테스트
3. 개발자도구 → Network 탭에서 Cookie 헤더 확인
4. 서버 파일 재확인:
   ```bash
   grep "getCookie.*neuralgrid_token" /var/www/ddos.neuralgrid.kr/mypage.html
   ```

---

### 문제 3: 404 에러 계속 발생
**증상**: Console에 404 에러

**원인**: favicon.ico 또는 기타 리소스 파일 없음

**해결**: (선택 사항)
```bash
# favicon 추가
sudo touch /var/www/auth.neuralgrid.kr/favicon.ico
sudo touch /var/www/ddos.neuralgrid.kr/favicon.ico
```

---

### 문제 4: Nginx 경고 메시지
**증상**: 
```
[warn] protocol options redefined for 0.0.0.0:443
[warn] conflicting server name "neuralgrid.kr" on 0.0.0.0:80
```

**원인**: 여러 Nginx 설정 파일에서 동일한 포트/도메인 설정

**영향**: 경고일 뿐, Cookie SSO 기능에는 영향 없음

**해결**: (선택 사항) Nginx 설정 파일 정리 필요

---

## 성공 기준

다음 조건이 **모두** 충족되면 Cookie SSO 배포 성공:

- [x] 배포 스크립트 실행 완료
- [ ] Cookie Domain이 `.neuralgrid.kr`로 설정됨
- [ ] Test 2: Cross-domain SSO 성공 (MyPage 바로 표시)
- [ ] Test 3: 직접 URL 접근 성공
- [ ] Test 4: 로그아웃 시 Cookie 삭제됨

**3개 이상 성공 시**: ✅ Cookie SSO 정상 작동!

---

## 추가 정보

### 배포 시간
- 2025-12-16 02:34:08 KST

### Git 정보
- Repository: https://github.com/hompystory-coder/azamans
- Branch: genspark_ai_developer_clean
- Commit: 250822b

### 서버 정보
- IP: 115.91.5.140
- Web Root: `/var/www/`
- Nginx Config: `/etc/nginx/sites-enabled/`

---

**다음 단계**: 
1. 위 브라우저 테스트 5개 실행
2. 결과를 체크리스트에 기록
3. 문제 발생 시 Troubleshooting 섹션 참고
4. 모든 테스트 통과 시 Phase 3 진행!
