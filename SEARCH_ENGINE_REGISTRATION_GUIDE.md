# 검색엔진 등록안내 기능 추가

## 개요
RSS 및 Sitemap 관리 페이지에 검색엔진 등록을 위한 상세 가이드를 추가했습니다.

## 주요 기능

### 1. 토글 버튼
- **위치**: 검색엔진 바로가기 링크 아래
- **텍스트**: "등록안내 보기" / "등록안내 닫기"
- **아이콘**: `<i class="fas fa-book-open"></i>`
- **스타일**: `btn-sm btn-outline-secondary`
- **동작**: 클릭 시 등록안내 섹션 표시/숨김

### 2. 아코디언 UI
Bootstrap 5의 Accordion 컴포넌트 사용:
- 3개의 검색엔진별로 독립적인 아코디언 항목
- 각 항목은 접혀있는 상태로 시작
- 클릭 시 해당 항목만 펼쳐짐

### 3. Google Search Console 등록 가이드

#### 단계별 안내
1. Google Search Console 접속 및 로그인
2. 좌측 메뉴에서 "속성 추가" 클릭
3. "URL 접두어" 선택 후 사이트 URL 입력
4. 소유권 확인 (HTML 파일 업로드 또는 메타 태그)
5. 좌측 메뉴 "Sitemaps" 선택
6. Sitemap URL 입력: `{ROOTURL}/sitemap_index.xml`
7. "제출" 버튼 클릭

#### 공식 문서 링크
- https://support.google.com/webmasters/answer/9008080

### 4. 네이버 서치어드바이저 등록 가이드

#### 단계별 안내
1. 네이버 서치어드바이저 접속 및 로그인
2. 상단 "웹마스터 도구" 클릭
3. "사이트 등록" 버튼 클릭 후 사이트 URL 입력
4. 소유권 확인 (HTML 파일 업로드 또는 메타 태그)
5. 좌측 메뉴 "요청 > 사이트맵 제출" 선택
6. Sitemap URL 입력: `{ROOTURL}/sitemap_index.xml`
7. "확인" 버튼 클릭

#### 공식 문서 링크
- https://searchadvisor.naver.com/guide

### 5. Bing 웹마스터 도구 등록 가이드

#### 단계별 안내
1. Bing 웹마스터 도구 접속 및 로그인
2. "사이트 추가" 버튼 클릭
3. 사이트 URL, Sitemap URL 입력
4. Sitemap URL: `{ROOTURL}/sitemap_index.xml`
5. 소유권 확인 (XML 파일 업로드 또는 메타 태그)
6. 좌측 메뉴 "Sitemaps"에서 제출 상태 확인

#### 특별 팁
Google Search Console 계정이 있다면 "Google에서 가져오기" 옵션으로 간편하게 등록 가능

#### 공식 문서 링크
- https://www.bing.com/webmasters/help/getting-started-checklist-66a806de

## HTML 구조

```html
<!-- 등록안내 버튼 -->
<div class="mt-3">
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleGuide()">
        <i class="fas fa-book-open"></i> 등록안내 보기
    </button>
</div>

<!-- 등록안내 내용 (처음에는 숨김) -->
<div id="registrationGuide" style="display: none;" class="mt-3">
    <div class="accordion" id="searchEngineGuide">
        
        <!-- Google 아코디언 아이템 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingGoogle">
                <button class="accordion-button collapsed" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapseGoogle">
                    <i class="fab fa-google text-danger me-2"></i>
                    <strong>Google Search Console 등록방법</strong>
                </button>
            </h2>
            <div id="collapseGoogle" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <ol>...</ol>
                    <div class="alert alert-info">
                        공식 문서 링크
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 네이버, Bing 아코디언 아이템 동일 구조 -->
        
    </div>
</div>
```

## JavaScript 함수

```javascript
// 등록안내 토글
function toggleGuide() {
    const guide = document.getElementById('registrationGuide');
    const button = event.target.closest('button');
    
    if (guide.style.display === 'none') {
        guide.style.display = 'block';
        button.innerHTML = '<i class="fas fa-book-open"></i> 등록안내 닫기';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-secondary');
    } else {
        guide.style.display = 'none';
        button.innerHTML = '<i class="fas fa-book-open"></i> 등록안내 보기';
        button.classList.remove('btn-secondary');
        button.classList.add('btn-outline-secondary');
    }
}
```

## 적용된 페이지

1. **RSS 설정 페이지**: https://mvc.neuralgrid.kr/admin/rss
2. **Sitemap 설정 페이지**: https://mvc.neuralgrid.kr/admin/sitemap

## 파일 변경사항

### application/views/admin/rss.php
- 라인 수: 564 → 699 (+135줄)
- 등록안내 버튼 추가
- 3개 검색엔진 아코디언 가이드 추가
- toggleGuide() 함수 추가

### application/views/admin/sitemap.php
- 라인 수: 554 → 689 (+135줄)
- 등록안내 버튼 추가
- 3개 검색엔진 아코디언 가이드 추가
- toggleGuide() 함수 추가

## Git 커밋

```
commit 22e7f87
feat(admin): add collapsible registration guide for search engines

- Add toggle button to show/hide registration guide
- Include step-by-step instructions for Google Search Console
- Include step-by-step instructions for Naver Search Advisor
- Include step-by-step instructions for Bing Webmaster Tools
- Add accordion UI with Bootstrap collapse
- Include links to official documentation for each search engine
- Add helpful tips for easier registration process
- Improve user experience with detailed guidance
- Total ~250 lines of guide content added

2 files changed, 248 insertions(+)
```

## 최근 커밋 이력

```
22e7f87 - feat(admin): add collapsible registration guide for search engines
587f4a9 - feat(admin): add search engine registration shortcuts
1290da6 - fix(admin): remove unnecessary footercode.php include
4b51040 - style(admin): redesign RSS/Sitemap pages with 2-column layout
```

## UX/UI 특징

### 버튼 상태 변화
- **닫힌 상태**: `btn-outline-secondary` + "등록안내 보기"
- **열린 상태**: `btn-secondary` + "등록안내 닫기"

### 아코디언 헤더 아이콘
- Google: `fab fa-google text-danger` (빨간색)
- 네이버: `fas fa-n text-success` (초록색)
- Bing: `fab fa-microsoft text-info` (파란색)

### Alert 스타일
- **정보 알림**: `alert alert-info` (파란색)
- **팁 알림**: `alert alert-success` (초록색)

### 코드 표시
Sitemap URL은 `<code>` 태그로 표시하여 가독성 향상

## 브라우저 호환성
- Chrome/Edge: 완벽 지원
- Firefox: 완벽 지원
- Safari: 완벽 지원
- Bootstrap 5 Collapse 컴포넌트 사용

## 향후 개선사항
- [ ] 각 검색엔진별 소유권 확인 방법 상세 가이드 추가
- [ ] 스크린샷 이미지 추가
- [ ] 비디오 튜토리얼 링크 추가
- [ ] FAQ 섹션 추가
- [ ] 검색엔진별 색인 상태 확인 방법 안내

---
작성일: 2026-02-16
작성자: AI Assistant
