import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Video, Loader, CheckCircle, Film, AlertCircle, Upload, X, Music, Image as ImageIcon, Type, Settings as SettingsIcon } from 'lucide-react';
import { useStore } from '../store/useStore';
import api from '../api/client';

export default function VideoPage() {
  const { script, voiceData, setVideoData: setGlobalVideoData } = useStore();
  const [generating, setGenerating] = useState(false);
  const [progress, setProgress] = useState(0);
  const [status, setStatus] = useState('');
  const [videoId, setVideoId] = useState(null);
  const [error, setError] = useState('');
  const [videoUrl, setVideoUrl] = useState('');
  const [videoData, setVideoData] = useState(null); // Local video data state
  
  // 효과 설정 상태
  const [effectSettings, setEffectSettings] = useState({
    imageEffect: 'auto',
    effectIntensity: 'medium'
  });
  
  // 배경 음악 설정
  const [bgMusic, setBgMusic] = useState({
    enabled: false,
    file: null,
    url: '',
    volume: 0.3
  });
  
  // 배경 이미지/워터마크 설정
  const [bgImage, setBgImage] = useState({
    enabled: false,
    file: null,
    url: '',
    opacity: 0.5,
    position: 'bottom-right' // 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'
  });
  
  // 자막 설정
  const [subtitleSettings, setSubtitleSettings] = useState({
    fontSize: 48,
    fontFamily: 'NanumGothicBold',
    color: '#FFFFFF',
    position: 'bottom', // 'top', 'center', 'bottom'
    yOffset: 340, // 하단에서 340px (위로 40px 이동)
    strokeWidth: 2,
    strokeColor: '#000000',
    boxOpacity: 0, // 배경색 제거
    lineSpacing: 10
  });
  
  // 제목 설정
  const [titleSettings, setTitleSettings] = useState({
    enabled: true,
    fontSize: 64,
    fontFamily: 'NanumGothicBold',
    color: '#FFFF00',
    position: 'top', // 'top', 'center', 'bottom'
    yOffset: 270, // 상단에서 270px
    strokeWidth: 2,
    strokeColor: '#000000',
    boxOpacity: 0 // 배경색 제거
  });
  
  // 사용 가능한 폰트 목록
  const availableFonts = [
    // === 추천 폰트 ⭐ ===
    { value: 'NanumGothicBold', label: '⭐ 나눔고딕 Bold (기본)' },
    { value: 'BlackHanSans', label: '⭐ 검은고딕 (강렬함)' },
    { value: 'DoHyeon', label: '⭐ 도현체 (유튜브 인기)' },
    { value: 'Jua', label: '⭐ 주아체 (둥글둥글)' },
    
    // === 귀여운 폰트 🎀 ===
    { value: 'CuteFont', label: '🎀 귀여운폰트' },
    { value: 'KirangHaerang', label: '🎀 기랑해랑' },
    { value: 'Gaegu', label: '🎀 개구쟁이' },
    { value: 'GaeguBold', label: '🎀 개구쟁이 Bold' },
    
    // === 손글씨 폰트 ✍️ ===
    { value: 'GamjaFlower', label: '✍️ 감자꽃' },
    { value: 'YeonSung', label: '✍️ 연성체' },
    { value: 'SingleDay', label: '✍️ 싱글데이' },
    { value: 'PoorStory', label: '✍️ 가난한이야기' },
    { value: 'HiMelody', label: '✍️ 하이멜로디' },
    { value: 'EastSeaDokdo', label: '✍️ 동해독도' },
    
    // === 깔끔한 폰트 ✨ ===
    { value: 'Stylish', label: '✨ 스타일리시' },
    { value: 'Sunflower', label: '✨ 해바라기' },
    { value: 'SunflowerBold', label: '✨ 해바라기 Bold' },
    { value: 'NanumBarunGothicBold', label: '✨ 나눔바른고딕 Bold' },
    
    // === 전통적인 폰트 🏛️ ===
    { value: 'SongMyung', label: '🏛️ 송명조' },
    { value: 'NanumMyeongjo', label: '🏛️ 나눔명조' },
    { value: 'NanumMyeongjoBold', label: '🏛️ 나눔명조 Bold' },
    
    // === 나머지 나눔 폰트 ===
    { value: 'NanumGothic', label: '나눔고딕' },
    { value: 'NanumGothicCoding', label: '나눔고딕 코딩' },
    { value: 'NanumGothicCodingBold', label: '나눔고딕 코딩 Bold' },
    { value: 'NanumBarunGothic', label: '나눔바른고딕' },
    { value: 'NanumSquare', label: '나눔스퀘어' },
    { value: 'NanumSquareBold', label: '나눔스퀘어 Bold' },
    { value: 'NanumSquareRound', label: '나눔스퀘어라운드' },
    { value: 'NanumSquareRoundB', label: '나눔스퀘어라운드 Bold' },
    
    // === 기타 ===
    { value: 'NotoSansKR-Bold', label: 'Noto Sans KR Bold' },
    { value: 'NotoSerifKR-Bold', label: 'Noto Serif KR Bold' },
    { value: 'SpoqaHanSansNeo-Bold', label: '스포카 한 산스 Bold' }
  ];
  
  // Google Fonts 로드
  useEffect(() => {
    // Google Fonts 동적 로드
    const link = document.createElement('link');
    link.href = 'https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=Do+Hyeon&family=Jua&family=Cute+Font&family=Kirang+Haerang&family=Gamja+Flower&family=Yeon+Sung&family=Single+Day&family=Poor+Story&family=Hi+Melody&family=East+Sea+Dokdo&family=Gaegu:wght@400;700&family=Stylish&family=Sunflower:wght@500;700&family=Song+Myung&family=Nanum+Gothic:wght@400;700;800&family=Nanum+Myeongjo:wght@400;700;800&family=Nanum+Pen+Script&family=Nanum+Gothic+Coding:wght@400;700&family=Noto+Sans+KR:wght@400;700;900&family=Noto+Serif+KR:wght@400;700;900&display=swap';
    link.rel = 'stylesheet';
    document.head.appendChild(link);
    
    // 폰트 프리뷰 스타일 추가
    const style = document.createElement('style');
    style.innerHTML = `
      .font-preview-select option {
        padding: 8px;
        font-size: 16px;
      }
      .font-NanumGothicBold { font-family: 'Nanum Gothic', sans-serif; font-weight: 800; }
      .font-BlackHanSans { font-family: 'Black Han Sans', sans-serif; }
      .font-DoHyeon { font-family: 'Do Hyeon', sans-serif; }
      .font-Jua { font-family: 'Jua', sans-serif; }
      .font-CuteFont { font-family: 'Cute Font', cursive; }
      .font-KirangHaerang { font-family: 'Kirang Haerang', cursive; }
      .font-Gaegu { font-family: 'Gaegu', cursive; }
      .font-GaeguBold { font-family: 'Gaegu', cursive; font-weight: 700; }
      .font-GamjaFlower { font-family: 'Gamja Flower', cursive; }
      .font-YeonSung { font-family: 'Yeon Sung', cursive; }
      .font-SingleDay { font-family: 'Single Day', cursive; }
      .font-PoorStory { font-family: 'Poor Story', cursive; }
      .font-HiMelody { font-family: 'Hi Melody', cursive; }
      .font-EastSeaDokdo { font-family: 'East Sea Dokdo', cursive; }
      .font-Stylish { font-family: 'Stylish', sans-serif; }
      .font-Sunflower { font-family: 'Sunflower', sans-serif; font-weight: 500; }
      .font-SunflowerBold { font-family: 'Sunflower', sans-serif; font-weight: 700; }
      .font-SongMyung { font-family: 'Song Myung', serif; }
      .font-NanumGothic { font-family: 'Nanum Gothic', sans-serif; }
      .font-NanumMyeongjo { font-family: 'Nanum Myeongjo', serif; }
      .font-NanumMyeongjoBold { font-family: 'Nanum Myeongjo', serif; font-weight: 700; }
      .font-NanumGothicCoding { font-family: 'Nanum Gothic Coding', monospace; }
      .font-NanumGothicCodingBold { font-family: 'Nanum Gothic Coding', monospace; font-weight: 700; }
      .font-NanumBarunGothic { font-family: 'Nanum Gothic', sans-serif; }
      .font-NanumBarunGothicBold { font-family: 'Nanum Gothic', sans-serif; font-weight: 700; }
      .font-NanumSquare { font-family: 'Nanum Gothic', sans-serif; }
      .font-NanumSquareBold { font-family: 'Nanum Gothic', sans-serif; font-weight: 700; }
      .font-NanumSquareRound { font-family: 'Nanum Gothic', sans-serif; }
      .font-NanumSquareRoundB { font-family: 'Nanum Gothic', sans-serif; font-weight: 700; }
      .font-NotoSansKR-Bold { font-family: 'Noto Sans KR', sans-serif; font-weight: 700; }
      .font-NotoSerifKR-Bold { font-family: 'Noto Serif KR', serif; font-weight: 700; }
      .font-SpoqaHanSansNeo-Bold { font-family: 'Noto Sans KR', sans-serif; font-weight: 700; }
    `;
    document.head.appendChild(style);
    
    return () => {
      document.head.removeChild(link);
      document.head.removeChild(style);
    };
  }, []);
  
  // IndexedDB 유틸리티 함수들
  const openDB = () => {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open('ShortsCreatorDB', 1);
      
      request.onerror = () => reject(request.error);
      request.onsuccess = () => resolve(request.result);
      
      request.onupgradeneeded = (event) => {
        const db = event.target.result;
        if (!db.objectStoreNames.contains('files')) {
          db.createObjectStore('files', { keyPath: 'key' });
        }
      };
    });
  };
  
  const saveFileToIndexedDB = async (key, file) => {
    if (!file) return;
    try {
      const db = await openDB();
      const transaction = db.transaction(['files'], 'readwrite');
      const store = transaction.objectStore('files');
      
      const fileData = {
        key: key,
        file: file,
        name: file.name,
        type: file.type,
        size: file.size,
        timestamp: Date.now()
      };
      
      await store.put(fileData);
      console.log(`✅ ${key} 파일 저장 완료:`, file.name);
    } catch (error) {
      console.error(`❌ ${key} 파일 저장 실패:`, error);
    }
  };
  
  const loadFileFromIndexedDB = async (key) => {
    try {
      const db = await openDB();
      const transaction = db.transaction(['files'], 'readonly');
      const store = transaction.objectStore('files');
      
      return new Promise((resolve, reject) => {
        const request = store.get(key);
        request.onsuccess = () => {
          if (request.result && request.result.file) {
            console.log(`✅ ${key} 파일 불러오기 완료:`, request.result.name);
            resolve(request.result);
          } else {
            resolve(null);
          }
        };
        request.onerror = () => reject(request.error);
      });
    } catch (error) {
      console.error(`❌ ${key} 파일 불러오기 실패:`, error);
      return null;
    }
  };
  
  // 설정을 localStorage에서 불러오기
  useEffect(() => {
    const loadSettings = async () => {
      try {
        // 효과 설정 불러오기
        const savedEffectSettings = localStorage.getItem('videoSettings_effects');
        if (savedEffectSettings) {
          setEffectSettings(JSON.parse(savedEffectSettings));
        }
        
        // 배경 음악 설정 + 파일 불러오기
        const savedBgMusic = localStorage.getItem('videoSettings_bgMusic');
        if (savedBgMusic) {
          const parsed = JSON.parse(savedBgMusic);
          const fileData = await loadFileFromIndexedDB('bgMusicFile');
          
          setBgMusic({
            enabled: parsed.enabled,
            volume: parsed.volume,
            file: fileData ? fileData.file : null,
            url: fileData ? URL.createObjectURL(fileData.file) : ''
          });
        }
        
        // 배경 이미지 설정 + 파일 불러오기
        const savedBgImage = localStorage.getItem('videoSettings_bgImage');
        if (savedBgImage) {
          const parsed = JSON.parse(savedBgImage);
          const fileData = await loadFileFromIndexedDB('bgImageFile');
          
          setBgImage({
            enabled: parsed.enabled,
            opacity: parsed.opacity,
            position: parsed.position,
            file: fileData ? fileData.file : null,
            url: fileData ? URL.createObjectURL(fileData.file) : ''
          });
        }
        
        // 자막 설정 불러오기
        const savedSubtitleSettings = localStorage.getItem('videoSettings_subtitle');
        if (savedSubtitleSettings) {
          setSubtitleSettings(JSON.parse(savedSubtitleSettings));
        }
        
        // 제목 설정 불러오기
        const savedTitleSettings = localStorage.getItem('videoSettings_title');
        if (savedTitleSettings) {
          setTitleSettings(JSON.parse(savedTitleSettings));
        }
        
        console.log('✅ 저장된 설정을 불러왔습니다 (파일 포함)');
      } catch (error) {
        console.error('❌ 설정 불러오기 실패:', error);
      }
    };
    
    loadSettings();
  }, []); // 컴포넌트 마운트 시 한 번만 실행
  
  // 설정이 변경될 때마다 localStorage에 저장
  useEffect(() => {
    try {
      localStorage.setItem('videoSettings_effects', JSON.stringify(effectSettings));
      console.log('💾 효과 설정 자동 저장됨');
    } catch (error) {
      console.error('효과 설정 저장 실패:', error);
    }
  }, [effectSettings.imageEffect, effectSettings.effectIntensity]);
  
  useEffect(() => {
    try {
      // 설정값 저장
      const settingsToSave = {
        enabled: bgMusic.enabled,
        volume: bgMusic.volume
      };
      localStorage.setItem('videoSettings_bgMusic', JSON.stringify(settingsToSave));
      
      // 파일도 IndexedDB에 저장
      if (bgMusic.file) {
        saveFileToIndexedDB('bgMusicFile', bgMusic.file);
      }
      
      console.log('💾 배경 음악 설정 자동 저장됨');
    } catch (error) {
      console.error('배경 음악 설정 저장 실패:', error);
    }
  }, [bgMusic.enabled, bgMusic.volume, bgMusic.file]);
  
  useEffect(() => {
    try {
      // 설정값 저장
      const settingsToSave = {
        enabled: bgImage.enabled,
        opacity: bgImage.opacity,
        position: bgImage.position
      };
      localStorage.setItem('videoSettings_bgImage', JSON.stringify(settingsToSave));
      
      // 파일도 IndexedDB에 저장
      if (bgImage.file) {
        saveFileToIndexedDB('bgImageFile', bgImage.file);
      }
      
      console.log('💾 배경 이미지 설정 자동 저장됨');
    } catch (error) {
      console.error('배경 이미지 설정 저장 실패:', error);
    }
  }, [bgImage.enabled, bgImage.opacity, bgImage.position, bgImage.file]);
  
  useEffect(() => {
    try {
      localStorage.setItem('videoSettings_subtitle', JSON.stringify(subtitleSettings));
      console.log('💾 자막 설정 자동 저장됨');
    } catch (error) {
      console.error('자막 설정 저장 실패:', error);
    }
  }, [
    subtitleSettings.fontSize,
    subtitleSettings.fontFamily,
    subtitleSettings.color,
    subtitleSettings.position,
    subtitleSettings.yOffset,
    subtitleSettings.strokeWidth,
    subtitleSettings.strokeColor,
    subtitleSettings.boxOpacity,
    subtitleSettings.lineSpacing
  ]);
  
  useEffect(() => {
    try {
      localStorage.setItem('videoSettings_title', JSON.stringify(titleSettings));
      console.log('💾 제목 설정 자동 저장됨');
    } catch (error) {
      console.error('제목 설정 저장 실패:', error);
    }
  }, [
    titleSettings.enabled,
    titleSettings.fontSize,
    titleSettings.fontFamily,
    titleSettings.color,
    titleSettings.position,
    titleSettings.yOffset,
    titleSettings.strokeWidth,
    titleSettings.strokeColor,
    titleSettings.boxOpacity
  ]);
  
  // 배경 음악 파일 선택
  const handleBgMusicUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
      if (!file.type.startsWith('audio/')) {
        setError('오디오 파일만 업로드 가능합니다');
        return;
      }
      if (file.size > 10 * 1024 * 1024) { // 10MB 제한
        setError('파일 크기는 10MB 이하여야 합니다');
        return;
      }
      setBgMusic({
        ...bgMusic,
        file: file,
        url: URL.createObjectURL(file),
        enabled: true
      });
    }
  };
  
  // 배경 이미지 파일 선택
  const handleBgImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
      if (!file.type.startsWith('image/')) {
        setError('이미지 파일만 업로드 가능합니다');
        return;
      }
      if (file.size > 5 * 1024 * 1024) { // 5MB 제한
        setError('파일 크기는 5MB 이하여야 합니다');
        return;
      }
      setBgImage({
        ...bgImage,
        file: file,
        url: URL.createObjectURL(file),
        enabled: true
      });
    }
  };

  const handleGenerate = async () => {
    if (!script) {
      setError('스크립트를 먼저 생성해주세요');
      return;
    }

    setGenerating(true);
    setError('');
    setProgress(0);
    setStatus('비디오 생성 요청 중...');

    try {
      const audioFilesData = voiceData?.audioFiles || [];
      
      // audioFiles가 객체 배열인 경우 URL만 추출 (예: [{url: '...', duration: ...}] → ['...'])
      const audioFiles = audioFilesData.map(file => {
        if (typeof file === 'string') {
          return file; // 이미 문자열이면 그대로 반환
        }
        return file.url || file.filepath || file; // 객체면 url 속성 추출
      });
      
      // 음성이 없을 때 경고 (배경 음악만 있을 경우)
      if (audioFiles.length === 0) {
        console.warn('⚠️  음성 없이 비디오 생성 (자막과 제목만 표시됨, 배경 음악은 있을 수 있음)');
      }
      
      console.log('🎤 음성 파일:', audioFiles.length > 0 ? `${audioFiles.length}개 준비됨` : '없음');
      if (audioFiles.length > 0) {
        console.log('   첫 번째 음성:', audioFiles[0]);
      }
      
      // 스크립트와 음성 파일 결합
      const scenesWithAudio = script.map((scene, index) => ({
        ...scene,
        audioUrl: audioFiles[index] || '' // 각 장면에 해당하는 음성 URL 추가
      }));
      
      console.log('📝 최종 전송 데이터:', {
        scenes: scenesWithAudio.length,
        audioCount: audioFiles.length,
        firstSceneHasAudio: !!scenesWithAudio[0]?.audioUrl
      });
      
      // FormData 생성 (파일 업로드용)
      const formData = new FormData();
      formData.append('parts', JSON.stringify(scenesWithAudio)); // audioUrl이 포함된 scenes 전송
      formData.append('audioFiles', JSON.stringify(audioFiles)); // 백엔드 호환성 유지
      formData.append('title', script[0]?.text || '유튜브 쇼츠');
      
      // 설정 객체 생성
      const settings = {
        title: {
          enabled: titleSettings.enabled,
          text: script[0]?.text || '유튜브 쇼츠',
          fontSize: titleSettings.fontSize,
          fontFamily: titleSettings.fontFamily,
          color: titleSettings.color,
          position: titleSettings.position,
          yOffset: titleSettings.yOffset,
          strokeWidth: titleSettings.strokeWidth,
          strokeColor: titleSettings.strokeColor,
          boxOpacity: titleSettings.boxOpacity
        },
        imageEffect: effectSettings.imageEffect,
        effectIntensity: effectSettings.effectIntensity,
        subtitle: {
          enabled: true, // 항상 자막 활성화
          fontSize: subtitleSettings.fontSize,
          fontFamily: subtitleSettings.fontFamily,
          color: subtitleSettings.color,
          position: subtitleSettings.position,
          yOffset: subtitleSettings.yOffset,
          strokeWidth: subtitleSettings.strokeWidth,
          strokeColor: subtitleSettings.strokeColor,
          boxOpacity: subtitleSettings.boxOpacity,
          lineSpacing: subtitleSettings.lineSpacing
        },
        bgMusic: bgMusic.enabled ? {
          volume: bgMusic.volume
        } : null,
        bgImage: bgImage.enabled ? {
          opacity: bgImage.opacity,
          position: bgImage.position
        } : null
      };
      
      formData.append('settings', JSON.stringify(settings));
      
      // 배경 음악 파일 추가
      if (bgMusic.enabled && bgMusic.file) {
        formData.append('bgMusicFile', bgMusic.file);
      }
      
      // 배경 이미지 파일 추가
      if (bgImage.enabled && bgImage.file) {
        formData.append('bgImageFile', bgImage.file);
      }

      const response = await api.post('/api/video/generate', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      // API 응답 구조: { success: true, data: { videoId, status, ... } }
      console.log('API 응답:', response.data);
      
      if (!response.data.success) {
        throw new Error(response.data.error || '비디오 생성 요청 실패');
      }
      
      const id = response.data.data?.videoId || response.data.videoId;
      if (!id) {
        throw new Error('비디오 ID를 받지 못했습니다');
      }
      
      setVideoId(id);
      setStatus('비디오 생성 중...');
      setProgress(20);

      // Poll for status
      const pollInterval = setInterval(async () => {
        try {
          const statusResponse = await api.get(`/api/video/status/${id}`);
          // API 응답 구조: { success: true, data: { videoId, status, ... } }
          const statusData = statusResponse.data?.data || statusResponse.data;

          setProgress(statusData.progress || 50);
          setStatus(statusData.message || '처리 중...');

          if (statusData.status === 'completed') {
            clearInterval(pollInterval);
            setProgress(100);
            setStatus('비디오 생성 완료!');
            
            // 비디오 URL을 절대 경로로 변환 (백엔드가 상대 경로 반환)
            const fullVideoUrl = statusData.videoUrl.startsWith('http') 
              ? statusData.videoUrl 
              : `${window.location.origin}${statusData.videoUrl}`;
            
            setVideoUrl(fullVideoUrl);
            const videoInfo = {
              videoId: id,
              videoUrl: fullVideoUrl,
              fileSize: statusData.fileSize
            };
            setVideoData(videoInfo);
            setGlobalVideoData(videoInfo); // Also update global store
            setGenerating(false);
          } else if (statusData.status === 'failed' || statusData.status === 'error') {
            clearInterval(pollInterval);
            setError(statusData.error || '비디오 생성 중 오류가 발생했습니다');
            setGenerating(false);
          }
        } catch (err) {
          clearInterval(pollInterval);
          setError('상태 확인 중 오류가 발생했습니다');
          setGenerating(false);
        }
      }, 5000);

      setTimeout(() => {
        clearInterval(pollInterval);
        if (generating) {
          setError('비디오 생성 시간이 초과되었습니다');
          setGenerating(false);
        }
      }, 300000);

    } catch (err) {
      setError(err.response?.data?.error || '비디오 생성 중 오류가 발생했습니다');
      setGenerating(false);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl p-8 text-white"
      >
        <div className="flex items-center justify-between">
          <div>
            <div className="flex items-center gap-3 mb-2">
              <Film className="w-8 h-8" />
              <h1 className="text-3xl font-bold">비디오 생성</h1>
            </div>
            <p className="text-orange-100">
              FFmpeg를 사용하여 고품질 YouTube Shorts를 생성합니다
            </p>
          </div>
          <button
            onClick={() => {
              if (confirm('모든 설정을 기본값으로 초기화하시겠습니까?\n(배경 음악/이미지 파일은 유지됩니다)')) {
                // 기본값으로 초기화
                setEffectSettings({ imageEffect: 'auto', effectIntensity: 'medium' });
                setBgMusic({ ...bgMusic, enabled: false, volume: 0.3 });
                setBgImage({ ...bgImage, enabled: false, opacity: 0.5, position: 'bottom-right' });
                setSubtitleSettings({
                  fontSize: 48,
                  fontFamily: 'NanumGothicBold',
                  color: '#FFFFFF',
                  position: 'bottom',
                  yOffset: 180,
                  strokeWidth: 2,
                  strokeColor: '#000000',
                  boxOpacity: 0.7,
                  lineSpacing: 10
                });
                setTitleSettings({
                  enabled: true,
                  fontSize: 64,
                  fontFamily: 'NanumGothicBold',
                  color: '#FFFF00',
                  position: 'top',
                  yOffset: 150,
                  strokeWidth: 2,
                  strokeColor: '#000000',
                  boxOpacity: 0.7
                });
                
                // localStorage 초기화
                localStorage.removeItem('videoSettings_effects');
                localStorage.removeItem('videoSettings_bgMusic');
                localStorage.removeItem('videoSettings_bgImage');
                localStorage.removeItem('videoSettings_subtitle');
                localStorage.removeItem('videoSettings_title');
                
                alert('✅ 설정이 초기화되었습니다');
              }
            }}
            className="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg font-medium transition-colors flex items-center gap-2 backdrop-blur-sm"
          >
            <SettingsIcon className="w-5 h-5" />
            설정 초기화
          </button>
        </div>
      </motion.div>

      {/* Summary */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <h3 className="font-semibold text-gray-900 mb-4">생성 준비 상태</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className={`p-4 rounded-lg border-2 ${script ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-gray-50'}`}>
            <div className="flex items-center gap-2 mb-2">
              {script ? (
                <CheckCircle className="w-5 h-5 text-green-500" />
              ) : (
                <AlertCircle className="w-5 h-5 text-gray-400" />
              )}
              <span className="font-medium text-gray-900">스크립트</span>
            </div>
            <p className="text-sm text-gray-600">
              {script ? `${script.length}개 장면 준비 완료` : '스크립트 없음'}
            </p>
          </div>

          <div className={`p-4 rounded-lg border-2 ${voiceData ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-gray-50'}`}>
            <div className="flex items-center gap-2 mb-2">
              {voiceData ? (
                <CheckCircle className="w-5 h-5 text-green-500" />
              ) : (
                <AlertCircle className="w-5 h-5 text-gray-400" />
              )}
              <span className="font-medium text-gray-900">음성</span>
            </div>
            <p className="text-sm text-gray-600">
              {voiceData ? '음성 파일 준비 완료' : '음성 없음'}
            </p>
          </div>

          <div className={`p-4 rounded-lg border-2 ${videoUrl ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-gray-50'}`}>
            <div className="flex items-center gap-2 mb-2">
              {videoUrl ? (
                <CheckCircle className="w-5 h-5 text-green-500" />
              ) : (
                <AlertCircle className="w-5 h-5 text-gray-400" />
              )}
              <span className="font-medium text-gray-900">비디오</span>
            </div>
            <p className="text-sm text-gray-600">
              {videoUrl ? '비디오 생성 완료' : '비디오 없음'}
            </p>
          </div>
        </div>
      </motion.div>

      {/* 배경 음악 설정 */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.15 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-3">
            <Music className="w-6 h-6 text-orange-500" />
            <h3 className="font-semibold text-gray-900">배경 음악</h3>
            <span className="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">자동 저장됨</span>
          </div>
          <label className="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              checked={bgMusic.enabled}
              onChange={(e) => setBgMusic({...bgMusic, enabled: e.target.checked})}
              className="sr-only peer"
            />
            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
          </label>
        </div>
        
        {bgMusic.enabled && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                음악 파일 업로드
              </label>
              <div className="flex gap-3">
                <label className="flex-1 flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-orange-400 transition-colors">
                  <input
                    type="file"
                    accept="audio/*"
                    onChange={handleBgMusicUpload}
                    className="hidden"
                  />
                  <Upload className="w-5 h-5 text-gray-400 mr-2" />
                  <span className="text-sm text-gray-600">
                    {bgMusic.file ? bgMusic.file.name : '음악 파일 선택 (MP3, WAV 등)'}
                  </span>
                </label>
                {bgMusic.file && (
                  <button
                    onClick={() => setBgMusic({...bgMusic, file: null, url: ''})}
                    className="px-4 py-3 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"
                  >
                    <X className="w-5 h-5" />
                  </button>
                )}
              </div>
            </div>
            
            {bgMusic.url && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  미리듣기
                </label>
                <audio controls className="w-full" src={bgMusic.url} />
              </div>
            )}
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                배경음악 볼륨: {Math.round(bgMusic.volume * 100)}%
              </label>
              <input
                type="range"
                min="0"
                max="1"
                step="0.05"
                value={bgMusic.volume}
                onChange={(e) => setBgMusic({...bgMusic, volume: parseFloat(e.target.value)})}
                className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              />
            </div>
          </div>
        )}
      </motion.div>

      {/* 배경 이미지/워터마크 설정 */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.17 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-3">
            <ImageIcon className="w-6 h-6 text-orange-500" />
            <h3 className="font-semibold text-gray-900">배경 이미지 / 워터마크</h3>
            <span className="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">자동 저장됨</span>
          </div>
          <label className="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              checked={bgImage.enabled}
              onChange={(e) => setBgImage({...bgImage, enabled: e.target.checked})}
              className="sr-only peer"
            />
            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
          </label>
        </div>
        
        {bgImage.enabled && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                이미지 파일 업로드
              </label>
              <div className="flex gap-3">
                <label className="flex-1 flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-orange-400 transition-colors">
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleBgImageUpload}
                    className="hidden"
                  />
                  <Upload className="w-5 h-5 text-gray-400 mr-2" />
                  <span className="text-sm text-gray-600">
                    {bgImage.file ? bgImage.file.name : '이미지 파일 선택 (PNG, JPG 등)'}
                  </span>
                </label>
                {bgImage.file && (
                  <button
                    onClick={() => setBgImage({...bgImage, file: null, url: ''})}
                    className="px-4 py-3 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"
                  >
                    <X className="w-5 h-5" />
                  </button>
                )}
              </div>
            </div>
            
            {bgImage.url && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  미리보기
                </label>
                <img src={bgImage.url} alt="배경 이미지" className="max-w-xs rounded-lg border border-gray-200" />
              </div>
            )}
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                위치 선택
              </label>
              <div className="grid grid-cols-3 gap-2">
                {['top-left', 'top-right', 'center', 'bottom-left', 'bottom-right'].map((pos) => (
                  <label key={pos} className={`flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-all ${bgImage.position === pos ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                    <input
                      type="radio"
                      name="bgImagePosition"
                      value={pos}
                      checked={bgImage.position === pos}
                      onChange={(e) => setBgImage({...bgImage, position: e.target.value})}
                      className="sr-only"
                    />
                    <span className="text-sm font-medium">{pos.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                  </label>
                ))}
              </div>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                투명도: {Math.round(bgImage.opacity * 100)}%
              </label>
              <input
                type="range"
                min="0"
                max="1"
                step="0.05"
                value={bgImage.opacity}
                onChange={(e) => setBgImage({...bgImage, opacity: parseFloat(e.target.value)})}
                className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              />
            </div>
          </div>
        )}
      </motion.div>

      {/* Effect Options */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <div className="flex items-center gap-3 mb-4">
          <h3 className="font-semibold text-gray-900">이미지 효과 설정</h3>
          <span className="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">자동 저장됨</span>
        </div>
        
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              이미지 효과
            </label>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
              <label className={`relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.imageEffect === 'auto' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                <input
                  type="radio"
                  name="imageEffect"
                  value="auto"
                  checked={effectSettings.imageEffect === 'auto'}
                  onChange={(e) => setEffectSettings({...effectSettings, imageEffect: e.target.value})}
                  className="sr-only"
                />
                <div className="text-center">
                  <div className="text-2xl mb-1">🎲</div>
                  <div className="font-medium text-sm">자동 (추천)</div>
                  <div className="text-xs text-gray-500 mt-1">효과 자동 순환</div>
                </div>
              </label>
              
              <label className={`relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.imageEffect === 'zoom-in' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                <input
                  type="radio"
                  name="imageEffect"
                  value="zoom-in"
                  checked={effectSettings.imageEffect === 'zoom-in'}
                  onChange={(e) => setEffectSettings({...effectSettings, imageEffect: e.target.value})}
                  className="sr-only"
                />
                <div className="text-center">
                  <div className="text-2xl mb-1">🔍+</div>
                  <div className="font-medium text-sm">줌인</div>
                  <div className="text-xs text-gray-500 mt-1">확대 효과</div>
                </div>
              </label>
              
              <label className={`relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.imageEffect === 'zoom-out' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                <input
                  type="radio"
                  name="imageEffect"
                  value="zoom-out"
                  checked={effectSettings.imageEffect === 'zoom-out'}
                  onChange={(e) => setEffectSettings({...effectSettings, imageEffect: e.target.value})}
                  className="sr-only"
                />
                <div className="text-center">
                  <div className="text-2xl mb-1">🔍-</div>
                  <div className="font-medium text-sm">줌아웃</div>
                  <div className="text-xs text-gray-500 mt-1">축소 효과</div>
                </div>
              </label>
              
              <label className={`relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.imageEffect === 'pan-lr' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                <input
                  type="radio"
                  name="imageEffect"
                  value="pan-lr"
                  checked={effectSettings.imageEffect === 'pan-lr'}
                  onChange={(e) => setEffectSettings({...effectSettings, imageEffect: e.target.value})}
                  className="sr-only"
                />
                <div className="text-center">
                  <div className="text-2xl mb-1">➡️</div>
                  <div className="font-medium text-sm">좌→우 패닝</div>
                  <div className="text-xs text-gray-500 mt-1">좌우 이동 + 줌</div>
                </div>
              </label>
              
              <label className={`relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.imageEffect === 'pan-rl' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                <input
                  type="radio"
                  name="imageEffect"
                  value="pan-rl"
                  checked={effectSettings.imageEffect === 'pan-rl'}
                  onChange={(e) => setEffectSettings({...effectSettings, imageEffect: e.target.value})}
                  className="sr-only"
                />
                <div className="text-center">
                  <div className="text-2xl mb-1">⬅️</div>
                  <div className="font-medium text-sm">우→좌 패닝</div>
                  <div className="text-xs text-gray-500 mt-1">우좌 이동 + 줌</div>
                </div>
              </label>
              
              <label className={`relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.imageEffect === 'none' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                <input
                  type="radio"
                  name="imageEffect"
                  value="none"
                  checked={effectSettings.imageEffect === 'none'}
                  onChange={(e) => setEffectSettings({...effectSettings, imageEffect: e.target.value})}
                  className="sr-only"
                />
                <div className="text-center">
                  <div className="text-2xl mb-1">⏸️</div>
                  <div className="font-medium text-sm">효과 없음</div>
                  <div className="text-xs text-gray-500 mt-1">정적 이미지</div>
                </div>
              </label>
            </div>
          </div>
          
          {effectSettings.imageEffect !== 'none' && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                효과 강도
              </label>
              <div className="grid grid-cols-3 gap-3">
                <label className={`flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.effectIntensity === 'low' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                  <input
                    type="radio"
                    name="effectIntensity"
                    value="low"
                    checked={effectSettings.effectIntensity === 'low'}
                    onChange={(e) => setEffectSettings({...effectSettings, effectIntensity: e.target.value})}
                    className="sr-only"
                  />
                  <div className="text-center">
                    <div className="font-medium text-sm">약함</div>
                    <div className="text-xs text-gray-500 mt-1">1.0x → 1.08x</div>
                  </div>
                </label>
                
                <label className={`flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.effectIntensity === 'medium' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                  <input
                    type="radio"
                    name="effectIntensity"
                    value="medium"
                    checked={effectSettings.effectIntensity === 'medium'}
                    onChange={(e) => setEffectSettings({...effectSettings, effectIntensity: e.target.value})}
                    className="sr-only"
                  />
                  <div className="text-center">
                    <div className="font-medium text-sm">보통 (추천)</div>
                    <div className="text-xs text-gray-500 mt-1">1.0x → 1.15x</div>
                  </div>
                </label>
                
                <label className={`flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-all ${effectSettings.effectIntensity === 'high' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`}>
                  <input
                    type="radio"
                    name="effectIntensity"
                    value="high"
                    checked={effectSettings.effectIntensity === 'high'}
                    onChange={(e) => setEffectSettings({...effectSettings, effectIntensity: e.target.value})}
                    className="sr-only"
                  />
                  <div className="text-center">
                    <div className="font-medium text-sm">강함</div>
                    <div className="text-xs text-gray-500 mt-1">1.0x → 1.25x</div>
                  </div>
                </label>
              </div>
            </div>
          )}
        </div>
      </motion.div>

      {/* 자막 설정 */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.25 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <div className="flex items-center gap-3 mb-4">
          <Type className="w-6 h-6 text-orange-500" />
          <h3 className="font-semibold text-gray-900">자막 설정</h3>
          <span className="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">자동 저장됨</span>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              폰트
            </label>
            <select
              value={subtitleSettings.fontFamily}
              onChange={(e) => setSubtitleSettings({...subtitleSettings, fontFamily: e.target.value})}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 font-preview-select"
            >
              {availableFonts.map(font => (
                <option key={font.value} value={font.value} className={`font-${font.value}`}>
                  {font.label}
                </option>
              ))}
            </select>
            <div className="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
              <p className={`text-2xl text-center font-${subtitleSettings.fontFamily}`}>
                미리보기: 한글 ABC 123
              </p>
            </div>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              크기: {subtitleSettings.fontSize}pt
            </label>
            <input
              type="range"
              min="24"
              max="72"
              step="2"
              value={subtitleSettings.fontSize}
              onChange={(e) => setSubtitleSettings({...subtitleSettings, fontSize: parseInt(e.target.value)})}
              className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              색상
            </label>
            <div className="flex gap-2">
              <input
                type="color"
                value={subtitleSettings.color}
                onChange={(e) => setSubtitleSettings({...subtitleSettings, color: e.target.value})}
                className="w-12 h-10 rounded cursor-pointer"
              />
              <input
                type="text"
                value={subtitleSettings.color}
                onChange={(e) => setSubtitleSettings({...subtitleSettings, color: e.target.value})}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                placeholder="#FFFFFF"
              />
            </div>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              위치
            </label>
            <select
              value={subtitleSettings.position}
              onChange={(e) => setSubtitleSettings({...subtitleSettings, position: e.target.value})}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
            >
              <option value="top">상단</option>
              <option value="center">중앙</option>
              <option value="bottom">하단</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              테두리 두께: {subtitleSettings.strokeWidth}px
            </label>
            <input
              type="range"
              min="0"
              max="8"
              step="1"
              value={subtitleSettings.strokeWidth}
              onChange={(e) => setSubtitleSettings({...subtitleSettings, strokeWidth: parseInt(e.target.value)})}
              className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            />
            <p className="text-xs text-gray-500 mt-1">
              {subtitleSettings.strokeWidth === 0 ? '테두리 없음' : '가독성 향상을 위한 테두리'}
            </p>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              테두리 색상
            </label>
            <div className="flex gap-2">
              <input
                type="color"
                value={subtitleSettings.strokeColor}
                onChange={(e) => setSubtitleSettings({...subtitleSettings, strokeColor: e.target.value})}
                className="w-12 h-10 rounded cursor-pointer"
              />
              <input
                type="text"
                value={subtitleSettings.strokeColor}
                onChange={(e) => setSubtitleSettings({...subtitleSettings, strokeColor: e.target.value})}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                placeholder="#000000"
              />
            </div>
            <p className="text-xs text-gray-500 mt-1">
              어두운 배경엔 밝은 테두리, 밝은 배경엔 어두운 테두리 권장
            </p>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              배경 투명도: {Math.round(subtitleSettings.boxOpacity * 100)}%
            </label>
            <input
              type="range"
              min="0"
              max="1"
              step="0.1"
              value={subtitleSettings.boxOpacity}
              onChange={(e) => setSubtitleSettings({...subtitleSettings, boxOpacity: parseFloat(e.target.value)})}
              className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            />
          </div>
        </div>
      </motion.div>

      {/* 제목 설정 */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.28 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-3">
            <SettingsIcon className="w-6 h-6 text-orange-500" />
            <h3 className="font-semibold text-gray-900">제목 설정</h3>
            <span className="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">자동 저장됨</span>
          </div>
          <label className="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              checked={titleSettings.enabled}
              onChange={(e) => setTitleSettings({...titleSettings, enabled: e.target.checked})}
              className="sr-only peer"
            />
            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
          </label>
        </div>
        
        {titleSettings.enabled && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                폰트
              </label>
              <select
                value={titleSettings.fontFamily}
                onChange={(e) => setTitleSettings({...titleSettings, fontFamily: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 font-preview-select"
              >
                {availableFonts.map(font => (
                  <option key={font.value} value={font.value} className={`font-${font.value}`}>
                    {font.label}
                  </option>
                ))}
              </select>
              <div className="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <p className={`text-2xl text-center font-${titleSettings.fontFamily}`}>
                  미리보기: 한글 ABC 123
                </p>
              </div>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                크기: {titleSettings.fontSize}pt
              </label>
              <input
                type="range"
                min="32"
                max="96"
                step="4"
                value={titleSettings.fontSize}
                onChange={(e) => setTitleSettings({...titleSettings, fontSize: parseInt(e.target.value)})}
                className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                색상
              </label>
              <div className="flex gap-2">
                <input
                  type="color"
                  value={titleSettings.color}
                  onChange={(e) => setTitleSettings({...titleSettings, color: e.target.value})}
                  className="w-12 h-10 rounded cursor-pointer"
                />
                <input
                  type="text"
                  value={titleSettings.color}
                  onChange={(e) => setTitleSettings({...titleSettings, color: e.target.value})}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                  placeholder="#FFFF00"
                />
              </div>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                위치
              </label>
              <select
                value={titleSettings.position}
                onChange={(e) => setTitleSettings({...titleSettings, position: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
              >
                <option value="top">상단</option>
                <option value="center">중앙</option>
                <option value="bottom">하단</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                테두리 두께: {titleSettings.strokeWidth}px
              </label>
              <input
                type="range"
                min="0"
                max="8"
                step="1"
                value={titleSettings.strokeWidth}
                onChange={(e) => setTitleSettings({...titleSettings, strokeWidth: parseInt(e.target.value)})}
                className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              />
              <p className="text-xs text-gray-500 mt-1">
                {titleSettings.strokeWidth === 0 ? '테두리 없음' : '가독성 향상을 위한 테두리'}
              </p>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                테두리 색상
              </label>
              <div className="flex gap-2">
                <input
                  type="color"
                  value={titleSettings.strokeColor}
                  onChange={(e) => setTitleSettings({...titleSettings, strokeColor: e.target.value})}
                  className="w-12 h-10 rounded cursor-pointer"
                />
                <input
                  type="text"
                  value={titleSettings.strokeColor}
                  onChange={(e) => setTitleSettings({...titleSettings, strokeColor: e.target.value})}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                  placeholder="#000000"
                />
              </div>
              <p className="text-xs text-gray-500 mt-1">
                어두운 배경엔 밝은 테두리, 밝은 배경엔 어두운 테두리 권장
              </p>
            </div>
          </div>
        )}
      </motion.div>

      {/* Generate Button */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="bg-white rounded-xl shadow-lg p-6"
      >
        <button
          onClick={handleGenerate}
          disabled={generating || !script}
          className="w-full px-6 py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-medium hover:from-orange-600 hover:to-red-600 disabled:from-gray-300 disabled:to-gray-300 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3"
        >
          {generating ? (
            <>
              <Loader className="w-6 h-6 animate-spin" />
              {status} ({progress}%)
            </>
          ) : (
            <>
              <Video className="w-6 h-6" />
              비디오 생성 시작
            </>
          )}
        </button>

        {generating && (
          <div className="mt-4 space-y-3">
            <div className="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <motion.div
                initial={{ width: 0 }}
                animate={{ width: `${progress}%` }}
                transition={{ duration: 0.5 }}
                className="h-full bg-gradient-to-r from-orange-500 to-red-500"
              />
            </div>
            <p className="text-center text-sm text-gray-600">{status}</p>
          </div>
        )}

        {error && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3"
          >
            <AlertCircle className="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
            <p className="text-red-700">{error}</p>
          </motion.div>
        )}
      </motion.div>

      {/* Video Preview */}
      {videoUrl && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-white rounded-xl shadow-lg p-6"
        >
          <div className="flex items-center gap-3 mb-4">
            <CheckCircle className="w-6 h-6 text-green-500" />
            <h3 className="font-semibold text-gray-900">비디오 생성 완료!</h3>
          </div>

          <div className="aspect-[9/16] bg-black rounded-lg overflow-hidden max-w-sm mx-auto">
            <video
              controls
              className="w-full h-full"
              src={videoUrl}
            >
              Your browser does not support the video tag.
            </video>
          </div>

          {videoData.fileSize && (
            <div className="mt-4 text-center text-sm text-gray-600">
              파일 크기: {(videoData.fileSize / 1024 / 1024).toFixed(2)} MB
            </div>
          )}

          <div className="mt-6 flex gap-3 justify-center">
            <button
              onClick={async () => {
                try {
                  console.log('🎬 다운로드 시작:', videoUrl);
                  
                  let blob;
                  let filename = `shorts_video_${videoData.videoId || Date.now()}.mp4`;
                  
                  // videoUrl이 data: URL인지 확인
                  if (videoUrl.startsWith('data:')) {
                    console.log('📦 Data URL 감지, base64 디코딩 중...');
                    // data:video/mp4;base64,... 형식에서 base64 부분 추출
                    const base64Data = videoUrl.split(',')[1];
                    const binaryString = atob(base64Data);
                    const bytes = new Uint8Array(binaryString.length);
                    for (let i = 0; i < binaryString.length; i++) {
                      bytes[i] = binaryString.charCodeAt(i);
                    }
                    blob = new Blob([bytes], { type: 'video/mp4' });
                    console.log('✅ Blob 생성 완료:', blob.size, 'bytes');
                  } else {
                    console.log('🌐 URL에서 다운로드 중...');
                    // 일반 URL에서 fetch
                    const response = await fetch(videoUrl, {
                      mode: 'cors',
                      credentials: 'omit'
                    });
                    
                    if (!response.ok) {
                      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    blob = await response.blob();
                    console.log('✅ Fetch 완료:', blob.size, 'bytes');
                  }
                  
                  // Blob을 다운로드 가능한 URL로 변환
                  const blobUrl = window.URL.createObjectURL(blob);
                  const link = document.createElement('a');
                  link.href = blobUrl;
                  link.download = filename;
                  link.style.display = 'none';
                  document.body.appendChild(link);
                  
                  console.log('📥 다운로드 트리거:', filename);
                  link.click();
                  
                  // 클린업
                  setTimeout(() => {
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(blobUrl);
                    console.log('✅ 다운로드 완료 및 메모리 정리');
                  }, 100);
                  
                } catch (error) {
                  console.error('❌ 다운로드 실패:', error);
                  alert(`다운로드 실패: ${error.message}\n\n비디오 URL이 올바른지 확인해주세요.`);
                }
              }}
              className="px-8 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-medium hover:from-orange-600 hover:to-red-600 transition-colors flex items-center gap-2 shadow-lg cursor-pointer"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              비디오 다운로드
            </button>
            <button
              onClick={() => {
                setVideoUrl('');
                setVideoData(null);
                setStatus('');
                setProgress(0);
                setError('');
              }}
              className="px-6 py-3 bg-gray-500 text-white rounded-lg font-medium hover:bg-gray-600 transition-colors"
            >
              새로 만들기
            </button>
          </div>
        </motion.div>
      )}
    </div>
  );
}
