import { useState, useEffect, useRef } from 'react';
import { useAppStore } from '../store/appStore';
import { voicesAPI } from '../utils/api';

export default function VoiceSelector() {
  const [voices, setVoices] = useState([]);
  const [playing, setPlaying] = useState(null);
  const audioRef = useRef(null);
  const { selectedVoice, setSelectedVoice } = useAppStore();

  useEffect(() => {
    loadVoices();
  }, []);

  const loadVoices = async () => {
    try {
      const response = await voicesAPI.getAll();
      setVoices(response.data.data || []);
    } catch (error) {
      console.error('Failed to load voices:', error);
    }
  };

  const handlePlay = async (voiceId) => {
    try {
      if (playing === voiceId) {
        // Stop current audio
        if (audioRef.current) {
          audioRef.current.pause();
          audioRef.current = null;
        }
        setPlaying(null);
        return;
      }

      // Generate sample audio
      const response = await voicesAPI.generateSample(voiceId);
      const audioUrl = response.data.data.url;

      // Play audio
      if (audioRef.current) {
        audioRef.current.pause();
      }
      
      const audio = new Audio(audioUrl);
      audio.onended = () => setPlaying(null);
      audio.play();
      
      audioRef.current = audio;
      setPlaying(voiceId);
    } catch (error) {
      console.error('Failed to play voice sample:', error);
    }
  };

  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold text-white">음성 선택</h2>
      <p className="text-gray-300">
        영상 내레이션에 사용할 AI 음성을 선택하세요. 미리듣기로 각 음성의 특징을 확인할 수 있습니다.
      </p>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {voices.map((voice) => (
          <div
            key={voice.id}
            onClick={() => setSelectedVoice(voice.id)}
            className={`p-6 rounded-xl border-2 cursor-pointer transition-all ${
              selectedVoice === voice.id
                ? 'border-purple-500 bg-purple-500/20 shadow-lg'
                : 'border-white/20 bg-white/5 hover:bg-white/10'
            }`}
          >
            <div className="flex items-start justify-between mb-4">
              <div className="flex-1">
                <div className="flex items-center space-x-2 mb-2">
                  <span className="text-3xl">{voice.gender === 'female' ? '👩' : '👨'}</span>
                  <div>
                    <div className="text-white font-bold text-lg">{voice.name}</div>
                    <div className="text-gray-400 text-sm">{voice.language} • {voice.style}</div>
                  </div>
                </div>
                <p className="text-gray-300 text-sm mb-3">
                  {voice.description}
                </p>
                <div className="flex flex-wrap gap-2">
                  {voice.tags?.map((tag) => (
                    <span
                      key={tag}
                      className="px-2 py-1 bg-white/10 text-white text-xs rounded-full"
                    >
                      {tag}
                    </span>
                  ))}
                </div>
              </div>
              
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  handlePlay(voice.id);
                }}
                className="ml-4 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors flex items-center space-x-2"
              >
                {playing === voice.id ? (
                  <>
                    <span>⏸</span>
                    <span>정지</span>
                  </>
                ) : (
                  <>
                    <span>▶️</span>
                    <span>미리듣기</span>
                  </>
                )}
              </button>
            </div>

            {/* Voice characteristics */}
            <div className="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-white/10">
              <div className="text-center">
                <div className="text-xs text-gray-400 mb-1">톤</div>
                <div className="text-white text-sm font-semibold">{voice.tone}</div>
              </div>
              <div className="text-center">
                <div className="text-xs text-gray-400 mb-1">속도</div>
                <div className="text-white text-sm font-semibold">{voice.speed}</div>
              </div>
              <div className="text-center">
                <div className="text-xs text-gray-400 mb-1">감정</div>
                <div className="text-white text-sm font-semibold">{voice.emotion}</div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {selectedVoice && (
        <div className="mt-6 p-6 bg-purple-500/10 border border-purple-500/30 rounded-xl">
          <h3 className="text-white font-bold text-lg mb-2">✅ 선택된 음성</h3>
          <div className="text-gray-300">
            {voices.find(v => v.id === selectedVoice)?.name} - {voices.find(v => v.id === selectedVoice)?.description}
          </div>
        </div>
      )}
    </div>
  );
}
