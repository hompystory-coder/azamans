# 🧠 RAG + 멀티 AI 시스템 구축 계획서

**프로젝트명:** NeuralGrid Memory AI System  
**목표:** 대화 맥락을 영구 저장하고 점진적으로 학습하는 개인 AI 시스템 구축  
**서버:** AMD Ryzen 7 H 255, 32GB RAM, 1TB SSD  
**예상 소요 시간:** 4주

---

## 📋 목차

1. [프로젝트 개요](#프로젝트-개요)
2. [시스템 아키텍처](#시스템-아키텍처)
3. [기술 스택](#기술-스택)
4. [단계별 구현 계획](#단계별-구현-계획)
5. [비용 최적화 전략](#비용-최적화-전략)
6. [예상 성장 로드맵](#예상-성장-로드맵)

---

## 🎯 프로젝트 개요

### 현재 문제점
- ❌ AI와 대화할 때 맥락을 자꾸 잊어버림
- ❌ 긴 프로젝트 진행 시 이전 코드/대화 기억 못함
- ❌ 매번 처음부터 설명해야 함
- ❌ API 비용 과다 발생 (불필요한 컨텍스트 전송)

### 목표 솔루션
- ✅ 모든 대화를 영구 저장하는 장기 메모리 시스템
- ✅ 과거 대화/코드를 검색하여 관련 정보만 추출
- ✅ 간단한 작업은 로컬 AI로 처리 (무료)
- ✅ 복잡한 작업만 외부 API 사용 (유료)
- ✅ 시간이 지날수록 점점 똑똑해지는 AI

---

## 🏗️ 시스템 아키텍처

```
┌──────────────────────────────────────────────────────────────┐
│                    사용자 인터페이스                         │
│              (AnythingLLM 웹 UI - ChatGPT 스타일)            │
└──────────────────────────┬───────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│                  Dify.ai 워크플로우 엔진                     │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  1. 입력 분석 (작업 복잡도 판단)                      │  │
│  │  2. 메모리 검색 (PostgreSQL + pgvector)               │  │
│  │  3. AI 모델 선택 (비용 최적화 라우팅)                 │  │
│  │  4. 응답 생성 & 저장                                   │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────┬───────────────────────────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
┌───────▼───────┐  ┌───────▼───────┐  ┌──────▼──────┐
│  로컬 AI      │  │  무료/저렴 AI │  │  프리미엄 AI│
│               │  │               │  │             │
│ Ollama        │  │ Gemini 2.0    │  │ GPT-4o      │
│ Llama 3.1 70B │  │ (무료 할당량) │  │ Claude 3.5  │
│               │  │ DeepSeek      │  │             │
│ 간단한 작업   │  │ 중간 작업     │  │ 복잡한 작업 │
│ $0/월         │  │ ~$5/월        │  │ ~$10-20/월  │
└───────────────┘  └───────────────┘  └─────────────┘
        │                  │                  │
        └──────────────────┼──────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│              장기 메모리 데이터베이스                        │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  PostgreSQL + pgvector (벡터 검색)                    │  │
│  │  ├─ 대화 히스토리 (conversations)                     │  │
│  │  ├─ 생성된 코드 (code_snippets)                       │  │
│  │  ├─ 프로젝트 문맥 (project_context)                   │  │
│  │  └─ 사용자 선호도 (user_preferences)                  │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 기술 스택

### 핵심 컴포넌트

| 컴포넌트 | 기술 | 용도 | 비용 |
|----------|------|------|------|
| **프론트엔드** | AnythingLLM | 사용자 인터페이스 | 무료 (오픈소스) |
| **워크플로우** | Dify.ai | AI 오케스트레이션 | 무료 (오픈소스) |
| **로컬 AI** | Ollama + Llama 3.1 70B | 간단한 작업 처리 | 무료 |
| **무료 AI** | Gemini 2.0 Flash, DeepSeek | 중간 난이도 작업 | 무료/저렴 |
| **프리미엄 AI** | GPT-4o, Claude 3.5 | 복잡한 작업 | 유료 |
| **벡터 DB** | PostgreSQL + pgvector | 의미 기반 검색 | 무료 (자체 호스팅) |
| **임베딩** | text-embedding-3-small | 텍스트 벡터화 | $0.00002/1K tokens |

### 인프라

```yaml
서버: AMD Ryzen 7 H 255 (32GB RAM, 1TB SSD)
OS: Ubuntu Server 22.04 LTS
컨테이너: Docker + Docker Compose
리버스 프록시: Nginx
모니터링: Grafana + Prometheus
```

---

## 📅 단계별 구현 계획

### Week 1: 기초 인프라 구축 (12월 16일 - 22일)

#### Day 1-2: Docker 환경 설정
```bash
# Docker 및 Docker Compose 설치
sudo apt update
sudo apt install -y docker.io docker-compose
sudo usermod -aG docker $USER

# 프로젝트 디렉토리 생성
mkdir -p /opt/neuralgrid-ai
cd /opt/neuralgrid-ai
```

#### Day 3-4: Ollama 설치 및 모델 다운로드
```bash
# Ollama 설치
curl -fsSL https://ollama.com/install.sh | sh

# Llama 3.1 70B 모델 다운로드 (약 40GB)
ollama pull llama3.1:70b

# 테스트
ollama run llama3.1:70b "안녕하세요, 테스트입니다."
```

#### Day 5-7: PostgreSQL + pgvector 설정
```yaml
# docker-compose.yml
version: '3.8'

services:
  postgres:
    image: ankane/pgvector:latest
    environment:
      POSTGRES_USER: neuralgrid_ai
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD}
      POSTGRES_DB: memory_db
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5433:5432"
    restart: always

volumes:
  postgres_data:
```

```sql
-- 데이터베이스 스키마 생성
CREATE EXTENSION IF NOT EXISTS vector;

-- 대화 히스토리 테이블
CREATE TABLE conversations (
    id SERIAL PRIMARY KEY,
    session_id VARCHAR(255),
    user_message TEXT,
    ai_response TEXT,
    model_used VARCHAR(50),
    tokens_used INTEGER,
    cost_usd DECIMAL(10, 6),
    embedding vector(1536),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 코드 스니펫 테이블
CREATE TABLE code_snippets (
    id SERIAL PRIMARY KEY,
    project_name VARCHAR(255),
    file_path TEXT,
    code_content TEXT,
    language VARCHAR(50),
    description TEXT,
    embedding vector(1536),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 벡터 검색 인덱스
CREATE INDEX ON conversations USING ivfflat (embedding vector_cosine_ops);
CREATE INDEX ON code_snippets USING ivfflat (embedding vector_cosine_ops);
```

### Week 2: Dify.ai 및 AnythingLLM 설치 (12월 23일 - 29일)

#### Day 1-3: Dify.ai 설치
```bash
cd /opt/neuralgrid-ai

# Dify 클론
git clone https://github.com/langgenius/dify.git
cd dify/docker

# 환경 변수 설정
cp .env.example .env

# .env 파일 편집
nano .env
```

**.env 설정 예시:**
```env
# PostgreSQL (기존 DB 연결)
DB_USERNAME=neuralgrid_ai
DB_PASSWORD=${POSTGRES_PASSWORD}
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=memory_db

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# API Keys
OPENAI_API_KEY=${OPENAI_API_KEY}
ANTHROPIC_API_KEY=${ANTHROPIC_API_KEY}
GOOGLE_API_KEY=${GOOGLE_API_KEY}

# Ollama (로컬)
OLLAMA_API_BASE=http://host.docker.internal:11434
```

```bash
# Dify 실행
docker-compose up -d

# 접속 확인
# http://115.91.5.140:3103
```

#### Day 4-6: AnythingLLM 설치
```bash
cd /opt/neuralgrid-ai

# AnythingLLM 실행
docker run -d \
  --name anythingllm \
  -p 3104:3001 \
  -v anythingllm_data:/app/server/storage \
  -e STORAGE_DIR="/app/server/storage" \
  mintplexlabs/anythingllm:latest

# 접속 확인
# http://115.91.5.140:3104
```

#### Day 7: Nginx 리버스 프록시 설정
```nginx
# /etc/nginx/sites-available/ai.neuralgrid.kr

server {
    listen 80;
    server_name ai.neuralgrid.kr;

    location / {
        proxy_pass http://localhost:3104;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

server {
    listen 80;
    server_name dify.neuralgrid.kr;

    location / {
        proxy_pass http://localhost:3103;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### Week 3: 워크플로우 구성 및 통합 (12월 30일 - 1월 5일)

#### Dify 워크플로우: "Smart AI Router"

```
[사용자 입력]
    ↓
[1. 복잡도 분석 노드]
├─ 프롬프트: "다음 요청의 복잡도를 1-10으로 평가하세요."
├─ 모델: Gemini 2.0 Flash (무료)
└─ 출력: complexity_score
    ↓
[2. 메모리 검색 노드]
├─ Vector DB 쿼리
├─ 유사도 Top 5 결과
└─ 출력: relevant_context
    ↓
[3. 조건부 라우팅]
├─ IF complexity_score <= 3:
│   → Ollama Llama 3.1 (로컬, 무료)
├─ ELIF complexity_score <= 6:
│   → Gemini 2.0 Flash (무료)
├─ ELIF complexity_score <= 8:
│   → Claude 3.5 Sonnet (유료)
└─ ELSE:
    → GPT-4o (프리미엄)
    ↓
[4. AI 응답 생성]
├─ 컨텍스트: relevant_context
├─ 사용자 입력
└─ 선택된 모델로 처리
    ↓
[5. 결과 저장 노드]
├─ 대화 DB 저장
├─ 임베딩 생성
└─ 비용 기록
    ↓
[사용자에게 반환]
```

#### AnythingLLM 워크스페이스 설정

**워크스페이스 1: 웹 개발 프로젝트**
```
├─ 문서:
│  ├─ neuralgrid.kr 소스코드
│  ├─ mfx.neuralgrid.kr 소스코드
│  ├─ bn-shop 소스코드
│  └─ API 문서들
├─ 모델: Dify API 연결
└─ 설정:
   ├─ 자동 저장: 활성화
   ├─ 컨텍스트 윈도우: 최대
   └─ 임베딩: text-embedding-3-small
```

**워크스페이스 2: AI 쇼츠 자동화**
```
├─ 문서:
│  ├─ MediaFX 워크플로우
│  ├─ N8N 자동화 스크립트
│  └─ FFmpeg 명령어 모음
└─ 모델: Ollama (간단한 작업)
```

### Week 4: 최적화 및 모니터링 (1월 6일 - 12일)

#### 비용 모니터링 대시보드

```python
# /opt/neuralgrid-ai/cost_monitor.py

import psycopg2
from datetime import datetime, timedelta

def get_daily_costs():
    conn = psycopg2.connect(
        dbname="memory_db",
        user="neuralgrid_ai",
        password=os.getenv("POSTGRES_PASSWORD"),
        host="localhost",
        port="5433"
    )
    
    cursor = conn.cursor()
    
    # 최근 7일 비용 통계
    query = """
    SELECT 
        DATE(created_at) as date,
        model_used,
        SUM(tokens_used) as total_tokens,
        SUM(cost_usd) as total_cost
    FROM conversations
    WHERE created_at >= NOW() - INTERVAL '7 days'
    GROUP BY DATE(created_at), model_used
    ORDER BY date DESC, total_cost DESC;
    """
    
    cursor.execute(query)
    results = cursor.fetchall()
    
    for row in results:
        print(f"{row[0]} | {row[1]} | {row[2]:,} tokens | ${row[3]:.4f}")
    
    conn.close()

if __name__ == "__main__":
    get_daily_costs()
```

#### Grafana 대시보드 설정

```yaml
# docker-compose.monitoring.yml

version: '3.8'

services:
  prometheus:
    image: prom/prometheus:latest
    volumes:
      - ./prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    ports:
      - "9090:9090"
    restart: always

  grafana:
    image: grafana/grafana:latest
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=${GRAFANA_PASSWORD}
    volumes:
      - grafana_data:/var/lib/grafana
    ports:
      - "3105:3000"
    restart: always

volumes:
  prometheus_data:
  grafana_data:
```

---

## 💰 비용 최적화 전략

### 작업 분류 알고리즘

```python
def classify_task(user_input: str) -> dict:
    """
    사용자 입력을 분석하여 최적의 AI 모델을 선택
    """
    
    # 간단한 작업 (로컬 Ollama - $0)
    simple_keywords = [
        "간단한", "설명해", "뭐야", "이게", "차이",
        "예시", "샘플", "테스트", "확인"
    ]
    
    # 코드 관련 (Gemini 무료 할당량)
    code_keywords = [
        "코드", "함수", "클래스", "버그", "에러",
        "디버그", "수정", "추가", "변경"
    ]
    
    # 복잡한 작업 (GPT-4o 유료)
    complex_keywords = [
        "아키텍처", "설계", "최적화", "리팩토링",
        "전체", "시스템", "통합", "마이그레이션"
    ]
    
    input_lower = user_input.lower()
    
    if any(kw in input_lower for kw in simple_keywords):
        return {
            "model": "ollama/llama3.1:70b",
            "cost_per_1k": 0,
            "reason": "간단한 질문"
        }
    
    elif any(kw in input_lower for kw in code_keywords):
        return {
            "model": "gemini-2.0-flash-exp",
            "cost_per_1k": 0,
            "reason": "코드 관련 작업 (무료 할당량)"
        }
    
    elif any(kw in input_lower for kw in complex_keywords):
        return {
            "model": "gpt-4o",
            "cost_per_1k": 0.03,
            "reason": "복잡한 아키텍처 작업"
        }
    
    else:
        # 기본값: Gemini
        return {
            "model": "gemini-2.0-flash-exp",
            "cost_per_1k": 0,
            "reason": "일반 작업"
        }
```

### 예상 월간 비용 (첫 달)

| 항목 | 예상 사용량 | 비용 |
|------|-------------|------|
| Ollama (로컬) | 70% 작업 | $0 |
| Gemini 2.0 Flash | 20% 작업 | $0 (무료 할당량) |
| GPT-4o | 8% 작업 | $15 |
| Claude 3.5 | 2% 작업 | $5 |
| 임베딩 (text-embedding-3-small) | 10,000 문서 | $0.20 |
| **총계** | | **$20.20** |

### 6개월 후 예상 비용

| 항목 | 예상 사용량 | 비용 |
|------|-------------|------|
| Ollama (로컬) | 85% 작업 | $0 |
| Gemini 2.0 Flash | 10% 작업 | $0 |
| GPT-4o | 4% 작업 | $8 |
| Claude 3.5 | 1% 작업 | $2 |
| **총계** | | **$10** |

---

## 📈 예상 성장 로드맵

### 1개월차
```
저장된 데이터:
├─ 대화: ~100개
├─ 코드 스니펫: ~20개
└─ 프로젝트 문서: ~10개

자체 해결률: 20%
API 의존도: 80%
월 비용: ~$20
```

### 3개월차
```
저장된 데이터:
├─ 대화: ~500개
├─ 코드 스니펫: ~100개
├─ 프로젝트 문서: ~50개
└─ 학습된 패턴: 많음

자체 해결률: 50%
API 의존도: 50%
월 비용: ~$15
```

### 6개월차
```
저장된 데이터:
├─ 대화: ~1500개
├─ 코드 스니펫: ~300개
├─ 프로젝트 문서: ~150개
└─ 완전히 학습된 워크플로우

자체 해결률: 70%
API 의존도: 30%
월 비용: ~$10
```

### 1년차
```
저장된 데이터:
├─ 대화: ~5000개
├─ 코드 스니펫: ~1000개
├─ 프로젝트 문서: ~500개
└─ "진짜 내 AI"처럼 작동

자체 해결률: 85%
API 의존도: 15% (복잡한 작업만)
월 비용: ~$5-8
```

---

## 🎯 핵심 성공 지표 (KPI)

### 기술적 지표
- ✅ 평균 응답 시간: < 3초 (로컬 AI)
- ✅ 메모리 검색 정확도: > 90%
- ✅ 시스템 가동률: > 99.5%

### 비용 지표
- ✅ 월간 API 비용: < $20 (초기), < $10 (6개월 후)
- ✅ 로컬 AI 사용률: > 70%

### 사용자 경험 지표
- ✅ 맥락 유지율: 100% (영구 저장)
- ✅ "까먹는 문제": 0건
- ✅ 사용자 만족도: "진짜 내 AI 같음"

---

## 🚀 즉시 실행 가능한 첫 단계

```bash
# 1. 프로젝트 디렉토리 생성
sudo mkdir -p /opt/neuralgrid-ai
sudo chown azamans:azamans /opt/neuralgrid-ai
cd /opt/neuralgrid-ai

# 2. Ollama 설치
curl -fsSL https://ollama.com/install.sh | sh

# 3. Llama 3.1 다운로드 (백그라운드)
nohup ollama pull llama3.1:70b > ollama_pull.log 2>&1 &

# 4. PostgreSQL + pgvector 시작
cat > docker-compose.db.yml << 'EOF'
version: '3.8'
services:
  postgres:
    image: ankane/pgvector:latest
    environment:
      POSTGRES_USER: neuralgrid_ai
      POSTGRES_PASSWORD: neuralgrid_pass_2024
      POSTGRES_DB: memory_db
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5433:5432"
    restart: always
volumes:
  postgres_data:
EOF

docker-compose -f docker-compose.db.yml up -d

# 5. 상태 확인
echo "✅ Ollama 다운로드 진행 중: tail -f ollama_pull.log"
echo "✅ PostgreSQL 실행 중: docker ps | grep postgres"
```

---

**문서 작성일:** 2025-12-15  
**시작 예정일:** 2025-12-16  
**완료 목표일:** 2026-01-12
