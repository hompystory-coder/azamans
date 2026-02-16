#!/usr/bin/env python3
"""
Hugging Face API 테스트 스크립트
완전 무료 이미지 생성 기능을 테스트합니다.
"""

import os
import sys
from pathlib import Path

# 프로젝트 루트 경로 추가
project_root = Path(__file__).parent / "ai-backend"
sys.path.insert(0, str(project_root))

from huggingface_client import HuggingFaceClient

def test_huggingface_image_generation():
    """Hugging Face API를 사용한 이미지 생성 테스트"""
    
    print("=" * 60)
    print("🤗 Hugging Face API 이미지 생성 테스트")
    print("=" * 60)
    
    # API 토큰 확인
    api_token = os.getenv("HF_TOKEN") or os.getenv("HUGGINGFACE_TOKEN")
    if not api_token:
        print("\n❌ 오류: HF_TOKEN 환경 변수가 설정되지 않았습니다.")
        print("\n📝 설정 방법:")
        print("   1. https://huggingface.co 에서 회원가입")
        print("   2. https://huggingface.co/settings/tokens 에서 토큰 발급")
        print("   3. 다음 명령어로 토큰 설정:")
        print("      export HF_TOKEN='hf_...'")
        print("\n또는 직접 입력:")
        token_input = input("   Hugging Face Token을 입력하세요 (Enter로 건너뛰기): ").strip()
        if token_input:
            os.environ["HF_TOKEN"] = token_input
            api_token = token_input
        else:
            print("\n⏭️  테스트를 건너뜁니다.")
            return False
    
    print(f"\n✅ API Token 확인됨: {api_token[:10]}...")
    
    # HuggingFaceClient 초기화
    print("\n🔧 Hugging Face 클라이언트 초기화 중...")
    try:
        client = HuggingFaceClient(api_token=api_token)
        if not client.enabled:
            print("❌ 클라이언트 초기화 실패")
            return False
        print("✅ 클라이언트 초기화 완료")
    except Exception as e:
        print(f"❌ 클라이언트 초기화 실패: {e}")
        return False
    
    # 테스트 프롬프트
    test_prompts = [
        {
            "prompt": "A magical forest with glowing mushrooms at night, fantasy art style, detailed",
            "model": "sdxl",
            "description": "마법의 숲 (SDXL)",
            "save_path": "/tmp/hf_test_forest.png"
        },
        # {
        #     "prompt": "A cute robot playing with a kitten, cartoon style, colorful",
        #     "model": "sd15",
        #     "description": "귀여운 로봇 (SD 1.5)",
        #     "save_path": "/tmp/hf_test_robot.png"
        # }
    ]
    
    results = []
    
    for idx, test in enumerate(test_prompts, 1):
        print(f"\n{'='*60}")
        print(f"🎨 테스트 {idx}/{len(test_prompts)}: {test['description']}")
        print(f"{'='*60}")
        print(f"📝 프롬프트: {test['prompt']}")
        print(f"🤖 모델: {test['model'].upper()}")
        print(f"💾 저장 경로: {test['save_path']}")
        
        try:
            print("\n⏳ 이미지 생성 중... (대기열 방식, 1~5분 소요 가능)")
            print("💡 Hugging Face는 완전 무료이지만 대기 시간이 있습니다.")
            
            success = client.generate_and_save(
                prompt=test['prompt'],
                save_path=test['save_path'],
                model=test['model']
            )
            
            if success:
                print(f"\n✅ 이미지 생성 및 저장 성공!")
                print(f"📁 파일: {test['save_path']}")
                
                # 파일 크기 확인
                file_size = os.path.getsize(test['save_path'])
                print(f"📊 파일 크기: {file_size / 1024:.1f} KB")
                
                results.append({
                    "test": test['description'],
                    "model": test['model'],
                    "path": test['save_path'],
                    "size_kb": file_size / 1024,
                    "status": "success"
                })
            else:
                print(f"\n❌ 이미지 생성 실패")
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
            print(f"   📁 {result['path']}")
            print(f"   📊 {result['size_kb']:.1f} KB")
        elif result["status"] == "error":
            print(f"   ⚠️  오류: {result.get('error', 'Unknown error')}")
    
    print(f"\n📈 성공률: {success_count}/{total_count} ({success_count/total_count*100:.0f}%)")
    
    if success_count == total_count:
        print("\n🎉 모든 테스트 성공!")
        print("\n💡 장점:")
        print("   • 완전 무료 (무제한)")
        print("   • 계정만 있으면 사용 가능")
        print("   • 다양한 모델 지원")
        print("\n⚠️  단점:")
        print("   • 대기 시간 1~5분")
        print("   • 품질이 Replicate보다 낮을 수 있음")
        return True
    elif success_count > 0:
        print("\n⚠️  일부 테스트 실패")
        return True
    else:
        print("\n❌ 모든 테스트 실패")
        return False


