import { useState } from 'react';
import { useAppStore } from './store/appStore';
import Dashboard from './pages/Dashboard';
import ProjectWizard from './pages/ProjectWizard';

function App() {
  const [showWizard, setShowWizard] = useState(false);
  const { currentProject } = useAppStore();

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      {/* Header */}
      <header className="bg-black/20 backdrop-blur-lg border-b border-white/10">
        <div className="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="text-4xl">🎬</div>
              <div>
                <h1 className="text-2xl font-bold text-white">
                  AI 쇼츠 자동화 Pro
                </h1>
                <p className="text-sm text-gray-300">
                  블로그 URL로 전문가급 쇼츠 영상 자동 생성
                </p>
              </div>
            </div>
            
            <button
              onClick={() => setShowWizard(true)}
              className="px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
            >
              ✨ 새 프로젝트
            </button>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        {showWizard ? (
          <ProjectWizard onClose={() => setShowWizard(false)} />
        ) : (
          <Dashboard onCreateNew={() => setShowWizard(true)} />
        )}
      </main>

      {/* Footer */}
      <footer className="mt-auto py-6 text-center text-gray-400 text-sm">
        <p>Made with ❤️ by NeuralGrid Team</p>
        <p className="mt-1">Powered by Nano Banana Pro, Minimax, Gemini TTS & FFmpeg</p>
      </footer>
    </div>
  );
}

export default App;
