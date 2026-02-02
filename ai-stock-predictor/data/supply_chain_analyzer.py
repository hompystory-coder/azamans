"""
공급망 및 파트너십 분석 모듈 (v6.0)
주요 공급업체, 파트너사, 고객사 분석 및 공급망 리스크 평가
"""

import random
from datetime import datetime, timedelta
from typing import Dict, List


class SupplyChainAnalyzer:
    """공급망 분석 클래스"""
    
    def __init__(self, ticker: str, company_name: str = ""):
        self.ticker = ticker
        self.company_name = company_name or ticker
        
    def analyze_supply_chain(self) -> Dict:
        """공급망 종합 분석"""
        suppliers = self._analyze_suppliers()
        partners = self._analyze_partners()
        customers = self._analyze_key_customers()
        risks = self._analyze_supply_chain_risks()
        
        # 공급망 건전성 점수
        health_score = self._calculate_health_score(suppliers, partners, risks)
        
        return {
            'suppliers': suppliers,
            'partners': partners,
            'customers': customers,
            'risks': risks,
            'health_score': health_score,
            'diversification_level': self._assess_diversification(suppliers, customers)
        }
    
    def _analyze_suppliers(self) -> Dict:
        """주요 공급업체 분석"""
        top_suppliers = []
        regions = ['아시아', '북미', '유럽']
        
        for i in range(random.randint(5, 10)):
            dependency = random.uniform(0.05, 0.3)
            reliability = random.uniform(0.7, 0.98)
            
            top_suppliers.append({
                'name': f"Supplier {chr(65+i)}",
                'region': random.choice(regions),
                'dependency': dependency,  # 의존도 (0-1)
                'reliability_score': reliability,
                'contract_status': random.choice(['장기계약', '단기계약', '협상중']),
                'risk_level': '높음' if dependency > 0.2 else '보통' if dependency > 0.1 else '낮음'
            })
        
        # 지역별 분산
        region_dist = {}
        for region in regions:
            count = sum(1 for s in top_suppliers if s['region'] == region)
            region_dist[region] = count
        
        # 높은 의존도 공급업체 수
        high_dependency = sum(1 for s in top_suppliers if s['dependency'] > 0.15)
        
        return {
            'suppliers': top_suppliers,
            'total_count': len(top_suppliers),
            'region_distribution': region_dist,
            'high_dependency_count': high_dependency,
            'avg_reliability': sum(s['reliability_score'] for s in top_suppliers) / len(top_suppliers) * 100
        }
    
    def _analyze_partners(self) -> Dict:
        """파트너십 분석"""
        partnerships = []
        partnership_types = ['기술 제휴', '유통 파트너', '전략적 제휴', 'R&D 협력']
        
        for i in range(random.randint(3, 8)):
            value = random.randint(10, 500)  # 백만달러
            duration = random.randint(1, 5)  # 년
            
            partnerships.append({
                'partner': f"Partner Corp {i+1}",
                'type': random.choice(partnership_types),
                'value': value,
                'duration_years': duration,
                'status': random.choice(['활성', '협상중', '갱신예정']),
                'strategic_importance': random.choice(['매우 높음', '높음', '보통'])
            })
        
        total_value = sum(p['value'] for p in partnerships)
        strategic_count = sum(1 for p in partnerships if p['strategic_importance'] == '매우 높음')
        
        return {
            'partnerships': partnerships,
            'total_count': len(partnerships),
            'total_value': total_value,
            'strategic_partnerships': strategic_count,
            'partnership_strength': '강함' if strategic_count >= 2 else '보통'
        }
    
    def _analyze_key_customers(self) -> Dict:
        """주요 고객사 분석"""
        customers = []
        
        for i in range(random.randint(5, 10)):
            revenue_contribution = random.uniform(0.03, 0.25)
            
            customers.append({
                'name': f"Customer {chr(65+i)}",
                'revenue_contribution': revenue_contribution,
                'relationship_duration': random.randint(1, 10),
                'growth_trend': random.choice(['증가', '안정', '감소']),
                'risk_level': '높음' if revenue_contribution > 0.15 else '보통' if revenue_contribution > 0.08 else '낮음'
            })
        
        # 상위 고객 집중도
        top3_concentration = sum(sorted([c['revenue_contribution'] for c in customers], reverse=True)[:3])
        
        return {
            'customers': customers,
            'total_count': len(customers),
            'top3_concentration': top3_concentration,
            'concentration_risk': '높음' if top3_concentration > 0.5 else '보통' if top3_concentration > 0.3 else '낮음'
        }
    
    def _analyze_supply_chain_risks(self) -> Dict:
        """공급망 리스크 분석"""
        risks = []
        risk_types = [
            ('지정학적 리스크', '특정 지역 의존도'),
            ('자연재해 리스크', '재해 취약 지역'),
            ('물류 리스크', '운송 지연 가능성'),
            ('원자재 가격 변동', '가격 상승 압력'),
            ('공급업체 재무 리스크', '파산 위험')
        ]
        
        for risk_type, description in random.sample(risk_types, k=random.randint(2, 4)):
            probability = random.uniform(0.2, 0.7)
            impact = random.uniform(0.3, 0.8)
            
            risks.append({
                'type': risk_type,
                'description': description,
                'probability': probability,
                'impact': impact,
                'severity': self._calculate_risk_severity(probability, impact),
                'mitigation': self._get_mitigation_strategy(risk_type)
            })
        
        # 전체 리스크 레벨
        avg_severity = sum(r['probability'] * r['impact'] for r in risks) / len(risks) if risks else 0
        
        return {
            'risks': risks,
            'total_risks': len(risks),
            'avg_severity': avg_severity,
            'risk_level': '높음' if avg_severity > 0.5 else '보통' if avg_severity > 0.3 else '낮음'
        }
    
    def _calculate_health_score(self, suppliers: Dict, partners: Dict, risks: Dict) -> Dict:
        """공급망 건전성 점수 계산"""
        # 각 요소별 점수
        supplier_score = (
            (100 - suppliers['high_dependency_count'] * 15) *
            (suppliers['avg_reliability'] / 100)
        )
        
        partner_score = min(100, partners['total_count'] * 10 + partners['strategic_partnerships'] * 15)
        
        risk_score = 100 - (risks['avg_severity'] * 100)
        
        # 가중 평균
        overall_score = (supplier_score * 0.4 + partner_score * 0.3 + risk_score * 0.3)
        
        if overall_score >= 80:
            grade = '우수'
            color = '#10b981'
        elif overall_score >= 65:
            grade = '양호'
            color = '#3b82f6'
        elif overall_score >= 50:
            grade = '보통'
            color = '#f59e0b'
        else:
            grade = '주의'
            color = '#ef4444'
        
        return {
            'score': overall_score,
            'grade': grade,
            'color': color,
            'components': {
                'supplier': supplier_score,
                'partner': partner_score,
                'risk': risk_score
            }
        }
    
    def _assess_diversification(self, suppliers: Dict, customers: Dict) -> str:
        """공급망 다각화 수준 평가"""
        supplier_diversity = len(set(s['region'] for s in suppliers['suppliers']))
        customer_risk = customers['concentration_risk']
        
        if supplier_diversity >= 3 and customer_risk == '낮음':
            return '우수'
        elif supplier_diversity >= 2 and customer_risk in ['낮음', '보통']:
            return '양호'
        else:
            return '개선 필요'
    
    def _calculate_risk_severity(self, probability: float, impact: float) -> str:
        """리스크 심각도 계산"""
        severity = probability * impact
        if severity > 0.5:
            return '높음'
        elif severity > 0.3:
            return '보통'
        else:
            return '낮음'
    
    def _get_mitigation_strategy(self, risk_type: str) -> str:
        """리스크 완화 전략"""
        strategies = {
            '지정학적 리스크': '공급업체 지역 다각화',
            '자연재해 리스크': '재고 버퍼 확보 및 대체 공급선 구축',
            '물류 리스크': '다중 물류 채널 확보',
            '원자재 가격 변동': '장기 고정가격 계약 체결',
            '공급업체 재무 리스크': '재무상태 정기 모니터링'
        }
        return strategies.get(risk_type, '리스크 모니터링 강화')


# 테스트
if __name__ == "__main__":
    analyzer = SupplyChainAnalyzer("AAPL", "Apple")
    results = analyzer.analyze_supply_chain()
    
    print("=== 공급망 분석 결과 ===")
    print(f"건전성: {results['health_score']['grade']} ({results['health_score']['score']:.0f}점)")
    print(f"공급업체: {results['suppliers']['total_count']}개")
    print(f"파트너십: {results['partners']['total_count']}개")
    print(f"다각화 수준: {results['diversification_level']}")
    print(f"리스크 레벨: {results['risks']['risk_level']}")
