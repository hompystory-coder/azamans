// Character-based video generation route
import express from 'express';
import path from 'path';
import { fileURLToPath } from 'url';
import { MinimaxVideoService, generateSceneVideo } from '../services/minimaxVideo.js';
import { getCharacterById } from '../../../frontend/src/lib/characters.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const router = express.Router();

/**
 * POST /api/character-video/generate
 * Generate videos with AI characters using Minimax Hailuo 2.3
 */
router.post('/generate', async (req, res) => {
  try {
    const {
      scenes,
      characterId,
      contentMode = 'character', // 'character' | 'hybrid' | 'realistic'
      settings = {}
    } = req.body;

    // Validate input
    if (!scenes || !Array.isArray(scenes) || scenes.length === 0) {
      return res.status(400).json({
        success: false,
        error: '장면 데이터가 필요합니다.'
      });
    }

    // Get character if specified
    let character = null;
    if (contentMode !== 'realistic' && characterId) {
      character = getCharacterById(characterId);
      if (!character) {
        return res.status(400).json({
          success: false,
          error: '유효하지 않은 캐릭터 ID'
        });
      }
      console.log(`🎭 선택된 캐릭터: ${character.nameKr}`);
    }

    console.log(`🎬 캐릭터 비디오 생성 시작`);
    console.log(`   - 콘텐츠 모드: ${contentMode}`);
    console.log(`   - 장면 수: ${scenes.length}개`);
    console.log(`   - 캐릭터: ${character ? character.nameKr : '없음'}`);

    const generatedScenes = [];
    const tempDir = path.join(__dirname, '../../../temp/character-videos');

    // Generate videos for each scene
    for (let i = 0; i < scenes.length; i++) {
      const scene = scenes[i];
      console.log(`\n🎬 장면 ${i + 1}/${scenes.length} 생성 중...`);
      console.log(`   - 텍스트: ${scene.narration || scene.text}`);

      try {
        let videoPath = null;

        // Determine if this scene should use character video
        const useCharacter = contentMode === 'character' || 
                           (contentMode === 'hybrid' && i % 2 === 0);

        if (useCharacter && character) {
          // Generate character video using Minimax
          videoPath = await generateSceneVideo(
            {
              ...scene,
              sceneNumber: i + 1
            },
            character,
            tempDir
          );
          console.log(`✅ 캐릭터 비디오 생성: ${videoPath}`);
        } else {
          // Use static image (existing flow)
          videoPath = scene.imageUrl;
          console.log(`📷 실사 이미지 사용: ${scene.imageUrl}`);
        }

        generatedScenes.push({
          sceneNumber: i + 1,
          videoPath,
          narration: scene.narration || scene.text,
          duration: scene.duration || 3,
          useCharacter
        });

      } catch (error) {
        console.error(`❌ 장면 ${i + 1} 생성 실패:`, error.message);
        
        // Fallback to image if video generation fails
        generatedScenes.push({
          sceneNumber: i + 1,
          videoPath: scene.imageUrl,
          narration: scene.narration || scene.text,
          duration: scene.duration || 3,
          useCharacter: false,
          error: error.message
        });
      }
    }

    console.log(`\n✅ 모든 장면 생성 완료`);

    // Return generated scenes for further processing
    res.json({
      success: true,
      data: {
        scenes: generatedScenes,
        totalScenes: generatedScenes.length,
        characterUsed: character ? character.nameKr : null,
        contentMode
      }
    });

  } catch (error) {
    console.error('❌ 캐릭터 비디오 생성 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message || '캐릭터 비디오 생성에 실패했습니다.'
    });
  }
});

/**
 * GET /api/character-video/status/:taskId
 * Check Minimax video generation status
 */
router.get('/status/:taskId', async (req, res) => {
  try {
    const { taskId } = req.params;
    
    const service = new MinimaxVideoService();
    const status = await service.checkStatus(taskId);

    res.json({
      success: true,
      data: status
    });

  } catch (error) {
    console.error('❌ 상태 확인 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * POST /api/character-video/test
 * Test Minimax video generation with a single scene
 */
router.post('/test', async (req, res) => {
  try {
    const { imageUrl, prompt, characterId } = req.body;

    if (!imageUrl || !prompt) {
      return res.status(400).json({
        success: false,
        error: 'imageUrl and prompt are required'
      });
    }

    const character = characterId ? getCharacterById(characterId) : null;
    const service = new MinimaxVideoService();

    console.log('🧪 테스트 비디오 생성 시작...');

    const task = await service.generateVideo({
      imageUrl,
      prompt,
      character,
      duration: 3
    });

    const videoUrl = await service.waitForCompletion(task.taskId);

    res.json({
      success: true,
      data: {
        taskId: task.taskId,
        videoUrl,
        message: 'Test video generated successfully'
      }
    });

  } catch (error) {
    console.error('❌ 테스트 실패:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

export default router;
