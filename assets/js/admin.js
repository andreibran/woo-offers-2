/**
 * Woo Offers Admin JavaScript
 *
 * @package WooOffers
 * @since 2.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    /**
     * Initialize admin functionality
     */
    function initWooOffersAdmin() {
        initTabSwitching();
        initPostboxes();
        initConfirmActions();
        initOffersListTable();
    }

    /**
     * Initialize tab switching for settings pages
     */
    function initTabSwitching() {
        $('.woo-offers-settings-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            
            var target = $(this).attr('href');
            
            // Remove active class from all tabs and panes
            $('.nav-tab').removeClass('nav-tab-active');
            $('.tab-pane').removeClass('active');
            
            // Add active class to clicked tab and corresponding pane
            $(this).addClass('nav-tab-active');
            $(target).addClass('active');
        });
    }

    /**
     * Initialize WordPress postboxes (metaboxes)
     */
    function initPostboxes() {
        if (typeof postboxes !== 'undefined') {
            postboxes.add_postbox_toggles(pagenow);
        }
    }

    /**
     * Initialize confirmation dialogs for destructive actions
     */
    function initConfirmActions() {
        $(document).on('click', '[data-confirm]', function(e) {
            var message = $(this).data('confirm') || wooOffersAdmin.strings.confirmDelete;
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });

        // Handle delete links with confirmation
        $(document).on('click', 'a[href*="action=delete"]', function(e) {
            if (!confirm(wooOffersAdmin.strings.confirmDelete)) {
                e.preventDefault();
                return false;
            }
        });
    }

    /**
     * Initialize offers list table functionality
     */
    function initOffersListTable() {
        // Auto-submit form when filters change
        $('#filter-by-status, #filter-by-type').on('change', function() {
            $('#offers-filter').submit();
        });

        // Enhanced search functionality
        var searchTimeout;
        $('#offer-search-input').on('input', function() {
            clearTimeout(searchTimeout);
            var $this = $(this);
            
            // Add a small delay to avoid too many submissions
            searchTimeout = setTimeout(function() {
                if ($this.val().length >= 3 || $this.val().length === 0) {
                    $('#offers-filter').submit();
                }
            }, 500);
        });

        // Handle bulk actions
        $(document).on('submit', '#offers-filter', function(e) {
            var action = $('select[name="action"]').val();
            var action2 = $('select[name="action2"]').val();
            var selectedAction = action !== '-1' ? action : action2;
            
            if (selectedAction && selectedAction !== '-1') {
                var checkedItems = $('input[name="offer[]"]:checked');
                
                if (checkedItems.length === 0) {
                    e.preventDefault();
                    alert(wooOffersAdmin.strings.selectItems || 'Please select at least one item.');
                    return false;
                }
                
                if (selectedAction === 'delete') {
                    if (!confirm(wooOffersAdmin.strings.confirmBulkDelete || 'Are you sure you want to delete the selected offers?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });

        // Handle "select all" checkbox
        $(document).on('change', '#cb-select-all-1, #cb-select-all-2', function() {
            var checked = $(this).prop('checked');
            $('input[name="offer[]"]').prop('checked', checked);
        });

        // Update "select all" checkbox when individual items are checked/unchecked
        $(document).on('change', 'input[name="offer[]"]', function() {
            var totalItems = $('input[name="offer[]"]').length;
            var checkedItems = $('input[name="offer[]"]:checked').length;
            
            $('#cb-select-all-1, #cb-select-all-2').prop('checked', totalItems === checkedItems);
        });

        // Add loading states for filters
        $('#post-query-submit').on('click', function() {
            $(this).prop('disabled', true).text(wooOffersAdmin.strings.filtering || 'Filtering...');
        });

        // Reset filter loading state on page load
        $('#post-query-submit').prop('disabled', false).text(wooOffersAdmin.strings.filter || 'Filter');
    }

    /**
     * Show admin notice
     * @param {string} message - The message to display
     * @param {string} type - The notice type (success, error, warning, info)
     */
    function showAdminNotice(message, type) {
        type = type || 'success';
        
        var noticeHtml = '<div class="notice notice-' + type + ' is-dismissible">' +
                        '<p>' + message + '</p>' +
                        '<button type="button" class="notice-dismiss">' +
                        '<span class="screen-reader-text">Dismiss this notice.</span>' +
                        '</button>' +
                        '</div>';
        
        $('.wrap h1').after(noticeHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.notice').slideUp();
        }, 5000);
    }

    /**
     * Generic AJAX handler
     * @param {Object} data - The AJAX data to send
     * @param {Function} successCallback - Success callback function
     * @param {Function} errorCallback - Error callback function
     */
    function doAjax(data, successCallback, errorCallback) {
        data.action = data.action || '';
        data.nonce = wooOffersAdmin.nonce;
        
        $.ajax({
            url: wooOffersAdmin.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    if (typeof successCallback === 'function') {
                        successCallback(response.data);
                    }
                } else {
                    var errorMessage = response.data ? response.data.message : wooOffersAdmin.strings.error;
                    if (typeof errorCallback === 'function') {
                        errorCallback(errorMessage);
                    } else {
                        showAdminNotice(errorMessage, 'error');
                    }
                }
            },
            error: function() {
                var errorMessage = wooOffersAdmin.strings.error;
                if (typeof errorCallback === 'function') {
                    errorCallback(errorMessage);
                } else {
                    showAdminNotice(errorMessage, 'error');
                }
            }
        });
    }

    /**
     * Handle filter changes
     */
    function initFilters() {
        $('.woo-offers-filters select').on('change', function() {
            // This will be implemented when we add the WP_List_Table
            console.log('Filter changed:', $(this).val());
        });
    }

    /**
     * Handle date range changes for analytics
     */
    function initDateRangeFilters() {
        $('.woo-offers-date-range input[type="date"]').on('change', function() {
            // This will be implemented when we add the analytics functionality
            console.log('Date range changed');
        });
    }

    /**
     * Initialize everything when DOM is ready
     */
    initWooOffersAdmin();
    initFilters();
    initDateRangeFilters();

    // Make functions available globally for other scripts
    window.WooOffersAdmin = {
        showNotice: showAdminNotice,
        doAjax: doAjax
    };

});
