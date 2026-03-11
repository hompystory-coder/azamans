"""
🌟 통합 실시간 주식 모니터링 - Premium Edition
삼성전자, 현대차, 테슬라, S&P500
"""

import streamlit as st
import pandas as pd
import numpy as np
import plotly.graph_objects as go
from datetime import datetime, timedelta
import sys
import os
import time
import json
from pathlib import Path

# 경로 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

# 데이터 저장 디렉토리
DATA_DIR = Path(__file__).parent.parent.parent / "data" / "trading_data"
DATA_DIR.mkdir(parents=True, exist_ok=True)
TRADE_HISTORY_FILE = DATA_DIR / "trade_history.json"
PORTFOLIO_FILE = DATA_DIR / "portfolio.json"

def save_trade_history():
    """거래 히스토리를 JSON 파일로 저장"""
    try:
        with open(TRADE_HISTORY_FILE, 'w', encoding='utf-8') as f:
            json.dump(st.session_state.trade_history, f, ensure_ascii=False, indent=2)
        return True
    except Exception as e:
        st.error(f"저장 실패: {str(e)}")
        return False

def load_trade_history():
    """저장된 거래 히스토리 불러오기"""
    try:
        if TRADE_HISTORY_FILE.exists():
            with open(TRADE_HISTORY_FILE, 'r', encoding='utf-8') as f:
                return json.load(f)
        return []
    except Exception as e:
        st.error(f"불러오기 실패: {str(e)}")
        return []

def save_portfolio():
    """포트폴리오를 JSON 파일로 저장"""
    try:
        with open(PORTFOLIO_FILE, 'w', encoding='utf-8') as f:
            json.dump(st.session_state.portfolio, f, ensure_ascii=False, indent=2)
        return True
    except Exception as e:
        st.error(f"포트폴리오 저장 실패: {str(e)}")
        return False

def load_portfolio():
    """저장된 포트폴리오 불러오기"""
    try:
        if PORTFOLIO_FILE.exists():
            with open(PORTFOLIO_FILE, 'r', encoding='utf-8') as f:
                return json.load(f)
        return {}
    except Exception as e:
        st.error(f"포트폴리오 불러오기 실패: {str(e)}")
        return {}

try:
    from data.fetcher import StockDataFetcher
except Exception as e:
    st.error(f"모듈 로드 실패: {str(e)}")
    st.stop()

# 페이지 설정
st.set_page_config(
    page_title="통합 실시간 - AI 주식 도우미",
    page_icon="📊",
    layout="wide",
    initial_sidebar_state="expanded"
)

