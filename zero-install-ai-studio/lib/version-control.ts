/**
 * 버전 관리 시스템 v1.0
 * 
 * 기능:
 * - 쇼츠의 여러 버전 관리
 * - 버전 비교
 * - 롤백 기능
 * - 변경 이력 추적
 * - 자동 버전 생성
 */

export interface Version {
  id: string;
  projectId: string;
  version: number;
  name: string;
  description?: string;
  createdAt: Date;
  createdBy: string;
  
  // 버전 데이터
  data: {
    prompt: string;
    preset?: string;
    images: string[];
    video?: string;
    audio?: string;
    settings: Record<string, any>;
  };
  
  // 메타데이터
  metadata: {
    duration: number;
    resolution: string;
    fps: number;
    fileSize: number;
  };
  
  // 변경 사항
  changes?: VersionChange[];
  
  // 상태
  status: 'draft' | 'published' | 'archived';
  tags: string[];
}

export interface VersionChange {
  field: string;
  oldValue: any;
  newValue: any;
  timestamp: Date;
}

export interface VersionComparison {
  version1: Version;
  version2: Version;
  differences: {
    field: string;
    value1: any;
    value2: any;
    type: 'added' | 'removed' | 'modified';
  }[];
}

export class VersionControl {
  private versions: Map<string, Version[]> = new Map();
  private currentVersion: Map<string, string> = new Map();
  private storageKey = 'ai-studio-versions';

  constructor() {
    this.loadFromStorage();
  }

  /**
   * 새 버전 생성
   */
  createVersion(
    projectId: string,
    name: string,
    data: Version['data'],
    metadata: Version['metadata'],
    description?: string
  ): Version {
    const projectVersions = this.versions.get(projectId) || [];
    const versionNumber = projectVersions.length + 1;

    // 이전 버전과 비교하여 변경사항 추적
    const changes: VersionChange[] = [];
    if (projectVersions.length > 0) {
      const prevVersion = projectVersions[projectVersions.length - 1];
      changes.push(...this.detectChanges(prevVersion.data, data));
    }

    const version: Version = {
      id: `${projectId}-v${versionNumber}`,
      projectId,
      version: versionNumber,
      name,
      description,
      createdAt: new Date(),
      createdBy: 'user', // TODO: 실제 사용자 정보
      data,
      metadata,
      changes,
      status: 'draft',
      tags: []
    };

    projectVersions.push(version);
    this.versions.set(projectId, projectVersions);
    this.currentVersion.set(projectId, version.id);
    
    this.saveToStorage();
    return version;
  }

  /**
   * 변경사항 감지
   */
  private detectChanges(oldData: any, newData: any): VersionChange[] {
    const changes: VersionChange[] = [];
    const allKeys = new Set([...Object.keys(oldData), ...Object.keys(newData)]);

    for (const key of allKeys) {
      const oldValue = oldData[key];
      const newValue = newData[key];

      if (JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
        changes.push({
          field: key,
          oldValue,
          newValue,
          timestamp: new Date()
        });
      }
    }

    return changes;
  }

  /**
   * 버전 가져오기
   */
  getVersion(projectId: string, versionId: string): Version | null {
    const versions = this.versions.get(projectId);
    return versions?.find(v => v.id === versionId) || null;
  }

  /**
   * 프로젝트의 모든 버전 가져오기
   */
  getVersions(projectId: string): Version[] {
    return this.versions.get(projectId) || [];
  }

  /**
   * 현재 버전 가져오기
   */
  getCurrentVersion(projectId: string): Version | null {
    const versionId = this.currentVersion.get(projectId);
    if (!versionId) return null;
    return this.getVersion(projectId, versionId);
  }

  /**
   * 버전으로 복원 (롤백)
   */
  restoreVersion(projectId: string, versionId: string): Version | null {
    const version = this.getVersion(projectId, versionId);
    if (!version) return null;

    // 새 버전으로 복사 생성
    const restoredVersion = this.createVersion(
      projectId,
      `${version.name} (Restored)`,
      version.data,
      version.metadata,
      `Restored from v${version.version}`
    );

    return restoredVersion;
  }

  /**
   * 버전 비교
   */
  compareVersions(
    projectId: string,
    versionId1: string,
    versionId2: string
  ): VersionComparison | null {
    const v1 = this.getVersion(projectId, versionId1);
    const v2 = this.getVersion(projectId, versionId2);

    if (!v1 || !v2) return null;

    const differences: VersionComparison['differences'] = [];

    // 데이터 비교
    const allDataKeys = new Set([
      ...Object.keys(v1.data),
      ...Object.keys(v2.data)
    ]);

    for (const key of allDataKeys) {
      const value1 = (v1.data as any)[key];
      const value2 = (v2.data as any)[key];

      if (JSON.stringify(value1) !== JSON.stringify(value2)) {
        let type: 'added' | 'removed' | 'modified';
        if (value1 === undefined) type = 'added';
        else if (value2 === undefined) type = 'removed';
        else type = 'modified';

        differences.push({
          field: key,
          value1,
          value2,
          type
        });
      }
    }

    // 메타데이터 비교
    const metadataKeys: (keyof Version['metadata'])[] = [
      'duration',
      'resolution',
      'fps',
      'fileSize'
    ];

    for (const key of metadataKeys) {
      if (v1.metadata[key] !== v2.metadata[key]) {
        differences.push({
          field: `metadata.${key}`,
          value1: v1.metadata[key],
          value2: v2.metadata[key],
          type: 'modified'
        });
      }
    }

    return {
      version1: v1,
      version2: v2,
      differences
    };
  }

