"""
기술적 지표 계산 모듈
- RSI, MACD, Bollinger Bands 등
"""

import pandas as pd
import numpy as np
from typing import Dict

class TechnicalIndicators:
    """기술적 지표 계산 클래스"""
    
    @staticmethod
    def calculate_rsi(df: pd.DataFrame, period: int = 14) -> pd.Series:
        """RSI (Relative Strength Index) 계산"""
        delta = df['Close'].diff()
        gain = (delta.where(delta > 0, 0)).rolling(window=period).mean()
        loss = (-delta.where(delta < 0, 0)).rolling(window=period).mean()
        
        rs = gain / loss
        rsi = 100 - (100 / (1 + rs))
        return rsi
    
    @staticmethod
    def calculate_macd(df: pd.DataFrame) -> Dict[str, pd.Series]:
        """MACD 계산"""
        exp1 = df['Close'].ewm(span=12, adjust=False).mean()
        exp2 = df['Close'].ewm(span=26, adjust=False).mean()
        
        macd = exp1 - exp2
        signal = macd.ewm(span=9, adjust=False).mean()
        histogram = macd - signal
        
        return {
            'macd': macd,
            'signal': signal,
            'histogram': histogram
        }
    
    @staticmethod
    def calculate_bollinger_bands(df: pd.DataFrame, period: int = 20) -> Dict[str, pd.Series]:
        """볼린저 밴드 계산"""
        sma = df['Close'].rolling(window=period).mean()
        std = df['Close'].rolling(window=period).std()
        
        upper = sma + (std * 2)
        lower = sma - (std * 2)
        
        return {
            'upper': upper,
            'middle': sma,
            'lower': lower
        }
    
    @staticmethod
    def calculate_moving_averages(df: pd.DataFrame) -> Dict[str, pd.Series]:
        """이동평균선 계산"""
        return {
            'MA5': df['Close'].rolling(window=5).mean(),
            'MA20': df['Close'].rolling(window=20).mean(),
            'MA60': df['Close'].rolling(window=60).mean(),
            'MA120': df['Close'].rolling(window=120).mean(),
        }
    
    @staticmethod
    def calculate_volatility(df: pd.DataFrame, period: int = 20) -> pd.Series:
        """변동성 계산 (표준편차)"""
        returns = df['Close'].pct_change()
        volatility = returns.rolling(window=period).std() * np.sqrt(252)  # 연간화
        return volatility
    
    @staticmethod
    def add_all_indicators(df: pd.DataFrame) -> pd.DataFrame:
        """모든 지표를 DataFrame에 추가"""
        df = df.copy()
        
        # RSI
        df['RSI'] = TechnicalIndicators.calculate_rsi(df)
        
        # MACD
        macd = TechnicalIndicators.calculate_macd(df)
        df['MACD'] = macd['macd']
        df['MACD_Signal'] = macd['signal']
        df['MACD_Histogram'] = macd['histogram']
        
        # 볼린저 밴드
        bb = TechnicalIndicators.calculate_bollinger_bands(df)
        df['BB_Upper'] = bb['upper']
        df['BB_Middle'] = bb['middle']
        df['BB_Lower'] = bb['lower']
        
        # 이동평균
        ma = TechnicalIndicators.calculate_moving_averages(df)
        for key, value in ma.items():
            df[key] = value
        
        # 변동성
        df['Volatility'] = TechnicalIndicators.calculate_volatility(df)
        
        return df
    
    @staticmethod
    def get_trading_signal(df: pd.DataFrame) -> str:
        """
        매매 신호 생성
        
        Returns:
            'BUY', 'SELL', 'HOLD'
        """
        if df.empty or len(df) < 2:
            return 'HOLD'
        
        latest = df.iloc[-1]
        prev = df.iloc[-2]
        
        signals = []
        
        # RSI 신호
        if latest['RSI'] < 30:
            signals.append('BUY')  # 과매도
        elif latest['RSI'] > 70:
            signals.append('SELL')  # 과매수
        
        # MACD 신호
        if latest['MACD'] > latest['MACD_Signal'] and prev['MACD'] <= prev['MACD_Signal']:
            signals.append('BUY')  # 골든크로스
        elif latest['MACD'] < latest['MACD_Signal'] and prev['MACD'] >= prev['MACD_Signal']:
            signals.append('SELL')  # 데드크로스
        
        # 이동평균 신호
        if latest['Close'] > latest['MA20'] and prev['Close'] <= prev['MA20']:
            signals.append('BUY')
        elif latest['Close'] < latest['MA20'] and prev['Close'] >= prev['MA20']:
            signals.append('SELL')
        
        # 다수결
        if signals.count('BUY') > signals.count('SELL'):
            return 'BUY'
        elif signals.count('SELL') > signals.count('BUY'):
            return 'SELL'
        else:
            return 'HOLD'


if __name__ == "__main__":
    # 테스트
    from data.fetcher import StockDataFetcher
    
    fetcher = StockDataFetcher()
    df = fetcher.get_stock_data("TSLA", "2023-01-01")
    
    print("\n=== 기술적 지표 추가 ===")
    df_with_indicators = TechnicalIndicators.add_all_indicators(df)
    print(df_with_indicators.tail())
    
    print("\n=== 매매 신호 ===")
    signal = TechnicalIndicators.get_trading_signal(df_with_indicators)
    print(f"현재 신호: {signal}")
