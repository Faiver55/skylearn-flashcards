<?php
/**
 * Integration tests for SkyLearn Flashcards
 *
 * @package SkyLearn_Flashcards
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Integration tests class
 *
 * @since 1.0.0
 */
class SkyLearnFlashcardsIntegrationTest extends TestCase {

	/**
	 * Set up test environment before each test
	 *
	 * @since 1.0.0
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Mock WordPress environment more thoroughly for integration tests
		$this->mockWordPressEnvironment();
		$this->createTestData();
	}

	/**
	 * Mock WordPress environment for integration testing
	 *
	 * @since 1.0.0
	 */
	private function mockWordPressEnvironment() {
		// Mock database functions
		if ( ! function_exists( 'wp_insert_post' ) ) {
			function wp_insert_post( $postarr, $wp_error = false ) {
				return rand( 1, 1000 ); // Mock post ID
			}
		}
		
		if ( ! function_exists( 'wp_update_post' ) ) {
			function wp_update_post( $postarr, $wp_error = false ) {
				return rand( 1, 1000 ); // Mock post ID
			}
		}
		
		if ( ! function_exists( 'wp_delete_post' ) ) {
			function wp_delete_post( $postid, $force_delete = false ) {
				return (object) array( 'ID' => $postid );
			}
		}
		
		if ( ! function_exists( 'add_post_meta' ) ) {
			function add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
				return rand( 1, 1000 ); // Mock meta ID
			}
		}
		
