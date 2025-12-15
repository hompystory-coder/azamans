import express from 'express';
import cors from 'cors';
import path from 'path';
import sqlite3 from 'sqlite3';
import fs from 'fs';
import { fileURLToPath } from 'url';
import crypto from 'crypto';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3003;

// 데이터베이스 경로 - 새로 생성한 데이터베이스 사용
const DB_PATH = path.join(__dirname, 'shorts-market.db');

// 데이터베이스 연결
let db;
try {
  if (!fs.existsSync(DB_PATH)) {
    console.error(`Database file not found: ${DB_PATH}`);
    process.exit(1);
  }
  const Database = sqlite3.default || sqlite3;
  db = new Database.Database(DB_PATH, Database.OPEN_READWRITE, (err) => {
    if (err) {
      console.error('Failed to connect to database:', err.message);
      process.exit(1);
    }
    console.log('✅ Connected to SQLite database:', DB_PATH);
  });
} catch (error) {
  console.error('Database initialization error:', error);
  process.exit(1);
}

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('dist'));

// API Keys from environment
const API_KEYS = {
  COUPANG_ACCESS_KEY: process.env.COUPANG_ACCESS_KEY || 'c70d5581-434b-4223-9c81-f72641545958',
  COUPANG_SECRET_KEY: process.env.COUPANG_SECRET_KEY || '115b6ad08b30eeba54a624f2ed94ca3f0f18005d',
  COUPANG_PARTNER_ID: process.env.COUPANG_PARTNER_ID || 'AF8150630',
  YOUTUBE_API_KEY: process.env.YOUTUBE_API_KEY || 'your_youtube_api_key_here',
  JWT_SECRET: process.env.JWT_SECRET || 'your_jwt_secret_here_ESlISrPC33IMEwsYuVQq703GmaU4eQ9wP9cmMytkMzw='
};

// Helper function to promisify database queries
function dbAll(query, params = []) {
  return new Promise((resolve, reject) => {
    db.all(query, params, (err, rows) => {
      if (err) reject(err);
      else resolve(rows);
    });
  });
}

function dbGet(query, params = []) {
  return new Promise((resolve, reject) => {
    db.get(query, params, (err, row) => {
      if (err) reject(err);
      else resolve(row);
    });
  });
}

function dbRun(query, params = []) {
  return new Promise((resolve, reject) => {
    db.run(query, params, function(err) {
      if (err) reject(err);
      else resolve({ lastID: this.lastID, changes: this.changes });
    });
  });
}

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Get API keys and environment info
app.get('/api/config', (req, res) => {
  res.json({
    success: true,
    config: {
      COUPANG_ACCESS_KEY: API_KEYS.COUPANG_ACCESS_KEY,
      COUPANG_SECRET_KEY: API_KEYS.COUPANG_SECRET_KEY.substring(0, 10) + '...',
      COUPANG_PARTNER_ID: API_KEYS.COUPANG_PARTNER_ID,
      YOUTUBE_API_KEY: API_KEYS.YOUTUBE_API_KEY === 'your_youtube_api_key_here' ? 'NOT_SET' : 'CONFIGURED',
      JWT_SECRET: 'CONFIGURED',
      DATABASE: DB_PATH
    }
  });
});

// Get all shorts
app.get('/api/shorts', async (req, res) => {
  try {
    const shorts = await dbAll(`
      SELECT 
        s.*,
        c.youtube_channel_name,
        c.subtag,
        u.name as creator_name
      FROM shorts s
      LEFT JOIN creators c ON s.creator_id = c.id
      LEFT JOIN users u ON c.user_id = u.id
      ORDER BY s.created_at DESC
    `);
    
    res.json({
      success: true,
      data: shorts,
      count: shorts.length
    });
  } catch (error) {
    console.error('Error fetching shorts:', error);
    res.status(500).json({
      success: false,
      error: '쇼츠 목록 조회 중 오류가 발생했습니다.'
    });
  }
});

// Get shorts by status
app.get('/api/shorts/status/:status', async (req, res) => {
  try {
    const { status } = req.params;
    const shorts = await dbAll(`
      SELECT 
        s.*,
        c.youtube_channel_name,
        c.subtag,
        u.name as creator_name
      FROM shorts s
      LEFT JOIN creators c ON s.creator_id = c.id
      LEFT JOIN users u ON c.user_id = u.id
      WHERE s.status = ?
      ORDER BY s.created_at DESC
    `, [status]);
    
    res.json({
      success: true,
      data: shorts,
      count: shorts.length
    });
  } catch (error) {
    console.error('Error fetching shorts by status:', error);
    res.status(500).json({
      success: false,
      error: '쇼츠 목록 조회 중 오류가 발생했습니다.'
    });
  }
});

