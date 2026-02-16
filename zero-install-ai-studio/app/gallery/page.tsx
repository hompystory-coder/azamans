'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'

interface GalleryItem {
  id: string
  type: 'image' | 'video'
  url: string
  thumbnail?: string
  prompt: string
  preset?: string
  createdAt: Date
  duration?: number
  size?: number
  favorite?: boolean
  tags?: string[]
  views?: number
  likes?: number
}

export default function GalleryPage() {
  const [items, setItems] = useState<GalleryItem[]>([])
  const [filter, setFilter] = useState<'all' | 'image' | 'video' | 'favorite'>('all')
  const [selectedItem, setSelectedItem] = useState<GalleryItem | null>(null)
  const [searchQuery, setSearchQuery] = useState('')
  const [sortBy, setSortBy] = useState<'newest' | 'oldest' | 'popular'>('newest')
  const [showShareModal, setShowShareModal] = useState(false)

  useEffect(() => {
    loadGallery()
  }, [])

  const loadGallery = () => {
    // LocalStorage에서 히스토리 로드
    const stored = localStorage.getItem('ai-studio-gallery')
    if (stored) {
      const parsed = JSON.parse(stored)
      setItems(parsed.map((item: any) => ({
        ...item,
        createdAt: new Date(item.createdAt)
      })))
    }
  }

  const saveToGallery = (item: Omit<GalleryItem, 'id' | 'createdAt'>) => {
    const newItem: GalleryItem = {
      ...item,
      id: Date.now().toString(),
      createdAt: new Date()
    }
    
    const updated = [newItem, ...items]
    setItems(updated)
    
    localStorage.setItem('ai-studio-gallery', JSON.stringify(updated))
  }

  const deleteItem = (id: string) => {
    const updated = items.filter(item => item.id !== id)
    setItems(updated)
    localStorage.setItem('ai-studio-gallery', JSON.stringify(updated))
    setSelectedItem(null)
  }

  const toggleFavorite = (id: string) => {
    const updated = items.map(item => 
      item.id === id ? { ...item, favorite: !item.favorite } : item
    )
    setItems(updated)
    localStorage.setItem('ai-studio-gallery', JSON.stringify(updated))
  }

  const likeItem = (id: string) => {
    const updated = items.map(item => 
      item.id === id ? { ...item, likes: (item.likes || 0) + 1 } : item
    )
    setItems(updated)
    localStorage.setItem('ai-studio-gallery', JSON.stringify(updated))
  }

  const shareItem = (item: GalleryItem) => {
    if (navigator.share) {
      navigator.share({
        title: 'AI Studio - ' + item.prompt,
        text: `AI로 생성한 ${item.type === 'video' ? '비디오' : '이미지'}: ${item.prompt}`,
        url: item.url
      }).catch(() => {})
    } else {
      // Fallback: Copy to clipboard
      navigator.clipboard.writeText(item.url)
      alert('링크가 클립보드에 복사되었습니다!')
    }
  }

  const downloadItem = async (item: GalleryItem) => {
    try {
      const response = await fetch(item.url)
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `ai-studio-${item.type}-${item.id}.${item.type === 'video' ? 'mp4' : 'png'}`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      window.URL.revokeObjectURL(url)
    } catch (error) {
      console.error('Download failed:', error)
      // Fallback
      window.open(item.url, '_blank')
    }
  }

  const clearAll = () => {
    if (confirm('모든 항목을 삭제하시겠습니까?')) {
      setItems([])
      localStorage.removeItem('ai-studio-gallery')
    }
  }

  const filteredItems = items
    .filter(item => {
      // Filter by type
      if (filter === 'favorite') return item.favorite
      if (filter !== 'all' && item.type !== filter) return false
      
      // Filter by search query
      if (searchQuery) {
        const query = searchQuery.toLowerCase()
        return item.prompt.toLowerCase().includes(query) ||
               item.tags?.some(tag => tag.toLowerCase().includes(query))
      }
      
      return true
    })
    .sort((a, b) => {
      switch (sortBy) {
        case 'newest':
          return b.createdAt.getTime() - a.createdAt.getTime()
        case 'oldest':
          return a.createdAt.getTime() - b.createdAt.getTime()
        case 'popular':
          return (b.likes || 0) - (a.likes || 0)
        default:
          return 0
      }
    })

  const totalSize = items.reduce((sum, item) => sum + (item.size || 0), 0)
  const formatSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900">
      {/* Header */}
      <header className="border-b border-white/10 backdrop-blur-lg bg-black/20">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <Link href="/" className="text-2xl font-bold text-white flex items-center gap-2">
            <span className="text-3xl">🖼️</span>
            <span className="gradient-text">Gallery</span>
          </Link>
          <div className="flex items-center gap-4">
            <Link href="/auto-shorts" className="px-4 py-2 text-white hover:bg-white/10 rounded-lg transition-colors">
              쇼츠 생성
            </Link>
            <Link href="/studio" className="px-4 py-2 text-white hover:bg-white/10 rounded-lg transition-colors">
              이미지 스튜디오
            </Link>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* Stats */}
        <div className="mb-8 grid md:grid-cols-4 gap-4">
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
            <div className="text-3xl mb-2">📊</div>
            <div className="text-2xl font-bold text-white">{items.length}</div>
            <div className="text-gray-400">총 항목</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
            <div className="text-3xl mb-2">🎨</div>
            <div className="text-2xl font-bold text-white">{items.filter(i => i.type === 'image').length}</div>
            <div className="text-gray-400">이미지</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
            <div className="text-3xl mb-2">🎬</div>
            <div className="text-2xl font-bold text-white">{items.filter(i => i.type === 'video').length}</div>
            <div className="text-gray-400">비디오</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
            <div className="text-3xl mb-2">💾</div>
            <div className="text-2xl font-bold text-white">{formatSize(totalSize)}</div>
            <div className="text-gray-400">총 용량</div>
          </div>
        </div>

        {/* Search and Sort */}
        <div className="mb-6 flex flex-col md:flex-row gap-4">
          <div className="flex-1">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="🔍 검색: 프롬프트, 태그..."
              className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>
          <div className="flex gap-2">
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value as any)}
              className="px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
              <option value="newest">최신순</option>
              <option value="oldest">오래된순</option>
              <option value="popular">인기순</option>
            </select>
          </div>
        </div>

        {/* Filters */}
        <div className="mb-6 flex justify-between items-center flex-wrap gap-4">
          <div className="flex gap-2 flex-wrap">
            <button
              onClick={() => setFilter('all')}
              className={`px-4 py-2 rounded-lg font-semibold transition-colors ${
                filter === 'all'
                  ? 'bg-purple-500 text-white'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              📂 전체
            </button>
            <button
              onClick={() => setFilter('image')}
              className={`px-4 py-2 rounded-lg font-semibold transition-colors ${
                filter === 'image'
                  ? 'bg-purple-500 text-white'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              🎨 이미지
            </button>
            <button
              onClick={() => setFilter('video')}
              className={`px-4 py-2 rounded-lg font-semibold transition-colors ${
                filter === 'video'
                  ? 'bg-purple-500 text-white'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              🎬 비디오
            </button>
            <button
              onClick={() => setFilter('favorite')}
              className={`px-4 py-2 rounded-lg font-semibold transition-colors ${
                filter === 'favorite'
                  ? 'bg-purple-500 text-white'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              ⭐ 즐겨찾기
            </button>
          </div>
          
          <button
            onClick={clearAll}
            className="px-4 py-2 bg-red-500/20 text-red-300 hover:bg-red-500/30 rounded-lg transition-colors"
          >
            🗑️ 전체 삭제
          </button>
        </div>

        {/* Gallery Grid */}
        {filteredItems.length === 0 ? (
          <div className="text-center py-20">
            <div className="text-6xl mb-4">🎨</div>
            <div className="text-white text-xl mb-2">아직 생성된 항목이 없습니다</div>
            <div className="text-gray-400 mb-6">AI 스튜디오에서 이미지나 비디오를 생성해보세요</div>
            <Link
              href="/auto-shorts"
              className="inline-block px-6 py-3 bg-gradient-to-r from-purple-500 to-blue-500 text-white font-semibold rounded-lg hover:shadow-xl transition-all"
            >
              쇼츠 생성하러 가기
            </Link>
          </div>
        ) : (
          <div className="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
            {filteredItems.map((item) => (
              <div
                key={item.id}
                onClick={() => setSelectedItem(item)}
                className="group relative bg-white/10 backdrop-blur-lg rounded-xl overflow-hidden border border-white/20 cursor-pointer hover:scale-105 transition-transform"
              >
                {/* Thumbnail */}
                <div className="aspect-[9/16] bg-gray-800 relative overflow-hidden">
                  {item.type === 'image' ? (
                    <img
                      src={item.url}
                      alt={item.prompt}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <video
                      src={item.url}
                      className="w-full h-full object-cover"
                      muted
                    />
                  )}
                  
                  {/* Type Badge */}
                  <div className="absolute top-2 right-2 px-2 py-1 bg-black/70 rounded text-white text-xs font-semibold">
                    {item.type === 'image' ? '🎨 IMAGE' : '🎬 VIDEO'}
                  </div>
                  
                  {/* Favorite Button */}
                  <button
                    onClick={(e) => {
                      e.stopPropagation()
                      toggleFavorite(item.id)
                    }}
                    className="absolute top-2 left-2 p-2 bg-black/70 rounded-full text-2xl hover:scale-110 transition-transform"
                  >
                    {item.favorite ? '⭐' : '☆'}
                  </button>
                  
                  {/* Hover Overlay */}
                  <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                    <button
                      onClick={(e) => {
                        e.stopPropagation()
                        likeItem(item.id)
                      }}
                      className="text-white text-2xl hover:scale-125 transition-transform"
                    >
                      ❤️
                    </button>
                    <div className="text-white text-4xl">👁️</div>
                    <button
                      onClick={(e) => {
                        e.stopPropagation()
                        shareItem(item)
                      }}
                      className="text-white text-2xl hover:scale-125 transition-transform"
                    >
                      📤
                    </button>
                  </div>
                </div>

                {/* Info */}
                <div className="p-3">
                  <div className="text-white text-sm font-semibold mb-1 truncate">
                    {item.prompt}
                  </div>
                  <div className="flex justify-between items-center text-xs">
                    <span className="text-gray-400">{item.createdAt.toLocaleDateString()}</span>
                    <div className="flex gap-2">
                      {item.likes && item.likes > 0 && (
                        <span className="text-red-400">❤️ {item.likes}</span>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Detail Modal */}
      {selectedItem && (
        <div
          className="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
          onClick={() => setSelectedItem(null)}
        >
          <div
            className="bg-gray-900 rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-auto"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Media */}
            <div className="aspect-[9/16] max-h-[60vh] bg-black flex items-center justify-center">
              {selectedItem.type === 'image' ? (
                <img
                  src={selectedItem.url}
                  alt={selectedItem.prompt}
                  className="max-w-full max-h-full object-contain"
                />
              ) : (
                <video
                  src={selectedItem.url}
                  controls
                  className="max-w-full max-h-full"
                />
              )}
            </div>

            {/* Details */}
            <div className="p-6">
              <h2 className="text-2xl font-bold text-white mb-4">상세 정보</h2>
              
              <div className="space-y-3 mb-6">
                <div>
                  <div className="text-gray-400 text-sm">프롬프트</div>
                  <div className="text-white">{selectedItem.prompt}</div>
                </div>
                
                {selectedItem.preset && (
                  <div>
                    <div className="text-gray-400 text-sm">프리셋</div>
                    <div className="text-white">{selectedItem.preset}</div>
                  </div>
                )}
                
                <div>
                  <div className="text-gray-400 text-sm">생성 일시</div>
                  <div className="text-white">{selectedItem.createdAt.toLocaleString()}</div>
                </div>
                
                {selectedItem.duration && (
                  <div>
                    <div className="text-gray-400 text-sm">재생 시간</div>
                    <div className="text-white">{selectedItem.duration}초</div>
                  </div>
                )}
              </div>

              {/* Actions */}
              <div className="grid grid-cols-2 gap-3 mb-3">
                <button
                  onClick={() => downloadItem(selectedItem)}
                  className="py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                  <span>📥</span>
                  <span>다운로드</span>
                </button>
                <button
                  onClick={() => shareItem(selectedItem)}
                  className="py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                  <span>📤</span>
                  <span>공유</span>
                </button>
                <button
                  onClick={() => {
                    toggleFavorite(selectedItem.id)
                    setSelectedItem({...selectedItem, favorite: !selectedItem.favorite})
                  }}
                  className="py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                  <span>{selectedItem.favorite ? '⭐' : '☆'}</span>
                  <span>{selectedItem.favorite ? '즐겨찾기 해제' : '즐겨찾기'}</span>
                </button>
                <button
                  onClick={() => likeItem(selectedItem.id)}
                  className="py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                  <span>❤️</span>
                  <span>좋아요 ({selectedItem.likes || 0})</span>
                </button>
              </div>
              <div className="flex gap-3">
                <button
                  onClick={() => deleteItem(selectedItem.id)}
                  className="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-colors"
                >
                  🗑️ 삭제
                </button>
                <button
                  onClick={() => setSelectedItem(null)}
                  className="flex-1 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors"
                >
                  닫기
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

// Export saveToGallery for use in other pages
export const useGallery = () => {
  const saveToGallery = (item: Omit<GalleryItem, 'id' | 'createdAt'>) => {
    const stored = localStorage.getItem('ai-studio-gallery')
    const items = stored ? JSON.parse(stored) : []
    
    const newItem: GalleryItem = {
      ...item,
      id: Date.now().toString(),
      createdAt: new Date()
    }
    
    const updated = [newItem, ...items]
    localStorage.setItem('ai-studio-gallery', JSON.stringify(updated))
  }
  
  return { saveToGallery }
}
