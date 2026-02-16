# SEO 메타 태그 자동 적용 시스템

## 📋 작업 일시
- **날짜**: 2026-02-16 13:30
- **상태**: ✅ 완료

## 🎯 목적
모든 페이지에 동적 SEO 메타 태그를 자동으로 적용하여 검색 엔진 최적화 및 SNS 공유 최적화

## 📊 주요 기능

### 1. 기본 SEO 메타 태그
- `<title>` - 페이지 제목
- `<meta name="description">` - 페이지 설명
- `<meta name="keywords">` - 키워드
- `<meta name="author">` - 저자
- `<meta name="robots">` - 크롤러 제어
- `<meta name="googlebot">` - 구글봇 설정

### 2. Open Graph (OG) 태그
페이스북, 네이버, X(트위터) 등 SNS 공유 시 사용
- `og:type` - 콘텐츠 타입 (website)
- `og:title` - 공유 제목
- `og:description` - 공유 설명
- `og:image` - 공유 이미지
- `og:url` - 페이지 URL
- `og:site_name` - 사이트명
- `og:locale` - 언어/지역 (ko_KR)

### 3. Twitter Card
X(트위터) 전용 메타 태그
- `twitter:card` - 카드 타입 (summary_large_image)
- `twitter:title` - 트위터 제목
- `twitter:description` - 트위터 설명
- `twitter:image` - 트위터 이미지
- `twitter:site` - 사이트 핸들
- `twitter:creator` - 작성자 핸들

### 4. 기타
- `<link rel="canonical">` - 정규 URL
- `<link rel="icon">` - Favicon
- `<link rel="apple-touch-icon">` - Apple Touch Icon

---

## 🗄️ 데이터베이스 구조

### site_config 테이블 - SEO 설정

| config_key | config_value | config_group | 설명 |
|-----------|--------------|--------------|------|
| seo_title | MVC Framework | seo | 기본 사이트 제목 |
| seo_description | PHP MVC Framework로 구축된 웹사이트 | seo | 기본 설명 |
| seo_keywords | PHP, MVC, Framework, 웹개발 | seo | 기본 키워드 |
| seo_author | Admin | seo | 저자명 |
| seo_image | https://mvc.neuralgrid.kr/public/images/default-avatar.png | seo | 기본 OG 이미지 |
| seo_twitter_handle | @YourTwitter | seo | Twitter 핸들 |
| favicon_ico | /favicon.ico | seo | Favicon ICO |
| favicon_apple | /apple-touch-icon.png | seo | Apple Touch Icon |

---

## 🔧 시스템 구조

### 1. SEO 헬퍼 파일 (`application/config/_seo_helper.php`)

#### 주요 함수

##### `getDefaultSeoData()`
기본 SEO 설정을 site_config에서 가져옴
```php
return [
    'title' => 'MVC Framework',
    'description' => 'PHP MVC Framework',
    'keywords' => 'PHP, MVC, Framework',
    'author' => 'Admin',
    'image' => 'https://mvc.neuralgrid.kr/public/images/default-avatar.png',
    'url' => 'https://mvc.neuralgrid.kr/...',
    'site_name' => 'MVC Framework',
    'twitter_handle' => '@YourTwitter',
    'favicon_ico' => '/favicon.ico',
    'favicon_apple' => '/apple-touch-icon.png'
];
```

##### `getBbsSeoData($bbsId, $postId)`
게시판 글의 SEO 데이터 생성
- 글 제목 → `title`
- 본문 앞 150자 → `description`
- 본문 첫 이미지 → `image`
- 작성자 → `author`

##### `getNewsSeoData($newsId, $postId)`
뉴스 글의 SEO 데이터 생성
- 글 제목 → `title`
- 본문 앞 150자 → `description`
- 본문 첫 이미지 → `image`
- 작성자 → `author`

##### `getPageSeoData($menuTable, $menuId)`
페이지 메뉴의 SEO 데이터 생성
- 메뉴명 → `title`
- 페이지 본문 앞 150자 → `description`
- 페이지 첫 이미지 → `image`

##### `getPageSeoMetaData()`
**자동 감지 함수** - URL 패턴을 분석하여 적절한 SEO 데이터 반환

