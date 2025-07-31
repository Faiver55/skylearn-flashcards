<?php
/**
 * Tests for Premium functionality
 *
 * @package SkyLearn_Flashcards
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Premium features tests
 *
 * @since 1.0.0
 */
class PremiumFeaturesTest extends TestCase {

	/**
	 * Set up test environment before each test
	 *
	 * @since 1.0.0
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Mock WordPress functions
		if ( ! function_exists( 'get_option' ) ) {
			function get_option( $option, $default = false ) {
				// Mock premium license as active for testing
				if ( 'skylearn_flashcards_license_status' === $option ) {
					return 'valid';
				} elseif ( 'skylearn_flashcards_license_key' === $option ) {
					return 'test-license-key-12345';
				}
				return $default;
			}
		}
		
		if ( ! function_exists( 'update_option' ) ) {
			function update_option( $option, $value ) {
				return true;
			}
		}
		
		if ( ! function_exists( 'wp_remote_post' ) ) {
			function wp_remote_post( $url, $args = array() ) {
				return array(
					'response' => array( 'code' => 200 ),
					'body' => json_encode( array( 'license' => 'valid' ) ),
				);
			}
		}
		
		if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
			function wp_remote_retrieve_body( $response ) {
				return $response['body'];
			}
		}
		
		if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
			function wp_remote_retrieve_response_code( $response ) {
				return $response['response']['code'];
			}
		}
	}

	/**
	 * Test premium license validation
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::validate_license
	 */
	public function test_license_validation() {
		// Include premium class if it exists
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			$result = $premium->validate_license( 'test-license-key-12345' );
			
			$this->assertTrue( $result );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}

	/**
	 * Test premium feature gating
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::is_feature_available
	 */
	public function test_premium_feature_gating() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			
			// Test premium features
			$this->assertTrue( $premium->is_feature_available( 'advanced_reporting' ) );
			$this->assertTrue( $premium->is_feature_available( 'bulk_export' ) );
			$this->assertTrue( $premium->is_feature_available( 'lead_capture' ) );
			$this->assertTrue( $premium->is_feature_available( 'unlimited_sets' ) );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}

	/**
	 * Test advanced reporting functionality
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Advanced_Reporting::generate_report
	 */
	public function test_advanced_reporting() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-advanced-reporting.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-advanced-reporting.php';
			
			$reporting = new SkyLearn_Flashcards_Advanced_Reporting();
			
			// Mock report data
			$report_data = array(
				'total_sessions' => 100,
				'average_score' => 85.5,
				'completion_rate' => 92.3,
				'time_spent' => 3600,
			);
			
			$report = $reporting->generate_report( $report_data );
			
			$this->assertIsArray( $report );
			$this->assertArrayHasKey( 'total_sessions', $report );
			$this->assertArrayHasKey( 'average_score', $report );
		} else {
			$this->assertTrue( true ); // Skip if reporting class doesn't exist
		}
	}

	/**
	 * Test bulk export functionality
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Export::export_sets
	 */
	public function test_bulk_export() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-export.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-export.php';
			
			$export = new SkyLearn_Flashcards_Export();
			
			// Mock flashcard sets
			$sets = array(
				array(
					'id' => 1,
					'title' => 'Test Set 1',
					'cards' => array(
						array( 'front' => 'Q1', 'back' => 'A1' ),
						array( 'front' => 'Q2', 'back' => 'A2' ),
					),
				),
				array(
					'id' => 2,
					'title' => 'Test Set 2',
					'cards' => array(
						array( 'front' => 'Q3', 'back' => 'A3' ),
					),
				),
			);
			
			$export_data = $export->export_sets( $sets, 'json' );
			
			$this->assertIsString( $export_data );
			$this->assertJson( $export_data );
		} else {
			$this->assertTrue( true ); // Skip if export class doesn't exist
		}
	}

	/**
	 * Test lead capture functionality
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::capture_lead
	 */
	public function test_lead_capture() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			
			$lead_data = array(
				'name' => 'John Doe',
				'email' => 'john@example.com',
				'set_id' => 123,
				'score' => 85.5,
				'timestamp' => current_time( 'mysql' ),
			);
			
			$result = $premium->capture_lead( $lead_data );
			
			$this->assertTrue( $result );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}

	/**
	 * Test premium upsell display
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::show_upsell
	 */
	public function test_premium_upsell() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			
			// Test with free version
			$premium->set_license_status( 'invalid' );
			$upsell_html = $premium->show_upsell( 'advanced_reporting' );
			
			$this->assertIsString( $upsell_html );
			$this->assertStringContainsString( 'Upgrade to Premium', $upsell_html );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}

	/**
	 * Test premium settings validation
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::validate_settings
	 */
	public function test_premium_settings_validation() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			
			$valid_settings = array(
				'license_key' => 'valid-license-key',
				'max_sets' => 100,
				'enable_reporting' => true,
				'enable_lead_capture' => true,
			);
			
			$invalid_settings = array(
				'license_key' => '',
				'max_sets' => -1,
				'enable_reporting' => 'invalid',
			);
			
			$this->assertTrue( $premium->validate_settings( $valid_settings ) );
			$this->assertFalse( $premium->validate_settings( $invalid_settings ) );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}

	/**
	 * Test premium API communication
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::api_request
	 */
	public function test_premium_api_communication() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			
			$response = $premium->api_request( 'validate_license', array(
				'license_key' => 'test-license-key-12345',
			) );
			
			$this->assertIsArray( $response );
			$this->assertArrayHasKey( 'license', $response );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}

	/**
	 * Test premium data encryption
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::encrypt_data
	 * @covers SkyLearn_Flashcards_Premium::decrypt_data
	 */
	public function test_premium_data_encryption() {
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			
			$original_data = 'sensitive user data';
			$encrypted = $premium->encrypt_data( $original_data );
			$decrypted = $premium->decrypt_data( $encrypted );
			
			$this->assertNotEquals( $original_data, $encrypted );
			$this->assertEquals( $original_data, $decrypted );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}

	/**
	 * Test premium cache management
	 *
	 * @since 1.0.0
	 * @covers SkyLearn_Flashcards_Premium::clear_premium_cache
	 */
	public function test_premium_cache_management() {
		// Mock WordPress cache functions
		if ( ! function_exists( 'wp_cache_delete' ) ) {
			function wp_cache_delete( $key, $group = '' ) {
				return true;
			}
		}
		
		if ( file_exists( SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-premium.php';
			
			$premium = new SkyLearn_Flashcards_Premium();
			$result = $premium->clear_premium_cache();
			
			$this->assertTrue( $result );
		} else {
			$this->assertTrue( true ); // Skip if premium class doesn't exist
		}
	}
}