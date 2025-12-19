// 로컬 FFmpeg 기반 비디오 렌더러
// Shotstack API 비용 제로 - 완전 무료 로컬 처리
import ffmpeg from 'fluent-ffmpeg';
import path from 'path';
import fs from 'fs/promises';
import { fileURLToPath } from 'url';
import { createWriteStream, existsSync } from 'fs';
import axios from 'axios';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// 출력 디렉토리 설정
const OUTPUT_DIR = process.env.OUTPUT_DIR || '/tmp/outputs/videos';
const TEMP_DIR = path.join(OUTPUT_DIR, 'temp');

/**
 * 비디오 렌더러 클래스
 * - 로컬 FFmpeg 사용으로 API 비용 제로
 * - 자막/제목 2줄 중앙 정렬 지원
 * - 배경 이미지, 음악, 효과 지원
 */
class VideoRenderer {
  constructor() {
    this.ensureDirs();
  }

  /**
   * 필요한 디렉토리 생성
   */
  async ensureDirs() {
    try {
      await fs.mkdir(OUTPUT_DIR, { recursive: true });
      await fs.mkdir(TEMP_DIR, { recursive: true });
      console.log('✅ 비디오 출력 디렉토리 준비 완료');
    } catch (error) {
      console.error('❌ 디렉토리 생성 실패:', error);
    }
  }

  /**
   * URL에서 파일 다운로드 (로컬 파일 경로도 지원)
   */
  async downloadFile(url, outputPath) {
    try {
      // 프록시 URL인 경우 (로컬 API 서버로 변환)
      if (url.startsWith('/api/')) {
        url = `http://localhost:4001${url}`;
        console.log(`🔗 프록시 URL 변환: ${url}`);
      }
      
      // 로컬 파일 경로인 경우 (/ 또는 ./ 로 시작하지만 /api/ 제외)
      if ((url.startsWith('/') || url.startsWith('./') || url.startsWith('../')) && 
          !url.startsWith('http://') && !url.startsWith('https://')) {
        // 상대 경로를 절대 경로로 변환
        let sourcePath = url;
        if (url.startsWith('/outputs/')) {
          // /outputs/ 경로는 /tmp/outputs/로 변환
          sourcePath = path.join('/tmp', url);
        } else if (!path.isAbsolute(url)) {
          sourcePath = path.resolve(url);
        }
        
        // 파일이 존재하는지 확인
        if (!existsSync(sourcePath)) {
          throw new Error(`로컬 파일이 존재하지 않습니다: ${sourcePath}`);
        }
        
        // 파일 복사
        await fs.copyFile(sourcePath, outputPath);
        console.log(`✅ 로컬 파일 복사 완료: ${path.basename(sourcePath)}`);
        return;
      }
      
      // HTTP/HTTPS URL인 경우
      const response = await axios({
        method: 'GET',
        url: url,
        responseType: 'stream'
      });

      const writer = createWriteStream(outputPath);
      response.data.pipe(writer);

      return new Promise((resolve, reject) => {
        writer.on('finish', resolve);
        writer.on('error', reject);
      });
    } catch (error) {
      console.error(`❌ 파일 다운로드 실패: ${url}`, error.message);
      throw error;
    }
  }

  /**
   * 비디오 파일에 오디오 스트림이 있는지 확인
   */
  async checkHasAudio(videoPath) {
    return new Promise((resolve) => {
      ffmpeg.ffprobe(videoPath, (err, metadata) => {
        if (err) {
          console.warn(`⚠️ ffprobe 실패, 오디오 없음으로 간주: ${err.message}`);
          resolve(false);
          return;
        }
        
        // 오디오 스트림 찾기
        const hasAudio = metadata.streams.some(stream => stream.codec_type === 'audio');
        resolve(hasAudio);
      });
    });
  }

