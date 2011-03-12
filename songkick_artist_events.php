<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickArtistEvents extends SongkickEvents {
	public $id;
	public $apikey;

	function SongkickArtistEvents($apikey, $id) {
		$this->SongkickEvents($apikey);
		$this->id = $id;
	}

	function profile_url() {
		return "http://www.songkick.com/artists/$this->id";
	}

	protected function get_my_upcoming_events($per_page) {
		$url      = "$this->apiurl/artists/$this->id/calendar.json?apikey=$this->apikey&per_page=$per_page";
		$response = $this->fetch($url);
		if ($response === false) {
			// OMG something went wrong...
		}
		return $this->events_from_json($response);
	}
}
?>