<?php
/**
 * Creative Distro Dashboard Configuration
 * 
 * This file contains all configuration settings for the dashboard system.
 */

// Load environment variables
require_once 'env-loader.php';

// Database Configuration
define('DASHBOARD_DB_HOST', getenv('DASHBOARD_DB_HOST') ?: 'localhost');
define('DASHBOARD_DB_NAME', getenv('DASHBOARD_DB_NAME') ?: 'creative_distro_dashboard');
define('DASHBOARD_DB_USER', getenv('DASHBOARD_DB_USER') ?: 'root');
define('DASHBOARD_DB_PASS', getenv('DASHBOARD_DB_PASS') ?: '');
define('DASHBOARD_DB_PORT', getenv('DASHBOARD_DB_PORT') ?: '3306');
define('DASHBOARD_DB_CHARSET', 'utf8mb4');
define('DATABASE_TYPE', getenv('DATABASE_TYPE') ?: 'mysql'); // mysql or pgsql

// Application Configuration
define('DASHBOARD_BASE_URL', getenv('DASHBOARD_BASE_URL') ?: 'https://creative-distro-dashboard.netlify.app');
define('MAIN_SITE_URL', getenv('MAIN_SITE_URL') ?: 'https://creativedistro.com');

// Invite System Configuration
define('DEFAULT_INVITE_QUOTA', 5);
define('BONUS_INVITES_PER_REFERRAL', 2);
define('MAX_NETWORK_DEPTH', 6);
define('INVITE_EXPIRY_DAYS', 30);

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Email Configuration
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'localhost');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'noreply@creativedistro.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Creative Distro Dashboard');

// Development/Debug Settings
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'INFO');

/**
 * Get database connection for dashboard
 */
function getDashboardDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // Build DSN based on database type
            if (DATABASE_TYPE === 'pgsql') {
                $dsn = "pgsql:host=" . DASHBOARD_DB_HOST . ";port=" . DASHBOARD_DB_PORT . ";dbname=" . DASHBOARD_DB_NAME;
            } else {
                $dsn = "mysql:host=" . DASHBOARD_DB_HOST . ";port=" . DASHBOARD_DB_PORT . ";dbname=" . DASHBOARD_DB_NAME . ";charset=" . DASHBOARD_DB_CHARSET;
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DASHBOARD_DB_USER, DASHBOARD_DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Dashboard Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    return $pdo;
}

/**
 * Require authentication and return user data
 */
function requireAuth() {
    session_start();
    
    if (!isset($_SESSION['dashboard_user_id'])) {
        sendJsonResponse(['error' => 'Authentication required'], 401);
    }
    
    $pdo = getDashboardDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_activated = 1");
    $stmt->execute([$_SESSION['dashboard_user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        sendJsonResponse(['error' => 'Invalid session'], 401);
    }
    
    return $user;
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Generate a UUID
 */
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Generate a referral code
 */
function generateReferralCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $code;
}

/**
 * Generate a secure token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash a password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log email activity
 */
function logEmail($senderId, $recipientId, $type, $recipientEmail, $subject, $status) {
    try {
        $pdo = getDashboardDB();
        $stmt = $pdo->prepare("
            INSERT INTO email_logs (sender_id, recipient_id, type, recipient_email, subject, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$senderId, $recipientId, $type, $recipientEmail, $subject, $status]);
    } catch (Exception $e) {
        error_log("Email logging error: " . $e->getMessage());
    }
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user has reached invite quota
 */
function hasInviteQuota($userId) {
    $pdo = getDashboardDB();
    $stmt = $pdo->prepare("SELECT invite_quota FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    return $user && $user['invite_quota'] > 0;
}

/**
 * Get user's network statistics
 */
function getUserNetworkStats($userId) {
    $pdo = getDashboardDB();
    $stmt = $pdo->prepare("SELECT * FROM network_stats WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: [
        'level_1_count' => 0,
        'level_2_count' => 0,
        'level_3_count' => 0,
        'level_4_count' => 0,
        'level_5_count' => 0,
        'level_6_count' => 0,
        'total_network_size' => 0
    ];
}

/**
 * Rate limiting function
 */
function checkRateLimit($identifier, $maxAttempts = 10, $timeWindow = 3600) {
    $pdo = getDashboardDB();
    
    // Clean old attempts
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE identifier = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$identifier, $timeWindow]);
    
    // Count current attempts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE identifier = ?");
    $stmt->execute([$identifier]);
    $attempts = $stmt->fetchColumn();
    
    if ($attempts >= $maxAttempts) {
        return false;
    }
    
    // Log this attempt
    $stmt = $pdo->prepare("INSERT INTO rate_limits (identifier, created_at) VALUES (?, NOW())");
    $stmt->execute([$identifier]);
    
    return true;
}

/**
 * Debug logging function
 */
function debugLog($message, $data = null) {
    if (DEBUG_MODE) {
        $logMessage = date('Y-m-d H:i:s') . " - " . $message;
        if ($data !== null) {
            $logMessage .= " - " . json_encode($data);
        }
        error_log($logMessage);
    }
}
?>
