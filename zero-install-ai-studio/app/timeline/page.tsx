'use client';

import { useState, useRef, useEffect } from 'react';
import { timelineEditor, type TimelineTrack, type TimelineClip } from '@/lib/timeline-editor';

export default function TimelinePage() {
  const [tracks, setTracks] = useState<TimelineTrack[]>([]);
  const [currentTime, setCurrentTime] = useState(0);
  const [duration, setDuration] = useState(60000);
  const [isPlaying, setIsPlaying] = useState(false);
  const [zoom, setZoom] = useState(10);
  const [selectedClips, setSelectedClips] = useState<string[]>([]);
  
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const timelineRef = useRef<HTMLDivElement>(null);
  
  useEffect(() => {
    if (canvasRef.current) {
      timelineEditor.initCanvas(canvasRef.current);
    }
    
    // 상태 동기화
    const state = timelineEditor.getState();
    setTracks(state.tracks);
    setDuration(state.duration);
  }, []);
  
  useEffect(() => {
    // 재생 상태 업데이트
    const interval = setInterval(() => {
      if (isPlaying) {
        const state = timelineEditor.getState();
        setCurrentTime(state.currentTime);
      }
    }, 100);
    
    return () => clearInterval(interval);
  }, [isPlaying]);
  
  const handlePlayPause = () => {
    timelineEditor.togglePlayback();
    setIsPlaying(!isPlaying);
  };
  
  const handleSeek = (e: React.ChangeEvent<HTMLInputElement>) => {
    const time = parseFloat(e.target.value);
    timelineEditor.seek(time);
    setCurrentTime(time);
  };
  
  const handleAddClip = (trackId: string) => {
    const clip = timelineEditor.addClip(trackId, {
      type: 'video',
      name: 'New Clip',
      url: '',
      startTime: currentTime,
      duration: 5000,
      trimStart: 0,
      trimEnd: 5000,
      volume: 1,
      opacity: 1,
      x: 0,
      y: 0,
      width: 1920,
      height: 1080,
      rotation: 0
    });
    
    const state = timelineEditor.getState();
    setTracks([...state.tracks]);
    setDuration(state.duration);
  };
  
  const handleExportProject = () => {
    const json = timelineEditor.exportProject();
    const blob = new Blob([json], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'project.json';
    a.click();
  };
  
  const handleRenderFinal = async () => {
    try {
      const blob = await timelineEditor.renderFinal();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'final-video.mp4';
      a.click();
    } catch (error) {
      console.error('Render failed:', error);
    }
  };
  
  const formatTime = (ms: number): string => {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    
    const mm = String(minutes % 60).padStart(2, '0');
    const ss = String(seconds % 60).padStart(2, '0');
    const ms2 = String(Math.floor((ms % 1000) / 10)).padStart(2, '0');
    
    if (hours > 0) {
      const hh = String(hours).padStart(2, '0');
      return `${hh}:${mm}:${ss}.${ms2}`;
    }
    return `${mm}:${ss}.${ms2}`;
  };
  
  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 text-white">
      {/* 헤더 */}
      <div className="border-b border-white/20 bg-black/30 backdrop-blur-sm">
        <div className="max-w-full mx-auto px-6 py-4">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-purple-400">
                🎬 타임라인 편집기
              </h1>
              <p className="text-sm text-gray-400">프로페셔널 멀티트랙 비디오 편집</p>
            </div>
            
            <div className="flex items-center gap-3">
              <button
                onClick={handleExportProject}
                className="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all"
              >
                💾 프로젝트 저장
              </button>
              
              <button
                onClick={handleRenderFinal}
                className="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-2 px-6 rounded-lg transition-all"
              >
                🎥 최종 렌더링
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* 메인 레이아웃 */}
      <div className="flex h-[calc(100vh-100px)]">
        {/* 왼쪽: 미리보기 */}
        <div className="w-1/3 border-r border-white/20 bg-black/20 p-6">
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 mb-4">
            <h2 className="text-xl font-bold mb-3">📺 미리보기</h2>
            
            {/* 미리보기 캔버스 */}
            <div className="aspect-video bg-black rounded-lg overflow-hidden mb-4">
              <canvas
                ref={canvasRef}
                width={1920}
                height={1080}
                className="w-full h-full object-contain"
              />
            </div>
            
            {/* 재생 컨트롤 */}
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <button
                  onClick={handlePlayPause}
                  className="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition-all flex-shrink-0"
                >
                  {isPlaying ? '⏸️ 일시정지' : '▶️ 재생'}
                </button>
                
                <button
                  onClick={() => timelineEditor.seek(0)}
                  className="bg-white/10 hover:bg-white/20 text-white font-semibold py-3 px-4 rounded-lg transition-all"
                >
                  ⏮️
                </button>
                
                <button
                  onClick={() => timelineEditor.seek(duration)}
                  className="bg-white/10 hover:bg-white/20 text-white font-semibold py-3 px-4 rounded-lg transition-all"
                >
                  ⏭️
                </button>
              </div>
              
              {/* 타임코드 */}
              <div className="text-center text-lg font-mono">
                {formatTime(currentTime)} / {formatTime(duration)}
              </div>
              
              {/* 시크바 */}
              <input
                type="range"
                min={0}
                max={duration}
                value={currentTime}
                onChange={handleSeek}
                className="w-full h-2 bg-white/20 rounded-lg appearance-none cursor-pointer"
                style={{
                  background: `linear-gradient(to right, #8b5cf6 0%, #8b5cf6 ${(currentTime/duration)*100}%, rgba(255,255,255,0.2) ${(currentTime/duration)*100}%, rgba(255,255,255,0.2) 100%)`
                }}
              />
            </div>
          </div>
          
          {/* 도구 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
            <h2 className="text-xl font-bold mb-3">🛠️ 도구</h2>
            
            <div className="space-y-2">
              <button className="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all text-left">
                ✂️ 분할
              </button>
              <button className="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all text-left">
                🎨 효과
              </button>
              <button className="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all text-left">
                🔄 트랜지션
              </button>
              <button className="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all text-left">
                🎯 키프레임
              </button>
            </div>
          </div>
        </div>

        {/* 오른쪽: 타임라인 */}
        <div className="flex-1 flex flex-col">
          {/* 타임라인 헤더 */}
          <div className="border-b border-white/20 bg-black/20 p-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-4">
                <label className="text-sm font-semibold">줌:</label>
                <input
                  type="range"
                  min={1}
                  max={100}
                  value={zoom}
                  onChange={(e) => setZoom(parseInt(e.target.value))}
                  className="w-32"
                />
                <span className="text-sm text-gray-400">{zoom}%</span>
              </div>
              
              <div className="flex items-center gap-2">
                <button className="bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all">
                  ➕ 트랙 추가
                </button>
              </div>
            </div>
          </div>
          
          {/* 타임라인 본문 */}
          <div className="flex-1 overflow-auto bg-gradient-to-b from-gray-900/50 to-black/50">
            <div ref={timelineRef} className="relative">
              {/* 시간 눈금 */}
              <div className="sticky top-0 z-10 h-12 bg-black/50 border-b border-white/20 flex items-center">
                {Array.from({ length: Math.ceil(duration / 1000) }).map((_, i) => (
                  <div
                    key={i}
                    className="flex-shrink-0 border-r border-white/10 px-2 text-xs text-gray-400"
                    style={{ width: `${zoom}px` }}
                  >
                    {i}s
                  </div>
                ))}
              </div>
              
              {/* 트랙들 */}
              <div className="space-y-1">
                {tracks.map((track) => (
                  <div key={track.id} className="flex items-center border-b border-white/10">
                    {/* 트랙 헤더 */}
                    <div className="w-48 flex-shrink-0 bg-black/30 border-r border-white/20 p-3">
                      <div className="flex items-center justify-between mb-2">
                        <span className="font-semibold text-sm">{track.name}</span>
                        <div className="flex items-center gap-1">
                          <button className="text-xs hover:bg-white/10 p-1 rounded">
                            {track.muted ? '🔇' : '🔊'}
                          </button>
                          <button className="text-xs hover:bg-white/10 p-1 rounded">
                            {track.locked ? '🔒' : '🔓'}
                          </button>
                        </div>
                      </div>
                      
                      <button
                        onClick={() => handleAddClip(track.id)}
                        className="w-full bg-purple-600/50 hover:bg-purple-600 text-white text-xs font-semibold py-1 px-2 rounded transition-all"
                      >
                        ➕ 클립 추가
                      </button>
                    </div>
                    
                    {/* 트랙 타임라인 */}
                    <div className="flex-1 relative h-20 bg-white/5">
                      {/* 클립들 */}
                      {track.clips.map((clip) => (
                        <div
                          key={clip.id}
                          className="absolute top-2 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded border-2 border-white/30 overflow-hidden cursor-move hover:border-white/60 transition-all"
                          style={{
                            left: `${(clip.startTime / 1000) * zoom}px`,
                            width: `${(clip.duration / 1000) * zoom}px`
                          }}
                        >
                          <div className="p-2 text-xs font-semibold truncate">
                            {clip.name}
                          </div>
                          <div className="absolute bottom-0 left-0 right-0 h-1 bg-white/20" />
                        </div>
                      ))}
                      
                      {/* 재생 헤드 */}
                      {currentTime >= 0 && (
                        <div
                          className="absolute top-0 bottom-0 w-0.5 bg-red-500 z-20"
                          style={{ left: `${(currentTime / 1000) * zoom}px` }}
                        >
                          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-red-500 rounded-full" />
                        </div>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
