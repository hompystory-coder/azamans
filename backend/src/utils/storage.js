// 스토리지 유틸리티
import fs from 'fs/promises';
import path from 'path';

export async function ensureDirectories() {
  const dirs = [
    process.env.UPLOAD_DIR || '/tmp/uploads',
    process.env.OUTPUT_DIR || '/tmp/outputs',
    process.env.TEMP_DIR || '/tmp/temp',
    path.join(process.env.UPLOAD_DIR || '/tmp/uploads', 'backgrounds'),
    path.join(process.env.UPLOAD_DIR || '/tmp/uploads', 'music'),
    path.join(process.env.OUTPUT_DIR || '/tmp/outputs', 'videos'),
    path.join(process.env.OUTPUT_DIR || '/tmp/outputs', 'audio'),
    path.join(process.env.OUTPUT_DIR || '/tmp/outputs', 'images')
  ];

  for (const dir of dirs) {
    try {
      await fs.access(dir);
      console.log(`✅ Directory exists: ${dir}`);
    } catch {
      await fs.mkdir(dir, { recursive: true });
      console.log(`✅ Directory created: ${dir}`);
    }
  }
}

export function getUploadPath(filename) {
  return path.join(process.env.UPLOAD_DIR || '/tmp/uploads', filename);
}

export function getOutputPath(filename) {
  return path.join(process.env.OUTPUT_DIR || '/tmp/outputs', filename);
}

export function getTempPath(filename) {
  return path.join(process.env.TEMP_DIR || '/tmp/temp', filename);
}
