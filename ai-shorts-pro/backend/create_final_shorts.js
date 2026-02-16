const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');
const { exec } = require('child_process');
const { promisify } = require('util');
const execAsync = promisify(exec);

const OUTPUT_DIR = '/mnt/music-storage/shorts-videos/output';
const AUDIO_DIR = '/mnt/music-storage/shorts-videos/audio';

const REAL_IMAGES = [
    'https://postfiles.pstatic.net/MjAyNTEyMTJfMjg1/MDAxNzY1NDkyOTEwODUx.N6wZH4q3k0R53_XLdfSctSB4axhoORhdJ8MV_iiRetAg.NC59B09lLgAUx-1eao3z1tY8WkRXSQU9LbyJPpq0rqAg.JPEG/image.jpg?type=w773',
    'https://postfiles.pstatic.net/MjAyNTEyMTJfMjEz/MDAxNzY1NDkyOTIwMTU3.RqUsXVhadKD_Rr-kwDt-abUujqkAIU5ZC-UUkUcogxgg.QQsqZpNsl-hp0cVaBnuweJ0k3qBGhikOaUTu3rnLRRUg.JPEG/image.jpg?type=w773',
    'https://postfiles.pstatic.net/MjAyNTEyMTJfMTY2/MDAxNzY1NDkyOTU4NTU1.6O6g-m04JEIqajqVY8MCCkeiYvqtxTjjU4GzTS2_Gv8g.Agut9L4fv2EHvG_QSnqvROESlFjR0GwGnqStgHPF3IUg.JPEG/image.jpg?type=w773',
    'https://postfiles.pstatic.net/MjAyNTEyMTJfMjQy/MDAxNzY1NDkyOTg2ODkw.1mklBLAK3BUIYz1lAUFA7NDp3vQh4jZqpDAeuIswD0Eg.OwfQqk72Sn7N40_sdKOVloG-QE1eU-Rn15K1udEE_Gkg.JPEG/image.jpg?type=w773',
    'https://postfiles.pstatic.net/MjAyNTEyMTJfMjg1/MDAxNzY1NDkyOTEwODUx.N6wZH4q3k0R53_XLdfSctSB4axhoORhdJ8MV_iiRetAg.NC59B09lLgAUx-1eao3z1tY8WkRXSQU9LbyJPpq0rqAg.JPEG/image.jpg?type=w773'
];

const SCENES = [
    { text: "안녕하세요!\n오늘은 프리미엄\n크리스마스 벽트리를\n소개할게요!", duration: 6 },
    { text: "100cm 사이즈로\n공간 활용이\n정말 좋아요", duration: 6 },
    { text: "장식들이 고급스럽고\n풀한 느낌이\n일품이죠", duration: 6 },
    { text: "LED 조명까지 있어서\n분위기가\n환상적이에요", duration: 6 },
    { text: "좋아요와 구독\n잊지 마세요!\n감사합니다", duration: 6 }
];

async function generateTTS(text, index) {
    const cleanText = text.replace(/\n/g, ' ');
    console.log(`🎤 TTS 생성 [${index}]: ${cleanText.substring(0, 30)}...`);
    const outputPath = path.join(AUDIO_DIR, `final_scene_${index}_${Date.now()}.mp3`);
    
    try {
        await execAsync(`gtts-cli "${cleanText}" --lang ko --output "${outputPath}"`);
        const stats = await fs.stat(outputPath);
        if (stats.size > 1000) {
            console.log(`  ✓ TTS 완료: ${(stats.size/1024).toFixed(1)}KB`);
            return outputPath;
        }
    } catch (error) {
        console.log(`  ⚠️ gtts 실패, espeak 사용`);
    }
    
    await execAsync(`espeak-ng -v ko -w "${outputPath}" "${cleanText}"`);
    return outputPath;
}

async function downloadImage(url, outputPath) {
    try {
        const response = await axios.get(url, { 
            responseType: 'arraybuffer',
            timeout: 10000,
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
        });
        await fs.writeFile(outputPath, response.data);
        return true;
    } catch (error) {
        return false;
    }
}

