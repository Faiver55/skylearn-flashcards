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
        
        <?php if ( skylearn_is_premium() ) : ?>
            <button type="button" class="skylearn-btn skylearn-btn-secondary btn-export-results">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export Results', 'skylearn-flashcards' ); ?>
            </button>
        <?php endif; ?>
        
        <button type="button" class="skylearn-btn skylearn-btn-outline btn-share">
            <span class="dashicons dashicons-share"></span>
            <?php esc_html_e( 'Share Results', 'skylearn-flashcards' ); ?>
        </button>
        
    </div>

    <!-- Progress History (Premium) -->
    <?php if ( skylearn_is_premium() && is_user_logged_in() ) : ?>
        <?php
        global $wpdb;
        $user_id = get_current_user_id();
        $set_id = isset( $set_data['id'] ) ? $set_data['id'] : 0;
        $analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
        
        $previous_attempts = $wpdb->get_results( $wpdb->prepare(
            "SELECT accuracy, created_at FROM {$analytics_table} 
             WHERE user_id = %d AND set_id = %d AND action = 'complete' 
             ORDER BY created_at DESC LIMIT 6",
            $user_id,
            $set_id
        ), ARRAY_A );
        ?>
        
        <?php if ( ! empty( $previous_attempts ) && count( $previous_attempts ) > 1 ) : ?>
        <div class="skylearn-progress-history">
            <h3><?php esc_html_e( 'Your Progress History', 'skylearn-flashcards' ); ?></h3>
            <div class="progress-chart-container">
                <canvas id="progress-history-chart" width="600" height="250"></canvas>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Export Modal (Premium only) -->
    <?php if ( skylearn_is_premium() ) : ?>
    <div id="export-results-modal" class="skylearn-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php esc_html_e( 'Export Study Results', 'skylearn-flashcards' ); ?></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="export-results-form">
                    <div class="form-group">
                        <label for="export-results-format"><?php esc_html_e( 'Format:', 'skylearn-flashcards' ); ?></label>
                        <select id="export-results-format" name="format" class="form-control">
                            <option value="pdf"><?php esc_html_e( 'PDF Report', 'skylearn-flashcards' ); ?></option>
                            <option value="csv"><?php esc_html_e( 'CSV Data', 'skylearn-flashcards' ); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="include_history" value="1" checked>
                            <?php esc_html_e( 'Include progress history', 'skylearn-flashcards' ); ?>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="include_recommendations" value="1" checked>
                            <?php esc_html_e( 'Include study recommendations', 'skylearn-flashcards' ); ?>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="skylearn-btn skylearn-btn-primary" id="confirm-export-results">
                    <?php esc_html_e( 'Export', 'skylearn-flashcards' ); ?>
                </button>
                <button type="button" class="skylearn-btn skylearn-btn-secondary modal-close">
                    <?php esc_html_e( 'Cancel', 'skylearn-flashcards' ); ?>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lead Collection (if enabled and completion rate is good) -->
    <?php if ( $completion_rate >= 80 && skylearn_is_premium() ) : ?>
        <div class="skylearn-lead-collection">
            <?php include 'lead-capture.php'; ?>
        </div>
    <?php endif; ?>

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    
    var resultsData = {
        completionRate: <?php echo (int) $completion_rate; ?>,
        timeSpent: <?php echo (int) $time_spent; ?>,
        accuracy: <?php echo (float) $accuracy; ?>,
        totalCards: <?php echo (int) $total_cards; ?>,
        completedCards: <?php echo (int) $completed_cards; ?>
    };
    
    console.log('Study session results:', resultsData);
    
    // Initialize progress history chart (Premium)
    <?php if ( skylearn_is_premium() && ! empty( $previous_attempts ) && count( $previous_attempts ) > 1 ) : ?>
    if (typeof Chart !== 'undefined') {
        var ctx = document.getElementById('progress-history-chart');
        if (ctx) {
            var historyData = <?php echo wp_json_encode( array_reverse( $previous_attempts ) ); ?>;
            var labels = historyData.map(function(item) {
                return new Date(item.created_at).toLocaleDateString();
            });
            var scores = historyData.map(function(item) {
                return parseFloat(item.accuracy) || 0;
            });
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?php esc_js( esc_html_e( 'Accuracy %', 'skylearn-flashcards' ) ); ?>',
                        data: scores,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#3498db',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    <?php endif; ?>
    
    // Action button handlers
    $('.btn-continue').on('click', function() {
        // Continue to next unreviewed card
        if (typeof window.skylearn_continue_study === 'function') {
            window.skylearn_continue_study();
        } else {
            location.reload();
        }
    });
    
    $('.btn-restart').on('click', function() {
        if (confirm('<?php esc_js( esc_html_e( 'Are you sure you want to restart this flashcard set?', 'skylearn-flashcards' ) ); ?>')) {
            if (typeof window.skylearn_restart_set === 'function') {
                window.skylearn_restart_set();
            } else {
                location.reload();
            }
        }
    });
    
    $('.btn-review-difficult').on('click', function() {
        // Show only cards marked as difficult
        if (typeof window.skylearn_review_difficult === 'function') {
            window.skylearn_review_difficult();
        } else {
            console.log('Review difficult cards feature not implemented yet');
        }
    });
    
    // Export results (Premium)
    <?php if ( skylearn_is_premium() ) : ?>
    $('.btn-export-results').on('click', function() {
        $('#export-results-modal').show();
    });
    
    $('#confirm-export-results').on('click', function() {
        var $button = $(this);
        var originalText = $button.text();
        
        $button.prop('disabled', true).text('<?php esc_js( esc_html_e( 'Exporting...', 'skylearn-flashcards' ) ); ?>');
        
        var formData = {
            action: 'skylearn_export_student_results',
            set_id: <?php echo esc_js( isset( $set_data['id'] ) ? $set_data['id'] : 0 ); ?>,
            results: resultsData,
            stats: <?php echo wp_json_encode( $stats ); ?>,
            format: $('#export-results-format').val(),
            include_history: $('#export-results-form input[name="include_history"]').is(':checked') ? 1 : 0,
            include_recommendations: $('#export-results-form input[name="include_recommendations"]').is(':checked') ? 1 : 0,
            nonce: '<?php echo esc_js( wp_create_nonce( 'skylearn_export_student_results' ) ); ?>'
        };
        
        $.post('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', formData)
            .done(function(response) {
                if (response.success) {
                    // Trigger download
                    var blob = new Blob([response.data.content], { type: response.data.mime_type });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = response.data.filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    
                    $('#export-results-modal').hide();
                } else {
                    alert(response.data.message || '<?php esc_js( esc_html_e( 'Export failed.', 'skylearn-flashcards' ) ); ?>');
                }
            })
            .fail(function() {
                alert('<?php esc_js( esc_html_e( 'Export request failed.', 'skylearn-flashcards' ) ); ?>');
            })
            .always(function() {
                $button.prop('disabled', false).text(originalText);
            });
    });
    <?php endif; ?>
    
    $('.btn-share').on('click', function() {
        var shareText = '<?php echo esc_js( sprintf( esc_html__( 'I just completed %d%% of a flashcard set with %s accuracy using SkyLearn Flashcards!', 'skylearn-flashcards' ), $completion_rate, round( $accuracy, 1 ) ) ); ?>';
        
        if (navigator.share) {
            navigator.share({
                title: '<?php echo esc_js( sprintf( esc_html__( 'My Study Results - %s', 'skylearn-flashcards' ), isset( $set_data['title'] ) ? $set_data['title'] : 'Flashcard Set' ) ); ?>',
                text: shareText,
                url: window.location.href
            });
        } else {
            // Fallback for browsers without Web Share API
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shareText + ' ' + window.location.href).then(function() {
                    alert('<?php esc_js( esc_html_e( 'Share text copied to clipboard!', 'skylearn-flashcards' ) ); ?>');
                });
            } else {
                prompt('<?php esc_js( esc_html_e( 'Copy this share text:', 'skylearn-flashcards' ) ); ?>', shareText + ' ' + window.location.href);
            }
        }
    });
    
    // Modal functionality
    $('.modal-close').on('click', function() {
        $('.skylearn-modal').hide();
    });
    
    $(document).on('click', '.skylearn-modal', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Track results view event
    if (typeof window.skylearn_track_event === 'function') {
        window.skylearn_track_event('results_viewed', {
            set_id: <?php echo esc_js( isset( $set_data['id'] ) ? $set_data['id'] : 0 ); ?>,
            completion_rate: resultsData.completionRate,
            accuracy: resultsData.accuracy,
            time_spent: resultsData.timeSpent
        });
    }
});
</script>