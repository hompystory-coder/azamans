import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { characters } from '../lib/characters';
import useStore from '../store/useStore';

function CharacterCard({ character, selected, onClick }) {
  return (
    <div
      onClick={onClick}
      className={`
        relative cursor-pointer transition-all duration-300 p-6 rounded-2xl shadow-lg border-4
        ${selected 
          ? 'border-purple-500 bg-purple-50 scale-105 shadow-purple-200' 
          : 'border-transparent bg-white hover:border-purple-200 hover:scale-102'
        }
      `}
    >
      {/* Selection Checkmark */}
      {selected && (
        <div className="absolute -top-3 -right-3 w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
          <svg className="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
          </svg>
        </div>
      )}

      {/* Character Icon */}
      <div className="text-6xl mb-4 text-center">
        {character.icon}
      </div>

      {/* Character Info */}
      <h3 className="text-lg font-bold text-gray-800 mb-2 text-center">
        {character.nameKr}
      </h3>
      <p className="text-sm text-gray-600 mb-4 text-center line-clamp-2 min-h-[40px]">
        {character.description}
      </p>

      {/* Suitable For Tags */}
      <div className="flex flex-wrap gap-2 justify-center">
        {character.suitableFor.slice(0, 2).map((tag) => (
          <span
            key={tag}
            className="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full"
          >
            {tag}
          </span>
        ))}
      </div>
    </div>
  );
}

export default function CharacterSelectionPage() {
  const navigate = useNavigate();
  const { selectedCharacter, setSelectedCharacter, contentMode } = useStore();
  
  const [localSelectedCharacter, setLocalSelectedCharacter] = useState(selectedCharacter);

  const handleSelectCharacter = (character) => {
    setLocalSelectedCharacter(character);
    setSelectedCharacter(character);
    console.log('🎭 캐릭터 선택:', character.nameKr);
  };

  const handleNext = () => {
    if (!localSelectedCharacter) return;
    
    // Navigate to script page
    navigate('/script');
  };

  const handleSkip = () => {
    // Skip character selection (for realistic-only mode)
    setSelectedCharacter(null);
    navigate('/script');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 p-8">
      {/* Header */}
      <div className="max-w-7xl mx-auto mb-12">
        <div className="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-white">
          <div className="flex items-center gap-3 mb-2">
            <span className="text-3xl">✨</span>
            <h1 className="text-4xl font-bold">AI 캐릭터 선택</h1>
          </div>
          <p className="text-purple-100 text-lg">
            쇼츠에 등장할 캐릭터를 선택하세요. 콘텐츠 스타일에 맞는 캐릭터를 추천해드립니다.
          </p>
          {contentMode === 'realistic' && (
            <div className="mt-4 p-4 bg-yellow-400 bg-opacity-20 rounded-lg">
              <p className="text-white font-semibold">
                💡 실사만 모드를 선택하셨습니다. 캐릭터 선택을 건너뛸 수 있습니다.
              </p>
            </div>
          )}
        </div>
      </div>

      {/* Character Grid */}
      <div className="max-w-7xl mx-auto">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
          {characters.map((character, index) => (
            <CharacterCard
              key={character.id}
              character={character}
              selected={localSelectedCharacter?.id === character.id}
              onClick={() => handleSelectCharacter(character)}
            />
          ))}
        </div>

        {/* Selected Character Detail */}
        {localSelectedCharacter && (
          <div className="bg-white rounded-2xl p-8 shadow-xl border-2 border-purple-200 mb-8 animate-fade-in">
            <div className="flex items-start gap-6">
              <div className="text-8xl">{localSelectedCharacter.icon}</div>
              <div className="flex-1">
                <h2 className="text-3xl font-bold text-gray-800 mb-2">
                  {localSelectedCharacter.nameKr}
                  <span className="text-gray-400 text-xl ml-3">
                    ({localSelectedCharacter.name})
                  </span>
                </h2>
                <p className="text-gray-600 mb-4 text-lg">
                  {localSelectedCharacter.description}
                </p>
                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <span className="text-sm font-semibold text-gray-500">음성 스타일</span>
                    <p className="text-gray-800">{localSelectedCharacter.voiceStyle}</p>
                  </div>
                  <div>
                    <span className="text-sm font-semibold text-gray-500">톤</span>
                    <p className="text-gray-800">{localSelectedCharacter.tone}</p>
                  </div>
                </div>
                <div>
                  <span className="text-sm font-semibold text-gray-500 block mb-2">
                    적합한 콘텐츠
                  </span>
                  <div className="flex flex-wrap gap-2">
                    {localSelectedCharacter.suitableFor.map((tag) => (
                      <span
                        key={tag}
                        className="px-3 py-1 bg-purple-100 text-purple-700 rounded-full"
                      >
                        {tag}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Action Buttons */}
        <div className="flex justify-between items-center">
          <button
            onClick={() => navigate('/script')}
            className="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors"
          >
            ← 이전 단계
          </button>

          <div className="flex gap-4">
            {contentMode === 'realistic' && (
              <button
                onClick={handleSkip}
                className="px-6 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-colors"
              >
                캐릭터 건너뛰기
              </button>
            )}
            <button
              onClick={handleNext}
              disabled={!localSelectedCharacter}
              className={`
                flex items-center gap-2 px-8 py-3 rounded-lg text-lg font-semibold transition-all
                ${localSelectedCharacter
                  ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:shadow-xl hover:scale-105'
                  : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                }
              `}
            >
              다음 단계로
              <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
