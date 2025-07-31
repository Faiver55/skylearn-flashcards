<?php
/**
 * TutorLMS integration for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/lms
 */

/**
 * TutorLMS integration class.
 *
 * Provides integration with TutorLMS.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/lms
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_TutorLMS {

	/**
	 * Initialize TutorLMS integration
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		// Only load if TutorLMS is active
		if ( ! function_exists( 'tutor' ) ) {
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
		add_action( 'tutor_lesson/single/lesson/content', array( $this, 'render_lesson_flashcards' ), 15 );
		
		// Track progress in TutorLMS
		add_action( 'skylearn_flashcard_completed', array( $this, 'track_progress' ), 10, 3 );
		
		// Add flashcards metabox to lessons
		add_action( 'add_meta_boxes', array( $this, 'add_lesson_metabox' ) );
		
		// Save lesson metabox data
		add_action( 'save_post', array( $this, 'save_lesson_meta' ) );
		
		// Add course metabox
		add_action( 'add_meta_boxes', array( $this, 'add_course_metabox' ) );
		add_action( 'save_post', array( $this, 'save_course_meta' ) );
		
		// Add flashcards tab to course single page
		add_filter( 'tutor_course/single/nav_items', array( $this, 'add_course_flashcards_tab' ) );
		add_action( 'tutor_course/single/tab/flashcards', array( $this, 'render_course_flashcards_tab' ) );
		
	}

	/**
	 * Render flashcards in lesson content
	 *
	 * @since    1.0.0
	 */
	public function render_lesson_flashcards() {
		
		global $post;
		
		if ( ! $post || $post->post_type !== 'lesson' ) {
			return;
		}
		
		// Get associated flashcard sets
		$flashcard_sets = get_post_meta( $post->ID, '_skylearn_flashcard_sets', true );
		
		if ( empty( $flashcard_sets ) ) {
			return;
		}
		
		// Check if user is enrolled
		$course_id = tutor_utils()->get_course_id_by_lesson( $post->ID );
		$user_id = get_current_user_id();
		
		if ( ! tutor_utils()->is_enrolled( $course_id, $user_id ) ) {
			return;
		}
		
		echo '<div class="skylearn-tutor-flashcards">';
		echo '<h4>' . esc_html__( 'Study with Flashcards', 'skylearn-flashcards' ) . '</h4>';
		echo '<p>' . esc_html__( 'Reinforce your learning with these interactive flashcards.', 'skylearn-flashcards' ) . '</p>';
		
		foreach ( $flashcard_sets as $set_id ) {
			$set_id = absint( $set_id );
			if ( ! $set_id ) {
				continue;
			}
			
			echo do_shortcode( "[skylearn_flashcards id=\"{$set_id}\"]" );
		}
		
		echo '</div>';
		
	}

	/**
	 * Track flashcard progress in TutorLMS
	 *
	 * @since    1.0.0
	 * @param    int     $user_id     User ID
	 * @param    int     $set_id      Flashcard set ID
	 * @param    float   $accuracy    Accuracy percentage
	 */
	public function track_progress( $user_id, $set_id, $accuracy ) {
		
		// Find associated lesson or course
		$lessons = get_posts( array(
			'post_type'  => 'lesson',
			'meta_query' => array(
				array(
					'key'     => '_skylearn_flashcard_sets',
					'value'   => $set_id,
					'compare' => 'LIKE',
				),
			),
		) );
		
		$courses = get_posts( array(
			'post_type'  => 'courses',
			'meta_query' => array(
				array(
					'key'     => '_skylearn_flashcard_sets',
					'value'   => $set_id,
					'compare' => 'LIKE',
				),
			),
		) );
		
		// Update progress for lessons
		if ( ! empty( $lessons ) ) {
			foreach ( $lessons as $lesson ) {
				$this->update_lesson_progress( $user_id, $lesson->ID, $set_id, $accuracy );
			}
		}
		
		// Update progress for courses
		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course ) {
				$this->update_course_progress( $user_id, $course->ID, $set_id, $accuracy );
			}
		}
		
	}

	/**
	 * Update lesson progress
	 *
	 * @since    1.0.0
	 * @param    int     $user_id    User ID
	 * @param    int     $lesson_id  Lesson ID
	 * @param    int     $set_id     Flashcard set ID
	 * @param    float   $accuracy   Accuracy percentage
	 */
	private function update_lesson_progress( $user_id, $lesson_id, $set_id, $accuracy ) {
		
		// Update user progress
		$progress = get_user_meta( $user_id, "_skylearn_flashcard_progress_{$lesson_id}", true );
		if ( ! is_array( $progress ) ) {
			$progress = array();
		}
		
		$progress[ $set_id ] = array(
			'accuracy'     => $accuracy,
			'completed'    => true,
			'completed_at' => current_time( 'mysql' ),
		);
		
		update_user_meta( $user_id, "_skylearn_flashcard_progress_{$lesson_id}", $progress );
		
		// Check if lesson should be marked complete
		$this->check_lesson_completion( $user_id, $lesson_id );
		
		// Submit grade to TutorLMS if enabled
		$this->submit_lesson_grade( $user_id, $lesson_id, $accuracy );
		
	}

	/**
	 * Update course progress
	 *
	 * @since    1.0.0
	 * @param    int     $user_id    User ID
	 * @param    int     $course_id  Course ID
	 * @param    int     $set_id     Flashcard set ID
	 * @param    float   $accuracy   Accuracy percentage
	 */
	private function update_course_progress( $user_id, $course_id, $set_id, $accuracy ) {
		
		// Update user progress
		$progress = get_user_meta( $user_id, "_skylearn_flashcard_progress_course_{$course_id}", true );
		if ( ! is_array( $progress ) ) {
			$progress = array();
		}
		
		$progress[ $set_id ] = array(
			'accuracy'     => $accuracy,
			'completed'    => true,
			'completed_at' => current_time( 'mysql' ),
		);
		
		update_user_meta( $user_id, "_skylearn_flashcard_progress_course_{$course_id}", $progress );
		
		// Submit grade to TutorLMS
		$this->submit_course_grade( $user_id, $course_id, $accuracy );
		
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
				// Mark lesson as complete in TutorLMS
				tutor_utils()->mark_lesson_complete( $lesson_id, $user_id );
			}
		}
		
	}

	/**
	 * Submit lesson grade to TutorLMS
	 *
	 * @since    1.0.0
	 * @param    int     $user_id    User ID
	 * @param    int     $lesson_id  Lesson ID
	 * @param    float   $accuracy   Accuracy percentage
	 */
	private function submit_lesson_grade( $user_id, $lesson_id, $accuracy ) {
		
		// Create a quiz attempt record for flashcard completion
		global $wpdb;
		
		$course_id = tutor_utils()->get_course_id_by_lesson( $lesson_id );
		
		// Check if grade submission is enabled
		$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
		$settings = $lms_manager->get_settings();
		
		if ( empty( $settings['grade_submission'] ) ) {
			return;
		}
		
		// Insert flashcard completion as a quiz attempt
		$attempt_data = array(
			'course_id'          => $course_id,
			'quiz_id'            => 0, // No specific quiz
			'user_id'            => $user_id,
			'total_questions'    => 100, // Representing percentage scale
			'total_answered_questions' => 100,
			'total_marks'        => 100,
			'earned_marks'       => $accuracy,
			'attempt_info'       => wp_json_encode( array(
				'flashcard_lesson' => $lesson_id,
				'type'            => 'flashcard_completion',
			) ),
			'attempt_status'     => 'attempt_ended',
			'attempt_ip'         => tutor_utils()->get_ip(),
			'attempt_started_at' => current_time( 'mysql' ),
			'attempt_ended_at'   => current_time( 'mysql' ),
		);
		
		$wpdb->insert( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_data );
		
	}

	/**
	 * Submit course grade to TutorLMS
	 *
	 * @since    1.0.0
	 * @param    int     $user_id    User ID
	 * @param    int     $course_id  Course ID
	 * @param    float   $accuracy   Accuracy percentage
	 */
	private function submit_course_grade( $user_id, $course_id, $accuracy ) {
		
		// Check if grade submission is enabled
		$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
		$settings = $lms_manager->get_settings();
		
		if ( empty( $settings['grade_submission'] ) ) {
			return;
		}
		
		// Update course progress with flashcard completion
		$progress_key = "tutor_course_progress_{$course_id}";
		$progress = get_user_meta( $user_id, $progress_key, true );
		
		if ( ! is_array( $progress ) ) {
			$progress = array();
		}
		
		$progress['flashcard_completion'] = array(
			'accuracy'     => $accuracy,
			'completed_at' => current_time( 'mysql' ),
		);
		
		update_user_meta( $user_id, $progress_key, $progress );
		
	}

	/**
	 * Add metabox to lesson edit screen
	 *
	 * @since    1.0.0
	 */
	public function add_lesson_metabox() {
		
		add_meta_box(
			'skylearn-flashcards-lesson-metabox',
			__( 'SkyLearn Flashcards', 'skylearn-flashcards' ),
			array( $this, 'render_lesson_metabox' ),
			'lesson',
			'side',
			'default'
		);
		
	}

	/**
	 * Add metabox to course edit screen
	 *
	 * @since    1.0.0
	 */
	public function add_course_metabox() {
		
		add_meta_box(
			'skylearn-flashcards-course-metabox',
			__( 'SkyLearn Flashcards', 'skylearn-flashcards' ),
			array( $this, 'render_course_metabox' ),
			'courses',
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
		
		wp_nonce_field( 'skylearn_tutor_lesson_flashcards', 'skylearn_tutor_lesson_nonce' );
		
		$flashcard_sets = get_post_meta( $post->ID, '_skylearn_flashcard_sets', true );
		$required_accuracy = get_post_meta( $post->ID, '_skylearn_required_accuracy', true ) ?: 80;
		
		// Get available flashcard sets
		$available_sets = get_posts( array(
			'post_type'   => 'flashcard_set',
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

	/**
	 * Render course metabox
	 *
	 * @since    1.0.0
	 * @param    WP_Post  $post  Post object
	 */
	public function render_course_metabox( $post ) {
		
		wp_nonce_field( 'skylearn_tutor_course_flashcards', 'skylearn_tutor_course_nonce' );
		
		$flashcard_sets = get_post_meta( $post->ID, '_skylearn_flashcard_sets', true );
		
		// Get available flashcard sets
		$available_sets = get_posts( array(
			'post_type'   => 'flashcard_set',
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="skylearn_course_flashcard_sets"><?php esc_html_e( 'Course Flashcard Sets', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<select name="skylearn_course_flashcard_sets[]" id="skylearn_course_flashcard_sets" multiple style="width: 100%; height: 100px;">
						<?php foreach ( $available_sets as $set ) : ?>
							<option value="<?php echo esc_attr( $set->ID ); ?>" <?php echo in_array( $set->ID, (array) $flashcard_sets ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $set->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Select flashcard sets to include as supplementary material for this course.', 'skylearn-flashcards' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
		
	}

	/**
	 * Save lesson meta data
	 *
	 * @since    1.0.0
	 * @param    int  $post_id  Post ID
	 */
	public function save_lesson_meta( $post_id ) {
		
		// Check nonce
		if ( ! isset( $_POST['skylearn_tutor_lesson_nonce'] ) || ! wp_verify_nonce( $_POST['skylearn_tutor_lesson_nonce'], 'skylearn_tutor_lesson_flashcards' ) ) {
			return;
		}
		
		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Check post type
		if ( get_post_type( $post_id ) !== 'lesson' ) {
			return;
		}
		
		// Save flashcard sets
		if ( isset( $_POST['skylearn_flashcard_sets'] ) ) {
			$sets = array_map( 'absint', $_POST['skylearn_flashcard_sets'] );
			update_post_meta( $post_id, '_skylearn_flashcard_sets', $sets );
		} else {
			delete_post_meta( $post_id, '_skylearn_flashcard_sets' );
		}
		
		// Save required accuracy
		if ( isset( $_POST['skylearn_required_accuracy'] ) ) {
			$accuracy = absint( $_POST['skylearn_required_accuracy'] );
			update_post_meta( $post_id, '_skylearn_required_accuracy', $accuracy );
		}
		
	}

	/**
	 * Save course meta data
	 *
	 * @since    1.0.0
	 * @param    int  $post_id  Post ID
	 */
	public function save_course_meta( $post_id ) {
		
		// Check nonce
		if ( ! isset( $_POST['skylearn_tutor_course_nonce'] ) || ! wp_verify_nonce( $_POST['skylearn_tutor_course_nonce'], 'skylearn_tutor_course_flashcards' ) ) {
			return;
		}
		
		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Check post type
		if ( get_post_type( $post_id ) !== 'courses' ) {
			return;
		}
		
		// Save flashcard sets for course
		if ( isset( $_POST['skylearn_course_flashcard_sets'] ) ) {
			$sets = array_map( 'absint', $_POST['skylearn_course_flashcard_sets'] );
			update_post_meta( $post_id, '_skylearn_flashcard_sets', $sets );
		} else {
			delete_post_meta( $post_id, '_skylearn_flashcard_sets' );
		}
		
	}

	/**
	 * Add flashcards tab to course navigation
	 *
	 * @since    1.0.0
	 * @param    array  $nav_items  Course navigation items
	 * @return   array              Modified navigation items
	 */
	public function add_course_flashcards_tab( $nav_items ) {
		
		global $post;
		
		if ( ! $post || $post->post_type !== 'courses' ) {
			return $nav_items;
		}
		
		// Check if course has flashcard sets
		$flashcard_sets = get_post_meta( $post->ID, '_skylearn_flashcard_sets', true );
		
		if ( empty( $flashcard_sets ) ) {
			return $nav_items;
		}
		
		$nav_items['flashcards'] = array(
			'title' => __( 'Flashcards', 'skylearn-flashcards' ),
			'method' => 'flashcards',
		);
		
		return $nav_items;
		
	}

	/**
	 * Render course flashcards tab content
	 *
	 * @since    1.0.0
	 */
	public function render_course_flashcards_tab() {
		
		global $post;
		
		if ( ! $post || $post->post_type !== 'courses' ) {
			return;
		}
		
		// Check if user is enrolled
		$user_id = get_current_user_id();
		if ( ! tutor_utils()->is_enrolled( $post->ID, $user_id ) ) {
			echo '<div class="tutor-alert-warning">';
			echo '<p>' . esc_html__( 'You must be enrolled in this course to access flashcards.', 'skylearn-flashcards' ) . '</p>';
			echo '</div>';
			return;
		}
		
		// Get associated flashcard sets
		$flashcard_sets = get_post_meta( $post->ID, '_skylearn_flashcard_sets', true );
		
		if ( empty( $flashcard_sets ) ) {
			return;
		}
		
		?>
		<div class="skylearn-tutor-course-flashcards">
			<h3><?php esc_html_e( 'Course Flashcards', 'skylearn-flashcards' ); ?></h3>
			<p><?php esc_html_e( 'Study these flashcard sets to reinforce key concepts from this course.', 'skylearn-flashcards' ); ?></p>
			
			<?php foreach ( $flashcard_sets as $set_id ) : ?>
				<?php
				$set_id = absint( $set_id );
				if ( ! $set_id ) {
					continue;
				}
				
				echo do_shortcode( "[skylearn_flashcards id=\"{$set_id}\"]" );
				?>
			<?php endforeach; ?>
		</div>
		<?php
		
	}

}