<?php
/**
 * Tests for SkyLearn_Flashcards_Frontend class
 *
 * @package SkyLearn_Flashcards
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Frontend functionality tests
 *
 * @since 1.0.0
 */
class SkyLearnFlashcardsFrontendTest extends TestCase {

	/**
	 * Instance of the frontend class
	 *
	 * @var SkyLearn_Flashcards_Frontend
	 */
	private $frontend;

	/**
	 * Set up test environment before each test
	 *
	 * @since 1.0.0
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Mock WordPress functions for frontend
		if ( ! function_exists( 'wp_enqueue_style' ) ) {
			function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
				return true;
			}
		}
		
		if ( ! function_exists( 'wp_enqueue_script' ) ) {
			function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
				return true;
			}
		}
		
		if ( ! function_exists( 'wp_localize_script' ) ) {
			function wp_localize_script( $handle, $object_name, $l10n ) {
				return true;
			}
		}
		
		if ( ! function_exists( 'wp_add_inline_style' ) ) {
			function wp_add_inline_style( $handle, $data ) {
				return true;
			}
		}
		
		if ( ! function_exists( 'is_admin' ) ) {
			function is_admin() {
				return false;
			}
		}
		
		// Include the frontend class
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-frontend.php';
		
		$this->frontend = new SkyLearn_Flashcards_Frontend( 'skylearn-flashcards', '1.0.0' );
	}

	/**
	 * Clean up after each test
	 *
	 * @since 1.0.0
	 */
	protected function tearDown(): void {
		$this->frontend = null;
		parent::tearDown();
	}

	/**
	 * Test frontend class instantiation
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::__construct
	 */
	public function test_frontend_instantiation() {
		$this->assertInstanceOf( 'SkyLearn_Flashcards_Frontend', $this->frontend );
	}

	/**
	 * Test plugin name is set correctly
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::get_plugin_name
	 */
	public function test_get_plugin_name() {
		$plugin_name = $this->frontend->get_plugin_name();
		$this->assertEquals( 'skylearn-flashcards', $plugin_name );
	}

	/**
	 * Test version is set correctly
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::get_version
	 */
	public function test_get_version() {
		$version = $this->frontend->get_version();
		$this->assertEquals( '1.0.0', $version );
	}

	/**
	 * Test frontend styles enqueue
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::enqueue_styles
	 */
	public function test_enqueue_styles() {
		// This should not throw any errors
		$this->frontend->enqueue_styles();
		$this->assertTrue( true ); // Test passes if no exception is thrown
	}

	/**
	 * Test frontend scripts enqueue
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::enqueue_scripts
	 */
	public function test_enqueue_scripts() {
		// This should not throw any errors
		$this->frontend->enqueue_scripts();
		$this->assertTrue( true ); // Test passes if no exception is thrown
	}

	/**
	 * Test shortcode registration
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::register_shortcodes
	 */
	public function test_register_shortcodes() {
		// Mock add_shortcode function
		if ( ! function_exists( 'add_shortcode' ) ) {
			function add_shortcode( $tag, $func ) {
				return true;
			}
		}
		
		// This should not throw any errors
		$this->frontend->register_shortcodes();
		$this->assertTrue( true ); // Test passes if no exception is thrown
	}

	/**
	 * Test shortcode output generation
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::flashcard_shortcode
	 */
	public function test_flashcard_shortcode() {
		// Mock shortcode attributes
		$atts = array(
			'id' => '123',
			'title' => 'Test Flashcard Set',
		);
		
		$output = $this->frontend->flashcard_shortcode( $atts );
		
		$this->assertIsString( $output );
		$this->assertStringContainsString( 'skylearn-flashcard-set', $output );
	}

	/**
	 * Test AJAX handlers for frontend
	 *
	 * @since 1.0.0
	 */
	public function test_ajax_handlers() {
		// Mock add_action function
		if ( ! function_exists( 'add_action' ) ) {
			function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
				return true;
			}
		}
		
		// Test that AJAX handlers can be registered
		$this->frontend->register_ajax_handlers();
		$this->assertTrue( true ); // Test passes if no exception is thrown
	}

	/**
	 * Test performance tracking
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::track_performance
	 */
	public function test_track_performance() {
		// Mock performance data
		$performance_data = array(
			'set_id' => 123,
			'user_id' => 1,
			'correct_answers' => 8,
			'total_questions' => 10,
			'time_spent' => 120,
		);
		
		$result = $this->frontend->track_performance( $performance_data );
		$this->assertTrue( $result );
	}

	/**
	 * Test lead capture functionality
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Frontend::capture_lead
	 */
	public function test_capture_lead() {
		// Mock lead data
		$lead_data = array(
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'set_id' => 123,
		);
		
		$result = $this->frontend->capture_lead( $lead_data );
		$this->assertTrue( $result );
	}

	/**
	 * Test data sanitization for frontend inputs
	 *
	 * @since 1.0.0
	 */
	public function test_input_sanitization() {
		// Test XSS prevention
		$malicious_input = '<script>alert("xss")</script>';
		$sanitized = $this->frontend->sanitize_user_input( $malicious_input );
		
		$this->assertStringNotContainsString( '<script>', $sanitized );
		$this->assertStringNotContainsString( 'alert', $sanitized );
	}

	/**
	 * Test responsive design elements
	 *
	 * @since 1.0.0
	 */
	public function test_responsive_design() {
		// Test that responsive classes are added
		$output = $this->frontend->render_flashcard_set( 123 );
		
		$this->assertStringContainsString( 'skylearn-responsive', $output );
		$this->assertStringContainsString( 'skylearn-mobile-friendly', $output );
	}

	/**
	 * Test accessibility features
	 *
	 * @since 1.0.0
	 */
	public function test_accessibility_features() {
		// Test that proper ARIA attributes are added
		$output = $this->frontend->render_flashcard_set( 123 );
		
		$this->assertStringContainsString( 'aria-label', $output );
		$this->assertStringContainsString( 'role=', $output );
		$this->assertStringContainsString( 'tabindex', $output );
	}

	/**
	 * Test internationalization support
	 *
	 * @since 1.0.0
	 */
	public function test_internationalization() {
		// Test that text strings are properly wrapped for translation
		$output = $this->frontend->render_flashcard_set( 123 );
		
		// Should contain translatable strings
		$this->assertStringContainsString( 'Flip Card', $output );
		$this->assertStringContainsString( 'Next', $output );
		$this->assertStringContainsString( 'Previous', $output );
	}

	/**
	 * Test error handling for invalid flashcard sets
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_set_handling() {
		// Test with non-existent set ID
		$output = $this->frontend->render_flashcard_set( 99999 );
		
		$this->assertStringContainsString( 'Flashcard set not found', $output );
	}

	/**
	 * Test caching mechanisms
	 *
	 * @since 1.0.0
	 */
	public function test_caching() {
		// Mock WordPress cache functions
		if ( ! function_exists( 'wp_cache_get' ) ) {
			function wp_cache_get( $key, $group = '' ) {
				return false;
			}
		}
		
		if ( ! function_exists( 'wp_cache_set' ) ) {
			function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
				return true;
			}
		}
		
		// Test that caching is properly implemented
		$cached_output = $this->frontend->get_cached_flashcard_set( 123 );
		$this->assertTrue( is_string( $cached_output ) || is_null( $cached_output ) );
	}
}