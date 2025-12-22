import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Mic, Play, Pause, Loader, Volume2, CheckCircle, User, Users } from 'lucide-react';
import useStore from '../store/useStore';
import api from '../api/client';

export default function VoicePage() {
  const { script, settings, setVoiceData } = useStore();
  const [voiceCategories, setVoiceCategories] = useState({});
  const [activeTab, setActiveTab] = useState('male');
  const [selectedVoice, setSelectedVoice] = useState(null);
  const [generating, setGenerating] = useState(false);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState('');
  const [previewAudio, setPreviewAudio] = useState(null);
  const [playing, setPlaying] = useState(null);
  const [previewing, setPreviewing] = useState(null);

  // 탭 아이콘 매핑
  const tabIcons = {
    male: <User className="w-5 h-5" />,
    female: <Users className="w-5 h-5" />
  };

  // 탭 라벨 매핑
  const tabLabels = {
    male: '남성',
    female: '여성'
  };

  useEffect(() => {
    loadVoiceSamples();
  }, []);

  const loadVoiceSamples = async () => {
    try {
      console.log('🎤 Loading voice samples...');
      const response = await api.get('/api/voice/samples');
      console.log('✅ Voice samples response:', response.data);
      
      // API 응답 구조: { success: true, data: { male: [...], female: [...] } }
      const voiceData = response.data.data || response.data;
      
      setVoiceCategories(voiceData);
      
      // 첫 번째 카테고리의 첫 번째 음성을 기본 선택
      const firstCategory = Object.keys(voiceData)[0];
      if (firstCategory && voiceData[firstCategory].length > 0) {
        setActiveTab(firstCategory);
        setSelectedVoice(voiceData[firstCategory][0].id);
        console.log('✅ Default voice selected:', voiceData[firstCategory][0].id);
      }
    } catch (err) {
      console.error('❌ 음성 샘플 로드 실패:', err);
      setError('음성 샘플을 불러오는데 실패했습니다');
    }
  };

  const handlePreview = async (voiceId) => {
    // 이미 재생 중이면 정지
    if (playing === voiceId) {
      previewAudio?.pause();
      setPlaying(null);
      setPreviewAudio(null);
      return;
    }

    // 다른 오디오 정지
    if (previewAudio) {
      previewAudio.pause();
      setPreviewAudio(null);
    }

    // API 키 확인
    if (!settings.minimaxApiKey || !settings.minimaxGroupId) {
      setError('설정 페이지에서 Minimax API 키와 Group ID를 먼저 입력해주세요');
      return;
    }

    setPreviewing(voiceId);
    setError('');

    try {
      console.log('🎵 Preview voice:', voiceId);
      
      const response = await api.post('/api/voice/preview', {
        voiceId,
        text: '안녕하세요, 이 음성으로 쇼츠를 생성합니다.',
        minimaxApiKey: settings.minimaxApiKey,
        minimaxGroupId: settings.minimaxGroupId
      });
      
      const audioData = response.data.data?.audioData || response.data.audioData;
      
      if (!audioData) {
        throw new Error('오디오 데이터를 받지 못했습니다');
      }
      
      const audio = new Audio(audioData);
      audio.onended = () => {
        setPlaying(null);
      };
      audio.onerror = (e) => {
        console.error('❌ Audio playback error:', e);
        setError('오디오 재생 중 오류가 발생했습니다');
        setPlaying(null);
      };
      
      await audio.play();
      
      setPreviewAudio(audio);
      setPlaying(voiceId);
    } catch (err) {
      console.error('❌ 미리듣기 실패:', err);
      const errorMsg = err.response?.data?.error || err.message || '음성 미리듣기에 실패했습니다';
      setError(errorMsg);
      
      if (errorMsg.includes('API') || errorMsg.includes('키')) {
        setError('Minimax API 키를 확인해주세요. 설정 페이지에서 올바른 API 키와 Group ID를 입력했는지 확인하세요.');
      }
    } finally {
      setPreviewing(null);
    }
  };

  const handleGenerate = async () => {
    if (!script || script.length === 0) {
      setError('먼저 스크립트를 생성해주세요');
      return;
    }

    if (!selectedVoice) {
      setError('음성을 선택해주세요');
      return;
    }

    setGenerating(true);
    setError('');
    setProgress(0);

    try {
      console.log('🎙️ Generating voice for', script.length, 'scenes');
      
      const scenesWithNarration = script.map(part => ({
        ...part,
        narration: part.narration || part.text || '',
        sceneNumber: part.sceneNumber || 1,
        duration: part.duration || 3
      }));
      
      const response = await api.post('/api/voice/generate', {
        scenes: scenesWithNarration,
        voiceId: selectedVoice,
        minimaxApiKey: settings.minimaxApiKey,
        minimaxGroupId: settings.minimaxGroupId
      });

      // Progress simulation
      let currentProgress = 0;
      const progressInterval = setInterval(() => {
        currentProgress += 10;
        setProgress(currentProgress);
        if (currentProgress >= 100) {
          clearInterval(progressInterval);
        }
      }, 300);

      const voiceResult = response.data.data || response.data;
      
      setVoiceData({
        audioFiles: voiceResult.audioFiles,
        totalDuration: voiceResult.totalDuration,
        voiceId: selectedVoice
      });

      clearInterval(progressInterval);
      setProgress(100);
      console.log('✅ Voice generation completed');
    } catch (err) {
      console.error('❌ Voice generation error:', err);
      setError(err.response?.data?.error || '음성 생성 중 오류가 발생했습니다: ' + err.message);
    } finally {
      setGenerating(false);
    }
  };

  const totalVoices = Object.values(voiceCategories).flat().length;

  return (
    <div className="space-y-6">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-gradient-to-r from-pink-500 to-orange-500 rounded-2xl p-8 text-white"
      >
        <div className="flex items-center gap-3 mb-2">
          <Mic className="w-8 h-8" />
          <h1 className="text-3xl font-bold">음성 생성 (TTS)</h1>
        </div>
        <p className="text-pink-100">
          Minimax API를 사용하여 고품질 음성을 생성합니다 - {totalVoices}개 음성 옵션 사용 가능
        </p>
      </motion.div>

      {/* Script Preview */}
      {script && script.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="bg-white rounded-xl shadow-lg p-6"
        >
          <h3 className="font-semibold text-gray-900 mb-3">스크립트 ({script.length}개 장면)</h3>
          <div className="space-y-2 max-h-48 overflow-y-auto">
            {script.map((part, idx) => (
              <div key={idx} className="flex gap-3 p-3 bg-gray-50 rounded-lg">
                <div className="flex-shrink-0 w-8 h-8 bg-pink-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  {idx + 1}
                </div>
                <p className="text-gray-700 text-sm">{part.text || part.narration || ''}</p>
              </div>
            ))}
          </div>
        </motion.div>
      )}

      {/* Voice Selection with Tabs */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <h3 className="font-semibold text-gray-900 mb-4">음성 선택</h3>
        
        {/* Tabs */}
        <div className="flex gap-2 mb-6 border-b border-gray-200">
          {Object.keys(voiceCategories).map((category) => (
            <button
              key={category}
              onClick={() => setActiveTab(category)}
              className={`px-4 py-3 font-medium transition-all flex items-center gap-2 ${
                activeTab === category
                  ? 'text-pink-600 border-b-2 border-pink-600'
                  : 'text-gray-600 hover:text-pink-500'
              }`}
            >
              {tabIcons[category]}
              {tabLabels[category]}
              <span className="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs">
                {voiceCategories[category]?.length || 0}
              </span>
            </button>
          ))}
        </div>

        {/* Voice Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {(voiceCategories[activeTab] || []).map((voice) => (
            <motion.div
              key={voice.id}
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.98 }}
              onClick={() => setSelectedVoice(voice.id)}
              className={`p-4 rounded-lg border-2 cursor-pointer transition-all ${
                selectedVoice === voice.id
                  ? 'border-pink-500 bg-pink-50'
                  : 'border-gray-200 hover:border-pink-300'
              }`}
            >
              <div className="flex items-start justify-between mb-3">
                <div className="flex-1">
                  <h4 className="font-medium text-gray-900 mb-1">{voice.name}</h4>
                  {voice.desc && (
                    <p className="text-xs text-gray-500">{voice.desc}</p>
                  )}
                </div>
                {selectedVoice === voice.id && (
                  <CheckCircle className="w-6 h-6 text-pink-500 flex-shrink-0" />
                )}
              </div>

              <button
                onClick={(e) => {
                  e.stopPropagation();
                  handlePreview(voice.id);
                }}
                disabled={previewing === voice.id}
                className="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 disabled:cursor-not-allowed rounded-lg font-medium text-sm flex items-center justify-center gap-2 transition-colors"
              >
                {previewing === voice.id ? (
                  <>
                    <Loader className="w-4 h-4 animate-spin" />
                    로딩 중...
                  </>
                ) : playing === voice.id ? (
                  <>
                    <Pause className="w-4 h-4" />
                    정지
                  </>
                ) : (
                  <>
                    <Play className="w-4 h-4" />
                    미리듣기
                  </>
                )}
              </button>
            </motion.div>
          ))}
        </div>
      </motion.div>

      {/* Generate Button */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <button
          onClick={handleGenerate}
          disabled={generating || !script || !selectedVoice}
          className="w-full px-6 py-4 bg-gradient-to-r from-pink-500 to-orange-500 text-white rounded-lg font-medium hover:from-pink-600 hover:to-orange-600 disabled:from-gray-300 disabled:to-gray-300 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3"
        >
          {generating ? (
            <>
              <Loader className="w-6 h-6 animate-spin" />
              음성 생성 중... ({progress}%)
            </>
          ) : (
            <>
              <Volume2 className="w-6 h-6" />
              음성 생성
            </>
          )}
        </button>

        {generating && (
          <div className="mt-4">
            <div className="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <motion.div
                initial={{ width: 0 }}
                animate={{ width: `${progress}%` }}
                transition={{ duration: 0.3 }}
                className="h-full bg-gradient-to-r from-pink-500 to-orange-500"
              />
            </div>
          </div>
        )}

        {error && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"
          >
            {error}
          </motion.div>
        )}

        {progress === 100 && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3"
          >
            <CheckCircle className="w-6 h-6 text-green-500" />
            <div>
              <p className="font-medium text-green-900">음성 생성 완료!</p>
              <p className="text-sm text-green-700">이제 비디오를 생성할 수 있습니다</p>
            </div>
          </motion.div>
        )}
      </motion.div>

      {/* Next Step */}
      {progress === 100 && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="pt-4"
        >
          <a
            href="/video"
            className="inline-flex items-center gap-2 px-6 py-3 bg-pink-500 text-white rounded-lg font-medium hover:bg-pink-600 transition-colors"
          >
            다음 단계: 비디오 생성
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </motion.div>
      )}
    </div>
  );
}
