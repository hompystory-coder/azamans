# auth.neuralgrid.kr DNS 설정 해결 방안

**날짜**: 2025-12-15  
**상태**: DNS 관리 방식 확인 필요

---

## 🔍 현재 상황

### ✅ 확인된 사항
1. **Cloudflare API 토큰**: 유효함 ✅
   - Token: `joXO55oWFjUJMEmEkl5fmPYFWsHvOQT4OoMzMYjY`
   - Status: Active
   - API: 정상 작동

2. **neuralgrid.kr 도메인**: Cloudflare에 없음 ❌
   - Zone ID: 찾을 수 없음
   - Result: 빈 배열 (`[]`)

3. **실제 DNS 관리**: dnszi.com ✅
   - URL: https://dnszi.com/
   - 1차 DNS: ns3.dnszi.com (121.78.251.20)
   - 2차 DNS: ns18.dnszi.com (121.78.72.36)

---

## 🎯 해결 방안

### 방법 1: dnszi.com에서 직접 DNS 설정 (가장 빠름) ⭐

**1단계: dnszi.com 접속**
- URL: https://dnszi.com/
- 로그인 (계정 정보 필요)

**2단계: neuralgrid.kr 도메인 선택**
- 도메인 관리 페이지 이동

**3단계: A 레코드 추가**
```
Type:    A
Host:    auth
Value:   115.91.5.140
TTL:     3600 (또는 기본값)
```

**4단계: DNS 전파 확인 (5-15분)**
```bash
dig +short auth.neuralgrid.kr @8.8.8.8
# 결과: 115.91.5.140
```

**5단계: SSL 인증서 발급**
```bash
ssh azamans@115.91.5.140

sudo certbot certonly --nginx -d auth.neuralgrid.kr \
  --non-interactive --agree-tos -m admin@neuralgrid.kr

sudo nginx -t && sudo systemctl reload nginx
```

---

### 방법 2: 서버에서 직접 DNS 설정 (로컬 호스트)

**임시 해결책**: `/etc/hosts` 파일에 추가

```bash
ssh azamans@115.91.5.140

# /etc/hosts에 추가
echo "115.91.5.140 auth.neuralgrid.kr" | sudo tee -a /etc/hosts

# SSL 인증서 발급 (자체 서명 또는 Let's Encrypt DNS Challenge)
sudo certbot certonly --manual -d auth.neuralgrid.kr \
  --preferred-challenges dns
```

⚠️ **주의**: 이 방법은 서버 내부에서만 작동합니다 (외부 접속 불가)

---

### 방법 3: 도메인을 Cloudflare로 이전 (장기적 해결책)

**장점:**
- ✅ API 자동화 가능
- ✅ 무료 SSL (자동 갱신)
- ✅ DDoS 보호
- ✅ CDN 무료 제공
- ✅ 빠른 DNS 전파

**단점:**
- ❌ 네임서버 변경 필요 (24-48시간 소요)
- ❌ 기존 DNS 레코드 재설정 필요

**진행 방법:**

**1단계: Cloudflare에 도메인 추가**
- https://dash.cloudflare.com/ 접속
- "Add a Site" 클릭
- neuralgrid.kr 입력

**2단계: DNS 레코드 가져오기**
- Cloudflare가 기존 DNS 레코드 자동 스캔
- 확인 후 "Continue"

**3단계: 네임서버 변경 (dnszi.com에서)**
- Cloudflare가 제공하는 네임서버로 변경:
  ```
  예시:
  ns1.cloudflare.com
  ns2.cloudflare.com
  ```

**4단계: DNS 전파 대기 (24-48시간)**

**5단계: Cloudflare에서 auth 레코드 추가**
- 이제 자동화 스크립트 사용 가능

---

## 🚀 권장 방안: 방법 1 (dnszi.com)

**이유:**
1. ✅ 가장 빠름 (10분 내 완료)
2. ✅ 기존 설정 변경 없음
3. ✅ 외부 접속 가능
4. ✅ Let's Encrypt SSL 자동 발급 가능

