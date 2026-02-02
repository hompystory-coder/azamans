"""
🔬 필라델피아 반도체 지수 (SOXX) 실시간 추적 대시보드
Philadelphia Semiconductor Index Tracker
"""

import streamlit as st
import pandas as pd
import numpy as np
import plotly.graph_objects as go
from plotly.subplots import make_subplots
from datetime import datetime, timedelta
import sys
import os
import time
import yfinance as yf

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

try:
    from data.fetcher import StockDataFetcher
    from data.indicators import TechnicalIndicators
except Exception as e:
    st.error(f"모듈 로드 실패: {str(e)}")
    st.stop()

# 페이지 설정
st.set_page_config(
    page_title="필라델피아 반도체 지수 - AI 주식 도우미",
    page_icon="🔬",
    layout="wide",
    initial_sidebar_state="expanded"
)

# 🎨 프리미엄 CSS 스타일
st.markdown("""
<style>
    /* 전체 배경 - 반도체 테마 그라데이션 */
    .stApp {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%) !important;
    }
    
    /* 메인 컨테이너 */
    .main .block-container {
        padding-top: 2rem;
        padding-bottom: 2rem;
        max-width: 1400px;
    }
    
    /* 사이드바 */
    [data-testid="stSidebar"] {
        background: linear-gradient(180deg, rgba(26, 26, 46, 0.98) 0%, rgba(15, 52, 96, 0.98) 100%) !important;
        border-right: 1px solid rgba(0, 173, 181, 0.3);
    }
    
    [data-testid="stSidebar"] * {
        color: #ffffff !important;
    }
    
    /* 모든 텍스트를 밝은 색으로 */
    body, p, span, div, label, input, textarea, select, option, 
    h1, h2, h3, h4, h5, h6, li, td, th, a {
        color: #ffffff !important;
    }
    
    /* Streamlit 기본 텍스트 */
    .stMarkdown, .stMarkdown p, .stMarkdown span, .stMarkdown div {
        color: #ffffff !important;
    }
    
    * {
        color: #ffffff !important;
    }
    
    /* 프리미엄 헤더 - 반도체 테마 */
    .semiconductor-header {
        text-align: center;
        background: linear-gradient(135deg, #00adb5 0%, #00d4ff 50%, #00fff0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 3.5rem;
        font-weight: 900;
        letter-spacing: 3px;
        margin: 20px 0;
        text-shadow: 0 0 30px rgba(0, 173, 181, 0.5);
        animation: glow 3s ease-in-out infinite;
    }
    
    @keyframes glow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; filter: brightness(1.2); }
    }
    
    /* 서브 헤더 */
    .semiconductor-subheader {
        text-align: center;
        color: #00fff0 !important;
        font-size: 1.4rem;
        font-weight: 500;
        margin-bottom: 40px;
        letter-spacing: 1px;
    }
    
    /* 메트릭 카드 스타일 */
    [data-testid="stMetricValue"] {
        font-size: 2.5rem !important;
        font-weight: 700 !important;
        color: #00fff0 !important;
    }
    
    [data-testid="stMetricLabel"] {
        font-size: 1.1rem !important;
        color: #aaaaaa !important;
        font-weight: 500 !important;
    }
    
    [data-testid="stMetricDelta"] {
        font-size: 1.2rem !important;
        font-weight: 600 !important;
    }
    
    /* 카드 스타일 */
    .metric-card {
        background: rgba(0, 173, 181, 0.1);
        border: 1px solid rgba(0, 173, 181, 0.3);
        border-radius: 15px;
        padding: 20px;
        margin: 10px 0;
        box-shadow: 0 4px 15px rgba(0, 173, 181, 0.2);
        backdrop-filter: blur(10px);
    }
    
    /* 버튼 스타일 */
    .stButton > button {
        background: linear-gradient(135deg, #00adb5 0%, #00d4ff 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 25px;
        padding: 12px 30px;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 173, 181, 0.3);
    }
    
    .stButton > button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 173, 181, 0.4);
    }
    
    /* 탭 스타일 */
    .stTabs [data-baseweb="tab-list"] {
        gap: 8px;
        background-color: rgba(0, 173, 181, 0.1);
        border-radius: 15px;
        padding: 5px;
    }
    
    .stTabs [data-baseweb="tab"] {
        background-color: transparent;
        border-radius: 10px;
        color: #ffffff !important;
        font-weight: 600;
        padding: 10px 20px;
    }
    
    .stTabs [aria-selected="true"] {
        background: linear-gradient(135deg, #00adb5 0%, #00d4ff 100%);
    }
    
    /* 테이블 스타일 */
    .dataframe {
        background: rgba(0, 173, 181, 0.05);
        border-radius: 10px;
    }
    
    .dataframe th {
        background: rgba(0, 173, 181, 0.2) !important;
        color: #00fff0 !important;
        font-weight: 700;
        padding: 12px;
    }
    
    .dataframe td {
        color: #ffffff !important;
        padding: 10px;
    }
</style>
""", unsafe_allow_html=True)

