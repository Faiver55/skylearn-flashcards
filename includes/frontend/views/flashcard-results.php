<?php
/**
 * Flashcard results and performance summary view
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
 * $results - Study session results
 * $set_data - Flashcard set data
 * $stats - Performance statistics
 */

$results = isset( $results ) ? $results : array();
$set_data = isset( $set_data ) ? $set_data : array();
$stats = isset( $stats ) ? $stats : array();

// Default values
$total_cards = isset( $stats['total_cards'] ) ? $stats['total_cards'] : 0;
$completed_cards = isset( $stats['completed_cards'] ) ? $stats['completed_cards'] : 0;
$time_spent = isset( $stats['time_spent'] ) ? $stats['time_spent'] : 0;
$accuracy = isset( $stats['accuracy'] ) ? $stats['accuracy'] : 0;
$completion_rate = $total_cards > 0 ? round( ( $completed_cards / $total_cards ) * 100 ) : 0;
?>

<div class="skylearn-results-container">
    
    <!-- Results Header -->
    <div class="skylearn-results-header">
        <div class="results-icon">
            <?php if ( $completion_rate >= 80 ) : ?>
                <span class="dashicons dashicons-yes-alt success-icon"></span>
            <?php elseif ( $completion_rate >= 50 ) : ?>
                <span class="dashicons dashicons-clock warning-icon"></span>
            <?php else : ?>
                <span class="dashicons dashicons-info info-icon"></span>
            <?php endif; ?>
        </div>
        
        <div class="results-title">
            <h2>
                <?php if ( $completion_rate >= 100 ) : ?>
                    <?php esc_html_e( 'Congratulations! Set Complete!', 'skylearn-flashcards' ); ?>
                <?php elseif ( $completion_rate >= 80 ) : ?>
                    <?php esc_html_e( 'Great Progress!', 'skylearn-flashcards' ); ?>
                <?php elseif ( $completion_rate >= 50 ) : ?>
                    <?php esc_html_e( 'Good Start!', 'skylearn-flashcards' ); ?>
                <?php else : ?>
                    <?php esc_html_e( 'Study Session Summary', 'skylearn-flashcards' ); ?>
                <?php endif; ?>
            </h2>
            
            <p class="results-subtitle">
                <?php 
                printf( 
                    /* translators: %s: flashcard set title */
                    esc_html__( 'Results for: %s', 'skylearn-flashcards' ),
                    esc_html( isset( $set_data['title'] ) ? $set_data['title'] : __( 'Flashcard Set', 'skylearn-flashcards' ) )
                );
                ?>
            </p>
        </div>
    </div>

    <!-- Performance Stats Grid -->
    <div class="skylearn-stats-grid">
        
        <!-- Completion Rate -->
        <div class="skylearn-stat-card primary">
            <div class="stat-header">
                <span class="stat-icon dashicons dashicons-chart-pie"></span>
                <h3><?php esc_html_e( 'Completion', 'skylearn-flashcards' ); ?></h3>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html( $completion_rate ); ?>%</div>
                <div class="stat-detail">
                    <?php 
                    printf( 
                        /* translators: %1$d: completed cards, %2$d: total cards */
                        esc_html__( '%1$d of %2$d cards', 'skylearn-flashcards' ),
                        $completed_cards,
                        $total_cards
                    );
                    ?>
                </div>
            </div>
            <div class="stat-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo esc_attr( $completion_rate ); ?>%;"></div>
                </div>
            </div>
        </div>
        
        <!-- Time Spent -->
        <div class="skylearn-stat-card">
            <div class="stat-header">
                <span class="stat-icon dashicons dashicons-clock"></span>
                <h3><?php esc_html_e( 'Time Spent', 'skylearn-flashcards' ); ?></h3>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html( gmdate( 'i:s', $time_spent ) ); ?></div>
                <div class="stat-detail">
                    <?php 
                    if ( $completed_cards > 0 ) {
                        $avg_time = round( $time_spent / $completed_cards );
                        printf( 
                            /* translators: %d: average seconds per card */
                            esc_html__( '%d sec/card avg', 'skylearn-flashcards' ),
                            $avg_time
                        );
                    } else {
                        esc_html_e( 'No cards completed', 'skylearn-flashcards' );
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Accuracy -->
        <div class="skylearn-stat-card">
            <div class="stat-header">
                <span class="stat-icon dashicons dashicons-chart-line"></span>
                <h3><?php esc_html_e( 'Knowledge Score', 'skylearn-flashcards' ); ?></h3>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html( round( $accuracy, 1 ) ); ?>/5.0</div>
                <div class="stat-detail">
                    <?php 
                    if ( $accuracy >= 4.0 ) {
                        esc_html_e( 'Excellent mastery', 'skylearn-flashcards' );
                    } elseif ( $accuracy >= 3.0 ) {
                        esc_html_e( 'Good understanding', 'skylearn-flashcards' );
                    } elseif ( $accuracy >= 2.0 ) {
                        esc_html_e( 'Need more practice', 'skylearn-flashcards' );
                    } else {
                        esc_html_e( 'Keep studying', 'skylearn-flashcards' );
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Cards by Knowledge Level -->
        <div class="skylearn-stat-card">
            <div class="stat-header">
                <span class="stat-icon dashicons dashicons-portfolio"></span>
                <h3><?php esc_html_e( 'Card Breakdown', 'skylearn-flashcards' ); ?></h3>
            </div>
            <div class="stat-content">
                <div class="knowledge-breakdown">
                    <div class="knowledge-item excellent">
                        <span class="knowledge-count"><?php echo esc_html( isset( $stats['excellent_cards'] ) ? $stats['excellent_cards'] : 0 ); ?></span>
                        <span class="knowledge-label"><?php esc_html_e( 'Excellent', 'skylearn-flashcards' ); ?></span>
                    </div>
                    <div class="knowledge-item good">
                        <span class="knowledge-count"><?php echo esc_html( isset( $stats['good_cards'] ) ? $stats['good_cards'] : 0 ); ?></span>
                        <span class="knowledge-label"><?php esc_html_e( 'Good', 'skylearn-flashcards' ); ?></span>
                    </div>
                    <div class="knowledge-item poor">
                        <span class="knowledge-count"><?php echo esc_html( isset( $stats['poor_cards'] ) ? $stats['poor_cards'] : 0 ); ?></span>
                        <span class="knowledge-label"><?php esc_html_e( 'Poor', 'skylearn-flashcards' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Recommendations -->
    <div class="skylearn-recommendations">
        <h3><?php esc_html_e( 'Recommendations', 'skylearn-flashcards' ); ?></h3>
        
        <div class="recommendation-list">
            <?php if ( $completion_rate < 100 ) : ?>
                <div class="recommendation-item">
                    <span class="dashicons dashicons-redo"></span>
                    <div class="recommendation-content">
                        <strong><?php esc_html_e( 'Continue Studying', 'skylearn-flashcards' ); ?></strong>
                        <p><?php esc_html_e( 'You have more cards to review. Keep going to complete the set!', 'skylearn-flashcards' ); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ( isset( $stats['poor_cards'] ) && $stats['poor_cards'] > 0 ) : ?>
                <div class="recommendation-item">
                    <span class="dashicons dashicons-book"></span>
                    <div class="recommendation-content">
                        <strong><?php esc_html_e( 'Review Difficult Cards', 'skylearn-flashcards' ); ?></strong>
                        <p>
                            <?php 
                            printf( 
                                /* translators: %d: number of difficult cards */
                                esc_html__( 'Focus on the %d cards you found challenging.', 'skylearn-flashcards' ),
                                $stats['poor_cards']
                            );
                            ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ( $accuracy >= 4.0 && $completion_rate >= 100 ) : ?>
                <div class="recommendation-item">
                    <span class="dashicons dashicons-star-filled"></span>
                    <div class="recommendation-content">
                        <strong><?php esc_html_e( 'Excellent Work!', 'skylearn-flashcards' ); ?></strong>
                        <p><?php esc_html_e( 'You\'ve mastered this set. Consider reviewing it periodically to maintain your knowledge.', 'skylearn-flashcards' ); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="recommendation-item">
                <span class="dashicons dashicons-calendar-alt"></span>
                <div class="recommendation-content">
                    <strong><?php esc_html_e( 'Schedule Regular Review', 'skylearn-flashcards' ); ?></strong>
                    <p><?php esc_html_e( 'Spaced repetition helps improve long-term retention. Review this set again in a few days.', 'skylearn-flashcards' ); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="skylearn-results-actions">
        
        <?php if ( $completion_rate < 100 ) : ?>
            <button type="button" class="skylearn-btn skylearn-btn-primary btn-continue">
                <span class="dashicons dashicons-controls-play"></span>
                <?php esc_html_e( 'Continue Studying', 'skylearn-flashcards' ); ?>
            </button>
        <?php endif; ?>
        
        <button type="button" class="skylearn-btn skylearn-btn-secondary btn-restart">
            <span class="dashicons dashicons-update"></span>
            <?php esc_html_e( 'Study Again', 'skylearn-flashcards' ); ?>
        </button>
        
        <?php if ( isset( $stats['poor_cards'] ) && $stats['poor_cards'] > 0 ) : ?>
            <button type="button" class="skylearn-btn skylearn-btn-secondary btn-review-difficult">
                <span class="dashicons dashicons-book"></span>
                <?php esc_html_e( 'Review Difficult Cards', 'skylearn-flashcards' ); ?>
            </button>
        <?php endif; ?>
        
        <button type="button" class="skylearn-btn skylearn-btn-outline btn-share">
            <span class="dashicons dashicons-share"></span>
            <?php esc_html_e( 'Share Results', 'skylearn-flashcards' ); ?>
        </button>
        
    </div>

    <!-- Lead Collection (if enabled and completion rate is good) -->
    <?php if ( $completion_rate >= 80 && skylearn_is_premium() ) : ?>
        <div class="skylearn-lead-collection">
            <?php include 'lead-capture.php'; ?>
        </div>
    <?php endif; ?>

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // TODO: Implement results page functionality
    
    var resultsData = {
        completionRate: <?php echo (int) $completion_rate; ?>,
        timeSpent: <?php echo (int) $time_spent; ?>,
        accuracy: <?php echo (float) $accuracy; ?>,
        totalCards: <?php echo (int) $total_cards; ?>,
        completedCards: <?php echo (int) $completed_cards; ?>
    };
    
    console.log('Study session results:', resultsData);
    
    // Action button handlers
    $('.btn-continue').on('click', function() {
        // TODO: Continue to next unreviewed card
        console.log('Continue studying clicked');
    });
    
    $('.btn-restart').on('click', function() {
        // TODO: Restart the flashcard set
        console.log('Restart set clicked');
    });
    
    $('.btn-review-difficult').on('click', function() {
        // TODO: Show only cards marked as difficult
        console.log('Review difficult cards clicked');
    });
    
    $('.btn-share').on('click', function() {
        // TODO: Implement sharing functionality
        if (navigator.share) {
            navigator.share({
                title: 'My Study Results',
                text: 'I just completed ' + resultsData.completionRate + '% of a flashcard set!',
                url: window.location.href
            });
        } else {
            // Fallback for browsers without Web Share API
            alert('<?php esc_js( esc_html_e( 'Sharing functionality will be implemented in a future update.', 'skylearn-flashcards' ) ); ?>');
        }
    });
    
    // Track results view
    // TODO: Send analytics event
});
</script>