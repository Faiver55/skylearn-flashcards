<?php
/**
 * Advanced reporting for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 */

/**
 * Advanced reporting class (Premium Feature).
 *
 * Provides detailed analytics and reporting for flashcard performance,
 * user engagement, and learning outcomes.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Advanced_Reporting {

	/**
	 * Initialize advanced reporting
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		// Only initialize if premium features are available
		if ( ! skylearn_is_premium() ) {
			return;
		}
		
		// Add admin menu hooks
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// AJAX handlers
		add_action( 'wp_ajax_skylearn_get_report_data', array( $this, 'ajax_get_report_data' ) );
		add_action( 'wp_ajax_skylearn_export_report', array( $this, 'ajax_export_report' ) );
		
	}

	/**
	 * Add advanced reporting to admin menu
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		
		add_submenu_page(
			'skylearn-flashcards',
			__( 'Advanced Reports', 'skylearn-flashcards' ),
			__( 'Reports', 'skylearn-flashcards' ),
			'view_skylearn_analytics',
			'skylearn-reports',
			array( $this, 'display_reports_page' )
		);
		
	}

	/**
	 * Display the advanced reports page
	 *
	 * @since    1.0.0
	 */
	public function display_reports_page() {
		include SKYLEARN_FLASHCARDS_PATH . 'includes/premium/views/reporting-page.php';
	}

	/**
	 * Get comprehensive analytics data
	 *
	 * @since    1.0.0
	 * @param    array   $args   Query arguments
	 * @return   array           Analytics data
	 */
	public function get_analytics_data( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			'date_to'   => date( 'Y-m-d' ),
			'set_id'    => null,
			'user_id'   => null,
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
		$leads_table = $wpdb->prefix . 'skylearn_flashcard_leads';
		
		// Base where clause
		$where_clauses = array( "created_at BETWEEN %s AND %s" );
		$where_values = array( $args['date_from'] . ' 00:00:00', $args['date_to'] . ' 23:59:59' );
		
		if ( $args['set_id'] ) {
			$where_clauses[] = 'set_id = %d';
			$where_values[] = $args['set_id'];
		}
		
		if ( $args['user_id'] ) {
			$where_clauses[] = 'user_id = %d';
			$where_values[] = $args['user_id'];
		}
		
		$where_sql = implode( ' AND ', $where_clauses );
		
		// Get overall statistics
		$total_views = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$analytics_table} WHERE action = 'view' AND {$where_sql}",
			$where_values
		) );
		
		$total_completions = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$analytics_table} WHERE action = 'complete' AND {$where_sql}",
			$where_values
		) );
		
		$average_accuracy = $wpdb->get_var( $wpdb->prepare(
			"SELECT AVG(accuracy) FROM {$analytics_table} WHERE action = 'complete' AND {$where_sql}",
			$where_values
		) );
		
		$total_time_spent = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(time_spent) FROM {$analytics_table} WHERE {$where_sql}",
			$where_values
		) );
		
		// Get daily statistics for charts
		$daily_stats = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				DATE(created_at) as date,
				COUNT(CASE WHEN action = 'view' THEN 1 END) as views,
				COUNT(CASE WHEN action = 'complete' THEN 1 END) as completions,
				AVG(CASE WHEN action = 'complete' THEN accuracy END) as avg_accuracy,
				SUM(time_spent) as total_time
			FROM {$analytics_table} 
			WHERE {$where_sql}
			GROUP BY DATE(created_at)
			ORDER BY date",
			$where_values
		), ARRAY_A );
		
		// Get top performing sets
		$top_sets = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				set_id,
				COUNT(CASE WHEN action = 'view' THEN 1 END) as views,
				COUNT(CASE WHEN action = 'complete' THEN 1 END) as completions,
				AVG(CASE WHEN action = 'complete' THEN accuracy END) as avg_accuracy
			FROM {$analytics_table} 
			WHERE {$where_sql}
			GROUP BY set_id
			ORDER BY completions DESC
			LIMIT 10",
			$where_values
		), ARRAY_A );
		
		// Add set titles
		foreach ( $top_sets as &$set ) {
			$set['title'] = get_the_title( $set['set_id'] ) ?: __( 'Unknown Set', 'skylearn-flashcards' );
		}
		
		// Get user engagement statistics
		$user_stats = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				COUNT(DISTINCT user_id) as unique_users,
				COUNT(DISTINCT session_id) as unique_sessions,
				AVG(time_spent) as avg_session_time
			FROM {$analytics_table} 
			WHERE {$where_sql}",
			$where_values
		), ARRAY_A );
		
		// Get lead conversion data (if available)
		$lead_stats = array();
		if ( skylearn_is_premium() ) {
			$lead_stats = $wpdb->get_results( $wpdb->prepare(
				"SELECT 
					COUNT(*) as total_leads,
					COUNT(CASE WHEN status = 'new' THEN 1 END) as new_leads,
					COUNT(CASE WHEN status = 'contacted' THEN 1 END) as contacted_leads,
					COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted_leads
				FROM {$leads_table} 
				WHERE created_at BETWEEN %s AND %s",
				$args['date_from'] . ' 00:00:00',
				$args['date_to'] . ' 23:59:59'
			), ARRAY_A );
		}
		
		return array(
			'overview' => array(
				'total_views'       => $total_views ?: 0,
				'total_completions' => $total_completions ?: 0,
				'average_accuracy'  => round( $average_accuracy ?: 0, 2 ),
				'total_time_spent'  => $total_time_spent ?: 0,
				'completion_rate'   => $total_views > 0 ? round( ( $total_completions / $total_views ) * 100, 2 ) : 0,
			),
			'daily_stats'  => $daily_stats,
			'top_sets'     => $top_sets,
			'user_stats'   => $user_stats[0] ?? array(),
			'lead_stats'   => $lead_stats[0] ?? array(),
			'date_range'   => array(
				'from' => $args['date_from'],
				'to'   => $args['date_to'],
			),
		);
	}

	/**
	 * Get learning progress analytics
	 *
	 * @since    1.0.0
	 * @param    int     $user_id    User ID (optional)
	 * @return   array               Progress data
	 */
	public function get_learning_progress( $user_id = null ) {
		global $wpdb;
		
		$progress_table = $wpdb->prefix . 'skylearn_flashcard_progress';
		$analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
		
		$where_clause = $user_id ? 'WHERE user_id = %d' : '';
		$query_values = $user_id ? array( $user_id ) : array();
		
		// Get mastery levels
		$mastery_data = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				CASE 
					WHEN mastery_level >= 0.8 THEN 'mastered'
					WHEN mastery_level >= 0.6 THEN 'good'
					WHEN mastery_level >= 0.4 THEN 'learning'
					ELSE 'struggling'
				END as level,
				COUNT(*) as count
			FROM {$progress_table} 
			{$where_clause}
			GROUP BY 
				CASE 
					WHEN mastery_level >= 0.8 THEN 'mastered'
					WHEN mastery_level >= 0.6 THEN 'good'
					WHEN mastery_level >= 0.4 THEN 'learning'
					ELSE 'struggling'
				END",
			$query_values
		), ARRAY_A );
		
		// Get study patterns
		$study_patterns = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				HOUR(created_at) as hour,
				COUNT(*) as sessions
			FROM {$analytics_table} 
			WHERE action = 'complete' {$user_id ? 'AND user_id = %d' : ''}
			GROUP BY HOUR(created_at)
			ORDER BY hour",
			$query_values
		), ARRAY_A );
		
		return array(
			'mastery_levels' => $mastery_data,
			'study_patterns' => $study_patterns,
		);
	}

	/**
	 * AJAX handler for getting report data
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_report_data() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_reports' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check permissions
		if ( ! current_user_can( 'view_skylearn_analytics' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}
		
		$report_type = sanitize_text_field( $_POST['report_type'] ?? 'overview' );
		$date_from = sanitize_text_field( $_POST['date_from'] ?? '' );
		$date_to = sanitize_text_field( $_POST['date_to'] ?? '' );
		$set_id = absint( $_POST['set_id'] ?? 0 );
		
		$args = array();
		if ( $date_from ) $args['date_from'] = $date_from;
		if ( $date_to ) $args['date_to'] = $date_to;
		if ( $set_id ) $args['set_id'] = $set_id;
		
		switch ( $report_type ) {
			case 'overview':
				$data = $this->get_analytics_data( $args );
				break;
			case 'progress':
				$data = $this->get_learning_progress( $args['user_id'] ?? null );
				break;
			default:
				wp_send_json_error( array( 'message' => __( 'Invalid report type.', 'skylearn-flashcards' ) ) );
		}
		
		wp_send_json_success( $data );
	}

	/**
	 * AJAX handler for exporting report data
	 *
	 * @since    1.0.0
	 */
	public function ajax_export_report() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_export_report' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check permissions
		if ( ! current_user_can( 'export_skylearn_flashcards' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}
		
		$export_type = sanitize_text_field( $_POST['export_type'] ?? 'analytics' );
		$format = sanitize_text_field( $_POST['format'] ?? 'csv' );
		
		// Generate export data
		$export_data = '';
		$filename = '';
		
		switch ( $export_type ) {
			case 'analytics':
				$data = $this->get_analytics_data();
				$export_data = $this->format_analytics_export( $data, $format );
				$filename = 'skylearn-analytics-' . date( 'Y-m-d' ) . '.' . $format;
				break;
			case 'progress':
				$data = $this->get_learning_progress();
				$export_data = $this->format_progress_export( $data, $format );
				$filename = 'skylearn-progress-' . date( 'Y-m-d' ) . '.' . $format;
				break;
		}
		
		if ( $export_data ) {
			wp_send_json_success( array( 
				'data' => $export_data,
				'filename' => $filename,
				'format' => $format
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Export failed.', 'skylearn-flashcards' ) ) );
		}
	}

	/**
	 * Format analytics data for export
	 *
	 * @since    1.0.0
	 * @param    array   $data     Analytics data
	 * @param    string  $format   Export format
	 * @return   string            Formatted export data
	 */
	private function format_analytics_export( $data, $format ) {
		
		if ( $format === 'csv' ) {
			$csv = "Date,Views,Completions,Average Accuracy,Total Time (seconds)\n";
			foreach ( $data['daily_stats'] as $day ) {
				$csv .= sprintf( 
					"%s,%d,%d,%.2f,%d\n",
					$day['date'],
					$day['views'],
					$day['completions'],
					$day['avg_accuracy'] ?: 0,
					$day['total_time'] ?: 0
				);
			}
			return $csv;
		}
		
		return '';
	}

	/**
	 * Format progress data for export
	 *
	 * @since    1.0.0
	 * @param    array   $data     Progress data
	 * @param    string  $format   Export format
	 * @return   string            Formatted export data
	 */
	private function format_progress_export( $data, $format ) {
		
		if ( $format === 'csv' ) {
			$csv = "Mastery Level,Count\n";
			foreach ( $data['mastery_levels'] as $level ) {
				$csv .= sprintf( "%s,%d\n", $level['level'], $level['count'] );
			}
			return $csv;
		}
		
		return '';
	}

}