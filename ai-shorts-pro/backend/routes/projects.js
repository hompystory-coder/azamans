const express = require('express');
const router = express.Router();
const projectController = require('../controllers/projectController');

// GET all projects
router.get('/', projectController.getAllProjects);

// GET project by ID
router.get('/:id', projectController.getProjectById);

// POST create new project
router.post('/', projectController.createProject);

// PUT update project
router.put('/:id', projectController.updateProject);

// DELETE project
router.delete('/:id', projectController.deleteProject);

// POST save project settings
router.post('/:id/settings', projectController.saveSettings);

// GET project settings
router.get('/:id/settings', projectController.getSettings);

// POST generate shorts
router.post('/:id/generate', projectController.generateShorts);

// GET generation status
router.get('/:id/status', projectController.getStatus);

module.exports = router;
