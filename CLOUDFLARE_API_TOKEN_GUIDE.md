# Cloudflare API 토큰 발급 가이드
**단계별 스크린샷 설명**

---

## 🔑 API 토큰 발급 방법 (5분)

### 1단계: Cloudflare 프로필 접속

**URL**: https://dash.cloudflare.com/profile/api-tokens

또는

1. Cloudflare 로그인: https://dash.cloudflare.com/
2. 우측 상단 프로필 아이콘 클릭
3. "My Profile" 클릭
4. 왼쪽 메뉴에서 "API Tokens" 클릭

---

### 2단계: 새 토큰 생성

**"Create Token" 버튼 클릭**

화면에 여러 템플릿이 표시됩니다:
- Edit zone DNS ⭐ **(이것을 사용하세요!)**
- Read all resources
- Edit Cloudflare Workers
- ... 등

---

### 3단계: "Edit zone DNS" 템플릿 선택

**"Edit zone DNS" 옆의 "Use template" 버튼 클릭**

템플릿 설명:
```
Edit zone DNS
Grants edit access to DNS records for a specific zone
```

---

### 4단계: 토큰 권한 설정

자동으로 채워진 설정:

**Permissions (권한):**
```
Zone    DNS    Edit
```
✅ 이미 올바르게 설정됨!

**Zone Resources (적용 범위):**
```
Include    Specific zone    [선택 필요]
```

**드롭다운에서 `neuralgrid.kr` 선택**

---

### 5단계: 추가 설정 (선택사항)

**Client IP Address Filtering** (선택):
- 특정 IP에서만 토큰 사용 가능하도록 제한
- 보안 강화를 원하면 설정, 그렇지 않으면 비워두기

**TTL (Time to Live)** (선택):
- 토큰 만료 기간 설정
- 기본값: 만료 없음 (권장)

---

### 6단계: 요약 확인

**"Continue to summary" 버튼 클릭**

확인 화면:
```
Token name: neuralgrid.kr DNS Edit (자동 생성)

Permissions:
✓ Zone - DNS - Edit

Zone Resources:
✓ Include - neuralgrid.kr
```

모든 것이 맞으면 **"Create Token" 버튼 클릭**

---

### 7단계: 토큰 복사 ⚠️ 중요!

**성공 화면:**
```
Success! Your API token has been created.

[긴 문자열 표시]
abc123def456ghi789jkl012mno345pqr678stu901vwx234yz

⚠️ Make sure to copy your API token now.
   You won't be able to see it again!
```

**토큰 복사 방법:**
1. 토큰 옆의 📋 "Copy" 버튼 클릭
2. 또는 전체 텍스트 드래그 후 Ctrl+C (Windows) / Cmd+C (Mac)

**⚠️ 매우 중요:**
- 이 토큰은 **딱 한 번만** 표시됩니다!
- 복사 후 안전한 곳에 저장하세요
- 창을 닫으면 다시 볼 수 없습니다

---

### 8단계: 토큰 테스트 (선택)

Cloudflare에서 제공하는 테스트 명령어:

```bash
curl -X GET "https://api.cloudflare.com/client/v4/user/tokens/verify" \
     -H "Authorization: Bearer <YOUR_API_TOKEN>" \
     -H "Content-Type:application/json"
```

**성공 응답:**
```json
{
  "result": {
    "id": "...",
    "status": "active"
  },
  "success": true
}
```

---

## 🎯 토큰 사용 방법

### 자동 스크립트로 사용

```bash
cd /home/azamans/webapp
./setup_auth_dns.sh <복사한_API_토큰>
```

**예시:**
```bash
./setup_auth_dns.sh abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
```

---

## 🔐 보안 주의사항

### ✅ 해야 할 것:
- ✅ 토큰을 안전한 곳에 저장 (비밀번호 관리자 등)
- ✅ 토큰에 최소 권한만 부여 (DNS Edit만)
- ✅ 특정 도메인에만 적용 (neuralgrid.kr)
- ✅ 사용 후 필요 없으면 토큰 삭제

