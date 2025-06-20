/**
 * WooOffers Frontend JavaScript
 * Handles offer tracking and user interactions
 * 
 * @package WooOffers
 * @since 2.0.0
 */

(function($) {
    'use strict';

    const WooOffersAnalytics = {
        
        config: {
            ajaxUrl: woo_offers_frontend?.ajax_url || '/wp-admin/admin-ajax.php',
            nonce: woo_offers_frontend?.nonce || '',
            debugMode: woo_offers_frontend?.debug_mode || false,
            trackingEnabled: woo_offers_frontend?.tracking_enabled !== false,
            sessionId: woo_offers_frontend?.session_id || 'session_' + Date.now(),
            userId: woo_offers_frontend?.user_id || 0
        },

        trackedEvents: new Set(),

        init: function() {
            if (!this.config.trackingEnabled) {
                this.log('Analytics tracking disabled');
                return;
            }

            this.log('Initializing WooOffers Analytics...');
            this.bindEvents();
            this.trackOfferViews();
            this.setupConversionTracking();
        },

        bindEvents: function() {
            const self = this;

            // Track offer button clicks
            $(document).on('click', '.woo-offer-button', function(e) {
                const $button = $(this);
                const $offer = $button.closest('.woo-offer');
                const offerId = $button.data('offer-id') || $offer.data('offer-id');
                const offerType = $offer.data('offer-type');
                
                self.trackEvent('offer_click', {
                    offer_id: offerId,
                    offer_type: offerType,
                    button_type: $button[0].className.match(/apply-(\w+)/)?.[1] || 'generic',
                    product_id: self.getCurrentProductId(),
                    timestamp: Date.now()
                });
            });

            // Track offer hover/view interactions
            $(document).on('mouseenter', '.woo-offer', function() {
                const $offer = $(this);
                const offerId = $offer.data('offer-id');
                const offerType = $offer.data('offer-type');
                
                if (!self.trackedEvents.has(`hover_${offerId}`)) {
                    self.trackEvent('offer_hover', {
                        offer_id: offerId,
                        offer_type: offerType,
                        product_id: self.getCurrentProductId(),
                        timestamp: Date.now()
                    });
                    self.trackedEvents.add(`hover_${offerId}`);
                }
            });

            // Track form interactions
            $(document).on('focus change', '.woo-offer input, .woo-offer select', function() {
                const $offer = $(this).closest('.woo-offer');
                const offerId = $offer.data('offer-id');
                
                if (!self.trackedEvents.has(`interact_${offerId}`)) {
                    self.trackEvent('offer_interaction', {
                        offer_id: offerId,
                        interaction_type: 'form_focus',
                        element_type: this.tagName.toLowerCase(),
                        product_id: self.getCurrentProductId(),
                        timestamp: Date.now()
                    });
                    self.trackedEvents.add(`interact_${offerId}`);
                }
            });

            // Track AJAX offer applications
            $(document).on('click', '.apply-percentage-discount, .apply-fixed-discount, .apply-bogo-offer, .apply-bundle-offer, .apply-quantity-discount, .apply-free-shipping', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const offerId = $button.data('offer-id');
                const offerType = $button.closest('.woo-offer').data('offer-type');
                
                self.applyOfferWithTracking(offerId, offerType, $button);
            });
        },

        trackOfferViews: function() {
            const self = this;
            
            // Use simple visibility check
            $('.woo-offer').each(function() {
                const $offer = $(this);
                const offerId = $offer.data('offer-id');
                const offerType = $offer.data('offer-type');
                
                if (!self.trackedEvents.has(`view_${offerId}`)) {
                    self.trackEvent('offer_view', {
                        offer_id: offerId,
                        offer_type: offerType,
                        product_id: self.getCurrentProductId(),
                        timestamp: Date.now()
                    });
                    self.trackedEvents.add(`view_${offerId}`);
                }
            });
        },

        setupConversionTracking: function() {
            const self = this;
            
            // Track when user adds to cart (conversion)
            $(document.body).on('added_to_cart', function(event, fragments, cart_hash, button) {
                const productId = button?.data('product_id') || self.getCurrentProductId();
                
                self.trackEvent('offer_conversion', {
                    product_id: productId,
                    timestamp: Date.now()
                });
            });
        },

        applyOfferWithTracking: function(offerId, offerType, $button) {
            const self = this;
            const startTime = Date.now();
            
            // Show loading state
            $button.prop('disabled', true).addClass('loading');
            const originalText = $button.find('.button-text').text() || $button.text();
            $button.find('.button-text').length > 0 
                ? $button.find('.button-text').text('Applying...') 
                : $button.text('Applying...');

            // Track application attempt
            this.trackEvent('offer_apply_attempt', {
                offer_id: offerId,
                offer_type: offerType,
                product_id: this.getCurrentProductId(),
                timestamp: startTime
            });

            // AJAX request to apply offer
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'woo_offers_apply_offer',
                    offer_id: offerId,
                    product_id: this.getCurrentProductId(),
                    nonce: this.config.nonce
                },
                success: function(response) {
                    const endTime = Date.now();
                    const duration = endTime - startTime;

                    if (response.success) {
                        // Track successful application
                        self.trackEvent('offer_apply_success', {
                            offer_id: offerId,
                            offer_type: offerType,
                            product_id: self.getCurrentProductId(),
                            discount_amount: response.data?.discount_amount || 0,
                            coupon_code: response.data?.coupon_code || '',
                            duration: duration,
                            timestamp: endTime
                        });

                        // Show success message
                        self.showOfferMessage('Offer applied successfully!', 'success');
                        
                        // Redirect to cart if configured
                        if (response.data?.redirect_to_cart) {
                            setTimeout(function() {
                                window.location.href = response.data.cart_url || '/cart';
                            }, 1500);
                        }
                        
                        // Update cart fragments
                        $(document.body).trigger('wc_fragment_refresh');
                        
                    } else {
                        // Track failed application
                        self.trackEvent('offer_apply_failure', {
                            offer_id: offerId,
                            offer_type: offerType,
                            product_id: self.getCurrentProductId(),
                            error_message: response.data?.message || 'Unknown error',
                            duration: duration,
                            timestamp: endTime
                        });

                        // Show error message
                        self.showOfferMessage(response.data?.message || 'Failed to apply offer', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    const endTime = Date.now();
                    const duration = endTime - startTime;

                    // Track AJAX error
                    self.trackEvent('offer_apply_error', {
                        offer_id: offerId,
                        offer_type: offerType,
                        product_id: self.getCurrentProductId(),
                        error_type: 'ajax_error',
                        error_message: error,
                        status_code: xhr.status,
                        duration: duration,
                        timestamp: endTime
                    });

                    self.showOfferMessage('Network error. Please try again.', 'error');
                },
                complete: function() {
                    // Restore button state
                    $button.prop('disabled', false).removeClass('loading');
                    $button.find('.button-text').length > 0 
                        ? $button.find('.button-text').text(originalText) 
                        : $button.text(originalText);
                }
            });
        },

        trackEvent: function(eventType, data) {
            if (!this.config.trackingEnabled) {
                return;
            }

            const eventData = {
                event_type: eventType,
                session_id: this.config.sessionId,
                user_id: this.config.userId,
                url: window.location.href,
                referrer: document.referrer,
                user_agent: navigator.userAgent,
                screen_resolution: screen.width + 'x' + screen.height,
                viewport_size: $(window).width() + 'x' + $(window).height(),
                ...data
            };

            this.log('Tracking event:', eventType, eventData);
            this.sendEvent(eventData);
        },

        sendEvent: function(eventData) {
            const self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'woo_offers_track_event',
                    event_data: JSON.stringify(eventData),
                    nonce: this.config.nonce
                },
                success: function(response) {
                    self.log('Event tracked successfully:', eventData.event_type);
                },
                error: function(xhr, status, error) {
                    self.log('Event tracking failed:', eventData.event_type, error);
                }
            });
        },

        showOfferMessage: function(message, type = 'info') {
            // Remove existing messages
            $('.woo-offers-message').remove();
            
            const messageClass = 'woo-offers-message-' + type;
            const messageHtml = `
                <div class="woo-offers-message ${messageClass}" style="
                    position: fixed; top: 20px; right: 20px; z-index: 9999;
                    background: ${type === 'success' ? '#4CAF50' : '#f44336'};
                    color: white; padding: 15px 20px; border-radius: 5px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                    max-width: 300px; word-wrap: break-word;
                ">
                    <span class="message-text">${message}</span>
                    <button class="message-close" style="
                        background: none; border: none; color: white; 
                        font-size: 18px; cursor: pointer; float: right;
                        margin-left: 10px; line-height: 1;
                    ">&times;</button>
                </div>
            `;
            
            $('body').append(messageHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.woo-offers-message').fadeOut();
            }, 5000);
            
            // Manual close
            $('.message-close').on('click', function() {
                $(this).parent().fadeOut();
            });
        },

        getCurrentProductId: function() {
            // Try multiple methods to get product ID
            return $('form.cart').data('product_id') || 
                   $('.woo-offers-container').data('product-id') ||
                   $('body').attr('class')?.match(/postid-(\d+)/)?.[1] ||
                   woo_offers_frontend?.product_id ||
                   0;
        },

        log: function() {
            if (this.config.debugMode && console && console.log) {
                const args = Array.prototype.slice.call(arguments);
                args.unshift('[WooOffers Analytics]');
                console.log.apply(console, args);
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WooOffersAnalytics.init();
    });

    // Expose globally for debugging
    window.WooOffersAnalytics = WooOffersAnalytics;

})(jQuery);