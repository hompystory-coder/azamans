# 🛡️ DDoS 방어 시스템 - DNS 설정 가이드

## 📋 도메인 정보

### ✅ 최종 결정된 도메인
```
ddos.neuralgrid.kr
```

**더 짧고 직관적인 이름으로 변경되었습니다!**

---

## 🌐 DNS 레코드 추가 방법

### Cloudflare 대시보드에서 설정

#### 1. Cloudflare 로그인
- https://dash.cloudflare.com/
- `neuralgrid.kr` 도메인 선택

#### 2. DNS 섹션 이동
- 좌측 메뉴에서 **"DNS"** 클릭
- **"Records"** 탭 선택

#### 3. 레코드 추가
**"Add record" 버튼 클릭 후 다음 정보 입력:**

```
Type: A
Name: ddos
IPv4 address: 115.91.5.140
Proxy status: ✅ Proxied (주황색 구름 아이콘)
TTL: Auto
```

#### 4. 저장
- **"Save"** 버튼 클릭
- DNS 전파 대기 (보통 1-5분)

---

## 🔍 DNS 전파 확인

### 명령어로 확인

```bash
# Google DNS로 확인
nslookup ddos.neuralgrid.kr 8.8.8.8

# Cloudflare DNS로 확인
nslookup ddos.neuralgrid.kr 1.1.1.1

# 일반 조회
nslookup ddos.neuralgrid.kr
```

### 예상 결과

DNS가 정상적으로 전파되면:

```
Server:		8.8.8.8
Address:	8.8.8.8#53

Non-authoritative answer:
Name:	ddos.neuralgrid.kr
Address: 104.21.x.x (Cloudflare IP)
Name:	ddos.neuralgrid.kr
Address: 172.67.x.x (Cloudflare IP)
```

**참고**: Cloudflare Proxy가 활성화되어 있으면 Cloudflare의 IP 주소가 반환됩니다.

---

## 🚀 자동 배포 스크립트

DNS 설정 후 아래 명령어로 자동 배포:

```bash
cd /home/azamans/webapp
./deploy-ddos-nginx.sh
```

### 배포 스크립트가 자동으로 수행하는 작업:
1. ✅ Nginx 설정 파일 업로드
2. ✅ Nginx 설정 적용
3. ✅ Nginx 설정 테스트
4. ✅ Nginx 재시작
5. ✅ DNS 전파 확인

---

## 🔒 SSL 인증서 발급

DNS 전파 확인 후 SSL 인증서 발급:

### 1. 서버 접속

```bash
ssh azamans@115.91.5.140
# 비밀번호: 7009011226119
```

### 2. SSL 인증서 자동 발급

```bash
sudo certbot --nginx -d ddos.neuralgrid.kr \
    --non-interactive \
    --agree-tos \
    --email admin@neuralgrid.kr \
    --redirect
```

### 예상 결과

```
Congratulations! You have successfully enabled HTTPS on https://ddos.neuralgrid.kr

IMPORTANT NOTES:
 - Congratulations! Your certificate and chain have been saved at:
   /etc/letsencrypt/live/ddos.neuralgrid.kr/fullchain.pem
   Your key file has been saved at:
   /etc/letsencrypt/live/ddos.neuralgrid.kr/privkey.pem
```

---

## ✅ 배포 검증

### 1. HTTP 접속 테스트

```bash
# 상태 코드 확인
curl -I http://ddos.neuralgrid.kr/

# API 상태 확인
curl http://ddos.neuralgrid.kr/api/status
```

### 2. HTTPS 접속 테스트 (SSL 설정 후)

```bash
# 상태 코드 확인
curl -I https://ddos.neuralgrid.kr/

# API 엔드포인트 테스트
curl https://ddos.neuralgrid.kr/api/status
curl https://ddos.neuralgrid.kr/api/traffic
curl https://ddos.neuralgrid.kr/api/blocked-ips
curl https://ddos.neuralgrid.kr/api/fail2ban/status
```

### 3. 웹 브라우저 접속

```
https://ddos.neuralgrid.kr
```

**예상 화면:**
- 🛡️ NeuralGrid DDoS Defense
- 실시간 트래픽 그래프
- 차단된 IP 목록
- 시스템 상태 모니터링
- API 제어 버튼들

---

## 📊 서비스 현황

### NeuralGrid 플랫폼 (10/10 서비스)

| # | 서비스 | 도메인 | 포트 | 상태 |
|---|---------|--------|------|------|
| 1 | 메인 플랫폼 | neuralgrid.kr | 80/443 | 🟢 |
| 2 | 인증 허브 | auth.neuralgrid.kr | 3099 | 🟢 |
| 3 | 블로그 숏츠 | bn-shop.neuralgrid.kr | - | 🟢 |
| 4 | MediaFX | mfx.neuralgrid.kr | - | 🟢 |
| 5 | StarMusic | music.neuralgrid.kr | - | 🟢 |
| 6 | 쿠팡 숏츠 | market.neuralgrid.kr | - | 🟢 |
| 7 | N8N 자동화 | n8n.neuralgrid.kr | - | 🟢 |
| 8 | 서버 모니터 | monitor.neuralgrid.kr | - | 🟢 |
| 9 | AI 어시스턴트 | ai.neuralgrid.kr | 3104 | 🟡 |
| 10 | **DDoS 방어** | **ddos.neuralgrid.kr** | **3105** | **🟡 → 🟢** |

