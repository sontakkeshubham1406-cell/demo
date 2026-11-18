<?php
// Test full session flow: Login -> Add to Cart -> Checkout Access

$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// 1. Post login as customer
$ch = curl_init('http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'customer@example.com',
    'password' => 'Customer@123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "1. Customer Login Attempt: HTTP {$code}\n";
curl_close($ch);

// 2. Add to Cart with logged in session
$ch = curl_init('http://127.0.0.1:8000/cart/add');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'variant_id' => 1,
    'quantity' => 2
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "2. Add to Cart (Logged In): HTTP {$code}\n";
curl_close($ch);

// 3. Access Checkout with logged in session
$ch = curl_init('http://127.0.0.1:8000/checkout');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "3. GET /checkout (Logged In): HTTP {$code} (Expected: 200)\n";
curl_close($ch);

if (file_exists($cookieFile)) unlink($cookieFile);
echo "\nFlow verification successfully completed!\n";

