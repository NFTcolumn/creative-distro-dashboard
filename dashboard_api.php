<?php
/**
 * Creative Distro Dashboard API
 * 
 * This file handles all API requests for the dashboard invite system.
 */

require_once 'dashboard_config.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove 'dashboard_api.php' from path if present
if (end($pathParts) === 'dashboard_api.php') {
    array_pop($pathParts);
}

$endpoint = isset($pathParts[0]) ? $pathParts[0] : '';
$action = isset($pathParts[1]) ? $pathParts[1] : '';

try {
    switch ($endpoint) {
        case 'auth':
            handleAuth($action, $method);
            break;
        case 'invites':
            handleInvites($action, $method);
            break;
        case 'network':
            handleNetwork($action, $method);
            break;
        case 'user':
            handleUser($action, $method);
            break;
        case 'stats':
            handleStats($action, $method);
            break;
        default:
            sendJsonResponse(['error' => 'Invalid endpoint'], 404);
    }
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Internal server error'], 500);
}

/**
 * Handle authentication endpoints
 */
function handleAuth($action, $method) {
    switch ($action) {
        case 'register':
            if ($method === 'POST') {
                registerUser();
            }
            break;
        case 'login':
            if ($method === 'POST') {
                loginUser();
            }
            break;
        case 'logout':
            if ($method === 'POST') {
                logoutUser();
            }
            break;
        case 'activate':
            if ($method === 'POST') {
                activateUser();
            }
            break;
        default:
            sendJsonResponse(['error' => 'Invalid auth action'], 404);
    }
}

/**
 * Handle invite endpoints
 */
function handleInvites($action, $method) {
    $user = requireAuth();
    
    switch ($action) {
        case 'send':
            if ($method === 'POST') {
                sendInvite($user);
            }
            break;
        case 'list':
            if ($method === 'GET') {
                getInvites($user);
            }
            break;
        case 'status':
            if ($method === 'GET') {
                getInviteStatus($user);
            }
            break;
        default:
            sendJsonResponse(['error' => 'Invalid invite action'], 404);
    }
}

/**
 * Handle network endpoints
 */
function handleNetwork($action, $method) {
    $user = requireAuth();
    
    switch ($action) {
        case 'tree':
            if ($method === 'GET') {
                getNetworkTree($user);
            }
            break;
        case 'stats':
            if ($method === 'GET') {
                getNetworkStats($user);
            }
            break;
        default:
            sendJsonResponse(['error' => 'Invalid network action'], 404);
    }
}

/**
 * Handle user endpoints
 */
function handleUser($action, $method) {
    $user = requireAuth();
    
    switch ($action) {
        case 'profile':
            if ($method === 'GET') {
                getUserProfile($user);
            } elseif ($method === 'PUT') {
                updateUserProfile($user);
            }
            break;
        case 'quota':
            if ($method === 'GET') {
                getInviteQuota($user);
            }
            break;
        default:
            sendJsonResponse(['error' => 'Invalid user action'], 404);
    }
}

/**
 * Handle stats endpoints
 */
function handleStats($action, $method) {
    $user = requireAuth();
    
    switch ($action) {
        case 'dashboard':
            if ($method === 'GET') {
                getDashboardStats($user);
            }
            break;
        default:
            sendJsonResponse(['error' => 'Invalid stats action'], 404);
    }
}

/**
 * Register a new user with invite code
 */
function registerUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $requiredFields = ['email', 'password', 'first_name', 'last_name', 'invite_code', 'tos_accepted'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendJsonResponse(['error' => "Field '$field' is required"], 400);
        }
    }
    
    if (!$input['tos_accepted']) {
        sendJsonResponse(['error' => 'Terms of Service must be accepted'], 400);
    }
    
    $pdo = getDashboardDB();
    
    // Check if invite code is valid
    $stmt = $pdo->prepare("SELECT * FROM invites WHERE invite_code = ? AND status = 'Pending' AND is_claimed = 0");
    $stmt->execute([$input['invite_code']]);
    $invite = $stmt->fetch();
    
    if (!$invite) {
        sendJsonResponse(['error' => 'Invalid or expired invite code'], 400);
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Email already registered'], 400);
    }
    
    $pdo->beginTransaction();
    
    try {
        // Create user
        $uuid = generateUUID();
        $referralCode = generateReferralCode();
        $hashedPassword = hashPassword($input['password']);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, email, password, first_name, last_name, referral_code, referred_by, tos_accepted, tos_accepted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $uuid,
            $input['email'],
            $hashedPassword,
            $input['first_name'],
            $input['last_name'],
            $referralCode,
            $invite['inviter_id']
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Update invite status
        $stmt = $pdo->prepare("UPDATE invites SET status = 'Joined', is_claimed = 1, claimed_at = NOW() WHERE id = ?");
        $stmt->execute([$invite['id']]);
        
        // Update inviter's quota and stats
        $stmt = $pdo->prepare("
            UPDATE users 
            SET invite_quota = invite_quota + ?, total_successful_invites = total_successful_invites + 1 
            WHERE id = ?
        ");
        $stmt->execute([BONUS_INVITES_PER_REFERRAL, $invite['inviter_id']]);
        
        // Build network relationships
        buildNetworkRelationships($userId, $invite['inviter_id'], $pdo);
        
        // Initialize network stats for new user
        $stmt = $pdo->prepare("INSERT INTO network_stats (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        
        // Create activation code
        $activationCode = generateSecureToken();
        $stmt = $pdo->prepare("
            INSERT INTO user_activations (user_id, activation_code, expires_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ");
        $stmt->execute([$userId, $activationCode]);
        
        $pdo->commit();
        
        // Send activation email (implement email sending here)
        sendActivationEmail($input['email'], $activationCode);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Registration successful. Please check your email for activation instructions.',
            'user_id' => $userId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Build network relationships for a new user
 */
function buildNetworkRelationships($newUserId, $referrerId, $pdo) {
    // Add direct relationship
    $stmt = $pdo->prepare("INSERT INTO user_network (ancestor_id, descendant_id, depth) VALUES (?, ?, 1)");
    $stmt->execute([$referrerId, $newUserId]);
    
    // Add relationships with all ancestors up to MAX_NETWORK_DEPTH
    $stmt = $pdo->prepare("
        INSERT INTO user_network (ancestor_id, descendant_id, depth)
        SELECT ancestor_id, ?, depth + 1
        FROM user_network
        WHERE descendant_id = ? AND depth < ?
    ");
    $stmt->execute([$newUserId, $referrerId, MAX_NETWORK_DEPTH]);
    
    // Update network stats for all ancestors
    updateNetworkStats($referrerId, $pdo);
}

/**
 * Update network statistics
 */
function updateNetworkStats($userId, $pdo) {
    $stmt = $pdo->prepare("
        UPDATE network_stats SET
            level_1_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 1),
            level_2_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 2),
            level_3_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 3),
            level_4_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 4),
            level_5_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 5),
            level_6_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 6),
            total_network_size = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ?)
        WHERE user_id = ?
    ");
    $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
    
    // Update stats for all ancestors
    $stmt = $pdo->prepare("SELECT DISTINCT ancestor_id FROM user_network WHERE descendant_id = ?");
    $stmt->execute([$userId]);
    $ancestors = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($ancestors as $ancestorId) {
        $stmt = $pdo->prepare("
            UPDATE network_stats SET
                level_1_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 1),
                level_2_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 2),
                level_3_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 3),
                level_4_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 4),
                level_5_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 5),
                level_6_count = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ? AND depth = 6),
                total_network_size = (SELECT COUNT(*) FROM user_network WHERE ancestor_id = ?)
            WHERE user_id = ?
        ");
        $stmt->execute([$ancestorId, $ancestorId, $ancestorId, $ancestorId, $ancestorId, $ancestorId, $ancestorId, $ancestorId]);
    }
}

/**
 * Send an invite
 */
