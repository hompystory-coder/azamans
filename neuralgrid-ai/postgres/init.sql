-- NeuralGrid Memory AI Database Schema
-- 벡터 검색을 위한 pgvector 확장 활성화

CREATE EXTENSION IF NOT EXISTS vector;

-- 대화 히스토리 테이블
CREATE TABLE IF NOT EXISTS conversations (
    id SERIAL PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    user_id VARCHAR(255),
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    model_used VARCHAR(100) NOT NULL,
    tokens_used INTEGER DEFAULT 0,
    cost_usd DECIMAL(10, 6) DEFAULT 0,
    embedding vector(1536),
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 코드 스니펫 테이블
CREATE TABLE IF NOT EXISTS code_snippets (
    id SERIAL PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL,
    file_path TEXT,
    code_content TEXT NOT NULL,
    language VARCHAR(50),
    description TEXT,
    tags TEXT[],
    embedding vector(1536),
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 프로젝트 컨텍스트 테이블
CREATE TABLE IF NOT EXISTS project_contexts (
    id SERIAL PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    tech_stack TEXT[],
    file_structure JSONB,
    key_concepts TEXT[],
    embedding vector(1536),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 사용자 선호도 테이블
CREATE TABLE IF NOT EXISTS user_preferences (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    preference_key VARCHAR(100) NOT NULL,
    preference_value TEXT,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, preference_key)
);

-- 비용 추적 테이블
CREATE TABLE IF NOT EXISTS cost_tracking (
    id SERIAL PRIMARY KEY,
    date DATE NOT NULL,
    model_name VARCHAR(100) NOT NULL,
    total_tokens INTEGER DEFAULT 0,
    total_cost_usd DECIMAL(10, 6) DEFAULT 0,
    request_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(date, model_name)
);

-- 벡터 검색 인덱스 생성 (HNSW - 더 빠른 검색)
CREATE INDEX IF NOT EXISTS conversations_embedding_idx 
ON conversations USING hnsw (embedding vector_cosine_ops);

CREATE INDEX IF NOT EXISTS code_snippets_embedding_idx 
ON code_snippets USING hnsw (embedding vector_cosine_ops);

CREATE INDEX IF NOT EXISTS project_contexts_embedding_idx 
ON project_contexts USING hnsw (embedding vector_cosine_ops);

-- 일반 인덱스
CREATE INDEX IF NOT EXISTS conversations_session_idx ON conversations(session_id);
CREATE INDEX IF NOT EXISTS conversations_created_at_idx ON conversations(created_at DESC);
CREATE INDEX IF NOT EXISTS code_snippets_project_idx ON code_snippets(project_name);
CREATE INDEX IF NOT EXISTS cost_tracking_date_idx ON cost_tracking(date DESC);

-- 함수: 유사한 대화 찾기
CREATE OR REPLACE FUNCTION find_similar_conversations(
    query_embedding vector(1536),
    similarity_threshold float DEFAULT 0.7,
    max_results int DEFAULT 5
)
RETURNS TABLE (
    id INTEGER,
    user_message TEXT,
    ai_response TEXT,
    model_used VARCHAR,
    similarity FLOAT,
    created_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        c.id,
        c.user_message,
        c.ai_response,
        c.model_used,
        1 - (c.embedding <=> query_embedding) AS similarity,
        c.created_at
    FROM conversations c
    WHERE 1 - (c.embedding <=> query_embedding) > similarity_threshold
    ORDER BY c.embedding <=> query_embedding
    LIMIT max_results;
END;
$$ LANGUAGE plpgsql;

-- 함수: 유사한 코드 찾기
CREATE OR REPLACE FUNCTION find_similar_code(
    query_embedding vector(1536),
    project_filter VARCHAR DEFAULT NULL,
    similarity_threshold float DEFAULT 0.7,
    max_results int DEFAULT 5
)
RETURNS TABLE (
    id INTEGER,
    project_name VARCHAR,
    file_path TEXT,
    code_content TEXT,
    description TEXT,
    similarity FLOAT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        cs.id,
        cs.project_name,
        cs.file_path,
        cs.code_content,
        cs.description,
        1 - (cs.embedding <=> query_embedding) AS similarity
    FROM code_snippets cs
    WHERE 
        (project_filter IS NULL OR cs.project_name = project_filter)
        AND 1 - (cs.embedding <=> query_embedding) > similarity_threshold
    ORDER BY cs.embedding <=> query_embedding
    LIMIT max_results;
END;
$$ LANGUAGE plpgsql;

-- 함수: 일일 비용 집계
CREATE OR REPLACE FUNCTION aggregate_daily_costs()
RETURNS void AS $$
BEGIN
    INSERT INTO cost_tracking (date, model_name, total_tokens, total_cost_usd, request_count)
    SELECT 
        DATE(created_at),
        model_used,
        SUM(tokens_used),
        SUM(cost_usd),
        COUNT(*)
    FROM conversations
    WHERE DATE(created_at) = CURRENT_DATE
    GROUP BY DATE(created_at), model_used
    ON CONFLICT (date, model_name) 
    DO UPDATE SET
        total_tokens = EXCLUDED.total_tokens,
        total_cost_usd = EXCLUDED.total_cost_usd,
        request_count = EXCLUDED.request_count;
END;
$$ LANGUAGE plpgsql;

-- 샘플 데이터 삽입 (테스트용)
INSERT INTO project_contexts (project_name, description, tech_stack, key_concepts)
VALUES 
    ('neuralgrid-platform', 'AI 통합 플랫폼', 
     ARRAY['Node.js', 'React', 'PostgreSQL', 'Docker'], 
     ARRAY['AI 자동화', '쇼츠 생성', 'RAG 시스템']),
    ('mediafx-shorts', 'AI 쇼츠 자동 생성 시스템', 
     ARRAY['Python', 'FFmpeg', 'Replicate API'], 
     ARRAY['영상 생성', '자막 렌더링', 'AI 이미지'])
ON CONFLICT (project_name) DO NOTHING;

-- 권한 설정
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO neuralgrid_ai;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO neuralgrid_ai;
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO neuralgrid_ai;

-- 완료 메시지
DO $$
BEGIN
    RAISE NOTICE '✅ NeuralGrid Memory Database 초기화 완료!';
    RAISE NOTICE '📊 테이블: conversations, code_snippets, project_contexts, user_preferences, cost_tracking';
    RAISE NOTICE '🔍 벡터 검색 인덱스: HNSW (고성능)';
    RAISE NOTICE '🎯 함수: find_similar_conversations(), find_similar_code(), aggregate_daily_costs()';
END $$;
