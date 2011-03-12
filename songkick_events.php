<?php

class SongkickEvents {
	public $apikey;
	public $upcoming_events = array();

	function SongkickEvents($apikey) {
		$this->apikey = $apikey;
		$this->apiurl = 'http://api.songkick.com/api/3.0';
	}

	function get_upcoming_events($per_page=10) {
		$this->upcoming_events = $this->get_my_upcoming_events($per_page);
		return $this->upcoming_events;
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

?>
