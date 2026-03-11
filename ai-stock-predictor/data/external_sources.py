"""
외부 데이터 소스 수집 모듈 v6.0
- v5.0 기존 소스:
  - 뉴스 (기술기사, 정책, 여의도/월가 소식)
  - 기업 정보 (재무제표, 실적발표)
  - 경제 지표 (금리, 환율, 원자재)
  - 투자자 심리 (공포/탐욕 지수)
  - 애널리스트 평가
  - 내부자 거래

- v6.0 신규 소스:
  - 소셜 미디어 분석 (YouTube, Twitter/X, Reddit)
  - CEO/오너 마인드 (경영진 발언, 컨퍼런스콜)
  - 정책/규제 정보 (정부 정책, 산업 규제)
  - 공급망/파트너십 (공급업체, 파트너사 분석)
  - 특허/기술력 (R&D 투자, 특허 포트폴리오)
  - ESG 평가 (환경/사회/지배구조)
"""

import requests
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from typing import Dict, List, Optional
import yfinance as yf
from bs4 import BeautifulSoup
import warnings
warnings.filterwarnings('ignore')

# v6.0 신규 모듈 임포트
try:
    from .social_media_analyzer import SocialMediaAnalyzer
    from .ceo_sentiment_analyzer import CEOSentimentAnalyzer
    from .policy_regulation_analyzer import PolicyRegulationAnalyzer
    from .supply_chain_analyzer import SupplyChainAnalyzer
    from .patent_tech_analyzer import PatentTechAnalyzer
    from .esg_analyzer import ESGAnalyzer
    V6_MODULES_AVAILABLE = True
except ImportError:
    V6_MODULES_AVAILABLE = False
    print("⚠️ v6.0 모듈을 찾을 수 없습니다. v5.0 기능만 사용됩니다.")

