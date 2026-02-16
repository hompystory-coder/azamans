# getCurrentUrl() 중복 선언 오류 수정

## 📋 작업 일시
- **날짜**: 2026-02-16 13:40
- **상태**: ✅ 완료

## 🐛 문제
```
Fatal error: Cannot redeclare getCurrentUrl() 
(previously declared in /home/mvc/application/config/_sys.func.php:12) 
in /home/mvc/application/config/_seo_helper.php on line 38
```

## 🔍 원인
`getCurrentUrl()` 함수가 두 곳에서 중복 선언됨:
1. `application/config/_sys.func.php` (기존)
2. `application/config/_seo_helper.php` (새로 추가)

## ✅ 해결
`_seo_helper.php`에서 `getCurrentUrl()` 함수 정의 제거

### Before
```php
/**
 * 현재 URL 가져오기
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return $protocol . '://' . $host . $uri;
}

/**
 * 게시판 글 보기 페이지의 SEO 데이터 생성
 */
```

### After
```php
/**
 * 게시판 글 보기 페이지의 SEO 데이터 생성
 */
```

## 🧪 테스트 결과

### HTML 출력 확인
```bash
curl -s https://mvc.neuralgrid.kr/ | grep -A 30 "<head>"
```

**결과**: ✅ 정상 출력
```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- 기본 SEO -->
    <title>메인 페이지 | MVC Framework</title>
    <meta name="description" content="PHP MVC Framework">
    <meta name="keywords" content="PHP, MVC, Framework">
    <meta name="author" content="Admin">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="메인 페이지 | MVC Framework">
    <meta property="og:description" content="PHP MVC Framework">
    <meta property="og:image" content="https://mvc.neuralgrid.kr/public/images/og-default.jpg">
    <meta property="og:url" content="https://mvc.neuralgrid.kr/">
    <meta property="og:site_name" content="MVC Framework">
    <meta property="og:locale" content="ko_KR">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="메인 페이지 | MVC Framework">
    <meta name="twitter:description" content="PHP MVC Framework">
    <meta name="twitter:image" content="https://mvc.neuralgrid.kr/public/images/og-default.jpg">
    <meta name="twitter:site" content="@YourTwitter">
    ...
</head>
```

## 📂 수정된 파일
- `application/config/_seo_helper.php` - `getCurrentUrl()` 함수 제거

## 🎉 결과
SEO 메타 태그 시스템이 정상 작동합니다!
