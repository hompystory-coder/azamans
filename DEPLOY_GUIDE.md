# 🚀 NeuralGrid 메인페이지 배포 가이드

**작성일**: 2025-12-15  
**업데이트**: 고급 UI 리뉴얼 + 홈 버튼 컴포넌트

---

## 📋 변경 사항 요약

### ✨ 메인페이지 개선사항

1. **고급스러운 UI 디자인**
   - 더 세련된 그라디언트 및 애니메이션
   - 향상된 카드 호버 효과
   - 부드러운 스크롤 리빌 애니메이션
   - 반응형 디자인 개선

2. **각 서브 콘텐츠 상세 PR 추가**
   - MediaFX Shorts: AI 쇼츠 생성 플랫폼 상세 설명
   - NeuronStar Music: 무료 AI 음악 생성 강조
   - BN Shop: AI 이커머스 기능 소개
   - System Monitor: 실시간 모니터링 기능
   - N8N Automation: 워크플로우 자동화 설명
   - Auth Service: 통합 인증 시스템 소개

3. **서브사이트 홈 버튼 컴포넌트**
   - 모든 서브사이트에서 메인으로 쉽게 돌아갈 수 있는 버튼
   - 반응형 디자인 (모바일에서 아이콘만 표시)
   - 고급스러운 호버 애니메이션

---

## 📁 파일 위치

### 생성된 파일

| 파일명 | 위치 | 설명 |
|--------|------|------|
| `neuralgrid-main-page.html` | `/home/azamans/webapp/` | 새로운 메인페이지 (고급 UI) |
| `home-button-component.html` | `/home/azamans/webapp/` | 홈 버튼 컴포넌트 |
| `DEPLOY_GUIDE.md` | `/home/azamans/webapp/` | 이 가이드 |

### 배포 대상

| 서비스 | 배포 위치 | 작업 |
|--------|-----------|------|
| **메인페이지** | `/var/www/neuralgrid.kr/html/index.html` | 파일 교체 필요 |
| MediaFX Shorts | `https://mfx.neuralgrid.kr` | 홈 버튼 추가 |
| NeuronStar Music | `https://music.neuralgrid.kr` | 홈 버튼 추가 |
| BN Shop | `https://bn-shop.neuralgrid.kr` | 홈 버튼 추가 |
| N8N | `https://n8n.neuralgrid.kr` | 홈 버튼 추가 (선택) |
| Monitor | `https://monitor.neuralgrid.kr` | 홈 버튼 추가 (선택) |

---

## 🔧 배포 방법

### 1단계: 메인페이지 배포

새 메인페이지를 배포하려면 **sudo 권한**이 필요합니다.

```bash
# 1. 현재 파일 백업
sudo cp /var/www/neuralgrid.kr/html/index.html \
        /var/www/neuralgrid.kr/html/index.html.backup_$(date +%Y%m%d_%H%M%S)

# 2. 새 파일 복사
sudo cp /home/azamans/webapp/neuralgrid-main-page.html \
        /var/www/neuralgrid.kr/html/index.html

# 3. 파일 권한 설정
sudo chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
sudo chmod 644 /var/www/neuralgrid.kr/html/index.html

# 4. Nginx 설정 확인
sudo nginx -t

# 5. Nginx 재시작 (필요시)
sudo systemctl reload nginx

# 6. 확인
curl -I https://neuralgrid.kr
```

**또는 간단하게:**

```bash
# 준비된 파일을 /tmp에서 복사
sudo cp /tmp/index.html /var/www/neuralgrid.kr/html/index.html
sudo chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
```

---

### 2단계: 서브사이트에 홈 버튼 추가

#### 방법 A: 수동 추가 (권장)

각 서브사이트의 HTML 파일을 수정합니다.

**1. CSS 추가 (`<head>` 태그 안)**

```html
<style>
    /* NeuralGrid Home Button */
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
    }

    .neuralgrid-home-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.5);
    }

    .neuralgrid-home-icon {
        font-size: 1.2rem;
    }

    /* 모바일 반응형 */
    @media (max-width: 480px) {
        .neuralgrid-home-btn .neuralgrid-home-text {
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
```

**2. HTML 추가 (`<body>` 시작 부분)**

```html
<a href="https://neuralgrid.kr" class="neuralgrid-home-btn" title="NeuralGrid 메인으로">
    <span class="neuralgrid-home-icon">🏠</span>
    <span class="neuralgrid-home-text">NeuralGrid 홈</span>
</a>
```

#### 방법 B: JavaScript로 동적 삽입

모든 서브사이트에 다음 스크립트를 추가:

```html
<script>
    // NeuralGrid 홈 버튼 자동 삽입
    (function() {
        const style = document.createElement('style');
        style.textContent = `
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
                transition: all 0.3s;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }
            .neuralgrid-home-btn:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 8px 20px rgba(99, 102, 241, 0.5);
            }
        `;
        document.head.appendChild(style);

        const button = document.createElement('a');
        button.href = 'https://neuralgrid.kr';
        button.className = 'neuralgrid-home-btn';
        button.title = 'NeuralGrid 메인으로';
        button.innerHTML = `
            <span class="neuralgrid-home-icon">🏠</span>
            <span class="neuralgrid-home-text">NeuralGrid 홈</span>
        `;
        document.body.insertBefore(button, document.body.firstChild);
    })();
</script>
```

