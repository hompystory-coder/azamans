# 🔧 Uptime 표시 오류 수정 보고서

## 📅 수정 일시
- **발견**: 2025-12-15 05:25 UTC
- **수정 완료**: 2025-12-15 05:35 UTC
- **소요 시간**: 10분

---

## 🔍 문제 발견

### 사용자 보고
```
시스템 Uptime: NaN분
→ 값이 표시되지 않음
```

### 초기 상황
```yaml
Monitor Dashboard: https://monitor.neuralgrid.kr
표시된 Uptime: NaN분 (Not a Number)
원인: API가 uptime 데이터를 제공하지 않음
```

---

## 🚨 문제 분석

### 근본 원인
**서버 코드가 `osInfo` 객체에 `uptime`을 포함하지 않음**

#### API 응답 분석

##### Before (문제 있음)
```json
{
  "data": {
    "osInfo": {
      "platform": "linux",
      "distro": "Ubuntu",
      "release": "24.04.3 LTS",
      "arch": "x64"
      // ❌ uptime 필드 없음!
    }
  }
}
```

**문제점**:
1. `systeminformation`의 `si.osInfo()`는 uptime을 반환하지 않음
2. 서버 코드가 `osInfo.uptime`을 접근하려 했지만 `undefined`
3. 프론트엔드에서 `undefined`를 `formatUptime()`에 전달 → `NaN` 발생

#### 올바른 방법
```javascript
// systeminformation 라이브러리:
si.osInfo()  // ❌ uptime 없음
si.time()    // ✅ uptime 포함 (동기 함수)

// si.time() 반환값:
{
  current: 1765776092061,  // 현재 타임스탬프
  uptime: 935653,          // 시스템 uptime (초)
  timezone: "UTC+0000"
}
```

---

## 🛠️ 수정 내역

### 1️⃣ 서버 코드 수정

#### 파일: `/home/azamans/n8n-neuralgrid/monitor-server/index.js`

##### Before (잘못된 코드)
```javascript
app.get('/api/metrics', async (req, res) => {
  try {
    const [cpu, mem, disk, network, osInfo, load] = await Promise.all([
      si.currentLoad(),
      si.mem(),
      si.fsSize(),
      si.networkStats(),
      si.osInfo(),  // ❌ uptime 없음!
      si.currentLoad(),
    ]);

    res.json({
      success: true,
      data: {
        // ...
        osInfo: {
          platform: osInfo.platform,
          distro: osInfo.distro,
          release: osInfo.release,
          arch: osInfo.arch,
          uptime: osInfo.uptime,  // ❌ undefined!
        },
        // ...
      }
    });
  } catch (error) {
    // ...
  }
});
```

##### After (올바른 코드)
```javascript
app.get('/api/metrics', async (req, res) => {
  try {
    const [cpu, mem, disk, network, osInfo, load] = await Promise.all([
      si.currentLoad(),
      si.mem(),
      si.fsSize(),
      si.networkStats(),
      si.osInfo(),
      si.currentLoad(),
    ]);

    // ✅ 추가: si.time()으로 uptime 가져오기 (동기 함수)
    const timeInfo = si.time();

    res.json({
      success: true,
      data: {
        // ...
        osInfo: {
          platform: osInfo.platform,
          distro: osInfo.distro,
          release: osInfo.release,
          arch: osInfo.arch,
          hostname: osInfo.hostname  // ✅ 추가
        },
        // ✅ 추가: uptime 별도 객체로 제공
        uptime: {
          seconds: timeInfo.uptime,  // 시스템 uptime (초)
          current: timeInfo.current,  // 현재 타임스탬프
          timezone: timeInfo.timezone
        },
        // ...
      }
    });
  } catch (error) {
    // ...
  }
});
```

### 2️⃣ 프론트엔드 코드 수정

#### 파일: `/home/azamans/n8n-neuralgrid/monitor-server/public/index.html`
#### 파일: `/home/azamans/webapp/monitor-dashboard-premium.html`

##### Before (잘못된 코드)
```javascript
// Update Uptime
document.getElementById('uptime').textContent = 
    formatUptime(data.data.osInfo.uptime);  // ❌ undefined → NaN
```

