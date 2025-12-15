// 쇼츠 상세 페이지 JavaScript

let currentShort = null;

// URL에서 쇼츠 ID 추출
const shortId = window.location.pathname.split('/').pop();

// 쇼츠 정보 로드
async function loadShortDetail() {
  try {
    const response = await axios.get(`/api/shorts/${shortId}`);
    
    if (response.data.success) {
      currentShort = response.data.data;
      displayShortDetail(currentShort);
    } else {
      alert('쇼츠를 찾을 수 없습니다.');
      window.location.href = '/';
    }
  } catch (error) {
    console.error('쇼츠 로드 실패:', error);
    alert('쇼츠를 불러올 수 없습니다.');
    window.location.href = '/';
  }
}

// URL을 클릭 가능한 링크로 변환
function linkifyText(text) {
  if (!text) return '설명이 없습니다.';
  
  // URL 정규식 패턴
  const urlPattern = /(https?:\/\/[^\s]+)/g;
  
  // 줄바꿈을 <br>로 변환하고 URL을 링크로 변환
  return text
    .replace(/\n/g, '<br>')
    .replace(urlPattern, (url) => {
      // URL 끝의 특수문자 제거
      const cleanUrl = url.replace(/[.,;)]+$/, '');
      return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline">${cleanUrl}</a>`;
    });
}

// 쇼츠 상세 정보 표시
function displayShortDetail(short) {
  // 제목 및 설명
  document.getElementById('shortTitle').textContent = short.title;
  
  // 설명을 HTML로 변환하여 링크 활성화
  const descriptionEl = document.getElementById('shortDescription');
  descriptionEl.innerHTML = linkifyText(short.description);
  
  // 통계
  document.getElementById('viewCount').textContent = (short.view_count || 0).toLocaleString();
  document.getElementById('likeCount').textContent = (short.like_count || 0).toLocaleString();
  document.getElementById('clickCount').textContent = (short.click_count || 0).toLocaleString();
  
  // 크리에이터 정보
  const creatorName = short.creator_name || '크리에이터';
  document.getElementById('creatorName').textContent = creatorName;
  document.getElementById('creatorChannel').textContent = short.creator_channel_name || 'YouTube Channel';
  document.getElementById('creatorAvatar').textContent = creatorName.charAt(0);
  
  // 플랫폼별 구매 버튼 스타일 설정
  const platform = short.affiliate_platform || 'coupang';
  const buyButton = document.querySelector('button[onclick="buyProduct()"]');
  
  if (platform === 'naver') {
    if (buyButton) {
      buyButton.className = 'w-full bg-green-600 text-white py-4 rounded-lg hover:bg-green-700 font-bold text-lg shadow-lg';
      buyButton.innerHTML = '<i class="fas fa-shopping-cart"></i> 네이버에서 구매하기';
    }
  } else {
    if (buyButton) {
      buyButton.className = 'w-full bg-orange-500 text-white py-4 rounded-lg hover:bg-orange-600 font-bold text-lg shadow-lg';
      buyButton.innerHTML = '<i class="fas fa-shopping-cart"></i> 쿠팡에서 구매하기';
    }
  }
  
  // YouTube 영상 임베드
  embedYouTubeVideo(short.youtube_video_id);
}

// YouTube 영상 임베드
function embedYouTubeVideo(videoId) {
  const playerContainer = document.getElementById('videoPlayer');
  
  // YouTube Shorts는 일반 영상처럼 임베드
  const iframe = document.createElement('iframe');
  iframe.src = `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1`;
  iframe.width = '100%';
  iframe.height = '100%';
  iframe.frameBorder = '0';
  iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
  iframe.allowFullscreen = true;
  iframe.style.width = '100%';
  iframe.style.height = '100%';
  
  playerContainer.innerHTML = '';
  playerContainer.appendChild(iframe);
}

// 구매하기 버튼
async function buyProduct() {
  if (!currentShort) return;
  
  try {
    // 클릭 로그 기록
    const response = await axios.post(`/api/shorts/${shortId}/click`);
    
    if (response.data.success && response.data.data.deeplink) {
      // 쿠팡 상품 페이지로 이동
      window.open(response.data.data.deeplink, '_blank');
    } else {
      alert('상품 페이지로 이동할 수 없습니다.');
    }
  } catch (error) {
    console.error('구매하기 실패:', error);
    alert('구매하기를 처리할 수 없습니다.');
  }
}

// 페이지 로드 시 실행
document.addEventListener('DOMContentLoaded', () => {
  loadShortDetail();
});
