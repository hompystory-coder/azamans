# RSS & Sitemap Admin Pages Layout Update

## 개요
RSS 설정 및 Sitemap 설정 관리 페이지를 bot 설정 페이지와 동일한 2열 레이아웃으로 재디자인했습니다.

## 변경사항

### 1. 레이아웃 구조 변경
- **이전**: 게시판 RSS/Sitemap 아래에 뉴스 RSS/Sitemap이 세로로 배치
- **변경**: 게시판과 뉴스를 2열로 나란히 배치 (side-by-side)

### 2. 스크롤 컨테이너 추가
- 각 섹션에 `max-height: 500px` 스크롤 컨테이너 적용
- 게시판/뉴스 목록이 길어져도 레이아웃 유지
- 커스텀 스크롤바 스타일 적용 (6px 너비, 라운드)

### 3. 카드 기반 체크박스 디자인
```css
.checkbox-item-card {
    - 2px 테두리, 8px 둥근 모서리
    - hover 시 테두리 색상 및 그림자 효과
    - 선택 시 gradient 배경 및 색상 변경
    - 클릭 시 체크박스 토글 기능
}
```

### 4. Gradient 헤더
- **RSS 페이지**: 보라색 gradient (`#667eea` → `#764ba2`)
- **Sitemap 페이지**: 초록색 gradient (`#28a745` → `#20c997`)

### 5. UX 개선
- 카드 전체를 클릭하여 체크박스 토글 가능
- 선택된 항목 시각적 강조 (배경색, 테두리, 라벨 색상)
- 전체 선택/해제 버튼 각 섹션에 배치

## 영향받는 파일

### 수정된 파일
1. **application/views/admin/rss.php**
   - 563줄 → 564줄
   - 2열 레이아웃 적용
   - 스크롤 컨테이너 추가
   - 카드 기반 체크박스 구현

2. **application/views/admin/sitemap.php**
   - 유사한 구조로 변경
   - 초록색 테마 적용

## 페이지 URL
- RSS 설정: https://mvc.neuralgrid.kr/admin/rss
- Sitemap 설정: https://mvc.neuralgrid.kr/admin/sitemap

## Git 커밋
```
commit 4b51040
style(admin): redesign RSS/Sitemap pages with 2-column scrollable layout

- Apply bot-style 2-column layout to RSS & Sitemap admin pages
- Split board/news sections into side-by-side columns
- Add scrollable containers (max-height: 500px) with custom scrollbar
- Implement card-based checkbox items with hover/selected states
- Use gradient headers (purple for RSS, green for Sitemap)
- Remove Bootstrap form-check dependency
- Add click-to-toggle functionality for better UX
- Match /admin/bot page design pattern
```

## 스타일 특징

### 2열 컨테이너
```css
.two-column-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}
```

### 스크롤 컨테이너
```css
.scroll-container {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 10px;
}

/* 커스텀 스크롤바 */
.scroll-container::-webkit-scrollbar {
    width: 6px;
}
```

### 체크박스 카드
```css
.checkbox-item-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.checkbox-item-card.selected {
    background: linear-gradient(...);
    border-color: #667eea;  /* RSS */
    border-color: #28a745;  /* Sitemap */
}
```

## JavaScript 기능

### 체크박스 토글
```javascript
function toggleCheckbox(id) {
    const checkbox = document.getElementById(id);
    checkbox.checked = !checkbox.checked;
    updateCardStyle(checkbox);
}
```

### 카드 스타일 업데이트
```javascript
function updateCardStyle(checkbox) {
    const card = checkbox.closest('.checkbox-item-card');
    if (checkbox.checked) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
}
```

## 브라우저 호환성
- Chrome/Edge: 완벽 지원
- Firefox: 완벽 지원
- Safari: 완벽 지원 (webkit scrollbar)

## 향후 개선사항
- [ ] 반응형 디자인 (모바일에서 1열로 변경)
- [ ] 드래그 앤 드롭으로 순서 변경
- [ ] 검색/필터 기능 추가
- [ ] 키보드 단축키 지원

---
작성일: 2026-02-16

## 2026-02-16 추가 업데이트

### 검색엔진 등록 바로가기 추가

저장 버튼 위에 검색엔진 웹마스터 도구 바로가기 링크를 추가했습니다.

#### 추가된 링크
1. **Google Search Console**
   - URL: https://search.google.com/search-console
   - 색상: 빨간색 (btn-outline-danger)
   - 아이콘: Google 로고

2. **네이버 서치어드바이저**
   - URL: https://searchadvisor.naver.com/
   - 색상: 초록색 (btn-outline-success)
   - 아이콘: N 아이콘

3. **Bing 웹마스터 도구**
   - URL: https://www.bing.com/webmasters
   - 색상: 파란색 (btn-outline-info)
   - 아이콘: Microsoft 로고

#### 디자인
```html
<div class="mt-4">
    <h6 class="border-bottom pb-2 mb-3">
        <i class="fas fa-search"></i> 검색엔진 등록
    </h6>
    
    <div class="alert alert-light border">
        <div class="d-flex flex-wrap gap-3">
            <!-- 3개의 버튼 링크 -->
        </div>
        <small class="text-muted d-block mt-2">
            <i class="fas fa-info-circle"></i> 
            위 링크에서 RSS 피드와 Sitemap을 등록하여 검색엔진에 사이트를 노출시킬 수 있습니다.
        </small>
    </div>
</div>
```

#### Git 커밋
```
commit 587f4a9
feat(admin): add search engine registration shortcuts to RSS/Sitemap pages

- Add quick links section above save buttons
- Include Google Search Console link
- Include Naver Search Advisor link
- Include Bing Webmaster Tools link
- Add helpful description for search engine registration
- Improve UX with direct access to webmaster tools

2 files changed, 50 insertions(+)
```

#### UX 개선사항
- 사용자가 검색엔진 웹마스터 도구에 쉽게 접근 가능
- 새 탭에서 열림 (target="_blank")
- 버튼 스타일로 클릭하기 쉬움
- 각 검색엔진별 브랜드 컬러 적용
- 안내 문구로 사용 목적 명확히 제시

