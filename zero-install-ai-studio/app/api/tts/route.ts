import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    
    // TTS 생성기로 요청 전달
    const response = await fetch('http://localhost:5005/generate-tts', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(body),
    });

    const data = await response.json();
    return NextResponse.json(data);
  } catch (error) {
    console.error('TTS API error:', error);
    return NextResponse.json(
      { success: false, error: 'TTS 생성 실패' },
      { status: 500 }
    );
  }
}
