<?php
/**
 * Schedule & Conditions Metabox Template
 * Modern UI for scheduling offers and setting conditions
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get saved schedule data
$schedule_data = $offer_data['schedule'] ?? array();
?>

<div class="woo-offers-schedule-metabox">
    
    <!-- Offer Timing Section -->
    <div class="schedule-section">
        <h4>⏰ <?php _e( 'Offer Timing', 'woo-offers' ); ?></h4>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="start_date"><?php _e( 'Start Date', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <input type="datetime-local" 
                           name="start_date" 
                           id="start_date" 
                           value="<?php echo esc_attr( $schedule_data['start_date'] ?? '' ); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php _e( 'When should this offer start? Leave empty for immediate activation.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="end_date"><?php _e( 'End Date', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <input type="datetime-local" 
                           name="end_date" 
                           id="end_date" 
                           value="<?php echo esc_attr( $schedule_data['end_date'] ?? '' ); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php _e( 'When should this offer end? Leave empty for unlimited duration.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Usage Limits Section -->
    <div class="schedule-section">
        <h4>🎯 <?php _e( 'Usage Limits', 'woo-offers' ); ?></h4>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="usage_limit"><?php _e( 'Total Usage Limit', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="usage_limit" 
                           id="usage_limit" 
                           value="<?php echo esc_attr( $schedule_data['usage_limit'] ?? '' ); ?>"
                           min="1" 
                           class="small-text">
                    <p class="description">
                        <?php _e( 'Maximum number of times this offer can be used. Leave empty for unlimited.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="usage_limit_per_user"><?php _e( 'Usage Limit Per User', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="usage_limit_per_user" 
                           id="usage_limit_per_user" 
                           value="<?php echo esc_attr( $schedule_data['usage_limit_per_user'] ?? '' ); ?>"
                           min="1" 
                           class="small-text">
                    <p class="description">
                        <?php _e( 'Maximum number of times a single user can use this offer.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Purchase Conditions Section -->
    <div class="schedule-section">
        <h4>💰 <?php _e( 'Purchase Conditions', 'woo-offers' ); ?></h4>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="minimum_amount"><?php _e( 'Minimum Purchase Amount', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="minimum_amount" 
                           id="minimum_amount" 
                           value="<?php echo esc_attr( $schedule_data['minimum_amount'] ?? '' ); ?>"
                           min="0" 
                           step="0.01" 
                           class="regular-text">
                    <p class="description">
                        <?php printf( __( 'Minimum cart total required to use this offer (%s).', 'woo-offers' ), get_woocommerce_currency_symbol() ); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="maximum_amount"><?php _e( 'Maximum Purchase Amount', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="maximum_amount" 
                           id="maximum_amount" 
                           value="<?php echo esc_attr( $schedule_data['maximum_amount'] ?? '' ); ?>"
                           min="0" 
                           step="0.01" 
                           class="regular-text">
                    <p class="description">
                        <?php printf( __( 'Maximum cart total allowed to use this offer (%s). Leave empty for no limit.', 'woo-offers' ), get_woocommerce_currency_symbol() ); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Customer Restrictions Section -->
    <div class="schedule-section">
        <h4>👥 <?php _e( 'Customer Restrictions', 'woo-offers' ); ?></h4>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="allowed_user_roles"><?php _e( 'Allowed User Roles', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <?php
                    $selected_roles = $schedule_data['allowed_user_roles'] ?? array();
                    $user_roles = wp_roles()->get_names();
                    ?>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e( 'Allowed User Roles', 'woo-offers' ); ?></legend>
                        
                        <label for="all_users">
                            <input type="checkbox" 
                                   name="allowed_user_roles[]" 
                                   id="all_users" 
                                   value="all" 
                                   <?php checked( in_array( 'all', $selected_roles ) || empty( $selected_roles ) ); ?>>
                            <?php _e( 'All Users (including guests)', 'woo-offers' ); ?>
                        </label><br>
                        
                        <?php foreach ( $user_roles as $role_key => $role_name ): ?>
                        <label for="role_<?php echo esc_attr( $role_key ); ?>">
                            <input type="checkbox" 
                                   name="allowed_user_roles[]" 
                                   id="role_<?php echo esc_attr( $role_key ); ?>" 
                                   value="<?php echo esc_attr( $role_key ); ?>" 
                                   <?php checked( in_array( $role_key, $selected_roles ) ); ?>>
                            <?php echo esc_html( $role_name ); ?>
                        </label><br>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="description">
                        <?php _e( 'Select which user roles can use this offer. If none selected, all users can use it.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="exclude_sale_items"><?php _e( 'Exclude Sale Items', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e( 'Exclude Sale Items', 'woo-offers' ); ?></legend>
                        
                        <label for="exclude_sale_items">
                            <input type="checkbox" 
                                   name="exclude_sale_items" 
                                   id="exclude_sale_items" 
                                   value="1" 
                                   <?php checked( $schedule_data['exclude_sale_items'] ?? false ); ?>>
                            <?php _e( 'Exclude products that are already on sale', 'woo-offers' ); ?>
                        </label>
                    </fieldset>
                    <p class="description">
                        <?php _e( 'Check this to prevent the offer from applying to products already on sale.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Days & Time Restrictions Section -->
    <div class="schedule-section">
        <h4>📅 <?php _e( 'Days & Time Restrictions', 'woo-offers' ); ?></h4>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="allowed_days"><?php _e( 'Allowed Days', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <?php
                    $selected_days = $schedule_data['allowed_days'] ?? array();
                    $days = array(
                        'monday'    => __( 'Monday', 'woo-offers' ),
                        'tuesday'   => __( 'Tuesday', 'woo-offers' ),
                        'wednesday' => __( 'Wednesday', 'woo-offers' ),
                        'thursday'  => __( 'Thursday', 'woo-offers' ),
                        'friday'    => __( 'Friday', 'woo-offers' ),
                        'saturday'  => __( 'Saturday', 'woo-offers' ),
                        'sunday'    => __( 'Sunday', 'woo-offers' )
                    );
                    ?>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e( 'Allowed Days', 'woo-offers' ); ?></legend>
                        
                        <?php foreach ( $days as $day_key => $day_name ): ?>
                        <label for="day_<?php echo esc_attr( $day_key ); ?>">
                            <input type="checkbox" 
                                   name="allowed_days[]" 
                                   id="day_<?php echo esc_attr( $day_key ); ?>" 
                                   value="<?php echo esc_attr( $day_key ); ?>" 
                                   <?php checked( in_array( $day_key, $selected_days ) || empty( $selected_days ) ); ?>>
                            <?php echo esc_html( $day_name ); ?>
                        </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="description">
                        <?php _e( 'Select which days of the week this offer is active. If none selected, offer is active all week.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="time_start"><?php _e( 'Active Time Range', 'woo-offers' ); ?></label>
                </th>
                <td>
                    <input type="time" 
                           name="time_start" 
                           id="time_start" 
                           value="<?php echo esc_attr( $schedule_data['time_start'] ?? '' ); ?>"
                           class="regular-text">
                    <span style="margin: 0 10px;"><?php _e( 'to', 'woo-offers' ); ?></span>
                    <input type="time" 
                           name="time_end" 
                           id="time_end" 
                           value="<?php echo esc_attr( $schedule_data['time_end'] ?? '' ); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php _e( 'Set specific hours when this offer is active. Leave empty for all-day availability.', 'woo-offers' ); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

</div>

<style>
.woo-offers-schedule-metabox .schedule-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.woo-offers-schedule-metabox .schedule-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.woo-offers-schedule-metabox h4 {
    margin: 0 0 15px 0;
    color: #1d2327;
    font-size: 14px;
    font-weight: 600;
}

.woo-offers-schedule-metabox fieldset {
    margin: 0;
    padding: 0;
    border: none;
}

.woo-offers-schedule-metabox fieldset label {
    display: inline-block;
    margin-right: 20px;
    margin-bottom: 8px;
    font-weight: normal;
    cursor: pointer;
}

.woo-offers-schedule-metabox input[type="checkbox"] {
    margin-right: 6px;
}

.woo-offers-schedule-metabox .form-table th {
    width: 150px;
    padding: 10px 0;
}

.woo-offers-schedule-metabox .form-table td {
    padding: 10px 0 10px 20px;
}
</style> 