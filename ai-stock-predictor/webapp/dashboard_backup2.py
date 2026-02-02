"""
AI 주식 분석 - 원페이지 UI
간단하고 직관적인 단일 페이지 디자인
"""

import streamlit as st
import pandas as pd
import numpy as np
import plotly.graph_objects as go
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
from analysis.final_decision import InvestmentDecisionEngine, get_decision_emoji, get_decision_color, get_decision_name_kr

# 페이지 설정
st.set_page_config(
    page_title="AI 주식 도우미",
    page_icon="📈",
    layout="wide"
)

# CSS 스타일
st.markdown("""
<style>
    .stApp {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .main-title {
        text-align: center;
        color: white;
        font-size: 3rem;
        font-weight: bold;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .search-box {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        margin-bottom: 2rem;
    }
    
    .result-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        margin-bottom: 1.5rem;
    }
    
    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .decision-banner {
        padding: 2rem;
        border-radius: 20px;
        text-align: center;
        font-size: 2rem;
        font-weight: bold;
        color: white;
        margin: 2rem 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .quick-button {
        background: white;
        border: 2px solid #667eea;
        color: #667eea;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .quick-button:hover {
        background: #667eea;
        color: white;
    }
</style>
""", unsafe_allow_html=True)

# 타이틀
st.markdown('<h1 class="main-title">📈 AI 주식 도우미</h1>', unsafe_allow_html=True)
st.markdown('<p style="text-align: center; color: white; font-size: 1.2rem; margin-bottom: 2rem;">간단하게 주식을 검색하고 AI 분석 결과를 확인하세요</p>', unsafe_allow_html=True)

# 세션 상태 초기화
if 'selected_ticker' not in st.session_state:
    st.session_state.selected_ticker = None

# 검색 섹션
with st.container():
    st.markdown('<div class="search-box">', unsafe_allow_html=True)
    
    col1, col2, col3 = st.columns([3, 1, 1])
    
    # 선택된 티커가 있으면 기본값으로 설정
    default_value = st.session_state.selected_ticker if st.session_state.selected_ticker else ""
    
    with col1:
        search_input = st.text_input(
            "🔍 주식 검색",
            value=default_value,
            placeholder="종목명 또는 티커를 입력하세요 (예: 삼성전자, AAPL, Tesla)",
            key="search_input",
            label_visibility="collapsed"
        )
    
    with col2:
        search_button = st.button("🔎 분석하기", use_container_width=True, type="primary")
    
    with col3:
        if st.button("🔄 초기화", use_container_width=True):
            st.session_state.selected_ticker = None
            st.rerun()
    
    # 인기 종목 바로가기
    st.markdown("**⚡ 인기 종목 바로가기:**")
    quick_cols = st.columns(6)
    popular = [
        ("AAPL", "애플"),
        ("TSLA", "테슬라"),
        ("NVDA", "엔비디아"),
        ("005930.KS", "삼성전자"),
        ("000660.KS", "SK하이닉스"),
        ("035420.KS", "NAVER")
    ]
    
    for idx, (ticker, name) in enumerate(popular):
        with quick_cols[idx]:
            if st.button(f"{name}", key=f"quick_{ticker}", use_container_width=True):
                st.session_state.selected_ticker = ticker
                st.rerun()
    
    st.markdown('</div>', unsafe_allow_html=True)

# 분석 실행
ticker = search_input.strip().upper() if search_input else ""

if search_button and ticker:
    st.session_state.selected_ticker = ticker

