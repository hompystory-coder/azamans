const express = require('express');
const router = express.Router();
const generationController = require('../controllers/generationController');

// POST start shorts generation
router.post('/start', generationController.startGeneration);

// GET generation progress
router.get('/:jobId/progress', generationController.getProgress);

// POST cancel generation
router.post('/:jobId/cancel', generationController.cancelGeneration);

// GET download generated video
router.get('/:jobId/download', generationController.downloadVideo);

// POST generate single scene
router.post('/scene', generationController.generateScene);

// POST generate character image
router.post('/character-image', generationController.generateCharacterImage);

// POST generate voice
router.post('/voice', generationController.generateVoice);

// POST generate background music
router.post('/bgm', generationController.generateBGM);

// POST render final video
router.post('/render', generationController.renderVideo);

module.exports = router;
