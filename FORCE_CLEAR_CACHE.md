# 🔴 브라우저 캐시 강제 삭제 가이드

## 문제 상황
- 서버에는 수정된 코드가 배포됨 ✅
- 브라우저가 캐시된 이전 버전을 계속 사용 중 ❌

---

## 해결 방법 (순서대로 시도)

### ✅ Method 1: Chrome 전체 캐시 삭제 (가장 확실)

1. **Chrome 설정 열기**
   - 주소창에 입력: `chrome://settings/clearBrowserData`
   - 또는: `Ctrl + Shift + Delete`

2. **시간 범위 선택**
   - "전체 기간" 선택

3. **항목 선택**
   - ✅ 쿠키 및 기타 사이트 데이터
   - ✅ 캐시된 이미지 및 파일
   - ✅ 호스팅된 앱 데이터

4. **"데이터 삭제"** 클릭

5. **Chrome 완전 종료**
   - 모든 Chrome 창 닫기
   - 작업 관리자에서 Chrome 프로세스 확인 후 종료

6. **Chrome 재시작**
   - https://auth.neuralgrid.kr/ 접속

---

### ✅ Method 2: 시크릿 모드 + 캐시 비활성화

1. **새 시크릿 창** (Ctrl + Shift + N)

2. **F12** (개발자 도구)

3. **Network** 탭

4. **"Disable cache"** 체크 ✅

5. **Dashboard 접속**
   - https://auth.neuralgrid.kr/dashboard

6. **강제 새로고침**
   - Ctrl + Shift + R

---

### ✅ Method 3: 개발자 도구 강제 새로고침

1. **F12** (개발자 도구 열기)

2. **새로고침 버튼 우클릭**
   - 주소창 옆 새로고침 버튼
   
3. **"캐시 비우기 및 강제 새로고침"** 선택
   - (Empty Cache and Hard Reload)

---

### ✅ Method 4: URL에 타임스탬프 추가

임시 해결책:

```
https://auth.neuralgrid.kr/dashboard?v=20251216050233
```

브라우저가 새 파일로 인식하도록 강제

---

### ✅ Method 5: 다른 브라우저 사용

- Firefox 시크릿 모드
- Edge 시크릿 모드
- Safari 사설 브라우징

---

## 🧪 캐시 삭제 확인 방법

### 1. Network 탭 확인

**F12 → Network**

- "Disable cache" ✅
- 페이지 새로고침
- dashboard 파일 클릭
- **Size** 칼럼 확인:
  - "(memory cache)" 또는 "(disk cache)" ❌ 캐시 사용 중
  - 파일 크기 (예: 25.3 kB) ✅ 서버에서 새로 받음

### 2. Response 헤더 확인

**F12 → Network → dashboard → Headers**

- **Request Headers**:
  - `Cache-Control: no-cache` 또는 `max-age=0` 있어야 함

- **Response Headers**:
  - `Status Code: 200` (not 304)
  - 304는 캐시 사용 의미

### 3. Preview에서 코드 확인

**F12 → Network → dashboard → Preview**

- `Ctrl + F`로 검색: `decodeURIComponent`
- 찾아지면 ✅ 새 버전
- 안 찾아지면 ❌ 캐시된 이전 버전

---

## 🎯 추천 순서

### 🥇 1순위: Method 1 (Chrome 전체 캐시 삭제)
- 가장 확실함
- 5분 소요

### 🥈 2순위: Method 2 (시크릿 + Disable cache)
- 빠름 (1분)
- 캐시 영향 완전 제거

### 🥉 3순위: Method 3 (강제 새로고침)
- 가장 빠름 (10초)
- 때로 작동 안 할 수 있음

---

## ⚠️ 주의사항

### Chrome 캐시 특성
- Chrome은 **매우 공격적**으로 캐시 사용
- 일반 새로고침(F5)으로는 캐시 안 지워짐
- 하드 리프레시(Ctrl+Shift+R)도 때로 부족

### 해결책
- **시크릿 모드 + Disable cache** 조합이 가장 확실
- 또는 **Chrome 완전 종료 후 재시작**

---

## 📊 확인 체크리스트

| # | 확인 항목 | 상태 |
|---|----------|------|
| 1 | Chrome 캐시 완전 삭제 | ⏳ |
| 2 | Chrome 완전 종료 & 재시작 | ⏳ |
| 3 | 시크릿 모드 열기 | ⏳ |
| 4 | F12 → Network → Disable cache | ⏳ |
| 5 | Dashboard 접속 | ⏳ |
| 6 | Console 에러 확인 | ⏳ |
| 7 | Network → Size 확인 (캐시 아님) | ⏳ |
| 8 | Preview에서 decodeURIComponent 검색 | ⏳ |

---

## 🚀 최종 추천 방법

**이 순서를 정확히 따라주세요:**

```bash
1. Chrome 완전 종료 (모든 창)
2. 작업 관리자에서 Chrome 프로세스 확인 & 종료
3. Chrome 재시작
4. Ctrl + Shift + N (시크릿 모드)
5. F12 (개발자 도구)
6. Network 탭 → "Disable cache" ✅
7. https://auth.neuralgrid.kr/ 접속
8. 로그인
9. F12 → Console 확인 (에러 없어야 함!)
```

---

## 💡 여전히 안 되면

### 서버 측 캐시 헤더 추가 (최후의 수단)

Nginx에서 캐시 방지 헤더 추가:

```nginx
location /dashboard.html {
    add_header Cache-Control "no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0";
    add_header Pragma "no-cache";
    add_header Expires "0";
}
```

하지만 **먼저 브라우저 캐시 삭제를 시도하세요!**

---

**작성일**: 2025-12-16 05:05 KST
