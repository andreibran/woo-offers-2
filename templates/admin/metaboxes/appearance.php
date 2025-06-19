<?php
/**
 * Appearance Settings Metabox Template
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
$appearance = $offer_data['appearance'] ?? [];

// Default appearance settings
$defaults = [
    'background_color' => '#ffffff',
    'text_color' => '#333333',
    'accent_color' => '#e92d3b',
    'border_style' => 'solid',
    'border_width' => '1',
    'border_color' => '#dddddd',
    'border_radius' => '4',
    'layout' => 'card',
    'position' => 'before_add_to_cart',
    'animation' => 'none',
    'shadow' => 'light'
];

$appearance = wp_parse_args( $appearance, $defaults );
?>

<table class="form-table">
    <tbody>
        <!-- Color Settings -->
        <tr>
            <th scope="row">
                <label><?php _e( 'Color Scheme', 'woo-offers' ); ?></label>
            </th>
            <td>
                <div class="color-scheme-controls">
                    <div class="color-control">
                        <label for="background_color"><?php _e( 'Background Color', 'woo-offers' ); ?></label>
                        <input type="color" 
                               name="appearance[background_color]" 
                               id="background_color" 
                               value="<?php echo esc_attr( $appearance['background_color'] ); ?>" 
                               class="color-picker" />
                        <input type="text" 
                               name="appearance[background_color_text]" 
                               value="<?php echo esc_attr( $appearance['background_color'] ); ?>" 
                               class="color-text-input regular-text" 
                               placeholder="#ffffff" />
                    </div>
                    
                    <div class="color-control">
                        <label for="text_color"><?php _e( 'Text Color', 'woo-offers' ); ?></label>
                        <input type="color" 
                               name="appearance[text_color]" 
                               id="text_color" 
                               value="<?php echo esc_attr( $appearance['text_color'] ); ?>" 
                               class="color-picker" />
                        <input type="text" 
                               name="appearance[text_color_text]" 
                               value="<?php echo esc_attr( $appearance['text_color'] ); ?>" 
                               class="color-text-input regular-text" 
                               placeholder="#333333" />
                    </div>
                    
                    <div class="color-control">
                        <label for="accent_color"><?php _e( 'Accent Color', 'woo-offers' ); ?></label>
                        <input type="color" 
                               name="appearance[accent_color]" 
                               id="accent_color" 
                               value="<?php echo esc_attr( $appearance['accent_color'] ); ?>" 
                               class="color-picker" />
                        <input type="text" 
                               name="appearance[accent_color_text]" 
                               value="<?php echo esc_attr( $appearance['accent_color'] ); ?>" 
                               class="color-text-input regular-text" 
                               placeholder="#e92d3b" />
                    </div>
                </div>
                <p class="description">
                    <?php _e( 'Customize the color scheme of your offer. The background color sets the main container color, text color affects all text within the offer, and accent color is used for buttons, links, and highlights. Tip: Use high contrast between text and background for better readability.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <!-- Border Settings -->
        <tr>
            <th scope="row">
                <label><?php _e( 'Border & Shape', 'woo-offers' ); ?></label>
            </th>
            <td>
                <div class="border-controls">
                    <div class="border-control-group">
                        <label for="border_style"><?php _e( 'Border Style', 'woo-offers' ); ?></label>
                        <select name="appearance[border_style]" id="border_style" class="regular-text">
                            <option value="none" <?php selected( $appearance['border_style'], 'none' ); ?>><?php _e( 'None', 'woo-offers' ); ?></option>
                            <option value="solid" <?php selected( $appearance['border_style'], 'solid' ); ?>><?php _e( 'Solid', 'woo-offers' ); ?></option>
                            <option value="dashed" <?php selected( $appearance['border_style'], 'dashed' ); ?>><?php _e( 'Dashed', 'woo-offers' ); ?></option>
                            <option value="dotted" <?php selected( $appearance['border_style'], 'dotted' ); ?>><?php _e( 'Dotted', 'woo-offers' ); ?></option>
                        </select>
                    </div>
                    
                    <div class="border-control-group">
                        <label for="border_width"><?php _e( 'Border Width (px)', 'woo-offers' ); ?></label>
                        <input type="number" 
                               name="appearance[border_width]" 
                               id="border_width" 
                               value="<?php echo esc_attr( $appearance['border_width'] ); ?>" 
                               class="small-text" 
                               min="0" 
                               max="10" />
                    </div>
                    
                    <div class="border-control-group">
                        <label for="border_color"><?php _e( 'Border Color', 'woo-offers' ); ?></label>
                        <input type="color" 
                               name="appearance[border_color]" 
                               id="border_color" 
                               value="<?php echo esc_attr( $appearance['border_color'] ); ?>" 
                               class="color-picker" />
                    </div>
                    
                    <div class="border-control-group">
                        <label for="border_radius"><?php _e( 'Border Radius (px)', 'woo-offers' ); ?></label>
                        <input type="number" 
                               name="appearance[border_radius]" 
                               id="border_radius" 
                               value="<?php echo esc_attr( $appearance['border_radius'] ); ?>" 
                               class="small-text" 
                               min="0" 
                               max="50" />
                    </div>
                </div>
                <p class="description">
                    <?php _e( 'Configure the border appearance. Choose "None" for a borderless design, or select a style and customize the width, color, and corner radius. Higher border radius values create more rounded corners.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <!-- Layout Settings -->
        <tr>
            <th scope="row">
                <label for="layout"><?php _e( 'Layout Style', 'woo-offers' ); ?></label>
            </th>
            <td>
                <select name="appearance[layout]" id="layout" class="regular-text">
                    <option value="card" <?php selected( $appearance['layout'], 'card' ); ?>><?php _e( 'Card Layout', 'woo-offers' ); ?></option>
                    <option value="banner" <?php selected( $appearance['layout'], 'banner' ); ?>><?php _e( 'Banner Layout', 'woo-offers' ); ?></option>
                    <option value="inline" <?php selected( $appearance['layout'], 'inline' ); ?>><?php _e( 'Inline Layout', 'woo-offers' ); ?></option>
                    <option value="modal" <?php selected( $appearance['layout'], 'modal' ); ?>><?php _e( 'Modal Popup', 'woo-offers' ); ?></option>
                    <option value="slide_in" <?php selected( $appearance['layout'], 'slide_in' ); ?>><?php _e( 'Slide-in Panel', 'woo-offers' ); ?></option>
                </select>
                <p class="description">
                    <?php _e( 'Select the visual presentation style. Card layout creates a contained box design, Banner layout spans full width, Inline layout blends with page content, Modal popup appears as an overlay, and Slide-in panel appears from the side of the screen.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <!-- Position Settings -->
        <tr>
            <th scope="row">
                <label for="position">
                    <?php _e( 'Display Position', 'woo-offers' ); ?>
                    <span class="woo-offers-tooltip" data-tooltip="<?php esc_attr_e( 'Choose where to display the offer on product pages. Positions before/after add to cart are most effective for conversion. Product summary positions are good for informational offers. Sidebar and floating positions are less intrusive but may have lower visibility.', 'woo-offers' ); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
            </th>
            <td>
                <select name="appearance[position]" id="position" class="regular-text">
                    <option value="before_add_to_cart" <?php selected( $appearance['position'], 'before_add_to_cart' ); ?>><?php _e( 'Before Add to Cart Button', 'woo-offers' ); ?></option>
                    <option value="after_add_to_cart" <?php selected( $appearance['position'], 'after_add_to_cart' ); ?>><?php _e( 'After Add to Cart Button', 'woo-offers' ); ?></option>
                    <option value="before_product_summary" <?php selected( $appearance['position'], 'before_product_summary' ); ?>><?php _e( 'Before Product Summary', 'woo-offers' ); ?></option>
                    <option value="after_product_summary" <?php selected( $appearance['position'], 'after_product_summary' ); ?>><?php _e( 'After Product Summary', 'woo-offers' ); ?></option>
                    <option value="sidebar" <?php selected( $appearance['position'], 'sidebar' ); ?>><?php _e( 'Sidebar', 'woo-offers' ); ?></option>
                    <option value="floating" <?php selected( $appearance['position'], 'floating' ); ?>><?php _e( 'Floating Position', 'woo-offers' ); ?></option>
                </select>
                <p class="description">
                    <?php _e( 'Choose where to display the offer on product pages. Positions before/after add to cart are most effective for conversion. Product summary positions are good for informational offers. Sidebar and floating positions are less intrusive but may have lower visibility.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <!-- Animation Settings -->
        <tr>
            <th scope="row">
                <label for="animation">
                    <?php _e( 'Animation Effect', 'woo-offers' ); ?>
                    <span class="woo-offers-tooltip" data-tooltip="<?php esc_attr_e( 'Add motion to your offer appearance. Subtle animations like Fade In work well for most cases. Bounce and Zoom In are more attention-grabbing but use sparingly. Choose None for better performance on mobile devices and faster loading.', 'woo-offers' ); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
            </th>
            <td>
                <select name="appearance[animation]" id="animation" class="regular-text">
                    <option value="none" <?php selected( $appearance['animation'], 'none' ); ?>><?php _e( 'None', 'woo-offers' ); ?></option>
                    <option value="fade_in" <?php selected( $appearance['animation'], 'fade_in' ); ?>><?php _e( 'Fade In', 'woo-offers' ); ?></option>
                    <option value="slide_down" <?php selected( $appearance['animation'], 'slide_down' ); ?>><?php _e( 'Slide Down', 'woo-offers' ); ?></option>
                    <option value="slide_up" <?php selected( $appearance['animation'], 'slide_up' ); ?>><?php _e( 'Slide Up', 'woo-offers' ); ?></option>
                    <option value="zoom_in" <?php selected( $appearance['animation'], 'zoom_in' ); ?>><?php _e( 'Zoom In', 'woo-offers' ); ?></option>
                    <option value="bounce" <?php selected( $appearance['animation'], 'bounce' ); ?>><?php _e( 'Bounce', 'woo-offers' ); ?></option>
                </select>
                <p class="description">
                    <?php _e( 'Add motion to your offer appearance. Subtle animations like "Fade In" work well for most cases. "Bounce" and "Zoom In" are more attention-grabbing but use sparingly. Choose "None" for better performance on mobile devices.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
        
        <!-- Shadow Settings -->
        <tr>
            <th scope="row">
                <label for="shadow"><?php _e( 'Shadow Effect', 'woo-offers' ); ?></label>
            </th>
            <td>
                <select name="appearance[shadow]" id="shadow" class="regular-text">
                    <option value="none" <?php selected( $appearance['shadow'], 'none' ); ?>><?php _e( 'None', 'woo-offers' ); ?></option>
                    <option value="light" <?php selected( $appearance['shadow'], 'light' ); ?>><?php _e( 'Light Shadow', 'woo-offers' ); ?></option>
                    <option value="medium" <?php selected( $appearance['shadow'], 'medium' ); ?>><?php _e( 'Medium Shadow', 'woo-offers' ); ?></option>
                    <option value="heavy" <?php selected( $appearance['shadow'], 'heavy' ); ?>><?php _e( 'Heavy Shadow', 'woo-offers' ); ?></option>
                </select>
                <p class="description">
                    <?php _e( 'Shadow effects add visual depth and help the offer stand out from the page content. Light shadows are subtle and professional, medium shadows provide good separation, and heavy shadows create dramatic emphasis. Use shadows sparingly for best visual impact.', 'woo-offers' ); ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>

<!-- Preview Section -->
<div class="appearance-preview">
    <h4><?php _e( 'Live Preview', 'woo-offers' ); ?></h4>
    <div class="preview-container">
        <div class="offer-preview" id="offer-preview">
            <div class="offer-content">
                <h5><?php _e( 'Special Offer!', 'woo-offers' ); ?></h5>
                <p><?php _e( 'This is how your offer will appear to customers.', 'woo-offers' ); ?></p>
                <button class="offer-button"><?php _e( 'Claim Offer', 'woo-offers' ); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Live preview functionality
    function updatePreview() {
        var $preview = $('#offer-preview');
        var $content = $preview.find('.offer-content');
        var $button = $preview.find('.offer-button');
        
        // Get current values
        var backgroundColor = $('input[name="appearance[background_color]"]').val();
        var textColor = $('input[name="appearance[text_color]"]').val();
        var accentColor = $('input[name="appearance[accent_color]"]').val();
        var borderStyle = $('select[name="appearance[border_style]"]').val();
        var borderWidth = $('input[name="appearance[border_width]"]').val() + 'px';
        var borderColor = $('input[name="appearance[border_color]"]').val();
        var borderRadius = $('input[name="appearance[border_radius]"]').val() + 'px';
        var shadow = $('select[name="appearance[shadow]"]').val();
        
        // Apply styles to preview
        var styles = {
            'background-color': backgroundColor,
            'color': textColor,
            'border-style': borderStyle,
            'border-width': borderWidth,
            'border-color': borderColor,
            'border-radius': borderRadius
        };
        
        // Shadow effects
        switch(shadow) {
            case 'light':
                styles['box-shadow'] = '0 1px 3px rgba(0,0,0,0.1)';
                break;
            case 'medium':
                styles['box-shadow'] = '0 4px 6px rgba(0,0,0,0.1)';
                break;
            case 'heavy':
                styles['box-shadow'] = '0 10px 25px rgba(0,0,0,0.2)';
                break;
            default:
                styles['box-shadow'] = 'none';
        }
        
        $preview.css(styles);
        $button.css({
            'background-color': accentColor,
            'border-color': accentColor
        });
    }
    
    // Color picker synchronization
    $('.color-picker').on('change', function() {
        var $this = $(this);
        var $textInput = $this.siblings('.color-text-input');
        $textInput.val($this.val());
        updatePreview();
    });
    
    $('.color-text-input').on('change keyup', function() {
        var $this = $(this);
        var $colorPicker = $this.siblings('.color-picker');
        var color = $this.val();
        
        // Validate hex color
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            $colorPicker.val(color);
            // Update the corresponding hidden field
            var hiddenFieldName = $colorPicker.attr('name');
            $colorPicker.attr('name', hiddenFieldName);
        }
        updatePreview();
    });
    
    // Update preview when any appearance setting changes
    $('input[name^="appearance"], select[name^="appearance"]').on('change keyup', function() {
        updatePreview();
    });
    
    // Initial preview update
    updatePreview();
    
    // Border style handling
    $('select[name="appearance[border_style]"]').on('change', function() {
        var borderControls = $('.border-control-group').not(':first');
        if ($(this).val() === 'none') {
            borderControls.hide();
        } else {
            borderControls.show();
        }
        updatePreview();
    }).trigger('change');

    // Tooltip functionality
    initTooltips();
});

function initTooltips() {
    // Create tooltip container
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