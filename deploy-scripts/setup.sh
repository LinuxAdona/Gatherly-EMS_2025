#!/bin/bash
# Quick Setup Script for cPanel Git Deployment
# Run this script to configure your deployment settings

echo "=========================================="
echo "Gatherly EMS - Deployment Setup"
echo "=========================================="
echo ""

# Get cPanel username
read -p "Enter your cPanel username: " cpanel_username

if [ -z "$cpanel_username" ]; then
    echo "Error: cPanel username is required!"
    exit 1
fi

echo ""
echo "Updating .cpanel.yml with your username..."

# Update .cpanel.yml
sed -i "s/yourusername/$cpanel_username/g" .cpanel.yml

echo "✓ Updated deployment path to: /home/$cpanel_username/public_html/"
echo "✓ Updated repository path to: /home/$cpanel_username/repositories/Gatherly-EMS_2025"

echo ""
echo "=========================================="
echo "Next Steps:"
echo "=========================================="
echo ""
echo "1. Create Git repository in cPanel:"
echo "   - Go to cPanel → Files → Git Version Control"
echo "   - Click 'Create'"
echo "   - Clone URL: $(git config --get remote.origin.url 2>/dev/null || echo 'YOUR_REPO_URL')"
echo "   - Repository Path: /home/$cpanel_username/repositories/Gatherly-EMS_2025"
echo ""
echo "2. Build Tailwind CSS before first deployment:"
echo "   bash deploy-scripts/build-tailwind.sh"
echo ""
echo "3. Commit the .cpanel.yml file:"
echo "   git add .cpanel.yml"
echo "   git commit -m 'Configure cPanel deployment'"
echo ""
echo "4. Push to your repository:"
echo "   git push origin main"
echo ""
echo "5. cPanel will automatically deploy your project!"
echo ""
echo "For detailed instructions, see DEPLOYMENT.md"
echo "=========================================="
