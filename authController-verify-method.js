// authController.js에 추가할 메서드

// JWT 토큰 검증
exports.verifyToken = async (req, res) => {
    try {
        const { token } = req.body;
        
        if (!token) {
            return res.status(400).json({ 
                success: false,
                error: 'Token is required' 
            });
        }

        // JWT 검증
        const jwt = require('jsonwebtoken');
        const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production';
        
        try {
            const decoded = jwt.verify(token, JWT_SECRET);
            
            // 사용자 정보 반환 (비밀번호 제외)
            return res.json({
                success: true,
                user: {
                    id: decoded.userId || decoded.id,
                    username: decoded.username,
                    email: decoded.email,
                    full_name: decoded.full_name,
                    role: decoded.role || 'user'
                }
            });
        } catch (jwtError) {
            return res.status(401).json({
                success: false,
                error: 'Invalid or expired token'
            });
        }
    } catch (error) {
        console.error('Token verification error:', error);
        return res.status(500).json({
            success: false,
            error: 'Token verification failed'
        });
    }
};