### ❌ 하지 말아야 할 것:
- ❌ 토큰을 Git에 커밋
- ❌ 토큰을 공개 채팅에 공유
- ❌ 토큰을 스크린샷으로 공유
- ❌ 불필요하게 넓은 권한 부여

---

## 🔄 토큰 분실 시

**토큰을 복사하지 못하고 창을 닫았다면:**

1. API Tokens 페이지로 돌아가기
2. 생성된 토큰 옆의 "Roll" 버튼 클릭 (토큰 재생성)
3. 또는 "Delete" 후 새로 생성

**기존 토큰 확인:**
- ❌ 기존 토큰 값은 다시 볼 수 없습니다
- ✅ 토큰 목록과 권한은 확인 가능합니다
- ✅ 필요시 새 토큰을 생성하세요

---

## 📋 체크리스트

토큰 발급 전:
- [ ] Cloudflare 계정 로그인 확인
- [ ] neuralgrid.kr 도메인이 계정에 있는지 확인

토큰 발급 중:
- [ ] "Edit zone DNS" 템플릿 선택
- [ ] Zone: neuralgrid.kr 선택
- [ ] Permissions: Zone - DNS - Edit 확인

토큰 발급 후:
- [ ] 토큰 복사 완료
- [ ] 안전한 곳에 저장
- [ ] 토큰 테스트 (선택)
- [ ] 스크립트 실행 준비

---

## 🚀 빠른 링크

| 단계 | URL |
|------|-----|
| 1. API 토큰 페이지 | https://dash.cloudflare.com/profile/api-tokens |
| 2. 토큰 생성 | "Create Token" 버튼 |
| 3. 템플릿 선택 | "Edit zone DNS" → "Use template" |
| 4. Zone 선택 | neuralgrid.kr |
| 5. 생성 | "Continue to summary" → "Create Token" |
| 6. 복사 | 📋 Copy 버튼 클릭 |

---

## ❓ 자주 묻는 질문 (FAQ)

### Q1: API 토큰과 Global API Key의 차이는?

**API 토큰 (권장):**
- ✅ 특정 권한만 부여 가능 (DNS만)
- ✅ 특정 도메인만 접근 가능
- ✅ 언제든 삭제/재생성 가능
- ✅ 보안성 높음

**Global API Key (비권장):**
- ❌ 모든 권한 (위험)
- ❌ 모든 도메인 접근
- ❌ 재생성 시 모든 앱에 영향
- ❌ 보안 위험 높음

👉 **반드시 API 토큰을 사용하세요!**

### Q2: 토큰 만료 기간은 어떻게 설정하나요?

**토큰 생성 시 TTL 설정:**
- 만료 없음 (기본값, 권장)
- 특정 날짜까지
- 특정 기간 (30일, 90일 등)

**권장:**
- DNS 설정용: 만료 없음 또는 1년
- 일회성 작업: 1일

### Q3: 토큰을 여러 개 만들 수 있나요?

**예, 가능합니다!**
- 각 토큰마다 다른 권한 부여 가능
- 각 토큰마다 다른 이름 지정 가능
- 개별적으로 관리/삭제 가능

**권장 사용법:**
- DNS 관리용 토큰 1개
- 개발/테스트용 토큰 1개
- 프로덕션용 토큰 1개

### Q4: 토큰이 작동하지 않으면?

**확인 사항:**
1. 토큰 복사 시 공백 포함되지 않았는지
2. 권한이 "Zone - DNS - Edit"인지
3. Zone이 "neuralgrid.kr"로 설정되었는지
4. 토큰이 만료되지 않았는지

**테스트 명령어:**
```bash
curl -X GET "https://api.cloudflare.com/client/v4/user/tokens/verify" \
     -H "Authorization: Bearer <YOUR_TOKEN>" \
     -H "Content-Type:application/json"
```

---

## 📞 도움이 필요하면

토큰을 발급받은 후 알려주세요!

```bash
./setup_auth_dns.sh <YOUR_API_TOKEN>
```

이 명령어로 자동으로 auth.neuralgrid.kr DNS를 설정할 수 있습니다! 🚀

---

**작성일**: 2025-12-15  
**대상 도메인**: neuralgrid.kr  
**목적**: auth.neuralgrid.kr DNS 설정
