"""
실제 데이터 기반 주가 예측 모델
- 실제 과거 데이터로 학습
- 예측 vs 실제 근접도 계산
- 정확도 검증 및 시각화
"""

import numpy as np
import pandas as pd
from sklearn.preprocessing import MinMaxScaler
from sklearn.ensemble import RandomForestRegressor, GradientBoostingRegressor
from sklearn.linear_model import LinearRegression
import warnings
warnings.filterwarnings('ignore')


class RealDataPredictor:
    """실제 데이터 기반 예측 모델"""
    
    def __init__(self):
        self.scaler = MinMaxScaler()
        self.models = {
            'rf': RandomForestRegressor(n_estimators=100, random_state=42),
            'gb': GradientBoostingRegressor(n_estimators=100, random_state=42),
            'lr': LinearRegression()
        }
        self.is_trained = False
        
    def prepare_features(self, df: pd.DataFrame) -> pd.DataFrame:
        """특징 공학 - 실제 데이터에서 유용한 특징 추출"""
        data = df.copy()
        
        # MultiIndex 컬럼을 단일 레벨로 변환
        if isinstance(data.columns, pd.MultiIndex):
            data.columns = data.columns.get_level_values(0)
        
        # 1. 이동평균
        data['MA5'] = data['Close'].rolling(window=5).mean()
        data['MA10'] = data['Close'].rolling(window=10).mean()
        data['MA20'] = data['Close'].rolling(window=20).mean()
        data['MA50'] = data['Close'].rolling(window=50).mean()
        
        # 2. 이동평균 교차
        data['MA5_MA20_diff'] = data['MA5'] - data['MA20']
        data['MA20_MA50_diff'] = data['MA20'] - data['MA50']
        
        # 3. RSI (Relative Strength Index)
        delta = data['Close'].diff()
        gain = (delta.where(delta > 0, 0)).rolling(window=14).mean()
        loss = (-delta.where(delta < 0, 0)).rolling(window=14).mean()
        rs = gain / loss
        data['RSI'] = 100 - (100 / (1 + rs))
        
        # 4. 볼린저 밴드
        rolling_std = data['Close'].rolling(window=20).std()
        data['BB_upper'] = data['MA20'] + (rolling_std * 2)
        data['BB_lower'] = data['MA20'] - (rolling_std * 2)
        data['BB_width'] = data['BB_upper'] - data['BB_lower']
        
        # 5. 거래량 특징
        data['Volume_MA5'] = data['Volume'].rolling(window=5).mean()
        data['Volume_ratio'] = data['Volume'] / data['Volume_MA5']
        
        # 6. 가격 변화율
        data['Returns_1d'] = data['Close'].pct_change(1)
        data['Returns_5d'] = data['Close'].pct_change(5)
        data['Returns_20d'] = data['Close'].pct_change(20)
        
        # 7. 변동성
        data['Volatility_20d'] = data['Returns_1d'].rolling(window=20).std()
        
        # 8. 모멘텀
        data['Momentum_10d'] = data['Close'] - data['Close'].shift(10)
        
        # 9. MACD
        ema12 = data['Close'].ewm(span=12, adjust=False).mean()
        ema26 = data['Close'].ewm(span=26, adjust=False).mean()
        data['MACD'] = ema12 - ema26
        data['MACD_signal'] = data['MACD'].ewm(span=9, adjust=False).mean()
        
        # 10. 가격 위치 (최근 52주 대비)
        data['High_52w'] = data['High'].rolling(window=252).max()
        data['Low_52w'] = data['Low'].rolling(window=252).min()
        data['Price_position'] = (data['Close'] - data['Low_52w']) / (data['High_52w'] - data['Low_52w'])
        
        # NaN 제거
        data = data.fillna(method='bfill').fillna(method='ffill')
        
        return data
    
    def create_sequences(self, data: np.ndarray, lookback: int = 60):
        """시계열 시퀀스 생성"""
        X, y = [], []
        for i in range(lookback, len(data)):
            X.append(data[i-lookback:i])
            y.append(data[i, 0])  # Close 가격만 예측
        return np.array(X), np.array(y)
    
    def train(self, df: pd.DataFrame, lookback: int = 60):
        """모델 학습 - 실제 데이터 사용"""
        print("🎓 실제 데이터로 AI 학습 시작...")
        
        # 특징 추출
        df_features = self.prepare_features(df)
        
        # 사용할 특징 컬럼
        feature_cols = [
            'Close', 'Volume',
            'MA5', 'MA10', 'MA20', 'MA50',
            'MA5_MA20_diff', 'MA20_MA50_diff',
            'RSI', 'BB_width', 'Volume_ratio',
            'Returns_1d', 'Returns_5d', 'Returns_20d',
            'Volatility_20d', 'Momentum_10d',
            'MACD', 'MACD_signal', 'Price_position'
        ]
        
        # 데이터 준비
        data = df_features[feature_cols].values
        
        # 정규화
        data_scaled = self.scaler.fit_transform(data)
        
        # 시퀀스 생성
        X, y = self.create_sequences(data_scaled, lookback)
        
        if len(X) == 0:
            print("⚠️ 데이터가 부족합니다. 최소 60일 이상 필요합니다.")
            return False
        
        # X를 2D로 변환 (RandomForest는 2D 입력 필요)
        X_2d = X.reshape(X.shape[0], -1)
        
        # 학습/검증 분할 (80/20)
        split_idx = int(len(X_2d) * 0.8)
        X_train, X_val = X_2d[:split_idx], X_2d[split_idx:]
        y_train, y_val = y[:split_idx], y[split_idx:]
        
        # 각 모델 학습
        for name, model in self.models.items():
            print(f"  - {name} 모델 학습 중...")
            model.fit(X_train, y_train)
            
            # 검증 정확도
            val_pred = model.predict(X_val)
            val_accuracy = 100 - np.mean(np.abs((val_pred - y_val) / y_val)) * 100
            print(f"    ✓ 검증 정확도: {val_accuracy:.2f}%")
        
        self.is_trained = True
        self.feature_cols = feature_cols
        self.lookback = lookback
        
        print("✅ 학습 완료!")
        return True
    
    def predict_future(self, df: pd.DataFrame, days: int = 30) -> dict:
        """미래 예측 - 실제 패턴 기반"""
        if not self.is_trained:
            print("⚠️ 모델이 학습되지 않았습니다. train()을 먼저 실행하세요.")
            return None
        
        # 특징 추출
        df_features = self.prepare_features(df)
        data = df_features[self.feature_cols].values
        
        # 정규화
        data_scaled = self.scaler.transform(data)
        
        # 마지막 lookback 기간 데이터
        last_sequence = data_scaled[-self.lookback:]
        
        # 각 모델로 예측
        predictions = {}
        
        for name, model in self.models.items():
            pred_list = []
            current_seq = last_sequence.copy()
            
            for _ in range(days):
                # 예측
                X_pred = current_seq.reshape(1, -1)
                pred_scaled = model.predict(X_pred)[0]
                
                # 저장
                pred_list.append(pred_scaled)
                
                # 다음 시퀀스 준비
                new_row = current_seq[-1].copy()
                new_row[0] = pred_scaled  # Close 가격만 업데이트
                current_seq = np.vstack([current_seq[1:], new_row])
            
            # 역정규화
            pred_array = np.array(pred_list).reshape(-1, 1)
            pred_array_full = np.zeros((len(pred_array), len(self.feature_cols)))
            pred_array_full[:, 0] = pred_array.flatten()
            pred_denorm = self.scaler.inverse_transform(pred_array_full)[:, 0]
            
            predictions[name] = pred_denorm
        
        # 앙상블 예측 (평균)
        ensemble_pred = np.mean([predictions[name] for name in predictions], axis=0)
        
        # 날짜 생성
        last_date = df.index[-1]
        future_dates = pd.date_range(start=last_date + pd.Timedelta(days=1), periods=days)
        
        return {
            'dates': future_dates,
            'predictions': ensemble_pred,
            'individual_models': predictions,
            'current_price': df['Close'].iloc[-1],
            'predicted_price': ensemble_pred[-1],
            'expected_return': ((ensemble_pred[-1] - df['Close'].iloc[-1]) / df['Close'].iloc[-1]) * 100
        }
    
    def validate_prediction(self, df: pd.DataFrame, days_back: int = 30) -> dict:
        """예측 검증 - 과거 데이터로 정확도 측정"""
        if len(df) < days_back + self.lookback + 30:
            return None
        
        # days_back일 전 시점에서 예측
        train_df = df.iloc[:-days_back]
        actual_df = df.iloc[-days_back:]
        
        # 재학습
        original_trained = self.is_trained
        self.train(train_df, self.lookback)
        
        # 예측
        pred_result = self.predict_future(train_df, days_back)
        
        if pred_result is None:
            return None
        
        # 실제값
        actual_prices = actual_df['Close'].values[:days_back]
        predicted_prices = pred_result['predictions'][:days_back]
        
        # 정확도 계산
        errors = np.abs((predicted_prices - actual_prices) / actual_prices) * 100
        accuracies = 100 - errors
        
        avg_accuracy = np.mean(accuracies)
        
        # 원래 학습 상태 복원
        if original_trained:
            self.train(df, self.lookback)
        
        return {
            'actual_prices': actual_prices,
            'predicted_prices': predicted_prices,
            'accuracies': accuracies,
            'avg_accuracy': avg_accuracy,
            'min_accuracy': np.min(accuracies),
            'max_accuracy': np.max(accuracies),
            'dates': actual_df.index[:days_back]
        }
    
    def calculate_proximity_score(self, predicted: float, actual: float) -> dict:
        """근접도 점수 계산"""
        error = abs((predicted - actual) / actual) * 100
        proximity = 100 - error
        
        # 등급
        if proximity >= 95:
            grade = 'S등급'
            color = '#10b981'
            emoji = '🎯'
        elif proximity >= 90:
            grade = 'A등급'
            color = '#3b82f6'
            emoji = '✅'
        elif proximity >= 85:
            grade = 'B등급'
            color = '#8b5cf6'
            emoji = '👍'
        elif proximity >= 80:
            grade = 'C등급'
            color = '#f59e0b'
            emoji = '⚠️'
        else:
            grade = 'D등급'
            color = '#ef4444'
            emoji = '❌'
        
        return {
            'proximity': proximity,
            'error': error,
            'grade': grade,
            'color': color,
            'emoji': emoji
        }


