// 메인 페이지 JavaScript

// Axios 글로벌 인터셉터 - 모든 에러를 조용히 처리
axios.interceptors.response.use(
  response => response,
  error => {
    // 모든 에러를 조용히 처리하고 rejected promise 반환
    return Promise.reject(error);
  }
);

// URL을 클릭 가능한 링크로 변환하는 함수
function convertUrlsToLinks(text) {
  if (!text) return '';
  
  // URL 패턴: http, https로 시작하는 링크
  const urlPattern = /(https?:\/\/[^\s]+)/g;
  
  return text.replace(urlPattern, (url) => {
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700 underline">${url}</a>`;
  });
}

// 로그인 상태 체크 및 헤더 업데이트
function checkLoginStatus() {
  const user = localStorage.getItem('user');
  const token = localStorage.getItem('token');
  
  if (user && token) {
    try {
      const userData = JSON.parse(user);
      
      // 로그인 버튼 숨기기
      document.getElementById('loginButton').style.display = 'none';
      
      // 사용자 정보 섹션 표시
      const userSection = document.getElementById('userSection');
      userSection.style.display = 'flex';
      
      // 사용자 정보 설정
      document.getElementById('userName').textContent = userData.name;
      const roleText = userData.role === 'admin' ? '관리자' : userData.role === 'creator' ? '크리에이터' : '사용자';
      document.getElementById('userRole').textContent = `(${roleText})`;
      
      // 관리자인 경우 관리자 버튼 표시
      const adminButton = document.getElementById('adminButton');
      if (adminButton) {
        if (userData.role === 'admin') {
          adminButton.style.display = 'inline-block';
        } else {
          adminButton.style.display = 'none';
        }
      }
    } catch (error) {
      // 파싱 오류 시 로그아웃 처리
      logout();
    }
  } else {
    // 로그인 버튼 표시
    document.getElementById('loginButton').style.display = 'block';
    document.getElementById('userSection').style.display = 'none';
    
    // 관리자 버튼 숨기기
    const adminButton = document.getElementById('adminButton');
    if (adminButton) {
      adminButton.style.display = 'none';
    }
  }
}

// 로그아웃
function logout() {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  alert('로그아웃되었습니다.');
  location.reload();
}

// 로그인 모달 함수
function showLoginModal() {
  document.getElementById('loginModal').classList.remove('hidden');
}

function closeLoginModal() {
  document.getElementById('loginModal').classList.add('hidden');
}

// 로그인 폼 제출
document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const email = formData.get('email');
  const password = formData.get('password');
  
  try {
    const response = await axios.post('/api/auth/login', { email, password });
    
    if (response.data.success) {
      // 토큰 저장 (API 응답 구조: {success, data: {user, token}})
      localStorage.setItem('token', response.data.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.data.user));
      
      alert('로그인 성공!');
      closeLoginModal();
      
      // 헤더 업데이트 후 페이지 새로고침
      checkLoginStatus();
      location.reload();
    }
  } catch (error) {
    console.error('Login error:', error);
    alert(error.response?.data?.error || '로그인에 실패했습니다.');
  }
});

// 페이지 로드 시 로그인 상태 체크
document.addEventListener('DOMContentLoaded', function() {
  checkLoginStatus();
});

// 전역 필터 상태
let currentFilters = {
  category: 'all',
  search: '',
  sortBy: 'newest'
};

