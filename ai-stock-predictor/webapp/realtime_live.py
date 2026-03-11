"""
진짜 실시간 라이브 페이지 - 네이버 금융 데이터 사용
한국 주식: 네이버 실시간
미국 주식: yfinance (15분 지연 명시)
"""

import streamlit as st
import yfinance as yf
from datetime import datetime
import time
import plotly.graph_objects as go
import sys
import os

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from data.naver_realtime import get_naver_stock_price

# 페이지 설정
st.set_page_config(
    page_title="실시간 주식 모니터",
    page_icon="📊",
    layout="wide"
)

# 실시간 스타일
st.markdown("""
<style>
    .stApp {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .main .block-container {
        padding: 2rem;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .live-price-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        text-align: center;
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        margin: 20px 0;
    }
    
    .live-badge {
        display: inline-block;
        background: #ff3b3b;
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 700;
        animation: blink 1.5s ease-in-out infinite;
        margin-left: 10px;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; background: #ff3b3b; }
        50% { opacity: 0.6; background: #ff6b6b; }
    }
    
    .big-price {
        font-size: 5rem;
        font-weight: 900;
        margin: 30px 0;
        text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        letter-spacing: -2px;
    }
    
    .price-change {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 15px 0;
    }
    
    .data-source {
        font-size: 1rem;
        opacity: 0.9;
        margin-top: 20px;
        padding: 10px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
    }
    
    .metric-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .metric-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 8px;
    }
    
    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin: 20px 0;
    }
    
    .warning-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }
    
    .success-box {
        background: #d4edda;
        border-left: 4px solid #28a745;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }
</style>
""", unsafe_allow_html=True)

# 세션 상태
if 'auto_refresh' not in st.session_state:
    st.session_state.auto_refresh = True
if 'refresh_count' not in st.session_state:
    st.session_state.refresh_count = 0
if 'selected_ticker' not in st.session_state:
    st.session_state.selected_ticker = "005930.KS"

# 사이드바
with st.sidebar:
    st.title("⚙️ 실시간 설정")
    
    # 종목 선택
    ticker_input = st.text_input(
        "종목 코드",
        value=st.session_state.selected_ticker,
        help="예: 005930.KS (삼성전자), AAPL (애플)"
    )
    
    if ticker_input != st.session_state.selected_ticker:
        st.session_state.selected_ticker = ticker_input
        st.session_state.refresh_count = 0
        st.rerun()
    
    # 자동 갱신
    st.markdown("---")
    auto_refresh = st.checkbox(
        "🔄 자동 갱신",
        value=st.session_state.auto_refresh
    )
    st.session_state.auto_refresh = auto_refresh
    
    if auto_refresh:
        refresh_interval = st.slider(
            "갱신 주기 (초)",
            min_value=3,
            max_value=30,
            value=5
        )
    else:
        refresh_interval = 5
    
    # 인기 종목
    st.markdown("---")
    st.markdown("### 💫 한국 인기 종목")
    
    korea_stocks = [
        ("삼성전자", "005930.KS"),
        ("SK하이닉스", "000660.KS"),
        ("두산에너빌리티", "034020.KS"),
        ("네이버", "035420.KS"),
        ("카카오", "035720.KS"),
        ("현대차", "005380.KS"),
        ("한화에어로스페이스", "012450.KS"),
    ]
    
    for name, ticker in korea_stocks:
        if st.button(f"🇰🇷 {name}", key=f"kr_{ticker}", use_container_width=True):
            st.session_state.selected_ticker = ticker
            st.session_state.refresh_count = 0
            st.rerun()
    
    st.markdown("### 🌎 미국 인기 종목")
    
    usa_stocks = [
        ("애플", "AAPL"),
        ("테슬라", "TSLA"),
        ("엔비디아", "NVDA"),
        ("마이크로소프트", "MSFT"),
        ("구글", "GOOGL"),
        ("아마존", "AMZN"),
    ]
    
    for name, ticker in usa_stocks:
        if st.button(f"🇺🇸 {name}", key=f"us_{ticker}", use_container_width=True):
            st.session_state.selected_ticker = ticker
            st.session_state.refresh_count = 0
            st.rerun()
    
    # 정보
    st.markdown("---")
    st.info(f"""
    **갱신 횟수**: {st.session_state.refresh_count}회
    
    **마지막 업데이트**  
    {datetime.now().strftime('%H:%M:%S')}
    """)

# 메인 영역
ticker = st.session_state.selected_ticker

st.markdown(f"""
<div style='text-align: center; margin-bottom: 30px;'>
    <h1 style='font-size: 3rem; margin: 0; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);'>
        📊 실시간 주식 모니터
    </h1>
    <p style='font-size: 1.5rem; color: white; margin-top: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);'>
        {ticker}
        <span class='live-badge'>🔴 LIVE</span>
    </p>
</div>
""", unsafe_allow_html=True)

