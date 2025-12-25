import './globals.css'
import type { Metadata } from 'next'
import { Inter } from 'next/font/google'

const inter = Inter({ subsets: ['latin'] })

export const metadata: Metadata = {
  title: 'Zero-Install AI Studio - 설치 없이 바로 시작하는 AI 쇼츠 생성',
  description: '15초 만에 시작하고, 3분 안에 첫 영상 완성! 설치 없음, 무료로 무제한 AI 쇼츠 제작',
  keywords: ['AI', '쇼츠', '영상 생성', 'WebGPU', '무료', '설치 없음'],
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="ko">
      <body className={inter.className}>{children}</body>
    </html>
  )
}
