# Deployment Scripts

This directory contains scripts to help with deploying Gatherly EMS to cPanel.

## Available Scripts

### 1. `setup.sh` - Initial Configuration

Configures your deployment settings with your cPanel username.

```bash
bash deploy-scripts/setup.sh
```

**What it does:**

- Prompts for your cPanel username
- Updates `.cpanel.yml` with correct paths
- Displays next steps for deployment

### 2. `build-tailwind.sh` - Build Tailwind CSS

Compiles Tailwind CSS for production with minification.

```bash
bash deploy-scripts/build-tailwind.sh
```

**What it does:**

- Compiles `src/input.css` to `src/output.css`
- Minifies CSS for production
- Reports build status

**When to use:**

- Before committing changes that affect styles
- Before first deployment
- When Tailwind config changes

### 3. `install-python-deps.sh` - Install Python Dependencies

Installs Python packages required for ML features.

```bash
bash deploy-scripts/install-python-deps.sh
```

**What it does:**

- Reads `requirements.txt`
- Installs packages using pip
- Handles different Python/pip versions

**When to use:**

- After cloning the repository
- When `requirements.txt` is updated
- On the cPanel server (via SSH)

### 4. `pre-deploy.sh` - Pre-Deployment Checks

Runs comprehensive checks before deployment.

```bash
bash deploy-scripts/pre-deploy.sh
```

**What it does:**

- Checks Git status
- Builds Tailwind CSS
- Validates Python syntax
- Validates PHP syntax
- Checks for required files
- Provides deployment instructions

**When to use:**

- Before every `git push`
- To verify project integrity
- Before production deployments

## Typical Workflow

### First-Time Setup

```bash
# 1. Configure deployment
bash deploy-scripts/setup.sh

# 2. Build CSS
bash deploy-scripts/build-tailwind.sh

# 3. Commit configuration
git add .cpanel.yml src/output.css
git commit -m "Configure cPanel deployment"

# 4. Push to trigger deployment
git push origin main
```

### Regular Development Workflow

```bash
# 1. Make your changes
# ... edit files ...

# 2. Run pre-deployment checks
bash deploy-scripts/pre-deploy.sh

# 3. Commit and push
git add .
git commit -m "Your changes"
git push origin main
```

### Manual Deployment Tasks (on cPanel via SSH)

```bash
# SSH into your cPanel account
ssh yourusername@yourserver.com

# Navigate to deployment directory
cd /home/yourusername/public_html

# Install Python dependencies
python3 -m pip install --user -r requirements.txt

# Build Tailwind CSS (if Node.js is available)
npx tailwindcss -i ./src/input.css -o ./src/output.css --minify

# Set permissions
chmod -R 755 public src ml
find . -type f -name "*.php" -exec chmod 644 {} \;
```

## Troubleshooting

### Script Permission Denied

```bash
chmod +x deploy-scripts/*.sh
```

### Python Syntax Errors

The pre-deploy script will identify files with syntax errors. Fix them before pushing.

### PHP Syntax Errors

The pre-deploy script validates all PHP files. Use `php -l filename.php` to check individual files.

### Tailwind Build Fails

- Ensure Node.js and npm are installed
- Run `npm install` if needed
- Check `tailwind.config.js` for errors

## Notes

- **Always run `pre-deploy.sh`** before pushing to production
- **Build CSS locally** if cPanel doesn't have Node.js
- **Test thoroughly** in development before deploying
- **Keep scripts executable** with `chmod +x`

## Support

See the main [DEPLOYMENT.md](../DEPLOYMENT.md) file for complete deployment documentation.
