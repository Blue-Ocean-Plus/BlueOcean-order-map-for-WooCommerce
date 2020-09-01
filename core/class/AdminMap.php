<?php

namespace BlueOcean\WooCommerceOrderMap;

if (!defined('ABSPATH')) exit; // No direct access allowed

/**
 * AdminMap class.
 *
 * @since 1.0.0
 */
class AdminMap extends Autoload
{

    static function init()
    {
        add_action('add_meta_boxes', 'BlueOcean\WooCommerceOrderMap\AdminMap::meta_box');
    }

    public static function meta_box()
    {
        add_meta_box('widget-blue-ocean-map', __('Address on the map', 'bo_woo_order_map'), 'BlueOcean\WooCommerceOrderMap\AdminMap::widget', 'shop_order', 'side', 'default');
    }

    private static function script($map)
    {
        $map = explode('_', $map);
        ?>
        <script>
            jQuery(document).ready(function () {
                let location = {
                    lat:<?=$map[0]?>,
                    lng:<?=$map[1]?>,
                    zoom:<?=$map[2]?>
                };

                let app = L.map('blue_ocean_map_c_load',
                    {
                        attributionControl: false,
                        trackResize: true,
                    }).setView([ <?=$map[0]?>, <?=$map[1]?>], <?=$map[2]?>);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 16
                }).addTo(app);

                L.marker([<?=$map[0]?>, <?=$map[1]?>], {
                    icon: L.icon({
                        iconUrl: "<?= plugins_url('assets/images/icon-marker-map.svg', BO_WOO_ORDER_MAP) ?>",
                        iconSize: [40, 40], // size of the icon
                        iconAnchor: [20, 40], // point of the icon which will correspond to marker's location
                    })
                }).addTo(app);

                // full screen map
                document.querySelector('span.full-screen').addEventListener('click', (e) => {

                    if (document.getElementById('blue_ocean_map_c').getAttribute('class') === 'fullscreen-map') {
                        document.querySelector('.content-box-blue-ocean-map').setAttribute('class', 'content-box-blue-ocean-map');
                        document.getElementById('blue_ocean_map_c').setAttribute('class', ' ');
                        e.target.innerHTML = '<?=__('FullScreen', 'bo_woo_order_map')?>';
                    } else {
                        document.querySelector('.content-box-blue-ocean-map').setAttribute('class', 'fullscreen-map content-box-blue-ocean-map');
                        document.getElementById('blue_ocean_map_c').setAttribute('class', 'fullscreen-map');
                        e.target.innerHTML = '<?=__('Close', 'bo_woo_order_map')?>';
                    }
                    app._onResize();
                    setTimeout(() => app.setView([location.lat, location.lng], location.zoom), 300)
                });

                const close = document.querySelectorAll('.close-map,.close-map-bg');

                for (let i = 0; i < close.length; i++) {
                    close[i].addEventListener("click", function () {
                        document.querySelector('.main-box-blue-ocean-map').style.display = 'none';
                    });
                }

                document.querySelector('.print-map').addEventListener('click', () => {
                    window.print();
                });
                document.querySelector('span.share-map').addEventListener('click', () => {
                    document.querySelector('input.share-map').select();

                    document.execCommand("copy") ?
                        alert('<?=__('The link was successfully copied to your clipboard', 'bo_woo_order_map')?>')
                        :
                        alert('<?=__('The operation encountered an error', 'bo_woo_order_map')?>');

                });
                document.querySelector('.view-in-blue-ocean-map').addEventListener('click', () => {
                    document.querySelector('.main-box-blue-ocean-map').style.display = 'flex';
                    if (location.hasOwnProperty('lat'))
                        setTimeout(() => app.setView([location.lat, location.lng], location.zoom), 300);
                    setTimeout(() => app._onResize(), 300)

                })
            });

        </script>
        <?php
    }

    private static function html($map)
    {
        ?>
        <ul class="table-info-blue-ocean-map">
            <li class="view-in-blue-ocean-map <?= $map ? 'active' : 'deactive' ?>">
                <?= $map ? __('View map', 'bo_woo_order_map') : __('No maps selected', 'bo_woo_order_map') ?>
            </li>
        </ul>
        <?php if ($map)
        $map = explode('_', $map);
        { ?>
            <div class="main-box-blue-ocean-map">
                <div class="close-map-bg"></div>
                <div class="content-box-blue-ocean-map">
                    <input type="text" class="share-map"
                           value="<?= "https://maps.google.com/maps?q={$map[0]}%2C{$map[1]}&z=17&hl=en" ?>">
                    <div class="tools-box-map">
                        <div>
                            <span class="close-map"><?= __('Close', 'bo_woo_order_map') ?></span>
                            <span class="print-map"><?= __('Print', 'bo_woo_order_map') ?></span>
                            <span class="share-map"><?= __('Share', 'bo_woo_order_map') ?></span>
                        </div>
                        <div>
                            <span class="full-screen"><?= __('Full Screen', 'bo_woo_order_map') ?></span>
                        </div>
                    </div>
                    <div id="blue_ocean_map_c">
                        <div id="blue_ocean_map_c_load" class="w-blue-ocean-map"></div>
                    </div>
                </div>
            </div>
            <?php
            // load styles
            wp_print_styles(['bo-woo-order-map-leaflet']);

            // load javascript
            wp_print_scripts('bo-woo-order-map-site');
        }

    }

    public static function widget($order)
    {
        $map = get_post_meta($order->ID, 'blue_ocean_map', true);

        self::html($map);

        self::script($map);
    }
}

