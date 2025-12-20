/**
 * FFmpeg Service - Video rendering and processing
 */

const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs').promises;

class FFmpegService {
  constructor() {
    this.ffmpegPath = process.env.FFMPEG_PATH || 'ffmpeg';
    this.ffprobePath = process.env.FFPROBE_PATH || 'ffprobe';
  }

  /**
   * Concatenate multiple videos
   */
  async concatenateVideos(videoFiles, outputPath) {
    return new Promise((resolve, reject) => {
      // Create concat file list
      const listContent = videoFiles.map(f => `file '${f}'`).join('\n');
      const listFile = outputPath + '.list';

      fs.writeFile(listFile, listContent)
        .then(() => {
          const args = [
            '-f', 'concat',
            '-safe', '0',
            '-i', listFile,
            '-c', 'copy',
            outputPath,
            '-y'
          ];

          const ffmpeg = spawn(this.ffmpegPath, args);
          let stderr = '';

          ffmpeg.stderr.on('data', (data) => {
            stderr += data.toString();
          });

          ffmpeg.on('close', (code) => {
            fs.unlink(listFile).catch(() => {});
            
            if (code === 0) {
              resolve(outputPath);
            } else {
              reject(new Error(`FFmpeg failed: ${stderr}`));
            }
          });
        })
        .catch(reject);
    });
  }

  /**
   * Mix audio files (voice + BGM)
   */
  async mixAudio(voiceFile, bgmFile, outputPath, voiceVolume = 1.0, bgmVolume = 0.25) {
    return new Promise((resolve, reject) => {
      const args = [
        '-i', voiceFile,
        '-i', bgmFile,
        '-filter_complex',
        `[0:a]volume=${voiceVolume}[voice];[1:a]volume=${bgmVolume}[bgm];[voice][bgm]amix=inputs=2:duration=first[a]`,
        '-map', '[a]',
        '-c:a', 'aac',
        '-b:a', '128k',
        outputPath,
        '-y'
      ];

      const ffmpeg = spawn(this.ffmpegPath, args);
      let stderr = '';

      ffmpeg.stderr.on('data', (data) => {
        stderr += data.toString();
      });

      ffmpeg.on('close', (code) => {
        if (code === 0) {
          resolve(outputPath);
        } else {
          reject(new Error(`FFmpeg audio mix failed: ${stderr}`));
        }
      });
    });
  }

  /**
   * Add video and audio together
   */
  async addAudioToVideo(videoFile, audioFile, outputPath) {
    return new Promise((resolve, reject) => {
      const args = [
        '-i', videoFile,
        '-i', audioFile,
        '-c:v', 'copy',
        '-c:a', 'aac',
        '-map', '0:v:0',
        '-map', '1:a:0',
        outputPath,
        '-y'
      ];

      const ffmpeg = spawn(this.ffmpegPath, args);
      let stderr = '';

      ffmpeg.stderr.on('data', (data) => {
        stderr += data.toString();
      });

      ffmpeg.on('close', (code) => {
        if (code === 0) {
          resolve(outputPath);
        } else {
          reject(new Error(`FFmpeg add audio failed: ${stderr}`));
        }
      });
    });
  }

  /**
   * Add subtitles to video
   */
  async addSubtitles(videoFile, subtitles, settings, outputPath) {
    return new Promise((resolve, reject) => {
      const fontFile = settings.fontFile || 'NanumGothic.ttf';
      const fontSize = settings.fontSize || 35;
      const fontColor = settings.fontColor || 'yellow';
      const borderWidth = settings.borderWidth || 3;

      // Build drawtext filters
      const filters = [];
      
      // Title (always on top)
      if (settings.title) {
        filters.push(
          `drawtext=fontfile=${fontFile}:text='${settings.title}':` +
          `fontcolor=white:fontsize=40:box=1:boxcolor=black@0.6:boxborderw=8:` +
          `x=(w-text_w)/2:y=50`
        );
      }

      // Subtitles (time-based)
      subtitles.forEach((sub, index) => {
        filters.push(
          `drawtext=fontfile=${fontFile}:text='${sub.text}':` +
          `fontcolor=${fontColor}:fontsize=${fontSize}:borderw=${borderWidth}:bordercolor=black:` +
          `x=(w-text_w)/2:y=h-150:` +
          `enable='between(t,${sub.startTime},${sub.endTime})'`
        );
      });

      const filterComplex = filters.join(',');

      const args = [
        '-i', videoFile,
        '-vf', filterComplex,
        '-c:a', 'copy',
        outputPath,
        '-y'
      ];

      const ffmpeg = spawn(this.ffmpegPath, args);
      let stderr = '';

      ffmpeg.stderr.on('data', (data) => {
        stderr += data.toString();
      });

      ffmpeg.on('close', (code) => {
        if (code === 0) {
          resolve(outputPath);
        } else {
          reject(new Error(`FFmpeg subtitle failed: ${stderr}`));
        }
      });
    });
  }

