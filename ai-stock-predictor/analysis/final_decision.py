"""
AI 기반 최종 투자 의사결정 시스템
매수/매도/보유 결정을 내리는 종합 분석 엔진
"""

import pandas as pd
import numpy as np
from typing import Dict, List, Tuple
from datetime import datetime


class InvestmentDecisionEngine:
    """
    최종 투자 의사결정 엔진
    
    매수/매도/보유 결정을 위한 종합 분석
    - 기술적 분석
    - 펀더멘털 분석
    - 외부 데이터 분석
    - AI 예측 결과
    - 리스크 평가
    """
    
    def __init__(self):
        self.decision_thresholds = {
            'strong_buy': 80,    # 강력 매수
            'buy': 65,           # 매수
            'hold': 45,          # 보유
            'sell': 30,          # 매도
            'strong_sell': 0     # 강력 매도
        }
    
    def generate_final_report(self, 
                             ticker: str,
                             df: pd.DataFrame,
                             technical_signal: str,
                             enhanced_prediction: Dict,
                             external_data: Dict,
                             backtest_results: Dict,
                             patterns: Dict) -> Dict:
        """
        최종 투자 의사결정 보고서 생성
        
        Args:
            ticker: 종목 코드
            df: 주가 데이터프레임 (기술지표 포함)
            technical_signal: 기술적 신호 (BUY/HOLD/SELL)
            enhanced_prediction: 강화된 AI 예측 결과
            external_data: 외부 데이터 (뉴스, 재무, 경제 등)
            backtest_results: 백테스팅 결과
            patterns: 패턴 분석 결과
        
        Returns:
            최종 의사결정 보고서 딕셔너리
        """
        
        # 1. 기술적 분석 점수
        technical_score = self._calculate_technical_score(df, technical_signal)
        
        # 2. AI 예측 점수
        ai_score = self._calculate_ai_score(enhanced_prediction, backtest_results)
        
        # 3. 펀더멘털 점수
        fundamental_score = self._calculate_fundamental_score(external_data)
        
        # 4. 외부 환경 점수
        market_score = self._calculate_market_score(external_data)
        
        # 5. 리스크 평가
        risk_assessment = self._assess_risk(df, external_data, patterns)
        
        # 6. 종합 점수 계산 (가중 평균)
        total_score = (
            technical_score * 0.25 +      # 기술적 25%
            ai_score * 0.30 +              # AI 예측 30%
            fundamental_score * 0.25 +     # 펀더멘털 25%
            market_score * 0.20            # 시장 환경 20%
        )
        
        # 7. 최종 결정
        decision, decision_strength = self._make_decision(total_score)
        
        # 8. 투자 전략 제안
        strategy = self._generate_strategy(decision, decision_strength, risk_assessment, df)
        
        # 9. 핵심 근거 추출
        key_reasons = self._extract_key_reasons(
            technical_score, ai_score, fundamental_score, 
            market_score, risk_assessment, enhanced_prediction
        )
        
        # 10. 가격 목표 설정
        price_targets = self._calculate_price_targets(df, enhanced_prediction, decision)
        
        return {
            'ticker': ticker,
            'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
            
            # 최종 결정
            'decision': decision,                    # STRONG_BUY, BUY, HOLD, SELL, STRONG_SELL
            'decision_strength': decision_strength,  # 결정 강도 (%)
            'total_score': round(total_score, 1),
            
            # 세부 점수
            'scores': {
                'technical': round(technical_score, 1),
                'ai_prediction': round(ai_score, 1),
                'fundamental': round(fundamental_score, 1),
                'market_environment': round(market_score, 1)
            },
            
            # 리스크 평가
            'risk': risk_assessment,
            
            # 투자 전략
            'strategy': strategy,
            
            # 핵심 근거
            'key_reasons': key_reasons,
            
            # 가격 목표
            'price_targets': price_targets,
            
            # 신뢰도
            'confidence': enhanced_prediction.get('confidence', 75),
            
            # 예상 수익률
            'expected_return': enhanced_prediction.get('predicted_return', 0),
        }
    
    def _calculate_technical_score(self, df: pd.DataFrame, signal: str) -> float:
        """기술적 분석 점수 (0-100)"""
        score = 50.0
        
        if df.empty or len(df) < 2:
            return score
        
        latest = df.iloc[-1]
        current_price = latest['Close']
        
        # 1. 매매 신호 (+/- 20점)
        signal_scores = {'BUY': 20, 'HOLD': 0, 'SELL': -20}
        score += signal_scores.get(signal, 0)
        
        # 2. RSI (+/- 15점)
        if 'RSI' in df.columns and not pd.isna(latest['RSI']):
            rsi = latest['RSI']
            if 40 < rsi < 60:
                score += 15  # 이상적 범위
            elif 30 < rsi < 40:
                score += 10  # 과매도 영역 (매수 기회)
            elif rsi < 30:
                score += 12  # 강한 과매도
            elif 60 < rsi < 70:
                score += 5   # 약간 과매수
            else:
                score -= 15  # 과매수
        
        # 3. 이동평균선 배열 (+/- 20점)
        if all(col in df.columns for col in ['MA5', 'MA20', 'MA50']):
            ma5, ma20, ma50 = latest['MA5'], latest['MA20'], latest['MA50']
            if pd.notna(ma5) and pd.notna(ma20) and pd.notna(ma50):
                # 정배열 (상승 추세)
                if ma5 > ma20 > ma50:
                    score += 20
                # 역배열 (하락 추세)
                elif ma5 < ma20 < ma50:
                    score -= 20
                # 중립
                else:
                    score += 0
        
        # 4. MACD (+/- 10점)
        if 'MACD' in df.columns and 'Signal' in df.columns:
            macd, signal_line = latest['MACD'], latest['Signal']
            if pd.notna(macd) and pd.notna(signal_line):
                if macd > signal_line:
                    score += 10  # 골든 크로스
                else:
                    score -= 10  # 데드 크로스
        
        # 5. 볼린저 밴드 (+/- 10점)
        if all(col in df.columns for col in ['BB_upper', 'BB_lower']):
            bb_upper, bb_lower = latest['BB_upper'], latest['BB_lower']
            if pd.notna(bb_upper) and pd.notna(bb_lower):
                if current_price < bb_lower:
                    score += 10  # 하단 돌파 (매수)
                elif current_price > bb_upper:
                    score -= 10  # 상단 돌파 (매도)
        
        # 6. 거래량 (+/- 5점)
        if 'Volume_MA' in df.columns:
            volume, volume_ma = latest['Volume'], latest['Volume_MA']
            if pd.notna(volume) and pd.notna(volume_ma) and volume_ma > 0:
                if volume > volume_ma * 1.5:
                    score += 5  # 거래량 급증
        
        return max(0, min(100, score))
    
    def _calculate_ai_score(self, enhanced_prediction: Dict, backtest_results: Dict) -> float:
        """AI 예측 점수 (0-100)"""
        score = 50.0
        
        # 1. 예측 신뢰도 (+/- 20점)
        confidence = enhanced_prediction.get('confidence', 75)
        if confidence > 85:
            score += 20
        elif confidence > 75:
            score += 15
        elif confidence > 65:
            score += 10
        elif confidence < 50:
            score -= 15
        
        # 2. 예상 수익률 (+/- 25점)
        predicted_return = enhanced_prediction.get('predicted_return', 0)
        if predicted_return > 15:
            score += 25
        elif predicted_return > 10:
            score += 20
        elif predicted_return > 5:
            score += 15
        elif predicted_return > 0:
            score += 10
        elif predicted_return > -5:
            score -= 10
        else:
            score -= 25
        
        # 3. 백테스팅 정확도 (+/- 15점)
        if backtest_results and 'accuracy' in backtest_results:
            accuracy = backtest_results['accuracy']
            if accuracy > 85:
                score += 15
            elif accuracy > 75:
                score += 10
            elif accuracy > 65:
                score += 5
            elif accuracy < 50:
                score -= 15
        
        # 4. 예측 방향 일관성 (+/- 10점)
        prediction_direction = enhanced_prediction.get('direction', 'neutral')
        if prediction_direction == 'strong_up':
            score += 10
        elif prediction_direction == 'up':
            score += 5
        elif prediction_direction == 'strong_down':
            score -= 10
        elif prediction_direction == 'down':
            score -= 5
        
        return max(0, min(100, score))
    
    def _calculate_fundamental_score(self, external_data: Dict) -> float:
        """펀더멘털 분석 점수 (0-100)"""
        score = 50.0
        
        # 1. 재무 건전성 (+/- 20점)
        financial = external_data.get('financial', {})
        if financial:
            # ROE
            roe = financial.get('roe', 0)
            if roe > 20:
                score += 10
            elif roe > 15:
                score += 7
            elif roe > 10:
                score += 5
            elif roe < 5:
                score -= 10
            
            # 부채비율
            debt_ratio = financial.get('debt_to_equity', 0)
            if debt_ratio < 50:
                score += 5
            elif debt_ratio < 100:
                score += 3
            elif debt_ratio > 200:
                score -= 10
            
            # 이익률
            profit_margin = financial.get('profit_margin', 0)
            if profit_margin > 15:
                score += 5
            elif profit_margin > 10:
                score += 3
            elif profit_margin < 0:
                score -= 10
        
        # 2. 뉴스 감정 (+/- 15점)
        news = external_data.get('news', {})
        if news and news.get('count', 0) > 0:
            avg_sentiment = news.get('avg_sentiment', 0)
            if avg_sentiment > 0.3:
                score += 15
            elif avg_sentiment > 0.1:
                score += 10
            elif avg_sentiment > -0.1:
                score += 0
            elif avg_sentiment > -0.3:
                score -= 10
            else:
                score -= 15
            
            # 긍정 뉴스 비율
            positive_ratio = news.get('positive_ratio', 0)
            if positive_ratio > 0.7:
                score += 5
            elif positive_ratio < 0.3:
                score -= 5
        
        # 3. 애널리스트 평가 (+/- 15점)
        analyst = external_data.get('analyst', {})
        if analyst and analyst.get('target_price', 0) > 0:
            upside = analyst.get('upside_potential', 0)
            if upside > 20:
                score += 15
            elif upside > 10:
                score += 10
            elif upside > 0:
                score += 5
            elif upside < -10:
                score -= 15
        
        return max(0, min(100, score))
    
    def _calculate_market_score(self, external_data: Dict) -> float:
        """시장 환경 점수 (0-100)"""
        score = 50.0
        
        economic = external_data.get('economic', {})
        if economic:
            # VIX (변동성 지수)
            vix = economic.get('vix', 20)
            if vix < 15:
                score += 15  # 안정적
            elif vix < 20:
                score += 10
            elif vix < 25:
                score += 0
            elif vix < 30:
                score -= 10
            else:
                score -= 20  # 불안정
            
            # 시장 변화율
            market_change = economic.get('market_change', 0)
            if market_change > 1:
                score += 10
            elif market_change > 0:
                score += 5
            elif market_change < -1:
                score -= 10
            
            # 금리 환경
            yield_rate = economic.get('us_10y_yield', 0)
            if 2 < yield_rate < 4:
                score += 5  # 적정 범위
            elif yield_rate > 5:
                score -= 10  # 고금리
        
        # 시장 심리
        sentiment = external_data.get('sentiment', {})
        if sentiment:
            fear_greed = sentiment.get('value', 50)
            if fear_greed > 70:
                score -= 5   # 극도의 탐욕
            elif fear_greed > 55:
                score += 10  # 탐욕
            elif fear_greed > 45:
                score += 5   # 중립
            elif fear_greed > 25:
                score += 10  # 공포 (매수 기회)
            else:
                score += 5   # 극도의 공포
        
        return max(0, min(100, score))
    
    def _assess_risk(self, df: pd.DataFrame, external_data: Dict, patterns: Dict) -> Dict:
        """리스크 평가"""
        risk_factors = []
        risk_level = 'LOW'
        risk_score = 0
        
        if df.empty:
            return {
                'level': 'UNKNOWN',
                'score': 0,
                'factors': ['데이터 부족']
            }
        
        latest = df.iloc[-1]
        
        # 1. 변동성 리스크
        if 'Volatility' in df.columns:
            volatility = latest['Volatility']
            if pd.notna(volatility):
                if volatility > 0.05:
                    risk_score += 30
                    risk_factors.append(f"높은 변동성 ({volatility*100:.1f}%)")
                elif volatility > 0.03:
                    risk_score += 15
                    risk_factors.append(f"중간 변동성 ({volatility*100:.1f}%)")
        
        # 2. 재무 리스크
        financial = external_data.get('financial', {})
        if financial:
            debt_ratio = financial.get('debt_to_equity', 0)
            if debt_ratio > 200:
                risk_score += 25
                risk_factors.append(f"높은 부채비율 ({debt_ratio:.0f}%)")
            elif debt_ratio > 150:
                risk_score += 15
                risk_factors.append(f"부채비율 주의 ({debt_ratio:.0f}%)")
        
        # 3. 시장 리스크
        economic = external_data.get('economic', {})
        if economic:
            vix = economic.get('vix', 20)
            if vix > 30:
                risk_score += 25
                risk_factors.append(f"시장 불안 (VIX {vix:.1f})")
            elif vix > 25:
                risk_score += 15
                risk_factors.append(f"시장 변동성 증가 (VIX {vix:.1f})")
        
        # 4. 하락 추세 리스크
        trend = patterns.get('trend', {})
        if trend.get('direction') == 'down' and trend.get('strength', 0) > 0.5:
            risk_score += 20
            risk_factors.append("강한 하락 추세")
        
        # 5. 뉴스 리스크
        news = external_data.get('news', {})
        if news:
            avg_sentiment = news.get('avg_sentiment', 0)
            if avg_sentiment < -0.3:
                risk_score += 15
                risk_factors.append("부정적 뉴스 급증")
        
        # 리스크 레벨 결정
        if risk_score >= 60:
            risk_level = 'VERY_HIGH'
        elif risk_score >= 45:
            risk_level = 'HIGH'
        elif risk_score >= 30:
            risk_level = 'MEDIUM'
        elif risk_score >= 15:
            risk_level = 'LOW'
        else:
            risk_level = 'VERY_LOW'
        
        if not risk_factors:
            risk_factors.append("리스크 요인 없음")
        
        return {
            'level': risk_level,
            'score': risk_score,
            'factors': risk_factors
        }
    
    def _make_decision(self, total_score: float) -> Tuple[str, float]:
        """최종 의사결정"""
        if total_score >= self.decision_thresholds['strong_buy']:
            return 'STRONG_BUY', min(100, (total_score - 80) * 5 + 80)
        elif total_score >= self.decision_thresholds['buy']:
            return 'BUY', min(100, (total_score - 65) * 3.33 + 65)
        elif total_score >= self.decision_thresholds['hold']:
            return 'HOLD', 50 + (total_score - 45) * 1.5
        elif total_score >= self.decision_thresholds['sell']:
            return 'SELL', 30 + (total_score - 30) * 1.33
        else:
            return 'STRONG_SELL', min(30, total_score)
    
    def _generate_strategy(self, decision: str, strength: float, 
                          risk: Dict, df: pd.DataFrame) -> Dict:
        """투자 전략 제안"""
        current_price = df['Close'].iloc[-1] if not df.empty else 0
        
        strategies = {
            'STRONG_BUY': {
                'action': '적극 매수 추천',
                'description': '강력한 상승 신호가 포착되었습니다. 적극적인 매수를 고려하세요.',
                'position_size': '포트폴리오의 8-10%',
                'entry_strategy': '현재가에서 즉시 매수',
                'target_period': '1-3개월',
                'stop_loss': f"${current_price * 0.92:.2f} (-8%)"
            },
            'BUY': {
                'action': '매수 권장',
                'description': '긍정적인 신호가 나타났습니다. 분할 매수를 추천합니다.',
                'position_size': '포트폴리오의 5-7%',
                'entry_strategy': '2-3회 분할 매수',
                'target_period': '1-2개월',
                'stop_loss': f"${current_price * 0.90:.2f} (-10%)"
            },
            'HOLD': {
                'action': '보유 유지',
                'description': '현재 포지션을 유지하세요. 추가 신호를 기다립니다.',
                'position_size': '현재 포지션 유지',
                'entry_strategy': '신규 진입 비추천, 기존 보유 유지',
                'target_period': '관망',
                'stop_loss': f"${current_price * 0.88:.2f} (-12%)"
            },
            'SELL': {
                'action': '매도 권장',
                'description': '부정적인 신호가 감지되었습니다. 일부 또는 전체 매도를 고려하세요.',
                'position_size': '포지션의 50-70% 정리',
                'entry_strategy': '분할 매도',
                'target_period': '1-2주 내',
                'stop_loss': '즉시 손절 고려'
            },
            'STRONG_SELL': {
                'action': '즉시 매도',
                'description': '강한 하락 신호가 포착되었습니다. 포지션 정리를 권장합니다.',
                'position_size': '전량 매도',
                'entry_strategy': '즉시 매도',
                'target_period': '즉시',
                'stop_loss': '지체 없이 손절'
            }
        }
        
        base_strategy = strategies.get(decision, strategies['HOLD'])
        
        # 리스크 레벨에 따른 조정
        risk_adjustments = {
            'VERY_HIGH': '⚠️ 매우 높은 리스크: 포지션 크기를 50% 축소하세요',
            'HIGH': '⚠️ 높은 리스크: 포지션 크기를 30% 축소하세요',
            'MEDIUM': '⚡ 중간 리스크: 신중한 접근이 필요합니다',
            'LOW': '✅ 낮은 리스크: 정상적인 전략 실행 가능',
            'VERY_LOW': '✅ 매우 낮은 리스크: 안정적인 투자 환경'
        }
        
        base_strategy['risk_adjustment'] = risk_adjustments.get(
            risk['level'], '리스크 평가 필요'
        )
        
        return base_strategy
    
    def _extract_key_reasons(self, technical_score: float, ai_score: float,
                            fundamental_score: float, market_score: float,
                            risk: Dict, enhanced_prediction: Dict) -> Dict:
        """핵심 근거 추출 (긍정/부정 요소 분리)"""
        positive_reasons = []
        negative_reasons = []
        
        # 1. 점수 기반 분석
        scores = {
            '기술적 분석': technical_score,
            'AI 예측': ai_score,
            '펀더멘털': fundamental_score,
            '시장 환경': market_score
        }
        
        sorted_scores = sorted(scores.items(), key=lambda x: x[1], reverse=True)
        
        # 긍정적 요소
        for category, score in sorted_scores:
            if score > 70:
                positive_reasons.append(f"{category} 매우 긍정적 ({score:.0f}점)")
            elif score > 60:
                positive_reasons.append(f"{category} 긍정적 ({score:.0f}점)")
        
        # 부정적 요소
        for category, score in reversed(sorted_scores):
            if score < 40:
                negative_reasons.append(f"{category} 부정적 ({score:.0f}점)")
            elif score < 50:
                negative_reasons.append(f"{category} 약세 ({score:.0f}점)")
        
        # 2. AI 예측 분석
        confidence = enhanced_prediction.get('confidence', 0)
        if confidence > 85:
            positive_reasons.append(f"AI 예측 신뢰도 매우 높음 ({confidence:.0f}%)")
        elif confidence > 75:
            positive_reasons.append(f"AI 예측 신뢰도 높음 ({confidence:.0f}%)")
        elif confidence < 50:
            negative_reasons.append(f"AI 예측 신뢰도 낮음 ({confidence:.0f}%)")
        
        # 3. 예상 수익률
        predicted_return = enhanced_prediction.get('predicted_return', 0)
        if predicted_return > 10:
            positive_reasons.append(f"높은 수익률 전망 (+{predicted_return:.1f}%)")
        elif predicted_return > 5:
            positive_reasons.append(f"양호한 수익률 전망 (+{predicted_return:.1f}%)")
        elif predicted_return < -5:
            negative_reasons.append(f"하락 전망 ({predicted_return:.1f}%)")
        elif predicted_return < -10:
            negative_reasons.append(f"큰 하락 전망 ({predicted_return:.1f}%)")
        
        # 4. 리스크 평가
        if risk['level'] == 'VERY_HIGH':
            negative_reasons.append(f"매우 높은 리스크")
            negative_reasons.extend(risk['factors'][:2])
        elif risk['level'] == 'HIGH':
            negative_reasons.append(f"높은 리스크")
            negative_reasons.extend(risk['factors'][:2])
        elif risk['level'] in ['VERY_LOW', 'LOW']:
            positive_reasons.append(f"낮은 리스크 환경")
        
        # 기본 메시지 추가
        if len(positive_reasons) == 0:
            positive_reasons.append("특별한 긍정 요소 없음")
        if len(negative_reasons) == 0:
            negative_reasons.append("특별한 위험 요소 없음")
        
        return {
            'positive': positive_reasons[:10],  # 최대 10개
            'negative': negative_reasons[:10]   # 최대 10개
        }
    
    def _calculate_price_targets(self, df: pd.DataFrame, 
                                 enhanced_prediction: Dict, 
                                 decision: str) -> Dict:
        """가격 목표 설정"""
        if df.empty:
            return {
                'current': 0,
                'target_1m': 0,
                'target_3m': 0,
                'stop_loss': 0,
                'potential_gain': 0,
                'potential_loss': 0
            }
        
        current_price = df['Close'].iloc[-1]
        predicted_return = enhanced_prediction.get('predicted_return', 0)
        
        # 기본 목표가 설정
        target_1m = current_price * (1 + predicted_return / 100)
        
        # 3개월 목표가 (더 보수적)
        if decision in ['STRONG_BUY', 'BUY']:
            target_3m = target_1m * 1.2
        elif decision in ['STRONG_SELL', 'SELL']:
            target_3m = target_1m * 0.8
        else:
            target_3m = target_1m * 1.05
        
        # 손절가
        if decision in ['STRONG_BUY', 'BUY']:
            stop_loss = current_price * 0.90  # -10%
        elif decision == 'HOLD':
            stop_loss = current_price * 0.88  # -12%
        else:
            stop_loss = current_price * 0.95  # -5% (빠른 손절)
        
        potential_gain = ((target_1m - current_price) / current_price) * 100
        potential_loss = ((stop_loss - current_price) / current_price) * 100
        
        return {
            'current': round(current_price, 2),
            'target_1m': round(target_1m, 2),
            'target_3m': round(target_3m, 2),
            'stop_loss': round(stop_loss, 2),
            'potential_gain': round(potential_gain, 2),
            'potential_loss': round(potential_loss, 2),
            'risk_reward_ratio': round(abs(potential_gain / potential_loss) if potential_loss != 0 else 0, 2)
        }


