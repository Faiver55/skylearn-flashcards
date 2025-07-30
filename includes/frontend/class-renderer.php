<?php
/**
 * The flashcard rendering functionality
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend
 */

/**
 * The flashcard renderer class.
 *
 * Handles rendering of flashcard sets and individual cards for frontend display.
 *
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Renderer {

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
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Render a complete flashcard set
	 *
	 * @since 1.0.0
	 * @param int   $set_id Flashcard set ID
	 * @param array $args Rendering arguments
	 * @return string Rendered HTML
	 */
	public function render_set( $set_id, $args = array() ) {
		$defaults = array(
			'show_progress'  => true,
			'show_controls'  => true,
			'auto_advance'   => false,
			'shuffle_cards'  => false,
			'cards_per_page' => -1,
			'theme'          => 'default',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		// Get set data
		$set_data = $this->get_set_data( $set_id );
		if ( ! $set_data ) {
			return $this->render_error( __( 'Flashcard set not found.', 'skylearn-flashcards' ) );
		}
		
		// Get cards
		$cards = $this->get_set_cards( $set_id, $args );
		if ( empty( $cards ) ) {
			return $this->render_empty_set( $set_data );
		}
		
		// Get settings and colors
		$settings = $this->prepare_settings( $args );
		$colors = skylearn_get_brand_colors();
		
		// Start output buffering
		ob_start();
		
		// Include the set template
		$set_id = $set_id;
		$set_data = $set_data;
		$cards = $cards;
		$settings = $settings;
		$colors = $colors;
		
		include $this->get_template_path( 'flashcard-set.php' );
		
		return ob_get_clean();
	}

	/**
	 * Render a single flashcard
	 *
	 * @since 1.0.0
	 * @param array $card Card data
	 * @param int   $index Card index
	 * @param array $args Rendering arguments
	 * @return string Rendered HTML
	 */
	public function render_card( $card, $index = 0, $args = array() ) {
		$defaults = array(
			'show_actions'   => true,
			'show_rating'    => true,
			'show_hints'     => true,
			'theme'          => 'default',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		// Start output buffering
		ob_start();
		
		// Include the card template
		$card = $card;
		$index = $index;
		$args = $args;
		
		include $this->get_template_path( 'flashcard-card.php' );
		
		return ob_get_clean();
	}

	/**
	 * Render study results
	 *
	 * @since 1.0.0
	 * @param array $results Study session results
	 * @param array $set_data Flashcard set data
	 * @param array $args Rendering arguments
	 * @return string Rendered HTML
	 */
	public function render_results( $results, $set_data, $args = array() ) {
		$defaults = array(
			'show_stats'         => true,
			'show_recommendations' => true,
			'show_actions'       => true,
			'show_lead_capture'  => false,
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		// Calculate statistics
		$stats = $this->calculate_stats( $results );
		
		// Start output buffering
		ob_start();
		
		// Include the results template
		$results = $results;
		$set_data = $set_data;
		$stats = $stats;
		$args = $args;
		
		include $this->get_template_path( 'flashcard-results.php' );
		
		return ob_get_clean();
	}

	/**
	 * Render lead capture form
	 *
	 * @since 1.0.0
	 * @param string $context Context for lead capture
	 * @param int    $set_id Flashcard set ID
	 * @param array  $args Rendering arguments
	 * @return string Rendered HTML
	 */
	public function render_lead_capture( $context = 'completion', $set_id = 0, $args = array() ) {
		// Check if lead collection is enabled
		if ( ! skylearn_is_premium() ) {
			return '';
		}
		
		$defaults = array(
			'form_title'       => __( 'Get Your Study Results!', 'skylearn-flashcards' ),
			'form_description' => __( 'Enter your email to receive detailed performance analytics.', 'skylearn-flashcards' ),
			'collect_name'     => true,
			'name_required'    => false,
		);
		
		$settings = wp_parse_args( $args, $defaults );
		
		// Start output buffering
		ob_start();
		
		// Include the lead capture template
		$context = $context;
		$set_id = $set_id;
		$settings = $settings;
		
		include $this->get_template_path( 'lead-capture.php' );
		
		return ob_get_clean();
	}

	/**
	 * Get flashcard set data
	 *
	 * @since 1.0.0
	 * @param int $set_id Set ID
	 * @return array|false Set data on success, false on failure
	 */
	private function get_set_data( $set_id ) {
		// TODO: Implement set data retrieval from database
		// For now, return placeholder data
		return array(
			'id'          => $set_id,
			'title'       => 'Sample Flashcard Set',
			'description' => 'This is a sample flashcard set for demonstration.',
			'author'      => 1,
			'created'     => current_time( 'mysql' ),
			'status'      => 'active',
		);
	}

	/**
	 * Get cards for a flashcard set
	 *
	 * @since 1.0.0
	 * @param int   $set_id Set ID
	 * @param array $args Query arguments
	 * @return array Array of cards
	 */
	private function get_set_cards( $set_id, $args = array() ) {
		// TODO: Implement card retrieval from database
		// For now, return placeholder cards
		return array(
			array(
				'id'       => 1,
				'question' => 'What is the capital of France?',
				'answer'   => 'Paris is the capital and most populous city of France.',
				'order'    => 1,
			),
			array(
				'id'       => 2,
				'question' => 'Who painted the Mona Lisa?',
				'answer'   => 'Leonardo da Vinci painted the Mona Lisa between 1503 and 1519.',
				'order'    => 2,
			),
			array(
				'id'       => 3,
				'question' => 'What is the largest planet in our solar system?',
				'answer'   => 'Jupiter is the largest planet in our solar system.',
				'order'    => 3,
			),
		);
	}

	/**
	 * Prepare settings for rendering
	 *
	 * @since 1.0.0
	 * @param array $args Raw arguments
	 * @return array Prepared settings
	 */
	private function prepare_settings( $args ) {
		// Get default settings
		$defaults = skylearn_get_default_settings();
		
		// Merge with provided arguments
		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Calculate study session statistics
	 *
	 * @since 1.0.0
	 * @param array $results Raw results data
	 * @return array Calculated statistics
	 */
	private function calculate_stats( $results ) {
		// TODO: Implement comprehensive statistics calculation
		return array(
			'total_cards'     => 3,
			'completed_cards' => 3,
			'time_spent'      => 180, // seconds
			'accuracy'        => 4.2,
			'excellent_cards' => 2,
			'good_cards'      => 1,
			'poor_cards'      => 0,
		);
	}

	/**
	 * Render error message
	 *
	 * @since 1.0.0
	 * @param string $message Error message
	 * @return string Rendered error HTML
	 */
	private function render_error( $message ) {
		return sprintf(
			'<div class="skylearn-error"><div class="error-icon"><span class="dashicons dashicons-warning"></span></div><p>%s</p></div>',
			esc_html( $message )
		);
	}

	/**
	 * Render empty set message
	 *
	 * @since 1.0.0
	 * @param array $set_data Set data
	 * @return string Rendered empty set HTML
	 */
	private function render_empty_set( $set_data ) {
		ob_start();
		?>
		<div class="skylearn-empty-set">
			<div class="empty-icon">
				<span class="dashicons dashicons-portfolio"></span>
			</div>
			<h3><?php esc_html_e( 'No Cards Available', 'skylearn-flashcards' ); ?></h3>
			<p>
				<?php 
				printf(
					/* translators: %s: set title */
					esc_html__( 'The flashcard set "%s" is empty or contains no published cards.', 'skylearn-flashcards' ),
					esc_html( $set_data['title'] )
				);
				?>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get template file path
	 *
	 * @since 1.0.0
	 * @param string $template Template filename
	 * @return string Full path to template file
	 */
	private function get_template_path( $template ) {
		return SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/views/' . $template;
	}

	/**
	 * Enqueue required assets for rendering
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		// Enqueue CSS
		wp_enqueue_style(
			$this->plugin_name . '-frontend',
			SKYLEARN_FLASHCARDS_ASSETS . 'css/frontend.css',
			array(),
			$this->version
		);
		
		wp_enqueue_style(
			$this->plugin_name . '-colors',
			SKYLEARN_FLASHCARDS_ASSETS . 'css/colors.css',
			array(),
			$this->version
		);
		
		// Enqueue JavaScript
		wp_enqueue_script(
			$this->plugin_name . '-flashcard',
			SKYLEARN_FLASHCARDS_ASSETS . 'js/flashcard.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		
		wp_enqueue_script(
			$this->plugin_name . '-frontend',
			SKYLEARN_FLASHCARDS_ASSETS . 'js/frontend.js',
			array( 'jquery', $this->plugin_name . '-flashcard' ),
			$this->version,
			true
		);
		
		// Localize script
		wp_localize_script(
			$this->plugin_name . '-frontend',
			'skylearn_frontend',
			array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'skylearn_frontend' ),
				'colors'        => skylearn_get_brand_colors(),
				'strings'       => array(
					'loading'       => __( 'Loading...', 'skylearn-flashcards' ),
					'error'         => __( 'An error occurred. Please try again.', 'skylearn-flashcards' ),
					'card_flipped'  => __( 'Card flipped', 'skylearn-flashcards' ),
					'set_complete'  => __( 'Congratulations! You completed the set!', 'skylearn-flashcards' ),
				),
			)
		);
	}

	/**
	 * Get available themes
	 *
	 * @since 1.0.0
	 * @return array Available themes
	 */
	public function get_available_themes() {
		return array(
			'default' => __( 'Default', 'skylearn-flashcards' ),
			'modern'  => __( 'Modern', 'skylearn-flashcards' ),
			'classic' => __( 'Classic', 'skylearn-flashcards' ),
		);
	}

	/**
	 * Check if template file exists
	 *
	 * @since 1.0.0
	 * @param string $template Template filename
	 * @return bool True if template exists
	 */
	public function template_exists( $template ) {
		return file_exists( $this->get_template_path( $template ) );
	}
}