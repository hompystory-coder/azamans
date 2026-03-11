"""
분석 데이터 캐싱 시스템
- 분석 결과 저장
- 빠른 재검색
- 데이터베이스 역할
"""

import json
import os
from datetime import datetime
from typing import Dict, Optional

class AnalysisCache:
    """분석 데이터 캐시 관리"""
    
    def __init__(self, cache_dir: str = 'cache/analysis_data'):
        self.cache_dir = cache_dir
        os.makedirs(cache_dir, exist_ok=True)
        
    def _get_cache_path(self, ticker: str) -> str:
        """캐시 파일 경로 생성"""
        safe_ticker = ticker.replace('.', '_')
        return os.path.join(self.cache_dir, f"{safe_ticker}.json")
    
    def save_analysis(self, ticker: str, data: Dict) -> bool:
        """
        분석 데이터 저장
        
        Args:
            ticker: 종목 코드
            data: 분석 결과 딕셔너리
        """
        try:
            cache_path = self._get_cache_path(ticker)
            
            # 저장 데이터 구조
            cache_data = {
                'ticker': ticker,
                'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                'analysis': data
            }
            
            # JSON으로 저장
            with open(cache_path, 'w', encoding='utf-8') as f:
                json.dump(cache_data, f, ensure_ascii=False, indent=2, default=str)
            
            return True
        except Exception as e:
            print(f"❌ 저장 실패: {str(e)}")
            return False
    
    def load_analysis(self, ticker: str, max_age_hours: int = 24) -> Optional[Dict]:
        """
        분석 데이터 로드 (캐시 유효 시간 체크)
        
        Args:
            ticker: 종목 코드
            max_age_hours: 캐시 최대 유효 시간 (시간)
            
        Returns:
            분석 데이터 or None (유효하지 않으면)
        """
        try:
            cache_path = self._get_cache_path(ticker)
            
            if not os.path.exists(cache_path):
                return None
            
            # 파일 읽기
            with open(cache_path, 'r', encoding='utf-8') as f:
                cache_data = json.load(f)
            
            # 타임스탬프 확인
            timestamp_str = cache_data.get('timestamp')
            if timestamp_str:
                timestamp = datetime.strptime(timestamp_str, '%Y-%m-%d %H:%M:%S')
                age = datetime.now() - timestamp
                
                # 유효 시간 체크
                if age.total_seconds() / 3600 > max_age_hours:
                    return None  # 너무 오래된 캐시
            
            return cache_data.get('analysis')
        
        except Exception as e:
            print(f"⚠️ 로드 실패: {str(e)}")
            return None
    
    def get_all_cached_tickers(self) -> list:
        """캐시된 모든 티커 목록 반환"""
        try:
            files = os.listdir(self.cache_dir)
            tickers = []
            
            for file in files:
                if file.endswith('.json'):
                    ticker = file.replace('.json', '').replace('_', '.')
                    tickers.append(ticker)
            
            return sorted(tickers)
        except:
            return []
    
    def get_cache_info(self, ticker: str) -> Optional[Dict]:
        """캐시 정보만 반환 (분석 데이터 제외)"""
        try:
            cache_path = self._get_cache_path(ticker)
            
            if not os.path.exists(cache_path):
                return None
            
            with open(cache_path, 'r', encoding='utf-8') as f:
                cache_data = json.load(f)
            
            return {
                'ticker': cache_data.get('ticker'),
                'timestamp': cache_data.get('timestamp'),
                'age_hours': self._get_cache_age(cache_path)
            }
        except:
            return None
    
    def _get_cache_age(self, cache_path: str) -> float:
        """캐시 파일의 나이 (시간)"""
        try:
            mtime = os.path.getmtime(cache_path)
            age_seconds = datetime.now().timestamp() - mtime
            return age_seconds / 3600  # 시간 단위
        except:
            return 999999  # 매우 오래됨
    
    def clear_cache(self, ticker: Optional[str] = None):
        """캐시 삭제"""
        try:
            if ticker:
                # 특정 티커만 삭제
                cache_path = self._get_cache_path(ticker)
                if os.path.exists(cache_path):
                    os.remove(cache_path)
                    return True
            else:
                # 모든 캐시 삭제
                files = os.listdir(self.cache_dir)
                for file in files:
                    if file.endswith('.json'):
                        os.remove(os.path.join(self.cache_dir, file))
                return True
        except Exception as e:
            print(f"❌ 삭제 실패: {str(e)}")
            return False
    
    def get_cache_stats(self) -> Dict:
        """캐시 통계"""
        try:
            files = os.listdir(self.cache_dir)
            json_files = [f for f in files if f.endswith('.json')]
            
            total_size = sum(
                os.path.getsize(os.path.join(self.cache_dir, f))
                for f in json_files
            )
            
            return {
                'total_tickers': len(json_files),
                'total_size_mb': round(total_size / 1024 / 1024, 2),
                'cache_dir': self.cache_dir
            }
        except:
            return {
                'total_tickers': 0,
                'total_size_mb': 0,
                'cache_dir': self.cache_dir
            }
