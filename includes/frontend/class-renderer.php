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
		$this->version = $version;
	}
	
	/**
	 * Render flashcard set HTML
	 *
	 * @since    1.0.0
	 * @param    array     $flashcard_set    Flashcard set data
	 * @param    array     $settings         Display settings
	 * @param    string    $container_id     Container ID
	 * @return   string                      Rendered HTML
	 */
	public function render_flashcard_set( $flashcard_set, $settings = array(), $container_id = '' ) {
		if ( empty( $flashcard_set['cards'] ) ) {
			return '';
		}
		
		$defaults = array(
			'theme'         => 'default',
			'shuffle'       => 'false',
			'show_progress' => 'true',
			'autoplay'      => 'false',
			'autoplay_delay' => 3,
		);
		
		$settings = wp_parse_args( $settings, $defaults );
		
		if ( empty( $container_id ) ) {
			$container_id = 'skylearn-flashcards-' . $flashcard_set['id'] . '-' . wp_rand( 1000, 9999 );
		}
		
		$cards = $flashcard_set['cards'];
		$total_cards = count( $cards );
		
		// Apply shuffle if enabled
		if ( $settings['shuffle'] === 'true' ) {
			shuffle( $cards );
		}
		
		ob_start();
		?>
		<div id="<?php echo esc_attr( $container_id ); ?>" 
			 class="skylearn-flashcard-container theme-<?php echo esc_attr( $settings['theme'] ); ?>"
			 data-set-id="<?php echo esc_attr( $flashcard_set['id'] ); ?>"
			 data-total-cards="<?php echo esc_attr( $total_cards ); ?>"
			 data-shuffle="<?php echo esc_attr( $settings['shuffle'] ); ?>"
			 data-show-progress="<?php echo esc_attr( $settings['show_progress'] ); ?>"
			 data-autoplay="<?php echo esc_attr( $settings['autoplay'] ); ?>"
			 data-autoplay-delay="<?php echo esc_attr( $settings['autoplay_delay'] * 1000 ); ?>">
			
			<!-- Header -->
			<div class="skylearn-header">
				<h3 class="skylearn-set-title"><?php echo esc_html( $flashcard_set['title'] ); ?></h3>
				
				<?php if ( $settings['show_progress'] === 'true' ) : ?>
					<div class="skylearn-progress">
						<span class="current-card">1</span> / <span class="total-cards"><?php echo esc_html( $total_cards ); ?></span>
						<div class="progress-bar">
							<div class="progress-fill" style="width: <?php echo esc_attr( ( 1 / $total_cards ) * 100 ); ?>%"></div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			
			<!-- Cards Container -->
			<div class="skylearn-cards-wrapper">
				<?php foreach ( $cards as $index => $card ) : ?>
					<?php $this->render_flashcard( $card, $index, $index === 0 ); ?>
				<?php endforeach; ?>
			</div>
			
			<!-- Controls -->
			<div class="skylearn-controls">
				<div class="navigation-controls">
					<button type="button" class="skylearn-btn btn-prev" disabled>
						<span class="dashicons dashicons-arrow-left-alt2"></span>
						<?php esc_html_e( 'Previous', 'skylearn-flashcards' ); ?>
					</button>
					
					<button type="button" class="skylearn-btn btn-flip">
						<?php esc_html_e( 'Flip Card', 'skylearn-flashcards' ); ?>
					</button>
					
					<button type="button" class="skylearn-btn btn-next">
						<?php esc_html_e( 'Next', 'skylearn-flashcards' ); ?>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</div>
				
				<div class="action-controls">
					<button type="button" class="skylearn-btn btn-shuffle" title="<?php esc_attr_e( 'Shuffle cards', 'skylearn-flashcards' ); ?>">
						<span class="dashicons dashicons-randomize"></span>
					</button>
					
					<button type="button" class="skylearn-btn btn-reset" title="<?php esc_attr_e( 'Reset progress', 'skylearn-flashcards' ); ?>">
						<span class="dashicons dashicons-backup"></span>
					</button>
				</div>
			</div>
			
			<!-- Answer Tracking (for free users) -->
			<div class="skylearn-answer-tracking">
				<div class="tracking-question">
					<p><?php esc_html_e( 'Did you get this card correct?', 'skylearn-flashcards' ); ?></p>
					<div class="tracking-buttons">
						<button type="button" class="skylearn-btn btn-correct btn-success">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Correct', 'skylearn-flashcards' ); ?>
						</button>
						<button type="button" class="skylearn-btn btn-incorrect btn-danger">
							<span class="dashicons dashicons-no"></span>
							<?php esc_html_e( 'Incorrect', 'skylearn-flashcards' ); ?>
						</button>
					</div>
				</div>
			</div>
			
			<!-- Session Summary (hidden by default) -->
			<div class="skylearn-session-summary" style="display: none;">
				<div class="summary-content">
					<h4><?php esc_html_e( 'Study Session Complete!', 'skylearn-flashcards' ); ?></h4>
					<div class="summary-stats">
						<div class="stat-item">
							<span class="stat-value correct-count">0</span>
							<span class="stat-label"><?php esc_html_e( 'Correct', 'skylearn-flashcards' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-value incorrect-count">0</span>
							<span class="stat-label"><?php esc_html_e( 'Incorrect', 'skylearn-flashcards' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-value time-taken">0:00</span>
							<span class="stat-label"><?php esc_html_e( 'Time Taken', 'skylearn-flashcards' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-value accuracy-rate">0%</span>
							<span class="stat-label"><?php esc_html_e( 'Accuracy', 'skylearn-flashcards' ); ?></span>
						</div>
					</div>
					<div class="summary-actions">
						<button type="button" class="skylearn-btn btn-study-again btn-primary">
							<?php esc_html_e( 'Study Again', 'skylearn-flashcards' ); ?>
						</button>
						<button type="button" class="skylearn-btn btn-shuffle-retry">
							<?php esc_html_e( 'Shuffle & Retry', 'skylearn-flashcards' ); ?>
						</button>
					</div>
				</div>
			</div>
			
			<!-- Loading indicator -->
			<div class="skylearn-loading" style="display: none;">
				<div class="loading-spinner"></div>
				<p><?php esc_html_e( 'Loading...', 'skylearn-flashcards' ); ?></p>
			</div>
		</div>
		<?php
		
		return ob_get_clean();
	}
	
	/**
	 * Render individual flashcard
	 *
	 * @since    1.0.0
	 * @param    array     $card      Card data
	 * @param    int       $index     Card index
	 * @param    bool      $active    Whether this card is currently active
	 * @return   void
	 */
	private function render_flashcard( $card, $index, $active = false ) {
		$front = $card['front'] ?? '';
		$back = $card['back'] ?? '';
		
		if ( empty( $front ) || empty( $back ) ) {
			return;
		}
		?>
		<div class="skylearn-flashcard <?php echo $active ? 'active' : ''; ?>" 
			 data-card-index="<?php echo esc_attr( $index ); ?>"
			 role="button"
			 tabindex="0"
			 aria-label="<?php esc_attr_e( 'Flashcard - click to flip', 'skylearn-flashcards' ); ?>">
			
			<div class="flashcard-inner">
				<div class="flashcard-front">
					<div class="card-content">
						<?php echo wp_kses_post( $front ); ?>
					</div>
					<div class="card-hint">
						<span class="flip-hint"><?php esc_html_e( 'Click to flip', 'skylearn-flashcards' ); ?></span>
					</div>
				</div>
				
				<div class="flashcard-back">
					<div class="card-content">
						<?php echo wp_kses_post( $back ); ?>
					</div>
					<div class="card-hint">
						<span class="flip-hint"><?php esc_html_e( 'Click to flip back', 'skylearn-flashcards' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Get flashcard theme classes
	 *
	 * @since    1.0.0
	 * @param    string    $theme    Theme name
	 * @return   string              Theme CSS classes
	 */
	public function get_theme_classes( $theme ) {
		$theme = sanitize_html_class( $theme );
		$classes = array( 'skylearn-theme', "skylearn-theme-{$theme}" );
		
		return implode( ' ', $classes );
	}
}
