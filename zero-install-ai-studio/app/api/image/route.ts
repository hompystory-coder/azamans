import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    
    const response = await fetch('http://localhost:5002/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(body),
    });

    const data = await response.json();
    
    // 이미지 URL을 상대 경로로 변환
    if (data.success && data.image_url) {
      data.image_url = data.image_url;
    }
    
    return NextResponse.json(data);
  } catch (error) {
    console.error('Image API error:', error);
    return NextResponse.json(
      { success: false, error: 'Failed to generate image' },
      { status: 500 }
    );
  }
}
