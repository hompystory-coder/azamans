#!/usr/bin/env python3
import re

# Read the file
with open('neuralgrid-main-page.html', 'r', encoding='utf-8') as f:
    content = f.read()

# New loadServices function
new_load_services = """        // Load Services (분리된 메인/추가 서비스)
        async function loadServices() {
            // Render main services
            const mainGrid = document.getElementById('main-services-grid');
            const additionalGrid = document.getElementById('additional-services-grid');
            
            if (mainGrid) {
                mainGrid.innerHTML = '';
                Object.keys(mainServices).forEach((serviceName, index) => {
                    const serviceInfo = mainServices[serviceName];
                    const card = createServiceCard(serviceInfo, serviceName, index);
                    mainGrid.appendChild(card);
                });
            }
            
            if (additionalGrid) {
                additionalGrid.innerHTML = '';
                Object.keys(additionalServices).forEach((serviceName, index) => {
                    const serviceInfo = additionalServices[serviceName];
                    const card = createServiceCard(serviceInfo, serviceName, index);
                    additionalGrid.appendChild(card);
                });
            }
        }
        
        // Create service card helper
        function createServiceCard(serviceInfo, serviceName, index) {
            const card = document.createElement('div');
            card.className = 'service-card reveal';
            card.style.animationDelay = `${index * 0.1}s`;
            card.innerHTML = `
                <div class="service-header">
                    <div class="service-icon-wrapper">
                        <div class="service-icon">${serviceInfo.icon || '⚡'}</div>
                    </div>
                    <span class="service-status status-online">
                        <span class="status-dot"></span>
                        Online
                    </span>
                </div>
                <h3 class="service-title">
                    ${serviceInfo.titleKo || serviceName}
                    ${serviceInfo.titleEn ? `<span class="service-title-en">${serviceInfo.titleEn}</span>` : ''}
                </h3>
                <p class="service-description">${serviceInfo.description || '서비스 설명이 없습니다.'}</p>
                ${serviceInfo.features ? `
                    <ul class="service-features">
                        ${serviceInfo.features.map(f => `<li>${f}</li>`).join('')}
                    </ul>
                ` : ''}
                ${serviceInfo.pricing ? `
                    <div class="service-pricing">
                        💰 ${serviceInfo.pricing}
                    </div>
                ` : ''}
                <a href="${serviceInfo.url}" class="service-link" target="_blank">
                    서비스 바로가기 →
                </a>
            `;
            return card;
        }"""

# Find and replace loadServices
pattern = r'// Load Services.*?async function loadServices\(\) \{[\s\S]*?grid\.appendChild\(card\);[\s\S]*?\}\s*\}\s*catch'
match = re.search(pattern, content)

if match:
    old_block = match.group(0)
    # Keep the catch block
    new_block = new_load_services + "\n            } catch"
    content = content.replace(old_block, new_block)
    print("✅ loadServices function updated!")
else:
    print("❌ Could not find loadServices function")
    # Try alternative pattern
    pattern2 = r'// Load Services[\s\S]*?}\s*}\s*catch'
    match2 = re.search(pattern2, content)
    if match2:
        print("✅ Found alternative pattern")
        old_block = match2.group(0)
        new_block = new_load_services + "\n            } catch"
        content = content.replace(old_block, new_block)
        print("✅ Updated with alternative pattern!")

# Write back
with open('neuralgrid-main-page.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("🎉 File updated!")
