# Creative Distro Dashboard

A modern web dashboard for the Creative Distro platform with user management, referral tracking, and network analytics.

## Project Structure

```
dashboard/
├── frontend/           # Static files for Netlify deployment
│   ├── index.html      # Main dashboard
│   ├── login.html      # Login page
│   ├── join.html       # Registration page
│   ├── style.css       # Styles
│   ├── dashboard.js    # Frontend logic
│   ├── netlify.toml    # Netlify configuration
│   └── icons/          # App icons
├── backend/            # PHP API for Render deployment
│   ├── index.php       # Entry point
│   ├── dashboard_api.php # API endpoints
│   ├── dashboard_config.php # Configuration
│   ├── database_schema.sql # PostgreSQL schema
│   ├── composer.json   # PHP dependencies
│   └── other files...
└── Configuration files
```

## Features

- **User Authentication**: Secure login and registration
- **Referral System**: Multi-level referral tracking
- **Network Analytics**: Real-time network statistics
- **Email Integration**: SMTP email notifications
- **Responsive Design**: Mobile-friendly interface
- **API-First**: RESTful API architecture

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8.4+
- **Database**: PostgreSQL (production) / MySQL (development)
- **Deployment**: Netlify (frontend) + Render (backend)
- **Email**: SMTP integration

## Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/creative-distro-dashboard.git
   cd creative-distro-dashboard
   ```

2. **Set up environment variables**
   - Copy `.env.render` to `.env` in the backend directory
   - Update with your actual database and SMTP credentials

3. **Initialize database**
   - Create a PostgreSQL database
   - Run the SQL from `backend/database_schema.sql`

4. **Test locally**
   ```bash
   # Test database connection
   php backend/test_db_connection.php
   
   # Test SMTP
   php backend/test_smtp.php
   ```

## Deployment

### Frontend (Netlify)
- Deploy from the `frontend/` directory
- Static site, no build process required
- Automatic HTTPS and CDN

### Backend (Render)
- Deploy as PHP web service
- Set environment variables in Render dashboard
- Automatic scaling and HTTPS

### Database
- Use Render PostgreSQL for production
- Free tier: 1GB storage, 1M rows
- Automatic backups included

## Environment Variables

Required environment variables (set in your deployment platform):

```
DATABASE_TYPE=pgsql
DASHBOARD_DB_HOST=your-database-host
DASHBOARD_DB_NAME=your-database-name
DASHBOARD_DB_USER=your-database-user
DASHBOARD_DB_PASS=your-database-password
DASHBOARD_DB_PORT=5432
DASHBOARD_BASE_URL=your-frontend-url
MAIN_SITE_URL=https://creativedistro.com
SMTP_HOST=your-smtp-host
SMTP_PORT=587
SMTP_USERNAME=your-smtp-username
SMTP_PASSWORD=your-smtp-password
SMTP_FROM_EMAIL=your-from-email
SMTP_FROM_NAME=Creative Distro Dashboard
DEBUG_MODE=false
LOG_LEVEL=INFO
SESSION_SECRET=generate-secure-random-string
ENCRYPTION_KEY=generate-secure-encryption-key
```

## Security

- All sensitive data uses environment variables
- `.env` files are excluded from Git
- HTTPS enforced for all communications
- CORS properly configured
- Password hashing with PHP's `password_hash()`

## Database Schema

- **users**: User accounts and referral data
- **invites**: Invitation system
- **user_network**: Network relationships
- **user_activations**: Activation tracking
- **network_stats**: Cached statistics
- **email_logs**: Email activity logs
- **rate_limits**: Rate limiting data

## API Endpoints

- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/user/profile` - Get user profile
- `POST /api/invites/send` - Send invitation
- `GET /api/network/stats` - Get network statistics
- `GET /api/network/tree` - Get network tree

## Development

1. **Local Setup**
   - Install PHP 8.4+
   - Set up local database
   - Configure environment variables

2. **Testing**
   - Use provided test scripts
   - Check database connectivity
   - Verify SMTP configuration

3. **Contributing**
   - Follow PSR-12 coding standards
   - Test all changes locally
   - Update documentation as needed

## Support

For technical issues:
1. Check server logs
2. Verify environment variables
3. Test database connectivity
4. Check browser console for frontend errors

## License

Private project for Creative Distro platform.

---

**Note**: This dashboard is designed for deployment to modern cloud platforms with automatic scaling, SSL, and monitoring capabilities.
