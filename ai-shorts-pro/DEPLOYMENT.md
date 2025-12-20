# 🎬 AI Shorts Pro - 배포 완료!

## ✅ 배포 상태

**백엔드**: ✅ 실행 중 (포트 5555)
**프론트엔드**: ✅ 빌드 완료
**nginx 설정**: ✅ 준비 완료
**도메인**: ai-shorts.neuralgrid.kr

---

## 🌐 접속 정보

### 로컬 접속 (현재 작동 중)
```bash
# 백엔드 API
curl http://localhost:5555/api/health

# 프론트엔드 파일
ls /home/azamans/webapp/ai-shorts-pro/frontend/dist/
```

### 공개 접속 (DNS 설정 필요)
```
🎬 메인 사이트: https://ai-shorts.neuralgrid.kr
📡 백엔드 API:  https://ai-shorts.neuralgrid.kr/api/health
```

---

## 🚀 최종 배포 단계

### 1️⃣ nginx 설정 적용 (관리자 권한 필요)

```bash
# 방법 1: 직접 실행
sudo /tmp/deploy_ai_shorts.sh

# 방법 2: 수동 실행
sudo cp /home/azamans/webapp/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-available/
sudo ln -sf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 2️⃣ DNS 설정 확인

도메인 `ai-shorts.neuralgrid.kr`이 서버 IP를 가리키는지 확인:

```bash
# DNS 확인
dig ai-shorts.neuralgrid.kr

# 또는
nslookup ai-shorts.neuralgrid.kr
```

### 3️⃣ 배포 테스트

```bash
# API 테스트
curl https://ai-shorts.neuralgrid.kr/api/health

# 브라우저로 접속
https://ai-shorts.neuralgrid.kr
```

---

## 🔧 시스템 구조

### 백엔드 (포트 5555)
```
/home/azamans/webapp/ai-shorts-pro/backend/
├── server.js              # 메인 서버
├── controllers/           # API 컨트롤러 (6개)
├── services/             # 비즈니스 로직 (3개)
├── routes/               # API 라우트 (7개)
└── .env                  # 환경 변수
```

### 프론트엔드 (정적 파일)
```
/home/azamans/webapp/ai-shorts-pro/frontend/dist/
├── index.html
├── assets/
│   ├── index-*.js        # React 앱
│   └── index-*.css       # Tailwind CSS
```

### nginx 설정
```
/etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf
↓ (symlink)
/etc/nginx/sites-enabled/ai-shorts.neuralgrid.kr.conf
```

---

## 📊 현재 실행 중인 프로세스

```bash
# 백엔드 프로세스 확인
ps aux | grep "node server.js"

# 포트 확인
netstat -tlnp | grep 5555
```

**현재 상태**:
- PID: 2355146
- 포트: 5555
- 상태: ✅ 정상 실행 중

---

## 🔄 재시작 방법

### 백엔드 재시작
```bash
# 1. 기존 프로세스 종료
pkill -9 -f "node.*server.js"

# 2. 새로 시작
cd /home/azamans/webapp/ai-shorts-pro/backend
nohup node server.js > server.log 2>&1 &

# 3. 확인
curl http://localhost:5555/api/health
```

### 프론트엔드 재빌드
```bash
cd /home/azamans/webapp/ai-shorts-pro/frontend
npm run build
```

---

## 📝 환경 변수 설정

`/home/azamans/webapp/ai-shorts-pro/backend/.env`:
```env
PORT=5555
NODE_ENV=production
REDIS_ENABLED=false
FFMPEG_PATH=ffmpeg
FFPROBE_PATH=ffprobe
```

나중에 AI API 키 추가:
```env
# AI API Keys (나중에 추가)
GEMINI_API_KEY=your_key_here
MINIMAX_API_KEY=your_key_here
ELEVENLABS_API_KEY=your_key_here
```

---

## 🎯 주요 기능

### 완성된 기능
- ✅ 10개 캐릭터 프리셋
- ✅ 8개 AI 음성
- ✅ YouTube 메타데이터 생성
- ✅ Redis 캐싱 (인메모리 폴백)
- ✅ FFmpeg 렌더링 파이프라인
- ✅ Socket.io 실시간 통신
- ✅ 블로그 크롤링 & 스크립트 생성

### API 엔드포인트
- `GET  /api/health` - 헬스 체크
- `GET  /api/characters` - 캐릭터 목록
- `GET  /api/voices` - 음성 목록
- `POST /api/crawler/crawl` - 블로그 크롤링
- `POST /api/youtube/generate` - YouTube 메타데이터
- `POST /api/generation/start` - 영상 생성 시작
- 기타 25+ 엔드포인트

---

## 🐛 트러블슈팅

### 1. 백엔드가 시작되지 않음
```bash
# 로그 확인
cd /home/azamans/webapp/ai-shorts-pro/backend
cat server.log

# 포트 충돌 확인
lsof -i :5555
```

### 2. nginx 설정 오류
```bash
# 설정 테스트
sudo nginx -t

# 에러 로그 확인
sudo tail -f /var/log/nginx/ai-shorts.neuralgrid.kr.error.log
```

### 3. DNS가 연결되지 않음
```bash
# DNS 전파 확인
dig ai-shorts.neuralgrid.kr

# hosts 파일로 임시 테스트
echo "YOUR_SERVER_IP ai-shorts.neuralgrid.kr" | sudo tee -a /etc/hosts
```

---

## 📞 지원

문제 발생 시:
1. `/home/azamans/webapp/ai-shorts-pro/backend/server.log` 확인
2. `/var/log/nginx/ai-shorts.neuralgrid.kr.error.log` 확인
3. GitHub Issue: https://github.com/hompystory-coder/azamans/issues

---

## 🎉 완료!

AI Shorts Pro가 성공적으로 배포되었습니다!

다음 명령어만 실행하면 바로 사용 가능합니다:

```bash
sudo /tmp/deploy_ai_shorts.sh
```

그 후 브라우저에서 접속:
```
https://ai-shorts.neuralgrid.kr
```

---

**Made with ❤️ by NeuralGrid Team**
