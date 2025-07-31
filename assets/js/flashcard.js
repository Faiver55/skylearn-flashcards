/**
 * SkyLearn Flashcards - Flashcard Core JavaScript
 * ===============================================
 * 
 * Core flashcard functionality and animations
 * 
 * @package SkyLearn_Flashcards
 * @subpackage Assets/JS
 * @author Ferdous Khalifa
 * @copyright 2024 Skyian LLC
 */

(function($) {
    'use strict';

    /**
     * Core flashcard functionality
     */
    const SkyLearnFlashcard = {
        
        // Instance variables
        currentCard: 0,
        totalCards: 0,
        isFlipped: false,
        startTime: null,
        answers: {},
        sessionData: {
            correct: 0,
            incorrect: 0,
            timeSpent: 0
        },
        
        /**
         * Animation settings
         */
        settings: {
            flipDuration: 600,
            slideDuration: 400,
            autoplayInterval: 3000,
            easing: 'ease-in-out'
        },

        /**
         * Initialize flashcard functionality
         */
        init: function(container) {
            const $container = $(container);
            
            if (!$container.length) {
                return;
            }
            
            this.$container = $container;
            this.setId = $container.data('set-id');
            this.totalCards = parseInt($container.data('total-cards')) || 0;
            this.startTime = new Date();
            
            // Initialize components
            this.setupCards();
            this.bindEvents();
            this.initializeSession();
            this.updateProgress();
            
            // Load saved progress from localStorage
            this.loadSession();
        },

        /**
         * Setup card elements and initial state
         */
        setupCards: function() {
            this.$cards = this.$container.find('.skylearn-flashcard');
            this.$progressBar = this.$container.find('.progress-fill');
            this.$currentIndicator = this.$container.find('.current-card');
            this.$totalIndicator = this.$container.find('.total-cards');
            
            // Hide all cards except first
            this.$cards.removeClass('active').eq(0).addClass('active');
            
            // Update indicators
            this.$totalIndicator.text(this.totalCards);
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;
            
            // Card flip events
            this.$container.on('click', '.skylearn-flashcard', function() {
                self.flipCard();
            });
            
            // Navigation events
            this.$container.on('click', '.btn-prev', function() {
                self.previousCard();
            });
            
            this.$container.on('click', '.btn-next', function() {
                self.nextCard();
            });
            
            this.$container.on('click', '.btn-flip', function() {
                self.flipCard();
            });
            
            // Answer tracking events
            this.$container.on('click', '.btn-correct', function() {
                self.markAnswer('correct');
            });
            
            this.$container.on('click', '.btn-incorrect', function() {
                self.markAnswer('incorrect');
            });
            
            // Action controls
            this.$container.on('click', '.btn-shuffle', function() {
                self.shuffleCards();
            });
            
            this.$container.on('click', '.btn-reset', function() {
                self.resetSession();
            });
            
            // Study again
            this.$container.on('click', '.btn-study-again', function() {
                self.studyAgain();
            });
            
            this.$container.on('click', '.btn-shuffle-retry', function() {
                self.shuffleAndRetry();
            });
            
            // Keyboard events
            $(document).on('keydown', function(e) {
                if (!self.$container.is(':visible')) return;
                
                switch(e.keyCode) {
                    case 32: // Space
                        e.preventDefault();
                        self.flipCard();
                        break;
                    case 37: // Left arrow
                        e.preventDefault();
                        self.previousCard();
                        break;
                    case 39: // Right arrow
                        e.preventDefault();
                        self.nextCard();
                        break;
                    case 82: // R key
                        if (e.ctrlKey || e.metaKey) return; // Don't interfere with browser refresh
                        e.preventDefault();
                        self.resetSession();
                        break;
                }
            });
        },

        /**
         * Initialize session data
         */
        initializeSession: function() {
            this.answers = {};
            this.sessionData = {
                correct: 0,
                incorrect: 0,
                timeSpent: 0
            };
        },

        /**
         * Flip current card
         */
        flipCard: function() {
            const $currentCard = this.getCurrentCard();
            
            if (!$currentCard.length) return;
            
            $currentCard.toggleClass('flipped');
            this.isFlipped = $currentCard.hasClass('flipped');
            
            // Show answer tracking after flip
            if (this.isFlipped) {
                this.showAnswerTracking();
            } else {
                this.hideAnswerTracking();
            }
            
            // Track card view
            this.trackCardView(this.currentCard);
        },

        /**
         * Navigate to next card
         */
        nextCard: function() {
            if (this.currentCard < this.totalCards - 1) {
                this.currentCard++;
                this.showCard(this.currentCard);
                this.updateProgress();
                this.updateNavigation();
            } else {
                // Session complete
                this.completeSession();
            }
        },

        /**
         * Navigate to previous card
         */
        previousCard: function() {
            if (this.currentCard > 0) {
                this.currentCard--;
                this.showCard(this.currentCard);
                this.updateProgress();
                this.updateNavigation();
            }
        },

        /**
         * Show specific card
         */
        showCard: function(index) {
            this.$cards.removeClass('active');
            const $targetCard = this.$cards.eq(index);
            $targetCard.addClass('active').removeClass('flipped');
            
            this.isFlipped = false;
            this.hideAnswerTracking();
        },

        /**
         * Get current card element
         */
        getCurrentCard: function() {
            return this.$cards.eq(this.currentCard);
        },

        /**
         * Update progress indicators
         */
        updateProgress: function() {
            const progress = ((this.currentCard + 1) / this.totalCards) * 100;
            
            this.$currentIndicator.text(this.currentCard + 1);
            this.$progressBar.css('width', progress + '%');
        },

        /**
         * Update navigation buttons
         */
        updateNavigation: function() {
            const $prevBtn = this.$container.find('.btn-prev');
            const $nextBtn = this.$container.find('.btn-next');
            
            $prevBtn.prop('disabled', this.currentCard === 0);
            
            if (this.currentCard === this.totalCards - 1) {
                $nextBtn.text(skyleanFlashcards.strings.session_complete);
            } else {
                $nextBtn.text(skyleanFlashcards.strings.next_card);
            }
        },

        /**
         * Show answer tracking
         */
        showAnswerTracking: function() {
            // Only show if card hasn't been answered yet
            if (!this.answers[this.currentCard]) {
                this.$container.find('.skylearn-answer-tracking').slideDown();
            }
        },

        /**
         * Hide answer tracking
         */
        hideAnswerTracking: function() {
            this.$container.find('.skylearn-answer-tracking').slideUp();
        },

        /**
         * Mark answer as correct or incorrect
         */
        markAnswer: function(answer) {
            if (this.answers[this.currentCard]) {
                return; // Already answered
            }
            
            this.answers[this.currentCard] = answer;
            
            if (answer === 'correct') {
                this.sessionData.correct++;
            } else {
                this.sessionData.incorrect++;
            }
            
            this.hideAnswerTracking();
            this.saveSession();
            
            // Automatically advance after short delay
            setTimeout(() => {
                this.nextCard();
            }, 1000);
        },

        /**
         * Shuffle cards
         */
        shuffleCards: function() {
            // Reset to first card
            this.currentCard = 0;
            
            // Shuffle card order (visual shuffle)
            const $cardsWrapper = this.$container.find('.skylearn-cards-wrapper');
            const $cards = $cardsWrapper.children().toArray();
            
            // Fisher-Yates shuffle
            for (let i = $cards.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [$cards[i], $cards[j]] = [$cards[j], $cards[i]];
            }
            
            // Re-append shuffled cards
            $cardsWrapper.empty().append($cards);
            this.$cards = $cardsWrapper.find('.skylearn-flashcard');
            
            // Reset view
            this.showCard(0);
            this.updateProgress();
            this.updateNavigation();
        },

        /**
         * Reset session
         */
        resetSession: function() {
            if (confirm(skyleanFlashcards.strings.confirm_reset || 'Are you sure you want to reset your progress?')) {
                this.currentCard = 0;
                this.initializeSession();
                this.startTime = new Date();
                
                this.showCard(0);
                this.updateProgress();
                this.updateNavigation();
                this.hideSessionSummary();
                
                this.clearSession();
            }
        },

        /**
         * Complete study session
         */
        completeSession: function() {
            const endTime = new Date();
            this.sessionData.timeSpent = Math.round((endTime - this.startTime) / 1000);
            
            this.showSessionSummary();
            this.trackCompletion();
        },

        /**
         * Show session summary
         */
        showSessionSummary: function() {
            const $summary = this.$container.find('.skylearn-session-summary');
            const total = this.sessionData.correct + this.sessionData.incorrect;
            const accuracy = total > 0 ? Math.round((this.sessionData.correct / total) * 100) : 0;
            
            // Update summary stats
            $summary.find('.correct-count').text(this.sessionData.correct);
            $summary.find('.incorrect-count').text(this.sessionData.incorrect);
            $summary.find('.time-taken').text(this.formatTime(this.sessionData.timeSpent));
            $summary.find('.accuracy-rate').text(accuracy + '%');
            
            // Hide main interface and show summary
            this.$container.find('.skylearn-cards-wrapper, .skylearn-controls, .skylearn-answer-tracking').hide();
            $summary.show();
        },

        /**
         * Hide session summary
         */
        hideSessionSummary: function() {
            this.$container.find('.skylearn-session-summary').hide();
            this.$container.find('.skylearn-cards-wrapper, .skylearn-controls').show();
        },

        /**
         * Study again (restart with same order)
         */
        studyAgain: function() {
            this.resetSession();
        },

        /**
         * Shuffle and retry
         */
        shuffleAndRetry: function() {
            this.resetSession();
            this.shuffleCards();
        },

        /**
         * Format time in MM:SS format
         */
        formatTime: function(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        },

        /**
         * Track card view via AJAX
         */
        trackCardView: function(cardIndex) {
            if (!skyleanFlashcards.ajax_url) return;
            
            $.post(skyleanFlashcards.ajax_url, {
                action: 'skylearn_track_card_view',
                nonce: skyleanFlashcards.nonce,
                set_id: this.setId,
                card_index: cardIndex
            });
        },

        /**
         * Track session completion
         */
        trackCompletion: function() {
            if (!skyleanFlashcards.ajax_url) return;
            
            const total = this.sessionData.correct + this.sessionData.incorrect;
            const accuracy = total > 0 ? (this.sessionData.correct / total) : 0;
            
            $.post(skyleanFlashcards.ajax_url, {
                action: 'skylearn_track_completion',
                nonce: skyleanFlashcards.nonce,
                set_id: this.setId,
                accuracy: accuracy
            });
        },

        /**
         * Save session to localStorage
         */
        saveSession: function() {
            const sessionKey = 'skylearn_session_' + this.setId;
            const sessionData = {
                currentCard: this.currentCard,
                answers: this.answers,
                sessionData: this.sessionData,
                startTime: this.startTime.getTime()
            };
            
            try {
                localStorage.setItem(sessionKey, JSON.stringify(sessionData));
            } catch (e) {
                // localStorage not available
            }
        },

        /**
         * Load session from localStorage
         */
        loadSession: function() {
            const sessionKey = 'skylearn_session_' + this.setId;
            
            try {
                const saved = localStorage.getItem(sessionKey);
                if (saved) {
                    const sessionData = JSON.parse(saved);
                    
                    // Only restore if session is recent (within 24 hours)
                    const savedTime = new Date(sessionData.startTime);
                    const now = new Date();
                    const hoursDiff = (now - savedTime) / (1000 * 60 * 60);
                    
                    if (hoursDiff < 24) {
                        this.currentCard = sessionData.currentCard || 0;
                        this.answers = sessionData.answers || {};
                        this.sessionData = sessionData.sessionData || this.sessionData;
                        this.startTime = savedTime;
                        
                        this.showCard(this.currentCard);
                        this.updateProgress();
                        this.updateNavigation();
                    }
                }
            } catch (e) {
                // localStorage not available or invalid data
            }
        },

        /**
         * Clear saved session
         */
        clearSession: function() {
            const sessionKey = 'skylearn_session_' + this.setId;
            
            try {
                localStorage.removeItem(sessionKey);
            } catch (e) {
                // localStorage not available
            }
        }
    };

    /**
     * Initialize all flashcard containers on page load
     */
    $(document).ready(function() {
        $('.skylearn-flashcard-container').each(function() {
            SkyLearnFlashcard.init(this);
        });
    });

    // Make available globally
    window.SkyLearnFlashcard = SkyLearnFlashcard;

})(jQuery);