<?php
/**
 * Appearance Metabox Template
 * Modern UI for customizing offer appearance
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get saved appearance data
$appearance_data = $offer_data['appearance'] ?? array();
?>

<div class="woo-offers-appearance-metabox">
    
    <!-- Offer Box Style -->
    <div class="appearance-section">
        <h4><?php _e( 'Offer Box Style', 'woo-offers' ); ?></h4>
        
        <div class="style-options">
            <div class="style-option-grid">
                <?php
                $box_styles = array(
                    'modern' => array(
                        'label' => __( 'Modern', 'woo-offers' ),
                        'description' => __( 'Clean, contemporary design with rounded corners', 'woo-offers' ),
                        'preview' => '🎨'
                    ),
                    'classic' => array(
                        'label' => __( 'Classic', 'woo-offers' ),
                        'description' => __( 'Traditional design with sharp edges', 'woo-offers' ),
                        'preview' => '📋'
                    ),
                    'gradient' => array(
                        'label' => __( 'Gradient', 'woo-offers' ),
                        'description' => __( 'Eye-catching gradient background', 'woo-offers' ),
                        'preview' => '🌈'
                    ),
                    'minimal' => array(
                        'label' => __( 'Minimal', 'woo-offers' ),
                        'description' => __( 'Simple, distraction-free design', 'woo-offers' ),
                        'preview' => '⚪'
                    )
                );
                
                foreach ( $box_styles as $style_key => $style ) :
                    $checked = ( $appearance_data['box_style'] ?? 'modern' ) === $style_key;
                ?>
                <label class="style-option <?php echo $checked ? 'selected' : ''; ?>">
                    <input type="radio" 
                           name="appearance[box_style]" 
                           value="<?php echo esc_attr( $style_key ); ?>"
                           <?php checked( $checked ); ?>>
                    <div class="style-preview">
                        <span class="style-emoji"><?php echo $style['preview']; ?></span>
                        <strong><?php echo esc_html( $style['label'] ); ?></strong>
                        <small><?php echo esc_html( $style['description'] ); ?></small>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Color Scheme -->
    <div class="appearance-section">
        <h4><?php _e( 'Color Scheme', 'woo-offers' ); ?></h4>
        
        <div class="color-scheme-options">
            <?php
            $color_schemes = array(
                'blue' => array(
                    'label' => __( 'Blue Ocean', 'woo-offers' ),
                    'primary' => '#2271b1',
                    'secondary' => '#135e96',
                    'accent' => '#f0f6fc'
                ),
                'green' => array(
                    'label' => __( 'Fresh Green', 'woo-offers' ),
                    'primary' => '#46b450',
                    'secondary' => '#00a32a',
                    'accent' => '#f0fff4'
                ),
                'purple' => array(
                    'label' => __( 'Royal Purple', 'woo-offers' ),
                    'primary' => '#667eea',
                    'secondary' => '#764ba2',
                    'accent' => '#f0f4ff'
                ),
                'orange' => array(
                    'label' => __( 'Sunset Orange', 'woo-offers' ),
                    'primary' => '#ff8c42',
                    'secondary' => '#ff6b1a',
                    'accent' => '#fff8f0'
                ),
                'red' => array(
                    'label' => __( 'Bold Red', 'woo-offers' ),
                    'primary' => '#e53e3e',
                    'secondary' => '#c53030',
                    'accent' => '#fff5f5'
                ),
                'dark' => array(
                    'label' => __( 'Dark Mode', 'woo-offers' ),
                    'primary' => '#2d3748',
                    'secondary' => '#1a202c',
                    'accent' => '#edf2f7'
                )
            );
            
            foreach ( $color_schemes as $scheme_key => $scheme ) :
                $checked = ( $appearance_data['color_scheme'] ?? 'blue' ) === $scheme_key;
            ?>
            <label class="color-scheme-option <?php echo $checked ? 'selected' : ''; ?>">
                <input type="radio" 
                       name="appearance[color_scheme]" 
                       value="<?php echo esc_attr( $scheme_key ); ?>"
                       <?php checked( $checked ); ?>>
                <div class="color-preview">
                    <div class="color-swatches">
                        <span class="color-swatch primary" style="background-color: <?php echo esc_attr( $scheme['primary'] ); ?>"></span>
                        <span class="color-swatch secondary" style="background-color: <?php echo esc_attr( $scheme['secondary'] ); ?>"></span>
                        <span class="color-swatch accent" style="background-color: <?php echo esc_attr( $scheme['accent'] ); ?>"></span>
                    </div>
                    <span class="color-label"><?php echo esc_html( $scheme['label'] ); ?></span>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Position Settings -->
    <div class="appearance-section">
        <h4><?php _e( 'Display Position', 'woo-offers' ); ?></h4>
        
        <div class="position-options">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="appearance_position"><?php _e( 'Position', 'woo-offers' ); ?></label>
                    </th>
                    <td>
                        <select name="appearance[position]" id="appearance_position" class="regular-text">
                            <option value="before_add_to_cart" <?php selected( $appearance_data['position'] ?? 'before_add_to_cart', 'before_add_to_cart' ); ?>>
                                <?php _e( 'Before Add to Cart Button', 'woo-offers' ); ?>
                            </option>
                            <option value="after_add_to_cart" <?php selected( $appearance_data['position'] ?? '', 'after_add_to_cart' ); ?>>
                                <?php _e( 'After Add to Cart Button', 'woo-offers' ); ?>
                            </option>
                            <option value="product_summary" <?php selected( $appearance_data['position'] ?? '', 'product_summary' ); ?>>
                                <?php _e( 'In Product Summary', 'woo-offers' ); ?>
                            </option>
                            <option value="product_tabs" <?php selected( $appearance_data['position'] ?? '', 'product_tabs' ); ?>>
                                <?php _e( 'In Product Tabs', 'woo-offers' ); ?>
                            </option>
                            <option value="cart" <?php selected( $appearance_data['position'] ?? '', 'cart' ); ?>>
                                <?php _e( 'Cart Page', 'woo-offers' ); ?>
                            </option>
                            <option value="checkout" <?php selected( $appearance_data['position'] ?? '', 'checkout' ); ?>>
                                <?php _e( 'Checkout Page', 'woo-offers' ); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e( 'Choose where the offer should be displayed on the frontend.', 'woo-offers' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Animation Settings -->
    <div class="appearance-section">
        <h4><?php _e( 'Animation & Effects', 'woo-offers' ); ?></h4>
        
        <div class="animation-options">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="appearance_animation"><?php _e( 'Entrance Animation', 'woo-offers' ); ?></label>
                    </th>
                    <td>
                        <select name="appearance[animation]" id="appearance_animation" class="regular-text">
                            <option value="none" <?php selected( $appearance_data['animation'] ?? 'slideIn', 'none' ); ?>>
                                <?php _e( 'None', 'woo-offers' ); ?>
                            </option>
                            <option value="slideIn" <?php selected( $appearance_data['animation'] ?? 'slideIn', 'slideIn' ); ?>>
                                <?php _e( 'Slide In', 'woo-offers' ); ?>
                            </option>
                            <option value="fadeIn" <?php selected( $appearance_data['animation'] ?? '', 'fadeIn' ); ?>>
                                <?php _e( 'Fade In', 'woo-offers' ); ?>
                            </option>
                            <option value="bounceIn" <?php selected( $appearance_data['animation'] ?? '', 'bounceIn' ); ?>>
                                <?php _e( 'Bounce In', 'woo-offers' ); ?>
                            </option>
                            <option value="zoomIn" <?php selected( $appearance_data['animation'] ?? '', 'zoomIn' ); ?>>
                                <?php _e( 'Zoom In', 'woo-offers' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Visual Effects', 'woo-offers' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" 
                                       name="appearance[show_countdown]" 
                                       value="1"
                                       <?php checked( $appearance_data['show_countdown'] ?? false ); ?>>
                                <?php _e( 'Show countdown timer', 'woo-offers' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" 
                                       name="appearance[show_savings]" 
                                       value="1"
                                       <?php checked( $appearance_data['show_savings'] ?? true ); ?>>
                                <?php _e( 'Display savings amount', 'woo-offers' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" 
                                       name="appearance[show_badge]" 
                                       value="1"
                                       <?php checked( $appearance_data['show_badge'] ?? true ); ?>>
                                <?php _e( 'Show offer badge/label', 'woo-offers' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" 
                                       name="appearance[pulse_effect]" 
                                       value="1"
                                       <?php checked( $appearance_data['pulse_effect'] ?? false ); ?>>
                                <?php _e( 'Add pulsing effect to grab attention', 'woo-offers' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Custom CSS -->
    <div class="appearance-section">
        <h4><?php _e( 'Custom Styling', 'woo-offers' ); ?></h4>
        
        <div class="custom-css-section">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="appearance_custom_css"><?php _e( 'Custom CSS', 'woo-offers' ); ?></label>
                    </th>
                    <td>
                        <textarea name="appearance[custom_css]" 
                                  id="appearance_custom_css" 
                                  rows="8" 
                                  cols="50" 
                                  class="large-text code"
                                  placeholder="/* Add your custom CSS here */&#10;.woo-offers-box {&#10;    /* Your styles */&#10;}"><?php echo esc_textarea( $appearance_data['custom_css'] ?? '' ); ?></textarea>
                        <p class="description">
                            <?php _e( 'Add custom CSS to further customize the appearance. Use the class .woo-offers-box to target the offer container.', 'woo-offers' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Live Preview -->
    <div class="appearance-section">
        <h4><?php _e( 'Live Preview', 'woo-offers' ); ?></h4>
        
        <div class="preview-section">
            <div id="offer-preview-container" class="offer-preview-container">
                <div class="offer-preview-box" id="offer-preview-box">
                    <div class="offer-content">
                        <span class="offer-badge">🎉 <?php _e( 'Special Offer', 'woo-offers' ); ?></span>
                        <h3 class="offer-title"><?php _e( 'Sample Offer Title', 'woo-offers' ); ?></h3>
                        <p class="offer-description"><?php _e( 'Get 20% off your purchase today!', 'woo-offers' ); ?></p>
                        <div class="offer-savings">
                            <?php _e( 'You save:', 'woo-offers' ); ?> <strong>$15.00</strong>
                        </div>
                        <button class="offer-button"><?php _e( 'Claim Offer', 'woo-offers' ); ?></button>
                    </div>
                </div>
            </div>
            <p class="description">
                <?php _e( 'This preview updates automatically as you change the appearance settings above.', 'woo-offers' ); ?>
            </p>
        </div>
    </div>

</div>

<style>
/* Appearance Metabox Styles */
.woo-offers-appearance-metabox .appearance-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.woo-offers-appearance-metabox .appearance-section:last-child {
    border-bottom: none;
}

.woo-offers-appearance-metabox h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Style Options Grid */
.style-option-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.style-option {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
    display: block;
    text-align: center;
}

.style-option:hover {
    border-color: #667eea;
    background: #f0f4ff;
    transform: translateY(-2px);
}

.style-option.selected {
    border-color: #667eea;
    background: #f0f4ff;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
}

.style-option input[type="radio"] {
    display: none;
}

.style-preview .style-emoji {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
}

.style-preview strong {
    display: block;
    margin-bottom: 4px;
    color: #1d2327;
}

.style-preview small {
    color: #646970;
    font-size: 12px;
    line-height: 1.4;
}

/* Color Scheme Options */
.color-scheme-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
}

