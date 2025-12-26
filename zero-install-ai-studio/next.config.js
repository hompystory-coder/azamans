/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  swcMinify: true,
  compress: true,
  
  images: {
    domains: ['localhost', 'api-inference.huggingface.co'],
    minimumCacheTTL: 60,
  },
  
  webpack: (config, { isServer }) => {
    // ONNX Runtime Web support - client side only
    if (!isServer) {
      config.resolve.fallback = {
        ...config.resolve.fallback,
        fs: false,
        path: false,
        module: false,
      }
      
      // Ignore ONNX Runtime Node.js specific files
      config.resolve.alias = {
        ...config.resolve.alias,
        'onnxruntime-node': false,
      }
    }
    
    // Ignore .node files completely
    config.module.rules.push({
      test: /\.node$/,
      loader: 'null-loader',
    })
    
    // Ignore ort.node files
    config.module.rules.push({
      test: /ort\.node\.min\.mjs$/,
      loader: 'null-loader',
    })
    
    // Handle .wasm files
    config.module.rules.push({
      test: /\.wasm$/,
      type: 'webassembly/async',
    })
    
    // Experiments for WebAssembly
    config.experiments = {
      ...config.experiments,
      asyncWebAssembly: true,
      layers: true,
    }
    
    return config
  },
  
  // Headers for security and performance
  async headers() {
    return [
      {
        source: '/:path*',
        headers: [
          {
            key: 'X-DNS-Prefetch-Control',
            value: 'on'
          },
          {
            key: 'X-Frame-Options',
            value: 'SAMEORIGIN'
          },
          {
            key: 'X-Content-Type-Options',
            value: 'nosniff'
          },
          {
            key: 'Referrer-Policy',
            value: 'origin-when-cross-origin'
          },
        ],
      },
      {
        source: '/_next/static/:path*',
        headers: [
          {
            key: 'Cache-Control',
            value: 'public, max-age=31536000, immutable',
          },
        ],
      },
    ]
  },
}

module.exports = nextConfig
