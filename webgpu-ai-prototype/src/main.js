/**
 * WebGPU AI Prototype - Main Logic
 * 천재적 시스템의 시작: 브라우저에서 로컬 GPU로 AI 실행
 */

// ========================================
// 1. 시스템 초기화 및 환경 체크
// ========================================

class WebGPUAISystem {
    constructor() {
        this.gpuDevice = null;
        this.gpuAdapter = null;
        this.modelLoaded = false;
        this.browserInfo = this.detectBrowser();
        
        this.init();
    }

    /**
     * 브라우저 감지
     */
    detectBrowser() {
        const ua = navigator.userAgent;
        let browser = 'Unknown';
        
        if (ua.includes('Chrome') && !ua.includes('Edg')) {
            browser = 'Chrome';
        } else if (ua.includes('Edg')) {
            browser = 'Edge';
        } else if (ua.includes('Firefox')) {
            browser = 'Firefox';
        } else if (ua.includes('Safari') && !ua.includes('Chrome')) {
            browser = 'Safari';
        }
        
        return browser;
    }

    /**
     * 시스템 초기화
     */
    async init() {
        console.log('🚀 WebGPU AI System 초기화 시작...');
        
        // 브라우저 정보 업데이트
        this.updateBrowserInfo();
        
        // WebGPU 지원 체크
        await this.checkWebGPUSupport();
        
        // 이벤트 리스너 설정
        this.setupEventListeners();
    }

    /**
     * 브라우저 정보 업데이트
     */
    updateBrowserInfo() {
        const browserInfoEl = document.getElementById('browser-info');
        browserInfoEl.textContent = this.browserInfo;
        browserInfoEl.className = 'status-value supported';
    }

    /**
     * WebGPU 지원 확인
     */
    async checkWebGPUSupport() {
        const statusEl = document.getElementById('webgpu-status');
        const gpuInfoEl = document.getElementById('gpu-info');
        const modelStatusEl = document.getElementById('model-status');
        const generateBtn = document.getElementById('generate-btn');

        try {
            // WebGPU API 존재 확인
            if (!navigator.gpu) {
                throw new Error('WebGPU not supported');
            }

            console.log('✅ WebGPU API 발견');
            
            // GPU Adapter 요청
            this.gpuAdapter = await navigator.gpu.requestAdapter({
                powerPreference: 'high-performance'
            });

            if (!this.gpuAdapter) {
                throw new Error('No GPU adapter found');
            }

            console.log('✅ GPU Adapter 획득:', this.gpuAdapter);

            // GPU Device 요청
            this.gpuDevice = await this.gpuAdapter.requestDevice();
            console.log('✅ GPU Device 획득:', this.gpuDevice);

            // GPU 정보 가져오기
            const adapterInfo = await this.gpuAdapter.requestAdapterInfo();
            const gpuName = adapterInfo.device || adapterInfo.description || 'Unknown GPU';
            
            console.log('GPU 정보:', adapterInfo);

            // UI 업데이트
            statusEl.textContent = '✅ 지원됨';
            statusEl.className = 'status-value supported';
            
            gpuInfoEl.textContent = gpuName;
            gpuInfoEl.className = 'status-value supported';

            modelStatusEl.textContent = '⚠️ 데모 모드 (실제 AI 모델 미포함)';
            modelStatusEl.className = 'status-value checking';

            // 생성 버튼 활성화
            generateBtn.disabled = false;
            generateBtn.textContent = '🎨 데모 이미지 생성하기';

            console.log('✅ 시스템 준비 완료!');

            return true;

        } catch (error) {
            console.error('❌ WebGPU 지원 확인 실패:', error);
            
            statusEl.textContent = '❌ 미지원';
            statusEl.className = 'status-value not-supported';
            
            gpuInfoEl.textContent = '사용 불가';
            gpuInfoEl.className = 'status-value not-supported';

            modelStatusEl.textContent = '사용 불가';
            modelStatusEl.className = 'status-value not-supported';

            // 폴백 안내
            this.showFallbackMessage();

            return false;
        }
    }

    /**
     * WebGPU 미지원 시 폴백 메시지
     */
    showFallbackMessage() {
        const container = document.querySelector('.container');
        const fallbackBox = document.createElement('div');
        fallbackBox.className = 'info-box';
        fallbackBox.style.background = '#fff3cd';
        fallbackBox.style.borderColor = '#ffc107';
        fallbackBox.innerHTML = `
            <h3 style="color: #856404;">⚠️ WebGPU 미지원 브라우저</h3>
            <p style="color: #856404; margin: 10px 0;">
                현재 브라우저는 WebGPU를 지원하지 않습니다.
            </p>
            <ul style="color: #856404;">
                <li><strong>권장 브라우저:</strong> Chrome 113+ 또는 Edge 113+</li>
                <li><strong>설정 확인:</strong> chrome://flags 에서 "Unsafe WebGPU" 활성화</li>
                <li><strong>대안:</strong> 서버 기반 AI 처리로 폴백 가능</li>
            </ul>
        `;
        container.appendChild(fallbackBox);
    }

