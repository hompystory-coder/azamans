import { useState, useEffect } from 'react';
import { projectsAPI } from '../utils/api';

export default function Dashboard({ onCreateNew }) {
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadProjects();
  }, []);

  const loadProjects = async () => {
    try {
      const response = await projectsAPI.getAll();
      setProjects(response.data.data || []);
    } catch (error) {
      console.error('Failed to load projects:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="flex flex-col items-center space-y-4">
          <div className="relative w-20 h-20">
            <div className="absolute inset-0 border-4 border-purple-500/30 rounded-full"></div>
            <div className="absolute inset-0 border-4 border-t-purple-500 rounded-full animate-spin"></div>
          </div>
          <div className="text-white text-xl font-semibold animate-pulse">로딩 중...</div>
        </div>
      </div>
    );
  }

  if (projects.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center h-96 space-y-8">
        <div className="relative">
          <div className="absolute inset-0 bg-purple-500/20 blur-3xl rounded-full animate-pulse"></div>
          <div className="relative text-8xl transform hover:scale-110 transition-transform duration-300">📺</div>
        </div>
        <div className="text-center space-y-3">
          <h2 className="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
            프로젝트가 없습니다
          </h2>
          <p className="text-gray-300 text-lg font-medium">
            ✨ 첫 번째 AI 쇼츠 영상을 만들어보세요!
          </p>
        </div>
        <button
          onClick={onCreateNew}
          className="group relative px-12 py-5 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 text-white font-bold text-xl rounded-2xl shadow-2xl hover:shadow-purple-500/50 transform hover:scale-105 hover:-translate-y-1 transition-all duration-300 overflow-hidden"
        >
          <span className="absolute inset-0 w-full h-full bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
          <span className="relative flex items-center space-x-2">
            <span className="text-2xl animate-bounce">🎬</span>
            <span>첫 프로젝트 만들기</span>
          </span>
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
        <div>
          <h2 className="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">내 프로젝트</h2>
          <p className="text-gray-400 mt-1 font-medium">총 {projects.length}개의 프로젝트</p>
        </div>
        <div className="flex items-center space-x-2 px-4 py-2 bg-purple-500/20 rounded-xl border border-purple-500/30">
          <span className="text-2xl">📊</span>
          <span className="text-white font-bold text-xl">{projects.length}</span>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {projects.map((project) => (
          <ProjectCard key={project.id} project={project} onRefresh={loadProjects} />
        ))}
      </div>
    </div>
  );
}

function ProjectCard({ project, onRefresh }) {
  const statusConfig = {
    draft: { bg: 'bg-gradient-to-r from-gray-500 to-gray-600', icon: '📝', text: '초안' },
    generating: { bg: 'bg-gradient-to-r from-blue-500 to-blue-600', icon: '⚙️', text: '생성 중' },
    completed: { bg: 'bg-gradient-to-r from-green-500 to-emerald-600', icon: '✅', text: '완료' },
    failed: { bg: 'bg-gradient-to-r from-red-500 to-red-600', icon: '❌', text: '실패' }
  };
  
  const status = statusConfig[project.status] || statusConfig.draft;

  return (
    <div className="group relative bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-2xl rounded-2xl p-6 border border-white/20 hover:border-purple-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-purple-500/20 hover:-translate-y-2 overflow-hidden">
      {/* Hover Effect Background */}
      <div className="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      
      <div className="relative">
        <div className="flex items-start justify-between mb-4">
          <h3 className="text-xl font-black text-white truncate flex-1 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-purple-400 group-hover:to-pink-400 transition-all duration-300">
            {project.title}
          </h3>
          <span className={`flex items-center space-x-1 px-3 py-1.5 rounded-xl text-xs font-bold text-white shadow-lg ${status.bg}`}>
            <span>{status.icon}</span>
            <span>{status.text}</span>
          </span>
        </div>

        <div className="space-y-3 text-sm mb-6">
          <div className="flex items-center space-x-3 bg-white/5 rounded-lg px-3 py-2 border border-white/10">
            <span className="text-xl">🎭</span>
            <div className="flex-1">
              <div className="text-gray-400 text-xs">캐릭터</div>
              <div className="text-white font-semibold">{project.characterId}</div>
            </div>
          </div>
          <div className="flex items-center space-x-3 bg-white/5 rounded-lg px-3 py-2 border border-white/10">
            <span className="text-xl">🎤</span>
            <div className="flex-1">
              <div className="text-gray-400 text-xs">음성</div>
              <div className="text-white font-semibold">{project.voiceId}</div>
            </div>
          </div>
          <div className="flex items-center space-x-3 bg-white/5 rounded-lg px-3 py-2 border border-white/10">
            <span className="text-xl">📅</span>
            <div className="flex-1">
              <div className="text-gray-400 text-xs">생성일</div>
              <div className="text-white font-semibold">{new Date(project.createdAt).toLocaleDateString('ko-KR')}</div>
            </div>
          </div>
        </div>

        <div className="flex space-x-2">
          <button className="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold rounded-xl hover:from-purple-700 hover:to-purple-800 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
            🎬 열기
          </button>
          <button className="px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white font-bold rounded-xl hover:from-red-700 hover:to-red-800 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
            🗑️
          </button>
        </div>
      </div>
    </div>
  );
}
