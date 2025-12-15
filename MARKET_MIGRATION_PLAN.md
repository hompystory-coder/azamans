# 🔄 Shorts Market 마이그레이션 계획

## 📋 목표
`a48be6e9.shorts-market.pages.dev` → `market.neuralgrid.kr` 완전 이전

---

## 🎯 이전할 항목

### 1. API 키 및 환경 변수
```env
PORT=3003
YOUTUBE_API_KEY=your_youtube_api_key_here
COUPANG_ACCESS_KEY=c70d5581-434b-4223-9c81-f72641545958
COUPANG_SECRET_KEY=115b6ad08b30eeba54a624f2ed94ca3f0f18005d
COUPANG_PARTNER_ID=AF8150630
JWT_SECRET=your_jwt_secret_here_ESlISrPC33IMEwsYuVQq703GmaU4eQ9wP9cmMytkMzw=
```

### 2. 데이터베이스
- 위치: `/home/azamans/shorts-market/.wrangler/state/v3/d1/miniflare-D1DatabaseObject/275edff5725c76b5490b39119bef7aaa8729e9d55dd85f018fe14ad0a7613dd4.sqlite`
- 크기: 151 KB
- 테이블: users, shorts, orders 등

### 3. 소스 코드
- 전체 디렉토리: `/home/azamans/shorts-market/`
- 주요 파일: server.js, dist/, public/, src/

### 4. PM2 프로세스
- 현재: `shorts-market` (PID: 1798181, 포트 3003)
- 목표: `market.neuralgrid.kr` 도메인으로 서비스

---

## 🔧 마이그레이션 단계

### Phase 1: 데이터베이스 백업
1. SQLite DB 다운로드
2. 스키마 및 데이터 확인
3. 백업 파일 생성

### Phase 2: 환경 설정
1. .env 파일 업데이트
2. 도메인 설정 변경
3. Nginx 설정

### Phase 3: 배포
1. PM2 재시작
2. Nginx 리로드
3. DNS 확인

### Phase 4: 검증
1. 기능 테스트
2. API 키 작동 확인
3. 데이터 무결성 확인

---

## 📊 현재 상태

### PM2 프로세스
- `shorts-market`: 온라인 (포트 3003)
- Uptime: 7시간
- Memory: 63.1 MB

### Nginx 설정
- 기존: Cloudflare Pages (a48be6e9.shorts-market.pages.dev)
- 신규: market.neuralgrid.kr

---

## 🚀 실행 계획

1. **데이터베이스 다운로드 및 백업**
2. **전체 소스 코드 확인**
3. **Nginx 설정 업데이트** (market.neuralgrid.kr)
4. **PM2 재시작**
5. **테스트 및 검증**
6. **DNS 확인**

---

## ⚠️ 주의사항

- 기존 서비스 중단 없이 마이그레이션
- 데이터 손실 방지를 위한 백업 필수
- API 키 보안 유지
- 사용자 세션 유지

---

**다음 단계**: 실제 마이그레이션 수행
