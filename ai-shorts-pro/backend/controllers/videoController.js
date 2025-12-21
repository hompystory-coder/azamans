const minimaxService = require('../services/minimaxService');
const ttsService = require('../services/ttsService');
const ffmpegService = require('../services/ffmpegService');
const { generateCharacterScript, getCharacter, getVideoMode } = require('../services/characterService');
const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');

/**
 * 비디오 생성 컨트롤러
 * 전체 쇼츠 생성 플로우 관리
 */

// 전체 쇼츠 생성 (자동 모드)
exports.createShorts = async (req, res) => {
  try {
    const {
      url,
      characterId = 'friendly_neighbor',
      videoMode = 'character_plus_images',
      settings = {}
    } = req.body;

    console.log(`\n🚀 쇼츠 생성 시작`);
    console.log(`📌 URL: ${url}`);
    console.log(`🎭 캐릭터: ${characterId}`);
    console.log(`🎬 비디오 모드: ${videoMode}`);

    // 1단계: 크롤링
    console.log(`\n📡 1단계: 크롤링 시작`);
    const crawlResponse = await axios.post('http://localhost:5555/api/crawler/crawl', { url });
    const crawlData = crawlResponse.data.data;

    console.log(`✅ 크롤링 완료`);
    console.log(`   제목: ${crawlData.title}`);
    console.log(`   단어 수: ${crawlData.wordCount}`);
    console.log(`   이미지: ${crawlData.imageCount}개`);

    // 2단계: AI 스크립트 생성
    console.log(`\n🤖 2단계: AI 스크립트 생성`);
    const scriptResponse = await axios.post('http://localhost:5555/api/crawler/generate-script', {
      content: crawlData.content,
      productName: extractProductName(crawlData.title),
      category: settings.category || '기타'
    });

    const baseScenes = scriptResponse.data.data.scenes;
    console.log(`✅ 기본 스크립트 생성 완료: ${baseScenes.length}개 장면`);

    // 3단계: 캐릭터 스타일 적용
    console.log(`\n🎭 3단계: 캐릭터 스타일 적용`);
    const character = getCharacter(characterId);
    const styledScenes = generateCharacterScript(character, crawlData.content, baseScenes);

    console.log(`✅ 캐릭터 스타일 적용 완료: ${character.name}`);

    // 4단계: 이미지 매핑
    console.log(`\n🖼️ 4단계: 이미지 매핑`);
    const mode = getVideoMode(videoMode);
    const scenesWithImages = await mapImagesToScenes(styledScenes, crawlData.images, mode);

    // 5단계: TTS 생성 (음성)
    console.log(`\n🎤 5단계: 음성 생성`);
    const scenesWithAudio = await ttsService.generateSceneVoices(
      scenesWithImages,
      character.voice
    );

    // 6단계: 비디오 생성 (Minimax)
    console.log(`\n🎬 6단계: 비디오 생성`);
    const scenesWithVideo = await generateSceneVideos(scenesWithAudio, mode);

    // 7단계: 최종 렌더링 (FFmpeg)
    console.log(`\n🎞️ 7단계: 최종 렌더링`);
    const finalVideo = await ffmpegService.mergeScenes(scenesWithVideo, {
      outputFilename: `shorts_${Date.now()}.mp4`,
      bgmPath: settings.bgmPath,
      bgmVolume: settings.bgmVolume || 0.3,
      resolution: '1080x1920'
    });

    // 8단계: 메타데이터 생성
    console.log(`\n📝 8단계: 메타데이터 생성`);
    const metadata = await generateMetadata(crawlData, styledScenes);

    console.log(`\n✅ 쇼츠 생성 완료!`);

    res.json({
      success: true,
      data: {
        videoPath: finalVideo.outputPath,
        duration: finalVideo.duration,
        fileSize: finalVideo.fileSize,
        metadata,
        scenes: scenesWithVideo.length,
        character: character.name
      }
    });
  } catch (error) {
    console.error('❌ 쇼츠 생성 실패:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// 수동 모드: 스크립트만 생성
exports.generateScript = async (req, res) => {
  try {
    const { url, characterId, category } = req.body;

    // 크롤링
    const crawlResponse = await axios.post('http://localhost:5555/api/crawler/crawl', { url });
    const crawlData = crawlResponse.data.data;

    // 기본 스크립트 생성
    const scriptResponse = await axios.post('http://localhost:5555/api/crawler/generate-script', {
      content: crawlData.content,
      productName: extractProductName(crawlData.title),
      category: category || '기타'
    });

    const baseScenes = scriptResponse.data.data.scenes;

    // 캐릭터 스타일 적용
    const character = getCharacter(characterId);
    const styledScenes = generateCharacterScript(character, crawlData.content, baseScenes);

    res.json({
      success: true,
      data: {
        scenes: styledScenes,
        crawlData,
        character: character.name
      }
    });
  } catch (error) {
    console.error('스크립트 생성 실패:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// 수동 모드: 비디오 렌더링
exports.renderVideo = async (req, res) => {
  try {
    const { scenes, settings } = req.body;

    // TTS 생성
    console.log(`🎤 음성 생성 중...`);
    const scenesWithAudio = await ttsService.generateSceneVoices(
      scenes,
      settings.voice || 'female_gentle'
    );

    // 비디오 생성
    console.log(`🎬 비디오 생성 중...`);
    const mode = getVideoMode(settings.videoMode);
    const scenesWithVideo = await generateSceneVideos(scenesWithAudio, mode);

    // 최종 렌더링
    console.log(`🎞️ 최종 렌더링 중...`);
    const finalVideo = await ffmpegService.mergeScenes(scenesWithVideo, settings);

    res.json({
      success: true,
      data: finalVideo
    });
  } catch (error) {
    console.error('비디오 렌더링 실패:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// 헬퍼 함수들

/**
 * 장면에 이미지 매핑
 */
async function mapImagesToScenes(scenes, images, mode) {
  if (!mode.useRealImages || !images || images.length === 0) {
    return scenes;
  }

  // 유효한 이미지 필터링 (spc.gif 등 제외)
  const validImages = images.filter(img => 
    !img.url.includes('spc.gif') && 
    !img.url.includes('ico_') &&
    img.url.startsWith('http')
  );

  console.log(`   유효한 이미지: ${validImages.length}개`);

  return scenes.map((scene, index) => {
    const imageIndex = index % validImages.length;
    return {
      ...scene,
      imageUrl: validImages[imageIndex]?.url
    };
  });
}

/**
 * 비디오 생성 (이미지 있으면 Image-to-Video, 없으면 Text-to-Video)
 */
async function generateSceneVideos(scenes, mode) {
  const results = [];

  for (let i = 0; i < scenes.length; i++) {
    const scene = scenes[i];
    console.log(`\n📹 장면 ${i + 1}/${scenes.length}: ${scene.title}`);

    try {
      let videoResult;

      if (scene.imageUrl && mode.useRealImages) {
        // Image-to-Video
        console.log(`   이미지→비디오: ${scene.imageUrl.substring(0, 50)}...`);
        videoResult = await minimaxService.generateVideoFromImage({
          imageUrl: scene.imageUrl,
          prompt: scene.script,
          duration: scene.duration || 3
        });
      } else if (mode.useCharacter) {
        // Text-to-Video (캐릭터)
        console.log(`   텍스트→비디오: ${scene.script.substring(0, 50)}...`);
        videoResult = await minimaxService.generateVideo({
          prompt: `${scene.visualStyle}: ${scene.script}`,
          duration: scene.duration || 3
        });
      } else {
        console.log(`   ⏭️ 비디오 생성 건너뜀`);
        results.push({ ...scene, success: false });
        continue;
      }

      // 비디오 다운로드
      const videoPath = await downloadVideo(videoResult.videoUrl, i);

      results.push({
        ...scene,
        videoPath,
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

  return results;
}

/**
 * 비디오 다운로드
 */
async function downloadVideo(url, index) {
  const outputPath = `/mnt/music-storage/shorts-videos/temp/scene_${index}_${Date.now()}.mp4`;
  
  const response = await axios({
    method: 'get',
    url,
    responseType: 'stream'
  });

  const writer = require('fs').createWriteStream(outputPath);
  response.data.pipe(writer);

  return new Promise((resolve, reject) => {
    writer.on('finish', () => resolve(outputPath));
    writer.on('error', reject);
  });
}

/**
 * 제품명 추출
 */
function extractProductName(title) {
  // 콜론 앞부분을 제품명으로 추출
  const match = title.match(/^(.+?)[:：]/);
  if (match) {
    return match[1].trim();
  }
  
  // 첫 30자 반환
  return title.substring(0, 30);
}

/**
 * 유튜브 메타데이터 생성
 */
async function generateMetadata(crawlData, scenes) {
  const productName = extractProductName(crawlData.title);
  
  // 제목 생성
  const title = `${productName} 솔직 리뷰 | 장단점 총정리 #shorts`;
  
  // 설명 생성
  const description = `
${productName} 완벽 분석!

📌 핵심 내용:
${scenes.slice(0, 3).map((s, i) => `${i + 1}. ${s.title}`).join('\n')}

🔗 원본 링크: ${crawlData.url}

#${productName.replace(/\s/g, '')} #리뷰 #추천 #쇼츠 #shorts
`.trim();

  // 해시태그 생성
  const tags = [
    productName,
    '리뷰',
    '추천',
    '쇼츠',
    'shorts',
    '상품리뷰',
    '구매후기'
  ];

  return {
    title,
    description,
    tags
  };
}

module.exports = exports;
