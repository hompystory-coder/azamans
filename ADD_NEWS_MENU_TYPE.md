# 메뉴 타입에 뉴스(News) 추가

## 📋 작업 일시
- **날짜**: 2026-02-16 12:35
- **상태**: ✅ 완료

## 🎯 요구사항
메뉴 타입을 **페이지 / 게시판 / 콘텐츠**에서 **페이지 / 뉴스 / 게시판 / 콘텐츠**로 확장:
- 뉴스 카테고리를 선택하면 `/news/{category_id}` 링크로 연결
- 게시판과 동일한 방식으로 작동

## ✅ 수정 내용

### 1. 데이터베이스 스키마 변경
**파일**: `database/alter_menu_add_news_type.sql`

```sql
-- header_menu와 footer_menu에 'news' 타입 추가
ALTER TABLE `header_menu` 
MODIFY COLUMN `menu_type` enum('page','board','news','content') DEFAULT 'page'
COMMENT '메뉴 타입: page(페이지), board(게시판), news(뉴스), content(콘텐츠)';

ALTER TABLE `footer_menu` 
MODIFY COLUMN `menu_type` enum('page','board','news','content') DEFAULT 'page'
COMMENT '메뉴 타입: page(페이지), board(게시판), news(뉴스), content(콘텐츠)';
```

**실행 결과**: ✅ 적용 완료

### 2. Header 메뉴 편집 핸들러 수정
**파일**: `application/libs/admin_header_menu_func.php`

#### 뉴스 목록 조회 추가 (line 234)
```php
// 게시판 목록 조회
$boards = getDbArray("SELECT board_id, board_name FROM bbs_list ORDER BY board_name");

// 뉴스 카테고리 목록 조회
$newsList = getDbArray("SELECT id, category_name FROM news_list ORDER BY category_name");

$data = [
    'title' => '헤더 메뉴 수정',
    'menu' => $menu,
    'page' => $page,
    'boards' => $boards,
    'newsList' => $newsList  // 추가
];
```

### 3. Footer 메뉴 편집 핸들러 수정
**파일**: `application/libs/admin_footer_menu_func.php`

동일하게 뉴스 목록 조회 추가

### 4. Header 메뉴 편집 뷰 수정
**파일**: `application/views/admin/menu_header_edit.php`

#### 뉴스 타입 라디오 버튼 추가 (line 61)
```html
<input type="radio" class="btn-check" name="menu_type" id="type_news" value="news" 
    <?php echo $menu['menu_type'] === 'news' ? 'checked' : ''; ?>>
<label class="btn btn-outline-primary" for="type_news">
    <i class="fas fa-newspaper me-1"></i>뉴스
</label>
```

#### 뉴스 선택 드롭다운 추가 (line 121)
```html
<!-- 뉴스 타입: 뉴스 카테고리 선택 -->
<div class="mb-3 type-option" id="option_news" style="display: none;">
    <label for="news_select" class="form-label">뉴스 카테고리 선택</label>
    <select class="form-select" name="menu_target" id="news_select">
        <option value="">뉴스 카테고리를 선택하세요</option>
        <?php foreach ($newsList as $news): ?>
            <option value="<?php echo xssFilter($news['id']); ?>" 
                <?php echo ($menu['menu_target'] ?? '') == $news['id'] ? 'selected' : ''; ?>>
                <?php echo xssFilter($news['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
```

#### JavaScript 폼 제출 처리 추가 (line 314)
```javascript
} else if (selectedType === 'news') {
    // 뉴스 카테고리 선택값만 사용
    formData.set('menu_target', $('#news_select').val());
    updateMenu(formData, menuId);
}
```

### 5. Footer 메뉴 편집 뷰 수정
**파일**: `application/views/admin/menu_footer_edit.php`

Header와 동일한 방식으로 수정:
- 뉴스 타입 라디오 버튼 추가
- 뉴스 선택 드롭다운 추가
- JavaScript 처리 추가

### 6. Header 렌더링에 뉴스 링크 생성 추가
**파일**: `application/views/_header.php`

