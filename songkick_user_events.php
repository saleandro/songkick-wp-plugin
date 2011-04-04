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

	protected function url($per_page) {
		return "$this->apiurl/users/$this->username/events.json?apikey=$this->apikey&per_page=$per_page&attendance=$this->attendance";
	}
}
?>