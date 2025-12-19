// 음성 미리듣기 API 수정 버전
// output_format을 "hex"에서 제거하여 기본 바이너리 응답 받기

const fixedPreviewHandler = `
    // Minimax T2A API v2 호출 (공식 문서 기준)
    const response = await axios.post(
      "https://api.minimax.io/v1/t2a_v2",
      {
        model: "speech-2.6-hd",
        text: text || "안녕하세요. 이것은 음성 샘플입니다.",
        stream: false,
        // output_format 제거 - 기본값으로 바이너리 응답 받음
        voice_setting: {
          voice_id: minimaxVoiceId,
          speed: 1.0,
          vol: 1.0,
          pitch: 0
        },
        audio_setting: {
          sample_rate: 32000,
          bitrate: 128000,
          format: "mp3",
          channel: 1
        }
      },
      {
        headers: {
          "Authorization": \`Bearer \${apiKey}\`,
          "Content-Type": "application/json"
        },
        responseType: "arraybuffer"  // 바이너리 데이터로 받음
      }
    );
`;

console.log(fixedPreviewHandler);
