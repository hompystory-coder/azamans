const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  
  console.log('🌐 페이지 로딩 중...');
  await page.goto('https://ai-studio.neuralgrid.kr/pro-shorts', {
    waitUntil: 'networkidle2',
    timeout: 60000
  });
  
  console.log('✅ 페이지 로드 완료!');
  
  // 프롬프트 입력
  console.log('📝 프롬프트 입력 중...');
  await page.type('input[type="text"]', '토끼와 거북이');
  
  // 비디오 길이 설정
  await page.evaluate(() => {
    const durationInput = document.querySelector('input[type="number"]');
    if (durationInput) durationInput.value = 20; // 20초로 짧게
  });
  
  console.log('🚀 생성 버튼 클릭!');
  await page.click('button:not([disabled])');
  
  // 콘솔 로그 수집
  page.on('console', msg => {
    const text = msg.text();
    if (text.includes('Stage') || text.includes('Scene') || text.includes('Video')) {
      console.log('🔔', text);
    }
  });
  
  // 60초 대기 (생성 완료까지)
  console.log('⏳ 생성 대기 중 (최대 120초)...');
  await page.waitForTimeout(120000);
  
  // 완성된 비디오 확인
  const videoElement = await page.$('video');
  if (videoElement) {
    const videoSrc = await page.evaluate(el => el.src, videoElement);
    console.log('✅ 비디오 생성 완료!');
    console.log('📹 비디오 URL:', videoSrc);
  } else {
    console.log('❌ 비디오를 찾을 수 없습니다.');
  }
  
  // 스크린샷
  await page.screenshot({ path: '/tmp/shorts_result.png', fullPage: true });
  console.log('📸 스크린샷 저장: /tmp/shorts_result.png');
  
  await browser.close();
})();
