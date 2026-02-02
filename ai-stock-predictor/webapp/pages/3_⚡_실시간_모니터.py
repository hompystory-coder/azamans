"""
🚀 실시간 주식 모니터 (간단 버전)
FinanceDataReader + 네이버 크롤링

특징:
- ⚡ 실시간 (지연 0초)
- 🔑 API 키 불필요
- 💰 완전 무료
- 🎯 간단한 설정
"""

import streamlit as st
import pandas as pd
import plotly.graph_objects as go
import time
from datetime import datetime
import sys
import os

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

# Simple Fetcher import
try:
    from data.simple_fetcher import SimpleRealtimeFetcher, create_simple_fetcher
except ImportError as e:
    st.error(f"⚠️ 모듈 로드 실패: {e}")
    st.stop()

# 페이지 설정
st.set_page_config(
    page_title="실시간 모니터 - AI 주식 도우미",
    page_icon="⚡",
    layout="wide"
)

# 프리미엄 스타일
st.markdown("""
<style>
    /* 배경 그라데이션 */
    .stApp {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    /* 메인 컨테이너 */
    .main .block-container {
        padding-top: 2rem;
        max-width: 1400px;
    }
    
    /* 실시간 배지 애니메이션 */
    .realtime-badge {
        display: inline-block;
        background: linear-gradient(45deg, #00ff00, #00cc00);
        color: #000;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: bold;
        animation: pulse 2s infinite;
        box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }
    
    /* 가격 카드 */
    .price-card {
        background: rgba(255, 255, 255, 0.95);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        margin: 10px 0;
    }
    
    .price-big {
        font-size: 3em;
        font-weight: bold;
        margin: 10px 0;
    }
    
    .price-up {
        color: #ff4444;
    }
    
    .price-down {
        color: #4444ff;
    }
    
    /* 정보 칩 */
    .info-chip {
        display: inline-block;
        background: rgba(102, 126, 234, 0.2);
        padding: 5px 12px;
        border-radius: 15px;
        margin: 5px;
        font-size: 0.9em;
    }
</style>
""", unsafe_allow_html=True)

# 헤더
st.markdown("""
<div style="text-align: center; padding: 20px; background: rgba(255, 255, 255, 0.1); border-radius: 15px; margin-bottom: 20px;">
    <h1 style="color: white; margin: 0;">
        ⚡ 실시간 주식 모니터
    </h1>
    <p style="color: rgba(255, 255, 255, 0.9); margin: 10px 0 0 0; font-size: 1.1em;">
        <span class="realtime-badge">⚡ LIVE</span>
        실시간 (지연 0초) | 네이버 금융 + FinanceDataReader | API 키 불필요
    </p>
</div>
""", unsafe_allow_html=True)

# Fetcher 초기화
@st.cache_resource
def get_fetcher():
    """Fetcher 인스턴스 생성 (캐싱)"""
    return create_simple_fetcher()

fetcher = get_fetcher()

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

# 자동 새로고침
st.sidebar.markdown("---")
st.sidebar.markdown("### ⚙️ 설정")
auto_refresh = st.sidebar.checkbox("🔄 자동 새로고침", value=True)
refresh_interval = st.sidebar.slider("새로고침 간격 (초)", 1, 10, 2)

# 차트 기간
chart_days = st.sidebar.slider("차트 기간 (일)", 7, 90, 30)

st.sidebar.markdown("---")
st.sidebar.markdown("""
### 💡 특징
- ⚡ **실시간**: 지연 0초
- 🔑 **API 키 불필요**
- 💰 **완전 무료**
- 📊 **안정적인 데이터**

### 📚 데이터 소스
- **현재가**: 네이버 금융 (실시간)
- **차트**: FinanceDataReader (안정적)
""")

# 메인 콘텐츠
st.markdown("---")

