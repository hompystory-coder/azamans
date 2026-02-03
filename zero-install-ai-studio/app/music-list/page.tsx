'use client';

import { useState, useEffect } from 'react';
import { Play, Pause, Download, Calendar, FolderOpen, Music } from 'lucide-react';

interface MusicFile {
  filename: string;
  path: string;
  url: string;
  size: number;
  createdAt: string;
  directory: string;
}

interface MusicListResponse {
  success: boolean;
  total: number;
  limit: number;
  offset: number;
  files: MusicFile[];
  filters: {
    year: string | null;
    month: string | null;
  };
  error?: string;
}

export default function MusicListPage() {
  const [musicFiles, setMusicFiles] = useState<MusicFile[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [total, setTotal] = useState(0);
  const [currentlyPlaying, setCurrentlyPlaying] = useState<string | null>(null);
  const [audioElement, setAudioElement] = useState<HTMLAudioElement | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedYear, setSelectedYear] = useState<string>('');
  const [selectedMonth, setSelectedMonth] = useState<string>('');

  useEffect(() => {
    fetchMusicFiles();
  }, [selectedYear, selectedMonth]);

  const fetchMusicFiles = async () => {
    setLoading(true);
    setError(null);

    try {
      let url = '/api/music-list?limit=1000';
      if (selectedYear) {
        url += `&year=${selectedYear}`;
        if (selectedMonth) {
          url += `&month=${selectedMonth}`;
        }
      }

      const response = await fetch(url);
      const data: MusicListResponse = await response.json();

      if (data.success) {
        setMusicFiles(data.files);
        setTotal(data.total);
      } else {
        setError(data.error || 'Failed to load music files');
      }
    } catch (err: any) {
      setError(err.message || 'An error occurred');
    } finally {
      setLoading(false);
    }
  };

  const handlePlayPause = (url: string) => {
    if (currentlyPlaying === url) {
      // Pause
      if (audioElement) {
        audioElement.pause();
        setCurrentlyPlaying(null);
      }
    } else {
      // Play new track
      if (audioElement) {
        audioElement.pause();
      }

      const audio = new Audio(url);
      audio.play();
      setAudioElement(audio);
      setCurrentlyPlaying(url);

      audio.onended = () => {
        setCurrentlyPlaying(null);
      };
    }
  };

  const handleDownload = (file: MusicFile) => {
    const link = document.createElement('a');
    link.href = file.url;
    link.download = file.filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  };

  const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleString('ko-KR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const filteredFiles = musicFiles.filter((file) =>
    file.filename.toLowerCase().includes(searchQuery.toLowerCase()) ||
    file.directory.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const years = ['2024', '2025', '2026'];
  const months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <Music className="w-8 h-8 text-purple-600" />
              <h1 className="text-3xl font-bold text-gray-900">음악 파일 라이브러리</h1>
            </div>
            <div className="text-sm text-gray-500">
              총 {total}개의 음악 파일
            </div>
          </div>
        </div>
      </div>

      {/* Filters */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {/* Search */}
            <div className="md:col-span-2">
              <input
                type="text"
                placeholder="파일명 또는 폴더명 검색..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>

            {/* Year Filter */}
            <div>
              <select
                value={selectedYear}
                onChange={(e) => {
                  setSelectedYear(e.target.value);
                  setSelectedMonth('');
                }}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="">전체 연도</option>
                {years.map((year) => (
                  <option key={year} value={year}>
                    {year}년
                  </option>
                ))}
              </select>
            </div>

            {/* Month Filter */}
            <div>
              <select
                value={selectedMonth}
                onChange={(e) => setSelectedMonth(e.target.value)}
                disabled={!selectedYear}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option value="">전체 월</option>
                {months.map((month) => (
                  <option key={month} value={month}>
                    {month}월
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>

        {/* Loading State */}
        {loading && (
          <div className="text-center py-12">
            <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
            <p className="mt-4 text-gray-600">음악 파일을 불러오는 중...</p>
          </div>
        )}

        {/* Error State */}
        {error && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
            <p className="font-semibold">오류 발생</p>
            <p className="text-sm mt-1">{error}</p>
          </div>
        )}

        {/* Music List */}
        {!loading && !error && (
          <>
            {filteredFiles.length === 0 ? (
              <div className="bg-white rounded-lg shadow-sm p-12 text-center">
                <Music className="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <p className="text-gray-500 text-lg">음악 파일이 없습니다</p>
              </div>
            ) : (
              <div className="bg-white rounded-lg shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          재생
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          파일명
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          폴더
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          크기
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          생성일
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          다운로드
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {filteredFiles.map((file, index) => (
                        <tr
                          key={`${file.path}-${index}`}
                          className="hover:bg-gray-50 transition-colors"
                        >
                          <td className="px-6 py-4 whitespace-nowrap">
                            <button
                              onClick={() => handlePlayPause(file.url)}
                              className="p-2 rounded-full hover:bg-purple-100 transition-colors"
                            >
                              {currentlyPlaying === file.url ? (
                                <Pause className="w-5 h-5 text-purple-600" />
                              ) : (
                                <Play className="w-5 h-5 text-purple-600" />
                              )}
                            </button>
                          </td>
                          <td className="px-6 py-4">
                            <div className="text-sm font-medium text-gray-900 truncate max-w-xs">
                              {file.filename}
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <div className="flex items-center text-sm text-gray-500">
                              <FolderOpen className="w-4 h-4 mr-1" />
                              <span className="truncate max-w-xs">{file.directory}</span>
                            </div>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {formatFileSize(file.size)}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div className="flex items-center">
                              <Calendar className="w-4 h-4 mr-1" />
                              {formatDate(file.createdAt)}
                            </div>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <button
                              onClick={() => handleDownload(file)}
                              className="p-2 rounded-full hover:bg-blue-100 transition-colors"
                            >
                              <Download className="w-5 h-5 text-blue-600" />
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
