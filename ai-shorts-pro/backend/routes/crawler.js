const express = require('express');
const router = express.Router();
const crawlerController = require('../controllers/crawlerController');

// POST crawl blog/article
router.post('/crawl', crawlerController.crawlContent);

// POST analyze crawled content
router.post('/analyze', crawlerController.analyzeContent);

// POST generate script from crawled content
router.post('/generate-script', crawlerController.generateScript);

// POST extract images from URL
router.post('/extract-images', crawlerController.extractImages);

module.exports = router;
