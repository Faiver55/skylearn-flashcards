<?php
/**
 * Uninstall script for SkyLearn Flashcards
 *
 * This file is executed when the plugin is deleted from WordPress admin.
 * It cleans up all plugin data, settings, and custom database tables.
 *
 * @package SkyLearn_Flashcards
 * @author Ferdous Khalifa
 * @copyright 2024 Skyian LLC
 * @license GPLv3
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall
 *
 * @since 1.0.0
 */
class SkyLearn_Flashcards_Uninstaller {

	/**
	 * Run the uninstall process
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		// Delete plugin options
		self::delete_options();

		// Delete custom post types and meta
		self::delete_posts_and_meta();

		// Delete user meta
		self::delete_user_meta();

		// Delete custom database tables
		self::delete_custom_tables();

		// Clear any cached data
		self::clear_cache();

		// Delete uploaded files (if any)
		self::delete_uploaded_files();
	}

	/**
	 * Delete plugin options
	 *
	 * @since 1.0.0
	 */
	private static function delete_options() {
		$options_to_delete = array(
			'skylearn_flashcards_settings',
			'skylearn_flashcards_version',
			'skylearn_flashcards_premium_license',
			'skylearn_flashcards_lead_settings',
			'skylearn_flashcards_lms_settings',
			'skylearn_flashcards_export_settings',
		);

		foreach ( $options_to_delete as $option ) {
			delete_option( $option );
		}

		// Delete site options for multisite
		if ( is_multisite() ) {
			foreach ( $options_to_delete as $option ) {
				delete_site_option( $option );
			}
		}
	}

	/**
	 * Delete custom post types and associated meta
	 *
	 * @since 1.0.0
	 */
	private static function delete_posts_and_meta() {
		global $wpdb;

		// Delete flashcard sets and their meta
		$post_types = array( 'flashcard_set' );

		foreach ( $post_types as $post_type ) {
			$posts = get_posts( array(
				'post_type'   => $post_type,
				'numberposts' => -1,
				'post_status' => 'any',
			) );

			foreach ( $posts as $post ) {
				// Delete post meta
				$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $post->ID ) );
				
				// Delete the post
				wp_delete_post( $post->ID, true );
			}
		}
	}

	/**
	 * Delete user meta related to the plugin
	 *
	 * @since 1.0.0
	 */
	private static function delete_user_meta() {
		global $wpdb;

		$user_meta_keys = array(
			'skylearn_flashcards_progress',
			'skylearn_flashcards_preferences',
			'skylearn_flashcards_performance',
		);

		foreach ( $user_meta_keys as $meta_key ) {
			$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => $meta_key ) );
		}
	}

	/**
	 * Delete custom database tables
	 *
	 * @since 1.0.0
	 */
	private static function delete_custom_tables() {
		global $wpdb;

		// Define custom table names
		$tables = array(
			$wpdb->prefix . 'skylearn_flashcard_sessions',
			$wpdb->prefix . 'skylearn_flashcard_leads',
			$wpdb->prefix . 'skylearn_flashcard_analytics',
		);

		// Drop each table
		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}
	}

	/**
	 * Clear any cached data
	 *
	 * @since 1.0.0
	 */
	private static function clear_cache() {
		// Clear WordPress object cache
		wp_cache_flush();

		// Clear any plugin-specific transients
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_skylearn_flashcards_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_skylearn_flashcards_%'" );
	}

	/**
	 * Delete uploaded files
	 *
	 * @since 1.0.0
	 */
	private static function delete_uploaded_files() {
		$upload_dir = wp_upload_dir();
		$plugin_upload_dir = $upload_dir['basedir'] . '/skylearn-flashcards/';

		if ( is_dir( $plugin_upload_dir ) ) {
			self::delete_directory( $plugin_upload_dir );
		}
	}

	/**
	 * Recursively delete a directory and its contents
	 *
	 * @since 1.0.0
	 * @param string $dir Directory path to delete
	 */
	private static function delete_directory( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			
			if ( is_dir( $path ) ) {
				self::delete_directory( $path );
			} else {
				unlink( $path );
			}
		}
		
		rmdir( $dir );
	}
}

// Run the uninstall process
SkyLearn_Flashcards_Uninstaller::uninstall();