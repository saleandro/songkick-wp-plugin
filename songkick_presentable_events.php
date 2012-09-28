<?php

require_once dirname(__FILE__) . '/songkick_presentable_event.php';
require_once dirname(__FILE__) . '/songkick_user_events.php';
require_once dirname(__FILE__) . '/songkick_artist_events.php';
require_once dirname(__FILE__) . '/songkick_metro_area_events.php';
require_once dirname(__FILE__) . '/songkick_venue_events.php';

class SongkickPresentableEvents {

    function SongkickPresentableEvents($options) {
        if (isset($options['username']) && $options['username']) { // legacy
            $songkick_id      = $options['username'];
            $songkick_id_type = 'user';
        } else {
            $songkick_id      = $options['songkick_id'];
            $songkick_id_type = $options['songkick_id_type'];
        }
        $apikey     = (isset($options['apikey'])) ? $options['apikey'] : null;
        $attendance = (isset($options['attendance'])) ? $options['attendance'] : false;
        $gigography = (isset($options['gigography']) && $options['gigography'] == 'true') ? true : false;
        $order      = (isset($options['order'])) ? $options['order'] : null;

        $this->number_of_events = (isset($options['number_of_events']) && is_numeric($options['number_of_events'])) ? $options['number_of_events'] : 10;
        if (!isset($options['show_pagination'])) $options['show_pagination'] = false;
        $this->show_pagination = $options['show_pagination'];
        if (!isset($options['page'])) $options['page'] = 1;
        $this->page            = intval($options['page']);

        if (empty($songkick_id)) {
            throw new Exception("Blank songkick id");
        }
        switch ($songkick_id_type) {
            case 'user':
                $this->songkick_events = new SongkickUserEvents($apikey, $songkick_id, $attendance, $gigography, $order);
                break;
            case 'artist':
                $this->songkick_events = new SongkickArtistEvents($apikey, $songkick_id, $gigography, $order);
                break;
            case 'metro_area':
                $this->songkick_events = new SongkickMetroAreaEvents($apikey, $songkick_id);
                break;
            case 'venue':
                $this->songkick_events = new SongkickVenueEvents($apikey, $songkick_id);
                break;
            default:
                throw new Exception("Unknown songkick id type: $songkick_id_type");
        }

        $results          = $this->songkick_events->get_events($this->page, $this->number_of_events);
        $this->events     = $results['events'];
        $this->total      = $results['total'];
        $this->date_color = isset($options['date_color']) ? $options['date_color'] : null;
        $this->logo       = isset($options['logo']) ? $options['logo'] : null;
        $this->no_calendar_style = false;
        if (isset($options['no_calendar_style'])) {
            $this->no_calendar_style = $options['no_calendar_style'];
        }
    }

    function to_html() {
        $profile_title = __('See all concerts', SONGKICK_TEXT_DOMAIN);

        $str = '';
        if (empty($this->events)) {
            $str .= '<p>'. htmlentities(__('No events...'), ENT_QUOTES, SONGKICK_I18N_ENCODING). '</p>';
        } else {
            $str .= '<ul class="songkick-events">';
            foreach($this->events as $event) {
                $presentable_event = new SongkickPresentableEvent($event);
                $str .= '<li>'.$presentable_event->to_html($this->no_calendar_style, $this->date_color).'</li>';
            }
            $str .= '</ul>';
        }
        if ($this->show_pagination) {
            $pages = ceil($this->total / $this->number_of_events);
            if ($pages > 1) {
                $min = max($this->page - 2, 2);
                $max = min($this->page + 2, $pages-1);

                $str .= '<div class="pagination">';
                if (1 == $this->page)
                    $str .= "« &nbsp;";
                else {
                    $prev = $this->page-1;
                    $str .= "<a href=\"".$this->current_url("skp=$prev")."\" rel=\"prev\">«</a> &nbsp;";
                }

                $str .= $this->page_to_html(1);
                if ($min > 2) $str .= "… &nbsp;";
                for ($i=$min; $i<$max+1; $i++) {
                    $str .=  $this->page_to_html($i);
                }
                if ($max < $pages-1) $str .= "… &nbsp;";
                $str .= $this->page_to_html($pages);
                if ($pages == $this->page)
                    $str .= "» &nbsp;";
                else {
                    $next = $this->page+1;
                    $str .= "<a href=\"".$this->current_url("skp=$next")."\" rel=\"next\">»</a> &nbsp;";
                }
                $str .= '</div>';
            }
        } else {
            $str .= '<p class="profile-title"><a href="'.$this->songkick_events->profile_url().'">';
            $str .= htmlentities($profile_title, ENT_QUOTES, SONGKICK_I18N_ENCODING)."</a></p>";
        }
        $str .= $this->powered_by_songkick($this->logo);
        return $str;
    }

    function no_events() {
        return empty($this->events);
    }

    private function current_url($query_string) {
        global $wp;
        $current_url = remove_query_arg('skp', add_query_arg($wp->query_string, '', home_url($wp->request)));
        return add_query_arg($query_string, '', $current_url);
    }

    private function page_to_html($page) {
        $str = '';
        if ($page == $this->page)
            $str .= "$page &nbsp;";
        else
            $str .= "<a href=\"".$this->current_url("skp=$page")."\">$page</a> &nbsp;";
        return $str;
    }

    private function powered_by_songkick($logo) {
        $text = __('Concerts by Songkick', SONGKICK_TEXT_DOMAIN);
        $html  = "<a class='powered-by' href='http://www.songkick.com/'>";
        $html .= "<img src='".site_url('/wp-content/plugins/songkick-concerts-and-festivals/'.$logo)."' title='".htmlentities($text, ENT_QUOTES, SONGKICK_I18N_ENCODING)."' alt='".htmlentities($text, ENT_QUOTES, SONGKICK_I18N_ENCODING)."' /></a>";
        return $html;
    }

}
?>