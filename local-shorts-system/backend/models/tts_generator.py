#!/usr/bin/env python3
"""
🎙️ Coqui TTS 음성 합성 모델
로컬 CPU/GPU에서 한국어 TTS 생성
"""

import torch
from TTS.api import TTS
from pathlib import Path
from typing import Optional
from loguru import logger
import time
import numpy as np

class TTSGenerator:
    """Coqui TTS 기반 음성 합성기"""
    
    def __init__(self, models_dir: Path, device: str = "cuda"):
        self.models_dir = models_dir
        self.device = device
        self.tts = None
        self.loaded = False
        
    def load_model(self, model_name: str = "tts_models/multilingual/multi-dataset/xtts_v2"):
        """TTS 모델 로드
        
        Args:
            model_name: Coqui TTS 모델 이름
                - "tts_models/multilingual/multi-dataset/xtts_v2" (추천, 한국어 지원)
                - "tts_models/ko/kss/tacotron2-DDC" (한국어 전용)
        """
        try:
            logger.info(f"🎙️ Loading TTS model: {model_name}")
            start_time = time.time()
            
            # TTS 모델 로드
            self.tts = TTS(
                model_name=model_name,
                progress_bar=True,
                gpu=(self.device == "cuda")
            )
            
            load_time = time.time() - start_time
            logger.info(f"✅ TTS model loaded in {load_time:.1f}s")
            self.loaded = True
            
            # 지원 언어 확인
            if hasattr(self.tts, 'languages'):
                logger.info(f"   Supported languages: {self.tts.languages}")
            
        except Exception as e:
            logger.error(f"❌ Failed to load TTS model: {str(e)}")
            raise
    
    def generate_speech(
        self,
        text: str,
        output_path: Optional[Path] = None,
        language: str = "ko",
        speaker: Optional[str] = None,
        speed: float = 1.0,
        emotion: str = "neutral"
    ) -> str:
        """텍스트를 음성으로 변환
        
        Args:
            text: 변환할 텍스트
            output_path: 출력 파일 경로 (None이면 자동 생성)
            language: 언어 코드 (ko, en, etc.)
            speaker: 화자 ID (모델에 따라 다름)
            speed: 재생 속도 (1.0 = 보통)
            emotion: 감정 (neutral, happy, sad, angry 등)
            
        Returns:
            생성된 음성 파일 경로
        """
        if not self.loaded:
            raise RuntimeError("TTS model not loaded. Call load_model() first.")
        
        try:
            logger.info(f"🎙️ Generating speech: {text[:50]}...")
            logger.info(f"   Language: {language}, Speed: {speed}x")
            
            start_time = time.time()
            
            # 출력 경로 설정
            if output_path is None:
                output_dir = self.models_dir.parent / "output" / "audio"
                output_dir.mkdir(parents=True, exist_ok=True)
                timestamp = int(time.time())
                output_path = output_dir / f"speech_{timestamp}.wav"
            
            # 음성 생성
            if hasattr(self.tts, 'tts_to_file'):
                # XTTS-v2 등 다국어 모델
                self.tts.tts_to_file(
                    text=text,
                    file_path=str(output_path),
                    language=language,
                    speaker=speaker,
                    speed=speed
                )
            else:
                # 단일 언어 모델
                self.tts.tts_to_file(
                    text=text,
                    file_path=str(output_path)
                )
            
            gen_time = time.time() - start_time
            duration = self._get_audio_duration(output_path)
            
            logger.info(f"✅ Generated speech in {gen_time:.1f}s")
            logger.info(f"   Duration: {duration:.1f}s")
            logger.info(f"   Saved: {output_path.name}")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ Speech generation failed: {str(e)}")
            raise
    
    def generate_batch(
        self,
        texts: list[str],
        output_dir: Optional[Path] = None,
        **kwargs
    ) -> list[str]:
        """여러 텍스트를 배치로 음성 생성
        
        Args:
            texts: 변환할 텍스트 리스트
            output_dir: 출력 디렉토리
            **kwargs: generate_speech의 추가 인자
            
        Returns:
            생성된 음성 파일 경로 리스트
        """
        if output_dir is None:
            output_dir = self.models_dir.parent / "output" / "audio"
            output_dir.mkdir(parents=True, exist_ok=True)
        
        logger.info(f"🎙️ Batch generating {len(texts)} speeches")
        
        output_paths = []
        timestamp = int(time.time())
        
        for idx, text in enumerate(texts):
            output_path = output_dir / f"speech_{timestamp}_{idx}.wav"
            
            try:
                path = self.generate_speech(
                    text=text,
                    output_path=output_path,
                    **kwargs
                )
                output_paths.append(path)
            except Exception as e:
                logger.error(f"❌ Failed to generate speech {idx}: {str(e)}")
                output_paths.append(None)
        
        success_count = sum(1 for p in output_paths if p is not None)
        logger.info(f"✅ Batch generation complete: {success_count}/{len(texts)} succeeded")
        
        return output_paths
    
    def clone_voice(
        self,
        text: str,
        reference_audio: str,
        output_path: Optional[Path] = None,
        language: str = "ko"
    ) -> str:
        """참조 음성을 클론하여 새로운 음성 생성 (XTTS-v2 전용)
        
        Args:
            text: 변환할 텍스트
            reference_audio: 참조 음성 파일 경로
            output_path: 출력 파일 경로
            language: 언어 코드
            
        Returns:
            생성된 음성 파일 경로
        """
        if not self.loaded:
            raise RuntimeError("TTS model not loaded.")
        
        try:
            logger.info(f"🎙️ Cloning voice from: {reference_audio}")
            
            start_time = time.time()
            
            if output_path is None:
                output_dir = self.models_dir.parent / "output" / "audio"
                output_dir.mkdir(parents=True, exist_ok=True)
                timestamp = int(time.time())
                output_path = output_dir / f"cloned_{timestamp}.wav"
            
            # 음성 클로닝
            self.tts.tts_to_file(
                text=text,
                file_path=str(output_path),
                speaker_wav=reference_audio,
                language=language
            )
            
            gen_time = time.time() - start_time
            logger.info(f"✅ Voice cloned in {gen_time:.1f}s")
            
            return str(output_path)
            
        except Exception as e:
            logger.error(f"❌ Voice cloning failed: {str(e)}")
            raise
    
    def _get_audio_duration(self, audio_path: Path) -> float:
        """오디오 파일 길이 가져오기"""
        try:
            import librosa
            duration = librosa.get_duration(path=str(audio_path))
            return duration
        except:
            # librosa가 없으면 대략적인 계산
            import wave
            try:
                with wave.open(str(audio_path), 'rb') as wf:
                    frames = wf.getnframes()
                    rate = wf.getframerate()
                    return frames / float(rate)
            except:
                return 0.0
    
    def unload_model(self):
        """모델 언로드"""
        if self.tts is not None:
            del self.tts
            self.tts = None
            
            if self.device == "cuda":
                torch.cuda.empty_cache()
            
            logger.info("🗑️ TTS model unloaded")
            self.loaded = False


