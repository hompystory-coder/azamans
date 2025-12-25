'use client';

import { useState, useEffect } from 'react';
import { musicLibrary, type MusicTrack } from '@/lib/music-library';

export default function MusicLibraryPage() {
  const [tracks, setTracks] = useState<MusicTrack[]>([]);
  const [filteredTracks, setFilteredTracks] = useState<MusicTrack[]>([]);
  const [selectedGenre, setSelectedGenre] = useState<string>('All');
  const [selectedMood, setSelectedMood] = useState<string>('All');
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [playingTrack, setPlayingTrack] = useState<string | null>(null);
  const [selectedTrack, setSelectedTrack] = useState<MusicTrack | null>(null);
  
  const genres = ['All', ...musicLibrary.constructor.getGenres()];
  const moods = ['All', ...musicLibrary.constructor.getMoods()];
  
  useEffect(() => {
    // 모든 트랙 로드
    const allTracks = musicLibrary.getAllTracks();
    setTracks(allTracks);
    setFilteredTracks(allTracks);
  }, []);
  
  useEffect(() => {
    // 필터링
    let filtered = tracks;
    
    if (selectedGenre !== 'All') {
      filtered = filtered.filter(track => track.genre === selectedGenre);
    }
    
    if (selectedMood !== 'All') {
      filtered = filtered.filter(track => track.mood === selectedMood);
    }
    
    if (searchTerm) {
      filtered = filtered.filter(track =>
        track.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        track.artist.toLowerCase().includes(searchTerm.toLowerCase()) ||
        track.tags.some(tag => tag.toLowerCase().includes(searchTerm.toLowerCase()))
      );
    }
    
    setFilteredTracks(filtered);
  }, [selectedGenre, selectedMood, searchTerm, tracks]);
  
  const handlePreview = async (trackId: string) => {
    if (playingTrack === trackId) {
      musicLibrary.stopPreview();
      setPlayingTrack(null);
    } else {
      try {
        await musicLibrary.preview(trackId, 0.5);
        setPlayingTrack(trackId);
        
        // 30초 후 자동 정지
        setTimeout(() => {
          musicLibrary.stopPreview();
          setPlayingTrack(null);
        }, 30000);
      } catch (error) {
        console.error('Failed to preview:', error);
      }
    }
  };
  
  const handleSelectTrack = (track: MusicTrack) => {
    setSelectedTrack(track);
  };
  
  const handleDownloadTrack = async () => {
    if (!selectedTrack) return;
    
    try {
      const blob = await musicLibrary.addToVideo(selectedTrack.id, {
        volume: 0.3,
        fadeIn: 2,
        fadeOut: 2
      });
      
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${selectedTrack.name}.wav`;
      a.click();
    } catch (error) {
      console.error('Failed to download:', error);
    }
  };
  
  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 text-white p-8">
      {/* 헤더 */}
      <div className="max-w-7xl mx-auto mb-8">
        <h1 className="text-5xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-purple-400">
          🎵 음악 라이브러리
        </h1>
        <p className="text-xl text-gray-300">
          로열티 프리 배경 음악 40+ 트랙
        </p>
      </div>

      <div className="max-w-7xl mx-auto">
        {/* 필터 및 검색 */}
        <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 mb-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            {/* 검색 */}
            <div>
              <label className="block text-sm font-semibold mb-2">🔍 검색</label>
              <input
                type="text"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                placeholder="트랙 이름, 아티스트, 태그..."
                className="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
              />
            </div>
            
            {/* 장르 필터 */}
            <div>
              <label className="block text-sm font-semibold mb-2">🎸 장르</label>
              <select
                value={selectedGenre}
                onChange={(e) => setSelectedGenre(e.target.value)}
                className="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                {genres.map(genre => (
                  <option key={genre} value={genre} className="bg-gray-900">
                    {genre}
                  </option>
                ))}
              </select>
            </div>
            
            {/* 무드 필터 */}
            <div>
              <label className="block text-sm font-semibold mb-2">😊 무드</label>
              <select
                value={selectedMood}
                onChange={(e) => setSelectedMood(e.target.value)}
                className="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                {moods.map(mood => (
                  <option key={mood} value={mood} className="bg-gray-900">
                    {mood}
                  </option>
                ))}
              </select>
            </div>
          </div>
          
          {/* 결과 카운트 */}
          <div className="text-sm text-gray-400">
            {filteredTracks.length}개의 트랙 발견
          </div>
        </div>

        {/* 트랙 그리드 */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          {filteredTracks.map((track) => (
            <div
              key={track.id}
              className={`bg-white/10 backdrop-blur-sm rounded-xl p-6 border-2 transition-all cursor-pointer ${
                selectedTrack?.id === track.id
                  ? 'border-purple-400 bg-purple-500/20'
                  : 'border-white/20 hover:border-white/40'
              }`}
              onClick={() => handleSelectTrack(track)}
            >
              {/* 썸네일 */}
              <div className="w-full aspect-square bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg mb-4 flex items-center justify-center text-6xl">
                🎵
              </div>
              
              {/* 트랙 정보 */}
              <h3 className="text-xl font-bold mb-2">{track.name}</h3>
              <p className="text-sm text-gray-400 mb-3">{track.artist}</p>
              
              {/* 메타데이터 */}
              <div className="flex items-center gap-2 mb-3 text-sm">
                <span className="px-2 py-1 bg-purple-600/50 rounded-full">
                  {track.genre}
                </span>
                <span className="px-2 py-1 bg-blue-600/50 rounded-full">
                  {track.mood}
                </span>
              </div>
              
              <div className="flex items-center gap-2 text-xs text-gray-400 mb-4">
                <span>⏱️ {Math.floor(track.duration / 60)}:{(track.duration % 60).toString().padStart(2, '0')}</span>
                <span>•</span>
                <span>🎼 {track.bpm} BPM</span>
              </div>
              
              {/* 태그 */}
              <div className="flex flex-wrap gap-1 mb-4">
                {track.tags.map(tag => (
                  <span key={tag} className="text-xs px-2 py-1 bg-white/10 rounded-full">
                    #{tag}
                  </span>
                ))}
              </div>
              
              {/* 버튼 */}
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  handlePreview(track.id);
                }}
                className={`w-full font-bold py-2 px-4 rounded-lg transition-all ${
                  playingTrack === track.id
                    ? 'bg-red-600 hover:bg-red-700'
                    : 'bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700'
                }`}
              >
                {playingTrack === track.id ? '⏸️ 정지' : '▶️ 미리듣기'}
              </button>
            </div>
          ))}
        </div>

        {/* 선택된 트랙 상세 */}
        {selectedTrack && (
          <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
            <h2 className="text-3xl font-bold mb-4">선택된 트랙</h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {/* 왼쪽: 정보 */}
              <div>
                <div className="mb-4">
                  <h3 className="text-2xl font-bold">{selectedTrack.name}</h3>
                  <p className="text-lg text-gray-400">{selectedTrack.artist}</p>
                </div>
                
                <div className="space-y-2 mb-6">
                  <div className="flex items-center gap-3">
                    <span className="text-gray-400 w-24">장르:</span>
                    <span className="font-semibold">{selectedTrack.genre}</span>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="text-gray-400 w-24">무드:</span>
                    <span className="font-semibold">{selectedTrack.mood}</span>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="text-gray-400 w-24">길이:</span>
                    <span className="font-semibold">
                      {Math.floor(selectedTrack.duration / 60)}분 {selectedTrack.duration % 60}초
                    </span>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="text-gray-400 w-24">BPM:</span>
                    <span className="font-semibold">{selectedTrack.bpm}</span>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="text-gray-400 w-24">라이선스:</span>
                    <span className="font-semibold capitalize">{selectedTrack.license}</span>
                  </div>
                </div>
                
                <div className="mb-6">
                  <span className="text-gray-400 mb-2 block">태그:</span>
                  <div className="flex flex-wrap gap-2">
                    {selectedTrack.tags.map(tag => (
                      <span key={tag} className="px-3 py-1 bg-purple-600/50 rounded-full text-sm">
                        #{tag}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
              
              {/* 오른쪽: 액션 */}
              <div className="space-y-4">
                <button
                  onClick={() => handlePreview(selectedTrack.id)}
                  className={`w-full font-bold py-4 px-6 rounded-xl transition-all text-lg ${
                    playingTrack === selectedTrack.id
                      ? 'bg-red-600 hover:bg-red-700'
                      : 'bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700'
                  }`}
                >
                  {playingTrack === selectedTrack.id ? '⏸️ 미리듣기 정지' : '▶️ 전체 미리듣기'}
                </button>
                
                <button
                  onClick={handleDownloadTrack}
                  className="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl transition-all text-lg"
                >
                  💾 WAV로 다운로드
                </button>
                
                <button
                  className="w-full bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-white font-bold py-4 px-6 rounded-xl transition-all text-lg"
                >
                  🎬 비디오에 추가
                </button>
                
                <div className="bg-white/5 rounded-xl p-4 border border-white/10">
                  <p className="text-sm text-gray-400">
                    💡 <strong>팁:</strong> 이 음악은 로열티 프리로 상업적 용도로 사용 가능합니다.
                    크레딧 표시는 선택사항입니다.
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
