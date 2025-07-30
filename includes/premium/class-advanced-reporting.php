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
		
		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_reporting_assets' ) );
		
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
	 * Enqueue reporting assets
	 *
	 * @since    1.0.0
	 * @param    string   $hook_suffix   Admin page hook suffix
	 */
	public function enqueue_reporting_assets( $hook_suffix ) {
		
		// Only load on our reporting page
		if ( $hook_suffix !== 'skylearn-flashcards_page_skylearn-reports' ) {
			return;
		}
		
		// Enqueue Chart.js from node_modules
		wp_enqueue_script(
			'chartjs',
			SKYLEARN_FLASHCARDS_URL . 'node_modules/chart.js/dist/chart.umd.min.js',
			array(),
			'4.3.0',
			true
		);
		
		// Enqueue reporting CSS
		wp_enqueue_style(
			'skylearn-reporting',
			SKYLEARN_FLASHCARDS_ASSETS . 'css/reporting.css',
			array(),
			SKYLEARN_FLASHCARDS_VERSION
		);
		
		// Enqueue reporting JavaScript
		wp_enqueue_script(
			'skylearn-reporting',
			SKYLEARN_FLASHCARDS_ASSETS . 'js/reporting.js',
			array( 'jquery', 'chartjs' ),
			SKYLEARN_FLASHCARDS_VERSION,
			true
		);
		
		// Prepare data for JavaScript
		$period_days = absint( $_GET['period'] ?? 30 );
		$analytics_data = $this->get_analytics_data( array(
			'date_from' => date( 'Y-m-d', strtotime( "-{$period_days} days" ) ),
			'date_to'   => date( 'Y-m-d' )
		) );
		$learning_progress = $this->get_learning_progress();
		
		// Localize script with data and translations
		wp_localize_script( 'skylearn-reporting', 'skylearn_admin', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonces'  => array(
				'get_report_data' => wp_create_nonce( 'skylearn_reports' ),
				'export_report'   => wp_create_nonce( 'skylearn_export_report' ),
			),
			'i18n'    => array(
				'views'           => __( 'Views', 'skylearn-flashcards' ),
				'completions'     => __( 'Completions', 'skylearn-flashcards' ),
				'study_sessions'  => __( 'Study Sessions', 'skylearn-flashcards' ),
				'mastered'        => __( 'Mastered', 'skylearn-flashcards' ),
				'good'            => __( 'Good', 'skylearn-flashcards' ),
				'learning'        => __( 'Learning', 'skylearn-flashcards' ),
				'struggling'      => __( 'Struggling', 'skylearn-flashcards' ),
				'exporting'       => __( 'Exporting...', 'skylearn-flashcards' ),
				'export_success'  => __( 'Export completed successfully!', 'skylearn-flashcards' ),
				'export_error'    => __( 'Export request failed.', 'skylearn-flashcards' ),
				'refreshing'      => __( 'Refreshing data...', 'skylearn-flashcards' ),
				'refresh_success' => __( 'Data refreshed successfully!', 'skylearn-flashcards' ),
				'refresh_error'   => __( 'Failed to refresh data.', 'skylearn-flashcards' ),
				'select_option'   => __( 'Select an option', 'skylearn-flashcards' ),
			),
		) );
		
		// Add reporting data for charts
		wp_localize_script( 'skylearn-reporting', 'skylernReportingData', array(
			'dailyStats'     => $analytics_data['daily_stats'] ?? array(),
			'studyPatterns'  => $learning_progress['study_patterns'] ?? array(),
			'masteryLevels'  => $learning_progress['mastery_levels'] ?? array(),
			'overview'       => $analytics_data['overview'] ?? array(),
		) );
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
		$study_patterns_where = $user_id ? 'WHERE action = \'complete\' AND user_id = %d' : 'WHERE action = \'complete\'';
		$study_patterns = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				HOUR(created_at) as hour,
				COUNT(*) as sessions
			FROM {$analytics_table} 
			{$study_patterns_where}
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
		
		$export_type = sanitize_text_field( $_POST['export_type'] ?? 'overview' );
		$format = sanitize_text_field( $_POST['format'] ?? 'csv' );
		$date_from = sanitize_text_field( $_POST['date_from'] ?? '' );
		$date_to = sanitize_text_field( $_POST['date_to'] ?? '' );
		$set_id = absint( $_POST['set_id'] ?? 0 );
		
		// Prepare filter arguments
		$args = array();
		if ( $date_from ) $args['date_from'] = $date_from;
		if ( $date_to ) $args['date_to'] = $date_to;
		if ( $set_id ) $args['set_id'] = $set_id;
		
		// Generate export data
		$export_data = '';
		$filename = '';
		$mime_type = 'text/plain';
		
		switch ( $export_type ) {
			case 'overview':
			case 'detailed':
				$data = $this->get_analytics_data( $args );
				$export_result = $this->format_analytics_export( $data, $format );
				$export_data = $export_result['content'];
				$mime_type = $export_result['mime_type'];
				$filename = 'skylearn-analytics-' . date( 'Y-m-d-H-i-s' ) . '.' . $format;
				break;
			case 'progress':
				$data = $this->get_learning_progress( $args['user_id'] ?? null );
				$export_result = $this->format_progress_export( $data, $format );
				$export_data = $export_result['content'];
				$mime_type = $export_result['mime_type'];
				$filename = 'skylearn-progress-' . date( 'Y-m-d-H-i-s' ) . '.' . $format;
				break;
			default:
				wp_send_json_error( array( 'message' => __( 'Invalid export type.', 'skylearn-flashcards' ) ) );
		}
		
		if ( $export_data ) {
			wp_send_json_success( array( 
				'content'   => $export_data,
				'filename'  => $filename,
				'format'    => $format,
				'mime_type' => $mime_type
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Export failed or no data available.', 'skylearn-flashcards' ) ) );
		}
	}

	/**
	 * Format analytics data for export
	 *
	 * @since    1.0.0
	 * @param    array   $data     Analytics data
	 * @param    string  $format   Export format
	 * @return   array             Array with content and mime_type
	 */
	private function format_analytics_export( $data, $format ) {
		
		switch ( $format ) {
			case 'csv':
				return $this->format_analytics_csv( $data );
			case 'json':
				return $this->format_analytics_json( $data );
			default:
				return array( 'content' => '', 'mime_type' => 'text/plain' );
		}
	}

	/**
	 * Format analytics data as CSV
	 *
	 * @since    1.0.0
	 * @param    array   $data     Analytics data
	 * @return   array             Array with content and mime_type
	 */
	private function format_analytics_csv( $data ) {
		$csv = '';
		
		// Overview section
		$csv .= "\"SkyLearn Flashcards Analytics Report\"\n";
		$csv .= "\"Generated: " . current_time( 'Y-m-d H:i:s' ) . "\"\n";
		$csv .= "\"Period: " . $data['date_range']['from'] . " to " . $data['date_range']['to'] . "\"\n\n";
		
		// Overview statistics
		$csv .= "\"OVERVIEW STATISTICS\"\n";
		$csv .= "\"Metric\",\"Value\"\n";
		$csv .= "\"Total Views\"," . $data['overview']['total_views'] . "\n";
		$csv .= "\"Total Completions\"," . $data['overview']['total_completions'] . "\n";
		$csv .= "\"Completion Rate\"," . $data['overview']['completion_rate'] . "%\n";
		$csv .= "\"Average Accuracy\"," . $data['overview']['average_accuracy'] . "%\n";
		$csv .= "\"Total Time Spent (seconds)\"," . $data['overview']['total_time_spent'] . "\n\n";
		
		// Daily statistics
		$csv .= "\"DAILY STATISTICS\"\n";
		$csv .= "\"Date\",\"Views\",\"Completions\",\"Average Accuracy (%)\",\"Total Time (seconds)\"\n";
		foreach ( $data['daily_stats'] as $day ) {
			$csv .= sprintf( 
				'"%s",%d,%d,%.2f,%d' . "\n",
				$day['date'],
				$day['views'] ?: 0,
				$day['completions'] ?: 0,
				$day['avg_accuracy'] ?: 0,
				$day['total_time'] ?: 0
			);
		}
		
		// Top performing sets
		if ( ! empty( $data['top_sets'] ) ) {
			$csv .= "\n\"TOP PERFORMING SETS\"\n";
			$csv .= "\"Set Title\",\"Views\",\"Completions\",\"Average Accuracy (%)\"\n";
			foreach ( $data['top_sets'] as $set ) {
				$csv .= sprintf(
					'"%s",%d,%d,%.2f' . "\n",
					str_replace( '"', '""', $set['title'] ),
					$set['views'] ?: 0,
					$set['completions'] ?: 0,
					$set['avg_accuracy'] ?: 0
				);
			}
		}
		
		// User engagement
		if ( ! empty( $data['user_stats'] ) ) {
			$csv .= "\n\"USER ENGAGEMENT\"\n";
			$csv .= "\"Metric\",\"Value\"\n";
			$csv .= "\"Unique Users\"," . ( $data['user_stats']['unique_users'] ?: 0 ) . "\n";
			$csv .= "\"Unique Sessions\"," . ( $data['user_stats']['unique_sessions'] ?: 0 ) . "\n";
			$csv .= "\"Average Session Time (seconds)\"," . ( $data['user_stats']['avg_session_time'] ?: 0 ) . "\n";
		}
		
		// Lead statistics (if available)
		if ( ! empty( $data['lead_stats'] ) ) {
			$csv .= "\n\"LEAD CONVERSION\"\n";
			$csv .= "\"Metric\",\"Value\"\n";
			$csv .= "\"Total Leads\"," . ( $data['lead_stats']['total_leads'] ?: 0 ) . "\n";
			$csv .= "\"New Leads\"," . ( $data['lead_stats']['new_leads'] ?: 0 ) . "\n";
			$csv .= "\"Contacted Leads\"," . ( $data['lead_stats']['contacted_leads'] ?: 0 ) . "\n";
			$csv .= "\"Converted Leads\"," . ( $data['lead_stats']['converted_leads'] ?: 0 ) . "\n";
		}
		
		return array(
			'content'   => $csv,
			'mime_type' => 'text/csv'
		);
	}

	/**
	 * Format analytics data as JSON
	 *
	 * @since    1.0.0
	 * @param    array   $data     Analytics data
	 * @return   array             Array with content and mime_type
	 */
	private function format_analytics_json( $data ) {
		$export_data = array(
			'report_type' => 'analytics',
			'generated_at' => current_time( 'c' ),
			'date_range' => $data['date_range'],
			'overview' => $data['overview'],
			'daily_statistics' => $data['daily_stats'],
			'top_sets' => $data['top_sets'],
			'user_engagement' => $data['user_stats'] ?? array(),
			'lead_conversion' => $data['lead_stats'] ?? array(),
		);
		
		return array(
			'content'   => wp_json_encode( $export_data, JSON_PRETTY_PRINT ),
			'mime_type' => 'application/json'
		);
	}

	/**
	 * Format progress data for export
	 *
	 * @since    1.0.0
	 * @param    array   $data     Progress data
	 * @param    string  $format   Export format
	 * @return   array             Array with content and mime_type
	 */
	private function format_progress_export( $data, $format ) {
		
		switch ( $format ) {
			case 'csv':
				return $this->format_progress_csv( $data );
			case 'json':
				return $this->format_progress_json( $data );
			default:
				return array( 'content' => '', 'mime_type' => 'text/plain' );
		}
	}

	/**
	 * Format progress data as CSV
	 *
	 * @since    1.0.0
	 * @param    array   $data     Progress data
	 * @return   array             Array with content and mime_type
	 */
	private function format_progress_csv( $data ) {
		$csv = '';
		
		// Header
		$csv .= "\"SkyLearn Flashcards Learning Progress Report\"\n";
		$csv .= "\"Generated: " . current_time( 'Y-m-d H:i:s' ) . "\"\n\n";
		
		// Mastery levels
		$csv .= "\"MASTERY LEVELS\"\n";
		$csv .= "\"Mastery Level\",\"Count\"\n";
		if ( ! empty( $data['mastery_levels'] ) ) {
			foreach ( $data['mastery_levels'] as $level ) {
				$csv .= sprintf( '"%s",%d' . "\n", ucfirst( $level['level'] ), $level['count'] );
			}
		}
		
		// Study patterns
		if ( ! empty( $data['study_patterns'] ) ) {
			$csv .= "\n\"STUDY PATTERNS (BY HOUR)\"\n";
			$csv .= "\"Hour\",\"Sessions\"\n";
			foreach ( $data['study_patterns'] as $pattern ) {
				$csv .= sprintf( '"%s:00",%d' . "\n", $pattern['hour'], $pattern['sessions'] );
			}
		}
		
		return array(
			'content'   => $csv,
			'mime_type' => 'text/csv'
		);
	}

	/**
	 * Format progress data as JSON
	 *
	 * @since    1.0.0
	 * @param    array   $data     Progress data
	 * @return   array             Array with content and mime_type
	 */
	private function format_progress_json( $data ) {
		$export_data = array(
			'report_type' => 'learning_progress',
			'generated_at' => current_time( 'c' ),
			'mastery_levels' => $data['mastery_levels'] ?? array(),
			'study_patterns' => $data['study_patterns'] ?? array(),
		);
		
		return array(
			'content'   => wp_json_encode( $export_data, JSON_PRETTY_PRINT ),
			'mime_type' => 'application/json'
		);
	}

	/**
	 * AJAX handler for student results export
	 *
	 * @since    1.0.0
	 */
	public function ajax_export_student_results() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_export_student_results' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check if premium and user is logged in
		if ( ! skylearn_is_premium() || ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'This feature requires a premium license and user account.', 'skylearn-flashcards' ) ) );
		}
		
		$set_id = absint( $_POST['set_id'] ?? 0 );
		$results = $_POST['results'] ?? array();
		$stats = $_POST['stats'] ?? array();
		$format = sanitize_text_field( $_POST['format'] ?? 'pdf' );
		$include_history = absint( $_POST['include_history'] ?? 1 );
		$include_recommendations = absint( $_POST['include_recommendations'] ?? 1 );
		
		// Get flashcard set data
		$flashcard_set = skylearn_get_flashcard_set( $set_id );
		if ( ! $flashcard_set ) {
			wp_send_json_error( array( 'message' => __( 'Flashcard set not found.', 'skylearn-flashcards' ) ) );
		}
		
		// Generate export data
		$export_result = $this->format_student_results_export( 
			$flashcard_set, 
			$results, 
			$stats, 
			$format, 
			$include_history, 
			$include_recommendations 
		);
		
		if ( $export_result['content'] ) {
			wp_send_json_success( array( 
				'content'   => $export_result['content'],
				'filename'  => $export_result['filename'],
				'format'    => $format,
				'mime_type' => $export_result['mime_type']
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Export failed or no data available.', 'skylearn-flashcards' ) ) );
		}
	}

	/**
	 * Format student results for export
	 *
	 * @since    1.0.0
	 * @param    array   $flashcard_set        Flashcard set data
	 * @param    array   $results              Study results
	 * @param    array   $stats                Performance statistics  
	 * @param    string  $format               Export format
	 * @param    bool    $include_history      Include progress history
	 * @param    bool    $include_recommendations Include study recommendations
	 * @return   array                         Array with content, filename, and mime_type
	 */
	private function format_student_results_export( $flashcard_set, $results, $stats, $format, $include_history, $include_recommendations ) {
		
		$user = wp_get_current_user();
		$timestamp = current_time( 'Y-m-d-H-i-s' );
		
		switch ( $format ) {
			case 'csv':
				return $this->format_student_results_csv( $flashcard_set, $results, $stats, $user, $timestamp );
			case 'pdf':
				// For now, return HTML that can be converted to PDF
				return $this->format_student_results_html( $flashcard_set, $results, $stats, $user, $timestamp, $include_history, $include_recommendations );
			default:
				return array( 'content' => '', 'filename' => '', 'mime_type' => 'text/plain' );
		}
	}

	/**
	 * Format student results as CSV
	 *
	 * @since    1.0.0
	 * @param    array    $flashcard_set   Flashcard set data
	 * @param    array    $results         Study results
	 * @param    array    $stats           Performance statistics
	 * @param    WP_User  $user            Current user
	 * @param    string   $timestamp       Export timestamp
	 * @return   array                     Array with content, filename, and mime_type
	 */
	private function format_student_results_csv( $flashcard_set, $results, $stats, $user, $timestamp ) {
		$csv = '';
		
		// Header
		$csv .= "\"SkyLearn Flashcards - Student Results\"\n";
		$csv .= "\"Generated: " . current_time( 'Y-m-d H:i:s' ) . "\"\n";
		$csv .= "\"Student: " . $user->display_name . "\"\n";
		$csv .= "\"Email: " . $user->user_email . "\"\n";
		$csv .= "\"Flashcard Set: " . $flashcard_set['title'] . "\"\n\n";
		
		// Performance Summary
		$csv .= "\"PERFORMANCE SUMMARY\"\n";
		$csv .= "\"Metric\",\"Value\"\n";
		$csv .= "\"Completion Rate\"," . ( $results['completionRate'] ?? 0 ) . "%\n";
		$csv .= "\"Time Spent\"," . gmdate( 'H:i:s', $results['timeSpent'] ?? 0 ) . "\n";
		$csv .= "\"Accuracy Score\"," . ( $results['accuracy'] ?? 0 ) . "/5.0\n";
		$csv .= "\"Total Cards\"," . ( $results['totalCards'] ?? 0 ) . "\n";
		$csv .= "\"Completed Cards\"," . ( $results['completedCards'] ?? 0 ) . "\n\n";
		
		// Knowledge Breakdown
		$csv .= "\"KNOWLEDGE BREAKDOWN\"\n";
		$csv .= "\"Level\",\"Count\"\n";
		$csv .= "\"Excellent\"," . ( $stats['excellent_cards'] ?? 0 ) . "\n";
		$csv .= "\"Good\"," . ( $stats['good_cards'] ?? 0 ) . "\n";
		$csv .= "\"Poor\"," . ( $stats['poor_cards'] ?? 0 ) . "\n";
		
		return array(
			'content'   => $csv,
			'filename'  => "skylearn-results-{$user->user_login}-{$timestamp}.csv",
			'mime_type' => 'text/csv'
		);
	}

	/**
	 * Format student results as HTML (for PDF conversion)
	 *
	 * @since    1.0.0
	 * @param    array    $flashcard_set           Flashcard set data
	 * @param    array    $results                 Study results
	 * @param    array    $stats                   Performance statistics
	 * @param    WP_User  $user                    Current user
	 * @param    string   $timestamp               Export timestamp
	 * @param    bool     $include_history         Include progress history
	 * @param    bool     $include_recommendations Include study recommendations
	 * @return   array                             Array with content, filename, and mime_type
	 */
	private function format_student_results_html( $flashcard_set, $results, $stats, $user, $timestamp, $include_history, $include_recommendations ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>SkyLearn Flashcards - Study Results</title>
			<style>
				body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
				.header { background: linear-gradient(135deg, #3498db, #f39c12); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
				.header h1 { margin: 0 0 10px 0; }
				.summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
				.stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #3498db; }
				.stat-value { font-size: 2em; font-weight: bold; color: #3498db; }
				.stat-label { font-size: 0.9em; color: #666; margin-top: 5px; }
				.section { margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 8px; }
				.section h2 { margin-top: 0; color: #3498db; }
				.knowledge-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; text-align: center; }
				.knowledge-item { padding: 10px; border-radius: 8px; }
				.excellent { background: #d5f4e6; color: #27ae60; }
				.good { background: #d6eaf8; color: #3498db; }
				.poor { background: #fadbd8; color: #e74c3c; }
				@media print { body { margin: 0; } .header { background: #3498db !important; } }
			</style>
		</head>
		<body>
			<div class="header">
				<h1>SkyLearn Flashcards - Study Results</h1>
				<p><strong>Student:</strong> <?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_email ); ?>)</p>
				<p><strong>Flashcard Set:</strong> <?php echo esc_html( $flashcard_set['title'] ); ?></p>
				<p><strong>Date:</strong> <?php echo esc_html( current_time( 'F j, Y g:i A' ) ); ?></p>
			</div>

			<div class="section">
				<h2>Performance Summary</h2>
				<div class="summary">
					<div class="stat-card">
						<div class="stat-value"><?php echo esc_html( $results['completionRate'] ?? 0 ); ?>%</div>
						<div class="stat-label">Completion Rate</div>
					</div>
					<div class="stat-card">
						<div class="stat-value"><?php echo esc_html( gmdate( 'i:s', $results['timeSpent'] ?? 0 ) ); ?></div>
						<div class="stat-label">Time Spent</div>
					</div>
					<div class="stat-card">
						<div class="stat-value"><?php echo esc_html( round( $results['accuracy'] ?? 0, 1 ) ); ?>/5.0</div>
						<div class="stat-label">Knowledge Score</div>
					</div>
					<div class="stat-card">
						<div class="stat-value"><?php echo esc_html( $results['completedCards'] ?? 0 ); ?>/<?php echo esc_html( $results['totalCards'] ?? 0 ); ?></div>
						<div class="stat-label">Cards Completed</div>
					</div>
				</div>
			</div>

			<div class="section">
				<h2>Knowledge Breakdown</h2>
				<div class="knowledge-grid">
					<div class="knowledge-item excellent">
						<div class="stat-value"><?php echo esc_html( $stats['excellent_cards'] ?? 0 ); ?></div>
						<div class="stat-label">Excellent</div>
					</div>
					<div class="knowledge-item good">
						<div class="stat-value"><?php echo esc_html( $stats['good_cards'] ?? 0 ); ?></div>
						<div class="stat-label">Good</div>
					</div>
					<div class="knowledge-item poor">
						<div class="stat-value"><?php echo esc_html( $stats['poor_cards'] ?? 0 ); ?></div>
						<div class="stat-label">Needs Work</div>
					</div>
				</div>
			</div>

			<?php if ( $include_recommendations ) : ?>
			<div class="section">
				<h2>Study Recommendations</h2>
				<ul>
					<?php if ( ( $results['completionRate'] ?? 0 ) < 100 ) : ?>
						<li><strong>Continue Studying:</strong> You have more cards to review. Keep going to complete the set!</li>
					<?php endif; ?>
					
					<?php if ( ( $stats['poor_cards'] ?? 0 ) > 0 ) : ?>
						<li><strong>Review Difficult Cards:</strong> Focus on the <?php echo esc_html( $stats['poor_cards'] ); ?> cards you found challenging.</li>
					<?php endif; ?>
					
					<?php if ( ( $results['accuracy'] ?? 0 ) >= 4.0 && ( $results['completionRate'] ?? 0 ) >= 100 ) : ?>
						<li><strong>Excellent Work!</strong> You've mastered this set. Consider reviewing it periodically to maintain your knowledge.</li>
					<?php endif; ?>
					
					<li><strong>Schedule Regular Review:</strong> Spaced repetition helps improve long-term retention. Review this set again in a few days.</li>
				</ul>
			</div>
			<?php endif; ?>

			<div class="section">
				<p><em>Generated by SkyLearn Flashcards on <?php echo esc_html( current_time( 'F j, Y \a\t g:i A' ) ); ?></em></p>
			</div>
		</body>
		</html>
		<?php
		$html = ob_get_clean();
		
		return array(
			'content'   => $html,
			'filename'  => "skylearn-results-{$user->user_login}-{$timestamp}.html",
			'mime_type' => 'text/html'
		);
	}

	/**
	 * Get LMS integration data for reporting
	 *
	 * @since    1.0.0
	 * @param    array   $args   Query arguments
	 * @return   array           LMS integration data
	 */
	public function get_lms_integration_data( $args = array() ) {
		
		// Check if LMS integration is available
		if ( ! class_exists( 'SkyLearn_Flashcards_LMS_Manager' ) ) {
			return array();
		}
		
		$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
		$lms_status = skylearn_get_lms_status();
		
		if ( ! $lms_status['enabled'] || empty( $lms_status['detected_lms'] ) ) {
			return array();
		}
		
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
		
		$defaults = array(
			'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			'date_to'   => date( 'Y-m-d' ),
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		// Get flashcard completion data with LMS course correlation
		$lms_completion_data = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				a.set_id,
				COUNT(CASE WHEN a.action = 'complete' THEN 1 END) as completions,
				AVG(CASE WHEN a.action = 'complete' THEN a.accuracy END) as avg_accuracy,
				pm.meta_value as lms_course_id
			FROM {$analytics_table} a
			LEFT JOIN {$wpdb->postmeta} pm ON a.set_id = pm.post_id AND pm.meta_key = '_lms_course_id'
			WHERE a.created_at BETWEEN %s AND %s
			GROUP BY a.set_id, pm.meta_value
			HAVING pm.meta_value IS NOT NULL
			ORDER BY completions DESC",
			$args['date_from'] . ' 00:00:00',
			$args['date_to'] . ' 23:59:59'
		), ARRAY_A );
		
		// Get LMS course titles
		foreach ( $lms_completion_data as &$data ) {
			$course_title = get_the_title( $data['lms_course_id'] );
			$data['course_title'] = $course_title ?: __( 'Unknown Course', 'skylearn-flashcards' );
			$data['set_title'] = get_the_title( $data['set_id'] ) ?: __( 'Unknown Set', 'skylearn-flashcards' );
		}
		
		return array(
			'lms_platform'     => $lms_status['detected_lms'],
			'course_performance' => $lms_completion_data,
			'integration_active' => true
		);
	}

	/**
	 * Export LMS integration report
	 *
	 * @since    1.0.0
	 * @param    array   $args    Query arguments
	 * @param    string  $format  Export format
	 * @return   array            Export data
	 */
	public function export_lms_integration_report( $args = array(), $format = 'csv' ) {
		
		$lms_data = $this->get_lms_integration_data( $args );
		
		if ( empty( $lms_data['course_performance'] ) ) {
			return array(
				'content'   => '',
				'filename'  => '',
				'mime_type' => 'text/plain'
			);
		}
		
		if ( $format === 'csv' ) {
			$csv = '';
			$csv .= "\"SkyLearn Flashcards - LMS Integration Report\"\n";
			$csv .= "\"Generated: " . current_time( 'Y-m-d H:i:s' ) . "\"\n";
			$csv .= "\"LMS Platform: " . implode( ', ', $lms_data['lms_platform'] ) . "\"\n\n";
			
			$csv .= "\"COURSE PERFORMANCE\"\n";
			$csv .= "\"Course Title\",\"Flashcard Set\",\"Completions\",\"Average Accuracy (%)\"\n";
			
			foreach ( $lms_data['course_performance'] as $course ) {
				$csv .= sprintf(
					'"%s","%s",%d,%.2f' . "\n",
					str_replace( '"', '""', $course['course_title'] ),
					str_replace( '"', '""', $course['set_title'] ),
					$course['completions'],
					$course['avg_accuracy'] ?: 0
				);
			}
			
			return array(
				'content'   => $csv,
				'filename'  => 'skylearn-lms-integration-' . date( 'Y-m-d-H-i-s' ) . '.csv',
				'mime_type' => 'text/csv'
			);
		}
		
		return array(
			'content'   => wp_json_encode( $lms_data, JSON_PRETTY_PRINT ),
			'filename'  => 'skylearn-lms-integration-' . date( 'Y-m-d-H-i-s' ) . '.json',
			'mime_type' => 'application/json'
		);
	}

}