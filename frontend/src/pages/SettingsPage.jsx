import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Save, ArrowRight, Key, Settings } from 'lucide-react'
import useStore from '../store/useStore'

export default function SettingsPage() {
  const navigate = useNavigate()
  const { settings, setSettings } = useStore()
  
  const [geminiApiKey, setGeminiApiKey] = useState(settings.geminiApiKey || '')
  const [minimaxApiKey, setMinimaxApiKey] = useState(settings.minimaxApiKey || '')
  const [minimaxGroupId, setMinimaxGroupId] = useState(settings.minimaxGroupId || '')
  const [shotstackApiKey, setShotstackApiKey] = useState(settings.shotstackApiKey || '')
  const [saved, setSaved] = useState(false)

  const handleSave = () => {
    const newSettings = {
      geminiApiKey,
      minimaxApiKey,
      minimaxGroupId,
      shotstackApiKey
    }
    setSettings(newSettings)
    setSaved(true)
    setTimeout(() => setSaved(false), 3000)
  }

  const handleNext = () => {
    handleSave()
    navigate('/')
  }

  return (
    <div className="max-w-4xl mx-auto">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white rounded-2xl shadow-xl p-8"
      >
        {/* 헤더 */}
        <div className="mb-8">
          <div className="flex items-center gap-3 mb-2">
            <Settings className="w-8 h-8 text-primary-600" />
            <h1 className="text-3xl font-bold text-gray-900">
              설정
            </h1>
          </div>
          <p className="text-gray-600">
            API 키 및 기본 설정을 관리합니다 (선택사항)
          </p>
        </div>

        {/* API 키 설정 */}
        <div className="space-y-6 mb-8">
          <div className="p-6 border border-gray-200 rounded-xl">
            <div className="flex items-center gap-3 mb-4">
              <Key className="w-6 h-6 text-primary-600" />
              <h2 className="text-xl font-semibold text-gray-900">
                API 키 설정
              </h2>
            </div>
            <p className="text-sm text-gray-600 mb-6">
              서버에 이미 API 키가 설정되어 있어 입력하지 않아도 됩니다.
              개인 키를 사용하려면 아래에 입력하세요.
            </p>

            <div className="space-y-4">
              {/* Gemini API Key */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Gemini API Key (스크립트 생성)
                </label>
                <input
                  type="password"
                  value={geminiApiKey}
                  onChange={(e) => setGeminiApiKey(e.target.value)}
                  placeholder="AIzaSy... (선택사항)"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>

              {/* Minimax API Key */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Minimax API Key (음성 생성)
                </label>
                <input
                  type="password"
                  value={minimaxApiKey}
                  onChange={(e) => setMinimaxApiKey(e.target.value)}
                  placeholder="eyJ... (선택사항)"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>

              {/* Minimax Group ID */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Minimax Group ID
                </label>
                <input
                  type="text"
                  value={minimaxGroupId}
                  onChange={(e) => setMinimaxGroupId(e.target.value)}
                  placeholder="1977... (선택사항)"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>

              {/* Shotstack API Key */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Shotstack API Key (최종 렌더링)
                </label>
                <input
                  type="password"
                  value={shotstackApiKey}
                  onChange={(e) => setShotstackApiKey(e.target.value)}
                  placeholder="C90... (선택사항)"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>
            </div>
          </div>

          {/* 안내 메시지 */}
          <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p className="text-sm text-blue-800">
              💡 <strong>Tip:</strong> API 키를 입력하지 않으면 서버에 설정된 공용 키가 사용됩니다.
              개인 키를 사용하면 더 높은 할당량과 빠른 처리가 가능합니다.
            </p>
          </div>
        </div>

        {/* 하단 버튼 */}
        <div className="flex justify-between items-center pt-6 border-t">
          <button
            onClick={handleSave}
            className="px-6 py-3 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors flex items-center gap-2 font-medium"
          >
            <Save className="w-5 h-5" />
            {saved ? '✓ 저장됨' : '설정 저장'}
          </button>
          <button
            onClick={handleNext}
            className="px-8 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2 font-medium"
          >
            다음 단계: 크롤링
            <ArrowRight className="w-5 h-5" />
          </button>
        </div>
      </motion.div>
    </div>
  )
}
