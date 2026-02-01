# 페이지 관련 테이블 구조

## 📋 현재 사용 중인 테이블

### 1. `header_menu` - 헤더 메뉴
메뉴 기본 정보를 저장합니다.

```sql
SELECT * FROM header_menu WHERE menu_type = 'page';
```

### 2. `menu_pages` - 페이지 콘텐츠
페이지 HTML 콘텐츠를 저장합니다 (편집용 백업).

```sql
SELECT * FROM menu_pages WHERE menu_id = ?;
```

**저장 위치:**
- DB: `menu_pages` 테이블
- 파일: `/public/uploads/page/{menu_id}.php`

### 3. `menu_page_upload` - 페이지 첨부파일
페이지에 첨부된 파일 정보를 저장합니다.

```sql
SELECT * FROM menu_page_upload WHERE menu_id = ?;
```

**구조:**
```sql
CREATE TABLE menu_page_upload (
    uid INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    filesize BIGINT NOT NULL,
    mime_type VARCHAR(100) NULL,
    download_count INT DEFAULT 0,
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_menu_id (menu_id),
    FOREIGN KEY (menu_id) REFERENCES header_menu(id) ON DELETE CASCADE
);
```

**저장 경로:**
```
/public/uploads/page/attach/{년}/{월}/{일}/파일명

예시:
/public/uploads/page/attach/2026/02/02/65c5f8a3_1738458123.pdf
```

---

## 🗑️ 삭제된 테이블

### ❌ `page_files` (구버전)
2026-02-02에 `menu_page_upload`로 대체되어 삭제되었습니다.

**삭제 이유:**
- `bbs_upload` 구조와 통일
- 컬럼명 일관성 (`uid`, `filesize` 등)
- 날짜별 폴더 구조 도입

---

## 📊 테이블 관계도

```
header_menu
    ├── menu_pages (1:1) - 페이지 콘텐츠
    └── menu_page_upload (1:N) - 첨부파일
```

---

## 🔧 주요 쿼리

### 페이지 전체 정보 조회
```sql
SELECT 
    hm.id,
    hm.menu_name,
    mp.content,
    COUNT(mpu.uid) as file_count
FROM header_menu hm
LEFT JOIN menu_pages mp ON hm.id = mp.menu_id
LEFT JOIN menu_page_upload mpu ON hm.id = mpu.menu_id
WHERE hm.menu_type = 'page'
GROUP BY hm.id;
```

### 첨부파일 다운로드 순위
```sql
SELECT 
    original_name,
    download_count,
    filesize,
    reg_date
FROM menu_page_upload
WHERE menu_id = ?
ORDER BY download_count DESC;
```

### 전체 첨부파일 용량
```sql
SELECT 
    COUNT(*) as total_files,
    SUM(filesize) as total_size,
    SUM(filesize) / 1024 / 1024 as total_size_mb
FROM menu_page_upload;
```

---

## 📝 참고 파일

- 테이블 생성: `/database/create_menu_page_upload.sql`
- 업로드 컨트롤러: `/application/controller/upload.php`
- 페이지 컨트롤러: `/application/controller/page.php`
- 관리자 컨트롤러: `/application/controller/admin.php`

---

## 🎯 업데이트 히스토리

| 날짜 | 변경사항 |
|------|----------|
| 2026-02-02 | `menu_page_upload` 테이블 생성 |
| 2026-02-02 | `page_files` 테이블 삭제 |
| 2026-02-02 | 날짜별 폴더 구조 도입 |
