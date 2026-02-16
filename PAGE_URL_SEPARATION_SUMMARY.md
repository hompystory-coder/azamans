# 헤더/푸터 페이지 URL 구분 작업 완료 요약

## ✅ 작업 완료 (2026-02-16 13:15)

### 🎯 해결한 문제
**문제**: 헤더 메뉴와 푸터 메뉴의 페이지가 같은 URL(`/page/1`)을 사용해서 어디로 가야 할지 모호했음

**해결**: URL에 메뉴 타입 구분자 추가
- 헤더: `/page/header/1`
- 푸터: `/page/footer/1`

---

## 📝 주요 변경 사항

### 1. URL 패턴 변경 ✅

| 위치 | Before | After |
|------|--------|-------|
| 헤더 페이지 | `/page/2` | `/page/header/2` |
| 푸터 페이지 | `/page/1` | `/page/footer/1` |

### 2. 파일 경로 변경 ✅

| 위치 | Before | After |
|------|--------|-------|
| 헤더 페이지 파일 | `public/uploads/page/2.php` | `public/uploads/page/header_2.php` |
| 푸터 페이지 파일 | `public/uploads/page/1.php` | `public/uploads/page/footer_1.php` |

### 3. DB 스키마 변경 ✅

**menu_page_upload 테이블에 `menu_table` 컬럼 추가**
```sql
ALTER TABLE menu_page_upload 
ADD COLUMN menu_table enum('header','footer') NOT NULL DEFAULT 'header';
```

---

## 🔧 수정된 파일 (6개)

| 파일 | 변경 내용 |
|------|----------|
| `application/controller/page.php` | `index()` 함수에 `$menuTable` 파라미터 추가, 헤더/푸터 구분 로직 |
| `application/views/_header.php` | 페이지 URL을 `/page/header/{id}` 로 변경 |
| `application/views/_footer.php` | 페이지 URL을 `/page/footer/{id}` 로 변경, `getDbArray()` 사용 |
| `application/libs/admin_header_menu_func.php` | PHP 파일 경로를 `header_{id}.php` 로 변경 (3곳) |
| `application/libs/admin_footer_menu_func.php` | PHP 파일 경로를 `footer_{id}.php` 로 변경 (3곳) |
| `database/alter_menu_page_upload_add_menu_table.sql` | SQL 스크립트 생성 및 실행 |

---

## 🧪 테스트 가이드

### 헤더 페이지 테스트
1. 페이지 접속: https://mvc.neuralgrid.kr/
2. 헤더 메뉴에서 "페이지1" 클릭
3. URL 확인: `/page/header/2`
4. 페이지 내용 정상 표시 확인 ✅

### 푸터 페이지 테스트
1. 페이지 접속: https://mvc.neuralgrid.kr/
2. 푸터 메뉴에서 "이용약관" 클릭
3. URL 확인: `/page/footer/1`
4. 페이지 내용 정상 표시 확인 ✅

---

## 📊 현재 상태

### 생성된 파일 (6개)
```
public/uploads/page/
├── footer_1.php           ← 푸터 메뉴 ID 1 (이용약관)
├── header_1.php           ← 헤더 메뉴 ID 1 (공지사항 - board 타입)
├── header_2.php           ← 헤더 메뉴 ID 2 (페이지1) ✅ 최신
├── header_2_old.php       ← 이전 버전 (삭제 가능)
├── header_4.php           ← 헤더 메뉴 ID 4 (페이지2)
└── header_6.php           ← 헤더 메뉴 ID 6 (뉴스 - news 타입)
```

### DB 테이블 구조
```sql
-- menu_pages (페이지 콘텐츠)
CREATE TABLE menu_pages (
    id int PRIMARY KEY AUTO_INCREMENT,
    menu_id int NOT NULL,
    menu_table enum('header','footer') NOT NULL,  ← 구분자
    content text,
    created_at timestamp,
    updated_at timestamp
);

-- menu_page_upload (첨부파일)
CREATE TABLE menu_page_upload (
    uid int PRIMARY KEY AUTO_INCREMENT,
    menu_id int NOT NULL,
    menu_table enum('header','footer') NOT NULL,  ← 구분자 (새로 추가)
    filename varchar(255),
    original_name varchar(255),
    filepath varchar(500),
    filesize bigint,
    mime_type varchar(100),
    download_count int DEFAULT 0,
    reg_date datetime
);
```

---

## ✨ 개선 효과

### 1. 명확성
- ✅ URL만 봐도 헤더/푸터 구분 가능
- ✅ 파일명도 `header_`, `footer_` prefix로 구분

### 2. 충돌 방지
- ✅ 헤더 ID 1과 푸터 ID 1이 서로 다른 URL 사용
- ✅ 동시에 존재해도 문제 없음

### 3. 유지보수성
- ✅ 파일 관리가 쉬워짐
- ✅ 디버깅 시 어느 메뉴인지 즉시 파악 가능

### 4. 확장성
- ✅ 향후 다른 메뉴 타입 추가 시 동일한 패턴 적용 가능
- ✅ 예: `/page/sidebar/1`, `/page/mobile/2`

---

## 🔗 관련 문서
- [상세 문서](SEPARATE_HEADER_FOOTER_PAGE_URLS.md)
- [푸터 메뉴 동적 표시](FOOTER_MENU_DYNAMIC_DISPLAY.md)
- [메뉴 타겟 저장 버그 수정](FIX_MENU_TARGET_SAVE.md)

---

## 🎉 결과
**이제 푸터에 생성한 "이용약관" 메뉴를 클릭하면:**
1. URL: `https://mvc.neuralgrid.kr/page/footer/1` ✅
2. 페이지 내용 정상 표시 ✅
3. 404 에러 해결 ✅