# 헤더
st.markdown('<h1 class="semiconductor-header">🔬 필라델피아 반도체 지수</h1>', unsafe_allow_html=True)
st.markdown('<p class="semiconductor-subheader">Philadelphia Semiconductor Index (SOXX) Real-time Tracker</p>', unsafe_allow_html=True)

# 반도체 주요 종목 리스트
SEMICONDUCTOR_STOCKS = {
    "SOXX": "iShares Semiconductor ETF",
    "NVDA": "NVIDIA",
    "TSM": "Taiwan Semiconductor",
    "AVGO": "Broadcom",
    "AMD": "Advanced Micro Devices",
    "INTC": "Intel",
    "QCOM": "Qualcomm",
    "TXN": "Texas Instruments",
    "AMAT": "Applied Materials",
    "LRCX": "Lam Research",
    "ASML": "ASML Holding",
    "MU": "Micron Technology",
    "KLAC": "KLA Corporation",
    "ADI": "Analog Devices",
    "MRVL": "Marvell Technology"
}

# 한국 반도체 종목
KOREA_SEMICONDUCTOR = {
    "005930.KS": "삼성전자",
    "000660.KS": "SK하이닉스",
    "042700.KS": "한미반도체",
    "003670.KS": "포스코케미칼"
}

@st.cache_data(ttl=60)
def get_realtime_data(ticker):
    """실시간 주가 데이터 가져오기"""
    try:
        stock = yf.Ticker(ticker)
        info = stock.info
        hist = stock.history(period="1d", interval="1m")
        
        if hist.empty:
            return None
            
        current_price = hist['Close'].iloc[-1]
        prev_close = info.get('previousClose', current_price)
        change = current_price - prev_close
        change_pct = (change / prev_close) * 100 if prev_close != 0 else 0
        
        return {
            'ticker': ticker,
            'name': SEMICONDUCTOR_STOCKS.get(ticker, ticker),
            'price': current_price,
            'change': change,
            'change_pct': change_pct,
            'volume': info.get('volume', 0),
            'market_cap': info.get('marketCap', 0),
            'hist': hist
        }
    except Exception as e:
        st.warning(f"{ticker} 데이터 로드 실패: {str(e)}")
        return None

@st.cache_data(ttl=300)
def get_historical_data(ticker, period="6mo"):
    """과거 데이터 가져오기"""
    try:
        stock = yf.Ticker(ticker)
        hist = stock.history(period=period)
        return hist
    except Exception as e:
        st.warning(f"{ticker} 과거 데이터 로드 실패: {str(e)}")
        return None

