<?php
/**
 * Admin capability management functionality
 *
 * Ensures that administrators always have the required capabilities
 * to access SkyLearn Flashcards admin pages.
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Ensure administrators always have the required SkyLearn Flashcards capabilities.
 *
 * This function ensures that the administrator role always has all the
 * required capabilities to access the plugin's admin pages and menu items.
 * This provides a safety net in case capabilities are removed or not properly 
 * set during activation, and ensures capabilities are present on every admin load.
 *
 * @since 1.0.0
 */
function skylearn_flashcards_ensure_admin_caps() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}

	// Core flashcard capabilities
	$capabilities = array(
		'manage_skylearn_flashcards',
		'edit_skylearn_flashcards',
		'delete_skylearn_flashcards',
		'read_skylearn_flashcards',
		'view_skylearn_analytics',
		'export_skylearn_flashcards',
		'manage_skylearn_leads',
	);

	// Add any missing capabilities
	foreach ( $capabilities as $capability ) {
		if ( ! $role->has_cap( $capability ) ) {
			$role->add_cap( $capability );
		}
	}
}

// Hook into admin_init to ensure capabilities are always present
add_action( 'admin_init', 'skylearn_flashcards_ensure_admin_caps' );