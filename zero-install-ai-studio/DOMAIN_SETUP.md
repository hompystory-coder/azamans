# 🌐 도메인 설정 가이드: ai-studio.neuralgrid.kr

## 현재 상태
- ✅ DNS: ai-studio.neuralgrid.kr → 115.91.5.140 (정상)
- ✅ 서버: localhost:3001 (PM2 온라인)
- ❌ Nginx: 설정 필요
- ❌ SSL: 인증서 발급 필요

## 🚀 빠른 설정 (자동 - 권장)

터미널에서 한 줄 실행:

```bash
/tmp/setup-ai-studio-domain.sh
```

이 스크립트가 자동으로:
1. ✅ Nginx 설정 파일 생성
2. ✅ Symbolic link 생성
3. ✅ Nginx 재시작
4. ✅ Let's Encrypt SSL 인증서 발급

을 완료합니다!

---

## 📝 수동 설정 (단계별)

자동 스크립트가 실행되지 않는 경우:

### 1단계: Nginx 설정 복사
```bash
sudo cp /tmp/ai-studio.neuralgrid.kr.conf /etc/nginx/sites-available/ai-studio.neuralgrid.kr.conf
```

### 2단계: Symbolic link 생성
```bash
sudo ln -sf /etc/nginx/sites-available/ai-studio.neuralgrid.kr.conf /etc/nginx/sites-enabled/
```

### 3단계: Nginx 설정 테스트
```bash
sudo nginx -t
```

### 4단계: Nginx 재시작
```bash
sudo systemctl reload nginx
```

### 5단계: SSL 인증서 발급
```bash
sudo certbot --nginx -d ai-studio.neuralgrid.kr
```

---

## ✅ 설정 완료 후

### 접속 URL
- **HTTP**: http://ai-studio.neuralgrid.kr
- **HTTPS**: https://ai-studio.neuralgrid.kr (SSL 설정 후)

### 상태 확인
```bash
# Nginx 상태
sudo systemctl status nginx

# SSL 인증서 확인
sudo certbot certificates | grep ai-studio

# 도메인 접속 테스트
curl -I https://ai-studio.neuralgrid.kr/
```

### 로그 확인
```bash
# Access log
sudo tail -f /var/log/nginx/ai-studio.neuralgrid.kr.access.log

# Error log
sudo tail -f /var/log/nginx/ai-studio.neuralgrid.kr.error.log

# PM2 log
pm2 logs ai-studio
```

---

## 🔥 문제 해결

### 502 Bad Gateway
```bash
# PM2 서버 재시작
pm2 restart ai-studio

# 포트 확인
netstat -tulpn | grep 3001
```

### SSL 인증서 오류
```bash
# 인증서 갱신
sudo certbot renew

# 강제 재발급
sudo certbot --nginx -d ai-studio.neuralgrid.kr --force-renewal
```

### Nginx 설정 오류
```bash
# 설정 파일 확인
sudo cat /etc/nginx/sites-available/ai-studio.neuralgrid.kr.conf

# 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl restart nginx
```

---

## 📂 생성된 파일

프로젝트 루트에서:
- `/tmp/ai-studio.neuralgrid.kr.conf` - Nginx 설정
- `/tmp/setup-ai-studio-domain.sh` - 자동 설정 스크립트
- `/tmp/DOMAIN_SETUP_README.md` - 상세 가이드

---

## 🎯 Nginx 설정 내용

```nginx
server {
    server_name ai-studio.neuralgrid.kr;
    
    location / {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        # ... (WebSocket, HMR 지원)
    }
}
```

포트 3001에서 실행되는 Next.js 앱을 프록시합니다.

---

## 🆘 지원

문제가 계속되면:
1. 📧 Nginx 로그 확인: `sudo tail -100 /var/log/nginx/error.log`
2. 📧 PM2 로그 확인: `pm2 logs ai-studio --lines 100`
3. 📧 방화벽 확인: `sudo ufw status`
4. 📧 포트 확인: `sudo netstat -tulpn | grep -E '80|443|3001'`

---

**지금 바로 시작하세요:**

```bash
/tmp/setup-ai-studio-domain.sh
```

설정 완료 후 https://ai-studio.neuralgrid.kr 로 접속하세요! 🚀
