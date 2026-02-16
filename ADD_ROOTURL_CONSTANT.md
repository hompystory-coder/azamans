# ROOTURL 전역 상수 추가 및 하드코딩 URL 제거

## 📋 작업 일시
- **날짜**: 2026-02-16 13:50
- **상태**: ✅ 완료

## 🎯 목적
하드코딩된 도메인 URL (`https://mvc.neuralgrid.kr`)을 전역 상수 `ROOTURL`로 교체하여 도메인 변경 시 유연하게 대응

## 📊 변경 사항

### 1. ROOTURL 전역 상수 추가 (`index.php`)

#### 추가된 코드
```php
// 루트 URL 상수 정의 (site_config에서 가져오거나 자동 감지)
if (!defined('ROOTURL')) {
    $siteUrl = getConfig('site_url', '');
    if (empty($siteUrl)) {
        // site_url이 없으면 자동 감지
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $siteUrl = $protocol . '://' . $host;
    }
    define('ROOTURL', rtrim($siteUrl, '/'));
}
```

#### 동작 방식
1. **우선순위 1**: `site_config.site_url` 값 사용
2. **우선순위 2**: 없으면 현재 요청의 프로토콜과 호스트로 자동 감지
3. **결과**: `ROOTURL` 상수에 `https://mvc.neuralgrid.kr` 저장 (슬래시 제거)

---

### 2. SEO 헬퍼 수정 (`application/config/_seo_helper.php`)

#### Before
```php
function getDefaultSeoData() {
    // ...
    'image' => getConfig('seo_image', getConfig('site_url', '') . '/public/images/og-default.jpg'),
    // ...
}

function extractFirstImage($html) {
    // ...
    $siteUrl = getConfig('site_url', '');
    if (strpos($imgSrc, '/') === 0) {
        return $siteUrl . $imgSrc;
    }
    // ...
}
```

#### After
```php
function getDefaultSeoData() {
    // 기본 이미지 경로 가져오기
    $seoImage = getConfig('seo_image', '/public/images/og-default.jpg');
    
    // 상대 경로를 절대 경로로 변환
    if (strpos($seoImage, 'http') !== 0) {
        $seoImage = ROOTURL . $seoImage;
    }
    
    $seoData = [
        // ...
        'image' => $seoImage,
        // ...
    ];
}

function extractFirstImage($html) {
    // ...
    // 상대 경로를 절대 경로로 변환
    if (strpos($imgSrc, 'http') !== 0) {
        if (strpos($imgSrc, '/') === 0) {
            return ROOTURL . $imgSrc;
        } else {
            return ROOTURL . '/' . $imgSrc;
        }
    }
    // ...
}
```

**변경점**:
- `getConfig('site_url', '')` → `ROOTURL`
- 상대 경로 이미지를 절대 경로로 자동 변환

---

### 3. SitemapService 수정 (`application/libs/SitemapService.php`)

#### Before
```php
private static function init() {
    if (!self::$baseUrl) {
        self::$baseUrl = rtrim(env('APP_URL', 'https://mvc.neuralgrid.kr'), '/');
    }
    // ...
}
```

#### After
```php
private static function init() {
    if (!self::$baseUrl) {
        self::$baseUrl = defined('ROOTURL') ? ROOTURL : rtrim(env('APP_URL', getConfig('site_url', '')), '/');
    }
    // ...
}
```

**변경점**:
- 하드코딩된 `'https://mvc.neuralgrid.kr'` 제거
- `ROOTURL` 상수 우선 사용
- Fallback: `APP_URL` → `site_url`

---

### 4. OPcache Reset 스크립트 수정 (`public/opcache_reset.php`)

#### Before
```php
echo "\n\n✅ Done! Now try: https://mvc.neuralgrid.kr/admin";
```

#### After
```php
echo "\n\n✅ Done! Cache cleared successfully.";
```

**변경점**: 하드코딩된 도메인 제거

---

### 5. 데이터베이스 수정

#### seo_image 상대 경로로 변경
```sql
-- Before
UPDATE site_config 
SET config_value = 'https://mvc.neuralgrid.kr/public/images/default-avatar.png' 
WHERE config_key = 'seo_image';

-- After
UPDATE site_config 
SET config_value = '/public/images/default-avatar.png' 
WHERE config_key = 'seo_image';
```

**변경점**: 절대 URL → 상대 경로 (ROOTURL과 자동 결합)

---

## 🗄️ 설정 우선순위

### ROOTURL 값 결정
```
1. site_config.site_url (DB 값)
   ↓ (없으면)
2. 자동 감지 (현재 요청의 protocol + host)
```

### 예시
```php
// 1. site_config에 site_url이 있는 경우
site_config.site_url = 'https://mvc.neuralgrid.kr'
→ ROOTURL = 'https://mvc.neuralgrid.kr'

// 2. site_config에 site_url이 없는 경우
$_SERVER['HTTPS'] = 'on'
$_SERVER['HTTP_HOST'] = 'mvc.neuralgrid.kr'
→ ROOTURL = 'https://mvc.neuralgrid.kr'

// 3. 로컬 개발 환경
$_SERVER['HTTP_HOST'] = 'localhost'
→ ROOTURL = 'http://localhost'
```

---

## 📂 수정된 파일

