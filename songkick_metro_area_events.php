<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickMetroAreaEvents extends SongkickEvents {
    public $id;
    public $apikey;

    function SongkickMetroAreaEvents($apikey, $id) {
        $this->SongkickEvents($apikey);
        $this->id = trim($id);
    }

    function profile_url() {
        return "http://www.songkick.com/metro_areas/$this->id";
    }

    protected function url($page, $per_page) {
        return "$this->apiurl/metro_areas/$this->id/calendar.json?apikey=$this->apikey&per_page=$per_page&page=$page";
    }
}

?>