---

## 🎨 UI 개선 상세 내역

### 메인페이지 변경사항

#### Before (이전)
- 기본적인 다크 테마
- 단순한 카드 디자인
- 정적인 레이아웃

#### After (개선)
✨ **고급 디자인 요소**
- 애니메이션 배경 (부드러운 그라디언트 효과)
- 펄스 애니메이션 로고
- 스크롤 리빌 애니메이션
- 향상된 카드 호버 효과 (3D transform)
- 세련된 그라디언트 텍스트
- 반응형 통계 카드

✨ **콘텐츠 개선**
- 각 서비스별 상세 설명 추가
- 주요 기능 5개 리스트업
- 가격 정보 명시
- 온라인/오프라인 상태 표시 개선

✨ **UX 개선**
- 더 명확한 CTA 버튼
- 부드러운 스크롤 애니메이션
- 모바일 최적화
- 접근성 향상

---

## 📊 서비스별 PR 내용

### 🎬 MediaFX Shorts
**타이틀**: AI 기반 숏폼 비디오 자동 생성  
**핵심 메시지**: 블로그 → 쇼츠 영상 4-5분 만에 자동 변환  
**강점**:
- Gemini 2.0 + Pollinations.AI + Kling v2.1 통합
- 영상당 $0.06의 저렴한 비용
- 한글 자막 완벽 지원

### 🎵 NeuronStar Music
**타이틀**: 100% 무료 AI 음악 생성  
**핵심 메시지**: 상업적 이용 가능한 무료 음악  
**강점**:
- 완전 무료 (Free Forever)
- 다양한 장르 지원
- 커스텀 가사 생성
- 고품질 오디오

### 🛒 BN Shop
**타이틀**: AI 이커머스 플랫폼  
**핵심 메시지**: AI 추천 & 자동 재고 관리  
**강점**:
- AI 상품 추천 엔진
- 실시간 재고 관리
- 통합 결제 시스템
- 베타 테스트 무료

### 🖥️ System Monitor
**타이틀**: 실시간 시스템 모니터링  
**핵심 메시지**: 서버 상태를 한눈에  
**강점**:
- 실시간 리소스 모니터링
- PM2 프로세스 관리
- 자동 알림 시스템
- 30초 자동 새로고침

### ⚙️ N8N Automation
**타이틀**: 워크플로우 자동화 엔진  
**핵심 메시지**: 비즈니스 프로세스 자동화  
**강점**:
- 200+ 앱 통합
- 드래그 앤 드롭 빌더
- REST API 자동화
- 무료 Self-hosted

### 🔐 Auth Service
**타이틀**: 통합 인증 시스템  
**핵심 메시지**: JWT 기반 보안 인증  
**강점**:
- JWT 보안 인증
- API 키 자동 발급
- 크레딧 시스템
- 세션 관리

---

## ✅ 체크리스트

### 메인페이지 배포
- [ ] 현재 파일 백업 완료
- [ ] 새 파일 복사 완료
- [ ] 파일 권한 설정 완료
- [ ] Nginx 설정 확인
- [ ] 브라우저 테스트 (https://neuralgrid.kr)
- [ ] 모바일 반응형 확인

### 홈 버튼 추가
- [ ] MediaFX Shorts (mfx.neuralgrid.kr)
- [ ] NeuronStar Music (music.neuralgrid.kr)
- [ ] BN Shop (bn-shop.neuralgrid.kr)
- [ ] System Monitor (monitor.neuralgrid.kr)
- [ ] N8N (n8n.neuralgrid.kr)
- [ ] Auth Service (auth.neuralgrid.kr)

### 테스트
- [ ] 메인페이지 로딩 속도 확인
- [ ] 서비스 카드 API 연동 확인
- [ ] 통계 데이터 로딩 확인
- [ ] 모든 링크 작동 확인
- [ ] 홈 버튼 작동 확인 (서브사이트 → 메인)

---

## 🐛 문제 해결

### 메인페이지가 업데이트되지 않을 때
```bash
# 브라우저 캐시 강제 새로고침
Ctrl + F5 (Windows)
Cmd + Shift + R (Mac)

# 또는 Nginx 캐시 클리어
sudo rm -rf /var/cache/nginx/*
sudo systemctl reload nginx
```

### API 데이터가 로드되지 않을 때
```bash
# API 서비스 확인
curl http://localhost:3200/api/dashboard/stats
curl http://localhost:3200/api/dashboard/services/status

# PM2 프로세스 확인
pm2 list
pm2 logs dashboard-api
```

### 홈 버튼이 표시되지 않을 때
1. 브라우저 개발자 도구 (F12) 열기
2. Console 탭에서 에러 확인
3. CSS가 제대로 로드되었는지 확인
4. z-index 충돌 확인

---

## 📞 지원

**문제 발생 시 연락처:**
- Email: admin@neuralgrid.kr
- Server: 115.91.5.140
- GitHub Issues: (저장소 링크)

---

**배포 완료 날짜**: ____________  
**배포 담당자**: ____________  
**확인자**: ____________
