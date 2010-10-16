<?php

/*
Plugin Name: Songkick Concerts
Plugin URI: http://github.com/saleandro/songkick-wp-plugin
Description: Widget to show your upcoming concerts based on your Songkick profile.
Version: 0.2
Author: Sabrina Leandro
Author URI: http://github.com/saleandro

*/

/*
    Copyright 2010 Sabrina Leandro (saleandro@yahoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('WP_Http'))
	include_once(ABSPATH . WPINC . '/class-http.php');


class SongkickUserEvents extends SongkickEvents {
	
	public $username;
	
	function SongkickUserEvents($apikey, $username) {
		$this->apikey   = $apikey;
		$this->username = $username;
	}

	protected function get_my_events() {
		$url      = "http://api.songkick.com/api/3.0/users/$this->username/events.json?apikey=$this->apikey";
		$response = $this->fetch($url);
		if ($response === false) {
			// OMG something went wrong...
		}
		return $this->events_from_json($response);
	}
}

class SongkickEvents {
	
	private $apikey;	
	public $events = array();
	
	function SongkickUserEvents($apikey) {
		$this->apikey = $apikey;
	}

	function get_events() {
		if ($this->cache_expired()) {
			$this->events = $this->get_my_events();
		}
		
		return $this->events;
	}
	
	private function options() {
		get_option(SONGKICK_OPTIONS);
	}
	
	private function cache_expired() {
		return (true || empty($this->events));
	}
	
	protected function fetch($url) {
		$http     = new WP_Http;
		$response =  $http->request($url);
		if ($response['response']['code'] != 200) return false;
		return $response['body'];
	}	
	
	protected function events_from_json($json) {
		$json_docs = json_decode($json);
 		if ($json_docs->totalEntries === 0) {
			return array();
		} else {
			return $json_docs->resultsPage->results->event;
		}
	}
}

function songkick_widget_init() {
	if (!function_exists('register_sidebar_widget'))
		return;

	function date_to_html($str_date, $uri, $date_color) {
		$override_color = (empty($date_color)) ? '' : 'style="background-color:'.$date_color.'"';
		$date = strtotime($str_date);
		$str  = '<span class="date-wrapper"><a title="'.date('Y-m-d', $date).'" href="'.$uri.'">';
		$str .= '  <span class="day-name" '.$override_color.'>'.date('D', $date).'</span>';
		$str .= '  <span class="day-month"><span class="month">'.date('M', $date).'</span>';
		$str .= '  <span class="day">'.date('d', $date).'</span></span>';
		$str .= '  <span class="year">'.date('Y', $date).'</span>';
		$str .= '</a></span>';
		return $str;
	}

	function songkick_widget($args) {
		extract($args);
		
		$powered_by_songkick = "Concerts by Songkick";
		$title               = 'Upcoming concerts';
		
		$options       = get_option(SONGKICK_OPTIONS);
		$username      = $options['username'];
		$apikey        = $options['apikey'];
		$hide_if_empty = $options['hide_if_empty'];
		$title         = ($options['title']) ? $options['title'] : $title;
		$profile_title = _("See all concerts");
		$logo          = $options['logo'];
		$date_color    = $options['date_color'];
			
		$sk =  new SongkickUserEvents($apikey, $username);
		$sk->get_events();
		
		if ($hide_if_empty && empty($sk->events)) return;
	
		echo '<link href="'.site_url('/wp-content/plugins/songkick_concerts/songkick_concerts.css').'" media="screen" rel="stylesheet" type="text/css" />';
		echo $before_widget . $before_title . $title . $after_title;
		if (empty($sk->events)) {
			echo "<p>No upcoming events...</p>";
		} else {
			echo "<ul>";
			foreach($sk->events as $event) {
				$date = date_to_html($event->start->date, $event->uri, $date_color);

				if (strtolower($event->type) == 'festival') {
					$event_name = $event->displayName;
					$venue_name = '';
				} else {
					$headliners = array();
					foreach ($event->performance as $performance) {
						$headliners[] = $performance->artist->displayName;
					}
					$event_name = join(', ', $headliners);
					$venue_name = ' at '.$event->venue->displayName;
				}
				echo "<li> $date <span class='event-name'><a href=\"$event->uri\">$event_name</a>";
				echo "<span class='venue'>$venue_name</span></span><div style=\"clear:left\"></div></li>";
			}
			echo "</ul>";
		}
		echo "<p class=\"profile-title\"><a href='http://www.songkick.com/users/$username/'>";
		echo _($profile_title)."</a></p>";
		echo "<a class='powered-by' href='http://www.songkick.com/'>";
		echo "<img src='".site_url('/wp-content/plugins/songkick_concerts/'.$logo)."' title='"._($powered_by_songkick)."' alt='"._($powered_by_songkick)."' /></a>";
		echo $after_widget;
	}

	function songkick_widget_ctrl() {
		$options = get_option(SONGKICK_OPTIONS);
		if (!is_array($options)) {
			$options = array(
				'title'         => '', 
				'username'      => '', 
				'apikey'        => '', 
				'logo'          => 'songkick-logo.png',
				'date_color'    => '#303030',
				'hide_if_empty' => false, 
			);
		}

		if ($_POST['songkick_submit']) {
			$options['title']          = strip_tags(stripslashes($_POST['songkick_title']));
			$options['username']       = strip_tags(stripslashes($_POST['songkick_username']));
			$options['apikey']         = strip_tags(stripslashes($_POST['songkick_apikey']));
			$options['logo']           = strip_tags(stripslashes($_POST['songkick_logo']));
			$options['date_color']     = strip_tags(stripslashes($_POST['songkick_date_color']));
			$options['hide_if_empty']  = ($_POST['songkick_hide_if_empty'] === 'on');
			update_option(SONGKICK_OPTIONS, $options);
		}

		$title    = htmlspecialchars($options['title'], ENT_QUOTES);
		$username = htmlspecialchars($options['username'], ENT_QUOTES);
		$apikey   = htmlspecialchars($options['apikey'], ENT_QUOTES);
		$songkick_logo = htmlspecialchars($options['logo'], ENT_QUOTES);
		$date_color    = htmlspecialchars($options['date_color'], ENT_QUOTES);
		$hide_if_empty = ($options['hide_if_empty']) ? 'checked="checked"' : '';

		echo '<p><label for="songkick_title">' . __('Title:') . '</label>';
		echo '  <br><input class="widefat" id="songkick_title" name="songkick_title" type="text" value="'.$title.'" />';
		echo '</p>';
		echo '<p><label for="songkick_username">' . __('Username:') . '</label>';
		echo '  <br><input class="widefat" id="songkick_username" name="songkick_username" type="text" value="'.$username.'" />';
		echo '</p>';
		echo '<p><label for="songkick_apikey">' . __('Songkick API Key:') . '</label>';
		echo '  <br><input class="widefat" id="songkick_apikey" name="songkick_apikey" type="text" value="'.$apikey.'" />';
		echo '</p>';
		echo '<p><label for="songkick_logo">' . __('Songkick logo') . '</label>';
		echo '  <select id="songkick_logo" name="songkick_logo">';
		echo '    <option value="songkick-logo.png" '.(($songkick_logo == 'songkick-logo.png') ? ' selected' : '').'>' . __('white background') . '</option>';
		echo '    <option value="songkick-logo-black.png" '.(($songkick_logo == 'songkick-logo-black.png') ? ' selected' : '').'>' . __('black background') . '</option>';
		echo '  </select>';
		echo '</p>';
		echo '<p><label for="songkick_date_color">' . __('Background color for date:') . '</label>';
		echo '  <br><input class="widefat" id="songkick_date_color" name="songkick_date_color" type="text" value="'.$date_color.'" />';
		echo '</p>';
		echo '<p><label for="songkick_hide_if_empty">';
		echo '  <input id="songkick_hide_if_empty" name="songkick_hide_if_empty" type="checkbox" '.$hide_if_empty.' /> ';
		echo    __('Hide if there are no events?');
		echo '</label></p>';
		echo '<input type="hidden" name="songkick_submit" value="submit" />';
	}

	register_sidebar_widget(array('Songkick Concerts', 'widgets'), 'songkick_widget');
	register_widget_control(array('Songkick Concerts', 'widgets'), 'songkick_widget_ctrl');
}

add_action('widgets_init', 'songkick_widget_init');

?>