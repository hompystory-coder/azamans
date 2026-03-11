"""
투자자 매매 패턴 기반 AI 예측 모델
- 외국인/기관 투자자의 매수/매도 패턴 분석
- 선행지표로 활용하여 주가 예측 정확도 향상
"""

import numpy as np
import pandas as pd
from typing import Dict, Tuple, List
from datetime import datetime, timedelta

class InvestorPatternPredictor:
    """투자자 매매 패턴 분석 및 예측"""
    
    def __init__(self):
        self.pattern_weights = {
            'foreign_momentum': 0.35,      # 외국인 매수 모멘텀
            'institutional_momentum': 0.25, # 기관 매수 모멘텀
            'consensus_strength': 0.20,     # 외국인+기관 합의 강도
            'trend_persistence': 0.15,      # 추세 지속성
            'volume_surge': 0.05           # 거래량 급증
        }
    
    def analyze_investor_pattern(self, timeline_data: Dict) -> Dict:
        """
        투자자 매매 패턴 종합 분석
        
        Returns:
            - pattern_score: 0-100 (높을수록 긍정적)
            - signals: 매수/매도 신호 리스트
            - predictions: 향후 추세 예측
            - confidence: 신뢰도
        """
        
        if not timeline_data.get('available'):
            return self._default_analysis()
        
        # 데이터 추출
        foreign_net = np.array(timeline_data['foreign_net'])
        institutional_net = np.array(timeline_data['institutional_net'])
        prices = np.array(timeline_data['prices'])
        
        # 1. 외국인 매수 모멘텀 분석
        foreign_momentum = self._calculate_momentum(foreign_net)
        
        # 2. 기관 매수 모멘텀 분석
        institutional_momentum = self._calculate_momentum(institutional_net)
        
        # 3. 외국인+기관 합의 분석 (둘 다 같은 방향)
        consensus_strength = self._calculate_consensus(foreign_net, institutional_net)
        
        # 4. 추세 지속성 분석
        trend_persistence = self._calculate_trend_persistence(foreign_net, institutional_net, prices)
        
        # 5. 거래량 급증 패턴
        volume_surge = self._calculate_volume_surge(foreign_net, institutional_net)
        
        # 종합 패턴 점수 계산
        pattern_score = (
            foreign_momentum * self.pattern_weights['foreign_momentum'] +
            institutional_momentum * self.pattern_weights['institutional_momentum'] +
            consensus_strength * self.pattern_weights['consensus_strength'] +
            trend_persistence * self.pattern_weights['trend_persistence'] +
            volume_surge * self.pattern_weights['volume_surge']
        )
        
        # 신호 생성
        signals = self._generate_signals(
            foreign_momentum, institutional_momentum, 
            consensus_strength, trend_persistence, volume_surge
        )
        
        # 예측 생성
        predictions = self._generate_predictions(
            pattern_score, foreign_net, institutional_net, prices
        )
        
        # 신뢰도 계산
        confidence = self._calculate_confidence(
            foreign_net, institutional_net, len(prices)
        )
        
        return {
            'pattern_score': round(pattern_score, 2),
            'signals': signals,
            'predictions': predictions,
            'confidence': round(confidence, 2),
            'components': {
                'foreign_momentum': round(foreign_momentum, 2),
                'institutional_momentum': round(institutional_momentum, 2),
                'consensus_strength': round(consensus_strength, 2),
                'trend_persistence': round(trend_persistence, 2),
                'volume_surge': round(volume_surge, 2)
            }
        }
    
    def _calculate_momentum(self, net_values: np.ndarray, window: int = 20) -> float:
        """
        매수 모멘텀 계산 (최근 추세 강도)
        """
        if len(net_values) < window:
            window = len(net_values)
        
        recent = net_values[-window:]
        
        # 1. 평균 순매수
        avg_net = np.mean(recent)
        
        # 2. 추세 (선형 회귀 기울기)
        x = np.arange(len(recent))
        slope = np.polyfit(x, recent, 1)[0] if len(recent) > 1 else 0
        
        # 3. 일관성 (양수 비율)
        positive_ratio = np.sum(recent > 0) / len(recent)
        
        # 점수화 (0-100)
        momentum_score = (
            (avg_net / (np.abs(avg_net) + 100)) * 40 +  # 평균 기여도
            (slope / (abs(slope) + 10)) * 30 +          # 추세 기여도
            positive_ratio * 30                          # 일관성 기여도
        ) * 100
        
        return np.clip(momentum_score, 0, 100)
    
    def _calculate_consensus(self, foreign_net: np.ndarray, institutional_net: np.ndarray) -> float:
        """
        외국인+기관 합의 강도 (같은 방향으로 움직일 때 강세)
        """
        recent_window = 20
        foreign_recent = foreign_net[-recent_window:]
        institutional_recent = institutional_net[-recent_window:]
        
        # 같은 방향 (둘 다 양수 or 둘 다 음수)
        same_direction = np.sum(
            (foreign_recent > 0) == (institutional_recent > 0)
        )
        
        consensus_ratio = same_direction / len(foreign_recent)
        
        # 합의 강도 (둘 다 매수 시 가중치)
        both_buying = np.sum((foreign_recent > 0) & (institutional_recent > 0))
        buying_strength = both_buying / len(foreign_recent)
        
        consensus_score = (consensus_ratio * 50 + buying_strength * 50) * 100
        
        return np.clip(consensus_score, 0, 100)
    
    def _calculate_trend_persistence(self, foreign_net: np.ndarray, 
                                     institutional_net: np.ndarray, 
                                     prices: np.ndarray) -> float:
        """
        투자자 매매와 주가 추세의 일치도
        """
        window = 20
        if len(prices) < window + 1:
            window = len(prices) - 1
        
        # 가격 변화
        price_changes = np.diff(prices[-window-1:])
        
        # 투자자 순매수
        foreign_recent = foreign_net[-window:]
        institutional_recent = institutional_net[-window:]
        
        # 가격 상승 시 순매수 양수, 가격 하락 시 순매수 음수인 비율
        foreign_match = np.sum(
            ((price_changes > 0) & (foreign_recent > 0)) |
            ((price_changes < 0) & (foreign_recent < 0))
        ) / len(price_changes)
        
        institutional_match = np.sum(
            ((price_changes > 0) & (institutional_recent > 0)) |
            ((price_changes < 0) & (institutional_recent < 0))
        ) / len(price_changes)
        
        persistence_score = ((foreign_match + institutional_match) / 2) * 100
        
        return np.clip(persistence_score, 0, 100)
    
    def _calculate_volume_surge(self, foreign_net: np.ndarray, 
                                institutional_net: np.ndarray) -> float:
        """
        최근 거래량 급증 패턴 (큰 매수 = 강한 신호)
        """
        recent_window = 5
        historical_window = 20
        
        recent_foreign = foreign_net[-recent_window:]
        recent_institutional = institutional_net[-recent_window:]
        
        hist_foreign = foreign_net[-historical_window:-recent_window]
        hist_institutional = institutional_net[-historical_window:-recent_window]
        
        # 평균 대비 비율
        foreign_ratio = np.mean(np.abs(recent_foreign)) / (np.mean(np.abs(hist_foreign)) + 1)
        institutional_ratio = np.mean(np.abs(recent_institutional)) / (np.mean(np.abs(hist_institutional)) + 1)
        
        # 급증 점수 (1.5배 이상이면 만점)
        surge_score = min((foreign_ratio + institutional_ratio) / 3 * 100, 100)
        
        return surge_score
    
    def _generate_signals(self, foreign_mom: float, institutional_mom: float,
                         consensus: float, persistence: float, surge: float) -> List[str]:
        """
        매수/매도 신호 생성
        """
        signals = []
        
        # 🚀 초강력 매수 신호
        if foreign_mom > 70 and institutional_mom > 70:
            signals.append("🚀 **초강력 매수 신호**: 외국인+기관 모두 강력 매수 중")
        
        # 💎 강력 매수 신호
        elif foreign_mom > 60 or institutional_mom > 60:
            signals.append("💎 **강력 매수 신호**: 스마트머니 유입 지속")
        
        # ✅ 매수 신호
        elif consensus > 65:
            signals.append("✅ **매수 신호**: 외국인+기관 매수 합의")
        
        # 📊 중립
        elif 40 <= foreign_mom <= 60 and 40 <= institutional_mom <= 60:
            signals.append("📊 **중립**: 관망 국면, 추세 확인 필요")
        
        # ⚠️ 주의 신호
        elif foreign_mom < 40 and institutional_mom < 40:
            signals.append("⚠️ **주의 신호**: 스마트머니 이탈 조짐")
        
        # 🔴 매도 신호
        elif foreign_mom < 30 or institutional_mom < 30:
            signals.append("🔴 **매도 신호**: 투자자 매도세 증가")
        
        # 추가 신호
        if surge > 70:
            signals.append("⚡ **거래량 급증**: 최근 대량 거래 발생")
        
        if persistence > 70:
            signals.append("📈 **추세 강력**: 투자자 매매-주가 높은 상관관계")
        
        return signals
    
    def _generate_predictions(self, pattern_score: float, 
                            foreign_net: np.ndarray,
                            institutional_net: np.ndarray,
                            prices: np.ndarray) -> Dict:
        """
        향후 주가 예측
        """
        current_price = prices[-1]
        
        # 패턴 점수 기반 예측
        if pattern_score >= 70:
            direction = "상승"
            price_change_pct = 5 + (pattern_score - 70) * 0.5  # 5-20%
            probability = 75 + (pattern_score - 70) * 0.8
        elif pattern_score >= 55:
            direction = "소폭 상승"
            price_change_pct = 2 + (pattern_score - 55) * 0.2  # 2-5%
            probability = 60 + (pattern_score - 55) * 1.0
        elif pattern_score >= 45:
            direction = "보합"
            price_change_pct = 0
            probability = 50
        elif pattern_score >= 30:
            direction = "소폭 하락"
            price_change_pct = -2 - (45 - pattern_score) * 0.2  # -2 ~ -5%
            probability = 60 + (45 - pattern_score) * 0.5
        else:
            direction = "하락"
            price_change_pct = -5 - (30 - pattern_score) * 0.5  # -5 ~ -20%
            probability = 70 + (30 - pattern_score) * 0.8
        
        predicted_price = current_price * (1 + price_change_pct / 100)
        
        return {
            'direction': direction,
            'price_change_pct': round(price_change_pct, 2),
            'predicted_price': round(predicted_price, 2),
            'probability': round(min(probability, 95), 2),
            'timeframe': '1-2주'
        }
    
    def _calculate_confidence(self, foreign_net: np.ndarray, 
                             institutional_net: np.ndarray,
                             data_points: int) -> float:
        """
        예측 신뢰도 계산
        """
        # 1. 데이터 충분성
        data_confidence = min(data_points / 100, 1.0) * 30
        
        # 2. 패턴 일관성
        foreign_std = np.std(foreign_net[-20:]) if len(foreign_net) >= 20 else 999
        institutional_std = np.std(institutional_net[-20:]) if len(institutional_net) >= 20 else 999
        
        consistency = (1 - min(foreign_std / 200, 1)) * 35
        
        # 3. 최근 활동성
        recent_activity = min(
            (abs(np.mean(foreign_net[-5:])) + abs(np.mean(institutional_net[-5:]))) / 200,
            1.0
        ) * 35
        
        total_confidence = data_confidence + consistency + recent_activity
        
        return np.clip(total_confidence, 0, 100)
    
    def _default_analysis(self) -> Dict:
        """기본 분석 (데이터 없을 때)"""
        return {
            'pattern_score': 50,
            'signals': ["📊 투자자 데이터 부족 (한국 주식만 지원)"],
            'predictions': {
                'direction': '보합',
                'price_change_pct': 0,
                'predicted_price': 0,
                'probability': 50,
                'timeframe': '1-2주'
            },
            'confidence': 0,
            'components': {
                'foreign_momentum': 50,
                'institutional_momentum': 50,
                'consensus_strength': 50,
                'trend_persistence': 50,
                'volume_surge': 50
            }
        }
    
    def get_trading_recommendation(self, pattern_analysis: Dict, current_price: float) -> Dict:
        """
        투자 추천 생성
        """
        score = pattern_analysis['pattern_score']
        predictions = pattern_analysis['predictions']
        
        if score >= 70:
            action = "적극 매수"
            reason = "외국인+기관 강력 매수, 상승 모멘텀 강함"
            target_price = current_price * 1.15
            stop_loss = current_price * 0.95
        elif score >= 55:
            action = "매수"
            reason = "투자자 긍정적, 상승 가능성 높음"
            target_price = current_price * 1.08
            stop_loss = current_price * 0.97
        elif score >= 45:
            action = "보유/관망"
            reason = "투자자 중립, 추세 확인 필요"
            target_price = current_price * 1.03
            stop_loss = current_price * 0.98
        elif score >= 30:
            action = "매도 검토"
            reason = "투자자 이탈 조짐, 하락 가능성"
            target_price = current_price * 0.97
            stop_loss = current_price * 0.95
        else:
            action = "매도"
            reason = "스마트머니 이탈, 하락 우려"
            target_price = current_price * 0.90
            stop_loss = current_price * 0.92
        
        return {
            'action': action,
            'reason': reason,
            'target_price': round(target_price, 2),
            'stop_loss': round(stop_loss, 2),
            'confidence': pattern_analysis['confidence']
        }
