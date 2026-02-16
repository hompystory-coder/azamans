# PG Popup 토스트 알림 시스템 통합

## 개요
관리자 페이지에 비침투적(non-intrusive) 토스트 알림 시스템인 pgpopup.js를 통합했습니다.

## 주요 변경사항

### 1. PG Popup 플러그인 추가
- **파일**: `public/js/pgpopup.js`
- **출처**: `/home/mvc/clippopup.js`
- **크기**: 3,347 bytes
- **기능**: 
  - Toast 팝업 (페이드 인/아웃)
  - Layer 팝업 (닫기 버튼 포함)
  - Slide 팝업 (위/아래에서 슬라이드)

### 2. 관리자 헤더에 통합
**파일**: `application/views/admin/_admin_header.php`

#### 추가된 스크립트
```html
<!-- PG Popup Plugin -->
<script src="/public/js/pgpopup.js"></script>
```

#### 추가된 컨테이너
```html
<body>
    <!-- Popup Container -->
    <div id="ai-pop"></div>
```

### 3. RSS 페이지 적용
**파일**: `application/views/admin/rss.php`

#### URL 복사 알림
```javascript
$('#ai-pop').pgpopup({
    type: 'toast',
    msg: 'URL이 복사되었습니다.',
    padding: '15px',
    width: '250',
    color: '#ffffff',
    bgcolor: '#667eea',        // 보라색 (RSS 테마)
    transparency: '0.9',
    delay: '1500',
    time: '500'
});
```

#### 설정 저장 성공
```javascript
$('#ai-pop').pgpopup({
    type: 'toast',
    msg: 'RSS 설정이 저장되었습니다.',
    bgcolor: '#28a745',        // 초록색 (성공)
    // ... 기타 설정
});
```

#### RSS 재생성 성공
```javascript
$('#ai-pop').pgpopup({
    type: 'toast',
    msg: 'RSS 피드가 재생성되었습니다.',
    bgcolor: '#0d6efd',        // 파란색 (정보)
    // ... 기타 설정
});
```

#### 에러 메시지
```javascript
$('#ai-pop').pgpopup({
    type: 'toast',
    msg: '오류 메시지',
    bgcolor: '#dc3545',        // 빨간색 (에러)
    delay: '2000',             // 에러는 2초 표시
    // ... 기타 설정
});
```

### 4. Sitemap 페이지 적용
**파일**: `application/views/admin/sitemap.php`

동일한 패턴으로 적용, 단 복사 알림 색상만 다름:
```javascript
bgcolor: '#28a745'  // 초록색 (Sitemap 테마)
```

## 제거된 기능

### showAlert 함수 제거
**이전**:
```javascript
function showAlert(message, type = 'info') {
    const alertBox = document.getElementById('alertMessage');
    alertBox.className = `alert alert-${type}`;
    alertBox.textContent = message;
    alertBox.classList.remove('d-none');
    
    setTimeout(() => {
        alertBox.classList.add('d-none');
    }, 3000);
}
```

### alertMessage div 제거
**이전**:
```html
<!-- 알림 메시지 -->
<div id="alertMessage" class="alert d-none"></div>
```

## PG Popup 사용법

### 기본 구조
```javascript
$('#ai-pop').pgpopup({
    type: 'toast',              // 팝업 형태 (toast, layer, slide)
    msg: '메시지 내용',          // 표시할 메시지
    padding: '15px',            // 내부 여백
    width: '250',               // 폭 (픽셀)
    color: '#ffffff',           // 텍스트 색상
    bgcolor: '#111111',         // 배경색 (헥사 코드)
    transparency: '0.8',        // 투명도 (0~1)
    delay: '1500',              // 표시 시간 (ms)
    time: '500',                // 애니메이션 시간 (ms)
    direction: 'up'             // slide 방향 (up/down)
});
```

### Toast 팝업 (현재 사용 중)
- **특징**: 화면 중앙에 나타났다가 자동으로 사라짐
- **용도**: 간단한 알림, 성공/에러 메시지
- **장점**: 사용자 작업을 방해하지 않음

### Layer 팝업
- **특징**: 화면 중앙에 나타나며 닫기 버튼 포함
- **용도**: 중요한 정보, 사용자 확인 필요
- **장점**: 명시적으로 닫을 수 있음

### Slide 팝업
- **특징**: 화면 위/아래에서 슬라이드
- **용도**: 알림 바, 공지사항
- **장점**: 눈에 잘 띄면서도 덜 침투적

## 색상 코드 체계

