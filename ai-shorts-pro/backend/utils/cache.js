const NodeCache = require('node-cache');
const crypto = require('crypto');

// Create cache instance with 1 hour TTL
const cache = new NodeCache({
  stdTTL: 3600, // 1 hour
  checkperiod: 600, // Check for expired keys every 10 minutes
  useClones: false // Don't clone data (better performance)
});

// Generate cache key from URL and options
function generateCacheKey(url, options = {}) {
  const data = JSON.stringify({ url, ...options });
  return crypto.createHash('md5').update(data).digest('hex');
}

// Get cached data
function get(key) {
  const data = cache.get(key);
  if (data) {
    console.log(`✅ Cache HIT: ${key}`);
    return data;
  }
  console.log(`❌ Cache MISS: ${key}`);
  return null;
}

// Set cache data with optional TTL
function set(key, value, ttl) {
  const success = cache.set(key, value, ttl);
  if (success) {
    console.log(`💾 Cache SET: ${key}`);
  }
  return success;
}

// Delete cached data
function del(key) {
  const count = cache.del(key);
  if (count > 0) {
    console.log(`🗑️ Cache DEL: ${key}`);
  }
  return count;
}

// Clear all cache
function flush() {
  cache.flushAll();
  console.log('🧹 Cache FLUSHED');
}

// Get cache statistics
function getStats() {
  return {
    keys: cache.keys().length,
    hits: cache.getStats().hits,
    misses: cache.getStats().misses,
    ksize: cache.getStats().ksize,
    vsize: cache.getStats().vsize
  };
}

// Cache middleware for Express
function cacheMiddleware(ttl = 3600) {
  return (req, res, next) => {
    // Generate cache key from request
    const cacheKey = generateCacheKey(req.originalUrl, {
      method: req.method,
      body: req.body
    });

    // Try to get cached response
    const cachedResponse = get(cacheKey);
    
    if (cachedResponse) {
      // Set cache hit header
      res.set('X-Cache', 'HIT');
      return res.json(cachedResponse);
    }

    // Store original json method
    const originalJson = res.json.bind(res);

    // Override json method to cache response
    res.json = function(data) {
      // Only cache successful responses
      if (data && data.success !== false) {
        set(cacheKey, data, ttl);
      }
      
      // Set cache miss header
      res.set('X-Cache', 'MISS');
      
      return originalJson(data);
    };

    next();
  };
}

module.exports = {
  get,
  set,
  del,
  flush,
  getStats,
  generateCacheKey,
  cacheMiddleware
};