def create_candlestick_chart(data, title):
    """캔들스틱 차트 생성"""
    fig = go.Figure(data=[go.Candlestick(
        x=data.index,
        open=data['Open'],
        high=data['High'],
        low=data['Low'],
        close=data['Close'],
        increasing_line_color='#00ff88',
        decreasing_line_color='#ff4444',
        name=title
    )])
    
    # 거래량 추가
    fig.add_trace(go.Bar(
        x=data.index,
        y=data['Volume'],
        name='Volume',
        yaxis='y2',
        marker_color='rgba(0, 173, 181, 0.3)'
    ))
    
    fig.update_layout(
        title=f"{title} 차트",
        yaxis_title="가격 (USD)",
        yaxis2=dict(title="거래량", overlaying='y', side='right'),
        xaxis_rangeslider_visible=False,
        template="plotly_dark",
        height=500,
        paper_bgcolor='rgba(0,0,0,0)',
        plot_bgcolor='rgba(0,0,0,0)',
        font=dict(color='#ffffff')
    )
    
    return fig

def create_comparison_chart(stocks_data):
    """여러 종목 비교 차트"""
    fig = go.Figure()
    
    for ticker, data in stocks_data.items():
        if data is not None and not data['hist'].empty:
            normalized = (data['hist']['Close'] / data['hist']['Close'].iloc[0] - 1) * 100
            fig.add_trace(go.Scatter(
                x=data['hist'].index,
                y=normalized,
                mode='lines',
                name=f"{ticker} ({data['name']})",
                line=dict(width=2)
            ))
    
    fig.update_layout(
        title="반도체 주요 종목 수익률 비교 (정규화)",
        yaxis_title="수익률 (%)",
        xaxis_title="시간",
        template="plotly_dark",
        height=500,
        paper_bgcolor='rgba(0,0,0,0)',
        plot_bgcolor='rgba(0,0,0,0)',
        font=dict(color='#ffffff'),
        hovermode='x unified'
    )
    
    return fig

# 사이드바 - 설정
with st.sidebar:
    st.markdown("### ⚙️ 설정")
    
    refresh_interval = st.slider(
        "새로고침 간격 (초)",
        min_value=10,
        max_value=300,
        value=60,
        step=10
    )
    
    show_korean = st.checkbox("한국 반도체 종목 포함", value=True)
    
    period_options = {
        "1일": "1d",
        "5일": "5d",
        "1개월": "1mo",
        "3개월": "3mo",
        "6개월": "6mo",
        "1년": "1y"
    }
    selected_period = st.selectbox(
        "조회 기간",
        options=list(period_options.keys()),
        index=4
    )
    period = period_options[selected_period]
    
    st.markdown("---")
    st.markdown("### 📊 주요 지표")
    st.info("""
    **SOXX ETF**: 필라델피아 반도체 지수를 추종하는 ETF
    
    **주요 구성 종목**:
    - NVIDIA, AMD (GPU)
    - Intel, Qualcomm (CPU)
    - TSMC, Samsung (파운드리)
    - ASML (장비)
    """)
    
    if st.button("🔄 수동 새로고침"):
        st.cache_data.clear()
        st.rerun()

# 메인 영역
tab1, tab2, tab3, tab4 = st.tabs(["📈 실시간 모니터", "📊 상세 분석", "🔍 종목 비교", "🌏 글로벌 반도체"])

