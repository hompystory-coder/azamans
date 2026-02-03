import { NextResponse } from 'next/server';
import { promises as fs } from 'fs';
import path from 'path';

export const dynamic = 'force-dynamic';

const MUSIC_STORAGE_PATH = '/mnt/music-storage/generated-music';

interface MusicFile {
  filename: string;
  path: string;
  url: string;
  size: number;
  createdAt: string;
  directory: string;
}

async function scanMusicFiles(dir: string, baseDir: string = dir): Promise<MusicFile[]> {
  const files: MusicFile[] = [];
  
  try {
    const entries = await fs.readdir(dir, { withFileTypes: true });
    
    for (const entry of entries) {
      const fullPath = path.join(dir, entry.name);
      
      if (entry.isDirectory()) {
        // Recursively scan subdirectories
        const subFiles = await scanMusicFiles(fullPath, baseDir);
        files.push(...subFiles);
      } else if (entry.isFile() && entry.name.endsWith('.mp3')) {
        // Get file stats
        const stats = await fs.stat(fullPath);
        const relativePath = path.relative(baseDir, fullPath);
        
        files.push({
          filename: entry.name,
          path: fullPath,
          url: `/music-files/${relativePath}`,
          size: stats.size,
          createdAt: stats.mtime.toISOString(),
          directory: path.dirname(relativePath)
        });
      }
    }
  } catch (error) {
    console.error(`Error scanning directory ${dir}:`, error);
  }
  
  return files;
}

export async function GET(request: Request) {
  try {
    const { searchParams } = new URL(request.url);
    const year = searchParams.get('year');
    const month = searchParams.get('month');
    const limit = parseInt(searchParams.get('limit') || '100');
    const offset = parseInt(searchParams.get('offset') || '0');
    
    // Determine scan path
    let scanPath = MUSIC_STORAGE_PATH;
    if (year) {
      scanPath = path.join(MUSIC_STORAGE_PATH, year);
      if (month) {
        scanPath = path.join(scanPath, month.padStart(2, '0'));
      }
    }
    
    // Check if directory exists
    try {
      await fs.access(scanPath);
    } catch {
      return NextResponse.json({ 
        success: false, 
        error: 'Directory not found',
        path: scanPath
      }, { status: 404 });
    }
    
    // Scan for music files
    const allFiles = await scanMusicFiles(scanPath);
    
    // Sort by creation date (newest first)
    allFiles.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
    
    // Apply pagination
    const paginatedFiles = allFiles.slice(offset, offset + limit);
    
    return NextResponse.json({
      success: true,
      total: allFiles.length,
      limit,
      offset,
      files: paginatedFiles,
      filters: {
        year: year || null,
        month: month || null
      }
    });
  } catch (error: any) {
    console.error('Error listing music files:', error);
    return NextResponse.json({ 
      success: false, 
      error: error.message 
    }, { status: 500 });
  }
}
