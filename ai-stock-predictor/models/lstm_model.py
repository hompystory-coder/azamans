"""
LSTM 기반 주가 예측 모델
"""

import numpy as np
import pandas as pd
from sklearn.preprocessing import MinMaxScaler
from sklearn.model_selection import train_test_split
import torch
import torch.nn as nn
from typing import Tuple, List
import warnings
warnings.filterwarnings('ignore')

class LSTMPredictor(nn.Module):
    """LSTM 신경망 모델"""
    
    def __init__(self, input_size=5, hidden_size=64, num_layers=2, output_size=1):
        super(LSTMPredictor, self).__init__()
        
        self.hidden_size = hidden_size
        self.num_layers = num_layers
        
        self.lstm = nn.LSTM(input_size, hidden_size, num_layers, batch_first=True, dropout=0.2)
        self.fc = nn.Linear(hidden_size, output_size)
    
    def forward(self, x):
        h0 = torch.zeros(self.num_layers, x.size(0), self.hidden_size).to(x.device)
        c0 = torch.zeros(self.num_layers, x.size(0), self.hidden_size).to(x.device)
        
        out, _ = self.lstm(x, (h0, c0))
        out = self.fc(out[:, -1, :])
        return out


class StockPricePredictor:
    """주가 예측 클래스"""
    
    def __init__(self, sequence_length=60):
        self.sequence_length = sequence_length
        self.scaler = MinMaxScaler()
        self.model = None
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
        
    def prepare_data(self, df: pd.DataFrame, features=['Open', 'High', 'Low', 'Close', 'Volume']) -> Tuple:
        """데이터 전처리"""
        if df.empty or len(df) < self.sequence_length + 1:
            return None, None, None, None
        
        # 특성 선택
        data = df[features].values
        
        # 정규화
        scaled_data = self.scaler.fit_transform(data)
        
        # 시퀀스 생성
        X, y = [], []
        for i in range(self.sequence_length, len(scaled_data)):
            X.append(scaled_data[i-self.sequence_length:i])
            y.append(scaled_data[i, 3])  # Close 가격
        
        X, y = np.array(X), np.array(y)
        
        # Train/Test 분할
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, shuffle=False)
        
        return X_train, X_test, y_train, y_test
    
    def train(self, X_train, y_train, epochs=50, batch_size=32, learning_rate=0.001):
        """모델 훈련"""
        print(f"🚀 모델 훈련 시작... (epochs={epochs})")
        
        # 데이터 -> Tensor
        X_train = torch.FloatTensor(X_train).to(self.device)
        y_train = torch.FloatTensor(y_train).reshape(-1, 1).to(self.device)
        
        # 모델 초기화
        input_size = X_train.shape[2]
        self.model = LSTMPredictor(input_size=input_size).to(self.device)
        
        criterion = nn.MSELoss()
        optimizer = torch.optim.Adam(self.model.parameters(), lr=learning_rate)
        
        # 훈련
        self.model.train()
        for epoch in range(epochs):
            total_loss = 0
            for i in range(0, len(X_train), batch_size):
                batch_X = X_train[i:i+batch_size]
                batch_y = y_train[i:i+batch_size]
                
                outputs = self.model(batch_X)
                loss = criterion(outputs, batch_y)
                
                optimizer.zero_grad()
                loss.backward()
                optimizer.step()
                
                total_loss += loss.item()
            
            if (epoch + 1) % 10 == 0:
                avg_loss = total_loss / (len(X_train) / batch_size)
                print(f"Epoch [{epoch+1}/{epochs}], Loss: {avg_loss:.6f}")
        
        print("✅ 훈련 완료!")
    
    def predict(self, X_test) -> np.ndarray:
        """예측"""
        if self.model is None:
            raise ValueError("모델이 훈련되지 않았습니다.")
        
        self.model.eval()
        with torch.no_grad():
            X_test = torch.FloatTensor(X_test).to(self.device)
            predictions = self.model(X_test).cpu().numpy()
        
        return predictions
    
    def predict_future(self, df: pd.DataFrame, days=30, features=['Open', 'High', 'Low', 'Close', 'Volume']) -> List[float]:
        """
        미래 주가 예측
        
        Args:
            df: 과거 데이터
            days: 예측할 일수
            features: 사용할 특성
        
        Returns:
            예측된 주가 리스트
        """
        if self.model is None:
            # 모델이 없으면 간단한 추세 기반 예측
            return self._simple_trend_prediction(df, days)
        
        # 최근 데이터 사용
        recent_data = df[features].tail(self.sequence_length).values
        scaled_data = self.scaler.transform(recent_data)
        
        predictions = []
        current_sequence = scaled_data.copy()
        
        self.model.eval()
        with torch.no_grad():
            for _ in range(days):
                # 예측
                X = torch.FloatTensor(current_sequence).unsqueeze(0).to(self.device)
                pred = self.model(X).cpu().numpy()[0, 0]
                predictions.append(pred)
                
                # 다음 입력을 위해 시퀀스 업데이트
                new_row = current_sequence[-1].copy()
                new_row[3] = pred  # Close 가격 업데이트
                current_sequence = np.vstack([current_sequence[1:], new_row])
        
        # 역정규화
        predictions = np.array(predictions).reshape(-1, 1)
        dummy = np.zeros((len(predictions), len(features)))
        dummy[:, 3] = predictions.flatten()
        predictions_original = self.scaler.inverse_transform(dummy)[:, 3]
        
        return predictions_original.tolist()
    
    def _simple_trend_prediction(self, df: pd.DataFrame, days=30) -> List[float]:
        """간단한 추세 기반 예측 (fallback)"""
        if df.empty:
            return [0] * days
        
        # 최근 30일 추세
        recent_prices = df['Close'].tail(30).values
        if len(recent_prices) < 2:
            return [recent_prices[-1]] * days
        
        # 선형 회귀로 추세 계산
        x = np.arange(len(recent_prices))
        coeffs = np.polyfit(x, recent_prices, 1)
        
        # 미래 예측
        future_x = np.arange(len(recent_prices), len(recent_prices) + days)
        predictions = np.polyval(coeffs, future_x)
        
        # 랜덤 변동성 추가 (현실적인 예측)
        volatility = np.std(recent_prices) * 0.3
        noise = np.random.normal(0, volatility, days)
        predictions += noise
        
        return predictions.tolist()


if __name__ == "__main__":
    # 테스트
    from data.fetcher import StockDataFetcher
    
    print("=== LSTM 예측 모델 테스트 ===")
    
    fetcher = StockDataFetcher()
    df = fetcher.get_stock_data("TSLA", "2022-01-01")
    
    predictor = StockPricePredictor(sequence_length=60)
    
    # 미래 예측 (간단한 추세 기반)
    print("\n미래 30일 예측:")
    predictions = predictor.predict_future(df, days=30)
    print(f"예측 가격: {predictions[:5]}... (첫 5일)")
    print(f"현재 가격: ${df['Close'].iloc[-1]:.2f}")
    print(f"30일 후 예측: ${predictions[-1]:.2f}")