# 실시간 주가 조회
try:
    with st.spinner("⚡ 실시간 데이터 로딩 중..."):
        price_data = fetcher.get_realtime_price(selected_ticker)
    
    if price_data['source'] == 'error':
        st.error("❌ 데이터 조회 실패")
        st.stop()
    
    # 색상 결정
    if price_data['change'] > 0:
        color_class = "price-up"
        arrow = "🔴"
    elif price_data['change'] < 0:
        color_class = "price-down"
        arrow = "🔵"
    else:
        color_class = ""
        arrow = "⚪"
    
    # 상단: 실시간 주가
    col1, col2 = st.columns([2, 3])
    
    with col1:
        st.markdown(f"""
        <div class="price-card">
            <h2 style="margin: 0; color: #333;">
                {arrow} {price_data['name']}
                <span style="font-size: 0.5em; color: #999;">({selected_ticker})</span>
            </h2>
            <div class="price-big {color_class}">
                {price_data['price']:,}원
            </div>
            <div style="font-size: 1.3em; {('color: #ff4444;' if price_data['change'] > 0 else 'color: #4444ff;' if price_data['change'] < 0 else 'color: #999;')}">
                {price_data['change']:+,}원 ({price_data['change_pct']:+.2f}%)
            </div>
            <div style="margin-top: 15px; color: #666; font-size: 0.9em;">
                <div class="info-chip">거래량: {price_data['volume']:,}주</div>
                <div class="info-chip">시가총액: {price_data['market_cap']}</div>
            </div>
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; color: #999; font-size: 0.85em;">
                <div>⏰ {price_data['timestamp']}</div>
                <div>📡 {price_data['source']}</div>
            </div>
        </div>
        """, unsafe_allow_html=True)
        
        # 추가 정보
        st.markdown("### 📊 상세 정보")
        info_col1, info_col2 = st.columns(2)
        
        with info_col1:
            st.metric("시가", f"{price_data['open']:,}원")
            st.metric("고가", f"{price_data['high']:,}원")
        
        with info_col2:
            st.metric("저가", f"{price_data['low']:,}원")
            st.metric("전일종가", f"{price_data['prev_close']:,}원")
    
    with col2:
        # 차트
        st.markdown(f"### 📈 {selected_name} 가격 차트 (최근 {chart_days}일)")
        
        df = fetcher.get_ohlcv_daily(selected_ticker, days=chart_days)
        
        if not df.empty:
            # Plotly 캔들스틱 차트
            fig = go.Figure(data=[go.Candlestick(
                x=df['date'],
                open=df['open'],
                high=df['high'],
                low=df['low'],
                close=df['close'],
                name='가격'
            )])
            
            fig.update_layout(
                title=f"{selected_name} 일봉 차트",
                yaxis_title="가격 (원)",
                xaxis_title="날짜",
                template="plotly_white",
                height=400,
                showlegend=False,
                hovermode='x unified'
            )
            
            st.plotly_chart(fig, use_container_width=True)
            
            # 거래량 차트
            fig_vol = go.Figure(data=[go.Bar(
                x=df['date'],
                y=df['volume'],
                name='거래량',
                marker_color='rgba(102, 126, 234, 0.6)'
            )])
            
            fig_vol.update_layout(
                title="거래량",
                yaxis_title="거래량 (주)",
                xaxis_title="날짜",
                template="plotly_white",
                height=200,
                showlegend=False
            )
            
            st.plotly_chart(fig_vol, use_container_width=True)
        else:
            st.warning("차트 데이터를 불러올 수 없습니다.")
    
    # 하단: 통계
    st.markdown("---")
    st.markdown("### 📊 오늘의 통계")
    
    stat_col1, stat_col2, stat_col3, stat_col4 = st.columns(4)
    
    with stat_col1:
        st.markdown(f"""
        <div class="price-card" style="text-align: center;">
            <div style="color: #666; font-size: 0.9em;">등락률</div>
            <div style="font-size: 2em; font-weight: bold; {('color: #ff4444;' if price_data['change_pct'] > 0 else 'color: #4444ff;' if price_data['change_pct'] < 0 else 'color: #999;')}">
                {price_data['change_pct']:+.2f}%
            </div>
        </div>
        """, unsafe_allow_html=True)
    
    with stat_col2:
        st.markdown(f"""
        <div class="price-card" style="text-align: center;">
            <div style="color: #666; font-size: 0.9em;">등락폭</div>
            <div style="font-size: 2em; font-weight: bold; color: #667eea;">
                {abs(price_data['high'] - price_data['low']):,}원
            </div>
        </div>
        """, unsafe_allow_html=True)
    
    with stat_col3:
        st.markdown(f"""
        <div class="price-card" style="text-align: center;">
            <div style="color: #666; font-size: 0.9em;">거래량</div>
            <div style="font-size: 2em; font-weight: bold; color: #667eea;">
                {price_data['volume']:,}
            </div>
        </div>
        """, unsafe_allow_html=True)
    
    with stat_col4:
        volatility = ((price_data['high'] - price_data['low']) / price_data['prev_close']) * 100 if price_data['prev_close'] > 0 else 0
        st.markdown(f"""
        <div class="price-card" style="text-align: center;">
            <div style="color: #666; font-size: 0.9em;">변동성</div>
            <div style="font-size: 2em; font-weight: bold; color: #667eea;">
                {volatility:.2f}%
            </div>
        </div>
        """, unsafe_allow_html=True)
    
    # 여러 종목 비교
    st.markdown("---")
    st.markdown("### 🔍 주요 종목 한눈에 보기")
    
    comparison_data = []
    for ticker, name in KOREA_STOCKS.items():
        try:
            price = fetcher.get_realtime_price(ticker)
            comparison_data.append({
                '종목명': name,
                '종목코드': ticker,
                '현재가': f"{price['price']:,}원",
                '변동': f"{price['change']:+,}원",
                '등락률': f"{price['change_pct']:+.2f}%",
                '거래량': f"{price['volume']:,}주",
                '데이터': price['source']
            })
        except:
            pass
    
    if comparison_data:
        df_comparison = pd.DataFrame(comparison_data)
        st.dataframe(df_comparison, use_container_width=True, hide_index=True)

except Exception as e:
    st.error(f"❌ 오류 발생: {str(e)}")
    import traceback
    st.code(traceback.format_exc())

# 자동 새로고침
if auto_refresh:
    time.sleep(refresh_interval)
    st.rerun()

# 푸터
st.markdown("---")
st.caption(f"""
⚡ 실시간 주식 모니터 | 
업데이트: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} | 
종목: {selected_name} ({selected_ticker}) |
API 키 불필요 · 완전 무료
""")
