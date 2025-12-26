import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    
    // 음악 매칭 API로 요청 전달
    const response = await fetch('http://localhost:5006/match-music', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(body),
    });

    const data = await response.json();
    return NextResponse.json(data);
  } catch (error) {
    console.error('Music API error:', error);
    return NextResponse.json(
      { success: false, error: '배경음악 매칭 실패' },
      { status: 500 }
    );
  }
}
