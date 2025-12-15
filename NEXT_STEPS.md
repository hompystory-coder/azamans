# 🚀 다음 단계 가이드

## 📍 현재 상태

✅ **Pull Request 생성 완료**
- **PR URL:** https://github.com/hompystory-coder/azamans/pull/1
- **상태:** Open (리뷰 대기 중)
- **브랜치:** `genspark_ai_developer_clean` → `main`

---

## 🎯 즉시 수행할 작업 (High Priority)

### 1. Pull Request 검토 및 승인 ⭐⭐⭐
**담당자:** 프로젝트 관리자 또는 리뷰어

**작업 내용:**
```bash
# PR 확인
1. https://github.com/hompystory-coder/azamans/pull/1 접속
2. 변경 사항 검토 (Files changed 탭)
3. 코드 리뷰 수행
4. 필요시 코멘트 작성
5. Approve & Merge 또는 Request Changes

# Merge 후
git checkout main
git pull origin main
```

**예상 소요 시간:** 30분

---

### 2. 홈 버튼을 모든 서브사이트에 추가 ⭐⭐⭐

#### 📋 작업 대상 서브사이트
1. MediaFX Shorts (`mfx.neuralgrid.kr`)
2. NeuronStar Music (`music.neuralgrid.kr`)
3. BN Shop (`bn-shop.neuralgrid.kr`)
4. System Monitor (`monitor.neuralgrid.kr`)
5. N8N Automation (`n8n.neuralgrid.kr`)
6. Auth Service (`auth.neuralgrid.kr`)

#### 🔧 작업 방법

**Step 1: 홈 버튼 컴포넌트 파일 확인**
```bash
# 로컬에서 확인
cat /home/azamans/webapp/home-button-component.html
```

**Step 2: 각 서브사이트 HTML 파일 수정**

각 서브사이트의 `</body>` 태그 직전에 다음 코드 삽입:

```html
<!-- 홈 버튼 (메인 페이지로 돌아가기) -->
<div style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
  <a href="https://neuralgrid.kr" 
     style="display: flex; align-items: center; gap: 8px; 
            padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; text-decoration: none; border-radius: 50px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            font-weight: 600; font-size: 14px; transition: all 0.3s ease;"
     onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.6)';"
     onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)';"
     aria-label="메인 페이지로 돌아가기">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
      <polyline points="9 22 9 12 15 12 15 22"></polyline>
    </svg>
    <span>홈</span>
  </a>
</div>
<!-- 반응형: 모바일에서는 하단 중앙에 표시 -->
<style>
@media (max-width: 768px) {
  div[style*="position: fixed"] {
    bottom: 10px !important;
    right: 50% !important;
    transform: translateX(50%);
  }
}
</style>
```

**Step 3: 배포**

각 서브사이트별로 수정 후 배포:
```bash
# 예: MediaFX Shorts
cd /path/to/mfx.neuralgrid.kr
# HTML 파일 수정
# Git commit & push
# 서버 재시작 (필요시)
```

**예상 소요 시간:** 서브사이트당 10분, 총 1시간

---

### 3. AnythingLLM 초기 설정 ⭐⭐

#### 📍 접속 정보
- **URL:** http://115.91.5.140:3104
- **상태:** 설치 완료, 초기 설정 대기

#### 🔧 작업 내용

**Step 1: AnythingLLM 접속**
```bash
# 브라우저에서 접속
http://115.91.5.140:3104
```

**Step 2: 관리자 계정 생성**
1. 최초 접속 시 관리자 계정 생성 화면 표시
2. 다음 정보로 계정 생성:
   - Username: `admin`
   - Email: `aza700901@nate.com`
   - Password: `[안전한 비밀번호]`

**Step 3: 워크스페이스 생성**
1. 새 워크스페이스 생성
2. 이름: `NeuralGrid Development`
3. 설명: `NeuralGrid 플랫폼 개발 및 운영`

**Step 4: 문서 업로드**
다음 문서들을 워크스페이스에 업로드:
- `SERVER_MASTER_DOCUMENT.md`
- `RAG_MULTI_AI_SYSTEM_PLAN.md`
- `DEPLOY_GUIDE.md`
- `neuralgrid-ai/README.md`

**Step 5: AI 모델 연결**
1. Settings → LLM Provider 선택
2. Ollama 선택
3. Endpoint: `http://localhost:11434`
4. Model: `llama3.1:8b` 선택
5. Test Connection 클릭

**Step 6: 벡터 데이터베이스 연결**
1. Settings → Vector Database 선택
2. PostgreSQL + pgvector 선택
3. Connection String:
   ```
   postgresql://neuralgrid:[password]@localhost:5435/memory_db
   ```
4. Test Connection 클릭

