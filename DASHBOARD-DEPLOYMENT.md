# Creative Distro Dashboard Deployment Guide

This guide covers deploying the Creative Distro Dashboard as a standalone website on dash.creativedistro.com via Hostinger.

## Overview

The dashboard is a self-contained web application that includes:
- User authentication and registration
- Invite management system
- Network tracking and statistics
- Responsive design optimized for all devices

## Prerequisites

1. **Hostinger Account** with PHP hosting
2. **MySQL Database** access
3. **Domain/Subdomain** configured (dash.creativedistro.com)
4. **FTP/File Manager** access

## Files Structure

The dashboard directory contains all necessary files:

```
dashboard/
├── .env                    # Environment configuration
├── .htaccess              # Apache configuration
├── dashboard_api.php      # API endpoints
├── dashboard_config.php   # Configuration functions
├── env-loader.php         # Environment loader
├── index.html            # Main dashboard page
├── login.html            # Login page
├── join.html             # Registration page
├── dashboard.js          # Dashboard JavaScript
├── style.css             # Standalone CSS
└── icons/                # Favicon and app icons
    ├── Favicon.png
    └── App Icon.png
```

## Deployment Steps

### 1. Database Setup

Create a MySQL database for the dashboard:

```sql
-- Use the dashboard_database.sql file from the parent directory
-- This contains all necessary tables for the invite system
```

### 2. Environment Configuration

Update the `.env` file with your production settings:

```env
# Database Configuration
DASHBOARD_DB_HOST=your-db-host
DASHBOARD_DB_NAME=your-db-name
DASHBOARD_DB_USER=your-db-user
DASHBOARD_DB_PASS=your-db-password

# Application URLs
DASHBOARD_BASE_URL=https://dash.creativedistro.com
MAIN_SITE_URL=https://creativedistro.com

# Email Configuration
SMTP_HOST=your-smtp-host
SMTP_PORT=587
SMTP_USERNAME=your-smtp-username
SMTP_PASSWORD=your-smtp-password
SMTP_FROM_EMAIL=noreply@creativedistro.com
SMTP_FROM_NAME=Creative Distro Dashboard

# Security
DEBUG_MODE=false
LOG_LEVEL=INFO
```

### 3. File Upload

Upload all files from the `dashboard/` directory to your subdomain's root directory:

**Via File Manager:**
1. Access Hostinger File Manager
2. Navigate to `dash.creativedistro.com` directory
3. Upload all dashboard files
4. Ensure proper file permissions (644 for files, 755 for directories)

**Via FTP:**
```bash
# Upload entire dashboard directory
ftp your-ftp-host
# Navigate to subdomain directory
cd public_html/dash.creativedistro.com
# Upload files
put -r dashboard/* .
```

### 4. Database Import

Import the dashboard database schema:

1. Access phpMyAdmin or MySQL interface
2. Create new database (if not exists)
3. Import `dashboard_database.sql`
4. Verify all tables are created

### 5. Permissions Setup

Set correct file permissions:

```bash
# Files should be 644
chmod 644 *.php *.html *.css *.js .env
# Directories should be 755
chmod 755 icons/
# .htaccess should be 644
chmod 644 .htaccess
```

### 6. SSL Configuration

Ensure SSL is enabled for the subdomain:
1. In Hostinger panel, enable SSL for dash.creativedistro.com
2. Force HTTPS redirects
3. Update .htaccess if needed for additional security headers

### 7. Testing

Test the deployment:

1. **Access Login Page:** https://dash.creativedistro.com/login
2. **Check API Endpoints:** Test with browser dev tools
3. **Database Connection:** Verify no connection errors
4. **Email Configuration:** Test invite sending (if configured)

## Security Considerations

### File Protection

The `.htaccess` file includes:
- Protection for `.env` files
- Security headers
- Directory browsing disabled

### Database Security

- Use strong database passwords
- Limit database user permissions
- Enable SSL for database connections if available

### Session Security

- Sessions are configured with secure settings
- CSRF protection implemented
- Rate limiting on login attempts

## Maintenance

### Regular Tasks

1. **Monitor Logs:** Check error logs regularly
2. **Database Backups:** Schedule regular backups
3. **Security Updates:** Keep PHP and dependencies updated
4. **Performance Monitoring:** Monitor response times

### Troubleshooting

**Common Issues:**

1. **Database Connection Errors:**
   - Check `.env` database credentials
   - Verify database server is accessible
   - Check database user permissions

2. **API Errors:**
   - Check PHP error logs
   - Verify `.htaccess` rewrite rules
   - Test API endpoints directly

3. **Login Issues:**
   - Check session configuration
   - Verify database tables exist
   - Check user activation status

4. **Email Issues:**
   - Verify SMTP settings in `.env`
   - Check email logs
   - Test SMTP connection

### Log Files

Monitor these log locations:
- PHP error logs (usually in `/logs/` or `/error_logs/`)
- Apache access/error logs
- Application-specific logs (if configured)

## Performance Optimization

### Caching

The `.htaccess` includes:
- Static asset caching headers
- Gzip compression
- Browser caching directives

### Database Optimization

- Regular database optimization
- Index optimization for large datasets
- Query performance monitoring

## Backup Strategy

### Files
- Regular backup of all dashboard files
- Version control for code changes
- Configuration file backups

### Database
- Daily database backups
- Transaction log backups
- Test restore procedures

## Support

For deployment issues:
1. Check this documentation
2. Review error logs
3. Test individual components
4. Contact hosting support if needed

## Updates

When updating the dashboard:
1. Backup current files and database
2. Test updates in staging environment
3. Deploy during low-traffic periods
4. Monitor for issues post-deployment
