# post_likes 테이블 BBS/News 구분 작업 완료

## 📅 작업 일시: 2026-02-16 01:10

---

## 🎯 작업 목적

BBS와 News가 같은 `post_likes` 테이블을 공유하면서 `post_uid`만으로 구분할 경우, BBS의 게시물과 News의 게시물이 같은 uid를 가질 때 좋아요가 섞이는 문제 발생.

**예시 문제 상황:**
- BBS 게시물 uid=1 에 좋아요 5개
- News 게시물 uid=1 에 좋아요 3개
- 구분 필드 없이는 두 게시물의 좋아요가 합쳐져서 표시됨

---

## ✅ 해결 방법

### 1. 데이터베이스 스키마 변경

**파일:** `database/alter_post_likes_add_post_type.sql`

```sql
-- post_type 컬럼 추가
ALTER TABLE `post_likes` 
ADD COLUMN IF NOT EXISTS `post_type` VARCHAR(10) NOT NULL DEFAULT 'bbs' 
COMMENT '게시물 타입 (bbs, news)' AFTER `post_uid`;

-- UNIQUE KEY 변경 (post_type 포함)
ALTER TABLE `post_likes` 
ADD UNIQUE KEY IF NOT EXISTS `unique_type_post_member` (`post_type`, `post_uid`, `member_uid`);

-- 인덱스 추가
ALTER TABLE `post_likes` 
ADD INDEX IF NOT EXISTS `idx_type_post` (`post_type`, `post_uid`);
```

### 2. 애플리케이션 코드 수정

#### News 컨트롤러 (`application/controller/news.php`)

**위치 1: 라인 180 - 좋아요 상태 확인 (viewPost)**
```php
// Before
SELECT uid FROM post_likes WHERE post_uid = ? AND member_uid = ?

// After  
SELECT uid FROM post_likes WHERE post_type = 'news' AND post_uid = ? AND member_uid = ?
```

**위치 2: 라인 778 - 기존 좋아요 확인 (toggleLike)**
```php
// Before
SELECT * FROM post_likes WHERE post_uid = ? AND member_uid = ?

// After
SELECT * FROM post_likes WHERE post_type = 'news' AND post_uid = ? AND member_uid = ?
```

**위치 3: 라인 796 - 좋아요 추가 (toggleLike)**
```php
// Before
getDbInsert('post_likes', [
    'post_uid' => $postUid,
    'member_uid' => $memberUid
]);

// After
getDbInsert('post_likes', [
    'post_type' => 'news',
    'post_uid' => $postUid,
    'member_uid' => $memberUid
]);
```

#### BBS 컨트롤러 (`application/controller/bbs.php`)

동일한 3개 위치에 `post_type = 'bbs'` 조건 추가

#### News 모델 (`application/models/NewsModel.php`)

**위치: 라인 139 - 좋아요 수 조회 (getPost)**
```php
// Before
SELECT COUNT(*) as cnt FROM post_likes WHERE post_uid = ?

// After
SELECT COUNT(*) as cnt FROM post_likes WHERE post_type = 'news' AND post_uid = ?
```

---

## 📊 변경 요약

| 컴포넌트 | 파일 | 수정 위치 | 변경 내용 |
|---------|------|----------|----------|
| **SQL** | `database/alter_post_likes_add_post_type.sql` | 신규 생성 | post_type 필드 추가, UNIQUE KEY 변경 |
| **News Controller** | `application/controller/news.php` | 3곳 (180, 778, 796) | `post_type = 'news'` 조건 추가 |
| **BBS Controller** | `application/controller/bbs.php` | 3곳 (178, 726, 744) | `post_type = 'bbs'` 조건 추가 |
| **News Model** | `application/models/NewsModel.php` | 1곳 (139) | `post_type = 'news'` 조건 추가 |
| **총 변경** | - | **7개 위치** | - |

---

## 🗄️ post_likes 테이블 구조 (변경 후)

```sql
CREATE TABLE `post_likes` (
  `uid` INT(11) NOT NULL AUTO_INCREMENT,
  `post_uid` INT(11) NOT NULL,
  `post_type` VARCHAR(10) NOT NULL DEFAULT 'bbs' COMMENT '게시물 타입 (bbs, news)',
  `member_uid` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `unique_type_post_member` (`post_type`, `post_uid`, `member_uid`),
  KEY `idx_type_post` (`post_type`, `post_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 📝 SQL 실행 방법

```bash
mysql -u [사용자명] -p [데이터베이스명] < /home/mvc/database/alter_post_likes_add_post_type.sql
```

---

## ✅ 검증 체크리스트

### 코드 검증
- [x] News 컨트롤러에 `post_type = 'news'` 추가 (3곳)
- [x] BBS 컨트롤러에 `post_type = 'bbs'` 추가 (3곳)
- [x] NewsModel에 `post_type = 'news'` 추가 (1곳)

### 기능 테스트 (SQL 실행 후)
- [ ] BBS 게시물 좋아요 추가/취소 정상 작동
- [ ] News 게시물 좋아요 추가/취소 정상 작동
- [ ] 같은 uid를 가진 BBS/News 게시물이 독립적으로 좋아요 관리
- [ ] 좋아요 카운트가 정확하게 표시됨

---

## 🎯 기대 효과

1. **데이터 무결성**: BBS와 News의 좋아요 데이터가 완전히 분리
2. **정확한 통계**: 각 시스템의 좋아요 수가 정확하게 집계
3. **확장성**: 향후 다른 게시물 타입(예: gallery, qna 등) 추가 가능
4. **성능**: UNIQUE KEY와 INDEX로 쿼리 성능 최적화

---

## 📂 관련 파일 목록

### 생성된 파일 (1개)
- `database/alter_post_likes_add_post_type.sql` (SQL 스크립트)

### 수정된 파일 (3개)
- `application/controller/news.php` (News 컨트롤러)
- `application/controller/bbs.php` (BBS 컨트롤러)  
- `application/models/NewsModel.php` (News 모델)

---

## 🚀 다음 단계

1. **SQL 스크립트 실행**
   ```bash
   mysql -u [user] -p [database] < database/alter_post_likes_add_post_type.sql
   ```

2. **기능 테스트**
   - BBS 게시물 좋아요 테스트
   - News 게시물 좋아요 테스트
   - 동일 uid 케이스 테스트

3. **데이터 검증**
   ```sql
   -- BBS 좋아요 확인
   SELECT * FROM post_likes WHERE post_type = 'bbs' ORDER BY created_at DESC LIMIT 10;
   
   -- News 좋아요 확인
   SELECT * FROM post_likes WHERE post_type = 'news' ORDER BY created_at DESC LIMIT 10;
   
   -- 좋아요 통계
   SELECT post_type, COUNT(*) as like_count 
   FROM post_likes 
   GROUP BY post_type;
   ```

---

## 💡 참고사항

### 기존 데이터 처리
- 기본값이 `'bbs'`로 설정되어 있어 기존 데이터는 모두 BBS로 분류됨
- 필요시 News 관련 기존 데이터는 수동으로 업데이트 필요:
  ```sql
  UPDATE post_likes pl
  INNER JOIN news_data nd ON pl.post_uid = nd.uid
  SET pl.post_type = 'news';
  ```

### 성능 고려사항
- `(post_type, post_uid)` 인덱스로 조회 성능 최적화
- UNIQUE KEY로 중복 좋아요 방지

---

**작업 완료 일시:** 2026-02-16 01:10  
**작업자:** AI Assistant  
**검증 상태:** 코드 수정 완료, SQL 실행 대기 중
