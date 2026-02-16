# 🧠 NeuralGrid RAG + Multi-AI System

**대화 맥락을 영구 저장하고 점진적으로 학습하는 개인 AI 시스템**

## 🎯 핵심 기능

- ✅ **영구 메모리**: 모든 대화와 코드를 PostgreSQL + pgvector에 저장
- ✅ **멀티 AI 라우팅**: 작업 복잡도에 따라 최적의 AI 모델 자동 선택
- ✅ **비용 최적화**: 간단한 작업은 로컬 AI로 무료 처리
- ✅ **벡터 검색**: 과거 대화/코드에서 관련 정보만 추출하여 전송
- ✅ **점진적 학습**: 시간이 지날수록 점점 똑똑해지는 시스템

## 📦 설치된 컴포넌트

| 컴포넌트 | 버전 | 포트 | 용도 |
|----------|------|------|------|
| **PostgreSQL + pgvector** | 15.4 | 5435 | 벡터 데이터베이스 (메모리 저장) |
| **Ollama** | 0.13.1 | 11434 | 로컬 AI 모델 실행 |
| **AnythingLLM** | Latest | 3104 | 사용자 인터페이스 |
| **Dify.ai** | Latest | 3103 | AI 워크플로우 엔진 (선택) |

## 🚀 빠른 시작

### 1. 전체 시스템 설치 (최초 1회)

```bash
cd /home/azamans/webapp/neuralgrid-ai/scripts
./install_all.sh
```

**예상 소요 시간:** 30-40분 (Ollama 모델 다운로드 포함)

### 2. 개별 서비스 관리

#### PostgreSQL + pgvector
```bash
# 시작
cd /home/azamans/webapp/neuralgrid-ai/postgres
docker compose up -d

# 중지
docker compose down

# 로그 확인
docker compose logs -f

# 데이터베이스 접속
docker exec -it neuralgrid-memory-db psql -U neuralgrid_ai -d memory_db
```

#### AnythingLLM
```bash
# 시작
cd /home/azamans/webapp/neuralgrid-ai/anythingllm
docker compose up -d

# 브라우저 접속
# http://115.91.5.140:3104

# 중지
docker compose down
```

#### Ollama
```bash
# 모델 목록 확인
ollama list

# 모델 실행 (테스트)
ollama run llama3.1:8b

# 새 모델 다운로드
ollama pull [model_name]
```

#### Dify.ai (선택 사항)
```bash
# 시작 (첫 실행은 10-15분 소요)
cd /home/azamans/webapp/neuralgrid-ai/dify/docker
docker compose up -d

# 브라우저 접속
# http://115.91.5.140:3103

# 중지
docker compose down
```

### 3. 시스템 상태 확인

```bash
# 실행 중인 컨테이너 확인
docker ps

# 모든 서비스 상태
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# 디스크 사용량
docker system df

# 로그 확인
docker compose logs -f [service_name]
```

## 📊 데이터베이스 스키마

### 주요 테이블

#### 1. conversations (대화 히스토리)
```sql
- id: 고유 ID
- session_id: 세션 식별자
- user_message: 사용자 입력
- ai_response: AI 응답
- model_used: 사용된 AI 모델
- tokens_used: 토큰 사용량
- cost_usd: 비용 (USD)
- embedding: 벡터 임베딩 (1536차원)
- created_at: 생성 시간
```

#### 2. code_snippets (코드 스니펫)
```sql
- id: 고유 ID
- project_name: 프로젝트명
- file_path: 파일 경로
- code_content: 코드 내용
- language: 프로그래밍 언어
- description: 설명
- embedding: 벡터 임베딩
- created_at: 생성 시간
```

#### 3. project_contexts (프로젝트 컨텍스트)
```sql
- id: 고유 ID
- project_name: 프로젝트명 (UNIQUE)
- description: 프로젝트 설명
- tech_stack: 기술 스택 (배열)
- key_concepts: 핵심 개념 (배열)
- embedding: 벡터 임베딩
```

### 유용한 SQL 쿼리

```sql
-- 최근 10개 대화 조회
SELECT user_message, ai_response, model_used, created_at 
FROM conversations 
ORDER BY created_at DESC 
LIMIT 10;

-- 모델별 비용 통계 (최근 7일)
SELECT 
    model_used,
    COUNT(*) as request_count,
    SUM(tokens_used) as total_tokens,
    SUM(cost_usd) as total_cost
FROM conversations
WHERE created_at >= NOW() - INTERVAL '7 days'
GROUP BY model_used
ORDER BY total_cost DESC;

-- 유사한 대화 찾기 (함수 사용)
SELECT * FROM find_similar_conversations(
    [your_embedding_vector],  -- 임베딩 벡터
    0.7,                       -- 유사도 임계값
    5                          -- 최대 결과 수
);

-- 프로젝트별 코드 수
SELECT project_name, COUNT(*) as code_count
FROM code_snippets
GROUP BY project_name
ORDER BY code_count DESC;
```

## 🔧 설정 가이드

### AnythingLLM 초기 설정

1. **브라우저 접속**: http://115.91.5.140:3104
2. **계정 생성**: 최초 접속 시 관리자 계정 생성
3. **워크스페이스 생성**: 
   - "Web Development"
   - "AI Shorts Automation"
   - "General Knowledge" 등
