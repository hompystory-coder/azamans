"""
실시간 주식 대시보드 - 개선된 버전
- 자동 갱신
- 실시간 가격 업데이트
- 라이브 차트
- 웹소켓 스타일 업데이트
"""

import streamlit as st
import pandas as pd
import plotly.graph_objects as go
from datetime import datetime, timedelta
import time

# 페이지 설정
st.set_page_config(
    page_title="실시간 AI 주식 도우미",
    page_icon="📊",
    layout="wide"
)

# 실시간 스타일 CSS
st.markdown("""
<style>
    /* 전체 배경 */
    .stApp {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    /* 메인 컨테이너 */
    .main .block-container {
        padding: 2rem;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    /* 실시간 지표 카드 */
    .live-metric {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 20px;
        color: white;
        text-align: center;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    
    .live-badge {
        display: inline-block;
        background: #ff3b3b;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        animation: blink 1.5s ease-in-out infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .metric-value-live {
        font-size: 3rem;
        font-weight: 800;
        margin: 10px 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .metric-label-live {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .metric-change-live {
        font-size: 1.3rem;
        font-weight: 600;
        margin-top: 8px;
    }
    
    .chart-container-live {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin: 20px 0;
    }
    
    .update-info {
        background: #f0f0f0;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        margin: 20px 0;
        font-size: 0.9rem;
        color: #666;
    }
    
    .ticker-badge {
        display: inline-block;
        background: #667eea;
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 1.2rem;
        font-weight: 700;
        margin: 10px 5px;
    }
    
    /* 애니메이션 효과 */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animated {
        animation: slideInUp 0.6s ease-out;
    }
    
    /* 프로그레스 바 */
    .progress-bar {
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
        animation: progress 3s ease-in-out infinite;
    }
    
    @keyframes progress {
        0% { width: 0%; }
        50% { width: 100%; }
        100% { width: 0%; }
    }
</style>
""", unsafe_allow_html=True)

# 세션 상태 초기화
if 'last_update' not in st.session_state:
    st.session_state.last_update = datetime.now()
if 'update_count' not in st.session_state:
    st.session_state.update_count = 0
if 'auto_refresh' not in st.session_state:
    st.session_state.auto_refresh = True

# 타이틀
st.markdown("""
<div style='text-align: center; margin-bottom: 2rem;'>
    <h1 style='font-size: 3rem; font-weight: 800; 
               background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
               -webkit-background-clip: text;
               -webkit-text-fill-color: transparent;
               margin: 0;'>
        📊 실시간 AI 주식 도우미
    </h1>
    <p style='color: #666; font-size: 1.1rem; margin-top: 10px;'>
        <span class='live-badge'>🔴 LIVE</span> 자동 갱신 차트
    </p>
</div>
""", unsafe_allow_html=True)

# 사이드바 설정
with st.sidebar:
    st.markdown("## ⚙️ 실시간 설정")
    
    # 종목 선택
    ticker = st.text_input(
        "🔍 종목 코드",
        value="005930.KS",
        help="예: 005930.KS (삼성전자), AAPL (애플), TSLA (테슬라)"
    )
    
    # 갱신 설정
    st.markdown("---")
    st.markdown("### 🔄 자동 갱신")
    
    auto_refresh = st.checkbox(
        "자동 갱신 활성화",
        value=st.session_state.auto_refresh,
        help="체크하면 차트가 자동으로 업데이트됩니다"
    )
    st.session_state.auto_refresh = auto_refresh
    
    if auto_refresh:
        refresh_interval = st.slider(
            "갱신 주기 (초)",
            min_value=3,
            max_value=60,
            value=5,
            step=1,
            help="짧을수록 실시간성이 높지만 서버 부하가 증가합니다"
        )
    else:
        refresh_interval = 10
    
    # 차트 설정
    st.markdown("---")
    st.markdown("### 📈 차트 설정")
    
    chart_type = st.selectbox(
        "차트 타입",
        ["캔들스틱", "라인", "영역", "OHLC"],
        index=0
    )
    
    time_range = st.selectbox(
        "시간 범위",
        ["1시간", "1일", "5일", "1주일", "1개월"],
        index=1
    )
    
    show_volume = st.checkbox("거래량 표시", value=True)
    show_indicators = st.checkbox("기술적 지표 표시", value=True)
    
    # 업데이트 정보
    st.markdown("---")
    st.markdown("### ℹ️ 업데이트 정보")
    st.info(f"""
    **마지막 업데이트**  
    {st.session_state.last_update.strftime('%Y-%m-%d %H:%M:%S')}
    
    **총 업데이트 횟수**  
    {st.session_state.update_count}회
    """)
    
    if auto_refresh:
        time_since_update = (datetime.now() - st.session_state.last_update).seconds
        remaining = max(0, refresh_interval - time_since_update)
        st.markdown(f"**다음 갱신까지**: {remaining}초")

