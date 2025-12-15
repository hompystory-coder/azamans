// 관리자 페이지 JavaScript

// URL을 클릭 가능한 링크로 변환하는 함수
function convertUrlsToLinks(text) {
  if (!text) return '';
  
  // URL 패턴: http, https로 시작하는 링크
  const urlPattern = /(https?:\/\/[^\s]+)/g;
  
  return text.replace(urlPattern, (url) => {
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700 underline">${url}</a>`;
  });
}

// 로그인 확인 및 권한 체크
const user = JSON.parse(localStorage.getItem('user') || '{}');
if (!user.id || user.role !== 'admin') {
  alert('관리자 권한이 필요합니다.');
  window.location.href = '/';
}

let currentTab = 'pending';

// 탭 표시
async function showTab(tab) {
  currentTab = tab;
  
  // 탭 버튼 스타일 변경
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('border-red-500', 'text-red-600');
    btn.classList.add('border-transparent', 'text-gray-500');
  });
  event.target.classList.remove('border-transparent', 'text-gray-500');
  event.target.classList.add('border-red-500', 'text-red-600');
  
  // 탭 컨텐츠 로드
  if (tab === 'creators') {
    await loadCreators();
  } else {
    await loadShorts(tab);
  }
}

// 쇼츠 목록 로드
async function loadShorts(status) {
  try {
    const response = await axios.get(`/api/admin/shorts/${status}`);
    
    if (response.data.success) {
      const shorts = response.data.data;
      const container = document.getElementById('tabContent');
      
      // 카운트 업데이트
      document.getElementById(`${status}Count`).textContent = shorts.length;
      
      if (shorts.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center">쇼츠가 없습니다.</p>';
        return;
      }
      
      // 일괄 작업 바 HTML
      const bulkActionsBar = `
        <div id="bulkActionsBar" class="bg-gray-100 rounded-lg p-4 mb-4 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <label class="flex items-center space-x-2 cursor-pointer">
              <input type="checkbox" 
                     id="selectAllCheckbox" 
                     data-checkbox-action="selectAll"
                     class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
              <span class="text-sm font-medium text-gray-700">전체 선택</span>
            </label>
            
            <span id="selectedCount" class="text-sm text-gray-600">0개 선택됨</span>
          </div>
          
          <div class="flex items-center space-x-2 bulk-action-buttons">
            ${status === 'pending' ? `
              <button 
                id="bulkApproveBtn"
                data-bulk-action="approve"
                disabled
                class="bulk-action-btn px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm opacity-50 cursor-not-allowed">
                <i class="fas fa-check"></i> 선택 항목 승인
              </button>
              <button 
                id="bulkRejectBtn"
                data-bulk-action="reject"
                disabled
                class="bulk-action-btn px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm opacity-50 cursor-not-allowed">
                <i class="fas fa-times"></i> 선택 항목 반려
              </button>
            ` : ''}
            <button 
              id="bulkPendingBtn"
              data-bulk-action="pending"
              disabled
              class="bulk-action-btn px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm opacity-50 cursor-not-allowed">
              <i class="fas fa-clock"></i> 대기로 변경
            </button>
            <button 
              id="bulkDeleteBtn"
              data-bulk-action="delete"
              disabled
              class="bulk-action-btn px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm opacity-50 cursor-not-allowed">
              <i class="fas fa-trash"></i> 선택 항목 삭제
            </button>
          </div>
        </div>
      `;
      
      container.innerHTML = bulkActionsBar + shorts.map(short => `
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
          <div class="flex items-start space-x-4">
            <div class="flex items-center pt-2">
              <input type="checkbox" 
                     class="short-checkbox w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500" 
                     data-short-id="${short.id}"
                     data-checkbox-action="toggleShort">
            </div>
            
            <img src="${short.thumbnail_url || 'https://via.placeholder.com/120x213?text=Shorts'}" 
                 alt="${short.title}"
                 class="w-32 rounded shadow-sm" 
                 style="aspect-ratio: 9/16; object-fit: cover; object-position: center;">
            
            <div class="flex-1">
              <div class="flex items-start justify-between mb-2">
                <div>
                  <h3 class="font-bold text-gray-900 text-lg">${short.title}</h3>
                  <p class="text-sm text-gray-600 mb-1">
                    <i class="fab fa-youtube text-red-500"></i> 
                    <a href="${short.youtube_video_url}" target="_blank" class="hover:text-red-500">
                      ${short.youtube_video_id}
                    </a>
                  </p>
                  <p class="text-sm text-gray-600">
                    <i class="fas fa-user"></i> ${short.creator_name} 
                    (${short.youtube_channel_name || 'YouTube Channel'})
                  </p>
                </div>
                ${getStatusBadge(short.status)}
              </div>
              
              <div class="text-sm text-gray-700 mb-3">${convertUrlsToLinks(short.description || '')}</div>
              
              <div class="bg-white rounded p-3 mb-3">
                <p class="text-sm font-medium text-gray-900 mb-1">
                  <i class="fas fa-shopping-cart text-orange-500"></i> 쿠팡 상품
                </p>
                <p class="text-sm text-gray-600">${short.coupang_product_name || '상품명 없음'}</p>
                <a href="${short.coupang_product_url}" target="_blank" 
                   class="text-xs text-blue-500 hover:text-blue-600">
                  상품 링크 확인 →
                </a>
              </div>
              
              <div class="flex items-center space-x-6 text-sm text-gray-500 mb-3">
                <span><i class="fas fa-eye"></i> ${short.view_count || 0}</span>
                <span><i class="fas fa-mouse-pointer"></i> ${short.click_count || 0} 클릭</span>
                <span><i class="fas fa-shopping-cart"></i> ${short.purchase_count || 0} 구매</span>
                <span class="text-green-600"><i class="fas fa-won-sign"></i> ${(short.earnings || 0).toLocaleString()}</span>
              </div>
              
              ${short.approval_note ? `
                <div class="bg-blue-50 border border-blue-200 rounded p-2 mb-3">
                  <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle"></i> ${short.approval_note}
                  </p>
                </div>
              ` : ''}
              
              <div class="flex space-x-2 action-buttons">
                ${status === 'pending' ? `
                  <button data-action="approve" data-short-id="${short.id}" 
                          class="action-btn px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                    <i class="fas fa-check"></i> 승인
                  </button>
                  <button data-action="reject" data-short-id="${short.id}" 
                          class="action-btn px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                    <i class="fas fa-times"></i> 반려
                  </button>
                ` : status === 'approved' ? `
                  <button data-action="pending" data-short-id="${short.id}" 
                          class="action-btn px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                    <i class="fas fa-clock"></i> 대기로 변경
                  </button>
                  <button data-action="reject" data-short-id="${short.id}" 
                          class="action-btn px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                    <i class="fas fa-times"></i> 반려
                  </button>
                ` : status === 'rejected' ? `
                  <button data-action="pending" data-short-id="${short.id}" 
                          class="action-btn px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                    <i class="fas fa-clock"></i> 대기로 변경
                  </button>
                  <button data-action="approve" data-short-id="${short.id}" 
                          class="action-btn px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                    <i class="fas fa-check"></i> 승인
                  </button>
                ` : ''}
                <button data-action="delete" data-short-id="${short.id}" 
                        class="action-btn px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                  <i class="fas fa-trash"></i> 삭제
                </button>
              </div>
            </div>
          </div>
        </div>
      `).join('');
      
      // 이벤트 위임: 개별 액션 버튼 클릭 처리
      container.addEventListener('click', (e) => {
        const button = e.target.closest('.action-btn');
        if (!button) return;
        
        const action = button.dataset.action;
        const shortId = parseInt(button.dataset.shortId);
        
        if (action === 'approve') {
          approveShort(shortId);
        } else if (action === 'reject') {
          rejectShort(shortId);
        } else if (action === 'pending') {
          changeStatusToPending(shortId);
        } else if (action === 'delete') {
          deleteShort(shortId);
        }
      });
      
      // 이벤트 위임: 일괄 작업 버튼 클릭 처리
      container.addEventListener('click', (e) => {
        const button = e.target.closest('.bulk-action-btn');
        if (!button) return;
        
        const action = button.dataset.bulkAction;
        
        if (action === 'approve') {
          bulkApproveShorts();
        } else if (action === 'reject') {
          bulkRejectShorts();
        } else if (action === 'pending') {
          bulkSetPending();
        } else if (action === 'delete') {
          bulkDeleteShorts();
        }
      });
      
      // 이벤트 위임: 체크박스 변경 처리
      container.addEventListener('change', (e) => {
        const checkbox = e.target;
        const checkboxAction = checkbox.dataset.checkboxAction;
        
        if (checkboxAction === 'selectAll') {
          toggleSelectAll();
        } else if (checkboxAction === 'toggleShort') {
          updateBulkActionButtons();
        }
      });
    }
  } catch (error) {
    console.error('쇼츠 로드 실패:', error);
  }
}

