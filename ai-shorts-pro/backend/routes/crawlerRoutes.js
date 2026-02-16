const express = require('express');
const router = express.Router();
const crawlerController = require('../controllers/crawlerController');

// Crawl content from URL
router.post('/crawl', crawlerController.crawlContent);

// Analyze crawled content
router.post('/analyze', crawlerController.analyzeContent);

// Generate script from content
router.post('/generate-script', crawlerController.generateScript);

// Extract and download images
router.post('/extract-images', crawlerController.extractImages);

module.exports = router;
