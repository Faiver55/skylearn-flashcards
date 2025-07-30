<?php
/**
 * LMS Integration Settings View
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin/views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get LMS Manager instance
$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
$detected_lms = $lms_manager->get_detected_lms();
$lms_settings = $lms_manager->get_settings();

?>

<div class="skylearn-card">
	<div class="skylearn-card-header">
		<h2 class="skylearn-card-title"><?php esc_html_e( 'LMS Integration', 'skylearn-flashcards' ); ?></h2>
	</div>
	
	<?php settings_errors( 'skylearn_flashcards_lms_settings' ); ?>
	
	<table class="skylearn-form-table" role="presentation">
		<tbody>
			
			<!-- LMS Detection Section -->
			<tr>
				<th scope="row">
					<strong><?php esc_html_e( 'Detected LMS', 'skylearn-flashcards' ); ?></strong>
				</th>
				<td>
					<?php if ( !empty( $detected_lms ) ) : ?>
						<div class="skylearn-lms-detected">
							<?php foreach ( $detected_lms as $lms_key => $lms_data ) : ?>
								<div class="skylearn-lms-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
									<strong style="color: <?php echo esc_attr( SKYLEARN_FLASHCARDS_COLOR_PRIMARY ); ?>;">
										<span class="dashicons dashicons-yes-alt" style="color: #46b450; margin-right: 5px;"></span>
										<?php echo esc_html( $lms_data['name'] ); ?>
									</strong>
									<span style="color: #666; margin-left: 10px;">
										<?php
										printf(
											/* translators: %s: LMS version */
											esc_html__( 'Version: %s', 'skylearn-flashcards' ),
											esc_html( $lms_data['version'] )
										);
										?>
									</span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="skylearn-no-lms" style="padding: 15px; border: 1px solid #dc3232; border-radius: 4px; background: #fef7f7;">
							<p style="margin: 0; color: #721c24;">
								<span class="dashicons dashicons-warning" style="color: #dc3232; margin-right: 5px;"></span>
								<?php esc_html_e( 'No supported LMS detected. Install LearnDash or TutorLMS to enable integration.', 'skylearn-flashcards' ); ?>
							</p>
						</div>
					<?php endif; ?>
				</td>
			</tr>
			
			<?php if ( !empty( $detected_lms ) ) : ?>
			
			<!-- Enable Integration -->
			<tr>
				<th scope="row">
					<label for="skylearn_lms_enabled"><?php esc_html_e( 'Enable LMS Integration', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<label for="skylearn_lms_enabled">
						<input 
							type="hidden" 
							name="skylearn_flashcards_lms_settings[enabled]" 
							value="0"
						>
						<input 
							type="checkbox" 
							name="skylearn_flashcards_lms_settings[enabled]" 
							id="skylearn_lms_enabled" 
							value="1" 
							<?php checked( $lms_settings['enabled'] ); ?>
						>
						<?php esc_html_e( 'Enable LMS integration features', 'skylearn-flashcards' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Enables progress tracking, grading, and course enrollment restrictions for flashcard sets.', 'skylearn-flashcards' ); ?>
					</p>
				</td>
			</tr>
			
			<!-- Progress Tracking -->
			<tr>
				<th scope="row">
					<label for="skylearn_lms_progress_tracking"><?php esc_html_e( 'Progress Tracking', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<label for="skylearn_lms_progress_tracking">
						<input 
							type="hidden" 
							name="skylearn_flashcards_lms_settings[progress_tracking]" 
							value="0"
						>
						<input 
							type="checkbox" 
							name="skylearn_flashcards_lms_settings[progress_tracking]" 
							id="skylearn_lms_progress_tracking" 
							value="1" 
							<?php checked( $lms_settings['progress_tracking'] ); ?>
						>
						<?php esc_html_e( 'Track flashcard progress in LMS', 'skylearn-flashcards' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Store user progress and completion data in the LMS user profile.', 'skylearn-flashcards' ); ?>
					</p>
				</td>
			</tr>
			
			<!-- Auto Complete Lessons -->
			<tr>
				<th scope="row">
					<label for="skylearn_lms_auto_complete"><?php esc_html_e( 'Auto Complete Lessons', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<label for="skylearn_lms_auto_complete">
						<input 
							type="hidden" 
							name="skylearn_flashcards_lms_settings[auto_complete_lessons]" 
							value="0"
						>
						<input 
							type="checkbox" 
							name="skylearn_flashcards_lms_settings[auto_complete_lessons]" 
							id="skylearn_lms_auto_complete" 
							value="1" 
							<?php checked( $lms_settings['auto_complete_lessons'] ); ?>
						>
						<?php esc_html_e( 'Automatically mark lessons complete when flashcards are finished', 'skylearn-flashcards' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'When enabled, completing linked flashcard sets will mark associated lessons as complete in the LMS.', 'skylearn-flashcards' ); ?>
					</p>
				</td>
			</tr>
			
			<!-- Grade Submission -->
			<tr>
				<th scope="row">
					<label for="skylearn_lms_grade_submission"><?php esc_html_e( 'Grade Submission', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<label for="skylearn_lms_grade_submission">
						<input 
							type="hidden" 
							name="skylearn_flashcards_lms_settings[grade_submission]" 
							value="0"
						>
						<input 
							type="checkbox" 
							name="skylearn_flashcards_lms_settings[grade_submission]" 
							id="skylearn_lms_grade_submission" 
							value="1" 
							<?php checked( $lms_settings['grade_submission'] ); ?>
						>
						<?php esc_html_e( 'Submit flashcard scores to LMS gradebook', 'skylearn-flashcards' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Send flashcard completion scores to the LMS for tracking and reporting.', 'skylearn-flashcards' ); ?>
					</p>
				</td>
			</tr>
			
			<!-- Required Accuracy -->
			<tr>
				<th scope="row">
					<label for="skylearn_lms_required_accuracy"><?php esc_html_e( 'Required Accuracy', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<input 
						type="number" 
						name="skylearn_flashcards_lms_settings[required_accuracy]" 
						id="skylearn_lms_required_accuracy" 
						value="<?php echo esc_attr( $lms_settings['required_accuracy'] ); ?>" 
						min="0" 
						max="100" 
						class="small-text"
						step="1"
					> %
					<p class="description">
						<?php esc_html_e( 'Minimum accuracy percentage required to pass flashcard sets and trigger lesson completion.', 'skylearn-flashcards' ); ?>
					</p>
				</td>
			</tr>
			
			<!-- Enrollment Restriction -->
			<tr>
				<th scope="row">
					<label for="skylearn_lms_enrollment_restriction"><?php esc_html_e( 'Enrollment Restrictions', 'skylearn-flashcards' ); ?></label>
				</th>
				<td>
					<label for="skylearn_lms_enrollment_restriction">
						<input 
							type="hidden" 
							name="skylearn_flashcards_lms_settings[enrollment_restriction]" 
							value="0"
						>
						<input 
							type="checkbox" 
							name="skylearn_flashcards_lms_settings[enrollment_restriction]" 
							id="skylearn_lms_enrollment_restriction" 
							value="1" 
							<?php checked( $lms_settings['enrollment_restriction'] ); ?>
						>
						<?php esc_html_e( 'Restrict access to flashcards based on course enrollment', 'skylearn-flashcards' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Only allow enrolled students to access flashcard sets linked to their courses.', 'skylearn-flashcards' ); ?>
					</p>
				</td>
			</tr>
			
			<?php endif; ?>
			
		</tbody>
	</table>
	
	<?php if ( !empty( $detected_lms ) ) : ?>
		<div class="skylearn-lms-integration-help" style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f0f8ff;">
			<h4 style="margin-top: 0; color: <?php echo esc_attr( SKYLEARN_FLASHCARDS_COLOR_PRIMARY ); ?>;">
				<span class="dashicons dashicons-info" style="margin-right: 5px;"></span>
				<?php esc_html_e( 'How LMS Integration Works', 'skylearn-flashcards' ); ?>
			</h4>
			
			<div class="skylearn-help-content">
				<p><strong><?php esc_html_e( '1. Link Flashcards to Courses/Lessons', 'skylearn-flashcards' ); ?></strong><br>
				<?php esc_html_e( 'When editing flashcard sets, use the "LMS Integration" metabox to link sets to specific courses or lessons in your LMS.', 'skylearn-flashcards' ); ?></p>
				
				<p><strong><?php esc_html_e( '2. Control Access', 'skylearn-flashcards' ); ?></strong><br>
				<?php esc_html_e( 'Set visibility rules to show flashcards only to enrolled students, or students who have completed specific prerequisites.', 'skylearn-flashcards' ); ?></p>
				
				<p><strong><?php esc_html_e( '3. Track Progress', 'skylearn-flashcards' ); ?></strong><br>
				<?php esc_html_e( 'Student progress and scores are automatically tracked in your LMS, and can trigger lesson completion when accuracy requirements are met.', 'skylearn-flashcards' ); ?></p>
			</div>
		</div>
	<?php endif; ?>
</div>

<style>
.skylearn-lms-detected .skylearn-lms-item:hover {
	background: #f0f8ff !important;
	border-color: <?php echo esc_attr( SKYLEARN_FLASHCARDS_COLOR_PRIMARY ); ?> !important;
}
</style>