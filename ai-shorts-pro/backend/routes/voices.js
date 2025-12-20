const express = require('express');
const router = express.Router();
const voices = require('../../shared/voices.json');
const voiceController = require('../controllers/voiceController');

// GET all voices
router.get('/', (req, res) => {
  try {
    res.json({
      success: true,
      data: voices.voices,
      defaultVoice: voices.defaultVoice
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// GET voice by ID
router.get('/:id', (req, res) => {
  try {
    const voice = voices.voices.find(v => v.id === req.params.id);
    if (!voice) {
      return res.status(404).json({
        success: false,
        error: 'Voice not found'
      });
    }
    res.json({
      success: true,
      data: voice
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// POST generate voice sample
router.post('/sample', voiceController.generateSample);

// POST test voice with custom text
router.post('/test', voiceController.testVoice);

module.exports = router;
