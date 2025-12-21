const express = require('express');
const axios = require('axios');
const sharp = require('sharp');
const crypto = require('crypto');
const path = require('path');
const fs = require('fs').promises;

const router = express.Router();

// Image cache directory
const CACHE_DIR = path.join(__dirname, '../cache/images');
const THUMBNAIL_DIR = path.join(__dirname, '../cache/thumbnails');

// Ensure cache directories exist
async function ensureCacheDirectories() {
  await fs.mkdir(CACHE_DIR, { recursive: true });
  await fs.mkdir(THUMBNAIL_DIR, { recursive: true });
}

ensureCacheDirectories();

// Generate cache key from URL
function getCacheKey(url) {
  return crypto.createHash('md5').update(url).digest('hex');
}

// Proxy image with CORS support
router.get('/image', async (req, res) => {
  try {
    const { url, width, height, quality = 80 } = req.query;

    if (!url) {
      return res.status(400).json({
        success: false,
        error: 'Image URL is required'
      });
    }

    // Decode URL
    const imageUrl = decodeURIComponent(url);
    const cacheKey = getCacheKey(imageUrl);
    
    // Determine if thumbnail is requested
    const isThumbnail = width || height;
    const cacheDir = isThumbnail ? THUMBNAIL_DIR : CACHE_DIR;
    const cacheFile = path.join(
      cacheDir, 
      `${cacheKey}_${width || 'orig'}_${height || 'orig'}_${quality}.jpg`
    );

    // Check cache first
    try {
      await fs.access(cacheFile);
      const cachedImage = await fs.readFile(cacheFile);
      
      res.set({
        'Content-Type': 'image/jpeg',
        'Cache-Control': 'public, max-age=2592000', // 30 days
        'Access-Control-Allow-Origin': '*'
      });
      
      return res.send(cachedImage);
    } catch (err) {
      // Cache miss, continue to fetch
    }

    // Fetch image from original URL
    const response = await axios.get(imageUrl, {
      responseType: 'arraybuffer',
      timeout: 10000,
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      }
    });

    let imageBuffer = Buffer.from(response.data);

    // Process image with sharp if resizing is requested
    if (isThumbnail || quality < 100) {
      let sharpInstance = sharp(imageBuffer);

      if (width || height) {
        sharpInstance = sharpInstance.resize(
          width ? parseInt(width) : null,
          height ? parseInt(height) : null,
          {
            fit: 'inside',
            withoutEnlargement: true
          }
        );
      }

      imageBuffer = await sharpInstance
        .jpeg({ quality: parseInt(quality) })
        .toBuffer();
    }

    // Save to cache
    await fs.writeFile(cacheFile, imageBuffer);

    // Send response
    res.set({
      'Content-Type': 'image/jpeg',
      'Cache-Control': 'public, max-age=2592000',
      'Access-Control-Allow-Origin': '*',
      'X-Cache': 'MISS'
    });

    res.send(imageBuffer);

  } catch (error) {
    console.error('Image proxy error:', error);
    
    if (error.code === 'ECONNABORTED' || error.code === 'ETIMEDOUT') {
      return res.status(504).json({
        success: false,
        error: 'Image fetch timeout'
      });
    }

    res.status(500).json({
      success: false,
      error: 'Failed to proxy image: ' + error.message
    });
  }
});

// Batch process multiple images
router.post('/batch', async (req, res) => {
  try {
    const { images, width, height, quality = 80 } = req.body;

    if (!images || !Array.isArray(images)) {
      return res.status(400).json({
        success: false,
        error: 'Images array is required'
      });
    }

    const results = await Promise.allSettled(
      images.map(async (imageUrl) => {
        const cacheKey = getCacheKey(imageUrl);
        const proxyUrl = `/api/image-proxy/image?url=${encodeURIComponent(imageUrl)}&width=${width || ''}&height=${height || ''}&quality=${quality}`;
        
        return {
          original: imageUrl,
          proxy: proxyUrl,
          cacheKey: cacheKey
        };
      })
    );

    const processed = results
      .filter(r => r.status === 'fulfilled')
      .map(r => r.value);

    res.json({
      success: true,
      data: {
        images: processed,
        count: processed.length
      }
    });

  } catch (error) {
    console.error('Batch processing error:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// Clear cache
router.delete('/cache', async (req, res) => {
  try {
    const { older_than_days = 30 } = req.query;
    const cutoffTime = Date.now() - (older_than_days * 24 * 60 * 60 * 1000);

    let deletedCount = 0;

    for (const dir of [CACHE_DIR, THUMBNAIL_DIR]) {
      const files = await fs.readdir(dir);
      
      for (const file of files) {
        const filePath = path.join(dir, file);
        const stats = await fs.stat(filePath);
        
        if (stats.mtimeMs < cutoffTime) {
          await fs.unlink(filePath);
          deletedCount++;
        }
      }
    }

    res.json({
      success: true,
      data: {
        deletedCount: deletedCount,
        message: `Deleted ${deletedCount} cached images older than ${older_than_days} days`
      }
    });

  } catch (error) {
    console.error('Cache clear error:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// Get cache stats
router.get('/stats', async (req, res) => {
  try {
    const getDirectoryStats = async (dir) => {
      const files = await fs.readdir(dir);
      let totalSize = 0;
      
      for (const file of files) {
        const stats = await fs.stat(path.join(dir, file));
        totalSize += stats.size;
      }
      
      return {
        count: files.length,
        size: totalSize,
        sizeReadable: (totalSize / 1024 / 1024).toFixed(2) + ' MB'
      };
    };

    const cacheStats = await getDirectoryStats(CACHE_DIR);
    const thumbnailStats = await getDirectoryStats(THUMBNAIL_DIR);

    res.json({
      success: true,
      data: {
        cache: cacheStats,
        thumbnails: thumbnailStats,
        total: {
          count: cacheStats.count + thumbnailStats.count,
          size: cacheStats.size + thumbnailStats.size,
          sizeReadable: ((cacheStats.size + thumbnailStats.size) / 1024 / 1024).toFixed(2) + ' MB'
        }
      }
    });

  } catch (error) {
    console.error('Stats error:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

module.exports = router;