// Get categories
app.get('/api/shorts/categories/list', async (req, res) => {
  try {
    const categories = await dbAll(`
      SELECT 
        category,
        COUNT(*) as count
      FROM shorts
      WHERE category IS NOT NULL
      GROUP BY category
      ORDER BY count DESC
    `);
    
    res.json({
      success: true,
      data: categories
    });
  } catch (error) {
    console.error('Error fetching categories:', error);
    res.status(500).json({
      success: false,
      error: '카테고리 목록 조회 중 오류가 발생했습니다.'
    });
  }
});

// Get all creators
app.get('/api/admin/creators', async (req, res) => {
  try {
    const creators = await dbAll(`
      SELECT 
        c.*,
        u.name as user_name,
        u.email as user_email,
        COUNT(DISTINCT s.id) as shorts_count,
        SUM(s.click_count) as total_clicks,
        SUM(s.earnings) as total_earnings
      FROM creators c
      LEFT JOIN users u ON c.user_id = u.id
      LEFT JOIN shorts s ON c.id = s.creator_id
      GROUP BY c.id
      ORDER BY c.created_at DESC
    `);
    
    res.json({
      success: true,
      data: creators,
      count: creators.length
    });
  } catch (error) {
    console.error('Error fetching creators:', error);
    res.status(500).json({
      success: false,
      error: '크리에이터 목록 조회 중 오류가 발생했습니다.'
    });
  }
});

// Get all users
app.get('/api/admin/users', async (req, res) => {
  try {
    const users = await dbAll(`
      SELECT 
        u.id,
        u.email,
        u.name,
        u.role,
        u.created_at,
        COUNT(DISTINCT c.id) as creator_count
      FROM users u
      LEFT JOIN creators c ON u.id = c.user_id
      GROUP BY u.id
      ORDER BY u.created_at DESC
    `);
    
    res.json({
      success: true,
      data: users,
      count: users.length
    });
  } catch (error) {
    console.error('Error fetching users:', error);
    res.status(500).json({
      success: false,
      error: '사용자 목록 조회 중 오류가 발생했습니다.'
    });
  }
});

// Get statistics
app.get('/api/stats', async (req, res) => {
  try {
    const stats = await dbAll(`
      SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM creators) as total_creators,
        (SELECT COUNT(*) FROM shorts) as total_shorts,
        (SELECT COUNT(*) FROM shorts WHERE status = 'approved') as approved_shorts,
        (SELECT COUNT(*) FROM shorts WHERE status = 'pending') as pending_shorts,
        (SELECT SUM(click_count) FROM shorts) as total_clicks,
        (SELECT SUM(earnings) FROM shorts) as total_earnings
    `);
    
    res.json({
      success: true,
      data: stats[0]
    });
  } catch (error) {
    console.error('Error fetching stats:', error);
    res.status(500).json({
      success: false,
      error: '통계 조회 중 오류가 발생했습니다.'
    });
  }
});

// User settings endpoints
// Get user settings
app.get('/api/user/settings/:email', async (req, res) => {
  try {
    const { email } = req.params;
    
    // Get user
    const user = await dbGet('SELECT * FROM users WHERE email = ?', [email]);
    if (!user) {
      return res.status(404).json({
        success: false,
        error: '사용자를 찾을 수 없습니다.'
      });
    }
    
    // Get creator info if exists
    const creator = await dbGet('SELECT * FROM creators WHERE user_id = ?', [user.id]);
    
    res.json({
      success: true,
      data: {
        channelId: creator?.youtube_channel_id || '',
        channelName: creator?.youtube_channel_name || '',
        channelUrl: creator?.youtube_channel_url || '',
        coupangPartnerId: creator?.coupang_partner_id || '',
        coupangAccessKey: creator?.coupang_access_key || '',
        coupangSecretKey: creator?.coupang_secret_key || '',
        subtag: creator?.subtag || ''
      }
    });
  } catch (error) {
    console.error('Get settings error:', error);
    res.status(500).json({
      success: false,
      error: '설정 조회 중 오류가 발생했습니다.'
    });
  }
});

