// Minimax Hailuo 2.3 Video Generation Service
import axios from 'axios';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Minimax Video Generation Service using Hailuo 2.3 model
 * Converts images to 3-second video clips with character animations
 */
export class MinimaxVideoService {
  constructor(apiKey, groupId) {
    this.apiKey = apiKey || process.env.MINIMAX_API_KEY;
    this.groupId = groupId || process.env.MINIMAX_GROUP_ID;
    
    // Minimax API endpoints
    this.baseUrl = 'https://api.minimaxi.com/v1';
    this.videoGenerationUrl = `${this.baseUrl}/video_generation`;
    
    if (!this.apiKey) {
      console.warn('⚠️  Minimax API key not configured');
    }
  }

  /**
   * Generate video from image using Hailuo 2.3
   * @param {Object} options - Video generation options
   * @param {string} options.imageUrl - Source image URL
   * @param {string} options.prompt - Video generation prompt
   * @param {number} options.duration - Video duration in seconds (default: 3)
   * @param {Object} options.character - Character object with video prompt template
   * @returns {Promise<{taskId: string, status: string}>}
   */
  async generateVideo(options) {
    const {
      imageUrl,
      prompt,
      duration = 3,
      character = null
    } = options;

    try {
      // Build full prompt with character template if provided
      let fullPrompt = prompt;
      
      if (character && character.videoPromptTemplate) {
        const action = 'explaining naturally with gestures';
        const characterPrompt = character.videoPromptTemplate
          .replace('{action}', action);
        fullPrompt = `${characterPrompt}. ${prompt}`;
      }

      console.log(`🎬 Minimax 비디오 생성 요청:`, {
        imageUrl: imageUrl.substring(0, 50) + '...',
        prompt: fullPrompt.substring(0, 100) + '...',
        duration
      });

      // Call Minimax Video Generation API
      const response = await axios.post(
        this.videoGenerationUrl,
        {
          model: 'hailuo-2.3',
          prompt: fullPrompt,
          first_frame_image: imageUrl,
          prompt_optimizer: true,
          duration: duration,
          aspect_ratio: '9:16' // YouTube Shorts format
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          },
          params: {
            GroupId: this.groupId
          },
          timeout: 30000 // 30 second timeout
        }
      );

      const taskId = response.data.task_id || response.data.id;
      
      console.log(`✅ Minimax 비디오 생성 시작: ${taskId}`);

      return {
        taskId,
        status: 'processing'
      };

    } catch (error) {
      console.error('❌ Minimax 비디오 생성 실패:', error.response?.data || error.message);
      throw new Error(`Minimax video generation failed: ${error.message}`);
    }
  }

  /**
   * Check video generation status
   * @param {string} taskId - Task ID from generateVideo
   * @returns {Promise<{status: string, videoUrl?: string, progress?: number}>}
   */
  async checkStatus(taskId) {
    try {
      const response = await axios.get(
        `${this.videoGenerationUrl}/${taskId}`,
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`
          },
          params: {
            GroupId: this.groupId
          }
        }
      );

      const data = response.data;
      
      return {
        status: data.status, // 'processing', 'completed', 'failed'
        videoUrl: data.video_url || data.file?.url,
        progress: data.progress || 0
      };

    } catch (error) {
      console.error('❌ 상태 확인 실패:', error.message);
      throw error;
    }
  }

  /**
   * Wait for video generation to complete
   * @param {string} taskId - Task ID
   * @param {number} maxWaitTime - Maximum wait time in milliseconds (default: 5 minutes)
   * @param {number} pollInterval - Polling interval in milliseconds (default: 5 seconds)
   * @returns {Promise<string>} Video URL
   */
  async waitForCompletion(taskId, maxWaitTime = 300000, pollInterval = 5000) {
    const startTime = Date.now();
    
    console.log(`⏳ 비디오 생성 대기 중... (최대 ${maxWaitTime / 1000}초)`);

    while (Date.now() - startTime < maxWaitTime) {
      try {
        const status = await this.checkStatus(taskId);

        console.log(`📊 진행률: ${status.progress}% (상태: ${status.status})`);

        if (status.status === 'completed' && status.videoUrl) {
          console.log(`✅ 비디오 생성 완료: ${status.videoUrl}`);
          return status.videoUrl;
        } else if (status.status === 'failed') {
          throw new Error('비디오 생성 실패');
        }

        // Wait before next poll
        await new Promise(resolve => setTimeout(resolve, pollInterval));

      } catch (error) {
        if (Date.now() - startTime < maxWaitTime) {
          // Retry on transient errors
          await new Promise(resolve => setTimeout(resolve, pollInterval));
          continue;
        }
        throw error;
      }
    }

    throw new Error('비디오 생성 시간 초과');
  }

  /**
   * Download video from URL to local path
   * @param {string} videoUrl - Video URL
   * @param {string} outputPath - Local output path
   * @returns {Promise<string>} Local file path
   */
  async downloadVideo(videoUrl, outputPath) {
    try {
      console.log(`📥 비디오 다운로드 중: ${videoUrl}`);

      const response = await axios.get(videoUrl, {
        responseType: 'stream',
        timeout: 60000 // 60 second timeout for download
      });

      // Ensure output directory exists
      const dir = path.dirname(outputPath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }

      // Write to file
      const writer = fs.createWriteStream(outputPath);
      response.data.pipe(writer);

      return new Promise((resolve, reject) => {
        writer.on('finish', () => {
          console.log(`✅ 비디오 다운로드 완료: ${outputPath}`);
          resolve(outputPath);
        });
        writer.on('error', reject);
      });

    } catch (error) {
      console.error('❌ 비디오 다운로드 실패:', error.message);
      throw error;
    }
  }

  /**
   * Generate and download video in one call
   * @param {Object} options - Generation options
   * @param {string} outputPath - Local output path
   * @returns {Promise<string>} Local file path
   */
  async generateAndDownload(options, outputPath) {
    // Generate video
    const task = await this.generateVideo(options);
    
    // Wait for completion
    const videoUrl = await this.waitForCompletion(task.taskId);
    
    // Download video
    const localPath = await this.downloadVideo(videoUrl, outputPath);
    
    return localPath;
  }
}

/**
 * Helper function to generate a single scene video
 * @param {Object} scene - Scene data with narration, imageUrl
 * @param {Object} character - Character data
 * @param {string} outputDir - Output directory
 * @returns {Promise<string>} Local video path
 */
export async function generateSceneVideo(scene, character, outputDir) {
  const service = new MinimaxVideoService();
  
  // Create output filename
  const filename = `scene_${scene.sceneNumber || scene.id || Date.now()}.mp4`;
  const outputPath = path.join(outputDir, filename);

  try {
    const videoPath = await service.generateAndDownload(
      {
        imageUrl: scene.imageUrl,
        prompt: scene.narration || scene.text,
        character: character,
        duration: 3
      },
      outputPath
    );

    return videoPath;

  } catch (error) {
    console.error(`❌ 장면 ${scene.sceneNumber} 생성 실패:`, error.message);
    throw error;
  }
}

export default MinimaxVideoService;