# 🎨 프리미엄 CSS 스타일 - 밝은 텍스트
st.markdown("""
<style>
    /* 전체 배경 - 프리미엄 다크 그라데이션 */
    .stApp {
        background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%) !important;
    }
    
    /* 메인 컨테이너 */
    .main .block-container {
        padding-top: 2rem;
        padding-bottom: 2rem;
        max-width: 1400px;
    }
    
    /* 사이드바 */
    [data-testid="stSidebar"] {
        background: linear-gradient(180deg, rgba(15, 12, 41, 0.98) 0%, rgba(36, 36, 62, 0.98) 100%) !important;
        border-right: 1px solid rgba(102, 126, 234, 0.2);
    }
    
    [data-testid="stSidebar"] * {
        color: #ffffff !important;
    }
    
    /* 모든 텍스트 요소를 밝은 색으로 강제 */
    body, p, span, div, label, input, textarea, select, option, 
    h1, h2, h3, h4, h5, h6, li, td, th, a {
        color: #ffffff !important;
    }
    
    /* Streamlit 기본 텍스트 */
    .stMarkdown, .stMarkdown p, .stMarkdown span, .stMarkdown div {
        color: #ffffff !important;
    }
    
    /* 모든 텍스트를 흰색으로 */
    * {
        color: #ffffff !important;
    }
    
    /* 프리미엄 헤더 */
    .premium-header {
        text-align: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 3.5rem;
        font-weight: 900;
        letter-spacing: 3px;
        margin: 20px 0;
        text-shadow: 0 0 30px rgba(102, 126, 234, 0.5);
        animation: shine 3s ease-in-out infinite;
    }
    
    @keyframes shine {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    
    /* 서브 헤더 */
    .premium-subheader {
        text-align: center;
        color: #ffff00 !important;
        font-size: 1.4rem;
        font-weight: 500;
        margin-bottom: 40px;
        letter-spacing: 1px;
        text-shadow: 0 2px 8px rgba(255, 255, 0, 0.5);
    }
    
    /* 프리미엄 카드 */
    .premium-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 30px;
        margin: 20px 0;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 
            0 8px 32px rgba(0, 0, 0, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.1),
            0 0 0 1px rgba(102, 126, 234, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .premium-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent, 
            rgba(255, 255, 255, 0.1), 
            transparent);
        transition: left 0.5s;
    }
    
    .premium-card:hover::before {
        left: 100%;
    }
    
    .premium-card:hover {
        transform: translateY(-8px);
        border-color: rgba(102, 126, 234, 0.3);
        box-shadow: 
            0 16px 48px rgba(102, 126, 234, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.2),
            0 0 0 1px rgba(102, 126, 234, 0.3);
    }
    
    /* 카드 내부 텍스트 - 모두 밝은 색 */
    .premium-card h2, .premium-card h3, .premium-card h4 {
        color: #ffff00 !important;
        text-shadow: 0 2px 8px rgba(255, 255, 0, 0.5);
        font-weight: 900;
    }
    
    .premium-card p, .premium-card span, .premium-card div, .premium-card label {
        color: #ffffff !important;
    }
    
    /* 가격 표시 - 럭셔리 */
    .luxury-price {
        font-size: 3rem;
        font-weight: 900;
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ff6b6b 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        margin: 20px 0;
        letter-spacing: 2px;
    }
    
    /* 시그널 배지 - 3D 효과 */
    .signal-badge-3d {
        display: inline-block;
        padding: 12px 28px;
        border-radius: 30px;
        font-weight: 900;
        font-size: 1.3rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        position: relative;
        transform-style: preserve-3d;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .signal-badge-3d::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 30px;
        background: inherit;
        transform: translateZ(-2px);
        filter: blur(8px);
        opacity: 0.7;
    }
    
    .signal-badge-3d:hover {
        transform: translateY(-3px) scale(1.05);
    }
    
    .signal-buy-3d {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white !important;
        box-shadow: 
            0 8px 20px rgba(17, 153, 142, 0.4),
            0 0 40px rgba(56, 239, 125, 0.3);
    }
    
    .signal-sell-3d {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        color: white !important;
        box-shadow: 
            0 8px 20px rgba(235, 51, 73, 0.4),
            0 0 40px rgba(244, 92, 67, 0.3);
    }
    
    .signal-hold-3d {
        background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
        color: white !important;
        box-shadow: 
            0 8px 20px rgba(242, 153, 74, 0.4),
            0 0 40px rgba(242, 201, 76, 0.3);
    }
    
    /* 변동률 */
    .change-up {
        color: #38ef7d !important;
        font-weight: 900;
        font-size: 1.5rem;
        text-shadow: 0 0 15px rgba(56, 239, 125, 0.6);
        animation: pulse 2s ease-in-out infinite;
    }
    
    .change-down {
        color: #f45c43 !important;
        font-weight: 900;
        font-size: 1.5rem;
        text-shadow: 0 0 15px rgba(244, 92, 67, 0.6);
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    /* 입력 그룹 - 네온 */
    .neon-input-group {
        background: rgba(102, 126, 234, 0.05);
        backdrop-filter: blur(10px);
        padding: 24px;
        border-radius: 20px;
        margin: 20px 0;
        border: 2px solid rgba(102, 126, 234, 0.2);
        box-shadow: 
            0 4px 20px rgba(0, 0, 0, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    
    .neon-input-group:hover {
        border-color: rgba(102, 126, 234, 0.5);
        box-shadow: 
            0 8px 30px rgba(102, 126, 234, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }
    
    .neon-input-group h4 {
        color: #ffff00 !important;
        font-weight: 900;
        font-size: 1.2rem;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 2px;
        text-shadow: 0 0 10px rgba(255, 255, 0, 0.7);
    }
    
    /* 입력 그룹 내부 모든 텍스트 */
    .neon-input-group label, .neon-input-group p, .neon-input-group span {
        color: #ffffff !important;
    }
    
    /* 프리미엄 버튼 */
    .stButton > button {
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        border: none;
        border-radius: 16px;
        padding: 16px 32px;
        font-weight: 900;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow: 
            0 8px 24px rgba(102, 126, 234, 0.4),
            inset 0 2px 0 rgba(255, 255, 255, 0.2);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .stButton > button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .stButton > button:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .stButton > button:hover {
        transform: translateY(-4px);
        box-shadow: 
            0 12px 36px rgba(102, 126, 234, 0.6),
            inset 0 2px 0 rgba(255, 255, 255, 0.3);
    }
    
    .stButton > button:active {
        transform: translateY(-1px);
    }
    
    /* 입력 필드 라벨 */
    .stNumberInput label, .stDateInput label {
        color: #ffff00 !important;
        font-weight: 900;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-shadow: 0 0 10px rgba(255, 255, 0, 0.5);
    }
    
    /* 입력 필드 - 밝은 배경 + 어두운 텍스트 */
    .stNumberInput input, .stDateInput input {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 2px solid rgba(255, 255, 0, 0.5) !important;
        border-radius: 12px !important;
        color: #1a1a2e !important;
        padding: 12px !important;
        font-size: 1.2rem !important;
        font-weight: 900 !important;
        transition: all 0.3s ease;
        text-shadow: none !important;
    }
    
    /* 입력 필드 placeholder */
    .stNumberInput input::placeholder, .stDateInput input::placeholder {
        color: rgba(26, 26, 46, 0.5) !important;
        font-weight: 600 !important;
    }
    
    /* 입력 필드 포커스 */
    .stNumberInput input:focus, .stDateInput input:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.3) !important;
        background: rgba(255, 255, 255, 1) !important;
        color: #0f0c29 !important;
    }
    
    /* 달력 위젯 스타일링 - 강력한 텍스트 가시성 */
    /* 달력 컨테이너 */
    [data-baseweb="calendar"],
    [data-baseweb="calendar"] *,
    .stDateInput [data-baseweb="calendar"],
    .stDateInput [data-baseweb="calendar"] * {
        background: rgba(255, 255, 255, 0.98) !important;
        border-radius: 12px !important;
        padding: 10px !important;
    }
    
    /* 달력 헤더 (월/년도) - 매우 진하게 */
    [data-baseweb="calendar"] [role="heading"],
    [data-baseweb="calendar"] button,
    [data-baseweb="calendar"] [role="heading"] *,
    [data-baseweb="calendar"] button * {
        color: #000000 !important;
        font-weight: 900 !important;
        font-size: 1.1rem !important;
    }
    
    /* 달력 모든 텍스트 강제 */
    [data-baseweb="calendar"] div,
    [data-baseweb="calendar"] span,
    [data-baseweb="calendar"] p {
        color: #000000 !important;
        font-weight: 700 !important;
    }
    
    /* 달력 요일 헤더 - 검정색 */
    [data-baseweb="calendar"] thead th,
    [data-baseweb="calendar"] thead th *,
    [data-baseweb="calendar"] [role="columnheader"],
    [data-baseweb="calendar"] [role="columnheader"] * {
        color: #000000 !important;
        font-weight: 900 !important;
        font-size: 1rem !important;
        background: transparent !important;
    }
    
    /* 달력 날짜 셀 - 검정색 */
    [data-baseweb="calendar"] td,
    [data-baseweb="calendar"] td *,
    [data-baseweb="calendar"] tbody td,
    [data-baseweb="calendar"] tbody td * {
        color: #000000 !important;
        font-weight: 700 !important;
        font-size: 1rem !important;
    }
    
    [data-baseweb="calendar"] td button,
    [data-baseweb="calendar"] td button *,
    [data-baseweb="calendar"] [role="gridcell"] button,
    [data-baseweb="calendar"] [role="gridcell"] button * {
        color: #000000 !important;
        font-weight: 700 !important;
        background: transparent !important;
    }
    
    /* 달력 날짜 호버 */
    [data-baseweb="calendar"] td button:hover,
    [data-baseweb="calendar"] td button:hover * {
        background: rgba(102, 126, 234, 0.2) !important;
        color: #000000 !important;
        font-weight: 900 !important;
    }
    
    /* 달력 선택된 날짜 */
    [data-baseweb="calendar"] [aria-selected="true"],
    [data-baseweb="calendar"] [aria-selected="true"] * {
        background: #667eea !important;
        color: #ffffff !important;
        font-weight: 900 !important;
    }
    
    /* 달력 오늘 날짜 */
    [data-baseweb="calendar"] [data-highlighted="true"],
    [data-baseweb="calendar"] [data-highlighted="true"] * {
        border: 2px solid #667eea !important;
        color: #000000 !important;
        font-weight: 900 !important;
    }
    
    /* 달력 이전/다음 달 화살표 */
    [data-baseweb="calendar"] svg,
    [data-baseweb="calendar"] svg * {
        fill: #000000 !important;
        color: #000000 !important;
    }
    
    /* 달력 비활성 날짜 */
    [data-baseweb="calendar"] [disabled],
    [data-baseweb="calendar"] [disabled] * {
        color: rgba(0, 0, 0, 0.3) !important;
    }
    
    /* 달력 내부 모든 요소 강제 검정색 */
    [data-baseweb="calendar"] * {
        color: #000000 !important;
    }
    
    /* 체크박스 */
    .stCheckbox label {
        color: #ffff00 !important;
        font-weight: 900;
        font-size: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-shadow: 0 0 10px rgba(255, 255, 0, 0.5);
    }
    
    /* 섹션 제목 */
    h3 {
        color: #ffff00 !important;
        font-weight: 900 !important;
        font-size: 1.8rem !important;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 30px 0 20px 0 !important;
        text-shadow: 0 0 15px rgba(255, 255, 0, 0.7);
    }
    
    /* Metric 카드 */
    [data-testid="stMetricValue"] {
        font-size: 2rem !important;
        font-weight: 900 !important;
        color: #ffff00 !important;
        text-shadow: 0 0 10px rgba(255, 255, 0, 0.5);
    }
    
    [data-testid="stMetricLabel"] {
        color: #ffffff !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    [data-testid="stMetricDelta"] {
        color: #00ff00 !important;
        font-weight: 900 !important;
        text-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
    }
        font-size: 1.2rem;
        text-transform: uppercase;
    }
    
    /* 셀렉트박스 라벨 */
    .stSelectbox label {
        color: #ffff00 !important;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-shadow: 0 0 10px rgba(255, 255, 0, 0.5);
    }
    
    /* 셀렉트박스 - 밝은 배경 + 어두운 텍스트 */
    .stSelectbox > div > div {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 2px solid rgba(255, 255, 0, 0.5) !important;
        border-radius: 12px !important;
        color: #1a1a2e !important;
    }
    
    /* 셀렉트박스 옵션 */
    .stSelectbox [data-baseweb="select"] {
        background: rgba(255, 255, 255, 0.95) !important;
    }
    
    .stSelectbox [data-baseweb="select"] > div {
        color: #1a1a2e !important;
        font-weight: 700 !important;
    }
    
    /* 드롭다운 메뉴 */
    [data-baseweb="popover"] {
        background: rgba(255, 255, 255, 0.98) !important;
    }
    
    [role="option"] {
        color: #1a1a2e !important;
        font-weight: 600 !important;
    }
    
    [role="option"]:hover {
        background: rgba(102, 126, 234, 0.2) !important;
        color: #0f0c29 !important;
    }
    
    /* 캡션 */
    .stCaption {
        color: #ffffff !important;
        font-weight: 600;
    }
    /* 스크롤바 */
    ::-webkit-scrollbar {
        width: 12px;
        height: 12px;
    }
    
    ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 10px;
        border: 2px solid rgba(0, 0, 0, 0.2);
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #764ba2, #667eea);
    }
    
    /* 푸터 */
    .premium-footer {
        text-align: center;
        color: #ffff00 !important;
        font-size: 1.1rem;
        margin-top: 60px;
        padding: 30px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 0, 0.2);
        backdrop-filter: blur(10px);
        text-shadow: 0 0 10px rgba(255, 255, 0, 0.5);
    }
</style>
""", unsafe_allow_html=True)

