const fs = require('fs');
const { createCanvas } = require('canvas');

function generateIcon(size) {
    const canvas = createCanvas(size, size);
    const ctx = canvas.getContext('2d');
    
    // 배경 그라데이션 (간소화 - 단색)
    ctx.fillStyle = '#6366f1';
    ctx.fillRect(0, 0, size, size);
    
    // 둥근 모서리 효과
    ctx.fillStyle = '#8b5cf6';
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, size / 3, 0, Math.PI * 2);
    ctx.fill();
    
    // 텍스트
    ctx.fillStyle = '#ffffff';
    ctx.font = `bold ${size * 0.35}px Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('AI', size / 2, size / 2);
    
    // 저장
    const buffer = canvas.toBuffer('image/png');
    fs.writeFileSync(`icon-${size}x${size}.png`, buffer);
    console.log(`Generated icon-${size}x${size}.png`);
}

try {
    generateIcon(192);
    generateIcon(512);
} catch (error) {
    console.error('Canvas 모듈이 설치되지 않았습니다. 기본 아이콘을 사용합니다.');
}
