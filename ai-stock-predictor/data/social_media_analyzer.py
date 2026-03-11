"""
소셜 미디어 분석 모듈 (v6.0)
YouTube, Twitter/X, Reddit 등의 소셜 미디어에서 종목 관련 정보 수집 및 분석
"""

import re
from datetime import datetime, timedelta
from typing import Dict, List, Tuple
import random


class SocialMediaAnalyzer:
    """소셜 미디어 분석 클래스"""
    
    def __init__(self, ticker: str, company_name: str = ""):
        self.ticker = ticker
        self.company_name = company_name or ticker
        
    def analyze_all_sources(self) -> Dict:
        """모든 소셜 미디어 소스 분석"""
        youtube_data = self._analyze_youtube()
        twitter_data = self._analyze_twitter()
        reddit_data = self._analyze_reddit()
        
        # 종합 점수 계산
        overall_sentiment = self._calculate_overall_sentiment(
            youtube_data, twitter_data, reddit_data
        )
        
        return {
            'youtube': youtube_data,
            'twitter': twitter_data,
            'reddit': reddit_data,
            'overall_sentiment': overall_sentiment,
            'total_mentions': (
                youtube_data['video_count'] + 
                twitter_data['tweet_count'] + 
                reddit_data['post_count']
            ),
            'engagement_score': self._calculate_engagement_score(
                youtube_data, twitter_data, reddit_data
            )
        }
    
    def _analyze_youtube(self) -> Dict:
        """YouTube 비디오 분석
        
        실제 구현 시:
        - YouTube Data API v3 사용
        - 검색 쿼리: {company_name} stock, {ticker} analysis 등
        - 최근 30일 비디오 수집
        - 조회수, 좋아요, 댓글 분석
        """
        # 시뮬레이션 데이터
        video_count = random.randint(15, 50)
        avg_views = random.randint(5000, 100000)
        
        # 감성 분석 시뮬레이션
        positive_ratio = random.uniform(0.3, 0.7)
        negative_ratio = 1 - positive_ratio
        
        sentiment_score = (positive_ratio - negative_ratio) * 100
        
        # 인기 비디오 시뮬레이션
        popular_videos = []
        video_titles = [
            f"{self.company_name} Stock Analysis - Buy or Sell?",
            f"Why {self.ticker} is Going Up! Technical Analysis",
            f"{self.company_name} Earnings Preview - What to Expect",
            f"Is {self.ticker} a Good Investment in 2024?",
            f"{self.company_name} Latest News and Stock Updates"
        ]
        
        for i, title in enumerate(video_titles[:3]):
            popular_videos.append({
                'title': title,
                'views': random.randint(10000, 500000),
                'likes': random.randint(500, 10000),
                'comments': random.randint(100, 2000),
                'sentiment': random.choice(['긍정적', '중립', '부정적']),
                'channel': f"Stock Analysis Channel {i+1}",
                'published_date': (datetime.now() - timedelta(days=random.randint(1, 30))).strftime('%Y-%m-%d')
            })
        
        return {
            'video_count': video_count,
            'avg_views': avg_views,
            'total_views': avg_views * video_count,
            'sentiment_score': sentiment_score,
            'positive_ratio': positive_ratio,
            'popular_videos': popular_videos,
            'trending': sentiment_score > 20  # 긍정적 감성이 강할 때
        }
    
    def _analyze_twitter(self) -> Dict:
        """Twitter/X 트윗 분석
        
        실제 구현 시:
        - Twitter API v2 사용
        - $TICKER 해시태그 검색
        - 리트윗, 좋아요, 답글 수 집계
        - 인플루언서 트윗 가중치 적용
        """
        # 시뮬레이션 데이터
        tweet_count = random.randint(500, 5000)
        total_engagement = random.randint(50000, 500000)
        
        # 감성 분석
        positive_ratio = random.uniform(0.35, 0.65)
        negative_ratio = 1 - positive_ratio
        sentiment_score = (positive_ratio - negative_ratio) * 100
        
        # 인기 트윗 시뮬레이션
        trending_topics = []
        topics = [
            f"#{self.ticker} Breaking News",
            f"{self.company_name} Earnings Beat",
            f"${self.ticker} Technical Breakout",
            "Bullish on " + self.ticker,
            f"{self.company_name} New Product Launch"
        ]
        
        for topic in topics[:3]:
            trending_topics.append({
                'topic': topic,
                'mentions': random.randint(100, 2000),
                'sentiment': random.choice(['긍정적', '중립', '부정적'])
            })
        
        # 인플루언서 언급
        influencer_mentions = random.randint(5, 20)
        
        return {
            'tweet_count': tweet_count,
            'total_engagement': total_engagement,
            'avg_engagement': total_engagement / tweet_count if tweet_count > 0 else 0,
            'sentiment_score': sentiment_score,
            'positive_ratio': positive_ratio,
            'trending_topics': trending_topics,
            'influencer_mentions': influencer_mentions,
            'viral': total_engagement > 200000  # 바이럴 여부
        }
    
    def _analyze_reddit(self) -> Dict:
        """Reddit 게시글 분석
        
        실제 구현 시:
        - Reddit API (PRAW) 사용
        - r/stocks, r/investing, r/wallstreetbets 검색
        - 업보트, 댓글 수 집계
        - 감성 분석
        """
        # 시뮬레이션 데이터
        post_count = random.randint(50, 300)
        total_upvotes = random.randint(5000, 50000)
        total_comments = random.randint(1000, 10000)
        
        # 감성 분석
        positive_ratio = random.uniform(0.4, 0.7)
        negative_ratio = 1 - positive_ratio
        sentiment_score = (positive_ratio - negative_ratio) * 100
        
        # 인기 게시글
        popular_posts = []
        post_titles = [
            f"DD: Why {self.ticker} is undervalued",
            f"{self.company_name} earnings discussion thread",
            f"Is anyone else bullish on {self.ticker}?",
            f"{self.ticker} technical analysis and price targets"
        ]
        
        for title in post_titles[:2]:
            popular_posts.append({
                'title': title,
                'upvotes': random.randint(500, 5000),
                'comments': random.randint(50, 500),
                'sentiment': random.choice(['긍정적', '중립', '부정적']),
                'subreddit': random.choice(['r/stocks', 'r/investing', 'r/wallstreetbets'])
            })
        
        return {
            'post_count': post_count,
            'total_upvotes': total_upvotes,
            'total_comments': total_comments,
            'avg_upvotes': total_upvotes / post_count if post_count > 0 else 0,
            'sentiment_score': sentiment_score,
            'positive_ratio': positive_ratio,
            'popular_posts': popular_posts,
            'trending': total_upvotes > 20000
        }
    
    def _calculate_overall_sentiment(self, youtube: Dict, twitter: Dict, reddit: Dict) -> Dict:
        """전체 감성 점수 계산"""
        # 각 플랫폼의 가중치
        weights = {
            'youtube': 0.3,  # YouTube - 전문가 분석 비중 높음
            'twitter': 0.4,  # Twitter - 실시간 여론 반영
            'reddit': 0.3    # Reddit - 커뮤니티 토론
        }
        
        # 가중 평균
        overall_score = (
            youtube['sentiment_score'] * weights['youtube'] +
            twitter['sentiment_score'] * weights['twitter'] +
            reddit['sentiment_score'] * weights['reddit']
        )
        
        # 등급 결정
        if overall_score > 30:
            grade = '매우 긍정적'
            color = '#10b981'  # 초록
        elif overall_score > 10:
            grade = '긍정적'
            color = '#3b82f6'  # 파랑
        elif overall_score > -10:
            grade = '중립'
            color = '#6b7280'  # 회색
        elif overall_score > -30:
            grade = '부정적'
            color = '#f59e0b'  # 주황
        else:
            grade = '매우 부정적'
            color = '#ef4444'  # 빨강
        
        return {
            'score': overall_score,
            'grade': grade,
            'color': color,
            'confidence': min(95, max(60, 70 + abs(overall_score) / 2))
        }
    
    def _calculate_engagement_score(self, youtube: Dict, twitter: Dict, reddit: Dict) -> int:
        """참여도 점수 계산 (0-100)"""
        # 정규화
        youtube_norm = min(100, youtube['total_views'] / 10000)
        twitter_norm = min(100, twitter['total_engagement'] / 5000)
        reddit_norm = min(100, reddit['total_upvotes'] / 500)
        
        # 평균 점수
        engagement = (youtube_norm + twitter_norm + reddit_norm) / 3
        
        return int(engagement)
    
    def get_key_insights(self) -> List[str]:
        """주요 인사이트 추출"""
        data = self.analyze_all_sources()
        insights = []
        
        # YouTube 트렌드
        if data['youtube']['trending']:
            insights.append(f"🎥 YouTube에서 {self.ticker}가 트렌딩 중! (영상 {data['youtube']['video_count']}개)")
        
        # Twitter 바이럴
        if data['twitter']['viral']:
            insights.append(f"🐦 Twitter에서 화제! (참여 {data['twitter']['total_engagement']:,}회)")
        
        # Reddit 인기
        if data['reddit']['trending']:
            insights.append(f"📱 Reddit에서 활발한 토론 중 (업보트 {data['reddit']['total_upvotes']:,}개)")
        
        # 전체 감성
        sentiment = data['overall_sentiment']
        insights.append(f"💭 소셜 미디어 전체 분위기: {sentiment['grade']}")
        
        # 참여도
        if data['engagement_score'] > 70:
            insights.append(f"🔥 높은 관심도! (참여 점수 {data['engagement_score']}/100)")
        
        return insights


# 테스트 코드
if __name__ == "__main__":
    analyzer = SocialMediaAnalyzer("AAPL", "Apple")
    results = analyzer.analyze_all_sources()
    
    print("=== 소셜 미디어 분석 결과 ===")
    print(f"YouTube: {results['youtube']['video_count']}개 영상")
    print(f"Twitter: {results['twitter']['tweet_count']}개 트윗")
    print(f"Reddit: {results['reddit']['post_count']}개 게시글")
    print(f"전체 감성: {results['overall_sentiment']['grade']}")
    
    print("\n주요 인사이트:")
    for insight in analyzer.get_key_insights():
        print(f"  {insight}")
