"""
통합 실시간 주식 모니터링 - 삼성전자, 현대차, 테슬라, S&P500
실시간 1분 단위 차트 + 매매 신호 시스템
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

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from data.fetcher import StockDataFetcher

# 페이지 설정
st.set_page_config(
    page_title="통합 실시간 - AI 주식 도우미",
    page_icon="📊",
    layout="wide",
    initial_sidebar_state="collapsed"
)

# 커스텀 CSS
st.markdown("""
<style>
    /* 전체 배경 */
    .stApp {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    /* 헤더 */
    .main-header {
        text-align: center;
        color: white;
        padding: 20px;
        font-size: 2.5rem;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    /* 카드 스타일 */
    .stock-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        margin-bottom: 20px;
    }
    
    /* 매매 신호 배지 */
    .signal-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 1.1rem;
        margin: 10px 0;
    }
    
    .signal-buy {
        background: #4caf50;
        color: white;
    }
    
    .signal-sell {
        background: #f44336;
        color: white;
    }
    
    .signal-hold {
        background: #ff9800;
        color: white;
    }
    
    /* 가격 표시 */
    .price-display {
        font-size: 2rem;
        font-weight: bold;
        color: #1976d2;
        margin: 10px 0;
    }
    
    /* 변동률 */
    .change-positive {
        color: #d32f2f;
        font-weight: bold;
    }
    
    .change-negative {
        color: #1976d2;
        font-weight: bold;
    }
    
    /* 입력 필드 그룹 */
    .input-group {
        background: #f5f5f5;
        padding: 15px;
        border-radius: 10px;
        margin: 10px 0;
    }
    
    /* 버튼 스타일 */
    .stButton > button {
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: bold;
        font-size: 1rem;
    }
    
    .stButton > button:hover {
        opacity: 0.9;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
</style>
""", unsafe_allow_html=True)

# 헤더
st.markdown('<div class="main-header">📊 통합 실시간 주식 모니터링</div>', unsafe_allow_html=True)
st.markdown('<p style="text-align: center; color: white; font-size: 1.2rem;">실시간 1분 단위 차트 + 3일 연속 추세 분석 + 자동 매매 신호</p>', unsafe_allow_html=True)

# 감시 대상 주식
STOCKS = [
    {"ticker": "005930.KQ", "name": "삼성전자", "icon": "📱", "market": "KR"},
    {"ticker": "005380.KS", "name": "현대차", "icon": "🚗", "market": "KR"},
    {"ticker": "TSLA", "name": "테슬라", "icon": "⚡", "market": "US"},
    {"ticker": "^GSPC", "name": "S&P 500", "icon": "📈", "market": "US"}
]

# 세션 상태 초기화
if 'last_update' not in st.session_state:
    st.session_state.last_update = None
if 'portfolio' not in st.session_state:
    st.session_state.portfolio = {stock['ticker']: {'shares': 0, 'avg_price': 0} for stock in STOCKS}

# 자동 새로고침 설정
auto_refresh = st.checkbox("🔄 자동 새로고침 (30초)", value=True)

def calculate_3day_trend(df):
    """3일 연속 추세 판단"""
    if len(df) < 3:
        return "INSUFFICIENT_DATA", "데이터 부족", "⚠️"
    
    # 최근 3일 종가
    recent_3 = df['Close'].tail(3).values
    
    # 3일 연속 상승
    if recent_3[2] > recent_3[1] > recent_3[0]:
        return "BUY", "3일 연속 상승 - 매수 신호", "🟢"
    
    # 3일 연속 하락
    elif recent_3[2] < recent_3[1] < recent_3[0]:
        return "SELL", "3일 연속 하락 - 매도 신호", "🔴"
    
    # 그 외
    else:
        return "HOLD", "관망", "🟡"

def get_realtime_data(ticker, market):
    """실시간 데이터 가져오기"""
    try:
        fetcher = StockDataFetcher()
        
        # 최근 30일 데이터 (3일 추세 계산용)
        start_date = (datetime.now() - timedelta(days=30)).strftime("%Y-%m-%d")
        df = fetcher.get_stock_data(ticker, start_date=start_date)
        
        if df is None or len(df) == 0:
            return None
        
        # 실시간 가격
        realtime_info = fetcher.get_realtime_price(ticker)
        current_price = realtime_info.get('price', df['Close'].iloc[-1])
        is_realtime = realtime_info.get('is_realtime', False)
        
        # 전일 대비
        prev_close = df['Close'].iloc[-2] if len(df) > 1 else current_price
        change = current_price - prev_close
        change_pct = (change / prev_close * 100) if prev_close > 0 else 0
        
        # 3일 추세
        trend_signal, trend_reason, trend_icon = calculate_3day_trend(df)
        
        return {
            'df': df,
            'current_price': current_price,
            'is_realtime': is_realtime,
            'change': change,
            'change_pct': change_pct,
            'trend_signal': trend_signal,
            'trend_reason': trend_reason,
            'trend_icon': trend_icon
        }
    except Exception as e:
        st.error(f"❌ 데이터 가져오기 실패: {str(e)}")
        return None

def create_mini_chart(df, current_price, ticker_name):
    """1분 단위 미니 차트 생성"""
    # 최근 60개 데이터 포인트 (약 1시간)
    recent_df = df.tail(60)
    
    fig = go.Figure()
    
    # 캔들스틱 차트
    fig.add_trace(go.Candlestick(
        x=recent_df.index,
        open=recent_df['Open'],
        high=recent_df['High'],
        low=recent_df['Low'],
        close=recent_df['Close'],
        name=ticker_name,
        increasing_line_color='#d32f2f',
        decreasing_line_color='#1976d2'
    ))
    
    # 실시간 가격 마커
    fig.add_trace(go.Scatter(
        x=[recent_df.index[-1]],
        y=[current_price],
        mode='markers+text',
        marker=dict(size=15, color='#ff0000', symbol='star'),
        text=[f'{current_price:,.0f}'],
        textposition='top center',
        name='실시간 가격',
        showlegend=False
    ))
    
    # 레이아웃
    fig.update_layout(
        height=250,
        margin=dict(l=10, r=10, t=10, b=10),
        xaxis=dict(showgrid=False),
        yaxis=dict(showgrid=True, gridcolor='#f0f0f0'),
        plot_bgcolor='white',
        paper_bgcolor='white',
        xaxis_rangeslider_visible=False
    )
    
    return fig

# 메인 대시보드
for stock in STOCKS:
    ticker = stock['ticker']
    name = stock['name']
    icon = stock['icon']
    market = stock['market']
    
    st.markdown(f'<div class="stock-card">', unsafe_allow_html=True)
    
    # 헤더
    col1, col2, col3 = st.columns([2, 3, 2])
    
    with col1:
        st.markdown(f"## {icon} {name}")
        st.caption(f"티커: {ticker}")
    
    # 데이터 로드
    data = get_realtime_data(ticker, market)
    
    if data:
        with col2:
            # 현재가 표시
            is_korean = market == "KR"
            price_text = f"{data['current_price']:,.0f}{'원' if is_korean else '$'}"
            st.markdown(f'<div class="price-display">{price_text}</div>', unsafe_allow_html=True)
            
            # 변동률
            change_class = "change-positive" if data['change'] >= 0 else "change-negative"
            change_icon = "▲" if data['change'] >= 0 else "▼"
            st.markdown(
                f'<span class="{change_class}">{change_icon} {abs(data["change"]):,.2f} ({abs(data["change_pct"]):.2f}%)</span>',
                unsafe_allow_html=True
            )
            
            # 실시간 여부
            if data['is_realtime']:
                st.success("🟢 실시간 데이터")
            else:
                st.warning("🟡 15-20분 지연")
        
        with col3:
            # 3일 추세 신호
            signal = data['trend_signal']
            signal_class = "signal-buy" if signal == "BUY" else "signal-sell" if signal == "SELL" else "signal-hold"
            st.markdown(
                f'<div class="signal-badge {signal_class}">{data["trend_icon"]} {signal}</div>',
                unsafe_allow_html=True
            )
            st.caption(data['trend_reason'])
        
        # 차트
        st.markdown("### 📊 실시간 차트 (최근 1시간)")
        chart = create_mini_chart(data['df'], data['current_price'], name)
        st.plotly_chart(chart, use_container_width=True)
        
        # 매매 입력 섹션
        st.markdown("### 💰 매매 관리")
        
        col_buy, col_sell = st.columns(2)
        
        with col_buy:
            st.markdown('<div class="input-group">', unsafe_allow_html=True)
            st.markdown("**🟢 매수**")
            
            buy_shares = st.number_input(
                "매수 수량",
                min_value=0,
                value=0,
                step=1,
                key=f"buy_shares_{ticker}"
            )
            
            if st.button(f"매수 실행", key=f"buy_btn_{ticker}"):
                if buy_shares > 0:
                    # 포트폴리오 업데이트
                    current = st.session_state.portfolio[ticker]
                    total_shares = current['shares'] + buy_shares
                    total_cost = (current['shares'] * current['avg_price']) + (buy_shares * data['current_price'])
                    new_avg_price = total_cost / total_shares if total_shares > 0 else 0
                    
                    st.session_state.portfolio[ticker] = {
                        'shares': total_shares,
                        'avg_price': new_avg_price
                    }
                    
                    st.success(f"✅ {buy_shares}주 매수 완료! (가격: {data['current_price']:,.2f})")
                    st.rerun()
                else:
                    st.error("수량을 입력하세요")
            
            st.markdown('</div>', unsafe_allow_html=True)
        
        with col_sell:
            st.markdown('<div class="input-group">', unsafe_allow_html=True)
            st.markdown("**🔴 매도**")
            
            current_holding = st.session_state.portfolio[ticker]['shares']
            
            sell_shares = st.number_input(
                f"매도 수량 (보유: {current_holding}주)",
                min_value=0,
                max_value=current_holding,
                value=0,
                step=1,
                key=f"sell_shares_{ticker}"
            )
            
            if st.button(f"매도 실행", key=f"sell_btn_{ticker}"):
                if sell_shares > 0 and sell_shares <= current_holding:
                    # 포트폴리오 업데이트
                    current = st.session_state.portfolio[ticker]
                    new_shares = current['shares'] - sell_shares
                    
                    # 수익 계산
                    profit = (data['current_price'] - current['avg_price']) * sell_shares
                    profit_pct = (profit / (current['avg_price'] * sell_shares) * 100) if current['avg_price'] > 0 else 0
                    
                    st.session_state.portfolio[ticker] = {
                        'shares': new_shares,
                        'avg_price': current['avg_price'] if new_shares > 0 else 0
                    }
                    
                    profit_icon = "📈" if profit >= 0 else "📉"
                    st.success(f"✅ {sell_shares}주 매도 완료! {profit_icon} 수익: {profit:,.2f} ({profit_pct:+.2f}%)")
                    st.rerun()
                elif sell_shares > current_holding:
                    st.error("보유 수량을 초과했습니다")
                else:
                    st.error("수량을 입력하세요")
            
            st.markdown('</div>', unsafe_allow_html=True)
        
        # 현재 보유 현황
        if current_holding > 0:
            avg_price = st.session_state.portfolio[ticker]['avg_price']
            current_value = current_holding * data['current_price']
            total_cost = current_holding * avg_price
            unrealized_profit = current_value - total_cost
            unrealized_pct = (unrealized_profit / total_cost * 100) if total_cost > 0 else 0
            
            st.markdown("---")
            st.markdown("### 📊 보유 현황")
            
            col1, col2, col3, col4 = st.columns(4)
            col1.metric("보유 수량", f"{current_holding}주")
            col2.metric("평균 매수가", f"{avg_price:,.2f}")
            col3.metric("현재 평가액", f"{current_value:,.2f}")
            col4.metric("평가 손익", f"{unrealized_profit:,.2f}", f"{unrealized_pct:+.2f}%")
    
    else:
        st.error("❌ 데이터를 불러올 수 없습니다")
    
    st.markdown('</div>', unsafe_allow_html=True)

# 하단 정보
st.markdown("---")
st.markdown('<p style="text-align: center; color: white;">⏰ 마지막 업데이트: {}</p>'.format(
    datetime.now().strftime("%Y-%m-%d %H:%M:%S")
), unsafe_allow_html=True)

# 자동 새로고침
if auto_refresh:
    time.sleep(30)
    st.rerun()
