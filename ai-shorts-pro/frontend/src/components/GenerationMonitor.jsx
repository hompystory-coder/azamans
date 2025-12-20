import { useState, useEffect } from 'react';
import { useAppStore } from '../store/appStore';
import { generationAPI } from '../utils/api';

export default function GenerationMonitor({ projectId, onComplete }) {
  const [progress, setProgress] = useState({
    status: 'idle',
    currentStep: '',
    progress: 0,
    logs: []
  });
  const [videoUrl, setVideoUrl] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!projectId) return;

    // Connect to WebSocket for real-time updates
    const ws = new WebSocket(`ws://localhost:3000/generation/${projectId}`);

    ws.onmessage = (event) => {
      const data = JSON.parse(event.data);
      
      if (data.type === 'progress') {
        setProgress({
          status: data.status,
          currentStep: data.step,
          progress: data.progress,
          logs: [...progress.logs, data.message]
        });
      } else if (data.type === 'complete') {
        setProgress(prev => ({ ...prev, status: 'completed', progress: 100 }));
        setVideoUrl(data.videoUrl);
        if (onComplete) onComplete(data);
      } else if (data.type === 'error') {
        setProgress(prev => ({ ...prev, status: 'failed' }));
        setError(data.error);
      }
    };

    ws.onerror = (error) => {
      console.error('WebSocket error:', error);
      setError('실시간 연결 오류');
    };

    return () => {
      ws.close();
    };
  }, [projectId]);

  const getStepIcon = (step) => {
    const icons = {
      'crawling': '🔍',
      'script_generation': '📝',
      'image_generation': '🎨',
      'video_generation': '🎬',
      'voice_generation': '🎤',
      'rendering': '⚙️',
      'complete': '✅'
    };
    return icons[step] || '⏳';
  };

  const steps = [
    { id: 'crawling', label: '콘텐츠 수집' },
    { id: 'script_generation', label: '스크립트 생성' },
    { id: 'image_generation', label: '이미지 생성' },
    { id: 'video_generation', label: '비디오 생성' },
    { id: 'voice_generation', label: '음성 생성' },
    { id: 'rendering', label: '최종 렌더링' }
  ];

  if (!projectId) {
    return (
      <div className="text-center py-12">
        <div className="text-6xl mb-4">🎬</div>
        <h3 className="text-2xl font-bold text-white mb-2">프로젝트를 선택하세요</h3>
        <p className="text-gray-300">생성 진행 상황을 확인할 수 있습니다</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-3xl font-bold text-white">생성 진행 상황</h2>
        <div className={`px-4 py-2 rounded-full font-semibold ${
          progress.status === 'completed' ? 'bg-green-500 text-white' :
          progress.status === 'failed' ? 'bg-red-500 text-white' :
          'bg-blue-500 text-white'
        }`}>
          {progress.status === 'idle' && '대기 중'}
          {progress.status === 'processing' && '생성 중'}
          {progress.status === 'completed' && '완료'}
          {progress.status === 'failed' && '실패'}
        </div>
      </div>

      {/* Progress Bar */}
      <div className="bg-white/10 border border-white/20 rounded-xl p-6">
        <div className="mb-4">
          <div className="flex items-center justify-between mb-2">
            <span className="text-white font-semibold">{progress.currentStep || '준비 중...'}</span>
            <span className="text-white font-bold">{progress.progress}%</span>
          </div>
          <div className="w-full bg-white/10 rounded-full h-4 overflow-hidden">
            <div
              className="h-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 transition-all duration-500 ease-out"
              style={{ width: `${progress.progress}%` }}
            />
          </div>
        </div>

        {/* Step Indicators */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mt-6">
          {steps.map((step, index) => {
            const isActive = progress.currentStep === step.id;
            const isCompleted = steps.findIndex(s => s.id === progress.currentStep) > index;
            
            return (
              <div
                key={step.id}
                className={`p-4 rounded-lg text-center transition-all ${
                  isActive ? 'bg-purple-600 shadow-lg scale-105' :
                  isCompleted ? 'bg-green-600' :
                  'bg-white/5'
                }`}
              >
                <div className="text-3xl mb-2">{getStepIcon(step.id)}</div>
                <div className={`text-xs font-semibold ${
                  isActive || isCompleted ? 'text-white' : 'text-gray-400'
                }`}>
                  {step.label}
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Logs */}
      <div className="bg-white/10 border border-white/20 rounded-xl p-6">
        <h3 className="text-white font-bold mb-4">실시간 로그</h3>
        <div className="bg-black/30 rounded-lg p-4 h-64 overflow-y-auto font-mono text-sm">
          {progress.logs.length === 0 ? (
            <div className="text-gray-500 text-center py-8">로그가 없습니다</div>
          ) : (
            progress.logs.map((log, index) => (
              <div key={index} className="text-gray-300 mb-1">
                <span className="text-gray-500">[{new Date().toLocaleTimeString()}]</span> {log}
              </div>
            ))
          )}
        </div>
      </div>

      {/* Error Display */}
      {error && (
        <div className="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
          <div className="flex items-start space-x-3">
            <div className="text-3xl">⚠️</div>
            <div>
              <h3 className="text-red-400 font-bold text-lg mb-2">오류 발생</h3>
              <p className="text-red-300">{error}</p>
              <button
                onClick={() => window.location.reload()}
                className="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
              >
                다시 시도
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Video Result */}
      {videoUrl && (
        <div className="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
          <h3 className="text-green-400 font-bold text-xl mb-4">🎉 영상 생성 완료!</h3>
          
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Video Preview */}
            <div>
              <div className="bg-black rounded-lg overflow-hidden" style={{ aspectRatio: '9/16' }}>
                <video
                  src={videoUrl}
                  controls
                  className="w-full h-full"
                  style={{ objectFit: 'contain' }}
                >
                  Your browser does not support the video tag.
                </video>
              </div>
            </div>

            {/* Actions */}
            <div className="space-y-4">
              <div className="bg-white/10 rounded-lg p-4">
                <h4 className="text-white font-bold mb-3">다운로드 & 공유</h4>
                <div className="space-y-2">
                  <a
                    href={videoUrl}
                    download
                    className="block w-full px-4 py-3 bg-blue-600 text-white text-center font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    💾 비디오 다운로드
                  </a>
                  <button
                    onClick={() => {
                      navigator.clipboard.writeText(videoUrl);
                      alert('URL이 클립보드에 복사되었습니다!');
                    }}
                    className="block w-full px-4 py-3 bg-purple-600 text-white text-center font-semibold rounded-lg hover:bg-purple-700 transition-colors"
                  >
                    🔗 URL 복사
                  </button>
                </div>
              </div>

              <div className="bg-white/10 rounded-lg p-4">
                <h4 className="text-white font-bold mb-3">YouTube 업로드 정보</h4>
                <div className="space-y-2 text-sm text-gray-300">
                  <div>
                    <strong className="text-white">제목:</strong>
                    <p className="mt-1 bg-black/30 rounded p-2">프로젝트 제목</p>
                  </div>
                  <div>
                    <strong className="text-white">설명:</strong>
                    <p className="mt-1 bg-black/30 rounded p-2 h-24 overflow-y-auto">
                      AI 자동 생성 설명...
                    </p>
                  </div>
                  <div>
                    <strong className="text-white">태그:</strong>
                    <p className="mt-1 bg-black/30 rounded p-2">#shorts #ai #제품리뷰</p>
                  </div>
                </div>
              </div>

              <div className="bg-white/10 rounded-lg p-4">
                <h4 className="text-white font-bold mb-3">생성 통계</h4>
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between text-gray-300">
                    <span>총 소요 시간:</span>
                    <span className="text-white font-semibold">~25분</span>
                  </div>
                  <div className="flex justify-between text-gray-300">
                    <span>비용:</span>
                    <span className="text-white font-semibold">~$0.30</span>
                  </div>
                  <div className="flex justify-between text-gray-300">
                    <span>해상도:</span>
                    <span className="text-white font-semibold">720x1280 (9:16)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
