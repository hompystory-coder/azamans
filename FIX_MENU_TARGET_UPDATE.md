# 메뉴 타겟(게시판/뉴스 선택) 저장 오류 수정

## 📋 작업 일시
- **날짜**: 2026-02-16 12:50
- **상태**: ✅ 완료

## 🐛 문제점
메뉴 편집 화면에서:
1. **게시판 타입** - 다른 게시판으로 변경 후 저장해도 변경되지 않음
2. **뉴스 타입** - 다른 뉴스로 변경 후 저장해도 변경되지 않음

### 증상
- 드롭다운에서 선택은 정상 작동
- 저장 버튼 클릭 시 "메뉴가 업데이트되었습니다" 메시지 표시
- 하지만 새로고침 후 이전 선택값으로 되돌아감
- 실제 데이터베이스에 업데이트되지 않음

## 🔍 원인 분석

### 디버그 로그 확인
```
Menu Update - Input: {"menu_name":"공지사항","menu_type":"board","menu_target":"video",...}
Menu Update - Decoded data: Array(
    [menu_name] => 공지사항
    [menu_type] => board
    [menu_target] => video  ← ✅ 전송됨
    ...
)
Menu Update - Update data: Array(
    [menu_name] => 공지사항
    [menu_type] => board    ← ❌ menu_target이 없음!
)
```

### 문제 코드
**위치**: `application/libs/admin_header_menu_func.php` (line 281-287)

```php
$updateData = [];

if (isset($data['menu_name'])) $updateData['menu_name'] = cleanInput($data['menu_name']);
if (isset($data['menu_type'])) $updateData['menu_type'] = $data['menu_type'];
if (isset($data['menu_link'])) $updateData['menu_link'] = cleanInput($data['menu_link']);
if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];
if (isset($data['open_new_window'])) $updateData['open_new_window'] = $data['open_new_window'];
// ❌ menu_target이 빠져있음!
```

**결과**:
- 클라이언트에서 `menu_target` 값을 전송함
- 서버에서 받은 데이터에 `menu_target`이 포함되어 있음
- 하지만 `$updateData`에 `menu_target`을 추가하지 않음
- DB UPDATE 쿼리에 `menu_target`이 포함되지 않음
- 결과적으로 `menu_target`이 업데이트되지 않음

## ✅ 수정 내용

### 1. Header 메뉴 업데이트 핸들러
**파일**: `application/libs/admin_header_menu_func.php` (line 281-293)

```php
// 변경 후 - 모든 필드 포함
$updateData = [];

if (isset($data['menu_name'])) $updateData['menu_name'] = cleanInput($data['menu_name']);
if (isset($data['menu_type'])) $updateData['menu_type'] = $data['menu_type'];
if (isset($data['menu_target'])) $updateData['menu_target'] = cleanInput($data['menu_target']);  // ✅ 추가
if (isset($data['menu_link'])) $updateData['menu_link'] = cleanInput($data['menu_link']);
if (isset($data['custom_url'])) $updateData['custom_url'] = cleanInput($data['custom_url']);  // ✅ 추가
if (isset($data['target_window'])) $updateData['target_window'] = $data['target_window'];  // ✅ 추가
if (isset($data['use_redirect'])) $updateData['use_redirect'] = $data['use_redirect'];  // ✅ 추가
if (isset($data['is_hidden'])) $updateData['is_hidden'] = $data['is_hidden'];  // ✅ 추가
if (isset($data['is_blocked'])) $updateData['is_blocked'] = $data['is_blocked'];  // ✅ 추가
if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];
if (isset($data['open_new_window'])) $updateData['open_new_window'] = $data['open_new_window'];
```

### 2. Footer 메뉴 업데이트 핸들러
**파일**: `application/libs/admin_footer_menu_func.php` (line 288-300)

동일하게 수정

## 📊 추가된 필드

| 필드 | 설명 | 중요도 |
|------|------|--------|
| **menu_target** | 게시판/뉴스 ID 저장 | ⭐⭐⭐ 필수 |
| custom_url | 커스텀 URL | ⭐⭐ 중요 |
| target_window | 새 창 열기 설정 | ⭐⭐ 중요 |
| use_redirect | 리다이렉트 사용 여부 | ⭐ 선택 |
| is_hidden | 숨김 여부 | ⭐ 선택 |
| is_blocked | 차단 여부 | ⭐ 선택 |

