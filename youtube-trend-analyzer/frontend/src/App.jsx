import { useState, useEffect } from 'react';
import axios from 'axios';
import { format } from 'date-fns';
import './App.css';

const API_BASE_URL = '/azaman/api';

function App() {
  const [videos, setVideos] = useState([]);
  const [keywords, setKeywords] = useState([]);
  const [selectedKeyword, setSelectedKeyword] = useState('');
  const [stats, setStats] = useState({});
  const [logs, setLogs] = useState([]);
  const [settings, setSettings] = useState({});
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [pagination, setPagination] = useState({});
  const [activeTab, setActiveTab] = useState('videos');
  const [searchKeyword, setSearchKeyword] = useState('');
  const [apiKey, setApiKey] = useState('');

  // 초기 데이터 로드
  useEffect(() => {
    loadKeywords();
    loadStats();
    loadSettings();
  }, []);

  // 비디오 로드
  useEffect(() => {
    loadVideos();
  }, [selectedKeyword, currentPage]);

  const loadVideos = async () => {
    setLoading(true);
    try {
      const params = { page: currentPage, limit: 50 };
      if (selectedKeyword) params.keyword = selectedKeyword;

      const response = await axios.get(`${API_BASE_URL}/videos`, { params });
      setVideos(response.data.videos);
      setPagination(response.data.pagination);
    } catch (error) {
      console.error('Error loading videos:', error);
    }
    setLoading(false);
  };

  const loadKeywords = async () => {
    try {
      const response = await axios.get(`${API_BASE_URL}/keywords`);
      setKeywords(response.data);
    } catch (error) {
      console.error('Error loading keywords:', error);
    }
  };

  const loadStats = async () => {
    try {
      const response = await axios.get(`${API_BASE_URL}/stats`);
      setStats(response.data);
    } catch (error) {
      console.error('Error loading stats:', error);
    }
  };

  const loadLogs = async () => {
    try {
      const response = await axios.get(`${API_BASE_URL}/logs`);
      setLogs(response.data);
    } catch (error) {
      console.error('Error loading logs:', error);
    }
  };

  const loadSettings = async () => {
    try {
      const response = await axios.get(`${API_BASE_URL}/settings`);
      setSettings(response.data);
      setApiKey(response.data.youtube_api_key || '');
    } catch (error) {
      console.error('Error loading settings:', error);
    }
  };

  const handleManualSearch = async () => {
    if (!searchKeyword || !apiKey) {
      alert('검색어와 API 키를 입력해주세요.');
      return;
    }

    setLoading(true);
    try {
      const response = await axios.post(`${API_BASE_URL}/search`, {
        keyword: searchKeyword,
        apiKey: apiKey
      });
      alert(`${response.data.count}개의 영상을 찾았습니다!`);
      loadVideos();
      loadKeywords();
      loadStats();
      setSearchKeyword('');
    } catch (error) {
      alert('검색 중 오류가 발생했습니다: ' + error.message);
    }
    setLoading(false);
  };

  const handleSaveApiKey = async () => {
    try {
      await axios.post(`${API_BASE_URL}/settings`, {
        key: 'youtube_api_key',
        value: apiKey
      });
      alert('API 키가 저장되었습니다!');
    } catch (error) {
      alert('API 키 저장 실패: ' + error.message);
    }
  };

  const handleDeleteVideos = async (keyword) => {
    if (!confirm(`"${keyword}" 키워드의 모든 영상을 삭제하시겠습니까?`)) return;

    try {
      await axios.delete(`${API_BASE_URL}/videos`, { params: { keyword } });
      alert('삭제되었습니다.');
      loadVideos();
      loadKeywords();
      loadStats();
    } catch (error) {
      alert('삭제 실패: ' + error.message);
    }
  };

  const formatNumber = (num) => {
    if (!num) return '0';
    return new Intl.NumberFormat('ko-KR').format(num);
  };

  const formatDate = (dateString) => {
    if (!dateString) return '-';
    try {
      return format(new Date(dateString), 'yyyy-MM-dd HH:mm');
    } catch {
      return dateString;
    }
  };

  return (
    <div className="app">
      <header className="header">
        <h1>📊 YouTube 트렌드 분석기</h1>
        <p className="subtitle">시니어 대상 유튜브 영상 자동 수집 및 분석 시스템</p>
      </header>

      {/* 통계 대시보드 */}
      <div className="stats-container">
        <div className="stat-card">
          <div className="stat-value">{formatNumber(stats.totalVideos)}</div>
          <div className="stat-label">총 영상 수</div>
        </div>
        <div className="stat-card">
          <div className="stat-value">{formatNumber(stats.totalKeywords)}</div>
          <div className="stat-label">검색 키워드</div>
        </div>
        <div className="stat-card">
          <div className="stat-value">{formatNumber(stats.totalSearches)}</div>
          <div className="stat-label">검색 횟수</div>
        </div>
        <div className="stat-card">
          <div className="stat-value">{formatDate(stats.lastSearch)}</div>
          <div className="stat-label">마지막 검색</div>
        </div>
      </div>

      {/* 탭 네비게이션 */}
      <div className="tabs">
        <button
          className={`tab ${activeTab === 'videos' ? 'active' : ''}`}
          onClick={() => setActiveTab('videos')}
        >
          📹 영상 목록
        </button>
        <button
          className={`tab ${activeTab === 'search' ? 'active' : ''}`}
          onClick={() => setActiveTab('search')}
        >
          🔍 수동 검색
        </button>
        <button
          className={`tab ${activeTab === 'logs' ? 'active' : ''}`}
          onClick={() => {
            setActiveTab('logs');
            loadLogs();
          }}
        >
          📝 검색 로그
        </button>
        <button
          className={`tab ${activeTab === 'settings' ? 'active' : ''}`}
          onClick={() => setActiveTab('settings')}
        >
          ⚙️ 설정
        </button>
      </div>

      {/* 영상 목록 탭 */}
      {activeTab === 'videos' && (
        <div className="content">
          <div className="filter-bar">
            <select
              value={selectedKeyword}
              onChange={(e) => {
                setSelectedKeyword(e.target.value);
                setCurrentPage(1);
              }}
              className="keyword-select"
            >
              <option value="">전체 키워드</option>
              {keywords.map((keyword, idx) => (
                <option key={idx} value={keyword}>
                  {keyword}
                </option>
              ))}
            </select>
            {selectedKeyword && (
              <button
                onClick={() => handleDeleteVideos(selectedKeyword)}
                className="btn btn-danger"
              >
                삭제
              </button>
            )}
            <button onClick={loadVideos} className="btn btn-primary">
              새로고침
            </button>
          </div>

          {loading ? (
            <div className="loading">로딩 중...</div>
          ) : (
            <>
              <div className="video-grid">
                {videos.map((video) => (
                  <div key={video.id} className="video-card">
                    <a
                      href={video.video_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="video-thumbnail"
                    >
                      <img src={video.thumbnail_url} alt={video.video_title} />
                    </a>
                    <div className="video-info">
                      <h3 className="video-title">
                        <a
                          href={video.video_url}
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          {video.video_title}
                        </a>
                      </h3>
                      <div className="video-meta">
                        <div className="channel-name">📺 {video.channel_name}</div>
                        <div className="video-stats">
                          <span>👁️ {formatNumber(video.view_count)}</span>
                          <span>👥 구독자 {formatNumber(video.subscriber_count)}</span>
                        </div>
                        <div className="video-keyword">🏷️ {video.keyword}</div>
                        <div className="video-date">📅 {formatDate(video.published_at)}</div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* 페이지네이션 */}
              {pagination.totalPages > 1 && (
                <div className="pagination">
                  <button
                    onClick={() => setCurrentPage(currentPage - 1)}
                    disabled={currentPage === 1}
                    className="btn"
                  >
                    이전
                  </button>
                  <span className="page-info">
                    {currentPage} / {pagination.totalPages}
                  </span>
                  <button
                    onClick={() => setCurrentPage(currentPage + 1)}
                    disabled={currentPage === pagination.totalPages}
                    className="btn"
                  >
                    다음
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      )}

      {/* 수동 검색 탭 */}
      {activeTab === 'search' && (
        <div className="content">
          <div className="search-form">
            <h2>🔍 수동 검색</h2>
            <div className="form-group">
              <label>검색 키워드:</label>
              <input
                type="text"
                value={searchKeyword}
                onChange={(e) => setSearchKeyword(e.target.value)}
                placeholder="예: 시니어대상 한국 시니어 사연"
                className="input"
              />
            </div>
            <div className="form-group">
              <label>YouTube API Key:</label>
              <input
                type="text"
                value={apiKey}
                onChange={(e) => setApiKey(e.target.value)}
                placeholder="YouTube Data API v3 키를 입력하세요"
                className="input"
              />
            </div>
            <div className="button-group">
              <button
                onClick={handleManualSearch}
                disabled={loading}
                className="btn btn-primary"
              >
                {loading ? '검색 중...' : '검색 시작'}
              </button>
              <button onClick={handleSaveApiKey} className="btn btn-secondary">
                API 키 저장
              </button>
            </div>
            <div className="info-box">
              <h3>💡 자동 검색 키워드 (매일 오전 6시 실행)</h3>
              <ul>
                <li>시니어대상 한국 시니어 사연</li>
                <li>한국 시니어 대상 해외감동사연</li>
                <li>한국시니어대상 극복</li>
                <li>한국시니어 대상 북한</li>
                <li>한국 시니어 대상 디지털정보</li>
              </ul>
            </div>
          </div>
        </div>
      )}

      {/* 검색 로그 탭 */}
      {activeTab === 'logs' && (
        <div className="content">
          <h2>📝 검색 로그</h2>
          <table className="log-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>키워드</th>
                <th>영상 수</th>
                <th>상태</th>
                <th>검색 시간</th>
              </tr>
            </thead>
            <tbody>
              {logs.map((log) => (
                <tr key={log.id}>
                  <td>{log.id}</td>
                  <td>{log.keyword}</td>
                  <td>{formatNumber(log.video_count)}</td>
                  <td>
                    <span className={`status ${log.status}`}>
                      {log.status === 'success' ? '✅ 성공' : '❌ 실패'}
                    </span>
                  </td>
                  <td>{formatDate(log.searched_at)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* 설정 탭 */}
      {activeTab === 'settings' && (
        <div className="content">
          <div className="settings-form">
            <h2>⚙️ 시스템 설정</h2>
            <div className="form-group">
              <label>YouTube API Key:</label>
              <input
                type="text"
                value={apiKey}
                onChange={(e) => setApiKey(e.target.value)}
                className="input"
              />
              <button onClick={handleSaveApiKey} className="btn btn-primary">
                저장
              </button>
            </div>
            <div className="info-box">
              <h3>📌 API 키 발급 방법</h3>
              <ol>
                <li>
                  <a
                    href="https://console.cloud.google.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    Google Cloud Console
                  </a>{' '}
                  접속
                </li>
                <li>새 프로젝트 생성</li>
                <li>YouTube Data API v3 활성화</li>
                <li>사용자 인증 정보 생성 {'>'} API 키 생성</li>
                <li>생성된 API 키를 위에 입력</li>
              </ol>
            </div>
            <div className="info-box">
              <h3>⏰ 자동 검색 설정</h3>
              <p>✅ 자동 검색 활성화: 매일 오전 6시</p>
              <p>📊 검색 기준: 최근 1개월 이내 영상</p>
              <p>🎯 키워드당 수집: 200개 영상 (조회수 순)</p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default App;
