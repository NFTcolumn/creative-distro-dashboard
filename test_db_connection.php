<?php
// Test Database Connection for Creative Distro Dashboard
// This script tests the database connection using the .env configuration

require_once 'env-loader.php';
require_once 'dashboard_config.php';

echo "<h2>Creative Distro Dashboard - Database Connection Test</h2>\n";
echo "<p>Testing connection to database: " . $_ENV['DASHBOARD_DB_NAME'] . "</p>\n";

try {
    $pdo = getDashboardDB();
    echo "<p style='color: green;'>✅ Database connection successful!</p>\n";
    
    // Test if tables exist
    $tables = ['users', 'invites', 'user_network', 'user_activations', 'network_stats', 'email_logs'];
    echo "<h3>Checking Database Tables:</h3>\n";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✅ Table '$table' exists</p>\n";
            } else {
                echo "<p style='color: red;'>❌ Table '$table' does not exist</p>\n";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error checking table '$table': " . $e->getMessage() . "</p>\n";
        }
    }
    
    // Check if admin user exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@creativedistro.com'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            echo "<p style='color: green;'>✅ Admin user exists</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠️ Admin user does not exist (will be created when database is imported)</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error checking admin user: " . $e->getMessage() . "</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>\n";
    echo "<p>Please check your database credentials in the .env file.</p>\n";
}

echo "<hr>\n";
echo "<h3>Current Database Configuration:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Host:</strong> " . $_ENV['DASHBOARD_DB_HOST'] . "</li>\n";
echo "<li><strong>Database:</strong> " . $_ENV['DASHBOARD_DB_NAME'] . "</li>\n";
echo "<li><strong>Username:</strong> " . $_ENV['DASHBOARD_DB_USER'] . "</li>\n";
echo "<li><strong>Password:</strong> " . (empty($_ENV['DASHBOARD_DB_PASS']) ? 'Not set' : 'Set (hidden)') . "</li>\n";
echo "</ul>\n";
?>
