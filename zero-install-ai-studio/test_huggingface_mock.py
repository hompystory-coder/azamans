#!/usr/bin/env python3
"""
Hugging Face API 모의 테스트 (API 호출 없이 코드 검증)
"""

import sys
from pathlib import Path

# 프로젝트 루트 경로 추가
project_root = Path(__file__).parent / "ai-backend"
sys.path.insert(0, str(project_root))

def test_huggingface_client_import():
    """HuggingFaceClient 임포트 테스트"""
    print("=" * 60)
    print("📦 HuggingFaceClient 임포트 테스트")
    print("=" * 60)
    
    try:
        from huggingface_client import HuggingFaceClient
        print("\n✅ HuggingFaceClient 클래스 임포트 성공")
        
        # 클래스 메서드 확인
        methods = [m for m in dir(HuggingFaceClient) if not m.startswith('_')]
        print(f"\n📋 사용 가능한 메서드 ({len(methods)}개):")
        for method in methods:
            print(f"   • {method}")
        
        # 모델 확인
        print(f"\n🤖 지원되는 모델:")
        for model_key, model_id in HuggingFaceClient.MODELS.items():
            print(f"   • {model_key}: {model_id}")
        
        return True
    except ImportError as e:
        print(f"\n❌ 임포트 실패: {e}")
        return False
    except Exception as e:
        print(f"\n❌ 오류 발생: {e}")
        return False


def test_huggingface_client_structure():
    """HuggingFaceClient 구조 테스트"""
    print("\n" + "=" * 60)
    print("🔧 HuggingFaceClient 구조 테스트")
    print("=" * 60)
    
    try:
        from huggingface_client import HuggingFaceClient
        
        # 예상되는 메서드 확인
        expected_methods = [
            'generate_image',
            'save_image',
            'generate_and_save',
            'generate_batch'
        ]
        
        print(f"\n✅ 필수 메서드 확인:")
        for method in expected_methods:
            has_method = hasattr(HuggingFaceClient, method)
            status = "✅" if has_method else "❌"
            print(f"   {status} {method}")
        
        # 모든 메서드가 존재하는지 확인
        all_methods_exist = all(hasattr(HuggingFaceClient, m) for m in expected_methods)
        
        if all_methods_exist:
            print(f"\n🎉 모든 필수 메서드가 구현되어 있습니다!")
            return True
        else:
            print(f"\n⚠️  일부 메서드가 누락되었습니다.")
            return False
            
    except Exception as e:
        print(f"\n❌ 오류 발생: {e}")
        return False


def test_huggingface_client_dependencies():
    """HuggingFaceClient 의존성 테스트"""
    print("\n" + "=" * 60)
    print("📚 의존성 패키지 테스트")
    print("=" * 60)
    
    dependencies = {
        'requests': 'HTTP 라이브러리',
        'logging': 'Python 기본 로깅',
        'time': 'Python 기본 시간',
        'typing': 'Python 기본 타입 힌트'
    }
    
    results = []
    
    for package, description in dependencies.items():
        try:
            __import__(package)
            print(f"✅ {package:15} - {description}")
            results.append(True)
        except ImportError:
            print(f"❌ {package:15} - {description} (설치 필요)")
            results.append(False)
    
    all_installed = all(results)
    
    if all_installed:
        print(f"\n🎉 모든 의존성이 설치되어 있습니다!")
    else:
        print(f"\n⚠️  일부 패키지 설치 필요")
    
    return all_installed