4. **AI 모델 연결**:
   - Settings → LLM Provider → Ollama
   - Base URL: `http://host.docker.internal:11434`
   - Model: `llama3.1:8b`
5. **벡터 DB 연결** (자동 설정됨):
   - Provider: PostgreSQL
   - Connection String: `postgresql://neuralgrid_ai:neuralgrid_pass_2024@postgres-memory:5432/memory_db`

### 문서 업로드

AnythingLLM에서 다음 문서들을 업로드하세요:

- `/home/azamans/webapp/SERVER_MASTER_DOCUMENT.md` - 서버 정보
- `/home/azamans/webapp/RAG_MULTI_AI_SYSTEM_PLAN.md` - 시스템 계획
- 프로젝트 소스코드 디렉토리 (드래그 앤 드롭)

## 💰 비용 최적화 전략

### AI 모델 선택 기준

| 작업 유형 | 추천 모델 | 비용 | 사용 시나리오 |
|-----------|-----------|------|---------------|
| 간단한 질문 | Ollama Llama 3.1 8B | $0 | "이게 뭐야?", "설명해줘" |
| 코드 생성/수정 | Gemini 2.0 Flash | $0 (무료 할당량) | 간단한 함수, 버그 수정 |
| 복잡한 아키텍처 | GPT-4o | $0.03/1K tokens | 시스템 설계, 리팩토링 |
| 긴 코드 분석 | Claude 3.5 Sonnet | $0.015/1K tokens | 전체 프로젝트 분석 |

### 월간 예상 비용

#### 초기 (1개월차)
- 로컬 AI (Ollama): 70% → $0
- Gemini (무료): 20% → $0
- GPT-4o: 8% → $15
- Claude: 2% → $5
- **총계: $20/월**

#### 성장 (6개월차)
- 로컬 AI: 85% → $0
- Gemini: 10% → $0
- GPT-4o: 4% → $8
- Claude: 1% → $2
- **총계: $10/월**

## 📈 성장 로드맵

### 1개월차
```
저장된 데이터: ~100개 대화
자체 해결률: 20%
월 비용: ~$20
```

### 3개월차
```
저장된 데이터: ~500개 대화, ~100개 코드
자체 해결률: 50%
월 비용: ~$15
```

### 6개월차
```
저장된 데이터: ~1500개 대화, ~300개 코드
자체 해결률: 70%
월 비용: ~$10
```

### 1년차
```
저장된 데이터: ~5000개 대화, ~1000개 코드
자체 해결률: 85%
월 비용: $5-8
→ "진짜 내 AI"처럼 작동 ✨
```

## 🛠️ 문제 해결

### 컨테이너가 시작되지 않을 때
```bash
# 로그 확인
docker compose logs -f

# 컨테이너 재시작
docker compose restart

# 완전 재구성
docker compose down
docker compose up -d --force-recreate
```

### 데이터베이스 연결 오류
```bash
# PostgreSQL 상태 확인
docker exec neuralgrid-memory-db pg_isready -U neuralgrid_ai

# 연결 테스트
docker exec neuralgrid-memory-db psql -U neuralgrid_ai -d memory_db -c "SELECT 1"

# 포트 확인
ss -tuln | grep 5435
```

### Ollama 모델이 작동하지 않을 때
```bash
# Ollama 서비스 상태
systemctl status ollama

# 모델 재다운로드
ollama pull llama3.1:8b

# 테스트 실행
ollama run llama3.1:8b "Hello"
```

### 디스크 공간 부족
```bash
# Docker 정리
docker system prune -a

# 사용하지 않는 볼륨 제거
docker volume prune

# 이미지 정리
docker image prune -a
```

## 📚 추가 문서

- [SERVER_MASTER_DOCUMENT.md](../SERVER_MASTER_DOCUMENT.md) - 전체 서버 구성 정보
- [RAG_MULTI_AI_SYSTEM_PLAN.md](../RAG_MULTI_AI_SYSTEM_PLAN.md) - 상세 시스템 설계
- [Dify 공식 문서](https://docs.dify.ai/)
- [AnythingLLM 문서](https://docs.useanything.com/)
- [Ollama 모델 리스트](https://ollama.com/library)

## 🔗 접속 링크 (빠른 액세스)

- **AnythingLLM**: http://115.91.5.140:3104
- **Dify.ai**: http://115.91.5.140:3103
- **PostgreSQL**: `postgresql://neuralgrid_ai:neuralgrid_pass_2024@localhost:5435/memory_db`

## 🎯 다음 단계

1. ✅ 시스템 설치 완료
2. ⬜ AnythingLLM 계정 생성 및 초기 설정
3. ⬜ 워크스페이스 생성 및 문서 업로드
4. ⬜ 첫 대화 시작하여 메모리 시스템 테스트
5. ⬜ 실제 프로젝트에 적용
6. ⬜ 비용 추적 및 최적화
7. ⬜ (선택) Dify.ai 워크플로우 구성

---

**프로젝트 시작일**: 2025-12-15  
**마지막 업데이트**: 2025-12-15  
**문의**: GitHub Issues 또는 neuralgrid.kr
