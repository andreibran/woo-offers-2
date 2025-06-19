<?php
/**
 * Import/Export Admin Page Template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'manage_woocommerce' ) ) {
    wp_die( __( 'You do not have sufficient permissions to access this page.', 'woo-offers' ) );
}

// Display notices
if ( isset( $_GET['message'] ) ) {
    $message_type = sanitize_text_field( $_GET['message'] );
    switch ( $message_type ) {
        case 'exported':
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Offers exported successfully!', 'woo-offers' ); ?></p>
            </div>
            <?php
            break;
        case 'imported':
            $count = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf( _n( '%d offer imported successfully!', '%d offers imported successfully!', $count, 'woo-offers' ), $count ); ?></p>
            </div>
            <?php
            break;
        case 'import_errors':
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e( 'Some offers could not be imported. Please check the import log for details.', 'woo-offers' ); ?></p>
            </div>
            <?php
            break;
        case 'error':
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e( 'An error occurred during the operation. Please try again.', 'woo-offers' ); ?></p>
            </div>
            <?php
            break;
    }
}
?>

<div class="wrap woo-offers-import-export">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p class="description">
        <?php _e( 'Import and export your offers to backup your data or transfer it between sites.', 'woo-offers' ); ?>
    </p>

    <div class="woo-offers-import-export-container">
        <div class="woo-offers-import-export-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#export" class="nav-tab nav-tab-active" data-tab="export">
                    <?php _e( 'Export', 'woo-offers' ); ?>
                </a>
                <a href="#import" class="nav-tab" data-tab="import">
                    <?php _e( 'Import', 'woo-offers' ); ?>
                </a>
            </nav>

            <!-- Export Tab -->
            <div id="export" class="tab-content active">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e( 'Export Offers', 'woo-offers' ); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php _e( 'Export your offers to a file for backup or transfer to another site.', 'woo-offers' ); ?></p>
                        
                        <!-- Export Progress -->
                        <div id="export-progress" class="progress-container" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <div class="progress-text"><?php _e( 'Preparing export...', 'woo-offers' ); ?></div>
                        </div>

                        <div class="export-options">
                            <h3><?php _e( 'CSV Format', 'woo-offers' ); ?></h3>
                            <p><?php _e( 'Export offers as a CSV file suitable for spreadsheet applications.', 'woo-offers' ); ?></p>
                            <form method="post" style="display: inline;" id="csv-export-form">
                                <?php wp_nonce_field( 'woo_offers_export_csv', 'export_csv_nonce' ); ?>
                                <input type="hidden" name="action" value="export_csv">
                                <button type="submit" class="button button-primary export-btn" data-format="csv">
                                    <span class="dashicons dashicons-download"></span>
                                    <?php _e( 'Export as CSV', 'woo-offers' ); ?>
                                </button>
                            </form>
                            
                            <hr style="margin: 20px 0;">
                            
                            <h3><?php _e( 'JSON Format', 'woo-offers' ); ?></h3>
                            <p><?php _e( 'Export offers as JSON with complete configuration details.', 'woo-offers' ); ?></p>
                            <form method="post" style="display: inline;" id="json-export-form">
                                <?php wp_nonce_field( 'woo_offers_export_json', 'export_json_nonce' ); ?>
                                <input type="hidden" name="action" value="export_json">
                                <button type="submit" class="button button-primary export-btn" data-format="json">
                                    <span class="dashicons dashicons-download"></span>
                                    <?php _e( 'Export as JSON', 'woo-offers' ); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Tab -->
            <div id="import" class="tab-content">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e( 'Import Offers', 'woo-offers' ); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php _e( 'Import offers from a CSV or JSON file.', 'woo-offers' ); ?></p>
                        
                        <!-- Import Progress -->
                        <div id="import-progress" class="progress-container" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <div class="progress-text"><?php _e( 'Processing import...', 'woo-offers' ); ?></div>
                            <div class="progress-details">
                                <span id="processed-count">0</span> / <span id="total-count">0</span> <?php _e( 'offers processed', 'woo-offers' ); ?>
                            </div>
                        </div>

                        <!-- Import Results -->
                        <div id="import-results" class="import-results" style="display: none;">
                            <h3><?php _e( 'Import Results', 'woo-offers' ); ?></h3>
                            <div class="results-summary">
                                <div class="result-item success-count">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <strong id="success-count">0</strong> <?php _e( 'successfully imported', 'woo-offers' ); ?>
                                </div>
                                <div class="result-item error-count">
                                    <span class="dashicons dashicons-warning"></span>
                                    <strong id="error-count">0</strong> <?php _e( 'errors encountered', 'woo-offers' ); ?>
                                </div>
                            </div>
                            <div id="error-details" class="error-details" style="display: none;">
                                <h4><?php _e( 'Error Details:', 'woo-offers' ); ?></h4>
                                <ul id="error-list"></ul>
                            </div>
                        </div>
                        
                        <form method="post" enctype="multipart/form-data" id="import-form">
                            <?php wp_nonce_field( 'woo_offers_import_csv', 'import_csv_nonce' ); ?>
                            <input type="hidden" name="action" value="import_csv">
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="import_file"><?php _e( 'Select File', 'woo-offers' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="file" id="import_file" name="import_file" accept=".csv,.json" required>
                                        <p class="description">
                                            <?php _e( 'Choose a CSV or JSON file to import. Maximum file size: 2MB.', 'woo-offers' ); ?>
                                        </p>
                                        <div id="file-info" class="file-info" style="display: none;">
                                            <span class="dashicons dashicons-media-document"></span>
                                            <span id="file-name"></span>
                                            <span id="file-size"></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="import_mode"><?php _e( 'Import Mode', 'woo-offers' ); ?></label>
                                    </th>
                                    <td>
                                        <select id="import_mode" name="import_mode">
                                            <option value="create"><?php _e( 'Create new offers only', 'woo-offers' ); ?></option>
                                            <option value="update"><?php _e( 'Update existing offers', 'woo-offers' ); ?></option>
                                            <option value="replace"><?php _e( 'Replace all offers', 'woo-offers' ); ?></option>
                                        </select>
                                        <p class="description">
                                            <?php _e( 'Choose how to handle existing offers during import.', 'woo-offers' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="validate_products"><?php _e( 'Validate Products', 'woo-offers' ); ?></label>
                                    </th>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="validate_products" name="validate_products" value="1" checked>
                                            <?php _e( 'Validate that products exist before importing offers', 'woo-offers' ); ?>
                                        </label>
                                        <p class="description">
                                            <?php _e( 'Recommended to prevent importing offers for non-existent products.', 'woo-offers' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <button type="submit" class="button button-primary" id="import-btn">
                                    <span class="dashicons dashicons-upload"></span>
                                    <?php _e( 'Import Offers', 'woo-offers' ); ?>
                                </button>
                                <button type="button" class="button" id="preview-import" style="display: none;">
                                    <span class="dashicons dashicons-visibility"></span>
                                    <?php _e( 'Preview Import', 'woo-offers' ); ?>
                                </button>
                            </p>
                        </form>

                        <!-- Help Section -->
                        <div class="import-help">
                            <h3><?php _e( 'Import Help', 'woo-offers' ); ?></h3>
                            <div class="help-content">
                                <h4><?php _e( 'CSV Format Requirements', 'woo-offers' ); ?></h4>
                                <p><?php _e( 'Your CSV file should include the following columns:', 'woo-offers' ); ?></p>
                                <ul>
                                    <li><strong>Title:</strong> <?php _e( 'Offer name (required)', 'woo-offers' ); ?></li>
                                    <li><strong>Type:</strong> <?php _e( 'Offer type (percentage, fixed, bogo, etc.)', 'woo-offers' ); ?></li>
                                    <li><strong>Status:</strong> <?php _e( 'active, inactive, or scheduled', 'woo-offers' ); ?></li>
                                    <li><strong>Value:</strong> <?php _e( 'Discount value', 'woo-offers' ); ?></li>
                                    <li><strong>Description:</strong> <?php _e( 'Offer description (optional)', 'woo-offers' ); ?></li>
                                </ul>
                                
                                <div class="sample-download">
                                    <h4><?php _e( 'Sample File', 'woo-offers' ); ?></h4>
                                    <p><?php _e( 'Download a sample CSV file to see the expected format:', 'woo-offers' ); ?></p>
                                    <a href="#" class="button button-secondary" id="download-sample">
                                        <span class="dashicons dashicons-download"></span>
                                        <?php _e( 'Download Sample CSV', 'woo-offers' ); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        var tabId = $(this).data('tab');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Update active content
        $('.tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // File selection handling
    $('#import_file').on('change', function() {
        var file = this.files[0];
        if (file) {
            var fileSize = (file.size / 1024 / 1024).toFixed(2);
            $('#file-name').text(file.name);
            $('#file-size').text('(' + fileSize + ' MB)');
            $('#file-info').show();
            
            // Show preview button for CSV files
            if (file.name.toLowerCase().endsWith('.csv')) {
                $('#preview-import').show();
            } else {
                $('#preview-import').hide();
            }
            
            // Validate file size
            if (file.size > 2 * 1024 * 1024) {
                alert('<?php _e( 'File is too large. Maximum size is 2MB.', 'woo-offers' ); ?>');
                $(this).val('');
                $('#file-info').hide();
                $('#preview-import').hide();
            }
        } else {
            $('#file-info').hide();
            $('#preview-import').hide();
        }
    });

    // Export button handling
    $('.export-btn').on('click', function(e) {
        var button = $(this);
        var format = button.data('format');
        
        // Show progress
        $('#export-progress').show();
        button.prop('disabled', true);
        
        // Update progress text
        $('.progress-text').text('<?php _e( 'Preparing export...', 'woo-offers' ); ?>');
        
        // Simulate progress (since export happens on server)
        var progress = 0;
        var interval = setInterval(function() {
            progress += 20;
            $('.progress-fill').css('width', progress + '%');
            
            if (progress >= 100) {
                clearInterval(interval);
                $('.progress-text').text('<?php _e( 'Download starting...', 'woo-offers' ); ?>');
                
                // Hide progress after delay
                setTimeout(function() {
                    $('#export-progress').hide();
                    $('.progress-fill').css('width', '0%');
                    button.prop('disabled', false);
                }, 2000);
            }
        }, 300);
    });

    // Import form handling
    $('#import-form').on('submit', function(e) {
        // Show progress
        $('#import-progress').show();
        $('#import-results').hide();
        $('#import-btn').prop('disabled', true);
        
        // Reset progress
        $('.progress-fill').css('width', '0%');
        $('.progress-text').text('<?php _e( 'Processing import...', 'woo-offers' ); ?>');
        
        // Note: Real progress would be handled by server-side processing
        // This is a visual indicator for user feedback
        var progress = 0;
        var interval = setInterval(function() {
            progress += 10;
            $('.progress-fill').css('width', Math.min(progress, 90) + '%');
            
            if (progress >= 90) {
                clearInterval(interval);
                $('.progress-text').text('<?php _e( 'Finalizing import...', 'woo-offers' ); ?>');
            }
        }, 500);
    });

    // Preview import functionality (placeholder)
    $('#preview-import').on('click', function(e) {
        e.preventDefault();
        alert('<?php _e( 'Preview functionality shows the first 5 rows of your CSV file to verify the format before importing.', 'woo-offers' ); ?>');
    });
    
    // Download sample CSV
    $('#download-sample').on('click', function(e) {
        e.preventDefault();
        
        // Create sample CSV content
        var csvContent = "Title,Type,Value,Status,Description\n";
        csvContent += "Summer Sale 20%,percentage,20,active,Get 20% off all summer items\n";
        csvContent += "Free Shipping,free_shipping,0,active,Free shipping on orders over $50\n";
        csvContent += "Buy 2 Get 1 Free,bogo,1,active,Buy any 2 items and get 1 free\n";
        csvContent += "$10 Off,fixed,10,inactive,Get $10 off your purchase\n";
        
        // Create and download file
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'woo-offers-sample.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Handle import results display (would be triggered by server response)
    function showImportResults(data) {
        $('#import-progress').hide();
        $('#import-results').show();
        
        $('#success-count').text(data.imported || 0);
        $('#error-count').text(data.errors ? data.errors.length : 0);
        
        if (data.errors && data.errors.length > 0) {
            $('#error-details').show();
            var errorList = $('#error-list');
            errorList.empty();
            
            data.errors.forEach(function(error) {
                errorList.append('<li>' + error + '</li>');
            });
        }
        
        $('#import-btn').prop('disabled', false);
    }
});
</script>

<style>
.woo-offers-import-export-container {
    margin-top: 20px;
}

.woo-offers-import-export-tabs .tab-content {
    display: none;
    padding: 20px 0;
}

.woo-offers-import-export-tabs .tab-content.active {
    display: block;
}

.export-options {
    margin-top: 15px;
}

.export-options h3 {
    margin-top: 0;
    color: #23282d;
}

.export-options p {
    margin-bottom: 15px;
    color: #666;
}

/* Progress Bar Styles */
.progress-container {
    margin: 20px 0;
    padding: 15px;
    background: #f0f6fc;
    border: 1px solid #c3dcf1;
    border-radius: 4px;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #e1e1e1;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0073aa, #005a87);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 10px;
}

