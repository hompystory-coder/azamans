const path = require('path');
const fs = require('fs').promises;
const { v4: uuidv4 } = require('uuid');

// In-memory asset storage
const assets = new Map();

// POST upload background music
exports.uploadMusic = async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({
        success: false,
        error: 'No file uploaded'
      });
    }

    const asset = {
      id: uuidv4(),
      type: 'music',
      filename: req.file.filename,
      originalName: req.file.originalname,
      size: req.file.size,
      path: req.file.path,
      url: `/uploads/music/${req.file.filename}`,
      uploadedAt: new Date().toISOString()
    };

    assets.set(asset.id, asset);

    res.json({
      success: true,
      data: asset
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST upload background image
exports.uploadImage = async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({
        success: false,
        error: 'No file uploaded'
      });
    }

    const asset = {
      id: uuidv4(),
      type: 'image',
      filename: req.file.filename,
      originalName: req.file.originalname,
      size: req.file.size,
      path: req.file.path,
      url: `/uploads/image/${req.file.filename}`,
      uploadedAt: new Date().toISOString()
    };

    assets.set(asset.id, asset);

    res.json({
      success: true,
      data: asset
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST upload font
exports.uploadFont = async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({
        success: false,
        error: 'No file uploaded'
      });
    }

    const asset = {
      id: uuidv4(),
      type: 'font',
      filename: req.file.filename,
      originalName: req.file.originalname,
      size: req.file.size,
      path: req.file.path,
      url: `/uploads/font/${req.file.filename}`,
      uploadedAt: new Date().toISOString()
    };

    assets.set(asset.id, asset);

    res.json({
      success: true,
      data: asset
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// GET list all assets
exports.listAssets = async (req, res) => {
  try {
    const { type } = req.query;

    let assetList = Array.from(assets.values());

    // Filter by type if provided
    if (type) {
      assetList = assetList.filter(a => a.type === type);
    }

    // Sort by upload date (newest first)
    assetList.sort((a, b) => 
      new Date(b.uploadedAt) - new Date(a.uploadedAt)
    );

    res.json({
      success: true,
      data: assetList,
      count: assetList.length
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// GET asset by ID
exports.getAsset = async (req, res) => {
  try {
    const { id } = req.params;

    const asset = assets.get(id);
    if (!asset) {
      return res.status(404).json({
        success: false,
        error: 'Asset not found'
      });
    }

    res.json({
      success: true,
      data: asset
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// DELETE asset
exports.deleteAsset = async (req, res) => {
  try {
    const { id } = req.params;

    const asset = assets.get(id);
    if (!asset) {
      return res.status(404).json({
        success: false,
        error: 'Asset not found'
      });
    }

    // Delete file from disk
    try {
      await fs.unlink(asset.path);
    } catch (error) {
      console.error('Error deleting file:', error);
    }

    // Remove from map
    assets.delete(id);

    res.json({
      success: true,
      message: 'Asset deleted successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

module.exports = exports;