def test_huggingface_api_readiness():
    """Hugging Face API 준비 상태 종합 테스트"""
    print("\n" + "=" * 60)
    print("🎯 Hugging Face API 준비 상태 종합 평가")
    print("=" * 60)
    
    import os
    
    checklist = {
        "클라이언트 코드": False,
        "의존성 패키지": False,
        "API 토큰": False
    }
    
    # 1. 클라이언트 코드 확인
    try:
        from huggingface_client import HuggingFaceClient
        checklist["클라이언트 코드"] = True
        print("\n✅ 1. 클라이언트 코드: 준비됨")
    except:
        print("\n❌ 1. 클라이언트 코드: 누락")
    
    # 2. 의존성 확인
    try:
        import requests
        checklist["의존성 패키지"] = True
        print("✅ 2. 의존성 패키지: 설치됨")
    except:
        print("❌ 2. 의존성 패키지: 미설치")
    
    # 3. API 토큰 확인
    api_token = os.getenv("HF_TOKEN") or os.getenv("HUGGINGFACE_TOKEN")
    if api_token:
        checklist["API 토큰"] = True
        print(f"✅ 3. API 토큰: 설정됨 ({api_token[:10]}...)")
    else:
        print("❌ 3. API 토큰: 미설정")
    
    # 종합 평가
    ready_count = sum(checklist.values())
    total_count = len(checklist)
    
    print("\n" + "=" * 60)
    print(f"📊 준비 상태: {ready_count}/{total_count} ({ready_count/total_count*100:.0f}%)")
    print("=" * 60)
    
    if ready_count == total_count:
        print("\n🎉 Hugging Face API 사용 준비 완료!")
        print("   ▶ python3 test_huggingface_api.py 실행하여 실제 테스트를 진행하세요.")
        print("\n💡 Hugging Face 장점:")
        print("   • 완전 무료 (무제한)")
        print("   • 계정만 있으면 사용 가능")
        print("   • 다양한 모델 지원")
        print("\n⚠️  Hugging Face 단점:")
        print("   • 대기 시간 1~5분")
        print("   • 품질이 Replicate보다 낮을 수 있음")
        return True
    else:
        print("\n⚠️  준비 미완료 항목:")
        for item, status in checklist.items():
            if not status:
                print(f"   • {item}")
        
        print("\n📝 다음 단계:")
        if not checklist["의존성 패키지"]:
            print("   1. pip install requests")
        if not checklist["API 토큰"]:
            print("   2. ./setup_huggingface.sh 실행하여 API 토큰 설정")
        
        return False


def compare_apis():
    """Replicate vs Hugging Face 비교"""
    print("\n" + "=" * 60)
    print("🆚 Replicate vs Hugging Face 비교")
    print("=" * 60)
    
    comparison = {
        "비용": {
            "Replicate": "$5 무료 크레딧 → 유료",
            "Hugging Face": "완전 무료 (무제한)"
        },
        "속도": {
            "Replicate": "빠름 (30~60초)",
            "Hugging Face": "느림 (1~5분)"
        },
        "품질": {
            "Replicate": "최고 품질",
            "Hugging Face": "중간 품질"
        },
        "대기열": {
            "Replicate": "없음 (즉시 처리)",
            "Hugging Face": "있음 (대기 필요)"
        },
        "추천 용도": {
            "Replicate": "프로덕션 환경",
            "Hugging Face": "개발/테스트 환경"
        }
    }
    
    print("\n┌─────────────┬─────────────────────┬──────────────────────┐")
    print("│ 항목        │ Replicate           │ Hugging Face         │")
    print("├─────────────┼─────────────────────┼──────────────────────┤")
    
    for category, values in comparison.items():
        rep_value = values["Replicate"]
        hf_value = values["Hugging Face"]
        print(f"│ {category:11} │ {rep_value:19} │ {hf_value:20} │")
    
    print("└─────────────┴─────────────────────┴──────────────────────┘")
    
    print("\n💡 권장 전략:")
    print("   1. 개발 단계: Hugging Face (무료)")
    print("   2. 테스트 단계: Replicate ($5 크레딧)")
    print("   3. 소규모 프로덕션: Replicate (유료)")
    print("   4. 대규모 프로덕션: GPU 서버 렌탈")


if __name__ == "__main__":
    print("\n🚀 Hugging Face API 모의 테스트 시작\n")
    
    # 테스트 실행
    test1 = test_huggingface_client_import()
    test2 = test_huggingface_client_structure()
    test3 = test_huggingface_client_dependencies()
    test4 = test_huggingface_api_readiness()
    
    # API 비교
    compare_apis()
    
    # 최종 결과
    print("\n" + "=" * 60)
    print("✅ 모의 테스트 완료")
    print("=" * 60)
    
    if test4:
        print("\n✨ 실제 API 테스트를 실행할 준비가 되었습니다!")
        print("   명령어: python3 test_huggingface_api.py")
    else:
        print("\n⚠️  설정을 완료한 후 다시 시도하세요.")
        print("   가이드: ./setup_huggingface.sh")
    print()
