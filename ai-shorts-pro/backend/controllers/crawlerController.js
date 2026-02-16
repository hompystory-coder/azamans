const axios = require('axios');
const cheerio = require('cheerio');
const path = require('path');
const fs = require('fs').promises;
const { v4: uuidv4 } = require('uuid');
const puppeteer = require('puppeteer');
const cache = require('../utils/cache');

// Crawl blog/article content with Puppeteer for dynamic sites
exports.crawlContent = async (req, res) => {
  let browser = null;
  const startTime = Date.now();
  
  try {
    const { url, timeout = 30000, useCache = true } = req.body;

    if (!url) {
      return res.status(400).json({
        success: false,
        error: 'URL is required'
      });
    }

    // Check cache first
    if (useCache) {
      const cacheKey = cache.generateCacheKey(url, { type: 'crawl' });
      const cachedData = cache.get(cacheKey);
      
      if (cachedData) {
        return res.json({
          success: true,
          data: {
            ...cachedData,
            cached: true,
            cacheAge: Math.floor((Date.now() - cachedData.timestamp) / 1000)
          }
        });
      }
    }

    // Validate URL format
    try {
      new URL(url);
    } catch (err) {
      return res.status(400).json({
        success: false,
        error: 'Invalid URL format'
      });
    }

    console.log('🔍 Crawling URL:', url);

    // Check if it's a Naver Blog (requires Puppeteer)
    const isNaverBlog = url.includes('blog.naver.com');
    
    if (isNaverBlog) {
      console.log('📱 Detected Naver Blog - using Puppeteer');
      
      // Launch headless browser with optimized settings
      browser = await puppeteer.launch({
        headless: true,
        args: [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-dev-shm-usage',
          '--disable-gpu',
          '--disable-software-rasterizer',
          '--disable-extensions'
        ],
        timeout: timeout
      });

      const page = await browser.newPage();
      
      // Set shorter default timeout
      page.setDefaultNavigationTimeout(timeout);
      page.setDefaultTimeout(timeout);
      
      // Set viewport and user agent
      await page.setViewport({ width: 1920, height: 1080 });
      await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
      
      // Navigate to URL with retry logic
      let retries = 2;
      let lastError = null;
      
      while (retries > 0) {
        try {
          await page.goto(url, { 
            waitUntil: 'domcontentloaded',  // Faster than networkidle2
            timeout: Math.min(timeout, 20000)
          });
          lastError = null;
          break;
        } catch (err) {
          lastError = err;
          retries--;
          if (retries > 0) {
            console.log(`⚠️ Navigation failed, retrying... (${retries} attempts left)`);
            await new Promise(resolve => setTimeout(resolve, 1000));
          }
        }
      }
      
      if (lastError) {
        throw new Error('Failed to load page after retries: ' + lastError.message);
      }

      // Wait for iframe to load (Naver Blog uses iframe) - with timeout
      await Promise.race([
        new Promise(resolve => setTimeout(resolve, 3000)),
        page.waitForSelector('iframe, .se-main-container', { timeout: 5000 }).catch(() => {})
      ]);

      // Get iframe content
      const frames = page.frames();
      let mainFrame = frames.find(f => f.url().includes('PostView.naver') || f.url().includes('PostList.naver'));
      
      if (!mainFrame) {
        mainFrame = page.mainFrame();
      }

      // Extract title with timeout protection
      let title = '';
      const titleSelectors = ['title', '.se-title-text', 'h3', 'h1', 'h2'];
      
      for (const selector of titleSelectors) {
        try {
          title = await Promise.race([
            mainFrame.$eval(selector, el => el.textContent),
            new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 2000))
          ]);
          if (title && title.trim()) break;
        } catch (err) {
          // Continue to next selector
        }
      }
      
      if (!title) title = '제목 없음';

      // Extract content
      let content = '';
      
      // Try multiple selectors for Naver Blog
      const selectors = [
        '.se-main-container',
        '.se-component',
        '.se-text',
        '#postViewArea',
        '.post-view',
        'div[class*="content"]',
        'article',
        'p'
      ];

      for (const selector of selectors) {
        try {
          const elements = await mainFrame.$$(selector);
          if (elements.length > 0) {
            const texts = await Promise.all(
              elements.map(el => el.evaluate(node => node.textContent))
            );
            const combined = texts.join('\n').trim();
            if (combined.length > content.length) {
              content = combined;
            }
          }
        } catch (err) {
          // Continue to next selector
        }
      }

      // Extract images with timeout and better error handling
      const images = [];
      try {
        const imgElements = await Promise.race([
          mainFrame.$$('img.se-image-resource, img[data-lazy-src], img[src]'),
          new Promise((_, reject) => setTimeout(() => reject(new Error('Image extraction timeout')), 5000))
        ]);
        
        const extractionPromises = [];
        for (let i = 0; i < Math.min(imgElements.length, 20); i++) {
          extractionPromises.push(
            (async () => {
              try {
                const src = await Promise.race([
                  imgElements[i].evaluate(el => 
                    el.getAttribute('data-lazy-src') || 
                    el.getAttribute('src') || 
                    el.getAttribute('data-src')
                  ),
                  new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 1000))
                ]);
                const alt = await imgElements[i].evaluate(el => el.getAttribute('alt') || '').catch(() => '');

                if (src && !src.includes('icon') && !src.includes('logo') && !src.includes('btn_')) {
                  let fullUrl = src;
                  if (src.startsWith('//')) {
                    fullUrl = 'https:' + src;
                  } else if (src.startsWith('/')) {
                    fullUrl = 'https://blog.naver.com' + src;
                  }

                  if (fullUrl.startsWith('http')) {
                    return {
                      url: fullUrl,
                      alt: alt,
                      description: alt || `이미지 ${i + 1}`
                    };
                  }
                }
              } catch (err) {
                // Silently skip failed images
              }
              return null;
            })()
          );
        }
        
        const results = await Promise.all(extractionPromises);
        images.push(...results.filter(img => img !== null));
      } catch (err) {
        console.error('Error extracting images:', err.message);
      }

      await browser.close();
      browser = null;
      
      const elapsedTime = Date.now() - startTime;

      console.log('✅ Crawling complete:', {
        title: title.slice(0, 50),
        contentLength: content.length,
        imageCount: images.length,
        duration: `${elapsedTime}ms`
      });

      const resultData = {
        url: url,
        title: title.trim(),
        content: content.trim(),
        images: images,
        imageCount: images.length,
        wordCount: content.split(/\s+/).filter(w => w.length > 0).length,
        crawlTime: elapsedTime,
        method: 'puppeteer',
        timestamp: Date.now()
      };

      // Cache the result for 1 hour
      const cacheKey = cache.generateCacheKey(url, { type: 'crawl' });
      cache.set(cacheKey, resultData, 3600);

      return res.json({
        success: true,
        data: resultData
      });

    } else {
      // Use regular HTTP request for non-Naver sites
      const response = await axios.get(url, {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        },
        timeout: Math.min(timeout, 10000),
        maxRedirects: 5
      });

      const $ = cheerio.load(response.data);
      
      // Extract content
      const title = $('h1').first().text().trim() || $('title').text().trim();
      
      // Extract paragraphs
      const paragraphs = [];
      $('p').each((i, elem) => {
        const text = $(elem).text().trim();
        if (text.length > 20) {
          paragraphs.push(text);
        }
      });

      const content = paragraphs.join('\n\n');

      // Extract images
      const images = [];
      $('img').each((i, elem) => {
        const src = $(elem).attr('src');
        const alt = $(elem).attr('alt') || '';
        
        if (src && !src.includes('icon') && !src.includes('logo')) {
          let fullUrl = src;
          if (src.startsWith('//')) {
            fullUrl = 'https:' + src;
          } else if (src.startsWith('/')) {
            const urlObj = new URL(url);
            fullUrl = urlObj.origin + src;
          }
          
          images.push({
            url: fullUrl,
            alt: alt,
            description: alt
          });
        }
      });

      const elapsedTime = Date.now() - startTime;
      
      const resultData = {
        url: url,
        title: title,
        content: content,
        images: images,
        imageCount: images.length,
        wordCount: content.split(/\s+/).length,
        crawlTime: elapsedTime,
        method: 'cheerio',
        timestamp: Date.now()
      };

      // Cache the result for 1 hour
      const cacheKey = cache.generateCacheKey(url, { type: 'crawl' });
      cache.set(cacheKey, resultData, 3600);
      
      return res.json({
        success: true,
        data: resultData
      });
    }

  } catch (error) {
    const elapsedTime = Date.now() - startTime;
    console.error('❌ Error crawling content:', error);
    
    // Cleanup browser if still open
    if (browser) {
      try {
        await browser.close();
      } catch (err) {
        console.error('Error closing browser:', err.message);
      }
    }
    
    // Provide user-friendly error messages
    let errorMessage = error.message;
    let statusCode = 500;
    
    if (error.message.includes('timeout') || error.message.includes('Navigation timeout')) {
      errorMessage = '페이지 로딩 시간 초과. 다시 시도해주세요.';
      statusCode = 408;
    } else if (error.message.includes('net::ERR')) {
      errorMessage = '네트워크 오류. 인터넷 연결을 확인해주세요.';
      statusCode = 503;
    } else if (error.message.includes('Invalid URL')) {
      errorMessage = '올바르지 않은 URL 형식입니다.';
      statusCode = 400;
    }
    
    res.status(statusCode).json({
      success: false,
      error: errorMessage,
      details: process.env.NODE_ENV === 'development' ? error.stack : undefined,
      duration: elapsedTime
    });
  }
};

