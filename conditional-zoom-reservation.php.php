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

    global $wpdb;

    // get current users id
    $current_user_id = wp_get_current_user()->ID;

    // var_dump($current_user_id);
    if ($current_user_id) {

        // var_dump($user_id_who_buy_membership);
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
        $price = $zoom_product_price * 0;


        // get yith membership plan lists
        $membership_post_plan_args = array(
            'numberposts' => -1,
            'post_type' => 'yith-wcmbs-plan',
        );

        // get yith membership post type full row
        $membership_posts_plan = get_posts($membership_post_plan_args);


        $membership_plan_arr = [];
        $membership_total_credit_arr = [];

        foreach ($membership_posts_plan as $membership_post_plan_info) {

            $membership_post_plan_id =  $membership_post_plan_info->ID;
            $membership_post_post_status =  $membership_post_plan_info->post_title;


            $membership_total_credit = get_post_meta($membership_post_plan_id, '_download-limit')[0];
            // var_dump($membership_total_credit);
            array_push($membership_total_credit_arr, $membership_total_credit);
            array_push($membership_plan_arr, $membership_post_post_status);
            // array_push($membership_plan_arr, $membership_post_post_status);
        };
        // var_dump($membership_plan_arr);


        // get yith membership buy by users
        $membership_post_args = array(
            'numberposts' => -1,
            'post_type' => 'ywcmbs-membership',

        );

        // get yith membership post type full row
        $membership_posts = get_posts($membership_post_args);

        // var_dump($membership_posts);
        foreach ($membership_posts as $membership_post) {

            $user_id_who_buy_membership = intval($membership_post->post_author);
            $membership_post_title = $membership_post->post_title;

            // var_dump($user_id_who_buy_membership);

            if ($user_id_who_buy_membership == $current_user_id) {
                $membership_post_id = $membership_post->ID;

                $user_membership_package_title = $membership_post->post_title;

                if ($membership_post_title == $user_membership_package_title) {

                    //get array key by the package name
                    $membership_plan_array_key_number_check_on_credit_arr = array_search($membership_post_title, $membership_plan_arr);

                    $users_packages_total_credit = intval($membership_total_credit_arr[$membership_plan_array_key_number_check_on_credit_arr]);
                    var_dump($users_packages_total_credit);
                }

                // get all orders infos of current user
                $customers_orders_infos = wc_get_orders(array(
                    'numberposts' => -1,
                    'status' => 'completed',
                    'author' => $current_user_id
                ));

                // Loop through each WC_Order object
                $users_product_id_array = [];

                foreach ($customers_orders_infos as $customers_order_info) {

                    // get customers order ids
                    $customers_order_id = intval($customers_order_info->get_id());

                    // The order status
                    $customers_order_status = $customers_order_info->get_status();

                    // get product id based on order ids
                    $order_product_lookup = "SELECT `product_id` FROM `wp_wc_order_product_lookup` WHERE `order_id` = $customers_order_id";
                    $users_product_ids_based_on_order_id = $wpdb->get_results($order_product_lookup);

                    // echo $customers_order_id . '-';
                    $users_product_id = $users_product_ids_based_on_order_id[0]->product_id;

                    // storing product ids to an array
                    array_push($users_product_id_array, $users_product_id);
                }


                // count the specific product in an array
                $total_users_bought_product_zoom = array_count_values($users_product_id_array);
                $zoom_product_id_frequency = $total_users_bought_product_zoom[$zoom_product_id];


                // get total credit from array zero index 
                $users_credit_info = intval(get_post_meta($membership_post_id, '_credits')[0]);

                // users credit reduction value
                $users_credit_reduction_on_zoom_product_purchase = 35;

                // credit will be deducted on users zoom appoinment purchase
                $users_credit_info_num  = $users_packages_total_credit - ($zoom_product_id_frequency * $users_credit_reduction_on_zoom_product_purchase);
                var_dump($users_credit_info_num);
                // var_dump($users_credit_info_num);
                update_post_meta($membership_post_id, '_credits', $users_credit_info_num);
            }
        }
        return $price;
    } else {
        return $price;
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
        <div class="yith-wcmbs-membership-detail yith-wcmbs-membership-detail--next-credits-update">
            <div class="yith-wcmbs-membership-detail__title">Next credits update</div>
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
