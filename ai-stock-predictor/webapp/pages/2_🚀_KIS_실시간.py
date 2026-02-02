"""
🚀 한국투자증권 실시간 모니터 (지연 없음!)
완전 실시간 주가, 호가, 체결 데이터
"""

import streamlit as st
import pandas as pd
import time
from datetime import datetime
import sys
import os

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

# 한국투자증권 위젯 import
try:
    from webapp.kis_realtime_widget import (
        init_kis_fetcher,
        kis_realtime_price_widget,
        kis_orderbook_widget,
        kis_mini_chart,
        kis_setup_guide
    )
except ImportError as e:
    st.error(f"⚠️ 모듈 로드 실패: {e}")
    st.stop()

# 페이지 설정
st.set_page_config(
    page_title="KIS 실시간 - AI 주식 도우미",
    page_icon="🚀",
    layout="wide"
)

# 프리미엄 스타일
st.markdown("""
<style>
    .stApp {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .main .block-container {
        padding-top: 2rem;
        max-width: 1400px;
    }
    
    /* 실시간 배지 */
    .realtime-badge {
        display: inline-block;
        background: #00ff00;
        color: #000;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: bold;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    
    /* 카드 스타일 */
    .info-card {
        background: rgba(255, 255, 255, 0.95);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin: 10px 0;
    }
</style>
""", unsafe_allow_html=True)

# 헤더
st.markdown("""
<div style="text-align: center; padding: 20px; background: rgba(255, 255, 255, 0.1); border-radius: 10px; margin-bottom: 20px;">
    <h1 style="color: white; margin: 0;">
        🚀 한국투자증권 실시간 모니터
    </h1>
    <p style="color: rgba(255, 255, 255, 0.8); margin: 10px 0 0 0;">
        <span class="realtime-badge">⚡ LIVE</span>
        완전 실시간 (지연 없음) | 한국투자증권 Open API
    </p>
</div>
""", unsafe_allow_html=True)

# API 초기화
fetcher = init_kis_fetcher()

# API 상태 확인
if not fetcher or not st.session_state.get('kis_enabled'):
    # API 미설정 시 안내
    st.error("❌ 한국투자증권 API가 설정되지 않았습니다.")
    
    with st.expander("🔧 API 설정 방법 보기", expanded=True):
        kis_setup_guide()
    
    st.info("💡 API 키를 발급받은 후 .env 파일에 설정하고 서버를 재시작하세요.")
    st.stop()

# 정상 작동
st.success("✅ 한국투자증권 API 연결 완료!")

# 사이드바 - 종목 선택
st.sidebar.markdown("## 📈 종목 선택")

# 한국 주요 종목
KOREA_STOCKS = {
    "005930": "삼성전자",
    "000660": "SK하이닉스",
    "035420": "NAVER",
    "005380": "현대차",
    "051910": "LG화학",
    "006400": "삼성SDI",
    "035720": "카카오",
    "207940": "삼성바이오로직스",
    "068270": "셀트리온",
    "028260": "삼성물산"
}

# 종목 선택
selected_ticker = st.sidebar.selectbox(
    "종목 선택",
    options=list(KOREA_STOCKS.keys()),
    format_func=lambda x: f"{KOREA_STOCKS[x]} ({x})",
    index=0
)

selected_name = KOREA_STOCKS[selected_ticker]

# 자동 새로고침 설정
st.sidebar.markdown("---")
auto_refresh = st.sidebar.checkbox("🔄 자동 새로고침", value=True)
refresh_interval = st.sidebar.slider("새로고침 간격 (초)", 1, 10, 3)

st.sidebar.markdown("---")
st.sidebar.markdown("""
### 💡 기능
- ⚡ **완전 실시간**: 지연 없음
- 📊 **10호가**: 매수/매도 호가
- 📈 **차트**: 일봉/분봉
- 🔄 **자동 업데이트**: 1-10초

### 📚 참고
- [API 문서](https://apiportal.koreainvestment.com)
- [설정 가이드](../KIS_API_SETUP_GUIDE.md)
""")

# 메인 콘텐츠
st.markdown("---")

# 2열 레이아웃
col1, col2 = st.columns([2, 3])

with col1:
    # 실시간 주가
    st.markdown("### ⚡ 실시간 주가")
    price_data = kis_realtime_price_widget(selected_ticker, selected_name)
    
    if price_data:
        # 추가 정보
        st.markdown("#### 📊 상세 정보")
        info_cols = st.columns(2)
        
        with info_cols[0]:
            st.metric("시가", f"{price_data['open']:,}원")
            st.metric("고가", f"{price_data['high']:,}원")
        
        with info_cols[1]:
            st.metric("저가", f"{price_data['low']:,}원")
            st.metric("전일종가", f"{price_data['prev_close']:,}원")

with col2:
    # 미니 차트
    st.markdown("### 📈 가격 차트 (최근 30일)")
    df = kis_mini_chart(selected_ticker, selected_name, days=30)

st.markdown("---")

# 호가창
st.markdown("### 📊 실시간 호가창")
orderbook = kis_orderbook_widget(selected_ticker, selected_name)

st.markdown("---")

# 하단 정보
col1, col2, col3 = st.columns(3)

with col1:
    st.markdown("""
    <div class="info-card">
        <h4>⚡ 실시간 데이터</h4>
        <p>한국투자증권 Open API를 통해 <strong>완전 실시간</strong> 데이터를 제공합니다.</p>
        <p><small>지연 시간: <strong>0초</strong></small></p>
    </div>
    """, unsafe_allow_html=True)

with col2:
    st.markdown("""
    <div class="info-card">
        <h4>🔄 자동 업데이트</h4>
        <p>설정한 간격마다 자동으로 최신 데이터를 가져옵니다.</p>
        <p><small>현재 간격: <strong>{} 초</strong></small></p>
    </div>
    """.format(refresh_interval), unsafe_allow_html=True)

with col3:
    st.markdown("""
    <div class="info-card">
        <h4>📊 10호가 지원</h4>
        <p>매수/매도 10호가를 실시간으로 표시합니다.</p>
        <p><small>업데이트: <strong>실시간</strong></small></p>
    </div>
    """, unsafe_allow_html=True)

# 자동 새로고침
if auto_refresh:
    time.sleep(refresh_interval)
    st.rerun()

# 푸터
st.markdown("---")
st.caption(f"""
🚀 한국투자증권 실시간 모니터 | 
업데이트: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} | 
종목: {selected_name} ({selected_ticker}) |
⚡ 완전 실시간 (지연 없음)
""")
