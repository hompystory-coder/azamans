import { NextResponse } from 'next/server';
import { createReadStream, statSync } from 'fs';
import path from 'path';

const MUSIC_STORAGE_PATH = '/mnt/music-storage/generated-music';

export async function GET(
  request: Request,
  { params }: { params: { path: string[] } }
) {
  try {
    const filePath = path.join(MUSIC_STORAGE_PATH, ...params.path);
    
    // Security check: ensure file is within music storage
    if (!filePath.startsWith(MUSIC_STORAGE_PATH)) {
      return NextResponse.json({ error: 'Invalid path' }, { status: 403 });
    }
    
    // Check if file exists
    try {
      const stats = statSync(filePath);
      if (!stats.isFile()) {
        return NextResponse.json({ error: 'Not a file' }, { status: 404 });
      }
    } catch {
      return NextResponse.json({ error: 'File not found' }, { status: 404 });
    }
    
    // Stream the file
    const fileStream = createReadStream(filePath);
    const chunks: Buffer[] = [];
    
    for await (const chunk of fileStream) {
      chunks.push(chunk);
    }
    
    const buffer = Buffer.concat(chunks);
    
    return new NextResponse(buffer, {
      headers: {
        'Content-Type': 'audio/mpeg',
        'Content-Length': buffer.length.toString(),
        'Accept-Ranges': 'bytes',
      },
    });
  } catch (error: any) {
    console.error('Error serving music file:', error);
    return NextResponse.json({ error: error.message }, { status: 500 });
  }
}
