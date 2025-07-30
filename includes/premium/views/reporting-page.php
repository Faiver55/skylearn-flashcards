<?php
/**
 * Premium reporting dashboard view
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium/views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if premium features are available
if ( ! skylearn_is_premium() ) {
	return;
}

/**
 * Get reporting data for display
 */
$reporting_instance = new SkyLearn_Flashcards_Advanced_Reporting( 'skylearn-flashcards', '1.0.0' );
$analytics_data = $reporting_instance->get_analytics_data();
$performance_data = $reporting_instance->get_performance_data();
?>

<div class="wrap skylearn-admin-page skylearn-premium-page">
    <div class="skylearn-header">
        <div class="skylearn-header-content">
            <img src="<?php echo esc_url( skylearn_get_logo_url( 'horizontal' ) ); ?>" 
                 alt="SkyLearn Flashcards" class="skylearn-logo">
            <h1>
                <?php esc_html_e( 'Advanced Reporting', 'skylearn-flashcards' ); ?>
                <span class="premium-badge"><?php esc_html_e( 'Premium', 'skylearn-flashcards' ); ?></span>
            </h1>
        </div>
        <div class="skylearn-header-actions">
            <button type="button" class="button button-primary" id="export-report">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export Report', 'skylearn-flashcards' ); ?>
            </button>
            <button type="button" class="button" id="refresh-data">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e( 'Refresh Data', 'skylearn-flashcards' ); ?>
            </button>
        </div>
    </div>

    <div class="skylearn-content">
        <div class="skylearn-grid">
            
            <!-- Analytics Overview -->
            <div class="skylearn-panel full-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Analytics Overview', 'skylearn-flashcards' ); ?></h2>
                    <div class="panel-controls">
                        <select id="analytics-period" class="filter-select">
                            <option value="7"><?php esc_html_e( 'Last 7 Days', 'skylearn-flashcards' ); ?></option>
                            <option value="30" selected><?php esc_html_e( 'Last 30 Days', 'skylearn-flashcards' ); ?></option>
                            <option value="90"><?php esc_html_e( 'Last 90 Days', 'skylearn-flashcards' ); ?></option>
                            <option value="365"><?php esc_html_e( 'Last Year', 'skylearn-flashcards' ); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( number_format( $analytics_data['total_views'] ?? 0 ) ); ?></h3>
                            <p><?php esc_html_e( 'Total Views', 'skylearn-flashcards' ); ?></p>
                            <span class="trend positive">+12%</span>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-groups"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( number_format( $analytics_data['unique_users'] ?? 0 ) ); ?></h3>
                            <p><?php esc_html_e( 'Unique Users', 'skylearn-flashcards' ); ?></p>
                            <span class="trend positive">+8%</span>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( number_format( $analytics_data['completions'] ?? 0 ) ); ?></h3>
                            <p><?php esc_html_e( 'Completions', 'skylearn-flashcards' ); ?></p>
                            <span class="trend positive">+15%</span>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( gmdate( 'i:s', $analytics_data['avg_time'] ?? 0 ) ); ?></h3>
                            <p><?php esc_html_e( 'Avg. Study Time', 'skylearn-flashcards' ); ?></p>
                            <span class="trend neutral">+2%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'User Engagement', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="engagement-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Completion Rates', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="completion-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Top Performing Sets -->
            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Top Performing Sets', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="performance-list">
                    <!-- TODO: Dynamic data from database -->
                    <div class="performance-item">
                        <div class="item-info">
                            <h4><?php esc_html_e( 'Sample Set 1', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( '120 views, 85% completion rate', 'skylearn-flashcards' ); ?></p>
                        </div>
                        <div class="item-metrics">
                            <span class="metric-value">4.8</span>
                            <span class="metric-label"><?php esc_html_e( 'Avg Score', 'skylearn-flashcards' ); ?></span>
                        </div>
                    </div>
                    
                    <div class="performance-item">
                        <div class="item-info">
                            <h4><?php esc_html_e( 'Sample Set 2', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( '98 views, 78% completion rate', 'skylearn-flashcards' ); ?></p>
                        </div>
                        <div class="item-metrics">
                            <span class="metric-value">4.5</span>
                            <span class="metric-label"><?php esc_html_e( 'Avg Score', 'skylearn-flashcards' ); ?></span>
                        </div>
                    </div>
                    
                    <div class="performance-item">
                        <div class="item-info">
                            <h4><?php esc_html_e( 'Sample Set 3', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( '87 views, 72% completion rate', 'skylearn-flashcards' ); ?></p>
                        </div>
                        <div class="item-metrics">
                            <span class="metric-value">4.2</span>
                            <span class="metric-label"><?php esc_html_e( 'Avg Score', 'skylearn-flashcards' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Insights -->
            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Learning Insights', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="insights-list">
                    <div class="insight-item">
                        <div class="insight-icon">
                            <span class="dashicons dashicons-lightbulb"></span>
                        </div>
                        <div class="insight-content">
                            <h4><?php esc_html_e( 'Peak Study Times', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Most users study between 7-9 PM on weekdays.', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="insight-item">
                        <div class="insight-icon">
                            <span class="dashicons dashicons-chart-area"></span>
                        </div>
                        <div class="insight-content">
                            <h4><?php esc_html_e( 'Optimal Set Size', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Sets with 10-15 cards show highest completion rates.', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="insight-item">
                        <div class="insight-icon">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                        <div class="insight-content">
                            <h4><?php esc_html_e( 'User Retention', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( '68% of users return within 7 days of first study session.', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics Table -->
            <div class="skylearn-panel full-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Detailed Analytics', 'skylearn-flashcards' ); ?></h2>
                    <div class="panel-controls">
                        <input type="text" placeholder="<?php esc_attr_e( 'Search sets...', 'skylearn-flashcards' ); ?>" class="search-input">
                        <select class="filter-select">
                            <option value="all"><?php esc_html_e( 'All Sets', 'skylearn-flashcards' ); ?></option>
                            <option value="published"><?php esc_html_e( 'Published', 'skylearn-flashcards' ); ?></option>
                            <option value="draft"><?php esc_html_e( 'Draft', 'skylearn-flashcards' ); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="skylearn-table-container">
                    <table class="widefat striped skylearn-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Flashcard Set', 'skylearn-flashcards' ); ?></th>
                                <th><?php esc_html_e( 'Views', 'skylearn-flashcards' ); ?></th>
                                <th><?php esc_html_e( 'Completions', 'skylearn-flashcards' ); ?></th>
                                <th><?php esc_html_e( 'Completion Rate', 'skylearn-flashcards' ); ?></th>
                                <th><?php esc_html_e( 'Avg. Score', 'skylearn-flashcards' ); ?></th>
                                <th><?php esc_html_e( 'Avg. Time', 'skylearn-flashcards' ); ?></th>
                                <th><?php esc_html_e( 'Last Activity', 'skylearn-flashcards' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- TODO: Dynamic data from database -->
                            <tr>
                                <td>
                                    <strong><?php esc_html_e( 'Sample Flashcard Set 1', 'skylearn-flashcards' ); ?></strong>
                                    <div class="set-meta"><?php esc_html_e( '15 cards', 'skylearn-flashcards' ); ?></div>
                                </td>
                                <td><?php echo esc_html( number_format( 120 ) ); ?></td>
                                <td><?php echo esc_html( number_format( 102 ) ); ?></td>
                                <td>
                                    <span class="completion-rate high">85%</span>
                                </td>
                                <td>4.8/5.0</td>
                                <td>04:32</td>
                                <td><?php echo esc_html( human_time_diff( strtotime( '-2 hours' ) ) ); ?> ago</td>
                            </tr>
                            <tr>
                                <td colspan="7" class="skylearn-placeholder">
                                    <?php esc_html_e( 'Detailed analytics data will be displayed here when available.', 'skylearn-flashcards' ); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // TODO: Initialize charts and interactive elements
    console.log('Advanced Reporting page loaded');
    
    // Placeholder for chart initialization
    if (typeof Chart !== 'undefined') {
        // Initialize engagement chart
        var engagementCtx = document.getElementById('engagement-chart');
        if (engagementCtx) {
            // TODO: Implement actual chart with real data
        }
        
        // Initialize completion chart
        var completionCtx = document.getElementById('completion-chart');
        if (completionCtx) {
            // TODO: Implement actual chart with real data
        }
    }
    
    // Export functionality
    $('#export-report').on('click', function() {
        alert('<?php esc_js( esc_html_e( 'Export functionality will be implemented in the next phase.', 'skylearn-flashcards' ) ); ?>');
    });
    
    // Refresh data
    $('#refresh-data').on('click', function() {
        location.reload();
    });
});
</script>