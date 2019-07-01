<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickVenueEvents extends SongkickEvents {
    public $id;
    public $apikey;

    function __construct($apikey, $id) {
        parent::__construct($apikey);
        $this->id = trim($id);
    }

    function profile_url() {
        return "http://www.songkick.com/venues/$this->id";
    }

    protected function url($page, $per_page) {
        return "$this->apiurl/venues/$this->id/calendar.json?apikey=$this->apikey&per_page=$per_page&page=$page";
    }
}
?>