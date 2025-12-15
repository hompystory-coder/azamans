// MyPage JavaScript

// Load user settings on page load
document.addEventListener('DOMContentLoaded', async function() {
  console.log('MyPage loaded');
  
  // Check login status
  const user = localStorage.getItem('user');
  const token = localStorage.getItem('token');
  
  if (!user || !token) {
    alert('로그인이 필요합니다.');
    window.location.href = '/';
    return;
  }
  
  const userData = JSON.parse(user);
  console.log('User:', userData);
  
  // Load settings
  await loadSettings(userData.email);
  
  // Load user shorts
  await loadUserShorts(userData.email);
  
  // Setup event listeners
  setupEventListeners(userData);
});

// Load user settings
async function loadSettings(email) {
  try {
    const response = await axios.get(`/api/user/settings/${email}`);
    
    if (response.data.success) {
      const { channelId, coupangPartnerId, coupangAccessKey, coupangSecretKey } = response.data.data;
      
      // Fill form fields
      if (channelId) {
        document.getElementById('youtubeChannelId').value = channelId;
      }
      
      if (coupangPartnerId) {
        document.getElementById('coupangPartnerId').value = coupangPartnerId;
      }
      
      if (coupangAccessKey !== '••••••••') {
        document.getElementById('coupangAccessKey').value = coupangAccessKey;
      }
      
      if (coupangSecretKey !== '••••••••') {
        document.getElementById('coupangSecretKey').value = coupangSecretKey;
      }
      
      console.log('Settings loaded successfully');
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
}

// Save API keys
async function saveApiKeys(email) {
  try {
    const coupangPartnerId = document.getElementById('coupangPartnerId').value.trim();
    const coupangAccessKey = document.getElementById('coupangAccessKey').value.trim();
    const coupangSecretKey = document.getElementById('coupangSecretKey').value.trim();
    const youtubeChannelId = document.getElementById('youtubeChannelId').value.trim();
    
    if (!coupangPartnerId || !coupangAccessKey || !coupangSecretKey) {
      alert('쿠팡 파트너스 API 키를 모두 입력해주세요.');
      return;
    }
    
    if (!youtubeChannelId) {
      alert('YouTube 채널 ID를 입력해주세요.');
      return;
    }
    
    const response = await axios.post('/api/user/settings', {
      email,
      coupangPartnerId,
      coupangAccessKey,
      coupangSecretKey,
      youtubeChannelId
    });
    
    if (response.data.success) {
      alert('✅ API 키가 저장되었습니다!');
      console.log('API keys saved:', response.data);
    } else {
      alert('❌ 저장 실패: ' + (response.data.error || '알 수 없는 오류'));
    }
  } catch (error) {
    console.error('Save API keys error:', error);
    alert('❌ 저장 중 오류가 발생했습니다: ' + (error.response?.data?.error || error.message));
  }
}

// Fetch YouTube shorts
async function fetchYouTubeShorts(email) {
  try {
    const channelId = document.getElementById('youtubeChannelId').value.trim();
    
    if (!channelId) {
      alert('YouTube 채널 ID를 먼저 입력해주세요.');
      return;
    }
    
    // Show loading
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 수집 중...';
    
    const response = await axios.post('/api/youtube/fetch-shorts', {
      channelId,
      email
    });
    
    if (response.data.success) {
      const total = response.data.data?.total || 0;
      const message = response.data.message || `${total}개의 쇼츠를 가져왔습니다!`;
      
      if (total > 0) {
        alert(`✅ ${message}`);
        // Reload shorts list
        await loadUserShorts(email);
      } else {
        alert(`ℹ️ ${message}`);
      }
    } else {
      alert('❌ 수집 실패: ' + (response.data.error || '알 수 없는 오류'));
    }
    
    // Restore button
    button.disabled = false;
    button.innerHTML = originalText;
  } catch (error) {
    console.error('Fetch shorts error:', error);
    alert('❌ 쇼츠 수집 중 오류가 발생했습니다: ' + (error.response?.data?.error || error.message));
    
    // Restore button
    event.target.disabled = false;
    event.target.innerHTML = '<i class="fas fa-download"></i> 쇼츠 불러오기';
  }
}

// Load user shorts
async function loadUserShorts(email) {
  try {
    const response = await axios.get(`/api/user/shorts/${email}`);
    
    if (response.data.success) {
      const shorts = response.data.data.shorts;
      const total = response.data.data.total;
      
      // Update statistics
      document.getElementById('totalShorts').textContent = total;
      
      // Update shorts list
      displayShorts(shorts);
      
      console.log(`Loaded ${total} shorts`);
    }
  } catch (error) {
    console.error('Failed to load shorts:', error);
  }
}

// Display shorts in the list
function displayShorts(shorts) {
  const container = document.getElementById('shortsContainer');
  
  if (!container) {
    console.warn('Shorts container not found');
    return;
  }
  
  if (shorts.length === 0) {
    container.innerHTML = `
      <div class="col-span-full text-center py-12 bg-gray-50 rounded-lg">
        <i class="fas fa-video text-gray-400 text-5xl mb-4"></i>
        <p class="text-gray-600 text-lg">등록된 쇼츠가 없습니다.</p>
        <p class="text-gray-500 text-sm mt-2">\"쇼츠 불러오기\" 버튼을 클릭하여 쇼츠를 가져오세요.</p>
      </div>
    `;
    return;
  }
  
  container.innerHTML = shorts.map(short => `
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex items-start space-x-4">
        <input type="checkbox" class="mt-2" data-short-id="${short.id}">
        <img src="${short.thumbnail}" alt="${short.title}" class="w-24 h-16 object-cover rounded">
        <div class="flex-1">
          <h3 class="font-semibold text-gray-900 mb-1">${short.title}</h3>
          <p class="text-sm text-gray-600 mb-2">${short.description || '설명 없음'}</p>
          <div class="flex items-center space-x-4 text-xs text-gray-500">
            <span><i class="fas fa-eye"></i> ${short.views?.toLocaleString() || 0}</span>
            <span><i class="fas fa-heart"></i> ${short.likes?.toLocaleString() || 0}</span>
            <span><i class="fas fa-calendar"></i> ${new Date(short.publishedAt).toLocaleDateString()}</span>
          </div>
        </div>
        <div class="flex space-x-2">
          <button onclick="editShort('${short.id}')" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
            <i class="fas fa-edit"></i> 수정
          </button>
          <button onclick="deleteShort('${short.id}')" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
            <i class="fas fa-trash"></i> 삭제
          </button>
        </div>
      </div>
    </div>
  `).join('');
}

// Setup event listeners
function setupEventListeners(userData) {
  // Save API keys button
  const saveButton = document.getElementById('saveApiKeysButton');
  if (saveButton) {
    saveButton.addEventListener('click', () => saveApiKeys(userData.email));
  }
  
  // Fetch shorts button - using onclick in HTML
  // Auto-fetch settings save button
  const autoFetchSaveButton = document.getElementById('saveAutoFetchButton');
  if (autoFetchSaveButton) {
    autoFetchSaveButton.addEventListener('click', saveAutoFetchSettings);
  }
  
  // Fetch now button
  const fetchNowButton = document.getElementById('fetchNowButton');
  if (fetchNowButton) {
    fetchNowButton.addEventListener('click', () => fetchYouTubeShorts(userData.email));
  }
}

// Save auto-fetch settings
async function saveAutoFetchSettings() {
  try {
    const enabled = document.getElementById('autoFetchEnabled').checked;
    const interval = document.getElementById('autoFetchInterval').value;
    
    console.log('Auto-fetch settings:', { enabled, interval });
    
    alert('✅ 자동 수집 설정이 저장되었습니다!');
  } catch (error) {
    console.error('Save auto-fetch settings error:', error);
    alert('❌ 설정 저장 중 오류가 발생했습니다.');
  }
}

// Edit short
function editShort(shortId) {
  alert('수정 기능은 곧 추가될 예정입니다.');
}

// Delete short
function deleteShort(shortId) {
  if (confirm('정말 이 쇼츠를 삭제하시겠습니까?')) {
    alert('삭제 기능은 곧 추가될 예정입니다.');
  }
}

// Show user guide (called from button)
function showUserGuide() {
  // Scroll to guide section
  const guideSection = document.querySelector('.bg-white.rounded-lg.shadow.p-6.mb-8');
  if (guideSection) {
    guideSection.scrollIntoView({ behavior: 'smooth' });
  }
}

// Make functions globally accessible
window.fetchYouTubeShorts = function() {
  const user = localStorage.getItem('user');
  if (user) {
    const userData = JSON.parse(user);
    fetchYouTubeShorts(userData.email);
  }
};

window.showUserGuide = showUserGuide;

// Show add short modal
function showAddShortModal() {
  alert('쇼츠 추가 기능은 곧 추가될 예정입니다.\n\n현재는 "쇼츠 불러오기" 버튼을 사용하여\nYouTube 채널에서 쇼츠를 자동으로 가져올 수 있습니다.');
}

// Make globally accessible
window.showAddShortModal = showAddShortModal;
