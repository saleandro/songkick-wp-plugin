<?php

/*
Plugin Name: Songkick Concerts and Festivals
Plugin URI: http://github.com/saleandro/songkick-wp-plugin
Description: Plugin to show concerts based on your Songkick profile. It can display upcoming events for a user, an artist, venue, or metro area/location.
It can also display past events for users and artists. For a user, simply put your username in the admin interface. For an artist, you should use the artist's Songkick id, as shown in the url for your artist page.
For example, the url "http://www.songkick.com/artists/123-your-name" has the id "123". The same goes for metro areas or venues: "http://www.songkick.com/venues/123-venue-name" and "http://www.songkick.com/metro_areas/123-city-name" both have the id "123".
Version: 0.9.4.4
Author: Sabrina Leandro
Author URI: http://github.com/saleandro
License: GPL3

*/

/*
    Copyright 2012 Sabrina Leandro (saleandro@yahoo.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// For debugging:
// error_reporting(E_ALL);
// if ( !defined('WP_DEBUG') )
//     define('WP_DEBUG', true);
// @ini_set('display_errors','On');

defined('ABSPATH') or die("Cannot access pages directly.");
defined("DS") or define("DS", DIRECTORY_SEPARATOR);

if (!class_exists('WP_Http'))
    include_once(ABSPATH . WPINC . '/class-http.php');

define('SONGKICK_OPTIONS',       'songkick-concerts');
define('SONGKICK_TEXT_DOMAIN',   'songkick-concerts-and-festivals');
define('SONGKICK_I18N_ENCODING', 'UTF-8');
define('SONGKICK_CACHE',         'songkick-concerts-cache');
define('SONGKICK_REFRESH_CACHE', 60 * 60);

require_once dirname(__FILE__) . '/songkick_presentable_events.php';
require_once dirname(__FILE__) . '/songkick_settings.php';
require_once dirname(__FILE__) . '/songkick_widget.php';

/**
 * Global Initialization of the Songkick Plugin
 */
function songkick_plugin_init() {
    // Load Plugin Text Domain for i18n
    load_plugin_textdomain(SONGKICK_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action('init', 'songkick_plugin_init');

function songkick_concerts_and_festivals_shortcode_handler($options = null) {
    try {
        wp_enqueue_style('songkick_concerts', '/wp-content/plugins/songkick-concerts-and-festivals/songkick_concerts.css') ;

        $default_options = get_option(SONGKICK_OPTIONS);
        if ($default_options) {
            $default_options['logo']             = $default_options['shortcode_logo'];
            $default_options['date_color']       = $default_options['shortcode_date_color'];
            $default_options['number_of_events'] = $default_options['shortcode_number_of_events'];
        } else {
            $default_options = array();
        }
        if (is_array($options)) {
            $options = array_merge($default_options, $options);
        } else {
            $options = $default_options;
        }

        if (!isset($options['show_pagination'])) $options['show_pagination'] = false;
        if ($options['show_pagination'] && isset($_GET['skp']))
            $options['page'] = $_GET['skp'];

        $sk = new SongkickPresentableEvents($options);
        $str = '<div class="songkick-events">';
        $str .= $sk->to_html();
        $str .= '</div>';
        return $str;
    } catch (Exception $e) {
        $msg = 'Error on ' . get_bloginfo('url') . ' while trying to display Songkick Concerts plugin: ' . $e->getMessage();
        error_log($msg, 0);
        return '';
    }
}

add_action('admin_menu', 'songkick_admin_menu');
function songkick_admin_menu() {
    add_options_page('Songkick Concerts and Festivals', 'Songkick', 'administrator', 'songkick-concerts-and-festivals', 'songkick_admin_settings');
}
add_shortcode("songkick_concerts_and_festivals", "songkick_concerts_and_festivals_shortcode_handler");

?>
