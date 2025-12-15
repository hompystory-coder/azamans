# 🤖 AnythingLLM Integration - NeuralGrid Platform

## 📋 Overview

**Service**: AnythingLLM - Personal LLM Platform with RAG capabilities
**Subdomain**: `ai.neuralgrid.kr`
**Backend Port**: `3104`
**Server**: `115.91.5.140`
**Status**: ✅ Configuration Ready

---

## 🌟 What is AnythingLLM?

AnythingLLM is a full-stack application that enables you to:
- 🧠 Create private AI workspaces
- 📚 Train LLMs on custom documents (RAG - Retrieval-Augmented Generation)
- 💬 Chat with your data securely
- 🔒 Keep all data private and on-premises

**Key Features:**
- Document embedding and vector storage
- Multi-LLM support (OpenAI, Anthropic, local models)
- Web scraping and document processing
- Custom AI agents
- Team collaboration

---

## 🚀 Quick Deployment Guide

### Step 1: Add DNS Record in Cloudflare

**CRITICAL**: You must add the DNS record first!

1. Login to Cloudflare Dashboard: https://dash.cloudflare.com
2. Select domain: `neuralgrid.kr`
3. Go to **DNS** → **Records**
4. Click **Add record**
5. Configure:
   ```
   Type: A
   Name: ai
   IPv4 address: 115.91.5.140
   Proxy status: ✅ Proxied (Orange cloud icon)
   TTL: Auto
   ```
6. Click **Save**
7. Wait 1-5 minutes for DNS propagation

### Step 2: Verify DNS Resolution

```bash
# Check if DNS is working
nslookup ai.neuralgrid.kr

# Expected output should show:
# Name: ai.neuralgrid.kr
# Address: 115.91.5.140 (or Cloudflare proxy IP)
```

### Step 3: Run Automated Setup Script

```bash
cd /home/azamans/webapp
./setup-ai-subdomain.sh
```

**The script will automatically:**
- ✅ Upload Nginx configuration
- ✅ Enable the site
- ✅ Test Nginx config
- ✅ Reload Nginx
- ✅ Configure SSL with Let's Encrypt
- ✅ Verify deployment

### Step 4: Verify Access

```bash
# Test HTTPS access
curl -I https://ai.neuralgrid.kr/

# Expected: HTTP/2 200 OK
```

**Browser Test:**
Open https://ai.neuralgrid.kr/ in your browser

---

## 📁 File Structure

```
/home/azamans/webapp/
├── ai.neuralgrid.kr.nginx.conf          # Nginx configuration
├── setup-ai-subdomain.sh                 # Automated setup script
└── ANYTHINGLLM_SETUP.md                  # This documentation

/etc/nginx/sites-available/
└── ai.neuralgrid.kr                      # Nginx config (deployed)

/etc/nginx/sites-enabled/
└── ai.neuralgrid.kr → ../sites-available/ai.neuralgrid.kr

/etc/letsencrypt/live/ai.neuralgrid.kr/
├── fullchain.pem                         # SSL certificate
└── privkey.pem                          # SSL private key
```

---

## 🔧 Technical Configuration

### Nginx Reverse Proxy

```nginx
# HTTP → HTTPS redirect
listen 80;
return 301 https://$server_name$request_uri;

# HTTPS configuration
listen 443 ssl http2;
server_name ai.neuralgrid.kr;

# Backend proxy
proxy_pass http://localhost:3104;
proxy_http_version 1.1;

# WebSocket support
location /ws {
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
}

# Long-running LLM requests
proxy_read_timeout 600s;
client_max_body_size 100M;
```

### Security Features

- ✅ **HTTPS/SSL**: Let's Encrypt certificate (TLS 1.2/1.3)
- ✅ **HSTS**: Strict-Transport-Security header
- ✅ **XSS Protection**: X-XSS-Protection header
- ✅ **Frame Options**: X-Frame-Options SAMEORIGIN
- ✅ **Content Sniffing**: X-Content-Type-Options nosniff
- ✅ **Cloudflare Proxy**: DDoS protection and CDN

### Performance Optimizations

