# Cloudflare DNS 설정 가이드
**대상**: auth.neuralgrid.kr  
**서버 IP**: 115.91.5.140

---

## 🚀 방법 1: 자동 스크립트 (추천)

### 1단계: Cloudflare API 토큰 발급

**1. Cloudflare 대시보드 접속**
- URL: https://dash.cloudflare.com/profile/api-tokens

**2. API 토큰 생성**
- "Create Token" 버튼 클릭
- "Edit zone DNS" 템플릿 선택
- **Permissions**:
  - Zone / DNS / Edit
- **Zone Resources**:
  - Include / Specific zone / neuralgrid.kr
- "Continue to summary" → "Create Token"

**3. 토큰 복사**
- 생성된 API 토큰을 복사 (한 번만 표시됩니다!)

### 2단계: 스크립트 실행

```bash
# 자동 설정 스크립트 실행
cd /home/azamans/webapp
./setup_auth_dns.sh <YOUR_CLOUDFLARE_API_TOKEN>
```

**예시:**
```bash
./setup_auth_dns.sh abc123def456ghi789jkl012mno345pqr678stu901
```

### 3단계: SSL 인증서 발급 (서버에서)

스크립트가 완료되면 출력되는 명령어를 서버에서 실행:

```bash
ssh azamans@115.91.5.140

# SSL 인증서 발급
sudo certbot certonly --nginx -d auth.neuralgrid.kr \
  --non-interactive --agree-tos -m admin@neuralgrid.kr

# Nginx 설정 업데이트 및 리로드
sudo nginx -t && sudo systemctl reload nginx

# 테스트
curl -I https://auth.neuralgrid.kr/health
```

---

## 🖱️ 방법 2: Cloudflare 대시보드 (수동)

### 1단계: Cloudflare 대시보드 접속

1. **URL**: https://dash.cloudflare.com/
2. **neuralgrid.kr** 도메인 선택
3. 왼쪽 메뉴에서 **DNS** → **Records** 클릭

### 2단계: A 레코드 추가

**"Add record" 버튼 클릭 후 입력:**

| 항목 | 값 | 설명 |
|------|-----|------|
| Type | **A** | DNS 레코드 타입 |
| Name | **auth** | 서브도메인 이름 |
| IPv4 address | **115.91.5.140** | 서버 IP 주소 |
| Proxy status | **✅ Proxied** | 주황색 클라우드 ON |
| TTL | **Auto** | 자동 |

**"Save" 버튼 클릭**

### 3단계: DNS 전파 확인 (5-15분 대기)

```bash
# DNS 확인 (로컬 또는 서버에서)
dig +short auth.neuralgrid.kr @8.8.8.8

# 결과 예시:
# 104.21.xxx.xxx (Cloudflare 프록시 IP)
# 또는
# 115.91.5.140 (직접 IP)
```

### 4단계: SSL 인증서 발급

**서버 (115.91.5.140)에 접속:**
```bash
ssh azamans@115.91.5.140
```

**SSL 인증서 발급:**
```bash
sudo certbot certonly --nginx \
  -d auth.neuralgrid.kr \
  --non-interactive \
  --agree-tos \
  -m admin@neuralgrid.kr
```

**성공 메시지 예시:**
```
Successfully received certificate.
Certificate is saved at: /etc/letsencrypt/live/auth.neuralgrid.kr/fullchain.pem
Key is saved at:         /etc/letsencrypt/live/auth.neuralgrid.kr/privkey.pem
This certificate expires on 2026-03-15.
```

### 5단계: Nginx 설정 업데이트

**Nginx 설정 파일 수정:**
```bash
sudo nano /etc/nginx/sites-available/auth.neuralgrid.kr
```

**SSL 경로 업데이트 (자동):**
```bash
sudo sed -i 's|ssl_certificate.*|ssl_certificate /etc/letsencrypt/live/auth.neuralgrid.kr/fullchain.pem;|' \
  /etc/nginx/sites-available/auth.neuralgrid.kr

sudo sed -i 's|ssl_certificate_key.*|ssl_certificate_key /etc/letsencrypt/live/auth.neuralgrid.kr/privkey.pem;|' \
  /etc/nginx/sites-available/auth.neuralgrid.kr
```

**Nginx 테스트 및 리로드:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 6단계: 최종 테스트

```bash
# Health check
curl -I https://auth.neuralgrid.kr/health

# 예상 출력:
# HTTP/2 200 
# server: nginx/1.24.0 (Ubuntu)
# ...
```

---

## 📊 현재 설정 현황

### ✅ 이미 완료된 것들