**예상 소요 시간:** 30분

---

## 📅 단기 작업 (Medium Priority)

### 4. 환경변수 설정 ⭐⭐

#### 🔧 작업 내용

**Step 1: .env 파일 생성**
```bash
cd /home/azamans/webapp
cp .env.example .env
nano .env
```

**Step 2: 실제 API 키 입력**
```bash
# OpenAI
OPENAI_API_KEY=sk-proj-[your_actual_key]

# Claude (Anthropic)
CLAUDE_API_KEY=sk-ant-api03-[your_actual_key]

# Google Gemini
GEMINI_API_KEY=AIzaSy[your_actual_key]

# MiniMax
MINIMAX_GROUP_ID=[your_group_id]
MINIMAX_API_KEY=eyJhbGci[your_actual_key]

# OpenRouter
OPENROUTER_API_KEY=sk-or-v1-[your_actual_key]

# Replicate
REPLICATE_API_KEY=r8_[your_actual_key]

# ElevenLabs
ELEVENLABS_API_KEY=sk_[your_actual_key]

# Shotstack
SHOTSTACK_SANDBOX_OWNER_ID=[your_owner_id]
SHOTSTACK_SANDBOX_API_KEY=[your_api_key]
SHOTSTACK_PROD_OWNER_ID=[your_owner_id]
SHOTSTACK_PROD_API_KEY=[your_api_key]

# Cloudflare
CLOUDFLARE_API_TOKEN=[your_token]
CLOUDFLARE_ACCOUNT_ID=[your_account_id]

# Supabase
SUPABASE_PUBLISHABLE_KEY=[your_key]
SUPABASE_ANON_KEY=[your_key]
SUPABASE_SERVICE_ROLE_KEY=[your_key]

# 쿠팡 파트너스
COUPANG_ACCESS_KEY=[your_key]
COUPANG_SECRET_KEY=[your_key]

# Google OAuth
GOOGLE_CLIENT_ID=[your_client_id]
GOOGLE_CLIENT_SECRET=[your_secret]

# Database Passwords
N8N_DB_PASSWORD=[your_password]
MEMORY_DB_PASSWORD=[your_password]
```

**Step 3: 권한 설정**
```bash
chmod 600 .env
```

**Step 4: .gitignore 확인**
```bash
# .env가 .gitignore에 포함되어 있는지 확인
cat .gitignore | grep .env
```

**예상 소요 시간:** 15분

---

### 5. Dify.ai 워크플로우 구성 ⭐⭐

#### 🔧 작업 내용

**Step 1: Dify.ai 실행**
```bash
cd /home/azamans/webapp/neuralgrid-ai/dify/docker
cp .env.example .env
nano .env  # 환경변수 수정

# Docker Compose 실행
docker-compose up -d

# 상태 확인
docker-compose ps
```

**Step 2: Dify.ai 접속**
```bash
# 브라우저에서 접속 (포트는 .env 설정에 따라 다름)
http://115.91.5.140:3000  # 또는 설정된 포트
```

**Step 3: 관리자 계정 생성**
- Email: `aza700901@nate.com`
- Password: `[안전한 비밀번호]`

**Step 4: 첫 번째 워크플로우 생성**
1. "Create Workflow" 클릭
2. 워크플로우 이름: `Blog to Shorts`
3. 노드 구성:
   - Input: Blog URL
   - Crawler: 블로그 콘텐츠 크롤링
   - AI: 스토리 생성 (GPT-4)
   - AI: 이미지 프롬프트 생성 (Claude)
   - API: 이미지 생성 (Replicate)
   - API: 영상 생성 (Kling v2.1)
   - Output: 영상 URL

**예상 소요 시간:** 1시간

---

### 6. API 비용 모니터링 대시보드 ⭐

#### 🔧 작업 내용

**Step 1: Cost Tracking 테이블 활용**
```sql
-- PostgreSQL 연결
PGPASSWORD='[password]' psql -U neuralgrid -d memory_db -p 5435 -h localhost

-- 비용 조회 쿼리
SELECT 
  service,
  model,
  DATE(timestamp) as date,
  SUM(cost) as daily_cost,
  COUNT(*) as request_count
FROM cost_tracking
GROUP BY service, model, DATE(timestamp)
ORDER BY date DESC, daily_cost DESC;
```

**Step 2: 모니터링 스크립트 작성**
```bash
# /home/azamans/webapp/neuralgrid-ai/scripts/monitor_costs.sh
#!/bin/bash

# 오늘 날짜
TODAY=$(date +%Y-%m-%d)

# 비용 조회
psql -U neuralgrid -d memory_db -p 5435 -h localhost << EOF
SELECT 
  service,
  SUM(cost) as total_cost
FROM cost_tracking
WHERE DATE(timestamp) = '$TODAY'
GROUP BY service
ORDER BY total_cost DESC;
EOF
```