.color-scheme-option {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
    display: block;
    text-align: center;
}

.color-scheme-option:hover {
    border-color: #667eea;
    transform: translateY(-2px);
}

.color-scheme-option.selected {
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
}

.color-scheme-option input[type="radio"] {
    display: none;
}

.color-swatches {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-bottom: 8px;
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
}

.color-label {
    font-size: 11px;
    color: #646970;
    font-weight: 500;
}

/* Live Preview */
.offer-preview-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
    border-radius: 8px;
    margin-top: 10px;
}

.offer-preview-box {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-width: 300px;
    margin: 0 auto;
    transition: all 0.3s ease;
}

.offer-content .offer-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 10px;
}

.offer-content .offer-title {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
}

.offer-content .offer-description {
    margin: 0 0 12px 0;
    color: #646970;
    font-size: 14px;
}

.offer-content .offer-savings {
    margin: 0 0 15px 0;
    font-size: 14px;
    color: #46b450;
}

.offer-content .offer-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.offer-content .offer-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .style-option-grid {
        grid-template-columns: 1fr;
    }
    
    .color-scheme-options {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Live preview updates
    function updatePreview() {
        const $preview = $('#offer-preview-box');
        const $container = $('#offer-preview-container');
        
        // Get selected values
        const boxStyle = $('input[name="appearance[box_style]"]:checked').val() || 'modern';
        const colorScheme = $('input[name="appearance[color_scheme]"]:checked').val() || 'blue';
        const animation = $('#appearance_animation').val() || 'slideIn';
        const showCountdown = $('input[name="appearance[show_countdown]"]').is(':checked');
        const showSavings = $('input[name="appearance[show_savings]"]').is(':checked');
        const showBadge = $('input[name="appearance[show_badge]"]').is(':checked');
        const pulseEffect = $('input[name="appearance[pulse_effect]"]').is(':checked');
        
        // Color schemes
        const colorSchemes = {
            blue: { primary: '#2271b1', secondary: '#135e96', accent: '#f0f6fc' },
            green: { primary: '#46b450', secondary: '#00a32a', accent: '#f0fff4' },
            purple: { primary: '#667eea', secondary: '#764ba2', accent: '#f0f4ff' },
            orange: { primary: '#ff8c42', secondary: '#ff6b1a', accent: '#fff8f0' },
            red: { primary: '#e53e3e', secondary: '#c53030', accent: '#fff5f5' },
            dark: { primary: '#2d3748', secondary: '#1a202c', accent: '#edf2f7' }
        };
        
        const colors = colorSchemes[colorScheme];
        
        // Apply box style
        $preview.removeClass('style-modern style-classic style-gradient style-minimal');
        $preview.addClass('style-' + boxStyle);
        
        // Apply colors
        const $badge = $preview.find('.offer-badge');
        const $button = $preview.find('.offer-button');
        
        $badge.css('background', `linear-gradient(135deg, ${colors.primary} 0%, ${colors.secondary} 100%)`);
        $button.css('background', `linear-gradient(135deg, ${colors.primary} 0%, ${colors.secondary} 100%)`);
        
        if (boxStyle === 'gradient') {
            $preview.css('background', `linear-gradient(135deg, ${colors.accent} 0%, #fff 100%)`);
        } else {
            $preview.css('background', '#fff');
        }
        
        // Show/hide elements
        $preview.find('.offer-badge').toggle(showBadge);
        $preview.find('.offer-savings').toggle(showSavings);
        
        // Pulse effect
        if (pulseEffect) {
            $preview.addClass('pulse-effect');
        } else {
            $preview.removeClass('pulse-effect');
        }
        
        // Animation preview
        $preview.removeClass('animate-slideIn animate-fadeIn animate-bounceIn animate-zoomIn');
        if (animation !== 'none') {
            setTimeout(() => {
                $preview.addClass('animate-' + animation);
            }, 100);
        }
    }
    
    // Update preview on any change
    $('input[name^="appearance"], select[name^="appearance"]').on('change', updatePreview);
    
    // Initial preview
    updatePreview();
    
    // Style option selection
    $('.style-option').on('click', function() {
        $('.style-option').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        updatePreview();
    });
    
    // Color scheme selection
    $('.color-scheme-option').on('click', function() {
        $('.color-scheme-option').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        updatePreview();
    });
});
</script>

<style>
/* Additional animation styles */
.pulse-effect {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.animate-slideIn {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate-bounceIn {
    animation: bounceIn 0.6s ease-out;
}

@keyframes bounceIn {
    0% { opacity: 0; transform: scale(0.3); }
    50% { opacity: 1; transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { opacity: 1; transform: scale(1); }
}

.animate-zoomIn {
    animation: zoomIn 0.5s ease-out;
}

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.5); }
    to { opacity: 1; transform: scale(1); }
}

/* Box style variations */
.style-classic {
    border-radius: 0 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.style-modern {
    border-radius: 12px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
}

.style-minimal {
    border-radius: 4px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    border: 1px solid #e0e0e0 !important;
}
</style> 