# 메인 영역 플레이스홀더
header_placeholder = st.empty()
metrics_placeholder = st.empty()
chart_placeholder = st.empty()
volume_placeholder = st.empty()
indicators_placeholder = st.empty()
footer_placeholder = st.empty()

def get_live_data(ticker, time_range):
    """실시간 데이터 가져오기 (시뮬레이션)"""
    # 실제로는 API에서 가져와야 함
    # 여기서는 샘플 데이터 생성
    
    range_map = {
        "1시간": ("1d", "1m", 60),
        "1일": ("1d", "1m", 390),
        "5일": ("5d", "5m", 390),
        "1주일": ("5d", "15m", 224),
        "1개월": ("1mo", "1h", 168)
    }
    
    period, interval, points = range_map.get(time_range, ("1d", "1m", 390))
    
    # 실제 데이터 가져오기 (yfinance 사용)
    try:
        import yfinance as yf
        data = yf.download(ticker, period=period, interval=interval, progress=False)
        
        if data.empty:
            # 샘플 데이터 생성
            dates = pd.date_range(end=datetime.now(), periods=points, freq='1min')
            base_price = 70000
            data = pd.DataFrame({
                'Open': base_price + np.random.randn(points) * 500,
                'High': base_price + np.random.randn(points) * 500 + 200,
                'Low': base_price + np.random.randn(points) * 500 - 200,
                'Close': base_price + np.random.randn(points) * 500,
                'Volume': np.random.randint(1000000, 5000000, points)
            }, index=dates)
        
        return data
    except:
        # 실패 시 샘플 데이터
        dates = pd.date_range(end=datetime.now(), periods=points, freq='1min')
        base_price = 70000
        data = pd.DataFrame({
            'Open': base_price + np.random.randn(points) * 500,
            'High': base_price + np.random.randn(points) * 500 + 200,
            'Low': base_price + np.random.randn(points) * 500 - 200,
            'Close': base_price + np.random.randn(points) * 500,
            'Volume': np.random.randint(1000000, 5000000, points)
        }, index=dates)
        return data

