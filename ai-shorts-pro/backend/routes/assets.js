const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const assetsController = require('../controllers/assetsController');

// Configure multer for file uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadType = req.body.type || 'general';
    cb(null, path.join(__dirname, `../uploads/${uploadType}/`));
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
  }
});

const upload = multer({
  storage: storage,
  limits: {
    fileSize: 50 * 1024 * 1024 // 50MB limit
  },
  fileFilter: (req, file, cb) => {
    // Allowed file types
    const allowedTypes = {
      image: /jpeg|jpg|png|gif|webp/,
      audio: /mp3|wav|m4a|aac/,
      font: /ttf|otf|woff|woff2/
    };
    
    const type = req.body.type || 'image';
    const extname = allowedTypes[type].test(path.extname(file.originalname).toLowerCase());
    const mimetype = allowedTypes[type].test(file.mimetype);
    
    if (extname && mimetype) {
      return cb(null, true);
    } else {
      cb(new Error(`Invalid file type for ${type}`));
    }
  }
});

// POST upload background music
router.post('/upload/music', upload.single('file'), assetsController.uploadMusic);

// POST upload background image
router.post('/upload/image', upload.single('file'), assetsController.uploadImage);

// POST upload font
router.post('/upload/font', upload.single('file'), assetsController.uploadFont);

// GET list all user assets
router.get('/list', assetsController.listAssets);

// DELETE asset
router.delete('/:id', assetsController.deleteAsset);

// GET asset by ID
router.get('/:id', assetsController.getAsset);

module.exports = router;
