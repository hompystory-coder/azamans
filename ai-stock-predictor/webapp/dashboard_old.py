"""
AI 주식 분석 & 예측 시스템 - 초보자 친화 UI
젊고 직관적이며 쉽게 이해할 수 있는 디자인
"""

import streamlit as st
import pandas as pd
import numpy as np
import plotly.graph_objects as go
import plotly.express as px
from datetime import datetime, timedelta
import sys
import os

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from data.fetcher import StockDataFetcher, POPULAR_STOCKS, search_ticker
from data.indicators import TechnicalIndicators
from models.lstm_model import StockPricePredictor
from analysis.recommender import StockRecommender
from analysis.pattern_analyzer import AdvancedPatternAnalyzer
from analysis.backtesting import BacktestingEngine
from data.external_sources import ExternalDataCollector
from models.enhanced_predictor import EnhancedPredictor

# ===========================
# 페이지 설정
# ===========================
st.set_page_config(
    page_title="AI 주식 도우미 🚀",
    page_icon="🚀",
    layout="wide",
    initial_sidebar_state="expanded",
    menu_items={
        'Get Help': 'https://stock.neuralgrid.kr',
        'About': "AI 주식 도우미 v2.0 - 초보자를 위한 쉬운 주식 분석!"
    }
)

# ===========================
# 커스텀 CSS - 밝고 친근한 디자인
# ===========================
st.markdown("""
<style>
    /* 전체 배경 - 생동감 있는 그라디언트 애니메이션 */
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .stApp {
        background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
    }
    
    /* 메인 컨테이너 */
    .main .block-container {
        padding: 1.5rem 2rem;
        max-width: 1400px;
    }
    
    /* 헤더 카드 스타일 */
    .welcome-card {
        background: white;
        padding: 2rem;
        border-radius: 25px;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        border: 3px solid rgba(255, 255, 255, 0.8);
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .welcome-title {
        font-size: 3rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        animation: pulse 3s ease-in-out infinite;
        text-shadow: 0 0 30px rgba(102, 126, 234, 0.3);
    }
    
    .welcome-subtitle {
        font-size: 1.2rem;
        color: #666;
        margin-top: 1rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .welcome-emoji {
        font-size: 4rem;
        display: block;
        margin: 1rem 0;
        animation: bounce 2s ease-in-out infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* 도움말 팁 박스 - 더 눈에 띄게 */
    .help-tip {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 20px;
        margin: 1.5rem 0;
        font-size: 1rem;
        box-shadow: 0 8px 25px rgba(245, 87, 108, 0.4);
        border: 3px solid rgba(255, 255, 255, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .help-tip::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        animation: shine 3s infinite;
    }
    
    @keyframes shine {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    .help-tip strong {
        font-size: 1.1rem;
    }
    
    /* 카드 스타일 - 밝고 깔끔 */
    .info-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 2px solid rgba(102, 126, 234, 0.2);
        height: 100%;
    }
    
    .info-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        border-color: rgba(102, 126, 234, 0.5);
    }
    
    /* 아이콘 스타일 */
    .icon-emoji {
        font-size: 3rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    /* 숫자 강조 */
    .big-number {
        font-size: 3rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0.5rem 0;
        animation: numberPop 0.5s ease-out;
    }
    
    @keyframes numberPop {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .card-label {
        font-size: 0.9rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    /* 버튼 스타일 - 큰 버튼, 쉽게 클릭 */
    .stButton>button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border: none;
        border-radius: 15px;
        padding: 1rem 2rem;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        width: 100%;
    }
    
    .stButton>button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.6);
    }
    
    /* 사이드바 - 밝고 친근 */
    .css-1d391kg, [data-testid="stSidebar"] {
        background: linear-gradient(180deg, #ffffff 0%, #f5f7fa 100%);
    }
    
    [data-testid="stSidebar"] {
        border-right: 3px solid rgba(102, 126, 234, 0.2);
    }
    
    /* 입력 필드 - 크고 명확 */
    .stTextInput>div>div>input {
        background: white;
        border: 3px solid rgba(102, 126, 234, 0.3);
        border-radius: 15px;
        color: #333;
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .stTextInput>div>div>input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* 탭 스타일 */
    .stTabs [data-baseweb="tab-list"] {
        gap: 10px;
        background: white;
        border-radius: 15px;
        padding: 10px;
    }
    
    .stTabs [data-baseweb="tab"] {
        background: #f5f7fa;
        border-radius: 10px;
        padding: 12px 24px;
        color: #666;
        border: none;
        font-weight: 600;
    }
    
    .stTabs [aria-selected="true"] {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
    }
    
    /* 신호 배지 - 크고 명확하고 반짝거림 */
    .signal-badge {
        padding: 1.5rem 3rem;
        border-radius: 25px;
        font-weight: 800;
        font-size: 1.5rem;
        display: inline-block;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        margin: 1.5rem 0;
        position: relative;
        animation: badgePulse 2s ease-in-out infinite;
        border: 3px solid rgba(255, 255, 255, 0.3);
    }
    
    @keyframes badgePulse {
        0%, 100% { box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2); }
        50% { box-shadow: 0 8px 40px rgba(0, 0, 0, 0.4), 0 0 30px currentColor; }
    }
    
    .signal-buy {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .signal-sell {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        color: white;
    }
    
    .signal-hold {
        background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
        color: white;
    }
    
    /* 설명 텍스트 */
    .explain-text {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 10px;
        border-left: 4px solid #667eea;
        margin: 1rem 0;
        color: #333;
    }
    
    /* 애니메이션 */
    @keyframes bounceIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    .animate-in {
        animation: bounceIn 0.6s ease-out;
    }
    
    /* 진행 바 */
    .stProgress > div > div > div {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    /* 메트릭 스타일 */
    .stMetric {
        background: white;
        padding: 1rem;
        border-radius: 15px;
        border: 2px solid rgba(102, 126, 234, 0.2);
    }
    
    .stMetric label {
        color: #666 !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
    }
    
    /* 알림 박스 */
    .stAlert {
        border-radius: 15px;
        border: none;
        padding: 1.5rem;
    }
    
    /* 종목 카드 */
    .stock-card {
        background: white;
        padding: 1.5rem;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .stock-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        border-color: #667eea;
    }
    
    .stock-ticker {
        font-size: 1.5rem;
        font-weight: 800;
        color: #333;
        margin: 0;
    }
    
    .stock-name {
        font-size: 0.9rem;
        color: #888;
        margin: 0.3rem 0;
    }
    
    .stock-change {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }
</style>
""", unsafe_allow_html=True)

# ===========================
# 캐시 함수
# ===========================
@st.cache_data(ttl=3600)
def load_stock_data(ticker, start_date):
    fetcher = StockDataFetcher()
    return fetcher.get_stock_data(ticker, start_date)

@st.cache_data(ttl=3600)
def get_stock_info(ticker):
    fetcher = StockDataFetcher()
    return fetcher.get_stock_info(ticker)

@st.cache_data(ttl=3600)
def get_recommendations(market, top_n):
    recommender = StockRecommender()
    return recommender.get_top_recommendations(market, top_n)

# ===========================
# 차트 함수 - 초보자 친화적
# ===========================
def plot_stock_chart_simple(df, predictions=None, ticker=""):
    """초보자용 간단한 주가 차트 - 개선 버전"""
    fig = go.Figure()
    
    # 1. 실제 주가 (더 선명하게)
    fig.add_trace(go.Scatter(
        x=df.index,
        y=df['Close'],
        name='📊 실제 가격',
        line=dict(color='#10b981', width=4),
        mode='lines',
        fill='tozeroy',
        fillcolor='rgba(16,185,129,0.08)',
        hovertemplate='<b>실제 가격</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
    ))
    
    # 2. 이동평균선 추가 (20일)
    if 'MA20' in df.columns:
        fig.add_trace(go.Scatter(
            x=df.index,
            y=df['MA20'],
            name='📈 추세선 (20일 평균)',
            line=dict(color='#f59e0b', width=3, dash='dot'),
            mode='lines',
            hovertemplate='<b>20일 이동평균</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
        ))
    
    # 3. AI 예측 (있는 경우)
    if predictions is not None and len(predictions) > 0:
        # 미래 날짜 생성
        last_date = df.index[-1]
        future_dates = pd.date_range(
            start=last_date + pd.Timedelta(days=1),
            periods=len(predictions)
        )
        
        # 연결선
        fig.add_trace(go.Scatter(
            x=[last_date, future_dates[0]],
            y=[df['Close'].iloc[-1], predictions[0]],
            mode='lines',
            line=dict(color='#8b5cf6', width=2, dash='dot'),
            showlegend=False,
            hoverinfo='skip'
        ))
        
        # AI 예측선
        fig.add_trace(go.Scatter(
            x=future_dates,
            y=predictions,
            name='🤖 AI 예측 (30일 후)',
            line=dict(color='#8b5cf6', width=5, dash='dash'),
            mode='lines+markers',
            marker=dict(size=8, symbol='star', color='#8b5cf6', line=dict(width=2, color='white')),
            fill='tozeroy',
            fillcolor='rgba(139,92,246,0.08)',
            hovertemplate='<b>AI 예측</b><br>날짜: %{x}<br>예측 가격: $%{y:.2f}<extra></extra>'
        ))
        
        # 현재가 마커
        fig.add_trace(go.Scatter(
            x=[last_date],
            y=[df['Close'].iloc[-1]],
            mode='markers+text',
            marker=dict(size=16, color='#ef4444', symbol='circle', line=dict(width=3, color='white')),
            text=['현재'],
            textposition='top center',
            textfont=dict(size=13, color='#ef4444', family='Arial Black'),
            name='현재가',
            showlegend=False,
            hovertemplate='<b>현재가</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
        ))
    
    # 레이아웃
    title_text = f'<b>📈 {ticker} 주가 흐름</b>'
    if predictions is not None and len(predictions) > 0:
        change = ((predictions[-1] - df['Close'].iloc[-1]) / df['Close'].iloc[-1]) * 100
        change_emoji = '📈' if change > 0 else '📉'
        change_color = '#10b981' if change > 0 else '#ef4444'
        title_text += f'<br><sup style="font-size: 14px;">30일 후 예상: <span style="color:{change_color}"><b>{change_emoji} {change:+.1f}%</b></span></sup>'
    
    fig.update_layout(
        title=dict(
            text=title_text,
            font=dict(size=24, color='#1f2937', family='Arial, sans-serif'),
            x=0.5,
            xanchor='center'
        ),
        paper_bgcolor='white',
        plot_bgcolor='rgba(248,250,252,0.8)',
        font=dict(color='#1f2937', size=13, family='Arial, sans-serif'),
        xaxis=dict(
            title='<b>📅 날짜</b>',
            gridcolor='rgba(0,0,0,0.08)',
            showgrid=True,
            zeroline=False,
            tickformat='%Y-%m-%d',
            title_font=dict(size=15, color='#4b5563')
        ),
        yaxis=dict(
            title='<b>💰 가격 ($)</b>',
            gridcolor='rgba(0,0,0,0.08)',
            showgrid=True,
            zeroline=False,
            tickformat='$,.2f',
            title_font=dict(size=15, color='#4b5563')
        ),
        height=550,
        hovermode='x unified',
        xaxis_rangeslider_visible=False,
        hoverlabel=dict(
            bgcolor='white',
            font_size=13,
            font_family='Arial',
            bordercolor='rgba(0,0,0,0.1)'
        ),
        legend=dict(
            orientation='h',
            yanchor='bottom',
            y=-0.2,
            xanchor='center',
            x=0.5,
            bgcolor='rgba(255,255,255,0.95)',
            bordercolor='rgba(0,0,0,0.1)',
            borderwidth=1,
            font=dict(size=12)
        ),
        margin=dict(t=100, b=100, l=80, r=40)
    )
    
    return fig
    """초보자용 주가 차트 - 단순하고 명확"""
    fig = go.Figure()
    
    # 실제 가격 선 (캔들스틱 대신 라인)
    fig.add_trace(go.Scatter(
        x=df.index,
        y=df['Close'],
        name='📊 실제 가격',
        line=dict(color='#667eea', width=3),
        fill='tozeroy',
        fillcolor='rgba(102, 126, 234, 0.1)'
    ))
    
    # 이동평균선 (추세선)
    if 'MA20' in df.columns:
        fig.add_trace(go.Scatter(
            x=df.index,
            y=df['MA20'],
            name='📈 추세선 (20일)',
            line=dict(color='#ffa500', width=2, dash='dot')
        ))
    
    # AI 예측
    if predictions is not None and len(predictions) > 0:
        last_date = df.index[-1]
        future_dates = pd.date_range(
            start=last_date + timedelta(days=1),
            periods=len(predictions),
            freq='D'
        )
        
        fig.add_trace(go.Scatter(
            x=future_dates,
            y=predictions,
            name='🤖 AI 예측 (30일 후)',
            line=dict(color='#ff006e', width=4, dash='dash'),
            mode='lines+markers',
            marker=dict(size=8, symbol='star', color='#ff006e')
        ))
    
    fig.update_layout(
        title=dict(
            text=f'<b>{ticker}</b> 주가 흐름',
            font=dict(size=26, color='#333', family='Arial Black')
        ),
        paper_bgcolor='white',
        plot_bgcolor='#f8f9fa',
        font=dict(color='#333', size=14, family='Arial'),
        xaxis=dict(
            title='📅 날짜',
            gridcolor='#e1e4e8',
            showgrid=True,
            title_font=dict(size=16, color='#666')
        ),
        yaxis=dict(
            title='💰 가격 ($)',
            gridcolor='#e1e4e8',
            showgrid=True,
            title_font=dict(size=16, color='#666')
        ),
        height=500,
        hovermode='x unified',
        xaxis_rangeslider_visible=False,
        legend=dict(
            bgcolor='white',
            bordercolor='#e1e4e8',
            borderwidth=2,
            font=dict(size=13)
        ),
        margin=dict(t=80, b=60, l=60, r=60)
    )
    
    return fig

