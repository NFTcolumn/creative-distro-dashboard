<?php
/**
 * Creative Distro Dashboard API Entry Point
 * This file serves as the main entry point for the Render deployment
 */

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: https://creative-distro-dashboard.netlify.app');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Route all requests to the dashboard API
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove leading slash and check if it's an API request
$path = ltrim($path, '/');

// Special handling for init_admin.php
if ($path === 'init_admin.php') {
    require_once 'init_admin.php';
    exit;
}

// If it's a direct API call or starts with dashboard_api.php, route to the API
if ($path === '' || $path === 'dashboard_api.php' || strpos($path, 'dashboard_api.php') !== false) {
    require_once 'dashboard_api.php';
} else {
    // For any other path, also route to the API (since this is an API-only backend)
    require_once 'dashboard_api.php';
}
?>