**Step 3: System Monitor에 통합**
```javascript
// monitor.neuralgrid.kr에 API 추가
app.get('/api/costs/today', async (req, res) => {
  const result = await db.query(`
    SELECT service, SUM(cost) as total_cost
    FROM cost_tracking
    WHERE DATE(timestamp) = CURRENT_DATE
    GROUP BY service
  `);
  res.json(result.rows);
});
```

**예상 소요 시간:** 2시간

---

## 🔄 장기 작업 (Low Priority)

### 7. Stable Diffusion 설치
- sd.neuralgrid.kr 도메인 설정
- WebUI 설치
- 모델 다운로드
- API 통합

**예상 소요 시간:** 4시간

---

### 8. 통합 인증 시스템 강화
- OAuth 2.0 통합
- SSO (Single Sign-On) 구현
- 2FA (Two-Factor Authentication)
- 세션 관리 개선

**예상 소요 시간:** 8시간

---

### 9. 성능 최적화
- CDN 설정 (Cloudflare)
- 이미지 최적화
- Lazy Loading 구현
- 캐싱 전략 수립

**예상 소요 시간:** 6시간

---

## 📊 작업 우선순위 매트릭스

| 작업 | 우선순위 | 긴급도 | 중요도 | 예상 시간 | 담당자 |
|------|---------|--------|--------|-----------|--------|
| PR 승인 | ⭐⭐⭐ | 높음 | 높음 | 30분 | 관리자 |
| 홈 버튼 추가 | ⭐⭐⭐ | 높음 | 중간 | 1시간 | 개발자 |
| AnythingLLM 설정 | ⭐⭐ | 중간 | 높음 | 30분 | 개발자 |
| 환경변수 설정 | ⭐⭐ | 중간 | 높음 | 15분 | 개발자 |
| Dify.ai 구성 | ⭐⭐ | 중간 | 중간 | 1시간 | 개발자 |
| 비용 모니터링 | ⭐ | 낮음 | 높음 | 2시간 | 개발자 |
| Stable Diffusion | ⭐ | 낮음 | 낮음 | 4시간 | 개발자 |
| 통합 인증 | ⭐ | 낮음 | 중간 | 8시간 | 개발자 |
| 성능 최적화 | ⭐ | 낮음 | 중간 | 6시간 | 개발자 |

---

## 🎯 주간 작업 계획 (Week 1)

### Day 1 (오늘)
- [x] Pull Request 생성
- [ ] PR 승인 대기
- [ ] 홈 버튼 추가 (3개 서브사이트)

### Day 2
- [ ] 홈 버튼 추가 (나머지 3개 서브사이트)
- [ ] AnythingLLM 초기 설정
- [ ] 환경변수 설정

### Day 3
- [ ] Dify.ai 워크플로우 구성
- [ ] 첫 번째 자동화 워크플로우 테스트

### Day 4-5
- [ ] API 비용 모니터링 대시보드 개발
- [ ] 실시간 비용 추적 구현

### Day 6-7
- [ ] 전체 시스템 테스트
- [ ] 문서 업데이트
- [ ] 백업 시스템 구축

---

## 📝 체크리스트

### 즉시 수행 (오늘~내일)
- [ ] PR 승인 완료
- [ ] 홈 버튼 6개 서브사이트 추가
- [ ] AnythingLLM 관리자 계정 생성
- [ ] 환경변수 설정

### 이번 주 수행
- [ ] Dify.ai 워크플로우 구성
- [ ] API 비용 모니터링 대시보드
- [ ] 전체 시스템 통합 테스트

### 이번 달 수행
- [ ] Stable Diffusion 설치
- [ ] 통합 인증 시스템 강화
- [ ] 성능 최적화

---

## 📞 지원 및 문의

### 문서 참고
- **이 문서:** `NEXT_STEPS.md`
- **PR 요약:** `PR_SUMMARY.md`
- **배포 가이드:** `DEPLOY_GUIDE.md`
- **RAG 시스템:** `RAG_MULTI_AI_SYSTEM_PLAN.md`

### 주요 링크
- **Pull Request:** https://github.com/hompystory-coder/azamans/pull/1
- **메인 페이지:** https://neuralgrid.kr
- **AnythingLLM:** http://115.91.5.140:3104
- **System Monitor:** https://monitor.neuralgrid.kr

### 담당자
- **프로젝트 관리자:** azamans
- **이메일:** aza700901@nate.com
- **GitHub:** @hompystory-coder

---

**작성일:** 2025-12-15  
**최종 업데이트:** 2025-12-15  
**버전:** v1.0  
**상태:** ✅ 작성 완료
