<?php
/**
 * The flashcard set editor functionality
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 */

/**
 * The flashcard set editor class.
 *
 * Defines all functionality for creating and editing flashcard sets.
 *
 * @since      1.0.0
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Editor {

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
		
		// Initialize editor hooks
		$this->init_hooks();
	}
	
	/**
	 * Initialize editor hooks
	 *
	 * @since    1.0.0
	 */
	private function init_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_flashcard_set' ) );
		add_action( 'wp_ajax_skylearn_save_cards', array( $this, 'ajax_save_cards' ) );
		add_action( 'wp_ajax_skylearn_reorder_cards', array( $this, 'ajax_reorder_cards' ) );
		add_action( 'wp_ajax_skylearn_check_set_limit', array( $this, 'ajax_check_set_limit' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_editor_assets' ) );
	}
	
	/**
	 * Add meta boxes for flashcard editor
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'skylearn-flashcard-editor',
			__( 'Flashcard Editor', 'skylearn-flashcards' ),
			array( $this, 'render_editor_meta_box' ),
			'flashcard_set',
			'normal',
			'high'
		);
		
		add_meta_box(
			'skylearn-flashcard-settings',
			__( 'Set Settings', 'skylearn-flashcards' ),
			array( $this, 'render_settings_meta_box' ),
			'flashcard_set',
			'side',
			'default'
		);
	}
	
	/**
	 * Render flashcard editor meta box
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    Current post object
	 */
	public function render_editor_meta_box( $post ) {
		wp_nonce_field( 'skylearn_save_flashcards', 'skylearn_flashcards_nonce' );
		
		$cards = get_post_meta( $post->ID, '_skylearn_flashcard_data', true );
		if ( ! is_array( $cards ) ) {
			$cards = array();
		}
		
		?>
		<div id="skylearn-flashcard-editor" data-set-id="<?php echo esc_attr( $post->ID ); ?>">
			<div class="skylearn-editor-header">
				<div class="skylearn-editor-stats">
					<span class="card-count"><?php printf( esc_html__( 'Cards: %d', 'skylearn-flashcards' ), count( $cards ) ); ?></span>
					<button type="button" class="button button-primary" id="add-new-card">
						<?php esc_html_e( 'Add New Card', 'skylearn-flashcards' ); ?>
					</button>
				</div>
			</div>
			
			<div id="flashcard-list" class="skylearn-cards-container">
				<?php if ( empty( $cards ) ) : ?>
					<div class="skylearn-empty-state">
						<h3><?php esc_html_e( 'No cards yet', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Click "Add New Card" to create your first flashcard.', 'skylearn-flashcards' ); ?></p>
					</div>
				<?php else : ?>
					<?php foreach ( $cards as $index => $card ) : ?>
						<?php $this->render_card_editor( $index, $card ); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			
			<div class="skylearn-editor-actions">
				<button type="button" class="button" id="save-cards">
					<?php esc_html_e( 'Save Cards', 'skylearn-flashcards' ); ?>
				</button>
				<span class="save-status" style="display: none;"></span>
			</div>
		</div>
		
		<!-- Card template for JavaScript -->
		<script type="text/template" id="card-template">
			<?php $this->render_card_editor( '{{INDEX}}', array( 'front' => '', 'back' => '' ) ); ?>
		</script>
		<?php
	}
	
	/**
	 * Render individual card editor
	 *
	 * @since    1.0.0
	 * @param    int|string    $index    Card index
	 * @param    array         $card     Card data
	 */
	private function render_card_editor( $index, $card ) {
		$front = $card['front'] ?? '';
		$back = $card['back'] ?? '';
		?>
		<div class="skylearn-card-editor" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="card-header">
				<span class="card-number"><?php printf( esc_html__( 'Card %s', 'skylearn-flashcards' ), '<span class="number">' . ( is_numeric( $index ) ? $index + 1 : 1 ) . '</span>' ); ?></span>
				<div class="card-actions">
					<button type="button" class="button-link move-card" title="<?php esc_attr_e( 'Drag to reorder', 'skylearn-flashcards' ); ?>">
						<span class="dashicons dashicons-menu"></span>
					</button>
					<button type="button" class="button-link delete-card" title="<?php esc_attr_e( 'Delete card', 'skylearn-flashcards' ); ?>">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</div>
			</div>
			
			<div class="card-content">
				<div class="card-side front-side">
					<label><?php esc_html_e( 'Front (Question/Term)', 'skylearn-flashcards' ); ?></label>
					<textarea name="cards[<?php echo esc_attr( $index ); ?>][front]" class="card-front" rows="3" placeholder="<?php esc_attr_e( 'Enter the question or term here...', 'skylearn-flashcards' ); ?>"><?php echo esc_textarea( $front ); ?></textarea>
				</div>
				
				<div class="card-side back-side">
					<label><?php esc_html_e( 'Back (Answer/Definition)', 'skylearn-flashcards' ); ?></label>
					<textarea name="cards[<?php echo esc_attr( $index ); ?>][back]" class="card-back" rows="3" placeholder="<?php esc_attr_e( 'Enter the answer or definition here...', 'skylearn-flashcards' ); ?>"><?php echo esc_textarea( $back ); ?></textarea>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Render settings meta box
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    Current post object
	 */
	public function render_settings_meta_box( $post ) {
		$settings = get_post_meta( $post->ID, '_skylearn_set_settings', true );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		
		$defaults = array(
			'shuffle_default' => false,
			'show_progress'   => true,
			'autoplay'        => false,
			'autoplay_delay'  => 3,
		);
		
		$settings = wp_parse_args( $settings, $defaults );
		?>
		<div class="skylearn-set-settings">
			<p>
				<label>
					<input type="checkbox" name="set_settings[shuffle_default]" value="1" <?php checked( $settings['shuffle_default'] ); ?>>
					<?php esc_html_e( 'Shuffle cards by default', 'skylearn-flashcards' ); ?>
				</label>
			</p>
			
			<p>
				<label>
					<input type="checkbox" name="set_settings[show_progress]" value="1" <?php checked( $settings['show_progress'] ); ?>>
					<?php esc_html_e( 'Show progress indicator', 'skylearn-flashcards' ); ?>
				</label>
			</p>
			
			<p>
				<label>
					<input type="checkbox" name="set_settings[autoplay]" value="1" <?php checked( $settings['autoplay'] ); ?>>
					<?php esc_html_e( 'Enable autoplay mode', 'skylearn-flashcards' ); ?>
				</label>
			</p>
			
			<p class="autoplay-delay" style="<?php echo $settings['autoplay'] ? '' : 'display: none;'; ?>">
				<label>
					<?php esc_html_e( 'Autoplay delay (seconds)', 'skylearn-flashcards' ); ?>
					<input type="number" name="set_settings[autoplay_delay]" value="<?php echo esc_attr( $settings['autoplay_delay'] ); ?>" min="1" max="10" step="1">
				</label>
			</p>
			
			<hr>
			
			<h4><?php esc_html_e( 'Shortcode', 'skylearn-flashcards' ); ?></h4>
			<p><?php esc_html_e( 'Use this shortcode to display this flashcard set:', 'skylearn-flashcards' ); ?></p>
			<input type="text" readonly value="[skylearn_flashcard_set id=&quot;<?php echo esc_attr( $post->ID ); ?>&quot;]" onclick="this.select();" style="width: 100%;">
		</div>
		<?php
	}
	
	/**
	 * Enqueue editor assets
	 *
	 * @since    1.0.0
	 * @param    string    $hook    Current admin page hook
	 */
	public function enqueue_editor_assets( $hook ) {
		global $post_type;
		
		if ( $post_type !== 'flashcard_set' || ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}
		
		// Enqueue jQuery UI for sortable
		wp_enqueue_script( 'jquery-ui-sortable' );
		
		// Localize script data
		wp_localize_script( 'skylearn-flashcards-admin', 'skyleanEditor', array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'skylearn_editor_nonce' ),
			'strings'     => array(
				'confirm_delete'    => __( 'Are you sure you want to delete this card?', 'skylearn-flashcards' ),
				'saving'            => __( 'Saving...', 'skylearn-flashcards' ),
				'saved'             => __( 'Cards saved successfully!', 'skylearn-flashcards' ),
				'error'             => __( 'Error saving cards. Please try again.', 'skylearn-flashcards' ),
				'empty_card'        => __( 'Please fill in both front and back of the card.', 'skylearn-flashcards' ),
				'max_sets_reached'  => __( 'You have reached the maximum number of flashcard sets (5) for the free version. Upgrade to premium for unlimited sets!', 'skylearn-flashcards' ),
				'card_label'        => __( 'Card %d', 'skylearn-flashcards' ),
			),
		) );
	}
	
	/**
	 * Save flashcard set data
	 *
	 * @since    1.0.0
	 * @param    int    $post_id    Post ID
	 */
	public function save_flashcard_set( $post_id ) {
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		// Check post type
		if ( get_post_type( $post_id ) !== 'flashcard_set' ) {
			return;
		}
		
		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Verify nonce
		if ( ! isset( $_POST['skylearn_flashcards_nonce'] ) || ! wp_verify_nonce( $_POST['skylearn_flashcards_nonce'], 'skylearn_save_flashcards' ) ) {
			return;
		}
		
		// Save cards data
		if ( isset( $_POST['cards'] ) && is_array( $_POST['cards'] ) ) {
			$cards = skylearn_sanitize_flashcard_data( $_POST['cards'] );
			update_post_meta( $post_id, '_skylearn_flashcard_data', $cards );
		}
		
		// Save set settings
		if ( isset( $_POST['set_settings'] ) && is_array( $_POST['set_settings'] ) ) {
			$settings = array(
				'shuffle_default' => ! empty( $_POST['set_settings']['shuffle_default'] ),
				'show_progress'   => ! empty( $_POST['set_settings']['show_progress'] ),
				'autoplay'        => ! empty( $_POST['set_settings']['autoplay'] ),
				'autoplay_delay'  => absint( $_POST['set_settings']['autoplay_delay'] ?? 3 ),
			);
			update_post_meta( $post_id, '_skylearn_set_settings', $settings );
		}
	}
	
	/**
	 * AJAX: Save cards
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_cards() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_editor_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		$set_id = absint( $_POST['set_id'] ?? 0 );
		$cards = $_POST['cards'] ?? array();
		
		if ( ! $set_id || ! skylearn_current_user_can_edit() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'skylearn-flashcards' ) ) );
		}
		
		// Check if user owns this set or can edit others' posts
		$post = get_post( $set_id );
		if ( ! $post || ( $post->post_author != get_current_user_id() && ! current_user_can( 'edit_others_posts' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'skylearn-flashcards' ) ) );
		}
		
		$sanitized_cards = skylearn_sanitize_flashcard_data( $cards );
		update_post_meta( $set_id, '_skylearn_flashcard_data', $sanitized_cards );
		
		wp_send_json_success( array(
			'message'    => __( 'Cards saved successfully!', 'skylearn-flashcards' ),
			'card_count' => count( $sanitized_cards ),
		) );
	}
	
	/**
	 * AJAX: Reorder cards
	 *
	 * @since    1.0.0
	 */
	public function ajax_reorder_cards() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_editor_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		$set_id = absint( $_POST['set_id'] ?? 0 );
		$new_order = $_POST['new_order'] ?? array();
		
		if ( ! $set_id || ! skylearn_current_user_can_edit() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'skylearn-flashcards' ) ) );
		}
		
		$cards = get_post_meta( $set_id, '_skylearn_flashcard_data', true );
		if ( ! is_array( $cards ) ) {
			wp_send_json_error( array( 'message' => __( 'No cards found.', 'skylearn-flashcards' ) ) );
		}
		
		$reordered_cards = array();
		foreach ( $new_order as $index ) {
			if ( isset( $cards[ $index ] ) ) {
				$reordered_cards[] = $cards[ $index ];
			}
		}
		
		update_post_meta( $set_id, '_skylearn_flashcard_data', $reordered_cards );
		
		wp_send_json_success( array( 'message' => __( 'Cards reordered successfully!', 'skylearn-flashcards' ) ) );
	}
	
	/**
	 * AJAX: Check set limit for current user
	 *
	 * @since    1.0.0
	 */
	public function ajax_check_set_limit() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_editor_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}
		
		$can_create = skylearn_user_can_create_set();
		$current_count = skylearn_get_user_set_count();
		$max_sets = skylearn_is_premium() ? __( 'Unlimited', 'skylearn-flashcards' ) : 5;
		
		wp_send_json_success( array(
			'can_create'     => $can_create,
			'current_count'  => $current_count,
			'max_sets'       => $max_sets,
			'is_premium'     => skylearn_is_premium(),
			'upsell_message' => __( 'Upgrade to Premium for unlimited flashcard sets, advanced analytics, and more!', 'skylearn-flashcards' ),
		) );
	}
}
