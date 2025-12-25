#!/usr/bin/env python3
"""
🎞️ FFmpeg 렌더링 서비스
비디오 + 오디오 합성, 자막 추가, 최종 쇼츠 렌더링
"""

import subprocess
from pathlib import Path
from typing import Optional, List, Dict
from loguru import logger
import time
import json

class RenderService:
    """FFmpeg 기반 비디오 렌더링 서비스"""
    
    def __init__(self, temp_dir: Path):
        self.temp_dir = temp_dir
        self.temp_dir.mkdir(parents=True, exist_ok=True)
        
        # FFmpeg 설치 확인
        if not self._check_ffmpeg():
            logger.error("❌ FFmpeg not found! Please install FFmpeg.")
            raise RuntimeError("FFmpeg not installed")
    
    def _check_ffmpeg(self) -> bool:
        """FFmpeg 설치 확인"""
        try:
            result = subprocess.run(
                ["ffmpeg", "-version"],
                capture_output=True,
                text=True,
                timeout=5
            )
            return result.returncode == 0
        except:
            return False
    
    def merge_audio_video(
        self,
        video_path: Path,
        audio_path: Path,
        output_path: Optional[Path] = None
    ) -> str:
        """비디오와 오디오 합성
        
        Args:
            video_path: 비디오 파일 경로
            audio_path: 오디오 파일 경로
            output_path: 출력 파일 경로
            
        Returns:
            합성된 비디오 파일 경로
        """
        try:
            logger.info(f"🎞️ Merging audio and video")
            logger.info(f"   Video: {video_path.name}")
            logger.info(f"   Audio: {audio_path.name}")
            
            start_time = time.time()
            
            if output_path is None:
                output_path = self.temp_dir / f"merged_{int(time.time())}.mp4"
            
            # FFmpeg 명령어
            cmd = [
                "ffmpeg",
                "-i", str(video_path),
                "-i", str(audio_path),
                "-c:v", "copy",  # 비디오 코덱 복사 (빠름)
                "-c:a", "aac",   # 오디오 AAC 인코딩
                "-b:a", "128k",  # 오디오 비트레이트
                "-shortest",     # 짧은 쪽 길이에 맞춤
                "-y",            # 덮어쓰기
                str(output_path)
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=300  # 5분 타임아웃
            )
            
            if result.returncode != 0:
                logger.error(f"FFmpeg error: {result.stderr}")
                raise RuntimeError("Audio-video merge failed")
            
            merge_time = time.time() - start_time
            logger.info(f"✅ Merged in {merge_time:.1f}s")
            logger.info(f"   Output: {output_path.name}")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ Merge failed: {str(e)}")
            raise
    
    def add_subtitles(
        self,
        video_path: Path,
        subtitles: List[Dict],  # [{"text": "...", "start": 0, "end": 3}, ...]
        output_path: Optional[Path] = None,
        font_size: int = 40,
        font_color: str = "white",
        background: bool = True
    ) -> str:
        """비디오에 자막 추가
        
        Args:
            video_path: 비디오 파일 경로
            subtitles: 자막 리스트 (text, start, end)
            output_path: 출력 경로
            font_size: 폰트 크기
            font_color: 폰트 색상
            background: 반투명 배경 사용
            
        Returns:
            자막이 추가된 비디오 경로
        """
        try:
            logger.info(f"📝 Adding subtitles to video")
            logger.info(f"   Subtitles: {len(subtitles)} segments")
            
            start_time = time.time()
            
            if output_path is None:
                output_path = self.temp_dir / f"subtitled_{int(time.time())}.mp4"
            
            # SRT 파일 생성
            srt_path = self.temp_dir / f"subtitles_{int(time.time())}.srt"
            self._create_srt_file(subtitles, srt_path)
            
            # FFmpeg 자막 필터
            subtitle_filter = f"subtitles={srt_path}:force_style='FontSize={font_size},PrimaryColour={self._color_to_hex(font_color)}'"
            
            if background:
                subtitle_filter += ",BorderStyle=3,BackColour=&H80000000"  # 반투명 검정 배경
            
            cmd = [
                "ffmpeg",
                "-i", str(video_path),
                "-vf", subtitle_filter,
                "-c:a", "copy",  # 오디오 복사
                "-y",
                str(output_path)
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=300
            )
            
            if result.returncode != 0:
                logger.error(f"FFmpeg error: {result.stderr}")
                raise RuntimeError("Subtitle addition failed")
            
            # SRT 파일 삭제
            srt_path.unlink()
            
            sub_time = time.time() - start_time
            logger.info(f"✅ Subtitles added in {sub_time:.1f}s")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ Subtitle addition failed: {str(e)}")
            raise
    
    def add_background_music(
        self,
        video_path: Path,
        bgm_path: Path,
        output_path: Optional[Path] = None,
        bgm_volume: float = 0.2,
        video_volume: float = 1.0
    ) -> str:
        """배경음악 추가
        
        Args:
            video_path: 비디오 파일 경로
            bgm_path: 배경음악 파일 경로
            output_path: 출력 경로
            bgm_volume: 배경음악 볼륨 (0.0-1.0)
            video_volume: 원본 오디오 볼륨 (0.0-1.0)
            
        Returns:
            배경음악이 추가된 비디오 경로
        """
        try:
            logger.info(f"🎵 Adding background music")
            logger.info(f"   BGM: {bgm_path.name}")
            logger.info(f"   BGM volume: {bgm_volume}, Video volume: {video_volume}")
            
            start_time = time.time()
            
            if output_path is None:
                output_path = self.temp_dir / f"with_bgm_{int(time.time())}.mp4"
            
            # 오디오 믹싱
            audio_filter = f"[0:a]volume={video_volume}[a1];[1:a]volume={bgm_volume}[a2];[a1][a2]amix=inputs=2:duration=shortest"
            
            cmd = [
                "ffmpeg",
                "-i", str(video_path),
                "-i", str(bgm_path),
                "-filter_complex", audio_filter,
                "-c:v", "copy",
                "-c:a", "aac",
                "-b:a", "192k",
                "-y",
                str(output_path)
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=300
            )
            
            if result.returncode != 0:
                logger.error(f"FFmpeg error: {result.stderr}")
                raise RuntimeError("BGM addition failed")
            
            bgm_time = time.time() - start_time
            logger.info(f"✅ BGM added in {bgm_time:.1f}s")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ BGM addition failed: {str(e)}")
            raise
    
    def concatenate_videos(
        self,
        video_paths: List[Path],
        output_path: Optional[Path] = None,
        transition: Optional[str] = None  # 'fade', 'wipe', etc.
    ) -> str:
        """여러 비디오 클립 연결
        
        Args:
            video_paths: 비디오 파일 경로 리스트
            output_path: 출력 경로
            transition: 전환 효과
            
        Returns:
            연결된 비디오 경로
        """
        try:
            logger.info(f"🔗 Concatenating {len(video_paths)} videos")
            
            start_time = time.time()
            
            if output_path is None:
                output_path = self.temp_dir / f"concat_{int(time.time())}.mp4"
            
            # concat 파일 생성
            concat_file = self.temp_dir / f"concat_{int(time.time())}.txt"
            with open(concat_file, 'w') as f:
                for video_path in video_paths:
                    f.write(f"file '{video_path.absolute()}'\n")
            
            cmd = [
                "ffmpeg",
                "-f", "concat",
                "-safe", "0",
                "-i", str(concat_file),
                "-c", "copy",
                "-y",
                str(output_path)
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=300
            )
            
            if result.returncode != 0:
                logger.error(f"FFmpeg error: {result.stderr}")
                raise RuntimeError("Video concatenation failed")
            
            # concat 파일 삭제
            concat_file.unlink()
            
            concat_time = time.time() - start_time
            logger.info(f"✅ Videos concatenated in {concat_time:.1f}s")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ Concatenation failed: {str(e)}")
            raise
    
    def resize_for_shorts(
        self,
        video_path: Path,
        output_path: Optional[Path] = None,
        width: int = 1080,
        height: int = 1920  # 9:16
    ) -> str:
        """쇼츠용 해상도로 리사이즈 (9:16)
        
        Args:
            video_path: 비디오 파일 경로
            output_path: 출력 경로
            width: 너비
            height: 높이
            
        Returns:
            리사이즈된 비디오 경로
        """
        try:
            logger.info(f"📐 Resizing to {width}x{height} (9:16 shorts)")
            
            start_time = time.time()
            
            if output_path is None:
                output_path = self.temp_dir / f"shorts_{int(time.time())}.mp4"
            
            cmd = [
                "ffmpeg",
                "-i", str(video_path),
                "-vf", f"scale={width}:{height}:force_original_aspect_ratio=decrease,pad={width}:{height}:(ow-iw)/2:(oh-ih)/2",
                "-c:a", "copy",
                "-y",
                str(output_path)
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=300
            )
            
            if result.returncode != 0:
                logger.error(f"FFmpeg error: {result.stderr}")
                raise RuntimeError("Resize failed")
            
            resize_time = time.time() - start_time
            logger.info(f"✅ Resized in {resize_time:.1f}s")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ Resize failed: {str(e)}")
            raise
    
    def _create_srt_file(self, subtitles: List[Dict], output_path: Path):
        """SRT 자막 파일 생성"""
        with open(output_path, 'w', encoding='utf-8') as f:
            for i, sub in enumerate(subtitles, 1):
                start = self._seconds_to_srt_time(sub["start"])
                end = self._seconds_to_srt_time(sub["end"])
                text = sub["text"]
                
                f.write(f"{i}\n")
                f.write(f"{start} --> {end}\n")
                f.write(f"{text}\n\n")
    
    def _seconds_to_srt_time(self, seconds: float) -> str:
        """초를 SRT 시간 형식으로 변환 (00:00:00,000)"""
        hours = int(seconds // 3600)
        minutes = int((seconds % 3600) // 60)
        secs = int(seconds % 60)
        millis = int((seconds % 1) * 1000)
        return f"{hours:02d}:{minutes:02d}:{secs:02d},{millis:03d}"
    
    def _color_to_hex(self, color: str) -> str:
        """색상 이름을 헥스로 변환"""
        colors = {
            "white": "&HFFFFFF",
            "black": "&H000000",
            "red": "&H0000FF",
            "yellow": "&H00FFFF"
        }
        return colors.get(color.lower(), "&HFFFFFF")


# ========== 테스트 코드 ==========
if __name__ == "__main__":
    # 테스트
    temp_dir = Path(__file__).parent.parent.parent / "output" / "temp"
    
    service = RenderService(temp_dir)
    logger.info("✅ Render service initialized")
    
    # FFmpeg 버전 확인
    result = subprocess.run(["ffmpeg", "-version"], capture_output=True, text=True)
    version_line = result.stdout.split('\n')[0]
    logger.info(f"   {version_line}")
