<?php
/**
 * Vbout integration for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/integrations
 */

/**
 * Vbout integration class.
 *
 * Handles lead submission to Vbout via API.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/integrations
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Vbout {

	/**
	 * Add subscriber to Vbout
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
		
		$url = 'https://api.vbout.com/1/contact/add.json';
		
		$data = array(
			'key'        => $api_key,
			'email'      => $email,
			'first_name' => $this->get_first_name( $name ),
			'last_name'  => $this->get_last_name( $name ),
			'status'     => 'active',
			'source'     => 'SkyLearn Flashcards',
		);
		
		// Add to specific list if provided
		if ( ! empty( $list_id ) ) {
			$data['listid'] = $list_id;
		}
		
		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			'body'    => http_build_query( $data ),
			'timeout' => 30,
		) );
		
		if ( is_wp_error( $response ) ) {
			skylearn_log( 'Vbout API Error: ' . $response->get_error_message(), 'error' );
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$decoded_response = json_decode( $response_body, true );
		
		if ( $response_code === 200 && isset( $decoded_response['data'] ) ) {
			skylearn_log( 'Successfully added subscriber to Vbout: ' . $email );
			return true;
		} else {
			$error_message = isset( $decoded_response['error'] ) ? $decoded_response['error'] : $response_body;
			skylearn_log( 'Vbout API Error: ' . $error_message, 'error' );
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
		
		$url = 'https://api.vbout.com/1/contact/getlists.json';
		
		$response = wp_remote_get( add_query_arg( 'key', $api_key, $url ), array(
			'timeout' => 15,
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$decoded_response = json_decode( $response_body, true );
		
		return $response_code === 200 && isset( $decoded_response['data'] );
		
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
		
		$url = 'https://api.vbout.com/1/contact/getlists.json';
		
		$response = wp_remote_get( add_query_arg( 'key', $api_key, $url ), array(
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