# 🚀 DDoS 보안 플랫폼 - 사용자 빠른 시작 가이드

## 📱 Phase 2 완료: My Page 대시보드 접근 방법

### ✅ **배포 완료 확인됨** (2025-12-16 00:50 KST)
- MyPage 대시보드: 정상 배포 ✅
- Backend API: 정상 운영 ✅
- Auth 통합: 완료 ✅

---

## 🔐 **접속 방법**

### **방법 1: Auth 대시보드를 통한 접근** (권장)

1. **로그인**
   ```
   https://auth.neuralgrid.kr/
   ```
   - 이메일/비밀번호로 로그인
   - 또는 소셜 로그인 (Google, Kakao 등)

2. **대시보드 자동 이동**
   - 로그인 후 자동으로 대시보드로 이동됩니다
   ```
   https://auth.neuralgrid.kr/dashboard
   ```

3. **DDoS 보안 플랫폼 카드 클릭**
   - 대시보드에서 "🛡️ DDoS 보안 플랫폼" 카드를 찾아 클릭
   - 자동으로 MyPage로 이동합니다

4. **MyPage 확인**
   ```
   https://ddos.neuralgrid.kr/mypage.html
   ```
   - 통계 카드 4개 확인
   - 서버 목록 확인
   - 실시간 차트 확인

---

### **방법 2: 직접 접근** (로그인 상태 필요)

로그인이 이미 되어 있다면 바로 MyPage로 접근 가능:
```
https://ddos.neuralgrid.kr/mypage.html
```

---

## ⚠️ **캐시 문제 해결**

### **증상**
- Auth 대시보드에서 DDoS 카드 클릭 시 등록 페이지(`/register.html`)로 이동
- MyPage(`/mypage.html`)로 이동하지 않음

### **원인**
- 브라우저 캐시 또는 Cloudflare CDN 캐시

### **해결 방법** (순서대로 시도)

#### 1️⃣ **강력 새로고침** (가장 빠름) ⭐
- **Windows/Linux**: `Ctrl` + `Shift` + `R`
- **Mac**: `Cmd` + `Shift` + `R`

#### 2️⃣ **시크릿 모드** (권장) ⭐⭐
1. 시크릿/사생활 보호 모드로 새 창 열기
   - Chrome: `Ctrl + Shift + N`
   - Firefox: `Ctrl + Shift + P`
   - Edge: `Ctrl + Shift + N`
2. `https://auth.neuralgrid.kr/` 접속
3. 로그인 후 테스트

#### 3️⃣ **브라우저 캐시 삭제**
1. 브라우저 설정 열기
2. "인터넷 사용 기록" 또는 "개인정보 보호"
3. "캐시된 이미지 및 파일" 삭제
4. 브라우저 재시작

#### 4️⃣ **타임스탬프 URL 사용**
```
https://auth.neuralgrid.kr/dashboard?v=1734285600
```

---

## 🎨 **MyPage 주요 기능**

### **1. 통계 대시보드**
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│ 등록된 서버 │ 차단된 IP   │ 차단된 도메인│ 일일 요청   │
│     0       │     0       │     0       │     0       │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

### **2. 서버 관리**
- 서버 목록 테이블
- 서버 상태 (온라인/오프라인)
- 서버 정보 (IP, 도메인, OS)
- 차단 통계 (IP/도메인)
- 작업 버튼 (상세보기/삭제)

### **3. 실시간 차트**
- **트래픽 차트**: 시간별 요청/차단 추이
- **차단 통계**: IP/도메인/GeoIP 비율

### **4. 서버 등록**
- "서버 등록" 버튼 클릭 → 등록 페이지 이동
- 무료/프리미엄 플랜 선택
- 서버 정보 입력
- API Key 자동 발급

---

## 🔧 **서버측 검증** (관리자용)

서버에 SSH 접속 후 검증 스크립트 실행:

```bash
cd /home/azamans/webapp
./SIMPLE_VERIFICATION.sh
```

또는 수동 검증:

```bash
# PM2 상태 확인
pm2 status

# API Health Check
curl http://localhost:3105/health

# MyPage 파일 확인
ls -lh /var/www/ddos.neuralgrid.kr/mypage.html

# Auth 대시보드 링크 확인
grep "ddos.neuralgrid.kr/mypage" /var/www/auth.neuralgrid.kr/dashboard.html
```

---

## 📊 **현재 시스템 상태**

| 서비스 | URL | 상태 |
|--------|-----|------|
| Auth 로그인 | `https://auth.neuralgrid.kr/` | 🟢 정상 |
| Auth 대시보드 | `https://auth.neuralgrid.kr/dashboard` | 🟢 정상 |
| **DDoS MyPage** | `https://ddos.neuralgrid.kr/mypage.html` | 🟢 **신규** |
| DDoS 서버 등록 | `https://ddos.neuralgrid.kr/register.html` | 🟢 정상 |
| API 서버 | `http://localhost:3105` | 🟢 정상 |

---

## 🆘 **문제 해결**

### **"로그인이 필요합니다" 메시지**
- **원인**: 로그인 토큰이 없거나 만료됨
- **해결**: `https://auth.neuralgrid.kr/` 에서 다시 로그인

### **404 오류**
- **원인**: 파일이 제대로 배포되지 않음
- **해결**: 관리자에게 문의 (서버측 재배포 필요)

### **차트가 표시되지 않음**
- **원인**: JavaScript 로딩 오류 또는 API 응답 지연
- **해결**: 
  1. 페이지 새로고침 (`F5`)
  2. 브라우저 콘솔 확인 (`F12`)
  3. 네트워크 연결 확인

### **서버 목록이 비어있음**
- **원인**: 아직 등록된 서버가 없음
- **해결**: "서버 등록" 버튼을 클릭하여 서버 등록

---

## 🎯 **다음 단계**

### **Phase 2 완료** ✅
- [x] MyPage 대시보드 개발
- [x] 통계 카드 4개
- [x] 서버 관리 테이블
- [x] 실시간 차트
- [x] Backend API 추가

### **Phase 3 예정** 🚀
- [ ] 서버 에이전트 개발 (클라이언트 설치)
- [ ] 상세 서버 관리 페이지
- [ ] 실시간 로그 스트림
- [ ] 알림 시스템
- [ ] 관리자 페이지

---

## 📞 **지원**

- **GitHub**: https://github.com/hompystory-coder/azamans
- **Documentation**: 
  - [PHASE2_FINAL_SUCCESS.md](./PHASE2_FINAL_SUCCESS.md)
  - [PHASE2_VERIFICATION.md](./PHASE2_VERIFICATION.md)

---

## ✨ **요약**

**Phase 2 "My Page 통합 대시보드"가 성공적으로 완료되었습니다!**

**접속 순서**:
1. `https://auth.neuralgrid.kr/` → 로그인
2. 대시보드 자동 이동
3. "🛡️ DDoS 보안 플랫폼" 카드 클릭
4. MyPage 확인 (`https://ddos.neuralgrid.kr/mypage.html`)

**캐시 문제 시**: `Ctrl+Shift+R` 또는 시크릿 모드 사용

**현재 상태**: 🟢 **모든 서비스 정상 운영 중**

---

**마지막 업데이트**: 2025-12-16 00:50 KST  
**버전**: Phase 2 (v3.0.0-hybrid)  
**상태**: ✅ 배포 완료
