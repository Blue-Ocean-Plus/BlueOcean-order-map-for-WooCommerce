<?php

namespace BlueOcean\WooCommerceOrderMap;

if (!defined('ABSPATH')) exit; // No direct access allowed

/**
 * Map class.
 *
 * @since 1.0.0
 */
class Map extends Autoload
{

    static function init()
    {

        add_action('woocommerce_before_order_notes', 'BlueOcean\WooCommerceOrderMap\Map::map_checkout_field');

    }

    private static function get_default_location()
    {
        $map = self::get_option('default');

        $default = ['lat' => 40.713955826286046, 'lng' => 0.17578125, 'zoom' => 1];

        $user_data = get_user_meta(get_current_user_id(), 'blue_ocean_map', true);

        if ($user_data != '') {
            $data = explode('-', $user_data);
            $default = ['lat' => $data[0], 'lng' => $data[1], 'zoom' => $data[2]];
        } elseif (isset($map['default']['latitude']) && $map['default']['latitude'] != '') {
            $default = ['lat' => $map['default']['latitude'], 'lng' => $map['default']['longitude'], 'zoom' => $map['default']['zoom']];
        }

        return $default;
    }

    private static function script_map()
    {
        $default = self::get_default_location();
        ?>
        <script>
            jQuery(document).ready(function () {
                let location, marker = undefined;

                let app = L.map('bo_woo_order_map_c_load',
                    {
                        attributionControl: false,
                        trackResize: true,
                    }).setView([ <?=$default['lat']?>, <?=$default['lng']?>], <?=$default['zoom']?>);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(app);

                app.on('click', function (e) {
                    location = {
                        lat: e.latlng.lat,
                        lng: e.latlng.lng,
                        zoom: e.target._zoom
                    };

                    if (marker !== undefined)
                        marker.remove();

                    marker = L.marker([location.lat, location.lng], {
                        icon: L.icon({
                            iconUrl: "<?= plugins_url('assets/images/icon-marker-map.svg', BO_WOO_ORDER_MAP) ?>",
                            iconSize: [40, 40], // size of the icon
                            iconAnchor: [20, 40], // point of the icon which will correspond to marker's location
                        })
                    }).addTo(app);


                    app.setView([location.lat, location.lng], location.zoom);

                    document.querySelector('input#bo_woo_order_map').value = `${location.lat}-${location.lng}-${location.zoom}`;

                });

                // full screen map
                document.querySelector('button.fullscreen_bo_woo_order_map').addEventListener('click', (e) => {

                    if (document.getElementById('bo_woo_order_map_c').getAttribute('class') === 'fullscreen-map') {
                        document.getElementById('bo_woo_order_map_c').setAttribute('class', ' ');
                        e.target.innerHTML = '<?=__('FullScreen', 'bo_woo_order_map')?>';
                    } else {
                        document.getElementById('bo_woo_order_map_c').setAttribute('class', 'fullscreen-map');
                        e.target.innerHTML = '<?=__('Close', 'bo_woo_order_map')?>';
                    }
                    app._onResize();
                    setTimeout(() => app.setView([location.lat, location.lng], location.zoom), 300)
                });
            });
        </script>
        <?php
    }

    private static function html($checkout)
    {
        ?>
        <div id="bo_woo_order_map_main">

            <?php
            woocommerce_form_field('bo_woo_order_map', array(
                'type' => 'text',
                'class' => array(
                    'bo-woo-order-map-class form-row-wide'
                ),
                'label' => __('Order delivery address on the map', 'bo_woo_order_map'),
                'placeholder' => '',
                'required' => true,
            ), $checkout->get_value('bo_woo_order_map'));
            ?>
            <div id="bo_woo_order_map_c">
                <button class="fullscreen_bo_woo_order_map" type="button"><?=__('FullScreen', 'bo_woo_order_map')?></button>
                <div id="bo_woo_order_map_c_load"></div>
            </div>

        </div>
        <?php
    }

    public static function map_checkout_field($checkout)
    {
        // load styles
        wp_print_styles(['bo-woo-order-map-leaflet','bo-woo-order-map-site']);

        // load javascript
        wp_print_scripts('bo-woo-order-map-site');

        // load html
        self::html($checkout);

        // load script
        self::script_map();

    }
}

