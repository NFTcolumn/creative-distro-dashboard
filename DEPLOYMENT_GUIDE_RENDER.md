# Creative Distro Dashboard - Render + Netlify Deployment Guide

This guide will help you deploy the Creative Distro Dashboard using Render PostgreSQL database, Render backend, and Netlify frontend.

## Project Structure

```
dashboard/
├── frontend/           # Static files for Netlify
│   ├── index.html
│   ├── login.html
│   ├── join.html
│   ├── style.css
│   ├── dashboard.js
│   ├── netlify.toml
│   └── icons/
├── backend/            # PHP API for Render
│   ├── index.php
│   ├── dashboard_api.php
│   ├── dashboard_config.php
│   ├── database_schema.sql
│   ├── email_templates.php
│   ├── env-loader.php
│   ├── composer.json
│   └── test files...
└── deployment files...
```

## Prerequisites

1. GitHub account
2. Netlify account  
3. Render account

## Step 1: Push to GitHub

1. **Initialize and commit the repository:**
```bash
git add .
git commit -m "Initial commit - restructured for Render deployment"
```

2. **Create a new repository on GitHub and push:**
```bash
git remote add origin https://github.com/YOUR_USERNAME/creative-distro-dashboard.git
git branch -M main
git push -u origin main
```

## Step 2: Create PostgreSQL Database on Render

1. **Create PostgreSQL Database:**
   - Go to https://render.com/dashboard
   - Click "New" → "PostgreSQL"
   - Configure the database:
     - **Name:** `creative-distro-dashboard-db`
     - **Database:** `creative_distro_dashboard`
     - **User:** `dashboard_user`
     - **Region:** Choose closest to your users
     - **PostgreSQL Version:** Latest
     - **Plan:** Free tier

2. **Note Database Connection Details:**
   After creation, you'll get:
   - **Internal Database URL** (for Render services)
   - **External Database URL** (for external connections)
   - **Host, Database, User, Password** (individual values)

3. **Initialize Database Schema:**
   - Connect to your database using the external URL
   - Run the SQL from `backend/database_schema.sql`
   - Or use Render's built-in database console

## Step 3: Deploy Backend to Render

1. **Create Web Service:**
   - Go to https://render.com/dashboard
   - Click "New" → "Web Service"
   - Connect your GitHub repository
   - Select the repository you just created

2. **Configure the service:**
   - **Name:** `creative-distro-dashboard-api`
   - **Environment:** `PHP`
   - **Build Command:** `composer install`
   - **Start Command:** `php -S 0.0.0.0:$PORT -t backend`
   - **Root Directory:** Leave empty

3. **Set Environment Variables:**
   Add these environment variables in Render (use your actual database values):
   ```
   DATABASE_TYPE=pgsql
   DASHBOARD_DB_HOST=your-db-host.render.com
   DASHBOARD_DB_NAME=creative_distro_dashboard
   DASHBOARD_DB_USER=dashboard_user
   DASHBOARD_DB_PASS=your-database-password
   DASHBOARD_DB_PORT=5432
   DASHBOARD_BASE_URL=https://creative-distro-dashboard.netlify.app
   MAIN_SITE_URL=https://creativedistro.com
   SMTP_HOST=smtp.zoho.com
   SMTP_PORT=587
   SMTP_USERNAME=noreply@creativedistro.com
   SMTP_PASSWORD=F_reeMinds07$
   SMTP_FROM_EMAIL=noreply@creativedistro.com
   SMTP_FROM_NAME=Creative Distro Dashboard
   DEBUG_MODE=false
   LOG_LEVEL=INFO
   SESSION_SECRET=Kx9mP2vR8nQ4wE7tY1uI5oP3aS6dF9gH2jK5lZ8xC1vB4nM7qW0eR3tY6uI9oP2a
   ENCRYPTION_KEY=a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456
   ```

4. **Deploy:**
   - Click "Create Web Service"
   - Wait for deployment to complete
   - Note the URL (e.g., `https://creative-distro-dashboard-api.onrender.com`)

## Step 4: Deploy Frontend to Netlify

1. **Create a new site on Netlify:**
   - Go to https://app.netlify.com/
   - Click "New site from Git"
   - Connect your GitHub repository
   - Select the repository you created

2. **Configure build settings:**
   - **Base directory:** `frontend`
   - **Build command:** Leave empty (static site)
   - **Publish directory:** `frontend`

3. **Deploy:**
   - Click "Deploy site"
   - Wait for deployment to complete
   - Note the URL (e.g., `https://creative-distro-dashboard.netlify.app`)

## Step 5: Update Configuration

1. **Update frontend API URL (if needed):**
   - Edit `frontend/dashboard.js`
   - Change `API_BASE_URL` to your actual Render URL

2. **Update backend CORS (if needed):**
   - Edit `backend/dashboard_api.php` and `backend/index.php`
   - Change the CORS origin to your actual Netlify URL

3. **Update environment variables in Render:**
   - Update `DASHBOARD_BASE_URL` to your actual Netlify URL

## Step 6: Initialize Database