# 플레이스홀더
price_placeholder = st.empty()
chart_placeholder = st.empty()
info_placeholder = st.empty()
warning_placeholder = st.empty()

# 한국 주식 여부 확인
is_korean = ticker.endswith('.KS') or ticker.endswith('.KQ')

# 데이터 가져오기
with st.spinner('📡 실시간 데이터 수신 중...'):
    if is_korean:
        # 한국 주식 - 네이버 실시간
        code = ticker.split('.')[0]
        data = get_naver_stock_price(code)
        
        if data:
            current_price = data['current_price']
            prev_close = data['prev_close']
            price_change = data['change_price']
            price_change_pct = data['change_percent']
            
            if data['change_direction'] == '상승':
                color = "#10b981"
                emoji = "📈"
                arrow = "▲"
            elif data['change_direction'] == '하락':
                color = "#ef4444"
                emoji = "📉"
                arrow = "▼"
            else:
                color = "#fbbf24"
                emoji = "➡️"
                arrow = "━"
            
            is_realtime = True
            data_source = "네이버 금융 (KRX 실시간)"
            delay_info = "✅ 실시간 데이터 (15초 이내)"
    else:
        # 미국 주식 - yfinance
        try:
            stock = yf.Ticker(ticker)
            hist = stock.history(period="5d")
            info = stock.info
            
            if not hist.empty:
                current_price = hist['Close'].iloc[-1]
                prev_close = info.get('previousClose', hist['Close'].iloc[0])
                price_change = current_price - prev_close
                price_change_pct = (price_change / prev_close) * 100
                
                if price_change >= 0:
                    color = "#10b981"
                    emoji = "📈"
                    arrow = "▲"
                else:
                    color = "#ef4444"
                    emoji = "📉"
                    arrow = "▼"
                
                data = {
                    'current_price': current_price,
                    'prev_close': prev_close,
                    'open': hist['Open'].iloc[-1],
                    'high': hist['High'].iloc[-1],
                    'low': hist['Low'].iloc[-1],
                    'volume': hist['Volume'].iloc[-1],
                    'name': info.get('longName', ticker)
                }
                
                is_realtime = False
                data_source = "Yahoo Finance"
                delay_info = "⚠️ 15-20분 지연 데이터"
            else:
                data = None
        except:
            data = None

