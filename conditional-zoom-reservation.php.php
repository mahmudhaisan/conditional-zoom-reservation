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




/*
 * Step 1. Add Link (Tab) to My Account menu
 */
add_filter('woocommerce_account_menu_items', 'reservation_add_links_account_page', 40);
function reservation_add_links_account_page($menu_links) {
    $menu_links = array_slice($menu_links, 0, 2, true)
        + array('reservation-info' => 'Reservation')
        + array_slice($menu_links, 2, NULL, true);
    return $menu_links;
}


/*
 * Step 2. Register Permalink Endpoint
 */
add_action('init', 'reservation_endpoints');
function reservation_endpoints() {

    add_rewrite_endpoint('reservation-info', EP_PAGES);
}



/*
 * Step 3. Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
 */
add_action('woocommerce_account_reservation-info_endpoint', 'reservation_endpoint_contents');
function reservation_endpoint_contents($price) {

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

        $membership_author_id = $membership_post->post_author;
        if ($membership_author_id == $current_user_id) {
            $membership_post_id = $membership_post->ID;
            $membership_status_field = $membership_post->post_name;
        }
    }

    // 1. Decrease cradit on user appointment purchase. 
    // 2. Give warning if user don't have cradit. User will not be able to purchase.
    // 3. Give backend option to control credit. You can use product custom field.

    if (isset($current_user_id)) {

        // product slug
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
        $price = 0;

        return $price;

        // $members_activities = get_post_meta($membership_post_id, '_activities');
    }
}

add_filter('woocommerce_get_price', 'reservation_endpoint_contents', 10, 2);


// $membership_post_meta = get_post_meta(, '_title');
