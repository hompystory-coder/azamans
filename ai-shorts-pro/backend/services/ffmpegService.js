const ffmpeg = require('fluent-ffmpeg');
const fs = require('fs').promises;
const path = require('path');
const { exec } = require('child_process');
const util = require('util');
const execPromise = util.promisify(exec);

/**
 * FFmpeg 비디오 렌더링 서비스
 */
class FFmpegService {
  constructor() {
    this.outputDir = '/mnt/music-storage/shorts-videos/output';
    this.tempDir = '/mnt/music-storage/shorts-videos/temp';
    
    // FFmpeg 경로 설정
    ffmpeg.setFfmpegPath('/usr/bin/ffmpeg');
    ffmpeg.setFfprobePath('/usr/bin/ffprobe');
  }

  async ensureDirectories() {
    await fs.mkdir(this.outputDir, { recursive: true });
    await fs.mkdir(this.tempDir, { recursive: true });
  }

  /**
   * 장면 비디오들을 하나로 병합
   */
  async mergeScenes(scenes, options = {}) {
    await this.ensureDirectories();

    const {
      outputFilename = `shorts_${Date.now()}.mp4`,
      bgmPath,
      bgmVolume = 0.3,
      resolution = '1080x1920' // 9:16 세로 영상
    } = options;

    console.log(`🎬 ${scenes.length}개 장면 병합 시작`);

    try {
      // 1단계: 장면별로 비디오+음성+자막 합성
      const processedScenes = [];

      for (let i = 0; i < scenes.length; i++) {
        const scene = scenes[i];
        console.log(`\n📹 장면 ${i + 1}/${scenes.length} 처리 중...`);

        const processedPath = await this.processScene(scene, i);
        processedScenes.push(processedPath);
      }

      // 2단계: 모든 장면 병합
      const mergedPath = path.join(this.tempDir, `merged_${Date.now()}.mp4`);
      await this.concatenateVideos(processedScenes, mergedPath);

      // 3단계: 배경음악 추가 (선택사항)
      let finalPath = mergedPath;
      if (bgmPath) {
        finalPath = await this.addBackgroundMusic(mergedPath, bgmPath, bgmVolume);
      }

      // 4단계: 최종 출력 파일 생성
      const outputPath = path.join(this.outputDir, outputFilename);
      await this.finalizeVideo(finalPath, outputPath, resolution);

      // 5단계: 임시 파일 정리
      await this.cleanup([...processedScenes, mergedPath]);

      console.log(`\n✅ 최종 렌더링 완료: ${outputPath}`);

      return {
        success: true,
        outputPath,
        duration: await this.getVideoDuration(outputPath),
        fileSize: await this.getFileSize(outputPath)
      };
    } catch (error) {
      console.error('❌ 렌더링 실패:', error);
      throw error;
    }
  }

  /**
   * 개별 장면 처리 (비디오 + 음성 + 자막)
   */
  async processScene(scene, index) {
    const outputPath = path.join(this.tempDir, `scene_${index}_${Date.now()}.mp4`);

    return new Promise(async (resolve, reject) => {
      try {
        let command = ffmpeg();

        // 비디오 입력
        if (scene.videoPath) {
          command = command.input(scene.videoPath);
        }

        // 음성 입력
        if (scene.audioPath) {
          command = command.input(scene.audioPath);
        }

        // 자막 추가
        const subtitleFilter = this.createSubtitleFilter(scene);

        command
          .outputOptions([
            '-c:v libx264',
            '-preset fast',
            '-crf 23',
            '-c:a aac',
            '-b:a 128k',
            '-vf', subtitleFilter,
            '-pix_fmt yuv420p'
          ])
          .output(outputPath)
          .on('end', () => {
            console.log(`✅ 장면 ${index + 1} 처리 완료`);
            resolve(outputPath);
          })
          .on('error', (err) => {
            console.error(`❌ 장면 ${index + 1} 처리 실패:`, err);
            reject(err);
          })
          .on('progress', (progress) => {
            if (progress.percent) {
              process.stdout.write(`\r⏳ 진행률: ${Math.round(progress.percent)}%`);
            }
          })
          .run();
      } catch (error) {
        reject(error);
      }
    });
  }