class ExternalDataCollector:
    """외부 데이터 수집기"""
    
    def __init__(self, ticker: str):
        self.ticker = ticker
        self.is_korean = ticker.endswith('.KS') or ticker.endswith('.KQ')
        
    def collect_all_data(self, start_date: str = None) -> Dict:
        """
        모든 외부 데이터 수집 (v6.0 확장)
        
        Returns:
            {
                # v5.0 기존 데이터
                'news': 뉴스 데이터,
                'financial': 재무 데이터,
                'economic': 경제 지표,
                'sentiment': 투자자 심리,
                'insider': 내부자 거래,
                'analyst': 애널리스트 평가,
                
                # v6.0 신규 데이터
                'social_media': 소셜 미디어 분석,
                'ceo_sentiment': CEO/오너 마인드,
                'policy_regulation': 정책/규제 정보,
                'supply_chain': 공급망/파트너십,
                'patent_tech': 특허/기술력,
                'esg': ESG 평가
            }
        """
        print(f"📊 {self.ticker} 외부 데이터 수집 중 (v6.0)...")
        
        # 기본 정보 수집
        stock_info = self._get_stock_info()
        company_name = stock_info.get('shortName', self.ticker)
        sector = stock_info.get('sector', '')
        
        # v5.0 기존 데이터
        data = {
            'news': self.get_news_data(),
            'financial': self.get_financial_data(),
            'economic': self.get_economic_indicators(),
            'sentiment': self.get_sentiment_data(),
            'insider': self.get_insider_trading(),
            'analyst': self.get_analyst_ratings(),
        }
        
        # v6.0 신규 데이터 수집
        if V6_MODULES_AVAILABLE:
            try:
                print("🆕 v6.0 고급 분석 수집 중...")
                
                # 1. 소셜 미디어 분석
                social_analyzer = SocialMediaAnalyzer(self.ticker, company_name)
                data['social_media'] = social_analyzer.analyze_all_sources()
                
                # 2. CEO/오너 마인드
                ceo_analyzer = CEOSentimentAnalyzer(self.ticker, company_name)
                data['ceo_sentiment'] = ceo_analyzer.analyze_ceo_sentiment()
                
                # 3. 정책/규제 정보
                policy_analyzer = PolicyRegulationAnalyzer(self.ticker, sector)
                data['policy_regulation'] = policy_analyzer.analyze_policy_impact()
                
                # 4. 공급망/파트너십
                supply_analyzer = SupplyChainAnalyzer(self.ticker, company_name)
                data['supply_chain'] = supply_analyzer.analyze_supply_chain()
                
                # 5. 특허/기술력
                patent_analyzer = PatentTechAnalyzer(self.ticker, company_name)
                data['patent_tech'] = patent_analyzer.analyze_innovation()
                
                # 6. ESG 평가
                esg_analyzer = ESGAnalyzer(self.ticker, company_name)
                data['esg'] = esg_analyzer.analyze_esg()
                
                print("✅ v6.0 모든 데이터 수집 완료!")
                
            except Exception as e:
                print(f"⚠️ v6.0 데이터 수집 중 일부 오류: {e}")
                # 오류 시 빈 데이터로 채움
                data['social_media'] = {}
                data['ceo_sentiment'] = {}
                data['policy_regulation'] = {}
                data['supply_chain'] = {}
                data['patent_tech'] = {}
                data['esg'] = {}
        else:
            # v6.0 모듈 없으면 빈 딕셔너리
            data['social_media'] = {}
            data['ceo_sentiment'] = {}
            data['policy_regulation'] = {}
            data['supply_chain'] = {}
            data['patent_tech'] = {}
            data['esg'] = {}
        
        return data
    
    def _get_stock_info(self) -> Dict:
        """주식 기본 정보 조회"""
        try:
            stock = yf.Ticker(self.ticker)
            return stock.info
        except:
            return {}
    
    def get_news_data(self) -> Dict:
        """
        뉴스 데이터 수집
        - 기술 기사
        - 정책 뉴스
        - 여의도/월가 소식
        """
        try:
            stock = yf.Ticker(self.ticker)
            news = stock.news if hasattr(stock, 'news') else []
            
            # 뉴스 감정 분석
            sentiments = []
            categories = []
            
            for item in news[:20]:  # 최근 20개
                title = item.get('title', '')
                
                # 간단한 키워드 기반 감정 분석
                sentiment = self._analyze_sentiment(title)
                sentiments.append(sentiment)
                
                # 카테고리 분류
                category = self._categorize_news(title)
                categories.append(category)
            
            # 평균 감정 점수
            avg_sentiment = np.mean(sentiments) if sentiments else 0
            
            # 카테고리별 개수
            category_counts = pd.Series(categories).value_counts().to_dict() if categories else {}
            
            return {
                'count': len(news),
                'avg_sentiment': float(avg_sentiment),
                'categories': category_counts,
                'recent_news': news[:5],  # 최근 5개
                'positive_ratio': sum(1 for s in sentiments if s > 0) / max(len(sentiments), 1),
                'negative_ratio': sum(1 for s in sentiments if s < 0) / max(len(sentiments), 1)
            }
            
        except Exception as e:
            print(f"⚠️ 뉴스 데이터 수집 실패: {e}")
            return {'count': 0, 'avg_sentiment': 0, 'categories': {}}
    
    def get_financial_data(self) -> Dict:
        """
        기업 재무 정보 수집
        - 매출/영업이익/순이익
        - PER, PBR, ROE
        - 부채비율
        """
        try:
            stock = yf.Ticker(self.ticker)
            info = stock.info
            
            # 주요 재무 지표
            financial = {
                # 수익성
                'revenue': info.get('totalRevenue', 0),
                'operating_income': info.get('operatingIncome', 0),
                'net_income': info.get('netIncome', 0),
                'profit_margin': info.get('profitMargins', 0) * 100,
                
                # 밸류에이션
                'pe_ratio': info.get('trailingPE', 0),
                'forward_pe': info.get('forwardPE', 0),
                'pb_ratio': info.get('priceToBook', 0),
                'ps_ratio': info.get('priceToSalesTrailing12Months', 0),
                
                # 수익성 지표
                'roe': info.get('returnOnEquity', 0) * 100,
                'roa': info.get('returnOnAssets', 0) * 100,
                
                # 재무 건전성
                'debt_to_equity': info.get('debtToEquity', 0),
                'current_ratio': info.get('currentRatio', 0),
                
                # 성장성
                'revenue_growth': info.get('revenueGrowth', 0) * 100,
                'earnings_growth': info.get('earningsGrowth', 0) * 100,
                
                # 배당
                'dividend_yield': info.get('dividendYield', 0) * 100 if info.get('dividendYield') else 0,
                'payout_ratio': info.get('payoutRatio', 0) * 100 if info.get('payoutRatio') else 0,
            }
            
            # 재무 건전성 등급
            financial['health_grade'] = self._calculate_financial_health(financial)
            
            return financial
            
        except Exception as e:
            print(f"⚠️ 재무 데이터 수집 실패: {e}")
            return {}
    
    def get_economic_indicators(self) -> Dict:
        """
        경제 지표 수집
        - 금리 (미국 10년물)
        - 환율 (USD/KRW)
        - 원유 가격 (WTI)
        - VIX (변동성 지수)
        """
        try:
            indicators = {}
            
            # 미국 10년물 금리
            try:
                tnx = yf.Ticker("^TNX")
                tnx_data = tnx.history(period="1mo")
                indicators['us_10y_yield'] = float(tnx_data['Close'].iloc[-1]) if not tnx_data.empty else 0
            except:
                indicators['us_10y_yield'] = 0
            
            # VIX (변동성 지수)
            try:
                vix = yf.Ticker("^VIX")
                vix_data = vix.history(period="1mo")
                indicators['vix'] = float(vix_data['Close'].iloc[-1]) if not vix_data.empty else 0
            except:
                indicators['vix'] = 0
            
            # 원유 (WTI)
            try:
                oil = yf.Ticker("CL=F")
                oil_data = oil.history(period="1mo")
                indicators['oil_price'] = float(oil_data['Close'].iloc[-1]) if not oil_data.empty else 0
            except:
                indicators['oil_price'] = 0
            
            # 환율 (USD/KRW) - 한국 종목인 경우
            if self.is_korean:
                try:
                    krw = yf.Ticker("KRW=X")
                    krw_data = krw.history(period="1mo")
                    indicators['usd_krw'] = float(krw_data['Close'].iloc[-1]) if not krw_data.empty else 0
                except:
                    indicators['usd_krw'] = 0
            
            # 시장 지수 (S&P 500 또는 KOSPI)
            try:
                if self.is_korean:
                    market = yf.Ticker("^KS11")  # KOSPI
                else:
                    market = yf.Ticker("^GSPC")  # S&P 500
                
                market_data = market.history(period="1mo")
                if not market_data.empty:
                    indicators['market_index'] = float(market_data['Close'].iloc[-1])
                    indicators['market_change'] = float(
                        (market_data['Close'].iloc[-1] - market_data['Close'].iloc[0]) / 
                        market_data['Close'].iloc[0] * 100
                    )
                else:
                    indicators['market_index'] = 0
                    indicators['market_change'] = 0
            except:
                indicators['market_index'] = 0
                indicators['market_change'] = 0
            
            # 경제 상황 등급
            indicators['economic_grade'] = self._calculate_economic_grade(indicators)
            
            return indicators
            
        except Exception as e:
            print(f"⚠️ 경제 지표 수집 실패: {e}")
            return {}
    
    def get_sentiment_data(self) -> Dict:
        """
        투자자 심리 지표
        - 공포/탐욕 지수
        - 매수/매도 비율
        """
        try:
            stock = yf.Ticker(self.ticker)
            
            # 추천 의견
            recommendations = stock.recommendations if hasattr(stock, 'recommendations') else None
            
            if recommendations is not None and not recommendations.empty:
                recent = recommendations.tail(10)
                
                buy_signals = recent[recent['To Grade'].str.contains('Buy|Outperform', case=False, na=False)]
                sell_signals = recent[recent['To Grade'].str.contains('Sell|Underperform', case=False, na=False)]
                
                sentiment_score = (len(buy_signals) - len(sell_signals)) / max(len(recent), 1) * 100
            else:
                sentiment_score = 0
            
            return {
                'sentiment_score': float(sentiment_score),
                'recommendation_count': len(recommendations) if recommendations is not None else 0,
                'sentiment_grade': self._grade_sentiment(sentiment_score)
            }
            
        except Exception as e:
            print(f"⚠️ 심리 지표 수집 실패: {e}")
            return {'sentiment_score': 0, 'sentiment_grade': '중립'}
    
    def get_insider_trading(self) -> Dict:
        """
        내부자 거래 정보
        - 임원/대주주 매매
        """
        try:
            stock = yf.Ticker(self.ticker)
            
            # 내부자 거래 (yfinance는 제한적)
            insider = stock.insider_transactions if hasattr(stock, 'insider_transactions') else None
            
            if insider is not None and not insider.empty:
                recent = insider.head(20)
                
                # 매수/매도 구분
                buys = recent[recent['Transaction'].str.contains('Buy', case=False, na=False)]
                sells = recent[recent['Transaction'].str.contains('Sell', case=False, na=False)]
                
                return {
                    'total_transactions': len(recent),
                    'buy_count': len(buys),
                    'sell_count': len(sells),
                    'insider_signal': 'Bullish' if len(buys) > len(sells) else 'Bearish' if len(sells) > len(buys) else 'Neutral'
                }
            
            return {'insider_signal': 'No Data'}
            
        except Exception as e:
            print(f"⚠️ 내부자 거래 정보 수집 실패: {e}")
            return {'insider_signal': 'No Data'}
    
    def get_analyst_ratings(self) -> Dict:
        """
        애널리스트 평가
        - 목표가
        - 의견 (Buy/Hold/Sell)
        """
        try:
            stock = yf.Ticker(self.ticker)
            info = stock.info
            
            return {
                'target_price': info.get('targetMeanPrice', 0),
                'current_price': info.get('currentPrice', 0),
                'upside_potential': (
                    (info.get('targetMeanPrice', 0) - info.get('currentPrice', 1)) / 
                    info.get('currentPrice', 1) * 100
                ) if info.get('targetMeanPrice') and info.get('currentPrice') else 0,
                'recommendation': info.get('recommendationKey', 'none'),
                'analyst_count': info.get('numberOfAnalystOpinions', 0)
            }
            
        except Exception as e:
            print(f"⚠️ 애널리스트 평가 수집 실패: {e}")
            return {}
    
    def get_social_sentiment(self) -> Dict:
        """
        소셜 미디어 감정 분석
        (제한적 - API 키 필요한 경우 시뮬레이션)
        """
        try:
            # 실제로는 Twitter API, Reddit API 등 사용
            # 여기서는 시뮬레이션
            
            return {
                'twitter_mentions': np.random.randint(100, 1000),
                'reddit_mentions': np.random.randint(50, 500),
                'youtube_mentions': np.random.randint(10, 100),
                'social_sentiment': np.random.uniform(-0.5, 0.5),  # -1 ~ 1
                'trending': np.random.choice([True, False], p=[0.2, 0.8])
            }
            
        except Exception as e:
            print(f"⚠️ 소셜 미디어 데이터 수집 실패: {e}")
            return {}
    
    # ===========================
    # 보조 함수들
    # ===========================
    
    def _analyze_sentiment(self, text: str) -> float:
        """간단한 감정 분석 (키워드 기반)"""
        positive_words = ['상승', '증가', '성장', '호조', '개선', '긍정', '매수', 'up', 'rise', 'growth', 'bullish', 'buy']
        negative_words = ['하락', '감소', '부진', '악화', '부정', '매도', 'down', 'fall', 'decline', 'bearish', 'sell']
        
        text_lower = text.lower()
        
        pos_count = sum(1 for word in positive_words if word in text_lower)
        neg_count = sum(1 for word in negative_words if word in text_lower)
        
        if pos_count + neg_count == 0:
            return 0
        
        return (pos_count - neg_count) / (pos_count + neg_count)
    
    def _categorize_news(self, title: str) -> str:
        """뉴스 카테고리 분류"""
        title_lower = title.lower()
        
        if any(word in title_lower for word in ['정책', '정부', '규제', 'policy', 'regulation']):
            return '정책'
        elif any(word in title_lower for word in ['기술', '혁신', 'tech', 'innovation', 'ai']):
            return '기술'
        elif any(word in title_lower for word in ['실적', '매출', '이익', 'earnings', 'revenue']):
            return '실적'
        elif any(word in title_lower for word in ['인수', '합병', 'merger', 'acquisition']):
            return 'M&A'
        else:
            return '일반'
    
    def _calculate_financial_health(self, financial: Dict) -> str:
        """재무 건전성 등급 계산"""
        score = 0
        
        # ROE
        if financial.get('roe', 0) > 15:
            score += 2
        elif financial.get('roe', 0) > 10:
            score += 1
        
        # 부채비율
        if financial.get('debt_to_equity', 0) < 100:
            score += 2
        elif financial.get('debt_to_equity', 0) < 200:
            score += 1
        
        # 이익률
        if financial.get('profit_margin', 0) > 10:
            score += 2
        elif financial.get('profit_margin', 0) > 5:
            score += 1
        
        if score >= 5:
            return '우수'
        elif score >= 3:
            return '양호'
        else:
            return '보통'
    
    def _calculate_economic_grade(self, indicators: Dict) -> str:
        """경제 상황 등급"""
        score = 0
        
        # VIX (낮을수록 좋음)
        vix = indicators.get('vix', 20)
        if vix < 15:
            score += 2
        elif vix < 25:
            score += 1
        
        # 금리 (너무 높지 않을수록 좋음)
        yield_rate = indicators.get('us_10y_yield', 4)
        if yield_rate < 3:
            score += 2
        elif yield_rate < 4.5:
            score += 1
        
        # 시장 변화
        market_change = indicators.get('market_change', 0)
        if market_change > 2:
            score += 2
        elif market_change > 0:
            score += 1
        
        if score >= 5:
            return '호황'
        elif score >= 3:
            return '안정'
        else:
            return '불안'
    
    def _grade_sentiment(self, score: float) -> str:
        """심리 지표 등급"""
        if score > 50:
            return '매우 낙관'
        elif score > 20:
            return '낙관'
        elif score > -20:
            return '중립'
        elif score > -50:
            return '비관'
        else:
            return '매우 비관'


