'use client';

import { useState } from 'react';
import Link from 'next/link';

interface Stage {
  id: number;
  name: string;
  status: 'pending' | 'processing' | 'completed';
  progress: number;
  message: string;
}

export default function DemoPage() {
  const [prompt, setPrompt] = useState('');
  const [generating, setGenerating] = useState(false);
  const [stages, setStages] = useState<Stage[]>([
    { id: 1, name: '🧠 AI 분석', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 2, name: '✨ 프롬프트 확장', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 3, name: '🎨 스타일 선택', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 4, name: '🖼️ 이미지 생성 (3장)', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 5, name: '🎬 비디오 렌더링', status: 'pending', progress: 0, message: '대기 중...' },
  ]);
  const [result, setResult] = useState<{
    enhanced: string;
    style: string;
    images: string[];
    video: string;
  } | null>(null);

  const updateStage = (id: number, updates: Partial<Stage>) => {
    setStages(prev => prev.map(s => s.id === id ? { ...s, ...updates } : s));
  };

  const sleep = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

  const generateDemo = async () => {
    if (!prompt.trim()) {
      alert('프롬프트를 입력해주세요!');
      return;
    }

    setGenerating(true);
    setResult(null);

    try {
      // Stage 1: AI 분석
      updateStage(1, { status: 'processing', message: '입력 내용 분석 중...' });
      await sleep(800);
      updateStage(1, { status: 'completed', progress: 100, message: '분석 완료!' });

      // Stage 2: 프롬프트 확장
      updateStage(2, { status: 'processing', message: 'AI가 프롬프트 확장 중...' });
      await sleep(1000);
      const enhanced = `${prompt}, highly detailed, cinematic lighting, 4k resolution, professional photography, vibrant colors, sharp focus`;
      updateStage(2, { status: 'completed', progress: 100, message: '확장 완료!' });

      // Stage 3: 스타일 선택
      updateStage(3, { status: 'processing', message: '최적 스타일 선택 중...' });
      await sleep(600);
      const style = 'Cinematic';
      updateStage(3, { status: 'completed', progress: 100, message: `${style} 스타일 선택!` });

      // Stage 4: 이미지 생성
      updateStage(4, { status: 'processing', message: '장면 생성 중...' });
      const images: string[] = [];
      
      for (let i = 0; i < 3; i++) {
        updateStage(4, { 
          progress: ((i + 1) / 3) * 100, 
          message: `장면 ${i + 1}/3 생성 중...` 
        });
        
        // 데모 이미지 생성 (canvas로 간단한 그라데이션)
        const canvas = document.createElement('canvas');
        canvas.width = 1080;
        canvas.height = 1920;
        const ctx = canvas.getContext('2d')!;
        
        // 그라데이션 배경
        const gradient = ctx.createLinearGradient(0, 0, 0, 1920);
        const colors = [
          ['#667eea', '#764ba2'],
          ['#f093fb', '#f5576c'],
          ['#4facfe', '#00f2fe']
        ];
        const [color1, color2] = colors[i];
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, 1080, 1920);
        
        // 텍스트 추가
        ctx.fillStyle = 'white';
        ctx.font = 'bold 80px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`Scene ${i + 1}`, 540, 960);
        ctx.font = '40px sans-serif';
        ctx.fillText(prompt.slice(0, 30), 540, 1050);
        
        images.push(canvas.toDataURL('image/png'));
        await sleep(800);
      }
      
      updateStage(4, { status: 'completed', progress: 100, message: '이미지 생성 완료!' });

      // Stage 5: 비디오 렌더링
      updateStage(5, { status: 'processing', message: '비디오 렌더링 중...' });
      await sleep(1500);
      
      // 첫 번째 이미지를 비디오 썸네일로 사용
      updateStage(5, { status: 'completed', progress: 100, message: '렌더링 완료!' });

      setResult({
        enhanced,
        style,
        images,
        video: images[0]
      });

    } catch (error) {
      console.error('Generation error:', error);
      alert('생성 중 오류가 발생했습니다.');
    } finally {
      setGenerating(false);
    }
  };

  const overallProgress = Math.round(
    stages.reduce((sum, s) => sum + s.progress, 0) / stages.length
  );

  return (
    <div className="min-h-screen bg-gradient-to-b from-purple-900 via-blue-900 to-black text-white">
      {/* 헤더 */}
      <header className="border-b border-white/10 bg-black/20 backdrop-blur-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex items-center justify-between">
            <Link href="/" className="text-2xl font-bold gradient-text">
              Zero-Install AI Studio
            </Link>
            <div className="text-white/60">Demo - AI 쇼츠 생성</div>
          </div>
        </div>
      </header>

      <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* 소개 */}
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold mb-4 gradient-text">
            ✨ AI 쇼츠 생성 데모
          </h1>
          <p className="text-xl text-white/70">
            간단한 프롬프트로 멋진 쇼츠를 만들어보세요!
          </p>
        </div>

        {/* 입력 섹션 */}
        <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
          <label className="block text-lg font-bold mb-4">
            무엇을 만들고 싶으신가요?
          </label>
          <input
            type="text"
            value={prompt}
            onChange={(e) => setPrompt(e.target.value)}
            placeholder="예: 우주를 여행하는 고양이, 마법의 숲, 미래 도시..."
            className="w-full px-6 py-4 bg-white/5 border border-white/20 rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500 text-lg"
            disabled={generating}
          />
          
          <button
            onClick={generateDemo}
            disabled={generating || !prompt.trim()}
            className="mt-6 w-full px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:from-gray-600 disabled:to-gray-600 disabled:cursor-not-allowed rounded-lg font-bold text-xl transition-all transform hover:scale-105"
          >
            {generating ? '🎨 생성 중...' : '🚀 AI 쇼츠 생성하기'}
          </button>
        </div>

        {/* 진행 상황 */}
        {generating && (
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
            <div className="mb-6">
              <div className="flex justify-between mb-2">
                <span className="font-bold text-lg">전체 진행</span>
                <span className="text-purple-400 font-bold text-lg">{overallProgress}%</span>
              </div>
              <div className="h-3 bg-white/10 rounded-full overflow-hidden">
                <div
                  className="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-300"
                  style={{ width: `${overallProgress}%` }}
                />
              </div>
            </div>

            <div className="space-y-4">
              {stages.map((stage) => (
                <div
                  key={stage.id}
                  className={`p-4 rounded-lg transition-all ${
                    stage.status === 'processing'
                      ? 'bg-blue-500/20 ring-2 ring-blue-500/50'
                      : stage.status === 'completed'
                      ? 'bg-green-500/10'
                      : 'bg-white/5'
                  }`}
                >
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-3">
                      <span className="text-2xl">{stage.name.split(' ')[0]}</span>
                      <span className="font-medium">{stage.name.split(' ').slice(1).join(' ')}</span>
                    </div>
                    {stage.status === 'completed' && (
                      <span className="text-green-400">✅</span>
                    )}
                    {stage.status === 'processing' && (
                      <span className="animate-spin">⚙️</span>
                    )}
                  </div>
                  <p className="text-sm text-white/60">{stage.message}</p>
                  {stage.status === 'processing' && (
                    <div className="mt-2 h-1 bg-white/10 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-blue-500 transition-all duration-300"
                        style={{ width: `${stage.progress}%` }}
                      />
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 결과 */}
        {result && (
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20">
            <h2 className="text-3xl font-bold mb-6 gradient-text">🎉 생성 완료!</h2>
            
            {/* 확장된 프롬프트 */}
            <div className="mb-6">
              <h3 className="font-bold mb-2">📝 AI 확장 프롬프트:</h3>
              <p className="text-white/70 bg-white/5 p-4 rounded-lg">{result.enhanced}</p>
            </div>

            {/* 선택된 스타일 */}
            <div className="mb-6">
              <h3 className="font-bold mb-2">🎨 선택된 스타일:</h3>
              <span className="inline-block bg-purple-500/20 px-4 py-2 rounded-full">
                {result.style}
              </span>
            </div>

            {/* 생성된 이미지들 */}
            <div className="mb-6">
              <h3 className="font-bold mb-4">🖼️ 생성된 장면 (3개):</h3>
              <div className="grid grid-cols-3 gap-4">
                {result.images.map((img, i) => (
                  <div key={i} className="relative group">
                    <img
                      src={img}
                      alt={`Scene ${i + 1}`}
                      className="w-full rounded-lg shadow-lg group-hover:scale-105 transition-transform"
                    />
                    <div className="absolute top-2 left-2 bg-black/70 px-3 py-1 rounded-full text-sm">
                      Scene {i + 1}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* 다운로드 버튼 */}
            <div className="flex gap-4">
              <button className="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 rounded-lg font-bold transition-colors">
                💾 이미지 다운로드
              </button>
              <button className="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg font-bold transition-colors">
                🎬 비디오로 변환
              </button>
              <button
                onClick={() => {
                  setResult(null);
                  setPrompt('');
                  setStages(prev => prev.map(s => ({
                    ...s,
                    status: 'pending' as const,
                    progress: 0,
                    message: '대기 중...'
                  })));
                }}
                className="px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-lg font-bold transition-colors"
              >
                🔄 다시 만들기
              </button>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
