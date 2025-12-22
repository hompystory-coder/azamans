import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Youtube, Copy, CheckCircle, Download, Share2, Sparkles, PlayCircle, Calendar, HardDrive } from 'lucide-react';
import useStore from '../store/useStore';
import client from '../api/client';

export default function PreviewPage() {
  const { finalVideo, crawledData, script } = useStore();
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [keywords, setKeywords] = useState('');
  const [copied, setCopied] = useState({ title: false, description: false, keywords: false });
  const [videoList, setVideoList] = useState([]);
  const [loadingVideos, setLoadingVideos] = useState(true);

  useEffect(() => {
    // Auto-generate YouTube upload info
    if (crawledData && script) {
      const autoTitle = generateTitle();
      const autoDescription = generateDescription();
      const autoKeywords = generateKeywords();
      
      setTitle(autoTitle);
      setDescription(autoDescription);
      setKeywords(autoKeywords);
    }

    // Load video list
    loadVideoList();
  }, [crawledData, script]);

  const loadVideoList = async () => {
    try {
      setLoadingVideos(true);
      const response = await client.get('/api/video/list');
      
      if (response.data.success) {
        setVideoList(response.data.data.videos);
        console.log(`📹 ${response.data.data.total}개의 비디오 로드됨`);
      }
    } catch (error) {
      console.error('❌ 비디오 목록 로드 실패:', error);
      setVideoList([]); // 에러 시 빈 배열로 설정
    } finally {
      setLoadingVideos(false);
    }
  };

  const generateTitle = () => {
    if (!crawledData?.title) return '';
    const title = crawledData.title;
    // Truncate to 100 chars (YouTube limit)
    return title.length > 100 ? title.slice(0, 97) + '...' : title;
  };

  const generateDescription = () => {
    if (!crawledData) return '';
    const lines = [
      crawledData.title,
      '',
      crawledData.content.slice(0, 500) + (crawledData.content.length > 500 ? '...' : ''),
      '',
      '🎬 AI로 자동 생성된 쇼츠입니다',
      `📌 원본 URL: ${crawledData.url}`,
      '',
      '#Shorts #유튜브쇼츠 #AI생성'
    ];
    return lines.join('\n');
  };

  const generateKeywords = () => {
    const keywords = [
      '쇼츠',
      'shorts',
      'AI',
      '자동생성',
      '유튜브쇼츠'
    ];
    
    // Add keywords from title
    if (crawledData?.title) {
      const titleWords = crawledData.title
        .split(' ')
        .filter(word => word.length > 2)
        .slice(0, 5);
      keywords.push(...titleWords);
    }
    
    return keywords.join(', ');
  };

  const copyToClipboard = (text, field) => {
    navigator.clipboard.writeText(text);
    setCopied({ ...copied, [field]: true });
    setTimeout(() => {
      setCopied({ ...copied, [field]: false });
    }, 2000);
  };

  const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return '방금 전';
    if (diffMins < 60) return `${diffMins}분 전`;
    if (diffHours < 24) return `${diffHours}시간 전`;
    if (diffDays < 7) return `${diffDays}일 전`;
    
    return date.toLocaleDateString('ko-KR', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-gradient-to-r from-red-600 to-red-500 rounded-2xl p-8 text-white"
      >
        <div className="flex items-center gap-3 mb-2">
          <Youtube className="w-8 h-8" />
          <h1 className="text-3xl font-bold">YouTube 업로드 준비</h1>
        </div>
        <p className="text-red-100">
          생성된 Shorts를 YouTube에 업로드할 준비가 완료되었습니다
        </p>
      </motion.div>

      {/* 생성된 쇼츠 리스트 */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.05 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <h3 className="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <HardDrive className="w-5 h-5 text-blue-500" />
          지금까지 생성된 쇼츠 ({videoList.length}개)
        </h3>
        
        {loadingVideos ? (
          <div className="flex justify-center items-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500"></div>
          </div>
        ) : videoList.length === 0 ? (
          <div className="text-center py-12 text-gray-500">
            <PlayCircle className="w-16 h-16 mx-auto mb-4 text-gray-300" />
            <p>아직 생성된 쇼츠가 없습니다</p>
            <p className="text-sm mt-2">비디오 페이지에서 쇼츠를 생성해보세요!</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {videoList.map((video, index) => (
              <motion.div
                key={video.videoId}
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: index * 0.05 }}
                className="bg-gray-50 rounded-lg overflow-hidden border border-gray-200 hover:border-red-300 hover:shadow-md transition-all"
              >
                {/* 비디오 썸네일 */}
                <div className="aspect-[9/16] bg-black relative group">
                  <video
                    className="w-full h-full object-cover"
                    src={video.url}
                    muted
                    playsInline
                    onMouseEnter={(e) => e.target.play()}
                    onMouseLeave={(e) => {
                      e.target.pause();
                      e.target.currentTime = 0;
                    }}
                  />
                  <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                    <PlayCircle className="w-16 h-16 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                  </div>
                </div>
                
                {/* 비디오 정보 */}
                <div className="p-4">
                  <div className="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <Calendar className="w-4 h-4" />
                    <span>{formatDate(video.createdAt)}</span>
                  </div>
                  <div className="text-xs text-gray-500 mb-3">
                    크기: {formatFileSize(video.size)}
                  </div>
                  <div className="flex gap-2">
                    <a
                      href={video.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex-1 px-3 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition-colors text-center flex items-center justify-center gap-1"
                    >
                      <PlayCircle className="w-4 h-4" />
                      재생
                    </a>
                    <a
                      href={video.url}
                      download={video.filename}
                      className="flex-1 px-3 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors text-center flex items-center justify-center gap-1"
                    >
                      <Download className="w-4 h-4" />
                      다운로드
                    </a>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        )}
      </motion.div>

      {/* Final Video Preview */}
      {finalVideo?.url && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="bg-white rounded-xl shadow-lg p-6"
        >
          <h3 className="font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <Sparkles className="w-5 h-5 text-yellow-500" />
            최종 비디오
          </h3>
          <div className="aspect-[9/16] bg-black rounded-lg overflow-hidden max-w-sm mx-auto">
            <video
              controls
              className="w-full h-full"
              src={finalVideo.url}
            >
              Your browser does not support the video tag.
            </video>
          </div>
          <div className="mt-4 text-center">
            <a
              href={finalVideo.url}
              download
              className="inline-flex items-center gap-2 px-6 py-3 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors"
            >
              <Download className="w-5 h-5" />
              비디오 다운로드
            </a>
          </div>
        </motion.div>
      )}

      {/* YouTube Upload Info */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="bg-white rounded-xl shadow-lg p-6 space-y-6"
      >
        <h3 className="font-semibold text-gray-900 flex items-center gap-2">
          <Youtube className="w-5 h-5 text-red-600" />
          YouTube 업로드 정보
        </h3>

        {/* Title */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            제목 (최대 100자)
          </label>
          <div className="relative">
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              maxLength={100}
              className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            />
            <button
              onClick={() => copyToClipboard(title, 'title')}
              className="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 transition-colors"
              title="복사"
            >
              {copied.title ? (
                <CheckCircle className="w-5 h-5 text-green-500" />
              ) : (
                <Copy className="w-5 h-5" />
              )}
            </button>
          </div>
          <p className="mt-1 text-sm text-gray-500">
            {title.length}/100자
          </p>
        </div>

        {/* Description */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            설명 (최대 5000자)
          </label>
          <div className="relative">
            <textarea
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              maxLength={5000}
              rows={8}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
            />
            <button
              onClick={() => copyToClipboard(description, 'description')}
              className="absolute right-2 top-2 p-2 text-gray-400 hover:text-gray-600 transition-colors"
              title="복사"
            >
              {copied.description ? (
                <CheckCircle className="w-5 h-5 text-green-500" />
              ) : (
                <Copy className="w-5 h-5" />
              )}
            </button>
          </div>
          <p className="mt-1 text-sm text-gray-500">
            {description.length}/5000자
          </p>
        </div>

        {/* Keywords/Tags */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            태그 (쉼표로 구분)
          </label>
          <div className="relative">
            <input
              type="text"
              value={keywords}
              onChange={(e) => setKeywords(e.target.value)}
              className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
              placeholder="예: 쇼츠, AI, 자동생성"
            />
            <button
              onClick={() => copyToClipboard(keywords, 'keywords')}
              className="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 transition-colors"
              title="복사"
            >
              {copied.keywords ? (
                <CheckCircle className="w-5 h-5 text-green-500" />
              ) : (
                <Copy className="w-5 h-5" />
              )}
            </button>
          </div>
        </div>
      </motion.div>

      {/* Upload Instructions */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl shadow-lg p-6 border-2 border-red-200"
      >
        <h3 className="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <Share2 className="w-5 h-5 text-red-500" />
          YouTube 업로드 방법
        </h3>
        <ol className="space-y-3 text-gray-700">
          <li className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
              1
            </span>
            <span>위의 <strong>비디오 다운로드</strong> 버튼을 클릭하여 비디오를 다운로드합니다</span>
          </li>
          <li className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
              2
            </span>
            <span>
              <a 
                href="https://studio.youtube.com/channel/UC/videos/upload?filter=%5B%5D&sort=%7B%22columnType%22%3A%22date%22%2C%22sortOrder%22%3A%22DESCENDING%22%7D" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-red-600 hover:text-red-700 underline font-medium"
              >
                YouTube Studio
              </a>에 접속하여 비디오를 업로드합니다
            </span>
          </li>
          <li className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
              3
            </span>
            <span>위에서 생성된 <strong>제목</strong>, <strong>설명</strong>, <strong>태그</strong>를 복사하여 붙여넣습니다</span>
          </li>
          <li className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
              4
            </span>
            <span><strong>"Shorts"</strong> 카테고리를 선택하고 게시합니다</span>
          </li>
        </ol>

        <div className="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p className="text-sm text-yellow-800">
            <strong>💡 팁:</strong> YouTube Shorts는 세로형 비디오(9:16)이며, 60초 이하가 가장 좋은 성과를 냅니다.
            자막과 배경음악이 자동으로 추가되어 있어 바로 업로드할 수 있습니다!
          </p>
        </div>
      </motion.div>

      {/* Actions */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.4 }}
        className="flex flex-col sm:flex-row gap-3"
      >
        <a
          href="/"
          className="flex-1 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors text-center"
        >
          새로운 Shorts 만들기
        </a>
        <a
          href="/settings"
          className="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg font-medium hover:from-red-600 hover:to-pink-600 transition-colors text-center"
        >
          설정 관리
        </a>
      </motion.div>

      {/* Success Message */}
      <motion.div
        initial={{ opacity: 0, scale: 0.95 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ delay: 0.5 }}
        className="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white text-center"
      >
        <div className="flex items-center justify-center gap-3 mb-2">
          <CheckCircle className="w-8 h-8" />
          <h2 className="text-2xl font-bold">축하합니다! 🎉</h2>
        </div>
        <p className="text-green-100">
          고품질 YouTube Shorts가 성공적으로 생성되었습니다!<br />
          이제 YouTube에 업로드하여 많은 조회수를 받아보세요.
        </p>
      </motion.div>
    </div>
  );
}
