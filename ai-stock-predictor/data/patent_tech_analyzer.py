"""
특허 및 기술력 분석 모듈 (v6.0)
특허 포트폴리오, R&D 투자, 기술 혁신 지표 분석
"""

import random
from datetime import datetime, timedelta
from typing import Dict, List


class PatentTechAnalyzer:
    """특허 및 기술력 분석 클래스"""
    
    def __init__(self, ticker: str, company_name: str = ""):
        self.ticker = ticker
        self.company_name = company_name or ticker
        
    def analyze_innovation(self) -> Dict:
        """혁신 능력 종합 분석"""
        patents = self._analyze_patents()
        rd_investment = self._analyze_rd_investment()
        tech_leadership = self._analyze_tech_leadership()
        citations = self._analyze_patent_citations()
        
        # 혁신 점수
        innovation_score = self._calculate_innovation_score(
            patents, rd_investment, tech_leadership, citations
        )
        
        return {
            'patents': patents,
            'rd_investment': rd_investment,
            'tech_leadership': tech_leadership,
            'citations': citations,
            'innovation_score': innovation_score
        }
    
    def _analyze_patents(self) -> Dict:
        """특허 포트폴리오 분석
        
        실제 구현 시:
        - USPTO, WIPO, 한국 특허청 API
        - 특허 출원/등록 현황
        - 기술 분야별 분류
        """
        # 최근 5년간 특허 활동
        yearly_patents = []
        for year in range(2020, 2025):
            filed = random.randint(50, 300)
            granted = random.randint(30, 200)
            
            yearly_patents.append({
                'year': year,
                'filed': filed,
                'granted': granted,
                'grant_rate': granted / filed if filed > 0 else 0
            })
        
        # 기술 분야별 분포
        tech_areas = {
            'AI/ML': random.randint(50, 150),
            '반도체': random.randint(30, 100),
            '통신': random.randint(40, 120),
            '소프트웨어': random.randint(60, 180),
            '하드웨어': random.randint(30, 90)
        }
        
        # 주요 특허 (가치 높은 특허)
        key_patents = []
        for i in range(3):
            key_patents.append({
                'title': f"혁신 기술 특허 #{i+1}",
                'filed_date': (datetime.now() - timedelta(days=random.randint(365, 1825))).strftime('%Y-%m-%d'),
                'citations': random.randint(10, 100),
                'technology_field': random.choice(list(tech_areas.keys())),
                'strategic_value': random.choice(['매우 높음', '높음'])
            })
        
        total_patents = sum(tech_areas.values())
        recent_growth = (yearly_patents[-1]['filed'] - yearly_patents[-2]['filed']) / yearly_patents[-2]['filed'] * 100
        
        return {
            'yearly_data': yearly_patents,
            'tech_areas': tech_areas,
            'key_patents': key_patents,
            'total_portfolio': total_patents,
            'recent_growth_rate': recent_growth,
            'active_patents': random.randint(500, 2000)
        }
    
    def _analyze_rd_investment(self) -> Dict:
        """R&D 투자 분석"""
        # 최근 5년 R&D 투자
        yearly_rd = []
        for year in range(2020, 2025):
            amount = random.randint(500, 3000)  # 백만달러
            revenue = random.randint(5000, 30000)
            rd_ratio = (amount / revenue) * 100
            
            yearly_rd.append({
                'year': year,
                'amount': amount,
                'revenue': revenue,
                'rd_ratio': rd_ratio
            })
        
        # R&D 인력
        rd_employees = random.randint(1000, 10000)
        total_employees = random.randint(10000, 100000)
        rd_personnel_ratio = (rd_employees / total_employees) * 100
        
        # R&D 센터
        rd_centers = []
        locations = ['실리콘밸리', '서울', '텔아비브', '베를린', '도쿄']
        for location in random.sample(locations, k=random.randint(2, 4)):
            rd_centers.append({
                'location': location,
                'focus_area': random.choice(['AI', '반도체', '소프트웨어', '하드웨어']),
                'size': random.randint(50, 500)
            })
        
        avg_rd_ratio = sum(y['rd_ratio'] for y in yearly_rd) / len(yearly_rd)
        
        return {
            'yearly_investment': yearly_rd,
            'latest_amount': yearly_rd[-1]['amount'],
            'avg_rd_ratio': avg_rd_ratio,
            'rd_employees': rd_employees,
            'rd_personnel_ratio': rd_personnel_ratio,
            'rd_centers': rd_centers,
            'investment_trend': '증가' if yearly_rd[-1]['amount'] > yearly_rd[-2]['amount'] else '감소'
        }
    
    def _analyze_tech_leadership(self) -> Dict:
        """기술 리더십 분석"""
        # 업계 순위
        industry_rank = random.randint(1, 20)
        
        # 기술 우위 분야
        leadership_areas = []
        tech_fields = ['인공지능', '클라우드', '반도체', '5G/6G', '양자컴퓨팅']
        for field in random.sample(tech_fields, k=random.randint(2, 4)):
            leadership_areas.append({
                'field': field,
                'position': random.choice(['선도', '추격', '경쟁']),
                'market_share': random.uniform(0.05, 0.35)
            })
        
        # 기술 혁신 수상
        awards = []
        award_types = ['CES 혁신상', 'R&D 100 Awards', '특허청 표창', '기술혁신대상']
        for _ in range(random.randint(2, 5)):
            awards.append({
                'award': random.choice(award_types),
                'year': random.randint(2020, 2024),
                'category': random.choice(leadership_areas)['field'] if leadership_areas else '기술 혁신'
            })
        
        return {
            'industry_rank': industry_rank,
            'leadership_areas': leadership_areas,
            'awards': awards,
            'leading_areas_count': sum(1 for a in leadership_areas if a['position'] == '선도')
        }
    
    def _analyze_patent_citations(self) -> Dict:
        """특허 인용 분석 (영향력 지표)"""
        # 인용 지표
        avg_citations = random.uniform(5, 50)
        highly_cited_patents = random.randint(10, 100)
        
        # 인용 트렌드
        citation_trend = []
        for year in range(2020, 2025):
            citation_trend.append({
                'year': year,
                'citations': random.randint(100, 1000)
            })
        
        # 인용하는 주요 기업들
        citing_companies = []
        companies = ['Tech Giant A', 'Competitor B', 'Partner C', 'Startup D']
        for company in random.sample(companies, k=random.randint(2, 4)):
            citing_companies.append({
                'company': company,
                'citation_count': random.randint(10, 100)
            })
        
        return {
            'avg_citations_per_patent': avg_citations,
            'highly_cited_count': highly_cited_patents,
            'citation_trend': citation_trend,
            'citing_companies': citing_companies,
            'impact_score': min(100, avg_citations * 2)  # 0-100 스케일
        }
    
    def _calculate_innovation_score(self, patents: Dict, rd: Dict, 
                                   leadership: Dict, citations: Dict) -> Dict:
        """혁신 능력 종합 점수"""
        # 특허 점수 (40%)
        patent_score = min(100, (
            (patents['total_portfolio'] / 10) +
            (patents['recent_growth_rate']) +
            (len(patents['key_patents']) * 5)
        ))
        
        # R&D 투자 점수 (30%)
        rd_score = min(100, (
            (rd['avg_rd_ratio'] * 5) +
            (rd['rd_personnel_ratio'] * 2) +
            (len(rd['rd_centers']) * 10)
        ))
        
        # 기술 리더십 점수 (20%)
        leadership_score = min(100, (
            ((20 - leadership['industry_rank']) * 5) +
            (leadership['leading_areas_count'] * 20) +
            (len(leadership['awards']) * 5)
        ))
        
        # 특허 영향력 점수 (10%)
        citation_score = citations['impact_score']
        
        # 가중 평균
        overall_score = (
            patent_score * 0.4 +
            rd_score * 0.3 +
            leadership_score * 0.2 +
            citation_score * 0.1
        )
        
        if overall_score >= 80:
            grade = '혁신 리더'
            color = '#10b981'
        elif overall_score >= 65:
            grade = '기술 우위'
            color = '#3b82f6'
        elif overall_score >= 50:
            grade = '경쟁력 있음'
            color = '#6b7280'
        else:
            grade = '개선 필요'
            color = '#f59e0b'
        
        return {
            'score': overall_score,
            'grade': grade,
            'color': color,
            'components': {
                'patents': patent_score,
                'rd_investment': rd_score,
                'tech_leadership': leadership_score,
                'citations': citation_score
            }
        }


# 테스트
if __name__ == "__main__":
    analyzer = PatentTechAnalyzer("AAPL", "Apple")
    results = analyzer.analyze_innovation()
    
    print("=== 특허/기술력 분석 결과 ===")
    print(f"혁신 등급: {results['innovation_score']['grade']} ({results['innovation_score']['score']:.0f}점)")
    print(f"특허 포트폴리오: {results['patents']['total_portfolio']}건")
    print(f"R&D 투자 비율: {results['rd_investment']['avg_rd_ratio']:.1f}%")
    print(f"기술 리더십 순위: {results['tech_leadership']['industry_rank']}위")
    print(f"특허 영향력: {results['citations']['impact_score']:.0f}점")