if ticker:
if ticker:
    with st.spinner(f"🤖 {ticker} 분석 중... 잠시만 기다려주세요!"):
        try:
                # 데이터 수집
                fetcher = StockDataFetcher()
                start_date = (datetime.now() - timedelta(days=5*365)).strftime("%Y-%m-%d")
                df = fetcher.get_stock_data(ticker, start_date=start_date)
                
                if df is None or len(df) == 0:
                    st.error(f"❌ '{ticker}' 종목을 찾을 수 없습니다. 티커를 확인해주세요.")
                else:
                    info = fetcher.get_stock_info(ticker)
                    
                    # 기술적 분석
                    TechnicalIndicators.add_all_indicators(df)
                    signal = TechnicalIndicators.get_trading_signal(df)
                    
                    # AI 예측
                    predictor = StockPricePredictor()
                    predictions = predictor.predict_future(df, days=30)
                    
                    # 외부 데이터
                    external_collector = ExternalDataCollector()
                    external_data = external_collector.collect_all_data(ticker)
                    
                    # 백테스팅
                    backtest_engine = BacktestingEngine()
                    backtest_results = backtest_engine.run_monthly_backtest(ticker, df)
                    
                    # 패턴 분석
                    pattern_analyzer = AdvancedPatternAnalyzer()
                    patterns = pattern_analyzer.analyze_all_patterns(df)
                    
                    # Enhanced Predictor
                    enhanced_predictor = EnhancedPredictor()
                    enhanced_prediction = enhanced_predictor.predict_with_external_data(
                        df, external_data, days=30
                    )
                    
                    # 최종 투자 결정
                    decision_engine = InvestmentDecisionEngine()
                    final_report = decision_engine.generate_final_report(
                        ticker=ticker,
                        df=df,
                        technical_signal=signal,
                        prediction=enhanced_prediction,
                        external_data=external_data,
                        backtest_results=backtest_results,
                        patterns=patterns
                    )
                    
                    current_price = df['Close'].iloc[-1]
                    predicted_price = predictions[-1] if len(predictions) > 0 else current_price
                    change_pct = ((predicted_price - current_price) / current_price * 100)
                    
                    # === 결과 표시 ===
                    
                    # 1. 최종 투자 결정 배너
                    decision = final_report['decision']
                    decision_emoji = get_decision_emoji(decision)
                    decision_color = get_decision_color(decision)
                    decision_name = get_decision_name_kr(decision)
                    
                    st.markdown(
                        f'<div class="decision-banner" style="background: {decision_color};">'
                        f'{decision_emoji} {decision_name}'
                        f'</div>',
                        unsafe_allow_html=True
                    )
                    
                    # 2. 종목 정보 및 주요 지표
                    st.markdown('<div class="result-card">', unsafe_allow_html=True)
                    st.markdown(f"### 📊 {info.get('longName', ticker)} ({ticker})")
                    
                    col1, col2, col3, col4 = st.columns(4)
                    
                    with col1:
                        st.metric("현재가", f"${current_price:,.2f}")
                    
                    with col2:
                        st.metric("30일 예측가", f"${predicted_price:,.2f}", f"{change_pct:+.2f}%")
                    
                    with col3:
                        rsi = df['RSI'].iloc[-1] if 'RSI' in df.columns else 50
                        st.metric("RSI", f"{rsi:.1f}", 
                                 "과매수" if rsi > 70 else "과매도" if rsi < 30 else "중립")
                    
                    with col4:
                        score = final_report['total_score']
                        st.metric("AI 신뢰도", f"{score:.0f}/100", 
                                 "높음" if score > 70 else "중간" if score > 40 else "낮음")
                    
                    st.markdown('</div>', unsafe_allow_html=True)
                    
                    # 3. 투자 전략 (최종 보고서)
                    st.markdown('<div class="result-card">', unsafe_allow_html=True)
                    st.markdown("### 💡 AI 투자 전략")
                    
                    strategy = final_report['investment_strategy']
                    
                    col1, col2 = st.columns(2)
                    with col1:
                        st.markdown(f"""
                        **📍 진입 전략**
                        - 추천 비중: {strategy['position_size']}
                        - 진입 가격: ${strategy['entry_price']:,.2f}
                        - 손절가: ${strategy['stop_loss']:,.2f}
                        """)
                    
                    with col2:
                        st.markdown(f"""
                        **🎯 목표 수익**
                        - 1개월 목표: ${final_report['price_targets']['target_1m']:,.2f}
                        - 3개월 목표: ${final_report['price_targets']['target_3m']:,.2f}
                        - 손익비: {final_report['price_targets']['risk_reward_ratio']:.2f}
                        """)
                    
                    st.markdown(f"**⏰ 투자 기간:** {strategy['holding_period']}")
                    
                    st.markdown('</div>', unsafe_allow_html=True)
                    
                    # 4. 핵심 근거
                    st.markdown('<div class="result-card">', unsafe_allow_html=True)
                    st.markdown("### 📋 투자 결정 근거")
                    
                    col1, col2 = st.columns(2)
                    
                    with col1:
                        st.markdown("**✅ 긍정적 요인**")
                        for reason in final_report['key_reasons']['positive'][:3]:
                            st.markdown(f"- {reason}")
                    
                    with col2:
                        st.markdown("**⚠️ 위험 요인**")
                        for reason in final_report['key_reasons']['negative'][:3]:
                            st.markdown(f"- {reason}")
                    
                    st.markdown('</div>', unsafe_allow_html=True)
                    
                    # 5. 가격 차트
                    st.markdown('<div class="result-card">', unsafe_allow_html=True)
                    st.markdown("### 📈 가격 추세 & AI 예측")
                    
                    # 최근 6개월 + 30일 예측
                    recent_df = df.tail(180)
                    
                    fig = go.Figure()
                    
                    # 실제 가격
                    fig.add_trace(go.Scatter(
                        x=recent_df.index,
                        y=recent_df['Close'],
                        name='실제 가격',
                        line=dict(color='#667eea', width=2)
                    ))
                    
                    # 예측 가격
                    future_dates = pd.date_range(
                        start=df.index[-1] + timedelta(days=1),
                        periods=len(predictions),
                        freq='D'
                    )
                    
                    fig.add_trace(go.Scatter(
                        x=future_dates,
                        y=predictions,
                        name='AI 예측',
                        line=dict(color='#f093fb', width=2, dash='dash'),
                        mode='lines+markers'
                    ))
                    
                    fig.update_layout(
                        height=400,
                        xaxis_title="날짜",
                        yaxis_title="가격 ($)",
                        hovermode='x unified',
                        showlegend=True,
                        template='plotly_white'
                    )
                    
                    st.plotly_chart(fig, use_container_width=True)
                    st.markdown('</div>', unsafe_allow_html=True)
                    
                    # 6. 세부 분석 점수 (접기 가능)
                    with st.expander("📊 세부 분석 점수 보기"):
                        scores = final_report['scores']
                        
                        col1, col2, col3, col4 = st.columns(4)
                        
                        with col1:
                            st.markdown(f"""
                            <div class="metric-card">
                                <div style="font-size: 2rem;">📈</div>
                                <div style="font-size: 1.5rem; font-weight: bold;">{scores['technical']:.0f}</div>
                                <div>기술적 분석</div>
                            </div>
                            """, unsafe_allow_html=True)
                        
                        with col2:
                            st.markdown(f"""
                            <div class="metric-card">
                                <div style="font-size: 2rem;">🤖</div>
                                <div style="font-size: 1.5rem; font-weight: bold;">{scores['ai_prediction']:.0f}</div>
                                <div>AI 예측</div>
                            </div>
                            """, unsafe_allow_html=True)
                        
                        with col3:
                            st.markdown(f"""
                            <div class="metric-card">
                                <div style="font-size: 2rem;">💼</div>
                                <div style="font-size: 1.5rem; font-weight: bold;">{scores['fundamental']:.0f}</div>
                                <div>펀더멘털</div>
                            </div>
                            """, unsafe_allow_html=True)
                        
                        with col4:
                            st.markdown(f"""
                            <div class="metric-card">
                                <div style="font-size: 2rem;">🌍</div>
                                <div style="font-size: 1.5rem; font-weight: bold;">{scores['market_sentiment']:.0f}</div>
                                <div>시장 심리</div>
                            </div>
                            """, unsafe_allow_html=True)
                    
                    # 7. 백테스트 결과
                    if backtest_results:
                        with st.expander("📉 과거 예측 정확도 (백테스트)"):
                            st.markdown(f"**평균 정확도:** {backtest_results['metrics']['accuracy']:.1f}%")
                            st.markdown(f"**평균 오차:** ${backtest_results['metrics']['avg_error']:.2f}")
                            
                            # 백테스트 차트
                            backtest_df = pd.DataFrame({
                                '날짜': backtest_results['dates'],
                                '실제': backtest_results['actual'],
                                '예측': backtest_results['predicted']
                            })
                            
                            fig_bt = go.Figure()
                            fig_bt.add_trace(go.Scatter(
                                x=backtest_df['날짜'],
                                y=backtest_df['실제'],
                                name='실제',
                                line=dict(color='green', width=2)
                            ))
                            fig_bt.add_trace(go.Scatter(
                                x=backtest_df['날짜'],
                                y=backtest_df['예측'],
                                name='예측',
                                mode='markers',
                                marker=dict(color='purple', size=8)
                            ))
                            fig_bt.update_layout(height=300, template='plotly_white')
                            st.plotly_chart(fig_bt, use_container_width=True)
                    
                    # 8. 리스크 경고
                    risk_level = final_report['risk']['level']
                    risk_factors = final_report['risk']['factors']
                    
                    risk_colors = {
                        'VERY_LOW': '#10b981',
                        'LOW': '#3b82f6',
                        'MEDIUM': '#f59e0b',
                        'HIGH': '#ef4444',
                        'VERY_HIGH': '#dc2626'
                    }
                    
                    risk_names = {
                        'VERY_LOW': '매우 낮음',
                        'LOW': '낮음',
                        'MEDIUM': '보통',
                        'HIGH': '높음',
                        'VERY_HIGH': '매우 높음'
                    }
                    
                    st.markdown('<div class="result-card">', unsafe_allow_html=True)
                    st.markdown(f"### ⚠️ 리스크 평가: **{risk_names.get(risk_level, '보통')}**")
                    
                    for factor in risk_factors[:3]:
                        st.markdown(f"- {factor}")
                    
                    st.markdown('</div>', unsafe_allow_html=True)
                    
                    # 9. 면책 조항
                    st.info("""
                    ⚠️ **투자 유의사항**
                    - 이 분석은 참고용이며, 최종 투자 결정은 본인의 책임입니다.
                    - 과거 데이터 기반 예측은 미래 수익을 보장하지 않습니다.
                    - 분산 투자 및 리스크 관리를 권장합니다.
                    """)
                    
            except Exception as e:
                st.error(f"❌ 분석 중 오류가 발생했습니다: {str(e)}")
                import traceback
                st.code(traceback.format_exc())

# 푸터
st.markdown("---")
st.markdown(
    '<p style="text-align: center; color: white; opacity: 0.8;">Made with ❤️ by AI Stock Predictor Team | v7.2 One-Page UI</p>',
    unsafe_allow_html=True
)
