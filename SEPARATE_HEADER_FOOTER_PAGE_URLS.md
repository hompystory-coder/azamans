# 헤더/푸터 페이지 메뉴 URL 구분

## 📋 작업 일시
- **날짜**: 2026-02-16 13:10
- **상태**: ✅ 완료

## 🎯 문제점
헤더 메뉴와 푸터 메뉴의 페이지 타입이 동일한 URL(`/page/1`)을 사용하여 구분이 불가능했음

## ✨ 해결 방법
URL에 메뉴 타입(header/footer) 구분자를 추가하여 명확히 구분

### 변경된 URL 패턴

#### Before
- 헤더 페이지: `/page/1` ❌
- 푸터 페이지: `/page/1` ❌
- **문제**: 같은 URL이라 어디로 가야할지 불명확

#### After
- 헤더 페이지: `/page/header/1` ✅
- 푸터 페이지: `/page/footer/1` ✅
- **장점**: URL만 봐도 헤더/푸터 구분 가능

## 📊 수정된 파일

### 1. 페이지 컨트롤러 (`application/controller/page.php`)

#### 함수 시그니처 변경
```php
// Before
public function index($menuId = null)

// After  
public function index($menuTable = null, $menuId = null)
```

#### 주요 로직
```php
// menuTable 검증 (header 또는 footer만 허용)
if (!in_array($menuTable, ['header', 'footer'])) {
    $this->show404();
    return;
}

// 메뉴 테이블명 결정
$tableName = $menuTable . '_menu'; // header_menu 또는 footer_menu

// 메뉴 정보 조회
$menu = getUidData("SELECT * FROM {$tableName} WHERE id = ? AND menu_type = 'page'", [$menuId]);

// 페이지 콘텐츠 조회 (menu_table로 구분)
$page = getUidData("
    SELECT content FROM menu_pages 
    WHERE menu_id = ? AND menu_table = ?
", [$menuId, $menuTable]);

// 첨부파일 조회 (menu_table로 구분)
$pageFiles = getDbArray("
    SELECT * FROM menu_page_upload 
    WHERE menu_id = ? AND menu_table = ? 
    ORDER BY uid ASC
", [$menuId, $menuTable]);
```

#### 파일 경로 변경
```php
// Before
$pageFilePath = BASE_PATH . '/public/uploads/page/' . $menuId . '.php';

// After
$pageFilePath = BASE_PATH . '/public/uploads/page/' . $menuTable . '_' . $menuId . '.php';
```

예시:
- 헤더 메뉴 ID 2: `/public/uploads/page/header_2.php`
- 푸터 메뉴 ID 1: `/public/uploads/page/footer_1.php`

### 2. 헤더 뷰 (`application/views/_header.php`)

```php
// Before
case 'page':
    $menuUrl = '/page/' . $headerMenu['id'];
    break;

// After
case 'page':
    $menuUrl = '/page/header/' . $headerMenu['id'];
    break;
```

### 3. 푸터 뷰 (`application/views/_footer.php`)

```php
// Before
case 'page':
    $menuUrl = '/page/' . $footerMenu['id'];
    break;

// After
case 'page':
    $menuUrl = '/page/footer/' . $footerMenu['id'];
    break;
```

### 4. 헤더 메뉴 관리 함수 (`application/libs/admin_header_menu_func.php`)

3곳 수정:
```php
// 1. 메뉴 삭제 시 PHP 파일 삭제 (line 135)
$phpFilePath = BASE_PATH . '/public/uploads/page/header_' . $id . '.php';

// 2. 서브메뉴 삭제 시 PHP 파일 삭제 (line 156)
$phpFilePath = BASE_PATH . '/public/uploads/page/header_' . $subMenu['id'] . '.php';

// 3. 메뉴 업데이트 시 PHP 파일 생성 (line 355)
$phpFile = BASE_PATH . '/public/uploads/page/header_' . $id . '.php';
```

### 5. 푸터 메뉴 관리 함수 (`application/libs/admin_footer_menu_func.php`)

3곳 수정:
```php
// 1. 메뉴 삭제 시 PHP 파일 삭제 (line 106)
$phpFile = BASE_PATH . '/public/uploads/page/footer_' . $id . '.php';

// 2. 서브메뉴 삭제 시 PHP 파일 삭제 (line 126)
$phpFile = BASE_PATH . '/public/uploads/page/footer_' . $sub['id'] . '.php';

// 3. 메뉴 업데이트 시 PHP 파일 생성 (line 336)
$phpFile = BASE_PATH . '/public/uploads/page/footer_' . $id . '.php';
```

### 6. DB 스키마 변경

#### menu_page_upload 테이블에 menu_table 컬럼 추가

```sql
-- 1. menu_table 컬럼 추가
ALTER TABLE menu_page_upload 
ADD COLUMN menu_table enum('header','footer') NOT NULL DEFAULT 'header' 
COMMENT '메뉴 타입 (header/footer)'
AFTER menu_id;

-- 2. 복합 인덱스 추가
ALTER TABLE menu_page_upload 
ADD INDEX idx_menu_upload (menu_id, menu_table);
```

