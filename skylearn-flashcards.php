<?php
/**
 * Plugin Name: SkyLearn Flashcards
 * Plugin URI: https://skyian.com/skylearn-flashcards/
 * Description: A premium WordPress flashcard plugin for teachers, students, schools, and online academies. Create interactive flashcard sets with LMS integration and advanced reporting.
 * Version: 1.0.0-beta
 * Author: Ferdous Khalifa
 * Author URI: https://skyian.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: skylearn-flashcards
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package SkyLearn_Flashcards
 * @author Ferdous Khalifa
 * @copyright 2024 Skyian LLC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SKYLEARN_FLASHCARDS_VERSION', '1.0.0-beta' );

/**
 * Plugin constants
 */
define( 'SKYLEARN_FLASHCARDS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKYLEARN_FLASHCARDS_URL', plugin_dir_url( __FILE__ ) );
define( 'SKYLEARN_FLASHCARDS_BASENAME', plugin_basename( __FILE__ ) );
define( 'SKYLEARN_FLASHCARDS_ASSETS', SKYLEARN_FLASHCARDS_URL . 'assets/' );
define( 'SKYLEARN_FLASHCARDS_LOGO', SKYLEARN_FLASHCARDS_ASSETS . 'img/' );

/**
 * Color scheme constants
 */
define( 'SKYLEARN_FLASHCARDS_COLOR_PRIMARY', '#3498db' ); // Sky Blue
define( 'SKYLEARN_FLASHCARDS_COLOR_ACCENT', '#f39c12' );  // Soft Orange
define( 'SKYLEARN_FLASHCARDS_COLOR_BACKGROUND', '#f8f9fa' ); // Light Gray
define( 'SKYLEARN_FLASHCARDS_COLOR_TEXT', '#222831' ); // Dark Slate

/**
 * Beta-specific constants
 */
if ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) {
	define( 'SKYLEARN_FLASHCARDS_IS_BETA', true );
	define( 'SKYLEARN_FLASHCARDS_BETA_SUPPORT_EMAIL', 'support@skyian.com' );
	define( 'SKYLEARN_FLASHCARDS_BETA_REPO_URL', 'https://github.com/Faiver55/skylearn-flashcards' );
} else {
	define( 'SKYLEARN_FLASHCARDS_IS_BETA', false );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/setup/class-setup.php
 */
function activate_skylearn_flashcards() {
	require_once SKYLEARN_FLASHCARDS_PATH . 'includes/setup/class-setup.php';
	SkyLearn_Flashcards_Setup::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/setup/class-setup.php
 */
function deactivate_skylearn_flashcards() {
	require_once SKYLEARN_FLASHCARDS_PATH . 'includes/setup/class-setup.php';
	SkyLearn_Flashcards_Setup::deactivate();
}

/**
 * Ensure admin capability is assigned during activation.
 * 
 * This function provides a safety net to ensure that the administrator role has 
 * the required capability to access the Flashcards admin pages. While the main
 * activation process in SkyLearn_Flashcards_Setup::add_capabilities() also adds
 * this capability, this dedicated hook ensures it's present even if there were
 * issues during the main activation process.
 *
 * @since    1.0.0
 */
function skylearn_flashcards_add_caps() {
	$role = get_role( 'administrator' );
	if ( $role && ! $role->has_cap( 'edit_skylearn_flashcards' ) ) {
		$role->add_cap( 'edit_skylearn_flashcards' );
	}
}

register_activation_hook( __FILE__, 'activate_skylearn_flashcards' );
register_activation_hook( __FILE__, 'skylearn_flashcards_add_caps' );
register_deactivation_hook( __FILE__, 'deactivate_skylearn_flashcards' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require SKYLEARN_FLASHCARDS_PATH . 'includes/class-skylearn-flashcard.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_skylearn_flashcards() {
	$plugin = new SkyLearn_Flashcard();
	$plugin->run();
}

run_skylearn_flashcards();

/**
 * Beta-specific helper functions
 */
if ( SKYLEARN_FLASHCARDS_IS_BETA ) {
	
	/**
	 * Check if current install is beta version
	 *
	 * @since 1.0.0-beta
	 * @return bool
	 */
	function skylearn_is_beta() {
		return SKYLEARN_FLASHCARDS_IS_BETA;
	}
	
	/**
	 * Get beta support email
	 *
	 * @since 1.0.0-beta
	 * @return string
	 */
	function skylearn_get_beta_support_email() {
		return SKYLEARN_FLASHCARDS_BETA_SUPPORT_EMAIL;
	}
	
	/**
	 * Get beta repository URL
	 *
	 * @since 1.0.0-beta
	 * @return string
	 */
	function skylearn_get_beta_repo_url() {
		return SKYLEARN_FLASHCARDS_BETA_REPO_URL;
	}
	
	/**
	 * Log beta event
	 *
	 * @since 1.0.0-beta
	 * @param string $message
	 * @param array $context
	 */
	function skylearn_log_beta_event( $message, $context = array() ) {
		if ( class_exists( 'SkyLearn_Flashcards_Beta' ) ) {
			$beta = new SkyLearn_Flashcards_Beta();
			$beta->log_beta_event( $message, $context );
		}
	}
	
	/**
	 * Show beta admin notice
	 *
	 * @since 1.0.0-beta
	 * @param string $message
	 * @param string $type
	 */
	function skylearn_show_beta_notice( $message, $type = 'info' ) {
		add_action( 'admin_notices', function() use ( $message, $type ) {
			$class = 'notice notice-' . $type . ' skylearn-beta-notice';
			printf( '<div class="%1$s"><p>🧪 <strong>Beta:</strong> %2$s</p></div>', 
				esc_attr( $class ), 
				esc_html( $message ) 
			);
		} );
	}
}