#!/usr/bin/env python3
"""
장르별 스토리 구조 자동 적용 테스트
Ollama를 사용한 장르 감지 및 맞춤 구조 적용
"""

import sys
from pathlib import Path
import logging

# 프로젝트 루트 경로 추가
project_root = Path(__file__).parent / "ai-backend"
sys.path.insert(0, str(project_root))

from genre_detector import GenreDetector

# 로깅 설정
logging.basicConfig(
    level=logging.INFO,
    format='%(levelname)s - %(message)s'
)


def test_genre_detection_availability():
    """장르 감지 시스템 상태 확인"""
    print("=" * 60)
    print("🔍 장르 감지 시스템 상태 확인")
    print("=" * 60)
    
    detector = GenreDetector()
    
    if detector.enabled:
        print(f"\n✅ Ollama 서비스 실행 중")
        print(f"🤖 모델: {detector.model}")
        print(f"🔗 URL: {detector.base_url}")
        print(f"\n📚 지원 장르 ({len(detector.GENRE_STRUCTURES)}개):")
        for genre, info in detector.GENRE_STRUCTURES.items():
            print(f"   • {genre}: {info['acts']}막 구조 - {info['description']}")
        return detector
    else:
        print("\n❌ Ollama 서비스 미실행")
        print("\n📝 해결 방법:")
        print("   터미널에서: ollama serve")
        return None


def test_genre_detection_accuracy(detector):
    """장르 감지 정확도 테스트"""
    print("\n" + "=" * 60)
    print("🎯 테스트 1: 장르 감지 정확도")
    print("=" * 60)
    
    test_cases = [
        {
            "input": "선녀와 나무꾼의 전설적인 사랑 이야기, 하늘에서 내려온 선녀",
            "expected": "동화",
            "description": "전통 동화"
        },
        {
            "input": "우주 해적과의 최후의 결전, 폭발하는 우주선, 레이저 총격전",
            "expected": "액션",
            "description": "우주 액션"
        },
        {
            "input": "외계인이 지구에 처음 도착한 날, 미래 기술로 인류와 소통",
            "expected": "SF",
            "description": "SF 퍼스트 컨택트"
        },
        {
            "input": "한밤중 폐가에서 들리는 괴상한 소리, 문이 저절로 열리고",
            "expected": "공포",
            "description": "공포 호러"
        },
        {
            "input": "짝사랑하던 그녀와의 첫 데이트, 두근거리는 마음",
            "expected": "로맨스",
            "description": "로맨스 러브스토리"
        },
        {
            "input": "실수로 상사에게 장난 메일을 보낸 회사원의 하루",
            "expected": "코미디",
            "description": "코미디 일상"
        }
    ]
    
    results = []
    
    for i, test in enumerate(test_cases, 1):
        print(f"\n🎬 테스트 케이스 {i}: {test['description']}")
        print(f"   입력: {test['input'][:50]}...")
        print(f"   예상 장르: {test['expected']}")
        
        # 장르 감지
        result = detector.detect_genre(test['input'])
        detected = result['genre']
        confidence = result.get('confidence', 0)
        keywords = result.get('keywords', [])
        
        print(f"   감지 장르: {detected}")
        print(f"   신뢰도: {confidence:.2f}")
        print(f"   키워드: {keywords}")
        
        # 정확도 판정
        is_correct = (detected == test['expected'])
        status = "✅ 정확" if is_correct else f"⚠️  불일치"
        print(f"   {status}")
        
        results.append({
            "test": test['description'],
            "expected": test['expected'],
            "detected": detected,
            "correct": is_correct
        })
    
    # 정확도 계산
    correct_count = sum(1 for r in results if r['correct'])
    total = len(results)
    accuracy = (correct_count / total) * 100
    
    print(f"\n📊 정확도: {correct_count}/{total} ({accuracy:.0f}%)")
    
    return accuracy >= 50  # 50% 이상이면 통과


def test_structure_application(detector):
    """장르별 구조 적용 테스트"""
    print("\n" + "=" * 60)
    print("📐 테스트 2: 장르별 구조 적용")
    print("=" * 60)
    
    test_stories = [
        ("동화: 마법사의 제자", 7),
        ("액션: 도시를 구하라", 6),
        ("SF: 화성 정복", 7),
        ("공포: 저주받은 집", 8)
    ]
    
    results = []
    
    for story_title, scenes_count in test_stories:
        print(f"\n📖 스토리: {story_title} ({scenes_count}씬)")
        
        # 구조 적용
        result = detector.apply_genre_structure(story_title, scenes_count)
        
        genre = result['genre']
        structure = result['structure']
        scenes = result['scenes']
        
        print(f"   🎭 감지 장르: {genre}")
        print(f"   📐 구조: {structure['acts']}막")
        print(f"   🎬 생성 씬: {len(scenes)}개")
        print(f"   🎨 스타일: {structure['narration_style']}")
        
        # 씬 구성 확인
        print(f"   씬 구성:")
        for scene in scenes[:3]:  # 처음 3개만 출력
            print(f"      씬 {scene['scene_number']}: {scene['act_name']} ({scene['mood']})")
        
        if len(scenes) > 3:
            print(f"      ... ({len(scenes) - 3}개 더)")
        
        # 검증
        is_valid = (
            len(scenes) == scenes_count and
            all('act_name' in s for s in scenes) and
            all('mood' in s for s in scenes)
        )
        
        status = "✅" if is_valid else "❌"
        print(f"   {status} 구조 검증: {'통과' if is_valid else '실패'}")
        
        results.append(is_valid)
    
    success_count = sum(results)
    print(f"\n📊 결과: {success_count}/{len(test_stories)} 성공")
    
    return all(results)