# 헤더
st.markdown('<div class="premium-header">💎 통합 실시간 주식 모니터링</div>', unsafe_allow_html=True)
st.markdown('<div class="premium-subheader">🌟 Premium Trading Suite | AI 자산 분석 | 3일 추세 예측</div>', unsafe_allow_html=True)

# 감시 대상 주식
STOCKS = [
    {"ticker": "005930.KQ", "name": "삼성전자", "icon": "📱", "market": "KR"},
    {"ticker": "005380.KS", "name": "현대차", "icon": "🚗", "market": "KR"},
    {"ticker": "TSLA", "name": "테슬라", "icon": "⚡", "market": "US"},
    {"ticker": "379800.KS", "name": "KODEX 미국S&P500", "icon": "📈", "market": "KR"}
]

# 세션 상태 초기화
if 'last_update' not in st.session_state:
    st.session_state.last_update = None

if 'portfolio' not in st.session_state:
    # 저장된 포트폴리오 불러오기
    loaded_portfolio = load_portfolio()
    if loaded_portfolio:
        st.session_state.portfolio = loaded_portfolio
        # 새로운 종목이 추가되었을 경우 포트폴리오에 추가
        for stock in STOCKS:
            if stock['ticker'] not in st.session_state.portfolio:
                st.session_state.portfolio[stock['ticker']] = {'shares': 0, 'avg_price': 0}
        # 포트폴리오 업데이트 저장
        save_portfolio()
    else:
        st.session_state.portfolio = {stock['ticker']: {'shares': 0, 'avg_price': 0} for stock in STOCKS}
