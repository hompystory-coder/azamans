/**
 * NeuralGrid Home Button - Universal JavaScript Component
 * 
 * 모든 서브사이트에 추가하여 메인페이지로 쉽게 돌아갈 수 있습니다.
 * 
 * 사용법:
 * HTML 파일의 </body> 태그 직전에 추가:
 * <script src="/neuralgrid-home-button.js"></script>
 * 
 * 또는 CDN/서버 경로:
 * <script src="https://neuralgrid.kr/assets/neuralgrid-home-button.js"></script>
 */

(function() {
    'use strict';
    
    // 스타일 추가
    const style = document.createElement('style');
    style.textContent = `
        .neuralgrid-home-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            border: none;
            cursor: pointer;
            backdrop-filter: blur(10px);
        }

        .neuralgrid-home-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
        }

        .neuralgrid-home-btn:active {
            transform: translateY(-1px) scale(1.02);
        }

        .neuralgrid-home-icon {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .neuralgrid-home-text {
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        @media (max-width: 768px) {
            .neuralgrid-home-btn {
                top: 10px;
                left: 10px;
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }
            .neuralgrid-home-icon {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .neuralgrid-home-text {
                display: none;
            }
            .neuralgrid-home-btn {
                padding: 0.75rem;
                border-radius: 50%;
                width: 48px;
                height: 48px;
                justify-content: center;
            }
            .neuralgrid-home-icon {
                font-size: 1.3rem;
            }
        }

        @keyframes neuralgrid-pulse {
            0%, 100% {
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            }
            50% {
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.6);
            }
        }

        .neuralgrid-home-btn.pulse {
            animation: neuralgrid-pulse 2s ease-in-out infinite;
        }
    `;
    document.head.appendChild(style);

    // DOM 로드 대기
    function addHomeButton() {
        // 이미 버튼이 존재하면 추가하지 않음
        if (document.querySelector('.neuralgrid-home-btn')) {
            return;
        }

        // 버튼 생성
        const homeButton = document.createElement('a');
        homeButton.href = 'https://neuralgrid.kr';
        homeButton.className = 'neuralgrid-home-btn';
        homeButton.title = 'NeuralGrid 메인으로 돌아가기';
        homeButton.setAttribute('aria-label', 'NeuralGrid 메인으로 돌아가기');

        // 아이콘
        const icon = document.createElement('span');
        icon.className = 'neuralgrid-home-icon';
        icon.textContent = '🏠';

        // 텍스트
        const text = document.createElement('span');
        text.className = 'neuralgrid-home-text';
        text.textContent = 'NeuralGrid 홈';

        homeButton.appendChild(icon);
        homeButton.appendChild(text);

        // body에 추가
        document.body.appendChild(homeButton);
    }

    // DOM이 로드되면 버튼 추가
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addHomeButton);
    } else {
        addHomeButton();
    }

    // SPA (Single Page Application) 지원
    // React Router, Next.js 등에서 페이지 전환 시에도 버튼 유지
    window.addEventListener('popstate', function() {
        setTimeout(addHomeButton, 100);
    });

})();
