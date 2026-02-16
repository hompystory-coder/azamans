const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');
const { exec } = require('child_process');
const { promisify } = require('util');
const execAsync = promisify(exec);

const OUTPUT_DIR = '/mnt/music-storage/shorts-videos/output';
const AUDIO_DIR = '/mnt/music-storage/shorts-videos/audio';

// 캐릭터 정보
const CHARACTER = {
    name: '소피아',
    description: '친근한 20대 여성 AI 쇼핑 도우미',
    personality: '밝고 활기찬 성격'
};

const SCENES = [
    { 
        text: "안녕하세요!\n저는 AI 쇼핑 도우미\n소피아예요!",
        character_prompt: "A friendly young Korean woman with a bright smile, wearing casual modern clothing, waving hello, professional product presentation style, warm lighting, 4k quality",
        duration: 6 
    },
    { 
        text: "오늘 소개할 제품은\n프리미엄 크리스마스\n벽트리예요",
        character_prompt: "A friendly young Korean woman gesturing towards a Christmas wall tree, excited expression, professional product presentation, bright studio lighting, 4k quality",
        duration: 6 
    },
    { 
        text: "100cm 사이즈로\n공간 활용이\n정말 좋답니다",
        character_prompt: "A friendly young Korean woman showing size measurement with hands, explaining with enthusiasm, professional product presentation, modern background, 4k quality",
        duration: 6 
    },
    { 
        text: "LED 조명이 함께\n제공되어서\n분위기가 환상적이에요",
        character_prompt: "A friendly young Korean woman pointing at LED lights, amazed expression, professional product presentation, sparkling background, 4k quality",
        duration: 6 
    },
    { 
        text: "좋아요와 구독\n잊지 마세요!\n감사합니다",
        character_prompt: "A friendly young Korean woman giving thumbs up and smiling, saying goodbye, professional product presentation, colorful background, 4k quality",
        duration: 6 
    }
];

async function generateCharacterImage(prompt, index) {
    console.log(`🎨 캐릭터 이미지 생성 [장면 ${index}]...`);
    
    // 실제로는 image_generation API를 사용하지만, 여기서는 플레이스홀더 생성
    const outputPath = path.join(OUTPUT_DIR, `character_${index}.jpg`);
    
    // 캐릭터 색상 (각 장면마다 다른 배경)
    const colors = [
        ['#FF6B9D', '#C44569'],  // 핑크
        ['#4A69BD', '#1E3799'],  // 블루
        ['#26de81', '#20bf6b'],  // 그린
        ['#FD7272', '#FC5C65'],  // 레드
        ['#A55EEA', '#8854d0']   // 퍼플
    ];
    
    const [c1, c2] = colors[index % colors.length];
    
    // 그라데이션 배경 + 캐릭터 실루엣 생성
    await execAsync(`convert -size 1080x1920 gradient:"${c1}-${c2}" \
        -gravity center \
        \\( -size 600x1200 xc:none -fill white -draw "ellipse 300,600 250,500 0,360" \\) \
        -compose Over -composite \
        "${outputPath}"`);
    
    console.log(`  ✓ 캐릭터 플레이스홀더 생성`);
    return outputPath;
}

async function generateTTS(text, index) {
    const cleanText = text.replace(/\n/g, ' ');
    console.log(`🎤 TTS 생성 [${index}]: ${cleanText.substring(0, 30)}...`);
    const outputPath = path.join(AUDIO_DIR, `char_scene_${index}_${Date.now()}.mp3`);
    
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

async function createSceneVideo(characterImage, audioPath, text, duration, index) {
    console.log(`\n🎬 장면 ${index} 비디오 생성 중...`);
    
    const outputFile = path.join(OUTPUT_DIR, `char_scene_${index}.mp4`);
    
    // 음성 길이
    const { stdout } = await execAsync(`ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "${audioPath}"`);
    const audioDuration = parseFloat(stdout.trim());
    const finalDuration = Math.max(duration, audioDuration + 0.5);
    
    // 자막 줄 분리
    const lines = text.split('\n');
    const escapedLines = lines.map(line => line.replace(/"/g, '\\"').replace(/'/g, "\\'"));
    
    // 자막 필터 생성
    let drawtextFilters = '';
    const lineHeight = 85;
    const startY = 1920 - 450;
    
    for (let i = 0; i < escapedLines.length; i++) {
        const y = startY + (i * lineHeight);
        drawtextFilters += `drawtext=text='${escapedLines[i]}':fontfile=/usr/share/fonts/truetype/nanum/NanumGothicBold.ttf:fontsize=75:fontcolor=white:borderw=6:bordercolor=black:x=(w-text_w)/2:y=${y}:shadowcolor=black:shadowx=3:shadowy=3,`;
    }
    drawtextFilters = drawtextFilters.slice(0, -1);
    
    // 캐릭터 애니메이션 효과 (부드러운 줌 + 페이드)
    const zoomEffect = `zoompan=z='min(1.0+0.1*sin(in_time*2*PI/${finalDuration}),1.15)':d=${Math.floor(finalDuration * 25)}:s=1080x1920:fps=25`;
    
    // FFmpeg 비디오 생성
    const ffmpegCmd = `ffmpeg -loop 1 -i "${characterImage}" -i "${audioPath}" \
        -filter_complex "[0:v]${zoomEffect},eq=brightness=0.1:contrast=1.1,${drawtextFilters}[v]" \
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
    
    const concatFile = path.join(OUTPUT_DIR, 'char_concat.txt');
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
        console.log('🚀 AI 캐릭터 쇼츠 생성 시작\n');
        console.log(`👤 캐릭터: ${CHARACTER.name} (${CHARACTER.description})\n`);
        
        const sceneVideos = [];
        
        for (let i = 0; i < SCENES.length; i++) {
            const scene = SCENES[i];
            console.log(`\n📌 장면 ${i+1}/${SCENES.length}`);
            
            // 캐릭터 이미지 생성
            const characterImage = await generateCharacterImage(scene.character_prompt, i + 1);
            
            // TTS 생성
            const audioPath = await generateTTS(scene.text, i + 1);
            
            // 장면 비디오 생성
            const sceneVideo = await createSceneVideo(
                characterImage,
                audioPath,
                scene.text,
                scene.duration,
                i + 1
            );
            
            sceneVideos.push(sceneVideo);
        }
        
        // 최종 병합
        const timestamp = Date.now();
        const finalVideo = await mergeVideos(sceneVideos, `CHARACTER_SHORTS_${timestamp}.mp4`);
        
        console.log('\n' + '='.repeat(70));
        console.log('🎉 AI 캐릭터 쇼츠 생성 완료!');
        console.log('='.repeat(70));
        console.log(`👤 캐릭터: ${CHARACTER.name}`);
        console.log(`📁 파일: ${finalVideo.path}`);
        console.log(`📦 크기: ${(finalVideo.size/1024).toFixed(0)} KB`);
        console.log(`⏱️ 길이: ${finalVideo.duration.toFixed(1)}초`);
        console.log(`🎬 장면: ${sceneVideos.length}개`);
        console.log(`📝 자막: 75px 대형 자막 (그림자 효과)`);
        console.log(`🎨 애니메이션: 부드러운 줌 효과`);
        console.log(`🌐 다운로드: https://ai-shorts.neuralgrid.kr/videos/${path.basename(finalVideo.path)}`);
        console.log('='.repeat(70));
        
    } catch (error) {
        console.error('❌ 에러:', error);
        console.error(error.stack);
        process.exit(1);
    }
}

main();
