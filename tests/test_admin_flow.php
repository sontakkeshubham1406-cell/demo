<?php
// Test admin login flow

$cookieFile = __DIR__ . '/test_admin_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// 1. Post admin login
$ch = curl_init('http://127.0.0.1:8000/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'admin@velorisluxe.com',
    'password' => 'Admin@123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "1. Admin Login Attempt: HTTP {$code}\n";
curl_close($ch);

// 2. Access Admin Dashboard
$ch = curl_init('http://127.0.0.1:8000/admin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "2. GET /admin (Logged In Admin): HTTP {$code} (Expected: 200)\n";
curl_close($ch);

// 3. Access Admin Products
$ch = curl_init('http://127.0.0.1:8000/admin/products');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "3. GET /admin/products (Logged In Admin): HTTP {$code} (Expected: 200)\n";
curl_close($ch);

if (file_exists($cookieFile)) unlink($cookieFile);
echo "\nAdmin flow verification successfully completed!\n";