else:
    # 이미 세션에 포트폴리오가 있어도 새로운 종목 확인
    for stock in STOCKS:
        if stock['ticker'] not in st.session_state.portfolio:
            st.session_state.portfolio[stock['ticker']] = {'shares': 0, 'avg_price': 0}
            save_portfolio()

if 'trade_history' not in st.session_state:
    # 저장된 거래 히스토리 불러오기
    loaded_history = load_trade_history()
    st.session_state.trade_history = loaded_history if loaded_history else []

# 수정 모드 상태
if 'edit_mode' not in st.session_state:
    st.session_state.edit_mode = {}

if 'edit_index' not in st.session_state:
    st.session_state.edit_index = None

# 자동 새로고침 (기본값 True로 변경)
auto_refresh = st.checkbox("🔄 자동 새로고침 (30초)", value=True)

def calculate_3day_trend(df):
    """3일 연속 추세 판단"""
    try:
        if len(df) < 3:
            return "HOLD", "데이터 부족", "⚪"
        
        recent_3 = df['Close'].tail(3).values
        
        if recent_3[2] > recent_3[1] > recent_3[0]:
            return "BUY", "3일 연속 상승 - 매수 신호", "🟢"
        elif recent_3[2] < recent_3[1] < recent_3[0]:
            return "SELL", "3일 연속 하락 - 매도 신호", "🔴"
        else:
            return "HOLD", "관망", "🟡"
    except Exception as e:
        return "HOLD", "분석 오류", "⚪"

def get_ai_recommendation(ticker, current_price, avg_price, shares, trend_signal, df):
    """AI 추천 시스템"""
    try:
        if shares == 0:
            if trend_signal == "BUY":
                return {
                    'action': 'BUY',
                    'icon': '🟢',
                    'title': '신규 매수 추천',
                    'reason': '3일 연속 상승세',
                    'suggestion': f'추천가: {current_price:,.0f}',
                    'confidence': 'HIGH'
                }
            else:
                return {
                    'action': 'WAIT',
                    'icon': '⏸️',
                    'title': '관망 추천',
                    'reason': '매수 신호 대기',
                    'suggestion': '3일 상승 시 매수',
                    'confidence': 'MEDIUM'
                }
        
        profit_pct = ((current_price - avg_price) / avg_price * 100) if avg_price > 0 else 0
        
        if profit_pct >= 10:
            if trend_signal == "SELL":
                return {
                    'action': 'SELL',
                    'icon': '🔴',
                    'title': '익절 매도 추천',
                    'reason': f'수익률 {profit_pct:.1f}% + 하락세',
                    'suggestion': f'{shares}주 전량 매도',
                    'confidence': 'VERY HIGH'
                }
            elif trend_signal == "BUY":
                return {
                    'action': 'HOLD_OR_ADD',
                    'icon': '🟢',
                    'title': '보유 또는 추가 매수',
                    'reason': f'수익률 {profit_pct:.1f}% + 상승세',
                    'suggestion': '추가 매수 또는 보유',
                    'confidence': 'HIGH'
                }
            else:
                return {
                    'action': 'HOLD',
                    'icon': '💎',
                    'title': '보유 추천',
                    'reason': f'수익률 {profit_pct:.1f}%',
                    'suggestion': '추세 관찰',
                    'confidence': 'MEDIUM'
                }
        
        elif profit_pct <= -5:
            if trend_signal == "SELL":
                return {
                    'action': 'SELL',
                    'icon': '🔴',
                    'title': '손절 매도 추천',
                    'reason': f'손실 {abs(profit_pct):.1f}% + 하락세',
                    'suggestion': '손절 권장',
                    'confidence': 'HIGH'
                }
            elif trend_signal == "BUY":
                return {
                    'action': 'HOLD_OR_ADD',
                    'icon': '🟢',
                    'title': '물타기 또는 보유',
                    'reason': f'손실 {abs(profit_pct):.1f}% + 반등 신호',
                    'suggestion': '평단 낮추기',
                    'confidence': 'MEDIUM'
                }
            else:
                return {
                    'action': 'HOLD',
                    'icon': '⚠️',
                    'title': '신중한 보유',
                    'reason': f'손실 {abs(profit_pct):.1f}%',
                    'suggestion': '추세 전환 대기',
                    'confidence': 'LOW'
                }
        
        else:
            if trend_signal == "BUY":
                return {
                    'action': 'HOLD_OR_ADD',
                    'icon': '🟢',
                    'title': '추가 매수 고려',
                    'reason': f'수익률 {profit_pct:+.1f}% + 상승',
                    'suggestion': '추가 매수 가능',
                    'confidence': 'MEDIUM'
                }
            elif trend_signal == "SELL":
                return {
                    'action': 'HOLD',
                    'icon': '⚠️',
                    'title': '보유 및 관찰',
                    'reason': f'수익률 {profit_pct:+.1f}% + 하락',
                    'suggestion': '추가 하락 시 손절',
                    'confidence': 'MEDIUM'
                }
            else:
                return {
                    'action': 'HOLD',
                    'icon': '💎',
                    'title': '보유 추천',
                    'reason': f'수익률 {profit_pct:+.1f}%',
                    'suggestion': '현재 추세 관찰',
                    'confidence': 'MEDIUM'
                }
    except Exception as e:
        return {
            'action': 'WAIT',
            'icon': '⚠️',
            'title': '분석 오류',
            'reason': str(e),
            'suggestion': '나중에 다시 확인',
            'confidence': 'LOW'
        }