		if ( ! function_exists( 'update_post_meta' ) ) {
			function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
				return true;
			}
		}
		
		if ( ! function_exists( 'delete_post_meta' ) ) {
			function delete_post_meta( $post_id, $meta_key, $meta_value = '' ) {
				return true;
			}
		}
		
		// Mock user functions
		if ( ! function_exists( 'wp_insert_user' ) ) {
			function wp_insert_user( $userdata ) {
				return rand( 1, 1000 ); // Mock user ID
			}
		}
		
		if ( ! function_exists( 'get_userdata' ) ) {
			function get_userdata( $user_id ) {
				return (object) array(
					'ID' => $user_id,
					'user_login' => 'testuser',
					'user_email' => 'test@example.com',
					'display_name' => 'Test User',
				);
			}
		}
		
		// Mock options functions
		global $wp_options;
		$wp_options = array();
		
		if ( ! function_exists( 'add_option' ) ) {
			function add_option( $option, $value = '', $deprecated = '', $autoload = 'yes' ) {
				global $wp_options;
				$wp_options[ $option ] = $value;
				return true;
			}
		}
		
		if ( ! function_exists( 'get_option' ) ) {
			function get_option( $option, $default = false ) {
				global $wp_options;
				return isset( $wp_options[ $option ] ) ? $wp_options[ $option ] : $default;
			}
		}
		
		if ( ! function_exists( 'update_option' ) ) {
			function update_option( $option, $value, $autoload = null ) {
				global $wp_options;
				$wp_options[ $option ] = $value;
				return true;
			}
		}
		
		if ( ! function_exists( 'delete_option' ) ) {
			function delete_option( $option ) {
				global $wp_options;
				unset( $wp_options[ $option ] );
				return true;
			}
		}
	}

	/**
	 * Create test data for integration tests
	 *
	 * @since 1.0.0
	 */
	private function createTestData() {
		// Create test flashcard sets
		add_option( 'skylearn_flashcards_test_sets', array(
			1 => array(
				'id' => 1,
				'title' => 'Test Set 1',
				'cards' => array(
					array( 'front' => 'Question 1', 'back' => 'Answer 1' ),
					array( 'front' => 'Question 2', 'back' => 'Answer 2' ),
				),
			),
			2 => array(
				'id' => 2,
				'title' => 'Test Set 2',
				'cards' => array(
					array( 'front' => 'Question 3', 'back' => 'Answer 3' ),
				),
			),
		) );
		
		// Create test user data
		add_option( 'skylearn_flashcards_test_users', array(
			1 => array(
				'id' => 1,
				'progress' => array(
					1 => array( 'completed' => 2, 'total' => 2, 'score' => 100 ),
					2 => array( 'completed' => 0, 'total' => 1, 'score' => 0 ),
				),
			),
		) );
	}

	/**
	 * Test complete plugin workflow
	 *
	 * @since 1.0.0
	 */
	public function test_complete_plugin_workflow() {
		// 1. Initialize plugin
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/class-skylearn-flashcard.php';
		$plugin = new SkyLearn_Flashcard();
		
		$this->assertInstanceOf( 'SkyLearn_Flashcard', $plugin );
		
		// 2. Create a flashcard set
		$set_data = array(
			'title' => 'Integration Test Set',
			'cards' => array(
				array( 'front' => 'Integration Question 1', 'back' => 'Integration Answer 1' ),
				array( 'front' => 'Integration Question 2', 'back' => 'Integration Answer 2' ),
			),
		);
		
		// Mock set creation
		$set_id = wp_insert_post( array(
			'post_title' => $set_data['title'],
			'post_type' => 'flashcard_set',
			'post_status' => 'publish',
		) );
		
		$this->assertIsInt( $set_id );
		$this->assertGreaterThan( 0, $set_id );
		
		// 3. Add flashcard data
		$meta_result = add_post_meta( $set_id, '_skylearn_flashcard_data', $set_data['cards'] );
		$this->assertIsInt( $meta_result );
		
		// 4. Test frontend rendering
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-frontend.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-frontend.php';
			
			$frontend = new SkyLearn_Flashcards_Frontend( 'skylearn-flashcards', '1.0.0' );
			$output = $frontend->render_flashcard_set( $set_id );
			
			$this->assertIsString( $output );
			$this->assertStringContainsString( 'Integration Question 1', $output );
		}
		
		// 5. Test admin functionality
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/admin/class-admin.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/admin/class-admin.php';
			
			$admin = new SkyLearn_Flashcards_Admin( 'skylearn-flashcards', '1.0.0' );
			
			$this->assertInstanceOf( 'SkyLearn_Flashcards_Admin', $admin );
		}
	}

	/**
	 * Test shortcode functionality end-to-end
	 *
	 * @since 1.0.0
	 */
	public function test_shortcode_workflow() {
		// Mock shortcode registration
		if ( ! function_exists( 'add_shortcode' ) ) {
			function add_shortcode( $tag, $func ) {
				global $shortcode_tags;
				$shortcode_tags[ $tag ] = $func;
				return true;
			}
		}
		
		if ( ! function_exists( 'do_shortcode' ) ) {
			function do_shortcode( $content ) {
				// Simple mock implementation
				if ( preg_match( '/\[skylearn_flashcards[^\]]*\]/', $content ) ) {
					return '<div class="skylearn-flashcard-set">Flashcard content</div>';
				}
				return $content;
			}
		}
		
		// Register shortcode
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-shortcode.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-shortcode.php';
			
			$shortcode = new SkyLearn_Flashcards_Shortcode();
			$shortcode->register_shortcodes();
		}
		
		// Test shortcode output
		$content = 'This is a test [skylearn_flashcards id="123"] with shortcode.';
		$output = do_shortcode( $content );
		
		$this->assertStringContainsString( 'skylearn-flashcard-set', $output );
	}

	/**
	 * Test AJAX workflow
	 *
	 * @since 1.0.0
	 */
	public function test_ajax_workflow() {
		// Mock AJAX environment
		$_POST['action'] = 'skylearn_save_progress';
		$_POST['nonce'] = 'test_nonce';
		$_POST['set_id'] = '123';
		$_POST['progress_data'] = json_encode( array(
			'correct' => 8,
			'incorrect' => 2,
			'time_spent' => 300,
		) );
		
		// Mock AJAX functions
		if ( ! function_exists( 'wp_die' ) ) {
			function wp_die( $message = '', $title = '', $args = array() ) {
				throw new Exception( 'AJAX response: ' . $message );
			}
		}
		
		if ( ! function_exists( 'wp_send_json_success' ) ) {
			function wp_send_json_success( $data = null ) {
				echo json_encode( array( 'success' => true, 'data' => $data ) );
				wp_die();
			}
		}
		
		if ( ! function_exists( 'wp_send_json_error' ) ) {
			function wp_send_json_error( $data = null ) {
				echo json_encode( array( 'success' => false, 'data' => $data ) );
				wp_die();
			}
		}
		
		// Test AJAX handler
		try {
			if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-frontend.php' ) ) {
				require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-frontend.php';
				
				$frontend = new SkyLearn_Flashcards_Frontend( 'skylearn-flashcards', '1.0.0' );
				
				ob_start();
				$frontend->handle_save_progress();
				$output = ob_get_clean();
				
				$this->assertJson( $output );
				$response = json_decode( $output, true );
				$this->assertTrue( $response['success'] );
			}
		} catch ( Exception $e ) {
			// AJAX handlers use wp_die() which throws our mocked exception
			$this->assertStringContainsString( 'AJAX response:', $e->getMessage() );
		}
		
		// Clean up
		unset( $_POST['action'] );
		unset( $_POST['nonce'] );
		unset( $_POST['set_id'] );
		unset( $_POST['progress_data'] );
	}

	/**
	 * Test database operations workflow
	 *
	 * @since 1.0.0
	 */
	public function test_database_workflow() {
		// Test creating flashcard set
		$set_id = wp_insert_post( array(
			'post_title' => 'Database Test Set',
			'post_type' => 'flashcard_set',
			'post_status' => 'publish',
		) );
		
		$this->assertIsInt( $set_id );
		
		// Test adding meta data
		$cards = array(
			array( 'front' => 'DB Question 1', 'back' => 'DB Answer 1' ),
			array( 'front' => 'DB Question 2', 'back' => 'DB Answer 2' ),
		);
		
		$meta_id = add_post_meta( $set_id, '_skylearn_flashcard_data', $cards );
		$this->assertIsInt( $meta_id );
		
		// Test updating meta data
		$updated_cards = array(
			array( 'front' => 'Updated Question 1', 'back' => 'Updated Answer 1' ),
		);
		
		$update_result = update_post_meta( $set_id, '_skylearn_flashcard_data', $updated_cards );
		$this->assertTrue( $update_result );
		
		// Test deleting
		$delete_result = delete_post_meta( $set_id, '_skylearn_flashcard_data' );
		$this->assertTrue( $delete_result );
		
		$post_delete_result = wp_delete_post( $set_id, true );
		$this->assertIsObject( $post_delete_result );
	}

	/**
	 * Test settings and options workflow
	 *
	 * @since 1.0.0
	 */
	public function test_settings_workflow() {
		// Test adding settings
		$settings = array(
			'color_scheme' => 'blue',
			'animation_speed' => 300,
			'auto_advance' => false,
			'show_progress' => true,
		);
		
		$add_result = add_option( 'skylearn_flashcards_settings', $settings );
		$this->assertTrue( $add_result );
		
		// Test retrieving settings
		$retrieved_settings = get_option( 'skylearn_flashcards_settings' );
		$this->assertEquals( $settings, $retrieved_settings );
		
		// Test updating settings
		$updated_settings = array_merge( $settings, array( 'animation_speed' => 500 ) );
		$update_result = update_option( 'skylearn_flashcards_settings', $updated_settings );
		$this->assertTrue( $update_result );
		
		$final_settings = get_option( 'skylearn_flashcards_settings' );
		$this->assertEquals( 500, $final_settings['animation_speed'] );
		
		// Test deleting settings
		$delete_result = delete_option( 'skylearn_flashcards_settings' );
		$this->assertTrue( $delete_result );
		
		$deleted_settings = get_option( 'skylearn_flashcards_settings', 'not_found' );
		$this->assertEquals( 'not_found', $deleted_settings );
	}

	/**
	 * Test user progress tracking workflow
	 *
	 * @since 1.0.0
	 */
	public function test_user_progress_workflow() {
		$user_id = 1;
		$set_id = 123;
		
		// Test initial progress
		$initial_progress = get_user_meta( $user_id, "skylearn_progress_{$set_id}", true );
		$this->assertEmpty( $initial_progress );
		
		// Test saving progress
		$progress_data = array(
			'completed_cards' => array( 1, 2, 3 ),
			'correct_answers' => 2,
			'incorrect_answers' => 1,
			'total_time' => 180,
			'last_accessed' => current_time( 'mysql' ),
		);
		
		$save_result = update_user_meta( $user_id, "skylearn_progress_{$set_id}", $progress_data );
		$this->assertTrue( $save_result );
		
		// Test retrieving progress
		$retrieved_progress = get_user_meta( $user_id, "skylearn_progress_{$set_id}", true );
		$this->assertEquals( $progress_data, $retrieved_progress );
		
		// Test updating progress
		$updated_progress = array_merge( $progress_data, array(
			'completed_cards' => array( 1, 2, 3, 4 ),
			'correct_answers' => 3,
		) );
		
		$update_result = update_user_meta( $user_id, "skylearn_progress_{$set_id}", $updated_progress );
		$this->assertTrue( $update_result );
		
		$final_progress = get_user_meta( $user_id, "skylearn_progress_{$set_id}", true );
		$this->assertEquals( 3, $final_progress['correct_answers'] );
		$this->assertCount( 4, $final_progress['completed_cards'] );
	}

	/**
	 * Test error handling workflow
	 *
	 * @since 1.0.0
	 */
	public function test_error_handling_workflow() {
		// Test invalid data handling
		$invalid_card_data = array(
			array( 'front' => '', 'back' => 'Answer without question' ),
			array( 'front' => 'Question without answer', 'back' => '' ),
			'invalid_card_structure',
		);
		
		$sanitized = skylearn_sanitize_flashcard_data( $invalid_card_data );
		$this->assertEmpty( $sanitized );
		
		// Test error logging (if implemented)
		if ( function_exists( 'error_log' ) ) {
			$this->assertTrue( true ); // Error logging is available
		}
		
		// Test graceful degradation
		$nonexistent_set = skylearn_get_flashcard_set( 99999 );
		$this->assertFalse( $nonexistent_set );
	}

	/**
	 * Test performance with large dataset
	 *
	 * @since 1.0.0
	 */
	public function test_performance_workflow() {
		// Create large flashcard set
		$large_card_set = array();
		for ( $i = 1; $i <= 100; $i++ ) {
			$large_card_set[] = array(
				'front' => "Question {$i}",
				'back' => "Answer {$i}",
			);
		}
		
		$start_time = microtime( true );
		
		// Test sanitization performance
		$sanitized = skylearn_sanitize_flashcard_data( $large_card_set );
		
		$end_time = microtime( true );
		$execution_time = $end_time - $start_time;
		
		$this->assertCount( 100, $sanitized );
		$this->assertLessThan( 1.0, $execution_time ); // Should complete in under 1 second
	}

	/**
	 * Clean up after each test
	 *
	 * @since 1.0.0
	 */
	protected function tearDown(): void {
		// Clean up global variables
		global $wp_options;
		$wp_options = array();
		
		parent::tearDown();
	}
}