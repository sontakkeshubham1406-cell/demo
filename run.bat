@echo off
title DEMO - Clothing eCommerce Platform
echo ============================================================
echo   DEMO - Complete Clothing eCommerce Platform
echo   Production-Ready PHP + Tailwind CSS + Razorpay Gateway
echo ============================================================
echo.
echo Initializing Local Web Server at: http://127.0.0.1:8000
echo Storefront URL:  http://127.0.0.1:8000/
echo Admin Panel URL: http://127.0.0.1:8000/admin (admin@demo.com / Admin@123)
echo.
echo Press Ctrl+C at any time to shut down the server.
echo.

"C:\Users\Solv-IT\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" -S 127.0.0.1:8000 -t public
pause

