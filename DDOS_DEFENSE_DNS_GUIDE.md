# 🛡️ DDoS 방어 시스템 - DNS 설정 가이드

## 📋 현재 상태

### ✅ 완료된 작업
- **Fail2ban**: 7개 Jail 활성화 (sshd, nginx-http-flood, nginx-limit-req, nginx-404, nginx-bad-bot, nginx-slowloris, neuralgrid-auth)
- **DDoS Dashboard**: PM2로 정상 작동 중 (프로세스 ID: 21)
- **포트 리스닝**: 3105 포트 정상 오픈 (0.0.0.0:3105)
- **API 테스트**: 정상 응답 확인
  ```json
  {
    "timestamp": "2025-12-15T13:48:26.203Z",
    "uptime": "13:48:26 up 11 days, 4:21, 62 users, load average: 0.27, 0.32, 0.32",
    "load": 0.27,
    "memory": 16.79,
    "status": "normal"
  }
  ```
- **Nginx 프록시**: 설정 완료 (HTTP 전용, HTTPS는 DNS 후 설정)
- **Git 커밋**: 완료 (commit: 0e41a14)
- **Pull Request**: 업데이트 완료

---

## 🌐 DNS 설정 (필수)

### Cloudflare DNS 레코드 추가

#### defense.neuralgrid.kr

**⚠️ 이 DNS 레코드를 추가해주세요:**

```
Type: A
Name: defense
IPv4 Address: 115.91.5.140
Proxy Status: ✅ Proxied (주황색 구름 아이콘)
TTL: Auto
```

### Cloudflare 대시보드 설정 순서

1. **Cloudflare 로그인**
   - https://dash.cloudflare.com/
   - neuralgrid.kr 도메인 선택

2. **DNS 레코드 추가**
   - 좌측 메뉴에서 "DNS" 클릭
   - "Add record" 버튼 클릭
   
3. **레코드 정보 입력**
   ```
   Type: A
   Name: defense
   IPv4 address: 115.91.5.140
   Proxy status: Proxied (주황색 구름)
   TTL: Auto
   ```

4. **저장**
   - "Save" 버튼 클릭
   - DNS 전파 대기 (보통 1-5분)

---

## 🔍 DNS 전파 확인

### 명령어로 확인

```bash
# Google DNS로 확인
nslookup defense.neuralgrid.kr 8.8.8.8

# Cloudflare DNS로 확인
nslookup defense.neuralgrid.kr 1.1.1.1

# 일반 조회
nslookup defense.neuralgrid.kr
```

### 예상 결과

```
Server:		8.8.8.8
Address:	8.8.8.8#53

Non-authoritative answer:
Name:	defense.neuralgrid.kr
Address: 104.21.x.x (Cloudflare IP)
Name:	defense.neuralgrid.kr
Address: 172.67.x.x (Cloudflare IP)
```

**참고**: Cloudflare Proxy가 활성화되어 있으면 Cloudflare의 IP가 반환됩니다 (104.21.x.x, 172.67.x.x 등).

---

## 🔒 SSL 인증서 설정 (DNS 전파 후)

### 1. 서버 접속

```bash
ssh azamans@115.91.5.140
# 비밀번호: 7009011226119
```

### 2. Nginx 설정 파일 업로드

DDoS Defense용 Nginx 설정 파일 생성:

```bash
sudo tee /etc/nginx/sites-available/defense.neuralgrid.kr.conf > /dev/null <<'EOF'
server {
    listen 80;
    server_name defense.neuralgrid.kr;
    
    # Rate Limiting
    limit_req zone=general burst=20 nodelay;
    limit_conn addr 10;
    
    # Logging
    access_log /var/log/nginx/defense.neuralgrid.kr.access.log;
    error_log /var/log/nginx/defense.neuralgrid.kr.error.log;
    
    # Proxy to Node.js
    location / {
        proxy_pass http://localhost:3105;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Timeout 설정
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
    
    # API 엔드포인트
    location /api/ {
        limit_req zone=api burst=50 nodelay;
        
        proxy_pass http://localhost:3105;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Health Check
    location /health {
        proxy_pass http://localhost:3105/health;
        access_log off;
    }
}
EOF
```

### 3. Nginx 설정 활성화

```bash
# Symlink 생성
sudo ln -sf /etc/nginx/sites-available/defense.neuralgrid.kr.conf /etc/nginx/sites-enabled/

# 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl reload nginx
```

### 4. SSL 인증서 발급

```bash
sudo certbot --nginx -d defense.neuralgrid.kr \
    --non-interactive \
    --agree-tos \
    --email admin@neuralgrid.kr \
    --redirect
```

### 예상 결과

```
Congratulations! You have successfully enabled HTTPS on https://defense.neuralgrid.kr
```

---

## ✅ 배포 검증

### 1. HTTP 접속 테스트

