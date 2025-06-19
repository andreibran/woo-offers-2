<?php
/**
 * General Settings Metabox Template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get offer data passed from the metabox callback
$offer_data = $offer_data ?? [];
$offer_type = $offer_data['type'] ?? 'percentage';
$offer_value = $offer_data['value'] ?? '';
$usage_limit = $offer_data['usage_limit'] ?? '';
$minimum_amount = $offer_data['minimum_amount'] ?? '';
$maximum_amount = $offer_data['maximum_amount'] ?? '';
?>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label><?php _e( 'Offer Type', 'woo-offers' ); ?></label>
            </th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span><?php _e( 'Offer Type', 'woo-offers' ); ?></span>
                    </legend>
                    
                    <div class="offer-type-options">
                        <label>
                            <input type="radio" name="offer_type" value="percentage" <?php checked( $offer_type, 'percentage' ); ?> />
                            <span class="offer-type-label">
                                <strong><?php _e( 'Percentage Discount', 'woo-offers' ); ?></strong>
                                <small class="offer-type-description"><?php _e( 'Discount as a percentage of the product price', 'woo-offers' ); ?></small>
                            </span>
                        </label>
                        
                        <label>
                            <input type="radio" name="offer_type" value="fixed" <?php checked( $offer_type, 'fixed' ); ?> />
                            <span class="offer-type-label">
                                <strong><?php _e( 'Fixed Amount Discount', 'woo-offers' ); ?></strong>
                                <small class="offer-type-description"><?php _e( 'Fixed dollar amount discount', 'woo-offers' ); ?></small>
                            </span>
                        </label>
                        
                        <label>
                            <input type="radio" name="offer_type" value="bogo" <?php checked( $offer_type, 'bogo' ); ?> />
                            <span class="offer-type-label">
                                <strong><?php _e( 'Buy One Get One (BOGO)', 'woo-offers' ); ?></strong>
                                <small class="offer-type-description"><?php _e( 'Customer gets free or discounted items when purchasing', 'woo-offers' ); ?></small>
                            </span>
                        </label>
                        
                        <label>
                            <input type="radio" name="offer_type" value="bundle" <?php checked( $offer_type, 'bundle' ); ?> />
                            <span class="offer-type-label">
                                <strong><?php _e( 'Bundle Discount', 'woo-offers' ); ?></strong>
                                <small class="offer-type-description"><?php _e( 'Discount when purchasing multiple items together', 'woo-offers' ); ?></small>
                            </span>
                        </label>
                        
                        <label>
                            <input type="radio" name="offer_type" value="quantity" <?php checked( $offer_type, 'quantity' ); ?> />
                            <span class="offer-type-label">
                                <strong><?php _e( 'Quantity Discount', 'woo-offers' ); ?></strong>
                                <small class="offer-type-description"><?php _e( 'Discount based on quantity purchased', 'woo-offers' ); ?></small>
                            </span>
                        </label>
                        
                        <label>
                            <input type="radio" name="offer_type" value="free_shipping" <?php checked( $offer_type, 'free_shipping' ); ?> />
                            <span class="offer-type-label">
                                <strong><?php _e( 'Free Shipping', 'woo-offers' ); ?></strong>
                                <small class="offer-type-description"><?php _e( 'Remove shipping costs for qualifying orders', 'woo-offers' ); ?></small>
                            </span>
                        </label>
                    </div>
                </fieldset>
            </td>
        </tr>
        
        <tr class="offer-value-row">
            <th scope="row">
                <label for="offer_value"><?php _e( 'Discount Value', 'woo-offers' ); ?></label>
            </th>
            <td>
                <input type="number" 
                       name="offer_value" 
                       id="offer_value" 
                       value="<?php echo esc_attr( $offer_value ); ?>" 
                       class="regular-text" 
                       min="0" 
                       step="0.01"
                       placeholder="0">
                <p class="description offer-value-description">
                    <?php _e( 'Enter the discount value (percentage for % discounts, amount for fixed discounts).', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="usage_limit">
                    <?php _e( 'Usage Limit', 'woo-offers' ); ?>
                    <span class="woo-offers-tooltip" data-tooltip="<?php esc_attr_e( 'Controls the total number of times this offer can be used across all customers. Setting a limit creates scarcity and urgency. Leave blank for unlimited usage. Consider your inventory levels and marketing goals when setting limits.', 'woo-offers' ); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
            </th>
            <td>
                <input type="number" 
                       name="usage_limit" 
                       id="usage_limit" 
                       value="<?php echo esc_attr( $usage_limit ); ?>" 
                       class="regular-text" 
                       min="0"
                       placeholder="<?php esc_attr_e( 'Unlimited', 'woo-offers' ); ?>">
                <p class="description">
                    <?php _e( 'Control how many times this offer can be used total across all customers. Leave blank for unlimited usage. Setting a limit creates urgency and exclusivity. Consider your inventory and marketing goals when setting limits.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="minimum_amount">
                    <?php _e( 'Minimum Order Amount', 'woo-offers' ); ?>
                    <span class="woo-offers-tooltip" data-tooltip="<?php esc_attr_e( 'Sets the minimum cart total required for this offer to activate. This encourages higher order values and can improve your average order value. Consider your product price ranges when setting this value. Too high may reduce conversions, too low may not drive desired behavior.', 'woo-offers' ); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
            </th>
            <td>
                <input type="number" 
                       name="minimum_amount" 
                       id="minimum_amount" 
                       value="<?php echo esc_attr( $minimum_amount ); ?>" 
                       class="regular-text" 
                       min="0" 
                       step="0.01"
                       placeholder="0.00">
                <p class="description">
                    <?php _e( 'Set the minimum cart total required for this offer to activate. This encourages higher order values and can improve your average order value. Leave blank if you want the offer to apply to any order size. Consider your product prices when setting minimums.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="maximum_amount">
                    <?php _e( 'Maximum Order Amount', 'woo-offers' ); ?>
                    <span class="woo-offers-tooltip" data-tooltip="<?php esc_attr_e( 'Sets a maximum cart total limit for this offer. Useful for controlling discount costs on very large orders or targeting specific customer segments. Use carefully as it may discourage larger purchases. Leave blank for no maximum limit.', 'woo-offers' ); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
            </th>
            <td>
                <input type="number" 
                       name="maximum_amount" 
                       id="maximum_amount" 
                       value="<?php echo esc_attr( $maximum_amount ); ?>" 
                       class="regular-text" 
                       min="0" 
                       step="0.01"
                       placeholder="<?php esc_attr_e( 'No maximum', 'woo-offers' ); ?>">
                <p class="description">
                    <?php _e( 'Set a maximum cart total limit for this offer. This is useful for controlling discount costs on very large orders or targeting specific customer segments. Leave blank for no maximum limit. Use carefully as it may discourage larger purchases.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>

<script>
jQuery(document).ready(function($) {
    // Handle offer type changes
    $('input[name="offer_type"]').on('change', function() {
        var selectedType = $(this).val();
        var $valueField = $('#offer_value');
        var $valueDescription = $('.offer-value-description');
        var $valueRow = $('.offer-value-row');
        
        // Reset field attributes
        $valueField.removeAttr('max readonly').val($valueField.val());
        
        switch(selectedType) {
            case 'percentage':
                $valueField.attr('max', '100').attr('placeholder', '10');
                $valueDescription.text('<?php _e( 'Enter percentage discount (0-100)', 'woo-offers' ); ?>');
                $valueRow.show();
                break;
                
            case 'fixed':
                $valueField.removeAttr('max').attr('placeholder', '10.00');
                $valueDescription.text('<?php _e( 'Enter fixed discount amount', 'woo-offers' ); ?>');
                $valueRow.show();
                break;
                
            case 'quantity':
                $valueField.removeAttr('max').attr('placeholder', '2');
                $valueDescription.text('<?php _e( 'Enter quantity threshold (e.g., 2 for "buy 2, get discount")', 'woo-offers' ); ?>');
                $valueRow.show();
                break;
                
            case 'free_shipping':
                $valueField.val('0').prop('readonly', true);
                $valueDescription.text('<?php _e( 'Free shipping offers do not require a value', 'woo-offers' ); ?>');
                $valueRow.show();
                break;
                
            case 'bogo':
                $valueField.removeAttr('max').attr('placeholder', '1');
                $valueDescription.text('<?php _e( 'Enter quantity to get free (e.g., 1 for "buy 1 get 1 free")', 'woo-offers' ); ?>');
                $valueRow.show();
                break;
                
            case 'bundle':
                $valueField.removeAttr('max').attr('placeholder', '10.00');
                $valueDescription.text('<?php _e( 'Enter bundle discount amount or percentage', 'woo-offers' ); ?>');
                $valueRow.show();
                break;
                
            default:
                $valueField.removeAttr('max readonly').attr('placeholder', '');
                $valueDescription.text('<?php _e( 'Enter the discount value for this offer type', 'woo-offers' ); ?>');
                $valueRow.show();
        }
    });
    
    // Trigger change on page load to set initial state
    $('input[name="offer_type"]:checked').trigger('change');

    // Initialize tooltips
    initTooltips();
});

function initTooltips() {
    // Create tooltip container if it doesn't exist
    if (!$('#woo-offers-tooltip').length) {
        $('body').append('<div id="woo-offers-tooltip" class="woo-offers-tooltip-container"></div>');
    }
    
    var $tooltip = $('#woo-offers-tooltip');
    
    // Show tooltip on hover
    $(document).on('mouseenter', '.woo-offers-tooltip', function(e) {
        var tooltipText = $(this).data('tooltip');
        if (tooltipText) {
            $tooltip.html(tooltipText).show();
            positionTooltip(e, $tooltip);
        }
    });
    
    // Hide tooltip on leave
    $(document).on('mouseleave', '.woo-offers-tooltip', function() {
        $tooltip.hide();
    });
    
    // Update tooltip position on mouse move
    $(document).on('mousemove', '.woo-offers-tooltip', function(e) {
        if ($tooltip.is(':visible')) {
            positionTooltip(e, $tooltip);
        }
    });
}

function positionTooltip(e, $tooltip) {
    var mouseX = e.pageX;
    var mouseY = e.pageY;
    var tooltipWidth = $tooltip.outerWidth();
    var tooltipHeight = $tooltip.outerHeight();
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var scrollLeft = $(window).scrollLeft();
    var scrollTop = $(window).scrollTop();
    
    // Default position: to the right and below cursor
    var left = mouseX + 10;
    var top = mouseY + 10;
    
    // Adjust if tooltip would go off screen
    if (left + tooltipWidth > windowWidth + scrollLeft) {
        left = mouseX - tooltipWidth - 10;
    }
    
    if (top + tooltipHeight > windowHeight + scrollTop) {
        top = mouseY - tooltipHeight - 10;
    }
    
    $tooltip.css({
        left: left + 'px',
        top: top + 'px'
    });
}
</script>

<style>
/* Tooltip Styles */
.woo-offers-tooltip {
    display: inline-block;
    margin-left: 5px;
    cursor: help;
    color: #666;
    vertical-align: top;
}

.woo-offers-tooltip .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    line-height: 16px;
}

.woo-offers-tooltip:hover .dashicons {
    color: #0073aa;
}

.woo-offers-tooltip-container {
    position: absolute;
    background: #333;
    color: #fff;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    line-height: 1.4;
    max-width: 250px;
    z-index: 9999;
    display: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    word-wrap: break-word;
}

.woo-offers-tooltip-container:before {
    content: '';
    position: absolute;
    width: 0;
    height: 0;
    border: 5px solid transparent;
    border-bottom-color: #333;
    top: -10px;
    left: 10px;
}

/* Form enhancements for tooltips */
.form-table th label {
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-table th .woo-offers-tooltip {
    flex-shrink: 0;
}
</style> 