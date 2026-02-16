# RSS & Sitemap 재생성 오류 수정

**날짜**: 2026-02-16  
**작성자**: MVC Developer

## 문제 상황

관리자 페이지에서 "RSS 재생성" 및 "Sitemap 재생성" 버튼 클릭 시 "재생성 중 오류가 발생했습니다" 메시지가 표시되는 문제 발생.

## 원인 분석

### 1. `env()` 함수 미정의 오류
```
Fatal error: Call to undefined function env() in RssService.php:19
Fatal error: Call to undefined function env() in SitemapService.php:17
```

**원인**: `init()` 메서드에서 `env('APP_URL')` 호출 시 해당 함수가 정의되지 않음

**발생 위치**:
- `application/libs/RssService.php` line 19
- `application/libs/SitemapService.php` line 17

### 2. `jsonResponse()` 함수 중복 선언 오류
```
Fatal error: Cannot redeclare jsonResponse() (previously declared in _sys.func.php:30)
in helpers.php on line 56
```

**원인**: `jsonResponse()` 함수가 다음 두 파일에서 중복 선언됨
- `application/config/_sys.func.php` (line 30)
- `application/libs/helpers.php` (line 52)

### 3. 파일 경로 불일치
- **RssService**: `ROOTPATH` 사용 (올바름)
- **SitemapService**: `BASE_PATH` 사용 (잘못됨)

## 해결 방법

### 1. env() 함수 호출 제거

#### RssService.php (Line 17-20)
**수정 전**:
```php
if (!self::$baseUrl) {
    self::$baseUrl = defined('ROOTURL') ? ROOTURL : rtrim(env('APP_URL', getConfig('site_url', '')), '/');
}
```

**수정 후**:
```php
if (!self::$baseUrl) {
    self::$baseUrl = defined('ROOTURL') ? ROOTURL : rtrim(getConfig('site_url', ''), '/');
}
```

#### SitemapService.php (Line 15-18)
**수정 전**:
```php
if (!self::$baseUrl) {
    self::$baseUrl = defined('ROOTURL') ? ROOTURL : rtrim(env('APP_URL', getConfig('site_url', '')), '/');
}
```

**수정 후**:
```php
if (!self::$baseUrl) {
    self::$baseUrl = defined('ROOTURL') ? ROOTURL : rtrim(getConfig('site_url', ''), '/');
}
```

### 2. jsonResponse() 중복 선언 제거

#### helpers.php (Line 48-57)
**수정 전**:
```php
/**
 * JSON 응답 헬퍼 함수
 * 
 * @param array $data 응답 데이터
 * @param int $statusCode HTTP 상태 코드
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
```

**수정 후**:
```php
/**
 * JSON 응답 헬퍼 함수
 * 
 * @param array $data 응답 데이터
 * @param int $statusCode HTTP 상태 코드
 */
if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
```

### 3. 파일 경로 통일 (SitemapService.php)

#### generateAll() 메서드 (Line 394-425)
**수정 전**:
```php
$indexFile = BASE_PATH . '/sitemap_index.xml';
$newsFile = BASE_PATH . '/sitemap_news.xml';
$bbsFile = BASE_PATH . '/sitemap_bbs.xml';
```

**수정 후**:
```php
$indexFile = ROOTPATH . '/sitemap_index.xml';
$newsFile = ROOTPATH . '/sitemap_news.xml';
$bbsFile = ROOTPATH . '/sitemap_bbs.xml';
```

## 수정된 파일 목록

1. `application/libs/RssService.php` - env() 제거
2. `application/libs/SitemapService.php` - env() 제거, BASE_PATH → ROOTPATH 변경
3. `application/libs/helpers.php` - function_exists() 조건 추가

## 생성되는 파일

### RSS 파일
- `/home/mvc/rss_index.xml` - RSS 인덱스 (모든 RSS 피드 목록)
- `/home/mvc/rss_bbs.xml` - 게시판 RSS 피드
- `/home/mvc/rss_news.xml` - 뉴스 RSS 피드

