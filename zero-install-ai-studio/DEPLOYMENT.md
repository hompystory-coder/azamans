# Zero-Install AI Studio - 배포 가이드

## 🌐 현재 상태

### 개발 서버
- **URL**: http://115.91.5.140:3000
- **상태**: ✅ 실행 중
- **페이지**:
  - `/` - 랜딩 페이지
  - `/studio` - AI 이미지 스튜디오
  - `/pro-shorts` - 프로 쇼츠 생성기
  - `/timeline` - 타임라인 편집기
  - `/editor` - 고급 편집기
  - `/music` - 음악 라이브러리
  - `/export` - 일괄 내보내기
  - `/gallery` - 갤러리

---

## 🚀 프로덕션 배포 옵션

### Option A: 서브도메인 설정 (권장)

#### 1. DNS 설정
서브도메인을 현재 서버 IP로 연결:
```
ai-studio.yourdomain.com  →  115.91.5.140
```

#### 2. Nginx 리버스 프록시 설정
```nginx
# /etc/nginx/sites-available/ai-studio

server {
    listen 80;
    server_name ai-studio.yourdomain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ai-studio.yourdomain.com;

    # SSL 인증서 (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/ai-studio.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ai-studio.yourdomain.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Proxy to Next.js
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Cache static files
    location /_next/static {
        proxy_pass http://localhost:3000;
        add_header Cache-Control "public, max-age=31536000, immutable";
    }

    # Cache images
    location ~* \.(jpg|jpeg|png|gif|ico|svg|webp)$ {
        proxy_pass http://localhost:3000;
        add_header Cache-Control "public, max-age=604800";
    }
}
```

#### 3. SSL 인증서 발급
```bash
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# 인증서 발급
sudo certbot --nginx -d ai-studio.yourdomain.com

# 자동 갱신 테스트
sudo certbot renew --dry-run
```

#### 4. Nginx 활성화
```bash
sudo ln -s /etc/nginx/sites-available/ai-studio /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 5. PM2로 프로덕션 서버 실행
```bash
cd /home/azamans/webapp/zero-install-ai-studio

# PM2 설치 (없는 경우)
npm install -g pm2

# 프로덕션 빌드
npm run build

# PM2로 시작
pm2 start npm --name "ai-studio" -- start

# 부팅 시 자동 시작
pm2 startup
pm2 save

# 상태 확인
pm2 status
pm2 logs ai-studio
```

---

### Option B: Vercel 배포 (가장 빠름)

#### 1. Vercel CLI 설치
```bash
npm i -g vercel
```

#### 2. 배포
```bash
cd /home/azamans/webapp/zero-install-ai-studio
vercel

# 프로덕션 배포
vercel --prod
```

#### 3. 커스텀 도메인 연결
```bash
vercel domains add ai-studio.yourdomain.com
```

---

### Option C: Docker 컨테이너 (격리 환경)

#### 1. Dockerfile 생성
```dockerfile
FROM node:20-alpine AS base

# Dependencies
FROM base AS deps
WORKDIR /app
COPY package*.json ./
RUN npm ci

# Builder
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN npm run build

# Runner
FROM base AS runner
WORKDIR /app

ENV NODE_ENV production

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

CMD ["node", "server.js"]
```

#### 2. Docker Compose
```yaml
version: '3.8'

services:
  ai-studio:
    build: .
    ports:
      - "3000:3000"
    restart: always
    environment:
      - NODE_ENV=production
    volumes:
      - ./data:/app/data
```

#### 3. 실행
```bash
docker-compose up -d
```

---

## 📊 성능 최적화

### 1. Next.js 설정 최적화
```javascript
// next.config.js
module.exports = {
  reactStrictMode: true,
  swcMinify: true,
  compress: true,
  
  // Output standalone for Docker
  output: 'standalone',
  
  // Image optimization
  images: {
    domains: ['api-inference.huggingface.co'],
    minimumCacheTTL: 60,
  },
  
  // Headers
  async headers() {
    return [
      {
        source: '/:path*',
        headers: [
          {
            key: 'X-DNS-Prefetch-Control',
            value: 'on'
          },
          {
            key: 'Strict-Transport-Security',
            value: 'max-age=63072000; includeSubDomains; preload'
          },
        ],
      },
    ]
  },
}
```

### 2. CDN 설정
- Vercel: 자동 CDN 지원
- Cloudflare: DNS 설정 후 자동 CDN
- AWS CloudFront: S3 + CloudFront 배포

---

## 🔧 모니터링 & 유지보수

### PM2 모니터링
```bash
# 로그 확인
pm2 logs ai-studio

# 메모리/CPU 사용량
pm2 monit

# 재시작
pm2 restart ai-studio

# 중지
pm2 stop ai-studio
```

### 성능 모니터링
```bash
# 접속 로그
sudo tail -f /var/log/nginx/access.log

# 에러 로그
sudo tail -f /var/log/nginx/error.log

# PM2 실시간 모니터링
pm2 monit
```

---

## 🌟 권장 배포 순서

1. **즉시 배포** (15분):
   - PM2로 프로덕션 빌드 실행
   - 현재 IP로 접속 테스트

2. **서브도메인 설정** (30분):
   - DNS 설정
   - Nginx 리버스 프록시
   - SSL 인증서 발급

3. **최적화** (1시간):
   - CDN 설정
   - 캐싱 전략
   - 모니터링 설정

---

## 📝 체크리스트

### 배포 전
- [ ] `npm run build` 성공
- [ ] 모든 페이지 로딩 확인
- [ ] AI 엔진 초기화 테스트
- [ ] 브라우저 호환성 확인
- [ ] 모바일 반응형 테스트

### 배포 후
- [ ] HTTPS 인증서 작동
- [ ] 모든 페이지 접속 확인
- [ ] PWA 설치 테스트
- [ ] 오프라인 모드 테스트
- [ ] 성능 메트릭 확인

---

## 🆘 문제 해결

### Next.js 빌드 에러
```bash
rm -rf .next node_modules
npm install
npm run build
```

### PM2 재시작 필요
```bash
pm2 delete ai-studio
cd /home/azamans/webapp/zero-install-ai-studio
pm2 start npm --name "ai-studio" -- start
```

### Nginx 설정 테스트
```bash
sudo nginx -t
sudo systemctl status nginx
sudo systemctl restart nginx
```

---

## 🎯 다음 단계

현재 선택할 수 있는 옵션:

### A) 즉시 프로덕션 배포 (15분)
- PM2로 프로덕션 모드 전환
- 현재 IP에서 안정화 테스트

### B) 서브도메인 + SSL 설정 (30분)
- 도메인 정보 제공 필요
- Nginx + Let's Encrypt 자동 설정

### C) Vercel 배포 (10분)
- 가장 빠른 배포
- 자동 CDN + SSL
- 무료 커스텀 도메인

---

**현재 상태**: ✅ 모든 기능 구현 완료, 배포 준비 완료

**Live Demo**: http://115.91.5.140:3000

**GitHub**: https://github.com/hompystory-coder/azamans

**어떤 옵션으로 진행하시겠습니까?**
