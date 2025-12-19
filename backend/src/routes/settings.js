// 설정 관리 API
import express from 'express';
import fs from 'fs/promises';
import path from 'path';

const router = express.Router();

// 설정 파일 경로
const SETTINGS_DIR = process.env.UPLOAD_DIR ? path.join(process.env.UPLOAD_DIR, 'settings') : '/tmp/settings';
const SETTINGS_FILE = path.join(SETTINGS_DIR, 'user-settings.json');

// 설정 디렉토리 생성
async function ensureSettingsDir() {
  try {
    await fs.access(SETTINGS_DIR);
  } catch {
    await fs.mkdir(SETTINGS_DIR, { recursive: true });
  }
}

// GET /api/settings/list - 저장된 설정 목록
router.get('/list', async (req, res) => {
  try {
    await ensureSettingsDir();
    
    try {
      const data = await fs.readFile(SETTINGS_FILE, 'utf-8');
      const settings = JSON.parse(data);
      
      res.json({
        success: true,
        data: settings
      });
    } catch {
      // 파일이 없으면 빈 배열
      res.json({
        success: true,
        data: []
      });
    }
  } catch (error) {
    console.error('설정 목록 조회 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// POST /api/settings/save - 설정 저장
router.post('/save', async (req, res) => {
  try {
    const { name, settings } = req.body;
    
    if (!name || !settings) {
      return res.status(400).json({
        success: false,
        error: 'name과 settings가 필요합니다.'
      });
    }
    
    await ensureSettingsDir();
    
    // 기존 설정 읽기
    let allSettings = [];
    try {
      const data = await fs.readFile(SETTINGS_FILE, 'utf-8');
      allSettings = JSON.parse(data);
    } catch {
      // 파일이 없으면 빈 배열
    }
    
    // 새 설정 추가
    const newSetting = {
      id: Date.now().toString(),
      name,
      settings,
      createdAt: new Date().toISOString()
    };
    
    allSettings.push(newSetting);
    
    // 파일에 저장
    await fs.writeFile(SETTINGS_FILE, JSON.stringify(allSettings, null, 2));
    
    res.json({
      success: true,
      data: newSetting
    });
  } catch (error) {
    console.error('설정 저장 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// DELETE /api/settings/:id - 설정 삭제
router.delete('/:id', async (req, res) => {
  try {
    const { id } = req.params;
    
    await ensureSettingsDir();
    
    // 기존 설정 읽기
    const data = await fs.readFile(SETTINGS_FILE, 'utf-8');
    let allSettings = JSON.parse(data);
    
    // 필터링
    allSettings = allSettings.filter(s => s.id !== id);
    
    // 파일에 저장
    await fs.writeFile(SETTINGS_FILE, JSON.stringify(allSettings, null, 2));
    
    res.json({
      success: true
    });
  } catch (error) {
    console.error('설정 삭제 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

export default router;
