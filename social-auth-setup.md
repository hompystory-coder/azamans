# 소셜 로그인 설정 가이드

## Google OAuth 설정

1. **Google Cloud Console**: https://console.cloud.google.com/
2. **프로젝트 생성** → API 및 서비스 → 사용자 인증 정보
3. **OAuth 2.0 클라이언트 ID 생성**
   - 애플리케이션 유형: 웹 애플리케이션
   - 승인된 리디렉션 URI: `https://auth.neuralgrid.kr/auth/google/callback`
4. **클라이언트 ID와 Secret을 .env에 추가**

```bash
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
```

## GitHub OAuth 설정

1. **GitHub Settings**: https://github.com/settings/developers
2. **New OAuth App**
   - Application name: NeuralGrid Auth
   - Homepage URL: https://neuralgrid.kr
   - Authorization callback URL: `https://auth.neuralgrid.kr/auth/github/callback`
3. **클라이언트 ID와 Secret을 .env에 추가**

```bash
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
```

## Passport.js 통합 (준비 완료)

의존성이 이미 설치되었습니다:
- passport
- passport-google-oauth20
- passport-github2

## 로그인 페이지 업데이트

소셜 로그인 버튼이 준비되었습니다:
- Google 로그인 버튼
- GitHub 로그인 버튼

## 사용 방법

1. .env 파일에 OAuth 자격 증명 추가
2. auth 서비스 재시작: `pm2 restart auth-service`
3. https://auth.neuralgrid.kr 에서 소셜 로그인 버튼 확인
4. 버튼 클릭 → OAuth 인증 → 자동 회원가입/로그인

## 보안 참고사항

- OAuth 자격 증명은 절대 Git에 커밋하지 마세요
- .env 파일을 .gitignore에 추가하세요
- Production 환경에서는 환경 변수로 관리하세요
