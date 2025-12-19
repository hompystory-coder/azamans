# 🚀 Shorts Creator Pro 배포 가이드

## ✅ 현재 상태

### 완료된 작업
- ✅ 백엔드 API 서버 개발 완료 (포트 4001, PM2 ID: 28)
- ✅ 프론트엔드 React 앱 개발 완료 (7개 페이지)
- ✅ 프론트엔드 빌드 완료 (`~/shorts-creator-pro/frontend/dist`)
- ✅ Nginx 설정 파일 준비 완료 (`~/nginx-shorts-creator-pro.conf`)

---

## 🔧 배포 단계 (수동 실행 필요)

### 1단계: SSH 접속
```bash
ssh neuralgrid
```

### 2단계: Nginx 설정 백업
```bash
sudo cp /etc/nginx/sites-available/shorts.neuralgrid.kr \
        /etc/nginx/sites-available/shorts.neuralgrid.kr.backup_old_$(date +%Y%m%d_%H%M%S)
```

### 3단계: 새 Nginx 설정 적용
```bash
sudo cp ~/nginx-shorts-creator-pro.conf /etc/nginx/sites-available/shorts.neuralgrid.kr
```

### 4단계: Nginx 설정 테스트
```bash
sudo nginx -t
```

**예상 출력**:
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### 5단계: Nginx 재시작
```bash
sudo systemctl reload nginx
```

### 6단계: 서비스 상태 확인
```bash
# Nginx 상태
sudo systemctl status nginx

# 백엔드 상태
pm2 status shorts-creator-backend

# 포트 확인
ss -tlnp | grep 4001
```

### 7단계: 웹사이트 접속
```
https://shorts.neuralgrid.kr
```

---

## 🌐 서비스 구조

### Frontend (React + Vite)
- **위치**: `/home/azamans/shorts-creator-pro/frontend/dist`
- **도메인**: `https://shorts.neuralgrid.kr`
- **포트**: 443 (HTTPS)
- **역할**: 사용자 인터페이스 (SPA)

### Backend (Express.js)
- **위치**: `/home/azamans/shorts-creator-pro/backend`
- **내부 포트**: 4001
- **PM2 이름**: `shorts-creator-backend`
- **PM2 ID**: 28
- **역할**: API 서버 (크롤링, 스크립트, 음성, 비디오 생성)

### Nginx (Reverse Proxy)
- **프론트엔드**: 정적 파일 서빙 (`/`)
- **백엔드 API**: 프록시 (`/api/*` → `http://127.0.0.1:4001`)
- **SSL**: Let's Encrypt 인증서

---

## 📝 API 엔드포인트

백엔드 API는 `/api` 경로로 프록시됩니다:

```
GET  /api/settings/list              → 설정 목록
POST /api/settings/save              → 설정 저장
POST /api/crawler/fetch              → 크롤링
POST /api/script/generate            → 스크립트 생성 (Gemini)
GET  /api/voice/samples              → 음성 샘플
POST /api/voice/generate             → 음성 생성 (Minimax)
POST /api/video/generate             → 비디오 생성 (FFmpeg)
POST /api/render/final               → 최종 렌더링 (Shotstack)
```

---

## 🧪 테스트 방법

### 1. 백엔드 API 테스트
```bash
# Health check
curl http://localhost:4001/health

# 설정 목록
curl http://localhost:4001/api/settings/list

# 또는 외부에서
curl https://shorts.neuralgrid.kr/api/settings/list
```

### 2. 프론트엔드 테스트
```bash
# 브라우저에서 접속
https://shorts.neuralgrid.kr

# 예상 결과: React 앱 로딩, 크롤링 페이지 표시
```

### 3. 전체 워크플로우 테스트
1. `https://shorts.neuralgrid.kr` 접속
2. 블로그 URL 입력 (예: 네이버 블로그)
3. [크롤링] → [스크립트 생성] → [음성 생성] → [비디오 생성] → [렌더링] → [미리보기]

---

## 🔍 문제 해결 (Troubleshooting)

### 문제 1: Nginx 설정 오류
```bash
# 설정 테스트
sudo nginx -t

# 에러 로그 확인
sudo tail -f /var/log/nginx/shorts.neuralgrid.kr.error.log
```

### 문제 2: 백엔드 API 연결 실패
```bash
# PM2 상태 확인
pm2 status shorts-creator-backend

# 백엔드 로그 확인
pm2 logs shorts-creator-backend --lines 50

# 백엔드 재시작
pm2 restart shorts-creator-backend
```

