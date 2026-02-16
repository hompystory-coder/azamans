# 푸터 메뉴 동적 표시 구현

## 📋 작업 일시
- **날짜**: 2026-02-16 13:00
- **상태**: ✅ 완료

## 🎯 목적
사이트 푸터에 하드코딩된 임시 메뉴(이용약관, 개인정보보호정책, 청소년보호정책)를 제거하고, DB의 `footer_menu` 테이블에서 동적으로 메뉴를 불러와 표시

## 📊 변경 사항

### 1. 푸터 뷰 수정 (`application/views/_footer.php`)

#### Before (하드코딩된 메뉴)
```php
<div class="footer-links">
    <a href="/policy/terms" class="text-decoration-none me-3">이용약관</a>
    <a href="/policy/privacy" class="text-decoration-none me-3">개인정보보호정책</a>
    <a href="/policy/youth" class="text-decoration-none">청소년보호정책</a>
</div>
```

#### After (동적 메뉴)
```php
<?php
// 푸터 메뉴 조회
$footerMenus = getDbResult("
    SELECT * FROM footer_menu 
    WHERE is_active = 'Y' 
    AND parent_id = 0
    ORDER BY menu_order ASC, id ASC
");
?>
<div class="footer-links">
    <?php if (!empty($footerMenus)): ?>
        <?php foreach ($footerMenus as $index => $footerMenu): ?>
            <?php
            // 메뉴 타입에 따른 URL 생성
            $menuUrl = '#';
            $menuTarget = ($footerMenu['target_window'] ?? 'self') === 'blank' ? ' target="_blank"' : '';
            
            switch ($footerMenu['menu_type']) {
                case 'page':
                    $menuUrl = '/page/' . $footerMenu['id'];
                    break;
                case 'board':
                    if (!empty($footerMenu['menu_target'])) {
                        $menuUrl = '/bbs/' . xssFilter($footerMenu['menu_target']);
                    }
                    break;
                case 'news':
                    if (!empty($footerMenu['menu_target'])) {
                        $menuUrl = '/news/' . xssFilter($footerMenu['menu_target']);
                    }
                    break;
                case 'content':
                    if (!empty($footerMenu['menu_target'])) {
                        $menuUrl = '/content/' . xssFilter($footerMenu['menu_target']);
                    }
                    break;
            }
            
            // custom_url이 있으면 우선 사용
            if (!empty($footerMenu['custom_url'])) {
                $menuUrl = xssFilter($footerMenu['custom_url']);
            }
            ?>
            <a href="<?php echo $menuUrl; ?>" 
               class="text-decoration-none<?php echo ($index < count($footerMenus) - 1) ? ' me-3' : ''; ?>"
               <?php echo $menuTarget; ?>>
                <?php echo xssFilter($footerMenu['menu_name']); ?>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- 관리자일 때만 메뉴 생성 링크 표시 -->
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 'Y'): ?>
            <a href="/admin/menu/footer" class="text-muted text-decoration-none">
                <i class="bi bi-plus-circle"></i> 푸터 메뉴 생성하기
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>
```

## 🔧 주요 기능

### 1. DB 조회
- `footer_menu` 테이블에서 활성화(`is_active = 'Y'`)된 최상위 메뉴(`parent_id = 0`) 조회
- `menu_order ASC, id ASC` 순서로 정렬

### 2. URL 생성 로직
메뉴 타입별 URL 패턴:
- **page**: `/page/{id}`
- **board**: `/bbs/{menu_target}` (bbs_id)
- **news**: `/news/{menu_target}` (news_id)
- **content**: `/content/{menu_target}`
- **custom_url**: 우선순위 최상위, 입력된 URL 그대로 사용

### 3. 타겟 윈도우 처리
- `target_window = 'blank'` → `target="_blank"` 속성 추가
- 기본값: 현재 창에서 열기

### 4. XSS 방어
- `xssFilter()` 함수로 모든 출력 데이터 필터링

### 5. 빈 메뉴 처리
- 메뉴가 없을 때: 일반 사용자에게는 아무것도 표시 안 함
- 관리자일 때: 푸터 메뉴 생성 링크 표시

## 📝 현재 DB 데이터 확인

```sql
SELECT id, menu_name, menu_type, menu_target, is_active, menu_order 
FROM footer_menu 
ORDER BY menu_order ASC, id ASC;
```

결과:
```
id  | menu_name | menu_type | menu_target | is_active | menu_order
----|-----------|-----------|-------------|-----------|------------
1   | 이용약관   | page      |             | Y         | 1
```

## 🧪 테스트 방법

### 1. 푸터 메뉴 확인
1. 메인 페이지 접속: https://mvc.neuralgrid.kr/
2. 페이지 하단 푸터 확인
3. "이용약관" 메뉴 클릭 → `/page/1` 이동

### 2. 새 푸터 메뉴 추가
1. 관리자 페이지: https://mvc.neuralgrid.kr/admin/menu/footer
2. "메뉴 추가" 버튼 클릭
3. 메뉴명 입력 후 저장
4. 메인 페이지 새로고침 → 푸터에 새 메뉴 표시 확인

### 3. 다양한 메뉴 타입 테스트
#### 페이지 타입
- 메뉴 수정 → 타입: 페이지 선택 → 본문 입력 → 저장
- 푸터 메뉴 클릭 → 페이지 내용 확인

#### 게시판 타입
- 메뉴 수정 → 타입: 게시판 선택 → 게시판 선택(예: notice) → 저장
- 푸터 메뉴 클릭 → `/bbs/notice` 이동 확인

#### 뉴스 타입
- 메뉴 수정 → 타입: 뉴스 선택 → 뉴스 선택(예: news1) → 저장
- 푸터 메뉴 클릭 → `/news/news1` 이동 확인

## ✅ 예상 결과
1. 푸터에 DB의 실제 메뉴가 동적으로 표시됨
2. 하드코딩된 임시 메뉴(이용약관, 개인정보보호정책, 청소년보호정책) 제거됨
3. 관리자 패널에서 추가/수정/삭제한 메뉴가 즉시 푸터에 반영됨
4. 메뉴 순서(`menu_order`)에 따라 정렬되어 표시됨
5. 메뉴가 없을 때 관리자에게만 생성 링크 표시

## 📂 수정된 파일
- `application/views/_footer.php` - 푸터 뷰 전체 수정 (동적 메뉴 조회 및 표시)

## 🔗 관련 기능
- 헤더 메뉴도 동일한 로직으로 구현되어 있음 (`_header.php`)
- 메뉴 관리: `/admin/menu/footer`
- 메뉴 수정: `/admin/editFooterMenu/{id}`
