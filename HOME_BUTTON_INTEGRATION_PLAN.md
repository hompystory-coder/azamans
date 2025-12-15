# 🏠 Home Button Integration Plan

## 목표
모든 NeuralGrid 서브사이트에 메인페이지(https://neuralgrid.kr)로 돌아가는 홈 버튼 추가

---

## 대상 서브사이트 (6개)

### 1️⃣ Monitor (https://monitor.neuralgrid.kr) ✅
- **경로**: `/home/azamans/n8n-neuralgrid/monitor-server/public/index.html`
- **상태**: 이미 홈 버튼 존재 ✅
- **작업**: 완료됨

### 2️⃣ MediaFX Shorts (https://mfx.neuralgrid.kr)
- **경로**: `/home/azamans/mfx-redesign/` 또는 `/home/azamans/mfx-web-ui-v2/`
- **포트**: 3101
- **상태**: 확인 필요
- **작업**: 홈 버튼 추가 필요

### 3️⃣ NeuronStar Music (https://music.neuralgrid.kr)
- **경로**: 확인 필요
- **포트**: 3002
- **상태**: 확인 필요
- **작업**: 홈 버튼 추가 필요

### 4️⃣ BN Shop (https://bn-shop.neuralgrid.kr)
- **경로**: `/home/azamans/bn-shop-webapp/`
- **포트**: 3001
- **상태**: 확인 필요
- **작업**: 홈 버튼 추가 필요

### 5️⃣ N8N Automation (https://n8n.neuralgrid.kr)
- **경로**: Docker 컨테이너
- **포트**: 5678
- **상태**: 외부 애플리케이션 (n8n.io)
- **작업**: Nginx reverse proxy로 홈 버튼 추가 or 커스텀 헤더

### 6️⃣ Auth Service (https://auth.neuralgrid.kr)
- **경로**: `/home/azamans/n8n-neuralgrid/auth-service/`
- **포트**: 3099
- **상태**: 확인 필요
- **작업**: 홈 버튼 추가 필요

---

## Home Button Component

### 디자인
```html
<!-- CSS -->
<style>
    .neuralgrid-home-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.95rem;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        border: none;
        cursor: pointer;
        backdrop-filter: blur(10px);
    }

    .neuralgrid-home-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.5);
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
    }

    .neuralgrid-home-btn:active {
        transform: translateY(-1px) scale(1.02);
    }

    .neuralgrid-home-icon {
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .neuralgrid-home-text {
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .neuralgrid-home-btn {
            top: 10px;
            left: 10px;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .neuralgrid-home-text {
            display: none;
        }
        .neuralgrid-home-btn {
            padding: 0.75rem;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            justify-content: center;
        }
    }
</style>

<!-- HTML -->
<a href="https://neuralgrid.kr" class="neuralgrid-home-btn" title="NeuralGrid 메인으로 돌아가기">
    <span class="neuralgrid-home-icon">🏠</span>
    <span class="neuralgrid-home-text">NeuralGrid 홈</span>
</a>
```

---

## 작업 순서

### Phase 1: 조사 및 확인
1. ✅ Monitor 페이지 - 이미 홈 버튼 존재
2. 각 서브사이트의 메인 HTML/React 파일 찾기
3. 현재 홈 버튼 존재 여부 확인

### Phase 2: 구현
1. MediaFX Shorts - 홈 버튼 추가
2. NeuronStar Music - 홈 버튼 추가
3. BN Shop - 홈 버튼 추가
4. Auth Service - 홈 버튼 추가
5. N8N - Nginx 레벨에서 헤더 추가 (선택사항)

### Phase 3: 테스트
1. 각 서브사이트 접속 테스트
2. 홈 버튼 클릭 테스트
3. 반응형 디자인 확인 (Desktop, Tablet, Mobile)

### Phase 4: 배포
1. PM2 서비스 재시작
2. 캐시 클리어
3. 최종 확인

---

## 예상 소요 시간
- Phase 1: 10분
- Phase 2: 30분 (서브사이트당 5분)
- Phase 3: 10분
- Phase 4: 5분

**총 예상 시간**: 55분

---

## 주의사항

### React/Vue 앱의 경우
- App.js 또는 Layout 컴포넌트에 추가
- 또는 index.html에 직접 추가

### Next.js 앱의 경우
- _app.js 또는 Layout 컴포넌트에 추가
- 또는 _document.js에 추가

### Docker 컨테이너 앱 (N8N)
- Nginx reverse proxy에 커스텀 헤더 추가
- 또는 iframe wrapper 생성

---

**작성자**: AI Assistant (Claude)  
**작성일**: 2025-12-15  
**상태**: 진행 중
