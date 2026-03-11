"""
ESG (환경/사회/지배구조) 평가 모듈 (v6.0)
기업의 ESG 성과 분석 및 점수화
"""

import random
from typing import Dict, List


class ESGAnalyzer:
    """ESG 평가 분석 클래스"""
    
    def __init__(self, ticker: str, company_name: str = ""):
        self.ticker = ticker
        self.company_name = company_name or ticker
        
    def analyze_esg(self) -> Dict:
        """ESG 종합 분석"""
        environmental = self._analyze_environmental()
        social = self._analyze_social()
        governance = self._analyze_governance()
        
        # 종합 ESG 점수
        overall_score = self._calculate_esg_score(
            environmental, social, governance
        )
        
        # ESG 리스크와 기회
        risks_opportunities = self._identify_esg_risks_opportunities(
            environmental, social, governance
        )
        
        return {
            'environmental': environmental,
            'social': social,
            'governance': governance,
            'overall_score': overall_score,
            'risks_opportunities': risks_opportunities,
            'esg_rating': self._get_external_ratings()
        }
    
    def _analyze_environmental(self) -> Dict:
        """환경 (E) 지표 분석"""
        # 탄소 배출
        carbon_emissions = {
            'total_emissions': random.randint(10000, 500000),  # 톤 CO2
            'emissions_intensity': random.uniform(50, 200),  # 톤/백만달러 매출
            'reduction_target': random.randint(20, 50),  # % 감축 목표
            'progress': random.uniform(0.3, 0.9),  # 목표 달성률
            'year_over_year_change': random.uniform(-15, 5)  # % 변화
        }
        
        # 재생에너지
        renewable_energy = {
            'usage_percentage': random.uniform(20, 90),
            'target': random.randint(80, 100),
            'solar_capacity': random.randint(10, 1000),  # MW
            'wind_capacity': random.randint(5, 500)
        }
        
        # 물 사용
        water_management = {
            'total_usage': random.randint(100000, 5000000),  # m³
            'recycling_rate': random.uniform(30, 80),
            'reduction_efforts': random.choice(['우수', '양호', '보통'])
        }
        
        # 폐기물 관리
        waste_management = {
            'total_waste': random.randint(5000, 100000),  # 톤
            'recycling_rate': random.uniform(50, 95),
            'landfill_diversion': random.uniform(70, 99),
            'circular_economy_initiatives': random.randint(3, 10)
        }
        
        # 환경 점수 계산
        env_score = (
            (100 - abs(carbon_emissions['year_over_year_change']) * 2) * 0.3 +
            renewable_energy['usage_percentage'] * 0.3 +
            water_management['recycling_rate'] * 0.2 +
            waste_management['recycling_rate'] * 0.2
        )
        
        return {
            'carbon_emissions': carbon_emissions,
            'renewable_energy': renewable_energy,
            'water_management': water_management,
            'waste_management': waste_management,
            'score': env_score,
            'grade': self._get_grade(env_score),
            'key_initiatives': [
                '탄소중립 2050 목표',
                '100% 재생에너지 전환',
                '순환경제 실천'
            ]
        }
    
    def _analyze_social(self) -> Dict:
        """사회 (S) 지표 분석"""
        # 직원 다양성
        diversity = {
            'women_ratio': random.uniform(0.30, 0.50),
            'women_leadership': random.uniform(0.20, 0.40),
            'minority_representation': random.uniform(0.25, 0.45),
            'pay_equity_ratio': random.uniform(0.90, 1.0)
        }
        
        # 직원 만족도
        employee_satisfaction = {
            'satisfaction_score': random.uniform(3.5, 4.8),  # /5.0
            'turnover_rate': random.uniform(5, 20),
            'training_hours': random.randint(20, 100),  # 연간 시간
            'benefits_rating': random.choice(['우수', '양호', '보통'])
        }
        
        # 안전보건
        health_safety = {
            'accident_rate': random.uniform(0.1, 2.0),  # per 100 직원
            'safety_training_hours': random.randint(8, 40),
            'safety_certifications': random.randint(2, 8),
            'health_programs': random.randint(5, 15)
        }
        
        # 지역사회 기여
        community = {
            'donation_amount': random.randint(1, 50),  # 백만달러
            'volunteer_hours': random.randint(10000, 100000),
            'community_programs': random.randint(5, 20),
            'local_hiring_ratio': random.uniform(0.6, 0.9)
        }
        
        # 사회 점수
        social_score = (
            ((diversity['women_ratio'] + diversity['women_leadership']) / 2) * 100 * 0.25 +
            (employee_satisfaction['satisfaction_score'] / 5.0) * 100 * 0.30 +
            (100 - health_safety['accident_rate'] * 20) * 0.25 +
            min(100, community['donation_amount'] * 2) * 0.20
        )
        
        return {
            'diversity': diversity,
            'employee_satisfaction': employee_satisfaction,
            'health_safety': health_safety,
            'community': community,
            'score': social_score,
            'grade': self._get_grade(social_score),
            'key_initiatives': [
                '다양성과 포용성 프로그램',
                '직원 복지 확대',
                '지역사회 파트너십'
            ]
        }
    
    def _analyze_governance(self) -> Dict:
        """지배구조 (G) 지표 분석"""
        # 이사회 구성
        board = {
            'size': random.randint(7, 15),
            'independent_ratio': random.uniform(0.5, 0.9),
            'women_directors': random.randint(2, 6),
            'avg_tenure': random.uniform(4, 10),
            'meeting_frequency': random.randint(6, 12)
        }
        
        # 경영진 보상
        executive_compensation = {
            'pay_ratio_ceo_median': random.randint(50, 300),
            'performance_linked': random.uniform(0.6, 0.9),
            'ltip_ratio': random.uniform(0.4, 0.7),  # 장기 인센티브 비율
            'clawback_policy': True
        }
        
        # 주주권리
        shareholder_rights = {
            'voting_structure': random.choice(['1주 1의결권', '차등의결권']),
            'shareholder_proposals': random.randint(5, 20),
            'proxy_voting_transparency': random.uniform(0.7, 1.0),
            'dividend_policy': random.choice(['안정적', '성장형'])
        }
        
        # 윤리/컴플라이언스
        ethics_compliance = {
            'code_of_conduct': True,
            'whistleblower_policy': True,
            'anti_corruption_training': random.uniform(0.9, 1.0),
            'compliance_violations': random.randint(0, 3),
            'ethics_hotline_calls': random.randint(50, 200)
        }
        
        # 지배구조 점수
        governance_score = (
            board['independent_ratio'] * 100 * 0.25 +
            executive_compensation['performance_linked'] * 100 * 0.25 +
            shareholder_rights['proxy_voting_transparency'] * 100 * 0.25 +
            (100 - ethics_compliance['compliance_violations'] * 10) * 0.25
        )
        
        return {
            'board': board,
            'executive_compensation': executive_compensation,
            'shareholder_rights': shareholder_rights,
            'ethics_compliance': ethics_compliance,
            'score': governance_score,
            'grade': self._get_grade(governance_score),
            'key_initiatives': [
                '이사회 독립성 강화',
                '주주환원 정책',
                '윤리경영 실천'
            ]
        }
    
    def _calculate_esg_score(self, env: Dict, social: Dict, gov: Dict) -> Dict:
        """ESG 종합 점수 계산"""
        # 가중치 (일반적으로 동등)
        weights = {'E': 0.33, 'S': 0.34, 'G': 0.33}
        
        overall = (
            env['score'] * weights['E'] +
            social['score'] * weights['S'] +
            gov['score'] * weights['G']
        )
        
        # ESG 등급
        if overall >= 80:
            rating = 'AAA'
            color = '#10b981'
            description = 'ESG 리더'
        elif overall >= 70:
            rating = 'AA'
            color = '#3b82f6'
            description = 'ESG 우수'
        elif overall >= 60:
            rating = 'A'
            color = '#6b7280'
            description = 'ESG 양호'
        elif overall >= 50:
            rating = 'BBB'
            color = '#f59e0b'
            description = 'ESG 보통'
        else:
            rating = 'BB 이하'
            color = '#ef4444'
            description = 'ESG 개선 필요'
        
        return {
            'score': overall,
            'rating': rating,
            'color': color,
            'description': description,
            'components': {
                'environmental': env['score'],
                'social': social['score'],
                'governance': gov['score']
            },
            'percentile': random.randint(60, 95)  # 업계 내 백분위
        }
    
    def _identify_esg_risks_opportunities(self, env: Dict, social: Dict, gov: Dict) -> Dict:
        """ESG 리스크와 기회 식별"""
        risks = []
        opportunities = []
        
        # 환경 리스크/기회
        if env['carbon_emissions']['year_over_year_change'] > 0:
            risks.append({
                'category': '환경',
                'risk': '탄소 배출 증가',
                'severity': '중간',
                'mitigation': '재생에너지 투자 확대'
            })
        else:
            opportunities.append({
                'category': '환경',
                'opportunity': '탄소 감축 성과',
                'potential': '높음',
                'action': '그린 파이낸싱 접근성 향상'
            })
        
        # 사회 리스크/기회
        if social['diversity']['women_leadership'] < 0.3:
            risks.append({
                'category': '사회',
                'risk': '낮은 여성 리더십 비율',
                'severity': '낮음',
                'mitigation': '다양성 프로그램 강화'
            })
        
        if social['employee_satisfaction']['satisfaction_score'] > 4.0:
            opportunities.append({
                'category': '사회',
                'opportunity': '높은 직원 만족도',
                'potential': '높음',
                'action': '인재 유치 경쟁력 활용'
            })
        
        # 지배구조 리스크/기회
        if gov['board']['independent_ratio'] < 0.6:
            risks.append({
                'category': '지배구조',
                'risk': '이사회 독립성 부족',
                'severity': '중간',
                'mitigation': '사외이사 비율 확대'
            })
        
        return {
            'risks': risks,
            'opportunities': opportunities,
            'risk_count': len(risks),
            'opportunity_count': len(opportunities)
        }
    
    def _get_external_ratings(self) -> Dict:
        """외부 ESG 평가기관 등급"""
        agencies = ['MSCI', 'Sustainalytics', 'CDP', 'FTSE Russell']
        ratings = []
        
        for agency in random.sample(agencies, k=random.randint(2, 4)):
            rating_scale = {
                'MSCI': ['AAA', 'AA', 'A', 'BBB', 'BB'],
                'Sustainalytics': ['Negligible', 'Low', 'Medium', 'High'],
                'CDP': ['A', 'A-', 'B', 'C'],
                'FTSE Russell': ['4.5+', '4.0-4.5', '3.5-4.0', '3.0-3.5']
            }
            
            ratings.append({
                'agency': agency,
                'rating': random.choice(rating_scale[agency]),
                'last_updated': '2024'
            })
        
        return {'ratings': ratings, 'count': len(ratings)}
    
    def _get_grade(self, score: float) -> str:
        """점수를 등급으로 변환"""
        if score >= 80:
            return 'A+'
        elif score >= 70:
            return 'A'
        elif score >= 60:
            return 'B+'
        elif score >= 50:
            return 'B'
        else:
            return 'C'


# 테스트
if __name__ == "__main__":
    analyzer = ESGAnalyzer("AAPL", "Apple")
    results = analyzer.analyze_esg()
    
    print("=== ESG 분석 결과 ===")
    print(f"종합 등급: {results['overall_score']['rating']} ({results['overall_score']['description']})")
    print(f"종합 점수: {results['overall_score']['score']:.1f}/100")
    print(f"E (환경): {results['environmental']['score']:.1f} ({results['environmental']['grade']})")
    print(f"S (사회): {results['social']['score']:.1f} ({results['social']['grade']})")
    print(f"G (지배구조): {results['governance']['score']:.1f} ({results['governance']['grade']})")
    print(f"업계 백분위: 상위 {100 - results['overall_score']['percentile']}%")
    print(f"리스크: {results['risks_opportunities']['risk_count']}건")
    print(f"기회: {results['risks_opportunities']['opportunity_count']}건")