async function createSceneVideo(imageUrl, audioPath, text, duration, index) {
    console.log(`\n🎬 장면 ${index} 생성 중...`);
    
    const imageFile = path.join(OUTPUT_DIR, `final_scene_${index}_img.jpg`);
    const outputFile = path.join(OUTPUT_DIR, `final_scene_${index}.mp4`);
    
    // 이미지 다운로드
    const downloaded = await downloadImage(imageUrl, imageFile);
    
    if (!downloaded) {
        const colors = [
            ['#667eea', '#764ba2'],
            ['#f093fb', '#f5576c'],
            ['#4facfe', '#00f2fe'],
            ['#43e97b', '#38f9d7'],
            ['#fa709a', '#fee140']
        ];
        const [c1, c2] = colors[index % colors.length];
        await execAsync(`convert -size 1080x1920 gradient:"${c1}-${c2}" "${imageFile}"`);
        console.log(`  ✓ 그라데이션 배경`);
    } else {
        console.log(`  ✓ 이미지 다운로드 성공`);
    }
    
    // 음성 길이
    const { stdout } = await execAsync(`ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "${audioPath}"`);
    const audioDuration = parseFloat(stdout.trim());
    const finalDuration = Math.max(duration, audioDuration + 0.5);
    
    // 자막을 줄바꿈으로 분리
    const lines = text.split('\n');
    const escapedLines = lines.map(line => line.replace(/"/g, '\\"').replace(/'/g, "\\'"));
    
    // FFmpeg 비디오 생성 - drawtext 필터로 자막 추가
    let drawtextFilters = '';
    const lineHeight = 80;
    const startY = 1920 - 400; // 하단에서 400px 위
    
    for (let i = 0; i < escapedLines.length; i++) {
        const y = startY + (i * lineHeight);
        drawtextFilters += `drawtext=text='${escapedLines[i]}':fontfile=/usr/share/fonts/truetype/nanum/NanumGothicBold.ttf:fontsize=70:fontcolor=white:borderw=5:bordercolor=black:x=(w-text_w)/2:y=${y},`;
    }
    drawtextFilters = drawtextFilters.slice(0, -1); // 마지막 콤마 제거
    
    // Ken Burns 효과 + 자막
    const zoomEffect = index % 2 === 0 
        ? `zoompan=z='min(zoom+0.0015,1.1)':d=${Math.floor(finalDuration * 25)}:s=1080x1920`
        : `zoompan=z='if(lte(zoom,1.0),1.1,max(1.0,zoom-0.0015))':d=${Math.floor(finalDuration * 25)}:s=1080x1920`;
    
    const ffmpegCmd = `ffmpeg -loop 1 -i "${imageFile}" -i "${audioPath}" \
        -filter_complex "[0:v]${zoomEffect},colorlevels=rimax=0.9:gimax=0.9:bimax=0.9,${drawtextFilters}[v]" \
        -map "[v]" -map 1:a \
        -c:v libx264 -preset fast -tune stillimage \
        -pix_fmt yuv420p -t ${finalDuration} \
        -c:a aac -b:a 192k -shortest \
        -y "${outputFile}"`;
    
    await execAsync(ffmpegCmd);
    
    const stats = await fs.stat(outputFile);
    console.log(`✅ 장면 ${index} 완료: ${(stats.size/1024).toFixed(0)}KB, ${finalDuration.toFixed(1)}초`);
    
    return { path: outputFile, duration: finalDuration };
}

async function mergeVideos(sceneVideos, outputFilename) {
    console.log('\n🎞️ 최종 비디오 병합 중...');
    
    const concatFile = path.join(OUTPUT_DIR, 'final_concat.txt');
    const concatContent = sceneVideos.map(v => `file '${v.path}'`).join('\n');
    await fs.writeFile(concatFile, concatContent);
    
    const outputPath = path.join(OUTPUT_DIR, outputFilename);
    
    const cmd = `ffmpeg -f concat -safe 0 -i "${concatFile}" \
        -c:v libx264 -profile:v baseline -level 3.0 -preset fast \
        -pix_fmt yuv420p -movflags +faststart \
        -c:a aac -b:a 192k \
        -y "${outputPath}"`;
    
    await execAsync(cmd);
    
    const stats = await fs.stat(outputPath);
    const totalDuration = sceneVideos.reduce((sum, v) => sum + v.duration, 0);
    
    console.log(`✅ 최종 병합 완료: ${(stats.size/1024).toFixed(0)}KB, ${totalDuration.toFixed(1)}초`);
    
    return { path: outputPath, size: stats.size, duration: totalDuration };
}

async function main() {
    try {
        console.log('🚀 최종 AI 쇼츠 생성 시작 (큰 자막 버전)\n');
        
        const sceneVideos = [];
        
        for (let i = 0; i < SCENES.length; i++) {
            const scene = SCENES[i];
            const audioPath = await generateTTS(scene.text, i + 1);
            const sceneVideo = await createSceneVideo(
                REAL_IMAGES[i],
                audioPath,
                scene.text,
                scene.duration,
                i + 1
            );
            sceneVideos.push(sceneVideo);
        }
        
        const timestamp = Date.now();
        const finalVideo = await mergeVideos(sceneVideos, `FINAL_SHORTS_${timestamp}.mp4`);
        
        console.log('\n' + '='.repeat(70));
        console.log('🎉 최종 AI 쇼츠 생성 완료!');
        console.log('='.repeat(70));
        console.log(`📁 파일: ${finalVideo.path}`);
        console.log(`📦 크기: ${(finalVideo.size/1024).toFixed(0)} KB`);
        console.log(`⏱️ 길이: ${finalVideo.duration.toFixed(1)}초`);
        console.log(`🎬 장면: ${sceneVideos.length}개`);
        console.log(`📝 자막: 크게 표시됨 (70px, 굵은 테두리)`);
        console.log(`🌐 다운로드: https://ai-shorts.neuralgrid.kr/videos/${path.basename(finalVideo.path)}`);
        console.log('='.repeat(70));
        
    } catch (error) {
        console.error('❌ 에러:', error);
        console.error(error.stack);
        process.exit(1);
    }
}

main();
