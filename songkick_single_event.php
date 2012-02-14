<?php

require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickSingleEvent extends SongkickEvents {
    public $id;
    public $apikey;

    function SongkickSingleEvent($apikey, $id) {
        parent::SongkickEvents($apikey);
        $this->id = trim($id);
    }

    function get_event() {
      $url = $this->url();
      $cached_results = $this->get_cached_results($url);
      if ($this->cache_expired($cached_results)) {
        $cached_results = $this->get_uncached_event($url);
        $cached_results['timestamp'] = time();
        $this->set_cached_results($url, $cached_results);
      }
      return $cached_results;
    }

    function get_uncached_event($url) {
        $response = $this->fetch($url);
        return $this->event_from_json($response);
    }

    protected function url(){
        return "$this->apiurl/events/$this->id.json?apikey=$this->apikey";
    }

    protected function event_from_json($json) {
        $json_docs = json_decode($json);
        //$total     = $json_docs->resultsPage->totalEntries;
        if (isset($json_docs->resultsPage->results->event) && is_object($json_docs->resultsPage->results->event)) {
            $event = $json_docs->resultsPage->results->event;
        } else {
            $event = array();
        }
        return array('event' => $event);
    }
}
?>