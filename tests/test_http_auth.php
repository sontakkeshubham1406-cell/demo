<?php
// HTTP test against running server http://127.0.0.1:8000

function httpGet($url, $cookies = '') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'header' => $header, 'body' => $body];
}

function httpPost($url, $fields = [], $cookies = '', $isJson = false) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_POST, true);
    if ($isJson) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    }
    if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'header' => $header, 'body' => $body];
}

echo "=== TESTING STOREFRONT AUTHENTICATION ENFORCEMENT ===\n";

// 1. GET /checkout without auth
$r1 = httpGet('http://127.0.0.1:8000/checkout');
echo "1. GET /checkout (guest): HTTP {$r1['code']}\n";
preg_match('/Location:\s*([^\r\n]+)/i', $r1['header'], $m1);
echo "   Redirect Location: " . ($m1[1] ?? 'None') . "\n";

// 2. POST /cart/add without auth
$r2 = httpPost('http://127.0.0.1:8000/cart/add', ['variant_id' => 1, 'quantity' => 1]);
echo "2. POST /cart/add (guest): HTTP {$r2['code']}\n";
preg_match('/Location:\s*([^\r\n]+)/i', $r2['header'], $m2);
echo "   Redirect Location: " . ($m2[1] ?? 'None') . "\n";

// 3. POST /api/checkout/create-order without auth
$r3 = httpPost('http://127.0.0.1:8000/api/checkout/create-order', ['name' => 'Test'], '', true);
echo "3. POST /api/checkout/create-order (guest): HTTP {$r3['code']}\n";
echo "   Response: {$r3['body']}\n";

// 4. GET /admin without auth
$r4 = httpGet('http://127.0.0.1:8000/admin');
echo "4. GET /admin (guest): HTTP {$r4['code']}\n";
preg_match('/Location:\s*([^\r\n]+)/i', $r4['header'], $m4);
echo "   Redirect Location: " . ($m4[1] ?? 'None') . "\n";

// 5. GET /admin/login
$r5 = httpGet('http://127.0.0.1:8000/admin/login');
echo "5. GET /admin/login: HTTP {$r5['code']}\n";

echo "\nAll HTTP tests completed!\n";

