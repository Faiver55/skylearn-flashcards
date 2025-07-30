<?php
/**
 * Mailchimp integration for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/integrations
 */

/**
 * Mailchimp integration class.
 *
 * Handles lead submission to Mailchimp via API.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/integrations
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Mailchimp {

	/**
	 * Add subscriber to Mailchimp list
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
		$double_optin = $settings['double_optin'] ?? true;
		
		if ( empty( $api_key ) || empty( $list_id ) ) {
			return false;
		}
		
		// Extract datacenter from API key
		list( $key, $datacenter ) = explode( '-', $api_key );
		
		if ( empty( $datacenter ) ) {
			return false;
		}
		
		$url = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$list_id}/members";
		
		$data = array(
			'email_address' => $email,
			'status'        => $double_optin ? 'pending' : 'subscribed',
			'merge_fields'  => array(
				'FNAME' => $this->get_first_name( $name ),
				'LNAME' => $this->get_last_name( $name ),
			),
			'tags'          => array( 'SkyLearn Flashcards' ),
		);
		
		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
				'Content-Type'  => 'application/json',
			),
			'body' => json_encode( $data ),
			'timeout' => 30,
		) );
		
		if ( is_wp_error( $response ) ) {
			skylearn_log( 'Mailchimp API Error: ' . $response->get_error_message(), 'error' );
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		
		if ( $response_code === 200 || $response_code === 201 ) {
			skylearn_log( 'Successfully added subscriber to Mailchimp: ' . $email );
			return true;
		} else {
			skylearn_log( 'Mailchimp API Error: ' . $response_body, 'error' );
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
		
		// Extract datacenter from API key
		list( $key, $datacenter ) = explode( '-', $api_key );
		
		if ( empty( $datacenter ) ) {
			return false;
		}
		
		$url = "https://{$datacenter}.api.mailchimp.com/3.0/ping";
		
		$response = wp_remote_get( $url, array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
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
		
		// Extract datacenter from API key
		list( $key, $datacenter ) = explode( '-', $api_key );
		
		if ( empty( $datacenter ) ) {
			return array();
		}
		
		$url = "https://{$datacenter}.api.mailchimp.com/3.0/lists";
		
		$response = wp_remote_get( $url, array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
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
		if ( isset( $data['lists'] ) && is_array( $data['lists'] ) ) {
			foreach ( $data['lists'] as $list ) {
				$lists[] = array(
					'id'   => $list['id'],
					'name' => $list['name'],
				);
			}
		}
		
		return $lists;
		
	}

}