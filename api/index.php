<?php
/**
 * Vercel Serverless Function Dispatcher
 * Bridges serverless requests directly to the public Front Controller
 */

// Route to front controller
require_once __DIR__ . '/../public/index.php';
