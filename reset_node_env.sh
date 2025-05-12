#!/bin/bash

echo "🚀 Starting Node.js environment reset..."

# Step 1: Remove existing node_modules and package-lock.json
echo "🗑️ Removing node_modules and package-lock.json..."
sudo rm -rf node_modules package-lock.json

# Step 2: Clear npm cache
echo "🧹 Clearing npm cache..."
npm cache clean --force

# Step 3: Update system packages
echo "🔄 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Step 4: Install required dependencies for GTK and Cairo (Linux compatibility)
echo "📦 Installing dependencies..."
sudo apt install -y libcairo2-dev libjpeg-dev libpango1.0-dev libgif-dev build-essential

# Step 5: Install GTK runtime (for Windows, ensure it's installed separately)
# Verify GTK installation and add to PATH
echo "🖼️ Verifying GTK installation..."
if [ -d "/mnt/c/Program Files/GTK3-Runtime Win64/bin" ]; then
  export PATH=$PATH:"/mnt/c/Program Files/GTK3-Runtime Win64/bin"
  echo "✅ GTK detected and added to PATH."
else
  echo "⚠️ GTK not found in Windows. Ensure it's installed correctly."
fi

# Step 6: Reinstall project dependencies
echo "📦 Reinstalling dependencies..."
npm install
npm install lru-cache@latest eslint@latest @eslint/config-array@latest @eslint/object-schema@latest glob@latest rimraf@latest --save-dev

# Step 7: Install canvas with fallback build
echo "🎨 Installing canvas..."
npm install --build-from-source canvas --verbose

echo "✅ Reset completed! Restart your system before running tests."
echo "🔄 Restarting system..."
sudo shutdown -r now