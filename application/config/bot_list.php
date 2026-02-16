<?php
/**
 * 봇 목록 설정
 * 검색엔진 봇 및 AI 크롤러 정의
 */

// 주요 검색엔진/서비스 크롤러 봇 (50개)
$searchBots = [
    // 글로벌 검색엔진
    ['name' => 'Googlebot', 'user_agent' => 'Googlebot', 'description' => '구글 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Googlebot-News', 'user_agent' => 'Googlebot-News', 'description' => '구글 뉴스봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Googlebot-Image', 'user_agent' => 'Googlebot-Image', 'description' => '구글 이미지봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Googlebot-Video', 'user_agent' => 'Googlebot-Video', 'description' => '구글 비디오봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Googlebot-Mobile', 'user_agent' => 'Googlebot-Mobile', 'description' => '구글 모바일봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'AdsBot-Google', 'user_agent' => 'AdsBot-Google', 'description' => '구글 광고봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Mediapartners-Google', 'user_agent' => 'Mediapartners-Google', 'description' => '구글 애드센스', 'category' => '글로벌 검색엔진'],
    ['name' => 'Bingbot', 'user_agent' => 'Bingbot', 'description' => '빙 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'MSNBot', 'user_agent' => 'msnbot', 'description' => 'MSN 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'AdIdxBot', 'user_agent' => 'AdIdxBot', 'description' => '빙 광고봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Slurp', 'user_agent' => 'Slurp', 'description' => '야후 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'DuckDuckBot', 'user_agent' => 'DuckDuckBot', 'description' => '덕덕고 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Baiduspider', 'user_agent' => 'Baiduspider', 'description' => '바이두 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Baiduspider-image', 'user_agent' => 'Baiduspider-image', 'description' => '바이두 이미지봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Baiduspider-video', 'user_agent' => 'Baiduspider-video', 'description' => '바이두 비디오봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'YandexBot', 'user_agent' => 'YandexBot', 'description' => '얀덱스 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'YandexImages', 'user_agent' => 'YandexImages', 'description' => '얀덱스 이미지봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'YandexVideo', 'user_agent' => 'YandexVideo', 'description' => '얀덱스 비디오봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'YandexMobileBot', 'user_agent' => 'YandexMobileBot', 'description' => '얀덱스 모바일봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Sogou Spider', 'user_agent' => 'Sogou Spider', 'description' => '소고우 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Sogou web spider', 'user_agent' => 'Sogou web spider', 'description' => '소고우 웹봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Exabot', 'user_agent' => 'Exabot', 'description' => '엑사봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Exabot-Thumbnails', 'user_agent' => 'Exabot-Thumbnails', 'description' => '엑사봇 썸네일', 'category' => '글로벌 검색엔진'],
    ['name' => 'SeznamBot', 'user_agent' => 'SeznamBot', 'description' => '세즈남 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'Qwantify', 'user_agent' => 'Qwantify', 'description' => '콴트 검색봇', 'category' => '글로벌 검색엔진'],
    ['name' => 'MojeekBot', 'user_agent' => 'MojeekBot', 'description' => '모지크 검색봇', 'category' => '글로벌 검색엔진'],
    
    // 한국/아시아
    ['name' => 'NaverBot', 'user_agent' => 'NaverBot', 'description' => '네이버 검색봇', 'category' => '한국/아시아'],
    ['name' => 'Yeti', 'user_agent' => 'Yeti', 'description' => '네이버 공식 검색봇', 'category' => '한국/아시아'],
    ['name' => 'Daumoa', 'user_agent' => 'Daumoa', 'description' => '다음 검색봇', 'category' => '한국/아시아'],
    ['name' => 'ZumBot', 'user_agent' => 'ZumBot', 'description' => '줌 검색봇', 'category' => '한국/아시아'],
    ['name' => 'PetalBot', 'user_agent' => 'PetalBot', 'description' => '화웨이 검색봇', 'category' => '한국/아시아'],
    ['name' => 'Bytespider', 'user_agent' => 'Bytespider', 'description' => '바이트댄스 크롤러', 'category' => '한국/아시아'],
    
    // SNS / 링크 미리보기
    ['name' => 'FacebookExternalHit', 'user_agent' => 'facebookexternalhit', 'description' => '페이스북 크롤러', 'category' => 'SNS'],
    ['name' => 'Facebot', 'user_agent' => 'Facebot', 'description' => '페이스북봇', 'category' => 'SNS'],
    ['name' => 'Twitterbot', 'user_agent' => 'Twitterbot', 'description' => '트위터봇', 'category' => 'SNS'],
    ['name' => 'LinkedInBot', 'user_agent' => 'LinkedInBot', 'description' => '링크드인봇', 'category' => 'SNS'],
    ['name' => 'Pinterestbot', 'user_agent' => 'Pinterestbot', 'description' => '핀터레스트봇', 'category' => 'SNS'],
    ['name' => 'Discordbot', 'user_agent' => 'Discordbot', 'description' => '디스코드봇', 'category' => 'SNS'],
    ['name' => 'TelegramBot', 'user_agent' => 'TelegramBot', 'description' => '텔레그램봇', 'category' => 'SNS'],
    ['name' => 'Slackbot', 'user_agent' => 'Slackbot-LinkExpanding', 'description' => '슬랙봇', 'category' => 'SNS'],
    ['name' => 'WhatsApp', 'user_agent' => 'WhatsApp', 'description' => '왓츠앱', 'category' => 'SNS'],
    
    // 포털/리더/피드
    ['name' => 'FeedFetcher-Google', 'user_agent' => 'FeedFetcher-Google', 'description' => '구글 피드 수집', 'category' => '포털/피드'],
    ['name' => 'FeedlyBot', 'user_agent' => 'FeedlyBot', 'description' => '피들리봇', 'category' => '포털/피드'],
    ['name' => 'Applebot', 'user_agent' => 'Applebot', 'description' => '애플 Siri 검색', 'category' => '포털/피드'],
    ['name' => 'Embedly', 'user_agent' => 'Embedly', 'description' => '임베들리', 'category' => '포털/피드'],
    ['name' => 'FlipboardProxy', 'user_agent' => 'FlipboardProxy', 'description' => '플립보드 프록시', 'category' => '포털/피드'],
    ['name' => 'FlipboardBot', 'user_agent' => 'FlipboardBot', 'description' => '플립보드봇', 'category' => '포털/피드'],
    
    // SEO/분석 서비스
    ['name' => 'AhrefsBot', 'user_agent' => 'AhrefsBot', 'description' => 'Ahrefs SEO 봇', 'category' => 'SEO/분석'],
    ['name' => 'SemrushBot', 'user_agent' => 'SemrushBot', 'description' => 'Semrush SEO 봇', 'category' => 'SEO/분석'],
    ['name' => 'MJ12bot', 'user_agent' => 'MJ12bot', 'description' => 'Majestic SEO 봇', 'category' => 'SEO/분석']
];

// AI/LLM 데이터 수집 봇 (50개)
$aiBots = [
    // OpenAI 계열
    ['name' => 'GPTBot', 'user_agent' => 'GPTBot', 'description' => 'OpenAI GPT 크롤러', 'category' => 'OpenAI'],
    ['name' => 'ChatGPT-User', 'user_agent' => 'ChatGPT-User', 'description' => 'ChatGPT 사용자 에이전트', 'category' => 'OpenAI'],
    ['name' => 'OAI-SearchBot', 'user_agent' => 'OAI-SearchBot', 'description' => 'OpenAI 검색봇', 'category' => 'OpenAI'],
    
    // Google AI
    ['name' => 'Google-Extended', 'user_agent' => 'Google-Extended', 'description' => 'Gemini 학습용 크롤러', 'category' => 'Google AI'],
    
    // Anthropic
    ['name' => 'anthropic-ai', 'user_agent' => 'anthropic-ai', 'description' => 'Anthropic AI 크롤러', 'category' => 'Anthropic'],
    ['name' => 'ClaudeBot', 'user_agent' => 'ClaudeBot', 'description' => 'Claude 크롤러', 'category' => 'Anthropic'],
    ['name' => 'Claude-Web', 'user_agent' => 'Claude-Web', 'description' => 'Claude 웹 크롤러', 'category' => 'Anthropic'],
    
    // Meta AI
    ['name' => 'Meta-ExternalAgent', 'user_agent' => 'Meta-ExternalAgent', 'description' => 'Meta AI 에이전트', 'category' => 'Meta AI'],
    ['name' => 'Meta-ExternalFetcher', 'user_agent' => 'Meta-ExternalFetcher', 'description' => 'Meta AI 페처', 'category' => 'Meta AI'],
    ['name' => 'FacebookBot', 'user_agent' => 'FacebookBot', 'description' => 'Meta AI 크롤러', 'category' => 'Meta AI'],
    
    // Perplexity / AI Search
    ['name' => 'PerplexityBot', 'user_agent' => 'PerplexityBot', 'description' => 'Perplexity AI 봇', 'category' => 'AI 검색'],
    ['name' => 'Perplexity-User', 'user_agent' => 'Perplexity-User', 'description' => 'Perplexity 사용자', 'category' => 'AI 검색'],
    
    // Amazon
    ['name' => 'Amazonbot', 'user_agent' => 'Amazonbot', 'description' => '아마존 크롤러', 'category' => 'Amazon'],
    
    // Apple AI 확장
    ['name' => 'Applebot-Extended', 'user_agent' => 'Applebot-Extended', 'description' => '애플 AI 크롤러', 'category' => 'Apple AI'],
    
    // Common Crawl
    ['name' => 'CCBot', 'user_agent' => 'CCBot', 'description' => 'Common Crawl', 'category' => 'Common Crawl'],
    
    // AI 데이터 수집 기업
    ['name' => 'Diffbot', 'user_agent' => 'Diffbot', 'description' => 'Diffbot AI', 'category' => 'AI 데이터 수집'],
    ['name' => 'ImagesiftBot', 'user_agent' => 'ImagesiftBot', 'description' => 'Imagesift AI', 'category' => 'AI 데이터 수집'],
    ['name' => 'DataForSeoBot', 'user_agent' => 'DataForSeoBot', 'description' => 'DataForSEO 봇', 'category' => 'AI 데이터 수집'],
    ['name' => 'SeekportBot', 'user_agent' => 'SeekportBot', 'description' => 'Seekport 봇', 'category' => 'AI 데이터 수집'],
    
    // AI Research
    ['name' => 'AI2Bot', 'user_agent' => 'AI2Bot', 'description' => 'Allen AI 봇', 'category' => 'AI Research'],
    ['name' => 'SemanticScholarBot', 'user_agent' => 'SemanticScholarBot', 'description' => 'Semantic Scholar', 'category' => 'AI Research'],
    
    // Cohere
    ['name' => 'cohere-ai', 'user_agent' => 'cohere-ai', 'description' => 'Cohere AI', 'category' => 'Cohere'],
    
    // You.com
    ['name' => 'YouBot', 'user_agent' => 'YouBot', 'description' => 'You.com AI', 'category' => 'AI 검색'],
    
    // 기타 AI 에이전트
    ['name' => 'TurnitinBot', 'user_agent' => 'TurnitinBot', 'description' => 'Turnitin 봇', 'category' => '기타 AI'],
    ['name' => 'ZoominfoBot', 'user_agent' => 'ZoominfoBot', 'description' => 'Zoominfo 봇', 'category' => '기타 AI'],
    ['name' => 'Neevabot', 'user_agent' => 'Neevabot', 'description' => 'Neeva AI 봇', 'category' => '기타 AI'],
    ['name' => 'KagiBot', 'user_agent' => 'KagiBot', 'description' => 'Kagi Search AI', 'category' => '기타 AI'],
    ['name' => 'Omgilibot', 'user_agent' => 'Omgilibot', 'description' => 'Omgili 크롤러', 'category' => '기타 AI'],
    
    // Scraping Framework UA
    ['name' => 'Scrapy', 'user_agent' => 'Scrapy', 'description' => 'Scrapy 프레임워크', 'category' => '스크래핑 도구'],
    ['name' => 'Python-requests', 'user_agent' => 'Python-requests', 'description' => 'Python Requests', 'category' => '스크래핑 도구'],
    ['name' => 'Go-http-client', 'user_agent' => 'Go-http-client', 'description' => 'Go HTTP 클라이언트', 'category' => '스크래핑 도구'],
    ['name' => 'axios', 'user_agent' => 'axios', 'description' => 'Axios JS 라이브러리', 'category' => '스크래핑 도구'],
    ['name' => 'node-fetch', 'user_agent' => 'node-fetch', 'description' => 'Node Fetch', 'category' => '스크래핑 도구'],
    ['name' => 'okhttp', 'user_agent' => 'okhttp', 'description' => 'OkHttp 클라이언트', 'category' => '스크래핑 도구'],
    
    // Headless Browser 기반
    ['name' => 'HeadlessChrome', 'user_agent' => 'HeadlessChrome', 'description' => 'Headless Chrome', 'category' => 'Headless Browser'],
    ['name' => 'Puppeteer', 'user_agent' => 'Puppeteer', 'description' => 'Puppeteer 봇', 'category' => 'Headless Browser'],
    ['name' => 'Playwright', 'user_agent' => 'Playwright', 'description' => 'Playwright 봇', 'category' => 'Headless Browser'],
    ['name' => 'SeleniumBot', 'user_agent' => 'SeleniumBot', 'description' => 'Selenium 봇', 'category' => 'Headless Browser'],
    
    // SEO+AI 혼합
    ['name' => 'DotBot', 'user_agent' => 'DotBot', 'description' => 'Moz 크롤러', 'category' => 'SEO+AI'],
    
    // 기타 알려진 수집봇
    ['name' => 'BLEXBot', 'user_agent' => 'BLEXBot', 'description' => 'BLEXBot 크롤러', 'category' => '기타 수집봇'],
    ['name' => 'SurveyBot', 'user_agent' => 'SurveyBot', 'description' => 'Survey 봇', 'category' => '기타 수집봇'],
    ['name' => 'LinkpadBot', 'user_agent' => 'LinkpadBot', 'description' => 'Linkpad 봇', 'category' => '기타 수집봇'],
    ['name' => 'SerpstatBot', 'user_agent' => 'SerpstatBot', 'description' => 'Serpstat 봇', 'category' => '기타 수집봇'],
    ['name' => 'SiteAuditBot', 'user_agent' => 'SiteAuditBot', 'description' => 'Site Audit 봇', 'category' => '기타 수집봇'],
    ['name' => 'MegaIndex.ru', 'user_agent' => 'MegaIndex.ru', 'description' => 'MegaIndex 크롤러', 'category' => '기타 수집봇'],
    ['name' => 'Seekport Crawler', 'user_agent' => 'Seekport Crawler', 'description' => 'Seekport 크롤러', 'category' => '기타 수집봇'],
    ['name' => 'ZoomBot', 'user_agent' => 'ZoomBot', 'description' => 'Zoom 봇', 'category' => '기타 수집봇'],
    ['name' => 'WebCopier', 'user_agent' => 'WebCopier', 'description' => 'WebCopier 크롤러', 'category' => '기타 수집봇'],
    ['name' => 'HTTrack', 'user_agent' => 'HTTrack', 'description' => 'HTTrack Website Copier', 'category' => '기타 수집봇'],
    ['name' => 'Wget', 'user_agent' => 'Wget', 'description' => 'GNU Wget', 'category' => '기타 수집봇']
];

return [
    'searchBots' => $searchBots,
    'aiBots' => $aiBots
];
