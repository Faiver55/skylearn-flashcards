# SkyLearn Flashcards Testing Infrastructure

This document provides comprehensive information about the testing infrastructure implemented for the SkyLearn Flashcards WordPress plugin as part of **PHASE 8: Testing & QA**.

## 🏗️ Architecture Overview

The testing infrastructure follows modern testing best practices with a multi-layer approach:

```
Testing Architecture
├── Unit Tests (70%)
│   ├── PHP (PHPUnit)
│   └── JavaScript (Jest)
├── Integration Tests (20%)
│   ├── Database Integration
│   ├── WordPress Integration
│   └── LMS Integration
└── End-to-End Tests (10%)
    ├── Manual Testing
    ├── Accessibility Testing
    └── Performance Testing
```

## 📁 Directory Structure

```
tests/
├── phpunit/                 # PHP unit tests
│   ├── bootstrap.php        # Test bootstrap and mocking
│   ├── core/               # Core functionality tests
│   │   ├── SkyLearnFlashcardTest.php
│   │   └── HelperFunctionsTest.php
│   ├── admin/              # Admin interface tests
│   │   └── AdminTest.php
│   ├── frontend/           # Frontend functionality tests
│   │   └── FrontendTest.php
│   ├── premium/            # Premium features tests
│   │   └── PremiumFeaturesTest.php
│   ├── lms/                # LMS integration tests
│   │   └── LMSIntegrationTest.php
│   └── integration/        # Integration tests
│       └── IntegrationTest.php
├── js/                     # JavaScript tests
│   ├── setup.js           # Jest setup and utilities
│   ├── flashcard.test.js  # Core flashcard functionality
│   └── admin.test.js      # Admin interface tests
└── results/               # Test results and reports
```

## 🚀 Quick Start

### Prerequisites

- PHP 7.4+ with required extensions
- Node.js 14+ and npm 6+
- Composer 2.0+
- MySQL/MariaDB (for integration tests)

### Installation

```bash
# Install PHP dependencies
composer install --dev

# Install Node.js dependencies
npm install

# Make test scripts executable
chmod +x bin/run-tests.sh
chmod +x bin/install-wp-tests.sh
```

### Running Tests

```bash
# Run all tests
./bin/run-tests.sh

# Run specific test suites
./bin/run-tests.sh php        # PHP tests only
./bin/run-tests.sh js         # JavaScript tests only
./bin/run-tests.sh lint       # Code linting only
./bin/run-tests.sh coverage   # Generate coverage reports
./bin/run-tests.sh build      # Build and minification tests
```

### Manual Commands

```bash
# PHP tests
./vendor/bin/phpunit                    # All PHP tests
./vendor/bin/phpunit --coverage-html coverage/html  # With coverage

# JavaScript tests
npm test                                # All JS tests
npm run test:watch                      # Watch mode
npm run test:coverage                   # With coverage

# Linting
composer run-script lint                # PHP linting
npm run lint:js                         # JavaScript linting
npm run lint:css                        # CSS linting

# Code analysis
composer run-script analyze             # PHP static analysis
```

## 🧪 Test Suites

### PHP Unit Tests

#### Core Tests (`tests/phpunit/core/`)
- **SkyLearnFlashcardTest.php**: Main plugin class functionality
- **HelperFunctionsTest.php**: Utility and helper functions

Coverage includes:
- Plugin instantiation and initialization
- Configuration and constants validation
- Data sanitization and validation
- Color scheme and branding functions
- User capability checking

#### Admin Tests (`tests/phpunit/admin/`)
- **AdminTest.php**: Admin interface functionality

Coverage includes:
- Admin class initialization
- Settings management
- Form handling and validation
- AJAX handlers
- Capability checking
- Nonce verification

#### Frontend Tests (`tests/phpunit/frontend/`)
- **FrontendTest.php**: Public-facing functionality

Coverage includes:
- Frontend class initialization
- Shortcode functionality
- Asset enqueuing
- Performance tracking
- Lead capture
- Input sanitization
- Responsive design elements
- Accessibility features

