import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Link, Globe, Image, FileText, Loader, CheckCircle, AlertCircle } from 'lucide-react';
import { useStore } from '../store/useStore';
import api from '../api/client';

export default function CrawlerPage() {
  const [url, setUrl] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [selectedImages, setSelectedImages] = useState([]);
  
  const { setCrawledData } = useStore();

  const handleCrawl = async () => {
    if (!url.trim()) {
      setError('URL을 입력해주세요');
      return;
    }

    setLoading(true);
    setError('');

    try {
      const response = await api.post('/api/crawler/fetch', { url });
      const apiData = response.data;

      // API 응답 구조: { success: true, data: { title, content, images, url } }
      if (apiData.success && apiData.data) {
        setCrawledData({
          title: apiData.data.title || '',
          content: apiData.data.content || '',
          images: apiData.data.images || [],
          url: apiData.data.url || url
        });
        // 모든 이미지를 기본 선택
        setSelectedImages(apiData.data.images?.map((_, idx) => idx) || []);
        setError('');
      } else {
        setError('크롤링 데이터를 가져오는데 실패했습니다');
      }
    } catch (err) {
      console.error('Crawl error:', err);
      setError(err.response?.data?.error || '크롤링 중 오류가 발생했습니다');
    } finally {
      setLoading(false);
    }
  };

  const { crawledData } = useStore();

  const toggleImageSelection = (index) => {
    setSelectedImages(prev => {
      if (prev.includes(index)) {
        return prev.filter(i => i !== index);
      } else {
        return [...prev, index];
      }
    });
  };

  const selectAllImages = () => {
    setSelectedImages(crawledData.images.map((_, idx) => idx));
  };

  const deselectAllImages = () => {
    setSelectedImages([]);
  };

  const handleProceedToScript = () => {
    // 선택된 이미지만 저장
    const selectedImageData = crawledData.images.filter((_, idx) => 
      selectedImages.includes(idx)
    );
    
    setCrawledData({
      ...crawledData,
      images: selectedImageData
    });
    
    // 스크립트 페이지로 이동
    window.location.href = '/script';
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl p-8 text-white"
      >
        <div className="flex items-center gap-3 mb-2">
          <Globe className="w-8 h-8" />
          <h1 className="text-3xl font-bold">블로그/기사 크롤링</h1>
        </div>
        <p className="text-primary-100">
          네이버 블로그, 티스토리, 워드프레스 등 다양한 플랫폼의 콘텐츠를 가져옵니다
        </p>
      </motion.div>

      {/* URL Input */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <label className="block text-sm font-medium text-gray-700 mb-2">
          블로그/기사 URL
        </label>
        <div className="flex gap-3">
          <div className="flex-1 relative">
            <Link className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              type="url"
              value={url}
              onChange={(e) => setUrl(e.target.value)}
              onKeyPress={(e) => e.key === 'Enter' && handleCrawl()}
              placeholder="https://blog.naver.com/example/123456789"
              className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              disabled={loading}
            />
          </div>
          <button
            onClick={handleCrawl}
            disabled={loading}
            className="px-6 py-3 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
          >
            {loading ? (
              <>
                <Loader className="w-5 h-5 animate-spin" />
                크롤링 중...
              </>
            ) : (
              <>
                <Globe className="w-5 h-5" />
                크롤링 시작
              </>
            )}
          </button>
        </div>

        {error && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3"
          >
            <AlertCircle className="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
            <p className="text-red-700">{error}</p>
          </motion.div>
        )}
      </motion.div>

      {/* Crawled Data Preview */}
      {crawledData && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
          className="bg-white rounded-xl shadow-lg p-6 space-y-6"
        >
          {/* Success Message */}
          <div className="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
            <CheckCircle className="w-6 h-6 text-green-500" />
            <div>
              <p className="font-medium text-green-900">크롤링 완료!</p>
              <p className="text-sm text-green-700">콘텐츠를 성공적으로 가져왔습니다</p>
            </div>
          </div>

          {/* Title */}
          <div>
            <div className="flex items-center gap-2 mb-3">
              <FileText className="w-5 h-5 text-primary-500" />
              <h3 className="font-semibold text-gray-900">제목</h3>
            </div>
            <p className="text-lg text-gray-800 font-medium bg-gray-50 p-4 rounded-lg">
              {crawledData.title}
            </p>
          </div>

          {/* Content Preview */}
          <div>
            <div className="flex items-center gap-2 mb-3">
              <FileText className="w-5 h-5 text-primary-500" />
              <h3 className="font-semibold text-gray-900">본문 미리보기</h3>
            </div>
            <div className="bg-gray-50 p-4 rounded-lg max-h-48 overflow-y-auto">
              <p className="text-gray-700 whitespace-pre-line">
                {crawledData.content ? (
                  <>
                    {crawledData.content.slice(0, 500)}
                    {crawledData.content.length > 500 && '...'}
                  </>
                ) : (
                  <span className="text-gray-400">본문 내용이 없습니다</span>
                )}
              </p>
            </div>
            <p className="text-sm text-gray-500 mt-2">
              총 {(crawledData.content || '').length.toLocaleString()}자
            </p>
          </div>

          {/* Images */}
          {crawledData.images && crawledData.images.length > 0 && (
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                  <Image className="w-5 h-5 text-primary-500" />
                  <h3 className="font-semibold text-gray-900">
                    이미지 선택 ({selectedImages.length}/{crawledData.images.length}개)
                  </h3>
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={selectAllImages}
                    className="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded hover:bg-primary-200 transition-colors"
                  >
                    전체 선택
                  </button>
                  <button
                    onClick={deselectAllImages}
                    className="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors"
                  >
                    전체 해제
                  </button>
                </div>
              </div>
              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {(crawledData.images || []).map((img, idx) => {
                  // proxyUrl 사용 (리퍼러 문제 해결)
                  const imgUrl = typeof img === 'string' ? img : (img?.proxyUrl || img?.url);
                  const isSelected = selectedImages.includes(idx);
                  
                  return (
                    <div 
                      key={idx} 
                      className="relative aspect-square bg-gray-100 rounded-lg overflow-hidden group cursor-pointer"
                      onClick={() => toggleImageSelection(idx)}
                    >
                      <img
                        src={imgUrl}
                        alt={img?.alt || `Image ${idx + 1}`}
                        className={`w-full h-full object-cover transition-all duration-300 ${
                          isSelected ? 'scale-100' : 'scale-95 opacity-50'
                        } group-hover:scale-110`}
                        onError={(e) => {
                          e.target.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect fill="%23e5e7eb" width="100" height="100"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%239ca3af"%3E이미지%3C/text%3E%3C/svg%3E';
                        }}
                      />
                      {/* 선택 오버레이 */}
                      <div className={`absolute inset-0 transition-all duration-300 ${
                        isSelected 
                          ? 'bg-primary-500 bg-opacity-0 hover:bg-opacity-10' 
                          : 'bg-black bg-opacity-40 hover:bg-opacity-30'
                      }`} />
                      
                      {/* 체크박스 */}
                      <div className="absolute top-2 right-2 z-10">
                        <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all ${
                          isSelected 
                            ? 'bg-primary-500 border-primary-500' 
                            : 'bg-white bg-opacity-80 border-gray-300'
                        }`}>
                          {isSelected && (
                            <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                            </svg>
                          )}
                        </div>
                      </div>
                      
                      {/* 이미지 번호 */}
                      <div className="absolute bottom-2 left-2 px-2 py-1 bg-black bg-opacity-60 text-white text-xs rounded">
                        #{idx + 1}
                      </div>
                    </div>
                  );
                })}
              </div>
              <p className="text-sm text-gray-500 mt-3">
                💡 이미지를 클릭하여 선택/해제할 수 있습니다
              </p>
            </div>
          )}

          {/* Next Step */}
          <div className="pt-4 border-t">
            <div className="flex items-center justify-between">
              <div className="text-sm text-gray-600">
                {selectedImages.length > 0 ? (
                  <span className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-500" />
                    {selectedImages.length}개의 이미지가 선택되었습니다
                  </span>
                ) : (
                  <span className="flex items-center gap-2 text-amber-600">
                    <AlertCircle className="w-4 h-4" />
                    최소 1개 이상의 이미지를 선택해주세요
                  </span>
                )}
              </div>
              <button
                onClick={handleProceedToScript}
                disabled={selectedImages.length === 0}
                className="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
              >
                다음 단계: 스크립트 생성
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </motion.div>
      )}
    </div>
  );
}
