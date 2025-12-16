#!/usr/bin/env node

console.log('🧪 Testing Registration Flow\n');

async function testFlow() {
    try {
        console.log('Step 1: Get a test token from auth service');
        console.log('(In real scenario, user logs in and gets a JWT token)');
        const fakeToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.test';
        
        console.log('\nStep 2: Verify token with auth service');
        const verifyResponse = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: fakeToken })
        });
        
        console.log('   Verify Status:', verifyResponse.status);
        console.log('   Verify Headers:', Object.fromEntries(verifyResponse.headers));
        
        const verifyText = await verifyResponse.text();
        console.log('   Verify Response:', verifyText);
        
        console.log('\nStep 3: Test DDoS registration endpoint');
        console.log('   URL: https://ddos.neuralgrid.kr/api/servers/register-website');
        
        const regResponse = await fetch('https://ddos.neuralgrid.kr/api/servers/register-website', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${fakeToken}`
            },
            body: JSON.stringify({
                companyName: '테스트 회사',
                phone: '010-1234-5678',
                domains: 'test.example.com'
            })
        });
        
        console.log('   Registration Status:', regResponse.status);
        console.log('   Registration Headers:', Object.fromEntries(regResponse.headers));
        
        const regText = await regResponse.text();
        console.log('   Registration Response:', regText.substring(0, 500));
        
        if (regResponse.status === 401) {
            console.log('\n❌ GOT 401! This is the problem.');
            console.log('   The production server rejected the token.');
            console.log('   Need to check what verifyToken() is doing in production.');
        }
        
    } catch (error) {
        console.error('❌ Error:', error.message);
    }
}

testFlow();
