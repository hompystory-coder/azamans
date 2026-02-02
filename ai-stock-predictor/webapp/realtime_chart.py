"""
실시간 주식 차트 - 자동 갱신 그래프
"""

import streamlit as st
import pandas as pd
import plotly.graph_objects as go
from datetime import datetime, timedelta
import sys
import os
import time

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from data.fetcher import StockDataFetcher
from data.company_database import search_ticker_advanced, KOREA_TOP_COMPANIES, USA_TOP_COMPANIES

# 페이지 설정
st.set_page_config(
    page_title="실시간 주식 차트",
    page_icon="📊",
    layout="wide"
)

# CSS 스타일
st.markdown("""
<style>
    .main .block-container {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
    
    .metric-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    
    .metric-label {
        font-size: 0.9rem;
        color: #666;
        text-transform: uppercase;
    }
    
    .metric-change {
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .positive {
        color: #34A853;
    }
    
    .negative {
        color: #EA4335;
    }
    
    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
</style>
""", unsafe_allow_html=True)

# 타이틀
st.title("📊 실시간 주식 차트")
st.markdown("---")

# 사이드바 - 설정
with st.sidebar:
    st.header("⚙️ 차트 설정")
    
    # 종목 선택
    ticker_input = st.text_input(
        "종목 코드 입력",
        value="005930.KS",
        help="예: 005930.KS (삼성전자), AAPL (애플)"
    )
    
    # 기간 선택
    period = st.selectbox(
        "기간 선택",
        ["1일", "5일", "1개월", "3개월", "6개월", "1년"],
        index=2
    )
    
    # 차트 타입
    chart_type = st.selectbox(
        "차트 타입",
        ["캔들스틱", "라인", "영역"],
        index=0
    )
    
    # 자동 갱신 설정
    auto_refresh = st.checkbox("자동 갱신", value=True)
    
    if auto_refresh:
        refresh_interval = st.slider(
            "갱신 주기 (초)",
            min_value=5,
            max_value=60,
            value=10,
            step=5
        )
    
    st.markdown("---")
    st.markdown("### 💡 주요 지수")
    
    # 주요 지수 표시
    indices = [
        ("코스피", "^KS11"),
        ("코스닥", "^KQ11"),
        ("S&P 500", "^GSPC"),
        ("나스닥", "^IXIC")
    ]
    
    for idx_name, idx_ticker in indices:
        try:
            fetcher = StockDataFetcher()
            idx_data = fetcher.fetch_data(idx_ticker, period="1d", interval="1m")
            if not idx_data.empty:
                current = idx_data['Close'].iloc[-1]
                prev = idx_data['Open'].iloc[0]
                change = ((current - prev) / prev) * 100
                
                color = "🟢" if change >= 0 else "🔴"
                st.markdown(f"{color} **{idx_name}**: {current:,.2f} ({change:+.2f}%)")
        except:
            st.markdown(f"⚪ **{idx_name}**: -")

# 메인 영역
def get_period_interval(period_str):
    """기간에 따른 interval 설정"""
    mapping = {
        "1일": ("1d", "1m"),
        "5일": ("5d", "5m"),
        "1개월": ("1mo", "30m"),
        "3개월": ("3mo", "1d"),
        "6개월": ("6mo", "1d"),
        "1년": ("1y", "1d")
    }
    return mapping.get(period_str, ("1mo", "1d"))

# 플레이스홀더 생성
metrics_placeholder = st.empty()
chart_placeholder = st.empty()
volume_placeholder = st.empty()
info_placeholder = st.empty()

