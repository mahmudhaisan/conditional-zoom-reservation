<?php

/**
 * Plugin Name: Users Conditional Zoom Reservation
 * Plugin URI: https://github.com/mahmudhaisan/woo-solutions
 * Description: Users Conditional Zoom Reservation 
 * Author: Mahmud haisan                                     
 * Author URI: https://github.com/mahmudhaisan
 * Developer: Mahmud Haisan
 * Developer URI: https://github.com/mahmudhaisan
 * Text Domain: zoomwooreservation
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */



if (!defined('ABSPATH')) {
    die('are you cheating');
}

define("PLUGINS_PATHS_ASSETS", plugin_dir_url(__FILE__) . 'assets/');
define("PLUGINS_PATHS", plugin_dir_url(__FILE__) . '');



function users_zoom_reservation_enqueue_files() {

    wp_enqueue_style('zoom_reservation-style', PLUGINS_PATHS_ASSETS . 'css/style.css');
    wp_enqueue_script('zoom_reservation-jquery', PLUGINS_PATHS_ASSETS . 'js/script.js', array('jquery'));
}


add_action('wp_enqueue_scripts', 'users_zoom_reservation_enqueue_files');




// woocommerce price filter callback
function zoom_product_price_change_for_members($price) {

    // get current users id
    $current_user_id = wp_get_current_user()->ID;

    // yith membership post type args
    $membership_post_args = array(
        'numberposts' => -1,
        'post_type' => 'ywcmbs-membership'
    );

    // get yith membership post type full row
    $membership_posts = get_posts($membership_post_args);

    foreach ($membership_posts as $membership_post) {

        $membership_author_id = intval($membership_post->post_author);
        if ($membership_author_id == $current_user_id) {
            $membership_post_id = $membership_post->ID;
        }
    }



    // var_dump($membership_posts);
    // 1. Decrease cradit on user appointment purchase. 
    // 2. Give warning if user don't have cradit. User will not be able to purchase.
    // 3. Give backend option to control credit. You can use product custom field.

    if ($current_user_id) {

        // var_dump($membership_author_id);

        $zoom_product_slug = 'zoom-meeting';

        // get product info by product slug 
        $zoom_product_obj = get_page_by_path($zoom_product_slug, OBJECT, 'product');

        // get product id
        $zoom_product_id = $zoom_product_obj->ID;

        // get product by id
        $zoom_product = wc_get_product($zoom_product_id);

        // get regular pice
        $zoom_product_price = $zoom_product->get_regular_price();

        // if the user buy any package, then zoom product price will be zero
        $price = $zoom_product_price  * 0;

        // get total credit from array zero index 
        $users_credit_info = get_post_meta($membership_post_id, '_credits')[0];



        if ($membership_author_id == $current_user_id) {

            // get all orders infos of current user
            $customers_orders_infos = wc_get_orders(array(
                'numberposts' => -1,
                'status' => 'completed',
                'author' => $current_user_id
            ));

            // Loop through each WC_Order object
            foreach ($customers_orders_infos as $customers_order_info) {
                $customers_order_id = intval($customers_order_info->get_id()); // The order ID
                $customers_order_status = $customers_order_info->get_status(); // The order status


                global $wpdb;
                $order_product_lookup = "SELECT `product_id` FROM `wp_wc_order_product_lookup` WHERE `order_id` = $customers_order_id";
                $users_product_ids_based_on_order_id = $wpdb->get_results($order_product_lookup);
                // echo 'break';
                // echo $customers_order_id . '-';
                $users_product_id = $users_product_ids_based_on_order_id[0]->product_id;
                echo $users_product_id . ' ';

                if ($zoom_product_id == $users_product_id) {
                }


                echo '<br> ';
            }




            echo '<br> ';

            // decrease_yith_credits_on_users_zoom_product_purchase();
            return $price;
            // $members_activities = get_post_meta($membership_post_id, '_activities');
        } else {
            return $price;
        }
    }
}

// get woocommerce price filter
add_filter('woocommerce_get_price', 'zoom_product_price_change_for_members', 10, 2);






//Step 1. Add Link (Tab) to My Account menu

add_filter('woocommerce_account_menu_items', 'reservation_add_links_account_page', 40);
function reservation_add_links_account_page($menu_links) {
    $menu_links = array_slice($menu_links, 0, 2, true)
        + array('reservation-info' => 'Reservation')
        + array_slice($menu_links, 2, NULL, true);
    return $menu_links;
}



//* Step 2. Register Permalink Endpoint
add_action('init', 'reservation_endpoints');
function reservation_endpoints() {

    add_rewrite_endpoint('reservation-info', EP_PAGES);
}




//Step 3. Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint

add_action('woocommerce_account_reservation-info_endpoint', 'membership_reservation_infos');

function membership_reservation_infos() {

?>


    <div class="yith-wcmbs-membership-details">
        <div class="yith-wcmbs-membership-detail yith-wcmbs-membership-detail--remaining-credits">
            <div class="yith-wcmbs-membership-detail__title">Remaining Credits</div>
            <div class="yith-wcmbs-membership-detail__value">2500</div>
        </div>

    </div>


<?php
}





// Decrease cradit on user appointment purchase. 


// function decrease_yith_credits_on_users_zoom_product_purchase() {
//     $current_user_id = wp_get_current_user()->ID;

  
// }


// decrease_yith_credits_on_users_zoom_product_purchase();
