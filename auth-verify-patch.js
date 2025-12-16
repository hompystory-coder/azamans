// auth.js에 추가할 내용
/**
 * @swagger
 * /api/auth/verify:
 *   post:
 *     tags:
 *       - Auth
 *     summary: 토큰 검증
 *     description: JWT 토큰의 유효성을 검증하고 사용자 정보를 반환합니다
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - token
 *             properties:
 *               token:
 *                 type: string
 *                 example: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
 *     responses:
 *       200:
 *         description: 토큰 검증 성공
 *       401:
 *         description: 유효하지 않은 토큰
 */
router.post('/verify', authController.verifyToken);
