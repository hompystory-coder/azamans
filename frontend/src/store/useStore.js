import { create } from 'zustand';
import { persist } from 'zustand/middleware';

const useStore = create(
  persist(
    (set) => ({
      // Settings
      settings: {
        geminiApiKey: '',
        minimaxApiKey: '',
        minimaxGroupId: '',
        shotstackApiKey: '',
      },
      setSettings: (settings) => set({ settings }),

      // Crawled Data
      crawledData: null,
      setCrawledData: (data) => set({ crawledData: data }),

      // Script
      script: null,
      setScript: (script) => set({ script }),

      // Voice Data
      voiceData: null,
      setVoiceData: (data) => set({ voiceData: data }),

      // Video Data
      videoData: null,
      setVideoData: (data) => set({ videoData: data }),

      // Final Video
      finalVideo: null,
      setFinalVideo: (data) => set({ finalVideo: data }),

      // Reset all data
      reset: () =>
        set({
          crawledData: null,
          script: null,
          voiceData: null,
          videoData: null,
          finalVideo: null,
        }),
    }),
    {
      name: 'shorts-creator-storage',
      partialize: (state) => ({
        settings: state.settings,
        crawledData: state.crawledData,
        script: state.script,
        voiceData: state.voiceData,
        videoData: state.videoData,
        finalVideo: state.finalVideo,
      }),
    }
  )
);

export { useStore };
