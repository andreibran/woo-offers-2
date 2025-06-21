<?php
/**
 * Products Selection Metabox Template
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
$selected_products = $offer_data['products'] ?? [];
?>

<div class="woo-offers-products-metabox">
    
    <!-- Product Search Section -->
    <div class="product-search-section">
        <h4><?php _e( 'Search Products', 'woo-offers' ); ?></h4>
        
        <!-- WooCommerce Native Product Search -->
        <div class="woocommerce-product-search">
            <label for="woocommerce-product-search"><?php _e( 'WooCommerce Product Search:', 'woo-offers' ); ?></label>
            <select class="wc-product-search" multiple="multiple" style="width: 100%;" 
                    id="woocommerce-product-search" 
                    name="woocommerce_product_search[]" 
                    data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" 
                    data-action="woocommerce_json_search_products_and_variations">
            </select>
            <button type="button" id="add-wc-products" class="button button-primary" style="margin-top: 5px;">
                <?php _e( 'Add Selected Products', 'woo-offers' ); ?>
            </button>
            <p class="description">
                <?php _e( 'Use WooCommerce native product search. Select products and click "Add Selected Products" to include them in your offer.', 'woo-offers' ); ?>
            </p>
        </div>
        
        <hr style="margin: 20px 0;" />
        
        <!-- Custom Product Search (Alternative) -->
        <div class="custom-product-search">
            <label for="product-search"><?php _e( 'Alternative Search:', 'woo-offers' ); ?></label>
            <div class="product-search-wrapper">
                <input type="text" 
                       id="product-search" 
                       class="regular-text" 
                       placeholder="<?php esc_attr_e( 'Search for products...', 'woo-offers' ); ?>" 
                       autocomplete="off" />
                <span class="search-spinner spinner"></span>
            </div>
            <div id="product-search-results" class="product-search-results"></div>
            <p class="description">
                <?php _e( 'Alternative search method. Type at least 2 characters to see results. Products can be simple, variable, grouped, or downloadable.', 'woo-offers' ); ?>
            </p>
        </div>
    </div>

    <!-- Selected Products Section -->
    <div class="selected-products-section">
        <h4><?php _e( 'Selected Products', 'woo-offers' ); ?></h4>
        <div id="selected-products-list" class="selected-products-list">
            <?php if ( empty( $selected_products ) ): ?>
                <div class="no-products-message">
                    <p><?php _e( 'No products selected yet. Use the search above to add products to this offer.', 'woo-offers' ); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ( $selected_products as $product_data ): ?>
                    <?php
                    $product = \wc_get_product( $product_data['id'] );
                    if ( ! $product ) continue;
                    ?>
                    <div class="selected-product-item" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
                        <div class="product-details">
                            <div class="product-image">
                                <?php echo $product->get_image( 'thumbnail' ); ?>
                            </div>
                            <div class="product-info">
                                <h5 class="product-title"><?php echo esc_html( $product->get_name() ); ?></h5>
                                <div class="product-meta">
                                    <span class="product-sku"><?php printf( __( 'SKU: %s', 'woo-offers' ), $product->get_sku() ?: __( 'N/A', 'woo-offers' ) ); ?></span>
                                    <span class="product-price"><?php echo esc_html( $product->get_price_html() ?: __( 'Price not available', 'woo-offers' ) ); ?></span>
                                    <span class="product-type"><?php echo esc_html( \wc_get_product_type_name( $product->get_type() ) ?: '' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="product-actions">
                            <div class="product-quantity">
                                <label><?php _e( 'Qty:', 'woo-offers' ); ?></label>
                                <input type="number" 
                                       name="selected_products[<?php echo esc_attr( $product->get_id() ); ?>][quantity]" 
                                       value="<?php echo esc_attr( $product_data['quantity'] ?? 1 ); ?>" 
                                       min="1" 
                                       class="small-text quantity-input" />
                            </div>
                            <button type="button" class="button button-small remove-product" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
                                <?php _e( 'Remove', 'woo-offers' ); ?>
                            </button>
                        </div>
                        <input type="hidden" name="selected_products[<?php echo esc_attr( $product->get_id() ); ?>][id]" value="<?php echo esc_attr( $product->get_id() ); ?>" />
                        <input type="hidden" name="selected_products[<?php echo esc_attr( $product->get_id() ); ?>][name]" value="<?php echo esc_attr( $product->get_name() ); ?>" />
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="product-bulk-actions">
        <div class="bulk-actions-wrapper">
            <button type="button" id="select-all-search-results" class="button button-secondary" style="display: none;">
                <?php _e( 'Select All Results', 'woo-offers' ); ?>
            </button>
            <button type="button" id="clear-all-products" class="button button-secondary">
                <?php _e( 'Clear All Products', 'woo-offers' ); ?>
            </button>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    let searchTimeout;
    let searchXhr;
    
    // Initialize WooCommerce Enhanced Select
    if (typeof $.fn.selectWoo !== 'undefined') {
        $('.wc-product-search').selectWoo({
            allowClear: true,
            placeholder: $('.wc-product-search').data('placeholder'),
            minimumInputLength: 3,
            escapeMarkup: function(m) {
                return m;
            }
        });
    }
    
    // Handle WooCommerce product selection
    $('#add-wc-products').on('click', function() {
        const selectedProducts = $('#woocommerce-product-search').val();
        
        if (!selectedProducts || selectedProducts.length === 0) {
            alert('<?php _e( 'Please select at least one product.', 'woo-offers' ); ?>');
            return;
        }
        
        // Get product data for selected products
        selectedProducts.forEach(function(productId) {
            // Check if product is already selected
            if ($('#selected-products-list').find('[data-product-id="' + productId + '"]').length > 0) {
                return; // Skip if already added
            }
            
            // Get product data via AJAX
            $.ajax({
                url: typeof wooOffersAdmin !== 'undefined' ? wooOffersAdmin.ajaxUrl : ajaxurl,
                type: 'POST',
                data: {
                    action: 'woo_offers_search_products',
                    nonce: typeof wooOffersAdmin !== 'undefined' ? wooOffersAdmin.nonce : '',
                    query: productId // Search by ID
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        const product = response.data[0]; // Get first result
                        addProductToSelection(product);
                    }
                }
            });
        });
        
        // Clear the select
        $('#woocommerce-product-search').val(null).trigger('change');
    });
    
    // Product search functionality
    $('#product-search').on('input', function() {
        const query = $(this).val().trim();
        const $results = $('#product-search-results');
        const $spinner = $('.search-spinner');
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Abort previous request
        if (searchXhr) {
            searchXhr.abort();
        }
        
        if (query.length < 2) {
            $results.hide().empty();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            $spinner.addClass('is-active');
            
            searchXhr = $.ajax({
                url: typeof wooOffersAdmin !== 'undefined' ? wooOffersAdmin.ajaxUrl : ajaxurl,
                type: 'POST',
                data: {
                    action: 'woo_offers_search_products',
                    nonce: typeof wooOffersAdmin !== 'undefined' ? wooOffersAdmin.nonce : '',
                    query: query
                },
                success: function(response) {
                    $spinner.removeClass('is-active');
                    
                    if (response.success && response.data.length > 0) {
                        let html = '<div class="search-results-list">';
                        
                        response.data.forEach(function(product) {
                            // Check if product is already selected
                            const isSelected = $('#selected-products-list').find('[data-product-id="' + product.id + '"]').length > 0;
                            const disabledClass = isSelected ? 'disabled' : '';
                            const buttonText = isSelected ? '<?php _e( 'Already Added', 'woo-offers' ); ?>' : '<?php _e( 'Add Product', 'woo-offers' ); ?>';
                            
                            html += '<div class="search-result-item ' + disabledClass + '" data-product-id="' + product.id + '">';
                            html += '<div class="result-image">' + product.image + '</div>';
                            html += '<div class="result-details">';
                            html += '<h6>' + product.name + '</h6>';
                            html += '<div class="result-meta">';
                            html += '<span class="result-sku">SKU: ' + (product.sku || 'N/A') + '</span>';
                            html += '<span class="result-price">' + product.price + '</span>';
                            html += '<span class="result-type">' + product.type + '</span>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="result-actions">';
                            html += '<button type="button" class="button button-small add-product" ' + (isSelected ? 'disabled' : '') + ' data-product="' + encodeURIComponent(JSON.stringify(product)) + '">';
                            html += buttonText;
                            html += '</button>';
                            html += '</div>';
                            html += '</div>';
                        });
                        
                        html += '</div>';
                        $results.html(html).show();
                        $('#select-all-search-results').show();
                    } else {
                        $results.html('<div class="no-results"><?php _e( 'No products found.', 'woo-offers' ); ?></div>').show();
                        $('#select-all-search-results').hide();
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.statusText !== 'abort') {
                        $spinner.removeClass('is-active');
                        $results.html('<div class="search-error"><?php _e( 'Error searching products. Please try again.', 'woo-offers' ); ?><br><small style="color: #666;">Debug: ' + status + ' - ' + error + '</small></div>').show();
                    }
                }
            });
        }, 300);
    });
    
    // Add product to selection
    $(document).on('click', '.add-product', function() {
        const $button = $(this);
        if ($button.prop('disabled')) return;
        
        const productData = JSON.parse(decodeURIComponent($button.data('product')));
        addProductToSelection(productData);
        
        // Update button state
        $button.prop('disabled', true).text('<?php _e( 'Already Added', 'woo-offers' ); ?>');
        $button.closest('.search-result-item').addClass('disabled');
    });
    
    // Remove product from selection
    $(document).on('click', '.remove-product', function() {
        const productId = $(this).data('product-id');
        const $productItem = $(this).closest('.selected-product-item');
        
        // Remove from DOM
        $productItem.remove();
        
        // Update search results if visible
        $('.search-result-item[data-product-id="' + productId + '"]')
            .removeClass('disabled')
            .find('.add-product')
            .prop('disabled', false)
            .text('<?php _e( 'Add Product', 'woo-offers' ); ?>');
        
        // Show no products message if all removed
        if ($('#selected-products-list .selected-product-item').length === 0) {
            $('#selected-products-list').html('<div class="no-products-message"><p><?php _e( 'No products selected yet. Use the search above to add products to this offer.', 'woo-offers' ); ?></p></div>');
        }
    });
    
    // Clear all products
    $('#clear-all-products').on('click', function() {
        if (confirm('<?php _e( 'Are you sure you want to remove all selected products?', 'woo-offers' ); ?>')) {
            $('#selected-products-list').html('<div class="no-products-message"><p><?php _e( 'No products selected yet. Use the search above to add products to this offer.', 'woo-offers' ); ?></p></div>');
            
            // Re-enable all search results
            $('.search-result-item').removeClass('disabled')
                .find('.add-product')
                .prop('disabled', false)
                .text('<?php _e( 'Add Product', 'woo-offers' ); ?>');
        }
    });
    
    // Select all search results
    $('#select-all-search-results').on('click', function() {
        $('.search-result-item:not(.disabled) .add-product').each(function() {
            $(this).click();
        });
    });
    
    // Update quantity
    $(document).on('change', '.quantity-input', function() {
        const quantity = parseInt($(this).val());
        if (quantity < 1) {
            $(this).val(1);
        }
    });
    
    // Function to add product to selection
    function addProductToSelection(product) {
        // Remove no products message
        $('.no-products-message').remove();
        
        const html = 
            '<div class="selected-product-item" data-product-id="' + product.id + '">' +
                '<div class="product-details">' +
                    '<div class="product-image">' + product.image + '</div>' +
                    '<div class="product-info">' +
                        '<h5 class="product-title">' + product.name + '</h5>' +
                        '<div class="product-meta">' +
                            '<span class="product-sku">SKU: ' + (product.sku || 'N/A') + '</span>' +
                            '<span class="product-price">' + product.price + '</span>' +
                            '<span class="product-type">' + product.type + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="product-actions">' +
                    '<div class="product-quantity">' +
                        '<label><?php _e( 'Qty:', 'woo-offers' ); ?></label>' +
                        '<input type="number" name="selected_products[' + product.id + '][quantity]" value="1" min="1" class="small-text quantity-input" />' +
                    '</div>' +
                    '<button type="button" class="button button-small remove-product" data-product-id="' + product.id + '"><?php _e( 'Remove', 'woo-offers' ); ?></button>' +
                '</div>' +
                '<input type="hidden" name="selected_products[' + product.id + '][id]" value="' + product.id + '" />' +
                '<input type="hidden" name="selected_products[' + product.id + '][name]" value="' + product.name + '" />' +
            '</div>';
        
        $('#selected-products-list').append(html);
    }
    
    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.product-search-wrapper, #product-search-results').length) {
            $('#product-search-results').hide();
            $('#select-all-search-results').hide();
        }
    });
});
</script> 