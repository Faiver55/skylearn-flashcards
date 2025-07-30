<?php
/**
 * Fired during plugin activation, deactivation, and uninstall
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/setup
 */

/**
 * Fired during plugin activation, deactivation, and uninstall.
 *
 * This class defines all code necessary to run during the plugin's activation,
 * deactivation, and uninstall.
 *
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/setup
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Setup {

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in skylearn-flashcards.php
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		// Check WordPress version
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			wp_die( 
				'SkyLearn Flashcards requires WordPress version 5.0 or higher. Please update WordPress and try again.',
				'WordPress Version Error',
				array( 'back_link' => true )
			);
		}

		// Check PHP version
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			wp_die( 
				'SkyLearn Flashcards requires PHP version 7.4 or higher. Please contact your hosting provider to update PHP.',
				'PHP Version Error',
				array( 'back_link' => true )
			);
		}

		// Create database tables
		self::create_tables();

		// Set default options
		self::set_default_options();

		// Create default capabilities
		self::add_capabilities();

		// Create custom post types and taxonomies
		self::register_post_types();
		self::register_taxonomies();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Set activation redirect flag
		add_option( 'skylearn_flashcards_activation_redirect', true );

		// Log activation
		self::log_event( 'plugin_activated' );
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in skylearn-flashcards.php
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		// Flush rewrite rules
		flush_rewrite_rules();

		// Clear scheduled events
		wp_clear_scheduled_hook( 'skylearn_flashcards_daily_cleanup' );
		wp_clear_scheduled_hook( 'skylearn_flashcards_weekly_stats' );

		// Log deactivation
		self::log_event( 'plugin_deactivated' );
	}

	/**
	 * Create database tables for the plugin
	 *
	 * @since    1.0.0
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Flashcard analytics table
		$table_analytics = $wpdb->prefix . 'skylearn_flashcard_analytics';
		$sql_analytics = "CREATE TABLE $table_analytics (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			set_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			card_index int(11) NOT NULL,
			action varchar(50) NOT NULL,
			time_spent int(11) DEFAULT 0,
			accuracy float DEFAULT 0,
			session_id varchar(255) NOT NULL,
			ip_address varchar(45) DEFAULT '',
			user_agent text DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY set_id (set_id),
			KEY user_id (user_id),
			KEY session_id (session_id),
			KEY created_at (created_at)
		) $charset_collate;";

		// User progress table
		$table_progress = $wpdb->prefix . 'skylearn_flashcard_progress';
		$sql_progress = "CREATE TABLE $table_progress (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			set_id bigint(20) NOT NULL,
			card_index int(11) NOT NULL,
			status varchar(20) DEFAULT 'unknown',
			attempts int(11) DEFAULT 0,
			correct_attempts int(11) DEFAULT 0,
			last_attempt datetime DEFAULT CURRENT_TIMESTAMP,
			mastery_level float DEFAULT 0,
			next_review datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_set_card (user_id, set_id, card_index),
			KEY user_id (user_id),
			KEY set_id (set_id),
			KEY status (status),
			KEY next_review (next_review)
		) $charset_collate;";

		// Lead collection table (premium feature)
		$table_leads = $wpdb->prefix . 'skylearn_flashcard_leads';
		$sql_leads = "CREATE TABLE $table_leads (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			set_id bigint(20) NOT NULL,
			name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			phone varchar(50) DEFAULT '',
			message text DEFAULT '',
			source varchar(100) DEFAULT '',
			status varchar(20) DEFAULT 'new',
			tags text DEFAULT '',
			ip_address varchar(45) DEFAULT '',
			user_agent text DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY set_id (set_id),
			KEY email (email),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_analytics );
		dbDelta( $sql_progress );
		dbDelta( $sql_leads );
	}

	/**
	 * Set default plugin options
	 *
	 * @since    1.0.0
	 */
	private static function set_default_options() {
		
		// Plugin version
		add_option( 'skylearn_flashcards_version', SKYLEARN_FLASHCARDS_VERSION );

		// Default settings
		$default_settings = array(
			'primary_color'       => SKYLEARN_FLASHCARDS_COLOR_PRIMARY,
			'accent_color'        => SKYLEARN_FLASHCARDS_COLOR_ACCENT,
			'background_color'    => SKYLEARN_FLASHCARDS_COLOR_BACKGROUND,
			'text_color'          => SKYLEARN_FLASHCARDS_COLOR_TEXT,
			'enable_analytics'    => true,
			'enable_lead_capture' => false,
			'autoplay_interval'   => 3000,
			'flip_animation'      => 'flip',
			'show_progress'       => true,
			'enable_keyboard'     => true,
			'enable_touch'        => true,
			'cards_per_session'   => 10,
			'spaced_repetition'   => false,
			'difficulty_adjustment' => false,
		);

		add_option( 'skylearn_flashcards_settings', $default_settings );

		// Premium settings (empty by default)
		add_option( 'skylearn_flashcards_premium_settings', array() );

		// License information
		add_option( 'skylearn_flashcards_license_key', '' );
		add_option( 'skylearn_flashcards_license_status', 'inactive' );

		// Email integration settings
		add_option( 'skylearn_flashcards_email_settings', array(
			'provider'    => '',
			'api_key'     => '',
			'list_id'     => '',
			'double_optin' => true,
		) );
	}

	/**
	 * Add custom capabilities
	 *
	 * @since    1.0.0
	 */
	private static function add_capabilities() {
		
		// Get administrator role
		$admin_role = get_role( 'administrator' );
		
		if ( $admin_role ) {
			$admin_role->add_cap( 'manage_skylearn_flashcards' );
			$admin_role->add_cap( 'edit_skylearn_flashcards' );
			$admin_role->add_cap( 'delete_skylearn_flashcards' );
			$admin_role->add_cap( 'view_skylearn_analytics' );
			$admin_role->add_cap( 'export_skylearn_flashcards' );
			$admin_role->add_cap( 'manage_skylearn_leads' );
		}

		// Add teacher role capabilities (if role exists)
		$teacher_role = get_role( 'teacher' );
		if ( $teacher_role ) {
			$teacher_role->add_cap( 'edit_skylearn_flashcards' );
			$teacher_role->add_cap( 'view_skylearn_analytics' );
		}

		// Add editor role capabilities
		$editor_role = get_role( 'editor' );
		if ( $editor_role ) {
			$editor_role->add_cap( 'edit_skylearn_flashcards' );
		}
	}

	/**
	 * Register custom post types
	 *
	 * @since    1.0.0
	 */
	public static function register_post_types() {
		
		// Flashcard set post type (PHASE 2 requirement: 'flashcard_set')
		register_post_type( 'flashcard_set', array(
			'labels' => array(
				'name'               => __( 'Flashcard Sets', 'skylearn-flashcards' ),
				'singular_name'      => __( 'Flashcard Set', 'skylearn-flashcards' ),
				'menu_name'          => __( 'Flashcards', 'skylearn-flashcards' ),
				'name_admin_bar'     => __( 'Flashcard Set', 'skylearn-flashcards' ),
				'add_new'            => __( 'Add New', 'skylearn-flashcards' ),
				'add_new_item'       => __( 'Add New Flashcard Set', 'skylearn-flashcards' ),
				'new_item'           => __( 'New Flashcard Set', 'skylearn-flashcards' ),
				'edit_item'          => __( 'Edit Flashcard Set', 'skylearn-flashcards' ),
				'view_item'          => __( 'View Flashcard Set', 'skylearn-flashcards' ),
				'all_items'          => __( 'All Flashcard Sets', 'skylearn-flashcards' ),
				'search_items'       => __( 'Search Flashcard Sets', 'skylearn-flashcards' ),
				'parent_item_colon'  => __( 'Parent Flashcard Sets:', 'skylearn-flashcards' ),
				'not_found'          => __( 'No flashcard sets found.', 'skylearn-flashcards' ),
				'not_found_in_trash' => __( 'No flashcard sets found in Trash.', 'skylearn-flashcards' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => false, // We'll add our own menu
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'flashcard-set' ),
			'capability_type'     => 'flashcard_set',
			'capabilities'        => array(
				'edit_post'          => 'edit_skylearn_flashcards',
				'read_post'          => 'read_skylearn_flashcards',
				'delete_post'        => 'delete_skylearn_flashcards',
				'edit_posts'         => 'edit_skylearn_flashcards',
				'edit_others_posts'  => 'manage_skylearn_flashcards',
				'delete_posts'       => 'delete_skylearn_flashcards',
				'publish_posts'      => 'edit_skylearn_flashcards',
				'read_private_posts' => 'read_skylearn_flashcards',
			),
			'map_meta_cap'        => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title', 'author', 'custom-fields' ),
			'show_in_rest'        => true,
			'rest_base'           => 'flashcard-sets',
		) );
	}

	/**
	 * Register custom taxonomies
	 *
	 * @since    1.0.0
	 */
	public static function register_taxonomies() {
		
		// Flashcard category taxonomy
		register_taxonomy( 'flashcard_category', array( 'flashcard_set' ), array(
			'hierarchical'      => true,
			'labels'            => array(
				'name'              => __( 'Flashcard Categories', 'skylearn-flashcards' ),
				'singular_name'     => __( 'Flashcard Category', 'skylearn-flashcards' ),
				'search_items'      => __( 'Search Categories', 'skylearn-flashcards' ),
				'all_items'         => __( 'All Categories', 'skylearn-flashcards' ),
				'parent_item'       => __( 'Parent Category', 'skylearn-flashcards' ),
				'parent_item_colon' => __( 'Parent Category:', 'skylearn-flashcards' ),
				'edit_item'         => __( 'Edit Category', 'skylearn-flashcards' ),
				'update_item'       => __( 'Update Category', 'skylearn-flashcards' ),
				'add_new_item'      => __( 'Add New Category', 'skylearn-flashcards' ),
				'new_item_name'     => __( 'New Category Name', 'skylearn-flashcards' ),
				'menu_name'         => __( 'Categories', 'skylearn-flashcards' ),
			),
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'flashcard-category' ),
			'show_in_rest'      => true,
		) );

		// Flashcard tag taxonomy
		register_taxonomy( 'flashcard_tag', array( 'flashcard_set' ), array(
			'hierarchical'      => false,
			'labels'            => array(
				'name'                       => __( 'Flashcard Tags', 'skylearn-flashcards' ),
				'singular_name'              => __( 'Flashcard Tag', 'skylearn-flashcards' ),
				'search_items'               => __( 'Search Tags', 'skylearn-flashcards' ),
				'popular_items'              => __( 'Popular Tags', 'skylearn-flashcards' ),
				'all_items'                  => __( 'All Tags', 'skylearn-flashcards' ),
				'edit_item'                  => __( 'Edit Tag', 'skylearn-flashcards' ),
				'update_item'                => __( 'Update Tag', 'skylearn-flashcards' ),
				'add_new_item'               => __( 'Add New Tag', 'skylearn-flashcards' ),
				'new_item_name'              => __( 'New Tag Name', 'skylearn-flashcards' ),
				'separate_items_with_commas' => __( 'Separate tags with commas', 'skylearn-flashcards' ),
				'add_or_remove_items'        => __( 'Add or remove tags', 'skylearn-flashcards' ),
				'choose_from_most_used'      => __( 'Choose from the most used tags', 'skylearn-flashcards' ),
				'not_found'                  => __( 'No tags found.', 'skylearn-flashcards' ),
				'menu_name'                  => __( 'Tags', 'skylearn-flashcards' ),
			),
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'flashcard-tag' ),
			'show_in_rest'      => true,
		) );
	}

	/**
	 * Log plugin events
	 *
	 * @since    1.0.0
	 * @param    string    $event    The event to log
	 */
	private static function log_event( $event ) {
		
		$log_data = array(
			'event'     => $event,
			'timestamp' => current_time( 'mysql' ),
			'user_id'   => get_current_user_id(),
			'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
		);

		// Store in database or log file (implementation depends on requirements)
		error_log( 'SkyLearn Flashcards: ' . $event . ' at ' . $log_data['timestamp'] );
	}

	/**
	 * Check if plugin needs database update
	 *
	 * @since    1.0.0
	 * @return   bool    True if update needed, false otherwise
	 */
	public static function needs_database_update() {
		$current_version = get_option( 'skylearn_flashcards_version', '0.0.0' );
		return version_compare( $current_version, SKYLEARN_FLASHCARDS_VERSION, '<' );
	}

	/**
	 * Update database schema if needed
	 *
	 * @since    1.0.0
	 */
	public static function maybe_update_database() {
		if ( self::needs_database_update() ) {
			self::create_tables();
			update_option( 'skylearn_flashcards_version', SKYLEARN_FLASHCARDS_VERSION );
			self::log_event( 'database_updated' );
		}
	}

	/**
	 * Create default flashcard set for demo purposes
	 *
	 * @since    1.0.0
	 */
	public static function create_demo_content() {
		
		// Check if demo content already exists
		$existing_demo = get_posts( array(
			'post_type'   => 'flashcard_set',
			'meta_key'    => '_skylearn_demo_content',
			'meta_value'  => '1',
			'numberposts' => 1,
		) );

		if ( ! empty( $existing_demo ) ) {
			return; // Demo content already exists
		}

		// Create demo flashcard set
		$demo_set_id = wp_insert_post( array(
			'post_title'   => __( 'Welcome to SkyLearn Flashcards!', 'skylearn-flashcards' ),
			'post_content' => __( 'This is a demo flashcard set to help you get started with SkyLearn Flashcards.', 'skylearn-flashcards' ),
			'post_status'  => 'publish',
			'post_type'    => 'flashcard_set',
			'post_author'  => get_current_user_id(),
		) );

		if ( $demo_set_id ) {
			// Mark as demo content
			update_post_meta( $demo_set_id, '_skylearn_demo_content', '1' );

			// Add demo flashcards
			$demo_cards = array(
				array(
					'front' => __( 'What is SkyLearn Flashcards?', 'skylearn-flashcards' ),
					'back'  => __( 'SkyLearn Flashcards is a premium WordPress plugin for creating interactive flashcard sets for educational purposes.', 'skylearn-flashcards' ),
				),
				array(
					'front' => __( 'How do you flip a flashcard?', 'skylearn-flashcards' ),
					'back'  => __( 'Click on the flashcard or press the spacebar to flip it and reveal the answer.', 'skylearn-flashcards' ),
				),
				array(
					'front' => __( 'What are the main features?', 'skylearn-flashcards' ),
					'back'  => __( 'Interactive flashcards, LMS integration, analytics, lead collection, and premium reporting features.', 'skylearn-flashcards' ),
				),
				array(
					'front' => __( 'Who can use this plugin?', 'skylearn-flashcards' ),
					'back'  => __( 'Teachers, students, schools, online academies, and anyone who wants to create educational content.', 'skylearn-flashcards' ),
				),
				array(
					'front' => __( 'Is it mobile-friendly?', 'skylearn-flashcards' ),
					'back'  => __( 'Yes! SkyLearn Flashcards is fully responsive and includes touch gesture support for mobile devices.', 'skylearn-flashcards' ),
				),
			);

			update_post_meta( $demo_set_id, '_skylearn_flashcard_data', $demo_cards );
			
			// Add to demo category
			wp_set_object_terms( $demo_set_id, 'Demo', 'flashcard_category' );
			wp_set_object_terms( $demo_set_id, array( 'getting-started', 'demo', 'tutorial' ), 'flashcard_tag' );
		}
	}
}