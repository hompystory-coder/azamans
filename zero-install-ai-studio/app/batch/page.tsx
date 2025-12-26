'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { batchGenerator, BatchJob } from '@/lib/batch-generator';
import { PRESETS } from '@/lib/presets';

export default function BatchPage() {
  const [jobs, setJobs] = useState<BatchJob[]>([]);
  const [stats, setStats] = useState(batchGenerator.getStats());
  const [isProcessing, setIsProcessing] = useState(false);

  // 새 작업 폼
  const [bulkInput, setBulkInput] = useState('');
  const [selectedPreset, setSelectedPreset] = useState('');
  const [selectedPlatform, setSelectedPlatform] = useState('youtube');
  const [priority, setPriority] = useState<'low' | 'normal' | 'high'>('normal');

  useEffect(() => {
    loadJobs();

    // 이벤트 리스너
    const callback = () => {
      loadJobs();
    };

    batchGenerator.on(callback);

    // 자동 새로고침
    const interval = setInterval(() => {
      if (batchGenerator.isProcessing()) {
        loadJobs();
      }
    }, 500);

    return () => {
      batchGenerator.off(callback);
      clearInterval(interval);
    };
  }, []);

  const loadJobs = () => {
    setJobs(batchGenerator.getAllJobs());
    setStats(batchGenerator.getStats());
    setIsProcessing(batchGenerator.isProcessing());
  };

  const handleBulkAdd = () => {
    const lines = bulkInput.split('\n').filter(line => line.trim());
    
    if (lines.length === 0) {
      alert('생성할 프롬프트를 입력해주세요.');
      return;
    }

    lines.forEach((line, index) => {
      const name = `Shorts #${jobs.length + index + 1}`;
      batchGenerator.addJob(name, line.trim(), selectedPlatform, selectedPreset, priority);
    });

    setBulkInput('');
    loadJobs();
  };

  const handleStart = () => {
    batchGenerator.start();
    setIsProcessing(true);
  };

  const handlePause = () => {
    batchGenerator.pause();
    setIsProcessing(false);
  };

  const handleCancelAll = () => {
    if (confirm('모든 대기 중인 작업을 취소하시겠습니까?')) {
      batchGenerator.cancelAll();
      loadJobs();
    }
  };

  const handleClearCompleted = () => {
    batchGenerator.clearCompleted();
    loadJobs();
  };

  const handleCancelJob = (jobId: string) => {
    batchGenerator.cancelJob(jobId);
    loadJobs();
  };

  const handleRemoveJob = (jobId: string) => {
    if (confirm('이 작업을 삭제하시겠습니까?')) {
      batchGenerator.removeJob(jobId);
      loadJobs();
    }
  };

  const getStatusColor = (status: BatchJob['status']) => {
    switch (status) {
      case 'pending': return 'bg-gray-500';
      case 'processing': return 'bg-blue-500';
      case 'completed': return 'bg-green-500';
      case 'failed': return 'bg-red-500';
      case 'cancelled': return 'bg-yellow-500';
      default: return 'bg-gray-500';
    }
  };

  const getStatusText = (status: BatchJob['status']) => {
    switch (status) {
      case 'pending': return '대기중';
      case 'processing': return '처리중';
      case 'completed': return '완료';
      case 'failed': return '실패';
      case 'cancelled': return '취소됨';
      default: return status;
    }
  };

  const getPriorityIcon = (priority: BatchJob['priority']) => {
    switch (priority) {
      case 'high': return '🔴';
      case 'normal': return '🟡';
      case 'low': return '🟢';
      default: return '⚪';
    }
  };

  const formatDuration = (job: BatchJob) => {
    if (!job.startedAt || !job.completedAt) return '-';
    const duration = job.completedAt.getTime() - job.startedAt.getTime();
    const seconds = Math.floor(duration / 1000);
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return minutes > 0 ? `${minutes}분 ${remainingSeconds}초` : `${seconds}초`;
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-purple-900 via-blue-900 to-black text-white">
      {/* 헤더 */}
      <header className="border-b border-white/10 bg-black/20 backdrop-blur-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Link href="/" className="text-2xl font-bold gradient-text">
                Zero-Install AI Studio
              </Link>
              <div className="hidden md:block text-white/60">Batch Generation</div>
            </div>
            <div className="flex items-center gap-3">
              {isProcessing ? (
                <button
                  onClick={handlePause}
                  className="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors"
                >
                  ⏸️ 일시정지
                </button>
              ) : (
                <button
                  onClick={handleStart}
                  disabled={stats.pending === 0}
                  className="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg transition-colors"
                >
                  ▶️ 시작 ({stats.pending}개 대기)
                </button>
              )}
              <button
                onClick={handleCancelAll}
                disabled={stats.pending === 0}
                className="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                ❌ 모두 취소
              </button>
              <button
                onClick={handleClearCompleted}
                disabled={stats.completed === 0}
                className="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                🗑️ 완료 항목 삭제
              </button>
            </div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* 통계 */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
            <div className="text-2xl mb-1">📊</div>
            <div className="text-2xl font-bold">{stats.total}</div>
            <div className="text-xs text-white/70">총 작업</div>
          </div>
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
            <div className="text-2xl mb-1">⏳</div>
            <div className="text-2xl font-bold">{stats.pending}</div>
            <div className="text-xs text-white/70">대기중</div>
          </div>
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
            <div className="text-2xl mb-1">⚙️</div>
            <div className="text-2xl font-bold">{stats.processing}</div>
            <div className="text-xs text-white/70">처리중</div>
          </div>
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
            <div className="text-2xl mb-1">✅</div>
            <div className="text-2xl font-bold">{stats.completed}</div>
            <div className="text-xs text-white/70">완료</div>
          </div>
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
            <div className="text-2xl mb-1">❌</div>
            <div className="text-2xl font-bold">{stats.failed}</div>
            <div className="text-xs text-white/70">실패</div>
          </div>
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
            <div className="text-2xl mb-1">📈</div>
            <div className="text-2xl font-bold">{stats.successRate.toFixed(0)}%</div>
            <div className="text-xs text-white/70">성공률</div>
          </div>
        </div>

        {/* 새 작업 추가 */}
        <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20 mb-8">
          <h2 className="text-xl font-bold mb-4">📝 배치 작업 추가</h2>
          
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <div>
              <label className="block text-sm font-medium mb-2">프리셋</label>
              <select
                value={selectedPreset}
                onChange={(e) => setSelectedPreset(e.target.value)}
                className="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                <option value="">자동 선택</option>
                {PRESETS.map((preset) => (
                  <option key={preset.id} value={preset.id}>
                    {preset.icon} {preset.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">플랫폼</label>
              <select
                value={selectedPlatform}
                onChange={(e) => setSelectedPlatform(e.target.value)}
                className="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                <option value="youtube">YouTube Shorts</option>
                <option value="tiktok">TikTok</option>
                <option value="instagram">Instagram Reels</option>
                <option value="square">Square (1:1)</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">우선순위</label>
              <select
                value={priority}
                onChange={(e) => setPriority(e.target.value as 'low' | 'normal' | 'high')}
                className="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                <option value="low">🟢 낮음</option>
                <option value="normal">🟡 보통</option>
                <option value="high">🔴 높음</option>
              </select>
            </div>
          </div>

          <textarea
            value={bulkInput}
            onChange={(e) => setBulkInput(e.target.value)}
            placeholder="생성할 프롬프트를 한 줄에 하나씩 입력하세요.&#10;예:&#10;고양이가 피아노를 치는 모습&#10;우주를 여행하는 로봇&#10;마법의 숲 속 풍경"
            className="w-full px-4 py-3 bg-white/5 border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 min-h-[200px] font-mono text-sm"
          />

          <div className="flex items-center justify-between mt-4">
            <div className="text-sm text-white/70">
              {bulkInput.split('\n').filter(line => line.trim()).length}개 작업이 추가됩니다
            </div>
            <button
              onClick={handleBulkAdd}
              disabled={!bulkInput.trim()}
              className="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:from-gray-600 disabled:to-gray-600 disabled:cursor-not-allowed rounded-lg font-bold transition-all"
            >
              ➕ 작업 추가
            </button>
          </div>
        </div>

        {/* 작업 목록 */}
        <div className="space-y-3">
          {jobs.length === 0 ? (
            <div className="text-center py-20 bg-white/5 rounded-xl border border-white/10">
              <div className="text-6xl mb-4">📋</div>
              <div className="text-xl mb-2">작업이 없습니다</div>
              <div className="text-white/60">위에서 배치 작업을 추가해보세요!</div>
            </div>
          ) : (
            jobs.map((job) => (
              <div
                key={job.id}
                className="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 hover:border-white/40 transition-all"
              >
                <div className="flex items-start gap-4">
                  {/* 우선순위 */}
                  <div className="text-3xl mt-1">{getPriorityIcon(job.priority)}</div>

                  {/* 정보 */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-start justify-between gap-4 mb-2">
                      <div className="flex-1 min-w-0">
                        <h3 className="font-bold text-lg mb-1">{job.name}</h3>
                        <p className="text-sm text-white/70 truncate">{job.prompt}</p>
                      </div>
                      <div className="flex items-center gap-2">
                        <span className={`px-3 py-1 rounded-full text-xs font-bold ${getStatusColor(job.status)}`}>
                          {getStatusText(job.status)}
                        </span>
                      </div>
                    </div>

                    {/* 프로그레스바 */}
                    {job.status === 'processing' && (
                      <div className="mb-2">
                        <div className="flex items-center justify-between text-xs text-white/70 mb-1">
                          <span>진행 중...</span>
                          <span>{job.progress}%</span>
                        </div>
                        <div className="h-2 bg-white/10 rounded-full overflow-hidden">
                          <div
                            className="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-300"
                            style={{ width: `${job.progress}%` }}
                          />
                        </div>
                      </div>
                    )}

                    {/* 메타 정보 */}
                    <div className="flex flex-wrap items-center gap-4 text-xs text-white/60">
                      {job.preset && <span>🎨 {job.preset}</span>}
                      <span>📱 {job.platform}</span>
                      <span>📅 {job.createdAt.toLocaleString()}</span>
                      {job.status === 'completed' && (
                        <span className="text-green-400">⏱️ {formatDuration(job)}</span>
                      )}
                      {job.retryCount > 0 && (
                        <span className="text-yellow-400">🔄 재시도 {job.retryCount}/{job.maxRetries}</span>
                      )}
                      {job.error && (
                        <span className="text-red-400" title={job.error}>❌ {job.error}</span>
                      )}
                    </div>
                  </div>

                  {/* 액션 버튼 */}
                  <div className="flex items-center gap-2">
                    {job.status === 'pending' && (
                      <button
                        onClick={() => handleCancelJob(job.id)}
                        className="px-3 py-2 bg-yellow-600 hover:bg-yellow-700 rounded-lg text-sm transition-colors"
                        title="취소"
                      >
                        ⏸️
                      </button>
                    )}
                    {(job.status === 'completed' || job.status === 'failed' || job.status === 'cancelled') && (
                      <button
                        onClick={() => handleRemoveJob(job.id)}
                        className="px-3 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm transition-colors"
                        title="삭제"
                      >
                        🗑️
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      </main>
    </div>
  );
}
