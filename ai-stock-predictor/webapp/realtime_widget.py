"""
실시간 가격 위젯 - 네이버 금융 사용 (진짜 실시간!)
"""

import streamlit as st
import requests
from bs4 import BeautifulSoup
from datetime import datetime
import time

def get_naver_realtime_price(code):
    """네이버 금융에서 실시간 주가 가져오기"""
    
    # 종목 코드 정리
    if '.' in code:
        code = code.split('.')[0]
    
    url = f"https://finance.naver.com/item/main.naver?code={code}"
    
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        response = requests.get(url, headers=headers, timeout=10)
        
        if response.status_code != 200:
            return None
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # 현재가
        price_elem = soup.select_one('.rate_info .no_today .blind')
        if not price_elem:
            return None
        
        current_price = int(price_elem.text.replace(',', ''))
        
        # 전일 종가
        prev_elem = soup.select_one('.rate_info .no_exday em:nth-child(2) .blind')
        prev_close = int(prev_elem.text.replace(',', '')) if prev_elem else current_price
        
        # 등락
        change_elem = soup.select_one('.rate_info .no_exday em:nth-child(1) .blind')
        change_price = 0
        if change_elem:
            change_text = change_elem.text.replace(',', '').replace('상승', '').replace('하락', '').replace('보합', '').strip()
            try:
                change_price = int(change_text)
            except:
                change_price = current_price - prev_close
        
        # 등락 방향
        if soup.select_one('.rate_info .no_exday.up'):
            change_direction = "상승"
        elif soup.select_one('.rate_info .no_exday.down'):
            change_direction = "하락"
        else:
            change_direction = "보합"
        
        change_percent = (change_price / prev_close * 100) if prev_close > 0 else 0
        
        # 시가, 고가, 저가
        today_list = soup.select('.new_totalinfo dl dd')
        
        open_price = current_price
        high_price = current_price
        low_price = current_price
        
        for dd in today_list:
            text = dd.text.strip()
            if '시가' in text:
                try:
                    open_price = int(dd.select_one('.blind').text.replace(',', ''))
                except:
                    pass
            elif '고가' in text:
                try:
                    high_price = int(dd.select_one('.blind').text.replace(',', ''))
                except:
                    pass
            elif '저가' in text:
                try:
                    low_price = int(dd.select_one('.blind').text.replace(',', ''))
                except:
                    pass
        
        return {
            'current_price': current_price,
            'prev_close': prev_close,
            'change_price': change_price,
            'change_percent': change_percent,
            'change_direction': change_direction,
            'open': open_price,
            'high': high_price,
            'low': low_price,
            'timestamp': datetime.now(),
            'source': 'NAVER 금융 (KRX 실시간)'
        }
        
    except Exception as e:
        print(f"네이버 크롤링 실패: {e}")
        return None


def realtime_price_widget(ticker, auto_refresh=False, refresh_interval=5):
    """
    실시간 가격 위젯
    """
    
    # 플레이스홀더
    price_placeholder = st.empty()
    
    # 세션 상태
    if 'price_update_count' not in st.session_state:
        st.session_state.price_update_count = 0
    
    try:
        # 한국 주식 여부 확인
        is_korean = ticker.endswith('.KS') or ticker.endswith('.KQ') or (len(ticker) == 6 and ticker.isdigit())
        
        if is_korean:
            # 네이버 실시간 사용
            data = get_naver_realtime_price(ticker)
            
            if not data:
                st.error("실시간 데이터를 가져올 수 없습니다.")
                return
            
            current_price = data['current_price']
            change_price = data['change_price']
            change_percent = data['change_percent']
            source = data['source']
            
        else:
            # 미국 주식 - yfinance 사용 (15-20분 지연)
            import yfinance as yf
            stock = yf.Ticker(ticker)
            hist = stock.history(period="2d")
            
            if hist.empty:
                st.error("데이터를 가져올 수 없습니다.")
                return
            
            current_price = hist['Close'].iloc[-1]
            prev_close = hist['Close'].iloc[-2] if len(hist) > 1 else current_price
            change_price = current_price - prev_close
            change_percent = (change_price / prev_close) * 100
            source = "Yahoo Finance (15-20분 지연)"
        
        # 색상 결정
        if change_price >= 0:
            color = "#10b981"
            emoji = "📈"
            arrow = "▲"
        else:
            color = "#ef4444"
            emoji = "📉"
            arrow = "▼"
        
        # 실시간 가격 표시
        with price_placeholder.container():
            st.markdown(f"""
            <div style='
                background: linear-gradient(135deg, {color}22 0%, {color}11 100%);
                border-left: 5px solid {color};
                border-radius: 15px;
                padding: 25px;
                margin: 15px 0;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            '>
                <div style='display: flex; justify-content: space-between; align-items: center;'>
                    <div style='flex: 1;'>
                        <div style='font-size: 1rem; color: #666; margin-bottom: 8px;'>
                            {emoji} 실시간 현재가
                            <span style='
                                background: #ff3b3b;
                                color: white;
                                padding: 3px 10px;
                                border-radius: 12px;
                                font-size: 0.75rem;
                                margin-left: 10px;
                                font-weight: 700;
                                animation: blink 1.5s ease-in-out infinite;
                            '>LIVE</span>
                        </div>
                        <div style='font-size: 3rem; font-weight: 900; color: {color}; line-height: 1.2;'>
                            {current_price:,.0f}{"원" if is_korean else ""}
                        </div>
                        <div style='font-size: 1.4rem; color: {color}; font-weight: 700; margin-top: 8px;'>
                            {arrow} {change_price:+,.0f}{"원" if is_korean else ""} ({change_percent:+.2f}%)
                        </div>
                    </div>
                    <div style='text-align: right; font-size: 0.9rem; color: #888; padding-left: 20px;'>
                        <div style='margin-bottom: 5px;'>🕐 {datetime.now().strftime('%H:%M:%S')}</div>
                        <div style='margin-bottom: 5px;'>갱신 {st.session_state.price_update_count}회</div>
                        <div style='font-size: 0.8rem; color: #999;'>📡 {source}</div>
                    </div>
                </div>
            </div>
            
            <style>
                @keyframes blink {{
                    0%, 100% {{ opacity: 1; }}
                    50% {{ opacity: 0.5; }}
                }}
            </style>
            """, unsafe_allow_html=True)
        
        # 세션 상태 업데이트
        st.session_state.price_update_count += 1
        
        # 자동 갱신
        if auto_refresh:
            time.sleep(refresh_interval)
            st.rerun()
            
    except Exception as e:
        st.error(f"오류 발생: {str(e)}")