**URL 패턴 매칭:**
- `/bbs/{bbsId}/view/{postId}` → `getBbsSeoData()`
- `/news/{newsId}/view/{postId}` → `getNewsSeoData()`
- `/page/header/{id}` → `getPageSeoData('header', id)`
- `/page/footer/{id}` → `getPageSeoData('footer', id)`
- 기타 → `getDefaultSeoData()`

##### `extractFirstImage($html)`
HTML 콘텐츠에서 첫 번째 `<img>` 태그의 `src` 추출
- 상대 경로 → 절대 경로로 변환
- 이미지 없으면 → `null` 반환

---

### 2. 헤더 뷰 수정 (`application/views/_header.php`)

#### Before (기본 title만)
```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title ?? 'MVC Framework'); ?></title>
    ...
</head>
```

#### After (동적 SEO 메타 태그)
```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php
    // SEO 메타 데이터 자동 생성
    $seoData = getPageSeoMetaData();
    $pageTitle = xssFilter($title ?? $seoData['title']);
    $siteName = xssFilter($seoData['site_name']);
    $fullTitle = $pageTitle . ' | ' . $siteName;
    ?>
    
    <!-- 기본 SEO -->
    <title><?php echo $fullTitle; ?></title>
    <meta name="description" content="<?php echo xssFilter($seoData['description']); ?>">
    <meta name="keywords" content="<?php echo xssFilter($seoData['keywords']); ?>">
    <meta name="author" content="<?php echo xssFilter($seoData['author']); ?>">
    
    <!-- 크롤러용 -->
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo xssFilter($fullTitle); ?>">
    <meta property="og:description" content="<?php echo xssFilter($seoData['description']); ?>">
    <meta property="og:image" content="<?php echo xssFilter($seoData['image']); ?>">
    <meta property="og:url" content="<?php echo xssFilter($seoData['url']); ?>">
    <meta property="og:site_name" content="<?php echo xssFilter($siteName); ?>">
    <meta property="og:locale" content="ko_KR">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo xssFilter($fullTitle); ?>">
    <meta name="twitter:description" content="<?php echo xssFilter($seoData['description']); ?>">
    <meta name="twitter:image" content="<?php echo xssFilter($seoData['image']); ?>">
    <meta name="twitter:site" content="<?php echo xssFilter($seoData['twitter_handle']); ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo xssFilter($seoData['favicon_ico']); ?>">
    <link rel="apple-touch-icon" href="<?php echo xssFilter($seoData['favicon_apple']); ?>">
    
    <!-- Canonical -->
    <link rel="canonical" href="<?php echo xssFilter($seoData['url']); ?>">
    
    ...
</head>
```

---

### 3. 진입점 수정 (`index.php`)

SEO 헬퍼를 자동 로드하도록 추가:
```php
require_once APP_PATH . '/config/_seo_helper.php';
```

---

## 📝 페이지별 동작 방식

### 1. 일반 페이지 (홈, 소개 등)
**URL**: `https://mvc.neuralgrid.kr/`

**메타 데이터**:
- **Title**: `페이지명 | MVC Framework`
- **Description**: `PHP MVC Framework로 구축된 웹사이트`
- **Keywords**: `PHP, MVC, Framework, 웹개발`
- **Image**: `https://mvc.neuralgrid.kr/public/images/default-avatar.png`
- **URL**: `https://mvc.neuralgrid.kr/`

**데이터 소스**: `site_config` 테이블의 기본 설정

---

### 2. 게시판 글 보기
**URL**: `https://mvc.neuralgrid.kr/bbs/notice/view/1`

**메타 데이터**:
- **Title**: `{게시글 제목} | MVC Framework`
- **Description**: `{본문 내용 앞 150자}...`
- **Keywords**: 기본 키워드
- **Author**: `{작성자 ID}`
- **Image**: `{본문의 첫 번째 이미지 URL}`
- **URL**: `https://mvc.neuralgrid.kr/bbs/notice/view/1`

**데이터 소스**: `bbs_notice` 테이블 (또는 해당 게시판 테이블)