def plot_backtest_comparison(backtest_results):
    """과거 예측 vs 실제 비교 그래프"""
    fig = go.Figure()
    
    if not backtest_results or not backtest_results.get('dates'):
        return None
    
    dates = backtest_results['dates']
    predictions = backtest_results['predictions']
    actuals = backtest_results['actuals']
    
    # 실제 가격
    fig.add_trace(go.Scatter(
        x=dates,
        y=actuals,
        name='✅ 실제 가격',
        line=dict(color='#10b981', width=4),
        mode='lines+markers',
        marker=dict(size=10, symbol='circle', color='#10b981')
    ))
    
    # AI 예측
    fig.add_trace(go.Scatter(
        x=dates,
        y=predictions,
        name='🤖 AI 예측',
        line=dict(color='#8b5cf6', width=4, dash='dash'),
        mode='lines+markers',
        marker=dict(size=10, symbol='star', color='#8b5cf6')
    ))
    
    # 정확도 표시
    accuracy = backtest_results.get('accuracy', 0)
    avg_error = backtest_results.get('avg_error', 0)
    
    fig.update_layout(
        title=dict(
            text=f'<b>📊 월별 예측 vs 실제 비교</b><br><sup>평균 정확도: {accuracy:.1f}% | 평균 오차: {avg_error:.1f}%</sup>',
            font=dict(size=24, color='#333', family='Arial Black'),
            x=0.5,
            xanchor='center'
        ),
        paper_bgcolor='white',
        plot_bgcolor='#f8f9fa',
        font=dict(color='#333', size=14, family='Arial'),
        xaxis=dict(
            title='📅 예측 시작 날짜',
            gridcolor='#e1e4e8',
            showgrid=True,
            title_font=dict(size=16, color='#666')
        ),
        yaxis=dict(
            title='💰 30일 후 가격',
            gridcolor='#e1e4e8',
            showgrid=True,
            title_font=dict(size=16, color='#666')
        ),
        height=500,
        hovermode='x unified',
        legend=dict(
            bgcolor='white',
            bordercolor='#e1e4e8',
            borderwidth=2,
            font=dict(size=14),
            x=0.01,
            y=0.99
        ),
        margin=dict(t=100, b=60, l=60, r=60)
    )
    
    return fig

def plot_3month_future(future_data, current_price):
    """3개월 미래 예측 그래프 (신뢰구간 포함) - 개선 버전"""
    fig = go.Figure()
    
    if not future_data or not future_data.get('dates'):
        return None
    
    dates = future_data['dates']
    prices = future_data['prices']
    upper = future_data['upper_bound']
    lower = future_data['lower_bound']
    confidence = future_data.get('confidence', 0)
    
    # 과거 30일 추가 (컨텍스트)
    last_30_dates = dates[-30:] if len(dates) >= 30 else dates
    
    # 1. 신뢰 구간 (더 선명한 색상)
    fig.add_trace(go.Scatter(
        x=list(dates) + list(dates[::-1]),
        y=list(upper) + list(lower[::-1]),
        fill='toself',
        fillcolor='rgba(139, 92, 246, 0.15)',
        line=dict(color='rgba(139, 92, 246, 0)'),
        name='📊 신뢰 구간 (±σ)',
        hoverinfo='skip',
        showlegend=True
    ))
    
    # 2. 하한선 (비관적)
    fig.add_trace(go.Scatter(
        x=dates,
        y=lower,
        name='📉 비관적 예상',
        line=dict(color='#ef4444', width=2, dash='dot'),
        mode='lines',
        hovertemplate='<b>최소 예상</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
    ))
    
    # 3. 상한선 (낙관적)
    fig.add_trace(go.Scatter(
        x=dates,
        y=upper,
        name='📈 낙관적 예상',
        line=dict(color='#10b981', width=2, dash='dot'),
        mode='lines',
        hovertemplate='<b>최대 예상</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
    ))
    
    # 4. 예측 가격 (메인 라인, 더 굵고 선명하게)
    fig.add_trace(go.Scatter(
        x=dates,
        y=prices,
        name='🚀 AI 예측 (중간값)',
        line=dict(color='#8b5cf6', width=5),
        mode='lines+markers',
        marker=dict(
            size=6,
            color='#8b5cf6',
            symbol='circle',
            line=dict(width=1, color='white')
        ),
        hovertemplate='<b>AI 예측</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
    ))
    
    # 5. 현재 가격선 (더 눈에 띄게)
    fig.add_hline(
        y=current_price,
        line_dash="solid",
        line_color="#fbbf24",
        line_width=3,
        annotation_text=f"📍 현재가: ${current_price:.2f}",
        annotation_position="left",
        annotation_font=dict(size=14, color='#f59e0b', family='Arial Black')
    )
    
    # 6. 시작점 마커
    fig.add_trace(go.Scatter(
        x=[dates[0]],
        y=[prices[0]],
        mode='markers+text',
        marker=dict(size=15, color='#3b82f6', symbol='circle', line=dict(width=3, color='white')),
        text=['시작'],
        textposition='top center',
        textfont=dict(size=12, color='#3b82f6', family='Arial Black'),
        name='시작점',
        showlegend=False,
        hovertemplate='<b>예측 시작</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
    ))
    
    # 7. 종료점 마커 (90일 후)
    fig.add_trace(go.Scatter(
        x=[dates[-1]],
        y=[prices[-1]],
        mode='markers+text',
        marker=dict(size=18, color='#8b5cf6', symbol='star', line=dict(width=3, color='white')),
        text=['90일 후'],
        textposition='top center',
        textfont=dict(size=13, color='#8b5cf6', family='Arial Black'),
        name='목표점',
        showlegend=False,
        hovertemplate=f'<b>90일 후 예측</b><br>날짜: %{{x}}<br>가격: $%{{y:.2f}}<extra></extra>'
    ))
    
    # 예상 수익률 계산 (안전 처리)
    if current_price > 0 and len(prices) > 0 and not np.isnan(prices[-1]) and not np.isinf(prices[-1]):
        expected_return = ((prices[-1] - current_price) / current_price) * 100
        if np.isinf(expected_return) or np.isnan(expected_return):
            expected_return = 0.0
    else:
        expected_return = 0.0
    
    return_color = '#10b981' if expected_return > 0 else '#ef4444'
    return_emoji = '📈' if expected_return > 0 else '📉'
    
    # 수익률 범위 계산
    if current_price > 0:
        upper_return = ((upper[-1] - current_price) / current_price) * 100
        lower_return = ((lower[-1] - current_price) / current_price) * 100
    else:
        upper_return = lower_return = 0.0
    
    fig.update_layout(
        title=dict(
            text=f'<b>🔮 3개월 (90일) 미래 예측</b><br>' +
                 f'<sup style="font-size: 14px;">' +
                 f'예상 수익률: <span style="color:{return_color}"><b>{return_emoji} {expected_return:+.1f}%</b></span> | ' +
                 f'범위: <span style="color:#ef4444">{lower_return:+.1f}%</span> ~ ' +
                 f'<span style="color:#10b981">{upper_return:+.1f}%</span> | ' +
                 f'신뢰도: <b>{confidence:.0f}%</b></sup>',
            font=dict(size=22, color='#1f2937', family='Arial, sans-serif'),
            x=0.5,
            xanchor='center'
        ),
        paper_bgcolor='white',
        plot_bgcolor='rgba(248,250,252,0.8)',
        font=dict(color='#1f2937', size=13, family='Arial, sans-serif'),
        xaxis=dict(
            title='<b>📅 미래 날짜</b>',
            gridcolor='rgba(0,0,0,0.08)',
            showgrid=True,
            zeroline=False,
            tickformat='%m/%d',
            title_font=dict(size=15, color='#4b5563')
        ),
        yaxis=dict(
            title='<b>💰 예상 가격 ($)</b>',
            gridcolor='rgba(0,0,0,0.08)',
            showgrid=True,
            zeroline=False,
            tickformat='$,.2f',
            title_font=dict(size=15, color='#4b5563')
        ),
        hovermode='x unified',
        hoverlabel=dict(
            bgcolor='white',
            font_size=13,
            font_family='Arial',
            bordercolor='rgba(0,0,0,0.1)'
        ),
        legend=dict(
            orientation='h',
            yanchor='bottom',
            y=-0.25,
            xanchor='center',
            x=0.5,
            bgcolor='rgba(255,255,255,0.95)',
            bordercolor='rgba(0,0,0,0.1)',
            borderwidth=1,
            font=dict(size=12)
        ),
        height=650,
        margin=dict(t=120, b=120, l=80, r=40)
    )
    
    return fig

