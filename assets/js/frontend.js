/**
 * SkyLearn Flashcards - Frontend JavaScript
 * =========================================
 * 
 * Frontend JavaScript for SkyLearn Flashcards plugin
 * 
 * @package SkyLearn_Flashcards
 * @subpackage Assets/JS
 * @author Ferdous Khalifa
 * @copyright 2024 Skyian LLC
 */

(function($) {
    'use strict';

    /**
     * Frontend functionality for SkyLearn Flashcards
     */
    const SkyLearnFrontend = {
        
        /**
         * Initialize frontend functionality
         */
        init: function() {
            this.bindEvents();
            this.initFlashcardSets();
            this.initKeyboardNavigation();
            this.initTouchGestures();
            this.initAccessibility();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Flashcard interactions
            $(document).on('click', '.skylearn-flashcard', this.flipCard);
            $(document).on('click', '.skylearn-control-btn[data-action="next"]', this.nextCard);
            $(document).on('click', '.skylearn-control-btn[data-action="prev"]', this.prevCard);
            $(document).on('click', '.skylearn-control-btn[data-action="shuffle"]', this.shuffleCards);
            $(document).on('click', '.skylearn-control-btn[data-action="reset"]', this.resetProgress);
            
            // Study mode controls
            $(document).on('click', '.skylearn-study-mode', this.toggleStudyMode);
            $(document).on('click', '.skylearn-autoplay', this.toggleAutoplay);
            
            // Progress tracking
            $(document).on('click', '.skylearn-mark-known', this.markAsKnown);
            $(document).on('click', '.skylearn-mark-unknown', this.markAsUnknown);
            
            // Lead capture form
            $(document).on('submit', '.skylearn-lead-form', this.handleLeadSubmission);
        },

        /**
         * Initialize flashcard sets
         */
        initFlashcardSets: function() {
            $('.skylearn-flashcard-set').each(function() {
                const $set = $(this);
                const setId = $set.data('set-id');
                
                if (setId) {
                    SkyLearnFrontend.setupFlashcardSet($set);
                }
            });
        },

        /**
         * Setup individual flashcard set
         */
        setupFlashcardSet: function($set) {
            const setId = $set.data('set-id');
            const cards = $set.find('.skylearn-flashcard');
            
            // Initialize set data
            $set.data('current-card', 0);
            $set.data('total-cards', cards.length);
            $set.data('known-cards', []);
            $set.data('study-mode', false);
            $set.data('autoplay', false);
            
            // Show first card
            this.showCard($set, 0);
            this.updateProgress($set);
            
            // Load progress from localStorage
            this.loadProgress($set);
        },

        /**
         * Flip flashcard
         */
        flipCard: function(e) {
            e.preventDefault();
            const $card = $(this);
            
            if ($card.hasClass('flipped')) {
                $card.removeClass('flipped');
            } else {
                $card.addClass('flipped');
                
                // Track card view
                const setId = $card.closest('.skylearn-flashcard-set').data('set-id');
                const cardIndex = $card.data('card-index');
                SkyLearnFrontend.trackCardView(setId, cardIndex);
            }
        },

        /**
         * Show next card
         */
        nextCard: function(e) {
            e.preventDefault();
            const $set = $(this).closest('.skylearn-flashcard-set');
            const currentCard = $set.data('current-card');
            const totalCards = $set.data('total-cards');
            
            let nextCard = currentCard + 1;
            if (nextCard >= totalCards) {
                nextCard = 0; // Loop back to first card
                SkyLearnFrontend.showResults($set);
                return;
            }
            
            SkyLearnFrontend.showCard($set, nextCard);
        },

        /**
         * Show previous card
         */
        prevCard: function(e) {
            e.preventDefault();
            const $set = $(this).closest('.skylearn-flashcard-set');
            const currentCard = $set.data('current-card');
            const totalCards = $set.data('total-cards');
            
            let prevCard = currentCard - 1;
            if (prevCard < 0) {
                prevCard = totalCards - 1; // Loop to last card
            }
            
            SkyLearnFrontend.showCard($set, prevCard);
        },

        /**
         * Show specific card
         */
        showCard: function($set, cardIndex) {
            const cards = $set.find('.skylearn-flashcard');
            
            // Hide all cards
            cards.hide().removeClass('flipped');
            
            // Show target card
            cards.eq(cardIndex).fadeIn();
            
            // Update set data
            $set.data('current-card', cardIndex);
            
            // Update progress
            this.updateProgress($set);
            
            // Update navigation buttons
            this.updateNavigation($set);
        },

        /**
         * Update progress display
         */
        updateProgress: function($set) {
            const currentCard = $set.data('current-card');
            const totalCards = $set.data('total-cards');
            const knownCards = $set.data('known-cards') || [];
            
            const progressText = (currentCard + 1) + ' / ' + totalCards;
            const progressPercent = ((currentCard + 1) / totalCards) * 100;
            const knownPercent = (knownCards.length / totalCards) * 100;
            
            $set.find('.skylearn-progress-text').text(progressText);
            $set.find('.skylearn-progress-fill').css('width', progressPercent + '%');
            $set.find('.skylearn-known-progress').css('width', knownPercent + '%');
        },

        /**
         * Update navigation buttons
         */
        updateNavigation: function($set) {
            const currentCard = $set.data('current-card');
            const totalCards = $set.data('total-cards');
            
            const $prevBtn = $set.find('.skylearn-control-btn[data-action="prev"]');
            const $nextBtn = $set.find('.skylearn-control-btn[data-action="next"]');
            
            // Update button states (optional - can always allow navigation)
            $prevBtn.prop('disabled', false);
            $nextBtn.prop('disabled', false);
            
            // Update button text for last card
            if (currentCard === totalCards - 1) {
                $nextBtn.text('Finish');
            } else {
                $nextBtn.text('Next');
            }
        },

        /**
         * Shuffle cards
         */
        shuffleCards: function(e) {
            e.preventDefault();
            const $set = $(this).closest('.skylearn-flashcard-set');
            const $container = $set.find('.skylearn-flashcards-container');
            const cards = $container.children('.skylearn-flashcard').toArray();
            
            // Fisher-Yates shuffle
            for (let i = cards.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [cards[i], cards[j]] = [cards[j], cards[i]];
            }
            
            // Re-append shuffled cards
            $.each(cards, function(index, card) {
                $(card).data('card-index', index);
                $container.append(card);
            });
            
            // Reset to first card
            SkyLearnFrontend.showCard($set, 0);
            SkyLearnFrontend.showNotice('Cards shuffled!', 'info');
        },

        /**
         * Reset progress
         */
        resetProgress: function(e) {
            e.preventDefault();
            
            if (!confirm(skylearn_frontend.strings.confirm_reset_progress || 'Are you sure you want to reset your progress?')) {
                return;
            }
            
            const $set = $(this).closest('.skylearn-flashcard-set');
            
            // Reset set data
            $set.data('current-card', 0);
            $set.data('known-cards', []);
            $set.data('study-mode', false);
            $set.data('autoplay', false);
            
            // Clear localStorage
            const setId = $set.data('set-id');
            localStorage.removeItem('skylearn_progress_' + setId);
            
            // Reset display
            SkyLearnFrontend.showCard($set, 0);
            SkyLearnFrontend.showNotice('Progress reset!', 'info');
        },

        /**
         * Mark card as known
         */
        markAsKnown: function(e) {
            e.preventDefault();
            const $set = $(this).closest('.skylearn-flashcard-set');
            const currentCard = $set.data('current-card');
            const knownCards = $set.data('known-cards') || [];
            
            if (!knownCards.includes(currentCard)) {
                knownCards.push(currentCard);
                $set.data('known-cards', knownCards);
                SkyLearnFrontend.updateProgress($set);
                SkyLearnFrontend.saveProgress($set);
            }
            
            // Auto-advance to next card
            setTimeout(function() {
                SkyLearnFrontend.nextCard.call($set.find('.skylearn-control-btn[data-action="next"]')[0], e);
            }, 500);
        },

        /**
         * Mark card as unknown
         */
        markAsUnknown: function(e) {
            e.preventDefault();
            const $set = $(this).closest('.skylearn-flashcard-set');
            const currentCard = $set.data('current-card');
            const knownCards = $set.data('known-cards') || [];
            
            const index = knownCards.indexOf(currentCard);
            if (index > -1) {
                knownCards.splice(index, 1);
                $set.data('known-cards', knownCards);
                SkyLearnFrontend.updateProgress($set);
                SkyLearnFrontend.saveProgress($set);
            }
        },

        /**
         * Initialize keyboard navigation
         */
        initKeyboardNavigation: function() {
            $(document).on('keydown', function(e) {
                const $activeSet = $('.skylearn-flashcard-set:visible').first();
                if ($activeSet.length === 0) return;
                
                switch(e.which) {
                    case 32: // Spacebar - flip card
                        e.preventDefault();
                        $activeSet.find('.skylearn-flashcard:visible').click();
                        break;
                    case 37: // Left arrow - previous card
                        e.preventDefault();
                        $activeSet.find('.skylearn-control-btn[data-action="prev"]').click();
                        break;
                    case 39: // Right arrow - next card
                        e.preventDefault();
                        $activeSet.find('.skylearn-control-btn[data-action="next"]').click();
                        break;
                    case 75: // K - mark as known
                        e.preventDefault();
                        $activeSet.find('.skylearn-mark-known').click();
                        break;
                    case 85: // U - mark as unknown
                        e.preventDefault();
                        $activeSet.find('.skylearn-mark-unknown').click();
                        break;
                }
            });
        },

        /**
         * Initialize touch gestures
         */
        initTouchGestures: function() {
            let startX = null;
            let startY = null;
            
            $(document).on('touchstart', '.skylearn-flashcard', function(e) {
                const touch = e.originalEvent.touches[0];
                startX = touch.clientX;
                startY = touch.clientY;
            });
            
            $(document).on('touchend', '.skylearn-flashcard', function(e) {
                if (startX === null || startY === null) return;
                
                const touch = e.originalEvent.changedTouches[0];
                const endX = touch.clientX;
                const endY = touch.clientY;
                
                const deltaX = endX - startX;
                const deltaY = endY - startY;
                
                const $set = $(this).closest('.skylearn-flashcard-set');
                
                // Horizontal swipe threshold
                if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                    if (deltaX > 0) {
                        // Swipe right - previous card
                        $set.find('.skylearn-control-btn[data-action="prev"]').click();
                    } else {
                        // Swipe left - next card
                        $set.find('.skylearn-control-btn[data-action="next"]').click();
                    }
                }
                
                startX = null;
                startY = null;
            });
        },

        /**
         * Initialize accessibility features
         */
        initAccessibility: function() {
            // Add ARIA labels
            $('.skylearn-flashcard').attr('role', 'button')
                .attr('tabindex', '0')
                .attr('aria-label', 'Click to flip flashcard')
                .attr('aria-expanded', 'false');
            
            // Add keyboard support for card flipping
            $('.skylearn-flashcard').on('keydown', function(e) {
                if (e.which === 13 || e.which === 32) { // Enter or Space
                    e.preventDefault();
                    $(this).click();
                }
            });
            
            // Manage focus indicators
            $(document).on('focusin', '.skylearn-flashcard', function() {
                $(this).attr('data-keyboard-focused', 'true');
            });
            
            $(document).on('focusout', '.skylearn-flashcard', function() {
                $(this).removeAttr('data-keyboard-focused');
            });
            
            // Update ARIA states on flip
            $(document).on('click', '.skylearn-flashcard', function() {
                const $card = $(this);
                const isFlipped = $card.hasClass('flipped');
                
                $card.attr('aria-expanded', isFlipped ? 'true' : 'false');
                
                // Update card side visibility for screen readers
                $card.find('.skylearn-card-front').attr('aria-hidden', isFlipped ? 'true' : 'false');
                $card.find('.skylearn-card-back').attr('aria-hidden', isFlipped ? 'false' : 'true');
            });
            
            // Add skip link for keyboard navigation
            if ($('.skylearn-skip-link').length === 0) {
                $('body').prepend(`
                    <a href="#skylearn-main-content" class="skylearn-skip-link skylearn-sr-only">
                        ${skylearn_frontend.strings.skip_to_content || 'Skip to main content'}
                    </a>
                `);
            }
        },

        /**
         * Show results summary
         */
        showResults: function($set) {
            const totalCards = $set.data('total-cards');
            const knownCards = $set.data('known-cards') || [];
            const accuracy = Math.round((knownCards.length / totalCards) * 100);
            
            const resultHtml = `
                <div class="skylearn-results">
                    <h3 class="skylearn-results-title">Study Session Complete!</h3>
                    <div class="skylearn-results-stats">
                        <div class="skylearn-stat">
                            <span class="skylearn-stat-value">${totalCards}</span>
                            <span class="skylearn-stat-label">Total Cards</span>
                        </div>
                        <div class="skylearn-stat">
                            <span class="skylearn-stat-value">${knownCards.length}</span>
                            <span class="skylearn-stat-label">Cards Mastered</span>
                        </div>
                        <div class="skylearn-stat">
                            <span class="skylearn-stat-value">${accuracy}%</span>
                            <span class="skylearn-stat-label">Accuracy</span>
                        </div>
                    </div>
                    <button class="skylearn-control-btn" onclick="location.reload()">Study Again</button>
                </div>
            `;
            
            $set.find('.skylearn-flashcards-container').html(resultHtml);
            
            // Track completion
            const setId = $set.data('set-id');
            this.trackCompletion(setId, accuracy);
        },

        /**
         * Save progress to localStorage
         */
        saveProgress: function($set) {
            const setId = $set.data('set-id');
            const progress = {
                currentCard: $set.data('current-card'),
                knownCards: $set.data('known-cards') || [],
                timestamp: Date.now()
            };
            
            localStorage.setItem('skylearn_progress_' + setId, JSON.stringify(progress));
        },

        /**
         * Load progress from localStorage
         */
        loadProgress: function($set) {
            const setId = $set.data('set-id');
            const savedProgress = localStorage.getItem('skylearn_progress_' + setId);
            
            if (savedProgress) {
                try {
                    const progress = JSON.parse(savedProgress);
                    $set.data('current-card', progress.currentCard || 0);
                    $set.data('known-cards', progress.knownCards || []);
                    this.showCard($set, progress.currentCard || 0);
                } catch (e) {
                    console.warn('Could not load saved progress:', e);
                }
            }
        },

        /**
         * Track card view for analytics
         */
        trackCardView: function(setId, cardIndex) {
            if (typeof skylearn_frontend === 'undefined' || !skylearn_frontend.ajax_url) {
                return;
            }
            
            $.post(skylearn_frontend.ajax_url, {
                action: 'skylearn_track_card_view',
                set_id: setId,
                card_index: cardIndex,
                nonce: skylearn_frontend.nonce
            });
        },

        /**
         * Track study session completion
         */
        trackCompletion: function(setId, accuracy) {
            if (typeof skylearn_frontend === 'undefined' || !skylearn_frontend.ajax_url) {
                return;
            }
            
            $.post(skylearn_frontend.ajax_url, {
                action: 'skylearn_track_completion',
                set_id: setId,
                accuracy: accuracy,
                nonce: skylearn_frontend.nonce
            });
        },

        /**
         * Handle lead form submission
         */
        handleLeadSubmission: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const formData = new FormData($form[0]);
            formData.append('action', 'skylearn_submit_lead');
            formData.append('nonce', skylearn_frontend.nonce);
            
            $.ajax({
                url: skylearn_frontend.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $form.find('.skylearn-submit-btn').prop('disabled', true).text('Submitting...');
                },
                success: function(response) {
                    if (response.success) {
                        $form.html('<div class="skylearn-notice skylearn-notice-success">' + response.data.message + '</div>');
                    } else {
                        SkyLearnFrontend.showNotice(response.data.message, 'error');
                    }
                },
                complete: function() {
                    $form.find('.skylearn-submit-btn').prop('disabled', false).text('Submit');
                }
            });
        },

        /**
         * Show frontend notice
         */
        showNotice: function(message, type) {
            type = type || 'info';
            
            const notice = $('<div class="skylearn-notice skylearn-notice-' + type + '">')
                .html(message)
                .hide();
            
            $('body').prepend(notice);
            notice.slideDown();
            
            setTimeout(function() {
                notice.slideUp(function() {
                    notice.remove();
                });
            }, 3000);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        SkyLearnFrontend.init();
    });

    // Make SkyLearnFrontend globally available
    window.SkyLearnFrontend = SkyLearnFrontend;

})(jQuery);

// Placeholder for future frontend functionality