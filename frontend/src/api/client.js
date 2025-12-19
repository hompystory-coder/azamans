import axios from 'axios'

// HTTPS에서도 작동하도록 상대 경로 사용
// Nginx가 /api/*를 백엔드(포트 4001)로 프록시합니다
const API_BASE_URL = import.meta.env.VITE_API_URL || ''

const client = axios.create({
  baseURL: API_BASE_URL,
  timeout: 120000, // 2분
  headers: {
    'Content-Type': 'application/json'
  }
})

// 요청 인터셉터
client.interceptors.request.use(
  (config) => {
    console.log(`📡 API Request: ${config.method.toUpperCase()} ${config.url}`)
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// 응답 인터셉터
client.interceptors.response.use(
  (response) => {
    console.log(`✅ API Response: ${response.config.url}`, response.data)
    return response
  },
  (error) => {
    console.error(`❌ API Error: ${error.config?.url}`, error.response?.data || error.message)
    return Promise.reject(error)
  }
)

// API 함수들
export const api = {
  // Health check
  health: () => client.get('/health'),

  // 설정 관리
  settings: {
    list: () => client.get('/api/settings/list'),
    save: (name, settings) => client.post('/api/settings/save', { name, settings }),
    delete: (id) => client.delete(`/api/settings/${id}`)
  },

  // 크롤링
  crawler: {
    fetch: (url) => client.post('/api/crawler/fetch', { url })
  },

  // 스크립트 생성
  script: {
    generate: (data) => client.post('/api/script/generate', data)
  },

  // 음성 생성
  voice: {
    samples: () => client.get('/api/voice/samples'),
    preview: (data) => client.post('/api/voice/preview', data),
    generate: (data) => client.post('/api/voice/generate', data)
  },

  // 비디오 생성
  video: {
    generate: (data) => client.post('/api/video/generate', data),
    status: (videoId) => client.get(`/api/video/status/${videoId}`)
  },

  // 최종 렌더링
  render: {
    final: (data) => client.post('/api/render/final', data),
    status: (renderId, apiKey) => client.get(`/api/render/status/${renderId}`, {
      params: { apiKey }
    })
  }
}

export default client
