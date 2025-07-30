<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 
			$this->plugin_name . '-admin', 
			SKYLEARN_FLASHCARDS_ASSETS . 'css/admin.css', 
			array(), 
			$this->version, 
			'all' 
		);

		wp_enqueue_style( 
			$this->plugin_name . '-colors', 
			SKYLEARN_FLASHCARDS_ASSETS . 'css/colors.css', 
			array(), 
			$this->version, 
			'all' 
		);

		// WordPress color picker styles
		wp_enqueue_style( 'wp-color-picker' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 
			$this->plugin_name . '-admin', 
			SKYLEARN_FLASHCARDS_ASSETS . 'js/admin.js', 
			array( 'jquery', 'wp-color-picker' ), 
			$this->version, 
			false 
		);

		// Localize script for AJAX
		wp_localize_script( $this->plugin_name . '-admin', 'skylearn_admin', array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'skylearn_admin_nonce' ),
			'preview_url' => site_url( '?skylearn_preview=SET_ID' ),
			'strings'     => array(
				'confirm_delete'   => __( 'Are you sure you want to delete this item?', 'skylearn-flashcards' ),
				'confirm_bulk'     => __( 'Are you sure you want to perform this bulk action?', 'skylearn-flashcards' ),
				'saving'           => __( 'Saving...', 'skylearn-flashcards' ),
				'saved'            => __( 'Saved successfully!', 'skylearn-flashcards' ),
				'error'            => __( 'An error occurred. Please try again.', 'skylearn-flashcards' ),
				'required_field'   => __( 'This field is required.', 'skylearn-flashcards' ),
				'invalid_email'    => __( 'Please enter a valid email address.', 'skylearn-flashcards' ),
			),
		) );

	}

	/**
	 * Add admin menu pages
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {

		// Main menu page
		add_menu_page(
			__( 'SkyLearn Flashcards', 'skylearn-flashcards' ),
			__( 'Flashcards', 'skylearn-flashcards' ),
			'edit_skylearn_flashcards',
			'skylearn-flashcards',
			array( $this, 'display_main_page' ),
			'dashicons-book-alt',
			30
		);

		// Submenu pages
		add_submenu_page(
			'skylearn-flashcards',
			__( 'All Flashcard Sets', 'skylearn-flashcards' ),
			__( 'All Sets', 'skylearn-flashcards' ),
			'edit_skylearn_flashcards',
			'skylearn-flashcards',
			array( $this, 'display_main_page' )
		);

		add_submenu_page(
			'skylearn-flashcards',
			__( 'Add New Flashcard Set', 'skylearn-flashcards' ),
			__( 'Add New', 'skylearn-flashcards' ),
			'edit_skylearn_flashcards',
			'skylearn-flashcards-new',
			array( $this, 'display_editor_page' )
		);

		add_submenu_page(
			'skylearn-flashcards',
			__( 'Analytics', 'skylearn-flashcards' ),
			__( 'Analytics', 'skylearn-flashcards' ),
			'view_skylearn_analytics',
			'skylearn-flashcards-analytics',
			array( $this, 'display_analytics_page' )
		);

		if ( skylearn_is_premium() ) {
			add_submenu_page(
				'skylearn-flashcards',
				__( 'Leads', 'skylearn-flashcards' ),
				__( 'Leads', 'skylearn-flashcards' ),
				'manage_skylearn_leads',
				'skylearn-flashcards-leads',
				array( $this, 'display_leads_page' )
			);

			add_submenu_page(
				'skylearn-flashcards',
				__( 'Reports', 'skylearn-flashcards' ),
				__( 'Reports', 'skylearn-flashcards' ),
				'view_skylearn_analytics',
				'skylearn-flashcards-reports',
				array( $this, 'display_reports_page' )
			);
		}

		add_submenu_page(
			'skylearn-flashcards',
			__( 'Settings', 'skylearn-flashcards' ),
			__( 'Settings', 'skylearn-flashcards' ),
			'manage_skylearn_flashcards',
			'skylearn-flashcards-settings',
			array( $this, 'display_settings_page' )
		);

	}

	/**
	 * Initialize admin functionality
	 *
	 * @since    1.0.0
	 */
	public function admin_init() {

		// Register settings
		$this->register_settings();

		// Handle activation redirect
		$this->handle_activation_redirect();

		// Add custom post type columns
		$this->setup_post_type_columns();

	}

	/**
	 * Register plugin settings
	 *
	 * @since    1.0.0
	 */
	private function register_settings() {

		register_setting( 'skylearn_flashcards_settings', 'skylearn_flashcards_settings', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
		) );

		register_setting( 'skylearn_flashcards_premium', 'skylearn_flashcards_premium_license', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		) );

	}

	/**
	 * Handle activation redirect
	 *
	 * @since    1.0.0
	 */
	private function handle_activation_redirect() {

		if ( get_option( 'skylearn_flashcards_activation_redirect', false ) ) {
			delete_option( 'skylearn_flashcards_activation_redirect' );
			
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( admin_url( 'admin.php?page=skylearn-flashcards&skylearn_welcome=1' ) );
				exit;
			}
		}

	}

	/**
	 * Setup custom post type columns
	 *
	 * @since    1.0.0
	 */
	private function setup_post_type_columns() {

		add_filter( 'manage_flashcard_set_posts_columns', array( $this, 'set_custom_columns' ) );
		add_action( 'manage_flashcard_set_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

	}

	/**
	 * Set custom columns for flashcard post type
	 *
	 * @since    1.0.0
	 * @param    array    $columns    Default columns
	 * @return   array                Modified columns
	 */
	public function set_custom_columns( $columns ) {

		$new_columns = array();
		
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			
			if ( $key === 'title' ) {
				$new_columns['cards_count'] = __( 'Cards', 'skylearn-flashcards' );
				$new_columns['shortcode'] = __( 'Shortcode', 'skylearn-flashcards' );
			}
		}

		return $new_columns;

	}

	/**
	 * Custom column content
	 *
	 * @since    1.0.0
	 * @param    string   $column     Column name
	 * @param    int      $post_id    Post ID
	 */
	public function custom_column_content( $column, $post_id ) {

		switch ( $column ) {
			case 'cards_count':
				$flashcard_data = get_post_meta( $post_id, '_skylearn_flashcard_data', true );
				$count = is_array( $flashcard_data ) ? count( $flashcard_data ) : 0;
				echo esc_html( $count );
				break;

			case 'shortcode':
				echo '<code>[skylearn_flashcard_set id="' . esc_attr( $post_id ) . '"]</code>';
				break;
		}

	}

	/**
	 * Display main admin page
	 *
	 * @since    1.0.0
	 */
	public function display_main_page() {

		skylearn_load_template( 'main-page', array(), 'admin' );

	}

	/**
	 * Display editor page
	 *
	 * @since    1.0.0
	 */
	public function display_editor_page() {

		skylearn_load_template( 'editor-page', array(), 'admin' );

	}

	/**
	 * Display analytics page
	 *
	 * @since    1.0.0
	 */
	public function display_analytics_page() {

		skylearn_load_template( 'analytics-page', array(), 'admin' );

	}

	/**
	 * Display leads page
	 *
	 * @since    1.0.0
	 */
	public function display_leads_page() {

		skylearn_load_template( 'leads-page', array(), 'admin' );

	}

	/**
	 * Display reports page
	 *
	 * @since    1.0.0
	 */
	public function display_reports_page() {

		skylearn_load_template( 'reports-page', array(), 'admin' );

	}

	/**
	 * Display settings page
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {

		skylearn_load_template( 'settings-page', array(), 'admin' );

	}

	/**
	 * Sanitize settings
	 *
	 * @since    1.0.0
	 * @param    array    $input    Raw settings input
	 * @return   array              Sanitized settings
	 */
	public function sanitize_settings( $input ) {

		$sanitized = array();

		if ( isset( $input['primary_color'] ) ) {
			$sanitized['primary_color'] = sanitize_hex_color( $input['primary_color'] );
		}

		if ( isset( $input['accent_color'] ) ) {
			$sanitized['accent_color'] = sanitize_hex_color( $input['accent_color'] );
		}

		if ( isset( $input['background_color'] ) ) {
			$sanitized['background_color'] = sanitize_hex_color( $input['background_color'] );
		}

		if ( isset( $input['text_color'] ) ) {
			$sanitized['text_color'] = sanitize_hex_color( $input['text_color'] );
		}

		if ( isset( $input['enable_analytics'] ) ) {
			$sanitized['enable_analytics'] = (bool) $input['enable_analytics'];
		}

		if ( isset( $input['enable_lead_capture'] ) ) {
			$sanitized['enable_lead_capture'] = (bool) $input['enable_lead_capture'];
		}

		if ( isset( $input['autoplay_interval'] ) ) {
			$sanitized['autoplay_interval'] = absint( $input['autoplay_interval'] );
		}

		if ( isset( $input['cards_per_session'] ) ) {
			$sanitized['cards_per_session'] = absint( $input['cards_per_session'] );
		}

		// Add more field sanitization as needed

		return $sanitized;

	}

	/**
	 * Handle AJAX settings save
	 *
	 * @since    1.0.0
	 */
	public function save_settings() {

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_admin_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'skylearn-flashcards' ) );
		}

		// Check permissions
		if ( ! skylearn_current_user_can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}

		// Save settings
		$settings = $this->sanitize_settings( $_POST );
		update_option( 'skylearn_flashcards_settings', $settings );

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully!', 'skylearn-flashcards' ) ) );

	}

	/**
	 * Handle AJAX export flashcards
	 *
	 * @since    1.0.0
	 */
	public function export_flashcards() {

		// Verify nonce
		if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'skylearn_admin_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'skylearn-flashcards' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'export_skylearn_flashcards' ) ) {
			wp_die( __( 'Insufficient permissions.', 'skylearn-flashcards' ) );
		}

		$set_id = absint( $_GET['set_id'] ?? 0 );
		$format = sanitize_key( $_GET['format'] ?? 'json' );

		if ( ! $set_id ) {
			wp_die( __( 'Invalid flashcard set ID.', 'skylearn-flashcards' ) );
		}

		$flashcard_set = skylearn_get_flashcard_set( $set_id );
		
		if ( ! $flashcard_set ) {
			wp_die( __( 'Flashcard set not found.', 'skylearn-flashcards' ) );
		}

		$filename = sanitize_file_name( $flashcard_set['title'] ) . '.' . $format;
		$content = skylearn_export_flashcards_format( $flashcard_set['cards'], $format );

		// Set headers for download
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . strlen( $content ) );

		echo $content;
		exit;

	}

	/**
	 * Handle AJAX import flashcards
	 *
	 * @since    1.0.0
	 */
	public function import_flashcards() {

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}

		// Check permissions
		if ( ! skylearn_current_user_can_edit() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}

		// Check if file was uploaded
		if ( ! isset( $_FILES['file'] ) || $_FILES['file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => __( 'File upload failed.', 'skylearn-flashcards' ) ) );
		}

		// Process the uploaded file
		$file_content = file_get_contents( $_FILES['file']['tmp_name'] );
		$file_extension = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );

		// Parse file content based on extension
		$flashcards = array();
		
		switch ( $file_extension ) {
			case 'json':
				$flashcards = json_decode( $file_content, true );
				break;
			case 'csv':
				// Basic CSV parsing - can be enhanced
				$lines = explode( "\n", $file_content );
				array_shift( $lines ); // Remove header
				foreach ( $lines as $line ) {
					if ( empty( trim( $line ) ) ) continue;
					$data = str_getcsv( $line );
					if ( count( $data ) >= 2 ) {
						$flashcards[] = array(
							'question' => $data[0] ?? '',
							'answer'   => $data[1] ?? '',
							'hint'     => $data[2] ?? '',
							'difficulty' => $data[3] ?? 'medium',
						);
					}
				}
				break;
			default:
				wp_send_json_error( array( 'message' => __( 'Unsupported file format.', 'skylearn-flashcards' ) ) );
		}

		if ( empty( $flashcards ) ) {
			wp_send_json_error( array( 'message' => __( 'No valid flashcards found in file.', 'skylearn-flashcards' ) ) );
		}

		// Create new flashcard set
		$post_id = wp_insert_post( array(
			'post_title'  => sanitize_text_field( $_POST['set_title'] ?? 'Imported Flashcard Set' ),
			'post_type'   => 'skylearn_flashcard',
			'post_status' => 'draft',
			'post_author' => get_current_user_id(),
		) );

		if ( $post_id ) {
			update_post_meta( $post_id, '_skylearn_flashcard_data', skylearn_sanitize_flashcard_data( $flashcards ) );
			wp_send_json_success( array( 
				'message' => sprintf( __( 'Successfully imported %d flashcards.', 'skylearn-flashcards' ), count( $flashcards ) ),
				'post_id' => $post_id
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create flashcard set.', 'skylearn-flashcards' ) ) );
		}

	}

	/**
	 * Handle bulk actions
	 *
	 * @since    1.0.0
	 */
	public function handle_bulk_action() {

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}

		// Check permissions
		if ( ! skylearn_current_user_can_edit() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}

		$action = sanitize_key( $_POST['bulk_action'] ?? '' );
		$items = array_map( 'absint', $_POST['items'] ?? array() );

		if ( empty( $action ) || empty( $items ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid bulk action or items.', 'skylearn-flashcards' ) ) );
		}

		$processed = 0;

		foreach ( $items as $item_id ) {
			switch ( $action ) {
				case 'delete':
					if ( wp_delete_post( $item_id, true ) ) {
						$processed++;
					}
					break;
				case 'publish':
					if ( wp_update_post( array( 'ID' => $item_id, 'post_status' => 'publish' ) ) ) {
						$processed++;
					}
					break;
				case 'draft':
					if ( wp_update_post( array( 'ID' => $item_id, 'post_status' => 'draft' ) ) ) {
						$processed++;
					}
					break;
			}
		}

		wp_send_json_success( array( 
			'message' => sprintf( __( 'Bulk action completed. %d items processed.', 'skylearn-flashcards' ), $processed )
		) );

	}

}