if data:
    # 가격 위젯
    with price_placeholder.container():
        st.markdown(f"""
        <div class='live-price-container'>
            <div style='font-size: 1.8rem; margin-bottom: 15px;'>
                {emoji} {data.get('name', ticker)}
            </div>
            <div class='big-price'>
                {current_price:,.0f}{'원' if is_korean else ''}
            </div>
            <div class='price-change' style='color: {"#00ff88" if price_change >= 0 else "#ff0055"};'>
                {arrow} {abs(price_change):,.0f}{'원' if is_korean else ''} ({price_change_pct:+.2f}%)
            </div>
            <div class='data-source'>
                📡 {data_source}<br>
                {delay_info}<br>
                🕐 {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} 업데이트
            </div>
        </div>
        """, unsafe_allow_html=True)
    
    # 데이터 출처 경고/안내
    with warning_placeholder.container():
        if is_realtime:
            st.markdown("""
            <div class='success-box'>
                <strong>✅ 진짜 실시간 데이터!</strong><br>
                네이버 금융에서 직접 가져온 KRX 실시간 데이터입니다. (15초 이내 최신)
            </div>
            """, unsafe_allow_html=True)
        else:
            st.markdown("""
            <div class='warning-box'>
                <strong>⚠️ 지연 데이터 안내</strong><br>
                미국 주식은 Yahoo Finance를 사용하여 15-20분 지연됩니다.<br>
                정확한 실시간 가격은 증권사 HTS/MTS를 확인하세요.
            </div>
            """, unsafe_allow_html=True)
    
    # 추가 정보
    with info_placeholder.container():
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            st.markdown(f"""
            <div class='metric-card'>
                <div class='metric-label'>시가</div>
                <div class='metric-value'>{data['open']:,.0f}</div>
            </div>
            """, unsafe_allow_html=True)
        
        with col2:
            high_change = ((data['high'] - prev_close) / prev_close * 100)
            st.markdown(f"""
            <div class='metric-card'>
                <div class='metric-label'>고가</div>
                <div class='metric-value'>{data['high']:,.0f}</div>
                <div style='color: #10b981; font-size: 0.9rem; margin-top: 5px;'>
                    +{high_change:.2f}%
                </div>
            </div>
            """, unsafe_allow_html=True)
        
        with col3:
            low_change = ((data['low'] - prev_close) / prev_close * 100)
            st.markdown(f"""
            <div class='metric-card'>
                <div class='metric-label'>저가</div>
                <div class='metric-value'>{data['low']:,.0f}</div>
                <div style='color: #ef4444; font-size: 0.9rem; margin-top: 5px;'>
                    {low_change:.2f}%
                </div>
            </div>
            """, unsafe_allow_html=True)
        
        with col4:
            st.markdown(f"""
            <div class='metric-card'>
                <div class='metric-label'>거래량</div>
                <div class='metric-value' style='font-size: 1.5rem;'>{data['volume']:,.0f}</div>
            </div>
            """, unsafe_allow_html=True)
    
    # 차트 (한국 주식만)
    if is_korean:
        with chart_placeholder.container():
            st.markdown('<div class="chart-container">', unsafe_allow_html=True)
            st.markdown("### 📊 최근 5일 차트 (yfinance)")
            
            try:
                stock = yf.Ticker(ticker)
                hist = stock.history(period="5d")
                
                if not hist.empty:
                    fig = go.Figure()
                    
                    fig.add_trace(go.Candlestick(
                        x=hist.index,
                        open=hist['Open'],
                        high=hist['High'],
                        low=hist['Low'],
                        close=hist['Close'],
                        name='가격',
                        increasing_line_color='#10b981',
                        decreasing_line_color='#ef4444'
                    ))
                    
                    fig.update_layout(
                        title='최근 5일 가격 차트',
                        xaxis_title='날짜',
                        yaxis_title='가격 (원)',
                        height=400,
                        hovermode='x unified',
                        template='plotly_white',
                        xaxis_rangeslider_visible=False
                    )
                    
                    st.plotly_chart(fig, use_container_width=True)
                    st.caption("⚠️ 차트는 yfinance 데이터 (15분 지연)")
            except Exception as e:
                st.warning(f"차트를 표시할 수 없습니다: {str(e)}")
            
            st.markdown('</div>', unsafe_allow_html=True)
    else:
        # 미국 주식 차트
        with chart_placeholder.container():
            st.markdown('<div class="chart-container">', unsafe_allow_html=True)
            st.markdown("### 📊 최근 5일 차트")
            
            try:
                stock = yf.Ticker(ticker)
                hist = stock.history(period="5d")
                
                if not hist.empty:
                    fig = go.Figure()
                    
                    fig.add_trace(go.Candlestick(
                        x=hist.index,
                        open=hist['Open'],
                        high=hist['High'],
                        low=hist['Low'],
                        close=hist['Close'],
                        name='가격',
                        increasing_line_color='#10b981',
                        decreasing_line_color='#ef4444'
                    ))
                    
                    fig.update_layout(
                        title='최근 5일 가격 차트',
                        xaxis_title='날짜',
                        yaxis_title='가격 (USD)',
                        height=400,
                        hovermode='x unified',
                        template='plotly_white',
                        xaxis_rangeslider_visible=False
                    )
                    
                    st.plotly_chart(fig, use_container_width=True)
                    st.caption("⚠️ 15-20분 지연 데이터 (Yahoo Finance)")
            except Exception as e:
                st.warning(f"차트를 표시할 수 없습니다: {str(e)}")
            
            st.markdown('</div>', unsafe_allow_html=True)
    
    # 세션 상태 업데이트
    st.session_state.refresh_count += 1
    
    # 자동 갱신 안내
    if auto_refresh:
        st.markdown(f"""
        <div style='text-align: center; color: #666; margin-top: 30px; padding: 20px; 
                    background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <div style='font-size: 1.2rem; font-weight: 700; margin-bottom: 10px;'>
                🔄 자동 갱신 중
            </div>
            <div style='font-size: 2rem; font-weight: 700; color: #667eea;'>
                {refresh_interval}초 후
            </div>
            <div style='margin-top: 10px; font-size: 0.9rem; color: #888;'>
                페이지가 자동으로 새로고침됩니다
            </div>
        </div>
        """, unsafe_allow_html=True)
        
        time.sleep(refresh_interval)
        st.rerun()
else:
    st.error("❌ 데이터를 불러올 수 없습니다. 종목 코드를 확인해주세요.")

# 하단 안내
st.markdown("---")
if is_korean:
    st.markdown("""
    <div style='text-align: center; color: white; padding: 20px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);'>
        💡 <strong>한국 주식은 네이버 금융 실시간 데이터를 사용합니다!</strong><br>
        약 15초 이내 최신 데이터가 표시됩니다.
    </div>
    """, unsafe_allow_html=True)
else:
    st.markdown("""
    <div style='text-align: center; color: white; padding: 20px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);'>
        ⚠️ <strong>미국 주식은 15-20분 지연 데이터입니다</strong><br>
        정확한 실시간 가격은 증권사에서 확인하세요.
    </div>
    """, unsafe_allow_html=True)
