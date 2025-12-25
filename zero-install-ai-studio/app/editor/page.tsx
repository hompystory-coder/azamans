'use client';

import { useState, useRef, useEffect } from 'react';
import { videoFilters, type VideoFilter } from '@/lib/video-filters';
import { subtitleEngine, type SubtitleSegment, type SubtitleStyle } from '@/lib/subtitle-engine';

export default function AdvancedEditorPage() {
  const [uploadedVideo, setUploadedVideo] = useState<string | null>(null);
  const [selectedFilter, setSelectedFilter] = useState<string>('none');
  const [filters, setFilters] = useState<VideoFilter[]>([]);
  
  // 자막 관련 상태
  const [subtitles, setSubtitles] = useState<SubtitleSegment[]>([]);
  const [subtitleStyle, setSubtitleStyle] = useState<SubtitleStyle>(subtitleEngine.constructor.getDefaultStyle());
  const [showSubtitleEditor, setShowSubtitleEditor] = useState(false);
  
  // Canvas 및 비디오 refs
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const videoRef = useRef<HTMLVideoElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  
  useEffect(() => {
    // 모든 필터 로드
    setFilters(videoFilters.getAllFilters());
  }, []);
  
  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const url = URL.createObjectURL(file);
      setUploadedVideo(url);
    }
  };
  
  const applyFilter = () => {
    if (!canvasRef.current || !videoRef.current) return;
    
    const canvas = canvasRef.current;
    const video = videoRef.current;
    const ctx = canvas.getContext('2d');
    
    if (!ctx) return;
    
    // 비디오를 캔버스에 그리기
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);
    
    // 필터 적용
    if (selectedFilter !== 'none') {
      videoFilters.applyFilterToCanvas(canvas, selectedFilter);
    }
  };
  
  const generateSubtitles = async () => {
    // 샘플 스크립트로 자막 생성
    const sampleScript = "안녕하세요! 오늘은 멋진 하루입니다. 이 영상에서는 놀라운 내용을 소개합니다. 지금 바로 시작해보세요!";
    const duration = videoRef.current?.duration || 30; // 초 단위
    
    const segments = subtitleEngine.generateFromText(sampleScript, duration * 1000);
    setSubtitles(segments);
    setShowSubtitleEditor(true);
  };
  
  const exportSubtitles = (format: 'srt' | 'vtt') => {
    const content = format === 'srt' 
      ? subtitleEngine.exportToSRT(subtitles)
      : subtitleEngine.exportToVTT(subtitles);
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `subtitles.${format}`;
    a.click();
  };
  
  const downloadFilteredVideo = () => {
    if (!canvasRef.current) return;
    
    const url = canvasRef.current.toDataURL('image/png');
    const a = document.createElement('a');
    a.href = url;
    a.download = 'filtered_frame.png';
    a.click();
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 text-white p-8">
      {/* 헤더 */}
      <div className="max-w-7xl mx-auto mb-8">
        <h1 className="text-5xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-purple-400">
          🎬 고급 편집 스튜디오
        </h1>
        <p className="text-xl text-gray-300">
          프로페셔널 필터 + 자막 시스템
        </p>
      </div>

      <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* 왼쪽: 컨트롤 패널 */}
        <div className="lg:col-span-1 space-y-6">
          {/* 비디오 업로드 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">📁 비디오 업로드</h2>
            <input
              ref={fileInputRef}
              type="file"
              accept="video/*"
              onChange={handleFileUpload}
              className="hidden"
            />
            <button
              onClick={() => fileInputRef.current?.click()}
              className="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 px-6 rounded-xl transition-all"
            >
              비디오 선택
            </button>
          </div>

          {/* 필터 선택 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">🎨 필터 선택</h2>
            <div className="grid grid-cols-2 gap-3">
              <button
                onClick={() => setSelectedFilter('none')}
                className={`p-4 rounded-xl border-2 transition-all ${
                  selectedFilter === 'none'
                    ? 'bg-purple-600 border-purple-400'
                    : 'bg-white/5 border-white/20 hover:border-white/40'
                }`}
              >
                <div className="text-3xl mb-2">🚫</div>
                <div className="font-semibold">필터 없음</div>
              </button>
              {filters.map((filter) => (
                <button
                  key={filter.id}
                  onClick={() => setSelectedFilter(filter.id)}
                  className={`p-4 rounded-xl border-2 transition-all ${
                    selectedFilter === filter.id
                      ? 'bg-purple-600 border-purple-400'
                      : 'bg-white/5 border-white/20 hover:border-white/40'
                  }`}
                >
                  <div className="text-3xl mb-2">{filter.thumbnail}</div>
                  <div className="font-semibold text-sm">{filter.name}</div>
                </button>
              ))}
            </div>
            <button
              onClick={applyFilter}
              disabled={!uploadedVideo}
              className="w-full mt-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-all"
            >
              필터 적용
            </button>
          </div>

          {/* 자막 컨트롤 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">💬 자막 시스템</h2>
            <div className="space-y-3">
              <button
                onClick={generateSubtitles}
                disabled={!uploadedVideo}
                className="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-all"
              >
                자막 자동 생성
              </button>
              
              {subtitles.length > 0 && (
                <>
                  <button
                    onClick={() => setShowSubtitleEditor(!showSubtitleEditor)}
                    className="w-full bg-white/10 hover:bg-white/20 text-white font-bold py-3 px-6 rounded-xl transition-all border border-white/20"
                  >
                    {showSubtitleEditor ? '편집기 닫기' : '편집기 열기'}
                  </button>
                  
                  <div className="flex gap-2">
                    <button
                      onClick={() => exportSubtitles('srt')}
                      className="flex-1 bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all border border-white/20 text-sm"
                    >
                      SRT 내보내기
                    </button>
                    <button
                      onClick={() => exportSubtitles('vtt')}
                      className="flex-1 bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all border border-white/20 text-sm"
                    >
                      VTT 내보내기
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>

          {/* 내보내기 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">💾 내보내기</h2>
            <button
              onClick={downloadFilteredVideo}
              disabled={!uploadedVideo}
              className="w-full bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-all"
            >
              프레임 다운로드
            </button>
          </div>
        </div>

        {/* 오른쪽: 미리보기 및 편집 */}
        <div className="lg:col-span-2 space-y-6">
          {/* 비디오 미리보기 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">📺 미리보기</h2>
            
            <div className="relative aspect-video bg-black/50 rounded-xl overflow-hidden mb-4">
              {uploadedVideo ? (
                <video
                  ref={videoRef}
                  src={uploadedVideo}
                  controls
                  className="w-full h-full"
                  onLoadedMetadata={applyFilter}
                />
              ) : (
                <div className="flex items-center justify-center h-full">
                  <div className="text-center">
                    <div className="text-6xl mb-4">🎥</div>
                    <p className="text-xl text-gray-400">비디오를 업로드하세요</p>
                  </div>
                </div>
              )}
            </div>

            {/* 필터 적용된 프레임 */}
            <div className="relative aspect-video bg-black/50 rounded-xl overflow-hidden">
              <canvas
                ref={canvasRef}
                className="w-full h-full object-contain"
              />
              {!uploadedVideo && (
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="text-center">
                    <div className="text-6xl mb-4">🖼️</div>
                    <p className="text-xl text-gray-400">필터 적용 결과</p>
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* 자막 편집기 */}
          {showSubtitleEditor && subtitles.length > 0 && (
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
              <h2 className="text-2xl font-bold mb-4">✏️ 자막 편집기</h2>
              
              <div className="space-y-3 max-h-96 overflow-y-auto">
                {subtitles.map((subtitle) => (
                  <div
                    key={subtitle.id}
                    className="bg-white/5 rounded-xl p-4 border border-white/10"
                  >
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm font-semibold text-cyan-400">
                        #{subtitle.id}
                      </span>
                      <span className="text-xs text-gray-400">
                        {(subtitle.startTime / 1000).toFixed(2)}s - {(subtitle.endTime / 1000).toFixed(2)}s
                      </span>
                    </div>
                    <input
                      type="text"
                      value={subtitle.text}
                      onChange={(e) => {
                        const updated = subtitles.map(s =>
                          s.id === subtitle.id ? { ...s, text: e.target.value } : s
                        );
                        setSubtitles(updated);
                      }}
                      className="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