def get_decision_emoji(decision: str) -> str:
    """의사결정 이모지"""
    emojis = {
        'STRONG_BUY': '🚀',
        'BUY': '📈',
        'HOLD': '⏸️',
        'SELL': '📉',
        'STRONG_SELL': '🔻'
    }
    return emojis.get(decision, '❓')


def get_decision_color(decision: str) -> str:
    """의사결정 색상"""
    colors = {
        'STRONG_BUY': '#10b981',  # 초록
        'BUY': '#34d399',         # 연두
        'HOLD': '#fbbf24',        # 노랑
        'SELL': '#f87171',        # 주황
        'STRONG_SELL': '#ef4444'  # 빨강
    }
    return colors.get(decision, '#6b7280')


def get_decision_name_kr(decision: str) -> str:
    """의사결정 한글명"""
    names = {
        'STRONG_BUY': '적극 매수',
        'BUY': '매수',
        'HOLD': '보유',
        'SELL': '매도',
        'STRONG_SELL': '즉시 매도'
    }
    return names.get(decision, '판단 보류')


# 테스트 코드
if __name__ == "__main__":
    print("최종 투자 의사결정 엔진 로드 완료!")
    engine = InvestmentDecisionEngine()
    print(f"결정 기준: {engine.decision_thresholds}")
