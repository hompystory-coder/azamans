# 🌐 서브도메인 배포 가이드

## 📋 개요

YouTube Trend Analyzer를 서브도메인으로 배포하는 가이드입니다.

**서브도메인**: `youtube-trend.neuralgrid.app`

## 🚀 빠른 설치 (자동)

### 1️⃣ 자동 설치 스크립트 실행

```bash
cd /home/azamans/webapp/youtube-trend-analyzer
sudo bash setup-youtube-trend.sh
```

이 스크립트는 자동으로:
- ✅ Node.js 설치 확인
- ✅ 프로젝트 의존성 설치
- ✅ Systemd 서비스 설정
- ✅ Nginx 리버스 프록시 설정
- ✅ 서비스 시작 및 활성화

### 2️⃣ API 키 설정

```bash
sudo nano /home/azamans/webapp/youtube-trend-analyzer/backend/.env
```

다음 내용을 수정:
```env
YOUTUBE_API_KEY=여기에_실제_API_키_입력
```

저장 후 백엔드 재시작:
```bash
sudo systemctl restart youtube-trend-backend
```

### 3️⃣ 접속

브라우저에서 접속:
```
http://youtube-trend.neuralgrid.app
```

---

## 🔧 수동 설치 (상세)

자동 설치 스크립트를 사용하지 않는 경우:

### 1. 의존성 설치

```bash
# 백엔드
cd /home/azamans/webapp/youtube-trend-analyzer/backend
npm install

# 프론트엔드
cd /home/azamans/webapp/youtube-trend-analyzer/frontend
npm install
```

### 2. 환경 변수 설정

```bash
cd /home/azamans/webapp/youtube-trend-analyzer/backend
cp .env.example .env
nano .env
```

`.env` 파일 수정:
```env
PORT=5000
YOUTUBE_API_KEY=your_youtube_api_key_here
AUTO_SEARCH_ENABLED=true
SEARCH_TIME=06:00
```

### 3. Systemd 서비스 설정

**백엔드 서비스:**
```bash
sudo cp youtube-trend-backend.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable youtube-trend-backend
sudo systemctl start youtube-trend-backend
```

**프론트엔드 서비스:**
```bash
sudo cp youtube-trend-frontend.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable youtube-trend-frontend
sudo systemctl start youtube-trend-frontend
```

### 4. Nginx 설정

```bash
# Nginx 설정 파일 복사
sudo cp nginx-config.conf /etc/nginx/sites-available/youtube-trend

# 심볼릭 링크 생성
sudo ln -s /etc/nginx/sites-available/youtube-trend /etc/nginx/sites-enabled/

# 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl restart nginx
```

### 5. 방화벽 설정 (선택사항)

```bash
sudo ufw allow 'Nginx Full'
```

---

## 🔐 SSL 인증서 설정 (HTTPS)

### Certbot으로 무료 SSL 인증서 발급

```bash
# Certbot 설치
sudo apt-get update
sudo apt-get install -y certbot python3-certbot-nginx

# SSL 인증서 발급 및 자동 설정
sudo certbot --nginx -d youtube-trend.neuralgrid.app

# 자동 갱신 테스트
sudo certbot renew --dry-run
```

Certbot이 자동으로:
- SSL 인증서 발급
- Nginx 설정 업데이트
- HTTP → HTTPS 리다이렉트 설정
- 자동 갱신 설정

SSL 설정 후 접속:
```
https://youtube-trend.neuralgrid.app
```

---

## 📊 서비스 관리

### 서비스 상태 확인

```bash
# 백엔드 상태
sudo systemctl status youtube-trend-backend

# 프론트엔드 상태
sudo systemctl status youtube-trend-frontend

# Nginx 상태
sudo systemctl status nginx
```

### 서비스 재시작

```bash
# 백엔드 재시작
sudo systemctl restart youtube-trend-backend

# 프론트엔드 재시작
sudo systemctl restart youtube-trend-frontend

# Nginx 재시작
sudo systemctl restart nginx

# 모든 서비스 재시작
sudo systemctl restart youtube-trend-backend youtube-trend-frontend nginx
```

### 서비스 중지

```bash
# 백엔드 중지
sudo systemctl stop youtube-trend-backend

# 프론트엔드 중지
sudo systemctl stop youtube-trend-frontend
```

