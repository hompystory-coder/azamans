import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:5000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Characters API
export const charactersAPI = {
  getAll: () => api.get('/characters'),
  getById: (id) => api.get(`/characters/${id}`),
  getByCategory: (category) => api.get(`/characters/category/${category}`)
};

// Voices API
export const voicesAPI = {
  getAll: () => api.get('/voices'),
  getById: (id) => api.get(`/voices/${id}`),
  generateSample: (voiceId) => api.post('/voices/sample', { voiceId }),
  testVoice: (voiceId, text) => api.post('/voices/test', { voiceId, text })
};

// Projects API
export const projectsAPI = {
  getAll: () => api.get('/projects'),
  getById: (id) => api.get(`/projects/${id}`),
  create: (data) => api.post('/projects', data),
  update: (id, data) => api.put(`/projects/${id}`, data),
  delete: (id) => api.delete(`/projects/${id}`),
  saveSettings: (id, settings) => api.post(`/projects/${id}/settings`, { settings }),
  getSettings: (id) => api.get(`/projects/${id}/settings`),
  generate: (id, data) => api.post(`/projects/${id}/generate`, data),
  getStatus: (id) => api.get(`/projects/${id}/status`)
};

// Crawler API
export const crawlerAPI = {
  crawl: (url) => api.post('/crawler/crawl', { url }),
  analyze: (urlOrContent, images) => {
    // Support both URL-only and content-based analysis
    if (typeof urlOrContent === 'string' && urlOrContent.startsWith('http')) {
      return api.post('/crawler/analyze', { url: urlOrContent, images });
    }
    return api.post('/crawler/analyze', { content: urlOrContent, images });
  },
  generateScript: (data) => api.post('/crawler/generate-script', data),
  extractImages: (images) => api.post('/crawler/extract-images', { images })
};

// Generation API
export const generationAPI = {
  start: (data) => api.post('/generation/start', data),
  getProgress: (jobId) => api.get(`/generation/${jobId}/progress`),
  cancel: (jobId) => api.post(`/generation/${jobId}/cancel`),
  download: (jobId) => api.get(`/generation/${jobId}/download`, { responseType: 'blob' }),
  generateScene: (data) => api.post('/generation/scene', data),
  generateCharacterImage: (data) => api.post('/generation/character-image', data),
  generateVoice: (data) => api.post('/generation/voice', data),
  generateBGM: (data) => api.post('/generation/bgm', data),
  renderVideo: (data) => api.post('/generation/render', data)
};

// Assets API
export const assetsAPI = {
  uploadMusic: (file) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'music');
    return api.post('/assets/upload/music', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },
  uploadImage: (file) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'image');
    return api.post('/assets/upload/image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },
  uploadFont: (file) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'font');
    return api.post('/assets/upload/font', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },
  list: (type) => api.get('/assets/list', { params: { type } }),
  getById: (id) => api.get(`/assets/${id}`),
  delete: (id) => api.delete(`/assets/${id}`)
};

// YouTube API
export const youtubeAPI = {
  generateMetadata: (data) => api.post('/youtube/generate', data),
  generateThumbnailSuggestions: (data) => api.post('/youtube/thumbnail-suggestions', data),
  validateMetadata: (data) => api.post('/youtube/validate', data)
};

export default api;
