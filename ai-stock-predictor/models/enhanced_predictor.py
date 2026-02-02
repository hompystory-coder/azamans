"""
강화된 예측 엔진 - 외부 데이터 통합
- 주가 데이터
- 뉴스 감정
- 재무 지표
- 경제 지표
- 투자자 심리
- 애널리스트 평가
"""

import numpy as np
import pandas as pd
from datetime import datetime, timedelta
from typing import Dict, List, Tuple
from sklearn.preprocessing import MinMaxScaler
import warnings
warnings.filterwarnings('ignore')

class EnhancedPredictor:
    """외부 데이터를 활용한 강화 예측 엔진"""
    
    def __init__(self):
        self.scaler = MinMaxScaler()
        
    def predict_with_external_data(
        self, 
        df: pd.DataFrame, 
        external_data: Dict, 
        days: int = 30
    ) -> Dict:
        """
        외부 데이터를 활용한 예측
        
        Args:
            df: 주가 데이터프레임
            external_data: 외부 데이터
            days: 예측 일수
        
        Returns:
            {
                'predictions': 예측 가격,
                'confidence': 신뢰도,
                'factors': 주요 영향 요인,
                'explanation': 예측 근거 설명
            }
        """
        # 1. 기술적 예측 (차트 기반)
        technical_pred = self._technical_prediction(df, days)
        
        # 2. 뉴스 기반 조정
        news_adjustment = self._news_adjustment(external_data.get('news', {}))
        
        # 3. 재무 기반 조정
        financial_adjustment = self._financial_adjustment(external_data.get('financial', {}))
        
        # 4. 경제 지표 기반 조정
        economic_adjustment = self._economic_adjustment(external_data.get('economic', {}))
        
        # 5. 심리 지표 기반 조정
        sentiment_adjustment = self._sentiment_adjustment(external_data.get('sentiment', {}))
        
        # 6. 애널리스트 기반 조정
        analyst_adjustment = self._analyst_adjustment(external_data.get('analyst', {}))
        
        # 가중 평균으로 최종 예측
        weights = {
            'technical': 0.35,      # 기술적 35%
            'news': 0.15,          # 뉴스 15%
            'financial': 0.20,     # 재무 20%
            'economic': 0.10,      # 경제 10%
            'sentiment': 0.10,     # 심리 10%
            'analyst': 0.10        # 애널리스트 10%
        }
        
        # 최종 예측 = 기술적 예측 * (1 + 조정치들의 가중 합)
        total_adjustment = (
            news_adjustment * weights['news'] +
            financial_adjustment * weights['financial'] +
            economic_adjustment * weights['economic'] +
            sentiment_adjustment * weights['sentiment'] +
            analyst_adjustment * weights['analyst']
        ) / (weights['news'] + weights['financial'] + weights['economic'] + 
             weights['sentiment'] + weights['analyst'])
        
        final_predictions = technical_pred * (1 + total_adjustment)
        
        # 신뢰도 계산
        confidence = self._calculate_confidence(
            external_data, 
            news_adjustment, 
            financial_adjustment, 
            economic_adjustment
        )
        
        # 주요 영향 요인 분석
        factors = self._analyze_factors(
            news_adjustment,
            financial_adjustment,
            economic_adjustment,
            sentiment_adjustment,
            analyst_adjustment
        )
        
        # 예측 근거 설명 생성
        explanation = self._generate_explanation(
            external_data,
            factors,
            total_adjustment
        )
        
        return {
            'predictions': final_predictions.tolist(),
            'confidence': float(confidence),
            'factors': factors,
            'explanation': explanation,
            'data_sources': self._list_data_sources(external_data)
        }
    
    def _technical_prediction(self, df: pd.DataFrame, days: int) -> np.ndarray:
        """기술적 예측 (차트 기반)"""
        recent_data = df['Close'].tail(60).values
        
        if len(recent_data) < 30:
            return np.array([recent_data[-1]] * days)
        
        # 추세 분석
        x = np.arange(len(recent_data))
        coeffs = np.polyfit(x, recent_data, 2)
        
        # 미래 예측
        future_x = np.arange(len(recent_data), len(recent_data) + days)
        predictions = np.polyval(coeffs, future_x)
        
        # 변동성 추가
        volatility = np.std(recent_data[-30:])
        noise = np.random.normal(0, volatility * 0.1, days)
        
        return predictions + noise
    
    def _news_adjustment(self, news_data: Dict) -> float:
        """뉴스 감정 기반 조정"""
        if not news_data:
            return 0.0
        
        sentiment = news_data.get('avg_sentiment', 0)
        positive_ratio = news_data.get('positive_ratio', 0.5)
        
        # 감정 점수를 조정 비율로 변환 (-0.1 ~ +0.1)
        adjustment = sentiment * 0.05 + (positive_ratio - 0.5) * 0.05
        
        return float(np.clip(adjustment, -0.1, 0.1))
    
    def _financial_adjustment(self, financial_data: Dict) -> float:
        """재무 지표 기반 조정"""
        if not financial_data:
            return 0.0
        
        score = 0
        
        # ROE (높을수록 좋음)
        roe = financial_data.get('roe', 0)
        if roe > 20:
            score += 0.03
        elif roe > 15:
            score += 0.02
        elif roe > 10:
            score += 0.01
        elif roe < 5:
            score -= 0.02
        
        # 부채비율 (낮을수록 좋음)
        debt_ratio = financial_data.get('debt_to_equity', 0)
        if debt_ratio < 50:
            score += 0.02
        elif debt_ratio < 100:
            score += 0.01
        elif debt_ratio > 200:
            score -= 0.02
        
        # 이익률
        profit_margin = financial_data.get('profit_margin', 0)
        if profit_margin > 15:
            score += 0.03
        elif profit_margin > 10:
            score += 0.02
        elif profit_margin < 5:
            score -= 0.01
        
        # 성장성
        revenue_growth = financial_data.get('revenue_growth', 0)
        if revenue_growth > 20:
            score += 0.03
        elif revenue_growth > 10:
            score += 0.01
        elif revenue_growth < 0:
            score -= 0.02
        
        return float(np.clip(score, -0.1, 0.1))
    
    def _economic_adjustment(self, economic_data: Dict) -> float:
        """경제 지표 기반 조정"""
        if not economic_data:
            return 0.0
        
        score = 0
        
        # VIX (낮을수록 좋음)
        vix = economic_data.get('vix', 20)
        if vix < 15:
            score += 0.02
        elif vix > 30:
            score -= 0.03
        elif vix > 25:
            score -= 0.01
        
        # 시장 변화
        market_change = economic_data.get('market_change', 0)
        if market_change > 3:
            score += 0.02
        elif market_change > 1:
            score += 0.01
        elif market_change < -3:
            score -= 0.02
        
        # 금리 (너무 높지 않을수록 좋음)
        yield_rate = economic_data.get('us_10y_yield', 0)
        if yield_rate > 5:
            score -= 0.02
        elif yield_rate > 4.5:
            score -= 0.01
        
        return float(np.clip(score, -0.1, 0.1))
    
    def _sentiment_adjustment(self, sentiment_data: Dict) -> float:
        """투자자 심리 기반 조정"""
        if not sentiment_data:
            return 0.0
        
        sentiment_score = sentiment_data.get('sentiment_score', 0)
        
        # 감정 점수를 조정 비율로 변환
        adjustment = sentiment_score / 1000  # -0.1 ~ +0.1 범위
        
        return float(np.clip(adjustment, -0.1, 0.1))
    
    def _analyst_adjustment(self, analyst_data: Dict) -> float:
        """애널리스트 평가 기반 조정"""
        if not analyst_data:
            return 0.0
        
        upside_potential = analyst_data.get('upside_potential', 0)
        
        # 상승 여력을 조정 비율로 변환
        adjustment = upside_potential / 500  # -0.1 ~ +0.1 범위
        
        return float(np.clip(adjustment, -0.1, 0.1))
    
    def _calculate_confidence(
        self, 
        external_data: Dict, 
        news_adj: float, 
        financial_adj: float, 
        economic_adj: float
    ) -> float:
        """신뢰도 계산"""
        base_confidence = 65.0
        
        # 데이터 품질에 따라 신뢰도 조정
        if external_data.get('news', {}).get('count', 0) > 10:
            base_confidence += 5
        
        if external_data.get('financial', {}).get('revenue', 0) > 0:
            base_confidence += 5
        
        if external_data.get('analyst', {}).get('analyst_count', 0) > 5:
            base_confidence += 5
        
        # 조정치들이 일관성 있으면 신뢰도 증가
        adjustments = [news_adj, financial_adj, economic_adj]
        if all(a > 0 for a in adjustments) or all(a < 0 for a in adjustments):
            base_confidence += 10  # 모두 같은 방향이면 신뢰도 증가
        
        return float(np.clip(base_confidence, 0, 100))
    
    def _analyze_factors(
        self,
        news_adj: float,
        financial_adj: float,
        economic_adj: float,
        sentiment_adj: float,
        analyst_adj: float
    ) -> List[Dict]:
        """주요 영향 요인 분석"""
        factors = []
        
        # 각 요인의 영향력 계산
        factor_impacts = {
            '뉴스 감정': news_adj,
            '재무 건전성': financial_adj,
            '경제 지표': economic_adj,
            '투자자 심리': sentiment_adj,
            '애널리스트 평가': analyst_adj
        }
        
        # 영향력이 큰 순서로 정렬
        sorted_factors = sorted(
            factor_impacts.items(), 
            key=lambda x: abs(x[1]), 
            reverse=True
        )
        
        for name, impact in sorted_factors:
            if abs(impact) > 0.01:  # 유의미한 영향만
                factors.append({
                    'name': name,
                    'impact': float(impact),
                    'direction': '상승 요인' if impact > 0 else '하락 요인',
                    'strength': '강함' if abs(impact) > 0.05 else '보통' if abs(impact) > 0.03 else '약함'
                })
        
        return factors
    
    def _generate_explanation(
        self, 
        external_data: Dict, 
        factors: List[Dict], 
        total_adjustment: float
    ) -> List[str]:
        """예측 근거 설명 생성"""
        explanations = []
        
        # 전체 방향성
        if total_adjustment > 0.03:
            explanations.append(f"📈 전체적으로 **상승 요인**이 우세합니다 ({total_adjustment*100:+.1f}%)")
        elif total_adjustment < -0.03:
            explanations.append(f"📉 전체적으로 **하락 요인**이 우세합니다 ({total_adjustment*100:+.1f}%)")
        else:
            explanations.append(f"➡️ **중립적** 상황입니다 ({total_adjustment*100:+.1f}%)")
        
        # 주요 요인 설명
        for factor in factors[:3]:  # 상위 3개만
            name = factor['name']
            direction = factor['direction']
            strength = factor['strength']
            impact = factor['impact']
            
            if name == '뉴스 감정':
                news = external_data.get('news', {})
                sentiment = news.get('avg_sentiment', 0)
                if sentiment > 0:
                    explanations.append(
                        f"✅ **뉴스 감정이 긍정적** ({strength}): "
                        f"최근 {news.get('count', 0)}개 뉴스의 {news.get('positive_ratio', 0)*100:.0f}%가 긍정적"
                    )
                else:
                    explanations.append(
                        f"⚠️ **뉴스 감정이 부정적** ({strength}): "
                        f"최근 {news.get('count', 0)}개 뉴스의 {news.get('negative_ratio', 0)*100:.0f}%가 부정적"
                    )
            
            elif name == '재무 건전성':
                financial = external_data.get('financial', {})
                roe = financial.get('roe', 0)
                debt = financial.get('debt_to_equity', 0)
                
                if impact > 0:
                    explanations.append(
                        f"✅ **재무 상태 우수** ({strength}): "
                        f"ROE {roe:.1f}%, 부채비율 {debt:.1f}%"
                    )
                else:
                    explanations.append(
                        f"⚠️ **재무 상태 주의** ({strength}): "
                        f"ROE {roe:.1f}%, 부채비율 {debt:.1f}%"
                    )
            
            elif name == '경제 지표':
                economic = external_data.get('economic', {})
                vix = economic.get('vix', 0)
                market_change = economic.get('market_change', 0)
                
                if impact > 0:
                    explanations.append(
                        f"✅ **경제 환경 양호** ({strength}): "
                        f"VIX {vix:.1f}, 시장 {market_change:+.1f}%"
                    )
                else:
                    explanations.append(
                        f"⚠️ **경제 환경 불안** ({strength}): "
                        f"VIX {vix:.1f}, 시장 {market_change:+.1f}%"
                    )
            
            elif name == '애널리스트 평가':
                analyst = external_data.get('analyst', {})
                upside = analyst.get('upside_potential', 0)
                target = analyst.get('target_price', 0)
                
                if upside > 0:
                    explanations.append(
                        f"✅ **애널리스트 낙관적** ({strength}): "
                        f"목표가 ${target:.2f} (상승여력 {upside:+.1f}%)"
                    )
                else:
                    explanations.append(
                        f"⚠️ **애널리스트 신중** ({strength}): "
                        f"목표가 ${target:.2f} (하락위험 {upside:+.1f}%)"
                    )
        
        return explanations
    
    def _list_data_sources(self, external_data: Dict) -> List[str]:
        """사용된 데이터 소스 목록"""
        sources = []
        
        if external_data.get('news', {}).get('count', 0) > 0:
            sources.append(f"📰 뉴스 {external_data['news']['count']}건")
        
        if external_data.get('financial', {}).get('revenue', 0) > 0:
            sources.append("💼 재무제표")
        
        if external_data.get('economic', {}).get('vix', 0) > 0:
            sources.append("📊 경제지표 (VIX, 금리, 시장지수)")
        
        if external_data.get('sentiment', {}).get('sentiment_score', 0) != 0:
            sources.append("💭 투자자 심리")
        
        if external_data.get('analyst', {}).get('analyst_count', 0) > 0:
            count = external_data['analyst']['analyst_count']
            sources.append(f"🎯 애널리스트 평가 ({count}명)")
        
        if external_data.get('insider', {}).get('insider_signal') != 'No Data':
            sources.append("👔 내부자 거래")
        
        return sources
