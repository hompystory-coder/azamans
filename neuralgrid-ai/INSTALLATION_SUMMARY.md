# 🎉 NeuralGrid RAG + Multi-AI System 설치 완료 보고서

**설치 일시**: 2025-12-15 03:56 UTC  
**서버**: 115.91.5.140 (AMD Ryzen 7 H 255, 32GB RAM)  
**상태**: ✅ 정상 작동 중

---

## ✅ 설치 완료된 컴포넌트

### 1. PostgreSQL + pgvector (벡터 데이터베이스)

| 항목 | 값 |
|------|-----|
| **컨테이너 이름** | neuralgrid-memory-db |
| **이미지** | ankane/pgvector:latest |
| **버전** | PostgreSQL 15.4 + pgvector 0.5.1 |
| **포트** | 5435 → 5432 |
| **데이터베이스** | memory_db |
| **사용자** | neuralgrid_ai |
| **비밀번호** | neuralgrid_pass_2024 |
| **상태** | ✅ Up 4 minutes |

**생성된 테이블:**
- `conversations` - 대화 히스토리 저장
- `code_snippets` - 코드 스니펫 저장
- `project_contexts` - 프로젝트 컨텍스트 저장
- `user_preferences` - 사용자 선호도 저장
- `cost_tracking` - 비용 추적

**벡터 검색 인덱스:**
- HNSW 인덱스 (고성능 벡터 검색)
- 코사인 유사도 기반 검색

**연결 문자열:**
```
postgresql://neuralgrid_ai:neuralgrid_pass_2024@localhost:5435/memory_db
```

### 2. Ollama (로컬 AI 엔진)

| 항목 | 값 |
|------|-----|
| **버전** | 0.13.1 |
| **설치 경로** | /usr/local/bin/ollama |
| **API 포트** | 11434 |
| **상태** | ✅ 실행 중 |

**설치된 모델:**
- `llama3.1:8b` (4.9 GB) - 범용 AI 모델
- `deepseek-r1:1.5b` (1.1 GB) - 경량 추론 모델

**테스트 명령어:**
```bash
ollama run llama3.1:8b "안녕하세요"
```

### 3. AnythingLLM (프론트엔드 UI)

| 항목 | 값 |
|------|-----|
| **컨테이너 이름** | neuralgrid-anythingllm |
| **이미지** | mintplexlabs/anythingllm:latest |
| **포트** | 3104 → 3001 |
| **상태** | ✅ Up 3 minutes (healthy) |
| **데이터 볼륨** | anythingllm_data |

**접속 URL:**
- 직접 접속: http://115.91.5.140:3104
- 도메인 (설정 후): http://ai.neuralgrid.kr

**초기 설정 단계:**
1. 브라우저에서 접속
2. 관리자 계정 생성
3. 워크스페이스 생성
4. AI 모델 연결 (Ollama)
5. 문서 업로드

### 4. Dify.ai (워크플로우 엔진) - 준비 완료

| 항목 | 값 |
|------|-----|
| **상태** | ⏸️ 설치 준비 완료 (시작 대기 중) |
| **포트** | 3103 (시작 후) |
| **설치 경로** | /home/azamans/webapp/neuralgrid-ai/dify |

**시작 방법:**
```bash
cd /home/azamans/webapp/neuralgrid-ai/dify/docker
docker compose up -d
```

**예상 소요 시간:** 첫 실행 시 10-15분

---

## 📊 시스템 리소스 사용 현황

### Docker 컨테이너
```
총 실행 중: 19개
- NeuralGrid AI: 2개 (PostgreSQL, AnythingLLM)
- 기존 서비스: 17개 (n8n, neuralgrid-platform 등)
```

### 디스크 사용량
```
Ollama 모델: 6.0 GB
Docker 볼륨: ~2 GB
총 사용량: ~8 GB
```

---

## 🔑 중요 정보 요약

### 데이터베이스 접속 정보

**PostgreSQL (Memory DB)**
```bash
Host: localhost
Port: 5435
Database: memory_db
Username: neuralgrid_ai
Password: neuralgrid_pass_2024

# CLI 접속
docker exec -it neuralgrid-memory-db psql -U neuralgrid_ai -d memory_db

# 외부 접속 (DBeaver, pgAdmin 등)
postgresql://neuralgrid_ai:neuralgrid_pass_2024@115.91.5.140:5435/memory_db
```

### 서비스 접속 URL

| 서비스 | 내부 URL | 외부 URL (Nginx 후) | 상태 |
|--------|----------|---------------------|------|
| AnythingLLM | http://115.91.5.140:3104 | http://ai.neuralgrid.kr | ✅ |
| Dify.ai | http://115.91.5.140:3103 | http://dify.neuralgrid.kr | ⏸️ |
| Ollama API | http://localhost:11434 | - | ✅ |
| PostgreSQL | localhost:5435 | - | ✅ |

### API 키 (환경 변수 설정)

Dify.ai 사용 시 `.env` 파일에 설정된 API 키:
- `OPENAI_API_KEY` - GPT-4o 사용
- `ANTHROPIC_API_KEY` - Claude 3.5 사용
- `GOOGLE_API_KEY` - Gemini 2.0 사용

---

## 🚀 다음 단계 (Quick Start)

### 1단계: AnythingLLM 접속 및 설정 (5분)

```bash
# 브라우저에서 접속
http://115.91.5.140:3104

# 1. 관리자 계정 생성
# 2. 워크스페이스 생성: "Web Development"
# 3. AI 모델 연결:
#    - LLM Provider: Ollama
#    - Base URL: http://host.docker.internal:11434
#    - Model: llama3.1:8b
```

