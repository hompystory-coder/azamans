/**
 * Auth Service Verification Test
 * 
 * Tests the auth.neuralgrid.kr/auth/verify endpoint
 */

const fetch = require('node-fetch');

async function testAuthVerify() {
    console.log('🧪 Testing Auth Service /auth/verify endpoint\n');
    console.log('=' .repeat(60));
    
    // Test 1: Without token (should fail)
    console.log('\n📋 Test 1: No Token');
    try {
        const response1 = await fetch('https://auth.neuralgrid.kr/auth/verify', {
            method: 'GET'
        });
        console.log('Status:', response1.status, response1.statusText);
        const data1 = await response1.text();
        console.log('Response:', data1.substring(0, 200));
    } catch (error) {
        console.error('❌ Error:', error.message);
    }
    
    // Test 2: With invalid token (should fail)
    console.log('\n📋 Test 2: Invalid Token');
    try {
        const response2 = await fetch('https://auth.neuralgrid.kr/auth/verify', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer invalid_token_here'
            }
        });
        console.log('Status:', response2.status, response2.statusText);
        const data2 = await response2.text();
        console.log('Response:', data2.substring(0, 200));
    } catch (error) {
        console.error('❌ Error:', error.message);
    }
    
    // Test 3: Endpoint test (GET vs POST)
    console.log('\n📋 Test 3: POST Method (wrong)');
    try {
        const response3 = await fetch('https://auth.neuralgrid.kr/auth/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token: 'test' })
        });
        console.log('Status:', response3.status, response3.statusText);
    } catch (error) {
        console.error('❌ Error:', error.message);
    }
    
    // Test 4: Old endpoint (should 404)
    console.log('\n📋 Test 4: Old Endpoint /api/auth/verify (should 404)');
    try {
        const response4 = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token: 'test' })
        });
        console.log('Status:', response4.status, response4.statusText);
    } catch (error) {
        console.error('❌ Error:', error.message);
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('\n✅ Test completed!');
    console.log('\nNext steps:');
    console.log('1. Deploy ddos-server-updated.js to production');
    console.log('2. Test with real user login token');
    console.log('3. Verify registration flow works without 401 errors\n');
}

testAuthVerify().catch(console.error);
