// AI Assistant 서비스를 additionalServices에 추가하는 JavaScript 코드
// 이 코드를 neuralgrid index.html의 additionalServices 객체에 추가하세요

const aiAssistantService = {
    'AI Assistant': {
        icon: '🤖',
        titleKo: 'AI 어시스턴트',
        titleEn: 'AI Assistant',
        url: 'http://ai.neuralgrid.kr',  // SSL 설정 완료 후 https://로 변경
        description: '📚 개인 LLM 플랫폼 AnythingLLM으로 문서를 학습시키고 대화하세요. RAG(검색 증강 생성) 기술로 정확한 답변을 제공합니다.',
        features: [
            '🧠 프라이빗 AI 워크스페이스 생성',
            '📄 PDF, Word, TXT 등 다양한 문서 학습',
            '💬 학습한 데이터 기반 정확한 답변',
            '🔒 완전한 프라이버시 (온프레미스)',
            '🤝 다중 LLM 지원 (OpenAI, Claude, 로컬 모델)',
            '⚡ 벡터 DB 기반 고속 검색'
        ],
        pricing: '무료 (Free)'
    }
};

// 추가 위치: additionalServices 객체 내부, 'Auth Service' 다음에 추가