### 2단계: 문서 업로드 (10분)

```bash
# AnythingLLM 웹 UI에서:
# 1. 워크스페이스 선택
# 2. "Documents" 탭 클릭
# 3. 다음 파일 업로드:
```

**업로드할 문서:**
- `/home/azamans/webapp/SERVER_MASTER_DOCUMENT.md` - 서버 정보
- `/home/azamans/webapp/RAG_MULTI_AI_SYSTEM_PLAN.md` - 시스템 계획
- 프로젝트 소스코드 (선택)

### 3단계: 첫 대화 시작 (즉시)

```
사용자: 이 서버에 설치된 서비스들을 설명해줘

AI: [문서를 검색하여 정확한 정보 제공]
    - neuralgrid.kr (메인)
    - mfx.neuralgrid.kr (MediaFX Shorts)
    - bn-shop.neuralgrid.kr (BN Shop)
    ...
```

### 4단계: Dify.ai 시작 (선택, 15분)

```bash
cd /home/azamans/webapp/neuralgrid-ai/dify/docker
docker compose up -d

# 접속
http://115.91.5.140:3103
```

---

## 📈 예상 성장 지표

### 초기 (현재)
- 저장된 데이터: 0개
- 자체 해결률: 0%
- 외부 API 의존도: 100%

### 1개월 후
- 저장된 대화: ~100개
- 저장된 코드: ~20개
- 자체 해결률: 20%
- 월 비용: ~$20

### 3개월 후
- 저장된 대화: ~500개
- 저장된 코드: ~100개
- 자체 해결률: 50%
- 월 비용: ~$15

### 6개월 후
- 저장된 대화: ~1500개
- 저장된 코드: ~300개
- 자체 해결률: 70%
- 월 비용: ~$10

### 1년 후 🎯
- 저장된 대화: ~5000개
- 저장된 코드: ~1000개
- 자체 해결률: 85%
- 월 비용: $5-8
- **"진짜 내 AI"처럼 작동!** ✨

---

## 🛠️ 유지보수 명령어

### 일일 점검
```bash
# 컨테이너 상태 확인
docker ps

# 디스크 사용량 확인
docker system df

# 로그 확인
docker compose logs -f --tail=50
```

### 주간 작업
```bash
# 데이터베이스 백업
docker exec neuralgrid-memory-db pg_dump -U neuralgrid_ai memory_db > backup_$(date +%Y%m%d).sql

# Docker 이미지 업데이트
docker compose pull
docker compose up -d

# 사용하지 않는 이미지 정리
docker image prune -a
```

### 월간 작업
```bash
# 비용 분석
docker exec -it neuralgrid-memory-db psql -U neuralgrid_ai -d memory_db << 'SQL'
SELECT 
    DATE(created_at) as date,
    model_used,
    SUM(cost_usd) as daily_cost
FROM conversations
WHERE created_at >= NOW() - INTERVAL '30 days'
GROUP BY DATE(created_at), model_used
ORDER BY date DESC;
SQL

# 전체 시스템 백업
tar -czf neuralgrid_ai_backup_$(date +%Y%m%d).tar.gz /home/azamans/webapp/neuralgrid-ai
```

---

## 🔗 빠른 링크

| 리소스 | 링크 |
|--------|------|
| **AnythingLLM 접속** | http://115.91.5.140:3104 |
| **Dify.ai 접속** | http://115.91.5.140:3103 |
| **서버 문서** | [SERVER_MASTER_DOCUMENT.md](../SERVER_MASTER_DOCUMENT.md) |
| **시스템 계획** | [RAG_MULTI_AI_SYSTEM_PLAN.md](../RAG_MULTI_AI_SYSTEM_PLAN.md) |
| **빠른 시작** | [README.md](README.md) |
| **설치 스크립트** | [scripts/install_all.sh](scripts/install_all.sh) |

---

## 📞 문제 발생 시

### 로그 확인
```bash
# PostgreSQL
docker logs neuralgrid-memory-db

# AnythingLLM
docker logs neuralgrid-anythingllm

# Ollama
journalctl -u ollama -f
```

### 재시작
```bash
# 개별 컨테이너
docker restart neuralgrid-memory-db
docker restart neuralgrid-anythingllm

# 전체 재시작
cd /home/azamans/webapp/neuralgrid-ai/postgres && docker compose restart
cd /home/azamans/webapp/neuralgrid-ai/anythingllm && docker compose restart
```

### 완전 재구성
```bash
# 주의: 데이터 손실 가능!
docker compose down
docker compose up -d --force-recreate
```

---

## ✅ 체크리스트

- [x] PostgreSQL + pgvector 설치 및 실행
- [x] Ollama + 모델 다운로드 완료
- [x] AnythingLLM 설치 및 실행
- [x] 데이터베이스 스키마 생성
- [x] 벡터 검색 인덱스 설정
- [x] Dify.ai 설치 준비
- [x] 문서 작성 완료
- [ ] AnythingLLM 초기 설정
- [ ] 워크스페이스 생성
- [ ] 문서 업로드
- [ ] 첫 대화 테스트
- [ ] Nginx 리버스 프록시 설정
- [ ] 외부 도메인 연결
- [ ] SSL 인증서 설정

---

**설치 완료! 🎉**

이제 http://115.91.5.140:3104 에 접속하여 AI와 대화를 시작하세요!

모든 대화는 자동으로 PostgreSQL에 저장되며, 시간이 지날수록 점점 똑똑해집니다.

---

**문서 작성**: 2025-12-15  
**시스템 버전**: v1.0.0  
**다음 업데이트**: 사용 1주일 후 성과 리포트
