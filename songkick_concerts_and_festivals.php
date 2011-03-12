<?php

/*
Plugin Name: Songkick Concerts and Festivals
Plugin URI: http://github.com/saleandro/songkick-wp-plugin
Description: Widget to show your upcoming concerts based on your Songkick profile. It can display upcoming events for a user or an artist.
For a user, simply put your username in the admin interface. For an artist, you should use the artist's Songkick id, as shown in the url for your artist page.
For example, the url "http://www.songkick.com/artists/123-your-name" has the id "123".
Version: 0.4
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

if (!class_exists('WP_Http'))
	include_once(ABSPATH . WPINC . '/class-http.php');

define('SONGKICK_OPTIONS', 'songkick-concerts');
define('SONGKICK_CACHE', 'songkick-concerts-cache');
define('SONGKICK_REFRESH_CACHE', 60 * 60);
define('SONGKICK_TEXT_DOMAIN', 'songkick-concerts-and-festivals');
define('SONGKICK_I18N_ENCODING', 'UTF-8');

require_once dirname(__FILE__) . '/songkick_user_events.php';
require_once dirname(__FILE__) . '/songkick_artist_events.php';

/**
 * Global Initialization of the Songkick Plugin
 */
function songkick_plugin_init() {
	// Load Plugin Text Domain for i18n
	load_plugin_textdomain(SONGKICK_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action('init', 'songkick_plugin_init');

/**
 * Global Initialization of the Songkick Sidebar Widget
 */
function songkick_widget_init() {
	if (!function_exists('register_sidebar_widget'))
		return;

	wp_enqueue_style('songkick_concerts', '/wp-content/plugins/songkick-concerts-and-festivals/songkick_concerts.css') ;

    /**
     * Construct an HTML block presenting a concert date.
     * @param string $str_date The concert date.
     * @param string $uri An URL to the concert date, which is to be linked.
     * @param string $date_color (optional) An override background color, in the #rrggbb form.
     * @return string The HTML block.
     */
	function date_to_html($str_date, $uri, $date_color) {
		$date = strtotime($str_date);

		/*
		 * Localization (l10n) of the date.
		 *
		 * Translation of day and month is leveraged to strftime(), the output
		 * of which is controlled by the locale. The locale must therefore be
		 * set to a value based on WPLANG (WordPress localized language).
		 */
		// Save current locale setting.
		// WARNING: setlocale() is known to not be thread-safe!
		$saved_locale = setlocale(LC_TIME,"0");
		setlocale(LC_TIME, WPLANG.'.UTF-8');
		$day_name = strftime('%a', $date);
		$month_name = strftime('%b', $date);
		// Restore previous locale setting
		setlocale(LC_TIME,$saved_locale);

		// Construct the HTML block presenting the formatted date.
		$override_color = (empty($date_color)) ? '' : 'style="background-color:'.$date_color.'"';
		$str  = '<span class="date-wrapper"><a title="'.date('Y-m-d', $date).'" href="'.$uri.'">';
		$str .= '  <span class="day-name" '.$override_color.'>'.htmlentities($day_name, ENT_QUOTES, 'UTF-8').'</span>';
		$str .= '  <span class="day-month"><span class="month">'.htmlentities($month_name, ENT_QUOTES, 'UTF-8').'</span>';
		$str .= '  <span class="day">'.date('d', $date).'</span></span>';
		$str .= '  <span class="year">'.date('Y', $date).'</span>';
		$str .= '</a></span>';

		return $str;
	}

	function cache_expired($cached_results) {
		if (!$cached_results || $cached_results == null) return true;
		return (bool) ((time() - $cached_results['timestamp'] ) > SONGKICK_REFRESH_CACHE);
	}

	function songkick_widget($args) {
		extract($args);

		$powered_by_songkick = __('Concerts by Songkick', SONGKICK_TEXT_DOMAIN);
		$title               = __('Concerts', SONGKICK_TEXT_DOMAIN);

		$options       = get_option(SONGKICK_OPTIONS);
		if ($options['username']) {
			$songkick_id      = $options['username'];
			$songkick_id_type = 'user';
		} else {
			$songkick_id      = $options['songkick_id'];
			$songkick_id_type = $options['songkick_id_type'];
		}
		$apikey        = $options['apikey'];
		$hide_if_empty = $options['hide_if_empty'];
		$title         = ($options['title']) ? $options['title'] : htmlentities($title, ENT_QUOTES, SONGKICK_I18N_ENCODING);
		$profile_title = __('See all concerts', SONGKICK_TEXT_DOMAIN);
		$logo          = $options['logo'];
		$date_color    = $options['date_color'];
		$attendance    = $options['attendance'];
		$number_of_events = $options['number_of_events'];

		if ($songkick_id_type == 'user')
			$sk = new SongkickUserEvents($apikey, $songkick_id, $attendance);
		else
			$sk = new SongkickArtistEvents($apikey, $songkick_id);

		$cached_results = get_option(SONGKICK_CACHE);
		if (cache_expired($cached_results)) {
			$events = $sk->get_upcoming_events($number_of_events);
			$cached_results = array('events' => $events, 'timestamp'=> time());
			update_option(SONGKICK_CACHE, $cached_results);
		} else {
			$events = $cached_results['events'];
		}

		if ($hide_if_empty && empty($events)) return;

		echo $before_widget . $before_title . $title . $after_title;
		if (empty($events)) {
			echo '<p>', htmlentities(__('No events...'), ENT_QUOTES, SONGKICK_I18N_ENCODING), '</p>';
		} else {
			echo "<ul>";
			foreach($events as $event) {
				$date = date_to_html($event->start->date, $event->uri, $date_color);
				if (strtolower($event->type) == 'festival') {
					$event_name = $event->displayName;
					$venue_name = $event->location->city;
				} else {
					$headliners = array();
					foreach ($event->performance as $performance) {
						if ($performance->billing == 'headline')
							$headliners[] = $performance->artist->displayName;
					}
					if (empty($headliners))
						$headliners[]= $event->performance[0];
					$event_name = join(', ', $headliners);
					$venue_name = $event->venue->displayName.', '.$event->location->city;
				}
				echo "<li> $date <span class='event-name'><a href=\"$event->uri\">$event_name</a>";
				echo '<br> <span class="venue">', htmlentities($venue_name, ENT_QUOTES, SONGKICK_I18N_ENCODING), '</span></span><div style="clear:left"></div></li>';
			}
			echo "</ul>";
		}
		echo '<p class="profile-title"><a href="'.$sk->profile_url().'">';
		echo htmlentities($profile_title, ENT_QUOTES, SONGKICK_I18N_ENCODING)."</a></p>";
		echo "<a class='powered-by' href='http://www.songkick.com/'>";
		echo "<img src='".site_url('/wp-content/plugins/songkick-concerts-and-festivals/'.$logo)."' title='".htmlentities($powered_by_songkick, ENT_QUOTES, SONGKICK_I18N_ENCODING)."' alt='".htmlentities($powered_by_songkick, ENT_QUOTES, SONGKICK_I18N_ENCODING)."' /></a>";
		echo $after_widget;
	}

	function songkick_widget_ctrl() {
		$options = get_option(SONGKICK_OPTIONS);
			if (!is_array($options)) {
			$options = array(
				'title'         => '',
				'songkick_id'   => '',
				'songkick_id_type' => 'user',
				'apikey'        => '',
				'logo'          => 'songkick-logo.png',
				'date_color'    => '#303030',
				'attendance'    => 'all',
				'number_of_events' => 10,
				'hide_if_empty' => false,
			);
		}

		if (current_user_can('manage_options') && $_POST['songkick_submit']) {
			$options['title']            = strip_tags(stripslashes($_POST['songkick_title']));
			$options['username']         = null;
			$options['songkick_id']      = strip_tags(stripslashes($_POST['songkick_id']));
			$options['songkick_id_type'] = strip_tags(stripslashes($_POST['songkick_id_type']));
			$options['apikey']         = strip_tags(stripslashes($_POST['songkick_apikey']));
			$options['logo']           = strip_tags(stripslashes($_POST['songkick_logo']));
			$options['date_color']     = strip_tags(stripslashes($_POST['songkick_date_color']));
			$options['hide_if_empty']     = ($_POST['songkick_hide_if_empty'] === 'on');
			$options['attendance']        = strip_tags(stripslashes($_POST['songkick_attendance']));

			$limit = (int)$_POST['songkick_number_of_events'];
			if ($limit > 50) $limit = 50;
			$options['number_of_events'] = $limit;

			update_option(SONGKICK_CACHE,   null);
			update_option(SONGKICK_OPTIONS, $options);
		}

		if ($options['username']) {
			$songkick_id_type = 'user';
			$songkick_id      = htmlspecialchars($options['username'], ENT_QUOTES);
		} else {
			$songkick_id_type = htmlspecialchars($options['songkick_id_type'], ENT_QUOTES);
			$songkick_id      = htmlspecialchars($options['songkick_id'], ENT_QUOTES);
		}
		$title            = htmlspecialchars($options['title'], ENT_QUOTES);
		$apikey           = htmlspecialchars($options['apikey'], ENT_QUOTES);
		$songkick_logo    = htmlspecialchars($options['logo'], ENT_QUOTES);
		$date_color       = htmlspecialchars($options['date_color'], ENT_QUOTES);
		$attendance       = htmlspecialchars($options['attendance']);
		$number_of_events = htmlspecialchars($options['number_of_events']);
		$hide_if_empty    = ($options['hide_if_empty']) ? 'checked="checked"' : '';

		echo '<p><select id="songkick_id_type" name="songkick_id_type">';
		echo '    <option value="user" '.(($songkick_id_type == 'user') ? ' selected' : '').'>username</option>';
		echo '    <option value="artist" '.(($songkick_id_type == 'artist') ? ' selected' : '').'>artist id</option>';
		echo '  </select>';
		echo '  <input size="15" id="songkick_id" name="songkick_id" type="text" value="'.$songkick_id.'" />';
		echo '</p>';

		echo '<p><label for="songkick_attendance">' . 'Attendance (for users only)' . '</label>';
		echo '  <select id="songkick_attendance" name="songkick_attendance">';
		echo '    <option value="all" '.(($attendance == 'all') ? ' selected' : '').'>all</option>';
		echo '    <option value="im_going" '.(($attendance == 'im_going') ? ' selected' : '').'>I’m going</option>';
		echo '    <option value="i_might_go" '.(($attendance == 'i_might_go') ? ' selected' : '').'>I might go</option>';
		echo '  </select>';
		echo '</p>';

		echo '<p><label for="songkick_apikey">' . 'Songkick API Key  (required):' . '</label>';
		echo '  <br><input class="widefat" id="songkick_apikey" name="songkick_apikey" type="text" value="'.$apikey.'" />';
		echo '</p>';

		echo '<p><label for="songkick_title">' . 'Title:' . '</label>';
		echo '  <br><input class="widefat" id="songkick_title" name="songkick_title" type="text" value="'.$title.'" />';
		echo '</p>';

		echo '<p><label for="songkick_number_of_events">Number of events to show (max 50)</label>';
		echo '   <br><input id="songkick_number_of_events" name="songkick_number_of_events" type="text" value="'.$number_of_events.'" /> ';
		echo '</p>';

		echo '<p><label for="songkick_hide_if_empty">';
		echo '  <input id="songkick_hide_if_empty" name="songkick_hide_if_empty" type="checkbox" '.$hide_if_empty.' /> ';
		echo    'Hide if there are no events?';
		echo '</label></p>';

		echo '<p><label for="songkick_logo">' . 'Songkick logo' . '</label>';
		echo '  <select id="songkick_logo" name="songkick_logo">';
		echo '    <option value="songkick-logo.png" '.(($songkick_logo == 'songkick-logo.png') ? ' selected' : '').'>' .
						'white background' . '</option>';
		echo '    <option value="songkick-logo-black.png" '.(($songkick_logo == 'songkick-logo-black.png') ? ' selected' : '').'>' .
						'black background' . '</option>';
		echo '  </select>';
		echo '</p>';

		echo '<p><label for="songkick_date_color">' . 'Background color for date:' . '</label>';
		echo '  <br><input class="widefat" id="songkick_date_color" name="songkick_date_color" type="text" value="'.$date_color.'" />';
		echo '</p>';

		echo '<input type="hidden" name="songkick_submit" value="submit" />';
	}

	register_sidebar_widget(array('Songkick Concerts and Festivals', 'widgets'), 'songkick_widget');
	register_widget_control(array('Songkick Concerts and Festivals', 'widgets'), 'songkick_widget_ctrl');
}

add_action('widgets_init', 'songkick_widget_init');

?>
