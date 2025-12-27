#!/usr/bin/env python3
"""
전체 시스템 통합 테스트 (End-to-End Test)
스토리 입력 → 완성 쇼츠까지 전체 파이프라인 검증

테스트 흐름:
1. 사용자 스토리 입력
2. 장르 자동 감지 + 구조 적용
3. 씬별 나레이션 자동 생성
4. (선택) 이미지 생성 (Replicate/Hugging Face)
5. 다국어 번역 (영어/일본어/중국어)
6. 최종 결과물 생성 및 검증
"""

import sys
import os
import time
import logging
from typing import Dict, List

# 모듈 경로 추가
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'ai-backend'))

from genre_detector import GenreDetector
from ollama_narration_generator import OllamaNarrationGenerator
from multilang_translator import MultiLangTranslator

# 로깅 설정
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class ShortsPipeline:
    """쇼츠 생성 전체 파이프라인"""
    
    def __init__(self):
        """초기화 - 모든 모듈 로드"""
        logger.info("=" * 80)
        logger.info("🎬 AI 쇼츠 생성 파이프라인 초기화 중...")
        logger.info("=" * 80)
        
        # 1. 장르 감지기
        logger.info("\n1️⃣ 장르 감지 시스템 로딩...")
        self.genre_detector = GenreDetector()
        logger.info(f"   ✅ 지원 장르: {', '.join(self.genre_detector.GENRE_STRUCTURES.keys())}")
        
        # 2. 나레이션 생성기
        logger.info("\n2️⃣ 나레이션 생성 시스템 로딩...")
        self.narration_gen = OllamaNarrationGenerator()
        logger.info(f"   ✅ Ollama 상태: {'활성화' if self.narration_gen.enabled else '비활성화'}")
        
        # 3. 다국어 번역기
        logger.info("\n3️⃣ 다국어 번역 시스템 로딩...")
        self.translator = MultiLangTranslator()
        logger.info(f"   ✅ 지원 언어: {', '.join(self.translator.LANGUAGES.keys())}")
        
        logger.info("\n" + "=" * 80)
        logger.info("✅ 모든 시스템 준비 완료!")
        logger.info("=" * 80 + "\n")
    
    def generate_shorts(
        self,
        story_input: str,
        scenes_count: int = 7,
        target_languages: List[str] = None
    ) -> Dict:
        """
        전체 쇼츠 생성 프로세스
        
        Args:
            story_input: 사용자 스토리 입력
            scenes_count: 생성할 씬 개수 (기본 7개)
            target_languages: 번역할 언어 리스트 (기본: ['en', 'ja', 'zh'])
        
        Returns:
            최종 쇼츠 데이터 (한국어 + 다국어 버전)
        """
        if target_languages is None:
            target_languages = ['en', 'ja', 'zh']
        
        start_time = time.time()
        result = {
            "story_input": story_input,
            "pipeline_stages": {},
            "korean_version": {},
            "multilang_versions": {},
            "performance": {}
        }
        
        try:
            # ========================================
            # STAGE 1: 장르 감지 및 구조 적용
            # ========================================
            logger.info("\n" + "=" * 80)
            logger.info("📊 STAGE 1: 장르 감지 및 스토리 구조 적용")
            logger.info("=" * 80)
            
            stage1_start = time.time()
            genre_info = self.genre_detector.detect_genre(story_input)
            
            if genre_info:
                logger.info(f"   ✅ 감지된 장르: {genre_info['genre']}")
                logger.info(f"   ✅ 적용 구조: {genre_info['structure']}")
                logger.info(f"   ✅ 톤: {genre_info.get('tone', 'N/A')}")
            else:
                logger.warning("   ⚠️ 장르 감지 실패 - 기본 구조 사용")
                genre_info = {"genre": "동화", "structure": "5막"}
            
            result["pipeline_stages"]["genre_detection"] = genre_info
            result["performance"]["stage1_genre_detection"] = time.time() - stage1_start
            
            # ========================================
            # STAGE 2: 씬 구조화
            # ========================================
            logger.info("\n" + "=" * 80)
            logger.info("🎬 STAGE 2: 씬 구조화 및 배분")
            logger.info("=" * 80)
            
            stage2_start = time.time()
            structure_data = self.genre_detector.apply_genre_structure(
                story_input, 
                scenes_count
            )
            
            if structure_data and "scenes" in structure_data:
                scenes = structure_data["scenes"]
                logger.info(f"   ✅ 총 {len(scenes)}개 씬 생성")
                
                for i, scene in enumerate(scenes, 1):
                    logger.info(f"   씬 {i}: {scene['act_name']} - {scene['mood']} ({scene.get('title', 'N/A')})")
            else:
                logger.error("   ❌ 씬 구조화 실패")
                return None
            
            result["pipeline_stages"]["scene_structure"] = structure_data
            result["performance"]["stage2_scene_structure"] = time.time() - stage2_start
            
            # ========================================
            # STAGE 3: 나레이션 자동 생성 (한국어)
            # ========================================
            logger.info("\n" + "=" * 80)
            logger.info("🎙️ STAGE 3: 나레이션 자동 생성 (한국어)")
            logger.info("=" * 80)
            
            stage3_start = time.time()
            
            if not self.narration_gen.enabled:
                logger.error("   ❌ Ollama 서비스가 실행되지 않았습니다!")
                return None
            
            korean_narrations = []
            for i, scene in enumerate(scenes, 1):
                narration = self.narration_gen.generate_narration(
                    scene_number=i,
                    act_name=scene['act_name'],
                    korean_mood=scene['mood'],
                    scene_title=scene.get('title', ''),
                    user_input=story_input,
                    style=structure_data.get('narration_style', 'curious')
                )
                
                if narration:
                    korean_narrations.append(narration)
                    logger.info(f"   씬 {i}: {narration}")
                else:
                    logger.warning(f"   ⚠️ 씬 {i} 나레이션 생성 실패")
                    korean_narrations.append(f"[씬 {i} 나레이션]")
            
            logger.info(f"\n   ✅ 총 {len(korean_narrations)}/{len(scenes)}개 나레이션 생성 완료")
            
            result["korean_version"] = {
                "genre": genre_info['genre'],
                "structure": structure_data,
                "narrations": korean_narrations,
                "scenes": scenes
            }
            result["performance"]["stage3_narration_generation"] = time.time() - stage3_start
            
            # ========================================
            # STAGE 4: 다국어 번역
            # ========================================
            logger.info("\n" + "=" * 80)
            logger.info("🌍 STAGE 4: 다국어 번역 (영어/일본어/중국어)")
            logger.info("=" * 80)
            
            stage4_start = time.time()
            
            if not self.translator.enabled:
                logger.warning("   ⚠️ 번역 시스템 비활성화 - 한국어 버전만 제공")
            else:
                for lang in target_languages:
                    logger.info(f"\n   [{lang.upper()}] 번역 시작...")
                    
                    lang_start = time.time()
                    translated_narrations = []
                    
                    for i, korean_narration in enumerate(korean_narrations, 1):
                        translated = self.translator.translate_narration(
                            korean_narration,
                            lang
                        )
                        
                        if translated and translated != korean_narration:
                            translated_narrations.append(translated)
                            logger.info(f"      씬 {i}: {translated}")
                        else:
                            logger.warning(f"      ⚠️ 씬 {i} 번역 실패 (원문 사용)")
                            translated_narrations.append(korean_narration)
                    
                    result["multilang_versions"][lang] = {
                        "narrations": translated_narrations,
                        "language_name": self.translator.LANGUAGES[lang]["name"]
                    }
                    
                    lang_time = time.time() - lang_start
                    logger.info(f"   ✅ [{lang.upper()}] 번역 완료 ({lang_time:.2f}초)")
            
            result["performance"]["stage4_translation"] = time.time() - stage4_start
            
            # ========================================
            # 최종 결과 요약
            # ========================================
            total_time = time.time() - start_time
            result["performance"]["total_time"] = total_time
            
            logger.info("\n" + "=" * 80)
            logger.info("🎉 전체 파이프라인 완료!")
            logger.info("=" * 80)
            logger.info(f"총 소요 시간: {total_time:.2f}초")
            logger.info(f"  - 장르 감지: {result['performance']['stage1_genre_detection']:.2f}초")
            logger.info(f"  - 씬 구조화: {result['performance']['stage2_scene_structure']:.2f}초")
            logger.info(f"  - 나레이션 생성: {result['performance']['stage3_narration_generation']:.2f}초")
            logger.info(f"  - 다국어 번역: {result['performance']['stage4_translation']:.2f}초")
            logger.info(f"\n생성된 버전:")
            logger.info(f"  - 한국어: ✅")
            for lang in result["multilang_versions"].keys():
                logger.info(f"  - {self.translator.LANGUAGES[lang]['name']}: ✅")
            logger.info("=" * 80 + "\n")
            
            return result
            
        except Exception as e:
            logger.error(f"❌ 파이프라인 실행 중 오류 발생: {e}")
            import traceback
            traceback.print_exc()
            return None


