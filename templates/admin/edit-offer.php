<?php
/**
 * Edit/Create offer admin page template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get offer ID from URL if editing
$offer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$is_editing = $offer_id > 0;

// Initialize offer data
$offer_data = [
    'id' => $offer_id,
    'name' => '',
    'description' => '',
    'type' => 'percentage',
    'value' => '',
    'status' => 'draft',
    'start_date' => '',
    'end_date' => '',
    'usage_limit' => '',
    'conditions' => []
];

// If editing, load existing offer data
if ( $is_editing ) {
    global $wpdb;
    $offer = $wpdb->get_row( 
        $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_offers WHERE id = %d", $offer_id ) 
    );
    
    if ( $offer ) {
        $offer_data = [
            'id' => $offer->id,
            'name' => $offer->name,
            'description' => $offer->description,
            'type' => $offer->type,
            'value' => $offer->value,
            'status' => $offer->status,
            'start_date' => $offer->start_date,
            'end_date' => $offer->end_date,
            'usage_limit' => $offer->usage_limit,
            'conditions' => json_decode( $offer->conditions, true ) ?? []
        ];
    } else {
        // Offer not found
        wp_die( __( 'Offer not found.', 'woo-offers' ) );
    }
}

$page_title = $is_editing ? __( 'Edit Offer', 'woo-offers' ) : __( 'Create New Offer', 'woo-offers' );
?>

<div class="wrap woo-offers-edit-offer-page">
    <h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>
    
    <?php if ( $is_editing ): ?>
        <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create-offer' ); ?>" class="page-title-action">
            <?php _e( 'Add New', 'woo-offers' ); ?>
        </a>
    <?php endif; ?>
    
    <hr class="wp-header-end">

    <form method="post" action="" id="woo-offers-edit-form">
        <?php wp_nonce_field( 'woo_offers_save_offer', 'woo_offers_nonce' ); ?>
        <input type="hidden" name="action" value="save_offer">
        <input type="hidden" name="offer_id" value="<?php echo esc_attr( $offer_data['id'] ); ?>">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <!-- Main content area -->
                <div id="post-body-content">
                    
                    <!-- Basic Information -->
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label class="screen-reader-text" for="title"><?php _e( 'Offer Name', 'woo-offers' ); ?></label>
                            <input type="text" name="offer_name" size="30" value="<?php echo esc_attr( $offer_data['name'] ); ?>" 
                                   id="title" spellcheck="true" autocomplete="off" placeholder="<?php _e( 'Enter offer name here', 'woo-offers' ); ?>" required>
                        </div>
                    </div>

                    <!-- Description -->
                    <div id="postdivrich" class="postarea wp-editor-expand">
                        <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap tmce-active">
                            <div id="wp-content-editor-tools" class="wp-editor-tools hide-if-no-js">
                                <div id="wp-content-media-buttons" class="wp-media-buttons">
                                    <button type="button" class="button insert-media add_media" data-editor="content">
                                        <span class="wp-media-buttons-icon"></span>
                                        <?php _e( 'Add Media', 'woo-offers' ); ?>
                                    </button>
                                </div>
                            </div>
                            <div id="wp-content-editor-container" class="wp-editor-container">
                                <label class="screen-reader-text" for="content"><?php _e( 'Offer Description', 'woo-offers' ); ?></label>
                                <?php
                                wp_editor( 
                                    $offer_data['description'], 
                                    'offer_description',
                                    [
                                        'textarea_name' => 'offer_description',
                                        'textarea_rows' => 5,
                                        'media_buttons' => true,
                                        'teeny' => false,
                                        'quicktags' => true,
                                        'tinymce' => [
                                            'resize' => false,
                                            'wp_autoresize_on' => true,
                                            'add_unload_trigger' => false
                                        ]
                                    ]
                                );
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- WordPress Registered Metaboxes -->
                    <?php do_meta_boxes( 'woo-offers-edit', 'normal', null ); ?>

                    <!-- Schedule Metabox -->
                    <div id="offer-schedule" class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle ui-sortable-handle">
                                <span><?php _e( 'Schedule', 'woo-offers' ); ?></span>
                            </h2>
                            <div class="handle-actions hide-if-no-js">
                                <button type="button" class="handle-order-higher" aria-describedby="offer-schedule-handle-order-higher-description">
                                    <span class="screen-reader-text"><?php _e( 'Move up', 'woo-offers' ); ?></span>
                                    <span class="order-higher-indicator" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="handle-order-lower" aria-describedby="offer-schedule-handle-order-lower-description">
                                    <span class="screen-reader-text"><?php _e( 'Move down', 'woo-offers' ); ?></span>
                                    <span class="order-lower-indicator" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="handlediv" aria-expanded="true">
                                    <span class="screen-reader-text"><?php _e( 'Toggle panel: Schedule', 'woo-offers' ); ?></span>
                                    <span class="toggle-indicator" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="start_date"><?php _e( 'Start Date', 'woo-offers' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="datetime-local" 
                                               name="start_date" 
                                               id="start_date" 
                                               value="<?php echo esc_attr( $offer_data['start_date'] ? date( 'Y-m-d\TH:i', strtotime( $offer_data['start_date'] ) ) : '' ); ?>" 
                                               class="regular-text">
                                        <p class="description">
                                            <?php _e( 'Leave blank to start immediately when activated.', 'woo-offers' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="end_date"><?php _e( 'End Date', 'woo-offers' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="datetime-local" 
                                               name="end_date" 
                                               id="end_date" 
                                               value="<?php echo esc_attr( $offer_data['end_date'] ? date( 'Y-m-d\TH:i', strtotime( $offer_data['end_date'] ) ) : '' ); ?>" 
                                               class="regular-text">
                                        <p class="description">
                                            <?php _e( 'Leave blank for no expiration date.', 'woo-offers' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    
                    <!-- Publish Box -->
                    <div id="submitdiv" class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle ui-sortable-handle">
                                <span><?php _e( 'Publish', 'woo-offers' ); ?></span>
                            </h2>
                        </div>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                
                                <div id="minor-publishing">
                                    <!-- Save Draft -->
                                    <div id="minor-publishing-actions">
                                        <div id="save-action">
                                            <input type="submit" name="save_draft" id="save-post" value="<?php _e( 'Save Draft', 'woo-offers' ); ?>" class="button">
                                            <span class="spinner"></span>
                                        </div>
                                        <div id="preview-action">
                                            <a class="preview button" href="#" target="wp-preview">
                                                <?php _e( 'Preview', 'woo-offers' ); ?>
                                            </a>
                                        </div>
                                        <div class="clear"></div>
                                    </div>

                                    <div id="misc-publishing-actions">
                                        <!-- Status -->
                                        <div class="misc-pub-section misc-pub-post-status">
                                            <label for="offer_status"><?php _e( 'Status:', 'woo-offers' ); ?></label>
                                            <span id="post-status-display">
                                                <select name="offer_status" id="offer_status">
                                                    <option value="draft" <?php selected( $offer_data['status'], 'draft' ); ?>>
                                                        <?php _e( 'Draft', 'woo-offers' ); ?>
                                                    </option>
                                                    <option value="active" <?php selected( $offer_data['status'], 'active' ); ?>>
                                                        <?php _e( 'Active', 'woo-offers' ); ?>
                                                    </option>
                                                    <option value="inactive" <?php selected( $offer_data['status'], 'inactive' ); ?>>
                                                        <?php _e( 'Inactive', 'woo-offers' ); ?>
                                                    </option>
                                                    <option value="scheduled" <?php selected( $offer_data['status'], 'scheduled' ); ?>>
                                                        <?php _e( 'Scheduled', 'woo-offers' ); ?>
                                                    </option>
                                                </select>
                                            </span>
                                        </div>

                                        <!-- Created Date (for existing offers) -->
                                        <?php if ( $is_editing ): ?>
                                        <div class="misc-pub-section curtime misc-pub-curtime">
                                            <span id="timestamp">
                                                <?php printf( 
                                                    __( 'Created on: <b>%s</b>', 'woo-offers' ),
                                                    date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), strtotime( $offer->created_at ?? '' ) )
                                                ); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="delete-action">
                                        <?php if ( $is_editing ): ?>
                                            <a class="submitdelete deletion" 
                                               href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=woo-offers-offers&action=delete&id=' . $offer_id ), 'delete_offer_' . $offer_id ); ?>"
                                               onclick="return confirm('<?php _e( 'Are you sure you want to delete this offer?', 'woo-offers' ); ?>');">
                                                <?php _e( 'Move to Trash', 'woo-offers' ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" 
                                               name="publish" 
                                               id="publish" 
                                               class="button button-primary button-large" 
                                               value="<?php echo $is_editing ? __( 'Update Offer', 'woo-offers' ) : __( 'Publish Offer', 'woo-offers' ); ?>">
                                    </div>
                                    <div class="clear"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Offer Statistics (for existing offers) -->
                    <?php if ( $is_editing ): ?>
                    <div id="offer-stats" class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle ui-sortable-handle">
                                <span><?php _e( 'Statistics', 'woo-offers' ); ?></span>
                            </h2>
                        </div>
                        <div class="inside">
                            <?php
                            // Get offer statistics
                            $stats = $wpdb->get_row( 
                                $wpdb->prepare( 
                                    "SELECT 
                                        COUNT(*) as total_conversions,
                                        SUM(revenue) as total_revenue,
                                        AVG(revenue) as avg_revenue
                                     FROM {$wpdb->prefix}woo_offers_analytics 
                                     WHERE offer_id = %d", 
                                    $offer_id 
                                ) 
                            );
                            ?>
                            <div class="woo-offers-stats-grid">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo number_format( $stats->total_conversions ?? 0 ); ?></div>
                                    <div class="stat-label"><?php _e( 'Total Conversions', 'woo-offers' ); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo wc_price( $stats->total_revenue ?? 0 ); ?></div>
                                    <div class="stat-label"><?php _e( 'Total Revenue', 'woo-offers' ); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo wc_price( $stats->avg_revenue ?? 0 ); ?></div>
                                    <div class="stat-label"><?php _e( 'Avg. Revenue', 'woo-offers' ); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

            </div>
        </div>
    </form>
</div>

<!-- JavaScript for enhanced form validation and behavior -->
<script>
jQuery(document).ready(function($) {
    // Initialize metaboxes
    if (typeof postboxes !== 'undefined') {
        postboxes.add_postbox_toggles('woo-offers-edit');
    }
    
    // Initialize form validation
    initFormValidation();
    
    // Auto-save functionality
    let autoSaveTimeout;
    $('#woo-offers-edit-form input, #woo-offers-edit-form select, #woo-offers-edit-form textarea').on('change input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Could implement auto-save here if needed
        }, 2000);
    });
});

// Enhanced form validation system
function initFormValidation() {
    const $ = jQuery;
    const $form = $('#woo-offers-edit-form');
    const $submitButtons = $('#publish, #save-post');
    
    // Validation rules
    const validationRules = {
        'offer_name': {
            required: true,
            minLength: 3,
            maxLength: 200,
            message: '<?php _e( 'Offer name is required and must be between 3-200 characters.', 'woo-offers' ); ?>'
        },
        'offer_type': {
            required: true,
            message: '<?php _e( 'Please select an offer type.', 'woo-offers' ); ?>'
        },
        'offer_value': {
            required: function() {
                const type = $('input[name="offer_type"]:checked').val();
                return ['percentage', 'fixed', 'quantity'].includes(type);
            },
            min: 0.01,
            max: function() {
                const type = $('input[name="offer_type"]:checked').val();
                return type === 'percentage' ? 100 : 999999;
            },
            message: function() {
                const type = $('input[name="offer_type"]:checked').val();
                if (type === 'percentage') {
                    return '<?php _e( 'Percentage value must be between 0.01 and 100.', 'woo-offers' ); ?>';
                }
                return '<?php _e( 'Offer value must be greater than 0.', 'woo-offers' ); ?>';
            }
        },
        'minimum_amount': {
            min: 0,
            lessThan: 'maximum_amount',
            message: '<?php _e( 'Minimum amount must be positive and less than maximum amount.', 'woo-offers' ); ?>'
        },
        'maximum_amount': {
            min: 0,
            greaterThan: 'minimum_amount',
            message: '<?php _e( 'Maximum amount must be greater than minimum amount.', 'woo-offers' ); ?>'
        },
        'usage_limit': {
            min: 1,
            integer: true,
            message: '<?php _e( 'Usage limit must be a positive integer.', 'woo-offers' ); ?>'
        },
        'start_date': {
            beforeDate: 'end_date',
            message: '<?php _e( 'Start date must be before end date.', 'woo-offers' ); ?>'
        },
        'end_date': {
            afterDate: 'start_date',
            message: '<?php _e( 'End date must be after start date.', 'woo-offers' ); ?>'
        }
    };
    
    // Add real-time validation to all fields
    Object.keys(validationRules).forEach(function(fieldName) {
        const $field = $('[name="' + fieldName + '"]');
        if ($field.length) {
            // Handle both regular inputs and radio buttons
            if ($field.attr('type') === 'radio') {
                $field.on('change', function() {
                    validateField(fieldName, validationRules[fieldName]);
                    // Also validate dependent fields
                    if (fieldName === 'offer_type') {
                        validateField('offer_value', validationRules['offer_value']);
                    }
                    updateSubmitButtonState();
                });
            } else {
                $field.on('blur change input', function() {
                    validateField(fieldName, validationRules[fieldName]);
                    updateSubmitButtonState();
                });
            }
        }
    });
    
    // Form submission validation
    $form.on('submit', function(e) {
        let isValid = true;
        let firstErrorField = null;
        
        // Clear previous submission notices
        $('.woo-offers-validation-notice').remove();
        
        // Validate all fields
        Object.keys(validationRules).forEach(function(fieldName) {
            if (!validateField(fieldName, validationRules[fieldName])) {
                isValid = false;
                if (!firstErrorField) {
                    firstErrorField = $('[name="' + fieldName + '"]');
                }
            }
        });
        
        // Check if at least one product is selected for certain offer types
        const offerType = $('input[name="offer_type"]:checked').val();
        if (['bogo', 'bundle'].includes(offerType)) {
            const hasProducts = $('#selected-products-list .selected-product-item').length > 0;
            if (!hasProducts) {
                isValid = false;
                showValidationNotice('<?php _e( 'Please select at least one product for this offer type.', 'woo-offers' ); ?>', 'error');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first error
            if (firstErrorField) {
                $('html, body').animate({
                    scrollTop: firstErrorField.offset().top - 100
                }, 500);
                firstErrorField.focus();
            }
            
            showValidationSummary();
            return false;
        }
        
        // Show loading state
        $('.spinner').addClass('is-active');
        $submitButtons.prop('disabled', true);
        
        const isPublish = $(e.target).find('input[name="publish"]').length > 0 || 
                         $(e.target).attr('name') === 'publish';
        
        if (isPublish) {
            $('#publish').val('<?php _e( 'Publishing...', 'woo-offers' ); ?>');
        } else {
            $('#save-post').val('<?php _e( 'Saving...', 'woo-offers' ); ?>');
        }
    });
}

// Validate individual field
function validateField(fieldName, rules) {
    const $ = jQuery;
    const $field = $('[name="' + fieldName + '"]');
    let value;
    
    // Handle different input types
    if ($field.attr('type') === 'radio') {
        value = $field.filter(':checked').val() || '';
    } else {
        value = $field.val() || '';
    }
    
    const $errorContainer = getOrCreateErrorContainer($field);
    
    // Clear previous errors
    clearFieldError($field, $errorContainer);
    
    // Required validation
    if (rules.required) {
        const isRequired = typeof rules.required === 'function' ? rules.required() : rules.required;
        if (isRequired && (!value || value.trim() === '')) {
            const message = typeof rules.message === 'function' ? rules.message() : rules.message;
            showFieldError($field, $errorContainer, message);
            return false;
        }
    }
    
    // Skip other validations if field is empty and not required
    if (!value || value.trim() === '') {
        return true;
    }
    
    // Length validation
    if (rules.minLength && value.length < rules.minLength) {
        const message = typeof rules.message === 'function' ? rules.message() : rules.message;
        showFieldError($field, $errorContainer, message);
        return false;
    }
    
    if (rules.maxLength && value.length > rules.maxLength) {
        const message = typeof rules.message === 'function' ? rules.message() : rules.message;
        showFieldError($field, $errorContainer, message);
        return false;
    }
    
    // Numeric validations
    if (rules.min !== undefined || rules.max !== undefined || rules.integer) {
        const numValue = parseFloat(value);
        
        if (isNaN(numValue)) {
            showFieldError($field, $errorContainer, '<?php _e( 'Please enter a valid number.', 'woo-offers' ); ?>');
            return false;
        }
        
        if (rules.integer && !Number.isInteger(numValue)) {
            showFieldError($field, $errorContainer, '<?php _e( 'Please enter a whole number.', 'woo-offers' ); ?>');
            return false;
        }
        
        if (rules.min !== undefined && numValue < rules.min) {
            const message = typeof rules.message === 'function' ? rules.message() : rules.message;
            showFieldError($field, $errorContainer, message);
            return false;
        }
        
        if (rules.max !== undefined) {
            const maxValue = typeof rules.max === 'function' ? rules.max() : rules.max;
            if (maxValue !== null && numValue > maxValue) {
                const message = typeof rules.message === 'function' ? rules.message() : rules.message;
                showFieldError($field, $errorContainer, message);
                return false;
            }
        }
    }
    
    // Comparison validations
    if (rules.lessThan) {
        const compareValue = parseFloat($('[name="' + rules.lessThan + '"]').val());
        const currentValue = parseFloat(value);
        
        if (!isNaN(currentValue) && !isNaN(compareValue) && currentValue >= compareValue) {
            const message = typeof rules.message === 'function' ? rules.message() : rules.message;
            showFieldError($field, $errorContainer, message);
            return false;
        }
    }
    
    if (rules.greaterThan) {
        const compareValue = parseFloat($('[name="' + rules.greaterThan + '"]').val());
        const currentValue = parseFloat(value);
        
        if (!isNaN(currentValue) && !isNaN(compareValue) && currentValue <= compareValue) {
            const message = typeof rules.message === 'function' ? rules.message() : rules.message;
            showFieldError($field, $errorContainer, message);
            return false;
        }
    }
    
    // Date validations
    if (rules.beforeDate) {
        const compareDate = new Date($('[name="' + rules.beforeDate + '"]').val());
        const currentDate = new Date(value);
        
        if (!isNaN(currentDate.getTime()) && !isNaN(compareDate.getTime()) && currentDate >= compareDate) {
            const message = typeof rules.message === 'function' ? rules.message() : rules.message;
            showFieldError($field, $errorContainer, message);
            return false;
        }
    }
    
    if (rules.afterDate) {
        const compareDate = new Date($('[name="' + rules.afterDate + '"]').val());
        const currentDate = new Date(value);
        
        if (!isNaN(currentDate.getTime()) && !isNaN(compareDate.getTime()) && currentDate <= compareDate) {
            const message = typeof rules.message === 'function' ? rules.message() : rules.message;
            showFieldError($field, $errorContainer, message);
            return false;
        }
    }
    
    return true;
}

// Show field error
function showFieldError($field, $errorContainer, message) {
    $field.addClass('form-invalid');
    $errorContainer.html('<span class="form-error-message">' + message + '</span>').show();
}

// Clear field error
function clearFieldError($field, $errorContainer) {
    $field.removeClass('form-invalid');
    $errorContainer.empty().hide();
}

// Get or create error container for field
function getOrCreateErrorContainer($field) {
    const $ = jQuery;
    let $container = $field.siblings('.form-error-container');
    
    if ($container.length === 0) {
        $container = $('<div class="form-error-container"></div>');
        
        // Handle radio buttons differently
        if ($field.attr('type') === 'radio') {
            const $lastRadio = $('[name="' + $field.attr('name') + '"]').last();
            $lastRadio.closest('label').after($container);
        } else {
            $field.after($container);
        }
    }
    
    return $container;
}

// Update submit button state
function updateSubmitButtonState() {
    const $ = jQuery;
    const $form = $('#woo-offers-edit-form');
    const $submitButtons = $('#publish, #save-post');
    const hasErrors = $form.find('.form-invalid').length > 0;
    
    $submitButtons.prop('disabled', hasErrors);
    
    if (hasErrors) {
        $submitButtons.addClass('button-disabled');
    } else {
        $submitButtons.removeClass('button-disabled');
    }
}

// Show validation summary
function showValidationSummary() {
    const $ = jQuery;
    const $form = $('#woo-offers-edit-form');
    const $errors = $form.find('.form-error-message');
    
    if ($errors.length > 0) {
        const errorCount = $errors.length;
        const message = errorCount === 1 
            ? '<?php _e( 'Please fix the validation error below.', 'woo-offers' ); ?>'
            : '<?php printf( __( 'Please fix the %s validation errors below.', 'woo-offers' ), 'errorCount' ); ?>'.replace('errorCount', errorCount);
        
        showValidationNotice(message, 'error');
    }
}

// Show validation notice
function showValidationNotice(message, type) {
    const $ = jQuery;
    const $form = $('#woo-offers-edit-form');
    
    // Remove existing notices
    $('.woo-offers-validation-notice').remove();
    
    // Add new notice
    const $notice = $('<div class="notice notice-' + type + ' woo-offers-validation-notice is-dismissible"><p>' + message + '</p></div>');
    $form.prepend($notice);
    
    // Make notice dismissible
    $notice.on('click', '.notice-dismiss', function() {
        $notice.fadeOut();
    });
    
    // Auto-hide after 7 seconds
    setTimeout(function() {
        $notice.fadeOut();
    }, 7000);
}
</script> 