// Update user settings
app.post('/api/user/settings/:email', async (req, res) => {
  try {
    const { email } = req.params;
    const { channelId, coupangPartnerId, coupangAccessKey, coupangSecretKey } = req.body;
    
    // Get user
    const user = await dbGet('SELECT * FROM users WHERE email = ?', [email]);
    if (!user) {
      return res.status(404).json({
        success: false,
        error: '사용자를 찾을 수 없습니다.'
      });
    }
    
    // Get or create creator
    let creator = await dbGet('SELECT * FROM creators WHERE user_id = ?', [user.id]);
    
    if (creator) {
      // Update existing creator
      await dbRun(
        `UPDATE creators SET 
          youtube_channel_id = ?,
          coupang_partner_id = ?,
          coupang_access_key = ?,
          coupang_secret_key = ?,
          updated_at = CURRENT_TIMESTAMP
         WHERE user_id = ?`,
        [channelId, coupangPartnerId, coupangAccessKey, coupangSecretKey, user.id]
      );
    } else {
      // Create new creator
      await dbRun(
        `INSERT INTO creators (user_id, youtube_channel_id, coupang_partner_id, coupang_access_key, coupang_secret_key, subtag)
         VALUES (?, ?, ?, ?, ?, ?)`,
        [user.id, channelId, coupangPartnerId, coupangAccessKey, coupangSecretKey, `SUB${user.id}`]
      );
    }
    
    res.json({
      success: true,
      message: '설정이 저장되었습니다.'
    });
  } catch (error) {
    console.error('Update settings error:', error);
    res.status(500).json({
      success: false,
      error: '설정 저장 중 오류가 발생했습니다.'
    });
  }
});

// Get user's shorts
app.get('/api/user/shorts/:email', async (req, res) => {
  try {
    const { email } = req.params;
    
    // Get user
    const user = await dbGet('SELECT * FROM users WHERE email = ?', [email]);
    if (!user) {
      return res.status(404).json({
        success: false,
        error: '사용자를 찾을 수 없습니다.'
      });
    }
    
    // Get creator
    const creator = await dbGet('SELECT * FROM creators WHERE user_id = ?', [user.id]);
    if (!creator) {
      return res.json({
        success: true,
        data: []
      });
    }
    
    // Get shorts
    const shorts = await dbAll(
      `SELECT * FROM shorts WHERE creator_id = ? ORDER BY created_at DESC`,
      [creator.id]
    );
    
    res.json({
      success: true,
      data: shorts
    });
  } catch (error) {
    console.error('Get user shorts error:', error);
    res.status(500).json({
      success: false,
      error: '쇼츠 목록 조회 중 오류가 발생했습니다.'
    });
  }
});

// Creator registration
app.post('/api/creator/register', async (req, res) => {
  try {
    const { email, channelId, channelName, channelUrl, coupangPartnerId, coupangAccessKey, coupangSecretKey } = req.body;
    
    // Get user
    const user = await dbGet('SELECT * FROM users WHERE email = ?', [email]);
    if (!user) {
      return res.status(404).json({
        success: false,
        error: '사용자를 찾을 수 없습니다.'
      });
    }
    
    // Check if creator already exists
    const existing = await dbGet('SELECT * FROM creators WHERE user_id = ?', [user.id]);
    if (existing) {
      return res.status(400).json({
        success: false,
        error: '이미 크리에이터로 등록되어 있습니다.'
      });
    }
    
    // Create creator
    await dbRun(
      `INSERT INTO creators (user_id, youtube_channel_id, youtube_channel_name, youtube_channel_url, 
        coupang_partner_id, coupang_access_key, coupang_secret_key, subtag, is_approved)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)`,
      [user.id, channelId, channelName, channelUrl, coupangPartnerId, coupangAccessKey, coupangSecretKey, `SUB${user.id}`]
    );
    
    // Update user role
    await dbRun('UPDATE users SET role = ? WHERE id = ?', ['creator', user.id]);
    
    res.json({
      success: true,
      message: '크리에이터 등록이 완료되었습니다. 관리자 승인을 기다려주세요.'
    });
  } catch (error) {
    console.error('Creator registration error:', error);
    res.status(500).json({
      success: false,
      error: '크리에이터 등록 중 오류가 발생했습니다.'
    });
  }
});

