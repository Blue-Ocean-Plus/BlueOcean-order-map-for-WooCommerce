<?php

namespace BlueOcean\WooCommerceOrderMap;

if (!defined('ABSPATH')) exit; // No direct access allowed

/**
 * Tracking class.
 *
 * @since 1.0.0
 */
class Tracking extends Autoload
{

    static function init()
    {
        $prefix = self::$prefix;

        if (!self::get_option('active') && empty(self::get_option('tracking')))
            add_action('admin_notices', 'BlueOcean\WooCommerceOrderMap\Tracking::notices');

        add_action("tracking-scheduled-{$prefix}", 'BlueOcean\WooCommerceOrderMap\Tracking::Tracking');

        register_activation_hook(BO_WOO_ORDER_MAP, 'BlueOcean\WooCommerceOrderMap\Tracking::RegisterHook');
        register_deactivation_hook(BO_WOO_ORDER_MAP, 'BlueOcean\WooCommerceOrderMap\Tracking::DeactivationHook');

    }

    public static function DeactivationHook()
    {
        $prefix = self::$prefix;

        wp_clear_scheduled_hook("tracking-scheduled-{$prefix}");
    }

    public static function RegisterHook()
    {
        $prefix = self::$prefix;

        if (!wp_next_scheduled("tracking-scheduled-{$prefix}")) {
            wp_schedule_event(time(), 'daily', "tracking-scheduled-{$prefix}");
        }
    }

    private static function ActiveDeActive()
    {

        $prefix = self::$prefix;
        $option = self::get_option();
        if (isset($_GET["allow-tracking-{$prefix}"])) {

            $option['tracking'] = true;
            if (get_option(self::$prefix) !== false) {
                update_option(self::$prefix, $option);
            } else {
                add_option(self::$prefix, $option);
            }

            self::send_request('scheduled');
        }

        if (isset($_GET["disallow-tracking-{$prefix}"])) {

            $option['tracking'] = false;
            if (get_option(self::$prefix) !== false) {
                update_option(self::$prefix, $option);
            } else {
                add_option(self::$prefix, $option);
            }

        }

    }

    public static function notices()
    {
        self::ActiveDeActive();

        if (self::get_option('tracking') === false || self::get_option('tracking') === true)
            return null;

        $plugin_info = get_plugin_data(BO_WOO_ORDER_MAP);

        $prefix = self::$prefix;

        $url = explode("?", $_SERVER['REQUEST_URI']);


        if (!isset($url[1])) {
            $AllowUrl = "?allow-tracking-{$prefix}=1";
            $DisAllowUrl = "?disallow-tracking-{$prefix}=1";
        } else {
            $AllowUrl = "?{$url[1]}&allow-tracking-{$prefix}=1";
            $DisAllowUrl = "?{$url[1]}&disallow-tracking-{$prefix}=1";
        }

        ?>
        <div class="updated blue-ocean">
            <p>
                <?php
                printf(__('Want to help make <strong>%1$s</strong> even more awesome?
                Allow %2$s to collect non-sensitive diagnostic data and usage information. (<a
                        class="insights-data-we-collect" href="#">what we collect</a>)', $plugin_info['TextDomain']),
                    $plugin_info['Name'], $plugin_info['Name'])
                ?>
            </p>
            <p class="description" style="display: none;">
                <?= __('Server environment details (php, mysql, server, WordPress
                versions), Number of users in your site, Site language, Number of active and inactive plugins, Site name
                and url, Your name and email address. No sensitive data is tracked.', $plugin_info['TextDomain']) ?>
            </p>
            <p>

            </p>
            <p class="submit">
                <a href="<?= $AllowUrl ?>"
                   class="button-primary button-large"><?= __('Allow', $plugin_info['TextDomain']) ?></a>
                <a href="<?= $DisAllowUrl ?>"
                   class="button-secondary button-large"><?= __('No thanks', $plugin_info['TextDomain']) ?></a>
            </p>
        </div>
        <?php
    }

    private static function mysql_ver()
    {
        global $wpdb;

        if (isset($wpdb->use_mysqli) && $wpdb->use_mysqli) {
            $mysql = $wpdb->dbh->client_info;
        } else {
            // phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysql_get_client_info,PHPCompatibility.Extensions.RemovedExtensions.mysql_DeprecatedRemoved
            if (preg_match('|[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}|', mysql_get_client_info(), $matches)) {
                $mysql = $matches[0];
            } else {
                $mysql = null;
            }
        }
        return $mysql;
    }

    private static function theme_info()
    {
        $active_theme = wp_get_theme();

        return [
            'name' => sprintf(
            /* translators: 1: Theme name. 2: Theme slug. */
                __('%1$s (%2$s)'),
                $active_theme->name,
                $active_theme->stylesheet
            ),
            'version' => $active_theme->version
        ];
    }

    private static function plugins()
    {
        $plugins = [];

        foreach (get_plugins() as $plugin_path => $plugin)
            $plugins[(is_plugin_active($plugin_path)) ? 'active' : 'inactive'][] = [
                'name' => $plugin['Name'],
                'version' => $plugin['Version'],
            ];

        return $plugins;
    }

    private static function InitData($status)
    {
        global $wpdb;

        if (!function_exists('get_plugin_data')) {
            /** @noinspection PhpIncludeInspection */
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }

        $plugin_info = get_plugin_data(BO_WOO_ORDER_MAP);

        return [
            'product' => $plugin_info['Name'],
            'version' => $plugin_info['Version'],
            'wordpress' => get_bloginfo('version'),
            'language' => get_locale(),
            'site_url' => parse_url(home_url())['host'],
            'site_name' => get_bloginfo('name'),
            'site_desc' => get_bloginfo('description'),
            'ssl' => is_ssl(),
            'email' => get_bloginfo('admin_email'),
            'php' => function_exists('phpversion') ? phpversion() : __('Unable to determine PHP version'),
            'linux' => function_exists('php_uname') ? sprintf('%s %s %s', php_uname('s'), php_uname('r'), php_uname('m')) : 'unknown',
            'web_service' => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : __('Unable to determine what web server software is used')),
            'server' => $wpdb->get_var('SELECT VERSION()'),
            'mysql' => self::mysql_ver(),
            'users' => count_users()['total_users'],
            'theme' => json_encode(self::theme_info()),
            'plugins' => json_encode(self::plugins()),
            'tracking' => $status,
        ];
    }

    private static function send_request($status)
    {

        $url = "https://tracking.blueocean.plus/stats.php";

        return wp_remote_post($url, array(
            'method' => 'POST',
            'timeout' => 5,
            'redirection' => 5,
            'httpversion' => '1.0',
            'headers' => [],
            'body' => self::InitData($status),
            'cookies' => array()
        ));
    }

    public static function Tracking()
    {
        $option = self::get_option();

        if ((isset($option['tracking']) && !$option['tracking']))
            return null;

        self::send_request('scheduled');
    }
}

