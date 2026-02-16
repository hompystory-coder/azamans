const express = require('express');
const router = express.Router();
const path = require('path');
const fs = require('fs');

// Download generated shorts
router.get('/shorts/:filename', (req, res) => {
  try {
    const filename = req.params.filename;
    const filePaths = [
      `/mnt/music-storage/shorts-videos/output/${filename}`,
      `/mnt/music-storage/generated-shorts/${filename}`,
      `/mnt/music-storage/generated-shorts/videos/${filename}`
    ];
    
    for (const filePath of filePaths) {
      if (fs.existsSync(filePath)) {
        console.log(`📥 Serving video: ${filePath}`);
        res.setHeader('Content-Type', 'video/mp4');
        res.setHeader('Content-Disposition', `inline; filename="${filename}"`);
        res.setHeader('Cache-Control', 'public, max-age=31536000');
        res.setHeader('Access-Control-Allow-Origin', '*');
        return res.sendFile(filePath);
      }
    }
    
    res.status(404).json({ error: 'Video not found' });
  } catch (error) {
    console.error('Download error:', error);
    res.status(500).json({ error: error.message });
  }
});

module.exports = router;
