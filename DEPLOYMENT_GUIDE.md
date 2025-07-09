# Creative Distro Dashboard - Deployment Guide

This guide will help you deploy the Creative Distro Dashboard to both Netlify (frontend) and Render (backend).

## Project Structure

The project has been restructured for deployment:

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
│   ├── email_templates.php
│   ├── env-loader.php
│   ├── composer.json
│   ├── .env
│   └── test files...
└── deployment files...
```

## Prerequisites

1. GitHub account
2. Netlify account
3. Render account
4. MySQL database (already configured in .env)

## Step 1: Push to GitHub

1. Initialize and commit the repository:
```bash
git add .
git commit -m "Initial commit - restructured for deployment"
```

2. Create a new repository on GitHub and push:
```bash
git remote add origin https://github.com/YOUR_USERNAME/creative-distro-dashboard.git
git branch -M main
git push -u origin main
```

## Step 2: Deploy Backend to Render

1. **Create a new Web Service on Render:**
   - Go to https://render.com/dashboard
   - Click "New" → "Web Service"
   - Connect your GitHub repository
   - Select the repository you just created

2. **Configure the service:**
   - **Name:** `creative-distro-dashboard-api`
   - **Environment:** `PHP`
   - **Build Command:** `composer install` (if needed)
   - **Start Command:** `php -S 0.0.0.0:$PORT -t backend`
   - **Root Directory:** Leave empty (we'll handle routing in index.php)

3. **Set Environment Variables:**
   Add these environment variables in Render:
   ```
   DASHBOARD_DB_HOST=auth-db1613.hstgr.io
   DASHBOARD_DB_NAME=u132234435_dash
   DASHBOARD_DB_USER=u132234435_dash
   DASHBOARD_DB_PASS=F_distro2030!
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
   - Note the URL (should be something like `https://creative-distro-dashboard-api.onrender.com`)

## Step 3: Deploy Frontend to Netlify

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
   - Note the URL (should be something like `https://creative-distro-dashboard.netlify.app`)

## Step 4: Update URLs (if needed)

If your actual deployment URLs differ from the ones configured:

1. **Update frontend API URL:**
   - Edit `frontend/dashboard.js`
   - Change `API_BASE_URL` to your actual Render URL

2. **Update backend CORS:**
   - Edit `backend/dashboard_api.php` and `backend/index.php`
   - Change the CORS origin to your actual Netlify URL

3. **Update environment variables:**
   - In Render, update `DASHBOARD_BASE_URL` to your actual Netlify URL

## Step 5: Custom Domains (Optional)

### For Netlify (Frontend):
1. Go to Site settings → Domain management
2. Add custom domain: `dash.creativedistro.com`
3. Configure DNS records as instructed

### For Render (Backend):
1. Go to Settings → Custom Domains
2. Add custom domain: `api.creativedistro.com`
3. Configure DNS records as instructed

## Step 6: SSL/HTTPS

Both Netlify and Render provide automatic SSL certificates. Ensure:
- Netlify site is accessible via HTTPS
- Render service is accessible via HTTPS
- All API calls use HTTPS URLs

## Step 7: Testing

1. **Test the frontend:**
   - Visit your Netlify URL
   - Check that the dashboard loads
   - Verify all static assets load correctly

2. **Test the backend:**
   - Visit your Render URL directly
   - Should see API response or error (not a blank page)

3. **Test integration:**
   - Try logging in (if you have test credentials)
   - Check browser console for any CORS errors
   - Verify API calls are reaching the backend

## Environment Variables Reference

### Required for Render (Backend):
- `DASHBOARD_DB_HOST` - Database host
- `DASHBOARD_DB_NAME` - Database name
- `DASHBOARD_DB_USER` - Database username
- `DASHBOARD_DB_PASS` - Database password
- `DASHBOARD_BASE_URL` - Frontend URL (Netlify)
- `SMTP_*` - Email configuration
- `DEBUG_MODE` - Set to `false` for production

### No environment variables needed for Netlify (Frontend)
The frontend is a static site with API URL hardcoded in JavaScript.

## Troubleshooting

### Common Issues:

1. **CORS Errors:**
   - Check that backend CORS headers match frontend domain
   - Ensure credentials are allowed in CORS settings

2. **API Not Found:**
   - Verify Render service is running
   - Check that routing in `backend/index.php` is correct

3. **Database Connection:**
   - Verify environment variables in Render
   - Test database connectivity

4. **Session Issues:**
   - Ensure cookies are enabled
   - Check that HTTPS is used for both frontend and backend

### Logs:
- **Render:** Check logs in Render dashboard
- **Netlify:** Check function logs and deploy logs
- **Browser:** Check console for JavaScript errors

## Security Notes

1. Never commit `.env` files to Git
2. Use environment variables for all sensitive data
3. Ensure HTTPS is used for all communications
4. Regularly rotate database passwords and API keys
5. Monitor logs for suspicious activity

## Maintenance

1. **Updates:** Push changes to GitHub, both services will auto-deploy
2. **Monitoring:** Set up monitoring for both services
3. **Backups:** Ensure database is regularly backed up
4. **SSL:** Certificates are auto-renewed by both platforms

## Support

If you encounter issues:
1. Check the logs in both Render and Netlify dashboards
2. Verify environment variables are set correctly
3. Test API endpoints directly
4. Check browser console for frontend errors