- ✅ HTTP/2 enabled
- ✅ WebSocket support for real-time features
- ✅ 600s timeout for long LLM requests
- ✅ 100MB upload limit for documents
- ✅ Gzip compression (Nginx default)

---

## 🌐 Integration with NeuralGrid Main Page

### Add to neuralgrid.kr Dashboard

Update `/var/www/neuralgrid.kr/html/index.html`:

```html
<!-- Add to services section -->
<div class="service-card">
    <div class="icon">🤖</div>
    <h3>AI Assistant</h3>
    <p>Private LLM workspace with RAG</p>
    <a href="https://ai.neuralgrid.kr" class="btn">Launch AI →</a>
</div>
```

### Add to Dashboard (auth.neuralgrid.kr)

Update `/home/azamans/n8n-neuralgrid/auth-service/public/dashboard.html`:

```html
<!-- Add to quick access links -->
<a href="https://ai.neuralgrid.kr" class="service-link">
    <div class="icon">🤖</div>
    <div class="info">
        <h4>AI Assistant</h4>
        <p>Private LLM Platform</p>
    </div>
    <div class="status">●</div>
</a>
```

---

## 🔐 SSO Integration (Optional)

### Step 1: Create SSO Wrapper

```javascript
// Add to AnythingLLM frontend
<script src="https://auth.neuralgrid.kr/sso-middleware.js"></script>
<script>
  // Initialize SSO
  window.addEventListener('DOMContentLoaded', () => {
    if (window.NeuralGridSSO) {
      window.NeuralGridSSO.init({
        authUrl: 'https://auth.neuralgrid.kr',
        serviceName: 'ai.neuralgrid.kr',
        redirectPath: '/workspace'
      });
    }
  });
</script>
```

### Step 2: Backend Authentication

Integrate JWT verification in AnythingLLM backend:

```javascript
// Add to Express middleware
const jwt = require('jsonwebtoken');

function verifyNeuralGridAuth(req, res, next) {
  const token = req.headers.authorization?.split(' ')[1];
  
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;
    next();
  } catch (err) {
    res.status(401).json({ error: 'Unauthorized' });
  }
}
```

---

## 🧪 Testing Checklist

### Manual Testing

- [ ] DNS resolution works: `nslookup ai.neuralgrid.kr`
- [ ] HTTP redirects to HTTPS: `curl -I http://ai.neuralgrid.kr`
- [ ] HTTPS works: `curl -I https://ai.neuralgrid.kr`
- [ ] SSL certificate valid: Check in browser (padlock icon)
- [ ] Service loads correctly in browser
- [ ] File upload works (test document embedding)
- [ ] WebSocket connections work (real-time features)
- [ ] Long-running requests don't timeout

### Automated Testing

```bash
# DNS check
dig ai.neuralgrid.kr +short

# HTTP → HTTPS redirect
curl -I http://ai.neuralgrid.kr/ 2>&1 | grep "301\|Location"

# HTTPS status
curl -I https://ai.neuralgrid.kr/ 2>&1 | grep "HTTP"

# SSL certificate expiry
openssl s_client -connect ai.neuralgrid.kr:443 -servername ai.neuralgrid.kr < /dev/null 2>/dev/null | openssl x509 -noout -dates

# Backend health
curl -s http://115.91.5.140:3104/ | grep -o "<title>.*</title>"
```

---

## 📊 System Status

### Current Services (9/9 Operational)

| # | Service | Domain | Port | SSL | Status |
|---|---------|--------|------|-----|--------|
| 1 | Main Platform | `neuralgrid.kr` | 80/443 | ✅ | 🟢 Online |
| 2 | Auth Hub | `auth.neuralgrid.kr` | 3099 | ✅ | 🟢 Online |
| 3 | Blog Shorts | `bn-shop.neuralgrid.kr` | - | ✅ | 🟢 Online |
| 4 | MediaFX | `mfx.neuralgrid.kr` | - | ✅ | 🟢 Online |
| 5 | StarMusic | `music.neuralgrid.kr` | - | ✅ | 🟢 Online |
| 6 | Coupang Shorts | `market.neuralgrid.kr` | - | ✅ | 🟢 Online |
| 7 | N8N Automation | `n8n.neuralgrid.kr` | - | ✅ | 🟢 Online |
| 8 | Server Monitor | `monitor.neuralgrid.kr` | - | ✅ | 🟢 Online |
| 9 | **AI Assistant** | `ai.neuralgrid.kr` | 3104 | 🔄 Pending | 🟡 Setup |

