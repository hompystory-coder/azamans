# 🎬 통합 쇼츠 영상 프리뷰 페이지

## ✅ 완성된 기능

서버에 있는 **모든 쇼츠 영상을 한 곳에서** 볼 수 있는 통합 프리뷰 페이지가 완성되었습니다!

---

## 📍 접속 URL

### 🌐 외부 접속 (권장)
```
http://115.91.5.140:3003/preview
```

### 🏠 로컬 접속
```
http://localhost:3003/preview
```

---

## 📊 현재 통계

### 발견된 영상
- **Character Shorts (AI 캐릭터)**: 37개
- **Creator Shorts (사용자 생성)**: 37개
- **전체**: 74개 영상
- **저장 용량**: 0.26 GB

### 스캔 위치
1. `/mnt/music-storage/generated-shorts/videos` - Character Shorts
2. `/mnt/music-storage/shorts-videos/outputs/videos` - Creator Shorts

---

## 🎯 주요 기능

### 1️⃣ 실시간 통계 대시보드
- **Total Videos**: 전체 영상 개수
- **Character Shorts**: AI 캐릭터 쇼츠 개수
- **Creator Shorts**: 사용자 생성 쇼츠 개수
- **Total Size**: 전체 저장 용량 (GB)
- **Characters**: 사용된 캐릭터 종류 수

### 2️⃣ 캐릭터 필터링
- **전체**: 모든 영상 표시
- **10개 AI 캐릭터**:
  - 🦊 똑똑한 여우 (clever-fox)
  - 🐰 행복한 토끼 (happy-rabbit)
  - 🦉 현명한 부엉이 (wise-owl)
  - 🐶 활기찬 강아지 (energetic-dog)
  - 🐱 차분한 고양이 (calm-cat)
  - 🐻 명랑한 곰 (cheerful-bear)
  - 🐧 창의적인 펭귄 (creative-penguin)
  - 🐵 모험가 원숭이 (adventurous-monkey)
  - 🦝 테크 너구리 (tech-raccoon)
  - 🐼 스타일리시 판다 (stylish-panda)
- **🎨 Creator Shorts**: 사용자 생성 영상

### 3️⃣ 비디오 카드 기능
- **영상 미리보기**: 9:16 세로 비율 표시
- **클릭 재생**: 비디오 클릭 시 재생/일시정지
- **제목 표시**: 영상 제목 (없을 경우 '제목 없음')
- **캐릭터 배지**: 어떤 AI 캐릭터가 생성했는지 표시
- **날짜 배지**: 생성 날짜 (한국어 형식)
- **재생 버튼**: 새 탭에서 전체 화면으로 재생
- **다운로드 버튼**: 영상을 로컬에 다운로드

### 4️⃣ 반응형 디자인
- **그리드 레이아웃**: 화면 크기에 따라 자동 조정
- **모바일 최적화**: 스마트폰에서도 완벽하게 작동
- **애니메이션 효과**: 호버 시 부드러운 효과

---

## 🔧 기술 스택

### Backend API
```javascript
GET /api/preview/all-videos
```

**응답 형식:**
```json
{
  "success": true,
  "total": 74,
  "videos": [
    {
      "id": "shorts_shorts_1766558859196_5wnvqd",
      "filename": "shorts_shorts_1766558859196_5wnvqd.mp4",
      "url": "https://mfx.neuralgrid.kr/videos/...",
      "size": 8765432,
      "timestamp": 1766558859.196,
      "date": "2025-12-24T07:03:05.865Z",
      "type": "character-shorts",
      "location": "generated-shorts"
    }
  ],
  "summary": {
    "characterShorts": 37,
    "creatorShorts": 37,
    "totalSize": 279684512,
    "totalSizeGB": "0.26"
  }
}
```

### Frontend
- **HTML5 Video Player**: 네이티브 비디오 재생
- **Vanilla JavaScript**: 프레임워크 없이 순수 JS
- **CSS Grid Layout**: 반응형 그리드 시스템
- **Fetch API**: 비동기 데이터 로딩

