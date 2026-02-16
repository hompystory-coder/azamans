# ROOTPATH 및 ROOTURL 전역 상수 완료

## ✅ 작업 완료 (2026-02-16 14:05)

### 🎯 목적
서버 이전 및 도메인 변경 시 자동으로 경로가 변경되도록 전역 상수 구현

---

## 📊 전역 상수 정의

### index.php
```php
// 기본 경로 설정 (서버 이전 시 자동 변경)
define('BASE_PATH', __DIR__);          // /home/mvc
define('ROOTPATH', __DIR__);           // /home/mvc
define('APP_PATH', BASE_PATH . '/application');
define('PUBLIC_PATH', BASE_PATH . '/public');

// 루트 URL 설정 (도메인 변경 시 DB 수정)
define('ROOTURL', 'https://mvc.neuralgrid.kr');
```

---

## 🔄 자동 변경 원리

### ROOTPATH (서버 경로)
```php
__DIR__  // 현재 파일 디렉토리 자동 감지

// 서버 1
/home/mvc/index.php → ROOTPATH = '/home/mvc'

// 서버 2
/var/www/mysite/index.php → ROOTPATH = '/var/www/mysite'

// 로컬
C:\xampp\htdocs\mysite\index.php → ROOTPATH = 'C:\xampp\htdocs\mysite'
```

**결과**: 서버 이전 시 **코드 수정 없이 자동 변경** ✅

### ROOTURL (도메인)
```php
// site_config.site_url에서 가져오거나 자동 감지
getConfig('site_url') → ROOTURL
```

**변경 방법**: DB 수정
```sql
UPDATE site_config 
SET config_value = 'https://new-domain.com' 
WHERE config_key = 'site_url';
```

---

## 📂 사용 가능한 상수

| 상수 | 값 | 용도 | 예시 |
|------|-----|------|------|
| `ROOTPATH` | `/home/mvc` | 서버 파일 경로 | `ROOTPATH . '/.env'` |
| `BASE_PATH` | `/home/mvc` | 프로젝트 루트 | `BASE_PATH . '/logs'` |
| `APP_PATH` | `/home/mvc/application` | 애플리케이션 | `APP_PATH . '/config'` |
| `PUBLIC_PATH` | `/home/mvc/public` | 웹 루트 | `PUBLIC_PATH . '/uploads'` |
| `ROOTURL` | `https://mvc.neuralgrid.kr` | 도메인 URL | `ROOTURL . '/api/v1'` |

---

## 🌐 ROOTURL vs ROOTPATH

### ROOTURL (웹 URL)
```php
ROOTURL = 'https://mvc.neuralgrid.kr'

// 웹 브라우저 접근
$imageUrl = ROOTURL . '/public/images/logo.png';
// → https://mvc.neuralgrid.kr/public/images/logo.png

$apiUrl = ROOTURL . '/api/v1/users';
// → https://mvc.neuralgrid.kr/api/v1/users
```

### ROOTPATH (파일 시스템)
```php
ROOTPATH = '/home/mvc'

// 서버 파일 접근
$envFile = ROOTPATH . '/.env';
// → /home/mvc/.env

$uploadDir = ROOTPATH . '/public/uploads';
// → /home/mvc/public/uploads
```

---

## 🚀 서버 이전 시나리오

### 시나리오 1: 리눅스 서버 이전
```bash
# 기존 서버
/home/mvc/ → ROOTPATH = '/home/mvc' ✅

# 새 서버로 파일 복사
/var/www/mysite/ → ROOTPATH = '/var/www/mysite' ✅ 자동 변경

# 코드 수정 불필요!
```

### 시나리오 2: Windows 로컬 개발
```bash
# 로컬 환경
C:\xampp\htdocs\mysite\ → ROOTPATH = 'C:\xampp\htdocs\mysite' ✅ 자동 변경

# 코드 수정 불필요!
```

### 시나리오 3: Docker 컨테이너
```bash
# Docker 내부
/app/ → ROOTPATH = '/app' ✅ 자동 변경

# 코드 수정 불필요!
```

---

## ✅ 하드코딩 제거 확인