**변경 사유**: 첨부파일도 헤더/푸터 메뉴를 구분해야 함

## 🔍 테이블 구조 비교

### menu_pages (이미 menu_table 컬럼 존재)
```
id          | int
menu_id     | int
menu_table  | enum('header','footer')  ← 이미 있음
content     | text
created_at  | timestamp
updated_at  | timestamp
```

### menu_page_upload (menu_table 컬럼 추가됨)
```
uid             | int
menu_id         | int
menu_table      | enum('header','footer')  ← 새로 추가
filename        | varchar(255)
original_name   | varchar(255)
filepath        | varchar(500)
filesize        | bigint
mime_type       | varchar(100)
download_count  | int
reg_date        | datetime
```

## 🧪 테스트 방법

### 1. 헤더 페이지 메뉴 테스트
1. 헤더 메뉴 생성: https://mvc.neuralgrid.kr/admin/menu/header
2. 메뉴 타입: **페이지** 선택
3. CKEditor로 본문 작성 후 저장
4. 메인 페이지에서 헤더 메뉴 클릭
5. URL 확인: `/page/header/2` (ID는 메뉴에 따라 다름)
6. 페이지 내용 정상 표시 확인

### 2. 푸터 페이지 메뉴 테스트
1. 푸터 메뉴 생성: https://mvc.neuralgrid.kr/admin/menu/footer
2. 메뉴 수정에서 타입: **페이지** 선택
3. CKEditor로 본문 작성 후 저장
4. 메인 페이지에서 푸터 메뉴 클릭
5. URL 확인: `/page/footer/1` (ID는 메뉴에 따라 다름)
6. 페이지 내용 정상 표시 확인

### 3. 파일 생성 확인
```bash
# 헤더 페이지 파일
ls -la /home/mvc/public/uploads/page/header_*.php

# 푸터 페이지 파일
ls -la /home/mvc/public/uploads/page/footer_*.php
```

### 4. DB 데이터 확인
```sql
-- menu_pages 확인
SELECT menu_id, menu_table, 
       LEFT(content, 50) as content_preview 
FROM menu_pages 
ORDER BY menu_table, menu_id;

-- menu_page_upload 확인
SELECT menu_id, menu_table, original_name 
FROM menu_page_upload 
ORDER BY menu_table, menu_id;
```

## ✅ 예상 결과

### URL 패턴
- ✅ 헤더 페이지: `https://mvc.neuralgrid.kr/page/header/2`
- ✅ 푸터 페이지: `https://mvc.neuralgrid.kr/page/footer/1`
- ✅ 404 에러 해결 (이전에는 `/page/1`에서 404 발생)

### 파일 시스템
```
/home/mvc/public/uploads/page/
├── header_2.php    ← 헤더 메뉴 ID 2의 페이지
├── footer_1.php    ← 푸터 메뉴 ID 1의 페이지
└── ...
```

### 데이터베이스
- `menu_pages`: `menu_table` 컬럼으로 헤더/푸터 구분
- `menu_page_upload`: `menu_table` 컬럼으로 첨부파일도 구분

## 🎨 장점

### 1. 명확한 구분
- URL만 봐도 헤더 메뉴인지 푸터 메뉴인지 즉시 파악 가능
- `/page/header/2` → 헤더 메뉴 ID 2
- `/page/footer/1` → 푸터 메뉴 ID 1

### 2. 충돌 방지
- 헤더 메뉴 ID 1과 푸터 메뉴 ID 1이 같은 URL을 사용하지 않음
- 각각 `/page/header/1`, `/page/footer/1`로 분리

### 3. 유지보수 용이
- 파일명도 구분되어 관리가 쉬움
- `header_2.php`, `footer_1.php`

### 4. 확장성
- 나중에 다른 메뉴 타입 추가 시에도 동일한 패턴 적용 가능
- 예: `/page/sidebar/3`, `/page/mobile/4` 등

## 📂 수정된 파일 목록
1. `application/controller/page.php` - 페이지 컨트롤러 (메뉴 타입 파라미터 추가)
2. `application/views/_header.php` - 헤더 뷰 (URL 패턴 변경)
3. `application/views/_footer.php` - 푸터 뷰 (URL 패턴 변경)
4. `application/libs/admin_header_menu_func.php` - 헤더 메뉴 관리 (파일 경로 변경 3곳)
5. `application/libs/admin_footer_menu_func.php` - 푸터 메뉴 관리 (파일 경로 변경 3곳)
6. `database/alter_menu_page_upload_add_menu_table.sql` - DB 스키마 변경 스크립트

## 🔗 관련 문서
- [푸터 메뉴 동적 표시 구현](FOOTER_MENU_DYNAMIC_DISPLAY.md)
- [메뉴 타겟 저장 버그 수정](FIX_MENU_TARGET_SAVE.md)