  /**
   * Scale video to 9:16 (shorts format)
   */
  async scaleToShorts(videoFile, outputPath) {
    return new Promise((resolve, reject) => {
      const args = [
        '-i', videoFile,
        '-vf', 'scale=720:1280:force_original_aspect_ratio=decrease,pad=720:1280:(ow-iw)/2:(oh-ih)/2:black',
        '-c:a', 'copy',
        outputPath,
        '-y'
      ];

      const ffmpeg = spawn(this.ffmpegPath, args);
      let stderr = '';

      ffmpeg.stderr.on('data', (data) => {
        stderr += data.toString();
      });

      ffmpeg.on('close', (code) => {
        if (code === 0) {
          resolve(outputPath);
        } else {
          reject(new Error(`FFmpeg scale failed: ${stderr}`));
        }
      });
    });
  }

  /**
   * Get video duration
   */
  async getDuration(videoFile) {
    return new Promise((resolve, reject) => {
      const args = [
        '-v', 'error',
        '-show_entries', 'format=duration',
        '-of', 'default=noprint_wrappers=1:nokey=1',
        videoFile
      ];

      const ffprobe = spawn(this.ffprobePath, args);
      let stdout = '';

      ffprobe.stdout.on('data', (data) => {
        stdout += data.toString();
      });

      ffprobe.on('close', (code) => {
        if (code === 0) {
          resolve(parseFloat(stdout.trim()));
        } else {
          reject(new Error('Failed to get duration'));
        }
      });
    });
  }

  /**
   * Full render pipeline
   */
  async renderShorts(scenes, voiceFiles, bgmFile, settings, outputDir) {
    try {
      const timestamp = Date.now();
      
      // Step 1: Concatenate videos
      const videoFiles = scenes.map(s => s.videoPath);
      const mergedVideo = path.join(outputDir, `merged_${timestamp}.mp4`);
      await this.concatenateVideos(videoFiles, mergedVideo);

      // Step 2: Concatenate voices
      const mergedVoice = path.join(outputDir, `voice_${timestamp}.mp3`);
      await this.concatenateVideos(voiceFiles, mergedVoice);

      // Step 3: Mix voice with BGM
      const mixedAudio = path.join(outputDir, `audio_${timestamp}.mp3`);
      await this.mixAudio(mergedVoice, bgmFile, mixedAudio);

      // Step 4: Scale video to 9:16
      const scaledVideo = path.join(outputDir, `scaled_${timestamp}.mp4`);
      await this.scaleToShorts(mergedVideo, scaledVideo);

      // Step 5: Add audio to video
      const videoWithAudio = path.join(outputDir, `with_audio_${timestamp}.mp4`);
      await this.addAudioToVideo(scaledVideo, mixedAudio, videoWithAudio);

      // Step 6: Add subtitles
      const finalVideo = path.join(outputDir, `final_${timestamp}.mp4`);
      await this.addSubtitles(videoWithAudio, settings.subtitles, settings, finalVideo);

      // Cleanup intermediate files
      await Promise.all([
        fs.unlink(mergedVideo).catch(() => {}),
        fs.unlink(mergedVoice).catch(() => {}),
        fs.unlink(mixedAudio).catch(() => {}),
        fs.unlink(scaledVideo).catch(() => {}),
        fs.unlink(videoWithAudio).catch(() => {})
      ]);

      return finalVideo;
    } catch (error) {
      throw new Error(`Render pipeline failed: ${error.message}`);
    }
  }
}

module.exports = new FFmpegService();
