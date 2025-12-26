import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    
    const response = await fetch('http://localhost:5003/generate-video', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(body),
    });

    const data = await response.json();
    
    // 비디오 URL을 상대 경로로 변환
    if (data.success && data.video_url) {
      data.video_url = data.video_url;
    }
    
    return NextResponse.json(data);
  } catch (error) {
    console.error('Video API error:', error);
    return NextResponse.json(
      { success: false, error: 'Failed to generate video' },
      { status: 500 }
    );
  }
}
