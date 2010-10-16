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
			
		$sk =  new SongkickUserEvents($apikey, $username);
		$sk->get_events();
		
		if ($hide_if_empty && empty($sk->events)) return;
	
		echo $before_widget . $before_title . $title . $after_title;
		if (empty($sk->events)) {
			echo "<p>No upcoming events...</p>";
		} else {
			echo "<ul>";
			foreach($sk->events as $event) {
				if (strtolower($event->type) == 'festival') {
					$event_name = $event->displayName;
					$venue_name = '';
					$date = '';
				} else {
					$headliners = array();
					foreach ($event->performance as $performance) {
						$headliners[] = $performance->artist->displayName;
					}
					$event_name = join(', ', $headliners);
					$venue_name = '<span class="venue"> at '.$event->venue->displayName.'</span>';
					$date = '<span class="date">('.date('M, d', strtotime($event->start->date)).')</span>';
				}
				echo "<li><a href=\"$event->uri\">$event_name</a>$venue_name $date</li>";
			}
			echo "</ul>";
		}
		echo "<p style='margin-top: 10px; margin-bottom: 0px; text-align: right'><a href='http://www.songkick.com/users/$username/'>";
		echo _($profile_title)."</a></p>";
		echo "<a style='margin: 0px' href='http://www.songkick.com/'>";
		echo "<img style='margin: 0px' src='".site_url('/wp-content/plugins/songkick_concerts/'.$logo)."' title='"._($powered_by_songkick)."' alt='"._($powered_by_songkick)."' /></a>";
		echo $after_widget;
	}

	function songkick_widget_ctrl() {
		$options = get_option(SONGKICK_OPTIONS);
		if (!is_array($options)) {
			$options = array(
				'title'    => '', 
				'username' => '', 
				'apikey'   => '', 
				'logo'     => 'songkick-logo.png',
				'hide_if_empty' => false, 
			);
		}

		if ($_POST['songkick_submit']) {
			$options['title']          = strip_tags(stripslashes($_POST['songkick_title']));
			$options['username']       = strip_tags(stripslashes($_POST['songkick_username']));
			$options['apikey']         = strip_tags(stripslashes($_POST['songkick_apikey']));
			$options['hide_if_empty']  = ($_POST['songkick_hide_if_empty'] === 'on');
			$options['logo']           = strip_tags(stripslashes($_POST['songkick_logo']));
			update_option(SONGKICK_OPTIONS, $options);
		}

		$title    = htmlspecialchars($options['title'], ENT_QUOTES);
		$username = htmlspecialchars($options['username'], ENT_QUOTES);
		$apikey   = htmlspecialchars($options['apikey'], ENT_QUOTES);
		$hide_if_empty = ($options['hide_if_empty']) ? 'checked="checked"' : '';
		$songkick_logo = htmlspecialchars($options['logo'], ENT_QUOTES);

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