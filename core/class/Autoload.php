<?php

namespace BlueOcean\WooCommerceOrderMap;

use CSF;

if (!defined('ABSPATH')) exit; // No direct access allowed

/**
 * Autoloader class.
 *
 * @since 1.0.0
 */
class Autoload
{
    protected static $prefix = 'bo_woo_order_map', $slug;

    static function init()
    {
        // includes files
        self::includes();

        add_action('plugins_loaded', 'BlueOcean\WooCommerceOrderMap\Autoload::language');
        add_action('plugins_loaded', 'BlueOcean\WooCommerceOrderMap\Autoload::admin_page');
        add_action('admin_enqueue_scripts', 'BlueOcean\WooCommerceOrderMap\Autoload::admin_enqueue', 20, 1);
        add_action('admin_enqueue_scripts', 'BlueOcean\WooCommerceOrderMap\Autoload::wp_enqueue_scripts', 20, 1);
        add_action('wp_enqueue_scripts', 'BlueOcean\WooCommerceOrderMap\Autoload::wp_enqueue_scripts', 20, 1);

        if (self::get_option('active'))
            Map::init();

        AdminMap::init();
    }

    static private function includes()
    {
        include(plugin_dir_path(BO_WOO_ORDER_MAP) . '/core/class/Map.php');

        include(plugin_dir_path(BO_WOO_ORDER_MAP) . '/core/class/AdminMap.php');

        // load global functions
        include(plugin_dir_path(BO_WOO_ORDER_MAP) . '/core/functions/global.php');

    }

    static function language()
    {
        load_plugin_textdomain('bo_woo_order_map', false, dirname(plugin_basename(BO_WOO_ORDER_MAP)) . '/languages');
    }

    static function admin_enqueue()
    {
        // Set Info Plugin
        $plugin_info = get_plugin_data(BO_WOO_ORDER_MAP);

        /**
         * Add Style Admin
         */
        wp_enqueue_style('bo_woo_order_map', plugins_url('assets/css/admin/core.css', BO_WOO_ORDER_MAP), array(), $plugin_info['Version']);

        /**
         * Add javascript Admin
         */
        wp_enqueue_script('bo_woo_order_map-js', plugins_url('/assets/js/admin.js', BO_WOO_ORDER_MAP), [], $plugin_info['Version']);

    }

    static function wp_enqueue_scripts()
    {
        // Set Info Plugin

        wp_register_style('bo-woo-order-map-leaflet', plugins_url('assets/lib/leaflet/leaflet.css', BO_WOO_ORDER_MAP), []);

        wp_register_script('bo-woo-order-map-site', plugins_url('assets/lib/leaflet/leaflet.js', BO_WOO_ORDER_MAP), ['jquery']);

        wp_register_style('bo-woo-order-map-site', plugins_url('assets/css/site/core.css', BO_WOO_ORDER_MAP), []);

    }

    static function get_option($name)
    {
        $options = get_option(self::$prefix); // unique id of the framework

        if (isset($options[$name]))
            return $options[$name];

        return '';
    }

    static function admin_page()
    {
        // load CSF
        include(plugin_dir_path(BO_WOO_ORDER_MAP) . '/lib/codestar-framework/classes/setup.class.php');

        // Control core classes for avoid errors
        if (!class_exists('CSF')) {
            add_action('admin_notices', function () {
                self::alert(__('ERROR CLASS EXIST CSF', 'bo_woo_order_map'));
            });
            return;
        }


        // Set Admin Option
        self::set_plugin_info();
    }

    static function set_plugin_info()
    {
        CSF::createOptions(self::$prefix, array(
            'menu_title' => __('Woo Order Map', 'bo_woo_order_map'),
            'framework_title' => "<img src='" . plugins_url('assets/images/logo.svg', BO_WOO_ORDER_MAP) . "' alt=''/>" . __('Woo Order Map', 'bo_woo_order_map'),
            'menu_slug' => self::$prefix,
            'menu_type' => self::get_option('submenu') ? 'submenu' : 'menu',
            'menu_parent' => self::get_option('submenu') ? 'options-general.php' : self::$prefix,
            'menu_icon' => plugins_url('assets/images/icon.svg', BO_WOO_ORDER_MAP),
            'show_bar_menu' => false,
            'theme' => 'light',
            'footer_credit' => ' ',
            'class' => 'bo_woo_order_map'
        ));

        // Load Section Panel Admin
        Autoload::createSection();

    }

    static function createSection()
    {

        CSF::createSection(self::$prefix, array(
            'title' => __('Main settings', 'bo_woo_order_map'),
            'menu_hidden' => true,
            'fields' => array(
                array(
                    'id' => 'active',
                    'type' => 'checkbox',
                    'title' => __('Activation', 'bo_woo_order_map'),
                ),
                array(
                    'id' => 'required',
                    'type' => 'checkbox',
                    'title' => __('Required', 'bo_woo_order_map'),
                ),
                array(
                    'id' => 'default',
                    'type' => 'map',
                    'settings' => array(
                        'scrollWheelZoom' => true,
                    ),
                    'title' => __('Default Location', 'bo_woo_order_map'),
                ),
            )
        ));
        CSF::createSection(self::$prefix, array(
            'title' => __('Other Settings', 'bo_woo_order_map'),
            'menu_hidden' => true,
            'fields' => array(
                // slug
                array(
                    'id' => 'submenu',
                    'type' => 'checkbox',
                    'title' => __('Move to Settings submenu', 'bo_woo_order_map'),
                    'default' => 'panel',
                ),
            )
        ));
    }

    public static function alert($msg, $type = 'error')
    {
        ?>
        <div class="notice notice-<?= $type ?> is-dismissible" style="display: block!important">
            <p><?= $msg ?></p>
        </div>
        <?php
    }
}
