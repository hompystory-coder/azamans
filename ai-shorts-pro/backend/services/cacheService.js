const redis = require('redis');

class CacheService {
  constructor() {
    this.client = null;
    this.isConnected = false;
    this.useRedis = process.env.REDIS_ENABLED === 'true';
    
    if (this.useRedis) {
      this.initRedis();
    } else {
      // In-memory cache fallback
      this.memoryCache = new Map();
      console.log('📦 Using in-memory cache (Redis disabled)');
    }
  }

  async initRedis() {
    try {
      this.client = redis.createClient({
        url: process.env.REDIS_URL || 'redis://localhost:6379',
        socket: {
          reconnectStrategy: (retries) => {
            if (retries > 10) {
              return new Error('Redis reconnection failed');
            }
            return Math.min(retries * 100, 3000);
          }
        }
      });

      this.client.on('error', (err) => {
        console.error('Redis Client Error:', err);
        this.isConnected = false;
      });

      this.client.on('connect', () => {
        console.log('✅ Redis connected successfully');
        this.isConnected = true;
      });

      await this.client.connect();
    } catch (error) {
      console.error('Failed to initialize Redis, falling back to in-memory cache:', error);
      this.memoryCache = new Map();
      this.useRedis = false;
    }
  }

  /**
   * Get value from cache
   */
  async get(key) {
    try {
      if (this.useRedis && this.isConnected) {
        const value = await this.client.get(key);
        return value ? JSON.parse(value) : null;
      } else {
        return this.memoryCache.get(key) || null;
      }
    } catch (error) {
      console.error('Cache get error:', error);
      return null;
    }
  }

  /**
   * Set value in cache with expiration (in seconds)
   */
  async set(key, value, expirationSeconds = 3600) {
    try {
      if (this.useRedis && this.isConnected) {
        await this.client.setEx(key, expirationSeconds, JSON.stringify(value));
      } else {
        this.memoryCache.set(key, value);
        // Set expiration for memory cache
        setTimeout(() => {
          this.memoryCache.delete(key);
        }, expirationSeconds * 1000);
      }
      return true;
    } catch (error) {
      console.error('Cache set error:', error);
      return false;
    }
  }

  /**
   * Delete value from cache
   */
  async del(key) {
    try {
      if (this.useRedis && this.isConnected) {
        await this.client.del(key);
      } else {
        this.memoryCache.delete(key);
      }
      return true;
    } catch (error) {
      console.error('Cache delete error:', error);
      return false;
    }
  }

  /**
   * Check if key exists
   */
  async exists(key) {
    try {
      if (this.useRedis && this.isConnected) {
        return await this.client.exists(key) === 1;
      } else {
        return this.memoryCache.has(key);
      }
    } catch (error) {
      console.error('Cache exists error:', error);
      return false;
    }
  }

  /**
   * Get multiple values at once
   */
  async mget(keys) {
    try {
      if (this.useRedis && this.isConnected) {
        const values = await this.client.mGet(keys);
        return values.map(v => v ? JSON.parse(v) : null);
      } else {
        return keys.map(key => this.memoryCache.get(key) || null);
      }
    } catch (error) {
      console.error('Cache mget error:', error);
      return [];
    }
  }

  /**
   * Clear all cache
   */
  async flushAll() {
    try {
      if (this.useRedis && this.isConnected) {
        await this.client.flushAll();
      } else {
        this.memoryCache.clear();
      }
      return true;
    } catch (error) {
      console.error('Cache flush error:', error);
      return false;
    }
  }

  /**
   * Get cache statistics
   */
  async getStats() {
    try {
      if (this.useRedis && this.isConnected) {
        const info = await this.client.info('stats');
        return {
          type: 'redis',
          connected: this.isConnected,
          info: info
        };
      } else {
        return {
          type: 'memory',
          size: this.memoryCache.size,
          keys: Array.from(this.memoryCache.keys())
        };
      }
    } catch (error) {
      console.error('Cache stats error:', error);
      return { error: error.message };
    }
  }

  /**
   * Cache character images (reuse same character for multiple scenes)
   */
  async cacheCharacterImage(characterId, action, imageUrl) {
    const key = `character:${characterId}:${action}`;
    await this.set(key, imageUrl, 86400); // Cache for 24 hours
  }

  async getCachedCharacterImage(characterId, action) {
    const key = `character:${characterId}:${action}`;
    return await this.get(key);
  }

  /**
   * Cache voice samples
   */
  async cacheVoiceSample(voiceId, text, audioUrl) {
    const key = `voice:${voiceId}:${text}`;
    await this.set(key, audioUrl, 86400); // Cache for 24 hours
  }

  async getCachedVoiceSample(voiceId, text) {
    const key = `voice:${voiceId}:${text}`;
    return await this.get(key);
  }

  /**
   * Cache BGM
   */
  async cacheBGM(prompt, audioUrl) {
    const key = `bgm:${prompt}`;
    await this.set(key, audioUrl, 604800); // Cache for 7 days
  }

  async getCachedBGM(prompt) {
    const key = `bgm:${prompt}`;
    return await this.get(key);
  }

  /**
   * Cache script generation
   */
  async cacheScript(contentHash, script) {
    const key = `script:${contentHash}`;
    await this.set(key, script, 3600); // Cache for 1 hour
  }

  async getCachedScript(contentHash) {
    const key = `script:${contentHash}`;
    return await this.get(key);
  }
}

// Singleton instance
const cacheService = new CacheService();

module.exports = cacheService;