**필요한 정보:**
- dnszi.com 로그인 계정 (확인 필요)

---

## 📋 dnszi.com DNS 설정 가이드

### 1. dnszi.com 접속
**URL**: https://dnszi.com/

### 2. 로그인
- 계정 정보 필요 (MASTER_CREDENTIALS.md에 없음)
- 계정 복구 또는 새로 생성 필요할 수 있음

### 3. neuralgrid.kr 도메인 선택
- 대시보드에서 neuralgrid.kr 클릭
- "DNS 관리" 또는 "레코드 관리" 메뉴

### 4. 새 레코드 추가
**입력 정보:**
```
레코드 타입: A
호스트명:    auth
IP 주소:     115.91.5.140
TTL:         3600 (1시간) 또는 기본값
```

### 5. 저장 및 적용

### 6. DNS 전파 확인
```bash
# 로컬에서 테스트
dig +short auth.neuralgrid.kr @8.8.8.8

# 또는
nslookup auth.neuralgrid.kr 8.8.8.8
```

**예상 결과:**
```
115.91.5.140
```

### 7. SSL 인증서 발급
**DNS 전파 확인 후 실행:**
```bash
ssh azamans@115.91.5.140

sudo certbot certonly --nginx -d auth.neuralgrid.kr \
  --non-interactive --agree-tos -m admin@neuralgrid.kr

# Nginx 리로드
sudo systemctl reload nginx

# 테스트
curl -I https://auth.neuralgrid.kr/health
```

---

## 🔍 현재 DNS 상태 확인

### 기존 서브도메인 확인
```bash
# 현재 작동 중인 서브도메인들
dig +short music.neuralgrid.kr    # 확인
dig +short monitor.neuralgrid.kr  # 확인
dig +short n8n.neuralgrid.kr      # 확인
dig +short market.neuralgrid.kr   # 확인
```

이들이 모두 정상 작동한다면 dnszi.com이 올바른 DNS 관리 시스템입니다.

---

## 🎯 다음 단계

### Option A: dnszi.com 계정 있음
1. dnszi.com 로그인
2. auth A 레코드 추가 (5분)
3. DNS 전파 대기 (10분)
4. SSL 발급 (2분)
✅ **총 소요 시간: 약 15-20분**

### Option B: dnszi.com 계정 없음/분실
1. 계정 복구 시도
2. 또는 도메인 등록 이메일로 비밀번호 재설정
3. 또는 도메인 등록업체 연락

### Option C: Cloudflare로 완전 이전 (선택)
1. Cloudflare에 도메인 추가
2. 네임서버 변경
3. 24-48시간 대기
4. 자동화 스크립트 사용
✅ **장기적 이점: API 자동화, 무료 SSL, CDN, DDoS 보호**

---

## 📞 필요한 정보

**dnszi.com 로그인 정보 확인:**
- [ ] 사용자 ID/이메일
- [ ] 비밀번호
- [ ] 또는 도메인 등록 이메일 주소

**있으시면 알려주세요!** 
바로 DNS 설정을 도와드리겠습니다. 🚀

---

## 🛠️ 대안: 수동 SSL 설정 (DNS 없이)

**만약 DNS 설정이 불가능하다면:**

1. **자체 서명 인증서 사용** (개발 전용)
   ```bash
   sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
     -keyout /etc/ssl/private/auth-neuralgrid-selfsigned.key \
     -out /etc/ssl/certs/auth-neuralgrid-selfsigned.crt
   ```

2. **IP로 직접 접속**
   - `https://115.91.5.140:3099`
   - Nginx 설정으로 특정 포트 오픈

⚠️ **주의**: 프로덕션 환경에는 적합하지 않음

---

**업데이트**: 2025-12-15  
**작성자**: Genspark AI Assistant  
**목적**: auth.neuralgrid.kr DNS 문제 해결
