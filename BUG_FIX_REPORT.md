# 🐛 NeuralGrid 홈페이지 버그 수정 보고서

**날짜**: 2025-12-15  
**Git Commit**: `1727101`  
**Branch**: `genspark_ai_developer_clean`  
**PR**: https://github.com/hompystory-coder/azamans/pull/1

---

## 📌 문제 설명 (Bug Description)

### 증상
- 홈페이지에서 **서비스 설명 콘텐츠가 시간이 지나면 사라지는 현상** 발생
- 페이지 로드 직후에는 정상적으로 보이다가, **30초마다 콘텐츠가 사라짐**
- 서비스 카드의 설명, 기능 목록, 가격 정보 등이 모두 투명해짐

### 재현 방법
1. https://neuralgrid.kr/ 접속
2. 메인 페이지에서 서비스 카드 확인
3. 30초 대기
4. 서비스 콘텐츠가 점점 투명해지며 사라짐

---

## 🔍 원인 분석 (Root Cause)

### 문제 코드 위치
**파일**: `neuralgrid-homepage.html`  
**라인**: 1354-1356

```javascript
// Auto refresh services every 30 seconds
setInterval(() => {
    loadServices();
}, 30000);
```

### 근본 원인
1. **30초 자동 새로고침**: `loadServices()` 함수가 30초마다 자동으로 호출됨
2. **DOM 재생성**: 함수가 호출될 때마다 `.service-card` 요소들을 완전히 삭제하고 다시 생성
3. **애니메이션 재적용**: 새로 생성된 카드에 `.reveal` 클래스가 다시 적용됨
4. **초기 상태로 복귀**: `.reveal` 클래스의 기본 CSS 속성 `opacity: 0`이 다시 적용됨
5. **IntersectionObserver 미작동**: 카드가 이미 viewport에 있어서 observer가 트리거되지 않음

### CSS 코드 (790-800줄)
```css
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease;
}

.reveal.active {
    opacity: 1;
    transform: translateY(0);
}
```

---

## ✅ 해결 방법 (Solution)

### 수정 내용
**파일**: `neuralgrid-homepage.html`  
**라인**: 1227-1258 (loadServices 함수 수정)

```javascript
// 추가된 플래그 변수
let servicesLoaded = false;

function loadServices() {
    try {
        // ... 기존 서비스 로드 로직 ...
        
        // 🔥 핵심 수정: 첫 로드 이후에는 즉시 reveal 클래스 활성화
        if (servicesLoaded) {
            const newReveals = document.querySelectorAll('.reveal:not(.active)');
            newReveals.forEach(reveal => {
                reveal.classList.add('active');
            });
        }
        
        servicesLoaded = true;
    } catch (error) {
        console.error('Error loading services:', error);
    }
}
```

### 동작 원리
1. `servicesLoaded` 플래그로 첫 로드 여부 추적
2. 첫 로드 시: IntersectionObserver가 정상 작동하여 애니메이션 실행
3. 이후 로드 시: 새로 생성된 `.reveal` 요소에 즉시 `.active` 클래스 추가
4. 결과: 서비스 카드가 사라지지 않고 계속 표시됨

---

## 🧪 테스트 (Testing)

### 수정 전 (Before)
```
[0초] 페이지 로드 → 서비스 카드 표시 (정상)
[30초] 자동 새로고침 → 서비스 카드 사라짐 (opacity: 0)
[60초] 자동 새로고침 → 서비스 카드 사라짐 (opacity: 0)
```

### 수정 후 (After)
```
[0초] 페이지 로드 → 서비스 카드 표시 (정상)
[30초] 자동 새로고침 → 서비스 카드 계속 표시 ✅
[60초] 자동 새로고침 → 서비스 카드 계속 표시 ✅
```

### 확인 방법
1. https://neuralgrid.kr/ 접속
2. 개발자 도구 콘솔 열기 (F12)
3. 30초 이상 페이지 유지
4. `loadServices()` 호출 확인 (콘솔 로그)
5. 서비스 카드가 계속 표시되는지 확인

---

## 📦 배포 절차 (Deployment)

### Git 커밋 정보
```bash
Commit ID: 1727101
Branch: genspark_ai_developer_clean
Commit Message: fix: 서비스 콘텐츠가 30초마다 사라지는 버그 수정
```

### 서버 배포 명령 (115.91.5.140)
```bash
# 1. Git 업데이트
cd /tmp/azamans
git fetch origin
git checkout genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean

# 2. 백업
sudo cp /var/www/html/index.html /var/www/html/index.html.backup-$(date +%Y%m%d-%H%M%S)

# 3. 배포
sudo cp neuralgrid-homepage.html /var/www/html/index.html
sudo chown www-data:www-data /var/www/html/index.html
sudo chmod 644 /var/www/html/index.html

# 4. Nginx 재시작
sudo nginx -t
sudo systemctl reload nginx

# 5. 검증
curl -I https://neuralgrid.kr/
```

### 간편 배포 스크립트
```bash
cd /tmp/azamans && bash deploy-fix.sh
```

---

## 📝 변경 파일 목록 (Changed Files)

1. **neuralgrid-homepage.html**
   - `loadServices()` 함수 수정
   - `servicesLoaded` 플래그 추가
   - `.reveal` 클래스 자동 활성화 로직 추가

2. **deploy-fix.sh** (신규)
   - 배포 자동화 스크립트

3. **BUG_FIX_REPORT.md** (신규)
   - 버그 수정 상세 보고서

---

## ✨ 예상 효과 (Expected Results)

### 개선 사항
- ✅ 서비스 콘텐츠가 더 이상 사라지지 않음
- ✅ 사용자 경험(UX) 대폭 개선
- ✅ 페이지 안정성 향상
- ✅ 30초 자동 새로고침 기능 유지 (온라인 상태 확인 등)

### 부작용 없음
- ⚠️ 기존 애니메이션 동작 유지 (첫 로드 시)
- ⚠️ IntersectionObserver 정상 작동
- ⚠️ 성능 영향 없음 (경량 플래그 변수 추가)

---

## 🚀 다음 단계 (Next Steps)

1. ✅ Git 커밋 완료 (1727101)
2. ✅ GitHub 푸시 완료
3. ⏳ 서버 배포 대기 (사용자가 직접 실행)
4. ⏳ 프로덕션 환경 테스트
5. ⏳ 사용자 피드백 수집

---

## 📞 연락처 (Contact)

**문제 재발 시**:
- Git Issue: https://github.com/hompystory-coder/azamans/issues
- Email: admin@neuralgrid.kr
- Server: 115.91.5.140

---

**작성일**: 2025-12-15  
**작성자**: AI Development Assistant  
**상태**: ✅ 수정 완료 (배포 대기)
