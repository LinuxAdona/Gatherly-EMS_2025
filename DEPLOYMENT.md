# Gatherly EMS - cPanel Git Deployment Guide

This document explains how to deploy the Gatherly EMS project to cPanel using Git Version Control.

## Prerequisites

1. **cPanel Account** with Git Version Control feature enabled
2. **SSH Access** to your cPanel account (recommended)
3. **Node.js** installed on cPanel (for Tailwind CSS compilation)
4. **Python 3** installed on cPanel (for ML features)

## Initial Setup

### 1. Update .cpanel.yml Configuration

Open `.cpanel.yml` and replace `yourusername` with your actual cPanel username:

```yaml
- export DEPLOYPATH=/home/yourusername/public_html/
- export REPOPATH=/home/yourusername/repositories/Gatherly-EMS_2025
```

### 2. Create Repository in cPanel

1. Log in to cPanel
2. Go to **Files** → **Git Version Control**
3. Click **Create** button
4. Fill in the details:
   - **Clone URL**: Your GitHub/GitLab repository URL
   - **Repository Path**: `/home/yourusername/repositories/Gatherly-EMS_2025`
   - **Repository Name**: `Gatherly-EMS_2025`
5. Click **Create**

### 3. Configure Database

1. Create MySQL database in cPanel (Databases → MySQL Databases)
2. Import your database schema:
   ```bash
   mysql -u username -p database_name < db/sad_db.sql
   ```
3. Update database credentials in your PHP files

### 4. Set Up Environment Variables

Create `.env` file in the deployment directory (not in Git repo):

```bash
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
```

## Deployment Methods

### Automatic Deployment (Push to Deploy)

When you push to your repository, cPanel automatically deploys changes:

```bash
# 1. Make your changes locally
# 2. Build Tailwind CSS
bash deploy-scripts/build-tailwind.sh

# 3. Run pre-deployment checks
bash deploy-scripts/pre-deploy.sh

# 4. Commit and push
git add .
git commit -m "Your commit message"
git push origin main
```

cPanel will automatically:

- Copy files to `public_html`
- Install Python dependencies
- Build Tailwind CSS (if npx is available)
- Set proper permissions

### Manual Deployment (Pull to Deploy)

1. Go to cPanel → **Git Version Control**
2. Click **Manage** on your repository
3. Go to **Pull or Deploy** tab
4. Click **Update from Remote** (pulls latest changes)
5. Click **Deploy HEAD Commit** (runs deployment tasks)

## Project Structure on cPanel

After deployment, your files will be organized as:

```
/home/yourusername/
├── public_html/              # Deployment directory
│   ├── public/              # Web-accessible files
│   │   ├── assets/
│   │   └── pages/
│   ├── src/                 # Source files
│   │   ├── input.css
│   │   ├── output.css      # Built Tailwind CSS
│   │   └── services/
│   ├── ml/                  # Python ML system
│   ├── db/                  # Database files
│   ├── requirements.txt
│   └── package.json
└── repositories/            # Git repository
    └── Gatherly-EMS_2025/
```

## Post-Deployment Tasks

### 1. Set Up Cron Jobs (for Python ML)

If your ML system needs scheduled tasks:

1. Go to cPanel → **Advanced** → **Cron Jobs**
2. Add cron job:
   ```
   0 * * * * cd /home/yourusername/public_html && python3 ml/venue_recommender.py
   ```

### 2. Configure .htaccess

Ensure your `.htaccess` file is properly configured in `public_html/public/`:

```apache
# Enable rewriting
RewriteEngine On

# Redirect to HTTPS (if you have SSL)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Deny access to sensitive files
<FilesMatch "\.(yml|yaml|env|git)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 3. Install Python Packages Manually (if needed)

SSH into your cPanel account:

```bash
cd /home/yourusername/public_html
python3 -m pip install --user -r requirements.txt
```

### 4. Build Tailwind CSS Manually (if needed)

```bash
cd /home/yourusername/public_html
npx tailwindcss -i ./src/input.css -o ./src/output.css --minify
```

## Troubleshooting

### Deployment Not Running

- Check if `.cpanel.yml` is in the repository root
- Ensure the file is committed to Git
- Verify YAML syntax (no tabs, proper indentation)
- Check cPanel → Git Version Control for error messages

### Python Dependencies Not Installing

- SSH into cPanel and run manually:
  ```bash
  cd /home/yourusername/public_html
  python3 -m pip install --user -r requirements.txt
  ```
- Check Python version: `python3 --version`
- Contact hosting support if pip is not available

### Tailwind CSS Not Building

- Check if Node.js is installed: `node --version`
- Build manually via SSH:
  ```bash
  cd /home/yourusername/public_html
  npx tailwindcss -i ./src/input.css -o ./src/output.css --minify
  ```
- Alternatively, build locally and commit `src/output.css` to Git

### Permission Errors

Run this via SSH:

```bash
cd /home/yourusername/public_html
chmod -R 755 public src ml
find . -type f -name "*.php" -exec chmod 644 {} \;
```

### Database Connection Issues

- Verify database credentials in your configuration files
- Check if database user has proper permissions
- Test connection via PHP script

## Security Best Practices

1. **Never commit sensitive files**:

   - `.env` files
   - Database credentials
   - API keys

2. **Use .gitignore**:

   ```gitignore
   .env
   .env.local
   /vendor/
   node_modules/
   ```

3. **Protect sensitive directories**:

   - Add `.htaccess` to block access to `/src/services/`
   - Deny access to `.yml` and `.env` files

4. **Use environment variables** for configuration
5. **Enable HTTPS** on your domain
6. **Keep dependencies updated** regularly

## Useful Commands

### Local Development

```bash
# Run pre-deployment checks
bash deploy-scripts/pre-deploy.sh

# Build Tailwind CSS
bash deploy-scripts/build-tailwind.sh

# Install Python dependencies
bash deploy-scripts/install-python-deps.sh
```

### On cPanel (via SSH)

```bash
# Navigate to deployment directory
cd /home/yourusername/public_html

# View deployment log
cat last_deployment.txt

# Check Python packages
python3 -m pip list --user

# Test PHP syntax
php -l public/pages/home.php
```

## Deployment Workflow

### Standard Workflow

1. **Develop locally** on your machine
2. **Test thoroughly** before committing
3. **Run pre-deployment checks**:
   ```bash
   bash deploy-scripts/pre-deploy.sh
   ```
4. **Commit changes**:
   ```bash
   git add .
   git commit -m "Description of changes"
   ```
5. **Push to repository**:
   ```bash
   git push origin main
   ```
6. **Automatic deployment** runs on cPanel
7. **Verify deployment** by visiting your website
8. **Check logs** in cPanel if issues occur

## Support

- **cPanel Documentation**: https://docs.cpanel.net/
- **Git Documentation**: https://git-scm.com/doc
- **Contact your hosting provider** for cPanel-specific issues

---

**Note**: Always test in a staging environment before deploying to production!
