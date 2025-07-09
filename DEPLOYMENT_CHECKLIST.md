# Creative Distro Dashboard - Hostinger Deployment Checklist

## Pre-Deployment Checklist

### ✅ Configuration Complete
- [x] Database credentials updated in .env
- [x] Zoho SMTP configuration set up
- [x] Security keys generated (SESSION_SECRET & ENCRYPTION_KEY)
- [x] Production URLs configured
- [x] Debug mode disabled

### 🔧 Files to Upload to Hostinger

Upload these files to your `dash.creativedistro.com` directory:

**Core Files:**
- [ ] .env (with production settings)
- [ ] .htaccess (security configurations)
- [ ] dashboard_api.php
- [ ] dashboard_config.php
- [ ] env-loader.php
- [ ] index.html
- [ ] login.html
- [ ] join.html
- [ ] dashboard.js
- [ ] style.css
- [ ] test_db_connection.php

**Testing Files (temporary):**
- [ ] test_smtp.php (DELETE after testing)

**Assets:**
- [ ] icons/Favicon.png
- [ ] icons/App Icon.png

### 🚀 Deployment Steps

1. **Upload Files via Hostinger File Manager:**
   - Access Hostinger control panel
   - Navigate to File Manager
   - Go to `dash.creativedistro.com` directory
   - Upload all files maintaining directory structure

2. **Set File Permissions:**
   ```
   Files: 644 (readable by web server)
   Directories: 755 (executable by web server)
   .env: 600 (readable only by owner)
   ```

3. **Verify SSL Certificate:**
   - Ensure SSL is enabled for dash.creativedistro.com
   - Test HTTPS redirect works

### 🧪 Testing Procedure

1. **Test Configuration:**
   - Visit: `https://dash.creativedistro.com/test_smtp.php`
   - Verify database connection ✓
   - Verify SMTP connection ✓
   - Test email sending: `?test_email=your@email.com`

2. **Test Dashboard Functions:**
   - Visit: `https://dash.creativedistro.com/login`
   - Test user registration
   - Test login functionality
   - Test invite system
   - Verify email delivery

3. **Security Verification:**
   - Confirm .env file is not accessible via browser
   - Test HTTPS redirects
   - Verify session security

### 🔒 Post-Deployment Security

1. **Delete Test Files:**
   - [ ] Remove test_smtp.php
   - [ ] Remove any other test files

2. **Monitor Logs:**
   - Check PHP error logs
   - Monitor email delivery logs
   - Watch for any security issues

### 🛠️ Hostinger-Specific Settings

**PHP Configuration:**
- Ensure PHP 7.4+ is enabled
- Verify PDO MySQL extension is available
- Check mail() function is enabled

**Database:**
- Your database: `u132234435_dash`
- User: `dash`
- Host: `localhost`

**Email Settings:**
- SMTP Host: smtp.zoho.com
- Port: 587 (STARTTLS)
- Authentication: Required

### 🚨 Troubleshooting

**Common Issues:**

1. **Database Connection Fails:**
   - Verify database credentials in .env
   - Check if database user has proper permissions
   - Ensure database exists and is accessible

2. **SMTP Authentication Fails:**
   - Verify Zoho app password is correct
   - Check if SMTP is enabled in Zoho account
   - Ensure noreply@creativedistro.com exists in Zoho

3. **File Permission Errors:**
   - Set .env to 600 permissions
   - Ensure web server can read PHP files (644)
   - Check directory permissions (755)

4. **SSL/HTTPS Issues:**
   - Verify SSL certificate is installed
   - Check .htaccess HTTPS redirect rules
   - Ensure mixed content warnings are resolved

### 📞 Support Contacts

- **Hostinger Support:** Available in control panel
- **Zoho Support:** For email-related issues
- **Domain Issues:** Check DNS settings

### 🎯 Success Criteria

Deployment is successful when:
- [ ] Dashboard loads at https://dash.creativedistro.com
- [ ] Users can register and login
- [ ] Invite emails are sent successfully
- [ ] All API endpoints respond correctly
- [ ] No PHP errors in logs
- [ ] SSL certificate is working
- [ ] .env file is protected from web access

---

**Last Updated:** <?php echo date('Y-m-d H:i:s'); ?>
