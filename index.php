<?php
/**
 * Plugin Name: BlueOcean woo order map
 * Plugin URI: https://wordpress.org/plugins/blueocean-woo-order-map
 * Description: BlueOcean woocommerce order map
 * Version: 1.0
 * Author: blueocean.plus
 * Author URI: http://blueocean.plus
 * Text Domain: bo_woo_order_map
 * Domain Path: /languages
 * License: GNU v2
 * Requires PHP: 5.6
 **/
if (!defined('ABSPATH')) exit; // No direct access allowed

define('BO_WOO_ORDER_MAP', __FILE__);


/**
 * Load Core Class
 */
if (!class_exists('wp_panel\Autoload', false))
    include(plugin_dir_path(__FILE__) . '/core/class/Autoload.php');

BlueOcean\WooCommerceOrderMap\Autoload::init();