# 테스트 코드
if __name__ == "__main__":
    import yfinance as yf
    
    # 데이터 다운로드
    ticker = "AAPL"
    df = yf.download(ticker, start="2020-01-01", progress=False)
    
    print(f"=== {ticker} 실제 데이터 기반 예측 테스트 ===\n")
    
    # 모델 생성 및 학습
    predictor = RealDataPredictor()
    predictor.train(df)
    
    # 30일 예측
    print("\n📈 30일 후 예측:")
    pred_result = predictor.predict_future(df, days=30)
    current_price = float(pred_result['current_price'])
    predicted_price = float(pred_result['predicted_price'])
    expected_return = float(pred_result['expected_return'])
    
    print(f"현재 가격: ${current_price:.2f}")
    print(f"예상 가격: ${predicted_price:.2f}")
    print(f"예상 수익률: {expected_return:.2f}%")
    
    # 검증 (30일 전 데이터로 테스트)
    print("\n🎯 예측 정확도 검증 (30일 전 데이터):")
    validation = predictor.validate_prediction(df, days_back=30)
    if validation:
        print(f"평균 근접도: {validation['avg_accuracy']:.2f}%")
        print(f"최고 근접도: {validation['max_accuracy']:.2f}%")
        print(f"최저 근접도: {validation['min_accuracy']:.2f}%")
        
        # 근접도 점수
        proximity_score = predictor.calculate_proximity_score(
            validation['predicted_prices'][-1],
            validation['actual_prices'][-1]
        )
        print(f"\n{proximity_score['emoji']} 예측 등급: {proximity_score['grade']}")
        print(f"근접도: {proximity_score['proximity']:.2f}%")
