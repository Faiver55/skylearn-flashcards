<?php
/**
 * LearnDash integration for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/lms
 */

/**
 * LearnDash integration class.
 *
 * Provides integration with LearnDash LMS.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/lms
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_LearnDash {

	/**
	 * Initialize LearnDash integration
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		// Only load if LearnDash is active
		if ( ! class_exists( 'SFWD_LMS' ) ) {
			return;
		}

		// Add integration hooks
		add_action( 'init', array( $this, 'init' ) );
		
	}

	/**
	 * Initialize integration
	 *
	 * @since    1.0.0
	 */
	public function init() {
		
		// Add flashcards to course content
		add_action( 'learndash_lesson_content_tabs', array( $this, 'add_flashcards_tab' ), 10, 3 );
		
		// Track progress in LearnDash
		add_action( 'skylearn_flashcard_completed', array( $this, 'track_progress' ), 10, 3 );
		
		// Add flashcards metabox to lessons
		add_action( 'add_meta_boxes', array( $this, 'add_lesson_metabox' ) );
		
	}

	/**
	 * Add flashcards tab to lesson content
	 *
	 * @since    1.0.0
	 * @param    array   $tabs       Existing tabs
	 * @param    int     $lesson_id  Lesson ID
	 * @param    int     $user_id    User ID
	 * @return   array               Modified tabs
	 */
	public function add_flashcards_tab( $tabs, $lesson_id, $user_id ) {
		
		// Get associated flashcard sets
		$flashcard_sets = get_post_meta( $lesson_id, '_skylearn_flashcard_sets', true );
		
		if ( empty( $flashcard_sets ) ) {
			return $tabs;
		}
		
		$tabs['flashcards'] = array(
			'id'       => 'flashcards',
			'icon'     => 'ld-icon-flashcards',
			'label'    => __( 'Flashcards', 'skylearn-flashcards' ),
			'content'  => $this->render_lesson_flashcards( $flashcard_sets ),
		);
		
		return $tabs;
		
	}

	/**
	 * Render flashcards for lesson
	 *
	 * @since    1.0.0
	 * @param    array   $flashcard_sets  Flashcard set IDs
	 * @return   string                   Rendered content
	 */
	private function render_lesson_flashcards( $flashcard_sets ) {
		
		ob_start();
		
		?>
		<div class="skylearn-learndash-flashcards">
			<h4><?php esc_html_e( 'Study with Flashcards', 'skylearn-flashcards' ); ?></h4>
			<p><?php esc_html_e( 'Reinforce your learning with these interactive flashcards.', 'skylearn-flashcards' ); ?></p>
			
			<?php foreach ( $flashcard_sets as $set_id ) : ?>
				<?php
				$set_id = absint( $set_id );
				if ( ! $set_id ) continue;
				
				echo do_shortcode( "[skylearn_flashcards id=\"{$set_id}\"]" );
				?>
			<?php endforeach; ?>
		</div>
		<?php
		
		return ob_get_clean();
		
	}

	/**
	 * Track flashcard progress in LearnDash
	 *
	 * @since    1.0.0
	 * @param    int     $user_id     User ID
	 * @param    int     $set_id      Flashcard set ID
	 * @param    float   $accuracy    Accuracy percentage
	 */
	public function track_progress( $user_id, $set_id, $accuracy ) {
		
		// Find associated lesson
		$lessons = get_posts( array(
			'post_type'  => 'sfwd-lessons',
			'meta_query' => array(
				array(
					'key'     => '_skylearn_flashcard_sets',
					'value'   => $set_id,
					'compare' => 'LIKE',
				),
			),
		) );
		
		if ( empty( $lessons ) ) {
			return;
		}
		
		$lesson_id = $lessons[0]->ID;
		
		// Update user progress
		$progress = get_user_meta( $user_id, "_skylearn_flashcard_progress_{$lesson_id}", true );
		if ( ! is_array( $progress ) ) {
			$progress = array();
		}
		
		$progress[ $set_id ] = array(
			'accuracy'    => $accuracy,
			'completed'   => true,
			'completed_at' => current_time( 'mysql' ),
		);
		
		update_user_meta( $user_id, "_skylearn_flashcard_progress_{$lesson_id}", $progress );
		
		// Mark lesson as completed if all flashcards are done and accuracy is high enough
		$this->check_lesson_completion( $user_id, $lesson_id );
		
	}

	/**
	 * Check if lesson should be marked complete based on flashcard progress
	 *
	 * @since    1.0.0
	 * @param    int   $user_id    User ID
	 * @param    int   $lesson_id  Lesson ID
	 */
	private function check_lesson_completion( $user_id, $lesson_id ) {
		
		$flashcard_sets = get_post_meta( $lesson_id, '_skylearn_flashcard_sets', true );
		$progress = get_user_meta( $user_id, "_skylearn_flashcard_progress_{$lesson_id}", true );
		
		if ( empty( $flashcard_sets ) || ! is_array( $progress ) ) {
			return;
		}
		
		$required_accuracy = get_post_meta( $lesson_id, '_skylearn_required_accuracy', true ) ?: 80;
		$total_accuracy = 0;
		$completed_sets = 0;
		
		foreach ( $flashcard_sets as $set_id ) {
			if ( isset( $progress[ $set_id ] ) && $progress[ $set_id ]['completed'] ) {
				$total_accuracy += $progress[ $set_id ]['accuracy'];
				$completed_sets++;
			}
		}
		
		// Check if all sets are completed and average accuracy meets requirement
		if ( $completed_sets === count( $flashcard_sets ) ) {
			$average_accuracy = $total_accuracy / $completed_sets;
			
			if ( $average_accuracy >= $required_accuracy ) {
				// Mark lesson as complete
				learndash_process_mark_complete( $user_id, $lesson_id );
			}
		}
		
	}

	/**
	 * Add metabox to lesson edit screen
	 *
	 * @since    1.0.0
	 */
	public function add_lesson_metabox() {
		
		add_meta_box(
			'skylearn-flashcards-metabox',
			__( 'SkyLearn Flashcards', 'skylearn-flashcards' ),
			array( $this, 'render_lesson_metabox' ),
			'sfwd-lessons',
			'side',
			'default'
		);
		
	}

	/**
	 * Render lesson metabox
	 *
	 * @since    1.0.0
	 * @param    WP_Post  $post  Post object
	 */
	public function render_lesson_metabox( $post ) {
		
		wp_nonce_field( 'skylearn_lesson_flashcards', 'skylearn_lesson_nonce' );
		
		$flashcard_sets = get_post_meta( $post->ID, '_skylearn_flashcard_sets', true );
		$required_accuracy = get_post_meta( $post->ID, '_skylearn_required_accuracy', true ) ?: 80;
		
		// Get available flashcard sets
		$available_sets = get_posts( array(
			'post_type'   => 'skylearn_flashcard',
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="skylearn_flashcard_sets"><?php esc_html_e( 'Flashcard Sets', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<select name="skylearn_flashcard_sets[]" id="skylearn_flashcard_sets" multiple style="width: 100%; height: 100px;">
						<?php foreach ( $available_sets as $set ) : ?>
							<option value="<?php echo esc_attr( $set->ID ); ?>" <?php echo in_array( $set->ID, (array) $flashcard_sets ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $set->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Select flashcard sets to include with this lesson.', 'skylearn-flashcards' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="skylearn_required_accuracy"><?php esc_html_e( 'Required Accuracy', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<input type="number" name="skylearn_required_accuracy" id="skylearn_required_accuracy" value="<?php echo esc_attr( $required_accuracy ); ?>" min="0" max="100" class="small-text">%
					<p class="description"><?php esc_html_e( 'Minimum accuracy required to complete this lesson via flashcards.', 'skylearn-flashcards' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
		
	}

}