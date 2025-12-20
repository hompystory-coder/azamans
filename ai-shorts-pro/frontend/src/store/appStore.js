import { create } from 'zustand';

export const useAppStore = create((set, get) => ({
  // Project state
  currentProject: null,
  projects: [],
  
  // Character & Voice
  selectedCharacter: 'forty',
  selectedVoice: 'leda',
  selectedFont: 'nanum-gothic-bold',
  
  // Settings
  mode: 'auto', // 'auto' or 'manual'
  style: 'character', // 'character', 'mixed', 'realistic'
  
  // Scenes
  scenes: [],
  
  // Generation
  isGenerating: false,
  generationProgress: 0,
  currentStep: '',
  jobId: null,
  
  // Assets
  uploadedMusic: null,
  uploadedImage: null,
  
  // Actions
  setCurrentProject: (project) => set({ currentProject: project }),
  
  setSelectedCharacter: (characterId) => set({ selectedCharacter: characterId }),
  
  setSelectedVoice: (voiceId) => set({ selectedVoice: voiceId }),
  
  setSelectedFont: (fontId) => set({ selectedFont: fontId }),
  
  setMode: (mode) => set({ mode }),
  
  setStyle: (style) => set({ style }),
  
  setScenes: (scenes) => set({ scenes }),
  
  addScene: (scene) => set((state) => ({ 
    scenes: [...state.scenes, scene] 
  })),
  
  updateScene: (index, updates) => set((state) => ({
    scenes: state.scenes.map((s, i) => 
      i === index ? { ...s, ...updates } : s
    )
  })),
  
  removeScene: (index) => set((state) => ({
    scenes: state.scenes.filter((_, i) => i !== index)
  })),
  
  setGenerationProgress: (progress, step) => set({ 
    generationProgress: progress,
    currentStep: step
  }),
  
  startGeneration: (jobId) => set({ 
    isGenerating: true,
    jobId,
    generationProgress: 0 
  }),
  
  completeGeneration: () => set({ 
    isGenerating: false,
    generationProgress: 100,
    currentStep: 'Completed'
  }),
  
  setUploadedMusic: (file) => set({ uploadedMusic: file }),
  
  setUploadedImage: (file) => set({ uploadedImage: file }),
  
  resetProject: () => set({
    currentProject: null,
    scenes: [],
    uploadedMusic: null,
    uploadedImage: null,
    isGenerating: false,
    generationProgress: 0,
    currentStep: '',
    jobId: null
  })
}));
