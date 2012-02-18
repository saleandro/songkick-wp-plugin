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

    protected function url($page, $per_page){
        $method = $this->gigography ? 'gigography' : 'calendar';
        return "$this->apiurl/artists/$this->id/$method.json?apikey=$this->apikey&per_page=$per_page&page=$page";
    }
}
?>