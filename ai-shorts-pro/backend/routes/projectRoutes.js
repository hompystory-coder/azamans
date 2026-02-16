const express = require('express');
const router = express.Router();
const projectController = require('../controllers/projectController');

// 프로젝트 목록 조회
router.get('/', projectController.getAllProjects);

// 특정 프로젝트 조회
router.get('/:id', projectController.getProject);

// 프로젝트 생성
router.post('/', projectController.createProject);

// 프로젝트 수정
router.put('/:id', projectController.updateProject);

// 프로젝트 삭제
router.delete('/:id', projectController.deleteProject);

// 프로젝트 복제
router.post('/:id/duplicate', projectController.duplicateProject);

// 프로젝트 씬 업데이트
router.put('/:id/scenes', projectController.updateScenes);

// 프로젝트 설정 업데이트
router.put('/:id/settings', projectController.updateSettings);

module.exports = router;
