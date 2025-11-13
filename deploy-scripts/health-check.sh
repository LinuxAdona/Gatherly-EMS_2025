#!/bin/bash
# Deployment Health Check
# Verifies that your deployment configuration is correct

echo "=========================================="
echo "Gatherly EMS - Deployment Health Check"
echo "=========================================="
echo ""

errors=0
warnings=0

# Check 1: .cpanel.yml exists and is valid
echo "1. Checking .cpanel.yml..."
if [ -f ".cpanel.yml" ]; then
    if grep -q "linuxman" .cpanel.yml; then
        echo "   ⚠ WARNING: .cpanel.yml still contains 'linuxman'"
        echo "   Run: bash deploy-scripts/setup.sh"
        warnings=$((warnings + 1))
    else
        echo "   ✓ .cpanel.yml is configured"
    fi
else
    echo "   ✗ ERROR: .cpanel.yml not found!"
    errors=$((errors + 1))
fi

# Check 2: Git repository
echo ""
echo "2. Checking Git repository..."
if [ -d ".git" ]; then
    echo "   ✓ Git repository initialized"
    
    # Check for remote
    if git remote -v | grep -q "origin"; then
        echo "   ✓ Git remote configured"
        remote_url=$(git config --get remote.origin.url)
        echo "   → Remote: $remote_url"
    else
        echo "   ⚠ WARNING: No git remote configured"
        warnings=$((warnings + 1))
    fi
else
    echo "   ✗ ERROR: Not a git repository!"
    errors=$((errors + 1))
fi

# Check 3: Required files
echo ""
echo "3. Checking required files..."
required_files=(
    "requirements.txt"
    "package.json"
    "tailwind.config.js"
    "public/.htaccess"
)

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✓ $file"
    else
        echo "   ✗ ERROR: $file missing!"
        errors=$((errors + 1))
    fi
done

# Check 4: Tailwind CSS
echo ""
echo "4. Checking Tailwind CSS..."
if [ -f "src/input.css" ]; then
    echo "   ✓ src/input.css exists"
else
    echo "   ✗ ERROR: src/input.css not found!"
    errors=$((errors + 1))
fi

if [ -f "src/output.css" ]; then
    echo "   ✓ src/output.css exists"
else
    echo "   ⚠ WARNING: src/output.css not built"
    echo "   Run: bash deploy-scripts/build-tailwind.sh"
    warnings=$((warnings + 1))
fi

# Check 5: Python files
echo ""
echo "5. Checking Python ML system..."
python_files=("ml/conversational_planner.py" "ml/venue_recommender.py")
for file in "${python_files[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✓ $file"
    else
        echo "   ⚠ WARNING: $file not found"
        warnings=$((warnings + 1))
    fi
done

# Check 6: PHP files
echo ""
echo "6. Checking PHP files..."
if [ -d "public/pages" ]; then
    php_count=$(find public -name "*.php" | wc -l)
    echo "   ✓ Found $php_count PHP files"
else
    echo "   ✗ ERROR: public/pages directory not found!"
    errors=$((errors + 1))
fi

# Check 7: Database schema
echo ""
echo "7. Checking database files..."
if [ -f "db/sad_db.sql" ]; then
    echo "   ✓ Database schema exists"
else
    echo "   ⚠ WARNING: db/sad_db.sql not found"
    warnings=$((warnings + 1))
fi

# Check 8: Deployment scripts
echo ""
echo "8. Checking deployment scripts..."
deploy_scripts=("setup.sh" "build-tailwind.sh" "install-python-deps.sh" "pre-deploy.sh")
for script in "${deploy_scripts[@]}"; do
    if [ -f "deploy-scripts/$script" ]; then
        if [ -x "deploy-scripts/$script" ]; then
            echo "   ✓ $script (executable)"
        else
            echo "   ⚠ WARNING: $script not executable"
            echo "   Run: chmod +x deploy-scripts/$script"
            warnings=$((warnings + 1))
        fi
    else
        echo "   ✗ ERROR: $script not found!"
        errors=$((errors + 1))
    fi
done

# Check 9: .gitignore
echo ""
echo "9. Checking .gitignore..."
if [ -f ".gitignore" ]; then
    echo "   ✓ .gitignore exists"
    if grep -q "\.env" .gitignore; then
        echo "   ✓ .env files are ignored"
    else
        echo "   ⚠ WARNING: .env not in .gitignore"
        warnings=$((warnings + 1))
    fi
else
    echo "   ⚠ WARNING: .gitignore not found"
    warnings=$((warnings + 1))
fi

# Check 10: Node modules
echo ""
echo "10. Checking Node.js dependencies..."
if [ -d "node_modules" ]; then
    echo "   ✓ node_modules exists"
else
    echo "   ⚠ WARNING: node_modules not found"
    echo "   Run: npm install"
    warnings=$((warnings + 1))
fi

# Summary
echo ""
echo "=========================================="
echo "Health Check Summary"
echo "=========================================="

if [ $errors -eq 0 ] && [ $warnings -eq 0 ]; then
    echo "✅ All checks passed! Ready for deployment."
    echo ""
    echo "Next steps:"
    echo "1. Run: bash deploy-scripts/setup.sh (if not done)"
    echo "2. Commit: git add . && git commit -m 'Setup deployment'"
    echo "3. Push: git push origin main"
elif [ $errors -eq 0 ]; then
    echo "⚠️  $warnings warning(s) found"
    echo "You can proceed, but fix warnings for best results."
else
    echo "❌ $errors error(s) and $warnings warning(s) found"
    echo "Fix errors before deploying!"
    exit 1
fi

echo "=========================================="
