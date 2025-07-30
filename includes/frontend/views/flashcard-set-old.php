<?php
/**
 * Frontend flashcard set display view
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
 * $set_id - Flashcard set ID
 * $set_data - Flashcard set data
 * $cards - Array of flashcards
 * $settings - Display settings
 * $colors - Color scheme
 */

$set_id = isset( $set_id ) ? $set_id : 0;
$set_data = isset( $set_data ) ? $set_data : array();
$cards = isset( $cards ) ? $cards : array();
$settings = isset( $settings ) ? $settings : array();
$colors = isset( $colors ) ? skylearn_get_brand_colors() : skylearn_get_brand_colors();
?>

<div class="skylearn-flashcard-set" 
     data-set-id="<?php echo esc_attr( $set_id ); ?>"
     data-total-cards="<?php echo esc_attr( count( $cards ) ); ?>">
     
    <!-- Set Header -->
    <div class="skylearn-set-header">
        <div class="skylearn-set-info">
            <h2 class="skylearn-set-title">
                <?php echo esc_html( isset( $set_data['title'] ) ? $set_data['title'] : __( 'Flashcard Set', 'skylearn-flashcards' ) ); ?>
            </h2>
            
            <?php if ( ! empty( $set_data['description'] ) ) : ?>
                <p class="skylearn-set-description">
                    <?php echo esc_html( $set_data['description'] ); ?>
                </p>
            <?php endif; ?>
            
            <div class="skylearn-set-meta">
                <span class="cards-count">
                    <?php 
                    printf( 
                        /* translators: %d: number of cards */
                        esc_html( _n( '%d card', '%d cards', count( $cards ), 'skylearn-flashcards' ) ),
                        count( $cards )
                    ); 
                    ?>
                </span>
                
                <?php if ( isset( $settings['show_progress'] ) && $settings['show_progress'] ) : ?>
                    <span class="progress-indicator">
                        <span class="current-card">1</span> / <span class="total-cards"><?php echo esc_html( count( $cards ) ); ?></span>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="skylearn-set-controls">
            <button type="button" class="skylearn-btn skylearn-btn-secondary" id="skylearn-restart">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e( 'Restart', 'skylearn-flashcards' ); ?>
            </button>
            
            <?php if ( isset( $settings['shuffle_cards'] ) && $settings['shuffle_cards'] ) : ?>
                <button type="button" class="skylearn-btn skylearn-btn-secondary" id="skylearn-shuffle">
                    <span class="dashicons dashicons-randomize"></span>
                    <?php esc_html_e( 'Shuffle', 'skylearn-flashcards' ); ?>
                </button>
            <?php endif; ?>
            
            <button type="button" class="skylearn-btn skylearn-btn-primary" id="skylearn-fullscreen">
                <span class="dashicons dashicons-fullscreen-alt"></span>
                <?php esc_html_e( 'Fullscreen', 'skylearn-flashcards' ); ?>
            </button>
        </div>
    </div>

    <!-- Progress Bar -->
    <?php if ( isset( $settings['show_progress'] ) && $settings['show_progress'] ) : ?>
        <div class="skylearn-progress-container">
            <div class="skylearn-progress-bar">
                <div class="skylearn-progress-fill" style="width: 0%;"></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Flashcards Container -->
    <div class="skylearn-cards-container">
        <?php if ( empty( $cards ) ) : ?>
            <div class="skylearn-empty-set">
                <div class="empty-icon">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
                <h3><?php esc_html_e( 'No Cards Available', 'skylearn-flashcards' ); ?></h3>
                <p><?php esc_html_e( 'This flashcard set is empty. Please check back later or contact the administrator.', 'skylearn-flashcards' ); ?></p>
            </div>
        <?php else : ?>
            <div class="skylearn-card-wrapper">
                <?php 
                foreach ( $cards as $index => $card ) {
                    // Include individual card template
                    include 'flashcard-card.php';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Navigation Controls -->
    <?php if ( ! empty( $cards ) ) : ?>
        <div class="skylearn-navigation">
            <button type="button" class="skylearn-nav-btn skylearn-nav-prev" disabled>
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php esc_html_e( 'Previous', 'skylearn-flashcards' ); ?>
            </button>
            
            <div class="skylearn-nav-indicators">
                <?php for ( $i = 0; $i < count( $cards ); $i++ ) : ?>
                    <button type="button" class="skylearn-indicator <?php echo $i === 0 ? 'active' : ''; ?>" 
                            data-card-index="<?php echo esc_attr( $i ); ?>">
                        <?php echo esc_html( $i + 1 ); ?>
                    </button>
                <?php endfor; ?>
            </div>
            
            <button type="button" class="skylearn-nav-btn skylearn-nav-next">
                <?php esc_html_e( 'Next', 'skylearn-flashcards' ); ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Study Stats -->
    <div class="skylearn-study-stats" style="display: none;">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label"><?php esc_html_e( 'Cards Reviewed', 'skylearn-flashcards' ); ?></span>
                <span class="stat-value" id="cards-reviewed">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php esc_html_e( 'Time Spent', 'skylearn-flashcards' ); ?></span>
                <span class="stat-value" id="time-spent">00:00</span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php esc_html_e( 'Completion', 'skylearn-flashcards' ); ?></span>
                <span class="stat-value" id="completion-rate">0%</span>
            </div>
        </div>
    </div>

</div>

<!-- Keyboard shortcuts hint -->
<div class="skylearn-shortcuts-hint">
    <p>
        <strong><?php esc_html_e( 'Keyboard Shortcuts:', 'skylearn-flashcards' ); ?></strong>
        <span><?php esc_html_e( 'Space/Click: Flip card', 'skylearn-flashcards' ); ?></span> |
        <span><?php esc_html_e( 'Arrow keys: Navigate', 'skylearn-flashcards' ); ?></span> |
        <span><?php esc_html_e( 'R: Restart', 'skylearn-flashcards' ); ?></span> |
        <span><?php esc_html_e( 'S: Shuffle', 'skylearn-flashcards' ); ?></span>
    </p>
</div>

<!-- Color scheme CSS variables -->
<style>
:root {
    --skylearn-primary: <?php echo esc_attr( $colors['primary'] ); ?>;
    --skylearn-accent: <?php echo esc_attr( $colors['accent'] ); ?>;
    --skylearn-background: <?php echo esc_attr( $colors['background'] ); ?>;
    --skylearn-text: <?php echo esc_attr( $colors['text'] ); ?>;
}
</style>

<script type="text/javascript">
// Initialize flashcard set
jQuery(document).ready(function($) {
    // TODO: Initialize flashcard functionality
    console.log('SkyLearn Flashcard Set initialized', {
        setId: <?php echo (int) $set_id; ?>,
        totalCards: <?php echo count( $cards ); ?>,
        settings: <?php echo wp_json_encode( $settings ); ?>
    });
    
    // Placeholder for future JavaScript functionality
    if (typeof SkyLearnFlashcards !== 'undefined') {
        SkyLearnFlashcards.init({
            container: '.skylearn-flashcard-set',
            setId: <?php echo (int) $set_id; ?>,
            autoAdvance: <?php echo isset( $settings['auto_advance'] ) && $settings['auto_advance'] ? 'true' : 'false'; ?>,
            showProgress: <?php echo isset( $settings['show_progress'] ) && $settings['show_progress'] ? 'true' : 'false'; ?>
        });
    }
});
</script>