### 문제 3: 프론트엔드 빌드 파일 누락
```bash
# dist 폴더 확인
ls -la ~/shorts-creator-pro/frontend/dist

# 빌드 다시 실행
cd ~/shorts-creator-pro/frontend
npm run build
```

### 문제 4: API 요청 CORS 오류
- `backend/src/server.js`에서 CORS 설정 확인
- `CORS_ORIGIN=https://shorts.neuralgrid.kr` 설정 확인

### 문제 5: SSL 인증서 만료
```bash
# 인증서 갱신
sudo certbot renew

# Nginx 재시작
sudo systemctl reload nginx
```

---

## 📊 PM2 프로세스 관리

### 백엔드 서버 관리
```bash
# 시작
pm2 start shorts-creator-backend

# 중지
pm2 stop shorts-creator-backend

# 재시작
pm2 restart shorts-creator-backend

# 로그 확인
pm2 logs shorts-creator-backend

# 상세 정보
pm2 show shorts-creator-backend
```

---

## 🎨 프론트엔드 페이지 구조

1. **SettingsPage** (`/settings`) - API 키 설정 (선택사항)
2. **CrawlerPage** (`/crawler`) - 블로그/기사 크롤링
3. **ScriptPage** (`/script`) - AI 스크립트 생성 및 편집
4. **VoicePage** (`/voice`) - 음성 선택 및 생성
5. **VideoPage** (`/video`) - 비디오 생성 (FFmpeg)
6. **RenderPage** (`/render`) - 최종 렌더링 (Shotstack)
7. **PreviewPage** (`/preview`) - 미리보기 및 YouTube 업로드 준비

---

## 🔐 보안 고려사항

1. **API 키 보호**: `.env` 파일 권한 확인
   ```bash
   chmod 600 ~/shorts-creator-pro/backend/.env
   ```

2. **Nginx 보안 헤더**: 이미 설정됨 (SSL, HSTS)

3. **PM2 자동 시작**: 서버 재부팅 시 자동 시작 설정됨
   ```bash
   pm2 save
   pm2 startup
   ```

---

## 📦 디렉토리 구조

```
/home/azamans/shorts-creator-pro/
├── backend/
│   ├── src/
│   │   ├── server.js           # Express 서버
│   │   ├── routes/             # API 라우트
│   │   └── utils/              # 유틸리티
│   ├── .env                    # 환경 변수 (API 키)
│   └── package.json
├── frontend/
│   ├── src/
│   │   ├── pages/              # 7개 페이지
│   │   ├── components/         # Layout 등
│   │   ├── store/              # Zustand 상태 관리
│   │   └── api/                # API 클라이언트
│   ├── dist/                   # 빌드된 정적 파일 ⭐
│   └── package.json
└── docs/                       # 문서
```

---

## ✅ 최종 체크리스트

배포 전 확인사항:

- [ ] 백엔드 PM2 프로세스 정상 동작 (`pm2 status`)
- [ ] 프론트엔드 빌드 파일 존재 (`ls ~/shorts-creator-pro/frontend/dist`)
- [ ] Nginx 설정 파일 업로드 (`~/nginx-shorts-creator-pro.conf`)
- [ ] Nginx 설정 테스트 통과 (`sudo nginx -t`)
- [ ] Nginx 재시작 (`sudo systemctl reload nginx`)
- [ ] 웹사이트 접속 테스트 (`https://shorts.neuralgrid.kr`)
- [ ] API 응답 테스트 (`curl https://shorts.neuralgrid.kr/api/settings/list`)
- [ ] 브라우저 콘솔 에러 확인 (F12)

---

## 🎉 배포 완료 후

### 성공 확인
```bash
# 1. 백엔드 상태
pm2 status shorts-creator-backend

# 2. Nginx 상태
sudo systemctl status nginx

# 3. 웹사이트 접속
curl -I https://shorts.neuralgrid.kr

# 4. API 테스트
curl https://shorts.neuralgrid.kr/api/settings/list
```

### 다음 단계
1. 전체 워크플로우 실제 테스트
2. 성능 모니터링 설정
3. 에러 로깅 및 알림 설정
4. 사용자 피드백 수집

---

**작성일**: 2025-12-17  
**작성자**: Claude AI Assistant  
**문서 위치**: `/home/azamans/webapp/DEPLOYMENT_GUIDE.md`
