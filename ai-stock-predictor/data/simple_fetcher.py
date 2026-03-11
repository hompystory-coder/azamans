"""
간단한 실시간 주식 데이터 수집기
FinanceDataReader + 네이버 금융 크롤링 조합

특징:
- API 키 불필요!
- 실시간 현재가 (네이버 크롤링, 지연 0초)
- 안정적인 차트 데이터 (FinanceDataReader)
- 완전 무료
"""

import requests
from bs4 import BeautifulSoup
import pandas as pd
import FinanceDataReader as fdr
from datetime import datetime, timedelta
from typing import Dict, List, Optional
import time
import logging

# 로깅 설정
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class SimpleRealtimeFetcher:
    """
    간단한 실시간 주식 데이터 수집기
    
    조합:
    - 네이버 크롤링: 실시간 현재가 (지연 0초)
    - FinanceDataReader: 차트 데이터 (안정적)
    """
    
    def __init__(self):
        """초기화"""
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        })
        
        # 캐시 (1초 동안 유지)
        self.cache = {}
        self.cache_time = {}
        self.cache_duration = 1  # 초
    
    def get_realtime_price(self, ticker: str) -> Dict:
        """
        네이버 금융에서 실시간 현재가 조회 (지연 0초!)
        
        Args:
            ticker: 종목코드 (예: "005930" - 삼성전자)
        
        Returns:
            {
                'ticker': '005930',
                'name': '삼성전자',
                'price': 119900,
                'change': 400,
                'change_pct': 0.33,
                'volume': 12345678,
                'high': 120500,
                'low': 119000,
                'open': 119500,
                'prev_close': 119500,
                'timestamp': '2026-01-01 15:30:00',
                'market_cap': '715조',
                'per': 15.2,
                'source': 'naver (실시간)'
            }
        """
        # 캐시 확인
        cache_key = f"realtime_{ticker}"
        if cache_key in self.cache:
            elapsed = time.time() - self.cache_time.get(cache_key, 0)
            if elapsed < self.cache_duration:
                return self.cache[cache_key]
        
        try:
            # 네이버 금융 페이지 요청
            url = f"https://finance.naver.com/item/main.nhn?code={ticker}"
            response = self.session.get(url, timeout=5)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # 종목명
            name = soup.select_one('.wrap_company h2 a').text.strip()
            
            # 현재가
            price_elem = soup.select_one('.no_today .blind')
            if not price_elem:
                raise ValueError(f"가격 정보를 찾을 수 없습니다: {ticker}")
            
            price = int(price_elem.text.replace(',', ''))
            
            # 등락
            change_elems = soup.select('.no_exday .blind')
            change = 0
            change_pct = 0.0
            
            if len(change_elems) >= 2:
                change_text = change_elems[0].text.replace(',', '').replace('+', '').replace('-', '')
                change_pct_text = change_elems[1].text.replace('%', '').replace('+', '').replace('-', '')
                
                # 부호 확인
                is_negative = '하락' in soup.select_one('.no_exday em').text
                
                change = int(change_text) * (-1 if is_negative else 1)
                change_pct = float(change_pct_text) * (-1 if is_negative else 1)
            
            # 전일 종가
            prev_close = price - change
            
            # 거래량
            volume_elem = soup.select_one('.first .blind')
            volume = 0
            if volume_elem:
                volume_text = volume_elem.text.replace(',', '')
                try:
                    volume = int(volume_text)
                except:
                    volume = 0
            
            # 고가, 저가, 시가
            table = soup.select('.first table tr')
            high = low = open_price = price
            
            for tr in table:
                th = tr.select_one('th')
                td = tr.select_one('td')
                
                if th and td:
                    label = th.text.strip()
                    value_elem = td.select_one('.blind')
                    
                    if value_elem:
                        value_text = value_elem.text.replace(',', '')
                        try:
                            value = int(value_text)
                            
                            if '고가' in label:
                                high = value
                            elif '저가' in label:
                                low = value
                            elif '시가' in label:
                                open_price = value
                        except:
                            pass
            
            # 시가총액
            market_cap = "N/A"
            market_cap_elem = soup.select_one('.first em')
            if market_cap_elem:
                market_cap = market_cap_elem.text.strip()
            
            # PER
            per = 0.0
            per_elem = soup.select('.per em')
            if per_elem and len(per_elem) > 0:
                per_text = per_elem[0].text.strip()
                try:
                    per = float(per_text)
                except:
                    per = 0.0
            
            result = {
                'ticker': ticker,
                'name': name,
                'price': price,
                'change': change,
                'change_pct': change_pct,
                'volume': volume,
                'high': high,
                'low': low,
                'open': open_price,
                'prev_close': prev_close,
                'timestamp': datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                'market_cap': market_cap,
                'per': per,
                'source': 'naver (실시간)'
            }
            
            # 캐시 저장
            self.cache[cache_key] = result
            self.cache_time[cache_key] = time.time()
            
            return result
            
        except Exception as e:
            logger.error(f"네이버 크롤링 실패 ({ticker}): {e}")
            
            # Fallback: yfinance 사용
            return self._get_price_fallback(ticker)
    
    def _get_price_fallback(self, ticker: str) -> Dict:
        """네이버 크롤링 실패 시 yfinance로 대체"""
        try:
            import yfinance as yf
            
            # 종목코드 변환 (한국 주식)
            ticker_yf = f"{ticker}.KS" if len(ticker) == 6 else ticker
            
            stock = yf.Ticker(ticker_yf)
            info = stock.info
            hist = stock.history(period="1d")
            
            if hist.empty:
                raise ValueError("데이터 없음")
            
            current = hist['Close'].iloc[-1]
            prev = hist['Open'].iloc[0]
            change = current - prev
            change_pct = (change / prev) * 100
            
            return {
                'ticker': ticker,
                'name': info.get('shortName', ticker),
                'price': int(current),
                'change': int(change),
                'change_pct': round(change_pct, 2),
                'volume': int(hist['Volume'].iloc[-1]),
                'high': int(hist['High'].iloc[-1]),
                'low': int(hist['Low'].iloc[-1]),
                'open': int(hist['Open'].iloc[0]),
                'prev_close': int(prev),
                'timestamp': datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                'market_cap': 'N/A',
                'per': 0.0,
                'source': 'yfinance (15분 지연)'
            }
            
        except Exception as e:
            logger.error(f"Fallback 실패 ({ticker}): {e}")
            
            return {
                'ticker': ticker,
                'name': ticker,
                'price': 0,
                'change': 0,
                'change_pct': 0.0,
                'volume': 0,
                'high': 0,
                'low': 0,
                'open': 0,
                'prev_close': 0,
                'timestamp': datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                'market_cap': 'N/A',
                'per': 0.0,
                'source': 'error'
            }
    
    def get_ohlcv_daily(self, ticker: str, days: int = 100) -> pd.DataFrame:
        """
        FinanceDataReader로 일봉 데이터 조회
        
        Args:
            ticker: 종목코드
            days: 조회할 일수
        
        Returns:
            DataFrame with columns: date, open, high, low, close, volume
        """
        try:
            end_date = datetime.now()
            start_date = end_date - timedelta(days=days)
            
            # FinanceDataReader 사용
            df = fdr.DataReader(ticker, start_date, end_date)
            
            if df.empty:
                return pd.DataFrame()
            
            # 컬럼명 정리
            df = df.reset_index()
            df.columns = ['date', 'open', 'high', 'low', 'close', 'volume', 'change']
            
            # 필요한 컬럼만 선택
            df = df[['date', 'open', 'high', 'low', 'close', 'volume']]
            
            return df
            
        except Exception as e:
            logger.error(f"FinanceDataReader 실패 ({ticker}): {e}")
            return pd.DataFrame()
    
    def get_ohlcv_minute(self, ticker: str, interval: str = "1m", hours: int = 1) -> pd.DataFrame:
        """
        분봉 데이터 조회 (yfinance 사용)
        
        Args:
            ticker: 종목코드
            interval: 간격 (1m, 5m, 15m, 30m, 1h)
            hours: 조회할 시간
        
        Returns:
            DataFrame
        """
        try:
            import yfinance as yf
            
            ticker_yf = f"{ticker}.KS" if len(ticker) == 6 else ticker
            stock = yf.Ticker(ticker_yf)
            
            # 기간 계산
            period_map = {1: "1d", 2: "2d", 5: "5d", 7: "7d"}
            period = period_map.get(hours, "1d")
            
            df = stock.history(period=period, interval=interval)
            
            if df.empty:
                return pd.DataFrame()
            
            # 컬럼명 소문자로
            df = df.reset_index()
            df.columns = [col.lower() for col in df.columns]
            
            # datetime 컬럼명 통일
            if 'datetime' in df.columns:
                df = df.rename(columns={'datetime': 'date'})
            
            return df[['date', 'open', 'high', 'low', 'close', 'volume']]
            
        except Exception as e:
            logger.error(f"분봉 조회 실패 ({ticker}): {e}")
            return pd.DataFrame()
    
    def search_ticker(self, query: str) -> List[Dict]:
        """
        종목 검색 (간단한 구현)
        
        Args:
            query: 검색어
        
        Returns:
            [{'ticker': '005930', 'name': '삼성전자'}, ...]
        """
        # 한국 주요 종목
        KOREA_STOCKS = {
            "005930": "삼성전자",
            "000660": "SK하이닉스",
            "035420": "NAVER",
            "005380": "현대차",
            "051910": "LG화학",
            "006400": "삼성SDI",
            "035720": "카카오",
            "207940": "삼성바이오로직스",
            "068270": "셀트리온",
            "028260": "삼성물산",
            "000270": "기아",
            "105560": "KB금융",
            "055550": "신한지주",
            "012330": "현대모비스",
            "096770": "SK이노베이션"
        }
        
        results = []
        query = query.upper()
        
        for ticker, name in KOREA_STOCKS.items():
            if query in ticker or query in name:
                results.append({'ticker': ticker, 'name': name})
        
        return results


