"""
한국투자증권 Open API 데이터 수집기
- 실시간 주가 조회 (지연 없음)
- 실시간 호가 조회
- 일봉/분봉 데이터
- Websocket 실시간 스트리밍
"""

import os
import json
import time
import requests
import pandas as pd
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Callable
from functools import wraps
import logging

# 로깅 설정
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class RateLimiter:
    """API 호출 제한 (초당 20회)"""
    
    def __init__(self, min_interval=0.05):
        self.min_interval = min_interval
        self.last_called = 0.0
    
    def __call__(self, func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            elapsed = time.time() - self.last_called
            if elapsed < self.min_interval:
                time.sleep(self.min_interval - elapsed)
            result = func(*args, **kwargs)
            self.last_called = time.time()
            return result
        return wrapper


class KISStockDataFetcher:
    """한국투자증권 API 데이터 수집기"""
    
    # API URLs
    BASE_URL = "https://openapi.koreainvestment.com:9443"
    MOCK_URL = "https://openapimock.koreainvestment.com:9443"  # 모의투자
    
    # 거래 ID
    TR_ID_PRICE = "FHKST01010100"  # 현재가 조회
    TR_ID_ORDERBOOK = "FHKST01010200"  # 호가 조회
    TR_ID_DAILY = "FHKST01010400"  # 일봉 조회
    TR_ID_MINUTE = "FHKST01010600"  # 분봉 조회
    
    def __init__(self, app_key: str = None, app_secret: str = None, 
                 account_no: str = None, mock: bool = True):
        """
        초기화
        
        Args:
            app_key: 앱 키
            app_secret: 앱 시크릿
            account_no: 계좌번호
            mock: 모의투자 여부 (True=모의투자, False=실전투자)
        """
        self.app_key = app_key or os.getenv("KIS_APP_KEY")
        self.app_secret = app_secret or os.getenv("KIS_APP_SECRET")
        self.account_no = account_no or os.getenv("KIS_ACCOUNT_NO")
        self.mock = mock
        
        self.base_url = self.MOCK_URL if mock else self.BASE_URL
        self.access_token = None
        self.token_expire_time = None
        
        # Rate limiter
        self.rate_limiter = RateLimiter(min_interval=0.05)
        
        # 토큰 발급
        if self.app_key and self.app_secret:
            self._get_access_token()
    
    def _get_access_token(self) -> str:
        """접근 토큰 발급"""
        if self.access_token and self.token_expire_time:
            if datetime.now() < self.token_expire_time:
                return self.access_token
        
        url = f"{self.base_url}/oauth2/tokenP"
        headers = {"content-type": "application/json"}
        data = {
            "grant_type": "client_credentials",
            "appkey": self.app_key,
            "appsecret": self.app_secret
        }
        
        try:
            resp = requests.post(url, headers=headers, json=data)
            resp.raise_for_status()
            
            result = resp.json()
            self.access_token = result["access_token"]
            
            # 토큰 만료 시간 (24시간 - 10분 여유)
            self.token_expire_time = datetime.now() + timedelta(hours=23, minutes=50)
            
            logger.info("한국투자증권 API 토큰 발급 완료")
            return self.access_token
            
        except Exception as e:
            logger.error(f"토큰 발급 실패: {e}")
            raise
    
    def _get_headers(self, tr_id: str, tr_type: str = "M") -> Dict:
        """API 요청 헤더 생성"""
        return {
            "content-type": "application/json; charset=utf-8",
            "authorization": f"Bearer {self.access_token}",
            "appkey": self.app_key,
            "appsecret": self.app_secret,
            "tr_id": tr_id,
            "tr_cont": "",
            "custtype": "P",  # 개인
            "mac_address": ""
        }
    
    @RateLimiter(min_interval=0.05)
    def _request(self, endpoint: str, tr_id: str, params: Dict) -> Dict:
        """API 요청"""
        url = f"{self.base_url}{endpoint}"
        headers = self._get_headers(tr_id)
        
        try:
            resp = requests.get(url, headers=headers, params=params)
            resp.raise_for_status()
            return resp.json()
        except Exception as e:
            logger.error(f"API 요청 실패: {e}")
            raise
    
    def get_current_price(self, ticker: str) -> Dict:
        """
        실시간 현재가 조회 (지연 없음!)
        
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
                'timestamp': '2026-01-01 15:30:00'
            }
        """
        params = {
            "fid_cond_mrkt_div_code": "J",  # 주식
            "fid_input_iscd": ticker
        }
        
        data = self._request("/uapi/domestic-stock/v1/quotations/inquire-price", 
                            self.TR_ID_PRICE, params)
        
        output = data.get("output", {})
        
        return {
            'ticker': ticker,
            'name': output.get("hts_kor_isnm", ""),
            'price': int(output.get("stck_prpr", 0)),
            'change': int(output.get("prdy_vrss", 0)),
            'change_pct': float(output.get("prdy_ctrt", 0)),
            'volume': int(output.get("acml_vol", 0)),
            'high': int(output.get("stck_hgpr", 0)),
            'low': int(output.get("stck_lwpr", 0)),
            'open': int(output.get("stck_oprc", 0)),
            'prev_close': int(output.get("stck_prpr", 0)) - int(output.get("prdy_vrss", 0)),
            'timestamp': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }
    
    def get_orderbook(self, ticker: str) -> Dict:
        """
        실시간 호가 조회
        
        Args:
            ticker: 종목코드
        
        Returns:
            {
                'ticker': '005930',
                'asks': [  # 매도 호가 (낮은 가격순)
                    {'price': 120000, 'quantity': 100, 'level': 1},
                    {'price': 120100, 'quantity': 200, 'level': 2},
                    ...
                ],
                'bids': [  # 매수 호가 (높은 가격순)
                    {'price': 119900, 'quantity': 150, 'level': 1},
                    {'price': 119800, 'quantity': 250, 'level': 2},
                    ...
                ],
                'total_ask_qty': 5000,
                'total_bid_qty': 6000,
                'timestamp': '2026-01-01 15:30:00'
            }
        """
        params = {
            "fid_cond_mrkt_div_code": "J",
            "fid_input_iscd": ticker
        }
        
        data = self._request("/uapi/domestic-stock/v1/quotations/inquire-asking-price-exp-ccn",
                            self.TR_ID_ORDERBOOK, params)
        
        output = data.get("output", {})
        
        # 매도 호가 (10호가)
        asks = []
        for i in range(1, 11):
            price = int(output.get(f"askp{i}", 0))
            qty = int(output.get(f"askp_rsqn{i}", 0))
            if price > 0:
                asks.append({'price': price, 'quantity': qty, 'level': i})
        
        # 매수 호가 (10호가)
        bids = []
        for i in range(1, 11):
            price = int(output.get(f"bidp{i}", 0))
            qty = int(output.get(f"bidp_rsqn{i}", 0))
            if price > 0:
                bids.append({'price': price, 'quantity': qty, 'level': i})
        
        return {
            'ticker': ticker,
            'asks': asks,
            'bids': bids,
            'total_ask_qty': int(output.get("total_askp_rsqn", 0)),
            'total_bid_qty': int(output.get("total_bidp_rsqn", 0)),
            'timestamp': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }
    
    def get_ohlcv_daily(self, ticker: str, days: int = 100) -> pd.DataFrame:
        """
        일봉 데이터 조회
        
        Args:
            ticker: 종목코드
            days: 조회할 일수 (최대 100일)
        
        Returns:
            DataFrame with columns: date, open, high, low, close, volume
        """
        end_date = datetime.now().strftime("%Y%m%d")
        start_date = (datetime.now() - timedelta(days=days)).strftime("%Y%m%d")
        
        params = {
            "fid_cond_mrkt_div_code": "J",
            "fid_input_iscd": ticker,
            "fid_input_date_1": start_date,
            "fid_input_date_2": end_date,
            "fid_period_div_code": "D",  # 일봉
            "fid_org_adj_prc": "0"  # 수정주가 적용
        }
        
        data = self._request("/uapi/domestic-stock/v1/quotations/inquire-daily-price",
                            self.TR_ID_DAILY, params)
        
        output = data.get("output", [])
        
        df_data = []
        for item in output:
            df_data.append({
                'date': pd.to_datetime(item["stck_bsop_date"]),
                'open': int(item["stck_oprc"]),
                'high': int(item["stck_hgpr"]),
                'low': int(item["stck_lwpr"]),
                'close': int(item["stck_clpr"]),
                'volume': int(item["acml_vol"])
            })
        
        df = pd.DataFrame(df_data)
        df = df.sort_values('date').reset_index(drop=True)
        return df
    
    def get_ohlcv_minute(self, ticker: str, interval: int = 1, hours: int = 1) -> pd.DataFrame:
        """
        분봉 데이터 조회
        
        Args:
            ticker: 종목코드
            interval: 분봉 간격 (1, 3, 5, 10, 15, 30, 60)
            hours: 조회할 시간 (최대 24시간)
        
        Returns:
            DataFrame with columns: datetime, open, high, low, close, volume
        """
        end_time = datetime.now().strftime("%H%M%S")
        
        params = {
            "fid_cond_mrkt_div_code": "J",
            "fid_input_iscd": ticker,
            "fid_input_hour_1": end_time,
            "fid_pw_data_incu_yn": "Y"
        }
        
        data = self._request("/uapi/domestic-stock/v1/quotations/inquire-time-itemchartprice",
                            self.TR_ID_MINUTE, params)
        
        output = data.get("output", [])
        
        df_data = []
        for item in output:
            df_data.append({
                'datetime': pd.to_datetime(f"{item['stck_bsop_date']} {item['stck_cntg_hour']}"),
                'open': int(item["stck_oprc"]),
                'high': int(item["stck_hgpr"]),
                'low': int(item["stck_lwpr"]),
                'close': int(item["stck_prpr"]),
                'volume': int(item["cntg_vol"])
            })
        
        df = pd.DataFrame(df_data)
        df = df.sort_values('datetime').reset_index(drop=True)
        
        # 시간 필터링
        cutoff_time = datetime.now() - timedelta(hours=hours)
        df = df[df['datetime'] >= cutoff_time]
        
        return df
    
    def search_ticker(self, query: str) -> List[Dict]:
        """
        종목 검색
        
        Args:
            query: 검색어 (종목명 또는 종목코드)
        
        Returns:
            [{'ticker': '005930', 'name': '삼성전자', ...}, ...]
        """
        # 간단한 구현: 한국 주요 종목 데이터베이스에서 검색
        # 실제로는 한국투자증권 API의 종목검색 API를 사용해야 함
        
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
            "028260": "삼성물산"
        }
        
        results = []
        query = query.upper()
        
        for ticker, name in KOREA_STOCKS.items():
            if query in ticker or query in name:
                results.append({'ticker': ticker, 'name': name})
        
        return results