// 쇼츠 목록 로드 (필터링 지원)
async function loadShorts(filters = {}) {
  try {
    // 필터 업데이트
    currentFilters = { ...currentFilters, ...filters };
    
    // 쿼리 파라미터 구성
    const params = new URLSearchParams();
    if (currentFilters.category && currentFilters.category !== 'all') {
      params.append('category', currentFilters.category);
    }
    if (currentFilters.search) {
      params.append('search', currentFilters.search);
    }
    if (currentFilters.sortBy) {
      params.append('sortBy', currentFilters.sortBy);
    }
    
    const response = await axios.get(`/api/shorts?${params.toString()}`, {
      validateStatus: function (status) {
        return status < 600; // 모든 에러를 정상 응답으로 처리
      }
    });
    
    if (response.status >= 400) {
      // 에러 응답은 조용히 처리
      return;
    }
    
    if (response.data.success) {
      const shorts = response.data.data.shorts || response.data.data;
      const container = document.getElementById('shorts-container');
      
      if (shorts.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center col-span-full">검색 결과가 없습니다.</p>';
        return;
      }
      
      container.innerHTML = shorts.map(short => `
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition cursor-pointer" 
             onclick="viewShort(${short.id})">
          <div class="relative aspect-[9/16] bg-gray-900">
            <img src="${short.thumbnail_url || 'https://via.placeholder.com/360x640?text=Shorts'}" 
                 alt="${short.title}"
                 onerror="this.src='https://via.placeholder.com/360x640?text=No+Image'"
                 class="w-full h-full object-cover">
            <div class="absolute top-2 right-2 bg-black bg-opacity-70 text-white px-2 py-1 rounded text-xs">
              <i class="fas fa-eye"></i> ${short.view_count || 0}
            </div>
            ${short.affiliate_platform === 'naver' ? `
            <div class="absolute top-2 left-2 bg-green-600 text-white px-2 py-1 rounded text-xs font-bold">
              NAVER
            </div>
            ` : short.affiliate_platform === 'coupang' ? `
            <div class="absolute top-2 left-2 bg-orange-600 text-white px-2 py-1 rounded text-xs font-bold">
              COUPANG
            </div>
            ` : short.affiliate_platform === 'both' ? `
            <div class="absolute top-2 left-2 bg-blue-600 text-white px-2 py-1 rounded text-xs font-bold">
              BOTH
            </div>
            ` : ''}
          </div>
          <div class="p-4">
            <h3 class="font-bold text-gray-900 mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${short.title}</h3>
            <div class="text-sm text-gray-600 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${convertUrlsToLinks(short.description || '')}</div>
            
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white text-xs">
                  ${short.creator_name?.charAt(0) || 'C'}
                </div>
                <span class="text-sm text-gray-700">${short.creator_name}</span>
              </div>
            </div>
            
            <div class="flex items-center text-sm text-gray-500">
              <span><i class="fas fa-eye"></i> ${(short.view_count || 0).toLocaleString()}</span>
            </div>
            
            <button onclick="buyProduct(${short.id}); event.stopPropagation();" 
                    class="w-full mt-4 ${short.affiliate_platform === 'naver' ? 'bg-green-600 hover:bg-green-700' : 'bg-orange-500 hover:bg-orange-600'} text-white py-2 rounded-lg font-medium">
              <i class="fas fa-shopping-cart"></i> ${short.affiliate_platform === 'naver' ? '네이버에서 구매' : '쿠팡에서 구매'}
            </button>
          </div>
        </div>
      `).join('');
    }
  } catch (error) {
    // 에러를 조용히 처리 (콘솔에 표시하지 않음)
  } finally {
    const loading = document.getElementById('loading');
    if (loading) {
      loading.classList.add('hidden');
    }
  }
}

// 쇼츠 상세 보기
function viewShort(shortId) {
  window.location.href = `/short/${shortId}`;
}

// 상품 구매하기 (쿠팡 링크로 이동)
async function buyProduct(shortId) {
  try {
    // 클릭 로그 기록
    const response = await axios.post(`/api/shorts/${shortId}/click`, {}, {
      validateStatus: function (status) {
        return status < 600;
      }
    });
    
    if (response.status >= 400) {
      alert('구매하기를 처리할 수 없습니다.');
      return;
    }
    
    if (response.data.success && response.data.data.deeplink) {
      // 쿠팡 상품 페이지로 이동
      window.open(response.data.data.deeplink, '_blank');
    }
  } catch (error) {
    // 에러를 조용히 처리
    alert('구매하기를 처리할 수 없습니다.');
  }
}

// 크리에이터 목록 로드
async function loadCreators() {
  try {
    const response = await axios.get('/api/admin/creators', {
      validateStatus: function (status) {
        return status < 600;
      }
    });
    
    if (response.status >= 400) {
      return;
    }
    
    if (response.data.success) {
      const creators = response.data.data.slice(0, 4); // 상위 4명만
      const container = document.getElementById('creators-container');
      
      container.innerHTML = creators.map(creator => `
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition">
          <div class="flex items-center space-x-3 mb-3">
            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center text-white text-lg font-bold">
              ${creator.user_name?.charAt(0) || 'C'}
            </div>
            <div>
              <h3 class="font-bold text-gray-900">${creator.user_name}</h3>
              <p class="text-xs text-gray-500">${creator.youtube_channel_name || 'YouTube Channel'}</p>
            </div>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">
              <i class="fas fa-video"></i> ${creator.approved_shorts || 0}개
            </span>
            <span class="text-green-600 font-medium">
              ₩${(creator.total_earnings || 0).toLocaleString()}
            </span>
          </div>
        </div>
      `).join('');
    }
  } catch (error) {
    // 에러를 조용히 처리
  }
}

// 카테고리 목록 로드
async function loadCategories() {
  try {
    const response = await axios.get('/api/shorts/categories/list', {
      validateStatus: function (status) {
        return status < 600;
      }
    });
    
    if (response.status >= 400) {
      return;
    }
    
    if (response.data.success) {
      const categories = response.data.data;
      const select = document.querySelector('select[onchange="filterByCategory(this.value)"]');
      
      if (select) {
        select.innerHTML = `
          <option value="all">전체</option>
          ${categories.map(cat => `
            <option value="${cat.id}">${cat.name} (${cat.count})</option>
          `).join('')}
        `;
      }
    }
  } catch (error) {
    // 에러를 조용히 처리
  }
}

// 카테고리 필터
function filterByCategory(category) {
  loadShorts({ category });
}

// 정렬 필터
function filterBySortBy(sortBy) {
  loadShorts({ sortBy });
}

// 검색
function searchShorts(keyword) {
  loadShorts({ search: keyword });
}

// 페이지 로드 시 실행
document.addEventListener('DOMContentLoaded', () => {
  loadShorts();
  loadCreators();
  loadCategories();
  
  // 검색 입력 이벤트
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        searchShorts(e.target.value);
      }, 500); // 500ms 딜레이
    });
  }
});
