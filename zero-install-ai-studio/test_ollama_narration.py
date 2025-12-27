#!/usr/bin/env python3
"""
Ollama 나레이션 자동 생성 테스트 스크립트
서버의 로컬 AI로 무료 나레이션 생성
"""

import sys
from pathlib import Path
import logging

# 프로젝트 루트 경로 추가
project_root = Path(__file__).parent / "ai-backend"
sys.path.insert(0, str(project_root))

from ollama_narration_generator import OllamaNarrationGenerator

# 로깅 설정
logging.basicConfig(
    level=logging.INFO,
    format='%(levelname)s - %(message)s'
)

def test_ollama_availability():
    """Ollama 서비스 상태 테스트"""
    print("=" * 60)
    print("🔍 Ollama 서비스 상태 확인")
    print("=" * 60)
    
    generator = OllamaNarrationGenerator()
    
    if generator.enabled:
        print(f"\n✅ Ollama 서비스 실행 중")
        print(f"🤖 모델: {generator.model}")
        print(f"🔗 URL: {generator.base_url}")
        return generator
    else:
        print("\n❌ Ollama 서비스 미실행")
        print("\n📝 해결 방법:")
        print("   1. 터미널에서 'ollama serve' 실행")
        print("   2. 또는 백그라운드 실행: 'ollama serve &'")
        print("   3. 모델 확인: 'ollama list'")
        return None


def test_single_narration(generator):
    """단일 나레이션 생성 테스트"""
    print("\n" + "=" * 60)
    print("🎬 테스트 1: 단일 나레이션 생성")
    print("=" * 60)
    
    test_cases = [
        {
            "scene_number": 1,
            "act_name": "발단",
            "korean_mood": "신비로운",
            "scene_title": "시작",
            "user_input": "우주 비행사의 모험",
            "style": "curious",
            "description": "1막 발단 - 호기심 유발"
        },
        {
            "scene_number": 5,
            "act_name": "절정",
            "korean_mood": "충격적인",
            "scene_title": "결정적 순간",
            "user_input": "우주 비행사의 모험",
            "style": "dramatic",
            "description": "4막 절정 - 극적 전개"
        },
        {
            "scene_number": 7,
            "act_name": "결말",
            "korean_mood": "여운이 남는",
            "scene_title": "새로운 시작",
            "user_input": "우주 비행사의 모험",
            "style": "calm",
            "description": "5막 결말 - 차분한 마무리"
        }
    ]
    
    results = []
    
    for i, test in enumerate(test_cases, 1):
        print(f"\n🎨 테스트 케이스 {i}: {test['description']}")
        print(f"   씬 {test['scene_number']}: {test['scene_title']}")
        print(f"   분위기: {test['korean_mood']}")
        print(f"   스타일: {test['style']}")
        
        narration = generator.generate_narration(
            scene_number=test['scene_number'],
            act_name=test['act_name'],
            korean_mood=test['korean_mood'],
            scene_title=test['scene_title'],
            user_input=test['user_input'],
            style=test['style']
        )
        
        if narration:
            print(f"   ✅ 생성: \"{narration}\"")
            print(f"   📏 길이: {len(narration)}자")
            results.append(True)
        else:
            print(f"   ❌ 생성 실패")
            results.append(False)
    
    success_count = sum(results)
    print(f"\n📊 결과: {success_count}/{len(test_cases)} 성공")
    
    return all(results)


def test_batch_narration(generator):
    """배치 나레이션 생성 테스트"""
    print("\n" + "=" * 60)
    print("📦 테스트 2: 배치 나레이션 생성 (7씬)")
    print("=" * 60)
    
    scenes = [
        {"scene_number": 1, "act_name": "발단", "korean_mood": "신비로운", 
         "scene_title": "아침", "user_input": "제빵사의 하루"},
        {"scene_number": 2, "act_name": "전개", "korean_mood": "흥미진진한", 
         "scene_title": "재료 준비", "user_input": "제빵사의 하루"},
        {"scene_number": 3, "act_name": "전개", "korean_mood": "발전하는", 
         "scene_title": "반죽", "user_input": "제빵사의 하루"},
        {"scene_number": 4, "act_name": "위기", "korean_mood": "긴장감 넘치는", 
         "scene_title": "오븐", "user_input": "제빵사의 하루"},
        {"scene_number": 5, "act_name": "절정", "korean_mood": "충격적인", 
         "scene_title": "완성", "user_input": "제빵사의 하루"},
        {"scene_number": 6, "act_name": "결말", "korean_mood": "여운이 남는", 
         "scene_title": "첫 손님", "user_input": "제빵사의 하루"},
        {"scene_number": 7, "act_name": "결말", "korean_mood": "희망찬", 
         "scene_title": "성공", "user_input": "제빵사의 하루"}
    ]
    
    print(f"\n⏳ 7개 나레이션 생성 중... (약 1~2분 소요)")
    
    narrations = generator.generate_batch(scenes)
    
    print(f"\n📝 생성된 나레이션:")
    for i, narration in enumerate(narrations, 1):
        if narration:
            print(f"\n{i}. 씬 {scenes[i-1]['scene_number']}: {scenes[i-1]['scene_title']}")
            print(f"   나레이션: \"{narration}\"")
            print(f"   길이: {len(narration)}자")
        else:
            print(f"\n{i}. ❌ 생성 실패")
    
    success_count = sum(1 for n in narrations if n is not None)
    print(f"\n📊 결과: {success_count}/{len(scenes)} 성공")
    
    # 중복 확인
    valid_narrations = [n for n in narrations if n is not None]
    unique_count = len(set(valid_narrations))
    
    print(f"🔍 중복 검사: {unique_count}/{len(valid_narrations)} 고유 ({len(valid_narrations) - unique_count}개 중복)")
    
    if unique_count == len(valid_narrations):
        print("✅ 모든 나레이션이 고유합니다!")
        return True
    else:
        print("⚠️  일부 나레이션 중복 발견")
        return False