  /**
   * 버전 업데이트
   */
  updateVersion(
    projectId: string,
    versionId: string,
    updates: Partial<Pick<Version, 'name' | 'description' | 'status' | 'tags'>>
  ): Version | null {
    const versions = this.versions.get(projectId);
    if (!versions) return null;

    const versionIndex = versions.findIndex(v => v.id === versionId);
    if (versionIndex === -1) return null;

    versions[versionIndex] = {
      ...versions[versionIndex],
      ...updates
    };

    this.saveToStorage();
    return versions[versionIndex];
  }

  /**
   * 버전 삭제
   */
  deleteVersion(projectId: string, versionId: string): boolean {
    const versions = this.versions.get(projectId);
    if (!versions) return false;

    const index = versions.findIndex(v => v.id === versionId);
    if (index === -1) return false;

    versions.splice(index, 1);
    
    // 현재 버전이 삭제된 경우 업데이트
    if (this.currentVersion.get(projectId) === versionId) {
      const newCurrent = versions[versions.length - 1];
      if (newCurrent) {
        this.currentVersion.set(projectId, newCurrent.id);
      } else {
        this.currentVersion.delete(projectId);
      }
    }

    this.saveToStorage();
    return true;
  }

  /**
   * 프로젝트의 모든 버전 삭제
   */
  deleteAllVersions(projectId: string): boolean {
    const deleted = this.versions.delete(projectId);
    this.currentVersion.delete(projectId);
    this.saveToStorage();
    return deleted;
  }

  /**
   * 버전 게시
   */
  publishVersion(projectId: string, versionId: string): Version | null {
    return this.updateVersion(projectId, versionId, { status: 'published' });
  }

  /**
   * 버전 보관
   */
  archiveVersion(projectId: string, versionId: string): Version | null {
    return this.updateVersion(projectId, versionId, { status: 'archived' });
  }

  /**
   * 태그 추가
   */
  addTag(projectId: string, versionId: string, tag: string): Version | null {
    const version = this.getVersion(projectId, versionId);
    if (!version) return null;

    if (!version.tags.includes(tag)) {
      version.tags.push(tag);
      return this.updateVersion(projectId, versionId, { tags: version.tags });
    }

    return version;
  }

  /**
   * 태그 제거
   */
  removeTag(projectId: string, versionId: string, tag: string): Version | null {
    const version = this.getVersion(projectId, versionId);
    if (!version) return null;

    const index = version.tags.indexOf(tag);
    if (index > -1) {
      version.tags.splice(index, 1);
      return this.updateVersion(projectId, versionId, { tags: version.tags });
    }

    return version;
  }

  /**
   * 태그로 검색
   */
  searchByTag(tag: string): Version[] {
    const results: Version[] = [];
    
    for (const versions of this.versions.values()) {
      for (const version of versions) {
        if (version.tags.includes(tag)) {
          results.push(version);
        }
      }
    }

    return results;
  }

  /**
   * 버전 히스토리 요약
   */
  getVersionHistory(projectId: string): {
    totalVersions: number;
    firstVersion: Date | null;
    lastVersion: Date | null;
    totalChanges: number;
    versions: Array<{
      id: string;
      version: number;
      name: string;
      createdAt: Date;
      status: string;
      changesCount: number;
    }>;
  } {
    const versions = this.getVersions(projectId);

    return {
      totalVersions: versions.length,
      firstVersion: versions[0]?.createdAt || null,
      lastVersion: versions[versions.length - 1]?.createdAt || null,
      totalChanges: versions.reduce((sum, v) => sum + (v.changes?.length || 0), 0),
      versions: versions.map(v => ({
        id: v.id,
        version: v.version,
        name: v.name,
        createdAt: v.createdAt,
        status: v.status,
        changesCount: v.changes?.length || 0
      }))
    };
  }

  /**
   * LocalStorage에 저장
   */
  private saveToStorage(): void {
    try {
      const data = {
        versions: Array.from(this.versions.entries()),
        currentVersion: Array.from(this.currentVersion.entries())
      };
      localStorage.setItem(this.storageKey, JSON.stringify(data));
    } catch (error) {
      console.error('Failed to save versions to storage:', error);
    }
  }

  /**
   * LocalStorage에서 불러오기
   */
  private loadFromStorage(): void {
    try {
      const data = localStorage.getItem(this.storageKey);
      if (data) {
        const parsed = JSON.parse(data);
        this.versions = new Map(parsed.versions);
        this.currentVersion = new Map(parsed.currentVersion);

        // Date 객체 복원
        for (const versions of this.versions.values()) {
          for (const version of versions) {
            version.createdAt = new Date(version.createdAt);
            if (version.changes) {
              version.changes.forEach(change => {
                change.timestamp = new Date(change.timestamp);
              });
            }
          }
        }
      }
    } catch (error) {
      console.error('Failed to load versions from storage:', error);
    }
  }

  /**
   * 전체 통계
   */
  getGlobalStats(): {
    totalProjects: number;
    totalVersions: number;
    totalChanges: number;
    avgVersionsPerProject: number;
    mostActiveProject: string | null;
  } {
    const totalProjects = this.versions.size;
    let totalVersions = 0;
    let totalChanges = 0;
    let mostActiveProject: string | null = null;
    let maxVersions = 0;

    for (const [projectId, versions] of this.versions.entries()) {
      totalVersions += versions.length;
      totalChanges += versions.reduce((sum, v) => sum + (v.changes?.length || 0), 0);

      if (versions.length > maxVersions) {
        maxVersions = versions.length;
        mostActiveProject = projectId;
      }
    }

    return {
      totalProjects,
      totalVersions,
      totalChanges,
      avgVersionsPerProject: totalProjects > 0 ? totalVersions / totalProjects : 0,
      mostActiveProject
    };
  }
}

// 싱글톤 인스턴스
export const versionControl = new VersionControl();

export default VersionControl;
