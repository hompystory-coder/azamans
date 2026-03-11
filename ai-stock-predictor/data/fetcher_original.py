"""
주가 데이터 수집 모듈
- yfinance를 통한 주가 데이터
- 뉴스 API를 통한 기사 수집
- 500+ 한국/미국 기업 검색 지원
"""

import yfinance as yf
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import requests
from typing import Dict, List, Optional
import json

# 500+ 기업 데이터베이스 import
from data.company_database import search_ticker_advanced, KOREA_TOP_COMPANIES, USA_TOP_COMPANIES

class StockDataFetcher:
    """주식 데이터 수집 클래스"""
    
    def __init__(self):
        self.cache = {}
    
    def get_stock_data(self, ticker: str, start_date: str = "2020-01-01", end_date: Optional[str] = None) -> pd.DataFrame:
        """
        주가 데이터 가져오기
        
        Args:
            ticker: 종목 코드 (예: "TSLA", "005930.KS")
            start_date: 시작일 (YYYY-MM-DD)
            end_date: 종료일 (YYYY-MM-DD), None이면 오늘
        
        Returns:
            DataFrame with OHLCV data
        """
        if end_date is None:
            end_date = datetime.now().strftime("%Y-%m-%d")
        
        print(f"📊 {ticker} 데이터 수집 중... ({start_date} ~ {end_date})")
        
        try:
            stock = yf.Ticker(ticker)
            df = stock.history(start=start_date, end=end_date)
            
            if df.empty:
                print(f"⚠️ {ticker} 데이터 없음")
                return pd.DataFrame()
            
            # 월별 집계
            df['Year'] = df.index.year
            df['Month'] = df.index.month
            
            print(f"✅ {ticker} 데이터 수집 완료: {len(df)} 행")
            return df
        
        except Exception as e:
            print(f"❌ {ticker} 데이터 수집 오류: {e}")
            return pd.DataFrame()
    
    def get_stock_info(self, ticker: str) -> Dict:
        """종목 기본 정보"""
        try:
            stock = yf.Ticker(ticker)
            info = stock.info
            
            return {
                'name': info.get('longName', ticker),
                'sector': info.get('sector', 'N/A'),
                'industry': info.get('industry', 'N/A'),
                'country': info.get('country', 'N/A'),
                'marketCap': info.get('marketCap', 0),
                'currency': info.get('currency', 'USD'),
                'description': info.get('longBusinessSummary', '')
            }
        except Exception as e:
            print(f"❌ {ticker} 정보 수집 오류: {e}")
            return {'name': ticker}
    
    def get_monthly_summary(self, df: pd.DataFrame) -> pd.DataFrame:
        """월별 요약 데이터"""
        if df.empty:
            return pd.DataFrame()
        
        monthly = df.groupby(['Year', 'Month']).agg({
            'Open': 'first',
            'High': 'max',
            'Low': 'min',
            'Close': 'last',
            'Volume': 'sum'
        }).reset_index()
        
        # 수익률 계산
        monthly['Returns'] = monthly['Close'].pct_change() * 100
        monthly['Date'] = pd.to_datetime(monthly[['Year', 'Month']].assign(DAY=1))
        
        return monthly
    
    def search_news(self, query: str, days_back: int = 30) -> List[Dict]:
        """
        뉴스 검색 (시뮬레이션)
        실제로는 News API, Google News API 등을 사용
        """
        # 실제 구현시 News API 키 필요
        # 여기서는 시뮬레이션 데이터 반환
        
        simulated_news = [
            {
                'date': (datetime.now() - timedelta(days=i)).strftime('%Y-%m-%d'),
                'title': f'{query} 관련 주요 뉴스 {i+1}',
                'summary': f'{query}에 대한 긍정적/부정적 소식',
                'sentiment': np.random.choice(['positive', 'neutral', 'negative'], p=[0.4, 0.4, 0.2])
            }
            for i in range(min(days_back, 10))
        ]
        
        return simulated_news
    
    def get_sentiment_score(self, news_list: List[Dict]) -> float:
        """뉴스 감성 점수 (-1 ~ 1)"""
        if not news_list:
            return 0.0
        
        sentiment_map = {'positive': 1, 'neutral': 0, 'negative': -1}
        scores = [sentiment_map[n['sentiment']] for n in news_list]
        
        return np.mean(scores)
    
    def get_investor_distribution(self, ticker: str) -> Dict:
        """
        투자자 분포 데이터 가져오기 (한국 주식 전용)
        
        Args:
            ticker: 종목 코드
            
        Returns:
            투자자별 보유 비율 및 거래 데이터
        """
        # 한국 주식인지 확인
        is_korean = ticker.endswith('.KS') or ticker.endswith('.KQ')
        
        if not is_korean:
            return {
                'available': False,
                'message': '투자자 분포는 한국 주식만 제공됩니다.'
            }
        
        try:
            # yfinance에서 주요 주주 정보 가져오기
            stock = yf.Ticker(ticker)
            
            # 한국 주식의 경우 일반적인 투자자 분포 시뮬레이션
            # 실제로는 KRX API나 증권사 API를 사용해야 함
            
            # 시뮬레이션 데이터 (실제 시장 평균에 기반)
            np.random.seed(hash(ticker) % 10000)
            
            # 기관/외국인/개인 비율 생성 (합계 100%)
            institutional = np.random.uniform(15, 35)  # 기관 15-35%
            foreign = np.random.uniform(10, 40)        # 외국인 10-40%
            individual = 100 - institutional - foreign # 개인 나머지
            
            # 최근 거래 추세 (매수세/매도세)
            foreign_trend = np.random.choice(['매수', '매도', '보합'], p=[0.4, 0.3, 0.3])
            institutional_trend = np.random.choice(['매수', '매도', '보합'], p=[0.35, 0.35, 0.3])
            
            return {
                'available': True,
                'distribution': {
                    'institutional': round(institutional, 1),  # 기관
                    'foreign': round(foreign, 1),              # 외국인
                    'individual': round(individual, 1)         # 개인
                },
                'trends': {
                    'foreign': foreign_trend,
                    'institutional': institutional_trend
                },
                'message': '투자자 분포 데이터 (시뮬레이션)'
            }
            
        except Exception as e:
            return {
                'available': False,
                'message': f'투자자 분포 데이터를 가져올 수 없습니다: {str(e)}'
            }
    
    def get_investor_timeline(self, ticker: str, df: pd.DataFrame) -> Dict:
        """
        시간대별 투자자 분포 데이터 생성 (한국 주식 전용)
        
        Args:
            ticker: 종목 코드
            df: 주가 데이터프레임
            
        Returns:
            시간대별 외국인/기관 순매수/순매도 데이터
        """
        is_korean = ticker.endswith('.KS') or ticker.endswith('.KQ')
        
        if not is_korean or df.empty:
            return {
                'available': False,
                'message': '시간대별 투자자 데이터는 한국 주식만 제공됩니다.'
            }
        
        try:
            # 최근 6개월 데이터 사용
            recent_df = df.tail(180).copy()
            
            # 시드 고정 (티커별 일관된 데이터)
            np.random.seed(hash(ticker) % 10000)
            
            # 주가 변동률
            price_change = recent_df['Close'].pct_change()
            
            # 외국인 매수/매도 분리 데이터 생성
            foreign_buy = []
            foreign_sell = []
            foreign_net = []
            
            for i, change in enumerate(price_change):
                if pd.isna(change):
                    buy = abs(np.random.normal(50, 20))
                    sell = abs(np.random.normal(50, 20))
                else:
                    # 주가 상승 시 매수 증가
                    if change > 0:
                        buy = abs(np.random.normal(100 + change * 2000, 30))
                        sell = abs(np.random.normal(60, 20))
                    else:
                        buy = abs(np.random.normal(60, 20))
                        sell = abs(np.random.normal(100 - change * 2000, 30))
                
                foreign_buy.append(round(buy, 1))
                foreign_sell.append(round(sell, 1))
                foreign_net.append(round(buy - sell, 1))
            
            # 기관 매수/매도 분리 데이터 (외국인과 반대 패턴)
            institutional_buy = []
            institutional_sell = []
            institutional_net = []
            
            for i, change in enumerate(price_change):
                if pd.isna(change):
                    buy = abs(np.random.normal(50, 20))
                    sell = abs(np.random.normal(50, 20))
                else:
                    # 외국인과 반대 (차익 실현)
                    if change > 0:
                        buy = abs(np.random.normal(50, 20))
                        sell = abs(np.random.normal(90 + change * 1500, 25))
                    else:
                        buy = abs(np.random.normal(80 - change * 1500, 25))
                        sell = abs(np.random.normal(50, 20))
                
                institutional_buy.append(round(buy, 1))
                institutional_sell.append(round(sell, 1))
                institutional_net.append(round(buy - sell, 1))
            
            # 데이터프레임에 추가
            recent_df['foreign_buy'] = foreign_buy
            recent_df['foreign_sell'] = foreign_sell
            recent_df['foreign_net'] = foreign_net
            recent_df['institutional_buy'] = institutional_buy
            recent_df['institutional_sell'] = institutional_sell
            recent_df['institutional_net'] = institutional_net
            
            return {
                'available': True,
                'dates': recent_df.index.tolist(),
                'foreign_buy': recent_df['foreign_buy'].tolist(),
                'foreign_sell': recent_df['foreign_sell'].tolist(),
                'foreign_net': recent_df['foreign_net'].tolist(),
                'institutional_buy': recent_df['institutional_buy'].tolist(),
                'institutional_sell': recent_df['institutional_sell'].tolist(),
                'institutional_net': recent_df['institutional_net'].tolist(),
                'prices': recent_df['Close'].tolist(),
                'message': '시간대별 투자자 매수/매도 데이터 (시뮬레이션)'
            }
            
        except Exception as e:
            return {
                'available': False,
                'message': f'시간대별 데이터를 가져올 수 없습니다: {str(e)}'
            }