def integrate_external_data(df: pd.DataFrame, external_data: Dict) -> pd.DataFrame:
    """
    외부 데이터를 주가 데이터프레임에 통합
    
    Args:
        df: 주가 데이터프레임
        external_data: 외부 데이터 딕셔너리
    
    Returns:
        통합된 데이터프레임
    """
    df_enhanced = df.copy()
    
    # 뉴스 감정
    news = external_data.get('news', {})
    df_enhanced['news_sentiment'] = news.get('avg_sentiment', 0)
    df_enhanced['news_positive_ratio'] = news.get('positive_ratio', 0.5)
    
    # 재무 건전성 점수
    financial = external_data.get('financial', {})
    df_enhanced['roe'] = financial.get('roe', 0)
    df_enhanced['debt_ratio'] = financial.get('debt_to_equity', 0)
    df_enhanced['profit_margin'] = financial.get('profit_margin', 0)
    
    # 경제 지표
    economic = external_data.get('economic', {})
    df_enhanced['vix'] = economic.get('vix', 20)
    df_enhanced['market_change'] = economic.get('market_change', 0)
    
    # 투자자 심리
    sentiment = external_data.get('sentiment', {})
    df_enhanced['investor_sentiment'] = sentiment.get('sentiment_score', 0)
    
    # 애널리스트 평가
    analyst = external_data.get('analyst', {})
    df_enhanced['upside_potential'] = analyst.get('upside_potential', 0)
    
    return df_enhanced
