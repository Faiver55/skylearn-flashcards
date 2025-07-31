<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/uninstall
 * @author     Ferdous Khalifa <support@skyian.com>
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete all plugin data when uninstalled
 */
function skylearn_flashcards_uninstall() {
	
	// Remove plugin options
	delete_option( 'skylearn_flashcards_version' );
	delete_option( 'skylearn_flashcards_settings' );
	delete_option( 'skylearn_flashcards_activation_redirect' );
	delete_option( 'skylearn_flashcards_premium_license' );
	
	// Remove any transients we've left behind
	delete_transient( 'skylearn_flashcards_admin_notice' );
	delete_transient( 'skylearn_flashcards_version_check' );
	
	// Remove user meta data
	delete_metadata( 'user', 0, 'skylearn_flashcards_progress', '', true );
	delete_metadata( 'user', 0, 'skylearn_flashcards_preferences', '', true );
	
	// Remove custom post meta
	delete_post_meta_by_key( '_skylearn_flashcard_data' );
	delete_post_meta_by_key( '_skylearn_flashcard_settings' );
	delete_post_meta_by_key( '_skylearn_flashcard_analytics' );
	
	// Get all flashcard posts and delete them
	$flashcard_posts = get_posts( array(
		'post_type'      => 'flashcard_set',
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids'
	) );
	
	foreach ( $flashcard_posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	
	// Remove custom taxonomy terms
	$taxonomies = array( 'flashcard_category', 'flashcard_tag' );
	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids'
		) );
		
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term_id ) {
				wp_delete_term( $term_id, $taxonomy );
			}
		}
	}
	
	// Remove custom database tables if they exist
	global $wpdb;
	
	$table_flashcard_analytics = $wpdb->prefix . 'skylearn_flashcard_analytics';
	$table_flashcard_progress = $wpdb->prefix . 'skylearn_flashcard_progress';
	$table_flashcard_leads = $wpdb->prefix . 'skylearn_flashcard_leads';
	
	$wpdb->query( "DROP TABLE IF EXISTS {$table_flashcard_analytics}" );
	$wpdb->query( "DROP TABLE IF EXISTS {$table_flashcard_progress}" );
	$wpdb->query( "DROP TABLE IF EXISTS {$table_flashcard_leads}" );
	
	// Clear any cached data that has been removed
	wp_cache_flush();
	
	// Note: No longer removing custom capabilities as they are not used
	
	// For multisite installations
	if ( is_multisite() ) {
		// Remove network-wide options if this is a network activation
		delete_site_option( 'skylearn_flashcards_network_settings' );
		delete_site_option( 'skylearn_flashcards_network_license' );
	}
}

// Execute the uninstall function
skylearn_flashcards_uninstall();