# 주요 종목 리스트
POPULAR_STOCKS = {
    'US': [
        ('AAPL', 'Apple'),
        ('MSFT', 'Microsoft'),
        ('GOOGL', 'Google'),
        ('TSLA', 'Tesla'),
        ('AMZN', 'Amazon'),
        ('NVDA', 'NVIDIA'),
        ('META', 'Meta'),
        ('NFLX', 'Netflix'),
    ],
    'KR': [
        ('005930.KS', '삼성전자'),
        ('000660.KS', 'SK하이닉스'),
        ('035420.KS', 'NAVER'),
        ('035720.KS', '카카오'),
        ('051910.KS', 'LG화학'),
        ('006400.KS', '삼성SDI'),
        ('207940.KS', '삼성바이오로직스'),
        ('068270.KS', '셀트리온'),
        ('005380.KS', '현대차'),
        ('000270.KS', '기아'),
        ('012330.KS', '현대모비스'),
        ('105560.KS', 'KB금융'),
        ('055550.KS', '신한지주'),
        ('086790.KS', '하나금융지주'),
        ('017670.KS', 'SK텔레콤'),
        ('030200.KS', 'KT'),
        ('032640.KS', 'LG유플러스'),
        ('066570.KS', 'LG전자'),
        ('373220.KS', 'LG에너지솔루션'),
        ('034730.KS', 'SK'),
        ('096770.KS', 'SK이노베이션'),
        ('018260.KS', '삼성에스디에스'),
        ('009150.KS', '삼성전기'),
        ('028260.KS', '삼성물산'),
        ('010950.KS', 'S-Oil'),
        ('011170.KS', '롯데케미칼'),
        ('004020.KS', '현대제철'),
        ('009540.KS', 'HD한국조선해양'),
        ('003490.KS', '대한항공'),
        ('271560.KS', '오리온'),
    ]
}

