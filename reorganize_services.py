#!/usr/bin/env python3
import re

# Read the file
with open('neuralgrid-main-page.html', 'r', encoding='utf-8') as f:
    content = f.read()

# New services structure
new_services = """        // 🌟 Main Featured Services (메인 서비스)
        const mainServices = {
            'Blog Shorts Generator': {
                icon: '📰',
                titleKo: '블로그 기사 쇼츠생성기',
                titleEn: 'Blog Shorts Generator',
                url: 'https://bn-shop.neuralgrid.kr',
                description: '🚀 블로그 글을 단 4분만에 유튜브 쇼츠로! AI가 자동으로 기사 분석부터 영상 제작까지 원스톱 처리. Gemini 2.0, Pollinations.AI, Kling v2.1 Pro 엔진 탑재.',
                features: [
                    '✨ URL 입력 한 번으로 완전 자동화',
                    '🎯 Gemini 2.0 기반 스마트 콘텐츠 분석',
                    '🎨 Pollinations.AI 고품질 이미지 생성',
                    '🎬 Kling v2.1 Pro 프로급 영상 렌더링',
                    '💬 한국어 자막 자동 삽입 (가독성 최적화)',
                    '⚡ 초고속 처리: 평균 4~5분 이내 완성'
                ],
                pricing: '💰 영상당 단돈 $0.06 (세계 최저가)'
            },
            'MediaFX Shorts': {
                icon: '🎬',
                titleKo: '쇼츠 영상 자동화',
                titleEn: 'MediaFX Shorts',
                url: 'https://mfx.neuralgrid.kr',
                description: '🎥 3분이면 충분해! AI가 텍스트를 전문가급 쇼츠 영상으로 자동 변환. 인스타그램, 틱톡, 유튜브 최적화는 기본. 크리에이터의 시간을 10배 절약하세요.',
                features: [
                    '🚀 텍스트 입력 → 바로 영상 완성',
                    '🎨 100+ 프리미엄 템플릿 & 스타일',
                    '🎵 AI 음악 자동 싱크 (분위기 매칭)',
                    '📱 SNS 플랫폼별 자동 최적화 (9:16 비율)',
                    '⚙️ 배치 프로세싱 (한 번에 10개 제작)',
                    '📊 실시간 퍼포먼스 대시보드'
                ],
                pricing: '💎 유료 플랜 (합리적 가격, 무료 체험 가능)'
            },
            'NeuronStar Music': {
                icon: '🎵',
                titleKo: '스타뮤직',
                titleEn: 'NeuronStar Music',
                url: 'https://music.neuralgrid.kr',
                description: '🎶 완전 무료 AI 작곡가! 30초 안에 나만의 음악 완성. 텍스트만 입력하면 고품질 BGM 즉시 생성. 상업적 이용 100% 자유, 저작권 걱정 ZERO!',
                features: [
                    '🎼 전 장르 지원 (Pop, Rock, Jazz, EDM, Classical, K-pop)',
                    '✍️ 커스텀 가사로 완벽한 맞춤 제작',
                    '🎧 스튜디오급 고음질 (WAV/MP3 출력)',
                    '💼 상업적 이용 100% 허용 (라이선스 FREE)',
                    '♾️ 무제한 생성 (횟수 제한 없음)',
                    '⚡ 실시간 미리듣기 & 즉시 다운로드'
                ],
                pricing: '🎁 완전 무료 (영원히 FREE!)'
            },
            'Shorts Market': {
                icon: '🛒',
                titleKo: '쿠팡쇼츠',
                titleEn: 'Shorts Market',
                url: 'https://market.neuralgrid.kr',
                description: '💸 쇼츠로 돈 버는 가장 쉬운 방법! 유튜브 쇼츠와 쿠팡 파트너스 자동 연결. 클릭 한 번으로 딥링크 생성, 수익 대시보드로 실시간 확인. 크리에이터 수익 10배 증폭!',
                features: [
                    '📹 YouTube Shorts 자동 수집 (100% 성공률)',
                    '🛒 쿠팡 파트너스 딥링크 1초 생성',
                    '🔗 네이버 브랜드 커넥트 완벽 통합',
                    '💰 실시간 수익 대시보드 (일/월 통계)',
                    '📊 조회수·좋아요·댓글 빅데이터 분석',
                    '🎯 AI 자동 상품 매칭 (관련도 99%)'
                ],
                pricing: '🆓 완전 무료 (베타 오픈 중)'
            },
            'N8N Automation': {
                icon: '⚙️',
                titleKo: 'N8N 워크플로우 자동화',
                titleEn: 'N8N Automation',
                url: 'https://n8n.neuralgrid.kr',
                description: '🤖 노코드로 모든 업무를 자동화! 200+ 앱 연동, 드래그 앤 드롭만으로 복잡한 워크플로우 완성. 반복 작업은 이제 AI에게 맡기고 창의적인 일에 집중하세요!',
                features: [
                    '🔗 200+ 앱 연동 (Google, Slack, DB, API 등)',
                    '🖱️ 초직관적 드래그 앤 드롭 빌더',
                    '🔄 REST API 무제한 자동화',
                    '⏰ Cron 스케줄러 (정확한 시간 제어)',
                    '🔐 프라이빗 자체 호스팅 (보안 100%)',
                    '📊 실시간 로그 & 디버깅 대시보드'
                ],
                pricing: '🆓 무료 (Self-hosted, 영구 무료)'
            }
        };

        // 🔧 Additional Services (추가 서비스)
        const additionalServices = {
            'System Monitor': {
                icon: '🖥️',
                titleKo: '서버모니터링',
                titleEn: 'System Monitor',
                url: 'https://monitor.neuralgrid.kr',
                description: '실시간 서버 상태 및 PM2 프로세스를 한눈에 모니터링하는 프리미엄 대시보드. Glassmorphism 디자인과 Chart.js 그래프를 제공합니다.',
                features: [
                    '📊 실시간 CPU/메모리 사용률 (Chart.js)',
                    '💿 디스크 용량 모니터링',
                    '⚙️ PM2 프로세스 상태 추적',
                    '🔔 자동 알림 시스템 (Slack/Email)',
                    '🎨 Glassmorphism 프리미엄 디자인',
                    '🔄 30초 자동 갱신'
                ],
                pricing: '무료 (Free)'
            },
            'Auth Service': {
                icon: '🔐',
                titleKo: '통합 인증 서비스',
                titleEn: 'Auth Service',
                url: 'https://auth.neuralgrid.kr',
                description: '모든 NeuralGrid 서비스를 위한 중앙 집중식 JWT 기반 SSO 인증 시스템. 한 번의 로그인으로 모든 서비스에 즉시 접근하세요.',
                features: [
                    '🔐 JWT 기반 보안 인증',
                    '🎫 API 키 발급 및 관리',
                    '💳 크레딧 추적 시스템',
                    '👤 통합 사용자 관리',
                    '🔄 자동 토큰 갱신',
                    '🛡️ 권한 기반 접근 제어'
                ],
                pricing: '무료 (Free)'
            }
        };

        // Combine for backward compatibility
        const servicesData = { ...mainServices, ...additionalServices };"""

# Find and replace servicesData
pattern = r'// Enhanced Service Data with PR Content Only\s+const servicesData = \{[\s\S]*?\};'
match = re.search(pattern, content)

if match:
    old_block = match.group(0)
    content = content.replace(old_block, new_services)
    print("✅ Services reorganized successfully!")
else:
    print("❌ Could not find servicesData block")

# Write back
with open('neuralgrid-main-page.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("🎉 File updated!")
