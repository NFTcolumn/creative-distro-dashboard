<?php
/**
 * Email Templates for Creative Distro Dashboard
 * This file contains all email templates and sending functions
 */

require_once 'dashboard_config.php';

/**
 * Send email using Zoho SMTP configuration
 */
function sendEmail($to, $subject, $htmlBody, $textBody = null) {
    // If no text body provided, create a simple version from HTML
    if ($textBody === null) {
        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
    }
    
    // Configure SMTP settings for Hostinger
    ini_set('SMTP', SMTP_HOST);
    ini_set('smtp_port', SMTP_PORT);
    ini_set('sendmail_from', SMTP_FROM_EMAIL);
    
    // Email headers with proper authentication
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">";
    $headers[] = "Reply-To: " . SMTP_FROM_EMAIL;
    $headers[] = "Return-Path: " . SMTP_FROM_EMAIL;
    $headers[] = "X-Mailer: Creative Distro Dashboard";
    $headers[] = "X-Priority: 3";
    $headers[] = "Message-ID: <" . time() . "." . uniqid() . "@creativedistro.com>";
    
    // Add authentication headers for better delivery
    $headers[] = "X-Sender: " . SMTP_FROM_EMAIL;
    $headers[] = "X-Original-Sender: " . SMTP_FROM_EMAIL;
    
    $headerString = implode("\r\n", $headers);
    
    // Send email
    $success = mail($to, $subject, $htmlBody, $headerString);
    
    // Log email attempt (only if database is available)
    try {
        logEmail(null, null, 'system', $to, $subject, $success ? 'sent' : 'failed');
    } catch (Exception $e) {
        // Silently fail if database logging fails
        error_log("Email logging failed: " . $e->getMessage());
    }
    
    return $success;
}

/**
 * Send invite email
 */
function sendInviteEmail($inviterName, $inviteCode, $recipientEmail) {
    $inviteLink = DASHBOARD_BASE_URL . "/join?code=" . urlencode($inviteCode);
    
    $subject = "You're invited to join Creative Distro!";
    
    $htmlBody = getInviteEmailTemplate($inviterName, $inviteLink, $inviteCode);
    
    return sendEmail($recipientEmail, $subject, $htmlBody);
}

/**
 * Send welcome email after registration
 */
