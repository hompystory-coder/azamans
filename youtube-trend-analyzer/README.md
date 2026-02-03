# 📊 YouTube 트렌드 분석 자동화 시스템

시니어 대상 유튜브 영상을 자동으로 검색하고 정리하는 웹 기반 시스템입니다.

## 🎯 주요 기능

### ✅ 자동 검색
- **실행 시간**: 매일 오전 6시 자동 실행
- **검색 기준**: 최근 1개월 이내 영상
- **수집 개수**: 각 키워드당 200개 (조회수 순)

### 📋 검색 키워드 (자동)
1. 시니어대상 한국 시니어 사연
2. 한국 시니어 대상 해외감동사연
3. 한국시니어대상 극복
4. 한국시니어 대상 북한
5. 한국 시니어 대상 디지털정보

### 📊 수집 정보
- 채널명
- 영상 제목
- 영상 URL
- 썸네일 이미지
- 조회수
- 채널 구독자 수
- 영상 설명
- 업로드 날짜

### 🌟 추가 기능
- 실시간 통계 대시보드
- 키워드별 필터링
- 수동 검색 기능
- 검색 로그 조회
- 페이지네이션
- 반응형 UI 디자인

## 🚀 설치 및 실행

### 🌐 서브도메인 배포 (추천)

서브도메인으로 바로 배포하려면:

```bash
cd /home/azamans/webapp/youtube-trend-analyzer
sudo bash setup-youtube-trend.sh
```

자세한 배포 가이드: [DEPLOYMENT.md](DEPLOYMENT.md)

**접속 URL**: http://youtube-trend.neuralgrid.app

---

### 💻 로컬 개발 환경

#### 1️⃣ 사전 요구사항
- Node.js 18.x 이상
- npm 또는 yarn

#### 2️⃣ YouTube API 키 발급

