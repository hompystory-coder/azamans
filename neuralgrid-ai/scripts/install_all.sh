#!/bin/bash

##############################################################################
# NeuralGrid RAG + Multi-AI System - 완전 자동 설치 스크립트
##############################################################################
# 작성일: 2025-12-15
# 용도: PostgreSQL + pgvector, Ollama, AnythingLLM, Dify.ai 자동 설치
# 예상 소요 시간: 30-40분
##############################################################################

set -e  # 에러 발생 시 즉시 종료

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 로그 함수
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# 배너 출력
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║   🧠 NeuralGrid RAG + Multi-AI System Installer          ║
║                                                           ║
║   Components:                                             ║
║   • PostgreSQL 15 + pgvector (벡터 검색)                  ║
║   • Ollama + Llama 3.1 8B (로컬 AI)                      ║
║   • AnythingLLM (프론트엔드)                              ║
║   • Dify.ai (워크플로우 엔진)                             ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# 현재 경로 확인
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

log_info "프로젝트 루트: $PROJECT_ROOT"
cd "$PROJECT_ROOT"

# API 키 확인
log_info "API 키 설정 확인..."
OPENAI_API_KEY="${OPENAI_API_KEY:-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}"
ANTHROPIC_API_KEY="${ANTHROPIC_API_KEY:-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}"
GOOGLE_API_KEY="${GOOGLE_API_KEY:-AIzaSyDsGSRcW58NIK8hRIh9fFa4v-qTYZT234A}"

##############################################################################
# 1. PostgreSQL + pgvector 설정
##############################################################################
log_info "PostgreSQL + pgvector 설정 중..."

if docker ps | grep -q neuralgrid-memory-db; then
    log_success "PostgreSQL이 이미 실행 중입니다."
else
    cd postgres
    docker compose up -d
    log_success "PostgreSQL + pgvector 시작 완료"
    sleep 5
    
    # 연결 테스트
    if docker exec neuralgrid-memory-db psql -U neuralgrid_ai -d memory_db -c "SELECT 1" > /dev/null 2>&1; then
        log_success "PostgreSQL 연결 테스트 성공"
    else
        log_error "PostgreSQL 연결 실패"
        exit 1
    fi
fi

##############################################################################
# 2. Ollama 및 모델 설치
##############################################################################
log_info "Ollama 설치 확인..."

if command -v ollama &> /dev/null; then
    log_success "Ollama가 이미 설치되어 있습니다. (버전: $(ollama --version))"
    
    # 설치된 모델 확인
    log_info "설치된 모델 목록:"
    ollama list
    
    # Llama 3.1 8B 확인
    if ollama list | grep -q "llama3.1:8b"; then
        log_success "Llama 3.1 8B 모델이 이미 설치되어 있습니다."
    else
        log_warning "Llama 3.1 8B 모델을 다운로드합니다... (약 5GB, 5-10분 소요)"
        ollama pull llama3.1:8b
        log_success "Llama 3.1 8B 다운로드 완료"
    fi
    
    # DeepSeek 확인
    if ollama list | grep -q "deepseek-r1:1.5b"; then
        log_success "DeepSeek R1 1.5B 모델이 이미 설치되어 있습니다."
    else
        log_info "DeepSeek R1 1.5B 모델을 다운로드합니다..."
        ollama pull deepseek-r1:1.5b
        log_success "DeepSeek R1 1.5B 다운로드 완료"
    fi
else
    log_info "Ollama를 설치합니다..."
    curl -fsSL https://ollama.com/install.sh | sh
    log_success "Ollama 설치 완료"
    
    log_info "Llama 3.1 8B 모델 다운로드 중..."
    ollama pull llama3.1:8b
    log_success "Llama 3.1 8B 다운로드 완료"
fi

##############################################################################
# 3. AnythingLLM 설정
##############################################################################
log_info "AnythingLLM 설정 중..."

if docker ps | grep -q neuralgrid-anythingllm; then
    log_success "AnythingLLM이 이미 실행 중입니다."
else
    cd "$PROJECT_ROOT/anythingllm"
    docker compose up -d
    log_success "AnythingLLM 시작 완료"
    log_info "접속 URL: http://115.91.5.140:3104"
fi

##############################################################################
# 4. Dify.ai 설정
##############################################################################
log_info "Dify.ai 설정 중..."

cd "$PROJECT_ROOT/dify"

