import { useState } from 'react';
import { useAppStore } from '../store/appStore';

export default function ScriptEditor({ scenes: initialScenes }) {
  const { scenes, setScenes } = useAppStore();
  const [activeScene, setActiveScene] = useState(0);

  const displayScenes = scenes.length > 0 ? scenes : initialScenes || [];

  const handleSceneChange = (index, field, value) => {
    const updatedScenes = [...displayScenes];
    updatedScenes[index] = { ...updatedScenes[index], [field]: value };
    setScenes(updatedScenes);
  };

  const addScene = () => {
    const newScene = {
      id: displayScenes.length + 1,
      subtitle: '',
      narration: '',
      imagePrompt: '',
      duration: 3
    };
    setScenes([...displayScenes, newScene]);
  };

  const removeScene = (index) => {
    if (displayScenes.length <= 1) {
      alert('최소 1개의 장면이 필요합니다.');
      return;
    }
    const updatedScenes = displayScenes.filter((_, i) => i !== index);
    setScenes(updatedScenes);
    if (activeScene >= updatedScenes.length) {
      setActiveScene(updatedScenes.length - 1);
    }
  };

  const duplicateScene = (index) => {
    const sceneToDuplicate = { ...displayScenes[index] };
    const updatedScenes = [
      ...displayScenes.slice(0, index + 1),
      { ...sceneToDuplicate, id: displayScenes.length + 1 },
      ...displayScenes.slice(index + 1)
    ];
    setScenes(updatedScenes);
  };

  if (displayScenes.length === 0) {
    return (
      <div className="text-center py-12">
        <div className="text-6xl mb-4">📝</div>
        <h3 className="text-2xl font-bold text-white mb-2">스크립트가 없습니다</h3>
        <p className="text-gray-300 mb-6">
          블로그 URL을 입력하거나 직접 장면을 추가하세요
        </p>
        <button
          onClick={addScene}
          className="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors"
        >
          ➕ 첫 장면 추가
        </button>
      </div>
    );
  }

  const currentScene = displayScenes[activeScene];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-3xl font-bold text-white">스크립트 편집</h2>
        <button
          onClick={addScene}
          className="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2"
        >
          <span>➕</span>
          <span>장면 추가</span>
        </button>
      </div>

      <p className="text-gray-300">
        각 장면의 자막, 내레이션, 이미지 프롬프트를 수정할 수 있습니다. (총 {displayScenes.length}개 장면)
      </p>

      {/* Scene Timeline */}
      <div className="bg-white/5 border border-white/10 rounded-lg p-4">
        <div className="flex items-center space-x-2 overflow-x-auto pb-2">
          {displayScenes.map((scene, index) => (
            <button
              key={scene.id}
              onClick={() => setActiveScene(index)}
              className={`flex-shrink-0 px-4 py-2 rounded-lg font-semibold transition-all ${
                activeScene === index
                  ? 'bg-purple-600 text-white shadow-lg scale-105'
                  : 'bg-white/10 text-gray-300 hover:bg-white/20'
              }`}
            >
              장면 {index + 1}
            </button>
          ))}
        </div>
      </div>

      {/* Scene Editor */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Left: Editor */}
        <div className="space-y-4">
          <div className="bg-white/10 border border-white/20 rounded-xl p-6">
            <div className="flex items-center justify-between mb-6">
              <h3 className="text-xl font-bold text-white">
                장면 {activeScene + 1} / {displayScenes.length}
              </h3>
              <div className="flex space-x-2">
                <button
                  onClick={() => duplicateScene(activeScene)}
                  className="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition-colors"
                  title="복제"
                >
                  📋
                </button>
                <button
                  onClick={() => removeScene(activeScene)}
                  className="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded transition-colors"
                  title="삭제"
                >
                  🗑️
                </button>
              </div>
            </div>

            <div className="space-y-4">
              {/* Subtitle */}
              <div>
                <label className="block text-white text-sm font-semibold mb-2">
                  자막 텍스트
                </label>
                <input
                  type="text"
                  value={currentScene.subtitle || ''}
                  onChange={(e) => handleSceneChange(activeScene, 'subtitle', e.target.value)}
                  placeholder="화면에 표시될 자막을 입력하세요"
                  className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                />
                <p className="mt-1 text-xs text-gray-400">
                  💡 짧고 임팩트 있게 작성하세요 (권장: 10자 이내)
                </p>
              </div>

              {/* Narration */}
              <div>
                <label className="block text-white text-sm font-semibold mb-2">
                  내레이션 (음성)
                </label>
                <textarea
                  value={currentScene.narration || ''}
                  onChange={(e) => handleSceneChange(activeScene, 'narration', e.target.value)}
                  placeholder="AI 음성으로 읽을 대사를 입력하세요"
                  rows="3"
                  className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                />
                <p className="mt-1 text-xs text-gray-400">
                  💡 자연스러운 말투로 작성하세요
                </p>
              </div>

              {/* Image Prompt */}
              <div>
                <label className="block text-white text-sm font-semibold mb-2">
                  이미지 프롬프트
                </label>
                <textarea
                  value={currentScene.imagePrompt || ''}
                  onChange={(e) => handleSceneChange(activeScene, 'imagePrompt', e.target.value)}
                  placeholder="캐릭터가 어떤 모습으로 등장할지 설명하세요"
                  rows="3"
                  className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                />
                <p className="mt-1 text-xs text-gray-400">
                  💡 캐릭터의 표정, 포즈, 배경 등을 구체적으로 설명하세요
                </p>
              </div>

              {/* Duration */}
              <div>
                <label className="block text-white text-sm font-semibold mb-2">
                  재생 시간 (초)
                </label>
                <input
                  type="number"
                  min="2"
                  max="10"
                  step="0.5"
                  value={currentScene.duration || 3}
                  onChange={(e) => handleSceneChange(activeScene, 'duration', parseFloat(e.target.value))}
                  className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                />
                <p className="mt-1 text-xs text-gray-400">
                  💡 일반적으로 3~6초가 적당합니다
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Right: Preview */}
        <div className="space-y-4">
          <div className="bg-white/10 border border-white/20 rounded-xl p-6">
            <h3 className="text-xl font-bold text-white mb-4">미리보기</h3>
            
            {/* Phone Preview */}
            <div className="relative bg-black rounded-3xl overflow-hidden shadow-2xl" style={{ aspectRatio: '9/16' }}>
              <div className="absolute inset-0 flex flex-col items-center justify-center p-6">
                {/* Subtitle Preview */}
                <div className="absolute bottom-24 left-0 right-0 px-6">
                  <div className="bg-black/70 backdrop-blur-sm rounded-lg p-4 text-center">
                    <p className="text-yellow-400 font-bold text-2xl leading-tight" style={{ textShadow: '2px 2px 4px rgba(0,0,0,0.8)' }}>
                      {currentScene.subtitle || '자막 미리보기'}
                    </p>
                  </div>
                </div>

                {/* Narration Preview */}
                <div className="absolute inset-0 flex items-center justify-center p-6">
                  <div className="text-center">
                    <div className="text-6xl mb-4">🎭</div>
                    <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4 max-w-md">
                      <p className="text-white text-sm italic">
                        {currentScene.narration || '내레이션 미리보기'}
                      </p>
                    </div>
                  </div>
                </div>

                {/* Scene Info */}
                <div className="absolute top-4 left-4 right-4">
                  <div className="bg-black/50 backdrop-blur-sm rounded-lg px-3 py-2 text-xs text-white">
                    Scene {activeScene + 1} • {currentScene.duration || 3}s
                  </div>
                </div>
              </div>
            </div>

            {/* Scene Stats */}
            <div className="mt-4 grid grid-cols-3 gap-2">
              <div className="bg-white/5 rounded-lg p-3 text-center">
                <div className="text-gray-400 text-xs mb-1">자막</div>
                <div className="text-white font-bold">{currentScene.subtitle?.length || 0}자</div>
              </div>
              <div className="bg-white/5 rounded-lg p-3 text-center">
                <div className="text-gray-400 text-xs mb-1">내레이션</div>
                <div className="text-white font-bold">{currentScene.narration?.length || 0}자</div>
              </div>
              <div className="bg-white/5 rounded-lg p-3 text-center">
                <div className="text-gray-400 text-xs mb-1">재생시간</div>
                <div className="text-white font-bold">{currentScene.duration || 3}초</div>
              </div>
            </div>
          </div>

          {/* Total Stats */}
          <div className="bg-purple-500/10 border border-purple-500/30 rounded-xl p-4">
            <h4 className="text-white font-bold mb-3">전체 영상 정보</h4>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between text-gray-300">
                <span>총 장면 수:</span>
                <span className="text-white font-semibold">{displayScenes.length}개</span>
              </div>
              <div className="flex justify-between text-gray-300">
                <span>예상 재생 시간:</span>
                <span className="text-white font-semibold">
                  {displayScenes.reduce((sum, s) => sum + (s.duration || 3), 0).toFixed(1)}초
                </span>
              </div>
              <div className="flex justify-between text-gray-300">
                <span>예상 비용:</span>
                <span className="text-white font-semibold">약 $0.30</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <div className="flex justify-between">
        <button
          onClick={() => setActiveScene(Math.max(0, activeScene - 1))}
          disabled={activeScene === 0}
          className="px-6 py-3 bg-white/10 text-white rounded-lg hover:bg-white/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          ⬅️ 이전 장면
        </button>
        <button
          onClick={() => setActiveScene(Math.min(displayScenes.length - 1, activeScene + 1))}
          disabled={activeScene === displayScenes.length - 1}
          className="px-6 py-3 bg-white/10 text-white rounded-lg hover:bg-white/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          다음 장면 ➡️
        </button>
      </div>
    </div>
  );
}
