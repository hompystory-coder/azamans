# 🚀 Shorts Market 로컬 서버 배포 완료

**Date**: 2025-12-15  
**Status**: ✅ 배포 완료  
**Domain**: market.neuralgrid.kr (DNS 설정 대기 중)

---

## 📊 배포 요약

### ✅ 완료된 작업
1. ✅ 로컬 Node.js 서버 구축 (Hono + @hono/node-server)
2. ✅ PM2로 백그라운드 실행 설정
3. ✅ Nginx 리버스 프록시 설정
4. ✅ SSL 인증서 설정 (임시: neuralgrid.kr 와일드카드)

### ⚠️ DNS 설정 필요
- **도메인**: market.neuralgrid.kr
- **필요한 작업**: A 레코드 추가 → 115.91.5.140

---

## 🎯 서비스 정보

### **서비스명**: Shorts Market
- **PM2 이름**: shorts-market
- **포트**: 3003
- **상태**: ✅ Online
- **메모리**: ~57 MB
- **프로세스 ID**: 1798181

### **URL 정보**
- **로컬**: http://localhost:3003 ✅ 작동 중
- **서버 내부**: http://115.91.5.140:3003 ✅ 작동 중
- **공개 도메인**: https://market.neuralgrid.kr ⚠️ DNS 설정 필요
- **기존 Cloudflare**: https://a48be6e9.shorts-market.pages.dev ✅ 여전히 작동

---

## 🔧 기술 상세

### **서버 구성**
```javascript
// server.js
import { serve } from '@hono/node-server';
import app from './dist/_worker.js';

serve({
  fetch: app.fetch,
  port: 3003,
});
```

### **PM2 Ecosystem**
```javascript
// ecosystem.config.cjs
module.exports = {
  apps: [{
    name: 'shorts-market',
    script: 'server.js',
    cwd: '/home/azamans/shorts-market',
    env: {
      NODE_ENV: 'production',
      PORT: 3003
    },
    instances: 1,
    exec_mode: 'fork',
    autorestart: true,
    max_restarts: 10
  }]
};
```

### **Nginx 설정**
```nginx
server {
    listen 443 ssl http2;
    server_name market.neuralgrid.kr;
    
    ssl_certificate /etc/letsencrypt/live/neuralgrid.kr/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/neuralgrid.kr/privkey.pem;
    
    location / {
        proxy_pass http://127.0.0.1:3003;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 50M;
    }
}
```

---

## 📁 파일 구조

```
/home/azamans/shorts-market/
├── server.js                  ✅ 새로 생성 (Node.js 서버)
├── ecosystem.config.cjs       ✅ 업데이트 (PM2 설정)
├── dist/
│   └── _worker.js            (빌드된 Hono 앱)
├── src/                      (소스 코드)
├── public/                   (정적 파일)
├── logs/
│   ├── error.log
│   └── out.log
└── package.json
```

---

## 🎮 PM2 관리 명령어

### **상태 확인**
```bash
pm2 list
pm2 info shorts-market
pm2 logs shorts-market
```

### **재시작 / 중지**
```bash
# 재시작
pm2 restart shorts-market

# 중지
pm2 stop shorts-market

# 삭제
pm2 delete shorts-market
```

### **로그 확인**
```bash
# 실시간 로그
pm2 logs shorts-market --lines 50

# 에러 로그만
pm2 logs shorts-market --err

# 파일로 확인
tail -f /home/azamans/shorts-market/logs/out.log
tail -f /home/azamans/shorts-market/logs/error.log
```

---

## 🌐 DNS 설정 방법

### **필요한 작업**
Cloudflare 또는 도메인 관리 페이지에서:

1. **A 레코드 추가**:
   - **Type**: A
   - **Name**: market (또는 market.neuralgrid.kr)
   - **IPv4 address**: 115.91.5.140
   - **TTL**: Auto
   - **Proxy status**: DNS only (또는 Proxied)

2. **저장 후 전파 대기** (최대 24시간, 보통 5-10분)

3. **DNS 확인**:
   ```bash
   nslookup market.neuralgrid.kr
   # 또는
   dig market.neuralgrid.kr
   ```

### **DNS 설정 후 SSL 재발급** (선택사항)
DNS가 전파되면 전용 SSL 인증서 발급:
```bash
sudo certbot --nginx -d market.neuralgrid.kr --non-interactive \
    --agree-tos --email admin@neuralgrid.kr --redirect
```

---

## 🧪 테스트 결과

### **로컬 테스트** ✅
```bash
curl -I http://localhost:3003
# HTTP/1.1 200 OK
# content-type: text/html; charset=UTF-8
```

