# RSS 시스템 구축 진행 상황

## 📋 작업 일시
- **시작**: 2026-02-16 14:20
- **상태**: 🔄 진행 중 (30% 완료)

## 🎯 목표
1. Admin 메뉴에서 "RSS & Sitemap 설정"을 "RSS 설정"과 "Sitemap 설정"으로 분리
2. RSS 자동화 시스템 구축
3. RSS 피드 URL: `rss_index.xml`, `rss_bbs.xml`, `rss_news.xml`

---

## ✅ 완료된 작업

### 1. Admin 사이드바 메뉴 분리 (application/views/admin/_sidebar.php)
```php
// Before
<li><a href="/admin/rss">RSS & Sitemap 설정</a></li>

// After
<li><a href="/admin/rss">RSS 설정</a></li>
<li><a href="/admin/sitemap">Sitemap 설정</a></li>
```

### 2. Admin 컨트롤러에 sitemap() 함수 추가 (application/controller/admin.php)
```php
/**
 * 사이트 설정 - RSS
 */
public function rss($action = null) {
    require_once APP_PATH . '/libs/admin_rss_func.php';
    admin_rss_handler($this, $action);
}

/**
 * 사이트 설정 - Sitemap
 */
public function sitemap($action = null) {
    require_once APP_PATH . '/libs/admin_sitemap_func.php';
    admin_sitemap_handler($this, $action);
}
```

### 3. admin_sitemap_func.php 생성
- `admin_rss_func.php`를 복사하여 `admin_sitemap_func.php` 생성
- Sitemap 전용 함수로 수정
- BBS/News Sitemap 제외 설정 기능
- Sitemap 캐시 무효화 기능

### 4. admin_rss_func.php 수정
- RSS 전용 함수로 수정
- 게시판 RSS 설정 (`save-bbs` 액션)
- 뉴스 RSS 설정 (`save-news` 액션)
- RSS 재생성 기능 (`regenerate` 액션)

---

## 🔄 진행 중인 작업

### 1. RSS 뷰 페이지 작성
**파일**: `application/views/admin/rss.php`

**필요한 내용**:
- RSS 게시판 선택 (체크박스)
- RSS 뉴스 선택 (체크박스)
- 추출 개수 설정 (기본 100개)
- RSS 재생성 버튼
- 생성된 RSS 링크 표시

### 2. Sitemap 뷰 페이지 확인
**파일**: `application/views/admin/sitemap.php`

**확인 사항**:
- 기존 sitemap.php 파일 존재 확인
- Sitemap 전용으로 수정 필요

---

## ⏳ 남은 작업

### 1. RssService 클래스 생성 (High Priority)
**파일**: `application/libs/RssService.php`

**필요한 메소드**:
```php
class RssService {
    // RSS Index 생성
    public static function buildIndex();
    
    // 게시판 RSS 생성
    public static function buildBbs($bbsId = null, $limit = 100);
    
    // 뉴스 RSS 생성
    public static function buildNews($newsId = null, $limit = 100);
    
    // 전체 RSS 생성
    public static function generateAll();
}
```

### 2. RSS 컨트롤러 추가
**파일**: `application/controller/rss.php` (새로 생성)

**필요한 함수**:
```php
class Rss extends Controller {
    // RSS Index: /rss_index.xml
    public function rss_index();
    
    // BBS RSS: /rss_bbs.xml
    public function rss_bbs();
    
    // News RSS: /rss_news.xml
    public function rss_news();
}
```

### 3. RSS XSL 스타일 파일 생성
**파일**: `public/rss.xsl`

**내용**: `/home/mvc/rss-xsl.txt` 참고하여 생성

### 4. RSS 뷰 페이지 완성
**파일**: `application/views/admin/rss.php`

**UI 구성**:
- 게시판 RSS 설정 카드
- 뉴스 RSS 설정 카드
- RSS 링크 표시 영역
- 재생성 버튼

### 5. .htaccess RSS 라우팅 추가
```apache
# RSS 피드
RewriteRule ^rss_index\.xml$ index.php?url=rss/rss_index [L,QSA]
RewriteRule ^rss_bbs\.xml$ index.php?url=rss/rss_bbs [L,QSA]
RewriteRule ^rss_news\.xml$ index.php?url=rss/rss_news [L,QSA]
```

---

## 📊 데이터베이스 설정 (site_config)

### RSS 설정
```sql
-- 게시판 RSS 포함 목록
INSERT INTO site_config (config_key, config_value, config_group, config_description) 
VALUES ('rss_boards', '[]', 'rss', 'RSS 피드에 포함할 게시판 ID 배열');

-- 게시판 RSS 추출 개수
INSERT INTO site_config (config_key, config_value, config_group, config_description) 
VALUES ('rss_bbs_limit', '100', 'rss', '게시판 RSS 최대 항목 수');

-- 뉴스 RSS 포함 목록
INSERT INTO site_config (config_key, config_value, config_group, config_description) 
VALUES ('rss_news', '[]', 'rss', 'RSS 피드에 포함할 뉴스 ID 배열');

-- 뉴스 RSS 추출 개수
INSERT INTO site_config (config_key, config_value, config_group, config_description) 
VALUES ('rss_news_limit', '100', 'rss', '뉴스 RSS 최대 항목 수');
```

