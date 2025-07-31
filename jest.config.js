module.exports = {
  // Test environment
  testEnvironment: 'jsdom',
  
  // Setup files
  setupFilesAfterEnv: ['<rootDir>/tests/js/setup.js'],
  
  // Test file patterns
  testMatch: [
    '<rootDir>/tests/js/**/*.test.js',
    '<rootDir>/tests/js/**/*.spec.js'
  ],
  
  // Coverage configuration
  collectCoverage: true,
  coverageDirectory: 'coverage/js',
  collectCoverageFrom: [
    'assets/js/**/*.js',
    '!assets/js/**/*.min.js',
    '!assets/js/**/*.test.js',
    '!**/node_modules/**'
  ],
  coverageReporters: ['html', 'text', 'lcov'],
  
  // Module paths
  moduleNameMapping: {
    '^@/(.*)$': '<rootDir>/assets/js/$1'
  },
  
  // Transform files
  transform: {
    '^.+\\.js$': 'babel-jest'
  },
  
  // Ignore patterns
  testPathIgnorePatterns: [
    '<rootDir>/node_modules/',
    '<rootDir>/vendor/'
  ],
  
  // Global setup
  globals: {
    // WordPress globals
    wp: {},
    ajaxurl: 'admin-ajax.php',
    skylearn_frontend: {
      ajax_url: 'admin-ajax.php',
      nonce: 'test_nonce',
      strings: {
        flip_card: 'Flip Card',
        next: 'Next',
        previous: 'Previous',
        show_answer: 'Show Answer',
        hide_answer: 'Hide Answer'
      }
    },
    skylearn_admin: {
      ajax_url: 'admin-ajax.php',
      nonce: 'test_nonce'
    }
  },
  
  // Verbose output
  verbose: true,
  
  // Test timeout
  testTimeout: 10000
};