def plot_rsi_simple(df):
    """초보자용 RSI 차트 - 설명 추가"""
    fig = go.Figure()
    
    if 'RSI' in df.columns:
        # RSI 선
        fig.add_trace(go.Scatter(
            x=df.index,
            y=df['RSI'],
            name='RSI 지수',
            line=dict(color='#667eea', width=3),
            fill='tozeroy',
            fillcolor='rgba(102, 126, 234, 0.2)'
        ))
        
        # 과매수 영역 (70 이상)
        fig.add_hrect(y0=70, y1=100, 
                     fillcolor="rgba(238, 9, 121, 0.1)", 
                     line_width=0,
                     annotation_text="🔴 과매수 (비싸요!)", 
                     annotation_position="top right")
        
        # 과매도 영역 (30 이하)
        fig.add_hrect(y0=0, y1=30,
                     fillcolor="rgba(17, 153, 142, 0.1)",
                     line_width=0,
                     annotation_text="🟢 과매도 (저렴해요!)",
                     annotation_position="bottom right")
        
        # 기준선
        fig.add_hline(y=50, line_dash="dash", line_color="#999", 
                     annotation_text="중립선", annotation_position="left")
    
    fig.update_layout(
        title=dict(
            text='<b>RSI</b> 지표 - 주식이 비싼지 저렴한지 알려줘요!',
            font=dict(size=20, color='#333')
        ),
        paper_bgcolor='white',
        plot_bgcolor='#f8f9fa',
        font=dict(color='#333'),
        xaxis=dict(title='날짜', gridcolor='#e1e4e8'),
        yaxis=dict(title='RSI 값', gridcolor='#e1e4e8', range=[0, 100]),
        height=350,
        hovermode='x unified',
        margin=dict(t=80, b=60, l=60, r=60)
    )
    
    return fig

def plot_backtest_comparison(backtest_results: dict, df: pd.DataFrame):
    """백테스팅 결과 비교 차트 - 개선 버전"""
    fig = go.Figure()
    
    # 1. 실제 주가 (더 진하고 선명하게)
    fig.add_trace(go.Scatter(
        x=df.index,
        y=df['Close'],
        name='📊 실제 주가',
        line=dict(color='#10b981', width=4),
        mode='lines',
        fill='tozeroy',
        fillcolor='rgba(16,185,129,0.05)',
        hovertemplate='<b>실제 주가</b><br>날짜: %{x}<br>가격: $%{y:.2f}<extra></extra>'
    ))
    
    # 2. 월별 예측 포인트 (더 명확하게)
    if backtest_results.get('prediction_points'):
        # 색상 팔레트
        colors = ['#8b5cf6', '#ec4899', '#f59e0b', '#3b82f6', '#ef4444', '#14b8a6']
        
        for i, point in enumerate(backtest_results['prediction_points'][-6:]):  # 최근 6개월
            start_date = point['start_date']
            
            if start_date in df.index:
                start_price = df.loc[start_date, 'Close']
                color = colors[i % len(colors)]
                
                # 예측 시작점 마커
                fig.add_trace(go.Scatter(
                    x=[start_date],
                    y=[start_price],
                    mode='markers',
                    marker=dict(size=12, color=color, symbol='circle', line=dict(width=2, color='white')),
                    name=f'시작점 {start_date.strftime("%m/%d")}',
                    showlegend=False,
                    hovertemplate=f'<b>예측 시작</b><br>날짜: {start_date.strftime("%Y-%m-%d")}<br>가격: ${start_price:.2f}<extra></extra>'
                ))
                
                # 예측선 (더 선명하게)
                fig.add_trace(go.Scatter(
                    x=point['dates'],
                    y=point['predicted'],
                    name=f'🤖 AI 예측 {start_date.strftime("%m/%d")}',
                    line=dict(color=color, width=3, dash='dash'),
                    mode='lines+markers',
                    marker=dict(size=6, symbol='star'),
                    opacity=0.8,
                    showlegend=(i < 3),  # 처음 3개만 범례에
                    hovertemplate=f'<b>AI 예측</b><br>날짜: %{{x}}<br>예측 가격: $%{{y:.2f}}<extra></extra>'
                ))
    
    # 레이아웃 (더 깔끔하게)
    fig.update_layout(
        title=dict(
            text='<b>📊 AI 예측 vs 실제 주가 비교</b><br><sup style="color: #6b7280;">과거 예측이 얼마나 정확했을까요?</sup>',
            font=dict(size=24, color='#1f2937', family='Arial, sans-serif'),
            x=0.5,
            xanchor='center'
        ),
        paper_bgcolor='white',
        plot_bgcolor='rgba(248,250,252,0.5)',
        font=dict(color='#1f2937', size=13, family='Arial, sans-serif'),
        xaxis=dict(
            title='<b>📅 날짜</b>',
            gridcolor='rgba(0,0,0,0.05)',
            showgrid=True,
            zeroline=False,
            tickformat='%Y-%m-%d'
        ),
        yaxis=dict(
            title='<b>💰 가격 ($)</b>',
            gridcolor='rgba(0,0,0,0.05)',
            showgrid=True,
            zeroline=False,
            tickformat='$,.2f'
        ),
        hovermode='x unified',
        hoverlabel=dict(
            bgcolor='white',
            font_size=13,
            font_family='Arial'
        ),
        legend=dict(
            orientation='h',
            yanchor='bottom',
            y=-0.2,
            xanchor='center',
            x=0.5,
            bgcolor='rgba(255,255,255,0.9)',
            bordercolor='rgba(0,0,0,0.1)',
            borderwidth=1
        ),
        height=600,
        margin=dict(t=100, b=100, l=80, r=40)
    )
    
    return fig

def plot_future_3months(df: pd.DataFrame, future_prediction: dict):
    """3개월 미래 예측 차트"""
    fig = go.Figure()
    
    # 과거 실제 데이터 (최근 3개월)
    recent_df = df.tail(90)
    fig.add_trace(go.Scatter(
        x=recent_df.index,
        y=recent_df['Close'],
        name='📊 과거 실제 가격',
        line=dict(color='#667eea', width=4),
        mode='lines'
    ))
    
    # 미래 예측
    if future_prediction and 'dates' in future_prediction:
        dates = future_prediction['dates']
        prices = future_prediction['prices']
        upper = future_prediction['upper_bound']
        lower = future_prediction['lower_bound']
        
        # 예측선
        fig.add_trace(go.Scatter(
            x=dates,
            y=prices,
            name='🔮 AI 예측 (3개월)',
            line=dict(color='#ff006e', width=4, dash='dash'),
            mode='lines+markers',
            marker=dict(size=6, symbol='star')
        ))
        
        # 신뢰 구간
        fig.add_trace(go.Scatter(
            x=dates + dates[::-1],
            y=upper + lower[::-1],
            fill='toself',
            fillcolor='rgba(255, 0, 110, 0.1)',
            line=dict(color='rgba(255,0,110,0)'),
            name='신뢰 구간',
            showlegend=True,
            hoverinfo='skip'
        ))
        
        # 상한선
        fig.add_trace(go.Scatter(
            x=dates,
            y=upper,
            name='📈 상한선 (낙관적)',
            line=dict(color='#00ff88', width=2, dash='dot'),
            mode='lines',
            opacity=0.5
        ))
        
        # 하한선
        fig.add_trace(go.Scatter(
            x=dates,
            y=lower,
            name='📉 하한선 (비관적)',
            line=dict(color='#ff0055', width=2, dash='dot'),
            mode='lines',
            opacity=0.5
        ))
    
    # 현재 시점 표시
    last_date = df.index[-1]
    last_price = df['Close'].iloc[-1]
    fig.add_vline(
        x=last_date, 
        line_dash="solid", 
        line_color="orange", 
        line_width=3,
        annotation_text="📍 현재",
        annotation_position="top"
    )
    
    fig.update_layout(
        title=dict(
            text='<b>3개월 미래 예측</b> - AI가 섬세하게 그린 미래',
            font=dict(size=26, color='#333', family='Arial Black')
        ),
        paper_bgcolor='white',
        plot_bgcolor='#f8f9fa',
        font=dict(color='#333', size=14),
        xaxis=dict(
            title='📅 날짜 (과거 → 미래)',
            gridcolor='#e1e4e8',
            showgrid=True,
            title_font=dict(size=16, color='#666')
        ),
        yaxis=dict(
            title='💰 가격 ($)',
            gridcolor='#e1e4e8',
            showgrid=True,
            title_font=dict(size=16, color='#666')
        ),
        height=650,
        hovermode='x unified',
        legend=dict(
            bgcolor='white',
            bordercolor='#e1e4e8',
            borderwidth=2,
            font=dict(size=13),
            x=0.01,
            y=0.99
        ),
        margin=dict(t=100, b=60, l=60, r=60)
    )
    
    return fig

