const fs = require('fs').promises;
const path = require('path');

// 프로젝트 데이터 저장 경로
const PROJECTS_DIR = path.join(__dirname, '../data/projects');
const PROJECTS_FILE = path.join(PROJECTS_DIR, 'projects.json');

// 디렉토리 생성 확인
async function ensureProjectsDir() {
  try {
    await fs.access(PROJECTS_DIR);
  } catch {
    await fs.mkdir(PROJECTS_DIR, { recursive: true });
  }
}

// 프로젝트 목록 로드
async function loadProjects() {
  try {
    await ensureProjectsDir();
    const data = await fs.readFile(PROJECTS_FILE, 'utf8');
    return JSON.parse(data);
  } catch (error) {
    // 파일이 없으면 빈 배열 반환
    return [];
  }
}

// 프로젝트 목록 저장
async function saveProjects(projects) {
  await ensureProjectsDir();
  await fs.writeFile(PROJECTS_FILE, JSON.stringify(projects, null, 2), 'utf8');
}

// 모든 프로젝트 조회
exports.getAllProjects = async (req, res) => {
  try {
    const projects = await loadProjects();
    res.json({
      success: true,
      data: projects
    });
  } catch (error) {
    console.error('프로젝트 조회 오류:', error);
    res.status(500).json({
      success: false,
      error: '프로젝트 목록을 불러오는데 실패했습니다.'
    });
  }
};

// 특정 프로젝트 조회
exports.getProject = async (req, res) => {
  try {
    const { id } = req.params;
    const projects = await loadProjects();
    const project = projects.find(p => p.id === id);

    if (!project) {
      return res.status(404).json({
        success: false,
        error: '프로젝트를 찾을 수 없습니다.'
      });
    }

    res.json({
      success: true,
      data: project
    });
  } catch (error) {
    console.error('프로젝트 조회 오류:', error);
    res.status(500).json({
      success: false,
      error: '프로젝트를 불러오는데 실패했습니다.'
    });
  }
};

// 프로젝트 생성
exports.createProject = async (req, res) => {
  try {
    const {
      title,
      description,
      sourceUrl,
      category,
      status = 'draft'
    } = req.body;

    // 유효성 검사
    if (!title) {
      return res.status(400).json({
        success: false,
        error: '프로젝트 제목은 필수입니다.'
      });
    }

    const projects = await loadProjects();

    // 새 프로젝트 생성
    const newProject = {
      id: `project_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      title,
      description: description || '',
      sourceUrl: sourceUrl || '',
      category: category || '기타',
      status,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      scenes: [],
      settings: {
        duration: 30,
        aspectRatio: '9:16',
        voiceType: 'default',
        bgmType: 'default'
      }
    };

    projects.push(newProject);
    await saveProjects(projects);

    res.status(201).json({
      success: true,
      data: newProject
    });
  } catch (error) {
    console.error('프로젝트 생성 오류:', error);
    res.status(500).json({
      success: false,
      error: '프로젝트 생성에 실패했습니다.'
    });
  }
};

// 프로젝트 수정
exports.updateProject = async (req, res) => {
  try {
    const { id } = req.params;
    const updates = req.body;

    const projects = await loadProjects();
    const projectIndex = projects.findIndex(p => p.id === id);

    if (projectIndex === -1) {
      return res.status(404).json({
        success: false,
        error: '프로젝트를 찾을 수 없습니다.'
      });
    }

    // 프로젝트 업데이트
    projects[projectIndex] = {
      ...projects[projectIndex],
      ...updates,
      id, // ID는 변경 불가
      updatedAt: new Date().toISOString()
    };

    await saveProjects(projects);

    res.json({
      success: true,
      data: projects[projectIndex]
    });
  } catch (error) {
    console.error('프로젝트 수정 오류:', error);
    res.status(500).json({
      success: false,
      error: '프로젝트 수정에 실패했습니다.'
    });
  }
};

// 프로젝트 삭제
exports.deleteProject = async (req, res) => {
  try {
    const { id } = req.params;
    const projects = await loadProjects();
    const projectIndex = projects.findIndex(p => p.id === id);

    if (projectIndex === -1) {
      return res.status(404).json({
        success: false,
        error: '프로젝트를 찾을 수 없습니다.'
      });
    }

    // 프로젝트 삭제
    const deletedProject = projects.splice(projectIndex, 1)[0];
    await saveProjects(projects);

    res.json({
      success: true,
      data: deletedProject,
      message: '프로젝트가 삭제되었습니다.'
    });
  } catch (error) {
    console.error('프로젝트 삭제 오류:', error);
    res.status(500).json({
      success: false,
      error: '프로젝트 삭제에 실패했습니다.'
    });
  }
};

// 프로젝트 복제
exports.duplicateProject = async (req, res) => {
  try {
    const { id } = req.params;
    const projects = await loadProjects();
    const project = projects.find(p => p.id === id);

    if (!project) {
      return res.status(404).json({
        success: false,
        error: '프로젝트를 찾을 수 없습니다.'
      });
    }

    // 프로젝트 복제
    const duplicatedProject = {
      ...project,
      id: `project_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      title: `${project.title} (복사본)`,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      status: 'draft'
    };

    projects.push(duplicatedProject);
    await saveProjects(projects);

    res.status(201).json({
      success: true,
      data: duplicatedProject
    });
  } catch (error) {
    console.error('프로젝트 복제 오류:', error);
    res.status(500).json({
      success: false,
      error: '프로젝트 복제에 실패했습니다.'
    });
  }
};

// 프로젝트 씬 업데이트
exports.updateScenes = async (req, res) => {
  try {
    const { id } = req.params;
    const { scenes } = req.body;

    if (!Array.isArray(scenes)) {
      return res.status(400).json({
        success: false,
        error: 'scenes는 배열이어야 합니다.'
      });
    }

    const projects = await loadProjects();
    const projectIndex = projects.findIndex(p => p.id === id);

    if (projectIndex === -1) {
      return res.status(404).json({
        success: false,
        error: '프로젝트를 찾을 수 없습니다.'
      });
    }

    // 씬 업데이트
    projects[projectIndex].scenes = scenes;
    projects[projectIndex].updatedAt = new Date().toISOString();

    await saveProjects(projects);

    res.json({
      success: true,
      data: projects[projectIndex]
    });
  } catch (error) {
    console.error('씬 업데이트 오류:', error);
    res.status(500).json({
      success: false,
      error: '씬 업데이트에 실패했습니다.'
    });
  }
};

// 프로젝트 설정 업데이트
exports.updateSettings = async (req, res) => {
  try {
    const { id } = req.params;
    const { settings } = req.body;

    if (!settings || typeof settings !== 'object') {
      return res.status(400).json({
        success: false,
        error: 'settings는 객체여야 합니다.'
      });
    }

    const projects = await loadProjects();
    const projectIndex = projects.findIndex(p => p.id === id);

    if (projectIndex === -1) {
      return res.status(404).json({
        success: false,
        error: '프로젝트를 찾을 수 없습니다.'
      });
    }

    // 설정 업데이트
    projects[projectIndex].settings = {
      ...projects[projectIndex].settings,
      ...settings
    };
    projects[projectIndex].updatedAt = new Date().toISOString();

    await saveProjects(projects);

    res.json({
      success: true,
      data: projects[projectIndex]
    });
  } catch (error) {
    console.error('설정 업데이트 오류:', error);
    res.status(500).json({
      success: false,
      error: '설정 업데이트에 실패했습니다.'
    });
  }
};