function sendWelcomeEmail($userName, $userEmail) {
    $subject = "Welcome to Creative Distro Dashboard!";
    
    $htmlBody = getWelcomeEmailTemplate($userName);
    
    return sendEmail($userEmail, $subject, $htmlBody);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($userName, $userEmail, $resetToken) {
    $resetLink = DASHBOARD_BASE_URL . "/reset-password?token=" . urlencode($resetToken);
    
    $subject = "Reset your Creative Distro password";
    
    $htmlBody = getPasswordResetEmailTemplate($userName, $resetLink);
    
    return sendEmail($userEmail, $subject, $htmlBody);
}

/**
 * Get invite email template
 */
function getInviteEmailTemplate($inviterName, $inviteLink, $inviteCode) {
    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You\'re Invited to Creative Distro</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .invite-code { background: #fff; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 You\'re Invited!</h1>
        <p>Join the Creative Distro community</p>
    </div>
    
    <div class="content">
        <p>Hello!</p>
        
        <p><strong>' . htmlspecialchars($inviterName) . '</strong> has invited you to join <strong>Creative Distro</strong> - an exclusive community for creative professionals and entrepreneurs.</p>
        
        <div class="invite-code">
            <h3>Your Invite Code:</h3>
            <h2 style="color: #667eea; font-size: 24px; margin: 10px 0;">' . htmlspecialchars($inviteCode) . '</h2>
        </div>
        
        <p>Click the button below to accept your invitation and create your account:</p>
        
        <div style="text-align: center;">
            <a href="' . htmlspecialchars($inviteLink) . '" class="button">Accept Invitation</a>
        </div>
        
        <p><strong>What you\'ll get access to:</strong></p>
        <ul>
            <li>🎨 Exclusive creative resources and tools</li>
            <li>🤝 Network with like-minded professionals</li>
            <li>📈 Track your community growth and impact</li>
            <li>🎁 Earn rewards for growing the network</li>
        </ul>
        
        <p>This invitation is exclusive and limited. Don\'t miss out on joining this amazing community!</p>
        
        <p>If the button doesn\'t work, copy and paste this link into your browser:<br>
        <a href="' . htmlspecialchars($inviteLink) . '">' . htmlspecialchars($inviteLink) . '</a></p>
    </div>
    
    <div class="footer">
        <p>This invitation was sent by ' . htmlspecialchars($inviterName) . ' through Creative Distro.<br>
        If you don\'t want to receive these emails, you can ignore this message.</p>
        
        <p><a href="' . MAIN_SITE_URL . '">Visit Creative Distro</a> | <a href="' . DASHBOARD_BASE_URL . '">Dashboard</a></p>
    </div>
</body>
</html>';
}

/**
 * Get welcome email template
 */
function getWelcomeEmailTemplate($userName) {
    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Creative Distro</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
        .feature-box { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #667eea; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Welcome to Creative Distro!</h1>
        <p>Your journey starts here</p>
    </div>
    
    <div class="content">
        <p>Hi ' . htmlspecialchars($userName) . ',</p>
        
        <p>Welcome to the <strong>Creative Distro</strong> community! We\'re thrilled to have you on board.</p>
        
        <div class="feature-box">
            <h3>🚀 Getting Started</h3>
            <p>Your account is now active and ready to use. You can start inviting others to grow your network and earn rewards.</p>
        </div>
        
        <div class="feature-box">
            <h3>🎁 Your Invite Quota</h3>
            <p>You start with <strong>5 invitations</strong> to share with friends and colleagues. Each successful referral earns you bonus invites!</p>
        </div>
        
        <div class="feature-box">
            <h3>📊 Track Your Impact</h3>
            <p>Use your dashboard to monitor your network growth, track referrals, and see your community impact in real-time.</p>
        </div>
        
        <div style="text-align: center;">
            <a href="' . DASHBOARD_BASE_URL . '" class="button">Go to Dashboard</a>
        </div>
        
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Complete your profile in the dashboard</li>
            <li>Start inviting friends and colleagues</li>
            <li>Explore the community features</li>
            <li>Connect with other members</li>
        </ol>
        
        <p>If you have any questions or need help getting started, don\'t hesitate to reach out to our support team.</p>
        
        <p>Welcome aboard!</p>
        <p><strong>The Creative Distro Team</strong></p>
    </div>
    
    <div class="footer">
        <p><a href="' . MAIN_SITE_URL . '">Visit Creative Distro</a> | <a href="' . DASHBOARD_BASE_URL . '">Dashboard</a></p>
        <p>© ' . date('Y') . ' Creative Distro. All rights reserved.</p>
    </div>
</body>
</html>';
}

/**
 * Get password reset email template
 */
function getPasswordResetEmailTemplate($userName, $resetLink) {
    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔒 Password Reset</h1>
        <p>Reset your Creative Distro password</p>
    </div>
    
    <div class="content">
        <p>Hi ' . htmlspecialchars($userName) . ',</p>
        
        <p>We received a request to reset your password for your Creative Distro account.</p>
        
        <div style="text-align: center;">
            <a href="' . htmlspecialchars($resetLink) . '" class="button">Reset Password</a>
        </div>
        
        <div class="warning">
            <strong>Security Notice:</strong> This link will expire in 1 hour for your security. If you didn\'t request this reset, please ignore this email.
        </div>
        
        <p>If the button doesn\'t work, copy and paste this link into your browser:<br>
        <a href="' . htmlspecialchars($resetLink) . '">' . htmlspecialchars($resetLink) . '</a></p>
        
        <p>If you didn\'t request this password reset, please ignore this email. Your password will remain unchanged.</p>
        
        <p><strong>The Creative Distro Team</strong></p>
    </div>
    
    <div class="footer">
        <p><a href="' . MAIN_SITE_URL . '">Visit Creative Distro</a> | <a href="' . DASHBOARD_BASE_URL . '">Dashboard</a></p>
        <p>© ' . date('Y') . ' Creative Distro. All rights reserved.</p>
    </div>
</body>
</html>';
}

/**
 * Test email sending function
 */
function testEmailSystem($testEmail) {
    $subject = "Creative Distro Dashboard - Email System Test";
    $htmlBody = '
    <h2>Email System Test</h2>
    <p>This is a test email from your Creative Distro Dashboard.</p>
    <p><strong>SMTP Configuration:</strong></p>
    <ul>
        <li>Host: ' . SMTP_HOST . '</li>
        <li>Port: ' . SMTP_PORT . '</li>
        <li>From: ' . SMTP_FROM_EMAIL . '</li>
    </ul>
    <p>If you received this email, your Zoho SMTP configuration is working correctly!</p>
    <p><em>Sent at: ' . date('Y-m-d H:i:s') . '</em></p>
    ';
    
    return sendEmail($testEmail, $subject, $htmlBody);
}
?>
