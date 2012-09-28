<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickUserEvents extends SongkickEvents {
    public $username;
    public $apikey;

    function SongkickUserEvents($apikey, $username, $attendance='all', $gigography=true, $order=null) {
        $this->SongkickEvents($apikey);
        $this->attendance = $attendance;
        $this->username = trim($username);
        $this->gigography = $gigography;
        $this->order = $order;
    }

    function profile_url() {
        return "http://www.songkick.com/users/$this->username";
    }

    protected function url($page, $per_page) {
        if ($this->gigography) {
            $method = "gigography";
            if (!$this->order)
                $this->order = 'desc';
        } else {
            $method = "events";
            if (!$this->order)
                $this->order = 'asc';
        }
        $url  = "$this->apiurl/users/$this->username/$method.json?apikey=$this->apikey";
        $url .= "&order=$this->order&per_page=$per_page&page=$page";
        if ($this->attendance) {
            $url .= "&attendance=$this->attendance";
        }
        return $url;
    }
}
?>