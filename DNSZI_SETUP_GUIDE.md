# dnszi.com에서 auth.neuralgrid.kr DNS 설정 가이드

**목표**: auth A 레코드 추가  
**IP**: 115.91.5.140  
**예상 소요 시간**: 15-20분

---

## 📋 **설정 정보**

```
레코드 타입: A
호스트명:    auth
IP 주소:     115.91.5.140
TTL:         3600 (1시간)
```

---

## 🚀 **단계별 가이드**

### **1단계: dnszi.com 접속 및 로그인**

**1. 웹사이트 접속**
- **URL**: https://dnszi.com/
- 브라우저에서 열기

**2. 로그인**
- "로그인" 또는 "Login" 버튼 클릭
- 계정 정보 입력
  - 이메일 또는 사용자 ID
  - 비밀번호

> 💡 **Tip**: 계정 정보가 기억나지 않으면 "비밀번호 찾기" 클릭

---

### **2단계: neuralgrid.kr 도메인 선택**

**1. 대시보드/도메인 목록 확인**
- 로그인 후 자동으로 도메인 목록 표시
- 또는 "My Domains" / "도메인 관리" 메뉴 클릭

**2. neuralgrid.kr 선택**
- 도메인 목록에서 `neuralgrid.kr` 클릭
- "DNS 관리" 또는 "DNS Management" 메뉴로 이동

---

### **3단계: 새 레코드 추가**

**1. 레코드 추가 버튼 찾기**
- "레코드 추가" / "Add Record" / "+" 버튼 클릭

**2. 레코드 정보 입력**

| 항목 | 입력 값 | 설명 |
|------|---------|------|
| **레코드 타입** | **A** | IPv4 주소 레코드 |
| **호스트명** | **auth** | 서브도메인 이름 |
| **IP 주소** | **115.91.5.140** | 서버 IP |
| **TTL** | **3600** | Time To Live (1시간) |

**입력 예시:**
```
Type:  A
Host:  auth
Value: 115.91.5.140
TTL:   3600
```

> ⚠️ **중요**: 
> - 호스트명에 `.neuralgrid.kr`을 붙이지 마세요!
> - `auth`만 입력하면 자동으로 `auth.neuralgrid.kr`이 됩니다

**3. 저장**
- "저장" / "Save" / "추가" 버튼 클릭
- 확인 메시지가 나타나면 "확인" 클릭

---

### **4단계: 설정 확인**

**레코드 목록에서 확인:**
```
auth.neuralgrid.kr    A    115.91.5.140    3600
```

이렇게 표시되면 성공입니다! ✅

---

### **5단계: DNS 전파 대기 (10-15분)**

DNS 레코드가 인터넷 전체에 전파되는 시간입니다.

**대기 중 할 일:**
- ☕ 커피 한 잔
- 📧 이메일 확인
- 🎵 음악 감상

---

### **6단계: DNS 전파 확인**

**방법 1: dig 명령어 (로컬 또는 서버)**
```bash
dig +short auth.neuralgrid.kr @8.8.8.8
```

**예상 결과:**
```
115.91.5.140
```

**방법 2: nslookup 명령어**
```bash
nslookup auth.neuralgrid.kr 8.8.8.8
```

**예상 결과:**
```
Server:  google-public-dns-a.google.com
Address:  8.8.8.8

Name:    auth.neuralgrid.kr
Address:  115.91.5.140
```

**방법 3: 온라인 DNS 조회 도구**
- https://www.whatsmydns.net/
- Domain: `auth.neuralgrid.kr` 입력
- Record Type: `A` 선택
- "Search" 클릭
- 전 세계 DNS 서버에서 전파 상태 확인

---

### **7단계: SSL 인증서 발급**

**DNS 전파가 완료되면 실행:**

```bash
# 서버 접속
ssh azamans@115.91.5.140

# SSL 인증서 발급
sudo certbot certonly --nginx -d auth.neuralgrid.kr \
  --non-interactive --agree-tos -m admin@neuralgrid.kr

# 성공 메시지 확인
# Certificate is saved at: /etc/letsencrypt/live/auth.neuralgrid.kr/fullchain.pem
```

---

### **8단계: Nginx 설정 업데이트**

```bash
# SSL 경로 업데이트
sudo sed -i 's|ssl_certificate .*|ssl_certificate /etc/letsencrypt/live/auth.neuralgrid.kr/fullchain.pem;|' \
  /etc/nginx/sites-available/auth.neuralgrid.kr

sudo sed -i 's|ssl_certificate_key .*|ssl_certificate_key /etc/letsencrypt/live/auth.neuralgrid.kr/privkey.pem;|' \
  /etc/nginx/sites-available/auth.neuralgrid.kr

# Nginx 설정 테스트
sudo nginx -t

# Nginx 리로드
sudo systemctl reload nginx
```

---

### **9단계: 최종 테스트**

**1. Health Check**
```bash
curl -I https://auth.neuralgrid.kr/health
```

