// Universal SSO Middleware for all NeuralGrid services
// Add this script to any service to enable SSO authentication

(function() {
    // Check authentication on page load
    function checkAuth() {
        const token = localStorage.getItem('neuralgrid_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        
        // Update UI based on auth status
        const authButton = document.getElementById('neural-auth-btn');
        if (authButton) {
            if (token && user.name) {
                authButton.innerHTML = `${user.name}님 | <span onclick="neuralLogout()" style="cursor:pointer">로그아웃</span>`;
                authButton.onclick = () => window.location.href = 'https://auth.neuralgrid.kr/dashboard';
            } else {
                authButton.textContent = '로그인';
                authButton.onclick = () => window.location.href = 'https://auth.neuralgrid.kr';
            }
        }
    }

    // Logout function
    window.neuralLogout = function() {
        if (confirm('로그아웃 하시겠습니까?')) {
            localStorage.removeItem('neuralgrid_token');
            localStorage.removeItem('user');
            window.location.reload();
        }
    };

    // Verify token with auth service
    async function verifyToken() {
        const token = localStorage.getItem('neuralgrid_token');
        if (!token) return false;

        try {
            const response = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return response.ok;
        } catch (error) {
            console.error('Token verification failed:', error);
            return false;
        }
    }

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkAuth);
    } else {
        checkAuth();
    }

    // Export for use in other scripts
    window.NeuralGridSSO = {
        checkAuth,
        verifyToken,
        isAuthenticated: () => !!localStorage.getItem('neuralgrid_token'),
        getUser: () => JSON.parse(localStorage.getItem('user') || '{}'),
        getToken: () => localStorage.getItem('neuralgrid_token')
    };
})();