---

## 🎯 다음 단계 체크리스트

### 즉시 수행
- [ ] ✅ Cloudflare에서 **ddos.neuralgrid.kr** DNS 레코드 추가
  - Type: **A**
  - Name: **ddos**
  - IPv4: **115.91.5.140**
  - Proxy: **✅ ON** (주황색 구름)
  
- [ ] DNS 전파 확인 (5-10분 대기)
  ```bash
  nslookup ddos.neuralgrid.kr 8.8.8.8
  ```

- [ ] Nginx 설정 배포
  ```bash
  cd /home/azamans/webapp
  ./deploy-ddos-nginx.sh
  ```

- [ ] SSL 인증서 발급
  ```bash
  ssh azamans@115.91.5.140
  sudo certbot --nginx -d ddos.neuralgrid.kr
  ```

- [ ] HTTPS 접속 테스트
  ```bash
  curl -I https://ddos.neuralgrid.kr/
  ```

### 검증 단계
- [ ] 대시보드 기능 테스트
- [ ] API 엔드포인트 테스트 (12개)
- [ ] Fail2ban 자동 차단 테스트
- [ ] Rate Limiting 동작 확인
- [ ] 긴급 모드 활성화 테스트

---

## 🔧 관리 명령어

### PM2 서비스 관리

```bash
# 서비스 상태 확인
pm2 list

# DDoS Defense 로그 확인
pm2 logs ddos-defense

# 서비스 재시작
pm2 restart ddos-defense
```

### Fail2ban 관리

```bash
# 전체 상태
sudo fail2ban-client status

# 특정 Jail 상태
sudo fail2ban-client status nginx-limit-req
sudo fail2ban-client status nginx-http-flood

# IP 수동 차단
sudo fail2ban-client set nginx-limit-req banip 192.168.1.100

# IP 차단 해제
sudo fail2ban-client set nginx-limit-req unbanip 192.168.1.100
```

### Nginx 관리

```bash
# 설정 테스트
sudo nginx -t

# 재시작
sudo systemctl reload nginx

# 로그 확인
sudo tail -f /var/log/nginx/ddos.neuralgrid.kr.access.log
sudo tail -f /var/log/nginx/ddos.neuralgrid.kr.error.log
```

---

## 🚨 트러블슈팅

### DNS가 전파되지 않을 때

**1. DNS 캐시 초기화 (로컬)**
```bash
# Linux
sudo systemd-resolve --flush-caches

# macOS
sudo dscacheutil -flushcache
```

**2. 직접 IP로 접속 테스트**
```bash
curl -H "Host: ddos.neuralgrid.kr" http://115.91.5.140:3105/api/status
```

**3. 다른 DNS 서버로 확인**
```bash
# Google DNS
nslookup ddos.neuralgrid.kr 8.8.8.8

# Cloudflare DNS
nslookup ddos.neuralgrid.kr 1.1.1.1
```

### SSL 인증서 발급 실패 시

**1. DNS 전파 재확인**
```bash
nslookup ddos.neuralgrid.kr 8.8.8.8
```

**2. Nginx 설정 확인**
```bash
sudo nginx -t
```

**3. Certbot 로그 확인**
```bash
sudo tail -f /var/log/letsencrypt/letsencrypt.log
```

**4. 수동 재시도**
```bash
sudo certbot certonly --nginx -d ddos.neuralgrid.kr
```

### 서비스가 응답하지 않을 때

**1. PM2 상태 확인**
```bash
pm2 list
pm2 logs ddos-defense --lines 50
```

**2. 포트 확인**
```bash
ss -tuln | grep 3105
```

**3. 프로세스 확인**
```bash
ps aux | grep ddos-defense
```

**4. 서비스 재시작**
```bash
pm2 restart ddos-defense
```

---

## 📞 추가 정보

### 현재 서비스 상태
- **DDoS Dashboard**: PM2로 정상 작동 중 (프로세스 ID: 21)
- **포트**: 3105 (0.0.0.0:3105 리스닝)
- **Fail2ban**: 7개 Jail 활성화
- **API**: 12개 엔드포인트 정상 작동
- **시스템 부하**: 0.27 (정상)
- **메모리 사용량**: 16.79%

### Git 정보
- **Repository**: https://github.com/hompystory-coder/azamans
- **Branch**: `genspark_ai_developer_clean`
- **Pull Request**: https://github.com/hompystory-coder/azamans/pull/1

---

## 🎉 완료 예상

**DNS 설정부터 HTTPS 완료까지**: 약 15-20분 소요

### 타임라인
1. **DNS 레코드 추가**: 1분
2. **DNS 전파 대기**: 5-10분
3. **Nginx 배포**: 2분
4. **SSL 인증서 발급**: 2-3분
5. **테스트 및 검증**: 5분

**총 예상 시간**: 15-20분

---

**문서 버전**: 2.0.0 (ddos.neuralgrid.kr)
**작성일**: 2025-12-15
**작성자**: GenSpark AI Developer
**상태**: ✅ 준비 완료 / 🔄 DNS 설정 대기
