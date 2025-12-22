import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { FileText, Sparkles, Loader, Edit3, Save, Eye, EyeOff } from 'lucide-react';
import { useStore } from '../store/useStore';
import api from '../api/client';

// 기본 프롬프트 (TTS 친화적)
const DEFAULT_PROMPT = `당신은 블로그 콘텐츠를 YouTube Shorts용 자연스러운 TTS 나레이션으로 변환하는 전문가입니다.

**엄격한 금지사항**:
- 이모지 사용 금지
- 장면 설명 금지 (예: "scene", "1-3s")
- 마크다운 기호 금지 (**, #, -)
- 콜론(:) 사용 금지
- 제목/부제목 형식 금지
- 타임코드 표시 금지

**작성 원칙**:
1. 블로그 원문 내용에 충실 (상상 금지)
2. 자연스러운 구어체 문장
3. 문장 길이: 15-50자 각각
4. 총 6-10개 문장
5. 마침표(.)로만 구분

**출력**: 순수 텍스트만 제공`;

export default function ScriptPage() {
  const { crawledData, settings, setScript } = useStore();
  const [generating, setGenerating] = useState(false);
  const [error, setError] = useState('');
  const [parts, setParts] = useState([]);
  const [editingIndex, setEditingIndex] = useState(null);
  const [customPrompt, setCustomPrompt] = useState(DEFAULT_PROMPT);
  const [showPromptEditor, setShowPromptEditor] = useState(false);

  const handleGenerate = async () => {
    if (!crawledData) {
      setError('먼저 블로그/기사를 크롤링해주세요');
      return;
    }

    setGenerating(true);
    setError('');

    try {
      console.log('📡 API Request: POST /api/script/generate');
      const response = await api.post('/api/script/generate', {
        content: crawledData.content,
        title: crawledData.title,
        images: crawledData.images,
        geminiApiKey: settings.geminiApiKey,
        sceneCount: 12,  // 30초 이상 영상을 위해 12개 장면으로 증가
        prompt: customPrompt // 커스텀 프롬프트 전달
      });

      console.log('✅ API Response:', response.data);

      // API 응답 구조: { success: true, data: { title, description, keywords, scenes: [...] } }
      const scriptData = response.data.data || response.data;
      const scenes = scriptData.scenes || [];
      
      console.log(`✅ Scenes count: ${scenes.length}`);

      // 장면 데이터를 parts 형식으로 변환
      const generatedParts = scenes.map((scene, index) => ({
        sceneNumber: scene.sceneNumber || index + 1,
        text: scene.narration || '',
        imageDescription: scene.imageDescription || '',
        imageUrl: scene.suggestedImage?.proxyUrl || scene.suggestedImage?.url || '',
        duration: scene.duration || 3,
        imageVisible: true
      }));

      console.log('✅ Generated parts:', generatedParts.length);
      
      setParts(generatedParts);
      setScript(generatedParts);
      setError('');
    } catch (err) {
      console.error('❌ Script generation error:', err);
      setError(err.response?.data?.error || '스크립트 생성 중 오류가 발생했습니다');
    } finally {
      setGenerating(false);
    }
  };

  const handleEditPart = (index, field, value) => {
    const newParts = [...parts];
    newParts[index][field] = value;
    setParts(newParts);
  };

  const handleSave = () => {
    setScript(parts);
    setEditingIndex(null);
  };

  const toggleImageVisibility = (index) => {
    const newParts = [...parts];
    newParts[index].imageVisible = !newParts[index].imageVisible;
    setParts(newParts);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl p-8 text-white"
      >
        <div className="flex items-center gap-3 mb-2">
          <Sparkles className="w-8 h-8" />
          <h1 className="text-3xl font-bold">AI 스크립트 생성</h1>
        </div>
        <p className="text-purple-100">
          Gemini API를 사용하여 자동으로 장면별 스크립트를 생성합니다
        </p>
      </motion.div>

      {/* Source Content */}
      {crawledData && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="bg-white rounded-xl shadow-lg p-6"
        >
          <h3 className="font-semibold text-gray-900 mb-3">원본 콘텐츠</h3>
          <div className="bg-gray-50 p-4 rounded-lg">
            <p className="font-medium text-gray-800 mb-2">{crawledData.title}</p>
            <p className="text-sm text-gray-600 line-clamp-3">{crawledData.content}</p>
          </div>
        </motion.div>
      )}

      {/* Custom Prompt Editor */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.15 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-3">
            <Edit3 className="w-6 h-6 text-purple-500" />
            <h3 className="font-semibold text-gray-900">AI 프롬프트 설정</h3>
          </div>
          <button
            onClick={() => setShowPromptEditor(!showPromptEditor)}
            className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
          >
            {showPromptEditor ? (
              <>
                <EyeOff className="w-4 h-4" />
                숨기기
              </>
            ) : (
              <>
                <Eye className="w-4 h-4" />
                편집하기
              </>
            )}
          </button>
        </div>

        {showPromptEditor ? (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                커스텀 프롬프트 (AI가 스크립트 생성 시 사용)
              </label>
              <textarea
                value={customPrompt}
                onChange={(e) => setCustomPrompt(e.target.value)}
                rows={12}
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"
                placeholder="프롬프트를 입력하세요..."
              />
            </div>
            
            <div className="flex gap-3">
              <button
                onClick={() => setCustomPrompt(DEFAULT_PROMPT)}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
              >
                기본값으로 복원
              </button>
              <button
                onClick={() => setShowPromptEditor(false)}
                className="px-4 py-2 text-sm font-medium text-white bg-purple-500 hover:bg-purple-600 rounded-lg transition-colors"
              >
                적용
              </button>
            </div>

            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div className="flex gap-2">
                <svg className="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                </svg>
                <div className="text-sm text-blue-700">
                  <strong>프롬프트 팁:</strong> 
                  <ul className="mt-2 list-disc list-inside space-y-1">
                    <li>TTS(음성합성)에 적합한 자연스러운 문장을 요청하세요</li>
                    <li>문장 길이와 개수를 명확히 지정하세요</li>
                    <li>금지사항(이모지, 마크다운 등)을 명시하세요</li>
                    <li>원문 내용에 충실하도록 요청하세요</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        ) : (
          <div className="bg-gray-50 p-4 rounded-lg">
            <p className="text-sm text-gray-600 line-clamp-3 font-mono">{customPrompt}</p>
          </div>
        )}
      </motion.div>

      {/* Generate Button */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <button
          onClick={handleGenerate}
          disabled={generating || !crawledData}
          className="w-full px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg font-medium hover:from-purple-600 hover:to-pink-600 disabled:from-gray-300 disabled:to-gray-300 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3"
        >
          {generating ? (
            <>
              <Loader className="w-6 h-6 animate-spin" />
              AI가 스크립트를 생성하고 있습니다...
            </>
          ) : (
            <>
              <Sparkles className="w-6 h-6" />
              AI 스크립트 생성
            </>
          )}
        </button>

        {error && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"
          >
            {error}
          </motion.div>
        )}
      </motion.div>

      {/* Generated Script */}
      {parts.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3 }}
          className="space-y-4"
        >
          <div className="flex items-center justify-between">
            <h3 className="text-xl font-bold text-gray-900">생성된 스크립트 ({parts.length}개 장면)</h3>
            <button
              onClick={handleSave}
              className="px-4 py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors flex items-center gap-2"
            >
              <Save className="w-4 h-4" />
              저장
            </button>
          </div>

          <div className="space-y-4">
            {parts.map((part, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: index * 0.1 }}
                className="bg-white rounded-xl shadow-lg p-6"
              >
                <div className="flex items-start gap-4">
                  {/* Scene Number */}
                  <div className="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                    {index + 1}
                  </div>

                  <div className="flex-1 space-y-4">
                    {/* Text */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        스크립트 텍스트
                      </label>
                      {editingIndex === index ? (
                        <textarea
                          value={part.text}
                          onChange={(e) => handleEditPart(index, 'text', e.target.value)}
                          className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          rows={3}
                        />
                      ) : (
                        <p className="text-gray-800 bg-gray-50 p-4 rounded-lg">{part.text}</p>
                      )}
                    </div>

                    {/* Image */}
                    {part.imageUrl && (
                      <div>
                        <div className="flex items-center justify-between mb-2">
                          <label className="block text-sm font-medium text-gray-700">
                            이미지
                          </label>
                          <button
                            onClick={() => toggleImageVisibility(index)}
                            className="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1"
                          >
                            {part.imageVisible !== false ? (
                              <>
                                <Eye className="w-4 h-4" />
                                표시
                              </>
                            ) : (
                              <>
                                <EyeOff className="w-4 h-4" />
                                숨김
                              </>
                            )}
                          </button>
                        </div>
                        {part.imageVisible !== false && (
                          <div className="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
                            <img
                              src={part.imageUrl}
                              alt={`Scene ${index + 1}`}
                              className="w-full h-full object-cover"
                            />
                          </div>
                        )}
                      </div>
                    )}

                    {/* Edit Button */}
                    <div className="flex gap-2">
                      {editingIndex === index ? (
                        <button
                          onClick={() => setEditingIndex(null)}
                          className="px-4 py-2 bg-gray-500 text-white rounded-lg font-medium hover:bg-gray-600 transition-colors flex items-center gap-2"
                        >
                          <Save className="w-4 h-4" />
                          완료
                        </button>
                      ) : (
                        <button
                          onClick={() => setEditingIndex(index)}
                          className="px-4 py-2 bg-purple-500 text-white rounded-lg font-medium hover:bg-purple-600 transition-colors flex items-center gap-2"
                        >
                          <Edit3 className="w-4 h-4" />
                          수정
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>

          {/* Next Step */}
          <div className="pt-4">
            <a
              href="/voice"
              className="inline-flex items-center gap-2 px-6 py-3 bg-purple-500 text-white rounded-lg font-medium hover:bg-purple-600 transition-colors"
            >
              다음 단계: 음성 생성
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