**예시**:
```php
게시글 제목: "2025년 신년 공지"
본문: "<p>안녕하세요. <img src='/uploads/notice.jpg'> 새해 복 많이 받으세요...</p>"

결과:
- Title: "2025년 신년 공지 | MVC Framework"
- Description: "안녕하세요. 새해 복 많이 받으세요..."
- Image: "https://mvc.neuralgrid.kr/uploads/notice.jpg"
```

---

### 3. 뉴스 글 보기
**URL**: `https://mvc.neuralgrid.kr/news/news1/view/2`

**메타 데이터**:
- **Title**: `{뉴스 제목} | MVC Framework`
- **Description**: `{본문 내용 앞 150자}...`
- **Keywords**: 기본 키워드
- **Author**: `{작성자 ID}`
- **Image**: `{본문의 첫 번째 이미지 URL}`
- **URL**: `https://mvc.neuralgrid.kr/news/news1/view/2`

**데이터 소스**: `news_news1` 테이블 (또는 해당 뉴스 테이블)

---

### 4. 페이지 메뉴 (헤더/푸터)
**URL**: 
- `https://mvc.neuralgrid.kr/page/header/2`
- `https://mvc.neuralgrid.kr/page/footer/1`

**메타 데이터**:
- **Title**: `{메뉴명} | MVC Framework`
- **Description**: `{페이지 본문 앞 150자}...`
- **Keywords**: 기본 키워드
- **Image**: `{페이지 본문의 첫 번째 이미지 URL}`
- **URL**: 현재 페이지 URL

**데이터 소스**: 
- `header_menu` 또는 `footer_menu` 테이블
- `menu_pages` 테이블

---

## 🔍 누락 항목 처리 (Fallback)

모든 페이지에서 **누락된 메타 정보는 기본 설정으로 대체**:

| 항목 | 우선순위 1 (페이지별) | 우선순위 2 (기본값) |
|------|---------------------|-------------------|
| Title | 게시글/메뉴명 | site_config.seo_title |
| Description | 본문 앞 150자 | site_config.seo_description |
| Keywords | - | site_config.seo_keywords |
| Author | 작성자 | site_config.seo_author |
| Image | 본문 첫 이미지 | site_config.seo_image |

**예시**:
```
게시글에 이미지가 없으면 → 기본 이미지 사용
본문이 비어있으면 → 기본 설명 사용
```

---

## 🧪 테스트 방법

### 1. 기본 페이지 테스트
1. 메인 페이지 접속: https://mvc.neuralgrid.kr/
2. 소스 보기 (Ctrl+U)
3. `<head>` 섹션 확인
4. OG 태그, Twitter 태그 존재 확인

### 2. 게시판 글 테스트
1. 게시판 글 작성: https://mvc.neuralgrid.kr/bbs/notice
2. 이미지 포함하여 글 작성
3. 글 보기 페이지 접속: `/bbs/notice/view/1`
4. 소스 보기
5. 메타 태그에 글 제목, 본문, 이미지 반영 확인

### 3. 뉴스 글 테스트
1. 뉴스 작성: https://mvc.neuralgrid.kr/news/news1
2. 이미지 포함하여 뉴스 작성
3. 뉴스 보기: `/news/news1/view/2`
4. 메타 태그 확인

### 4. 페이지 메뉴 테스트
1. 헤더 메뉴 수정: https://mvc.neuralgrid.kr/admin/editMenu/2
2. CKEditor로 이미지 포함한 본문 작성
3. 페이지 접속: `/page/header/2`
4. 메타 태그 확인

### 5. SNS 공유 미리보기 테스트
**페이스북 디버거**:
- URL: https://developers.facebook.com/tools/debug/
- 테스트할 페이지 URL 입력
- OG 태그 인식 확인

**Twitter Card Validator**:
- URL: https://cards-dev.twitter.com/validator
- 테스트할 페이지 URL 입력
- Twitter Card 미리보기 확인

---

## ✅ 예상 결과

