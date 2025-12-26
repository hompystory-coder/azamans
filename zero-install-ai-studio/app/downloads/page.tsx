'use client'

import { useState } from 'react'
import Link from 'next/link'

export default function DownloadsPage() {
  const [downloadStatus, setDownloadStatus] = useState<{ [key: string]: boolean }>({})

  const documents = [
    {
      id: 'simple',
      title: '⚡ 간편 출원 가이드',
      filename: 'PATENT_SIMPLE_SUBMISSION.txt',
      description: '전자출원 시 바로 사용할 수 있는 간편 양식',
      size: '3.4 KB',
      icon: '📋',
      recommended: true
    },
    {
      id: 'application',
      title: '📄 특허 명세서 (완전판)',
      filename: 'PATENT_APPLICATION.md',
      description: '15개 청구항을 포함한 완전한 특허 명세서',
      size: '32 KB',
      icon: '📜',
      recommended: true
    },
    {
      id: 'disability',
      title: '♿ 장애인 무료 출원 가이드',
      filename: 'PATENT_DISABILITY_GUIDE.md',
      description: '100% 수수료 감면 신청 방법 상세 안내',
      size: '8.3 KB',
      icon: '💰',
      recommended: true
    },
    {
      id: 'guide',
      title: '📚 특허 출원 완전 가이드',
      filename: 'PATENT_GUIDE.md',
      description: '7개 도면 설명 및 출원 체크리스트',
      size: '43 KB',
      icon: '🎨',
      recommended: false
    }
  ]

  const handleDownload = (filename: string, id: string) => {
    setDownloadStatus({ ...downloadStatus, [id]: true })
    
    // 실제 다운로드 링크 생성
    const link = document.createElement('a')
    link.href = `/downloads/${filename}`
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    // 3초 후 상태 초기화
    setTimeout(() => {
      setDownloadStatus({ ...downloadStatus, [id]: false })
    }, 3000)
  }

  const handleDownloadAll = () => {
    documents.forEach((doc, index) => {
      setTimeout(() => {
        handleDownload(doc.filename, doc.id)
      }, index * 500)
    })
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
      {/* Header */}
      <header className="border-b bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex justify-between items-center">
            <Link href="/" className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Zero-Install AI Studio
            </Link>
            <Link 
              href="/"
              className="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors"
            >
              ← 홈으로
            </Link>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* Title Section */}
        <div className="text-center mb-12">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">
            <span className="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              특허 출원 문서 다운로드
            </span>
          </h1>
          <p className="text-xl text-gray-600 mb-6">
            세계 최초 Zero-Install AI 쇼츠 생성 시스템
          </p>
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-full">
            <span className="text-2xl">✅</span>
            <span className="font-semibold">특허 문서 작성 완료</span>
          </div>
        </div>

        {/* Download All Button */}
        <div className="mb-8 text-center">
          <button
            onClick={handleDownloadAll}
            className="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-xl transition-all transform hover:scale-105"
          >
            📦 전체 문서 한번에 다운로드
          </button>
        </div>

        {/* Documents Grid */}
        <div className="grid md:grid-cols-2 gap-6 mb-12">
          {documents.map((doc) => (
            <div
              key={doc.id}
              className={`bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all ${
                doc.recommended ? 'ring-2 ring-blue-500' : ''
              }`}
            >
              {doc.recommended && (
                <div className="bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center py-2 text-sm font-bold">
                  ⭐ 필수 문서
                </div>
              )}
              
              <div className="p-6">
                <div className="flex items-start gap-4 mb-4">
                  <div className="text-5xl">{doc.icon}</div>
                  <div className="flex-1">
                    <h3 className="text-xl font-bold mb-2">{doc.title}</h3>
                    <p className="text-gray-600 text-sm mb-3">{doc.description}</p>
                    <div className="flex items-center gap-2 text-sm text-gray-500">
                      <span>📦 파일 크기: {doc.size}</span>
                    </div>
                  </div>
                </div>

                <button
                  onClick={() => handleDownload(doc.filename, doc.id)}
                  disabled={downloadStatus[doc.id]}
                  className={`w-full py-3 rounded-lg font-bold transition-all ${
                    downloadStatus[doc.id]
                      ? 'bg-green-500 text-white'
                      : 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:shadow-lg transform hover:scale-105'
                  }`}
                >
                  {downloadStatus[doc.id] ? '✅ 다운로드 완료!' : '⬇️ 다운로드'}
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* Quick Guide */}
        <div className="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-8 mb-8">
          <h2 className="text-2xl font-bold mb-6 text-center">🚀 빠른 출원 가이드</h2>
          
          <div className="grid md:grid-cols-3 gap-6">
            <div className="bg-white rounded-xl p-6 shadow-md">
              <div className="text-3xl mb-4">1️⃣</div>
              <h3 className="font-bold text-lg mb-2">특허로 접속</h3>
              <p className="text-sm text-gray-600 mb-3">
                <a href="https://www.patent.go.kr" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                  www.patent.go.kr
                </a> 접속 후 회원가입
              </p>
              <p className="text-xs text-gray-500">⏱️ 소요시간: 10분</p>
            </div>

            <div className="bg-white rounded-xl p-6 shadow-md">
              <div className="text-3xl mb-4">2️⃣</div>
              <h3 className="font-bold text-lg mb-2">감면 신청</h3>
              <p className="text-sm text-gray-600 mb-3">
                장애인 등록증 첨부하여 100% 감면 신청
              </p>
              <p className="text-xs text-gray-500">⏱️ 소요시간: 5분</p>
            </div>

            <div className="bg-white rounded-xl p-6 shadow-md">
              <div className="text-3xl mb-4">3️⃣</div>
              <h3 className="font-bold text-lg mb-2">출원 제출</h3>
              <p className="text-sm text-gray-600 mb-3">
                다운로드한 문서 내용 복사하여 제출
              </p>
              <p className="text-xs text-gray-500">⏱️ 소요시간: 30분</p>
            </div>
          </div>

          <div className="mt-6 text-center">
            <p className="text-2xl font-bold text-blue-600">총 소요시간: 약 1시간</p>
            <p className="text-xl font-bold text-green-600 mt-2">총 비용: 0원 (100% 무료)</p>
          </div>
        </div>

        {/* Support Section */}
        <div className="bg-white rounded-2xl shadow-lg p-8">
          <h2 className="text-2xl font-bold mb-6 text-center">📞 도움이 필요하신가요?</h2>
          
          <div className="grid md:grid-cols-3 gap-6">
            <div className="text-center">
              <div className="text-4xl mb-3">☎️</div>
              <h3 className="font-bold mb-2">특허청 고객센터</h3>
              <p className="text-2xl font-bold text-blue-600 mb-1">1544-8080</p>
              <p className="text-sm text-gray-600">평일 09:00~18:00</p>
            </div>

            <div className="text-center">
              <div className="text-4xl mb-3">♿</div>
              <h3 className="font-bold mb-2">장애인 전담 상담</h3>
              <p className="text-2xl font-bold text-blue-600 mb-1">1544-8080</p>
              <p className="text-sm text-gray-600">(내선 2번)</p>
            </div>

            <div className="text-center">
              <div className="text-4xl mb-3">💼</div>
              <h3 className="font-bold mb-2">무료 변리사 지원</h3>
              <a 
                href="https://www.kipo.go.kr/ipplug" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:underline text-sm"
              >
                IP-PLUG 신청하기
              </a>
              <p className="text-sm text-gray-600 mt-1">장애인 우선 지원</p>
            </div>
          </div>
        </div>

        {/* Tech Highlights */}
        <div className="mt-12 bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl p-8">
          <h2 className="text-2xl font-bold mb-6 text-center">🎯 핵심 특허 기술</h2>
          
          <div className="grid md:grid-cols-2 gap-4">
            <div className="bg-white rounded-xl p-4 shadow-sm">
              <h3 className="font-bold mb-2">🤖 AI 프롬프트 자동 확장</h3>
              <p className="text-sm text-gray-600">비전문가의 간단한 입력을 프로 수준으로 자동 변환</p>
            </div>
            
            <div className="bg-white rounded-xl p-4 shadow-sm">
              <h3 className="font-bold mb-2">🎨 스마트 스타일 선택</h3>
              <p className="text-sm text-gray-600">컨텍스트 기반 최적 스타일 자동 매칭</p>
            </div>
            
            <div className="bg-white rounded-xl p-4 shadow-sm">
              <h3 className="font-bold mb-2">⚡ 원클릭 생성 파이프라인</h3>
              <p className="text-sm text-gray-600">6단계 자동화로 5분 이내 완성</p>
            </div>
            
            <div className="bg-white rounded-xl p-4 shadow-sm">
              <h3 className="font-bold mb-2">🌐 Zero-Install 아키텍처</h3>
              <p className="text-sm text-gray-600">브라우저만으로 GPU 활용한 AI 생성</p>
            </div>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="border-t bg-white/80 backdrop-blur-md mt-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-gray-600">
          <p>© 2024 Zero-Install AI Studio. 세계 최초 브라우저 기반 AI 쇼츠 생성 플랫폼</p>
          <p className="text-sm mt-2">특허 출원 준비 완료 | 장애인 100% 무료 출원 지원</p>
        </div>
      </footer>
    </div>
  )
}