1. [Google Cloud Console](https://console.cloud.google.com/) 접속
2. 새 프로젝트 생성
3. **YouTube Data API v3** 활성화
   - API 및 서비스 > 라이브러리
   - "YouTube Data API v3" 검색 및 활성화
4. **사용자 인증 정보** 생성
   - API 및 서비스 > 사용자 인증 정보
   - "사용자 인증 정보 만들기" > "API 키" 선택
5. 생성된 API 키 복사

#### 3️⃣ 프로젝트 설치

```bash
# 프로젝트 디렉토리로 이동
cd youtube-trend-analyzer

# 백엔드 설치
cd backend
npm install

# 환경 변수 설정
cp .env.example .env
# .env 파일을 열어 YOUTUBE_API_KEY에 발급받은 API 키 입력

# 프론트엔드 설치
cd ../frontend
npm install
```

#### 4️⃣ 개발 서버 실행

**터미널 1 - 백엔드 서버:**
```bash
cd backend
npm start
```
서버가 http://localhost:5000 에서 실행됩니다.

**터미널 2 - 프론트엔드:**
```bash
cd frontend
npm run dev
```
웹 애플리케이션이 http://localhost:3000 에서 실행됩니다.

#### 프로덕션 빌드

```bash
# 프론트엔드 빌드
cd frontend
npm run build

# 프로덕션 서버는 백엔드만 실행
cd ../backend
npm start
```

## 📖 사용 방법

### 1. API 키 설정
1. 웹 브라우저에서 http://localhost:3000 접속
2. **⚙️ 설정** 탭 클릭
3. YouTube API Key 입력 후 **저장** 버튼 클릭

### 2. 수동 검색
1. **🔍 수동 검색** 탭 클릭
2. 검색할 키워드 입력
3. API 키 입력 (설정에서 저장한 경우 자동 입력됨)
4. **검색 시작** 버튼 클릭
5. 검색 완료 후 **📹 영상 목록** 탭에서 결과 확인

### 3. 자동 검색
- 서버가 실행 중이면 매일 오전 6시에 자동으로 5개 키워드 검색
- 검색 결과는 자동으로 데이터베이스에 저장됨
- **📝 검색 로그** 탭에서 실행 이력 확인 가능

### 4. 영상 목록 조회
1. **📹 영상 목록** 탭에서 수집된 영상 확인
2. 키워드 드롭다운으로 필터링
3. 영상 카드 클릭 시 YouTube에서 영상 재생
4. 페이지네이션으로 많은 데이터 탐색

### 5. 통계 확인
- 상단 대시보드에서 실시간 통계 확인
  - 총 영상 수
  - 검색 키워드 수
  - 총 검색 횟수
  - 마지막 검색 시간

## 🗂️ 프로젝트 구조

```
youtube-trend-analyzer/
├── backend/                    # 백엔드 서버
│   ├── server.js              # Express 서버 + API + 스케줄러
│   ├── package.json           # 백엔드 의존성
│   ├── .env                   # 환경 변수 (API 키)
│   └── youtube-trends.db      # SQLite 데이터베이스 (자동 생성)
├── frontend/                   # 프론트엔드
│   ├── src/
│   │   ├── App.jsx            # 메인 React 컴포넌트
│   │   ├── App.css            # 스타일시트
│   │   └── main.jsx           # React 엔트리 포인트
│   ├── package.json           # 프론트엔드 의존성
│   └── vite.config.js         # Vite 설정
└── README.md                   # 이 파일
```

## 🔌 API 엔드포인트

### GET /api/settings
설정 조회

### POST /api/settings
설정 업데이트
```json
{
  "key": "youtube_api_key",
  "value": "YOUR_API_KEY"
}
```

### POST /api/search
수동 검색 실행
```json
{
  "keyword": "검색어",
  "apiKey": "YOUR_API_KEY"
}
```

### GET /api/videos
영상 목록 조회 (페이지네이션)
- Query params: `keyword`, `page`, `limit`

### GET /api/keywords
등록된 키워드 목록 조회

### GET /api/logs
검색 로그 조회

### GET /api/stats
통계 데이터 조회

### DELETE /api/videos
영상 삭제
- Query params: `keyword` (선택사항)

## 🛠️ 기술 스택

### 백엔드
- **Node.js** - JavaScript 런타임
- **Express** - 웹 프레임워크
- **better-sqlite3** - SQLite 데이터베이스
- **node-cron** - 스케줄러
- **axios** - HTTP 클라이언트
- **YouTube Data API v3** - 유튜브 데이터 수집

### 프론트엔드
- **React** - UI 라이브러리
- **Vite** - 빌드 도구
- **axios** - API 통신
- **date-fns** - 날짜 포맷팅

## ⚙️ 환경 변수

`backend/.env` 파일 설정:

```env
PORT=5000
YOUTUBE_API_KEY=your_youtube_api_key_here
AUTO_SEARCH_ENABLED=true
SEARCH_TIME=06:00
```

## 📊 데이터베이스 스키마

### videos 테이블
- `id`: 고유 ID
- `video_id`: YouTube 영상 ID (UNIQUE)
- `keyword`: 검색 키워드
- `channel_name`: 채널명
- `video_title`: 영상 제목
- `video_url`: 영상 URL
- `thumbnail_url`: 썸네일 URL
- `view_count`: 조회수
- `subscriber_count`: 구독자 수
- `description`: 영상 설명
- `published_at`: 업로드 날짜
- `searched_at`: 검색 날짜

### search_logs 테이블
- `id`: 고유 ID
- `keyword`: 검색 키워드
- `video_count`: 찾은 영상 수
- `status`: 검색 상태 (success/error)
- `searched_at`: 검색 시간

### settings 테이블
- `id`: 고유 ID
- `key`: 설정 키
- `value`: 설정 값

## 🚨 주의사항

1. **API 할당량**
   - YouTube Data API는 일일 할당량이 있습니다 (기본 10,000 units)
   - 검색 1회당 약 100 units 소비
   - 5개 키워드 × 200개 영상 = 약 500 units/일

2. **데이터 중복**
   - 동일한 영상이 여러 키워드로 검색될 수 있습니다
   - 데이터베이스는 `video_id`로 중복 방지

3. **서버 실행**
   - 자동 검색 기능을 사용하려면 서버가 24시간 실행되어야 합니다
   - PM2나 systemd로 서버를 데몬으로 실행하는 것을 권장합니다

## 🔧 문제 해결

### 포트 충돌
백엔드나 프론트엔드 포트가 이미 사용 중인 경우:
- 백엔드: `.env`에서 `PORT` 변경
- 프론트엔드: `vite.config.js`에서 `server.port` 변경

### API 키 오류
- API 키가 올바른지 확인
- YouTube Data API v3가 활성화되어 있는지 확인
- API 할당량을 초과하지 않았는지 확인

### 데이터베이스 오류
- `backend/youtube-trends.db` 파일 삭제 후 서버 재시작
- 데이터베이스가 자동으로 재생성됩니다

## 📝 라이선스

MIT License

## 👨‍💻 개발자

YouTube Trend Analyzer - 2026

## 🙏 감사의 말

이 프로젝트는 시니어 콘텐츠 제작자들을 위한 트렌드 분석 도구로 개발되었습니다.