### HTML 소스 예시 (게시판 글)
```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- 기본 SEO -->
    <title>2025년 신년 공지 | MVC Framework</title>
    <meta name="description" content="안녕하세요. 새해 복 많이 받으세요. 올해도 좋은 일만 가득하시길 바랍니다...">
    <meta name="keywords" content="PHP, MVC, Framework, 웹개발">
    <meta name="author" content="admin">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="2025년 신년 공지 | MVC Framework">
    <meta property="og:description" content="안녕하세요. 새해 복 많이 받으세요...">
    <meta property="og:image" content="https://mvc.neuralgrid.kr/uploads/notice.jpg">
    <meta property="og:url" content="https://mvc.neuralgrid.kr/bbs/notice/view/1">
    <meta property="og:site_name" content="MVC Framework">
    <meta property="og:locale" content="ko_KR">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="2025년 신년 공지 | MVC Framework">
    <meta name="twitter:description" content="안녕하세요. 새해 복 많이 받으세요...">
    <meta name="twitter:image" content="https://mvc.neuralgrid.kr/uploads/notice.jpg">
    
    ...
</head>
```

---

## 🎨 장점

### 1. 자동화
- ✅ 모든 페이지에 자동으로 메타 태그 적용
- ✅ 페이지 타입별로 최적화된 메타 데이터 생성
- ✅ 수동 작업 불필요

### 2. SEO 최적화
- ✅ 구글, 네이버 등 검색 엔진 최적화
- ✅ 크롤러 친화적인 메타 태그
- ✅ Canonical URL로 중복 콘텐츠 방지

### 3. SNS 공유 최적화
- ✅ 페이스북, 네이버에서 예쁜 미리보기
- ✅ X(트위터)에서 Large Image Card 표시
- ✅ 이미지, 제목, 설명 자동 추출

### 4. 유지보수성
- ✅ 기본 설정은 DB에서 관리 (site_config)
- ✅ 페이지별 메타는 자동 생성
- ✅ 한 곳에서 모든 SEO 로직 관리

### 5. 확장성
- ✅ 새로운 페이지 타입 추가 용이
- ✅ 커스텀 메타 데이터 추가 가능
- ✅ 다국어 지원 확장 가능

---

## 📂 수정/생성된 파일

1. `application/config/_seo_helper.php` - **새로 생성** (SEO 헬퍼 함수)
2. `application/views/_header.php` - **수정** (메타 태그 추가)
3. `index.php` - **수정** (SEO 헬퍼 로드)
4. `database/insert_seo_config.sql` - **새로 생성** (SEO 설정 SQL)

---

## 🔗 관리자 페이지 (추후 구현 권장)

### 1. SEO 기본 설정 페이지
**URL**: `https://mvc.neuralgrid.kr/admin/seo`

**설정 항목**:
- 기본 사이트 제목
- 기본 설명
- 기본 키워드
- 저자명
- 기본 OG 이미지 (업로드)
- Twitter 핸들

### 2. 헤더 코드 삽입
**URL**: `https://mvc.neuralgrid.kr/admin/headercode`

**기능**:
- 커스텀 `<head>` 코드 삽입
- Google Analytics 코드
- 기타 트래킹 코드

### 3. Favicon 관리
**URL**: `https://mvc.neuralgrid.kr/admin/favicon`

**기능**:
- Favicon 업로드 (.ico)
- Apple Touch Icon 업로드 (.png)

### 4. 기본 설정
**URL**: `https://mvc.neuralgrid.kr/admin/config`

**기능**:
- 사이트명 설정
- 사이트 URL 설정
- 기타 일반 설정

---

## 📖 참고 자료

- [Open Graph Protocol](https://ogp.me/)
- [Twitter Cards Documentation](https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards)
- [Google SEO Starter Guide](https://developers.google.com/search/docs/fundamentals/seo-starter-guide)
- [Meta Tags 검증 도구](https://metatags.io/)

---

## 🎉 결과
**모든 페이지에 SEO 메타 태그가 자동으로 적용됩니다!**

- ✅ 게시판 글: 제목, 본문, 이미지 자동 추출
- ✅ 뉴스 글: 제목, 본문, 이미지 자동 추출
- ✅ 페이지 메뉴: 메뉴명, 본문, 이미지 자동 추출
- ✅ 누락 항목: 기본 설정으로 자동 대체
- ✅ SNS 공유: 예쁜 미리보기 자동 생성