# ===========================
# 메인 앱
# ===========================
def main():
    # 웰컴 헤더
    st.markdown("""
    <div class="welcome-card animate-in">
        <span class="welcome-emoji">🚀</span>
        <h1 class="welcome-title">AI 주식 도우미</h1>
        <p class="welcome-subtitle">초보자도 쉽게! AI가 주식을 분석해드려요</p>
        <p style="font-size: 0.9rem; color: #888; margin-top: 1rem;">
            📊 5년 데이터 분석 · 🤖 AI 예측 · 💡 쉬운 설명
        </p>
    </div>
    """, unsafe_allow_html=True)
    
    # 사이드바
    with st.sidebar:
        st.markdown("## 🎯 메뉴")
        page = st.radio(
            "",
            ["🏠 홈", "🔍 주식 찾기", "🤖 AI 추천", "📚 배우기"],
            label_visibility="collapsed"
        )
        
        st.markdown("---")
        st.markdown("## 🔥 인기 종목")
        quick_stocks = {
            "TSLA": "테슬라",
            "AAPL": "애플",
            "MSFT": "마이크로소프트",
            "005930.KS": "삼성전자",
            "035420.KS": "네이버"
        }
        selected_quick = st.selectbox("", list(quick_stocks.keys()), 
                                     format_func=lambda x: f"{x} ({quick_stocks[x]})")
        
        st.markdown("---")
        st.markdown("## ⏰ 기간")
        date_options = {
            "1개월": 30,
            "3개월": 90,
            "6개월": 180,
            "1년": 365,
            "5년": 1825
        }
        date_range = st.selectbox("", list(date_options.keys()), index=4)
    
    # ===========================
    # 페이지: 홈
    # ===========================
    if page == "🏠 홈":
        st.markdown('<div class="animate-in">', unsafe_allow_html=True)
        
        # 도움말
        st.markdown("""
        <div class="help-tip">
            <strong style="font-size: 1.3rem;">💡 이 사이트는 뭐하는 곳인가요?</strong><br><br>
            ✨ AI가 과거 5년간의 주식 데이터를 분석해서, 앞으로 30일 뒤 가격을 예측해줘요!<br><br>
            🎯 <b>쉽게 말하면:</b> "이 주식 사야할까?" 고민될 때 AI가 도와주는 곳이에요 😊<br><br>
            👉 <b>완전 무료</b>이고, <b>회원가입도 필요 없어요</b>!
        </div>
        """, unsafe_allow_html=True)
        
        # 주요 기능 카드
        st.markdown("### 🎁 어떤 기능이 있나요?")
        
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            st.markdown("""
            <div class="info-card">
                <span class="icon-emoji">🔍</span>
                <p class="card-label">기능 1</p>
                <h3 style="color: #333; margin: 0.5rem 0;">주식 검색</h3>
                <p style="color: #666; font-size: 0.9rem;">
                    테슬라, 애플, 삼성전자 등<br>어떤 주식이든 검색!
                </p>
            </div>
            """, unsafe_allow_html=True)
        
        with col2:
            st.markdown("""
            <div class="info-card">
                <span class="icon-emoji">📊</span>
                <p class="card-label">기능 2</p>
                <h3 style="color: #333; margin: 0.5rem 0;">과거 분석</h3>
                <p style="color: #666; font-size: 0.9rem;">
                    지난 5년 동안<br>가격이 어떻게 변했는지!
                </p>
            </div>
            """, unsafe_allow_html=True)
        
        with col3:
            st.markdown("""
            <div class="info-card">
                <span class="icon-emoji">🤖</span>
                <p class="card-label">기능 3</p>
                <h3 style="color: #333; margin: 0.5rem 0;">AI 예측</h3>
                <p style="color: #666; font-size: 0.9rem;">
                    30일 후 가격을<br>AI가 예측!
                </p>
            </div>
            """, unsafe_allow_html=True)
        
        with col4:
            st.markdown("""
            <div class="info-card">
                <span class="icon-emoji">⭐</span>
                <p class="card-label">기능 4</p>
                <h3 style="color: #333; margin: 0.5rem 0;">추천 종목</h3>
                <p style="color: #666; font-size: 0.9rem;">
                    AI가 분석한<br>추천 주식 TOP 10!
                </p>
            </div>
            """, unsafe_allow_html=True)
        
        st.markdown("</div>", unsafe_allow_html=True)
        
        st.markdown("<br>", unsafe_allow_html=True)
        
        # 인기 종목 미리보기
        st.markdown("### 🔥 지금 인기 있는 주식들")
        
        cols = st.columns(5)
        popular_stocks = [
            ("TSLA", "테슬라", "🚗", "+2.3%", True),
            ("AAPL", "애플", "🍎", "-0.5%", False),
            ("MSFT", "마이크로소프트", "💻", "+1.2%", True),
            ("NVDA", "엔비디아", "🎮", "+3.8%", True),
            ("005930.KS", "삼성전자", "📱", "+0.8%", True)
        ]
        
        for col, (ticker, name, emoji, change, is_up) in zip(cols, popular_stocks):
            color = "#00ff88" if is_up else "#ff0055"
            arrow = "↑" if is_up else "↓"
            with col:
                st.markdown(f"""
                <div class="stock-card">
                    <p class="stock-ticker">{emoji} {ticker}</p>
                    <p class="stock-name">{name}</p>
                    <p class="stock-change" style="color: {color};">
                        {arrow} {change}
                    </p>
                </div>
                """, unsafe_allow_html=True)
        
        st.markdown("<br>", unsafe_allow_html=True)
        
        # 시작하기 버튼
        col1, col2, col3 = st.columns([1, 2, 1])
        with col2:
            if st.button("🚀 주식 분석 시작하기!", use_container_width=True):
                st.balloons()
                st.success("좌측 메뉴에서 '🔍 주식 찾기'를 선택하세요!")
    
    # ===========================
    # 페이지: 주식 찾기
    # ===========================
    elif page == "🔍 주식 찾기":
        # 멋진 검색창 헤더
        st.markdown("""
        <div style="text-align: center; padding: 2rem 0 1rem 0;">
            <h1 style="font-size: 2.5rem; font-weight: 900; margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                🔍 AI 주식 분석기
            </h1>
            <p style="font-size: 1.1rem; color: #666; margin-top: 0.5rem;">
                검색 한 번으로 과거 패턴부터 미래 예측까지!
            </p>
        </div>
        """, unsafe_allow_html=True)
        
        # 고급 검색창 디자인
        st.markdown("""
        <style>
            .search-container {
                background: white;
                padding: 2rem;
                border-radius: 25px;
                box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
                margin: 2rem 0;
                border: 3px solid rgba(102, 126, 234, 0.2);
            }
            
            .search-input-wrapper {
                position: relative;
                margin: 1rem 0;
            }
            
            .popular-searches {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
                margin-top: 1rem;
            }
            
            .search-tag {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-block;
            }
            
            .search-tag:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
            }
        </style>
        
        <div class="search-container">
            <h3 style="color: #333; margin: 0 0 1rem 0; font-size: 1.3rem;">
                💎 어떤 주식을 분석할까요?
            </h3>
            <p style="color: #888; font-size: 0.95rem; margin-bottom: 1.5rem;">
                한글 이름, 영문 이름, 티커 코드 모두 검색 가능해요!
            </p>
        </div>
        """, unsafe_allow_html=True)
        
        # 검색 입력
        col1, col2 = st.columns([5, 1])
        with col1:
            ticker_input = st.text_input(
                "종목 검색",
                value=selected_quick,
                placeholder="🔍 예: 삼성전자, 현대차, 테슬라, TSLA, 005930.KS",
                label_visibility="collapsed",
                key="search_input"
            )
        with col2:
            analyze_btn = st.button("⚡ 분석 시작", use_container_width=True, type="primary", key="analyze_btn")
        
        # 인기 검색어 태그
        st.markdown("""
        <div style="background: white; padding: 1.5rem; border-radius: 20px; margin: 1rem 0; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
            <p style="color: #666; font-size: 0.9rem; margin: 0 0 0.8rem 0; font-weight: 600;">
                🔥 인기 검색어
            </p>
            <div class="popular-searches">
        """, unsafe_allow_html=True)
        
        popular_tags = ["삼성전자", "현대차", "테슬라", "애플", "네이버", "카카오", "엔비디아", "기아"]
        cols = st.columns(len(popular_tags))
        for col, tag in zip(cols, popular_tags):
            with col:
                if st.button(tag, key=f"tag_{tag}", use_container_width=True):
                    ticker_input = tag
                    analyze_btn = True
        
        st.markdown("</div></div>", unsafe_allow_html=True)
        
        if analyze_btn or ticker_input:
            # 한글/영문 이름을 티커로 변환
            ticker_query = ticker_input.strip()
            ticker = search_ticker(ticker_query)
            
            # 변환된 티커 표시
            if ticker != ticker_query.upper():
                st.info(f"🔍 '{ticker_query}' → {ticker} 로 검색합니다!")
            
            with st.spinner("📊 데이터를 가져오는 중... 잠시만 기다려주세요!"):
                start_date = (datetime.now() - timedelta(days=date_options[date_range])).strftime('%Y-%m-%d')
                df = load_stock_data(ticker, start_date)
                
                if df.empty:
                    st.error(f"😢 '{ticker_query}'를 찾을 수 없어요. 다시 확인해주세요!")
                    st.markdown("""
                    <div class="explain-text">
                        <strong>💡 검색 팁:</strong><br>
                        • 한글: 삼성전자, 테슬라, 애플, 네이버 등<br>
                        • 영문: TSLA, AAPL, GOOGL 등<br>
                        • 한국 종목 코드: 005930.KS (끝에 .KS 필수!)
                    </div>
                    """, unsafe_allow_html=True)
                    return
                
                info = get_stock_info(ticker)
            
            # 성공 메시지
            st.success(f"✅ {info.get('name', ticker)} 분석 완료!")
            
            # 기본 정보 카드
            st.markdown("#### 📌 기본 정보")
            col1, col2, col3, col4 = st.columns(4)
            
            current_price = df['Close'].iloc[-1]
            prev_price = df['Close'].iloc[-2] if len(df) > 1 else current_price
            price_change = ((current_price - prev_price) / prev_price) * 100
            
            with col1:
                st.metric("💰 현재 가격", f"${current_price:.2f}", 
                         f"{price_change:+.2f}%",
                         delta_color="normal")
            with col2:
                st.metric("🏭 업종", info.get('sector', '정보 없음'))
            with col3:
                volume = df['Volume'].iloc[-1]
                st.metric("📦 거래량", f"{volume:,.0f}")
            with col4:
                st.metric("🌍 국가", info.get('country', '정보 없음'))
            
            # 🆕 데이터 소스 안내
            st.markdown("---")
            st.markdown("## 📚 AI가 분석하는 데이터")
            st.markdown("""
            <div class="help-tip">
                <strong>💡 정확한 예측을 위해 다양한 데이터를 분석해요!</strong><br>
                단순히 차트만 보는 게 아니라, 뉴스·재무·경제·심리 등 **종합적인 정보**를 활용합니다.
            </div>
            """, unsafe_allow_html=True)
            
            # AI 분석 시작
            with st.spinner("🤖 AI가 다양한 데이터를 수집하고 분석 중... 잠시만 기다려주세요!"):
                # 🆕 외부 데이터 수집
                external_collector = ExternalDataCollector(ticker)
                external_data = external_collector.collect_all_data()
                
                # 기술적 지표 추가
                df = TechnicalIndicators.add_all_indicators(df)
                signal = TechnicalIndicators.get_trading_signal(df)  # 참고용
                
                # 고급 패턴 분석
                pattern_analyzer = AdvancedPatternAnalyzer(df)
                patterns = pattern_analyzer.analyze_all_patterns()
                
                # 백테스팅 (월별 과거 예측)
                backtester = BacktestingEngine(df)
                backtest_results = backtester.monthly_backtest(prediction_days=30)
                
                # 3개월 미래 예측
                future_3months = backtester.predict_future_3months()
                
                # 🆕 강화된 AI 예측 (30일) - 외부 데이터 활용
                enhanced_predictor = EnhancedPredictor()
                enhanced_prediction = enhanced_predictor.predict_with_external_data(df, external_data, days=30)
                
                # 기존 기술적 예측도 유지
                predictor = StockPricePredictor()
                predictions = predictor.predict_future(df, days=30)
                
                # 안전한 수익률 계산
                if current_price > 0 and len(predictions) > 0:
                    last_pred = float(predictions[-1]) if hasattr(predictions[-1], '__float__') else predictions[-1]
                    if not np.isnan(last_pred) and not np.isinf(last_pred) and last_pred > 0:
                        expected_return = ((last_pred - current_price) / current_price) * 100
                        # Infinity/NaN 체크
                        if np.isinf(expected_return) or np.isnan(expected_return):
                            expected_return = 0.0
                    else:
                        expected_return = 0.0
                else:
                    expected_return = 0.0
                
                # ========================================
                # 🎯 최종 투자 의사결정 - 먼저 생성!
                # ========================================
                from analysis.final_decision import (
                    InvestmentDecisionEngine, 
                    get_decision_emoji, 
                    get_decision_color,
                    get_decision_name_kr
                )
                
                decision_engine = InvestmentDecisionEngine()
                final_report = decision_engine.generate_final_report(
                    ticker=ticker,
                    df=df,
                    technical_signal=signal,
                    enhanced_prediction=enhanced_prediction,
                    external_data=external_data,
                    backtest_results=backtest_results,
                    patterns=patterns
                )
                
                # 최종 결정을 signal로 덮어쓰기 (통일성 확보)
                final_decision = final_report['decision']
                
                # 최종 결정을 간단한 신호로 변환
                if final_decision in ['STRONG_BUY', 'BUY']:
                    signal = 'BUY'
                elif final_decision in ['STRONG_SELL', 'SELL']:
                    signal = 'SELL'
                else:
                    signal = 'HOLD'
            
            # 🆕 수집된 외부 데이터 표시
            st.markdown("---")
            
            # ========================================
            # 🎯 최종 결정 먼저 크게 표시!
            # ========================================
            decision = final_report['decision']
            decision_strength = final_report['decision_strength']
            total_score = final_report['total_score']
            
            decision_emoji = get_decision_emoji(decision)
            decision_color = get_decision_color(decision)
            decision_name = get_decision_name_kr(decision)
            
            # 큰 결정 배너
            st.markdown(f"""
            <div style="background: linear-gradient(135deg, {decision_color}33 0%, {decision_color}11 100%); 
                        padding: 2.5rem; border-radius: 20px; border: 4px solid {decision_color}; 
                        text-align: center; margin: 2rem 0; box-shadow: 0 15px 40px rgba(0,0,0,0.2);">
                <h1 style="color: {decision_color}; margin: 0 0 0.5rem 0; font-size: 3.5rem;">
                    {decision_emoji}
                </h1>
                <h1 style="color: {decision_color}; margin: 0 0 1rem 0; font-size: 2.5rem;">
                    AI 최종 결정: {decision_name}
                </h1>
                <p style="color: #666; font-size: 1.3rem; margin: 0;">
                    결정 강도: <strong style="color: {decision_color}; font-size: 1.5rem;">{decision_strength:.0f}%</strong> | 
                    종합 점수: <strong style="font-size: 1.5rem;">{total_score:.0f}/100</strong>
                </p>
            </div>
            """, unsafe_allow_html=True)
            
            # 가격 목표 간단 표시
            targets = final_report['price_targets']
            col1, col2, col3, col4 = st.columns(4)
            
            with col1:
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">💵</span>
                    <p class="card-label">현재 가격</p>
                    <h2 class="big-number">${targets['current']:.2f}</h2>
                </div>
                """, unsafe_allow_html=True)
            
            with col2:
                gain_color = "#10b981" if targets['potential_gain'] > 0 else "#ef4444"
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">📈</span>
                    <p class="card-label">1개월 목표가</p>
                    <h2 class="big-number">${targets['target_1m']:.2f}</h2>
                    <p style="color: {gain_color}; font-size: 0.9rem; margin-top: 0.5rem;">
                        {targets['potential_gain']:+.1f}%
                    </p>
                </div>
                """, unsafe_allow_html=True)
            
            with col3:
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">🔮</span>
                    <p class="card-label">3개월 목표가</p>
                    <h2 class="big-number">${targets['target_3m']:.2f}</h2>
                </div>
                """, unsafe_allow_html=True)
            
            with col4:
                st.markdown(f"""
                <div class="info-card" style="border-left: 4px solid #ef4444;">
                    <span class="icon-emoji">🛡️</span>
                    <p class="card-label">손절가</p>
                    <h2 class="big-number" style="color: #ef4444;">${targets['stop_loss']:.2f}</h2>
                    <p style="color: #ef4444; font-size: 0.9rem; margin-top: 0.5rem;">
                        {targets['potential_loss']:.1f}%
                    </p>
                </div>
                """, unsafe_allow_html=True)
            
            # 투자 전략 간략
            strategy = final_report['strategy']
            st.info(f"""
