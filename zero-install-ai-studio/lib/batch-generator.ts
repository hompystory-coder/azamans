/**
 * 배치 생성 시스템 v1.0
 * 
 * 기능:
 * - 여러 쇼츠 동시 생성
 * - 작업 큐 관리
 * - 병렬 처리
 * - 진행 상황 추적
 * - 실패 재시도
 * - 우선순위 처리
 */

export interface BatchJob {
  id: string;
  name: string;
  prompt: string;
  preset?: string;
  platform: string;
  priority: 'low' | 'normal' | 'high';
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled';
  progress: number; // 0-100
  createdAt: Date;
  startedAt?: Date;
  completedAt?: Date;
  error?: string;
  retryCount: number;
  maxRetries: number;
  result?: {
    images: string[];
    video?: string;
    audio?: string;
  };
}

export interface BatchConfig {
  maxConcurrent: number; // 동시 처리 개수
  maxRetries: number;
  retryDelay: number; // ms
  autoStart: boolean;
}

export type BatchEventType = 
  | 'job_added'
  | 'job_started'
  | 'job_progress'
  | 'job_completed'
  | 'job_failed'
  | 'job_cancelled'
  | 'batch_completed';

export type BatchEventCallback = (type: BatchEventType, job: BatchJob) => void;

export class BatchGenerator {
  private jobs: Map<string, BatchJob> = new Map();
  private queue: string[] = []; // job IDs in priority order
  private processing: Set<string> = new Set();
  private config: BatchConfig;
  private eventCallbacks: BatchEventCallback[] = [];
  private isRunning: boolean = false;

  constructor(config?: Partial<BatchConfig>) {
    this.config = {
      maxConcurrent: config?.maxConcurrent || 3,
      maxRetries: config?.maxRetries || 2,
      retryDelay: config?.retryDelay || 5000,
      autoStart: config?.autoStart !== false
    };
  }

