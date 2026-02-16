# 메뉴 편집 - 게시판/뉴스 목록 표시 오류 수정

## 📋 작업 일시
- **날짜**: 2026-02-16 12:45
- **상태**: ✅ 완료

## 🐛 문제점
메뉴 편집 화면에서:
1. **게시판 선택** 드롭다운 - 게시판 목록이 표시되지 않음
2. **뉴스 선택** 드롭다운 - 뉴스 목록이 표시되지 않음

### 원인
**잘못된 컬럼명 사용**:
- 코드에서 사용: `board_id`, `board_name` (존재하지 않음!)
- 실제 테이블: `bbs_id`, `bbs_name` ✅
- 코드에서 사용: `id`, `category_name` (존재하지 않음!)
- 실제 테이블: `news_id`, `news_name` ✅

## 📊 실제 테이블 구조

### bbs_list 테이블
```sql
CREATE TABLE `bbs_list` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `bbs_id` varchar(50) NOT NULL,      -- ✅ 게시판 ID
  `bbs_name` varchar(100) NOT NULL,   -- ✅ 게시판명
  `bbs_skin` varchar(50) DEFAULT 'default',
  -- ... 기타 컬럼
  PRIMARY KEY (`uid`),
  UNIQUE KEY (`bbs_id`)
);
```

### news_list 테이블
```sql
CREATE TABLE `news_list` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `news_id` varchar(50) NOT NULL,     -- ✅ 뉴스 ID
  `news_name` varchar(100) NOT NULL,  -- ✅ 뉴스명
  `news_skin` varchar(50) DEFAULT 'default',
  -- ... 기타 컬럼
  PRIMARY KEY (`uid`),
  UNIQUE KEY (`news_id`)
);
```

## ✅ 수정 내용

### 1. Header 메뉴 핸들러
**파일**: `application/libs/admin_header_menu_func.php`

```php
// 변경 전 (❌ 잘못된 컬럼명)
$boards = getDbArray("SELECT board_id, board_name FROM bbs_list ORDER BY board_name");
$newsList = getDbArray("SELECT id, category_name FROM news_list ORDER BY category_name");

// 변경 후 (✅ 올바른 컬럼명)
$boards = getDbArray("SELECT bbs_id, bbs_name FROM bbs_list ORDER BY bbs_name");
$newsList = getDbArray("SELECT news_id, news_name FROM news_list ORDER BY news_name");
```

### 2. Footer 메뉴 핸들러
**파일**: `application/libs/admin_footer_menu_func.php`

동일하게 수정

### 3. Header 메뉴 편집 뷰
**파일**: `application/views/admin/menu_header_edit.php`

#### 게시판 드롭다운
```php
// 변경 전
<option value="<?php echo xssFilter($board['board_id']); ?>">
    <?php echo xssFilter($board['board_name']); ?> (<?php echo xssFilter($board['board_id']); ?>)
</option>

// 변경 후
<option value="<?php echo xssFilter($board['bbs_id']); ?>">
    <?php echo xssFilter($board['bbs_name']); ?> (<?php echo xssFilter($board['bbs_id']); ?>)
</option>
```

#### 뉴스 드롭다운
```php
// 변경 전
<option value="<?php echo xssFilter($news['id']); ?>">
    <?php echo xssFilter($news['category_name']); ?>
</option>

// 변경 후
<option value="<?php echo xssFilter($news['news_id']); ?>">
    <?php echo xssFilter($news['news_name']); ?>
</option>
```

### 4. Footer 메뉴 편집 뷰
**파일**: `application/views/admin/menu_footer_edit.php`

동일하게 수정

## 📝 수정된 파일 목록
1. `application/libs/admin_header_menu_func.php`
2. `application/libs/admin_footer_menu_func.php`
3. `application/views/admin/menu_header_edit.php`
4. `application/views/admin/menu_footer_edit.php`

## 🔍 실제 데이터 확인

### 게시판 목록
```sql
SELECT bbs_id, bbs_name FROM bbs_list ORDER BY bbs_name;
```

**결과**:
| bbs_id  | bbs_name      |
|---------|---------------|
| gallery | 갤러리        |
| notice  | 공지사항      |
| video   | 동영상        |
| free    | 자유게시판    |
| qna     | 질문과답변    |

### 뉴스 목록
```sql
SELECT news_id, news_name FROM news_list ORDER BY news_name;
```

**결과**:
| news_id | news_name         |
|---------|-------------------|
| news2   | 리스트스킨뉴스    |
| news1   | 새뉴스            |

## 🧪 테스트 방법

1. **메뉴 편집 페이지 접속**
   - https://mvc.neuralgrid.kr/admin/editMenu/2

2. **게시판 타입 선택**
   - 메뉴 타입에서 "게시판" 선택
   - 게시판 선택 드롭다운 확인:
     ```
     ▼ 게시판 선택
       게시판을 선택하세요
       갤러리 (gallery)
       공지사항 (notice)
       동영상 (video)
       자유게시판 (free)
       질문과답변 (qna)
     ```

3. **뉴스 타입 선택**
   - 메뉴 타입에서 "뉴스" 선택
   - 뉴스 카테고리 선택 드롭다운 확인:
     ```
     ▼ 뉴스 카테고리 선택
       뉴스 카테고리를 선택하세요
       리스트스킨뉴스
       새뉴스
     ```

4. **저장 및 동작 확인**
   - 게시판/뉴스 선택 후 저장
   - 헤더 메뉴에서 클릭하여 링크 확인

## ✨ 예상 결과

### 메뉴 편집 화면
- ✅ 게시판 목록 정상 표시 (5개)
- ✅ 뉴스 목록 정상 표시 (2개)
- ✅ 선택 후 저장 가능
- ✅ 기존 선택값 유지 (수정 시)

### 생성된 링크
- 게시판: `/bbs/{bbs_id}` (예: `/bbs/free`)
- 뉴스: `/news/{news_id}` (예: `/news/news1`)

## 🎯 컬럼명 정리

| 용도 | 잘못된 컬럼명 | 올바른 컬럼명 |
|------|--------------|--------------|
| 게시판 ID | board_id | **bbs_id** |
| 게시판명 | board_name | **bbs_name** |
| 뉴스 ID | id | **news_id** |
| 뉴스명 | category_name | **news_name** |

## 📌 주의사항
- `bbs_list`와 `news_list` 테이블은 동일한 구조입니다
- 두 테이블 모두 `{prefix}_id`, `{prefix}_name` 패턴을 사용합니다
- URL에도 동일한 ID를 사용합니다 (`bbs_id`, `news_id`)

---
**작성일**: 2026-02-16 12:45
**작성자**: Claude Code Assistant