def update_dashboard():
    """대시보드 업데이트"""
    
    # 데이터 가져오기
    with st.spinner('📡 실시간 데이터 수신 중...'):
        df = get_live_data(ticker, time_range)
    
    if df.empty:
        st.error("데이터를 가져올 수 없습니다.")
        return
    
    # 현재 정보 계산
    current_price = df['Close'].iloc[-1]
    prev_close = df['Close'].iloc[0]
    price_change = current_price - prev_close
    price_change_pct = (price_change / prev_close) * 100
    
    high_price = df['High'].max()
    low_price = df['Low'].min()
    volume = df['Volume'].iloc[-1]
    avg_volume = df['Volume'].mean()
    
    # 헤더 - 티커 정보
    with header_placeholder.container():
        st.markdown(f"""
        <div style='text-align: center; margin-bottom: 20px;'>
            <span class='ticker-badge'>{ticker}</span>
        </div>
        """, unsafe_allow_html=True)
    
    # 실시간 메트릭 카드
    with metrics_placeholder.container():
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            change_color = "#00ff88" if price_change >= 0 else "#ff0055"
            st.markdown(f"""
            <div class='live-metric animated'>
                <div class='metric-label-live'>실시간 현재가</div>
                <div class='metric-value-live'>{current_price:,.0f}</div>
                <div class='metric-change-live' style='color: {change_color};'>
                    {price_change:+,.0f} ({price_change_pct:+.2f}%)
                </div>
            </div>
            """, unsafe_allow_html=True)
        
        with col2:
            st.markdown(f"""
            <div class='live-metric animated' style='animation-delay: 0.1s;'>
                <div class='metric-label-live'>고가</div>
                <div class='metric-value-live'>{high_price:,.0f}</div>
                <div class='metric-change-live' style='color: #00ff88;'>
                    +{((high_price - prev_close) / prev_close * 100):,.2f}%
                </div>
            </div>
            """, unsafe_allow_html=True)
        
        with col3:
            st.markdown(f"""
            <div class='live-metric animated' style='animation-delay: 0.2s;'>
                <div class='metric-label-live'>저가</div>
                <div class='metric-value-live'>{low_price:,.0f}</div>
                <div class='metric-change-live' style='color: #ff0055;'>
                    {((low_price - prev_close) / prev_close * 100):,.2f}%
                </div>
            </div>
            """, unsafe_allow_html=True)
        
        with col4:
            volume_ratio = (volume / avg_volume) * 100
            st.markdown(f"""
            <div class='live-metric animated' style='animation-delay: 0.3s;'>
                <div class='metric-label-live'>거래량</div>
                <div class='metric-value-live' style='font-size: 2rem;'>{volume:,.0f}</div>
                <div class='metric-change-live'>
                    평균 대비 {volume_ratio:.0f}%
                </div>
            </div>
            """, unsafe_allow_html=True)
    
    # 가격 차트
    with chart_placeholder.container():
        st.markdown('<div class="chart-container-live animated">', unsafe_allow_html=True)
        
        fig = go.Figure()
        
        if chart_type == "캔들스틱":
            fig.add_trace(go.Candlestick(
                x=df.index,
                open=df['Open'],
                high=df['High'],
                low=df['Low'],
                close=df['Close'],
                name='가격',
                increasing_line_color='#00ff88',
                decreasing_line_color='#ff0055'
            ))
        elif chart_type == "라인":
            fig.add_trace(go.Scatter(
                x=df.index,
                y=df['Close'],
                mode='lines',
                name='종가',
                line=dict(color='#667eea', width=3)
            ))
        elif chart_type == "영역":
            fig.add_trace(go.Scatter(
                x=df.index,
                y=df['Close'],
                fill='tozeroy',
                name='종가',
                line=dict(color='#667eea', width=2),
                fillcolor='rgba(102, 126, 234, 0.3)'
            ))
        else:  # OHLC
            fig.add_trace(go.Ohlc(
                x=df.index,
                open=df['Open'],
                high=df['High'],
                low=df['Low'],
                close=df['Close'],
                name='가격'
            ))
        
        # 이동평균선 추가
        if show_indicators and len(df) >= 20:
            ma5 = df['Close'].rolling(window=5).mean()
            ma20 = df['Close'].rolling(window=20).mean()
            
            fig.add_trace(go.Scatter(
                x=df.index, y=ma5,
                mode='lines',
                name='MA(5)',
                line=dict(color='orange', width=1, dash='dot')
            ))
            
            fig.add_trace(go.Scatter(
                x=df.index, y=ma20,
                mode='lines',
                name='MA(20)',
                line=dict(color='purple', width=1, dash='dot')
            ))
        
        fig.update_layout(
            title=f'🔴 LIVE - {ticker} 실시간 가격 차트',
            xaxis_title='시간',
            yaxis_title='가격',
            height=500,
            hovermode='x unified',
            template='plotly_white',
            xaxis_rangeslider_visible=False,
            plot_bgcolor='rgba(0,0,0,0.02)'
        )
        
        st.plotly_chart(fig, use_container_width=True)
        st.markdown('</div>', unsafe_allow_html=True)
    
    # 거래량 차트
    if show_volume:
        with volume_placeholder.container():
            st.markdown('<div class="chart-container-live animated">', unsafe_allow_html=True)
            
            fig_volume = go.Figure()
            
            colors = ['#ff0055' if df['Open'].iloc[i] > df['Close'].iloc[i] else '#00ff88' 
                     for i in range(len(df))]
            
            fig_volume.add_trace(go.Bar(
                x=df.index,
                y=df['Volume'],
                name='거래량',
                marker_color=colors,
                marker_line_width=0
            ))
            
            fig_volume.update_layout(
                title='📊 실시간 거래량',
                xaxis_title='시간',
                yaxis_title='거래량',
                height=250,
                template='plotly_white',
                showlegend=False,
                plot_bgcolor='rgba(0,0,0,0.02)'
            )
            
            st.plotly_chart(fig_volume, use_container_width=True)
            st.markdown('</div>', unsafe_allow_html=True)
    
    # 기술적 지표
    if show_indicators and len(df) >= 20:
        with indicators_placeholder.container():
            col1, col2, col3 = st.columns(3)
            
            with col1:
                st.markdown("### 📊 이동평균")
                ma5 = df['Close'].rolling(window=5).mean().iloc[-1]
                ma20 = df['Close'].rolling(window=20).mean().iloc[-1]
                
                st.metric("MA(5)", f"{ma5:,.0f}", f"{((current_price - ma5) / ma5 * 100):+.2f}%")
                st.metric("MA(20)", f"{ma20:,.0f}", f"{((current_price - ma20) / ma20 * 100):+.2f}%")
            
            with col2:
                st.markdown("### 📈 변동성")
                volatility = df['Close'].pct_change().std() * 100
                st.metric("변동성", f"{volatility:.2f}%")
                
                intraday_range = ((high_price - low_price) / low_price) * 100
                st.metric("일중 변동폭", f"{intraday_range:.2f}%")
            
            with col3:
                st.markdown("### 🎯 RSI")
                if len(df) >= 14:
                    delta = df['Close'].diff()
                    gain = (delta.where(delta > 0, 0)).rolling(window=14).mean()
                    loss = (-delta.where(delta < 0, 0)).rolling(window=14).mean()
                    rs = gain / loss
                    rsi = 100 - (100 / (1 + rs))
                    rsi_value = rsi.iloc[-1]
                    
                    rsi_status = "과매수" if rsi_value > 70 else "과매도" if rsi_value < 30 else "중립"
                    rsi_color = "🔴" if rsi_value > 70 else "🟢" if rsi_value < 30 else "🟡"
                    
                    st.metric("RSI(14)", f"{rsi_value:.1f}", f"{rsi_color} {rsi_status}")
    
    # 업데이트 정보
    with footer_placeholder.container():
        st.markdown(f"""
        <div class='update-info'>
            <div class='progress-bar'></div>
            <p style='margin: 10px 0 0 0;'>
                🕐 마지막 업데이트: <strong>{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</strong> | 
                🔄 업데이트 횟수: <strong>{st.session_state.update_count}회</strong> |
                {f"⏱️ 다음 갱신: <strong>{refresh_interval}초 후</strong>" if auto_refresh else "⏸️ 자동 갱신 정지됨"}
            </p>
        </div>
        """, unsafe_allow_html=True)
    
    # 세션 상태 업데이트
    st.session_state.last_update = datetime.now()
    st.session_state.update_count += 1

# 대시보드 업데이트 실행
update_dashboard()

# 자동 갱신
if auto_refresh:
    time.sleep(refresh_interval)
    st.rerun()