    /**
     * 이벤트 리스너 설정
     */
    setupEventListeners() {
        const generateBtn = document.getElementById('generate-btn');
        const promptInput = document.getElementById('prompt-input');

        generateBtn.addEventListener('click', () => {
            const prompt = promptInput.value.trim();
            if (prompt) {
                this.generateImage(prompt);
            } else {
                alert('이미지 설명을 입력해주세요!');
            }
        });

        // Enter 키로도 생성 가능
        promptInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !generateBtn.disabled) {
                generateBtn.click();
            }
        });
    }

    /**
     * 이미지 생성 (데모 버전)
     */
    async generateImage(prompt) {
        console.log('🎨 이미지 생성 시작:', prompt);

        const resultBox = document.getElementById('result-box');
        const resultText = document.getElementById('result-text');
        const resultImage = document.getElementById('result-image');
        const loader = document.getElementById('loader');
        const progressBar = document.getElementById('progress-bar');
        const progressFill = document.getElementById('progress-fill');
        const generateBtn = document.getElementById('generate-btn');

        // UI 초기화
        resultBox.classList.add('show');
        resultImage.style.display = 'none';
        loader.style.display = 'block';
        progressBar.classList.add('show');
        progressFill.style.width = '0%';
        generateBtn.disabled = true;

        try {
            // 프로그레스 바 시뮬레이션
            resultText.textContent = '1/4: GPU 초기화 중...';
            await this.simulateProgress(progressFill, 25, 500);

            resultText.textContent = '2/4: 프롬프트 인코딩 중...';
            await this.simulateProgress(progressFill, 50, 800);

            resultText.textContent = '3/4: AI 이미지 생성 중...';
            await this.simulateProgress(progressFill, 75, 1500);

            resultText.textContent = '4/4: 후처리 및 최적화 중...';
            await this.simulateProgress(progressFill, 100, 500);

            // 데모 이미지 생성 (실제로는 AI 모델 사용)
            const demoImageUrl = await this.generateDemoImage(prompt);

            // 결과 표시
            loader.style.display = 'none';
            resultText.textContent = '✅ 생성 완료!';
            resultImage.src = demoImageUrl;
            resultImage.style.display = 'block';

            console.log('✅ 이미지 생성 완료');

        } catch (error) {
            console.error('❌ 이미지 생성 실패:', error);
            resultText.textContent = '❌ 생성 실패: ' + error.message;
            loader.style.display = 'none';
        } finally {
            generateBtn.disabled = false;
            setTimeout(() => {
                progressBar.classList.remove('show');
            }, 1000);
        }
    }

    /**
     * 프로그레스 바 애니메이션
     */
    simulateProgress(element, targetWidth, duration) {
        return new Promise(resolve => {
            setTimeout(() => {
                element.style.width = targetWidth + '%';
                setTimeout(resolve, 300);
            }, duration);
        });
    }

    /**
     * 데모 이미지 생성 (Canvas API 사용)
     */
    async generateDemoImage(prompt) {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');

            // 그라데이션 배경
            const gradient = ctx.createLinearGradient(0, 0, 512, 512);
            gradient.addColorStop(0, '#667eea');
            gradient.addColorStop(0.5, '#764ba2');
            gradient.addColorStop(1, '#f093fb');
            
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, 512, 512);

            // 텍스트 추가
            ctx.fillStyle = 'white';
            ctx.font = 'bold 24px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            
            // 프롬프트 표시 (줄바꿈 처리)
            const words = prompt.split(' ');
            let line = '';
            let y = 200;
            
            ctx.fillText('🎨 DEMO IMAGE', 256, 150);
            ctx.font = '18px Arial';
            
            for (let word of words) {
                const testLine = line + word + ' ';
                const metrics = ctx.measureText(testLine);
                
                if (metrics.width > 450 && line !== '') {
                    ctx.fillText(line, 256, y);
                    line = word + ' ';
                    y += 30;
                } else {
                    line = testLine;
                }
            }
            ctx.fillText(line, 256, y);

            // 추가 정보
            ctx.font = '14px Arial';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.fillText('실제 AI 모델 통합 예정', 256, 400);
            ctx.fillText('WebGPU + ONNX Runtime', 256, 430);
            ctx.fillText(`Generated: ${new Date().toLocaleTimeString()}`, 256, 460);

            // Canvas를 이미지로 변환
            resolve(canvas.toDataURL('image/png'));
        });
    }

    /**
     * GPU 성능 측정 (향후 구현)
     */
    async measureGPUPerformance() {
        // TODO: 실제 GPU 성능 벤치마크
        console.log('GPU 성능 측정 예정...');
    }
}

// ========================================
// 시스템 시작
// ========================================

let aiSystem;

document.addEventListener('DOMContentLoaded', () => {
    console.log('🎬 WebGPU AI Prototype 로드 완료');
    aiSystem = new WebGPUAISystem();
});

// ========================================
// 유틸리티 함수
// ========================================

/**
 * 브라우저 기능 체크
 */
function checkBrowserCapabilities() {
    return {
        webgpu: 'gpu' in navigator,
        webgl2: !!document.createElement('canvas').getContext('webgl2'),
        indexedDB: 'indexedDB' in window,
        serviceWorker: 'serviceWorker' in navigator,
        webassembly: typeof WebAssembly !== 'undefined'
    };
}

console.log('🔍 브라우저 기능:', checkBrowserCapabilities());
