#!/usr/bin/env bash

# SkyLearn Flashcards Test Runner
# Simple script to run all tests locally

set -e

echo "🚀 SkyLearn Flashcards Test Suite"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check dependencies
print_status "Checking dependencies..."

if ! command -v php &> /dev/null; then
    print_error "PHP is not installed or not in PATH"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed or not in PATH"
    exit 1
fi

if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed or not in PATH"
    exit 1
fi

if ! command -v npm &> /dev/null; then
    print_error "npm is not installed or not in PATH"
    exit 1
fi

print_success "All dependencies found"

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    print_status "Installing PHP dependencies..."
    composer install --no-interaction
fi

if [ ! -d "node_modules" ]; then
    print_status "Installing Node.js dependencies..."
    npm install
fi

# Run tests based on arguments
case "${1:-all}" in
    "php"|"phpunit")
        print_status "Running PHP unit tests..."
        if ./vendor/bin/phpunit; then
            print_success "PHP tests passed!"
        else
            print_error "PHP tests failed!"
            exit 1
        fi
        ;;
    
    "js"|"javascript"|"jest")
        print_status "Running JavaScript tests..."
        if npm test; then
            print_success "JavaScript tests passed!"
        else
            print_error "JavaScript tests failed!"
            exit 1
        fi
        ;;
    
    "lint")
        print_status "Running code linting..."
        
        # PHP linting
        print_status "Linting PHP code..."
        if composer run-script lint; then
            print_success "PHP linting passed!"
        else
            print_warning "PHP linting found issues"
        fi
        
        # JavaScript linting
        print_status "Linting JavaScript code..."
        if npm run lint:js; then
            print_success "JavaScript linting passed!"
        else
            print_warning "JavaScript linting found issues"
        fi
        
        # CSS linting
        print_status "Linting CSS code..."
        if npm run lint:css; then
            print_success "CSS linting passed!"
        else
            print_warning "CSS linting found issues"
        fi
        ;;
    
    "coverage")
        print_status "Running tests with coverage..."
        
        # PHP coverage
        print_status "Generating PHP coverage..."
        composer run-script test:coverage
        
        # JavaScript coverage
        print_status "Generating JavaScript coverage..."
        npm run test:coverage
        
        print_success "Coverage reports generated!"
        print_status "PHP coverage: coverage/html/index.html"
        print_status "JavaScript coverage: coverage/js/lcov-report/index.html"
        ;;
    
    "build")
        print_status "Running build tests..."
        
        # Build assets
        print_status "Building assets..."
        if npm run build; then
            print_success "Build completed successfully!"
        else
            print_error "Build failed!"
            exit 1
        fi
        
        # Test minification
        print_status "Testing minification..."
        if npm run minify; then
            print_success "Minification completed successfully!"
        else
            print_error "Minification failed!"
            exit 1
        fi
        ;;
    
    "all"|*)
        print_status "Running complete test suite..."
        
        # 1. Linting
        print_status "Step 1/5: Code linting..."
        if composer run-script lint && npm run lint:js && npm run lint:css; then
            print_success "✓ Linting passed"
        else
            print_warning "⚠ Linting found issues (continuing...)"
        fi
        
        # 2. PHP tests
        print_status "Step 2/5: PHP unit tests..."
        if ./vendor/bin/phpunit; then
            print_success "✓ PHP tests passed"
        else
            print_error "✗ PHP tests failed"
            exit 1
        fi
        
        # 3. JavaScript tests
        print_status "Step 3/5: JavaScript tests..."
        if npm test; then
            print_success "✓ JavaScript tests passed"
        else
            print_error "✗ JavaScript tests failed"
            exit 1
        fi
        
        # 4. Build test
        print_status "Step 4/5: Build test..."
        if npm run build; then
            print_success "✓ Build completed"
        else
            print_error "✗ Build failed"
            exit 1
        fi
        
        # 5. Code analysis
        print_status "Step 5/5: Code analysis..."
        if composer run-script analyze; then
            print_success "✓ Code analysis passed"
        else
            print_warning "⚠ Code analysis found issues (continuing...)"
        fi
        
        print_success "🎉 All tests completed successfully!"
        ;;
esac

echo ""
echo "Test run completed at $(date)"
echo "=================================="