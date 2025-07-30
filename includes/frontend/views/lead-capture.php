<?php
/**
 * Lead capture form for flashcard interactions
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend/views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Available variables:
 * $context - Context where lead capture is displayed ('completion', 'midway', 'popup')
 * $set_id - Flashcard set ID
 * $settings - Lead capture settings
 */

$context = isset( $context ) ? $context : 'completion';
$set_id = isset( $set_id ) ? $set_id : 0;
$settings = isset( $settings ) ? $settings : array();

// Check if lead collection is enabled
if ( ! skylearn_is_premium() || ! isset( $settings['enable_leads'] ) || ! $settings['enable_leads'] ) {
    return;
}

$form_title = isset( $settings['form_title'] ) ? $settings['form_title'] : __( 'Get Your Study Results!', 'skylearn-flashcards' );
$form_description = isset( $settings['form_description'] ) ? $settings['form_description'] : __( 'Enter your email to receive detailed performance analytics and study recommendations.', 'skylearn-flashcards' );
$privacy_text = isset( $settings['privacy_text'] ) ? $settings['privacy_text'] : __( 'We respect your privacy. Your email will only be used to send study-related content.', 'skylearn-flashcards' );
?>

<div class="skylearn-lead-capture <?php echo esc_attr( 'context-' . $context ); ?>">
    
    <div class="lead-capture-container">
        
        <!-- Header -->
        <div class="lead-capture-header">
            <div class="header-icon">
                <span class="dashicons dashicons-email-alt"></span>
            </div>
            <h3 class="form-title"><?php echo esc_html( $form_title ); ?></h3>
            <p class="form-description"><?php echo esc_html( $form_description ); ?></p>
        </div>

        <!-- Lead Capture Form -->
        <form class="skylearn-lead-form" data-set-id="<?php echo esc_attr( $set_id ); ?>" data-context="<?php echo esc_attr( $context ); ?>">
            
            <!-- Form Fields -->
            <div class="form-fields">
                
                <!-- Name Field (Optional) -->
                <?php if ( isset( $settings['collect_name'] ) && $settings['collect_name'] ) : ?>
                    <div class="form-group">
                        <label for="skylearn-lead-name">
                            <?php esc_html_e( 'Your Name', 'skylearn-flashcards' ); ?>
                            <?php if ( ! isset( $settings['name_required'] ) || ! $settings['name_required'] ) : ?>
                                <span class="optional"><?php esc_html_e( '(Optional)', 'skylearn-flashcards' ); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="text" 
                               id="skylearn-lead-name" 
                               name="lead_name" 
                               class="form-input"
                               placeholder="<?php esc_attr_e( 'Enter your name', 'skylearn-flashcards' ); ?>"
                               <?php echo ( isset( $settings['name_required'] ) && $settings['name_required'] ) ? 'required' : ''; ?>>
                    </div>
                <?php endif; ?>

                <!-- Email Field (Always Required) -->
                <div class="form-group">
                    <label for="skylearn-lead-email">
                        <?php esc_html_e( 'Email Address', 'skylearn-flashcards' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="email" 
                           id="skylearn-lead-email" 
                           name="lead_email" 
                           class="form-input"
                           placeholder="<?php esc_attr_e( 'Enter your email address', 'skylearn-flashcards' ); ?>"
                           required>
                    <div class="field-hint">
                        <?php esc_html_e( 'We\'ll send your detailed study results to this email.', 'skylearn-flashcards' ); ?>
                    </div>
                </div>

                <!-- Additional Fields (if configured) -->
                <?php if ( isset( $settings['additional_fields'] ) && is_array( $settings['additional_fields'] ) ) : ?>
                    <?php foreach ( $settings['additional_fields'] as $field ) : ?>
                        <div class="form-group">
                            <label for="skylearn-field-<?php echo esc_attr( $field['id'] ); ?>">
                                <?php echo esc_html( $field['label'] ); ?>
                                <?php if ( isset( $field['required'] ) && $field['required'] ) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if ( $field['type'] === 'select' ) : ?>
                                <select id="skylearn-field-<?php echo esc_attr( $field['id'] ); ?>" 
                                        name="<?php echo esc_attr( $field['name'] ); ?>" 
                                        class="form-input"
                                        <?php echo ( isset( $field['required'] ) && $field['required'] ) ? 'required' : ''; ?>>
                                    <option value=""><?php esc_html_e( 'Select an option...', 'skylearn-flashcards' ); ?></option>
                                    <?php foreach ( $field['options'] as $option ) : ?>
                                        <option value="<?php echo esc_attr( $option['value'] ); ?>">
                                            <?php echo esc_html( $option['label'] ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else : ?>
                                <input type="<?php echo esc_attr( $field['type'] ); ?>" 
                                       id="skylearn-field-<?php echo esc_attr( $field['id'] ); ?>" 
                                       name="<?php echo esc_attr( $field['name'] ); ?>" 
                                       class="form-input"
                                       placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                                       <?php echo ( isset( $field['required'] ) && $field['required'] ) ? 'required' : ''; ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Consent Checkbox -->
                <div class="form-group consent-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               id="skylearn-lead-consent" 
                               name="lead_consent" 
                               class="form-checkbox"
                               required>
                        <span class="checkmark"></span>
                        <span class="checkbox-text">
                            <?php 
                            printf(
                                /* translators: %1$s: privacy policy URL, %2$s: terms URL */
                                esc_html__( 'I agree to receive study-related emails and acknowledge the %1$s and %2$s.', 'skylearn-flashcards' ),
                                '<a href="' . esc_url( 'https://skyian.com/skylearn-flashcards/privacy-policy/' ) . '" target="_blank">' . esc_html__( 'Privacy Policy', 'skylearn-flashcards' ) . '</a>',
                                '<a href="' . esc_url( 'https://skyian.com/skylearn-flashcards/tos/' ) . '" target="_blank">' . esc_html__( 'Terms of Service', 'skylearn-flashcards' ) . '</a>'
                            );
                            ?>
                        </span>
                    </label>
                </div>

            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="skylearn-btn skylearn-btn-primary btn-submit">
                    <span class="btn-text"><?php esc_html_e( 'Get My Results', 'skylearn-flashcards' ); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="dashicons dashicons-update spin"></span>
                        <?php esc_html_e( 'Sending...', 'skylearn-flashcards' ); ?>
                    </span>
                </button>
                
                <?php if ( $context !== 'completion' ) : ?>
                    <button type="button" class="skylearn-btn skylearn-btn-outline btn-skip">
                        <?php esc_html_e( 'Skip for Now', 'skylearn-flashcards' ); ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Privacy Notice -->
            <div class="privacy-notice">
                <span class="dashicons dashicons-shield-alt"></span>
                <small><?php echo esc_html( $privacy_text ); ?></small>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="action" value="skylearn_submit_lead">
            <input type="hidden" name="set_id" value="<?php echo esc_attr( $set_id ); ?>">
            <input type="hidden" name="context" value="<?php echo esc_attr( $context ); ?>">
            <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'skylearn_lead_submit' ) ); ?>">
            
        </form>

        <!-- Success Message -->
        <div class="lead-success-message" style="display: none;">
            <div class="success-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h3><?php esc_html_e( 'Thank You!', 'skylearn-flashcards' ); ?></h3>
            <p><?php esc_html_e( 'Your detailed study results have been sent to your email. Check your inbox for insights and recommendations!', 'skylearn-flashcards' ); ?></p>
            <button type="button" class="skylearn-btn skylearn-btn-outline btn-continue-studying">
                <?php esc_html_e( 'Continue Studying', 'skylearn-flashcards' ); ?>
            </button>
        </div>

        <!-- Error Message -->
        <div class="lead-error-message" style="display: none;">
            <div class="error-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <h3><?php esc_html_e( 'Oops! Something went wrong.', 'skylearn-flashcards' ); ?></h3>
            <p class="error-text"><?php esc_html_e( 'Please try again or contact support if the problem persists.', 'skylearn-flashcards' ); ?></p>
            <button type="button" class="skylearn-btn skylearn-btn-primary btn-retry">
                <?php esc_html_e( 'Try Again', 'skylearn-flashcards' ); ?>
            </button>
        </div>

    </div>

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var leadForm = $('.skylearn-lead-form');
    var formContainer = $('.lead-capture-container');
    
    // Form submission
    leadForm.on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitBtn = $(this).find('.btn-submit');
        
        // Show loading state
        submitBtn.addClass('loading');
        submitBtn.find('.btn-text').hide();
        submitBtn.find('.btn-loading').show();
        submitBtn.prop('disabled', true);
        
        // TODO: Implement actual AJAX submission
        $.ajax({
            url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    formContainer.find('.skylearn-lead-form').hide();
                    formContainer.find('.lead-success-message').show();
                    
                    // Track conversion
                    console.log('Lead captured successfully', response.data);
                } else {
                    // Show error message
                    formContainer.find('.lead-error-message .error-text').text(response.data.message || 'An error occurred');
                    formContainer.find('.skylearn-lead-form').hide();
                    formContainer.find('.lead-error-message').show();
                }
            },
            error: function() {
                // Show generic error
                formContainer.find('.skylearn-lead-form').hide();
                formContainer.find('.lead-error-message').show();
            },
            complete: function() {
                // Reset button state
                submitBtn.removeClass('loading');
                submitBtn.find('.btn-text').show();
                submitBtn.find('.btn-loading').hide();
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Retry button
    $('.btn-retry').on('click', function() {
        formContainer.find('.lead-error-message').hide();
        formContainer.find('.skylearn-lead-form').show();
    });
    
    // Skip button
    $('.btn-skip').on('click', function() {
        // TODO: Hide lead capture and continue
        $('.skylearn-lead-capture').fadeOut();
    });
    
    // Continue studying after success
    $('.btn-continue-studying').on('click', function() {
        // TODO: Continue to next activity
        $('.skylearn-lead-capture').fadeOut();
    });
    
    console.log('Lead capture form initialized for context: <?php echo esc_js( $context ); ?>');
});
</script>