#!/usr/bin/env python3
"""
🎬 전체 워크플로우 파이프라인 서비스
URL → 완성된 쇼츠까지 완전 자동화
"""

import asyncio
from pathlib import Path
from typing import Optional, Dict, List
from loguru import logger
import time
import json
from enum import Enum

# 모델 임포트
import sys
sys.path.append(str(Path(__file__).parent.parent))

from models.image_generator import ImageGenerator, get_character_prompt
from models.tts_generator import TTSGenerator, get_voice_settings
from models.video_generator import VideoGenerator, get_character_action
from models.script_generator import ScriptGenerator
from services.render_service import RenderService


class JobStatus(Enum):
    """작업 상태"""
    PENDING = "pending"
    CRAWLING = "crawling"
    GENERATING_SCRIPT = "generating_script"
    GENERATING_IMAGES = "generating_images"
    GENERATING_VOICE = "generating_voice"
    GENERATING_VIDEO = "generating_video"
    RENDERING = "rendering"
    COMPLETED = "completed"
    FAILED = "failed"


class PipelineService:
    """완전 자동화 쇼츠 생성 파이프라인"""
    
    def __init__(
        self,
        models_dir: Path,
        output_dir: Path,
        device: str = "cuda"
    ):
        self.models_dir = models_dir
        self.output_dir = output_dir
        self.device = device
        
        # 디렉토리 생성
        self.videos_dir = output_dir / "videos"
        self.images_dir = output_dir / "images"
        self.audio_dir = output_dir / "audio"
        self.temp_dir = output_dir / "temp"
        
        for d in [self.videos_dir, self.images_dir, self.audio_dir, self.temp_dir]:
            d.mkdir(parents=True, exist_ok=True)
        
        # 모델 인스턴스 (지연 로드)
        self.image_gen = None
        self.tts_gen = None
        self.video_gen = None
        self.script_gen = None
        self.render_service = None
        
        # 작업 상태 추적
        self.jobs: Dict[str, Dict] = {}
    
    def _initialize_generators(self):
        """모델 초기화 (최초 1회)"""
        if self.image_gen is None:
            logger.info("🔧 Initializing AI models...")
            
            # 이미지 생성기
            self.image_gen = ImageGenerator(self.models_dir, self.device)
            logger.info("   Loading Stable Diffusion XL...")
            self.image_gen.load_model()
            
            # TTS 생성기
            self.tts_gen = TTSGenerator(self.models_dir, self.device)
            logger.info("   Loading Coqui TTS...")
            self.tts_gen.load_model()
            
            # 비디오 생성기
            self.video_gen = VideoGenerator(self.models_dir, self.device)
            logger.info("   Loading AnimateDiff...")
            self.video_gen.load_model()
            
            # 스크립트 생성기
            self.script_gen = ScriptGenerator()
            
            # 렌더링 서비스
            self.render_service = RenderService(self.temp_dir)
            
            logger.info("✅ All models loaded successfully!")
    
    async def generate_shorts(
        self,
        job_id: str,
        request: Dict
    ) -> Dict:
        """쇼츠 생성 메인 파이프라인
        
        Args:
            job_id: 작업 ID
            request: {
                "url": "...",  # 선택
                "product_info": {...},  # 또는 직접 제공
                "character_id": "executive-fox",
                "num_scenes": 5,
                "duration": 15,
                "aspect_ratio": "9:16"
            }
        
        Returns:
            결과 딕셔너리
        """
        try:
            # 작업 초기화
            self.jobs[job_id] = {
                "status": JobStatus.PENDING.value,
                "progress": 0,
                "message": "작업 시작",
                "started_at": time.time(),
                "output_path": None,
                "error": None
            }
            
            logger.info(f"🎬 Starting shorts generation: {job_id}")
            
            # 모델 초기화
            self._initialize_generators()
            
            # 파라미터 추출
            character_id = request.get("character_id", "executive-fox")
            num_scenes = request.get("num_scenes", 5)
            duration = request.get("duration", 15)
            aspect_ratio = request.get("aspect_ratio", "9:16")
            
            # 1. 크롤링 (선택)
            if "url" in request:
                await self._update_status(job_id, JobStatus.CRAWLING, 5)
                product_info = await self._crawl_product(request["url"])
            else:
                product_info = request.get("product_info", {
                    "title": "프리미엄 제품",
                    "description": "최고의 품질",
                    "features": ["고품질", "저렴한 가격"],
                    "price": "99,000원"
                })
            
            # 2. 스크립트 생성
            await self._update_status(job_id, JobStatus.GENERATING_SCRIPT, 15)
            script = await self._generate_script(
                product_info, character_id, num_scenes, duration
            )
            
            scenes = script["scenes"]
            logger.info(f"   Generated {len(scenes)} scenes")
            
            # 3. 이미지 생성 (병렬)
            await self._update_status(job_id, JobStatus.GENERATING_IMAGES, 30)
            image_paths = await self._generate_images(
                character_id, scenes
            )
            
            # 4. 음성 생성 (병렬)
            await self._update_status(job_id, JobStatus.GENERATING_VOICE, 45)
            audio_paths = await self._generate_voices(
                character_id, scenes
            )
            
            # 5. 비디오 생성
            await self._update_status(job_id, JobStatus.GENERATING_VIDEO, 60)
            video_clips = await self._generate_videos(
                character_id, scenes, image_paths
            )
            
            # 6. 최종 렌더링
            await self._update_status(job_id, JobStatus.RENDERING, 85)
            final_video = await self._render_final_video(
                job_id, scenes, video_clips, audio_paths, aspect_ratio
            )
            
            # 완료
            await self._update_status(job_id, JobStatus.COMPLETED, 100)
            self.jobs[job_id]["output_path"] = final_video
            self.jobs[job_id]["completed_at"] = time.time()
            
            elapsed = time.time() - self.jobs[job_id]["started_at"]
            logger.info(f"✅ Shorts generation completed in {elapsed:.1f}s")
            logger.info(f"   Output: {final_video}")
            
            return {
                "job_id": job_id,
                "status": "completed",
                "output_path": final_video,
                "elapsed_time": elapsed,
                "script": script,
                "metadata": {
                    "character": character_id,
                    "scenes": len(scenes),
                    "duration": sum(s["duration"] for s in scenes)
                }
            }
            
        except Exception as e:
            logger.error(f"❌ Shorts generation failed: {str(e)}")
            await self._update_status(job_id, JobStatus.FAILED, self.jobs[job_id]["progress"])
            self.jobs[job_id]["error"] = str(e)
            raise
    
    async def _update_status(
        self,
        job_id: str,
        status: JobStatus,
        progress: int
    ):
        """작업 상태 업데이트"""
        self.jobs[job_id]["status"] = status.value
        self.jobs[job_id]["progress"] = progress
        self.jobs[job_id]["message"] = self._get_status_message(status)
        logger.info(f"   [{progress}%] {self.jobs[job_id]['message']}")
    
    def _get_status_message(self, status: JobStatus) -> str:
        """상태 메시지"""
        messages = {
            JobStatus.PENDING: "작업 대기 중",
            JobStatus.CRAWLING: "제품 정보 수집 중",
            JobStatus.GENERATING_SCRIPT: "AI 스크립트 생성 중",
            JobStatus.GENERATING_IMAGES: "캐릭터 이미지 생성 중",
            JobStatus.GENERATING_VOICE: "음성 합성 중",
            JobStatus.GENERATING_VIDEO: "비디오 생성 중",
            JobStatus.RENDERING: "최종 렌더링 중",
            JobStatus.COMPLETED: "완료",
            JobStatus.FAILED: "실패"
        }
        return messages.get(status, "처리 중")
    
    async def _crawl_product(self, url: str) -> Dict:
        """제품 정보 크롤링 (간단한 구현)"""
        logger.info(f"   Crawling: {url}")
        
        # TODO: 실제 크롤링 구현
        # 현재는 더미 데이터 반환
        await asyncio.sleep(2)  # 크롤링 시뮬레이션
        
        return {
            "title": "프리미엄 무선 이어폰",
            "description": "최고의 음질과 편안한 착용감",
            "features": ["ANC 노이즈 캔슬링", "30시간 배터리", "IPX7 방수"],
            "price": "149,000원",
            "url": url
        }
    
    async def _generate_script(
        self,
        product_info: Dict,
        character_id: str,
        num_scenes: int,
        duration: int
    ) -> Dict:
        """스크립트 생성"""
        logger.info(f"   Generating script for {character_id}")
        
        # 비동기 실행
        loop = asyncio.get_event_loop()
        script = await loop.run_in_executor(
            None,
            self.script_gen.generate_script,
            product_info,
            character_id,
            num_scenes,
            duration,
            "professional"
        )
        
        return script
    
    async def _generate_images(
        self,
        character_id: str,
        scenes: List[Dict]
    ) -> List[str]:
        """이미지 생성 (각 장면마다)"""
        logger.info(f"   Generating {len(scenes)} images")
        
        image_paths = []
        character_prompt = get_character_prompt(character_id)
        
        # 각 장면의 이미지 생성
        for i, scene in enumerate(scenes):
            # 장면별 프롬프트 (캐릭터 + 제스처)
            action = get_character_action(character_id)
            full_prompt = f"{character_prompt}, {action}"
            
            # 이미지 생성
            loop = asyncio.get_event_loop()
            paths = await loop.run_in_executor(
                None,
                self.image_gen.generate_character,
                f"{character_id}_scene{i+1}",
                full_prompt,
                None,  # negative_prompt
                1,     # num_images
                1024,  # width
                1024,  # height
                25,    # steps
                7.5,   # guidance
                None   # seed
            )
            
            image_paths.extend(paths)
            logger.info(f"      Scene {i+1}: {Path(paths[0]).name}")
        
        return image_paths
    
    async def _generate_voices(
        self,
        character_id: str,
        scenes: List[Dict]
    ) -> List[str]:
        """음성 생성"""
        logger.info(f"   Generating {len(scenes)} voice clips")
        
        audio_paths = []
        voice_settings = get_voice_settings(character_id)
        
        # 각 장면의 음성 생성
        for i, scene in enumerate(scenes):
            text = scene["text"]
            
            # 음성 생성
            output_path = self.audio_dir / f"scene{i+1}_{int(time.time())}.wav"
            
            loop = asyncio.get_event_loop()
            path = await loop.run_in_executor(
                None,
                self.tts_gen.generate_speech,
                text,
                output_path,
                voice_settings.get("language", "ko"),
                voice_settings.get("speaker"),
                voice_settings.get("speed", 1.0),
                voice_settings.get("emotion", "neutral")
            )
            
            audio_paths.append(path)
            logger.info(f"      Scene {i+1}: {Path(path).name}")
        
        return audio_paths
    
    async def _generate_videos(
        self,
        character_id: str,
        scenes: List[Dict],
        image_paths: List[str]
    ) -> List[str]:
        """비디오 클립 생성"""
        logger.info(f"   Generating {len(scenes)} video clips")
        
        video_paths = []
        
        # 각 장면의 비디오 생성
        for i, (scene, image_path) in enumerate(zip(scenes, image_paths)):
            character_prompt = get_character_prompt(character_id)
            action_prompt = get_character_action(character_id)
            duration = scene.get("duration", 3)
            
            # 비디오 생성
            output_path = self.temp_dir / f"clip{i+1}_{int(time.time())}.mp4"
            
            loop = asyncio.get_event_loop()
            path = await loop.run_in_executor(
                None,
                self.video_gen.generate_character_video,
                character_id,
                character_prompt,
                action_prompt,
                duration,
                "9:16",
                output_path
            )
            
            video_paths.append(path)
            logger.info(f"      Scene {i+1}: {Path(path).name}")
        
        return video_paths
    
    async def _render_final_video(
        self,
        job_id: str,
        scenes: List[Dict],
        video_clips: List[str],
        audio_clips: List[str],
        aspect_ratio: str
    ) -> str:
        """최종 비디오 렌더링"""
        logger.info(f"   Rendering final video")
        
        loop = asyncio.get_event_loop()
        
        # 1. 비디오 클립 연결
        logger.info("      Concatenating video clips...")
        concat_video = self.temp_dir / f"concat_{job_id}.mp4"
        concat_video = await loop.run_in_executor(
            None,
            self.render_service.concatenate_videos,
            [Path(v) for v in video_clips],
            concat_video
        )
        
        # 2. 오디오 클립 연결 (간단히 첫 번째만 사용)
        # TODO: 모든 오디오 클립 연결
        main_audio = audio_clips[0] if audio_clips else None
        
        # 3. 오디오-비디오 합성
        if main_audio:
            logger.info("      Merging audio and video...")
            merged_video = self.temp_dir / f"merged_{job_id}.mp4"
            merged_video = await loop.run_in_executor(
                None,
                self.render_service.merge_audio_video,
                Path(concat_video),
                Path(main_audio),
                merged_video
            )
        else:
            merged_video = concat_video
        
        # 4. 자막 추가
        logger.info("      Adding subtitles...")
        subtitles = []
        current_time = 0
        for scene in scenes:
            subtitles.append({
                "text": scene["text"],
                "start": current_time,
                "end": current_time + scene["duration"]
            })
            current_time += scene["duration"]
        
        subtitled_video = self.temp_dir / f"subtitled_{job_id}.mp4"
        subtitled_video = await loop.run_in_executor(
            None,
            self.render_service.add_subtitles,
            Path(merged_video),
            subtitles,
            subtitled_video
        )
        
        # 5. 최종 출력 (9:16 리사이즈)
        logger.info("      Final resizing...")
        final_path = self.videos_dir / f"shorts_{job_id}.mp4"
        final_path = await loop.run_in_executor(
            None,
            self.render_service.resize_for_shorts,
            Path(subtitled_video),
            final_path,
            1080,
            1920
        )
        
        return str(final_path)
    
    def get_job_status(self, job_id: str) -> Optional[Dict]:
        """작업 상태 조회"""
        return self.jobs.get(job_id)
    
    def cleanup_temp_files(self, job_id: str):
        """임시 파일 정리"""
        try:
            # temp 디렉토리의 해당 작업 파일 삭제
            import glob
            temp_files = glob.glob(str(self.temp_dir / f"*{job_id}*"))
            for f in temp_files:
                Path(f).unlink()
            logger.info(f"🗑️ Cleaned up {len(temp_files)} temp files for {job_id}")
        except Exception as e:
            logger.warning(f"⚠️ Cleanup failed: {str(e)}")


# ========== 테스트 코드 ==========
if __name__ == "__main__":
    import asyncio
    
    # 테스트
    models_dir = Path(__file__).parent.parent.parent / "models"
    output_dir = Path(__file__).parent.parent.parent / "output"
    
    pipeline = PipelineService(models_dir, output_dir, device="cuda")
    
    # 간단한 테스트 (실제 생성은 시간이 오래 걸림)
    logger.info("✅ Pipeline service initialized")
    logger.info(f"   Models dir: {models_dir}")
    logger.info(f"   Output dir: {output_dir}")