```php
switch ($headerMenu['menu_type']) {
    case 'page':
        $menuUrl = '/page/' . $headerMenu['id'];
        break;
    case 'board':
        if (!empty($headerMenu['menu_target'])) {
            $menuUrl = '/bbs/' . xssFilter($headerMenu['menu_target']);
        }
        break;
    case 'news':
        // 뉴스 카테고리 링크 생성
        if (!empty($headerMenu['menu_target'])) {
            $menuUrl = '/news/' . xssFilter($headerMenu['menu_target']);
        }
        break;
    case 'content':
        $menuUrl = '/content/' . xssFilter($headerMenu['menu_target']);
        break;
}
```

## 📊 데이터 구조

### header_menu / footer_menu 테이블
```sql
menu_type enum('page','board','news','content') DEFAULT 'page'
menu_target varchar(255)  -- 뉴스 타입일 경우 news_list.id 값 저장
```

### news_list 테이블
```sql
id int  -- 뉴스 카테고리 ID
category_name varchar(100)  -- 뉴스 카테고리명
```

## 🔗 URL 구조

### 메뉴 타입별 URL
- **페이지**: `/page/{menu_id}`
- **게시판**: `/bbs/{board_id}`
- **뉴스**: `/news/{category_id}` ⭐ 신규 추가
- **콘텐츠**: `/content/{content_target}`

## 🧪 테스트 방법

1. **메뉴 편집 페이지 접속**
   - https://mvc.neuralgrid.kr/admin/editMenu/2

2. **뉴스 타입 선택**
   - 메뉴 타입에서 "뉴스" 라디오 버튼 클릭
   - 뉴스 카테고리 선택 드롭다운 표시 확인

3. **뉴스 카테고리 선택**
   - 드롭다운에서 원하는 뉴스 카테고리 선택
   - 예: "IT뉴스" 선택 (ID: 1)

4. **저장 및 확인**
   - 저장 버튼 클릭
   - 메뉴가 업데이트되었습니다 알림 확인
   - 헤더 메뉴에서 해당 메뉴 클릭
   - `/news/1` (선택한 카테고리 ID)로 이동하는지 확인

## 📝 수정된 파일 목록

### SQL 스크립트
1. `database/alter_menu_add_news_type.sql` (신규 생성)

### Backend (PHP)
2. `application/libs/admin_header_menu_func.php`
   - 뉴스 목록 조회 추가
3. `application/libs/admin_footer_menu_func.php`
   - 뉴스 목록 조회 추가

### Frontend (Views)
4. `application/views/admin/menu_header_edit.php`
   - 뉴스 라디오 버튼 추가
   - 뉴스 선택 드롭다운 추가
   - JavaScript 처리 추가
5. `application/views/admin/menu_footer_edit.php`
   - 동일하게 수정
6. `application/views/_header.php`
   - 뉴스 링크 생성 로직 추가

## ✨ 예상 결과

### 메뉴 편집 화면
```
메뉴 타입: [페이지] [뉴스] [게시판] [콘텐츠]

▼ 뉴스 카테고리 선택
  ┌─────────────────────────┐
  │ 뉴스 카테고리를 선택하세요 │
  │ IT뉴스                   │
  │ 경제뉴스                  │
  │ 스포츠뉴스                │
  └─────────────────────────┘
```

### 생성된 링크
- 선택한 카테고리 ID가 1인 경우: `/news/1`
- 선택한 카테고리 ID가 2인 경우: `/news/2`

### 헤더 메뉴 예시
```
홈 | 뉴스 | 게시판 | 소개
     ↓
   /news/1
```

## 🎯 게시판과의 차이점

| 구분 | 게시판 | 뉴스 |
|-----|-------|-----|
| **테이블** | `bbs_list` | `news_list` |
| **식별자** | `board_id` (문자열) | `id` (숫자) |
| **URL** | `/bbs/{board_id}` | `/news/{category_id}` |
| **선택 표시** | 게시판명 (board_id) | 카테고리명 |

## 🔍 데이터 확인

### 뉴스 카테고리 목록 확인
```sql
SELECT id, category_name FROM news_list ORDER BY category_name;
```

### 메뉴 설정 확인
```sql
SELECT id, menu_name, menu_type, menu_target 
FROM header_menu 
WHERE menu_type = 'news';
```

---
**작성일**: 2026-02-16 12:35
**작성자**: Claude Code Assistant
