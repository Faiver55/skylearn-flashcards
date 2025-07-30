<?php
/**
 * Provide an admin analytics page view for the plugin
 *
 * This file displays analytics and statistics for flashcard usage.
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin/views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get analytics data (placeholder - actual implementation would query the database)
$analytics_data = array(
	'total_sessions' => 0,
	'total_cards_viewed' => 0,
	'average_accuracy' => 0,
	'total_study_time' => 0,
	'most_popular_sets' => array(),
	'recent_activity' => array(),
);

?>

<div class="skylearn-admin-wrap">
	
	<div class="skylearn-admin-header">
		<h1><?php esc_html_e( 'Analytics', 'skylearn-flashcards' ); ?></h1>
		<div class="skylearn-header-actions">
			<select id="skylearn-date-range" class="skylearn-date-filter">
				<option value="7"><?php esc_html_e( 'Last 7 days', 'skylearn-flashcards' ); ?></option>
				<option value="30" selected><?php esc_html_e( 'Last 30 days', 'skylearn-flashcards' ); ?></option>
				<option value="90"><?php esc_html_e( 'Last 90 days', 'skylearn-flashcards' ); ?></option>
				<option value="365"><?php esc_html_e( 'Last year', 'skylearn-flashcards' ); ?></option>
			</select>
			<?php if ( skylearn_is_premium() ) : ?>
				<button class="skylearn-btn skylearn-export-analytics">
					<?php esc_html_e( 'Export Report', 'skylearn-flashcards' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
	
	<div class="skylearn-admin-content">
		
		<!-- Overview Stats -->
		<div class="skylearn-stats-grid">
			<div class="skylearn-stat-card">
				<div class="skylearn-stat-icon">
					<span class="dashicons dashicons-chart-line"></span>
				</div>
				<div class="skylearn-stat-content">
					<span class="skylearn-stat-value"><?php echo esc_html( number_format( $analytics_data['total_sessions'] ) ); ?></span>
					<span class="skylearn-stat-label"><?php esc_html_e( 'Study Sessions', 'skylearn-flashcards' ); ?></span>
					<span class="skylearn-stat-change positive">+12%</span>
				</div>
			</div>
			
			<div class="skylearn-stat-card">
				<div class="skylearn-stat-icon">
					<span class="dashicons dashicons-visibility"></span>
				</div>
				<div class="skylearn-stat-content">
					<span class="skylearn-stat-value"><?php echo esc_html( number_format( $analytics_data['total_cards_viewed'] ) ); ?></span>
					<span class="skylearn-stat-label"><?php esc_html_e( 'Cards Viewed', 'skylearn-flashcards' ); ?></span>
					<span class="skylearn-stat-change positive">+8%</span>
				</div>
			</div>
			
			<div class="skylearn-stat-card">
				<div class="skylearn-stat-icon">
					<span class="dashicons dashicons-yes-alt"></span>
				</div>
				<div class="skylearn-stat-content">
					<span class="skylearn-stat-value"><?php echo esc_html( $analytics_data['average_accuracy'] ); ?>%</span>
					<span class="skylearn-stat-label"><?php esc_html_e( 'Average Accuracy', 'skylearn-flashcards' ); ?></span>
					<span class="skylearn-stat-change neutral">0%</span>
				</div>
			</div>
			
			<div class="skylearn-stat-card">
				<div class="skylearn-stat-icon">
					<span class="dashicons dashicons-clock"></span>
				</div>
				<div class="skylearn-stat-content">
					<span class="skylearn-stat-value"><?php echo esc_html( skylearn_format_duration( $analytics_data['total_study_time'] ) ); ?></span>
					<span class="skylearn-stat-label"><?php esc_html_e( 'Study Time', 'skylearn-flashcards' ); ?></span>
					<span class="skylearn-stat-change positive">+15%</span>
				</div>
			</div>
		</div>
		
		<!-- Charts Section -->
		<div class="skylearn-charts-grid">
			
			<!-- Usage Chart -->
			<div class="skylearn-card skylearn-chart-card">
				<div class="skylearn-card-header">
					<h3 class="skylearn-card-title"><?php esc_html_e( 'Daily Usage', 'skylearn-flashcards' ); ?></h3>
				</div>
				<div class="skylearn-chart-container">
					<canvas id="skylearn-usage-chart" width="400" height="200"></canvas>
					<?php if ( ! skylearn_is_premium() ) : ?>
						<div class="skylearn-chart-overlay">
							<div class="skylearn-upgrade-notice">
								<h4><?php esc_html_e( 'Premium Feature', 'skylearn-flashcards' ); ?></h4>
								<p><?php esc_html_e( 'Upgrade to access detailed analytics charts.', 'skylearn-flashcards' ); ?></p>
								<a href="https://skyian.com/skylearn-flashcards/premium/" target="_blank" class="skylearn-btn">
									<?php esc_html_e( 'Upgrade Now', 'skylearn-flashcards' ); ?>
								</a>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			
			<!-- Performance Chart -->
			<div class="skylearn-card skylearn-chart-card">
				<div class="skylearn-card-header">
					<h3 class="skylearn-card-title"><?php esc_html_e( 'Learning Progress', 'skylearn-flashcards' ); ?></h3>
				</div>
				<div class="skylearn-chart-container">
					<canvas id="skylearn-performance-chart" width="400" height="200"></canvas>
					<?php if ( ! skylearn_is_premium() ) : ?>
						<div class="skylearn-chart-overlay">
							<div class="skylearn-upgrade-notice">
								<h4><?php esc_html_e( 'Premium Feature', 'skylearn-flashcards' ); ?></h4>
								<p><?php esc_html_e( 'Track learning progress with detailed charts.', 'skylearn-flashcards' ); ?></p>
								<a href="https://skyian.com/skylearn-flashcards/premium/" target="_blank" class="skylearn-btn">
									<?php esc_html_e( 'Upgrade Now', 'skylearn-flashcards' ); ?>
								</a>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			
		</div>
		
		<!-- Popular Sets -->
		<div class="skylearn-card">
			<div class="skylearn-card-header">
				<h3 class="skylearn-card-title"><?php esc_html_e( 'Most Popular Flashcard Sets', 'skylearn-flashcards' ); ?></h3>
			</div>
			
			<?php if ( skylearn_is_premium() && ! empty( $analytics_data['most_popular_sets'] ) ) : ?>
				<div class="skylearn-popular-sets">
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Flashcard Set', 'skylearn-flashcards' ); ?></th>
								<th><?php esc_html_e( 'Sessions', 'skylearn-flashcards' ); ?></th>
								<th><?php esc_html_e( 'Avg. Accuracy', 'skylearn-flashcards' ); ?></th>
								<th><?php esc_html_e( 'Total Time', 'skylearn-flashcards' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $analytics_data['most_popular_sets'] as $set ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $set['title'] ); ?></strong></td>
									<td><?php echo esc_html( $set['sessions'] ); ?></td>
									<td><?php echo esc_html( $set['accuracy'] ); ?>%</td>
									<td><?php echo esc_html( skylearn_format_duration( $set['time'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<div class="skylearn-empty-state">
					<div class="skylearn-empty-icon">
						<span class="dashicons dashicons-chart-bar"></span>
					</div>
					<?php if ( ! skylearn_is_premium() ) : ?>
						<h3><?php esc_html_e( 'Premium Analytics', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Upgrade to premium to see detailed analytics about your most popular flashcard sets.', 'skylearn-flashcards' ); ?></p>
						<a href="https://skyian.com/skylearn-flashcards/premium/" target="_blank" class="skylearn-btn">
							<?php esc_html_e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
						</a>
					<?php else : ?>
						<h3><?php esc_html_e( 'No data available', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Analytics data will appear here once users start interacting with your flashcards.', 'skylearn-flashcards' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		
		<!-- Recent Activity -->
		<div class="skylearn-card">
			<div class="skylearn-card-header">
				<h3 class="skylearn-card-title"><?php esc_html_e( 'Recent Activity', 'skylearn-flashcards' ); ?></h3>
			</div>
			
			<?php if ( ! empty( $analytics_data['recent_activity'] ) ) : ?>
				<div class="skylearn-activity-feed">
					<?php foreach ( $analytics_data['recent_activity'] as $activity ) : ?>
						<div class="skylearn-activity-item">
							<div class="skylearn-activity-icon">
								<span class="dashicons dashicons-<?php echo esc_attr( $activity['icon'] ); ?>"></span>
							</div>
							<div class="skylearn-activity-content">
								<p><?php echo esc_html( $activity['message'] ); ?></p>
								<span class="skylearn-activity-time"><?php echo esc_html( $activity['time'] ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="skylearn-empty-state">
					<div class="skylearn-empty-icon">
						<span class="dashicons dashicons-admin-users"></span>
					</div>
					<h3><?php esc_html_e( 'No recent activity', 'skylearn-flashcards' ); ?></h3>
					<p><?php esc_html_e( 'Recent user activity will be displayed here once people start using your flashcards.', 'skylearn-flashcards' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		
		<!-- System Info -->
		<div class="skylearn-card">
			<div class="skylearn-card-header">
				<h3 class="skylearn-card-title"><?php esc_html_e( 'System Information', 'skylearn-flashcards' ); ?></h3>
			</div>
			
			<table class="skylearn-form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Plugin Version', 'skylearn-flashcards' ); ?></th>
					<td><?php echo esc_html( SKYLEARN_FLASHCARDS_VERSION ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'WordPress Version', 'skylearn-flashcards' ); ?></th>
					<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'PHP Version', 'skylearn-flashcards' ); ?></th>
					<td><?php echo esc_html( PHP_VERSION ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Database Version', 'skylearn-flashcards' ); ?></th>
					<td><?php echo esc_html( $GLOBALS['wpdb']->db_version() ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Memory Usage', 'skylearn-flashcards' ); ?></th>
					<td>
						<?php 
						$memory = skylearn_get_memory_usage();
						echo esc_html( size_format( $memory['current'] ) . ' / ' . $memory['limit'] );
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Premium Status', 'skylearn-flashcards' ); ?></th>
					<td>
						<?php if ( skylearn_is_premium() ) : ?>
							<span class="skylearn-status skylearn-status-active"><?php esc_html_e( 'Active', 'skylearn-flashcards' ); ?></span>
						<?php else : ?>
							<span class="skylearn-status skylearn-status-inactive"><?php esc_html_e( 'Inactive', 'skylearn-flashcards' ); ?></span>
							<a href="https://skyian.com/skylearn-flashcards/premium/" target="_blank"><?php esc_html_e( 'Upgrade', 'skylearn-flashcards' ); ?></a>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		
	</div>
	
</div>