## 📝 수정된 파일 목록
1. `application/libs/admin_header_menu_func.php` - menu_target 등 6개 필드 추가
2. `application/libs/admin_footer_menu_func.php` - 동일하게 수정

## 🧪 테스트 방법

### 게시판 변경 테스트
1. https://mvc.neuralgrid.kr/admin/editMenu/2 접속
2. **메뉴 타입**: 게시판 선택
3. **게시판 선택**: "공지사항" → "자유게시판"으로 변경
4. **저장** 버튼 클릭
5. 페이지 새로고침
6. ✅ "자유게시판"이 선택되어 있는지 확인

### 뉴스 변경 테스트
1. **메뉴 타입**: 뉴스 선택
2. **뉴스 선택**: "새뉴스" → "리스트스킨뉴스"로 변경
3. **저장** 버튼 클릭
4. 페이지 새로고침
5. ✅ "리스트스킨뉴스"가 선택되어 있는지 확인

### 데이터베이스 확인
```sql
-- 저장 전
SELECT id, menu_name, menu_type, menu_target FROM header_menu WHERE id = 2;
-- 예: 2 | 페이지1 | page | 

-- 저장 후 (게시판 "free" 선택)
SELECT id, menu_name, menu_type, menu_target FROM header_menu WHERE id = 2;
-- 예: 2 | 게시판 | board | free

-- 저장 후 (뉴스 "news1" 선택)
SELECT id, menu_name, menu_type, menu_target FROM header_menu WHERE id = 2;
-- 예: 2 | 뉴스 | news | news1
```

## 🔍 디버그 로그 (수정 후)

### 예상되는 정상 로그
```
Menu Update - Input: {"menu_name":"게시판","menu_type":"board","menu_target":"free",...}
Menu Update - Decoded data: Array(
    [menu_name] => 게시판
    [menu_type] => board
    [menu_target] => free
    ...
)
Menu Update - Update data: Array(
    [menu_name] => 게시판
    [menu_type] => board
    [menu_target] => free    ← ✅ 포함됨!
    [custom_url] => 
    [target_window] => self
    ...
)
Menu Update - Result: 1 (type: integer)  ← ✅ 1개 행 업데이트됨
Menu Update - SUCCESS: Menu updated
```

## ✨ 예상 결과

### 메뉴 업데이트 동작
- ✅ 게시판 선택 → `menu_target`에 `bbs_id` 저장
- ✅ 뉴스 선택 → `menu_target`에 `news_id` 저장
- ✅ 페이지 선택 → `menu_target` 빈 값
- ✅ 저장 후 새로고침 시 선택값 유지
- ✅ 헤더 메뉴 클릭 시 올바른 링크로 이동

### 링크 생성
- 게시판: `/bbs/{menu_target}` (예: `/bbs/free`)
- 뉴스: `/news/{menu_target}` (예: `/news/news1`)

## 🚨 이전 문제 영향 범위

### 영향받던 기능
- ✅ 게시판 선택 변경 - **수정 완료**
- ✅ 뉴스 선택 변경 - **수정 완료**
- ✅ 커스텀 URL 설정 - **수정 완료**
- ✅ 새 창 열기 설정 - **수정 완료**
- ✅ 리다이렉트 설정 - **수정 완료**
- ✅ 숨김/차단 설정 - **수정 완료**

### 영향받지 않던 기능
- ✅ 메뉴명 변경 - 정상 작동
- ✅ 메뉴 타입 변경 - 정상 작동
- ✅ 페이지 콘텐츠 저장 - 정상 작동

## 📌 주의사항
- `menu_target`은 게시판/뉴스 타입일 때만 사용됩니다
- 페이지 타입일 때는 빈 값으로 설정됩니다
- 모든 필드는 `cleanInput()`으로 XSS 방지 처리됩니다
- enum 타입 필드는 검증 없이 그대로 저장됩니다 (DB에서 검증)

---
**작성일**: 2026-02-16 12:50
**작성자**: Claude Code Assistant
