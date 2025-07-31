#!/bin/bash

# SkyLearn Flashcards - Marketplace Build Script
# This script packages the plugin for different marketplace submissions

set -e

# Configuration
PLUGIN_NAME="skylearn-flashcards"
VERSION="1.0.0"
BUILD_DIR="build"
WORDPRESS_DIR="$BUILD_DIR/wordpress-org"
PREMIUM_DIR="$BUILD_DIR/premium"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}SkyLearn Flashcards Marketplace Build Script${NC}"
echo -e "${BLUE}=============================================${NC}"
echo ""

# Clean previous builds
echo -e "${YELLOW}Cleaning previous builds...${NC}"
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR
mkdir -p $WORDPRESS_DIR
mkdir -p $PREMIUM_DIR

# Run npm build
echo -e "${YELLOW}Building assets...${NC}"
npm run build

# Function to copy core files
copy_core_files() {
    local dest_dir=$1
    local include_premium=$2
    
    echo -e "${YELLOW}Copying core files to $dest_dir...${NC}"
    
    # Core plugin files
    cp skylearn-flashcards.php "$dest_dir/"
    cp readme.txt "$dest_dir/" 2>/dev/null || echo "readme.txt not found, skipping..."
    cp LICENSE "$dest_dir/"
    cp uninstall.php "$dest_dir/"
    
    # Assets (built)
    cp -r assets "$dest_dir/"
    
    # Remove source files from assets
    rm -rf "$dest_dir/assets/js/src" 2>/dev/null || true
    rm -rf "$dest_dir/assets/css/src" 2>/dev/null || true
    rm -rf "$dest_dir/assets/wordpress-org" 2>/dev/null || true
    
    # Core includes
    cp -r includes "$dest_dir/"
    
    # Languages
    cp -r languages "$dest_dir/"
    
    # Documentation
    mkdir -p "$dest_dir/docs"
    cp docs/MARKETPLACE_GUIDE.md "$dest_dir/docs/" 2>/dev/null || true
    cp docs/SUPPORT_TEMPLATE.md "$dest_dir/docs/" 2>/dev/null || true
    cp docs/PRIVACY_POLICY.md "$dest_dir/docs/" 2>/dev/null || true
    cp docs/TERMS_OF_SERVICE.md "$dest_dir/docs/" 2>/dev/null || true
    
    if [ "$include_premium" = false ]; then
        echo -e "${YELLOW}Removing premium features for free version...${NC}"
        # Keep premium directory but add restrictions
        # The plugin will handle premium gating internally
    fi
}

# WordPress.org build
echo -e "${GREEN}Building WordPress.org version...${NC}"
copy_core_files "$WORDPRESS_DIR" false

# Create WordPress.org README.md
cat > "$WORDPRESS_DIR/README.md" << 'EOF'
# SkyLearn Flashcards

This is the WordPress.org version of SkyLearn Flashcards.

## Installation

1. Download the plugin
2. Upload to your WordPress site
3. Activate the plugin
4. Start creating flashcards!

## Documentation

Visit https://skyian.com/skylearn-flashcards/docs/ for complete documentation.

## Support

- WordPress.org: https://wordpress.org/support/plugin/skylearn-flashcards/
- Premium Support: https://skyian.com/support/

## Upgrade to Premium

Unlock advanced features:
- Advanced Analytics
- Lead Collection
- Unlimited Flashcard Sets
- Export Features
- Priority Support

Visit https://skyian.com/skylearn-flashcards/premium/ to upgrade.
EOF

# Premium build
echo -e "${GREEN}Building Premium version...${NC}"
copy_core_files "$PREMIUM_DIR" true

# Add premium-specific files
cat > "$PREMIUM_DIR/README.md" << 'EOF'
# SkyLearn Flashcards Premium

Thank you for purchasing SkyLearn Flashcards Premium!

## Installation

1. Upload the plugin files
2. Activate the plugin
3. Enter your license key in Settings > License
4. Enjoy all premium features!

## Premium Features

- Advanced Analytics & Reporting
- Lead Collection & Email Integration
- Unlimited Flashcard Sets
- Export to PDF/CSV
- Priority Support
- Advanced LMS Integrations

