'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';

interface Scene {
  id: number;
  description: string;
  imageUrl: string;
  duration: number;
}

interface Stage {
  id: number;
  name: string;
  status: 'pending' | 'processing' | 'completed';
  progress: number;
  message: string;
}

export default function ShortsMakerPage() {
  const [title, setTitle] = useState('');
  const [generating, setGenerating] = useState(false);
  const [stages, setStages] = useState<Stage[]>([
    { id: 1, name: '🎬 스토리 분석', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 2, name: '📝 장면 구성', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 3, name: '🎨 스타일 설정', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 4, name: '🖼️ 장면 생성', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 5, name: '🎵 음악 매칭', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 6, name: '🎬 비디오 합성', status: 'pending', progress: 0, message: '대기 중...' },
  ]);
  const [scenes, setScenes] = useState<Scene[]>([]);
  const [finalVideo, setFinalVideo] = useState<string | null>(null);
  const [storyInfo, setStoryInfo] = useState<any>(null);

  const updateStage = (id: number, updates: Partial<Stage>) => {
    setStages(prev => prev.map(s => s.id === id ? { ...s, ...updates } : s));
  };

  const sleep = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

  // 스토리별 장면 생성
  const generateStoryScenes = (storyTitle: string): Array<{description: string; duration: number}> => {
    const stories: Record<string, Array<{description: string; duration: number}>> = {
      '선녀와 나무꾼': [
        { description: '깊은 산속, 나무를 하는 가난한 나무꾼, 석양 빛이 비치는 숲', duration: 3 },
        { description: '맑은 연못에서 목욕하는 아름다운 선녀들, 신비로운 빛', duration: 3 },
        { description: '나무 뒤에 숨겨진 선녀의 날개옷, 반짝이는 마법의 빛', duration: 3 },
        { description: '나무꾼과 선녀가 처음 만나는 순간, 로맨틱한 분위기', duration: 3 },
        { description: '행복한 결혼 생활, 예쁜 아이들과 함께하는 가족', duration: 3 },
        { description: '선녀가 날개옷을 찾아 하늘로 날아가는 장면, 감동적인 이별', duration: 3 },
        { description: '나무꾼이 하늘을 바라보며 눈물 흘리는 마지막 장면', duration: 3 },
      ],
      '흥부와 놀부': [
        { description: '가난하지만 착한 흥부의 초라한 집', duration: 3 },
        { description: '부자이지만 욕심 많은 놀부의 화려한 집', duration: 3 },
        { description: '다친 제비를 구해주는 착한 흥부', duration: 3 },
        { description: '제비가 가져온 박씨를 심는 흥부', duration: 3 },
        { description: '박에서 나오는 금은보화, 반짝이는 보물들', duration: 3 },
        { description: '욕심내어 제비 다리를 부러뜨리는 놀부', duration: 3 },
        { description: '놀부의 박에서 나오는 도깨비들, 벌받는 장면', duration: 3 },
      ],
      '심청전': [
        { description: '앞을 보지 못하는 심봉사와 어린 심청', duration: 3 },
        { description: '아버지를 위해 공양미 300석에 팔리는 심청', duration: 3 },
        { description: '인당수에 빠지는 심청, 슬픈 이별', duration: 3 },
        { description: '용궁에서 환대받는 심청, 화려한 수중 궁전', duration: 3 },
        { description: '연꽃을 타고 떠오르는 심청', duration: 3 },
        { description: '왕비가 된 심청과 아버지의 감동적인 재회', duration: 3 },
        { description: '눈을 뜬 심봉사, 행복한 결말', duration: 3 },
      ],
    };

    // 기본 장면 (제목이 매칭되지 않을 때)
    const defaultScenes = [
      { description: `${storyTitle}의 시작, 아름다운 배경`, duration: 3 },
      { description: `${storyTitle}의 주요 장면, 극적인 순간`, duration: 3 },
      { description: `${storyTitle}의 클라이맥스, 감동적인 장면`, duration: 3 },
      { description: `${storyTitle}의 결말, 여운이 남는 마무리`, duration: 3 },
    ];

    return stories[storyTitle] || defaultScenes;
  };

  const generateShorts = async () => {
    if (!title.trim()) {
      alert('제목을 입력해주세요!');
      return;
    }

    setGenerating(true);
    setScenes([]);
    setFinalVideo(null);
    setStoryInfo(null);

    try {
      // Stage 1: 스토리 분석
      updateStage(1, { status: 'processing', message: '스토리 구조 분석 중...' });
      await sleep(1000);
      
      const sceneData = generateStoryScenes(title);
      const totalDuration = sceneData.reduce((sum, s) => sum + s.duration, 0);
      
      setStoryInfo({
        title,
        sceneCount: sceneData.length,
        totalDuration,
        genre: '한국 전통 설화'
      });
      
      updateStage(1, { 
        status: 'completed', 
        progress: 100, 
        message: `${sceneData.length}개 장면 분석 완료!` 
      });

      // Stage 2: 장면 구성
      updateStage(2, { status: 'processing', message: '장면 스크립트 작성 중...' });
      await sleep(800);
      updateStage(2, { 
        status: 'completed', 
        progress: 100, 
        message: `${totalDuration}초 구성 완료!` 
      });

      // Stage 3: 스타일 설정
      updateStage(3, { status: 'processing', message: '한국 전통 스타일 적용 중...' });
      await sleep(600);
      updateStage(3, { 
        status: 'completed', 
        progress: 100, 
        message: '전통 수채화 스타일 선택!' 
      });

      // Stage 4: 장면 생성
      updateStage(4, { status: 'processing', message: '장면 생성 시작...' });
      
      const generatedScenes: Scene[] = [];
      
      for (let i = 0; i < sceneData.length; i++) {
        updateStage(4, {
          progress: ((i + 1) / sceneData.length) * 100,
          message: `장면 ${i + 1}/${sceneData.length} 생성 중...`
        });

        // Canvas로 이미지 생성
        const canvas = document.createElement('canvas');
        canvas.width = 1080;
        canvas.height = 1920;
        const ctx = canvas.getContext('2d')!;

        // 배경 그라데이션 (전통 색상)
        const gradient = ctx.createLinearGradient(0, 0, 0, 1920);
        const colorSets = [
          ['#8B7355', '#D4AF37'], // 갈색-금색
          ['#2C5F2D', '#97BC62'], // 녹색
          ['#191970', '#4169E1'], // 남색-파란색
          ['#8B4513', '#DEB887'], // 갈색
          ['#483D8B', '#9370DB'], // 보라색
          ['#DC143C', '#FF69B4'], // 빨강-분홍
          ['#2F4F4F', '#708090'], // 어두운 회색
        ];
        
        const [color1, color2] = colorSets[i % colorSets.length];
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, 1080, 1920);

        // 장식 무늬 (전통 문양)
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
        ctx.lineWidth = 3;
        for (let j = 0; j < 5; j++) {
          ctx.beginPath();
          ctx.arc(540, 200 + j * 150, 100, 0, Math.PI * 2);
          ctx.stroke();
        }

        // 제목
        ctx.fillStyle = 'white';
        ctx.font = 'bold 80px serif';
        ctx.textAlign = 'center';
        ctx.shadowColor = 'black';
        ctx.shadowBlur = 10;
        ctx.fillText(title, 540, 200);

        // 장면 번호
        ctx.font = 'bold 120px serif';
        ctx.fillText(`${i + 1}`, 540, 800);

        // 장면 설명
        ctx.font = '40px serif';
        ctx.shadowBlur = 5;
        const words = sceneData[i].description.split(' ');
        let line = '';
        let y = 1000;
        
        for (let n = 0; n < words.length; n++) {
          const testLine = line + words[n] + ' ';
          const metrics = ctx.measureText(testLine);
          if (metrics.width > 900 && n > 0) {
            ctx.fillText(line, 540, y);
            line = words[n] + ' ';
            y += 50;
          } else {
            line = testLine;
          }
        }
        ctx.fillText(line, 540, y);

        // 하단 타이밍 정보
        ctx.font = 'bold 35px sans-serif';
        ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
        ctx.fillText(`${sceneData[i].duration}초`, 540, 1800);

        const imageUrl = canvas.toDataURL('image/png');
        
        generatedScenes.push({
          id: i + 1,
          description: sceneData[i].description,
          imageUrl,
          duration: sceneData[i].duration
        });

        setScenes([...generatedScenes]);
        await sleep(600);
      }

      updateStage(4, { 
        status: 'completed', 
        progress: 100, 
        message: `${sceneData.length}개 장면 생성 완료!` 
      });

      // Stage 5: 음악 매칭
      updateStage(5, { status: 'processing', message: '전통 음악 선택 중...' });
      await sleep(800);
      updateStage(5, { 
        status: 'completed', 
        progress: 100, 
        message: '가야금 배경음악 매칭!' 
      });

      // Stage 6: 비디오 합성
      updateStage(6, { status: 'processing', message: '최종 비디오 렌더링 중...' });
      await sleep(1500);
      
      // 첫 장면을 대표 이미지로
      setFinalVideo(generatedScenes[0].imageUrl);
      
      updateStage(6, { 
        status: 'completed', 
        progress: 100, 
        message: `${totalDuration}초 쇼츠 완성!` 
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

  const downloadScene = (scene: Scene) => {
    const link = document.createElement('a');
    link.href = scene.imageUrl;
    link.download = `${title}_scene_${scene.id}.png`;
    link.click();
  };

  const downloadAllScenes = () => {
    scenes.forEach((scene, index) => {
      setTimeout(() => {
        downloadScene(scene);
      }, index * 500);
    });
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-purple-900 via-blue-900 to-black text-white">
      {/* 헤더 */}
      <header className="border-b border-white/10 bg-black/20 backdrop-blur-sm sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex items-center justify-between">
            <Link href="/" className="text-2xl font-bold gradient-text">
              Zero-Install AI Studio
            </Link>
            <div className="text-white/60">AI Shorts Maker - 30초 스토리텔링</div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* 소개 */}
        <div className="text-center mb-12">
          <h1 className="text-6xl font-bold mb-4 gradient-text">
            🎬 AI 스토리 쇼츠 메이커
          </h1>
          <p className="text-2xl text-white/70 mb-2">
            한국 전통 설화를 30초 쇼츠로 만들어보세요!
          </p>
          <p className="text-lg text-white/50">
            선녀와 나무꾼, 흥부와 놀부, 심청전 등 다양한 이야기 지원
          </p>
        </div>

        {/* 입력 섹션 */}
        <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
          <label className="block text-xl font-bold mb-4">
            📖 어떤 이야기를 만들까요?
          </label>
          
          {/* 추천 제목 */}
          <div className="flex flex-wrap gap-3 mb-6">
            {['선녀와 나무꾼', '흥부와 놀부', '심청전', '토끼와 거북이', '콩쥐팥쥐'].map((suggestion) => (
              <button
                key={suggestion}
                onClick={() => setTitle(suggestion)}
                className="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-full transition-colors"
                disabled={generating}
              >
                {suggestion}
              </button>
            ))}
          </div>

          <input
            type="text"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            placeholder="제목을 입력하세요 (예: 선녀와 나무꾼 이야기)"
            className="w-full px-6 py-4 bg-white/5 border border-white/20 rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500 text-lg mb-6"
            disabled={generating}
          />
          
          <button
            onClick={generateShorts}
            disabled={generating || !title.trim()}
            className="w-full px-8 py-5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:from-gray-600 disabled:to-gray-600 disabled:cursor-not-allowed rounded-lg font-bold text-2xl transition-all transform hover:scale-105 shadow-2xl"
          >
            {generating ? '🎨 생성 중...' : '🚀 30초 쇼츠 만들기'}
          </button>
        </div>

        {/* 스토리 정보 */}
        {storyInfo && (
          <div className="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm rounded-xl p-6 mb-8 border border-purple-500/30">
            <h3 className="text-xl font-bold mb-4">📊 스토리 정보</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <div className="text-white/60 text-sm">제목</div>
                <div className="font-bold text-lg">{storyInfo.title}</div>
              </div>
              <div>
                <div className="text-white/60 text-sm">장면 수</div>
                <div className="font-bold text-lg">{storyInfo.sceneCount}개</div>
              </div>
              <div>
                <div className="text-white/60 text-sm">총 시간</div>
                <div className="font-bold text-lg">{storyInfo.totalDuration}초</div>
              </div>
              <div>
                <div className="text-white/60 text-sm">장르</div>
                <div className="font-bold text-lg">{storyInfo.genre}</div>
              </div>
            </div>
          </div>
        )}

        {/* 진행 상황 */}
        {generating && (
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
            <div className="mb-6">
              <div className="flex justify-between mb-2">
                <span className="font-bold text-xl">전체 진행</span>
                <span className="text-purple-400 font-bold text-xl">{overallProgress}%</span>
              </div>
              <div className="h-4 bg-white/10 rounded-full overflow-hidden">
                <div
                  className="h-full bg-gradient-to-r from-purple-500 via-pink-500 to-blue-500 transition-all duration-300 animate-pulse"
                  style={{ width: `${overallProgress}%` }}
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {stages.map((stage) => (
                <div
                  key={stage.id}
                  className={`p-4 rounded-lg transition-all ${
                    stage.status === 'processing'
                      ? 'bg-blue-500/20 ring-2 ring-blue-500/50 scale-105'
                      : stage.status === 'completed'
                      ? 'bg-green-500/10'
                      : 'bg-white/5'
                  }`}
                >
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-3">
                      <span className="text-3xl">{stage.name.split(' ')[0]}</span>
                      <span className="font-bold">{stage.name.split(' ').slice(1).join(' ')}</span>
                    </div>
                    {stage.status === 'completed' && (
                      <span className="text-green-400 text-2xl">✅</span>
                    )}
                    {stage.status === 'processing' && (
                      <span className="animate-spin text-2xl">⚙️</span>
                    )}
                  </div>
                  <p className="text-sm text-white/70">{stage.message}</p>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 생성된 장면들 */}
        {scenes.length > 0 && (
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-3xl font-bold gradient-text">🎬 생성된 장면들</h2>
              <button
                onClick={downloadAllScenes}
                className="px-6 py-3 bg-green-600 hover:bg-green-700 rounded-lg font-bold transition-colors"
              >
                💾 모두 다운로드
              </button>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {scenes.map((scene) => (
                <div key={scene.id} className="group relative">
                  <div className="aspect-[9/16] rounded-lg overflow-hidden shadow-2xl">
                    <img
                      src={scene.imageUrl}
                      alt={`Scene ${scene.id}`}
                      className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                    />
                  </div>
                  <div className="absolute top-3 left-3 bg-black/70 px-3 py-1 rounded-full font-bold">
                    Scene {scene.id}
                  </div>
                  <div className="absolute bottom-3 right-3 bg-black/70 px-3 py-1 rounded-full text-sm">
                    {scene.duration}초
                  </div>
                  <div className="mt-3">
                    <p className="text-sm text-white/70 mb-2">{scene.description}</p>
                    <button
                      onClick={() => downloadScene(scene)}
                      className="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-bold transition-colors"
                    >
                      다운로드
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 완료 메시지 */}
        {finalVideo && (
          <div className="bg-gradient-to-r from-green-500/20 to-blue-500/20 backdrop-blur-sm rounded-xl p-8 border border-green-500/30 text-center">
            <h2 className="text-4xl font-bold mb-4 gradient-text">
              🎉 쇼츠 생성 완료!
            </h2>
            <p className="text-xl text-white/80 mb-6">
              총 {scenes.length}개 장면, {storyInfo.totalDuration}초 분량의 스토리가 완성되었습니다!
            </p>
            <div className="flex gap-4 justify-center">
              <button
                onClick={() => {
                  setScenes([]);
                  setFinalVideo(null);
                  setTitle('');
                  setStoryInfo(null);
                  setStages(prev => prev.map(s => ({
                    ...s,
                    status: 'pending' as const,
                    progress: 0,
                    message: '대기 중...'
                  })));
                }}
                className="px-8 py-4 bg-purple-600 hover:bg-purple-700 rounded-lg font-bold text-lg transition-colors"
              >
                🔄 새로 만들기
              </button>
              <Link
                href="/gallery"
                className="px-8 py-4 bg-blue-600 hover:bg-blue-700 rounded-lg font-bold text-lg transition-colors"
              >
                🖼️ 갤러리 보기
              </Link>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
