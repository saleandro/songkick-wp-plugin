<?php
require_once dirname(__FILE__) . '/songkick_events.php';

class SongkickArtistEvents extends SongkickEvents {
    public $id;
    public $apikey;

    function SongkickArtistEvents($apikey, $id, $gigography=false, $order=null) {
        $this->SongkickEvents($apikey);
        $this->id = trim($id);
        $this->gigography = $gigography;
        $this->order = $order;
    }

    function profile_url() {
        return "http://www.songkick.com/artists/$this->id";
    }

    protected function url($page, $per_page) {
        if ($this->gigography) {
            $method = "gigography";
            if (!$this->order)
                $this->order = 'desc';
        } else {
            $method = "calendar";
            if (!$this->order)
                $this->order = 'asc';
        }
        $url  = "$this->apiurl/artists/$this->id/$method.json?apikey=$this->apikey";
        $url .= "&order=$this->order&per_page=$per_page&page=$page";
        return $url;
    }
}

?>