#### Premium Tests (`tests/phpunit/premium/`)
- **PremiumFeaturesTest.php**: Premium functionality

Coverage includes:
- License validation and management
- Feature gating
- Advanced reporting
- Bulk export functionality
- Lead capture premium features
- API communication
- Data encryption
- Cache management

#### LMS Integration Tests (`tests/phpunit/lms/`)
- **LMSIntegrationTest.php**: LMS platform integrations

Coverage includes:
- LearnDash integration
- TutorLMS integration
- Progress tracking
- Grade passback
- Course enrollment checking
- Certificate integration
- Shortcode integration
- Permission management
- Data synchronization
- Webhook handling
- Fallback behavior

#### Integration Tests (`tests/phpunit/integration/`)
- **IntegrationTest.php**: End-to-end workflow testing

Coverage includes:
- Complete plugin workflow
- Shortcode functionality
- AJAX workflows
- Database operations
- Settings management
- User progress tracking
- Error handling
- Performance testing

### JavaScript Tests

#### Core Flashcard Tests (`tests/js/flashcard.test.js`)
- Flashcard initialization
- Card navigation and flipping
- Progress tracking
- Session management
- Keyboard navigation
- Responsive behavior
- Touch interactions (mobile)

#### Admin Interface Tests (`tests/js/admin.test.js`)
- Settings management
- Flashcard editor
- Import/export functionality
- Bulk actions
- Tab navigation
- Form validation
- AJAX error handling

### Test Configuration

#### PHPUnit Configuration (`phpunit.xml`)
```xml
<phpunit bootstrap="tests/phpunit/bootstrap.php">
    <testsuites>
        <testsuite name="SkyLearn Flashcards Core Tests">
            <directory>./tests/phpunit/core/</directory>
        </testsuite>
        <!-- Additional test suites -->
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">./includes/</directory>
        </include>
    </coverage>
</phpunit>
```

#### Jest Configuration (`jest.config.js`)
```javascript
module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/tests/js/setup.js'],
  collectCoverageFrom: [
    'assets/js/**/*.js',
    '!assets/js/**/*.min.js'
  ]
};
```

## 📊 Coverage Requirements

### Minimum Coverage Targets
- **Overall**: 85%
- **Core Functions**: 95%
- **Admin Functions**: 90%
- **Frontend Functions**: 90%
- **Premium Features**: 85%
- **LMS Integrations**: 80%

### Coverage Reports

Coverage reports are generated in multiple formats:
- **HTML**: `coverage/html/index.html` (PHP), `coverage/js/lcov-report/index.html` (JS)
- **Text**: `coverage/coverage.txt`
- **XML**: `coverage/clover.xml`
- **LCOV**: `coverage/js/lcov.info`

## 🔧 Continuous Integration

### GitHub Actions Workflow (`.github/workflows/test.yml`)

The CI pipeline includes:

1. **PHP Tests**: Multiple PHP/WordPress version combinations
2. **JavaScript Tests**: Multiple Node.js versions
3. **Security Scanning**: Dependency vulnerability checks
4. **Build Testing**: Asset compilation and minification
5. **Accessibility Testing**: Automated a11y audits
6. **Integration Testing**: Full WordPress environment tests

### Matrix Testing

#### PHP/WordPress Combinations
- PHP: 7.4, 8.0, 8.1, 8.2
- WordPress: 5.0, 5.9, 6.0, 6.4

#### Node.js Versions
- Node.js: 16, 18, 20

## 🎯 Manual Testing

### QA Checklist (`docs/QA_CHECKLIST.md`)

Comprehensive manual testing checklist covering:
- Core functionality (22 test categories)
- Admin interface
- Premium features
- LMS integrations
- Cross-browser compatibility
- Accessibility compliance
- Performance benchmarks
- Security validation

### Testing Procedures