// Analyze crawled content
exports.analyzeContent = async (req, res) => {
  try {
    const { url, content, images } = req.body;

    // If content is not provided but URL is, try to crawl first
    if (!content && !url) {
      return res.status(400).json({
        success: false,
        error: 'Either URL or content is required'
      });
    }

    let analysisContent = content;
    let analysisImages = images || [];

    // If only URL is provided, crawl it first
    if (!content && url) {
      try {
        const crawlResponse = await axios.get(url, {
          headers: {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
          }
        });

        const $ = cheerio.load(crawlResponse.data);
        
        // Extract paragraphs
        const paragraphs = [];
        $('p').each((i, elem) => {
          const text = $(elem).text().trim();
          if (text.length > 20) {
            paragraphs.push(text);
          }
        });

        analysisContent = paragraphs.join('\n\n');
      } catch (crawlError) {
        return res.status(400).json({
          success: false,
          error: 'Failed to crawl URL. Please provide content directly or check the URL.'
        });
      }
    }

    if (!analysisContent || analysisContent.trim().length === 0) {
      return res.status(400).json({
        success: false,
        error: 'No content found to analyze'
      });
    }

    // Extract keywords (simple implementation)
    const words = analysisContent.toLowerCase().split(/\s+/);
    const wordCount = {};
    const stopWords = ['은', '는', '이', '가', '을', '를', '의', '에', '과', '와', '으로', '로'];
    
    words.forEach(word => {
      if (word.length > 2 && !stopWords.includes(word)) {
        wordCount[word] = (wordCount[word] || 0) + 1;
      }
    });

    const keywords = Object.entries(wordCount)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 10)
      .map(([word]) => word);

    // Detect product name (heuristic)
    const sentences = analysisContent.split(/[.!?]/);
    let productName = '';
    for (const sentence of sentences) {
      if (sentence.includes('제품') || sentence.includes('상품') || sentence.includes('추천')) {
        productName = sentence.trim().slice(0, 100);
        break;
      }
    }

    // Suggest category based on keywords
    const categoryKeywords = {
      food: ['음식', '요리', '맛', '레시피', '식당'],
      tech: ['기술', '가전', '노트북', '스마트폰', 'IT'],
      beauty: ['화장', '뷰티', '피부', '메이크업', '스킨케어'],
      home: ['가구', '인테리어', '소파', '침대', '수납'],
      fitness: ['운동', '헬스', '다이어트', '요가', '피트니스'],
      travel: ['여행', '관광', '여행지', '숙소', '비행'],
      pets: ['반려동물', '강아지', '고양이', '펫', '애완'],
      education: ['교육', '학습', '공부', '강의', '수업'],
      business: ['비즈니스', '업무', '사무', '회사', '경영']
    };

    let suggestedCategory = 'general';
    let maxScore = 0;

    for (const [category, catKeywords] of Object.entries(categoryKeywords)) {
      let score = 0;
      catKeywords.forEach(keyword => {
        if (analysisContent.includes(keyword)) score++;
      });
      if (score > maxScore) {
        maxScore = score;
        suggestedCategory = category;
      }
    }

    res.json({
      success: true,
      data: {
        keywords: keywords,
        productName: productName,
        suggestedCategory: suggestedCategory,
        contentLength: analysisContent.length,
        imageCount: analysisImages.length
      }
    });

  } catch (error) {
    console.error('Error analyzing content:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// AI-Enhanced Script Generation Helper
function generateEnhancedScript(content, keywords = [], productName = '', category = 'general', sceneCount = 5) {
  const scenes = [];
  
  // Analyze content to extract key points
  const sentences = content.split(/[.!?]/).filter(s => s.trim().length > 10);
  const paragraphs = content.split(/\n+/).filter(p => p.trim().length > 50);
  
  // Extract main features and benefits
  const features = [];
  const benefits = [];
  
  sentences.forEach(sentence => {
    const lower = sentence.toLowerCase();
    if (lower.includes('특징') || lower.includes('기능') || lower.includes('스펙')) {
      features.push(sentence.trim());
    }
    if (lower.includes('효과') || lower.includes('장점') || lower.includes('좋은') || lower.includes('편리')) {
      benefits.push(sentence.trim());
    }
  });
  
  // Generate intro variations based on category
  const introTemplates = {
    food: [
      `오늘은 ${productName || '맛있는 음식'}을 소개해드릴게요!`,
      `여러분! ${productName || '이 특별한 메뉴'}를 꼭 드셔보세요!`,
      `입맛 돋우는 ${productName || '맛집'}을 발견했어요!`
    ],
    tech: [
      `최신 기술! ${productName || '이 제품'}을 소개합니다!`,
      `혁신적인 ${productName || '가전제품'}을 만나보세요!`,
      `테크 리뷰! ${productName || '이 제품'}은 어떨까요?`
    ],
    beauty: [
      `뷰티 추천! ${productName || '이 제품'}을 소개합니다!`,
      `피부가 달라지는 ${productName || '뷰티 아이템'}!`,
      `메이크업 필수템! ${productName || '이것'} 써보셨나요?`
    ],
    home: [
      `인테리어 꿀팁! ${productName || '이 아이템'} 추천드려요!`,
      `집꾸 필수템 ${productName || '이것'} 소개합니다!`,
      `공간 활용의 정석! ${productName || '이 제품'}을 만나보세요!`
    ],
    general: [
      `오늘은 ${productName || '특별한 제품'}을 소개해드릴게요!`,
      `여러분께 추천하고 싶은 ${productName || '제품'}이 있어요!`,
      `놓치면 후회할 ${productName || '이것'}! 지금 확인하세요!`
    ]
  };
  
  // Scene 1: Enhanced Intro
  const categoryIntros = introTemplates[category] || introTemplates.general;
  const introScript = categoryIntros[Math.floor(Math.random() * categoryIntros.length)];
  
  scenes.push({
    id: uuidv4(),
    order: 1,
    type: 'intro',
    title: '인트로',
    script: introScript,
    duration: 5,
    imagePrompt: 'energetic welcome with bright smile, friendly greeting gesture',
    visualStyle: 'bright and welcoming'
  });
  
  // Scene 2: Hook (문제 제기 또는 호기심 유발)
  let hookScript = '';
  if (keywords.length > 0) {
    const keyword = keywords[0];
    hookScript = `${keyword}에 대해 고민하신 적 있으신가요? 오늘 완벽한 솔루션을 알려드릴게요!`;
  } else {
    hookScript = sentences[0]?.trim().slice(0, 80) || '이런 경험 있으신가요?';
  }
  
  scenes.push({
    id: uuidv4(),
    order: 2,
    type: 'hook',
    title: '문제 제기',
    script: hookScript,
    duration: 5,
    imagePrompt: 'thoughtful expression, pointing to problem, questioning gesture',
    visualStyle: 'engaging and relatable'
  });
  
  // Scenes 3-N: Main Features (improved extraction)
  const mainScenes = Math.min(sceneCount - 3, Math.max(features.length, benefits.length, 3));
  
  for (let i = 0; i < mainScenes; i++) {
    let sceneScript = '';
    let sceneTitle = `특징 ${i + 1}`;
    let imagePrompt = 'demonstrating features enthusiastically';
    
    if (i < features.length) {
      sceneScript = features[i].slice(0, 120);
      sceneTitle = `핵심 기능 ${i + 1}`;
      imagePrompt = 'pointing to key feature, excited explanation';
    } else if (i - features.length < benefits.length) {
      sceneScript = benefits[i - features.length].slice(0, 120);
      sceneTitle = `장점 ${i - features.length + 1}`;
      imagePrompt = 'showing benefits with thumbs up, satisfied expression';
    } else if (i < sentences.length) {
      sceneScript = sentences[i].trim().slice(0, 120);
      imagePrompt = 'energetic presentation, showcasing product';
    } else {
      // Fallback to paragraphs
      const para = paragraphs[i % paragraphs.length];
      sceneScript = para.slice(0, 120);
    }
    
    // Clean up script
    sceneScript = sceneScript.replace(/\s+/g, ' ').trim();
    
    scenes.push({
      id: uuidv4(),
      order: i + 3,
      type: 'content',
      title: sceneTitle,
      script: sceneScript,
      duration: 6,
      imagePrompt: imagePrompt,
      visualStyle: 'informative and dynamic'
    });
  }
  
  // Last Scene: Enhanced CTA
  const ctaTemplates = [
    '좋아요와 구독 잊지 마세요! 더 많은 정보는 링크를 확인해주세요!',
    '영상이 도움되셨다면 좋아요! 구독은 큰 힘이 됩니다!',
    '궁금한 점은 댓글로! 좋아요와 구독 부탁드려요!',
    '더 많은 리뷰가 궁금하시다면 구독 필수! 알림 설정도 잊지 마세요!'
  ];
  
  scenes.push({
    id: uuidv4(),
    order: sceneCount,
    type: 'cta',
    title: '마무리',
    script: ctaTemplates[Math.floor(Math.random() * ctaTemplates.length)],
    duration: 5,
    imagePrompt: 'enthusiastic thumbs up, waving goodbye, call to action',
    visualStyle: 'warm and encouraging'
  });
  
  return scenes;
}

// Generate script from crawled content
exports.generateScript = async (req, res) => {
  try {
    const { title, content, keywords = [], productName = '', category = 'general', sceneCount = 5, aiEnhanced = true } = req.body;

    if (!content) {
      return res.status(400).json({
        success: false,
        error: 'Content is required'
      });
    }

    let scenes = [];
    
    if (aiEnhanced) {
      // Use AI-enhanced script generation
      scenes = generateEnhancedScript(content, keywords, productName, category, sceneCount);
    } else {
      // Fallback to simple script generation
      const sentences = content.split(/[.!?]/).filter(s => s.trim().length > 10);
      
      scenes.push({
        id: uuidv4(),
        order: 1,
        type: 'intro',
        title: '인트로',
        script: `안녕하세요! 오늘은 ${productName || '특별한 제품'}을 소개해드릴게요!`,
        duration: 5,
        imagePrompt: 'jumping excitedly with joy, welcoming gesture'
      });
      
      const mainScenes = Math.min(sceneCount - 2, sentences.length);
      for (let i = 0; i < mainScenes; i++) {
        const sentence = sentences[i].trim();
        if (sentence.length > 0) {
          scenes.push({
            id: uuidv4(),
            order: i + 2,
            type: 'content',
            title: `특징 ${i + 1}`,
            script: sentence.slice(0, 100),
            duration: 6,
            imagePrompt: 'demonstrating features enthusiastically, showing product'
          });
        }
      }
      
      scenes.push({
        id: uuidv4(),
        order: sceneCount,
        type: 'cta',
        title: '마무리',
        script: '좋아요와 구독 잊지 마세요! 더 많은 정보는 링크를 확인해주세요!',
        duration: 5,
        imagePrompt: 'giving thumbs up with big smile, call to action'
      });
    }

    res.json({
      success: true,
      data: {
        title: title,
        scenes: scenes,
        totalDuration: scenes.reduce((sum, s) => sum + s.duration, 0),
        sceneCount: scenes.length
      }
    });

  } catch (error) {
    console.error('Error generating script:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// Extract and download images
exports.extractImages = async (req, res) => {
  try {
    const { images } = req.body;

    if (!images || images.length === 0) {
      return res.status(400).json({
        success: false,
        error: 'Images array is required'
      });
    }

    const downloadedImages = [];
    const imagesDir = path.join(__dirname, '../uploads/crawled-images');
    await fs.mkdir(imagesDir, { recursive: true });

    // Download each image
    for (let i = 0; i < Math.min(images.length, 10); i++) {
      try {
        const image = images[i];
        const response = await axios.get(image.url, {
          responseType: 'arraybuffer',
          timeout: 10000
        });

        const ext = path.extname(image.url).split('?')[0] || '.jpg';
        const filename = `crawled_${Date.now()}_${i}${ext}`;
        const filepath = path.join(imagesDir, filename);

        await fs.writeFile(filepath, response.data);

        downloadedImages.push({
          original: image.url,
          local: `/uploads/crawled-images/${filename}`,
          alt: image.alt,
          description: image.description
        });
      } catch (error) {
        console.error(`Error downloading image ${i}:`, error.message);
      }
    }

    res.json({
      success: true,
      data: {
        images: downloadedImages,
        count: downloadedImages.length
      }
    });

  } catch (error) {
    console.error('Error extracting images:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

module.exports = exports;
