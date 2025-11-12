#!/bin/bash
# Install Python dependencies for ML system
# This script installs required Python packages

echo "Installing Python dependencies..."

# Check if requirements.txt exists
if [ ! -f "./requirements.txt" ]; then
    echo "Error: requirements.txt not found!"
    exit 1
fi

# Try different Python/pip commands
if command -v pip3 &> /dev/null; then
    echo "Using pip3..."
    pip3 install --user -r requirements.txt
elif command -v pip &> /dev/null; then
    echo "Using pip..."
    pip install --user -r requirements.txt
elif command -v python3 &> /dev/null; then
    echo "Using python3 -m pip..."
    python3 -m pip install --user -r requirements.txt
else
    echo "Error: pip is not installed. Please install Python pip."
    exit 1
fi

if [ $? -eq 0 ]; then
    echo "✓ Python dependencies installed successfully!"
else
    echo "✗ Failed to install Python dependencies"
    exit 1
fi