1. **Pre-testing Setup**
2. **Core Functionality Tests**
3. **Admin Interface Tests**
4. **Premium Features Tests**
5. **LMS Integration Tests**
6. **Cross-browser Tests**
7. **Accessibility Tests**
8. **Performance Tests**
9. **Security Tests**
10. **Final QA Sign-off**

## 🎨 Visual Testing

### Testing CSS (`assets/css/testing.css`)

Visual indicators for testing environments:
- Test mode banners
- Element highlighting
- Debug panels
- Performance metrics
- Accessibility indicators
- Responsive design markers

## 🐛 Bug Reporting

### Severity Levels
- **Critical**: Plugin broken, data loss, security breach
- **High**: Major feature broken, significant UX issue
- **Medium**: Minor feature issue, cosmetic problem
- **Low**: Enhancement request, trivial issue

### Bug Report Template
```markdown
**Environment:** WordPress X.X, Plugin X.X, PHP X.X, Browser
**Steps to Reproduce:** 1. 2. 3.
**Expected:** [What should happen]
**Actual:** [What actually happens]
**Screenshots:** [If applicable]
```

## 📈 Performance Testing

### Metrics Tracked
- Database query performance
- JavaScript execution time
- CSS rendering performance
- Memory usage
- Load times
- Core Web Vitals

### Load Testing Scenarios
- 1000+ flashcard sets
- 100+ concurrent users
- Large datasets
- High-traffic conditions

## ♿ Accessibility Testing

### Automated Testing
- axe-core integration
- Lighthouse accessibility audits
- WordPress accessibility standards

### Manual Testing
- Screen reader compatibility (NVDA, JAWS)
- Keyboard navigation
- Color contrast validation
- High contrast mode
- Browser zoom testing (up to 200%)

## 🔒 Security Testing

### Automated Scans
- Dependency vulnerability scanning
- Static code analysis
- SARIF reporting for GitHub Security

### Manual Security Testing
- Input validation (XSS prevention)
- CSRF protection
- SQL injection prevention
- File upload security
- User permission validation

## 📚 Documentation

### Test Documentation
- **TEST_PLAN.md**: Comprehensive testing procedures
- **QA_CHECKLIST.md**: Manual testing checklist
- **README_TESTING.md**: This document

### Code Documentation
- Well-commented test code
- Inline documentation
- PHPDoc blocks
- JSDoc comments

## 🛠️ Troubleshooting

### Common Issues

#### PHPUnit Issues
```bash
# WordPress test environment not found
bin/install-wp-tests.sh wp_test root password localhost latest

# Memory limit issues
php -d memory_limit=512M vendor/bin/phpunit
```

#### Jest Issues
```bash
# Clear Jest cache
npm test -- --clearCache

# Update snapshots
npm test -- --updateSnapshot
```

#### Database Issues
```bash
# Reset test database
mysql -u root -p -e "DROP DATABASE wp_test; CREATE DATABASE wp_test;"
```

### Debug Mode

Enable debug mode for detailed test output:
```bash
# PHP debugging
XDEBUG_MODE=debug ./vendor/bin/phpunit --debug

# JavaScript debugging
npm test -- --verbose
```

## 🎉 Best Practices

### Writing Tests
1. **Arrange-Act-Assert** pattern
2. **Single assertion per test** (when possible)
3. **Descriptive test names**
4. **Comprehensive mocking**
5. **Edge case coverage**

### Test Organization
1. **Logical grouping** by functionality
2. **Consistent naming** conventions
3. **Shared setup** via `setUp()` methods
4. **Clean teardown** via `tearDown()` methods

### Maintenance
1. **Regular test updates** with code changes
2. **Dependency updates** for security
3. **Coverage monitoring** for regressions
4. **Performance benchmarking**

## 📞 Support

For questions about the testing infrastructure:
1. Check this documentation
2. Review test code comments
3. Check GitHub issues
4. Contact the development team

---

**Last Updated**: January 2024  
**Version**: 1.0.0  
**Maintained By**: SkyLearn Development Team