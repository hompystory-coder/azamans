# 푸터 메뉴 생성 오류 수정 - customLog 함수 미정의

## 📋 작업 일시
- **날짜**: 2026-02-16 12:55
- **상태**: ✅ 완료

## 🐛 문제점
푸터 메뉴 생성 시 Fatal Error 발생:
```
Fatal error: Uncaught Error: Call to undefined function customLog() 
in /home/mvc/application/libs/admin_footer_menu_func.php:31
```

### 증상
- 푸터 메뉴 생성 버튼 클릭 시 오류 발생
- JSON 파싱 에러 (HTML 에러 페이지가 반환됨)
- 푸터 메뉴 생성 불가

### JavaScript 콘솔 에러
```javascript
footer:356 Error: Object
footer:357 Status: parsererror
footer:358 Error: SyntaxError: Unexpected token '<', "<br />
<b>"... is not valid JSON
```

## 🔍 원인
**존재하지 않는 함수 `customLog()` 호출**

`admin_footer_menu_func.php`에서 로깅을 위해 `customLog()` 함수를 호출했지만, 이 함수가 정의되지 않음:

```php
function admin_footer_menu_create_handler($controller) {
    customLog('[createFooterMenu] 시작', 'menu');  // ❌ 함수 미정의!
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        customLog('[createFooterMenu] POST 요청 아님', 'menu');  // ❌
        ...
    }
    ...
}
```

### customLog 호출 위치 (8곳)
1. Line 31: `customLog('[createFooterMenu] 시작', 'menu');`
2. Line 34: `customLog('[createFooterMenu] POST 요청 아님', 'menu');`
3. Line 40: `customLog('[createFooterMenu] menu_name 원본: ' . $menuNameRaw, 'menu');`
4. Line 43: `customLog('[createFooterMenu] 분리된 메뉴명: ' . json_encode($menuNames), 'menu');`
5. Line 53: `customLog('[createFooterMenu] 현재 최대 순서: ' . $currentMaxOrder, 'menu');`
6. Line 69: `customLog('[createFooterMenu] 푸터 메뉴 추가 성공: ' . $menuName . ' (순서: ' . $currentMaxOrder . ')', 'menu');`
7. Line 72: `customLog('[createFooterMenu] 푸터 메뉴 추가 실패: ' . $menuName, 'menu');`
8. Line 77: `customLog('[createFooterMenu] 완료: ' . $message, 'menu');`

## ✅ 수정 내용

### 1. customLog 호출 제거
**파일**: `application/libs/admin_footer_menu_func.php`

**방법**: `sed` 명령어로 모든 `customLog` 라인 제거
```bash
sed -i '/customLog/d' application/libs/admin_footer_menu_func.php
```

### 2. 수정 전/후 비교

#### 수정 전
```php
function admin_footer_menu_create_handler($controller) {
    customLog('[createFooterMenu] 시작', 'menu');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        customLog('[createFooterMenu] POST 요청 아님', 'menu');
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    $menuNameRaw = trim($_POST['menu_name'] ?? '');
    customLog('[createFooterMenu] menu_name 원본: ' . $menuNameRaw, 'menu');
    
    $menuNames = array_map('trim', explode(',', $menuNameRaw));
    customLog('[createFooterMenu] 분리된 메뉴명: ' . json_encode($menuNames), 'menu');
    ...
}
```

#### 수정 후
```php
function admin_footer_menu_create_handler($controller) {
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    $menuNameRaw = trim($_POST['menu_name'] ?? '');
    
    $menuNames = array_map('trim', explode(',', $menuNameRaw));
    ...
}
```

## 📝 수정된 파일 목록
1. `application/libs/admin_footer_menu_func.php` - 8개 customLog 호출 제거

## 🧪 테스트 방법

1. **푸터 메뉴 관리 페이지 접속**
   - https://mvc.neuralgrid.kr/admin/menu/footer

2. **푸터 메뉴 생성**
   - "새 메뉴 추가" 입력
   - 생성 버튼 클릭
   - ✅ "1개의 푸터 메뉴가 생성되었습니다" 메시지 확인
   - ✅ 목록에 새 메뉴 표시 확인

3. **여러 메뉴 동시 생성**
   - "메뉴1, 메뉴2, 메뉴3" 입력 (콤마로 구분)
   - 생성 버튼 클릭
   - ✅ "3개의 푸터 메뉴가 생성되었습니다" 메시지 확인

## ✨ 예상 결과

### 푸터 메뉴 생성
- ✅ Fatal Error 없음
- ✅ JSON 응답 정상 반환
- ✅ 메뉴 생성 성공
- ✅ 페이지 자동 새로고침

### JSON 응답 예시
```json
{
  "success": true,
  "message": "1개의 푸터 메뉴가 생성되었습니다."
}
```

## 💡 대안적 해결 방법

### Option 1: customLog 함수 구현 (선택하지 않음)
```php
function customLog($message, $type = 'general') {
    $logFile = BASE_PATH . '/logs/' . $type . '.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}
```

### Option 2: error_log 사용 (선택하지 않음)
```php
error_log('[createFooterMenu] 시작');
```

### Option 3: 로그 제거 (✅ 선택함)
- 가장 간단한 해결 방법
- 프로덕션 환경에서는 불필요한 로깅
- 필요시 나중에 표준 로깅 추가 가능

## 🔍 추가 확인 사항

### Header 메뉴에는 customLog 사용 안 함
```bash
grep -n "customLog" application/libs/admin_header_menu_func.php
# 결과: (없음)
```
- ✅ Header 메뉴는 정상 작동

### 다른 파일에 customLog 사용 여부 확인
```bash
grep -r "customLog" application/
# 결과: (admin_footer_menu_func.php에만 존재했음, 이제 제거됨)
```

## 📌 향후 개선 사항
프로젝트 전체에 표준 로깅 시스템 도입 고려:
1. PSR-3 Logger Interface 구현
2. Monolog 같은 라이브러리 사용
3. 환경별 로그 레벨 설정 (개발/프로덕션)
4. 로그 파일 로테이션 설정

---
**작성일**: 2026-02-16 12:55
**작성자**: Claude Code Assistant
