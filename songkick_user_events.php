<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickUserEvents extends SongkickEvents {
    public $username;
    public $apikey;

    function SongkickUserEvents($apikey, $username, $attendance='all', $gigography=true) {
        $this->SongkickEvents($apikey);
        $this->attendance = $attendance;
        $this->username = trim($username);
        $this->gigography = $gigography;
    }

    function profile_url() {
        return "http://www.songkick.com/users/$this->username";
    }

    protected function url($page, $per_page) {
        $method = $this->gigography ? 'gigography' : 'events';
        return "$this->apiurl/users/$this->username/$method.json?apikey=$this->apikey&per_page=$per_page&attendance=$this->attendance&page=$page";
    }
}
?>