function sendInvite($user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || empty($input['email'])) {
        sendJsonResponse(['error' => 'Email is required'], 400);
    }
    
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(['error' => 'Invalid email format'], 400);
    }
    
    if ($user['invite_quota'] <= 0) {
        sendJsonResponse(['error' => 'No invites remaining'], 400);
    }
    
    $pdo = getDashboardDB();
    
    // Check if email already invited or registered
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Email already registered'], 400);
    }
    
    $stmt = $pdo->prepare("SELECT id FROM invites WHERE invitee_email = ? AND status = 'Pending'");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Email already invited'], 400);
    }
    
    $pdo->beginTransaction();
    
    try {
        // Create invite
        $inviteCode = generateReferralCode();
        $inviteToken = generateSecureToken(64);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . INVITE_EXPIRY_DAYS . ' days'));
        
        $stmt = $pdo->prepare("
            INSERT INTO invites (inviter_id, invitee_email, invitee_name, invite_code, invite_token, expires_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            $input['email'],
            $input['name'] ?? null,
            $inviteCode,
            $inviteToken,
            $expiresAt
        ]);
        
        $inviteId = $pdo->lastInsertId();
        
        // Decrease user's invite quota
        $stmt = $pdo->prepare("
            UPDATE users 
            SET invite_quota = invite_quota - 1, total_invites_sent = total_invites_sent + 1 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        
        $pdo->commit();
        
        // Send invite email
        sendInviteEmail($input['email'], $inviteCode, $inviteToken, $user);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Invite sent successfully',
            'invite_id' => $inviteId,
            'invite_code' => $inviteCode
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Get user's invites
 */
function getInvites($user) {
    $pdo = getDashboardDB();
    
    $stmt = $pdo->prepare("
        SELECT id, invitee_email, invitee_name, status, invite_code, created_at, claimed_at, expires_at
        FROM invites 
        WHERE inviter_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $invites = $stmt->fetchAll();
    
    sendJsonResponse(['invites' => $invites]);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats($user) {
    $pdo = getDashboardDB();
    
    // Get network stats
    $stmt = $pdo->prepare("SELECT * FROM network_stats WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $networkStats = $stmt->fetch() ?: [
        'level_1_count' => 0,
        'level_2_count' => 0,
        'level_3_count' => 0,
        'level_4_count' => 0,
        'level_5_count' => 0,
        'level_6_count' => 0,
        'total_network_size' => 0
    ];
    
    // Get invite stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_sent,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Joined' THEN 1 ELSE 0 END) as joined,
            SUM(CASE WHEN status = 'Live' THEN 1 ELSE 0 END) as live
        FROM invites 
        WHERE inviter_id = ?
    ");
    $stmt->execute([$user['id']]);
    $inviteStats = $stmt->fetch();
    
    sendJsonResponse([
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'referral_code' => $user['referral_code'],
            'invite_quota' => $user['invite_quota'],
            'total_invites_sent' => $user['total_invites_sent'],
            'total_successful_invites' => $user['total_successful_invites']
        ],
        'network_stats' => $networkStats,
        'invite_stats' => $inviteStats
    ]);
}

/**
 * Send activation email (placeholder - implement with your email service)
 */
function sendActivationEmail($email, $activationCode) {
    $subject = "Activate your Creative Distro Dashboard account";
    $activationUrl = DASHBOARD_BASE_URL . "/activate.php?code=" . $activationCode;
    
    $message = "
    <html>
    <body>
        <h2>Welcome to Creative Distro Dashboard!</h2>
        <p>Please click the link below to activate your account:</p>
        <p><a href='$activationUrl'>Activate Account</a></p>
        <p>If the link doesn't work, copy and paste this URL into your browser:</p>
        <p>$activationUrl</p>
        <p>This link will expire in 24 hours.</p>
    </body>
    </html>
    ";
    
    // Log the email (implement actual email sending here)
    logEmail(null, null, 'activation', $email, $subject, 'sent');
}

/**
 * Send invite email (placeholder - implement with your email service)
 */
function sendInviteEmail($email, $inviteCode, $inviteToken, $inviter) {
    $subject = "You're invited to join Creative Distro Dashboard";
    $joinUrl = DASHBOARD_BASE_URL . "/join.php?code=" . $inviteCode . "&token=" . $inviteToken;
    
    $message = "
    <html>
    <body>
        <h2>You're invited to Creative Distro Dashboard!</h2>
        <p>{$inviter['first_name']} {$inviter['last_name']} has invited you to join Creative Distro Dashboard.</p>
        <p>Click the link below to create your account:</p>
        <p><a href='$joinUrl'>Join Now</a></p>
        <p>If the link doesn't work, copy and paste this URL into your browser:</p>
        <p>$joinUrl</p>
        <p>Your invite code is: <strong>$inviteCode</strong></p>
    </body>
    </html>
    ";
    
    // Log the email (implement actual email sending here)
    logEmail($inviter['id'], null, 'invite', $email, $subject, 'sent');
}

/**
 * Login user
 */
function loginUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password'])) {
        sendJsonResponse(['error' => 'Email and password are required'], 400);
    }
    
    $pdo = getDashboardDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_activated = 1");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch();
    
    if (!$user || !verifyPassword($input['password'], $user['password'])) {
        sendJsonResponse(['error' => 'Invalid credentials'], 401);
    }
    
    session_start();
    $_SESSION['dashboard_user_id'] = $user['id'];
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'referral_code' => $user['referral_code']
        ]
    ]);
}

/**
 * Logout user
 */
function logoutUser() {
    session_start();
    session_destroy();
    sendJsonResponse(['success' => true, 'message' => 'Logout successful']);
}

/**
 * Activate user account
 */
function activateUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['activation_code'])) {
        sendJsonResponse(['error' => 'Activation code is required'], 400);
    }
    
    $pdo = getDashboardDB();
    
    $stmt = $pdo->prepare("
        SELECT ua.*, u.id as user_id 
        FROM user_activations ua 
        JOIN users u ON ua.user_id = u.id 
        WHERE ua.activation_code = ? AND ua.is_used = 0 AND ua.expires_at > NOW()
    ");
    $stmt->execute([$input['activation_code']]);
    $activation = $stmt->fetch();
    
    if (!$activation) {
        sendJsonResponse(['error' => 'Invalid or expired activation code'], 400);
    }
    
    $pdo->beginTransaction();
    
    try {
        // Mark activation as used
        $stmt = $pdo->prepare("UPDATE user_activations SET is_used = 1 WHERE id = ?");
        $stmt->execute([$activation['id']]);
        
        // Activate user
        $stmt = $pdo->prepare("UPDATE users SET is_activated = 1 WHERE id = ?");
        $stmt->execute([$activation['user_id']]);
        
        $pdo->commit();
        
        sendJsonResponse(['success' => true, 'message' => 'Account activated successfully']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>
