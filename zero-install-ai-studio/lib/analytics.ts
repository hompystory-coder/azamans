/**
 * 애널리틱스 시스템 v1.0
 * 
 * 기능:
 * - 생성 통계 추적
 * - 사용 패턴 분석
 * - 성능 메트릭
 * - 인사이트 생성
 * - 데이터 시각화 지원
 */

export interface AnalyticsEvent {
  id: string;
  type: 'generation' | 'edit' | 'export' | 'error' | 'view' | 'interaction';
  action: string;
  timestamp: Date;
  duration?: number; // ms
  metadata: Record<string, any>;
  userId?: string;
  sessionId: string;
}

export interface GenerationStats {
  totalGenerations: number;
  successRate: number;
  avgDuration: number; // ms
  avgFileSize: number; // bytes
  popularPresets: Array<{ preset: string; count: number }>;
  popularFormats: Array<{ format: string; count: number }>;
  peakUsageHours: Array<{ hour: number; count: number }>;
  dailyStats: Array<{ date: string; count: number }>;
}

export interface PerformanceMetrics {
  avgImageGenerationTime: number;
  avgVideoRenderTime: number;
  avgTTSTime: number;
  avgTotalTime: number;
  successRate: number;
  errorRate: number;
  bottlenecks: Array<{ stage: string; avgTime: number }>;
}

export interface UserBehavior {
  mostUsedFeatures: Array<{ feature: string; count: number }>;
  avgSessionDuration: number;
  avgActionsPerSession: number;
  retentionRate: number;
  favoritePresets: string[];
  preferredPlatforms: string[];
}

export interface Insight {
  id: string;
  type: 'trend' | 'recommendation' | 'warning' | 'achievement';
  title: string;
  description: string;
  data: any;
  createdAt: Date;
  priority: 'low' | 'medium' | 'high';
}

export class Analytics {
  private events: AnalyticsEvent[] = [];
  private sessionId: string;
  private storageKey = 'ai-studio-analytics';
  private maxEvents = 10000; // 최대 이벤트 수

  constructor() {
    this.sessionId = this.generateSessionId();
    this.loadFromStorage();
    this.startSession();
  }

  /**
   * 세션 ID 생성
   */
  private generateSessionId(): string {
    return `session-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * 세션 시작
   */
  private startSession(): void {
    this.trackEvent('interaction', 'session_start', {});
  }

  /**
   * 이벤트 추적
   */
  trackEvent(
    type: AnalyticsEvent['type'],
    action: string,
    metadata: Record<string, any> = {},
    duration?: number
  ): void {
    const event: AnalyticsEvent = {
      id: `event-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      type,
      action,
      timestamp: new Date(),
      duration,
      metadata,
      sessionId: this.sessionId
    };

    this.events.push(event);

    // 이벤트 수 제한
    if (this.events.length > this.maxEvents) {
      this.events = this.events.slice(-this.maxEvents);
    }