# 편의 함수
def create_simple_fetcher() -> SimpleRealtimeFetcher:
    """간단한 실시간 수집기 생성"""
    return SimpleRealtimeFetcher()


# 테스트 코드
if __name__ == "__main__":
    print("\n" + "="*60)
    print("🚀 간단한 실시간 주식 데이터 수집기 테스트")
    print("   FinanceDataReader + 네이버 크롤링 조합")
    print("="*60 + "\n")
    
    # 수집기 생성
    fetcher = create_simple_fetcher()
    
    # 삼성전자 실시간 현재가 (네이버 크롤링)
    print("📊 삼성전자 실시간 현재가 (네이버 크롤링)")
    print("-" * 60)
    price = fetcher.get_realtime_price("005930")
    print(f"종목명: {price['name']}")
    print(f"현재가: {price['price']:,}원")
    print(f"변동: {price['change']:+,}원 ({price['change_pct']:+.2f}%)")
    print(f"거래량: {price['volume']:,}주")
    print(f"고가: {price['high']:,}원 | 저가: {price['low']:,}원")
    print(f"시가총액: {price['market_cap']} | PER: {price['per']}")
    print(f"데이터 출처: {price['source']}")
    print(f"조회 시각: {price['timestamp']}")
    
    # 일봉 데이터 (FinanceDataReader)
    print("\n📈 일봉 데이터 (최근 5일, FinanceDataReader)")
    print("-" * 60)
    df = fetcher.get_ohlcv_daily("005930", days=5)
    if not df.empty:
        print(df.tail())
    else:
        print("데이터 없음")
    
    # 여러 종목 조회
    print("\n🔍 여러 종목 실시간 조회")
    print("-" * 60)
    tickers = [
        ("005930", "삼성전자"),
        ("000660", "SK하이닉스"),
        ("035420", "NAVER")
    ]
    
    for ticker, name in tickers:
        price = fetcher.get_realtime_price(ticker)
        print(f"{name:10s} {price['price']:>8,}원 {price['change']:>+7,}원 ({price['change_pct']:>+6.2f}%) [{price['source']}]")
    
    print("\n" + "="*60)
    print("✅ 테스트 완료!")
    print("   - 네이버 크롤링: 실시간 현재가 (지연 0초)")
    print("   - FinanceDataReader: 차트 데이터 (안정적)")
    print("   - API 키 불필요, 완전 무료!")
    print("="*60 + "\n")
