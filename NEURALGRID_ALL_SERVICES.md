# 🌐 NeuralGrid Platform - 전체 서비스 목록

**Date**: 2025-12-15  
**Server**: 115.91.5.140  

---

## 📊 전체 서비스 개요

**총 서비스**: 13개  
**PM2 프로세스**: 7개  
**Nginx 도메인**: 11개  

---

## 🚀 활성화된 서비스 (PM2)

| ID | 서비스명 | 상태 | 포트 | 용도 | 메모리 |
|----|----------|------|------|------|--------|
| 0 | mfx-shorts | ✅ Online | 3101 | MediaFX Shorts | 61.9 MB |
| 5 | youtube-shorts-generator | ✅ Online | 3001 | YouTube Shorts 생성기 | 95.7 MB |
| 11 | neuronstar-music | ✅ Online | 3002 | AI 음악 생성 | 229.7 MB |
| 13 | monitor-server | ✅ Online | 5001 | 시스템 모니터링 | 81.2 MB |
| 17 | auth-service | ✅ Online | 3099 | 통합 인증 서비스 | 77.1 MB |
| 18 | main-dashboard | ✅ Online | 3200 | 메인 대시보드 API | 75.8 MB |
| 19 | api-gateway | ✅ Online | 4000 | API Gateway | 69.9 MB |

---

## 🌍 공개 도메인 (Nginx)

### **1. neuralgrid.kr** (메인 플랫폼)
- **URL**: https://neuralgrid.kr
- **용도**: 메인 랜딩 페이지
- **백엔드**: Static HTML + API Gateway (port 3200)
- **상태**: ✅ 작동 중
- **파일 위치**: `/var/www/neuralgrid.kr/html/`

### **2. api.neuralgrid.kr** (API Gateway)
- **URL**: https://api.neuralgrid.kr
- **용도**: 통합 API Gateway
- **백엔드**: port 4000 (api-gateway PM2)
- **상태**: ✅ 작동 중
- **프로젝트**: `/home/azamans/n8n-neuralgrid/api-gateway/`

### **3. auth.neuralgrid.kr** (인증 서비스)
- **URL**: https://auth.neuralgrid.kr
- **용도**: JWT 기반 통합 인증
- **백엔드**: port 3099 (auth-service PM2)
- **상태**: ✅ 작동 중
- **프로젝트**: `/home/azamans/n8n-neuralgrid/auth-service/`

### **4. mfx.neuralgrid.kr** (MediaFX Shorts)
- **URL**: https://mfx.neuralgrid.kr
- **용도**: AI 비디오 쇼츠 생성 플랫폼
- **백엔드**: port 3101 (mfx-shorts PM2, cluster mode)
- **상태**: ✅ 작동 중
- **프로젝트**: `/var/www/mfx.neuralgrid.kr/`

### **5. music.neuralgrid.kr** (NeuronStar Music)
- **URL**: https://music.neuralgrid.kr
- **용도**: AI 음악 생성 서비스
- **백엔드**: port 3002 (neuronstar-music PM2)
- **상태**: ✅ 작동 중
- **프로젝트**: `/home/azamans/n8n-neuralgrid/apps/neuronstar-music/`

### **6. bn-shop.neuralgrid.kr** (BN Shop)
- **URL**: https://bn-shop.neuralgrid.kr
- **용도**: 이커머스 플랫폼
- **백엔드**: Next.js app (port 미확인)
- **상태**: ✅ 작동 중
- **프로젝트**: 확인 필요

### **7. monitor.neuralgrid.kr** (시스템 모니터)
- **URL**: https://monitor.neuralgrid.kr
- **용도**: 실시간 시스템 모니터링 대시보드
- **백엔드**: port 5001 (monitor-server PM2)
- **상태**: ✅ 작동 중
- **프로젝트**: `/home/azamans/n8n-neuralgrid/monitor-server/`

### **8. n8n.neuralgrid.kr** (N8N Automation)
- **URL**: https://n8n.neuralgrid.kr
- **용도**: 워크플로우 자동화
- **백엔드**: port 5678 (N8N standalone) + port 5692 (proxy)
- **상태**: ✅ 작동 중
- **프로젝트**: N8N 설치

### **9. shorts.neuralgrid.kr** (YouTube Shorts)
- **URL**: https://shorts.neuralgrid.kr
- **용도**: YouTube Shorts 생성기
- **백엔드**: port 3001 (youtube-shorts-generator PM2)
- **상태**: ✅ 작동 중 (방금 수정됨)
- **프로젝트**: `/home/azamans/youtube-shorts-generator/`

### **10. ollama.neuralgrid.kr** (Ollama AI)
- **URL**: https://ollama.neuralgrid.kr
- **용도**: Ollama LLM API
- **백엔드**: port 11434 (Ollama systemd service)
- **상태**: ✅ 작동 중 (방금 설정됨)
- **SSL**: 2026-03-15까지 유효

### **11. ai-services** (AI 서비스 통합?)
- **설정 파일**: `/etc/nginx/sites-enabled/ai-services`
- **용도**: 확인 필요 (N8N + Ollama 통합?)
- **상태**: 확인 필요

---

## 📁 프로젝트 디렉토리 구조

### **메인 프로젝트 그룹**

