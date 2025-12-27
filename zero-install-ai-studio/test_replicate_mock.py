#!/usr/bin/env python3
"""
Replicate API 모의 테스트 (API 토큰 없이 코드 검증)
실제 API 호출 없이 클라이언트 구조를 확인합니다.
"""

import sys
from pathlib import Path

# 프로젝트 루트 경로 추가
project_root = Path(__file__).parent / "ai-backend"
sys.path.insert(0, str(project_root))

def test_replicate_client_import():
    """ReplicateClient 임포트 테스트"""
    print("=" * 60)
    print("📦 ReplicateClient 임포트 테스트")
    print("=" * 60)
    
    try:
        from replicate_client import ReplicateClient
        print("\n✅ ReplicateClient 클래스 임포트 성공")
        
        # 클래스 메서드 확인
        methods = [m for m in dir(ReplicateClient) if not m.startswith('_')]
        print(f"\n📋 사용 가능한 메서드 ({len(methods)}개):")
        for method in methods:
            print(f"   • {method}")
        
        return True
    except ImportError as e:
        print(f"\n❌ 임포트 실패: {e}")
        return False
    except Exception as e:
        print(f"\n❌ 오류 발생: {e}")
        return False


def test_replicate_client_structure():
    """ReplicateClient 구조 테스트"""
    print("\n" + "=" * 60)
    print("🔧 ReplicateClient 구조 테스트")
    print("=" * 60)
    
    try:
        from replicate_client import ReplicateClient
        
        # 모의 클라이언트 생성 (토큰 없이)
        print("\n🔍 클래스 구조 분석 중...")
        
        # 예상되는 메서드 확인
        expected_methods = [
            'generate_image_sdxl',
            'generate_image_flux',
            'generate_video',
            'generate_images_batch'
        ]
        
        print(f"\n✅ 필수 메서드 확인:")
        for method in expected_methods:
            has_method = hasattr(ReplicateClient, method)
            status = "✅" if has_method else "❌"
            print(f"   {status} {method}")
        
        # 모든 메서드가 존재하는지 확인
        all_methods_exist = all(hasattr(ReplicateClient, m) for m in expected_methods)
        
        if all_methods_exist:
            print(f"\n🎉 모든 필수 메서드가 구현되어 있습니다!")
            return True
        else:
            print(f"\n⚠️  일부 메서드가 누락되었습니다.")
            return False
            
    except Exception as e:
        print(f"\n❌ 오류 발생: {e}")
        return False


def test_replicate_client_dependencies():
    """ReplicateClient 의존성 테스트"""
    print("\n" + "=" * 60)
    print("📚 의존성 패키지 테스트")
    print("=" * 60)
    
    dependencies = {
        'replicate': 'Replicate Python SDK',
        'requests': 'HTTP 라이브러리',
        'Pillow': '이미지 처리 라이브러리'
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
        print(f"\n⚠️  일부 패키지 설치 필요:")
        print("   pip install replicate requests Pillow")
    
    return all_installed


def test_replicate_api_readiness():
    """Replicate API 준비 상태 종합 테스트"""
    print("\n" + "=" * 60)
    print("🎯 Replicate API 준비 상태 종합 평가")
    print("=" * 60)
    
    import os
    
    checklist = {
        "클라이언트 코드": False,
        "의존성 패키지": False,
        "API 토큰": False
    }
    
    # 1. 클라이언트 코드 확인
    try:
        from replicate_client import ReplicateClient
        checklist["클라이언트 코드"] = True
        print("\n✅ 1. 클라이언트 코드: 준비됨")
    except:
        print("\n❌ 1. 클라이언트 코드: 누락")
    
    # 2. 의존성 확인
    try:
        import replicate
        checklist["의존성 패키지"] = True
        print("✅ 2. 의존성 패키지: 설치됨")
    except:
        print("❌ 2. 의존성 패키지: 미설치")
    
    # 3. API 토큰 확인
    api_token = os.getenv("REPLICATE_API_TOKEN")
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
        print("\n🎉 Replicate API 사용 준비 완료!")
        print("   ▶ python3 test_replicate_api.py 실행하여 실제 테스트를 진행하세요.")
        return True
    else:
        print("\n⚠️  준비 미완료 항목:")
        for item, status in checklist.items():
            if not status:
                print(f"   • {item}")
        
        print("\n📝 다음 단계:")
        if not checklist["의존성 패키지"]:
            print("   1. pip install replicate requests Pillow")
        if not checklist["API 토큰"]:
            print("   2. ./setup_replicate.sh 실행하여 API 토큰 설정")
        
        return False


if __name__ == "__main__":
    print("\n🚀 Replicate API 모의 테스트 시작\n")
    
    # 테스트 실행
    test1 = test_replicate_client_import()
    test2 = test_replicate_client_structure()
    test3 = test_replicate_client_dependencies()
    test4 = test_replicate_api_readiness()
    
    # 최종 결과
    print("\n" + "=" * 60)
    print("✅ 모의 테스트 완료")
    print("=" * 60)
    
    if test4:
        print("\n✨ 실제 API 테스트를 실행할 준비가 되었습니다!")
        print("   명령어: python3 test_replicate_api.py")
    else:
        print("\n⚠️  설정을 완료한 후 다시 시도하세요.")
    print()
