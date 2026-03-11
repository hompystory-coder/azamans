const express = require('express');
const cors = require('cors');
const cron = require('node-cron');
const axios = require('axios');
const Database = require('better-sqlite3');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 5000;

// 데이터베이스 초기화
const db = new Database(path.join(__dirname, 'youtube-trends.db'));

// 테이블 생성
db.exec(`
  CREATE TABLE IF NOT EXISTS videos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    video_id TEXT UNIQUE,
    keyword TEXT,
    channel_name TEXT,
    video_title TEXT,
    video_url TEXT,
    thumbnail_url TEXT,
    view_count INTEGER,
    subscriber_count INTEGER,
    description TEXT,
    published_at TEXT,
    searched_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS search_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    keyword TEXT,
    video_count INTEGER,
    status TEXT,
    searched_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key TEXT UNIQUE,
    value TEXT
  );
`);

// 설정 초기화
const insertSetting = db.prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)');
insertSetting.run('youtube_api_key', '');
insertSetting.run('auto_search_enabled', 'true');
insertSetting.run('search_time', '06:00');

// Middleware
app.use(cors());
app.use(express.json());

// YouTube API 검색 키워드
const SEARCH_KEYWORDS = [
  '시니어대상 한국 시니어 사연',
  '한국 시니어 대상 해외감동사연',
  '한국시니어대상 극복',
  '한국시니어 대상 북한',
  '한국 시니어 대상 디지털정보'
];

// YouTube API 검색 함수
async function searchYouTubeVideos(keyword, apiKey, maxResults = 200) {
  try {
    const publishedAfter = new Date();
    publishedAfter.setMonth(publishedAfter.getMonth() - 1); // 최근 1개월

    const searchUrl = 'https://www.googleapis.com/youtube/v3/search';
    const videoDetailsUrl = 'https://www.googleapis.com/youtube/v3/videos';
    const channelDetailsUrl = 'https://www.googleapis.com/youtube/v3/channels';

    let allVideos = [];
    let pageToken = null;

    // 페이지네이션으로 최대 200개 수집
    while (allVideos.length < maxResults) {
      const searchParams = {
        part: 'snippet',
        q: keyword,
        type: 'video',
        order: 'viewCount', // 조회수 순
        maxResults: Math.min(50, maxResults - allVideos.length),
        publishedAfter: publishedAfter.toISOString(),
        key: apiKey
      };

      if (pageToken) {
        searchParams.pageToken = pageToken;
      }

      const searchResponse = await axios.get(searchUrl, { params: searchParams });
      const searchResults = searchResponse.data.items;

      if (searchResults.length === 0) break;

      // 비디오 ID 추출
      const videoIds = searchResults.map(item => item.id.videoId).join(',');

      // 비디오 상세 정보 가져오기
      const videoDetailsResponse = await axios.get(videoDetailsUrl, {
        params: {
          part: 'snippet,statistics',
          id: videoIds,
          key: apiKey
        }
      });

      const videos = videoDetailsResponse.data.items;

      // 채널 ID 추출
      const channelIds = [...new Set(videos.map(v => v.snippet.channelId))].join(',');

      // 채널 정보 가져오기
      const channelDetailsResponse = await axios.get(channelDetailsUrl, {
        params: {
          part: 'statistics',
          id: channelIds,
          key: apiKey
        }
      });

      const channelMap = {};
      channelDetailsResponse.data.items.forEach(channel => {
        channelMap[channel.id] = channel.statistics.subscriberCount;
      });

      // 데이터 조합
      videos.forEach(video => {
        allVideos.push({
          videoId: video.id,
          keyword: keyword,
          channelName: video.snippet.channelTitle,
          videoTitle: video.snippet.title,
          videoUrl: `https://www.youtube.com/watch?v=${video.id}`,
          thumbnailUrl: video.snippet.thumbnails.high?.url || video.snippet.thumbnails.default.url,
          viewCount: parseInt(video.statistics.viewCount) || 0,
          subscriberCount: parseInt(channelMap[video.snippet.channelId]) || 0,
          description: video.snippet.description,
          publishedAt: video.snippet.publishedAt
        });
      });

      pageToken = searchResponse.data.nextPageToken;
      if (!pageToken) break;
    }

    // 조회수 순으로 정렬하고 상위 200개만 반환
    allVideos.sort((a, b) => b.viewCount - a.viewCount);
    return allVideos.slice(0, maxResults);

  } catch (error) {
    console.error(`Error searching for "${keyword}":`, error.message);
    throw error;
  }
}

// 비디오 저장 함수
function saveVideos(videos) {
  const insertVideo = db.prepare(`
    INSERT OR REPLACE INTO videos 
    (video_id, keyword, channel_name, video_title, video_url, thumbnail_url, 
     view_count, subscriber_count, description, published_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `);

  const insertMany = db.transaction((videoList) => {
    for (const video of videoList) {
      insertVideo.run(
        video.videoId,
        video.keyword,
        video.channelName,
        video.videoTitle,
        video.videoUrl,
        video.thumbnailUrl,
        video.viewCount,
        video.subscriberCount,
        video.description,
        video.publishedAt
      );
    }
  });

  insertMany(videos);
}

