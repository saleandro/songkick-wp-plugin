<?php

class SongkickEvents {
	public $apikey;
	public $upcoming_events = array();

	function SongkickEvents($apikey) {
		$this->apikey = $apikey;
		$this->apiurl = 'http://api.songkick.com/api/3.0';
	}

	function get_upcoming_events($per_page=10) {
		$cached_results = $this->get_cached_results($this->url($per_page));
		if ($this->cache_expired($cached_results)) {
			$events = $this->get_uncached_upcoming_events($per_page);
			$cached_results = array('events' => $events, 'timestamp'=> time());
			$this->set_cached_results($this->url($per_page), $cached_results);
		} else {
			$events = $cached_results['events'];
		}
		return $events;
	}
	
	protected function get_cached_results($key) {
		$all_cache = get_option(SONGKICK_CACHE);
		if (isset($all_cache[$key]) && $all_cache[$key]) {
			return $all_cache[$key];
		} else {
			return NULL;
		}
	}
	
	protected function get_uncached_upcoming_events($per_page) {
		$response = $this->fetch($this->url($per_page));
		if ($response === false) {
			// OMG something went wrong...
		}
		return $this->events_from_json($response);
	}

	protected function set_cached_results($key, $value) {
		$all_cache = get_option(SONGKICK_CACHE);
		if (!$all_cache) {
			$all_cache = array();
		}
		$all_cache[$key] = $value;
		update_option(SONGKICK_CACHE, $all_cache);
	}
	
	protected function cache_expired($cached_results) {
		if (!$cached_results || $cached_results == null) return true;
		return (bool) ((time() - $cached_results['timestamp'] ) > SONGKICK_REFRESH_CACHE);
	}

	protected function fetch($url) {
		$http     = new WP_Http;
		$response =  $http->request($url);
		if (!is_array($response) || $response['response']['code'] != 200) return false;
		return $response['body'];
	}

	protected function events_from_json($json) {
		$json_docs = json_decode($json);
		if ($json_docs->resultsPage->totalEntries === 0) {
			return array();
		} else {
			return $json_docs->resultsPage->results->event;
		}
	}
}

?>
