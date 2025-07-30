<?php
/**
 * Provide an admin settings page view for the plugin
 *
 * This file is used to markup the admin-facing settings page.
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin/views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current settings
$settings = get_option( 'skylearn_flashcards_settings', skylearn_get_default_set_settings() );

?>

<div class="skylearn-admin-wrap">
	
	<div class="skylearn-admin-header">
		<div class="skylearn-header-content">
			<div class="skylearn-header-logo">
				<img src="<?php echo esc_url( SKYLEARN_FLASHCARDS_LOGO . 'logo-horiz.png' ); ?>" 
					 alt="<?php esc_attr_e( 'SkyLearn Flashcards', 'skylearn-flashcards' ); ?>" 
					 class="skylearn-logo-horizontal">
			</div>
			<div class="skylearn-header-text">
				<h1><?php esc_html_e( 'SkyLearn Flashcards Settings', 'skylearn-flashcards' ); ?></h1>
				<p class="skylearn-header-tagline"><?php esc_html_e( 'Configure your flashcard settings and preferences', 'skylearn-flashcards' ); ?></p>
			</div>
		</div>
	</div>
	
	<div class="skylearn-admin-content">
		
		<!-- Settings Navigation -->
		<nav class="skylearn-nav-tabs">
			<a href="#general" class="active"><?php esc_html_e( 'General', 'skylearn-flashcards' ); ?></a>
			<a href="#appearance"><?php esc_html_e( 'Appearance', 'skylearn-flashcards' ); ?></a>
			<a href="#behavior"><?php esc_html_e( 'Behavior', 'skylearn-flashcards' ); ?></a>
			<a href="#lms"><?php esc_html_e( 'LMS Integration', 'skylearn-flashcards' ); ?></a>
			<a href="#analytics"><?php esc_html_e( 'Analytics', 'skylearn-flashcards' ); ?></a>
			<?php if ( skylearn_is_premium() ) : ?>
				<a href="#premium"><?php esc_html_e( 'Premium', 'skylearn-flashcards' ); ?></a>
			<?php endif; ?>
			<a href="#advanced"><?php esc_html_e( 'Advanced', 'skylearn-flashcards' ); ?></a>
		</nav>
		
		<!-- Settings Form -->
		<form method="post" action="options.php" class="skylearn-settings-form">
			<?php settings_fields( 'skylearn_flashcards_settings' ); ?>
			
			<!-- General Settings -->
			<div id="general" class="skylearn-tab-content">
				<div class="skylearn-card">
					<div class="skylearn-card-header">
						<h2 class="skylearn-card-title"><?php esc_html_e( 'General Settings', 'skylearn-flashcards' ); ?></h2>
					</div>
					
					<table class="skylearn-form-table">
						<tr>
							<th scope="row">
								<label for="enable_analytics"><?php esc_html_e( 'Enable Analytics', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="enable_analytics" name="skylearn_flashcards_settings[enable_analytics]" value="1" <?php checked( $settings['enable_analytics'] ?? true ); ?>>
									<?php esc_html_e( 'Track user interactions and study progress', 'skylearn-flashcards' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'This helps you understand how users interact with your flashcards.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="enable_lead_capture"><?php esc_html_e( 'Lead Capture', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="enable_lead_capture" name="skylearn_flashcards_settings[enable_lead_capture]" value="1" <?php checked( $settings['enable_lead_capture'] ?? false ); ?> <?php echo skylearn_is_premium() ? '' : 'disabled'; ?>>
									<?php esc_html_e( 'Enable lead collection forms', 'skylearn-flashcards' ); ?>
									<?php if ( ! skylearn_is_premium() ) : ?>
										<span class="skylearn-premium-badge"><?php esc_html_e( 'Premium', 'skylearn-flashcards' ); ?></span>
									<?php endif; ?>
								</label>
								<p class="description"><?php esc_html_e( 'Collect user information during flashcard study sessions.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="cards_per_session"><?php esc_html_e( 'Cards Per Session', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<input type="number" id="cards_per_session" name="skylearn_flashcards_settings[cards_per_session]" value="<?php echo esc_attr( $settings['cards_per_session'] ?? 10 ); ?>" min="0" max="100" class="small-text">
								<p class="description"><?php esc_html_e( 'Maximum number of cards to show in one session. Set to 0 for unlimited.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- Appearance Settings -->
			<div id="appearance" class="skylearn-tab-content" style="display: none;">
				<div class="skylearn-card">
					<div class="skylearn-card-header">
						<h2 class="skylearn-card-title"><?php esc_html_e( 'Appearance Settings', 'skylearn-flashcards' ); ?></h2>
					</div>
					
					<table class="skylearn-form-table">
						<tr>
							<th scope="row">
								<label for="primary_color"><?php esc_html_e( 'Primary Color', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<input type="text" id="primary_color" name="skylearn_flashcards_settings[primary_color]" value="<?php echo esc_attr( $settings['primary_color'] ?? SKYLEARN_FLASHCARDS_COLOR_PRIMARY ); ?>" class="skylearn-color-picker">
								<p class="description"><?php esc_html_e( 'Main brand color used throughout the flashcards.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="accent_color"><?php esc_html_e( 'Accent Color', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<input type="text" id="accent_color" name="skylearn_flashcards_settings[accent_color]" value="<?php echo esc_attr( $settings['accent_color'] ?? SKYLEARN_FLASHCARDS_COLOR_ACCENT ); ?>" class="skylearn-color-picker">
								<p class="description"><?php esc_html_e( 'Secondary color for highlights and accents.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="background_color"><?php esc_html_e( 'Background Color', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<input type="text" id="background_color" name="skylearn_flashcards_settings[background_color]" value="<?php echo esc_attr( $settings['background_color'] ?? SKYLEARN_FLASHCARDS_COLOR_BACKGROUND ); ?>" class="skylearn-color-picker">
								<p class="description"><?php esc_html_e( 'Background color for flashcard containers.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="text_color"><?php esc_html_e( 'Text Color', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<input type="text" id="text_color" name="skylearn_flashcards_settings[text_color]" value="<?php echo esc_attr( $settings['text_color'] ?? SKYLEARN_FLASHCARDS_COLOR_TEXT ); ?>" class="skylearn-color-picker">
								<p class="description"><?php esc_html_e( 'Primary text color for flashcard content.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="flip_animation"><?php esc_html_e( 'Flip Animation', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<select id="flip_animation" name="skylearn_flashcards_settings[flip_animation]">
									<option value="flip" <?php selected( $settings['flip_animation'] ?? 'flip', 'flip' ); ?>><?php esc_html_e( 'Flip', 'skylearn-flashcards' ); ?></option>
									<option value="slide" <?php selected( $settings['flip_animation'] ?? 'flip', 'slide' ); ?>><?php esc_html_e( 'Slide', 'skylearn-flashcards' ); ?></option>
									<option value="fade" <?php selected( $settings['flip_animation'] ?? 'flip', 'fade' ); ?>><?php esc_html_e( 'Fade', 'skylearn-flashcards' ); ?></option>
									<option value="none" <?php selected( $settings['flip_animation'] ?? 'flip', 'none' ); ?>><?php esc_html_e( 'None', 'skylearn-flashcards' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Animation style when flipping flashcards.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- Behavior Settings -->
			<div id="behavior" class="skylearn-tab-content" style="display: none;">
				<div class="skylearn-card">
					<div class="skylearn-card-header">
						<h2 class="skylearn-card-title"><?php esc_html_e( 'Behavior Settings', 'skylearn-flashcards' ); ?></h2>
					</div>
					
					<table class="skylearn-form-table">
						<tr>
							<th scope="row">
								<label for="show_progress"><?php esc_html_e( 'Show Progress', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="show_progress" name="skylearn_flashcards_settings[show_progress]" value="1" <?php checked( $settings['show_progress'] ?? true ); ?>>
									<?php esc_html_e( 'Display progress bar and card counter', 'skylearn-flashcards' ); ?>
								</label>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="enable_keyboard"><?php esc_html_e( 'Keyboard Navigation', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="enable_keyboard" name="skylearn_flashcards_settings[enable_keyboard]" value="1" <?php checked( $settings['enable_keyboard'] ?? true ); ?>>
									<?php esc_html_e( 'Enable keyboard shortcuts for navigation', 'skylearn-flashcards' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Spacebar to flip, arrow keys to navigate, K/U for known/unknown.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="enable_touch"><?php esc_html_e( 'Touch Gestures', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="enable_touch" name="skylearn_flashcards_settings[enable_touch]" value="1" <?php checked( $settings['enable_touch'] ?? true ); ?>>
									<?php esc_html_e( 'Enable swipe gestures on mobile devices', 'skylearn-flashcards' ); ?>
								</label>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="autoplay_interval"><?php esc_html_e( 'Autoplay Interval', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<input type="number" id="autoplay_interval" name="skylearn_flashcards_settings[autoplay_interval]" value="<?php echo esc_attr( $settings['autoplay_interval'] ?? 3000 ); ?>" min="1000" max="10000" step="500" class="small-text">
								<span><?php esc_html_e( 'milliseconds', 'skylearn-flashcards' ); ?></span>
								<p class="description"><?php esc_html_e( 'Time between cards when autoplay is enabled.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- LMS Integration Settings -->
			<div id="lms" class="skylearn-tab-content" style="display: none;">
				<?php
				// Include LMS settings template
				require_once SKYLEARN_FLASHCARDS_PATH . 'includes/admin/views/lms-settings.php';
				?>
			</div>
			
			<!-- Analytics Settings -->
			<div id="analytics" class="skylearn-tab-content" style="display: none;">
				<div class="skylearn-card">
					<div class="skylearn-card-header">
						<h2 class="skylearn-card-title"><?php esc_html_e( 'Analytics Settings', 'skylearn-flashcards' ); ?></h2>
					</div>
					
					<table class="skylearn-form-table">
						<tr>
							<th scope="row">
								<label for="spaced_repetition"><?php esc_html_e( 'Spaced Repetition', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="spaced_repetition" name="skylearn_flashcards_settings[spaced_repetition]" value="1" <?php checked( $settings['spaced_repetition'] ?? false ); ?> <?php echo skylearn_is_premium() ? '' : 'disabled'; ?>>
									<?php esc_html_e( 'Use spaced repetition algorithm', 'skylearn-flashcards' ); ?>
									<?php if ( ! skylearn_is_premium() ) : ?>
										<span class="skylearn-premium-badge"><?php esc_html_e( 'Premium', 'skylearn-flashcards' ); ?></span>
									<?php endif; ?>
								</label>
								<p class="description"><?php esc_html_e( 'Automatically schedule card reviews based on performance.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="difficulty_adjustment"><?php esc_html_e( 'Difficulty Adjustment', 'skylearn-flashcards' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="difficulty_adjustment" name="skylearn_flashcards_settings[difficulty_adjustment]" value="1" <?php checked( $settings['difficulty_adjustment'] ?? false ); ?> <?php echo skylearn_is_premium() ? '' : 'disabled'; ?>>
									<?php esc_html_e( 'Adjust card difficulty based on performance', 'skylearn-flashcards' ); ?>
									<?php if ( ! skylearn_is_premium() ) : ?>
										<span class="skylearn-premium-badge"><?php esc_html_e( 'Premium', 'skylearn-flashcards' ); ?></span>
									<?php endif; ?>
								</label>
								<p class="description"><?php esc_html_e( 'Automatically adjust card difficulty based on user performance.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- Premium Settings -->
			<?php if ( skylearn_is_premium() ) : ?>
				<div id="premium" class="skylearn-tab-content" style="display: none;">
					<div class="skylearn-card">
						<div class="skylearn-card-header">
							<h2 class="skylearn-card-title"><?php esc_html_e( 'Premium Settings', 'skylearn-flashcards' ); ?></h2>
						</div>
						
						<table class="skylearn-form-table">
							<tr>
								<th scope="row">
									<label for="license_key"><?php esc_html_e( 'License Key', 'skylearn-flashcards' ); ?></label>
								</th>
								<td>
									<input type="text" id="license_key" name="skylearn_flashcards_premium_license" value="<?php echo esc_attr( get_option( 'skylearn_flashcards_premium_license', '' ) ); ?>" class="regular-text">
									<button type="button" class="button skylearn-validate-license"><?php esc_html_e( 'Validate', 'skylearn-flashcards' ); ?></button>
									<p class="description"><?php esc_html_e( 'Enter your premium license key to unlock advanced features.', 'skylearn-flashcards' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>
			<?php endif; ?>
			
			<!-- Advanced Settings -->
			<div id="advanced" class="skylearn-tab-content" style="display: none;">
				<div class="skylearn-card">
					<div class="skylearn-card-header">
						<h2 class="skylearn-card-title"><?php esc_html_e( 'Advanced Settings', 'skylearn-flashcards' ); ?></h2>
					</div>
					
					<table class="skylearn-form-table">
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Data Cleanup', 'skylearn-flashcards' ); ?>
							</th>
							<td>
								<button type="button" class="button skylearn-cleanup-data"><?php esc_html_e( 'Clean Old Data', 'skylearn-flashcards' ); ?></button>
								<p class="description"><?php esc_html_e( 'Remove analytics data older than 90 days.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Reset Settings', 'skylearn-flashcards' ); ?>
							</th>
							<td>
								<button type="button" class="button skylearn-reset-settings"><?php esc_html_e( 'Reset to Defaults', 'skylearn-flashcards' ); ?></button>
								<p class="description"><?php esc_html_e( 'Reset all settings to their default values.', 'skylearn-flashcards' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- Save Button -->
			<div class="skylearn-settings-footer">
				<?php submit_button( __( 'Save Settings', 'skylearn-flashcards' ), 'primary', 'submit', false ); ?>
				<span class="skylearn-loading" style="display: none;">
					<span class="spinner is-active"></span>
					<?php esc_html_e( 'Saving...', 'skylearn-flashcards' ); ?>
				</span>
			</div>
			
		</form>
		
	</div>
	
</div>