def test_huggingface_batch_generation():
    """Hugging Face API 배치 생성 테스트 (선택적)"""
    
    print("\n" + "="*60)
    print("📦 Hugging Face API 배치 생성 테스트")
    print("="*60)
    
    # API 토큰 확인
    api_token = os.getenv("HF_TOKEN") or os.getenv("HUGGINGFACE_TOKEN")
    if not api_token:
        print("❌ API 토큰이 설정되지 않았습니다. 배치 생성 테스트를 건너뜁니다.")
        return False
    
    skip = input("\n배치 생성 테스트를 진행하시겠습니까? (이미지 3장, 약 5~15분 소요) (y/N): ").strip().lower()
    if skip != 'y':
        print("⏭️  배치 생성 테스트를 건너뜁니다.")
        return False
    
    print(f"\n✅ API Token 확인됨")
    
    # HuggingFaceClient 초기화
    print("\n🔧 Hugging Face 클라이언트 초기화 중...")
    try:
        client = HuggingFaceClient(api_token=api_token)
        if not client.enabled:
            print("❌ 클라이언트 초기화 실패")
            return False
        print("✅ 클라이언트 초기화 완료")
    except Exception as e:
        print(f"❌ 클라이언트 초기화 실패: {e}")
        return False
    
    # 배치 프롬프트
    batch_prompts = [
        "A sunrise over mountains, cinematic",
        "A cozy coffee shop, warm lighting",
        "A futuristic city at night, neon lights"
    ]
    
    print(f"\n📦 배치 생성 시작 ({len(batch_prompts)}개 이미지)")
    for i, prompt in enumerate(batch_prompts, 1):
        print(f"   {i}. {prompt}")
    
    try:
        print("\n⏳ 배치 생성 중... (약 5~15분 소요)")
        batch_results = client.generate_batch(
            prompts=batch_prompts,
            model="sdxl",
            max_wait_per_image=300
        )
        
        success_count = 0
        for i, image_bytes in enumerate(batch_results, 1):
            if image_bytes:
                save_path = f"/tmp/hf_batch_{i}.png"
                client.save_image(image_bytes, save_path)
                file_size = os.path.getsize(save_path)
                print(f"✅ 이미지 {i}: {save_path} ({file_size/1024:.1f} KB)")
                success_count += 1
            else:
                print(f"❌ 이미지 {i}: 생성 실패")
        
        print(f"\n📈 배치 생성 결과: {success_count}/{len(batch_prompts)} 성공")
        
        if success_count == len(batch_prompts):
            print("🎉 배치 생성 완료!")
            return True
        else:
            print("⚠️  일부 이미지 생성 실패")
            return False
            
    except Exception as e:
        print(f"\n❌ 오류 발생: {e}")
        return False


if __name__ == "__main__":
    print("\n" + "🚀 Hugging Face API 통합 테스트 시작\n")
    
    # 단일 이미지 생성 테스트
    image_success = test_huggingface_image_generation()
    
    # 배치 생성 테스트 (선택적)
    if image_success:
        test_huggingface_batch_generation()
    
    print("\n" + "="*60)
    print("✅ 테스트 완료")
    print("="*60)
    print("\n💡 다음 단계:")
    print("   - 생성된 이미지 파일을 확인하세요 (/tmp/hf_*.png)")
    print("   - Replicate vs Hugging Face 비교")
    print("     * Replicate: 빠름 ($5 무료 크레딧), 품질 최고")
    print("     * Hugging Face: 느림 (완전 무료), 품질 중간")
    print("   - 개발 단계: Hugging Face 추천")
    print("   - 프로덕션: Replicate 추천")
    print()
