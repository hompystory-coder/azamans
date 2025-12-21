# 🔍 네이버 블로그 크롤링 문제 및 해결 방안

## ❌ 발생한 문제

### 1. 문제 상황
- 네이버 블로그 URL 입력 시 크롤링 실패
- 콘텐츠가 비어있음 (content: "", images: [])
- 404 Not Found 에러 발생

### 2. 원인 분석
네이버 블로그는 **iframe + JavaScript 동적 렌더링**을 사용:
- 일반 HTTP 요청으로는 HTML만 가져옴
- 실제 콘텐츠는 JavaScript로 로드됨
- iframe 안에 실제 포스트 내용이 있음

---

## 🛠️ 시도한 해결책

### 1. Puppeteer 통합 (진행 중)
```javascript
// Headless 브라우저로 동적 페이지 크롤링
const browser = await puppeteer.launch();
const page = await browser.newPage();
await page.goto(url);
// iframe 콘텐츠 추출
```

**문제점**:
- Puppeteer 설치 성공 (80 packages)
- API 호환성 문제 (waitForTimeout → setTimeout)
- 백엔드 재시작 시 시간 초과 발생

---

## ✅ 즉시 사용 가능한 해결책

### 방법 1: 텍스트 직접 입력 (권장)
현재 이미 구현되어 있음!

1. **"텍스트 입력" 탭 사용**
   - URL 입력 대신 텍스트 직접 입력
   - 블로그 내용을 복사하여 붙여넣기
   - 즉시 스크립트 생성

2. **사용 방법**:
   ```
   1. 네이버 블로그 열기
   2. 포스트 내용 복사 (Ctrl+A, Ctrl+C)
   3. AI 쇼츠 Pro → "텍스트 입력" 선택
   4. 붙여넣기 (Ctrl+V)
   5. "분석하기" 클릭
   ```

### 방법 2: 크롬 확장 프로그램
네이버 블로그 전용 크롬 확장 프로그램 개발:
- 포스트 페이지에서 "AI 쇼츠 만들기" 버튼 추가
- 자동으로 콘텐츠 추출 및 전송
- 원클릭 스크립트 생성

### 방법 3: API 프록시 서버
별도의 Puppeteer 서버 운영:
- Python/Node.js Puppeteer 전용 서버
- API: POST /crawl/{url}
- 결과를 메인 서버로 반환

---

## 🔄 현재 상태

### 작동하는 기능
✅ 일반 웹사이트 크롤링 (Cheerio)
✅ 텍스트 직접 입력 모드
✅ 콘텐츠 분석 API
✅ 스크립트 생성 API

### 진행 중
🔄 네이버 블로그 Puppeteer 크롤링
🔄 백엔드 안정화

### 대기 중
⏸️ 크롬 확장 프로그램 개발
⏸️ API 프록시 서버 구축

---

## 📝 사용자 가이드

### 현재 권장 워크플로우

#### 방법 A: 텍스트 입력 (가장 빠름)
```
1. 네이버 블로그 포스트 열기
2. 내용 전체 선택 (Ctrl+A)
3. 복사 (Ctrl+C)
4. AI 쇼츠 Pro 접속
5. "새 프로젝트" 클릭
6. "텍스트 입력" 탭 선택
7. 붙여넣기 (Ctrl+V)
8. "분석하기" 버튼 클릭
9. 스크립트 자동 생성됨 ✅
```

#### 방법 B: URL 입력 (일부 사이트만 지원)
```
지원하는 사이트:
✅ 티스토리 블로그
✅ 워드프레스 블로그
✅ 미디엄
✅ 일반 웹사이트

미지원:
❌ 네이버 블로그 (iframe 사용)
❌ 다음 블로그 (동적 로딩)
```

---

## 🚀 향후 개선 계획

### Phase 1 (즉시)
1. ✅ 텍스트 입력 UI 개선
2. ✅ 분석 결과 시각화
3. 🔄 Puppeteer 크롤링 안정화

### Phase 2 (1주일)
1. 크롬 확장 프로그램 개발
2. 네이버 블로그 원클릭 추출
3. 이미지 자동 다운로드

### Phase 3 (2주일)
1. 전용 크롤링 서버 구축
2. 큐 시스템 도입
3. 백그라운드 처리

---

## 💡 임시 해결책 코드

### Frontend: 텍스트 입력 UI 개선

```jsx
// URLInput.jsx 개선
<div className="tabs">
  <button 
    className={mode === 'url' ? 'active' : ''}
    onClick={() => setMode('url')}
  >
    📎 URL 입력
  </button>
  <button 
    className={mode === 'text' ? 'active' : ''}
    onClick={() => setMode('text')}
  >
    📝 텍스트 입력 (네이버 블로그 권장)
  </button>
</div>

{mode === 'text' && (
  <textarea
    placeholder="네이버 블로그 내용을 복사하여 붙여넣으세요..."
    rows={15}
    value={text}
    onChange={(e) => setText(e.target.value)}
  />
)}
```

---

## 🔗 관련 링크

- **메인 페이지**: https://ai-shorts.neuralgrid.kr/
- **Storage 테스트**: https://ai-shorts.neuralgrid.kr/test-storage.html
- **Crawler 테스트**: https://ai-shorts.neuralgrid.kr/test-crawler.html

---

**작성 일시**: 2025-12-21
**상태**: 텍스트 입력 모드 사용 권장
**예상 완전 해결**: Puppeteer 안정화 후 (진행 중)
