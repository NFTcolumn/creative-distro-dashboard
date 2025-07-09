<?php
/**
 * SMTP Test Script for Creative Distro Dashboard
 * This script tests the Zoho SMTP configuration
 */

// Load environment variables
require_once 'env-loader.php';

// Simple SMTP test function
function testSMTPConnection() {
    $smtp_host = getenv('SMTP_HOST');
    $smtp_port = getenv('SMTP_PORT');
    $smtp_username = getenv('SMTP_USERNAME');
    $smtp_password = getenv('SMTP_PASSWORD');
    $smtp_from_email = getenv('SMTP_FROM_EMAIL');
    $smtp_from_name = getenv('SMTP_FROM_NAME');
    
    echo "<h2>SMTP Configuration Test</h2>\n";
    echo "<p><strong>Host:</strong> " . htmlspecialchars($smtp_host) . "</p>\n";
    echo "<p><strong>Port:</strong> " . htmlspecialchars($smtp_port) . "</p>\n";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($smtp_username) . "</p>\n";
    echo "<p><strong>From Email:</strong> " . htmlspecialchars($smtp_from_email) . "</p>\n";
    echo "<p><strong>From Name:</strong> " . htmlspecialchars($smtp_from_name) . "</p>\n";
    
    // Test socket connection
    echo "<h3>Testing Socket Connection...</h3>\n";
    $socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
    
    if ($socket) {
        echo "<p style='color: green;'>✓ Successfully connected to $smtp_host:$smtp_port</p>\n";
        
        // Read server response
        $response = fgets($socket, 256);
        echo "<p><strong>Server Response:</strong> " . htmlspecialchars(trim($response)) . "</p>\n";
        
        // Send EHLO command
        fwrite($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
        $response = fgets($socket, 256);
        echo "<p><strong>EHLO Response:</strong> " . htmlspecialchars(trim($response)) . "</p>\n";
        
        // Test STARTTLS
        fwrite($socket, "STARTTLS\r\n");
        $response = fgets($socket, 256);
        echo "<p><strong>STARTTLS Response:</strong> " . htmlspecialchars(trim($response)) . "</p>\n";
        
        fclose($socket);
    } else {
        echo "<p style='color: red;'>✗ Failed to connect to $smtp_host:$smtp_port</p>\n";
        echo "<p><strong>Error:</strong> $errstr ($errno)</p>\n";
    }
}

// Test email sending function using PHP's mail() with custom headers
function testEmailSending($test_email = null) {
    if (!$test_email) {
        echo "<h3>Email Test Skipped</h3>\n";
        echo "<p>To test email sending, add ?test_email=your@email.com to the URL</p>\n";
        return;
    }
    
    echo "<h3>Testing Email Sending...</h3>\n";
    echo "<p><strong>Testing email to:</strong> " . htmlspecialchars($test_email) . "</p>\n";
    
    // Check PHP mail configuration
    echo "<h4>PHP Mail Configuration:</h4>\n";
    echo "<ul>\n";
    echo "<li><strong>mail() function available:</strong> " . (function_exists('mail') ? 'Yes' : 'No') . "</li>\n";
    echo "<li><strong>sendmail_path:</strong> " . htmlspecialchars(ini_get('sendmail_path')) . "</li>\n";
    echo "<li><strong>SMTP (ini):</strong> " . htmlspecialchars(ini_get('SMTP')) . "</li>\n";
    echo "<li><strong>smtp_port (ini):</strong> " . htmlspecialchars(ini_get('smtp_port')) . "</li>\n";
    echo "<li><strong>sendmail_from (ini):</strong> " . htmlspecialchars(ini_get('sendmail_from')) . "</li>\n";
    echo "</ul>\n";
    
    // Test with enhanced email template system
    echo "<h4>Testing with Enhanced Email System:</h4>\n";
    try {
        require_once 'email_templates.php';
        
        $success = testEmailSystem($test_email);
        
        if ($success) {
            echo "<p style='color: green;'>✓ Enhanced email system test successful</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Enhanced email system test failed</p>\n";
            
            // Get last error
            $error = error_get_last();
            if ($error && strpos($error['message'], 'mail') !== false) {
                echo "<p><strong>Last PHP Error:</strong> " . htmlspecialchars($error['message']) . "</p>\n";
                echo "<p><strong>Error File:</strong> " . htmlspecialchars($error['file']) . ":" . $error['line'] . "</p>\n";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Email template system error</p>\n";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>\n";
    }
    
    // Test basic PHP mail() function
    echo "<h4>Testing Basic PHP mail() Function:</h4>\n";
    
    $to = $test_email;
    $subject = "Creative Distro Dashboard - Basic SMTP Test";
    $message = "This is a basic test email from your Creative Distro Dashboard.\n\n";
    $message .= "If you received this email, your basic PHP mail configuration is working!\n\n";
    $message .= "Sent at: " . date('Y-m-d H:i:s') . "\n";
    
    $headers = "From: " . getenv('SMTP_FROM_NAME') . " <" . getenv('SMTP_FROM_EMAIL') . ">\r\n";
    $headers .= "Reply-To: " . getenv('SMTP_FROM_EMAIL') . "\r\n";
    $headers .= "X-Mailer: Creative Distro Dashboard\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    echo "<p><strong>Email Headers:</strong></p>\n";
    echo "<pre>" . htmlspecialchars($headers) . "</pre>\n";
    
    // Clear any previous errors
    error_clear_last();
    
    $result = mail($to, $subject, $message, $headers);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Basic mail() function returned success</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Basic mail() function returned failure</p>\n";
        
        // Check for errors
        $error = error_get_last();
        if ($error) {
            echo "<p><strong>Last Error:</strong> " . htmlspecialchars($error['message']) . "</p>\n";
            echo "<p><strong>Error Type:</strong> " . $error['type'] . "</p>\n";
            echo "<p><strong>Error File:</strong> " . htmlspecialchars($error['file']) . ":" . $error['line'] . "</p>\n";
        }
    }
    
    // Additional diagnostics
    echo "<h4>Additional Diagnostics:</h4>\n";
    echo "<ul>\n";
    echo "<li><strong>Server Software:</strong> " . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>\n";
    echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>\n";
    echo "<li><strong>Operating System:</strong> " . php_uname() . "</li>\n";
    echo "<li><strong>Current Time:</strong> " . date('Y-m-d H:i:s T') . "</li>\n";
    echo "</ul>\n";
}

// Database connection test
function testDatabaseConnection() {
    echo "<h3>Testing Database Connection...</h3>\n";
    
    // Show configuration being used
    echo "<p><strong>Database Configuration:</strong></p>\n";
    echo "<ul>\n";
    echo "<li><strong>Host:</strong> " . htmlspecialchars(getenv('DASHBOARD_DB_HOST')) . "</li>\n";
    echo "<li><strong>Database:</strong> " . htmlspecialchars(getenv('DASHBOARD_DB_NAME')) . "</li>\n";
    echo "<li><strong>User:</strong> " . htmlspecialchars(getenv('DASHBOARD_DB_USER')) . "</li>\n";
    echo "<li><strong>Password:</strong> " . (getenv('DASHBOARD_DB_PASS') ? '[SET]' : '[NOT SET]') . "</li>\n";
    echo "</ul>\n";
    
    try {
        require_once 'dashboard_config.php';
        
        // Test basic PDO connection
        $dsn = "mysql:host=" . getenv('DASHBOARD_DB_HOST') . ";dbname=" . getenv('DASHBOARD_DB_NAME') . ";charset=utf8mb4";
        echo "<p><strong>DSN:</strong> " . htmlspecialchars($dsn) . "</p>\n";
        
        $pdo = new PDO($dsn, getenv('DASHBOARD_DB_USER'), getenv('DASHBOARD_DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        echo "<p style='color: green;'>✓ Database connection successful</p>\n";
        
        // Test server info
        $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        echo "<p><strong>MySQL Version:</strong> " . htmlspecialchars($version) . "</p>\n";
        
        // Test if tables exist
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>Tables found:</strong> " . count($tables) . "</p>\n";
        if (count($tables) > 0) {
            echo "<ul>\n";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
        // Test users table if it exists
        if (in_array('users', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            echo "<p><strong>Users in database:</strong> " . $result['count'] . "</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠ Users table not found - database may need to be initialized</p>\n";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ PDO Database connection failed</p>\n";
        echo "<p><strong>Error Code:</strong> " . htmlspecialchars($e->getCode()) . "</p>\n";
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<p><strong>Error File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ General database error</p>\n";
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<p><strong>Error File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>\n";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Distro Dashboard - Configuration Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        h2, h3 {
            color: #555;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Creative Distro Dashboard - Configuration Test</h1>
        
        <div class="warning">
            <strong>Security Notice:</strong> This test file should be deleted after testing is complete.
        </div>
        
        <?php
        testDatabaseConnection();
        testSMTPConnection();
        
        $test_email = isset($_GET['test_email']) ? $_GET['test_email'] : null;
        testEmailSending($test_email);
        ?>
        
        <div class="success">
            <h3>Next Steps:</h3>
            <ol>
                <li>If all tests pass, your configuration is ready for deployment</li>
                <li>Upload all files to your Hostinger subdomain directory</li>
                <li>Test the live dashboard at https://dash.creativedistro.com</li>
                <li><strong>Delete this test file (test_smtp.php) after testing</strong></li>
            </ol>
        </div>
        
        <p><small>Test performed at: <?php echo date('Y-m-d H:i:s'); ?></small></p>
    </div>
</body>
</html>