def analyze_portfolio_performance():
    """포트폴리오 분석"""
    try:
        if not st.session_state.trade_history:
            return None
        
        total_invested = sum(h['total'] for h in st.session_state.trade_history if h['action'] == 'BUY')
        total_sell = sum(h['total'] for h in st.session_state.trade_history if h['action'] == 'SELL')
        total_realized_profit = sum(h.get('profit', 0) for h in st.session_state.trade_history if h['action'] == 'SELL')
        
        total_current_value = sum(
            holdings['shares'] * holdings['avg_price'] 
            for holdings in st.session_state.portfolio.values() 
            if holdings['shares'] > 0
        )
        
        total_profit = total_realized_profit + (total_current_value - (total_invested - total_sell))
        total_profit_pct = (total_profit / total_invested * 100) if total_invested > 0 else 0
        
        if total_profit_pct >= 15:
            performance = "🌟 매우 우수"
            advice = "훌륭한 투자 성과! 현재 전략 유지하세요."
        elif total_profit_pct >= 5:
            performance = "✅ 우수"
            advice = "좋은 성과입니다. 계속 모니터링하세요."
        elif total_profit_pct >= 0:
            performance = "📊 양호"
            advice = "안정적인 수익을 유지 중입니다."
        elif total_profit_pct >= -5:
            performance = "⚠️ 주의"
            advice = "일부 손실. 포트폴리오 재조정 고려하세요."
        else:
            performance = "🚨 경고"
            advice = "큰 손실. 전략 재검토가 필요합니다."
        
        return {
            'total_invested': total_invested,
            'total_current_value': total_current_value,
            'total_realized_profit': total_realized_profit,
            'total_profit': total_profit,
            'total_profit_pct': total_profit_pct,
            'total_trades': len(st.session_state.trade_history),
            'buy_count': sum(1 for h in st.session_state.trade_history if h['action'] == 'BUY'),
            'sell_count': sum(1 for h in st.session_state.trade_history if h['action'] == 'SELL'),
            'performance': performance,
            'advice': advice
        }
    except Exception as e:
        st.error(f"포트폴리오 분석 오류: {str(e)}")
        return None

def get_realtime_data(ticker, market):
    """실시간 데이터 가져오기"""
    try:
        fetcher = StockDataFetcher()
        end_date = datetime.now().strftime('%Y-%m-%d')
        start_date = (datetime.now() - timedelta(days=30)).strftime('%Y-%m-%d')
        
        # get_stock_data 메서드 사용 (get_historical_data가 아님)
        df = fetcher.get_stock_data(ticker, start_date, end_date)
        
        if df is None or len(df) == 0:
            st.warning(f"⚠️ {ticker} 데이터가 없습니다")
            return None
        
        realtime_info = fetcher.get_realtime_price(ticker)
        current_price = realtime_info.get('price', df['Close'].iloc[-1]) if realtime_info else df['Close'].iloc[-1]
        is_realtime = realtime_info.get('is_realtime', False) if realtime_info else False
        
        prev_close = df['Close'].iloc[-2] if len(df) > 1 else current_price
        change = current_price - prev_close
        change_pct = (change / prev_close * 100) if prev_close > 0 else 0
        
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
        st.error(f"❌ 데이터 로드 실패 ({ticker}): {str(e)}")
        import traceback
        st.code(traceback.format_exc())
        return None

def create_premium_chart(df, current_price, ticker_name):
    """프리미엄 차트 생성"""
    try:
        recent_df = df.tail(3)
        
        fig = go.Figure()
        
        fig.add_trace(go.Candlestick(
            x=recent_df.index,
            open=recent_df['Open'],
            high=recent_df['High'],
            low=recent_df['Low'],
            close=recent_df['Close'],
            name=ticker_name,
            increasing_line_color='#38ef7d',
            decreasing_line_color='#f45c43',
            increasing_fillcolor='rgba(56, 239, 125, 0.3)',
            decreasing_fillcolor='rgba(244, 92, 67, 0.3)'
        ))
        
        fig.add_trace(go.Scatter(
            x=[recent_df.index[-1]],
            y=[current_price],
            mode='markers+text',
            marker=dict(size=20, color='#ffd700', symbol='star', line=dict(width=2, color='white')),
            text=[f'{current_price:,.0f}'],
            textposition='top center',
            textfont=dict(size=14, color='white', family='Arial Black'),
            name='현재가',
            showlegend=False
        ))
        
        fig.update_layout(
            height=350,
            margin=dict(l=20, r=20, t=40, b=20),
            xaxis=dict(
                showgrid=False,
                title="",
                tickformat='%m/%d',
                color='rgba(255, 255, 255, 0.7)'
            ),
            yaxis=dict(
                showgrid=True,
                gridcolor='rgba(255, 255, 255, 0.1)',
                title="",
                color='rgba(255, 255, 255, 0.7)'
            ),
            plot_bgcolor='rgba(0, 0, 0, 0.2)',
            paper_bgcolor='rgba(0, 0, 0, 0)',
            xaxis_rangeslider_visible=False,
            title=dict(
                text=f"<b>{ticker_name} - 최근 3일</b>",
                x=0.5,
                xanchor='center',
                font=dict(size=16, color='white', family='Arial Black')
            ),
            font=dict(color='white')
        )
        
        return fig
    except Exception as e:
        st.error(f"차트 생성 오류: {str(e)}")
        return None

# 포트폴리오 분석
st.markdown("---")
st.markdown("### 📈 포트폴리오 전체 분석")

portfolio_analysis = analyze_portfolio_performance()

