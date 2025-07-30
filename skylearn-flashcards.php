<?php
/**
 * Plugin Name: SkyLearn Flashcards
 * Plugin URI: https://skyian.com/skylearn-flashcards/
 * Description: A premium WordPress flashcard plugin for teachers, students, schools, and online academies. Create engaging flashcard sets with LMS integration, lead collection, and advanced reporting.
 * Version: 1.0.0
 * Author: Ferdous Khalifa
 * Author URI: https://skyian.com/
 * Text Domain: skylearn-flashcards
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package SkyLearn_Flashcards
 * @author Ferdous Khalifa
 * @copyright 2024 Skyian LLC
 * @license GPLv3
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'SKYLEARN_FLASHCARDS_VERSION', '1.0.0' );
define( 'SKYLEARN_FLASHCARDS_PLUGIN_FILE', __FILE__ );
define( 'SKYLEARN_FLASHCARDS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SKYLEARN_FLASHCARDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SKYLEARN_FLASHCARDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SKYLEARN_FLASHCARDS_ASSETS_URL', SKYLEARN_FLASHCARDS_PLUGIN_URL . 'assets/' );
define( 'SKYLEARN_FLASHCARDS_IMAGES_URL', SKYLEARN_FLASHCARDS_ASSETS_URL . 'img/' );

// Define color scheme constants
define( 'SKYLEARN_FLASHCARDS_COLOR_PRIMARY', '#3498db' ); // Sky Blue
define( 'SKYLEARN_FLASHCARDS_COLOR_ACCENT', '#f39c12' );  // Soft Orange
define( 'SKYLEARN_FLASHCARDS_COLOR_BACKGROUND', '#f8f9fa' ); // Light Gray
define( 'SKYLEARN_FLASHCARDS_COLOR_TEXT', '#222831' ); // Dark Slate

// Define text domain for translations
define( 'SKYLEARN_FLASHCARDS_TEXT_DOMAIN', 'skylearn-flashcards' );

/**
 * Main plugin class
 *
 * @since 1.0.0
 */
class SkyLearn_Flashcard {

	/**
	 * Plugin instance
	 *
	 * @var SkyLearn_Flashcard
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return SkyLearn_Flashcard
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	private function init() {
		// Load text domain for translations
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

		// Initialize plugin components
		add_action( 'init', array( $this, 'init_components' ) );

		// Plugin activation and deactivation hooks
		register_activation_hook( SKYLEARN_FLASHCARDS_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( SKYLEARN_FLASHCARDS_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Load plugin text domain for translations
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain() {
		load_plugin_textdomain(
			SKYLEARN_FLASHCARDS_TEXT_DOMAIN,
			false,
			dirname( SKYLEARN_FLASHCARDS_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Initialize plugin components
	 *
	 * @since 1.0.0
	 */
	public function init_components() {
		// Include required files
		$this->include_files();

		// Initialize admin components
		if ( is_admin() ) {
			// TODO: Initialize admin classes
		}

		// Initialize frontend components
		if ( ! is_admin() ) {
			// TODO: Initialize frontend classes
		}

		// Initialize LMS integrations
		$this->init_lms_integrations();

		// Initialize premium features (if applicable)
		$this->init_premium_features();
	}

	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	private function include_files() {
		// Helper functions
		require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/helpers.php';

		// Setup classes
		require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/setup/class-setup.php';

		// Admin classes
		if ( is_admin() ) {
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/admin/class-admin.php';
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/admin/class-settings.php';
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/admin/class-editor.php';
		}

		// Frontend classes
		require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/frontend/class-frontend.php';
		require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/frontend/class-shortcode.php';
		require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/frontend/class-renderer.php';
	}

	/**
	 * Initialize LMS integrations
	 *
	 * @since 1.0.0
	 */
	private function init_lms_integrations() {
		// LearnDash integration
		if ( class_exists( 'SFWD_LMS' ) ) {
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/lms/class-learndash.php';
		}

		// TutorLMS integration
		if ( function_exists( 'tutor' ) ) {
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/lms/class-tutorlms.php';
		}
	}

	/**
	 * Initialize premium features
	 *
	 * @since 1.0.0
	 */
	private function init_premium_features() {
		// TODO: Check for premium license and initialize premium features
		if ( $this->is_premium_active() ) {
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/premium/class-premium.php';
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/premium/class-advanced-reporting.php';
			require_once SKYLEARN_FLASHCARDS_PLUGIN_DIR . 'includes/premium/class-export.php';
		}
	}

	/**
	 * Check if premium features are active
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function is_premium_active() {
		// TODO: Implement premium license check
		return false;
	}

	/**
	 * Plugin activation callback
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// Flush rewrite rules
		flush_rewrite_rules();

		// TODO: Create database tables if needed
		// TODO: Set default options
		// TODO: Schedule any cron jobs
	}

	/**
	 * Plugin deactivation callback
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();

		// TODO: Clear any scheduled cron jobs
	}
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 */
function skylearn_flashcards_init() {
	return SkyLearn_Flashcard::get_instance();
}

// Start the plugin
skylearn_flashcards_init();