// Load environment variables before anything else
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Load .env from backend directory
dotenv.config({ path: path.join(__dirname, '..', '..', '.env') });

console.log('🔧 Environment variables loaded');
console.log(`📂 OUTPUT_DIR: ${process.env.OUTPUT_DIR || 'NOT SET'}`);
console.log(`📂 UPLOAD_DIR: ${process.env.UPLOAD_DIR || 'NOT SET'}`);
console.log(`📂 TEMP_DIR: ${process.env.TEMP_DIR || 'NOT SET'}`);