// Fetch YouTube shorts (placeholder)
app.post('/api/youtube/fetch-shorts', async (req, res) => {
  try {
    const { channelId } = req.body;
    
    // This would normally call YouTube API
    // For now, return empty array
    res.json({
      success: true,
      data: [],
      message: 'YouTube API 연동이 필요합니다.'
    });
  } catch (error) {
    console.error('Fetch shorts error:', error);
    res.status(500).json({
      success: false,
      error: '쇼츠 가져오기 중 오류가 발생했습니다.'
    });
  }
});

// Authentication endpoints
// Login
app.post('/api/auth/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    
    if (!email || !password) {
      return res.status(400).json({
        success: false,
        error: '이메일과 비밀번호를 입력해주세요.'
      });
    }
    
    // Get user by email
    const user = await dbGet('SELECT * FROM users WHERE email = ?', [email]);
    
    if (!user) {
      return res.status(401).json({
        success: false,
        error: '이메일 또는 비밀번호가 올바르지 않습니다.'
      });
    }
    
    // Hash password with SHA256 to compare
    const passwordHash = crypto.createHash('sha256').update(password).digest('hex');
    
    if (user.password_hash !== passwordHash) {
      return res.status(401).json({
        success: false,
        error: '이메일 또는 비밀번호가 올바르지 않습니다.'
      });
    }
    
    // Generate a simple token (in production, use JWT)
    const token = crypto.randomBytes(32).toString('hex');
    
    // Remove password hash from response
    delete user.password_hash;
    
    res.json({
      success: true,
      data: {
        user,
        token
      }
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({
      success: false,
      error: '로그인 중 오류가 발생했습니다.'
    });
  }
});

// Register
app.post('/api/auth/register', async (req, res) => {
  try {
    const { email, password, name } = req.body;
    
    if (!email || !password || !name) {
      return res.status(400).json({
        success: false,
        error: '모든 필드를 입력해주세요.'
      });
    }
    
    // Check if user already exists
    const existingUser = await dbGet('SELECT id FROM users WHERE email = ?', [email]);
    
    if (existingUser) {
      return res.status(400).json({
        success: false,
        error: '이미 존재하는 이메일입니다.'
      });
    }
    
    // Hash password
    const passwordHash = crypto.createHash('sha256').update(password).digest('hex');
    
    // Insert new user
    const result = await dbRun(
      'INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, ?)',
      [email, passwordHash, name, 'user']
    );
    
    // Get created user
    const user = await dbGet('SELECT * FROM users WHERE id = ?', [result.lastID]);
    delete user.password_hash;
    
    // Generate token
    const token = crypto.randomBytes(32).toString('hex');
    
    res.json({
      success: true,
      data: {
        user,
        token
      }
    });
  } catch (error) {
    console.error('Register error:', error);
    res.status(500).json({
      success: false,
      error: '회원가입 중 오류가 발생했습니다.'
    });
  }
});

// Serve index.html for specific routes (client-side routing)
const clientRoutes = ['/', '/creator', '/mypage', '/admin', '/register', '/short/:id'];
clientRoutes.forEach(route => {
  app.get(route, (req, res) => {
    const filePath = path.join(__dirname, 'dist', 'index.html');
    if (fs.existsSync(filePath)) {
      res.sendFile(filePath);
    } else {
      res.status(404).json({ error: 'index.html not found' });
    }
  });
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`
╔════════════════════════════════════════════════════════════════╗
║           🚀 Shorts Market Server (Standalone)                 ║
╠════════════════════════════════════════════════════════════════╣
║  Port:     ${PORT}                                                  ║
║  Database: ${DB_PATH}                   ║
║  URL:      http://localhost:${PORT}                                ║
║  Health:   http://localhost:${PORT}/health                         ║
║  Config:   http://localhost:${PORT}/api/config                     ║
╚════════════════════════════════════════════════════════════════╝
  `);
  
  console.log('📦 API Keys Configuration:');
  console.log('  ✓ COUPANG_ACCESS_KEY:', API_KEYS.COUPANG_ACCESS_KEY);
  console.log('  ✓ COUPANG_SECRET_KEY:', API_KEYS.COUPANG_SECRET_KEY.substring(0, 15) + '...');
  console.log('  ✓ COUPANG_PARTNER_ID:', API_KEYS.COUPANG_PARTNER_ID);
  console.log('  ✓ JWT_SECRET: Configured');
  console.log('\n🔥 Server is ready!');
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM signal received: closing HTTP server');
  db.close();
  process.exit(0);
});

process.on('SIGINT', () => {
  console.log('SIGINT signal received: closing HTTP server');
  db.close();
  process.exit(0);
});