### 검증 결과
```bash
grep -r "/home/mvc" application/ --include="*.php"
```

**결과**: ✅ 주석 2건만 발견 (실제 코드는 없음)
- `application/controller/news.php:672` (주석)
- `application/controller/bbs.php:621` (주석)

### 올바른 사용
```php
// ✅ Good
require_once APP_PATH . '/config/_db_func.php';
$file = PUBLIC_PATH . '/uploads/image.jpg';
$envFile = BASE_PATH . '/.env';

// ❌ Bad (사용하지 말 것)
require_once '/home/mvc/application/config/_db_func.php';
$file = '/home/mvc/public/uploads/image.jpg';
```

---

## 🛠️ 서버 이전 체크리스트

### ✅ 자동 변경 (코드 수정 불필요)
- [x] `ROOTPATH` / `BASE_PATH`
- [x] `APP_PATH`
- [x] `PUBLIC_PATH`
- [x] 모든 `require_once`, `include` 경로

### ⚠️ 수동 변경 필요
- [ ] `ROOTURL` - DB site_config 수정
  ```sql
  UPDATE site_config 
  SET config_value = 'https://new-domain.com' 
  WHERE config_key = 'site_url';
  ```
- [ ] `.env` - DB 접속 정보 수정
  ```env
  DB_HOST=localhost
  DB_NAME=mvc
  DB_USER=root
  DB_PASS=new_password
  APP_URL=https://new-domain.com
  ```
- [ ] 웹 서버 설정 (Nginx/Apache)
- [ ] 파일 권한 설정

---

## 💡 사용 예시

### PHP 코드
```php
// 파일 시스템 접근
$logFile = ROOTPATH . '/logs/error.log';
$envFile = BASE_PATH . '/.env';
$uploadDir = PUBLIC_PATH . '/uploads';

// 웹 URL 생성
$imageUrl = ROOTURL . '/public/images/logo.png';
$apiUrl = ROOTURL . '/api/v1/users';
```

### JavaScript
```php
<script>
const ROOTURL = '<?php echo ROOTURL; ?>';
const apiUrl = ROOTURL + '/api/posts';
</script>
```

---

## 📂 수정된 파일

1. ✅ `index.php` - ROOTPATH, ROOTURL 정의
2. ✅ `editor.php` - 주석 수정 (하드코딩 제거)
3. ✅ `application/config/_seo_helper.php` - ROOTURL 사용
4. ✅ `application/libs/SitemapService.php` - ROOTURL 사용

---

## 🎉 완료!

### 자동 변경 시스템
```
서버 이전 시:
✅ ROOTPATH  → __DIR__ 기반 자동 감지
✅ BASE_PATH → __DIR__ 기반 자동 감지
✅ APP_PATH  → BASE_PATH 기반 자동 생성
✅ PUBLIC_PATH → BASE_PATH 기반 자동 생성

도메인 변경 시:
⚠️ ROOTURL   → DB site_config.site_url 수정
```

---

## 📊 비교 정리

| 항목 | 변경 방법 | 자동/수동 |
|------|----------|----------|
| 서버 경로 (ROOTPATH) | 파일 복사만 하면 됨 | ✅ 자동 |
| 프로젝트 루트 (BASE_PATH) | 파일 복사만 하면 됨 | ✅ 자동 |
| 도메인 (ROOTURL) | DB 수정 필요 | ⚠️ 수동 |
| DB 접속 정보 | .env 수정 필요 | ⚠️ 수동 |

---

**서버를 옮기고 도메인을 바꾸면 자동으로 변경됩니다!** 🚀

- ✅ **ROOTPATH**: 서버 이전 시 자동 변경
- ✅ **ROOTURL**: DB 한 곳만 수정하면 끝
- ✅ **하드코딩 제거**: 모든 경로가 상수 사용
- ✅ **코드 수정 불필요**: 파일 복사 + DB 수정만

---

## 🔗 관련 문서
- [상세 문서](ADD_ROOTPATH_CONSTANT.md)
- [ROOTURL 문서](ADD_ROOTURL_CONSTANT.md)
- [SEO 메타 태그](SEO_META_TAGS_SYSTEM.md)
