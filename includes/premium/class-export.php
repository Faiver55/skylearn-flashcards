<?php
/**
 * Export functionality for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 */

/**
 * Export functionality class (Premium Feature).
 *
 * Handles bulk export of flashcard sets, analytics data, and user progress.
 * Supports multiple formats including CSV, JSON, and SCORM packages.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Export {

	/**
	 * Initialize export functionality
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
		add_action( 'wp_ajax_skylearn_bulk_export', array( $this, 'ajax_bulk_export' ) );
		add_action( 'wp_ajax_skylearn_export_progress', array( $this, 'ajax_export_progress' ) );
		
	}

	/**
	 * Add export functionality to admin menu
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		
		add_submenu_page(
			'skylearn-flashcards',
			__( 'Export & Backup', 'skylearn-flashcards' ),
			__( 'Export', 'skylearn-flashcards' ),
			'export_skylearn_flashcards',
			'skylearn-export',
			array( $this, 'display_export_page' )
		);
		
	}

	/**
	 * Display the export page
	 *
	 * @since    1.0.0
	 */
	public function display_export_page() {
		include SKYLEARN_FLASHCARDS_PATH . 'includes/premium/views/export-page.php';
	}

	/**
	 * Export flashcard sets in bulk
	 *
	 * @since    1.0.0
	 * @param    array   $set_ids   Array of set IDs to export
	 * @param    string  $format    Export format (csv, json, scorm)
	 * @return   string|false       Export data or false on failure
	 */
	public function export_flashcard_sets( $set_ids, $format = 'csv' ) {
		
		if ( empty( $set_ids ) || ! is_array( $set_ids ) ) {
			return false;
		}
		
		$sets_data = array();
		
		foreach ( $set_ids as $set_id ) {
			$set_data = skylearn_get_flashcard_set( $set_id );
			if ( $set_data ) {
				$sets_data[] = $set_data;
			}
		}
		
		if ( empty( $sets_data ) ) {
			return false;
		}
		
		switch ( $format ) {
			case 'csv':
				return $this->format_sets_as_csv( $sets_data );
			case 'json':
				return $this->format_sets_as_json( $sets_data );
			case 'scorm':
				return $this->format_sets_as_scorm( $sets_data );
			default:
				return false;
		}
	}

	/**
	 * Export user progress data
	 *
	 * @since    1.0.0
	 * @param    array   $user_ids   Array of user IDs (optional, exports all if empty)
	 * @param    string  $format     Export format
	 * @return   string|false        Export data or false on failure
	 */
	public function export_user_progress( $user_ids = array(), $format = 'csv' ) {
		global $wpdb;
		
		$progress_table = $wpdb->prefix . 'skylearn_flashcard_progress';
		$analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
		
		// Build query conditions
		$where_clauses = array();
		$where_values = array();
		
		if ( ! empty( $user_ids ) && is_array( $user_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
			$where_clauses[] = "p.user_id IN ({$placeholders})";
			$where_values = array_merge( $where_values, array_map( 'absint', $user_ids ) );
		}
		
		$where_sql = ! empty( $where_clauses ) ? 'WHERE ' . implode( ' AND ', $where_clauses ) : '';
		
		// Get progress data with user information
		$query = "SELECT 
			p.user_id,
			u.display_name,
			u.user_email,
			p.set_id,
			p.card_index,
			p.status,
			p.attempts,
			p.correct_attempts,
			p.mastery_level,
			p.last_attempt,
			ps.post_title as set_title
		FROM {$progress_table} p
		LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
		LEFT JOIN {$wpdb->posts} ps ON p.set_id = ps.ID
		{$where_sql}
		ORDER BY p.user_id, p.set_id, p.card_index";
		
		if ( ! empty( $where_values ) ) {
			$query = $wpdb->prepare( $query, $where_values );
		}
		
		$progress_data = $wpdb->get_results( $query, ARRAY_A );
		
		if ( empty( $progress_data ) ) {
			return false;
		}
		
		switch ( $format ) {
			case 'csv':
				return $this->format_progress_as_csv( $progress_data );
			case 'json':
				return $this->format_progress_as_json( $progress_data );
			default:
				return false;
		}
	}

	/**
	 * Export analytics data
	 *
	 * @since    1.0.0
	 * @param    array   $args     Export arguments
	 * @return   string|false      Export data or false on failure
	 */
	public function export_analytics_data( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			'date_to'   => date( 'Y-m-d' ),
			'format'    => 'csv',
			'set_ids'   => array(),
			'user_ids'  => array(),
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
		
		// Build query conditions
		$where_clauses = array( 'created_at BETWEEN %s AND %s' );
		$where_values = array( $args['date_from'] . ' 00:00:00', $args['date_to'] . ' 23:59:59' );
		
		if ( ! empty( $args['set_ids'] ) && is_array( $args['set_ids'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['set_ids'] ), '%d' ) );
			$where_clauses[] = "set_id IN ({$placeholders})";
			$where_values = array_merge( $where_values, array_map( 'absint', $args['set_ids'] ) );
		}
		
		if ( ! empty( $args['user_ids'] ) && is_array( $args['user_ids'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['user_ids'] ), '%d' ) );
			$where_clauses[] = "user_id IN ({$placeholders})";
			$where_values = array_merge( $where_values, array_map( 'absint', $args['user_ids'] ) );
		}
		
		$where_sql = implode( ' AND ', $where_clauses );
		
		// Get analytics data
		$query = "SELECT 
			a.*,
			u.display_name,
			u.user_email,
			ps.post_title as set_title
		FROM {$analytics_table} a
		LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
		LEFT JOIN {$wpdb->posts} ps ON a.set_id = ps.ID
		WHERE {$where_sql}
		ORDER BY a.created_at DESC";
		
		$analytics_data = $wpdb->get_results( $wpdb->prepare( $query, $where_values ), ARRAY_A );
		
		if ( empty( $analytics_data ) ) {
			return false;
		}
		
		switch ( $args['format'] ) {
			case 'csv':
				return $this->format_analytics_as_csv( $analytics_data );
			case 'json':
				return $this->format_analytics_as_json( $analytics_data );
			default:
				return false;
		}
	}

	/**
	 * Format flashcard sets as CSV
	 *
	 * @since    1.0.0
	 * @param    array   $sets_data   Sets data
	 * @return   string               CSV data
	 */
	private function format_sets_as_csv( $sets_data ) {
		
		$csv = "Set ID,Set Title,Card Front,Card Back,Card Index\n";
		
		foreach ( $sets_data as $set ) {
			foreach ( $set['cards'] as $index => $card ) {
				$csv .= sprintf(
					"%d,\"%s\",\"%s\",\"%s\",%d\n",
					$set['id'],
					str_replace( '"', '""', $set['title'] ),
					str_replace( '"', '""', $card['front'] ),
					str_replace( '"', '""', $card['back'] ),
					$index
				);
			}
		}
		
		return $csv;
	}

	/**
	 * Format flashcard sets as JSON
	 *
	 * @since    1.0.0
	 * @param    array   $sets_data   Sets data
	 * @return   string               JSON data
	 */
	private function format_sets_as_json( $sets_data ) {
		return json_encode( $sets_data, JSON_PRETTY_PRINT );
	}

	/**
	 * Format flashcard sets as SCORM package
	 *
	 * @since    1.0.0
	 * @param    array   $sets_data   Sets data
	 * @return   string               SCORM package data
	 */
	private function format_sets_as_scorm( $sets_data ) {
		// This would create a SCORM-compliant package
		// For now, return a simplified structure
		
		$scorm_data = array(
			'meta' => array(
				'title' => 'SkyLearn Flashcards Export',
				'description' => 'Exported flashcard sets from SkyLearn',
				'version' => '1.0.0',
				'export_date' => current_time( 'c' ),
			),
			'content' => $sets_data,
		);
		
		return json_encode( $scorm_data, JSON_PRETTY_PRINT );
	}

	/**
	 * Format progress data as CSV
	 *
	 * @since    1.0.0
	 * @param    array   $progress_data   Progress data
	 * @return   string                   CSV data
	 */
	private function format_progress_as_csv( $progress_data ) {
		
		$csv = "User ID,User Name,User Email,Set ID,Set Title,Card Index,Status,Attempts,Correct Attempts,Mastery Level,Last Attempt\n";
		
		foreach ( $progress_data as $record ) {
			$csv .= sprintf(
				"%d,\"%s\",\"%s\",%d,\"%s\",%d,\"%s\",%d,%d,%.2f,\"%s\"\n",
				$record['user_id'],
				str_replace( '"', '""', $record['display_name'] ),
				str_replace( '"', '""', $record['user_email'] ),
				$record['set_id'],
				str_replace( '"', '""', $record['set_title'] ),
				$record['card_index'],
				$record['status'],
				$record['attempts'],
				$record['correct_attempts'],
				$record['mastery_level'],
				$record['last_attempt']
			);
		}
		
		return $csv;
	}

	/**
	 * Format progress data as JSON
	 *
	 * @since    1.0.0
	 * @param    array   $progress_data   Progress data
	 * @return   string                   JSON data
	 */
	private function format_progress_as_json( $progress_data ) {
		return json_encode( $progress_data, JSON_PRETTY_PRINT );
	}

	/**
	 * Format analytics data as CSV
	 *
	 * @since    1.0.0
	 * @param    array   $analytics_data   Analytics data
	 * @return   string                    CSV data
	 */
	private function format_analytics_as_csv( $analytics_data ) {
		
		$csv = "ID,Set ID,Set Title,User ID,User Name,User Email,Card Index,Action,Time Spent,Accuracy,Session ID,IP Address,Created At\n";
		
		foreach ( $analytics_data as $record ) {
			$csv .= sprintf(
				"%d,%d,\"%s\",%d,\"%s\",\"%s\",%d,\"%s\",%d,%.2f,\"%s\",\"%s\",\"%s\"\n",
				$record['id'],
				$record['set_id'],
				str_replace( '"', '""', $record['set_title'] ),
				$record['user_id'],
				str_replace( '"', '""', $record['display_name'] ),
				str_replace( '"', '""', $record['user_email'] ),
				$record['card_index'],
				$record['action'],
				$record['time_spent'],
				$record['accuracy'],
				$record['session_id'],
				$record['ip_address'],
				$record['created_at']
			);
		}
		
		return $csv;
	}

	/**
	 * Format analytics data as JSON
	 *
	 * @since    1.0.0
	 * @param    array   $analytics_data   Analytics data
	 * @return   string                    JSON data
	 */
	private function format_analytics_as_json( $analytics_data ) {
		return json_encode( $analytics_data, JSON_PRETTY_PRINT );
	}

	/**
	 * AJAX handler for bulk export
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_export() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_bulk_export' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check permissions
		if ( ! current_user_can( 'export_skylearn_flashcards' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}
		
		$export_type = sanitize_text_field( $_POST['export_type'] ?? '' );
		$format = sanitize_text_field( $_POST['format'] ?? 'csv' );
		$items = $_POST['items'] ?? array();
		
		$export_data = '';
		$filename = '';
		
		switch ( $export_type ) {
			case 'flashcards':
				$set_ids = array_map( 'absint', $items );
				$export_data = $this->export_flashcard_sets( $set_ids, $format );
				$filename = 'skylearn-flashcards-' . date( 'Y-m-d' ) . '.' . $format;
				break;
				
			case 'progress':
				$user_ids = array_map( 'absint', $items );
				$export_data = $this->export_user_progress( $user_ids, $format );
				$filename = 'skylearn-progress-' . date( 'Y-m-d' ) . '.' . $format;
				break;
				
			case 'analytics':
				$args = array(
					'format'    => $format,
					'date_from' => sanitize_text_field( $_POST['date_from'] ?? '' ),
					'date_to'   => sanitize_text_field( $_POST['date_to'] ?? '' ),
					'set_ids'   => array_map( 'absint', $_POST['set_ids'] ?? array() ),
					'user_ids'  => array_map( 'absint', $_POST['user_ids'] ?? array() ),
				);
				$export_data = $this->export_analytics_data( $args );
				$filename = 'skylearn-analytics-' . date( 'Y-m-d' ) . '.' . $format;
				break;
				
			default:
				wp_send_json_error( array( 'message' => __( 'Invalid export type.', 'skylearn-flashcards' ) ) );
		}
		
		if ( $export_data ) {
			wp_send_json_success( array(
				'data'     => $export_data,
				'filename' => $filename,
				'format'   => $format,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Export failed or no data available.', 'skylearn-flashcards' ) ) );
		}
	}

	/**
	 * AJAX handler for export progress tracking
	 *
	 * @since    1.0.0
	 */
	public function ajax_export_progress() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_export_progress' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check permissions
		if ( ! current_user_can( 'export_skylearn_flashcards' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}
		
		$job_id = sanitize_text_field( $_POST['job_id'] ?? '' );
		
		// In a real implementation, this would check the status of a background export job
		// For now, we'll simulate progress
		wp_send_json_success( array(
			'progress' => 100,
			'status'   => 'completed',
			'message'  => __( 'Export completed successfully.', 'skylearn-flashcards' ),
		) );
	}

}