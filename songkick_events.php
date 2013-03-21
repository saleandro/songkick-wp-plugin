<?php

class InvalidApiKeyException extends Exception { }
class HttpError extends Exception { }

class SongkickEvents {
    public $apikey;
    public $upcoming_events = array();

    function SongkickEvents($apikey) {
        $this->apikey = ($apikey && $apikey != '') ? trim($apikey) : 'io09K9l3ebJxmxe2';
        $this->apiurl = 'http://api.songkick.com/api/3.0';
    }

    function get_events($page=1, $per_page=10) {
        $url = $this->url($page, $per_page);
        $cached_results = $this->get_cached_results($url);
        if ($this->cache_expired($cached_results)) {
            $cached_results = $this->get_uncached_events($url);
            $cached_results['timestamp'] = time();
            $this->set_cached_results($url, $cached_results);
        }
        return $cached_results;
    }

    function test_api_call() {
        try {
            $this->fetch("$this->apiurl/metro_areas/24426/calendar.xml?apikey=$this->apikey&page=1&per_page=1");
        } catch (HttpError $e) {
            $msg = 'Error on ' . get_bloginfo('url') . ' while trying to test Songkick Concerts plugins API key: ' . $e->getMessage();
            error_log($msg, 0);
            return '';
        }
    }

    protected function get_cached_results($key) {
        $all_cache = get_option(SONGKICK_CACHE);
        if (isset($all_cache[$key]) && $all_cache[$key]) {
            return $all_cache[$key];
        } else {
            return NULL;
        }
    }

    protected function get_uncached_events($url) {
        $response = $this->fetch($url);
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
        if (!$cached_results || $cached_results == null || !isset($cached_results['total'])) return true;
        return (bool) ((time() - $cached_results['timestamp'] ) > SONGKICK_REFRESH_CACHE);
    }

    protected function fetch($url) {
        $http     = new WP_Http;
        $response =  $http->request($url);

        if (is_wp_error($response)) {
            throw new HttpError('WP_Http/WP_Error message: '.$response->get_error_message());
        } elseif (!is_array($response)) {
            throw new Exception('WP_Http/Invalid response');
        } elseif  ($response['response']['code'] == 403) {
            throw new InvalidApiKeyException();
        } elseif  ($response['response']['code'] != 200) {
            throw new Exception('WP_Http error response: '.$response['response']['code']);
        }
        return $response['body'];
    }

    protected function events_from_json($json) {
        $json_docs = json_decode($json);
        $total     = $json_docs->resultsPage->totalEntries;
        if (isset($json_docs->resultsPage->results->event) && is_array($json_docs->resultsPage->results->event)) {
            $events = $json_docs->resultsPage->results->event;
        } else {
            $events = array();
        }
        return array('events' => $events, 'total' => $total);
    }
}

?>