# ========== 캐릭터별 음성 설정 ==========

CHARACTER_VOICES = {
    "executive-fox": {
        "language": "ko",
        "speaker": None,  # 기본 화자
        "speed": 1.0,
        "emotion": "professional"
    },
    "ceo-lion": {
        "language": "ko",
        "speaker": None,
        "speed": 0.95,  # 약간 느리게 (권위)
        "emotion": "powerful"
    },
    "tech-fox": {
        "language": "ko",
        "speaker": None,
        "speed": 1.1,  # 약간 빠르게 (활발)
        "emotion": "energetic"
    },
    "fashionista-cat": {
        "language": "ko",
        "speaker": None,
        "speed": 1.05,
        "emotion": "elegant"
    },
    "comedian-parrot": {
        "language": "ko",
        "speaker": None,
        "speed": 1.2,  # 빠르게 (에너지 넘침)
        "emotion": "funny"
    },
}

def get_voice_settings(character_id: str) -> dict:
    """캐릭터 ID로 음성 설정 가져오기"""
    return CHARACTER_VOICES.get(
        character_id,
        CHARACTER_VOICES["executive-fox"]  # 기본값
    )


# ========== 테스트 코드 ==========
if __name__ == "__main__":
    # 테스트
    models_dir = Path(__file__).parent.parent.parent / "models"
    device = "cuda" if torch.cuda.is_available() else "cpu"
    
    logger.info(f"🔧 Device: {device}")
    
    generator = TTSGenerator(models_dir, device)
    
    # 모델 로드 (최초 1회)
    # generator.load_model()
    
    # 음성 생성 테스트
    # text = "안녕하세요! 이그제큐티브 폭스입니다. 오늘은 프리미엄 무선 이어폰을 소개해드리겠습니다."
    # audio_path = generator.generate_speech(text, language="ko")
    # print(f"Generated: {audio_path}")