def run_demo_tests():
    """데모 테스트 실행"""
    
    # 파이프라인 초기화
    pipeline = ShortsPipeline()
    
    # 테스트 스토리 목록
    test_stories = [
        {
            "title": "테스트 1: SF 우주 모험",
            "story": "우주 비행사의 모험",
            "scenes": 7,
            "languages": ["en", "ja", "zh"]
        },
        {
            "title": "테스트 2: 로맨스",
            "story": "짝사랑하던 그녀와의 첫 데이트",
            "scenes": 5,
            "languages": ["en"]
        },
        {
            "title": "테스트 3: 공포",
            "story": "한밤중 폐가에서 들리는 괴상한 소리",
            "scenes": 6,
            "languages": ["ja"]
        }
    ]
    
    logger.info("\n" + "🔥" * 40)
    logger.info("전체 시스템 통합 테스트 시작")
    logger.info("🔥" * 40 + "\n")
    
    results = []
    
    for i, test in enumerate(test_stories, 1):
        logger.info("\n" + "=" * 80)
        logger.info(f"🎬 {test['title']}")
        logger.info("=" * 80)
        
        result = pipeline.generate_shorts(
            story_input=test['story'],
            scenes_count=test['scenes'],
            target_languages=test['languages']
        )
        
        if result:
            results.append({
                "test": test['title'],
                "success": True,
                "result": result
            })
            logger.info(f"✅ {test['title']} - 성공")
        else:
            results.append({
                "test": test['title'],
                "success": False
            })
            logger.error(f"❌ {test['title']} - 실패")
        
        # 테스트 간 대기 시간 (서버 부하 방지)
        if i < len(test_stories):
            logger.info("\n⏳ 다음 테스트까지 3초 대기...\n")
            time.sleep(3)
    
    # 최종 결과 요약
    logger.info("\n" + "=" * 80)
    logger.info("📊 전체 테스트 결과 요약")
    logger.info("=" * 80)
    
    success_count = sum(1 for r in results if r['success'])
    total_count = len(results)
    
    logger.info(f"총 테스트: {total_count}개")
    logger.info(f"성공: {success_count}개")
    logger.info(f"실패: {total_count - success_count}개")
    logger.info(f"성공률: {(success_count/total_count*100):.1f}%")
    
    logger.info("\n상세 결과:")
    for r in results:
        status = "✅" if r['success'] else "❌"
        logger.info(f"  {status} {r['test']}")
        
        if r['success'] and 'result' in r:
            perf = r['result']['performance']
            logger.info(f"     ⏱️ 총 소요 시간: {perf['total_time']:.2f}초")
    
    logger.info("=" * 80 + "\n")
    
    return results


if __name__ == "__main__":
    # 데모 테스트 실행
    results = run_demo_tests()
    
    # 종료 코드 반환
    success_count = sum(1 for r in results if r['success'])
    exit_code = 0 if success_count == len(results) else 1
    
    logger.info(f"🏁 테스트 종료 (종료 코드: {exit_code})")
    sys.exit(exit_code)
