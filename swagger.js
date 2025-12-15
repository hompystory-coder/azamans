const swaggerJsdoc = require('swagger-jsdoc');

const options = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'NeuralGrid Auth API',
      version: '1.0.0',
      description: 'NeuralGrid 통합 인증 시스템 API 문서',
      contact: {
        name: 'NeuralGrid Support',
        email: 'admin@neuralgrid.kr',
        url: 'https://neuralgrid.kr'
      },
      license: {
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
      }
    },
    servers: [
      {
        url: 'https://auth.neuralgrid.kr',
        description: 'Production server'
      },
      {
        url: 'http://localhost:3099',
        description: 'Development server'
      }
    ],
    components: {
      securitySchemes: {
        bearerAuth: {
          type: 'http',
          scheme: 'bearer',
          bearerFormat: 'JWT',
          description: 'JWT Authorization header using the Bearer scheme'
        }
      },
      schemas: {
        User: {
          type: 'object',
          required: ['name', 'email', 'password'],
          properties: {
            id: {
              type: 'integer',
              description: '사용자 고유 ID',
              example: 1
            },
            name: {
              type: 'string',
              description: '사용자 이름',
              example: '홍길동'
            },
            email: {
              type: 'string',
              format: 'email',
              description: '이메일 주소',
              example: 'user@example.com'
            },
            password: {
              type: 'string',
              format: 'password',
              description: '비밀번호 (최소 6자)',
              example: 'password123'
            },
            created_at: {
              type: 'string',
              format: 'date-time',
              description: '계정 생성 시간'
            }
          }
        },
        LoginRequest: {
          type: 'object',
          required: ['email', 'password'],
          properties: {
            email: {
              type: 'string',
              format: 'email',
              example: 'user@example.com'
            },
            password: {
              type: 'string',
              format: 'password',
              example: 'password123'
            }
          }
        },
        RegisterRequest: {
          type: 'object',
          required: ['name', 'email', 'password'],
          properties: {
            name: {
              type: 'string',
              example: '홍길동'
            },
            email: {
              type: 'string',
              format: 'email',
              example: 'user@example.com'
            },
            password: {
              type: 'string',
              format: 'password',
              minLength: 6,
              example: 'password123'
            }
          }
        },
        AuthResponse: {
          type: 'object',
          properties: {
            success: {
              type: 'boolean',
              example: true
            },
            message: {
              type: 'string',
              example: '로그인 성공'
            },
            token: {
              type: 'string',
              description: 'JWT 액세스 토큰',
              example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
            },
            user: {
              $ref: '#/components/schemas/User'
            }
          }
        },
        ErrorResponse: {
          type: 'object',
          properties: {
            success: {
              type: 'boolean',
              example: false
            },
            message: {
              type: 'string',
              example: '에러 메시지'
            },
            error: {
              type: 'string',
              example: '상세 에러 내용'
            }
          }
        },
        HealthResponse: {
          type: 'object',
          properties: {
            status: {
              type: 'string',
              example: 'healthy'
            },
            service: {
              type: 'string',
              example: 'NeuralGrid Auth Service'
            },
            timestamp: {
              type: 'string',
              format: 'date-time'
            }
          }
        }
      }
    },
    tags: [
      {
        name: 'Auth',
        description: '인증 관련 API (로그인, 회원가입)'
      },
      {
        name: 'Health',
        description: '헬스 체크 API'
      },
      {
        name: 'User',
        description: '사용자 정보 관리 API'
      }
    ]
  },
  apis: ['./routes/*.js', './index.js']
};

const specs = swaggerJsdoc(options);

module.exports = specs;
