# 🚀 AI Shorts Pro - 빠른 배포 가이드

## 현재 상태

✅ **백엔드**: 실행 중 (포트 5555)  
✅ **프론트엔드**: 빌드 완료  
✅ **설정 파일**: 준비 완료  
⏳ **nginx**: 적용 대기 중  

---

## 🎯 1분 배포

**단 한 줄로 배포 완료:**

```bash
sudo bash /home/azamans/webapp/install-ai-shorts-pro.sh
```

이 명령어는 자동으로:
- ✅ nginx 설정 적용
- ✅ systemd 서비스 등록
- ✅ 백엔드 자동 시작 설정
- ✅ 모든 설정 완료

---

## 🌐 접속 정보

### 배포 후 접속
```
🎬 메인: https://ai-shorts.neuralgrid.kr
📡 API:  https://ai-shorts.neuralgrid.kr/api/health
```

### 현재 로컬 접속 (테스트 완료)
```bash
curl http://localhost:5555/api/health
# {"status":"ok","timestamp":"2025-12-20T01:35:05.004Z","service":"AI Shorts Pro Backend"}
```

---

## 🔧 수동 배포 (선택사항)

자동 스크립트 대신 수동으로 배포하려면:

### 1. nginx 설정
```bash
sudo cp /home/azamans/webapp/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-available/
sudo ln -sf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 2. Systemd 서비스 (백엔드 영구 실행)
```bash
sudo cp /home/azamans/webapp/ai-shorts-pro.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable ai-shorts-pro
sudo systemctl start ai-shorts-pro
sudo systemctl status ai-shorts-pro
```

---

## 📊 서비스 관리

### 상태 확인
```bash
sudo systemctl status ai-shorts-pro
```

### 재시작
```bash
sudo systemctl restart ai-shorts-pro
```

### 로그 확인
```bash
# 실시간 로그
sudo journalctl -u ai-shorts-pro -f

# 또는
tail -f /home/azamans/webapp/ai-shorts-pro/backend/server.log
```

### 서비스 중지
```bash
sudo systemctl stop ai-shorts-pro
```

---

## 🧪 배포 테스트

배포 완료 후 테스트:

```bash
# 1. API 헬스 체크
curl https://ai-shorts.neuralgrid.kr/api/health

# 2. 캐릭터 목록
curl https://ai-shorts.neuralgrid.kr/api/characters

# 3. 음성 목록
curl https://ai-shorts.neuralgrid.kr/api/voices

# 4. 브라우저 접속
open https://ai-shorts.neuralgrid.kr
```

---

## 🎨 UI 미리보기

배포 후 다음 페이지들을 확인할 수 있습니다:

1. **메인 대시보드** - 프로젝트 관리
2. **프로젝트 생성** - 6단계 마법사
3. **캐릭터 선택** - 10개 전문 캐릭터
4. **음성 선택** - 8개 AI 음성 (미리듣기 기능)
5. **스크립트 편집** - 실시간 미리보기
6. **생성 모니터** - 실시간 진행 상황

---

## 📁 프로젝트 구조

```
/home/azamans/webapp/ai-shorts-pro/
├── backend/
│   ├── server.js              # Express 서버
│   ├── controllers/           # 6개 컨트롤러
│   ├── services/             # AI, FFmpeg, Cache
│   ├── routes/               # 7개 API 라우트
│   └── .env                  # 환경 변수 (PORT=5555)
│
├── frontend/
│   ├── dist/                 # 빌드된 정적 파일
│   └── src/                  # React 소스
│
├── shared/
│   ├── characters.json       # 10개 캐릭터
│   ├── voices.json          # 8개 음성
│   └── fonts.json           # 6개 폰트
│
└── DEPLOYMENT.md            # 상세 배포 가이드
```

---

## 🐛 문제 해결

### 백엔드가 시작되지 않음
```bash
# 로그 확인
sudo journalctl -u ai-shorts-pro -n 50

# 수동 시작
cd /home/azamans/webapp/ai-shorts-pro/backend
node server.js
```

### nginx 오류
```bash
# 설정 테스트
sudo nginx -t

# 오류 로그
sudo tail -f /var/log/nginx/ai-shorts.neuralgrid.kr.error.log
```

### 포트 충돌
```bash
# 5555 포트 사용 프로세스 확인
sudo lsof -i :5555

# 프로세스 종료 (필요시)
sudo kill -9 $(lsof -ti:5555)
```

---

## 🔐 보안 설정 (선택사항)

### Firewall 설정
```bash
sudo ufw allow 443/tcp
sudo ufw allow 80/tcp
sudo ufw status
```

### SSL 인증서 갱신
```bash
sudo certbot renew --dry-run
```

---

## 📝 환경 변수

필요시 `/home/azamans/webapp/ai-shorts-pro/backend/.env` 수정:

```env
PORT=5555
NODE_ENV=production
REDIS_ENABLED=false

# AI API Keys (나중에 추가)
# GEMINI_API_KEY=your_key
# MINIMAX_API_KEY=your_key
# ELEVENLABS_API_KEY=your_key
```

변경 후 재시작:
```bash
sudo systemctl restart ai-shorts-pro
```

---

## 🎉 완료!

이제 다음 명령어만 실행하면 됩니다:

```bash
sudo bash /home/azamans/webapp/install-ai-shorts-pro.sh
```

**5분 후** 브라우저에서 접속:

```
https://ai-shorts.neuralgrid.kr
```

---

## 📞 지원

- GitHub: https://github.com/hompystory-coder/azamans/pull/1
- 배포 가이드: `/home/azamans/webapp/ai-shorts-pro/DEPLOYMENT.md`
- 로그: `/home/azamans/webapp/ai-shorts-pro/backend/server.log`

---

**Made with ❤️ by NeuralGrid Team**
**Powered by: Nano Banana Pro, Minimax, Gemini TTS, ElevenLabs, FFmpeg**
