# YouTube 트렌드 분석기 - 빠른 시작 가이드

## 🚀 5분 안에 시작하기

### 1단계: API 키 발급 (3분)

1. https://console.cloud.google.com/ 접속
2. 새 프로젝트 생성
3. "YouTube Data API v3" 검색 후 활성화
4. 사용자 인증 정보 > API 키 생성
5. 생성된 API 키 복사

### 2단계: 설치 (1분)

```bash
# 백엔드 설치
cd youtube-trend-analyzer/backend
npm install

# 환경 변수 설정
echo "PORT=5000
YOUTUBE_API_KEY=여기에_API_키_붙여넣기
AUTO_SEARCH_ENABLED=true
SEARCH_TIME=06:00" > .env

# 프론트엔드 설치
cd ../frontend
npm install
```

### 3단계: 실행 (1분)

**터미널 1:**
```bash
cd backend
npm start
```

**터미널 2:**
```bash
cd frontend
npm run dev
```

### 4단계: 사용

1. 브라우저에서 http://localhost:3000 접속
2. ⚙️ 설정 탭에서 API 키 입력 후 저장
3. 🔍 수동 검색 탭에서 키워드 입력 후 검색
4. 📹 영상 목록에서 결과 확인

## ✅ 완료!

이제 매일 오전 6시에 자동으로 유튜브 영상이 검색됩니다!

## 🎯 자동 검색 키워드

시스템이 매일 자동으로 검색하는 키워드:
- ✅ 시니어대상 한국 시니어 사연
- ✅ 한국 시니어 대상 해외감동사연
- ✅ 한국시니어대상 극복
- ✅ 한국시니어 대상 북한
- ✅ 한국 시니어 대상 디지털정보

각 키워드당 200개 영상, 총 1,000개의 최신 인기 영상이 자동 수집됩니다!