class RealtimeWebsocket:
    """Websocket 실시간 스트리밍"""
    
    WS_URL = "ws://ops.koreainvestment.com:21000"
    
    def __init__(self, fetcher: KISStockDataFetcher):
        """
        초기화
        
        Args:
            fetcher: KISStockDataFetcher 인스턴스
        """
        self.fetcher = fetcher
        self.ws = None
        self.callbacks = {}
        self.running = False
    
    def subscribe_price(self, ticker: str, callback: Callable):
        """
        실시간 체결가 구독
        
        Args:
            ticker: 종목코드
            callback: 콜백 함수 (data: Dict)
        
        Example:
            def on_price(data):
                print(f"체결가: {data['price']}, 체결량: {data['volume']}")
            
            ws.subscribe_price("005930", on_price)
        """
        self.callbacks[ticker] = callback
        
        if not self.running:
            self.start()
        
        # 구독 메시지 전송
        subscribe_msg = {
            "header": {
                "approval_key": self._get_approval_key(),
                "custtype": "P",
                "tr_type": "1",
                "content-type": "utf-8"
            },
            "body": {
                "input": {
                    "tr_id": "H0STCNT0",  # 실시간 체결
                    "tr_key": ticker
                }
            }
        }
        
        if self.ws:
            self.ws.send(json.dumps(subscribe_msg))
    
    def _get_approval_key(self) -> str:
        """Websocket 접속키 발급"""
        url = f"{self.fetcher.base_url}/oauth2/Approval"
        headers = {
            "content-type": "application/json",
            "authorization": f"Bearer {self.fetcher.access_token}",
            "appkey": self.fetcher.app_key,
            "appsecret": self.fetcher.app_secret
        }
        data = {"grant_type": "client_credentials"}
        
        resp = requests.post(url, headers=headers, json=data)
        return resp.json()["approval_key"]
    
    def start(self):
        """Websocket 연결 시작"""
        import websocket
        import threading
        
        def on_message(ws, message):
            try:
                data = json.loads(message)
                ticker = data.get("ticker")
                
                if ticker in self.callbacks:
                    # 데이터 파싱
                    parsed = {
                        'ticker': ticker,
                        'price': int(data.get("price", 0)),
                        'volume': int(data.get("volume", 0)),
                        'timestamp': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    }
                    self.callbacks[ticker](parsed)
            except Exception as e:
                logger.error(f"Websocket 메시지 처리 실패: {e}")
        
        def on_error(ws, error):
            logger.error(f"Websocket 에러: {error}")
        
        def on_close(ws, close_status_code, close_msg):
            logger.info("Websocket 연결 종료")
            self.running = False
        
        def on_open(ws):
            logger.info("Websocket 연결 성공")
            self.running = True
        
        def run():
            self.ws = websocket.WebSocketApp(
                self.WS_URL,
                on_message=on_message,
                on_error=on_error,
                on_close=on_close,
                on_open=on_open
            )
            self.ws.run_forever()
        
        thread = threading.Thread(target=run)
        thread.daemon = True
        thread.start()
    
    def stop(self):
        """Websocket 연결 종료"""
        if self.ws:
            self.ws.close()
        self.running = False


