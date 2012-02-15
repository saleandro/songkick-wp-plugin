<?php

require_once dirname(__FILE__) . '/songkick_presentable.php';
require_once dirname(__FILE__) . '/songkick_presentable_event.php';
require_once dirname(__FILE__) . '/songkick_presentable_single_event.php';
require_once dirname(__FILE__) . '/songkick_user_events.php';
require_once dirname(__FILE__) . '/songkick_artist_events.php';
require_once dirname(__FILE__) . '/songkick_metro_area_events.php';
require_once dirname(__FILE__) . '/songkick_venue_events.php';

class SongkickPresentableEvents extends SongkickPresentable
{

    protected $widget_args = array(); // when we are a widget, we need these to render

    function SongkickPresentableEvents($options)
    {
        if ($options['username']) { // legacy
            $songkick_id = $options['username'];
            $songkick_id_type = 'user';
        } else {
            $songkick_id = $options['songkick_id'];
            $songkick_id_type = $options['songkick_id_type'];
        }
        $apikey = $options['apikey'];
        $attendance = $options['attendance'];

        $this->number_of_events = $options['number_of_events'];
        if (!isset($options['show_pagination'])) $options['show_pagination'] = false;
        $this->show_pagination = $options['show_pagination'];
        if (!isset($options['page'])) $options['page'] = 1;
        $this->page = intval($options['page']);

        switch ($songkick_id_type) {
            case 'user':
                $this->songkick_events = new SongkickUserEvents($apikey, $songkick_id, $attendance);
                break;
            case 'artist':
                $this->songkick_events = new SongkickArtistEvents($apikey, $songkick_id);
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

        if (isset($options['is_widget']) && $options['is_widget'] == true) {
            $this->template = 'songkick-widget_events.php';
            $this->widget_args = $options['_widget'];
        } else {
            $this->template = 'songkick-events.php';
        }
        $results = $this->songkick_events->get_upcoming_events($this->page, $this->number_of_events);
        $this->events = $results['events'];
        $this->total = $results['total'];
        $this->date_color = $options['date_color'];
        $this->logo = $options['logo'];
        $this->no_calendar_style = false;
        if (isset($options['no_calendar_style'])) {
            $this->no_calendar_style = $options['no_calendar_style'];
        }
    }

    function to_html()
    {
        $profile_title = __('See all concerts', SONGKICK_TEXT_DOMAIN);

        $str = '';
        if (empty($this->events)) {
            $str .= '<p>' . htmlentities(__('No events...'), ENT_QUOTES, SONGKICK_I18N_ENCODING) . '</p>';
        } else {
            $str .= $this->render($this->template, array());
        }
        return $str;
    }

    function no_events()
    {
        return empty($this->events);
    }

    protected function current_url($query_string)
    {
        global $wp;
        $current_url = remove_query_arg('skp', add_query_arg($wp->query_string, '', home_url($wp->request)));
        return add_query_arg($query_string, '', $current_url);
    }

    protected function page_to_html($page)
    {
        $str = '';
        if ($page == $this->page)
            $str .= "$page &nbsp;";
        else
            $str .= "<a href=\"" . $this->current_url("skp=$page") . "\">$page</a> &nbsp;";
        return $str;
    }

}

?>