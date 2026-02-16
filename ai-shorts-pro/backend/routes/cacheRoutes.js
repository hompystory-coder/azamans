const express = require('express');
const router = express.Router();
const cache = require('../utils/cache');

// Get cache statistics
router.get('/stats', (req, res) => {
  try {
    const stats = cache.getStats();
    
    res.json({
      success: true,
      data: {
        ...stats,
        hitRate: stats.hits > 0 ? ((stats.hits / (stats.hits + stats.misses)) * 100).toFixed(2) + '%' : '0%'
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// Clear all cache
router.delete('/flush', (req, res) => {
  try {
    cache.flush();
    
    res.json({
      success: true,
      message: 'Cache flushed successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// Delete specific cache entry
router.delete('/entry/:key', (req, res) => {
  try {
    const { key } = req.params;
    const count = cache.del(key);
    
    res.json({
      success: true,
      deleted: count > 0,
      message: count > 0 ? 'Cache entry deleted' : 'Cache entry not found'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

module.exports = router;
