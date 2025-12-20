const { v4: uuidv4 } = require('uuid');
const path = require('path');
const fs = require('fs').promises;

// In-memory storage (나중에 DB로 교체)
const projects = new Map();

// GET all projects
exports.getAllProjects = async (req, res) => {
  try {
    const projectList = Array.from(projects.values());
    
    res.json({
      success: true,
      data: projectList,
      count: projectList.length
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// GET project by ID
exports.getProjectById = async (req, res) => {
  try {
    const { id } = req.params;
    const project = projects.get(id);

    if (!project) {
      return res.status(404).json({
        success: false,
        error: 'Project not found'
      });
    }

    res.json({
      success: true,
      data: project
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST create new project
exports.createProject = async (req, res) => {
  try {
    const { 
      title, 
      characterId, 
      voiceId, 
      fontId,
      mode = 'auto',
      style = 'character'
    } = req.body;

    if (!title) {
      return res.status(400).json({
        success: false,
        error: 'Title is required'
      });
    }

    const project = {
      id: uuidv4(),
      title: title,
      characterId: characterId || 'forty',
      voiceId: voiceId || 'leda',
      fontId: fontId || 'nanum-gothic-bold',
      mode: mode,
      style: style,
      status: 'draft',
      scenes: [],
      settings: {},
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };

    projects.set(project.id, project);

    // Create project directory
    const projectDir = path.join(__dirname, '../generated/projects', project.id);
    await fs.mkdir(projectDir, { recursive: true });

    res.status(201).json({
      success: true,
      data: project
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// PUT update project
exports.updateProject = async (req, res) => {
  try {
    const { id } = req.params;
    const updates = req.body;

    const project = projects.get(id);
    if (!project) {
      return res.status(404).json({
        success: false,
        error: 'Project not found'
      });
    }

    // Update project
    const updatedProject = {
      ...project,
      ...updates,
      id: project.id, // Prevent ID change
      updatedAt: new Date().toISOString()
    };

    projects.set(id, updatedProject);

    res.json({
      success: true,
      data: updatedProject
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// DELETE project
exports.deleteProject = async (req, res) => {
  try {
    const { id } = req.params;

    if (!projects.has(id)) {
      return res.status(404).json({
        success: false,
        error: 'Project not found'
      });
    }

    projects.delete(id);

    // Delete project directory
    const projectDir = path.join(__dirname, '../generated/projects', id);
    try {
      await fs.rm(projectDir, { recursive: true, force: true });
    } catch (error) {
      console.error('Error deleting project directory:', error);
    }

    res.json({
      success: true,
      message: 'Project deleted successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST save project settings
exports.saveSettings = async (req, res) => {
  try {
    const { id } = req.params;
    const { settings } = req.body;

    const project = projects.get(id);
    if (!project) {
      return res.status(404).json({
        success: false,
        error: 'Project not found'
      });
    }

    project.settings = settings;
    project.updatedAt = new Date().toISOString();

    projects.set(id, project);

    // Save settings to file
    const settingsFile = path.join(
      __dirname, 
      '../generated/projects', 
      id, 
      'settings.json'
    );
    await fs.writeFile(settingsFile, JSON.stringify(settings, null, 2));

    res.json({
      success: true,
      data: project
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// GET project settings
exports.getSettings = async (req, res) => {
  try {
    const { id } = req.params;

    const project = projects.get(id);
    if (!project) {
      return res.status(404).json({
        success: false,
        error: 'Project not found'
      });
    }

    res.json({
      success: true,
      data: project.settings
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST generate shorts
exports.generateShorts = async (req, res) => {
  try {
    const { id } = req.params;
    const { scenes, settings } = req.body;

    const project = projects.get(id);
    if (!project) {
      return res.status(404).json({
        success: false,
        error: 'Project not found'
      });
    }

    // Update project with scenes
    project.scenes = scenes;
    project.settings = settings || project.settings;
    project.status = 'generating';
    project.updatedAt = new Date().toISOString();

    projects.set(id, project);

    // Create job ID for generation queue
    const jobId = uuidv4();

    // TODO: Add to Bull queue for background processing
    // For now, return job ID immediately
    
    // Emit socket event for real-time updates
    if (req.io) {
      req.io.emit('generation:started', {
        projectId: id,
        jobId: jobId,
        status: 'queued'
      });
    }

    res.json({
      success: true,
      data: {
        projectId: id,
        jobId: jobId,
        status: 'queued',
        message: 'Generation started'
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// GET generation status
exports.getStatus = async (req, res) => {
  try {
    const { id } = req.params;

    const project = projects.get(id);
    if (!project) {
      return res.status(404).json({
        success: false,
        error: 'Project not found'
      });
    }

    // TODO: Get actual status from queue/job
    res.json({
      success: true,
      data: {
        projectId: id,
        status: project.status,
        progress: 0,
        currentStep: '',
        estimatedTime: 1500 // 25 minutes in seconds
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

module.exports = exports;
