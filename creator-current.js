// 크리에이터 등록 JavaScript

document.getElementById('creatorForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  
  // 로그인 확인
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!user.id) {
    alert('로그인이 필요합니다.');
    window.location.href = '/';
    return;
  }
  
  const data = {
    userId: user.id,
    youtubeChannelId: formData.get('youtube_channel_id'),
    youtubeChannelName: formData.get('youtube_channel_name'),
    coupangAccessKey: formData.get('coupang_access_key') || null,
    coupangSecretKey: formData.get('coupang_secret_key') || null
  };
  
  try {
    const response = await axios.post('/api/creator/register', data);
    
    if (response.data.success) {
      alert(response.data.message);
      window.location.href = '/mypage';
    }
  } catch (error) {
    alert(error.response?.data?.error || '크리에이터 등록에 실패했습니다.');
  }
});
