<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickUserEvents extends SongkickEvents {
	public $username;
	public $apikey;

	function SongkickUserEvents($apikey, $username, $attendance='all') {
		$this->SongkickEvents($apikey);
		$this->attendance = $attendance;
		$this->username = $username;
	}

	function profile_url() {
		return "http://www.songkick.com/users/$this->username";
	}

	protected function get_my_upcoming_events($per_page) {
		$url      = "$this->apiurl/users/$this->username/events.json?apikey=$this->apikey&per_page=$per_page&attendance=$this->attendance";
		$response = $this->fetch($url);
		if ($response === false) {
			// OMG something went wrong...
		}
		return $this->events_from_json($response);
	}
}
?>