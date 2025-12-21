const axios = require('axios');

/**
 * Minimax Hailuo 2.3 비디오 생성 서비스
 */
class MinimaxService {
  constructor() {
    this.apiKey = process.env.MINIMAX_API_KEY;
    this.groupId = process.env.MINIMAX_GROUP_ID;
    this.baseUrl = 'https://api.minimax.chat/v1/video_generation';
  }

  /**
   * 텍스트 → 비디오 생성 (Hailuo 2.3)
   */
  async generateVideo(options) {
    const {
      prompt,
      duration = 5,
      aspectRatio = '9:16',
      model = 'hailuo-2.3'
    } = options;

    try {
      console.log(`🎬 Minimax 비디오 생성 시작: ${prompt.substring(0, 50)}...`);

      const response = await axios.post(
        this.baseUrl,
        {
          model,
          prompt,
          duration,
          aspect_ratio: aspectRatio
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          },
          timeout: 300000 // 5분
        }
      );

      const taskId = response.data.task_id;
      console.log(`✅ 비디오 생성 작업 시작: ${taskId}`);

      // 폴링으로 결과 확인
      return await this.pollVideoStatus(taskId);
    } catch (error) {
      console.error('❌ Minimax 비디오 생성 실패:', error.message);
      throw new Error(`비디오 생성 실패: ${error.message}`);
    }
  }

  /**
   * 이미지 → 비디오 생성 (Image-to-Video)
   */
  async generateVideoFromImage(options) {
    const {
      imageUrl,
      prompt,
      duration = 3,
      aspectRatio = '9:16'
    } = options;

    try {
      console.log(`🖼️ 이미지→비디오 생성: ${imageUrl}`);

      const response = await axios.post(
        `${this.baseUrl}/image-to-video`,
        {
          model: 'hailuo-2.3',
          image_url: imageUrl,
          prompt,
          duration,
          aspect_ratio: aspectRatio
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          },
          timeout: 300000
        }
      );

      const taskId = response.data.task_id;
      return await this.pollVideoStatus(taskId);
    } catch (error) {
      console.error('❌ Image-to-Video 실패:', error.message);
      throw error;
    }
  }

  /**
   * 비디오 생성 상태 폴링
   */
  async pollVideoStatus(taskId, maxAttempts = 60) {
    console.log(`⏳ 비디오 생성 대기 중... (최대 ${maxAttempts * 5}초)`);

    for (let attempt = 0; attempt < maxAttempts; attempt++) {
      try {
        const response = await axios.get(
          `${this.baseUrl}/query?task_id=${taskId}`,
          {
            headers: {
              'Authorization': `Bearer ${this.apiKey}`
            }
          }
        );

        const { status, video_url, progress } = response.data;

        if (status === 'Success') {
          console.log(`✅ 비디오 생성 완료: ${video_url}`);
          return {
            success: true,
            videoUrl: video_url,
            taskId
          };
        } else if (status === 'Failed') {
          throw new Error('비디오 생성 실패');
        }

        // 진행 중
        console.log(`⏳ 진행률: ${progress || 0}% (${attempt + 1}/${maxAttempts})`);
        await this.sleep(5000); // 5초 대기
      } catch (error) {
        if (attempt === maxAttempts - 1) {
          throw error;
        }
        await this.sleep(5000);
      }
    }

    throw new Error('비디오 생성 타임아웃 (5분 초과)');
  }

  /**
   * 장면별 비디오 생성 (배치)
   */
  async generateSceneVideos(scenes) {
    console.log(`🎬 ${scenes.length}개 장면 비디오 생성 시작`);

    const results = [];

    for (let i = 0; i < scenes.length; i++) {
      const scene = scenes[i];
      console.log(`\n📹 장면 ${i + 1}/${scenes.length}: ${scene.title}`);

      try {
        let videoResult;

        if (scene.imageUrl) {
          // 이미지가 있으면 Image-to-Video
          videoResult = await this.generateVideoFromImage({
            imageUrl: scene.imageUrl,
            prompt: scene.script,
            duration: scene.duration || 3
          });
        } else {
          // 텍스트만으로 비디오 생성
          videoResult = await this.generateVideo({
            prompt: `${scene.visualStyle}: ${scene.script}`,
            duration: scene.duration || 3
          });
        }

        results.push({
          ...scene,
          videoUrl: videoResult.videoUrl,
          success: true
        });

        console.log(`✅ 장면 ${i + 1} 완료`);
      } catch (error) {
        console.error(`❌ 장면 ${i + 1} 실패:`, error.message);
        results.push({
          ...scene,
          error: error.message,
          success: false
        });
      }
    }

    const successCount = results.filter(r => r.success).length;
    console.log(`\n✅ 비디오 생성 완료: ${successCount}/${scenes.length}`);

    return results;
  }

  sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

module.exports = new MinimaxService();
