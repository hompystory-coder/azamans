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
        <div className="text-white text-xl">Loading...</div>
      </div>
    );
  }

  if (projects.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center h-96 space-y-6">
        <div className="text-6xl">📺</div>
        <h2 className="text-3xl font-bold text-white">프로젝트가 없습니다</h2>
        <p className="text-gray-300 text-lg">
          첫 번째 AI 쇼츠 영상을 만들어보세요!
        </p>
        <button
          onClick={onCreateNew}
          className="px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold text-lg rounded-lg shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-200"
        >
          🎬 첫 프로젝트 만들기
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-2xl font-bold text-white">내 프로젝트</h2>
        <div className="text-gray-300">
          총 {projects.length}개
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
  const statusColors = {
    draft: 'bg-gray-500',
    generating: 'bg-blue-500',
    completed: 'bg-green-500',
    failed: 'bg-red-500'
  };

  const statusText = {
    draft: '초안',
    generating: '생성 중',
    completed: '완료',
    failed: '실패'
  };

  return (
    <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 hover:border-white/40 transition-all duration-200 hover:shadow-xl">
      <div className="flex items-start justify-between mb-4">
        <h3 className="text-lg font-bold text-white truncate flex-1">
          {project.title}
        </h3>
        <span className={`px-3 py-1 rounded-full text-xs font-semibold text-white ${statusColors[project.status]}`}>
          {statusText[project.status]}
        </span>
      </div>

      <div className="space-y-2 text-sm text-gray-300">
        <div className="flex items-center space-x-2">
          <span>🎭</span>
          <span>캐릭터: {project.characterId}</span>
        </div>
        <div className="flex items-center space-x-2">
          <span>🎤</span>
          <span>음성: {project.voiceId}</span>
        </div>
        <div className="flex items-center space-x-2">
          <span>📅</span>
          <span>{new Date(project.createdAt).toLocaleDateString()}</span>
        </div>
      </div>

      <div className="mt-4 flex space-x-2">
        <button className="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
          열기
        </button>
        <button className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
          삭제
        </button>
      </div>
    </div>
  );
}
