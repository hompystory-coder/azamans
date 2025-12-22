import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useStore from '../store/useStore';

// Mode card component
function ModeCard({ icon, title, description, cost, selected, onClick, badge }) {
  return (
    <div
      onClick={onClick}
      className={`
        relative p-6 rounded-xl cursor-pointer transition-all duration-300
        ${selected 
          ? 'bg-blue-500 text-white shadow-xl scale-105' 
          : 'bg-white hover:bg-gray-50 text-gray-800 border-2 border-gray-200 hover:border-blue-300'
        }
      `}
    >
      {badge && (
        <div className="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
          {badge}
        </div>
      )}
      <div className="text-4xl mb-3">{icon}</div>
      <h3 className="text-xl font-bold mb-2">{title}</h3>
      <p className={`text-sm mb-3 ${selected ? 'text-blue-100' : 'text-gray-600'}`}>
        {description}
      </p>
      {cost && (
        <div className={`text-lg font-bold ${selected ? 'text-white' : 'text-blue-600'}`}>
          {cost}
        </div>
      )}
    </div>
  );
}

export default function ModeSelectionPage() {
  const navigate = useNavigate();
  const { setContentMode, setAutomationMode, contentMode, automationMode } = useStore();
  
  const [localContentMode, setLocalContentMode] = useState(contentMode || 'character');
  const [localAutomationMode, setLocalAutomationMode] = useState(automationMode || 'auto');

  const handleStart = () => {
    // Save to global store
    setContentMode(localContentMode);
    setAutomationMode(localAutomationMode);
    
    console.log('🎬 모드 선택 완료:', {
      contentMode: localContentMode,
      automationMode: localAutomationMode
    });
    
    // Navigate to crawler page to input blog URL
    navigate('/');
  };

  return (
    <div className="max-w-6xl mx-auto p-8">
      {/* Header */}
      <div className="text-center mb-12">
        <h1 className="text-4xl font-bold mb-4 text-gray-800">
          🎬 AI 캐릭터 쇼츠 생성 모드 선택
        </h1>
        <p className="text-lg text-gray-600">
          원하는 콘텐츠 타입과 자동화 수준을 선택해주세요
        </p>
      </div>

      {/* Content Mode Selection */}
      <div className="mb-12">
        <h2 className="text-2xl font-bold mb-6 text-gray-800">
          1️⃣ 콘텐츠 타입 선택
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <ModeCard
            icon="🤖"
            title="캐릭터만"
            description="AI 캐릭터가 모든 장면에 등장합니다. 가장 몰입감 있는 경험을 제공합니다."
            cost="₩14,760 / 영상"
            selected={localContentMode === 'character'}
            onClick={() => setLocalContentMode('character')}
            badge="추천"
          />
          <ModeCard
            icon="🤖📷"
            title="하이브리드"
            description="AI 캐릭터와 실제 이미지를 혼합합니다. 균형잡힌 스타일입니다."
            cost="₩7,560 / 영상"
            selected={localContentMode === 'hybrid'}
            onClick={() => setLocalContentMode('hybrid')}
          />
          <ModeCard
            icon="📷"
            title="실사만"
            description="실제 이미지로만 구성합니다. 가장 경제적인 옵션입니다."
            cost="₩360 / 영상"
            selected={localContentMode === 'realistic'}
            onClick={() => setLocalContentMode('realistic')}
            badge="저렴"
          />
        </div>
      </div>

      {/* Automation Mode Selection */}
      <div className="mb-12">
        <h2 className="text-2xl font-bold mb-6 text-gray-800">
          2️⃣ 자동화 수준 선택
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <ModeCard
            icon="⚡"
            title="자동 모드"
            description="AI가 모든 단계를 자동으로 처리합니다. 빠르고 편리합니다."
            selected={localAutomationMode === 'auto'}
            onClick={() => setLocalAutomationMode('auto')}
            badge="빠름"
          />
          <ModeCard
            icon="🎨"
            title="수동 모드"
            description="각 단계를 직접 설정하고 편집할 수 있습니다. 세밀한 조정이 가능합니다."
            selected={localAutomationMode === 'manual'}
            onClick={() => setLocalAutomationMode('manual')}
          />
        </div>
      </div>

      {/* Selected Mode Summary */}
      <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl mb-8">
        <h3 className="text-xl font-bold mb-4 text-gray-800">📋 선택한 설정</h3>
        <div className="grid grid-cols-2 gap-4 text-gray-700">
          <div>
            <span className="font-semibold">콘텐츠 타입:</span>{' '}
            {localContentMode === 'character' && '🤖 캐릭터만'}
            {localContentMode === 'hybrid' && '🤖📷 하이브리드'}
            {localContentMode === 'realistic' && '📷 실사만'}
          </div>
          <div>
            <span className="font-semibold">자동화 수준:</span>{' '}
            {localAutomationMode === 'auto' ? '⚡ 자동 모드' : '🎨 수동 모드'}
          </div>
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex justify-center gap-4">
        <button
          onClick={() => navigate('/')}
          className="px-8 py-4 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-colors"
        >
          취소
        </button>
        <button
          onClick={handleStart}
          className="px-8 py-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-lg"
        >
          시작하기 🚀
        </button>
      </div>

      {/* Info Box */}
      <div className="mt-8 p-6 bg-yellow-50 border-l-4 border-yellow-400 rounded">
        <h4 className="font-bold text-yellow-800 mb-2">💡 팁</h4>
        <ul className="text-sm text-yellow-700 space-y-1">
          <li>• <strong>캐릭터만</strong>: 브랜딩과 캐릭터 마케팅에 최적</li>
          <li>• <strong>하이브리드</strong>: 정보 전달과 감성 모두 필요한 경우</li>
          <li>• <strong>실사만</strong>: 뉴스나 정보성 콘텐츠에 적합</li>
          <li>• <strong>자동 모드</strong>: 빠른 대량 생산에 유리</li>
          <li>• <strong>수동 모드</strong>: 고품질 콘텐츠 제작에 유리</li>
        </ul>
      </div>
    </div>
  );
}