- ✅ Auth Service (port 3099): PM2로 실행 중
- ✅ Nginx 설정: `/etc/nginx/sites-available/auth.neuralgrid.kr` 준비됨
- ✅ Systemd 서비스: 자동 시작 설정됨

### ⚠️ 필요한 작업

- ⚠️ **Cloudflare DNS A 레코드**: auth → 115.91.5.140
- ⚠️ **SSL 인증서**: Let's Encrypt 발급 (DNS 후)
- ⚠️ **Nginx SSL 경로**: 새 인증서 경로로 업데이트

---

## 🔍 트러블슈팅

### 문제 1: DNS가 전파되지 않음

**증상:**
```bash
dig +short auth.neuralgrid.kr @8.8.8.8
# (응답 없음)
```

**해결:**
1. Cloudflare 대시보드에서 A 레코드가 추가되었는지 확인
2. 5-15분 더 대기
3. 다른 DNS 서버로 확인: `dig +short auth.neuralgrid.kr @1.1.1.1`

### 문제 2: SSL 인증서 발급 실패

**증상:**
```
Challenge failed for domain auth.neuralgrid.kr
DNS problem: NXDOMAIN looking up A for auth.neuralgrid.kr
```

**해결:**
1. DNS가 정상적으로 전파되었는지 확인:
   ```bash
   dig +short auth.neuralgrid.kr @8.8.8.8
   ```
2. DNS가 전파되지 않았다면 더 대기 (최대 24시간)
3. Cloudflare 프록시가 켜져 있는지 확인 (주황색 클라우드)

### 문제 3: Nginx 503 Service Unavailable

**증상:**
```bash
curl -I https://auth.neuralgrid.kr
# HTTP/2 503
```

**해결:**
1. Auth 서비스가 실행 중인지 확인:
   ```bash
   pm2 list | grep auth
   ```
2. 포트 3099가 리스닝 중인지 확인:
   ```bash
   sudo lsof -i :3099
   ```
3. 서비스 재시작:
   ```bash
   pm2 restart auth-service
   ```

### 문제 4: Cloudflare API 오류

**증상:**
```
Error: Could not get Zone ID
```

**해결:**
1. API 토큰 권한 확인:
   - Zone / DNS / Edit 권한이 있는지 확인
2. Zone ID 수동 입력:
   - Cloudflare 대시보드 → neuralgrid.kr → Overview → Zone ID 복사
   ```bash
   ./setup_auth_dns.sh <API_TOKEN> <ZONE_ID>
   ```

---

## 📝 체크리스트

DNS 설정 완료 전:
- [ ] Cloudflare API 토큰 발급 또는 대시보드 접속 준비
- [ ] Auth 서비스가 port 3099에서 실행 중인지 확인
- [ ] Nginx 설정 파일 존재 확인

DNS 설정 후:
- [ ] DNS 전파 확인 (dig 명령어)
- [ ] SSL 인증서 발급
- [ ] Nginx 설정 업데이트
- [ ] Nginx 리로드
- [ ] HTTPS 테스트 (curl -I https://auth.neuralgrid.kr/health)
- [ ] 브라우저에서 접속 테스트

---

## 🎯 예상 소요 시간

| 단계 | 소요 시간 |
|------|-----------|
| 1. Cloudflare API 토큰 발급 | 2분 |
| 2. DNS 레코드 추가 (자동 스크립트) | 1분 |
| 3. DNS 전파 대기 | 5-15분 |
| 4. SSL 인증서 발급 | 1분 |
| 5. Nginx 설정 및 리로드 | 1분 |
| 6. 테스트 | 1분 |
| **총 예상 시간** | **10-20분** |

---

## 🚀 빠른 시작 (요약)

**방법 A: 자동 (추천)**
```bash
# 1. API 토큰 발급: https://dash.cloudflare.com/profile/api-tokens
# 2. 스크립트 실행
cd /home/azamans/webapp
./setup_auth_dns.sh <YOUR_API_TOKEN>

# 3. 서버에서 SSL 발급 (5-15분 후)
ssh azamans@115.91.5.140
sudo certbot certonly --nginx -d auth.neuralgrid.kr --non-interactive --agree-tos -m admin@neuralgrid.kr
sudo nginx -t && sudo systemctl reload nginx
```

**방법 B: 수동**
1. Cloudflare 대시보드 → DNS → Add record
2. Type: A, Name: auth, IP: 115.91.5.140, Proxy: ON
3. 5-15분 대기
4. 서버에서 SSL 발급 (위와 동일)

---

**문의사항이나 오류 발생 시 위 트러블슈팅 섹션을 참고하세요!** 🔧
