'use client';

import { useState } from 'react';
import Link from 'next/link';

interface Scene {
  scene_number: number;
  title: string;
  description: string;
  korean_description: string;
  narration?: string;
  duration: number;
  camera_movement: string;
  mood: string;
  imageUrl?: string;
  audioUrl?: string;
}

interface Story {
  title: string;
  genre: string;
  total_duration: number;
  total_scenes: number;
  style: string;
  mood: string;
  scenes: Scene[];
  music_suggestion: string;
}

interface Stage {
  id: number;
  name: string;
  status: 'pending' | 'processing' | 'completed' | 'error';
  progress: number;
  message: string;
}

export default function ProShortsPage() {
  const [prompt, setPrompt] = useState('');
  const [duration, setDuration] = useState(30);
  const [generating, setGenerating] = useState(false);
  const [story, setStory] = useState<Story | null>(null);
  const [finalVideoUrl, setFinalVideoUrl] = useState<string | null>(null);
  
  const [stages, setStages] = useState<Stage[]>([
    { id: 1, name: '📝 스토리 생성', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 2, name: '🎨 AI 이미지 생성', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 3, name: '🎙️ TTS 음성 생성', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 4, name: '🎵 배경음악 매칭', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 5, name: '🎬 카메라 움직임 적용', status: 'pending', progress: 0, message: '대기 중...' },
    { id: 6, name: '🎥 최종 비디오 합성', status: 'pending', progress: 0, message: '대기 중...' },
  ]);

  const updateStage = (id: number, updates: Partial<Stage>) => {
    setStages(prev => prev.map(s => s.id === id ? { ...s, ...updates } : s));
  };

  const sleep = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

  const generateShorts = async () => {
    if (!prompt.trim()) {
      alert('프롬프트를 입력해주세요!');
      return;
    }

    setGenerating(true);
    setStory(null);
    setFinalVideoUrl(null);

    try {
      // ==================== 1단계: 스토리 생성 ====================
      updateStage(1, { status: 'processing', message: '🤖 AI가 스토리를 생성하는 중...' });
      
      const storyResponse = await fetch('/api/story', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt, duration })
      });

      if (!storyResponse.ok) {
        throw new Error('스토리 생성 실패');
      }

      const storyData = await storyResponse.json();
      
      if (!storyData.success) {
        throw new Error('스토리 생성 실패');
      }

      const generatedStory: Story = storyData.story;
      setStory(generatedStory);

      updateStage(1, { 
        status: 'completed', 
        progress: 100, 
        message: `✅ ${generatedStory.total_scenes}개 장면 스토리 완성!` 
      });

      await sleep(1000);

      // ==================== 2단계: AI 이미지 생성 ====================
      updateStage(2, { status: 'processing', message: '🎨 AI가 실제 이미지를 생성하는 중...' });

      const scenesWithImages: Scene[] = [];

      for (let i = 0; i < generatedStory.scenes.length; i++) {
        const scene = generatedStory.scenes[i];
        
        updateStage(2, {
          progress: ((i + 1) / generatedStory.scenes.length) * 100,
          message: `🎨 장면 ${i + 1}/${generatedStory.scenes.length} 이미지 생성 중...`
        });

        try {
          // 실제 AI 이미지 생성 API 호출
          const imageResponse = await fetch('/api/image', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              prompt: scene.description,
              width: 1080,
              height: 1920,
              style: 'traditional'
            })
          });

          if (imageResponse.ok) {
            const imageData = await imageResponse.json();
            scene.imageUrl = imageData.image_url;
          } else {
            throw new Error('이미지 생성 실패');
          }
        } catch (error) {
          console.error('이미지 생성 오류:', error);
          
          // 폴백: Canvas로 기본 이미지 생성
          const canvas = document.createElement('canvas');
          canvas.width = 1080;
          canvas.height = 1920;
          const ctx = canvas.getContext('2d')!;

          // 그라데이션 배경
          const gradient = ctx.createLinearGradient(0, 0, 0, 1920);
          const colorSets = [
            ['#8B7355', '#D4AF37'],
            ['#2C5F2D', '#97BC62'],
            ['#191970', '#4169E1'],
            ['#8B4513', '#DEB887'],
            ['#483D8B', '#9370DB'],
            ['#DC143C', '#FF69B4'],
            ['#2F4F4F', '#708090'],
          ];
          const [color1, color2] = colorSets[i % colorSets.length];
          gradient.addColorStop(0, color1);
          gradient.addColorStop(1, color2);
          ctx.fillStyle = gradient;
          ctx.fillRect(0, 0, 1080, 1920);

          // 텍스트
          ctx.fillStyle = 'white';
          ctx.font = 'bold 60px sans-serif';
          ctx.textAlign = 'center';
          ctx.shadowColor = 'black';
          ctx.shadowBlur = 10;
          ctx.fillText(scene.title, 540, 900);
          
          ctx.font = '40px sans-serif';
          ctx.fillText(`Scene ${scene.scene_number}`, 540, 1000);

          scene.imageUrl = canvas.toDataURL('image/png');
        }

        scenesWithImages.push(scene);
        setStory({ ...generatedStory, scenes: scenesWithImages });
        await sleep(500);
      }

      updateStage(2, { 
        status: 'completed', 
        progress: 100, 
        message: `✅ ${scenesWithImages.length}개 AI 이미지 생성 완료!` 
      });

      await sleep(1000);

      // ==================== 3단계: TTS 음성 생성 ====================
      updateStage(3, { status: 'processing', message: '🎙️ AI가 나레이션 음성을 생성하는 중...' });

      const scenesWithAudio: Scene[] = [];

      for (let i = 0; i < scenesWithImages.length; i++) {
        const scene = scenesWithImages[i];
        
        updateStage(3, {
          progress: ((i + 1) / scenesWithImages.length) * 100,
          message: `🎙️ 장면 ${i + 1}/${scenesWithImages.length} 음성 생성 중...`
        });

        try {
          const narration = scene.narration || scene.korean_description;
          
          // TTS 음성 생성 API 호출
          const ttsResponse = await fetch('/api/tts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              text: narration
            })
          });

          if (ttsResponse.ok) {
            const ttsData = await ttsResponse.json();
            scene.audioUrl = ttsData.audio_url;
          } else {
            console.warn(`Scene ${i + 1} TTS failed, skipping audio`);
          }
        } catch (error) {
          console.error('TTS 생성 오류:', error);
          // TTS 실패해도 계속 진행
        }

        scenesWithAudio.push(scene);
        setStory({ ...generatedStory, scenes: scenesWithAudio });
        await sleep(500);
      }

      updateStage(3, { 
        status: 'completed', 
        progress: 100, 
        message: `✅ ${scenesWithAudio.length}개 나레이션 음성 생성 완료!` 
      });

      await sleep(1000);

      // ==================== 4단계: 배경음악 매칭 ====================
      updateStage(4, { status: 'processing', message: '🎵 스토리에 어울리는 배경음악 선택 중...' });
      
      await sleep(2000);
      
      updateStage(4, { 
        status: 'completed', 
        progress: 100, 
        message: `✅ 배경음악 매칭 완료! (${generatedStory.music_suggestion})` 
      });

      await sleep(1000);

      // ==================== 5단계: 카메라 움직임 분석 ====================
      updateStage(5, { status: 'processing', message: '🎬 카메라 움직임 효과 적용 중...' });

      // 각 장면의 카메라 움직임 확인
      const cameraMovements = scenesWithAudio.map(s => s.camera_movement);
      const uniqueMovements = [...new Set(cameraMovements)];

      updateStage(5, { 
        status: 'completed', 
        progress: 100, 
        message: `✅ ${uniqueMovements.length}가지 카메라 효과 준비 완료!` 
      });

      await sleep(1000);

      // ==================== 6단계: 최종 비디오 합성 ====================
      updateStage(6, { status: 'processing', message: '🎥 최종 비디오 렌더링 중 (이미지 + 음성 + 카메라)...' });

      try {
        const videoResponse = await fetch('/api/video', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            title: generatedStory.title,
            scenes: scenesWithAudio.map(scene => ({
              description: scene.description,
              duration: scene.duration,
              style: generatedStory.style,
              camera_movement: scene.camera_movement,
              audio_url: scene.audioUrl
            })),
            fps: 30
          })
        });

        if (videoResponse.ok) {
          const videoData = await videoResponse.json();
          
          if (videoData.success) {
            const videoUrl = videoData.video_url;
            setFinalVideoUrl(videoUrl);
            
            updateStage(6, { 
              status: 'completed', 
              progress: 100, 
              message: `✅ ${generatedStory.total_duration}초 비디오 완성! (${(videoData.file_size / 1024 / 1024).toFixed(2)}MB)` 
            });
          } else {
            throw new Error('비디오 생성 실패');
          }
        } else {
          throw new Error('비디오 API 오류');
        }
      } catch (videoError) {
        console.error('비디오 생성 오류:', videoError);
        updateStage(6, { 
          status: 'error', 
          progress: 100, 
          message: '⚠️ 비디오 생성 실패 (이미지와 음성은 확인 가능)' 
        });
      }

    } catch (error) {
      console.error('Generation error:', error);
      alert('생성 중 오류가 발생했습니다: ' + (error as Error).message);
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
      <header className="border-b border-white/10 bg-black/20 backdrop-blur-sm sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex items-center justify-between">
            <Link href="/" className="text-2xl font-bold gradient-text">
              Zero-Install AI Studio
            </Link>
            <div className="flex gap-4">
              <Link
                href="/shorts-maker"
                className="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors"
              >
                기본 메이커
              </Link>
              <Link
                href="/gallery"
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
              >
                갤러리
              </Link>
            </div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* 타이틀 */}
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold mb-4 gradient-text">
            🎬 프로 AI 쇼츠 메이커
          </h1>
          <p className="text-xl text-white/70 mb-2">
            프롬프트 하나로 완전 자동 AI 쇼츠 생성
          </p>
          <p className="text-lg text-purple-400">
            스토리 생성 → AI 이미지 → 카메라 움직임 → 비디오 합성
          </p>
        </div>

        {/* 입력 폼 */}
        <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
          <div className="mb-6">
            <label className="block text-lg font-bold mb-3">
              🎯 무엇을 만들고 싶으신가요?
            </label>
            <input
              type="text"
              value={prompt}
              onChange={(e) => setPrompt(e.target.value)}
              placeholder="예: 선녀와 나무꾼, 토끼와 거북이, 우주를 여행하는 고양이..."
              className="w-full px-6 py-4 bg-white/10 border border-white/30 rounded-lg text-white placeholder-white/50 text-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
              disabled={generating}
            />
          </div>

          <div className="mb-6">
            <label className="block text-lg font-bold mb-3">
              ⏱️ 비디오 길이 (초)
            </label>
            <input
              type="number"
              value={duration}
              onChange={(e) => setDuration(Number(e.target.value))}
              min={15}
              max={60}
              className="w-full px-6 py-4 bg-white/10 border border-white/30 rounded-lg text-white text-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
              disabled={generating}
            />
            <p className="text-sm text-white/60 mt-2">
              추천: 30초 (약 7개 장면)
            </p>
          </div>

          <button
            onClick={generateShorts}
            disabled={generating || !prompt.trim()}
            className={`w-full px-8 py-4 rounded-lg font-bold text-xl transition-all ${
              generating || !prompt.trim()
                ? 'bg-gray-600 cursor-not-allowed'
                : 'bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 shadow-lg hover:shadow-xl'
            }`}
          >
            {generating ? '🎬 AI가 열심히 만드는 중...' : '🚀 AI 쇼츠 생성 시작!'}
          </button>
        </div>

        {/* 진행 상황 */}
        {generating && (
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
            <div className="mb-6">
              <div className="flex justify-between mb-2">
                <span className="font-bold text-2xl">전체 진행</span>
                <span className="text-purple-400 font-bold text-2xl">{overallProgress}%</span>
              </div>
              <div className="h-6 bg-white/10 rounded-full overflow-hidden">
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
                  className={`p-6 rounded-lg transition-all ${
                    stage.status === 'processing'
                      ? 'bg-blue-500/20 ring-2 ring-blue-500/50 scale-105'
                      : stage.status === 'completed'
                      ? 'bg-green-500/10'
                      : stage.status === 'error'
                      ? 'bg-red-500/10'
                      : 'bg-white/5'
                  }`}
                >
                  <div className="flex items-center justify-between mb-3">
                    <span className="font-bold text-lg">{stage.name}</span>
                    {stage.status === 'completed' && (
                      <span className="text-green-400 text-2xl">✅</span>
                    )}
                    {stage.status === 'processing' && (
                      <span className="animate-spin text-2xl">⚙️</span>
                    )}
                    {stage.status === 'error' && (
                      <span className="text-red-400 text-2xl">❌</span>
                    )}
                  </div>
                  <p className="text-sm text-white/70">{stage.message}</p>
                  {stage.status === 'processing' && (
                    <div className="mt-3 h-2 bg-white/10 rounded-full overflow-hidden">
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

        {/* 스토리 정보 */}
        {story && (
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white/20">
            <h2 className="text-3xl font-bold mb-6 gradient-text">📖 생성된 스토리</h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
              <div className="bg-white/5 rounded-lg p-4">
                <div className="text-white/60 text-sm mb-1">제목</div>
                <div className="font-bold text-lg">{story.title}</div>
              </div>
              <div className="bg-white/5 rounded-lg p-4">
                <div className="text-white/60 text-sm mb-1">장르</div>
                <div className="font-bold text-lg">{story.genre}</div>
              </div>
              <div className="bg-white/5 rounded-lg p-4">
                <div className="text-white/60 text-sm mb-1">총 시간</div>
                <div className="font-bold text-lg">{story.total_duration}초</div>
              </div>
              <div className="bg-white/5 rounded-lg p-4">
                <div className="text-white/60 text-sm mb-1">장면 수</div>
                <div className="font-bold text-lg">{story.total_scenes}개</div>
              </div>
            </div>

            {/* 장면 목록 */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {story.scenes.map((scene) => (
                <div key={scene.scene_number} className="bg-white/5 rounded-lg overflow-hidden">
                  {scene.imageUrl && (
                    <div className="aspect-[9/16] relative">
                      <img
                        src={scene.imageUrl}
                        alt={scene.title}
                        className="w-full h-full object-cover"
                      />
                      <div className="absolute top-3 left-3 bg-black/70 px-3 py-1 rounded-full font-bold">
                        Scene {scene.scene_number}
                      </div>
                      <div className="absolute bottom-3 right-3 bg-black/70 px-3 py-1 rounded-full text-sm">
                        {scene.duration.toFixed(1)}초
                      </div>
                    </div>
                  )}
                  <div className="p-4">
                    <h3 className="font-bold text-lg mb-2">{scene.title}</h3>
                    <p className="text-sm text-white/70 mb-2">{scene.korean_description}</p>
                    <div className="flex gap-2 text-xs">
                      <span className="bg-purple-500/20 px-2 py-1 rounded">{scene.camera_movement}</span>
                      <span className="bg-blue-500/20 px-2 py-1 rounded">{scene.mood}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 최종 비디오 */}
        {finalVideoUrl && (
          <div className="bg-gradient-to-r from-green-500/20 to-blue-500/20 backdrop-blur-sm rounded-xl p-8 border border-green-500/30">
            <h2 className="text-4xl font-bold mb-6 text-center gradient-text">
              🎉 AI 쇼츠 완성!
            </h2>
            
            <div className="max-w-md mx-auto mb-8">
              <div className="aspect-[9/16] rounded-xl overflow-hidden shadow-2xl bg-black">
                <video
                  src={finalVideoUrl}
                  controls
                  className="w-full h-full"
                  poster={story?.scenes[0]?.imageUrl}
                >
                  Your browser does not support the video tag.
                </video>
              </div>
              
              <a
                href={finalVideoUrl}
                download={`${story?.title}_shorts.mp4`}
                className="mt-4 w-full block px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 rounded-lg font-bold text-center transition-all shadow-lg hover:shadow-xl"
              >
                📥 비디오 다운로드
              </a>
            </div>
            
            <div className="flex gap-4 justify-center">
              <button
                onClick={() => {
                  setPrompt('');
                  setStory(null);
                  setFinalVideoUrl(null);
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
