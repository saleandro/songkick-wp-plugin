<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickPastEvents extends SongkickEvents {
    public $username;
    public $apikey;

    function SongkickPastEvents($apikey, $username) {
        $this->SongkickEvents($apikey);
        $this->username = trim($username);
    }

    function profile_url() {
        return "http://www.songkick.com/users/$this->username";
    }

    protected function url($page, $per_page) {
        return "$this->apiurl/users/$this->username/gigography.json?apikey=$this->apikey&per_page=$per_page&page=$page";
    }
}
?>