# 편의 함수
def create_fetcher(mock=True) -> KISStockDataFetcher:
    """
    한국투자증권 API 수집기 생성
    
    Args:
        mock: 모의투자 여부
    
    Returns:
        KISStockDataFetcher 인스턴스
    """
    return KISStockDataFetcher(mock=mock)


# 테스트 코드
if __name__ == "__main__":
    # 환경 변수에서 API 키 로드
    from dotenv import load_dotenv
    load_dotenv()
    
    # 수집기 생성 (모의투자)
    fetcher = create_fetcher(mock=True)
    
    # 삼성전자 현재가 조회
    print("\n=== 삼성전자 현재가 ===")
    price = fetcher.get_current_price("005930")
    print(f"현재가: {price['price']:,}원")
    print(f"변동: {price['change']:+,}원 ({price['change_pct']:+.2f}%)")
    print(f"거래량: {price['volume']:,}주")
    
    # 호가 조회
    print("\n=== 호가창 ===")
    orderbook = fetcher.get_orderbook("005930")
    print("매도 호가:")
    for ask in orderbook['asks'][:5]:
        print(f"  {ask['price']:,}원 | {ask['quantity']:,}주")
    print("매수 호가:")
    for bid in orderbook['bids'][:5]:
        print(f"  {bid['price']:,}원 | {bid['quantity']:,}주")
    
    # 일봉 데이터
    print("\n=== 일봉 데이터 (최근 5일) ===")
    df = fetcher.get_ohlcv_daily("005930", days=5)
    print(df.tail())
    
    print("\n✅ 한국투자증권 API 테스트 완료!")
