#!/bin/bash
# Build Tailwind CSS for production deployment
# This script compiles Tailwind CSS with minification

echo "Building Tailwind CSS for production..."

# Check if Node.js and npx are available
if ! command -v npx &> /dev/null; then
    echo "Error: npx is not installed. Please install Node.js."
    exit 1
fi

# Check if input.css exists
if [ ! -f "./src/input.css" ]; then
    echo "Error: src/input.css not found!"
    exit 1
fi

# Build Tailwind CSS
npx tailwindcss -i ./src/input.css -o ./src/output.css --minify

if [ $? -eq 0 ]; then
    echo "✓ Tailwind CSS built successfully!"
    echo "✓ Output: src/output.css"
else
    echo "✗ Failed to build Tailwind CSS"
    exit 1
fi