### 파일 시스템 스캔
```bash
# Character Shorts 스캔
find /mnt/music-storage/generated-shorts/videos -name "shorts_*.mp4"

# Creator Shorts 스캔
find /mnt/music-storage/shorts-videos/outputs/videos -name "video_*.mp4"
```

---

## 🎨 UI/UX 특징

### 색상 테마
- **그라디언트 배경**: 보라색 → 분홍색 → 주황색
- **글래스모피즘**: 반투명 카드 효과
- **다크 모드 친화적**: 어두운 배경에 밝은 텍스트

### 인터랙션
- **호버 효과**: 카드에 마우스 올릴 때 확대 & 그림자
- **로딩 상태**: "영상 목록을 불러오는 중..." 메시지
- **빈 상태**: "영상이 없습니다." 메시지
- **에러 처리**: "오류가 발생했습니다." 메시지

---

## 📦 배포 정보

### 서비스 위치
- **서버**: `shorts-market` (PM2 프로세스 ID: 24)
- **포트**: 3003
- **스크립트**: `/home/azamans/shorts-market/standalone-server.js`
- **Git 커밋**: `38141bd` (2025-12-24)

### 재시작 명령
```bash
cd /home/azamans/shorts-market
pm2 restart shorts-market
```

### 로그 확인
```bash
pm2 logs shorts-market --lines 50
```

---

## 🚀 다음 단계 (선택사항)

### HTTPS 접속 지원
현재 Nginx가 `shorts.neuralgrid.kr`를 포트 4001로 프록시하고 있어, 
포트 3003에서 실행되는 이 프리뷰 페이지는 IP 주소로만 접근 가능합니다.

**HTTPS로 접속하려면:**
```nginx
# /etc/nginx/sites-enabled/shorts.neuralgrid.kr에 추가
location /preview {
    proxy_pass http://127.0.0.1:3003/preview;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}

location /api/preview {
    proxy_pass http://127.0.0.1:3003/api/preview;
}
```

그러면 다음 URL로 접속 가능:
```
https://shorts.neuralgrid.kr/preview
```

### 추가 기능 아이디어
- 검색 기능 (제목, 캐릭터)
- 정렬 옵션 (최신순, 오래된순, 크기순)
- 페이지네이션 (100개 이상 시)
- 영상 상세 페이지
- 좋아요/북마크 기능
- YouTube 업로드 연동

---

## 📝 사용 예시

### 1. 전체 영상 보기
`http://115.91.5.140:3003/preview` 접속 → "전체" 버튼 클릭

### 2. 특정 캐릭터 필터링
"🦊 여우" 버튼 클릭 → 여우 캐릭터가 생성한 영상만 표시

### 3. 영상 재생
비디오 카드에서 "재생" 버튼 클릭 → 새 탭에서 전체 화면 재생

### 4. 영상 다운로드
"다운로드" 버튼 클릭 → 파일이 로컬에 저장됨

---

## 💡 알려진 이슈

### 영상 개수 불일치
- 파일 시스템: 469개 파일 (temp 파일 포함)
- 실제 완성 영상: 74개
- temp 폴더의 중간 파일들은 제외됨

### 해결 방법
`find` 명령어가 `shorts_*.mp4`와 `video_*.mp4` 패턴만 찾도록 설정하여 
완성된 최종 영상만 표시합니다.

---

## ✅ 테스트 완료

- [x] API 응답 정상 (74 videos)
- [x] 통계 계산 정확 (37 + 37 = 74)
- [x] HTML 페이지 렌더링 정상
- [x] 캐릭터 필터링 작동
- [x] 비디오 재생 가능
- [x] 다운로드 기능 작동
- [x] 외부 IP 접속 가능 (115.91.5.140:3003)
- [x] Git 커밋 완료 (38141bd)

---

## 🎉 완료!

이제 서버의 모든 쇼츠 영상을 한 곳에서 편리하게 볼 수 있습니다!

**즐거운 감상 되세요!** 🎬✨
