<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcard {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SkyLearn_Flashcards_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The admin class instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SkyLearn_Flashcards_Admin    $admin    Admin functionality.
	 */
	protected $admin;

	/**
	 * The frontend class instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SkyLearn_Flashcards_Frontend    $frontend    Frontend functionality.
	 */
	protected $frontend;

	/**
	 * The LMS Manager instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SkyLearn_Flashcards_LMS_Manager    $lms_manager    LMS integration manager.
	 */
	protected $lms_manager;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SKYLEARN_FLASHCARDS_VERSION' ) ) {
			$this->version = SKYLEARN_FLASHCARDS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'skylearn-flashcards';

		$this->load_dependencies();
		$this->set_locale();
		$this->init_lms_integration();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SkyLearn_Flashcards_Loader. Orchestrates the hooks of the plugin.
	 * - SkyLearn_Flashcards_i18n. Defines internationalization functionality.
	 * - SkyLearn_Flashcards_Admin. Defines all hooks for the admin area.
	 * - SkyLearn_Flashcards_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/helpers/class-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/helpers/class-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/admin/class-admin.php';

		/**
		 * The flashcard editor functionality.
		 */
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/admin/class-editor.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-frontend.php';

		/**
		 * Helper functions and utilities
		 */
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/helpers/functions.php';
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/helpers.php';

		/**
		 * Premium functionality (if enabled)
		 */
		if ( $this->is_premium_enabled() ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
		}

		$this->loader = new SkyLearn_Flashcards_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the SkyLearn_Flashcards_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new SkyLearn_Flashcards_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Initialize LMS integration.
	 *
	 * Loads and initializes the LMS Manager for integration with supported LMS platforms.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_lms_integration() {

		/**
		 * Load LMS Manager
		 */
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/lms/class-lms-manager.php';

		$this->lms_manager = new SkyLearn_Flashcards_LMS_Manager();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->admin = new SkyLearn_Flashcards_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $this->admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $this->admin, 'admin_init' );

		// Initialize editor functionality
		$editor = new SkyLearn_Flashcards_Editor( $this->get_plugin_name(), $this->get_version() );

		// AJAX hooks for admin functionality
		$this->loader->add_action( 'wp_ajax_skylearn_save_settings', $this->admin, 'save_settings' );
		$this->loader->add_action( 'wp_ajax_skylearn_export_flashcards', $this->admin, 'export_flashcards' );
		$this->loader->add_action( 'wp_ajax_skylearn_import_flashcards', $this->admin, 'import_flashcards' );
		$this->loader->add_action( 'wp_ajax_skylearn_bulk_action', $this->admin, 'handle_bulk_action' );
		
		// Lead management AJAX hooks (premium only)
		if ( skylearn_is_premium() ) {
			$leads = new SkyLearn_Flashcards_Leads( $this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'wp_ajax_skylearn_get_lead_details', $leads, 'ajax_get_lead_details' );
			$this->loader->add_action( 'wp_ajax_skylearn_update_lead_status', $leads, 'ajax_update_lead_status' );
			$this->loader->add_action( 'wp_ajax_skylearn_delete_lead', $leads, 'ajax_delete_lead' );
			
			// Advanced reporting AJAX hooks
			$reporting = new SkyLearn_Flashcards_Advanced_Reporting();
			$this->loader->add_action( 'wp_ajax_skylearn_export_student_results', $reporting, 'ajax_export_student_results' );
		}
		
		// Add set limit enforcement hook
		$this->loader->add_action( 'wp_insert_post', $this, 'enforce_set_limit', 10, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$this->frontend = new SkyLearn_Flashcards_Frontend( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $this->frontend, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->frontend, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $this->frontend, 'init' );

		// Shortcode registration
		$this->loader->add_action( 'init', $this->frontend, 'register_shortcodes' );

		// Block registration for Gutenberg
		$this->loader->add_action( 'init', $this->frontend, 'register_blocks' );

		// AJAX hooks for frontend functionality
		$this->loader->add_action( 'wp_ajax_skylearn_track_card_view', $this->frontend, 'track_card_view' );
		$this->loader->add_action( 'wp_ajax_nopriv_skylearn_track_card_view', $this->frontend, 'track_card_view' );
		$this->loader->add_action( 'wp_ajax_skylearn_track_completion', $this->frontend, 'track_completion' );
		$this->loader->add_action( 'wp_ajax_nopriv_skylearn_track_completion', $this->frontend, 'track_completion' );
		$this->loader->add_action( 'wp_ajax_skylearn_submit_lead', $this->frontend, 'submit_lead' );
		$this->loader->add_action( 'wp_ajax_nopriv_skylearn_submit_lead', $this->frontend, 'submit_lead' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    SkyLearn_Flashcards_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Check if premium features are enabled.
	 *
	 * @since     1.0.0
	 * @return    bool    True if premium is enabled, false otherwise.
	 */
	private function is_premium_enabled() {
		// Check for premium license or setting
		$premium_license = get_option( 'skylearn_flashcards_premium_license', false );
		return !empty( $premium_license );
	}

	/**
	 * Get LMS Manager instance.
	 *
	 * @since     1.0.0
	 * @return    SkyLearn_Flashcards_LMS_Manager    The LMS Manager instance.
	 */
	public function get_lms_manager() {
		return $this->lms_manager;
	}

	/**
	 * Get plugin instance (singleton pattern).
	 *
	 * @since     1.0.0
	 * @return    SkyLearn_Flashcard    The plugin instance.
	 */
	public static function get_instance() {
		static $instance = null;
		
		if ( null === $instance ) {
			$instance = new self();
		}
		
		return $instance;
	}
	
	/**
	 * Enforce flashcard set limit for free users
	 *
	 * @since     1.0.0
	 * @param     int      $post_id   Post ID
	 * @param     WP_Post  $post      Post object
	 * @param     bool     $update    Whether this is an update
	 */
	public function enforce_set_limit( $post_id, $post, $update ) {
		// Only check for new flashcard sets
		if ( $update || $post->post_type !== 'flashcard_set' || $post->post_status === 'auto-draft' ) {
			return;
		}
		
		// Skip if premium
		if ( skylearn_is_premium() ) {
			return;
		}
		
		// Check if user has exceeded set limit
		if ( ! skylearn_user_can_create_set( $post->post_author ) ) {
			// Prevent saving and show error
			wp_delete_post( $post_id, true );
			
			wp_die(
				__( 'You have reached the maximum number of flashcard sets (5) for the free version. Please upgrade to Premium for unlimited sets and advanced features.', 'skylearn-flashcards' ),
				__( 'Set Limit Reached', 'skylearn-flashcards' ),
				array(
					'back_link' => true,
					'response'  => 403,
				)
			);
		}
	}
}