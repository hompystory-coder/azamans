-- rb_news_index 테이블 최적화용 복합 인덱스
-- 쿼리 패턴: WHERE site=X AND auth=1 AND embargo_date<=YYYYMMDDHHSS AND category...

USE news;

-- 1. 기본 조회용 복합 인덱스 (site, auth, embargo_date, d_regis)
ALTER TABLE rb_news_index 
ADD INDEX idx_site_auth_embargo_regis (site, auth, embargo_date, d_regis);

-- 2. 카테고리1 조회용
ALTER TABLE rb_news_index 
ADD INDEX idx_site_auth_cat1 (site, auth, category1, d_regis);

-- 3. 카테고리2 조회용
ALTER TABLE rb_news_index 
ADD INDEX idx_site_auth_cat2 (site, auth, category2, d_regis);

-- 4. Share 조회용
ALTER TABLE rb_news_index 
ADD INDEX idx_share_auth_embargo (share, auth, embargo_date, sharecat, d_regis);

-- rb_news_index_old 테이블도 동일하게
ALTER TABLE rb_news_index_old 
ADD INDEX idx_site_auth_embargo_regis (site, auth, embargo_date, d_regis);

ALTER TABLE rb_news_index_old 
ADD INDEX idx_site_auth_cat1 (site, auth, category1, d_regis);

ALTER TABLE rb_news_index_old 
ADD INDEX idx_site_auth_cat2 (site, auth, category2, d_regis);

ALTER TABLE rb_news_index_old 
ADD INDEX idx_share_auth_embargo (share, auth, embargo_date, sharecat, d_regis);