| 파일 | 변경 내용 |
|------|----------|
| `index.php` | ROOTURL 상수 정의 추가 |
| `application/config/_seo_helper.php` | ROOTURL 사용 (2개 함수) |
| `application/libs/SitemapService.php` | ROOTURL 사용 |
| `public/opcache_reset.php` | 하드코딩 URL 제거 |
| DB: `site_config.seo_image` | 절대 URL → 상대 경로 |

---

## 🔍 하드코딩 제거 확인

### Before (하드코딩)
```bash
# 검색 결과
application/config/_seo_helper.php:    'image' => getConfig('seo_image', getConfig('site_url', '') . '/public/images/og-default.jpg'),
application/config/_seo_helper.php:    $siteUrl = getConfig('site_url', '');
application/libs/SitemapService.php:    self::$baseUrl = rtrim(env('APP_URL', 'https://mvc.neuralgrid.kr'), '/');
public/opcache_reset.php:    echo "\n\n✅ Done! Now try: https://mvc.neuralgrid.kr/admin";
```

### After (ROOTURL 사용)
```bash
# 하드코딩된 도메인 제거 확인
grep -r "mvc\.neuralgrid\.kr" --include="*.php" application/ public/

# 결과: 하드코딩 없음 (제외: 주석, 문서)
```

---

## ✨ 장점

### 1. 유연성
- ✅ 도메인 변경 시 DB 한 곳만 수정
- ✅ 로컬 개발 환경에서 자동 감지
- ✅ 프로덕션/스테이징 환경 구분 용이

### 2. 유지보수
- ✅ 하드코딩 제거로 코드 수정 불필요
- ✅ 중앙 관리 (site_config 또는 .env)
- ✅ 일관성 유지

### 3. 멀티 도메인 지원
- ✅ 서브도메인 지원 가능
- ✅ 개발/스테이징/프로덕션 자동 전환
- ✅ 같은 코드베이스로 여러 도메인 운영 가능

---

## 🧪 테스트 방법

### 1. ROOTURL 값 확인
페이지 소스에 임시 코드 추가:
```php
// index.php 하단 또는 _header.php 상단
echo "<!-- ROOTURL: " . ROOTURL . " -->";
```

결과:
```html
<!-- ROOTURL: https://mvc.neuralgrid.kr -->
```

### 2. SEO 이미지 URL 확인
```bash
curl -s https://mvc.neuralgrid.kr/ | grep "og:image"
```

결과:
```html
<meta property="og:image" content="https://mvc.neuralgrid.kr/public/images/default-avatar.png">
```

### 3. 다른 도메인 테스트
```bash
# site_config 수정
UPDATE site_config SET config_value = 'https://example.com' WHERE config_key = 'site_url';

# 결과 확인
curl -s https://mvc.neuralgrid.kr/ | grep "og:image"
# <meta property="og:image" content="https://example.com/public/images/default-avatar.png">
```

---

## 📝 환경별 설정

### 개발 환경 (로컬)
```env
# .env
APP_URL=http://localhost

# site_config (DB)
site_url = http://localhost

# 결과
ROOTURL = http://localhost
```

### 스테이징 환경
```env
# .env
APP_URL=https://staging.example.com

# site_config (DB)
site_url = https://staging.example.com

# 결과
ROOTURL = https://staging.example.com
```

### 프로덕션 환경
```env
# .env
APP_URL=https://mvc.neuralgrid.kr

# site_config (DB)
site_url = https://mvc.neuralgrid.kr

# 결과
ROOTURL = https://mvc.neuralgrid.kr
```

---

## 🔧 도메인 변경 방법

### 방법 1: DB에서 변경 (권장)
```sql
UPDATE site_config 
SET config_value = 'https://new-domain.com' 
WHERE config_key = 'site_url';
```

### 방법 2: .env에서 변경
```env
# .env
APP_URL=https://new-domain.com
```

### 방법 3: 자동 감지 (권장하지 않음)
```sql
-- site_url을 비워두면 자동 감지
UPDATE site_config 
SET config_value = '' 
WHERE config_key = 'site_url';
```

---

## 💡 사용 예시

### PHP 코드에서 ROOTURL 사용
```php
// 절대 URL 생성
$profileImage = ROOTURL . '/uploads/profile/' . $userId . '.jpg';
$apiEndpoint = ROOTURL . '/api/v1/users';
$redirectUrl = ROOTURL . '/member/login';

// 이메일 템플릿
$verifyLink = ROOTURL . '/member/verify?token=' . $token;

// Sitemap
$sitemapUrl = ROOTURL . '/sitemap.xml';
```

### JavaScript에서 사용
```php
<script>
const ROOTURL = '<?php echo ROOTURL; ?>';
const apiUrl = ROOTURL + '/api/v1/posts';
</script>
```

---

## 🎉 결과
**모든 하드코딩된 도메인이 ROOTURL 상수로 교체되었습니다!**

- ✅ 도메인 변경 시 DB 한 곳만 수정
- ✅ 멀티 환경 지원 (개발/스테이징/프로덕션)
- ✅ 자동 감지 기능 지원
- ✅ 코드 수정 불필요

---

## 📚 관련 문서
- [SEO 메타 태그 시스템](SEO_META_TAGS_SYSTEM.md)
- [site_config 설정](../admin/config)
