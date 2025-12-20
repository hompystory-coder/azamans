import { useState } from 'react';
import { useAppStore } from './store/appStore';
import Dashboard from './pages/Dashboard';
import ProjectWizard from './pages/ProjectWizard';

function App() {
  const [showWizard, setShowWizard] = useState(false);
  const { currentProject } = useAppStore();

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 relative overflow-hidden">
      {/* Animated Background */}
      <div className="absolute inset-0 overflow-hidden">
        <div className="absolute w-96 h-96 bg-purple-500/20 rounded-full blur-3xl -top-48 -left-48 animate-pulse"></div>
        <div className="absolute w-96 h-96 bg-blue-500/20 rounded-full blur-3xl -bottom-48 -right-48 animate-pulse" style={{animationDelay: '2s'}}></div>
        <div className="absolute w-96 h-96 bg-pink-500/20 rounded-full blur-3xl top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse" style={{animationDelay: '4s'}}></div>
      </div>
      
      {/* Content */}
      <div className="relative z-10">
        {/* Header */}
        <header className="bg-white/5 backdrop-blur-2xl border-b border-white/10 shadow-2xl sticky top-0 z-50">
          <div className="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-4 group">
                <div className="text-5xl transform group-hover:scale-110 group-hover:rotate-12 transition-all duration-300">🎬</div>
                <div>
                  <h1 className="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-blue-400 animate-gradient">
                    AI 쇼츠 자동화 Pro
                  </h1>
                  <p className="text-sm text-gray-300/90 font-medium mt-1">
                    🚀 블로그 URL로 전문가급 쇼츠 영상 자동 생성
                  </p>
                </div>
              </div>
              
              <button
                onClick={() => setShowWizard(true)}
                className="group relative px-8 py-4 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 text-white font-bold rounded-2xl shadow-2xl hover:shadow-purple-500/50 transform hover:scale-105 hover:-translate-y-1 transition-all duration-300 overflow-hidden"
              >
                <span className="absolute inset-0 w-full h-full bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                <span className="relative flex items-center space-x-2">
                  <span className="text-xl animate-bounce">✨</span>
                  <span>새 프로젝트</span>
                </span>
              </button>
            </div>
          </div>
        </header>

        {/* Main Content */}
        <main className="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
          {showWizard ? (
            <ProjectWizard onClose={() => setShowWizard(false)} />
          ) : (
            <Dashboard onCreateNew={() => setShowWizard(true)} />
          )}
        </main>

        {/* Footer */}
        <footer className="mt-20 py-8 text-center">
          <div className="max-w-4xl mx-auto">
            <div className="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
              <p className="text-gray-300 font-medium">Made with <span className="text-red-400 animate-pulse">❤️</span> by <span className="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 font-bold">NeuralGrid Team</span></p>
              <div className="mt-3 flex items-center justify-center space-x-3 text-sm text-gray-400">
                <span className="flex items-center space-x-1">
                  <span>🎨</span>
                  <span>Nano Banana Pro</span>
                </span>
                <span className="text-white/20">•</span>
                <span className="flex items-center space-x-1">
                  <span>🎥</span>
                  <span>Minimax</span>
                </span>
                <span className="text-white/20">•</span>
                <span className="flex items-center space-x-1">
                  <span>🎤</span>
                  <span>Gemini TTS</span>
                </span>
                <span className="text-white/20">•</span>
                <span className="flex items-center space-x-1">
                  <span>🎬</span>
                  <span>FFmpeg</span>
                </span>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
  );
}

export default App;
