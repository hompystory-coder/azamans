# DDoS Register Page - 상품 구성 업데이트 완료

**작업 완료 시간**: 2025-12-16 05:20 KST  
**작업 브랜치**: `genspark_ai_developer_clean`  
**Git 커밋**: `95ef52a`

---

## ✅ 작업 완료 내용

### 1. 3가지 상품 플랜 구성

https://ddos.neuralgrid.kr/register.html 페이지를 다음과 같이 재구성했습니다:

#### 🚀 1) 무료 7일 체험
- **가격**: 무료
- **기간**: 7일간 무료 체험
- **특징**:
  - 1개 서버/사이트 등록
  - 기본 DDoS 방어
  - IP 차단 관리
  - 실시간 모니터링
- **제한사항**:
  - 전담 기술 지원 없음
  - 고급 분석 리포트 없음

#### 🌐 2) 홈페이지 보호 (인기 플랜)
- **가격**: ₩330,000/년
- **대상**: 중소규모 웹사이트 최적
- **특징**:
  - 최대 5개 도메인 보호
  - Layer 7 DDoS 방어
  - WAF (웹 방화벽) 기본
  - SSL/TLS 지원
  - 실시간 대시보드
  - 이메일 알림
  - 월간 보안 리포트
  - 영업일 기준 24시간 지원
- **모달 기능**:
  - 회사/개인명, 연락처 입력
  - 보호할 도메인 입력 (최대 5개, 쉼표 구분)
  - 운영체제 선택
  - 사용 목적 선택
  - 결제 안내 이메일 발송
  - DNS 설정 가이드 제공

#### 🛡️ 3) 서버 보호 (프리미엄)
- **가격**: ₩2,990,000/년
- **대상**: 엔터프라이즈급 인프라 보호
- **특징**:
  - 무제한 서버 등록
  - Layer 3/4/7 DDoS 방어
  - 고급 WAF + IPS/IDS
  - 전용 IP 할당
  - 국가별 GeoIP 차단
  - API 연동 지원
  - 주간 상세 분석 리포트
  - 24/7 전담 기술 지원
  - SLA 99.9% 보장
- **모달 기능**:
  - 회사/개인명, 연락처 입력
  - 서버 IP 주소 (복수 등록 가능, 쉼표 구분)
  - 도메인 (선택)
  - 운영체제 선택 (Windows Server 포함)
  - 사용 목적 선택 (금융 서비스 옵션 포함)
  - 예상 트래픽 규모 선택
  - 상세 설명 입력
  - 결제 안내 이메일 발송
  - 24시간 이내 전담 매니저 배정

---

## 📁 수정된 파일

### 1. ddos-register.html
**변경 사항**:
- 3가지 상품 카드 UI 구성
- 각 상품별 모달 추가:
  - `trialModal`: 무료 체험 신청
  - `websiteModal`: 홈페이지 보호 신청 (신규)
  - `serverModal`: 서버 보호 신청 (신규)
- JavaScript 함수 업데이트:
  - `openWebsiteModal()`: 홈페이지 보호 모달 열기
  - `openServerModal()`: 서버 보호 모달 열기
  - `submitWebsite()`: 홈페이지 보호 신청 API 호출
  - `submitServer()`: 서버 보호 신청 API 호출

**API 엔드포인트**:
```javascript
// 무료 체험
POST /api/servers/register-trial

// 홈페이지 보호
POST /api/servers/register-website

// 서버 보호
POST /api/servers/register-server
```

### 2. DEPLOY_REGISTER_UPDATE.sh
**배포 스크립트**:
```bash
#!/bin/bash
# Git 최신 코드 가져오기
# DDoS Register 페이지 배포
# Nginx 설정 확인
# Nginx 재시작
```

---

## 🚀 배포 방법

### 서버에서 실행:
```bash
cd /home/azamans/webapp
./DEPLOY_REGISTER_UPDATE.sh
```

### 예상 출력:
```
======================================
DDoS Register Page 상품 구성 배포
======================================

[1/4] Git 최신 코드 가져오기...
Already up to date.

[2/4] DDoS Register 페이지 배포 중...

[3/4] Nginx 설정 확인 중...
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful

[4/4] Nginx 재시작 중...

======================================
배포 완료!
======================================

확인 방법:
  https://ddos.neuralgrid.kr/register.html 접속
  3가지 상품 플랜 확인:
    1. 무료 7일 체험
    2. 홈페이지 보호 (₩330,000/년)
    3. 서버 보호 (₩2,990,000/년)
```

---

## ✅ 테스트 방법

### 1. 페이지 접속
```
https://ddos.neuralgrid.kr/register.html
```

