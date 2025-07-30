/**
 * Jest test setup file
 * Configures the testing environment for SkyLearn Flashcards
 *
 * @package SkyLearn_Flashcards
 * @subpackage Tests
 * @since 1.0.0
 */

// Mock jQuery if not available
global.$ = global.jQuery = require('jquery');

// Mock WordPress globals
global.wp = {
  i18n: {
    __: (text) => text,
    _e: (text) => text,
    sprintf: (text, ...args) => text
  },
  ajax: {
    post: jest.fn(),
    get: jest.fn()
  },
  hooks: {
    addAction: jest.fn(),
    addFilter: jest.fn(),
    doAction: jest.fn(),
    applyFilters: jest.fn()
  }
};

// Mock AJAX URL
global.ajaxurl = '/wp-admin/admin-ajax.php';

// Mock skylearn globals
global.skylearn_frontend = {
  ajax_url: '/wp-admin/admin-ajax.php',
  nonce: 'test_nonce_12345',
  strings: {
    flip_card: 'Flip Card',
    next: 'Next',
    previous: 'Previous',
    show_answer: 'Show Answer',
    hide_answer: 'Hide Answer',
    correct: 'Correct',
    incorrect: 'Incorrect',
    score: 'Score',
    time_spent: 'Time Spent',
    restart: 'Restart',
    shuffle: 'Shuffle',
    study_mode: 'Study Mode',
    quiz_mode: 'Quiz Mode'
  },
  settings: {
    animation_speed: 300,
    auto_advance: false,
    show_progress: true,
    enable_sound: false
  }
};

global.skylearn_admin = {
  ajax_url: '/wp-admin/admin-ajax.php',
  nonce: 'test_admin_nonce_12345',
  strings: {
    save: 'Save',
    cancel: 'Cancel',
    delete: 'Delete',
    confirm_delete: 'Are you sure you want to delete this item?',
    saved: 'Saved successfully',
    error: 'An error occurred'
  }
};

// Mock DOM methods
Object.defineProperty(window, 'localStorage', {
  value: {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
  },
  writable: true
});

Object.defineProperty(window, 'sessionStorage', {
  value: {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
  },
  writable: true
});

// Mock CSS transitions and animations
Object.defineProperty(window, 'getComputedStyle', {
  value: () => ({
    transitionDuration: '0.3s',
    animationDuration: '0.3s'
  })
});

// Mock window.matchMedia for responsive tests
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(), // deprecated
    removeListener: jest.fn(), // deprecated
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

// Mock console methods for cleaner test output
global.console = {
  ...console,
  // Uncomment to suppress console outputs during tests
  // log: jest.fn(),
  // warn: jest.fn(),
  // error: jest.fn(),
};

// Setup DOM for each test
beforeEach(() => {
  // Reset DOM
  document.body.innerHTML = '';
  
  // Reset mocks
  jest.clearAllMocks();
  
  // Reset localStorage/sessionStorage
  localStorage.clear();
  sessionStorage.clear();
});

// Cleanup after each test
afterEach(() => {
  // Clean up any timers
  jest.runOnlyPendingTimers();
  jest.useRealTimers();
  
  // Clean up event listeners
  document.body.innerHTML = '';
});

// Global test utilities
global.testUtils = {
  /**
   * Create a mock flashcard element
   */
  createMockFlashcard: (id = 1, front = 'Question', back = 'Answer') => {
    const flashcard = document.createElement('div');
    flashcard.className = 'skylearn-flashcard';
    flashcard.dataset.cardId = id;
    
    const front_el = document.createElement('div');
    front_el.className = 'flashcard-front';
    front_el.textContent = front;
    
    const back_el = document.createElement('div');
    back_el.className = 'flashcard-back';
    back_el.textContent = back;
    
    flashcard.appendChild(front_el);
    flashcard.appendChild(back_el);
    
    return flashcard;
  },
  
  /**
   * Create a mock flashcard set
   */
  createMockFlashcardSet: (cards = []) => {
    const set = document.createElement('div');
    set.className = 'skylearn-flashcard-set';
    set.dataset.setId = '123';
    
    if (cards.length === 0) {
      cards = [
        { id: 1, front: 'Question 1', back: 'Answer 1' },
        { id: 2, front: 'Question 2', back: 'Answer 2' },
        { id: 3, front: 'Question 3', back: 'Answer 3' }
      ];
    }
    
    cards.forEach(card => {
      const flashcard = testUtils.createMockFlashcard(card.id, card.front, card.back);
      set.appendChild(flashcard);
    });
    
    return set;
  },
  
  /**
   * Simulate user interaction
   */
  simulateEvent: (element, eventType, options = {}) => {
    const event = new Event(eventType, { bubbles: true, ...options });
    element.dispatchEvent(event);
    return event;
  },
  
  /**
   * Wait for next tick
   */
  nextTick: () => new Promise(resolve => setTimeout(resolve, 0)),
  
  /**
   * Wait for animation/transition
   */
  waitForTransition: (duration = 300) => new Promise(resolve => setTimeout(resolve, duration))
};