### Sitemap 파일
- `/home/mvc/sitemap_index.xml` - Sitemap 인덱스 (모든 sitemap 목록)
- `/home/mvc/sitemap_bbs.xml` - 게시판 Sitemap
- `/home/mvc/sitemap_news.xml` - 뉴스 Sitemap

## API 엔드포인트

### RSS 재생성
```bash
POST /index.php?url=admin/rss
Content-Type: application/x-www-form-urlencoded

action=regenerate
```

**응답 예시**:
```json
{
    "success": true,
    "message": "모든 RSS 피드가 재생성되었습니다.",
    "results": {
        "index": {"success": true, "file": "rss_index.xml"},
        "bbs": {"success": true, "file": "rss_bbs.xml"},
        "news": {"success": true, "file": "rss_news.xml"}
    }
}
```

### Sitemap 재생성
```bash
POST /index.php?url=admin/sitemap
Content-Type: application/x-www-form-urlencoded

action=regenerate
```

**응답 예시**:
```json
{
    "success": true,
    "message": "모든 Sitemap이 재생성되었습니다.",
    "results": {
        "index": {"success": true, "file": "sitemap_index.xml"},
        "news": {"success": true, "file": "sitemap_news.xml"},
        "bbs": {"success": true, "file": "sitemap_bbs.xml"}
    }
}
```

## 관련 커밋

- `2d4e4cc` - fix(helpers): remove duplicate jsonResponse function
- `26100c3` - feat(helpers): add jsonResponse helper function
- `1cda2ec` - fix(rss): add init() call and fix file paths in generateAll method
- `d859b51` - fix(sitemap): add missing generateAll method to SitemapService
- `017410e` - feat(admin): add clear inclusion info for RSS/Sitemap selection
- `81aa515` - feat(admin): integrate pgpopup toast notification system

## 테스트 방법

### 웹 UI 테스트
1. 관리자로 로그인: https://mvc.neuralgrid.kr/admin
2. RSS 설정 페이지 접속: https://mvc.neuralgrid.kr/admin/rss
3. "RSS 재생성" 버튼 클릭
4. 성공 토스트 메시지 확인: "RSS 피드가 재생성되었습니다" (파란색)
5. Sitemap 설정 페이지 접속: https://mvc.neuralgrid.kr/admin/sitemap
6. "Sitemap 재생성" 버튼 클릭
7. 성공 토스트 메시지 확인: "Sitemap이 재생성되었습니다" (파란색)

### 생성된 파일 확인
```bash
cd /home/mvc
ls -lh rss_*.xml sitemap_*.xml
```

### 파일 내용 확인
```bash
# RSS Index 확인
curl https://mvc.neuralgrid.kr/rss_index.xml

# Sitemap Index 확인
curl https://mvc.neuralgrid.kr/sitemap_index.xml
```

## 주의사항

1. **인증 필요**: API 호출 시 관리자 세션 필요 (로그인하지 않으면 302 리다이렉트 발생)
2. **파일 권한**: 생성되는 XML 파일들은 웹 서버 사용자(azamans)가 쓰기 권한을 가져야 함
3. **캐시**: Sitemap 생성 시 `/application/cache/sitemap/` 디렉터리의 캐시 파일이 삭제됨

## 향후 개선 사항

1. CLI 환경에서도 테스트 가능하도록 의존성 로딩 개선
2. 재생성 시 에러 발생 시 더 자세한 에러 메시지 제공
3. 재생성 성공 시 각 파일의 URL과 크기 정보 포함
4. 자동 재생성 스케줄러 추가 (cron)

## 관련 문서

- [PGPOPUP_INTEGRATION.md](./PGPOPUP_INTEGRATION.md) - Toast 알림 시스템
- [SEARCH_ENGINE_REGISTRATION_GUIDE.md](./SEARCH_ENGINE_REGISTRATION_GUIDE.md) - 검색엔진 등록 가이드
- [RSS_SITEMAP_LAYOUT_UPDATE.md](./RSS_SITEMAP_LAYOUT_UPDATE.md) - UI 레이아웃 업데이트
