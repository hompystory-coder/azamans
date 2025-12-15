#!/bin/bash

# NeuralGrid 메인 페이지에 AI Assistant 추가

SERVER="115.91.5.140"
USER="azamans"
PASSWORD="7009011226119"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "=========================================="
echo "🤖 AI Assistant를 메인 페이지에 추가 중..."
echo "=========================================="

# SSH로 서버 접속해서 index.html 수정
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} << 'ENDSSH'
# 백업 생성
sudo cp /var/www/neuralgrid.kr/html/index.html /var/www/neuralgrid.kr/html/index.html.backup_ai_$(date +%Y%m%d_%H%M%S)

# AI Assistant 서비스 추가
sudo sed -i "/pricing: '무료 (Free)'/a\\
            },\\
            'AI Assistant': {\\
                icon: '🤖',\\
                titleKo: 'AI 어시스턴트',\\
                titleEn: 'AI Assistant',\\
                url: 'http://ai.neuralgrid.kr',\\
                description: '📚 개인 LLM 플랫폼 AnythingLLM으로 문서를 학습시키고 대화하세요. RAG(검색 증강 생성) 기술로 정확한 답변을 제공합니다.',\\
                features: [\\
                    '🧠 프라이빗 AI 워크스페이스 생성',\\
                    '📄 PDF, Word, TXT 등 다양한 문서 학습',\\
                    '💬 학습한 데이터 기반 정확한 답변',\\
                    '🔒 완전한 프라이버시 (온프레미스)',\\
                    '🤝 다중 LLM 지원 (OpenAI, Claude, 로컬 모델)',\\
                    '⚡ 벡터 DB 기반 고속 검색'\\
                ],\\
                pricing: '무료 (Free)'" /var/www/neuralgrid.kr/html/index.html

# footer 섹션에도 추가
sudo sed -i "s|<a href=\"https://auth.neuralgrid.kr\">인증 서비스</a>|<a href=\"https://auth.neuralgrid.kr\">인증 서비스</a>\\n                <a href=\"http://ai.neuralgrid.kr\">AI 어시스턴트</a>|" /var/www/neuralgrid.kr/html/index.html

# 서비스 혜택 목록에도 추가
sudo sed -i "s|<li>🖥️ System Monitor - 실시간 모니터링</li>|<li>🖥️ System Monitor - 실시간 모니터링</li>\\n                    <li>🤖 AI Assistant - 문서 기반 AI 채팅</li>|" /var/www/neuralgrid.kr/html/index.html

echo "✅ AI Assistant 서비스 추가 완료!"
ENDSSH

echo ""
echo "=========================================="
echo "🎉 메인 페이지 업데이트 완료!"
echo "=========================================="
echo ""
echo "확인: https://neuralgrid.kr"