# 한글 이름 → 티커 매핑
KOREAN_TO_TICKER = {
    # 삼성 계열
    '삼성전자': '005930.KS',
    '삼성': '005930.KS',
    '삼성sdi': '006400.KS',
    '삼성SDI': '006400.KS',
    '삼성바이오로직스': '207940.KS',
    '삼성바이오': '207940.KS',
    '삼성에스디에스': '018260.KS',
    '삼성전기': '009150.KS',
    '삼성물산': '028260.KS',
    
    # SK 계열
    'sk하이닉스': '000660.KS',
    'SK하이닉스': '000660.KS',
    'sk': '034730.KS',
    'SK': '034730.KS',
    'sk텔레콤': '017670.KS',
    'SK텔레콤': '017670.KS',
    'skt': '017670.KS',
    'sk이노베이션': '096770.KS',
    'SK이노베이션': '096770.KS',
    
    # 현대/기아 계열
    '현대차': '005380.KS',
    '현대자동차': '005380.KS',
    '현대': '005380.KS',
    'hyundai': '005380.KS',
    '기아': '000270.KS',
    'kia': '000270.KS',
    '현대모비스': '012330.KS',
    '현대제철': '004020.KS',
    
    # LG 계열
    'lg화학': '051910.KS',
    'LG화학': '051910.KS',
    'lg': '066570.KS',
    'LG': '066570.KS',
    'lg전자': '066570.KS',
    'LG전자': '066570.KS',
    'lg에너지솔루션': '373220.KS',
    'LG에너지솔루션': '373220.KS',
    'lg에너지': '373220.KS',
    'lg유플러스': '032640.KS',
    'LG유플러스': '032640.KS',
    'lgu+': '032640.KS',
    
    # 통신
    '네이버': '035420.KS',
    'naver': '035420.KS',
    '카카오': '035720.KS',
    'kakao': '035720.KS',
    'kt': '030200.KS',
    'KT': '030200.KS',
    
    # 금융
    'kb금융': '105560.KS',
    'KB금융': '105560.KS',
    '국민은행': '105560.KS',
    '신한지주': '055550.KS',
    '신한': '055550.KS',
    '신한은행': '055550.KS',
    '하나금융지주': '086790.KS',
    '하나금융': '086790.KS',
    '하나은행': '086790.KS',
    
    # 바이오/제약
    '셀트리온': '068270.KS',
    'celltrion': '068270.KS',
    
    # 조선/항공/화학
    'hd한국조선해양': '009540.KS',
    '한국조선해양': '009540.KS',
    '대한항공': '003490.KS',
    's-oil': '010950.KS',
    'soil': '010950.KS',
    '에쓰오일': '010950.KS',
    '롯데케미칼': '011170.KS',
    
    # 식품
    '오리온': '271560.KS',
    'orion': '271560.KS',
}

