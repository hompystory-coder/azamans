# MVC Framework - 사용자 가이드

## 🔐 관리자 계정 정보

### 관리자 로그인
- **URL**: https://mvc.neuralgrid.kr/member/login
- **아이디**: `admin`
- **비밀번호**: `admin1234`

### 관리자 페이지 접근
로그인 후 상단 메뉴에서 "관리자" 링크를 클릭하거나 다음 URL로 직접 접근:
- **관리자 대시보드**: https://mvc.neuralgrid.kr/admin

---

## 📚 주요 기능

### 1. 회원 시스템
- **회원가입**: `/member/register`
- **로그인**: `/member/login`
- **로그아웃**: `/member/logout`
- **마이페이지**: `/member/mypage`
  - 프로필 사진 업로드
  - 회원정보 수정
  - 비밀번호 변경
  - 포인트 내역 확인

### 2. 게시판 시스템
- **게시판 목록**: `/bbs/{게시판ID}`
- **게시글 작성**: `/bbs/{게시판ID}/write`
- **게시글 보기**: `/bbs/{게시판ID}/view/{글번호}`
- **파일 첨부**: 게시글 작성 시 이미지 및 파일 업로드 가능
- **댓글**: 게시글에 댓글 작성 가능

### 3. 포인트 시스템
- 게시글 작성: **10 포인트**
- 댓글 작성: **5 포인트**
- 일일 로그인: **2 포인트** (1일 1회)

### 4. 알림 시스템
- 헤더의 🔔 아이콘에서 알림 확인
- 30초마다 자동 업데이트

---

## 🛠️ 관리자 기능

### 대시보드 (`/admin`)
- 실시간 통계 확인
- 최근 회원 목록
- 최근 게시물 목록

### 사이트 설정 (`/admin/config`)
- 사이트 기본 정보 설정
- **소개 페이지 편집**
  - 제목 변경
  - 내용 작성 (HTML 사용 가능)

### 회원 관리 (`/admin/members`)
- 회원 목록 조회
- 회원 정보 수정
- 회원 등급 관리

### 게시판 관리 (`/admin/boards`)
- 게시판 생성/수정/삭제
- 게시판 권한 설정

### 통계 (`/admin/statistics`)
- 기간별 통계 (7일/30일/90일)
- 방문자 추이
- 회원 가입 추이
- 게시물 작성 추이
- 인기 게시물 TOP 10

---

## 🔧 기술 스택

- **백엔드**: PHP 8.3 + MySQL
- **프론트엔드**: HTML5, CSS3, Vanilla JavaScript
- **서버**: Nginx + PHP-FPM
- **라이브러리**: Chart.js (통계 시각화)

---

## 📂 디렉토리 구조

```
/home/mvc/
├── application/
│   ├── config/          # 설정 파일
│   ├── controller/      # 컨트롤러
│   ├── models/          # 모델
│   ├── views/           # 뷰
│   └── libs/            # 라이브러리
├── public/
│   ├── css/            # 스타일시트
│   ├── js/             # JavaScript
│   ├── images/         # 이미지
│   └── uploads/        # 업로드 파일
├── .env                # 환경설정
├── .env.example        # 환경설정 템플릿
├── index.php           # 진입점
└── database.sql        # DB 스키마
```

---

## 🚀 PHP-FPM 재시작 (필요시)

변경사항 적용을 위해 PHP-FPM을 재시작하려면:

```bash
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

---

## 📋 기본 게시판

다음 게시판이 기본으로 생성되어 있습니다:
- **공지사항** (`/bbs/notice`)
- **자유게시판** (`/bbs/free`)
- **질문과답변** (`/bbs/qna`)

---

## 🔒 보안 기능

- XSS 방어
- CSRF 토큰
- SQL Injection 방지
- Rate Limiting (로그인 시도 제한)
- 파일 업로드 검증

---

## 📞 지원

문제가 발생하면 다음을 확인하세요:

1. **PHP 에러 로그**: `/var/log/php8.3-fpm.log`
2. **Nginx 에러 로그**: `/var/log/nginx/mvc_error.log`
3. **디버그 모드**: `.env` 파일에서 `APP_DEBUG=true` 설정

---

## 📝 변경 이력

### 2026-02-01
- ✅ 로그인/회원가입 페이지 구현
- ✅ 소개 페이지 구현 (관리자 편집 가능)
- ✅ 파일 업로드 기능
- ✅ 댓글 시스템
- ✅ 프로필 사진 업로드
- ✅ 포인트 시스템
- ✅ 알림 시스템
- ✅ 관리자 통계
- ✅ 환경설정 시스템 (.env)

---

**🎉 모든 기능이 정상 작동합니다!**
