import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Sparkles, Check, ArrowRight } from 'lucide-react';
import { characters, Character } from '../lib/characters';

interface CharacterSelectionPageProps {
  onSelect: (character: Character) => void;
  onNext: () => void;
}

export default function CharacterSelectionPage({ onSelect, onNext }: CharacterSelectionPageProps) {
  const [selectedCharacter, setSelectedCharacter] = useState<Character | null>(null);

  const handleSelectCharacter = (character: Character) => {
    setSelectedCharacter(character);
    onSelect(character);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 p-8">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="max-w-7xl mx-auto mb-12"
      >
        <div className="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-white">
          <div className="flex items-center gap-3 mb-2">
            <Sparkles className="w-8 h-8" />
            <h1 className="text-4xl font-bold">AI 캐릭터 선택</h1>
          </div>
          <p className="text-purple-100 text-lg">
            쇼츠에 등장할 캐릭터를 선택하세요. 콘텐츠 스타일에 맞는 캐릭터를 추천해드립니다.
          </p>
        </div>
      </motion.div>

      {/* Character Grid */}
      <div className="max-w-7xl mx-auto">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
          {characters.map((character, index) => (
            <motion.div
              key={character.id}
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: index * 0.05 }}
              className={`relative cursor-pointer transition-all duration-300 ${
                selectedCharacter?.id === character.id
                  ? 'scale-105'
                  : 'hover:scale-102'
              }`}
              onClick={() => handleSelectCharacter(character)}
            >
              <div
                className={`bg-white rounded-2xl p-6 shadow-lg border-4 transition-all ${
                  selectedCharacter?.id === character.id
                    ? 'border-purple-500 shadow-purple-200'
                    : 'border-transparent hover:border-purple-200'
                }`}
              >
                {/* Selection Checkmark */}
                {selectedCharacter?.id === character.id && (
                  <motion.div
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    className="absolute -top-3 -right-3 w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center shadow-lg"
                  >
                    <Check className="w-6 h-6 text-white" />
                  </motion.div>
                )}

                {/* Character Icon */}
                <div className="text-6xl mb-4 text-center">
                  {character.icon}
                </div>

                {/* Character Info */}
                <h3 className="text-lg font-bold text-gray-800 mb-2 text-center">
                  {character.nameKr}
                </h3>
                <p className="text-sm text-gray-600 mb-4 text-center line-clamp-2">
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
            </motion.div>
          ))}
        </div>

        {/* Selected Character Detail */}
        {selectedCharacter && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white rounded-2xl p-8 shadow-xl border-2 border-purple-200 mb-8"
          >
            <div className="flex items-start gap-6">
              <div className="text-8xl">{selectedCharacter.icon}</div>
              <div className="flex-1">
                <h2 className="text-3xl font-bold text-gray-800 mb-2">
                  {selectedCharacter.nameKr}
                  <span className="text-gray-400 text-xl ml-3">
                    ({selectedCharacter.name})
                  </span>
                </h2>
                <p className="text-gray-600 mb-4 text-lg">
                  {selectedCharacter.description}
                </p>
                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <span className="text-sm font-semibold text-gray-500">음성 스타일</span>
                    <p className="text-gray-800">{selectedCharacter.voiceStyle}</p>
                  </div>
                  <div>
                    <span className="text-sm font-semibold text-gray-500">톤</span>
                    <p className="text-gray-800">{selectedCharacter.tone}</p>
                  </div>
                </div>
                <div>
                  <span className="text-sm font-semibold text-gray-500 block mb-2">
                    적합한 콘텐츠
                  </span>
                  <div className="flex flex-wrap gap-2">
                    {selectedCharacter.suitableFor.map((tag) => (
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
          </motion.div>
        )}

        {/* Next Button */}
        <div className="flex justify-end">
          <button
            onClick={onNext}
            disabled={!selectedCharacter}
            className={`flex items-center gap-2 px-8 py-4 rounded-xl text-lg font-semibold transition-all ${
              selectedCharacter
                ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:shadow-xl hover:scale-105'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
            }`}
          >
            다음 단계로
            <ArrowRight className="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  );
}
