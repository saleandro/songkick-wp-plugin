<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickArtistEvents extends SongkickEvents {
    public $id;
    public $apikey;

    function SongkickArtistEvents($apikey, $id, $gigography=false) {
        $this->SongkickEvents($apikey);
        $this->id = trim($id);
        $this->gigography = $gigography;
    }

    function profile_url() {
        return "http://www.songkick.com/artists/$this->id";
    }

    protected function url($page, $per_page) {
        $url = "$this->apiurl/artists/$this->id/";
        if ($this->gigography) {
            $url .= "gigography.json?order=desc";
        } else {
            $url .= "calendar.json?order=asc";
        }
        $url .= "&apikey=$this->apikey&per_page=$per_page&page=$page";
        return $url;
    }
}

?>