.progress-text {
    font-weight: 600;
    color: #0073aa;
    margin-bottom: 5px;
}

.progress-details {
    font-size: 14px;
    color: #666;
}

/* Import Results Styles */
.import-results {
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.results-summary {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.result-item {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 10px;
    border-radius: 4px;
}

.result-item.success-count {
    background: #d1e7dd;
    color: #0f5132;
}

.result-item.error-count {
    background: #f8d7da;
    color: #842029;
}

.error-details {
    margin-top: 15px;
    padding: 10px;
    background: #fff3cd;
    border: 1px solid #ffecb5;
    border-radius: 4px;
}

.error-details ul {
    margin: 10px 0 0 20px;
    max-height: 200px;
    overflow-y: auto;
}

.error-details li {
    margin-bottom: 5px;
    color: #664d03;
}

/* File Info Styles */
.file-info {
    margin-top: 10px;
    padding: 8px 12px;
    background: #f0f6fc;
    border: 1px solid #c3dcf1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.file-info .dashicons {
    color: #0073aa;
}

/* Help Section Styles */
.import-help {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.import-help h3 {
    color: #23282d;
}

.import-help ul {
    margin-left: 20px;
}

.import-help li {
    margin-bottom: 5px;
}

.sample-download {
    margin-top: 20px;
    padding: 15px;
    background: #f0f6fc;
    border: 1px solid #c3dcf1;
    border-radius: 4px;
}

.sample-download h4 {
    margin-top: 0;
    color: #0073aa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .results-summary {
        flex-direction: column;
        gap: 10px;
    }
    
    .export-options {
        text-align: center;
    }
    
    .file-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}

/* Button states */
.button:disabled {
    opacity: 0.6;
    pointer-events: none;
}
</style> 