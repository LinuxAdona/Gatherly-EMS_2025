#!/bin/bash
# Pre-deployment script
# Run this locally before pushing to ensure everything is ready for deployment

echo "=========================================="
echo "Gatherly EMS - Pre-Deployment Checklist"
echo "=========================================="

# Check Git status
echo ""
echo "1. Checking Git status..."
if git diff-index --quiet HEAD --; then
    echo "✓ Working directory is clean"
else
    echo "⚠ You have uncommitted changes"
    git status --short
fi

# Build Tailwind CSS
echo ""
echo "2. Building Tailwind CSS..."
bash deploy-scripts/build-tailwind.sh

# Check if output.css was built
if [ -f "./src/output.css" ]; then
    echo "✓ Tailwind CSS output file exists"
else
    echo "✗ Tailwind CSS output file missing!"
    exit 1
fi

# Test Python syntax
echo ""
echo "3. Checking Python files..."
python_errors=0
for file in ml/*.py; do
    if [ -f "$file" ]; then
        python3 -m py_compile "$file" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo "✓ $file"
        else
            echo "✗ $file has syntax errors"
            python_errors=$((python_errors + 1))
        fi
    fi
done

if [ $python_errors -gt 0 ]; then
    echo "⚠ Found $python_errors Python files with errors"
else
    echo "✓ All Python files are valid"
fi

# Check PHP syntax
echo ""
echo "4. Checking PHP files..."
php_errors=0
for file in $(find public src -name "*.php"); do
    php -l "$file" > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo "✗ $file has syntax errors"
        php_errors=$((php_errors + 1))
    fi
done

if [ $php_errors -gt 0 ]; then
    echo "⚠ Found $php_errors PHP files with errors"
    exit 1
else
    echo "✓ All PHP files are valid"
fi

# Check required files
echo ""
echo "5. Checking required files..."
required_files=(
    ".cpanel.yml"
    "requirements.txt"
    "package.json"
    "tailwind.config.js"
    "src/output.css"
)

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✓ $file"
    else
        echo "✗ $file is missing!"
        exit 1
    fi
done

# Summary
echo ""
echo "=========================================="
echo "✓ Pre-deployment checks passed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Commit your changes: git add . && git commit -m 'Your message'"
echo "2. Push to repository: git push origin main"
echo "3. cPanel will automatically deploy your changes"
echo ""
