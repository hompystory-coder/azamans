const express = require('express');
const router = express.Router();
const youtubeController = require('../controllers/youtubeController');

// Generate YouTube metadata from content
router.post('/generate', youtubeController.generateMetadata);

// Generate thumbnail suggestions
router.post('/thumbnail-suggestions', youtubeController.generateThumbnailSuggestions);

// Validate YouTube metadata
router.post('/validate', youtubeController.validateMetadata);

module.exports = router;
