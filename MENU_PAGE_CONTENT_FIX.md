# 메뉴 페이지 콘텐츠 저장 오류 수정

## 📋 작업 일시
- **날짜**: 2026-02-16 12:15
- **상태**: ✅ 완료

## 🐛 문제점
1. **증상**: 메뉴명은 업데이트되지만 CK 에디터 본문 내용이 저장되지 않음
2. **원인**: `menu_pages` 테이블에 INSERT 시 필수 컬럼 `menu_table` 누락
   - `menu_pages` 테이블 구조:
     - `menu_table` enum('header','footer') NOT NULL
     - 헤더/푸터 메뉴를 구분하는 필수 컬럼
   - 기존 코드에서 INSERT 시 `menu_table` 값을 지정하지 않아 DB 오류 발생

## ✅ 수정 내용

### 1. Header 메뉴 핸들러 수정
**파일**: `application/libs/admin_header_menu_func.php`

#### 페이지 존재 여부 확인 (line 303)
```php
// 변경 전
$pageExists = getDbCnt('menu_pages', 'menu_id = ?', [$id]);

// 변경 후  
$pageExists = getDbCnt('menu_pages', 'menu_id = ? AND menu_table = ?', [$id, 'header']);
```

#### 페이지 업데이트 (line 308-312)
```php
// 변경 전
$pageUpdateResult = getDbUpdate('menu_pages', 
    ['content' => $data['page_content']], 
    'menu_id = ?', 
    [$id]
);

// 변경 후
$pageUpdateResult = getDbUpdate('menu_pages', 
    ['content' => $data['page_content']], 
    'menu_id = ? AND menu_table = ?', 
    [$id, 'header']
);
```

#### 페이지 INSERT (line 315-318)
```php
// 변경 전
$pageInsertResult = getDbInsert('menu_pages', [
    'menu_id' => $id,
    'content' => $data['page_content']
]);

// 변경 후
$pageInsertResult = getDbInsert('menu_pages', [
    'menu_id' => $id,
    'menu_table' => 'header',  // 필수 컬럼 추가
    'content' => $data['page_content']
]);
```

### 2. Footer 메뉴 핸들러 수정
**파일**: `application/libs/admin_footer_menu_func.php`

동일한 방식으로 수정:
- 페이지 존재 확인 시 `menu_table = 'footer'` 조건 추가
- UPDATE 시 `menu_table = 'footer'` 조건 추가
- INSERT 시 `'menu_table' => 'footer'` 데이터 추가

## 🧪 테스트 방법
1. https://mvc.neuralgrid.kr/admin/editMenu/2 접속
2. CK 에디터에서 페이지 내용 수정
3. 저장 버튼 클릭
4. 페이지 새로고침 후 내용이 저장되었는지 확인

## 📊 데이터베이스 구조
```sql
CREATE TABLE `menu_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NOT NULL,
  `menu_table` enum('header','footer') NOT NULL,  -- 필수 컬럼!
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menu_idx` (`menu_id`,`menu_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 📝 수정된 파일 목록
1. `application/libs/admin_header_menu_func.php` (3곳 수정)
2. `application/libs/admin_footer_menu_func.php` (3곳 수정)
3. `application/controller/admin.php` (디버그 로그 추가)
4. `application/views/admin/menu_header_edit.php` (디버그 로그 추가)

## 🔍 디버그 로그 위치
- **로그 파일**: `/home/mvc/menu_update_debug.log`
- **확인 방법**: `cat /home/mvc/menu_update_debug.log`

## ✨ 예상 결과
- ✅ 메뉴명 업데이트: 정상 작동
- ✅ 페이지 콘텐츠 저장: 정상 작동
- ✅ CK 에디터 내용 유지: 정상 작동
- ✅ 헤더/푸터 메뉴 구분: 정상 작동

---
**작성일**: 2026-02-16
**작성자**: Claude Code Assistant
