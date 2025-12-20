const express = require('express');
const router = express.Router();
const characters = require('../../shared/characters.json');

// GET all characters
router.get('/', (req, res) => {
  try {
    res.json({
      success: true,
      data: characters.characters
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// GET character by ID
router.get('/:id', (req, res) => {
  try {
    const character = characters.characters.find(c => c.id === req.params.id);
    if (!character) {
      return res.status(404).json({
        success: false,
        error: 'Character not found'
      });
    }
    res.json({
      success: true,
      data: character
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// GET characters by category
router.get('/category/:category', (req, res) => {
  try {
    const filtered = characters.characters.filter(
      c => c.category === req.params.category
    );
    res.json({
      success: true,
      data: filtered
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

module.exports = router;
