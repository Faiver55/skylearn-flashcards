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
	 * @param    array    $atts    Shortcode attributes
	 * @return   string            Rendered HTML
	 */
	public function render_shortcode( $atts ) {

		// Parse shortcode attributes
		$atts = shortcode_atts( array(
			'id'            => 0,
			'show_progress' => 'true',
			'shuffle'       => 'false',
			'theme'         => 'default',
			'autoplay'      => 'false',
			'show_hints'    => 'true',
			'max_cards'     => 0,
			'difficulty'    => 'all', // all, easy, medium, hard
			'category'      => '',
			'tag'           => '',
		), $atts, 'skylearn_flashcards' );

		// Validate set ID
		$set_id = absint( $atts['id'] );
		if ( ! $set_id ) {
			return $this->render_error( __( 'Please specify a valid flashcard set ID.', 'skylearn-flashcards' ) );
		}

		// Get flashcard set data
		$flashcard_set = skylearn_get_flashcard_set( $set_id );
		if ( ! $flashcard_set ) {
			return $this->render_error( __( 'Flashcard set not found.', 'skylearn-flashcards' ) );
		}

		// Check if set is published (unless in preview mode)
		if ( ! isset( $_GET['skylearn_preview'] ) && get_post_status( $set_id ) !== 'publish' ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				return $this->render_error( __( 'This flashcard set is not available.', 'skylearn-flashcards' ) );
			}
		}

		// Filter cards based on attributes
		$cards = $this->filter_cards( $flashcard_set['cards'], $atts );

		if ( empty( $cards ) ) {
			return $this->render_error( __( 'No flashcards found matching the specified criteria.', 'skylearn-flashcards' ) );
		}

		// Generate unique ID for this instance
		$instance_id = 'skylearn-set-' . $set_id . '-' . wp_rand( 1000, 9999 );

		// Build settings array
		$settings = array_merge( $flashcard_set['settings'], array(
			'show_progress' => filter_var( $atts['show_progress'], FILTER_VALIDATE_BOOLEAN ),
			'shuffle_cards' => filter_var( $atts['shuffle'], FILTER_VALIDATE_BOOLEAN ),
			'theme'         => sanitize_key( $atts['theme'] ),
			'autoplay'      => filter_var( $atts['autoplay'], FILTER_VALIDATE_BOOLEAN ),
			'show_hints'    => filter_var( $atts['show_hints'], FILTER_VALIDATE_BOOLEAN ),
			'max_cards'     => absint( $atts['max_cards'] ),
		) );

		// Limit cards if max_cards is set
		if ( $settings['max_cards'] > 0 && count( $cards ) > $settings['max_cards'] ) {
			$cards = array_slice( $cards, 0, $settings['max_cards'] );
		}

		// Shuffle cards if enabled
		if ( $settings['shuffle_cards'] ) {
			shuffle( $cards );
		}

		// Render the flashcard set
		return $this->render_flashcard_set( $flashcard_set, $cards, $settings, $instance_id );

	}

	/**
	 * Filter cards based on shortcode attributes
	 *
	 * @since    1.0.0
	 * @param    array    $cards    Original cards array
	 * @param    array    $atts     Shortcode attributes
	 * @return   array              Filtered cards array
	 */
	private function filter_cards( $cards, $atts ) {

		$filtered_cards = $cards;

		// Filter by difficulty
		if ( $atts['difficulty'] !== 'all' ) {
			$filtered_cards = array_filter( $filtered_cards, function( $card ) use ( $atts ) {
				return ( $card['difficulty'] ?? 'medium' ) === $atts['difficulty'];
			} );
		}

		// Reset array keys
		return array_values( $filtered_cards );

	}

	/**
	 * Render flashcard set HTML
	 *
	 * @since    1.0.0
	 * @param    array    $flashcard_set    Flashcard set data
	 * @param    array    $cards            Cards to display
	 * @param    array    $settings         Display settings
	 * @param    string   $instance_id      Unique instance ID
	 * @return   string                     Rendered HTML
	 */
	private function render_flashcard_set( $flashcard_set, $cards, $settings, $instance_id ) {

		ob_start();

		?>
		<div class="skylearn-flashcard-set skylearn-theme-<?php echo esc_attr( $settings['theme'] ); ?>" 
		     id="<?php echo esc_attr( $instance_id ); ?>" 
		     data-set-id="<?php echo esc_attr( $flashcard_set['id'] ); ?>" 
		     data-settings="<?php echo esc_attr( json_encode( $settings ) ); ?>">
			
			<!-- Header -->
			<div class="skylearn-flashcard-header">
				<h2 class="skylearn-flashcard-title"><?php echo esc_html( $flashcard_set['title'] ); ?></h2>
				<?php if ( ! empty( $flashcard_set['description'] ) ) : ?>
					<p class="skylearn-flashcard-description"><?php echo wp_kses_post( $flashcard_set['description'] ); ?></p>
				<?php endif; ?>
			</div>

			<!-- Progress Bar -->
			<?php if ( $settings['show_progress'] ) : ?>
				<div class="skylearn-progress">
					<div class="skylearn-progress-text">
						<span class="skylearn-current">1</span> / <span class="skylearn-total"><?php echo esc_html( count( $cards ) ); ?></span>
					</div>
					<div class="skylearn-progress-bar">
						<div class="skylearn-progress-fill" style="width: 0%"></div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Flashcards Container -->
			<div class="skylearn-flashcards-container">
				<?php foreach ( $cards as $index => $card ) : ?>
					<div class="skylearn-flashcard" 
					     data-card-index="<?php echo esc_attr( $index ); ?>" 
					     data-difficulty="<?php echo esc_attr( $card['difficulty'] ?? 'medium' ); ?>"
					     style="<?php echo $index === 0 ? '' : 'display: none;'; ?>"
					     tabindex="0"
					     role="button"
					     aria-label="<?php esc_attr_e( 'Flashcard - click to flip', 'skylearn-flashcards' ); ?>">
						
						<div class="skylearn-flashcard-inner">
							
							<!-- Front Side (Question) -->
							<div class="skylearn-flashcard-front">
								<div class="skylearn-flashcard-content">
									<?php echo wp_kses_post( wpautop( $card['question'] ) ); ?>
								</div>
								
								<?php if ( $settings['show_hints'] && ! empty( $card['hint'] ) ) : ?>
									<div class="skylearn-flashcard-hint">
										<small><strong><?php esc_html_e( 'Hint:', 'skylearn-flashcards' ); ?></strong> <?php echo esc_html( $card['hint'] ); ?></small>
									</div>
								<?php endif; ?>
								
								<div class="skylearn-flashcard-meta">
									<span class="skylearn-difficulty skylearn-difficulty-<?php echo esc_attr( $card['difficulty'] ?? 'medium' ); ?>">
										<?php echo esc_html( ucfirst( $card['difficulty'] ?? 'medium' ) ); ?>
									</span>
								</div>
							</div>

							<!-- Back Side (Answer) -->
							<div class="skylearn-flashcard-back">
								<div class="skylearn-flashcard-content">
									<?php echo wp_kses_post( wpautop( $card['answer'] ) ); ?>
								</div>
							</div>

						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Controls -->
			<div class="skylearn-controls">
				<button type="button" class="skylearn-control-btn" data-action="prev" disabled>
					<span class="dashicons dashicons-arrow-left-alt2"></span>
					<?php esc_html_e( 'Previous', 'skylearn-flashcards' ); ?>
				</button>

				<div class="skylearn-progress-info">
					<span class="skylearn-card-info">
						<?php printf( esc_html__( 'Card %1$s of %2$s', 'skylearn-flashcards' ), '<span class="skylearn-current-card">1</span>', '<span class="skylearn-total-cards">' . count( $cards ) . '</span>' ); ?>
					</span>
				</div>

				<button type="button" class="skylearn-control-btn" data-action="next">
					<?php esc_html_e( 'Next', 'skylearn-flashcards' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</button>
			</div>

			<!-- Advanced Controls -->
			<div class="skylearn-advanced-controls">
				<button type="button" class="skylearn-control-btn skylearn-btn-secondary" data-action="shuffle">
					<span class="dashicons dashicons-randomize"></span>
					<?php esc_html_e( 'Shuffle', 'skylearn-flashcards' ); ?>
				</button>

				<?php if ( $settings['autoplay'] ) : ?>
					<button type="button" class="skylearn-control-btn skylearn-btn-secondary skylearn-autoplay">
						<span class="dashicons dashicons-controls-play"></span>
						<?php esc_html_e( 'Auto Play', 'skylearn-flashcards' ); ?>
					</button>
				<?php endif; ?>

				<button type="button" class="skylearn-control-btn skylearn-btn-secondary" data-action="reset">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Reset', 'skylearn-flashcards' ); ?>
				</button>
			</div>

			<!-- Study Mode Controls -->
			<div class="skylearn-study-controls">
				<div class="skylearn-study-modes">
					<button type="button" class="skylearn-study-mode active" data-mode="normal">
						<?php esc_html_e( 'Study', 'skylearn-flashcards' ); ?>
					</button>
					<button type="button" class="skylearn-study-mode" data-mode="quiz">
						<?php esc_html_e( 'Quiz', 'skylearn-flashcards' ); ?>
					</button>
					<button type="button" class="skylearn-study-mode" data-mode="review">
						<?php esc_html_e( 'Review', 'skylearn-flashcards' ); ?>
					</button>
				</div>

				<div class="skylearn-knowledge-buttons" style="display: none;">
					<button type="button" class="skylearn-mark-known skylearn-btn">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'I Know This', 'skylearn-flashcards' ); ?>
					</button>
					<button type="button" class="skylearn-mark-unknown skylearn-btn-secondary">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'I Don\'t Know', 'skylearn-flashcards' ); ?>
					</button>
				</div>
			</div>

			<!-- Lead Capture Form (Premium Feature) -->
			<?php if ( skylearn_is_premium() && skylearn_get_setting( 'enable_lead_capture', false ) ) : ?>
				<?php $this->render_lead_capture_form( $flashcard_set, $settings ); ?>
			<?php endif; ?>

			<!-- Keyboard Shortcuts Help -->
			<div class="skylearn-help-text">
				<small>
					<?php esc_html_e( 'Keyboard shortcuts:', 'skylearn-flashcards' ); ?>
					<strong><?php esc_html_e( 'Space', 'skylearn-flashcards' ); ?></strong> - <?php esc_html_e( 'flip card', 'skylearn-flashcards' ); ?>,
					<strong><?php esc_html_e( 'Arrow keys', 'skylearn-flashcards' ); ?></strong> - <?php esc_html_e( 'navigate', 'skylearn-flashcards' ); ?>,
					<strong>K/U</strong> - <?php esc_html_e( 'mark known/unknown', 'skylearn-flashcards' ); ?>
				</small>
			</div>

		</div>
		<?php

		return ob_get_clean();

	}

	/**
	 * Render lead capture form
	 *
	 * @since    1.0.0
	 * @param    array    $flashcard_set    Flashcard set data
	 * @param    array    $settings         Display settings
	 */
	private function render_lead_capture_form( $flashcard_set, $settings ) {

		$lead_settings = $flashcard_set['settings'];
		$form_title = $lead_settings['lead_form_title'] ?? __( 'Want to learn more?', 'skylearn-flashcards' );
		$form_message = $lead_settings['lead_form_message'] ?? __( 'Enter your details to get more study materials.', 'skylearn-flashcards' );

		?>
		<div class="skylearn-lead-capture" style="display: none;">
			<div class="skylearn-lead-form-container">
				<h3><?php echo esc_html( $form_title ); ?></h3>
				<p><?php echo esc_html( $form_message ); ?></p>
				
				<form class="skylearn-lead-form">
					<div class="skylearn-form-row">
						<div class="skylearn-form-field">
							<label for="skylearn-lead-name"><?php esc_html_e( 'Name', 'skylearn-flashcards' ); ?> <span class="required">*</span></label>
							<input type="text" id="skylearn-lead-name" name="name" required>
						</div>
						<div class="skylearn-form-field">
							<label for="skylearn-lead-email"><?php esc_html_e( 'Email', 'skylearn-flashcards' ); ?> <span class="required">*</span></label>
							<input type="email" id="skylearn-lead-email" name="email" required>
						</div>
					</div>
					
					<div class="skylearn-form-field">
						<label for="skylearn-lead-phone"><?php esc_html_e( 'Phone (Optional)', 'skylearn-flashcards' ); ?></label>
						<input type="tel" id="skylearn-lead-phone" name="phone">
					</div>
					
					<div class="skylearn-form-field">
						<label for="skylearn-lead-message"><?php esc_html_e( 'Message (Optional)', 'skylearn-flashcards' ); ?></label>
						<textarea id="skylearn-lead-message" name="message" rows="3"></textarea>
					</div>
					
					<input type="hidden" name="set_id" value="<?php echo esc_attr( $flashcard_set['id'] ); ?>">
					
					<div class="skylearn-form-actions">
						<button type="submit" class="skylearn-btn skylearn-submit-btn">
							<?php esc_html_e( 'Submit', 'skylearn-flashcards' ); ?>
						</button>
						<button type="button" class="skylearn-btn-secondary skylearn-close-form">
							<?php esc_html_e( 'Maybe Later', 'skylearn-flashcards' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
		<?php

	}

	/**
	 * Render error message
	 *
	 * @since    1.0.0
	 * @param    string   $message    Error message
	 * @return   string               Error HTML
	 */
	private function render_error( $message ) {

		return '<div class="skylearn-error"><p>' . esc_html( $message ) . '</p></div>';

	}

	/**
	 * Get flashcard sets for dropdown (used in block editor)
	 *
	 * @since    1.0.0
	 * @return   array    Array of flashcard sets
	 */
	public static function get_flashcard_sets_for_select() {

		$sets = get_posts( array(
			'post_type'      => 'skylearn_flashcard',
			'post_status'    => 'publish',
			'numberposts'    => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$options = array(
			array(
				'label' => __( 'Select a flashcard set...', 'skylearn-flashcards' ),
				'value' => 0,
			),
		);

		foreach ( $sets as $set ) {
			$options[] = array(
				'label' => $set->post_title,
				'value' => $set->ID,
			);
		}

		return $options;

	}

}