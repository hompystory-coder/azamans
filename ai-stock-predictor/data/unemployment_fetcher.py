"""
실업률 데이터 수집 모듈
- 한국 실업률: 통계청(KOSIS) 크롤링
- 미국 실업률: FRED API 사용
"""

import requests
from bs4 import BeautifulSoup
import pandas as pd
from datetime import datetime, timedelta
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class UnemploymentDataFetcher:
    """실업률 데이터 수집 클래스"""
    
    def __init__(self):
        self.korea_url = "https://kosis.kr/statHtml/statHtml.do?orgId=101&tblId=DT_1DA7012S"
        self.us_fred_url = "https://fred.stlouisfed.org/series/UNRATE"
        
    def get_korea_unemployment(self):
        """
        한국 실업률 데이터 수집 (통계청)
        
        Returns:
            dict: {
                'rate': 3.2,  # 최신 실업률 (%)
                'date': '2025-11',  # 기준 월
                'trend': 'down',  # up/down/stable
                'previous': 3.4,  # 전월 실업률
                'change': -0.2  # 전월 대비 변화
            }
        """
        try:
            logger.info("🇰🇷 한국 실업률 데이터 수집 중...")
            
            # 통계청 KOSIS 페이지 크롤링
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
            
            response = requests.get(self.korea_url, headers=headers, timeout=10)
            
            if response.status_code == 200:
                soup = BeautifulSoup(response.text, 'html.parser')
                
                # 테이블에서 최신 데이터 파싱
                # (실제 구조에 맞게 셀렉터 조정 필요)
                table = soup.find('table', class_='tbl')
                
                if table:
                    rows = table.find_all('tr')
                    
                    # 최근 2개월 데이터 추출
                    latest_data = []
                    for row in rows[1:3]:  # 헤더 제외, 최근 2행
                        cols = row.find_all('td')
                        if len(cols) >= 2:
                            date = cols[0].get_text(strip=True)
                            rate = float(cols[1].get_text(strip=True))
                            latest_data.append({'date': date, 'rate': rate})
                    
                    if len(latest_data) >= 2:
                        current = latest_data[0]
                        previous = latest_data[1]
                        
                        change = current['rate'] - previous['rate']
                        
                        if change < -0.1:
                            trend = 'down'
                        elif change > 0.1:
                            trend = 'up'
                        else:
                            trend = 'stable'
                        
                        result = {
                            'rate': current['rate'],
                            'date': current['date'],
                            'trend': trend,
                            'previous': previous['rate'],
                            'change': round(change, 2),
                            'source': '통계청 KOSIS',
                            'country': 'KR'
                        }
                        
                        logger.info(f"✅ 한국 실업률: {result['rate']}% ({result['date']})")
                        return result
            
            # 크롤링 실패 시 더미 데이터 (최근 평균값)
            logger.warning("⚠️ 실시간 수집 실패, 최근 평균값 사용")
            return self._get_korea_fallback()
            
        except Exception as e:
            logger.error(f"❌ 한국 실업률 수집 오류: {e}")
            return self._get_korea_fallback()
    
    def get_us_unemployment(self):
        """
        미국 실업률 데이터 수집 (FRED)
        
        Returns:
            dict: {
                'rate': 3.7,
                'date': '2025-11',
                'trend': 'stable',
                'previous': 3.7,
                'change': 0.0
            }
        """
        try:
            logger.info("🇺🇸 미국 실업률 데이터 수집 중...")
            
            # FRED API 엔드포인트 (공개 데이터)
            # API 키가 필요하면 환경변수에서 가져오기
            # api_key = os.getenv('FRED_API_KEY', '')
            
            # 웹 크롤링 방식 (FRED 웹페이지)
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
            
            response = requests.get(self.us_fred_url, headers=headers, timeout=10)
            
            if response.status_code == 200:
                soup = BeautifulSoup(response.text, 'html.parser')
                
                # FRED 페이지에서 최신 값 파싱
                value_div = soup.find('span', class_='series-meta-observation-value')
                
                if value_div:
                    rate = float(value_div.get_text(strip=True))
                    
                    # 날짜 파싱
                    date_div = soup.find('span', class_='series-meta-observation-date')
                    date_str = date_div.get_text(strip=True) if date_div else 'N/A'
                    
                    result = {
                        'rate': rate,
                        'date': date_str,
                        'trend': 'stable',  # 추세 계산은 이전 데이터 필요
                        'previous': rate,  # 단순화
                        'change': 0.0,
                        'source': 'FRED (Federal Reserve)',
                        'country': 'US'
                    }
                    
                    logger.info(f"✅ 미국 실업률: {result['rate']}% ({result['date']})")
                    return result
            
            # 실패 시 폴백
            logger.warning("⚠️ 실시간 수집 실패, 최근 평균값 사용")
            return self._get_us_fallback()
            
        except Exception as e:
            logger.error(f"❌ 미국 실업률 수집 오류: {e}")
            return self._get_us_fallback()
    
    def get_unemployment_data(self, country='KR'):
        """
        국가별 실업률 데이터 조회
        
        Args:
            country (str): 'KR' 또는 'US'
            
        Returns:
            dict: 실업률 정보
        """
        if country == 'KR':
            return self.get_korea_unemployment()
        elif country == 'US':
            return self.get_us_unemployment()
        else:
            logger.error(f"❌ 지원하지 않는 국가: {country}")
            return None
    
    def _get_korea_fallback(self):
        """한국 실업률 폴백 데이터 (2024-2025 평균)"""
        return {
            'rate': 3.0,
            'date': datetime.now().strftime('%Y-%m'),
            'trend': 'stable',
            'previous': 3.0,
            'change': 0.0,
            'source': '통계청 KOSIS (추정)',
            'country': 'KR'
        }
    
    def _get_us_fallback(self):
        """미국 실업률 폴백 데이터 (2024-2025 평균)"""
        return {
            'rate': 3.7,
            'date': datetime.now().strftime('%Y-%m'),
            'trend': 'stable',
            'previous': 3.7,
            'change': 0.0,
            'source': 'FRED (추정)',
            'country': 'US'
        }


# 테스트 코드
if __name__ == "__main__":
    fetcher = UnemploymentDataFetcher()
    
    print("\n" + "="*60)
    print("📊 실업률 데이터 테스트")
    print("="*60 + "\n")
    
    # 한국 실업률
    kr_data = fetcher.get_korea_unemployment()
    print(f"🇰🇷 한국 실업률: {kr_data['rate']}% ({kr_data['date']})")
    print(f"   전월 대비: {kr_data['change']:+.2f}%p ({kr_data['trend']})")
    print(f"   출처: {kr_data['source']}\n")
    
    # 미국 실업률
    us_data = fetcher.get_us_unemployment()
    print(f"🇺🇸 미국 실업률: {us_data['rate']}% ({us_data['date']})")
    print(f"   전월 대비: {us_data['change']:+.2f}%p ({us_data['trend']})")
    print(f"   출처: {us_data['source']}\n")
    
    print("="*60)
