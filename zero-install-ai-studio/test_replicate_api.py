#!/usr/bin/env python3
"""
Replicate API 테스트 스크립트
이미지 생성 기능을 테스트합니다.
"""

import os
import sys
from pathlib import Path

# 프로젝트 루트 경로 추가
project_root = Path(__file__).parent / "ai-backend"
sys.path.insert(0, str(project_root))

from replicate_client import ReplicateClient

def test_replicate_image_generation():
    """Replicate API를 사용한 이미지 생성 테스트"""
    
    print("=" * 60)
    print("🎨 Replicate API 이미지 생성 테스트")
    print("=" * 60)
    
    # API 토큰 확인
    api_token = os.getenv("REPLICATE_API_TOKEN")
    if not api_token:
        print("\n❌ 오류: REPLICATE_API_TOKEN 환경 변수가 설정되지 않았습니다.")
        print("\n📝 설정 방법:")
        print("   1. https://replicate.com 에서 회원가입")
        print("   2. API 토큰 발급 (초기 $5 무료 크레딧)")
        print("   3. 다음 명령어로 토큰 설정:")
        print("      export REPLICATE_API_TOKEN='r8_...'")
        print("\n또는 직접 입력:")
        token_input = input("   Replicate API Token을 입력하세요 (Enter로 건너뛰기): ").strip()
        if token_input:
            os.environ["REPLICATE_API_TOKEN"] = token_input
            api_token = token_input
        else:
            print("\n⏭️  테스트를 건너뜁니다.")
            return False
    
    print(f"\n✅ API Token 확인됨: {api_token[:10]}...")
    
    # ReplicateClient 초기화
    print("\n🔧 Replicate 클라이언트 초기화 중...")
    try:
        client = ReplicateClient(api_token=api_token)
        print("✅ 클라이언트 초기화 완료")
    except Exception as e:
        print(f"❌ 클라이언트 초기화 실패: {e}")
        return False
    
    # 테스트 프롬프트
    test_prompts = [
        {
            "prompt": "A magical forest with glowing mushrooms at night, fantasy art style, detailed, 4k",
            "model": "sdxl",
            "description": "마법의 숲 (SDXL)"
        },
        # {
        #     "prompt": "A cute robot playing with a kitten, cartoon style, colorful, happy mood",
        #     "model": "flux",
        #     "description": "귀여운 로봇 (FLUX)"
        # }
    ]
    
    results = []
    
    for idx, test in enumerate(test_prompts, 1):
        print(f"\n{'='*60}")
        print(f"🎨 테스트 {idx}/{len(test_prompts)}: {test['description']}")
        print(f"{'='*60}")
        print(f"📝 프롬프트: {test['prompt']}")
        print(f"🤖 모델: {test['model'].upper()}")
        
        try:
            print("\n⏳ 이미지 생성 중... (약 30-60초 소요)")
            
            if test['model'] == 'sdxl':
                image_url = client.generate_image_sdxl(
                    prompt=test['prompt'],
                    negative_prompt="blurry, low quality, distorted",
                    width=1024,
                    height=1024
                )
            elif test['model'] == 'flux':
                image_url = client.generate_image_flux(
                    prompt=test['prompt'],
                    width=1024,
                    height=1024
                )
            else:
                print(f"❌ 알 수 없는 모델: {test['model']}")
                continue
            
            if image_url:
                print(f"\n✅ 이미지 생성 성공!")
                print(f"🔗 URL: {image_url}")
                results.append({
                    "test": test['description'],
                    "model": test['model'],
                    "url": image_url,
                    "status": "success"
                })
            else:
                print(f"\n❌ 이미지 생성 실패: URL이 반환되지 않았습니다.")
                results.append({
                    "test": test['description'],
                    "model": test['model'],
                    "status": "failed"
                })
                
        except Exception as e:
            print(f"\n❌ 오류 발생: {e}")
            results.append({
                "test": test['description'],
                "model": test['model'],
                "status": "error",
                "error": str(e)
            })
    
    # 결과 요약
    print("\n" + "="*60)
    print("📊 테스트 결과 요약")
    print("="*60)
    
    success_count = sum(1 for r in results if r["status"] == "success")
    total_count = len(results)
    
    for idx, result in enumerate(results, 1):
        status_icon = "✅" if result["status"] == "success" else "❌"
        print(f"\n{status_icon} 테스트 {idx}: {result['test']} ({result['model'].upper()})")
        if result["status"] == "success":
            print(f"   🔗 {result['url']}")
        elif result["status"] == "error":
            print(f"   ⚠️  오류: {result.get('error', 'Unknown error')}")
    
    print(f"\n📈 성공률: {success_count}/{total_count} ({success_count/total_count*100:.0f}%)")
    
    if success_count == total_count:
        print("\n🎉 모든 테스트 성공!")
        return True
    elif success_count > 0:
        print("\n⚠️  일부 테스트 실패")
        return True
    else:
        print("\n❌ 모든 테스트 실패")
        return False


def test_replicate_video_generation():
    """Replicate API를 사용한 영상 생성 테스트 (선택적)"""
    
    print("\n" + "="*60)
    print("🎬 Replicate API 영상 생성 테스트")
    print("="*60)
    
    # API 토큰 확인
    api_token = os.getenv("REPLICATE_API_TOKEN")
    if not api_token:
        print("❌ API 토큰이 설정되지 않았습니다. 영상 생성 테스트를 건너뜁니다.")
        return False
    
    skip = input("\n영상 생성 테스트를 진행하시겠습니까? (y/N): ").strip().lower()
    if skip != 'y':
        print("⏭️  영상 생성 테스트를 건너뜁니다.")
        return False
    
    print(f"\n✅ API Token 확인됨")
    
    # ReplicateClient 초기화
    print("\n🔧 Replicate 클라이언트 초기화 중...")
    try:
        client = ReplicateClient(api_token=api_token)
        print("✅ 클라이언트 초기화 완료")
    except Exception as e:
        print(f"❌ 클라이언트 초기화 실패: {e}")
        return False
    
    # 테스트 프롬프트
    print("\n🎬 영상 생성 테스트")
    print("📝 프롬프트: A beautiful sunset over the ocean, waves crashing, cinematic")
    
    try:
        print("\n⏳ 영상 생성 중... (약 2-5분 소요)")
        video_url = client.generate_video(
            prompt="A beautiful sunset over the ocean, waves crashing, cinematic",
            duration=3
        )
        
        if video_url:
            print(f"\n✅ 영상 생성 성공!")
            print(f"🔗 URL: {video_url}")
            return True
        else:
            print(f"\n❌ 영상 생성 실패")
            return False
            
    except Exception as e:
        print(f"\n❌ 오류 발생: {e}")
        return False


if __name__ == "__main__":
    print("\n" + "🚀 Replicate API 통합 테스트 시작\n")
    
    # 이미지 생성 테스트
    image_success = test_replicate_image_generation()
    
    # 영상 생성 테스트 (선택적)
    if image_success:
        test_replicate_video_generation()
    
    print("\n" + "="*60)
    print("✅ 테스트 완료")
    print("="*60)
    print("\n💡 다음 단계:")
    print("   - 생성된 이미지 URL을 확인하세요")
    print("   - 쇼츠 생성 파이프라인에 통합하세요")
    print("   - 비용 모니터링: https://replicate.com/account/billing")
    print()
