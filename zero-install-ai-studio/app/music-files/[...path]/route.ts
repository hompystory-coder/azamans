import { NextResponse } from 'next/server';
import { promises as fs } from 'fs';
import path from 'path';

export const dynamic = 'force-dynamic';

const MUSIC_STORAGE_PATH = '/mnt/music-storage/generated-music';

export async function GET(
  request: Request,
  { params }: { params: { path: string[] } }
) {
  try {
    const filePath = params.path.join('/');
    const fullPath = path.join(MUSIC_STORAGE_PATH, filePath);
    
    // Security check: Ensure path is within music storage
    const realPath = await fs.realpath(fullPath);
    if (!realPath.startsWith(MUSIC_STORAGE_PATH)) {
      return NextResponse.json({ 
        success: false, 
        error: 'Access denied' 
      }, { status: 403 });
    }
    
    // Check if file exists
    try {
      await fs.access(fullPath);
    } catch {
      return NextResponse.json({ 
        success: false, 
        error: 'File not found' 
      }, { status: 404 });
    }
    
    // Read file
    const fileBuffer = await fs.readFile(fullPath);
    const stats = await fs.stat(fullPath);
    
    // Return audio file with appropriate headers
    return new NextResponse(fileBuffer, {
      headers: {
        'Content-Type': 'audio/mpeg',
        'Content-Length': stats.size.toString(),
        'Cache-Control': 'public, max-age=31536000',
        'Content-Disposition': `inline; filename="${encodeURIComponent(path.basename(fullPath))}"`,
      },
    });
  } catch (error: any) {
    console.error('Error serving music file:', error);
    return NextResponse.json({ 
      success: false, 
      error: error.message 
    }, { status: 500 });
  }
}
