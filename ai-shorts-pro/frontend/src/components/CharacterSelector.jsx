import { useState, useEffect } from 'react';
import { useAppStore } from '../store/appStore';
import { charactersAPI } from '../utils/api';

export default function CharacterSelector() {
  const [characters, setCharacters] = useState([]);
  const { selectedCharacter, setSelectedCharacter } = useAppStore();

  useEffect(() => {
    loadCharacters();
  }, []);

  const loadCharacters = async () => {
    try {
      const response = await charactersAPI.getAll();
      setCharacters(response.data.data || []);
    } catch (error) {
      console.error('Failed to load characters:', error);
    }
  };

  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold text-white">캐릭터 선택</h2>
      <p className="text-gray-300">
        영상에 사용할 캐릭터를 선택하세요. 각 캐릭터는 특정 카테고리에 최적화되어 있습니다.
      </p>

      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {characters.map((character) => (
          <button
            key={character.id}
            onClick={() => setSelectedCharacter(character.id)}
            className={`p-4 rounded-xl border-2 transition-all ${
              selectedCharacter === character.id
                ? 'border-purple-500 bg-purple-500/20 shadow-lg scale-105'
                : 'border-white/20 bg-white/5 hover:bg-white/10 hover:scale-102'
            }`}
          >
            <div className="text-5xl mb-2">{character.emoji}</div>
            <div className="text-white font-bold text-sm">{character.name}</div>
            <div className="text-gray-400 text-xs mt-1">{character.description}</div>
          </button>
        ))}
      </div>

      {selectedCharacter && (
        <div className="mt-6 p-6 bg-purple-500/10 border border-purple-500/30 rounded-xl">
          <h3 className="text-white font-bold text-lg mb-2">선택된 캐릭터</h3>
          <div className="text-gray-300">
            {characters.find(c => c.id === selectedCharacter)?.description}
          </div>
        </div>
      )}
    </div>
  );
}
