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
function reservation_endpoint_contents() { ?>
    <div>
        hello from reservation page
    </div>


<?php }