### 서비스 자동 시작 비활성화

```bash
sudo systemctl disable youtube-trend-backend
sudo systemctl disable youtube-trend-frontend
```

---

## 📝 로그 확인

### 실시간 로그 모니터링

```bash
# 백엔드 로그
sudo journalctl -u youtube-trend-backend -f

# 프론트엔드 로그
sudo journalctl -u youtube-trend-frontend -f

# Nginx 접근 로그
sudo tail -f /var/log/nginx/youtube-trend-access.log

# Nginx 에러 로그
sudo tail -f /var/log/nginx/youtube-trend-error.log
```

### 최근 로그 확인

```bash
# 백엔드 최근 100줄
sudo journalctl -u youtube-trend-backend -n 100

# 프론트엔드 최근 100줄
sudo journalctl -u youtube-trend-frontend -n 100
```

---

## 🔄 업데이트

코드 업데이트 후:

```bash
# 1. Git pull
cd /home/azamans/webapp/youtube-trend-analyzer
git pull

# 2. 백엔드 의존성 업데이트
cd backend
npm install

# 3. 프론트엔드 의존성 업데이트
cd ../frontend
npm install

# 4. 서비스 재시작
sudo systemctl restart youtube-trend-backend
sudo systemctl restart youtube-trend-frontend
```

---

## 🐛 문제 해결

### 백엔드가 시작되지 않는 경우

```bash
# 로그 확인
sudo journalctl -u youtube-trend-backend -n 50

# 포트 사용 확인
sudo lsof -i :5000

# 환경 변수 확인
cat /home/azamans/webapp/youtube-trend-analyzer/backend/.env
```

### 프론트엔드가 시작되지 않는 경우

```bash
# 로그 확인
sudo journalctl -u youtube-trend-frontend -n 50

# 포트 사용 확인
sudo lsof -i :3000
```

### Nginx 에러

```bash
# 설정 테스트
sudo nginx -t

# 에러 로그 확인
sudo tail -f /var/log/nginx/youtube-trend-error.log

# Nginx 재시작
sudo systemctl restart nginx
```

### API 키 문제

```bash
# .env 파일 확인
cat /home/azamans/webapp/youtube-trend-analyzer/backend/.env

# API 키 설정 후 재시작
sudo systemctl restart youtube-trend-backend
```

---

## 🌐 DNS 설정

서브도메인이 작동하려면 DNS 레코드 설정이 필요합니다:

### DNS 레코드 추가

도메인 관리 페이지에서 A 레코드 추가:

```
Type: A
Name: youtube-trend
Value: [서버 IP 주소]
TTL: 3600
```

또는 CNAME 레코드:

```
Type: CNAME
Name: youtube-trend
Value: neuralgrid.app
TTL: 3600
```

DNS 전파 확인:
```bash
nslookup youtube-trend.neuralgrid.app
```

---

## 📋 체크리스트

설치 완료 확인:

- [ ] Node.js 설치 확인
- [ ] 백엔드 의존성 설치
- [ ] 프론트엔드 의존성 설치
- [ ] YouTube API 키 설정
- [ ] Systemd 서비스 활성화
- [ ] Nginx 설정 완료
- [ ] DNS 레코드 설정
- [ ] 서브도메인 접속 확인
- [ ] SSL 인증서 설정 (선택)
- [ ] 자동 검색 동작 확인

---

## 🎯 성능 최적화 (선택사항)

### PM2로 프로세스 관리 (대안)

```bash
# PM2 설치
npm install -g pm2

# 백엔드 실행
cd /home/azamans/webapp/youtube-trend-analyzer/backend
pm2 start server.js --name youtube-trend-backend

# 프론트엔드 실행
cd /home/azamans/webapp/youtube-trend-analyzer/frontend
pm2 start "npm run dev" --name youtube-trend-frontend

# 부팅 시 자동 시작
pm2 startup
pm2 save
```

---

## 📞 지원

문제가 발생하면:

1. 로그 확인
2. 서비스 상태 확인
3. 방화벽 및 포트 확인
4. DNS 설정 확인

---

**설치 완료 후 접속**: http://youtube-trend.neuralgrid.app 🎉