if portfolio_analysis:
    col1, col2, col3, col4 = st.columns(4)
    col1.metric("💰 총 투자금", f"{portfolio_analysis['total_invested']:,.0f}원")
    col2.metric("💎 현재 평가액", f"{portfolio_analysis['total_current_value']:,.0f}원")
    col3.metric("📊 실현 손익", f"{portfolio_analysis['total_realized_profit']:,.0f}원")
    col4.metric("🎯 총 수익률", f"{portfolio_analysis['total_profit_pct']:+.2f}%", 
                f"{portfolio_analysis['total_profit']:,.0f}원")
    
    st.markdown(f"""
    <div style="
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
        padding: 25px;
        border-radius: 20px;
        margin: 20px 0;
        border-left: 5px solid #667eea;
        backdrop-filter: blur(10px);
    ">
        <h3 style="margin: 0; color: #ffffff; font-size: 1.5rem;">{portfolio_analysis['performance']} 투자 성과</h3>
        <p style="font-size: 1.1rem; margin: 10px 0; color: rgba(255,255,255,0.9);">
            <strong>거래 횟수:</strong> {portfolio_analysis['total_trades']}회 
            (매수: {portfolio_analysis['buy_count']}, 매도: {portfolio_analysis['sell_count']})
        </p>
        <p style="font-size: 1rem; margin: 10px 0; color: rgba(255,255,255,0.85);">
            <strong>💡 AI 조언:</strong> {portfolio_analysis['advice']}
        </p>
    </div>
    """, unsafe_allow_html=True)
else:
    st.info("💡 거래를 시작하면 포트폴리오 분석이 표시됩니다.")

st.markdown("---")

# 주식 모니터링
for stock in STOCKS:
    ticker = stock['ticker']
    name = stock['name']
    icon = stock['icon']
    market = stock['market']
    
    st.markdown('<div class="premium-card">', unsafe_allow_html=True)
    
    col1, col2, col3 = st.columns([2, 3, 2])
    
    with col1:
        st.markdown(f"## {icon} {name}")
        st.caption(f"티커: {ticker}")
    
    data = get_realtime_data(ticker, market)
    
    if data:
        with col2:
            is_korean = market == "KR"
            price_text = f"{data['current_price']:,.0f}{'원' if is_korean else '$'}"
            st.markdown(f'<div class="luxury-price">{price_text}</div>', unsafe_allow_html=True)
            
            change_class = "change-up" if data['change'] >= 0 else "change-down"
            change_icon = "▲" if data['change'] >= 0 else "▼"
            st.markdown(
                f'<span class="{change_class}">{change_icon} {abs(data["change"]):,.2f} ({abs(data["change_pct"]):.2f}%)</span>',
                unsafe_allow_html=True
            )
        
        with col3:
            signal = data['trend_signal']
            signal_class = "signal-buy-3d" if signal == "BUY" else "signal-sell-3d" if signal == "SELL" else "signal-hold-3d"
            st.markdown(
                f'<div class="signal-badge-3d {signal_class}">{data["trend_icon"]} {signal}</div>',
                unsafe_allow_html=True
            )
            st.caption(data['trend_reason'])
        
        st.markdown("### 📊 최근 3일 차트")
        chart = create_premium_chart(data['df'], data['current_price'], name)
        if chart:
            st.plotly_chart(chart, use_container_width=True)
        
        st.markdown("### 💰 매매 관리")
        
        col_buy, col_sell = st.columns(2)
        
        with col_buy:
            st.markdown('<div class="neon-input-group">', unsafe_allow_html=True)
            st.markdown("<h4>🟢 매수 BUY</h4>", unsafe_allow_html=True)
            
            buy_date = st.date_input("매수 날짜", value=datetime.now(), key=f"buy_date_{ticker}")
            buy_shares = st.number_input("매수 수량", min_value=0, value=0, step=1, key=f"buy_shares_{ticker}")
            buy_price = st.number_input("매수 가격", min_value=0.0, value=float(data['current_price']), step=0.01, key=f"buy_price_{ticker}")
            
            if st.button("매수 실행", key=f"buy_btn_{ticker}"):
                if buy_shares > 0:
                    current = st.session_state.portfolio[ticker]
                    total_shares = current['shares'] + buy_shares
                    total_cost = (current['shares'] * current['avg_price']) + (buy_shares * buy_price)
                    new_avg_price = total_cost / total_shares if total_shares > 0 else 0
                    
                    st.session_state.portfolio[ticker] = {'shares': total_shares, 'avg_price': new_avg_price}
                    
                    st.session_state.trade_history.append({
                        'id': len(st.session_state.trade_history),  # 고유 ID 추가
                        'date': buy_date.strftime("%Y-%m-%d"),
                        'time': datetime.now().strftime("%H:%M:%S"),
                        'ticker': ticker,
                        'name': name,
                        'action': 'BUY',
                        'shares': buy_shares,
                        'price': buy_price,
                        'total': buy_shares * buy_price,
                        'trend': data['trend_signal']
                    })
                    
                    # 데이터 저장
                    save_trade_history()
                    save_portfolio()
                    
                    st.success(f"✅ {buy_shares}주 매수 완료! 데이터가 저장되었습니다.")
                    st.rerun()
                else:
                    st.error("수량을 입력하세요")
            
            st.markdown('</div>', unsafe_allow_html=True)
        
        with col_sell:
            st.markdown('<div class="neon-input-group">', unsafe_allow_html=True)
            st.markdown("<h4>🔴 매도 SELL</h4>", unsafe_allow_html=True)
            
            current_holding = st.session_state.portfolio[ticker]['shares']
            
            sell_date = st.date_input("매도 날짜", value=datetime.now(), key=f"sell_date_{ticker}")
            sell_shares = st.number_input(f"매도 수량 (보유: {current_holding}주)", min_value=0, max_value=current_holding, value=0, step=1, key=f"sell_shares_{ticker}")
            sell_price = st.number_input("매도 가격", min_value=0.0, value=float(data['current_price']), step=0.01, key=f"sell_price_{ticker}")
            
            if st.button("매도 실행", key=f"sell_btn_{ticker}"):
                if sell_shares > 0 and sell_shares <= current_holding:
                    current = st.session_state.portfolio[ticker]
                    new_shares = current['shares'] - sell_shares
                    profit = (sell_price - current['avg_price']) * sell_shares
                    profit_pct = (profit / (current['avg_price'] * sell_shares) * 100) if current['avg_price'] > 0 else 0
                    
                    st.session_state.portfolio[ticker] = {'shares': new_shares, 'avg_price': current['avg_price'] if new_shares > 0 else 0}
                    
                    st.session_state.trade_history.append({
                        'id': len(st.session_state.trade_history),  # 고유 ID 추가
                        'date': sell_date.strftime("%Y-%m-%d"),
                        'time': datetime.now().strftime("%H:%M:%S"),
                        'ticker': ticker,
                        'name': name,
                        'action': 'SELL',
                        'shares': sell_shares,
                        'price': sell_price,
                        'total': sell_shares * sell_price,
                        'profit': profit,
                        'profit_pct': profit_pct,
                        'trend': data['trend_signal']
                    })
                    
                    # 데이터 저장
                    save_trade_history()
                    save_portfolio()
                    
                    profit_icon = "📈" if profit >= 0 else "📉"
                    st.success(f"✅ {sell_shares}주 매도 완료! {profit_icon} 수익: {profit:,.2f} ({profit_pct:+.2f}%) - 데이터가 저장되었습니다.")
                    st.rerun()
                elif sell_shares > current_holding:
                    st.error("보유 수량 초과")
                else:
                    st.error("수량을 입력하세요")
            
            st.markdown('</div>', unsafe_allow_html=True)
        
        # AI 추천
        current_holding = st.session_state.portfolio[ticker]['shares']
        avg_price = st.session_state.portfolio[ticker]['avg_price']
        
        ai_rec = get_ai_recommendation(ticker, data['current_price'], avg_price, current_holding, data['trend_signal'], data['df'])
        
        st.markdown("---")
        st.markdown("### 🤖 AI 매매 추천")
        
        confidence_color = {'VERY HIGH': '#38ef7d', 'HIGH': '#66bb6a', 'MEDIUM': '#f2c94c', 'LOW': '#f45c43'}
        
        st.markdown(f"""
        <div style="
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
            border-left: 5px solid {confidence_color.get(ai_rec['confidence'], '#667eea')};
            padding: 25px;
            border-radius: 20px;
            margin: 20px 0;
            backdrop-filter: blur(10px);
        ">
            <h3 style="margin: 0; color: #ffffff; font-size: 1.4rem;">{ai_rec['icon']} {ai_rec['title']}</h3>
            <p style="font-size: 1.1rem; margin: 15px 0; color: rgba(255,255,255,0.95);">
                <strong>📊 판단 근거:</strong> {ai_rec['reason']}
            </p>
            <p style="font-size: 1rem; margin: 10px 0; color: rgba(255,255,255,0.9);">
                <strong>💡 추천 사항:</strong> {ai_rec['suggestion']}
            </p>
            <p style="font-size: 0.9rem; margin: 10px 0; color: {confidence_color.get(ai_rec['confidence'], '#667eea')};">
                <strong>🎯 신뢰도:</strong> {ai_rec['confidence']}
            </p>
        </div>
        """, unsafe_allow_html=True)
        
        # 보유 현황
        if current_holding > 0:
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
        st.error(f"❌ {name} 데이터를 불러올 수 없습니다")
    
    st.markdown('</div>', unsafe_allow_html=True)