  /**
   * 자막 필터 생성
   */
  createSubtitleFilter(scene) {
    const {
      script,
      fontSize = 40,
      fontColor = 'white',
      fontPath = '/home/azamans/webapp/ai-shorts-pro/backend/fonts/NanumGothicBold.ttf',
      position = 'center'
    } = scene;

    // 자막 위치 계산
    let y = 'h-th-100'; // 하단 중앙 (기본값)
    if (position === 'top') y = '50';
    else if (position === 'middle') y = '(h-th)/2';

    // 텍스트 이스케이프 처리
    const escapedText = script
      .replace(/\\/g, '\\\\')
      .replace(/'/g, "\\'")
      .replace(/:/g, '\\:')
      .replace(/\n/g, ' ');

    return `drawtext=fontfile='${fontPath}':text='${escapedText}':fontsize=${fontSize}:fontcolor=${fontColor}:x=(w-tw)/2:y=${y}:box=1:boxcolor=black@0.5:boxborderw=10`;
  }

  /**
   * 여러 비디오 연결
   */
  async concatenateVideos(videoPaths, outputPath) {
    console.log(`\n🔗 ${videoPaths.length}개 비디오 병합 중...`);

    const listFile = path.join(this.tempDir, `concat_${Date.now()}.txt`);
    const listContent = videoPaths.map(p => `file '${p}'`).join('\n');
    await fs.writeFile(listFile, listContent);

    return new Promise((resolve, reject) => {
      ffmpeg()
        .input(listFile)
        .inputOptions(['-f concat', '-safe 0'])
        .outputOptions([
          '-c copy'
        ])
        .output(outputPath)
        .on('end', async () => {
          await fs.unlink(listFile);
          console.log(`✅ 비디오 병합 완료`);
          resolve(outputPath);
        })
        .on('error', (err) => {
          console.error('❌ 비디오 병합 실패:', err);
          reject(err);
        })
        .run();
    });
  }

  /**
   * 배경음악 추가
   */
  async addBackgroundMusic(videoPath, bgmPath, volume = 0.3) {
    console.log(`\n🎵 배경음악 추가 중...`);

    const outputPath = path.join(this.tempDir, `with_bgm_${Date.now()}.mp4`);

    return new Promise((resolve, reject) => {
      ffmpeg()
        .input(videoPath)
        .input(bgmPath)
        .complexFilter([
          `[1:a]volume=${volume}[bgm]`,
          `[0:a][bgm]amix=inputs=2:duration=first[a]`
        ])
        .outputOptions([
          '-map 0:v',
          '-map [a]',
          '-c:v copy',
          '-c:a aac',
          '-shortest'
        ])
        .output(outputPath)
        .on('end', () => {
          console.log(`✅ 배경음악 추가 완료`);
          resolve(outputPath);
        })
        .on('error', (err) => {
          console.error('❌ 배경음악 추가 실패:', err);
          reject(err);
        })
        .run();
    });
  }

  /**
   * 최종 비디오 생성 (해상도 조정)
   */
  async finalizeVideo(inputPath, outputPath, resolution) {
    console.log(`\n🎬 최종 렌더링 중... (해상도: ${resolution})`);

    return new Promise((resolve, reject) => {
      ffmpeg()
        .input(inputPath)
        .outputOptions([
          '-c:v libx264',
          '-preset slow',
          '-crf 18',
          '-c:a aac',
          '-b:a 192k',
          `-s ${resolution}`,
          '-pix_fmt yuv420p',
          '-movflags +faststart' // 웹 스트리밍 최적화
        ])
        .output(outputPath)
        .on('end', () => {
          console.log(`✅ 최종 렌더링 완료`);
          resolve(outputPath);
        })
        .on('error', (err) => {
          console.error('❌ 최종 렌더링 실패:', err);
          reject(err);
        })
        .on('progress', (progress) => {
          if (progress.percent) {
            process.stdout.write(`\r⏳ 렌더링: ${Math.round(progress.percent)}%`);
          }
        })
        .run();
    });
  }

  /**
   * 비디오 길이 가져오기
   */
  async getVideoDuration(filepath) {
    try {
      const { stdout } = await execPromise(
        `ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "${filepath}"`
      );
      return parseFloat(stdout.trim());
    } catch (error) {
      return 0;
    }
  }

  /**
   * 파일 크기 가져오기
   */
  async getFileSize(filepath) {
    try {
      const stats = await fs.stat(filepath);
      return stats.size;
    } catch (error) {
      return 0;
    }
  }

  /**
   * 임시 파일 정리
   */
  async cleanup(filePaths) {
    for (const filepath of filePaths) {
      try {
        await fs.unlink(filepath);
      } catch (error) {
        // 무시
      }
    }
  }
}

module.exports = new FFmpegService();
