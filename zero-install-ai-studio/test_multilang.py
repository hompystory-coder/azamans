#!/usr/bin/env python3
"""
다국어 쇼츠 생성 파이프라인 테스트
Ollama를 사용한 나레이션 번역 시스템
"""

import sys
from pathlib import Path
import logging

# 프로젝트 루트 경로 추가
project_root = Path(__file__).parent / "ai-backend"
sys.path.insert(0, str(project_root))

from multilang_translator import MultiLangTranslator

# 로깅 설정
logging.basicConfig(
    level=logging.INFO,
    format='%(levelname)s - %(message)s'
)


def test_translator_availability():
    """번역 시스템 상태 확인"""
    print("=" * 60)
    print("🔍 다국어 번역 시스템 상태 확인")
    print("=" * 60)
    
    translator = MultiLangTranslator()
    
    if translator.enabled:
        print(f"\n✅ Ollama 서비스 실행 중")
        print(f"🤖 모델: {translator.model}")
        print(f"🔗 URL: {translator.base_url}")
        
        # 지원 언어 목록
        languages = translator.get_supported_languages()
        print(f"\n🌐 지원 언어 ({len(languages)}개):")
        for lang in languages:
            print(f"   • {lang['code']}: {lang['name']} ({lang['native']}) - {lang['quality']}")
        
        return translator
    else:
        print("\n❌ Ollama 서비스 미실행")
        print("\n📝 해결 방법:")
        print("   터미널에서: ollama serve")
        return None


def test_single_translation(translator):
    """단일 나레이션 번역 테스트"""
    print("\n" + "=" * 60)
    print("🌐 테스트 1: 단일 나레이션 번역")
    print("=" * 60)
    
    test_cases = [
        {
            "korean": "여러분, 이건 정말 믿기 힘든 이야기인데 한번 들어보세요.",
            "description": "호기심 유발형 나레이션"
        },
        {
            "korean": "그런데 여기서 예상치 못한 일이 벌어지기 시작했죠.",
            "description": "전개 나레이션"
        },
        {
            "korean": "바로 이 순간, 모든 게 완전히 바뀌어버렸어요.",
            "description": "절정 나레이션"
        }
    ]
    
    target_languages = ["en", "ja", "zh"]
    results = []
    
    for test in test_cases:
        print(f"\n📝 원문 ({test['description']}):")
        print(f"   🇰🇷 {test['korean']}")
        
        test_results = {"korean": test['korean'], "translations": {}}
        
        for lang_code in target_languages:
            lang_name = translator.LANGUAGES[lang_code]['native']
            
            translated = translator.translate_narration(test['korean'], lang_code)
            
            if translated:
                print(f"   {_get_flag(lang_code)} {translated}")
                test_results['translations'][lang_code] = translated
            else:
                print(f"   ❌ {lang_name}: 번역 실패")
                test_results['translations'][lang_code] = None
        
        results.append(test_results)
    
    # 성공률 계산
    total_translations = len(test_cases) * len(target_languages)
    successful = sum(
        1 for r in results 
        for t in r['translations'].values() 
        if t is not None
    )
    
    print(f"\n📊 번역 성공률: {successful}/{total_translations} ({successful/total_translations*100:.0f}%)")
    
    return successful == total_translations


def test_batch_translation(translator):
    """배치 번역 테스트"""
    print("\n" + "=" * 60)
    print("📦 테스트 2: 스토리 배치 번역")
    print("=" * 60)
    
    # 샘플 스토리 (7씬)
    sample_story = [
        {"scene_number": 1, "narration": "오늘은 정말 특별한 날이 될 거예요."},
        {"scene_number": 2, "narration": "여러분, 이야기를 시작해볼까요?"},
        {"scene_number": 3, "narration": "그런데 여기서 예상치 못한 일이 벌어졌죠."},
        {"scene_number": 4, "narration": "이제는 모든 게 달라지기 시작했어요."},
        {"scene_number": 5, "narration": "바로 이 순간, 결정적인 선택을 해야 했죠."},
        {"scene_number": 6, "narration": "여러분, 결과가 어땠을까요?"},
        {"scene_number": 7, "narration": "이제는 모든 게 끝났어요. 새로운 시작이네요."}
    ]
    
    print(f"\n📖 원본 스토리 (한국어, {len(sample_story)}씬):")
    for scene in sample_story:
        print(f"   씬 {scene['scene_number']}: {scene['narration']}")
    
    # 언어별 번역
    results = {}
    
    for lang_code in ["en", "ja", "zh"]:
        lang_name = translator.LANGUAGES[lang_code]['native']
        flag = _get_flag(lang_code)
        
        print(f"\n{flag} {lang_name} 번역:")
        
        translated_story = translator.translate_story_batch(sample_story, lang_code)
        
        for scene in translated_story:
            print(f"   씬 {scene['scene_number']}: {scene['narration']}")
        
        results[lang_code] = translated_story
    
    # 검증
    all_success = all(
        len(story) == len(sample_story)
        for story in results.values()
    )
    
    print(f"\n✅ 배치 번역 검증: {'통과' if all_success else '실패'}")
    
    return all_success


