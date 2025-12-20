const AIService = require('../services/aiService');

/**
 * Generate YouTube metadata (title, description, tags)
 */
exports.generateMetadata = async (req, res) => {
  try {
    const { projectId, content, keywords = [] } = req.body;

    if (!content) {
      return res.status(400).json({
        success: false,
        error: 'Content is required'
      });
    }

    // Extract key information from content
    const analysis = await AIService.analyzeContent(content);
    
    // Generate title (max 100 characters for YouTube)
    const title = generateTitle(content, analysis.keywords);
    
    // Generate description (max 5000 characters for YouTube)
    const description = generateDescription(content, keywords);
    
    // Generate tags (max 500 characters total)
    const tags = generateTags(analysis.keywords, keywords);

    res.json({
      success: true,
      data: {
        title,
        description,
        tags,
        metadata: {
          titleLength: title.length,
          descriptionLength: description.length,
          tagsCount: tags.length
        }
      }
    });

  } catch (error) {
    console.error('Error generating YouTube metadata:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

/**
 * Generate optimized title for YouTube Shorts
 */
function generateTitle(content, keywords) {
  // Extract first sentence or key phrase
  const firstSentence = content.split(/[.!?]/)[0];
  
  // Take first keyword for SEO
  const mainKeyword = keywords[0] || '';
  
  // Create engaging title
  let title = `${firstSentence.slice(0, 60)} #Shorts`;
  
  // Ensure it's not too long
  if (title.length > 95) {
    title = title.slice(0, 92) + '...';
  }
  
  return title;
}

/**
 * Generate comprehensive description for YouTube
 */
function generateDescription(content, keywords) {
  const sections = [];
  
  // Main description
  sections.push('🎬 이 영상에서는...');
  sections.push(content.slice(0, 500));
  sections.push('');
  
  // Keywords section
  if (keywords.length > 0) {
    sections.push('🔑 주요 키워드:');
    sections.push(keywords.join(', '));
    sections.push('');
  }
  
  // Timestamps (if applicable)
  sections.push('⏰ 타임스탬프:');
  sections.push('00:00 인트로');
  sections.push('00:05 메인 콘텐츠');
  sections.push('00:25 마무리');
  sections.push('');
  
  // Social media & CTA
  sections.push('👍 좋아요와 구독 부탁드립니다!');
  sections.push('💬 댓글로 의견을 남겨주세요!');
  sections.push('🔔 알림 설정으로 새 영상을 놓치지 마세요!');
  sections.push('');
  
  // Hashtags
  sections.push('─────────────────────────');
  sections.push('📌 해시태그:');
  sections.push('#Shorts #쇼츠 #숏폼콘텐츠 #AI #자동화');
  if (keywords.length > 0) {
    sections.push(keywords.map(k => `#${k.replace(/\s+/g, '')}`).join(' '));
  }
  sections.push('');
  
  // Disclaimer
  sections.push('─────────────────────────');
  sections.push('ℹ️ 이 영상은 AI로 자동 생성되었습니다.');
  sections.push('📧 문의: info@example.com');
  sections.push('');
  
  // Ensure total length is under 5000 characters
  let description = sections.join('\n');
  if (description.length > 4900) {
    description = description.slice(0, 4900) + '...';
  }
  
  return description;
}

/**
 * Generate optimized tags for YouTube
 */
function generateTags(extractedKeywords, customKeywords) {
  const tags = new Set();
  
  // Default tags
  tags.add('Shorts');
  tags.add('쇼츠');
  tags.add('숏폼콘텐츠');
  tags.add('AI');
  tags.add('자동화');
  tags.add('리뷰');
  tags.add('추천');
  
  // Add extracted keywords
  extractedKeywords.slice(0, 10).forEach(keyword => {
    tags.add(keyword);
  });
  
  // Add custom keywords
  customKeywords.forEach(keyword => {
    tags.add(keyword);
  });
  
  // Convert to array and limit
  const tagArray = Array.from(tags).slice(0, 30);
  
  // Check total length (YouTube limit is 500 characters)
  const totalLength = tagArray.join(',').length;
  if (totalLength > 490) {
    // Remove tags until under limit
    while (tagArray.join(',').length > 490 && tagArray.length > 5) {
      tagArray.pop();
    }
  }
  
  return tagArray;
}

/**
 * Generate thumbnail suggestions
 */
exports.generateThumbnailSuggestions = async (req, res) => {
  try {
    const { projectId, scenes } = req.body;

    if (!scenes || scenes.length === 0) {
      return res.status(400).json({
        success: false,
        error: 'Scenes are required'
      });
    }

    // Analyze scenes to find best thumbnail moments
    const suggestions = scenes.map((scene, index) => {
      return {
        sceneIndex: index,
        timestamp: index * 6, // Assuming 6 seconds per scene
        reason: scene.subtitle || scene.narration || `Scene ${index + 1}`,
        score: calculateThumbnailScore(scene)
      };
    });

    // Sort by score (highest first)
    suggestions.sort((a, b) => b.score - a.score);

    res.json({
      success: true,
      data: {
        suggestions: suggestions.slice(0, 3), // Top 3 suggestions
        bestThumbnail: suggestions[0]
      }
    });

  } catch (error) {
    console.error('Error generating thumbnail suggestions:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

/**
 * Calculate thumbnail score based on scene characteristics
 */
function calculateThumbnailScore(scene) {
  let score = 0;
  
  // Scenes with expressive text score higher
  if (scene.subtitle) {
    score += scene.subtitle.length > 0 ? 10 : 0;
    score += scene.subtitle.includes('!') ? 5 : 0;
    score += scene.subtitle.includes('?') ? 3 : 0;
  }
  
  // Action keywords
  const actionKeywords = ['추천', '최고', '놀라운', '대박', '신기한'];
  actionKeywords.forEach(keyword => {
    if (scene.narration?.includes(keyword)) {
      score += 5;
    }
  });
  
  // First and last scenes are often good for thumbnails
  if (scene.id === 1) score += 5;
  
  return score;
}

/**
 * Validate YouTube requirements
 */
exports.validateMetadata = async (req, res) => {
  try {
    const { title, description, tags } = req.body;

    const validation = {
      title: validateTitle(title),
      description: validateDescription(description),
      tags: validateTags(tags)
    };

    const isValid = validation.title.valid && validation.description.valid && validation.tags.valid;

    res.json({
      success: true,
      data: {
        isValid,
        validation
      }
    });

  } catch (error) {
    console.error('Error validating metadata:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

function validateTitle(title) {
  const maxLength = 100;
  const minLength = 10;
  
  const issues = [];
  if (!title) {
    issues.push('Title is required');
  } else {
    if (title.length > maxLength) {
      issues.push(`Title is too long (${title.length}/${maxLength})`);
    }
    if (title.length < minLength) {
      issues.push(`Title is too short (${title.length}/${minLength})`);
    }
  }
  
  return {
    valid: issues.length === 0,
    length: title?.length || 0,
    maxLength,
    issues
  };
}

function validateDescription(description) {
  const maxLength = 5000;
  const minLength = 50;
  
  const issues = [];
  if (!description) {
    issues.push('Description is required');
  } else {
    if (description.length > maxLength) {
      issues.push(`Description is too long (${description.length}/${maxLength})`);
    }
    if (description.length < minLength) {
      issues.push(`Description is too short (${description.length}/${minLength})`);
    }
  }
  
  return {
    valid: issues.length === 0,
    length: description?.length || 0,
    maxLength,
    issues
  };
}

function validateTags(tags) {
  const maxTags = 30;
  const maxTotalLength = 500;
  
  const issues = [];
  if (!tags || !Array.isArray(tags)) {
    issues.push('Tags must be an array');
  } else {
    if (tags.length > maxTags) {
      issues.push(`Too many tags (${tags.length}/${maxTags})`);
    }
    
    const totalLength = tags.join(',').length;
    if (totalLength > maxTotalLength) {
      issues.push(`Tags total length is too long (${totalLength}/${maxTotalLength})`);
    }
  }
  
  return {
    valid: issues.length === 0,
    count: tags?.length || 0,
    maxTags,
    totalLength: tags ? tags.join(',').length : 0,
    maxTotalLength,
    issues
  };
}

module.exports = exports;
