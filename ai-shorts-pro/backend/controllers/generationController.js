const { v4: uuidv4 } = require('uuid');
const path = require('path');
const fs = require('fs').promises;

// Job storage (나중에 Bull Queue로 교체)
const jobs = new Map();

// POST start shorts generation
exports.startGeneration = async (req, res) => {
  try {
    const { 
      projectId,
      scenes,
      characterId,
      voiceId,
      settings
    } = req.body;

    if (!projectId || !scenes || scenes.length === 0) {
      return res.status(400).json({
        success: false,
        error: 'Project ID and scenes are required'
      });
    }

    // Create job
    const jobId = uuidv4();
    const job = {
      id: jobId,
      projectId: projectId,
      status: 'queued',
      progress: 0,
      currentStep: 'Initializing',
      scenes: scenes,
      characterId: characterId,
      voiceId: voiceId,
      settings: settings,
      createdAt: new Date().toISOString(),
      startedAt: null,
      completedAt: null,
      result: null,
      error: null
    };

    jobs.set(jobId, job);

    // Start processing (in background)
    processGeneration(jobId, req.io).catch(error => {
      console.error('Generation error:', error);
      job.status = 'failed';
      job.error = error.message;
      jobs.set(jobId, job);
    });

    res.json({
      success: true,
      data: {
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

// GET generation progress
exports.getProgress = async (req, res) => {
  try {
    const { jobId } = req.params;

    const job = jobs.get(jobId);
    if (!job) {
      return res.status(404).json({
        success: false,
        error: 'Job not found'
      });
    }

    res.json({
      success: true,
      data: {
        jobId: job.id,
        status: job.status,
        progress: job.progress,
        currentStep: job.currentStep,
        result: job.result,
        error: job.error
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST cancel generation
exports.cancelGeneration = async (req, res) => {
  try {
    const { jobId } = req.params;

    const job = jobs.get(jobId);
    if (!job) {
      return res.status(404).json({
        success: false,
        error: 'Job not found'
      });
    }

    if (job.status === 'completed' || job.status === 'failed') {
      return res.status(400).json({
        success: false,
        error: 'Job already finished'
      });
    }

    job.status = 'cancelled';
    jobs.set(jobId, job);

    res.json({
      success: true,
      message: 'Job cancelled successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// GET download video
exports.downloadVideo = async (req, res) => {
  try {
    const { jobId } = req.params;

    const job = jobs.get(jobId);
    if (!job) {
      return res.status(404).json({
        success: false,
        error: 'Job not found'
      });
    }

    if (job.status !== 'completed' || !job.result) {
      return res.status(400).json({
        success: false,
        error: 'Video not ready yet'
      });
    }

    const videoPath = job.result.videoPath;
    res.download(videoPath);
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST generate single scene
exports.generateScene = async (req, res) => {
  try {
    const { 
      sceneId,
      script,
      characterId,
      imagePrompt,
      duration 
    } = req.body;

    // TODO: Implement scene generation
    // 1. Generate character image
    // 2. Animate with Minimax
    // 3. Return video URL

    res.json({
      success: true,
      data: {
        sceneId: sceneId,
        videoUrl: '/placeholder.mp4',
        duration: duration
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST generate character image
exports.generateCharacterImage = async (req, res) => {
  try {
    const { characterId, action, prompt } = req.body;

    // TODO: Call Nano Banana Pro API
    res.json({
      success: true,
      data: {
        imageUrl: '/placeholder.jpg'
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST generate voice
exports.generateVoice = async (req, res) => {
  try {
    const { text, voiceId } = req.body;

    // TODO: Call TTS API
    res.json({
      success: true,
      data: {
        audioUrl: '/placeholder.mp3',
        duration: 5
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST generate background music
exports.generateBGM = async (req, res) => {
  try {
    const { prompt, duration } = req.body;

    // TODO: Call ElevenLabs Music API
    res.json({
      success: true,
      data: {
        audioUrl: '/placeholder-bgm.mp3',
        duration: duration
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// POST render final video
exports.renderVideo = async (req, res) => {
  try {
    const { scenes, voiceFiles, bgmFile, settings } = req.body;

    // TODO: Call FFmpeg service
    res.json({
      success: true,
      data: {
        videoUrl: '/final-video.mp4',
        duration: 30
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

// Background processing function
async function processGeneration(jobId, io) {
  const job = jobs.get(jobId);
  if (!job) return;

  try {
    job.status = 'processing';
    job.startedAt = new Date().toISOString();
    jobs.set(jobId, job);

    // Emit initial status
    if (io) {
      io.emit('generation:progress', {
        jobId: jobId,
        status: 'processing',
        progress: 0,
        currentStep: 'Starting generation'
      });
    }

    // Step 1: Generate character images (10%)
    await updateProgress(jobId, 10, 'Generating character images', io);
    // TODO: Generate images

    // Step 2: Generate videos (50%)
    await updateProgress(jobId, 30, 'Generating scene videos', io);
    // TODO: Generate videos

    // Step 3: Generate voices (70%)
    await updateProgress(jobId, 70, 'Generating voice narration', io);
    // TODO: Generate voices

    // Step 4: Generate BGM (80%)
    await updateProgress(jobId, 80, 'Generating background music', io);
    // TODO: Generate BGM

    // Step 5: Render final video (100%)
    await updateProgress(jobId, 90, 'Rendering final video', io);
    // TODO: Render

    // Complete
    job.status = 'completed';
    job.progress = 100;
    job.currentStep = 'Completed';
    job.completedAt = new Date().toISOString();
    job.result = {
      videoPath: '/path/to/video.mp4',
      videoUrl: '/generated/videos/final.mp4',
      duration: 30,
      metadata: {}
    };
    jobs.set(jobId, job);

    if (io) {
      io.emit('generation:completed', {
        jobId: jobId,
        result: job.result
      });
    }

  } catch (error) {
    job.status = 'failed';
    job.error = error.message;
    jobs.set(jobId, job);

    if (io) {
      io.emit('generation:failed', {
        jobId: jobId,
        error: error.message
      });
    }
  }
}

async function updateProgress(jobId, progress, step, io) {
  const job = jobs.get(jobId);
  if (!job) return;

  job.progress = progress;
  job.currentStep = step;
  jobs.set(jobId, job);

  if (io) {
    io.emit('generation:progress', {
      jobId: jobId,
      progress: progress,
      currentStep: step
    });
  }

  // Simulate processing time
  await new Promise(resolve => setTimeout(resolve, 1000));
}

module.exports = exports;
