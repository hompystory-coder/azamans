import { useState, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';
import { assetsAPI } from '../utils/api';

export default function AssetUploader({ onUploadComplete }) {
  const [uploading, setUploading] = useState(false);
  const [uploadedFiles, setUploadedFiles] = useState([]);

  const onDrop = useCallback(async (acceptedFiles) => {
    setUploading(true);

    try {
      const uploadPromises = acceptedFiles.map(async (file) => {
        const formData = new FormData();
        formData.append('file', file);
        
        const response = await assetsAPI.upload(formData);
        return {
          name: file.name,
          url: response.data.data.url,
          type: file.type
        };
      });

      const results = await Promise.all(uploadPromises);
      setUploadedFiles([...uploadedFiles, ...results]);
      
      if (onUploadComplete) {
        onUploadComplete(results);
      }
    } catch (error) {
      console.error('Upload error:', error);
      alert('파일 업로드 실패: ' + error.message);
    } finally {
      setUploading(false);
    }
  }, [uploadedFiles, onUploadComplete]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: {
      'image/*': ['.png', '.jpg', '.jpeg', '.gif'],
      'audio/*': ['.mp3', '.wav'],
      'video/*': ['.mp4', '.mov']
    },
    maxSize: 50 * 1024 * 1024 // 50MB
  });

  const removeFile = (index) => {
    setUploadedFiles(uploadedFiles.filter((_, i) => i !== index));
  };

  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold text-white">커스텀 에셋 업로드</h2>
      <p className="text-gray-300">
        배경 이미지, 배경음악, 기타 파일을 업로드하여 영상을 더욱 풍성하게 만드세요.
      </p>

      {/* Upload Area */}
      <div
        {...getRootProps()}
        className={`border-2 border-dashed rounded-xl p-12 text-center cursor-pointer transition-all ${
          isDragActive
            ? 'border-purple-500 bg-purple-500/20'
            : 'border-white/20 bg-white/5 hover:bg-white/10'
        }`}
      >
        <input {...getInputProps()} />
        <div className="text-6xl mb-4">{uploading ? '⏳' : '📁'}</div>
        {uploading ? (
          <div>
            <p className="text-white text-xl font-bold mb-2">업로드 중...</p>
            <p className="text-gray-300">잠시만 기다려주세요</p>
          </div>
        ) : isDragActive ? (
          <div>
            <p className="text-white text-xl font-bold mb-2">파일을 놓으세요</p>
            <p className="text-gray-300">여기에 드래그 앤 드롭</p>
          </div>
        ) : (
          <div>
            <p className="text-white text-xl font-bold mb-2">
              파일을 드래그 앤 드롭하거나 클릭하여 선택
            </p>
            <p className="text-gray-300 mb-4">
              이미지, 오디오, 비디오 파일 지원 (최대 50MB)
            </p>
            <div className="flex items-center justify-center space-x-4 text-sm text-gray-400">
              <span>🖼️ JPG, PNG, GIF</span>
              <span>•</span>
              <span>🎵 MP3, WAV</span>
              <span>•</span>
              <span>🎬 MP4, MOV</span>
            </div>
          </div>
        )}
      </div>

      {/* Uploaded Files */}
      {uploadedFiles.length > 0 && (
        <div className="bg-white/10 border border-white/20 rounded-xl p-6">
          <h3 className="text-white font-bold text-lg mb-4">
            업로드된 파일 ({uploadedFiles.length})
          </h3>
          <div className="space-y-3">
            {uploadedFiles.map((file, index) => (
              <div
                key={index}
                className="flex items-center justify-between bg-white/5 rounded-lg p-4 hover:bg-white/10 transition-colors"
              >
                <div className="flex items-center space-x-3 flex-1">
                  <div className="text-2xl">
                    {file.type.startsWith('image/') && '🖼️'}
                    {file.type.startsWith('audio/') && '🎵'}
                    {file.type.startsWith('video/') && '🎬'}
                  </div>
                  <div className="flex-1">
                    <div className="text-white font-semibold">{file.name}</div>
                    <div className="text-gray-400 text-sm truncate">{file.url}</div>
                  </div>
                </div>
                <div className="flex items-center space-x-2">
                  <a
                    href={file.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors"
                  >
                    미리보기
                  </a>
                  <button
                    onClick={() => removeFile(index)}
                    className="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors"
                  >
                    삭제
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Usage Tips */}
      <div className="bg-white/5 border border-white/10 rounded-lg p-6">
        <h4 className="text-white font-semibold mb-3">💡 업로드 가이드</h4>
        <ul className="space-y-2 text-gray-300 text-sm">
          <li className="flex items-start">
            <span className="mr-2">•</span>
            <span><strong>배경 이미지:</strong> 9:16 세로 비율 권장 (1080x1920px)</span>
          </li>
          <li className="flex items-start">
            <span className="mr-2">•</span>
            <span><strong>배경 음악:</strong> 30초 이하 권장, 저작권 주의</span>
          </li>
          <li className="flex items-start">
            <span className="mr-2">•</span>
            <span><strong>비디오 클립:</strong> 짧은 클립 (3~10초)이 효과적</span>
          </li>
          <li className="flex items-start">
            <span className="mr-2">•</span>
            <span><strong>파일 크기:</strong> 최대 50MB, 큰 파일은 자동 압축</span>
          </li>
        </ul>
      </div>
    </div>
  );
}