### 기능별 색상
| 기능 | 색상 | 코드 | 용도 |
|------|------|------|------|
| RSS 복사 | 보라색 | `#667eea` | RSS 테마 색상 |
| Sitemap 복사 | 초록색 | `#28a745` | Sitemap 테마 색상 |
| 저장 성공 | 초록색 | `#28a745` | 성공 메시지 |
| 재생성 성공 | 파란색 | `#0d6efd` | 정보 메시지 |
| 에러 | 빨간색 | `#dc3545` | 에러 메시지 |

### 타이밍 설정
| 상황 | delay | time | 총 시간 |
|------|-------|------|---------|
| 일반 알림 | 1500ms | 500ms | ~2초 |
| 에러 | 2000ms | 500ms | ~2.5초 |

## Git 커밋

```
commit 81aa515
feat(admin): integrate pgpopup toast notification system

- Add pgpopup.js plugin to public/js directory
- Include pgpopup in admin header for global use
- Add #ai-pop container div in admin header
- Replace all showAlert calls with pgpopup toast notifications
- Update copyToClipboard functions in RSS/Sitemap pages
- Update save/regenerate success/error messages
- Remove unused showAlert function and alertMessage div
- Use color-coded toasts:
  * RSS copy: purple (#667eea)
  * Sitemap copy: green (#28a745)
  * Save success: green (#28a745)
  * Regenerate success: blue (#0d6efd)
  * Error messages: red (#dc3545)
- Toast settings: 500ms fade, 1500ms display, 0.9 transparency
- Improve UX with non-intrusive toast notifications

4 files changed, 308 insertions(+), 46 deletions(-)
create mode 100644 public/js/pgpopup.js
```

## 파일 변경 요약

### 생성된 파일
1. **public/js/pgpopup.js** (3,347 bytes)
   - jQuery 플러그인
   - 3가지 팝업 타입 지원

### 수정된 파일
1. **application/views/admin/_admin_header.php**
   - pgpopup.js 스크립트 추가
   - #ai-pop 컨테이너 추가

2. **application/views/admin/rss.php**
   - copyToClipboard 함수 업데이트
   - saveRssSettings 함수 업데이트
   - regenerateRss 함수 업데이트
   - showAlert 함수 제거
   - alertMessage div 제거

3. **application/views/admin/sitemap.php**
   - copyToClipboard 함수 업데이트
   - saveSitemapSettings 함수 업데이트
   - regenerateSitemap 함수 업데이트
   - showAlert 함수 제거
   - alertMessage div 제거

## 적용 페이지

1. **RSS 설정**: https://mvc.neuralgrid.kr/admin/rss
2. **Sitemap 설정**: https://mvc.neuralgrid.kr/admin/sitemap

## UX 개선사항

### 이전 (showAlert)
- ❌ 페이지 상단에 고정된 alert 박스
- ❌ 페이지 레이아웃 밀림 현상
- ❌ 단조로운 Bootstrap alert 스타일
- ❌ 수동으로 닫기 전까지 3초간 표시

### 이후 (pgpopup)
- ✅ 화면 중앙에 떠있는 토스트
- ✅ 페이지 레이아웃에 영향 없음
- ✅ 색상으로 구분된 메시지 타입
- ✅ 부드러운 페이드 인/아웃 애니메이션
- ✅ 자동으로 사라짐 (사용자 개입 불필요)
- ✅ 작업 흐름을 방해하지 않음

## 향후 확장 가능성

### 다른 관리자 페이지에 적용
pgpopup이 admin header에 통합되어 모든 관리자 페이지에서 사용 가능:

```javascript
// 어떤 관리자 페이지에서든 사용 가능
$('#ai-pop').pgpopup({
    type: 'toast',
    msg: '작업이 완료되었습니다.',
    bgcolor: '#28a745'
});
```

### 다양한 팝업 타입 활용
```javascript
// 중요한 확인 메시지
$('#ai-pop').pgpopup({
    type: 'layer',
    msg: '정말 삭제하시겠습니까?'
});

// 공지사항
$('#ai-pop').pgpopup({
    type: 'slide',
    direction: 'down',
    msg: '시스템 점검 예정입니다.'
});
```

### 커스텀 스타일링
```javascript
// 경고 메시지
$('#ai-pop').pgpopup({
    type: 'toast',
    msg: '주의가 필요합니다.',
    bgcolor: '#ff9800',  // 주황색
    width: '300',
    delay: '2500'
});
```

## 브라우저 호환성
- Chrome/Edge: 완벽 지원
- Firefox: 완벽 지원
- Safari: 완벽 지원
- jQuery 3.7.1+ 필요

## 주의사항
1. `#ai-pop` div는 페이지당 하나만 존재해야 함
2. pgpopup 호출 시 이전 팝업이 자동으로 제거됨
3. 너무 긴 메시지는 width를 조절하여 표시
4. 중요한 확인이 필요한 경우 `confirm()` 사용 권장

---
작성일: 2026-02-16
작성자: AI Assistant
