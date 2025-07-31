<?php
/**
 * Tests for SkyLearn Flashcards helper functions
 *
 * @package SkyLearn_Flashcards
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Helper functions tests
 *
 * @since 1.0.0
 */
class HelperFunctionsTest extends TestCase {

	/**
	 * Set up test environment before each test
	 *
	 * @since 1.0.0
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Mock WordPress functions that may not be available
		if ( ! function_exists( 'wp_kses_post' ) ) {
			function wp_kses_post( $data ) {
				return strip_tags( $data, '<p><br><strong><em><ul><ol><li>' );
			}
		}
		
		if ( ! function_exists( 'get_post' ) ) {
			function get_post( $post_id ) {
				// Mock post object for testing
				return (object) array(
					'ID' => $post_id,
					'post_type' => 'flashcard_set',
					'post_title' => 'Test Flashcard Set',
					'post_content' => 'Test content',
				);
			}
		}
		
		if ( ! function_exists( 'get_post_meta' ) ) {
			function get_post_meta( $post_id, $key, $single = false ) {
				// Mock meta data for testing
				if ( '_skylearn_flashcard_data' === $key ) {
					return array(
						array( 'front' => 'Question 1', 'back' => 'Answer 1' ),
						array( 'front' => 'Question 2', 'back' => 'Answer 2' ),
					);
				}
				return '';
			}
		}
	}

	/**
	 * Test flashcard data sanitization
	 *
	 * @since 1.0.0
	 * @covers skylearn_sanitize_flashcard_data
	 */
	public function test_sanitize_flashcard_data() {
		// Test valid data
		$valid_data = array(
			array( 'front' => 'Question 1', 'back' => 'Answer 1' ),
			array( 'front' => 'Question 2', 'back' => 'Answer 2' ),
		);
		
		$sanitized = skylearn_sanitize_flashcard_data( $valid_data );
		
		$this->assertIsArray( $sanitized );
		$this->assertCount( 2, $sanitized );
		$this->assertEquals( 'Question 1', $sanitized[0]['front'] );
		$this->assertEquals( 'Answer 1', $sanitized[0]['back'] );
	}

	/**
	 * Test flashcard data sanitization with invalid data
	 *
	 * @since 1.0.0
	 * @covers skylearn_sanitize_flashcard_data
	 */
	public function test_sanitize_flashcard_data_invalid() {
		// Test with non-array input
		$result = skylearn_sanitize_flashcard_data( 'not an array' );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
		
		// Test with empty cards
		$empty_data = array(
			array( 'front' => '', 'back' => 'Answer' ),
			array( 'front' => 'Question', 'back' => '' ),
		);
		
		$sanitized = skylearn_sanitize_flashcard_data( $empty_data );
		$this->assertEmpty( $sanitized );
	}

	/**
	 * Test flashcard data sanitization with HTML content
	 *
	 * @since 1.0.0
	 * @covers skylearn_sanitize_flashcard_data
	 */
	public function test_sanitize_flashcard_data_with_html() {
		$html_data = array(
			array(
				'front' => '<p>Question with <strong>HTML</strong></p>',
				'back' => '<p>Answer with <em>formatting</em></p>',
			),
		);
		
		$sanitized = skylearn_sanitize_flashcard_data( $html_data );
		
		$this->assertCount( 1, $sanitized );
		$this->assertStringContainsString( '<strong>HTML</strong>', $sanitized[0]['front'] );
		$this->assertStringContainsString( '<em>formatting</em>', $sanitized[0]['back'] );
	}

	/**
	 * Test getting brand colors
	 *
	 * @since 1.0.0
	 * @covers skylearn_get_brand_colors
	 */
	public function test_get_brand_colors() {
		$colors = skylearn_get_brand_colors();
		
		$this->assertIsArray( $colors );
		$this->assertArrayHasKey( 'primary', $colors );
		$this->assertArrayHasKey( 'accent', $colors );
		$this->assertArrayHasKey( 'background', $colors );
		$this->assertArrayHasKey( 'text', $colors );
		
		// Test color values are valid hex colors
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', $colors['primary'] );
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', $colors['accent'] );
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', $colors['background'] );
		$this->assertMatchesRegularExpression( '/^#[a-fA-F0-9]{6}$/', $colors['text'] );
	}

	/**
	 * Test getting logo URLs
	 *
	 * @since 1.0.0
	 * @covers skylearn_get_logo_url
	 */
	public function test_get_logo_url() {
		// Test horizontal logo (default)
		$horizontal_url = skylearn_get_logo_url();
		$this->assertStringContainsString( 'logo-horiz.png', $horizontal_url );
		
		// Test horizontal logo (explicit)
		$horizontal_url_explicit = skylearn_get_logo_url( 'horizontal' );
		$this->assertStringContainsString( 'logo-horiz.png', $horizontal_url_explicit );
		
		// Test icon logo
		$icon_url = skylearn_get_logo_url( 'icon' );
		$this->assertStringContainsString( 'logo-icon.png', $icon_url );
		
		// Test invalid type defaults to horizontal
		$invalid_url = skylearn_get_logo_url( 'invalid' );
		$this->assertStringContainsString( 'logo-horiz.png', $invalid_url );
	}

	/**
	 * Test user capability checking functions
	 *
	 * @since 1.0.0
	 * @covers skylearn_current_user_can_manage
	 * @covers skylearn_current_user_can_edit
	 */
	public function test_user_capability_functions() {
		// These will return true in our test environment
		$this->assertTrue( skylearn_current_user_can_manage() );
		$this->assertTrue( skylearn_current_user_can_edit() );
	}

	/**
	 * Test getting flashcard set data
	 *
	 * @since 1.0.0
	 * @covers skylearn_get_flashcard_set
	 */
	public function test_get_flashcard_set() {
		$set_data = skylearn_get_flashcard_set( 123 );
		
		$this->assertIsArray( $set_data );
		$this->assertArrayHasKey( 'id', $set_data );
		$this->assertArrayHasKey( 'title', $set_data );
		$this->assertArrayHasKey( 'cards', $set_data );
		$this->assertEquals( 123, $set_data['id'] );
		$this->assertEquals( 'Test Flashcard Set', $set_data['title'] );
		$this->assertIsArray( $set_data['cards'] );
		$this->assertCount( 2, $set_data['cards'] );
	}

	/**
	 * Test data validation functions
	 *
	 * @since 1.0.0
	 */
	public function test_data_validation() {
		// Test that empty or whitespace-only content is handled properly
		$whitespace_data = array(
			array( 'front' => '   ', 'back' => 'Valid answer' ),
			array( 'front' => 'Valid question', 'back' => "\n\t  " ),
		);
		
		$sanitized = skylearn_sanitize_flashcard_data( $whitespace_data );
		$this->assertEmpty( $sanitized );
	}

	/**
	 * Test that missing array keys are handled gracefully
	 *
	 * @since 1.0.0
	 */
	public function test_missing_keys_handling() {
		$incomplete_data = array(
			array( 'front' => 'Question without back' ),
			array( 'back' => 'Answer without front' ),
			array(), // Empty card
		);
		
		$sanitized = skylearn_sanitize_flashcard_data( $incomplete_data );
		$this->assertEmpty( $sanitized );
	}
}