**예상 결과:**
```
HTTP/2 200 
server: nginx/1.24.0 (Ubuntu)
content-type: application/json
...
```

**2. 브라우저 테스트**
- 브라우저에서 `https://auth.neuralgrid.kr` 접속
- 인증서 확인 (🔒 자물쇠 아이콘)

**3. 서비스 확인**
```bash
# 서버에서 확인
pm2 list | grep auth
```

---

## ✅ **성공 체크리스트**

- [ ] dnszi.com 로그인 완료
- [ ] neuralgrid.kr 도메인 선택
- [ ] auth A 레코드 추가 (115.91.5.140)
- [ ] 레코드 저장 완료
- [ ] DNS 전파 확인 (10-15분)
- [ ] dig 명령어로 IP 확인 (115.91.5.140)
- [ ] SSL 인증서 발급 완료
- [ ] Nginx 설정 업데이트
- [ ] Nginx 리로드
- [ ] HTTPS 접속 테스트 (200 OK)
- [ ] 브라우저에서 확인

---

## 🔧 **트러블슈팅**

### 문제 1: DNS가 전파되지 않음 (15분 후에도)

**확인 사항:**
```bash
# 여러 DNS 서버로 확인
dig +short auth.neuralgrid.kr @8.8.8.8          # Google DNS
dig +short auth.neuralgrid.kr @1.1.1.1          # Cloudflare DNS
dig +short auth.neuralgrid.kr @ns3.dnszi.com    # dnszi DNS
```

**해결 방법:**
- dnszi.com에서 레코드가 올바르게 저장되었는지 재확인
- TTL 값 확인 (3600 = 1시간)
- 최대 24시간까지 대기 (일반적으로 15분 내)

---

### 문제 2: SSL 인증서 발급 실패

**증상:**
```
Challenge failed for domain auth.neuralgrid.kr
DNS problem: NXDOMAIN
```

**원인:** DNS가 아직 전파되지 않음

**해결:**
1. DNS 전파 재확인:
   ```bash
   dig +short auth.neuralgrid.kr @8.8.8.8
   ```
2. 결과가 `115.91.5.140`이 나올 때까지 대기
3. 다시 certbot 실행

---

### 문제 3: Nginx 503 Service Unavailable

**증상:**
```bash
curl -I https://auth.neuralgrid.kr
# HTTP/2 503
```

**원인:** Auth 서비스가 실행되지 않음

**해결:**
```bash
# 서비스 상태 확인
pm2 list | grep auth

# 서비스 재시작
pm2 restart auth-service

# 로그 확인
pm2 logs auth-service --lines 50
```

---

### 문제 4: dnszi.com 계정 로그인 실패

**해결 방법:**

**1. 비밀번호 재설정**
- dnszi.com 로그인 페이지
- "비밀번호 찾기" 클릭
- 도메인 등록 이메일 입력

**2. 계정 복구**
- 도메인 등록 이메일 확인
- dnszi.com으로부터 받은 이메일 검색

**3. 고객 지원 연락**
- dnszi.com 고객센터 연락
- 도메인 소유권 증명 (등록 이메일, WHOIS 정보)

---

## 📞 **도움이 필요하면**

각 단계를 진행하면서 문제가 발생하면 알려주세요!

**현재 상태를 알려주시면:**
- 어느 단계에서 막혔는지
- 에러 메시지가 무엇인지
- 스크린샷 (선택사항)

바로 도와드리겠습니다! 🚀

---

## 🎯 **완료 후 확인 사항**

### **최종 확인 명령어**
```bash
# DNS 확인
dig +short auth.neuralgrid.kr @8.8.8.8
# 결과: 115.91.5.140

# HTTPS 확인
curl -I https://auth.neuralgrid.kr/health
# 결과: HTTP/2 200

# SSL 인증서 확인
openssl s_client -connect auth.neuralgrid.kr:443 -servername auth.neuralgrid.kr < /dev/null 2>&1 | grep 'Verify return code'
# 결과: Verify return code: 0 (ok)
```

---

## 🎉 **성공하면**

auth.neuralgrid.kr이 완전히 활성화됩니다!

**운영 중인 서비스 (8/8):**
- ✅ neuralgrid.kr (메인)
- ✅ music.neuralgrid.kr (음악 생성)
- ✅ monitor.neuralgrid.kr (모니터링)
- ✅ n8n.neuralgrid.kr (자동화)
- ✅ market.neuralgrid.kr (쿠팡쇼츠)
- ✅ bn-shop.neuralgrid.kr (블로그 쇼츠)
- ✅ mfx.neuralgrid.kr (MediaFX)
- ✅ **auth.neuralgrid.kr (인증 서비스)** ⭐ NEW

**NeuralGrid 플랫폼 100% 완성!** 🎊

---

**작성일**: 2025-12-15  
**업데이트**: DNS 설정 가이드 최종판  
**담당자**: Genspark AI Assistant
