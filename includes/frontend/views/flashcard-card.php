<?php
/**
 * Individual flashcard view
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend/views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Available variables:
 * $card - Individual card data
 * $index - Card index in the set
 */

$card = isset( $card ) ? $card : array();
$index = isset( $index ) ? $index : 0;
$card_id = isset( $card['id'] ) ? $card['id'] : $index;
$question = isset( $card['question'] ) ? $card['question'] : '';
$answer = isset( $card['answer'] ) ? $card['answer'] : '';
$is_first = $index === 0;
?>

<div class="skylearn-flashcard <?php echo $is_first ? 'active' : ''; ?>" 
     data-card-id="<?php echo esc_attr( $card_id ); ?>"
     data-card-index="<?php echo esc_attr( $index ); ?>"
     role="button"
     tabindex="0"
     aria-label="<?php printf( esc_attr__( 'Flashcard %d, click or press space to flip', 'skylearn-flashcards' ), $index + 1 ); ?>"
     aria-expanded="false"
     aria-describedby="flashcard-instructions-<?php echo esc_attr( $card_id ); ?>">
     
    <div class="skylearn-card-inner">
        
        <!-- Front Side (Question) -->
        <div class="skylearn-card-front" aria-hidden="false">
            <div class="skylearn-card-content">
                <div class="skylearn-card-header">
                    <span class="skylearn-card-type" aria-label="<?php esc_attr_e( 'Question side', 'skylearn-flashcards' ); ?>">
                        <?php esc_html_e( 'Question', 'skylearn-flashcards' ); ?>
                    </span>
                    <span class="skylearn-card-number" aria-label="<?php printf( esc_attr__( 'Card %d', 'skylearn-flashcards' ), $index + 1 ); ?>">
                        <?php echo esc_html( $index + 1 ); ?>
                    </span>
                </div>
                
                <div class="skylearn-card-body">
                    <?php if ( ! empty( $question ) ) : ?>
                        <div class="skylearn-question" role="heading" aria-level="3">
                            <?php echo wp_kses_post( $question ); ?>
                        </div>
                    <?php else : ?>
                        <div class="skylearn-placeholder">
                            <span class="dashicons dashicons-edit"></span>
                            <p><?php esc_html_e( 'No question available', 'skylearn-flashcards' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="skylearn-card-footer">
                    <div class="skylearn-card-hint">
                        <span class="dashicons dashicons-pointer"></span>
                        <?php esc_html_e( 'Click or press Space to reveal answer', 'skylearn-flashcards' ); ?>
                    </div>
                </div>
            </div>
            
            <!-- Visual Elements -->
            <div class="skylearn-card-decoration">
                <div class="card-corner card-corner-tl"></div>
                <div class="card-corner card-corner-tr"></div>
                <div class="card-corner card-corner-bl"></div>
                <div class="card-corner card-corner-br"></div>
            </div>
        </div>
        
        <!-- Back Side (Answer) -->
        <div class="skylearn-card-back" aria-hidden="true">
            <div class="skylearn-card-content">
                <div class="skylearn-card-header">
                    <span class="skylearn-card-type" aria-label="<?php esc_attr_e( 'Answer side', 'skylearn-flashcards' ); ?>">
                        <?php esc_html_e( 'Answer', 'skylearn-flashcards' ); ?>
                    </span>
                    <span class="skylearn-card-number" aria-label="<?php printf( esc_attr__( 'Card %d', 'skylearn-flashcards' ), $index + 1 ); ?>">
                        <?php echo esc_html( $index + 1 ); ?>
                    </span>
                </div>
                
                <div class="skylearn-card-body">
                    <?php if ( ! empty( $answer ) ) : ?>
                        <div class="skylearn-answer" role="heading" aria-level="3">
                            <?php echo wp_kses_post( $answer ); ?>
                        </div>
                    <?php else : ?>
                        <div class="skylearn-placeholder" role="alert">
                            <span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
                            <p><?php esc_html_e( 'No answer available', 'skylearn-flashcards' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="skylearn-card-footer">
                    <!-- Knowledge Rating -->
                    <div class="skylearn-knowledge-rating">
                        <span class="rating-label"><?php esc_html_e( 'How well did you know this?', 'skylearn-flashcards' ); ?></span>
                        <div class="rating-buttons">
                            <button type="button" class="rating-btn rating-poor" data-rating="1" title="<?php esc_attr_e( 'Poor - Need more practice', 'skylearn-flashcards' ); ?>">
                                <span class="dashicons dashicons-thumbs-down"></span>
                                <span class="rating-text"><?php esc_html_e( 'Poor', 'skylearn-flashcards' ); ?></span>
                            </button>
                            <button type="button" class="rating-btn rating-good" data-rating="2" title="<?php esc_attr_e( 'Good - Some practice needed', 'skylearn-flashcards' ); ?>">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <span class="rating-text"><?php esc_html_e( 'Good', 'skylearn-flashcards' ); ?></span>
                            </button>
                            <button type="button" class="rating-btn rating-excellent" data-rating="3" title="<?php esc_attr_e( 'Excellent - Know it well', 'skylearn-flashcards' ); ?>">
                                <span class="dashicons dashicons-thumbs-up"></span>
                                <span class="rating-text"><?php esc_html_e( 'Excellent', 'skylearn-flashcards' ); ?></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="skylearn-card-hint">
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                        <?php esc_html_e( 'Rate your knowledge to continue', 'skylearn-flashcards' ); ?>
                    </div>
                </div>
            </div>
            
            <!-- Visual Elements -->
            <div class="skylearn-card-decoration">
                <div class="card-corner card-corner-tl"></div>
                <div class="card-corner card-corner-tr"></div>
                <div class="card-corner card-corner-bl"></div>
                <div class="card-corner card-corner-br"></div>
            </div>
        </div>
        
    </div>
    
    <!-- Card Actions -->
    <div class="skylearn-card-actions">
        <button type="button" class="skylearn-action-btn flip-card" title="<?php esc_attr_e( 'Flip card (Space)', 'skylearn-flashcards' ); ?>">
            <span class="dashicons dashicons-image-flip-horizontal"></span>
        </button>
        
        <button type="button" class="skylearn-action-btn bookmark-card" title="<?php esc_attr_e( 'Bookmark for review', 'skylearn-flashcards' ); ?>">
            <span class="dashicons dashicons-book-alt"></span>
        </button>
        
        <button type="button" class="skylearn-action-btn flag-card" title="<?php esc_attr_e( 'Flag for attention', 'skylearn-flashcards' ); ?>">
            <span class="dashicons dashicons-flag"></span>
        </button>
    </div>
</div>

<!-- Card-specific JavaScript -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    var cardElement = $('.skylearn-flashcard[data-card-index="<?php echo esc_js( $index ); ?>"]');
    
    // TODO: Implement card-specific functionality
    
    // Flip card on click
    cardElement.on('click', function() {
        if (!$(this).hasClass('flipped')) {
            $(this).addClass('flipped');
            // TODO: Track card flip event
        }
    });
    
    // Knowledge rating
    cardElement.find('.rating-btn').on('click', function() {
        var rating = $(this).data('rating');
        var cardId = cardElement.data('card-id');
        
        // TODO: Send rating to server
        console.log('Card rated:', { cardId: cardId, rating: rating });
        
        // Visual feedback
        $(this).siblings().removeClass('selected');
        $(this).addClass('selected');
        
        // Auto-advance if enabled
        setTimeout(function() {
            // TODO: Trigger next card
        }, 1000);
    });
    
    // Bookmark functionality
    cardElement.find('.bookmark-card').on('click', function(e) {
        e.stopPropagation();
        $(this).toggleClass('bookmarked');
        // TODO: Save bookmark state
    });
    
    // Flag functionality
    cardElement.find('.flag-card').on('click', function(e) {
        e.stopPropagation();
        $(this).toggleClass('flagged');
        // TODO: Save flag state
    });
});
</script>

<!-- Accessibility Instructions -->
<div id="flashcard-instructions-<?php echo esc_attr( $card_id ); ?>" class="skylearn-sr-only">
    <?php esc_html_e( 'Use arrow keys to navigate between cards, space bar to flip cards, and tab to access interactive elements.', 'skylearn-flashcards' ); ?>
</div>