with tab1:
    st.markdown("### 🔬 SOXX ETF 실시간 현황")
    
    # SOXX 실시간 데이터
    soxx_data = get_realtime_data("SOXX")
    
    if soxx_data:
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            st.metric(
                "현재가",
                f"${soxx_data['price']:.2f}",
                f"{soxx_data['change']:+.2f} ({soxx_data['change_pct']:+.2f}%)"
            )
        
        with col2:
            st.metric("거래량", f"{soxx_data['volume']:,}")
        
        with col3:
            market_cap_b = soxx_data['market_cap'] / 1e9
            st.metric("시가총액", f"${market_cap_b:.2f}B")
        
        with col4:
            last_update = datetime.now().strftime("%H:%M:%S")
            st.metric("최종 업데이트", last_update)
        
        # 1분봉 차트
        if not soxx_data['hist'].empty:
            st.markdown("#### 📊 실시간 1분봉 차트")
            fig = go.Figure()
            fig.add_trace(go.Scatter(
                x=soxx_data['hist'].index,
                y=soxx_data['hist']['Close'],
                mode='lines',
                name='SOXX',
                line=dict(color='#00d4ff', width=2),
                fill='tozeroy',
                fillcolor='rgba(0, 212, 255, 0.1)'
            ))
            
            fig.update_layout(
                height=400,
                template="plotly_dark",
                paper_bgcolor='rgba(0,0,0,0)',
                plot_bgcolor='rgba(0,0,0,0)',
                font=dict(color='#ffffff'),
                yaxis_title="가격 (USD)",
                xaxis_title="시간",
                hovermode='x'
            )
            
            st.plotly_chart(fig, use_container_width=True)
    else:
        st.error("SOXX 데이터를 불러올 수 없습니다.")
    
    st.markdown("---")
    
    # 주요 반도체 종목 현황
    st.markdown("### 🏢 주요 반도체 종목 실시간 현황")
    
    # 상위 8개 종목만 표시
    top_stocks = ["NVDA", "TSM", "AVGO", "AMD", "INTC", "QCOM", "ASML", "MU"]
    
    cols = st.columns(4)
    for idx, ticker in enumerate(top_stocks):
        with cols[idx % 4]:
            stock_data = get_realtime_data(ticker)
            if stock_data:
                color = "🟢" if stock_data['change'] >= 0 else "🔴"
                st.markdown(f"""
                <div class="metric-card">
                    <h4>{color} {stock_data['name']}</h4>
                    <h3>${stock_data['price']:.2f}</h3>
                    <p>{stock_data['change']:+.2f} ({stock_data['change_pct']:+.2f}%)</p>
                </div>
                """, unsafe_allow_html=True)

with tab2:
    st.markdown("### 📊 SOXX ETF 상세 분석")
    
    # 과거 데이터 로드
    hist_data = get_historical_data("SOXX", period)
    
    if hist_data is not None and not hist_data.empty:
        # 캔들스틱 차트
        fig = create_candlestick_chart(hist_data, "SOXX ETF")
        st.plotly_chart(fig, use_container_width=True)
        
        # 기술적 지표
        st.markdown("#### 📈 기술적 지표")
        
        # 이동평균선 계산
        hist_data['MA5'] = hist_data['Close'].rolling(window=5).mean()
        hist_data['MA20'] = hist_data['Close'].rolling(window=20).mean()
        hist_data['MA60'] = hist_data['Close'].rolling(window=60).mean()
        
        # RSI 계산
        delta = hist_data['Close'].diff()
        gain = (delta.where(delta > 0, 0)).rolling(window=14).mean()
        loss = (-delta.where(delta < 0, 0)).rolling(window=14).mean()
        rs = gain / loss
        rsi = 100 - (100 / (1 + rs))
        
        col1, col2, col3 = st.columns(3)
        
        with col1:
            current_price = hist_data['Close'].iloc[-1]
            ma20 = hist_data['MA20'].iloc[-1]
            trend = "상승" if current_price > ma20 else "하락"
            st.metric("현재 추세", trend)
        
        with col2:
            rsi_current = rsi.iloc[-1]
            rsi_status = "과매수" if rsi_current > 70 else "과매도" if rsi_current < 30 else "중립"
            st.metric("RSI (14일)", f"{rsi_current:.2f}", rsi_status)
        
        with col3:
            volatility = hist_data['Close'].pct_change().std() * 100
            st.metric("변동성", f"{volatility:.2f}%")
        
        # 통계 정보
        st.markdown("#### 📊 통계 정보")
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            st.metric("52주 최고가", f"${hist_data['High'].max():.2f}")
        with col2:
            st.metric("52주 최저가", f"${hist_data['Low'].min():.2f}")
        with col3:
            avg_volume = hist_data['Volume'].mean()
            st.metric("평균 거래량", f"{avg_volume:,.0f}")
        with col4:
            price_range = ((hist_data['Close'].iloc[-1] - hist_data['Close'].min()) / 
                          (hist_data['High'].max() - hist_data['Close'].min()) * 100)
            st.metric("가격 위치", f"{price_range:.1f}%")
    else:
        st.error("과거 데이터를 불러올 수 없습니다.")

