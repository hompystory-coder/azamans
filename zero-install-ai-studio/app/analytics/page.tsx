'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { analytics, GenerationStats, PerformanceMetrics, UserBehavior, Insight } from '@/lib/analytics';

export default function AnalyticsPage() {
  const [stats, setStats] = useState<GenerationStats | null>(null);
  const [performance, setPerformance] = useState<PerformanceMetrics | null>(null);
  const [behavior, setBehavior] = useState<UserBehavior | null>(null);
  const [insights, setInsights] = useState<Insight[]>([]);
  const [timeRange, setTimeRange] = useState(30); // days

  useEffect(() => {
    loadAnalytics();
  }, [timeRange]);

  const loadAnalytics = () => {
    const generationStats = analytics.getGenerationStats(timeRange);
    const performanceMetrics = analytics.getPerformanceMetrics();
    const userBehavior = analytics.getUserBehavior();
    const generatedInsights = analytics.generateInsights();

    setStats(generationStats);
    setPerformance(performanceMetrics);
    setBehavior(userBehavior);
    setInsights(generatedInsights);
  };

  const exportData = () => {
    const csv = analytics.exportToCSV();
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `analytics-${new Date().toISOString()}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  };

  const clearOldData = () => {
    if (confirm('30일 이상 된 데이터를 삭제하시겠습니까?')) {
      analytics.clearData(30);
      loadAnalytics();
    }
  };

  const formatDuration = (ms: number) => {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return minutes > 0 ? `${minutes}분 ${remainingSeconds}초` : `${seconds}초`;
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };

  const getInsightIcon = (type: Insight['type']) => {
    switch (type) {
      case 'trend': return '📈';
      case 'recommendation': return '💡';
      case 'warning': return '⚠️';
      case 'achievement': return '🎉';
      default: return '📊';
    }
  };

  const getInsightColor = (priority: Insight['priority']) => {
    switch (priority) {
      case 'high': return 'border-red-500 bg-red-50';
      case 'medium': return 'border-yellow-500 bg-yellow-50';
      case 'low': return 'border-blue-500 bg-blue-50';
      default: return 'border-gray-500 bg-gray-50';
    }
  };

  if (!stats || !performance || !behavior) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-purple-900 via-blue-900 to-black text-white p-8">
        <div className="max-w-7xl mx-auto">
          <div className="text-center py-20">
            <div className="animate-spin text-6xl mb-4">📊</div>
            <p>데이터를 불러오는 중...</p>
          </div>
        </div>
      </div>
    );
  }

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
              <div className="hidden md:block text-white/60">Analytics Dashboard</div>
            </div>
            <div className="flex items-center gap-3">
              <select
                value={timeRange}
                onChange={(e) => setTimeRange(Number(e.target.value))}
                className="px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                <option value={7}>최근 7일</option>
                <option value={30}>최근 30일</option>
                <option value={90}>최근 90일</option>
                <option value={365}>최근 1년</option>
              </select>
              <button
                onClick={exportData}
                className="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
              >
                📥 데이터 내보내기
              </button>
              <button
                onClick={clearOldData}
                className="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
              >
                🗑️ 오래된 데이터 삭제
              </button>
            </div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* 인사이트 카드 */}
        {insights.length > 0 && (
          <div className="mb-8">
            <h2 className="text-2xl font-bold mb-4">💡 인사이트</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {insights.map((insight) => (
                <div
                  key={insight.id}
                  className={`p-4 border-2 rounded-lg ${getInsightColor(insight.priority)}`}
                >
                  <div className="flex items-start gap-3">
                    <div className="text-3xl">{getInsightIcon(insight.type)}</div>
                    <div className="flex-1">
                      <h3 className="font-bold text-gray-900 mb-1">{insight.title}</h3>
                      <p className="text-sm text-gray-700">{insight.description}</p>
                      <p className="text-xs text-gray-500 mt-2">
                        {insight.createdAt.toLocaleString()}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 주요 지표 */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <div className="text-4xl mb-2">🎬</div>
            <div className="text-3xl font-bold mb-1">{stats.totalGenerations}</div>
            <div className="text-white/70 text-sm">총 생성 수</div>
          </div>

          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <div className="text-4xl mb-2">✅</div>
            <div className="text-3xl font-bold mb-1">{stats.successRate.toFixed(1)}%</div>
            <div className="text-white/70 text-sm">성공률</div>
          </div>

          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <div className="text-4xl mb-2">⏱️</div>
            <div className="text-3xl font-bold mb-1">{formatDuration(stats.avgDuration)}</div>
            <div className="text-white/70 text-sm">평균 생성 시간</div>
          </div>

          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <div className="text-4xl mb-2">💾</div>
            <div className="text-3xl font-bold mb-1">{formatFileSize(stats.avgFileSize)}</div>
            <div className="text-white/70 text-sm">평균 파일 크기</div>
          </div>
        </div>

        {/* 인기 프리셋 */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
              <span>🎨</span> 인기 프리셋 TOP 10
            </h2>
            <div className="space-y-3">
              {stats.popularPresets.slice(0, 10).map((preset, index) => (
                <div key={preset.preset} className="flex items-center gap-3">
                  <div className="text-2xl font-bold text-white/40 w-8">#{index + 1}</div>
                  <div className="flex-1">
                    <div className="flex items-center justify-between mb-1">
                      <span className="font-medium">{preset.preset}</span>
                      <span className="text-white/70">{preset.count}회</span>
                    </div>
                    <div className="h-2 bg-white/10 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-gradient-to-r from-purple-500 to-pink-500"
                        style={{
                          width: `${(preset.count / stats.popularPresets[0].count) * 100}%`
                        }}
                      />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
              <span>📱</span> 플랫폼별 생성 수
            </h2>
            <div className="space-y-3">
              {stats.popularFormats.map((format, index) => (
                <div key={format.format} className="flex items-center gap-3">
                  <div className="text-2xl font-bold text-white/40 w-8">#{index + 1}</div>
                  <div className="flex-1">
                    <div className="flex items-center justify-between mb-1">
                      <span className="font-medium">{format.format}</span>
                      <span className="text-white/70">{format.count}회</span>
                    </div>
                    <div className="h-2 bg-white/10 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-gradient-to-r from-blue-500 to-cyan-500"
                        style={{
                          width: `${(format.count / stats.popularFormats[0].count) * 100}%`
                        }}
                      />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* 성능 메트릭 */}
        <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20 mb-8">
          <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
            <span>⚡</span> 성능 메트릭
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <div className="text-white/70 text-sm mb-1">이미지 생성</div>
              <div className="text-2xl font-bold">{formatDuration(performance.avgImageGenerationTime)}</div>
            </div>
            <div>
              <div className="text-white/70 text-sm mb-1">비디오 렌더링</div>
              <div className="text-2xl font-bold">{formatDuration(performance.avgVideoRenderTime)}</div>
            </div>
            <div>
              <div className="text-white/70 text-sm mb-1">TTS 생성</div>
              <div className="text-2xl font-bold">{formatDuration(performance.avgTTSTime)}</div>
            </div>
            <div>
              <div className="text-white/70 text-sm mb-1">전체 평균</div>
              <div className="text-2xl font-bold">{formatDuration(performance.avgTotalTime)}</div>
            </div>
          </div>

          <div className="mt-6">
            <h3 className="font-bold mb-3">병목 구간</h3>
            <div className="space-y-2">
              {performance.bottlenecks.map((bottleneck, index) => (
                <div key={bottleneck.stage} className="flex items-center gap-3">
                  <div className="text-white/60 w-32">{bottleneck.stage}</div>
                  <div className="flex-1">
                    <div className="h-6 bg-white/10 rounded-full overflow-hidden">
                      <div
                        className={`h-full flex items-center justify-end px-3 text-xs font-bold ${
                          index === 0 ? 'bg-red-500' : index === 1 ? 'bg-yellow-500' : 'bg-green-500'
                        }`}
                        style={{
                          width: `${(bottleneck.avgTime / performance.bottlenecks[0].avgTime) * 100}%`
                        }}
                      >
                        {formatDuration(bottleneck.avgTime)}
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* 사용자 행동 */}
        <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20 mb-8">
          <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
            <span>👤</span> 사용자 행동 분석
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <h3 className="font-bold mb-3 text-white/80">가장 많이 사용한 기능</h3>
              <div className="space-y-2">
                {behavior.mostUsedFeatures.slice(0, 5).map((feature, index) => (
                  <div key={feature.feature} className="flex items-center justify-between text-sm">
                    <span className="text-white/70">#{index + 1} {feature.feature}</span>
                    <span className="font-bold">{feature.count}회</span>
                  </div>
                ))}
              </div>
            </div>

            <div>
              <h3 className="font-bold mb-3 text-white/80">선호 프리셋</h3>
              <div className="space-y-2">
                {behavior.favoritePresets.map((preset, index) => (
                  <div key={preset} className="flex items-center gap-2 text-sm">
                    <span className="text-2xl">
                      {index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '⭐'}
                    </span>
                    <span>{preset}</span>
                  </div>
                ))}
              </div>
            </div>

            <div>
              <h3 className="font-bold mb-3 text-white/80">세션 통계</h3>
              <div className="space-y-3">
                <div>
                  <div className="text-white/70 text-sm">평균 세션 시간</div>
                  <div className="text-xl font-bold">{formatDuration(behavior.avgSessionDuration)}</div>
                </div>
                <div>
                  <div className="text-white/70 text-sm">세션당 평균 작업</div>
                  <div className="text-xl font-bold">{behavior.avgActionsPerSession.toFixed(1)}개</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* 일별 통계 차트 */}
        {stats.dailyStats.length > 0 && (
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
              <span>📊</span> 일별 생성 추이
            </h2>
            <div className="h-64 flex items-end justify-between gap-2">
              {stats.dailyStats.slice(-30).map((day) => {
                const maxCount = Math.max(...stats.dailyStats.map(d => d.count));
                const height = (day.count / maxCount) * 100;
                return (
                  <div key={day.date} className="flex-1 flex flex-col items-center gap-2">
                    <div
                      className="w-full bg-gradient-to-t from-purple-500 to-pink-500 rounded-t-lg hover:from-purple-400 hover:to-pink-400 transition-all cursor-pointer group relative"
                      style={{ height: `${height}%` }}
                      title={`${day.date}: ${day.count}개`}
                    >
                      <div className="absolute -top-8 left-1/2 -translate-x-1/2 bg-black/80 px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">
                        {day.count}개
                      </div>
                    </div>
                    <div className="text-xs text-white/50 transform -rotate-45 origin-top-left">
                      {new Date(day.date).toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' })}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
