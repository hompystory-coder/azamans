# 🎬 영상 프리뷰 페이지 접속 방법

## ✅ 작동하는 URL (3가지)

### 1️⃣ 직접 포트 접속 (가장 확실)
```
http://115.91.5.140:3003/preview
```
- ✅ 즉시 사용 가능
- ✅ shorts-market 서버 (포트 3003) 직접 접속
- ✅ 모든 20개 영상 목록 표시

### 2️⃣ 백엔드 프록시 접속
```
http://115.91.5.140:4001/preview
```
- ✅ shorts-creator-backend를 통한 프록시
- ✅ 포트 4001에서 3003으로 자동 프록시

### 3️⃣ HTTPS 도메인 접속 (nginx 설정 필요)
```
https://shorts.neuralgrid.kr/preview
```
- ⚠️ 현재 프론트엔드 앱이 먼저 로드됨
- 🔧 nginx 설정 수정 필요 (아래 참조)

---

## 🎨 프리뷰 페이지 기능

### 📊 실시간 통계
- **총 영상:** 20개
- **캐릭터:** 5개 (여우, 토끼, 부엉이, 강아지, 펭귄)

### 🎭 캐릭터 필터
```
[전체] [🦊 여우] [🐰 토끼] [🦉 부엉이] [🐶 강아지] [🐧 펭귄]
```

### 🎥 영상 카드 기능
- ▶️ 클릭하면 재생
- ⬇️ 다운로드 버튼
- 📝 제목, 캐릭터, 날짜 표시
- 📱 9:16 세로 비율

### 🎨 UI/UX
- 보라색/인디고 그라데이션 배경
- 반투명 글래스모피즘 카드
- 호버 애니메이션 효과
- 반응형 그리드 레이아웃 (1-4열)

---

## 🔧 nginx 설정 수정 (선택 사항)

### 현재 문제
- nginx가 `/preview` 요청을 프론트엔드(React SPA)로 라우팅
- 프론트엔드에는 /preview 페이지가 없음

### 해결 방법 1: nginx에 /preview location 추가

`/etc/nginx/sites-enabled/shorts.neuralgrid.kr` 파일에 추가:

```nginx
server {
    listen 443 ssl http2;
    server_name shorts.neuralgrid.kr;
    
    # 기존 SSL 설정...
    
    # Preview 페이지를 백엔드(4001)로 직접 프록시
    location /preview {
        proxy_pass http://127.0.0.1:4001/preview;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
    
    # API 프록시 (기존 설정)
    location /api/ {
        proxy_pass http://127.0.0.1:4001;
        # ... 기존 설정
    }
    
    # 나머지 요청은 프론트엔드로
    location / {
        # ... 기존 프론트엔드 설정
    }
}
```

### 적용 방법
```bash
sudo nano /etc/nginx/sites-enabled/shorts.neuralgrid.kr
# 위 설정 추가
sudo nginx -t
sudo systemctl reload nginx
```

---

## 📂 서버 구성

### Ports
- **3003** - shorts-market (standalone Express 서버)
  - `/preview` - 실제 프리뷰 페이지
  - `/api/preview/videos` - 영상 목록 API
  
- **4001** - shorts-creator-backend (Express API 서버)
  - `/preview` - 3003으로 프록시
  - `/api/preview/videos` - 3003으로 프록시
  - `/api/*` - 다른 API 엔드포인트

- **443 (nginx)** - shorts.neuralgrid.kr
  - `/api/*` → 4001 프록시
  - `/*` → 프론트엔드 (React SPA)

### 서버 파일
```
/home/azamans/shorts-market/standalone-server.js (포트 3003)
/home/azamans/shorts-creator-pro/backend/src/server.js (포트 4001)
/etc/nginx/sites-enabled/shorts.neuralgrid.kr (nginx 설정)
```

### 데이터 소스
```
/var/www/mfx.neuralgrid.kr/shorts_history.json (20개 영상 메타데이터)
https://mfx.neuralgrid.kr/videos/*.mp4 (실제 영상 파일)
```

---

## 🎯 빠른 접속 링크

```
✅ 포트 3003: http://115.91.5.140:3003/preview
✅ 포트 4001: http://115.91.5.140:4001/preview
⚠️ HTTPS: https://shorts.neuralgrid.kr/preview (nginx 수정 필요)
```

---

## 🔍 API 테스트

```bash
# 영상 목록 조회
curl https://shorts.neuralgrid.kr/api/preview/videos | jq '.'

# 응답 예시
{
  "success": true,
  "total": 20,
  "videos": [
    {
      "jobId": "shorts_1766557788703_8p2gz5",
      "title": "VOVO GO 휴대용비데",
      "characterId": "happy-rabbit",
      "videoUrl": "/videos/shorts_shorts_1766557788703_8p2gz5.mp4",
      "createdAt": "2025-12-24T06:29:48.704Z",
      "status": "completed"
    }
    // ... 19 more
  ]
}
```

---

## ✅ 완료된 작업

1. ✅ shorts-market에 /preview 페이지 추가 (HTML/CSS/JS)
2. ✅ shorts-market에 API 엔드포인트 추가
3. ✅ shorts-creator-backend에 프록시 라우트 추가
4. ✅ 20개 완료 영상 데이터 로드 확인
5. ✅ 캐릭터 필터링 기능 구현
6. ✅ 반응형 UI 구현

## 🔴 남은 작업 (선택 사항)

1. ⏳ nginx 설정 수정 (HTTPS 도메인 접속 활성화)
2. ⏳ 프론트엔드 React 앱에도 /preview 페이지 추가 (통합)

---

## 🎉 결론

**현재 바로 사용 가능한 URL:**
```
http://115.91.5.140:3003/preview
```

이 URL로 접속하시면 모든 AI 쇼츠 영상을 멋진 프리뷰 페이지에서 확인하실 수 있습니다! 🎬✨