### **서버 테스트** ✅
```bash
curl http://115.91.5.140:3003 | grep title
# <title>쇼츠 마켓 - 쿠팡 파트너스 쇼츠 플랫폼</title>
```

### **PM2 상태** ✅
```
shorts-market │ online │ port 3003 │ 57.1 MB
```

---

## 📊 Before vs After

### **Before (Cloudflare Pages)**
- **호스팅**: Cloudflare Pages
- **데이터베이스**: D1 (Cloudflare)
- **URL**: https://a48be6e9.shorts-market.pages.dev
- **장점**: 무료, 글로벌 CDN
- **단점**: 복잡한 URL, Cloudflare 종속

### **After (로컬 서버)** ✅
- **호스팅**: PM2 on 115.91.5.140
- **데이터베이스**: 로컬 SQLite (예정)
- **URL**: https://market.neuralgrid.kr (DNS 대기)
- **장점**: 완전한 제어, neuralgrid 통합, 간단한 URL
- **단점**: 서버 리소스 사용

---

## 🔄 Cloudflare D1 → SQLite 마이그레이션 (다음 단계)

현재는 D1 데이터베이스 없이 실행 중입니다. 데이터가 필요한 경우:

### **옵션 1: Cloudflare D1 데이터 Export**
```bash
wrangler d1 export webapp-production --output=data.sql
sqlite3 local.db < data.sql
```

### **옵션 2: 새로 시작**
```bash
cd /home/azamans/shorts-market
npm run db:migrate:local
npm run db:seed
```

### **옵션 3: Cloudflare Pages 계속 사용**
데이터베이스가 필요하면 Cloudflare Pages 버전 유지:
- https://a48be6e9.shorts-market.pages.dev

---

## 💡 권장 사항

### **즉시 가능**
1. ✅ 로컬 서버 사용 (http://localhost:3003)
2. ✅ 기존 Cloudflare 계속 사용 (데이터베이스 있음)

### **DNS 설정 후**
1. ⏳ https://market.neuralgrid.kr 접속 가능
2. ⏳ 전용 SSL 인증서 발급
3. ⏳ 로컬 SQLite 데이터베이스 설정

### **양쪽 다 유지 (추천)**
- **로컬**: 빠른 접근, 테스트용
- **Cloudflare**: 프로덕션, 데이터 보존

---

## 🔗 접근 방법

### **현재 사용 가능**
1. ✅ **서버 내부**: http://localhost:3003
2. ✅ **Cloudflare Pages**: https://a48be6e9.shorts-market.pages.dev

### **DNS 설정 후**
3. ⏳ **커스텀 도메인**: https://market.neuralgrid.kr

---

## 📝 다음 단계

1. **DNS 설정** (5분 작업)
   - Cloudflare DNS에서 A 레코드 추가
   - market.neuralgrid.kr → 115.91.5.140

2. **SSL 재발급** (선택사항, DNS 후)
   - 전용 SSL 인증서 발급
   - 현재는 neuralgrid.kr 와일드카드 사용 중

3. **데이터베이스 설정** (필요시)
   - Cloudflare D1 export
   - 또는 로컬 SQLite 새로 생성

4. **테스트**
   - 모든 기능 정상 작동 확인
   - 쿠팡 API 연동 확인
   - YouTube API 확인

---

## 🎉 배포 완료 상태

| 항목 | 상태 | 비고 |
|------|------|------|
| 로컬 서버 빌드 | ✅ 완료 | port 3003 |
| PM2 배포 | ✅ 완료 | 자동 재시작 활성화 |
| Nginx 설정 | ✅ 완료 | market.neuralgrid.kr |
| SSL 인증서 | ✅ 완료 | 임시 (neuralgrid.kr) |
| DNS 설정 | ⚠️ 대기 | A 레코드 추가 필요 |
| 데이터베이스 | ⚠️ 선택 | D1 또는 SQLite |

---

## 📞 문제 해결

### **서버가 응답하지 않는 경우**
```bash
# PM2 상태 확인
pm2 status shorts-market

# 로그 확인
pm2 logs shorts-market --err

# 재시작
pm2 restart shorts-market
```

### **Nginx 에러**
```bash
# 설정 테스트
sudo nginx -t

# 로그 확인
sudo tail -f /var/log/nginx/market.neuralgrid.kr.error.log
```

### **포트 충돌**
```bash
# 포트 3003 사용 중인 프로세스 확인
sudo lsof -i :3003
```

---

**배포 완료!** 🎉

**DNS 설정만 하시면 https://market.neuralgrid.kr 로 접속 가능합니다!**

---

**Generated**: 2025-12-15  
**Server**: 115.91.5.140  
**Port**: 3003  
**Status**: ✅ Online and Ready