# 실시간 업데이트 함수
def update_chart(ticker):
    try:
        fetcher = StockDataFetcher()
        
        # 기간 설정
        period_val, interval_val = get_period_interval(period)
        
        # 데이터 가져오기
        df = fetcher.fetch_data(ticker, period=period_val, interval=interval_val)
        
        if df.empty:
            st.error("데이터를 가져올 수 없습니다.")
            return
        
        # 현재가 정보
        current_price = df['Close'].iloc[-1]
        open_price = df['Open'].iloc[0]
        high_price = df['High'].max()
        low_price = df['Low'].min()
        volume = df['Volume'].iloc[-1]
        
        price_change = current_price - open_price
        price_change_pct = (price_change / open_price) * 100
        
        # 메트릭 카드 표시
        with metrics_placeholder.container():
            col1, col2, col3, col4, col5 = st.columns(5)
            
            with col1:
                st.markdown(
                    f'<div class="metric-card">'
                    f'<div class="metric-label">현재가</div>'
                    f'<div class="metric-value">{current_price:,.2f}</div>'
                    f'<div class="metric-change {"positive" if price_change >= 0 else "negative"}">'
                    f'{price_change:+,.2f} ({price_change_pct:+.2f}%)</div>'
                    f'</div>',
                    unsafe_allow_html=True
                )
            
            with col2:
                st.markdown(
                    f'<div class="metric-card">'
                    f'<div class="metric-label">시가</div>'
                    f'<div class="metric-value">{open_price:,.2f}</div>'
                    f'</div>',
                    unsafe_allow_html=True
                )
            
            with col3:
                st.markdown(
                    f'<div class="metric-card">'
                    f'<div class="metric-label">고가</div>'
                    f'<div class="metric-value">{high_price:,.2f}</div>'
                    f'</div>',
                    unsafe_allow_html=True
                )
            
            with col4:
                st.markdown(
                    f'<div class="metric-card">'
                    f'<div class="metric-label">저가</div>'
                    f'<div class="metric-value">{low_price:,.2f}</div>'
                    f'</div>',
                    unsafe_allow_html=True
                )
            
            with col5:
                st.markdown(
                    f'<div class="metric-card">'
                    f'<div class="metric-label">거래량</div>'
                    f'<div class="metric-value">{volume:,.0f}</div>'
                    f'</div>',
                    unsafe_allow_html=True
                )
        
        # 가격 차트
        with chart_placeholder.container():
            st.markdown('<div class="chart-container">', unsafe_allow_html=True)
            
            fig = go.Figure()
            
            if chart_type == "캔들스틱":
                fig.add_trace(go.Candlestick(
                    x=df.index,
                    open=df['Open'],
                    high=df['High'],
                    low=df['Low'],
                    close=df['Close'],
                    name='가격'
                ))
            elif chart_type == "라인":
                fig.add_trace(go.Scatter(
                    x=df.index,
                    y=df['Close'],
                    mode='lines',
                    name='종가',
                    line=dict(color='#1f77b4', width=2)
                ))
            else:  # 영역
                fig.add_trace(go.Scatter(
                    x=df.index,
                    y=df['Close'],
                    fill='tozeroy',
                    name='종가',
                    line=dict(color='#1f77b4', width=2)
                ))
            
            fig.update_layout(
                title=f'{ticker} 가격 차트',
                xaxis_title='시간',
                yaxis_title='가격 (KRW)' if ticker.endswith('.KS') or ticker.endswith('.KQ') else '가격 (USD)',
                height=500,
                hovermode='x unified',
                template='plotly_white',
                xaxis_rangeslider_visible=False
            )
            
            st.plotly_chart(fig, use_container_width=True)
            st.markdown('</div>', unsafe_allow_html=True)
        
        # 거래량 차트
        with volume_placeholder.container():
            st.markdown('<div class="chart-container">', unsafe_allow_html=True)
            
            fig_volume = go.Figure()
            
            colors = ['red' if row['Open'] > row['Close'] else 'green' 
                     for idx, row in df.iterrows()]
            
            fig_volume.add_trace(go.Bar(
                x=df.index,
                y=df['Volume'],
                name='거래량',
                marker_color=colors
            ))
            
            fig_volume.update_layout(
                title='거래량',
                xaxis_title='시간',
                yaxis_title='거래량',
                height=200,
                template='plotly_white',
                showlegend=False
            )
            
            st.plotly_chart(fig_volume, use_container_width=True)
            st.markdown('</div>', unsafe_allow_html=True)
        
        # 추가 정보
        with info_placeholder.container():
            col1, col2, col3 = st.columns(3)
            
            with col1:
                st.markdown("### 📊 기술적 지표")
                
                # 이동평균
                ma_5 = df['Close'].rolling(window=5).mean().iloc[-1] if len(df) >= 5 else current_price
                ma_20 = df['Close'].rolling(window=20).mean().iloc[-1] if len(df) >= 20 else current_price
                
                st.markdown(f"**MA(5)**: {ma_5:,.2f}")
                st.markdown(f"**MA(20)**: {ma_20:,.2f}")
                
                # RSI 계산
                if len(df) >= 14:
                    delta = df['Close'].diff()
                    gain = (delta.where(delta > 0, 0)).rolling(window=14).mean()
                    loss = (-delta.where(delta < 0, 0)).rolling(window=14).mean()
                    rs = gain / loss
                    rsi = 100 - (100 / (1 + rs))
                    st.markdown(f"**RSI(14)**: {rsi.iloc[-1]:.2f}")
            
            with col2:
                st.markdown("### 📈 변동성")
                
                # 일중 변동폭
                intraday_range = ((high_price - low_price) / low_price) * 100
                st.markdown(f"**일중 변동폭**: {intraday_range:.2f}%")
                
                # 평균 거래량 대비
                avg_volume = df['Volume'].mean()
                volume_ratio = (volume / avg_volume) * 100
                st.markdown(f"**거래량 비율**: {volume_ratio:.2f}%")
            
            with col3:
                st.markdown("### ⏰ 업데이트 정보")
                st.markdown(f"**마지막 업데이트**: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
                st.markdown(f"**데이터 포인트**: {len(df):,}개")
                
                if auto_refresh:
                    st.markdown(f"**다음 갱신**: {refresh_interval}초 후")
        
    except Exception as e:
        st.error(f"오류 발생: {str(e)}")

# 초기 차트 표시
if ticker_input:
    update_chart(ticker_input)
    
    # 자동 갱신
    if auto_refresh:
        time.sleep(refresh_interval)
        st.rerun()
else:
    st.warning("종목 코드를 입력하세요.")

# 하단 정보
st.markdown("---")
st.markdown("""
<div style='text-align: center; color: #666; font-size: 0.9rem;'>
    💡 <strong>팁</strong>: 차트는 자동으로 갱신됩니다. 사이드바에서 설정을 변경할 수 있습니다.
</div>
""", unsafe_allow_html=True)