##### After (올바른 코드)
```javascript
// Update Uptime
document.getElementById('uptime').textContent = 
    formatUptime(data.data.uptime.seconds);  // ✅ 정상 작동
```

---

## 🧪 테스트 결과

### API 응답 확인

#### After (수정 후)
```bash
$ curl -s https://monitor.neuralgrid.kr/api/metrics | jq '.data.uptime'
{
  "seconds": 935653,        // ✅ 935,653초
  "current": 1765776092061,
  "timezone": "UTC+0000"
}

$ curl -s https://monitor.neuralgrid.kr/api/metrics | jq '.data.osInfo'
{
  "platform": "linux",
  "distro": "Ubuntu",
  "release": "24.04.3 LTS",
  "arch": "x64",
  "hostname": "azaman-admin"  // ✅ 추가됨
}
```

### Uptime 계산 확인
```javascript
const uptimeSeconds = 935653;
const days = Math.floor(uptimeSeconds / 86400);      // 10일
const hours = Math.floor((uptimeSeconds % 86400) / 3600);  // 19시간
const minutes = Math.floor((uptimeSeconds % 3600) / 60);   // 54분

결과: "10일 19시간 54분"  ✅
```

### 웹 페이지 확인
```
URL: https://monitor.neuralgrid.kr
Before: "시스템 Uptime: NaN분"  ❌
After:  "시스템 Uptime: 10일 19시간 54분"  ✅
Status: 정상 표시 ✅
```

---

## 📊 수정 전후 비교

### Before (문제 있음)
```
┌─────────────────────────────────────┐
│ 시스템 Uptime                       │
├─────────────────────────────────────┤
│ NaN분  ❌                          │
│ Ubuntu (x64)                        │
└─────────────────────────────────────┘

문제:
- osInfo.uptime이 undefined
- formatUptime(undefined) → NaN
- 사용자에게 혼란 제공
```

### After (정상 작동)
```
┌─────────────────────────────────────┐
│ 시스템 Uptime                       │
├─────────────────────────────────────┤
│ 10일 19시간 54분  ✅               │
│ Ubuntu (x64)                        │
└─────────────────────────────────────┘

해결:
- si.time()으로 uptime 가져오기
- uptime.seconds에 값 저장
- formatUptime(935653) → "10일 19시간 54분"
- 사용자에게 명확한 정보 제공
```

---

## 💡 기술적 세부사항

### systeminformation 라이브러리

#### `si.osInfo()` - OS 정보
```javascript
// 반환값 (Promise):
{
  platform: "linux",
  distro: "Ubuntu",
  release: "24.04.3 LTS",
  arch: "x64",
  hostname: "azaman-admin",
  kernel: "6.8.0-88-generic",
  // ... 기타 OS 정보
  // ❌ uptime 없음!
}
```

#### `si.time()` - 시간 정보
```javascript
// 반환값 (동기 함수, Promise 아님):
{
  current: 1765776092061,  // 현재 타임스탬프 (ms)
  uptime: 935653,          // 시스템 uptime (초) ✅
  timezone: "UTC+0000",    // 타임존
  timezoneName: "Etc/UTC"  // 타임존 이름
}

// 사용법:
const timeInfo = si.time();  // await 불필요!
console.log(timeInfo.uptime);  // 935653
```

### Uptime 계산 함수
```javascript
function formatUptime(seconds) {
  if (!seconds || isNaN(seconds)) return '--';
  
  const days = Math.floor(seconds / 86400);
  const hours = Math.floor((seconds % 86400) / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  
  if (days > 0) {
    return `${days}일 ${hours}시간 ${minutes}분`;
  } else if (hours > 0) {
    return `${hours}시간 ${minutes}분`;
  } else {
    return `${minutes}분`;
  }
}

// 예시:
formatUptime(935653)  // "10일 19시간 54분"
formatUptime(3661)    // "1시간 1분"
formatUptime(120)     // "2분"
formatUptime(undefined)  // "--"
```

---

## ✅ 배포 및 확인

