#!/usr/bin/env python3
import re

# Read the file
with open('neuralgrid-main-page.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Service updates
updates = {
    # Blog Shorts Generator
    "'Blog Shorts Generator': {": """'Blog Shorts Generator': {
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
            },""",
    
    # MediaFX Shorts
    "'MediaFX Shorts': {": """'MediaFX Shorts': {
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
            },""",
    
    # NeuronStar Music
    "'NeuronStar Music': {": """'NeuronStar Music': {
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
            },""",
    
    # Shorts Market
    "'Shorts Market': {": """'Shorts Market': {
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
            },"""
}

# Apply each update
for old_pattern, new_text in updates.items():
    # Find the service block and replace it
    pattern = re.escape(old_pattern)
    start_pos = content.find(old_pattern)
    if start_pos != -1:
        # Find the end of this service block (next service or closing brace)
        end_pos = content.find('},', start_pos) + 2
        old_block = content[start_pos:end_pos]
        content = content.replace(old_block, new_text)
        print(f"✅ Updated: {old_pattern[:30]}...")

# Write back
with open('neuralgrid-main-page.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("\n🎉 All services updated successfully!")