## Documentation

Complete documentation: https://skyian.com/skylearn-flashcards/docs/
Premium tutorials: https://skyian.com/skylearn-flashcards/premium-tutorials/

## Support

Premium users get priority support:
- Email: support@skyian.com
- Ticket system: https://skyian.com/support/
- Response time: 12-24 hours

## License

This is the commercial version. License key required for activation.
EOF

# Add premium documentation
mkdir -p "$PREMIUM_DIR/docs/premium"
cat > "$PREMIUM_DIR/docs/premium/INSTALLATION.md" << 'EOF'
# Premium Installation Guide

## Step 1: Upload Plugin
1. Download the premium ZIP file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the ZIP file and click Install Now
4. Activate the plugin

## Step 2: License Activation
1. Go to SkyLearn Flashcards > Settings > License
2. Enter your license key (from purchase email)
3. Click "Activate License"
4. Verify "License Active" status

## Step 3: Configure Premium Features
1. Enable desired premium features in Settings
2. Configure analytics and reporting
3. Set up lead collection if needed
4. Connect email marketing integrations

## Troubleshooting
- License key not working? Check for typos and trailing spaces
- Already activated? Deactivate on old site first
- Need help? Contact support@skyian.com

## Next Steps
- Check out our video tutorials
- Explore the premium features
- Contact support if you need help
EOF

# Package files
echo -e "${GREEN}Creating packages...${NC}"

# WordPress.org package
cd "$WORDPRESS_DIR"
zip -r "../${PLUGIN_NAME}-${VERSION}-wordpress-org.zip" . -x "*.DS_Store" "*.git*" "node_modules/*" "*.log"
cd ../..

# Premium package
cd "$PREMIUM_DIR"
zip -r "../${PLUGIN_NAME}-${VERSION}-premium.zip" . -x "*.DS_Store" "*.git*" "node_modules/*" "*.log"
cd ../..

# Validation checks
echo -e "${GREEN}Running validation checks...${NC}"

validate_package() {
    local package_path=$1
    local package_name=$2
    
    echo -e "${YELLOW}Validating $package_name...${NC}"
    
    if [ ! -f "$package_path" ]; then
        echo -e "${RED}❌ Package not found: $package_path${NC}"
        return 1
    fi
    
    # Check package size
    local size=$(stat -f%z "$package_path" 2>/dev/null || stat -c%s "$package_path" 2>/dev/null)
    if [ "$size" -lt 100000 ]; then
        echo -e "${RED}❌ Package seems too small: $size bytes${NC}"
        return 1
    fi
    
    echo -e "${GREEN}✅ Package validated: $package_name ($size bytes)${NC}"
    return 0
}

# Validate packages
validate_package "$BUILD_DIR/${PLUGIN_NAME}-${VERSION}-wordpress-org.zip" "WordPress.org"
validate_package "$BUILD_DIR/${PLUGIN_NAME}-${VERSION}-premium.zip" "Premium"

# Generate checksums
echo -e "${GREEN}Generating checksums...${NC}"
cd "$BUILD_DIR"
sha256sum *.zip > checksums.txt
cd ..

# Final report
echo ""
echo -e "${GREEN}Build completed successfully!${NC}"
echo -e "${BLUE}=============================================${NC}"
echo "Packages created:"
echo "  📦 WordPress.org: $BUILD_DIR/${PLUGIN_NAME}-${VERSION}-wordpress-org.zip"
echo "  📦 Premium: $BUILD_DIR/${PLUGIN_NAME}-${VERSION}-premium.zip"
echo "  📄 Checksums: $BUILD_DIR/checksums.txt"
echo ""
echo "Next steps:"
echo "  1. Test both packages on clean WordPress installations"
echo "  2. Submit WordPress.org package to repository"
echo "  3. Upload premium package to sales platform"
echo "  4. Update documentation and marketing materials"
echo ""
echo -e "${YELLOW}Remember to test the packages before submission!${NC}"

# Optional: Open build directory
if command -v open >/dev/null 2>&1; then
    echo ""
    read -p "Open build directory? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        open "$BUILD_DIR"
    fi
fi

echo -e "${GREEN}Build script completed!${NC}"