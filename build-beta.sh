#!/bin/bash

# SkyLearn Flashcards Beta Package Builder
# Creates a distribution-ready beta package

echo "🧪 Building SkyLearn Flashcards Beta Package..."
echo "=================================================="

# Check if we're in the right directory
if [ ! -f "skylearn-flashcards.php" ]; then
    echo "❌ Error: Not in plugin directory. Please run from plugin root."
    exit 1
fi

# Get version info
VERSION=$(grep "Version:" skylearn-flashcards.php | sed 's/.*Version: *//')
echo "📦 Package Version: $VERSION"

# Check if it's a beta version
if [[ $VERSION != *"beta"* ]]; then
    echo "⚠️  Warning: This doesn't appear to be a beta version"
    echo "   Current version: $VERSION"
    read -p "   Continue anyway? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Aborted"
        exit 1
    fi
fi

# Create temp directory for package
PACKAGE_DIR="skylearn-flashcards-beta"
TEMP_DIR="/tmp/$PACKAGE_DIR"

echo "🧹 Cleaning up previous builds..."
rm -rf "$TEMP_DIR"
rm -f "skylearn-flashcards-beta.zip"

# Create package directory
echo "📁 Creating package directory..."
mkdir -p "$TEMP_DIR"

# Copy plugin files
echo "📋 Copying plugin files..."
rsync -av \
    --exclude='node_modules/' \
    --exclude='.git*' \
    --exclude='*.json' \
    --exclude='*.md' \
    --exclude='tests/' \
    --exclude='webpack.config.js' \
    --exclude='.github/' \
    --exclude='bin/' \
    --exclude='*.log' \
    --exclude='*.tmp' \
    ./ "$TEMP_DIR/"

# Copy beta-specific documentation
echo "📖 Adding beta documentation..."
cp BETA_README.md "$TEMP_DIR/README_BETA.md"

# Create package info file
echo "ℹ️  Creating package info..."
cat > "$TEMP_DIR/BETA_PACKAGE_INFO.txt" << EOF
SkyLearn Flashcards Beta Package
================================

Package Version: $VERSION
Build Date: $(date)
Build System: $(uname -s)
Plugin Files: $(find "$TEMP_DIR" -name "*.php" | wc -l) PHP files
Asset Files: $(find "$TEMP_DIR/assets" -type f | wc -l) asset files

Installation Instructions:
1. Upload this ZIP file via WordPress admin (Plugins > Add New > Upload)
2. Activate the plugin
3. Follow the beta welcome wizard
4. Provide feedback via Flashcards > Beta Feedback

Beta Resources:
- Beta Guide: docs/ONBOARDING.md
- Feedback Template: docs/FEEDBACK_TEMPLATE.md
- Support: support@skyian.com

Thank you for beta testing!
EOF

# Create the ZIP package
echo "🗜️  Creating ZIP package..."
cd /tmp
zip -r "skylearn-flashcards-beta.zip" "$PACKAGE_DIR/" -q

# Move back to original directory
cd - > /dev/null
mv "/tmp/skylearn-flashcards-beta.zip" ./

# Cleanup
rm -rf "$TEMP_DIR"

# Package info
PACKAGE_SIZE=$(du -h "skylearn-flashcards-beta.zip" | cut -f1)
FILE_COUNT=$(unzip -l "skylearn-flashcards-beta.zip" | tail -1 | awk '{print $2}')

echo ""
echo "✅ Beta package created successfully!"
echo "   📦 File: skylearn-flashcards-beta.zip"
echo "   📏 Size: $PACKAGE_SIZE"
echo "   📄 Files: $FILE_COUNT"
echo ""
echo "🚀 Ready for distribution!"
echo ""
echo "Next steps:"
echo "1. Test the package on a fresh WordPress install"
echo "2. Send to beta testers with installation instructions"
echo "3. Monitor feedback via support@skyian.com"
echo ""
echo "Beta testing resources:"
echo "- Installation guide included in package"
echo "- Feedback form: Flashcards > Beta Feedback"
echo "- Support email: support@skyian.com"
echo ""
echo "🧪 Happy beta testing!"