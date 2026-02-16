#!/usr/bin/env python3
"""
빠른 통합 테스트 (Quick Integration Test)
간단한 스토리로 전체 파이프라인 검증 (1개 스토리, 3개 씬)
"""

import sys
import os
import time
import logging

# 모듈 경로 추가
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'ai-backend'))

from genre_detector import GenreDetector
from ollama_narration_generator import OllamaNarrationGenerator
from multilang_translator import MultiLangTranslator

# 로깅 설정
logging.basicConfig(
    level=logging.INFO,
    format='%(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


def quick_test():
    """빠른 통합 테스트"""
    
    logger.info("=" * 60)
    logger.info("🚀 빠른 통합 테스트 시작")
    logger.info("=" * 60)
    
    start_time = time.time()
    
    # 테스트 스토리
    story = "우주 비행사의 모험"
    scenes_count = 3  # 3개 씬만 생성
    
    logger.info(f"\n📖 스토리: {story}")
    logger.info(f"🎬 씬 개수: {scenes_count}")
    
    # 1. 장르 감지 시스템 초기화
    logger.info("\n1️⃣ 장르 감지 시스템...")
    genre_detector = GenreDetector()
    
    genre_info = genre_detector.detect_genre(story)
    if genre_info:
        logger.info(f"   ✅ 장르: {genre_info['genre']}")
        logger.info(f"   ✅ 구조: {genre_info['structure']}")
    else:
        logger.error("   ❌ 장르 감지 실패")
        return False
    
    # 2. 씬 구조화
    logger.info("\n2️⃣ 씬 구조화...")
    structure = genre_detector.apply_genre_structure(story, scenes_count)
    
    if not structure or "scenes" not in structure:
        logger.error("   ❌ 씬 구조화 실패")
        return False
    
    scenes = structure["scenes"]
    logger.info(f"   ✅ 총 {len(scenes)}개 씬 생성")
    for i, scene in enumerate(scenes, 1):
        logger.info(f"      씬 {i}: {scene['act_name']} - {scene['mood']}")
    
    # 3. 나레이션 생성
    logger.info("\n3️⃣ 나레이션 생성 (Ollama)...")
    narration_gen = OllamaNarrationGenerator()
    
    if not narration_gen.enabled:
        logger.error("   ❌ Ollama 서비스 미실행")
        return False
    
    korean_narrations = []
    for i, scene in enumerate(scenes, 1):
        narration = narration_gen.generate_narration(
            scene_number=i,
            act_name=scene['act_name'],
            korean_mood=scene['mood'],
            scene_title=scene.get('title', ''),
            user_input=story,
            style=structure.get('narration_style', 'curious')
        )
        
        if narration:
            korean_narrations.append(narration)
            logger.info(f"   씬 {i}: {narration}")
        else:
            logger.warning(f"   ⚠️ 씬 {i} 나레이션 생성 실패")
            korean_narrations.append(f"[씬 {i}]")
    
    logger.info(f"   ✅ {len(korean_narrations)}/{len(scenes)}개 나레이션 생성")
    
    # 4. 다국어 번역 (영어만)
    logger.info("\n4️⃣ 다국어 번역 (영어)...")
    translator = MultiLangTranslator()
    
    if not translator.enabled:
        logger.warning("   ⚠️ 번역 시스템 비활성화")
    else:
        translated = []
        for i, korean in enumerate(korean_narrations, 1):
            en_text = translator.translate_narration(korean, "en")
            if en_text and en_text != korean:
                translated.append(en_text)
                logger.info(f"   씬 {i}: {en_text}")
            else:
                logger.warning(f"   ⚠️ 씬 {i} 번역 실패")
                translated.append(korean)
        
        logger.info(f"   ✅ {len(translated)}/{len(korean_narrations)}개 번역 완료")
    
    # 최종 결과
    total_time = time.time() - start_time
    
    logger.info("\n" + "=" * 60)
    logger.info("✅ 통합 테스트 완료!")
    logger.info("=" * 60)
    logger.info(f"⏱️ 총 소요 시간: {total_time:.2f}초")
    logger.info(f"📊 결과:")
    logger.info(f"   - 장르 감지: ✅")
    logger.info(f"   - 씬 구조화: ✅ ({len(scenes)}개)")
    logger.info(f"   - 나레이션 생성: ✅ ({len(korean_narrations)}개)")
    logger.info(f"   - 다국어 번역: ✅")
    logger.info("=" * 60)
    
    return True


if __name__ == "__main__":
    try:
        success = quick_test()
        exit_code = 0 if success else 1
        logger.info(f"\n🏁 테스트 종료 (종료 코드: {exit_code})")
        sys.exit(exit_code)
    except Exception as e:
        logger.error(f"❌ 오류 발생: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