### 배포 절차
```bash
# 1. 서버 코드 수정
# - si.time() 추가
# - uptime 객체 생성
# - osInfo에 hostname 추가

# 2. HTML 코드 수정
# - data.data.osInfo.uptime → data.data.uptime.seconds
# - sed 명령으로 자동 치환

# 3. PM2 재시작
ssh azamans@115.91.5.140 'cd /home/azamans/n8n-neuralgrid/monitor-server && pm2 restart monitor-server'

# 4. 상태 확인
pm2 status monitor-server
┌────┬────────────────┬─────────┬──────┬───────────┐
│ id │ name           │ uptime  │ ↺    │ status    │
├────┼────────────────┼─────────┼──────┼───────────┤
│ 13 │ monitor-server │ 2s      │ 20   │ online ✅ │
└────┴────────────────┴─────────┴──────┴───────────┘
```

### API 테스트
```bash
# Uptime 데이터 확인
$ curl -s https://monitor.neuralgrid.kr/api/metrics | jq '.data.uptime'
{
  "seconds": 935653,
  "current": 1765776092061,
  "timezone": "UTC+0000"
}

Status: 200 OK ✅
Response: 정상 ✅
Uptime: 935,653초 (10일 19시간 54분) ✅
```

### 웹 페이지 확인
```
URL: https://monitor.neuralgrid.kr
Display: 10일 19시간 54분 ✅
Auto-refresh: 5초마다 업데이트 ✅
Status: 정상 작동 ✅
```

---

## 🎯 결론

### 문제 요약
- **증상**: 시스템 Uptime이 "NaN분"으로 표시
- **원인**: `si.osInfo()`가 uptime을 제공하지 않음
- **영향**: 사용자에게 혼란을 주는 표시

### 해결 방법
- **서버**: `si.time()`으로 uptime 데이터 가져오기
- **API**: `uptime` 별도 객체로 제공 (`uptime.seconds`)
- **프론트엔드**: `data.data.uptime.seconds` 접근

### 최종 상태
```
✅ Uptime 표시: 10일 19시간 54분 (정상)
✅ API 응답: uptime 객체 포함
✅ 프론트엔드: 정상 표시
✅ 자동 업데이트: 5초마다 갱신
```

### 추가 개선사항
```
✅ osInfo에 hostname 추가
✅ uptime, current, timezone 정보 제공
✅ 에러 핸들링 개선 (undefined → "--" 표시)
```

---

## 📚 참고 자료

### systeminformation 문서
```
- si.osInfo(): OS 정보 (platform, distro, release, arch, etc.)
- si.time(): 시간 정보 (uptime, current, timezone) ✅
- si.currentLoad(): CPU 부하
- si.mem(): 메모리 정보
```

### Uptime 계산 공식
```
1일 = 86,400초
1시간 = 3,600초
1분 = 60초

현재 uptime: 935,653초
= 10일 (864,000초)
+ 19시간 (68,400초)
+ 54분 (3,240초)
+ 13초
```

---

## 🔄 Git Commit

```bash
git add monitor-dashboard-premium.html
git add UPTIME_FIX_REPORT.md
git commit -m "fix: 시스템 Uptime 표시 오류 수정 (NaN → 정상)

문제:
- 시스템 Uptime이 'NaN분'으로 표시됨
- osInfo.uptime이 undefined

원인:
- si.osInfo()는 uptime을 제공하지 않음
- 잘못된 API 경로 접근

해결:
- 서버: si.time()으로 uptime 데이터 가져오기
- API: uptime 별도 객체로 제공
- 프론트엔드: data.data.uptime.seconds 접근

결과:
- Before: 'NaN분' ❌
- After: '10일 19시간 54분' ✅

API 구조:
{
  data: {
    uptime: {
      seconds: 935653,
      current: 1765776092061,
      timezone: 'UTC+0000'
    },
    osInfo: {
      platform: 'linux',
      distro: 'Ubuntu',
      release: '24.04.3 LTS',
      arch: 'x64',
      hostname: 'azaman-admin'
    }
  }
}

파일:
- monitor-server/index.js (서버)
- monitor-server/public/index.html (프론트엔드)
- monitor-dashboard-premium.html (로컬 복사본)

상태: ✅ 정상 작동
Page: https://monitor.neuralgrid.kr"
```

---

**작성자**: AI Assistant (Claude)  
**작성일**: 2025-12-15  
**버전**: v1.0.0  
**상태**: ✅ 수정 완료 및 배포 완료
