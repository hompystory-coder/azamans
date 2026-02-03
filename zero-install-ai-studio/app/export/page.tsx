'use client';

import { useState, useRef } from 'react';
import { batchExporter, type PlatformSpec, type ExportResult } from '@/lib/batch-exporter';

export default function ExportPage() {
  const [uploadedVideo, setUploadedVideo] = useState<File | null>(null);
  const [selectedPlatforms, setSelectedPlatforms] = useState<Set<string>>(new Set());
  const [exporting, setExporting] = useState(false);
  const [exportProgress, setExportProgress] = useState(0);
  const [exportResults, setExportResults] = useState<ExportResult[]>([]);
  const fileInputRef = useRef<HTMLInputElement>(null);
  
  const platforms = batchExporter.getAllPlatforms();
  // const platformGroups = batchExporter.constructor.getPlatformGroups();
  
  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setUploadedVideo(file);
    }
  };
  
  const togglePlatform = (platformId: string) => {
    const newSet = new Set(selectedPlatforms);
    if (newSet.has(platformId)) {
      newSet.delete(platformId);
    } else {
      newSet.add(platformId);
    }
    setSelectedPlatforms(newSet);
  };
  
  const selectGroup = (groupName: string) => {
    // const groupPlatforms = platformGroups[groupName];
    // setSelectedPlatforms(new Set(groupPlatforms));
  };
  
  const handleExport = async () => {
    if (!uploadedVideo || selectedPlatforms.size === 0) {
      alert('비디오와 플랫폼을 선택해주세요.');
      return;
    }
    
    setExporting(true);
    setExportProgress(0);
    
    try {
      const platformIds = Array.from(selectedPlatforms);
      const blob = new Blob([await uploadedVideo.arrayBuffer()], { type: uploadedVideo.type });
      
      // 시뮬레이션: 진행률 업데이트
      const progressInterval = setInterval(() => {
        setExportProgress(prev => {
          if (prev >= 90) {
            clearInterval(progressInterval);
            return 90;
          }
          return prev + 10;
        });
      }, 500);
      
      const results = await batchExporter.exportToMultiplePlatforms(
        blob,
        platformIds,
        {
          quality: 'high',
          format: 'mp4',
          codec: 'h264',
          includeSubtitles: false,
          includeMusic: true
        }
      );
      
      clearInterval(progressInterval);
      setExportProgress(100);
      setExportResults(results);
      
      setTimeout(() => {
        setExporting(false);
      }, 1000);
      
    } catch (error) {
      console.error('Export failed:', error);
      setExporting(false);
      alert('내보내기 실패: ' + (error as Error).message);
    }
  };
  
  const handleDownloadAll = async () => {
    if (exportResults.length === 0) return;
    await batchExporter.downloadAsZip(exportResults);
  };
  
  const handleDownloadSingle = (result: ExportResult) => {
    const a = document.createElement('a');
    a.href = result.url;
    a.download = result.filename;
    a.click();
  };
  
  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 text-white p-8">
      {/* 헤더 */}
      <div className="max-w-7xl mx-auto mb-8">
        <h1 className="text-5xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-purple-400">
          📤 일괄 내보내기
        </h1>
        <p className="text-xl text-gray-300">
          한 번에 여러 플랫폼으로 최적화된 동영상 내보내기
        </p>
      </div>

      <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* 왼쪽: 비디오 업로드 및 플랫폼 선택 */}
        <div className="lg:col-span-1 space-y-6">
          {/* 비디오 업로드 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">1️⃣ 비디오 업로드</h2>
            <input
              ref={fileInputRef}
              type="file"
              accept="video/*"
              onChange={handleFileUpload}
              className="hidden"
            />
            
            {uploadedVideo ? (
              <div className="space-y-3">
                <div className="bg-green-600/20 border border-green-600/50 rounded-xl p-4">
                  <div className="flex items-center gap-3 mb-2">
                    <span className="text-3xl">✅</span>
                    <div className="flex-1">
                      <p className="font-semibold">{uploadedVideo.name}</p>
                      <p className="text-sm text-gray-400">
                        {(uploadedVideo.size / (1024 * 1024)).toFixed(2)} MB
                      </p>
                    </div>
                  </div>
                </div>
                
                <button
                  onClick={() => fileInputRef.current?.click()}
                  className="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-3 px-6 rounded-xl transition-all border border-white/20"
                >
                  다른 파일 선택
                </button>
              </div>
            ) : (
              <button
                onClick={() => fileInputRef.current?.click()}
                className="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-4 px-6 rounded-xl transition-all"
              >
                📁 비디오 선택
              </button>
            )}
          </div>

          {/* 빠른 선택 */}
          {/* <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">⚡ 빠른 선택</h2>
            <div className="space-y-2">
              {Object.entries(platformGroups).map(([groupName, _]) => (
                <button
                  key={groupName}
                  onClick={() => selectGroup(groupName)}
                  className="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-2 px-4 rounded-lg transition-all border border-white/20 text-left"
                >
                  {groupName === 'all' && '🌐 모든 플랫폼'}
                  {groupName === 'shorts' && '📱 쇼츠 전용'}
                  {groupName === 'social' && '👥 소셜 미디어'}
                  {groupName === 'vertical' && '⬆️ 세로형'}
                  {groupName === 'horizontal' && '↔️ 가로형'}
                </button>
              ))}
            </div>
          </div> */}

          {/* 내보내기 버튼 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <button
              onClick={handleExport}
              disabled={!uploadedVideo || selectedPlatforms.size === 0 || exporting}
              className="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 px-6 rounded-xl transition-all"
            >
              {exporting ? '내보내는 중...' : `🚀 ${selectedPlatforms.size}개 플랫폼으로 내보내기`}
            </button>
            
            {exporting && (
              <div className="mt-4">
                <div className="bg-white/10 rounded-full h-4 overflow-hidden">
                  <div
                    className="bg-gradient-to-r from-green-500 to-emerald-500 h-full transition-all duration-300"
                    style={{ width: `${exportProgress}%` }}
                  />
                </div>
                <p className="text-center text-sm text-gray-400 mt-2">
                  {exportProgress}% 완료
                </p>
              </div>
            )}
          </div>
        </div>

        {/* 오른쪽: 플랫폼 선택 및 결과 */}
        <div className="lg:col-span-2 space-y-6">
          {/* 플랫폼 선택 */}
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 className="text-2xl font-bold mb-4">2️⃣ 플랫폼 선택</h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {platforms.map((platform) => (
                <div
                  key={platform.id}
                  onClick={() => togglePlatform(platform.id)}
                  className={`p-4 rounded-xl border-2 cursor-pointer transition-all ${
                    selectedPlatforms.has(platform.id)
                      ? 'bg-purple-600/30 border-purple-400'
                      : 'bg-white/5 border-white/20 hover:border-white/40'
                  }`}
                >
                  <div className="flex items-start gap-3">
                    <div className="text-3xl">{platform.icon}</div>
                    <div className="flex-1">
                      <h3 className="font-bold text-lg mb-1">{platform.name}</h3>
                      <p className="text-sm text-gray-400 mb-2">{platform.description}</p>
                      <div className="flex flex-wrap gap-2 text-xs">
                        <span className="px-2 py-1 bg-blue-600/50 rounded">
                          {platform.aspectRatio}
                        </span>
                        <span className="px-2 py-1 bg-green-600/50 rounded">
                          {platform.width}x{platform.height}
                        </span>
                        <span className="px-2 py-1 bg-purple-600/50 rounded">
                          {platform.fps}fps
                        </span>
                      </div>
                    </div>
                    {selectedPlatforms.has(platform.id) && (
                      <div className="text-2xl">✅</div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* 내보내기 결과 */}
          {exportResults.length > 0 && (
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-2xl font-bold">3️⃣ 내보내기 완료!</h2>
                <button
                  onClick={handleDownloadAll}
                  className="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold py-2 px-6 rounded-xl transition-all"
                >
                  📦 전체 다운로드
                </button>
              </div>
              
              <div className="space-y-3">
                {exportResults.map((result, index) => (
                  <div
                    key={index}
                    className="bg-white/5 rounded-xl p-4 border border-white/10"
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex-1">
                        <h3 className="font-bold text-lg mb-1">{result.platform}</h3>
                        <p className="text-sm text-gray-400 mb-2">{result.filename}</p>
                        <div className="flex items-center gap-4 text-xs text-gray-400">
                          <span>📦 {(result.size / (1024 * 1024)).toFixed(2)} MB</span>
                          <span>⏱️ {result.duration.toFixed(1)}초</span>
                        </div>
                      </div>
                      
                      <button
                        onClick={() => handleDownloadSingle(result)}
                        className="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold py-2 px-4 rounded-lg transition-all"
                      >
                        💾 다운로드
                      </button>
                    </div>
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
