# 🔴 auth.neuralgrid.kr DNS 문제 해결 가이드

## 📌 문제 상황

**URL:** https://auth.neuralgrid.kr  
**상태:** ❌ 접속 불가  
**에러:** `Could not resolve host: auth.neuralgrid.kr`

---

## 🔍 문제 원인

### DNS A 레코드 미설정

`auth.neuralgrid.kr` 도메인이 DNS에 등록되어 있지 않습니다.

```bash
# DNS 조회 실패
$ nslookup auth.neuralgrid.kr
Server:		8.8.8.8
Address:	8.8.8.8#53

** server can't find auth.neuralgrid.kr: NXDOMAIN
```

### 현재 상태

#### ✅ 서비스 정상 가동
```
PM2 Process: auth-service
Status: ✅ Online (26h uptime)
Port: 3099
Script: /home/azamans/n8n-neuralgrid/auth-service/index.js
Health: http://localhost:3099/health → 200 OK
```

#### ✅ Nginx 설정 완료
```
Config: /etc/nginx/sites-available/auth.neuralgrid.kr
Enabled: ✅ Yes
Proxy: http://127.0.0.1:3099
SSL: /etc/letsencrypt/live/neuralgrid.kr/fullchain.pem (temporary)
```

#### ❌ DNS 레코드 없음
```
Missing: A record for auth.neuralgrid.kr
Expected: auth.neuralgrid.kr → 115.91.5.140
```

---

## 🔧 해결 방법

### 방법 1: Cloudflare DNS 설정 (권장)

#### Step 1: Cloudflare Dashboard 접속
```
1. https://dash.cloudflare.com/ 로그인
2. neuralgrid.kr 도메인 선택
3. "DNS" → "Records" 메뉴 클릭
```

#### Step 2: A 레코드 추가
```
Type:    A
Name:    auth
Content: 115.91.5.140
Proxy:   ☑ Proxied (오렌지 구름 활성화)
TTL:     Auto
```

#### Step 3: DNS 전파 대기
```
⏱️ 시간: 5~10분
🔍 확인: nslookup auth.neuralgrid.kr
```

#### Step 4: SSL 인증서 발급
```bash
# SSH 접속
ssh azamans@115.91.5.140

# SSL 인증서 발급
sudo certbot --nginx -d auth.neuralgrid.kr --non-interactive --agree-tos --email admin@neuralgrid.kr

# Nginx 재시작
sudo systemctl reload nginx
```

---

### 방법 2: 임시 해결 (hosts 파일 수정)

개발/테스트 목적으로 로컬에서만 접속하려면:

#### macOS/Linux
```bash
sudo nano /etc/hosts

# 아래 라인 추가
115.91.5.140    auth.neuralgrid.kr

# 저장 후 종료 (Ctrl+X, Y, Enter)

# 테스트
curl https://auth.neuralgrid.kr/health
```

#### Windows
```cmd
# 관리자 권한으로 메모장 실행
notepad C:\Windows\System32\drivers\etc\hosts

# 아래 라인 추가
115.91.5.140    auth.neuralgrid.kr

# 저장
```

**⚠️ 주의:** 이 방법은 로컬 컴퓨터에서만 작동하며, 다른 사용자는 접속할 수 없습니다.

---

## 🎯 권장 해결 순서

### 1단계: DNS 설정 확인
```bash
# 현재 DNS 레코드 확인
nslookup neuralgrid.kr
# → 115.91.5.140 (정상)

nslookup auth.neuralgrid.kr
# → NXDOMAIN (문제!)
```

### 2단계: Cloudflare에 A 레코드 추가
```
auth.neuralgrid.kr → 115.91.5.140
```

### 3단계: DNS 전파 확인 (5-10분)
```bash
# 계속 확인
watch -n 5 "nslookup auth.neuralgrid.kr"

# 또는
dig auth.neuralgrid.kr +short
# → 115.91.5.140 나오면 성공!
```

### 4단계: SSL 인증서 발급
```bash
ssh azamans@115.91.5.140
echo '7009011226119' | sudo -S certbot --nginx -d auth.neuralgrid.kr --non-interactive --agree-tos --email admin@neuralgrid.kr
```

### 5단계: 접속 테스트
```bash
curl https://auth.neuralgrid.kr/health
# {"status":"ok","service":"auth-service","timestamp":"..."}
```

---

## 📊 현재 도메인 상태

### ✅ 정상 작동하는 서브도메인
```
✅ neuralgrid.kr (115.91.5.140)
✅ www.neuralgrid.kr (115.91.5.140)
✅ mfx.neuralgrid.kr (115.91.5.140)
✅ music.neuralgrid.kr (115.91.5.140)
✅ bn-shop.neuralgrid.kr (115.91.5.140)
✅ n8n.neuralgrid.kr (115.91.5.140)
✅ monitor.neuralgrid.kr (115.91.5.140)
✅ shorts.neuralgrid.kr (115.91.5.140)
✅ ollama.neuralgrid.kr (115.91.5.140)
✅ api.neuralgrid.kr (115.91.5.140)
```