**💡 {strategy['action']}**

{strategy['description']}

📊 **포지션**: {strategy['position_size']}  
🎯 **진입**: {strategy['entry_strategy']}  
⏱️ **기간**: {strategy['target_period']}  
🛡️ **손절**: {strategy['stop_loss']}
            """)
            
            # 핵심 근거 간단히
            with st.expander("🔍 결정 핵심 근거", expanded=True):
                key_reasons = final_report['key_reasons']
                for i, reason in enumerate(key_reasons, 1):
                    st.markdown(f"**{i}.** {reason}")
            
            st.markdown("---")
            st.markdown("### 📊 수집된 데이터 소스")
            
            data_sources = enhanced_prediction.get('data_sources', [])
            if data_sources:
                cols = st.columns(min(len(data_sources), 4))
                for i, source in enumerate(data_sources):
                    with cols[i % 4]:
                        st.markdown(f"""
                        <div style="background: white; padding: 0.8rem; border-radius: 10px; 
                                    text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            {source}
                        </div>
                        """, unsafe_allow_html=True)
            
            # 외부 데이터 상세 (확장 가능)
            with st.expander("🔍 수집된 데이터 상세 보기", expanded=False):
                # 뉴스 데이터
                news = external_data.get('news', {})
                if news.get('count', 0) > 0:
                    st.markdown("#### 📰 뉴스 분석")
                    col1, col2, col3 = st.columns(3)
                    with col1:
                        st.metric("뉴스 건수", f"{news.get('count', 0)}건")
                    with col2:
                        sentiment = news.get('avg_sentiment', 0)
                        sentiment_emoji = "😊" if sentiment > 0 else "😐" if sentiment == 0 else "😢"
                        st.metric("평균 감정", f"{sentiment_emoji} {sentiment:.2f}")
                    with col3:
                        positive = news.get('positive_ratio', 0) * 100
                        st.metric("긍정 비율", f"{positive:.1f}%")
                    
                    # 뉴스 카테고리
                    categories = news.get('categories', {})
                    if categories:
                        st.markdown("**뉴스 카테고리:**")
                        for category, count in list(categories.items())[:5]:
                            st.write(f"- {category}: {count}건")
                
                # 재무 데이터
                financial = external_data.get('financial', {})
                if financial:
                    st.markdown("#### 💼 재무 정보")
                    col1, col2, col3, col4 = st.columns(4)
                    with col1:
                        roe = financial.get('roe', 0)
                        roe_color = "green" if roe > 15 else "orange" if roe > 10 else "red"
                        st.markdown(f"**ROE:** <span style='color:{roe_color}'>{roe:.1f}%</span>", unsafe_allow_html=True)
                    with col2:
                        debt = financial.get('debt_to_equity', 0)
                        debt_color = "green" if debt < 100 else "orange" if debt < 200 else "red"
                        st.markdown(f"**부채비율:** <span style='color:{debt_color}'>{debt:.0f}%</span>", unsafe_allow_html=True)
                    with col3:
                        margin = financial.get('profit_margin', 0)
                        margin_color = "green" if margin > 10 else "orange" if margin > 5 else "red"
                        st.markdown(f"**이익률:** <span style='color:{margin_color}'>{margin:.1f}%</span>", unsafe_allow_html=True)
                    with col4:
                        health = financial.get('health_grade', '보통')
                        st.markdown(f"**건전성:** {health}")
                
                # 경제 지표
                economic = external_data.get('economic', {})
                if economic:
                    st.markdown("#### 📈 경제 지표")
                    col1, col2, col3, col4 = st.columns(4)
                    with col1:
                        vix = economic.get('vix', 0)
                        vix_color = "green" if vix < 15 else "orange" if vix < 25 else "red"
                        st.markdown(f"**VIX:** <span style='color:{vix_color}'>{vix:.1f}</span>", unsafe_allow_html=True)
                    with col2:
                        market = economic.get('market_change', 0)
                        market_emoji = "📈" if market > 0 else "📉"
                        st.markdown(f"**시장:** {market_emoji} {market:+.1f}%")
                    with col3:
                        yield_rate = economic.get('us_10y_yield', 0)
                        st.markdown(f"**미국 10년물:** {yield_rate:.2f}%")
                    with col4:
                        grade = economic.get('economic_grade', '안정')
                        st.markdown(f"**경제 상황:** {grade}")
                
                # 애널리스트 평가
                analyst = external_data.get('analyst', {})
                if analyst.get('target_price', 0) > 0:
                    st.markdown("#### 🎯 애널리스트 평가")
                    col1, col2, col3 = st.columns(3)
                    with col1:
                        target = analyst.get('target_price', 0)
                        st.metric("목표가", f"${target:.2f}")
                    with col2:
                        upside = analyst.get('upside_potential', 0)
                        upside_color = "green" if upside > 0 else "red"
                        st.markdown(f"**상승여력:** <span style='color:{upside_color}'>{upside:+.1f}%</span>", unsafe_allow_html=True)
                    with col3:
                        count = analyst.get('analyst_count', 0)
                        st.metric("애널리스트 수", f"{count}명")
            
            # 📊 과거 패턴 분석 섹션 (새로 추가!)
            st.markdown("---")
            st.markdown("## 📊 과거 패턴 분석")
            st.markdown("""
            <div class="help-tip">
                <strong>💡 왜 과거를 분석하나요?</strong><br>
                과거의 패턴을 이해하면 미래를 더 정확하게 예측할 수 있어요!<br>
                AI는 5년간의 데이터를 학습해서 수익 패턴, 계절성, 트렌드를 찾아냈어요.
            </div>
            """, unsafe_allow_html=True)
            
            # 패턴 분석 결과 카드
            col1, col2, col3, col4 = st.columns(4)
            
            profit = patterns['profit_patterns']
            trend = patterns['trend']
            vol = patterns['volatility']
            momentum = patterns['momentum']
            
            with col1:
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">💰</span>
                    <p class="card-label">총 수익률 ({date_range})</p>
                    <h2 class="big-number">{profit['total_return']:+.1f}%</h2>
                    <p style="color: #888; font-size: 0.85rem;">승률: {profit['win_rate']:.0f}%</p>
                </div>
                """, unsafe_allow_html=True)
            
            with col2:
                trend_emoji = "📈" if "상승" in trend['trend_direction'] else "📉" if "하락" in trend['trend_direction'] else "➡️"
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">{trend_emoji}</span>
                    <p class="card-label">최근 추세</p>
                    <h3 style="color: #333; margin: 0.5rem 0;">{trend['trend_direction']}</h3>
                    <p style="color: #888; font-size: 0.85rem;">30일: {trend['return_30d']:+.1f}%</p>
                </div>
                """, unsafe_allow_html=True)
            
            with col3:
                vol_color = "#00ff88" if "낮음" in vol['volatility_grade'] else "#f2994a" if "보통" in vol['volatility_grade'] else "#ff0055"
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">⚡</span>
                    <p class="card-label">변동성</p>
                    <h3 style="color: {vol_color}; margin: 0.5rem 0;">{vol['volatility_grade']}</h3>
                    <p style="color: #888; font-size: 0.85rem;">연간: {vol['annual_volatility']:.0f}%</p>
                </div>
                """, unsafe_allow_html=True)
            
            with col4:
                momentum_color = "#00ff88" if "상승" in momentum['momentum_grade'] else "#ff0055" if "하락" in momentum['momentum_grade'] else "#f2994a"
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">🚀</span>
                    <p class="card-label">모멘텀</p>
                    <h3 style="color: {momentum_color}; margin: 0.5rem 0;">{momentum['momentum_grade']}</h3>
                    <p style="color: #888; font-size: 0.85rem;">점수: {momentum['momentum_score']:.0f}</p>
                </div>
                """, unsafe_allow_html=True)
            
            # 상세 패턴 분석
            with st.expander("📈 상세 패턴 분석 보기", expanded=False):
                st.markdown("### 🎯 수익 패턴 분석")
                
                col1, col2 = st.columns(2)
                with col1:
                    st.metric("평균 수익 (상승시)", f"+{profit['avg_win']:.2f}%")
                    st.metric("평균 손실 (하락시)", f"{profit['avg_loss']:.2f}%")
                with col2:
                    st.metric("승률", f"{profit['win_rate']:.1f}%")
                    if profit['best_year']:
                        st.metric(f"최고의 해", f"{profit['best_year']}년", f"+{profit['yearly_returns'][profit['best_year']]:.1f}%")
                
                st.markdown("### 🌊 계절성 패턴")
                seasonal = patterns['seasonal']
                if seasonal['best_quarter']:
                    quarter_names = {1: "1분기(1-3월)", 2: "2분기(4-6월)", 3: "3분기(7-9월)", 4: "4분기(10-12월)"}
                    st.info(f"💡 **{quarter_names[seasonal['best_quarter']]}**에 가장 좋은 성과를 보였어요!")
                
                st.markdown("### 📊 리스크 분석")
                risk = patterns['risk_reward']
                col1, col2 = st.columns(2)
                with col1:
                    st.metric("샤프 비율", f"{risk['sharpe_ratio']:.2f}")
                    st.caption("위험 대비 수익률 (1 이상이면 우수)")
                with col2:
                    st.metric("리스크 등급", risk['risk_grade'])
                    st.caption(f"최대 낙폭: {vol['max_drawdown']:.1f}%")
            
            # AI 분석 결과
            st.markdown("---")
            st.markdown("## 🤖 AI 종합 예측 결과")
            
            # enhanced_prediction에서 신뢰도 추출
            enhanced_confidence = enhanced_prediction.get('confidence', 0)
            
            st.markdown(f"""
            <div class="help-tip">
                <strong>💡 AI가 어떻게 예측하나요?</strong><br>
                • <b>학습 데이터</b>: 과거 5년간의 주가 + 뉴스 + 재무 + 경제 + 심리<br>
                • <b>분석 항목</b>: 가격 패턴, 거래량, 기술적 지표, 뉴스 감정, 재무제표, 경제지표, 애널리스트 평가<br>
                • <b>AI 모델</b>: LSTM 딥러닝 + 앙상블 통합<br>
                • <b>예측 기간</b>: 30일 후의 주가<br>
                • <b>신뢰도</b>: {enhanced_confidence:.1f}% (외부 데이터 통합으로 정확도 향상!)
            </div>
            """, unsafe_allow_html=True)
            
            # 🆕 예측 방법 비교
            st.markdown("### 📊 예측 방법 비교")
            col1, col2 = st.columns(2)
            
            # 기술적 예측만 (차트만)
            tech_only_pred = predictions[-1]
            tech_only_return = ((tech_only_pred - current_price) / current_price) * 100
            
            # 강화된 예측 (차트 + 외부 데이터)
            enhanced_pred = enhanced_prediction.get('predictions', [])[-1]
            enhanced_return = ((enhanced_pred - current_price) / current_price) * 100
            
            with col1:
                st.markdown(f"""
                <div style="background: white; padding: 1.5rem; border-radius: 15px; border: 2px solid #667eea;">
                    <h4 style="color: #667eea; margin: 0 0 1rem 0;">📈 기술적 분석만</h4>
                    <p style="color: #888; font-size: 0.9rem; margin-bottom: 1rem;">차트 패턴만 사용</p>
                    <h2 style="color: #333; margin: 0;">${tech_only_pred:.2f}</h2>
                    <p style="color: {"green" if tech_only_return > 0 else "red"}; font-size: 1.2rem; margin: 0.5rem 0 0 0;">
                        {tech_only_return:+.2f}%
                    </p>
                </div>
                """, unsafe_allow_html=True)
            
            with col2:
                st.markdown(f"""
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                            padding: 1.5rem; border-radius: 15px; color: white; box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);">
                    <h4 style="color: white; margin: 0 0 1rem 0;">🚀 종합 분석 (권장)</h4>
                    <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem; margin-bottom: 1rem;">
                        차트 + 뉴스 + 재무 + 경제 + 심리
                    </p>
                    <h2 style="color: white; margin: 0;">${enhanced_pred:.2f}</h2>
                    <p style="color: {"#00ff88" if enhanced_return > 0 else "#ff0055"}; font-size: 1.2rem; margin: 0.5rem 0 0 0;">
                        {enhanced_return:+.2f}%
                    </p>
                    <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin: 0.5rem 0 0 0;">
                        신뢰도: {enhanced_prediction.get('confidence', 0):.1f}%
                    </p>
                </div>
                """, unsafe_allow_html=True)
            
            # 🆕 예측 근거 (외부 데이터 기반)
            st.markdown("### 🧠 종합 분석 근거")
            explanations = enhanced_prediction.get('explanation', [])
            
            if explanations:
                st.markdown("""
                <div style="background: white; padding: 1.5rem; border-radius: 15px; margin: 1rem 0; border-left: 5px solid #667eea;">
                    <h4 style="color: #333; margin: 0 0 1rem 0;">💡 AI가 종합 분석한 결과</h4>
                """, unsafe_allow_html=True)
                
                for explanation in explanations:
                    st.markdown(f"- {explanation}")
                
                st.markdown("</div>", unsafe_allow_html=True)
            
            # 주요 영향 요인
            factors = enhanced_prediction.get('factors', [])
            if factors:
                st.markdown("### 🎯 주요 영향 요인 (Top 3)")
                
                cols = st.columns(min(len(factors), 3))
                for i, factor in enumerate(factors[:3]):
                    with cols[i]:
                        impact = factor['impact']
                        direction = factor['direction']
                        strength = factor['strength']
                        
                        # 색상 결정
                        if '상승' in direction:
                            color = "#10b981"
                            emoji = "📈"
                        else:
                            color = "#ef4444"
                            emoji = "📉"
                        
                        st.markdown(f"""
                        <div style="background: white; padding: 1rem; border-radius: 10px; 
                                    border-left: 4px solid {color}; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <p style="color: #888; font-size: 0.85rem; margin: 0 0 0.5rem 0;">{factor['name']}</p>
                            <h3 style="color: {color}; margin: 0 0 0.3rem 0;">{emoji} {direction}</h3>
                            <p style="color: #888; font-size: 0.9rem; margin: 0;">영향력: {strength} ({impact*100:+.1f}%)</p>
                        </div>
                        """, unsafe_allow_html=True)
            
            # 매매 신호 및 근거
            signal_text = {
                "BUY": "🟢 사세요! (매수)",
                "SELL": "🔴 파세요! (매도)",
                "HOLD": "🟡 기다리세요! (보유)"
            }[signal]
            
            # 예측 근거 생성
            reasons = []
            
            # 1. 트렌드 분석
            if "상승" in trend['trend_direction']:
                reasons.append(f"✅ 최근 {date_range} 추세가 **상승** 중이에요 ({trend['return_30d']:+.1f}%)")
            elif "하락" in trend['trend_direction']:
                reasons.append(f"⚠️ 최근 {date_range} 추세가 **하락** 중이에요 ({trend['return_30d']:+.1f}%)")
            else:
                reasons.append(f"➡️ 최근 {date_range} 추세가 **횡보** 중이에요")
            
            # 2. 모멘텀 분석
            if momentum['momentum_score'] > 10:
                reasons.append(f"✅ 강한 **상승 모멘텀** (점수: {momentum['momentum_score']:.0f})")
            elif momentum['momentum_score'] < -10:
                reasons.append(f"⚠️ 강한 **하락 모멘텀** (점수: {momentum['momentum_score']:.0f})")
            
            # 3. 승률 분석
            if profit['win_rate'] > 55:
                reasons.append(f"✅ 높은 **승률** {profit['win_rate']:.0f}% (100번 중 {profit['win_rate']:.0f}번 수익)")
            elif profit['win_rate'] < 45:
                reasons.append(f"⚠️ 낮은 **승률** {profit['win_rate']:.0f}%")
            
            # 4. 기술적 지표
            if 'RSI' in df.columns:
                rsi = df['RSI'].iloc[-1]
                if rsi < 30:
                    reasons.append("✅ RSI 지표: **과매도** 구간 (저렴해요!)")
                elif rsi > 70:
                    reasons.append("⚠️ RSI 지표: **과매수** 구간 (비싸요!)")
            
            # 5. 변동성
            if "낮음" in vol['volatility_grade']:
                reasons.append("✅ 변동성이 **낮아서 안정적**이에요")
            elif "높음" in vol['volatility_grade']:
                reasons.append("⚠️ 변동성이 **높아서 위험**할 수 있어요")
            
            st.markdown(f'<div class="signal-badge signal-{signal.lower()}">{signal_text}</div>', 
                       unsafe_allow_html=True)
            
            # 예측 근거 표시
            st.markdown("""
            <div style="background: white; padding: 1.5rem; border-radius: 15px; margin: 1rem 0; border-left: 5px solid #667eea;">
                <h4 style="color: #333; margin: 0 0 1rem 0;">🧠 AI가 이렇게 판단한 이유</h4>
            """, unsafe_allow_html=True)
            
            for reason in reasons:
                st.markdown(f"- {reason}")
            
            st.markdown("</div>", unsafe_allow_html=True)
            
            # 예측 결과
            col1, col2 = st.columns(2)
            with col1:
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">📅</span>
                    <p class="card-label">30일 후 예상 가격</p>
                    <h2 class="big-number">${predictions[-1]:.2f}</h2>
                </div>
                """, unsafe_allow_html=True)
            
            with col2:
                return_color = "green" if expected_return > 0 else "red"
                return_emoji = "📈" if expected_return > 0 else "📉"
                st.markdown(f"""
                <div class="info-card">
                    <span class="icon-emoji">{return_emoji}</span>
                    <p class="card-label">예상 수익률</p>
                    <h2 class="big-number" style="color: {return_color};">{expected_return:+.2f}%</h2>
                </div>
                """, unsafe_allow_html=True)
            
            # 차트
            st.markdown("#### 📈 가격 흐름 그래프")
            st.markdown("""
            <div class="explain-text">
                <strong>📊 그래프 보는 법:</strong><br>
                • <span style="color: #667eea; font-weight: bold;">파란 선</span>은 실제 가격이에요<br>
                • <span style="color: #ffa500; font-weight: bold;">주황 점선</span>은 추세선 (전체적인 흐름)<br>
                • <span style="color: #ff006e; font-weight: bold;">핑크 별</span>은 AI가 예측한 미래 가격!
            </div>
            """, unsafe_allow_html=True)
            
            st.plotly_chart(plot_stock_chart_simple(df, predictions, ticker), 
                          use_container_width=True)
            
            # 🔥 백테스팅 및 미래 예측 섹션 (신규 추가!)
            st.markdown("---")
            st.markdown("## 🔬 AI 예측 검증 및 미래 전망")
            
            # 백테스팅 결과
            if backtest_results and backtest_results.get('dates'):
                st.markdown("### 📊 과거 월별 예측 vs 실제 비교")
                st.markdown("""
                <div class="help-tip">
                    <strong>💡 AI가 얼마나 정확할까요?</strong><br>
                    과거 각 월마다 AI가 예측했던 가격과 실제 가격을 비교해봤어요.<br>
                    정확도가 높을수록 AI 예측을 더 신뢰할 수 있어요!
                </div>
                """, unsafe_allow_html=True)
                
                # 백테스팅 통계
                col1, col2, col3 = st.columns(3)
                with col1:
                    accuracy = backtest_results.get('accuracy', 0)
                    acc_color = "green" if accuracy > 70 else ("orange" if accuracy > 50 else "red")
                    st.markdown(f"""
                    <div class="info-card">
                        <span class="icon-emoji">🎯</span>
                        <p class="card-label">평균 정확도</p>
                        <h2 class="big-number" style="color: {acc_color};">{accuracy:.1f}%</h2>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col2:
                    avg_error = backtest_results.get('avg_error', 0)
                    st.markdown(f"""
                    <div class="info-card">
                        <span class="icon-emoji">📏</span>
                        <p class="card-label">평균 오차</p>
                        <h2 class="big-number">{avg_error:.1f}%</h2>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col3:
                    test_count = len(backtest_results.get('dates', []))
                    st.markdown(f"""
                    <div class="info-card">
                        <span class="icon-emoji">🔢</span>
                        <p class="card-label">검증 횟수</p>
                        <h2 class="big-number">{test_count}회</h2>
                    </div>
                    """, unsafe_allow_html=True)
                
                # 백테스팅 차트
                backtest_chart = plot_backtest_comparison(backtest_results, df)
                if backtest_chart:
                    st.plotly_chart(backtest_chart, use_container_width=True)
                    
                    st.markdown("""
                    <div class="explain-text">
                        <strong>📊 차트 해석:</strong><br>
                        • <span style="color: #10b981; font-weight: bold;">녹색 선 (실제)</span>과 <span style="color: #8b5cf6; font-weight: bold;">보라색 별 (AI 예측)</span>이 가까울수록 정확해요!<br>
                        • 과거에도 잘 맞췄다면, 미래 예측도 신뢰할 수 있어요<br>
                        • 하지만 100% 맞는 건 불가능해요. 참고용으로만 활용하세요!
                    </div>
                    """, unsafe_allow_html=True)
            
            # 3개월 미래 예측
            if future_3months and future_3months.get('dates'):
                st.markdown("### 🔮 3개월 미래 예측 (섬세한 분석)")
                st.markdown("""
                <div class="help-tip">
                    <strong>💡 3개월 후는 어떻게 될까요?</strong><br>
                    AI가 여러 방법(추세, 이동평균, 계절성)으로 분석해서 3개월 미래를 섬세하게 그렸어요.<br>
                    신뢰 구간(회색 영역)은 예측이 틀릴 수 있는 범위를 보여줘요!
                </div>
                """, unsafe_allow_html=True)
                
                # 3개월 예측 통계
                col1, col2, col3 = st.columns(3)
                
                predicted_price_3m = future_3months['prices'][-1]
                expected_return_3m = ((predicted_price_3m - current_price) / current_price) * 100
                confidence_3m = future_3months.get('confidence', 0)
                
                with col1:
                    st.markdown(f"""
                    <div class="info-card">
                        <span class="icon-emoji">🎯</span>
                        <p class="card-label">3개월 후 예상가</p>
                        <h2 class="big-number">${predicted_price_3m:.2f}</h2>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col2:
                    return_color_3m = "green" if expected_return_3m > 0 else "red"
                    return_emoji_3m = "📈" if expected_return_3m > 0 else "📉"
                    st.markdown(f"""
                    <div class="info-card">
                        <span class="icon-emoji">{return_emoji_3m}</span>
                        <p class="card-label">예상 수익률</p>
                        <h2 class="big-number" style="color: {return_color_3m};">{expected_return_3m:+.1f}%</h2>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col3:
                    conf_color = "green" if confidence_3m > 60 else ("orange" if confidence_3m > 40 else "red")
                    st.markdown(f"""
                    <div class="info-card">
                        <span class="icon-emoji">🔒</span>
                        <p class="card-label">신뢰도</p>
                        <h2 class="big-number" style="color: {conf_color};">{confidence_3m:.1f}%</h2>
                    </div>
                    """, unsafe_allow_html=True)
                
                # 3개월 예측 차트
                future_chart = plot_3month_future(future_3months, current_price)
                if future_chart:
                    st.plotly_chart(future_chart, use_container_width=True)
                    
                    st.markdown("""
                    <div class="explain-text">
                        <strong>🔮 차트 해석:</strong><br>
                        • <span style="color: #8b5cf6; font-weight: bold;">보라색 선</span>이 AI의 메인 예측이에요<br>
                        • <span style="color: #10b981;">녹색 점선</span>은 최대 예상 (낙관적), <span style="color: #ef4444;">빨강 점선</span>은 최소 예상 (비관적)<br>
                        • 회색 영역이 신뢰 구간 (실제 가격이 이 안에 있을 확률이 높아요)<br>
                        • 노란 선은 현재 가격이에요
                    </div>
                    """, unsafe_allow_html=True)
                    
                    # 투자 조언
                    if expected_return_3m > 10:
                        advice = "🚀 3개월 후 큰 상승이 예상돼요! 하지만 변동성을 고려해서 신중하게 투자하세요."
                    elif expected_return_3m > 5:
                        advice = "📈 3개월 후 완만한 상승이 예상돼요. 안정적인 투자 기회일 수 있어요."
                    elif expected_return_3m > -5:
                        advice = "➡️ 3개월 후 횡보가 예상돼요. 단기보다 장기 투자를 고려하세요."
                    else:
                        advice = "⚠️ 3개월 후 하락이 예상돼요. 투자에 신중하시고, 손절 시점을 미리 정하세요."
                    
                    st.info(f"**💡 AI 조언:** {advice}")
            
            # ========================================
            # 📊 상세 분석 보고서 (확장 패널)
            # ========================================
            st.markdown("---")
            with st.expander("📋 상세 분석 보고서 보기", expanded=False):
                st.markdown("## 📊 세부 분석 점수")
                scores = final_report['scores']
                
                col1, col2, col3, col4 = st.columns(4)
                
                with col1:
                    score_color = "#10b981" if scores['technical'] > 60 else ("#fbbf24" if scores['technical'] > 40 else "#ef4444")
                    st.markdown(f"""
                    <div class="info-card" style="border-left: 4px solid {score_color};">
                        <span class="icon-emoji">📊</span>
                        <p class="card-label">기술적 분석</p>
                        <h2 class="big-number" style="color: {score_color};">{scores['technical']:.0f}</h2>
                        <p style="color: #888; font-size: 0.85rem;">차트 패턴, 지표</p>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col2:
                    score_color = "#10b981" if scores['ai_prediction'] > 60 else ("#fbbf24" if scores['ai_prediction'] > 40 else "#ef4444")
                    st.markdown(f"""
                    <div class="info-card" style="border-left: 4px solid {score_color};">
                        <span class="icon-emoji">🤖</span>
                        <p class="card-label">AI 예측</p>
                        <h2 class="big-number" style="color: {score_color};">{scores['ai_prediction']:.0f}</h2>
                        <p style="color: #888; font-size: 0.85rem;">신뢰도, 수익률</p>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col3:
                    score_color = "#10b981" if scores['fundamental'] > 60 else ("#fbbf24" if scores['fundamental'] > 40 else "#ef4444")
                    st.markdown(f"""
                    <div class="info-card" style="border-left: 4px solid {score_color};">
                        <span class="icon-emoji">💼</span>
                        <p class="card-label">펀더멘털</p>
                        <h2 class="big-number" style="color: {score_color};">{scores['fundamental']:.0f}</h2>
                        <p style="color: #888; font-size: 0.85rem;">재무, 뉴스</p>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col4:
                    score_color = "#10b981" if scores['market_environment'] > 60 else ("#fbbf24" if scores['market_environment'] > 40 else "#ef4444")
                    st.markdown(f"""
                    <div class="info-card" style="border-left: 4px solid {score_color};">
                        <span class="icon-emoji">🌍</span>
                        <p class="card-label">시장 환경</p>
                        <h2 class="big-number" style="color: {score_color};">{scores['market_environment']:.0f}</h2>
                        <p style="color: #888; font-size: 0.85rem;">경제, 심리</p>
                    </div>
                    """, unsafe_allow_html=True)
                
                # 리스크 평가
                st.markdown("### ⚠️ 리스크 평가")
                risk = final_report['risk']
                risk_colors = {
                    'VERY_LOW': '#10b981',
                    'LOW': '#34d399',
                    'MEDIUM': '#fbbf24',
                    'HIGH': '#f87171',
                    'VERY_HIGH': '#ef4444'
                }
                risk_names = {
                    'VERY_LOW': '매우 낮음',
                    'LOW': '낮음',
                    'MEDIUM': '보통',
                    'HIGH': '높음',
                    'VERY_HIGH': '매우 높음'
                }
                
                risk_color = risk_colors.get(risk['level'], '#6b7280')
                risk_name = risk_names.get(risk['level'], '알 수 없음')
                
                col1, col2 = st.columns([1, 2])
                with col1:
                    st.markdown(f"""
                    <div class="info-card" style="border-left: 4px solid {risk_color};">
                        <span class="icon-emoji">⚠️</span>
                        <p class="card-label">리스크 레벨</p>
                        <h2 class="big-number" style="color: {risk_color};">{risk_name}</h2>
                        <p style="color: #888; font-size: 0.85rem;">리스크 점수: {risk['score']}/100</p>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col2:
                    st.markdown("**주요 리스크 요인:**")
                    for factor in risk['factors']:
                        st.markdown(f"- {factor}")
                
                # 투자 전략 상세
                st.markdown("### 💡 상세 투자 전략")
                strategy = final_report['strategy']
                
                st.markdown(f"""
                <div style="background: white; padding: 1.5rem; border-radius: 15px; 
                            border-left: 5px solid {decision_color}; margin: 1rem 0;">
                    <h3 style="color: {decision_color}; margin: 0 0 1rem 0;">{strategy['action']}</h3>
                    <p style="color: #555; line-height: 1.6; margin-bottom: 1rem;">{strategy['description']}</p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                        <div>
                            <strong style="color: #888;">📊 포지션 크기</strong><br>
                            <span style="color: #333;">{strategy['position_size']}</span>
                        </div>
                        <div>
                            <strong style="color: #888;">🎯 진입 전략</strong><br>
                            <span style="color: #333;">{strategy['entry_strategy']}</span>
                        </div>
                        <div>
                            <strong style="color: #888;">⏱️ 목표 기간</strong><br>
                            <span style="color: #333;">{strategy['target_period']}</span>
                        </div>
                        <div>
                            <strong style="color: #888;">🛡️ 손절가</strong><br>
                            <span style="color: #ef4444; font-weight: bold;">{strategy['stop_loss']}</span>
                        </div>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 1rem; border-radius: 10px; margin-top: 1rem; border-left: 4px solid #ffc107;">
                        <strong>⚡ 리스크 조정:</strong> {strategy['risk_adjustment']}
                    </div>
                </div>
                """, unsafe_allow_html=True)
                
                # 손익비
                st.markdown("### ⚖️ 손익비 분석")
                col1, col2 = st.columns(2)
                with col1:
                    st.markdown(f"""
                    <div style="background: #fee2e2; padding: 1rem; border-radius: 10px; border-left: 4px solid #ef4444;">
                        <strong style="color: #991b1b;">🛡️ 최대 손실 (손절가 도달 시)</strong><br>
                        <span style="font-size: 1.5rem; color: #ef4444; font-weight: bold;">${targets['stop_loss']:.2f}</span><br>
                        <span style="color: #991b1b;">({targets['potential_loss']:.1f}% 손실)</span>
                    </div>
                    """, unsafe_allow_html=True)
                
                with col2:
                    rr_color = "#10b981" if targets['risk_reward_ratio'] > 2 else "#fbbf24"
                    st.markdown(f"""
                    <div style="background: #d1fae5; padding: 1rem; border-radius: 10px; border-left: 4px solid #10b981;">
                        <strong style="color: #065f46;">⚖️ 손익비 (Risk/Reward Ratio)</strong><br>
                        <span style="font-size: 1.5rem; color: {rr_color}; font-weight: bold;">{targets['risk_reward_ratio']:.2f}</span><br>
                        <span style="color: #065f46;">
                            {"✅ 좋은 비율 (2.0 이상)" if targets['risk_reward_ratio'] > 2 else "⚠️ 신중 필요"}
                        </span>
                    </div>
                    """, unsafe_allow_html=True)
            
            # 주의사항
            st.warning("""
            **⚠️ 투자 유의사항**
            - 이 보고서는 AI 분석 결과로 참고용이며, 투자의 최종 책임은 본인에게 있습니다.
            - 과거 데이터 기반 예측이므로 미래 수익을 100% 보장하지 않습니다.
            - 분산 투자와 리스크 관리를 철저히 하세요.
            - 손절가는 반드시 설정하고 지키세요.
            - 여유 자금으로만 투자하시고, 빚을 내서 투자하지 마세요.
            """)
            

            # 탭으로 상세 정보
            st.markdown("---")
            tab1, tab2, tab3, tab4 = st.tabs(["📊 지표 설명", "📈 월별 수익률", "🎯 예측 방법론", "❓ 더 알아보기"])
            
            with tab1:
                st.markdown("""
                <div class="help-tip">
                    <strong>🎓 RSI란?</strong><br>
                    주식이 너무 비싸졌는지(과매수), 너무 저렴해졌는지(과매도)를 알려주는 지표예요!<br>
                    • 70 이상: 너무 비싸요! 곧 떨어질 수 있어요<br>
                    • 30 이하: 저렴해요! 살 찬스일 수 있어요<br>
                    • 30~70: 적당한 가격이에요
                </div>
                """, unsafe_allow_html=True)
                
                st.plotly_chart(plot_rsi_simple(df), use_container_width=True)
                
                col1, col2, col3 = st.columns(3)
                with col1:
                    rsi = df['RSI'].iloc[-1] if 'RSI' in df else 50
                    rsi_status = "🔴 비싸요" if rsi > 70 else ("🟢 저렴해요" if rsi < 30 else "🟡 적당해요")
                    st.metric("RSI 값", f"{rsi:.1f}", rsi_status)
                
                with col2:
                    macd = df['MACD'].iloc[-1] if 'MACD' in df else 0
                    st.metric("MACD", f"{macd:.2f}")
                
                with col3:
                    vol_val = df['Volatility'].iloc[-1] if 'Volatility' in df else 0
                    st.metric("변동성", f"{vol_val:.2%}")
            
            with tab2:
                st.markdown("#### 📅 최근 12개월 수익률")
                st.markdown("""
                <div class="explain-text">
                    월별로 얼마나 올랐는지/떨어졌는지 보여줘요!
                </div>
                """, unsafe_allow_html=True)
                
                try:
                    monthly = df.groupby([df.index.year, df.index.month])['Close'].last().pct_change() * 100
                    monthly_df = monthly.tail(12).reset_index(drop=True).to_frame('수익률 (%)')
                    st.bar_chart(monthly_df)
                except Exception as e:
                    st.warning(f"월별 차트를 표시할 수 없어요: {str(e)}")
            
            with tab3:
                st.markdown("""
                ### 🎯 AI 예측 방법론
                
                **1. 데이터 수집**
                - 과거 5년간의 주가 데이터
                - 뉴스, 재무제표, 경제 지표 등 외부 데이터 (13개 소스)
                
                **2. 기술적 분석 (30+ 지표)**
                - RSI, MACD, 볼린저 밴드
                - 이동평균선 (5일, 20일, 50일, 200일)
                - ATR, 변동성 지표
                
                **3. AI 학습**
                - LSTM 딥러닝 모델
                - 앙상블 학습 (RF, GB, LR)
                - 백테스팅으로 정확도 검증
                
                **4. 외부 데이터 통합**
                - 뉴스 감정 분석
                - 재무제표 분석
                - 경제 지표 (VIX, 금리 등)
                - 시장 심리 (Fear & Greed Index)
                
                **5. 최종 의사결정**
                - 기술적 분석 (25%)
                - AI 예측 (30%)
                - 펀더멘털 (25%)
                - 시장 환경 (20%)
                - 종합 점수로 매수/매도/보유 결정
                """)
            
            with tab4:
                st.markdown("### ❓ 자주 묻는 질문")
                
                with st.expander("💰 주식이 뭔가요?"):
                    st.markdown("""
                    ### 주식이란?
                    
                    **쉽게 말하면:** 회사의 작은 조각이에요!
                    
                    회사가 돈을 모으려고 자기 회사를 작은 조각(주식)으로 나눠서 팔아요.
                    당신이 주식을 사면 = 그 회사의 주인이 되는 거예요! (아주 작은 주인이지만요)
                    
                    **주식으로 돈 버는 법:**
                    1. 주식 가격이 오르면 = 비싸게 팔아서 차익
                    2. 회사가 이익을 내면 = 배당금을 받을 수 있어요
                    
                    **쉽게 말하면:** 좋은 회사 주식을 싸게 사서, 비싸게 팔면 돈을 버는 거예요!
                    """)
                
                with st.expander("📊 차트는 어떻게 보나요?"):
                    st.markdown("""
                    ### 차트 보는 법
                    
                    **1. 선 그래프**
                    - 위로 올라가면 = 가격이 오르는 중 📈
                    - 아래로 내려가면 = 가격이 떨어지는 중 📉
                    
                    **2. 이동평균선 (추세선)**
                    - 전체적인 흐름을 보여줘요
                    - 위로 가면 = 상승 추세
                    - 아래로 가면 = 하락 추세
                    
                    **3. RSI 지표**
                    - 70 이상 = 너무 비싸요 (조심!)
                    - 30 이하 = 저렴해요 (기회!)
                    """)
                
                with st.expander("🤖 AI 예측을 어떻게 믿나요?"):
                    st.markdown("""
                    ### AI 예측의 신뢰도
                    
                    **AI가 하는 일:**
                    1. 과거 5년간의 주가 데이터를 학습해요
                    2. 패턴을 찾아요
                    3. 그 패턴으로 미래를 예측해요
                    
                    **하지만!**
                    - AI 정확도는 약 70~80% 정도예요
                    - 100% 맞출 순 없어요!
                    - **참고용**으로만 사용하세요
                    
                    **투자 원칙:**
                    - AI 말만 듣지 말고, 회사 뉴스도 확인하세요
                    - 여러 정보를 종합해서 판단하세요
                    - 잃어도 괜찮은 돈으로만 투자하세요!
                    """)
                
                with st.expander("💰 투자 꿀팁"):
                    st.markdown("""
                    ### 초보자 투자 가이드
                    
                    **1. 분산 투자하세요**
                    - 한 종목에 몰빵 ❌
                    - 5~10개 종목에 나눠서 ✅
                    
                    **2. 장기 투자하세요**
                    - 단기 등락에 흔들리지 마세요
                    - 좋은 회사는 장기적으로 오릅니다
                    
                    **3. 손절가를 지키세요**
                    - 10% 떨어지면 무조건 팔기!
                    - 손실을 키우지 마세요
                    
                    **4. 여유 자금으로만**
                    - 당장 필요한 돈으로 투자 ❌
                    - 빚내서 투자 절대 ❌
                    - 잃어도 괜찮은 돈으로만 ✅
                    """)


# ========================================
# 사이드바: 추천 종목
# ========================================
with st.sidebar:
    st.markdown("## 🔥 인기 종목")
    
    markets = {
        "🇺🇸 미국": POPULAR_STOCKS['US'],
        "🇰🇷 한국": POPULAR_STOCKS['KR']
    }
    
    for market_name, stocks in markets.items():
        with st.expander(market_name, expanded=False):
            for ticker, name in stocks[:5]:  # 상위 5개만
                if st.button(f"📊 {name}", key=f"rec_{ticker}"):
                    st.session_state.search_ticker = ticker
                    st.rerun()


# ========================================
# 실행
# ========================================
if __name__ == "__main__":
    pass
