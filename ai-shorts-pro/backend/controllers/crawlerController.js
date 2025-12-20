const axios = require('axios');
const cheerio = require('cheerio');
const path = require('path');
const fs = require('fs').promises;
const { v4: uuidv4 } = require('uuid');

// Crawl blog/article content
exports.crawlContent = async (req, res) => {
  try {
    const { url } = req.body;

    if (!url) {
      return res.status(400).json({
        success: false,
        error: 'URL is required'
      });
    }

    // Fetch webpage
    const response = await axios.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      }
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

    res.json({
      success: true,
      data: {
        url: url,
        title: title,
        content: content,
        images: images,
        imageCount: images.length,
        wordCount: content.split(/\s+/).length
      }
    });

  } catch (error) {
    console.error('Error crawling content:', error);
    res.status(500).json({
      success: false,
      error: error.message
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

// Generate script from crawled content
exports.generateScript = async (req, res) => {
  try {
    const { title, content, keywords, productName, sceneCount = 5 } = req.body;

    if (!content) {
      return res.status(400).json({
        success: false,
        error: 'Content is required'
      });
    }

    // Generate script scenes
    const scenes = [];
    const sentences = content.split(/[.!?]/).filter(s => s.trim().length > 10);

    // Scene 1: Intro
    scenes.push({
      id: uuidv4(),
      order: 1,
      type: 'intro',
      title: '인트로',
      script: `안녕하세요! 오늘은 ${productName || '특별한 제품'}을 소개해드릴게요!`,
      duration: 5,
      imagePrompt: 'jumping excitedly with joy, welcoming gesture'
    });

    // Scene 2-4: Main content
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

    // Last Scene: CTA
    scenes.push({
      id: uuidv4(),
      order: sceneCount,
      type: 'cta',
      title: '마무리',
      script: '좋아요와 구독 잊지 마세요! 더 많은 정보는 링크를 확인해주세요!',
      duration: 5,
      imagePrompt: 'giving thumbs up with big smile, call to action'
    });

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
