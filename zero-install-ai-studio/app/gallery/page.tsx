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
}

export default function GalleryPage() {
  const [items, setItems] = useState<GalleryItem[]>([])
  const [filter, setFilter] = useState<'all' | 'image' | 'video'>('all')
  const [selectedItem, setSelectedItem] = useState<GalleryItem | null>(null)

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

  const clearAll = () => {
    if (confirm('모든 항목을 삭제하시겠습니까?')) {
      setItems([])
      localStorage.removeItem('ai-studio-gallery')
    }
  }

  const filteredItems = items.filter(item => 
    filter === 'all' || item.type === filter
  )

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

        {/* Filters */}
        <div className="mb-6 flex justify-between items-center">
          <div className="flex gap-2">
            <button
              onClick={() => setFilter('all')}
              className={`px-4 py-2 rounded-lg font-semibold transition-colors ${
                filter === 'all'
                  ? 'bg-purple-500 text-white'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              전체
            </button>
            <button
              onClick={() => setFilter('image')}
              className={`px-4 py-2 rounded-lg font-semibold transition-colors ${
                filter === 'image'
                  ? 'bg-purple-500 text-white'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              이미지
            </button>
            <button
              onClick={() => setFilter('video')}
              className={`px-4 py-2 rounded-lg font-semibold transition-colors ${
                filter === 'video'
                  ? 'bg-purple-500 text-white'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              비디오
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
                  
                  {/* Hover Overlay */}
                  <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <div className="text-white text-4xl">👁️</div>
                  </div>
                </div>

                {/* Info */}
                <div className="p-3">
                  <div className="text-white text-sm font-semibold mb-1 truncate">
                    {item.prompt}
                  </div>
                  <div className="text-gray-400 text-xs">
                    {item.createdAt.toLocaleDateString()}
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
              <div className="flex gap-3">
                <a
                  href={selectedItem.url}
                  download
                  className="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg text-center transition-colors"
                >
                  📥 다운로드
                </a>
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
