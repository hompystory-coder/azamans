#!/usr/bin/env node

console.log('🔍 Diagnosing DDoS Security Auth Issue\n');
console.log('Testing authentication endpoint...\n');

async function testAuth() {
    try {
        console.log('1️⃣ Testing with invalid token:');
        const response1 = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: 'invalid_test_token' })
        });
        
        console.log('   Status:', response1.status);
        console.log('   Content-Type:', response1.headers.get('content-type'));
        
        const text1 = await response1.text();
        console.log('   Response (first 200 chars):', text1.substring(0, 200));
        
        try {
            const json1 = JSON.parse(text1);
            console.log('   ✅ Valid JSON response:', json1);
        } catch (e) {
            console.log('   ❌ NOT JSON! Got HTML/text instead');
            console.log('   This is the problem!');
        }
        
        console.log('\n2️⃣ Testing without token (empty):');
        const response2 = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        
        console.log('   Status:', response2.status);
        const text2 = await response2.text();
        console.log('   Response:', text2.substring(0, 200));
        
        console.log('\n3️⃣ Testing GET method (wrong method):');
        const response3 = await fetch('https://auth.neuralgrid.kr/api/auth/verify');
        console.log('   Status:', response3.status);
        const text3 = await response3.text();
        console.log('   Response:', text3.substring(0, 200));
        
        console.log('\n4️⃣ Testing if endpoint exists:');
        const response4 = await fetch('https://auth.neuralgrid.kr/health');
        console.log('   Health endpoint status:', response4.status);
        const text4 = await response4.text();
        console.log('   Response:', text4.substring(0, 200));
        
    } catch (error) {
        console.error('❌ Error during testing:', error.message);
        console.error('Stack:', error.stack);
    }
}

testAuth();
