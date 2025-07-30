<?php
/**
 * Tests for the main SkyLearn_Flashcard class
 *
 * @package SkyLearn_Flashcards
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Main plugin class tests
 *
 * @since 1.0.0
 */
class SkyLearnFlashcardTest extends TestCase {

	/**
	 * Instance of the main plugin class
	 *
	 * @var SkyLearn_Flashcard
	 */
	private $plugin;

	/**
	 * Set up test environment before each test
	 *
	 * @since 1.0.0
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Include the main plugin class
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/class-skylearn-flashcard.php';
		
		// Mock WordPress functions that may not be available
		if ( ! function_exists( 'wp_kses_post' ) ) {
			function wp_kses_post( $data ) {
				return $data;
			}
		}
		
		$this->plugin = new SkyLearn_Flashcard();
	}

	/**
	 * Clean up after each test
	 *
	 * @since 1.0.0
	 */
	protected function tearDown(): void {
		$this->plugin = null;
		parent::tearDown();
	}

	/**
	 * Test plugin instantiation
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcard::__construct
	 */
	public function test_plugin_instantiation() {
		$this->assertInstanceOf( 'SkyLearn_Flashcard', $this->plugin );
	}

	/**
	 * Test plugin name is set correctly
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcard::get_plugin_name
	 */
	public function test_get_plugin_name() {
		$plugin_name = $this->plugin->get_plugin_name();
		$this->assertEquals( 'skylearn-flashcards', $plugin_name );
	}

	/**
	 * Test plugin version is set correctly
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcard::get_version
	 */
	public function test_get_version() {
		$version = $this->plugin->get_version();
		$this->assertEquals( SKYLEARN_FLASHCARDS_VERSION, $version );
	}

	/**
	 * Test loader is initialized
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcard::get_loader
	 */
	public function test_get_loader() {
		$loader = $this->plugin->get_loader();
		$this->assertNotNull( $loader );
	}

	/**
	 * Test that required dependencies are loaded
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcard::load_dependencies
	 */
	public function test_dependencies_loaded() {
		// Test that main class files exist
		$this->assertFileExists( SKYLEARN_FLASHCARDS_PATH . 'includes/helpers/class-loader.php' );
		$this->assertFileExists( SKYLEARN_FLASHCARDS_PATH . 'includes/helpers/class-i18n.php' );
		$this->assertFileExists( SKYLEARN_FLASHCARDS_PATH . 'includes/admin/class-admin.php' );
		$this->assertFileExists( SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-frontend.php' );
	}

	/**
	 * Test plugin constants are defined
	 *
	 * @since 1.0.0
	 * @covers Constants definition
	 */
	public function test_constants_defined() {
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_VERSION' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_PATH' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_URL' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_BASENAME' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_ASSETS' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_LOGO' ) );
	}

	/**
	 * Test color scheme constants are defined
	 *
	 * @since 1.0.0
	 * @covers Color constants definition
	 */
	public function test_color_constants_defined() {
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_COLOR_PRIMARY' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_COLOR_ACCENT' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_COLOR_BACKGROUND' ) );
		$this->assertTrue( defined( 'SKYLEARN_FLASHCARDS_COLOR_TEXT' ) );
		
		// Test color values are valid hex colors
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', SKYLEARN_FLASHCARDS_COLOR_PRIMARY );
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', SKYLEARN_FLASHCARDS_COLOR_ACCENT );
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', SKYLEARN_FLASHCARDS_COLOR_BACKGROUND );
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', SKYLEARN_FLASHCARDS_COLOR_TEXT );
	}

	/**
	 * Test plugin initialization order
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcard::__construct
	 */
	public function test_initialization_order() {
		// Create a mock plugin to test initialization sequence
		$reflection = new ReflectionClass( 'SkyLearn_Flashcard' );
		
		// Test that required properties exist
		$this->assertTrue( $reflection->hasProperty( 'plugin_name' ) );
		$this->assertTrue( $reflection->hasProperty( 'version' ) );
		$this->assertTrue( $reflection->hasProperty( 'loader' ) );
		$this->assertTrue( $reflection->hasProperty( 'admin' ) );
		$this->assertTrue( $reflection->hasProperty( 'frontend' ) );
		$this->assertTrue( $reflection->hasProperty( 'lms_manager' ) );
	}
}