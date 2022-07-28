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



// zoom conditional product price zero
function zoom_product_price_reduction() {

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

    return  array($price, $zoom_product_id);
}





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

    // get current users id
    $current_user_id = wp_get_current_user()->ID;


    //membership plans lists function
    $membership_plan_lists = get_membership_plans_lists();
    $membership_plan_arr = $membership_plan_lists[0];
    $membership_total_credit_arr = $membership_plan_lists[1];


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

            // users available credits 
            $users_credit_info = intval(get_post_meta($membership_post_id, '_credits')[0]);
        }
    }



    // global $users_credit_info_num;


?>
    <div class="yith-wcmbs-membership-details">
        <div class="yith-wcmbs-membership-detail yith-wcmbs-membership-detail--next-credits-update">
            <div class="yith-wcmbs-membership-detail__title">Available Credits</div>
            <div class="yith-wcmbs-membership-detail__value"><?php echo $users_credit_info; ?></div>
        </div>

    </div>

    <?php
}



//conditional add to cart option hide
function specific_product_add_to_cart_hide() {

    // product id to add functionality
    $product_id_array = [187, 185];

    foreach ($product_id_array as $single_product_item_to_hide) {
        global $post;
        $current_post_url_id = $post->ID;
        if ($current_post_url_id  == $single_product_item_to_hide) {
    ?>
            <style>
                .elementor-add-to-cart.elementor-product-simple .cart {
                    display: none !important;
                }
            </style>

<?php
        }
    }
}

add_action('woocommerce_before_add_to_cart_form', 'specific_product_add_to_cart_hide');





/**
 * Register a custom menu page.
 */
function add_menu_to_wp_dashboard() {
    add_menu_page(
        __('Membership Zoom', 'zoomwooreservation'),
        'Membership Zoom',
        'manage_options',
        'membership-zoom', // slug
        'membership_zoom_cb', // callback
        'dashicons-controls-repeat',
        45
    );
}
add_action('admin_menu', 'add_menu_to_wp_dashboard');


function membership_zoom_cb() {
    echo 'hello';
}
