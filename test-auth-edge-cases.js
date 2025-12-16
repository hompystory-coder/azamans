#!/usr/bin/env node

console.log('🔬 Testing Auth Edge Cases\n');

async function testEdgeCases() {
    const testCases = [
        { name: 'Empty string token', token: '' },
        { name: 'Null token', token: null },
        { name: 'Undefined token', token: undefined },
        { name: 'Malformed JWT (no dots)', token: 'invalidtoken' },
        { name: 'JWT with only header', token: 'eyJhbGci..' },
        { name: 'Very long token', token: 'x'.repeat(10000) },
        { name: 'Special characters', token: '<script>alert("xss")</script>' },
    ];
    
    for (const testCase of testCases) {
        try {
            console.log(`\n📝 Testing: ${testCase.name}`);
            console.log(`   Token: ${String(testCase.token).substring(0, 50)}...`);
            
            const body = testCase.token === undefined 
                ? '{}' 
                : JSON.stringify({ token: testCase.token });
            
            const response = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body
            });
            
            console.log(`   Status: ${response.status}`);
            console.log(`   Content-Type: ${response.headers.get('content-type')}`);
            
            const text = await response.text();
            
            // Check if it's HTML
            if (text.trim().startsWith('<')) {
                console.log('   ❌ GOT HTML RESPONSE!');
                console.log('   First 200 chars:', text.substring(0, 200));
            } else {
                try {
                    const json = JSON.parse(text);
                    console.log('   ✅ Got JSON:', json);
                } catch (e) {
                    console.log('   ⚠️ Not HTML, not JSON:', text.substring(0, 100));
                }
            }
        } catch (error) {
            console.log(`   ❌ Exception: ${error.message}`);
        }
    }
    
    console.log('\n\n🔍 Key Findings:');
    console.log('If any test returned HTML, that could explain the "Unexpected token \'<\'" error');
}

testEdgeCases();
