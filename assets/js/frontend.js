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
            
            // ✅ COMPATIBILITY: Check if advanced analytics-tracker.js is loaded
            if (window.WooOffersAnalytics && typeof window.WooOffersAnalytics.trackView === 'function') {
                this.log('Advanced analytics tracker detected, using simplified offer-specific tracking');
                this.useAdvancedTracker = true;
                this.bindOfferEvents(); // Only track offer-specific events
                this.setupConversionTracking();
                this.setCampaignAttribution();
            } else {
                this.log('Using full analytics tracking');
                this.useAdvancedTracker = false;
                this.bindEvents();
                this.trackOfferViews();
                this.setupConversionTracking();
                this.setCampaignAttribution();
            }
        },

        setCampaignAttribution: function() {
            // ✅ NEW: Set campaign attribution for conversion tracking
            const campaignId = this.getCampaignIdFromPage();
            if (campaignId) {
                this.log('Setting campaign attribution:', campaignId);
                
                // Store attribution in session and cookie
                sessionStorage.setItem('woo_offers_campaign_attribution', campaignId);
                document.cookie = `woo_offers_campaign_attribution=${campaignId}; path=/; max-age=${30 * 24 * 60 * 60}; SameSite=Lax`;
            }
        },

        getCampaignIdFromPage: function() {
            // Try to find campaign ID from various sources
            const campaignElement = document.querySelector('[data-campaign-id]');
            if (campaignElement) {
                return campaignElement.getAttribute('data-campaign-id');
            }
            
            // Check URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const campaignParam = urlParams.get('campaign_id') || urlParams.get('woo_campaign');
            if (campaignParam) {
                return campaignParam;
            }
            
            return null;
        },

        bindOfferEvents: function() {
            // ✅ SIMPLIFIED: Only track offer-specific events when advanced tracker is present
            const self = this;

            // Track offer application attempts (not covered by advanced tracker)
            $(document).on('click', '.apply-percentage-discount, .apply-fixed-discount, .apply-bogo-offer, .apply-bundle-offer, .apply-quantity-discount, .apply-free-shipping', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const offerId = $button.data('offer-id');
                const offerType = $button.closest('.woo-offer').data('offer-type');
                
                self.applyOfferWithTracking(offerId, offerType, $button);
            });
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
            
            // ✅ SECURITY: Validate input data before processing
            if (!this.validateOfferInput(offerId, offerType)) {
                this.showOfferMessage('Invalid offer data', 'error');
                return;
            }

            // ✅ SECURITY: Validate nonce before making request
            if (!this.validateNonce()) {
                this.showOfferMessage('Security validation failed. Please refresh the page.', 'error');
                return;
            }
            
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

            // ✅ SECURITY: Use secure AJAX wrapper with sanitized data
            this.secureAjax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'woo_offers_apply_offer',
                    offer_id: this.sanitizeInput(offerId, 'int'),
                    product_id: this.sanitizeInput(this.getCurrentProductId(), 'int'),
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
            
            // ✅ FIXED: Send data in format expected by AnalyticsManager.php
            const ajaxData = {
                action: 'woo_offers_track_event',
                nonce: this.config.nonce,
                event_type: eventData.event_type,
                campaign_id: eventData.offer_id || eventData.campaign_id || 0,
                page_url: eventData.url || window.location.href,
                referrer_url: eventData.referrer || document.referrer,
                session_id: eventData.session_id,
                user_id: eventData.user_id,
                metadata: JSON.stringify({
                    offer_type: eventData.offer_type,
                    product_id: eventData.product_id,
                    button_type: eventData.button_type,
                    interaction_type: eventData.interaction_type,
                    element_type: eventData.element_type,
                    discount_amount: eventData.discount_amount,
                    coupon_code: eventData.coupon_code,
                    duration: eventData.duration,
                    error_message: eventData.error_message,
                    error_type: eventData.error_type,
                    status_code: eventData.status_code,
                    user_agent: eventData.user_agent,
                    screen_resolution: eventData.screen_resolution,
                    viewport_size: eventData.viewport_size,
                    timestamp: eventData.timestamp
                })
            };
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        self.log('Event tracked successfully:', eventData.event_type);
                    } else {
                        self.log('Event tracking failed:', response.data?.message || 'Unknown error');
                    }
                },
                error: function(xhr, status, error) {
                    self.log('Event tracking AJAX error:', eventData.event_type, error);
                }
            });
        },

        showOfferMessage: function(message, type = 'info') {
            // Remove existing messages
            $('.woo-offers-message').remove();
            
            // ✅ SECURITY: Sanitize message type and escape HTML content
            const sanitizedType = this.sanitizeInput(type, 'string');
            const escapedMessage = this.escapeHtml(message);
            const messageClass = 'woo-offers-message-' + sanitizedType;
            
            // ✅ SECURITY: Use safer DOM manipulation instead of innerHTML
            const $messageDiv = $('<div>')
                .addClass('woo-offers-message')
                .addClass(messageClass)
                .css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    zIndex: 9999,
                    background: type === 'success' ? '#4CAF50' : '#f44336',
                    color: 'white',
                    padding: '15px 20px',
                    borderRadius: '5px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
                    maxWidth: '300px',
                    wordWrap: 'break-word'
                });

            const $messageText = $('<span>')
                .addClass('message-text')
                .text(message); // Use .text() to prevent XSS

            const $closeButton = $('<button>')
                .addClass('message-close')
                .css({
                    background: 'none',
                    border: 'none',
                    color: 'white',
                    fontSize: '18px',
                    cursor: 'pointer',
                    float: 'right',
                    marginLeft: '10px',
                    lineHeight: 1
                })
                .html('&times;');

            $messageDiv.append($messageText).append($closeButton);
            $('body').append($messageDiv);
            
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
        },

        // ✅ SECURITY ENHANCEMENTS

        /**
         * Validate offer input data
         */
        validateOfferInput: function(offerId, offerType) {
            // Validate offer ID
            if (!offerId || isNaN(parseInt(offerId)) || parseInt(offerId) <= 0) {
                this.log('Security: Invalid offer ID:', offerId);
                return false;
            }

            // Validate offer type (whitelist approach)
            const allowedTypes = [
                'percentage', 'fixed', 'bogo', 'bundle', 'quantity', 'free_shipping'
            ];
            
            if (!offerType || !allowedTypes.includes(offerType.toLowerCase())) {
                this.log('Security: Invalid offer type:', offerType);
                return false;
            }

            return true;
        },

        /**
         * Sanitize input data based on type
         */
        sanitizeInput: function(value, type) {
            switch (type) {
                case 'int':
                    return parseInt(value) || 0;
                
                case 'float':
                    return parseFloat(value) || 0.0;
                
                case 'string':
                    return String(value).replace(/[<>\"'&]/g, function(match) {
                        const escapeMap = {
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#x27;',
                            '&': '&amp;'
                        };
                        return escapeMap[match];
                    });
                
                case 'url':
                    try {
                        const url = new URL(value);
                        return url.href;
                    } catch (e) {
                        return '';
                    }
                
                default:
                    return value;
            }
        },

        /**
         * Enhanced secure AJAX wrapper
         */
        secureAjax: function(options) {
            const self = this;
            
            // Add security headers and validation
            const defaultOptions = {
                timeout: 30000, // 30 second timeout
                beforeSend: function(xhr) {
                    // Add custom security headers if needed
                    xhr.setRequestHeader('X-WooOffers-Request', 'true');
                },
                error: function(xhr, status, error) {
                    // Enhanced error logging
                    self.log('Secure AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        url: options.url
                    });
                    
                    // Call original error handler if provided
                    if (options.originalError) {
                        options.originalError(xhr, status, error);
                    }
                }
            };

            // Store original error handler
            if (options.error) {
                defaultOptions.originalError = options.error;
            }

            // Merge options
            const secureOptions = $.extend({}, defaultOptions, options);
            
            return $.ajax(secureOptions);
        },

        /**
         * Validate and refresh nonce if needed
         */
        validateNonce: function() {
            // Check if nonce is still valid (simple client-side check)
            if (!this.config.nonce || this.config.nonce.length < 10) {
                this.log('Security: Invalid nonce detected');
                return false;
            }
            
            return true;
        },

        /**
         * Escape HTML content to prevent XSS
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WooOffersAnalytics.init();
    });

    // Expose globally for debugging
    window.WooOffersAnalytics = WooOffersAnalytics;

})(jQuery);