# 미국 주식 한글 이름 매핑
ENGLISH_TO_TICKER = {
    # 빅테크
    '테슬라': 'TSLA',
    'tesla': 'TSLA',
    '애플': 'AAPL',
    'apple': 'AAPL',
    '마이크로소프트': 'MSFT',
    'microsoft': 'MSFT',
    'ms': 'MSFT',
    '구글': 'GOOGL',
    'google': 'GOOGL',
    '알파벳': 'GOOGL',
    'alphabet': 'GOOGL',
    '아마존': 'AMZN',
    'amazon': 'AMZN',
    '엔비디아': 'NVDA',
    'nvidia': 'NVDA',
    '메타': 'META',
    'meta': 'META',
    '페이스북': 'META',
    'facebook': 'META',
    '넷플릭스': 'NFLX',
    'netflix': 'NFLX',
    
    # 반도체/칩
    '인텔': 'INTC',
    'intel': 'INTC',
    'amd': 'AMD',
    '퀄컴': 'QCOM',
    'qualcomm': 'QCOM',
    
    # 자동차
    '포드': 'F',
    'ford': 'F',
    'gm': 'GM',
    '제너럴모터스': 'GM',
    'general motors': 'GM',
    '루시드': 'LCID',
    'lucid': 'LCID',
    '리비안': 'RIVN',
    'rivian': 'RIVN',
    
    # 금융
    '뱅크오브아메리카': 'BAC',
    'bank of america': 'BAC',
    'bac': 'BAC',
    'jpmorgan': 'JPM',
    '제이피모건': 'JPM',
    'jpm': 'JPM',
    '골드만삭스': 'GS',
    'goldman sachs': 'GS',
    
    # 소비재
    '코카콜라': 'KO',
    'coca cola': 'KO',
    'cocacola': 'KO',
    '펩시': 'PEP',
    'pepsi': 'PEP',
    '맥도날드': 'MCD',
    'mcdonalds': 'MCD',
    '나이키': 'NKE',
    'nike': 'NKE',
    '스타벅스': 'SBUX',
    'starbucks': 'SBUX',
    
    # 항공우주
    '보잉': 'BA',
    'boeing': 'BA',
    '스페이스x': 'SPACE',
    'spacex': 'SPACE',
    
    # 기타
    '디즈니': 'DIS',
    'disney': 'DIS',
    '월마트': 'WMT',
    'walmart': 'WMT',
    '코스트코': 'COST',
    'costco': 'COST',
}

def search_ticker(query: str) -> str:
    """
    한글/영문 이름으로 티커 검색 (500+ 기업 지원)
    
    Args:
        query: 검색어 (예: "삼성전자", "두산에너빌리티", "테슬라", "AAPL", "애플")
    
    Returns:
        ticker code
    """
    # 새로운 고급 검색 사용
    return search_ticker_advanced(query)


if __name__ == "__main__":
    # 테스트
    fetcher = StockDataFetcher()
    
    # 테슬라 데이터 테스트
    print("\n=== TESLA 데이터 테스트 ===")
    tsla_data = fetcher.get_stock_data("TSLA", "2023-01-01")
    print(tsla_data.head())
    
    # 삼성전자 데이터 테스트
    print("\n=== 삼성전자 데이터 테스트 ===")
    samsung_data = fetcher.get_stock_data("005930.KS", "2023-01-01")
    print(samsung_data.head())
    
    # 월별 요약
    print("\n=== 월별 요약 ===")
    monthly = fetcher.get_monthly_summary(tsla_data)
    print(monthly)
