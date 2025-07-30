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
         * Initialize flashcard core functionality
         */
        init: function() {
            this.setupAnimations();
            this.initAutoplay();
            this.initStudyModes();
            this.initCardEffects();
        },

        /**
         * Setup card flip animations
         */
        setupAnimations: function() {
            // Enhanced flip animation with CSS3
            $('.skylearn-flashcard').each(function() {
                const $card = $(this);
                
                $card.on('flip:start', function() {
                    $card.addClass('flipping');
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