def test_different_stories(generator):
    """다양한 스토리 테마 테스트"""
    print("\n" + "=" * 60)
    print("🎭 테스트 3: 다양한 스토리 테마")
    print("=" * 60)
    
    stories = [
        {
            "user_input": "고양이가 집을 나간 이야기",
            "genre": "동화"
        },
        {
            "user_input": "외계인과의 첫 만남",
            "genre": "SF"
        },
        {
            "user_input": "마지막 결투",
            "genre": "액션"
        }
    ]
    
    results = []
    
    for story in stories:
        print(f"\n📖 스토리: {story['user_input']} ({story['genre']})")
        
        narration = generator.generate_narration(
            scene_number=1,
            act_name="발단",
            korean_mood="신비로운",
            scene_title="시작",
            user_input=story['user_input'],
            style="curious"
        )
        
        if narration:
            print(f"   ✅ \"{narration}\"")
            results.append(True)
        else:
            print(f"   ❌ 생성 실패")
            results.append(False)
    
    success_count = sum(results)
    print(f"\n📊 결과: {success_count}/{len(stories)} 성공")
    
    return all(results)


def test_narration_quality(generator):
    """나레이션 품질 평가"""
    print("\n" + "=" * 60)
    print("⭐ 테스트 4: 나레이션 품질 평가")
    print("=" * 60)
    
    # 샘플 생성
    narration = generator.generate_narration(
        scene_number=1,
        act_name="발단",
        korean_mood="신비로운",
        scene_title="시작",
        user_input="비밀의 정원",
        style="curious"
    )
    
    if not narration:
        print("❌ 나레이션 생성 실패")
        return False
    
    print(f"\n📝 생성된 나레이션: \"{narration}\"")
    
    # 품질 체크
    checks = {
        "길이 적절 (10~40자)": 10 <= len(narration) <= 40,
        "반말 종결어미": any(ending in narration for ending in ['요', '죠', '네요', '어요', '예요']),
        "이모티콘 없음": not any(emoji in narration for emoji in ['😀', '❤️', '👍', '💕', '🎉']),
        "빈 문자열 아님": len(narration.strip()) > 0
    }
    
    print(f"\n🔍 품질 체크:")
    for check, passed in checks.items():
        status = "✅" if passed else "❌"
        print(f"   {status} {check}")
    
    all_passed = all(checks.values())
    
    if all_passed:
        print(f"\n🎉 품질 검증 통과!")
        return True
    else:
        print(f"\n⚠️  일부 품질 기준 미달")
        return False


if __name__ == "__main__":
    print("\n" + "🚀 Ollama 나레이션 자동 생성 테스트 시작\n")
    
    # 1. Ollama 상태 확인
    generator = test_ollama_availability()
    
    if not generator:
        print("\n" + "="*60)
        print("❌ Ollama 서비스가 실행 중이지 않습니다.")
        print("="*60)
        print("\n💡 시작 방법:")
        print("   터미널에서: ollama serve")
        print("\n그 후 다시 테스트를 실행하세요.")
        sys.exit(1)
    
    # 2~5. 테스트 실행
    test_results = {
        "단일 나레이션": test_single_narration(generator),
        "배치 생성": test_batch_narration(generator),
        "다양한 테마": test_different_stories(generator),
        "품질 평가": test_narration_quality(generator)
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
        print("   - 고정 나레이션 풀 → AI 자동 생성으로 전환")
        print("   - 쇼츠 생성 파이프라인에 적용")
    else:
        print("\n⚠️  일부 테스트 실패")
        print("   - 로그를 확인하여 원인 파악")
        print("   - Ollama 모델 상태 확인: ollama list")
    
    print()
