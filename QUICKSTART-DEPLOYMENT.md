# cPanel Git Deployment - Quick Start Guide

## 🚀 Initial Setup (Do Once)

### Step 1: Configure Deployment

```bash
cd /opt/lampp/htdocs/Gatherly-EMS_2025
bash deploy-scripts/setup.sh
```

Enter your cPanel username when prompted.

### Step 2: Create Repository in cPanel

1. Login to cPanel
2. Go to **Files** → **Git Version Control**
3. Click **Create**
4. Fill in:
   - **Clone URL**: Your GitHub repository URL
   - **Repository Path**: `/home/YOURUSERNAME/repositories/Gatherly-EMS_2025`
   - **Repository Name**: `Gatherly-EMS_2025`
5. Click **Create**

### Step 3: Build and Commit

```bash
# Build Tailwind CSS
bash deploy-scripts/build-tailwind.sh

# Commit deployment configuration
git add .cpanel.yml src/output.css deploy-scripts/ public/.htaccess
git commit -m "Configure cPanel deployment"

# Push to repository
git push origin main
```

cPanel will automatically deploy your project! 🎉

---

## 📝 Regular Deployment Workflow

Every time you make changes:

```bash
# 1. Make your changes
# ... edit files ...

# 2. Run pre-deployment checks
bash deploy-scripts/pre-deploy.sh

# 3. Commit and push
git add .
git commit -m "Description of your changes"
git push origin main
```

**That's it!** cPanel automatically deploys when you push.

---

## 🛠️ Manual Deployment (Alternative)

If automatic deployment doesn't work:

1. Go to cPanel → **Git Version Control**
2. Click **Manage** on your repository
3. Click **Pull or Deploy** tab
4. Click **Update from Remote**
5. Click **Deploy HEAD Commit**

---

## ✅ Post-Deployment Checklist

After first deployment:

### 1. Set Up Database

```bash
# Via cPanel or phpMyAdmin
- Create database
- Import db/sad_db.sql
- Update database credentials in PHP files
```

### 2. Install Python Dependencies (if needed)

SSH into cPanel:

```bash
cd /home/YOURUSERNAME/public_html
python3 -m pip install --user -r requirements.txt
```

### 3. Verify Files

Check that these exist in `/home/YOURUSERNAME/public_html/`:

- ✓ `public/` directory
- ✓ `src/output.css`
- ✓ `ml/` directory with Python files
- ✓ All PHP files

### 4. Test Your Website

Visit: `https://yourdomain.com/public/pages/home.php`

---

## 🔍 Troubleshooting

### Deployment Not Running?

- ✓ Check `.cpanel.yml` is committed
- ✓ Verify YAML syntax (no tabs!)
- ✓ Check cPanel Git Version Control for errors

### CSS Not Loading?

```bash
# Build locally and commit:
bash deploy-scripts/build-tailwind.sh
git add src/output.css
git commit -m "Update CSS"
git push
```

### Python Errors?

```bash
# SSH into cPanel and install manually:
cd /home/YOURUSERNAME/public_html
python3 -m pip install --user -r requirements.txt
```

### Permission Errors?

```bash
# SSH into cPanel:
cd /home/YOURUSERNAME/public_html
chmod -R 755 public src ml
find . -type f -name "*.php" -exec chmod 644 {} \;
```

---

## 📁 File Structure on cPanel

```
/home/YOURUSERNAME/
├── public_html/              ← Your deployed website
│   ├── public/              ← Web-accessible files
│   ├── src/                 ← Source files
│   ├── ml/                  ← Python ML system
│   └── requirements.txt
└── repositories/            ← Git repository
    └── Gatherly-EMS_2025/
```

---

## 🔐 Security Notes

**Never commit these files:**

- ✗ `.env` (database passwords)
- ✗ `node_modules/`
- ✗ Database backups with real data

**Already protected by .htaccess:**

- ✓ `.cpanel.yml`
- ✓ `.env` files
- ✓ Configuration files
- ✓ Git files

---

## 📚 Documentation

- **Full Guide**: See `DEPLOYMENT.md`
- **Script Details**: See `deploy-scripts/README.md`
- **cPanel Docs**: https://docs.cpanel.net/knowledge-base/web-services/guide-to-git-deployment/

---

## ⚡ Quick Commands

```bash
# Configure deployment (first time)
bash deploy-scripts/setup.sh

# Pre-deployment checks (before every push)
bash deploy-scripts/pre-deploy.sh

# Build Tailwind CSS
bash deploy-scripts/build-tailwind.sh

# Install Python packages
bash deploy-scripts/install-python-deps.sh

# Standard commit and deploy
git add .
git commit -m "Your message"
git push origin main
```

---

**Need Help?** Check the full `DEPLOYMENT.md` guide or contact your hosting support.
