const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');

/**
 * TTS (Text-to-Speech) 서비스
 * Minimax Speech 2.6 HD 사용
 */
class TTSService {
  constructor() {
    this.apiKey = process.env.MINIMAX_API_KEY;
    this.groupId = process.env.MINIMAX_GROUP_ID;
    this.baseUrl = 'https://api.minimax.io/v1/t2a_v2';
    this.outputDir = '/mnt/music-storage/shorts-videos/audio';
  }

  async ensureOutputDir() {
    try {
      await fs.mkdir(this.outputDir, { recursive: true });
    } catch (error) {
      console.error('Output directory creation failed:', error);
    }
  }

  /**
   * 한국어 음성 생성 (Minimax TTS v2)
   */
  async generateVoice(options) {
    const {
      text,
      voice = 'female_gentle', // 기본: 부드러운 여성 목소리
      speed = 1.0,
      pitch = 0,
      outputPath
    } = options;

    try {
      console.log(`🎤 음성 생성 시작: ${text.substring(0, 30)}...`);

      // Minimax T2A API v2 호출
      const response = await axios.post(
        this.baseUrl,
        {
          model: 'speech-2.6-hd',
          text,
          voice_setting: {
            voice_id: this.getVoiceId(voice),
            speed: speed,
            vol: 1.0,
            pitch: pitch
          },
          audio_setting: {
            sample_rate: 32000,
            bitrate: 128000,
            format: 'mp3'
          }
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          },
          timeout: 60000
        }
      );

      // JSON 응답에서 오디오 데이터 추출
      if (!response.data || !response.data.data || !response.data.data.audio) {
        throw new Error('Invalid TTS API response');
      }

      // Base64 디코딩
      const audioBuffer = Buffer.from(response.data.data.audio, 'base64');

      // 파일 저장
      await this.ensureOutputDir();
      const filename = outputPath || path.join(
        this.outputDir,
        `voice_${Date.now()}.mp3`
      );

      await fs.writeFile(filename, audioBuffer);
      console.log(`✅ 음성 파일 저장: ${filename}`);

      return {
        success: true,
        audioPath: filename,
        duration: response.data.data.duration || 3
      };
    } catch (error) {
      console.error('❌ TTS 생성 실패:', error.message);
      if (error.response) {
        console.error('API 응답:', error.response.data);
      }
      throw error;
    }
  }

  /**
   * 장면별 음성 생성 (배치)
   */
  async generateSceneVoices(scenes, voiceType = 'female_gentle') {
    console.log(`🎤 ${scenes.length}개 장면 음성 생성 시작`);

    const results = [];

    for (let i = 0; i < scenes.length; i++) {
      const scene = scenes[i];
      console.log(`\n🎙️ 장면 ${i + 1}/${scenes.length}: ${scene.title}`);

      try {
        const voiceResult = await this.generateVoice({
          text: scene.script,
          voice: voiceType,
          outputPath: path.join(
            this.outputDir,
            `scene_${i + 1}_${Date.now()}.mp3`
          )
        });

        results.push({
          ...scene,
          audioPath: voiceResult.audioPath,
          audioDuration: voiceResult.duration,
          success: true
        });

        console.log(`✅ 장면 ${i + 1} 음성 완료`);
      } catch (error) {
        console.error(`❌ 장면 ${i + 1} 음성 실패:`, error.message);
        results.push({
          ...scene,
          error: error.message,
          success: false
        });
      }
    }

    return results;
  }

  /**
   * 음성 타입별 ID 매핑
   */
  getVoiceId(voiceType) {
    // 실제 작동하는 Minimax voice ID만 사용
    const voiceMap = {
      'female_gentle': 'presenter_female',
      'female_energetic': 'audiobook_female_1',
      'male_calm': 'presenter_male',
      'male_powerful': 'audiobook_male_1',
      'child_cute': 'presenter_female' // 대체 음성
    };

    return voiceMap[voiceType] || 'presenter_male';
  }

  /**
   * 오디오 길이 계산
   */
  async getAudioDuration(filepath) {
    try {
      const { exec } = require('child_process');
      const util = require('util');
      const execPromise = util.promisify(exec);

      const { stdout } = await execPromise(
        `ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "${filepath}"`
      );

      return parseFloat(stdout.trim());
    } catch (error) {
      console.error('Duration calculation failed:', error);
      return 0;
    }
  }

  /**
   * 샘플 음성 생성 (미리듣기용)
   */
  async generateVoiceSamples() {
    const sampleText = '안녕하세요! 이것은 음성 샘플입니다.';
    const voices = ['female_gentle', 'female_energetic', 'male_calm', 'male_powerful'];

    const samples = [];

    for (const voice of voices) {
      try {
        const result = await this.generateVoice({
          text: sampleText,
          voice,
          outputPath: path.join(this.outputDir, `sample_${voice}.mp3`)
        });

        samples.push({
          voice,
          path: result.audioPath,
          duration: result.duration
        });
      } catch (error) {
        console.error(`Sample generation failed for ${voice}:`, error);
      }
    }

    return samples;
  }
}

module.exports = new TTSService();
