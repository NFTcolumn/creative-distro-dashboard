# Creative Distro Dashboard

A modern, responsive dashboard for managing invites and tracking referral networks in the Creative Distro ecosystem.

## Features

- **User Authentication**: Secure login and registration system
- **Invite Management**: Send and track invitations with unique referral codes
- **Network Tracking**: Monitor your referral network across 6 levels
- **Real-time Statistics**: View invite quotas, success rates, and network growth
- **Responsive Design**: Works seamlessly on desktop and mobile devices
- **Email Integration**: Automated invite and activation emails

## Architecture

This application uses a modern separated architecture:

- **Frontend**: Static HTML/CSS/JavaScript hosted on Netlify
- **Backend**: PHP API hosted on Render
- **Database**: MySQL database for data persistence
- **Email**: SMTP integration for automated communications

## Quick Start

### For Users
Visit the live dashboard at: `https://creative-distro-dashboard.netlify.app`

### For Developers

1. **Clone the repository:**
```bash
git clone https://github.com/YOUR_USERNAME/creative-distro-dashboard.git
cd creative-distro-dashboard
```

2. **Set up environment variables:**
Copy `.env.example` to `.env` and configure your settings.

3. **Deploy:**
Follow the detailed instructions in `DEPLOYMENT_GUIDE.md`

## Project Structure

```
dashboard/
├── frontend/           # Static files for Netlify
│   ├── index.html     # Main dashboard
│   ├── login.html     # Login page
│   ├── join.html      # Registration page
│   ├── style.css      # Styles
│   ├── dashboard.js   # Frontend logic
│   └── icons/         # App icons
├── backend/            # PHP API for Render
│   ├── index.php      # Entry point
│   ├── dashboard_api.php    # API endpoints
│   ├── dashboard_config.php # Configuration
│   └── *.php          # Other PHP files
└── docs/              # Documentation
```

## API Endpoints

### Authentication
- `POST /auth/login` - User login
- `POST /auth/register` - User registration
- `POST /auth/logout` - User logout
- `POST /auth/activate` - Account activation

### Invites
- `POST /invites/send` - Send an invite
- `GET /invites/list` - Get user's invites
- `GET /invites/status` - Get invite status

### User Management
- `GET /user/profile` - Get user profile
- `PUT /user/profile` - Update user profile
- `GET /user/quota` - Get invite quota

### Statistics
- `GET /stats/dashboard` - Get dashboard statistics
- `GET /network/stats` - Get network statistics

## Technologies Used

### Frontend
- HTML5, CSS3, JavaScript (ES6+)
- CSS Grid and Flexbox for responsive layout
- Fetch API for HTTP requests
- Local Storage for client-side data

### Backend
- PHP 7.4+
- PDO for database interactions
- Session management
- RESTful API design

### Database
- MySQL 5.7+
- Normalized schema design
- Foreign key constraints
- Indexed queries for performance

### Deployment
- **Frontend**: Netlify (Static hosting)
- **Backend**: Render (PHP hosting)
- **Database**: Hostinger MySQL
- **Email**: Zoho SMTP

## Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- CSRF protection
- Rate limiting
- Session management
- HTTPS enforcement
- CORS configuration

## Development

### Local Development

1. **Frontend**: Serve the `frontend/` directory with any static server
2. **Backend**: Use PHP's built-in server or XAMPP/MAMP
3. **Database**: Set up MySQL locally or use the remote database

### Environment Variables

Required environment variables (see `.env.example`):
- Database configuration
- SMTP settings
- Application URLs
- Security keys

## Deployment

See `DEPLOYMENT_GUIDE.md` for detailed deployment instructions for both Netlify and Render.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is proprietary software for Creative Distro.

## Support

For technical support or questions:
- Check the `DEPLOYMENT_GUIDE.md` for common issues
- Review the API documentation
- Contact the development team

## Changelog

### v1.0.0 (Current)
- Initial release
- User authentication system
- Invite management
- Network tracking
- Responsive dashboard
- Email integration
- Netlify/Render deployment ready