def test_translation_quality(translator):
    """번역 품질 평가"""
    print("\n" + "=" * 60)
    print("⭐ 테스트 3: 번역 품질 평가")
    print("=" * 60)
    
    test_text = "여러분, 이건 정말 믿기 힘든 이야기예요."
    
    print(f"\n📝 테스트 텍스트: {test_text}")
    print(f"📏 원문 길이: {len(test_text)}자")
    
    quality_checks = {}
    
    for lang_code in ["en", "ja", "zh"]:
        lang_name = translator.LANGUAGES[lang_code]['native']
        
        translated = translator.translate_narration(test_text, lang_code)
        
        if not translated:
            print(f"\n❌ {lang_name}: 번역 실패")
            quality_checks[lang_code] = False
            continue
        
        print(f"\n🌐 {lang_name}:")
        print(f"   번역: {translated}")
        print(f"   길이: {len(translated)}자")
        
        # 품질 체크
        checks = {
            "번역 성공": translated is not None,
            "빈 문자열 아님": len(translated.strip()) > 0,
            "적절한 길이": 10 <= len(translated) <= 80,
            "따옴표 없음": not any(q in translated for q in ['"', "'", '"', '"', ''', '''])
        }
        
        print(f"   품질 체크:")
        for check, passed in checks.items():
            status = "✅" if passed else "❌"
            print(f"      {status} {check}")
        
        quality_checks[lang_code] = all(checks.values())
    
    all_passed = all(quality_checks.values())
    
    print(f"\n📊 품질 평가: {'통과' if all_passed else '일부 실패'}")
    
    return all_passed


def test_multilang_pipeline():
    """전체 다국어 파이프라인 시뮬레이션"""
    print("\n" + "=" * 60)
    print("🎬 테스트 4: 다국어 쇼츠 생성 파이프라인")
    print("=" * 60)
    
    translator = MultiLangTranslator()
    
    if not translator.enabled:
        print("❌ Ollama 미실행")
        return False
    
    # 시뮬레이션: 한국어 쇼츠 생성 후 다국어 버전 생성
    print("\n📋 시나리오: 한국어 쇼츠를 3개 언어로 확장")
    
    # 1단계: 한국어 쇼츠
    korean_shorts = {
        "title": "우주 비행사의 모험",
        "scenes": [
            {"scene_number": 1, "narration": "오늘은 특별한 우주 미션이 시작되는 날이에요."},
            {"scene_number": 2, "narration": "그런데 예상치 못한 문제가 발생했죠."},
            {"scene_number": 3, "narration": "이제 모든 게 달라졌어요. 새로운 도전이 시작됐죠."}
        ]
    }
    
    print(f"\n🇰🇷 원본 (한국어):")
    print(f"   제목: {korean_shorts['title']}")
    for scene in korean_shorts['scenes']:
        print(f"   씬 {scene['scene_number']}: {scene['narration']}")
    
    # 2단계: 다국어 버전 생성
    multilang_versions = {}
    
    for lang_code, lang_info in [("en", "영어"), ("ja", "일본어"), ("zh", "중국어")]:
        print(f"\n{_get_flag(lang_code)} {lang_info} 버전 생성 중...")
        
        translated_scenes = translator.translate_story_batch(
            korean_shorts['scenes'], 
            lang_code
        )
        
        multilang_versions[lang_code] = {
            "title": korean_shorts['title'],  # 제목은 원본 유지 (또는 별도 번역)
            "language": lang_code,
            "scenes": translated_scenes
        }
        
        print(f"   ✅ {len(translated_scenes)}개 씬 번역 완료")
    
    # 결과 요약
    print(f"\n📊 다국어 쇼츠 생성 완료:")
    print(f"   원본: 한국어 (3씬)")
    print(f"   생성: {len(multilang_versions)}개 언어 버전")
    
    for lang_code, version in multilang_versions.items():
        lang_name = translator.LANGUAGES[lang_code]['name']
        print(f"   - {lang_name}: {len(version['scenes'])}씬")
    
    return len(multilang_versions) == 3


def _get_flag(lang_code):
    """언어 코드에 해당하는 국기 이모지 반환"""
    flags = {
        "ko": "🇰🇷",
        "en": "🇺🇸",
        "ja": "🇯🇵",
        "zh": "🇨🇳",
        "es": "🇪🇸"
    }
    return flags.get(lang_code, "🌐")


if __name__ == "__main__":
    print("\n" + "🚀 다국어 쇼츠 생성 파이프라인 테스트 시작\n")
    
    # 1. 시스템 상태 확인
    translator = test_translator_availability()
    
    if not translator:
        print("\n" + "="*60)
        print("❌ Ollama 서비스가 실행 중이지 않습니다.")
        print("="*60)
        print("\n💡 시작 방법:")
        print("   터미널에서: ollama serve")
        sys.exit(1)
    
    # 2~5. 테스트 실행
    test_results = {
        "단일 번역": test_single_translation(translator),
        "배치 번역": test_batch_translation(translator),
        "품질 평가": test_translation_quality(translator),
        "파이프라인": test_multilang_pipeline()
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
        print("   - TTS API 연동 (다국어 음성 합성)")
        print("   - 자막 자동 생성")
        print("   - 글로벌 배포 준비")
    else:
        print("\n⚠️  일부 테스트 실패")
        print("   - 번역 품질 개선")
        print("   - 프롬프트 최적화")
    
    print()
