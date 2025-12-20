import { useState, useEffect } from 'react';
import { useAppStore } from '../store/appStore';
import { charactersAPI, voicesAPI, projectsAPI, crawlerAPI } from '../utils/api';
import CharacterSelector from '../components/CharacterSelector';
import VoiceSelector from '../components/VoiceSelector';
import URLInput from '../components/URLInput';
import ScriptEditor from '../components/ScriptEditor';

export default function ProjectWizard({ onClose }) {
  const [step, setStep] = useState(1);
  const [projectTitle, setProjectTitle] = useState('');
  const [blogUrl, setBlogUrl] = useState('');
  const [crawledData, setCrawledData] = useState(null);
  const [loading, setLoading] = useState(false);
  
  const {
    selectedCharacter,
    selectedVoice,
    mode,
    setMode,
    scenes,
    setScenes
  } = useAppStore();

  const steps = [
    { num: 1, title: '기본 설정', icon: '⚙️' },
    { num: 2, title: '캐릭터 선택', icon: '🎭' },
    { num: 3, title: '음성 선택', icon: '🎤' },
    { num: 4, title: '콘텐츠 입력', icon: '📝' },
    { num: 5, title: '스크립트 편집', icon: '✏️' },
    { num: 6, title: '완료', icon: '✨' }
  ];

  const handleCrawl = async () => {
    if (!blogUrl) return;
    
    setLoading(true);
    try {
      // Crawl content
      const crawlRes = await crawlerAPI.crawl(blogUrl);
      const { title, content, images } = crawlRes.data.data;
      
      // Analyze content
      const analyzeRes = await crawlerAPI.analyze(content, images);
      const { keywords, productName, suggestedCategory } = analyzeRes.data.data;
      
      // Generate script
      const scriptRes = await crawlerAPI.generateScript({
        title,
        content,
        keywords,
        productName,
        sceneCount: 5
      });
      
      const generatedScenes = scriptRes.data.data.scenes;
      setScenes(generatedScenes);
      setCrawledData({ title, content, images, keywords });
      setProjectTitle(productName || title);
      
      setStep(5); // Go to script editor
    } catch (error) {
      console.error('Crawl error:', error);
      alert('크롤링 실패: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateProject = async () => {
    try {
      const projectData = {
        title: projectTitle,
        characterId: selectedCharacter,
        voiceId: selectedVoice,
        mode: mode,
        style: 'character'
      };
      
      const response = await projectsAPI.create(projectData);
      const project = response.data.data;
      
      // Save scenes
      await projectsAPI.update(project.id, { scenes });
      
      alert('프로젝트가 생성되었습니다!');
      onClose();
    } catch (error) {
      console.error('Create project error:', error);
      alert('프로젝트 생성 실패: ' + error.message);
    }
  };

  return (
    <div className="bg-white/10 backdrop-blur-xl rounded-2xl p-8 border border-white/20">
      {/* Progress Steps */}
      <div className="mb-8">
        <div className="flex items-center justify-between">
          {steps.map((s, idx) => (
            <div key={s.num} className="flex items-center">
              <div className={`flex items-center space-x-2 px-4 py-2 rounded-lg ${
                step >= s.num ? 'bg-purple-600 text-white' : 'bg-white/5 text-gray-400'
              }`}>
                <span className="text-2xl">{s.icon}</span>
                <span className="font-semibold">{s.title}</span>
              </div>
              {idx < steps.length - 1 && (
                <div className={`w-8 h-1 mx-2 ${
                  step > s.num ? 'bg-purple-600' : 'bg-white/10'
                }`} />
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Step Content */}
      <div className="min-h-96">
        {step === 1 && (
          <div className="space-y-6">
            <h2 className="text-3xl font-bold text-white mb-6">기본 설정</h2>
            
            <div>
              <label className="block text-white text-sm font-semibold mb-2">
                프로젝트 이름
              </label>
              <input
                type="text"
                value={projectTitle}
                onChange={(e) => setProjectTitle(e.target.value)}
                className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                placeholder="예: 동서가구 뉴테라 소파 리뷰"
              />
            </div>

            <div>
              <label className="block text-white text-sm font-semibold mb-2">
                생성 모드
              </label>
              <div className="grid grid-cols-2 gap-4">
                <button
                  onClick={() => setMode('auto')}
                  className={`p-6 rounded-xl border-2 transition-all ${
                    mode === 'auto'
                      ? 'border-purple-500 bg-purple-500/20'
                      : 'border-white/20 bg-white/5 hover:bg-white/10'
                  }`}
                >
                  <div className="text-4xl mb-2">🤖</div>
                  <div className="text-white font-bold text-lg">자동 모드</div>
                  <div className="text-gray-300 text-sm mt-2">
                    AI가 자동으로 모든 것을 생성
                  </div>
                </button>
                
                <button
                  onClick={() => setMode('manual')}
                  className={`p-6 rounded-xl border-2 transition-all ${
                    mode === 'manual'
                      ? 'border-purple-500 bg-purple-500/20'
                      : 'border-white/20 bg-white/5 hover:bg-white/10'
                  }`}
                >
                  <div className="text-4xl mb-2">✏️</div>
                  <div className="text-white font-bold text-lg">수동 모드</div>
                  <div className="text-gray-300 text-sm mt-2">
                    직접 편집하고 커스터마이징
                  </div>
                </button>
              </div>
            </div>
          </div>
        )}

        {step === 2 && <CharacterSelector />}
        {step === 3 && <VoiceSelector />}
        
        {step === 4 && (
          <URLInput
            url={blogUrl}
            setUrl={setBlogUrl}
            onCrawl={handleCrawl}
            loading={loading}
          />
        )}
        
        {step === 5 && <ScriptEditor scenes={scenes} />}
        
        {step === 6 && (
          <div className="text-center space-y-6">
            <div className="text-6xl">🎉</div>
            <h2 className="text-3xl font-bold text-white">준비 완료!</h2>
            <p className="text-gray-300 text-lg">
              프로젝트를 생성하고 쇼츠 영상을 만들어보세요!
            </p>
            <button
              onClick={handleCreateProject}
              className="px-12 py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold text-xl rounded-lg shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-200"
            >
              🎬 프로젝트 생성하기
            </button>
          </div>
        )}
      </div>

      {/* Navigation */}
      <div className="mt-8 flex justify-between">
        <button
          onClick={() => step > 1 ? setStep(step - 1) : onClose()}
          className="px-6 py-3 bg-white/10 text-white rounded-lg hover:bg-white/20 transition-colors"
        >
          {step === 1 ? '취소' : '이전'}
        </button>
        
        {step < 6 && (
          <button
            onClick={() => setStep(step + 1)}
            disabled={step === 1 && !projectTitle}
            className="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            다음
          </button>
        )}
      </div>
    </div>
  );
}
