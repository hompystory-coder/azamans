"""
고급 주식 패턴 분석 모듈
- 과거 수익 패턴 분석
- 계절성 분석
- 트렌드 분석
- 변동성 패턴
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from typing import Dict, List, Tuple

class AdvancedPatternAnalyzer:
    """고급 패턴 분석 클래스"""
    
    def __init__(self, df: pd.DataFrame):
        """
        Args:
            df: OHLCV 데이터프레임
        """
        self.df = df.copy()
        self.df['Returns'] = self.df['Close'].pct_change()
        
    def analyze_all_patterns(self) -> Dict:
        """모든 패턴 분석"""
        return {
            'profit_patterns': self.analyze_profit_patterns(),
            'seasonal': self.analyze_seasonality(),
            'trend': self.analyze_trend(),
            'volatility': self.analyze_volatility(),
            'best_periods': self.find_best_periods(),
            'risk_reward': self.calculate_risk_reward(),
            'momentum': self.analyze_momentum()
        }
    
    def analyze_profit_patterns(self) -> Dict:
        """수익 패턴 분석"""
        returns = self.df['Returns'].dropna()
        
        # 연도별 수익률
        yearly_returns = self.df.groupby(self.df.index.year)['Returns'].sum() * 100
        
        # 월별 평균 수익률
        monthly_avg = self.df.groupby(self.df.index.month)['Returns'].mean() * 100
        
        # 요일별 평균 수익률
        daily_avg = self.df.groupby(self.df.index.dayofweek)['Returns'].mean() * 100
        
        # 승률 계산
        win_rate = (returns > 0).sum() / len(returns) * 100
        
        # 평균 수익/손실
        avg_win = returns[returns > 0].mean() * 100 if (returns > 0).any() else 0
        avg_loss = returns[returns < 0].mean() * 100 if (returns < 0).any() else 0
        
        return {
            'yearly_returns': yearly_returns.to_dict(),
            'monthly_avg': monthly_avg.to_dict(),
            'daily_avg': daily_avg.to_dict(),
            'win_rate': win_rate,
            'avg_win': avg_win,
            'avg_loss': avg_loss,
            'total_return': returns.sum() * 100,
            'best_year': yearly_returns.idxmax() if len(yearly_returns) > 0 else None,
            'best_month': monthly_avg.idxmax() if len(monthly_avg) > 0 else None
        }
    
    def analyze_seasonality(self) -> Dict:
        """계절성 분석"""
        # 분기별 수익률
        quarterly = self.df.groupby(self.df.index.quarter)['Returns'].mean() * 100
        
        # 월별 승률
        monthly_win_rate = {}
        for month in range(1, 13):
            month_data = self.df[self.df.index.month == month]['Returns'].dropna()
            if len(month_data) > 0:
                monthly_win_rate[month] = (month_data > 0).sum() / len(month_data) * 100
        
        return {
            'quarterly_returns': quarterly.to_dict(),
            'monthly_win_rate': monthly_win_rate,
            'best_quarter': quarterly.idxmax() if len(quarterly) > 0 else None,
            'worst_quarter': quarterly.idxmin() if len(quarterly) > 0 else None
        }
    
    def analyze_trend(self) -> Dict:
        """트렌드 분석"""
        # 최근 추세
        recent_30d = self.df['Close'].tail(30)
        recent_90d = self.df['Close'].tail(90)
        recent_365d = self.df['Close'].tail(365)
        
        # 수익률 계산
        ret_30d = (recent_30d.iloc[-1] / recent_30d.iloc[0] - 1) * 100 if len(recent_30d) > 0 else 0
        ret_90d = (recent_90d.iloc[-1] / recent_90d.iloc[0] - 1) * 100 if len(recent_90d) > 0 else 0
        ret_365d = (recent_365d.iloc[-1] / recent_365d.iloc[0] - 1) * 100 if len(recent_365d) > 0 else 0
        
        # 추세 방향
        ma_20 = self.df['Close'].rolling(20).mean()
        ma_60 = self.df['Close'].rolling(60).mean()
        
        current_price = self.df['Close'].iloc[-1]
        trend_direction = "상승" if current_price > ma_20.iloc[-1] > ma_60.iloc[-1] else \
                         "하락" if current_price < ma_20.iloc[-1] < ma_60.iloc[-1] else "횡보"
        
        return {
            'return_30d': ret_30d,
            'return_90d': ret_90d,
            'return_365d': ret_365d,
            'trend_direction': trend_direction,
            'strength': abs(ret_30d)
        }
    
    def analyze_volatility(self) -> Dict:
        """변동성 분석"""
        returns = self.df['Returns'].dropna()
        
        # 일간 변동성
        daily_vol = returns.std() * 100
        
        # 연간 변동성
        annual_vol = daily_vol * np.sqrt(252)
        
        # 최대 낙폭 (Max Drawdown)
        cumulative = (1 + returns).cumprod()
        running_max = cumulative.expanding().max()
        drawdown = (cumulative - running_max) / running_max * 100
        max_drawdown = drawdown.min()
        
        # 변동성 등급
        if annual_vol < 20:
            vol_grade = "낮음 (안정적)"
        elif annual_vol < 40:
            vol_grade = "보통"
        else:
            vol_grade = "높음 (위험)"
        
        return {
            'daily_volatility': daily_vol,
            'annual_volatility': annual_vol,
            'max_drawdown': max_drawdown,
            'volatility_grade': vol_grade
        }
    
    def find_best_periods(self) -> Dict:
        """최고 수익 기간 찾기"""
        returns = self.df['Returns'].dropna()
        
        # 연속 상승 최장 기간
        positive_streaks = []
        current_streak = 0
        for ret in returns:
            if ret > 0:
                current_streak += 1
            else:
                if current_streak > 0:
                    positive_streaks.append(current_streak)
                current_streak = 0
        
        max_streak = max(positive_streaks) if positive_streaks else 0
        
        # 30일 롤링 수익률
        rolling_30d = self.df['Close'].pct_change(30) * 100
        best_30d_return = rolling_30d.max()
        best_30d_date = rolling_30d.idxmax()
        
        return {
            'max_consecutive_wins': max_streak,
            'best_30d_return': best_30d_return,
            'best_30d_date': best_30d_date.strftime('%Y-%m-%d') if pd.notna(best_30d_date) else None
        }
    
    def calculate_risk_reward(self) -> Dict:
        """위험 대비 수익률"""
        returns = self.df['Returns'].dropna()
        
        # 샤프 비율 (연간화)
        mean_return = returns.mean() * 252
        std_return = returns.std() * np.sqrt(252)
        sharpe = mean_return / std_return if std_return > 0 else 0
        
        # 소티노 비율
        downside_returns = returns[returns < 0]
        downside_std = downside_returns.std() * np.sqrt(252) if len(downside_returns) > 0 else 0.001
        sortino = mean_return / downside_std if downside_std > 0 else 0
        
        return {
            'sharpe_ratio': sharpe,
            'sortino_ratio': sortino,
            'risk_grade': "우수" if sharpe > 1 else "보통" if sharpe > 0.5 else "낮음"
        }
    
    def analyze_momentum(self) -> Dict:
        """모멘텀 분석"""
        # 가격 모멘텀
        returns_20d = (self.df['Close'].iloc[-1] / self.df['Close'].iloc[-20] - 1) * 100 if len(self.df) >= 20 else 0
        returns_60d = (self.df['Close'].iloc[-1] / self.df['Close'].iloc[-60] - 1) * 100 if len(self.df) >= 60 else 0
        
        # 모멘텀 점수 (-100 ~ 100)
        momentum_score = (returns_20d * 0.6 + returns_60d * 0.4)
        
        # 모멘텀 등급
        if momentum_score > 10:
            momentum_grade = "강한 상승"
        elif momentum_score > 3:
            momentum_grade = "약한 상승"
        elif momentum_score > -3:
            momentum_grade = "횡보"
        elif momentum_score > -10:
            momentum_grade = "약한 하락"
        else:
            momentum_grade = "강한 하락"
        
        return {
            'momentum_20d': returns_20d,
            'momentum_60d': returns_60d,
            'momentum_score': momentum_score,
            'momentum_grade': momentum_grade
        }
    
    def generate_analysis_report(self) -> str:
        """분석 리포트 생성"""
        patterns = self.analyze_all_patterns()
        
        report = []
        report.append("📊 **과거 패턴 분석 결과**\n")
        
        # 수익 패턴
        profit = patterns['profit_patterns']
        report.append(f"💰 **총 수익률**: {profit['total_return']:.2f}%")
        report.append(f"📈 **승률**: {profit['win_rate']:.1f}% (100번 거래 시 {profit['win_rate']:.0f}번 수익)")
        report.append(f"✅ **평균 수익**: +{profit['avg_win']:.2f}%")
        report.append(f"❌ **평균 손실**: {profit['avg_loss']:.2f}%")
        
        if profit['best_year']:
            report.append(f"🏆 **최고의 해**: {profit['best_year']}년 ({profit['yearly_returns'][profit['best_year']]:.2f}%)")
        
        # 트렌드
        trend = patterns['trend']
        report.append(f"\n📈 **최근 추세**: {trend['trend_direction']}")
        report.append(f"   • 30일: {trend['return_30d']:+.2f}%")
        report.append(f"   • 90일: {trend['return_90d']:+.2f}%")
        report.append(f"   • 1년: {trend['return_365d']:+.2f}%")
        
        # 변동성
        vol = patterns['volatility']
        report.append(f"\n⚡ **변동성**: {vol['volatility_grade']}")
        report.append(f"   • 연간 변동성: {vol['annual_volatility']:.1f}%")
        report.append(f"   • 최대 낙폭: {vol['max_drawdown']:.2f}%")
        
        # 모멘텀
        momentum = patterns['momentum']
        report.append(f"\n🚀 **모멘텀**: {momentum['momentum_grade']}")
        report.append(f"   • 모멘텀 점수: {momentum['momentum_score']:.1f}")
        
        return "\n".join(report)
