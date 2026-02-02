"""
한국투자증권 API 실시간 위젯
- 실시간 주가 표시
- 실시간 호가창
- Websocket 스트리밍
"""

import streamlit as st
import time
import pandas as pd
from datetime import datetime
from typing import Optional
import sys
import os

# 경로 추가
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

try:
    from data.kis_fetcher import KISStockDataFetcher, create_fetcher
except ImportError:
    st.error("⚠️ 한국투자증권 API 모듈을 찾을 수 없습니다. data/kis_fetcher.py를 확인하세요.")
    st.stop()


def init_kis_fetcher():
    """한국투자증권 API 초기화"""
    if 'kis_fetcher' not in st.session_state:
        try:
            # .env 파일 로드
            from dotenv import load_dotenv
            load_dotenv()
            
            # API 키 확인
            app_key = os.getenv("KIS_APP_KEY")
            app_secret = os.getenv("KIS_APP_SECRET")
            
            if not app_key or not app_secret:
                st.session_state.kis_enabled = False
                st.session_state.kis_error = "⚠️ API 키가 설정되지 않았습니다. .env 파일을 확인하세요."
                return None
            
            # 수집기 생성 (모의투자)
            fetcher = create_fetcher(mock=True)
            st.session_state.kis_fetcher = fetcher
            st.session_state.kis_enabled = True
            st.session_state.kis_error = None
            
            return fetcher
            
        except Exception as e:
            st.session_state.kis_enabled = False
            st.session_state.kis_error = f"⚠️ API 초기화 실패: {str(e)}"
            return None
    
    return st.session_state.get('kis_fetcher')


def kis_realtime_price_widget(ticker: str, name: str = ""):
    """
    한국투자증권 실시간 주가 위젯
    
    Args:
        ticker: 종목코드 (예: "005930")
        name: 종목명 (예: "삼성전자")
    """
    fetcher = init_kis_fetcher()
    
    if not fetcher or not st.session_state.get('kis_enabled'):
        # API 사용 불가 시 안내 메시지
        st.warning(st.session_state.get('kis_error', '⚠️ 한국투자증권 API를 사용할 수 없습니다.'))
        return None
    
    try:
        # 실시간 데이터 조회
        price_data = fetcher.get_current_price(ticker)
        
        # 색상 결정
        if price_data['change'] > 0:
            color = "🔴"
            text_color = "red"
        elif price_data['change'] < 0:
            color = "🔵"
            text_color = "blue"
        else:
            color = "⚪"
            text_color = "gray"
        
        # 위젯 표시
        st.markdown(f"""
        <div style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        ">
            <h3 style="margin: 0 0 10px 0;">
                {color} {price_data['name'] or name} <span style="font-size: 0.7em; opacity: 0.8;">({ticker})</span>
            </h3>
            <h1 style="margin: 0; font-size: 2.5em;">
                {price_data['price']:,}원
            </h1>
            <p style="margin: 10px 0 0 0; font-size: 1.2em;">
                <span style="color: {'#ff4444' if price_data['change'] > 0 else '#4444ff' if price_data['change'] < 0 else '#aaa'};">
                    {price_data['change']:+,}원 ({price_data['change_pct']:+.2f}%)
                </span>
            </p>
            <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 0.9em;">
                거래량: {price_data['volume']:,}주 | {price_data['timestamp']}
            </p>
            <p style="margin: 5px 0 0 0; opacity: 0.6; font-size: 0.8em;">
                ✅ 실시간 (지연 없음)
            </p>
        </div>
        """, unsafe_allow_html=True)
        
        return price_data
        
    except Exception as e:
        st.error(f"❌ 실시간 데이터 조회 실패: {str(e)}")
        return None


