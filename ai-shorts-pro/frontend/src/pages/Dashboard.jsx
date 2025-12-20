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
      <div className="space-y-12">
        {/* Hero Section */}
        <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-900/40 via-pink-900/40 to-blue-900/40 backdrop-blur-2xl border border-white/20 p-12">
          <div className="absolute inset-0">
            <div className="absolute w-64 h-64 bg-purple-500/30 rounded-full blur-3xl top-0 left-0 animate-pulse"></div>
            <div className="absolute w-64 h-64 bg-pink-500/30 rounded-full blur-3xl bottom-0 right-0 animate-pulse" style={{animationDelay: '1s'}}></div>
          </div>
          
          <div className="relative z-10 text-center space-y-8">
            <div className="flex justify-center">
              <div className="relative">
                <div className="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 blur-3xl opacity-50 animate-pulse"></div>
                <div className="relative text-9xl animate-bounce">🎬</div>
              </div>
            </div>
            
            <div className="space-y-4">
              <h1 className="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-300 via-pink-300 to-blue-300 animate-gradient">
                블로그를 영상으로!
              </h1>
              <p className="text-2xl text-gray-200 font-semibold max-w-2xl mx-auto">
                URL만 입력하면 <span className="text-purple-400 font-black">AI가 자동으로</span> 전문가급 쇼츠 영상을 만들어드립니다
              </p>
            </div>

            <button
              onClick={onCreateNew}
              className="group relative px-16 py-6 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 text-white font-black text-2xl rounded-2xl shadow-2xl hover:shadow-purple-500/50 transform hover:scale-110 hover:-translate-y-2 transition-all duration-300 overflow-hidden"
            >
              <span className="absolute inset-0 w-full h-full bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
              <span className="absolute inset-0 w-full h-full">
                <span className="absolute top-0 left-0 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100 animate-ping"></span>
                <span className="absolute top-0 right-0 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100 animate-ping" style={{animationDelay: '0.2s'}}></span>
                <span className="absolute bottom-0 left-0 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100 animate-ping" style={{animationDelay: '0.4s'}}></span>
                <span className="absolute bottom-0 right-0 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100 animate-ping" style={{animationDelay: '0.6s'}}></span>
              </span>
              <span className="relative flex items-center space-x-3">
                <span className="text-3xl">✨</span>
                <span>지금 시작하기</span>
                <span className="text-3xl">🚀</span>
              </span>
            </button>
          </div>
        </div>

        {/* Features Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <FeatureCard 
            icon="🎭"
            title="10가지 캐릭터"
            description="비즈니스, 푸드, 테크 등 다양한 캐릭터 프리셋"
            gradient="from-purple-500 to-purple-700"
          />
          <FeatureCard 
            icon="🎤"
            title="8가지 AI 음성"
            description="자연스러운 한국어 AI 음성 합성"
            gradient="from-pink-500 to-pink-700"
          />
          <FeatureCard 
            icon="⚡"
            title="25분 생성"
            description="평균 25분만에 720p 쇼츠 완성"
            gradient="from-blue-500 to-blue-700"
          />
          <FeatureCard 
            icon="💰"
            title="$0.30/영상"
            description="업계 최저가! 45% 비용 절감"
            gradient="from-green-500 to-green-700"
          />
        </div>

        {/* How It Works */}
        <div className="bg-white/5 backdrop-blur-2xl rounded-3xl p-10 border border-white/10">
          <h2 className="text-4xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 mb-12">
            작동 방식
          </h2>
          
          <div className="grid grid-cols-1 md:grid-cols-5 gap-6">
            <StepCard number="1" icon="📝" title="URL 입력" description="블로그 글 URL을 붙여넣기" />
            <StepCard number="2" icon="🤖" title="AI 분석" description="내용을 자동으로 분석" />
            <StepCard number="3" icon="🎬" title="영상 생성" description="5개 장면 영상 제작" />
            <StepCard number="4" icon="🎵" title="음성 합성" description="AI 성우가 나레이션" />
            <StepCard number="5" icon="✨" title="완성!" description="다운로드 & 업로드" />
          </div>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <StatCard value="720×1280" label="해상도" icon="📐" />
          <StatCard value="30 FPS" label="프레임" icon="🎞️" />
          <StatCard value="9:16" label="비율" icon="📱" />
        </div>
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

// Feature Card Component
function FeatureCard({ icon, title, description, gradient }) {
  return (
    <div className="group relative bg-white/5 backdrop-blur-2xl rounded-2xl p-6 border border-white/10 hover:border-purple-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-purple-500/20 overflow-hidden">
      <div className={`absolute inset-0 bg-gradient-to-br ${gradient} opacity-0 group-hover:opacity-10 transition-opacity duration-300`}></div>
      <div className="relative space-y-4">
        <div className="text-5xl transform group-hover:scale-125 group-hover:rotate-12 transition-all duration-300">{icon}</div>
        <div>
          <h3 className="text-xl font-black text-white mb-2">{title}</h3>
          <p className="text-gray-400 text-sm">{description}</p>
        </div>
      </div>
    </div>
  );
}

// Step Card Component
function StepCard({ number, icon, title, description }) {
  return (
    <div className="relative">
      <div className="flex flex-col items-center text-center space-y-3 group">
        <div className="relative">
          <div className="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 blur-xl opacity-50 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div className="relative w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-xl group-hover:scale-110 transition-transform duration-300">
            {number}
          </div>
        </div>
        <div className="text-4xl transform group-hover:scale-125 transition-transform duration-300">{icon}</div>
        <div>
          <h4 className="text-white font-bold text-lg">{title}</h4>
          <p className="text-gray-400 text-sm mt-1">{description}</p>
        </div>
      </div>
      {number !== "5" && (
        <div className="hidden md:block absolute top-8 left-full w-full h-0.5 bg-gradient-to-r from-purple-500 to-transparent"></div>
      )}
    </div>
  );
}

// Stat Card Component
function StatCard({ value, label, icon }) {
  return (
    <div className="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-2xl rounded-2xl p-8 border border-white/20 text-center group hover:border-purple-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-purple-500/20">
      <div className="text-5xl mb-4 transform group-hover:scale-125 group-hover:rotate-12 transition-all duration-300">{icon}</div>
      <div className="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 mb-2">
        {value}
      </div>
      <div className="text-gray-400 font-semibold">{label}</div>
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