1. **Connect to PostgreSQL:**
   - Use the external database URL from Render
   - Connect with a PostgreSQL client (pgAdmin, DBeaver, etc.)

2. **Run Database Schema:**
   - Execute the SQL from `backend/database_schema.sql`
   - This will create all tables and the admin user

3. **Verify Setup:**
   - Check that all tables were created
   - Verify admin user exists: `admin@creativedistro.com` / `admin123`

## Step 7: Testing

### Test Database Connection:
```bash
# Update .env with your Render PostgreSQL credentials
DATABASE_TYPE=pgsql
DASHBOARD_DB_HOST=your-render-db-host
DASHBOARD_DB_NAME=creative_distro_dashboard
DASHBOARD_DB_USER=dashboard_user
DASHBOARD_DB_PASS=your-password
DASHBOARD_DB_PORT=5432

# Test connection
php test_db_connection.php
```

### Test SMTP:
```bash
# Test email configuration
php test_smtp.php
```

### Test Full Integration:
1. Visit your Netlify URL
2. Try logging in with: `admin@creativedistro.com` / `admin123`
3. Check browser console for any errors
4. Verify API calls reach the backend

## Environment Variables Reference

### Render Backend Environment Variables:
```
DATABASE_TYPE=pgsql
DASHBOARD_DB_HOST=your-db-host.render.com
DASHBOARD_DB_NAME=creative_distro_dashboard
DASHBOARD_DB_USER=dashboard_user
DASHBOARD_DB_PASS=your-database-password
DASHBOARD_DB_PORT=5432
DASHBOARD_BASE_URL=https://your-netlify-site.netlify.app
MAIN_SITE_URL=https://creativedistro.com
SMTP_HOST=smtp.zoho.com
SMTP_PORT=587
SMTP_USERNAME=noreply@creativedistro.com
SMTP_PASSWORD=F_reeMinds07$
SMTP_FROM_EMAIL=noreply@creativedistro.com
SMTP_FROM_NAME=Creative Distro Dashboard
DEBUG_MODE=false
LOG_LEVEL=INFO
SESSION_SECRET=Kx9mP2vR8nQ4wE7tY1uI5oP3aS6dF9gH2jK5lZ8xC1vB4nM7qW0eR3tY6uI9oP2a
ENCRYPTION_KEY=a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456
```

## Database Schema

The PostgreSQL schema includes:
- **users** - User accounts and referral data
- **invites** - Invitation system
- **user_network** - Network relationships
- **user_activations** - Activation tracking
- **network_stats** - Cached network statistics
- **email_logs** - Email activity logs
- **rate_limits** - Rate limiting data

Default admin user:
- **Email:** `admin@creativedistro.com`
- **Password:** `admin123`
- **Referral Code:** `ADMIN001`

## Troubleshooting

### Database Issues:
1. **Connection Failed:**
   - Verify environment variables in Render
   - Check database is running in Render dashboard
   - Ensure `DATABASE_TYPE=pgsql` is set

2. **Tables Don't Exist:**
   - Run the database schema SQL
   - Check PostgreSQL logs in Render

### API Issues:
1. **CORS Errors:**
   - Verify CORS headers in backend
   - Check frontend/backend URL configuration

2. **500 Errors:**
   - Check Render service logs
   - Verify environment variables
   - Test database connection

### Frontend Issues:
1. **API Calls Fail:**
   - Check API URL in `frontend/dashboard.js`
   - Verify backend is running
   - Check browser console for errors

## Security Best Practices

1. **Environment Variables:**
   - Never commit `.env` files
   - Use Render's environment variable system
   - Rotate secrets regularly

2. **Database Security:**
   - Use strong passwords
   - Limit database access to Render services
   - Regular backups

3. **HTTPS:**
   - Both services use HTTPS by default
   - Verify all API calls use HTTPS

## Monitoring & Maintenance

1. **Render Dashboard:**
   - Monitor service health
   - Check logs for errors
   - Monitor database usage

2. **Netlify Dashboard:**
   - Monitor deploy status
   - Check function logs
   - Monitor bandwidth usage

3. **Database Maintenance:**
   - Regular backups (Render handles this)
   - Monitor storage usage
   - Optimize queries as needed

## Cost Considerations

### Render Free Tier Limits:
- **Web Service:** 750 hours/month, sleeps after 15 min inactivity
- **PostgreSQL:** 1GB storage, 1 million rows
- **Bandwidth:** 100GB/month

### Scaling Options:
- Upgrade to paid plans for:
  - Always-on services
  - More database storage
  - Better performance
  - Custom domains

## Support

For issues:
1. Check Render service logs
2. Check Netlify deploy logs
3. Test database connection
4. Verify environment variables
5. Check browser console for frontend errors

## Next Steps

After successful deployment:
1. Set up custom domains (optional)
2. Configure monitoring/alerts
3. Set up automated backups
4. Plan for scaling as needed
5. Delete test files from production

---

**Expected URLs:**
- Frontend: `https://your-site.netlify.app`
- Backend API: `https://your-api.onrender.com`
- Database: Managed by Render (internal access only)
