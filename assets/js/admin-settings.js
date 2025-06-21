/**
 * Woo Offers Settings Page JavaScript
 * 
 * Handles color picker initialization, file uploads, tab switching,
 * and other advanced field functionality in the settings page.
 * 
 * @package WooOffers
 * @since 3.0.0
 */

(function($) {
    'use strict';

    // Settings page functionality
    const WooOffersSettings = {
        
        /**
         * Initialize all settings functionality
         */
        init: function() {
            this.initTabs();
            this.initColorPickers();
            this.initFileUploads();
            this.initFormValidation();
            this.initTooltips();
            this.bindEvents();
        },
        
        /**
         * Initialize tab functionality
         */
        initTabs: function() {
            const $tabs = $('.woo-offers-nav-tabs .nav-tab');
            const $tabContents = $('.tab-content');
            
            $tabs.on('click', function(e) {
                e.preventDefault();
                
                const targetTab = $(this).attr('href').substring(1);
                
                // Remove active classes
                $tabs.removeClass('nav-tab-active');
                $tabContents.removeClass('active');
                
                // Add active classes
                $(this).addClass('nav-tab-active');
                $('#' + targetTab).addClass('active');
                
                // Save active tab to localStorage
                localStorage.setItem('wooOffersActiveTab', targetTab);
            });
            
            // Restore active tab from localStorage
            const activeTab = localStorage.getItem('wooOffersActiveTab') || 'general';
            $tabs.filter('[href="#' + activeTab + '"]').trigger('click');
        },
        
        /**
         * Initialize WordPress color pickers
         */
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.wp-color-picker').wpColorPicker({
                    defaultColor: false,
                    change: function(event, ui) {
                        // Trigger change event for form validation
                        $(this).trigger('change');
                    },
                    clear: function() {
                        // Trigger change event when cleared
                        $(this).trigger('change');
                    }
                });
            }
        },
        
        /**
         * Initialize file upload functionality
         */
        initFileUploads: function() {
            const self = this;
            
            $('.upload-button').on('click', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const fieldId = button.data('field');
                const accept = button.data('accept');
                
                // Create media uploader
                const uploader = wp.media({
                    title: wooOffersSettings.strings.choose_file,
                    button: {
                        text: wooOffersSettings.strings.choose_file
                    },
                    library: {
                        type: accept ? accept.split(',') : ''
                    },
                    multiple: false
                });
                
                // Handle file selection
                uploader.on('select', function() {
                    const attachment = uploader.state().get('selection').first().toJSON();
                    
                    // Update hidden field
                    $('#' + fieldId).val(attachment.url);
                    
                    // Update preview
                    $('#preview_' + fieldId).text(attachment.filename);
                    
                    // Add remove button
                    self.addRemoveButton(button, fieldId);
                });
                
                uploader.open();
            });
        },
        
        /**
         * Add remove button for uploaded files
         */
        addRemoveButton: function(uploadButton, fieldId) {
            if (uploadButton.siblings('.remove-file').length === 0) {
                const removeButton = $('<button type="button" class="button remove-file">' + 
                    wooOffersSettings.strings.remove_file + '</button>');
                
                removeButton.on('click', function(e) {
                    e.preventDefault();
                    
                    // Clear field
                    $('#' + fieldId).val('');
                    
                    // Clear preview
                    $('#preview_' + fieldId).text(wooOffersSettings.strings.no_file_selected);
                    
                    // Remove this button
                    $(this).remove();
                });
                
                uploadButton.after(removeButton);
            }
        },
        
        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            const $form = $('.woo-offers-settings-form');
            
            // Real-time validation
            $form.on('input change', 'input, select, textarea', function() {
                const $field = $(this);
                this.validateField($field);
            }.bind(this));
            
            // Form submission validation
            $form.on('submit', function(e) {
                const isValid = this.validateForm();
                
                if (!isValid) {
                    e.preventDefault();
                    this.showValidationErrors();
                }
            }.bind(this));
        },
        
        /**
         * Validate individual field
         */
        validateField: function($field) {
            const fieldType = $field.attr('type') || $field.prop('tagName').toLowerCase();
            const value = $field.val();
            let isValid = true;
            let errorMessage = '';
            
            // Remove existing validation classes
            $field.removeClass('form-invalid form-valid');
            $field.siblings('.form-error-message').remove();
            
            // Validate based on field type
            switch (fieldType) {
                case 'number':
                    const min = parseFloat($field.attr('min'));
                    const max = parseFloat($field.attr('max'));
                    const numValue = parseFloat(value);
                    
                    if (value && (isNaN(numValue) || numValue < min || numValue > max)) {
                        isValid = false;
                        errorMessage = `Value must be between ${min} and ${max}`;
                    }
                    break;
                    
                case 'url':
                    if (value && !this.isValidUrl(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid URL';
                    }
                    break;
                    
                case 'email':
                    if (value && !this.isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
            }
            
            // Apply validation classes
            if (value) {
                $field.addClass(isValid ? 'form-valid' : 'form-invalid');
                
                if (!isValid && errorMessage) {
                    $field.after('<span class="form-error-message">' + errorMessage + '</span>');
                }
            }
            
            return isValid;
        },
        
        /**
         * Validate entire form
         */
        validateForm: function() {
            let isValid = true;
            
            $('.woo-offers-settings-form input, .woo-offers-settings-form select, .woo-offers-settings-form textarea').each(function() {
                if (!this.validateField($(this))) {
                    isValid = false;
                }
            }.bind(this));
            
            return isValid;
        },
        
        /**
         * Show validation errors
         */
        showValidationErrors: function() {
            const $firstError = $('.form-invalid').first();
            
            if ($firstError.length) {
                // Scroll to first error
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
                
                // Focus on field
                $firstError.focus();
                
                // Show error notification
                this.showNotification('Please correct the errors below before saving.', 'error');
            }
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Add tooltips to field descriptions
            $('.description').each(function() {
                const $desc = $(this);
                const $field = $desc.prevAll('input, select, textarea').first();
                
                if ($field.length) {
                    $field.attr('title', $desc.text());
                }
            });
        },
        
        /**
         * Bind additional events
         */
        bindEvents: function() {
            // Reset to defaults button
            $('.reset-defaults').on('click', function(e) {
                e.preventDefault();
                
                if (confirm(wooOffersSettings.strings.confirm_reset)) {
                    // You could implement AJAX reset functionality here
                    location.reload();
                }
            });
            
            // Import/Export functionality
            $('.export-settings').on('click', function(e) {
                e.preventDefault();
                this.exportSettings();
            }.bind(this));
            
            $('.import-settings').on('change', function(e) {
                this.importSettings(e.target.files[0]);
            }.bind(this));
        },
        
        /**
         * Export settings as JSON
         */
        exportSettings: function() {
            const settings = {};
            
            // Collect all form data
            $('.woo-offers-settings-form').serializeArray().forEach(function(item) {
                settings[item.name] = item.value;
            });
            
            // Create download
            const blob = new Blob([JSON.stringify(settings, null, 2)], {
                type: 'application/json'
            });
            
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'woo-offers-settings.json';
            a.click();
            
            URL.revokeObjectURL(url);
            
            this.showNotification('Settings exported successfully!', 'success');
        },
        
        /**
         * Import settings from JSON file
         */
        importSettings: function(file) {
            if (!file) return;
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    
                    // Apply settings to form
                    Object.entries(settings).forEach(([name, value]) => {
                        const $field = $('[name="' + name + '"]');
                        
                        if ($field.length) {
                            if ($field.attr('type') === 'checkbox') {
                                $field.prop('checked', !!value);
                            } else {
                                $field.val(value);
                            }
                        }
                    });
                    
                    // Reinitialize color pickers
                    this.initColorPickers();
                    
                    this.showNotification('Settings imported successfully!', 'success');
                    
                } catch (error) {
                    this.showNotification('Error importing settings. Invalid file format.', 'error');
                }
            }.bind(this);
            
            reader.readAsText(file);
        },
        
        /**
         * Show notification message
         */
        showNotification: function(message, type) {
            const notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + 
                message + '</p></div>');
            
            $('.woo-offers-settings').prepend(notification);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                notification.fadeOut();
            }, 5000);
        },
        
        /**
         * Validate URL format
         */
        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },
        
        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WooOffersSettings.init();
    });

})(jQuery); 