with tab3:
    st.markdown("### 🔍 반도체 주요 종목 비교")
    
    # 비교할 종목 선택
    selected_tickers = st.multiselect(
        "비교할 종목을 선택하세요",
        options=list(SEMICONDUCTOR_STOCKS.keys()),
        default=["SOXX", "NVDA", "AMD", "INTC"]
    )
    
    if selected_tickers:
        # 데이터 로드
        stocks_data = {}
        for ticker in selected_tickers:
            data = get_realtime_data(ticker)
            if data:
                stocks_data[ticker] = data
        
        # 비교 차트
        if stocks_data:
            fig = create_comparison_chart(stocks_data)
            st.plotly_chart(fig, use_container_width=True)
            
            # 성과 비교 테이블
            st.markdown("#### 📊 성과 비교")
            
            comparison_df = pd.DataFrame([
                {
                    '종목': data['name'],
                    '티커': ticker,
                    '현재가': f"${data['price']:.2f}",
                    '변동': f"{data['change']:+.2f}",
                    '변동률': f"{data['change_pct']:+.2f}%",
                    '거래량': f"{data['volume']:,}"
                }
                for ticker, data in stocks_data.items()
            ])
            
            st.dataframe(comparison_df, use_container_width=True, hide_index=True)
        else:
            st.warning("선택한 종목의 데이터를 불러올 수 없습니다.")
    else:
        st.info("비교할 종목을 선택해주세요.")

with tab4:
    st.markdown("### 🌏 글로벌 반도체 시장")
    
    col1, col2 = st.columns(2)
    
    with col1:
        st.markdown("#### 🇺🇸 미국 반도체 기업")
        
        us_stocks = ["NVDA", "AMD", "INTC", "QCOM", "TXN", "AMAT"]
        
        for ticker in us_stocks:
            data = get_realtime_data(ticker)
            if data:
                color = "🟢" if data['change'] >= 0 else "🔴"
                st.markdown(f"""
                <div class="metric-card">
                    <h4>{color} {data['name']} ({ticker})</h4>
                    <h3>${data['price']:.2f}</h3>
                    <p>{data['change']:+.2f} ({data['change_pct']:+.2f}%)</p>
                </div>
                """, unsafe_allow_html=True)
    
    with col2:
        st.markdown("#### 🇰🇷 한국 반도체 기업")
        
        if show_korean:
            for ticker, name in KOREA_SEMICONDUCTOR.items():
                try:
                    stock = yf.Ticker(ticker)
                    info = stock.info
                    hist = stock.history(period="1d")
                    
                    if not hist.empty:
                        current_price = hist['Close'].iloc[-1]
                        prev_close = info.get('previousClose', current_price)
                        change = current_price - prev_close
                        change_pct = (change / prev_close) * 100 if prev_close != 0 else 0
                        
                        color = "🟢" if change >= 0 else "🔴"
                        st.markdown(f"""
                        <div class="metric-card">
                            <h4>{color} {name} ({ticker})</h4>
                            <h3>₩{current_price:,.0f}</h3>
                            <p>{change:+,.0f} ({change_pct:+.2f}%)</p>
                        </div>
                        """, unsafe_allow_html=True)
                except Exception as e:
                    st.warning(f"{name} 데이터 로드 실패")
        else:
            st.info("사이드바에서 '한국 반도체 종목 포함'을 체크하세요.")

# 푸터
st.markdown("---")
st.markdown("""
<div style='text-align: center; color: #aaaaaa; padding: 20px;'>
    <p>🔬 필라델피아 반도체 지수 실시간 추적 대시보드</p>
    <p>데이터 제공: Yahoo Finance | 업데이트: 실시간</p>
    <p style='font-size: 0.8rem;'>본 정보는 투자 참고용이며, 투자 결정은 본인의 책임입니다.</p>
</div>
""", unsafe_allow_html=True)

# 자동 새로고침
if refresh_interval > 0:
    time.sleep(refresh_interval)
    st.rerun()
