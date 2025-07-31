<?php
/**
 * Provide an admin main page view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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

// Check if this is a welcome screen
$is_welcome = isset( $_GET['skylearn_welcome'] ) && $_GET['skylearn_welcome'] === '1';

// Get flashcard sets
$flashcard_sets = get_posts( array(
	'post_type'      => 'flashcard_set',
	'post_status'    => array( 'publish', 'draft', 'private' ),
	'numberposts'    => 20,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

?>

<div class="skylearn-admin-wrap">
	
	<?php if ( $is_welcome ) : ?>
		<!-- Welcome Screen -->
		<div class="skylearn-welcome-panel">
			<div class="skylearn-admin-header <?php echo ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) ? 'beta-version' : ''; ?>">
				<img src="<?php echo esc_url( skylearn_get_asset_url( 'img/logo-horiz.png' ) ); ?>" alt="SkyLearn Flashcards" class="skylearn-logo">
				<h1>
					<?php esc_html_e( 'Welcome to SkyLearn Flashcards!', 'skylearn-flashcards' ); ?>
					<?php if ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) : ?>
						<span class="skylearn-beta-badge">Beta</span>
					<?php endif; ?>
				</h1>
				<?php if ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) : ?>
					<p><?php esc_html_e( 'Thank you for participating in our BETA program! Your feedback helps us create the best flashcard plugin for WordPress.', 'skylearn-flashcards' ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Thank you for installing SkyLearn Flashcards. Let\'s get you started with creating your first flashcard set.', 'skylearn-flashcards' ); ?></p>
				<?php endif; ?>
			</div>
			
			<?php if ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) : ?>
				<!-- Beta-specific welcome content -->
				<div class="beta-success">
					<h4><?php esc_html_e( 'Beta Tester Benefits', 'skylearn-flashcards' ); ?></h4>
					<p><?php esc_html_e( 'As a beta tester, you have access to ALL premium features during the testing period. Please share your feedback to help us improve!', 'skylearn-flashcards' ); ?></p>
				</div>
			<?php endif; ?>
			
			<div class="skylearn-admin-content">
				<div class="skylearn-welcome-steps">
					<div class="skylearn-welcome-step">
						<div class="skylearn-step-icon">
							<span class="dashicons dashicons-plus-alt"></span>
						</div>
						<h3><?php esc_html_e( 'Create Your First Set', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Start by creating your first flashcard set. Add questions, answers, and customize the appearance.', 'skylearn-flashcards' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-flashcards-new' ) ); ?>" class="skylearn-btn">
							<?php esc_html_e( 'Create New Set', 'skylearn-flashcards' ); ?>
						</a>
					</div>
					
					<div class="skylearn-welcome-step">
						<div class="skylearn-step-icon">
							<span class="dashicons dashicons-admin-settings"></span>
						</div>
						<h3><?php esc_html_e( 'Configure Settings', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Customize colors, animations, and behavior to match your site\'s branding.', 'skylearn-flashcards' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-flashcards-settings' ) ); ?>" class="skylearn-btn skylearn-btn-secondary">
							<?php esc_html_e( 'Open Settings', 'skylearn-flashcards' ); ?>
						</a>
					</div>
					
					<div class="skylearn-welcome-step">
						<div class="skylearn-step-icon">
							<span class="dashicons dashicons-shortcode"></span>
						</div>
						<h3><?php esc_html_e( 'Embed Flashcards', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Use shortcodes or Gutenberg blocks to display your flashcards on any page or post.', 'skylearn-flashcards' ); ?></p>
						<code>[skylearn_flashcards id="SET_ID"]</code>
					</div>
					
					<?php if ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) : ?>
					<div class="skylearn-welcome-step">
						<div class="skylearn-step-icon">
							<span class="dashicons dashicons-feedback" style="color: var(--skylearn-beta);"></span>
						</div>
						<h3><?php esc_html_e( 'Provide Beta Feedback', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Share your experience and help us improve the plugin before public release.', 'skylearn-flashcards' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skylearn_flashcard&page=skylearn-beta-feedback' ) ); ?>" class="skylearn-btn" style="background: var(--skylearn-beta); border-color: var(--skylearn-beta);">
							<?php esc_html_e( 'Beta Feedback', 'skylearn-flashcards' ); ?>
						</a>
					</div>
					<?php endif; ?>
				</div>
				
				<div class="skylearn-welcome-footer">
					<p>
						<?php if ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) : ?>
							<?php 
							printf( 
								esc_html__( 'Beta tester resources: %1$sBeta Guide%2$s | %3$sFeedback Template%4$s | %5$sSupport%6$s', 'skylearn-flashcards' ),
								'<a href="https://github.com/Faiver55/skylearn-flashcards/blob/main/docs/ONBOARDING.md" target="_blank">',
								'</a>',
								'<a href="https://github.com/Faiver55/skylearn-flashcards/blob/main/docs/FEEDBACK_TEMPLATE.md" target="_blank">',
								'</a>',
								'<a href="mailto:support@skyian.com?subject=Beta Support">',
								'</a>'
							);
							?>
						<?php else : ?>
							<?php 
							printf( 
								esc_html__( 'Need help? Check out our %1$sdocumentation%2$s or %3$scontact support%4$s.', 'skylearn-flashcards' ),
								'<a href="https://skyian.com/skylearn-flashcards/docs/" target="_blank">',
								'</a>',
								'<a href="mailto:support@skyian.com">',
								'</a>'
							);
							?>
						<?php endif; ?>
					</p>
				</div>
			</div>
		</div>
	<?php else : ?>
		<!-- Main Dashboard -->
		<div class="skylearn-admin-header <?php echo ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) ? 'beta-version' : ''; ?>">
			<h1>
				<?php esc_html_e( 'SkyLearn Flashcards', 'skylearn-flashcards' ); ?>
				<?php if ( strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false ) : ?>
					<span class="skylearn-beta-badge">Beta</span>
				<?php endif; ?>
			</h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-flashcards-new' ) ); ?>" class="skylearn-btn">
				<?php esc_html_e( 'Add New Set', 'skylearn-flashcards' ); ?>
			</a>
		</div>
		
		<div class="skylearn-admin-content">
			
			<!-- Quick Stats -->
			<div class="skylearn-stats-grid">
				<div class="skylearn-stat-card">
					<div class="skylearn-stat-icon">
						<span class="dashicons dashicons-book-alt"></span>
					</div>
					<div class="skylearn-stat-content">
						<span class="skylearn-stat-value"><?php echo esc_html( count( $flashcard_sets ) ); ?></span>
						<span class="skylearn-stat-label"><?php esc_html_e( 'Flashcard Sets', 'skylearn-flashcards' ); ?></span>
					</div>
				</div>
				
				<div class="skylearn-stat-card">
					<div class="skylearn-stat-icon">
						<span class="dashicons dashicons-index-card"></span>
					</div>
					<div class="skylearn-stat-content">
						<?php
						$total_cards = 0;
						foreach ( $flashcard_sets as $set ) {
							$cards = get_post_meta( $set->ID, '_skylearn_flashcard_data', true );
							$total_cards += is_array( $cards ) ? count( $cards ) : 0;
						}
						?>
						<span class="skylearn-stat-value"><?php echo esc_html( $total_cards ); ?></span>
						<span class="skylearn-stat-label"><?php esc_html_e( 'Total Cards', 'skylearn-flashcards' ); ?></span>
					</div>
				</div>
				
				<div class="skylearn-stat-card">
					<div class="skylearn-stat-icon">
						<span class="dashicons dashicons-chart-line"></span>
					</div>
					<div class="skylearn-stat-content">
						<span class="skylearn-stat-value">0</span>
						<span class="skylearn-stat-label"><?php esc_html_e( 'Study Sessions', 'skylearn-flashcards' ); ?></span>
					</div>
				</div>
				
				<?php if ( skylearn_is_premium() ) : ?>
					<div class="skylearn-stat-card">
						<div class="skylearn-stat-icon">
							<span class="dashicons dashicons-groups"></span>
						</div>
						<div class="skylearn-stat-content">
							<span class="skylearn-stat-value">0</span>
							<span class="skylearn-stat-label"><?php esc_html_e( 'Leads Collected', 'skylearn-flashcards' ); ?></span>
						</div>
					</div>
				<?php endif; ?>
			</div>
			
			<!-- Recent Flashcard Sets -->
			<div class="skylearn-card">
				<div class="skylearn-card-header">
					<h2 class="skylearn-card-title"><?php esc_html_e( 'Recent Flashcard Sets', 'skylearn-flashcards' ); ?></h2>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skylearn_flashcard' ) ); ?>" class="skylearn-btn skylearn-btn-secondary">
						<?php esc_html_e( 'View All', 'skylearn-flashcards' ); ?>
					</a>
				</div>
				
				<?php if ( ! empty( $flashcard_sets ) ) : ?>
					<div class="skylearn-sets-table">
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Title', 'skylearn-flashcards' ); ?></th>
									<th><?php esc_html_e( 'Cards', 'skylearn-flashcards' ); ?></th>
									<th><?php esc_html_e( 'Status', 'skylearn-flashcards' ); ?></th>
									<th><?php esc_html_e( 'Date', 'skylearn-flashcards' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'skylearn-flashcards' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( array_slice( $flashcard_sets, 0, 5 ) as $set ) : ?>
									<?php
									$cards = get_post_meta( $set->ID, '_skylearn_flashcard_data', true );
									$card_count = is_array( $cards ) ? count( $cards ) : 0;
									?>
									<tr>
										<td>
											<strong>
												<a href="<?php echo esc_url( get_edit_post_link( $set->ID ) ); ?>">
													<?php echo esc_html( $set->post_title ?: __( '(no title)', 'skylearn-flashcards' ) ); ?>
												</a>
											</strong>
										</td>
										<td><?php echo esc_html( $card_count ); ?></td>
										<td>
											<span class="skylearn-status skylearn-status-<?php echo esc_attr( $set->post_status ); ?>">
												<?php echo esc_html( ucfirst( $set->post_status ) ); ?>
											</span>
										</td>
										<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $set->post_date ) ); ?></td>
										<td>
											<a href="<?php echo esc_url( get_edit_post_link( $set->ID ) ); ?>" class="button button-small">
												<?php esc_html_e( 'Edit', 'skylearn-flashcards' ); ?>
											</a>
											<a href="<?php echo esc_url( get_permalink( $set->ID ) ); ?>" class="button button-small" target="_blank">
												<?php esc_html_e( 'View', 'skylearn-flashcards' ); ?>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<div class="skylearn-empty-state">
						<div class="skylearn-empty-icon">
							<span class="dashicons dashicons-book-alt"></span>
						</div>
						<h3><?php esc_html_e( 'No flashcard sets yet', 'skylearn-flashcards' ); ?></h3>
						<p><?php esc_html_e( 'Create your first flashcard set to get started with interactive learning.', 'skylearn-flashcards' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-flashcards-new' ) ); ?>" class="skylearn-btn">
							<?php esc_html_e( 'Create Your First Set', 'skylearn-flashcards' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
			
			<!-- Premium Upgrade Notice -->
			<?php if ( ! skylearn_is_premium() ) : ?>
				<div class="skylearn-card skylearn-premium-notice">
					<div class="skylearn-card-header">
						<h2 class="skylearn-card-title">
							<span class="dashicons dashicons-star-filled"></span>
							<?php esc_html_e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
						</h2>
					</div>
					<div class="skylearn-premium-features">
						<ul>
							<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Advanced Analytics & Reporting', 'skylearn-flashcards' ); ?></li>
							<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Lead Collection & Email Integration', 'skylearn-flashcards' ); ?></li>
							<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Unlimited Flashcard Sets', 'skylearn-flashcards' ); ?></li>
							<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Bulk Export & Import', 'skylearn-flashcards' ); ?></li>
							<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Priority Support', 'skylearn-flashcards' ); ?></li>
						</ul>
						<a href="https://skyian.com/skylearn-flashcards/premium/" target="_blank" class="skylearn-btn">
							<?php esc_html_e( 'Upgrade Now', 'skylearn-flashcards' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>
			
		</div>
	<?php endif; ?>
	
</div>