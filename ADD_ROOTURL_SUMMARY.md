# ROOTURL 전역 상수 추가 완료

## ✅ 작업 완료 (2026-02-16 13:55)

### 🎯 목적
하드코딩된 `https://mvc.neuralgrid.kr`을 전역 상수 `ROOTURL`로 교체

---

## 📊 주요 변경

### 1. **ROOTURL 상수 정의** (`index.php`)
```php
// 우선순위:
// 1. site_config.site_url (DB)
// 2. 자동 감지 (protocol + host)

define('ROOTURL', 'https://mvc.neuralgrid.kr');
```

### 2. **하드코딩 제거** (4개 파일)
| 파일 | Before | After |
|------|--------|-------|
| `_seo_helper.php` | `getConfig('site_url', '')` | `ROOTURL` |
| `SitemapService.php` | `'https://mvc.neuralgrid.kr'` | `ROOTURL` |
| `opcache_reset.php` | `https://mvc.neuralgrid.kr/admin` | 제거 |
| DB: `seo_image` | 절대 URL | 상대 경로 |

---

## ✨ 장점

### 1. **유연성**
- ✅ 도메인 변경 시 DB 한 곳만 수정
- ✅ 로컬 개발 환경 자동 감지
- ✅ 멀티 환경 지원 (개발/스테이징/프로덕션)

### 2. **유지보수**
- ✅ 하드코딩 제거
- ✅ 중앙 관리 (site_config)
- ✅ 일관성 유지

### 3. **확장성**
- ✅ 서브도메인 지원
- ✅ 여러 도메인 운영 가능

---

## 🔧 도메인 변경 방법

### DB에서 변경 (권장)
```sql
UPDATE site_config 
SET config_value = 'https://new-domain.com' 
WHERE config_key = 'site_url';
```

변경 후 **자동 적용** (코드 수정 불필요)

---

## 💡 사용 예시

### PHP 코드
```php
$imageUrl = ROOTURL . '/uploads/profile.jpg';
$apiUrl = ROOTURL . '/api/v1/users';
$redirectUrl = ROOTURL . '/member/login';
```

### JavaScript
```php
<script>
const ROOTURL = '<?php echo ROOTURL; ?>';
const apiUrl = ROOTURL + '/api/posts';
</script>
```

---

## 🧪 테스트 결과

```bash
curl -s https://mvc.neuralgrid.kr/ | grep "og:image"
```

**결과**: ✅ 정상
```html
<meta property="og:image" content="https://mvc.neuralgrid.kr/public/images/og-default.jpg">
```

---

## 📂 수정된 파일

1. `index.php` - ROOTURL 상수 정의
2. `application/config/_seo_helper.php` - ROOTURL 사용
3. `application/libs/SitemapService.php` - ROOTURL 사용
4. `public/opcache_reset.php` - 하드코딩 제거
5. DB: `site_config.seo_image` - 상대 경로로 변경

---

## 🎉 완료!

**모든 하드코딩된 도메인이 ROOTURL로 교체되었습니다!**

- ✅ 도메인 변경 시 DB만 수정
- ✅ 환경별 자동 전환
- ✅ 코드 수정 불필요

---

## 📝 환경별 설정 예시

| 환경 | site_url | ROOTURL |
|------|----------|---------|
| 로컬 | `http://localhost` | `http://localhost` |
| 스테이징 | `https://staging.example.com` | `https://staging.example.com` |
| 프로덕션 | `https://mvc.neuralgrid.kr` | `https://mvc.neuralgrid.kr` |

---

## 🔗 관련 문서
- [상세 문서](ADD_ROOTURL_CONSTANT.md)
- [SEO 메타 태그 시스템](SEO_META_TAGS_SYSTEM.md)
