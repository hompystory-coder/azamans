"""
백테스팅 및 예측 검증 모듈
- 월별 과거 예측 vs 실제 비교
- 3개월 미래 예측
- 정확도 검증
"""

import numpy as np
import pandas as pd
from datetime import datetime, timedelta
from typing import Dict, List, Tuple
import warnings
warnings.filterwarnings('ignore')

class BacktestingEngine:
    """백테스팅 엔진"""
    
    def __init__(self, df: pd.DataFrame):
        """
        Args:
            df: OHLCV 데이터프레임
        """
        self.df = df.copy()
        self.df = self.df.sort_index()
    
    def monthly_backtest(self, prediction_days=30) -> Dict:
        """
        월별 백테스팅
        - 과거 각 월의 시작점에서 30일 예측
        - 실제 가격과 비교
        
        Returns:
            {
                'dates': 예측 시작 날짜들,
                'predictions': 예측 가격들,
                'actuals': 실제 가격들,
                'errors': 오차율들,
                'accuracy': 평균 정확도
            }
        """
        results = {
            'dates': [],
            'predictions': [],
            'actuals': [],
            'errors': [],
            'prediction_points': []  # 각 예측의 전체 경로
        }
        
        # 최소 120일 데이터 필요
        if len(self.df) < 120:
            return results
        
        # 월별로 예측 수행 (최근 12개월)
        start_idx = max(90, len(self.df) - 365)  # 최대 1년 전부터
        
        for i in range(start_idx, len(self.df) - prediction_days - 1, 30):
            try:
                # 이 시점까지의 데이터로 예측
                train_data = self.df.iloc[:i]
                
                if len(train_data) < 60:
                    continue
                
                # 예측 수행
                predicted_prices = self._predict_from_point(train_data, prediction_days)
                
                # 실제 가격
                actual_data = self.df.iloc[i:i+prediction_days]
                if len(actual_data) < prediction_days:
                    continue
                
                actual_prices = actual_data['Close'].values
                
                # 결과 저장
                prediction_date = self.df.index[i]
                results['dates'].append(prediction_date)
                results['predictions'].append(predicted_prices[-1])  # 마지막 예측값
                results['actuals'].append(actual_prices[-1])  # 마지막 실제값
                
                # 오차율 계산
                error = abs(predicted_prices[-1] - actual_prices[-1]) / actual_prices[-1] * 100
                results['errors'].append(error)
                
                # 전체 예측 경로 저장
                results['prediction_points'].append({
                    'start_date': prediction_date,
                    'dates': actual_data.index.tolist(),
                    'predicted': predicted_prices.tolist(),
                    'actual': actual_prices.tolist()
                })
                
            except Exception as e:
                print(f"예측 오류 (인덱스 {i}): {e}")
                continue
        
        # 평균 정확도 계산
        if results['errors']:
            results['accuracy'] = 100 - np.mean(results['errors'])
            results['avg_error'] = np.mean(results['errors'])
        else:
            results['accuracy'] = 0
            results['avg_error'] = 0
        
        return results
    
    def _predict_from_point(self, historical_data: pd.DataFrame, days: int) -> np.ndarray:
        """
        특정 시점에서 미래 예측
        
        Args:
            historical_data: 과거 데이터
            days: 예측 일수
        
        Returns:
            예측된 가격 배열
        """
        # 최근 60일 데이터 사용
        recent_data = historical_data['Close'].tail(60).values
        
        if len(recent_data) < 30:
            # 데이터 부족 시 마지막 가격 반환
            return np.array([recent_data[-1]] * days)
        
        # 추세 + 변동성 기반 예측
        # 1. 선형 추세
        x = np.arange(len(recent_data))
        coeffs = np.polyfit(x, recent_data, 1)
        
        # 2. 미래 X 값
        future_x = np.arange(len(recent_data), len(recent_data) + days)
        trend_predictions = np.polyval(coeffs, future_x)
        
        # 3. 변동성 추가
        volatility = np.std(recent_data[-30:])  # 최근 30일 변동성
        
        # 4. 평균 회귀 효과 (극단적 예측 방지)
        mean_price = np.mean(recent_data[-30:])
        
        predictions = []
        last_price = recent_data[-1]
        
        for i in range(days):
            # 추세 예측
            trend_pred = trend_predictions[i]
            
            # 변동성 노이즈
            noise = np.random.normal(0, volatility * 0.2)
            
            # 평균 회귀 (극단 방지)
            pull_to_mean = (mean_price - last_price) * 0.05
            
            # 최종 예측
            pred = last_price + (trend_pred - last_price) * 0.7 + noise + pull_to_mean
            
            predictions.append(pred)
            last_price = pred
        
        return np.array(predictions)
    
    def predict_future_3months(self) -> Dict:
        """
        3개월(90일) 미래 예측 - 섬세하게
        
        Returns:
            {
                'dates': 날짜 리스트,
                'prices': 예측 가격 리스트,
                'upper_bound': 상한선,
                'lower_bound': 하한선,
                'confidence': 신뢰도
            }
        """
        # 최근 90일 데이터로 패턴 학습
        recent_data = self.df['Close'].tail(90).values
        
        if len(recent_data) < 30:
            return {'dates': [], 'prices': [], 'upper_bound': [], 'lower_bound': [], 'confidence': 0}
        
        # 여러 방법으로 예측하여 앙상블
        predictions_ensemble = []
        
        # 1. 단순 추세 예측
        trend_pred = self._trend_prediction(recent_data, 90)
        predictions_ensemble.append(trend_pred)
        
        # 2. 이동평균 기반 예측
        ma_pred = self._moving_average_prediction(recent_data, 90)
        predictions_ensemble.append(ma_pred)
        
        # 3. 계절성 고려 예측
        seasonal_pred = self._seasonal_prediction(self.df, 90)
        predictions_ensemble.append(seasonal_pred)
        
        # 앙상블 평균
        predictions = np.mean(predictions_ensemble, axis=0)
        
        # 신뢰 구간 계산
        volatility = np.std(recent_data[-30:])
        std_predictions = np.std(predictions_ensemble, axis=0)
        
        upper_bound = predictions + std_predictions + volatility * 0.5
        lower_bound = predictions - std_predictions - volatility * 0.5
        
        # 날짜 생성
        last_date = self.df.index[-1]
        future_dates = pd.date_range(start=last_date + timedelta(days=1), periods=90, freq='D')
        
        # 신뢰도 계산 (예측 변동성이 낮을수록 신뢰도 높음)
        confidence = max(0, 100 - (np.mean(std_predictions) / np.mean(predictions) * 200))
        
        return {
            'dates': future_dates.tolist(),
            'prices': predictions.tolist(),
            'upper_bound': upper_bound.tolist(),
            'lower_bound': lower_bound.tolist(),
            'confidence': confidence
        }
    
    def _trend_prediction(self, data: np.ndarray, days: int) -> np.ndarray:
        """추세 기반 예측"""
        x = np.arange(len(data))
        coeffs = np.polyfit(x, data, 2)  # 2차 다항식
        
        future_x = np.arange(len(data), len(data) + days)
        predictions = np.polyval(coeffs, future_x)
        
        return predictions
    
    def _moving_average_prediction(self, data: np.ndarray, days: int) -> np.ndarray:
        """이동평균 기반 예측"""
        ma_20 = np.mean(data[-20:])
        ma_50 = np.mean(data[-50:]) if len(data) >= 50 else ma_20
        
        # 이동평균 차이로 추세 파악
        trend = (ma_20 - ma_50) / ma_50
        
        predictions = []
        last_price = data[-1]
        
        for i in range(days):
            # 이동평균으로 회귀하는 경향
            pred = last_price * (1 + trend * 0.3)
            
            # 랜덤 워크 추가
            pred += np.random.normal(0, np.std(data[-30:]) * 0.1)
            
            predictions.append(pred)
            last_price = pred
        
        return np.array(predictions)
    
    def _seasonal_prediction(self, df: pd.DataFrame, days: int) -> np.ndarray:
        """계절성 고려 예측"""
        try:
            # 월별 평균 수익률 계산
            df_copy = df.copy()
            df_copy['Month'] = df_copy.index.month
            df_copy['Returns'] = df_copy['Close'].pct_change()
            
            # 월별 평균 수익률 (Series로 보장)
            monthly_returns = df_copy.groupby('Month')['Returns'].mean()
            
            # NaN 값 제거
            monthly_returns = monthly_returns.fillna(0)
            
        except Exception as e:
            print(f"계절성 계산 중 오류: {e}")
            # 오류 시 빈 Series
            monthly_returns = pd.Series(dtype=float)
        
        last_price = df['Close'].iloc[-1]
        predictions = []
        current_price = last_price
        
        last_date = df.index[-1]
        
        for i in range(days):
            future_date = last_date + timedelta(days=i+1)
            month = future_date.month
            
            # 해당 월의 평균 수익률 적용
            if isinstance(monthly_returns, pd.Series) and month in monthly_returns.index:
                daily_return = monthly_returns.loc[month] / 30  # 일일 수익률 근사
            else:
                daily_return = 0
            
            # 예측
            pred = current_price * (1 + daily_return)
            predictions.append(pred)
            current_price = pred
        
        return np.array(predictions)
    
    def calculate_accuracy_metrics(self, predictions: List[float], actuals: List[float]) -> Dict:
        """
        정확도 지표 계산
        
        Returns:
            {
                'mae': 평균 절대 오차,
                'mape': 평균 절대 퍼센트 오차,
                'rmse': 제곱근 평균 제곱 오차,
                'accuracy': 정확도 (%)
            }
        """
        predictions = np.array(predictions)
        actuals = np.array(actuals)
        
        # MAE (Mean Absolute Error)
        mae = np.mean(np.abs(predictions - actuals))
        
        # MAPE (Mean Absolute Percentage Error)
        mape = np.mean(np.abs((actuals - predictions) / actuals)) * 100
        
        # RMSE (Root Mean Squared Error)
        rmse = np.sqrt(np.mean((predictions - actuals) ** 2))
        
        # Accuracy
        accuracy = 100 - mape
        
        return {
            'mae': mae,
            'mape': mape,
            'rmse': rmse,
            'accuracy': max(0, accuracy)
        }