---

## 🐛 Troubleshooting

### Issue 1: DNS not resolving

**Symptom**: `nslookup ai.neuralgrid.kr` fails

**Solution**:
1. Verify DNS record in Cloudflare
2. Wait 5-10 minutes for propagation
3. Clear DNS cache: `sudo systemd-resolve --flush-caches`
4. Try different DNS: `nslookup ai.neuralgrid.kr 8.8.8.8`

### Issue 2: SSL certificate error

**Symptom**: Browser shows "Certificate not valid"

**Solution**:
```bash
# Re-run Certbot
ssh azamans@115.91.5.140
sudo certbot --nginx -d ai.neuralgrid.kr --force-renewal
```

### Issue 3: 502 Bad Gateway

**Symptom**: Nginx shows 502 error

**Solution**:
```bash
# Check if backend is running
ssh azamans@115.91.5.140
pm2 list | grep 3104
curl http://localhost:3104/

# Restart backend if needed
pm2 restart <process-name>
```

### Issue 4: WebSocket connection fails

**Symptom**: Real-time features don't work

**Solution**:
- Check Nginx WebSocket configuration
- Verify `Upgrade` and `Connection` headers
- Check browser console for WebSocket errors

---

## 🔄 Maintenance

### SSL Certificate Renewal

Certbot auto-renews certificates. Verify:

```bash
ssh azamans@115.91.5.140
sudo certbot renew --dry-run
sudo systemctl status certbot.timer
```

### Monitoring

```bash
# Check service status
pm2 status

# View logs
pm2 logs <process-name>

# Monitor resource usage
htop
```

### Backup

```bash
# Backup Nginx config
sudo cp /etc/nginx/sites-available/ai.neuralgrid.kr ~/backups/

# Backup AnythingLLM data
# (depends on storage configuration)
```

---

## 📚 References

- **AnythingLLM GitHub**: https://github.com/Mintplex-Labs/anything-llm
- **AnythingLLM Docs**: https://docs.anythingllm.com/
- **NeuralGrid Auth**: https://auth.neuralgrid.kr/api-docs
- **Nginx Docs**: https://nginx.org/en/docs/
- **Let's Encrypt**: https://letsencrypt.org/docs/
- **Cloudflare DNS**: https://developers.cloudflare.com/dns/

---

## ✅ Deployment Checklist

### Pre-Deployment

- [x] Service identified (AnythingLLM on port 3104)
- [x] Subdomain chosen (`ai.neuralgrid.kr`)
- [x] Nginx config created
- [x] Setup script created
- [x] Documentation written

### Deployment

- [ ] DNS A record added in Cloudflare
- [ ] DNS propagation verified
- [ ] Setup script executed
- [ ] SSL certificate configured
- [ ] HTTPS access verified
- [ ] Service functionality tested

### Post-Deployment

- [ ] Integrated with main page
- [ ] SSO authentication added (optional)
- [ ] Monitoring configured
- [ ] Documentation updated
- [ ] Changes committed to Git

---

## 🎉 Success Metrics

After successful deployment, you should have:

✅ **Secure Access**: https://ai.neuralgrid.kr (HTTPS with valid SSL)
✅ **Fast Performance**: <100ms response time
✅ **High Availability**: 99.9% uptime
✅ **Seamless Integration**: Linked from main platform
✅ **Professional Setup**: Production-grade configuration

---

## 📞 Support

For issues or questions:
- Check logs: `pm2 logs` and `/var/log/nginx/ai.neuralgrid.kr.error.log`
- Review this documentation
- Check NeuralGrid system documentation

---

**Document Version**: 1.0.0
**Last Updated**: 2025-12-15
**Author**: GenSpark AI Developer
**Status**: ✅ Ready for Deployment
