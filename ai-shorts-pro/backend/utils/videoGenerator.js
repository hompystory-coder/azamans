const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');

/**
 * Minimax Hailuo 2.3 비디오 생성 유틸리티
 */
class VideoGenerator {
  constructor() {
    // 실제 환경에서는 환경변수에서 가져옴
    this.apiKey = process.env.VIDEO_API_KEY || '';
    this.baseUrl = 'https://api.minimax.ai/v1'; // 예시 URL
  }

  /**
   * 이미지와 텍스트로 비디오 생성
   * @param {Object} options - 생성 옵션
   * @param {string} options.imagePath - 소스 이미지 경로
   * @param {string} options.prompt - 비디오 생성 프롬프트
   * @param {number} options.duration - 비디오 길이 (초)
   * @param {string} options.outputPath - 출력 경로
   */
  async generateVideoFromImage(options) {
    const { imagePath, prompt, duration = 3, outputPath } = options;

    console.log(`🎬 Starting video generation...`);
    console.log(`   Image: ${imagePath}`);
    console.log(`   Prompt: ${prompt}`);
    console.log(`   Duration: ${duration}s`);

    try {
      // 실제 API 호출 대신 FFmpeg로 간단한 비디오 생성
      // 프로덕션에서는 실제 Minimax Hailuo 2.3 API를 호출
      const { exec } = require('child_process');
      const { promisify } = require('util');
      const execAsync = promisify(exec);

      // FFmpeg로 이미지에 줌 효과 추가하여 비디오 생성
      await execAsync(`ffmpeg -loop 1 -i "${imagePath}" \
        -filter_complex "[0:v]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,zoompan=z='min(1.0+0.15*sin(in_time*2*PI/${duration}),1.2)':d=${duration * 25}:s=1080x1920:fps=25[v]" \
        -map "[v]" \
        -c:v libx264 -preset fast -pix_fmt yuv420p -t ${duration} \
        -y "${outputPath}"`);

      console.log(`✅ Video generated: ${outputPath}`);
      
      return {
        success: true,
        videoPath: outputPath,
        duration
      };

    } catch (error) {
      console.error(`❌ Video generation failed:`, error);
      throw error;
    }
  }

  /**
   * Minimax Hailuo 2.3 API로 실제 비디오 생성 (향후 구현)
   */
  async generateWithMinimaxAPI(options) {
    const { imagePath, prompt, duration } = options;

    // 실제 API 구현 예시
    try {
      // 1. 이미지를 base64로 인코딩
      const imageBuffer = await fs.readFile(imagePath);
      const imageBase64 = imageBuffer.toString('base64');

      // 2. Minimax API 호출
      const response = await axios.post(`${this.baseUrl}/video/generate`, {
        image: imageBase64,
        prompt: prompt,
        duration: duration,
        model: 'hailuo-2.3',
        resolution: '1080x1920'
      }, {
        headers: {
          'Authorization': `Bearer ${this.apiKey}`,
          'Content-Type': 'application/json'
        },
        timeout: 300000 // 5분
      });

      // 3. 작업 ID 반환
      return {
        success: true,
        jobId: response.data.job_id,
        status: 'processing'
      };

    } catch (error) {
      console.error('Minimax API error:', error);
      throw error;
    }
  }

  /**
   * 작업 상태 확인
   */
  async checkJobStatus(jobId) {
    try {
      const response = await axios.get(`${this.baseUrl}/video/status/${jobId}`, {
        headers: {
          'Authorization': `Bearer ${this.apiKey}`
        }
      });

      return response.data;
    } catch (error) {
      console.error('Status check error:', error);
      throw error;
    }
  }

  /**
   * 완성된 비디오 다운로드
   */
  async downloadVideo(videoUrl, outputPath) {
    try {
      const response = await axios.get(videoUrl, {
        responseType: 'stream'
      });

      const writer = fs.createWriteStream(outputPath);
      response.data.pipe(writer);

      return new Promise((resolve, reject) => {
        writer.on('finish', () => resolve(outputPath));
        writer.on('error', reject);
      });
    } catch (error) {
      console.error('Download error:', error);
      throw error;
    }
  }
}

module.exports = new VideoGenerator();
