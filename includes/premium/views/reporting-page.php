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
	?>
	<div class="wrap skylearn-admin-page">
		<div class="skylearn-upgrade-notice">
			<div class="upgrade-content">
				<h1><?php esc_html_e( 'Advanced Reporting - Premium Feature', 'skylearn-flashcards' ); ?></h1>
				<p><?php esc_html_e( 'Get detailed insights into user engagement, learning patterns, and performance metrics with our advanced reporting suite.', 'skylearn-flashcards' ); ?></p>
				<a href="<?php echo esc_url( SkyLearn_Flashcards_Premium::get_upgrade_url( 'advanced_reporting' ) ); ?>" 
				   class="button button-primary button-hero" target="_blank">
					<?php esc_html_e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
	return;
}

/**
 * Get reporting data for display
 */
$reporting_instance = new SkyLearn_Flashcards_Advanced_Reporting();
$period_days = absint( $_GET['period'] ?? 30 );
$analytics_data = $reporting_instance->get_analytics_data( array(
	'date_from' => date( 'Y-m-d', strtotime( "-{$period_days} days" ) ),
	'date_to'   => date( 'Y-m-d' )
) );
$learning_progress = $reporting_instance->get_learning_progress();
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
                        <form method="get" action="" id="period-filter">
                            <input type="hidden" name="page" value="skylearn-reports">
                            <select name="period" id="analytics-period" class="filter-select" onchange="this.form.submit()">
                                <option value="7" <?php selected( $period_days, 7 ); ?>><?php esc_html_e( 'Last 7 Days', 'skylearn-flashcards' ); ?></option>
                                <option value="30" <?php selected( $period_days, 30 ); ?>><?php esc_html_e( 'Last 30 Days', 'skylearn-flashcards' ); ?></option>
                                <option value="90" <?php selected( $period_days, 90 ); ?>><?php esc_html_e( 'Last 90 Days', 'skylearn-flashcards' ); ?></option>
                                <option value="365" <?php selected( $period_days, 365 ); ?>><?php esc_html_e( 'Last Year', 'skylearn-flashcards' ); ?></option>
                            </select>
                        </form>
                    </div>
                </div>
                
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( number_format( $analytics_data['overview']['total_views'] ) ); ?></h3>
                            <p><?php esc_html_e( 'Total Views', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( number_format( $analytics_data['overview']['total_completions'] ) ); ?></h3>
                            <p><?php esc_html_e( 'Completions', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-chart-line"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( $analytics_data['overview']['completion_rate'] ); ?>%</h3>
                            <p><?php esc_html_e( 'Completion Rate', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-admin-generic"></span>
                        </div>
                        <div class="card-content">
                            <h3><?php echo esc_html( $analytics_data['overview']['average_accuracy'] ); ?>%</h3>
                            <p><?php esc_html_e( 'Avg. Accuracy', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Activity Chart -->
            <div class="skylearn-panel full-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Daily Activity Trends', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="daily-activity-chart" width="800" height="300"></canvas>
                </div>
            </div>

            <!-- Top Performing Sets -->
            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Top Performing Sets', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="performance-list">
                    <?php if ( ! empty( $analytics_data['top_sets'] ) ) : ?>
                        <?php foreach ( array_slice( $analytics_data['top_sets'], 0, 5 ) as $set ) : ?>
                            <div class="performance-item">
                                <div class="item-info">
                                    <h4><?php echo esc_html( $set['title'] ); ?></h4>
                                    <p><?php printf( 
                                        esc_html__( '%d views, %d completions', 'skylearn-flashcards' ), 
                                        $set['views'], 
                                        $set['completions'] 
                                    ); ?></p>
                                </div>
                                <div class="item-metrics">
                                    <span class="metric-value"><?php echo esc_html( number_format( $set['avg_accuracy'] ?: 0, 1 ) ); ?>%</span>
                                    <span class="metric-label"><?php esc_html_e( 'Avg Score', 'skylearn-flashcards' ); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="skylearn-empty-state">
                            <p><?php esc_html_e( 'No data available for the selected period.', 'skylearn-flashcards' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Learning Progress Breakdown -->
            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Learning Progress', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="progress-breakdown">
                    <?php if ( ! empty( $learning_progress['mastery_levels'] ) ) : ?>
                        <?php foreach ( $learning_progress['mastery_levels'] as $level ) : ?>
                            <div class="progress-item">
                                <div class="progress-label">
                                    <span class="level-indicator level-<?php echo esc_attr( $level['level'] ); ?>"></span>
                                    <?php echo esc_html( ucfirst( $level['level'] ) ); ?>
                                </div>
                                <div class="progress-count"><?php echo esc_html( $level['count'] ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="skylearn-empty-state">
                            <p><?php esc_html_e( 'No progress data available.', 'skylearn-flashcards' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Engagement Stats -->
            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'User Engagement', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="engagement-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo esc_html( number_format( $analytics_data['user_stats']['unique_users'] ?? 0 ) ); ?></div>
                        <div class="stat-label"><?php esc_html_e( 'Unique Users', 'skylearn-flashcards' ); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo esc_html( number_format( $analytics_data['user_stats']['unique_sessions'] ?? 0 ) ); ?></div>
                        <div class="stat-label"><?php esc_html_e( 'Study Sessions', 'skylearn-flashcards' ); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo esc_html( gmdate( 'i:s', $analytics_data['user_stats']['avg_session_time'] ?? 0 ) ); ?></div>
                        <div class="stat-label"><?php esc_html_e( 'Avg Session Time', 'skylearn-flashcards' ); ?></div>
                    </div>
                </div>
            </div>

            <!-- Lead Conversion (if available) -->
            <?php if ( ! empty( $analytics_data['lead_stats'] ) ) : ?>
            <div class="skylearn-panel half-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Lead Conversion', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="conversion-stats">
                    <div class="conversion-item">
                        <div class="conversion-number"><?php echo esc_html( $analytics_data['lead_stats']['total_leads'] ); ?></div>
                        <div class="conversion-label"><?php esc_html_e( 'Total Leads', 'skylearn-flashcards' ); ?></div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-number"><?php echo esc_html( $analytics_data['lead_stats']['contacted_leads'] ); ?></div>
                        <div class="conversion-label"><?php esc_html_e( 'Contacted', 'skylearn-flashcards' ); ?></div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-number"><?php echo esc_html( $analytics_data['lead_stats']['converted_leads'] ); ?></div>
                        <div class="conversion-label"><?php esc_html_e( 'Converted', 'skylearn-flashcards' ); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Study Pattern Insights -->
            <div class="skylearn-panel full-width">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Study Pattern Insights', 'skylearn-flashcards' ); ?></h2>
                </div>
                <div class="insights-grid">
                    <?php if ( ! empty( $learning_progress['study_patterns'] ) ) : ?>
                        <div class="insight-chart">
                            <h4><?php esc_html_e( 'Peak Study Hours', 'skylearn-flashcards' ); ?></h4>
                            <canvas id="study-pattern-chart" width="400" height="200"></canvas>
                        </div>
                    <?php endif; ?>
                    
                    <div class="insights-list">
                        <div class="insight-item">
                            <div class="insight-icon">
                                <span class="dashicons dashicons-lightbulb"></span>
                            </div>
                            <div class="insight-content">
                                <h4><?php esc_html_e( 'Performance Tips', 'skylearn-flashcards' ); ?></h4>
                                <p><?php esc_html_e( 'Sets with 10-15 cards show the highest completion rates and user engagement.', 'skylearn-flashcards' ); ?></p>
                            </div>
                        </div>
                        
                        <div class="insight-item">
                            <div class="insight-icon">
                                <span class="dashicons dashicons-chart-area"></span>
                            </div>
                            <div class="insight-content">
                                <h4><?php esc_html_e( 'Engagement Boost', 'skylearn-flashcards' ); ?></h4>
                                <p><?php esc_html_e( 'Adding images and interactive elements increases completion rates by up to 25%.', 'skylearn-flashcards' ); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="export-modal" class="skylearn-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Export Report', 'skylearn-flashcards' ); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="export-form">
                <div class="form-group">
                    <label for="export-type"><?php esc_html_e( 'Export Type:', 'skylearn-flashcards' ); ?></label>
                    <select id="export-type" name="export_type" class="form-control">
                        <option value="overview"><?php esc_html_e( 'Analytics Overview', 'skylearn-flashcards' ); ?></option>
                        <option value="detailed"><?php esc_html_e( 'Detailed Analytics', 'skylearn-flashcards' ); ?></option>
                        <option value="progress"><?php esc_html_e( 'Learning Progress', 'skylearn-flashcards' ); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="export-format"><?php esc_html_e( 'Format:', 'skylearn-flashcards' ); ?></label>
                    <select id="export-format" name="format" class="form-control">
                        <option value="csv"><?php esc_html_e( 'CSV', 'skylearn-flashcards' ); ?></option>
                        <option value="json"><?php esc_html_e( 'JSON', 'skylearn-flashcards' ); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="export-date-from"><?php esc_html_e( 'Date From (optional):', 'skylearn-flashcards' ); ?></label>
                    <input type="date" id="export-date-from" name="date_from" class="form-control" 
                           max="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                </div>
                <div class="form-group">
                    <label for="export-date-to"><?php esc_html_e( 'Date To (optional):', 'skylearn-flashcards' ); ?></label>
                    <input type="date" id="export-date-to" name="date_to" class="form-control" 
                           max="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                </div>
                <div class="form-group">
                    <label for="export-set-filter"><?php esc_html_e( 'Specific Set (optional):', 'skylearn-flashcards' ); ?></label>
                    <select id="export-set-filter" name="set_filter" class="form-control">
                        <option value=""><?php esc_html_e( 'All Sets', 'skylearn-flashcards' ); ?></option>
                        <?php
                        // Get available flashcard sets
                        $sets = get_posts( array(
                            'post_type'      => 'flashcard_set',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'orderby'        => 'title',
                            'order'          => 'ASC'
                        ) );
                        foreach ( $sets as $set ) {
                            printf(
                                '<option value="%d">%s</option>',
                                esc_attr( $set->ID ),
                                esc_html( $set->post_title )
                            );
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-primary" id="confirm-export"><?php esc_html_e( 'Export', 'skylearn-flashcards' ); ?></button>
            <button type="button" class="button modal-close"><?php esc_html_e( 'Cancel', 'skylearn-flashcards' ); ?></button>
        </div>
    </div>
</div>