// 스크립트 생성 API (Gemini API)
import express from 'express';
import axios from 'axios';

const router = express.Router();

// POST /api/script/generate - AI 스크립트 생성
router.post('/generate', async (req, res) => {
  try {
    const { 
      title, 
      content, 
      images, 
      prompt,
      sceneCount = 5,
      geminiApiKey 
    } = req.body;
    
    const apiKey = geminiApiKey || process.env.GEMINI_API_KEY;
    
    if (!apiKey) {
      return res.status(400).json({
        success: false,
        error: 'Gemini API 키가 필요합니다.'
      });
    }
    
    if (!content) {
      return res.status(400).json({
        success: false,
        error: '콘텐츠가 필요합니다.'
      });
    }
    
    console.log(`🤖 Gemini API로 스크립트 생성 시작...`);
    console.log(`   제목: ${title}`);
    console.log(`   콘텐츠 길이: ${content.length}자`);
    console.log(`   이미지 수: ${images?.length || 0}개`);
    console.log(`   장면 수: ${sceneCount}개`);
    
    // 기본 프롬프트
    const defaultPrompt = `
당신은 유튜브 쇼츠 TTS 나레이션 전문 작가입니다. 주어진 블로그/기사 내용을 분석하여 ${sceneCount}개의 장면으로 구성된 매력적인 쇼츠 스크립트를 작성해주세요.

절대 금지 사항:
- 이모티콘/이모지 사용 금지
- 장면 설명 금지 (예: "장면", "1-3초" 등)
- 마크다운 형식 금지 (**, #, - 등)
- 콜론 사용 금지
- 제목/부제 형식 금지
- 타임코드 금지

작성 원칙:
1. 원문 충실 (블로그 내용 기반, 상상 금지)
2. 자연스러운 말투 (TTS용 구어체)
3. 문장 길이: 각 15-50자
4. 총 ${sceneCount}개 문장
5. 마침표로만 구분

예시:
크리스마스가 다가왔습니다. 올해는 특별한 트리로 분위기를 내보세요. 고급스러운 블랙 컬러가 돋보입니다.

응답은 반드시 JSON 형식으로:
{
  "title": "쇼츠 제목 (구체적이고 간결하게)",
  "description": "쇼츠 설명 (간략히 1-2문장)",
  "keywords": ["키워드1", "키워드2", "키워드3"],
  "scenes": [
    {
      "sceneNumber": 1,
      "narration": "순수 텍스트만 작성 (15-50자, 구어체)",
      "imageDescription": "이미지 설명 (간략히)",
      "duration": 3.5
    }
  ]
}
`;
    
    const systemPrompt = prompt || defaultPrompt;
    
    // Gemini API 호출
    const geminiResponse = await axios.post(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=${apiKey}`,
      {
        contents: [
          {
            parts: [
              {
                text: `${systemPrompt}\n\n제목: ${title}\n\n본문:\n${content.substring(0, 5000)}`
              }
            ]
          }
        ],
        generationConfig: {
          temperature: 0.7,
          maxOutputTokens: 2048
        }
      },
      {
        headers: {
          'Content-Type': 'application/json'
        }
      }
    );
    
    const responseText = geminiResponse.data.candidates[0].content.parts[0].text;
    console.log(`✅ Gemini API 응답 받음`);
    
    // JSON 추출 (```json ... ``` 형식 처리) 또는 순수 텍스트 처리
    let scriptData;
    try {
      const jsonMatch = responseText.match(/```json\s*([\s\S]*?)\s*```/);
      if (jsonMatch) {
        scriptData = JSON.parse(jsonMatch[1]);
      } else {
        // JSON 파싱 시도
        try {
          scriptData = JSON.parse(responseText);
        } catch {
          // JSON이 아닌 경우 순수 텍스트로 처리 (마침표로 구분)
          console.log('순수 텍스트 응답 감지, 장면으로 변환 중...');
          const sentences = responseText
            .split(/\n+/)
            .map(s => s.trim())
            .filter(s => s.length > 0 && s.length >= 10);
          
          // 장면 객체로 변환
          const scenes = sentences.map((sentence, index) => ({
            sceneNumber: index + 1,
            narration: sentence,
            imageDescription: `장면 ${index + 1}`,
            duration: 3.5
          }));
          
          scriptData = {
            title: title || '유튜브 쇼츠',
            description: sentences[0] || '',
            keywords: [],
            scenes: scenes
          };
          
          console.log(`✅ 순수 텍스트를 ${scenes.length}개 장면으로 변환`);
        }
      }
    } catch (parseError) {
      console.error('JSON 파싱 오류:', parseError);
      return res.status(500).json({
        success: false,
        error: 'AI 응답을 파싱할 수 없습니다.',
        rawResponse: responseText
      });
    }
    
    // 이미지 매칭 (간단한 매칭) - imageUrl로 매핑하여 비디오 렌더러와 호환
    if (images && images.length > 0) {
      scriptData.scenes = scriptData.scenes.map((scene, index) => {
        const imageObj = images[index % images.length];
        const imageUrl = typeof imageObj === 'string' ? imageObj : (imageObj.url || imageObj.proxyUrl);
        
        return {
          ...scene,
          imageUrl: imageUrl,  // 비디오 렌더러가 사용
          suggestedImage: imageObj  // 호환성 유지
        };
      });
    }
    
    console.log(`✅ 스크립트 생성 완료: ${scriptData.scenes.length}개 장면`);
    
    res.json({
      success: true,
      data: scriptData
    });
    
  } catch (error) {
    console.error('❌ 스크립트 생성 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message || '스크립트 생성에 실패했습니다.',
      details: error.response?.data
    });
  }
});

export default router;
