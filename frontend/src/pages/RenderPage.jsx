import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Sparkles, Loader, CheckCircle, Wand2, AlertCircle } from 'lucide-react';
import useStore from '../store/useStore';
import api from '../api/client';

export default function RenderPage() {
  const { videoData, settings, setFinalVideo } = useStore();
  const [rendering, setRendering] = useState(false);
  const [progress, setProgress] = useState(0);
  const [status, setStatus] = useState('');
  const [renderId, setRenderId] = useState(null);
  const [error, setError] = useState('');
  const [finalUrl, setFinalUrl] = useState('');
  const [options, setOptions] = useState({
    addBgMusic: true,
    addEffects: true,
    quality: 'high'
  });

  const handleRender = async () => {
    if (!videoData?.videoUrl) {
      setError('먼저 비디오를 생성해주세요');
      return;
    }

    setRendering(true);
    setError('');
    setProgress(0);
    setStatus('최종 렌더링 준비 중...');

    try {
      // Start final rendering
      const response = await api.post('/api/render/final', {
        videoUrl: videoData.videoUrl,
        options: options,
        shotstackApiKey: settings.shotstackApiKey
      });

      const id = response.data.renderId;
      setRenderId(id);
      setStatus('Shotstack에서 렌더링 중...');
      setProgress(20);

      // Poll for status
      const pollInterval = setInterval(async () => {
        try {
          const statusResponse = await api.get(`/api/render/status/${id}`);
          const statusData = statusResponse.data;

          setProgress(statusData.progress || 50);
          setStatus(statusData.message || '렌더링 중...');

          if (statusData.status === 'done') {
            clearInterval(pollInterval);
            setProgress(100);
            setStatus('최종 렌더링 완료!');
            setFinalUrl(statusData.url);
            setFinalVideo({
              renderId: id,
              url: statusData.url,
              fileSize: statusData.fileSize
            });
            setRendering(false);
          } else if (statusData.status === 'failed') {
            clearInterval(pollInterval);
            setError(statusData.error || '렌더링 중 오류가 발생했습니다');
            setRendering(false);
          }
        } catch (err) {
          clearInterval(pollInterval);
          setError('상태 확인 중 오류가 발생했습니다');
          setRendering(false);
        }
      }, 5000);

      // Timeout after 10 minutes
      setTimeout(() => {
        clearInterval(pollInterval);
        if (rendering) {
          setError('렌더링 시간이 초과되었습니다');
          setRendering(false);
        }
      }, 600000);

    } catch (err) {
      setError(err.response?.data?.error || '렌더링 중 오류가 발생했습니다');
      setRendering(false);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl p-8 text-white"
      >
        <div className="flex items-center gap-3 mb-2">
          <Wand2 className="w-8 h-8" />
          <h1 className="text-3xl font-bold">최종 렌더링</h1>
        </div>
        <p className="text-red-100">
          Shotstack API를 사용하여 전문가급 품질로 최종 렌더링합니다
        </p>
      </motion.div>

      {/* Render Options */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <h3 className="font-semibold text-gray-900 mb-4">렌더링 옵션</h3>
        <div className="space-y-4">
          <label className="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 transition-colors">
            <input
              type="checkbox"
              checked={options.addBgMusic}
              onChange={(e) => setOptions({ ...options, addBgMusic: e.target.checked })}
              className="w-5 h-5 text-red-500 rounded focus:ring-red-500"
            />
            <div>
              <p className="font-medium text-gray-900">배경 음악 추가</p>
              <p className="text-sm text-gray-600">저작권 무료 배경 음악을 자동으로 추가합니다</p>
            </div>
          </label>

          <label className="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 transition-colors">
            <input
              type="checkbox"
              checked={options.addEffects}
              onChange={(e) => setOptions({ ...options, addEffects: e.target.checked })}
              className="w-5 h-5 text-red-500 rounded focus:ring-red-500"
            />
            <div>
              <p className="font-medium text-gray-900">비디오 효과 추가</p>
              <p className="text-sm text-gray-600">전환 효과, 필터 등을 적용합니다</p>
            </div>
          </label>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              화질 설정
            </label>
            <select
              value={options.quality}
              onChange={(e) => setOptions({ ...options, quality: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            >
              <option value="medium">중간 (720p)</option>
              <option value="high">고화질 (1080p)</option>
              <option value="ultra">최고화질 (2K)</option>
            </select>
          </div>
        </div>
      </motion.div>

      {/* Source Video */}
      {videoData?.videoUrl && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
          className="bg-white rounded-xl shadow-lg p-6"
        >
          <h3 className="font-semibold text-gray-900 mb-4">원본 비디오</h3>
          <div className="aspect-[9/16] bg-black rounded-lg overflow-hidden max-w-sm mx-auto">
            <video
              controls
              className="w-full h-full"
              src={videoData.videoUrl}
            >
              Your browser does not support the video tag.
            </video>
          </div>
        </motion.div>
      )}

      {/* Render Button */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <button
          onClick={handleRender}
          disabled={rendering || !videoData?.videoUrl}
          className="w-full px-6 py-4 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg font-medium hover:from-red-600 hover:to-pink-600 disabled:from-gray-300 disabled:to-gray-300 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3"
        >
          {rendering ? (
            <>
              <Loader className="w-6 h-6 animate-spin" />
              {status} ({progress}%)
            </>
          ) : (
            <>
              <Sparkles className="w-6 h-6" />
              최종 렌더링 시작
            </>
          )}
        </button>

        {rendering && (
          <div className="mt-4 space-y-3">
            <div className="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <motion.div
                initial={{ width: 0 }}
                animate={{ width: `${progress}%` }}
                transition={{ duration: 0.5 }}
                className="h-full bg-gradient-to-r from-red-500 to-pink-500"
              />
            </div>
            <p className="text-center text-sm text-gray-600">{status}</p>
            <p className="text-center text-xs text-gray-500">
              고품질 렌더링에는 5-10분이 소요될 수 있습니다
            </p>
          </div>
        )}

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

      {/* Final Video */}
      {finalUrl && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl shadow-lg p-6 border-2 border-red-200"
        >
          <div className="flex items-center gap-3 mb-6">
            <CheckCircle className="w-8 h-8 text-green-500" />
            <div>
              <h3 className="text-2xl font-bold text-gray-900">렌더링 완료! 🎉</h3>
              <p className="text-gray-600">전문가급 YouTube Shorts가 완성되었습니다</p>
            </div>
          </div>

          <div className="aspect-[9/16] bg-black rounded-lg overflow-hidden max-w-sm mx-auto mb-6">
            <video
              controls
              className="w-full h-full"
              src={finalUrl}
            >
              Your browser does not support the video tag.
            </video>
          </div>

          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <a
              href={finalUrl}
              download
              className="px-6 py-3 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors text-center"
            >
              최종 비디오 다운로드
            </a>
            <a
              href="/preview"
              className="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg font-medium hover:from-red-600 hover:to-pink-600 transition-colors flex items-center justify-center gap-2"
            >
              유튜브 업로드 준비
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </motion.div>
      )}
    </div>
  );
}