```bash
# HTTP 상태 확인
curl -I http://defense.neuralgrid.kr/

# API 테스트
curl http://defense.neuralgrid.kr/api/status
```

### 2. HTTPS 접속 테스트 (SSL 설정 후)

```bash
# HTTPS 상태 확인
curl -I https://defense.neuralgrid.kr/

# API 테스트
curl https://defense.neuralgrid.kr/api/status

# Fail2ban 상태
curl https://defense.neuralgrid.kr/api/fail2ban/status

# 차단된 IP 목록
curl https://defense.neuralgrid.kr/api/blocked-ips

# 트래픽 통계
curl https://defense.neuralgrid.kr/api/traffic
```

### 3. 대시보드 접속

브라우저에서 접속:
```
https://defense.neuralgrid.kr
```

예상 화면:
- 🛡️ NeuralGrid DDoS Defense
- 실시간 트래픽 그래프
- 차단된 IP 목록
- 시스템 상태

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
| 10 | **DDoS 방어** | **defense.neuralgrid.kr** | **3105** | **🟡 → 🟢** |

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

# 서비스 중지
pm2 stop ddos-defense

# 서비스 시작
pm2 start ddos-defense
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

# 차단된 IP 목록
sudo fail2ban-client banned
```

### Nginx 관리

```bash
# 설정 테스트
sudo nginx -t

# 재시작
sudo systemctl reload nginx
sudo systemctl restart nginx

# 로그 확인
sudo tail -f /var/log/nginx/defense.neuralgrid.kr.access.log
sudo tail -f /var/log/nginx/defense.neuralgrid.kr.error.log
```

---

## 🚨 트러블슈팅

### DNS가 전파되지 않을 때

```bash
# 캐시 초기화 (로컬)
sudo systemd-resolve --flush-caches

# 또는
sudo dscacheutil -flushcache

# 직접 IP로 접속 테스트
curl -H "Host: defense.neuralgrid.kr" http://115.91.5.140:3105/api/status
```

### SSL 인증서 발급 실패 시

```bash
# DNS 전파 재확인
nslookup defense.neuralgrid.kr 8.8.8.8

# Nginx 설정 확인
sudo nginx -t

# Certbot 로그 확인
sudo tail -f /var/log/letsencrypt/letsencrypt.log

# 수동 재시도
sudo certbot certonly --nginx -d defense.neuralgrid.kr
```

### 서비스가 응답하지 않을 때

```bash
# PM2 상태 확인
pm2 list
pm2 logs ddos-defense --lines 50

# 포트 확인
ss -tuln | grep 3105

# 프로세스 확인
ps aux | grep ddos-defense

# 서비스 재시작
pm2 restart ddos-defense
```

---

## 📞 Git & Pull Request 정보

### Git 정보
- **Repository**: https://github.com/hompystory-coder/azamans
- **Branch**: `genspark_ai_developer_clean`
- **Latest Commit**: `0e41a14` - "feat: Add comprehensive DDoS Defense System (defense.neuralgrid.kr)"

### Pull Request
- **URL**: https://github.com/hompystory-coder/azamans/pull/1
- **Title**: "🛡️ feat: Add DDoS Defense System + AI Assistant"
- **Status**: Open, Ready for Review
- **Changed Files**: 15개
- **Code Lines**: 6,000+ 줄
- **Documentation**: 80,000+ 자

---

## 🎯 다음 단계 체크리스트

### 즉시 수행
- [ ] Cloudflare에서 defense.neuralgrid.kr DNS 레코드 추가
- [ ] DNS 전파 확인 (5-10분 대기)
- [ ] HTTP 접속 테스트
- [ ] SSL 인증서 발급
- [ ] HTTPS 접속 테스트

### 검증 단계
- [ ] 대시보드 기능 테스트
- [ ] API 엔드포인트 테스트 (12개 모두)
- [ ] Fail2ban 자동 차단 테스트
- [ ] Rate Limiting 동작 확인
- [ ] 긴급 모드 테스트

### 문서화
- [ ] 운영 매뉴얼 작성
- [ ] 대시보드 사용 가이드
- [ ] 공격 대응 절차서
- [ ] 주간/월간 보고서 템플릿

---

## 🎉 완료 예상

**DNS 설정부터 HTTPS 완료까지**: 약 15-20분 소요

### 타임라인
1. **DNS 레코드 추가**: 1분
2. **DNS 전파 대기**: 5-10분
3. **Nginx 설정**: 2분
4. **SSL 인증서 발급**: 2-3분
5. **테스트 및 검증**: 5분

**총 예상 시간**: 15-20분

---

**문서 버전**: 1.0.0
**작성일**: 2025-12-15
**작성자**: GenSpark AI Developer
**상태**: ✅ 배포 완료 / 🔄 DNS 설정 대기