### 2. UI 확인
- ✅ 3가지 상품 카드가 나란히 표시되는지 확인
- ✅ "홈페이지 보호" 카드에 "인기" 배지가 표시되는지 확인
- ✅ "서버 보호" 카드에 "프리미엄" 배지가 표시되는지 확인
- ✅ 각 상품의 가격과 특징이 명확하게 표시되는지 확인

### 3. 기능 테스트
**무료 체험**:
1. "🚀 무료 체험 시작" 버튼 클릭
2. 서버 IP, 도메인, 운영체제 입력
3. 신청 완료 후 설치 스크립트 확인

**홈페이지 보호**:
1. "🌐 홈페이지 보호 신청" 버튼 클릭
2. 회사명, 연락처, 도메인(최대 5개) 입력
3. 신청 완료 후 결제 안내 및 DNS 설정 가이드 확인

**서버 보호**:
1. "🛡️ 서버 보호 신청" 버튼 클릭
2. 회사명, 연락처, 서버 IP(복수 가능), 트래픽 규모 입력
3. 신청 완료 후 결제 안내 및 전담 매니저 배정 안내 확인

### 4. 로그인 확인
- ✅ 로그인하지 않은 상태에서 신청 시 로그인 페이지로 리다이렉트
- ✅ Cookie SSO를 통해 auth.neuralgrid.kr에서 로그인한 상태 유지

---

## 📊 백엔드 API 개발 필요 사항

현재 프론트엔드만 구현되었으며, 다음 API 엔드포인트 개발이 필요합니다:

### 1. POST /api/servers/register-trial
**요청 본문**:
```json
{
  "serverIp": "115.91.5.140",
  "domain": "example.com",
  "osType": "ubuntu",
  "purpose": "website"
}
```

**응답 본문**:
```json
{
  "success": true,
  "message": "무료 체험이 시작되었습니다.",
  "server": {
    "installScript": "curl -sSL https://neuralgrid.kr/install.sh | sudo bash -s abc123"
  }
}
```

### 2. POST /api/servers/register-website
**요청 본문**:
```json
{
  "companyName": "ABC Corp",
  "phone": "010-1234-5678",
  "domains": "example.com, www.example.com",
  "osType": "ubuntu",
  "purpose": "website",
  "description": "쇼핑몰 운영"
}
```

**응답 본문**:
```json
{
  "success": true,
  "message": "홈페이지 보호 신청이 완료되었습니다.",
  "order": {
    "orderId": "ORD-2025-001",
    "amount": 330000,
    "paymentUrl": "https://neuralgrid.kr/payment/ORD-2025-001"
  }
}
```

### 3. POST /api/servers/register-server
**요청 본문**:
```json
{
  "companyName": "XYZ Enterprise",
  "phone": "010-9876-5432",
  "serverIps": "115.91.5.140, 115.91.5.141",
  "domains": "api.example.com",
  "osType": "ubuntu",
  "purpose": "enterprise",
  "trafficScale": "high",
  "description": "금융 서비스 인프라"
}
```

**응답 본문**:
```json
{
  "success": true,
  "message": "서버 보호 신청이 완료되었습니다.",
  "order": {
    "orderId": "ORD-2025-002",
    "amount": 2990000,
    "paymentUrl": "https://neuralgrid.kr/payment/ORD-2025-002",
    "managerId": "MGR-001",
    "managerName": "김담당",
    "managerPhone": "02-1234-5678"
  }
}
```

---

## 📝 Git 커밋 히스토리

```bash
95ef52a - deploy: Add deployment script for DDoS register page product updates
0d59999 - feat: Add 3 product plans to DDoS register page (Trial, Website ₩330K, Server ₩2.99M)
```

---

## 🔗 관련 문서

- **PR**: https://github.com/hompystory-coder/azamans/pull/1
- **브랜치**: `genspark_ai_developer_clean`
- **배포 URL**: https://ddos.neuralgrid.kr/register.html

---

## 📌 다음 단계 (권장)

### 선택 1: 배포 및 테스트 (권장)
1. 서버에서 `./DEPLOY_REGISTER_UPDATE.sh` 실행
2. https://ddos.neuralgrid.kr/register.html 접속하여 3가지 상품 확인
3. 각 상품 모달의 UI 및 폼 테스트
4. 로그인 상태 유지 확인 (Cookie SSO)

### 선택 2: 백엔드 API 개발
1. `/api/servers/register-website` 엔드포인트 개발
2. `/api/servers/register-server` 엔드포인트 개발
3. 결제 시스템 연동 (Toss Payments, KG이니시스 등)
4. 이메일 발송 기능 구현
5. 전담 매니저 배정 시스템 구현

### 선택 3: Phase 3 진행
Cookie SSO가 완전히 검증되었으므로, Phase 3 (서버 상세 관리 페이지, WebSocket 실시간 알림) 개발 시작

---

**작업자**: GenSpark AI Developer  
**작업 시간**: 약 15분  
**상태**: ✅ 완료 (배포 대기)