def test_all_genres(detector):
    """모든 장르 구조 확인"""
    print("\n" + "=" * 60)
    print("🎭 테스트 3: 전체 장르 구조 확인")
    print("=" * 60)
    
    print("\n📚 지원 장르 목록:\n")
    
    for genre, structure in detector.GENRE_STRUCTURES.items():
        print(f"🎬 {genre}")
        print(f"   막 구조: {structure['acts']}막")
        print(f"   막 이름: {' → '.join(structure['act_names'])}")
        print(f"   스타일: {structure['narration_style']}")
        print(f"   분위기: {', '.join(structure['mood_palette'][:3])}...")
        print(f"   설명: {structure['description']}")
        print()
    
    return True


def test_scene_distribution(detector):
    """씬 배분 로직 테스트"""
    print("\n" + "=" * 60)
    print("📊 테스트 4: 씬 배분 로직")
    print("=" * 60)
    
    test_cases = [
        ("동화 5씬", "동화", 5),
        ("액션 7씬", "액션", 7),
        ("공포 8씬", "공포", 8),
        ("SF 10씬", "SF", 10)
    ]
    
    results = []
    
    for title, expected_genre, scenes_count in test_cases:
        print(f"\n📖 테스트: {title}")
        
        # 임시로 장르를 명시적으로 설정
        story_input = f"{expected_genre}: 테스트 스토리"
        result = detector.apply_genre_structure(story_input, scenes_count)
        
        scenes = result['scenes']
        structure = result['structure']
        acts_count = structure['acts']
        
        # 막별 씬 수 계산
        act_scene_counts = {}
        for scene in scenes:
            act = scene['act_number']
            act_scene_counts[act] = act_scene_counts.get(act, 0) + 1
        
        print(f"   막 구조: {acts_count}막")
        print(f"   총 씬: {len(scenes)}개")
        print(f"   막별 배분:")
        
        for act_num in sorted(act_scene_counts.keys()):
            act_name = structure['act_names'][act_num - 1]
            count = act_scene_counts[act_num]
            print(f"      {act_num}막 ({act_name}): {count}씬")
        
        # 검증: 총 씬 수가 요청한 수와 일치하는지
        is_correct = len(scenes) == scenes_count
        status = "✅" if is_correct else "❌"
        print(f"   {status} 배분 검증: {'통과' if is_correct else '실패'}")
        
        results.append(is_correct)
    
    success_count = sum(results)
    print(f"\n📊 결과: {success_count}/{len(test_cases)} 성공")
    
    return all(results)


if __name__ == "__main__":
    print("\n" + "🚀 장르별 스토리 구조 자동 적용 테스트 시작\n")
    
    # 1. 시스템 상태 확인
    detector = test_genre_detection_availability()
    
    if not detector:
        print("\n" + "="*60)
        print("❌ Ollama 서비스가 실행 중이지 않습니다.")
        print("="*60)
        print("\n💡 시작 방법:")
        print("   터미널에서: ollama serve")
        sys.exit(1)
    
    # 2~5. 테스트 실행
    test_results = {
        "장르 감지 정확도": test_genre_detection_accuracy(detector),
        "구조 적용": test_structure_application(detector),
        "전체 장르 확인": test_all_genres(detector),
        "씬 배분 로직": test_scene_distribution(detector)
    }
    
    # 결과 요약
    print("\n" + "="*60)
    print("📊 최종 테스트 결과")
    print("="*60)
    
    for test_name, passed in test_results.items():
        status = "✅" if passed else "❌"
        print(f"{status} {test_name}: {'통과' if passed else '실패'}")
    
    total_passed = sum(test_results.values())
    total_tests = len(test_results)
    
    print(f"\n🎯 총 {total_passed}/{total_tests} 테스트 통과 ({total_passed/total_tests*100:.0f}%)")
    
    if total_passed == total_tests:
        print("\n🎉 모든 테스트 성공!")
        print("\n💡 다음 단계:")
        print("   - story_generator.py에 통합")
        print("   - 장르별 최적화된 스토리 생성")
        print("   - 사용자 경험 개선")
    else:
        print("\n⚠️  일부 테스트 실패")
        print("   - 장르 감지 정확도 개선 필요")
        print("   - 프롬프트 엔지니어링 최적화")
    
    print()