if [ ! -d ".git" ]; then
    log_info "Dify 저장소를 클론합니다..."
    git clone https://github.com/langgenius/dify.git tmp_dify
    mv tmp_dify/* tmp_dify/.* . 2>/dev/null || true
    rm -rf tmp_dify
fi

cd docker

# .env 파일 설정
if [ ! -f ".env" ]; then
    log_info ".env 파일을 생성합니다..."
    cp .env.example .env
    
    # API 키 설정
    sed -i "s|^OPENAI_API_KEY=.*|OPENAI_API_KEY=${OPENAI_API_KEY}|" .env
    sed -i "s|^ANTHROPIC_API_KEY=.*|ANTHROPIC_API_KEY=${ANTHROPIC_API_KEY}|" .env
    sed -i "s|^GOOGLE_API_KEY=.*|GOOGLE_API_KEY=${GOOGLE_API_KEY}|" .env
    
    # 외부 접속 URL 설정
    sed -i "s|^CONSOLE_API_URL=.*|CONSOLE_API_URL=http://115.91.5.140:3103|" .env
    sed -i "s|^CONSOLE_WEB_URL=.*|CONSOLE_WEB_URL=http://115.91.5.140:3103|" .env
    
    log_success ".env 파일 생성 및 설정 완료"
else
    log_warning ".env 파일이 이미 존재합니다. 수동으로 확인해주세요."
fi

# Dify 시작 (선택 사항 - 시간이 오래 걸림)
read -p "Dify.ai를 지금 시작하시겠습니까? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log_info "Dify.ai를 시작합니다... (첫 실행은 10-15분 소요)"
    docker compose up -d
    log_success "Dify.ai 시작 완료"
    log_info "접속 URL: http://115.91.5.140:3103"
else
    log_info "Dify.ai 시작을 건너뜁니다. 나중에 'cd $PROJECT_ROOT/dify/docker && docker compose up -d' 명령으로 시작하세요."
fi

##############################################################################
# 5. Nginx 리버스 프록시 설정 (선택 사항)
##############################################################################
log_info "Nginx 리버스 프록시 설정..."

cat > "$PROJECT_ROOT/nginx.conf" << 'NGINX_EOF'
# NeuralGrid AI 시스템 Nginx 설정
# /etc/nginx/sites-available/ai-neuralgrid.conf

upstream anythingllm {
    server localhost:3104;
}

upstream dify_api {
    server localhost:3103;
}

# AnythingLLM
server {
    listen 80;
    server_name ai.neuralgrid.kr;

    client_max_body_size 100M;

    location / {
        proxy_pass http://anythingllm;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # WebSocket 지원
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}

# Dify.ai
server {
    listen 80;
    server_name dify.neuralgrid.kr;

    client_max_body_size 100M;

    location / {
        proxy_pass http://dify_api;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
NGINX_EOF

log_success "Nginx 설정 파일 생성: $PROJECT_ROOT/nginx.conf"
log_warning "Nginx 설정을 적용하려면 다음 명령어를 실행하세요:"
log_warning "  sudo ln -s $PROJECT_ROOT/nginx.conf /etc/nginx/sites-available/ai-neuralgrid.conf"
log_warning "  sudo ln -s /etc/nginx/sites-available/ai-neuralgrid.conf /etc/nginx/sites-enabled/"
log_warning "  sudo nginx -t && sudo systemctl reload nginx"

##############################################################################
# 6. 설치 완료 및 접속 정보
##############################################################################
echo ""
log_success "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
log_success "✨ NeuralGrid RAG + Multi-AI 시스템 설치 완료! ✨"
log_success "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo -e "${BLUE}📋 서비스 접속 정보:${NC}"
echo ""
echo -e "  ${GREEN}1. AnythingLLM (프론트엔드)${NC}"
echo -e "     URL: ${YELLOW}http://115.91.5.140:3104${NC}"
echo -e "     또는: ${YELLOW}http://ai.neuralgrid.kr${NC} (Nginx 설정 후)"
echo ""
echo -e "  ${GREEN}2. Dify.ai (워크플로우 엔진)${NC}"
if docker ps | grep -q dify; then
    echo -e "     URL: ${YELLOW}http://115.91.5.140:3103${NC}"
    echo -e "     또는: ${YELLOW}http://dify.neuralgrid.kr${NC} (Nginx 설정 후)"
else
    echo -e "     상태: ${RED}미시작${NC} (수동으로 시작 필요)"
    echo -e "     시작 명령: ${YELLOW}cd $PROJECT_ROOT/dify/docker && docker compose up -d${NC}"
fi
echo ""
echo -e "  ${GREEN}3. PostgreSQL + pgvector${NC}"
echo -e "     Host: localhost"
echo -e "     Port: 5435"
echo -e "     Database: memory_db"
echo -e "     User: neuralgrid_ai"
echo -e "     Password: neuralgrid_pass_2024"
echo ""
echo -e "  ${GREEN}4. Ollama (로컬 AI)${NC}"
echo -e "     설치된 모델:"
ollama list | tail -n +2 | awk '{print "       • " $1}'
echo ""

echo -e "${BLUE}🎯 다음 단계:${NC}"
echo ""
echo "  1️⃣  AnythingLLM 접속 후 초기 설정 (계정 생성)"
echo "  2️⃣  워크스페이스 생성 및 문서 업로드"
echo "  3️⃣  AI 모델 연결 (Ollama 또는 외부 API)"
echo "  4️⃣  대화 시작! 🚀"
echo ""

echo -e "${BLUE}📚 문서 위치:${NC}"
echo "  • 서버 마스터 문서: $PROJECT_ROOT/../SERVER_MASTER_DOCUMENT.md"
echo "  • RAG 시스템 계획: $PROJECT_ROOT/../RAG_MULTI_AI_SYSTEM_PLAN.md"
echo ""

log_success "설치가 완료되었습니다! 즐거운 AI 개발 되세요! 🎉"
