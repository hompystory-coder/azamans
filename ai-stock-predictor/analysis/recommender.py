"""
AI 기반 종목 추천 시스템
"""

import pandas as pd
import numpy as np
from typing import List, Dict
from data.fetcher import StockDataFetcher, POPULAR_STOCKS
from data.indicators import TechnicalIndicators
from models.lstm_model import StockPricePredictor

class StockRecommender:
    """종목 추천 시스템"""
    
    def __init__(self):
        self.fetcher = StockDataFetcher()
        self.predictor = StockPricePredictor()
    
    def analyze_stock(self, ticker: str) -> Dict:
        """
        종목 분석
        
        Returns:
            점수, 신호, 예측 등이 포함된 딕셔너리
        """
        print(f"\n📊 {ticker} 분석 중...")
        
        # 데이터 수집
        df = self.fetcher.get_stock_data(ticker, start_date="2023-01-01")
        if df.empty:
            return {'ticker': ticker, 'score': 0, 'error': 'No data'}
        
        # 기본 정보
        info = self.fetcher.get_stock_info(ticker)
        
        # 기술적 지표 추가
        df = TechnicalIndicators.add_all_indicators(df)
        
        # 매매 신호
        signal = TechnicalIndicators.get_trading_signal(df)
        
        # 미래 예측
        future_predictions = self.predictor.predict_future(df, days=30)
        
        # 점수 계산
        score = self._calculate_score(df, signal, future_predictions)
        
        # 수익률 계산
        current_price = df['Close'].iloc[-1]
        month_ago_price = df['Close'].iloc[-30] if len(df) >= 30 else df['Close'].iloc[0]
        monthly_return = ((current_price - month_ago_price) / month_ago_price) * 100
        
        predicted_price = future_predictions[-1]
        expected_return = ((predicted_price - current_price) / current_price) * 100
        
        return {
            'ticker': ticker,
            'name': info.get('name', ticker),
            'sector': info.get('sector', 'N/A'),
            'current_price': float(current_price),
            'predicted_price': float(predicted_price),
            'expected_return': float(expected_return),
            'monthly_return': float(monthly_return),
            'signal': signal,
            'score': float(score),
            'rsi': float(df['RSI'].iloc[-1]) if 'RSI' in df else 50,
            'volatility': float(df['Volatility'].iloc[-1]) if 'Volatility' in df else 0,
            'predictions': future_predictions[:30]  # 30일 예측
        }
    
    def _calculate_score(self, df: pd.DataFrame, signal: str, predictions: List[float]) -> float:
        """
        종합 점수 계산 (0-100)
        
        고려 요소:
        - 기술적 지표 (RSI, MACD)
        - 추세 (이동평균)
        - 예측 수익률
        - 변동성
        """
        score = 50.0  # 기본 점수
        
        if df.empty or len(df) < 2:
            return score
        
        latest = df.iloc[-1]
        current_price = latest['Close']
        
        # 1. 매매 신호 점수 (+/- 15점)
        signal_scores = {'BUY': 15, 'HOLD': 0, 'SELL': -15}
        score += signal_scores.get(signal, 0)
        
        # 2. RSI 점수 (+/- 10점)
        if 'RSI' in df.columns and not pd.isna(latest['RSI']):
            rsi = latest['RSI']
            if 30 < rsi < 70:
                score += 10  # 적정 범위
            elif rsi < 30:
                score += 5   # 과매도 (반등 가능)
            else:
                score -= 10  # 과매수
        
        # 3. 추세 점수 (+/- 15점)
        if 'MA20' in df.columns and not pd.isna(latest['MA20']):
            if current_price > latest['MA20']:
                score += 15  # 상승 추세
            else:
                score -= 10  # 하락 추세
        
        # 4. 예측 수익률 점수 (+/- 20점)
        if predictions and len(predictions) > 0:
            predicted_return = ((predictions[-1] - current_price) / current_price) * 100
            if predicted_return > 10:
                score += 20
            elif predicted_return > 5:
                score += 15
            elif predicted_return > 0:
                score += 10
            elif predicted_return > -5:
                score += 0
            else:
                score -= 20
        
        # 5. 변동성 페널티 (-10점)
        if 'Volatility' in df.columns and not pd.isna(latest['Volatility']):
            if latest['Volatility'] > 0.5:  # 높은 변동성
                score -= 10
        
        # 점수 범위 제한 (0-100)
        score = max(0, min(100, score))
        
        return score
    
    def get_top_recommendations(self, market='US', top_n=5) -> List[Dict]:
        """
        상위 추천 종목 반환
        
        Args:
            market: 'US' 또는 'KR'
            top_n: 추천 종목 수
        
        Returns:
            점수 순으로 정렬된 종목 리스트
        """
        print(f"\n🔍 {market} 시장 분석 중...")
        
        stocks = POPULAR_STOCKS.get(market, [])
        results = []
        
        for ticker, name in stocks:
            try:
                analysis = self.analyze_stock(ticker)
                if 'error' not in analysis:
                    results.append(analysis)
            except Exception as e:
                print(f"⚠️ {ticker} 분석 실패: {e}")
                continue
        
        # 점수 순으로 정렬
        results.sort(key=lambda x: x['score'], reverse=True)
        
        return results[:top_n]
    
    def get_combined_recommendations(self, kr_count=5, us_count=5) -> Dict:
        """국내 + 해외 추천 종목"""
        print("\n" + "="*50)
        print("🤖 AI 주식 분석 & 추천 시스템")
        print("="*50)
        
        kr_recommendations = self.get_top_recommendations('KR', kr_count)
        us_recommendations = self.get_top_recommendations('US', us_count)
        
        return {
            'domestic': kr_recommendations,
            'international': us_recommendations,
            'timestamp': pd.Timestamp.now().strftime('%Y-%m-%d %H:%M:%S')
        }


if __name__ == "__main__":
    # 테스트
    recommender = StockRecommender()
    
    # 테슬라 단일 분석
    print("\n=== TESLA 분석 ===")
    tesla_analysis = recommender.analyze_stock("TSLA")
    print(f"종목: {tesla_analysis['name']}")
    print(f"현재가: ${tesla_analysis['current_price']:.2f}")
    print(f"예상가 (30일): ${tesla_analysis['predicted_price']:.2f}")
    print(f"기대수익률: {tesla_analysis['expected_return']:.2f}%")
    print(f"신호: {tesla_analysis['signal']}")
    print(f"점수: {tesla_analysis['score']:.1f}/100")
    
    # 추천 종목 (간단 버전 - 전체는 시간 오래 걸림)
    print("\n=== 상위 추천 종목 (미국 2개) ===")
    recommendations = recommender.get_top_recommendations('US', 2)
    for i, rec in enumerate(recommendations, 1):
        print(f"\n{i}. {rec['name']} ({rec['ticker']})")
        print(f"   점수: {rec['score']:.1f}/100")
        print(f"   신호: {rec['signal']}")
        print(f"   기대수익률: {rec['expected_return']:.2f}%")