    this.saveToStorage();
  }

  /**
   * 생성 추적
   */
  trackGeneration(
    preset: string,
    platform: string,
    duration: number,
    success: boolean,
    fileSize?: number
  ): void {
    this.trackEvent('generation', success ? 'generation_success' : 'generation_failed', {
      preset,
      platform,
      success,
      fileSize
    }, duration);
  }

  /**
   * 에러 추적
   */
  trackError(error: Error, context: Record<string, any> = {}): void {
    this.trackEvent('error', 'error_occurred', {
      message: error.message,
      stack: error.stack,
      ...context
    });
  }

  /**
   * 생성 통계 계산
   */
  getGenerationStats(days: number = 30): GenerationStats {
    const cutoffDate = new Date();
    cutoffDate.setDate(cutoffDate.getDate() - days);

    const generationEvents = this.events.filter(
      e => e.type === 'generation' && e.timestamp >= cutoffDate
    );

    const successEvents = generationEvents.filter(
      e => e.action === 'generation_success'
    );

    // 프리셋 통계
    const presetCounts = new Map<string, number>();
    generationEvents.forEach(e => {
      if (e.metadata.preset) {
        presetCounts.set(
          e.metadata.preset,
          (presetCounts.get(e.metadata.preset) || 0) + 1
        );
      }
    });

    const popularPresets = Array.from(presetCounts.entries())
      .map(([preset, count]) => ({ preset, count }))
      .sort((a, b) => b.count - a.count)
      .slice(0, 10);

    // 포맷 통계
    const formatCounts = new Map<string, number>();
    generationEvents.forEach(e => {
      if (e.metadata.platform) {
        formatCounts.set(
          e.metadata.platform,
          (formatCounts.get(e.metadata.platform) || 0) + 1
        );
      }
    });

    const popularFormats = Array.from(formatCounts.entries())
      .map(([format, count]) => ({ format, count }))
      .sort((a, b) => b.count - a.count);

    // 시간대별 통계
    const hourCounts = new Map<number, number>();
    generationEvents.forEach(e => {
      const hour = e.timestamp.getHours();
      hourCounts.set(hour, (hourCounts.get(hour) || 0) + 1);
    });

    const peakUsageHours = Array.from(hourCounts.entries())
      .map(([hour, count]) => ({ hour, count }))
      .sort((a, b) => b.count - a.count)
      .slice(0, 5);

    // 일별 통계
    const dailyCounts = new Map<string, number>();
    generationEvents.forEach(e => {
      const date = e.timestamp.toISOString().split('T')[0];
      dailyCounts.set(date, (dailyCounts.get(date) || 0) + 1);
    });

    const dailyStats = Array.from(dailyCounts.entries())
      .map(([date, count]) => ({ date, count }))
      .sort((a, b) => a.date.localeCompare(b.date));

    return {
      totalGenerations: generationEvents.length,
      successRate: generationEvents.length > 0
        ? (successEvents.length / generationEvents.length) * 100
        : 0,
      avgDuration: successEvents.length > 0
        ? successEvents.reduce((sum, e) => sum + (e.duration || 0), 0) / successEvents.length
        : 0,
      avgFileSize: successEvents.length > 0
        ? successEvents.reduce((sum, e) => sum + (e.metadata.fileSize || 0), 0) / successEvents.length
        : 0,
      popularPresets,
      popularFormats,
      peakUsageHours,
      dailyStats
    };
  }

  /**
   * 성능 메트릭 계산
   */
  getPerformanceMetrics(): PerformanceMetrics {
    const generationEvents = this.events.filter(e => e.type === 'generation');
    const successEvents = generationEvents.filter(e => e.action === 'generation_success');
    const errorEvents = this.events.filter(e => e.type === 'error');

    // 단계별 시간 계산
    const imageEvents = this.events.filter(e => e.action === 'image_generated');
    const videoEvents = this.events.filter(e => e.action === 'video_rendered');
    const ttsEvents = this.events.filter(e => e.action === 'tts_generated');

    const avgImageTime = imageEvents.length > 0
      ? imageEvents.reduce((sum, e) => sum + (e.duration || 0), 0) / imageEvents.length
      : 0;

    const avgVideoTime = videoEvents.length > 0
      ? videoEvents.reduce((sum, e) => sum + (e.duration || 0), 0) / videoEvents.length
      : 0;

    const avgTTSTime = ttsEvents.length > 0
      ? ttsEvents.reduce((sum, e) => sum + (e.duration || 0), 0) / ttsEvents.length
      : 0;

    const avgTotalTime = successEvents.length > 0
      ? successEvents.reduce((sum, e) => sum + (e.duration || 0), 0) / successEvents.length
      : 0;

    const totalEvents = generationEvents.length;
    const successRate = totalEvents > 0
      ? (successEvents.length / totalEvents) * 100
      : 0;

    const errorRate = totalEvents > 0
      ? (errorEvents.length / totalEvents) * 100
      : 0;

    return {
      avgImageGenerationTime: avgImageTime,
      avgVideoRenderTime: avgVideoTime,
      avgTTSTime: avgTTSTime,
      avgTotalTime: avgTotalTime,
      successRate,
      errorRate,
      bottlenecks: [
        { stage: 'Image Generation', avgTime: avgImageTime },
        { stage: 'Video Rendering', avgTime: avgVideoTime },
        { stage: 'TTS Generation', avgTime: avgTTSTime }
      ].sort((a, b) => b.avgTime - a.avgTime)
    };
  }

  /**
   * 사용자 행동 분석
   */
  getUserBehavior(): UserBehavior {
    const sessions = new Map<string, AnalyticsEvent[]>();
    this.events.forEach(event => {
      const sessionEvents = sessions.get(event.sessionId) || [];
      sessionEvents.push(event);
      sessions.set(event.sessionId, sessionEvents);
    });

    // 기능 사용 통계
    const featureCounts = new Map<string, number>();
    this.events.forEach(e => {
      featureCounts.set(e.action, (featureCounts.get(e.action) || 0) + 1);
    });

    const mostUsedFeatures = Array.from(featureCounts.entries())
      .map(([feature, count]) => ({ feature, count }))
      .sort((a, b) => b.count - a.count)
      .slice(0, 10);

    // 세션 통계
    const sessionDurations: number[] = [];
    const sessionActionCounts: number[] = [];

    for (const sessionEvents of sessions.values()) {
      if (sessionEvents.length > 0) {
        const start = sessionEvents[0].timestamp.getTime();
        const end = sessionEvents[sessionEvents.length - 1].timestamp.getTime();
        sessionDurations.push(end - start);
        sessionActionCounts.push(sessionEvents.length);
      }
    }

    const avgSessionDuration = sessionDurations.length > 0
      ? sessionDurations.reduce((a, b) => a + b, 0) / sessionDurations.length
      : 0;

    const avgActionsPerSession = sessionActionCounts.length > 0
      ? sessionActionCounts.reduce((a, b) => a + b, 0) / sessionActionCounts.length
      : 0;

    // 선호 프리셋
    const presetCounts = new Map<string, number>();
    this.events
      .filter(e => e.type === 'generation' && e.metadata.preset)
      .forEach(e => {
        presetCounts.set(
          e.metadata.preset,
          (presetCounts.get(e.metadata.preset) || 0) + 1
        );
      });

    const favoritePresets = Array.from(presetCounts.entries())
      .sort((a, b) => b[1] - a[1])
      .slice(0, 5)
      .map(([preset]) => preset);

    // 선호 플랫폼
    const platformCounts = new Map<string, number>();
    this.events
      .filter(e => e.type === 'generation' && e.metadata.platform)
      .forEach(e => {
        platformCounts.set(
          e.metadata.platform,
          (platformCounts.get(e.metadata.platform) || 0) + 1
        );
      });

    const preferredPlatforms = Array.from(platformCounts.entries())
      .sort((a, b) => b[1] - a[1])
      .map(([platform]) => platform);

    return {
      mostUsedFeatures,
      avgSessionDuration,
      avgActionsPerSession,
      retentionRate: 0, // TODO: 구현 필요
      favoritePresets,
      preferredPlatforms
    };
  }

  /**
   * 인사이트 생성
   */
  generateInsights(): Insight[] {
    const insights: Insight[] = [];
    const stats = this.getGenerationStats(30);
    const performance = this.getPerformanceMetrics();
    const behavior = this.getUserBehavior();

    // 트렌드 인사이트
    if (stats.dailyStats.length >= 7) {
      const recentWeek = stats.dailyStats.slice(-7);
      const previousWeek = stats.dailyStats.slice(-14, -7);
      
      const recentAvg = recentWeek.reduce((sum, d) => sum + d.count, 0) / 7;
      const previousAvg = previousWeek.length > 0
        ? previousWeek.reduce((sum, d) => sum + d.count, 0) / previousWeek.length
        : 0;

      if (recentAvg > previousAvg * 1.2) {
        insights.push({
          id: `insight-${Date.now()}-1`,
          type: 'trend',
          title: '📈 사용량 증가 추세',
          description: `지난 주 대비 ${((recentAvg / previousAvg - 1) * 100).toFixed(0)}% 증가했습니다!`,
          data: { recentAvg, previousAvg },
          createdAt: new Date(),
          priority: 'high'
        });
      }
    }

    // 성능 경고
    if (performance.avgTotalTime > 300000) { // 5분 이상
      insights.push({
        id: `insight-${Date.now()}-2`,
        type: 'warning',
        title: '⚠️ 생성 시간이 느립니다',
        description: `평균 생성 시간: ${(performance.avgTotalTime / 1000).toFixed(0)}초`,
        data: performance,
        createdAt: new Date(),
        priority: 'high'
      });
    }

    // 추천 인사이트
    if (behavior.favoritePresets.length > 0) {
      insights.push({
        id: `insight-${Date.now()}-3`,
        type: 'recommendation',
        title: '💡 추천 프리셋',
        description: `"${behavior.favoritePresets[0]}" 프리셋을 자주 사용하시네요! 비슷한 스타일을 확인해보세요.`,
        data: { favoritePresets: behavior.favoritePresets },
        createdAt: new Date(),
        priority: 'medium'
      });
    }

    // 성취 인사이트
    if (stats.totalGenerations >= 100) {
      insights.push({
        id: `insight-${Date.now()}-4`,
        type: 'achievement',
        title: '🎉 100개 생성 달성!',
        description: `총 ${stats.totalGenerations}개의 쇼츠를 생성했습니다!`,
        data: { totalGenerations: stats.totalGenerations },
        createdAt: new Date(),
        priority: 'low'
      });
    }

    return insights;
  }

  /**
   * 데이터 내보내기 (CSV)
   */
  exportToCSV(): string {
    const headers = ['Timestamp', 'Type', 'Action', 'Duration', 'Session ID', 'Metadata'];
    const rows = this.events.map(e => [
      e.timestamp.toISOString(),
      e.type,
      e.action,
      e.duration || '',
      e.sessionId,
      JSON.stringify(e.metadata)
    ]);

    return [headers, ...rows]
      .map(row => row.map(cell => `"${cell}"`).join(','))
      .join('\n');
  }

  /**
   * 데이터 지우기
   */
  clearData(olderThanDays?: number): void {
    if (olderThanDays) {
      const cutoffDate = new Date();
      cutoffDate.setDate(cutoffDate.getDate() - olderThanDays);
      this.events = this.events.filter(e => e.timestamp >= cutoffDate);
    } else {
      this.events = [];
    }
    this.saveToStorage();
  }

  /**
   * LocalStorage에 저장
   */
  private saveToStorage(): void {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(this.events));
    } catch (error) {
      console.error('Failed to save analytics:', error);
    }
  }

  /**
   * LocalStorage에서 불러오기
   */
  private loadFromStorage(): void {
    try {
      const data = localStorage.getItem(this.storageKey);
      if (data) {
        this.events = JSON.parse(data);
        // Date 객체 복원
        this.events.forEach(e => {
          e.timestamp = new Date(e.timestamp);
        });
      }
    } catch (error) {
      console.error('Failed to load analytics:', error);
    }
  }
}

// 싱글톤 인스턴스
export const analytics = new Analytics();

export default Analytics;
