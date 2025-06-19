<?php
/**
 * Media Metabox Template
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
$featured_image_id = $offer_data['featured_image_id'] ?? 0;
$gallery_images = $offer_data['gallery_images'] ?? [];

// Get featured image data
$featured_image_url = '';
$featured_image_alt = '';
if ( $featured_image_id ) {
    $featured_image_url = wp_get_attachment_image_src( $featured_image_id, 'medium' );
    $featured_image_url = $featured_image_url ? $featured_image_url[0] : '';
    $featured_image_alt = get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true );
}
?>

<div class="woo-offers-media-metabox">
    
    <!-- Featured Image Section -->
    <div class="featured-image-section">
        <h4><?php _e( 'Featured Image', 'woo-offers' ); ?></h4>
        
        <div class="featured-image-container">
            <div class="featured-image-preview" <?php echo $featured_image_id ? '' : 'style="display: none;"'; ?>>
                <div class="image-preview">
                    <img src="<?php echo esc_url( $featured_image_url ); ?>" 
                         alt="<?php echo esc_attr( $featured_image_alt ); ?>" 
                         id="featured-image-preview" />
                </div>
                <div class="image-actions">
                    <button type="button" class="button button-secondary" id="change-featured-image">
                        <?php _e( 'Change Image', 'woo-offers' ); ?>
                    </button>
                    <button type="button" class="button-link-delete" id="remove-featured-image">
                        <?php _e( 'Remove Image', 'woo-offers' ); ?>
                    </button>
                </div>
            </div>
            
            <div class="featured-image-placeholder" <?php echo $featured_image_id ? 'style="display: none;"' : ''; ?>>
                <div class="upload-placeholder">
                    <div class="dashicons dashicons-format-image"></div>
                    <p><?php _e( 'No featured image selected', 'woo-offers' ); ?></p>
                    <button type="button" class="button button-primary" id="set-featured-image">
                        <?php _e( 'Set Featured Image', 'woo-offers' ); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="featured_image_id" id="featured-image-id" value="<?php echo esc_attr( $featured_image_id ); ?>" />
        
        <p class="description">
            <?php _e( 'Select a featured image that represents your offer visually. This image will be prominently displayed with the offer. For best results, use high-quality images with dimensions of 600x400 pixels or larger. Avoid text-heavy images as they may not scale well on mobile devices.', 'woo-offers' ); ?>
        </p>
    </div>

    <!-- Gallery Images Section -->
    <div class="gallery-images-section">
        <h4><?php _e( 'Gallery Images', 'woo-offers' ); ?></h4>
        
        <div class="gallery-container">
            <div id="gallery-images-list" class="gallery-images-list">
                <?php if ( ! empty( $gallery_images ) ): ?>
                    <?php foreach ( $gallery_images as $image_id ): ?>
                        <?php
                        $image_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                        $image_url = $image_url ? $image_url[0] : '';
                        $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
                        ?>
                        <div class="gallery-image-item" data-image-id="<?php echo esc_attr( $image_id ); ?>">
                            <div class="image-preview">
                                <img src="<?php echo esc_url( $image_url ); ?>" 
                                     alt="<?php echo esc_attr( $image_alt ); ?>" />
                            </div>
                            <div class="image-overlay">
                                <button type="button" class="remove-gallery-image" data-image-id="<?php echo esc_attr( $image_id ); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                            </div>
                            <input type="hidden" name="gallery_images[]" value="<?php echo esc_attr( $image_id ); ?>" />
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="add-gallery-image">
                    <button type="button" id="add-gallery-images" class="button button-secondary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e( 'Add Images', 'woo-offers' ); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <p class="description">
            <?php _e( 'Create an image gallery to showcase multiple aspects of your offer. Gallery images are useful for showing product variations, different angles, or step-by-step processes. Drag and drop images to reorder them. Keep galleries focused with 3-6 high-quality images for best user experience.', 'woo-offers' ); ?>
        </p>
    </div>

    <!-- Offer Preview Section -->
    <div class="offer-preview-section">
        <h4><?php _e( 'Offer Preview', 'woo-offers' ); ?></h4>
        
        <div class="preview-controls">
            <button type="button" id="preview-offer" class="button button-secondary">
                <span class="dashicons dashicons-visibility"></span>
                <?php _e( 'Preview Offer', 'woo-offers' ); ?>
            </button>
            <button type="button" id="preview-offer-modal" class="button button-secondary">
                <span class="dashicons dashicons-admin-page"></span>
                <?php _e( 'Preview in Modal', 'woo-offers' ); ?>
            </button>
        </div>
        
        <p class="description">
            <?php _e( 'Test your offer before publishing to ensure it looks and functions correctly. Use "Preview Offer" to see it in context on a product page, or "Preview in Modal" for a quick popup preview. Preview helps identify design issues and test user experience across different screen sizes.', 'woo-offers' ); ?>
        </p>
    </div>

</div>

<!-- Preview Modal -->
<div id="offer-preview-modal" class="woo-offers-modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e( 'Offer Preview', 'woo-offers' ); ?></h3>
            <button type="button" class="modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="modal-body">
            <div id="offer-preview-content">
                <!-- Preview content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let mediaUploader;
    let galleryUploader;
    
    // Initialize sortable for gallery images
    $('#gallery-images-list').sortable({
        items: '.gallery-image-item',
        cursor: 'move',
        placeholder: 'gallery-image-placeholder',
        tolerance: 'pointer',
        update: function(event, ui) {
            // Handle reordering if needed
        }
    });
    
    // Set Featured Image
    $('#set-featured-image, #change-featured-image').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: '<?php _e( 'Choose Featured Image', 'woo-offers' ); ?>',
            button: {
                text: '<?php _e( 'Set Featured Image', 'woo-offers' ); ?>'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            
            $('#featured-image-id').val(attachment.id);
            $('#featured-image-preview').attr('src', attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url);
            $('#featured-image-preview').attr('alt', attachment.alt || '');
            
            $('.featured-image-placeholder').hide();
            $('.featured-image-preview').show();
        });
        
        mediaUploader.open();
    });
    
    // Remove Featured Image
    $('#remove-featured-image').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('<?php _e( 'Are you sure you want to remove the featured image?', 'woo-offers' ); ?>')) {
            $('#featured-image-id').val('');
            $('#featured-image-preview').attr('src', '').attr('alt', '');
            
            $('.featured-image-preview').hide();
            $('.featured-image-placeholder').show();
        }
    });
    
    // Add Gallery Images
    $('#add-gallery-images').on('click', function(e) {
        e.preventDefault();
        
        if (galleryUploader) {
            galleryUploader.open();
            return;
        }
        
        galleryUploader = wp.media({
            title: '<?php _e( 'Add Gallery Images', 'woo-offers' ); ?>',
            button: {
                text: '<?php _e( 'Add to Gallery', 'woo-offers' ); ?>'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });
        
        galleryUploader.on('select', function() {
            const attachments = galleryUploader.state().get('selection').toJSON();
            
            attachments.forEach(function(attachment) {
                // Check if image is already in gallery
                if ($('#gallery-images-list').find('[data-image-id="' + attachment.id + '"]').length > 0) {
                    return;
                }
                
                const imageHtml = 
                    '<div class="gallery-image-item" data-image-id="' + attachment.id + '">' +
                        '<div class="image-preview">' +
                            '<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" alt="' + (attachment.alt || '') + '" />' +
                        '</div>' +
                        '<div class="image-overlay">' +
                            '<button type="button" class="remove-gallery-image" data-image-id="' + attachment.id + '">' +
                                '<span class="dashicons dashicons-no-alt"></span>' +
                            '</button>' +
                        '</div>' +
                        '<input type="hidden" name="gallery_images[]" value="' + attachment.id + '" />' +
                    '</div>';
                
                $('.add-gallery-image').before(imageHtml);
            });
        });
        
        galleryUploader.open();
    });
    
    // Remove Gallery Image
    $(document).on('click', '.remove-gallery-image', function(e) {
        e.preventDefault();
        
        if (confirm('<?php _e( 'Are you sure you want to remove this image from the gallery?', 'woo-offers' ); ?>')) {
            $(this).closest('.gallery-image-item').remove();
        }
    });
    
    // Preview Offer
    $('#preview-offer').on('click', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData($('#offer-form')[0]);
        formData.append('action', 'woo_offers_preview_offer');
        formData.append('nonce', wooOffersAdmin.nonce);
        
        // Open preview in new tab
        $.ajax({
            url: wooOffersAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.data.preview_url) {
                    window.open(response.data.preview_url, '_blank');
                } else {
                    alert('<?php _e( 'Error generating preview. Please try again.', 'woo-offers' ); ?>');
                }
            },
            error: function() {
                alert('<?php _e( 'Error generating preview. Please try again.', 'woo-offers' ); ?>');
            }
        });
    });
    
    // Preview Offer in Modal
    $('#preview-offer-modal').on('click', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData($('#offer-form')[0]);
        formData.append('action', 'woo_offers_preview_offer_modal');
        formData.append('nonce', wooOffersAdmin.nonce);
        
        // Show modal with loading
        $('#offer-preview-content').html('<div class="preview-loading"><span class="spinner is-active"></span><p><?php _e( 'Generating preview...', 'woo-offers' ); ?></p></div>');
        $('#offer-preview-modal').show();
        
        $.ajax({
            url: wooOffersAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.data.html) {
                    $('#offer-preview-content').html(response.data.html);
                } else {
                    $('#offer-preview-content').html('<div class="preview-error"><p><?php _e( 'Error generating preview. Please try again.', 'woo-offers' ); ?></p></div>');
                }
            },
            error: function() {
                $('#offer-preview-content').html('<div class="preview-error"><p><?php _e( 'Error generating preview. Please try again.', 'woo-offers' ); ?></p></div>');
            }
        });
    });
    
    // Close Modal
    $('.modal-close, .modal-backdrop').on('click', function(e) {
        e.preventDefault();
        $('#offer-preview-modal').hide();
    });
    
    // Escape key to close modal
    $(document).on('keyup', function(e) {
        if (e.keyCode === 27 && $('#offer-preview-modal').is(':visible')) {
            $('#offer-preview-modal').hide();
        }
    });
});
</script> 