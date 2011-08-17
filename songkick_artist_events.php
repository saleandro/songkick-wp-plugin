<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickArtistEvents extends SongkickEvents {
	public $id;
	public $apikey;

	function SongkickArtistEvents($apikey, $id) {
		$this->SongkickEvents($apikey);
		$this->id = trim($id);
	}

	function profile_url() {
		return "http://www.songkick.com/artists/$this->id";
	}

	protected function url($per_page){
		return "$this->apiurl/artists/$this->id/calendar.json?apikey=$this->apikey&per_page=$per_page";
	}
}

class SongkickArtistGigography extends SongkickGigography {
	public $id;
	public $apikey;

	function SongkickArtistGigography($apikey, $id) {
		$this->SongkickGigography($apikey);
		$this->id = trim($id);
	}

	function profile_url() {
		return "http://www.songkick.com/artists/$this->id";
	}

	protected function url($per_page){
		return "$this->apiurl/artists/$this->id/gigography.json?apikey=$this->apikey&per_page=$per_page";
	}
}
?>