  /**
   * 폰트 파일 경로 가져오기 (폴백 지원)
   */
  getFontPath(fontFamily) {
    console.log(`🔍 폰트 검색: "${fontFamily}"`);
    
    // path는 이미 상단에 import됨
    const customFontsPath = path.join(__dirname, '../..', 'fonts');
    
    // 폰트 매핑: 요청된 폰트 -> { path, file }
    const fontMap = {
      // === 커스텀 Google Fonts (다운로드한 실제 폰트) ===
      'BlackHanSans': { path: customFontsPath, file: 'BlackHanSans-Regular.ttf' },
      'DoHyeon': { path: customFontsPath, file: 'DoHyeon-Regular.ttf' },
      'Jua': { path: customFontsPath, file: 'Jua-Regular.ttf' },
      'Gaegu': { path: customFontsPath, file: 'Gaegu-Regular.ttf' },
      'GaeguBold': { path: customFontsPath, file: 'Gaegu-Bold.ttf' },
      'CuteFont': { path: customFontsPath, file: 'CuteFont-Regular.ttf' },
      'KirangHaerang': { path: customFontsPath, file: 'KirangHaerang-Regular.ttf' },
      'GamjaFlower': { path: customFontsPath, file: 'GamjaFlower-Regular.ttf' },
      'YeonSung': { path: customFontsPath, file: 'YeonSung-Regular.ttf' },
      'Stylish': { path: customFontsPath, file: 'Stylish-Regular.ttf' },
      'Sunflower': { path: customFontsPath, file: 'Sunflower-Light.ttf' },
      'SunflowerMedium': { path: customFontsPath, file: 'Sunflower-Medium.ttf' },
      'SunflowerBold': { path: customFontsPath, file: 'Sunflower-Bold.ttf' },
      
      // === Nanum 계열 (시스템 설치) ===
      'NanumGothicBold': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumGothicBold.ttf' },
      'NanumGothic': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumGothic.ttf' },
      'NanumBarunGothicBold': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumBarunGothicBold.ttf' },
      'NanumBarunGothic': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumBarunGothic.ttf' },
      'NanumMyeongjoBold': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumMyeongjoBold.ttf' },
      'NanumMyeongjo': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumMyeongjo.ttf' },
      'NanumSquare': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumSquareR.ttf' },
      'NanumSquareB': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumSquareB.ttf' },
      'NanumSquareRound': { path: '/usr/share/fonts/truetype/nanum', file: 'NanumSquareRoundR.ttf' },
      
      // === Noto Sans CJK KR (시스템 설치) ===
      'Noto Sans KR': { path: '/usr/share/fonts/opentype/noto', file: 'NotoSansCJK-Regular.ttc' },
      'Noto Sans KR Bold': { path: '/usr/share/fonts/opentype/noto', file: 'NotoSansCJK-Bold.ttc' },
      'Noto Sans KR Medium': { path: '/usr/share/fonts/opentype/noto', file: 'NotoSansCJK-Medium.ttc' },
      'Noto Sans KR Black': { path: '/usr/share/fonts/opentype/noto', file: 'NotoSansCJK-Black.ttc' },
      'Noto Serif KR': { path: '/usr/share/fonts/opentype/noto', file: 'NotoSerifCJK-Regular.ttc' },
    };

    // 폰트 찾기
    const fontInfo = fontMap[fontFamily];
    
    if (fontInfo) {
      const fullPath = `${fontInfo.path}/${fontInfo.file}`;
      console.log(`   ✅ 매핑됨: ${fullPath}`);
      return fullPath;
    }
    
    // 기본 폴백: NanumGothicBold (안정적인 한글 폰트)
    const fallbackPath = '/usr/share/fonts/truetype/nanum/NanumGothicBold.ttf';
    console.log(`   ⚠️ 폰트 없음, 폴백 사용: ${fallbackPath}`);
    return fallbackPath;
  }

  /**
   * 텍스트를 2줄로 분리 (한글 기준 약 20자)
   */
  splitTextToTwoLines(text, maxCharsPerLine = 20) {
    console.log(`📝 텍스트 분리 시도: "${text}" (길이: ${text.length}, 최대: ${maxCharsPerLine})`);
    
    if (text.length <= maxCharsPerLine) {
      console.log(`   ✅ 짧은 텍스트, 분리 안함`);
      return text;
    }

    // 중간 지점 찾기
    const midPoint = Math.floor(text.length / 2);
    
    // 띄어쓰기나 구두점을 찾아서 자연스럽게 나누기
    let splitPoint = midPoint;
    for (let i = midPoint; i < text.length && i < midPoint + 10; i++) {
      if (text[i] === ' ' || text[i] === ',' || text[i] === '.' || text[i] === '!' || text[i] === '?') {
        splitPoint = i + 1;
        break;
      }
    }

    const firstLine = text.substring(0, splitPoint).trim();
    const secondLine = text.substring(splitPoint).trim();
    const result = `${firstLine}\n${secondLine}`;
    
    console.log(`   ✂️ 텍스트 분리 완료: "${firstLine}" / "${secondLine}"`);
    
    return result;
  }

