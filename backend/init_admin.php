<?php
/**
 * Initialize Admin User for Creative Distro Dashboard
 * This script creates the admin user if it doesn't exist
 */

// Set headers to prevent API routing
header('Content-Type: text/html; charset=utf-8');

require_once 'dashboard_config.php';

echo "<h2>Creative Distro Dashboard - Admin User Initialization</h2>\n";

try {
    $pdo = getDashboardDB();
    echo "<p style='color: green;'>✅ Database connection successful!</p>\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@creativedistro.com'");
    $stmt->execute();
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "<p style='color: green;'>✅ Admin user already exists</p>\n";
        echo "<p><strong>Email:</strong> admin@creativedistro.com</p>\n";
        echo "<p><strong>Referral Code:</strong> " . $adminUser['referral_code'] . "</p>\n";
        echo "<p><strong>Invite Quota:</strong> " . $adminUser['invite_quota'] . "</p>\n";
        echo "<p><strong>Is Activated:</strong> " . ($adminUser['is_activated'] ? 'Yes' : 'No') . "</p>\n";
        
        // Test password
        if (password_verify('admin123', $adminUser['password_hash'])) {
            echo "<p style='color: green;'>✅ Password verification successful</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Password verification failed - updating password</p>\n";
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@creativedistro.com'");
            $stmt->execute([$newHash]);
            echo "<p style='color: green;'>✅ Password updated successfully</p>\n";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Admin user does not exist. Creating...</p>\n";
        
        // Create admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $referralCode = 'ADMIN001';
        
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, referral_code, is_activated, invite_quota, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            'admin@creativedistro.com',
            $hashedPassword,
            'Admin',
            'User',
            $referralCode,
            1, // is_activated = true
            100 // invite_quota
        ]);
        
        if ($result) {
            $adminId = $pdo->lastInsertId();
            
            // Initialize network stats for admin
            $stmt = $pdo->prepare("
                INSERT INTO network_stats (user_id, level_1_count, level_2_count, level_3_count, level_4_count, level_5_count, level_6_count, total_network_size)
                VALUES (?, 0, 0, 0, 0, 0, 0, 0)
            ");
            $stmt->execute([$adminId]);
            
            echo "<p style='color: green;'>✅ Admin user created successfully!</p>\n";
            echo "<p><strong>Email:</strong> admin@creativedistro.com</p>\n";
            echo "<p><strong>Password:</strong> admin123</p>\n";
            echo "<p><strong>Referral Code:</strong> " . $referralCode . "</p>\n";
            echo "<p><strong>Invite Quota:</strong> 100</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin user</p>\n";
        }
    }
    
    // Test login functionality
    echo "<hr>\n";
    echo "<h3>Testing Login Functionality:</h3>\n";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@creativedistro.com' AND is_activated = TRUE");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify('admin123', $user['password_hash'])) {
        echo "<p style='color: green;'>✅ Login test successful - credentials are valid</p>\n";
        echo "<p>You can now log in to the dashboard with:</p>\n";
        echo "<ul>\n";
        echo "<li><strong>Email:</strong> admin@creativedistro.com</li>\n";
        echo "<li><strong>Password:</strong> admin123</li>\n";
        echo "</ul>\n";
    } else {
        echo "<p style='color: red;'>❌ Login test failed - credentials are invalid</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>\n";
}

echo "<hr>\n";
echo "<p><a href='https://creative-distro-dash.netlify.app/login.html'>Go to Dashboard Login</a></p>\n";
?>