  /**
   * 작업 추가
   */
  addJob(
    name: string,
    prompt: string,
    platform: string,
    preset?: string,
    priority: BatchJob['priority'] = 'normal'
  ): BatchJob {
    const job: BatchJob = {
      id: `job-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      name,
      prompt,
      preset,
      platform,
      priority,
      status: 'pending',
      progress: 0,
      createdAt: new Date(),
      retryCount: 0,
      maxRetries: this.config.maxRetries
    };

    this.jobs.set(job.id, job);
    this.addToQueue(job.id);
    this.emitEvent('job_added', job);

    if (this.config.autoStart && !this.isRunning) {
      this.start();
    }

    return job;
  }

  /**
   * 우선순위에 따라 큐에 추가
   */
  private addToQueue(jobId: string): void {
    const job = this.jobs.get(jobId);
    if (!job) return;

    const priorityOrder = { high: 0, normal: 1, low: 2 };
    const jobPriority = priorityOrder[job.priority];

    let insertIndex = this.queue.length;
    for (let i = 0; i < this.queue.length; i++) {
      const queueJob = this.jobs.get(this.queue[i]);
      if (queueJob && priorityOrder[queueJob.priority] > jobPriority) {
        insertIndex = i;
        break;
      }
    }

    this.queue.splice(insertIndex, 0, jobId);
  }

  /**
   * 배치 시작
   */
  async start(): Promise<void> {
    if (this.isRunning) return;
    this.isRunning = true;

    while (this.queue.length > 0 || this.processing.size > 0) {
      // 동시 처리 개수 제한
      while (
        this.processing.size < this.config.maxConcurrent &&
        this.queue.length > 0
      ) {
        const jobId = this.queue.shift()!;
        this.processJob(jobId);
      }

      // 잠시 대기
      await new Promise(resolve => setTimeout(resolve, 100));
    }

    this.isRunning = false;
    this.emitEvent('batch_completed', {} as BatchJob);
  }

  /**
   * 배치 일시정지
   */
  pause(): void {
    this.isRunning = false;
  }

  /**
   * 작업 처리
   */
  private async processJob(jobId: string): Promise<void> {
    const job = this.jobs.get(jobId);
    if (!job) return;

    this.processing.add(jobId);
    job.status = 'processing';
    job.startedAt = new Date();
    this.emitEvent('job_started', job);

    try {
      // 실제 생성 로직 (여기서는 시뮬레이션)
      await this.simulateGeneration(job);

      job.status = 'completed';
      job.completedAt = new Date();
      job.progress = 100;
      this.emitEvent('job_completed', job);
    } catch (error) {
      console.error(`Job ${jobId} failed:`, error);

      if (job.retryCount < job.maxRetries) {
        // 재시도
        job.retryCount++;
        job.status = 'pending';
        job.progress = 0;
        
        await new Promise(resolve => setTimeout(resolve, this.config.retryDelay));
        
        this.addToQueue(jobId);
      } else {
        // 최종 실패
        job.status = 'failed';
        job.error = error instanceof Error ? error.message : 'Unknown error';
        this.emitEvent('job_failed', job);
      }
    } finally {
      this.processing.delete(jobId);
    }
  }

  /**
   * 생성 시뮬레이션 (실제로는 AI 엔진과 연동)
   */
  private async simulateGeneration(job: BatchJob): Promise<void> {
    const stages = [
      { name: '프롬프트 분석', duration: 1000 },
      { name: '이미지 생성 (1/3)', duration: 2000 },
      { name: '이미지 생성 (2/3)', duration: 2000 },
      { name: '이미지 생성 (3/3)', duration: 2000 },
      { name: 'TTS 생성', duration: 1500 },
      { name: '비디오 렌더링', duration: 2500 }
    ];

    const totalDuration = stages.reduce((sum, s) => sum + s.duration, 0);
    let elapsed = 0;

    for (const stage of stages) {
      await new Promise(resolve => setTimeout(resolve, stage.duration));
      elapsed += stage.duration;
      job.progress = Math.round((elapsed / totalDuration) * 100);
      this.emitEvent('job_progress', job);
    }

    // 결과 저장
    job.result = {
      images: [
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
      ],
      video: 'blob:...',
      audio: 'blob:...'
    };
  }

  /**
   * 작업 취소
   */
  cancelJob(jobId: string): boolean {
    const job = this.jobs.get(jobId);
    if (!job || job.status === 'completed') return false;

    if (job.status === 'processing') {
      // 처리 중인 작업은 완료를 기다림
      return false;
    }

    // 큐에서 제거
    const index = this.queue.indexOf(jobId);
    if (index > -1) {
      this.queue.splice(index, 1);
    }

    job.status = 'cancelled';
    this.emitEvent('job_cancelled', job);
    return true;
  }

  /**
   * 작업 제거
   */
  removeJob(jobId: string): boolean {
    if (this.processing.has(jobId)) {
      return false; // 처리 중인 작업은 제거 불가
    }

    this.cancelJob(jobId);
    return this.jobs.delete(jobId);
  }

  /**
   * 모든 작업 취소
   */
  cancelAll(): void {
    for (const jobId of this.queue) {
      this.cancelJob(jobId);
    }
    this.queue = [];
  }

  /**
   * 완료된 작업 제거
   */
  clearCompleted(): void {
    for (const [jobId, job] of this.jobs.entries()) {
      if (job.status === 'completed') {
        this.jobs.delete(jobId);
      }
    }
  }

  /**
   * 작업 가져오기
   */
  getJob(jobId: string): BatchJob | null {
    return this.jobs.get(jobId) || null;
  }

  /**
   * 모든 작업 가져오기
   */
  getAllJobs(): BatchJob[] {
    return Array.from(this.jobs.values());
  }

  /**
   * 상태별 작업 가져오기
   */
  getJobsByStatus(status: BatchJob['status']): BatchJob[] {
    return Array.from(this.jobs.values()).filter(job => job.status === status);
  }

  /**
   * 통계
   */
  getStats(): {
    total: number;
    pending: number;
    processing: number;
    completed: number;
    failed: number;
    cancelled: number;
    successRate: number;
    avgDuration: number;
  } {
    const jobs = this.getAllJobs();
    const completed = jobs.filter(j => j.status === 'completed');
    const failed = jobs.filter(j => j.status === 'failed');

    const durations = completed
      .filter(j => j.startedAt && j.completedAt)
      .map(j => j.completedAt!.getTime() - j.startedAt!.getTime());

    const avgDuration = durations.length > 0
      ? durations.reduce((sum, d) => sum + d, 0) / durations.length
      : 0;

    const total = jobs.length;
    const finishedJobs = completed.length + failed.length;
    const successRate = finishedJobs > 0
      ? (completed.length / finishedJobs) * 100
      : 0;

    return {
      total: jobs.length,
      pending: jobs.filter(j => j.status === 'pending').length,
      processing: jobs.filter(j => j.status === 'processing').length,
      completed: completed.length,
      failed: failed.length,
      cancelled: jobs.filter(j => j.status === 'cancelled').length,
      successRate,
      avgDuration
    };
  }

  /**
   * 이벤트 리스너 등록
   */
  on(callback: BatchEventCallback): void {
    this.eventCallbacks.push(callback);
  }

  /**
   * 이벤트 리스너 제거
   */
  off(callback: BatchEventCallback): void {
    const index = this.eventCallbacks.indexOf(callback);
    if (index > -1) {
      this.eventCallbacks.splice(index, 1);
    }
  }

  /**
   * 이벤트 발생
   */
  private emitEvent(type: BatchEventType, job: BatchJob): void {
    for (const callback of this.eventCallbacks) {
      try {
        callback(type, job);
      } catch (error) {
        console.error('Event callback error:', error);
      }
    }
  }

  /**
   * 설정 업데이트
   */
  updateConfig(config: Partial<BatchConfig>): void {
    this.config = { ...this.config, ...config };
  }

  /**
   * 큐 우선순위 재정렬
   */
  reorderQueue(): void {
    const jobs = this.queue.map(id => this.jobs.get(id)).filter(Boolean) as BatchJob[];
    const priorityOrder = { high: 0, normal: 1, low: 2 };
    
    jobs.sort((a, b) => {
      const priorityDiff = priorityOrder[a.priority] - priorityOrder[b.priority];
      if (priorityDiff !== 0) return priorityDiff;
      return a.createdAt.getTime() - b.createdAt.getTime();
    });

    this.queue = jobs.map(j => j.id);
  }

  /**
   * 진행 중인 작업 상태
   */
  isProcessing(): boolean {
    return this.processing.size > 0 || this.isRunning;
  }

  /**
   * 남은 작업 수
   */
  getRemainingCount(): number {
    return this.queue.length + this.processing.size;
  }
}

// 싱글톤 인스턴스
export const batchGenerator = new BatchGenerator();

export default BatchGenerator;
