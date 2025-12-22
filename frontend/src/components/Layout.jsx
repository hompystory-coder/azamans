import { Link, useLocation } from 'react-router-dom'
import { Settings, Download, Edit3, Mic, Video, Eye } from 'lucide-react'

const steps = [
  { path: '/settings', label: '설정', icon: Settings },
  { path: '/', label: '크롤링', icon: Download },
  { path: '/script', label: '스크립트', icon: Edit3 },
  { path: '/voice', label: '음성', icon: Mic },
  { path: '/video', label: '비디오', icon: Video },
  { path: '/preview', label: '미리보기', icon: Eye }
]

export default function Layout({ children }) {
  const location = useLocation()

  // '/' 경로를 크롤링 단계로 처리
  const currentPath = location.pathname === '/' ? '/' : location.pathname
  const currentStepIndex = steps.findIndex(step => step.path === currentPath)

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200">
      {/* Header */}
      <header className="bg-white shadow-md border-b border-gray-200">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold bg-gradient-to-r from-red-500 to-pink-500 bg-clip-text text-transparent">
                Shorts Creator Pro
              </h1>
              <p className="text-sm text-gray-600 mt-1">
                AI 기반 YouTube Shorts 자동 생성 플랫폼
              </p>
            </div>
            
            <div className="flex items-center gap-4">
              <div className="text-sm text-gray-600">
                <span className="font-semibold">Step {currentStepIndex + 1}</span> / {steps.length}
              </div>
            </div>
          </div>

          {/* Progress Steps */}
          <div className="mt-6 flex items-center justify-between">
            {steps.map((step, index) => {
              const Icon = step.icon
              const isActive = location.pathname === step.path
              const isCompleted = index < currentStepIndex

              return (
                <div key={step.path} className="flex items-center flex-1">
                  <Link
                    to={step.path}
                    className={`flex flex-col items-center gap-2 ${
                      isActive ? 'opacity-100' : 'opacity-50 hover:opacity-75'
                    } transition-opacity`}
                  >
                    <div
                      className={`w-12 h-12 rounded-full flex items-center justify-center transition-all ${
                        isActive
                          ? 'bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg scale-110'
                          : isCompleted
                          ? 'bg-green-500 text-white'
                          : 'bg-gray-200 text-gray-600'
                      }`}
                    >
                      <Icon className="w-6 h-6" />
                    </div>
                    <span className={`text-xs font-medium ${isActive ? 'text-gray-900' : 'text-gray-600'}`}>
                      {step.label}
                    </span>
                  </Link>
                  
                  {index < steps.length - 1 && (
                    <div className={`flex-1 h-1 mx-2 ${isCompleted ? 'bg-green-500' : 'bg-gray-300'}`} />
                  )}
                </div>
              )
            })}
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        <div className="animate-fade-in">
          {children}
        </div>
      </main>

      {/* Footer */}
      <footer className="bg-white border-t border-gray-200 mt-12">
        <div className="container mx-auto px-4 py-6">
          <div className="flex items-center justify-between text-sm text-gray-600">
            <div>
              © 2025 Shorts Creator Pro. Powered by AI.
            </div>
            <div className="flex items-center gap-4">
              <span>API 연결: ✅</span>
              <span>서버 상태: 정상</span>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}
