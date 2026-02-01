# 메뉴 페이지 콘텐츠 저장 확인 가이드

## 데이터 저장 위치

### 테이블: `menu_pages`

**컬럼:**
- `id` - Primary Key
- `menu_id` - 메뉴 ID (header_menu.id)
- `menu_table` - 메뉴 테이블 구분 ('header', 'footer')
- `content` - 페이지 내용 (HTML)
- `created_at` - 생성일
- `updated_at` - 수정일

---

## 테이블 생성 확인

### 1. 테이블 존재 확인
```sql
SHOW TABLES LIKE 'menu_pages';
```

### 2. 테이블이 없으면 생성
```bash
cd /home/mvc
mysql -u [사용자명] -p [DB명] < database/create_menu_tables.sql
```

### 3. 테이블 구조 확인
```sql
DESC menu_pages;
```

**예상 결과:**
```
+------------+-------------+------+-----+-------------------+
| Field      | Type        | Null | Key | Default           |
+------------+-------------+------+-----+-------------------+
| id         | int         | NO   | PRI | NULL              |
| menu_id    | int         | NO   | UNI | NULL              |
| menu_table | varchar(50) | YES  | MUL | header            |
| content    | text        | YES  |     | NULL              |
| created_at | timestamp   | NO   |     | CURRENT_TIMESTAMP |
| updated_at | timestamp   | NO   |     | CURRENT_TIMESTAMP |
+------------+-------------+------+-----+-------------------+
```

---

## 데이터 저장 흐름

### 1. 관리자 페이지에서 메뉴 저장
**URL:** https://mvc.neuralgrid.kr/admin/editMenu/2

**프로세스:**
1. 메뉴 타입: "페이지" 선택
2. 페이지 내용 (HTML 편집) 에 CKEditor로 내용 입력
3. "저장" 버튼 클릭
4. AJAX 전송: `/admin/updateMenu/2` (POST)
5. 서버 처리: `admin.php::updateMenu()`

### 2. 서버 처리 로직
```php
// 1. header_menu 테이블 업데이트 (메뉴 기본 정보)
getDbUpdate('header_menu', [
    'menu_name' => $menuName,
    'menu_type' => $menuType,
    // ...
], 'id = ?', [$id]);

// 2. menu_pages 테이블 업데이트/생성 (페이지 내용)
if ($menuType === 'page') {
    $pageContent = $this->post('page_content', '');
    
    // 기존 페이지 확인
    $existingPage = getUidData("SELECT id FROM menu_pages WHERE menu_id = ?", [$id]);
    
    if ($existingPage) {
        // 업데이트
        getDbUpdate('menu_pages', ['content' => $pageContent], 'menu_id = ?', [$id]);
    } else {
        // 생성
        getDbInsert('menu_pages', [
            'menu_id' => $id,
            'menu_table' => 'header',
            'content' => $pageContent
        ]);
    }
}
```

### 3. 데이터 조회
```php
// Page 컨트롤러에서 페이지 내용 조회
$page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$menuId]);
$content = $page['content'] ?? '<p>페이지 내용이 없습니다.</p>';
```

---

## 저장 데이터 확인 방법

### SQL 쿼리
```sql
-- 메뉴 ID 2번의 페이지 내용 확인
SELECT * FROM menu_pages WHERE menu_id = 2;
```

### PHP 디버깅
```php
// admin.php::updateMenu() 에 추가
error_log("Page Content: " . $this->post('page_content', ''));
```

### 브라우저 개발자 도구
1. F12 → Network 탭
2. "저장" 버튼 클릭
3. `/admin/updateMenu/2` 요청 확인
4. Request Payload에서 `page_content` 확인

---

## 문제 해결

### 문제 1: 테이블이 없음
**증상:** SQL Error: Table 'menu_pages' doesn't exist

**해결:**
```bash
cd /home/mvc
mysql -u [사용자명] -p [DB명] < database/create_menu_tables.sql
```

### 문제 2: menu_table 컬럼 없음
**증상:** SQL Error: Unknown column 'menu_table'

**해결:**
```sql
ALTER TABLE menu_pages ADD COLUMN menu_table VARCHAR(50) DEFAULT 'header' AFTER menu_id;
ALTER TABLE menu_pages ADD INDEX idx_menu_table (menu_table);
```

### 문제 3: 저장은 되는데 조회 안 됨
**원인:** Page 컨트롤러에서 조회 쿼리 확인

**해결:**
```php
// application/controller/page.php 확인
$page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$menuId]);
```

---

## 테스트 시나리오

### 1. 새 페이지 생성 테스트
1. https://mvc.neuralgrid.kr/admin/editMenu/2 접속
2. 메뉴 타입: "페이지" 선택
3. 내용: "안녕하세요! 테스트 페이지입니다." 입력
4. 저장 버튼 클릭
5. SQL 확인:
   ```sql
   SELECT * FROM menu_pages WHERE menu_id = 2;
   ```
6. 페이지 확인: https://mvc.neuralgrid.kr/page/2

### 2. 페이지 수정 테스트
1. 동일 페이지 재접속
2. 내용 수정: "수정된 내용입니다!"
3. 저장
4. SQL 확인: `updated_at` 변경됨
5. 페이지 확인: 내용 반영됨

### 3. 이미지 포함 테스트
1. CKEditor에서 이미지 업로드
2. 저장
3. SQL 확인: `content`에 `<img src="...">` 포함
4. 페이지 확인: 이미지 정상 표시

---

## 요약

✅ **저장 위치:** `menu_pages` 테이블
✅ **저장 경로:** `/admin/updateMenu/{menuId}` → `admin.php::updateMenu()`
✅ **저장 데이터:** `page_content` (HTML)
✅ **조회 경로:** `/page/{menuId}` → `page.php::index()`
✅ **조회 쿼리:** `SELECT content FROM menu_pages WHERE menu_id = ?`

---

**테이블이 없으면 `database/create_menu_tables.sql`을 실행하세요!**
