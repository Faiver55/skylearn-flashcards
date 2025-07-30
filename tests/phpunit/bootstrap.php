<?php
/**
 * PHPUnit bootstrap file for SkyLearn Flashcards plugin tests
 *
 * @package SkyLearn_Flashcards
 * @subpackage Tests
 * @since 1.0.0
 */

// Prevent WordPress from running
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/wordpress/' );
}

// WordPress test environment constants
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );

// Plugin constants
define( 'SKYLEARN_FLASHCARDS_VERSION', '1.0.0' );
define( 'SKYLEARN_FLASHCARDS_PATH', dirname( __DIR__, 2 ) . '/' );
define( 'SKYLEARN_FLASHCARDS_URL', 'http://localhost/skylearn-flashcards/' );
define( 'SKYLEARN_FLASHCARDS_BASENAME', 'skylearn-flashcards/skylearn-flashcards.php' );
define( 'SKYLEARN_FLASHCARDS_ASSETS', SKYLEARN_FLASHCARDS_URL . 'assets/' );
define( 'SKYLEARN_FLASHCARDS_LOGO', SKYLEARN_FLASHCARDS_ASSETS . 'img/' );

// Color scheme constants
define( 'SKYLEARN_FLASHCARDS_COLOR_PRIMARY', '#3498db' );
define( 'SKYLEARN_FLASHCARDS_COLOR_ACCENT', '#f39c12' );
define( 'SKYLEARN_FLASHCARDS_COLOR_BACKGROUND', '#f8f9fa' );
define( 'SKYLEARN_FLASHCARDS_COLOR_TEXT', '#222831' );

// Load WordPress test framework if available
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// WordPress functions that may be needed in tests
if ( ! function_exists( 'wp_die' ) ) {
	/**
	 * Mock wp_die function for testing
	 *
	 * @param string $message Error message.
	 * @param string $title   Error title.
	 * @param array  $args    Additional arguments.
	 * @throws Exception When wp_die is called in tests.
	 */
	function wp_die( $message = '', $title = '', $args = array() ) {
		throw new Exception( 'wp_die called: ' . $message );
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Mock translation function for testing
	 *
	 * @param string $text Text to translate.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( '_e' ) ) {
	/**
	 * Mock echo translation function for testing
	 *
	 * @param string $text Text to translate.
	 * @param string $domain Text domain.
	 */
	function _e( $text, $domain = 'default' ) {
		echo $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Mock HTML escaping function for testing
	 *
	 * @param string $text Text to escape.
	 * @return string
	 */
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/**
	 * Mock attribute escaping function for testing
	 *
	 * @param string $text Text to escape.
	 * @return string
	 */
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Mock sanitization function for testing
	 *
	 * @param string $str String to sanitize.
	 * @return string
	 */
	function sanitize_text_field( $str ) {
		return trim( strip_tags( $str ) );
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	/**
	 * Mock nonce field function for testing
	 *
	 * @param string $action Nonce action.
	 * @param string $name   Nonce name.
	 * @param bool   $referer Whether to add referer field.
	 * @param bool   $echo    Whether to echo or return.
	 * @return string
	 */
	function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
		$nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="test_nonce" />';
		if ( $echo ) {
			echo $nonce_field;
		}
		return $nonce_field;
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	/**
	 * Mock nonce verification function for testing
	 *
	 * @param string $nonce  Nonce to verify.
	 * @param string $action Action to verify against.
	 * @return bool
	 */
	function wp_verify_nonce( $nonce, $action = -1 ) {
		return 'test_nonce' === $nonce;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	/**
	 * Mock capability check function for testing
	 *
	 * @param string $capability Capability to check.
	 * @return bool
	 */
	function current_user_can( $capability ) {
		return true; // Return true for testing purposes
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	/**
	 * Mock plugin directory path function
	 *
	 * @param string $file Plugin file.
	 * @return string
	 */
	function plugin_dir_path( $file ) {
		return dirname( $file ) . '/';
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	/**
	 * Mock plugin directory URL function
	 *
	 * @param string $file Plugin file.
	 * @return string
	 */
	function plugin_dir_url( $file ) {
		return 'http://localhost/' . basename( dirname( $file ) ) . '/';
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	/**
	 * Mock plugin basename function
	 *
	 * @param string $file Plugin file.
	 * @return string
	 */
	function plugin_basename( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}
}

// Load Composer autoloader
if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'vendor/autoload.php' ) ) {
	require_once SKYLEARN_FLASHCARDS_PATH . 'vendor/autoload.php';
}

// Define WPINC to prevent direct access checks from failing
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// Load plugin files for testing
require_once SKYLEARN_FLASHCARDS_PATH . 'includes/helpers.php';

echo "SkyLearn Flashcards PHPUnit Bootstrap loaded successfully\n";