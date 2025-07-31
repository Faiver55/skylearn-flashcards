<?php
/**
 * LMS Manager for SkyLearn Flashcards
 *
 * Handles detection, settings, and coordination of LMS integrations.
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/lms
 */

/**
 * LMS Manager class.
 *
 * Manages LMS detection, settings, and integration coordination.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/lms
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_LMS_Manager {

	/**
	 * Instance of LearnDash integration
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      SkyLearn_Flashcards_LearnDash
	 */
	private $learndash;

	/**
	 * Instance of TutorLMS integration
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      SkyLearn_Flashcards_TutorLMS
	 */
	private $tutorlms;

	/**
	 * Detected LMS systems
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array
	 */
	private $detected_lms = array();

	/**
	 * LMS integration settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array
	 */
	private $settings;

	/**
	 * Initialize LMS Manager
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		// Load settings
		$this->settings = get_option( 'skylearn_flashcards_lms_settings', $this->get_default_settings() );
		
		// Detect available LMS systems
		$this->detect_lms_systems();
		
		// Initialize integrations if enabled
		if ( $this->is_lms_integration_enabled() ) {
			$this->init_integrations();
		}
		
		// Add admin hooks
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		
		// Add meta boxes for LMS linking
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_lms_meta' ) );
		
	}

	/**
	 * Get default LMS settings
	 *
	 * @since    1.0.0
	 * @return   array   Default settings
	 */
	private function get_default_settings() {
		return array(
			'enabled'                => false,
			'auto_complete_lessons'  => true,
			'grade_submission'       => true,
			'required_accuracy'      => 80,
			'progress_tracking'      => true,
			'enrollment_restriction' => true,
		);
	}

	/**
	 * Detect available LMS systems
	 *
	 * @since    1.0.0
	 */
	private function detect_lms_systems() {
		
		$this->detected_lms = array();
		
		// Check for LearnDash
		if ( class_exists( 'SFWD_LMS' ) ) {
			$this->detected_lms['learndash'] = array(
				'name'    => __( 'LearnDash', 'skylearn-flashcards' ),
				'version' => defined( 'LEARNDASH_VERSION' ) ? LEARNDASH_VERSION : '0.0.0',
				'active'  => true,
			);
		}
		
		// Check for TutorLMS
		if ( function_exists( 'tutor' ) ) {
			$this->detected_lms['tutorlms'] = array(
				'name'    => __( 'TutorLMS', 'skylearn-flashcards' ),
				'version' => defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '0.0.0',
				'active'  => true,
			);
		}
		
	}

	/**
	 * Initialize LMS integrations
	 *
	 * @since    1.0.0
	 */
	private function init_integrations() {
		
		// Initialize LearnDash integration
		if ( $this->is_lms_available( 'learndash' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/lms/class-learndash.php';
			$this->learndash = new SkyLearn_Flashcards_LearnDash();
		}
		
		// Initialize TutorLMS integration
		if ( $this->is_lms_available( 'tutorlms' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/lms/class-tutorlms.php';
			$this->tutorlms = new SkyLearn_Flashcards_TutorLMS();
		}
		
	}

	/**
	 * Check if LMS integration is enabled
	 *
	 * @since    1.0.0
	 * @return   bool
	 */
	public function is_lms_integration_enabled() {
		return !empty( $this->settings['enabled'] ) && !empty( $this->detected_lms );
	}

	/**
	 * Check if specific LMS is available
	 *
	 * @since    1.0.0
	 * @param    string  $lms_key  LMS identifier
	 * @return   bool
	 */
	public function is_lms_available( $lms_key ) {
		return isset( $this->detected_lms[ $lms_key ] ) && $this->detected_lms[ $lms_key ]['active'];
	}

	/**
	 * Get detected LMS systems
	 *
	 * @since    1.0.0
	 * @return   array
	 */
	public function get_detected_lms() {
		return $this->detected_lms;
	}

	/**
	 * Get LMS settings
	 *
	 * @since    1.0.0
	 * @return   array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Update LMS settings
	 *
	 * @since    1.0.0
	 * @param    array  $new_settings  New settings
	 * @return   bool                  Success
	 */
	public function update_settings( $new_settings ) {
		$this->settings = wp_parse_args( $new_settings, $this->get_default_settings() );
		return update_option( 'skylearn_flashcards_lms_settings', $this->settings );
	}

	/**
	 * Admin initialization
	 *
	 * @since    1.0.0
	 */
	public function admin_init() {
		
		// Register settings
		register_setting( 'skylearn_flashcards_lms', 'skylearn_flashcards_lms_settings', array(
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
		) );
		
	}

	/**
	 * Sanitize LMS settings
	 *
	 * @since    1.0.0
	 * @param    array  $input  Input settings
	 * @return   array          Sanitized settings
	 */
	public function sanitize_settings( $input ) {
		
		$sanitized = array();
		
		$sanitized['enabled']                = !empty( $input['enabled'] );
		$sanitized['auto_complete_lessons']  = !empty( $input['auto_complete_lessons'] );
		$sanitized['grade_submission']       = !empty( $input['grade_submission'] );
		$sanitized['required_accuracy']      = absint( $input['required_accuracy'] );
		$sanitized['progress_tracking']      = !empty( $input['progress_tracking'] );
		$sanitized['enrollment_restriction'] = !empty( $input['enrollment_restriction'] );
		
		// Validate accuracy range
		if ( $sanitized['required_accuracy'] < 0 || $sanitized['required_accuracy'] > 100 ) {
			$sanitized['required_accuracy'] = 80;
		}
		
		return $sanitized;
		
	}

	/**
	 * Show admin notices
	 *
	 * @since    1.0.0
	 */
	public function admin_notices() {
		
		// Show LMS detection notice
		if ( !empty( $this->detected_lms ) && !$this->is_lms_integration_enabled() ) {
			$lms_names = wp_list_pluck( $this->detected_lms, 'name' );
			$settings_url = admin_url( 'admin.php?page=skylearn-flashcards&tab=lms' );
			
			echo '<div class="notice notice-info is-dismissible">';
			echo '<p>';
			printf(
				/* translators: %1$s: LMS names, %2$s: settings URL */
				__( 'SkyLearn Flashcards detected %1$s. <a href="%2$s">Enable LMS integration</a> to enhance your flashcards with course progress tracking and grading.', 'skylearn-flashcards' ),
				implode( ', ', $lms_names ),
				esc_url( $settings_url )
			);
			echo '</p>';
			echo '</div>';
		}
		
	}

	/**
	 * Add meta boxes for LMS linking
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		
		if ( !$this->is_lms_integration_enabled() ) {
			return;
		}
		
		// Add to flashcard sets
		add_meta_box(
			'skylearn-lms-integration',
			__( 'LMS Integration', 'skylearn-flashcards' ),
			array( $this, 'render_lms_meta_box' ),
			'flashcard_set',
			'side',
			'default'
		);
		
	}

	/**
	 * Render LMS integration meta box
	 *
	 * @since    1.0.0
	 * @param    WP_Post  $post  Post object
	 */
	public function render_lms_meta_box( $post ) {
		
		wp_nonce_field( 'skylearn_lms_meta', 'skylearn_lms_nonce' );
		
		$lms_linked_courses = get_post_meta( $post->ID, '_skylearn_lms_courses', true );
		$lms_linked_lessons = get_post_meta( $post->ID, '_skylearn_lms_lessons', true );
		$visibility_setting = get_post_meta( $post->ID, '_skylearn_lms_visibility', true ) ?: 'all';
		
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="skylearn_lms_visibility"><?php esc_html_e( 'Visibility', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<select name="skylearn_lms_visibility" id="skylearn_lms_visibility" style="width: 100%;">
						<option value="all" <?php selected( $visibility_setting, 'all' ); ?>><?php esc_html_e( 'All Users', 'skylearn-flashcards' ); ?></option>
						<option value="enrolled" <?php selected( $visibility_setting, 'enrolled' ); ?>><?php esc_html_e( 'Enrolled Users Only', 'skylearn-flashcards' ); ?></option>
						<option value="completed" <?php selected( $visibility_setting, 'completed' ); ?>><?php esc_html_e( 'Users Who Completed Course/Lesson', 'skylearn-flashcards' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Control who can see this flashcard set based on LMS enrollment.', 'skylearn-flashcards' ); ?></p>
				</td>
			</tr>
			
			<?php if ( $this->is_lms_available( 'learndash' ) ) : ?>
				<tr>
					<th scope="row">
						<label for="skylearn_learndash_courses"><?php esc_html_e( 'LearnDash Courses', 'skylearn-flashcards' ); ?></label>
					</th>
					<td>
						<?php $this->render_learndash_course_selector( $lms_linked_courses ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="skylearn_learndash_lessons"><?php esc_html_e( 'LearnDash Lessons', 'skylearn-flashcards' ); ?></label>
					</th>
					<td>
						<?php $this->render_learndash_lesson_selector( $lms_linked_lessons ); ?>
					</td>
				</tr>
			<?php endif; ?>
			
			<?php if ( $this->is_lms_available( 'tutorlms' ) ) : ?>
				<tr>
					<th scope="row">
						<label for="skylearn_tutor_courses"><?php esc_html_e( 'TutorLMS Courses', 'skylearn-flashcards' ); ?></label>
					</th>
					<td>
						<?php $this->render_tutor_course_selector( $lms_linked_courses ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</table>
		<?php
		
	}

	/**
	 * Render LearnDash course selector
	 *
	 * @since    1.0.0
	 * @param    array  $selected_courses  Selected course IDs
	 */
	private function render_learndash_course_selector( $selected_courses ) {
		
		$courses = get_posts( array(
			'post_type'   => 'sfwd-courses',
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		?>
		<select name="skylearn_learndash_courses[]" id="skylearn_learndash_courses" multiple style="width: 100%; height: 100px;">
			<?php foreach ( $courses as $course ) : ?>
				<option value="<?php echo esc_attr( $course->ID ); ?>" 
					<?php echo in_array( $course->ID, (array) $selected_courses ) ? 'selected' : ''; ?>>
					<?php echo esc_html( $course->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Link this flashcard set to LearnDash courses.', 'skylearn-flashcards' ); ?></p>
		<?php
		
	}

	/**
	 * Render LearnDash lesson selector
	 *
	 * @since    1.0.0
	 * @param    array  $selected_lessons  Selected lesson IDs
	 */
	private function render_learndash_lesson_selector( $selected_lessons ) {
		
		$lessons = get_posts( array(
			'post_type'   => 'sfwd-lessons',
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		?>
		<select name="skylearn_learndash_lessons[]" id="skylearn_learndash_lessons" multiple style="width: 100%; height: 100px;">
			<?php foreach ( $lessons as $lesson ) : ?>
				<option value="<?php echo esc_attr( $lesson->ID ); ?>" 
					<?php echo in_array( $lesson->ID, (array) $selected_lessons ) ? 'selected' : ''; ?>>
					<?php echo esc_html( $lesson->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Link this flashcard set to specific LearnDash lessons.', 'skylearn-flashcards' ); ?></p>
		<?php
		
	}

	/**
	 * Render TutorLMS course selector
	 *
	 * @since    1.0.0
	 * @param    array  $selected_courses  Selected course IDs
	 */
	private function render_tutor_course_selector( $selected_courses ) {
		
		$courses = get_posts( array(
			'post_type'   => 'courses',
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		?>
		<select name="skylearn_tutor_courses[]" id="skylearn_tutor_courses" multiple style="width: 100%; height: 100px;">
			<?php foreach ( $courses as $course ) : ?>
				<option value="<?php echo esc_attr( $course->ID ); ?>" 
					<?php echo in_array( $course->ID, (array) $selected_courses ) ? 'selected' : ''; ?>>
					<?php echo esc_html( $course->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Link this flashcard set to TutorLMS courses.', 'skylearn-flashcards' ); ?></p>
		<?php
		
	}

	/**
	 * Save LMS meta data
	 *
	 * @since    1.0.0
	 * @param    int  $post_id  Post ID
	 */
	public function save_lms_meta( $post_id ) {
		
		// Check nonce
		if ( !isset( $_POST['skylearn_lms_nonce'] ) || !wp_verify_nonce( $_POST['skylearn_lms_nonce'], 'skylearn_lms_meta' ) ) {
			return;
		}
		
		// Check permissions
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Check post type
		if ( get_post_type( $post_id ) !== 'flashcard_set' ) {
			return;
		}
		
		// Save visibility setting
		if ( isset( $_POST['skylearn_lms_visibility'] ) ) {
			update_post_meta( $post_id, '_skylearn_lms_visibility', sanitize_text_field( $_POST['skylearn_lms_visibility'] ) );
		}
		
		// Save LearnDash course associations
		if ( isset( $_POST['skylearn_learndash_courses'] ) ) {
			$courses = array_map( 'absint', $_POST['skylearn_learndash_courses'] );
			update_post_meta( $post_id, '_skylearn_lms_courses', $courses );
		} else {
			delete_post_meta( $post_id, '_skylearn_lms_courses' );
		}
		
		// Save LearnDash lesson associations
		if ( isset( $_POST['skylearn_learndash_lessons'] ) ) {
			$lessons = array_map( 'absint', $_POST['skylearn_learndash_lessons'] );
			update_post_meta( $post_id, '_skylearn_lms_lessons', $lessons );
		} else {
			delete_post_meta( $post_id, '_skylearn_lms_lessons' );
		}
		
		// Save TutorLMS course associations
		if ( isset( $_POST['skylearn_tutor_courses'] ) ) {
			$courses = array_map( 'absint', $_POST['skylearn_tutor_courses'] );
			update_post_meta( $post_id, '_skylearn_tutor_courses', $courses );
		} else {
			delete_post_meta( $post_id, '_skylearn_tutor_courses' );
		}
		
	}

	/**
	 * Check if user has access to flashcard set based on LMS enrollment
	 *
	 * @since    1.0.0
	 * @param    int  $flashcard_set_id  Flashcard set ID
	 * @param    int  $user_id           User ID (optional, defaults to current user)
	 * @return   bool                    True if user has access
	 */
	public function user_has_access( $flashcard_set_id, $user_id = null ) {
		
		if ( !$this->is_lms_integration_enabled() ) {
			return true;
		}
		
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Admin always has access
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		
		$visibility = get_post_meta( $flashcard_set_id, '_skylearn_lms_visibility', true );
		
		// All users can access
		if ( 'all' === $visibility || empty( $visibility ) ) {
			return true;
		}
		
		// Check enrollment/completion status
		$has_access = false;
		
		// Check LearnDash
		if ( $this->is_lms_available( 'learndash' ) && $this->learndash ) {
			$has_access = $this->check_learndash_access( $flashcard_set_id, $user_id, $visibility );
		}
		
		// Check TutorLMS
		if ( !$has_access && $this->is_lms_available( 'tutorlms' ) && $this->tutorlms ) {
			$has_access = $this->check_tutor_access( $flashcard_set_id, $user_id, $visibility );
		}
		
		return $has_access;
		
	}

	/**
	 * Check LearnDash access for user
	 *
	 * @since    1.0.0
	 * @param    int     $flashcard_set_id  Flashcard set ID
	 * @param    int     $user_id           User ID
	 * @param    string  $visibility        Visibility setting
	 * @return   bool                       True if user has access
	 */
	private function check_learndash_access( $flashcard_set_id, $user_id, $visibility ) {
		
		$linked_courses = get_post_meta( $flashcard_set_id, '_skylearn_lms_courses', true );
		$linked_lessons = get_post_meta( $flashcard_set_id, '_skylearn_lms_lessons', true );
		
		// Check course enrollment/completion
		if ( !empty( $linked_courses ) ) {
			foreach ( $linked_courses as $course_id ) {
				if ( 'enrolled' === $visibility ) {
					if ( sfwd_lms_has_access( $course_id, $user_id ) ) {
						return true;
					}
				} elseif ( 'completed' === $visibility ) {
					if ( learndash_course_completed( $user_id, $course_id ) ) {
						return true;
					}
				}
			}
		}
		
		// Check lesson completion
		if ( !empty( $linked_lessons ) ) {
			foreach ( $linked_lessons as $lesson_id ) {
				if ( 'enrolled' === $visibility ) {
					$course_id = learndash_get_course_id( $lesson_id );
					if ( $course_id && sfwd_lms_has_access( $course_id, $user_id ) ) {
						return true;
					}
				} elseif ( 'completed' === $visibility ) {
					if ( learndash_is_lesson_complete( $user_id, $lesson_id ) ) {
						return true;
					}
				}
			}
		}
		
		return false;
		
	}

	/**
	 * Check TutorLMS access for user
	 *
	 * @since    1.0.0
	 * @param    int     $flashcard_set_id  Flashcard set ID
	 * @param    int     $user_id           User ID
	 * @param    string  $visibility        Visibility setting
	 * @return   bool                       True if user has access
	 */
	private function check_tutor_access( $flashcard_set_id, $user_id, $visibility ) {
		
		$linked_courses = get_post_meta( $flashcard_set_id, '_skylearn_tutor_courses', true );
		
		// Check course enrollment/completion
		if ( !empty( $linked_courses ) ) {
			foreach ( $linked_courses as $course_id ) {
				if ( 'enrolled' === $visibility ) {
					if ( tutor_utils()->is_enrolled( $course_id, $user_id ) ) {
						return true;
					}
				} elseif ( 'completed' === $visibility ) {
					if ( tutor_utils()->is_completed_course( $course_id, $user_id ) ) {
						return true;
					}
				}
			}
		}
		
		return false;
		
	}

	/**
	 * Track flashcard completion in LMS
	 *
	 * @since    1.0.0
	 * @param    int    $flashcard_set_id  Flashcard set ID
	 * @param    int    $user_id           User ID
	 * @param    float  $accuracy          Accuracy percentage
	 */
	public function track_completion( $flashcard_set_id, $user_id, $accuracy ) {
		
		if ( !$this->is_lms_integration_enabled() ) {
			return;
		}
		
		// Track in LearnDash
		if ( $this->is_lms_available( 'learndash' ) && $this->learndash ) {
			$this->learndash->track_progress( $user_id, $flashcard_set_id, $accuracy );
		}
		
		// Track in TutorLMS
		if ( $this->is_lms_available( 'tutorlms' ) && $this->tutorlms ) {
			$this->tutorlms->track_progress( $user_id, $flashcard_set_id, $accuracy );
		}
		
	}

}