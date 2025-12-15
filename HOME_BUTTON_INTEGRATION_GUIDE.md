# 🏠 NeuralGrid 홈 버튼 통합 가이드

## 📅 작업 일시
- **작성**: 2025-12-15
- **대상 서비스**: 모든 NeuralGrid 서브 서비스

---

## 🎯 목표

모든 서브 서비스에 메인 페이지(https://neuralgrid.kr)로 돌아갈 수 있는 홈 버튼 추가

---

## 📦 대상 서비스 (7개)

1. ✅ **monitor.neuralgrid.kr** - System Monitor (완료)
2. **mfx.neuralgrid.kr** - MediaFX Shorts
3. **music.neuralgrid.kr** - NeuronStar Music
4. **bn-shop.neuralgrid.kr** - BN Shop
5. **n8n.neuralgrid.kr** - N8N Automation
6. **auth.neuralgrid.kr** - Auth Service
7. (Main Page는 자체적으로 네비게이션이 있으므로 제외)

---

## 🛠️ Next.js 앱을 위한 홈 버튼 컴포넌트

### 방법 1: React 컴포넌트로 추가 (권장)

각 Next.js 앱의 `app/layout.tsx` 또는 `app/components/` 폴더에 홈 버튼 컴포넌트 추가

#### 1. 홈 버튼 컴포넌트 파일 생성

`app/components/HomeButton.tsx`:

```typescript
'use client';

export default function HomeButton() {
  return (
    <>
      <style jsx>{`
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

        @media (max-width: 768px) {
          .neuralgrid-home-btn {
            top: 10px;
            left: 10px;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
          }
          .neuralgrid-home-icon {
            font-size: 1rem;
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
          .neuralgrid-home-icon {
            font-size: 1.3rem;
          }
        }
      `}</style>
      
      <a 
        href="https://neuralgrid.kr" 
        className="neuralgrid-home-btn" 
        title="NeuralGrid 메인으로 돌아가기"
      >
        <span className="neuralgrid-home-icon">🏠</span>
        <span className="neuralgrid-home-text">NeuralGrid 홈</span>
      </a>
    </>
  );
}
```

#### 2. Layout에 컴포넌트 추가

`app/layout.tsx`:

```typescript
import HomeButton from './components/HomeButton';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="ko">
      <body>
        <HomeButton />  {/* 여기에 추가 */}
        {children}
      </body>
    </html>
  );
}
```

---

### 방법 2: globals.css에 스타일 추가 (더 간단)

#### 1. `app/globals.css`에 스타일 추가

```css
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

@media (max-width: 768px) {
  .neuralgrid-home-btn {
    top: 10px;
    left: 10px;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
  }
  .neuralgrid-home-icon {
    font-size: 1rem;
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
  .neuralgrid-home-icon {
    font-size: 1.3rem;
  }
}
```

#### 2. `app/layout.tsx`에 HTML 추가

```typescript
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="ko">
      <body>
        {/* NeuralGrid Home Button */}
        <a 
          href="https://neuralgrid.kr" 
          className="neuralgrid-home-btn" 
          title="NeuralGrid 메인으로 돌아가기"
        >
          <span className="neuralgrid-home-icon">🏠</span>
          <span className="neuralgrid-home-text">NeuralGrid 홈</span>
        </a>
        
        {children}
      </body>
    </html>
  );
}
```

---

## 🚀 빠른 배포 스크립트

각 서비스에 SSH로 접속하여 다음 명령 실행:

```bash
# 1. MediaFX Shorts
cd /var/www/mfx.neuralgrid.kr
# globals.css에 스타일 추가 및 layout.tsx 수정 후
pm2 restart mfx-shorts

# 2. NeuronStar Music
cd /home/azamans/n8n-neuralgrid/apps/neuronstar-music
# globals.css에 스타일 추가 및 layout.tsx 수정 후
pm2 restart neuronstar-music

# 3. BN Shop
cd /home/azamans/n8n-neuralgrid/apps/bn-shop
# (경로 확인 필요)
# globals.css에 스타일 추가 및 layout.tsx 수정 후
pm2 restart bn-shop

# 4. Auth Service
cd /home/azamans/n8n-neuralgrid/auth-service
# globals.css에 스타일 추가 및 layout.tsx 수정 후
pm2 restart auth-service
```

---

## 📝 체크리스트

각 서비스별로 다음 항목 확인:

- [ ] `app/globals.css`에 홈 버튼 스타일 추가
- [ ] `app/layout.tsx`에 홈 버튼 HTML 추가
- [ ] PM2로 서비스 재시작
- [ ] 브라우저에서 테스트 (홈 버튼 표시 확인)
- [ ] 홈 버튼 클릭 시 https://neuralgrid.kr로 이동 확인
- [ ] 모바일 반응형 테스트

---

## 🎨 디자인 특징

- **위치**: 좌측 상단 (top: 20px, left: 20px)
- **스타일**: 그라디언트 배경 (보라색)
- **애니메이션**: 
  - Hover 시 위로 올라가며 확대
  - Active 시 살짝 눌리는 효과
- **반응형**:
  - Desktop: 🏠 + "NeuralGrid 홈" 텍스트
  - Tablet: 약간 작은 버튼
  - Mobile: 🏠 아이콘만 (원형 버튼)

---

## 🔧 문제 해결

### 홈 버튼이 표시되지 않는 경우

1. **z-index 확인**: 다른 요소가 위에 있는지 확인
2. **스타일 충돌**: 기존 CSS와 충돌하는지 확인
3. **빌드 필요**: Next.js는 빌드가 필요할 수 있음
   ```bash
   npm run build
   pm2 restart <service-name>
   ```

### 클릭이 안되는 경우

- `pointer-events: none` 같은 스타일이 적용되었는지 확인
- 다른 요소가 위에 있는지 확인

---

## ✅ 완료 상태

- [x] Monitor (monitor.neuralgrid.kr) - 완료 ✅
- [ ] MediaFX Shorts (mfx.neuralgrid.kr) - 대기
- [ ] NeuronStar Music (music.neuralgrid.kr) - 대기
- [ ] BN Shop (bn-shop.neuralgrid.kr) - 대기
- [ ] N8N Automation (n8n.neuralgrid.kr) - 대기
- [ ] Auth Service (auth.neuralgrid.kr) - 대기

---

**작성자**: AI Assistant (Claude)  
**작성일**: 2025-12-15  
**버전**: v1.0.0  
**상태**: 📋 가이드 작성 완료