// 크리에이터 목록 로드
async function loadCreators() {
  try {
    const response = await axios.get('/api/admin/creators');
    
    if (response.data.success) {
      const creators = response.data.data;
      const container = document.getElementById('tabContent');
      
      if (creators.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center">크리에이터가 없습니다.</p>';
        return;
      }
      
      container.innerHTML = `
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">이름</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">이메일</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">YouTube 채널</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">쇼츠</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">승인된 쇼츠</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">수익</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">상태</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">작업</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              ${creators.map(creator => `
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm">${creator.user_name}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">${creator.user_email}</td>
                  <td class="px-4 py-3 text-sm">
                    ${creator.youtube_channel_name || '-'}
                    ${creator.youtube_channel_url ? `
                      <a href="${creator.youtube_channel_url}" target="_blank" 
                         class="text-blue-500 hover:text-blue-600 ml-1">
                        <i class="fab fa-youtube"></i>
                      </a>
                    ` : ''}
                  </td>
                  <td class="px-4 py-3 text-sm">${creator.total_shorts || 0}</td>
                  <td class="px-4 py-3 text-sm">${creator.approved_shorts || 0}</td>
                  <td class="px-4 py-3 text-sm text-green-600 font-medium">
                    ₩${(creator.total_earnings || 0).toLocaleString()}
                  </td>
                  <td class="px-4 py-3 text-sm">
                    ${creator.is_approved ? 
                      '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">승인됨</span>' : 
                      '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">대기중</span>'
                    }
                  </td>
                  <td class="px-4 py-3 text-sm">
                    ${creator.is_approved ? `
                      <button onclick="revokeCreator(${creator.id})" 
                              class="text-red-500 hover:text-red-600 text-xs">
                        승인 취소
                      </button>
                    ` : `
                      <button onclick="approveCreator(${creator.id})" 
                              class="text-green-500 hover:text-green-600 text-xs">
                        승인
                      </button>
                    `}
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  } catch (error) {
    console.error('크리에이터 로드 실패:', error);
  }
}

// 상태 배지
function getStatusBadge(status) {
  const badges = {
    'pending': '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">승인 대기</span>',
    'approved': '<span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">승인됨</span>',
    'rejected': '<span class="px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">반려됨</span>'
  };
  return badges[status] || '';
}

// 쇼츠 승인
async function approveShort(shortId) {
  const note = prompt('승인 메시지 (선택사항):');
  
  try {
    const response = await axios.post(`/api/admin/shorts/${shortId}/approve`, {
      adminId: user.id,
      note: note || null
    });
    
    if (response.data.success) {
      alert('승인되었습니다.');
      loadShorts(currentTab);
      loadStats();
    }
  } catch (error) {
    alert(error.response?.data?.error || '승인에 실패했습니다.');
  }
}

// 쇼츠 반려
async function rejectShort(shortId) {
  const note = prompt('반려 사유 (필수):');
  
  if (!note) {
    alert('반려 사유를 입력해주세요.');
    return;
  }
  
  try {
    const response = await axios.post(`/api/admin/shorts/${shortId}/reject`, {
      adminId: user.id,
      note: note
    });
    
    if (response.data.success) {
      alert('반려되었습니다.');
      loadShorts(currentTab);
      loadStats();
    }
  } catch (error) {
    alert(error.response?.data?.error || '반려에 실패했습니다.');
  }
}

// 쇼츠 삭제
async function deleteShort(shortId) {
  if (!confirm('이 쇼츠를 삭제하시겠습니까? 이 작업은 취소할 수 없습니다.')) {
    return;
  }
  
  try {
    const token = localStorage.getItem('token');
    const response = await axios.delete(`/api/shorts/${shortId}`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    if (response.data.success) {
      alert('삭제되었습니다.');
      loadShorts(currentTab);
      loadStats();
    }
  } catch (error) {
    alert(error.response?.data?.error || '삭제에 실패했습니다.');
  }
}

// 쇼츠 상태를 대기로 변경
async function changeStatusToPending(shortId) {
  if (!confirm('이 쇼츠를 승인 대기 상태로 변경하시겠습니까?')) {
    return;
  }
  
  try {
    const response = await axios.post(`/api/admin/shorts/${shortId}/pending`, {
      adminId: user.id
    });
    
    if (response.data.success) {
      alert('상태가 승인 대기로 변경되었습니다.');
      loadShorts(currentTab);
      loadStats();
    }
  } catch (error) {
    alert(error.response?.data?.error || '상태 변경에 실패했습니다.');
  }
}

// 크리에이터 승인
async function approveCreator(creatorId) {
  if (!confirm('이 크리에이터를 승인하시겠습니까?')) return;
  
  try {
    const response = await axios.post(`/api/admin/creators/${creatorId}/approve`);
    
    if (response.data.success) {
      alert('승인되었습니다.');
      loadCreators();
    }
  } catch (error) {
    alert(error.response?.data?.error || '승인에 실패했습니다.');
  }
}

// 크리에이터 승인 취소
async function revokeCreator(creatorId) {
  if (!confirm('이 크리에이터의 승인을 취소하시겠습니까?')) return;
  
  try {
    const response = await axios.post(`/api/admin/creators/${creatorId}/revoke`);
    
    if (response.data.success) {
      alert('승인이 취소되었습니다.');
      loadCreators();
    }
  } catch (error) {
    alert(error.response?.data?.error || '승인 취소에 실패했습니다.');
  }
}

// 페이지 로드 시 실행
document.addEventListener('DOMContentLoaded', () => {
  loadShorts('pending');
  
  // 전체 통계 로드
  loadStats();
});

// 전체 통계 로드
async function loadStats() {
  try {
    const response = await axios.get('/api/admin/stats');
    
    if (response.data.success) {
      const stats = response.data.data;
      
      // 카운트 업데이트
      document.getElementById('pendingCount').textContent = 
        stats.shortsByStatus?.find(s => s.status === 'pending')?.count || 0;
      document.getElementById('approvedCount').textContent = 
        stats.shortsByStatus?.find(s => s.status === 'approved')?.count || 0;
      document.getElementById('rejectedCount').textContent = 
        stats.shortsByStatus?.find(s => s.status === 'rejected')?.count || 0;
    }
  } catch (error) {
    console.error('통계 로드 실패:', error);
  }
}

// ============================================
// 일괄 작업 기능
// ============================================

// 전체 선택/해제
function toggleSelectAll() {
  const selectAllCheckbox = document.getElementById('selectAllCheckbox');
  const checkboxes = document.querySelectorAll('.short-checkbox');
  
  checkboxes.forEach(checkbox => {
    checkbox.checked = selectAllCheckbox.checked;
  });
  
  updateBulkActionButtons();
}

// 선택된 항목 개수 업데이트
function updateBulkActionButtons() {
  const checkedBoxes = document.querySelectorAll('.short-checkbox:checked');
  const count = checkedBoxes.length;
  const selectedCountSpan = document.getElementById('selectedCount');
  
  // 버튼들 가져오기
  const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
  const bulkPendingBtn = document.getElementById('bulkPendingBtn');
  const bulkApproveBtn = document.getElementById('bulkApproveBtn');
  const bulkRejectBtn = document.getElementById('bulkRejectBtn');
  
  if (count > 0) {
    selectedCountSpan.textContent = `${count}개 선택됨`;
    
    // 공통 버튼 활성화
    if (bulkDeleteBtn) {
      bulkDeleteBtn.disabled = false;
      bulkDeleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    if (bulkPendingBtn) {
      bulkPendingBtn.disabled = false;
      bulkPendingBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    // pending 탭 전용 버튼 활성화
    if (bulkApproveBtn) {
      bulkApproveBtn.disabled = false;
      bulkApproveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    if (bulkRejectBtn) {
      bulkRejectBtn.disabled = false;
      bulkRejectBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  } else {
    selectedCountSpan.textContent = '0개 선택됨';
    
    // 모든 버튼 비활성화
    if (bulkDeleteBtn) {
      bulkDeleteBtn.disabled = true;
      bulkDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    if (bulkPendingBtn) {
      bulkPendingBtn.disabled = true;
      bulkPendingBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    if (bulkApproveBtn) {
      bulkApproveBtn.disabled = true;
      bulkApproveBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    if (bulkRejectBtn) {
      bulkRejectBtn.disabled = true;
      bulkRejectBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
  }
  
  // 전체선택 체크박스 상태 업데이트
  const allCheckboxes = document.querySelectorAll('.short-checkbox');
  const selectAllCheckbox = document.getElementById('selectAllCheckbox');
  if (selectAllCheckbox && allCheckboxes.length > 0) {
    selectAllCheckbox.checked = count === allCheckboxes.length;
  }
}

// 선택 항목 일괄 삭제
async function bulkDeleteShorts() {
  console.log('🗑️ bulkDeleteShorts 함수 호출됨');
  
  const checkedBoxes = document.querySelectorAll('.short-checkbox:checked');
  const shortIds = Array.from(checkedBoxes).map(cb => parseInt(cb.dataset.shortId));
  
  console.log('✅ 선택된 체크박스:', checkedBoxes.length);
  console.log('✅ 삭제할 Short IDs:', shortIds);
  
  if (shortIds.length === 0) {
    alert('삭제할 쇼츠를 선택해주세요.');
    return;
  }
  
  if (!confirm(`선택한 ${shortIds.length}개의 쇼츠를 삭제하시겠습니까?`)) {
    console.log('❌ 사용자가 삭제 취소함');
    return;
  }
  
  try {
    const button = document.getElementById('bulkDeleteBtn');
    if (!button) {
      console.error('❌ bulkDeleteBtn을 찾을 수 없습니다!');
      alert('삭제 버튼을 찾을 수 없습니다.');
      return;
    }
    
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 삭제 중...';
    button.disabled = true;
    
    let successCount = 0;
    let failCount = 0;
    
    for (const shortId of shortIds) {
      try {
        console.log(`🗑️ Short ${shortId} 삭제 요청 중...`);
        const response = await axios.delete(`/api/admin/shorts/${shortId}`);
        console.log(`✅ Short ${shortId} 삭제 성공:`, response.data);
        successCount++;
      } catch (error) {
        console.error(`❌ Short ${shortId} 삭제 실패:`, error);
        console.error('에러 상세:', error.response?.data);
        failCount++;
      }
    }
    
    alert(`삭제 완료!\n\n성공: ${successCount}개\n실패: ${failCount}개`);
    
    console.log('🔄 목록 새로고침 중...');
    loadShorts(currentTab);
    loadStats();
    
    button.innerHTML = originalText;
    button.disabled = false;
    
  } catch (error) {
    console.error('❌ 일괄 삭제 오류:', error);
    alert('일괄 삭제에 실패했습니다: ' + (error.message || '알 수 없는 오류'));
    
    const button = document.getElementById('bulkDeleteBtn');
    if (button) {
      button.innerHTML = '<i class="fas fa-trash"></i> 선택 항목 삭제';
      button.disabled = false;
    }
  }
}

// 선택 항목 일괄 대기 상태로 변경
async function bulkSetPending() {
  const checkedBoxes = document.querySelectorAll('.short-checkbox:checked');
  const shortIds = Array.from(checkedBoxes).map(cb => parseInt(cb.dataset.shortId));
  
  if (shortIds.length === 0) {
    alert('대기 상태로 변경할 쇼츠를 선택해주세요.');
    return;
  }
  
  if (!confirm(`선택한 ${shortIds.length}개의 쇼츠를 대기 상태로 변경하시겠습니까?`)) {
    return;
  }
  
  try {
    const button = document.getElementById('bulkPendingBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 처리 중...';
    button.disabled = true;
    
    let successCount = 0;
    let failCount = 0;
    
    for (const shortId of shortIds) {
      try {
        await axios.post(`/api/admin/shorts/${shortId}/pending`, {
          adminId: user.id
        });
        successCount++;
      } catch (error) {
        console.error(`Short ${shortId} 상태 변경 실패:`, error);
        failCount++;
      }
    }
    
    alert(`상태 변경 완료!\n\n성공: ${successCount}개\n실패: ${failCount}개`);
    
    loadShorts(currentTab);
    loadStats();
    
  } catch (error) {
    console.error('일괄 상태 변경 오류:', error);
    alert('일괄 상태 변경에 실패했습니다.');
  }
}

// 선택 항목 일괄 승인
async function bulkApproveShorts() {
  const checkedBoxes = document.querySelectorAll('.short-checkbox:checked');
  const shortIds = Array.from(checkedBoxes).map(cb => parseInt(cb.dataset.shortId));
  
  if (shortIds.length === 0) {
    alert('승인할 쇼츠를 선택해주세요.');
    return;
  }
  
  const note = prompt(`선택한 ${shortIds.length}개의 쇼츠를 승인합니다.\n\n승인 메시지 (선택사항):`);
  
  if (note === null) {
    return; // 취소한 경우
  }
  
  try {
    const button = document.getElementById('bulkApproveBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 승인 중...';
    button.disabled = true;
    
    let successCount = 0;
    let failCount = 0;
    
    for (const shortId of shortIds) {
      try {
        await axios.post(`/api/admin/shorts/${shortId}/approve`, {
          adminId: user.id,
          note: note || null
        });
        successCount++;
      } catch (error) {
        console.error(`Short ${shortId} 승인 실패:`, error);
        failCount++;
      }
    }
    
    alert(`승인 완료!\n\n성공: ${successCount}개\n실패: ${failCount}개`);
    
    loadShorts(currentTab);
    loadStats();
    
  } catch (error) {
    console.error('일괄 승인 오류:', error);
    alert('일괄 승인에 실패했습니다.');
  }
}

// 선택 항목 일괄 반려
async function bulkRejectShorts() {
  const checkedBoxes = document.querySelectorAll('.short-checkbox:checked');
  const shortIds = Array.from(checkedBoxes).map(cb => parseInt(cb.dataset.shortId));
  
  if (shortIds.length === 0) {
    alert('반려할 쇼츠를 선택해주세요.');
    return;
  }
  
  const note = prompt(`선택한 ${shortIds.length}개의 쇼츠를 반려합니다.\n\n반려 사유 (필수):`);
  
  if (!note) {
    alert('반려 사유를 입력해주세요.');
    return;
  }
  
  try {
    const button = document.getElementById('bulkRejectBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 반려 중...';
    button.disabled = true;
    
    let successCount = 0;
    let failCount = 0;
    
    for (const shortId of shortIds) {
      try {
        await axios.post(`/api/admin/shorts/${shortId}/reject`, {
          adminId: user.id,
          note: note
        });
        successCount++;
      } catch (error) {
        console.error(`Short ${shortId} 반려 실패:`, error);
        failCount++;
      }
    }
    
    alert(`반려 완료!\n\n성공: ${successCount}개\n실패: ${failCount}개`);
    
    loadShorts(currentTab);
    loadStats();
    
  } catch (error) {
    console.error('일괄 반려 오류:', error);
    alert('일괄 반려에 실패했습니다.');
  }
}