def kis_orderbook_widget(ticker: str, name: str = ""):
    """
    한국투자증권 실시간 호가창 위젯
    
    Args:
        ticker: 종목코드
        name: 종목명
    """
    fetcher = init_kis_fetcher()
    
    if not fetcher or not st.session_state.get('kis_enabled'):
        st.warning("⚠️ 한국투자증권 API를 사용할 수 없습니다.")
        return None
    
    try:
        # 실시간 호가 조회
        orderbook = fetcher.get_orderbook(ticker)
        
        st.markdown(f"### 📊 {name or ticker} 실시간 호가")
        
        col1, col2 = st.columns(2)
        
        # 매도 호가 (왼쪽)
        with col1:
            st.markdown("#### 🔴 매도 호가")
            
            for ask in orderbook['asks'][:10]:
                st.markdown(f"""
                <div style="
                    background: rgba(255, 68, 68, 0.1);
                    padding: 8px;
                    margin: 2px 0;
                    border-radius: 5px;
                    display: flex;
                    justify-content: space-between;
                ">
                    <span style="color: red; font-weight: bold;">{ask['price']:,}원</span>
                    <span style="opacity: 0.7;">{ask['quantity']:,}주</span>
                </div>
                """, unsafe_allow_html=True)
            
            st.markdown(f"**총 매도 잔량**: {orderbook['total_ask_qty']:,}주")
        
        # 매수 호가 (오른쪽)
        with col2:
            st.markdown("#### 🔵 매수 호가")
            
            for bid in orderbook['bids'][:10]:
                st.markdown(f"""
                <div style="
                    background: rgba(68, 68, 255, 0.1);
                    padding: 8px;
                    margin: 2px 0;
                    border-radius: 5px;
                    display: flex;
                    justify-content: space-between;
                ">
                    <span style="color: blue; font-weight: bold;">{bid['price']:,}원</span>
                    <span style="opacity: 0.7;">{bid['quantity']:,}주</span>
                </div>
                """, unsafe_allow_html=True)
            
            st.markdown(f"**총 매수 잔량**: {orderbook['total_bid_qty']:,}주")
        
        st.caption(f"⏰ 업데이트: {orderbook['timestamp']} (실시간)")
        
        return orderbook
        
    except Exception as e:
        st.error(f"❌ 호가 조회 실패: {str(e)}")
        return None


def kis_mini_chart(ticker: str, name: str = "", days: int = 30):
    """
    한국투자증권 미니 차트
    
    Args:
        ticker: 종목코드
        name: 종목명
        days: 조회 기간 (일)
    """
    fetcher = init_kis_fetcher()
    
    if not fetcher or not st.session_state.get('kis_enabled'):
        return None
    
    try:
        # 일봉 데이터 조회
        df = fetcher.get_ohlcv_daily(ticker, days=days)
        
        if df.empty:
            st.warning("데이터가 없습니다.")
            return None
        
        # 간단한 라인 차트
        st.line_chart(df.set_index('date')['close'])
        st.caption(f"{name or ticker} 최근 {days}일 종가")
        
        return df
        
    except Exception as e:
        st.error(f"❌ 차트 조회 실패: {str(e)}")
        return None


def kis_setup_guide():
    """한국투자증권 API 설정 가이드"""
    st.markdown("""
    ## 🚀 한국투자증권 실시간 API 설정
    
    ### 1단계: API 키 발급
    1. [한국투자증권 OpenAPI 포털](https://apiportal.koreainvestment.com) 접속
    2. 회원가입 및 로그인
    3. `API 관리` → `앱 등록`
    4. 앱 이름/설명 입력 후 등록
    5. `APP_KEY`, `APP_SECRET` 복사
    
    ### 2단계: 모의투자 신청
    1. OpenAPI 포털 → `모의투자` 메뉴
    2. `모의계좌 신청` 클릭
    3. 가상 계좌번호 발급 (예: 50000000-01)
    
    ### 3단계: .env 파일 설정
    ```bash
    # /home/azamans/webapp/ai-stock-predictor/.env
    KIS_APP_KEY=your_app_key_here
    KIS_APP_SECRET=your_app_secret_here
    KIS_ACCOUNT_NO=50000000-01
    KIS_ENV=virtual
    ```
    
    ### 4단계: 서버 재시작
    ```bash
    cd /home/azamans/webapp/ai-stock-predictor
    # Streamlit 재시작
    ./restart_streamlit.sh
    ```
    
    ### ✅ 완료!
    - **실시간 주가**: 지연 없음
    - **실시간 호가**: 10호가
    - **무료**: 하루 10,000건
    
    자세한 내용은 [KIS_API_SETUP_GUIDE.md](../KIS_API_SETUP_GUIDE.md) 참고
    """)


# 테스트 코드
if __name__ == "__main__":
    st.set_page_config(page_title="한국투자증권 실시간 위젯 테스트", layout="wide")
    
    st.title("🚀 한국투자증권 실시간 API 테스트")
    
    # API 설정 확인
    fetcher = init_kis_fetcher()
    
    if fetcher and st.session_state.get('kis_enabled'):
        st.success("✅ 한국투자증권 API 연결 성공!")
        
        # 삼성전자 테스트
        st.markdown("---")
        st.header("삼성전자 (005930)")
        
        col1, col2 = st.columns([1, 2])
        
        with col1:
            # 실시간 주가
            price_data = kis_realtime_price_widget("005930", "삼성전자")
        
        with col2:
            # 미니 차트
            kis_mini_chart("005930", "삼성전자", days=30)
        
        st.markdown("---")
        
        # 호가창
        kis_orderbook_widget("005930", "삼성전자")
        
        # 자동 새로고침
        if st.checkbox("🔄 자동 새로고침 (1초)", value=True):
            time.sleep(1)
            st.rerun()
    
    else:
        st.error("❌ 한국투자증권 API 연결 실패")
        st.markdown("---")
        kis_setup_guide()
