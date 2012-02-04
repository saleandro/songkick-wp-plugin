<?php

/*
Plugin Name: Songkick Concerts and Festivals
Plugin URI: http://github.com/saleandro/songkick-wp-plugin
Description: Plugin to show upcoming concerts based on your Songkick profile. It can display upcoming events for a user, an artist, venue, or metro area/location.
For a user, simply put your username in the admin interface. For an artist, you should use the artist's Songkick id, as shown in the url for your artist page.
For example, the url "http://www.songkick.com/artists/123-your-name" has the id "123". The same goes for metro areas or venues: "http://www.songkick.com/venues/123-venue-name" and "http://www.songkick.com/metro_areas/123-city-name" both have the id "123".
You can also specify different user, artist, venue, or metro area ids when using the shortcode function.
Version: 0.9.3
Author: Sabrina Leandro
Author URI: http://github.com/saleandro
License: GPL3

*/

/*
    Copyright 2010 Sabrina Leandro (saleandro@yahoo.com)

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
error_reporting(E_ALL);
if ( !defined('WP_DEBUG') )
    define('WP_DEBUG', true);
@ini_set('display_errors','On');

if (!class_exists('WP_Http'))
    include_once(ABSPATH . WPINC . '/class-http.php');

define('SONGKICK_OPTIONS',       'songkick-concerts');
define('SONGKICK_TEXT_DOMAIN',   'songkick-concerts-and-festivals');
define('SONGKICK_I18N_ENCODING', 'UTF-8');
define('SONGKICK_CACHE',         'songkick-concerts-cache');
define('SONGKICK_REFRESH_CACHE', 60 * 60);

require_once dirname(__FILE__) . '/songkick_presentable_events.php';
require_once dirname(__FILE__) . '/songkick_settings.php';

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
        if (is_array($options)) {
            $options = array_merge($default_options, $options);
        } else {
            $options = $default_options;
        }
        $options['logo']             = $options['shortcode_logo'];
        $options['date_color']       = $options['shortcode_date_color'];
        $options['number_of_events'] = $options['shortcode_number_of_events'];

        if (!isset($options['show_pagination'])) $options['show_pagination'] = true;        
        if ($options['show_pagination'] && isset($_GET['skp']))
            $options['page'] = $_GET['skp'];

        $sk = new SongkickPresentableEvents($options);
        $str = '<div class="songkick-events">';
        $str .= $sk->to_html();
        $str .= '</div>';
        return $str;
    } catch (Exception $e) {
        $msg = 'Error on '.get_bloginfo('url').' while trying to display Songkick Concerts plugin: '. $e->getMessage();
        error_log($msg, 0);
    }
}

/**
 * Global Initialization of the Songkick Sidebar Widget
 */
function songkick_widget_init() {
    if (!function_exists('register_sidebar_widget'))
        return;

    wp_enqueue_style('songkick_concerts', '/wp-content/plugins/songkick-concerts-and-festivals/songkick_concerts.css') ;

    function songkick_widget($args) {
        try {
            extract($args);

            $options       = get_option(SONGKICK_OPTIONS);
            $hide_if_empty = $options['hide_if_empty'];

            $sk = new SongkickPresentableEvents($options);

            if ($hide_if_empty && $sk->no_events()) return;

            $options = get_option(SONGKICK_OPTIONS);
            $title = $options['title'];
            if (!$title || $title == '') {
                $title = __('Concerts', SONGKICK_TEXT_DOMAIN);
            }
            $title = htmlentities($title, ENT_QUOTES, SONGKICK_I18N_ENCODING);

            echo $before_widget;
            echo '<div class="songkick-events">';
            echo $before_title . $title . $after_title;
            echo $sk->to_html();
            echo '</div>';
            echo $after_widget;
        } catch (Exception $e) {
            $msg = 'Error on '.get_bloginfo('url').' while trying to display Songkick Concerts plugin: '. $e->getMessage();
            error_log($msg, 0);
        }
    }

    register_sidebar_widget(array('Songkick Concerts and Festivals', 'widgets'), 'songkick_widget');
    register_widget_control(array('Songkick Concerts and Festivals', 'widgets'), 'songkick_widget_settings');
}

add_action('admin_menu', 'songkick_admin_menu');

function songkick_admin_menu() {
    add_options_page('Songkick Concerts and Festivals', 'Songkick', 'administrator', 'songkick-concerts-and-festivals', 'songkick_admin_settings');
}

add_shortcode("songkick_concerts_and_festivals", "songkick_concerts_and_festivals_shortcode_handler");
add_action('widgets_init', 'songkick_widget_init');

?>