  /**
   * 자막 텍스트를 FFmpeg 필터 형식으로 변환
   * 2줄 중앙 정렬, 그림자 효과, 테두리 지원
   */
  createSubtitleFilter(text, settings = {}) {
    console.log(`\n🎨 [자막 필터 생성] 원본 텍스트: "${text}"`);
    
    const {
      fontFamily = 'NanumGothicBold',
      fontSize = 56,
      color,          // 프론트엔드에서 color로 전달
      fontColor,      // 또는 fontColor로 전달 (호환성)
      yOffset = 250,
      strokeWidth,    // 프론트엔드에서 strokeWidth로 전달
      strokeColor,    // 프론트엔드에서 strokeColor로 전달
      borderWidth,    // 또는 borderWidth로 전달 (호환성)
      borderColor     // 또는 borderColor로 전달 (호환성)
    } = settings;
    
    // color/fontColor 매핑 (color 우선)
    const finalFontColor = color !== undefined ? color : (fontColor !== undefined ? fontColor : 'white');
    
    // strokeWidth/strokeColor를 borderWidth/borderColor로 매핑
    const finalBorderWidth = strokeWidth !== undefined ? strokeWidth : (borderWidth !== undefined ? borderWidth : 4);
    const finalBorderColor = strokeColor !== undefined ? strokeColor : (borderColor !== undefined ? borderColor : 'black');
    
    // 그림자 강제 제거 (사용자 요청)
    const shadowX = 0;
    const shadowY = 0;

    console.log(`   설정: fontSize=${fontSize}, fontFamily=${fontFamily}, fontColor=${finalFontColor}, yOffset=${yOffset}, borderWidth=${finalBorderWidth}, borderColor=${finalBorderColor}`);

    // 텍스트를 한 줄로 유지 (2줄 분리 안함 - "n" 글자 방지)
    const escapedText = text
      .replace(/\\/g, '\\\\')
      .replace(/'/g, "\\'")
      .replace(/:/g, '\\:');

    console.log(`   이스케이프 후: "${escapedText}"`);

    // 폰트 파일 경로 가져오기
    const fontPath = this.getFontPath(fontFamily);
    console.log(`   폰트 경로: ${fontPath}`);

    // FFmpeg drawtext 필터
    const filter = `drawtext=` +
      `text='${escapedText}':` +
      `fontfile=${fontPath}:` +
      `fontsize=${fontSize}:` +
      `fontcolor=${finalFontColor}:` +
      `x=(w-text_w)/2:` +
      `y=h-${yOffset}:` +
      `line_spacing=10:` +
      `text_align=C:` +
      `borderw=${finalBorderWidth}:` +
      `bordercolor=${finalBorderColor}:` +
      `shadowx=${shadowX}:` +
      `shadowy=${shadowY}`;
    
    console.log(`   ✅ 최종 필터: ${filter.substring(0, 150)}...`);
    return filter;
  }

  /**
   * 제목 텍스트를 FFmpeg 필터 형식으로 변환
   * 2줄 중앙 정렬
   */
  createTitleFilter(text, settings = {}) {
    console.log(`\n🎬 [제목 필터 생성] 원본 텍스트: "${text}"`);
    
    const {
      fontFamily = 'NanumGothicBold',
      fontSize = 72,
      color,          // 프론트엔드에서 color로 전달
      fontColor,      // 또는 fontColor로 전달 (호환성)
      yPosition = 280,
      strokeWidth,    // 프론트엔드에서 strokeWidth로 전달
      strokeColor,    // 프론트엔드에서 strokeColor로 전달
      borderWidth,    // 또는 borderWidth로 전달 (호환성)
      borderColor     // 또는 borderColor로 전달 (호호성)
    } = settings;
    
    // color/fontColor 매핑 (color 우선)
    const finalFontColor = color !== undefined ? color : (fontColor !== undefined ? fontColor : 'yellow');
    
    // strokeWidth/strokeColor를 borderWidth/borderColor로 매핑
    const finalBorderWidth = strokeWidth !== undefined ? strokeWidth : (borderWidth !== undefined ? borderWidth : 5);
    const finalBorderColor = strokeColor !== undefined ? strokeColor : (borderColor !== undefined ? borderColor : 'black');
    
    // 그림자 강제 제거 (사용자 요청)
    const shadowX = 0;
    const shadowY = 0;

    console.log(`   설정: fontSize=${fontSize}, fontFamily=${fontFamily}, fontColor=${finalFontColor}, yPosition=${yPosition}, borderWidth=${finalBorderWidth}, borderColor=${finalBorderColor}`);

    // 텍스트를 한 줄로 유지 (2줄 분리 안함 - "n" 글자 방지)
    const escapedText = text
      .replace(/\\/g, '\\\\')
      .replace(/'/g, "\\'")
      .replace(/:/g, '\\:');

    console.log(`   이스케이프 후: "${escapedText}"`);

    // 폰트 파일 경로 가져오기
    const fontPath = this.getFontPath(fontFamily);
    console.log(`   폰트 경로: ${fontPath}`);

    const filter = `drawtext=` +
      `text='${escapedText}':` +
      `fontfile=${fontPath}:` +
      `fontsize=${fontSize}:` +
      `fontcolor=${finalFontColor}:` +
      `x=(w-text_w)/2:` +
      `y=${yPosition}:` +
      `line_spacing=10:` +
      `text_align=C:` +
      `borderw=${finalBorderWidth}:` +
      `bordercolor=${finalBorderColor}:` +
      `shadowx=${shadowX}:` +
      `shadowy=${shadowY}`;
    
    console.log(`   ✅ 최종 필터: ${filter.substring(0, 150)}...`);
    return filter;
  }

  /**
   * 이미지 효과 필터 생성 (Ken Burns, Pan, Zoom 등)
   * hasBackground=true: 오버레이용 (비율 유지), false: 전체 화면용 (1080x1920 crop)
   */
  createImageEffectFilter(effect = 'none', intensity = 'medium', duration = 3.5, hasBackground = false) {
    console.log(`🎬 이미지 효과: ${effect} (강도: ${intensity}, 배경: ${hasBackground ? '있음' : '없음'})`);
    
    // 강도별 파라미터
    const intensityParams = {
      'low': { zoomFactor: 1.1, panDistance: 50 },
      'medium': { zoomFactor: 1.2, panDistance: 100 },
      'high': { zoomFactor: 1.3, panDistance: 150 }
    };
    
    const params = intensityParams[intensity] || intensityParams['medium'];
    const fps = 30; // 프레임레이트
    const frames = Math.floor(duration * fps);
    
    // 배경이 있으면 오버레이용으로 비율 유지하며 효과 적용
    if (hasBackground) {
      // 오버레이 모드: 비율 유지하며 효과 적용 (최대 1080x1920 이내)
      switch(effect) {
        case 'zoom-in':
          // 줌인: 비율 유지하며 확대 (1080 이내)
          return `scale=w='if(gt(iw,ih),min(1080,iw*min(1+((${params.zoomFactor}-1)*t/${duration}),${params.zoomFactor})),-1)':h='if(gt(ih,iw),min(1920,ih*min(1+((${params.zoomFactor}-1)*t/${duration}),${params.zoomFactor})),-1)'`;
          
        case 'zoom-out':
          // 줌아웃: 비율 유지하며 축소
          return `scale=w='if(gt(iw,ih),min(1080,iw*max(1,${params.zoomFactor}-(${params.zoomFactor}-1)*t/${duration})),-1)':h='if(gt(ih,iw),min(1920,ih*max(1,${params.zoomFactor}-(${params.zoomFactor}-1)*t/${duration})),-1)'`;
          
        case 'pan-left':
        case 'pan-right':
        case 'pan-up':
        case 'pan-down':
        case 'pan-lr':
        case 'pan-rl':
          // 패닝 효과: 배경 있을 때는 간단히 스케일만 적용 (패닝은 전체 화면용)
          return `scale=1080:1920:force_original_aspect_ratio=decrease`;
          
        case 'ken-burns':
        case 'ken-burns-center':
          // Ken Burns: 비율 유지하며 중앙 줌인
          return `scale=w='if(gt(iw,ih),min(1080,iw*min(1+((${params.zoomFactor}-1)*t/${duration}),${params.zoomFactor})),-1)':h='if(gt(ih,iw),min(1920,ih*min(1+((${params.zoomFactor}-1)*t/${duration}),${params.zoomFactor})),-1)'`;
          
        case 'rotate-slow':
          // 회전: 비율 유지하며 회전
          return `rotate=a='PI*2*t/${duration}/4':fillcolor=none,scale=1080:1920:force_original_aspect_ratio=decrease`;
          
        case 'none':
        default:
          // 효과 없음: 비율 유지
          return `scale=1080:1920:force_original_aspect_ratio=decrease`;
      }
    } else {
      // 전체 화면 모드: 화면 채우며 효과 적용 (1080x1920 crop)
      switch(effect) {
        case 'zoom-in':
          // 줌인 효과: 점점 확대
          return `scale=w=iw*min(1+((${params.zoomFactor}-1)*n/${frames})\\,${params.zoomFactor}):h=ih*min(1+((${params.zoomFactor}-1)*n/${frames})\\,${params.zoomFactor}),crop=1080:1920:(iw-1080)/2:(ih-1920)/2`;
          
        case 'zoom-out':
          // 줌아웃 효과: 확대된 상태에서 축소
          return `scale=w=iw*min(${params.zoomFactor}-(${params.zoomFactor}-1)*n/${frames}\\,${params.zoomFactor}):h=ih*min(${params.zoomFactor}-(${params.zoomFactor}-1)*n/${frames}\\,${params.zoomFactor}),crop=1080:1920:(iw-1080)/2:(ih-1920)/2`;
          
        case 'pan-left':
          // 좌측으로 패닝
          return `scale=1280:1920,crop=1080:1920:min(iw-1080\\,${params.panDistance}*n/${frames}):0`;
          
        case 'pan-right':
          // 우측으로 패닝
          return `scale=1280:1920,crop=1080:1920:max(0\\,iw-1080-${params.panDistance}*n/${frames}):0`;
          
        case 'pan-up':
          // 위로 패닝
          return `scale=1080:2200,crop=1080:1920:0:max(0\\,ih-1920-${params.panDistance}*n/${frames})`;
          
        case 'pan-down':
          // 아래로 패닝
          return `scale=1080:2200,crop=1080:1920:0:min(ih-1920\\,${params.panDistance}*n/${frames})`;
          
        case 'pan-lr':
          // 좌우 패닝 (좌 -> 우)
          return `scale=1280:1920,crop=1080:1920:min(iw-1080\\,${params.panDistance*2}*n/${frames}):0`;
          
        case 'pan-rl':
          // 우좌 패닝 (우 -> 좌)
          return `scale=1280:1920,crop=1080:1920:max(0\\,iw-1080-${params.panDistance*2}*n/${frames}):0`;
          
        case 'ken-burns':
          // Ken Burns 효과: 줌인 + 패닝
          return `scale=w=iw*min(1+((${params.zoomFactor}-1)*n/${frames})\\,${params.zoomFactor}):h=ih*min(1+((${params.zoomFactor}-1)*n/${frames})\\,${params.zoomFactor}),crop=1080:1920:min((iw-1080)/2+(${params.panDistance}*n/${frames})\\,iw-1080):(ih-1920)/2`;
          
        case 'ken-burns-center':
          // Ken Burns 중앙 줌인
          return `scale=w=iw*min(1+((${params.zoomFactor}-1)*n/${frames})\\,${params.zoomFactor}):h=ih*min(1+((${params.zoomFactor}-1)*n/${frames})\\,${params.zoomFactor}),crop=1080:1920:(iw-1080)/2:(ih-1920)/2`;
          
        case 'rotate-slow':
          // 느린 회전 (시계방향)
          return `rotate=a='PI*2*n/${frames}/4':fillcolor=black,scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920`;
          
        case 'none':
        default:
          // 효과 없음: 화면 채움
          return `scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920`;
      }
    }
  }

  /**
   * 단일 장면 비디오 생성
   * 이미지 + 음성 + 자막 + 제목 결합
   */
  async createSceneVideo(scene, sceneIndex, settings = {}) {
    const sceneId = `scene_${Date.now()}_${sceneIndex}`;
    const outputPath = path.join(TEMP_DIR, `${sceneId}.mp4`);

    console.log(`🎬 장면 ${sceneIndex + 1} 생성 중...`);

    try {
      // 1. 원본 이미지 다운로드
      const imagePath = path.join(TEMP_DIR, `${sceneId}_image.jpg`);
      if (scene.imageUrl) {
        await this.downloadFile(scene.imageUrl, imagePath);
      }

      // 2. 배경 이미지 다운로드 (있을 경우)
      let bgImagePath = null;
      if (settings.bgImage && settings.bgImage.url) {
        bgImagePath = path.join(TEMP_DIR, `${sceneId}_bgimage.jpg`);
        await this.downloadFile(settings.bgImage.url, bgImagePath);
      }

      // 3. 음성 다운로드
      const audioPath = path.join(TEMP_DIR, `${sceneId}_audio.mp3`);
      if (scene.audioUrl) {
        await this.downloadFile(scene.audioUrl, audioPath);
      }

      // 4. FFmpeg 필터 생성
      const filters = [];

      // 이미지 효과 설정 가져오기
      const imageEffect = settings.imageEffect || 'none';
      const effectIntensity = settings.effectIntensity || 'medium';
      const sceneDuration = scene.duration || 3.5;
      
      // 배경 이미지 처리 (맨 앞 레이어)
      if (bgImagePath) {
        // 배경 이미지: 화면 전체를 채움 (효과 없음)
        filters.push(`[0:v]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920[bg]`);
        
        // 원본 이미지: 배경이 있어도 이미지 효과 적용 (장면 이미지에만 효과)
        console.log(`   🎨 장면 이미지 효과 적용: ${imageEffect} (배경 있음)`);
        const imageEffectFilter = this.createImageEffectFilter(imageEffect, effectIntensity, sceneDuration, true);
        filters.push(`[1:v]${imageEffectFilter}[overlay]`);
        
        // 오버레이: 원본 이미지를 배경 위에 중앙 배치
        const opacity = settings.bgImage.opacity || 1.0;
        filters.push(`[bg][overlay]overlay=(W-w)/2:(H-h)/2:format=auto,format=yuv420p[main]`);
      } else {
        // 배경 이미지 없으면 원본 이미지에 효과 적용 (화면 전체 채움, hasBackground=false)
        const imageEffectFilter = this.createImageEffectFilter(imageEffect, effectIntensity, sceneDuration, false);
        filters.push(`[0:v]${imageEffectFilter}[main]`);
      }

      // 자막 추가
      if (scene.subtitle) {
        const subtitleFilter = this.createSubtitleFilter(
          scene.subtitle,
          settings.subtitleSettings || {}
        );
        filters.push(`[main]${subtitleFilter}[sub]`);
      }

      // 제목 추가
      if (scene.title) {
        const titleFilter = this.createTitleFilter(
          scene.title,
          settings.titleSettings || {}
        );
        const inputLabel = scene.subtitle ? '[sub]' : '[main]';
        filters.push(`${inputLabel}${titleFilter}[final]`);
      }

      // 4. FFmpeg 실행
      return new Promise((resolve, reject) => {
        const command = ffmpeg();
        
        // 배경 이미지가 있으면 먼저 추가 (input 0)
        if (bgImagePath) {
          command
            .input(bgImagePath)
            .inputOptions(['-loop 1']);
        }
        
        // 원본 이미지 추가 (배경 있으면 input 1, 없으면 input 0)
        command
          .input(imagePath)
          .inputOptions(['-loop 1']);

        // 오디오 추가 (배경 있으면 input 2, 없으면 input 1)
        if (scene.audioUrl) {
          command.input(audioPath);
        }

        // Output options (오디오 유무에 따라 다르게 설정)
        // 최종 출력 레이블 결정: 제목 있으면 [final], 자막만 있으면 [sub], 둘 다 없으면 [main]
        let finalLabel = '[main]';
        if (scene.title) {
          finalLabel = '[final]';
        } else if (scene.subtitle) {
          finalLabel = '[sub]';
        }
        
        const outputOpts = [
          '-map', finalLabel
        ];
        
        // 오디오가 있을 때만 오디오 매핑 추가
        // 배경 이미지가 있으면 오디오는 input 2, 없으면 input 1
        if (scene.audioUrl) {
          const audioIndex = bgImagePath ? '2' : '1';
          outputOpts.push('-map', `${audioIndex}:a`);
        }
        
        // 비디오 및 오디오 코덱 옵션
        outputOpts.push(
          '-c:v', 'libx264',
          '-preset', 'medium',
          '-crf', '23',
          '-pix_fmt', 'yuv420p',
          '-shortest',
          '-t', String(scene.duration || '3')
        );
        
        // 오디오 코덱 (오디오가 있을 때만)
        if (scene.audioUrl) {
          outputOpts.push('-c:a', 'aac', '-b:a', '128k');
        }
        
        command
          .complexFilter(filters)
          .outputOptions(outputOpts)
          .output(outputPath)
          .on('start', (cmd) => {
            console.log(`   FFmpeg 시작: ${cmd}`);
          })
          .on('progress', (progress) => {
            console.log(`   진행률: ${Math.round(progress.percent || 0)}%`);
          })
          .on('end', () => {
            console.log(`✅ 장면 ${sceneIndex + 1} 완료: ${outputPath}`);
            // 임시 파일 정리
            this.cleanupTempFiles([imagePath, audioPath]).catch(console.error);
            resolve(outputPath);
          })
          .on('error', (error) => {
            console.error(`❌ 장면 ${sceneIndex + 1} 실패:`, error);
            reject(error);
          })
          .run();
      });

    } catch (error) {
      console.error(`❌ 장면 ${sceneIndex + 1} 생성 실패:`, error);
      throw error;
    }
  }

  /**
   * 모든 장면 비디오를 하나로 결합
   * 배경 음악 추가 지원
   */
  async concatenateScenes(scenePaths, settings = {}) {
    const videoId = `video_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;
    const outputPath = path.join(OUTPUT_DIR, `${videoId}.mp4`);
    const concatListPath = path.join(TEMP_DIR, `${videoId}_concat.txt`);

    console.log(`🔗 ${scenePaths.length}개 장면 결합 중...`);

    try {
      // 1. concat 리스트 파일 생성
      const concatList = scenePaths
        .map(p => `file '${p}'`)
        .join('\n');
      await fs.writeFile(concatListPath, concatList);

      // 2. 배경 음악 다운로드 (있을 경우)
      let bgMusicPath = null;
      if (settings.bgMusic && settings.bgMusic.url) {
        bgMusicPath = path.join(TEMP_DIR, `${videoId}_bgmusic.mp3`);
        await this.downloadFile(settings.bgMusic.url, bgMusicPath);
      }

      // 3. 첫 번째 장면 비디오의 오디오 스트림 확인
      const hasAudio = await this.checkHasAudio(scenePaths[0]);
      console.log(`   입력 비디오 오디오 스트림: ${hasAudio ? '있음' : '없음'}`);

      // 4. FFmpeg로 결합
      return new Promise((resolve, reject) => {
        const command = ffmpeg()
          .input(concatListPath)
          .inputOptions(['-f', 'concat', '-safe', '0']);

        // 배경 음악 추가
        if (bgMusicPath) {
          command.input(bgMusicPath);
          
          if (hasAudio) {
            // 입력 비디오에 오디오가 있으면 믹싱
            command.complexFilter([
              '[0:a]volume=1.0[voice]',
              '[1:a]volume=0.3[music]',
              '[voice][music]amix=inputs=2:duration=first[aout]'
            ]);
            command.outputOptions(['-map', '0:v', '-map', '[aout]']);
          } else {
            // 입력 비디오에 오디오가 없으면 배경 음악만 사용
            console.log('   입력 비디오에 오디오 없음, 배경 음악만 사용');
            command.complexFilter([
              '[1:a]volume=0.5[aout]'  // 음성 없으므로 배경 음악 볼륨 증가
            ]);
            command.outputOptions(['-map', '0:v', '-map', '[aout]']);
          }
        } else if (!hasAudio) {
          // 배경 음악도 없고 입력 오디오도 없으면 비디오만
          console.log('   오디오 없는 비디오 결합');
          command.outputOptions(['-map', '0:v']);
        }

        command
          .outputOptions([
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '23',
            '-c:a', 'aac',
            '-b:a', '128k'
          ])
          .output(outputPath)
          .on('start', (cmd) => {
            console.log(`🎬 최종 결합 시작: ${cmd}`);
          })
          .on('progress', (progress) => {
            console.log(`   진행률: ${Math.round(progress.percent || 0)}%`);
          })
          .on('end', async () => {
            console.log(`✅ 최종 비디오 생성 완료: ${outputPath}`);
            
            // 임시 파일 정리
            await this.cleanupTempFiles([
              concatListPath,
              bgMusicPath,
              ...scenePaths
            ]);

            // 파일 정보 가져오기
            const stats = await fs.stat(outputPath);
            
            resolve({
              videoId,
              videoPath: outputPath,
              videoUrl: `/outputs/videos/${videoId}.mp4`,
              size: stats.size,
              duration: settings.totalDuration || scenePaths.length * 3
            });
          })
          .on('error', (error) => {
            console.error('❌ 비디오 결합 실패:', error);
            reject(error);
          })
          .run();
      });

    } catch (error) {
      console.error('❌ 비디오 결합 중 오류:', error);
      throw error;
    }
  }

  /**
   * 전체 비디오 생성 프로세스
   * 장면 생성 → 결합 → 최종 출력
   */
  async generateVideo(scenes, settings = {}) {
    console.log(`🚀 비디오 생성 시작: ${scenes.length}개 장면`);
    console.log(`📦 받은 scenes:`, JSON.stringify(scenes, null, 2));
    console.log(`📦 받은 settings:`, JSON.stringify(settings, null, 2));
    
    try {
      // 1. 각 장면별 비디오 생성
      const scenePaths = [];
      for (let i = 0; i < scenes.length; i++) {
        const scenePath = await this.createSceneVideo(scenes[i], i, settings);
        scenePaths.push(scenePath);
      }

      // 2. 모든 장면 결합
      const result = await this.concatenateScenes(scenePaths, settings);

      console.log(`🎉 비디오 생성 완료!`);
      console.log(`   Video ID: ${result.videoId}`);
      console.log(`   Path: ${result.videoPath}`);
      console.log(`   Size: ${(result.size / 1024 / 1024).toFixed(2)} MB`);

      return result;

    } catch (error) {
      console.error('❌ 비디오 생성 실패:', error);
      throw error;
    }
  }

  /**
   * 임시 파일 정리
   */
  async cleanupTempFiles(filePaths) {
    for (const filePath of filePaths) {
      if (!filePath) continue;
      try {
        await fs.unlink(filePath);
        console.log(`🗑️  임시 파일 삭제: ${filePath}`);
      } catch (error) {
        // 파일이 없으면 무시
      }
    }
  }

  /**
   * 오래된 임시 파일 정리 (24시간 이상)
   */
  async cleanupOldTempFiles() {
    try {
      const files = await fs.readdir(TEMP_DIR);
      const now = Date.now();
      const maxAge = 24 * 60 * 60 * 1000; // 24시간

      for (const file of files) {
        const filePath = path.join(TEMP_DIR, file);
        const stats = await fs.stat(filePath);
        
        if (now - stats.mtimeMs > maxAge) {
          await fs.unlink(filePath);
          console.log(`🗑️  오래된 임시 파일 삭제: ${file}`);
        }
      }
    } catch (error) {
      console.error('❌ 임시 파일 정리 실패:', error);
    }
  }
}

// 싱글톤 인스턴스
const videoRenderer = new VideoRenderer();

// 정기적으로 오래된 임시 파일 정리 (1시간마다)
setInterval(() => {
  videoRenderer.cleanupOldTempFiles().catch(console.error);
}, 60 * 60 * 1000);

export default videoRenderer;
