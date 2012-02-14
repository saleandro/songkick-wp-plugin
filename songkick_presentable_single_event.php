<?php


require_once dirname(__FILE__) . '/songkick_single_event.php';

class SongkickPresentableSingleEvent extends SongkickPresentable
{

    function SongkickPresentableSingleEvent($options)
    {
        $songkick_id = $options['event_id'];
        $apikey = $options['apikey'];

        if (empty($songkick_id)) {
            throw new Exception("Unknown songkick id: $songkick_id");
        }

        $this->template = 'songkick-single_event.php';
        $this->songkick_event = new SongkickSingleEvent($apikey, $songkick_id);
        $results = $this->songkick_event->get_event();
        $this->event = $results['event'];
        $this->date_color = $options['date_color'];
        $this->logo = $options['logo'];
        $this->no_calendar_style = false;
        if (isset($options['no_calendar_style'])) {
            $this->no_calendar_style = $options['no_calendar_style'];
        }
    }

    function to_html()
    {
        $str = '';
        if (empty($this->event)) {
            $str .= '<p>' . htmlentities(__('Event not found...'), ENT_QUOTES, SONGKICK_I18N_ENCODING) . '</p>';
        } else {
            $str .= $this->render($this->template, compact('no_calendar_style', 'date_color'));
        }

        $str .= $this->powered_by_songkick($this->logo);
        return $str;
    }

    function get_headliners()
    {
        $headliners = array();
        foreach ($this->event->performance as $performance) {
            if ($performance->billing == 'headline') {
                $headliners[] = $performance;
            }
        }
        return $headliners;
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

    private function page_to_html($page)
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