const express = require('express');
const router = express.Router();
const videoController = require('../controllers/videoController');
const { characters, videoModes } = require('../services/characterService');

// 캐릭터 목록 조회
router.get('/characters', (req, res) => {
  res.json({
    success: true,
    data: characters
  });
});

// 비디오 모드 목록 조회
router.get('/video-modes', (req, res) => {
  res.json({
    success: true,
    data: videoModes
  });
});

// 전체 쇼츠 생성 (자동 모드)
router.post('/create-shorts', videoController.createShorts);

// 스크립트만 생성 (수동 모드 1단계)
router.post('/generate-script', videoController.generateScript);

// 비디오 렌더링 (수동 모드 2단계)
router.post('/render', videoController.renderVideo);

module.exports = router;