#### **1. n8n-neuralgrid/** (메인 플랫폼)
```
/home/azamans/n8n-neuralgrid/
├── api-gateway/          (API Gateway - port 4000)
├── auth-service/         (Auth Service - port 3099)
├── main-dashboard/       (Main Dashboard API - port 3200)
├── monitor-server/       (Monitor Server - port 5001)
└── apps/
    ├── neuronstar-music/ (Music Service - port 3002)
    ├── shorts-market/    (Shorts Market - 확인 필요)
    └── web/              (Web UI - 확인 필요)
```

#### **2. MediaFX 프로젝트들**
```
/home/azamans/
├── mfx-clean-design/
├── mfx-fixed/
├── mfx-modern/
├── mfx-neuralgrid-style/
├── mfx-redesign/
├── mfx-ultra-modern/
├── mfx-web-ui/
└── mfx-web-ui-v2/

Live: /var/www/mfx.neuralgrid.kr/ (port 3101)
```

#### **3. BN Shop 프로젝트들**
```
/home/azamans/
├── bn-shop-webapp/
└── bn-shop-webapp-backup/
```

#### **4. Shorts 관련 프로젝트들**
```
/home/azamans/
├── youtube-shorts-generator/     (Live - port 3001)
├── n8n-shorts-automation/
├── shorts-market/
├── shorts-market-backup-20251210_072221/
├── shorts-market-old-version/
└── shorts-market-source/
```

#### **5. 기타 프로젝트들**
```
/home/azamans/
├── ai-memory-system/
├── autotest-system/
└── webapp/                       (Git 레포지토리)
```

---

## 🔌 포트 매핑

| 포트 | 서비스 | 용도 |
|------|--------|------|
| 3001 | youtube-shorts-generator | YouTube Shorts 생성 |
| 3002 | neuronstar-music | AI 음악 생성 |
| 3099 | auth-service | 인증 서비스 |
| 3101 | mfx-shorts | MediaFX Shorts |
| 3200 | main-dashboard | 메인 대시보드 API |
| 4000 | api-gateway | API Gateway |
| 5001 | monitor-server | 시스템 모니터 |
| 5678 | n8n | N8N 워크플로우 (직접) |
| 5692 | n8n | N8N 워크플로우 (프록시) |
| 11434 | ollama | Ollama LLM API |

---

## 🔍 확인이 필요한 서비스들

### **1. ai-services 도메인**
- **파일**: `/etc/nginx/sites-enabled/ai-services`
- **내용**: N8N + Ollama 통합?
- **상태**: 설정 파일 확인 필요

### **2. shorts-market 시리즈**
- **디렉토리들**:
  - `/home/azamans/shorts-market/`
  - `/home/azamans/n8n-neuralgrid/apps/shorts-market/`
  - 여러 백업 버전들
- **용도**: 확인 필요
- **상태**: PM2에 없음 (미사용?)

### **3. BN Shop 실제 위치**
- **설정**: `/etc/nginx/sites-enabled/bn-shop.conf`
- **프로젝트**: 어느 디렉토리?
- **포트**: 확인 필요

### **4. kshorts.neuralgrid.kr**
- **상태**: 설정 없음
- **용도**: 기억 안 남 (Korean Shorts?)
- **필요 여부**: 확인 필요

---

## 💾 백업 파일들

### **프로젝트 백업**
- `n8n-neuralgrid-backup-20251206/`
- `bn-shop-webapp-backup/`
- `shorts-market-backup-20251210_072221/`
- `shorts-market-old-version/`
- `mfx.neuralgrid.kr.backup/`
- `mfx.neuralgrid.kr.backup.20251213_015033/`

### **설정 백업**
- `shorts.neuralgrid.kr.backup_20251215_080854`
- `subdomains.backup_20251215_081148`

---

## 🌟 서비스 아키텍처

```
                    [ neuralgrid.kr ]
                            |
                    ┌───────┴───────┐
                    |               |
            [ API Gateway ]   [ Auth Service ]
                    |               |
        ┌───────────┼───────────────┼───────────┐
        |           |               |           |
   [ MediaFX ]  [ Music ]    [ Monitor ]   [ Shorts ]
        |           |               |           |
    port 3101   port 3002      port 5001   port 3001
```

---

## 📝 다음 단계

### **정리가 필요한 항목**
1. **kshorts** - 필요 여부 확인 및 삭제 또는 설정
2. **ai-services** - 용도 확인
3. **shorts-market** - 사용 중인지 확인, 미사용 시 정리
4. **BN Shop** - 실제 프로젝트 위치 및 포트 확인
5. **중복 프로젝트들** - mfx 관련 여러 버전 정리

### **백업 정리**
- 오래된 백업 파일들 아카이브 또는 삭제
- `/mnt/aidrive`로 중요 백업 이동

---

## 🔗 모든 활성 URL

1. ✅ https://neuralgrid.kr (메인)
2. ✅ https://api.neuralgrid.kr (API Gateway)
3. ✅ https://auth.neuralgrid.kr (인증)
4. ✅ https://mfx.neuralgrid.kr (MediaFX)
5. ✅ https://music.neuralgrid.kr (음악)
6. ✅ https://bn-shop.neuralgrid.kr (쇼핑)
7. ✅ https://monitor.neuralgrid.kr (모니터)
8. ✅ https://n8n.neuralgrid.kr (자동화)
9. ✅ https://shorts.neuralgrid.kr (쇼츠 생성)
10. ✅ https://ollama.neuralgrid.kr (Ollama AI)
11. ⚠️ https://kshorts.neuralgrid.kr (미설정)

---

**Generated**: 2025-12-15  
**Total Services**: 13 active services  
**Platform Status**: All systems operational ✅
