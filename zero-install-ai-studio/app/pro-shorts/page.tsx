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
  backgroundMusic?: {
    name: string;
    url: string;
    description: string;
  };
}

interface TimelineItem {
  id: string;
  type: 'stage' | 'story' | 'scene' | 'scenes' | 'video';
  title: string;
  status: 'pending' | 'processing' | 'completed' | 'error';
  timestamp: Date;
  data?: any;
}

export default function ProShortsPage() {
  const [prompt, setPrompt] = useState('');
  const [duration, setDuration] = useState(30);
  const [generating, setGenerating] = useState(false);
  const [timeline, setTimeline] = useState<TimelineItem[]>([]);
  const [story, setStory] = useState<Story | null>(null);
  const [finalVideoUrl, setFinalVideoUrl] = useState<string | null>(null);

  const addToTimeline = (item: Omit<TimelineItem, 'timestamp'>) => {
    setTimeline(prev => [...prev, { ...item, timestamp: new Date() }]);
  };

  const updateTimelineItem = (id: string, updates: Partial<TimelineItem>) => {
    setTimeline(prev => prev.map(item => 
      item.id === id ? { ...item, ...updates } : item
    ));
  };

  const sleep = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

  const generateShorts = async () => {
    if (!prompt.trim()) {
      alert('프롬프트를 입력해주세요!');
      return;
    }

    setGenerating(true);
    setTimeline([]);
    setStory(null);
    setFinalVideoUrl(null);

    try {
      // 1단계: 스토리 생성
      addToTimeline({
        id: 'stage-1',
        type: 'stage',
        title: '📝 스토리 생성',
        status: 'processing',
        data: { message: 'AI가 스토리를 생성하는 중...' }
      });

      const storyResponse = await fetch('/api/story', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt, duration })
      });

      if (!storyResponse.ok) throw new Error('스토리 생성 실패');
      const storyData = await storyResponse.json();
      if (!storyData.success) throw new Error('스토리 생성 실패');

      const generatedStory: Story = storyData.story;
      setStory(generatedStory);

      updateTimelineItem('stage-1', {
        status: 'completed',
        data: { 
          message: `${generatedStory.total_scenes}개 장면 스토리 완성!`,
          story: generatedStory
        }
      });

      await sleep(300);

      // 2단계: AI 이미지 생성 시작
      addToTimeline({
        id: 'stage-2-start',
        type: 'stage',
        title: '🎨 AI 이미지 생성 시작',
        status: 'completed',
        data: { message: `${generatedStory.scenes.length}개 장면 이미지를 생성합니다...` }
      });

      await sleep(300);

      const scenesWithImages: Scene[] = [];

      // 각 장면별로 개별 타임라인 추가
      for (let i = 0; i < generatedStory.scenes.length; i++) {
        const scene = generatedStory.scenes[i];
        
        // 장면 생성 시작 타임라인 추가
        addToTimeline({
          id: `scene-${i}-image`,
          type: 'scene',
          title: `🖼️ Scene ${i + 1}: ${scene.title}`,
          status: 'processing',
          data: { 
            message: 'AI 이미지 생성 중...',
            scene: scene
          }
        });

        try {
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
            
            // 이미지 생성 완료로 업데이트
            updateTimelineItem(`scene-${i}-image`, {
              status: 'completed',
              data: { 
                message: '✅ 이미지 생성 완료!',
                scene: scene,
                imageUrl: scene.imageUrl
              }
            });
          }
        } catch (error) {
          console.error('이미지 생성 오류:', error);
          updateTimelineItem(`scene-${i}-image`, {
            status: 'error',
            data: { message: '❌ 이미지 생성 실패' }
          });
        }

        scenesWithImages.push(scene);
        await sleep(200);
      }

      setStory({ ...generatedStory, scenes: scenesWithImages });

      await sleep(300);

      // 이미지 생성 완료 요약
      addToTimeline({
        id: 'stage-2-complete',
        type: 'stage',
        title: '✅ AI 이미지 생성 완료',
        status: 'completed',
        data: { 
          message: `${scenesWithImages.length}개 이미지 생성 완료!`,
          scenes: scenesWithImages
        }
      });

      await sleep(300);

      // 3단계: TTS 음성 생성 시작
      addToTimeline({
        id: 'stage-3-start',
        type: 'stage',
        title: '🎙️ TTS 음성 생성 시작',
        status: 'completed',
        data: { message: `${scenesWithImages.length}개 장면 음성을 생성합니다...` }
      });

      await sleep(300);

      const scenesWithAudio: Scene[] = [...scenesWithImages];
      let ttsSuccessCount = 0;

      // 각 장면별로 TTS 생성
      for (let i = 0; i < scenesWithImages.length; i++) {
        const scene = scenesWithAudio[i];
        const narration = scene.narration || scene.korean_description;
        
        // 음성 생성 시작 타임라인 추가
        addToTimeline({
          id: `scene-${i}-audio`,
          type: 'scene',
          title: `🎙️ Scene ${i + 1}: 음성 생성`,
          status: 'processing',
          data: { 
            message: `"${narration.substring(0, 30)}..." 음성 생성 중...`,
            scene: scene
          }
        });

        try {
          const ttsResponse = await Promise.race([
            fetch('/api/tts', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ text: narration })
            }),
            new Promise((_, reject) => 
              setTimeout(() => reject(new Error('TTS timeout')), 15000)
            )
          ]) as Response;

          if (ttsResponse.ok) {
            const ttsData = await ttsResponse.json();
            scene.audioUrl = ttsData.audio_url;
            ttsSuccessCount++;
            
            // 음성 생성 완료로 업데이트
            updateTimelineItem(`scene-${i}-audio`, {
              status: 'completed',
              data: { 
                message: '✅ 음성 생성 완료!',
                scene: scene,
                audioUrl: scene.audioUrl
              }
            });
          } else {
            updateTimelineItem(`scene-${i}-audio`, {
              status: 'error',
              data: { message: '❌ 음성 생성 실패' }
            });
          }
        } catch (error) {
          console.warn(`Scene ${i + 1} TTS failed:`, error);
          updateTimelineItem(`scene-${i}-audio`, {
            status: 'error',
            data: { message: '⚠️ 음성 생성 타임아웃' }
          });
        }

        await sleep(200);
      }

      setStory({ ...generatedStory, scenes: scenesWithAudio });

      await sleep(300);

      // TTS 생성 완료 요약
      addToTimeline({
        id: 'stage-3-complete',
        type: 'stage',
        title: '✅ TTS 음성 생성 완료',
        status: 'completed',
        data: { 
          message: ttsSuccessCount > 0 
            ? `${ttsSuccessCount}/${scenesWithAudio.length}개 음성 생성 완료!`
            : `음성 생성 건너뜀`,
          scenes: scenesWithAudio
        }
      });

      await sleep(500);

      // 4-6단계는 간단히 표시
      addToTimeline({
        id: 'stage-4',
        type: 'stage',
        title: '🎬 카메라 움직임 적용',
        status: 'completed',
        data: { message: '카메라 효과 준비 완료!' }
      });

      await sleep(500);

      addToTimeline({
        id: 'stage-5',
        type: 'stage',
        title: '🎥 장면별 비디오 합성',
        status: 'processing',
        data: { message: '이미지 + 음성 + 카메라 → 비디오 합성 중...' }
      });

      // 비디오 생성 API 호출
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
              audio_url: scene.audioUrl,
              image_url: scene.imageUrl
            })),
            fps: 30
          })
        });

        if (videoResponse.ok) {
          const videoData = await videoResponse.json();
          
          if (videoData.success) {
            setFinalVideoUrl(videoData.video_url);
            
            updateTimelineItem('stage-5', {
              status: 'completed',
              data: { 
                message: `${generatedStory.total_duration}초 비디오 합성 완료!`,
                videoUrl: videoData.video_url
              }
            });

            await sleep(500);

            // 배경음악 단계
            addToTimeline({
              id: 'stage-6',
              type: 'stage',
              title: '🎵 배경음악 추가',
              status: 'processing',
              data: { message: '배경음악 선택 중...' }
            });

            // 배경음악 매칭
            const musicResponse = await fetch('/api/music', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                mood: generatedStory.mood,
                genre: generatedStory.genre,
                title: generatedStory.title
              })
            });

            if (musicResponse.ok) {
              const musicData = await musicResponse.json();
              const backgroundMusic = musicData.music;
              
              if (backgroundMusic) {
                setStory(prev => prev ? { ...prev, backgroundMusic } : prev);
                
                updateTimelineItem('stage-6', {
                  status: 'completed',
                  data: { 
                    message: `배경음악 선택 완료! (${backgroundMusic.name})`,
                    music: backgroundMusic
                  }
                });
              }
            }

            await sleep(500);

            // 최종 비디오 표시
            addToTimeline({
              id: 'final-video',
              type: 'video',
              title: '🎉 완성된 AI 쇼츠',
              status: 'completed',
              data: { 
                videoUrl: videoData.video_url,
                story: generatedStory
              }
            });
          }
        }
      } catch (error) {
        console.error('비디오 생성 오류:', error);
        updateTimelineItem('stage-5', {
          status: 'error',
          data: { message: '비디오 생성 실패' }
        });
      }

    } catch (error) {
      console.error('Generation error:', error);
      alert('생성 중 오류가 발생했습니다: ' + (error as Error).message);
    } finally {
      setGenerating(false);
    }
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

      <main className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* 타이틀 */}
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold mb-4 gradient-text">
            🎬 프로 AI 쇼츠 메이커
          </h1>
          <p className="text-xl text-white/70 mb-2">
            프롬프트 하나로 완전 자동 AI 쇼츠 생성
          </p>
          <p className="text-lg text-purple-400">
            스토리 → 이미지 → 음성 → 카메라 → 비디오 → 배경음악
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

        {/* 타임라인 */}
        {timeline.length > 0 && (
          <div className="relative">
            {/* 타임라인 라인 */}
            <div className="absolute left-8 top-0 bottom-0 w-1 bg-gradient-to-b from-purple-500 via-pink-500 to-blue-500"></div>

            {/* 타임라인 아이템들 */}
            <div className="space-y-8">
              {timeline.map((item, index) => (
                <div key={item.id} className="relative pl-20">
                  {/* 타임라인 점 */}
                  <div className={`absolute left-6 top-6 w-5 h-5 rounded-full border-4 ${
                    item.status === 'completed' ? 'bg-green-500 border-green-300' :
                    item.status === 'processing' ? 'bg-blue-500 border-blue-300 animate-pulse' :
                    item.status === 'error' ? 'bg-red-500 border-red-300' :
                    'bg-gray-500 border-gray-300'
                  }`}></div>

                  {/* 타임라인 컨텐츠 */}
                  {item.type === 'stage' && (
                    <div className={`bg-white/10 backdrop-blur-sm rounded-xl p-6 border ${
                      item.status === 'processing' ? 'border-blue-500/50 ring-2 ring-blue-500/30' :
                      item.status === 'completed' ? 'border-green-500/30' :
                      item.status === 'error' ? 'border-red-500/30' :
                      'border-white/20'
                    }`}>
                      <div className="flex items-center justify-between mb-2">
                        <h3 className="text-xl font-bold">{item.title}</h3>
                        {item.status === 'completed' && <span className="text-2xl">✅</span>}
                        {item.status === 'processing' && <span className="text-2xl animate-spin">⚙️</span>}
                        {item.status === 'error' && <span className="text-2xl">❌</span>}
                      </div>
                      <p className="text-white/70">{item.data?.message}</p>
                      {item.status === 'processing' && item.data?.progress !== undefined && (
                        <div className="mt-3 h-2 bg-white/10 rounded-full overflow-hidden">
                          <div
                            className="h-full bg-blue-500 transition-all duration-300"
                            style={{ width: `${item.data.progress}%` }}
                          />
                        </div>
                      )}
                      {item.status === 'completed' && item.data?.story && (
                        <div className="mt-4 grid grid-cols-2 gap-3">
                          <div className="bg-white/5 rounded-lg p-3">
                            <div className="text-xs text-white/60">제목</div>
                            <div className="font-bold">{item.data.story.title}</div>
                          </div>
                          <div className="bg-white/5 rounded-lg p-3">
                            <div className="text-xs text-white/60">장면 수</div>
                            <div className="font-bold">{item.data.story.total_scenes}개</div>
                          </div>
                        </div>
                      )}
                      {item.status === 'completed' && item.data?.music && (
                        <div className="mt-4 bg-purple-500/20 rounded-lg p-4">
                          <div className="flex items-center gap-3">
                            <div className="text-3xl">🎵</div>
                            <div>
                              <div className="font-bold">{item.data.music.name}</div>
                              <div className="text-sm text-white/70">{item.data.music.description}</div>
                            </div>
                          </div>
                          <audio src={item.data.music.url} controls className="w-full mt-3 h-10" />
                        </div>
                      )}
                    </div>
                  )}

                  {item.type === 'scene' && (
                    <div className={`bg-white/5 backdrop-blur-sm rounded-xl p-4 border ${
                      item.status === 'processing' ? 'border-blue-500/50' :
                      item.status === 'completed' ? 'border-green-500/30' :
                      item.status === 'error' ? 'border-red-500/30' :
                      'border-white/20'
                    }`}>
                      <div className="flex items-start gap-4">
                        {/* 상태 아이콘 */}
                        <div className="flex-shrink-0 mt-1">
                          {item.status === 'completed' && <span className="text-xl">✅</span>}
                          {item.status === 'processing' && <span className="text-xl animate-spin">⚙️</span>}
                          {item.status === 'error' && <span className="text-xl">❌</span>}
                        </div>

                        <div className="flex-1">
                          <h4 className="font-bold text-lg mb-1">{item.title}</h4>
                          <p className="text-sm text-white/70 mb-2">{item.data?.message}</p>
                          
                          {/* 이미지 표시 */}
                          {item.data?.imageUrl && item.status === 'completed' && (
                            <div className="mt-3">
                              <div className="aspect-[9/16] max-w-[200px] relative rounded-lg overflow-hidden">
                                <img
                                  src={item.data.imageUrl}
                                  alt={item.title}
                                  className="w-full h-full object-cover"
                                />
                              </div>
                            </div>
                          )}

                          {/* 오디오 표시 */}
                          {item.data?.audioUrl && item.status === 'completed' && (
                            <div className="mt-3">
                              <audio src={item.data.audioUrl} controls className="w-full h-8" />
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                  )}

                  {item.type === 'scenes' && (
                    <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                      <h3 className="text-xl font-bold mb-4">{item.title}</h3>
                      <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                        {item.data?.scenes?.map((scene: Scene) => (
                          <div key={scene.scene_number} className="bg-white/5 rounded-lg overflow-hidden">
                            {scene.imageUrl && (
                              <div className="aspect-[9/16] relative">
                                <img
                                  src={scene.imageUrl}
                                  alt={scene.title}
                                  className="w-full h-full object-cover"
                                />
                                <div className="absolute top-2 left-2 bg-black/70 px-2 py-1 rounded-full text-xs font-bold">
                                  Scene {scene.scene_number}
                                </div>
                              </div>
                            )}
                            <div className="p-3">
                              <h4 className="font-bold text-sm mb-1">{scene.title}</h4>
                              <p className="text-xs text-white/60 mb-2 line-clamp-2">{scene.korean_description}</p>
                              {scene.audioUrl && (
                                <audio src={scene.audioUrl} controls className="w-full h-8" />
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {item.type === 'video' && (
                    <div className="bg-gradient-to-r from-green-500/20 to-blue-500/20 backdrop-blur-sm rounded-xl p-8 border border-green-500/30">
                      <h3 className="text-3xl font-bold mb-6 text-center">{item.title}</h3>
                      
                      <div className="max-w-md mx-auto mb-6">
                        <div className="aspect-[9/16] rounded-xl overflow-hidden shadow-2xl bg-black">
                          <video
                            src={item.data?.videoUrl}
                            controls
                            className="w-full h-full"
                          >
                            Your browser does not support the video tag.
                          </video>
                        </div>
                      </div>

                      <div className="text-center">
                        <a
                          href={item.data?.videoUrl}
                          download={`${item.data?.story?.title}_shorts.mp4`}
                          className="inline-block px-8 py-4 bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 rounded-lg font-bold transition-all shadow-lg hover:shadow-xl"
                        >
                          📥 비디오 다운로드
                        </a>
                      </div>

                      {item.data?.story && (
                        <div className="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
                          <div className="bg-white/10 rounded-lg p-3">
                            <div className="text-xs text-white/60">제목</div>
                            <div className="font-bold text-sm">{item.data.story.title}</div>
                          </div>
                          <div className="bg-white/10 rounded-lg p-3">
                            <div className="text-xs text-white/60">장르</div>
                            <div className="font-bold text-sm">{item.data.story.genre}</div>
                          </div>
                          <div className="bg-white/10 rounded-lg p-3">
                            <div className="text-xs text-white/60">총 시간</div>
                            <div className="font-bold text-sm">{item.data.story.total_duration}초</div>
                          </div>
                          <div className="bg-white/10 rounded-lg p-3">
                            <div className="text-xs text-white/60">장면 수</div>
                            <div className="font-bold text-sm">{item.data.story.total_scenes}개</div>
                          </div>
                        </div>
                      )}
                    </div>
                  )}

                  {/* 타임스탬프 */}
                  <div className="text-xs text-white/40 mt-2">
                    {item.timestamp.toLocaleTimeString('ko-KR')}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
