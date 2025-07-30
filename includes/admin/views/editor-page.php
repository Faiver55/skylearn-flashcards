<?php
/**
 * Provide an admin flashcard editor page view for the plugin
 *
 * This file is used to markup the flashcard editor interface.
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

// Get current post ID if editing
$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
$flashcard_set = $post_id ? skylearn_get_flashcard_set( $post_id ) : null;

?>

<div class="skylearn-admin-wrap">
	
	<div class="skylearn-admin-header">
		<h1>
			<?php 
			if ( $flashcard_set ) {
				esc_html_e( 'Edit Flashcard Set', 'skylearn-flashcards' );
			} else {
				esc_html_e( 'Add New Flashcard Set', 'skylearn-flashcards' );
			}
			?>
		</h1>
	</div>
	
	<div class="skylearn-admin-content">
		
		<form method="post" class="skylearn-editor-form">
			<?php wp_nonce_field( 'skylearn_save_flashcard_set', 'skylearn_nonce' ); ?>
			
			<!-- Basic Information -->
			<div class="skylearn-card">
				<div class="skylearn-card-header">
					<h2 class="skylearn-card-title"><?php esc_html_e( 'Basic Information', 'skylearn-flashcards' ); ?></h2>
				</div>
				
				<table class="skylearn-form-table">
					<tr>
						<th scope="row">
							<label for="flashcard_title"><?php esc_html_e( 'Set Title', 'skylearn-flashcards' ); ?> <span class="required">*</span></label>
						</th>
						<td>
							<input type="text" id="flashcard_title" name="flashcard_title" value="<?php echo esc_attr( $flashcard_set['title'] ?? '' ); ?>" class="regular-text skylearn-required" required>
							<p class="description"><?php esc_html_e( 'Enter a descriptive title for this flashcard set.', 'skylearn-flashcards' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="flashcard_description"><?php esc_html_e( 'Description', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<textarea id="flashcard_description" name="flashcard_description" rows="4" class="large-text"><?php echo esc_textarea( $flashcard_set['description'] ?? '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Optional description that will be displayed with the flashcard set.', 'skylearn-flashcards' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="flashcard_categories"><?php esc_html_e( 'Categories', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<input type="text" id="flashcard_categories" name="flashcard_categories" value="<?php echo esc_attr( implode( ', ', $flashcard_set['categories'] ?? array() ) ); ?>" class="regular-text">
							<p class="description"><?php esc_html_e( 'Separate multiple categories with commas.', 'skylearn-flashcards' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="flashcard_tags"><?php esc_html_e( 'Tags', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<input type="text" id="flashcard_tags" name="flashcard_tags" value="<?php echo esc_attr( implode( ', ', $flashcard_set['tags'] ?? array() ) ); ?>" class="regular-text">
							<p class="description"><?php esc_html_e( 'Separate multiple tags with commas.', 'skylearn-flashcards' ); ?></p>
						</td>
					</tr>
				</table>
			</div>
			
			<!-- Flashcards -->
			<div class="skylearn-card">
				<div class="skylearn-card-header">
					<h2 class="skylearn-card-title"><?php esc_html_e( 'Flashcards', 'skylearn-flashcards' ); ?></h2>
					<button type="button" class="skylearn-btn skylearn-add-card">
						<span class="dashicons dashicons-plus"></span>
						<?php esc_html_e( 'Add Card', 'skylearn-flashcards' ); ?>
					</button>
				</div>
				
				<div class="skylearn-flashcards-container">
					<?php 
					$cards = $flashcard_set['cards'] ?? array();
					if ( empty( $cards ) ) {
						// Add one empty card by default
						$cards = array( array( 'question' => '', 'answer' => '', 'hint' => '', 'difficulty' => 'medium' ) );
					}
					
					foreach ( $cards as $index => $card ) : 
					?>
						<div class="skylearn-flashcard-item" data-index="<?php echo esc_attr( $index ); ?>">
							<div class="skylearn-flashcard-header">
								<h4><?php printf( esc_html__( 'Card %d', 'skylearn-flashcards' ), $index + 1 ); ?></h4>
								<div class="skylearn-flashcard-actions">
									<button type="button" class="button skylearn-duplicate-card" title="<?php esc_attr_e( 'Duplicate', 'skylearn-flashcards' ); ?>">
										<span class="dashicons dashicons-admin-page"></span>
									</button>
									<button type="button" class="button skylearn-remove-card" title="<?php esc_attr_e( 'Remove', 'skylearn-flashcards' ); ?>">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>
							</div>
							
							<div class="skylearn-flashcard-content">
								<div class="skylearn-field-group">
									<label for="question_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Question', 'skylearn-flashcards' ); ?> <span class="required">*</span></label>
									<textarea id="question_<?php echo esc_attr( $index ); ?>" name="flashcards[<?php echo esc_attr( $index ); ?>][question]" rows="3" class="large-text skylearn-question skylearn-required" required><?php echo esc_textarea( $card['question'] ); ?></textarea>
								</div>
								
								<div class="skylearn-field-group">
									<label for="answer_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Answer', 'skylearn-flashcards' ); ?> <span class="required">*</span></label>
									<textarea id="answer_<?php echo esc_attr( $index ); ?>" name="flashcards[<?php echo esc_attr( $index ); ?>][answer]" rows="3" class="large-text skylearn-answer skylearn-required" required><?php echo esc_textarea( $card['answer'] ); ?></textarea>
								</div>
								
								<div class="skylearn-field-row">
									<div class="skylearn-field-group">
										<label for="hint_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Hint (Optional)', 'skylearn-flashcards' ); ?></label>
										<input type="text" id="hint_<?php echo esc_attr( $index ); ?>" name="flashcards[<?php echo esc_attr( $index ); ?>][hint]" value="<?php echo esc_attr( $card['hint'] ?? '' ); ?>" class="regular-text">
									</div>
									
									<div class="skylearn-field-group">
										<label for="difficulty_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Difficulty', 'skylearn-flashcards' ); ?></label>
										<select id="difficulty_<?php echo esc_attr( $index ); ?>" name="flashcards[<?php echo esc_attr( $index ); ?>][difficulty]">
											<option value="easy" <?php selected( $card['difficulty'] ?? 'medium', 'easy' ); ?>><?php esc_html_e( 'Easy', 'skylearn-flashcards' ); ?></option>
											<option value="medium" <?php selected( $card['difficulty'] ?? 'medium', 'medium' ); ?>><?php esc_html_e( 'Medium', 'skylearn-flashcards' ); ?></option>
											<option value="hard" <?php selected( $card['difficulty'] ?? 'medium', 'hard' ); ?>><?php esc_html_e( 'Hard', 'skylearn-flashcards' ); ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<div class="skylearn-flashcards-footer">
					<button type="button" class="skylearn-btn skylearn-btn-secondary skylearn-add-card">
						<?php esc_html_e( 'Add Another Card', 'skylearn-flashcards' ); ?>
					</button>
				</div>
			</div>
			
			<!-- Set Settings -->
			<div class="skylearn-card">
				<div class="skylearn-card-header">
					<h2 class="skylearn-card-title"><?php esc_html_e( 'Set Settings', 'skylearn-flashcards' ); ?></h2>
				</div>
				
				<table class="skylearn-form-table">
					<tr>
						<th scope="row">
							<label for="shuffle_cards"><?php esc_html_e( 'Shuffle Cards', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="shuffle_cards" name="settings[shuffle_cards]" value="1" <?php checked( $flashcard_set['settings']['shuffle_cards'] ?? false ); ?>>
								<?php esc_html_e( 'Randomize card order when displayed', 'skylearn-flashcards' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="show_hints"><?php esc_html_e( 'Show Hints', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="show_hints" name="settings[show_hints]" value="1" <?php checked( $flashcard_set['settings']['show_hints'] ?? true ); ?>>
								<?php esc_html_e( 'Display hints when available', 'skylearn-flashcards' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="auto_advance"><?php esc_html_e( 'Auto Advance', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_advance" name="settings[auto_advance]" value="1" <?php checked( $flashcard_set['settings']['auto_advance'] ?? false ); ?>>
								<?php esc_html_e( 'Automatically advance to next card after answering', 'skylearn-flashcards' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="theme"><?php esc_html_e( 'Theme', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<select id="theme" name="settings[theme]">
								<option value="default" <?php selected( $flashcard_set['settings']['theme'] ?? 'default', 'default' ); ?>><?php esc_html_e( 'Default', 'skylearn-flashcards' ); ?></option>
								<option value="modern" <?php selected( $flashcard_set['settings']['theme'] ?? 'default', 'modern' ); ?>><?php esc_html_e( 'Modern', 'skylearn-flashcards' ); ?></option>
								<option value="minimal" <?php selected( $flashcard_set['settings']['theme'] ?? 'default', 'minimal' ); ?>><?php esc_html_e( 'Minimal', 'skylearn-flashcards' ); ?></option>
								<option value="dark" <?php selected( $flashcard_set['settings']['theme'] ?? 'default', 'dark' ); ?>><?php esc_html_e( 'Dark', 'skylearn-flashcards' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
			</div>
			
			<!-- Actions -->
			<div class="skylearn-editor-actions">
				<div class="skylearn-actions-left">
					<button type="submit" name="action" value="save_draft" class="button button-large">
						<?php esc_html_e( 'Save Draft', 'skylearn-flashcards' ); ?>
					</button>
					<button type="submit" name="action" value="publish" class="button button-primary button-large">
						<?php esc_html_e( 'Publish', 'skylearn-flashcards' ); ?>
					</button>
				</div>
				
				<div class="skylearn-actions-right">
					<button type="button" class="button button-large skylearn-preview-btn" data-set-id="<?php echo esc_attr( $post_id ); ?>">
						<?php esc_html_e( 'Preview', 'skylearn-flashcards' ); ?>
					</button>
					
					<?php if ( $post_id ) : ?>
						<button type="button" class="button button-large skylearn-export-btn" data-set-id="<?php echo esc_attr( $post_id ); ?>" data-format="json">
							<?php esc_html_e( 'Export', 'skylearn-flashcards' ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
			
		</form>
		
		<!-- Import Section -->
		<div class="skylearn-card skylearn-import-section">
			<div class="skylearn-card-header">
				<h2 class="skylearn-card-title"><?php esc_html_e( 'Import Flashcards', 'skylearn-flashcards' ); ?></h2>
			</div>
			
			<form method="post" enctype="multipart/form-data" class="skylearn-import-form">
				<?php wp_nonce_field( 'skylearn_import_flashcards', 'skylearn_import_nonce' ); ?>
				
				<table class="skylearn-form-table">
					<tr>
						<th scope="row">
							<label for="import_file"><?php esc_html_e( 'Select File', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<input type="file" id="import_file" name="import_file" accept=".json,.csv,.txt" class="skylearn-import-file">
							<p class="description">
								<?php esc_html_e( 'Supported formats: JSON, CSV, TXT. Maximum file size: 5MB.', 'skylearn-flashcards' ); ?>
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="import_title"><?php esc_html_e( 'Set Title', 'skylearn-flashcards' ); ?></label>
						</th>
						<td>
							<input type="text" id="import_title" name="set_title" value="" class="regular-text" placeholder="<?php esc_attr_e( 'Imported Flashcard Set', 'skylearn-flashcards' ); ?>">
						</td>
					</tr>
				</table>
				
				<button type="submit" class="button button-secondary">
					<?php esc_html_e( 'Import Flashcards', 'skylearn-flashcards' ); ?>
				</button>
			</form>
		</div>
		
	</div>
	
</div>

<!-- Card Template for JavaScript -->
<script type="text/template" id="skylearn-card-template">
	<div class="skylearn-flashcard-item" data-index="[INDEX]">
		<div class="skylearn-flashcard-header">
			<h4><?php esc_html_e( 'Card', 'skylearn-flashcards' ); ?> [INDEX]</h4>
			<div class="skylearn-flashcard-actions">
				<button type="button" class="button skylearn-duplicate-card" title="<?php esc_attr_e( 'Duplicate', 'skylearn-flashcards' ); ?>">
					<span class="dashicons dashicons-admin-page"></span>
				</button>
				<button type="button" class="button skylearn-remove-card" title="<?php esc_attr_e( 'Remove', 'skylearn-flashcards' ); ?>">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>
		</div>
		
		<div class="skylearn-flashcard-content">
			<div class="skylearn-field-group">
				<label for="question_[INDEX]"><?php esc_html_e( 'Question', 'skylearn-flashcards' ); ?> <span class="required">*</span></label>
				<textarea id="question_[INDEX]" name="flashcards[[INDEX]][question]" rows="3" class="large-text skylearn-question skylearn-required" required></textarea>
			</div>
			
			<div class="skylearn-field-group">
				<label for="answer_[INDEX]"><?php esc_html_e( 'Answer', 'skylearn-flashcards' ); ?> <span class="required">*</span></label>
				<textarea id="answer_[INDEX]" name="flashcards[[INDEX]][answer]" rows="3" class="large-text skylearn-answer skylearn-required" required></textarea>
			</div>
			
			<div class="skylearn-field-row">
				<div class="skylearn-field-group">
					<label for="hint_[INDEX]"><?php esc_html_e( 'Hint (Optional)', 'skylearn-flashcards' ); ?></label>
					<input type="text" id="hint_[INDEX]" name="flashcards[[INDEX]][hint]" value="" class="regular-text">
				</div>
				
				<div class="skylearn-field-group">
					<label for="difficulty_[INDEX]"><?php esc_html_e( 'Difficulty', 'skylearn-flashcards' ); ?></label>
					<select id="difficulty_[INDEX]" name="flashcards[[INDEX]][difficulty]">
						<option value="easy"><?php esc_html_e( 'Easy', 'skylearn-flashcards' ); ?></option>
						<option value="medium" selected><?php esc_html_e( 'Medium', 'skylearn-flashcards' ); ?></option>
						<option value="hard"><?php esc_html_e( 'Hard', 'skylearn-flashcards' ); ?></option>
					</select>
				</div>
			</div>
		</div>
	</div>
</script>