---

## 📁 파일 구조

### 생성된 파일
```
/home/mvc/
├── application/
│   ├── controller/
│   │   └── admin.php (수정)
│   ├── libs/
│   │   ├── admin_rss_func.php (수정 - RSS 전용)
│   │   ├── admin_sitemap_func.php (생성 - Sitemap 전용)
│   │   └── RssService.php (예정)
│   └── views/
│       └── admin/
│           ├── _sidebar.php (수정)
│           ├── rss.php (수정 예정)
│           └── sitemap.php (확인 필요)
```

### 생성 예정 파일
```
/home/mvc/
├── application/
│   ├── controller/
│   │   └── rss.php (새로 생성)
│   └── libs/
│       └── RssService.php (새로 생성)
├── public/
│   └── rss.xsl (새로 생성)
└── .htaccess (수정)
```

---

## 🔗 RSS URL 구조

### RSS Index
```
https://mvc.neuralgrid.kr/rss_index.xml

<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex>
  <sitemap>
    <loc>https://mvc.neuralgrid.kr/rss_bbs.xml</loc>
    <lastmod>2026-02-16T00:00:00+00:00</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://mvc.neuralgrid.kr/rss_news.xml</loc>
    <lastmod>2026-02-16T00:00:00+00:00</lastmod>
  </sitemap>
</sitemapindex>
```

### BBS RSS
```
https://mvc.neuralgrid.kr/rss_bbs.xml

<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="/public/rss.xsl"?>
<rss version="2.0">
  <channel>
    <title>MVC Framework - 게시판</title>
    <link>https://mvc.neuralgrid.kr/</link>
    <description>게시판 최신 글</description>
    <item>...</item>
  </channel>
</rss>
```

### News RSS
```
https://mvc.neuralgrid.kr/rss_news.xml

<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="/public/rss.xsl"?>
<rss version="2.0">
  <channel>
    <title>MVC Framework - 뉴스</title>
    <link>https://mvc.neuralgrid.kr/</link>
    <description>뉴스 최신 글</description>
    <item>...</item>
  </channel>
</rss>
```

---

## 📝 참고 파일
- `/home/mvc/rss2.txt` - RSS 자동 생성 예제
- `/home/mvc/rss-xsl.txt` - RSS XSL 스타일 예제

---

## 🎯 다음 단계

### 우선순위 1 (필수)
1. ✅ RssService 클래스 생성 및 구현
2. ✅ RSS 컨트롤러 생성
3. ✅ .htaccess RSS 라우팅 추가

### 우선순위 2 (중요)
4. ✅ RSS 뷰 페이지 완성
5. ✅ RSS XSL 스타일 파일 생성
6. ✅ DB에 RSS 설정 추가

### 우선순위 3 (테스트)
7. ✅ RSS 피드 생성 테스트
8. ✅ RSS 리더기에서 확인
9. ✅ 문서 작성

---

## 💡 참고 사항

### RSS 2.0 필수 요소
```xml
<rss version="2.0">
  <channel>
    <title>채널 제목</title>
    <link>채널 링크</link>
    <description>채널 설명</description>
    <item>
      <title>항목 제목</title>
      <link>항목 링크</link>
      <description>항목 설명</description>
      <pubDate>발행일</pubDate>
      <guid>고유 ID</guid>
    </item>
  </channel>
</rss>
```

### RSS 선택 요소
```xml
<language>ko-KR</language>
<lastBuildDate>마지막 빌드 날짜</lastBuildDate>
<atom:link href="RSS URL" rel="self" type="application/rss+xml" />
<dc:creator>작성자</dc:creator>
<category>카테고리</category>
<content:encoded><![CDATA[HTML 콘텐츠]]></content:encoded>
```

---

## 🔧 상태 요약

| 작업 | 진행률 | 상태 |
|------|--------|------|
| 메뉴 분리 | 100% | ✅ 완료 |
| Admin 컨트롤러 | 100% | ✅ 완료 |
| Admin 함수 파일 | 100% | ✅ 완료 |
| RSS 뷰 페이지 | 0% | ⏳ 대기 |
| RssService 클래스 | 0% | ⏳ 대기 |
| RSS 컨트롤러 | 0% | ⏳ 대기 |
| .htaccess 라우팅 | 0% | ⏳ 대기 |
| RSS XSL 스타일 | 0% | ⏳ 대기 |
| 테스트 | 0% | ⏳ 대기 |

**전체 진행률**: 30% ✅✅✅⬜⬜⬜⬜⬜⬜⬜

---

## 🎉 다음 작업 시작 전 확인사항
1. 현재까지 수정된 파일 확인
2. RssService 클래스 구조 설계
3. RSS 컨트롤러 구조 설계
4. RSS 뷰 페이지 UI 디자인

---

**작업을 계속하려면 다음 명령을 실행하세요:**
```
다음 작업: RssService 클래스 생성 및 RSS 자동 생성 함수 구현
```