### ❌ DNS 미설정 서브도메인
```
❌ auth.neuralgrid.kr (DNS A 레코드 없음)
❌ market.neuralgrid.kr (DNS A 레코드 없음)
```

---

## 🔐 SSL 인증서 현황

### neuralgrid.kr 인증서
```
Path: /etc/letsencrypt/live/neuralgrid.kr/
Subject Alternative Names:
  - neuralgrid.kr
  - www.neuralgrid.kr
  - n8n.neuralgrid.kr

⚠️ auth.neuralgrid.kr 포함 안 됨!
```

### 개별 인증서가 있는 서브도메인
```
✅ api.neuralgrid.kr
✅ bn-shop.neuralgrid.kr
✅ mfx.neuralgrid.kr
✅ monitor.neuralgrid.kr
✅ music.neuralgrid.kr
✅ n8n.neuralgrid.kr
✅ shorts.neuralgrid.kr
✅ ollama.neuralgrid.kr
```

### SSL 인증서 필요
```
❌ auth.neuralgrid.kr (DNS 설정 후 발급)
❌ market.neuralgrid.kr (DNS 설정 후 발급)
```

---

## 🚀 자동화 스크립트

DNS 설정 후 아래 스크립트로 SSL 자동 발급:

```bash
#!/bin/bash
# setup-auth-ssl.sh

DOMAIN="auth.neuralgrid.kr"
EMAIL="admin@neuralgrid.kr"
SERVER_IP="115.91.5.140"

echo "🔍 Checking DNS..."
if nslookup $DOMAIN | grep -q $SERVER_IP; then
    echo "✅ DNS is configured correctly"
    
    echo "🔐 Generating SSL certificate..."
    sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email $EMAIL
    
    echo "🔄 Reloading Nginx..."
    sudo systemctl reload nginx
    
    echo "✅ Testing HTTPS..."
    curl -I https://$DOMAIN/health
    
    echo "🎉 Setup complete!"
else
    echo "❌ DNS is not configured yet"
    echo "Please add A record: $DOMAIN → $SERVER_IP"
    exit 1
fi
```

---

## 📝 체크리스트

### DNS 설정 전
- [x] PM2 서비스 정상 가동 확인
- [x] Nginx 설정 파일 존재 확인
- [x] 로컬(localhost:3099) 접속 테스트 성공

### DNS 설정 후 해야 할 일
- [ ] Cloudflare에 A 레코드 추가
- [ ] DNS 전파 대기 (5-10분)
- [ ] nslookup으로 DNS 확인
- [ ] SSL 인증서 발급
- [ ] Nginx 재시작
- [ ] HTTPS 접속 테스트
- [ ] 메인 페이지 링크 확인

---

## 🔗 관련 링크

### Cloudflare DNS 관리
- **Dashboard:** https://dash.cloudflare.com/
- **Domain:** neuralgrid.kr
- **DNS Records:** DNS → Records

### 서버 정보
- **IP:** 115.91.5.140
- **SSH:** `ssh azamans@115.91.5.140`
- **Password:** `7009011226119`

### 서비스 정보
- **Service:** auth-service
- **Port:** 3099
- **Process:** PM2 ID 17
- **Health:** http://localhost:3099/health

---

## 💡 참고사항

### 왜 다른 서브도메인은 작동하나요?

다른 서브도메인들은 DNS A 레코드가 이미 설정되어 있기 때문입니다:

```bash
# 예시
$ nslookup mfx.neuralgrid.kr
Server:		8.8.8.8
Address:	8.8.8.8#53

Name:	mfx.neuralgrid.kr
Address: 115.91.5.140  ← DNS 레코드 존재!
```

### market.neuralgrid.kr도 같은 문제

`market.neuralgrid.kr` 역시 DNS A 레코드가 없어서 같은 문제가 있습니다. 동시에 설정하는 것을 권장합니다:

```
Type:    A
Name:    market
Content: 115.91.5.140
Proxy:   ☑ Proxied
```

---

## 📞 지원

문제가 계속되면:
1. DNS 전파 시간 확인 (최대 48시간, 보통 5-10분)
2. Cloudflare 대시보드에서 레코드 재확인
3. `nslookup auth.neuralgrid.kr 8.8.8.8` (Google DNS로 직접 확인)

---

**🎯 요약:**
`auth.neuralgrid.kr`가 작동하지 않는 이유는 **DNS A 레코드가 없기 때문**입니다. Cloudflare에서 A 레코드를 추가하면 5-10분 후 정상 작동합니다!
