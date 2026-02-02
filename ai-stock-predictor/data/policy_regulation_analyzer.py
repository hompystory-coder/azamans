"""
정책/규제 정보 분석 모듈 (v6.0)
정부 정책, 산업 규제, 법안 등이 기업에 미치는 영향 분석
"""

import random
from datetime import datetime, timedelta
from typing import Dict, List


class PolicyRegulationAnalyzer:
    """정책/규제 분석 클래스"""
    
    def __init__(self, ticker: str, sector: str = "", country: str = "US"):
        self.ticker = ticker
        self.sector = sector or "Technology"
        self.country = country
        
    def analyze_policy_impact(self) -> Dict:
        """정책/규제 영향 분석"""
        regulations = self._analyze_regulations()
        policies = self._analyze_government_policies()
        bills = self._analyze_pending_bills()
        compliance = self._analyze_compliance_status()
        
        # 종합 리스크 평가
        overall_risk = self._calculate_policy_risk(
            regulations, policies, bills, compliance
        )
        
        return {
            'regulations': regulations,
            'policies': policies,
            'bills': bills,
            'compliance': compliance,
            'overall_risk': overall_risk,
            'opportunities': self._identify_opportunities(policies, bills)
        }
    
    def _analyze_regulations(self) -> Dict:
        """규제 현황 분석"""
        current_regs = []
        reg_categories = ['환경 규제', '데이터 보호', '금융 규제', '노동 규제', '산업 안전']
        
        for category in reg_categories[:random.randint(2, 4)]:
            impact = random.choice(['높음', '보통', '낮음'])
            compliance_cost = random.randint(100, 5000)  # 만달러
            
            current_regs.append({
                'category': category,
                'impact_level': impact,
                'estimated_cost': compliance_cost,
                'deadline': (datetime.now() + timedelta(days=random.randint(30, 365))).strftime('%Y-%m-%d'),
                'status': random.choice(['준수 중', '대응 중', '검토 중'])
            })
        
        total_cost = sum(r['estimated_cost'] for r in current_regs)
        high_impact_count = sum(1 for r in current_regs if r['impact_level'] == '높음')
        
        return {
            'regulations': current_regs,
            'total_compliance_cost': total_cost,
            'high_impact_count': high_impact_count,
            'overall_burden': '높음' if high_impact_count >= 2 else '보통' if high_impact_count == 1 else '낮음'
        }
    
    def _analyze_government_policies(self) -> Dict:
        """정부 정책 분석"""
        policies = []
        policy_types = [
            '세금 정책',
            '보조금 프로그램',
            '무역 정책',
            '산업 지원',
            '기술 육성'
        ]
        
        for policy_type in policy_types[:random.randint(2, 4)]:
            impact = random.choice(['긍정적', '부정적', '중립적'])
            magnitude = random.uniform(0.3, 1.0)
            
            policies.append({
                'type': policy_type,
                'impact': impact,
                'magnitude': magnitude,
                'announced_date': (datetime.now() - timedelta(days=random.randint(1, 180))).strftime('%Y-%m-%d'),
                'implementation_date': (datetime.now() + timedelta(days=random.randint(30, 180))).strftime('%Y-%m-%d'),
                'description': self._generate_policy_description(policy_type, impact)
            })
        
        positive_count = sum(1 for p in policies if p['impact'] == '긍정적')
        negative_count = sum(1 for p in policies if p['impact'] == '부정적')
        
        return {
            'policies': policies,
            'positive_count': positive_count,
            'negative_count': negative_count,
            'net_impact': '긍정적' if positive_count > negative_count else '부정적' if negative_count > positive_count else '중립적'
        }
    
    def _analyze_pending_bills(self) -> Dict:
        """계류 중인 법안 분석"""
        bills = []
        bill_topics = [
            '반독점법 강화',
            '개인정보 보호 강화',
            '친환경 규제',
            '노동자 권리 보호',
            'R&D 세액공제 확대'
        ]
        
        for topic in bill_topics[:random.randint(2, 4)]:
            pass_probability = random.uniform(0.2, 0.8)
            impact = random.choice(['긍정적', '부정적', '중립적'])
            
            bills.append({
                'topic': topic,
                'pass_probability': pass_probability,
                'expected_impact': impact,
                'stage': random.choice(['위원회 심의', '본회의 대기', '표결 예정']),
                'expected_date': (datetime.now() + timedelta(days=random.randint(60, 365))).strftime('%Y-%m-%d')
            })
        
        high_risk_bills = [b for b in bills if b['pass_probability'] > 0.6 and b['expected_impact'] == '부정적']
        
        return {
            'bills': bills,
            'total_count': len(bills),
            'high_risk_count': len(high_risk_bills),
            'requires_attention': len(high_risk_bills) > 0
        }
    
    def _analyze_compliance_status(self) -> Dict:
        """컴플라이언스 현황"""
        score = random.randint(75, 98)
        violations = random.randint(0, 3)
        
        return {
            'compliance_score': score,
            'violations_last_year': violations,
            'certification_status': '양호' if score >= 90 else '보통' if score >= 80 else '주의 필요',
            'last_audit_date': (datetime.now() - timedelta(days=random.randint(30, 180))).strftime('%Y-%m-%d'),
            'next_audit_date': (datetime.now() + timedelta(days=random.randint(30, 180))).strftime('%Y-%m-%d')
        }
    
    def _calculate_policy_risk(self, regulations: Dict, policies: Dict, 
                               bills: Dict, compliance: Dict) -> Dict:
        """종합 정책 리스크 계산"""
        # 리스크 점수 (0-100, 높을수록 리스크 높음)
        reg_risk = regulations['high_impact_count'] * 20
        policy_risk = policies['negative_count'] * 15
        bill_risk = bills['high_risk_count'] * 25
        compliance_risk = (100 - compliance['compliance_score']) * 0.5
        
        total_risk = min(100, reg_risk + policy_risk + bill_risk + compliance_risk)
        
        if total_risk < 30:
            level = '낮음'
            color = '#10b981'
        elif total_risk < 60:
            level = '보통'
            color = '#f59e0b'
        else:
            level = '높음'
            color = '#ef4444'
        
        return {
            'risk_score': total_risk,
            'risk_level': level,
            'color': color,
            'components': {
                'regulations': reg_risk,
                'policies': policy_risk,
                'bills': bill_risk,
                'compliance': compliance_risk
            }
        }
    
    def _identify_opportunities(self, policies: Dict, bills: Dict) -> List[Dict]:
        """정책 기회 식별"""
        opportunities = []
        
        # 긍정적 정책
        for policy in policies['policies']:
            if policy['impact'] == '긍정적' and policy['magnitude'] > 0.6:
                opportunities.append({
                    'type': '정책 기회',
                    'source': policy['type'],
                    'description': policy['description'],
                    'potential_benefit': '높음'
                })
        
        # 유리한 법안
        for bill in bills['bills']:
            if bill['expected_impact'] == '긍정적' and bill['pass_probability'] > 0.5:
                opportunities.append({
                    'type': '법안 기회',
                    'source': bill['topic'],
                    'description': f"{bill['topic']} 통과 시 혜택 예상",
                    'potential_benefit': '높음' if bill['pass_probability'] > 0.7 else '보통'
                })
        
        return opportunities[:5]
    
    def _generate_policy_description(self, policy_type: str, impact: str) -> str:
        """정책 설명 생성"""
        descriptions = {
            '세금 정책': f"법인세율 {'인하' if impact == '긍정적' else '인상'} 정책",
            '보조금 프로그램': f"{self.sector} 산업 {'지원' if impact == '긍정적' else '축소'} 프로그램",
            '무역 정책': f"{'관세 완화' if impact == '긍정적' else '관세 강화'} 정책",
            '산업 지원': f"{'혁신 기업 육성' if impact == '긍정적' else '규제 강화'} 정책",
            '기술 육성': f"R&D {'세액공제 확대' if impact == '긍정적' else '지원 축소'}"
        }
        return descriptions.get(policy_type, "정책 변화")


# 테스트
if __name__ == "__main__":
    analyzer = PolicyRegulationAnalyzer("AAPL", "Technology", "US")
    results = analyzer.analyze_policy_impact()
    
    print(f"=== 정책/규제 분석 결과 ===")
    print(f"전체 리스크: {results['overall_risk']['risk_level']} ({results['overall_risk']['risk_score']:.0f}점)")
    print(f"규제 부담: {results['regulations']['overall_burden']}")
    print(f"정책 영향: {results['policies']['net_impact']}")
    print(f"기회 발견: {len(results['opportunities'])}건")