def mini_realtime_chart(ticker, time_range="1h"):
    """미니 실시간 차트 (네이버 금융 기반)"""
    import plotly.graph_objects as go
    import yfinance as yf
    from datetime import datetime, timedelta
    
    try:
        # 한국 주식 여부 확인
        is_korean = ticker.endswith('.KS') or ticker.endswith('.KQ') or (ticker.isdigit() and len(ticker) == 6)
        
        if is_korean:
            # 한국 주식: yfinance로 일봉 데이터 가져오기
            stock = yf.Ticker(ticker)
            hist = stock.history(period="5d")  # 최근 5일 데이터
            
            if hist.empty:
                st.warning("📊 차트 데이터를 가져올 수 없습니다.")
                st.info("💡 한국 주식은 일봉 데이터만 제공됩니다. 실시간 가격은 상단의 LIVE 위젯을 참고하세요.")
                return
            
            # 최근 5일 데이터 사용
            fig = go.Figure()
            
            fig.add_trace(go.Scatter(
                x=hist.index,
                y=hist['Close'],
                mode='lines+markers',
                name='종가',
                line=dict(color='#667eea', width=3),
                marker=dict(size=6, color='#667eea'),
                fill='tozeroy',
                fillcolor='rgba(102, 126, 234, 0.1)'
            ))
            
            fig.update_layout(
                title=f"📊 최근 5일 차트 (일봉)",
                xaxis_title='날짜',
                yaxis_title='가격 (원)',
                height=300,
                hovermode='x unified',
                template='plotly_white',
                xaxis_rangeslider_visible=False,
                margin=dict(l=20, r=20, t=40, b=20)
            )
            
            st.plotly_chart(fig, use_container_width=True)
            st.caption("📌 한국 주식은 일봉 데이터로 표시됩니다. 실시간 가격은 상단 LIVE 위젯을 참고하세요.")
            
        else:
            # 미국 주식: 1분 단위 데이터
            stock = yf.Ticker(ticker)
            hist = stock.history(period="1d", interval="1m")
            
            if hist.empty:
                st.warning("📊 차트 데이터를 가져올 수 없습니다.")
                return
            
            # 최근 1시간 데이터만 사용
            one_hour_ago = datetime.now() - timedelta(hours=1)
            hist_1h = hist[hist.index >= one_hour_ago]
            
            if hist_1h.empty:
                hist_1h = hist.tail(60)  # 최근 60분
            
            fig = go.Figure()
            
            fig.add_trace(go.Scatter(
                x=hist_1h.index,
                y=hist_1h['Close'],
                mode='lines',
                name='가격',
                line=dict(color='#667eea', width=2),
                fill='tozeroy',
                fillcolor='rgba(102, 126, 234, 0.1)'
            ))
            
            fig.update_layout(
                title=f"📊 최근 1시간 차트",
                xaxis_title='시간',
                yaxis_title='가격 ($)',
                height=300,
                hovermode='x unified',
                template='plotly_white',
                xaxis_rangeslider_visible=False,
                margin=dict(l=20, r=20, t=40, b=20)
            )
            
            st.plotly_chart(fig, use_container_width=True)
            st.caption("⚠️ 차트는 15-20분 지연될 수 있습니다.")
        
    except Exception as e:
        st.error(f"❌ 차트 오류: {str(e)}")
        st.info("💡 차트를 표시할 수 없습니다. 실시간 가격은 상단 LIVE 위젯을 참고하세요.")
