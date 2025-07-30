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
                });
                
                $card.on('flip:complete', function() {
                    $card.removeClass('flipping');
                });
            });
        },

        /**
         * Initialize autoplay functionality
         */
        initAutoplay: function() {
            $('.skylearn-autoplay').on('click', function() {
                const $set = $(this).closest('.skylearn-flashcard-set');
                const isActive = $set.data('autoplay');
                
                if (isActive) {
                    SkyLearnFlashcard.stopAutoplay($set);
                } else {
                    SkyLearnFlashcard.startAutoplay($set);
                }
            });
        },

        /**
         * Start autoplay for a set
         */
        startAutoplay: function($set) {
            const interval = this.settings.autoplayInterval;
            
            const autoplayTimer = setInterval(function() {
                const $nextBtn = $set.find('.skylearn-control-btn[data-action="next"]');
                if ($nextBtn.length) {
                    $nextBtn.click();
                } else {
                    SkyLearnFlashcard.stopAutoplay($set);
                }
            }, interval);
            
            $set.data('autoplay', true);
            $set.data('autoplay-timer', autoplayTimer);
            $set.find('.skylearn-autoplay').addClass('active').text('Stop Auto');
        },

        /**
         * Stop autoplay for a set
         */
        stopAutoplay: function($set) {
            const timer = $set.data('autoplay-timer');
            if (timer) {
                clearInterval(timer);
            }
            
            $set.data('autoplay', false);
            $set.removeData('autoplay-timer');
            $set.find('.skylearn-autoplay').removeClass('active').text('Auto Play');
        },

        /**
         * Initialize study modes
         */
        initStudyModes: function() {
            $('.skylearn-study-mode').on('click', function() {
                const mode = $(this).data('mode');
                const $set = $(this).closest('.skylearn-flashcard-set');
                
                SkyLearnFlashcard.setStudyMode($set, mode);
            });
        },

        /**
         * Set study mode for flashcard set
         */
        setStudyMode: function($set, mode) {
            $set.removeClass('mode-normal mode-quiz mode-review mode-random');
            $set.addClass('mode-' + mode);
            $set.data('study-mode', mode);
            
            switch(mode) {
                case 'quiz':
                    this.initQuizMode($set);
                    break;
                case 'review':
                    this.initReviewMode($set);
                    break;
                case 'random':
                    this.initRandomMode($set);
                    break;
                default:
                    this.initNormalMode($set);
            }
        },

        /**
         * Initialize normal study mode
         */
        initNormalMode: function($set) {
            // Standard linear progression through cards
            $set.find('.skylearn-quiz-controls').hide();
            $set.find('.skylearn-standard-controls').show();
        },

        /**
         * Initialize quiz mode
         */
        initQuizMode: function($set) {
            // Hide answers initially, show multiple choice or input
            $set.find('.skylearn-standard-controls').hide();
            $set.find('.skylearn-quiz-controls').show();
            
            // Add quiz-specific styling
            $set.find('.skylearn-flashcard').addClass('quiz-mode');
        },

        /**
         * Initialize review mode
         */
        initReviewMode: function($set) {
            // Only show cards marked as unknown
            const knownCards = $set.data('known-cards') || [];
            const totalCards = $set.data('total-cards');
            
            const unknownCards = [];
            for (let i = 0; i < totalCards; i++) {
                if (!knownCards.includes(i)) {
                    unknownCards.push(i);
                }
            }
            
            if (unknownCards.length === 0) {
                this.showMessage($set, 'All cards mastered! Great job!');
                return;
            }
            
            $set.data('review-cards', unknownCards);
            $set.data('current-review', 0);
            this.showReviewCard($set, 0);
        },

        /**
         * Show review card
         */
        showReviewCard: function($set, reviewIndex) {
            const reviewCards = $set.data('review-cards') || [];
            const cardIndex = reviewCards[reviewIndex];
            
            if (cardIndex !== undefined) {
                // Use the frontend method to show the card
                if (window.SkyLearnFrontend) {
                    window.SkyLearnFrontend.showCard($set, cardIndex);
                }
            }
        },

        /**
         * Initialize random mode
         */
        initRandomMode: function($set) {
            // Randomize card order each time
            const totalCards = $set.data('total-cards');
            const randomOrder = this.shuffleArray([...Array(totalCards).keys()]);
            
            $set.data('random-order', randomOrder);
            $set.data('random-index', 0);
            
            this.showRandomCard($set, 0);
        },

        /**
         * Show random card
         */
        showRandomCard: function($set, randomIndex) {
            const randomOrder = $set.data('random-order') || [];
            const cardIndex = randomOrder[randomIndex];
            
            if (cardIndex !== undefined) {
                // Use the frontend method to show the card
                if (window.SkyLearnFrontend) {
                    window.SkyLearnFrontend.showCard($set, cardIndex);
                }
            }
        },

        /**
         * Initialize card visual effects
         */
        initCardEffects: function() {
            // Card hover effects
            $('.skylearn-flashcard').hover(
                function() {
                    $(this).addClass('hover-effect');
                },
                function() {
                    $(this).removeClass('hover-effect');
                }
            );

            // Card focus effects for accessibility
            $('.skylearn-flashcard').focus(function() {
                $(this).addClass('focus-effect');
            }).blur(function() {
                $(this).removeClass('focus-effect');
            });
        },

        /**
         * Enhanced card flip with callbacks
         */
        flipCard: function($card, callback) {
            $card.trigger('flip:start');
            
            $card.addClass('flipping');
            
            setTimeout(function() {
                $card.toggleClass('flipped');
                
                setTimeout(function() {
                    $card.removeClass('flipping');
                    $card.trigger('flip:complete');
                    
                    if (typeof callback === 'function') {
                        callback();
                    }
                }, SkyLearnFlashcard.settings.flipDuration / 2);
            }, SkyLearnFlashcard.settings.flipDuration / 2);
        },

        /**
         * Smooth card transition
         */
        transitionCard: function($currentCard, $nextCard, direction, callback) {
            direction = direction || 'left';
            
            const slideDistance = direction === 'left' ? '-100%' : '100%';
            const slideDistanceReverse = direction === 'left' ? '100%' : '-100%';
            
            // Position next card
            $nextCard.css('transform', 'translateX(' + slideDistanceReverse + ')').show();
            
            // Animate current card out
            $currentCard.animate({
                transform: 'translateX(' + slideDistance + ')',
                opacity: 0
            }, this.settings.slideDuration);
            
            // Animate next card in
            $nextCard.animate({
                transform: 'translateX(0)',
                opacity: 1
            }, this.settings.slideDuration, function() {
                $currentCard.hide().css({
                    transform: 'translateX(0)',
                    opacity: 1
                });
                
                if (typeof callback === 'function') {
                    callback();
                }
            });
        },

        /**
         * Show message overlay
         */
        showMessage: function($set, message, type) {
            type = type || 'info';
            
            const messageHtml = `
                <div class="skylearn-message-overlay skylearn-message-${type}">
                    <div class="skylearn-message-content">
                        <p>${message}</p>
                        <button class="skylearn-message-close">OK</button>
                    </div>
                </div>
            `;
            
            $set.append(messageHtml);
            
            // Handle close button
            $set.find('.skylearn-message-close').on('click', function() {
                $(this).closest('.skylearn-message-overlay').fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Utility: Shuffle array
         */
        shuffleArray: function(array) {
            const newArray = [...array];
            for (let i = newArray.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
            }
            return newArray;
        },

        /**
         * Utility: Get random integer
         */
        getRandomInt: function(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        },

        /**
         * Card performance analytics
         */
        trackCardPerformance: function(setId, cardIndex, action, timeSpent) {
            if (typeof skylearn_frontend === 'undefined') return;
            
            const data = {
                action: 'skylearn_track_performance',
                set_id: setId,
                card_index: cardIndex,
                user_action: action,
                time_spent: timeSpent,
                nonce: skylearn_frontend.nonce
            };
            
            $.post(skylearn_frontend.ajax_url, data);
        },

        /**
         * Adaptive learning algorithm (basic implementation)
         */
        calculateNextCard: function($set) {
            const knownCards = $set.data('known-cards') || [];
            const totalCards = $set.data('total-cards');
            const studyMode = $set.data('study-mode');
            
            // Simple adaptive logic - prioritize unknown cards
            const unknownCards = [];
            for (let i = 0; i < totalCards; i++) {
                if (!knownCards.includes(i)) {
                    unknownCards.push(i);
                }
            }
            
            if (unknownCards.length === 0) {
                // All cards known, cycle through all
                return this.getRandomInt(0, totalCards - 1);
            }
            
            // Return random unknown card
            return unknownCards[this.getRandomInt(0, unknownCards.length - 1)];
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        SkyLearnFlashcard.init();
    });

    // Make SkyLearnFlashcard globally available
    window.SkyLearnFlashcard = SkyLearnFlashcard;

})(jQuery);

// Placeholder for future flashcard core functionality