// 자동 검색 실행 함수
async function runAutoSearch() {
  console.log('Starting automatic YouTube search...');
  
  const getSetting = db.prepare('SELECT value FROM settings WHERE key = ?');
  const apiKey = getSetting.get('youtube_api_key')?.value;
  
  if (!apiKey) {
    console.error('YouTube API key not configured');
    return;
  }

  const insertLog = db.prepare('INSERT INTO search_logs (keyword, video_count, status) VALUES (?, ?, ?)');

  for (const keyword of SEARCH_KEYWORDS) {
    try {
      console.log(`Searching for: ${keyword}`);
      const videos = await searchYouTubeVideos(keyword, apiKey, 200);
      
      saveVideos(videos);
      insertLog.run(keyword, videos.length, 'success');
      
      console.log(`✓ Saved ${videos.length} videos for "${keyword}"`);
      
      // API 제한 방지를 위한 대기
      await new Promise(resolve => setTimeout(resolve, 1000));
      
    } catch (error) {
      console.error(`✗ Error searching "${keyword}":`, error.message);
      insertLog.run(keyword, 0, 'error');
    }
  }

  console.log('Automatic search completed!');
}

// API 엔드포인트

// 설정 가져오기
app.get('/api/settings', (req, res) => {
  try {
    const getSettings = db.prepare('SELECT key, value FROM settings');
    const settings = getSettings.all();
    const settingsObj = {};
    settings.forEach(s => settingsObj[s.key] = s.value);
    res.json(settingsObj);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 설정 업데이트
app.post('/api/settings', (req, res) => {
  try {
    const { key, value } = req.body;
    const updateSetting = db.prepare('UPDATE settings SET value = ? WHERE key = ?');
    updateSetting.run(value, key);
    res.json({ success: true, message: 'Setting updated' });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 수동 검색 실행
app.post('/api/search', async (req, res) => {
  try {
    const { keyword } = req.body;
    const apiKey = process.env.YOUTUBE_API_KEY;
    
    if (!keyword || !apiKey) {
      return res.status(400).json({ error: 'Keyword and API key are required' });
    }

    const videos = await searchYouTubeVideos(keyword, apiKey, 200);
    saveVideos(videos);

    const insertLog = db.prepare('INSERT INTO search_logs (keyword, video_count, status) VALUES (?, ?, ?)');
    insertLog.run(keyword, videos.length, 'success');

    res.json({ 
      success: true, 
      message: `Found and saved ${videos.length} videos`,
      count: videos.length
    });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 모든 비디오 가져오기 (페이지네이션)
app.get('/api/videos', (req, res) => {
  try {
    const { keyword, page = 1, limit = 50 } = req.query;
    const offset = (page - 1) * limit;

    let query = 'SELECT * FROM videos';
    let countQuery = 'SELECT COUNT(*) as count FROM videos';
    const params = [];

    if (keyword) {
      query += ' WHERE keyword = ?';
      countQuery += ' WHERE keyword = ?';
      params.push(keyword);
    }

    query += ' ORDER BY view_count DESC LIMIT ? OFFSET ?';
    params.push(parseInt(limit), offset);

    const getVideos = db.prepare(query);
    const getCount = db.prepare(countQuery);

    const videos = keyword ? getVideos.all(keyword, parseInt(limit), offset) : getVideos.all(parseInt(limit), offset);
    const totalCount = keyword ? getCount.get(keyword).count : getCount.get().count;

    res.json({
      videos,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total: totalCount,
        totalPages: Math.ceil(totalCount / limit)
      }
    });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 키워드 목록 가져오기
app.get('/api/keywords', (req, res) => {
  try {
    const getKeywords = db.prepare('SELECT DISTINCT keyword FROM videos');
    const keywords = getKeywords.all();
    res.json(keywords.map(k => k.keyword));
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 검색 로그 가져오기
app.get('/api/logs', (req, res) => {
  try {
    const { limit = 50 } = req.query;
    const getLogs = db.prepare('SELECT * FROM search_logs ORDER BY searched_at DESC LIMIT ?');
    const logs = getLogs.all(parseInt(limit));
    res.json(logs);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 통계 가져오기
app.get('/api/stats', (req, res) => {
  try {
    const stats = {
      totalVideos: db.prepare('SELECT COUNT(*) as count FROM videos').get().count,
      totalKeywords: db.prepare('SELECT COUNT(DISTINCT keyword) as count FROM videos').get().count,
      totalSearches: db.prepare('SELECT COUNT(*) as count FROM search_logs').get().count,
      lastSearch: db.prepare('SELECT searched_at FROM search_logs ORDER BY searched_at DESC LIMIT 1').get()?.searched_at
    };
    res.json(stats);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 데이터 삭제
app.delete('/api/videos', (req, res) => {
  try {
    const { keyword } = req.query;
    
    if (keyword) {
      db.prepare('DELETE FROM videos WHERE keyword = ?').run(keyword);
      res.json({ success: true, message: `Deleted videos for keyword: ${keyword}` });
    } else {
      db.prepare('DELETE FROM videos').run();
      res.json({ success: true, message: 'Deleted all videos' });
    }
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// 스케줄러 설정 (매일 오전 6시 실행)
function setupScheduler() {
  const getSetting = db.prepare('SELECT value FROM settings WHERE key = ?');
  const autoSearchEnabled = getSetting.get('auto_search_enabled')?.value === 'true';
  
  if (autoSearchEnabled) {
    // 매일 오전 6시에 실행
    cron.schedule('0 6 * * *', () => {
      console.log('Running scheduled YouTube search at 6:00 AM');
      runAutoSearch();
    });
    console.log('✓ Scheduler enabled: Running daily at 6:00 AM');
  }
}

// 서버 시작
app.listen(PORT, () => {
  console.log(`\n🚀 YouTube Trend Analyzer API Server`);
  console.log(`📡 Server running on http://localhost:${PORT}`);
  console.log(`📊 Database: youtube-trends.db`);
  console.log(`⏰ Auto-search: Every day at 6:00 AM\n`);
  
  setupScheduler();
});

// 프로세스 종료 시 데이터베이스 닫기
process.on('SIGINT', () => {
  db.close();
  process.exit(0);
});