# 거래 히스토리
if len(st.session_state.trade_history) > 0:
    st.markdown("---")
    st.markdown('<h2 style="text-align: center; color: white; font-size: 2.5rem; margin: 40px 0;">📜 거래 히스토리</h2>', unsafe_allow_html=True)
    
    st.markdown('<div class="premium-card">', unsafe_allow_html=True)
    
    col_filter1, col_filter2, col_filter3 = st.columns([2, 2, 1])
    
    with col_filter1:
        filter_ticker = st.selectbox("종목 필터", ["전체"] + [s['ticker'] for s in STOCKS], key="history_ticker_filter")
    
    with col_filter2:
        filter_action = st.selectbox("거래 유형", ["전체", "매수", "매도"], key="history_action_filter")
    
    with col_filter3:
        st.markdown("<br>", unsafe_allow_html=True)
        if st.button("🗑️ 전체 초기화"):
            st.session_state.trade_history = []
            st.session_state.portfolio = {stock['ticker']: {'shares': 0, 'avg_price': 0} for stock in STOCKS}
            save_trade_history()
            save_portfolio()
            st.success("✅ 모든 거래 데이터가 초기화되었습니다!")
            st.rerun()
    
    filtered_history = st.session_state.trade_history[::-1]
    
    if filter_ticker != "전체":
        filtered_history = [h for h in filtered_history if h['ticker'] == filter_ticker]
    
    if filter_action == "매수":
        filtered_history = [h for h in filtered_history if h['action'] == 'BUY']
    elif filter_action == "매도":
        filtered_history = [h for h in filtered_history if h['action'] == 'SELL']
    
    if len(filtered_history) > 0:
        total_buy = sum(h['total'] for h in filtered_history if h['action'] == 'BUY')
        total_sell = sum(h['total'] for h in filtered_history if h['action'] == 'SELL')
        total_profit = sum(h.get('profit', 0) for h in filtered_history if h['action'] == 'SELL')
        
        st.markdown("---")
        
        col_stat1, col_stat2, col_stat3, col_stat4 = st.columns(4)
        col_stat1.metric("총 거래", f"{len(filtered_history)}회")
        col_stat2.metric("총 매수액", f"{total_buy:,.0f}")
        col_stat3.metric("총 매도액", f"{total_sell:,.0f}")
        col_stat4.metric("실현 손익", f"{total_profit:,.0f}", f"{(total_profit/total_buy*100) if total_buy > 0 else 0:+.2f}%")
        
        st.markdown("---")
        
        for idx, trade in enumerate(filtered_history):
            # 원본 인덱스 찾기 (역순이므로)
            original_idx = len(st.session_state.trade_history) - 1 - idx
            trade_id = trade.get('id', original_idx)
            
            action_color = "#38ef7d" if trade['action'] == 'BUY' else "#f45c43"
            action_text = "매수" if trade['action'] == 'BUY' else "매도"
            action_icon = "🟢" if trade['action'] == 'BUY' else "🔴"
            
            trend_badge = {'BUY': '🟢 상승', 'SELL': '🔴 하락', 'HOLD': '🟡 관망'}.get(trade['trend'], '⚪ -')
            
            profit_text = ""
            if trade['action'] == 'SELL' and 'profit' in trade:
                profit_icon = "📈" if trade['profit'] >= 0 else "📉"
                profit_text = f"<br><strong>수익:</strong> {profit_icon} {trade['profit']:,.2f} ({trade['profit_pct']:+.2f}%)"
            
            # 수정 모드인지 확인
            is_editing = st.session_state.edit_index == original_idx
            
            if is_editing:
                # 수정 모드 UI
                st.markdown(f"""
                <div style="
                    border-left: 5px solid #667eea;
                    padding: 20px;
                    margin: 15px 0;
                    background: rgba(102, 126, 234, 0.2);
                    border-radius: 16px;
                    backdrop-filter: blur(10px);
                ">
                    <h4 style="margin: 0; color: #667eea; font-size: 1.2rem;">✏️ 거래 정보 수정 중</h4>
                </div>
                """, unsafe_allow_html=True)
                
                col_edit1, col_edit2 = st.columns(2)
                
                with col_edit1:
                    edit_date = st.date_input("날짜", value=datetime.strptime(trade['date'], "%Y-%m-%d"), key=f"edit_date_{original_idx}")
                    edit_shares = st.number_input("수량", min_value=1, value=trade['shares'], step=1, key=f"edit_shares_{original_idx}")
                    edit_price = st.number_input("가격", min_value=0.01, value=trade['price'], step=0.01, key=f"edit_price_{original_idx}")
                
                with col_edit2:
                    st.markdown(f"**종목:** {trade['name']} ({trade['ticker']})")
                    st.markdown(f"**거래유형:** {action_icon} {action_text}")
                    st.markdown(f"**원래 금액:** {trade['total']:,.2f}")
                    new_total = edit_shares * edit_price
                    st.markdown(f"**수정 금액:** {new_total:,.2f}")
                
                col_btn1, col_btn2, col_btn3 = st.columns(3)
                
                with col_btn1:
                    if st.button("💾 저장", key=f"save_edit_{original_idx}"):
                        # 거래 정보 업데이트
                        st.session_state.trade_history[original_idx]['date'] = edit_date.strftime("%Y-%m-%d")
                        st.session_state.trade_history[original_idx]['shares'] = edit_shares
                        st.session_state.trade_history[original_idx]['price'] = edit_price
                        st.session_state.trade_history[original_idx]['total'] = edit_shares * edit_price
                        
                        # 매도인 경우 수익 재계산
                        if trade['action'] == 'SELL':
                            # 해당 티커의 평균 매수가 찾기
                            buy_trades = [t for t in st.session_state.trade_history if t['ticker'] == trade['ticker'] and t['action'] == 'BUY']
                            if buy_trades:
                                avg_buy_price = sum(t['total'] for t in buy_trades) / sum(t['shares'] for t in buy_trades)
                                profit = (edit_price - avg_buy_price) * edit_shares
                                profit_pct = (profit / (avg_buy_price * edit_shares) * 100) if avg_buy_price > 0 else 0
                                st.session_state.trade_history[original_idx]['profit'] = profit
                                st.session_state.trade_history[original_idx]['profit_pct'] = profit_pct
                        
                        # 저장
                        save_trade_history()
                        st.session_state.edit_index = None
                        st.success("✅ 거래 정보가 수정되었습니다!")
                        st.rerun()
                
                with col_btn2:
                    if st.button("❌ 취소", key=f"cancel_edit_{original_idx}"):
                        st.session_state.edit_index = None
                        st.rerun()
                
                with col_btn3:
                    if st.button("🗑️ 삭제", key=f"delete_from_edit_{original_idx}"):
                        del st.session_state.trade_history[original_idx]
                        save_trade_history()
                        st.session_state.edit_index = None
                        st.success("✅ 거래가 삭제되었습니다!")
                        st.rerun()
            
            else:
                # 일반 표시 모드
                st.markdown(f"""
                <div style="
                    border-left: 5px solid {action_color};
                    padding: 20px;
                    margin: 15px 0;
                    background: rgba(255, 255, 255, 0.03);
                    border-radius: 16px;
                    backdrop-filter: blur(10px);
                ">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="margin: 0; color: {action_color}; font-size: 1.2rem;">{action_icon} {action_text} - {trade['name']} ({trade['ticker']})</h4>
                            <p style="margin: 5px 0; color: rgba(255,255,255,0.7); font-size: 0.95rem;">📅 {trade['date']} {trade.get('time', '')}</p>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 0; font-size: 1.4rem; font-weight: bold; color: white;">{trade['shares']}주</p>
                            <p style="margin: 5px 0; color: rgba(255,255,255,0.8);">@ {trade['price']:,.2f}</p>
                        </div>
                    </div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.9);">
                        <strong>거래금액:</strong> {trade['total']:,.2f} | 
                        <strong>당시 추세:</strong> {trend_badge}
                        {profit_text}
                    </div>
                </div>
                """, unsafe_allow_html=True)
                
                # 수정/삭제 버튼
                col_action1, col_action2, col_action3 = st.columns([8, 1, 1])
                
                with col_action2:
                    if st.button("✏️", key=f"edit_btn_{original_idx}", help="수정"):
                        st.session_state.edit_index = original_idx
                        st.rerun()
                
                with col_action3:
                    if st.button("🗑️", key=f"delete_btn_{original_idx}", help="삭제"):
                        del st.session_state.trade_history[original_idx]
                        save_trade_history()
                        st.success("✅ 거래가 삭제되었습니다!")
                        st.rerun()
    
    st.markdown('</div>', unsafe_allow_html=True)

# 푸터
st.markdown(f'<div class="premium-footer">⏰ 마지막 업데이트: {datetime.now().strftime("%Y-%m-%d %H:%M:%S")} | 💎 Premium Trading Suite | 📊 AI 자산 분석 시스템</div>', unsafe_allow_html=True)

# 자동 새로고침
if auto_refresh:
    time.sleep(30)
    st.rerun()
