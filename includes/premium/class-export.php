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
		
		// AJAX handlers for export
		add_action( 'wp_ajax_skylearn_bulk_export', array( $this, 'ajax_bulk_export' ) );
		add_action( 'wp_ajax_skylearn_export_progress', array( $this, 'ajax_export_progress' ) );
		
		// AJAX handlers for import
		add_action( 'wp_ajax_skylearn_bulk_import', array( $this, 'ajax_bulk_import' ) );
		add_action( 'wp_ajax_skylearn_validate_import', array( $this, 'ajax_validate_import' ) );
		add_action( 'wp_ajax_skylearn_import_progress', array( $this, 'ajax_import_progress' ) );
		
		// Add export styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_export_assets' ) );
		
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
			'edit_posts',
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
		if ( ! is_user_logged_in() && skylearn_is_premium() ) {
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
				if ( $format === 'xlsx' ) {
					$export_data = $this->export_flashcard_sets_excel( $set_ids );
					$filename = 'skylearn-flashcards-' . date( 'Y-m-d' ) . '.xlsx';
				} else {
					$export_data = $this->export_flashcard_sets( $set_ids, $format );
					$filename = 'skylearn-flashcards-' . date( 'Y-m-d' ) . '.' . $format;
				}
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
		if ( ! is_user_logged_in() && skylearn_is_premium() ) {
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

	/**
	 * Enqueue export-specific assets
	 *
	 * @since    1.0.0
	 */
	public function enqueue_export_assets( $hook ) {
		
		// Only load on export page
		if ( strpos( $hook, 'skylearn-export' ) === false ) {
			return;
		}
		
		wp_enqueue_style( 
			'skylearn-export-css', 
			SKYLEARN_FLASHCARDS_ASSETS . 'css/export.css', 
			array(), 
			SKYLEARN_FLASHCARDS_VERSION 
		);
		
		wp_enqueue_script( 
			'skylearn-export-js', 
			SKYLEARN_FLASHCARDS_ASSETS . 'js/export.js', 
			array( 'jquery' ), 
			SKYLEARN_FLASHCARDS_VERSION, 
			true 
		);
		
		// Localize script for AJAX
		wp_localize_script( 'skylearn-export-js', 'skylearn_export', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'skylearn_bulk_export' ),
			'strings' => array(
				'export_error'      => __( 'Export failed. Please try again.', 'skylearn-flashcards' ),
				'import_error'      => __( 'Import failed. Please try again.', 'skylearn-flashcards' ),
				'invalid_file'      => __( 'Please select a valid CSV, JSON, or Excel file.', 'skylearn-flashcards' ),
				'invalid_file_type' => __( 'Please select a CSV, JSON, or Excel file.', 'skylearn-flashcards' ),
				'parse_error'       => __( 'Error parsing file: ', 'skylearn-flashcards' ),
				'no_sets_selected'  => __( 'Please select at least one flashcard set to export.', 'skylearn-flashcards' ),
				'confirm_import'    => __( 'This will import the selected data. Continue?', 'skylearn-flashcards' ),
			),
		) );
	}

	// ==========================================================================
	// IMPORT FUNCTIONALITY
	// ==========================================================================

	/**
	 * Import flashcard sets from file data
	 *
	 * @since    1.0.0
	 * @param    array   $data     Import data
	 * @param    string  $format   File format (csv, json, xlsx)
	 * @param    array   $options  Import options
	 * @return   array|WP_Error    Import results or error
	 */
	public function import_flashcard_sets( $data, $format = 'csv', $options = array() ) {
		
		$defaults = array(
			'update_existing' => false,
			'skip_duplicates' => true,
			'validate_data'   => true,
			'create_author'   => get_current_user_id(),
		);
		
		$options = wp_parse_args( $options, $defaults );
		
		// Validate import data
		if ( $options['validate_data'] ) {
			$validation = $this->validate_import_data( $data, $format );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
		}
		
		$results = array(
			'imported'   => 0,
			'updated'    => 0,
			'skipped'    => 0,
			'errors'     => array(),
			'set_ids'    => array(),
		);
		
		// Process import based on format
		switch ( $format ) {
			case 'csv':
				$results = $this->import_from_csv( $data, $options, $results );
				break;
			case 'json':
				$results = $this->import_from_json( $data, $options, $results );
				break;
			case 'xlsx':
				$results = $this->import_from_excel( $data, $options, $results );
				break;
			default:
				return new WP_Error( 'invalid_format', __( 'Unsupported import format.', 'skylearn-flashcards' ) );
		}
		
		return $results;
	}

	/**
	 * Import from CSV data
	 *
	 * @since    1.0.0
	 * @param    array   $data     CSV data
	 * @param    array   $options  Import options
	 * @param    array   $results  Results array
	 * @return   array             Updated results
	 */
	private function import_from_csv( $data, $options, $results ) {
		
		// Group cards by set
		$sets_data = array();
		
		foreach ( $data as $row ) {
			$set_id = absint( $row['Set ID'] ?? 0 );
			$set_title = sanitize_text_field( $row['Set Title'] ?? '' );
			$card_front = wp_kses_post( $row['Card Front'] ?? '' );
			$card_back = wp_kses_post( $row['Card Back'] ?? '' );
			$card_index = absint( $row['Card Index'] ?? 0 );
			
			if ( empty( $set_title ) || empty( $card_front ) || empty( $card_back ) ) {
				$results['errors'][] = sprintf( 
					__( 'Skipped row with missing data: %s', 'skylearn-flashcards' ), 
					json_encode( $row ) 
				);
				continue;
			}
			
			if ( ! isset( $sets_data[ $set_title ] ) ) {
				$sets_data[ $set_title ] = array(
					'title' => $set_title,
					'cards' => array(),
				);
			}
			
			$sets_data[ $set_title ]['cards'][ $card_index ] = array(
				'front' => $card_front,
				'back'  => $card_back,
			);
		}
		
		// Create/update flashcard sets
		foreach ( $sets_data as $set_data ) {
			$result = $this->create_or_update_set( $set_data, $options );
			
			if ( is_wp_error( $result ) ) {
				$results['errors'][] = $result->get_error_message();
			} else {
				$results['set_ids'][] = $result['set_id'];
				if ( $result['action'] === 'created' ) {
					$results['imported']++;
				} else {
					$results['updated']++;
				}
			}
		}
		
		return $results;
	}

	/**
	 * Import from JSON data
	 *
	 * @since    1.0.0
	 * @param    array   $data     JSON data
	 * @param    array   $options  Import options
	 * @param    array   $results  Results array
	 * @return   array             Updated results
	 */
	private function import_from_json( $data, $options, $results ) {
		
		// Handle different JSON structures
		if ( isset( $data['content'] ) && is_array( $data['content'] ) ) {
			// SCORM or structured export format
			$sets_data = $data['content'];
		} elseif ( is_array( $data ) && isset( $data[0]['title'] ) ) {
			// Direct array of sets
			$sets_data = $data;
		} else {
			$results['errors'][] = __( 'Invalid JSON structure for flashcard import.', 'skylearn-flashcards' );
			return $results;
		}
		
		foreach ( $sets_data as $set_data ) {
			$result = $this->create_or_update_set( $set_data, $options );
			
			if ( is_wp_error( $result ) ) {
				$results['errors'][] = $result->get_error_message();
			} else {
				$results['set_ids'][] = $result['set_id'];
				if ( $result['action'] === 'created' ) {
					$results['imported']++;
				} else {
					$results['updated']++;
				}
			}
		}
		
		return $results;
	}

	/**
	 * Import from Excel data
	 *
	 * @since    1.0.0
	 * @param    array   $data     Excel data
	 * @param    array   $options  Import options
	 * @param    array   $results  Results array
	 * @return   array             Updated results
	 */
	private function import_from_excel( $data, $options, $results ) {
		
		// For now, treat Excel data like CSV data
		// In a full implementation, this would use a library like PhpSpreadsheet
		return $this->import_from_csv( $data, $options, $results );
	}

	/**
	 * Create or update a flashcard set
	 *
	 * @since    1.0.0
	 * @param    array   $set_data  Set data
	 * @param    array   $options   Import options
	 * @return   array|WP_Error     Result or error
	 */
	private function create_or_update_set( $set_data, $options ) {
		
		$title = sanitize_text_field( $set_data['title'] ?? '' );
		$description = wp_kses_post( $set_data['description'] ?? '' );
		$cards = $set_data['cards'] ?? array();
		
		if ( empty( $title ) || empty( $cards ) ) {
			return new WP_Error( 'invalid_set_data', __( 'Set title and cards are required.', 'skylearn-flashcards' ) );
		}
		
		// Check if set exists
		$existing_set = get_page_by_title( $title, OBJECT, 'flashcard_set' );
		$action = 'created';
		
		if ( $existing_set ) {
			if ( ! $options['update_existing'] ) {
				if ( $options['skip_duplicates'] ) {
					return new WP_Error( 'duplicate_skipped', sprintf( 
						__( 'Skipped duplicate set: %s', 'skylearn-flashcards' ), 
						$title 
					) );
				} else {
					return new WP_Error( 'duplicate_exists', sprintf( 
						__( 'Set already exists: %s', 'skylearn-flashcards' ), 
						$title 
					) );
				}
			}
			$set_id = $existing_set->ID;
			$action = 'updated';
		} else {
			// Create new set
			$set_id = wp_insert_post( array(
				'post_type'    => 'flashcard_set',
				'post_title'   => $title,
				'post_content' => $description,
				'post_status'  => 'publish',
				'post_author'  => $options['create_author'],
			) );
			
			if ( is_wp_error( $set_id ) ) {
				return $set_id;
			}
		}
		
		// Sanitize and save cards
		$sanitized_cards = skylearn_sanitize_flashcard_data( $cards );
		update_post_meta( $set_id, '_skylearn_flashcard_data', $sanitized_cards );
		
		// Save import metadata
		update_post_meta( $set_id, '_skylearn_imported_at', current_time( 'mysql' ) );
		update_post_meta( $set_id, '_skylearn_import_source', 'bulk_import' );
		
		return array(
			'set_id' => $set_id,
			'action' => $action,
			'title'  => $title,
		);
	}

	/**
	 * Validate import data
	 *
	 * @since    1.0.0
	 * @param    array   $data     Import data
	 * @param    string  $format   File format
	 * @return   true|WP_Error     True if valid, WP_Error if invalid
	 */
	private function validate_import_data( $data, $format ) {
		
		if ( empty( $data ) || ! is_array( $data ) ) {
			return new WP_Error( 'empty_data', __( 'Import data is empty or invalid.', 'skylearn-flashcards' ) );
		}
		
		switch ( $format ) {
			case 'csv':
				return $this->validate_csv_data( $data );
			case 'json':
				return $this->validate_json_data( $data );
			case 'xlsx':
				return $this->validate_excel_data( $data );
			default:
				return new WP_Error( 'invalid_format', __( 'Unsupported file format.', 'skylearn-flashcards' ) );
		}
	}

	/**
	 * Validate CSV import data
	 *
	 * @since    1.0.0
	 * @param    array   $data   CSV data
	 * @return   true|WP_Error   True if valid, WP_Error if invalid
	 */
	private function validate_csv_data( $data ) {
		
		$required_headers = array( 'Set Title', 'Card Front', 'Card Back' );
		
		if ( empty( $data[0] ) ) {
			return new WP_Error( 'no_headers', __( 'CSV file must have headers.', 'skylearn-flashcards' ) );
		}
		
		$headers = array_keys( $data[0] );
		$missing_headers = array_diff( $required_headers, $headers );
		
		if ( ! empty( $missing_headers ) ) {
			return new WP_Error( 'missing_headers', sprintf( 
				__( 'Missing required CSV headers: %s', 'skylearn-flashcards' ), 
				implode( ', ', $missing_headers ) 
			) );
		}
		
		return true;
	}

	/**
	 * Validate JSON import data
	 *
	 * @since    1.0.0
	 * @param    array   $data   JSON data
	 * @return   true|WP_Error   True if valid, WP_Error if invalid
	 */
	private function validate_json_data( $data ) {
		
		// Handle different JSON structures
		if ( isset( $data['content'] ) && is_array( $data['content'] ) ) {
			$sets_data = $data['content'];
		} elseif ( is_array( $data ) ) {
			$sets_data = $data;
		} else {
			return new WP_Error( 'invalid_json', __( 'Invalid JSON structure.', 'skylearn-flashcards' ) );
		}
		
		if ( empty( $sets_data ) ) {
			return new WP_Error( 'empty_json', __( 'No flashcard sets found in JSON data.', 'skylearn-flashcards' ) );
		}
		
		// Validate first set structure
		$first_set = reset( $sets_data );
		if ( ! isset( $first_set['title'] ) || ! isset( $first_set['cards'] ) ) {
			return new WP_Error( 'invalid_structure', __( 'JSON sets must have "title" and "cards" properties.', 'skylearn-flashcards' ) );
		}
		
		return true;
	}

	/**
	 * Validate Excel import data
	 *
	 * @since    1.0.0
	 * @param    array   $data   Excel data
	 * @return   true|WP_Error   True if valid, WP_Error if invalid
	 */
	private function validate_excel_data( $data ) {
		
		// For now, use same validation as CSV
		return $this->validate_csv_data( $data );
	}

	/**
	 * AJAX handler for bulk import
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_import() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_bulk_import' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check permissions
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}
		
		// Check if file was uploaded
		if ( empty( $_FILES['import_file'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'skylearn-flashcards' ) ) );
		}
		
		$file = $_FILES['import_file'];
		
		// Validate file
		$allowed_types = array( 'text/csv', 'application/json', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		if ( ! in_array( $file['type'], $allowed_types ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file type. Please upload CSV, JSON, or Excel files only.', 'skylearn-flashcards' ) ) );
		}
		
		// Parse file content
		$file_content = file_get_contents( $file['tmp_name'] );
		$format = $this->detect_file_format( $file['type'] );
		
		$import_data = $this->parse_import_file( $file_content, $format );
		if ( is_wp_error( $import_data ) ) {
			wp_send_json_error( array( 'message' => $import_data->get_error_message() ) );
		}
		
		// Import options
		$options = array(
			'update_existing' => ! empty( $_POST['update_existing'] ),
			'skip_duplicates' => ! empty( $_POST['skip_duplicates'] ),
			'validate_data'   => true,
			'create_author'   => get_current_user_id(),
		);
		
		// Perform import
		$results = $this->import_flashcard_sets( $import_data, $format, $options );
		
		if ( is_wp_error( $results ) ) {
			wp_send_json_error( array( 'message' => $results->get_error_message() ) );
		}
		
		wp_send_json_success( array(
			'message' => sprintf( 
				__( 'Import completed: %d sets imported, %d updated, %d skipped.', 'skylearn-flashcards' ),
				$results['imported'],
				$results['updated'],
				$results['skipped']
			),
			'results' => $results,
		) );
	}

	/**
	 * AJAX handler for import validation
	 *
	 * @since    1.0.0
	 */
	public function ajax_validate_import() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_validate_import' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check permissions
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}
		
		$file_data = $_POST['file_data'] ?? '';
		$format = sanitize_text_field( $_POST['format'] ?? 'csv' );
		
		if ( empty( $file_data ) ) {
			wp_send_json_error( array( 'message' => __( 'No data provided for validation.', 'skylearn-flashcards' ) ) );
		}
		
		// Parse and validate
		$import_data = $this->parse_import_file( $file_data, $format );
		
		if ( is_wp_error( $import_data ) ) {
			wp_send_json_error( array( 'message' => $import_data->get_error_message() ) );
		}
		
		$validation = $this->validate_import_data( $import_data, $format );
		
		if ( is_wp_error( $validation ) ) {
			wp_send_json_error( array( 'message' => $validation->get_error_message() ) );
		}
		
		// Generate preview stats
		$stats = $this->generate_import_stats( $import_data, $format );
		
		wp_send_json_success( array(
			'message' => __( 'File validation successful.', 'skylearn-flashcards' ),
			'stats'   => $stats,
		) );
	}

	/**
	 * AJAX handler for import progress tracking
	 *
	 * @since    1.0.0
	 */
	public function ajax_import_progress() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_import_progress' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		// Check permissions
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}
		
		$job_id = sanitize_text_field( $_POST['job_id'] ?? '' );
		
		// In a real implementation, this would check the status of a background import job
		wp_send_json_success( array(
			'progress' => 100,
			'status'   => 'completed',
			'message'  => __( 'Import completed successfully.', 'skylearn-flashcards' ),
		) );
	}

	/**
	 * Detect file format from MIME type
	 *
	 * @since    1.0.0
	 * @param    string   $mime_type   MIME type
	 * @return   string                File format
	 */
	private function detect_file_format( $mime_type ) {
		
		$format_map = array(
			'text/csv'                                                          => 'csv',
			'application/json'                                                  => 'json',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
		);
		
		return $format_map[ $mime_type ] ?? 'csv';
	}

	/**
	 * Parse import file content
	 *
	 * @since    1.0.0
	 * @param    string   $content   File content
	 * @param    string   $format    File format
	 * @return   array|WP_Error     Parsed data or error
	 */
	private function parse_import_file( $content, $format ) {
		
		switch ( $format ) {
			case 'csv':
				return $this->parse_csv_content( $content );
			case 'json':
				return $this->parse_json_content( $content );
			case 'xlsx':
				return $this->parse_excel_content( $content );
			default:
				return new WP_Error( 'unsupported_format', __( 'Unsupported file format.', 'skylearn-flashcards' ) );
		}
	}

	/**
	 * Parse CSV content
	 *
	 * @since    1.0.0
	 * @param    string   $content   CSV content
	 * @return   array|WP_Error     Parsed data or error
	 */
	private function parse_csv_content( $content ) {
		
		$lines = explode( "\n", $content );
		if ( empty( $lines ) ) {
			return new WP_Error( 'empty_csv', __( 'CSV file is empty.', 'skylearn-flashcards' ) );
		}
		
		// Parse headers
		$headers = str_getcsv( trim( $lines[0] ) );
		if ( empty( $headers ) ) {
			return new WP_Error( 'no_headers', __( 'CSV file has no headers.', 'skylearn-flashcards' ) );
		}
		
		$data = array();
		
		// Parse data rows
		for ( $i = 1; $i < count( $lines ); $i++ ) {
			$line = trim( $lines[ $i ] );
			if ( empty( $line ) ) {
				continue;
			}
			
			$values = str_getcsv( $line );
			$row = array();
			
			foreach ( $headers as $index => $header ) {
				$row[ $header ] = $values[ $index ] ?? '';
			}
			
			$data[] = $row;
		}
		
		return $data;
	}

	/**
	 * Parse JSON content
	 *
	 * @since    1.0.0
	 * @param    string   $content   JSON content
	 * @return   array|WP_Error     Parsed data or error
	 */
	private function parse_json_content( $content ) {
		
		$data = json_decode( $content, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'invalid_json', sprintf( 
				__( 'Invalid JSON: %s', 'skylearn-flashcards' ), 
				json_last_error_msg() 
			) );
		}
		
		return $data;
	}

	/**
	 * Parse Excel content
	 *
	 * @since    1.0.0
	 * @param    string   $content   Excel content
	 * @return   array|WP_Error     Parsed data or error
	 */
	private function parse_excel_content( $content ) {
		
		// This is a placeholder - in a real implementation, you would use
		// a library like PhpSpreadsheet to parse Excel files
		return new WP_Error( 'excel_not_supported', __( 'Excel import is not yet fully implemented. Please use CSV or JSON format.', 'skylearn-flashcards' ) );
	}

	/**
	 * Generate import statistics
	 *
	 * @since    1.0.0
	 * @param    array   $data     Import data
	 * @param    string  $format   File format
	 * @return   array             Import statistics
	 */
	private function generate_import_stats( $data, $format ) {
		
		$stats = array(
			'total_rows'    => count( $data ),
			'estimated_sets' => 0,
			'estimated_cards' => 0,
			'format'        => strtoupper( $format ),
		);
		
		if ( $format === 'csv' ) {
			// Count unique sets and total cards
			$unique_sets = array();
			foreach ( $data as $row ) {
				$set_title = $row['Set Title'] ?? '';
				if ( ! empty( $set_title ) ) {
					$unique_sets[ $set_title ] = true;
					$stats['estimated_cards']++;
				}
			}
			$stats['estimated_sets'] = count( $unique_sets );
		} elseif ( $format === 'json' ) {
			// Handle different JSON structures
			if ( isset( $data['content'] ) && is_array( $data['content'] ) ) {
				$sets_data = $data['content'];
			} elseif ( is_array( $data ) ) {
				$sets_data = $data;
			} else {
				$sets_data = array();
			}
			
			$stats['estimated_sets'] = count( $sets_data );
			foreach ( $sets_data as $set ) {
				$stats['estimated_cards'] += count( $set['cards'] ?? array() );
			}
		}
		
		return $stats;
	}

	// ==========================================================================
	// EXCEL EXPORT SUPPORT
	// ==========================================================================

	/**
	 * Export flashcard sets as Excel format
	 *
	 * @since    1.0.0
	 * @param    array   $set_ids   Array of set IDs to export
	 * @return   string|false       Excel data or false on failure
	 */
	public function export_flashcard_sets_excel( $set_ids ) {
		
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
		
		return $this->format_sets_as_excel( $sets_data );
	}

	/**
	 * Format flashcard sets as Excel
	 *
	 * @since    1.0.0
	 * @param    array   $sets_data   Sets data
	 * @return   string               Excel data (simplified CSV format for now)
	 */
	private function format_sets_as_excel( $sets_data ) {
		
		// For now, return CSV format with BOM for Excel compatibility
		// In a full implementation, this would generate actual Excel files
		$csv = "\xEF\xBB\xBF"; // UTF-8 BOM
		$csv .= "Set ID,Set Title,Card Front,Card Back,Card Index,Created Date,Author\n";
		
		foreach ( $sets_data as $set ) {
			foreach ( $set['cards'] as $index => $card ) {
				$csv .= sprintf(
					"%d,\"%s\",\"%s\",\"%s\",%d,\"%s\",\"%s\"\n",
					$set['id'],
					str_replace( '"', '""', $set['title'] ),
					str_replace( '"', '""', $card['front'] ),
					str_replace( '"', '""', $card['back'] ),
					$index,
					$set['created'],
					get_user_by( 'id', $set['author_id'] )->display_name ?? 'Unknown'
				);
			}
		}
		
		return $csv;
	}

}