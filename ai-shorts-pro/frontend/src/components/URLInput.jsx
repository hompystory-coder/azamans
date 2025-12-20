import { useState } from 'react';

export default function URLInput({ url, setUrl, onCrawl, loading }) {
  const [inputMode, setInputMode] = useState('url'); // 'url' or 'text'

  const handleSubmit = (e) => {
    e.preventDefault();
    if (inputMode === 'url' && url) {
      onCrawl();
    }
  };

  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold text-white">콘텐츠 입력</h2>
      <p className="text-gray-300">
        블로그 URL을 입력하거나 직접 텍스트를 작성하여 쇼츠 영상을 만들어보세요.
      </p>

      {/* Input Mode Toggle */}
      <div className="flex space-x-4 mb-6">
        <button
          onClick={() => setInputMode('url')}
          className={`flex-1 py-3 px-6 rounded-lg font-semibold transition-all ${
            inputMode === 'url'
              ? 'bg-purple-600 text-white shadow-lg'
              : 'bg-white/10 text-gray-300 hover:bg-white/20'
          }`}
        >
          🔗 URL 입력
        </button>
        <button
          onClick={() => setInputMode('text')}
          className={`flex-1 py-3 px-6 rounded-lg font-semibold transition-all ${
            inputMode === 'text'
              ? 'bg-purple-600 text-white shadow-lg'
              : 'bg-white/10 text-gray-300 hover:bg-white/20'
          }`}
        >
          ✍️ 텍스트 입력
        </button>
      </div>

      {inputMode === 'url' ? (
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-white text-sm font-semibold mb-2">
              블로그 URL
            </label>
            <div className="flex space-x-2">
              <input
                type="url"
                value={url}
                onChange={(e) => setUrl(e.target.value)}
                placeholder="https://blog.naver.com/..."
                className="flex-1 px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                required
              />
              <button
                type="submit"
                disabled={loading || !url}
                className="px-8 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold rounded-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
              >
                {loading ? (
                  <span className="flex items-center space-x-2">
                    <span className="animate-spin">⏳</span>
                    <span>분석 중...</span>
                  </span>
                ) : (
                  '🔍 분석하기'
                )}
              </button>
            </div>
            <p className="mt-2 text-sm text-gray-400">
              💡 네이버 블로그, 티스토리, 일반 블로그 URL을 지원합니다
            </p>
          </div>

          {/* URL Examples */}
          <div className="bg-white/5 border border-white/10 rounded-lg p-4">
            <div className="text-white font-semibold text-sm mb-3">예시 URL</div>
            <div className="space-y-2">
              {[
                'https://blog.naver.com/alphahome/224113805624',
                'https://blog.naver.com/example/123456789',
                'https://example.tistory.com/123'
              ].map((exampleUrl) => (
                <button
                  key={exampleUrl}
                  type="button"
                  onClick={() => setUrl(exampleUrl)}
                  className="w-full text-left px-3 py-2 bg-white/5 hover:bg-white/10 rounded text-gray-300 text-sm transition-colors"
                >
                  {exampleUrl}
                </button>
              ))}
            </div>
          </div>
        </form>
      ) : (
        <div className="space-y-4">
          <div>
            <label className="block text-white text-sm font-semibold mb-2">
              직접 텍스트 입력
            </label>
            <textarea
              placeholder="쇼츠 영상으로 만들고 싶은 내용을 자유롭게 작성하세요...

예시:
- 제품 리뷰
- 브랜드 소개
- 상품 사용법
- 비교 분석
- 추천 콘텐츠"
              className="w-full h-64 px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
            />
          </div>
          <button
            type="button"
            disabled={loading}
            className="w-full px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold text-lg rounded-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? '생성 중...' : '🎬 스크립트 생성하기'}
          </button>
        </div>
      )}

      {/* Loading State */}
      {loading && (
        <div className="mt-6 p-6 bg-blue-500/10 border border-blue-500/30 rounded-xl">
          <div className="flex items-center space-x-3">
            <div className="animate-spin text-3xl">🔄</div>
            <div>
              <div className="text-white font-bold">AI가 콘텐츠를 분석하고 있습니다...</div>
              <div className="text-gray-300 text-sm mt-1">
                키워드 추출, 제품 정보 파악, 스크립트 생성 중
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Info Box */}
      <div className="bg-white/5 border border-white/10 rounded-lg p-6">
        <div className="text-white font-semibold mb-3">📝 자동 생성 내용</div>
        <ul className="space-y-2 text-gray-300 text-sm">
          <li className="flex items-start">
            <span className="mr-2">✓</span>
            <span><strong>5개 장면 스크립트:</strong> 자동으로 구성된 스토리라인</span>
          </li>
          <li className="flex items-start">
            <span className="mr-2">✓</span>
            <span><strong>자막 텍스트:</strong> 각 장면별 최적화된 자막</span>
          </li>
          <li className="flex items-start">
            <span className="mr-2">✓</span>
            <span><strong>캐릭터 이미지 프롬프트:</strong> 장면에 맞는 비주얼 가이드</span>
          </li>
          <li className="flex items-start">
            <span className="mr-2">✓</span>
            <span><strong>음성 내레이션:</strong> TTS로 읽을 대사</span>
          </li>
        </ul>
      </div>
    </div>
  );
}
