<?php
/**
 * SendFox integration for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/integrations
 */

/**
 * SendFox integration class.
 *
 * Handles lead submission to SendFox via API.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/integrations
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_SendFox {

	/**
	 * Add subscriber to SendFox
	 *
	 * @since    1.0.0
	 * @param    string   $email      Email address
	 * @param    string   $name       Name
	 * @param    array    $settings   Email service settings
	 * @return   bool                 True on success, false on failure
	 */
	public function add_subscriber( $email, $name, $settings ) {
		
		$api_key = $settings['api_key'] ?? '';
		$list_id = $settings['list_id'] ?? '';
		
		if ( empty( $api_key ) || empty( $email ) ) {
			return false;
		}
		
		$url = 'https://api.sendfox.com/contacts';
		
		$data = array(
			'email'      => $email,
			'first_name' => $this->get_first_name( $name ),
			'last_name'  => $this->get_last_name( $name ),
		);
		
		// Add to specific list if provided
		if ( ! empty( $list_id ) ) {
			$data['lists'] = array( (int) $list_id );
		}
		
		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => json_encode( $data ),
			'timeout' => 30,
		) );
		
		if ( is_wp_error( $response ) ) {
			skylearn_log( 'SendFox API Error: ' . $response->get_error_message(), 'error' );
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		
		if ( $response_code === 200 || $response_code === 201 ) {
			skylearn_log( 'Successfully added subscriber to SendFox: ' . $email );
			return true;
		} else {
			skylearn_log( 'SendFox API Error: ' . $response_body, 'error' );
			return false;
		}
		
	}

	/**
	 * Get first name from full name
	 *
	 * @since    1.0.0
	 * @param    string   $name   Full name
	 * @return   string           First name
	 */
	private function get_first_name( $name ) {
		
		$parts = explode( ' ', trim( $name ) );
		return $parts[0] ?? '';
		
	}

	/**
	 * Get last name from full name
	 *
	 * @since    1.0.0
	 * @param    string   $name   Full name
	 * @return   string           Last name
	 */
	private function get_last_name( $name ) {
		
		$parts = explode( ' ', trim( $name ) );
		if ( count( $parts ) > 1 ) {
			array_shift( $parts ); // Remove first name
			return implode( ' ', $parts );
		}
		return '';
		
	}

	/**
	 * Test API connection
	 *
	 * @since    1.0.0
	 * @param    string   $api_key   API key
	 * @return   bool                True if connection successful, false otherwise
	 */
	public static function test_connection( $api_key ) {
		
		if ( empty( $api_key ) ) {
			return false;
		}
		
		$url = 'https://api.sendfox.com/me';
		
		$response = wp_remote_get( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
			),
			'timeout' => 15,
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		return $response_code === 200;
		
	}

	/**
	 * Get available lists
	 *
	 * @since    1.0.0
	 * @param    string   $api_key   API key
	 * @return   array               Array of lists
	 */
	public static function get_lists( $api_key ) {
		
		if ( empty( $api_key ) ) {
			return array();
		}
		
		$url = 'https://api.sendfox.com/lists';
		
		$response = wp_remote_get( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
			),
			'timeout' => 15,
		) );
		
		if ( is_wp_error( $response ) ) {
			return array();
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			return array();
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		$lists = array();
		if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
			foreach ( $data['data'] as $list ) {
				$lists[] = array(
					'id'   => $list['id'],
					'name' => $list['name'],
				);
			}
		}
		
		return $lists;
		
	}

}