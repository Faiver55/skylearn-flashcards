<?php
/**
 * The shortcode functionality of the plugin
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend
 */

/**
 * The shortcode functionality of the plugin.
 *
 * Handles the rendering of flashcard shortcodes and blocks.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Shortcode {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	
	/**
	 * Render flashcard shortcode
	 *
	 * @since    1.0.0
	 * @param    array     $atts    Shortcode attributes
	 * @param    string    $content Shortcode content
	 * @return   string             Rendered HTML
	 */
	public function render_shortcode( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
			'id'            => 0,
			'theme'         => 'default',
			'shuffle'       => 'false',
			'show_progress' => 'true',
			'autoplay'      => 'false',
			'height'        => 'auto',
		), $atts, 'skylearn_flashcard_set' );
		
		$set_id = absint( $atts['id'] );
		
		if ( ! $set_id ) {
			return $this->render_error( __( 'Please specify a valid flashcard set ID.', 'skylearn-flashcards' ) );
		}
		
		$flashcard_set = skylearn_get_flashcard_set( $set_id );
		
		if ( ! $flashcard_set ) {
			return $this->render_error( __( 'Flashcard set not found.', 'skylearn-flashcards' ) );
		}
		
		if ( empty( $flashcard_set['cards'] ) ) {
			return $this->render_error( __( 'This flashcard set has no cards.', 'skylearn-flashcards' ) );
		}
		
		// Check if post is published (unless user can edit)
		if ( $flashcard_set['status'] !== 'publish' && ! skylearn_current_user_can_edit_post( $set_id, 'flashcard_set' ) ) {
			return $this->render_error( __( 'This flashcard set is not available.', 'skylearn-flashcards' ) );
		}
		
		// Check LMS access restrictions
		if ( class_exists( 'SkyLearn_Flashcards_LMS_Manager' ) ) {
			$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
			if ( ! $lms_manager->user_has_access( $set_id ) ) {
				return $this->render_error( __( 'You do not have access to this flashcard set. Please check your course enrollment.', 'skylearn-flashcards' ) );
			}
		}
		
		// Enqueue necessary scripts and styles
		$this->enqueue_frontend_assets();
		
		// Get set settings
		$set_settings = get_post_meta( $set_id, '_skylearn_set_settings', true );
		if ( ! is_array( $set_settings ) ) {
			$set_settings = array();
		}
		
		// Merge shortcode attributes with set settings
		$final_settings = wp_parse_args( $atts, array(
			'shuffle'       => $set_settings['shuffle_default'] ? 'true' : 'false',
			'show_progress' => $set_settings['show_progress'] ? 'true' : 'false',
			'autoplay'      => $set_settings['autoplay'] ? 'true' : 'false',
			'autoplay_delay' => $set_settings['autoplay_delay'] ?? 3,
		) );
		
		// Generate unique container ID
		$container_id = 'skylearn-flashcards-' . $set_id . '-' . wp_rand( 1000, 9999 );
		
		// Prepare data for template
		$template_data = array(
			'set_id'       => $set_id,
			'container_id' => $container_id,
			'flashcard_set' => $flashcard_set,
			'settings'     => $final_settings,
			'theme'        => sanitize_html_class( $atts['theme'] ),
		);
		
		return $this->render_template( 'flashcard-set', $template_data );
	}
	
	/**
	 * Enqueue frontend assets
	 *
	 * @since    1.0.0
	 */
	private function enqueue_frontend_assets() {
		// Only enqueue if not already enqueued
		if ( ! wp_script_is( $this->plugin_name . '-flashcard', 'enqueued' ) ) {
			wp_enqueue_script( 
				$this->plugin_name . '-flashcard', 
				SKYLEARN_FLASHCARDS_ASSETS . 'js/flashcard.js', 
				array( 'jquery' ), 
				$this->version, 
				true 
			);
			
			// Localize script
			wp_localize_script( $this->plugin_name . '-flashcard', 'skyleanFlashcards', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'skylearn_frontend_nonce' ),
				'strings'  => array(
					'flip_card'        => __( 'Click to flip', 'skylearn-flashcards' ),
					'next_card'        => __( 'Next Card', 'skylearn-flashcards' ),
					'prev_card'        => __( 'Previous Card', 'skylearn-flashcards' ),
					'correct'          => __( 'Correct', 'skylearn-flashcards' ),
					'incorrect'        => __( 'Incorrect', 'skylearn-flashcards' ),
					'shuffle'          => __( 'Shuffle', 'skylearn-flashcards' ),
					'reset'            => __( 'Reset', 'skylearn-flashcards' ),
					'study_again'      => __( 'Study Again', 'skylearn-flashcards' ),
					'session_complete' => __( 'Study session complete!', 'skylearn-flashcards' ),
					'loading'          => __( 'Loading...', 'skylearn-flashcards' ),
					'error'            => __( 'An error occurred. Please try again.', 'skylearn-flashcards' ),
				),
			) );
		}
		
		if ( ! wp_style_is( $this->plugin_name . '-frontend', 'enqueued' ) ) {
			wp_enqueue_style( 
				$this->plugin_name . '-frontend', 
				SKYLEARN_FLASHCARDS_ASSETS . 'css/frontend.css', 
				array(), 
				$this->version 
			);
		}
	}
	
	/**
	 * Render template
	 *
	 * @since    1.0.0
	 * @param    string    $template_name    Template name
	 * @param    array     $data            Template data
	 * @return   string                     Rendered HTML
	 */
	private function render_template( $template_name, $data = array() ) {
		ob_start();
		
		// Extract data to variables
		if ( ! empty( $data ) && is_array( $data ) ) {
			extract( $data );
		}
		
		$template_file = SKYLEARN_FLASHCARDS_PATH . "includes/frontend/views/{$template_name}.php";
		
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			echo $this->render_error( sprintf( __( 'Template not found: %s', 'skylearn-flashcards' ), $template_name ) );
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Render error message
	 *
	 * @since    1.0.0
	 * @param    string    $message    Error message
	 * @return   string                Error HTML
	 */
	private function render_error( $message ) {
		if ( skylearn_current_user_can_edit() ) {
			return sprintf(
				'<div class="skylearn-flashcard-error"><p><strong>SkyLearn Flashcards:</strong> %s</p></div>',
				esc_html( $message )
			);
		}
		
		return ''; // Don't show errors to regular users
	}
}
