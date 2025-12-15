# 🔍 DDoS Tester 카드 표시 문제 디버깅 가이드

**날짜**: 2025-12-15  
**Git Commit**: `1f7c9de`  
**문제**: DDoS Tester 카드가 화면에 표시되지 않음 (빨간 박스 영역 비어있음)

---

## 📌 현재 상황

### ✅ 확인된 사항
- DDoS Tester는 `additionalServices` 객체에 정의되어 있음
- JavaScript 코드는 정상적으로 작성됨
- Footer에는 DDoS Tester 링크가 표시됨

### ❓ 확인 필요 사항
- 서버에 배포된 파일이 최신 버전인지 확인
- 브라우저 콘솔에서 JavaScript 오류 확인
- `loadServices()` 함수가 정상 실행되는지 확인

---

## 🚀 즉시 배포 (디버그 로그 포함)

### 서버: 115.91.5.140

```bash
cd /tmp/azamans
git fetch origin
git checkout genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean

# 백업
sudo cp /var/www/html/index.html /var/www/html/index.html.backup-$(date +%Y%m%d-%H%M%S)

# 배포
sudo cp neuralgrid-homepage.html /var/www/html/index.html
sudo chown www-data:www-data /var/www/html/index.html
sudo chmod 644 /var/www/html/index.html

# Nginx 재시작
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔍 브라우저 디버깅 방법

### 1. 브라우저 콘솔 열기
1. https://neuralgrid.kr/ 접속
2. **F12** 키 누르기
3. **Console** 탭 선택

### 2. 예상되는 콘솔 출력
```javascript
Loading services... {mainGrid: div, additionalGrid: div}
Main services loaded: 5
🔍 Additional Services: ['System Monitor', 'Auth Service', 'AI Assistant', 'DDoS Tester']
  ✅ Creating card for: System Monitor {...}
  ✅ Creating card for: Auth Service {...}
  ✅ Creating card for: AI Assistant {...}
  ✅ Creating card for: DDoS Tester {...}
✅ Additional services loaded: 4
```

### 3. 만약 로그가 보이지 않으면
```javascript
// 콘솔에서 직접 실행
console.log('Additional Services:', Object.keys(additionalServices));
console.log('DDoS Tester:', additionalServices['DDoS Tester']);
```

---

## 🧪 수동 테스트

### 브라우저 콘솔에서 실행:

```javascript
// 1. Additional Services 확인
console.log('Total:', Object.keys(additionalServices).length);
console.log('Keys:', Object.keys(additionalServices));

// 2. DDoS Tester 확인
if (additionalServices['DDoS Tester']) {
    console.log('✅ DDoS Tester exists:', additionalServices['DDoS Tester']);
} else {
    console.log('❌ DDoS Tester NOT FOUND');
}

// 3. Grid 요소 확인
const grid = document.getElementById('additional-services-grid');
console.log('Grid element:', grid);
console.log('Grid children:', grid?.children.length);

// 4. 수동으로 서비스 재로드
loadServices();
```

---

## 🔧 가능한 원인 & 해결책

### 원인 1: 캐시 문제
**증상**: 구버전 파일이 로드됨  
**해결**: 
- 브라우저 캐시 삭제 (Ctrl + Shift + Delete)
- 강제 새로고침 (Ctrl + F5 또는 Cmd + Shift + R)

### 원인 2: JavaScript 오류
**증상**: 콘솔에 빨간색 오류 메시지  
**해결**: 
- 콘솔 로그 확인
- 오류 메시지 복사하여 전달

### 원인 3: CSS 문제
**증상**: 카드가 생성되었지만 화면에 보이지 않음  
**해결**:
```javascript
// 콘솔에서 확인
const cards = document.querySelectorAll('.service-card');
console.log('Total cards:', cards.length);

cards.forEach((card, index) => {
    console.log(`Card ${index}:`, {
        visible: card.offsetParent !== null,
        opacity: getComputedStyle(card).opacity,
        display: getComputedStyle(card).display
    });
});
```

### 원인 4: Grid 레이아웃 문제
**증상**: Additional services grid가 제대로 렌더링되지 않음  
**해결**:
```javascript
// 콘솔에서 실행
const grid = document.getElementById('additional-services-grid');
console.log('Grid style:', getComputedStyle(grid).display);
console.log('Grid children:', grid.children.length);
```

---

## 📊 체크리스트

배포 후 다음 사항을 확인하세요:

- [ ] 서버에 최신 코드 배포 완료 (Commit: 1f7c9de)
- [ ] Nginx 재시작 완료
- [ ] 브라우저 캐시 삭제
- [ ] 강제 새로고침 (Ctrl + F5)
- [ ] 콘솔 로그 확인
  - [ ] "Loading services..." 출력됨
  - [ ] "🔍 Additional Services:" 출력됨
  - [ ] "✅ Creating card for: DDoS Tester" 출력됨
  - [ ] "✅ Additional services loaded: 4" 출력됨
- [ ] DDoS Tester 카드가 화면에 표시됨

---

## 📸 스크린샷 확인 방법

### 정상 상태 (예상)
```
┌─────────────────┬─────────────────┐
│ System Monitor  │ Auth Service    │
├─────────────────┼─────────────────┤
│ AI Assistant    │ DDoS Tester ⚡  │
└─────────────────┴─────────────────┘
```

### 문제 상태 (현재)
```
┌─────────────────┬─────────────────┐
│ System Monitor  │ Auth Service    │
├─────────────────┼─────────────────┤
│ AI Assistant    │     빈 공간     │
└─────────────────┴─────────────────┘
```

---

## 🆘 긴급 연락

**문제가 계속될 경우:**
1. 브라우저 콘솔 전체 로그 복사
2. 스크린샷 캡처 (F12 콘솔 포함)
3. 네트워크 탭에서 index.html 로드 상태 확인

---

**작성일**: 2025-12-15  
**예상 소요 시간**: 5분 (배포 + 테스트)  
**다음 단계**: 콘솔 로그 확인 후 추가 조치 결정
